<?php
require_once 'config.php';
requireLogin();

// Vérifier si la table orders existe
$ordersTableExists = false;
try {
    $pdo->query("SELECT COUNT(*) FROM orders LIMIT 1");
    $ordersTableExists = true;
} catch (PDOException $e) {
    // La table n'existe pas
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Filtres
$statusFilter = $_GET['status'] ?? '';
$searchFilter = $_GET['search'] ?? '';

// Construire la requête avec filtres
$whereConditions = [];
$params = [];

if (!empty($statusFilter)) {
    $whereConditions[] = "status = :status";
    $params[':status'] = $statusFilter;
}

if (!empty($searchFilter)) {
    $whereConditions[] = "(customer_name LIKE :search OR customer_email LIKE :search)";
    $params[':search'] = '%' . $searchFilter . '%';
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Récupérer les commandes
$orders = [];
$total = 0;
$totalPages = 0;

if ($ordersTableExists) {
    try {
        // Compter le total
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders $whereClause");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        // Récupérer les commandes avec pagination
        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;
        
        $stmt = $pdo->prepare("SELECT * FROM orders $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        $orders = $stmt->fetchAll();
        
        // Statistiques
        $statsStmt = $pdo->query("
            SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                SUM(total_amount) as total_revenue
            FROM orders
        ");
        $stats = $statsStmt->fetch();
        
    } catch (PDOException $e) {
        // Erreur lors de la récupération
    }
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        
        $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        
        if ($orderId > 0 && in_array($newStatus, $validStatuses)) {
            try {
                $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $orderId, PDO::PARAM_INT);
                $stmt->execute();
                
                $_SESSION['message'] = 'Statut de la commande mis à jour avec succès.';
                $_SESSION['message_type'] = 'success';
                header('Location: orders.php');
                exit();
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
    }
}

// Messages
$message = '';
$messageType = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

$pageTitle = 'Gestion des Commandes';
$active = 'orders';
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
                        <h1 class="page-title">Gestion des Commandes</h1>
                        <p class="page-subtitle">Suivez et gérez toutes les commandes</p>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                        <button class="btn btn-success" onclick="exportOrders()">
                            <i class="fas fa-download me-2"></i>Exporter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!$ordersTableExists): ?>
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Table des commandes non trouvée</h6>
                    <p class="mb-0">La table des commandes n'existe pas encore. Elle sera créée automatiquement lors de la première commande ou via le script de migration.</p>
                </div>
            <?php else: ?>

            <!-- Statistiques -->
            <?php if (isset($stats)): ?>
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Total Commandes</div>
                        <div class="stat-icon primary">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_orders']); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-chart-up"></i>
                        <span>Toutes commandes</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">En Attente</div>
                        <div class="stat-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['pending_orders']); ?></div>
                    <div class="stat-change <?php echo $stats['pending_orders'] > 0 ? 'negative' : 'positive'; ?>">
                        <i class="fas fa-<?php echo $stats['pending_orders'] > 0 ? 'exclamation' : 'check'; ?>"></i>
                        <span>À traiter</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Livrées</div>
                        <div class="stat-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['delivered_orders']); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-truck"></i>
                        <span>Complétées</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Chiffre d'Affaires</div>
                        <div class="stat-icon danger">
                            <i class="fas fa-euro-sign"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo formatPrice($stats['total_revenue']); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>Total</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Rechercher</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($searchFilter); ?>" 
                                   placeholder="Nom ou email du client">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmée</option>
                                <option value="shipped" <?php echo $statusFilter === 'shipped' ? 'selected' : ''; ?>>Expédiée</option>
                                <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Livrée</option>
                                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Filtrer
                            </button>
                            <a href="orders.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des commandes -->
            <?php if (empty($orders)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune commande trouvée</h5>
                        <p class="text-muted">
                            <?php if (!empty($searchFilter) || !empty($statusFilter)): ?>
                                Aucune commande ne correspond à vos critères de recherche.
                            <?php else: ?>
                                Les commandes apparaîtront ici une fois que les clients commenceront à acheter.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="table-header">
                        <h5 class="table-title">Liste des commandes (<?php echo $total; ?>)</h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Email</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">#<?php echo $order['id']; ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($order['customer_email']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($order['customer_email']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">
                                                <?php echo formatPrice($order['total_amount']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClasses = [
                                                'pending' => 'bg-warning',
                                                'confirmed' => 'bg-info',
                                                'shipped' => 'bg-primary',
                                                'delivered' => 'bg-success',
                                                'cancelled' => 'bg-danger'
                                            ];
                                            $statusLabels = [
                                                'pending' => 'En attente',
                                                'confirmed' => 'Confirmée',
                                                'shipped' => 'Expédiée',
                                                'delivered' => 'Livrée',
                                                'cancelled' => 'Annulée'
                                            ];
                                            $statusClass = $statusClasses[$order['status']] ?? 'bg-secondary';
                                            $statusLabel = $statusLabels[$order['status']] ?? $order['status'];
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo $statusLabel; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo formatDate($order['created_at']); ?>
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewOrder(<?php echo $order['id']; ?>)"
                                                        title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        onclick="changeStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')"
                                                        title="Changer statut">
                                                    <i class="fas fa-edit"></i>
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
                                sur <?php echo $total; ?> commandes
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($searchFilter); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($searchFilter); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($searchFilter); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal Changer Statut -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Changer le statut de la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="order_id" id="statusOrderId">
                    
                    <div class="form-group">
                        <label for="orderStatus" class="form-label">Nouveau statut</label>
                        <select class="form-select" id="orderStatus" name="status" required>
                            <option value="pending">En attente</option>
                            <option value="confirmed">Confirmée</option>
                            <option value="shipped">Expédiée</option>
                            <option value="delivered">Livrée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$pageScripts = '
<script>
function changeStatus(orderId, currentStatus) {
    document.getElementById("statusOrderId").value = orderId;
    document.getElementById("orderStatus").value = currentStatus;
    new bootstrap.Modal(document.getElementById("statusModal")).show();
}

function viewOrder(orderId) {
    // Ici vous pourriez ouvrir une modal avec les détails de la commande
    alert("Fonctionnalité à venir : Voir les détails de la commande #" + orderId);
}

function exportOrders() {
    // Ici vous pourriez implémenter l\'export CSV/Excel
    alert("Fonctionnalité à venir : Export des commandes");
}
</script>
';

include 'layouts/footer.php';
?>