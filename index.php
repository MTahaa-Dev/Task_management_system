<?php
// Main Entry Point
session_start();

// If user is already logged in, redirect them to the correct dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: /demo/admin/dashboard.php");
        exit();
    } else {
        header("Location: /demo/user/dashboard.php");
        exit();
    }
}

// Otherwise, redirect to login
header("Location: /demo/auth/login.php");
exit();
?>
