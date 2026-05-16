<?php
/**
 * Resident Reports History
 * Lists all reports submitted by the active user with filtering and pagination.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Resident Verification
if ($_SESSION['role'] !== 'resident') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Handle Filtering Logic ---
$where_clauses = ["r.user_id = ?"];
$params        = [$user_id];
$types         = "i";

// Type Filter
if (!empty($_GET['type'])) {
    $where_clauses[] = "r.type = ?";
    $params[]        = $_GET['type'];
    $types          .= "s";
}

// Severity Filter
if (!empty($_GET['severity'])) {
    $where_clauses[] = "r.severity = ?";
    $params[]        = $_GET['severity'];
    $types          .= "s";
}

// Status Filter
if (!empty($_GET['status'])) {
    $where_clauses[] = "r.status = ?";
    $params[]        = $_GET['status'];
    $types          .= "s";
}

// Search Logic
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($searchTerm !== '') {
    $searchWildcard = "%" . $searchTerm . "%";
    $searchId       = intval($searchTerm);

    $where_clauses[] = "(r.id = ? OR r.location LIKE ? OR r.type LIKE ? OR r.status LIKE ?)";
    $params[]        = $searchId;
    $params[]        = $searchWildcard;
    $params[]        = $searchWildcard;
    $params[]        = $searchWildcard;
    $types          .= "isss";
}

// --- Pagination Configuration ---
$limit = 10;
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$base_where = " WHERE " . implode(" AND ", $where_clauses);

// --- Fetch Total Row Count ---
$count_sql = "SELECT COUNT(*) as total FROM reports r" . $base_where;
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['total'];

$total_pages = ceil($total_rows / $limit);
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// --- Fetch Filtered Reports ---
$sql = "SELECT r.id, r.type, r.location, r.location_details, r.description,
               r.severity, r.status, r.created_at, r.report_source
        FROM reports r" . $base_where . " ORDER BY r.created_at DESC, r.id DESC LIMIT ? OFFSET ?";

$queryParams = $params;
$queryParams[] = $limit;
$queryParams[] = $offset;
$queryTypes = $types . "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($queryTypes, ...$queryParams);
$stmt->execute();
$reports = $stmt->get_result();

// --- UI Helper Logic ---
$urlParams = $_GET;
unset($urlParams['page']);
$queryString = http_build_query($urlParams);
$paginationUrl = "?" . (!empty($queryString) ? $queryString . "&" : "") . "page=";

$activeFilters = (!empty($_GET['type']) ? 1 : 0)
              + (!empty($_GET['severity']) ? 1 : 0)
              + (!empty($_GET['status']) ? 1 : 0)
              + (!empty($_GET['search']) ? 1 : 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports | Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../../assets/css/global/residents_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/global/residents_components.css">
    <link rel="stylesheet" href="../../assets/css/pages/my_reports.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <!-- ===== HEADER ===== -->
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

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <h3>Menu</h3>
            <ul>
                <li><a href="../dashboard/resident_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="../reports/submit_report.php"><i class="fa-solid fa-paper-plane"></i> Submit Report</a></li>
                <li><a href="../reports/my_reports.php" class="active"><i class="fa-solid fa-file-invoice"></i> My Reports</a></li>
                <li><a href="../profile/profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="content">

            <!-- Page Header -->
            <div class="page-header-flex">
                <div>
                    <h2>My Reports</h2>
                    <p>Track the status of your submitted incident reports.</p>
                </div>
                
                <div class="page-toolbar">
                    <div class="toolbar-left">
                        <!-- FILTER SECTION -->
                        <form method="GET" class="filter-section resident-filter-row" style="margin-bottom: 0; padding: 0.5rem 0; box-shadow: none; border: none; background: transparent;">
                            <div class="search-group">
                                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                                <input type="text" name="search" class="search-input" placeholder="Search your reports…" value="<?php echo htmlspecialchars($searchTerm); ?>">
                            </div>
                            <div class="filter-group">
                                <select name="type" onchange="this.form.submit()">
                                    <option value="">All Types</option>
                                    <option value="flood" <?php if (!empty($_GET['type']) && $_GET['type'] == 'flood') echo 'selected'; ?>>Flood</option>
                                    <option value="drainage" <?php if (!empty($_GET['type']) && $_GET['type'] == 'drainage') echo 'selected'; ?>>Drainage</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php if (!empty($_GET['status']) && $_GET['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                    <option value="in_progress" <?php if (!empty($_GET['status']) && $_GET['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                                    <option value="resolved" <?php if (!empty($_GET['status']) && $_GET['status'] == 'resolved') echo 'selected'; ?>>Resolved</option>
                                    <option value="dismissed" <?php if (!empty($_GET['status']) && $_GET['status'] == 'dismissed') echo 'selected'; ?>>Dismissed</option>
                                </select>
                            </div>

                            <!-- MORE FILTERS DROPDOWN -->
                            <?php $mfActive = !empty($_GET['severity']) ? 1 : 0; ?>
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
                                            <option value="Low" <?php if (!empty($_GET['severity']) && $_GET['severity'] == 'Low') echo 'selected'; ?>>Low</option>
                                            <option value="Medium" <?php if (!empty($_GET['severity']) && $_GET['severity'] == 'Medium') echo 'selected'; ?>>Medium</option>
                                            <option value="High" <?php if (!empty($_GET['severity']) && $_GET['severity'] == 'High') echo 'selected'; ?>>High</option>
                                            <option value="Critical" <?php if (!empty($_GET['severity']) && $_GET['severity'] == 'Critical') echo 'selected'; ?>>Critical</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="mf-apply-btn">Apply Filters</button>
                                </div>
                            </div>

                            <a href="my_reports.php" class="btn-reset-filter" title="Clear all filters">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        </form>
                    </div>

                    <div class="toolbar-right">
                        <!-- Submit new report shortcut -->
                        <a href="submit_report.php" class="btn btn-fab-mobile">
                            <i class="fa-solid fa-plus"></i> <span class="fab-text">New Report</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Info Highlight -->
            <div class="info-highlight" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-info info-icon"></i>
                <div class="info-content">
                    <h4>Status Tracking:</h4>
                    <p>Track the progress of your submissions. <strong>Pending</strong> reports are awaiting verification, <strong>In Progress</strong> means responders have been dispatched/assigned, and <strong>Resolved</strong> indicates the issue has been cleared.</p>
                </div>
            </div>

            <!-- Reports Table -->
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 16%;">Type</th>
                        <th style="width: 22%;">Location</th>
                        <th style="width: 22%;">Description</th>
                        <th style="width: 10%;">Severity</th>
                        <th style="width: 13%;">Date</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 7%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reports && $reports->num_rows > 0): ?>
                        <?php while ($row = $reports->fetch_assoc()): ?>
                            <?php
                            $sev_icon = match($row['severity']) {
                                'Low'      => 'fa-circle-info',
                                'Medium'   => 'fa-exclamation-circle',
                                'High'     => 'fa-circle-exclamation',
                                'Critical' => 'fa-triangle-exclamation',
                                default    => 'fa-circle-info'
                            };
                            $status_icon = match($row['status']) {
                                'pending'     => 'fa-clock',
                                'in_progress' => 'fa-spinner',
                                'resolved'    => 'fa-circle-check',
                                'dismissed'   => 'fa-circle-xmark',
                                default       => 'fa-clock'
                            };
                            $is_critical = ($row['severity'] === 'Critical');
                            ?>
                            <tr>
                                <td data-label="Type">
                                    <strong style="display: block; margin-bottom: 0.3rem;">
                                        <?php echo ucfirst($row['type']); ?>
                                        <span style="color: #6b7280; font-weight: normal;">#<?php echo $row['id']; ?></span>
                                    </strong>
                                    <?php
                                    $src     = $row['report_source'] ?? 'Online';
                                    $src_cfg = [
                                        'Online'        => ['color' => '#065f46', 'bg' => '#dcfce7', 'icon' => 'fa-wifi'],
                                        'Walk-In'       => ['color' => '#3730a3', 'bg' => '#e0e7ff', 'icon' => 'fa-person-walking'],
                                        'Phone Call'    => ['color' => '#065f46', 'bg' => '#d1fae5', 'icon' => 'fa-phone'],
                                        'Offline Batch' => ['color' => '#92400e', 'bg' => '#fef3c7', 'icon' => 'fa-file-pen'],
                                    ];
                                    $cfg = $src_cfg[$src] ?? $src_cfg['Online'];
                                    ?>
                                    <span class="source-pill" style="background:<?php echo $cfg['bg']; ?>; color:<?php echo $cfg['color']; ?>;">
                                        <i class="fa-solid <?php echo $cfg['icon']; ?>"></i> <?php echo htmlspecialchars($src); ?>
                                    </span>
                                </td>
                                <td data-label="Location">
                                    <span class="location-text">
                                        <?php echo htmlspecialchars($row['location'] . ($row['location_details'] ? ' — ' . $row['location_details'] : '')); ?>
                                    </span>
                                </td>
                                <td data-label="Description">
                                    <span class="desc-text">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </span>
                                </td>
                                <td data-label="Severity">
                                    <span class="severity-badge severity-<?php echo $row['severity'] ?? 'Low'; ?>">
                                        <i class="fa-solid <?php echo $sev_icon; ?>"></i>
                                        <?php echo $row['severity'] ?? 'Low'; ?>
                                    </span>
                                </td>
                                <td data-label="Date" class="date-cell">
                                    <strong style="font-size: 0.9rem; color: #111827; display: block; font-weight: 600;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></strong>
                                    <span style="font-size: 0.8rem; color: #6b7280; margin-top: 0.15rem; display: block;"><i class="fa-regular fa-clock" style="font-size: 0.75rem; margin-right: 0.15rem;"></i> <?php echo date('h:i A', strtotime($row['created_at'])); ?></span>
                                </td>
                                <td data-label="Status" style="white-space: nowrap;">
                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                        <i class="fa-solid <?php echo $status_icon; ?>"></i>
                                        <?php echo ucwords(str_replace('_', ' ', $row['status'])); ?>
                                    </span>
                                </td>
                                <td data-label="Action">
                                    <a href="view_report.php?id=<?php echo $row['id']; ?>" class="action-btn btn-view">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fa-regular fa-folder-open"></i>
                                    <p><?php
                                        if (!empty($searchTerm)) {
                                            echo 'No reports found matching \'<strong>' . htmlspecialchars($searchTerm) . '</strong>\'';
                                        } elseif ($activeFilters > 0) {
                                            echo 'No reports match your current filters.';
                                        } else {
                                            echo "You haven't submitted any reports yet.";
                                        }
                                    ?></p>
                                </div>
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
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>
    <script>
        function toggleMoreFilters() {
            var panel = document.getElementById('mfPanel');
            panel.classList.toggle('open');
        }
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

