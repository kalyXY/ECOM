<?php
/**
 * Script de diagnostic pour l'intégration Lygos
 * Aide à identifier les problèmes de configuration et de fonctionnement
 */

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/lygos.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic Lygos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        .code { background: #f4f4f4; padding: 10px; border-left: 4px solid #ccc; margin: 10px 0; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>🔧 Diagnostic de l'intégration Lygos</h1>

    <div class="section">
        <h2>1. Configuration</h2>
        <?php
        echo "<p><strong>Clé API Lygos:</strong> ";
        if (!empty($LYGOS_API_KEY)) {
            echo '<span class="success">✅ Définie (' . substr($LYGOS_API_KEY, 0, 15) . '...)</span>';
        } else {
            echo '<span class="error">❌ MANQUANTE</span>';
        }
        echo "</p>";
        
        echo "<p><strong>URL de base API:</strong> <span class='info'>" . htmlspecialchars($LYGOS_API_BASE) . "</span></p>";
        
        echo "<p><strong>Fonction HTTP:</strong> ";
        if (function_exists('http_post_json')) {
            echo '<span class="success">✅ Disponible</span>';
        } else {
            echo '<span class="error">❌ Non définie</span>';
        }
        echo "</p>";
        
        echo "<p><strong>cURL:</strong> ";
        if (extension_loaded('curl')) {
            echo '<span class="success">✅ Extension chargée</span>';
        } else {
            echo '<span class="error">❌ Extension manquante</span>';
        }
        echo "</p>";
        ?>
    </div>

    <div class="section">
        <h2>2. Test de connectivité</h2>
        <?php
        if (!empty($LYGOS_API_KEY) && function_exists('http_post_json')) {
            $testPayload = [
                'amount' => 100, // 1.00 EUR en centimes
                'shop_name' => 'Test Shop',
                'message' => 'Test de diagnostic',
                'success_url' => app_url('success.php?order_id=test'),
                'failure_url' => app_url('fail.php?order_id=test'),
                'order_id' => 'diagnostic_' . time(),
            ];
            
            echo "<p><strong>Payload de test:</strong></p>";
            echo "<div class='code'>" . htmlspecialchars(json_encode($testPayload, JSON_PRETTY_PRINT)) . "</div>";
            
            $headers = ['api-key: ' . $LYGOS_API_KEY];
            $response = http_post_json($LYGOS_API_BASE . '/gateway', $headers, $testPayload);
            
            echo "<p><strong>Réponse de l'API:</strong></p>";
            echo "<div class='code'>";
            echo "Code de statut: " . $response['status'] . "<br>";
            echo "Erreur cURL: " . ($response['error'] ?: 'Aucune') . "<br>";
            echo "Corps de la réponse: " . htmlspecialchars($response['body']) . "<br>";
            echo "</div>";
            
            if ($response['error']) {
                echo '<p class="error">❌ Erreur de connexion: ' . htmlspecialchars($response['error']) . '</p>';
                echo '<p class="info">💡 Vérifiez votre connexion internet et l\'URL de l\'API.</p>';
            } else {
                $data = json_decode($response['body'], true);
                
                if ($response['status'] >= 200 && $response['status'] < 300) {
                    if (!empty($data['payment_url'])) {
                        echo '<p class="success">✅ Test réussi! URL de paiement générée.</p>';
                        echo '<p class="info">URL: ' . htmlspecialchars($data['payment_url']) . '</p>';
                    } else {
                        echo '<p class="warning">⚠️ Réponse OK mais pas d\'URL de paiement.</p>';
                    }
                } else {
                    echo '<p class="error">❌ Erreur API (Code ' . $response['status'] . ')</p>';
                    if ($data && isset($data['error'])) {
                        echo '<p class="error">Message: ' . htmlspecialchars($data['error']) . '</p>';
                    }
                    
                    // Suggestions
                    switch ($response['status']) {
                        case 401:
                            echo '<p class="info">💡 Vérifiez votre clé API Lygos.</p>';
                            break;
                        case 400:
                            echo '<p class="info">💡 Vérifiez le format des données envoyées.</p>';
                            break;
                        case 404:
                            echo '<p class="info">💡 Vérifiez l\'URL de l\'API.</p>';
                            break;
                        case 500:
                            echo '<p class="info">💡 Problème côté serveur Lygos, réessayez plus tard.</p>';
                            break;
                    }
                }
            }
        } else {
            echo '<p class="error">❌ Impossible de tester: configuration manquante.</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>3. Vérification des fichiers</h2>
        <?php
        $files = [
            'config/lygos.php' => 'Configuration Lygos',
            'pay_with_lygos.php' => 'Page de paiement',
            'lygos_return.php' => 'Page de retour',
            'api/lygos_create_gateway.php' => 'API Gateway',
            'checkout.php' => 'Page de commande'
        ];
        
        foreach ($files as $file => $description) {
            echo "<p><strong>$description:</strong> ";
            if (file_exists(__DIR__ . '/' . $file)) {
                echo '<span class="success">✅ Présent</span>';
            } else {
                echo '<span class="error">❌ Manquant</span>';
            }
            echo "</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>4. Logs récents</h2>
        <?php
        // Chercher les logs d'erreur PHP
        $logFiles = [
            '/var/log/apache2/error.log',
            '/var/log/nginx/error.log',
            '/tmp/php_errors.log',
            ini_get('error_log')
        ];
        
        echo "<p><strong>Recherche des logs Lygos dans les fichiers d'erreur...</strong></p>";
        
        $foundLogs = false;
        foreach ($logFiles as $logFile) {
            if ($logFile && file_exists($logFile) && is_readable($logFile)) {
                $command = "tail -50 " . escapeshellarg($logFile) . " | grep -i lygos";
                $output = shell_exec($command);
                
                if ($output) {
                    $foundLogs = true;
                    echo "<p><strong>Logs trouvés dans $logFile:</strong></p>";
                    echo "<div class='code'>" . htmlspecialchars($output) . "</div>";
                }
            }
        }
        
        if (!$foundLogs) {
            echo '<p class="info">ℹ️ Aucun log Lygos récent trouvé.</p>';
            echo '<p class="info">Les logs seront générés lors de la prochaine tentative de paiement.</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>5. Instructions de dépannage</h2>
        <ol>
            <li><strong>Si la clé API est manquante:</strong>
                <ul>
                    <li>Vérifiez le fichier <code>config/local.php</code></li>
                    <li>Assurez-vous que la clé commence par <code>lygosapp-</code></li>
                </ul>
            </li>
            <li><strong>Si l'API retourne une erreur 401:</strong>
                <ul>
                    <li>Votre clé API est invalide ou expirée</li>
                    <li>Contactez le support Lygos</li>
                </ul>
            </li>
            <li><strong>Si l'API retourne une erreur 400:</strong>
                <ul>
                    <li>Vérifiez le format des données (montant en centimes, URLs valides)</li>
                    <li>Assurez-vous que les URLs de retour sont accessibles</li>
                </ul>
            </li>
            <li><strong>Pour tester manuellement:</strong>
                <ul>
                    <li>Utilisez le script <code>test_lygos_api.php</code> en ligne de commande</li>
                    <li>Vérifiez les logs d'erreur après chaque tentative</li>
                </ul>
            </li>
        </ol>
    </div>

    <div class="section">
        <h2>6. Actions recommandées</h2>
        <ul>
            <?php if (empty($LYGOS_API_KEY)): ?>
            <li class="error">❌ Définir la clé API Lygos dans <code>config/local.php</code></li>
            <?php endif; ?>
            
            <?php if (!extension_loaded('curl')): ?>
            <li class="error">❌ Installer l'extension PHP cURL</li>
            <?php endif; ?>
            
            <li class="info">ℹ️ Tester avec le script <code>test_lygos_api.php</code></li>
            <li class="info">ℹ️ Vérifier les logs d'erreur après chaque test</li>
            <li class="info">ℹ️ S'assurer que les URLs de retour sont accessibles depuis l'extérieur</li>
        </ul>
    </div>
</body>
</html>