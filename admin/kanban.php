<?php
$required_role = 'admin';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Handle AJAX Request to Update Task Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_update_status'])) {
    header('Content-Type: application/json');
    $taskId = (int)$_POST['task_id'];
    $newStatus = $_POST['new_status'];
    
    // Validate status
    $validStatuses = ['Pending', 'In Progress', 'Review', 'Completed'];
    if (in_array($newStatus, $validStatuses)) {
        $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        if ($stmt->execute([$newStatus, $taskId])) {
            log_activity($pdo, $_SESSION['user_id'], "Moved task to $newStatus via Kanban", "Task ID: $taskId");
            echo json_encode(['success' => true]);
            exit;
        }
    }
    echo json_encode(['success' => false]);
    exit;
}

// Fetch all active tasks
$tasksStmt = $pdo->query("
    SELECT t.*, u.full_name, u.profile_picture 
    FROM tasks t 
    LEFT JOIN users u ON t.assigned_to = u.id 
    ORDER BY t.priority DESC, t.deadline ASC
");
$allTasks = $tasksStmt->fetchAll();

// Group tasks by status
$kanbanTasks = [
    'Pending' => [],
    'In Progress' => [],
    'Review' => [],
    'Completed' => []
];

foreach ($allTasks as $task) {
    if (array_key_exists($task['status'], $kanbanTasks)) {
        $kanbanTasks[$task['status']][] = $task;
    }
}

// Function to render a card
function renderKanbanCard($task) {
    $avatar = get_profile_picture($task['profile_picture']);
    $priorityColor = [
        'Low' => 'info',
        'Medium' => 'success',
        'High' => 'warning',
        'Urgent' => 'danger'
    ][$task['priority']] ?? 'secondary';
    
    $dl = $task['deadline'] ? '<small class="text-muted"><i class="bx bx-time-five"></i> ' . date('M j', strtotime($task['deadline'])) . '</small>' : '';
    
    return "
    <div class='kanban-card card-premium p-3 mb-3 cursor-grab' data-id='{$task['id']}'>
        <div class='d-flex justify-content-between mb-2'>
            <span class='badge bg-{$priorityColor} text-white' style='font-size:0.6rem;'>{$task['priority']}</span>
            $dl
        </div>
        <h6 class='fw-bold mb-2 fs-6'>" . htmlspecialchars($task['title']) . "</h6>
        <div class='progress mb-2' style='height: 4px;'>
            <div class='progress-bar bg-primary' style='width: {$task['progress']}%;'></div>
        </div>
        <div class='d-flex justify-content-between align-items-center mt-3'>
            <div class='d-flex align-items-center'>
                <img src='/demo/" . htmlspecialchars($avatar) . "' class='rounded-circle object-fit-cover shadow-sm' width='24' height='24' title='" . htmlspecialchars($task['full_name'] ?? 'Unassigned') . "'>
            </div>
            <a href='edit_task.php?id={$task['id']}' class='text-muted hover-primary transition-fast'><i class='bx bx-edit-alt'></i></a>
        </div>
    </div>
    ";
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<style>
    .kanban-board {
        display: flex;
        gap: 1.5rem;
        overflow-x: auto;
        padding-bottom: 1rem;
        min-height: calc(100vh - 150px);
    }
    .kanban-column {
        flex: 0 0 320px;
        background: rgba(0,0,0,0.02);
        border-radius: var(--radius-lg);
        padding: 1rem;
        display: flex;
        flex-direction: column;
    }
    [data-theme="dark"] .kanban-column {
        background: rgba(255,255,255,0.02);
    }
    .kanban-column-header {
        font-weight: bold;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .kanban-tasks {
        flex: 1;
        min-height: 200px;
    }
    .cursor-grab {
        cursor: grab;
    }
    .cursor-grab:active {
        cursor: grabbing;
    }
    .sortable-ghost {
        opacity: 0.4;
        background-color: var(--primary-color) !important;
    }
    .sortable-drag {
        box-shadow: var(--shadow-lg);
        transform: scale(1.05);
    }
</style>

<div class="main-content">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
            <div>
                <h4 class="fw-bold mb-0">Kanban Board</h4>
                <p class="text-muted small mb-0">Drag and drop tasks to update their status instantly.</p>
            </div>
            <button class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#quickAddModal">
                <i class='bx bx-plus'></i> Add Task
            </button>
        </div>

        <div class="kanban-board custom-scrollbar" data-aos="fade-up" data-aos-delay="100">
            
            <!-- Pending Column -->
            <div class="kanban-column">
                <div class="kanban-column-header border-secondary text-secondary">
                    <span><i class='bx bx-loader-circle me-1'></i> Pending</span>
                    <span class="badge bg-secondary rounded-pill"><?php echo count($kanbanTasks['Pending']); ?></span>
                </div>
                <div class="kanban-tasks" id="col-Pending" data-status="Pending">
                    <?php foreach($kanbanTasks['Pending'] as $task) echo renderKanbanCard($task); ?>
                </div>
            </div>

            <!-- In Progress Column -->
            <div class="kanban-column">
                <div class="kanban-column-header border-primary text-primary">
                    <span><i class='bx bx-pulse me-1'></i> In Progress</span>
                    <span class="badge bg-primary rounded-pill"><?php echo count($kanbanTasks['In Progress']); ?></span>
                </div>
                <div class="kanban-tasks" id="col-InProgress" data-status="In Progress">
                    <?php foreach($kanbanTasks['In Progress'] as $task) echo renderKanbanCard($task); ?>
                </div>
            </div>

            <!-- Review Column -->
            <div class="kanban-column">
                <div class="kanban-column-header border-warning text-warning">
                    <span><i class='bx bx-search-alt-2 me-1'></i> Review</span>
                    <span class="badge bg-warning text-dark rounded-pill"><?php echo count($kanbanTasks['Review']); ?></span>
                </div>
                <div class="kanban-tasks" id="col-Review" data-status="Review">
                    <?php foreach($kanbanTasks['Review'] as $task) echo renderKanbanCard($task); ?>
                </div>
            </div>

            <!-- Completed Column -->
            <div class="kanban-column">
                <div class="kanban-column-header border-success text-success">
                    <span><i class='bx bx-check-double me-1'></i> Completed</span>
                    <span class="badge bg-success rounded-pill"><?php echo count($kanbanTasks['Completed']); ?></span>
                </div>
                <div class="kanban-tasks" id="col-Completed" data-status="Completed">
                    <?php foreach($kanbanTasks['Completed'] as $task) echo renderKanbanCard($task); ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const columns = document.querySelectorAll('.kanban-tasks');
    
    columns.forEach(col => {
        new Sortable(col, {
            group: 'kanban', // set both lists to same group
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function (evt) {
                const itemEl = evt.item;  // dragged HTMLElement
                const toList = evt.to;    // target list
                
                const taskId = itemEl.getAttribute('data-id');
                const newStatus = toList.getAttribute('data-status');
                
                // Only send AJAX if moved to a different column
                if (evt.from !== evt.to) {
                    updateTaskStatus(taskId, newStatus);
                    updateBadges();
                }
            },
        });
    });

    function updateTaskStatus(taskId, newStatus) {
        const formData = new FormData();
        formData.append('ajax_update_status', '1');
        formData.append('task_id', taskId);
        formData.append('new_status', newStatus);

        fetch('kanban.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showToast("Task moved to " + newStatus, "success");
                if(newStatus === 'Completed') {
                    fireConfetti();
                }
            } else {
                showToast("Failed to move task", "error");
            }
        });
    }

    function updateBadges() {
        columns.forEach(col => {
            const count = col.querySelectorAll('.kanban-card').length;
            col.parentElement.querySelector('.badge').innerText = count;
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
