<?php
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";
require_once "../../includes/logger.php";

// Ensure only admin can access
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: manage_users.php");
    exit();
}

$user_id        = (int)($_POST['user_id']        ?? 0);
$current_status = trim($_POST['current_status'] ?? '');

if ($user_id <= 0) {
    $_SESSION['flash_error'] = "User not found.";
    header("Location: manage_users.php");
    exit();
}

if ($user_id === (int)$_SESSION['user_id']) {
    $_SESSION['flash_error'] = "You cannot disable your own active account.";
    header("Location: manage_users.php");
    exit();
}

// Determine new status
$new_status = ($current_status === 'active') ? 'disabled' : 'active';

// Fetch user name for logging (and verify user exists)
$name_stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$name_stmt->bind_param("i", $user_id);
$name_stmt->execute();
$name_result = $name_stmt->get_result();

if ($name_result->num_rows === 0) {
    $name_stmt->close();
    $_SESSION['flash_error'] = "User not found.";
    header("Location: manage_users.php");
    exit();
}

$user_name = $name_result->fetch_assoc()['full_name'];
$name_stmt->close();

// Update status
$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $user_id);

if ($stmt->execute()) {
    $action = ($new_status === 'active') ? 'User Activated' : 'User Disabled';
    logActivity($conn, $_SESSION['user_id'], $action, "$user_name status changed to $new_status");
    $stmt->close();

    // Respect a safe redirect back to view_user.php if one was provided
    $redirect_raw = trim($_POST['redirect'] ?? '');
    
    $_SESSION['flash_success'] = "User account successfully " . ($new_status === 'active' ? 'activated.' : 'disabled.');
    
    if (preg_match('/^view_user\.php\?id=\d+$/', $redirect_raw)) {
        header("Location: " . $redirect_raw);
    } else {
        header("Location: manage_users.php");
    }
    exit();
} else {
    $stmt->close();
    $_SESSION['flash_error'] = "Database error. Please try again.";
    header("Location: manage_users.php");
    exit();
}
?>
