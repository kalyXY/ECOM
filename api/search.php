<?php
/**
 * API de recherche avec suggestions
 * StyleHub E-Commerce Platform
 */

require_once '../config/bootstrap.php';
require_once '../models/Product.php';

header('Content-Type: application/json');

if (!isset($_GET['q']) || strlen($_GET['q']) < 2) {
    App::jsonResponse(['suggestions' => []]);
}

$query = Security::sanitizeInput($_GET['q']);
$limit = min((int) ($_GET['limit'] ?? 8), 20);

$productModel = new Product($pdo);
$suggestions = $productModel->search($query, $limit);

// Formatter les suggestions pour l'affichage
$formattedSuggestions = array_map(function($product) {
    return [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => App::formatPrice($product['sale_price'] ?: $product['price']),
        'image' => $product['image_url'] ?: 'assets/images/no-image.png',
        'brand' => $product['brand']
    ];
}, $suggestions);

App::jsonResponse(['suggestions' => $formattedSuggestions]);
?>