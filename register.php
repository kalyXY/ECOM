<?php
require_once 'includes/config.php';

$errors = [];
$first_name = '';
$last_name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validation
    if (empty($first_name)) {
        $errors[] = 'Le prénom est obligatoire.';
    }
    if (empty($last_name)) {
        $errors[] = 'Le nom de famille est obligatoire.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Une adresse email valide est obligatoire.';
    }
    if (empty($password)) {
        $errors[] = 'Le mot de passe est obligatoire.';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }

    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Cette adresse email est déjà utilisée.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur de base de données. Veuillez réessayer.';
            error_log('Registration check failed: ' . $e->getMessage());
        }
    }

    // If no errors, create user
    if (empty($errors)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO customers (first_name, last_name, email, phone, password_hash, status, created_at, updated_at)
                 VALUES (:first_name, :last_name, :email, :phone, :password_hash, 'active', NOW(), NOW())"
            );

            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':email' => $email,
                ':phone' => $phone,
                ':password_hash' => $password_hash,
            ]);

            $customer_id = $pdo->lastInsertId();

            // Log the user in
            $_SESSION['customer_id'] = (int)$customer_id;
            $_SESSION['customer_email'] = $email;

            // Redirect to profile page
            $_SESSION['message'] = 'Votre compte a été créé avec succès !';
            $_SESSION['message_type'] = 'success';
            header('Location: profile.php');
            exit;

        } catch (PDOException $e) {
            $errors[] = 'Une erreur est survenue lors de la création de votre compte. Veuillez réessayer.';
            error_log('Registration failed: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Créer un compte';
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
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Créer votre compte client</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="register.php" class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">Adresse Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="Optionnel">
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">8 caractères minimum.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Créer mon compte
                            </button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <p class="mb-0">Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
