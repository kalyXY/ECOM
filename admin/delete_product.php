<?php
require_once 'config.php';
requireLogin();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'ID de produit invalide.';
    $_SESSION['message_type'] = 'danger';
    header('Location: products.php');
    exit();
}

$productId = (int)$_GET['id'];

try {
    // Récupérer les informations du produit avant suppression
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['message'] = 'Produit non trouvé.';
        $_SESSION['message_type'] = 'danger';
        header('Location: products.php');
        exit();
    }

    // Supprimer l'image du serveur si elle existe
    if ($product['image_url'] && file_exists('../' . $product['image_url'])) {
        unlink('../' . $product['image_url']);
    }

    // Supprimer le produit de la base de données
    $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $deleteStmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $deleteStmt->execute();

    $_SESSION['message'] = 'Produit "' . htmlspecialchars($product['name']) . '" supprimé avec succès.';
    $_SESSION['message_type'] = 'success';

} catch (PDOException $e) {
    $_SESSION['message'] = 'Erreur lors de la suppression du produit : ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

header('Location: products.php');
exit();
?>