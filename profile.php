<?php
require_once 'includes/config.php';

// Simple session-based customer access: identify by email (dev/demo)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email !== '') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $customer = $stmt->fetch();
            if ($customer) {
                $_SESSION['customer_id'] = (int)$customer['id'];
                $_SESSION['customer_email'] = $customer['email'];
            } else {
                // Créer un client minimal si non existant (optionnel)
                $ins = $pdo->prepare("INSERT INTO customers (email, status, created_at) VALUES (:email, 'active', NOW())");
                $ins->execute([':email' => $email]);
                $_SESSION['customer_id'] = (int)$pdo->lastInsertId();
                $_SESSION['customer_email'] = $email;
            }
        } catch (Exception $e) {
            // ignore
        }
        header('Location: profile.php');
        exit;
    }
}

// Logout client
if (($_GET['action'] ?? '') === 'logout') {
    unset($_SESSION['customer_id'], $_SESSION['customer_email']);
    header('Location: profile.php');
    exit;
}

$customer = null;
if (!empty($_SESSION['customer_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$_SESSION['customer_id']]);
        $customer = $stmt->fetch();
    } catch (Exception $e) {
        $customer = null;
    }
}

// Fetch orders for this customer
$orders = [];
if ($customer) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = :cid ORDER BY created_at DESC");
        $stmt->execute([':cid' => (int)$customer['id']]);
        $orders = $stmt->fetchAll();
    } catch (Exception $e) {
        $orders = [];
    }
}

$pageTitle = 'Mon compte';
include 'includes/header.php';
?>

<div class="bg-light py-3">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Accueil</a></li>
        <li class="breadcrumb-item active" aria-current="page">Mon compte</li>
      </ol>
    </nav>
  </div>
  </div>

<div class="container py-5">
  <?php if (!$customer): ?>
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Se connecter à votre compte</h5>
          </div>
          <div class="card-body">
            <form method="POST" class="row g-3">
              <div class="col-12">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required placeholder="votre@email.com">
              </div>
              <div class="col-12 d-grid">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-sign-in-alt me-2"></i>Continuer
                </button>
              </div>
            </form>
            <div class="text-muted small mt-3">Astuce: en dev, entrez simplement votre email, un compte sera créé si nécessaire.</div>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card shadow-sm">
          <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-id-card me-2"></i>Profil</h6>
          </div>
          <div class="card-body">
            <div class="mb-2"><strong>Nom:</strong> <?php echo htmlspecialchars(trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')) ?: '—'); ?></div>
            <div class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></div>
            <div class="mb-2"><strong>Téléphone:</strong> <?php echo htmlspecialchars($customer['phone'] ?? '—'); ?></div>
            <div class="mb-2"><strong>Statut:</strong> <span class="badge bg-success">Client</span></div>
            <a href="profile.php?action=logout" class="btn btn-outline-secondary btn-sm"><i class="fas fa-sign-out-alt me-1"></i>Se déconnecter</a>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-box me-2"></i>Mes commandes</h6>
            <a href="orders.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
          </div>
          <div class="card-body">
            <?php if (empty($orders)): ?>
              <div class="text-center text-muted py-4">Aucune commande pour le moment.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Date</th>
                      <th>Statut</th>
                      <th>Total</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach (array_slice($orders, 0, 5) as $o): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($o['order_number'] ?? ('ORD-' . $o['id'])); ?></td>
                        <td><?php echo htmlspecialchars($o['created_at']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($o['status']); ?></span></td>
                        <td class="fw-bold text-primary"><?php echo number_format((float)$o['total_amount'], 2, ',', ' '); ?> €</td>
                        <td><a class="btn btn-sm btn-outline-secondary" href="orders.php?id=<?php echo (int)$o['id']; ?>">Détails</a></td>
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
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>


