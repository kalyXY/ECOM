<?php
require_once 'config/bootstrap.php';
require_once 'models/Product.php';

// Définir le code de réponse 404
http_response_code(404);

$pageTitle = 'Page non trouvée';

// Obtenir des produits populaires pour la page 404
$productModel = new Product($pdo);
$suggestedProducts = [];
try {
    $suggestedProducts = $productModel->getPopular(4);
} catch (Exception $e) {
    $suggestedProducts = [];
}
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <!-- Illustration 404 -->
            <div class="error-illustration mb-4">
                <div class="error-number">404</div>
                <div class="error-icon">
                    <i class="fas fa-search fa-4x text-muted"></i>
                </div>
            </div>
            
            <!-- Message d'erreur -->
            <h1 class="h2 mb-3">Oups ! Page introuvable</h1>
            <p class="lead text-muted mb-4">
                La page que vous recherchez n'existe pas ou a été déplacée. 
                Ne vous inquiétez pas, nous avons d'autres trésors à vous proposer !
            </p>
            
            <!-- Actions -->
            <div class="error-actions mb-5">
                <a href="index.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-home me-2"></i>Retour à l'accueil
                </a>
                <a href="products.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-tshirt me-2"></i>Voir nos produits
                </a>
            </div>
            
            <!-- Barre de recherche -->
            <div class="error-search">
                <h5 class="mb-3">Ou recherchez ce que vous cherchiez :</h5>
                <form method="GET" action="products.php" class="d-flex justify-content-center">
                    <div class="input-group" style="max-width: 400px;">
                        <input type="text" name="search" class="form-control form-control-lg" 
                               placeholder="Rechercher un produit..." autofocus>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Produits suggérés -->
    <?php if (!empty($suggestedProducts)): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="text-center mb-4">Découvrez nos produits populaires</h4>
            <div class="row g-3">
                <?php foreach ($suggestedProducts as $product): ?>
                    <div class="col-6 col-md-3">
                        <div class="product-card" onclick="location.href='product.php?id=<?php echo $product['id']; ?>'">
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
                            
                            <div class="product-info">
                                <h6 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                                <div class="product-price-container">
                                    <span class="product-price"><?php echo App::formatPrice($product['price']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Liens utiles -->
    <div class="row mt-5">
        <div class="col-md-4">
            <div class="help-card text-center p-4">
                <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                <h5>Besoin d'aide ?</h5>
                <p class="text-muted">Notre équipe est là pour vous aider</p>
                <a href="contact.php" class="btn btn-outline-primary">Nous contacter</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="help-card text-center p-4">
                <i class="fas fa-tags fa-3x text-success mb-3"></i>
                <h5>Nos catégories</h5>
                <p class="text-muted">Explorez toutes nos collections mode</p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="products.php?gender=femme" class="badge bg-light text-dark">Femme</a>
                    <a href="products.php?gender=homme" class="badge bg-light text-dark">Homme</a>
                    <a href="products.php?category=accessoires" class="badge bg-light text-dark">Accessoires</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="help-card text-center p-4">
                <i class="fas fa-fire fa-3x text-warning mb-3"></i>
                <h5>Tendances</h5>
                <p class="text-muted">Découvrez les dernières nouveautés</p>
                <a href="products.php?featured=1" class="btn btn-outline-warning">Voir les tendances</a>
            </div>
        </div>
    </div>
</div>

<style>
.error-illustration {
    position: relative;
    margin: 2rem 0;
}

.error-number {
    font-size: 8rem;
    font-weight: 900;
    color: var(--primary-color);
    line-height: 1;
    opacity: 0.1;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1;
}

.error-icon {
    position: relative;
    z-index: 2;
    padding: 2rem 0;
}

.help-card {
    background: var(--bg-primary);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s ease;
    height: 100%;
}

.help-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.error-actions .btn {
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .error-number {
        font-size: 5rem;
    }
    
    .error-actions .btn {
        display: block;
        width: 100%;
        margin-bottom: 1rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>