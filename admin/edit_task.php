<?php
$required_role = 'admin';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$taskId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Task
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$taskId]);
$task = $stmt->fetch();

if (!$task) {
    $_SESSION['flash_error'] = "Task not found.";
    header("Location: tasks.php");
    exit();
}

// Handle Checklists Actions (Add/Toggle/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['flash_error'] = "Invalid request token.";
    } else {
        $action = $_POST['action'];
        
        if ($action === 'add_checklist') {
            $clTitle = trim(sanitize($_POST['checklist_title']));
            if (!empty($clTitle)) {
                $clStmt = $pdo->prepare("INSERT INTO task_checklists (task_id, title) VALUES (?, ?)");
                $clStmt->execute([$taskId, $clTitle]);
                updateTaskProgress($pdo, $taskId);
                $_SESSION['flash_success'] = "Subtask added.";
            }
        } elseif ($action === 'toggle_checklist') {
            $clId = (int)$_POST['checklist_id'];
            $isCompleted = isset($_POST['is_completed']) ? 1 : 0;
            $clStmt = $pdo->prepare("UPDATE task_checklists SET is_completed = ? WHERE id = ? AND task_id = ?");
            $clStmt->execute([$isCompleted, $clId, $taskId]);
            updateTaskProgress($pdo, $taskId);
        } elseif ($action === 'delete_checklist') {
            $clId = (int)$_POST['checklist_id'];
            $clStmt = $pdo->prepare("DELETE FROM task_checklists WHERE id = ? AND task_id = ?");
            $clStmt->execute([$clId, $taskId]);
            updateTaskProgress($pdo, $taskId);
            $_SESSION['flash_success'] = "Subtask deleted.";
        } elseif ($action === 'update_task') {
            $title = trim(sanitize($_POST['title']));
            $description = trim(sanitize($_POST['description'] ?? ''));
            $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
            $priority = $_POST['priority'];
            $status = $_POST['status'];
            $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
            $category = trim(sanitize($_POST['category'] ?? ''));
            $estimatedTime = trim(sanitize($_POST['estimated_time'] ?? ''));
            
            // File upload logic
            $attachmentPath = $task['attachment'];
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                // simple validation
                $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('task_') . '.' . $ext;
                $targetDir = __DIR__ . '/../assets/uploads/task_files/';
                if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetDir . $filename)) {
                    $attachmentPath = 'assets/uploads/task_files/' . $filename;
                }
            }
            
            $upStmt = $pdo->prepare("UPDATE tasks SET title=?, description=?, assigned_to=?, priority=?, status=?, deadline=?, category=?, estimated_time=?, attachment=? WHERE id=?");
            if ($upStmt->execute([$title, $description, $assignedTo, $priority, $status, $deadline, $category, $estimatedTime, $attachmentPath, $taskId])) {
                log_activity($pdo, $_SESSION['user_id'], 'Updated a task', "Task ID: $taskId");
                // Check if marked as completed to trigger confetti on reload via session flag
                if($status === 'Completed' && $task['status'] !== 'Completed') {
                    $_SESSION['trigger_confetti'] = true;
                }
                $_SESSION['flash_success'] = "Task updated successfully.";
            } else {
                $_SESSION['flash_error'] = "Failed to update task.";
            }
        }
        
        header("Location: edit_task.php?id=" . $taskId);
        exit();
    }
}

// Function to update progress based on checklist
function updateTaskProgress($pdo, $taskId) {
    $total = $pdo->prepare("SELECT COUNT(*) FROM task_checklists WHERE task_id = ?");
    $total->execute([$taskId]);
    $totalCount = $total->fetchColumn();
    
    if ($totalCount > 0) {
        $completed = $pdo->prepare("SELECT COUNT(*) FROM task_checklists WHERE task_id = ? AND is_completed = 1");
        $completed->execute([$taskId]);
        $completedCount = $completed->fetchColumn();
        
        $progress = round(($completedCount / $totalCount) * 100);
    } else {
        // If no subtasks, check status
        $status = $pdo->prepare("SELECT status FROM tasks WHERE id = ?");
        $status->execute([$taskId]);
        $currentStatus = $status->fetchColumn();
        $progress = ($currentStatus === 'Completed') ? 100 : 0;
    }
    
    $upd = $pdo->prepare("UPDATE tasks SET progress = ? WHERE id = ?");
    $upd->execute([$progress, $taskId]);
}

