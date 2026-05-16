<?php
/**
 * Resident Dashboard
 * Provides overview of reported incidents and recent announcements.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Resident Verification
if ($_SESSION['role'] !== 'resident') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Fetch Statistics ---
$totalReports    = $conn->query("SELECT COUNT(*) AS c FROM reports WHERE user_id = $user_id")->fetch_assoc()['c'];
$pendingReports  = $conn->query("SELECT COUNT(*) AS c FROM reports WHERE user_id = $user_id AND status = 'pending'")->fetch_assoc()['c'];
$activeReports   = $conn->query("SELECT COUNT(*) AS c FROM reports WHERE user_id = $user_id AND status IN ('pending','in_progress')")->fetch_assoc()['c'];
$resolvedReports = $conn->query("SELECT COUNT(*) AS c FROM reports WHERE user_id = $user_id AND status = 'resolved'")->fetch_assoc()['c'];

// --- Fetch Recent Reports (Last 3) ---
$recentStmt = $conn->prepare("
    SELECT id, type, location, location_details, status, created_at
    FROM reports WHERE user_id = ?
    ORDER BY created_at DESC LIMIT 3
");
$recentStmt->bind_param("i", $user_id);
$recentStmt->execute();
$recentReports = $recentStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../../assets/css/global/residents_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/global/residents_components.css">
    <link rel="stylesheet" href="../../assets/css/pages/resident_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <!-- Header Branding -->
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

        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <h3>Menu</h3>
            <ul>
                <li><a href="../dashboard/resident_dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="../reports/submit_report.php"><i class="fa-solid fa-paper-plane"></i> Submit Report</a></li>
                <li><a href="../reports/my_reports.php"><i class="fa-solid fa-file-invoice"></i> My Reports</a></li>
                <li><a href="../profile/profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="content">
            <?php require_once "../../includes/flash_toast.php"; ?>

            <!-- Welcome Header -->
            <div class="page-header">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>
                <p>Track your reports and stay updated on barangay incidents.</p>
            </div>

            <?php if ($pendingReports > 0): ?>
                <div class="alert-box" style="margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-clock"></i>
                    You have <strong><?php echo $pendingReports; ?></strong> pending report<?php echo $pendingReports > 1 ? 's' : ''; ?> awaiting review by the barangay.
                </div>
            <?php endif; ?>

            <!-- ===== 1. STAT CARDS ===== -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-file-lines"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $totalReports; ?></div>
                        <div class="stat-label">Total Reports</div>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $activeReports; ?></div>
                        <div class="stat-label">Active Reports</div>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $resolvedReports; ?></div>
                        <div class="stat-label">Resolved</div>
                    </div>
                </div>
            </div>

            <!-- ===== 2. MIDDLE GRID: RECENT REPORTS + ANNOUNCEMENTS ===== -->
            <div class="dash-middle-grid">

                <!-- Recent Reports Panel -->
                <div class="dashboard-panel">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-clock-rotate-left"></i> Recent Reports</h3>
                        <a href="../reports/my_reports.php" class="panel-header-link">View All &rarr;</a>
                    </div>

                    <?php if ($recentReports && $recentReports->num_rows > 0): ?>
                        <?php while ($row = $recentReports->fetch_assoc()):
                            $status_icon = match($row['status']) {
                                'pending'     => 'fa-clock',
                                'in_progress' => 'fa-spinner',
                                'resolved'    => 'fa-circle-check',
                                default       => 'fa-clock'
                            };
                        ?>
                            <div class="recent-report-item">
                                <div class="report-icon <?php echo $row['type']; ?>">
                                    <?php echo $row['type'] === 'flood'
                                        ? '<i class="fa-solid fa-water"></i>'
                                        : '<i class="fa-solid fa-wrench"></i>'; ?>
                                </div>
                                <div class="report-info">
                                    <div class="report-main">
                                        <div class="report-primary">
                                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                                <strong><?php echo ucfirst($row['type']); ?></strong>
                                                <small>#<?php echo $row['id']; ?></small>
                                            </div>
                                            <div class="report-loc">
                                                <i class="fa-solid fa-location-dot"></i>
                                                <?php echo htmlspecialchars($row['location'] . ($row['location_details'] ? ' — ' . $row['location_details'] : '')); ?>
                                            </div>
                                        </div>
                                        <div class="report-meta-right">
                                            <span class="status-badge-sm status-<?php echo $row['status']; ?>">
                                                <i class="fa-solid <?php echo $status_icon; ?>"></i>
                                                <?php echo ucwords(str_replace('_', ' ', $row['status'])); ?>
                                            </span>
                                            <div class="report-time">
                                                <i class="fa-regular fa-clock"></i> 
                                                <span><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="dash-empty">
                            <i class="fa-regular fa-folder-open"></i>
                            <p>No reports submitted yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Announcements Panel -->
                <div class="dashboard-panel">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-bullhorn"></i> Announcements</h3>
                    </div>

                    <?php
                    $announcements = $conn->query("SELECT * FROM announcements WHERE status = 'Active' ORDER BY created_at DESC LIMIT 4");
                    if ($announcements && $announcements->num_rows > 0):
                        while ($ann = $announcements->fetch_assoc()):
                            $icon      = 'fa-info-circle';
                            $color     = '#1F7A8C';
                            $typeClass = 'type-general';
                            if ($ann['type'] === 'System Update') { $icon = 'fa-server';               $color = '#ef4444'; $typeClass = 'type-system'; }
                            if ($ann['type'] === 'Advisory')      { $icon = 'fa-triangle-exclamation'; $color = '#f59e0b'; $typeClass = 'type-advisory'; }
                    ?>
                        <div class="ann-card <?php echo $typeClass; ?>">
                            <div class="ann-header">
                                <h4 class="ann-title">
                                    <i class="fa-solid <?php echo $icon; ?>" style="color:<?php echo $color; ?>;"></i>
                                    <?php echo htmlspecialchars($ann['title']); ?>
                                </h4>
                                <span class="ann-date"><?php echo date('M d, Y', strtotime($ann['created_at'])); ?></span>
                            </div>
                            <p class="ann-body"><?php
                                $preview = mb_strlen($ann['content']) > 150
                                    ? mb_substr($ann['content'], 0, 150) . '…'
                                    : $ann['content'];
                                echo nl2br(htmlspecialchars($preview));
                            ?></p>
                        </div>
                    <?php
                        endwhile;
                    else: ?>
                        <div class="dash-empty">
                            <i class="fa-regular fa-bell-slash"></i>
                            <p>No new announcements at this time.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div><!-- /dash-middle-grid -->

            <!-- ===== 3. QUICK ACCESS ===== -->
            <div class="summary-box">
                <h3><i class="fa-solid fa-bolt"></i> Quick Access</h3>
                <div class="quick-actions-grid">
                    <a href="../reports/submit_report.php" class="quick-action-btn">
                        <span class="qa-icon"><i class="fa-solid fa-paper-plane"></i></span>
                        Submit Report
                    </a>
                    <a href="../reports/my_reports.php" class="quick-action-btn">
                        <span class="qa-icon"><i class="fa-solid fa-file-invoice"></i></span>
                        My Reports
                    </a>
                    <a href="../profile/profile.php" class="quick-action-btn">
                        <span class="qa-icon"><i class="fa-solid fa-user-gear"></i></span>
                        My Profile
                    </a>
                </div>
            </div>

        </main>
    </div>

    <!-- Footer Branding -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>
</body>
</html>
