<?php
/**
 * Resident FAQ & Help Center
 * Collapsible accordion interface for resident guidance and reporting assistance.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

if ($_SESSION['role'] !== 'resident') {
    header("Location: ../../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ & Help | Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../../assets/css/global/residents_global.css">
    <link rel="stylesheet" href="../../assets/css/global/admindashboard.css">
    <link rel="stylesheet" href="../../assets/css/global/residents_components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ── FAQ Accordion ────────────────────────── */
        .faq-section { margin-bottom: 1.75rem; }

        .faq-section-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .faq-section-title i { font-size: 0.8rem; }

        .faq-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.625rem;
            margin-bottom: 0.5rem;
            overflow: hidden;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .faq-item:last-child { margin-bottom: 0; }
        .faq-item:hover { border-color: #d1d5db; }
        .faq-item.open {
            border-color: #c6d4ce;
            box-shadow: 0 1px 4px rgba(6, 95, 70, 0.06);
        }

        .faq-question {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.875rem 1.125rem;
            cursor: pointer;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
            transition: background 0.15s, color 0.15s;
            font-family: inherit;
            line-height: 1.45;
        }
        .faq-question:hover { background: #f9fafb; }
        .faq-item.open .faq-question {
            background: #f8fdfb;
            color: #065f46;
        }

        .faq-chevron {
            font-size: 0.65rem;
            color: #9ca3af;
            transition: transform 0.2s ease, color 0.2s;
            flex-shrink: 0;
            width: 20px;
            text-align: center;
        }
        .faq-item.open .faq-chevron {
            transform: rotate(180deg);
            color: #065f46;
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.25s ease;
        }
        .faq-answer-inner {
            padding: 0 1.125rem 1rem;
            font-size: 0.85rem;
            color: #4b5563;
            line-height: 1.7;
            border-top: 1px solid #f3f4f6;
            padding-top: 0.75rem;
        }
        .faq-answer-inner a {
            color: #065f46;
            font-weight: 600;
            text-decoration: none;
        }
        .faq-answer-inner a:hover { text-decoration: underline; }
        .faq-answer-inner ul {
            margin: 0.5rem 0 0.25rem 0;
            padding-left: 1.25rem;
        }
        .faq-answer-inner li {
            margin-bottom: 0.3rem;
        }

        /* Contact box */
        .help-contact {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-top: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        .help-contact-icon {
            width: 38px; height: 38px;
            border-radius: 0.5rem;
            background: #ecfdf5;
            color: #059669;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }
        .help-contact-text h4 {
            margin: 0 0 0.25rem 0;
            font-size: 0.88rem;
            color: #111827;
        }
        .help-contact-text p {
            margin: 0;
            font-size: 0.82rem;
            color: #6b7280;
            line-height: 1.55;
        }

        @media (max-width: 768px) {
            .faq-question { padding: 0.8rem 1rem; font-size: 0.85rem; }
            .faq-answer-inner { padding: 0 1rem 0.875rem; font-size: 0.82rem; }
        }
    </style>
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
                <div class="avatar-circle"><?php echo strtoupper(substr($_SESSION['full_name'] ?: 'U', 0, 1)); ?></div>
            </div>
            <a href="../../auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <!-- LAYOUT -->
    <div class="dashboard-container">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <h3>Menu</h3>
            <ul>
                <li><a href="../dashboard/resident_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="../reports/submit_report.php"><i class="fa-solid fa-paper-plane"></i> Submit Report</a></li>
                <li><a href="../reports/my_reports.php"><i class="fa-solid fa-file-invoice"></i> My Reports</a></li>
                <li><a href="../profile/profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
                <li><a href="../help/faq.php" class="active"><i class="fa-solid fa-circle-question"></i> FAQ & Help</a></li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="content">

            <!-- Page Header -->
            <div class="page-header-flex">
                <div>
                    <h2>FAQ & Help</h2>
                    <p>Find answers to common questions about the reporting system.</p>
                </div>
            </div>

            <!-- ═══ SECTION 1: Reporting ═══ -->
            <div class="faq-section">
                <div class="faq-section-title">
                    <i class="fa-solid fa-paper-plane"></i> Submitting Reports
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>How do I submit a flood or drainage report?</span>
                        <i class="fa-solid fa-chevron-down faq-chevron"></i>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            Go to <a href="../reports/submit_report.php">Submit Report</a> from the sidebar menu. Select the incident type (flood or drainage), choose the affected location, add details describing the situation, and click submit. You can also attach a photo to help responders assess the incident.
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>What types of incidents can I report?</span>
                        <i class="fa-solid fa-chevron-down faq-chevron"></i>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            The system currently supports two incident types:
                            <ul>
                                <li><strong>Flood Reports</strong> — standing water, rising water levels, road flooding</li>
                                <li><strong>Drainage Reports</strong> — clogged drains, broken drainage infrastructure, water flow obstructions</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>Can I attach photos to my report?</span>
                        <i class="fa-solid fa-chevron-down faq-chevron"></i>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            Yes. When submitting a report, you can upload a photo showing the incident. This helps the barangay team assess the situation more accurately. Accepted formats include JPG, PNG, and GIF.
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ SECTION 2: Tracking ═══ -->
            <div class="faq-section">
                <div class="faq-section-title">
                    <i class="fa-solid fa-clock-rotate-left"></i> Tracking Your Reports
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>Where can I check the status of my report?</span>
                        <i class="fa-solid fa-chevron-down faq-chevron"></i>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            Visit <a href="../reports/my_reports.php">My Reports</a> to see all reports you have submitted. Each report displays its current status, and you can click on any report to view full details including the case timeline.
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>What do the report statuses mean?</span>
                        <i class="fa-solid fa-chevron-down faq-chevron"></i>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <ul>
                                <li><strong>Pending</strong> — Your report has been received and is awaiting review by the barangay.</li>
                                <li><strong>In Progress</strong> — A response team has been assigned and is currently working on the incident.</li>
                                <li><strong>Resolved</strong> — The incident has been addressed and the case is closed.</li>
                                <li><strong>Dismissed</strong> — The report was reviewed but could not be processed (a reason will be provided).</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ SECTION 3: Account ═══ -->
            <div class="faq-section">
                <div class="faq-section-title">
                    <i class="fa-solid fa-user-gear"></i> Account & Profile
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>How do I update my profile information?</span>
                        <i class="fa-solid fa-chevron-down faq-chevron"></i>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            Go to <a href="../profile/profile.php">My Profile</a> from the sidebar. You can update your contact number and change your password from there. Your full name and username are set during registration and managed by the barangay administrators.
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>I forgot my password. What should I do?</span>
                        <i class="fa-solid fa-chevron-down faq-chevron"></i>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            Please contact the Barangay Calauag office or your local administrator. An admin can reset your password from the system's user management panel. You will then be able to log in and set a new password.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Contact Box -->
            <div class="help-contact">
                <div class="help-contact-icon">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div class="help-contact-text">
                    <h4>Still need help?</h4>
                    <p>If your question is not listed above, please visit the Barangay Calauag office during business hours or contact your local administrator for assistance.</p>
                </div>
            </div>

        </main>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Barangay Calauag</p>
    </footer>

    <script src="../../assets/js/global/sidebar.js"></script>
    <script>
        // Accordion toggle with smooth height animation
        document.querySelectorAll('.faq-question').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var item = this.closest('.faq-item');
                var answer = item.querySelector('.faq-answer');
                var isOpen = item.classList.contains('open');

                // Close all other items in the same section
                item.closest('.faq-section').querySelectorAll('.faq-item.open').forEach(function(openItem) {
                    if (openItem !== item) {
                        openItem.classList.remove('open');
                        openItem.querySelector('.faq-answer').style.maxHeight = null;
                    }
                });

                // Toggle current item
                if (isOpen) {
                    item.classList.remove('open');
                    answer.style.maxHeight = null;
                } else {
                    item.classList.add('open');
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                }
            });
        });
    </script>
</body>
</html>
