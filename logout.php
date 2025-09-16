<?php
require_once 'includes/config.php';

// Unset all of the session variables
$_SESSION = [];

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to homepage with a message
// We start a new session briefly to pass the message
session_start();
$_SESSION['message'] = 'Vous avez été déconnecté avec succès.';
$_SESSION['message_type'] = 'success';
header('Location: index.php');
exit;
?>
