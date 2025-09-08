<?php
require_once 'includes/config.php';

// Calculer le nombre total d'articles dans le panier
$count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item) && isset($item['qty'])) {
            $count += (int)$item['qty'];
        } else {
            $count += (int)$item;
        }
    }
}

// Retourner la réponse JSON
header('Content-Type: application/json');
echo json_encode(['count' => $count]);
?>