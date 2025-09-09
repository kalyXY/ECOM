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
?>

<?php include 'includes/header.php'; ?>

<!-- Filtres et résultats -->
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar des filtres -->
        <div class="col-lg-3 col-md-4">
            <div class="filters-sidebar">
                <div class="filters-header">
                    <h5><i class="fas fa-filter me-2"></i>Filtres</h5>
                    <button class="btn btn-link btn-sm" id="clearFilters">Effacer tout</button>
                </div>
                
                <form id="filtersForm" method="GET">
                    <!-- Recherche -->
                    <?php if (!empty($filters['search'])): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>">
                    <?php endif; ?>
                    
                    <!-- Catégories -->
                    <?php if (!empty($categories)): ?>
                        <div class="filter-section">
                            <h6 class="filter-title">Catégories</h6>
                            <div class="filter-options">
                                <?php foreach ($categories as $category): ?>
                                    <label class="filter-option">
                                        <input type="radio" name="category" value="<?php echo $category['id']; ?>" 
                                               class="product-filter" <?php echo ($filters['category_id'] ?? '') == $category['id'] ? 'checked' : ''; ?>>
                                        <span class="checkmark"></span>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Genre -->
                    <div class="filter-section">
                        <h6 class="filter-title">Genre</h6>
                        <div class="filter-options">
                            <label class="filter-option">
                                <input type="radio" name="gender" value="femme" class="product-filter" 
                                       <?php echo ($filters['gender'] ?? '') === 'femme' ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Femme
                            </label>
                            <label class="filter-option">
                                <input type="radio" name="gender" value="homme" class="product-filter"
                                       <?php echo ($filters['gender'] ?? '') === 'homme' ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Homme
                            </label>
                            <label class="filter-option">
                                <input type="radio" name="gender" value="unisexe" class="product-filter"
                                       <?php echo ($filters['gender'] ?? '') === 'unisexe' ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Unisexe
                            </label>
                        </div>
                    </div>
                    
                    <!-- Prix -->
                    <div class="filter-section">
                        <h6 class="filter-title">Prix</h6>
                        <div class="price-range">
                            <div class="row g-2">
                                <div class="col">
                                    <input type="number" name="min_price" class="form-control form-control-sm" 
                                           placeholder="Min" value="<?php echo $filters['min_price'] ?? ''; ?>">
                                </div>
                                <div class="col">
                                    <input type="number" name="max_price" class="form-control form-control-sm" 
                                           placeholder="Max" value="<?php echo $filters['max_price'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Marques -->
                    <?php if (!empty($brands)): ?>
                        <div class="filter-section">
                            <h6 class="filter-title">Marques</h6>
                            <div class="filter-options scrollable">
                                <?php foreach ($brands as $brand): ?>
                                    <label class="filter-option">
                                        <input type="radio" name="brand" value="<?php echo htmlspecialchars($brand); ?>" 
                                               class="product-filter" <?php echo ($filters['brand'] ?? '') === $brand ? 'checked' : ''; ?>>
                                        <span class="checkmark"></span>
                                        <?php echo htmlspecialchars($brand); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Couleurs -->
                    <?php if (!empty($colors)): ?>
                        <div class="filter-section">
                            <h6 class="filter-title">Couleurs</h6>
                            <div class="color-options">
                                <?php foreach ($colors as $color): ?>
                                    <label class="color-option" title="<?php echo htmlspecialchars($color); ?>">
                                        <input type="radio" name="color" value="<?php echo htmlspecialchars($color); ?>" 
                                               class="product-filter" <?php echo ($filters['color'] ?? '') === $color ? 'checked' : ''; ?>>
                                        <span class="color-swatch" style="background-color: <?php echo strtolower($color); ?>"></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Tailles -->
                    <?php if (!empty($sizes)): ?>
                        <div class="filter-section">
                            <h6 class="filter-title">Tailles</h6>
                            <div class="size-options">
                                <?php foreach ($sizes as $size): ?>
                                    <label class="size-option">
                                        <input type="radio" name="size" value="<?php echo htmlspecialchars($size); ?>" 
                                               class="product-filter" <?php echo ($filters['size'] ?? '') === $size ? 'checked' : ''; ?>>
                                        <span class="size-label"><?php echo htmlspecialchars($size); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Zone des produits -->
        <div class="col-lg-9 col-md-8">
            <!-- Header des résultats -->
            <div class="results-header">
                <div class="results-info">
                    <h4><?php echo htmlspecialchars($pageTitle); ?></h4>
                    <p class="text-muted"><?php echo number_format($totalProducts); ?> produit(s) trouvé(s)</p>
                </div>
                
                <div class="results-actions">
                    <div class="view-toggle">
                        <button class="btn btn-outline-secondary btn-sm active" data-view="grid">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                    
                    <select name="sort" class="form-select form-select-sm" id="sortSelect">
                        <option value="newest" <?php echo ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : ''; ?>>Plus récents</option>
                        <option value="price_asc" <?php echo ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : ''; ?>>Prix croissant</option>
                        <option value="price_desc" <?php echo ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : ''; ?>>Prix décroissant</option>
                        <option value="name_asc" <?php echo ($filters['sort'] ?? '') === 'name_asc' ? 'selected' : ''; ?>>Nom A-Z</option>
                        <option value="popularity" <?php echo ($filters['sort'] ?? '') === 'popularity' ? 'selected' : ''; ?>>Popularité</option>
                    </select>
                </div>
            </div>
            
            <!-- Grille des produits -->
            <div class="products-grid" id="productsGrid">
                <?php if (!empty($products)): ?>
                    <div class="row g-3">
                        <?php foreach ($products as $product): ?>
                            <div class="col-6 col-md-4 col-xl-3">
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
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
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
                                                <span class="product-price"><?php echo App::formatPrice($product['sale_price']); ?></span>
                                                <span class="product-original-price"><?php echo App::formatPrice($product['price']); ?></span>
                                            <?php else: ?>
                                                <span class="product-price"><?php echo App::formatPrice($product['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Métadonnées -->
                                        <div class="product-meta">
                                            <div class="product-rating">
                                                <span class="stars">★★★★☆</span>
                                                <span>(4.2)</span>
                                            </div>
                                            <div class="product-sold"><?php echo rand(10, 500); ?> vendus</div>
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
                <?php else: ?>
                    <div class="no-products">
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5>Aucun produit trouvé</h5>
                            <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                            <button class="btn btn-primary" onclick="clearAllFilters()">
                                <i class="fas fa-refresh me-2"></i>Voir tous les produits
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="pagination-container">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        
                        if ($start > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a>
                            </li>
                            <?php if ($start > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>"><?php echo $totalPages; ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Gestion des filtres en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const filterInputs = document.querySelectorAll('.product-filter');
    const sortSelect = document.getElementById('sortSelect');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const priceInputs = document.querySelectorAll('input[name="min_price"], input[name="max_price"]');
    
    // Appliquer les filtres automatiquement
    filterInputs.forEach(input => {
        input.addEventListener('change', applyFilters);
    });
    
    // Débounce pour les champs de prix
    priceInputs.forEach(input => {
        input.addEventListener('input', debounce(applyFilters, 500));
    });
    
    if (sortSelect) {
        sortSelect.addEventListener('change', applyFilters);
    }
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearAllFilters);
    }
    
    function applyFilters() {
        const form = document.getElementById('filtersForm');
        const formData = new FormData(form);
        
        // Ajouter le tri
        if (sortSelect) {
            formData.set('sort', sortSelect.value);
        }
        
        // Ajouter les prix
        const minPrice = document.querySelector('input[name="min_price"]').value;
        const maxPrice = document.querySelector('input[name="max_price"]').value;
        
        if (minPrice) formData.set('min_price', minPrice);
        if (maxPrice) formData.set('max_price', maxPrice);
        
        // Construire l'URL
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            if (value && value.trim() !== '') {
                params.append(key, value);
            }
        }
        
        // Préserver la recherche si elle existe
        const searchParam = new URLSearchParams(window.location.search).get('search');
        if (searchParam) {
            params.set('search', searchParam);
        }
        
        // Rediriger avec les nouveaux filtres
        const newUrl = 'products.php' + (params.toString() ? '?' + params.toString() : '');
        window.location.href = newUrl;
    }
    
    function clearAllFilters() {
        // Préserver uniquement la recherche
        const searchParam = new URLSearchParams(window.location.search).get('search');
        const newUrl = searchParam ? `products.php?search=${encodeURIComponent(searchParam)}` : 'products.php';
        window.location.href = newUrl;
    }
    
    // Gestion de l'affichage grille/liste
    document.querySelectorAll('[data-view]').forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            const grid = document.getElementById('productsGrid');
            
            // Mettre à jour les boutons
            document.querySelectorAll('[data-view]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Changer la vue
            if (view === 'list') {
                grid.classList.add('list-view');
                localStorage.setItem('products_view', 'list');
            } else {
                grid.classList.remove('list-view');
                localStorage.setItem('products_view', 'grid');
            }
        });
    });
    
    // Restaurer la vue préférée
    const savedView = localStorage.getItem('products_view');
    if (savedView === 'list') {
        document.querySelector('[data-view="list"]').click();
    }
    
    // Fonction utilitaire debounce
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
});

// Fonction globale pour effacer tous les filtres
function clearAllFilters() {
    const searchParam = new URLSearchParams(window.location.search).get('search');
    const newUrl = searchParam ? `products.php?search=${encodeURIComponent(searchParam)}` : 'products.php';
    window.location.href = newUrl;
}

// Fonction pour appliquer un filtre spécifique (utile pour les liens directs)
function applyFilter(filterName, filterValue) {
    const params = new URLSearchParams(window.location.search);
    params.set(filterName, filterValue);
    window.location.href = 'products.php?' + params.toString();
}
</script>

<?php include 'includes/footer.php'; ?>