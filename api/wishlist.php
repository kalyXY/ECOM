<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Only logged-in customers can use this API
if (empty($_SESSION['customer_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'auth_required']);
    exit;
}

$customerId = (int)$_SESSION['customer_id'];

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'method_not_allowed']);
    exit;
}

$input = $_POST + (json_decode(file_get_contents('php://input'), true) ?: []);
$action = $input['action'] ?? 'toggle';
$productId = (int)($input['product_id'] ?? 0);

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_product']);
    exit;
}

try {
    // Ensure product exists and active
    $stmt = $pdo->prepare('SELECT id FROM products WHERE id = :id AND status = "active"');
    $stmt->execute([':id' => $productId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'product_not_found']);
        exit;
    }

    // Check if already in wishlist
    $check = $pdo->prepare('SELECT id FROM wishlists WHERE customer_id = :cid AND product_id = :pid');
    $check->execute([':cid' => $customerId, ':pid' => $productId]);
    $exists = (bool)$check->fetch();

    if ($action === 'add' || ($action === 'toggle' && !$exists)) {
        $ins = $pdo->prepare('INSERT IGNORE INTO wishlists (customer_id, product_id, created_at) VALUES (:cid, :pid, NOW())');
        $ins->execute([':cid' => $customerId, ':pid' => $productId]);
        echo json_encode(['success' => true, 'in_wishlist' => true]);
        exit;
    }

    if ($action === 'remove' || ($action === 'toggle' && $exists)) {
        $del = $pdo->prepare('DELETE FROM wishlists WHERE customer_id = :cid AND product_id = :pid');
        $del->execute([':cid' => $customerId, ':pid' => $productId]);
        echo json_encode(['success' => true, 'in_wishlist' => false]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'invalid_action']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'server_error']);
}
?>


