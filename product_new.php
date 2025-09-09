<?php
require_once 'config/bootstrap.php';
require_once 'models/Product.php';

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$productId = (int)$_GET['id'];

// Initialiser le modèle Product
$productModel = new Product($pdo);

// Récupérer le produit avec toutes ses données
$product = $productModel->getById($productId, true); // true pour incrémenter les vues

if (!$product || $product['status'] !== 'active') {
    header('Location: index.php');
    exit();
}

// Les images, tailles et couleurs sont déjà incluses dans $product
$images = $product['images'] ?? [];
$availableSizes = $product['available_sizes'] ?? [];
$allSizes = $product['sizes'] ?? [];
$availableColors = $product['colors'] ?? [];

// Récupérer des produits similaires
$similarProducts = [];
try {
    $filters = [
        'category_id' => $product['category_id'],
        'gender' => $product['gender']
    ];
    $similarData = $productModel->getAll($filters, 1, 4);
    $similarProducts = array_filter($similarData['products'], function($p) use ($productId) {
        return $p['id'] != $productId;
    });
    $similarProducts = array_slice($similarProducts, 0, 4);
} catch (Exception $e) {
    error_log('Error fetching similar products: ' . $e->getMessage());
}

// Récupérer les catégories pour la navigation
$categories = [];
try {
    $categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    // Table categories n'existe pas
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - StyleHub</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 160)); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Open Graph for social sharing -->
    <meta property="og:title" content="<?php echo htmlspecialchars($product['name']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 200)); ?>">
    <meta property="og:image" content="<?php echo !empty($images) ? htmlspecialchars($images[0]) : ''; ?>">
    <meta property="og:url" content="<?php echo $_SERVER['REQUEST_URI']; ?>">
    
    <style>
        .product-gallery {
            position: relative;
        }
        
        .main-image-container {
            position: relative;
            overflow: hidden;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .product-main-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            cursor: zoom-in;
            transition: transform 0.3s ease;
        }
        
        .product-main-image:hover {
            transform: scale(1.05);
        }
        
        .product-badge {
            position: absolute;
            top: 15px;
            z-index: 10;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .sale-badge {
            left: 15px;
            background: linear-gradient(45deg, #ff4757, #ff3742);
            color: white;
        }
        
        .featured-badge {
            right: 15px;
            background: linear-gradient(45deg, #ffa502, #ff6348);
            color: white;
        }
        
        .zoom-indicator {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .main-image-container:hover .zoom-indicator {
            opacity: 1;
        }
        
        .thumbnails-container {
            margin-top: 1rem;
        }
        
        .thumbnails-scroll {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        
        .thumbnail-item {
            flex-shrink: 0;
            width: 80px;
            height: 80px;
            border-radius: 0.25rem;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s ease;
        }
        
        .thumbnail-item.active {
            border-color: #0d6efd;
        }
        
        .thumbnail-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .size-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .size-option {
            position: relative;
            cursor: pointer;
        }
        
        .size-option input[type="radio"] {
            display: none;
        }
        
        .size-label {
            display: block;
            padding: 8px 16px;
            border: 2px solid #dee2e6;
            border-radius: 0.25rem;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .size-option:not(.disabled):hover .size-label {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        
        .size-option input[type="radio"]:checked + .size-label {
            border-color: #0d6efd;
            background-color: #0d6efd;
            color: white;
        }
        
        .size-option.disabled .size-label {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .color-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .color-option {
            position: relative;
            cursor: pointer;
        }
        
        .color-option input[type="radio"] {
            display: none;
        }
        
        .color-swatch {
            display: inline-block;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 3px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .color-option:hover .color-swatch {
            border-color: #0d6efd;
            transform: scale(1.1);
        }
        
        .color-option input[type="radio"]:checked + .color-swatch {
            border-color: #0d6efd;
            box-shadow: 0 0 0 2px #0d6efd;
        }
        
        .color-name {
            display: block;
            text-align: center;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
        
        .quantity-input {
            max-width: 150px;
        }
        
        .product-guarantees {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
        }
        
        .guarantee-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .product-tabs .nav-link {
            border-bottom: 2px solid transparent;
        }
        
        .product-tabs .nav-link.active {
            border-bottom-color: #0d6efd;
        }
        
        .specifications-list {
            display: grid;
            gap: 0.5rem;
        }
        
        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .spec-label {
            font-weight: 600;
            color: #6c757d;
        }
        
        .spec-value {
            color: #212529;
        }
        
        .care-instructions {
            display: grid;
            gap: 0.75rem;
        }
        
        .care-item {
            display: flex;
            align-items: center;
        }
        
        .price-display {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .current-price {
            font-size: 2rem;
            font-weight: bold;
            color: #0d6efd;
        }
        
        .original-price {
            font-size: 1.2rem;
            color: #6c757d;
            text-decoration: line-through;
        }
        
        .discount-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .product-main-image {
                height: 300px;
            }
            
            .thumbnails-scroll {
                justify-content: center;
            }
            
            .size-selector,
            .color-selector {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-store me-2"></i>StyleHub
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box me-1"></i>Produits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Panier
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" id="cart-count">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">
                            <i class="fas fa-user-shield me-1"></i>Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Spacer pour navbar fixe -->
    <div style="height: 76px;"></div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="bg-light py-3">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="products.php">Produits</a></li>
                <?php if (!empty($product['category_name'])): ?>
                    <li class="breadcrumb-item"><a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </div>
    </nav>

    <!-- Détail du produit -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Galerie d'images -->
                <div class="col-lg-6 mb-4">
                    <div class="product-gallery">
                        <?php if (!empty($images)): ?>
                            <!-- Image principale -->
                            <div class="main-image-container">
                                <img id="mainProductImage" 
                                     src="<?php echo htmlspecialchars($images[0]); ?>" 
                                     class="product-main-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#imageModal">
                                
                                <!-- Badge promo -->
                                <?php if ($product['has_discount']): ?>
                                    <div class="product-badge sale-badge">
                                        -<?php echo $product['discount_percentage']; ?>%
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($product['featured']): ?>
                                    <div class="product-badge featured-badge">
                                        <i class="fas fa-star"></i> Coup de cœur
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Zoom indicator -->
                                <div class="zoom-indicator">
                                    <i class="fas fa-search-plus"></i> Cliquer pour agrandir
                                </div>
                            </div>
                            
                            <!-- Miniatures -->
                            <?php if (count($images) > 1): ?>
                                <div class="thumbnails-container">
                                    <div class="thumbnails-scroll">
                                        <?php foreach ($images as $index => $image): ?>
                                            <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                                                 onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>', this)">
                                                <img src="<?php echo htmlspecialchars($image); ?>" 
                                                     alt="Vue <?php echo $index + 1; ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Image par défaut -->
                            <div class="main-image-container">
                                <img src="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'assets/images/placeholder.jpg'; ?>" 
                                     class="product-main-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Informations du produit -->
                <div class="col-lg-6">
                    <div class="product-info">
                        <h1 class="h2 mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <!-- Prix -->
                        <div class="price-display">
                            <span class="current-price">
                                <?php echo number_format($product['effective_price'], 2, ',', ' '); ?> €
                            </span>
                            <?php if ($product['has_discount']): ?>
                                <span class="original-price">
                                    <?php echo number_format($product['price'], 2, ',', ' '); ?> €
                                </span>
                                <span class="discount-badge">
                                    -<?php echo $product['discount_percentage']; ?>%
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Stock global -->
                        <?php if ($product['stock'] > 0): ?>
                            <div class="mb-3">
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>En stock (<?php echo $product['stock']; ?> disponible<?php echo $product['stock'] > 1 ? 's' : ''; ?>)
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <span class="badge bg-danger">
                                    <i class="fas fa-times me-1"></i>Rupture de stock
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- Description courte -->
                        <div class="mb-4">
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars(substr($product['description'], 0, 200))); ?>...</p>
                        </div>

                        <!-- Tailles disponibles -->
                        <?php if (!empty($allSizes)): ?>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0">Taille :</h6>
                                    <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#sizeGuideModal">
                                        <i class="fas fa-ruler me-1"></i>Guide des tailles
                                    </button>
                                </div>
                                <div class="size-selector">
                                    <?php foreach ($allSizes as $size): ?>
                                        <?php 
                                        $isAvailable = $size['size_stock'] === null || $size['size_stock'] > 0;
                                        $stockText = $size['size_stock'] !== null ? "Stock: {$size['size_stock']}" : "";
                                        ?>
                                        <label class="size-option <?php echo !$isAvailable ? 'disabled' : ''; ?>" 
                                               title="<?php echo $isAvailable ? $stockText : 'Rupture de stock'; ?>">
                                            <input type="radio" 
                                                   name="size" 
                                                   value="<?php echo $size['id']; ?>" 
                                                   data-size-name="<?php echo htmlspecialchars($size['name']); ?>"
                                                   data-stock="<?php echo $size['size_stock'] ?? 'null'; ?>"
                                                   <?php echo !$isAvailable ? 'disabled' : ''; ?>
                                                   required>
                                            <span class="size-label">
                                                <?php echo htmlspecialchars($size['name']); ?>
                                                <?php if (!$isAvailable): ?>
                                                    <small class="text-muted d-block">Indisponible</small>
                                                <?php elseif ($size['size_stock'] !== null && $size['size_stock'] <= 5): ?>
                                                    <small class="text-warning d-block">Plus que <?php echo $size['size_stock']; ?></small>
                                                <?php endif; ?>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div id="sizeStockInfo" class="mt-2 small text-muted" style="display: none;"></div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Couleurs disponibles -->
                        <?php if (!empty($availableColors)): ?>
                            <div class="mb-4">
                                <h6 class="fw-bold">Couleur :</h6>
                                <div class="color-selector">
                                    <?php foreach ($availableColors as $color): ?>
                                        <label class="color-option" title="<?php echo htmlspecialchars($color['name']); ?>">
                                            <input type="radio" name="color" value="<?php echo $color['id']; ?>">
                                            <span class="color-swatch" style="background-color: <?php echo htmlspecialchars($color['hex_code']); ?>"></span>
                                            <span class="color-name"><?php echo htmlspecialchars($color['name']); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Actions produit -->
                        <div class="product-actions">
                            <form id="addToCartForm" method="POST" action="cart.php">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="selected_size" id="selectedSize">
                                <input type="hidden" name="selected_color" id="selectedColor">
                                
                                <div class="quantity-selector mb-3">
                                    <label for="quantity" class="form-label fw-bold">Quantité :</label>
                                    <div class="input-group quantity-input">
                                        <button type="button" class="btn btn-outline-secondary" onclick="decreaseQuantity()">-</button>
                                        <input type="number" class="form-control text-center" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                        <button type="button" class="btn btn-outline-secondary" onclick="increaseQuantity()">+</button>
                                    </div>
                                    <div id="stockInfo" class="small text-muted mt-1"></div>
                                </div>
                                
                                <?php if ($product['stock'] > 0 || !empty($availableSizes)): ?>
                                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="addToCartBtn">
                                        <i class="fas fa-shopping-cart me-2"></i>Ajouter au panier - <?php echo number_format($product['effective_price'], 2, ',', ' '); ?> €
                                    </button>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <button type="button" class="btn btn-outline-danger w-100" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-heart me-1"></i>Favoris
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="btn btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#shareModal">
                                                <i class="fas fa-share-alt me-1"></i>Partager
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                                        <i class="fas fa-times me-2"></i>Rupture de stock
                                    </button>
                                    <button type="button" class="btn btn-outline-primary w-100" onclick="notifyWhenAvailable(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-bell me-2"></i>Me prévenir quand disponible
                                    </button>
                                <?php endif; ?>
                            </form>
                            
                            <!-- Informations supplémentaires -->
                            <div class="product-guarantees mt-4">
                                <div class="guarantee-item">
                                    <i class="fas fa-truck text-success me-2"></i>
                                    <small>Livraison gratuite dès 50€</small>
                                </div>
                                <div class="guarantee-item">
                                    <i class="fas fa-undo text-info me-2"></i>
                                    <small>Retours gratuits sous 30 jours</small>
                                </div>
                                <div class="guarantee-item">
                                    <i class="fas fa-shield-alt text-warning me-2"></i>
                                    <small>Garantie qualité</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Onglets détails -->
            <div class="product-tabs mt-5">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#details" role="tab">
                            <i class="fas fa-info-circle me-1"></i>Détails
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#specifications" role="tab">
                            <i class="fas fa-list-ul me-1"></i>Caractéristiques
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#care" role="tab">
                            <i class="fas fa-heart me-1"></i>Entretien
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="details" role="tabpanel">
                        <div class="product-description">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="specifications" role="tabpanel">
                        <div class="specifications-list">
                            <?php if (!empty($product['brand'])): ?>
                                <div class="spec-item">
                                    <span class="spec-label">Marque :</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['brand']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($product['material'])): ?>
                                <div class="spec-item">
                                    <span class="spec-label">Matière :</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['material']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($product['gender'])): ?>
                                <div class="spec-item">
                                    <span class="spec-label">Genre :</span>
                                    <span class="spec-value"><?php echo ucfirst(htmlspecialchars($product['gender'])); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($product['season'])): ?>
                                <div class="spec-item">
                                    <span class="spec-label">Saison :</span>
                                    <span class="spec-value"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($product['season']))); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($product['sku'])): ?>
                                <div class="spec-item">
                                    <span class="spec-label">Référence :</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['sku']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="care" role="tabpanel">
                        <div class="care-instructions">
                            <div class="care-item">
                                <i class="fas fa-tint text-info me-2"></i>
                                <span>Lavage en machine à 30°C maximum</span>
                            </div>
                            <div class="care-item">
                                <i class="fas fa-ban text-danger me-2"></i>
                                <span>Ne pas utiliser d'eau de javel</span>
                            </div>
                            <div class="care-item">
                                <i class="fas fa-iron text-warning me-2"></i>
                                <span>Repassage à température modérée</span>
                            </div>
                            <div class="care-item">
                                <i class="fas fa-wind text-success me-2"></i>
                                <span>Séchage à l'air libre recommandé</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Produits similaires -->
    <?php if (!empty($similarProducts)): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <h3 class="text-center mb-5">Produits similaires</h3>
            <div class="row g-4">
                <?php foreach ($similarProducts as $similar): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card product-card h-100">
                            <?php if ($similar['image_url'] && file_exists($similar['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($similar['image_url']); ?>" 
                                     class="card-img-top product-image" 
                                     alt="<?php echo htmlspecialchars($similar['name']); ?>"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top product-image bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title"><?php echo htmlspecialchars($similar['name']); ?></h6>
                                <p class="card-text flex-grow-1 small text-muted">
                                    <?php echo htmlspecialchars(substr($similar['description'], 0, 100)) . '...'; ?>
                                </p>
                                <div class="mt-auto">
                                    <div class="mb-2">
                                        <span class="h6 text-primary mb-0">
                                            <?php echo number_format($similar['price'], 2, ',', ' '); ?> €
                                        </span>
                                    </div>
                                    <div class="btn-group w-100">
                                        <a href="product_new.php?id=<?php echo $similar['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>Voir
                                        </a>
                                        <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $similar['id']; ?>, '<?php echo addslashes($similar['name']); ?>', <?php echo $similar['price']; ?>)">
                                            <i class="fas fa-shopping-cart me-1"></i>Panier
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Modal pour agrandissement d'image -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="Image agrandie">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal guide des tailles -->
    <div class="modal fade" id="sizeGuideModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Guide des tailles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Taille</th>
                                    <th>Poitrine (cm)</th>
                                    <th>Taille (cm)</th>
                                    <th>Hanches (cm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>XS</td><td>78-82</td><td>58-62</td><td>83-87</td></tr>
                                <tr><td>S</td><td>82-86</td><td>62-66</td><td>87-91</td></tr>
                                <tr><td>M</td><td>86-90</td><td>66-70</td><td>91-95</td></tr>
                                <tr><td>L</td><td>90-94</td><td>70-74</td><td>95-99</td></tr>
                                <tr><td>XL</td><td>94-98</td><td>74-78</td><td>99-103</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de partage -->
    <div class="modal fade" id="shareModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Partager ce produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="shareOnFacebook()">
                            <i class="fab fa-facebook-f me-2"></i>Facebook
                        </button>
                        <button class="btn btn-info" onclick="shareOnTwitter()">
                            <i class="fab fa-twitter me-2"></i>Twitter
                        </button>
                        <button class="btn btn-success" onclick="shareOnWhatsApp()">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp
                        </button>
                        <button class="btn btn-secondary" onclick="copyToClipboard()">
                            <i class="fas fa-copy me-2"></i>Copier le lien
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><i class="fas fa-store me-2"></i>StyleHub</h5>
                    <p class="mb-3">Votre boutique de mode en ligne de confiance depuis 2024.</p>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Navigation</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50 text-decoration-none">Accueil</a></li>
                        <li><a href="products.php" class="text-white-50 text-decoration-none">Produits</a></li>
                        <li><a href="contact.php" class="text-white-50 text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6>Contact</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone me-2"></i>01 23 45 67 89</li>
                        <li><i class="fas fa-envelope me-2"></i>contact@stylehub.com</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <small class="text-muted">© 2024 StyleHub. Tous droits réservés.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Mettre à jour le compteur du panier au chargement
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        }

        // Changer l'image principale
        function changeMainImage(imageSrc, thumbnailElement) {
            const mainImage = document.getElementById('mainProductImage');
            const modalImage = document.getElementById('modalImage');
            
            mainImage.src = imageSrc;
            modalImage.src = imageSrc;
            
            // Mettre à jour les classes active
            document.querySelectorAll('.thumbnail-item').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbnailElement.classList.add('active');
        }

        // Gestion des tailles
        document.querySelectorAll('input[name="size"]').forEach(sizeInput => {
            sizeInput.addEventListener('change', function() {
                const sizeName = this.dataset.sizeName;
                const stock = this.dataset.stock;
                const selectedSize = document.getElementById('selectedSize');
                const sizeStockInfo = document.getElementById('sizeStockInfo');
                const quantityInput = document.getElementById('quantity');
                
                selectedSize.value = this.value;
                
                if (stock !== 'null') {
                    const stockNum = parseInt(stock);
                    sizeStockInfo.innerHTML = `<i class="fas fa-info-circle me-1"></i>Stock disponible pour la taille ${sizeName}: ${stockNum}`;
                    sizeStockInfo.style.display = 'block';
                    quantityInput.max = stockNum;
                    if (parseInt(quantityInput.value) > stockNum) {
                        quantityInput.value = stockNum;
                    }
                } else {
                    sizeStockInfo.style.display = 'none';
                    quantityInput.max = <?php echo $product['stock']; ?>;
                }
            });
        });

        // Gestion des couleurs
        document.querySelectorAll('input[name="color"]').forEach(colorInput => {
            colorInput.addEventListener('change', function() {
                document.getElementById('selectedColor').value = this.value;
            });
        });

        // Gestion de la quantité
        function increaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            const max = parseInt(quantityInput.max);
            const current = parseInt(quantityInput.value);
            if (current < max) {
                quantityInput.value = current + 1;
            }
        }

        function decreaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            const current = parseInt(quantityInput.value);
            if (current > 1) {
                quantityInput.value = current - 1;
            }
        }

        // Validation du formulaire
        document.getElementById('addToCartForm').addEventListener('submit', function(e) {
            const sizeInputs = document.querySelectorAll('input[name="size"]');
            const colorInputs = document.querySelectorAll('input[name="color"]');
            
            let sizeSelected = false;
            let colorSelected = false;
            
            // Vérifier si une taille est requise et sélectionnée
            if (sizeInputs.length > 0) {
                sizeInputs.forEach(input => {
                    if (input.checked) sizeSelected = true;
                });
                
                if (!sizeSelected) {
                    e.preventDefault();
                    alert('Veuillez sélectionner une taille.');
                    return false;
                }
            }
            
            // Vérifier si une couleur est requise et sélectionnée
            if (colorInputs.length > 0) {
                colorInputs.forEach(input => {
                    if (input.checked) colorSelected = true;
                });
                
                if (!colorSelected) {
                    e.preventDefault();
                    alert('Veuillez sélectionner une couleur.');
                    return false;
                }
            }
        });

        // Fonctions de partage
        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
        }

        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('<?php echo htmlspecialchars($product['name']); ?>');
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
        }

        function shareOnWhatsApp() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('Regardez ce produit: <?php echo htmlspecialchars($product['name']); ?>');
            window.open(`https://wa.me/?text=${text} ${url}`, '_blank');
        }

        function copyToClipboard() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Lien copié dans le presse-papier !');
            });
        }

        // Ajout aux favoris (fonction à implémenter)
        function addToWishlist(productId) {
            // Implémenter la logique d'ajout aux favoris
            console.log('Ajout aux favoris:', productId);
            alert('Produit ajouté aux favoris !');
        }

        // Notification de disponibilité (fonction à implémenter)
        function notifyWhenAvailable(productId) {
            // Implémenter la logique de notification
            console.log('Notification demandée pour:', productId);
            alert('Vous serez notifié quand le produit sera disponible !');
        }

        // Fonction addToCart compatible avec le script existant
        function addToCart(productId, productName, price, quantity = 1) {
            if (typeof window.addToCart === 'function') {
                window.addToCart(productId, productName, price, quantity);
            } else {
                console.log('Ajout au panier:', {productId, productName, price, quantity});
                alert('Produit ajouté au panier !');
            }
        }
    </script>
</body>
</html>