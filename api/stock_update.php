<?php
/**
 * API de mise à jour des stocks en temps réel
 * StyleHub E-Commerce Platform
 */

require_once '../config/bootstrap.php';
require_once '../models/Product.php';

header('Content-Type: application/json');

// Vérifier l'authentification admin
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    App::jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Vérifier le token CSRF
if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    App::jsonResponse(['error' => 'Token CSRF invalide'], 403);
}

$productId = Security::sanitizeInput($_POST['product_id'] ?? '', 'int');
$newStock = Security::sanitizeInput($_POST['stock'] ?? '', 'int');
$operation = Security::sanitizeInput($_POST['operation'] ?? 'set'); // set, increment, decrement

if (!$productId || $newStock < 0) {
    App::jsonResponse(['error' => 'Données invalides'], 400);
}

$productModel = new Product($pdo);

try {
    $result = $productModel->updateStock($productId, $newStock, $operation);
    
    if ($result['success']) {
        // Log de l'action
        Security::logAction('stock_updated', [
            'product_id' => $productId,
            'old_stock' => $result['old_stock'] ?? null,
            'new_stock' => $result['new_stock'],
            'operation' => $operation
        ]);
        
        // Obtenir les informations du produit
        $product = $productModel->getById($productId);
        
        App::jsonResponse([
            'success' => true,
            'message' => 'Stock mis à jour avec succès',
            'product' => [
                'id' => $productId,
                'name' => $product['name'],
                'stock' => $result['new_stock'],
                'status' => $product['status']
            ]
        ]);
    } else {
        App::jsonResponse(['error' => $result['error']], 400);
    }
    
} catch (Exception $e) {
    error_log("Error updating stock: " . $e->getMessage());
    App::jsonResponse(['error' => 'Erreur serveur'], 500);
}
?>