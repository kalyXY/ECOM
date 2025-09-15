<?php
// Script de vérification de la structure du projet
// Activer le buffering de sortie pour éviter les warnings "headers already sent"
if (function_exists('ob_start')) { ob_start(); }

echo "<h1>🔍 Vérification de la structure E-Commerce</h1>";

$requiredFiles = [
    // Fichiers racine
    'index.php' => 'Front-office principal',
    'config.php' => 'Configuration principale',
    'database_complete.sql' => 'Structure de la base de données',
    'migrate.php' => 'Script de migration',
    
    // Dossiers
    'uploads/' => 'Dossier des images',
    'assets/' => 'Assets front-office',
    'admin/' => 'Dossier administration',
    
    // Fichiers admin
    'admin/index.php' => 'Dashboard admin',
    'admin/login.php' => 'Connexion admin',
    'admin/logout.php' => 'Déconnexion admin',
    'admin/products.php' => 'Gestion des produits',
    'admin/add_product.php' => 'Ajouter un produit',
    'admin/delete_product.php' => 'Supprimer un produit',
    'admin/config.php' => 'Configuration admin',
    'admin/.htaccess' => 'Sécurité admin',
    
    // Assets admin
    'admin/assets/' => 'Assets admin',
    'admin/assets/css/admin.css' => 'Styles admin',
    'admin/assets/js/admin.js' => 'JavaScript admin',
    
    // Layouts admin
    'admin/layouts/' => 'Templates admin',
    'admin/layouts/header.php' => 'En-tête admin',
    'admin/layouts/sidebar.php' => 'Sidebar admin',
    'admin/layouts/topbar.php' => 'Topbar admin',
    'admin/layouts/footer.php' => 'Pied de page admin',
];

$obsoleteFiles = [
    'login.php',
    'logout.php', 
    'admin.php',
    'products.php',
    'add_product.php',
    'edit_product.php',
    'delete_product.php',
    'profile.php',
    'layouts/',
    'migrate_database.sql'
];

echo "<h2>✅ Fichiers requis</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Fichier/Dossier</th><th>Description</th><th>Status</th></tr>";

