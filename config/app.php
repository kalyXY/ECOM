<?php
/**
 * Configuration principale de l'application
 * StyleHub E-Commerce Platform
 */

// Constantes de l'application
define('APP_NAME', 'StyleHub');
define('APP_VERSION', '2.0.0');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('DEBUG_MODE', $_ENV['DEBUG'] ?? false);

// Paramètres de l'application
$appConfig = [
    'timezone' => 'Europe/Paris',
    'locale' => 'fr_FR',
    'currency' => 'EUR',
    'currency_symbol' => '€',
    'items_per_page' => 12,
    'max_upload_size' => 5 * 1024 * 1024, // 5MB
    'allowed_image_types' => ['jpg', 'jpeg', 'png', 'webp'],
    'cache_duration' => 3600, // 1 heure
    'session_lifetime' => 3600, // 1 heure
];

// Configuration du fuseau horaire
date_default_timezone_set($appConfig['timezone']);

/**
 * Classe de configuration centralisée
 */
class Config {
    private static $config = [];
    
    /**
     * Initialiser la configuration
     */
    public static function init($config) {
        self::$config = $config;
    }
    
    /**
     * Obtenir une valeur de configuration
     */
    public static function get($key, $default = null) {
        return self::$config[$key] ?? $default;
    }
    
    /**
     * Définir une valeur de configuration
     */
    public static function set($key, $value) {
        self::$config[$key] = $value;
    }
    
    /**
     * Vérifier si une clé existe
     */
    public static function has($key) {
        return isset(self::$config[$key]);
    }
}

// Initialiser la configuration
Config::init($appConfig);

/**
 * Classe utilitaire pour l'application
 */
class App {
    
    /**
     * Formater un prix
     */
    public static function formatPrice($price) {
        return number_format((float)$price, 2, ',', ' ') . ' ' . Config::get('currency_symbol');
    }
    
    /**
     * Formater une date
     */
    public static function formatDate($date, $format = 'd/m/Y H:i') {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        return $date->format($format);
    }
    
    /**
     * Générer une URL
     */
    public static function url($path = '') {
        return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * Rediriger vers une URL
     */
    public static function redirect($url, $statusCode = 302) {
        if (!headers_sent()) {
            header("Location: " . self::url($url), true, $statusCode);
            exit;
        }
    }
    
    /**
     * Obtenir l'URL actuelle
     */
    public static function currentUrl() {
        $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Vérifier si on est en mode AJAX
     */
    public static function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Retourner une réponse JSON
     */
    public static function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Générer un slug à partir d'un texte
     */
    public static function generateSlug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text;
    }
    
    /**
     * Calculer le temps de lecture estimé
     */
    public static function readingTime($text, $wordsPerMinute = 200) {
        $wordCount = str_word_count(strip_tags($text));
        $minutes = ceil($wordCount / $wordsPerMinute);
        return max(1, $minutes);
    }
    
    /**
     * Compresser une image
     */
    public static function compressImage($source, $destination, $quality = 80) {
        $info = getimagesize($source);
        
        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
        } elseif ($info['mime'] == 'image/webp') {
            $image = imagecreatefromwebp($source);
        } else {
            return false;
        }
        
        // Redimensionner si nécessaire (max 1200px)
        $maxWidth = 1200;
        $width = imagesx($image);
        $height = imagesy($image);
        
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = ($height / $width) * $newWidth;
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }
        
        // Sauvegarder
        if ($info['mime'] == 'image/jpeg') {
            return imagejpeg($image, $destination, $quality);
        } elseif ($info['mime'] == 'image/png') {
            return imagepng($image, $destination, 9 - ($quality / 10));
        } elseif ($info['mime'] == 'image/webp') {
            return imagewebp($image, $destination, $quality);
        }
        
        imagedestroy($image);
        return false;
    }
}
?>