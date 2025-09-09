<?php
require_once '../config/bootstrap.php';
require_once '../includes/config.php';

$pageTitle = 'Inscription';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token CSRF invalide';
    }

    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ? $_POST['email'] : '';
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($name === '') $errors[] = 'Nom requis';
    if ($email === '') $errors[] = 'Email valide requis';
    if (strlen($password) < 6) $errors[] = 'Mot de passe trop court (6+)';
    if ($password !== $password2) $errors[] = 'Les mots de passe ne correspondent pas';

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('SELECT id FROM customers WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Cet email est déjà utilisé';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('INSERT INTO customers (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())');
                $stmt->execute([$name, $email, $hash]);
                $_SESSION['customer_id'] = $pdo->lastInsertId();
                $_SESSION['customer_name'] = $name;
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
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0">Créer un compte</h5></div>
                <div class="card-body">
                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?><li><?php echo htmlspecialchars($err); ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmer le mot de passe</label>
                            <input type="password" name="password2" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">S'inscrire</button>
                        <div class="text-center mt-3"><a href="login.php">Déjà un compte ? Se connecter</a></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

<?php include '../includes/footer.php'; ?>

