<?php
session_start();
require_once "../config/db.php";

// Allow access only if logged in OR if force change is required
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $user_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("UPDATE users SET password = ?, require_password_change = 0 WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user_id);

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = "Password updated successfully!";
            unset($_SESSION['force_change']);
            
            // Redirect immediately
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
    <title>Change Password - Barangay Calauag</title>
    <link rel="stylesheet" href="../assets/css/global/loginreg.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-login">
    <div class="auth-container">
        <div class="auth-header">
            <h2>Security Update</h2>
            <p><?php echo isset($_SESSION['force_change']) ? "First login detected. Please set a new password." : "Update your account password."; ?></p>
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
                    <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="At least 8 characters">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="Repeat new password">
                </div>

                <button type="submit" class="btn-primary">Update Password</button>
            </form>

        <div class="auth-footer">
            <p>&copy; <?php echo date('Y'); ?> Barangay Management System</p>
        </div>
    </div>
    
    <script src="../assets/js/auth/password_toggle.js"></script>
    <?php require_once "../includes/flash_toast.php"; ?>
</body>
</html>
