<?php
// Test de performance pour identifier les lenteurs
$startTime = microtime(true);
$memoryStart = memory_get_usage();

echo "<h2>Analyse de Performance</h2>";
echo "<p>Début du test : " . date('H:i:s') . "</p>";

// Test 1: Chargement de la configuration
$configStart = microtime(true);
try {
    require_once 'config.php';
    $configTime = (microtime(true) - $configStart) * 1000;
    echo "<p>✅ Configuration chargée en " . round($configTime, 2) . " ms</p>";
} catch (Exception $e) {
    echo "<p>❌ Erreur config: " . $e->getMessage() . "</p>";
}

// Test 2: Connexion à la base de données
$dbStart = microtime(true);
try {
    $stmt = $pdo->query("SELECT 1");
    $dbTime = (microtime(true) - $dbStart) * 1000;
    echo "<p>✅ Connexion DB en " . round($dbTime, 2) . " ms</p>";
} catch (Exception $e) {
    echo "<p>❌ Erreur DB: " . $e->getMessage() . "</p>";
}

// Test 3: Requête produits
$productsStart = microtime(true);
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $productCount = $stmt->fetchColumn();
    $productsTime = (microtime(true) - $productsStart) * 1000;
    echo "<p>✅ Requête produits ($productCount produits) en " . round($productsTime, 2) . " ms</p>";
} catch (Exception $e) {
    echo "<p>❌ Erreur produits: " . $e->getMessage() . "</p>";
}

// Test 4: Chargement du header
$headerStart = microtime(true);
try {
    ob_start();
    include 'includes/header.php';
    $headerOutput = ob_get_clean();
    $headerTime = (microtime(true) - $headerStart) * 1000;
    echo "<p>✅ Header chargé en " . round($headerTime, 2) . " ms (" . strlen($headerOutput) . " caractères)</p>";
} catch (Exception $e) {
    echo "<p>❌ Erreur header: " . $e->getMessage() . "</p>";
}

// Test 5: Chargement d'une page complète (index)
$pageStart = microtime(true);
try {
    ob_start();
    include 'index.php';
    $pageOutput = ob_get_clean();
    $pageTime = (microtime(true) - $pageStart) * 1000;
    echo "<p>✅ Page index chargée en " . round($pageTime, 2) . " ms (" . strlen($pageOutput) . " caractères)</p>";
} catch (Exception $e) {
    echo "<p>❌ Erreur page: " . $e->getMessage() . "</p>";
}

// Statistiques finales
$totalTime = (microtime(true) - $startTime) * 1000;
$memoryUsed = memory_get_usage() - $memoryStart;
$memoryPeak = memory_get_peak_usage();

echo "<hr>";
echo "<h3>Résumé Performance</h3>";
echo "<p><strong>Temps total:</strong> " . round($totalTime, 2) . " ms</p>";
echo "<p><strong>Mémoire utilisée:</strong> " . round($memoryUsed / 1024 / 1024, 2) . " MB</p>";
echo "<p><strong>Pic mémoire:</strong> " . round($memoryPeak / 1024 / 1024, 2) . " MB</p>";

// Analyse des extensions PHP
echo "<h3>Extensions PHP</h3>";
$extensions = ['pdo', 'pdo_mysql', 'session', 'json', 'mbstring', 'curl'];
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "<p>$status $ext</p>";
}

// Analyse de la configuration PHP
echo "<h3>Configuration PHP</h3>";
echo "<p><strong>Version PHP:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Memory limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>Max execution time:</strong> " . ini_get('max_execution_time') . "s</p>";
echo "<p><strong>Upload max filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";

// Test des fichiers statiques
echo "<h3>Fichiers Statiques</h3>";
$staticFiles = [
    'assets/css/style.css',
    'assets/js/script.js',
    'includes/config.php',
    'includes/header.php',
    'includes/footer.php'
];

foreach ($staticFiles as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<p>✅ $file (" . round($size / 1024, 1) . " KB)</p>";
    } else {
        echo "<p>❌ $file (manquant)</p>";
    }
}

echo "<p><strong>Test terminé à:</strong> " . date('H:i:s') . "</p>";
?>