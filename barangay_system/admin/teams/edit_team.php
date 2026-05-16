<?php
/**
 * Response Team Modification
 * Allows administrative updates to team metadata (Name, Leader, Contact).
 * Base status can be manually toggled here for unit availability.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Admin Verification
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

// --- Team Validation ---
$team_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$team_id) {
    header("Location: manage_teams.php");
    exit();
}

// Fetch existing unit details
$stmt = $conn->prepare("SELECT * FROM response_teams WHERE id = ?");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$result = $stmt->get_result();
$team   = $result->fetch_assoc();

if (!$team) {
    header("Location: manage_teams.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Team | Barangay Calauag</title>
    <link rel="stylesheet" href="../../assets/css/global/admin_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/pages/edit_team.css">
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

        <main class="content">
            <div class="page-header">
                <h2>Edit Response Team</h2>
                <p>Update the details for <strong><?php echo htmlspecialchars($team['team_name']); ?></strong>.</p>
            </div>

            <div class="form-container">
                <div class="form-section-header">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <h3>Team Information</h3>
                </div>

                <form action="process_team.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="team_id" value="<?php echo $team['id']; ?>">

                    <div class="form-group">
                        <label>Team Name</label>
                        <input type="text" name="team_name" class="form-control"
                            value="<?php echo htmlspecialchars($team['team_name']); ?>"
                            placeholder="e.g. Alpha Team, Quick Response 1" required>
                    </div>

                    <div class="form-group">
                        <label>Team Leader</label>
                        <input type="text" name="team_leader" class="form-control"
                            value="<?php echo htmlspecialchars($team['team_leader']); ?>"
                            placeholder="Full Name" required>
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" class="form-control"
                            value="<?php echo htmlspecialchars($team['contact_number']); ?>"
                            placeholder="09xxxxxxxxx" required>
                    </div>

                    <div class="form-group">
                        <label>Base Status</label>
                        <select name="status" class="form-control">
                            <option value="Active"   <?php if ($team['status'] === 'Active')   echo 'selected'; ?>>Active</option>
                            <option value="Inactive" <?php if ($team['status'] === 'Inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                        <small style="color: #6b7280; font-size: 0.8rem; display: block; margin-top: 0.4rem;">
                            Deployment status is determined automatically from active report assignments.
                        </small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fa-solid fa-floppy-disk"></i> Save Changes
                        </button>
                        <a href="manage_teams.php" class="btn-cancel">
                            <i class="fa-solid fa-xmark"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag | Administrative Panel</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>
</body>

</html>

