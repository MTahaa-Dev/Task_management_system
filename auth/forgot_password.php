<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    header("Location: /demo/user/dashboard.php");
    exit();
}

$resetLinkGenerated = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['flash_error'] = "Invalid request token.";
    } else {
        $email = trim(sanitize($_POST['email']));
        
        if (empty($email)) {
            $_SESSION['flash_error'] = "Please enter your email address.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate a secure token
                $token = bin2hex(random_bytes(32));
                // In a real app, store this token in a password_resets table with an expiration
                // For this demo, we will simulate the email by showing the link in the UI
                $resetLinkGenerated = "http://" . $_SERVER['HTTP_HOST'] . "/demo/auth/reset_password.php?token=" . $token . "&email=" . urlencode($email);
                
                $_SESSION['flash_success'] = "Password reset instructions sent to your email! (Simulated)";
            } else {
                // Don't reveal if email exists or not for security, just show success
                $_SESSION['flash_success'] = "If that email exists, password reset instructions have been sent.";
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
                            <i class='bx bx-lock-open-alt fs-2'></i>
                        </div>
                        <h4 class="fw-bold">Forgot Password</h4>
                        <p class="text-muted small">Enter your email and we'll send you instructions to reset your password.</p>
                    </div>

                    <?php if($resetLinkGenerated): ?>
                        <div class="alert alert-info small rounded-3 border-0 bg-info bg-opacity-10 text-info">
                            <strong>Demo Note:</strong> Email sending is simulated. Click the link below to reset:<br>
                            <a href="<?php echo htmlspecialchars($resetLinkGenerated); ?>" class="text-break fw-bold"><?php echo htmlspecialchars($resetLinkGenerated); ?></a>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?php csrf_field(); ?>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                        </div>
                        <button type="submit" class="btn btn-primary-gradient w-100 py-2 mb-3">Send Reset Link</button>
                        <div class="text-center">
                            <a href="login.php" class="text-muted small text-decoration-none"><i class='bx bx-arrow-back me-1'></i> Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
