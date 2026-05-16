<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect based on role
if (is_logged_in()) {
    header("Location: /demo/" . (is_admin() ? "admin" : "user") . "/dashboard.php");
    exit();
}

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['flash_error'] = "Invalid request token.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;

        if (empty($username) || empty($password)) {
            $_SESSION['flash_error'] = "Please fill in all fields.";
        } else {
            // Find user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $_SESSION['flash_error'] = "Your account is " . $user['status'] . ". Contact support.";
                } else {
                    // Success
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Update last login
                    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                    
                    log_activity($pdo, $user['id'], 'User logged in');
                    $_SESSION['flash_success'] = "Welcome back, " . htmlspecialchars($user['full_name']) . "!";
                    
                    header("Location: /demo/" . ($user['role'] === 'admin' ? "admin" : "user") . "/dashboard.php");
                    exit();
                }
            } else {
                $_SESSION['flash_error'] = "Invalid username or password.";
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="auth-wrapper">
    <div class="container py-3 py-md-0">
        <div class="row g-0 auth-card mx-auto" style="max-width: 900px;" data-aos="zoom-in">
            
            <!-- Image/Branding Side -->
            <div class="col-md-5 d-none d-md-flex auth-image-side text-center p-4">
                <div class="shape" style="width: 100px; height: 100px; top: 10px; right: -10px;"></div>
                <div class="shape" style="width: 70px; height: 70px; bottom: 30px; left: 10px; animation-delay: 1.5s;"></div>
                
                <i class='bx bx-task text-white mb-2' style="font-size: 4rem;"></i>
                <h3 class="fw-bold mb-2">TaskMaster Pro</h3>
                <p class="text-white-50 small px-2">Manage your projects, collaborate with your team, and achieve your goals with our premium SaaS platform.</p>
            </div>
            
            <!-- Form Side -->
            <div class="col-md-7 p-4 p-lg-5 bg-card d-flex flex-column justify-content-center" style="background-color: var(--card-bg);">
                <div class="text-center mb-3 d-md-none">
                    <i class='bx bx-task text-primary fs-2 mb-1'></i>
                    <h4 class="fw-bold">TaskMaster</h4>
                </div>
                
                <h4 class="fw-bold mb-1">Welcome Back</h4>
                <p class="text-muted small mb-3">Please enter your details to sign in.</p>
                
                <form method="POST" action="">
                    <?php csrf_field(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Username or Email</label>
                        <input type="text" name="username" class="form-control form-control-sm" placeholder="Enter username or email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Password</label>
                        <input type="password" name="password" class="form-control form-control-sm" placeholder="Enter password" required>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label small text-muted" for="remember">Remember me</label>
                        </div>
                        <a href="forgot_password.php" class="text-primary small text-decoration-none fw-bold">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-gradient btn-sm w-100 py-2 mb-2">Sign In</button>
                    
                    <p class="text-center text-muted small mb-0">
                        Don't have an account? <a href="register.php" class="text-primary fw-bold text-decoration-none">Sign up</a>
                    </p>
                </form>
            </div>
            
        </div>
    </div>
</div>

<script>
    // Clear any saved light mode preference so the user always starts in default Dark Mode upon logging in.
    localStorage.removeItem('theme');
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
