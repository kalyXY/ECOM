<?php
require_once 'includes/config.php';

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$productId = (int)$_GET['id'];

// Récupérer le produit
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id AND status = 'active'");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit();
}

// Récupérer la galerie d'images
$images = [];
try {
    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = :pid ORDER BY sort_order ASC, id ASC");
    $stmt->execute([':pid' => $productId]);
    $images = array_column($stmt->fetchAll(), 'image_url');
} catch (Exception $e) {}

// Récupérer les tailles disponibles
$availableSizes = [];
try {
    $stmt = $pdo->prepare("SELECT s.id, s.name, ps.stock FROM product_sizes ps JOIN sizes s ON ps.size_id = s.id WHERE ps.product_id = :pid ORDER BY s.sort_order ASC, s.name ASC");
    $stmt->execute([':pid' => $productId]);
    $availableSizes = $stmt->fetchAll();
} catch (Exception $e) {}

// Récupérer des produits similaires (même catégorie ou prix similaire)
$similarProducts = [];
try {
    $priceMin = $product['price'] * 0.7;
    $priceMax = $product['price'] * 1.3;
    
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE id != :id 
        AND status = 'active' 
        AND price BETWEEN :price_min AND :price_max
        ORDER BY RAND() 
        LIMIT 4
    ");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->bindParam(':price_min', $priceMin);
    $stmt->bindParam(':price_max', $priceMax);
    $stmt->execute();
    $similarProducts = $stmt->fetchAll();
    
    // Si pas assez de produits similaires, prendre les plus récents
    if (count($similarProducts) < 4) {
        $stmt = $pdo->prepare("
            SELECT * FROM products 
            WHERE id != :id AND status = 'active'
            ORDER BY created_at DESC 
            LIMIT 4
        ");
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        $similarProducts = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $similarProducts = [];
}

// Récupérer les catégories pour la navigation
$categories = [];
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    // Table categories n'existe pas
}
?>

<?php 
$pageTitle = $product['name']; 
$pageStyles = '<link href="assets/css/product-page.css" rel="stylesheet">';
include 'includes/header.php'; 
?>

    <!-- Breadcrumb moderne -->
    <nav aria-label="breadcrumb" class="breadcrumb-modern">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="index.php">
                        <i class="fas fa-home me-1"></i>Accueil
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="products.php">
                        <i class="fas fa-tshirt me-1"></i>Produits
                    </a>
                </li>
                <?php if (!empty($product['category_name'])): ?>
                <li class="breadcrumb-item">
                    <a href="products.php?category=<?php echo urlencode($product['category_slug'] ?? ''); ?>">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                </li>
                <?php endif; ?>
                <li class="breadcrumb-item active">
                    <?php echo htmlspecialchars($product['name']); ?>
                </li>
            </ol>
        </div>
    </nav>

    <!-- Section produit principale -->
    <section class="product-main-section py-5">
        <div class="container">
            <div class="row gx-5">
                <!-- Galerie d'images -->
                <div class="col-lg-7">
                    <?php 
                        $gallery = $images;
                        if ($product['image_url']) { array_unshift($gallery, $product['image_url']); }
                        $gallery = array_values(array_unique($gallery));
                        if (empty($gallery)) { $gallery = ['assets/images/placeholder.png']; }
                    ?>
                    <div class="product-gallery">
                        <!-- Image principale -->
                        <div class="main-image-container">
                            <div class="product-badges">
                                <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                    <?php $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>
                                    <span class="badge badge-sale">-<?php echo $discount; ?>%</span>
                                <?php endif; ?>
                                <?php if (strtotime($product['created_at']) > strtotime('-7 days')): ?>
                                    <span class="badge badge-new">Nouveau</span>
                                <?php endif; ?>
                                <?php if ($product['featured']): ?>
                                    <span class="badge badge-featured">Coup de cœur</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Bouton wishlist -->
                            <?php $inWishlist = isProductInWishlist($product['id']); ?>
                            <button class="wishlist-btn-gallery <?php echo $inWishlist ? 'active' : ''; ?>"
                                    onclick="toggleWishlist(<?php echo $product['id']; ?>)"
                                    title="<?php echo $inWishlist ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
                                <i class="<?php echo $inWishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                            </button>
                            
                            <div class="main-image-wrapper">
                                <img src="<?php echo htmlspecialchars($gallery[0]); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="main-product-image" 
                                     id="mainProductImage">
                                
                                <!-- Zoom overlay -->
                                <div class="zoom-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <span>Cliquer pour zoomer</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Miniatures -->
                        <?php if (count($gallery) > 1): ?>
                        <div class="thumbnails-container">
                            <div class="thumbnails-wrapper">
                                <?php foreach ($gallery as $index => $imgUrl): ?>
                                    <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                                         onclick="changeMainImage('<?php echo htmlspecialchars($imgUrl); ?>', this)">
                                        <img src="<?php echo htmlspecialchars($imgUrl); ?>" 
                                             alt="Miniature <?php echo $index + 1; ?>" 
                                             class="thumbnail-image">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Détails du produit -->
                <div class="col-lg-5">
                    <div class="product-info">
                        <!-- En-tête produit -->
                        <div class="product-header">
                            <?php if ($product['brand']): ?>
                                <div class="product-brand">
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo htmlspecialchars($product['brand']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                            
                            <!-- Évaluation (si disponible) -->
                            <div class="product-rating">
                                <div class="stars">
                                    <?php 
                                    $rating = $product['rating'] ?? 4.5; // Exemple
                                    for ($i = 1; $i <= 5; $i++): 
                                    ?>
                                        <i class="fas fa-star <?php echo $i <= $rating ? 'active' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-text">(<?php echo $product['review_count'] ?? 23; ?> avis)</span>
                                <a href="#reviews" class="rating-link">Voir les avis</a>
                            </div>
                        </div>
                        
                        <!-- Prix -->
                        <div class="product-pricing">
                            <div class="price-container">
                                <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                    <span class="current-price"><?php echo number_format($product['sale_price'], 2, ',', ' '); ?> €</span>
                                    <span class="original-price"><?php echo number_format($product['price'], 2, ',', ' '); ?> €</span>
                                    <span class="savings">
                                        Économisez <?php echo number_format($product['price'] - $product['sale_price'], 2, ',', ' '); ?> €
                                    </span>
                                <?php else: ?>
                                    <span class="current-price"><?php echo number_format($product['price'], 2, ',', ' '); ?> €</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Informations de livraison -->
                            <div class="shipping-info">
                                <div class="shipping-item">
                                    <i class="fas fa-truck text-success"></i>
                                    <span>Livraison gratuite dès 50€</span>
                                </div>
                                <div class="shipping-item">
                                    <i class="fas fa-clock text-info"></i>
                                    <span>Livraison sous 2-3 jours ouvrés</span>
                                </div>
                                <div class="shipping-item">
                                    <i class="fas fa-undo text-warning"></i>
                                    <span>Retours gratuits sous 30 jours</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Statut stock -->
                        <div class="stock-status">
                            <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                                <div class="stock-available">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>En stock (<?php echo $product['stock']; ?> disponible<?php echo $product['stock'] > 1 ? 's' : ''; ?>)</span>
                                </div>
                                <?php if ($product['stock'] <= 5): ?>
                                    <div class="stock-warning">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                        <span>Dépêchez-vous ! Plus que <?php echo $product['stock']; ?> en stock</span>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="stock-unavailable">
                                    <i class="fas fa-times-circle text-danger"></i>
                                    <span>Actuellement en rupture de stock</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Formulaire d'achat -->
                        <form id="add-to-cart-form" method="POST" action="cart.php" class="purchase-form">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($product['name']); ?>">
                            <input type="hidden" name="price" value="<?php echo (float)($product['sale_price'] ?: $product['price']); ?>">
                            
                            <!-- Sélecteur de taille -->
                            <?php if (!empty($availableSizes)): ?>
                            <div class="size-selection">
                                <label class="form-label">
                                    <i class="fas fa-ruler me-1"></i>
                                    Taille <span class="text-danger">*</span>
                                </label>
                                <div class="size-options">
                                    <?php foreach ($availableSizes as $s): ?>
                                        <input type="radio" class="size-radio" name="size" value="<?php echo $s['id']; ?>" 
                                               id="size-<?php echo $s['id']; ?>" <?php echo ($s['stock'] > 0) ? '' : 'disabled'; ?>>
                                        <label class="size-option <?php echo ($s['stock'] <= 0) ? 'disabled' : ''; ?>" 
                                               for="size-<?php echo $s['id']; ?>">
                                            <?php echo htmlspecialchars($s['name']); ?>
                                            <?php if ($s['stock'] <= 0): ?>
                                                <span class="size-unavailable">×</span>
                                            <?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <a href="#size-guide" class="size-guide-link">
                                    <i class="fas fa-question-circle me-1"></i>
                                    Guide des tailles
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Couleur (si disponible) -->
                            <?php if (!empty($product['color'])): ?>
                            <div class="color-selection">
                                <label class="form-label">
                                    <i class="fas fa-palette me-1"></i>
                                    Couleur
                                </label>
                                <div class="color-display">
                                    <span class="color-name"><?php echo htmlspecialchars($product['color']); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Quantité -->
                            <div class="quantity-selection">
                                <label class="form-label">
                                    <i class="fas fa-sort-numeric-up me-1"></i>
                                    Quantité
                                </label>
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn quantity-decrease" onclick="decreaseQuantity()">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" class="quantity-input" value="1" 
                                           min="1" max="<?php echo $product['stock'] ?? 10; ?>" id="quantityInput">
                                    <button type="button" class="quantity-btn quantity-increase" onclick="increaseQuantity()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Boutons d'action -->
                            <div class="action-buttons">
                                <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                                    <button type="submit" class="btn-add-to-cart">
                                        <i class="fas fa-shopping-cart me-2"></i>
                                        <span>Ajouter au panier</span>
                                        <div class="btn-price">
                                            <?php echo number_format($product['sale_price'] ?: $product['price'], 2, ',', ' '); ?> €
                                        </div>
                                    </button>
                                    
                                    <div class="secondary-actions">
                                        <button type="button" class="btn-wishlist <?php echo $inWishlist ? 'active' : ''; ?>"
                                                onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                                            <i class="<?php echo $inWishlist ? 'fas' : 'far'; ?> fa-heart me-1"></i>
                                            <?php echo $inWishlist ? 'Dans mes favoris' : 'Ajouter aux favoris'; ?>
                                        </button>
                                        
                                        <button type="button" class="btn-share" onclick="shareProduct()">
                                            <i class="fas fa-share me-1"></i>
                                            Partager
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <button type="button" class="btn-notify-stock" onclick="notifyWhenAvailable()">
                                        <i class="fas fa-bell me-2"></i>
                                        M'alerter quand disponible
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                        
                        <!-- Garanties et services -->
                        <div class="product-guarantees">
                            <div class="guarantee-item">
                                <i class="fas fa-shield-alt text-success"></i>
                                <span>Paiement 100% sécurisé</span>
                            </div>
                            <div class="guarantee-item">
                                <i class="fas fa-medal text-warning"></i>
                                <span>Garantie qualité</span>
                            </div>
                            <div class="guarantee-item">
                                <i class="fas fa-headset text-info"></i>
                                <span>Service client 7j/7</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Onglets d'informations -->
    <section class="product-tabs-section py-5 bg-light">
        <div class="container">
            <div class="product-tabs">
                <nav class="tabs-nav">
                    <button class="tab-btn active" data-tab="description">
                        <i class="fas fa-align-left me-1"></i>
                        Description
                    </button>
                    <button class="tab-btn" data-tab="specifications">
                        <i class="fas fa-list-ul me-1"></i>
                        Caractéristiques
                    </button>
                    <button class="tab-btn" data-tab="reviews">
                        <i class="fas fa-star me-1"></i>
                        Avis (<?php echo $product['review_count'] ?? 23; ?>)
                    </button>
                    <button class="tab-btn" data-tab="care">
                        <i class="fas fa-hands-wash me-1"></i>
                        Entretien
                    </button>
                </nav>
                
                <div class="tabs-content">
                    <!-- Description -->
                    <div class="tab-content active" id="description">
                        <div class="content-wrapper">
                            <h3>Description du produit</h3>
                            <div class="description-text">
                                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                            </div>
                            
                            <?php if ($product['features']): ?>
                            <div class="product-features">
                                <h4>Points forts</h4>
                                <ul class="features-list">
                                    <?php foreach (explode(',', $product['features']) as $feature): ?>
                                        <li><i class="fas fa-check text-success me-2"></i><?php echo trim(htmlspecialchars($feature)); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Caractéristiques -->
                    <div class="tab-content" id="specifications">
                        <div class="content-wrapper">
                            <h3>Caractéristiques techniques</h3>
                            <div class="specifications-grid">
                                <?php if ($product['brand']): ?>
                                <div class="spec-item">
                                    <span class="spec-label">Marque</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['brand']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['color']): ?>
                                <div class="spec-item">
                                    <span class="spec-label">Couleur</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['color']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['material']): ?>
                                <div class="spec-item">
                                    <span class="spec-label">Matière</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['material']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="spec-item">
                                    <span class="spec-label">Référence</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['sku'] ?? 'REF-' . $product['id']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Avis -->
                    <div class="tab-content" id="reviews">
                        <div class="content-wrapper">
                            <div class="reviews-summary">
                                <div class="rating-overview">
                                    <div class="rating-score">
                                        <span class="score"><?php echo $rating ?? 4.5; ?></span>
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= ($rating ?? 4.5) ? 'active' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p><?php echo $product['review_count'] ?? 23; ?> avis clients</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="reviews-list">
                                <!-- Exemples d'avis -->
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="reviewer-name">Marie D.</div>
                                        <div class="review-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="review-date">Il y a 2 jours</div>
                                    </div>
                                    <div class="review-content">
                                        <p>Excellent produit ! Très bonne qualité et conforme à la description. Je recommande vivement.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <button class="btn btn-outline-primary mt-3">
                                <i class="fas fa-plus me-1"></i>
                                Laisser un avis
                            </button>
                        </div>
                    </div>
                    
                    <!-- Entretien -->
                    <div class="tab-content" id="care">
                        <div class="content-wrapper">
                            <h3>Instructions d'entretien</h3>
                            <div class="care-instructions">
                                <div class="care-item">
                                    <i class="fas fa-tint text-info"></i>
                                    <span>Lavage à 30°C maximum</span>
                                </div>
                                <div class="care-item">
                                    <i class="fas fa-sun text-warning"></i>
                                    <span>Séchage à l'air libre recommandé</span>
                                </div>
                                <div class="care-item">
                                    <i class="fas fa-iron text-secondary"></i>
                                    <span>Repassage à température moyenne</span>
                                </div>
                                <div class="care-item">
                                    <i class="fas fa-ban text-danger"></i>
                                    <span>Ne pas nettoyer à sec</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Produits similaires -->
    <?php if (!empty($similarProducts)): ?>
    <section class="similar-products-section py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">
                    <i class="fas fa-heart text-danger me-2"></i>
                    Vous pourriez aussi aimer
                </h2>
                <p class="section-subtitle">Découvrez d'autres produits sélectionnés pour vous</p>
            </div>
            
            <div class="similar-products-carousel">
                <div class="row g-4">
                    <?php foreach ($similarProducts as $index => $similar): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="product-card similar-product animate-fade-in-up" 
                                 style="animation-delay: <?php echo $index * 0.1; ?>s;"
                                 onclick="location.href='product.php?id=<?php echo $similar['id']; ?>'">
                                
                                <!-- Badges -->
                                <div class="product-badge">
                                    <?php if ($similar['sale_price'] && $similar['sale_price'] < $similar['price']): ?>
                                        <?php $discount = round((($similar['price'] - $similar['sale_price']) / $similar['price']) * 100); ?>
                                        <span class="badge-sale">-<?php echo $discount; ?>%</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Bouton wishlist -->
                                <?php $inWishlistSimilar = isProductInWishlist($similar['id']); ?>
                                <button class="wishlist-btn <?php echo $inWishlistSimilar ? 'active' : ''; ?>"
                                        onclick="event.stopPropagation(); toggleWishlist(<?php echo $similar['id']; ?>)"
                                        title="<?php echo $inWishlistSimilar ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
                                    <i class="<?php echo $inWishlistSimilar ? 'fas' : 'far'; ?> fa-heart"></i>
                                </button>
                                
                                <!-- Image du produit -->
                                <div class="product-image-container">
                                    <?php if ($similar['image_url'] && file_exists($similar['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($similar['image_url']); ?>" 
                                             class="product-image" 
                                             alt="<?php echo htmlspecialchars($similar['name']); ?>">
                                    <?php else: ?>
                                        <div class="product-image d-flex align-items-center justify-content-center bg-light">
                                            <i class="fas fa-tshirt fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Informations du produit -->
                                <div class="product-info">
                                    <h5 class="product-title"><?php echo htmlspecialchars($similar['name']); ?></h5>
                                    
                                    <!-- Prix -->
                                    <div class="product-price-container">
                                        <?php if ($similar['sale_price'] && $similar['sale_price'] < $similar['price']): ?>
                                            <span class="product-price"><?php echo formatPrice($similar['sale_price']); ?></span>
                                            <span class="product-original-price"><?php echo formatPrice($similar['price']); ?></span>
                                        <?php else: ?>
                                            <span class="product-price"><?php echo formatPrice($similar['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Bouton d'ajout au panier -->
                                    <div class="mt-auto">
                                        <form method="POST" action="cart.php" class="d-grid" onClick="event.stopPropagation();">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="id" value="<?php echo (int)$similar['id']; ?>">
                                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($similar['name']); ?>">
                                            <input type="hidden" name="price" value="<?php echo (float)($similar['sale_price'] ?: $similar['price']); ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn-add-to-cart-small">
                                                <i class="fas fa-shopping-cart me-1"></i>
                                                Ajouter au panier
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Navigation pour voir plus -->
                <div class="text-center mt-5">
                    <a href="products.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-search me-2"></i>
                        Voir plus de produits
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Bouton flottant d'ajout au panier (mobile) -->
    <div class="floating-add-to-cart d-lg-none">
        <div class="floating-cart-content">
            <div class="product-price-mobile">
                <?php echo number_format($product['sale_price'] ?: $product['price'], 2, ',', ' '); ?> €
            </div>
            <button class="btn-floating-cart" onclick="scrollToForm()">
                <i class="fas fa-shopping-cart me-1"></i>
                Ajouter au panier
            </button>
        </div>
    </div>

    <?php
    $pageScripts = '
    <script>
    // JavaScript pour la page produit
    document.addEventListener("DOMContentLoaded", function() {
        initializeProductPage();
    });

    function initializeProductPage() {
        setupImageGallery();
        setupTabs();
        setupQuantityControls();
        setupFormValidation();
        setupShareFunction();
    }

    function setupImageGallery() {
        // Gestion du changement d\'image principale
        window.changeMainImage = function(imageSrc, thumbnailElement) {
            const mainImage = document.getElementById("mainProductImage");
            if (mainImage) {
                mainImage.src = imageSrc;
                
                // Mettre à jour les miniatures actives
                document.querySelectorAll(".thumbnail-item").forEach(thumb => {
                    thumb.classList.remove("active");
                });
                thumbnailElement.classList.add("active");
            }
        };
        
        // Zoom sur l\'image principale
        const mainImage = document.getElementById("mainProductImage");
        if (mainImage) {
            mainImage.addEventListener("click", function() {
                openImageModal(this.src);
            });
        }
    }

    function openImageModal(imageSrc) {
        // Créer une modal pour le zoom de l\'image
        const modal = document.createElement("div");
        modal.className = "image-modal";
        modal.innerHTML = `
            <div class="image-modal-content">
                <span class="image-modal-close">&times;</span>
                <img src="${imageSrc}" alt="Image agrandie" class="modal-image">
            </div>
        `;
        
        document.body.appendChild(modal);
        modal.style.display = "block";
        
        // Fermer la modal
        modal.querySelector(".image-modal-close").onclick = function() {
            modal.remove();
        };
        
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }

    function setupTabs() {
        const tabButtons = document.querySelectorAll(".tab-btn");
        const tabContents = document.querySelectorAll(".tab-content");
        
        tabButtons.forEach(button => {
            button.addEventListener("click", function() {
                const targetTab = this.dataset.tab;
                
                // Désactiver tous les onglets
                tabButtons.forEach(btn => btn.classList.remove("active"));
                tabContents.forEach(content => content.classList.remove("active"));
                
                // Activer l\'onglet cliqué
                this.classList.add("active");
                document.getElementById(targetTab).classList.add("active");
            });
        });
    }

    function setupQuantityControls() {
        window.decreaseQuantity = function() {
            const input = document.getElementById("quantityInput");
            const currentValue = parseInt(input.value);
            if (currentValue > parseInt(input.min)) {
                input.value = currentValue - 1;
            }
        };
        
        window.increaseQuantity = function() {
            const input = document.getElementById("quantityInput");
            const currentValue = parseInt(input.value);
            if (currentValue < parseInt(input.max)) {
                input.value = currentValue + 1;
            }
        };
    }

    function setupFormValidation() {
        const form = document.getElementById("add-to-cart-form");
        if (form) {
            form.addEventListener("submit", function(e) {
                const sizeRadios = form.querySelectorAll("input[name=\'size\']");
                
                if (sizeRadios.length > 0) {
                    const sizeSelected = Array.from(sizeRadios).some(radio => radio.checked);
                    if (!sizeSelected) {
                        e.preventDefault();
                        showNotification("Veuillez sélectionner une taille", "warning");
                        return false;
                    }
                }
                
                // Animation du bouton
                const submitBtn = form.querySelector(".btn-add-to-cart");
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>Ajout en cours...`;
                
                // Rétablir le bouton après soumission
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                }, 1000);
            });
        }
    }

    function setupShareFunction() {
        window.shareProduct = function() {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: window.location.href
                });
            } else {
                // Fallback: copier l\'URL
                navigator.clipboard.writeText(window.location.href).then(() => {
                    showNotification("Lien copié dans le presse-papiers", "success");
                });
            }
        };
        
        window.notifyWhenAvailable = function() {
            showNotification("Vous serez alerté dès que le produit sera disponible", "info");
        };
        
        window.scrollToForm = function() {
            document.getElementById("add-to-cart-form").scrollIntoView({
                behavior: "smooth"
            });
        };
    }

    function showNotification(message, type) {
        if (window.modernEcommerce && window.modernEcommerce.showNotification) {
            window.modernEcommerce.showNotification(message, type);
        } else {
            alert(message);
        }
    }
    </script>
    ';
    
    include 'includes/footer.php'; 
    ?>