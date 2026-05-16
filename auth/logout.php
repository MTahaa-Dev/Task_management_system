<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Log out activity
if (isset($_SESSION['user_id'])) {
    log_activity($pdo, $_SESSION['user_id'], 'User logged out');
}

// Clear session
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

session_destroy();
session_start();
$_SESSION['flash_success'] = "You have been successfully logged out.";
header("Location: /demo/auth/login.php");
exit();
?>
