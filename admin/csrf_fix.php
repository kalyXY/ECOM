<?php
/**
 * Correction du système CSRF
 * Ce fichier corrige les problèmes de token CSRF
 */

// Forcer le démarrage de session proprement
if (session_status() === PHP_SESSION_NONE) {
    // Configuration session sécurisée mais compatible
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 3600);
    
    // Ne pas forcer HTTPS en développement
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    session_start();
}

/**
 * Système CSRF corrigé et simplifié
 */
class FixedCSRF {
    
    /**
     * Générer un token CSRF
     */
    public static function generateToken() {
        // Toujours générer un nouveau token pour éviter les problèmes de cache
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Vérifier un token CSRF
     */
    public static function verifyToken($token) {
        // Vérifications de base
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Vérifier l'expiration (1 heure max)
        if (isset($_SESSION['csrf_token_time'])) {
            if (time() - $_SESSION['csrf_token_time'] > 3600) {
                unset($_SESSION['csrf_token']);
                unset($_SESSION['csrf_token_time']);
                return false;
            }
        }
        
        // Comparaison sécurisée
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Obtenir le token actuel (sans en générer un nouveau)
     */
    public static function getToken() {
        return $_SESSION['csrf_token'] ?? self::generateToken();
    }
}

// Test du système corrigé
echo "=== TEST DU SYSTÈME CSRF CORRIGÉ ===\n\n";

// Générer un token
$token = FixedCSRF::generateToken();
echo "✅ Token généré: " . substr($token, 0, 16) . "...\n";

// Tester la vérification
$isValid = FixedCSRF::verifyToken($token);
echo "✅ Vérification: " . ($isValid ? "VALIDE" : "INVALIDE") . "\n";

// Test avec token invalide
$isInvalid = FixedCSRF::verifyToken("invalid_token");
echo "✅ Test invalide: " . ($isInvalid ? "PROBLÈME" : "OK") . "\n\n";

echo "Le système CSRF corrigé est prêt.\n";
echo "Intégrez ce code dans votre application.\n\n";

// Créer les fonctions globales corrigées
if (!function_exists('generateCSRFTokenFixed')) {
    function generateCSRFTokenFixed() {
        return FixedCSRF::generateToken();
    }
}

if (!function_exists('verifyCSRFTokenFixed')) {
    function verifyCSRFTokenFixed($token) {
        return FixedCSRF::verifyToken($token);
    }
}

echo "Fonctions globales créées:\n";
echo "- generateCSRFTokenFixed()\n";
echo "- verifyCSRFTokenFixed()\n";

?>