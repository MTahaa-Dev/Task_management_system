<?php
// Fetch current user details for the navbar
$currentUser = get_current_user_data($pdo);

// Determine the page title based on the filename
$pageTitles = [
    'dashboard.php' => 'Dashboard',
    'tasks.php' => 'Task Management',
    'kanban.php' => 'Kanban Board',
    'calendar.php' => 'Calendar View',
    'users.php' => 'User Management',
    'activity_log.php' => 'Activity History',
    'chat' => 'Team Chat',
    'my_tasks.php' => 'My Assigned Tasks',
    'profile.php' => 'My Profile'
];
$title = $pageTitles[$current_page] ?? 'Dashboard';

// Fetch Notifications
$userId = $_SESSION['user_id'];
$notifCountStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$notifCountStmt->execute([$userId]);
$unreadCount = $notifCountStmt->fetchColumn();

$notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$notifStmt->execute([$userId]);
$notifications = $notifStmt->fetchAll();

// Handle Mark All Read via AJAX (using a simple GET endpoint pattern, but we'll do it via a quick inline script for UI polish or just visual clear)
// Actually we can add a small endpoint `?mark_read=1` check here if we wanted.
if(isset($_GET['mark_read'])) {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$userId]);
    $unreadCount = 0;
    // We would normally redirect here to clear the param, but this is an include. 
}
?>
<!-- Premium Top Navbar -->
<nav class="top-navbar">
    <div class="navbar-left">
        <button type="button" id="sidebarCollapse" title="Toggle Sidebar">
            <i class='bx bx-menu-alt-left'></i>
        </button>
        <h4 class="mb-0 ms-2 fw-bold d-none d-md-block"><?php echo htmlspecialchars($title); ?></h4>
        
        <!-- Breadcrumbs removed as per user request -->
    </div>

    <div class="navbar-right">
        <!-- Search Bar removed as per request -->

        <!-- Dark Mode Toggle -->
        <div class="nav-icon-btn me-2" id="theme-toggle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Toggle Dark Mode">
            <i class='bx bx-moon'></i>
        </div>

        <!-- Notification Bell -->
        <div class="nav-icon-btn me-3 position-relative dropdown" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notifications">
            <div data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;" id="bellIcon">
                <i class='bx bx-bell'></i>
                <?php if($unreadCount > 0): ?>
                    <span class="notification-badge" id="notifBadge"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Notification Dropdown Panel -->
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-0" style="width: 320px; overflow: hidden; border-radius: var(--radius-md);">
                <li class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small text-uppercase">Notifications</span>
                    <?php if($unreadCount > 0): ?>
                        <a href="?mark_read=1" class="text-decoration-none text-primary fw-bold" style="font-size: 0.75rem;">Mark all read</a>
                    <?php endif; ?>
                </li>
                
                <div style="max-height: 300px; overflow-y: auto;" class="custom-scrollbar">
                    <?php if(empty($notifications)): ?>
                        <li>
                            <div class="p-4 text-center text-muted">
                                <i class='bx bx-envelope-open fs-2 mb-2'></i>
                                <p class="mb-0 small">No recent notifications</p>
                            </div>
                        </li>
                    <?php else: ?>
                        <?php foreach($notifications as $notif): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-start py-3 border-bottom <?php echo $notif['is_read'] ? '' : 'bg-primary bg-opacity-10'; ?>" href="#">
                                    <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 35px; height: 35px;">
                                        <?php if($notif['type'] == 'assignment'): ?>
                                            <i class='bx bx-task text-primary'></i>
                                        <?php elseif($notif['type'] == 'info'): ?>
                                            <i class='bx bx-info-circle text-info'></i>
                                        <?php else: ?>
                                            <i class='bx bx-bell text-secondary'></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1 small text-wrap <?php echo $notif['is_read'] ? 'text-muted' : 'text-dark fw-bold'; ?>"><?php echo htmlspecialchars($notif['message']); ?></p>
                                        <small class="text-muted" style="font-size: 0.65rem;"><i class='bx bx-time me-1'></i><?php echo format_date($notif['created_at'], 'M j, g:i A'); ?></small>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <li class="text-center border-top">
                    <a href="#" class="dropdown-item py-2 small fw-bold text-primary bg-light">View All Notifications</a>
                </li>
            </ul>
        </div>

        <!-- User Dropdown -->
        <div class="dropdown">
            <div class="d-flex align-items-center" style="cursor: pointer;" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="/demo/<?php echo htmlspecialchars(get_profile_picture($currentUser['profile_picture'] ?? '')); ?>" alt="Profile" class="rounded-circle zoom-hover object-fit-cover" width="36" height="36" style="border: 2px solid var(--primary-color);">
                <div class="ms-2 d-none d-sm-block text-end">
                    <p class="mb-0 fw-bold" style="font-size: 0.85rem; line-height: 1.2;"><?php echo htmlspecialchars($currentUser['full_name'] ?? 'User'); ?></p>
                    <small class="text-muted" style="font-size: 0.7rem;"><?php echo htmlspecialchars(ucfirst($currentUser['role'] ?? 'user')); ?></small>
                </div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li><a class="dropdown-item" href="/demo/user/profile.php"><i class='bx bx-user me-2'></i> My Profile</a></li>
                <li><a class="dropdown-item" href="/demo/user/dashboard.php"><i class='bx bx-grid-alt me-2'></i> Dashboard</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="/demo/auth/logout.php"><i class='bx bx-log-out me-2'></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
