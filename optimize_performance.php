<?php
// Script d'optimisation des performances
echo "<h2>Optimisation des Performances StyleHub</h2>";

// 1. Vérifier les requêtes de base de données
echo "<h3>1. Analyse des Requêtes Base de Données</h3>";

try {
    require_once 'config.php';
    
    // Test des requêtes principales
    $queries = [
        "SELECT COUNT(*) FROM products WHERE status = 'active'" => "Comptage produits actifs",
        "SELECT * FROM products WHERE status = 'active' LIMIT 10" => "Récupération produits",
        "SELECT * FROM categories WHERE status = 'active'" => "Récupération catégories"
    ];
    
    foreach ($queries as $query => $description) {
        $start = microtime(true);
        try {
            $stmt = $pdo->query($query);
            $result = $stmt->fetchAll();
            $time = (microtime(true) - $start) * 1000;
            echo "<p>✅ $description: " . round($time, 2) . " ms (" . count($result) . " résultats)</p>";
        } catch (Exception $e) {
            echo "<p>❌ $description: Erreur - " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erreur de connexion DB: " . $e->getMessage() . "</p>";
}

// 2. Analyser les fichiers CSS/JS
echo "<h3>2. Analyse des Assets</h3>";

$assets = [
    'assets/css/style.css' => 'CSS principal',
    'assets/js/script.js' => 'JavaScript principal'
];

foreach ($assets as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        $content = file_get_contents($file);
        $lines = substr_count($content, "\n");
        
        echo "<p>✅ $description: " . round($size / 1024, 1) . " KB ($lines lignes)</p>";
        
        // Suggestions d'optimisation
        if ($size > 50000) { // Plus de 50KB
            echo "<p>⚠️ Fichier volumineux - Considérer la minification</p>";
        }
    } else {
        echo "<p>❌ $description: Fichier manquant</p>";
    }
}

// 3. Vérifier les images
echo "<h3>3. Analyse des Images</h3>";

$uploadDir = 'uploads/';
if (is_dir($uploadDir)) {
    $images = glob($uploadDir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    $totalSize = 0;
    $largeImages = [];
    
    foreach ($images as $image) {
        $size = filesize($image);
        $totalSize += $size;
        
        if ($size > 500000) { // Plus de 500KB
            $largeImages[] = basename($image) . ' (' . round($size / 1024, 1) . ' KB)';
        }
    }
    
    echo "<p>✅ " . count($images) . " images trouvées</p>";
    echo "<p>📊 Taille totale: " . round($totalSize / 1024 / 1024, 1) . " MB</p>";
    
    if (!empty($largeImages)) {
        echo "<p>⚠️ Images volumineuses à optimiser:</p>";
        foreach ($largeImages as $img) {
            echo "<p>  - $img</p>";
        }
    }
} else {
    echo "<p>❌ Dossier uploads/ non trouvé</p>";
}

// 4. Recommandations d'optimisation
echo "<h3>4. Recommandations d'Optimisation</h3>";

$recommendations = [
    "✅ Activer la compression GZIP dans .htaccess",
    "✅ Utiliser un CDN pour les assets statiques",
    "✅ Optimiser les images (WebP, compression)",
    "✅ Minifier CSS et JavaScript",
    "✅ Utiliser le cache navigateur",
    "✅ Optimiser les requêtes base de données",
    "✅ Utiliser un cache Redis/Memcached",
    "✅ Lazy loading pour les images"
];

foreach ($recommendations as $rec) {
    echo "<p>$rec</p>";
}

// 5. Créer un fichier .htaccess optimisé
echo "<h3>5. Optimisation .htaccess</h3>";

$htaccessOptimizations = '
# Optimisations de performance ajoutées
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache des fichiers statiques
<FilesMatch "\.(css|js|png|jpg|jpeg|gif|webp|svg|ico)$">
    Header set Cache-Control "max-age=31536000, public"
</FilesMatch>
';

echo "<p>✅ Optimisations .htaccess générées</p>";
echo "<details><summary>Voir le code .htaccess</summary><pre>" . htmlspecialchars($htaccessOptimizations) . "</pre></details>";

// 6. Test de vitesse final
echo "<h3>6. Test de Vitesse Final</h3>";

$start = microtime(true);
ob_start();
include 'index.php';
$output = ob_get_clean();
$loadTime = (microtime(true) - $start) * 1000;

echo "<p>🚀 Page d'accueil chargée en: " . round($loadTime, 2) . " ms</p>";

if ($loadTime < 100) {
    echo "<p>✅ Performance excellente (&lt; 100ms)</p>";
} elseif ($loadTime < 500) {
    echo "<p>⚠️ Performance correcte (&lt; 500ms)</p>";
} else {
    echo "<p>❌ Performance à améliorer (&gt; 500ms)</p>";
}

echo "<p><strong>Analyse terminée !</strong></p>";
?>