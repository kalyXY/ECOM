<?php
require_once __DIR__ . '/config/bootstrap.php';

$orderId = (int)($_GET['order_id'] ?? 0);
if ($orderId > 0) {
    try {
        $pdo->prepare("UPDATE orders SET payment_status = 'paid', status = 'processing' WHERE id = :id")
            ->execute([':id' => $orderId]);
    } catch (Exception $e) {}
}

$pageTitle = 'Paiement réussi';
$pageStyles = '';
include __DIR__ . '/includes/header.php';
?>

<section class="py-5">
  <div class="container">
    <div class="alert alert-success">
      <i class="fas fa-check-circle me-2"></i>Votre paiement a été confirmé. Merci pour votre commande !
    </div>
    <a href="orders.php<?php echo $orderId ? ('?id=' . $orderId) : ''; ?>" class="btn btn-primary">Voir ma commande</a>
    <a href="products.php" class="btn btn-outline-primary ms-2">Continuer mes achats</a>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

