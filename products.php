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
            <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded shadow-sm bg-white">
                <div>
                    <h4 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                    <p class="text-muted mb-0"><?php echo number_format($totalProducts); ?> produit(s)</p>
                </div>
                
                <div class="d-flex align-items-center">
                    <select name="sort" class="form-select form-select-sm me-2" id="sortSelect" style="width: auto;">
                        <option value="newest" <?php echo ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : ''; ?>>Plus récents</option>
                        <option value="price_asc" <?php echo ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : ''; ?>>Prix croissant</option>
                        <option value="price_desc" <?php echo ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : ''; ?>>Prix décroissant</option>
                        <option value="popularity" <?php echo ($filters['sort'] ?? '') === 'popularity' ? 'selected' : ''; ?>>Popularité</option>
                    </select>
                    <div class="view-toggle btn-group">
                        <button class="btn btn-outline-secondary btn-sm active" data-view="grid"><i class="fas fa-th"></i></button>
                        <button class="btn btn-outline-secondary btn-sm" data-view="list"><i class="fas fa-list"></i></button>
                    </div>
                </div>
            </div>
            
            <div class="products-grid" id="productsGrid">
                <?php if (empty($products)): ?>
                    <div class="text-center p-5 bg-white rounded shadow-sm">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5>Aucun produit ne correspond à votre recherche</h5>
                        <p class="text-muted">Essayez de modifier vos filtres ou de réinitialiser la recherche.</p>
                        <button class="btn btn-primary" onclick="clearAllFilters()">Voir tous les produits</button>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col-sm-6 col-md-4">
                                <div class="product-card h-100" onclick="location.href='product.php?id=<?php echo $product['id']; ?>'">
                                    <div class="product-image-container">
                                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'assets/images/placeholder.png'); ?>"
                                             class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
                                        <div class="product-badge">
                                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                                <span class="badge-sale">-<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                        <button class="wishlist-btn" onclick="event.stopPropagation(); toggleWishlist(<?php echo $product['id']; ?>)">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                    <div class="product-info">
                                        <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <div class="product-price-container">
                                            <span class="product-price"><?php echo App::formatPrice($product['sale_price'] ?: $product['price']); ?></span>
                                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                                <span class="product-original-price"><?php echo App::formatPrice($product['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-auto">
                                            <button class="btn btn-primary w-100" onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['sale_price'] ?: $product['price']; ?>)">
                                                Ajouter au panier
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
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

<?php include 'includes/footer.php'; ?>