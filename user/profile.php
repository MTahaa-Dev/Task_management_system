<?php
$required_role = 'user';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$userId = $_SESSION['user_id'];
$user = get_current_user_data($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['flash_error'] = "Invalid request token.";
    } else {
        $action = $_POST['action'];
        
        if ($action === 'update_profile') {
            $fullName = trim(sanitize($_POST['full_name']));
            $bio = trim(sanitize($_POST['bio'] ?? ''));
            $phone = trim(sanitize($_POST['phone'] ?? ''));
            
            // Handle Profile Picture Upload
            $profilePic = $user['profile_picture'];
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['profile_picture'];
                $allowed = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($file['type'], $allowed) && $file['size'] < 2 * 1024 * 1024) { // 2MB
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                    $targetDir = __DIR__ . '/../assets/uploads/profile_pictures/';
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                    if (move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
                        $profilePic = 'assets/uploads/profile_pictures/' . $filename;
                    }
                } else {
                    $_SESSION['flash_error'] = "Invalid image. Must be JPG/PNG/GIF and under 2MB.";
                }
            }

            if (!isset($_SESSION['flash_error'])) {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, bio = ?, phone = ?, profile_picture = ? WHERE id = ?");
                if ($stmt->execute([$fullName, $bio, $phone, $profilePic, $userId])) {
                    log_activity($pdo, $userId, 'Updated profile information');
                    $_SESSION['flash_success'] = "Profile updated successfully.";
                    // refresh user data
                    $user = get_current_user_data($pdo);
                }
            }
        } elseif ($action === 'change_password') {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if (password_verify($currentPassword, $user['password'])) {
                if ($newPassword === $confirmPassword && strlen($newPassword) >= 6) {
                    $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed, $userId]);
                    log_activity($pdo, $userId, 'Changed password');
                    $_SESSION['flash_success'] = "Password changed successfully.";
                } else {
                    $_SESSION['flash_error'] = "New passwords don't match or are too short.";
                }
            } else {
                $_SESSION['flash_error'] = "Current password is incorrect.";
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid p-4" style="max-width: 1000px;">
        
        <div class="mb-4" data-aos="fade-down">
            <h4 class="fw-bold mb-0">My Profile</h4>
            <p class="text-muted small mb-0">Manage your personal information and security settings.</p>
        </div>

        <div class="row g-4">
            <!-- Profile Settings -->
            <div class="col-md-8" data-aos="fade-right">
                <div class="card-premium p-4 h-100">
                    <h6 class="fw-bold text-primary mb-4"><i class='bx bx-user me-2'></i>Personal Information</h6>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="d-flex align-items-center mb-4">
                            <img src="/demo/<?php echo htmlspecialchars(get_profile_picture($user['profile_picture'])); ?>" alt="Avatar" class="rounded-circle object-fit-cover shadow me-4" width="100" height="100" id="avatarPreview">
                            <div>
                                <label class="btn btn-sm btn-outline-primary mb-2">
                                    <i class='bx bx-upload me-1'></i> Change Avatar
                                    <input type="file" name="profile_picture" hidden accept="image/*" onchange="previewImage(this)">
                                </label>
                                <p class="text-muted small mb-0">JPG, GIF or PNG. Max size 2MB.</p>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Username <span class="text-secondary fw-normal">(Read Only)</span></label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Email Address <span class="text-secondary fw-normal">(Read Only)</span></label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Bio</label>
                                <textarea name="bio" class="form-control" rows="4" placeholder="Tell us a bit about yourself..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary-gradient px-4">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="col-md-4" data-aos="fade-left">
                <div class="card-premium p-4 h-100">
                    <h6 class="fw-bold text-danger mb-4"><i class='bx bx-shield-quarter me-2'></i>Security Settings</h6>
                    
                    <form method="POST" action="">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-outline-danger w-100">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
