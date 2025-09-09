<?php
/**
 * Configuration principale - Rétrocompatibilité
 * Charge le nouveau système de configuration
 */

// Charger le nouveau système
require_once __DIR__ . '/config/bootstrap.php';

// Les fonctions isLoggedIn() et requireLogin() sont déjà définies dans bootstrap.php

// Fonction pour rediriger vers la page de connexion (version legacy)
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

// Fonction pour sécuriser les uploads - déjà définie dans bootstrap.php via Security class

// Fonctions CSRF - déjà définies dans bootstrap.php via Security class

// Fonction pour formater les prix - déplacée dans includes/config.php

// Fonction pour formater les dates - déjà définie dans bootstrap.php
?>



