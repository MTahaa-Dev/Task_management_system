<!-- Premium Sidebar component -->
<nav id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <i class='bx bx-task text-primary me-2 fs-3'></i>
        <h3>TaskMaster</h3>
    </div>

    <ul class="list-unstyled components">
        <li class="px-3 mb-2 text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 1px;">
            <span class="link-text">Main Menu</span>
        </li>
        
        <?php if(is_admin()): ?>
            <!-- Admin Navigation -->
            <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <a href="/demo/admin/dashboard.php">
                    <i class='bx bx-grid-alt'></i>
                    <span class="link-text">Dashboard</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'tasks.php') ? 'active' : ''; ?>">
                <a href="/demo/admin/tasks.php">
                    <i class='bx bx-list-check'></i>
                    <span class="link-text">All Tasks</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'kanban.php') ? 'active' : ''; ?>">
                <a href="/demo/admin/kanban.php">
                    <i class='bx bx-columns'></i>
                    <span class="link-text">Kanban Board</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'calendar.php') ? 'active' : ''; ?>">
                <a href="/demo/admin/calendar.php">
                    <i class='bx bx-calendar'></i>
                    <span class="link-text">Calendar View</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
                <a href="/demo/admin/users.php">
                    <i class='bx bx-group'></i>
                    <span class="link-text">Manage Users</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'chat') ? 'active' : ''; ?>">
                <a href="/demo/chat/">
                    <i class='bx bx-message-square-dots'></i>
                    <span class="link-text">Team Chat</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'activity_log.php') ? 'active' : ''; ?>">
                <a href="/demo/admin/activity_log.php">
                    <i class='bx bx-history'></i>
                    <span class="link-text">Activity Log</span>
                </a>
            </li>
        <?php else: ?>
            <!-- Regular User Navigation -->
            <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <a href="/demo/user/dashboard.php">
                    <i class='bx bx-grid-alt'></i>
                    <span class="link-text">My Dashboard</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'my_tasks.php') ? 'active' : ''; ?>">
                <a href="/demo/user/my_tasks.php">
                    <i class='bx bx-list-ul'></i>
                    <span class="link-text">My Tasks</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'calendar.php') ? 'active' : ''; ?>">
                <a href="/demo/user/calendar.php">
                    <i class='bx bx-calendar'></i>
                    <span class="link-text">My Calendar</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'chat') ? 'active' : ''; ?>">
                <a href="/demo/chat/">
                    <i class='bx bx-message-square-dots'></i>
                    <span class="link-text">Team Chat</span>
                </a>
            </li>
        <?php endif; ?>
        
        <li class="px-3 mt-4 mb-2 text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 1px;">
            <span class="link-text">Account</span>
        </li>
        <li class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
            <a href="/demo/user/profile.php">
                <i class='bx bx-user'></i>
                <span class="link-text">My Profile</span>
            </a>
        </li>
        <li>
            <a href="/demo/auth/logout.php" class="text-danger">
                <i class='bx bx-log-out'></i>
                <span class="link-text">Logout</span>
            </a>
        </li>
    </ul>
</nav>
