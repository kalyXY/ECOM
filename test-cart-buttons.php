<?php
session_start();

// Test simple pour vérifier les boutons du panier
echo "<h1>Test des boutons de panier</h1>";

// Simuler un panier avec des articles
$_SESSION['cart'] = [
    1 => ['name' => 'T-shirt Test', 'price' => 29.99, 'qty' => 1],
    2 => ['name' => 'Pantalon Test', 'price' => 59.99, 'qty' => 1]
];

$items = $_SESSION['cart'];
$total = 0;
$itemCount = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['qty'];
    $itemCount += $item['qty'];
}

echo "<p>Articles dans le panier: " . count($items) . "</p>";
echo "<p>Quantité totale: $itemCount</p>";
echo "<p>Total: " . number_format($total, 2, ',', ' ') . " €</p>";

echo "<h2>Boutons de test:</h2>";

// Bouton simple
echo '<a href="checkout.php" class="btn btn-primary btn-lg" style="display: inline-block; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px;">
    BOUTON TEST 1 - Passer la commande
</a><br>';

// Bouton avec style inline
echo '<a href="checkout.php" style="display: inline-block; padding: 20px 40px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: bold; margin: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
    🛒 BOUTON TEST 2 - Commander maintenant 🛒
</a><br>';

// Bouton Bootstrap
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
echo '<a href="checkout.php" class="btn btn-success btn-lg" style="margin: 10px; font-size: 1.2rem; padding: 15px 30px;">
    <i class="fas fa-shopping-cart"></i> BOUTON TEST 3 - Bootstrap
</a><br>';

// Test de condition PHP
if (!empty($items)) {
    echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; margin: 10px; border-radius: 5px;">
        <h3>✅ Condition PHP OK - Panier non vide</h3>
        <a href="checkout.php" style="display: inline-block; padding: 15px 30px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
            BOUTON TEST 4 - Condition réussie
        </a>
    </div>';
} else {
    echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; margin: 10px; border-radius: 5px;">
        <h3>❌ Problème - Panier vide</h3>
    </div>';
}

echo "<h3>Liens de navigation:</h3>";
echo '<a href="cart.php">← Retour au panier</a> | ';
echo '<a href="checkout.php">Aller au checkout →</a>';
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background: #f8f9fa;
}
</style>