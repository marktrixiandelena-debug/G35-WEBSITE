<?php
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_users.php");
    exit();
}

$user_id = intval($_POST['user_id'] ?? 0);

if ($user_id <= 0) {
    $_SESSION['flash_error'] = "User not found.";
    header("Location: manage_users.php");
    exit();
}

// Verify the user exists and is pending
$stmt = $conn->prepare("SELECT id, full_name, status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$target = $result->fetch_assoc();

if (!$target || $target['status'] !== 'pending') {
    $_SESSION['flash_error'] = "User not found or is no longer pending.";
    header("Location: manage_users.php");
    exit();
}

// Reject: delete the pending record
$delete = $conn->prepare("DELETE FROM users WHERE id = ? AND status = 'pending'");
$delete->bind_param("i", $user_id);

if ($delete->execute()) {
    // Log the action
    $log = $conn->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'Rejected Registration', ?)");
    $details = "Rejected and deleted pending registration for: " . $target['full_name'] . " (ID: $user_id)";
    $admin_id = $_SESSION['user_id'];
    $log->bind_param("is", $admin_id, $details);
    $log->execute();

    $_SESSION['flash_success'] = "Pending registration rejected and deleted.";
    header("Location: manage_users.php");
} else {
    $_SESSION['flash_error'] = "Database error. Please try again.";
    header("Location: manage_users.php");
}
exit();
