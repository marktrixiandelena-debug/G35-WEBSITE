<?php
/**
 * Response Team Management
 * Facilitates the creation, status tracking, and monitoring of emergency response units.
 * Prevents deactivation of teams with active deployments.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Admin Verification
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

// --- Handle Status Toggle Action ---
if (isset($_POST['toggle_status'])) {
    $team_id    = $_POST['team_id'];
    $new_status = $_POST['new_status'];
    
    // Safety check: Prevent deactivation of deployed teams
    $checkSql = "
        SELECT COUNT(*) as active 
        FROM report_assignments ra
        JOIN reports r ON ra.report_id = r.id
        WHERE ra.team_id = ? AND ra.status = 'Assigned'
        AND r.status IN ('pending', 'in_progress')
    ";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $team_id);
    $checkStmt->execute();
    $isActive = $checkStmt->get_result()->fetch_assoc()['active'];

    if ($new_status === 'Inactive' && $isActive > 0) {
        $_SESSION['flash_error'] = "Cannot deactivate a team that is currently deployed.";
    } else {
        $stmt = $conn->prepare("UPDATE response_teams SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $team_id);
        
        if ($stmt->execute()) {
            require_once "../../includes/logger.php";
            logActivity($conn, $_SESSION['user_id'], 'Toggle Team Status', "Changed team ID $team_id status to $new_status");
            $_SESSION['flash_success'] = "Team status updated successfully.";
        } else {
            $_SESSION['flash_error'] = "Error updating status.";
        }
    }
    header("Location: manage_teams.php");
    exit();
}

// --- Handle Filtering & Data Fetching ---
$where_clauses = [];
$params        = [];
$types         = "";
$status_filter = $_GET['status'] ?? '';

$sql = "
    SELECT t.*, COUNT(DISTINCT ra.report_id) AS active_deployments
    FROM response_teams t
    LEFT JOIN report_assignments ra
        ON ra.team_id = t.id
        AND ra.status = 'Assigned'
        AND EXISTS (
            SELECT 1 FROM reports r
            WHERE r.id = ra.report_id
            AND r.status IN ('pending', 'in_progress')
        )
";

// Apply Contextual Filters
if ($status_filter === 'Deployed') {
    $where_clauses[] = "
        EXISTS (
            SELECT 1 FROM report_assignments ra2
            JOIN reports r2 ON r2.id = ra2.report_id
            WHERE ra2.team_id = t.id
            AND ra2.status = 'Assigned'
            AND r2.status IN ('pending', 'in_progress')
        )
    ";
} elseif ($status_filter === 'Active' || $status_filter === 'Inactive') {
    $where_clauses[] = "t.status = ?";
    $params[]        = $status_filter;
    $types          .= "s";
    
    if ($status_filter === 'Active') {
        $where_clauses[] = "
            NOT EXISTS (
                SELECT 1 FROM report_assignments ra2
                JOIN reports r2 ON r2.id = ra2.report_id
                WHERE ra2.team_id = t.id
                AND ra2.status = 'Assigned'
                AND r2.status IN ('pending', 'in_progress')
            )
        ";
    }
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " GROUP BY t.id ORDER BY t.created_at DESC";

// Execute Final Query
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teams | Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../../assets/css/global/admin_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/pages/manage_teams.css?v=2">
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
                <li><a href="manage_teams.php" class="active"><i class="fa-solid fa-people-group"></i> Manage Teams</a></li>
                <li><a href="../announcements/manage_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
                <li><a href="../users/encode_report.php"><i class="fa-solid fa-keyboard"></i> Encode Report</a></li>
                <li><a href="../reports/view_reports.php"><i class="fa-solid fa-file-alt"></i> View Reports</a></li>
                <li><a href="../logs/audit_logs.php"><i class="fa-solid fa-shield-halved"></i> Audit Logs</a></li>
                <li><a href="../profile/admin_profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <!-- CONTENT AREA -->
        <main class="content">
            <div class="page-header-flex">
                <div>
                    <h2>Response Teams</h2>
                    <p>Manage emergency response units.</p>
                </div>
                
                <div class="page-toolbar">
                    <div class="toolbar-left">
                        <!-- FILTER SECTION -->
                        <form method="GET" class="filter-section" style="margin-bottom: 0; padding: 0.5rem 0; box-shadow: none; border: none; background: transparent;">
                            <div class="filter-group">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="Deployed" <?php if (isset($_GET['status']) && $_GET['status'] == 'Deployed') echo 'selected'; ?>>Deployed</option>
                                    <option value="Active" <?php if (isset($_GET['status']) && $_GET['status'] == 'Active') echo 'selected'; ?>>Active</option>
                                    <option value="Inactive" <?php if (isset($_GET['status']) && $_GET['status'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>
                            <a href="manage_teams.php" class="btn-reset-filter" title="Clear Filters">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        </form>
                    </div>

                    <div class="toolbar-right">
                        <button type="button" onclick="openTeamModal()" class="btn-new-team">
                            <span class="btn-icon"><i class="fa-solid fa-people-group"></i></span>
                            New Team
                        </button>
                    </div>
                </div>
            </div>

            <?php require_once "../../includes/flash_toast.php"; ?>

            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Team Name</th>
                        <th>Leader</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Team Name"><strong>
                                        <?php echo htmlspecialchars($row['team_name']); ?>
                                    </strong></td>
                                <td data-label="Leader">
                                    <?php echo htmlspecialchars($row['team_leader']); ?>
                                </td>
                                <td data-label="Contact">
                                    <?php echo htmlspecialchars($row['contact_number']); ?>
                                </td>
                                <td data-label="Status" style="white-space: nowrap;">
                                    <?php if ($row['active_deployments'] > 0): ?>
                                        <span class="status-badge status-deployed">
                                            <i class="fa-solid fa-truck-fast"></i> Deployed
                                        </span>
                                    <?php elseif ($row['status'] === 'Active'): ?>
                                        <span class="status-badge status-active">
                                            <i class="fa-solid fa-circle-check"></i> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">
                                            <i class="fa-solid fa-circle-minus"></i> Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Action" style="white-space: nowrap;">
                                    <!-- Edit Button -->
                                    <button type="button"
                                        onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo addslashes(htmlspecialchars($row['team_name'])); ?>', '<?php echo addslashes(htmlspecialchars($row['team_leader'])); ?>', '<?php echo addslashes(htmlspecialchars($row['contact_number'])); ?>', '<?php echo $row['status']; ?>')"
                                        class="action-btn btn-edit">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    <!-- Toggle Status Button -->
                                    <?php if ($row['active_deployments'] > 0): ?>
                                        <span class="action-btn" style="background:#fefce8; color:#92400e; border:1px solid #fbbf24; cursor:default; opacity:0.85;" title="Cannot change status while team is deployed to an active report.">
                                            <i class="fa-solid fa-lock"></i> Deployed
                                        </span>
                                    <?php elseif ($row['status'] === 'Active'): ?>
                                        <form method="POST" style="display:inline; margin-left:0.4rem;">
                                            <input type="hidden" name="team_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="new_status" value="Inactive">
                                            <input type="hidden" name="toggle_status" value="1">
                                            <button type="submit" class="action-btn btn-delete" style="background-color: #fce7e7; color: #991b1b; border-color: #f87171;"
                                                    onclick="return confirm('Deactivate this team? They will no longer appear in the assignment list.');">
                                                <i class="fa-solid fa-ban"></i> Deactivate
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display:inline; margin-left:0.4rem;">
                                            <input type="hidden" name="team_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="new_status" value="Active">
                                            <input type="hidden" name="toggle_status" value="1">
                                            <button type="submit" class="action-btn btn-edit" style="background-color: #ecfdf5; color: #065f46; border-color: #34d399;"
                                                    onclick="return confirm('Activate this team?');">
                                                <i class="fa-solid fa-check-circle"></i> Activate
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #6b7280; padding: 2rem;">No response teams
                                found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>

        </main>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag | Administrative Panel</p>
    </footer>

    <!-- CREATE TEAM MODAL Form -->
    <div id="teamModal" class="form-modal">
        <div class="modal-content" style="max-width: 500px;">
            <span class="btn-close" onclick="closeTeamModal()">&times;</span>
            <h3 id="modalTitle" style="margin-top: 0; margin-bottom: 1.5rem; color: #111827; font-size: 1.25rem;">
                <i class="fa-solid fa-people-group" style="color: #059669; margin-right: 0.5rem;"></i> New Response Team
            </h3>
            
            <form action="process_team.php" method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label>Team Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="team_name" class="form-control" placeholder="e.g. Alpha Team, Quick Response 1" required>
                </div>

                <div class="form-group">
                    <label>Team Leader <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="team_leader" class="form-control" placeholder="Full Name" required>
                </div>

                <div class="form-group">
                    <label>Contact Number <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="contact_number" class="form-control" placeholder="09xxxxxxxxx" maxlength="11" required>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                    <small style="color:#6b7280; font-size:0.8rem; display:block; margin-top:0.4rem;">
                        Deployment status is determined automatically from active report assignments.
                    </small>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                    <button type="button" onclick="closeTeamModal()" class="btn-cancel" style="padding: 0.6rem 1.25rem; border-radius: 0.375rem; background: white; border: 1px solid #d1d5db; color: #4b5563; font-weight: 500; cursor: pointer;">Cancel</button>
                    <button type="submit" class="btn-submit" style="width: auto; padding: 0.6rem 1.5rem; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 0.375rem;"><i class="fa-solid fa-save"></i> Save Team</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT TEAM MODAL -->
    <div id="editTeamModal" class="form-modal">
        <div class="modal-content" style="max-width: 500px;">
            <span class="btn-close" onclick="closeEditModal()">&times;</span>
            <h3 id="editModalTitle" style="margin-top: 0; margin-bottom: 1.5rem; color: #111827; font-size: 1.25rem;">
                <i class="fa-solid fa-pen-to-square" style="color: #059669; margin-right: 0.5rem;"></i> Edit Response Team
            </h3>

            <form action="process_team.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="team_id" id="editTeamId">

                <div class="form-group">
                    <label>Team Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="editTeamName" name="team_name" class="form-control"
                        placeholder="e.g. Alpha Team, Quick Response 1" required>
                </div>

                <div class="form-group">
                    <label>Team Leader <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="editTeamLeader" name="team_leader" class="form-control"
                        placeholder="Full Name" required>
                </div>

                <div class="form-group">
                    <label>Contact Number <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="editTeamContact" name="contact_number" class="form-control"
                        placeholder="09xxxxxxxxx" maxlength="11" required>
                </div>

                <div class="form-group">
                    <label>Base Status</label>
                    <select id="editTeamStatus" name="status" class="form-control">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                    <small style="color:#6b7280; font-size:0.8rem; display:block; margin-top:0.4rem;">
                        Deployment status is determined automatically from active report assignments.
                    </small>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem;">
                    <button type="button" onclick="closeEditModal()" class="btn-cancel"
                        style="padding:0.6rem 1.25rem; border-radius:0.375rem; background:white; border:1px solid #d1d5db; color:#4b5563; font-weight:500; cursor:pointer;">Cancel</button>
                    <button type="submit" class="btn-submit"
                        style="width:auto; padding:0.6rem 1.5rem; font-size:0.95rem; display:inline-flex; align-items:center; gap:0.5rem; border-radius:0.375rem;">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/global/sidebar.js"></script>
    <script src="../../assets/js/pages/manage_teams.js?v=3"></script>
</body>

</html>

