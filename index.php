<?php
require_once 'includes/config.php';

// Récupérer les produits en vedette
$featuredProducts = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'active' AND featured = 1 ORDER BY created_at DESC LIMIT 8");
    $stmt->execute();
    $featuredProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    // En cas d'erreur, récupérer les produits les plus récents
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 8");
        $stmt->execute();
        $featuredProducts = $stmt->fetchAll();
    } catch (PDOException $e) {
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

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title fashion-title">Style & Élégance</h1>
            <p class="hero-subtitle">Découvrez notre collection exclusive de mode contemporaine</p>
            <a href="products.php" class="btn hero-cta">
                <i class="fas fa-tshirt me-2"></i>Découvrir la Collection
            </a>
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
        
        <div class="row g-4">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card product-card h-100 fade-in-up">
                        <!-- Badge si en promotion -->
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                            <div class="product-badge sale">Promo</div>
                        <?php endif; ?>
                        
                        <!-- Bouton wishlist -->
                        <button class="wishlist-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                            <i class="far fa-heart"></i>
                        </button>
                        
                        <!-- Image du produit -->
                        <?php if ($product['image_url'] && file_exists($product['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 class="card-img-top product-image" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="card-img-top product-image bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-tshirt fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Informations du produit -->
                        <div class="card-body product-info d-flex flex-column">
                            <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            
                            <!-- Métadonnées produit -->
                            <div class="product-meta-inline mb-2">
                                <?php if ($product['brand']): ?>
                                    <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($product['brand']); ?></span>
                                <?php endif; ?>
                                <?php if ($product['color']): ?>
                                    <span class="badge bg-light text-dark me-1"><?php echo htmlspecialchars($product['color']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="product-description flex-grow-1">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                            </p>
                            
                            <div class="mt-auto">
                                <div class="product-price mb-3">
                                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                        <span class="sale-price"><?php echo formatPrice($product['sale_price']); ?></span>
                                        <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php else: ?>
                                        <?php echo formatPrice($product['price']); ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>Voir le Produit
                                    </a>
                                    <button class="btn btn-primary btn-add-to-cart btn-sm" 
                                            onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['sale_price'] ?: $product['price']; ?>)">
                                        <i class="fas fa-shopping-bag me-1"></i>Ajouter au Panier
                                    </button>
                                </div>
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