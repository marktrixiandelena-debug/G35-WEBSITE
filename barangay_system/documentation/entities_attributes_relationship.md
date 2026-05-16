# Flood and Drainage Incident Reporting and Management System
## Database Entities and Schema

### 1. Entity: Users (Account Management)
**Table Name:** `users`
This table stores all system user accounts including both administrators and residents.

| Field | Data Type | Description |
| :--- | :--- | :--- |
| `id` | INT(11), PK, Auto Increment | Unique identifier for each user. |
| `full_name` | VARCHAR(100), NOT NULL | The user's complete legal name. |
| `username` | VARCHAR(50), NOT NULL, UNIQUE | Unique username used for system login. |
| `password` | VARCHAR(255), NOT NULL | Securely hashed password using BCRYPT (`$2y$`). |
| `role` | ENUM('admin','resident'), NOT NULL | Defines the user's permission level in the system. |
| `require_password_change` | TINYINT(1), DEFAULT 0 | Indicates whether the user must update their password on the next login. |
| `contact_number` | VARCHAR(11), DEFAULT NULL | User's mobile or telephone number. |
| `address` | TEXT, DEFAULT NULL | The user's residential address (Selected from a standardized street list). |
| `status` | ENUM('active','disabled','pending'), DEFAULT 'active' | Indicates account state: active, disabled, or pending admin approval (for online registrations). |
| `created_at` | TIMESTAMP, DEFAULT CURRENT_TIMESTAMP | Timestamp of when the user account was created. |

---

### 2. Entity: Reports (Incident Data)
**Table Name:** `reports`
This table stores all reported flood or drainage incidents submitted by residents or encoded by administrators.

| Field | Data Type | Description |
| :--- | :--- | :--- |
| `id` | INT(11), PK, Auto Increment | Unique identifier for each report. |
| `user_id` | INT(11), FK → `users.id` ON DELETE CASCADE, DEFAULT NULL | Identifies the resident who submitted the report online (null if guest/walk-in). |
| `guest_name` | VARCHAR(150), DEFAULT NULL | Name of the person reporting if they don't have an online account. |
| `guest_contact` | VARCHAR(11), DEFAULT NULL | Contact number of the guest reporter. |
| `type` | ENUM('flood','drainage'), NOT NULL | Category of the reported issue. |
| `location` | VARCHAR(255), NOT NULL | The general location or street where the incident occurred. |
| `location_details` | TEXT, DEFAULT NULL | Additional details such as landmarks or nearby establishments. |
| `description` | TEXT, NOT NULL | Detailed explanation of the reported problem. |
| `photo_path` | VARCHAR(255), DEFAULT NULL | File path of the uploaded photo evidence. |
| `status` | ENUM('pending','in_progress','resolved','dismissed'), DEFAULT 'pending' | Current progress status of the report. |
| `dismissal_reason` | TEXT, DEFAULT NULL | Detailed reason provided if the report is dismissed by an administrator. |
| `report_source` | VARCHAR(50), NOT NULL, DEFAULT 'Walk-In' | Origin of the report (e.g., Online, Walk-In, Phone Call, Offline Batch). |
| `encoded_by` | INT(11), FK → `users.id` ON DELETE SET NULL, DEFAULT NULL | Identifies the administrator who manually encoded this report. |
| `severity` | ENUM('Low','Medium','High','Critical'), DEFAULT 'Low' | Severity level determined by the reporter. |
| `created_at` | TIMESTAMP, DEFAULT CURRENT_TIMESTAMP | Timestamp when the report was submitted. |
| `updated_at` | TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Automatically updates whenever report data changes. |

---

### 3. Entity: Announcements (Community Notices)
**Table Name:** `announcements`
This table stores announcements broadcasted by administrators to residents.

