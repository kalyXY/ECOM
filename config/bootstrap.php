<?php
/**
 * Bootstrap principal de l'application
 * StyleHub E-Commerce Platform
 */

// Démarrer la capture d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 0); // Ne pas afficher les erreurs en production

// Charger la configuration de sécurité
require_once __DIR__ . '/security.php';

// Charger la configuration de l'application
require_once __DIR__ . '/app.php';

// Charger la connexion à la base de données
$pdo = require_once __DIR__ . '/database.php';

/**
 * Classe de gestion des utilisateurs
 */
class Auth {
    private static $pdo;
    
    public static function init($pdo) {
        self::$pdo = $pdo;
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     */
    public static function check() {
        return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
    }
    
    /**
     * Obtenir l'utilisateur connecté
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }
        
        $stmt = self::$pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch();
    }
    
    /**
     * Connexion utilisateur
     */
    public static function login($username, $password, $rememberMe = false) {
        // Vérifier le rate limiting
        if (!Security::checkRateLimit('login_' . $_SERVER['REMOTE_ADDR'])) {
            return ['success' => false, 'message' => 'Trop de tentatives. Réessayez dans 5 minutes.'];
        }
        
        $stmt = self::$pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            Security::incrementRateLimit('login_' . $_SERVER['REMOTE_ADDR']);
            return ['success' => false, 'message' => 'Identifiants incorrects'];
        }
        
        // Connexion réussie
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_permissions'] = ['admin']; // À développer selon les besoins
        
        // Mettre à jour la dernière connexion
        $stmt = self::$pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        Security::logAction('login', ['user_id' => $user['id'], 'username' => $username]);
        
        return ['success' => true, 'message' => 'Connexion réussie'];
    }
    
    /**
     * Déconnexion
     */
    public static function logout() {
        Security::logAction('logout', ['user_id' => $_SESSION['admin_id'] ?? null]);
        
        session_destroy();
        session_start();
    }
    
    /**
     * Exiger une connexion
     */
    public static function requireLogin() {
        if (!self::check()) {
            if (App::isAjax()) {
                App::jsonResponse(['error' => 'Non autorisé'], 401);
            }
            
            $currentDir = basename(dirname($_SERVER['PHP_SELF']));
            if ($currentDir !== 'admin') {
                App::redirect('admin/login.php');
            } else {
                App::redirect('login.php');
            }
        }
    }
}

/**
 * Classe de gestion du cache
 */
class Cache {
    private static $cacheDir = __DIR__ . '/../cache/';
    
    /**
     * Initialiser le cache
     */
    public static function init() {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    /**
     * Obtenir une valeur du cache
     */
    public static function get($key) {
        $filename = self::$cacheDir . md5($key) . '.cache';
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        if ($data['expires'] < time()) {
            unlink($filename);
            return null;
        }
        
        return $data['value'];
    }
    
    /**
     * Stocker une valeur dans le cache
     */
    public static function set($key, $value, $duration = 3600) {
        $filename = self::$cacheDir . md5($key) . '.cache';
        
        $data = [
            'value' => $value,
            'expires' => time() + $duration
        ];
        
        file_put_contents($filename, serialize($data));
    }
    
    /**
     * Supprimer une valeur du cache
     */
    public static function delete($key) {
        $filename = self::$cacheDir . md5($key) . '.cache';
        
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
    
    /**
     * Vider tout le cache
     */
    public static function clear() {
        $files = glob(self::$cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}

// Initialiser les composants
Auth::init($pdo);
Cache::init();

// Fonctions utilitaires globales pour compatibilité
function isLoggedIn() {
    return Auth::check();
}

function requireLogin() {
    Auth::requireLogin();
}

function formatPrice($price) {
    return App::formatPrice($price);
}

function formatDate($date) {
    return App::formatDate($date);
}

function generateCSRFToken() {
    return Security::generateCSRFToken();
}

function verifyCSRFToken($token) {
    return Security::verifyCSRFToken($token);
}

function isValidImageUpload($file) {
    $result = Security::validateImageUpload($file);
    return $result['valid'];
}
?>