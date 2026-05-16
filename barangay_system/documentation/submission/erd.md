# Flood and Drainage Incident Reporting and Management System
## 🏗️ Module-Based Entity Relationship Diagrams (ERD)

This document provides the technical ERD for each system module using **Crow's Foot Notation**, strictly mapped from the current `database.sql` schema using 'id' as the primary key.

---

### 1. User Management Module
Focuses on the core `users` entity for identity and permission control.

```mermaid
erDiagram
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
```

---

### 2. Incident Reporting Module
The flow of incidents submitted by residents.

```mermaid
erDiagram
    USERS ||--o{ REPORTS : "submits"
    USERS {
        int id PK
    }
    REPORTS {
        int id PK
        int user_id FK
        string guest_name
        string guest_contact
        enum type
        string location
        text location_details
        text description
        string photo_path
        enum status
        enum severity
        enum priority
        text dismissal_reason
        string report_source
        int encoded_by FK
        timestamp created_at
        timestamp updated_at
    }
```

---

### 3. Advanced Report Management Module
Tracking the lifecycle and transition history of incidents.

```mermaid
erDiagram
    REPORTS ||--|{ CASE_TIMELINE : "tracks_history"
    USERS ||--o{ CASE_TIMELINE : "updates_status"
    REPORTS {
        int id PK
    }
    USERS {
        int id PK
    }
    CASE_TIMELINE {
        int id PK
        int report_id FK
        string status_from
        string status_to
        int changed_by FK
        text notes
        timestamp created_at
    }
```

---

### 4. Manual Report Encoding Module
Administrative logging of walk-in or offline incidents.

```mermaid
erDiagram
    USERS ||--o{ REPORTS : "encoded_by"
    USERS |o--o{ REPORTS : "reporter"
    USERS {
        int id PK
    }
    REPORTS {
        int id PK
        int user_id FK
        string guest_name
        string guest_contact
        enum type
        string location
        text location_details
        text description
        string photo_path
        enum status
        enum severity
        enum priority
        text dismissal_reason
        string report_source
        int encoded_by FK
        timestamp created_at
        timestamp updated_at
    }
```

---

### 5. Response Team Management Module
Dispatching resources to handle reported emergencies.

```mermaid
erDiagram
    REPORTS ||--o{ REPORT_ASSIGNMENTS : "receives"
    RESPONSE_TEAMS ||--o{ REPORT_ASSIGNMENTS : "handles"
    REPORTS {
        int id PK
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
        enum status
        timestamp assigned_at
    }
```

---

### 6. Communication and Advisory Module
The administrative broadcasting system.

```mermaid
erDiagram
    USERS ||--o{ ANNOUNCEMENTS : "publishes"
    USERS {
        int id PK
    }
    ANNOUNCEMENTS {
        int id PK
        string title
        text content
        enum type
        enum status
        int created_by FK
        timestamp created_at
    }
```

---

### 7. System Intelligence and Auditing Module
Monitoring administrative integrity and data trends.

```mermaid
erDiagram
    USERS ||--o{ AUDIT_LOGS : "logs_actions"
    USERS {
        int id PK
    }
    AUDIT_LOGS {
        int id PK
        int user_id FK
        string action
        text details
        timestamp created_at
    }
```

---

### 🏛️ Consolidated System ERD

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
        enum role
        enum status
    }

    REPORTS {
        int id PK
        int user_id FK
        string guest_name
        string guest_contact
        int encoded_by FK
        enum type
        string location
        text description
        enum status
        enum severity
        timestamp created_at
    }

    RESPONSE_TEAMS {
        int id PK
        string team_name
        string team_leader
        enum status
    }

    REPORT_ASSIGNMENTS {
        int id PK
        int report_id FK
        int team_id FK
        enum status
    }

    ANNOUNCEMENTS {
        int id PK
        int created_by FK
        string title
        enum type
    }

    AUDIT_LOGS {
        int id PK
        int user_id FK
        string action
    }

    CASE_TIMELINE {
        int id PK
        int report_id FK
        int changed_by FK
        string status_from
        string status_to
    }
```
