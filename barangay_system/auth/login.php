<?php
// Authentication Initialization
session_start();
require_once "../config/db.php";

// Initialize Flash Error
$error = '';
if (isset($_SESSION['reg_success'])) {
    $_SESSION['flash_success'] = $_SESSION['reg_success'];
    unset($_SESSION['reg_success']);
}

// Handle Login Request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Query Database for User
        $stmt = $conn->prepare("SELECT id, username, password, role, status, require_password_change, full_name FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Status Verification
            if ($user['status'] === 'pending') {
                $error = "Your account is pending admin approval.";
            } elseif ($user['status'] !== 'active') {
                $error = "This account has been disabled.";
            }
            // Password Verification
            elseif (password_verify($password, $user['password'])) {
                // Set Session Data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'] ?? '';

                // Force Password Change Check
                if ($user['require_password_change']) {
                    $_SESSION['force_change'] = true;
                    header("Location: change_password.php");
                    exit();
                }

                // Redirect to Dashboard
                header("Location: ../{$user['role']}/dashboard/{$user['role']}_dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Account not found.";
        }
    }

    // Set Error Flash
    if ($error) {
        $_SESSION['flash_error'] = $error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../assets/css/global/loginreg.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-login">
    <div class="auth-container">
        <div class="auth-header" style="margin-bottom: 1.25rem;">
            <img src="../assets/images/barangay_logo.jpg" alt="Barangay Calauag Seal"
                style="width: 90px; height: 90px; margin: 0 auto 0.75rem auto; display: block; border-radius: 50%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2>Authorized Login</h2>
            <p style="font-size: 0.9rem;">Please login to access your account</p>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" placeholder="Enter your username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" required
                        placeholder="Enter your password">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary">Sign In</button>
        </form>

        <div class="register-link" style="text-align:center; margin-top:1.25rem; font-size:0.875rem; color:#6b7280;">
            Don't have an account? <a href="register.php" style="color:#059669; font-weight:600; text-decoration:none;">Register here</a>
        </div>

        <div class="auth-footer">
            <p>&copy; <?php echo date('Y'); ?> Barangay Calauag</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/auth/password_toggle.js"></script>
    <?php require_once "../includes/flash_toast.php"; ?>
</body>

</html>