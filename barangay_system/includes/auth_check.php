<?php
// Initialize Session
session_start();

// Verify Active Session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Enforce Mandatory Password Change
// Prevents access to any protected page until the user sets a new personal password.
if (isset($_SESSION['force_change']) && $_SESSION['force_change'] === true) {
    header("Location: ../auth/change_password.php");
    exit();
}
?>
