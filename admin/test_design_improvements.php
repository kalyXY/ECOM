<?php
require_once 'config.php';
requireLogin();

$pageTitle = 'Test des Améliorations Design';
$active = 'test';
include 'layouts/header.php';
?>

<div class="admin-wrapper">
    <?php include 'layouts/sidebar.php'; ?>
    
    <main class="admin-content">
        <?php include 'layouts/topbar.php'; ?>
        
        <div class="page-content">
            <!-- En-tête de page moderne -->
            <div class="page-header-modern" data-aos="fade-down">
                <div class="header-content">
                    <div class="header-title">
                        <h1 class="gradient-text">🎨 Test des Améliorations Design</h1>
                        <p class="text-muted">Vérification de toutes les nouvelles fonctionnalités premium</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="AdminNotify.success('Test notification réussie !', 'Succès')">
                            <i class="fas fa-bell me-2"></i>Test Notification
                        </button>
                    </div>
                </div>
            </div>

            <!-- Test des stats cards -->
            <div class="stats-grid">
                <div class="stat-card primary" data-aos="fade-up" data-aos-delay="100" data-tooltip="Card avec effet hover premium">
                    <div class="stat-header">
                        <div class="stat-title">Design Premium</div>
                        <div class="stat-icon primary">
                            <i class="fas fa-paint-brush"></i>
                        </div>
                    </div>
                    <div class="stat-value">100%</div>
                    <div class="stat-change positive">
                        <i class="fas fa-check"></i>
                        <span>Fonctionnel</span>
                    </div>
                </div>

                <div class="stat-card success" data-aos="fade-up" data-aos-delay="200" data-tooltip="Animations AOS activées">
                    <div class="stat-header">
                        <div class="stat-title">Animations</div>
                        <div class="stat-icon success">
                            <i class="fas fa-magic"></i>
                        </div>
                    </div>
                    <div class="stat-value">AOS</div>
                    <div class="stat-change positive">
                        <i class="fas fa-play"></i>
                        <span>Actives</span>
                    </div>
                </div>

                <div class="stat-card warning" data-aos="fade-up" data-aos-delay="300" data-tooltip="Mode sombre disponible">
                    <div class="stat-header">
                        <div class="stat-title">Mode Sombre</div>
                        <div class="stat-icon warning">
                            <i class="fas fa-moon"></i>
                        </div>
                    </div>
                    <div class="stat-value">🌙</div>
                    <div class="stat-change positive">
                        <i class="fas fa-toggle-on"></i>
                        <span>Disponible</span>
                    </div>
                </div>

                <div class="stat-card danger" data-aos="fade-up" data-aos-delay="400" data-tooltip="Toutes les fonctionnalités préservées">
                    <div class="stat-header">
                        <div class="stat-title">Compatibilité</div>
                        <div class="stat-icon danger">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                    <div class="stat-value">100%</div>
                    <div class="stat-change positive">
                        <i class="fas fa-check-double"></i>
                        <span>Préservée</span>
                    </div>
                </div>
            </div>

            <!-- Test des composants -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card" data-aos="fade-right">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-test-tube me-2"></i>
                                Test des Boutons Premium
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3">
                                <button class="btn btn-primary" onclick="AdminNotify.info('Bouton primaire testé')">
                                    <i class="fas fa-star me-1"></i>Primaire
                                </button>
                                <button class="btn btn-success" onclick="AdminNotify.success('Bouton succès testé')">
                                    <i class="fas fa-check me-1"></i>Succès
                                </button>
                                <button class="btn btn-danger" onclick="AdminNotify.error('Bouton danger testé')">
                                    <i class="fas fa-times me-1"></i>Danger
                                </button>
                                <button class="btn btn-outline-primary hover-lift">
                                    <i class="fas fa-heart me-1"></i>Outline
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card" data-aos="fade-left">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-palette me-2"></i>
                                Test des Couleurs & Gradients
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div style="height: 60px; background: var(--primary-gradient); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                        Primary Gradient
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="height: 60px; background: var(--success-gradient); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                        Success Gradient
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="height: 60px; background: var(--warning-gradient); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                        Warning Gradient
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="height: 60px; background: var(--danger-gradient); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                        Danger Gradient
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test des forms -->
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="card" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-edit me-2"></i>
                                Test Formulaire Premium
                            </h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Nom du produit</label>
                                            <input type="text" class="form-control" placeholder="Entrez le nom..." required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Prix (€)</label>
                                            <input type="number" class="form-control" placeholder="0.00" step="0.01">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" rows="3" placeholder="Description du produit..."></textarea>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Sauvegarder
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo me-1"></i>Réinitialiser
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card" data-aos="fade-left">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-cogs me-2"></i>
                                Tests Fonctionnels
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary btn-sm" onclick="testNotifications()">
                                    <i class="fas fa-bell me-1"></i>Test Notifications
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="testAnimations()">
                                    <i class="fas fa-play me-1"></i>Test Animations
                                </button>
                                <button class="btn btn-outline-warning btn-sm" onclick="testTheme()">
                                    <i class="fas fa-palette me-1"></i>Toggle Thème
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="testInteractions()">
                                    <i class="fas fa-mouse me-1"></i>Test Interactions
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test table -->
            <div class="mt-4">
                <div class="table-container" data-aos="fade-up">
                    <div class="table-header">
                        <h5 class="table-title">
                            <i class="fas fa-table me-2"></i>
                            Table Interactive Premium
                        </h5>
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-filter me-1"></i>Filtrer
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fonctionnalité</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-tooltip="Cliquez pour sélectionner">
                                    <td><strong>Animations AOS</strong></td>
                                    <td><span class="badge bg-success">✅ Actif</span></td>
                                    <td>Visual</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Test</button>
                                    </td>
                                </tr>
                                <tr data-tooltip="Hover effects activés">
                                    <td><strong>Hover Effects</strong></td>
                                    <td><span class="badge bg-success">✅ Actif</span></td>
                                    <td>Interaction</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success">Test</button>
                                    </td>
                                </tr>
                                <tr data-tooltip="Mode sombre disponible">
                                    <td><strong>Dark Mode</strong></td>
                                    <td><span class="badge bg-warning">🌙 Disponible</span></td>
                                    <td>Theme</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning">Toggle</button>
                                    </td>
                                </tr>
                                <tr data-tooltip="Notifications temps réel">
                                    <td><strong>Notifications</strong></td>
                                    <td><span class="badge bg-info">🔔 Actif</span></td>
                                    <td>System</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info">Test</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Fonctions de test pour les nouvelles fonctionnalités
