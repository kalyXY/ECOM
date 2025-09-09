<?php
require_once 'config.php';
requireLogin();

$errors = [];
$success = '';
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$productId) {
    header('Location: products.php');
    exit();
}

// Récupérer les informations du produit
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: products.php');
        exit();
    }
} catch (PDOException $e) {
    header('Location: products.php');
    exit();
}

// Récupérer les images existantes
$existingImages = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$productId]);
    $existingImages = $stmt->fetchAll();
} catch (PDOException $e) {
    // Table n'existe pas encore
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token CSRF invalide.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'upload_images':
                // Upload de nouvelles images
                $uploadDir = '../uploads/';
                $maxImages = 5;
                $currentImageCount = count($existingImages);
                
                if (!empty($_FILES['new_images']['name'][0])) {
                    $files = [];
                    $count = count($_FILES['new_images']['name']);
                    
                    for ($i = 0; $i < $count; $i++) {
                        if ($_FILES['new_images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                            $files[] = [
                                'name' => $_FILES['new_images']['name'][$i],
                                'type' => $_FILES['new_images']['type'][$i],
                                'tmp_name' => $_FILES['new_images']['tmp_name'][$i],
                                'error' => $_FILES['new_images']['error'][$i],
                                'size' => $_FILES['new_images']['size'][$i]
                            ];
                        }
                    }
                    
                    if ($currentImageCount + count($files) > $maxImages) {
                        $errors[] = "Trop d'images. Maximum {$maxImages} images par produit.";
                    } else {
                        $uploadedImages = [];
                        
                        foreach ($files as $file) {
                            if ($file['error'] !== UPLOAD_ERR_OK) {
                                $errors[] = "Erreur d'upload pour une image (code {$file['error']}).";
                                continue;
                            }
                            
                            $validation = Security::validateImageUpload($file);
                            if (!$validation['valid']) {
                                $errors[] = "Image invalide : " . $validation['error'];
                                continue;
                            }
                            
                            $fileName = Security::generateSecureFileName($file['name']);
                            $uploadPath = $uploadDir . $fileName;
                            
                            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                                if (function_exists('resizeImage')) {
                                    resizeImage($uploadPath, $uploadPath, 1200, 1200);
                                }
                                $uploadedImages[] = 'uploads/' . $fileName;
                            } else {
                                $errors[] = "Impossible d'enregistrer une image.";
                            }
                        }
                        
                        // Enregistrer en base
                        if (!empty($uploadedImages) && empty($errors)) {
                            try {
                                $pdo->beginTransaction();
                                
                                $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?, ?, ?)");
                                foreach ($uploadedImages as $index => $imageUrl) {
                                    $sortOrder = $currentImageCount + $index;
                                    $stmt->execute([$productId, $imageUrl, $sortOrder]);
                                }
                                
                                $pdo->commit();
                                $success = count($uploadedImages) . " image(s) ajoutée(s) avec succès.";
                                
                                // Recharger les images
                                $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
                                $stmt->execute([$productId]);
                                $existingImages = $stmt->fetchAll();
                                
                            } catch (PDOException $e) {
                                $pdo->rollBack();
                                $errors[] = "Erreur lors de l'enregistrement des images.";
                                
                                // Nettoyer les fichiers uploadés
                                foreach ($uploadedImages as $imageUrl) {
                                    $filePath = '../' . $imageUrl;
                                    if (file_exists($filePath)) {
                                        unlink($filePath);
                                    }
                                }
                            }
                        }
                    }
                }
                break;
                
            case 'delete_image':
                $imageId = (int)($_POST['image_id'] ?? 0);
                if ($imageId) {
                    try {
                        $pdo->beginTransaction();
                        
                        // Récupérer l'URL de l'image
                        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE id = ? AND product_id = ?");
                        $stmt->execute([$imageId, $productId]);
                        $imageData = $stmt->fetch();
                        
                        if ($imageData) {
                            // Supprimer de la base
                            $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
                            $stmt->execute([$imageId, $productId]);
                            
                            // Supprimer le fichier
                            $filePath = '../' . $imageData['image_url'];
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                            
                            $pdo->commit();
                            $success = "Image supprimée avec succès.";
                            
                            // Recharger les images
                            $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
                            $stmt->execute([$productId]);
                            $existingImages = $stmt->fetchAll();
                        }
                        
                    } catch (PDOException $e) {
                        $pdo->rollBack();
                        $errors[] = "Erreur lors de la suppression de l'image.";
                    }
                }
                break;
                
            case 'update_main_image':
                $newMainImage = $_POST['main_image'] ?? '';
                if ($newMainImage) {
                    try {
                        $stmt = $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?");
                        $stmt->execute([$newMainImage, $productId]);
                        $success = "Image principale mise à jour.";
                        $product['image_url'] = $newMainImage;
                    } catch (PDOException $e) {
                        $errors[] = "Erreur lors de la mise à jour de l'image principale.";
                    }
                }
                break;
                
            case 'reorder_images':
                $imageOrder = json_decode($_POST['image_order'] ?? '[]', true);
                if (is_array($imageOrder)) {
                    try {
                        $pdo->beginTransaction();
                        
                        $stmt = $pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ? AND product_id = ?");
                        foreach ($imageOrder as $index => $imageId) {
                            $stmt->execute([$index, (int)$imageId, $productId]);
                        }
                        
                        $pdo->commit();
                        $success = "Ordre des images mis à jour.";
                        
                        // Recharger les images
                        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
                        $stmt->execute([$productId]);
                        $existingImages = $stmt->fetchAll();
                        
                    } catch (PDOException $e) {
                        $pdo->rollBack();
                        $errors[] = "Erreur lors de la réorganisation des images.";
                    }
                }
                break;
        }
    }
}

