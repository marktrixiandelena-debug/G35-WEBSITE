<?php
// Centralized Audit Logger Utility

function logActivity($conn, $user_id, $action, $details)
{
    // Execute Audit Insertion
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iss", $user_id, $action, $details);
        $stmt->execute();
        $stmt->close();
    }
}
?>