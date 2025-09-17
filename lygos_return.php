<?php
require_once __DIR__ . '/config/bootstrap.php';

$status = $_GET['status'] ?? 'failure';
$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId > 0) {
    try {
        if ($status === 'success') {
            $pdo->prepare("UPDATE orders SET payment_status = 'paid', status = 'processing' WHERE id = :id")
                ->execute([':id' => $orderId]);
            $_SESSION['message'] = 'Paiement confirmé. Merci pour votre commande !';
            $_SESSION['message_type'] = 'success';
        } else {
            $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = :id")
                ->execute([':id' => $orderId]);
            $_SESSION['message'] = 'Le paiement a été annulé ou a échoué.';
            $_SESSION['message_type'] = 'danger';
        }
    } catch (Exception $e) {}
}

header('Location: orders.php' . ($orderId ? ('?id=' . $orderId) : ''));
exit;
?>


