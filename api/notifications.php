<?php
/**
 * API de notifications en temps réel
 * StyleHub E-Commerce Platform
 */

require_once '../config/bootstrap.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');

// Fonction pour envoyer des données SSE
function sendSSE($data) {
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Vérifier les stocks faibles
function checkLowStock($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, name, stock FROM products WHERE stock <= 5 AND status = 'active' ORDER BY stock ASC");
        $lowStockProducts = $stmt->fetchAll();
        
        if (!empty($lowStockProducts)) {
            return [
                'type' => 'low_stock',
                'count' => count($lowStockProducts),
                'products' => $lowStockProducts,
                'message' => count($lowStockProducts) . ' produit(s) en stock faible'
            ];
        }
    } catch (Exception $e) {
        error_log("Error checking low stock: " . $e->getMessage());
    }
    
    return null;
}

// Vérifier les nouvelles commandes
function checkNewOrders($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $newOrdersCount = (int)$stmt->fetchColumn();
        
        if ($newOrdersCount > 0) {
            return [
                'type' => 'new_orders',
                'count' => $newOrdersCount,
                'message' => $newOrdersCount . ' nouvelle(s) commande(s)'
            ];
        }
    } catch (Exception $e) {
        error_log("Error checking new orders: " . $e->getMessage());
    }
    
    return null;
}

// Statistiques en temps réel
function getRealtimeStats($pdo) {
    try {
        $stats = [];
        
        // Produits actifs
        $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
        $stats['active_products'] = (int)$stmt->fetchColumn();
        
        // Commandes du jour
        $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
        $stats['today_orders'] = (int)$stmt->fetchColumn();
        
        // Revenus du jour
        $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'");
        $stats['today_revenue'] = (float)$stmt->fetchColumn();
        
        // Visiteurs en ligne (simulation)
        $stats['online_visitors'] = rand(5, 25);
        
        return [
            'type' => 'stats_update',
            'stats' => $stats,
            'timestamp' => time()
        ];
    } catch (Exception $e) {
        error_log("Error getting realtime stats: " . $e->getMessage());
    }
    
    return null;
}

// Boucle principale SSE
$lastCheck = 0;
while (true) {
    $currentTime = time();
    
    // Vérifier toutes les 30 secondes
    if ($currentTime - $lastCheck >= 30) {
        $notifications = [];
        
        // Vérifier les stocks faibles
        $lowStock = checkLowStock($pdo);
        if ($lowStock) {
            $notifications[] = $lowStock;
        }
        
        // Vérifier les nouvelles commandes
        $newOrders = checkNewOrders($pdo);
        if ($newOrders) {
            $notifications[] = $newOrders;
        }
        
        // Statistiques en temps réel
        $stats = getRealtimeStats($pdo);
        if ($stats) {
            $notifications[] = $stats;
        }
        
        // Envoyer les notifications
        foreach ($notifications as $notification) {
            sendSSE($notification);
        }
        
        // Heartbeat pour maintenir la connexion
        sendSSE(['type' => 'heartbeat', 'timestamp' => $currentTime]);
        
        $lastCheck = $currentTime;
    }
    
    // Pause de 5 secondes
    sleep(5);
    
    // Vérifier si la connexion est toujours active
    if (connection_aborted()) {
        break;
    }
}
?>