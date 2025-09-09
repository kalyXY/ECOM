<?php
require_once 'config/bootstrap.php';
require_once 'models/Product.php';

// Initialiser le modèle Product
$productModel = new Product($pdo);

// Gérer les actions de la wishlist
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);
    
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide.';
        $messageType = 'danger';
    } else {
        switch ($action) {
            case 'add':
                if ($productId > 0) {
                    $result = addToWishlist($pdo, $productId);
                    $message = $result['message'];
                    $messageType = $result['success'] ? 'success' : 'danger';
                }
                break;
                
            case 'remove':
                if ($productId > 0) {
                    $result = removeFromWishlist($pdo, $productId);
                    $message = $result['message'];
                    $messageType = $result['success'] ? 'success' : 'info';
                }
                break;
                
            case 'clear':
                $result = clearWishlist($pdo);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'danger';
                break;
        }
    }
}

// Récupérer les produits de la wishlist
$wishlistItems = getWishlistItems($pdo);
$wishlistCount = count($wishlistItems);

// Fonctions pour gérer la wishlist
function addToWishlist($pdo, $productId) {
    $sessionId = getOrCreateSessionId();
    
    try {
        // Vérifier si le produit existe
        $stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            return ['success' => false, 'message' => 'Produit introuvable.'];
        }
        
        // Vérifier si déjà dans la wishlist
        $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Ce produit est déjà dans vos favoris.'];
        }
        
        // Ajouter à la wishlist
        $stmt = $pdo->prepare("INSERT INTO wishlists (session_id, product_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$sessionId, $productId]);
        
        return ['success' => true, 'message' => 'Produit ajouté à vos favoris !'];
        
    } catch (PDOException $e) {
        error_log("Error adding to wishlist: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de l\'ajout aux favoris.'];
    }
}

