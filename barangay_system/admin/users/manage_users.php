<?php
/**
 * User Account Management
 * Allows administrators to manage system users, approve registrations, and reset passwords.
 * Includes advanced filtering, search, and pagination.
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

// Role Filter
if (isset($_GET['role']) && !empty($_GET['role'])) {
    $where_clauses[] = "role = ?";
    $params[]        = $_GET['role'];
    $types          .= "s";
}

// Status Filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_clauses[] = "status = ?";
    $params[]        = $_GET['status'];
    $types          .= "s";
}

// Search Logic
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $searchTerm     = trim($_GET['search']);
    $searchWildcard = "%" . $searchTerm . "%";

    $where_clauses[] = "(full_name LIKE ? OR username LIKE ? OR contact_number LIKE ? OR address LIKE ? OR status LIKE ?)";
    $params[]        = $searchWildcard;
    $params[]        = $searchWildcard;
    $params[]        = $searchWildcard;
    $params[]        = $searchWildcard;
    $params[]        = $searchWildcard;
    $types          .= "sssss";
}

// --- Pagination Configuration ---
$limit = 10;
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$base_where = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// --- Fetch Total Row Count ---
$count_sql = "SELECT COUNT(*) as total FROM users" . $base_where;
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

// --- Fetch Filtered Users ---
$sql = "SELECT id, full_name, username, role, status FROM users" . $base_where . " ORDER BY created_at DESC LIMIT ? OFFSET ?";

$queryParams   = $params;
$queryParams[] = $limit;
$queryParams[] = $offset;
$queryTypes    = $types . "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($queryTypes, ...$queryParams);
$stmt->execute();
$users = $stmt->get_result();

// --- UI Helper Logic ---
$urlParams = $_GET;
unset($urlParams['page']);
$queryString = http_build_query($urlParams);
$paginationUrl = "?" . (!empty($queryString) ? $queryString . "&" : "") . "page=";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../../assets/css/global/admin_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/pages/manage_users.css?v=2">
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
                <li><a href="manage_users.php" class="active"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="../teams/manage_teams.php"><i class="fa-solid fa-people-group"></i> Manage Teams</a></li>
                <li><a href="../announcements/manage_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
                <li><a href="encode_report.php"><i class="fa-solid fa-keyboard"></i> Encode Report</a></li>
                <li><a href="../reports/view_reports.php"><i class="fa-solid fa-file-alt"></i> View Reports</a></li>
                <li><a href="../logs/audit_logs.php"><i class="fa-solid fa-shield-halved"></i> Audit Logs</a></li>
                <li><a href="../profile/admin_profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <!-- CONTENT AREA -->
        <main class="content">

            <?php require_once "../../includes/flash_toast.php"; ?>

            <!-- PAGE HEADER -->
            <div class="page-header-flex">
                <div>
                    <h2>Manage Users</h2>
                    <p>View and manage system user accounts.</p>
                </div>
                
                <div class="page-toolbar">
                    <div class="toolbar-left">
                        <!-- FILTER SECTION -->
                        <form method="GET" class="filter-section" style="margin-bottom: 0; padding: 0.5rem 0; box-shadow: none; border: none; background: transparent;">
                            <div class="search-group">
                                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                                <input type="text" name="search" class="search-input" placeholder="Search users, streets, or contact numbers…" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            <div class="filter-group">
                                <select name="role" onchange="this.form.submit()">
                                    <option value="">All Roles</option>
                                    <option value="resident" <?php if (isset($_GET['role']) && $_GET['role'] == 'resident') echo 'selected'; ?>>Resident</option>
                                    <option value="admin" <?php if (isset($_GET['role']) && $_GET['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="active" <?php if (isset($_GET['status']) && $_GET['status'] == 'active') echo 'selected'; ?>>Active</option>
                                    <option value="disabled" <?php if (isset($_GET['status']) && $_GET['status'] == 'disabled') echo 'selected'; ?>>Disabled</option>
                                    <option value="pending" <?php if (isset($_GET['status']) && $_GET['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                </select>
                            </div>
                            <a href="manage_users.php" class="btn-reset-filter" title="Clear Filters">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        </form>
                    </div>

                    <div class="toolbar-right">
                        <button onclick="openUserModal()" class="btn-new-user">
                            <span class="btn-icon"><i class="fa-solid fa-user-plus"></i></span>
                            New User
                        </button>
                    </div>
                </div>
            </div>

            <!-- USERS TABLE -->
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->num_rows > 0): ?>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Full Name"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td data-label="Username"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td data-label="Role">
                                    <span class="role-badge">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <?php
                                    $status_class = $user['status'];
                                    $status_icon  = 'fa-circle-check';
                                    if ($user['status'] === 'disabled') $status_icon = 'fa-circle-xmark';
                                    if ($user['status'] === 'pending')  $status_icon = 'fa-clock';
                                    ?>
                                    <span class="status-badge status-<?php echo $status_class; ?>">
                                        <i class="fa-solid <?php echo $status_icon; ?>"></i>
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td data-label="Action" style="white-space: nowrap;">
                                    <!-- View Profile (all users) -->
                                    <a href="view_user.php?id=<?php echo $user['id']; ?>" class="action-btn btn-view">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>

                                        <?php if ($user['status'] === 'pending'): ?>
                                            <!-- Approve / Reject (pending users only) -->
                                            <form method="POST" action="process_approve_user.php" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="action-btn btn-enable"
                                                    onclick="return confirm('Approve this registration? The user will be able to log in.');">
                                                    <i class="fa-solid fa-circle-check"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="process_reject_user.php" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="action-btn btn-disable"
                                                    onclick="return confirm('Reject this registration? The record will be permanently deleted.');">
                                                    <i class="fa-solid fa-circle-xmark"></i> Reject
                                                </button>
                                            </form>
                                        <?php elseif ($user['role'] !== 'admin'): ?>
                                            <!-- Disable / Enable (residents only) -->
                                            <form method="POST" action="toggle_user.php" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $user['status']; ?>">
                                                <?php if ($user['status'] === 'active'): ?>
                                                    <button type="submit" class="action-btn btn-disable"
                                                        onclick="return confirm('Disable this user? They will not be able to login.');">
                                                        <i class="fa-solid fa-ban"></i> Disable
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" class="action-btn btn-enable"
                                                        onclick="return confirm('Enable this user account?');">
                                                        <i class="fa-solid fa-circle-check"></i> Enable
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($user['status'] !== 'pending'): ?>
                                        <!-- Reset Password (all users except self, not pending) -->
                                        <form method="POST" action="process_reset_password.php" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="action-btn btn-reset-pass"
                                                onclick="return confirm('Reset password for <?php echo htmlspecialchars(addslashes($user['full_name'])); ?>? A new temporary password will be generated.');">
                                                <i class="fa-solid fa-key"></i> Reset Password
                                            </button>
                                        </form>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <span style="color: #6b7280; font-size: 0.875rem;">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #6b7280; padding: 2rem;">
                                <?php if (!empty($_GET['search'])): ?>
                                    No users found matching '<strong><?php echo htmlspecialchars($_GET['search']); ?></strong>'.
                                <?php else: ?>
                                    No users found matching filters.
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

            <div class="info-highlight" style="margin-top: 2rem;">
                <i class="fa-solid fa-circle-info info-icon"></i>
                <div class="info-content">
                    <h4>Account Management:</h4>
                    <p>Disabling a user prevents system access without deleting records. This safely preserves user history and report data for system integrity.</p>
                </div>
            </div>

        </main>
    </div>

    <!-- FOOTER -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag | Administrative Panel</p>
    </footer>

    <!-- SUCCESS MODAL (Temp Password) -->
    <?php if (isset($_SESSION['new_user_success'])): ?>
        <div id="successModal" class="form-modal" style="display: flex;">
            <div class="modal-content" style="max-width: 450px; text-align: center;">
                <span class="btn-close" onclick="document.getElementById('successModal').style.display='none'">&times;</span>
                <div style="width: 60px; height: 60px; background: #dcfce7; color: #166534; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1rem auto;">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h3 style="margin-top: 0; margin-bottom: 0.5rem; color: #111827;">User Created Successfully</h3>
                <p style="color: #4b5563; font-size: 0.95rem; margin-bottom: 1.5rem;">
                    Please provide these credentials to <strong><?php echo htmlspecialchars($_SESSION['new_user_name']); ?></strong>
                </p>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 1.25rem; border-radius: 0.5rem; text-align: left; margin-bottom: 1.5rem;">
                    <div style="margin-bottom: 1rem;">
                        <span style="display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">Username</span>
                        <span style="font-family: monospace; font-size: 1.1rem; color: #0f172a; font-weight: 600;">
                            <?php echo htmlspecialchars($_SESSION['new_user_username']); ?>
                        </span>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.8rem; font-weight: 600; color: #ef4444; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">Temporary Password</span>
                        <span style="font-family: monospace; font-size: 1.25rem; color: #dc2626; font-weight: 700; letter-spacing: 1px; background: #fef2f2; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                            <?php echo htmlspecialchars($_SESSION['new_user_password']); ?>
                        </span>
                    </div>
                </div>

                <div class="info-note" style="text-align: left; background: #fffbeb; border-left: 4px solid #f59e0b; padding: 0.75rem; border-radius: 0.25rem; font-size: 0.85rem; color: #92400e; margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Make sure to copy this password now it will not be shown again.
                </div>

                <button class="btn-submit" style="width: 100%;" onclick="document.getElementById('successModal').style.display='none'">Done</button>
            </div>
        </div>
        <?php 
            // Clear the session variables so it doesn't show again on refresh
            unset($_SESSION['new_user_success'], $_SESSION['new_user_name'], $_SESSION['new_user_username'], $_SESSION['new_user_password']);
        endif; 
    ?>

    <!-- RESET PASSWORD SUCCESS MODAL -->
    <?php if (isset($_SESSION['reset_success'])): ?>
        <div id="resetModal" class="form-modal" style="display: flex;">
            <div class="modal-content" style="max-width: 450px; text-align: center;">
                <span class="btn-close" onclick="document.getElementById('resetModal').style.display='none'">&times;</span>
                <div style="width: 60px; height: 60px; background: #fef2f2; color: #dc2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1rem auto;">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h3 style="margin-top: 0; margin-bottom: 0.5rem; color: #111827;">Password Reset Successful</h3>
                <p style="color: #4b5563; font-size: 0.95rem; margin-bottom: 1.5rem;">
                    Please provide these new credentials to <strong><?php echo htmlspecialchars($_SESSION['reset_user_name']); ?></strong>
                </p>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 1.25rem; border-radius: 0.5rem; text-align: left; margin-bottom: 1.5rem;">
                    <div style="margin-bottom: 1rem;">
                        <span style="display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">Username</span>
                        <span style="font-family: monospace; font-size: 1.1rem; color: #0f172a; font-weight: 600;">
                            <?php echo htmlspecialchars($_SESSION['reset_user_username']); ?>
                        </span>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.8rem; font-weight: 600; color: #ef4444; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">New Temporary Password</span>
                        <span style="font-family: monospace; font-size: 1.25rem; color: #dc2626; font-weight: 700; letter-spacing: 1px; background: #fef2f2; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                            <?php echo htmlspecialchars($_SESSION['reset_temp_password']); ?>
                        </span>
                    </div>
                </div>

                <div class="info-note" style="text-align: left; background: #fffbeb; border-left: 4px solid #f59e0b; padding: 0.75rem; border-radius: 0.25rem; font-size: 0.85rem; color: #92400e; margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Copy this password now — it will not be shown again. The user will be required to change it on next login.
                </div>

                <button class="btn-submit" style="width: 100%;" onclick="document.getElementById('resetModal').style.display='none'">Done</button>
            </div>
        </div>
        <?php
            unset($_SESSION['reset_success'], $_SESSION['reset_user_name'], $_SESSION['reset_user_username'], $_SESSION['reset_temp_password']);
        endif;
    ?>

    <!-- CREATE USER MODAL Form -->
    <div id="userModal" class="form-modal">
        <div class="modal-content" style="max-width: 600px;">
            <span class="btn-close" onclick="closeUserModal()">&times;</span>
            <h3 id="modalTitle" style="margin-top: 0; margin-bottom: 1.5rem; color: #111827; font-size: 1.25rem;">
                <i class="fa-solid fa-user-plus" style="color: #059669; margin-right: 0.5rem;"></i> New User Account
            </h3>
            
            <form action="process_create_user.php" method="POST">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Full Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="full_name" id="full_name" class="form-control" placeholder="Juan Dela Cruz" required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label>Username <span style="color: #ef4444;">*</span></label>
                        <div style="position: relative;">
                            <input type="text" name="username" id="username" class="form-control" placeholder="juan.dc" required maxlength="50" autocomplete="off">
                            <div id="usernameStatus" style="position: absolute; right: 10px; top: 10px; font-size: 0.85rem;"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Role <span style="color: #ef4444;">*</span></label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="">Select Role</option>
                            <option value="resident">Resident</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status <span style="color: #ef4444;">*</span></label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1rem;">
                    <label>Contact Number <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="contact_number" id="contact_number" class="form-control" placeholder="09xxxxxxxxx" maxlength="11" required>
                </div>

                <div class="form-group">
                    <label>Address / Street <span style="color: #ef4444;">*</span></label>
                    <select id="address" name="address" class="form-control" required>
                        <option value="">Select Street...</option>
                        <option value="Acacia Street">Acacia Street</option>
                        <option value="Agate Street">Agate Street</option>
                        <option value="Anahaw Street">Anahaw Street</option>
                        <option value="Antipolo Street">Antipolo Street</option>
                        <option value="Apitong St">Apitong St</option>
                        <option value="Banaba St">Banaba St</option>
                        <option value="Blacer Street">Blacer Street</option>
                        <option value="Buhi St">Buhi St</option>
                        <option value="Cacao Street">Cacao Street</option>
                        <option value="Chico St">Chico St</option>
                        <option value="Cypress Street">Cypress Street</option>
                        <option value="Dapdap St">Dapdap St</option>
                        <option value="Dapo St">Dapo St</option>
                        <option value="Diamond Street">Diamond Street</option>
                        <option value="Dita St">Dita St</option>
                        <option value="Emerald Street">Emerald Street</option>
                        <option value="Garnet Street">Garnet Street</option>
                        <option value="Hamoraon St">Hamoraon St</option>
                        <option value="J. Antonio M. Carpio Street">J. Antonio M. Carpio Street</option>
                        <option value="Jade Street">Jade Street</option>
                        <option value="Kamagong St">Kamagong St</option>
                        <option value="Lawaan St">Lawaan St</option>
                        <option value="Lukban St">Lukban St</option>
                        <option value="Mahogany Street">Mahogany Street</option>
                        <option value="Narra St">Narra St</option>
                        <option value="Onyx Street">Onyx Street</option>
                        <option value="Opal Street">Opal Street</option>
                        <option value="Palomaria St">Palomaria St</option>
                        <option value="Papua Street">Papua Street</option>
                        <option value="Pili Street">Pili Street</option>
                        <option value="Rimas St">Rimas St</option>
                        <option value="Ruby Street">Ruby Street</option>
                        <option value="Sapphire Street">Sapphire Street</option>
                        <option value="Talisay St">Talisay St</option>
                        <option value="Topaz Street">Topaz Street</option>
                        <option value="Villafrancia Street">Villafrancia Street</option>
                        <option value="Yakal St">Yakal St</option>
                        <option value="Other">Other</option>
                    </select>
                </div>


                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 1rem; margin-top: 1.5rem; display: flex; gap: 1rem; align-items: flex-start;">
                    <i class="fa-solid fa-shield-halved" style="color: #059669; font-size: 1.25rem; margin-top: 0.1rem;"></i>
                    <div>
                        <p style="margin: 0 0 0.5rem 0; font-weight: 600; font-size: 0.95rem; color: #0f172a;">Account Security</p>
                        <p style="margin: 0 0 0.75rem 0; font-size: 0.85rem; color: #4b5563;">System will auto-generate a secure temporary password shown after creation.</p>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; cursor: pointer;">
                            <input type="checkbox" name="require_change" id="require_change" checked style="width: 1rem; height: 1rem; accent-color: #059669;">
                            Require change on first login
                        </label>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                    <button type="button" onclick="closeUserModal()" class="btn-cancel" style="padding: 0.6rem 1.25rem; border-radius: 0.375rem; background: white; border: 1px solid #d1d5db; color: #4b5563; font-weight: 500; cursor: pointer;">Cancel</button>
                    <button type="submit" id="submitUserBtn" class="btn-submit" style="width: auto; padding: 0.6rem 1.5rem; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 0.375rem;"><i class="fa-solid fa-user-plus"></i> Create User</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/global/sidebar.js"></script>
    <script src="../../assets/js/pages/manage_users.js?v=2"></script>

</body>

</html>

