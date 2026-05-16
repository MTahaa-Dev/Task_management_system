<?php
$required_role = 'admin';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Fetch Statistics
$stats = [
    'total_tasks' => $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn(),
    'pending_tasks' => $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Pending'")->fetchColumn(),
    'completed_tasks' => $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Completed'")->fetchColumn(),
    'overdue_tasks' => $pdo->query("SELECT COUNT(*) FROM tasks WHERE deadline < CURDATE() AND status != 'Completed'")->fetchColumn(),
    'active_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn()
];

// Fetch Recent Tasks
$recentTasks = $pdo->query("
    SELECT t.*, u.full_name as assignee 
    FROM tasks t 
    LEFT JOIN users u ON t.assigned_to = u.id 
    ORDER BY t.created_at DESC LIMIT 5
")->fetchAll();

// Fetch Recent Activity
$activities = $pdo->query("
    SELECT a.*, u.full_name, u.profile_picture 
    FROM activity_log a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC LIMIT 6
")->fetchAll();

// Chart Data: Tasks by Status (Pie)
$statusCounts = $pdo->query("SELECT status, COUNT(*) as count FROM tasks GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$statusLabels = json_encode(array_keys($statusCounts));
$statusData = json_encode(array_values($statusCounts));

// Chart Data: Weekly Productivity (Line - last 7 days completed tasks)
$weeklyDataQuery = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM tasks 
    WHERE status = 'Completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
    GROUP BY DATE(created_at) ORDER BY date ASC
")->fetchAll();
$weeklyLabels = [];
$weeklyCounts = [];
for($i=6; $i>=0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $weeklyLabels[] = date('M d', strtotime($date));
    $found = false;
    foreach($weeklyDataQuery as $row) {
        if($row['date'] == $date) {
            $weeklyCounts[] = $row['count'];
            $found = true;
            break;
        }
    }
    if(!$found) $weeklyCounts[] = 0;
}
$weeklyLabelsJson = json_encode($weeklyLabels);
$weeklyCountsJson = json_encode($weeklyCounts);

?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid p-4">
        
        <!-- Welcome Banner -->
        <div class="card-premium p-4 mb-4 border-0" style="background: var(--gradient-primary); color: white;" data-aos="fade-in">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold mb-1">Welcome back, <?php echo htmlspecialchars($currentUser['full_name']); ?>! 👋</h3>
                    <p class="mb-0 text-white-50">Here is what's happening with your projects today.</p>
                </div>
                <div class="d-none d-md-block text-end">
                    <p class="mb-0 text-white-50 fs-6"><?php echo date('l, F j, Y'); ?></p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-sm-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card-premium p-3 h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 fw-bold small text-uppercase">Total Tasks</p>
                        <h2 class="fw-bold mb-0 text-primary counter-value" data-target="<?php echo $stats['total_tasks']; ?>">0</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center">
                        <i class='bx bx-task fs-3'></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-sm-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card-premium p-3 h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 fw-bold small text-uppercase">Completed</p>
                        <h2 class="fw-bold mb-0 text-success counter-value" data-target="<?php echo $stats['completed_tasks']; ?>">0</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center">
                        <i class='bx bx-check-circle fs-3'></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6" data-aos="fade-up" data-aos-delay="300">
                <div class="card-premium p-3 h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 fw-bold small text-uppercase">Overdue</p>
                        <h2 class="fw-bold mb-0 text-danger counter-value" data-target="<?php echo $stats['overdue_tasks']; ?>">0</h2>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3 d-flex align-items-center justify-content-center">
                        <i class='bx bx-time fs-3'></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6" data-aos="fade-up" data-aos-delay="400">
                <div class="card-premium p-3 h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 fw-bold small text-uppercase">Active Users</p>
                        <h2 class="fw-bold mb-0 text-info counter-value" data-target="<?php echo $stats['active_users']; ?>">0</h2>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 d-flex align-items-center justify-content-center">
                        <i class='bx bx-group fs-3'></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8" data-aos="fade-up">
                <div class="card-premium h-100">
                    <div class="card-header-premium d-flex justify-content-between align-items-center border-0 pb-0">
                        <h6 class="mb-0 fw-bold">Weekly Productivity (Completed Tasks)</h6>
                        <i class='bx bx-trending-up text-success fs-4'></i>
                    </div>
                    <div class="p-4">
                        <canvas id="productivityChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-premium h-100">
                    <div class="card-header-premium border-0 pb-0">
                        <h6 class="mb-0 fw-bold">Task Status Distribution</h6>
                    </div>
                    <div class="p-4 d-flex justify-content-center">
                        <canvas id="statusChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="row g-4">
            <div class="col-lg-8" data-aos="fade-right">
                <div class="card-premium h-100">
                    <div class="card-header-premium d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Recent Tasks</h6>
                        <a href="tasks.php" class="btn btn-sm btn-light border small">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-premium mb-0">
                            <thead>
                                <tr>
                                    <th>Task Title</th>
                                    <th>Assignee</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($recentTasks)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class='bx bx-inbox fs-1 mb-2'></i><br>No recent tasks found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($recentTasks as $task): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($task['title']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 25px; height: 25px; font-size: 0.7rem;">
                                                    <?php echo strtoupper(substr($task['assignee'] ?? 'U', 0, 1)); ?>
                                                </div>
                                                <span class="small"><?php echo htmlspecialchars($task['assignee'] ?? 'Unassigned'); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo priority_badge($task['priority']); ?></td>
                                        <td><?php echo status_badge($task['status']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4" data-aos="fade-left">
                <div class="card-premium h-100">
                    <div class="card-header-premium">
                        <h6 class="mb-0 fw-bold">Activity Timeline</h6>
                    </div>
                    <div class="p-4 pt-2">
                        <div class="timeline position-relative ps-4" style="border-left: 2px solid var(--border-color);">
                            <?php foreach($activities as $log): ?>
                                <div class="timeline-item position-relative mb-4">
                                    <div class="timeline-icon position-absolute rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 12px; height: 12px; left: -31px; top: 5px; border: 2px solid var(--card-bg);"></div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <h6 class="mb-0 small fw-bold"><?php echo htmlspecialchars($log['full_name']); ?></h6>
                                        <small class="text-muted" style="font-size: 0.7rem;"><?php echo format_date($log['created_at'], 'M j, g:i a'); ?></small>
                                    </div>
                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($log['action']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="activity_log.php" class="text-primary text-decoration-none small fw-bold">View full log <i class='bx bx-right-arrow-alt'></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Counter Animation
    const counters = document.querySelectorAll('.counter-value');
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const duration = 1500; // ms
        const increment = target / (duration / 16); // 60fps
        
        let current = 0;
        const updateCounter = () => {
            current += increment;
            if(current < target) {
                counter.innerText = Math.ceil(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = target;
            }
        };
        if(target > 0) updateCounter();
    });

    // Setup Chart Defaults for Dark Mode Support
    const getChartColor = () => document.documentElement.getAttribute('data-theme') === 'dark' ? '#94A3B8' : '#6B7280';
    const getGridColor = () => document.documentElement.getAttribute('data-theme') === 'dark' ? '#334155' : '#E5E7EB';

    // Status Chart (Doughnut)
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    const statusLabels = <?php echo $statusLabels ?: '[]'; ?>;
    const statusData = <?php echo $statusData ?: '[]'; ?>;
    
    // Map colors to statuses
    const colorMap = {
        'Pending': '#6c757d',
        'In Progress': '#0d6efd',
        'Review': '#ffc107',
        'Completed': '#198754'
    };
    const bgColors = statusLabels.map(lbl => colorMap[lbl] || '#4F46E5');

    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: bgColors,
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            cutout: '75%',
            plugins: {
                legend: { position: 'bottom', labels: { color: getChartColor(), usePointStyle: true, padding: 20 } }
            },
            animation: { animateScale: true, animateRotate: true }
        }
    });

    // Productivity Chart (Line)
    const ctxProd = document.getElementById('productivityChart').getContext('2d');
    const prodChart = new Chart(ctxProd, {
        type: 'line',
        data: {
            labels: <?php echo $weeklyLabelsJson; ?>,
            datasets: [{
                label: 'Completed Tasks',
                data: <?php echo $weeklyCountsJson; ?>,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#10B981',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: getGridColor(), drawBorder: false }, ticks: { stepSize: 1, color: getChartColor() } },
                x: { grid: { display: false, drawBorder: false }, ticks: { color: getChartColor() } }
            },
            interaction: { mode: 'index', intersect: false }
        }
    });

    // Update charts on theme toggle
    document.getElementById('theme-toggle').addEventListener('click', () => {
        setTimeout(() => {
            const color = getChartColor();
            const gridColor = getGridColor();
            
            prodChart.options.scales.y.grid.color = gridColor;
            prodChart.options.scales.y.ticks.color = color;
            prodChart.options.scales.x.ticks.color = color;
            prodChart.update();
        }, 100);
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
