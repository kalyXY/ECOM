<?php
require_once 'includes/config.php';

// User must be logged in to access this page
if (empty($_SESSION['customer_id'])) {
    $_SESSION['message'] = 'Veuillez vous connecter pour accéder à votre profil.';
    $_SESSION['message_type'] = 'warning';
    header('Location: login.php');
    exit;
}

$customer_id = (int)$_SESSION['customer_id'];
$errors = [];
$success_message = '';

// Handle profile information update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($first_name)) {
        $errors[] = 'Le prénom est obligatoire.';
    }
    if (empty($last_name)) {
        $errors[] = 'Le nom de famille est obligatoire.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "UPDATE customers SET first_name = :first_name, last_name = :last_name, phone = :phone, updated_at = NOW() WHERE id = :id"
            );
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':phone' => $phone,
                ':id' => $customer_id
            ]);
            $success_message = 'Vos informations ont été mises à jour avec succès.';
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la mise à jour de votre profil. Veuillez réessayer.';
            error_log('Profile update failed: ' . $e->getMessage());
        }
    }
}

// Fetch customer data
try {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
    $stmt->execute([':id' => $customer_id]);
    $customer = $stmt->fetch();
    if (!$customer) {
        // This case is unlikely if session is managed properly
        unset($_SESSION['customer_id'], $_SESSION['customer_email']);
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    die("Erreur: Impossible de récupérer les informations du client.");
}

// Fetch customer orders
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = :cid ORDER BY created_at DESC");
    $stmt->execute([':cid' => $customer_id]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}


$pageTitle = 'Mon Compte';
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
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Update Form -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Mes Informations</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error) echo htmlspecialchars($error) . '<br>'; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="profile.php">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($customer['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($customer['last_name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($customer['email']); ?>" disabled readonly>
                            <div class="form-text">L'email ne peut pas être modifié.</div>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Order History -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-box me-2"></i>Historique des Commandes</h5>
                    <?php if (!empty($orders)): ?>
                        <a href="orders.php" class="btn btn-sm btn-outline-primary">Voir toutes les commandes</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div class="text-center text-muted py-4">
                            <p>Vous n'avez encore passé aucune commande.</p>
                            <a href="products.php" class="btn btn-primary">Commencer mes achats</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['order_number'] ?? ('#' . $order['id'])); ?></td>
                                            <td><?php echo date("d/m/Y", strtotime($order['created_at'])); ?></td>
                                            <td><span class="badge bg-secondary text-capitalize"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                            <td class="fw-bold"><?php echo number_format((float)$order['total_amount'], 2, ',', ' '); ?> €</td>
                                            <td><a class="btn btn-sm btn-outline-secondary" href="orders.php?id=<?php echo (int)$order['id']; ?>">Détails</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