$pageTitle = 'Gérer les images - ' . htmlspecialchars($product['name']);
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
                        <h1 class="page-title">Gérer les images</h1>
                        <p class="page-subtitle">Produit : <?php echo htmlspecialchars($product['name']); ?></p>
                    </div>
                    <div class="btn-group">
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                        </a>
                        <a href="edit_product.php?id=<?php echo $productId; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-2"></i>Modifier le produit
                        </a>
                    </div>
                </div>
            </div>

            <!-- Messages -->
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

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Upload de nouvelles images -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-plus me-2"></i>Ajouter des images
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="upload_images">
                                
                                <div class="form-group">
                                    <label for="new_images" class="form-label">Sélectionner des images</label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="new_images" 
                                           name="new_images[]" 
                                           accept="image/jpeg,image/png,image/jpg,image/webp"
                                           multiple
                                           required>
                                    <div class="form-text">
                                        Formats : JPG, PNG, WEBP • Max <?php echo 5 - count($existingImages); ?> nouvelles images • 2MB max par image
                                    </div>
                                </div>
                                
                                <div id="newImagesPreview" class="mt-3"></div>
                                
                                <button type="submit" class="btn btn-primary w-100 mt-3" <?php echo count($existingImages) >= 5 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-upload me-2"></i>Uploader les images
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Informations -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-info-circle text-info me-2"></i>Informations
                            </h6>
                            <ul class="small mb-0">
                                <li>Maximum 5 images par produit</li>
                                <li>Taille maximale : 2MB par image</li>
                                <li>Formats supportés : JPG, PNG, WEBP</li>
                                <li>Résolution recommandée : 1200x1200px</li>
                                <li>Glissez-déposez pour réorganiser</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Gestion des images existantes -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-images me-2"></i>Images existantes (<?php echo count($existingImages); ?>/5)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($existingImages)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-image fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">Aucune image</h5>
                                    <p class="text-muted">Commencez par ajouter des images à ce produit.</p>
                                </div>
                            <?php else: ?>
                                <!-- Image principale actuelle -->
                                <?php if ($product['image_url']): ?>
                                    <div class="mb-4">
                                        <h6 class="text-muted mb-2">Image principale actuelle :</h6>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                 class="img-thumbnail" 
                                                 style="width: 80px; height: 80px; object-fit: cover;"
                                                 alt="Image principale">
                                            <div>
                                                <strong><?php echo basename($product['image_url']); ?></strong>
                                                <br>
                                                <small class="text-muted">Cette image s'affiche en premier dans la boutique</small>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                <?php endif; ?>
                                
                                <!-- Liste des images -->
                                <div id="imagesList" class="sortable-images">
                                    <?php foreach ($existingImages as $image): ?>
                                        <div class="image-item card mb-3" data-image-id="<?php echo $image['id']; ?>">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <div class="drag-handle text-muted me-2" style="cursor: move;">
                                                            <i class="fas fa-grip-vertical"></i>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <img src="../<?php echo htmlspecialchars($image['image_url']); ?>" 
                                                             class="img-thumbnail" 
                                                             style="width: 80px; height: 80px; object-fit: cover;"
                                                             alt="Image produit">
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-1"><?php echo basename($image['image_url']); ?></h6>
                                                        <small class="text-muted">
                                                            Ordre : <?php echo $image['sort_order']; ?> • 
                                                            Ajoutée le <?php echo date('d/m/Y H:i', strtotime($image['created_at'] ?? 'now')); ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="btn-group">
                                                            <button type="button" 
                                                                    class="btn btn-outline-primary btn-sm" 
                                                                    onclick="setAsMainImage('<?php echo htmlspecialchars($image['image_url']); ?>')"
                                                                    title="Définir comme image principale">
                                                                <i class="fas fa-star"></i>
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn btn-outline-info btn-sm" 
                                                                    onclick="previewImage('../<?php echo htmlspecialchars($image['image_url']); ?>')"
                                                                    title="Aperçu">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger btn-sm" 
                                                                    onclick="deleteImage(<?php echo $image['id']; ?>, '<?php echo basename($image['image_url']); ?>')"
                                                                    title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Bouton pour sauvegarder l'ordre -->
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-success" onclick="saveImageOrder()">
                                        <i class="fas fa-save me-2"></i>Sauvegarder l'ordre
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal d'aperçu -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aperçu de l'image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" class="img-fluid" alt="Aperçu">
            </div>
        </div>
    </div>
