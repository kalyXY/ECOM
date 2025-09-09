<?php
require_once '../config/bootstrap.php';
require_once '../includes/config.php';

if (!isset($_SESSION['customer_id'])) {
    App::redirect('login.php');
}

$pageTitle = 'Mon profil';

$customer = null;
try {
    $stmt = $pdo->prepare('SELECT id, name, email, phone, address, city, postal_code, country, created_at FROM customers WHERE id = ?');
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch();
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $name = Security::sanitizeInput($_POST['name'] ?? '');
    $phone = Security::sanitizeInput($_POST['phone'] ?? '');
    $address = Security::sanitizeInput($_POST['address'] ?? '');
    $city = Security::sanitizeInput($_POST['city'] ?? '');
    $postal = Security::sanitizeInput($_POST['postal_code'] ?? '');
    $country = Security::sanitizeInput($_POST['country'] ?? '');
    try {
        $stmt = $pdo->prepare('UPDATE customers SET name=?, phone=?, address=?, city=?, postal_code=?, country=? WHERE id=?');
        $stmt->execute([$name, $phone, $address, $city, $postal, $country, $_SESSION['customer_id']]);
        $_SESSION['customer_name'] = $name;
        App::redirect('profile.php');
    } catch (Exception $e) {}
}
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-user-circle fa-4x text-muted"></i>
                    <h5 class="mt-3 mb-0"><?php echo htmlspecialchars($customer['name'] ?? ''); ?></h5>
                    <div class="text-muted"><?php echo htmlspecialchars($customer['email'] ?? ''); ?></div>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm mt-3">Se déconnecter</a>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Informations</h6></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input name="phone" class="form-control" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Adresse</label>
                                <input name="address" class="form-control" value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ville</label>
                                <input name="city" class="form-control" value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Code Postal</label>
                                <input name="postal_code" class="form-control" value="<?php echo htmlspecialchars($customer['postal_code'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Pays</label>
                                <input name="country" class="form-control" value="<?php echo htmlspecialchars($customer['country'] ?? ''); ?>">
                            </div>
                        </div>
                        <button class="btn btn-primary mt-3" type="submit">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

