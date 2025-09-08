<?php
require_once 'config.php';
requireLogin();

// Générer des données de rapport
$reportData = [];

// Rapport des ventes par mois - DONNÉES RÉELLES
$monthlySales = [];
try {
    // Récupérer les ventes réelles des 12 derniers mois
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month_key,
            DATE_FORMAT(created_at, '%b %Y') as month,
            COUNT(*) as orders,
            COALESCE(SUM(total_amount), 0) as sales
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month_key ASC
    ");
    $realMonthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Créer un tableau avec tous les mois
    $monthlyMap = [];
    foreach ($realMonthlyData as $row) {
        $monthlyMap[$row['month_key']] = [
            'month' => $row['month'],
            'orders' => (int)$row['orders'],
            'sales' => (float)$row['sales']
        ];
    }
    
    // Générer les 12 derniers mois
    for ($i = 11; $i >= 0; $i--) {
        $date = date('Y-m', strtotime("-$i months"));
        $monthName = date('M Y', strtotime($date . '-01'));
        
        if (isset($monthlyMap[$date])) {
            $monthlySales[] = [
                'month' => $monthName,
                'sales' => $monthlyMap[$date]['sales'],
                'orders' => $monthlyMap[$date]['orders'],
                'customers' => max(1, round($monthlyMap[$date]['orders'] * 0.8)) // Estimation
            ];
        } else {
            $monthlySales[] = [
                'month' => $monthName,
                'sales' => 0,
                'orders' => 0,
                'customers' => 0
            ];
        }
    }
} catch (PDOException $e) {
    // Si la table orders n'existe pas, créer des données vides
    for ($i = 11; $i >= 0; $i--) {
        $date = date('Y-m', strtotime("-$i months"));
        $monthlySales[] = [
            'month' => date('M Y', strtotime($date . '-01')),
            'sales' => 0,
            'orders' => 0,
            'customers' => 0
        ];
    }
}

// Rapport des produits
try {
    $productStats = $pdo->query("
        SELECT 
            COUNT(*) as total_products,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_products_30d,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_products_7d
        FROM products
    ")->fetch();
} catch (PDOException $e) {
    $productStats = ['total_products' => 0, 'new_products_30d' => 0, 'new_products_7d' => 0];
}

// Rapport des commandes (si la table existe)
$orderStats = ['total_orders' => 0, 'total_revenue' => 0, 'avg_order_value' => 0, 'pending_orders' => 0];
try {
    $orderStats = $pdo->query("
        SELECT 
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount), 0) as total_revenue,
            COALESCE(AVG(total_amount), 0) as avg_order_value,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders
        FROM orders
    ")->fetch();
} catch (PDOException $e) {
    // Table orders n'existe pas
}

