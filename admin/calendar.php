<?php
$required_role = 'admin';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Endpoint for FullCalendar to fetch events
if (isset($_GET['action']) && $_GET['action'] === 'get_events') {
    header('Content-Type: application/json');
    $stmt = $pdo->query("SELECT id, title, deadline, priority, status FROM tasks WHERE deadline IS NOT NULL");
    $tasks = $stmt->fetchAll();
    
    $events = [];
    foreach ($tasks as $task) {
        // Map priority to calendar event colors
        $color = '#6c757d'; // default
        if ($task['status'] === 'Completed') {
            $color = '#10B981'; // Success Green
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
            'start' => $task['deadline'], // YYYY-MM-DD
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
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: var(--border-color);
    }
    .fc .fc-button-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .fc .fc-button-primary:not(:disabled):active, .fc .fc-button-primary:not(:disabled).fc-button-active {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
    }
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
        padding: 2px 4px;
        font-size: 0.8rem;
        transition: transform 0.2s;
    }
    .fc-event:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    [data-theme="dark"] .fc-daygrid-day-number { color: var(--text-main); }
    [data-theme="dark"] .fc-col-header-cell-cushion { color: var(--text-main); }
</style>

<div class="main-content">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
            <div>
                <h4 class="fw-bold mb-0">Calendar View</h4>
                <p class="text-muted small mb-0">View all upcoming deadlines and schedules.</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-danger">Urgent</span>
                <span class="badge bg-warning text-dark">High</span>
                <span class="badge bg-primary">Medium</span>
                <span class="badge bg-success">Completed</span>
            </div>
        </div>

        <div class="card-premium p-4" data-aos="zoom-in" data-aos-delay="100">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="backdrop-filter: blur(5px);">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <span id="modalStatus" class="badge bg-secondary me-2"></span>
                    <span id="modalPriority" class="badge bg-info"></span>
                </div>
                <h6 class="text-muted mb-4"><i class='bx bx-calendar me-1'></i> <span id="modalDate"></span></h6>
                <a href="#" id="modalLink" class="btn btn-primary-gradient w-100">View Full Details</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    // Initialize FullCalendar
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 700,
        events: 'calendar.php?action=get_events',
        eventClick: function(info) {
            info.jsEvent.preventDefault(); // don't let the browser navigate
            
            // Populate Modal
            document.getElementById('modalTitle').innerText = info.event.title;
            document.getElementById('modalStatus').innerText = info.event.extendedProps.status;
            document.getElementById('modalPriority').innerText = info.event.extendedProps.priority + ' Priority';
            
            // Format date string nicely
            const dateStr = info.event.start.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('modalDate').innerText = 'Deadline: ' + dateStr;
            
            // Update link
            document.getElementById('modalLink').href = 'edit_task.php?id=' + info.event.id;
            
            // Show Modal
            var myModal = new bootstrap.Modal(document.getElementById('eventModal'));
            myModal.show();
        }
    });
    
    calendar.render();

    // Ensure calendar handles theme toggles properly
    document.getElementById('theme-toggle').addEventListener('click', () => {
        setTimeout(() => { calendar.render(); }, 200);
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
