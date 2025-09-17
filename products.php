<?php
require_once 'config/bootstrap.php';
require_once 'models/Product.php';

// Initialiser le modèle Product
$productModel = new Product($pdo);

// Gestion de la recherche et des filtres
$filters = [];
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = Config::get('items_per_page', 12);

// Appliquer les filtres de manière sécurisée
if (!empty($_GET['search'])) {
    $filters['search'] = Security::sanitizeInput($_GET['search']);
}

if (!empty($_GET['category'])) {
    $filters['category_id'] = Security::sanitizeInput($_GET['category'], 'int');
}

if (!empty($_GET['gender'])) {
    $filters['gender'] = Security::sanitizeInput($_GET['gender']);
}

if (!empty($_GET['brand'])) {
    $filters['brand'] = Security::sanitizeInput($_GET['brand']);
}

if (!empty($_GET['color'])) {
    $filters['color'] = Security::sanitizeInput($_GET['color']);
}

if (!empty($_GET['size'])) {
    $filters['size'] = Security::sanitizeInput($_GET['size']);
}

if (!empty($_GET['min_price'])) {
    $filters['min_price'] = Security::sanitizeInput($_GET['min_price'], 'float');
}

if (!empty($_GET['max_price'])) {
    $filters['max_price'] = Security::sanitizeInput($_GET['max_price'], 'float');
}

if (!empty($_GET['sort'])) {
    $filters['sort'] = Security::sanitizeInput($_GET['sort']);
}

if (isset($_GET['featured'])) {
    $filters['featured'] = $_GET['featured'] === '1';
}

// Obtenir les résultats avec le modèle optimisé
$result = $productModel->getAll($filters, $page, $perPage);
$products = $result['products'];
$totalProducts = $result['total'];
$totalPages = $result['pages'];

// Obtenir les données pour les filtres
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// Obtenir les marques disponibles
$brands = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT brand FROM products WHERE status = 'active' AND brand IS NOT NULL AND brand != '' ORDER BY brand");
    $brands = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $brands = [];
}

// Obtenir les couleurs disponibles
$colors = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT color FROM products WHERE status = 'active' AND color IS NOT NULL AND color != '' ORDER BY color");
    $colors = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $colors = [];
}

// Obtenir les tailles disponibles
$sizes = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT size FROM products WHERE status = 'active' AND size IS NOT NULL AND size != '' ORDER BY 
                        CASE 
                            WHEN size REGEXP '^[0-9]+$' THEN CAST(size AS UNSIGNED)
                            ELSE 999 
                        END, size");
    $sizes = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $sizes = [];
}