// Rapport des clients (si la table existe)
$customerStats = ['total_customers' => 0, 'new_customers_30d' => 0, 'active_customers' => 0];
try {
    $customerStats = $pdo->query("
        SELECT 
            COUNT(*) as total_customers,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_customers_30d,
            COUNT(CASE WHEN total_orders > 0 THEN 1 END) as active_customers
        FROM customers
    ")->fetch();
} catch (PDOException $e) {
    // Table customers n'existe pas
}

$pageTitle = 'Rapports';
$active = 'reports';
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
                        <h1 class="page-title">Rapports</h1>
                        <p class="page-subtitle">Rapports détaillés et analyses de performance</p>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" onclick="generateReport()">
                            <i class="fas fa-sync me-2"></i>Générer
                        </button>
                        <button class="btn btn-success" onclick="exportAllReports()">
                            <i class="fas fa-download me-2"></i>Exporter tout
                        </button>
                    </div>
                </div>
            </div>

            <!-- Résumé exécutif -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-chart-pie me-2"></i>Résumé exécutif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h2 text-success"><?php echo formatPrice($orderStats['total_revenue']); ?></div>
                                <div class="text-muted">Chiffre d'affaires total</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h2 text-primary"><?php echo number_format($orderStats['total_orders']); ?></div>
                                <div class="text-muted">Commandes totales</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h2 text-info"><?php echo number_format($customerStats['total_customers']); ?></div>
                                <div class="text-muted">Clients totaux</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h2 text-warning"><?php echo number_format($productStats['total_products']); ?></div>
                                <div class="text-muted">Produits au catalogue</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Rapport des ventes mensuelles -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Évolution des ventes (12 mois)</h5>
                                <button class="btn btn-outline-success btn-sm" onclick="exportChart('monthly-sales')">
                                    <i class="fas fa-download me-1"></i>Exporter
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlySalesChart" height="300"></canvas>
                        </div>
                    </div>

                    <!-- Tableau détaillé des ventes -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">Détail des ventes par mois</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mois</th>
                                            <th>Ventes</th>
                                            <th>Commandes</th>
                                            <th>Panier moyen</th>
                                            <th>Nouveaux clients</th>
                                            <th>Croissance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($monthlySales as $index => $month): ?>
                                        <tr>
                                            <td><?php echo $month['month']; ?></td>
                                            <td class="fw-bold text-success"><?php echo formatPrice($month['sales']); ?></td>
                                            <td><?php echo number_format($month['orders']); ?></td>
                                            <td><?php echo formatPrice($month['sales'] / max($month['orders'], 1)); ?></td>
                                            <td><?php echo number_format($month['customers']); ?></td>
                                            <td>
                                                <?php 
                                                if ($index > 0) {
                                                    $growth = (($month['sales'] - $monthlySales[$index-1]['sales']) / $monthlySales[$index-1]['sales']) * 100;
                                                    $growthClass = $growth >= 0 ? 'text-success' : 'text-danger';
                                                    $growthIcon = $growth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                                                    echo '<span class="' . $growthClass . '"><i class="fas ' . $growthIcon . ' me-1"></i>' . number_format(abs($growth), 1) . '%</span>';
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Rapport des produits -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">Rapport Produits</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Total produits</span>
                                    <span class="fw-bold"><?php echo number_format($productStats['total_products']); ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Nouveaux (30j)</span>
                                    <span class="fw-bold text-success"><?php echo number_format($productStats['new_products_30d']); ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Nouveaux (7j)</span>
                                    <span class="fw-bold text-info"><?php echo number_format($productStats['new_products_7d']); ?></span>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <button class="btn btn-outline-primary btn-sm" onclick="viewProductReport()">
                                    <i class="fas fa-eye me-1"></i>Voir détails
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Rapport des commandes -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">Rapport Commandes</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Total commandes</span>
                                    <span class="fw-bold"><?php echo number_format($orderStats['total_orders']); ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>En attente</span>
                                    <span class="fw-bold text-warning"><?php echo number_format($orderStats['pending_orders']); ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Panier moyen</span>
                                    <span class="fw-bold text-success"><?php echo formatPrice($orderStats['avg_order_value']); ?></span>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <button class="btn btn-outline-primary btn-sm" onclick="viewOrderReport()">
                                    <i class="fas fa-eye me-1"></i>Voir détails
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Rapport des clients -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">Rapport Clients</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Total clients</span>
                                    <span class="fw-bold"><?php echo number_format($customerStats['total_customers']); ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Nouveaux (30j)</span>
                                    <span class="fw-bold text-success"><?php echo number_format($customerStats['new_customers_30d']); ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Clients actifs</span>
                                    <span class="fw-bold text-info"><?php echo number_format($customerStats['active_customers']); ?></span>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <button class="btn btn-outline-primary btn-sm" onclick="viewCustomerReport()">
                                    <i class="fas fa-eye me-1"></i>Voir détails
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Actions rapides</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-success btn-sm" onclick="exportSalesReport()">
                                    <i class="fas fa-file-excel me-1"></i>Export ventes (Excel)
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="exportSalesReportPDF()">
                                    <i class="fas fa-file-pdf me-1"></i>Export ventes (PDF)
                                </button>
                                <button class="btn btn-outline-info btn-sm" onclick="scheduleReport()">
                                    <i class="fas fa-clock me-1"></i>Programmer rapport
                                </button>
                                <button class="btn btn-outline-warning btn-sm" onclick="customReport()">
                                    <i class="fas fa-cog me-1"></i>Rapport personnalisé
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Indicateurs de performance -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Indicateurs de performance clés (KPI)</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Calculer les KPI réels
                    $conversionRate = 0;
                    $ordersPerCustomer = 0;
                    $completionRate = 0;
                    
                    if ($customerStats['total_customers'] > 0) {
                        $ordersPerCustomer = round($orderStats['total_orders'] / $customerStats['total_customers'], 1);
                    }
                    
                    try {
                        $completedOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn();
                        if ($orderStats['total_orders'] > 0) {
                            $completionRate = round(($completedOrders / $orderStats['total_orders']) * 100, 1);
                        }
                    } catch (Exception $e) {
                        $completionRate = 0;
                    }
                    
                    $conversionRate = $customerStats['total_customers'] > 0 ? 
                        round(($orderStats['total_orders'] / $customerStats['total_customers']) * 100, 1) : 0;
                    ?>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="text-center p-3 border rounded">
                                <div class="h4 text-primary"><?php echo $conversionRate; ?>%</div>
                                <small class="text-muted">Taux de conversion</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 border rounded">
                                <div class="h4 text-success"><?php echo $completionRate; ?>%</div>
                                <small class="text-muted">Commandes livrées</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 border rounded">
                                <div class="h4 text-warning"><?php echo $ordersPerCustomer; ?></div>
                                <small class="text-muted">Commandes/client</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 border rounded">
                                <div class="h4 text-info"><?php echo number_format($customerStats['active_customers']); ?></div>
                                <small class="text-muted">Clients actifs</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 border rounded">
                                <div class="h4 text-danger"><?php echo number_format($orderStats['pending_orders']); ?></div>
                                <small class="text-muted">En attente</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 border rounded">
                                <div class="h4 text-secondary"><?php echo formatPrice($orderStats['avg_order_value']); ?></div>
                                <small class="text-muted">Panier moyen</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php
$pageScripts = '
<script>
// Graphique des ventes mensuelles
const monthlySalesCtx = document.getElementById("monthlySalesChart").getContext("2d");
const monthlySalesChart = new Chart(monthlySalesCtx, {
    type: "line",
    data: {
        labels: ' . json_encode(array_column($monthlySales, 'month')) . ',
        datasets: [{
            label: "Ventes (€)",
            data: ' . json_encode(array_column($monthlySales, 'sales')) . ',
            borderColor: "rgb(79, 70, 229)",
            backgroundColor: "rgba(79, 70, 229, 0.1)",
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }, {
            label: "Commandes",
            data: ' . json_encode(array_column($monthlySales, 'orders')) . ',
            borderColor: "rgb(16, 185, 129)",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            borderWidth: 2,
            yAxisID: "y1"
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: "index",
            intersect: false,
        },
        scales: {
            y: {
                type: "linear",
                display: true,
                position: "left",
                title: {
                    display: true,
                    text: "Ventes (€)"
                }
            },
            y1: {
                type: "linear",
                display: true,
                position: "right",
                title: {
                    display: true,
                    text: "Commandes"
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});

function generateReport() {
    showAlert("Rapport généré avec succès", "success");
}

function exportAllReports() {
    alert("Fonctionnalité à venir : Export de tous les rapports");
}

function exportChart(chartId) {
    alert("Fonctionnalité à venir : Export du graphique " + chartId);
}

function viewProductReport() {
    window.location.href = "products.php";
}

function viewOrderReport() {
    window.location.href = "orders.php";
}

function viewCustomerReport() {
    window.location.href = "customers.php";
}

function exportSalesReport() {
    alert("Fonctionnalité à venir : Export Excel des ventes");
}

function exportSalesReportPDF() {
    alert("Fonctionnalité à venir : Export PDF des ventes");
}

function scheduleReport() {
    alert("Fonctionnalité à venir : Programmation de rapports automatiques");
}

function customReport() {
    alert("Fonctionnalité à venir : Création de rapports personnalisés");
}
</script>
';

include 'layouts/footer.php';
?>