# Flood and Drainage Incident Reporting and Management System
## System Features

### 1. Global / Core System Features
These features apply to the entire system and ensure security, stability, and proper user access control.

#### 1.1 Role-Based Access Control (RBAC)
The system implements Role-Based Access Control to ensure that users can only access features appropriate to their assigned role.
The system supports two primary user roles:
*   Administrator
*   Resident

Each role is provided with:
*   A distinct dashboard
*   Access to specialized tools and modules relevant to their responsibilities.

#### 1.2 Secure Authentication
The system utilizes secure authentication mechanisms to protect user accounts and sensitive system data.
Security measures include:
*   Password encryption using BCRYPT hashing (PHP `$2y$` algorithm).
*   Protected session management to maintain secure user sessions.
*   Prevention of unauthorized access attempts.

These mechanisms ensure that user credentials and account data remain secure.

#### 1.3 Session Guard Protection
The system implements global authentication middleware to protect restricted system areas.
Key protections include:
*   Only authenticated users can access protected pages.
*   Direct access to system components via manual URL manipulation is blocked.
*   Unauthorized users are automatically redirected to the Login Page.

This ensures proper access control across the entire platform.

#### 1.4 Role-Based Redirection
After successful login authentication, the system automatically redirects users based on their assigned role.
Routing destinations include:
*   Administrator → Admin Dashboard
*   Resident → Resident Dashboard

This ensures users immediately access the correct system environment.

#### 1.5 Forced Password Change (First Login Security)
The system includes a forced password update mechanism for enhanced security.
This occurs when:
*   A new user account is created, or
*   An administrator resets a user's password.

Before accessing the dashboard, the user must:
*   Update their password through the Change Password Page.
*   Confirm the new secure password.

This prevents misuse of default or temporary passwords.

#### 1.6 Comprehensive Audit Logging
The system automatically records sensitive backend activities to maintain transparency and security.
Each audit log entry securely records:
*   User ID
*   Action performed
*   Specific action details
*   Timestamp

Examples of recorded actions include:
*   User account creation and approval
*   Report status updates
*   Report encoding
*   Team management activities
*   Announcement creation and deletion

This feature ensures accountability and traceability of system operations.

#### 1.7 Responsive User Interface
The system features a modern, responsive interface designed for usability across multiple devices.
Interface characteristics include:
*   Sidebar-based navigation layout with collapsible mobile overlay
*   Compatibility with desktop, tablet, and mobile devices
*   A consistent and standardized design across all system modules

This ensures accessibility for both administrators and residents.

#### 1.8 Online Resident Registration with Admin Approval
The system provides an online registration form for residents who wish to create their own accounts remotely.
Registration form collects:
*   Full Name
*   Username
*   Contact Number (Required)
*   Address / Street (Selected from a standardized dropdown list)
*   Password (user-defined)

Registration behavior:
*   Role is automatically set to `Resident`.
*   Account status is set to `Pending` until approved by an administrator.
*   Users with pending accounts cannot log in until approved.
*   Administrators can approve or reject pending registrations from the Manage Users page.
*   This is separate from the admin-created accounts, which use system-generated passwords for walk-in registrants.

---

### 2. Resident Features
Residents use the system primarily to report incidents and monitor the progress of their submitted reports.

#### 2.1 Personal Dashboard
Residents are provided with a centralized dashboard that displays an overview of their reporting activity.
The dashboard displays:
*   Total Reports Submitted
*   Active Reports
*   Resolved Reports

Additional dashboard features include:
*   Display of recent active announcements from administrators
*   Quick navigation buttons for easier system access.

#### 2.2 Incident Reporting Module
Residents can report flooding or drainage issues through a structured reporting form.
The form collects the following information:
*   **Incident Type**
    *   Flood
    *   Drainage Issue
*   **Severity Level**
    *   Low
    *   Medium
    *   High
    *   Critical (Emergency)
*   **Location**
    *   A filtered dropdown list of official barangay streets
*   **Location Details**
    *   Specific landmark information to help responders locate the issue.
*   **Description**
    *   Detailed explanation of the incident.
*   **Photo Evidence** *(Optional)*
    *   Accepted formats: JPG, PNG, GIF
    *   Uploaded and stored server-side.

After submission, the report is stored in the database with the default status set to `Pending`.

#### 2.3 Evidence Upload
The system supports photo evidence attachments to improve incident verification.
Features include:
*   Supported file formats: JPG, PNG, GIF
*   Helper notes to guide users during uploads

This allows residents to provide visual proof to assist response teams in evaluating the severity of incidents.

#### 2.4 Report Tracking and Timeline
Residents can track the progress of their reports through the My Reports section.
Available functions include:
*   Viewing all submitted reports with filters for Type, Severity, and Status
*   Monitoring the report status, which may be:
    *   `Pending` — Awaiting administrator review
    *   `In Progress` — Verified and a response team is working on it
    *   `Resolved` — Incident has been resolved
    *   `Dismissed` — Report was reviewed and dismissed by an administrator

Residents can also access the Case Timeline, which displays:
*   Step-by-step status updates
*   Team assignment events
*   Notes added by administrators regarding the progress of the case

