<?php
require_once 'config.php';
requireLogin();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token CSRF invalide.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = $_POST['price'] ?? '';
        $stock = (int)($_POST['stock'] ?? 0);

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

        // Gestion de l'upload d'image
        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if (!isValidImageUpload($_FILES['image'])) {
                $errors[] = 'Image invalide. Formats acceptés : JPG, PNG, JPEG. Taille max : 2MB.';
            } else {
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $uploadPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    // Redimensionner l'image si nécessaire
                    if (function_exists('resizeImage')) {
                        resizeImage($uploadPath, $uploadPath, 800, 600);
                    }
                    $imageUrl = 'uploads/' . $fileName;
                } else {
                    $errors[] = 'Erreur lors de l\'upload de l\'image.';
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

                if ($hasStock) {
                    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, image_url) VALUES (:name, :description, :price, :stock, :image_url)");
                    $stmt->bindParam(':stock', $stock);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url) VALUES (:name, :description, :price, :image_url)");
                }
                
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':image_url', $imageUrl);
                $stmt->execute();

                $_SESSION['message'] = 'Produit ajouté avec succès.';
                $_SESSION['message_type'] = 'success';
                header('Location: products.php');
                exit();

            } catch (PDOException $e) {
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
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="image" class="form-label">
                                        <i class="fas fa-image me-1"></i>Image du produit
                                    </label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="image" 
                                           name="image" 
                                           accept="image/jpeg,image/png,image/jpg"
                                           data-preview="imagePreview">
                                    <div class="form-text">
                                        Formats acceptés : JPG, PNG, JPEG<br>
                                        Taille maximum : 2MB
                                    </div>
                                    
                                    <!-- Aperçu de l'image -->
                                    <div class="mt-3">
                                        <img id="imagePreview" 
                                             src="#" 
                                             alt="Aperçu" 
                                             class="img-thumbnail" 
                                             style="max-width: 100%; max-height: 200px; display: none;">
                                    </div>
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