// Configuration du titre de la page
$pageTitle = 'Nos Produits';
if (!empty($filters['search'])) {
    $pageTitle = 'Recherche : ' . $filters['search'];
} elseif (!empty($filters['gender'])) {
    $pageTitle = 'Mode ' . ucfirst($filters['gender']);
} elseif (!empty($filters['category_id'])) {
    $categoryName = '';
    foreach ($categories as $cat) {
        if ($cat['id'] == $filters['category_id']) {
            $categoryName = $cat['name'];
            break;
        }
    }
    $pageTitle = $categoryName ?: 'Produits';
}
$bodyClass = 'products-page';
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar des filtres -->
        <aside class="col-lg-3">
            <div class="filters-sidebar p-4 rounded shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filtres</h5>
                    <button class="btn btn-light btn-sm" id="clearFilters">Effacer</button>
                </div>
                
                <form id="filtersForm" method="GET">
                    <?php if (!empty($filters['search'])): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>">
                    <?php endif; ?>
                    
                    <!-- Catégories -->
                    <?php if (!empty($categories)): ?>
                        <div class="filter-section mb-4">
                            <h6 class="filter-title">Catégories</h6>
                            <div class="filter-options">
                                <?php foreach ($categories as $category): ?>
                                    <div class="form-check">
                                        <input class="form-check-input product-filter" type="radio" name="category" value="<?php echo $category['id']; ?>" id="cat-<?php echo $category['id']; ?>"
                                               <?php echo ($filters['category_id'] ?? '') == $category['id'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="cat-<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Genre -->
                    <div class="filter-section mb-4">
                        <h6 class="filter-title">Genre</h6>
                        <div class="filter-options">
                            <div class="form-check">
                                <input class="form-check-input product-filter" type="radio" name="gender" value="femme" id="gender-femme" <?php echo ($filters['gender'] ?? '') === 'femme' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="gender-femme">Femme</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input product-filter" type="radio" name="gender" value="homme" id="gender-homme" <?php echo ($filters['gender'] ?? '') === 'homme' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="gender-homme">Homme</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input product-filter" type="radio" name="gender" value="unisexe" id="gender-unisexe" <?php echo ($filters['gender'] ?? '') === 'unisexe' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="gender-unisexe">Unisexe</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prix -->
                    <div class="filter-section mb-4">
                        <h6 class="filter-title">Prix</h6>
                        <div class="d-flex align-items-center">
                            <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min" value="<?php echo $filters['min_price'] ?? ''; ?>">
                            <span class="mx-2">-</span>
                            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max" value="<?php echo $filters['max_price'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <!-- Couleurs -->
                    <?php if (!empty($colors)): ?>
                    <div class="filter-section mb-4">
                        <h6 class="filter-title">Couleurs</h6>
                        <div class="color-options">
                            <?php foreach ($colors as $color): ?>
                                <div class="color-option form-check form-check-inline">
                                    <input class="form-check-input product-filter" type="radio" name="color" id="color-<?php echo strtolower($color); ?>" value="<?php echo htmlspecialchars($color); ?>" <?php echo ($filters['color'] ?? '') === $color ? 'checked' : ''; ?>>
                                    <label class="form-check-label color-swatch" for="color-<?php echo strtolower($color); ?>" style="background-color: <?php echo strtolower($color); ?>;" title="<?php echo htmlspecialchars($color); ?>"></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Tailles -->
                    <?php if (!empty($sizes)): ?>
                    <div class="filter-section mb-4">
                        <h6 class="filter-title">Tailles</h6>
                        <div class="size-options">
                            <?php foreach ($sizes as $size): ?>
                                <div class="size-option form-check form-check-inline">
                                    <input class="form-check-input product-filter" type="radio" name="size" id="size-<?php echo strtolower($size); ?>" value="<?php echo htmlspecialchars($size); ?>" <?php echo ($filters['size'] ?? '') === $size ? 'checked' : ''; ?>>
                                    <label class="form-check-label size-label" for="size-<?php echo strtolower($size); ?>"><?php echo htmlspecialchars($size); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </aside>
        
        <!-- Zone des produits -->
        <main class="col-lg-9">
            <!-- En-tête de la section produits -->
            <div class="products-header bg-white rounded-lg shadow-sm p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="section-title mb-2"><?php echo htmlspecialchars($pageTitle); ?></h1>
                        <div class="d-flex align-items-center text-muted">
                            <i class="fas fa-tag me-2"></i>
                            <span><?php echo number_format($totalProducts); ?> produit<?php echo $totalProducts > 1 ? 's' : ''; ?> trouvé<?php echo $totalProducts > 1 ? 's' : ''; ?></span>
                            <?php if (!empty($filters['search'])): ?>
                                <span class="ms-2">pour "<strong><?php echo htmlspecialchars($filters['search']); ?></strong>"</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
                            <!-- Tri -->
                            <div class="me-3">
                                <label class="form-label mb-1 small text-muted">Trier par</label>
                                <select name="sort" class="form-select form-select-sm" id="sortSelect" style="width: 200px;">
                                    <option value="newest" <?php echo ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : ''; ?>>
                                        <i class="fas fa-clock me-1"></i>Plus récents
                                    </option>
                                    <option value="price_asc" <?php echo ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : ''; ?>>
                                        <i class="fas fa-arrow-up me-1"></i>Prix croissant
                                    </option>
                                    <option value="price_desc" <?php echo ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : ''; ?>>
                                        <i class="fas fa-arrow-down me-1"></i>Prix décroissant
                                    </option>
                                    <option value="popularity" <?php echo ($filters['sort'] ?? '') === 'popularity' ? 'selected' : ''; ?>>
                                        <i class="fas fa-fire me-1"></i>Popularité
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Vue -->
                            <div>
                                <label class="form-label mb-1 small text-muted">Affichage</label>
                                <div class="view-toggle btn-group d-block">
                                    <button class="btn btn-outline-primary btn-sm active" data-view="grid" title="Vue grille">
                                        <i class="fas fa-th"></i>
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" data-view="list" title="Vue liste">
                                        <i class="fas fa-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filtres actifs -->
                <?php if (!empty(array_filter($filters))): ?>
                <div class="active-filters mt-3 pt-3 border-top">
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="me-2 small text-muted">Filtres actifs:</span>
                        <?php foreach ($filters as $key => $value): ?>
                            <?php if (!empty($value) && !in_array($key, ['sort', 'page'])): ?>
                                <span class="badge bg-primary me-2 mb-1">
                                    <?php 
                                    $labels = [
                                        'search' => 'Recherche',
                                        'category' => 'Catégorie',
                                        'gender' => 'Genre',
                                        'brand' => 'Marque',
                                        'color' => 'Couleur',
                                        'size' => 'Taille',
                                        'min_price' => 'Prix min',
                                        'max_price' => 'Prix max'
                                    ];
                                    echo ($labels[$key] ?? ucfirst($key)) . ': ' . htmlspecialchars($value);
                                    ?>
                                    <button type="button" class="btn-close btn-close-white ms-1" 
                                            onclick="removeFilter('<?php echo $key; ?>')" style="font-size: 0.7em;"></button>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <button class="btn btn-link btn-sm text-danger p-0 ms-2" onclick="clearAllFilters()">
                            <i class="fas fa-times me-1"></i>Effacer tout
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Grille des produits -->
            <div class="products-container" id="productsContainer">
                <?php if (empty($products)): ?>
                    <!-- État vide -->
                    <div class="empty-state bg-white rounded-lg shadow-sm p-5 text-center">
                        <div class="empty-state-icon mb-4">
                            <i class="fas fa-search fa-4x text-muted"></i>
                        </div>
                        <h3 class="mb-3">Aucun produit trouvé</h3>
                        <p class="text-muted mb-4">
                            <?php if (!empty($filters['search'])): ?>
                                Aucun produit ne correspond à votre recherche "<strong><?php echo htmlspecialchars($filters['search']); ?></strong>".
                            <?php else: ?>
                                Aucun produit ne correspond aux filtres sélectionnés.
                            <?php endif; ?>
                        </p>
                        <div class="empty-state-actions">
                            <button class="btn btn-primary me-2" onclick="clearAllFilters()">
                                <i class="fas fa-refresh me-2"></i>Voir tous les produits
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-2"></i>Retour à l'accueil
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Grille des produits -->
                    <div class="products-grid row g-4" id="productsGrid">
                        <?php foreach ($products as $index => $product): ?>
                            <div class="col-6 col-md-4 col-lg-4 product-col">
                                <div class="product-card h-100 animate-fade-in-up" 
                                     style="animation-delay: <?php echo ($index % 12) * 0.1; ?>s;"
                                     onclick="location.href='product.php?id=<?php echo $product['id']; ?>'">
                                     
                                    <!-- Badges -->
                                    <div class="product-badge">
                                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                            <?php $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>
                                            <span class="badge-sale">-<?php echo $discount; ?>%</span>
                                        <?php endif; ?>
                                        <?php if (strtotime($product['created_at']) > strtotime('-7 days')): ?>
                                            <span class="badge-new">Nouveau</span>
                                        <?php endif; ?>
                                        <?php if ($product['featured']): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-star me-1"></i>Coup de cœur
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Bouton wishlist -->
                                    <?php $inWishlist = isProductInWishlist($product['id']); ?>
                                    <button class="wishlist-btn <?php echo $inWishlist ? 'active' : ''; ?>"
                                            onclick="event.stopPropagation(); toggleWishlist(<?php echo $product['id']; ?>)"
                                            title="<?php echo $inWishlist ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
                                        <i class="<?php echo $inWishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                                    </button>
                                    
                                    <!-- Image du produit -->
                                    <div class="product-image-container">
                                        <?php if ($product['image_url'] && file_exists($product['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                 class="product-image" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 loading="lazy">
                                        <?php else: ?>
                                            <div class="product-image d-flex align-items-center justify-content-center bg-light">
                                                <i class="fas fa-tshirt fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Overlay avec actions rapides -->
                                        <div class="product-overlay">
                                            <div class="product-actions">
                                                <button class="btn btn-light btn-sm me-2" 
                                                        onclick="event.stopPropagation(); quickView(<?php echo $product['id']; ?>)"
                                                        title="Vue rapide">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-light btn-sm" 
                                                        onclick="event.stopPropagation(); compareProduct(<?php echo $product['id']; ?>)"
                                                        title="Comparer">
                                                    <i class="fas fa-balance-scale"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Informations du produit -->
                                    <div class="product-info">
                                        <!-- Marque et catégorie -->
                                        <div class="product-meta mb-2">
                                            <?php if ($product['brand']): ?>
                                                <span class="product-brand"><?php echo htmlspecialchars($product['brand']); ?></span>
                                            <?php endif; ?>
                                            <?php if ($product['category_name']): ?>
                                                <span class="product-category text-muted">
                                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Nom du produit -->
                                        <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        
                                        <!-- Évaluation (si disponible) -->
                                        <?php if (isset($product['rating']) && $product['rating'] > 0): ?>
                                            <div class="product-rating mb-2">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $product['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                                <span class="rating-count text-muted ms-1">
                                                    (<?php echo $product['review_count'] ?? 0; ?>)
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Prix -->
                                        <div class="product-price-container mb-3">
                                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                                <span class="product-price"><?php echo formatPrice($product['sale_price']); ?></span>
                                                <span class="product-original-price"><?php echo formatPrice($product['price']); ?></span>
                                            <?php else: ?>
                                                <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Attributs produit -->
                                        <div class="product-attributes mb-3">
                                            <?php if ($product['color']): ?>
                                                <span class="product-attribute">
                                                    <i class="fas fa-palette me-1"></i>
                                                    <?php echo htmlspecialchars($product['color']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($product['size']): ?>
                                                <span class="product-attribute">
                                                    <i class="fas fa-ruler me-1"></i>
                                                    <?php echo htmlspecialchars($product['size']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Stock et livraison -->
                                        <div class="product-status mb-3">
                                            <?php if ($product['stock'] > 0): ?>
                                                <div class="stock-status text-success">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    En stock (<?php echo $product['stock']; ?>)
                                                </div>
                                            <?php else: ?>
                                                <div class="stock-status text-danger">
                                                    <i class="fas fa-times-circle me-1"></i>
                                                    Rupture de stock
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="shipping-info text-muted small">
                                                <i class="fas fa-truck me-1"></i>
                                                Livraison gratuite dès 50€
                                            </div>
                                        </div>
                                        
                                        <!-- Bouton d'ajout au panier -->
                                        <div class="mt-auto">
                                            <?php if ($product['stock'] > 0): ?>
                                                <form method="POST" action="cart.php" class="add-to-cart-form" onClick="event.stopPropagation();">
                                                    <input type="hidden" name="action" value="add">
                                                    <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                                                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($product['name']); ?>">
                                                    <input type="hidden" name="price" value="<?php echo (float)($product['sale_price'] ?: $product['price']); ?>">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="btn-add-to-cart w-100">
                                                        <i class="fas fa-shopping-cart me-2"></i>
                                                        Ajouter au panier
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary w-100" disabled>
                                                    <i class="fas fa-times me-2"></i>
                                                    Indisponible
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Bouton "Voir plus" pour pagination infinie -->
                    <?php if ($totalPages > $page): ?>
                        <div class="text-center mt-5">
                            <button class="btn btn-outline-primary btn-lg" id="loadMoreBtn" 
                                    data-page="<?php echo $page + 1; ?>" data-total="<?php echo $totalPages; ?>">
                                <i class="fas fa-plus me-2"></i>
                                Voir plus de produits
                                <span class="badge bg-primary ms-2"><?php echo ($totalProducts - ($page * $perPage)); ?> restants</span>
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php
$pageScripts = '
<script>
// Fonctionnalités pour la page des produits
document.addEventListener("DOMContentLoaded", function() {
    // Gestion des filtres
    setupFilters();
    
    // Gestion du tri
    setupSorting();
    
    // Gestion des vues (grille/liste)
    setupViewToggle();
    
    // Pagination infinie
    setupInfiniteScroll();
    
    // Fonctionnalités produits
    setupProductFeatures();
});

function setupFilters() {
    const filterInputs = document.querySelectorAll(".product-filter");
    
    filterInputs.forEach(input => {
        input.addEventListener("change", function() {
            applyFilters();
        });
    });
    
    // Range slider pour les prix
    const priceRange = document.getElementById("priceRange");
    if (priceRange) {
        priceRange.addEventListener("input", debounce(applyFilters, 500));
    }
}

function setupSorting() {
    const sortSelect = document.getElementById("sortSelect");
    if (sortSelect) {
        sortSelect.addEventListener("change", function() {
            applyFilters();
        });
    }
}

function setupViewToggle() {
    const viewButtons = document.querySelectorAll(".view-toggle button");
    const productsGrid = document.getElementById("productsGrid");
    
    viewButtons.forEach(button => {
        button.addEventListener("click", function() {
            const view = this.dataset.view;
            
            // Update active button
            viewButtons.forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");
            
            // Update grid layout
            if (view === "list") {
                productsGrid.classList.add("products-list-view");
                productsGrid.querySelectorAll(".product-col").forEach(col => {
                    col.classList.remove("col-6", "col-md-4", "col-lg-4");
                    col.classList.add("col-12");
                });
            } else {
                productsGrid.classList.remove("products-list-view");
                productsGrid.querySelectorAll(".product-col").forEach(col => {
                    col.classList.remove("col-12");
                    col.classList.add("col-6", "col-md-4", "col-lg-4");
                });
            }
            
            // Store preference
            localStorage.setItem("productsView", view);
        });
    });
    
    // Restore saved view
    const savedView = localStorage.getItem("productsView");
    if (savedView) {
        const button = document.querySelector(`[data-view="${savedView}"]`);
        if (button) button.click();
    }
}

function setupInfiniteScroll() {
    const loadMoreBtn = document.getElementById("loadMoreBtn");
    if (!loadMoreBtn) return;
    
    loadMoreBtn.addEventListener("click", async function() {
        const page = parseInt(this.dataset.page);
        const total = parseInt(this.dataset.total);
        
        try {
            this.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>Chargement...`;
            this.disabled = true;
            
            const url = new URL(window.location);
            url.searchParams.set("page", page);
            url.searchParams.set("ajax", "1");
            
            const response = await fetch(url.toString());
            const html = await response.text();
            
            // Parse the response and extract products
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const newProducts = doc.querySelectorAll(".product-col");
            
            // Add new products with animation
            const productsGrid = document.getElementById("productsGrid");
            newProducts.forEach((product, index) => {
                product.style.animationDelay = `${index * 0.1}s`;
                product.classList.add("animate-fade-in-up");
                productsGrid.appendChild(product);
            });
            
            // Update button
            if (page >= total) {
                this.style.display = "none";
            } else {
                this.dataset.page = page + 1;
                this.innerHTML = `<i class="fas fa-plus me-2"></i>Voir plus de produits`;
                this.disabled = false;
            }
            
        } catch (error) {
            this.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>Erreur de chargement`;
            setTimeout(() => {
                this.innerHTML = `<i class="fas fa-plus me-2"></i>Voir plus de produits`;
                this.disabled = false;
            }, 3000);
        }
    });
}

function setupProductFeatures() {
    // Vue rapide
    window.quickView = function(productId) {
        if (window.modernEcommerce && window.modernEcommerce.openQuickView) {
            window.modernEcommerce.openQuickView(productId);
        }
    };
    
    // Comparaison de produits
    window.compareProduct = function(productId) {
        // À implémenter selon vos besoins
        console.log("Compare product:", productId);
    };
}

function applyFilters() {
    const form = document.getElementById("filtersForm");
    const formData = new FormData(form);
    const sortSelect = document.getElementById("sortSelect");
    
    // Add sort parameter
    if (sortSelect) {
        formData.set("sort", sortSelect.value);
    }
    
    // Build URL with parameters
    const url = new URL(window.location.href.split("?")[0], window.location.origin);
    for (const [key, value] of formData.entries()) {
        if (value) {
            url.searchParams.set(key, value);
        }
    }
    
    // Navigate to filtered results
    window.location.href = url.toString();
}

function clearAllFilters() {
    window.location.href = window.location.href.split("?")[0];
}

function removeFilter(filterName) {
    const url = new URL(window.location);
    url.searchParams.delete(filterName);
    window.location.href = url.toString();
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Mobile filters toggle
function toggleMobileFilters() {
    const sidebar = document.querySelector(".filters-sidebar");
    const overlay = document.querySelector(".filters-overlay");
    
    if (sidebar && overlay) {
        sidebar.classList.toggle("show");
        overlay.classList.toggle("show");
    }
}
</script>

<style>
/* Styles supplémentaires pour la page produits */
.products-header {
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
}

.section-title {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.active-filters .badge {
    transition: all 0.2s ease;
}

.active-filters .badge:hover {
    transform: scale(1.05);
}

.empty-state-icon {
    animation: pulse 2s infinite;
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.product-actions {
    display: flex;
    gap: 0.5rem;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.875rem;
}

.product-brand {
    font-weight: 600;
    color: var(--primary-color);
}

.product-category {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.product-attributes {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.product-attribute {
    background: var(--bg-tertiary);
    color: var(--text-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius-sm);
    font-size: 0.75rem;
    display: flex;
    align-items: center;
}

.stock-status {
    font-size: 0.875rem;
    font-weight: 500;
}

.shipping-info {
    margin-top: 0.25rem;
}

.products-list-view .product-card {
    display: flex;
    flex-direction: row;
    align-items: center;
}

.products-list-view .product-image-container {
    width: 200px;
    min-width: 200px;
    margin-right: 1rem;
    margin-bottom: 0;
}

.products-list-view .product-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

@media (max-width: 768px) {
    .filters-sidebar {
        position: fixed;
        top: 0;
        left: -300px;
        width: 300px;
        height: 100vh;
        background: white;
        z-index: 1050;
        transition: left 0.3s ease;
        overflow-y: auto;
    }
    
    .filters-sidebar.show {
        left: 0;
    }
    
    .filters-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .filters-overlay.show {
        opacity: 1;
        visibility: visible;
    }
    
    .products-list-view .product-card {
        flex-direction: column;
    }
    
    .products-list-view .product-image-container {
        width: 100%;
        margin-right: 0;
        margin-bottom: 1rem;
    }
}
</style>
';

include 'includes/footer.php'; ?>