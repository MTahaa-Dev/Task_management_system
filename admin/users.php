<?php
$required_role = 'admin';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Handle User Actions (Create, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['flash_error'] = "Invalid request token.";
    } else {
        $action = $_POST['action'];
        
        if ($action === 'create_user') {
            $fullName = trim(sanitize($_POST['full_name']));
            $username = trim(sanitize($_POST['username']));
            $email = trim(sanitize($_POST['email']));
            $password = $_POST['password'];
            $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
            
            if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
                $_SESSION['flash_error'] = "All fields are required.";
            } else {
                // check if exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetch()) {
                    $_SESSION['flash_error'] = "Username or Email already exists.";
                } else {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $ins = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
                    if ($ins->execute([$fullName, $username, $email, $hashed, $role])) {
                        log_activity($pdo, $_SESSION['user_id'], 'Created new user', "Username: $username");
                        $_SESSION['flash_success'] = "User created successfully.";
                    }
                }
            }
        } elseif ($action === 'update_status') {
            $uId = (int)$_POST['user_id'];
            $status = in_array($_POST['status'], ['active', 'inactive', 'banned']) ? $_POST['status'] : 'active';
            if ($uId !== $_SESSION['user_id']) { // prevent banning self
                $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$status, $uId]);
                log_activity($pdo, $_SESSION['user_id'], "Changed user status to $status", "User ID: $uId");
                $_SESSION['flash_success'] = "User status updated.";
            } else {
                $_SESSION['flash_error'] = "You cannot change your own status.";
            }
        } elseif ($action === 'delete_user') {
            $uId = (int)$_POST['user_id'];
            if ($uId !== $_SESSION['user_id']) {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uId]);
                log_activity($pdo, $_SESSION['user_id'], "Deleted a user", "User ID: $uId");
                $_SESSION['flash_success'] = "User permanently deleted.";
            } else {
                $_SESSION['flash_error'] = "You cannot delete yourself.";
            }
        }
        
        header("Location: users.php");
        exit();
    }
}

// Fetch all users
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "1=1";
$params = [];
if ($search) {
    $where = "(full_name LIKE ? OR username LIKE ? OR email LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid p-4">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3" data-aos="fade-down">
            <div>
                <h4 class="fw-bold mb-0">Manage Users</h4>
                <p class="text-muted small mb-0">Add, edit, or remove system access for users.</p>
            </div>
            <div class="d-flex gap-2">
                <form method="GET" class="input-group" style="max-width: 250px;">
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit"><i class='bx bx-search'></i></button>
                </form>
                <button class="btn btn-primary-gradient px-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class='bx bx-plus me-1'></i> Add User
                </button>
            </div>
        </div>

        <div class="card-premium" data-aos="fade-up">
            <div class="table-responsive">
                <table class="table table-premium mb-0">
                    <thead>
                        <tr>
                            <th>User Details</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class='bx bx-group fs-1 mb-2'></i><br>No users found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($users as $u): ?>
                            <tr class="align-middle">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="/demo/<?php echo htmlspecialchars(get_profile_picture($u['profile_picture'])); ?>" class="rounded-circle object-fit-cover shadow-sm me-3" width="40" height="40">
                                        <div>
                                            <span class="d-block fw-bold mb-1"><?php echo htmlspecialchars($u['full_name']); ?></span>
                                            <span class="badge bg-light text-muted border border-secondary border-opacity-25" style="font-size: 0.65rem;">@<?php echo htmlspecialchars($u['username']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block small"><i class='bx bx-envelope text-muted me-1'></i><?php echo htmlspecialchars($u['email']); ?></span>
                                    <?php if($u['phone']): ?>
                                        <span class="d-block small"><i class='bx bx-phone text-muted me-1'></i><?php echo htmlspecialchars($u['phone']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($u['role'] === 'admin'): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary"><i class='bx bx-shield-quarter me-1'></i>Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary"><i class='bx bx-user me-1'></i>User</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        if($u['status'] === 'active') echo "<span class='badge bg-success'>Active</span>";
                                        elseif($u['status'] === 'inactive') echo "<span class='badge bg-warning text-dark'>Inactive</span>";
                                        else echo "<span class='badge bg-danger'>Banned</span>";
                                    ?>
                                </td>
                                <td>
                                    <span class="small fw-bold text-dark d-block"><?php echo format_date($u['created_at']); ?></span>
                                    <?php if($u['last_login']): ?>
                                        <span class="text-muted" style="font-size: 0.65rem;">Last Login: <?php echo format_date($u['last_login'], 'M j, g:i a'); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 0.65rem;">Never logged in</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if($u['id'] !== $_SESSION['user_id']): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                                <i class='bx bx-dots-vertical-rounded'></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li>
                                                    <form method="POST" action="">
                                                        <?php csrf_field(); ?>
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                        <input type="hidden" name="status" value="<?php echo $u['status'] === 'active' ? 'banned' : 'active'; ?>">
                                                        <button class="dropdown-item <?php echo $u['status'] === 'active' ? 'text-danger' : 'text-success'; ?>" type="submit">
                                                            <i class='bx <?php echo $u['status'] === 'active' ? 'bx-block' : 'bx-check-shield'; ?> me-2'></i>
                                                            <?php echo $u['status'] === 'active' ? 'Ban User' : 'Unban User'; ?>
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="" id="deleteUser<?php echo $u['id']; ?>">
                                                        <?php csrf_field(); ?>
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                        <button type="button" class="dropdown-item text-danger" onclick="confirmDeleteUser(<?php echo $u['id']; ?>)">
                                                            <i class='bx bx-trash me-2'></i> Delete User
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">You</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="backdrop-filter: blur(5px);">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class='bx bx-user-plus text-primary me-2 fs-4 align-middle'></i>Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="create_user">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Password</label>
                            <input type="text" name="password" class="form-control" value="Password123!" required>
                            <small class="text-muted" style="font-size: 0.65rem;">Default provided. User can change it later.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Role</label>
                            <select name="role" class="form-select">
                                <option value="user" selected>User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-gradient px-4">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeleteUser(id) {
    Swal.fire({
        title: 'Delete User?',
        text: "This will permanently remove their account and all associated data!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete!',
        background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1E293B' : '#fff',
        color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#F8FAFC' : '#1F2937'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteUser' + id).submit();
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
