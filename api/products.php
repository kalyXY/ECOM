<?php
/**
 * API REST pour les produits
 * StyleHub E-Commerce Platform
 */

require_once '../config/bootstrap.php';
require_once '../models/Product.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gérer les requêtes OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$productModel = new Product($pdo);
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Extraire l'ID du produit si présent
$productId = null;
if (isset($pathParts[2]) && is_numeric($pathParts[2])) {
    $productId = (int) $pathParts[2];
}

try {
    switch ($method) {
        case 'GET':
            if ($productId) {
                // Obtenir un produit spécifique
                $product = $productModel->getById($productId, true);
                
                if (!$product) {
                    App::jsonResponse(['error' => 'Produit non trouvé'], 404);
                }
                
                App::jsonResponse(['product' => $product]);
                
            } else {
                // Obtenir la liste des produits avec filtres
                $filters = [];
                $page = (int) ($_GET['page'] ?? 1);
                $limit = min((int) ($_GET['limit'] ?? 12), 50); // Max 50 par page
                
                // Appliquer les filtres
                if (!empty($_GET['search'])) {
                    $filters['search'] = Security::sanitizeInput($_GET['search']);
                }
                
                if (!empty($_GET['category'])) {
                    $filters['category_id'] = Security::sanitizeInput($_GET['category'], 'int');
                }
                
                if (!empty($_GET['gender'])) {
                    $filters['gender'] = Security::sanitizeInput($_GET['gender']);
                }
                
                if (!empty($_GET['brand'])) {
                    $filters['brand'] = Security::sanitizeInput($_GET['brand']);
                }
                
                if (!empty($_GET['min_price'])) {
                    $filters['min_price'] = Security::sanitizeInput($_GET['min_price'], 'float');
                }
                
                if (!empty($_GET['max_price'])) {
                    $filters['max_price'] = Security::sanitizeInput($_GET['max_price'], 'float');
                }
                
                if (!empty($_GET['sort'])) {
                    $filters['sort'] = Security::sanitizeInput($_GET['sort']);
                }
                
                if (isset($_GET['featured'])) {
                    $filters['featured'] = $_GET['featured'] === '1';
                }
                
                $result = $productModel->getAll($filters, $page, $limit);
                App::jsonResponse($result);
            }
            break;
            
        case 'POST':
            // Créer un nouveau produit (admin seulement)
            requireLogin();
            
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                App::jsonResponse(['error' => 'Token CSRF invalide'], 403);
            }
            
            $data = [
                'name' => Security::sanitizeInput($_POST['name']),
                'description' => Security::sanitizeInput($_POST['description']),
                'price' => Security::sanitizeInput($_POST['price'], 'float'),
                'sale_price' => !empty($_POST['sale_price']) ? Security::sanitizeInput($_POST['sale_price'], 'float') : null,
                'sku' => Security::sanitizeInput($_POST['sku']),
                'stock' => Security::sanitizeInput($_POST['stock'], 'int'),
                'category_id' => !empty($_POST['category_id']) ? Security::sanitizeInput($_POST['category_id'], 'int') : null,
                'brand' => Security::sanitizeInput($_POST['brand']),
                'color' => Security::sanitizeInput($_POST['color']),
                'size' => Security::sanitizeInput($_POST['size']),
                'material' => Security::sanitizeInput($_POST['material']),
                'gender' => Security::sanitizeInput($_POST['gender']),
                'season' => Security::sanitizeInput($_POST['season']),
                'image_url' => Security::sanitizeInput($_POST['image_url']),
                'gallery' => !empty($_POST['gallery']) ? json_encode($_POST['gallery']) : null,
                'tags' => !empty($_POST['tags']) ? json_encode($_POST['tags']) : null,
                'featured' => isset($_POST['featured']) ? 1 : 0,
                'status' => Security::sanitizeInput($_POST['status'])
            ];
            
            $result = $productModel->create($data);
            
            if ($result['success']) {
                App::jsonResponse([
                    'message' => 'Produit créé avec succès',
                    'product_id' => $result['id']
                ], 201);
            } else {
                App::jsonResponse(['error' => $result['error']], 400);
            }
            break;
            
        case 'PUT':
            // Mettre à jour un produit (admin seulement)
            requireLogin();
            
            if (!$productId) {
                App::jsonResponse(['error' => 'ID produit requis'], 400);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!Security::verifyCSRFToken($input['csrf_token'] ?? '')) {
                App::jsonResponse(['error' => 'Token CSRF invalide'], 403);
            }
            
            $data = [
                'name' => Security::sanitizeInput($input['name']),
                'description' => Security::sanitizeInput($input['description']),
                'price' => Security::sanitizeInput($input['price'], 'float'),
                'sale_price' => !empty($input['sale_price']) ? Security::sanitizeInput($input['sale_price'], 'float') : null,
                'sku' => Security::sanitizeInput($input['sku']),
                'stock' => Security::sanitizeInput($input['stock'], 'int'),
                'category_id' => !empty($input['category_id']) ? Security::sanitizeInput($input['category_id'], 'int') : null,
                'brand' => Security::sanitizeInput($input['brand']),
                'color' => Security::sanitizeInput($input['color']),
                'size' => Security::sanitizeInput($input['size']),
                'material' => Security::sanitizeInput($input['material']),
                'gender' => Security::sanitizeInput($input['gender']),
                'season' => Security::sanitizeInput($input['season']),
                'image_url' => Security::sanitizeInput($input['image_url']),
                'gallery' => !empty($input['gallery']) ? json_encode($input['gallery']) : null,
                'tags' => !empty($input['tags']) ? json_encode($input['tags']) : null,
                'featured' => isset($input['featured']) ? 1 : 0,
                'status' => Security::sanitizeInput($input['status'])
            ];
            
            $result = $productModel->update($productId, $data);
            
            if ($result['success']) {
                App::jsonResponse(['message' => 'Produit mis à jour avec succès']);
            } else {
                App::jsonResponse(['error' => $result['error']], 400);
            }
            break;
            
        case 'DELETE':
            // Supprimer un produit (admin seulement)
            requireLogin();
            
            if (!$productId) {
                App::jsonResponse(['error' => 'ID produit requis'], 400);
            }
            
            $result = $productModel->delete($productId);
            
            if ($result['success']) {
                App::jsonResponse(['message' => 'Produit supprimé avec succès']);
            } else {
                App::jsonResponse(['error' => $result['error']], 400);
            }
            break;
            
        default:
            App::jsonResponse(['error' => 'Méthode non autorisée'], 405);
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    App::jsonResponse(['error' => 'Erreur serveur'], 500);
}
?>