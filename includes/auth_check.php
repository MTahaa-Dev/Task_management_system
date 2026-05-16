<?php
/**
 * Route protection check
 * Include this at the top of any protected page
 */

// If functions or database aren't loaded, we're probably including this wrong, but let's be safe
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/functions.php';
}

if (!is_logged_in()) {
    $_SESSION['flash_error'] = "You must be logged in to view that page.";
    header("Location: /demo/auth/login.php");
    exit();
}

// Optionally check role if defined in the script before including this
if (isset($required_role)) {
    if ($required_role === 'admin' && !is_admin()) {
        $_SESSION['flash_error'] = "You do not have permission to access this area.";
        header("Location: /demo/user/dashboard.php");
        exit();
    }
}

// Update last activity or something if needed
?>
