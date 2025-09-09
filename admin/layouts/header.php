<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $siteSettings = function_exists('getSiteSettings') ? getSiteSettings() : ['site_name' => 'StyleHub']; ?>
    <title><?php echo htmlspecialchars($pageTitle ?? 'Admin'); ?> - <?php echo htmlspecialchars($siteSettings['site_name']); ?> Admin</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Admin CSS -->
    <link href="assets/css/admin.css" rel="stylesheet">
    
    <!-- Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Interface d'administration E-Commerce">
</head>
<body>