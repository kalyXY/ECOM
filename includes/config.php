<?php
// Configuration pour le front-office
require_once dirname(__DIR__) . '/config.php';

// Démarrer la session pour le panier
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fonctions utilitaires pour le front-office
function formatPrice($price) {
    return number_format((float)$price, 2, ',', ' ') . ' €';
}

function addToCart($productId, $quantity = 1) {
    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = 0;
    }
    $_SESSION['cart'][$productId] += $quantity;
}

function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function updateCartQuantity($productId, $quantity) {
    if ($quantity <= 0) {
        removeFromCart($productId);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function getCartTotal() {
    global $pdo;
    $total = 0;
    
    if (!empty($_SESSION['cart'])) {
        $ids = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            if (isset($products[$productId])) {
                $total += $products[$productId] * $quantity;
            }
        }
    }
    
    return $total;
}

function getCartItemCount() {
    return array_sum($_SESSION['cart']);
}

function clearCart() {
    $_SESSION['cart'] = [];
}

// Récupérer les paramètres du site
function getSiteSettings() {
    global $pdo;
    $settings = [
        'site_name' => 'StyleHub',
        'site_description' => 'Votre destination mode pour un style unique et tendance',
        'site_email' => 'contact@stylehub.fr',
        'site_phone' => '01 42 86 95 73',
        'site_address' => '25 Avenue des Champs-Élysées, 75008 Paris'
    ];
    
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        // Table settings n'existe pas, utiliser les valeurs par défaut
    }
    
    return $settings;
}
?>