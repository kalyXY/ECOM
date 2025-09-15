<?php
/**
 * Diagnostic CSRF - Identifier le problème avec les tokens
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 DIAGNOSTIC CSRF TOKEN</h1>\n\n";

// 1. Vérifier l'état des sessions
echo "<h2>1. État des sessions</h2>\n";
echo "Status session: " . session_status() . "\n";
echo "Session démarrée: " . (session_status() === PHP_SESSION_ACTIVE ? "✅ OUI" : "❌ NON") . "\n";

// Démarrer la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "Session démarrée manuellement\n";
}

echo "ID session: " . session_id() . "\n";
echo "Nom session: " . session_name() . "\n\n";

// 2. Tester la génération de token
echo "<h2>2. Test génération token</h2>\n";

// Inclure les fonctions nécessaires
try {
    require_once '../config/bootstrap.php';
    echo "✅ Configuration chargée\n";
} catch (Exception $e) {
    echo "❌ Erreur configuration: " . $e->getMessage() . "\n";
    
    // Fallback - définir les fonctions manuellement
    if (!function_exists('generateCSRFToken')) {
        function generateCSRFToken() {
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }
    }
    
    if (!function_exists('verifyCSRFToken')) {
        function verifyCSRFToken($token) {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }
    }
    
    echo "✅ Fonctions CSRF définies manuellement\n";
}

// Générer un token
$token1 = generateCSRFToken();
echo "Token généré: " . substr($token1, 0, 20) . "...\n";

// Générer à nouveau (devrait être le même)
$token2 = generateCSRFToken();
echo "Token regénéré: " . substr($token2, 0, 20) . "...\n";
echo "Tokens identiques: " . ($token1 === $token2 ? "✅ OUI" : "❌ NON") . "\n\n";

// 3. Tester la vérification
echo "<h2>3. Test vérification</h2>\n";
$valid = verifyCSRFToken($token1);
echo "Vérification token valide: " . ($valid ? "✅ OUI" : "❌ NON") . "\n";

$invalid = verifyCSRFToken("token_invalide");
echo "Vérification token invalide: " . ($invalid ? "❌ PROBLÈME" : "✅ OK") . "\n\n";

// 4. Vérifier le contenu de $_SESSION
echo "<h2>4. Contenu session</h2>\n";
echo "Contenu \$_SESSION:\n";
if (empty($_SESSION)) {
    echo "❌ Session vide!\n";
} else {
    foreach ($_SESSION as $key => $value) {
        if ($key === 'csrf_token') {
            echo "- $key: " . substr($value, 0, 20) . "...\n";
        } else {
            echo "- $key: " . (is_string($value) ? $value : gettype($value)) . "\n";
        }
    }
}
echo "\n";

// 5. Simuler une soumission de formulaire
echo "<h2>5. Simulation formulaire</h2>\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "Formulaire soumis!\n";
    echo "Token reçu: " . ($_POST['csrf_token'] ?? 'AUCUN') . "\n";
    echo "Token session: " . ($_SESSION['csrf_token'] ?? 'AUCUN') . "\n";
    
    if (isset($_POST['csrf_token']) && isset($_SESSION['csrf_token'])) {
        $isValid = verifyCSRFToken($_POST['csrf_token']);
        echo "Validation: " . ($isValid ? "✅ VALIDE" : "❌ INVALIDE") . "\n";
        
        // Debug détaillé
        echo "Longueur token POST: " . strlen($_POST['csrf_token']) . "\n";
        echo "Longueur token SESSION: " . strlen($_SESSION['csrf_token']) . "\n";
        echo "Comparaison stricte: " . ($_POST['csrf_token'] === $_SESSION['csrf_token'] ? "✅ IDENTIQUE" : "❌ DIFFÉRENT") . "\n";
    }
} else {
    echo "Aucun formulaire soumis\n";
}

// 6. Informations sur l'environnement
echo "<h2>6. Environnement</h2>\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Session save path: " . session_save_path() . "\n";
echo "Session cookie params:\n";
$params = session_get_cookie_params();
foreach ($params as $key => $value) {
    echo "- $key: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Test CSRF</title>
    <style>
        body { font-family: monospace; white-space: pre-line; max-width: 800px; margin: 20px; }
        h1, h2 { color: #333; }
        form { background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 5px; }
        input, button { margin: 5px; padding: 8px; }
        button { background: #007cba; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

<h2>🧪 Test de formulaire</h2>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <label>Test: <input type="text" name="test" value="valeur test"></label><br>
    <button type="submit">Tester le formulaire</button>
</form>

<h2>📋 Instructions</h2>
1. Regardez les informations ci-dessus
2. Cliquez sur "Tester le formulaire" 
3. Vérifiez si la validation CSRF fonctionne
4. Si "INVALIDE", il y a un problème de session

</body>
</html>