<?php
require_once '../config/bootstrap.php';

header('Content-Type: application/json');

// Get the user and session identifiers
$customerId = $_SESSION['customer_id'] ?? null;
$sessionId = session_id();

// This function will be moved to a more appropriate place later, maybe a helper file.
function get_wishlist_items_count($pdo, $customerId, $sessionId) {
    if ($customerId) {
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT product_id) FROM wishlists WHERE customer_id = ?");
        $stmt->execute([$customerId]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT product_id) FROM wishlists WHERE session_id = ? AND customer_id IS NULL");
        $stmt->execute([$sessionId]);
    }
    return $stmt->fetchColumn();
}

$response = [
    'success' => false,
    'message' => 'Invalid request.',
    'action' => 'none',
    'wishlistCount' => get_wishlist_items_count($pdo, $customerId, $sessionId)
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit;
}

// Basic CSRF check, will be improved if needed
if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $response['message'] = 'Invalid security token.';
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$action = isset($data['action']) ? $data['action'] : '';

if (empty($productId) || !in_array($action, ['add', 'remove'])) {
    $response['message'] = 'Invalid product or action.';
    echo json_encode($response);
    exit;
}

try {
    if ($action === 'add') {
        // Check if item already exists
        if ($customerId) {
            $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ?");
            $stmt->execute([$customerId, $productId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE session_id = ? AND product_id = ? AND customer_id IS NULL");
            $stmt->execute([$sessionId, $productId]);
        }

        if ($stmt->fetch()) {
            $response['message'] = 'This product is already in your wishlist.';
            $response['success'] = true; // Still a success, just no change
            $response['action'] = 'already_exists';
        } else {
            $stmt = $pdo->prepare("INSERT INTO wishlists (customer_id, session_id, product_id) VALUES (?, ?, ?)");
            $stmt->execute([$customerId, $sessionId, $productId]);
            $response['success'] = true;
            $response['message'] = 'Product added to wishlist!';
            $response['action'] = 'added';
        }
    } elseif ($action === 'remove') {
        if ($customerId) {
            $stmt = $pdo->prepare("DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?");
            $stmt->execute([$customerId, $productId]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM wishlists WHERE session_id = ? AND product_id = ? AND customer_id IS NULL");
            $stmt->execute([$sessionId, $productId]);
        }

        $response['success'] = true;
        $response['message'] = 'Product removed from wishlist.';
        $response['action'] = 'removed';
    }

    $response['wishlistCount'] = get_wishlist_items_count($pdo, $customerId, $sessionId);

} catch (PDOException $e) {
    // In a real app, you would log this error, not expose it to the user
    error_log('Wishlist API Error: ' . $e->getMessage());
    $response['message'] = 'A server error occurred. Please try again later.';
}

echo json_encode($response);
?>
