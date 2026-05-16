<?php
$required_role = 'admin';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pagination logic
$limit = 15;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "";
$params = [];

if ($search !== '') {
    $whereClause = "WHERE a.action LIKE ? OR u.full_name LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM activity_log a JOIN users u ON a.user_id = u.id $whereClause");
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch logs
$query = "SELECT a.*, u.full_name, u.username, u.profile_picture 
          FROM activity_log a 
          JOIN users u ON a.user_id = u.id 
          $whereClause 
          ORDER BY a.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
            <div>
                <h4 class="fw-bold mb-0">System Activity Log</h4>
                <p class="text-muted small mb-0">Monitor all actions taken across the platform.</p>
            </div>
            
            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search logs..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit"><i class='bx bx-search'></i></button>
                    <?php if($search): ?>
                        <a href="activity_log.php" class="btn btn-outline-secondary"><i class='bx bx-x'></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card-premium" data-aos="fade-up">
            <div class="table-responsive">
                <table class="table table-premium mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action Taken</th>
                            <th>Description / Target</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($logs)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <img src="/demo/assets/images/empty-state.svg" alt="No Logs" style="width: 150px; opacity: 0.5;" class="mb-3 d-none">
                                    <i class='bx bx-ghost text-muted mb-2' style="font-size: 4rem;"></i>
                                    <h5 class="fw-bold text-muted">No activity found</h5>
                                    <p class="text-muted small">Try adjusting your search criteria.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($logs as $log): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="/demo/<?php echo htmlspecialchars(get_profile_picture($log['profile_picture'])); ?>" alt="Avatar" class="rounded-circle object-fit-cover me-2" width="32" height="32">
                                        <div>
                                            <span class="d-block fw-bold small"><?php echo htmlspecialchars($log['full_name']); ?></span>
                                            <span class="text-muted" style="font-size: 0.7rem;">@<?php echo htmlspecialchars($log['username']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><i class='bx bx-check-shield me-1'></i> <?php echo htmlspecialchars($log['action']); ?></span>
                                </td>
                                <td>
                                    <span class="text-muted small"><?php echo htmlspecialchars($log['description'] ?? '-'); ?></span>
                                </td>
                                <td>
                                    <div class="small fw-bold text-dark"><?php echo format_date($log['created_at'], 'M j, Y'); ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;"><i class='bx bx-time'></i> <?php echo format_date($log['created_at'], 'g:i A'); ?></div>
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
                <span class="text-muted small">Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalRows); ?> of <?php echo $totalRows; ?> entries</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                        </li>
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
