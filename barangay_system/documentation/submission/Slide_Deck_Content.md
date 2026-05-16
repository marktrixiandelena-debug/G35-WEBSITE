# Flood and Drainage Incident Reporting and Management System
## Slide Deck Content: Technical Documentation

This document contains the core technical components required for your presentation slides.

---

## 🏗️ 1. Entity Relationship Diagram (Crow's Foot)
*Copy this into a mermaid-compatible slide or export as an image. This version includes the "encoded_by" FK and uses 'id' primary keys matching the database.*

```mermaid
erDiagram
    USERS ||--o{ REPORTS : "reporter"
    USERS ||--o{ REPORTS : "encoder"
    USERS ||--o{ ANNOUNCEMENTS : "creates"
    USERS ||--o{ AUDIT_LOGS : "generates"
    USERS ||--o{ CASE_TIMELINE : "updates"
    
    REPORTS ||--|{ CASE_TIMELINE : "status_history"
    REPORTS ||--o{ REPORT_ASSIGNMENTS : "incidents"
    
    RESPONSE_TEAMS ||--o{ REPORT_ASSIGNMENTS : "dispatched_to"

    USERS {
        int id PK
        string full_name
        string username
        string password
        enum role
        tinyint require_password_change
        string contact_number
        text address
        enum status
        timestamp created_at
    }

    REPORTS {
        int id PK
        int user_id FK "nullable"
        string guest_name
        string guest_contact
        int encoded_by FK "nullable"
        enum type
        string location
        text location_details
        text description
        string photo_path
        enum status
        text dismissal_reason
        string report_source
        enum severity
        enum priority
        timestamp created_at
        timestamp updated_at
    }

    RESPONSE_TEAMS {
        int id PK
        string team_name
        string team_leader
        string contact_number
        enum status
        timestamp created_at
    }

    REPORT_ASSIGNMENTS {
        int id PK
        int report_id FK
        int team_id FK
        timestamp assigned_at
        enum status
    }

    ANNOUNCEMENTS {
        int id PK
        int created_by FK
        string title
        text content
        enum type
        enum status
        timestamp created_at
    }

    AUDIT_LOGS {
        int id PK
        int user_id FK
        string action
        text details
        timestamp created_at
    }

    CASE_TIMELINE {
        int id PK
        int report_id FK
        int changed_by FK "nullable"
        string status_from
        string status_to
        text notes
        timestamp created_at
    }
```

---

## 📑 2. Relational Schema (RS)
*Short-hand notation for the database structure.*

*   **Users** (<u>id</u>, full\_name, username, password, role, require\_password\_change, contact\_number, address, status, created\_at)
*   **Response_Teams** (<u>id</u>, team\_name, team\_leader, contact\_number, status, created\_at)
*   **Reports** (<u>id</u>, *user\_id*, guest\_name, guest\_contact, type, location, location\_details, description, photo\_path, status, dismissal\_reason, report\_source, *encoded\_by*, severity, priority, created\_at, updated\_at)
*   **Announcements** (<u>id</u>, title, content, type, status, *created\_by*, created\_at)
*   **Audit_Logs** (<u>id</u>, *user\_id*, action, details, created\_at)
*   **Case_Timeline** (<u>id</u>, *report\_id*, status\_from, status\_to, *changed\_by*, notes, created\_at)
*   **Report_Assignments** (<u>id</u>, *report\_id*, *team\_id*, assigned\_at, status)

---

## 📖 3. Data Dictionary
| Table | Primary Key | Foreign Keys | Key Attributes |
| :--- | :--- | :--- | :--- |
| **Users** | `id` | - | `role`, `status`, `require_password_change` |
| **Reports** | `id` | `user_id`, `encoded_by` | `type`, `location`, `status`, `severity`, `report_source` |
| **Response Teams** | `id` | - | `team_name`, `team_leader`, `status` (Automated) |
| **Assignments** | `id`| `report_id`, `team_id` | `status` (Assigned, On Site, Completed) |
| **Timeline** | `id`| `report_id`, `changed_by` | `status_from`, `status_to`, `notes` |
| **Announcements** | `id`| `created_by` | `type`, `status` |
| **Audit Logs** | `id`| `user_id` | `action`, `details` |

---

## 🐚 4. Visual UX User Flow: User Management
*This diagram covers registration, admin approval, and the forced password change security gate.*

```mermaid
graph TD
    Start((START)) --> Reg[Resident Registration Page]
    Start --> Log[Login Page]

    Reg -- "Fills Form & Submits" --> DB_Pending[(DB: Status = 'Pending')]
    DB_Pending --> Wait[Wait for Admin Approval]
    
    Log -- "Admin Logs In" --> AdminDash[Admin Dashboard]
    AdminDash --> ManageUsers[Manage Users Page]
    ManageUsers -- "Approve Resident" --> DB_Active[(DB: Status = 'Active')]
    DB_Active --> Log

    Log -- "User Logs In" --> Auth{Authenticate}
    Auth -- "Success" --> CheckStatus{Account Active?}
    
    CheckStatus -- "No" --> Error[Access Denied]
    CheckStatus -- "Yes" --> CheckPass{Force Pass Change?}
    
    CheckPass -- "Yes" --> ChangePass[Update Password]
    ChangePass --> Dashboard
    
    CheckPass -- "No" --> Dashboard[Dashboard]

    Dashboard -- "Resident" --> Profile[Profile Management]
    Dashboard -- "Admin" --> Security[RBAC / User Controls]
```

---

## 🌊 5. Visual UX User Flow: Incident Reporting
*Captures the resident reporting process and automated system reactions.*

```mermaid
graph LR
    Start((START)) --> Resident[Resident Portal]
    Resident --> ReportForm[Submit Report Form]
    
    ReportForm -- "Select Details" --> Data[Type, Location, Severity]
    Data -- "Optional" --> Photo[Photo Evidence]
    Photo --> Review[Resident Submits]
    
    Review --> DB[(Database)]
    
    subgraph System Automation
        DB -- "Auto" --> T[Timestamp Generated]
        DB -- "Auto" --> S[Status = Pending]
        DB -- "FK" --> L[Link to Resident ID]
    end
    
    S --> Admin[Visible in Admin Inbox]
```
