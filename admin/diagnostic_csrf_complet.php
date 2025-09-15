<?php
/**
 * Diagnostic complet du problème CSRF
 * Ce script va identifier exactement où est le problème
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 DIAGNOSTIC CSRF COMPLET</h1>\n";
echo "<style>body{font-family:monospace; white-space:pre-line;}</style>";

// 1. Vérifier l'état des sessions AVANT tout
echo "\n=== 1. ÉTAT DES SESSIONS AVANT CONFIGURATION ===\n";
echo "Session status: " . session_status() . " (1=disabled, 2=none, 3=active)\n";
echo "Session ID: " . (session_id() ?: 'AUCUN') . "\n";

// 2. Forcer le démarrage de session
if (session_status() === PHP_SESSION_NONE) {
    // Configuration session minimale
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
    echo "✅ Session démarrée manuellement\n";
} else {
    echo "Session déjà active\n";
}

echo "Session ID après: " . session_id() . "\n";
echo "Session save path: " . session_save_path() . "\n";

// 3. Vérifier les permissions du dossier de session
$sessionPath = session_save_path();
if (empty($sessionPath)) {
    $sessionPath = sys_get_temp_dir();
}
echo "Dossier session: $sessionPath\n";
echo "Dossier session existe: " . (is_dir($sessionPath) ? "✅ OUI" : "❌ NON") . "\n";
echo "Dossier session writable: " . (is_writable($sessionPath) ? "✅ OUI" : "❌ NON") . "\n";

// 4. Tester l'écriture en session
echo "\n=== 2. TEST ÉCRITURE SESSION ===\n";
$_SESSION['test_csrf'] = 'test_value_' . time();
echo "Valeur écrite: " . $_SESSION['test_csrf'] . "\n";

// Régénérer l'ID et vérifier
session_regenerate_id(false);
echo "Session ID après regenerate: " . session_id() . "\n";
echo "Valeur après regenerate: " . ($_SESSION['test_csrf'] ?? 'PERDUE!') . "\n";

// 5. Définir les fonctions CSRF avec debug
echo "\n=== 3. DÉFINITION FONCTIONS CSRF AVEC DEBUG ===\n";

function generateCSRFTokenDebug() {
    echo "🔧 generateCSRFTokenDebug appelée\n";
    echo "Session status dans fonction: " . session_status() . "\n";
    echo "Session ID dans fonction: " . session_id() . "\n";
    
    if (session_status() !== PHP_SESSION_ACTIVE) {
        echo "❌ Session non active dans generateCSRFTokenDebug!\n";
        return false;
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    
    echo "Token généré: " . substr($token, 0, 16) . "...\n";
    echo "Token stocké en session: " . substr($_SESSION['csrf_token'], 0, 16) . "...\n";
    echo "Timestamp: " . $_SESSION['csrf_token_time'] . "\n";
    
    return $token;
}

function verifyCSRFTokenDebug($token) {
    echo "🔍 verifyCSRFTokenDebug appelée\n";
    echo "Token reçu: " . ($token ? substr($token, 0, 16) . "..." : 'VIDE') . "\n";
    echo "Session status dans vérification: " . session_status() . "\n";
    echo "Session ID dans vérification: " . session_id() . "\n";
    
    if (empty($token)) {
        echo "❌ Token vide\n";
        return false;
    }
    
    if (empty($_SESSION['csrf_token'])) {
        echo "❌ Pas de token en session\n";
        echo "Contenu session: " . print_r($_SESSION, true) . "\n";
        return false;
    }
    
    echo "Token session: " . substr($_SESSION['csrf_token'], 0, 16) . "...\n";
    
    // Vérifier l'expiration
    if (isset($_SESSION['csrf_token_time'])) {
        $age = time() - $_SESSION['csrf_token_time'];
        echo "Âge du token: $age secondes\n";
        if ($age > 3600) {
            echo "❌ Token expiré\n";
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
    }
    
    // Comparaison
    $isValid = hash_equals($_SESSION['csrf_token'], $token);
    echo "Comparaison hash_equals: " . ($isValid ? "✅ VALIDE" : "❌ INVALIDE") . "\n";
    
    if (!$isValid) {
        echo "Debug comparaison:\n";
        echo "- Longueur token reçu: " . strlen($token) . "\n";
        echo "- Longueur token session: " . strlen($_SESSION['csrf_token']) . "\n";
        echo "- Égalité stricte: " . ($token === $_SESSION['csrf_token'] ? "OUI" : "NON") . "\n";
        echo "- Premiers 10 chars token reçu: " . substr($token, 0, 10) . "\n";
        echo "- Premiers 10 chars token session: " . substr($_SESSION['csrf_token'], 0, 10) . "\n";
    }
    
    return $isValid;
}

// 6. Test de génération
echo "\n=== 4. TEST GÉNÉRATION TOKEN ===\n";
$token1 = generateCSRFTokenDebug();
echo "Premier token: " . ($token1 ? "✅ GÉNÉRÉ" : "❌ ÉCHEC") . "\n";

// 7. Test de vérification immédiate
echo "\n=== 5. TEST VÉRIFICATION IMMÉDIATE ===\n";
if ($token1) {
    $valid1 = verifyCSRFTokenDebug($token1);
    echo "Vérification immédiate: " . ($valid1 ? "✅ VALIDE" : "❌ INVALIDE") . "\n";
}

// 8. Simuler une requête POST
echo "\n=== 6. SIMULATION REQUÊTE POST ===\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "🚀 REQUÊTE POST REÇUE\n";
    echo "Token POST: " . ($_POST['csrf_token'] ?? 'AUCUN') . "\n";
    echo "Token SESSION: " . ($_SESSION['csrf_token'] ?? 'AUCUN') . "\n";
    
    if (isset($_POST['csrf_token'])) {
        $postValid = verifyCSRFTokenDebug($_POST['csrf_token']);
        echo "Résultat vérification POST: " . ($postValid ? "✅ VALIDE" : "❌ INVALIDE") . "\n";
    }
} else {
    echo "Aucune requête POST - affichage du formulaire de test\n";
}

// 9. Vérifier les headers et cookies
echo "\n=== 7. INFORMATIONS HEADERS/COOKIES ===\n";
echo "Headers déjà envoyés: " . (headers_sent() ? "✅ OUI" : "❌ NON") . "\n";
if (headers_sent($file, $line)) {
    echo "Headers envoyés depuis: $file ligne $line\n";
}

echo "Cookies session:\n";
$cookies = $_COOKIE;
foreach ($cookies as $name => $value) {
    if (strpos($name, session_name()) !== false || strpos($name, 'PHPSESSID') !== false) {
        echo "- $name: " . substr($value, 0, 20) . "...\n";
    }
}

// 10. Informations PHP
echo "\n=== 8. CONFIGURATION PHP ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Session module: " . (extension_loaded('session') ? "✅ CHARGÉ" : "❌ MANQUANT") . "\n";
echo "Session auto start: " . ini_get('session.auto_start') . "\n";
echo "Session cookie lifetime: " . ini_get('session.cookie_lifetime') . "\n";
echo "Session gc maxlifetime: " . ini_get('session.gc_maxlifetime') . "\n";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic CSRF Complet</title>
    <style>
        body { font-family: monospace; max-width: 1000px; margin: 20px auto; padding: 20px; }
        .test-form { background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f8f8f8; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>

<div class="test-form">
    <h2>🧪 TEST FORMULAIRE CSRF</h2>
    <form method="POST">
        <?php if ($token1): ?>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token1); ?>">
            <p><strong>Token généré:</strong> <?php echo substr($token1, 0, 20); ?>...</p>
        <?php else: ?>
            <p class="error">❌ Impossible de générer un token!</p>
        <?php endif; ?>
        
        <div style="margin: 15px 0;">
            <label>Test data:</label>
            <input type="text" name="test_data" value="Test CSRF" style="padding: 5px;">
        </div>
        
        <button type="submit" style="padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer;">
            Tester le CSRF
        </button>
    </form>
</div>

<div class="test-form">
    <h2>📋 INSTRUCTIONS DE DÉBOGAGE</h2>
    <ol>
        <li>Regardez les informations ci-dessus pour identifier les problèmes</li>
        <li>Cliquez sur "Tester le CSRF" pour voir si ça fonctionne</li>
        <li>Si ça ne fonctionne pas, vérifiez:
            <ul>
                <li>Les permissions du dossier session</li>
                <li>La configuration PHP des sessions</li>
                <li>Les headers HTTP</li>
            </ul>
        </li>
    </ol>
</div>

<div class="test-form">
    <h2>🔧 SOLUTIONS POSSIBLES</h2>
    <pre>
1. Si le dossier session n'est pas writable:
   chmod 777 <?php echo $sessionPath; ?>

2. Si les sessions ne fonctionnent pas:
   Vérifiez php.ini pour session.save_path

3. Si les headers sont déjà envoyés:
   Vérifiez qu'il n'y a pas d'espaces avant &lt;?php

4. Si le problème persiste:
   Utilisez session_write_close() avant les redirections
    </pre>
</div>

</body>
</html>