<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    header("Location: /demo/user/dashboard.php");
    exit();
}

$email = isset($_GET['email']) ? $_GET['email'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($email) || empty($token)) {
    $_SESSION['flash_error'] = "Invalid password reset link.";
    header("Location: /demo/auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['flash_error'] = "Invalid request token.";
    } else {
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (empty($password) || empty($confirmPassword)) {
            $_SESSION['flash_error'] = "Please fill in all fields.";
        } elseif ($password !== $confirmPassword) {
            $_SESSION['flash_error'] = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $_SESSION['flash_error'] = "Password must be at least 6 characters long.";
        } else {
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashedPassword, $email]);
            
            if ($stmt->rowCount() > 0) {
                // Get user id to log activity
                $userStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $userStmt->execute([$email]);
                $user = $userStmt->fetch();
                if($user) {
                    log_activity($pdo, $user['id'], 'Password reset successfully');
                }
                
                $_SESSION['flash_success'] = "Password reset successfully! You can now log in.";
                header("Location: /demo/auth/login.php");
                exit();
            } else {
                $_SESSION['flash_error'] = "Failed to reset password. Email not found.";
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="auth-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card-premium p-5" data-aos="fade-down">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex p-3 mb-3 text-primary">
                            <i class='bx bx-key fs-2'></i>
                        </div>
                        <h4 class="fw-bold">Set New Password</h4>
                        <p class="text-muted small">Please create a new password for <?php echo htmlspecialchars($email); ?>.</p>
                    </div>

                    <form method="POST" action="">
                        <?php csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-primary-gradient w-100 py-2">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
