<?php
require_once 'config.php';
requireLogin();

$errors = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_wishlist'])) {
    try {
        $pdo->beginTransaction();
        
        // Créer la table wishlists
        $sql = "
            CREATE TABLE IF NOT EXISTS wishlists (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(255),
                customer_id INT,
                product_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                UNIQUE KEY unique_wishlist_item (session_id, product_id),
                INDEX idx_session_id (session_id),
                INDEX idx_customer_id (customer_id),
                INDEX idx_product_id (product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($sql);
        $success[] = "Table 'wishlists' créée avec succès.";
        
        $pdo->commit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $errors[] = "Erreur lors de la création de la table : " . $e->getMessage();
    }
}

// Vérifier si la table existe déjà
$tableExists = false;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM wishlists");
    $tableExists = true;
    $recordCount = $stmt->fetchColumn();
} catch (PDOException $e) {
    $tableExists = false;
    $recordCount = 0;
}

$pageTitle = 'Configuration Wishlist';
$active = 'settings';
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
                        <h1 class="page-title">Configuration Wishlist</h1>
                        <p class="page-subtitle">Mise en place de la fonctionnalité favoris</p>
                    </div>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour aux produits
                    </a>
                </div>
            </div>

            <!-- Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Erreurs :</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>Succès :</h6>
                    <ul class="mb-0">
                        <?php foreach ($success as $msg): ?>
                            <li><?php echo htmlspecialchars($msg); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- État actuel -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-database me-2"></i>État actuel
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($tableExists): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Table 'wishlists' configurée</strong>
                                    <br>
                                    <small>Nombre d'enregistrements : <?php echo $recordCount; ?></small>
                                </div>
                                
                                <h6>Fonctionnalités disponibles :</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <i class="fas fa-heart text-danger me-2"></i>
                                        Ajouter des produits aux favoris
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fas fa-list text-primary me-2"></i>
                                        Afficher la liste des favoris
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fas fa-trash text-warning me-2"></i>
                                        Supprimer des favoris
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fas fa-shopping-cart text-success me-2"></i>
                                        Ajouter au panier depuis la wishlist
                                    </li>
                                </ul>
                                
                                <div class="mt-3">
                                    <a href="<?php echo '../wishlist.php'; ?>" class="btn btn-primary" target="_blank">
                                        <i class="fas fa-external-link-alt me-2"></i>
                                        Tester la wishlist
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Table 'wishlists' non configurée</strong>
                                    <br>
                                    <small>La fonctionnalité wishlist n'est pas encore disponible.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Configuration -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cog me-2"></i>Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!$tableExists): ?>
                                <p>Créer la table nécessaire pour la fonctionnalité wishlist :</p>
                                <ul>
                                    <li>Table <code>wishlists</code> avec gestion des sessions</li>
                                    <li>Support des utilisateurs connectés et anonymes</li>
                                    <li>Relations avec les produits</li>
                                    <li>Index optimisés pour les performances</li>
                                </ul>
                                
                                <form method="POST">
                                    <button type="submit" name="setup_wishlist" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-heart me-2"></i>Configurer la Wishlist
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    La wishlist est déjà configurée et fonctionnelle !
                                </div>
                                
                                <h6>Liens utiles :</h6>
                                <div class="d-grid gap-2">
                                    <a href="../wishlist.php" class="btn btn-outline-primary" target="_blank">
                                        <i class="fas fa-heart me-2"></i>Page Wishlist
                                    </a>
                                    <a href="products.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-box me-2"></i>Gérer les produits
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Informations techniques -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Informations techniques
                            </h6>
                        </div>
                        <div class="card-body">
                            <small>
                                <strong>Fichiers créés :</strong><br>
                                • <code>wishlist.php</code> - Page principale<br>
                                • Fonctions JavaScript dans <code>assets/js/script.js</code><br>
                                • Styles CSS intégrés<br><br>
                                
                                <strong>Fonctionnalités :</strong><br>
                                • Gestion par session pour utilisateurs anonymes<br>
                                • Interface responsive et moderne<br>
                                • Intégration avec le panier<br>
                                • Actions sécurisées avec tokens CSRF<br>
                                • Messages de confirmation<br>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'layouts/footer.php'; ?>