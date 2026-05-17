<?php
// Registration Processing
session_start();
require_once "../config/db.php";
require_once "../includes/format.php";

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// Sanitize & Normalize Inputs
$full_name      = formatName($_POST['full_name'] ?? '');
$username       = formatUsername($_POST['username'] ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');
$address        = formatName($_POST['address'] ?? '');
$password       = $_POST['password'] ?? '';
$confirm        = $_POST['confirm_password'] ?? '';

// --- Validation ---
$errors = [];

if (empty($full_name)) $errors[] = "Full name is required.";
if (empty($username))  $errors[] = "Username is required.";
if (empty($contact_number)) $errors[] = "Contact number is required.";
if (empty($address))   $errors[] = "Address is required.";
if (empty($password))  $errors[] = "Password is required.";
if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
if ($password !== $confirm) $errors[] = "Passwords do not match.";

// Check username uniqueness
if (empty($errors)) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Username is already taken.";
    }
    $stmt->close();
}

// Handle Validation Failures
if (!empty($errors)) {
    $_SESSION['reg_errors'] = $errors;
    $_SESSION['reg_old'] = [
        'full_name'      => $full_name,
        'username'       => $username,
        'contact_number' => $contact_number,
        'address'        => $address,
    ];
    header("Location: register.php");
    exit();
}

// --- Insert User ---
$hashed = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO users (full_name, username, password, role, status, contact_number, address, require_password_change) VALUES (?, ?, ?, 'resident', 'pending', ?, ?, 0)");
$stmt->bind_param("sssss", $full_name, $username, $hashed, $contact_number, $address);

if ($stmt->execute()) {
    // Return Success to Login
    $_SESSION['reg_success'] = "Your account has been submitted for approval.";
    header("Location: login.php");
} else {
    $_SESSION['reg_errors'] = ["A database error occurred. Please try again."];
    header("Location: register.php");
}
exit();
?>