| Field | Data Type | Description |
| :--- | :--- | :--- |
| `id` | INT(11), PK, Auto Increment | Unique identifier for each announcement. |
| `title` | VARCHAR(255), NOT NULL | Title or headline of the announcement. |
| `content` | TEXT, NOT NULL | Detailed message or announcement body. |
| `type` | ENUM('System Update','Advisory','General'), DEFAULT 'General' | Classification of the announcement. |
| `status` | ENUM('Active','Inactive'), DEFAULT 'Active' | Determines if the announcement is currently visible to residents. |
| `created_by` | INT(11), FK → `users.id` ON DELETE CASCADE | References which administrator created the announcement. |
| `created_at` | TIMESTAMP, DEFAULT CURRENT_TIMESTAMP | Timestamp when the announcement was published. |

---

### 4. Entity: Response Teams (Responders)
**Table Name:** `response_teams`
This table stores information about barangay response teams responsible for handling reported incidents.

| Field | Data Type | Description |
| :--- | :--- | :--- |
| `id` | INT(11), PK, Auto Increment | Unique identifier for each response team. |
| `team_name` | VARCHAR(100), NOT NULL | Official name of the response team. |
| `team_leader` | VARCHAR(100), NOT NULL | Name of the team leader. |
| `contact_number` | VARCHAR(11), NOT NULL | Contact number used for team communication. |
| `status` | ENUM('Active','Inactive','Deployed'), DEFAULT 'Active' | Stored status: Active or Inactive. Note: 'Deployed' is a computed display status shown in the UI when a team has an active in-progress report assigned — it is not written to this column directly. |
| `created_at` | TIMESTAMP, DEFAULT CURRENT_TIMESTAMP | Timestamp of when the team record was created. |

---

### 5. Entity: Report Assignments (Dispatching)
**Table Name:** `report_assignments`
This table records which response team is assigned to handle a specific report. It serves as the junction (bridge) table resolving the Many-to-Many relationship between `reports` and `response_teams`.

| Field | Data Type | Description |
| :--- | :--- | :--- |
| `id` | INT(11), PK, Auto Increment | Unique identifier for each assignment record. |
| `report_id` | INT(11), FK → `reports.id` ON DELETE CASCADE | References the report that is being assigned. |
| `team_id` | INT(11), FK → `response_teams.id` ON DELETE CASCADE | References the response team dispatched to handle the report. |
| `assigned_at` | TIMESTAMP, DEFAULT CURRENT_TIMESTAMP | Timestamp when the team was assigned to the report. |
| `status` | ENUM('Assigned','Completed'), DEFAULT 'Assigned' | Tracks the current progress of the assigned response team. |

---

### 6. Entity: Audit Logs (Security Trail)
**Table Name:** `audit_logs`
This table records system activities performed by users, ensuring transparency and accountability.

| Field | Data Type | Description |
| :--- | :--- | :--- |
| `id` | INT(11), PK, Auto Increment | Unique identifier for each log entry. |
| `user_id` | INT(11), FK → `users.id` ON DELETE CASCADE | References the user who performed the recorded action. |
| `action` | VARCHAR(100), NOT NULL | Short description of the action performed. |
| `details` | TEXT, DEFAULT NULL | Detailed explanation of the system activity. |
| `created_at` | TIMESTAMP, DEFAULT CURRENT_TIMESTAMP | Timestamp when the system action occurred. |

---

### 7. Entity: Case Timeline (Status History)
**Table Name:** `case_timeline`
This table records the complete history of status changes for each report, allowing residents and administrators to track the progress of incidents transparently.

| Field | Data Type | Description |
| :--- | :--- | :--- |
| `id` | INT(11), PK, Auto Increment | Unique identifier for each timeline entry. |
| `report_id` | INT(11), FK → `reports.id` ON DELETE CASCADE | References the report whose status was changed. |
| `status_from` | VARCHAR(50), DEFAULT NULL | Previous status of the report (e.g., pending). Null on the first entry. |
| `status_to` | VARCHAR(50), NOT NULL | Updated status of the report (e.g., in_progress). Also used to record team assignment events (e.g., 'Team Assigned'). |
| `changed_by` | INT(11), FK → `users.id` ON DELETE SET NULL | References the administrator who made the status update. Null if user deleted. |
| `notes` | TEXT, DEFAULT NULL | Optional remarks or notes regarding the status change or update. |
| `created_at` | TIMESTAMP, DEFAULT CURRENT_TIMESTAMP | Timestamp indicating when the status update occurred. |

