<?php
$required_role = 'admin';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Handle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (validate_csrf_token($_POST['csrf_token'])) {
        $taskId = (int)$_POST['task_id'];
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        if ($stmt->execute([$taskId])) {
            log_activity($pdo, $_SESSION['user_id'], 'Deleted a task', "Task ID: $taskId");
            $_SESSION['flash_success'] = "Task deleted successfully.";
        } else {
            $_SESSION['flash_error'] = "Failed to delete task.";
        }
        header("Location: tasks.php");
        exit();
    }
}

// Handle Export to CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=tasks_export_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Title', 'Priority', 'Status', 'Category', 'Deadline', 'Progress', 'Created At']);
    
    $exportStmt = $pdo->query("SELECT id, title, priority, status, category, deadline, progress, created_at FROM tasks ORDER BY id DESC");
    while ($row = $exportStmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Filter and Search Logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterPriority = isset($_GET['priority']) ? $_GET['priority'] : '';
$filterCategory = isset($_GET['category']) ? $_GET['category'] : '';

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(t.title LIKE ? OR t.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filterStatus !== '') {
    $where[] = "t.status = ?";
    $params[] = $filterStatus;
}
if ($filterPriority !== '') {
    $where[] = "t.priority = ?";
    $params[] = $filterPriority;
}
if ($filterCategory !== '') {
    $where[] = "t.category = ?";
    $params[] = $filterCategory;
}

$whereClause = implode(" AND ", $where);

// Pagination
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count Total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM tasks t WHERE $whereClause");
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch Tasks
$query = "SELECT t.*, u.full_name as assignee, u.profile_picture 
          FROM tasks t 
          LEFT JOIN users u ON t.assigned_to = u.id 
          WHERE $whereClause 
          ORDER BY t.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Fetch distinct categories for filter
$categories = $pdo->query("SELECT DISTINCT category FROM tasks WHERE category IS NOT NULL AND category != ''")->fetchAll(PDO::FETCH_COLUMN);

// Build query string for pagination links
$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
$queryString = $queryString ? '&' . $queryString : '';

?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid p-4">
        
        <!-- Header & Actions -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3" data-aos="fade-down">
            <div>
                <h4 class="fw-bold mb-0">All Tasks</h4>
                <p class="text-muted small mb-0">Manage, filter, and organize all project tasks.</p>
            </div>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class='bx bx-export me-1'></i> Export
                    </button>
                    <ul class="dropdown-menu shadow-sm border-0">
                        <li><a class="dropdown-item" href="?export=csv"><i class='bx bx-file text-success me-2'></i> Download CSV</a></li>
                        <li><a class="dropdown-item" href="#" onclick="window.print();"><i class='bx bxs-file-pdf text-danger me-2'></i> Print / PDF</a></li>
                    </ul>
                </div>
                <!-- Quick Add Modal Trigger -->
                <button type="button" class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#quickAddModal">
                    <i class='bx bx-plus me-1'></i> Quick Add
                </button>
                <a href="create_task.php" class="btn btn-dark">Full Detail Add</a>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card-premium p-3 mb-4" data-aos="fade-up">
            <form method="GET" action="tasks.php" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Search Tasks</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0 bg-transparent"><i class='bx bx-search text-muted'></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0 bg-transparent" placeholder="Keywords..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?php echo $filterStatus == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?php echo $filterStatus == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Review" <?php echo $filterStatus == 'Review' ? 'selected' : ''; ?>>Review</option>
                        <option value="Completed" <?php echo $filterStatus == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="Low" <?php echo $filterPriority == 'Low' ? 'selected' : ''; ?>>Low</option>
                        <option value="Medium" <?php echo $filterPriority == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="High" <?php echo $filterPriority == 'High' ? 'selected' : ''; ?>>High</option>
                        <option value="Urgent" <?php echo $filterPriority == 'Urgent' ? 'selected' : ''; ?>>Urgent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $filterCategory == $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Filter Results</button>
                    <a href="tasks.php" class="btn btn-light border" title="Clear Filters"><i class='bx bx-reset'></i></a>
                </div>
            </form>
        </div>

        <!-- Task Table -->
        <div class="card-premium" data-aos="fade-up" data-aos-delay="100">
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-premium mb-0">
                    <thead style="position: sticky; top: 0; z-index: 20; background: var(--bg-color);">
                        <tr>
                            <th width="30%">Task Details</th>
                            <th>Assignee</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Deadline</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($tasks)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class='bx bx-task text-muted mb-3' style="font-size: 5rem; opacity: 0.5;"></i>
                                        <h5 class="fw-bold text-muted">No tasks found</h5>
                                        <p class="text-muted small mb-3">Try adjusting your filters or add a new task.</p>
                                        <button class="btn btn-primary-gradient btn-sm" data-bs-toggle="modal" data-bs-target="#quickAddModal">Create Task</button>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($tasks as $task): ?>
                            <tr class="align-middle">
                                <td>
                                    <div class="fw-bold text-dark mb-1 d-flex align-items-center">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                        <?php if($task['attachment']): ?>
                                            <i class='bx bx-paperclip ms-2 text-muted' data-bs-toggle="tooltip" title="Has Attachment"></i>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($task['category']): ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25" style="font-size: 0.65rem;">
                                            <i class='bx bx-purchase-tag me-1'></i><?php echo htmlspecialchars($task['category']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($task['assignee']): ?>
                                        <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($task['assignee']); ?>">
                                            <img src="/demo/<?php echo htmlspecialchars(get_profile_picture($task['profile_picture'])); ?>" alt="Avatar" class="rounded-circle object-fit-cover shadow-sm border border-2 border-white" width="32" height="32">
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo priority_badge($task['priority']); ?></td>
                                <td><?php echo status_badge($task['status']); ?></td>
                                <td style="min-width: 120px;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-bold" style="font-size: 0.7rem;"><?php echo $task['progress']; ?>%</small>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 10px;">
                                        <div class="progress-bar <?php echo $task['progress'] == 100 ? 'bg-success' : 'bg-primary'; ?> progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $task['progress']; ?>%;" aria-valuenow="<?php echo $task['progress']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                        if($task['deadline']) {
                                            $dl = new DateTime($task['deadline']);
                                            $now = new DateTime();
                                            $diff = $now->diff($dl);
                                            $isOverdue = ($dl < $now && $task['status'] != 'Completed');
                                            $textClass = $isOverdue ? 'text-danger fw-bold' : 'text-muted';
                                            echo '<span class="small ' . $textClass . '"><i class="bx bx-calendar me-1"></i>' . format_date($task['deadline'], 'M j, Y') . '</span>';
                                        } else {
                                            echo '<span class="small text-muted">-</span>';
                                        }
                                    ?>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                            <i class='bx bx-dots-vertical-rounded'></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li><a class="dropdown-item" href="edit_task.php?id=<?php echo $task['id']; ?>"><i class='bx bx-edit text-primary me-2'></i> Edit Details</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="tasks.php" class="d-inline" id="deleteForm<?php echo $task['id']; ?>">
                                                    <?php csrf_field(); ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                    <button type="button" class="dropdown-item text-danger" onclick="confirmDelete(<?php echo $task['id']; ?>)">
                                                        <i class='bx bx-trash me-2'></i> Delete Task
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
            <div class="card-footer bg-transparent border-top p-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small">Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalRows); ?> of <?php echo $totalRows; ?> tasks</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $queryString; ?>">Prev</a>
                        </li>
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $queryString; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $queryString; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Premium Quick Add Modal -->
<div class="modal fade" id="quickAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="backdrop-filter: blur(5px);">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class='bx bx-bolt-circle text-warning me-2 fs-4 align-middle'></i>Quick Add Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="create_task.php" method="POST">
                <?php csrf_field(); ?>
                <input type="hidden" name="is_quick_add" value="1">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Task Title</label>
                        <input type="text" name="title" class="form-control" placeholder="What needs to be done?" required autofocus>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Deadline</label>
                            <input type="date" name="deadline" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 d-flex justify-content-between">
                    <a href="create_task.php" class="text-muted small text-decoration-none"><i class='bx bx-expand-alt me-1'></i>More options</a>
                    <div>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-gradient px-4">Create</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(taskId) {
    Swal.fire({
        title: 'Delete Task?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1E293B' : '#fff',
        color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#F8FAFC' : '#1F2937'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteForm' + taskId).submit();
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
