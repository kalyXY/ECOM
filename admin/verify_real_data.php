<?php
require_once 'config.php';
requireLogin();

$pageTitle = 'Vérification des données réelles';
$active = 'dashboard';
include 'layouts/header.php';

// Vérifier les données dans chaque table
$dataStatus = [];

// Vérifier les produits
try {
    $productCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $dataStatus['products'] = [
        'exists' => true,
        'count' => $productCount,
        'status' => $productCount > 0 ? 'success' : 'warning',
        'message' => $productCount > 0 ? "$productCount produits trouvés" : "Aucun produit"
    ];
} catch (Exception $e) {
    $dataStatus['products'] = [
        'exists' => false,
        'count' => 0,
        'status' => 'danger',
        'message' => 'Table products non trouvée'
    ];
}

// Vérifier les commandes
try {
    $orderCount = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders")->fetchColumn();
    $dataStatus['orders'] = [
        'exists' => true,
        'count' => $orderCount,
        'revenue' => $totalRevenue,
        'status' => $orderCount > 0 ? 'success' : 'info',
        'message' => $orderCount > 0 ? "$orderCount commandes (" . formatPrice($totalRevenue) . ")" : "Aucune commande"
    ];
} catch (Exception $e) {
    $dataStatus['orders'] = [
        'exists' => false,
        'count' => 0,
        'status' => 'warning',
        'message' => 'Table orders non trouvée'
    ];
}

// Vérifier les clients
try {
    $customerCount = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    $dataStatus['customers'] = [
        'exists' => true,
        'count' => $customerCount,
        'status' => $customerCount > 0 ? 'success' : 'info',
        'message' => $customerCount > 0 ? "$customerCount clients trouvés" : "Aucun client"
    ];
} catch (Exception $e) {
    $dataStatus['customers'] = [
        'exists' => false,
        'count' => 0,
        'status' => 'warning',
        'message' => 'Table customers non trouvée'
    ];
}

// Vérifier les catégories
try {
    $categoryCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $dataStatus['categories'] = [
        'exists' => true,
        'count' => $categoryCount,
        'status' => $categoryCount > 0 ? 'success' : 'info',
        'message' => $categoryCount > 0 ? "$categoryCount catégories trouvées" : "Aucune catégorie"
    ];
} catch (Exception $e) {
    $dataStatus['categories'] = [
        'exists' => false,
        'count' => 0,
        'status' => 'warning',
        'message' => 'Table categories non trouvée'
    ];
}

// Calculer des métriques réelles
$realMetrics = [];

// Ventes des 7 derniers jours
try {
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as orders,
            COALESCE(SUM(total_amount), 0) as sales
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $weeklyData = $stmt->fetchAll();
    $realMetrics['weekly_sales'] = array_sum(array_column($weeklyData, 'sales'));
    $realMetrics['weekly_orders'] = array_sum(array_column($weeklyData, 'orders'));
} catch (Exception $e) {
    $realMetrics['weekly_sales'] = 0;
    $realMetrics['weekly_orders'] = 0;
}

// Produits ajoutés récemment
try {
    $recentProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
    $realMetrics['recent_products'] = $recentProducts;
} catch (Exception $e) {
    $realMetrics['recent_products'] = 0;
}
?>

<div class="admin-wrapper">
    <?php include 'layouts/sidebar.php'; ?>
    
    <main class="admin-content">
        <?php include 'layouts/topbar.php'; ?>
        
        <div class="page-content">
            <div class="page-header">
                <h1 class="page-title">Vérification des données réelles</h1>
                <p class="page-subtitle">État des données dans votre base de données</p>
            </div>

            <!-- État des tables -->
            <div class="row mb-4">
                <?php foreach ($dataStatus as $table => $status): ?>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <?php
                                $icons = [
                                    'products' => 'fa-box',
                                    'orders' => 'fa-shopping-cart',
                                    'customers' => 'fa-users',
                                    'categories' => 'fa-tags'
                                ];
                                ?>
                                <i class="fas <?php echo $icons[$table]; ?> fa-2x text-<?php echo $status['status']; ?>"></i>
                            </div>
                            <h5 class="text-capitalize"><?php echo $table; ?></h5>
                            <div class="badge bg-<?php echo $status['status']; ?> mb-2">
                                <?php echo $status['message']; ?>
                            </div>
                            <?php if (isset($status['revenue']) && $status['revenue'] > 0): ?>
                                <div class="small text-muted">
                                    CA: <?php echo formatPrice($status['revenue']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Métriques calculées en temps réel -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Métriques calculées en temps réel</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="metric-item">
                                <div class="h4 text-success"><?php echo formatPrice($realMetrics['weekly_sales']); ?></div>
                                <small class="text-muted">Ventes (7 derniers jours)</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="metric-item">
                                <div class="h4 text-primary"><?php echo $realMetrics['weekly_orders']; ?></div>
                                <small class="text-muted">Commandes (7 derniers jours)</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="metric-item">
                                <div class="h4 text-info"><?php echo $realMetrics['recent_products']; ?></div>
                                <small class="text-muted">Produits ajoutés (7 derniers jours)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommandations -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recommandations</h5>
                </div>
                <div class="card-body">
                    <?php if ($dataStatus['products']['count'] == 0): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Aucun produit :</strong> Commencez par ajouter des produits à votre catalogue.
                            <a href="add_product.php" class="btn btn-sm btn-primary ms-2">Ajouter un produit</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($dataStatus['orders']['count'] == 0): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Aucune commande :</strong> Créez des données de test pour voir les analytics en action.
                            <a href="create_sample_data.php" class="btn btn-sm btn-success ms-2">Créer des données test</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($dataStatus['categories']['count'] == 0): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Aucune catégorie :</strong> Organisez vos produits en créant des catégories.
                            <a href="categories.php" class="btn btn-sm btn-outline-primary ms-2">Gérer les catégories</a>
                        </div>
                    <?php endif; ?>

                    <?php if (array_sum(array_column($dataStatus, 'count')) > 0): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Félicitations !</strong> Votre base de données contient des données réelles. 
                            Toutes les statistiques et graphiques sont maintenant basés sur vos vraies données.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">Voir les données</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="analytics.php" class="btn btn-outline-primary">
                                    <i class="fas fa-chart-line me-2"></i>Analytics avec données réelles
                                </a>
                                <a href="reports.php" class="btn btn-outline-success">
                                    <i class="fas fa-file-alt me-2"></i>Rapports détaillés
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">Gérer les données</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="products.php" class="btn btn-outline-primary">
                                    <i class="fas fa-box me-2"></i>Gérer les produits
                                </a>
                                <a href="orders.php" class="btn btn-outline-success">
                                    <i class="fas fa-shopping-cart me-2"></i>Voir les commandes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'layouts/footer.php'; ?>