<?php
/**
 * Admin Profile Management
 * Allows administrative personnel to update their contact information and address.
 * Core account identity (Username/Full Name) is locked for audit integrity.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Admin Verification
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error   = '';

// --- Handle Profile Update Action ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($contact) || empty($address)) {
        $error = "Contact number and address cannot be empty.";
    } else {
        $updateStmt = $conn->prepare("UPDATE users SET contact_number = ?, address = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $contact, $address, $user_id);

        if ($updateStmt->execute()) {
            $_SESSION['flash_success'] = "Your profile has been updated successfully.";
            header("Location: admin_profile.php");
            exit();
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }
}

// --- Fetch Current Admin Profile Data ---
$stmt = $conn->prepare("SELECT id, full_name, username, contact_number, address, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// UI Context Helpers
$memberSince  = isset($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : 'N/A';
$avatarLetter = strtoupper(substr($user['full_name'] ?: 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Admin Panel – Barangay Calauag</title>
    <link rel="stylesheet" href="../../assets/css/global/admin_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/pages/admin_profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <!-- ===== HEADER ===== -->
    <header class="dashboard-header">
        <div class="brand-section">
            <button id="sidebarToggle" class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
            <img src="../../assets/images/barangay_logo.jpg" alt="Barangay Logo" class="logo-lg">
            <div class="header-titles">
                <h1>Barangay Calauag | Admin Panel</h1>
                <h2>Flood and Drainage Incident Reporting and Management System</h2>
            </div>
        </div>
        <div class="user-actions">
            <div class="profile-badge">
                <span class="role-text"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                <div class="avatar-circle"><?php echo $avatarLetter; ?></div>
            </div>
            <a href="../../auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <!-- ===== LAYOUT ===== -->
    <div class="dashboard-container">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <h3>Navigation</h3>
            <ul>
                <li><a href="../dashboard/admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="../dashboard/analytics.php"><i class="fa-solid fa-chart-bar"></i> Analytics</a></li>
                <li><a href="../users/manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="../teams/manage_teams.php"><i class="fa-solid fa-people-group"></i> Manage Teams</a></li>
                <li><a href="../announcements/manage_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
                <li><a href="../users/encode_report.php"><i class="fa-solid fa-keyboard"></i> Encode Report</a></li>
                <li><a href="../reports/view_reports.php"><i class="fa-solid fa-file-alt"></i> View Reports</a></li>
                <li><a href="../logs/audit_logs.php"><i class="fa-solid fa-shield-halved"></i> Audit Logs</a></li>
                <li><a href="admin_profile.php" class="active"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="content">

            <!-- Page Header -->
            <div class="page-header">
                <h2>My Profile</h2>
                <p>Manage your personal information and account security.</p>
            </div>

            <!-- Alerts -->
            <?php if ($error): ?>
                <div class="alert-error" style="margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Hero Banner -->
            <div class="profile-hero">
                <div class="hero-avatar"><?php echo $avatarLetter; ?></div>
                <div class="hero-info">
                    <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                    <span class="hero-badge">
                        <i class="fa-solid fa-shield-halved"></i> Administrator · Member since <?php echo $memberSince; ?>
                    </span>
                </div>
            </div>

            <!-- Two-column grid -->
            <div class="profile-grid">

                <!-- Personal Information Card -->
                <div class="summary-box">
                    <h3><i class="fa-solid fa-id-card"></i> Personal Information</h3>
                    <form method="POST" action="">

                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" class="form-control"
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" disabled>
                            <p class="field-hint"><i class="fa-solid fa-lock"></i> Full Name cannot be changed.</p>
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" class="form-control"
                                   value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <p class="field-hint"><i class="fa-solid fa-lock"></i> Username cannot be changed.</p>
                        </div>

                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" class="form-control"
                                   value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>"
                                   placeholder="09xxxxxxxxx" maxlength="11" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="3"
                                      placeholder="Street, Purok, Barangay..." required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit">
                                <i class="fa-solid fa-floppy-disk"></i> Save Changes
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Right column -->
                <div style="display: flex; flex-direction: column; gap: 1.75rem;">

                    <!-- Account Info Card -->
                    <div class="summary-box">
                        <h3><i class="fa-solid fa-circle-info"></i> Account Details</h3>

                        <div class="info-row">
                            <i class="fa-solid fa-user"></i>
                            <strong>Full Name</strong>
                            <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fa-solid fa-at"></i>
                            <strong>Username</strong>
                            <span><?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fa-solid fa-phone"></i>
                            <strong>Contact</strong>
                            <span><?php echo htmlspecialchars($user['contact_number'] ?: '—'); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fa-solid fa-map-pin"></i>
                            <strong>Address</strong>
                            <span><?php echo htmlspecialchars($user['address'] ?: '—'); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fa-solid fa-calendar-days"></i>
                            <strong>Member Since</strong>
                            <span><?php echo $memberSince; ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fa-solid fa-id-badge"></i>
                            <strong>Account ID</strong>
                            <span style="font-family:monospace; font-size:0.9rem;">#<?php echo $user['id']; ?></span>
                        </div>
                    </div>

                    <!-- Security Card -->
                    <div class="summary-box">
                        <h3><i class="fa-solid fa-shield-halved"></i> Security</h3>
                        <p style="color: #6b7280; font-size: 0.875rem; margin: 0 0 1.25rem 0;">
                            Keep your admin account secure with a strong, unique password. We recommend updating it regularly.
                        </p>
                        <a href="../../auth/change_password.php" class="btn-change-password">
                            <i class="fa-solid fa-key"></i> Change Password
                        </a>
                    </div>

                </div><!-- /right column -->

            </div><!-- /profile-grid -->

        </main>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag | Administrative Panel</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>

    <?php require_once "../../includes/flash_toast.php"; ?>
</body>
</html>

