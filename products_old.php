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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - E-Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-store me-2"></i>E-Commerce
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
                        <a class="nav-link active" href="products.php">
                            <i class="fas fa-box me-1"></i>Produits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>
                
                <!-- Barre de recherche -->
                <form class="d-flex me-3" method="GET" action="products.php">
                    <div class="input-group">
                        <input class="form-control" type="search" name="search" placeholder="Rechercher..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-light" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Panier
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">0</span>
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
                <li class="breadcrumb-item active">Produits</li>
            </ol>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="container py-4">
        <div class="row">
            <!-- Sidebar avec filtres -->
            <div class="col-lg-3 mb-4">
                <div class="search-filters">
                    <h5 class="mb-3">Filtres</h5>
                    
                    <form method="GET" action="products.php">
                        <!-- Conserver la recherche -->
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>
                        
                        <!-- Filtres par genre -->
                        <div class="filter-section">
                            <label class="filter-title">Genre</label>
                            <select name="gender" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous</option>
                                <option value="femme" <?php echo ($_GET['gender'] ?? '') == 'femme' ? 'selected' : ''; ?>>Femme</option>
                                <option value="homme" <?php echo ($_GET['gender'] ?? '') == 'homme' ? 'selected' : ''; ?>>Homme</option>
                                <option value="unisexe" <?php echo ($_GET['gender'] ?? '') == 'unisexe' ? 'selected' : ''; ?>>Unisexe</option>
                            </select>
                        </div>
                        
                        <!-- Catégories -->
                        <?php if (!empty($categories)): ?>
                        <div class="filter-section">
                            <label class="filter-title">Collection</label>
                            <select name="category" class="form-select" onchange="this.form.submit()">
                                <option value="">Toutes les collections</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Filtre par prix -->
                        <div class="filter-section">
                            <label class="filter-title">Prix</label>
                            <select name="price_range" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous les prix</option>
                                <option value="0-50" <?php echo ($_GET['price_range'] ?? '') == '0-50' ? 'selected' : ''; ?>>Moins de 50€</option>
                                <option value="50-100" <?php echo ($_GET['price_range'] ?? '') == '50-100' ? 'selected' : ''; ?>>50€ - 100€</option>
                                <option value="100-200" <?php echo ($_GET['price_range'] ?? '') == '100-200' ? 'selected' : ''; ?>>100€ - 200€</option>
                                <option value="200+" <?php echo ($_GET['price_range'] ?? '') == '200+' ? 'selected' : ''; ?>>Plus de 200€</option>
                            </select>
                        </div>
                        
                        <!-- Tri -->
                        <div class="filter-section">
                            <label class="filter-title">Trier par</label>
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Plus récents</option>
                                <option value="oldest" <?php echo $sortBy == 'oldest' ? 'selected' : ''; ?>>Plus anciens</option>
                                <option value="name" <?php echo $sortBy == 'name' ? 'selected' : ''; ?>>Nom A-Z</option>
                                <option value="price_asc" <?php echo $sortBy == 'price_asc' ? 'selected' : ''; ?>>Prix croissant</option>
                                <option value="price_desc" <?php echo $sortBy == 'price_desc' ? 'selected' : ''; ?>>Prix décroissant</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Liste des produits -->
            <div class="col-lg-9">
                <!-- En-tête des résultats -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1 fashion-title"><?php echo $pageTitle; ?></h1>
                        <p class="text-muted mb-0">
                            <?php echo $totalProducts; ?> article<?php echo $totalProducts > 1 ? 's' : ''; ?> trouvé<?php echo $totalProducts > 1 ? 's' : ''; ?>
                            <?php if (!empty($search)): ?>
                                pour "<?php echo htmlspecialchars($search); ?>"
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <?php if (empty($products)): ?>
                    <!-- Aucun produit trouvé -->
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>Aucun article trouvé</h4>
                        <p class="text-muted">Essayez de modifier vos critères de recherche ou explorez nos collections.</p>
                        <a href="products.php" class="btn btn-primary">Voir Toutes les Collections</a>
                    </div>
                <?php else: ?>
                    <!-- Grille des produits -->
                    <div class="row g-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card product-card h-100">
                                    <!-- Image du produit -->
                                    <?php if ($product['image_url'] && file_exists($product['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                             class="card-img-top product-image" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <div class="card-img-top product-image bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Informations du produit -->
                                    <div class="card-body product-info d-flex flex-column">
                                        <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <p class="product-description flex-grow-1">
                                            <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                                        </p>
                                        <div class="mt-auto">
                                            <div class="product-price mb-3">
                                                <?php echo formatPrice($product['price']); ?>
                                            </div>
                                            <div class="d-grid gap-2">
                                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i>Voir le produit
                                                </a>
                                                <button class="btn btn-primary btn-add-to-cart" 
                                                        onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>)">
                                                    <i class="fas fa-shopping-cart me-1"></i>Ajouter au panier
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Navigation des pages" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <!-- Page précédente -->
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                            <i class="fas fa-chevron-left"></i> Précédent
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <!-- Numéros de pages -->
                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <!-- Page suivante -->
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                            Suivant <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><i class="fas fa-store me-2"></i>E-Commerce</h5>
                    <p class="mb-3">Votre boutique en ligne de confiance depuis 2024.</p>
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
                        <li><i class="fas fa-envelope me-2"></i>contact@ecommerce.com</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <small class="text-muted">© 2024 E-Commerce. Tous droits réservés.</small>
            </div>
        </div>
    </footer>

    <!-- Bouton retour en haut -->
    <button id="backToTop" class="btn btn-primary position-fixed bottom-0 end-0 m-3" style="display: none; z-index: 1000;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Mettre à jour le compteur du panier au chargement
        updateCartCount();
    </script>
</body>
</html>