</div>

<!-- Formulaires cachés pour les actions -->
<form id="deleteImageForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="delete_image">
    <input type="hidden" name="image_id" id="deleteImageId">
</form>

<form id="mainImageForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="update_main_image">
    <input type="hidden" name="main_image" id="mainImageUrl">
</form>

<form id="reorderForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="reorder_images">
    <input type="hidden" name="image_order" id="imageOrder">
</form>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aperçu des nouvelles images
    const newImagesInput = document.getElementById('new_images');
    const previewContainer = document.getElementById('newImagesPreview');
    
    newImagesInput.addEventListener('change', function() {
        previewContainer.innerHTML = '';
        
        Array.from(this.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'position-relative d-inline-block me-2 mb-2';
                    previewDiv.innerHTML = `
                        <img src="${e.target.result}" 
                             class="img-thumbnail" 
                             style="width: 80px; height: 80px; object-fit: cover;">
                        <div class="text-center mt-1">
                            <small class="text-muted">${file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name}</small>
                        </div>
                    `;
                    previewContainer.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // Rendre la liste des images triable
    const imagesList = document.getElementById('imagesList');
    if (imagesList) {
        new Sortable(imagesList, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function(evt) {
                // Mettre à jour visuellement les numéros d'ordre
                updateOrderNumbers();
            }
        });
    }
});

function updateOrderNumbers() {
    const items = document.querySelectorAll('.image-item');
    items.forEach((item, index) => {
        const orderText = item.querySelector('.text-muted');
        if (orderText) {
            orderText.innerHTML = orderText.innerHTML.replace(/Ordre : \d+/, `Ordre : ${index}`);
        }
    });
}

function saveImageOrder() {
    const items = document.querySelectorAll('.image-item');
    const order = Array.from(items).map(item => item.dataset.imageId);
    
    document.getElementById('imageOrder').value = JSON.stringify(order);
    document.getElementById('reorderForm').submit();
}

function deleteImage(imageId, imageName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'image "${imageName}" ?\n\nCette action est irréversible.`)) {
        document.getElementById('deleteImageId').value = imageId;
        document.getElementById('deleteImageForm').submit();
    }
}

function setAsMainImage(imageUrl) {
    if (confirm('Définir cette image comme image principale du produit ?')) {
        document.getElementById('mainImageUrl').value = imageUrl;
        document.getElementById('mainImageForm').submit();
    }
}

function previewImage(imageUrl) {
    document.getElementById('previewImage').src = imageUrl;
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}
</script>

<?php include 'layouts/footer.php'; ?>