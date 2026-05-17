<?php
/**
 * Manual Incident Encoding (Admin)
 * Allows staff to file reports received via offline channels (Walk-in, Phone, Paper).
 * Supports both registered resident lookup and guest reporter entries.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Admin Verification
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

// --- Data Preparation ---

// Fetch validated residents for official reporter lookup
$residents = $conn->query("
    SELECT id, full_name, username, contact_number 
    FROM users 
    WHERE role = 'resident' AND status = 'active' 
    ORDER BY full_name ASC
");

// --- Input Context ---
$success = $_GET['success'] ?? null;
$error   = $_GET['error']   ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encode Report | Barangay Calauag Admin</title>
    <!-- Core Admin Dashboard CSS -->
    <link rel="stylesheet" href="../../assets/css/global/admin_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <!-- Specific Page CSS -->
    <link rel="stylesheet" href="../../assets/css/pages/encode_report.css">
    <!-- Font Awesome -->
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

                <li><a href="manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="../teams/manage_teams.php"><i class="fa-solid fa-people-group"></i> Manage Teams</a></li>
                <li><a href="../announcements/manage_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
                <li><a href="encode_report.php" class="active"><i class="fa-solid fa-keyboard"></i> Encode Report</a></li>
                <li><a href="../reports/view_reports.php"><i class="fa-solid fa-file-alt"></i> View Reports</a></li>
                <li><a href="../logs/audit_logs.php"><i class="fa-solid fa-shield-halved"></i> Audit Logs</a></li>
                <li><a href="../profile/admin_profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <!-- CONTENT AREA -->
        <main class="content">

            <!-- Title & Back Button -->
            <div class="page-header-flex">
                <div>
                    <h2>Encode Report Manually</h2>
                    <p>For walk-in, phone call, or offline reports. All entries are officially recorded in the system audit logs.</p>
                </div>
                <a href="../reports/view_reports.php" class="back-btn">
                    <i class="fa-solid fa-arrow-left"></i> Back to Reports
                </a>
            </div>

            <!-- Flash Messages -->
            <?php require_once "../../includes/flash_toast.php"; ?>



            <!-- The Form & Grid -->
            <div class="form-container">
                <form action="../reports/process_encode_report.php" method="POST" enctype="multipart/form-data">
                    <div class="encode-report-layout">

                        <!-- ══════════════════════════════
                             LEFT COLUMN — MAIN FORM
                             ══════════════════════════════ -->
                        <div class="main-form-panel">

                            <!-- SECTION 1: INCIDENT DETAILS (HIGHLIGHT) -->
                            <div class="form-section form-section-highlight">
                                <div class="form-section-header header-incident">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <h3 class="form-section-title">Incident Details</h3>
                                </div>
                                <div class="form-section-body" style="padding: 1.5rem;">
                                    
                                    <!-- 2-Col Grid for Type & Severity -->
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                                        <div class="form-group detail-row" style="margin-bottom:0;">
                                            <label class="detail-label" style="margin-bottom:0.4rem; font-size:0.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;"><i class="fa-solid fa-bolt"></i> Incident Type <span class="req-marker" style="color:#ef4444;">*</span></label>
                                            <select name="type" class="form-control" required style="font-size:0.9rem; padding:0.6rem 1rem;">
                                                <option value="">Select Type</option>
                                                <option value="flood">Flood</option>
                                                <option value="drainage">Drainage Issue</option>
                                            </select>
                                        </div>

                                        <div class="form-group detail-row" style="margin-bottom:0;">
                                            <label class="detail-label" style="margin-bottom:0.4rem; font-size:0.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;"><i class="fa-solid fa-triangle-exclamation"></i> Severity <span class="req-marker" style="color:#ef4444;">*</span></label>
                                            <select name="severity" class="form-control" required style="font-size:0.9rem; padding:0.6rem 1rem;">
                                                <option value="Low">Low - Minor issue</option>
                                                <option value="Medium">Medium - Noticeable impact</option>
                                                <option value="High">High - Urgent attention</option>
                                                <option value="Critical">Critical - Emergency</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group detail-row" style="margin-bottom:1.25rem;">
                                        <label class="detail-label" style="margin-bottom:0.4rem; font-size:0.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;"><i class="fa-solid fa-location-dot"></i> Street <span class="req-marker" style="color:#ef4444;">*</span></label>
                                        <select name="location" class="form-control" required style="font-size:0.9rem; padding:0.6rem 1rem;">
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

                                    <div class="form-group detail-row" style="margin-bottom:1.25rem;">
                                        <label class="detail-label" style="margin-bottom:0.4rem; font-size:0.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;"><i class="fa-solid fa-map-pin"></i> Specific Landmark / Details <span class="req-marker" style="color:#ef4444;">*</span></label>
                                        <input type="text" name="location_details" class="form-control" placeholder="e.g. Near the blue gate..." required style="font-size:0.9rem; padding:0.6rem 1rem;">
                                    </div>

                                    <div class="form-group detail-row" style="margin-bottom:1.25rem;">
                                        <label class="detail-label" style="margin-bottom:0.4rem; font-size:0.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;"><i class="fa-solid fa-comment-dots"></i> Description <span class="req-marker" style="color:#ef4444;">*</span></label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="Describe the situation..." required style="font-size:0.9rem; padding:0.6rem 1rem;"></textarea>
                                    </div>

                                    <div class="form-group detail-row" style="margin-top:1rem; margin-bottom:0;">
                                        <label class="detail-label" style="margin-bottom:0.4rem; font-size:0.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;"><i class="fa-solid fa-image"></i> Attach Photo <span class="opt-tag" style="font-weight:400; color:#9ca3af; text-transform:none; font-size:0.75rem;">(optional)</span></label>
                                        <input type="file" name="photo" class="form-control" accept="image/*" style="padding:0.5rem 1rem; font-size:0.85rem;">
                                        <small style="color:#6b7280; margin-top:0.4rem; display:block; font-size:0.8rem;">Max 5MB (JPG/PNG). Leave blank if none provided.</small>
                                    </div>

                                </div>
                            </div>

                            <!-- SECTION 2: REPORT SOURCE -->
                            <div class="form-section">
                                <div class="form-section-header header-source">
                                    <i class="fa-solid fa-signal"></i>
                                    <h3 class="form-section-title">How was this report received?</h3>
                                </div>
                                <div class="form-section-body">
                                    <div class="source-cards">
                                        <label class="source-card-label">
                                            <input type="radio" name="report_source" value="Walk-In" checked>
                                            <div class="source-card-inner source-walkin">
                                                <i class="fa-solid fa-person-walking"></i>
                                                <strong>Walk-In</strong>
                                                <small>Resident came to the barangay office</small>
                                            </div>
                                        </label>
                                        <label class="source-card-label">
                                            <input type="radio" name="report_source" value="Phone Call">
                                            <div class="source-card-inner source-phone">
                                                <i class="fa-solid fa-phone"></i>
                                                <strong>Phone Call</strong>
                                                <small>Resident called the barangay hotline</small>
                                            </div>
                                        </label>
                                        <label class="source-card-label" id="offline-batch-card">
                                            <input type="radio" name="report_source" value="Offline Batch">
                                            <div class="source-card-inner source-offline">
                                                <i class="fa-solid fa-file-pen"></i>
                                                <strong>Offline Batch</strong>
                                                <small>Paper form collected during outage</small>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Offline Batch Timestamp Note -->
                                    <div class="offline-batch-note" id="offlineBatchNote">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                        <div>
                                            <strong>Offline Batch Mode Active</strong><br>
                                            Specify the original report time below.
                                        </div>
                                    </div>

                                    <!-- Original Report Time (for Offline Batch) — now inside source section -->
                                    <div class="form-group" id="originalTimeGroup" style="display: none; margin-top: 1rem;">
                                        <label><i class="fa-regular fa-clock" style="margin-right: 0.3rem;"></i> Original Report Time <span class="req-marker">*</span></label>
                                        <input type="datetime-local" name="original_time" class="form-control" id="originalTimeInput"
                                            max="<?php echo date('Y-m-d\TH:i'); ?>">
                                        <small style="color: #6b7280; margin-top: 0.4rem; display: block;">Set this to the actual time the resident reported it on paper.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 3: REPORTER -->
                            <div class="form-section">
                                <div class="form-section-header header-resident">
                                    <i class="fa-solid fa-user"></i>
                                    <h3 class="form-section-title">Reporter Information</h3>
                                </div>
                                <div class="form-section-body">
                                    <div class="reporter-toggle">
                                        <input type="radio" id="reporter-registered" name="reporter_type" value="registered" class="reporter-toggle-btn" checked>
                                        <label for="reporter-registered" class="reporter-toggle-label">
                                            <i class="fa-solid fa-address-card"></i> Registered Resident
                                        </label>

                                        <input type="radio" id="reporter-guest" name="reporter_type" value="guest" class="reporter-toggle-btn">
                                        <label for="reporter-guest" class="reporter-toggle-label">
                                            <i class="fa-solid fa-user-secret"></i> Guest / No Account
                                        </label>
                                    </div>

                                    <!-- Registered Resident Fields -->
                                    <div id="registered-user-fields" class="form-group">
                                        <label>Search Resident Database <span class="req-marker">*</span></label>
                                        <div class="resident-search-wrap">
                                            <i class="fa-solid fa-magnifying-glass resident-search-icon"></i>
                                            <input type="text" id="residentSearch" placeholder="Type name to filter..." autocomplete="off">
                                        </div>
                                        <select name="resident_id" id="resident_id" class="form-control" required size="5" style="height: auto; min-height: 140px;">
                                            <?php if ($residents && $residents->num_rows > 0): ?>
                                                <?php while ($r = $residents->fetch_assoc()): ?>
                                                    <option value="<?php echo $r['id']; ?>" data-name="<?php echo strtolower($r['full_name']); ?>" data-contact="<?php echo htmlspecialchars($r['contact_number'] ?? ''); ?>">
                                                        <?php echo htmlspecialchars($r['full_name']); ?>
                                                        (<?php echo htmlspecialchars($r['username']); ?>)
                                                        <?php if (!empty($r['contact_number'])): ?>
                                                            — <?php echo htmlspecialchars($r['contact_number']); ?>
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <option value="" disabled>No active residents found.</option>
                                            <?php endif; ?>
                                        </select>
                                        <small style="color: #6b7280; font-size: 0.8rem; display: block; margin-top: 0.5rem;">Click to select the resident.</small>
                                    </div>

                                    <!-- Guest Fields -->
                                    <div id="guest-user-fields" style="display: none;">
                                        <div class="guest-info-note">
                                            <i class="fa-solid fa-circle-info"></i>
                                            <span>This report will be filed under a guest entry. The person does not need a system account.</span>
                                        </div>
                                        <div class="form-grid" style="margin-top: 1rem;">
                                            <div class="form-group">
                                                <label>Full Name <span class="req-marker">*</span></label>
                                                <input type="text" name="guest_name" class="form-control" placeholder="e.g. Juan Dela Cruz">
                                            </div>
                                            <div class="form-group">
                                                <label>Contact Number <span class="opt-tag">(optional)</span></label>
                                                <input type="text" name="guest_contact" class="form-control" placeholder="09xxxxxxxxx" maxlength="11">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ACTIONS inside the card footer -->
                                <div class="form-actions">
                                    <button type="submit" class="btn-submit">
                                        <i class="fa-solid fa-floppy-disk"></i> Save Report
                                    </button>
                                    <a href="../reports/view_reports.php" class="btn-cancel">
                                        <i class="fa-solid fa-xmark"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- ══════════════════════════════
                             RIGHT COLUMN — CONTEXT PANELS
                             ══════════════════════════════ -->
                        <div class="context-sidebar">

                            <!-- Priority Guide -->
                            <div class="side-panel priority-box">
                                <h3 class="panel-title">
                                    <i class="fa-solid fa-scale-unbalanced"></i> Severity Guide
                                </h3>
                                <ul class="guideline-list">
                                    <li>
                                        <span class="g-icon critical"><i class="fa-solid fa-triangle-exclamation"></i></span>
                                        <span><strong>Critical (Emergency)</strong> Immediate threat to life/property (e.g. waist-deep rapid water)</span>
                                    </li>
                                    <li>
                                        <span class="g-icon high"><i class="fa-solid fa-circle-exclamation"></i></span>
                                        <span><strong>High</strong> Urgent attention needed (e.g. knee-deep water entering houses)</span>
                                    </li>
                                    <li>
                                        <span class="g-icon low"><i class="fa-solid fa-circle-info"></i></span>
                                        <span><strong>Medium / Low</strong> Noticeable impact or minor issue (e.g. clogged drain, ankle-deep water)</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Audit Notice -->
                            <div class="side-panel audit-box">
                                <h3 class="panel-title">
                                    <i class="fa-solid fa-shield-halved"></i> Audit Trail
                                </h3>
                                <ul class="guideline-list">
                                    <li>
                                        <span class="g-icon log"><i class="fa-solid fa-file-signature"></i></span>
                                        <span><strong>Encoded By You</strong> This report will be officially logged under your admin account.</span>
                                    </li>
                                    <li>
                                        <span class="g-icon check"><i class="fa-solid fa-check-double"></i></span>
                                        <span><strong>Resident Notified</strong> If a valid mobile/email exists, the resident is notified upon encoding.</span>
                                    </li>
                                </ul>
                            </div>

                        </div>
                        <!-- /RIGHT COLUMN -->

                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- FOOTER -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag | Administrative Panel</p>
    </footer>

    <!-- Scripts -->
    <script src="../../assets/js/global/sidebar.js"></script>
    <script src="../../assets/js/pages/encode_report.js"></script>
</body>
</html>

