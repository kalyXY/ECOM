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

// Récupérer toutes les tailles disponibles
$allSizes = [];
try {
    $stmt = $pdo->query("SELECT * FROM sizes ORDER BY category ASC, sort_order ASC, name ASC");
    $allSizes = $stmt->fetchAll();
} catch (PDOException $e) {
    // Table sizes n'existe pas
}

// Récupérer les tailles actuellement assignées au produit
$productSizes = [];
try {
    $stmt = $pdo->prepare("
        SELECT ps.*, s.name as size_name, s.category as size_category 
        FROM product_sizes ps 
        JOIN sizes s ON ps.size_id = s.id 
        WHERE ps.product_id = ? 
        ORDER BY s.sort_order ASC, s.name ASC
    ");
    $stmt->execute([$productId]);
    $productSizes = $stmt->fetchAll();
} catch (PDOException $e) {
    // Table product_sizes n'existe pas
}

// Récupérer toutes les couleurs disponibles
$allColors = [];
try {
    $stmt = $pdo->query("SELECT * FROM colors ORDER BY name ASC");
    $allColors = $stmt->fetchAll();
} catch (PDOException $e) {
    // Table colors n'existe pas
}

// Récupérer les couleurs actuellement assignées au produit
$productColors = [];
try {
    $stmt = $pdo->prepare("
        SELECT pc.*, c.name as color_name, c.hex_code 
        FROM product_colors pc 
        JOIN colors c ON pc.color_id = c.id 
        WHERE pc.product_id = ?
        ORDER BY c.name ASC
    ");
    $stmt->execute([$productId]);
    $productColors = $stmt->fetchAll();
} catch (PDOException $e) {
    // Table product_colors n'existe pas
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token CSRF invalide.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'update_sizes':
                $selectedSizes = $_POST['sizes'] ?? [];
                $sizeStocks = $_POST['size_stock'] ?? [];
                
                try {
                    $pdo->beginTransaction();
                    
                    // Supprimer toutes les tailles actuelles
                    $stmt = $pdo->prepare("DELETE FROM product_sizes WHERE product_id = ?");
                    $stmt->execute([$productId]);
                    
                    // Ajouter les nouvelles tailles
                    if (!empty($selectedSizes)) {
                        $stmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size_id, stock) VALUES (?, ?, ?)");
                        foreach ($selectedSizes as $sizeId) {
                            $stock = isset($sizeStocks[$sizeId]) && $sizeStocks[$sizeId] !== '' ? (int)$sizeStocks[$sizeId] : null;
                            $stmt->execute([$productId, (int)$sizeId, $stock]);
                        }
                    }
                    
                    $pdo->commit();
                    $success = "Tailles mises à jour avec succès.";
                    
                    // Recharger les tailles du produit
                    $stmt = $pdo->prepare("
                        SELECT ps.*, s.name as size_name, s.category as size_category 
                        FROM product_sizes ps 
                        JOIN sizes s ON ps.size_id = s.id 
                        WHERE ps.product_id = ? 
                        ORDER BY s.sort_order ASC, s.name ASC
                    ");
                    $stmt->execute([$productId]);
                    $productSizes = $stmt->fetchAll();
                    
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $errors[] = "Erreur lors de la mise à jour des tailles.";
                }
                break;
                
            case 'update_colors':
                $selectedColors = $_POST['colors'] ?? [];
                
                try {
                    $pdo->beginTransaction();
                    
                    // Supprimer toutes les couleurs actuelles
                    $stmt = $pdo->prepare("DELETE FROM product_colors WHERE product_id = ?");
                    $stmt->execute([$productId]);
                    
                    // Ajouter les nouvelles couleurs
                    if (!empty($selectedColors)) {
                        $stmt = $pdo->prepare("INSERT INTO product_colors (product_id, color_id) VALUES (?, ?)");
                        foreach ($selectedColors as $colorId) {
                            $stmt->execute([$productId, (int)$colorId]);
                        }
                    }
                    
                    $pdo->commit();
                    $success = "Couleurs mises à jour avec succès.";
                    
                    // Recharger les couleurs du produit
                    $stmt = $pdo->prepare("
                        SELECT pc.*, c.name as color_name, c.hex_code 
                        FROM product_colors pc 
                        JOIN colors c ON pc.color_id = c.id 
                        WHERE pc.product_id = ?
                        ORDER BY c.name ASC
                    ");
                    $stmt->execute([$productId]);
                    $productColors = $stmt->fetchAll();
                    
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $errors[] = "Erreur lors de la mise à jour des couleurs.";
                }
                break;
                
            case 'update_stock':
                $sizeId = (int)($_POST['size_id'] ?? 0);
                $newStock = $_POST['new_stock'] !== '' ? (int)$_POST['new_stock'] : null;
                
                if ($sizeId) {
                    try {
                        $stmt = $pdo->prepare("UPDATE product_sizes SET stock = ? WHERE product_id = ? AND size_id = ?");
                        $stmt->execute([$newStock, $productId, $sizeId]);
                        $success = "Stock mis à jour pour la taille.";
                        
                        // Recharger les tailles du produit
                        $stmt = $pdo->prepare("
                            SELECT ps.*, s.name as size_name, s.category as size_category 
                            FROM product_sizes ps 
                            JOIN sizes s ON ps.size_id = s.id 
                            WHERE ps.product_id = ? 
                            ORDER BY s.sort_order ASC, s.name ASC
                        ");
                        $stmt->execute([$productId]);
                        $productSizes = $stmt->fetchAll();
                        
                    } catch (PDOException $e) {
                        $errors[] = "Erreur lors de la mise à jour du stock.";
                    }
                }
                break;
        }
    }
}

// Organiser les tailles par catégorie
$sizesByCategory = [];
foreach ($allSizes as $size) {
    $sizesByCategory[$size['category']][] = $size;
}

// IDs des tailles actuellement sélectionnées
$selectedSizeIds = array_column($productSizes, 'size_id');
$selectedColorIds = array_column($productColors, 'color_id');

$pageTitle = 'Gérer les variantes - ' . htmlspecialchars($product['name']);
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
                        <h1 class="page-title">Gérer les variantes</h1>
                        <p class="page-subtitle">Produit : <?php echo htmlspecialchars($product['name']); ?></p>
                    </div>
                    <div class="btn-group">
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                        </a>
                        <a href="edit_product.php?id=<?php echo $productId; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-2"></i>Modifier le produit
                        </a>
                        <a href="manage_product_images.php?id=<?php echo $productId; ?>" class="btn btn-outline-info">
                            <i class="fas fa-images me-2"></i>Gérer les images
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
                <!-- Gestion des tailles -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-ruler-combined me-2"></i>Tailles disponibles
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="update_sizes">
                                
                                <?php if (empty($sizesByCategory)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-ruler fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted">Aucune taille configurée</h6>
                                        <p class="text-muted">Configurez d'abord les tailles dans la section "Tailles & Couleurs".</p>
                                        <a href="sizes_colors.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Configurer les tailles
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($sizesByCategory as $category => $sizes): ?>
                                        <div class="mb-4">
                                            <h6 class="text-muted text-uppercase mb-3"><?php echo ucfirst($category); ?></h6>
                                            <div class="row">
                                                <?php foreach ($sizes as $size): ?>
                                                    <?php $isSelected = in_array($size['id'], $selectedSizeIds); ?>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card border <?php echo $isSelected ? 'border-primary' : ''; ?>">
                                                            <div class="card-body p-3">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" 
                                                                           type="checkbox" 
                                                                           name="sizes[]" 
                                                                           value="<?php echo $size['id']; ?>"
                                                                           id="size_<?php echo $size['id']; ?>"
                                                                           <?php echo $isSelected ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label fw-medium" for="size_<?php echo $size['id']; ?>">
                                                                        <?php echo htmlspecialchars($size['name']); ?>
                                                                    </label>
                                                                </div>
                                                                
                                                                <div class="mt-2">
                                                                    <label class="form-label small text-muted">Stock spécifique (optionnel)</label>
                                                                    <input type="number" 
                                                                           class="form-control form-control-sm" 
                                                                           name="size_stock[<?php echo $size['id']; ?>]" 
                                                                           placeholder="Laisser vide pour utiliser le stock global"
                                                                           min="0"
                                                                           value="<?php 
                                                                               foreach ($productSizes as $ps) {
                                                                                   if ($ps['size_id'] == $size['id']) {
                                                                                       echo $ps['stock'] !== null ? $ps['stock'] : '';
                                                                                       break;
                                                                                   }
                                                                               }
                                                                           ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Sauvegarder les tailles
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Gestion des couleurs -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-palette me-2"></i>Couleurs disponibles
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($allColors)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-palette fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Aucune couleur configurée</h6>
                                    <p class="text-muted">Configurez d'abord les couleurs dans la section "Tailles & Couleurs".</p>
                                    <a href="sizes_colors.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Configurer les couleurs
                                    </a>
                                </div>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="update_colors">
                                    
                                    <div class="row">
                                        <?php foreach ($allColors as $color): ?>
                                            <?php $isSelected = in_array($color['id'], $selectedColorIds); ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card border <?php echo $isSelected ? 'border-primary' : ''; ?>">
                                                    <div class="card-body p-3">
                                                        <div class="form-check d-flex align-items-center">
                                                            <input class="form-check-input me-3" 
                                                                   type="checkbox" 
                                                                   name="colors[]" 
                                                                   value="<?php echo $color['id']; ?>"
                                                                   id="color_<?php echo $color['id']; ?>"
                                                                   <?php echo $isSelected ? 'checked' : ''; ?>>
                                                            <div class="color-swatch me-3" 
                                                                 style="width: 30px; height: 30px; background-color: <?php echo htmlspecialchars($color['hex_code']); ?>; border: 1px solid #dee2e6; border-radius: 50%;"></div>
                                                            <label class="form-check-label fw-medium" for="color_<?php echo $color['id']; ?>">
                                                                <?php echo htmlspecialchars($color['name']); ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Sauvegarder les couleurs
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Récapitulatif des variantes -->
            <?php if (!empty($productSizes) || !empty($productColors)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Récapitulatif des variantes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Tailles actuelles -->
                            <?php if (!empty($productSizes)): ?>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Tailles configurées (<?php echo count($productSizes); ?>)</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Taille</th>
                                                    <th>Catégorie</th>
                                                    <th>Stock</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($productSizes as $ps): ?>
                                                    <tr>
                                                        <td><strong><?php echo htmlspecialchars($ps['size_name']); ?></strong></td>
                                                        <td><span class="badge bg-secondary"><?php echo ucfirst($ps['size_category']); ?></span></td>
                                                        <td>
                                                            <?php if ($ps['stock'] !== null): ?>
                                                                <span class="badge bg-<?php echo $ps['stock'] > 0 ? 'success' : 'danger'; ?>">
                                                                    <?php echo $ps['stock']; ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted small">Global</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <button type="button" 
                                                                    class="btn btn-outline-primary btn-sm" 
                                                                    onclick="editSizeStock(<?php echo $ps['size_id']; ?>, '<?php echo htmlspecialchars($ps['size_name']); ?>', <?php echo $ps['stock'] ?? 'null'; ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Couleurs actuelles -->
                            <?php if (!empty($productColors)): ?>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Couleurs configurées (<?php echo count($productColors); ?>)</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($productColors as $pc): ?>
                                            <div class="d-flex align-items-center bg-light rounded p-2">
                                                <div class="color-swatch me-2" 
                                                     style="width: 20px; height: 20px; background-color: <?php echo htmlspecialchars($pc['hex_code']); ?>; border: 1px solid #dee2e6; border-radius: 50%;"></div>
                                                <small><?php echo htmlspecialchars($pc['color_name']); ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal pour éditer le stock d'une taille -->
<div class="modal fade" id="editStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_stock">
                    <input type="hidden" name="size_id" id="editSizeId">
                    
                    <div class="mb-3">
                        <label class="form-label">Taille : <strong id="editSizeName"></strong></label>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editStock" class="form-label">Stock spécifique</label>
                        <input type="number" class="form-control" id="editStock" name="new_stock" min="0" placeholder="Laisser vide pour utiliser le stock global">
                        <div class="form-text">Laissez vide pour utiliser le stock global du produit (<?php echo $product['stock'] ?? 0; ?>)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSizeStock(sizeId, sizeName, currentStock) {
    document.getElementById('editSizeId').value = sizeId;
    document.getElementById('editSizeName').textContent = sizeName;
    document.getElementById('editStock').value = currentStock === null ? '' : currentStock;
    
    new bootstrap.Modal(document.getElementById('editStockModal')).show();
}

// Auto-submit pour les checkboxes (optionnel)
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Ajouter une classe visuelle pour indiquer les changements
            this.closest('.card').classList.toggle('border-primary', this.checked);
        });
    });
});
</script>

<?php include 'layouts/footer.php'; ?>