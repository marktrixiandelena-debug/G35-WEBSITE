<?php
// Initialize Global Database Connection
$conn = new mysqli("localhost", "root", "", "barangay_system");

// Validate Connection Status
if ($conn->connect_error) {
    die("Database connection failed.");
}
?>