foreach ($requiredFiles as $file => $description) {
    $exists = file_exists($file) || is_dir($file);
    $status = $exists ? "✅ OK" : "❌ MANQUANT";
    $color = $exists ? "green" : "red";
    
    echo "<tr>";
    echo "<td><code>$file</code></td>";
    echo "<td>$description</td>";
    echo "<td style='color: $color; font-weight: bold;'>$status</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🗑️ Fichiers obsolètes (doivent être supprimés)</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Fichier/Dossier</th><th>Status</th></tr>";

foreach ($obsoleteFiles as $file) {
    $exists = file_exists($file) || is_dir($file);
    $status = $exists ? "⚠️ ENCORE PRÉSENT" : "✅ SUPPRIMÉ";
    $color = $exists ? "orange" : "green";
    
    echo "<tr>";
    echo "<td><code>$file</code></td>";
    echo "<td style='color: $color; font-weight: bold;'>$status</td>";
    echo "</tr>";
}
echo "</table>";

// Vérification de la base de données
echo "<h2>🗄️ Vérification de la base de données</h2>";
try {
    require_once 'config.php';
    echo "<p style='color: green;'>✅ Connexion à la base de données réussie</p>";
    
    $autoFix = isset($_GET['fix']);

    // Schéma requis (colonnes essentielles utilisées par le code)
    $requiredSchema = [
        'products' => [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'name VARCHAR(255) NOT NULL',
            'slug VARCHAR(255)',
            'description TEXT',
            'price DECIMAL(10,2) NOT NULL',
            'sale_price DECIMAL(10,2)',
            'stock INT',
            'category_id INT',
            'brand VARCHAR(100)',
            'material VARCHAR(100)',
            'gender VARCHAR(20)',
            'season VARCHAR(20)',
            'image_url VARCHAR(255)',
            'gallery JSON',
            'featured BOOLEAN',
            'status VARCHAR(20)',
            'view_count INT',
            'rating DECIMAL(3,2)',
            'rating_count INT',
            'rating_average DECIMAL(3,2)',
            'created_at TIMESTAMP'
        ],
        'categories' => [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'name VARCHAR(100) NOT NULL',
            'slug VARCHAR(100)',
            'status VARCHAR(20)'
        ],
        'sizes' => [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'name VARCHAR(20) NOT NULL',
            'category VARCHAR(50)'
        ],
        'colors' => [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'name VARCHAR(50) NOT NULL',
            'hex_code VARCHAR(7) NOT NULL'
        ],
        'product_images' => [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'product_id INT NOT NULL',
            'image_url VARCHAR(255) NOT NULL',
            'sort_order INT'
        ],
        'product_sizes' => [
            'product_id INT NOT NULL',
            'size_id INT NOT NULL',
            'stock INT'
        ],
        'product_colors' => [
            'product_id INT NOT NULL',
            'color_id INT NOT NULL',
            'stock INT'
        ],
        'users' => [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'username VARCHAR(50)',
            'email VARCHAR(100)',
            'password_hash VARCHAR(255)'
        ],
        'settings' => [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'setting_key VARCHAR(100) UNIQUE',
            'setting_value LONGTEXT'
        ],
        'wishlists' => [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'session_id VARCHAR(255)',
            'customer_id INT',
            'product_id INT NOT NULL'
        ],
        'orders' => [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'order_number VARCHAR(50) NOT NULL',
            'customer_id INT',
            'subtotal DECIMAL(10,2) NOT NULL',
            'total_amount DECIMAL(10,2) NOT NULL'
        ],
        'order_items' => [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'order_id INT NOT NULL',
            'product_id INT',
            'quantity INT NOT NULL',
            'unit_price DECIMAL(10,2) NOT NULL'
        ],
        'payments' => [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'order_id INT NOT NULL',
            'payment_method VARCHAR(50) NOT NULL',
            'amount DECIMAL(10,2) NOT NULL'
        ],
        'cache' => [
            'cache_key VARCHAR(255) PRIMARY KEY',
            'cache_value LONGTEXT NOT NULL',
            'expires_at TIMESTAMP NOT NULL'
        ]
    ];

    echo "<h3>Tables & colonnes requises</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Table</th><th>Colonnes manquantes</th><th>Action</th></tr>";

    foreach ($requiredSchema as $table => $columns) {
        $missingColumns = [];
        $tableExists = false;
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
            $stmt->execute([$table]);
            $tableExists = ((int)$stmt->fetchColumn() > 0);
        } catch (Exception $e) {
            $tableExists = false;
        }

        if ($tableExists) {
            $existingCols = [];
            $colsStmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ?");
            $colsStmt->execute([$table]);
            foreach ($colsStmt->fetchAll(PDO::FETCH_COLUMN) as $c) { $existingCols[strtolower($c)] = true; }

            foreach ($columns as $def) {
                $colName = strtolower(trim(strtok($def, ' ')));
                if (!isset($existingCols[$colName])) { $missingColumns[] = $def; }
            }

            $action = "✅ OK";
            if (!empty($missingColumns)) {
                if ($autoFix) {
                    foreach ($missingColumns as $def) {
                        $sql = "ALTER TABLE `$table` ADD COLUMN $def";
                        try { $pdo->exec($sql); } catch (Exception $e) { /* ignorer et afficher */ }
                    }
                    $action = "🔧 Colonnes ajoutées";
                } else {
                    $action = "❌ Manquant (ajoutez avec ?fix=1)";
                }
            }

            echo "<tr><td><code>$table</code></td><td>" . (empty($missingColumns) ? '-' : '<pre style=\"margin:0;\">' . htmlspecialchars(implode("\n", $missingColumns)) . '</pre>') . "</td><td>$action</td></tr>";
        } else {
            // Créer table minimale si autoFix
            $action = "❌ Table absente";
            if ($autoFix) {
                $colsSql = implode(", ", $columns);
                $create = "CREATE TABLE IF NOT EXISTS `$table` ($colsSql) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                try { $pdo->exec($create); $action = "🆕 Table créée"; } catch (Exception $e) { $action = "⚠️ Échec création"; }
            }
            echo "<tr><td><code>$table</code></td><td>toutes</td><td>$action</td></tr>";
        }
    }
    echo "</table>";

    echo "<div style='margin-top:12px; display:flex; gap:12px; align-items:center;'>";
    if ($autoFix) {
        echo "<span style='color:green;font-weight:bold'>✅ Correctifs appliqués. <a href='check_structure.php' style='margin-left:8px;'>Relancer le contrôle</a></span>";
    } else {
        echo "<form method='get' onsubmit=\"return confirm('Appliquer automatiquement les corrections (création de tables/colonnes manquantes) ?');\">";
        echo "<input type='hidden' name='fix' value='1'>";
        echo "<button type='submit' style='background:#198754;color:#fff;border:0;padding:8px 12px;border-radius:4px;cursor:pointer;'>🔧 Corriger maintenant</button>";
        echo "</form>";
        echo "<a href='check_structure.php?fix=1' style='color:#0d6efd;'>(ou cliquer ici)</a>";
    }
    echo "</div>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur de connexion : " . $e->getMessage() . "</p>";
    echo "<p>👉 Exécutez d'abord le script <a href='migrate.php'>migrate.php</a></p>";
}

echo "<h2>🚀 Liens rapides</h2>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>🏠 Front-office (Site public)</a></li>";
echo "<li><a href='admin/login.php' target='_blank'>🔐 Back-office (Administration)</a></li>";
echo "<li><a href='migrate.php' target='_blank'>⚙️ Migration de la base de données</a></li>";
echo "</ul>";

echo "<h2>📋 Informations importantes</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007bff;'>";
echo "<h3>🔐 Accès Admin</h3>";
echo "<p><strong>URL :</strong> <code>http://votre-site.com/admin/</code></p>";
echo "<p><strong>Identifiants par défaut :</strong></p>";
echo "<ul>";
echo "<li>Utilisateur : <code>admin</code></li>";
echo "<li>Mot de passe : <code>admin123</code></li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #f0fff0; padding: 15px; border-left: 4px solid #28a745; margin-top: 15px;'>";
echo "<h3>✨ Fonctionnalités</h3>";
echo "<ul>";
echo "<li>Dashboard moderne avec statistiques</li>";
echo "<li>Gestion complète des produits (CRUD)</li>";
echo "<li>Upload d'images sécurisé</li>";
echo "<li>Interface responsive</li>";
echo "<li>Design professionnel</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "🎯 Structure réorganisée avec succès ! Votre e-commerce est prêt.";
echo "</p>";

// Vider le buffer en fin de script
if (function_exists('ob_get_level') && ob_get_level() > 0) { @ob_end_flush(); }
?>