<!-- Sidebar Admin -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-brand">
            <i class="fas fa-store"></i>
            <span>E-Commerce Admin</span>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <!-- Section principale -->
        <div class="nav-section">
            <div class="nav-section-title">Principal</div>
            <div class="nav-item">
                <a href="index.php" class="nav-link <?php echo ($active ?? '') === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>
        </div>
        
        <!-- Section produits -->
        <div class="nav-section">
            <div class="nav-section-title">Catalogue</div>
            <div class="nav-item">
                <a href="products.php" class="nav-link <?php echo ($active ?? '') === 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i>
                    <span>Produits</span>
                    <?php
                    try {
                        $countStmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
                        $productCount = $countStmt->fetchColumn();
                        if ($productCount > 0) {
                            echo '<span class="nav-badge">' . $productCount . '</span>';
                        }
                    } catch (Exception $e) {
                        // Ignorer les erreurs
                    }
                    ?>
                </a>
            </div>
            <div class="nav-item">
                <a href="add_product.php" class="nav-link <?php echo ($active ?? '') === 'add_product' ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Ajouter un produit</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="categories.php" class="nav-link <?php echo ($active ?? '') === 'categories' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i>
                    <span>Catégories</span>
                </a>
            </div>
        </div>
        
        <!-- Section ventes -->
        <div class="nav-section">
            <div class="nav-section-title">Ventes</div>
            <div class="nav-item">
                <a href="orders.php" class="nav-link <?php echo ($active ?? '') === 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Commandes</span>
                    <?php
                    try {
                        $orderCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
                        if ($orderCount > 0) {
                            echo '<span class="nav-badge">' . $orderCount . '</span>';
                        }
                    } catch (Exception $e) {
                        echo '<span class="nav-badge">0</span>';
                    }
                    ?>
                </a>
            </div>
            <div class="nav-item">
                <a href="customers.php" class="nav-link <?php echo ($active ?? '') === 'customers' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Clients</span>
                </a>
            </div>
        </div>
        
        <!-- Section rapports -->
        <div class="nav-section">
            <div class="nav-section-title">Analyse</div>
            <div class="nav-item">
                <a href="analytics.php" class="nav-link <?php echo ($active ?? '') === 'analytics' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="reports.php" class="nav-link <?php echo ($active ?? '') === 'reports' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>Rapports</span>
                </a>
            </div>
        </div>
        
        <!-- Section système -->
        <div class="nav-section">
            <div class="nav-section-title">Système</div>
            <div class="nav-item">
                <a href="settings.php" class="nav-link <?php echo ($active ?? '') === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="profile.php" class="nav-link <?php echo ($active ?? '') === 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i>
                    <span>Mon profil</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="logout.php" class="nav-link" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </div>
    </nav>
</aside>