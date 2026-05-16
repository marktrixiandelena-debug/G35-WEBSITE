<?php
/**
 * Resident Profile Management
 * Allows residents to update contact information and view account details.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Resident Verification
if ($_SESSION['role'] !== 'resident') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error   = '';

// --- Handle Profile Update ---
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
            header("Location: profile.php");
            exit();
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }
}

// --- Fetch Current User Data ---
$stmt = $conn->prepare("SELECT full_name, username, contact_number, address, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Prepare display values
$memberSince = isset($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : 'N/A';
$avatarLetter = strtoupper(substr($user['full_name'] ?: 'U', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../../assets/css/global/residents_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/global/residents_components.css">
    <link rel="stylesheet" href="../../assets/css/pages/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <!-- ===== HEADER ===== -->
    <header class="dashboard-header">
        <div class="brand-section">
            <button id="sidebarToggle" class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
            <img src="../../assets/images/barangay_logo.jpg" alt="Barangay Logo" class="logo-lg">
            <div class="header-titles">
                <h1>Barangay Calauag | Resident Portal</h1>
                <h2>Flood and Drainage Incident Reporting and Management System</h2>
            </div>
        </div>
        <div class="user-actions">
            <div class="profile-badge">
                <span class="role-text"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Resident'); ?></span>
                <div class="avatar-circle"><?php echo strtoupper(substr($_SESSION['full_name'] ?: 'U', 0, 1)); ?></div>
            </div>
            <a href="../../auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <!-- ===== LAYOUT ===== -->
    <div class="dashboard-container">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <h3>Menu</h3>
            <ul>
                <li><a href="../dashboard/resident_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="../reports/submit_report.php"><i class="fa-solid fa-paper-plane"></i> Submit Report</a></li>
                <li><a href="../reports/my_reports.php"><i class="fa-solid fa-file-invoice"></i> My Reports</a></li>
                <li><a href="../profile/profile.php" class="active"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
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
                        <i class="fa-solid fa-user-check"></i> Resident · Member since <?php echo $memberSince; ?>
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
                                   value="<?php echo htmlspecialchars($user['contact_number']); ?>"
                                   placeholder="09xxxxxxxxx" maxlength="11" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Home Address</label>
                            <textarea id="address" name="address" class="form-control" rows="3"
                                      placeholder="Street, Purok, Barangay..." required><?php echo htmlspecialchars($user['address']); ?></textarea>
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
                    </div>

                    <!-- Security Card -->
                    <div class="summary-box">
                        <h3><i class="fa-solid fa-shield-halved"></i> Security</h3>
                        <p style="color: #6b7280; font-size: 0.875rem; margin: 0 0 1.25rem 0;">
                            Keep your account secure with a strong, unique password. We recommend updating it regularly.
                        </p>
                        <a href="../../auth/change_password.php" class="btn-change-password">
                            <i class="fa-solid fa-key"></i> Change Password
                        </a>
                    </div>

                </div><!-- /right column -->

            </div><!-- /profile-grid -->

        </main>
    </div>

    <?php require_once "../../includes/flash_toast.php"; ?>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>

</body>
</html>