---

## Final Relationships

### 1. Users → Announcements
**Relationship:** `||--o{` (One-to-Many)
An administrator must be the author of any announcement, meaning each announcement is associated with exactly one user. However, a user may exist in the system without ever posting an announcement, or may publish multiple announcements over time.

**Cardinality Explanation**
*   One User → Zero or Many Announcements
*   One Announcement → Exactly One User

### 2. Users → Audit Logs
**Relationship:** `||--o{` (One-to-Many)
Every system action recorded in the audit log must be associated with exactly one user who performed the action. A newly created or inactive user may not yet have generated any log entries, while an active user may accumulate numerous logs over time.

**Cardinality Explanation**
*   One User → Zero or Many Audit Logs
*   One Audit Log → Exactly One User

### 3. Users → Reports (Reporting)
**Relationship:** `||--o{` (One-to-Many)
An online incident report must be submitted by exactly one resident (via `user_id`). However, reports encoded manually by an admin may be linked to no user if it is a Guest/Walk-in (in which case `user_id` is NULL). Thus, a report is associated with Zero or One Resident User. A resident may exist in the system without submitting any reports, or may submit multiple reports over time.

**Cardinality Explanation**
*   One User (Resident) → Zero or Many Reports
*   One Report → Zero or One User (Resident) *(Null for Guests)*

### 4. Users → Reports (Encoding)
**Relationship:** `||--o{` (One-to-Many)
Administrators can manually encode reports (offline/walk-in) using the `encoded_by` field. An admin user can encode zero, one, or multiple reports. A single report is either encoded by one admin or none (if submitted online directly by the resident).

**Cardinality Explanation**
*   One User (Admin) → Zero or Many Encoded Reports
*   One Report → Zero or One User (Admin Encoder)

### 5. Users → Case Timeline
**Relationship:** `||--o{` (One-to-Many)
Whenever a report status is updated, the system records the administrator responsible for the change using the `changed_by` field. An administrator may never update any report status, or may update many reports throughout system operations. If the administrator account is removed, the timeline record is preserved with `changed_by = NULL`.

**Cardinality Explanation**
*   One User (Admin) → Zero or Many Timeline Updates
*   One Timeline Entry → Zero or One User

### 6. Reports → Case Timeline
**Relationship:** `||--o{` (One-to-Many)
As administrators update a report's progress, timeline entries are recorded. A new report may have no timeline entries yet, and entries accumulate as the report moves through the workflow. Team assignment events are also recorded as timeline entries with a special `status_to` value of `'Team Assigned'`.

**Cardinality Explanation**
*   One Report → Zero or Many Timeline Entries
*   One Timeline Entry → Exactly One Report

### 7. Reports → Report Assignments
**Relationship:** `||--o{` (One-to-Many)
Each assignment record must reference exactly one report. When a report is initially submitted, no response team may be assigned yet. Once an administrator reviews the report, a team can be dispatched, creating an assignment record. Since the schema does not strictly restrict multiple assignments for the same report, the relationship remains Zero or Many at the database level.

**Cardinality Explanation**
*   One Report → Zero or Many Assignments
*   One Assignment → Exactly One Report

### 8. Response Teams → Report Assignments
**Relationship:** `||--o{` (One-to-Many)
Each assignment record must reference exactly one response team. A newly created response team may not yet have been deployed to any incidents, while active teams may handle multiple incident assignments across different reports.

**Cardinality Explanation**
*   One Response Team → Zero or Many Assignments
*   One Assignment → Exactly One Response Team
