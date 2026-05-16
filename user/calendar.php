<?php
$required_role = 'user';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (isset($_GET['action']) && $_GET['action'] === 'get_events') {
    header('Content-Type: application/json');
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT id, title, deadline, priority, status FROM tasks WHERE assigned_to = ? AND deadline IS NOT NULL");
    $stmt->execute([$userId]);
    $tasks = $stmt->fetchAll();
    
    $events = [];
    foreach ($tasks as $task) {
        $color = '#6c757d';
        if ($task['status'] === 'Completed') {
            $color = '#10B981';
        } else {
            switch ($task['priority']) {
                case 'Urgent': $color = '#EF4444'; break;
                case 'High': $color = '#F59E0B'; break;
                case 'Medium': $color = '#3B82F6'; break;
                case 'Low': $color = '#0EA5E9'; break;
            }
        }
        
        $events[] = [
            'id' => $task['id'],
            'title' => $task['title'],
            'start' => $task['deadline'],
            'allDay' => true,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'extendedProps' => [
                'priority' => $task['priority'],
                'status' => $task['status']
            ]
        ];
    }
    
    echo json_encode($events);
    exit;
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<style>
    .fc-theme-standard td, .fc-theme-standard th { border-color: var(--border-color); }
    .fc .fc-button-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
    .fc .fc-button-primary:not(:disabled):active, .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: var(--primary-hover); border-color: var(--primary-hover); }
    .fc-event { cursor: pointer; border-radius: 4px; padding: 2px 4px; font-size: 0.8rem; transition: transform 0.2s; }
    .fc-event:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    [data-theme="dark"] .fc-daygrid-day-number { color: var(--text-main); }
    [data-theme="dark"] .fc-col-header-cell-cushion { color: var(--text-main); }
</style>

<div class="main-content">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
            <div>
                <h4 class="fw-bold mb-0">My Calendar</h4>
                <p class="text-muted small mb-0">View all your upcoming personal deadlines.</p>
            </div>
        </div>

        <div class="card-premium p-4" data-aos="zoom-in">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        height: 700,
        events: 'calendar.php?action=get_events',
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            // Redirect to task detail
            window.location.href = 'my_tasks.php?view=' + info.event.id;
        }
    });
    calendar.render();

    document.getElementById('theme-toggle').addEventListener('click', () => {
        setTimeout(() => { calendar.render(); }, 200);
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
