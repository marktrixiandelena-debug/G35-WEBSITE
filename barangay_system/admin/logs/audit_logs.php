<?php
/**
 * System Audit Infrastructure
 * Provides immutable tracking of all administrative actions and system events.
 * Includes category-based filtering for security investigations.
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

// Action Category Filter
if (isset($_GET['action_filter']) && !empty($_GET['action_filter'])) {
    $where_clauses[] = "l.action = ?";
    $params[]        = $_GET['action_filter'];
    $types          .= "s";
}

$base_where = "";
if (!empty($where_clauses)) {
    $base_where = " WHERE " . implode(" AND ", $where_clauses);
}

// --- Pagination Configuration ---
$limit  = 10;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// --- Fetch Total Row Count ---
$count_sql = "SELECT COUNT(*) as total FROM audit_logs l" . $base_where;
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $count_result = $conn->query($count_sql);
    $total_rows = $count_result->fetch_assoc()['total'];
}

$total_pages = ceil($total_rows / $limit);
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// --- Fetch Filtered Data ---
$sql = "SELECT l.*, u.full_name, u.username FROM audit_logs l JOIN users u ON l.user_id = u.id" . $base_where . " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";

if (!empty($params)) {
    $queryParams = $params;
    $queryParams[] = $limit;
    $queryParams[] = $offset;
    $queryTypes = $types . "ii";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($queryTypes, ...$queryParams);
    $stmt->execute();
    $logs = $stmt->get_result();
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $logs = $stmt->get_result();
}

// --- UI Helper Logic ---
$urlParams = $_GET;
unset($urlParams['page']);
$queryString = http_build_query($urlParams);
$paginationUrl = "?" . (!empty($queryString) ? $queryString . "&" : "") . "page=";

// Record summary variables
$start_record = ($total_rows == 0) ? 0 : $offset + 1;
$end_record   = min($offset + $limit, $total_rows);


// --- Dynamic Filter Logic ---
// Fetch all unique actions recorded in the system for the dropdown
$actions_query = $conn->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC");
$available_actions = [];
if ($actions_query && $actions_query->num_rows > 0) {
    while ($act = $actions_query->fetch_assoc()) {
        $available_actions[] = $act['action'];
    }
}

// --- UI Helper Functions ---
/**
 * Determines badge aesthetic based on action intent
 */
