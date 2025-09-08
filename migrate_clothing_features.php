<?php
require_once 'config.php';

echo "<h2>Migration des fonctionnalités mode - StyleHub</h2>";

try {
    // Table des tailles
    echo "<p>Création de la table des tailles...</p>";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sizes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(20) NOT NULL,
            category VARCHAR(50) DEFAULT 'general',
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_category (category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Table des couleurs
    echo "<p>Création de la table des couleurs...</p>";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS colors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            hex_code VARCHAR(7) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Table des variantes produits
    echo "<p>Création de la table des variantes produits...</p>";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_variants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            size_id INT DEFAULT NULL,
            color_id INT DEFAULT NULL,
            sku VARCHAR(100),
            stock INT DEFAULT 0,
            price_modifier DECIMAL(10,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (size_id) REFERENCES sizes(id) ON DELETE SET NULL,
            FOREIGN KEY (color_id) REFERENCES colors(id) ON DELETE SET NULL,
            UNIQUE KEY unique_variant (product_id, size_id, color_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Insertion des tailles standards
    echo "<p>Insertion des tailles standards...</p>";
    $pdo->exec("
        INSERT IGNORE INTO sizes (name, category, sort_order) VALUES 
        ('XS', 'vetements', 1),
        ('S', 'vetements', 2),
        ('M', 'vetements', 3),
        ('L', 'vetements', 4),
        ('XL', 'vetements', 5),
        ('XXL', 'vetements', 6),
        ('34', 'vetements', 10),
        ('36', 'vetements', 11),
        ('38', 'vetements', 12),
        ('40', 'vetements', 13),
        ('42', 'vetements', 14),
        ('44', 'vetements', 15),
        ('35', 'chaussures', 20),
        ('36', 'chaussures', 21),
        ('37', 'chaussures', 22),
        ('38', 'chaussures', 23),
        ('39', 'chaussures', 24),
        ('40', 'chaussures', 25),
        ('41', 'chaussures', 26),
        ('42', 'chaussures', 27),
        ('43', 'chaussures', 28),
        ('44', 'chaussures', 29),
        ('45', 'chaussures', 30)
    ");
    
    // Insertion des couleurs de base
    echo "<p>Insertion des couleurs de base...</p>";
    $pdo->exec("
        INSERT IGNORE INTO colors (name, hex_code) VALUES 
        ('Noir', '#000000'),
        ('Blanc', '#FFFFFF'),
        ('Gris', '#808080'),
        ('Rouge', '#FF0000'),
        ('Bleu marine', '#000080'),
        ('Bleu ciel', '#87CEEB'),
        ('Vert', '#008000'),
        ('Jaune', '#FFFF00'),
        ('Rose', '#FFC0CB'),
        ('Violet', '#800080'),
        ('Orange', '#FFA500'),
        ('Marron', '#A52A2A'),
        ('Beige', '#F5F5DC'),
        ('Bordeaux', '#800020')
    ");
    
    echo "<div style='color: green; font-weight: bold; margin-top: 20px;'>";
    echo "✅ Migration réussie ! Toutes les fonctionnalités mode ont été ajoutées.";
    echo "</div>";
    
    echo "<p><a href='admin/sizes_colors.php'>🎨 Aller à la gestion des tailles et couleurs</a></p>";
    echo "<p><a href='admin/index.php'>📊 Retour au dashboard admin</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>";
    echo "❌ Erreur lors de la migration : " . $e->getMessage();
    echo "</div>";
}
?>

<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f8f9fa;
}

h2 {
    color: #ff6900;
    border-bottom: 2px solid #ff6900;
    padding-bottom: 10px;
}

p {
    background: white;
    padding: 10px;
    border-radius: 5px;
    border-left: 4px solid #ff6900;
}

a {
    color: #ff6900;
    text-decoration: none;
    font-weight: 500;
}

a:hover {
    text-decoration: underline;
}
</style>