<?php
/**
 * Configuration principale - Rétrocompatibilité
 * Charge le nouveau système de configuration
 */

// Charger le nouveau système
require_once __DIR__ . '/config/bootstrap.php';

// Les fonctions isLoggedIn() et requireLogin() sont déjà définies dans config/bootstrap.php

// Fonction legacy pour rediriger vers la page de connexion (si nécessaire)
function requireLoginLegacy() {
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

// Toutes les fonctions utilitaires sont déjà définies dans config/bootstrap.php :
// - isValidImageUpload() via Security::validateImageUpload()
// - generateCSRFToken() via Security::generateCSRFToken()
// - verifyCSRFToken() via Security::verifyCSRFToken()
// - formatPrice() via App::formatPrice()
// - formatDate() via App::formatDate()

// Fonction pour formater les prix - déplacée dans includes/config.php

// Fonction pour formater les dates - déjà définie dans config/bootstrap.php
?>



