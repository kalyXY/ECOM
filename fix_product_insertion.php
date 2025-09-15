<?php
// Script pour corriger l'insertion des produits et créer les tables manquantes
require_once 'config.php';

echo "<h2>Correction du Système de Produits</h2>";

try {
    // 1. Créer les tables manquantes
    echo "<p>Création des tables manquantes...</p>";
    
    // Table pour les images de produits
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX idx_product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Table pour les tailles
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sizes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(20) NOT NULL UNIQUE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Table pour les tailles de produits
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_sizes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            size_id INT NOT NULL,
            stock INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (size_id) REFERENCES sizes(id) ON DELETE CASCADE,
            UNIQUE KEY unique_product_size (product_id, size_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Table pour les couleurs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS colors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            hex_code VARCHAR(7),
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Table pour les couleurs de produits
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_colors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            color_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (color_id) REFERENCES colors(id) ON DELETE CASCADE,
            UNIQUE KEY unique_product_color (product_id, color_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    echo "<p>✅ Tables créées avec succès</p>";
    
    // 2. Insérer les tailles par défaut
    echo "<p>Insertion des tailles par défaut...</p>";
    
    $sizes = [
        ['XS', 1], ['S', 2], ['M', 3], ['L', 4], ['XL', 5], ['XXL', 6],
        ['34', 10], ['36', 11], ['38', 12], ['40', 13], ['42', 14], ['44', 15], ['46', 16], ['48', 17]
    ];
    
    $sizeStmt = $pdo->prepare("INSERT IGNORE INTO sizes (name, sort_order) VALUES (?, ?)");
    foreach ($sizes as $size) {
        $sizeStmt->execute($size);
    }
    
    echo "<p>✅ Tailles insérées</p>";
    
    // 3. Insérer les couleurs par défaut
    echo "<p>Insertion des couleurs par défaut...</p>";
    
    $colors = [
        ['Noir', '#000000', 1],
        ['Blanc', '#FFFFFF', 2],
        ['Gris', '#808080', 3],
        ['Bleu', '#0000FF', 4],
        ['Rouge', '#FF0000', 5],
        ['Vert', '#008000', 6],
        ['Jaune', '#FFFF00', 7],
        ['Rose', '#FFC0CB', 8],
        ['Violet', '#800080', 9],
        ['Orange', '#FFA500', 10],
        ['Marron', '#A52A2A', 11],
        ['Beige', '#F5F5DC', 12]
    ];
    
    $colorStmt = $pdo->prepare("INSERT IGNORE INTO colors (name, hex_code) VALUES (?, ?)");
    foreach ($colors as $color) {
        $colorStmt->execute([$color[0], $color[1]]); // Ignorer sort_order
    }
    
    echo "<p>✅ Couleurs insérées</p>";
    
    // 4. Vérifier la structure de la table products
    echo "<p>Vérification de la table products...</p>";
    
    $columns = $pdo->query("DESCRIBE products")->fetchAll(PDO::FETCH_COLUMN);
    $requiredColumns = ['sale_price', 'sku', 'brand', 'material', 'gender', 'season', 'gallery', 'tags', 'featured'];
    
    foreach ($requiredColumns as $column) {
        if (!in_array($column, $columns)) {
            echo "<p>Ajout de la colonne $column...</p>";
            
            switch ($column) {
                case 'sale_price':
                    $pdo->exec("ALTER TABLE products ADD COLUMN sale_price DECIMAL(10,2) DEFAULT NULL");
                    break;
                case 'sku':
                    $pdo->exec("ALTER TABLE products ADD COLUMN sku VARCHAR(100) UNIQUE");
                    break;
                case 'brand':
                    $pdo->exec("ALTER TABLE products ADD COLUMN brand VARCHAR(100)");
                    break;
                case 'material':
                    $pdo->exec("ALTER TABLE products ADD COLUMN material VARCHAR(100)");
                    break;
                case 'gender':
                    $pdo->exec("ALTER TABLE products ADD COLUMN gender ENUM('homme', 'femme', 'unisexe') DEFAULT 'unisexe'");
                    break;
                case 'season':
                    $pdo->exec("ALTER TABLE products ADD COLUMN season ENUM('printemps', 'été', 'automne', 'hiver', 'toute_saison') DEFAULT 'toute_saison'");
                    break;
                case 'gallery':
                    $pdo->exec("ALTER TABLE products ADD COLUMN gallery TEXT");
                    break;
                case 'tags':
                    $pdo->exec("ALTER TABLE products ADD COLUMN tags TEXT");
                    break;
                case 'featured':
                    $pdo->exec("ALTER TABLE products ADD COLUMN featured BOOLEAN DEFAULT FALSE");
                    break;
            }
        }
    }
    
    echo "<p>✅ Structure de la table products mise à jour</p>";

    // 5. Tester l'insertion d'un produit
    echo "<p>Test d'insertion d'un produit...</p>";

    // Vérifier si la colonne slug existe
    $hasSlug = false;
    try {
        $cols = $pdo->query("DESCRIBE products")->fetchAll(PDO::FETCH_COLUMN);
        $hasSlug = in_array('slug', $cols, true);
    } catch (Exception $e) {
        $hasSlug = false;
    }

    if ($hasSlug) {
        // Générer un slug unique
        $baseSlug = 'produit-test';
        $candidate = $baseSlug . '-' . time();
        try {
            $check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
            $i = 1;
            while (true) {
                $check->execute([$candidate]);
                if ((int)$check->fetchColumn() === 0) { break; }
                $candidate = $baseSlug . '-' . time() . '-' . (++$i);
            }
        } catch (Exception $e) {}

        $testStmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, stock, status, created_at) VALUES (:name, :slug, :description, :price, :stock, 'active', NOW())");
        $testStmt->execute([
            ':name' => 'Produit Test',
            ':slug' => $candidate,
            ':description' => 'Description test',
            ':price' => 29.99,
            ':stock' => 10
        ]);
    } else {
        $testStmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, status, created_at) VALUES ('Produit Test', 'Description test', 29.99, 10, 'active', NOW())");
        $testStmt->execute();
    }

    $testId = $pdo->lastInsertId();

    echo "<p>✅ Produit test inséré avec ID: $testId</p>";

    // Supprimer le produit test
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$testId]);
    echo "<p>✅ Produit test supprimé</p>";
    
    echo "<div style='color: green; font-weight: bold; margin-top: 20px;'>✅ Correction terminée avec succès !</div>";
    echo "<p><a href='admin/add_product.php'>Tester l'ajout de produit</a> | <a href='admin/products.php'>Voir les produits</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Erreur : " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>