<?php
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";
require_once "../../includes/logger.php";

// Only admins can reset passwords
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

// Must be a POST request with a valid user_id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['user_id'])) {
    header("Location: manage_users.php");
    exit();
}

$target_id = (int)$_POST['user_id'];

if ($target_id === (int)$_SESSION['user_id']) {
    $_SESSION['flash_error'] = "You cannot reset your own password here.";
    header("Location: manage_users.php");
    exit();
}

// Fetch the target user's name and username
$stmt = $conn->prepare("SELECT full_name, username FROM users WHERE id = ?");
$stmt->bind_param("i", $target_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $_SESSION['flash_error'] = "User not found.";
    header("Location: manage_users.php");
    exit();
}

$target_user = $result->fetch_assoc();
$stmt->close();

// Generate a new secure 8-character temporary password
$new_temp_password = bin2hex(random_bytes(4));
$hashed = password_hash($new_temp_password, PASSWORD_DEFAULT);

// Update the user's password and re-flag require_password_change
$stmt = $conn->prepare("UPDATE users SET password = ?, require_password_change = 1 WHERE id = ?");
$stmt->bind_param("si", $hashed, $target_id);

if (!$stmt->execute()) {
    $stmt->close();
    $_SESSION['flash_error'] = "Database error. Please try again.";
    header("Location: manage_users.php");
    exit();
}
$stmt->close();

// Log the action
logActivity(
    $conn,
    $_SESSION['user_id'],
    'Reset Password',
    "Admin reset password for user: {$target_user['username']} (ID: {$target_id})"
);

// Store result in session so the modal can display it on manage_users.php
$_SESSION['reset_success']        = true;
$_SESSION['reset_user_name']      = $target_user['full_name'];
$_SESSION['reset_user_username']  = $target_user['username'];
$_SESSION['reset_temp_password']  = $new_temp_password;

$_SESSION['flash_success'] = "Password reset successfully.";
header("Location: manage_users.php");
exit();
?>
