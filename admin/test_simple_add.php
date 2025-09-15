<?php
/**
 * Test simple d'ajout de produit
 */

// Inclure la configuration
try {
    require_once "config.php";
} catch (Exception $e) {
    require_once "config/database_fallback.php";
}

// Test d'ajout simple
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? "";
    $description = $_POST["description"] ?? "";
    $price = floatval($_POST["price"] ?? 0);
    
    if (empty($name) || empty($description) || $price <= 0) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, status) VALUES (?, ?, ?, 'active')");
            $result = $stmt->execute([$name, $description, $price]);
            
            if ($result) {
                $success = "Produit ajouté avec succès ! ID: " . $pdo->lastInsertId();
            } else {
                $error = "Erreur lors de l'ajout du produit.";
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
    <title>Test d'ajout de produit</title>
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
    <h1>Test d'ajout de produit</h1>
    
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
</html>