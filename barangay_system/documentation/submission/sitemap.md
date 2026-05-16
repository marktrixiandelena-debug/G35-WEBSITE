# SYSTEM SITE MAP

The site map outlines the hierarchical structure of the **Barangay Calauag Flood and Drainage Incident Reporting System**, showing the organization of pages for both Resident and Administrative users.

---

## 1. Public / Authentication Layer
These pages are accessible to all users for account creation and secure entry into their respective portals.
*   **Landing Page / Login (`auth/login.php`)**
*   **Resident Registration (`auth/register.php`)**
*   **Logout (`auth/logout.php`)**

---

## 2. Resident Portal
Accessible to registered residents of Barangay Calauag.
*   **Resident Dashboard (`resident/dashboard/`)** - Overview of active incidents and announcements.
*   **Incident Reporting**
    *   **Submit New Report (`resident/reports/submit_report.php`)** - Form with photo upload.
    *   **My Reports List (`resident/reports/my_reports.php`)** - History of submitted reports.
    *   **View Report Details (`resident/reports/view_report.php`)** - Status tracking and updates.
*   **Personal Profile (`resident/profile/profile.php`)** - Manage account details and password.

---

## 3. Administrative Portal (Barangay Officials)
Accessible only to authorized personnel and administrators.
*   **Admin Dashboard (`admin/dashboard/`)** - Main analytics, stat cards, and triage overview.
*   **Incident Management**
    *   **View All Reports (`admin/reports/view_reports.php`)** - Centralized report list with severity filters.
    *   **Report Details (`admin/reports/report_details.php`)** - Full info, photos, and status updates.
    *   **Encode Report (`admin/reports/encode_report.php`)** - Manual encoding for walk-in or offline batch reports.
*   **Response Team Management**
    *   **Manage Teams (`admin/teams/manage_teams.php`)** - List of active response teams.
    *   **Team Assignment** - Assign specific personnel to active incidents.
*   **User & Account Management**
    *   **Manage Users (`admin/users/manage_users.php`)** - List of residents and staff accounts.
    *   **Registration Requests** - Approval queue for new resident accounts.
*   **Information Hub**
    *   **Manage Announcements (`admin/announcements/`)** - Post news and emergency alerts.
    *   **System Logs (`admin/logs/`)** - Audit trail of system activity and user actions.
*   **Admin Profile (`admin/profile/`)** - Personal administrative account settings.

---

## 4. System Support Folder Structure (Non-UI)
*   **Assets (`assets/`)** - CSS styles, JavaScript logic, and system images/icons.
*   **Config (`config/`)** - Database connection strings.
*   **Includes (`includes/`)** - Shared PHP components (navbars, sidebars, footers).
*   **Uploads (`resident/uploads/`)** - Secure storage for resident-submitted incident photos.
