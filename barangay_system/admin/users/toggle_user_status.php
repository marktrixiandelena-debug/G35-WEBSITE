<?php
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Security: Only admins can toggle user status
if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

// Get form data
$user_id = intval($_POST['user_id']);
$current_status = $_POST['current_status'];

// Determine new status (toggle)
$new_status = ($current_status === 'active') ? 'disabled' : 'active';

// Prevent disabling admin accounts
$user = $conn->query("SELECT role FROM users WHERE id = $user_id")->fetch_assoc();

if ($user && $user['role'] === 'admin') {
    die("Cannot disable admin accounts.");
}

// Update user status
$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $user_id);

if ($stmt->execute()) {
    // Success - redirect back to manage users page
    header("Location: manage_users.php");
    exit();
} else {
    die("Error updating user status.");
}
?>
