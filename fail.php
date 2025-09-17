<?php
require_once __DIR__ . '/config/bootstrap.php';

$orderId = (int)($_GET['order_id'] ?? 0);
if ($orderId > 0) {
    try {
        $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = :id")
            ->execute([':id' => $orderId]);
    } catch (Exception $e) {}
}

$pageTitle = 'Paiement échoué';
include __DIR__ . '/includes/header.php';
?>

<section class="py-5">
  <div class="container">
    <div class="alert alert-danger">
      <i class="fas fa-times-circle me-2"></i>Le paiement a échoué ou a été annulé.
    </div>
    <a href="orders.php<?php echo $orderId ? ('?id=' . $orderId) : ''; ?>" class="btn btn-outline-secondary">Voir ma commande</a>
    <a href="cart.php" class="btn btn-primary ms-2">Retourner au panier</a>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

