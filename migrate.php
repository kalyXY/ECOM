<?php
require_once 'config.php';

echo "<h2>Migration de la base de données</h2>";

try {
    // Vérifier si la colonne stock existe
    $stockExists = false;
    try {
        $pdo->query('SELECT stock FROM products LIMIT 1');
        $stockExists = true;
        echo "<p>✅ La colonne 'stock' existe déjà.</p>";
    } catch (PDOException $e) {
        echo "<p>❌ La colonne 'stock' n'existe pas. Ajout en cours...</p>";
    }

    // Ajouter la colonne stock si elle n'existe pas
    if (!$stockExists) {
        $pdo->exec('ALTER TABLE products ADD COLUMN stock INT DEFAULT 0 AFTER price');
        echo "<p>✅ Colonne 'stock' ajoutée avec succès.</p>";
    }

    // Vérifier si la colonne status existe
    $statusExists = false;
    try {
        $pdo->query('SELECT status FROM products LIMIT 1');
        $statusExists = true;
        echo "<p>✅ La colonne 'status' existe déjà.</p>";
    } catch (PDOException $e) {
        echo "<p>❌ La colonne 'status' n'existe pas. Ajout en cours...</p>";
    }

    // Ajouter la colonne status si elle n'existe pas
    if (!$statusExists) {
        $pdo->exec("ALTER TABLE products ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER stock");
        echo "<p>✅ Colonne 'status' ajoutée avec succès.</p>";
    }

    // Vérifier si la colonne updated_at existe
    $updatedAtExists = false;
    try {
        $pdo->query('SELECT updated_at FROM products LIMIT 1');
        $updatedAtExists = true;
        echo "<p>✅ La colonne 'updated_at' existe déjà.</p>";
    } catch (PDOException $e) {
        echo "<p>❌ La colonne 'updated_at' n'existe pas. Ajout en cours...</p>";
    }

    // Ajouter la colonne updated_at si elle n'existe pas
    if (!$updatedAtExists) {
        $pdo->exec('ALTER TABLE products ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at');
        echo "<p>✅ Colonne 'updated_at' ajoutée avec succès.</p>";
    }

    // Mettre à jour les produits existants avec un stock par défaut
    if (!$stockExists) {
        $pdo->exec('UPDATE products SET stock = 10 WHERE stock = 0');
        echo "<p>✅ Stock par défaut ajouté aux produits existants.</p>";
    }

    // Afficher la structure de la table
    echo "<h3>Structure actuelle de la table products :</h3>";
    $stmt = $pdo->query('DESCRIBE products');
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h3>✅ Migration terminée avec succès !</h3>";
    echo "<p><a href='products.php'>Aller à la page des produits</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur lors de la migration : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>