function getActionBadgeClass($action) {
    $action_lower = strtolower($action);
    if (strpos($action_lower, 'create') !== false || strpos($action_lower, 'add') !== false || strpos($action_lower, 'register') !== false) {
        return 'badge-create';
    }
    if (strpos($action_lower, 'delete') !== false || strpos($action_lower, 'remove') !== false) {
        return 'badge-delete';
    }
    if (strpos($action_lower, 'update') !== false || strpos($action_lower, 'edit') !== false || strpos($action_lower, 'modify') !== false || strpos($action_lower, 'reset') !== false) {
        return 'badge-update';
    }
    if (strpos($action_lower, 'login') !== false || strpos($action_lower, 'logout') !== false) {
        return 'badge-auth';
    }
    if (strpos($action_lower, 'enable') !== false || strpos($action_lower, 'disable') !== false || strpos($action_lower, 'status') !== false) {
        return 'badge-state';
    }
    return ''; // Default fallback
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs | Barangay Calauag</title>
    <link rel="stylesheet" href="../../assets/css/global/admin_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/pages/audit_logs.css">
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
                <li><a href="../teams/manage_teams.php"><i class="fa-solid fa-people-group"></i> Manage Teams</a></li>
                <li><a href="../announcements/manage_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
                <li><a href="../users/encode_report.php"><i class="fa-solid fa-keyboard"></i> Encode Report</a></li>
                <li><a href="../reports/view_reports.php"><i class="fa-solid fa-file-alt"></i> View Reports</a></li>
                <li><a href="audit_logs.php" class="active"><i class="fa-solid fa-shield-halved"></i> Audit Logs</a></li>
                <li><a href="../profile/admin_profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <!-- CONTENT -->
        <main class="content">
            <?php require_once "../../includes/flash_toast.php"; ?>

            <!-- PAGE HEADER -->
            <div class="page-header-flex">
                <div>
                    <h2>System Audit Logs</h2>
                    <p>Track administrative actions, system events, and data modifications.</p>
                </div>
                
                <div class="page-toolbar">
                    <!-- FILTER SECTION -->
                    <form method="GET" class="filter-section" style="margin-bottom: 0; padding: 0.5rem 0; box-shadow: none; border: none; background: transparent;">
                        <div class="filter-group">
                            <div style="position: relative;">
                                <!-- Using a custom icon wrapper if we want, but select has a background chevron -->
                                <select name="action_filter" onchange="this.form.submit()">
                                    <option value="">All Actions</option>
                                    <?php foreach ($available_actions as $action_val): ?>
                                        <option value="<?php echo htmlspecialchars($action_val); ?>" <?php if (isset($_GET['action_filter']) && $_GET['action_filter'] === $action_val) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($action_val); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <a href="audit_logs.php" class="btn-reset-filter" title="Clear Filters">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    </form>
                </div>
            </div>

            <div style="margin-bottom: 0.75rem; color: #6b7280; font-size: 0.875rem;">
                Showing <strong><?php echo $start_record; ?>&ndash;<?php echo $end_record; ?></strong> of <strong><?php echo number_format($total_rows); ?></strong> log<?php echo $total_rows != 1 ? 's' : ''; ?>
            </div>

            <!-- TABLE CONTAINER -->
            <div class="table-responsive">
                <table>
                    <thead>
                            <th style="width: 15%;">Timestamp</th>
                            <th style="width: 25%;">User Account</th>
                            <th style="width: 15%;">Action</th>
                            <th style="width: 45%;">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs && $logs->num_rows > 0): ?>
                            <?php while ($row = $logs->fetch_assoc()): ?>
                                <tr>
                                    <td data-label="Timestamp" class="col-time">
                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?><br>
                                        <span style="color: #94a3b8; font-size: 0.75rem;"><?php echo date('h:i:s A', strtotime($row['created_at'])); ?></span>
                                    </td>
                                    
                                    <td data-label="User Account" class="col-user">
                                        <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                        <small>@<?php echo htmlspecialchars($row['username']); ?></small>
                                    </td>
                                    
                                    <td data-label="Action">
                                        <span class="action-badge <?php echo getActionBadgeClass($row['action']); ?>">
                                            <?php echo htmlspecialchars($row['action']); ?>
                                        </span>
                                    </td>
                                    
                                    <td data-label="Details" class="col-details">
                                        <?php echo htmlspecialchars($row['details']); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #64748b; padding: 3rem 1rem;">
                                    <i class="fa-solid fa-folder-open" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 0.75rem; display: block;"></i>
                                    No audit logs found matching the selected filter.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <?php if ($page > 1): ?>
                    <a href="<?php echo $paginationUrl . ($page - 1); ?>" class="page-link">&laquo; Prev</a>
                <?php else: ?>
                    <span class="page-link disabled">&laquo; Prev</span>
                <?php endif; ?>

                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($total_pages, $page + 2);
                
                if ($startPage > 1) echo '<span class="page-ellipsis">...</span>';
                
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="<?php echo $paginationUrl . $i; ?>" class="page-link <?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; 
                
                if ($endPage < $total_pages) echo '<span class="page-ellipsis">...</span>';
                ?>

                <?php if ($page < $total_pages): ?>
                    <a href="<?php echo $paginationUrl . ($page + 1); ?>" class="page-link">Next &raquo;</a>
                <?php else: ?>
                    <span class="page-link disabled">Next &raquo;</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="info-highlight" style="margin-top: 1.5rem;">
                <i class="fa-solid fa-circle-info info-icon"></i>
                <div class="info-content">
                    <h4>Data Integrity Note:</h4>
                    <p>Audit logs are immutable tracking records generated automatically by the system. They cannot be edited or deleted by any user or administrator.</p>
                </div>
            </div>

        </main>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag | Administrative Panel</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>

</body>

</html>

