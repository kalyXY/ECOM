<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/lygos.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'method_not_allowed']);
    exit;
}

// Must be logged as customer and have a pending order id
if (empty($_SESSION['customer_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'auth_required']);
    exit;
}

$orderId = (int)($_POST['order_id'] ?? 0);
if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_order_id']);
    exit;
}

// Load order
try {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id AND customer_id = :cid LIMIT 1');
    $stmt->execute([':id' => $orderId, ':cid' => (int)$_SESSION['customer_id']]);
    $order = $stmt->fetch();
    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'order_not_found']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'db_error']);
    exit;
}

// Build Lygos request
if (empty($LYGOS_API_KEY)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'lygos_api_key_missing']);
    exit;
}

$amountCents = (int)round(((float)$order['total_amount']) * 100);
$payload = [
    'amount' => $amountCents,
    'shop_name' => (isset($siteSettings['site_name']) ? $siteSettings['site_name'] : 'StyleHub Shop'),
    'message' => 'Commande #' . ($order['order_number'] ?? $orderId),
    'success_url' => app_url('success.php?order_id=' . $orderId),
    'failure_url' => app_url('fail.php?order_id=' . $orderId),
    'order_id' => (string)$orderId,
];

$resp = http_post_json($LYGOS_API_BASE . '/gateway', ['api-key: ' . $LYGOS_API_KEY], $payload);

// Logger les détails pour le débogage
error_log("Lygos API Gateway Request - Order ID: $orderId, Amount: $amountCents, Status: " . $resp['status']);
if ($resp['error']) {
    error_log("Lygos API Gateway cURL Error: " . $resp['error']);
}

if ($resp['error']) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'gateway_error', 'details' => $resp['error']]);
    exit;
}

$data = json_decode($resp['body'], true);
if ($resp['status'] >= 200 && $resp['status'] < 300 && !empty($data['payment_url'])) {
    error_log("Lygos API Gateway Success - Payment URL generated for order $orderId");
    echo json_encode(['success' => true, 'payment_url' => $data['payment_url']]);
} else {
    $apiError = $data['error'] ?? 'unknown_error';
    error_log("Lygos API Gateway Error - Status: " . $resp['status'] . ", Error: $apiError, Response: " . $resp['body']);
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $apiError, 'status_code' => $resp['status']]);
}


