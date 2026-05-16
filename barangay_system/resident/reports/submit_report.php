<?php
/**
 * Incident Report Submission
 * Allows residents to report new flooding or drainage issues.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

// Authenticated Resident Verification
if ($_SESSION['role'] !== 'resident') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Report | Flood and Drainage Incident Reporting and Management System</title>
    <!-- Core Admin Dashboard CSS (used for resident too) -->
    <link rel="stylesheet" href="../../assets/css/global/residents_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/global/residents_components.css">
    <!-- Specific Page CSS -->
    <link rel="stylesheet" href="../../assets/css/pages/submit_report.css">
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
                <h1>Barangay Calauag | Resident Portal</h1>
                <h2>Flood and Drainage Incident Reporting and Management System</h2>
            </div>
        </div>
        <div class="user-actions">
            <div class="profile-badge">
                <span class="role-text"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Resident'); ?></span>
                <div class="avatar-circle">
                    <?php echo strtoupper(substr($_SESSION['full_name'] ?: 'User', 0, 1)); ?>
                </div>
            </div>
            <a href="../../auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <!-- MAIN LAYOUT -->
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <h3>Menu</h3>
            <ul>
                <li><a href="../dashboard/resident_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="../reports/submit_report.php" class="active"><i class="fa-solid fa-paper-plane"></i> Submit Report</a></li>
                <li><a href="../reports/my_reports.php"><i class="fa-solid fa-file-invoice"></i> My Reports</a></li>
                <li><a href="../profile/profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            </ul>
        </aside>

        <!-- CONTENT AREA -->
        <main class="content">
            
            <!-- Title & Info Banner -->
            <div class="page-header-flex">
                <div>
                    <h2>Submit New Report</h2>
                    <p>Report flooding or drainage issues in your area.</p>
                </div>
            </div>

            <!-- Additional Info -->

            <div class="info-highlight" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-info info-icon"></i>
                <div class="info-content">
                    <h4>Important Note:</h4>
                    <p>Please provide accurate and descriptive details. Visual evidence (photos) and exact landmarks help the barangay verify and map the incident significantly faster.</p>
                </div>
            </div>

            <!-- The Form & Grid -->
            <div class="form-container">
                <form action="process_report.php" method="POST" enctype="multipart/form-data">
                    <div class="submit-report-layout">

                        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
                             LEFT COLUMN â€” MAIN FORM
                             â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
                        <div class="main-form-panel">
                            
                            <div class="form-section">
                                <div class="form-section-header">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <h3 class="form-section-title">Incident Details</h3>
                                </div>
                                <div class="form-section-body">
                                    <div class="form-grid">
                                        <!-- Report Type -->
                                        <div class="form-group">
                                            <label>Incident Type <span class="req-marker">*</span></label>
                                            <select name="type" class="form-control" required>
                                                <option value="">Select Type...</option>
                                                <option value="flood">Flood</option>
                                                <option value="drainage">Drainage Issue</option>
                                            </select>
                                        </div>

                                        <!-- Severity -->
                                        <div class="form-group">
                                            <label>Severity Level <span class="req-marker">*</span></label>
                                            <select name="severity" class="form-control" required>
                                                <option value="">Select Severity...</option>
                                                <option value="Low">Low - Minor issue</option>
                                                <option value="Medium">Medium - Noticeable impact</option>
                                                <option value="High">High - Urgent attention needed</option>
                                                <option value="Critical">Critical - Emergency</option>
                                            </select>
                                        </div>

                                        <!-- Location (Street) -->
                                        <div class="form-group full-width">
                                            <label>Street Name <span class="req-marker">*</span></label>
                                            <select name="location" class="form-control" required>
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

                                        <!-- Specific Landmark -->
                                        <div class="form-group full-width">
                                            <label>Specific Landmark / Details <span class="req-marker">*</span></label>
                                            <input type="text" name="location_details" class="form-control"
                                                placeholder="e.g. Near the blue gate, in front of the sari-sari store" required>
                                        </div>

                                        <!-- Description -->
                                        <div class="form-group full-width">
                                            <label>Description of Incident <span class="req-marker">*</span></label>
                                            <textarea name="description" class="form-control" rows="4"
                                                placeholder="Describe the situation clearly..." required></textarea>
                                        </div>

                                        <!-- Photo Upload -->
                                        <div class="form-group full-width">
                                            <label>Attach Photo <span class="opt-tag">(optional)</span></label>
                                            <input type="file" name="photo" class="form-control" accept="image/*" style="padding: 0.5rem 1rem;">
                                            <small style="color: #6b7280; display: block; margin-top: 0.4rem;">Max 5MB. Formats: JPG, PNG. A clear photo helps us assess the situation accurately.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn-submit">
                                        <i class="fa-solid fa-paper-plane"></i> Submit Report
                                    </button>
                                    <a href="../dashboard/resident_dashboard.php" class="btn-cancel">
                                        <i class="fa-solid fa-xmark"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
                             RIGHT COLUMN â€” CONTEXT PANELS
                             â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
                        <div class="context-sidebar">
                            
                            <!-- Priority Guide -->
                            <div class="side-panel priority-box">
                                <h3 class="panel-title">
                                    <i class="fa-solid fa-scale-unbalanced"></i> Severity Reference
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

                            <!-- Helpful Tips -->
                            <div class="side-panel tips-box">
                                <h3 class="panel-title">
                                    <i class="fa-solid fa-lightbulb"></i> Submission Tips
                                </h3>
                                <ul class="guideline-list">
                                    <li>
                                        <span class="g-icon tip"><i class="fa-solid fa-camera"></i></span>
                                        <span><strong>Take a photo:</strong> Visual evidence is highly recommended to prioritize response.</span>
                                    </li>
                                    <li>
                                        <span class="g-icon tip"><i class="fa-solid fa-map-pin"></i></span>
                                        <span><strong>Be specific:</strong> Include landmarks so responders can find the exact spot quickly.</span>
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

    <!-- Footer Branding -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>
    <?php require_once "../../includes/flash_toast.php"; ?>
</body>

</html>