// Refresh task data after updates
$stmt->execute([$taskId]);
$task = $stmt->fetch();

// Fetch users
$users = $pdo->query("SELECT id, full_name, username FROM users WHERE status = 'active' ORDER BY full_name")->fetchAll();

// Fetch checklists
$clStmt = $pdo->prepare("SELECT * FROM task_checklists WHERE task_id = ? ORDER BY id ASC");
$clStmt->execute([$taskId]);
$checklists = $clStmt->fetchAll();

?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid p-4" style="max-width: 1200px;">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3" data-aos="fade-down">
            <div class="d-flex align-items-center">
                <a href="tasks.php" class="btn btn-light rounded-circle shadow-sm me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class='bx bx-arrow-back fs-5'></i></a>
                <div>
                    <h4 class="fw-bold mb-0">Edit Task #<?php echo $taskId; ?></h4>
                    <p class="text-muted small mb-0">Update details and manage subtasks.</p>
                </div>
            </div>
            <div>
                <?php echo status_badge($task['status']); ?>
                <?php echo priority_badge($task['priority']); ?>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Form -->
            <div class="col-lg-8" data-aos="fade-right">
                <form method="POST" action="" enctype="multipart/form-data" class="card-premium p-4 h-100">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="update_task">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-primary mb-0"><i class='bx bx-edit me-2'></i>Task Details</h6>
                        <span class="badge bg-light border text-dark">Progress: <?php echo $task['progress']; ?>%</span>
                    </div>

                    <div class="progress mb-4" style="height: 8px; border-radius: 10px;">
                        <div class="progress-bar <?php echo $task['progress'] == 100 ? 'bg-success' : 'bg-primary'; ?> progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $task['progress']; ?>%;"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Task Title</label>
                        <input type="text" name="title" class="form-control form-control-lg" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Detailed Description</label>
                        <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($task['description']); ?></textarea>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Assign To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">All Users</option>
                                <?php foreach($users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>" <?php echo $task['assigned_to'] == $u['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Status</label>
                            <select name="status" class="form-select">
                                <option value="Pending" <?php echo $task['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" <?php echo $task['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Review" <?php echo $task['status'] == 'Review' ? 'selected' : ''; ?>>Review</option>
                                <option value="Completed" <?php echo $task['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="Low" <?php echo $task['priority'] == 'Low' ? 'selected' : ''; ?>>Low</option>
                                <option value="Medium" <?php echo $task['priority'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="High" <?php echo $task['priority'] == 'High' ? 'selected' : ''; ?>>High</option>
                                <option value="Urgent" <?php echo $task['priority'] == 'Urgent' ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Deadline</label>
                            <input type="date" name="deadline" class="form-control" value="<?php echo htmlspecialchars($task['deadline']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Category Label</label>
                            <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($task['category']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Estimated Time</label>
                            <input type="text" name="estimated_time" class="form-control" value="<?php echo htmlspecialchars($task['estimated_time']); ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Current Attachment</label>
                        <?php if($task['attachment']): ?>
                            <div class="d-flex align-items-center p-2 border rounded bg-light">
                                <i class='bx bx-file fs-4 text-primary me-2'></i>
                                <a href="/demo/<?php echo htmlspecialchars($task['attachment']); ?>" target="_blank" class="text-decoration-none text-truncate flex-grow-1"><?php echo basename($task['attachment']); ?></a>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small">No attachment.</p>
                        <?php endif; ?>
                        
                        <label class="form-label small fw-bold text-muted mt-3">Upload New Attachment (Replaces existing)</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary-gradient px-5"><i class='bx bx-save me-1'></i> Save Changes</button>
                </form>
            </div>

            <!-- Checklists Sidebar -->
            <div class="col-lg-4" data-aos="fade-left">
                <div class="card-premium p-4 h-100">
                    <h6 class="fw-bold mb-4 text-primary"><i class='bx bx-list-check me-2'></i>Checklist / Subtasks</h6>
                    
                    <ul class="list-group list-group-flush mb-4">
                        <?php if(empty($checklists)): ?>
                            <li class="list-group-item text-center text-muted py-4 border-0">
                                <i class='bx bx-list-minus fs-1 mb-2'></i><br>No subtasks added yet.
                            </li>
                        <?php else: ?>
                            <?php foreach($checklists as $cl): ?>
                                <li class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">
                                    <form method="POST" action="" class="d-flex align-items-center w-100">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="action" value="toggle_checklist">
                                        <input type="hidden" name="checklist_id" value="<?php echo $cl['id']; ?>">
                                        <div class="form-check m-0 d-flex align-items-center">
                                            <input class="form-check-input me-2" type="checkbox" name="is_completed" value="1" onchange="this.form.submit()" <?php echo $cl['is_completed'] ? 'checked' : ''; ?> style="width: 1.2rem; height: 1.2rem; cursor: pointer;">
                                            <label class="form-check-label <?php echo $cl['is_completed'] ? 'text-decoration-line-through text-muted' : ''; ?>" style="cursor: pointer;"><?php echo htmlspecialchars($cl['title']); ?></label>
                                        </div>
                                    </form>
                                    <form method="POST" action="">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="action" value="delete_checklist">
                                        <input type="hidden" name="checklist_id" value="<?php echo $cl['id']; ?>">
                                        <button type="submit" class="btn btn-link text-danger p-0 ms-2" title="Remove"><i class='bx bx-x fs-5'></i></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>

                    <form method="POST" action="">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="add_checklist">
                        <div class="input-group">
                            <input type="text" name="checklist_title" class="form-control form-control-sm border-end-0" placeholder="New subtask..." required>
                            <button class="btn btn-outline-primary border-start-0" type="submit"><i class='bx bx-plus'></i></button>
                        </div>
                    </form>

                    <?php if($task['deadline']): ?>
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3 text-primary"><i class='bx bx-timer me-2'></i>Deadline Timer</h6>
                    <div id="countdown" class="d-flex justify-content-between text-center gap-2">
                        <div class="bg-light rounded p-2 flex-grow-1 border"><h4 class="fw-bold mb-0 text-dark" id="cd-days">--</h4><small class="text-muted" style="font-size:0.65rem;">DAYS</small></div>
                        <div class="bg-light rounded p-2 flex-grow-1 border"><h4 class="fw-bold mb-0 text-dark" id="cd-hours">--</h4><small class="text-muted" style="font-size:0.65rem;">HRS</small></div>
                        <div class="bg-light rounded p-2 flex-grow-1 border"><h4 class="fw-bold mb-0 text-dark" id="cd-mins">--</h4><small class="text-muted" style="font-size:0.65rem;">MIN</small></div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<?php if(isset($_SESSION['trigger_confetti'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fireConfetti();
    });
</script>
<?php unset($_SESSION['trigger_confetti']); endif; ?>

<?php if($task['deadline']): ?>
<script>
// Countdown Timer Logic
document.addEventListener('DOMContentLoaded', function() {
    const deadlineStr = "<?php echo $task['deadline']; ?>T23:59:59";
    const countDownDate = new Date(deadlineStr).getTime();

    const x = setInterval(function() {
        const now = new Date().getTime();
        const distance = countDownDate - now;

        if (distance < 0) {
            clearInterval(x);
            document.getElementById("countdown").innerHTML = "<div class='alert alert-danger w-100 py-2 m-0 text-center fw-bold'>OVERDUE</div>";
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

        document.getElementById("cd-days").innerText = days;
        document.getElementById("cd-hours").innerText = hours;
        document.getElementById("cd-mins").innerText = minutes;
    }, 1000);
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
