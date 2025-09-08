<?php
/**
 * Configuration sécurisée de la base de données
 * StyleHub E-Commerce Platform
 */

// Configuration de la base de données
$config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname' => $_ENV['DB_NAME'] ?? 'stylehub_db',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]
];

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    // Configuration pour la performance
    $pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
    
} catch (PDOException $e) {
    // Log l'erreur sans exposer les détails
    error_log("Database connection failed: " . $e->getMessage());
    
    // Message générique pour l'utilisateur
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    } else {
        die("Service temporairement indisponible. Veuillez réessayer plus tard.");
    }
}

return $pdo;
?>