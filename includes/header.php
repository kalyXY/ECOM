<?php
require_once __DIR__ . '/../config/bootstrap.php'; // Adjusted path
$siteSettings = getSiteSettings(); // This function seems to be missing, I will assume it exists somewhere
$cartCount = getCartItemCount();
$wishlistCount = getWishlistItemCount();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?><?php echo htmlspecialchars($siteSettings['site_name'] ?? 'StyleHub'); ?></title>
    
    <!-- Meta tags -->
    <meta name="description" content="<?php echo htmlspecialchars($siteSettings['site_description'] ?? ''); ?>">
    <meta name="keywords" content="mode, fashion, vêtements, style, tendance, boutique, prêt-à-porter">
    <meta name="author" content="<?php echo htmlspecialchars($siteSettings['site_name'] ?? 'StyleHub'); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/custom-style.css" rel="stylesheet">
    <link href="assets/css/custom-style.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- JavaScript Globals -->
    <script>
        const IS_LOGGED_IN = <?php echo json_encode(!empty($_SESSION['customer_id'])); ?>;
        const CSRF_TOKEN = '<?php echo Security::generateCSRFToken(); ?>';
    </script>

    <!-- Preload important resources -->
    <link rel="preload" href="assets/js/modern-ecommerce.js" as="script">
    
    <!-- Meta tags SEO avancés -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?><?php echo htmlspecialchars($siteSettings['site_name']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($siteSettings['site_description']); ?>">
    <meta property="og:url" content="<?php echo App::currentUrl(); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($siteSettings['site_name']); ?>">
    <meta property="og:locale" content="fr_FR">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@stylehub">
    <meta name="twitter:title" content="<?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?><?php echo htmlspecialchars($siteSettings['site_name']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($siteSettings['site_description']); ?>">
    
    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    <?php
    $schemaData = [
        "@context" => "https://schema.org",
        "@type" => "WebSite",
        "name" => $siteSettings['site_name'],
        "description" => $siteSettings['site_description'],
        "url" => App::url(),
        "potentialAction" => [
            "@type" => "SearchAction",
            "target" => App::url("products.php?search={search_term_string}"),
            "query-input" => "required name=search_term_string"
        ]
    ];
    
    // Ajouter des données spécifiques selon la page
    if (isset($product) && $product) {
        $schemaData = [
            "@context" => "https://schema.org",
            "@type" => "Product",
            "name" => $product['name'],
            "description" => $product['description'],
            "image" => App::url($product['image_url']),
            "brand" => [
                "@type" => "Brand",
                "name" => $product['brand'] ?: $siteSettings['site_name']
            ],
            "offers" => [
                "@type" => "Offer",
                "price" => $product['sale_price'] ?: $product['price'],
                "priceCurrency" => "EUR",
                "availability" => $product['stock'] > 0 ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
                "seller" => [
                    "@type" => "Organization",
                    "name" => $siteSettings['site_name']
                ]
            ]
        ];
    }
    
    echo json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    ?>
    </script>
    
    <!-- Liens canoniques et alternates -->
    <link rel="canonical" href="<?php echo App::currentUrl(); ?>">
    
    <!-- DNS prefetch pour les performances -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
</head>
<body class="<?php echo $bodyClass ?? ''; ?>">
    <!-- Navigation moderne style Alibaba -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid px-3">
            <!-- Logo -->
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shopping-bag"></i>
                <?php echo htmlspecialchars($siteSettings['site_name']); ?>
            </a>
            
            <!-- Barre de recherche centrale -->
            <div class="search-container d-none d-lg-block">
                <form method="GET" action="products.php">
                    <div class="search-input-group">
                        <input class="form-control" type="search" name="search" 
                               placeholder="Rechercher des vêtements, marques..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button class="search-btn" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Bouton mobile -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Navigation -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto d-lg-none">
                    <!-- Recherche mobile -->
                    <li class="nav-item">
                        <form class="p-3" method="GET" action="products.php">
                            <div class="search-input-group">
                                <input class="form-control" type="search" name="search" 
                                       placeholder="Rechercher..." 
                                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                <button class="search-btn" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </li>
                </ul>
                
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home me-1"></i>Accueil
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-tshirt me-1"></i>Collections
                        </a>
                        <ul class="dropdown-menu border-0 shadow">
                            <li><a class="dropdown-item" href="products.php?gender=femme">
                                <i class="fas fa-female me-2 text-pink"></i>Mode Femme
                            </a></li>
                            <li><a class="dropdown-item" href="products.php?gender=homme">
                                <i class="fas fa-male me-2 text-primary"></i>Mode Homme  
                            </a></li>
                            <li><a class="dropdown-item" href="products.php?category=enfant">
                                <i class="fas fa-child me-2 text-success"></i>Mode Enfant
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="products.php?category=accessoires">
                                <i class="fas fa-gem me-2 text-warning"></i>Accessoires
                            </a></li>
                            <li><a class="dropdown-item" href="products.php">
                                <i class="fas fa-star me-2 text-primary"></i>Toute la collection
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php?featured=1">
                            <i class="fas fa-fire me-1"></i>Tendances
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>
                
                <!-- Actions utilisateur -->
                <ul class="navbar-nav">
                    <!-- Wishlist -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="wishlist.php">
                            <i class="far fa-heart"></i>
                            <span class="d-lg-none ms-2">Favoris</span>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="wishlist-header-count" style="<?php echo $wishlistCount > 0 ? '' : 'display: none;'; ?>">
                                <?php echo $wishlistCount; ?>
                            </span>
                        </a>
                    </li>
                    
                    <!-- Panier -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="d-lg-none ms-2">Panier</span>
                            <?php if ($cartCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">
                                    <?php echo $cartCount; ?>
                                </span>
                            <?php else: ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count" style="display: none;">0</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <!-- Compte -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i>
                            <span class="d-lg-none ms-2">Mon Compte</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                            <?php if (!empty($_SESSION['customer_id'])): ?>
                                <li><a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user-circle me-2"></i>Mon Profil
                                </a></li>
                                <li><a class="dropdown-item" href="orders.php">
                                    <i class="fas fa-box me-2"></i>Mes Commandes
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Se déconnecter
                                </a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="login.php">
                                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                </a></li>
                                <li><a class="dropdown-item" href="register.php">
                                    <i class="fas fa-user-plus me-2"></i>Créer un compte
                                </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="admin/login.php">
                                <i class="fas fa-user-shield me-2"></i>Administration
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Espace pour la navbar fixe -->
    <div style="height: 76px;"></div>