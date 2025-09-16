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

    <section class="py-5">
        <div class="container">
        <div class="row gx-5">
            <div class="col-lg-6">
                            <?php 
                                $gallery = $images;
                                if ($product['image_url']) { array_unshift($gallery, $product['image_url']); }
                                $gallery = array_values(array_unique($gallery));
                ?>
                <div class="product-gallery">
                    <div class="product-main-image mb-3">
                        <img src="<?php echo htmlspecialchars($gallery[0] ?? 'assets/images/placeholder.png'); ?>" alt="Image principale du produit" class="img-fluid rounded shadow-sm w-100">
                    </div>
                    <?php if (count($gallery) > 1): ?>
                    <div class="product-thumbnails d-flex gap-2">
                        <?php foreach ($gallery as $imgUrl): ?>
                            <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="Miniature produit" class="img-fluid rounded border">
                        <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-lg-6">
                <div class="product-details p-4 rounded shadow-sm bg-white">
                    <div class="position-relative mb-3">
                        <h1 class="product-detail-title text-center mb-0 w-100 pe-5"><?php echo htmlspecialchars($product['name']); ?></h1>
                        <button type="button" class="wishlist-btn icon-only position-absolute top-0 end-0" data-product-id="<?php echo $product['id']; ?>" title="Ajouter aux favoris">
                            <i class="far fa-heart"></i>
                            <span class="visually-hidden">Ajouter aux favoris</span>
                        </button>
                        </div>

                    <div class="d-flex align-items-center mb-3">
                        <span class="product-detail-price me-3"><?php echo number_format($product['price'], 2, ',', ' '); ?> €</span>
                        <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>En stock</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Épuisé</span>
                        <?php endif; ?>
                        </div>

                    <p class="product-detail-description mb-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

                    <form id="add-to-cart-form" method="POST" action="cart.php">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">

                        <?php if (!empty($availableSizes)): ?>
                        <div class="mb-4">
                            <h6 class="mb-2">Taille :</h6>
                            <div class="size-selector d-flex flex-wrap gap-2">
                                <?php foreach ($availableSizes as $s): ?>
                                    <input type="radio" class="btn-check" name="size" value="<?php echo $s['id']; ?>" id="size-<?php echo $s['id']; ?>" autocomplete="off" <?php echo ($s['stock'] > 0) ? '' : 'disabled'; ?>>
                                    <label class="btn btn-outline-dark" for="size-<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="row align-items-center mb-4">
                            <div class="col-auto">
                                <h6 class="mb-0">Quantité :</h6>
                                </div>
                            <div class="col-auto">
                                <div class="quantity-selector d-flex">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="this.nextElementSibling.stepDown()">-</button>
                                    <input type="number" name="quantity" class="form-control form-control-sm text-center" value="1" min="1" max="<?php echo $product['stock'] ?? 1; ?>" style="width: 60px;">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="this.previousElementSibling.stepUp()">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart me-2"></i>Ajouter au panier
                            </button>
                                </div>
                    </form>
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
                                        <form method="POST" action="cart.php" class="d-inline-block">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="id" value="<?php echo (int)$similar['id']; ?>">
                                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($similar['name']); ?>">
                                            <input type="hidden" name="price" value="<?php echo (float)$similar['price']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-shopping-cart me-1"></i>Panier
                                        </button>
                                        </form>
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
    <script src="assets/js/product-page.js"></script>
</body>
</html>

                <div class="product-details p-4 rounded shadow-sm bg-white">
                    <h1 class="product-detail-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                    <div class="d-flex align-items-center mb-3">
                        <span class="product-detail-price me-3"><?php echo number_format($product['price'], 2, ',', ' '); ?> €</span>
                        <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>En stock</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Épuisé</span>
                        <?php endif; ?>
                        </div>



                    <p class="product-detail-description mb-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

                    <form id="add-to-cart-form" method="POST" action="cart.php">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">

                        <?php if (!empty($availableSizes)): ?>

                        <div class="mb-4">

                            <h6 class="mb-2">Taille :</h6>
                            <div class="size-selector d-flex flex-wrap gap-2">
                                <?php foreach ($availableSizes as $s): ?>

                                    <input type="radio" class="btn-check" name="size" value="<?php echo $s['id']; ?>" id="size-<?php echo $s['id']; ?>" autocomplete="off" <?php echo ($s['stock'] > 0) ? '' : 'disabled'; ?>>
                                    <label class="btn btn-outline-dark" for="size-<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></label>
                                <?php endforeach; ?>

                            </div>

                        </div>

                        <?php endif; ?>



                        <div class="row align-items-center mb-4">
                            <div class="col-auto">
                                <h6 class="mb-0">Quantité :</h6>
                                </div>

                            <div class="col-auto">
                                <div class="quantity-selector d-flex">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="this.nextElementSibling.stepDown()">-</button>
                                    <input type="number" name="quantity" class="form-control form-control-sm text-center" value="1" min="1" max="<?php echo $product['stock'] ?? 1; ?>" style="width: 60px;">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="this.previousElementSibling.stepUp()">+</button>
                                </div>

                            </div>

                        </div>



                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart me-2"></i>Ajouter au panier
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-lg wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="far fa-heart me-2"></i>Ajouter à la wishlist
                            </button>
                                </div>

                    </form>
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
    <script src="assets/js/product-page.js"></script>
</body>

</html>
