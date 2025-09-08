<?php
require_once 'includes/config.php';

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Handle actions: add, remove, clear, update
$action = $_POST['action'] ?? $_GET['action'] ?? '';
if ($action === 'add') {
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  if ($id > 0 && $name !== '' && $price > 0) {
    if (!isset($_SESSION['cart'][$id])) {
      $_SESSION['cart'][$id] = ['name' => $name, 'price' => $price, 'qty' => 1];
    } else {
      $_SESSION['cart'][$id]['qty']++;
    }
  }
  header('Location: cart.php');
  exit;
}

if ($action === 'remove') {
  $id = (int)($_GET['id'] ?? 0);
  unset($_SESSION['cart'][$id]);
  header('Location: cart.php');
  exit;
}

if ($action === 'clear') {
  $_SESSION['cart'] = [];
  header('Location: cart.php');
  exit;
}

// Compute totals
$items = $_SESSION['cart'];
$total = 0.0;
foreach ($items as $id => $item) { $total += $item['price'] * $item['qty']; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panier | E-Commerce</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-store me-2"></i>E-Commerce</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">Produits</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <div style="height:76px"></div>

  <main class="container py-4">
    <h1 class="h4 mb-3">Votre panier</h1>
    <?php if (empty($items)): ?>
      <div class="alert alert-info">Votre panier est vide.</div>
      <a href="index.php" class="btn btn-primary"><i class="fa-solid fa-arrow-left me-1"></i>Continuer vos achats</a>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Produit</th><th>Prix</th><th>Quantité</th><th>Total</th><th class="text-end">Action</th></tr></thead>
          <tbody>
            <?php foreach ($items as $id => $item): $line = $item['price'] * $item['qty']; ?>
            <tr>
              <td class="fw-semibold"><?php echo htmlspecialchars($item['name']); ?></td>
              <td><?php echo number_format($item['price'], 2, ',', ' '); ?> €</td>
              <td><?php echo (int)$item['qty']; ?></td>
              <td class="fw-semibold"><?php echo number_format($line, 2, ',', ' '); ?> €</td>
              <td class="text-end"><a class="btn btn-sm btn-outline-danger" href="?action=remove&id=<?php echo (int)$id; ?>"><i class="fa-solid fa-trash"></i></a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="d-flex justify-content-between align-items-center">
        <a class="btn btn-outline-secondary" href="?action=clear" onclick="return confirm('Vider le panier ?');"><i class="fa-regular fa-trash-can me-1"></i>Vider le panier</a>
        <div class="h5 mb-0">Total: <span class="text-primary"><?php echo number_format($total, 2, ',', ' '); ?> €</span></div>
      </div>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/script.js"></script>
  <script>updateCartCount();</script>
</body>
</html>


