<?php
/**
 * Public Announcement Management
 * Allows administrators to broadcast advisories and system updates to all residents.
 * Includes status toggling for immediate visibility control.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Admin Verification
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

// --- Handle Filtering Logic ---
$where_clauses = [];
$params        = [];
$types         = "";

// Category Filter
if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where_clauses[] = "a.type = ?";
    $params[]        = $_GET['type'];
    $types          .= "s";
}

// Visibility Filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_clauses[] = "a.status = ?";
    $params[]        = $_GET['status'];
    $types          .= "s";
}

// --- Fetch Filtered Announcements ---
$sql = "SELECT a.*, u.full_name as author_name 
        FROM announcements a 
        JOIN users u ON a.created_by = u.id";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY a.created_at DESC";

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
    <title>Manage Announcements | Admin Panel</title>
    <link rel="stylesheet" href="../../assets/css/global/admin_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/pages/manage_announcements.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header class="dashboard-header">
        <div class="brand-section">
            <button id="sidebarToggle" class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
            <img src="../../assets/images/barangay_logo.jpg" alt="Logo" class="logo-lg">
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

                <li><a href="../users/manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="../teams/manage_teams.php"><i class="fa-solid fa-people-group"></i> Manage Teams</a></li>
                <li><a href="manage_announcements.php" class="active"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
                <li><a href="../users/encode_report.php"><i class="fa-solid fa-keyboard"></i> Encode Report</a></li>
                <li><a href="../reports/view_reports.php"><i class="fa-solid fa-file-alt"></i> View Reports</a></li>
                <li><a href="../logs/audit_logs.php"><i class="fa-solid fa-shield-halved"></i> Audit Logs</a></li>
                <li><a href="../profile/admin_profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <main class="content">
            <div class="page-header-flex">
                <div>
                    <h2>Manage Announcements</h2>
                    <p>Create and post advisories for residents.</p>
                </div>
                
                <div class="page-toolbar">
                    <div class="toolbar-left">
                        <!-- FILTER SECTION -->
                        <form method="GET" class="filter-section" style="margin-bottom: 0; padding: 0.5rem 0; box-shadow: none; border: none; background: transparent;">
                            <div class="filter-group">
                                <select name="type" onchange="this.form.submit()">
                                    <option value="">All Types</option>
                                    <option value="General" <?php if (isset($_GET['type']) && $_GET['type'] == 'General') echo 'selected'; ?>>General</option>
                                    <option value="Advisory" <?php if (isset($_GET['type']) && $_GET['type'] == 'Advisory') echo 'selected'; ?>>Advisory</option>
                                    <option value="System Update" <?php if (isset($_GET['type']) && $_GET['type'] == 'System Update') echo 'selected'; ?>>System Update</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="Active" <?php if (isset($_GET['status']) && $_GET['status'] == 'Active') echo 'selected'; ?>>Active</option>
                                    <option value="Inactive" <?php if (isset($_GET['status']) && $_GET['status'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>
                            <a href="manage_announcements.php" class="btn-reset-filter" title="Clear Filters">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        </form>
                    </div>

                    <div class="toolbar-right">
                        <button onclick="openModal()" class="btn-new-announcement">
                            <span class="btn-icon"><i class="fa-solid fa-bullhorn"></i></span>
                            New Announcement
                        </button>
                    </div>
                </div>
            </div>

            <?php require_once "../../includes/flash_toast.php"; ?>

            <div class="info-highlight" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-info info-icon"></i>
                <div class="info-content">
                    <h4>Public Visibility & Status:</h4>
                    <p><strong>Active</strong> announcements are immediately visible on all resident dashboards. Set the status to <strong>Inactive</strong> to safely hide an advisory without deleting it.</p>
                </div>
            </div>

            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%;">Title</th>
                        <th style="width: 15%;">Type</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;">Author</th>
                        <th style="width: 15%;">Date Posted</th>
                        <th style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td data-label="Title" style="font-weight: 500;"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td data-label="Type">
                                        <?php 
                                            $typeClass = 'badge-general';
                                            if ($row['type'] == 'System Update') $typeClass = 'badge-system';
                                            if ($row['type'] == 'Advisory') $typeClass = 'badge-advisory';
                                        ?>
                                        <span class="badge <?php echo $typeClass; ?>"><?php echo $row['type']; ?></span>
                                    </td>
                                    <td data-label="Status">
                                        <span class="status-badge <?php echo $row['status'] == 'Active' ? 'status-active' : 'status-inactive'; ?>">
                                            <i class="fa-solid <?php echo $row['status'] == 'Active' ? 'fa-circle-check' : 'fa-circle-minus'; ?>"></i>
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td data-label="Author"><?php echo htmlspecialchars($row['author_name']); ?></td>
                                    <td data-label="Date Posted" style="font-size: 0.9rem; color: #4b5563;"><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></td>
                                    <td data-label="Actions" style="white-space: nowrap;">
                                        <button onclick='editModal(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)' class="action-btn btn-edit" title="Edit">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </button>
                                        <form method="POST" action="process_announcement.php" style="display: inline; margin-left: 0.4rem;" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="action-btn btn-delete" title="Delete">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #6b7280; padding: 2rem;">No announcements found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- FOOTER -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag | Administrative Panel</p>
    </footer>

    <!-- Modal Form -->
    <div id="announcementModal" class="form-modal">
        <div class="modal-content">
            <span class="btn-close" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle" style="margin-top: 0; margin-bottom: 1.5rem;">New Announcement</h3>
            
            <form action="process_announcement.php" method="POST">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="announcementId" value="">
                
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Type</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="General">General</option>
                        <option value="Advisory">Advisory</option>
                        <option value="System Update">System Update</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" id="content" class="form-control" rows="5" required></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                    <button type="submit" class="btn-submit" style="width: auto; padding: 0.6rem 1.5rem; font-size: 0.95rem; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 0.375rem;"><i class="fa-solid fa-save"></i> Save Announcement</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/global/sidebar.js"></script>
    <script src="../../assets/js/pages/manage_announcements.js"></script>
</body>
</html>

