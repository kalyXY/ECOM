<?php
require_once 'config.php';
requireLogin();

// Vérifier si la table categories existe
$categoriesTableExists = false;
try {
    $pdo->query("SELECT COUNT(*) FROM categories LIMIT 1");
    $categoriesTableExists = true;
} catch (PDOException $e) {
    // La table n'existe pas, on va la créer
}

// Créer la table categories si elle n'existe pas
if (!$categoriesTableExists) {
    try {
        $pdo->exec("
            CREATE TABLE categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // Insérer quelques catégories par défaut
        $pdo->exec("
            INSERT INTO categories (name, description) VALUES 
            ('Électronique', 'Appareils électroniques et gadgets'),
            ('Informatique', 'Ordinateurs, laptops et accessoires'),
            ('Audio', 'Casques, écouteurs et systèmes audio'),
            ('Téléphonie', 'Smartphones et accessoires mobiles')
        ");
        
        $categoriesTableExists = true;
    } catch (PDOException $e) {
        // Erreur lors de la création
    }
}

// Traitement des actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                    $name = trim($_POST['name'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    
                    if (!empty($name)) {
                        try {
                            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
                            $stmt->bindParam(':name', $name);
                            $stmt->bindParam(':description', $description);
                            $stmt->execute();
                            
                            $message = 'Catégorie ajoutée avec succès.';
                            $messageType = 'success';
                        } catch (PDOException $e) {
                            $message = 'Erreur lors de l\'ajout : ' . $e->getMessage();
                            $messageType = 'danger';
                        }
                    } else {
                        $message = 'Le nom de la catégorie est requis.';
                        $messageType = 'danger';
                    }
                }
                break;
                
            case 'delete':
                if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id > 0) {
                        try {
                            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
                            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt->execute();
                            
                            $message = 'Catégorie supprimée avec succès.';
                            $messageType = 'success';
                        } catch (PDOException $e) {
                            $message = 'Erreur lors de la suppression : ' . $e->getMessage();
                            $messageType = 'danger';
                        }
                    }
                }
                break;
        }
    }
}

// Récupérer les catégories
$categories = [];
if ($categoriesTableExists) {
    try {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Erreur lors de la récupération
    }
}

$pageTitle = 'Gestion des Catégories';
$active = 'categories';
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
                        <h1 class="page-title">Gestion des Catégories</h1>
                        <p class="page-subtitle">Organisez vos produits par catégories</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus me-2"></i>Ajouter une catégorie
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!$categoriesTableExists): ?>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Table des catégories non trouvée</h6>
                    <p class="mb-0">La table des catégories n'existe pas dans votre base de données. Veuillez exécuter le script de migration.</p>
                </div>
            <?php else: ?>

            <!-- Statistiques -->
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Total Catégories</div>
                        <div class="stat-icon primary">
                            <i class="fas fa-tags"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo count($categories); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-check"></i>
                        <span>Actives</span>
                    </div>
                </div>
            </div>

            <!-- Liste des catégories -->
            <?php if (empty($categories)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune catégorie trouvée</h5>
                        <p class="text-muted">Commencez par créer votre première catégorie.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus me-2"></i>Ajouter une catégorie
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="table-header">
                        <h5 class="table-title">Liste des catégories</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Date de création</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $category['id']; ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($category['name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="text-muted" style="max-width: 300px;">
                                                <?php echo htmlspecialchars($category['description'] ?? 'Aucune description'); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo formatDate($category['created_at']); ?>
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary" 
                                                        onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>', '<?php echo addslashes($category['description'] ?? ''); ?>')"
                                                        title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDeleteCategory(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>')"
                                                        title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal Ajouter Catégorie -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group mb-3">
                        <label for="categoryName" class="form-label">Nom de la catégorie *</label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifier Catégorie -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier la catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="id" id="editCategoryId">
                    
                    <div class="form-group mb-3">
                        <label for="editCategoryName" class="form-label">Nom de la catégorie *</label>
                        <input type="text" class="form-control" id="editCategoryName" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editCategoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editCategoryDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulaire de suppression caché -->
<form id="deleteCategoryForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="id" id="deleteCategoryId">
</form>

<?php
$pageScripts = '
<script>
function editCategory(id, name, description) {
    document.getElementById("editCategoryId").value = id;
    document.getElementById("editCategoryName").value = name;
    document.getElementById("editCategoryDescription").value = description;
    new bootstrap.Modal(document.getElementById("editCategoryModal")).show();
}

function confirmDeleteCategory(id, name) {
    if (confirm("Êtes-vous sûr de vouloir supprimer la catégorie \"" + name + "\" ?\\n\\nCette action est irréversible.")) {
        document.getElementById("deleteCategoryId").value = id;
        document.getElementById("deleteCategoryForm").submit();
    }
}
</script>
';

include 'layouts/footer.php';
?>