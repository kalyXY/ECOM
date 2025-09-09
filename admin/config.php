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
    // Si l'extension GD n'est pas chargée, copier simplement l'image sans redimensionnement
    if (!extension_loaded('gd')) {
        return @copy($source, $destination);
    }

    $imageInfo = @getimagesize($source);
    if (!$imageInfo) return @copy($source, $destination);
    
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $type = $imageInfo[2];
    
    // Calculer les nouvelles dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    // Si l'image est déjà plus petite que la cible, conserver la taille et juste copier
    if ($ratio >= 1) {
        return @copy($source, $destination);
    }
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);
    
    // Créer l'image source (en gardant des garde-fous)
    switch ($type) {
        case IMAGETYPE_JPEG:
            if (!function_exists('imagecreatefromjpeg')) return @copy($source, $destination);
            $sourceImage = @imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            if (!function_exists('imagecreatefrompng')) return @copy($source, $destination);
            $sourceImage = @imagecreatefrompng($source);
            break;
        default:
            return @copy($source, $destination);
    }
    if (!$sourceImage) return @copy($source, $destination);
    
    // Créer l'image de destination
    $destImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Préserver la transparence pour PNG
    if ($type == IMAGETYPE_PNG) {
        imagealphablending($destImage, false);
        imagesavealpha($destImage, true);
        $transparent = imagecolorallocatealpha($destImage, 0, 0, 0, 127);
        imagefilledrectangle($destImage, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Redimensionner
    imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Sauvegarder
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            if (function_exists('imagejpeg')) {
                $result = @imagejpeg($destImage, $destination, 85);
            }
            break;
        case IMAGETYPE_PNG:
            if (function_exists('imagepng')) {
                $result = @imagepng($destImage, $destination);
            }
            break;
    }
    
    // Nettoyer la mémoire
    imagedestroy($sourceImage);
    imagedestroy($destImage);
    
    // Si la sauvegarde a échoué, fallback: copier l'original
    if (!$result) {
        return @copy($source, $destination);
    }
    return true;
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

// Fonction pour valider les uploads d'images (compatibilité)
if (!function_exists('isValidImageUpload')) {
function isValidImageUpload($file) {
    $validation = Security::validateImageUpload($file);
    return $validation['valid'];
}
}
?>