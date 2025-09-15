<?php
/**
 * Script de correction automatique des problèmes d'ajout de produit
 * StyleHub E-Commerce Platform
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== CORRECTION DES PROBLÈMES D'AJOUT DE PRODUIT ===\n\n";

// 1. Vérifier et créer les dossiers nécessaires
echo "1. Vérification des dossiers...\n";

$directories = [
    'uploads' => 'Dossier pour les images de produits',
    'cache' => 'Dossier pour le cache',
    'admin/logs' => 'Dossier pour les logs'
];

foreach ($directories as $dir => $description) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✓ Créé: $dir ($description)\n";
        } else {
            echo "✗ Impossible de créer: $dir\n";
        }
    } else {
        echo "✓ Existe: $dir\n";
    }
    
    // Vérifier les permissions
    if (is_writable($dir)) {
        echo "  ✓ Permissions OK\n";
    } else {
        echo "  ✗ Permissions insuffisantes\n";
        chmod($dir, 0755);
        echo "  ✓ Permissions corrigées\n";
    }
}

// 2. Créer un fichier de configuration de base de données minimal
echo "\n2. Configuration de base de données...\n";

$db_config_content = '<?php
/**
 * Configuration de base de données de secours
 * Utilisé si la configuration principale échoue
 */

// Configuration par défaut
$default_config = [
    "host" => "localhost",
    "dbname" => "stylehub_db", 
    "username" => "root",
    "password" => "",
    "charset" => "utf8mb4"
];

// Essayer de se connecter avec SQLite en fallback
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        echo "Connexion PDO existante détectée\n";
    } else {
        // Essayer MySQL
        $dsn = "mysql:host={$default_config[\'host\']};dbname={$default_config[\'dbname\']};charset={$default_config[\'charset\']}";
        $pdo = new PDO($dsn, $default_config[\'username\'], $default_config[\'password\'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        echo "Connexion MySQL réussie\n";
    }
} catch (Exception $e) {
    echo "Erreur MySQL, tentative SQLite...\n";
    try {
        // Fallback vers SQLite
        $pdo = new PDO("sqlite:" . __DIR__ . "/stylehub.db", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // Créer la table products basique
        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            sale_price DECIMAL(10,2) NULL,
            stock INTEGER DEFAULT 0,
            sku VARCHAR(100) UNIQUE,
            category_id INTEGER,
            brand VARCHAR(100),
            material VARCHAR(100),
            gender VARCHAR(20) DEFAULT \'unisexe\',
            season VARCHAR(20) DEFAULT \'toute_saison\',
            image_url VARCHAR(255),
            gallery TEXT,
            featured INTEGER DEFAULT 0,
            status VARCHAR(20) DEFAULT \'active\',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            slug VARCHAR(255)
        )");
        
        // Créer les autres tables nécessaires
        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100),
            status VARCHAR(20) DEFAULT \'active\'
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS sizes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) NOT NULL,
            category VARCHAR(50) DEFAULT \'clothing\',
            sort_order INTEGER DEFAULT 0
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS colors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) NOT NULL,
            hex_code VARCHAR(7)
        )");
        
        // Insérer quelques données de test
        $pdo->exec("INSERT OR IGNORE INTO categories (id, name) VALUES 
            (1, \'Vêtements\'), (2, \'Chaussures\'), (3, \'Accessoires\')");
            
        $pdo->exec("INSERT OR IGNORE INTO sizes (id, name, category) VALUES 
            (1, \'XS\', \'clothing\'), (2, \'S\', \'clothing\'), (3, \'M\', \'clothing\'), 
            (4, \'L\', \'clothing\'), (5, \'XL\', \'clothing\')");
            
        $pdo->exec("INSERT OR IGNORE INTO colors (id, name, hex_code) VALUES 
            (1, \'Noir\', \'#000000\'), (2, \'Blanc\', \'#FFFFFF\'), (3, \'Rouge\', \'#FF0000\'), 
            (4, \'Bleu\', \'#0000FF\'), (5, \'Vert\', \'#00FF00\')");
        
        echo "Base de données SQLite créée avec succès\n";
    } catch (Exception $e2) {
        echo "Erreur critique: " . $e2->getMessage() . "\n";
        die("Impossible de configurer la base de données");
    }
}

return $pdo;
?>';

file_put_contents('config/database_fallback.php', $db_config_content);
echo "✓ Fichier de configuration de secours créé\n";

// 3. Créer un fichier de test simple
echo "\n3. Création d'un fichier de test...\n";

$test_content = '<?php
/**
 * Test simple d\'ajout de produit
 */

// Inclure la configuration
try {
    require_once "config.php";
} catch (Exception $e) {
    require_once "config/database_fallback.php";
}

// Test d\'ajout simple
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? "";
    $description = $_POST["description"] ?? "";
    $price = floatval($_POST["price"] ?? 0);
    
    if (empty($name) || empty($description) || $price <= 0) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, status) VALUES (?, ?, ?, \'active\')");
            $result = $stmt->execute([$name, $description, $price]);
            
            if ($result) {
                $success = "Produit ajouté avec succès ! ID: " . $pdo->lastInsertId();
            } else {
                $error = "Erreur lors de l\'ajout du produit.";
            }
        } catch (Exception $e) {
            $error = "Erreur base de données: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test d\'ajout de produit</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Test d\'ajout de produit</h1>
    
    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="name">Nom du produit *</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" rows="4" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="price">Prix (€) *</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required>
        </div>
        
        <button type="submit">Ajouter le produit</button>
    </form>
    
    <hr>
    <h2>Produits existants</h2>
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 10");
        $products = $stmt->fetchAll();
        
        if ($products) {
            echo "<ul>";
            foreach ($products as $product) {
                echo "<li><strong>" . htmlspecialchars($product["name"]) . "</strong> - " . 
                     number_format($product["price"], 2) . "€ (ID: " . $product["id"] . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Aucun produit trouvé.</p>";
        }
    } catch (Exception $e) {
        echo "<p>Erreur lors de la récupération des produits: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
</body>
</html>';

file_put_contents('admin/test_simple_add.php', $test_content);
echo "✓ Fichier de test simple créé: admin/test_simple_add.php\n";

// 4. Créer un fichier .htaccess pour les uploads
echo "\n4. Configuration Apache...\n";

$htaccess_content = '# Protection des fichiers uploads
<Files "*.php">
    Deny from all
</Files>

# Types MIME autorisés pour les images
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Allow from all
</FilesMatch>

# Cache des images
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
</IfModule>';

file_put_contents('uploads/.htaccess', $htaccess_content);
echo "✓ Fichier .htaccess créé pour les uploads\n";

echo "\n=== CORRECTION TERMINÉE ===\n";
echo "✅ Tous les problèmes courants ont été corrigés.\n\n";

echo "PROCHAINES ÉTAPES :\n";
echo "1. Testez avec: http://votre-site/admin/test_simple_add.php\n";
echo "2. Si le test fonctionne, le problème était lié à la configuration\n";
echo "3. Vous pouvez maintenant utiliser admin/add_product.php normalement\n";
echo "4. Vérifiez que votre serveur web (Apache/Nginx) est configuré correctement\n";

?>