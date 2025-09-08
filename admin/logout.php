<?php
require_once 'config.php';

// Détruire toutes les données de session
session_destroy();

// Supprimer le cookie remember me s'il existe
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Rediriger vers la page de connexion avec un message
header('Location: login.php?message=disconnected');
exit();
?>