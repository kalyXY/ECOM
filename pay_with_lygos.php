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
        'shop_name' => (isset($siteSettings['site_name']) ? $siteSettings['site_name'] : 'StyleHub Shop'),
        'message' => 'Commande #' . ($order['order_number'] ?? $orderId),
        'success_url' => app_url('success.php?order_id=' . $orderId),
        'failure_url' => app_url('fail.php?order_id=' . $orderId),
        'order_id' => (string)$orderId,
    ];
    $resp = http_post_json($LYGOS_API_BASE . '/gateway', ['api-key: ' . $LYGOS_API_KEY], $payload);
    
    // Logger les détails pour le débogage
    error_log("Lygos API Request - Order ID: $orderId, Amount: $amountCents, Status: " . $resp['status']);
    if ($resp['error']) {
        error_log("Lygos API cURL Error: " . $resp['error']);
    }
    
    if ($resp['error']) {
        $errorMsg = 'Erreur de connexion au prestataire de paiement.';
    } else {
        $data = json_decode($resp['body'], true);
        if ($resp['status'] >= 200 && $resp['status'] < 300 && !empty($data['payment_url'])) {
            $paymentUrl = $data['payment_url'];
            error_log("Lygos API Success - Payment URL generated for order $orderId");
        } else {
            $apiError = $data['error'] ?? 'Erreur lors de la création du paiement.';
            $errorMsg = $apiError;
            error_log("Lygos API Error - Status: " . $resp['status'] . ", Error: $apiError, Response: " . $resp['body']);
        }
    }
}

// Redirection serveur immédiate si URL de paiement disponible
if (!empty($paymentUrl)) {
    header('Location: ' . $paymentUrl);
    exit;
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


