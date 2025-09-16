<?php
require_once 'includes/config.php';

// Ensure customer session (from profile.php flow)
if (empty($_SESSION['customer_id'])) {
  header('Location: profile.php');
  exit;
}

$customerId = (int)$_SESSION['customer_id'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch a single order with items
if ($orderId > 0) {
  try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id AND customer_id = :cid LIMIT 1");
    $stmt->execute([':id' => $orderId, ':cid' => $customerId]);
    $order = $stmt->fetch();
    if (!$order) { header('Location: orders.php'); exit; }

    $itemsStmt = $pdo->prepare("SELECT oi.*, p.image_url FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = :oid ORDER BY oi.id ASC");
    $itemsStmt->execute([':oid' => $orderId]);
    $items = $itemsStmt->fetchAll();
  } catch (Exception $e) {
    $order = null; $items = [];
  }
}

// Fetch list of orders
if ($orderId === 0) {
  try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = :cid ORDER BY created_at DESC");
    $stmt->execute([':cid' => $customerId]);
    $orders = $stmt->fetchAll();
  } catch (Exception $e) {
    $orders = [];
  }
}

$pageTitle = $orderId > 0 ? 'Commande #' . ($order['order_number'] ?? $orderId) : 'Mes commandes';
include 'includes/header.php';
?>

<div class="bg-light py-3">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Accueil</a></li>
        <li class="breadcrumb-item"><a href="profile.php" class="text-decoration-none">Mon compte</a></li>
        <?php if ($orderId > 0): ?>
          <li class="breadcrumb-item"><a href="orders.php" class="text-decoration-none">Mes commandes</a></li>
          <li class="breadcrumb-item active" aria-current="page">Commande #<?php echo htmlspecialchars($order['order_number'] ?? (string)$orderId); ?></li>
        <?php else: ?>
          <li class="breadcrumb-item active" aria-current="page">Mes commandes</li>
        <?php endif; ?>
      </ol>
    </nav>
  </div>
</div>

<div class="container py-5">
  <?php if ($orderId === 0): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3 mb-1"><i class="fas fa-box me-2"></i>Mes commandes</h1>
        <p class="text-muted mb-0">Historique de vos achats</p>
      </div>
    </div>

    <?php if (empty($orders)): ?>
      <div class="text-center text-muted py-5">
        <i class="fas fa-box-open fa-3x mb-3"></i>
        <div>Aucune commande pour le moment.</div>
        <a href="products.php" class="btn btn-primary btn-sm mt-3"><i class="fas fa-tshirt me-1"></i>Commencer vos achats</div></a>
      </div>
    <?php else: ?>
      <div class="card shadow-sm">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Paiement</th>
                <th>Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $o): ?>
                <tr>
                  <td><?php echo htmlspecialchars($o['order_number'] ?? ('ORD-' . $o['id'])); ?></td>
                  <td><?php echo htmlspecialchars($o['created_at']); ?></td>
                  <td><span class="badge bg-secondary"><?php echo htmlspecialchars($o['status']); ?></span></td>
                  <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($o['payment_status']); ?></span></td>
                  <td class="fw-bold text-primary"><?php echo number_format((float)$o['total_amount'], 2, ',', ' '); ?> €</td>
                  <td><a href="orders.php?id=<?php echo (int)$o['id']; ?>" class="btn btn-sm btn-outline-primary">Détails</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

  <?php else: ?>
    <div class="mb-4 d-flex justify-content-between align-items-center">
      <div>
        <h1 class="h4 mb-1">Commande #<?php echo htmlspecialchars($order['order_number'] ?? (string)$orderId); ?></h1>
        <div class="text-muted">Passée le <?php echo htmlspecialchars($order['created_at']); ?> • Statut: <span class="badge bg-secondary"><?php echo htmlspecialchars($order['status']); ?></span></div>
      </div>
      <a href="orders.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-header"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Articles</h6></div>
          <div class="card-body p-0">
            <?php if (empty($items)): ?>
              <div class="text-center text-muted py-4">Aucun article trouvé.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Produit</th>
                      <th class="text-center">Qté</th>
                      <th class="text-end">Prix</th>
                      <th class="text-end">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($items as $it): $line = (float)$it['total_price'] ?: ((float)$it['unit_price'] * (int)$it['quantity']); ?>
                      <tr>
                        <td>
                          <div class="d-flex align-items-center gap-3">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                              <?php if (!empty($it['image_url']) && file_exists($it['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($it['image_url']); ?>" alt="" style="max-width:60px; max-height:60px; object-fit:cover;">
                              <?php else: ?>
                                <i class="fas fa-image text-muted"></i>
                              <?php endif; ?>
                            </div>
                            <div>
                              <div class="fw-semibold"><?php echo htmlspecialchars($it['product_name'] ?? ('Produit #' . $it['product_id'])); ?></div>
                              <div class="text-muted small"><?php echo htmlspecialchars($it['variant_info'] ?? ''); ?></div>
                            </div>
                          </div>
                        </td>
                        <td class="text-center"><?php echo (int)$it['quantity']; ?></td>
                        <td class="text-end"><?php echo number_format((float)$it['unit_price'], 2, ',', ' '); ?> €</td>
                        <td class="text-end fw-bold"><?php echo number_format($line, 2, ',', ' '); ?> €</td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card shadow-sm">
          <div class="card-header"><h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Résumé</h6></div>
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2"><span>Sous-total</span><span class="fw-semibold"><?php echo number_format((float)$order['subtotal'], 2, ',', ' '); ?> €</span></div>
            <div class="d-flex justify-content-between mb-2"><span>TVA</span><span class="fw-semibold"><?php echo number_format((float)$order['tax_amount'], 2, ',', ' '); ?> €</span></div>
            <div class="d-flex justify-content-between mb-2"><span>Livraison</span><span class="fw-semibold"><?php echo number_format((float)$order['shipping_amount'], 2, ',', ' '); ?> €</span></div>
            <div class="d-flex justify-content-between text-success mb-2"><span>Remise</span><span class="fw-semibold">- <?php echo number_format((float)$order['discount_amount'], 2, ',', ' '); ?> €</span></div>
            <hr>
            <div class="d-flex justify-content-between"><span class="h6 mb-0">Total</span><span class="h5 text-primary mb-0 fw-bold"><?php echo number_format((float)$order['total_amount'], 2, ',', ' '); ?> €</span></div>
          </div>
        </div>

        <div class="card shadow-sm mt-4">
          <div class="card-header"><h6 class="mb-0"><i class="fas fa-truck me-2"></i>Livraison</h6></div>
          <div class="card-body">
            <div class="small text-muted">Méthode: <?php echo htmlspecialchars($order['shipping_method'] ?? '—'); ?></div>
            <div class="small text-muted">Suivi: <?php echo htmlspecialchars($order['tracking_number'] ?? '—'); ?></div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>


