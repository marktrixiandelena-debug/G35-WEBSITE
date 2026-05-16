<?php
/**
 * System Data Analytics
 * Aggregates incident report data for higher-level visualization and trend analysis.
 * Uses Chart.js for graphical representation of density and severity distributions.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Admin Verification
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

// --- Aggregate Overview Statistics ---

// Total Report Volume
$totalReports = $conn->query("SELECT COUNT(*) FROM reports")->fetch_row()[0];

// Resolution Rate Metrics
$resolvedCount = $conn->query("SELECT COUNT(*) FROM reports WHERE status = 'resolved'")->fetch_row()[0];
$activeCount   = $conn->query("SELECT COUNT(*) FROM reports WHERE status IN ('pending', 'in_progress')")->fetch_row()[0];

// Critical Operation Safety Verification
$hasSeverityColumn = $conn->query("SHOW COLUMNS FROM reports LIKE 'severity'")->num_rows > 0;
$criticalCount = 0;
if ($hasSeverityColumn) {
    $criticalCount = $conn->query("SELECT COUNT(*) FROM reports WHERE severity = 'Critical' AND status != 'dismissed'")->fetch_row()[0];
}

// --- Categorical Analysis (Non-Dismissed/Operational only) ---

// 1. Incident Type Breakdown
$floodCount    = 0;
$drainageCount = 0;
$typeData      = $conn->query("SELECT type, COUNT(*) as count FROM reports WHERE status != 'dismissed' GROUP BY type");
if ($typeData) {
    while ($row = $typeData->fetch_assoc()) {
        if ($row['type'] == 'flood')    $floodCount    = $row['count'];
        if ($row['type'] == 'drainage') $drainageCount = $row['count'];
    }
}

// 2. Incident Severity Distribution
$severities = [];
if ($hasSeverityColumn) {
    $severityData = $conn->query("SELECT severity, COUNT(*) as count FROM reports WHERE status != 'dismissed' GROUP BY severity");
    if ($severityData) {
        while ($row = $severityData->fetch_assoc()) {
            $severities[$row['severity']] = $row['count'];
        }
    }
}

// 3. Geographic Heatmap Data (Location Ranking)
$locationData = $conn->query("SELECT location, COUNT(*) as count FROM reports WHERE status != 'dismissed' GROUP BY location ORDER BY count DESC");
$locations    = [];
if ($locationData && $locationData->num_rows > 0) {
    while ($loc = $locationData->fetch_assoc()) {
        $locations[] = $loc;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics | Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../../assets/css/global/admin_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/pages/analytics.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li><a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="analytics.php" class="active"><i class="fa-solid fa-chart-bar"></i> Analytics</a></li>

                <li><a href="../users/manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="../teams/manage_teams.php"><i class="fa-solid fa-people-group"></i> Manage Teams</a></li>
                <li><a href="../announcements/manage_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
                <li><a href="../users/encode_report.php"><i class="fa-solid fa-keyboard"></i> Encode Report</a></li>
                <li><a href="../reports/view_reports.php"><i class="fa-solid fa-file-alt"></i> View Reports</a></li>
                <li><a href="../logs/audit_logs.php"><i class="fa-solid fa-shield-halved"></i> Audit Logs</a></li>
                <li><a href="../profile/admin_profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <!-- CONTENT -->
        <main class="content">
            <div class="page-header">
                <h2>Analytics Dashboard</h2>
                <p>Real-time insights on incident reports.</p>
            </div>

            <!-- OVERVIEW CARDS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-file-lines"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $totalReports; ?></div>
                        <div class="stat-label">Total Reports</div>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $resolvedCount; ?></div>
                        <div class="stat-label">Resolved Cases</div>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon amber"><i class="fa-solid fa-hourglass-half"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $activeCount; ?></div>
                        <div class="stat-label">Active / Pending</div>
                    </div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $criticalCount; ?></div>
                        <div class="stat-label">Critical Incidents</div>
                    </div>
                </div>
            </div>





            <!-- ANALYTICS GRID (3-COLUMN) -->
            <div class="analytics-container">
                <!-- INCIDENT TYPES -->
                <div class="analytics-card">
                    <h3><i class="fa-solid fa-chart-pie"></i> Incidents by Type</h3>
                    <div class="chart-container">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>

                <!-- SEVERITY DISTRIBUTION -->
                <div class="analytics-card">
                    <h3><i class="fa-solid fa-triangle-exclamation"></i> Severity Levels</h3>
                    <div class="chart-container">
                        <canvas id="severityChart"></canvas>
                    </div>
                </div>

                <!-- REPORTS BY ZONE (RANKED LIST) -->
                <div class="analytics-card">
                    <h3><i class="fa-solid fa-map-location-dot"></i> Top Affected Areas</h3>
                    <div class="ranked-list-wrapper">
                        <?php
                        if (!empty($locations)) {
                            $rank = 1;
                            foreach ($locations as $loc) {
                                echo "
                                <div class='ranked-item'>
                                    <div class='ranked-info'>
                                        <div class='ranked-rank'>#{$rank}</div>
                                        <div class='ranked-loc'>" . htmlspecialchars($loc['location']) . "</div>
                                    </div>
                                    <div class='ranked-count'>{$loc['count']}</div>
                                </div>";
                                $rank++;
                            }
                        } else {
                            echo "<div class='ranked-empty'><i class='fa-regular fa-folder-open' style='font-size: 1.5rem; display: block; margin-bottom: 0.5rem;'></i>No location data yet</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag | Administrative Panel</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>

    <!-- CHART.JS INITIALIZATION -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. Type Doughnut Chart ---
        const typeCtx = document.getElementById('typeChart');
        if (typeCtx) {
            new Chart(typeCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Flood', 'Drainage'],
                    datasets: [{
                        data: [<?php echo $floodCount; ?>, <?php echo $drainageCount; ?>],
                        backgroundColor: ['#0ea5e9', '#fb923c'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '50%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 20, font: { family: "'Inter', sans-serif", size: 13 } } }
                    }
                }
            });
        }

        // --- 2. Severity Doughnut Chart ---
        const severityCtx = document.getElementById('severityChart');
        if (severityCtx) {
            new Chart(severityCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Low', 'Medium', 'High', 'Critical'],
                    datasets: [{
                        data: [
                            <?php echo $severities['Low'] ?? 0; ?>,
                            <?php echo $severities['Medium'] ?? 0; ?>,
                            <?php echo $severities['High'] ?? 0; ?>,
                            <?php echo $severities['Critical'] ?? 0; ?>
                        ],
                        backgroundColor: ['#22c55e', '#fbbf24', '#f97316', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '50%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 15, font: { family: "'Inter', sans-serif" } } }
                    }
                }
            });
        }

    });
    </script>

</html>

