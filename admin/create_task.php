<?php
$required_role = 'admin';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Fetch users for assignment
$users = $pdo->query("SELECT id, full_name, username FROM users WHERE status = 'active' ORDER BY full_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['flash_error'] = "Invalid request token.";
    } else {
        $title = trim(sanitize($_POST['title']));
        $description = trim(sanitize($_POST['description'] ?? ''));
        $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
        $priority = in_array($_POST['priority'], ['Low', 'Medium', 'High', 'Urgent']) ? $_POST['priority'] : 'Medium';
        $status = 'Pending'; // New tasks are always pending
        $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
        $category = trim(sanitize($_POST['category'] ?? ''));
        $estimatedTime = trim(sanitize($_POST['estimated_time'] ?? ''));
        
        $createdBy = $_SESSION['user_id'];
        
        if (empty($title)) {
            $_SESSION['flash_error'] = "Task title is required.";
        } else {
            // Handle file upload
            $attachmentPath = null;
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['attachment'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!in_array($file['type'], $allowedTypes)) {
                    $_SESSION['flash_error'] = "Invalid file type. Allowed: JPG, PNG, GIF, PDF, DOCX.";
                } elseif ($file['size'] > $maxSize) {
                    $_SESSION['flash_error'] = "File exceeds 5MB limit.";
                } else {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('task_') . '_' . time() . '.' . $ext;
                    $targetDir = __DIR__ . '/../assets/uploads/task_files/';
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                    
                    if (move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
                        $attachmentPath = 'assets/uploads/task_files/' . $filename;
                    }
                }
            }

            if (!isset($_SESSION['flash_error'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO tasks (title, description, assigned_to, created_by, priority, status, deadline, category, attachment, estimated_time) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$title, $description, $assignedTo, $createdBy, $priority, $status, $deadline, $category, $attachmentPath, $estimatedTime])) {
                    $taskId = $pdo->lastInsertId();
                    log_activity($pdo, $createdBy, 'Created a new task', "Task ID: $taskId");
                    
                    if ($assignedTo) {
                        create_notification($pdo, $assignedTo, "You have been assigned a new task: " . mb_strimwidth($title, 0, 30, '...'), 'assignment');
                    }
                    
                    $_SESSION['flash_success'] = "Task created successfully!";
                    header("Location: tasks.php");
                    exit();
                } else {
                    $_SESSION['flash_error'] = "Failed to create task.";
                }
            }
        }
        
        // If coming from quick add modal and it failed
        if(isset($_POST['is_quick_add'])) {
            header("Location: tasks.php");
            exit();
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid p-4" style="max-width: 1000px;">
        
        <div class="d-flex align-items-center mb-4" data-aos="fade-right">
            <a href="tasks.php" class="btn btn-light rounded-circle shadow-sm me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class='bx bx-arrow-back fs-5'></i></a>
            <div>
                <h4 class="fw-bold mb-0">Create New Task</h4>
                <p class="text-muted small mb-0">Provide detailed information for the new task.</p>
            </div>
        </div>

        <form method="POST" action="" enctype="multipart/form-data">
            <?php csrf_field(); ?>
            <div class="row g-4">
                <!-- Main Task Info -->
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="card-premium p-4 h-100">
                        <h6 class="fw-bold mb-4 text-primary"><i class='bx bx-info-circle me-2'></i>General Information</h6>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Task Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg" placeholder="e.g., Design new landing page" required autofocus>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Detailed Description</label>
                            <textarea name="description" class="form-control" rows="6" placeholder="Provide requirements and context..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">File Attachment</label>
                            <div class="input-group">
                                <input type="file" name="attachment" class="form-control" id="taskAttachment" aria-describedby="fileHelp">
                                <label class="input-group-text" for="taskAttachment"><i class='bx bx-cloud-upload'></i></label>
                            </div>
                            <div id="fileHelp" class="form-text small">Allowed: JPG, PNG, GIF, PDF, DOCX (Max 5MB)</div>
                        </div>
                    </div>
                </div>

                <!-- Meta Info Sidebar -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-premium p-4 h-100">
                        <h6 class="fw-bold mb-4 text-primary"><i class='bx bx-slider-alt me-2'></i>Task Settings</h6>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Assign To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">All Users</option>
                                <?php foreach($users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['full_name']); ?> (@<?php echo htmlspecialchars($u['username']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>

                        <!-- Status field removed. All new tasks are implicitly 'Pending' -->
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Deadline</label>
                            <input type="date" name="deadline" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Category Label</label>
                            <input type="text" name="category" class="form-control" placeholder="e.g., Frontend, Marketing, Bug">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Estimated Time</label>
                            <input type="text" name="estimated_time" class="form-control" placeholder="e.g., 4 Hours, 2 Days">
                        </div>
                        
                        <button type="submit" class="btn btn-primary-gradient w-100 py-2 mt-2">Create Task</button>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
