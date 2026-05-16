<?php
/**
 * Admin Report Detailed View
 * Provides full case oversight, evidence review, team assignment, and status management.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Admin Verification
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: view_reports.php");
    exit();
}

$report_id = (int)$_GET['id'];

// --- Fetch Complete Report Details ---
$sql = "SELECT r.*, u.full_name, u.contact_number, enc.full_name AS encoder_name
        FROM reports r
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN users enc ON r.encoded_by = enc.id
        WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    die("Report not found.");
}

// --- Fetch Currently Assigned Team ---
$teamSql = "SELECT ra.team_id, t.team_name, ra.status as assignment_status
            FROM report_assignments ra
            JOIN response_teams t ON ra.team_id = t.id
            WHERE ra.report_id = ? AND ra.status = 'Assigned'
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

// --- Fetch Available Dispatch Teams ---
$allTeams = $conn->query("SELECT * FROM response_teams WHERE status = 'Active'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report #<?php echo $report_id; ?> | Admin Panel</title>
    <link rel="stylesheet" href="../../assets/css/global/admin_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
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
                <li><a href="../dashboard/admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="../dashboard/analytics.php"><i class="fa-solid fa-chart-bar"></i> Analytics</a></li>
                <li><a href="../users/manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="../teams/manage_teams.php"><i class="fa-solid fa-people-group"></i> Manage Teams</a></li>
                <li><a href="../announcements/manage_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
                <li><a href="../users/encode_report.php"><i class="fa-solid fa-keyboard"></i> Encode Report</a></li>
                <li><a href="view_reports.php" class="active"><i class="fa-solid fa-file-alt"></i> View Reports</a></li>
                <li><a href="../logs/audit_logs.php"><i class="fa-solid fa-shield-halved"></i> Audit Logs</a></li>
                <li><a href="../profile/admin_profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <!-- CONTENT -->
        <main class="content">

            <?php require_once "../../includes/flash_toast.php"; ?>
            <!-- PAGE HEADER -->
            <div class="page-header-flex">
                <div>
                    <a href="view_reports.php" class="back-btn">
                        <i class="fa-solid fa-arrow-left"></i> Back to Reports
                    </a>
                    <h1 class="report-title">
                        <i class="fa-solid fa-<?php echo $report['type'] === 'flood' ? 'water' : 'wrench'; ?>" style="font-size:1.4rem; color:#065f46; margin-right:0.35rem;"></i>
                        Case #<?php echo $report_id; ?>: <?php echo ucfirst($report['type']); ?> Report
                    </h1>
                    <div style="color:#6b7280; font-size:0.9rem; margin-top:0.5rem; display:flex; align-items:center; gap:0.4rem;">
                        <i class="fa-regular fa-clock"></i>
                        Submitted <?php echo date('F d, Y \a\t h:i A', strtotime($report['created_at'])); ?>
                    </div>
                </div>
                <?php
                $status_config = [
                    'pending'     => ['label' => 'Pending',     'icon' => 'fa-clock'],
                    'in_progress' => ['label' => 'In Progress', 'icon' => 'fa-spinner'],
                    'resolved'    => ['label' => 'Resolved',    'icon' => 'fa-circle-check'],
                    'dismissed'   => ['label' => 'Dismissed',   'icon' => 'fa-circle-xmark'],
                ];
                $stat = $status_config[$report['status']] ?? ['label' => ucfirst($report['status']), 'icon' => 'fa-clock'];
                ?>
                <span class="status-badge status-<?php echo $report['status']; ?>" style="padding:0.4rem 1rem; font-size:0.9rem; margin-top:0.5rem;">
                    <i class="fa-solid <?php echo $stat['icon']; ?>"></i>
                    <?php echo $stat['label']; ?>
                </span>
            </div>

            <!-- TWO-COLUMN GRID -->
            <div class="details-grid">

                <!-- ---- LEFT: Incident Details + Timeline ---- -->
                <div>

                    <!-- Incident Details Card -->
                    <div class="info-card">
                        <div class="card-header green">
                            <span class="card-icon green"><i class="fa-solid fa-circle-info"></i></span>
                            <h3 class="card-title">Incident Details</h3>
                        </div>
                        <div class="card-body">

                            <!-- Incident Type + Severity row -->
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                                <div class="detail-row">
                                    <div class="detail-label"><i class="fa-solid fa-bolt"></i> Incident Type</div>
                                    <div class="detail-value"><?php echo ucfirst($report['type']); ?></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label"><i class="fa-solid fa-triangle-exclamation"></i> Severity</div>
                                    <div class="detail-value">
                                        <?php
                                        $sev_icon = match ($report['severity']) {
                                            'Low'      => 'fa-circle-info',
                                            'Medium'   => 'fa-exclamation-circle',
                                            'High'     => 'fa-circle-exclamation',
                                            'Critical' => 'fa-triangle-exclamation',
                                            default    => 'fa-circle-info'
                                        };
                                        ?>
                                        <span class="severity-badge severity-<?php echo $report['severity'] ?? 'Low'; ?>">
                                            <i class="fa-solid <?php echo $sev_icon; ?>"></i>
                                            <?php echo htmlspecialchars($report['severity'] ?? 'Low'); ?>
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

                            <?php
                            $src     = $report['report_source'] ?? 'Online';
                            $src_cfg = [
                                'Online'        => ['color' => '#065f46', 'bg' => '#dcfce7', 'icon' => 'fa-wifi',           'label' => 'Online Submission'],
                                'Walk-In'       => ['color' => '#3730a3', 'bg' => '#e0e7ff', 'icon' => 'fa-person-walking', 'label' => 'Walk-In (Barangay Office)'],
                                'Phone Call'    => ['color' => '#065f46', 'bg' => '#d1fae5', 'icon' => 'fa-phone',          'label' => 'Phone Call'],
                                'Offline Batch' => ['color' => '#92400e', 'bg' => '#fef3c7', 'icon' => 'fa-file-pen',       'label' => 'Offline Batch Entry'],
                            ];
                            $cfg = $src_cfg[$src] ?? $src_cfg['Online'];
                            ?>
                            <div class="detail-row" style="margin-top:1rem;">
                                <div class="detail-label"><i class="fa-solid fa-signal"></i> Report Source</div>
                                <div class="detail-value">
                                    <span class="source-badge" style="background:<?php echo $cfg['bg']; ?>; color:<?php echo $cfg['color']; ?>;">
                                        <i class="fa-solid <?php echo $cfg['icon']; ?>"></i> <?php echo $cfg['label']; ?>
                                    </span>
                                </div>
                            </div>

                            <?php if (!empty($report['encoder_name'])): ?>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fa-solid fa-user-pen"></i> Encoded By (Staff)</div>
                                <div class="detail-value"><?php echo htmlspecialchars($report['encoder_name']); ?> <span style="color:#6b7280; font-size:0.82rem;">(Admin)</span></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($report['photo_path'])): ?>
                            <div class="detail-row" style="margin-top:1rem;">
                                <div class="detail-label"><i class="fa-solid fa-image"></i> Attached Photo</div>
                                <a href="../../resident/<?php echo htmlspecialchars($report['photo_path']); ?>" target="_blank" rel="noopener noreferrer" title="Click to view full size">
                                    <img src="../../resident/<?php echo htmlspecialchars($report['photo_path']); ?>" alt="Report Photo" class="attached-report-photo">
                                </a>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div><!-- /Incident Details Card -->

                    <!-- Case Timeline Card -->
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
                                                    <?php echo ucwords(str_replace('_', ' ', $log['status_from'] ?? 'Reported')); ?> &rarr; <?php echo ucwords(str_replace('_', ' ', $log['status_to'])); ?>
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
                                    <i class="fa-solid fa-box-open" style="font-size:2rem; display:block; margin-bottom:0.5rem;"></i>
                                    No activity recorded yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div><!-- /Timeline Card -->

                </div><!-- /LEFT COLUMN -->

                <!-- ---- RIGHT: Actions + Reporter ---- -->
                <div>

                    <!-- Actions Card -->
                    <div class="info-card">
                        <div class="card-header blue">
                            <span class="card-icon blue"><i class="fa-solid fa-list-check"></i></span>
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">

                            <div class="info-highlight" style="margin-bottom: 1.5rem;">
                                <i class="fa-solid fa-circle-info info-icon"></i>
                                <div class="info-content">
                                    <h4>Action Tracking:</h4>
                                    <p>All status updates, team assignments, and resolutions are officially recorded in the Case Timeline and Audit Logs.</p>
                                </div>
                            </div>

                            <?php if ($report['status'] === 'dismissed'): ?>
                                <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:0.5rem; padding:1rem; color:#991b1b;">
                                    <strong style="display:block; margin-bottom:0.35rem;"><i class="fa-solid fa-circle-xmark"></i> Report Dismissed</strong>
                                    <p style="margin:0; font-size:0.82rem; line-height:1.5; font-style:italic;">"<?php echo nl2br(htmlspecialchars($report['dismissal_reason'])); ?>"</p>
                                </div>

                            <?php elseif ($report['status'] === 'pending'): ?>
                                <!-- Verify flow -->
                                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:0.5rem; padding:1.25rem; margin-bottom:1.5rem;">
                                    <h4 style="margin:0 0 0.5rem 0; color:#0f172a; font-size:0.88rem;">Verify Submission</h4>
                                    <p style="margin:0 0 1rem 0; font-size:0.8rem; color:#64748b; line-height:1.5;">Verified reports move to "In Progress" and can be assigned a team.</p>
                                    <form action="process_report_action.php" method="POST">
                                        <input type="hidden" name="report_id" value="<?php echo $report_id; ?>">
                                        <input type="hidden" name="current_status" value="pending">
                                        <input type="hidden" name="new_status" value="in_progress">
                                        <button type="submit" class="btn-submit" style="width:100%; display:flex; align-items:center; justify-content:center; gap:0.5rem;">
                                            <i class="fa-solid fa-clipboard-check"></i> Verify &amp; Accept Report
                                        </button>
                                    </form>
                                </div>
                                <div style="border-top:1px solid #e2e8f0; padding-top:1.25rem;">
                                    <h4 style="margin:0 0 0.75rem 0; color:#b91c1c; font-size:0.88rem;"><i class="fa-solid fa-triangle-exclamation"></i> Dismiss Report</h4>
                                    <form action="process_dismiss_report.php" method="POST">
                                        <input type="hidden" name="report_id" value="<?php echo $report_id; ?>">
                                        <div class="form-group" style="margin-bottom:0.75rem;">
                                            <select name="reason_type" class="form-control" required style="font-size:0.85rem;">
                                                <option value="">-- Select Reason --</option>
                                                <option value="Spam / Troll Report">Spam / Troll Report</option>
                                                <option value="Duplicate Report">Duplicate Report</option>
                                                <option value="Mistaken / False Report">Mistaken / False Report</option>
                                                <option value="Insufficient Information">Insufficient Information</option>
                                                <option value="Outside Jurisdiction">Outside Jurisdiction</option>
                                                <option value="Other">Other (Please specify below)</option>
                                            </select>
                                        </div>
                                        <div class="form-group" style="margin-bottom:1rem;">
                                            <textarea name="additional_reason" class="form-control" rows="2" placeholder="Optional clarification..." style="font-size:0.85rem;"></textarea>
                                        </div>
                                        <button type="submit" class="btn-submit" style="width:100%; background:#fef2f2; border:1px solid #fca5a5; color:#dc2626; box-shadow:none;" onclick="return confirm('Dismiss this report? This action cannot be undone.');">
                                            <i class="fa-solid fa-circle-xmark"></i> Dismiss Report
                                        </button>
                                    </form>
                                </div>

                            <?php elseif ($report['status'] === 'resolved'): ?>
                                <!-- Resolved: read-only finalized state -->
                                <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:0.5rem; padding:1rem; color:#166534;">
                                    <strong style="display:block; margin-bottom:0.35rem;"><i class="fa-solid fa-circle-check"></i> Report Resolved</strong>
                                    <p style="margin:0; font-size:0.82rem; line-height:1.5;">This report has been resolved and is now read-only. No further changes can be made.</p>
                                </div>
                                <?php if (isset($assignedTeam)): ?>
                                    <div style="margin-top:1rem; font-size:0.85rem; color:#374151; background:#ecfdf5; border:1px solid #c6f6d5; border-radius:0.375rem; padding:0.75rem; display:flex; align-items:center; gap:0.5rem;">
                                        <i class="fa-solid fa-people-group" style="color:#059669;"></i>
                                        Last assigned team: <strong><?php echo htmlspecialchars($assignedTeam['team_name']); ?></strong>
                                    </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <!-- Standard update form (in_progress only) -->
                                <form action="process_report_action.php" method="POST">
                                    <input type="hidden" name="report_id" value="<?php echo $report_id; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $report['status']; ?>">

                                    <div class="detail-row">
                                        <div class="detail-label"><i class="fa-solid fa-rotate"></i> Update Status</div>
                                        <select name="new_status" class="form-control" style="font-size:0.9rem;">
                                            <option value="in_progress" <?php echo ($report['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="resolved"    <?php echo ($report['status'] == 'resolved')    ? 'selected' : ''; ?>>Resolved</option>
                                        </select>
                                    </div>

                                    <div class="detail-row">
                                        <div class="detail-label"><i class="fa-solid fa-people-group"></i> Assign Response Team</div>
                                        <select name="team_id" class="form-control" style="font-size:0.9rem;">
                                            <option value="">-- Select Team --</option>
                                            <?php while ($team = $allTeams->fetch_assoc()): ?>
                                                <option value="<?php echo $team['id']; ?>" <?php echo (isset($assignedTeam) && $assignedTeam['team_id'] == $team['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($team['team_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <?php if (isset($assignedTeam)): ?>
                                            <div id="teamAssignmentFeedback" style="margin-top:0.5rem; font-size:0.8rem; color:#059669; background:#ecfdf5; padding:0.5rem; border-radius:0.375rem; border:1px solid #c6f6d5; display:flex; align-items:center; gap:0.4rem; transition: opacity 0.5s ease;">
                                                <i class="fa-solid fa-check-circle"></i> Current Team: <strong><?php echo $assignedTeam['team_name']; ?></strong>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="detail-row">
                                        <div class="detail-label"><i class="fa-solid fa-pen-to-square"></i> Case Notes</div>
                                        <textarea name="notes" class="form-control" rows="3" placeholder="Add notes about this update..." style="font-size:0.9rem;"></textarea>
                                    </div>

                                    <button type="submit" class="btn-submit" style="width:100%;">
                                        <i class="fa-solid fa-save"></i> Save Changes
                                    </button>
                                </form>
                            <?php endif; ?>

                        </div>
                    </div><!-- /Actions Card -->

                    <!-- Reporter Info Card -->
                    <div class="info-card">
                        <div class="card-header blue">
                            <span class="card-icon blue"><i class="fa-solid fa-user-circle"></i></span>
                            <h3 class="card-title">Reporter Info</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($report['full_name'])): ?>
                                <div class="detail-row">
                                    <div class="detail-label">Full Name</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($report['full_name']); ?></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Contact Number</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($report['contact_number'] ?: 'Not Provided'); ?></div>
                                </div>
                            <?php elseif (!empty($report['guest_name'])): ?>
                                <div class="detail-row">
                                    <div class="detail-label">Guest Name</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($report['guest_name']); ?></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Guest Contact</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($report['guest_contact'] ?: 'Not Provided'); ?></div>
                                </div>
                            <?php else: ?>
                                <div style="text-align:center; padding:1rem; color:#9ca3af; font-size:0.88rem;">
                                    <i>Anonymous / Unknown Reporter</i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div><!-- /Reporter Card -->

                </div><!-- /RIGHT COLUMN -->
            </div><!-- /details-grid -->

        </main>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag | Administrative Panel</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const teamFeedback = document.getElementById('teamAssignmentFeedback');
            if (teamFeedback) {
                setTimeout(() => {
                    teamFeedback.style.opacity = '0';
                    setTimeout(() => {
                        if (teamFeedback.parentNode) {
                            teamFeedback.remove();
                        }
                    }, 500); // 500ms after opacity hits 0
                }, 2000); // Fade starts after 2 seconds
            }
        });
    </script>
</body>

</html>

