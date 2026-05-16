# 🌊 Flood and Drainage Incident Reporting and Management System

## System Flow Overview

### 🔐 1. Entry & Authentication Flow
The system begins at the Landing Page, where users are introduced to the reporting platform.

*   **Landing Page**
    *   Displays general information about the flood and drainage reporting system.
    *   Provides a button to proceed to the Login Page.

*   **Login Page**
    *   Users must enter:
        *   Username
        *   Password
    *   The system then:
        *   Verifies the user credentials.
        *   Checks account status — users with `Pending` or `Disabled` accounts cannot log in.
        *   Determines the user role:
            *   Administrator
            *   Resident

*   **📝 Resident Registration**
    *   New residents can create an account via the Registration Page.
    *   **Data Collected:**
        *   Full Name
        *   Username
        *   Contact Number
        *   Residential Address (Standardized street dropdown)
        *   Password
    *   **Process:**
        *   Account is created with a `Pending` status.
        *   User is redirected to the Login Page with a success message.
        *   **Note:** The user cannot log in until an Administrator approves their account.

*   **Password Management**
    *   If the account requires a password update (such as newly created or reset accounts):
        *   The user is automatically redirected to the Change Password page.
        *   After successfully updating the password:
            *   The user is redirected to their respective dashboard.
    *   If no password update is required:
        *   The user is directly redirected to their assigned dashboard.

---

### 👤 2. Resident Flow
After logging in, Residents gain access to the system's reporting and monitoring features.

*   **📊 Resident Dashboard**
    *   Residents can view a summary of their reporting activities:
        *   **Dashboard Statistics**
            *   Total Reports
            *   Active Reports
            *   Resolved Reports
    *   **Additional Features**
        *   View recent active announcements.
        *   Use the sidebar navigation menu to quickly access system features.

*   **📝 Submit a Report**
    *   Residents can report incidents through the Submit Report form.
    *   **Required Information**
        *   Incident Type
            *   Flood
            *   Drainage Issue
        *   Severity Level
            *   Low
            *   Medium
            *   High
            *   Critical
        *   Street Name
            *   Selected from a dropdown list of barangay streets
        *   Specific Landmark / Details
        *   Incident Description
    *   **Optional**
        *   Photo Evidence
            *   Accepted formats: JPG, PNG, GIF
    *   **System Action**
        *   After submission, the system:
            *   Saves the report and photo evidence to the database.
            *   Sets the default report status to `Pending`.
            *   Sets the report source to `Online`.

*   **📂 Track Reports (My Reports)**
    *   Residents can monitor the progress of their reports through My Reports.
    *   **Features**
        *   Residents can:
            *   View a list of submitted reports.
            *   Filter reports by Type, Severity, or Status.
            *   Check the current status of each report:
                *   `Pending` — Awaiting administrator review
                *   `In Progress` — Verified; response team is working on it
                *   `Resolved` — Incident has been resolved
                *   `Dismissed` — Report was reviewed and rejected by an administrator
    *   **Detailed Report View**
        *   When opening a specific report, residents can:
            *   View complete incident details.
            *   See uploaded photo evidence.
            *   View the assigned response team (if any).
            *   Monitor the case timeline, including status updates, team assignments, and administrator notes.
            *   View the dismissal reason if the report was dismissed.

*   **👤 Manage Profile**
    *   Residents can manage their personal information.
    *   **Available Actions**
        *   View account details:
            *   Username
            *   Full Name
        *   Update:
            *   Contact Number
            *   Address
    *   Changes are saved directly to the system database.

---

### 🛠 3. Administrator Flow
Administrators manage the system, users, reports, and operational teams.

*   **📊 Admin Dashboard**
    *   Administrators can monitor the overall system status.
    *   **Dashboard Features**
        *   View system-wide statistics:
            *   Total Residents
            *   Total Reports
            *   Pending Reports
            *   In Progress Reports
            *   Resolved Reports
        *   View the 5 most recent reports with reporter name, location, and status.
        *   View currently active announcements.
        *   Access quick management actions.

