<?php
require_once 'includes/config.php';

// If user is already logged in, redirect to profile
if (!empty($_SESSION['customer_id'])) {
    header('Location: profile.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Une adresse email valide est obligatoire.';
    }
    if (empty($password)) {
        $errors[] = 'Le mot de passe est obligatoire.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, email, password_hash FROM customers WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $customer = $stmt->fetch();

            if ($customer && password_verify($password, $customer['password_hash'])) {
                // Password is correct, log the user in
                $_SESSION['customer_id'] = (int)$customer['id'];
                $_SESSION['customer_email'] = $customer['email'];

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Redirect to profile page
                header('Location: profile.php');
                exit;
            } else {
                $errors[] = 'Email ou mot de passe incorrect.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur de connexion. Veuillez réessayer.';
            error_log('Login failed: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Connexion';
include 'includes/header.php';
?>

<div class="bg-light py-3">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Accueil</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo $pageTitle; ?></li>
      </ol>
    </nav>
  </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Connectez-vous</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                            <?php echo $_SESSION['message']; ?>
                        </div>
                        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php" class="row g-3">
                        <div class="col-12">
                            <label for="email" class="form-label">Adresse Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                            </button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <p class="mb-0">Pas encore de compte ? <a href="register.php">Créez-en un</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
