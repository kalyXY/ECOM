<?php
/**
 * Configuration de sécurité avancée
 * StyleHub E-Commerce Platform
 */

// Configuration des sessions sécurisées
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 3600); // 1 heure

// Démarrer la session avec sécurité renforcée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Régénérer l'ID de session périodiquement
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Classe de sécurité centralisée
 */
class Security {
    
    /**
     * Générer un token CSRF sécurisé
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Vérifier un token CSRF
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Nettoyer et valider les entrées
     */
    public static function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return self::sanitizeInput($item, $type);
            }, $input);
        }
        
        $input = trim($input);
        
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'int':
                return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                // Normaliser les nombres décimaux (gérer virgules et espaces)
                $normalized = str_replace([' ', '\u00A0'], '', $input); // enlever espaces classiques et insécables
                $normalized = str_replace(',', '.', $normalized); // convertir virgule en point décimal
                // Garder uniquement chiffres, signe et point
                $normalized = preg_replace('/[^0-9\.-]/', '', $normalized);
                if ($normalized === '' || $normalized === '-' || $normalized === '.') {
                    return null;
                }
                return (float) $normalized;
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'html':
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            default:
                return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Valider un upload d'image
     */
    public static function validateImageUpload($file) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Vérifier si le fichier a été uploadé
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Fichier non valide'];
        }
        
        // Vérifier la taille
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'Fichier trop volumineux (max 5MB)'];
        }
        
        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['valid' => false, 'error' => 'Type de fichier non autorisé'];
        }
        
        // Vérifier l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'error' => 'Extension de fichier non autorisée'];
        }
        
        // Vérifier que c'est vraiment une image
        if (!getimagesize($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Fichier corrompu ou non valide'];
        }
        
        return ['valid' => true, 'mime_type' => $mimeType, 'extension' => $extension];
    }
    
    /**
     * Générer un nom de fichier sécurisé
     */
    public static function generateSecureFileName($originalName) {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(16));
        return $filename . '.' . $extension;
    }
    
    /**
     * Vérifier les permissions utilisateur
     */
    public static function hasPermission($permission) {
        return isset($_SESSION['admin_id']) && 
               isset($_SESSION['admin_permissions']) && 
               in_array($permission, $_SESSION['admin_permissions']);
    }
    
    /**
     * Logger les actions sensibles
     */
    public static function logAction($action, $details = []) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['admin_id'] ?? 'anonymous',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'action' => $action,
            'details' => $details,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        error_log("SECURITY_LOG: " . json_encode($logData));
    }
    
    /**
     * Protection contre le brute force
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
        $key = 'rate_limit_' . $identifier;
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
        }
        
        $data = $_SESSION[$key];
        
        // Reset si la fenêtre de temps est expirée
        if (time() - $data['first_attempt'] > $timeWindow) {
            $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
            return true;
        }
        
        // Vérifier si la limite est atteinte
        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Incrémenter le compteur de tentatives
     */
    public static function incrementRateLimit($identifier) {
        $key = 'rate_limit_' . $identifier;
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
        }
        
        $_SESSION[$key]['attempts']++;
    }
}

// Headers de sécurité
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (isset($_SERVER['HTTPS'])) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
?>