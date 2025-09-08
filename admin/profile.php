<?php
require_once 'config.php';
requireLogin();

$errors = [];
$success = '';

// Récupérer les informations de l'utilisateur actuel
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['admin_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['message'] = 'Utilisateur non trouvé.';
        $_SESSION['message_type'] = 'danger';
        header('Location: logout.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['message'] = 'Erreur lors de la récupération du profil.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token CSRF invalide.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $fullName = trim($_POST['full_name'] ?? '');
            
            // Validation
            if (empty($username)) {
                $errors[] = 'Le nom d\'utilisateur est requis.';
            }
            
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'L\'adresse email n\'est pas valide.';
            }
            
            // Vérifier si le nom d'utilisateur est déjà pris
            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':id', $_SESSION['admin_id'], PDO::PARAM_INT);
                    $stmt->execute();
                    
                    if ($stmt->fetch()) {
                        $errors[] = 'Ce nom d\'utilisateur est déjà utilisé.';
                    }
                } catch (PDOException $e) {
                    $errors[] = 'Erreur lors de la vérification.';
                }
            }
            
            // Mettre à jour le profil
            if (empty($errors)) {
                try {
                    // Vérifier si les colonnes existent
                    $columns = ['username'];
                    $values = [':username' => $username];
                    
                    try {
                        $pdo->query("SELECT email FROM users LIMIT 1");
                        $columns[] = 'email';
                        $values[':email'] = $email;
                    } catch (PDOException $e) {
                        // La colonne email n'existe pas
                    }
                    
                    try {
                        $pdo->query("SELECT full_name FROM users LIMIT 1");
                        $columns[] = 'full_name';
                        $values[':full_name'] = $fullName;
                    } catch (PDOException $e) {
                        // La colonne full_name n'existe pas
                    }
                    
                    $setClause = implode(' = ?, ', $columns) . ' = ?';
                    $sql = "UPDATE users SET $setClause WHERE id = ?";
                    
                    $stmt = $pdo->prepare($sql);
                    $params = array_values($values);
                    $params[] = $_SESSION['admin_id'];
                    $stmt->execute($params);
                    
                    // Mettre à jour la session
                    $_SESSION['admin_username'] = $username;
                    
                    $success = 'Profil mis à jour avec succès.';
                    
                    // Recharger les données utilisateur
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                    $stmt->bindParam(':id', $_SESSION['admin_id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $user = $stmt->fetch();
                    
                } catch (PDOException $e) {
                    $errors[] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
                }
            }
        }
        
        elseif ($action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation
            if (empty($currentPassword)) {
                $errors[] = 'Le mot de passe actuel est requis.';
            }
            
            if (empty($newPassword)) {
                $errors[] = 'Le nouveau mot de passe est requis.';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'La confirmation du mot de passe ne correspond pas.';
            }
            
            // Vérifier le mot de passe actuel
            if (empty($errors)) {
                if (!password_verify($currentPassword, $user['password_hash'])) {
                    $errors[] = 'Le mot de passe actuel est incorrect.';
                }
            }
            
            // Changer le mot de passe
            if (empty($errors)) {
                try {
                    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :id");
                    $stmt->bindParam(':password_hash', $newPasswordHash);
                    $stmt->bindParam(':id', $_SESSION['admin_id'], PDO::PARAM_INT);
                    $stmt->execute();
                    
                    $success = 'Mot de passe modifié avec succès.';
                    
                } catch (PDOException $e) {
                    $errors[] = 'Erreur lors du changement de mot de passe : ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'Mon Profil';
$active = 'profile';
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
                        <h1 class="page-title">Mon Profil</h1>
                        <p class="page-subtitle">Gérez vos informations personnelles</p>
                    </div>
                    <div class="user-avatar" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Erreurs détectées :</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Informations du profil -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-user me-2"></i>Informations personnelles
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="update_profile">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="username" class="form-label">Nom d'utilisateur *</label>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                            <div class="invalid-feedback">
                                                Veuillez saisir un nom d'utilisateur.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="email" class="form-label">Adresse email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                            <div class="form-text">Optionnel - pour les notifications</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="full_name" class="form-label">Nom complet</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Mettre à jour le profil
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Changer le mot de passe -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-lock me-2"></i>Changer le mot de passe
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="change_password">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="form-group mb-3">
                                    <label for="current_password" class="form-label">Mot de passe actuel *</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    <div class="invalid-feedback">
                                        Veuillez saisir votre mot de passe actuel.
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="new_password" class="form-label">Nouveau mot de passe *</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                                   minlength="6" required>
                                            <div class="invalid-feedback">
                                                Le mot de passe doit contenir au moins 6 caractères.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="confirm_password" class="form-label">Confirmer le mot de passe *</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                   minlength="6" required>
                                            <div class="invalid-feedback">
                                                Veuillez confirmer votre mot de passe.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key me-2"></i>Changer le mot de passe
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Informations du compte -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-info-circle me-2"></i>Informations du compte
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>ID utilisateur :</strong><br>
                                <span class="badge bg-secondary"><?php echo $user['id']; ?></span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Membre depuis :</strong><br>
                                <small class="text-muted">
                                    <?php echo formatDate($user['created_at']); ?>
                                </small>
                            </div>
                            
                            <?php if (isset($user['updated_at'])): ?>
                            <div class="mb-3">
                                <strong>Dernière modification :</strong><br>
                                <small class="text-muted">
                                    <?php echo formatDate($user['updated_at']); ?>
                                </small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <strong>Statut :</strong><br>
                                <span class="badge bg-success">Administrateur</span>
                            </div>
                        </div>
                    </div>

                    <!-- Activité récente -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-clock me-2"></i>Activité récente
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Connexion</h6>
                                        <p class="timeline-text">Dernière connexion aujourd'hui</p>
                                        <small class="text-muted">Il y a quelques minutes</small>
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Profil mis à jour</h6>
                                        <p class="timeline-text">Informations personnelles modifiées</p>
                                        <small class="text-muted">Il y a 2 jours</small>
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Produit ajouté</h6>
                                        <p class="timeline-text">Nouveau produit dans le catalogue</p>
                                        <small class="text-muted">Il y a 1 semaine</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-bolt me-2"></i>Actions rapides
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="products.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-box me-2"></i>Gérer les produits
                                </a>
                                <a href="orders.php" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-shopping-cart me-2"></i>Voir les commandes
                                </a>
                                <a href="settings.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-cog me-2"></i>Paramètres
                                </a>
                                <a href="../index.php" target="_blank" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-external-link-alt me-2"></i>Voir le site
                                </a>
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
// Validation du mot de passe
document.getElementById("confirm_password").addEventListener("input", function() {
    const newPassword = document.getElementById("new_password").value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword) {
        this.setCustomValidity("Les mots de passe ne correspondent pas");
    } else {
        this.setCustomValidity("");
    }
});

// Indicateur de force du mot de passe
document.getElementById("new_password").addEventListener("input", function() {
    const password = this.value;
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    // Vous pourriez ajouter un indicateur visuel ici
});
</script>
';

include 'layouts/footer.php';
?>