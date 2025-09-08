<?php
require_once 'config.php';
requireLogin();

// Vérifier si la table settings existe, sinon la créer
$settingsTableExists = false;
try {
    $pdo->query("SELECT COUNT(*) FROM settings LIMIT 1");
    $settingsTableExists = true;
} catch (PDOException $e) {
    // Créer la table settings
    try {
        $pdo->exec("
            CREATE TABLE settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                setting_type ENUM('text', 'number', 'boolean', 'email', 'url') DEFAULT 'text',
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // Insérer les paramètres par défaut
        $defaultSettings = [
            ['site_name', 'E-Commerce', 'text', 'Nom du site'],
            ['site_description', 'Votre boutique en ligne de confiance', 'text', 'Description du site'],
            ['site_email', 'contact@ecommerce.com', 'email', 'Email de contact'],
            ['site_phone', '01 23 45 67 89', 'text', 'Téléphone de contact'],
            ['site_address', '123 Rue du Commerce, 75001 Paris', 'text', 'Adresse physique'],
            ['currency', 'EUR', 'text', 'Devise par défaut'],
            ['tax_rate', '20', 'number', 'Taux de TVA (%)'],
            ['shipping_cost', '5.99', 'number', 'Frais de livraison (€)'],
            ['free_shipping_threshold', '50', 'number', 'Seuil livraison gratuite (€)'],
            ['products_per_page', '12', 'number', 'Produits par page'],
            ['maintenance_mode', '0', 'boolean', 'Mode maintenance'],
            ['allow_registration', '1', 'boolean', 'Autoriser les inscriptions'],
            ['email_notifications', '1', 'boolean', 'Notifications par email'],
            ['google_analytics', '', 'text', 'Code Google Analytics'],
            ['facebook_pixel', '', 'text', 'Code Facebook Pixel'],
            ['meta_keywords', 'ecommerce, boutique, en ligne', 'text', 'Mots-clés SEO'],
            ['smtp_host', '', 'text', 'Serveur SMTP'],
            ['smtp_port', '587', 'number', 'Port SMTP'],
            ['smtp_username', '', 'text', 'Nom d\'utilisateur SMTP'],
            ['smtp_password', '', 'text', 'Mot de passe SMTP']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
        foreach ($defaultSettings as $setting) {
            $stmt->execute($setting);
        }
        
        $settingsTableExists = true;
    } catch (PDOException $e) {
        // Erreur lors de la création
    }
}

// Traitement du formulaire
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    try {
        $pdo->beginTransaction();
        
        foreach ($_POST as $key => $value) {
            if ($key !== 'csrf_token') {
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = :key");
                $stmt->bindParam(':value', $value);
                $stmt->bindParam(':key', $key);
                $stmt->execute();
            }
        }
        
        $pdo->commit();
        $message = 'Paramètres sauvegardés avec succès.';
        $messageType = 'success';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = 'Erreur lors de la sauvegarde : ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Récupérer les paramètres
$settings = [];
if ($settingsTableExists) {
    try {
        $stmt = $pdo->query("SELECT * FROM settings ORDER BY setting_key ASC");
        $settingsData = $stmt->fetchAll();
        
        foreach ($settingsData as $setting) {
            $settings[$setting['setting_key']] = $setting;
        }
    } catch (PDOException $e) {
        // Erreur lors de la récupération
    }
}

$pageTitle = 'Paramètres';
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
                        <h1 class="page-title">Paramètres</h1>
                        <p class="page-subtitle">Configurez votre boutique en ligne</p>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary" onclick="resetSettings()">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </button>
                        <button class="btn btn-success" onclick="exportSettings()">
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

            <?php if (!$settingsTableExists): ?>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Table des paramètres non trouvée</h6>
                    <p class="mb-0">La table des paramètres n'existe pas encore. Elle sera créée automatiquement.</p>
                </div>
            <?php else: ?>

            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Paramètres généraux -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-cog me-2"></i>Paramètres généraux
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="site_name" class="form-label">Nom du site</label>
                                            <input type="text" class="form-control" id="site_name" name="site_name" 
                                                   value="<?php echo htmlspecialchars($settings['site_name']['setting_value'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="site_email" class="form-label">Email de contact</label>
                                            <input type="email" class="form-control" id="site_email" name="site_email" 
                                                   value="<?php echo htmlspecialchars($settings['site_email']['setting_value'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="site_description" class="form-label">Description du site</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($settings['site_description']['setting_value'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="site_phone" class="form-label">Téléphone</label>
                                            <input type="text" class="form-control" id="site_phone" name="site_phone" 
                                                   value="<?php echo htmlspecialchars($settings['site_phone']['setting_value'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="currency" class="form-label">Devise</label>
                                            <select class="form-select" id="currency" name="currency">
                                                <option value="EUR" <?php echo ($settings['currency']['setting_value'] ?? '') === 'EUR' ? 'selected' : ''; ?>>Euro (€)</option>
                                                <option value="USD" <?php echo ($settings['currency']['setting_value'] ?? '') === 'USD' ? 'selected' : ''; ?>>Dollar ($)</option>
                                                <option value="GBP" <?php echo ($settings['currency']['setting_value'] ?? '') === 'GBP' ? 'selected' : ''; ?>>Livre (£)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_address" class="form-label">Adresse physique</label>
                                    <textarea class="form-control" id="site_address" name="site_address" rows="2"><?php echo htmlspecialchars($settings['site_address']['setting_value'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Paramètres e-commerce -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-shopping-cart me-2"></i>Paramètres e-commerce
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="tax_rate" class="form-label">Taux de TVA (%)</label>
                                            <input type="number" class="form-control" id="tax_rate" name="tax_rate" 
                                                   step="0.01" min="0" max="100"
                                                   value="<?php echo htmlspecialchars($settings['tax_rate']['setting_value'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="shipping_cost" class="form-label">Frais de livraison (€)</label>
                                            <input type="number" class="form-control" id="shipping_cost" name="shipping_cost" 
                                                   step="0.01" min="0"
                                                   value="<?php echo htmlspecialchars($settings['shipping_cost']['setting_value'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="free_shipping_threshold" class="form-label">Seuil livraison gratuite (€)</label>
                                            <input type="number" class="form-control" id="free_shipping_threshold" name="free_shipping_threshold" 
                                                   step="0.01" min="0"
                                                   value="<?php echo htmlspecialchars($settings['free_shipping_threshold']['setting_value'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="products_per_page" class="form-label">Produits par page</label>
                                    <select class="form-select" id="products_per_page" name="products_per_page">
                                        <option value="6" <?php echo ($settings['products_per_page']['setting_value'] ?? '') === '6' ? 'selected' : ''; ?>>6</option>
                                        <option value="12" <?php echo ($settings['products_per_page']['setting_value'] ?? '') === '12' ? 'selected' : ''; ?>>12</option>
                                        <option value="24" <?php echo ($settings['products_per_page']['setting_value'] ?? '') === '24' ? 'selected' : ''; ?>>24</option>
                                        <option value="48" <?php echo ($settings['products_per_page']['setting_value'] ?? '') === '48' ? 'selected' : ''; ?>>48</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Paramètres SEO -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-search me-2"></i>SEO & Analytics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="meta_keywords" class="form-label">Mots-clés SEO</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                                           value="<?php echo htmlspecialchars($settings['meta_keywords']['setting_value'] ?? ''); ?>"
                                           placeholder="ecommerce, boutique, en ligne">
                                    <div class="form-text">Séparez les mots-clés par des virgules</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="google_analytics" class="form-label">Google Analytics ID</label>
                                            <input type="text" class="form-control" id="google_analytics" name="google_analytics" 
                                                   value="<?php echo htmlspecialchars($settings['google_analytics']['setting_value'] ?? ''); ?>"
                                                   placeholder="G-XXXXXXXXXX">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="facebook_pixel" class="form-label">Facebook Pixel ID</label>
                                            <input type="text" class="form-control" id="facebook_pixel" name="facebook_pixel" 
                                                   value="<?php echo htmlspecialchars($settings['facebook_pixel']['setting_value'] ?? ''); ?>"
                                                   placeholder="123456789012345">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paramètres email -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-envelope me-2"></i>Configuration Email (SMTP)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="smtp_host" class="form-label">Serveur SMTP</label>
                                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                                   value="<?php echo htmlspecialchars($settings['smtp_host']['setting_value'] ?? ''); ?>"
                                                   placeholder="smtp.gmail.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="smtp_port" class="form-label">Port SMTP</label>
                                            <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                                   value="<?php echo htmlspecialchars($settings['smtp_port']['setting_value'] ?? ''); ?>"
                                                   placeholder="587">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="smtp_username" class="form-label">Nom d'utilisateur SMTP</label>
                                            <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                                   value="<?php echo htmlspecialchars($settings['smtp_username']['setting_value'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="smtp_password" class="form-label">Mot de passe SMTP</label>
                                            <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                                   value="<?php echo htmlspecialchars($settings['smtp_password']['setting_value'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Options système -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-toggle-on me-2"></i>Options système
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                           value="1" <?php echo ($settings['maintenance_mode']['setting_value'] ?? '') === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="maintenance_mode">
                                        Mode maintenance
                                    </label>
                                    <div class="form-text">Désactive temporairement le site pour les visiteurs</div>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="allow_registration" name="allow_registration" 
                                           value="1" <?php echo ($settings['allow_registration']['setting_value'] ?? '') === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="allow_registration">
                                        Autoriser les inscriptions
                                    </label>
                                    <div class="form-text">Permet aux nouveaux clients de s'inscrire</div>
                                </div>
                                
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" 
                                           value="1" <?php echo ($settings['email_notifications']['setting_value'] ?? '') === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="email_notifications">
                                        Notifications par email
                                    </label>
                                    <div class="form-text">Envoie des emails pour les nouvelles commandes</div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-tools me-2"></i>Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Sauvegarder les paramètres
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-info" onclick="testEmail()">
                                        <i class="fas fa-envelope me-2"></i>Tester l'email
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-warning" onclick="clearCache()">
                                        <i class="fas fa-broom me-2"></i>Vider le cache
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-danger" onclick="resetToDefaults()">
                                        <i class="fas fa-undo me-2"></i>Valeurs par défaut
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <?php endif; ?>
        </div>
    </main>
</div>

<?php
$pageScripts = '
<script>
function testEmail() {
    alert("Fonctionnalité à venir : Test de configuration email");
}

function clearCache() {
    if (confirm("Êtes-vous sûr de vouloir vider le cache ?")) {
        alert("Fonctionnalité à venir : Vidage du cache");
    }
}

function resetToDefaults() {
    if (confirm("Êtes-vous sûr de vouloir restaurer les valeurs par défaut ?\\n\\nCette action est irréversible.")) {
        alert("Fonctionnalité à venir : Restauration des valeurs par défaut");
    }
}

function resetSettings() {
    if (confirm("Êtes-vous sûr de vouloir réinitialiser tous les paramètres ?")) {
        location.reload();
    }
}

function exportSettings() {
    alert("Fonctionnalité à venir : Export des paramètres");
}

// Gestion des switches
document.querySelectorAll(".form-check-input[type=checkbox]").forEach(function(checkbox) {
    checkbox.addEventListener("change", function() {
        this.value = this.checked ? "1" : "0";
    });
});
</script>
';

include 'layouts/footer.php';
?>