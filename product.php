<?php
require_once 'includes/config.php';

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$productId = (int)$_GET['id'];

// Récupérer le produit
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id AND status = 'active'");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit();
}

// Récupérer la galerie d'images
$images = [];
try {
    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = :pid ORDER BY sort_order ASC, id ASC");
    $stmt->execute([':pid' => $productId]);
    $images = array_column($stmt->fetchAll(), 'image_url');
} catch (Exception $e) {}

// Récupérer les tailles disponibles
$availableSizes = [];
try {
    $stmt = $pdo->prepare("SELECT s.id, s.name, ps.stock FROM product_sizes ps JOIN sizes s ON ps.size_id = s.id WHERE ps.product_id = :pid ORDER BY s.sort_order ASC, s.name ASC");
    $stmt->execute([':pid' => $productId]);
    $availableSizes = $stmt->fetchAll();
} catch (Exception $e) {}

// Récupérer des produits similaires (même catégorie ou prix similaire)
$similarProducts = [];
try {
    $priceMin = $product['price'] * 0.7;
    $priceMax = $product['price'] * 1.3;
    
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE id != :id 
        AND status = 'active' 
        AND price BETWEEN :price_min AND :price_max
        ORDER BY RAND() 
        LIMIT 4
    ");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->bindParam(':price_min', $priceMin);
    $stmt->bindParam(':price_max', $priceMax);
    $stmt->execute();
    $similarProducts = $stmt->fetchAll();
    
    // Si pas assez de produits similaires, prendre les plus récents
    if (count($similarProducts) < 4) {
        $stmt = $pdo->prepare("
            SELECT * FROM products 
            WHERE id != :id AND status = 'active'
            ORDER BY created_at DESC 
            LIMIT 4
        ");
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        $similarProducts = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $similarProducts = [];
}

// Récupérer les catégories pour la navigation
$categories = [];
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    // Table categories n'existe pas
}
?>

<?php $pageTitle = $product['name']; include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="bg-light py-3">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="products.php">Produits</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </div>
    </nav>

    <!-- Détail du produit -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <!-- Galerie d'images -->
                    <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner rounded shadow">
                            <?php 
                                $gallery = $images;
                                if ($product['image_url']) { array_unshift($gallery, $product['image_url']); }
                                $gallery = array_values(array_unique($gallery));
                                if (empty($gallery)) {
                                    echo '<div class="bg-light d-flex align-items-center justify-content-center rounded shadow" style="height: 400px;"><i class="fas fa-image fa-5x text-muted"></i></div>';
                                } else {
                                    foreach ($gallery as $idx => $imgUrl) {
                                        $active = $idx === 0 ? 'active' : '';
                                        echo '<div class="carousel-item ' . $active . '">';
                                        echo '<img src="' . htmlspecialchars($imgUrl) . '" class="d-block w-100" alt="Image produit" style="max-height:500px; object-fit:contain;">';
                                        echo '</div>';
                                    }
                                }
                            ?>
                        </div>
                        <?php if (!empty($gallery) && count($gallery) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Précédent</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Suivant</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <!-- Informations du produit -->
                    <div class="product-info">
                        <h1 class="h2 mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <div class="mb-4">
                            <span class="h3 text-primary fw-bold">
                                <?php echo number_format($product['price'], 2, ',', ' '); ?> €
                            </span>
                        </div>

                        <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                            <div class="mb-3">
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>En stock (<?php echo $product['stock']; ?> disponible<?php echo $product['stock'] > 1 ? 's' : ''; ?>)
                                </span>
                            </div>
                        <?php elseif (isset($product['stock'])): ?>
                            <div class="mb-3">
                                <span class="badge bg-danger">
                                    <i class="fas fa-times me-1"></i>Rupture de stock
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h5>Description</h5>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>

                        <!-- Tailles disponibles -->
                        <?php if (!empty($availableSizes)): ?>
                        <div class="mb-4">
                            <h6>Tailles</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($availableSizes as $s): ?>
                                    <?php $inStock = is_null($s['stock']) ? ($product['stock'] ?? 0) > 0 : ((int)$s['stock'] > 0); ?>
                                    <input type="radio" class="btn-check" name="size" id="size_<?php echo $s['id']; ?>" autocomplete="off" <?php echo $inStock ? '' : 'disabled'; ?> data-stock="<?php echo $inStock ? (is_null($s['stock']) ? (int)($product['stock'] ?? 0) : (int)$s['stock']) : 0; ?>">
                                    <label class="btn btn-outline-secondary btn-sm <?php echo $inStock ? '' : 'disabled'; ?>" for="size_<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Actions -->
                        <div class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="quantity" class="form-label">Quantité</label>
                                    <select class="form-select" id="quantity" name="quantity" form="add-to-cart-form">
                                        <?php for ($i = 1; $i <= min(10, $product['stock'] ?? 10); $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-8 d-flex align-items-end">
                                    <form id="add-to-cart-form" method="POST" action="cart.php" class="w-100">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($product['name']); ?>">
                                        <input type="hidden" name="price" value="<?php echo (float)$product['sale_price'] ?: (float)$product['price']; ?>">
                                        <button type="submit" class="btn btn-primary btn-lg w-100" <?php echo (isset($product['stock']) && $product['stock'] <= 0) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-shopping-cart me-2"></i>Ajouter au panier
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Informations supplémentaires -->
                        <div class="border-top pt-4">
                            <div class="row text-center">
                                <div class="col-4">
                                    <i class="fas fa-truck fa-2x text-primary mb-2"></i>
                                    <p class="small mb-0">Livraison gratuite<br>dès 50€</p>
                                </div>
                                <div class="col-4">
                                    <i class="fas fa-undo fa-2x text-primary mb-2"></i>
                                    <p class="small mb-0">Retour gratuit<br>sous 30 jours</p>
                                </div>
                                <div class="col-4">
                                    <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                                    <p class="small mb-0">Paiement<br>sécurisé</p>
                                </div>
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
                                     alt="<?php echo htmlspecialchars($similar['name']); ?>">
                            <?php else: ?>
                                <div class="card-img-top product-image bg-light d-flex align-items-center justify-content-center">
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
                                        <a href="product.php?id=<?php echo $similar['id']; ?>" class="btn btn-outline-primary btn-sm">
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

    <?php include 'includes/footer.php'; ?>
    <script>
        // Mettre à jour le compteur du panier au chargement
        updateCartCount();
        // Adapter la quantité max en fonction de la taille sélectionnée
        (function(){
            const sizeInputs = document.querySelectorAll('input[name="size"]');
            const qty = document.getElementById('quantity');
            function updateQtyMax(stock) {
                const current = parseInt(qty.value, 10) || 1;
                const max = Math.max(1, Math.min(10, stock));
                qty.innerHTML = '';
                for (let i = 1; i <= max; i++) {
                    const opt = document.createElement('option');
                    opt.value = i; opt.textContent = i;
                    qty.appendChild(opt);
                }
                if (current <= max) qty.value = current; else qty.value = max;
            }
            sizeInputs.forEach(r => {
                r.addEventListener('change', () => {
                    const stock = parseInt(r.getAttribute('data-stock') || '0', 10);
                    updateQtyMax(stock);
                });
                if (r.checked) {
                    const stock = parseInt(r.getAttribute('data-stock') || '0', 10);
                    updateQtyMax(stock);
                }
            });
        })();
    </script>
</body>
</html>