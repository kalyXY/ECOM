<?php
require_once '../config/bootstrap.php';
require_once '../includes/config.php';

$pageTitle = 'Connexion';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token CSRF invalide';
    }

    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ? $_POST['email'] : '';
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email et mot de passe requis';
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('SELECT id, name, password_hash FROM customers WHERE email = ?');
            $stmt->execute([$email]);
            $customer = $stmt->fetch();
            if (!$customer || empty($customer['password_hash']) || !password_verify($password, $customer['password_hash'])) {
                $errors[] = 'Identifiants incorrects';
            } else {
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $customer['name'];
                App::redirect('../index.php');
            }
        } catch (Exception $e) {
            $errors[] = 'Erreur serveur';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0">Se connecter</h5></div>
                <div class="card-body">
                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?><li><?php echo htmlspecialchars($err); ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Connexion</button>
                        <div class="text-center mt-3"><a href="register.php">Créer un compte</a></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

