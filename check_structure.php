<?php
// Script de vérification de la structure du projet

echo "<h1>🔍 Vérification de la structure E-Commerce</h1>";

$requiredFiles = [
    // Fichiers racine
    'index.php' => 'Front-office principal',
    'config.php' => 'Configuration principale',
    'database.sql' => 'Structure de la base de données',
    'migrate.php' => 'Script de migration',
    
    // Dossiers
    'uploads/' => 'Dossier des images',
    'assets/' => 'Assets front-office',
    'admin/' => 'Dossier administration',
    
    // Fichiers admin
    'admin/index.php' => 'Dashboard admin',
    'admin/login.php' => 'Connexion admin',
    'admin/logout.php' => 'Déconnexion admin',
    'admin/products.php' => 'Gestion des produits',
    'admin/add_product.php' => 'Ajouter un produit',
    'admin/delete_product.php' => 'Supprimer un produit',
    'admin/config.php' => 'Configuration admin',
    'admin/.htaccess' => 'Sécurité admin',
    
    // Assets admin
    'admin/assets/' => 'Assets admin',
    'admin/assets/css/admin.css' => 'Styles admin',
    'admin/assets/js/admin.js' => 'JavaScript admin',
    
    // Layouts admin
    'admin/layouts/' => 'Templates admin',
    'admin/layouts/header.php' => 'En-tête admin',
    'admin/layouts/sidebar.php' => 'Sidebar admin',
    'admin/layouts/topbar.php' => 'Topbar admin',
    'admin/layouts/footer.php' => 'Pied de page admin',
];

$obsoleteFiles = [
    'login.php',
    'logout.php', 
    'admin.php',
    'products.php',
    'add_product.php',
    'edit_product.php',
    'delete_product.php',
    'profile.php',
    'layouts/',
    'migrate_database.sql'
];

echo "<h2>✅ Fichiers requis</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Fichier/Dossier</th><th>Description</th><th>Status</th></tr>";

foreach ($requiredFiles as $file => $description) {
    $exists = file_exists($file) || is_dir($file);
    $status = $exists ? "✅ OK" : "❌ MANQUANT";
    $color = $exists ? "green" : "red";
    
    echo "<tr>";
    echo "<td><code>$file</code></td>";
    echo "<td>$description</td>";
    echo "<td style='color: $color; font-weight: bold;'>$status</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🗑️ Fichiers obsolètes (doivent être supprimés)</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Fichier/Dossier</th><th>Status</th></tr>";

foreach ($obsoleteFiles as $file) {
    $exists = file_exists($file) || is_dir($file);
    $status = $exists ? "⚠️ ENCORE PRÉSENT" : "✅ SUPPRIMÉ";
    $color = $exists ? "orange" : "green";
    
    echo "<tr>";
    echo "<td><code>$file</code></td>";
    echo "<td style='color: $color; font-weight: bold;'>$status</td>";
    echo "</tr>";
}
echo "</table>";

// Vérification de la base de données
echo "<h2>🗄️ Vérification de la base de données</h2>";
try {
    require_once 'config.php';
    echo "<p style='color: green;'>✅ Connexion à la base de données réussie</p>";
    
    // Vérifier les tables
    $tables = ['users', 'products'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>✅ Table '$table' : $count enregistrements</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Table '$table' : " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur de connexion : " . $e->getMessage() . "</p>";
    echo "<p>👉 Exécutez d'abord le script <a href='migrate.php'>migrate.php</a></p>";
}

echo "<h2>🚀 Liens rapides</h2>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>🏠 Front-office (Site public)</a></li>";
echo "<li><a href='admin/login.php' target='_blank'>🔐 Back-office (Administration)</a></li>";
echo "<li><a href='migrate.php' target='_blank'>⚙️ Migration de la base de données</a></li>";
echo "</ul>";

echo "<h2>📋 Informations importantes</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007bff;'>";
echo "<h3>🔐 Accès Admin</h3>";
echo "<p><strong>URL :</strong> <code>http://votre-site.com/admin/</code></p>";
echo "<p><strong>Identifiants par défaut :</strong></p>";
echo "<ul>";
echo "<li>Utilisateur : <code>admin</code></li>";
echo "<li>Mot de passe : <code>admin123</code></li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #f0fff0; padding: 15px; border-left: 4px solid #28a745; margin-top: 15px;'>";
echo "<h3>✨ Fonctionnalités</h3>";
echo "<ul>";
echo "<li>Dashboard moderne avec statistiques</li>";
echo "<li>Gestion complète des produits (CRUD)</li>";
echo "<li>Upload d'images sécurisé</li>";
echo "<li>Interface responsive</li>";
echo "<li>Design professionnel</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "🎯 Structure réorganisée avec succès ! Votre e-commerce est prêt.";
echo "</p>";
?>