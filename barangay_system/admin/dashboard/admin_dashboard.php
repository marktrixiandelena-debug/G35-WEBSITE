<?php
/**
 * Administrative Dashboard
 * Central hub for system monitoring, statistics, and quick navigation.
 * Access restricted to authorized personnel only.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Admin Verification
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

// --- Fetch Statistics ---

// Active Residents Count
$residentCount = $conn->query("
    SELECT COUNT(*) AS total 
    FROM users 
    WHERE role = 'resident' AND status = 'active'
")->fetch_assoc()['total'];

// Initialize Report Counters
$totalReports      = 0;
$pendingReports    = 0;
$inProgressReports = 0;
$resolvedReports   = 0;

// Table Existence Verification (Self-healing safety)
$tableExists = $conn->query("SHOW TABLES LIKE 'reports'")->num_rows > 0;

if ($tableExists) {
    $totalReports      = $conn->query("SELECT COUNT(*) AS total FROM reports")->fetch_assoc()['total'];
    $pendingReports    = $conn->query("SELECT COUNT(*) AS total FROM reports WHERE status = 'pending'")->fetch_assoc()['total'];
    $inProgressReports = $conn->query("SELECT COUNT(*) AS total FROM reports WHERE status = 'in_progress'")->fetch_assoc()['total'];
    $resolvedReports   = $conn->query("SELECT COUNT(*) AS total FROM reports WHERE status = 'resolved'")->fetch_assoc()['total'];
}

// --- Fetch Recent Activity Log ---
$activityLog = [];

// 1. Recent User Registrations
$userResult = $conn->query("SELECT full_name, created_at FROM users ORDER BY created_at DESC LIMIT 3");
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $activityLog[] = [
            'type'    => 'user',
            'message' => 'New user registered: ' . htmlspecialchars($row['full_name']),
            'time'    => strtotime($row['created_at'])
        ];
    }
}

// 2. Recent Incident Reports
if ($tableExists && $totalReports > 0) {
    $reportResult = $conn->query("SELECT type, created_at FROM reports ORDER BY created_at DESC LIMIT 3");
    if ($reportResult) {
        while ($row = $reportResult->fetch_assoc()) {
            $activityLog[] = [
                'type'    => 'report',
                'message' => 'New ' . htmlspecialchars($row['type']) . ' report submitted',
                'time'    => strtotime($row['created_at'])
            ];
        }
    }
}

// Sort Combined Log (Chronological Descending)
usort($activityLog, function ($a, $b) {
    return $b['time'] - $a['time'];
});

$recentActivity = array_slice($activityLog, 0, 5);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../../assets/css/global/admin_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/pages/admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <!-- HEADER -->
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



    <!-- MAIN LAYOUT -->
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <h3>Navigation</h3>
            <ul>
                <li><a href="admin_dashboard.php" class="active"><i class="fa-solid fa-gauge"></i>
                        Dashboard</a></li>
                <li><a href="analytics.php"><i class="fa-solid fa-chart-bar"></i> Analytics</a></li>

                <li><a href="../users/manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="../teams/manage_teams.php"><i class="fa-solid fa-people-group"></i> Manage Teams</a></li>
                <li><a href="../announcements/manage_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
                <li><a href="../users/encode_report.php"><i class="fa-solid fa-keyboard"></i> Encode Report</a></li>
                <li><a href="../reports/view_reports.php"><i class="fa-solid fa-file-alt"></i> View Reports</a></li>
                <li><a href="../logs/audit_logs.php"><i class="fa-solid fa-shield-halved"></i> Audit Logs</a></li>
                <li><a href="../profile/admin_profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <!-- CONTENT AREA -->
        <main class="content">
            <?php require_once "../../includes/flash_toast.php"; ?>
            <h2>Dashboard Overview</h2>
            <p>Monitor incident trends, manage system users, and track response team activity.</p>

            <!-- 1. SUMMARY CARDS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $residentCount; ?></div>
                        <div class="stat-label">Total Residents</div>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon green"><i class="fa-solid fa-file-lines"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $totalReports; ?></div>
                        <div class="stat-label">Total Reports</div>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $pendingReports; ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon indigo"><i class="fa-solid fa-spinner"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $inProgressReports; ?></div>
                        <div class="stat-label">In Progress</div>
                    </div>
                </div>
                <div class="stat-card teal">
                    <div class="stat-icon teal"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $resolvedReports; ?></div>
                        <div class="stat-label">Resolved</div>
                    </div>
                </div>
            </div>

            <!-- 2. MIDDLE SECTION: ACTIVITY & STATUS -->
            <div class="dashboard-middle-grid">
                <!-- Recent Reports -->
                <div class="dashboard-panel">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6; margin-bottom: 1rem; padding-bottom: 1rem;">
                        <h3 style="margin:0; padding:0; border:none; display:flex; align-items:center; gap:0.5rem;"><i
                                class="fa-solid fa-clock-rotate-left"></i> Recent Reports</h3>
                        <a href="../reports/view_reports.php"
                            style="font-size: 0.85rem; color: #065f46; text-decoration: none; font-weight: 600;">View
                            All &rarr;</a>
                    </div>

                    <?php
                    // Fetch recent reports with details including ID and User Name
                    if ($tableExists) {
                        $recents = $conn->query("
                            SELECT r.id, r.type, r.location, r.location_details, r.status, r.created_at, u.full_name 
                            FROM reports r 
                            JOIN users u ON r.user_id = u.id 
                            ORDER BY r.created_at DESC 
                            LIMIT 3
                        ");
                    } else {
                        $recents = false;
                    }
                    ?>

                    <?php if ($recents && $recents->num_rows > 0): ?>
                        <div class="recent-reports-list">
                            <?php while ($row = $recents->fetch_assoc()): ?>
                                <div class="recent-report-item">
                                    <div class="report-icon <?php echo $row['type']; ?>">
                                        <?php echo $row['type'] === 'flood' ? '<i class="fa-solid fa-water"></i>' : '<i class="fa-solid fa-wind"></i>'; ?>
                                    </div>
                                    <div class="report-info">
                                        <div class="report-main">
                                            
                                            <!-- Primary Info: Title, Location, Reporter -->
                                            <div class="report-primary">
                                                <strong><?php echo ucfirst($row['type']); ?> Report <span style="color: #6b7280; font-weight: normal;">#<?php echo $row['id']; ?></span></strong>
                                                <span class="report-loc">
                                                    <i class="fa-solid fa-location-dot"></i> 
                                                    <?php echo htmlspecialchars($row['location'] . ($row['location_details'] ? ', ' . $row['location_details'] : '')); ?>
                                                </span>
                                                <span class="report-reporter">
                                                    <i class="fa-regular fa-user"></i> By <?php echo htmlspecialchars($row['full_name']); ?>
                                                </span>
                                            </div>

                                            <div class="report-meta-right">
                                                <div style="text-align: right;">
                                                    <strong style="font-size: 0.85rem; color: #111827; display: block; font-weight: 600;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></strong>
                                                    <span style="font-size: 0.75rem; color: #6b7280; margin-top: 0.15rem; display: block;"><i class="fa-regular fa-clock" style="font-size: 0.7rem; margin-right: 0.15rem;"></i> <?php echo date('h:i A', strtotime($row['created_at'])); ?></span>
                                                </div>
                                                <?php
                                                $dash_status_icon = match($row['status']) {
                                                    'pending'     => 'fa-clock',
                                                    'in_progress' => 'fa-spinner',
                                                    'resolved'    => 'fa-circle-check',
                                                    'dismissed'   => 'fa-circle-xmark',
                                                    default       => 'fa-clock'
                                                };
                                                ?>
                                                <span class="status-badge-sm status-<?php echo $row['status']; ?>">
                                                    <i class="fa-solid <?php echo $dash_status_icon; ?>" style="font-size:0.6rem;"></i>
                                                    <?php echo ucwords(str_replace('_', ' ', $row['status'])); ?>
                                                </span>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No reports submitted yet.</p>
                    <?php endif; ?>
                </div>

                <div class="dashboard-panel status-panel">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6; margin-bottom: 1.25rem; padding-bottom: 1rem;">
                        <h3 style="margin:0; padding:0; border:none; display:flex; align-items:center; gap:0.5rem;"><i class="fa-solid fa-bullhorn"></i> Active Announcements</h3>
                        <a href="../announcements/manage_announcements.php" style="font-size: 0.85rem; color: #065f46; text-decoration: none; font-weight: 600;">Manage All &rarr;</a>
                    </div>
                    
                    <div class="status-message">
                        <?php
                        $announcements = $conn->query("SELECT * FROM announcements WHERE status = 'Active' ORDER BY created_at DESC LIMIT 3");
                        if ($announcements && $announcements->num_rows > 0):
                            while ($ann = $announcements->fetch_assoc()):
                                $icon = 'fa-info-circle';
                                $color = '#3b82f6';
                                $typeClass = 'type-general';

                                if ($ann['type'] == 'System Update') { 
                                    $icon = 'fa-server'; 
                                    $color = '#ef4444'; 
                                    $typeClass = 'type-system';
                                }
                                if ($ann['type'] == 'Advisory') { 
                                    $icon = 'fa-triangle-exclamation'; 
                                    $color = '#f59e0b'; 
                                    $typeClass = 'type-advisory';
                                }
                        ?>
                                <div class="announcement-card <?php echo $typeClass; ?>">
                                    <div class="announcement-header">
                                        <h4 class="announcement-title">
                                            <i class="fa-solid <?php echo $icon; ?>" style="color: <?php echo $color; ?>;"></i> 
                                            <?php echo htmlspecialchars($ann['title']); ?>
                                        </h4>
                                        <span class="announcement-date"><?php echo date('M d, Y', strtotime($ann['created_at'])); ?></span>
                                    </div>
                                    <p class="announcement-body">
                                        <?php echo nl2br(htmlspecialchars($ann['content'])); ?>
                                    </p>
                                </div>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <div class="system-status" style="padding: 1rem; background: #f9fafb; border-radius: 0.5rem; text-align: center; display: block;">
                                <i class="fa-regular fa-bell-slash" style="font-size: 1.5rem; color: #d1d5db; margin-bottom: 0.5rem;"></i>
                                <p style="margin:0; font-size: 0.9rem; color: #6b7280;">No active announcements currently posted.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 3. QUICK ACTIONS -->
            <div class="summary-box">
                <h3><i class="fa-solid fa-bolt"></i> Quick Actions</h3>
                <div class="quick-actions-grid">
                    <a href="../reports/view_reports.php" class="quick-action-btn">
                        <i class="fa-solid fa-file-alt"></i> View Reports
                    </a>
                    <a href="../users/manage_users.php" class="quick-action-btn">
                        <i class="fa-solid fa-users"></i> Manage Users
                    </a>
                    <a href="../teams/manage_teams.php" class="quick-action-btn">
                        <i class="fa-solid fa-people-group"></i> Manage Teams
                    </a>
                    <a href="analytics.php" class="quick-action-btn">
                        <i class="fa-solid fa-chart-bar"></i> Analytics
                    </a>
                    <a href="../logs/audit_logs.php" class="quick-action-btn">
                        <i class="fa-solid fa-shield-halved"></i> Audit Logs
                    </a>
                </div>
            </div>
        </main>

    </div>

    <!-- Footer Branding -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag | Administrative Panel</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>

</body>

</html>

