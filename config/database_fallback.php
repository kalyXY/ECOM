<?php
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
        $dsn = "mysql:host={$default_config['host']};dbname={$default_config['dbname']};charset={$default_config['charset']}";
        $pdo = new PDO($dsn, $default_config['username'], $default_config['password'], [
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
            gender VARCHAR(20) DEFAULT 'unisexe',
            season VARCHAR(20) DEFAULT 'toute_saison',
            image_url VARCHAR(255),
            gallery TEXT,
            featured INTEGER DEFAULT 0,
            status VARCHAR(20) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            slug VARCHAR(255)
        )");
        
        // Créer les autres tables nécessaires
        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100),
            status VARCHAR(20) DEFAULT 'active'
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS sizes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) NOT NULL,
            category VARCHAR(50) DEFAULT 'clothing',
            sort_order INTEGER DEFAULT 0
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS colors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) NOT NULL,
            hex_code VARCHAR(7)
        )");
        
        // Insérer quelques données de test
        $pdo->exec("INSERT OR IGNORE INTO categories (id, name) VALUES 
            (1, 'Vêtements'), (2, 'Chaussures'), (3, 'Accessoires')");
            
        $pdo->exec("INSERT OR IGNORE INTO sizes (id, name, category) VALUES 
            (1, 'XS', 'clothing'), (2, 'S', 'clothing'), (3, 'M', 'clothing'), 
            (4, 'L', 'clothing'), (5, 'XL', 'clothing')");
            
        $pdo->exec("INSERT OR IGNORE INTO colors (id, name, hex_code) VALUES 
            (1, 'Noir', '#000000'), (2, 'Blanc', '#FFFFFF'), (3, 'Rouge', '#FF0000'), 
            (4, 'Bleu', '#0000FF'), (5, 'Vert', '#00FF00')");
        
        echo "Base de données SQLite créée avec succès\n";
    } catch (Exception $e2) {
        echo "Erreur critique: " . $e2->getMessage() . "\n";
        die("Impossible de configurer la base de données");
    }
}

return $pdo;
?>