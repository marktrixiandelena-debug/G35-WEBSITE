<?php
// Initialize Session
session_start();

// Verify Active Session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
