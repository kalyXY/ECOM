<?php
require_once 'includes/config.php';

// Définir le titre de la page
$pageTitle = 'Mon Panier';

// Ajouter le CSS spécifique à la page
$pageStyles = '<link href="assets/css/cart-page.css" rel="stylesheet">';

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

// Debug : Afficher l'état du panier
error_log("Cart Debug - Items: " . count($items) . ", Total: $total, ItemCount: $itemCount");

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
    <!-- Notifications (succès/erreur) -->
    <?php if (!empty($message)): ?>
      <div class="alert alert-<?php echo htmlspecialchars($messageType ?: 'info'); ?> mb-4">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <!-- En-tête du panier -->

    <div class="cart-header text-center mb-4">
        <h1 class="section-title">
            <i class="fas fa-shopping-cart me-3"></i>
            Mon Panier
        </h1>
        <!-- Étapes simplifiées pour une interface plus claire -->
        <div class="small text-muted">Étape 1 sur 2 · Panier → Paiement</div>
        
        <?php if ($itemCount > 0): ?>
            <div class="cart-stats mt-4">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $itemCount; ?></div>
                            <div class="stat-label">Article<?php echo $itemCount > 1 ? 's' : ''; ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo number_format($total, 2, ',', ' '); ?> €</div>
                            <div class="stat-label">Total</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-number text-success">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="stat-label">Livraison gratuite</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (empty($items)): ?>
        <!-- État panier vide -->
        <div class="empty-cart-state">
            <div class="empty-cart-content text-center">
                <div class="empty-cart-icon mb-4">
                    <i class="fas fa-shopping-bag fa-5x text-muted"></i>
                </div>
                <h3 class="mb-3">Votre panier est vide</h3>
                <p class="text-muted mb-4 lead">
                    Découvrez nos collections et trouvez les pièces qui vous correspondent.
                </p>
                
                <!-- Actions suggestions -->
                <div class="empty-cart-actions">
                    <a href="products.php" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-tshirt me-2"></i>
                        Découvrir les produits
                    </a>
                    <a href="products.php?featured=1" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-star me-2"></i>
                        Coups de cœur
                    </a>
                </div>
                
                <!-- Catégories populaires -->
                <div class="popular-categories mt-5">
                    <h5 class="mb-3">Catégories populaires</h5>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-6 col-md-3 mb-3">
                                    <a href="products.php?gender=femme" class="category-quick-link">
                                        <i class="fas fa-female fa-2x text-pink mb-2"></i>
                                        <div>Mode Femme</div>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <a href="products.php?gender=homme" class="category-quick-link">
                                        <i class="fas fa-male fa-2x text-primary mb-2"></i>
                                        <div>Mode Homme</div>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <a href="products.php?category=accessoires" class="category-quick-link">
                                        <i class="fas fa-gem fa-2x text-warning mb-2"></i>
                                        <div>Accessoires</div>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <a href="products.php?sale=1" class="category-quick-link">
                                        <i class="fas fa-tags fa-2x text-danger mb-2"></i>
                                        <div>Promotions</div>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- CTA retour à la boutique -->
                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <a href="products.php" class="btn btn-primary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Parcourir les produits
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Contenu du panier -->
        <div class="cart-content">
            <div class="row gx-5">
                <!-- Articles du panier -->
                <div class="col-lg-8">
                    <div class="cart-items-section">
                        <!-- Actions rapides -->
                        <div class="cart-actions-bar d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded">
                            <div class="items-count">
                                <span class="fw-bold"><?php echo $itemCount; ?> article<?php echo $itemCount > 1 ? 's' : ''; ?></span>
                                <span class="text-muted ms-2">dans votre panier</span>
                            </div>
                            <div class="quick-actions">
                                <button class="btn btn-sm btn-outline-secondary me-2" onclick="updateAllQuantities()">
                                    <i class="fas fa-sync me-1"></i>Mettre à jour
                                </button>
                                <a href="?action=clear" class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir vider votre panier ?')">
                                    <i class="fas fa-trash me-1"></i>Vider le panier
                                </a>
                            </div>
                        </div>

                        <!-- Liste des articles -->
                        <form method="POST" action="?action=update" id="cartUpdateForm">
                            <div class="cart-items">
                                <?php 
                                $index = 0;
                                foreach ($items as $id => $item): 
                                ?>
                                    <div class="cart-item animate-fade-in-up" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                        <div class="cart-item-content">
                                            <!-- Image du produit -->
                                            <div class="cart-item-image">
                                                <img src="assets/images/placeholder.svg" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                     class="img-fluid rounded">
                                                <div class="image-overlay">
                                                    <a href="product.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            
                                            <!-- Informations du produit -->
                                            <div class="cart-item-info">
                                                <div class="item-header">
                                                    <h5 class="item-name">
                                                        <a href="product.php?id=<?php echo $id; ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($item['name']); ?>
                                                        </a>
                                                    </h5>
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item" 
                                                            data-id="<?php echo $id; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Détails du produit -->
                                                <div class="item-details mb-3">
                                                    <div class="item-price">
                                                        <span class="current-price"><?php echo number_format($item['price'], 2, ',', ' '); ?> €</span>
                                                        <span class="price-label">l'unité</span>
                                                    </div>
                                                    <div class="item-meta">
                                                        <span class="text-success">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            En stock
                                                        </span>
                                                        <span class="text-muted ms-3">
                                                            <i class="fas fa-truck me-1"></i>
                                                            Livraison gratuite
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Contrôles quantité et total -->
                                                <div class="item-controls">
                                                    <div class="quantity-controls">
                                                        <label class="form-label small text-muted">Quantité</label>
                                                        <div class="quantity-selector">
                                                            <button type="button" class="quantity-btn quantity-decrease" data-id="<?php echo $id; ?>">
                                                                <i class="fas fa-minus"></i>
                                                            </button>
                                                            <input type="number" 
                                                                   class="quantity-input" 
                                                                   name="quantities[<?php echo $id; ?>]" 
                                                                   value="<?php echo (int)$item['qty']; ?>" 
                                                                   min="1" 
                                                                   max="99"
                                                                   data-id="<?php echo $id; ?>"
                                                                   data-price="<?php echo $item['price']; ?>">
                                                            <button type="button" class="quantity-btn quantity-increase" data-id="<?php echo $id; ?>">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="item-total">
                                                        <label class="form-label small text-muted">Sous-total</label>
                                                        <div class="total-price" data-id="<?php echo $id; ?>">
                                                            <?php echo number_format($item['price'] * $item['qty'], 2, ',', ' '); ?> €
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                $index++;
                                endforeach; 
                                ?>
                            </div>
                        </form>
                        
                        <!-- Actions en bas de liste -->
                        <div class="cart-bottom-actions mt-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <a href="products.php" class="btn btn-outline-primary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Continuer mes achats
                                    </a>
                                </div>
                                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                    <div class="savings-info">
                                        <div class="text-success">
                                            <i class="fas fa-gift me-1"></i>
                                            Vous économisez les frais de livraison !
                                        </div>
                                        <div class="small text-muted">
                                            Livraison gratuite dès 50€ d'achat
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Rien ici: le bouton principal est dans le résumé à droite -->
                        </div>
                    </div>
                </div>
                
                <!-- Résumé du panier -->
                <div class="col-lg-4">
                    <div class="cart-summary-section">
                        <div class="cart-summary">
                            <div class="summary-header">
                                <h4 class="mb-0">
                                    <i class="fas fa-receipt me-2"></i>
                                    Résumé de la commande
                                </h4>
                            </div>
                            
                            <div class="summary-content">
                                <!-- Détail des coûts -->
                                <div class="cost-breakdown">
                                    <div class="cost-item">
                                        <span>Sous-total (<?php echo $itemCount; ?> article<?php echo $itemCount > 1 ? 's' : ''; ?>)</span>
                                        <span class="cost-value" id="subtotal"><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                                    </div>
                                    <div class="cost-item">
                                        <span>Livraison</span>
                                        <span class="cost-value text-success">
                                            <del class="text-muted me-1">4,99 €</del>
                                            Gratuite
                                        </span>
                                    </div>
                                    <div class="cost-item">
                                        <span>TVA incluse</span>
                                        <span class="cost-value"><?php echo number_format($total * 0.2, 2, ',', ' '); ?> €</span>
                                    </div>
                                </div>
                                
                                <hr class="my-3">
                                
                                <!-- Total -->
                                <div class="total-section">
                                    <div class="total-amount">
                                        <span class="total-label">Total</span>
                                        <span class="total-value" id="totalAmount"><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                                    </div>
                                    <div class="savings-badge">
                                        <i class="fas fa-piggy-bank me-1"></i>
                                        Vous économisez 4,99 € sur la livraison
                                    </div>
                                </div>
                                
                                <!-- Code promo -->
                                <div class="promo-section">
                                    <div class="promo-toggle" onclick="togglePromoCode()">
                                        <i class="fas fa-tag me-2"></i>
                                        Avez-vous un code promo ?
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="promo-form" id="promoForm" style="display: none;">
                                        <div class="input-group mt-3">
                                            <input type="text" class="form-control" placeholder="Code promo" id="promoCode">
                                            <button class="btn btn-outline-secondary" type="button" onclick="applyPromoCode()">
                                                Appliquer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Bouton d'action principal -->
                                <div class="summary-actions">
                                    <a href="checkout.php" class="btn btn-success btn-lg w-100 mb-2 checkout-btn">
                                        <i class="fas fa-credit-card me-2"></i>
                                        <strong>Procéder au paiement</strong>
                                    </a>
                                    
                                    <!-- Lien alternatif discret -->
                                    <div class="text-center mb-2">
                                        <small class="text-muted">
                                            Ou continuez vos achats et
                                            <a href="products.php" class="text-decoration-none">découvrez plus de produits</a>
                                        </small>
                                    </div>
                                    
                                    <!-- Mentions sécurité -->
                                    <div class="text-center small text-muted"><i class="fas fa-lock me-1"></i>Paiement sécurisé via Lygos</div>
                                </div>
                                
                                <!-- Garanties -->
                                <div class="guarantees">
                                    <div class="guarantee-item">
                                        <i class="fas fa-shield-alt text-success"></i>
                                        <span>Paiement 100% sécurisé</span>
                                    </div>
                                    <div class="guarantee-item">
                                        <i class="fas fa-undo text-primary"></i>
                                        <span>Retours gratuits sous 30 jours</span>
                                    </div>
                                    <div class="guarantee-item">
                                        <i class="fas fa-headset text-info"></i>
                                        <span>Service client 7j/7</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Produits recommandés -->
                        <div class="recommended-products mt-4">
                            <h5 class="mb-3">
                                <i class="fas fa-heart text-danger me-2"></i>
                                Vous pourriez aussi aimer
                            </h5>
                            <div class="recommended-items">
                                <!-- Exemple d'items recommandés -->
                                <div class="recommended-item">
                                    <img src="assets/images/placeholder.svg" alt="Produit recommandé" class="recommended-image">
                                    <div class="recommended-info">
                                        <h6 class="recommended-name">T-shirt basique</h6>
                                        <div class="recommended-price">19,99 €</div>
                                        <button class="btn btn-sm btn-outline-primary">Ajouter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Fin contenu panier -->
</div>
<?php
// Inclure le footer du site
include 'includes/footer.php';
?>

