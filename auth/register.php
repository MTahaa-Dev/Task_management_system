<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    header("Location: /demo/" . (is_admin() ? "admin" : "user") . "/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['flash_error'] = "Invalid request token.";
    } else {
        $fullName = trim(sanitize($_POST['full_name']));
        $username = trim(sanitize($_POST['username']));
        $email = trim(sanitize($_POST['email']));
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
            $_SESSION['flash_error'] = "Please fill in all required fields.";
        } elseif ($password !== $confirmPassword) {
            $_SESSION['flash_error'] = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $_SESSION['flash_error'] = "Password must be at least 6 characters long.";
        } else {
            // Check if username or email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $_SESSION['flash_error'] = "Username or email already exists.";
            } else {
                // Insert new user
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $insertStmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role, status) VALUES (?, ?, ?, ?, 'user', 'active')");
                
                try {
                    $insertStmt->execute([$fullName, $username, $email, $hashedPassword]);
                    $newUserId = $pdo->lastInsertId();
                    
                    log_activity($pdo, $newUserId, 'User registered');
                    create_notification($pdo, $newUserId, 'Welcome to TaskMaster Pro!', 'info');
                    
                    $_SESSION['flash_success'] = "Registration successful! You can now log in.";
                    header("Location: /demo/auth/login.php");
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['flash_error'] = "An error occurred during registration. Please try again.";
                }
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="auth-wrapper">
    <div class="container py-3 py-md-0">
        <div class="row g-0 auth-card mx-auto flex-row-reverse" style="max-width: 900px;" data-aos="fade-up">
            
            <!-- Image/Branding Side -->
            <div class="col-md-5 d-none d-md-flex auth-image-side text-center p-4" style="background: linear-gradient(135deg, #10B981 0%, #047857 100%);">
                <div class="shape" style="width: 100px; height: 100px; top: 10px; right: -10px;"></div>
                <div class="shape" style="width: 70px; height: 70px; bottom: 30px; left: 10px; animation-delay: 1.5s;"></div>
                
                <i class='bx bx-rocket text-white mb-2' style="font-size: 4rem;"></i>
                <h3 class="fw-bold mb-2">Join Us</h3>
                <p class="text-white-50 small px-2">Create an account and start managing your tasks with unprecedented efficiency.</p>
            </div>
            
            <!-- Form Side -->
            <div class="col-md-7 p-4 p-lg-5 bg-card" style="background-color: var(--card-bg);">
                <div class="text-center mb-3 d-md-none">
                    <i class='bx bx-task text-primary fs-2 mb-1'></i>
                    <h4 class="fw-bold">TaskMaster</h4>
                </div>
                
                <h4 class="fw-bold mb-1">Create an Account</h4>
                <p class="text-muted small mb-3">Fill in your details below to get started.</p>
                
                <form method="POST" action="">
                    <?php csrf_field(); ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label text-muted small fw-bold mb-1">Full Name</label>
                            <input type="text" name="full_name" class="form-control form-control-sm" placeholder="John Doe" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label text-muted small fw-bold mb-1">Username</label>
                            <input type="text" name="username" class="form-control form-control-sm" placeholder="johndoe" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label text-muted small fw-bold mb-1">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="john@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label text-muted small fw-bold mb-1">Password</label>
                            <input type="password" name="password" class="form-control form-control-sm" placeholder="Enter password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold mb-1">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control form-control-sm" placeholder="Confirm password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-gradient btn-sm w-100 py-2 mb-2">Sign Up</button>
                    
                    <p class="text-center text-muted small mb-0">
                        Already have an account? <a href="login.php" class="text-primary fw-bold text-decoration-none">Sign in</a>
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
