<?php
require_once 'config.php';
requireLogin();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'ID de produit invalide.';
    $_SESSION['message_type'] = 'danger';
    header('Location: products.php');
    exit();
}

$productId = (int)$_GET['id'];
$errors = [];
$success = '';

// Charger catégories, tailles et images existantes
$categories = [];
$sizes = [];
$productImages = [];
try { $categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll(); } catch (Exception $e) {}
try { $sizes = $pdo->query("SELECT id, name FROM sizes ORDER BY sort_order ASC, name ASC")->fetchAll(); } catch (Exception $e) {}
try {
    $stmtImgs = $pdo->prepare("SELECT id, image_url, sort_order FROM product_images WHERE product_id = :pid ORDER BY sort_order ASC, id ASC");
    $stmtImgs->execute([':pid' => $productId]);
    $productImages = $stmtImgs->fetchAll();
} catch (Exception $e) {}
// Tailles liées
$selectedSizes = [];
try {
    $stmtSel = $pdo->prepare("SELECT size_id FROM product_sizes WHERE product_id = :pid");
    $stmtSel->execute([':pid' => $productId]);
    $selectedSizes = array_map('intval', array_column($stmtSel->fetchAll(), 'size_id'));
} catch (Exception $e) {}

// Récupérer les informations du produit
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['message'] = 'Produit non trouvé.';
        $_SESSION['message_type'] = 'danger';
        header('Location: products.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['message'] = 'Erreur lors de la récupération du produit.';
    $_SESSION['message_type'] = 'danger';
    header('Location: products.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token CSRF invalide.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = $_POST['price'] ?? '';
        $stock = (int)($_POST['stock'] ?? 0);
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $postedSizes = isset($_POST['sizes']) && is_array($_POST['sizes']) ? array_map('intval', $_POST['sizes']) : [];

        // Validation
        if (empty($name)) {
            $errors[] = 'Le nom du produit est requis.';
        }

        if (empty($description)) {
            $errors[] = 'La description est requise.';
        }

        if (empty($price) || !is_numeric($price) || $price <= 0) {
            $errors[] = 'Le prix doit être un nombre positif.';
        }

        if ($stock < 0) {
            $errors[] = 'Le stock ne peut pas être négatif.';
        }

        // Gestion d'images: nouvelle principale/galerie
        $imageUrl = $product['image_url'];
        $newGallery = [];
        $maxImages = 5;
        $existingCount = count($productImages) + (!empty($product['image_url']) ? 1 : 0);
        $remaining = max(0, $maxImages - $existingCount);

        $files = [];
        if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $count = count($_FILES['images']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                $files[] = [
                    'name' => $_FILES['images']['name'][$i],
                    'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error' => $_FILES['images']['error'][$i],
                    'size' => $_FILES['images']['size'][$i]
                ];
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $files[] = $_FILES['image'];
        }

        if (count($files) > $remaining) {
            $errors[] = 'Vous pouvez ajouter au maximum ' . $remaining . ' image(s) supplémentaire(s).';
        }

        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (empty($errors) && !empty($files)) {
            foreach ($files as $file) {
                if ($file['error'] !== UPLOAD_ERR_OK) { $errors[] = 'Erreur lors de l\'upload d\'une image.'; continue; }
                if (!isValidImageUpload($file)) { $errors[] = 'Fichier image invalide.'; continue; }
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $fileName = uniqid('', true) . '.' . $ext;
                $uploadPath = $uploadDir . $fileName;
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    if (function_exists('resizeImage')) { resizeImage($uploadPath, $uploadPath, 1200, 1200); }
                    $newGallery[] = 'uploads/' . $fileName;
                } else {
                    $errors[] = 'Impossible d\'enregistrer une image.';
                }
            }
        }

        // Si pas d'erreurs, mettre à jour le produit
        if (empty($errors)) {
            try {
                // Vérifier si la colonne stock existe
                $hasStock = false;
                try {
                    $pdo->query('SELECT stock FROM products LIMIT 1');
                    $hasStock = true;
                } catch (PDOException $e) {
                    // La colonne stock n'existe pas
                }

                $pdo->beginTransaction();

                if ($hasStock) {
                    $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, stock = :stock, category_id = :category_id, image_url = :image_url WHERE id = :id");
                    $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, category_id = :category_id, image_url = :image_url WHERE id = :id");
                }

                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':price', $price);
                if ($categoryId) { $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT); } else { $stmt->bindValue(':category_id', null, PDO::PARAM_NULL); }
                $stmt->bindParam(':image_url', $imageUrl);
                $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
                $stmt->execute();

                // Mettre à jour les tailles liées
                $pdo->prepare("DELETE FROM product_sizes WHERE product_id = :pid")->execute([':pid' => $productId]);
                if (!empty($postedSizes)) {
                    $sizeStmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size_id) VALUES (:pid, :sid)");
                    foreach ($postedSizes as $sid) {
                        $sizeStmt->execute([':pid' => $productId, ':sid' => (int)$sid]);
                    }
                }

                // Ajouter les nouvelles images de galerie
                if (!empty($newGallery)) {
                    $maxSort = 0;
                    if (!empty($productImages)) {
                        $maxSort = max(array_map(function($i){ return (int)$i['sort_order']; }, $productImages));
                    }
                    $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, sort_order) VALUES (:pid, :url, :ord)");
                    foreach ($newGallery as $idx => $url) {
                        $imgStmt->execute([':pid' => $productId, ':url' => $url, ':ord' => $maxSort + $idx + 1]);
                    }
                }

                $pdo->commit();

                $_SESSION['message'] = 'Produit modifié avec succès.';
                $_SESSION['message_type'] = 'success';
                header('Location: products.php');
                exit();

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $errors[] = 'Erreur lors de la modification : ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Modifier le Produit';
$active = 'products';
include 'layouts/header.php';
?>

