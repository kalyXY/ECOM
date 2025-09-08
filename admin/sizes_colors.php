<?php
require_once 'config.php';
requireLogin();

// Gestion des actions AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add_size':
            $name = trim($_POST['name']);
            $category = trim($_POST['category']);
            $sort_order = (int)$_POST['sort_order'];
            
            if (!empty($name)) {
                $stmt = $pdo->prepare("INSERT INTO sizes (name, category, sort_order) VALUES (?, ?, ?)");
                $stmt->execute([$name, $category, $sort_order]);
                echo json_encode(['success' => true, 'message' => 'Taille ajoutée avec succès']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Nom requis']);
            }
            exit;
            
        case 'add_color':
            $name = trim($_POST['name']);
            $hex_code = trim($_POST['hex_code']);
            
            if (!empty($name) && !empty($hex_code)) {
                $stmt = $pdo->prepare("INSERT INTO colors (name, hex_code) VALUES (?, ?)");
                $stmt->execute([$name, $hex_code]);
                echo json_encode(['success' => true, 'message' => 'Couleur ajoutée avec succès']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Nom et code couleur requis']);
            }
            exit;
            
        case 'delete_size':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM sizes WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Taille supprimée']);
            exit;
            
        case 'delete_color':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM colors WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Couleur supprimée']);
            exit;
    }
}

// Récupérer les tailles et couleurs
try {
    $sizes = $pdo->query("SELECT * FROM sizes ORDER BY category, sort_order, name")->fetchAll();
} catch (Exception $e) {
    $sizes = [];
}

try {
    $colors = $pdo->query("SELECT * FROM colors ORDER BY name")->fetchAll();
} catch (Exception $e) {
    $colors = [];
}

$pageTitle = 'Tailles & Couleurs';
$active = 'sizes_colors';
include 'layouts/header.php';
?>

<div class="admin-wrapper">
    <?php include 'layouts/sidebar.php'; ?>
    
    <main class="admin-content">
        <?php include 'layouts/topbar.php'; ?>
        
        <div class="page-content">
            <!-- En-tête de page moderne -->
            <div class="page-header-modern">
                <div class="header-content">
                    <div class="header-title">
                        <h1 class="display-6 fw-bold">Gestion des Tailles & Couleurs</h1>
                        <p class="text-muted fs-6">Gérez les options de vos produits de mode</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Gestion des tailles -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-ruler me-2"></i>Tailles</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSizeModal">
                                <i class="fas fa-plus me-1"></i>Ajouter
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($sizes)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nom</th>
                                                <th>Catégorie</th>
                                                <th>Ordre</th>
                                                <th width="80">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sizes as $size): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($size['name']); ?></strong></td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <?php echo htmlspecialchars($size['category'] ?: 'Général'); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $size['sort_order']; ?></td>
                                                    <td>
                                                        <button class="btn btn-danger btn-sm" onclick="deleteSize(<?php echo $size['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-ruler fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucune taille configurée</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Gestion des couleurs -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-palette me-2"></i>Couleurs</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addColorModal">
                                <i class="fas fa-plus me-1"></i>Ajouter
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($colors)): ?>
                                <div class="row g-3">
                                    <?php foreach ($colors as $color): ?>
                                        <div class="col-md-6">
                                            <div class="color-item">
                                                <div class="color-preview" style="background-color: <?php echo htmlspecialchars($color['hex_code']); ?>"></div>
                                                <div class="color-info">
                                                    <div class="color-name"><?php echo htmlspecialchars($color['name']); ?></div>
                                                    <div class="color-code"><?php echo htmlspecialchars($color['hex_code']); ?></div>
                                                </div>
                                                <button class="btn btn-danger btn-sm" onclick="deleteColor(<?php echo $color['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-palette fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucune couleur configurée</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Ajouter Taille -->
<div class="modal fade" id="addSizeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une taille</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSizeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="sizeName" class="form-label">Nom de la taille *</label>
                        <input type="text" class="form-control" id="sizeName" name="name" required 
                               placeholder="Ex: S, M, L, XL, 38, 40, 42...">
                    </div>
                    <div class="mb-3">
                        <label for="sizeCategory" class="form-label">Catégorie</label>
                        <select class="form-select" id="sizeCategory" name="category">
                            <option value="">Général</option>
                            <option value="vetements">Vêtements</option>
                            <option value="chaussures">Chaussures</option>
                            <option value="accessoires">Accessoires</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sizeOrder" class="form-label">Ordre d'affichage</label>
                        <input type="number" class="form-control" id="sizeOrder" name="sort_order" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajouter Couleur -->
<div class="modal fade" id="addColorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une couleur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addColorForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="colorName" class="form-label">Nom de la couleur *</label>
                        <input type="text" class="form-control" id="colorName" name="name" required 
                               placeholder="Ex: Rouge, Bleu marine, Blanc cassé...">
                    </div>
                    <div class="mb-3">
                        <label for="colorHex" class="form-label">Code couleur *</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="colorHex" name="hex_code" 
                                   value="#000000" title="Choisir une couleur">
                            <input type="text" class="form-control" id="colorHexText" placeholder="#000000">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.color-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: white;
}

.color-preview {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: 2px solid rgba(0,0,0,0.1);
    flex-shrink: 0;
}

.color-info {
    flex: 1;
}

.color-name {
    font-weight: 500;
    color: var(--text-color);
}

.color-code {
    font-size: 12px;
    color: var(--text-muted);
    font-family: monospace;
}
</style>

<script>
// Gestion des formulaires
document.getElementById('addSizeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'add_size');
    
    fetch('sizes_colors.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('addSizeModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    });
});

document.getElementById('addColorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'add_color');
    
    fetch('sizes_colors.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('addColorModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    });
});

// Synchronisation des champs couleur
document.getElementById('colorHex').addEventListener('change', function() {
    document.getElementById('colorHexText').value = this.value;
});

document.getElementById('colorHexText').addEventListener('input', function() {
    if (/^#[0-9A-F]{6}$/i.test(this.value)) {
        document.getElementById('colorHex').value = this.value;
    }
});

// Fonctions de suppression
function deleteSize(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette taille ?')) {
        const formData = new FormData();
        formData.append('action', 'delete_size');
        formData.append('id', id);
        
        fetch('sizes_colors.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                setTimeout(() => location.reload(), 1000);
            }
        });
    }
}

function deleteColor(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette couleur ?')) {
        const formData = new FormData();
        formData.append('action', 'delete_color');
        formData.append('id', id);
        
        fetch('sizes_colors.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                setTimeout(() => location.reload(), 1000);
            }
        });
    }
}
</script>

<?php include 'layouts/footer.php'; ?>