function removeFromWishlist($pdo, $productId) {
    $sessionId = getOrCreateSessionId();
    
    try {
        $stmt = $pdo->prepare("DELETE FROM wishlists WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Produit retiré de vos favoris.'];
        } else {
            return ['success' => false, 'message' => 'Produit non trouvé dans vos favoris.'];
        }
        
    } catch (PDOException $e) {
        error_log("Error removing from wishlist: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la suppression.'];
    }
}

function clearWishlist($pdo) {
    $sessionId = getOrCreateSessionId();
    
    try {
        $stmt = $pdo->prepare("DELETE FROM wishlists WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        
        return ['success' => true, 'message' => 'Liste de favoris vidée.'];
        
    } catch (PDOException $e) {
        error_log("Error clearing wishlist: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la suppression.'];
    }
}

function getWishlistItems($pdo) {
    $sessionId = getOrCreateSessionId();
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, w.created_at as added_to_wishlist,
                   CASE WHEN p.sale_price IS NOT NULL AND p.sale_price < p.price 
                        THEN p.sale_price 
                        ELSE p.price 
                   END as effective_price,
                   CASE WHEN p.sale_price IS NOT NULL AND p.sale_price < p.price 
                        THEN ROUND(((p.price - p.sale_price) / p.price) * 100) 
                        ELSE 0 
                   END as discount_percentage
            FROM wishlists w 
            JOIN products p ON w.product_id = p.id 
            WHERE w.session_id = ? AND p.status = 'active'
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting wishlist items: " . $e->getMessage());
        return [];
    }
}

function getOrCreateSessionId() {
    if (!isset($_SESSION['wishlist_session_id'])) {
        $_SESSION['wishlist_session_id'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['wishlist_session_id'];
}

function isInWishlist($pdo, $productId) {
    $sessionId = getOrCreateSessionId();
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Favoris - StyleHub</title>
    <meta name="description" content="Retrouvez tous vos produits favoris dans votre wishlist StyleHub">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .wishlist-item {
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .wishlist-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .product-image {
            height: 200px;
            object-fit: cover;
            border-radius: 0.375rem;
        }
        
        .price-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .current-price {
            font-size: 1.25rem;
            font-weight: bold;
            color: #0d6efd;
        }
        
        .original-price {
            font-size: 1rem;
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
        
        .empty-wishlist {
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .wishlist-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .btn-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(220, 53, 69, 0.9);
            border: none;
            color: white;
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .wishlist-item:hover .btn-remove {
            opacity: 1;
        }
        
        .btn-remove:hover {
            background: #dc3545;
            transform: scale(1.1);
        }
        
        .added-date {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .stock-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 5;
        }
        
        @media (max-width: 768px) {
            .product-image {
                height: 150px;
            }
            
            .wishlist-stats {
                padding: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- En-tête de page -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h2 mb-0">
                        <i class="fas fa-heart text-danger me-3"></i>
                        Mes Favoris
                    </h1>
                    <p class="text-muted mt-2">Retrouvez tous vos produits préférés</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-md-end">
                            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                            <li class="breadcrumb-item active">Favoris</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenu principal -->
    <section class="py-5">
        <div class="container">
            <!-- Messages -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($wishlistItems)): ?>
                <!-- Wishlist vide -->
                <div class="empty-wishlist text-center">
                    <div class="mb-4">
                        <i class="far fa-heart fa-5x text-muted"></i>
                    </div>
                    <h3 class="h4 text-muted mb-3">Votre liste de favoris est vide</h3>
                    <p class="text-muted mb-4">
                        Découvrez nos produits et ajoutez vos coups de cœur à vos favoris !
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                        <a href="products.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>
                            Découvrir nos produits
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-home me-2"></i>
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Statistiques de la wishlist -->
                <div class="wishlist-stats">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="h4 mb-2">
                                <i class="fas fa-heart me-2"></i>
                                <?php echo $wishlistCount; ?> produit<?php echo $wishlistCount > 1 ? 's' : ''; ?> dans vos favoris
                            </h3>
                            <p class="mb-0 opacity-75">
                                Ajoutez vos produits préférés au panier ou partagez votre liste
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="btn-group">
                                <button type="button" class="btn btn-light" onclick="addAllToCart()">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Tout ajouter au panier
                                </button>
                                <button type="button" class="btn btn-outline-light" onclick="clearWishlist()">
                                    <i class="fas fa-trash me-2"></i>
                                    Vider la liste
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des produits favoris -->
                <div class="row g-4">
                    <?php foreach ($wishlistItems as $item): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card wishlist-item h-100 position-relative">
                                <!-- Bouton de suppression -->
                                <button type="button" class="btn-remove" onclick="removeFromWishlist(<?php echo $item['id']; ?>)" title="Retirer des favoris">
                                    <i class="fas fa-times"></i>
                                </button>
                                
                                <!-- Badge de stock -->
                                <?php if ($item['stock'] <= 0): ?>
                                    <span class="badge bg-danger stock-badge">Rupture de stock</span>
                                <?php elseif ($item['stock'] <= 5): ?>
                                    <span class="badge bg-warning stock-badge">Stock limité</span>
                                <?php endif; ?>
                                
                                <!-- Image du produit -->
                                <div class="position-relative overflow-hidden">
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                             class="card-img-top product-image" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <?php else: ?>
                                        <div class="card-img-top product-image bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Badge de réduction -->
                                    <?php if ($item['discount_percentage'] > 0): ?>
                                        <span class="position-absolute top-0 start-0 m-2 discount-badge">
                                            -<?php echo $item['discount_percentage']; ?>%
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">
                                        <a href="product_new.php?id=<?php echo $item['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                    </h5>
                                    
                                    <p class="card-text text-muted flex-grow-1">
                                        <?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>...
                                    </p>
                                    
                                    <!-- Prix -->
                                    <div class="price-display mb-3">
                                        <span class="current-price">
                                            <?php echo number_format($item['effective_price'], 2, ',', ' '); ?> €
                                        </span>
                                        <?php if ($item['discount_percentage'] > 0): ?>
                                            <span class="original-price">
                                                <?php echo number_format($item['price'], 2, ',', ' '); ?> €
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Informations supplémentaires -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="added-date">
                                            <i class="fas fa-heart text-danger me-1"></i>
                                            Ajouté le <?php echo date('d/m/Y', strtotime($item['added_to_wishlist'])); ?>
                                        </small>
                                        <?php if (!empty($item['brand'])): ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($item['brand']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="mt-auto">
                                        <div class="row g-2">
                                            <div class="col-8">
                                                <?php if ($item['stock'] > 0): ?>
                                                    <button type="button" 
                                                            class="btn btn-primary w-100" 
                                                            onclick="addToCart(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>', <?php echo $item['effective_price']; ?>)">
                                                        <i class="fas fa-shopping-cart me-1"></i>
                                                        Ajouter au panier
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-secondary w-100" disabled>
                                                        <i class="fas fa-times me-1"></i>
                                                        Indisponible
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-4">
                                                <a href="product_new.php?id=<?php echo $item['id']; ?>" 
                                                   class="btn btn-outline-primary w-100" 
                                                   title="Voir le produit">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Actions globales -->
                <div class="text-center mt-5">
                    <div class="btn-group">
                        <a href="products.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>
                            Ajouter d'autres produits
                        </a>
                        <a href="cart.php" class="btn btn-success btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Voir mon panier
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Formulaires cachés pour les actions -->
    <form id="removeForm" method="POST" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="remove">
        <input type="hidden" name="product_id" id="removeProductId">
    </form>

    <form id="clearForm" method="POST" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="clear">
    </form>

    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    
    <script>
        // Supprimer un produit de la wishlist
        function removeFromWishlist(productId) {
            if (confirm('Êtes-vous sûr de vouloir retirer ce produit de vos favoris ?')) {
                document.getElementById('removeProductId').value = productId;
                document.getElementById('removeForm').submit();
            }
        }

        // Vider toute la wishlist
        function clearWishlist() {
            if (confirm('Êtes-vous sûr de vouloir vider votre liste de favoris ?\n\nCette action est irréversible.')) {
                document.getElementById('clearForm').submit();
            }
        }

        // Ajouter tous les produits disponibles au panier
        function addAllToCart() {
            const availableProducts = document.querySelectorAll('.btn-primary[onclick*="addToCart"]');
            
            if (availableProducts.length === 0) {
                alert('Aucun produit disponible à ajouter au panier.');
                return;
            }
            
            if (confirm(`Ajouter ${availableProducts.length} produit(s) au panier ?`)) {
                let addedCount = 0;
                
                availableProducts.forEach(button => {
                    // Simuler le clic sur chaque bouton
                    button.click();
                    addedCount++;
                });
                
                // Afficher un message de confirmation
                setTimeout(() => {
                    alert(`${addedCount} produit(s) ajouté(s) au panier !`);
                }, 500);
            }
        }

        // Fonction addToCart (si pas déjà définie dans script.js)
        if (typeof addToCart !== 'function') {
            function addToCart(productId, productName, price, quantity = 1) {
                // Récupérer le panier actuel
                let cart = JSON.parse(localStorage.getItem('cart') || '[]');
                
                // Vérifier si le produit existe déjà
                const existingItem = cart.find(item => item.id === productId);
                
                if (existingItem) {
                    existingItem.quantity += quantity;
                } else {
                    cart.push({
                        id: productId,
                        name: productName,
                        price: price,
                        quantity: quantity
                    });
                }
                
                // Sauvegarder le panier
                localStorage.setItem('cart', JSON.stringify(cart));
                
                // Mettre à jour le compteur du panier
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
                
                // Afficher une notification
                showNotification(`"${productName}" ajouté au panier !`, 'success');
            }
        }

        // Fonction de notification
        function showNotification(message, type = 'info') {
            // Créer la notification
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Ajouter au DOM
            document.body.appendChild(notification);
            
            // Supprimer automatiquement après 3 secondes
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }

        // Mettre à jour le compteur du panier au chargement
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
        });
    </script>
</body>
</html>