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
  <!-- En-tête de page -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h1 class="h2 mb-2">
            <i class="fas fa-shopping-cart text-primary me-2"></i>
            Mon Panier
          </h1>
          <p class="text-muted mb-0">
            <?php if ($itemCount > 0): ?>
              <?php echo $itemCount; ?> article<?php echo $itemCount > 1 ? 's' : ''; ?> dans votre panier
            <?php else: ?>
              Votre panier est vide
            <?php endif; ?>
          </p>
        </div>
        <?php if (!empty($items)): ?>
          <div class="text-end">
            <div class="h4 mb-0 text-primary">
              Total: <?php echo number_format($total, 2, ',', ' '); ?> €
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Messages -->
  <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
      <i class="fas fa-info-circle me-2"></i>
      <?php echo htmlspecialchars($message); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <!-- Contenu principal -->
  <?php if (empty($items)): ?>
    <!-- Panier vide -->
    <div class="row justify-content-center">
      <div class="col-md-6 text-center">
        <div class="py-5">
          <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
          <h3 class="h4 mb-3">Votre panier est vide</h3>
          <p class="text-muted mb-4">
            Découvrez notre collection de vêtements tendance et ajoutez vos articles préférés à votre panier.
          </p>
          <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
            <a href="index.php" class="btn btn-primary">
              <i class="fas fa-home me-2"></i>Retour à l'accueil
            </a>
            <a href="products.php" class="btn btn-outline-primary">
              <i class="fas fa-tshirt me-2"></i>Voir nos produits
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <!-- Panier avec articles -->
    <form method="POST" action="?action=update">
      <div class="row">
        <div class="col-lg-8">
          <!-- Articles du panier -->
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                Articles dans votre panier (<?php echo $itemCount; ?>)
              </h5>
            </div>
            <div class="card-body p-0">
              <?php foreach ($items as $id => $item): 
                $lineTotal = $item['price'] * $item['qty']; 
              ?>
                <div class="cart-item border-bottom p-4">
                  <div class="row align-items-center">
                    <!-- Image produit (placeholder) -->
                    <div class="col-md-2 col-3">
                      <div class="product-image bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                        <i class="fas fa-image fa-2x text-muted"></i>
                      </div>
                    </div>
                    
                    <!-- Informations produit -->
                    <div class="col-md-4 col-9">
                      <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                      <p class="text-muted small mb-0">Référence: #<?php echo $id; ?></p>
                      <div class="text-primary fw-bold">
                        <?php echo number_format($item['price'], 2, ',', ' '); ?> €
                      </div>
                    </div>
                    
                    <!-- Quantité -->
                    <div class="col-md-3 col-6">
                      <label class="form-label small text-muted">Quantité</label>
                      <div class="input-group input-group-sm" style="max-width: 120px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty(<?php echo $id; ?>)">
                          <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="form-control text-center" 
                               name="quantities[<?php echo $id; ?>]" 
                               value="<?php echo (int)$item['qty']; ?>" 
                               min="1" max="99"
                               id="qty-<?php echo $id; ?>">
                        <button class="btn btn-outline-secondary" type="button" onclick="increaseQty(<?php echo $id; ?>)">
                          <i class="fas fa-plus"></i>
                        </button>
                      </div>
                    </div>
                    
                    <!-- Total ligne -->
                    <div class="col-md-2 col-4 text-end">
                      <div class="fw-bold text-dark">
                        <?php echo number_format($lineTotal, 2, ',', ' '); ?> €
                      </div>
                      <a href="?action=remove&id=<?php echo (int)$id; ?>" 
                         class="btn btn-sm btn-outline-danger mt-2"
                         onclick="return confirm('Supprimer cet article du panier ?');"
                         title="Supprimer">
                        <i class="fas fa-trash"></i>
                      </a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            
            <!-- Actions panier -->
            <div class="card-footer bg-light d-flex justify-content-between align-items-center">
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm">
                  <i class="fas fa-sync me-1"></i>Mettre à jour
                </button>
                <a href="?action=clear" 
                   class="btn btn-outline-danger btn-sm"
                   onclick="return confirm('Êtes-vous sûr de vouloir vider votre panier ?');">
                  <i class="fas fa-trash me-1"></i>Vider le panier
                </a>
              </div>
              <a href="products.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Continuer mes achats
              </a>
            </div>
          </div>
        </div>
        
        <div class="col-lg-4">
          <!-- Résumé de commande -->
          <div class="card shadow-sm position-sticky" style="top: 100px;">
            <div class="card-header bg-primary text-white">
              <h5 class="card-title mb-0">
                <i class="fas fa-calculator me-2"></i>
                Résumé de la commande
              </h5>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between mb-2">
                <span>Sous-total (<?php echo $itemCount; ?> articles)</span>
                <span class="fw-bold"><?php echo number_format($total, 2, ',', ' '); ?> €</span>
              </div>
              
              <div class="d-flex justify-content-between mb-2 text-success">
                <span><i class="fas fa-truck me-1"></i>Livraison</span>
                <span class="fw-bold">Gratuite</span>
              </div>
              
              <hr>
              
              <div class="d-flex justify-content-between mb-3">
                <span class="h6">Total</span>
                <span class="h5 text-primary fw-bold"><?php echo number_format($total, 2, ',', ' '); ?> €</span>
              </div>
              
              <!-- Code promo -->
              <div class="mb-3">
                <label class="form-label small">Code promo</label>
                <div class="input-group input-group-sm">
                  <input type="text" class="form-control" placeholder="Entrez votre code">
                  <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-check"></i>
                  </button>
                </div>
              </div>
              
              <div class="d-grid gap-2">
                <button type="button" class="btn btn-success btn-lg" onclick="proceedToCheckout()">
                  <i class="fas fa-lock me-2"></i>
                  Passer la commande
                </button>
                <small class="text-muted text-center">
                  <i class="fas fa-shield-alt me-1"></i>
                  Paiement 100% sécurisé
                </small>
              </div>
            </div>
          </div>
          
          <!-- Livraison info -->
          <div class="card shadow-sm mt-4">
            <div class="card-body">
              <h6 class="card-title">
                <i class="fas fa-info-circle text-primary me-2"></i>
                Informations livraison
              </h6>
              <ul class="list-unstyled small mb-0">
                <li class="mb-2">
                  <i class="fas fa-truck text-success me-2"></i>
                  Livraison gratuite dès 50€
                </li>
                <li class="mb-2">
                  <i class="fas fa-clock text-info me-2"></i>
                  Livraison sous 2-3 jours ouvrés
                </li>
                <li class="mb-0">
                  <i class="fas fa-undo text-warning me-2"></i>
                  Retours gratuits sous 30 jours
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </form>
    
    <!-- Produits recommandés -->
    <div class="mt-5">
      <h4 class="mb-4">
        <i class="fas fa-heart text-danger me-2"></i>
        Vous pourriez aussi aimer
      </h4>
      <div class="row">
        <?php for ($i = 1; $i <= 4; $i++): ?>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
              <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                <i class="fas fa-image fa-3x text-muted"></i>
              </div>
              <div class="card-body">
                <h6 class="card-title">Produit recommandé <?php echo $i; ?></h6>
                <p class="text-muted small">Description du produit...</p>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="h6 text-primary mb-0">29,99 €</span>
                  <button class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
