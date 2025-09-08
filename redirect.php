<?php
// Fichier de redirection pour l'ancien système
// Redirige automatiquement vers le nouveau dossier admin

$redirects = [
    'login.php' => 'admin/login.php',
    'admin.php' => 'admin/index.php',
    'products.php' => 'admin/products.php',
    'add_product.php' => 'admin/add_product.php',
    'edit_product.php' => 'admin/edit_product.php',
    'delete_product.php' => 'admin/delete_product.php',
    'logout.php' => 'admin/logout.php'
];

$currentFile = basename($_SERVER['PHP_SELF']);

if (isset($redirects[$currentFile])) {
    $queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
    header('Location: ' . $redirects[$currentFile] . $queryString, true, 301);
    exit();
}

// Si aucune redirection trouvée, aller à l'accueil
header('Location: index.php', true, 301);
exit();
?>