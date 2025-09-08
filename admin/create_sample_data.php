<?php
require_once 'config.php';
requireLogin();

// Script pour créer des données d'exemple réelles dans la base de données

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_data'])) {
    try {
        $pdo->beginTransaction();
        
        // Créer des commandes d'exemple
        $sampleOrders = [
            ['Jean Dupont', 'jean.dupont@email.com', 1199.99, 'delivered', '-15 days'],
            ['Marie Martin', 'marie.martin@email.com', 699.99, 'delivered', '-12 days'],
            ['Pierre Durand', 'pierre.durand@email.com', 299.99, 'shipped', '-8 days'],
            ['Sophie Bernard', 'sophie.bernard@email.com', 899.99, 'confirmed', '-5 days'],
            ['Lucas Moreau', 'lucas.moreau@email.com', 1599.99, 'delivered', '-3 days'],
            ['Emma Leroy', 'emma.leroy@email.com', 449.99, 'pending', '-2 days'],
            ['Thomas Dubois', 'thomas.dubois@email.com', 799.99, 'delivered', '-1 day'],
            ['Julie Petit', 'julie.petit@email.com', 199.99, 'confirmed', '-6 hours']
        ];
        
        // Vérifier si la table orders existe
        try {
            $pdo->query("SELECT COUNT(*) FROM orders LIMIT 1");
            
            // Supprimer les anciennes données d'exemple
            $pdo->exec("DELETE FROM orders WHERE customer_email LIKE '%@email.com'");
            
            // Insérer les nouvelles commandes
            $stmt = $pdo->prepare("
                INSERT INTO orders (customer_name, customer_email, total_amount, status, created_at) 
                VALUES (?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))
            ");
            
            foreach ($sampleOrders as $order) {
                // Convertir la période en jours
                $days = 0;
                if (strpos($order[4], 'days') !== false) {
                    $days = (int)str_replace(['-', ' days'], '', $order[4]);
                } elseif (strpos($order[4], 'day') !== false) {
                    $days = (int)str_replace(['-', ' day'], '', $order[4]);
                } elseif (strpos($order[4], 'hours') !== false) {
                    $days = 0; // Moins d'un jour
                }
                
                $stmt->execute([
                    $order[0], // customer_name
                    $order[1], // customer_email
                    $order[2], // total_amount
                    $order[3], // status
                    $days      // days ago
                ]);
            }
            
            $message .= "✅ " . count($sampleOrders) . " commandes d'exemple créées.\n";
            
        } catch (PDOException $e) {
            $message .= "⚠️ Table 'orders' non trouvée. Création...\n";
            
            // Créer la table orders
            $pdo->exec("
                CREATE TABLE orders (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_name VARCHAR(100) NOT NULL,
                    customer_email VARCHAR(100) NOT NULL,
                    total_amount DECIMAL(10,2) NOT NULL,
                    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            // Insérer les commandes
            $stmt = $pdo->prepare("
                INSERT INTO orders (customer_name, customer_email, total_amount, status, created_at) 
                VALUES (?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))
            ");
            
            foreach ($sampleOrders as $order) {
                $days = 0;
                if (strpos($order[4], 'days') !== false) {
                    $days = (int)str_replace(['-', ' days'], '', $order[4]);
                } elseif (strpos($order[4], 'day') !== false) {
                    $days = (int)str_replace(['-', ' day'], '', $order[4]);
                }
                
                $stmt->execute([$order[0], $order[1], $order[2], $order[3], $days]);
            }
            
            $message .= "✅ Table 'orders' créée avec " . count($sampleOrders) . " commandes.\n";
        }
        
        // Créer des clients d'exemple
        try {
            $pdo->query("SELECT COUNT(*) FROM customers LIMIT 1");
            
            // Supprimer les anciens clients d'exemple
            $pdo->exec("DELETE FROM customers WHERE email LIKE '%@email.com'");
            
        } catch (PDOException $e) {
            // Créer la table customers
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
            
            $message .= "✅ Table 'customers' créée.\n";
        }
        
        // Insérer les clients basés sur les commandes
        $customerStmt = $pdo->prepare("
            INSERT INTO customers (name, email, total_orders, total_spent, created_at) 
            SELECT 
                customer_name,
                customer_email,
                COUNT(*) as total_orders,
                SUM(total_amount) as total_spent,
                MIN(created_at) as created_at
            FROM orders 
            WHERE customer_email LIKE '%@email.com'
            GROUP BY customer_name, customer_email
            ON DUPLICATE KEY UPDATE
                total_orders = VALUES(total_orders),
                total_spent = VALUES(total_spent)
        ");
        $customerStmt->execute();
        
        $customerCount = $pdo->query("SELECT COUNT(*) FROM customers WHERE email LIKE '%@email.com'")->fetchColumn();
        $message .= "✅ " . $customerCount . " clients créés/mis à jour.\n";
        
        // Créer des catégories si elles n'existent pas
        try {
            $pdo->query("SELECT COUNT(*) FROM categories LIMIT 1");
        } catch (PDOException $e) {
            $pdo->exec("
                CREATE TABLE categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            // Insérer des catégories
            $categories = [
                ['Électronique', 'Appareils électroniques et gadgets'],
                ['Informatique', 'Ordinateurs, laptops et accessoires'],
                ['Audio', 'Casques, écouteurs et systèmes audio'],
                ['Téléphonie', 'Smartphones et accessoires mobiles'],
                ['Gaming', 'Consoles et accessoires de jeu']
            ];
            
            $catStmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            foreach ($categories as $cat) {
                $catStmt->execute($cat);
            }
            
            $message .= "✅ " . count($categories) . " catégories créées.\n";
        }
        
        $pdo->commit();
        $messageType = 'success';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "❌ Erreur lors de la création des données : " . $e->getMessage();
        $messageType = 'danger';
    }
}

$pageTitle = 'Créer des données d\'exemple';
$active = 'dashboard';
include 'layouts/header.php';
?>

<div class="admin-wrapper">
    <?php include 'layouts/sidebar.php'; ?>
    
    <main class="admin-content">
        <?php include 'layouts/topbar.php'; ?>
        
        <div class="page-content">
            <div class="page-header">
                <h1 class="page-title">Créer des données d'exemple</h1>
                <p class="page-subtitle">Générez des données réelles pour tester les fonctionnalités</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <pre><?php echo htmlspecialchars($message); ?></pre>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Données d'exemple</h5>
                </div>
                <div class="card-body">
                    <p>Ce script va créer des données d'exemple réelles dans votre base de données :</p>
                    
                    <ul>
                        <li><strong>8 commandes</strong> avec différents statuts et dates</li>
                        <li><strong>Clients correspondants</strong> avec statistiques calculées</li>
                        <li><strong>5 catégories</strong> de produits</li>
                        <li><strong>Tables manquantes</strong> créées automatiquement</li>
                    </ul>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note :</strong> Les anciennes données d'exemple seront supprimées et remplacées.
                    </div>
                    
                    <form method="POST">
                        <button type="submit" name="create_data" class="btn btn-primary" onclick="return confirm('Êtes-vous sûr de vouloir créer/remplacer les données d\'exemple ?')">
                            <i class="fas fa-database me-2"></i>Créer les données d'exemple
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour au dashboard
                        </a>
                    </form>
                </div>
            </div>

            <!-- Aperçu des données actuelles -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-primary">
                                <?php 
                                try {
                                    echo (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </h3>
                            <p class="text-muted">Produits</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-success">
                                <?php 
                                try {
                                    echo (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </h3>
                            <p class="text-muted">Commandes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-info">
                                <?php 
                                try {
                                    echo (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </h3>
                            <p class="text-muted">Clients</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-warning">
                                <?php 
                                try {
                                    echo (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </h3>
                            <p class="text-muted">Catégories</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'layouts/footer.php'; ?>