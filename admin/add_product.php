<?php
require_once 'config.php';
requireLogin();

$errors = [];
$success = '';

// Charger catégories et tailles
$categories = [];
$sizes = [];
try {
    $categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    // Catégories optionnelles
}
try {
    $sizes = $pdo->query("SELECT id, name FROM sizes ORDER BY sort_order ASC, name ASC")->fetchAll();
} catch (Exception $e) {
    // Tailles optionnelles
}

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
        $selectedSizes = isset($_POST['sizes']) && is_array($_POST['sizes']) ? array_map('intval', $_POST['sizes']) : [];

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

        // Gestion des uploads d'images
        $imageUrl = null; // image principale (première)
        $galleryImages = [];
        $maxImages = 5;

        // Normaliser les inputs fichiers pour images multiples
        $files = [];
        if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $count = count($_FILES['images']['name']);
            for ($i = 0; $i < $count; $i++) {
                $files[] = [
                    'name' => $_FILES['images']['name'][$i],
                    'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error' => $_FILES['images']['error'][$i],
                    'size' => $_FILES['images']['size'][$i]
                ];
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Compat: ancien champ simple
            $files[] = $_FILES['image'];
        }

        if (count($files) > $maxImages) {
            $errors[] = 'Trop d\'images sélectionnées. Maximum : ' . $maxImages . '.';
        }

        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (empty($errors) && !empty($files)) {
            foreach ($files as $index => $file) {
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = 'Erreur d\'upload pour une image (code ' . $file['error'] . ').';
                    continue;
                }
                if (!isValidImageUpload($file)) {
                    $errors[] = 'Fichier non autorisé ou trop volumineux. Formats: JPG, PNG. Max 2MB.';
                    continue;
                }

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $fileName = uniqid('', true) . '.' . $ext;
                $uploadPath = $uploadDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    if (function_exists('resizeImage')) {
                        resizeImage($uploadPath, $uploadPath, 1200, 1200);
                    }
                    $url = 'uploads/' . $fileName;
                    if ($imageUrl === null) {
                        $imageUrl = $url; // première comme principale
                    }
                    $galleryImages[] = $url;
                } else {
                    $errors[] = 'Impossible d\'enregistrer une image.';
                }
            }
        }

        // Si pas d'erreurs, ajouter le produit
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
                    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id, image_url) VALUES (:name, :description, :price, :stock, :category_id, :image_url)");
                    $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, image_url) VALUES (:name, :description, :price, :category_id, :image_url)");
                }

                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':price', $price);
                if ($categoryId) {
                    $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(':category_id', null, PDO::PARAM_NULL);
                }
                $stmt->bindParam(':image_url', $imageUrl);
                $stmt->execute();

                $newProductId = (int)$pdo->lastInsertId();

                // Enregistrer les images supplémentaires dans product_images
                if (!empty($galleryImages)) {
                    $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, sort_order) VALUES (:product_id, :image_url, :sort_order)");
                    foreach ($galleryImages as $idx => $url) {
                        $imgStmt->execute([
                            ':product_id' => $newProductId,
                            ':image_url' => $url,
                            ':sort_order' => $idx
                        ]);
                    }
                }

                // Enregistrer les tailles sélectionnées
                if (!empty($selectedSizes)) {
                    $sizeStmt = $pdo->prepare("INSERT IGNORE INTO product_sizes (product_id, size_id) VALUES (:product_id, :size_id)");
                    foreach ($selectedSizes as $sizeId) {
                        $sizeStmt->execute([
                            ':product_id' => $newProductId,
                            ':size_id' => (int)$sizeId
                        ]);
                    }
                }

                $pdo->commit();

                $_SESSION['message'] = 'Produit ajouté avec succès.';
                $_SESSION['message_type'] = 'success';
                header('Location: products.php');
                exit();

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $errors[] = 'Erreur lors de l\'ajout : ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Ajouter un Produit';
$active = 'add_product';
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
                        <h1 class="page-title">Ajouter un Produit</h1>
                        <p class="page-subtitle">Créez un nouveau produit pour votre catalogue</p>
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

            <!-- Formulaire d'ajout -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Informations du produit</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate data-auto-save="add_product">
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
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                           required
                                           placeholder="Ex: iPhone 15 Pro">
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
                                              required
                                              placeholder="Décrivez votre produit en détail..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                    <div class="invalid-feedback">
                                        Veuillez saisir une description.
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Une description détaillée améliore les ventes
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
                                                   value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                                                   required
                                                   placeholder="0.00">
                                            <div class="invalid-feedback">
                                                Veuillez saisir un prix valide.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="stock" class="form-label">
                                                <i class="fas fa-boxes me-1"></i>Stock initial
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="stock" 
                                                   name="stock" 
                                                   min="0" 
                                                   value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>"
                                                   placeholder="0">
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Quantité disponible en stock
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="category_id" class="form-label">
                                                <i class="fas fa-layer-group me-1"></i>Catégorie
                                            </label>
                                            <select class="form-select" id="category_id" name="category_id">
                                                <option value="">-- Aucune --</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo (int)$cat['id']; ?>" <?php echo ((int)($categoryId ?? 0) === (int)$cat['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="fas fa-ruler-combined me-1"></i>Tailles disponibles
                                            </label>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php foreach ($sizes as $size): ?>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" id="size_<?php echo $size['id']; ?>" name="sizes[]" value="<?php echo (int)$size['id']; ?>" <?php echo in_array((int)$size['id'], $selectedSizes ?? []) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="size_<?php echo $size['id']; ?>"><?php echo htmlspecialchars($size['name']); ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="form-text">Sélectionnez plusieurs tailles si nécessaire</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="images" class="form-label">
                                        <i class="fas fa-images me-1"></i>Photos du produit
                                    </label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="images" 
                                           name="images[]" 
                                           accept="image/jpeg,image/png,image/jpg"
                                           multiple>
                                    <div class="form-text">
                                        Formats acceptés : JPG, PNG, JPEG. Max 5 images, 2MB chacune
                                    </div>

                                    <!-- Aperçus -->
                                    <div id="imagesPreview" class="mt-3 d-flex flex-wrap gap-2"></div>
                                </div>

                                <!-- Conseils -->
                                <div class="card bg-light mt-4">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-lightbulb text-warning me-1"></i>
                                            Conseils
                                        </h6>
                                        <ul class="small mb-0">
                                            <li>Utilisez des images de haute qualité</li>
                                            <li>Privilégiez un fond neutre</li>
                                            <li>Montrez le produit sous plusieurs angles</li>
                                            <li>Optimisez la taille pour le web</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <i class="fas fa-save me-1"></i>
                                <small>Brouillon sauvegardé automatiquement</small>
                            </div>
                            <div class="btn-group">
                                <a href="products.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Ajouter le produit
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