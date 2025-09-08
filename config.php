<?php
/**
 * Configuration principale - Rétrocompatibilité
 * Charge le nouveau système de configuration
 */

// Charger le nouveau système
require_once __DIR__ . '/config/bootstrap.php';

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

// Fonction pour rediriger vers la page de connexion
function requireLogin() {
    if (!isLoggedIn()) {
        // Rediriger vers le dossier admin si on n'est pas déjà dedans
        $currentDir = basename(dirname($_SERVER['PHP_SELF']));
        if ($currentDir !== 'admin') {
            header('Location: admin/login.php');
        } else {
            header('Location: login.php');
        }
        exit();
    }
}

// Fonction pour sécuriser les uploads
function isValidImageUpload($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    return true;
}

// Fonction pour générer un token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour vérifier le token CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Fonction pour formater les prix - déplacée dans includes/config.php

// Fonction pour formater les dates
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>