This ensures transparency between residents and administrators.

#### 2.5 Profile Management
Residents can manage and update their personal account information.
Available profile features include:
*   Viewing account information:
    *   Full Name
    *   Username
*   Updating personal details:
    *   Contact Number
    *   Address

All updates are saved directly to the system database.

---

### 3. Administrator Features
Administrators are responsible for managing system operations, reports, users, and response teams.

#### 3.1 Command Center (Admin Dashboard)
The Admin Dashboard serves as the central command center for system monitoring.
Key dashboard components include:
*   System-wide statistics such as:
    *   Total residents
    *   Total reports
    *   Report counts by status (`Pending`, `In Progress`, `Resolved`)
*   Recent reports list with reporter name, location, and current status
*   Active announcements panel
*   Quick access to all system modules

This dashboard provides administrators with a real-time overview of barangay operations.

#### 3.2 User Management Module
*   **Secure User Creation**
    *   Administrators can create accounts for:
        *   New resident users
        *   Additional administrators or staff
*   **Pending Registration Review**
    *   Administrators can approve or reject residents who self-registered online.
*   **Account Management**
    *   Administrators can:
        *   View and search all registered users
        *   Activate or disable user accounts
        *   Reset user passwords when necessary
        *   View detailed user profiles
*   **Admin Lock Protection**
    *   The system includes a safeguard mechanism that:
        *   Prevents administrators from disabling their own accounts
        *   Prevents disabling other administrator accounts
        *   This prevents accidental system lockouts.

#### 3.3 Advanced Report Management Module
*   **Global Reports Inbox**
    *   Administrators can view all submitted reports in a centralized interface.
    *   Filtering available by: Type, Status, Source, and Severity.
*   **Detailed Report Inspection**
    *   Administrators can:
        *   Review full incident details including reporter information
        *   Inspect uploaded photo evidence
        *   View the full case timeline
*   **Workflow and Status Control**
    *   The report status flow is: `Pending → In Progress → Resolved`
    *   Pending reports support two distinct actions:
        *   **Verify & Accept** — Moves the report to `In Progress`
        *   **Dismiss** — Moves the report to `Dismissed` with a documented reason (one-way)
    *   In Progress reports can have their status updated to `Resolved` and notes added.
    *   `Resolved` and `Dismissed` reports are permanently finalized and become read-only.
*   **Response Team Assignment**
    *   Administrators can assign Response Teams to reports.
    *   Assignment is tracked via a separate `report_assignments` record.

#### 3.4 Report Dismissal
Administrators can dismiss reports that are spam, false, duplicate, or outside jurisdiction.
*   Only `Pending` reports can be dismissed.
*   A reason category must be selected, with an optional additional note.
*   Dismissed reports are read-only and display the dismissal reason to both admins and residents.

#### 3.5 Manual Report Encoding Component
Administrators can manually encode reports received through offline channels.
Examples include:
*   Walk-in complaints
*   Phone call reports

*   **Resident Association**
    *   Reports can be linked to:
        *   A registered resident account, or
        *   Guest / No Account
    *   When Guest / No Account is selected, additional fields appear:
        *   Guest Name
        *   Contact Number
*   **Submission Types (Report Source)**
    *   **Walk-In**: Resident came reporting to the barangay office. Uses the current timestamp automatically.
    *   **Phone Call**: Resident reported via the barangay hotline. Uses the current timestamp automatically.
    *   **Offline Batch**: Paper form collected during outage. Allows administrators to manually input the exact date and time when the incident occurred.

#### 3.6 Team Management Module
The system allows administrators to manage barangay response teams.
Administrators can:
*   Create new response teams
*   Edit team details
*   Activate or Deactivate teams to manage availability while preserving history
*   Monitor team availability and current deployment

Each team includes:
*   Team Name
*   Team Leader
*   Contact Number

Team statuses include:
*   `Active` — Team is available for deployment
*   `Inactive` — Team has been deactivated
*   `Deployed` — Computed display status; shown when a team is currently linked to an active in-progress report

#### 3.7 Announcements Management System
Administrators can broadcast important updates to residents.
Available actions include:
*   Create announcements
*   Edit announcements
*   Disable or delete announcements

Announcements can be categorized as:
*   System Update
*   Advisory
*   General

Active announcements are automatically displayed on the Resident Dashboard.

#### 3.8 System Analytics and Security Auditing
*   **Analytics Monitoring**
    *   Administrators can view system metrics including:
        *   Overview counts (Total Reports, Resolved Cases, Active/Pending, Critical Incidents)
        *   Incidents by Type (Flood vs. Drainage)
        *   Severity Levels Distribution
        *   Reports by Zone (Location)
*   **Security Audit Trail**
    *   The system maintains a transparent audit log system that records administrative actions.
    *   Audit logs allow administrators to see:
        *   Which user performed an action
        *   What action was performed
        *   When the action occurred
    *   Logs are filterable by action type and display the 100 most recent entries.
    *   Audit logs are immutable — they cannot be edited or deleted by any user.
    *   This ensures security, accountability, and administrative transparency.
