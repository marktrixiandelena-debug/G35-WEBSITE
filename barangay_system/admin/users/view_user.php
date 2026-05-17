<?php
/**
 * Administrative Account Snapshot
 * Provides a detailed overview of a specific user or resident account.
 * Facilitates account status toggling and administrative password resets.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Admin Verification
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

$target_id = (int)($_GET['id'] ?? 0);
if (!$target_id) {
    header("Location: manage_users.php");
    exit();
}

// --- Fetch User Snapshot Data ---
$stmt = $conn->prepare("SELECT id, full_name, username, role, status, contact_number, address, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $target_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: manage_users.php?error=user_not_found");
    exit();
}

// UI Context Helpers
$memberSince  = date('F d, Y', strtotime($user['created_at']));
$avatarLetter = strtoupper(substr($user['full_name'], 0, 1));
$isSelf       = ($user['id'] == $_SESSION['user_id']);

// Resolve contextual back URL — preserves active listing filters/pagination
$backUrl = 'manage_users.php';
if (!empty($_GET['returnto'])) {
    $cleanRt = preg_replace('/[^a-zA-Z0-9=&_%+.-]/', '', $_GET['returnto']);
    if ($cleanRt) $backUrl = 'manage_users.php?' . $cleanRt;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile – <?php echo htmlspecialchars($user['full_name']); ?> | Admin Panel</title>
    <link rel="stylesheet" href="../../assets/css/global/admin_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/pages/view_user.css?v=5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

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
                <div class="avatar-circle"><?php echo strtoupper(substr($_SESSION['full_name'] ?: 'A', 0, 1)); ?></div>
            </div>
            <a href="../../auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <div class="dashboard-container">
        <aside class="sidebar">
            <h3>Navigation</h3>
            <ul>
                <li><a href="../dashboard/admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="../dashboard/analytics.php"><i class="fa-solid fa-chart-bar"></i> Analytics</a></li>
                <li><a href="manage_users.php" class="active"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="../teams/manage_teams.php"><i class="fa-solid fa-people-group"></i> Manage Teams</a></li>
                <li><a href="../announcements/manage_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
                <li><a href="encode_report.php"><i class="fa-solid fa-keyboard"></i> Encode Report</a></li>
                <li><a href="../reports/view_reports.php"><i class="fa-solid fa-file-alt"></i> View Reports</a></li>
                <li><a href="../logs/audit_logs.php"><i class="fa-solid fa-shield-halved"></i> Audit Logs</a></li>
                <li><a href="../profile/admin_profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <main class="content">

            <?php if (isset($_GET['success'])): ?>
                <div style="background:#dcfce7; border-left:4px solid #16a34a; padding:1rem; border-radius:0.375rem; margin-bottom:1.25rem; color:#166534; font-size:0.875rem;">
                    <?php
                    $success_msgs = [
                        'status_updated' => '✅ User account status has been updated successfully.',
                    ];
                    echo $success_msgs[$_GET['success']] ?? 'Operation completed successfully.';
                    ?>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="page-header-flex">
                <div>
                    <h2>User Profile</h2>
                    <p>Viewing account details and report history.</p>
                </div>
                <a href="<?php echo htmlspecialchars($backUrl); ?>" class="action-btn btn-back-nav">
                    <i class="fa-solid fa-arrow-left"></i> Back to Users
                </a>
            </div>

            <!-- ===== PROFILE CARD ===== -->
            <div class="profile-card">

                <!-- Hero Banner -->
                <div class="profile-card-header">
                    <div class="profile-avatar"><?php echo $avatarLetter; ?></div>
                    <div class="profile-hero-info">
                        <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="profile-username">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <div class="profile-hero-badges">
                            <span class="hero-badge role-badge-<?php echo $user['role']; ?>">
                                <i class="fa-solid <?php echo $user['role'] === 'admin' ? 'fa-shield-halved' : 'fa-user'; ?>"></i>
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                            <span class="hero-badge status-pill-<?php echo $user['status']; ?>">
                                <i class="fa-solid <?php echo $user['status'] === 'active' ? 'fa-circle-check' : 'fa-ban'; ?>"></i>
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                            <span class="hero-badge hero-badge-plain">
                                <i class="fa-regular fa-calendar-days"></i>
                                Joined <?php echo $memberSince; ?>
                            </span>
                        </div>
                        </div>
                    </div>

                    <!-- Action Buttons inside Hero Banner -->
                    <?php if (!$isSelf): ?>
                    <div class="profile-header-actions">
                        <?php if ($user['role'] !== 'admin'): ?>
                            <form method="POST" action="toggle_user.php" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $user['status']; ?>">
                                <input type="hidden" name="redirect" value="view_user.php?id=<?php echo $user['id']; ?>">
                                <?php if ($user['status'] === 'active'): ?>
                                    <button type="submit" class="action-btn btn-disable hero-btn-white"
                                        onclick="return confirm('Disable this account?');">
                                        <i class="fa-solid fa-ban"></i> Disable Account
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="action-btn btn-enable hero-btn-white"
                                        onclick="return confirm('Enable this account?');">
                                        <i class="fa-solid fa-circle-check"></i> Enable Account
                                    </button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                        <form method="POST" action="process_reset_password.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="action-btn btn-reset-pass hero-btn-white"
                                onclick="return confirm('Reset password for <?php echo htmlspecialchars(addslashes($user['full_name'])); ?>?');">
                                <i class="fa-solid fa-key"></i> Reset Password
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- Info Grid -->
                <div class="profile-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label><i class="fa-solid fa-user"></i> Full Name</label>
                            <p><?php echo htmlspecialchars($user['full_name']); ?></p>
                        </div>
                        <div class="info-item">
                            <label><i class="fa-solid fa-at"></i> Username</label>
                            <p><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                        <div class="info-item">
                            <label><i class="fa-solid fa-phone"></i> Contact Number</label>
                            <p><?php echo htmlspecialchars($user['contact_number'] ?: '—'); ?></p>
                        </div>
                        <div class="info-item">
                            <label><i class="fa-solid fa-location-dot"></i> Address / Purok</label>
                            <p><?php echo htmlspecialchars($user['address'] ?: '—'); ?></p>
                        </div>
                        <div class="info-item">
                            <label><i class="fa-solid fa-calendar-days"></i> Account Created</label>
                            <p><?php echo $memberSince; ?></p>
                        </div>
                        <div class="info-item">
                            <label><i class="fa-solid fa-id-badge"></i> Account ID</label>
                            <p class="mono-text">#<?php echo $user['id']; ?></p>
                        </div>
                    </div>
                </div>

                </div>

            </div><!-- /profile-card -->


        </main>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag | Administrative Panel</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>
</body>
</html>

