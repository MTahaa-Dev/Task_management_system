<?php
$required_role = 'user';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$userId = $_SESSION['user_id'];

// Personal Stats
$stats = [
    'assigned' => $pdo->query("SELECT COUNT(*) FROM tasks WHERE (assigned_to = $userId OR assigned_to IS NULL)")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM tasks WHERE (assigned_to = $userId OR assigned_to IS NULL) AND status != 'Completed'")->fetchColumn(),
    'completed' => $pdo->query("SELECT COUNT(*) FROM tasks WHERE (assigned_to = $userId OR assigned_to IS NULL) AND status = 'Completed'")->fetchColumn(),
];

// Calculate Productivity Score
$productivityScore = 0;
if ($stats['assigned'] > 0) {
    $productivityScore = round(($stats['completed'] / $stats['assigned']) * 100);
}

// Upcoming Deadlines (Next 7 Days)
$upcomingDeadlines = $pdo->query("
    SELECT id, title, deadline, priority 
    FROM tasks 
    WHERE (assigned_to = $userId OR assigned_to IS NULL)
      AND status != 'Completed' 
      AND deadline IS NOT NULL 
      AND deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY deadline ASC
")->fetchAll();

// Recent Activity for User
$recentActivity = $pdo->query("
    SELECT action, created_at 
    FROM activity_log 
    WHERE user_id = $userId 
    ORDER BY created_at DESC LIMIT 5
")->fetchAll();

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
                    <h3 class="fw-bold mb-1">Hello, <?php echo htmlspecialchars($currentUser['full_name']); ?>! 👋</h3>
                    <p class="mb-0 text-white-50">Here is your personal productivity overview.</p>
                </div>
                <div class="d-none d-md-flex align-items-center bg-white bg-opacity-10 rounded p-3">
                    <div class="me-3">
                        <p class="mb-0 small text-white-50 text-uppercase fw-bold">Productivity Score</p>
                        <h3 class="fw-bold mb-0 text-white counter-value" data-target="<?php echo $productivityScore; ?>">0</h3>
                    </div>
                    <div style="width: 60px; height: 60px;">
                        <canvas id="scoreChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-premium p-3 h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 fw-bold small text-uppercase">Total Assigned</p>
                        <h2 class="fw-bold mb-0 text-primary counter-value" data-target="<?php echo $stats['assigned']; ?>">0</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                        <i class='bx bx-list-ul fs-3'></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card-premium p-3 h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 fw-bold small text-uppercase">Active Tasks</p>
                        <h2 class="fw-bold mb-0 text-warning counter-value" data-target="<?php echo $stats['pending']; ?>">0</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                        <i class='bx bx-loader-circle fs-3'></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card-premium p-3 h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 fw-bold small text-uppercase">Completed</p>
                        <h2 class="fw-bold mb-0 text-success counter-value" data-target="<?php echo $stats['completed']; ?>">0</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                        <i class='bx bx-check-double fs-3'></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Row -->
        <div class="row g-4">
            <!-- Upcoming Deadlines -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="card-premium h-100">
                    <div class="card-header-premium d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class='bx bx-timer text-danger me-2'></i>Upcoming Deadlines (7 Days)</h6>
                        <a href="my_tasks.php" class="btn btn-sm btn-light border small">View All Tasks</a>
                    </div>
                    <div class="p-0">
                        <?php if(empty($upcomingDeadlines)): ?>
                            <div class="p-5 text-center text-muted">
                                <i class='bx bx-party fs-1 mb-2'></i>
                                <p class="mb-0 small">No upcoming deadlines. Great job!</p>
                            </div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach($upcomingDeadlines as $task): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center p-3 border-bottom">
                                        <div>
                                            <a href="my_tasks.php" class="fw-bold text-dark text-decoration-none d-block mb-1"><?php echo htmlspecialchars($task['title']); ?></a>
                                            <span class="small text-danger fw-bold"><i class='bx bx-calendar me-1'></i><?php echo format_date($task['deadline']); ?></span>
                                        </div>
                                        <?php echo priority_badge($task['priority']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="card-premium h-100">
                    <div class="card-header-premium">
                        <h6 class="mb-0 fw-bold"><i class='bx bx-history text-primary me-2'></i>Your Recent Activity</h6>
                    </div>
                    <div class="p-4">
                        <?php if(empty($recentActivity)): ?>
                            <div class="text-center text-muted py-4">
                                <p class="mb-0 small">No recent activity found.</p>
                            </div>
                        <?php else: ?>
                            <div class="timeline position-relative ps-4" style="border-left: 2px solid var(--border-color);">
                                <?php foreach($recentActivity as $log): ?>
                                    <div class="timeline-item position-relative mb-4">
                                        <div class="timeline-icon position-absolute rounded-circle bg-primary text-white" style="width: 12px; height: 12px; left: -31px; top: 5px; border: 2px solid var(--card-bg);"></div>
                                        <p class="mb-1 small fw-bold text-dark"><?php echo htmlspecialchars($log['action']); ?></p>
                                        <small class="text-muted" style="font-size: 0.7rem;"><i class='bx bx-time'></i> <?php echo format_date($log['created_at'], 'M j, g:i a'); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
        const duration = 1500;
        const increment = target / (duration / 16);
        let current = 0;
        const updateCounter = () => {
            current += increment;
            if(current < target) {
                counter.innerText = Math.ceil(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = target + (counter.parentElement.innerText.includes('Score') ? '%' : '');
            }
        };
        if(target > 0) updateCounter();
        else if(counter.parentElement.innerText.includes('Score')) counter.innerText = "0%";
    });

    // Score Doughnut Chart
    const score = <?php echo $productivityScore; ?>;
    const ctx = document.getElementById('scoreChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [score, 100 - score],
                backgroundColor: ['#FFFFFF', 'rgba(255,255,255,0.2)'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '80%',
            plugins: { tooltip: { enabled: false }, legend: { display: false } },
            animation: { animateScale: true }
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
