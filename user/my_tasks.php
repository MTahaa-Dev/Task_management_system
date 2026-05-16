<?php
$required_role = 'user';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$userId = $_SESSION['user_id'];
$taskId = isset($_GET['view']) ? (int)$_GET['view'] : 0;

if ($taskId > 0) {
    // Detail View Logic
    $stmt = $pdo->prepare("SELECT t.*, u.full_name as creator FROM tasks t JOIN users u ON t.created_by = u.id WHERE t.id = ? AND (t.assigned_to = ? OR t.assigned_to IS NULL)");
    $stmt->execute([$taskId, $userId]);
    $task = $stmt->fetch();
    
    if (!$task) {
        $_SESSION['flash_error'] = "Task not found or you don't have permission to view it.";
        header("Location: my_tasks.php");
        exit();
    }
    
    // Handle Updates (Status, Comments, Attachment)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if (!validate_csrf_token($_POST['csrf_token'])) {
            $_SESSION['flash_error'] = "Invalid request token.";
        } else {
            $action = $_POST['action'];
            
            if ($action === 'update_status') {
                $status = $_POST['status'];
                $progress = (int)$_POST['progress'];
                if ($status === 'Completed') $progress = 100;
                
                $upStmt = $pdo->prepare("UPDATE tasks SET status = ?, progress = ? WHERE id = ?");
                $upStmt->execute([$status, $progress, $taskId]);
                
                if ($status === 'Completed' && $task['status'] !== 'Completed') {
                    $_SESSION['trigger_confetti'] = true;
                }
                
                log_activity($pdo, $userId, 'Updated task status', "Task ID: $taskId to $status");
                $_SESSION['flash_success'] = "Task status updated.";
                header("Location: my_tasks.php?view=$taskId");
                exit();
                
            } elseif ($action === 'add_comment') {
                $comment = trim(sanitize($_POST['comment']));
                if (!empty($comment)) {
                    $cStmt = $pdo->prepare("INSERT INTO task_comments (task_id, user_id, comment) VALUES (?, ?, ?)");
                    $cStmt->execute([$taskId, $userId, $comment]);
                    log_activity($pdo, $userId, 'Added comment', "Task ID: $taskId");
                    $_SESSION['flash_success'] = "Comment added.";
                }
                header("Location: my_tasks.php?view=$taskId");
                exit();
                
            } elseif ($action === 'upload_attachment') {
                if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['attachment'];
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('task_') . '.' . $ext;
                    $targetDir = __DIR__ . '/../assets/uploads/task_files/';
                    if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                    if (move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
                        $path = 'assets/uploads/task_files/' . $filename;
                        $pdo->prepare("UPDATE tasks SET attachment = ? WHERE id = ?")->execute([$path, $taskId]);
                        $_SESSION['flash_success'] = "Attachment uploaded.";
                    }
                }
                header("Location: my_tasks.php?view=$taskId");
                exit();
            }
        }
    }
    
    // Fetch Comments
    $comments = $pdo->prepare("SELECT c.*, u.full_name, u.profile_picture FROM task_comments c JOIN users u ON c.user_id = u.id WHERE c.task_id = ? ORDER BY c.created_at ASC");
    $comments->execute([$taskId]);
    $commentsData = $comments->fetchAll();
    
    // Fetch Checklists
    $checklists = $pdo->prepare("SELECT * FROM task_checklists WHERE task_id = ?")->execute([$taskId]);
    $checklistsData = $pdo->prepare("SELECT * FROM task_checklists WHERE task_id = ?");
    $checklistsData->execute([$taskId]);
    $clItems = $checklistsData->fetchAll();

} else {
    // List View Logic
    $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
    $where = "(assigned_to = $userId OR assigned_to IS NULL)";
    if ($statusFilter) {
        $where .= " AND status = " . $pdo->quote($statusFilter);
    }
    
    $tasks = $pdo->query("SELECT * FROM tasks WHERE $where ORDER BY deadline ASC")->fetchAll();
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid p-4" style="max-width: 1100px;">
        
        <?php if($taskId > 0): ?>
            <!-- DETAIL VIEW -->
            <div class="d-flex align-items-center mb-4" data-aos="fade-down">
                <a href="my_tasks.php" class="btn btn-light rounded-circle shadow-sm me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class='bx bx-arrow-back fs-5'></i></a>
                <div>
                    <h4 class="fw-bold mb-0">Task Details</h4>
                    <p class="text-muted small mb-0">View info and update your progress.</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8" data-aos="fade-right">
                    <!-- Task Info Card -->
                    <div class="card-premium p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($task['title']); ?></h5>
                            <div>
                                <?php echo priority_badge($task['priority']); ?>
                                <?php echo status_badge($task['status']); ?>
                            </div>
                        </div>
                        
                        <div class="text-muted small mb-4 d-flex gap-4">
                            <span><i class='bx bx-user me-1'></i> From: <?php echo htmlspecialchars($task['creator']); ?></span>
                            <span><i class='bx bx-calendar me-1'></i> Due: <?php echo $task['deadline'] ? format_date($task['deadline']) : 'No deadline'; ?></span>
                            <?php if($task['category']): ?>
                                <span><i class='bx bx-purchase-tag me-1'></i> <?php echo htmlspecialchars($task['category']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="bg-light p-3 rounded mb-4 text-dark" style="font-size: 0.95rem; white-space: pre-wrap;"><?php echo htmlspecialchars($task['description'] ?: 'No description provided.'); ?></div>
                        
                        <!-- Checklists Readonly -->
                        <?php if(!empty($clItems)): ?>
                            <h6 class="fw-bold small text-muted text-uppercase mb-2">Checklist</h6>
                            <ul class="list-group mb-4">
                                <?php foreach($clItems as $cl): ?>
                                    <li class="list-group-item bg-transparent px-2 py-1 border-0">
                                        <i class='bx <?php echo $cl['is_completed'] ? 'bx-check-square text-success' : 'bx-square text-muted'; ?> me-2'></i>
                                        <span class="<?php echo $cl['is_completed'] ? 'text-decoration-line-through text-muted' : ''; ?>"><?php echo htmlspecialchars($cl['title']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <!-- Attachment -->
                        <h6 class="fw-bold small text-muted text-uppercase mb-2">Attachment</h6>
                        <?php if($task['attachment']): ?>
                            <div class="d-flex align-items-center p-2 border rounded bg-light mb-3">
                                <i class='bx bx-file fs-4 text-primary me-2'></i>
                                <a href="/demo/<?php echo htmlspecialchars($task['attachment']); ?>" target="_blank" class="text-decoration-none text-truncate flex-grow-1"><?php echo basename($task['attachment']); ?></a>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted mb-3">No attachment.</p>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data" class="d-flex gap-2">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="action" value="upload_attachment">
                            <input type="file" name="attachment" class="form-control form-control-sm" required>
                            <button class="btn btn-sm btn-outline-primary" type="submit">Upload</button>
                        </form>
                    </div>

                    <!-- Comments Section -->
                    <div class="card-premium p-4">
                        <h6 class="fw-bold mb-4"><i class='bx bx-message-square-dots me-2 text-primary'></i>Discussion</h6>
                        
                        <div class="comments-list mb-4 custom-scrollbar" style="max-height: 400px; overflow-y: auto;">
                            <?php if(empty($commentsData)): ?>
                                <p class="text-muted small text-center my-4">No comments yet. Start the conversation!</p>
                            <?php else: ?>
                                <?php foreach($commentsData as $comment): ?>
                                    <div class="d-flex mb-3 <?php echo $comment['user_id'] == $userId ? 'flex-row-reverse text-end' : ''; ?>">
                                        <img src="/demo/<?php echo htmlspecialchars(get_profile_picture($comment['profile_picture'])); ?>" class="rounded-circle object-fit-cover mx-2 shadow-sm" width="35" height="35">
                                        <div class="p-3 rounded <?php echo $comment['user_id'] == $userId ? 'bg-primary text-white' : 'bg-light'; ?>" style="max-width: 75%;">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small class="fw-bold <?php echo $comment['user_id'] == $userId ? 'text-white' : 'text-primary'; ?>"><?php echo htmlspecialchars($comment['full_name']); ?></small>
                                            </div>
                                            <p class="mb-1 small" style="white-space: pre-wrap;"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                            <small style="font-size: 0.65rem; opacity: 0.7;"><?php echo format_date($comment['created_at'], 'M j, g:i A'); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <form method="POST" action="">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="action" value="add_comment">
                            <div class="input-group">
                                <input type="text" name="comment" class="form-control" placeholder="Type your comment..." required>
                                <button class="btn btn-primary" type="submit"><i class='bx bx-send'></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-left">
                    <!-- Update Progress Card -->
                    <div class="card-premium p-4 sticky-top" style="top: 100px;">
                        <h6 class="fw-bold text-success mb-4"><i class='bx bx-refresh me-2'></i>Update Progress</h6>
                        
                        <form method="POST" action="">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="action" value="update_status">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Pending" <?php echo $task['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="In Progress" <?php echo $task['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Review" <?php echo $task['status'] == 'Review' ? 'selected' : ''; ?>>Review</option>
                                    <option value="Completed" <?php echo $task['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted d-flex justify-content-between">
                                    <span>Progress (%)</span>
                                    <span id="progVal"><?php echo $task['progress']; ?>%</span>
                                </label>
                                <input type="range" name="progress" class="form-range" min="0" max="100" step="5" value="<?php echo $task['progress']; ?>" oninput="document.getElementById('progVal').innerText = this.value + '%'">
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">Update Task</button>
                        </form>
                    </div>
                </div>
            </div>

            <?php if(isset($_SESSION['trigger_confetti'])): ?>
            <script>document.addEventListener('DOMContentLoaded', fireConfetti);</script>
            <?php unset($_SESSION['trigger_confetti']); endif; ?>

        <?php else: ?>
            <!-- LIST VIEW -->
            <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
                <div>
                    <h4 class="fw-bold mb-0">My Tasks</h4>
                    <p class="text-muted small mb-0">Manage tasks assigned specifically to you.</p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class='bx bx-filter-alt me-1'></i> Filter
                    </button>
                    <ul class="dropdown-menu shadow border-0">
                        <li><a class="dropdown-item" href="my_tasks.php">All Tasks</a></li>
                        <li><a class="dropdown-item" href="?status=Pending">Pending</a></li>
                        <li><a class="dropdown-item" href="?status=In Progress">In Progress</a></li>
                        <li><a class="dropdown-item" href="?status=Completed">Completed</a></li>
                    </ul>
                </div>
            </div>

            <div class="row g-4">
                <?php if(empty($tasks)): ?>
                    <div class="col-12 text-center py-5" data-aos="fade-up">
                        <i class='bx bx-party text-muted mb-3' style="font-size: 5rem; opacity: 0.5;"></i>
                        <h5 class="fw-bold text-muted">You're all caught up!</h5>
                        <p class="text-muted small">You don't have any tasks assigned at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($tasks as $t): ?>
                        <div class="col-md-6 col-xl-4" data-aos="fade-up">
                            <div class="card-premium h-100 p-4 position-relative">
                                <?php if($t['deadline'] && $t['deadline'] < date('Y-m-d') && $t['status'] != 'Completed'): ?>
                                    <div class="position-absolute top-0 end-0 bg-danger text-white px-2 py-1 small rounded-bl shadow-sm" style="font-size: 0.65rem; border-bottom-left-radius: 8px;">OVERDUE</div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <?php echo priority_badge($t['priority']); ?>
                                    <?php echo status_badge($t['status']); ?>
                                </div>
                                
                                <h6 class="fw-bold mb-3 mt-2"><a href="?view=<?php echo $t['id']; ?>" class="text-dark text-decoration-none hover-primary"><?php echo htmlspecialchars($t['title']); ?></a></h6>
                                
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar <?php echo $t['progress'] == 100 ? 'bg-success' : 'bg-primary'; ?>" style="width: <?php echo $t['progress']; ?>%;"></div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-3">
                                    <span class="small text-muted"><i class='bx bx-calendar me-1'></i><?php echo $t['deadline'] ? format_date($t['deadline']) : 'None'; ?></span>
                                    <a href="?view=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">View</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