<script>
// Fonctions JavaScript pour le panier
function decreaseQty(id) {
  const input = document.getElementById('qty-' + id);
  const currentValue = parseInt(input.value);
  if (currentValue > 1) {
    input.value = currentValue - 1;
  }
}

function increaseQty(id) {
  const input = document.getElementById('qty-' + id);
  const currentValue = parseInt(input.value);
  if (currentValue < 99) {
    input.value = currentValue + 1;
  }
}

function proceedToCheckout() {
  // Ici vous pouvez rediriger vers la page de checkout
  alert('Redirection vers la page de paiement...\n(Fonctionnalité à implémenter)');
  // window.location.href = 'checkout.php';
}

// Mettre à jour le compteur du panier
document.addEventListener('DOMContentLoaded', function() {
  if (typeof updateCartCount === 'function') {
    updateCartCount();
  }
});
</script>

<?php
// Scripts spécifiques à la page panier
$pageScripts = '<script>
  // Auto-save des quantités
  document.querySelectorAll(\'input[name^="quantities"]\').forEach(function(input) {
    input.addEventListener("change", function() {
      // Optionnel: auto-save après changement de quantité
      console.log("Quantité changée pour l\'article " + this.name + ": " + this.value);
    });
  });
</script>';

// Inclure le footer du site
include 'includes/footer.php';
?>
