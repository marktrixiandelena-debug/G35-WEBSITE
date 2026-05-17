<?php
/**
 * Mandatory / Voluntary Password Change
 * Handles the first-login mandatory password change and voluntary profile password updates.
 * Clears the force_change flag and logs the action on success.
 */
session_start();
require_once "../config/db.php";
require_once "../includes/logger.php";

// Require an active session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error   = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password     = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed  = password_hash($new_password, PASSWORD_DEFAULT);
        $user_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("UPDATE users SET password = ?, require_password_change = 0 WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user_id);

        if ($stmt->execute()) {
            // Log the password change action
            $log_detail = isset($_SESSION['force_change'])
                ? 'Set new personal password (mandatory first-login change)'
                : 'Updated account password';
            logActivity($conn, $user_id, 'Password Changed', $log_detail);

            // Clear force change flag
            unset($_SESSION['force_change']);

            $_SESSION['flash_success'] = "Password updated successfully.";
            header("Location: ../{$_SESSION['role']}/dashboard/{$_SESSION['role']}_dashboard.php");
            exit();
        } else {
            $error = "Error updating password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../assets/css/global/loginreg.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-login">
    <div class="auth-container">
        <div class="auth-header">
            <img src="../assets/images/barangay_logo.jpg" alt="Barangay Calauag Seal"
                style="width: 90px; height: 90px; margin: 0 auto 0.75rem auto; display: block; border-radius: 50%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2>Password Update Required</h2>
            <p style="font-size: 0.9rem; color: #4b5563; line-height: 1.5;">
                <?php if (isset($_SESSION['force_change'])): ?>
                    For security purposes, please create a new personal password before continuing.
                <?php else: ?>
                    Update your account password below.
                <?php endif; ?>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password" class="form-control" required
                        placeholder="At least 8 characters">
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
                        placeholder="Repeat new password">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary">Set New Password</button>
        </form>

        <div class="auth-footer">
            <p>&copy; <?php echo date('Y'); ?> Barangay Calauag</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/auth/password_toggle.js"></script>
    <?php require_once "../includes/flash_toast.php"; ?>
</body>

</html>
