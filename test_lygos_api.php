<?php
/**
 * Script de test pour l'API Lygos
 * Permet de vérifier si l'intégration fonctionne correctement
 */

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/lygos.php';

echo "=== TEST DE L'API LYGOS ===\n\n";

// 1. Vérifier la configuration
echo "1. Configuration Lygos:\n";
echo "   - Clé API: " . (!empty($LYGOS_API_KEY) ? 'Définie (' . substr($LYGOS_API_KEY, 0, 15) . '...)' : 'MANQUANTE') . "\n";
echo "   - URL de base: " . $LYGOS_API_BASE . "\n\n";

if (empty($LYGOS_API_KEY)) {
    echo "❌ ERREUR: Clé API Lygos manquante!\n";
    echo "Vérifiez le fichier config/local.php\n";
    exit(1);
}

// 2. Test de la fonction http_post_json
echo "2. Test de la fonction HTTP:\n";
if (!function_exists('http_post_json')) {
    echo "❌ ERREUR: Fonction http_post_json non définie!\n";
    exit(1);
}

// 3. Test de connexion à l'API Lygos
echo "   - Test de connexion à l'API Lygos...\n";

// Données de test
$testPayload = [
    'amount' => 1000, // 10.00 EUR en centimes
    'shop_name' => 'Test Shop',
    'message' => 'Test de paiement',
    'success_url' => app_url('lygos_return.php?status=success&order_id=test'),
    'failure_url' => app_url('lygos_return.php?status=failure&order_id=test'),
    'order_id' => 'test_' . time(),
];

echo "   - Payload de test: " . json_encode($testPayload, JSON_PRETTY_PRINT) . "\n";

// Appel à l'API
$headers = ['api-key: ' . $LYGOS_API_KEY];
$response = http_post_json($LYGOS_API_BASE . '/gateway', $headers, $testPayload);

echo "\n3. Réponse de l'API:\n";
echo "   - Code de statut: " . $response['status'] . "\n";
echo "   - Erreur cURL: " . ($response['error'] ?: 'Aucune') . "\n";
echo "   - Corps de la réponse: " . $response['body'] . "\n";

// Analyse de la réponse
if ($response['error']) {
    echo "\n❌ ERREUR DE CONNEXION: " . $response['error'] . "\n";
    echo "Vérifiez votre connexion internet et l'URL de l'API.\n";
} else {
    $data = json_decode($response['body'], true);
    
    if ($response['status'] >= 200 && $response['status'] < 300) {
        echo "\n✅ CONNEXION RÉUSSIE!\n";
        if (!empty($data['payment_url'])) {
            echo "   - URL de paiement générée: " . $data['payment_url'] . "\n";
            echo "\n🎉 L'intégration Lygos fonctionne correctement!\n";
        } else {
            echo "\n⚠️  ATTENTION: Pas d'URL de paiement dans la réponse\n";
            echo "   - Réponse complète: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "\n❌ ERREUR API (Code " . $response['status'] . "):\n";
        if ($data && isset($data['error'])) {
            echo "   - Message d'erreur: " . $data['error'] . "\n";
        }
        echo "   - Réponse complète: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        
        // Suggestions basées sur le code d'erreur
        switch ($response['status']) {
            case 401:
                echo "\n💡 SUGGESTION: Vérifiez votre clé API Lygos\n";
                break;
            case 400:
                echo "\n💡 SUGGESTION: Vérifiez le format des données envoyées\n";
                break;
            case 404:
                echo "\n💡 SUGGESTION: Vérifiez l'URL de l'API\n";
                break;
            case 500:
                echo "\n💡 SUGGESTION: Problème côté serveur Lygos, réessayez plus tard\n";
                break;
        }
    }
}

echo "\n=== FIN DU TEST ===\n";
?>