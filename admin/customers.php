<?php
require_once 'config.php';
requireLogin();

// Vérifier si la table customers existe, sinon la créer
$customersTableExists = false;
try {
    $pdo->query("SELECT COUNT(*) FROM customers LIMIT 1");
    $customersTableExists = true;
} catch (PDOException $e) {
    // Créer la table customers
    try {
        $pdo->exec("
            CREATE TABLE customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                phone VARCHAR(20),
                address TEXT,
                city VARCHAR(50),
                postal_code VARCHAR(10),
                country VARCHAR(50) DEFAULT 'France',
                total_orders INT DEFAULT 0,
                total_spent DECIMAL(10,2) DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // Insérer quelques clients d'exemple
        $pdo->exec("
            INSERT INTO customers (name, email, phone, address, city, postal_code, total_orders, total_spent) VALUES 
            ('Jean Dupont', 'jean.dupont@email.com', '01 23 45 67 89', '123 Rue de la Paix', 'Paris', '75001', 3, 1299.97),
            ('Marie Martin', 'marie.martin@email.com', '01 98 76 54 32', '456 Avenue des Champs', 'Lyon', '69000', 1, 699.99),
            ('Pierre Durand', 'pierre.durand@email.com', '01 11 22 33 44', '789 Boulevard Saint-Germain', 'Marseille', '13000', 2, 899.98),
            ('Sophie Bernard', 'sophie.bernard@email.com', '01 55 66 77 88', '321 Rue du Commerce', 'Toulouse', '31000', 1, 199.99),
            ('Lucas Moreau', 'lucas.moreau@email.com', '01 44 33 22 11', '654 Place de la République', 'Nice', '06000', 4, 2199.96)
        ");
        
        $customersTableExists = true;
    } catch (PDOException $e) {
        // Erreur lors de la création
    }
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Filtres
$searchFilter = $_GET['search'] ?? '';

// Construire la requête avec filtres
$whereClause = '';
$params = [];

if (!empty($searchFilter)) {
    $whereClause = "WHERE (name LIKE :search OR email LIKE :search OR city LIKE :search)";
    $params[':search'] = '%' . $searchFilter . '%';
}

// Récupérer les clients
$customers = [];
$total = 0;
$totalPages = 0;

if ($customersTableExists) {
    try {
        // Compter le total
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM customers $whereClause");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        // Récupérer les clients avec pagination
        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;
        
        $stmt = $pdo->prepare("SELECT * FROM customers $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        $customers = $stmt->fetchAll();
        
        // Statistiques
        $statsStmt = $pdo->query("
            SELECT 
                COUNT(*) as total_customers,
                SUM(total_orders) as total_orders,
                SUM(total_spent) as total_revenue,
                AVG(total_spent) as avg_spent_per_customer
            FROM customers
        ");
        $stats = $statsStmt->fetch();
        
    } catch (PDOException $e) {
        // Erreur lors de la récupération
    }
}

$pageTitle = 'Gestion des Clients';
$active = 'customers';
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
                        <h1 class="page-title">Gestion des Clients</h1>
                        <p class="page-subtitle">Gérez votre base de clients</p>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" onclick="exportCustomers()">
                            <i class="fas fa-download me-2"></i>Exporter
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            <i class="fas fa-plus me-2"></i>Ajouter un client
                        </button>
                    </div>
                </div>
            </div>

            <?php if (!$customersTableExists): ?>
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Table des clients non trouvée</h6>
                    <p class="mb-0">La table des clients n'existe pas encore. Elle sera créée automatiquement.</p>
                </div>
            <?php else: ?>

            <!-- Statistiques -->
            <?php if (isset($stats)): ?>
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Total Clients</div>
                        <div class="stat-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_customers']); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-user-plus"></i>
                        <span>Clients actifs</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Commandes Totales</div>
                        <div class="stat-icon success">
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
                        <div class="stat-title">Chiffre d'Affaires</div>
                        <div class="stat-icon warning">
                            <i class="fas fa-euro-sign"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo formatPrice($stats['total_revenue']); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>Total</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Panier Moyen</div>
                        <div class="stat-icon danger">
                            <i class="fas fa-calculator"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo formatPrice($stats['avg_spent_per_customer']); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-chart-line"></i>
                        <span>Par client</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Rechercher</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($searchFilter); ?>" 
                                   placeholder="Nom, email ou ville">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Rechercher
                            </button>
                            <a href="customers.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des clients -->
            <?php if (empty($customers)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun client trouvé</h5>
                        <p class="text-muted">
                            <?php if (!empty($searchFilter)): ?>
                                Aucun client ne correspond à votre recherche.
                            <?php else: ?>
                                Les clients apparaîtront ici une fois qu'ils s'inscriront.
                            <?php endif; ?>
                        </p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            <i class="fas fa-plus me-2"></i>Ajouter un client
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="table-header">
                        <h5 class="table-title">Liste des clients (<?php echo $total; ?>)</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Ville</th>
                                    <th>Commandes</th>
                                    <th>Total Dépensé</th>
                                    <th>Inscription</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($customer['name']); ?></div>
                                                    <small class="text-muted">ID: <?php echo $customer['id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($customer['email']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($customer['phone']): ?>
                                                <a href="tel:<?php echo htmlspecialchars($customer['phone']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($customer['phone']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['city']): ?>
                                                <?php echo htmlspecialchars($customer['city']); ?>
                                                <?php if ($customer['postal_code']): ?>
                                                    <small class="text-muted">(<?php echo htmlspecialchars($customer['postal_code']); ?>)</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo number_format($customer['total_orders']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">
                                                <?php echo formatPrice($customer['total_spent']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo formatDate($customer['created_at']); ?>
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewCustomer(<?php echo $customer['id']; ?>)"
                                                        title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        onclick="editCustomer(<?php echo $customer['id']; ?>)"
                                                        title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteCustomer(<?php echo $customer['id']; ?>, '<?php echo addslashes($customer['name']); ?>')"
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

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <div class="text-muted">
                                Affichage de <?php echo $offset + 1; ?> à <?php echo min($offset + $perPage, $total); ?> 
                                sur <?php echo $total; ?> clients
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchFilter); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($searchFilter); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchFilter); ?>">
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

<!-- Modal Ajouter Client -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="customerName" class="form-label">Nom complet *</label>
                                <input type="text" class="form-control" id="customerName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="customerEmail" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="customerEmail" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="customerPhone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="customerPhone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="customerCity" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="customerCity">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="customerAddress" class="form-label">Adresse</label>
                        <textarea class="form-control" id="customerAddress" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="customerPostalCode" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="customerPostalCode">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="customerCountry" class="form-label">Pays</label>
                                <input type="text" class="form-control" id="customerCountry" value="France">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="saveCustomer()">
                    <i class="fas fa-plus me-2"></i>Ajouter
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$pageScripts = '
<script>
function viewCustomer(customerId) {
    alert("Fonctionnalité à venir : Voir les détails du client #" + customerId);
}

function editCustomer(customerId) {
    alert("Fonctionnalité à venir : Modifier le client #" + customerId);
}

function deleteCustomer(customerId, customerName) {
    if (confirm("Êtes-vous sûr de vouloir supprimer le client \"" + customerName + "\" ?\\n\\nCette action est irréversible.")) {
        alert("Fonctionnalité à venir : Suppression du client #" + customerId);
    }
}

function saveCustomer() {
    alert("Fonctionnalité à venir : Sauvegarde du client");
}

function exportCustomers() {
    alert("Fonctionnalité à venir : Export des clients");
}
</script>
';

include 'layouts/footer.php';
?>