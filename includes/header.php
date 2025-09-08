<?php
$siteSettings = getSiteSettings();
$cartCount = getCartItemCount();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?><?php echo htmlspecialchars($siteSettings['site_name']); ?></title>
    
    <!-- Meta tags -->
    <meta name="description" content="<?php echo htmlspecialchars($siteSettings['site_description']); ?>">
    <meta name="keywords" content="mode, fashion, vêtements, style, tendance, boutique, prêt-à-porter">
    <meta name="author" content="<?php echo htmlspecialchars($siteSettings['site_name']); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-tshirt me-2"></i>
                <?php echo htmlspecialchars($siteSettings['site_name']); ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
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
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="products.php?category=femme"><i class="fas fa-female me-2"></i>Femme</a></li>
                            <li><a class="dropdown-item" href="products.php?category=homme"><i class="fas fa-male me-2"></i>Homme</a></li>
                            <li><a class="dropdown-item" href="products.php?category=accessoires"><i class="fas fa-gem me-2"></i>Accessoires</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="products.php"><i class="fas fa-star me-2"></i>Toutes les collections</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>
                
                <!-- Barre de recherche -->
                <form class="d-flex me-3" method="GET" action="products.php">
                    <div class="input-group">
                        <input class="form-control" type="search" name="search" placeholder="Rechercher un article..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button class="btn btn-outline-light" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <ul class="navbar-nav">
                    <!-- Panier -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Panier
                            <?php if ($cartCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">
                                    <?php echo $cartCount; ?>
                                </span>
                            <?php else: ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count" style="display: none;">0</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <!-- Admin -->
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">
                            <i class="fas fa-user-shield me-1"></i>Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Espace pour la navbar fixe -->
    <div style="height: 76px;"></div>