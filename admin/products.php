<?php
require_once 'config.php';
requireLogin();

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Compter le nombre total de produits
$total = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalPages = (int)ceil($total / $perPage);

// Vérifier si la colonne stock existe
$columns = 'id, name, description, price, image_url, created_at';
try {
    $pdo->query('SELECT stock FROM products LIMIT 1');
    $columns = 'id, name, description, price, stock, image_url, created_at';
} catch (PDOException $e) {
    // La colonne stock n'existe pas, on continue sans elle
}

// Récupérer les produits avec pagination
$stmt = $pdo->prepare("SELECT {$columns} FROM products ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

// Gestion des messages
$message = '';
$messageType = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

$pageTitle = 'Gestion des Produits';
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
                        <h1 class="page-title">Gestion des Produits</h1>
                        <p class="page-subtitle">Gérez votre catalogue de produits</p>
                    </div>
                    <a href="add_product.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Ajouter un produit
                    </a>
                </div>
            </div>

            <!-- Messages d'alerte -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistiques rapides -->
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Total Produits</div>
                        <div class="stat-icon primary">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($total); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>Catalogue complet</span>
                    </div>
                </div>
            </div>

            <!-- Table des produits -->
            <?php if (empty($products)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun produit trouvé</h5>
                        <p class="text-muted">Commencez par ajouter votre premier produit.</p>
                        <a href="add_product.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Ajouter un produit
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="table-header">
                        <h5 class="table-title">Liste des produits</h5>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-sm" placeholder="Rechercher..." id="searchInput" style="width: 200px;">
                            <a href="add_product.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Ajouter
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Image</th>
                                    <th>Nom</th>
                                    <th style="width: 120px;">Prix</th>
                                    <?php if (strpos($columns, 'stock') !== false): ?>
                                    <th style="width: 80px;">Stock</th>
                                    <?php endif; ?>
                                    <th style="width: 140px;">Date</th>
                                    <th style="width: 120px;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <?php if ($product['image_url'] && file_exists('../' . $product['image_url'])): ?>
                                                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                     class="rounded" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($product['name']); ?></div>
                                            <div class="text-muted small" style="max-width: 300px;">
                                                <?php 
                                                $description = htmlspecialchars($product['description']);
                                                echo strlen($description) > 80 ? substr($description, 0, 80) . '...' : $description;
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success fs-6">
                                                <?php echo formatPrice($product['price']); ?>
                                            </span>
                                        </td>
                                        <?php if (isset($product['stock'])): ?>
                                        <td>
                                            <span class="badge <?php echo $product['stock'] > 10 ? 'bg-success' : ($product['stock'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                                                <?php echo (int)$product['stock']; ?>
                                            </span>
                                        </td>
                                        <?php endif; ?>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo formatDate($product['created_at']); ?>
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="Supprimer"
                                                        onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <div class="text-muted">
                                Affichage de <?php echo $offset + 1; ?> à <?php echo min($offset + $perPage, $total); ?> 
                                sur <?php echo $total; ?> produits
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <!-- Bouton Précédent -->
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>

                                    <!-- Numéros de pages -->
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Bouton Suivant -->
                                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le produit <strong id="productName"></strong> ?</p>
                <p class="text-danger"><small>Cette action est irréversible.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>Supprimer
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$pageScripts = '
<script>
function confirmDelete(productId, productName) {
    document.getElementById("productName").textContent = productName;
    document.getElementById("confirmDeleteBtn").href = "delete_product.php?id=" + productId;
    new bootstrap.Modal(document.getElementById("deleteModal")).show();
}
</script>
';

include 'layouts/footer.php';
?>