function testNotifications() {
    AdminNotify.success('Notification de succès testée !', 'Test Réussi');
    setTimeout(() => AdminNotify.warning('Notification d\'avertissement', 'Attention'), 1000);
    setTimeout(() => AdminNotify.error('Notification d\'erreur', 'Erreur Test'), 2000);
    setTimeout(() => AdminNotify.info('Notification d\'information', 'Info'), 3000);
}

function testAnimations() {
    // Animer tous les éléments AOS
    AOS.refresh();
    
    // Animation des compteurs
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach(stat => {
        const originalValue = stat.textContent;
        stat.textContent = '0';
        
        if (window.adminUI && window.adminUI.animateCounter) {
            window.adminUI.animateCounter(stat, parseInt(originalValue) || 100);
        }
    });
    
    AdminNotify.info('Animations relancées !', 'Test Animations');
}

function testTheme() {
    if (window.adminUI) {
        window.adminUI.toggleTheme();
        AdminNotify.info('Thème basculé !', 'Mode Sombre');
    } else {
        AdminNotify.warning('AdminUI non initialisé', 'Erreur');
    }
}

function testInteractions() {
    // Test des interactions de table
    const rows = document.querySelectorAll('.table tbody tr');
    rows.forEach((row, index) => {
        setTimeout(() => {
            row.click();
        }, index * 200);
    });
    
    AdminNotify.success('Interactions testées !', 'Test Complet');
}

// Animation d'entrée personnalisée
document.addEventListener('DOMContentLoaded', function() {
    // Animer l'apparition du contenu
    const content = document.querySelector('.page-content');
    content.style.opacity = '0';
    content.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        content.style.transition = 'all 0.6s ease';
        content.style.opacity = '1';
        content.style.transform = 'translateY(0)';
    }, 100);
    
    // Message de bienvenue
    setTimeout(() => {
        AdminNotify.success('Interface améliorée chargée avec succès !', '🎉 Design Premium Activé');
    }, 1000);
});
</script>

<?php include 'layouts/footer.php'; ?>