*   **👥 User Management**
    *   Administrators manage all registered system users.
    *   **Functions**
        *   View all users:
            *   Administrators
            *   Residents
        *   Activate or disable user accounts
        *   Reset user passwords
        *   View detailed user profiles
        *   Approve or reject pending online registrations
    *   **Account Creation**
        *   Administrators can create:
            *   Resident accounts
            *   Administrator accounts

*   **👷 Team Management**
    *   Administrators manage response teams responsible for incident handling.
    *   **Features**
        *   View all response teams with current status:
            *   `Active` — Available for deployment
            *   `Inactive` — Deactivated
            *   `Deployed` — Currently assigned to an active in-progress report (computed)
    *   **Team Creation**
        *   Administrators can create new teams by entering:
            *   Team Name
            *   Team Leader
            *   Contact Number
    *   **Team Actions**
        *   Administrators can:
            *   Update team information
            *   Deactivate a team (preserves history)

*   **📋 Report Handling**
    *   Administrators oversee all submitted reports.
    *   **Report Inbox**
        *   View all reports in a centralized table.
        *   Filter by Type, Status, Source (Online, Walk-In, Phone Call, Offline Batch), and Severity.
    *   **Report Status Flow**
        *   `Pending` → Admin verifies → `In Progress`
        *   `In Progress` → Admin resolves → `Resolved`
        *   `Pending` → Admin dismisses → `Dismissed` *(irreversible, one-way)*
    *   **Detail View Actions**
        *   For `Pending` reports:
            *   **Verify & Accept** button — moves to `In Progress`
            *   **Dismiss Report** form — requires reason selection and moves to `Dismissed`
        *   For `In Progress` reports:
            *   Status dropdown (In Progress / Resolved)
            *   Team assignment dropdown
            *   Case notes textarea
            *   Save Changes button
        *   For `Resolved` / `Dismissed` reports:
            *   Read-only finalized state with specific visual alerts. No further changes can be made. For dismissed reports, the specific dismissal reason is displayed.
    *   **Team Assignment**
        *   Administrators can assign Response Teams to reports from the Report Details page.
        *   Assignment is recorded as a `report_assignments` entry.
        *   Assignment event is automatically logged to the case timeline.

*   **📝 Manual Report Encoding**
    *   Administrators can encode reports that were received outside the system.
    *   **Account Selection**
        *   Reports can be associated with:
            *   Registered Resident accounts
            *   Guest / No Account
        *   If Guest / No Account is selected, additional fields appear:
            *   Guest Name
            *   Contact Number
    *   **Submission Types (Report Source)**
        *   **Walk-In**
            *   Resident came to the barangay office. Uses current timestamp.
        *   **Phone Call**
            *   Resident called the barangay hotline. Uses current timestamp.
        *   **Offline Batch**
            *   Paper form collected during outage. Allows administrators to manually input the specific past date and time when the incident occurred.

*   **📢 Announcements**
    *   Administrators can broadcast important updates to residents.
    *   **Features**
        *   Create announcements
        *   Edit announcements
        *   Toggle status (Active / Inactive)
        *   Delete announcements
    *   **Announcement Categories**
        *   System Update
        *   Advisory
        *   General
    *   **Status Control**
        *   Announcements can be:
            *   Active — visible on the Resident Dashboard and Admin Dashboard
            *   Inactive — hidden from all dashboards

*   **📊 Monitoring & Logs**
    *   Administrators can monitor system usage and activities.
    *   **Analytics**
        *   View overview metrics (Total, Resolved, Active, Critical)
        *   Analyze incidents by Type, Severity, and Location Zone
    *   **Audit Logs**
        *   Filterable by action type
        *   Each log entry records:
            *   User performing the action
            *   Action performed
            *   Detailed description
            *   Timestamp
        *   Audit logs are immutable — they cannot be edited or deleted.
    *   This ensures security, transparency, and accountability.

---

### 🔓 4. Logout Flow
Users can log out anytime through the header logout button.

*   **System Action**
    *   When logging out, the system:
        *   Securely clears the active session.
        *   Redirects the user back to the Login Page.
