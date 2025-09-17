<?php
require_once 'includes/config.php';

// Must be logged in as customer to place order
if (empty($_SESSION['customer_id'])) {
    $_SESSION['message'] = 'Vous devez être connecté pour passer une commande. Veuillez vous connecter ou créer un compte.';
    $_SESSION['message_type'] = 'warning';
    header('Location: login.php');
    exit;
}

// Ensure cart exists and not empty
if (empty($_SESSION['cart'])) {
    $_SESSION['message'] = 'Votre panier est vide.';
    $_SESSION['message_type'] = 'warning';
    header('Location: cart.php');
    exit;
}

// Compute totals
$items = $_SESSION['cart'];
$subtotal = 0.0;
$totalQty = 0;
foreach ($items as $pid => $it) {
    $line = ((float)$it['price']) * ((int)$it['qty']);
    $subtotal += $line;
    $totalQty += (int)$it['qty'];
}

$taxRate = 0.20; // 20% TVA (peut venir de settings)
$tax = round($subtotal * $taxRate, 2);
$shipping = 0.00; // livraison gratuite pour démo
$discount = 0.00;
$total = $subtotal + $tax + $shipping - $discount;

try {
    $pdo->beginTransaction();

    // Create order
    $stmt = $pdo->prepare("INSERT INTO orders (
        order_number, customer_id, customer_email, subtotal, tax_amount, shipping_amount, discount_amount, total_amount,
        status, payment_status, shipping_status, currency, created_at
    ) VALUES (
        :order_number, :customer_id, :customer_email, :subtotal, :tax, :shipping, :discount, :total,
        'pending', 'pending', 'not_shipped', 'EUR', NOW()
    )");

    // Generate simple order number
    $orderNumber = 'ORD-' . date('Ymd-His') . '-' . rand(100, 999);

    $stmt->execute([
        ':order_number' => $orderNumber,
        ':customer_id' => (int)$_SESSION['customer_id'],
        ':customer_email' => $_SESSION['customer_email'] ?? null,
        ':subtotal' => $subtotal,
        ':tax' => $tax,
        ':shipping' => $shipping,
        ':discount' => $discount,
        ':total' => $total,
    ]);

    $orderId = (int)$pdo->lastInsertId();

    // Insert order items
    $itemStmt = $pdo->prepare("INSERT INTO order_items (
        order_id, product_id, product_name, product_sku, quantity, unit_price, total_price, created_at
    ) VALUES (
        :order_id, :product_id, :product_name, :product_sku, :quantity, :unit_price, :total_price, NOW()
    )");

    foreach ($items as $pid => $it) {
        $quantity = (int)$it['qty'];
        $unit = (float)$it['price'];
        $lineTotal = $unit * $quantity;

        // Fetch product for name/sku if not provided
        $productName = $it['name'] ?? ('Produit #' . $pid);
        $sku = null;
        try {
            $ps = $pdo->prepare('SELECT name, sku FROM products WHERE id = :id');
            $ps->execute([':id' => (int)$pid]);
            if ($row = $ps->fetch()) {
                $productName = $row['name'] ?: $productName;
                $sku = $row['sku'] ?? null;
            }
        } catch (Exception $e) {}

        $itemStmt->execute([
            ':order_id' => $orderId,
            ':product_id' => (int)$pid,
            ':product_name' => $productName,
            ':product_sku' => $sku,
            ':quantity' => $quantity,
            ':unit_price' => $unit,
            ':total_price' => $lineTotal,
        ]);
    }

    $pdo->commit();

    // Redirect customer to payment with Lygos
    header('Location: pay_with_lygos.php?order_id=' . $orderId);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    $_SESSION['message'] = "Erreur lors de la création de la commande.";
    $_SESSION['message_type'] = 'danger';
    header('Location: cart.php');
    exit;
}
?>


