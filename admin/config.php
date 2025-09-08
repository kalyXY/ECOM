<?php
// Configuration pour le back-office admin
require_once dirname(__DIR__) . '/config.php';

// Fonctions spécifiques à l'admin
if (!function_exists('formatPrice')) {
function formatPrice($price) {
    return number_format((float)$price, 2, ',', ' ') . ' €';
}
}

if (!function_exists('formatDate')) {
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
}

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

// Fonction pour redimensionner les images
if (!function_exists('resizeImage')) {
function resizeImage($source, $destination, $maxWidth = 800, $maxHeight = 600) {
    $imageInfo = getimagesize($source);
    if (!$imageInfo) return false;
    
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $type = $imageInfo[2];
    
    // Calculer les nouvelles dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);
    
    // Créer l'image source
    switch ($type) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($source);
            break;
        default:
            return false;
    }
    
    // Créer l'image de destination
    $destImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Préserver la transparence pour PNG
    if ($type == IMAGETYPE_PNG) {
        imagealphablending($destImage, false);
        imagesavealpha($destImage, true);
    }
    
    // Redimensionner
    imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Sauvegarder
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($destImage, $destination, 85);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($destImage, $destination);
            break;
        default:
            $result = false;
    }
    
    // Nettoyer la mémoire
    imagedestroy($sourceImage);
    imagedestroy($destImage);
    
    return $result;
}
}

// Fonction pour générer des slugs
if (!function_exists('generateSlug')) {
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}
}
?>