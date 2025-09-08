<!-- Topbar Admin -->
<header class="admin-topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <nav class="breadcrumb">
            <div class="breadcrumb-item">
                <i class="fas fa-home"></i>
                <span>Admin</span>
            </div>
            <?php if (isset($pageTitle) && $pageTitle !== 'Dashboard'): ?>
            <div class="breadcrumb-item active">
                <?php echo htmlspecialchars($pageTitle); ?>
            </div>
            <?php endif; ?>
        </nav>
    </div>
    
    <div class="topbar-right">
        <!-- Notifications -->
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-bell"></i>
                <span class="badge bg-danger">3</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-shopping-cart me-2"></i>Nouvelle commande</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Nouveau client</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-exclamation-triangle me-2"></i>Stock faible</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-center" href="#">Voir toutes les notifications</a></li>
            </ul>
        </div>
        
        <!-- Menu utilisateur -->
        <div class="dropdown">
            <div class="user-menu" data-bs-toggle="dropdown" role="button">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></div>
                    <div class="user-role">Administrateur</div>
                </div>
                <i class="fas fa-chevron-down"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Paramètres</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i>Voir le site</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
            </ul>
        </div>
    </div>
</header>