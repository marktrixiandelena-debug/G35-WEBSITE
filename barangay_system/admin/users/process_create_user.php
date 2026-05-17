<?php
/**
 * User Account Provisioning (Admin)
 * Facilitates the secure creation of new administrative or resident accounts.
 * Automatically generates temporary strong passwords and enforces mandatory reset logic.
 */
require_once "../../includes/auth_check.php";
// Include the database connection configuration.
require_once "../../config/db.php";
// Include the logger file for activity logging.
require_once "../../includes/logger.php";
// Include identity formatting utilities.
require_once "../../includes/format.php";

// Security Check: Only 'admin' users can create new accounts.
// If the logged-in user is not an admin, stop the script and show an error.
if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

// Get the data submitted from the form and normalize identity fields.
$full_name = formatName($_POST['full_name']);
$username  = formatUsername($_POST['username']);
$role              = $_POST['role'];
$status            = $_POST['status'];
$contact_number    = trim($_POST['contact_number'] ?? '');
$address           = formatName($_POST['address'] ?? '');
// Mandatory first-login password change for all admin-created accounts
$require_password_change = 1;

// Validate the input:
// 1. Username must not be too long (max 50 characters).
if (strlen($username) > 50) {
    die("Invalid input.");
}

// AUTO-GENERATE a secure random password (8 characters: letters + numbers)
// This is MORE SECURE than admin-chosen passwords.
// The system generates random passwords to prevent weak or reused passwords.
$temp_password = bin2hex(random_bytes(4)); // Generates 8 random hexadecimal characters
$hashed = password_hash($temp_password, PASSWORD_DEFAULT);

// Prepare the command to insert a new user into the database.
// '?' are placeholders for the actual data.
// We added contact_number, address, and status to the query.
$stmt = $conn->prepare(
    "INSERT INTO users (full_name, username, password, role, status, contact_number, address, require_password_change)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

// Bind the actual data to the placeholders.
// "sssssssi" means we are sending 7 Strings (text) and 1 Integer.
$stmt->bind_param("sssssssi", $full_name, $username, $hashed, $role, $status, $contact_number, $address, $require_password_change);

// Execute the command.
if ($stmt->execute()) {
    // Log Action
    logActivity($conn, $_SESSION['user_id'], 'Create User', "Created new $role account for $full_name (username: $username)");

    // Success! Store the new user credentials in the session to show them precisely once.
    $_SESSION['new_user_success'] = true;
    $_SESSION['new_user_name'] = $full_name;
    $_SESSION['new_user_username'] = $username;
    $_SESSION['new_user_password'] = $temp_password;

    header("Location: manage_users.php");
    exit();
} else {
    // If failed (e.g., username already exists), stop and show message.
    header("Location: manage_users.php?error=db_error");
    exit();
}
?>
