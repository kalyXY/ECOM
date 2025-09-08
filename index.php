<?php
require_once 'config/bootstrap.php';
require_once 'models/Product.php';

// Initialiser le modèle Product
$productModel = new Product($pdo);

// Récupérer les produits en vedette avec le nouveau système
$featuredProducts = [];
try {
    $result = $productModel->getAll(['featured' => true], 1, 8);
    $featuredProducts = $result['products'];
} catch (Exception $e) {
    // En cas d'erreur, récupérer les produits populaires
    try {
        $featuredProducts = $productModel->getPopular(8);
    } catch (Exception $e) {
        $featuredProducts = [];
    }
}

// Récupérer les catégories principales
$categories = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' AND parent_id IS NULL ORDER BY sort_order ASC, name ASC LIMIT 6");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

$pageTitle = 'Accueil';
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section moderne -->
<section class="hero-modern">
    <div class="container-fluid p-0">
        <div class="hero-banner">
            <div class="hero-content">
                <div class="container">
                    <div class="row align-items-center min-vh-50">
                        <div class="col-lg-6">
                            <div class="hero-text">
                                <h1 class="hero-title">Mode & Style pour Toute la Famille</h1>
                                <p class="hero-subtitle">Découvrez les dernières tendances mode pour hommes, femmes et enfants. Qualité premium, prix imbattables.</p>
                                <div class="hero-actions">
                                    <a href="products.php" class="btn btn-primary btn-lg me-3">
                                        <i class="fas fa-shopping-bag me-2"></i>Découvrir
                                    </a>
                                    <a href="products.php?featured=1" class="btn btn-outline-primary btn-lg">
                                        <i class="fas fa-fire me-2"></i>Tendances
                                    </a>
                                </div>
                                <div class="hero-features">
                                    <div class="feature-item">
                                        <i class="fas fa-shipping-fast text-success"></i>
                                        <span>Livraison gratuite dès 50€</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-undo text-info"></i>
                                        <span>Retours gratuits 30 jours</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-shield-alt text-warning"></i>
                                        <span>Paiement 100% sécurisé</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="hero-image">
                                <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800" 
                                     alt="Mode StyleHub" class="img-fluid rounded-3 shadow-lg">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Barre de catégories rapides -->
