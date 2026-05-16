<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Get current page name for active states if not already set by the script
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Task Manager</title>
    
    <!-- Google Fonts: Inter for modern SaaS look -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome & BoxIcons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css">

    <!-- FullCalendar CSS (for later phases) -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

    <!-- Custom Premium CSS -->
    <link rel="stylesheet" href="/demo/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/demo/assets/css/darkmode.css?v=<?php echo time(); ?>">
    
    <!-- Initialize Dark Mode Early to prevent flash -->
    <script>
        <?php if(!isset($_SESSION['user_id'])): ?>
            // Force Dark Mode on Login/Register pages
            document.documentElement.setAttribute('data-theme', 'dark');
        <?php else: ?>
            // Use saved preference for logged in users, defaulting to dark mode
            if (localStorage.getItem('theme') === 'light') {
                document.documentElement.setAttribute('data-theme', 'light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        <?php endif; ?>
    </script>
</head>
<body class="page-transition">
    <!-- Global Preloader -->
    <div id="global-preloader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg-color); z-index: 9999; display: flex; align-items: center; justify-content: center; transition: opacity 0.5s ease;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <?php if(isset($_SESSION['user_id'])): ?>
    <div class="wrapper d-flex">
        <!-- Sidebar and Navbar will be included in the individual pages via includes -->
    <?php endif; ?>
