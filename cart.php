<?php
require_once 'includes/config.php';

// Définir le titre de la page
$pageTitle = 'Mon Panier';

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Handle actions: add, remove, clear, update
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$message = '';
$messageType = '';

if ($action === 'add') {
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $qty = max(1, (int)($_POST['quantity'] ?? 1));
  if ($id > 0 && $name !== '' && $price > 0) {
    if (!isset($_SESSION['cart'][$id])) {
      $_SESSION['cart'][$id] = ['name' => $name, 'price' => $price, 'qty' => $qty];
      $message = 'Produit ajouté au panier avec succès !';
      $messageType = 'success';
    } else {
      $_SESSION['cart'][$id]['qty'] += $qty;
      $message = 'Quantité mise à jour dans le panier !';
      $messageType = 'info';
    }
  }
  header('Location: cart.php');
  exit;
}

if ($action === 'remove') {
  $id = (int)($_GET['id'] ?? 0);
  if (isset($_SESSION['cart'][$id])) {
    unset($_SESSION['cart'][$id]);
    $message = 'Produit supprimé du panier.';
    $messageType = 'warning';
  }
  header('Location: cart.php');
  exit;
}

if ($action === 'clear') {
  $_SESSION['cart'] = [];
  $message = 'Panier vidé avec succès.';
  $messageType = 'info';
  header('Location: cart.php');
  exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($_POST['quantities'] ?? [] as $id => $qty) {
    $id = (int)$id;
    $qty = max(0, (int)$qty);
    if ($qty === 0) {
      unset($_SESSION['cart'][$id]);
    } elseif (isset($_SESSION['cart'][$id])) {
      $_SESSION['cart'][$id]['qty'] = $qty;
    }
  }
  $message = 'Quantités mises à jour !';
  $messageType = 'success';
  header('Location: cart.php');
  exit;
}

// Compute totals
$items = $_SESSION['cart'];
$total = 0.0;
$itemCount = 0;
foreach ($items as $id => $item) { 
  $total += $item['price'] * $item['qty']; 
  $itemCount += $item['qty'];
}

// Inclure le header du site
include 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="bg-light py-3">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Accueil</a></li>
        <li class="breadcrumb-item active" aria-current="page">Mon Panier</li>
      </ol>
    </nav>
  </div>
</div>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="section-title">Mon Panier</h1>
        <p class="section-subtitle">
            <?php echo $itemCount > 0 ? "Vous avez $itemCount article(s) dans votre panier." : "Votre panier est actuellement vide."; ?>
        </p>
  </div>

  <?php if (empty($items)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-bag fa-4x text-muted mb-4"></i>
            <h4 class="mb-3">Votre panier est vide</h4>
            <p class="text-muted mb-4">Parcourez nos collections pour trouver votre bonheur.</p>
            <a href="products.php" class="btn btn-primary btn-lg">Découvrir les produits</a>
    </div>
  <?php else: ?>
        <div class="row gx-5">
            <div class="col-lg-8">
    <form method="POST" action="?action=update">
                    <div class="cart-items">
                        <?php foreach ($items as $id => $item): ?>
                            <div class="cart-item d-flex align-items-center gap-4 p-3 mb-3 border rounded">
                                <img src="assets/images/placeholder.svg" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100px; height: 100px; object-fit: cover;" class="rounded">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <span class="text-primary fw-bold"><?php echo number_format($item['price'], 2, ',', ' '); ?> €</span>
            </div>
                                <div class="quantity-selector d-flex align-items-center">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="this.nextElementSibling.stepDown(); this.form.submit()">-</button>
                                    <input type="number" class="form-control form-control-sm text-center" name="quantities[<?php echo $id; ?>]" value="<?php echo (int)$item['qty']; ?>" min="1" max="99" style="width: 60px;">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="this.previousElementSibling.stepUp(); this.form.submit()">+</button>
                      </div>
                                <div class="text-end" style="width: 100px;">
                                    <span class="fw-bold"><?php echo number_format($item['price'] * $item['qty'], 2, ',', ' '); ?> €</span>
                    </div>
                                <a href="?action=remove&id=<?php echo (int)$id; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cet article ?');"><i class="fas fa-times"></i></a>
                </div>
              <?php endforeach; ?>
            </div>
                </form>
        </div>
        <div class="col-lg-4">
                <div class="cart-summary p-4 rounded shadow-sm bg-light position-sticky" style="top: 100px;">
                    <h4 class="mb-4">Résumé</h4>
              <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total</span>
                        <span><?php echo number_format($total, 2, ',', ' '); ?> €</span>
              </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Livraison</span>
                        <span class="text-success">Gratuite</span>
              </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold h5">
                        <span>Total</span>
                        <span><?php echo number_format($total, 2, ',', ' '); ?> €</span>
              </div>
                    <div class="d-grid mt-4">
                        <a href="checkout.php" class="btn btn-primary btn-lg">Passer la commande</a>
                </div>
              </div>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php
// Inclure le footer du site
include 'includes/footer.php';
?>
<script src="assets/js/cart-page.js"></script>
