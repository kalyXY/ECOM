<?php
// Test simple de l'API notifications
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test API Notifications</h2>";

// Simuler une session admin
session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['admin_username'] = 'admin';

try {
    // Capturer la sortie pour éviter les headers SSE
    ob_start();
    
    // Inclure le fichier API avec un timeout court
    $timeout = 2; // 2 secondes seulement pour le test
    $_GET['timeout'] = $timeout;
    
    include 'api/notifications.php';
    
    $output = ob_get_clean();
    
    echo "<p>✅ API notifications chargée sans erreur fatale</p>";
    echo "<p>Taille de la sortie: " . strlen($output) . " caractères</p>";
    
    if (strpos($output, 'data:') !== false) {
        echo "<p>✅ Format SSE détecté</p>";
    }
    
    if (strpos($output, 'ob_flush') === false) {
        echo "<p>✅ Aucune erreur ob_flush dans la sortie</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>❌ Erreur fatale: " . $e->getMessage() . "</p>";
}
?>