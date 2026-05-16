<?php
/**
 * Resident Report Detailed View
 * Displays full details, evidence, and processing timeline of a specific report.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Resident Verification
if ($_SESSION['role'] !== 'resident') {
    header("Location: ../auth/login.php");
    exit();
}

$resident_id = $_SESSION['user_id'];
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my_reports.php");
    exit();
}

$report_id = (int)$_GET['id'];

// --- Fetch Report Details (Security: Must belong to active user) ---
$sql = "SELECT r.*, enc.full_name AS encoder_name
        FROM reports r
        LEFT JOIN users enc ON r.encoded_by = enc.id
        WHERE r.id = ? AND r.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $report_id, $resident_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    header("Location: my_reports.php");
    exit();
}

// --- Fetch Assigned Response Team ---
$teamSql = "SELECT t.team_name, ra.status AS assignment_status 
            FROM report_assignments ra 
            JOIN response_teams t ON ra.team_id = t.id 
            WHERE ra.report_id = ? 
            ORDER BY ra.assigned_at DESC LIMIT 1";
$teamStmt = $conn->prepare($teamSql);
$teamStmt->bind_param("i", $report_id);
$teamStmt->execute();
$assignedTeam = $teamStmt->get_result()->fetch_assoc();

// --- Fetch Case Timeline ---
$timelineSql = "SELECT * FROM case_timeline WHERE report_id = ? ORDER BY created_at DESC";
$timelineStmt = $conn->prepare($timelineSql);
$timelineStmt->bind_param("i", $report_id);
$timelineStmt->execute();
$timelineResult = $timelineStmt->get_result();

// --- UI Display Configuration ---
$status_config = [
    'pending'     => ['label' => 'Pending',     'icon' => 'fa-clock'],
    'in_progress' => ['label' => 'In Progress', 'icon' => 'fa-spinner'],
    'resolved'    => ['label' => 'Resolved',    'icon' => 'fa-circle-check'],
    'dismissed'   => ['label' => 'Dismissed',   'icon' => 'fa-circle-xmark'],
];
$stat = $status_config[$report['status']] ?? ['label' => ucfirst($report['status']), 'icon' => 'fa-clock'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report #<?php echo $report_id; ?> | Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../../assets/css/global/residents_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/global/residents_components.css">
    <link rel="stylesheet" href="../../assets/css/pages/view_report.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <!-- HEADER -->
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
                <div class="avatar-circle">
                    <?php echo strtoupper(substr($_SESSION['full_name'] ?: 'R', 0, 1)); ?>
                </div>
            </div>
            <a href="../../auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <!-- MAIN LAYOUT -->
    <div class="dashboard-container">
        <aside class="sidebar">
            <h3>Menu</h3>
            <ul>
                <li><a href="../dashboard/resident_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="../reports/submit_report.php"><i class="fa-solid fa-paper-plane"></i> Submit Report</a></li>
                <li><a href="../reports/my_reports.php" class="active"><i class="fa-solid fa-file-invoice"></i> My Reports</a></li>
                <li><a href="../profile/profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <main class="content">

            <!-- Page Header -->
            <div class="page-header-flex">
                <div>
                    <a href="../reports/my_reports.php" class="back-btn">
                        <i class="fa-solid fa-arrow-left"></i> Back to My Reports
                    </a>
                    <h1 class="report-title">
                        <i class="fa-solid fa-<?php echo $report['type'] === 'flood' ? 'water' : 'wrench'; ?>" style="font-size:1.4rem; color:#065f46; margin-right:0.35rem;"></i>
                        Case #<?php echo $report_id; ?>: <?php echo ucfirst($report['type']); ?> Report
                    </h1>
                    <div style="color:#6b7280; font-size:0.9rem; margin-top:0.5rem;">
                        <i class="fa-regular fa-clock"></i>
                        Submitted <?php echo date('F d, Y \a\t h:i A', strtotime($report['created_at'])); ?>
                    </div>
                </div>
                <span class="status-badge status-<?php echo $report['status']; ?>" style="padding: 0.4rem 1rem; font-size: 0.9rem; margin-top:0.5rem;">
                    <i class="fa-solid <?php echo $stat['icon']; ?>"></i>
                    <?php echo $stat['label']; ?>
                </span>
            </div>

            <!-- Two-column grid -->
            <div class="details-grid">

                <!-- â•â•â• LEFT: Incident Details + Timeline â•â•â• -->
                <div>

                    <!-- Incident Details Card -->
                    <div class="info-card">
                        <div class="card-header green">
                            <span class="card-icon green"><i class="fa-solid fa-circle-info"></i></span>
                            <h3 class="card-title">Incident Details</h3>
                        </div>
                        <div class="card-body">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                                <div class="detail-row">
                                    <div class="detail-label"><i class="fa-solid fa-bolt"></i> Incident Type</div>
                                    <div class="detail-value"><?php echo ucfirst($report['type']); ?></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label"><i class="fa-solid fa-triangle-exclamation"></i> Severity</div>
                                    <div class="detail-value">
                                        <?php
                                        $sev_icon = match($report['severity']) {
                                            'Low' => 'fa-circle-info',
                                            'Medium' => 'fa-exclamation-circle',
                                            'High' => 'fa-circle-exclamation',
                                            'Critical' => 'fa-triangle-exclamation',
                                            default => 'fa-circle-info'
                                        };
                                        ?>
                                        <span class="severity-badge severity-<?php echo $report['severity']; ?>">
                                            <i class="fa-solid <?php echo $sev_icon; ?>"></i>
                                            <?php echo htmlspecialchars($report['severity']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-label"><i class="fa-solid fa-location-dot"></i> Street</div>
                                <div class="detail-value"><?php echo htmlspecialchars($report['location']); ?></div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-label"><i class="fa-solid fa-map-pin"></i> Specific Landmark</div>
                                <div class="detail-value muted"><?php echo htmlspecialchars($report['location_details'] ?: '-'); ?></div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-label"><i class="fa-solid fa-comment-dots"></i> Description</div>
                                <div class="desc-box"><?php echo nl2br(htmlspecialchars($report['description'])); ?></div>
                            </div>

                            <?php if (!empty($report['encoder_name'])): ?>
                            <div class="detail-row" style="margin-top:1.25rem;">
                                <div class="detail-label"><i class="fa-solid fa-user-pen"></i> Encoded By (Staff)</div>
                                <div class="detail-value"><?php echo htmlspecialchars($report['encoder_name']); ?> <span style="color:#6b7280; font-size:0.82rem;">(Admin)</span></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($report['photo_path'])): ?>
                            <div class="detail-row" style="margin-top:1rem;">
                                <div class="detail-label"><i class="fa-solid fa-image"></i> Attached Photo</div>
                                <a href="../<?php echo htmlspecialchars($report['photo_path']); ?>" target="_blank" rel="noopener noreferrer" title="Click to view full size">
                                    <img src="../<?php echo htmlspecialchars($report['photo_path']); ?>" alt="Report Photo" class="attached-report-photo">
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Timeline Card -->
                    <div class="info-card">
                        <div class="card-header purple">
                            <span class="card-icon purple"><i class="fa-solid fa-clock-rotate-left"></i></span>
                            <h3 class="card-title">Case Timeline</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($timelineResult->num_rows > 0): ?>
                                <div class="timeline">
                                    <?php while ($log = $timelineResult->fetch_assoc()): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-dot"></div>
                                            <div class="timeline-status">
                                                <?php if ($log['status_from'] === $log['status_to']): ?>
                                                    <i class="fa-solid fa-note-sticky"></i> Note Added (<?php echo ucwords(str_replace('_', ' ', $log['status_to'])); ?>)
                                                <?php else: ?>
                                                    <?php echo ucwords(str_replace('_', ' ', $log['status_from'] ?? 'Reported')); ?> 
                                                    &rarr; 
                                                    <?php echo ucwords(str_replace('_', ' ', $log['status_to'])); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="timeline-meta">
                                                <i class="fa-regular fa-calendar-days"></i>
                                                <?php echo date('M d, Y', strtotime($log['created_at'])); ?>
                                                <i class="fa-regular fa-clock" style="margin-left:0.35rem;"></i>
                                                <?php echo date('h:i A', strtotime($log['created_at'])); ?>
                                            </div>
                                            <?php if (!empty($log['notes'])): ?>
                                                <div class="timeline-note">"<?php echo htmlspecialchars($log['notes']); ?>"</div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div style="text-align:center; padding:2rem; color:#9ca3af;">
                                    <i class="fa-solid fa-clock" style="font-size:2rem; display:block; margin-bottom:0.75rem; color:#d1d5db;"></i>
                                    No activity recorded yet. Your report is being reviewed.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- â•â•â• RIGHT: Status & Team Info â•â•â• -->
                <div>

                    <!-- Current Status Card -->
                    <div class="info-card">
                        <div class="card-header blue">
                            <span class="card-icon blue"><i class="fa-solid fa-clipboard-list"></i></span>
                            <h3 class="card-title">Case Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <?php
                                $status_color = match($report['status']) {
                                    'resolved' => 'green',
                                    'in_progress' => 'blue',
                                    'dismissed' => 'red',
                                    default => 'gray'
                                };
                                $status_icon_class = match($report['status']) {
                                    'resolved' => 'circle-check',
                                    'in_progress' => 'spinner',
                                    'dismissed' => 'circle-xmark',
                                    default => 'hourglass-half'
                                };
                                ?>
                                <span class="info-icon <?php echo $status_color; ?>">
                                    <i class="fa-solid fa-<?php echo $status_icon_class; ?>"></i>
                                </span>
                                <div class="info-text">
                                    <strong><?php echo $stat['label']; ?></strong>
                                    <span>
                                        <?php
                                        echo match($report['status']) {
                                            'pending'     => 'Your report has been received and is awaiting review.',
                                            'in_progress' => 'A response team is currently working on this incident.',
                                            'resolved'    => 'This incident has been resolved by the barangay team.',
                                            'dismissed'   => 'This report has been reviewed and dismissed.',
                                            default       => 'Status is being updated.'
                                        };
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($report['status'] === 'dismissed' && !empty($report['dismissal_reason'])): ?>
                            <div class="info-item" style="margin-top:0.75rem;">
                                <span class="info-icon red"><i class="fa-solid fa-ban"></i></span>
                                <div class="info-text" style="color: #991b1b;">
                                    <strong>Reason for Dismissal</strong>
                                    <span style="color: #b91c1c;"><?php echo nl2br(htmlspecialchars($report['dismissal_reason'])); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($assignedTeam): ?>
                            <div class="info-item" style="margin-top:0.75rem;">
                                <span class="info-icon green"><i class="fa-solid fa-people-group"></i></span>
                                <div class="info-text">
                                    <strong>Assigned Team</strong>
                                    <span><?php echo htmlspecialchars($assignedTeam['team_name']); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- What Happens Next -->
                    <div class="info-card">
                        <div class="card-header green">
                            <span class="card-icon green"><i class="fa-solid fa-lightbulb"></i></span>
                            <h3 class="card-title">What Happens Next</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <span class="info-icon blue"><i class="fa-solid fa-eye"></i></span>
                                <div class="info-text">
                                    <strong>Under Review</strong>
                                    <span>An admin will review and verify your report details.</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="info-icon orange"><i class="fa-solid fa-truck-fast"></i></span>
                                <div class="info-text">
                                    <strong>Team Dispatch</strong>
                                    <span>A response team will be assigned and dispatched to your location.</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="info-icon green"><i class="fa-solid fa-circle-check"></i></span>
                                <div class="info-text">
                                    <strong>Resolution</strong>
                                    <span>The status will be updated once the incident is resolved.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </main>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>
</body>
</html>