<div class="admin-wrapper">
    <?php include 'layouts/sidebar.php'; ?>
    
    <main class="admin-content">
        <?php include 'layouts/topbar.php'; ?>
        
        <div class="page-content">
            <!-- En-tête de page -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">Modifier le Produit</h1>
                        <p class="page-subtitle">Modifiez les informations de "<?php echo htmlspecialchars($product['name']); ?>"</p>
                    </div>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                    </a>
                </div>
            </div>

            <!-- Messages d'erreur -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Erreurs détectées :</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulaire de modification -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Informations du produit</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Nom du produit *
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? $product['name']); ?>" 
                                           required>
                                    <div class="invalid-feedback">
                                        Veuillez saisir un nom de produit.
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left me-1"></i>Description *
                                    </label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="5" 
                                              required><?php echo htmlspecialchars($_POST['description'] ?? $product['description']); ?></textarea>
                                    <div class="invalid-feedback">
                                        Veuillez saisir une description.
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price" class="form-label">
                                                <i class="fas fa-euro-sign me-1"></i>Prix (€) *
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="price" 
                                                   name="price" 
                                                   step="0.01" 
                                                   min="0" 
                                                   value="<?php echo htmlspecialchars($_POST['price'] ?? $product['price']); ?>" 
                                                   required>
                                            <div class="invalid-feedback">
                                                Veuillez saisir un prix valide.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="stock" class="form-label">
                                                <i class="fas fa-boxes me-1"></i>Stock
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="stock" 
                                                   name="stock" 
                                                   min="0" 
                                                   value="<?php echo htmlspecialchars($_POST['stock'] ?? ($product['stock'] ?? 0)); ?>">
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Quantité disponible en stock
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-images me-1"></i>Photos du produit</label>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <?php if ($product['image_url']): ?>
                                            <div class="position-relative">
                                                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                            </div>
                                        <?php endif; ?>
                                        <?php foreach ($productImages as $img): ?>
                                            <div class="position-relative">
                                                <img src="../<?php echo htmlspecialchars($img['image_url']); ?>" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                                <a href="delete_product.php?image_id=<?php echo (int)$img['id']; ?>&product_id=<?php echo (int)$productId; ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0" data-confirm="Supprimer cette image ?">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="file" class="form-control" id="images" name="images[]" accept="image/jpeg,image/png,image/jpg" multiple>
                                    <div class="form-text">Ajouter de nouvelles images (max 5, 2MB chacune)</div>
                                </div>

                                <!-- Informations produit -->
                                <div class="card bg-light mt-4">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle text-info me-1"></i>
                                            Informations
                                        </h6>
                                        <ul class="small mb-0">
                                            <li><strong>ID :</strong> <?php echo $product['id']; ?></li>
                                            <li><strong>Créé le :</strong> <?php echo formatDate($product['created_at']); ?></li>
                                            <?php if (isset($product['updated_at'])): ?>
                                            <li><strong>Modifié le :</strong> <?php echo formatDate($product['updated_at']); ?></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <i class="fas fa-save me-1"></i>
                                <small>Les modifications seront sauvegardées</small>
                            </div>
                            <div class="btn-group">
                                <a href="products.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Sauvegarder les modifications
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'layouts/footer.php'; ?>