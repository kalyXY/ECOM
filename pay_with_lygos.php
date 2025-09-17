<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/lygos.php';

if (empty($_SESSION['customer_id'])) {
    $_SESSION['message'] = 'Veuillez vous connecter pour payer.';
    $_SESSION['message_type'] = 'warning';
    header('Location: profile.php');
    exit;
}

$orderId = (int)($_GET['order_id'] ?? 0);
if ($orderId <= 0) { header('Location: cart.php'); exit; }

// Charger la commande
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id AND customer_id = :cid LIMIT 1');
$stmt->execute([':id' => $orderId, ':cid' => (int)$_SESSION['customer_id']]);
$order = $stmt->fetch();

$paymentUrl = null;
$errorMsg = '';

if (empty($LYGOS_API_KEY)) {
    $errorMsg = "Clé API Lygos manquante.";
} elseif (!$order) {
    $errorMsg = "Commande introuvable.";
} else {
    $amountCents = (int)round(((float)$order['total_amount']) * 100);
    $payload = [
        'amount' => $amountCents,
        'shop_name' => $siteSettings['site_name'] ?? 'Shop',
        'message' => 'Commande #' . ($order['order_number'] ?? $orderId),
        'success_url' => app_url('lygos_return.php?status=success&order_id=' . $orderId),
        'failure_url' => app_url('lygos_return.php?status=failure&order_id=' . $orderId),
        'order_id' => (string)$orderId,
    ];
    $resp = http_post_json($LYGOS_API_BASE . '/gateway', [ 'api-key: ' . $LYGOS_API_KEY ], $payload);
    if ($resp['error']) {
        $errorMsg = 'Erreur de connexion au prestataire de paiement.';
    } else {
        $data = json_decode($resp['body'], true);
        if ($resp['status'] >= 200 && $resp['status'] < 300 && !empty($data['payment_url'])) {
            $paymentUrl = $data['payment_url'];
        } else {
            $errorMsg = $data['error'] ?? 'Erreur lors de la création du paiement.';
        }
    }
}

include 'includes/header.php';
?>

<section class="py-5">
  <div class="container">
    <h1 class="h3 mb-4"><i class="fas fa-credit-card me-2"></i>Paiement</h1>
    <?php if ($paymentUrl): ?>
      <div class="alert alert-info">Redirection vers notre partenaire de paiement...</div>
      <a href="<?php echo htmlspecialchars($paymentUrl); ?>" class="btn btn-primary btn-lg">Payer maintenant</a>
      <script>
        setTimeout(function(){ window.location.href = <?php echo json_encode($paymentUrl); ?>; }, 1200);
      </script>
    <?php else: ?>
      <div class="alert alert-danger">Impossible de créer la passerelle de paiement. <?php echo htmlspecialchars($errorMsg); ?></div>
      <a href="orders.php?id=<?php echo $orderId; ?>" class="btn btn-outline-secondary">Voir la commande</a>
    <?php endif; ?>
  </div>
  </section>

<?php include 'includes/footer.php'; ?>


