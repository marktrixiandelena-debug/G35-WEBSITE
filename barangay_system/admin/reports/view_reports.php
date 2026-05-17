<?php
/**
 * Global Report Monitoring (Admin)
 * Comprehensive list of all reported incidents with multi-level filtering and search.
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

// Incident Type Filter
if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where_clauses[] = "r.type = ?";
    $params[]        = $_GET['type'];
    $types          .= "s";
}

// Processing Status Filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_clauses[] = "r.status = ?";
    $params[]        = $_GET['status'];
    $types          .= "s";
}

// Reporting Source Filter
if (isset($_GET['source']) && !empty($_GET['source'])) {
    $where_clauses[] = "r.report_source = ?";
    $params[]        = $_GET['source'];
    $types          .= "s";
}

// Severity Level Filter
if (isset($_GET['severity']) && !empty($_GET['severity'])) {
    $where_clauses[] = "r.severity = ?";
    $params[]        = $_GET['severity'];
    $types          .= "s";
}

// Search Logic (Global)
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $searchTerm     = trim($_GET['search']);
    $searchWildcard = "%" . $searchTerm . "%";
    $searchId       = intval($searchTerm);

    $where_clauses[] = "(r.id = ? OR r.location LIKE ? OR r.type LIKE ? OR r.status LIKE ? OR u.full_name LIKE ? OR r.severity LIKE ?)";
    $params[]        = $searchId;
    $params[]        = $searchWildcard;
    $params[]        = $searchWildcard;
    $params[]        = $searchWildcard;
    $params[]        = $searchWildcard;
    $params[]        = $searchWildcard;
    $types          .= "isssss";
}

// --- Pagination Configuration ---
$limit = 10;
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$base_where = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// --- Fetch Total Row Count ---
$count_sql = "SELECT COUNT(*) as total FROM reports r LEFT JOIN users u ON r.user_id = u.id" . $base_where;
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_rows = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_rows / $limit);
if ($page > $total_pages && $total_pages > 0) {
    $page   = $total_pages;
    $offset = ($page - 1) * $limit;
}

// --- Fetch Filtered Reports ---
$sql = "SELECT r.id, r.type, r.location, r.location_details, r.description, r.status, r.severity, r.created_at, r.report_source, r.guest_name, u.full_name 
        FROM reports r 
        LEFT JOIN users u ON r.user_id = u.id" . $base_where . " ORDER BY r.created_at DESC, r.id DESC LIMIT ? OFFSET ?";

$queryParams   = $params;
$queryParams[] = $limit;
$queryParams[] = $offset;
$queryTypes    = $types . "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($queryTypes, ...$queryParams);
$stmt->execute();
$reports = $stmt->get_result();

// --- UI Helper Logic ---
$urlParams = $_GET;
unset($urlParams['page']);
$queryString = http_build_query($urlParams);
$paginationUrl = "?" . (!empty($queryString) ? $queryString . "&" : "") . "page=";

// Build state-preserving return URL (excludes any stale returnto key)
$returnParams = array_diff_key($_GET, ['returnto' => '']);
$returnQS     = http_build_query($returnParams);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports | Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../../assets/css/global/admin_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/pages/view_reports.css">
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

        <!-- CONTENT AREA -->
        <main class="content">
            <?php require_once "../../includes/flash_toast.php"; ?>
            <div class="page-header-flex">
                <div>
                    <h2>View Reports</h2>
                    <p>Monitor and update flood and drainage incident reports.</p>
                </div>
                
                <div class="page-toolbar">
                    <div class="toolbar-left">
                        <!-- FILTER SECTION -->
                        <form method="GET" id="reportFilterForm" class="filter-section" style="margin-bottom: 0; padding: 0.5rem 0; box-shadow: none; border: none; background: transparent;">
                            <div class="search-group">
                                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                                <input type="text" name="search" class="search-input" placeholder="Search reports, streets, names, or IDs…" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            <div class="filter-group">
                                <select name="type" onchange="this.form.submit()">
                                    <option value="">All Types</option>
                                    <option value="flood" <?php if (isset($_GET['type']) && $_GET['type'] == 'flood') echo 'selected'; ?>>Flood</option>
                                    <option value="drainage" <?php if (isset($_GET['type']) && $_GET['type'] == 'drainage') echo 'selected'; ?>>Drainage</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php if (isset($_GET['status']) && $_GET['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                    <option value="in_progress" <?php if (isset($_GET['status']) && $_GET['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                                    <option value="resolved" <?php if (isset($_GET['status']) && $_GET['status'] == 'resolved') echo 'selected'; ?>>Resolved</option>
                                    <option value="dismissed" <?php if (isset($_GET['status']) && $_GET['status'] == 'dismissed') echo 'selected'; ?>>Dismissed</option>
                                </select>
                            </div>

                            <!-- MORE FILTERS DROPDOWN -->
                            <?php
                            $mfActive = (!empty($_GET['source']) ? 1 : 0) + (!empty($_GET['severity']) ? 1 : 0);
                            ?>
                            <div class="more-filters-wrapper">
                                <button type="button" id="mfToggle"
                                    class="more-filters-btn <?php echo $mfActive > 0 ? 'has-active' : ''; ?>"
                                    onclick="toggleMoreFilters()">
                                    <i class="fa-solid fa-sliders"></i>
                                    More Filters
                                    <?php if ($mfActive > 0): ?>
                                        <span class="mf-badge"><?php echo $mfActive; ?></span>
                                    <?php else: ?>
                                        <i class="fa-solid fa-chevron-down" style="font-size:0.7rem;"></i>
                                    <?php endif; ?>
                                </button>
                                <div class="more-filters-panel <?php echo $mfActive > 0 ? 'open' : ''; ?>" id="mfPanel">
                                    <div class="mf-panel-title">Additional Filters</div>
                                    <div class="mf-field">
                                        <label>Severity</label>
                                        <select name="severity">
                                            <option value="">All Severities</option>
                                            <option value="Low" <?php if (isset($_GET['severity']) && $_GET['severity'] == 'Low') echo 'selected'; ?>>Low</option>
                                            <option value="Medium" <?php if (isset($_GET['severity']) && $_GET['severity'] == 'Medium') echo 'selected'; ?>>Medium</option>
                                            <option value="High" <?php if (isset($_GET['severity']) && $_GET['severity'] == 'High') echo 'selected'; ?>>High</option>
                                            <option value="Critical" <?php if (isset($_GET['severity']) && $_GET['severity'] == 'Critical') echo 'selected'; ?>>Critical</option>
                                        </select>
                                    </div>
                                    <div class="mf-field">
                                        <label>Source</label>
                                        <select name="source">
                                            <option value="">All Sources</option>
                                            <option value="Online" <?php if (isset($_GET['source']) && $_GET['source'] == 'Online') echo 'selected'; ?>>Online</option>
                                            <option value="Walk-In" <?php if (isset($_GET['source']) && $_GET['source'] == 'Walk-In') echo 'selected'; ?>>Walk-In</option>
                                            <option value="Phone Call" <?php if (isset($_GET['source']) && $_GET['source'] == 'Phone Call') echo 'selected'; ?>>Phone Call</option>
                                            <option value="Offline Batch" <?php if (isset($_GET['source']) && $_GET['source'] == 'Offline Batch') echo 'selected'; ?>>Offline Batch</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="mf-apply-btn">Apply Filters</button>
                                </div>
                            </div>

                            <a href="view_reports.php" class="btn-reset-filter" title="Clear all filters">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        </form>
                    </div>

                    <div class="toolbar-right">
                        <a href="../users/encode_report.php" class="btn-encode-report">
                            <span class="btn-icon"><i class="fa-solid fa-keyboard"></i></span>
                            Encode Report
                        </a>
                    </div>
                </div>

            <!-- REPORTS TABLE -->
            <div class="table-responsive">
                <table>
                    <thead>
                    <tr>
                        <th style="width: 15%;">Type</th>
                        <th style="width: 20%;">Location</th>
                        <th style="width: 10%;">Severity</th>
                        <th style="width: 20%;">Description</th>
                        <th style="width: 15%;">Resident</th>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 5%;">Status</th>
                        <th style="width: 5%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reports && $reports->num_rows > 0): ?>
                        <?php while ($row = $reports->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Type">
                                    <strong style="display: block; margin-bottom: 0.4rem;"><?php echo ucfirst($row['type']); ?> <span style="color: #6b7280; font-weight: normal;">#<?php echo $row['id']; ?></span></strong>
                                    <?php
                                    $src = $row['report_source'] ?? 'Online';
                                    $src_cfg = [
                                        'Online'        => ['color' => '#065f46', 'bg' => '#dcfce7', 'icon' => 'fa-wifi'],
                                        'Walk-In'       => ['color' => '#3730a3', 'bg' => '#e0e7ff', 'icon' => 'fa-person-walking'],
                                        'Phone Call'    => ['color' => '#065f46', 'bg' => '#d1fae5', 'icon' => 'fa-phone'],
                                        'Offline Batch' => ['color' => '#92400e', 'bg' => '#fef3c7', 'icon' => 'fa-file-pen'],
                                    ];
                                    $cfg = $src_cfg[$src] ?? $src_cfg['Online'];
                                    ?>
                                    <span style="display: inline-flex; align-items: center; gap: 0.3rem; background: <?php echo $cfg['bg']; ?>; color: <?php echo $cfg['color']; ?>; font-size: 0.7rem; font-weight: 600; padding: 0.1rem 0.5rem; border-radius: 1rem;">
                                        <i class="fa-solid <?php echo $cfg['icon']; ?>"></i> <?php echo htmlspecialchars($src); ?>
                                    </span>
                                </td>
                                <td data-label="Location">
                                    <span style="font-size: 0.85rem; color: #374151; font-weight: 500; display: block; line-height: 1.4;">
                                        <?php echo htmlspecialchars($row['location'] . ($row['location_details'] ? ' - ' . $row['location_details'] : '')); ?>
                                    </span>
                                </td>
                                <td data-label="Severity">
                                    <?php
                                    $sev_icon = match ($row['severity']) {
                                        'Low' => 'fa-circle-info',
                                        'Medium' => 'fa-exclamation-circle',
                                        'High' => 'fa-circle-exclamation',
                                        'Critical' => 'fa-triangle-exclamation',
                                        default => 'fa-circle-info'
                                    };
                                    ?>
                                    <span class="severity-badge severity-<?php echo $row['severity'] ?? 'Low'; ?>">
                                        <i class="fa-solid <?php echo $sev_icon; ?>"></i>
                                        <?php echo $row['severity'] ?? 'Low'; ?>
                                    </span>
                                </td>
                                <td data-label="Description">
                                    <span style="font-size: 0.95rem; line-height: 1.4; display: block;">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </span>
                                </td>
                                <td data-label="Resident">
                                    <?php 
                                        if (!empty($row['full_name'])) {
                                            echo htmlspecialchars($row['full_name']); 
                                        } else if (!empty($row['guest_name'])) {
                                            echo "Guest: " . htmlspecialchars($row['guest_name']);
                                        } else {
                                            echo "<i>Unknown</i>";
                                        }
                                    ?>
                                </td>
                                <td data-label="Date">
                                    <strong style="font-size: 0.9rem; color: #111827; display: block; font-weight: 600;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></strong>
                                    <span style="font-size: 0.8rem; color: #6b7280; margin-top: 0.15rem; display: block;"><i class="fa-regular fa-clock" style="font-size: 0.75rem; margin-right: 0.15rem;"></i> <?php echo date('h:i A', strtotime($row['created_at'])); ?></span>
                                </td>
                                <td data-label="Status" style="white-space: nowrap;">
                                    <?php
                                    $status_icon = match($row['status']) {
                                        'pending'     => 'fa-clock',
                                        'in_progress' => 'fa-spinner',
                                        'resolved'    => 'fa-circle-check',
                                        'dismissed'   => 'fa-circle-xmark',
                                        default       => 'fa-clock'
                                    };
                                    ?>
                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                        <i class="fa-solid <?php echo $status_icon; ?>"></i>
                                        <?php echo ucwords(str_replace('_', ' ', $row['status'])); ?>
                                    </span>
                                </td>
                                <td data-label="Action">
                                    <a href="report_details.php?id=<?php echo $row['id']; ?><?php echo $returnQS ? '&returnto=' . urlencode($returnQS) : ''; ?>" class="action-btn btn-view" style="width: auto; margin-bottom: 0.5rem; text-align: center; display: inline-flex; justify-content: center;">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #6b7280; padding: 2rem;">
                                <?php if (!empty($_GET['search'])): ?>
                                    No reports found matching '<strong><?php echo htmlspecialchars($_GET['search']); ?></strong>'.
                                <?php else: ?>
                                    No reports found matching your criteria.
                                <?php endif; ?>
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

        </main>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag | Administrative Panel</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>
    <script>
        function toggleMoreFilters() {
            var panel = document.getElementById('mfPanel');
            var btn   = document.getElementById('mfToggle');
            panel.classList.toggle('open');
        }
        // Close panel when clicking outside
        document.addEventListener('click', function(e) {
            var wrapper = document.querySelector('.more-filters-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                var panel = document.getElementById('mfPanel');
                if (panel) panel.classList.remove('open');
            }
        });
    </script>

</body>

</html>

