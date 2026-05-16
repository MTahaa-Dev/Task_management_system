<?php
/**
 * Core utility and security functions
 */

session_start();

// --- Security Functions ---

/**
 * Generate a CSRF token and store it in the session
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token
 */
function validate_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * Output CSRF token as a hidden input field
 */
function csrf_field() {
    $token = generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Sanitize output (XSS prevention)
 */
function sanitize($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize($value);
        }
    } else {
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    return $data;
}

// --- User & Auth Functions ---

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if logged in user is admin
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user data
 */
function get_current_user_data($pdo) {
    if (!is_logged_in()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Get user profile picture (returns default if none)
 */
function get_profile_picture($path) {
    if (empty($path) || !file_exists(__DIR__ . '/../' . $path)) {
        return 'assets/images/default-avatar.png';
    }
    return $path;
}

// --- Activity Logging ---

/**
 * Log user activity
 */
function log_activity($pdo, $user_id, $action, $description = null) {
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, description) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $action, $description]);
}

// --- Notification Functions ---

/**
 * Create a new notification
 */
function create_notification($pdo, $user_id, $message, $type = 'info') {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $message, $type]);
}

// --- UI Helpers ---

/**
 * Format a date nicely
 */
function format_date($date_string, $format = 'M j, Y') {
    if (empty($date_string)) return 'N/A';
    $date = new DateTime($date_string);
    return $date->format($format);
}

/**
 * Display a nice status badge
 */
function status_badge($status) {
    $classes = [
        'Pending' => 'bg-secondary',
        'In Progress' => 'bg-primary',
        'Review' => 'bg-warning text-dark',
        'Completed' => 'bg-success'
    ];
    
    $class = $classes[$status] ?? 'bg-secondary';
    return '<span class="badge rounded-pill ' . $class . '">' . htmlspecialchars($status) . '</span>';
}

/**
 * Display priority badge
 */
function priority_badge($priority) {
    $classes = [
        'Low' => 'bg-info text-dark',
        'Medium' => 'bg-success',
        'High' => 'bg-warning text-dark',
        'Urgent' => 'bg-danger'
    ];
    
    $class = $classes[$priority] ?? 'bg-secondary';
    return '<span class="badge ' . $class . '">' . htmlspecialchars($priority) . '</span>';
}
?>