<section class="quick-categories py-3 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-6 col-md-3 mb-2">
                <a href="products.php?gender=femme" class="category-quick-link">
                    <i class="fas fa-female fa-2x text-pink mb-2"></i>
                    <div>Mode Femme</div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <a href="products.php?gender=homme" class="category-quick-link">
                    <i class="fas fa-male fa-2x text-primary mb-2"></i>
                    <div>Mode Homme</div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <a href="products.php?category=enfant" class="category-quick-link">
                    <i class="fas fa-child fa-2x text-success mb-2"></i>
                    <div>Mode Enfant</div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <a href="products.php?category=accessoires" class="category-quick-link">
                    <i class="fas fa-gem fa-2x text-warning mb-2"></i>
                    <div>Accessoires</div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<?php if (!empty($categories)): ?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fashion-title mb-3">Nos Collections</h2>
            <div class="fashion-divider"></div>
            <p class="text-muted">Explorez nos différentes gammes de produits mode</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card category-card h-100 border-0 shadow-sm">
                        <?php if ($category['image_url'] && file_exists($category['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($category['image_url']); ?>" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;" 
                                 alt="<?php echo htmlspecialchars($category['name']); ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-tshirt fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body text-center">
                            <h5 class="card-title fashion-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($category['description'] ?? 'Découvrez notre sélection'); ?></p>
                            <a href="products.php?category=<?php echo urlencode($category['slug']); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>Voir la Collection
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Products Section -->
<?php if (!empty($featuredProducts)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fashion-title mb-3">Coups de Cœur</h2>
            <div class="fashion-divider"></div>
            <p class="text-muted">Nos pièces favorites sélectionnées avec soin</p>
        </div>
        
        <div class="row g-3">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card" onclick="location.href='product.php?id=<?php echo $product['id']; ?>'">
                        <!-- Badges -->
                        <div class="product-badge">
                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                <?php $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>
                                <span class="badge-sale">-<?php echo $discount; ?>%</span>
                            <?php endif; ?>
                            <?php if (strtotime($product['created_at']) > strtotime('-7 days')): ?>
                                <span class="badge-new">Nouveau</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Bouton wishlist -->
                        <button class="wishlist-btn" onclick="event.stopPropagation(); toggleWishlist(<?php echo $product['id']; ?>)">
                            <i class="far fa-heart"></i>
                        </button>
                        
                        <!-- Image du produit -->
                        <div class="product-image-container">
                            <?php if ($product['image_url'] && file_exists($product['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     class="product-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div class="product-image d-flex align-items-center justify-content-center">
                                    <i class="fas fa-tshirt fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Informations du produit -->
                        <div class="product-info">
                            <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            
                            <!-- Prix -->
                            <div class="product-price-container">
                                <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                    <span class="product-price"><?php echo formatPrice($product['sale_price']); ?></span>
                                    <span class="product-original-price"><?php echo formatPrice($product['price']); ?></span>
                                <?php else: ?>
                                    <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Métadonnées -->
                            <div class="product-meta">
                                <div class="product-rating">
                                    <span class="stars">★★★★☆</span>
                                    <span>(4.2)</span>
                                </div>
                                <div class="product-sold">125 vendus</div>
                            </div>
                            
                            <!-- Tags -->
                            <div class="product-tags">
                                <?php if ($product['brand']): ?>
                                    <span class="product-tag"><?php echo htmlspecialchars($product['brand']); ?></span>
                                <?php endif; ?>
                                <?php if ($product['color']): ?>
                                    <span class="product-tag"><?php echo htmlspecialchars($product['color']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Livraison -->
                            <div class="product-shipping">
                                <i class="fas fa-shipping-fast me-1"></i>Livraison gratuite
                            </div>
                            
                            <!-- Boutons d'action -->
                            <div class="mt-2">
                                <button class="btn-add-to-cart mb-1" 
                                        onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['sale_price'] ?: $product['price']; ?>)">
                                    <i class="fas fa-shopping-cart me-1"></i>Ajouter au panier
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="products.php" class="btn btn-fashion btn-lg">
                <i class="fas fa-tshirt me-2"></i>Voir Toute la Collection
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Fashion Quote Section -->
<section class="py-5">
    <div class="container">
        <div class="fashion-quote">
            La mode se démode, le style jamais.
        </div>
        <p class="text-center text-muted">- Coco Chanel</p>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5 bg-dark text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h3 class="fashion-title mb-3">Restez à la Mode</h3>
                <p class="mb-0">Inscrivez-vous à notre newsletter pour recevoir nos dernières tendances et offres exclusives.</p>
            </div>
            <div class="col-lg-6">
                <form class="d-flex gap-2 mt-3 mt-lg-0">
                    <input type="email" class="form-control" placeholder="Votre adresse email" required>
                    <button type="submit" class="btn btn-fashion">
                        <i class="fas fa-paper-plane me-1"></i>S'inscrire
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="service-item">
                    <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                    <h5>Livraison Rapide</h5>
                    <p class="text-muted">Livraison gratuite dès 50€ d'achat</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="service-item">
                    <i class="fas fa-undo fa-3x text-primary mb-3"></i>
                    <h5>Retours Gratuits</h5>
                    <p class="text-muted">30 jours pour changer d'avis</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="service-item">
                    <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                    <h5>Service Client</h5>
                    <p class="text-muted">Équipe dédiée à votre écoute</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="service-item">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h5>Paiement Sécurisé</h5>
                    <p class="text-muted">Transactions 100% sécurisées</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
// Fonction pour gérer la wishlist
function toggleWishlist(productId) {
    const button = event.target.closest('.wishlist-btn');
    const icon = button.querySelector('i');
    
    if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        button.classList.add('active');
        showToast('Produit ajouté aux favoris', 'success');
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        button.classList.remove('active');
        showToast('Produit retiré des favoris', 'info');
    }
    
    // Ici vous pouvez ajouter la logique pour sauvegarder en base de données
}

// Animation au scroll
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.product-card, .category-card, .service-item').forEach(item => {
        observer.observe(item);
    });
});
</script>