# User Management Module: UX User Flow

This diagram illustrates the end-to-end journey of a user within the management system, from initial registration to administrative control and security protocols.

```mermaid
graph TD
    %% Start points
    Start((START)) --> Reg[Resident Registration Page]
    Start --> Log[Login Page]

    %% Registration Flow
    Reg -- "Fills Form & Submits" --> DB_Pending[(DB: Status = 'Pending')]
    DB_Pending --> Wait[Wait for Admin Approval]
    
    %% Admin Approval Flow
    Log -- "Admin Credentials" --> Auth_Admin{System Authenticates}
    Auth_Admin -- "Success" --> AdminDash[Admin Dashboard]
    AdminDash --> ManageUsers[Manage Users Page]
    ManageUsers -- "Review & Approve" --> DB_Active[(DB: Status = 'Active')]
    DB_Active --> Log

    %% Login/Security logic
    Log -- "User/Resident Credentials" --> Auth_User{System Authenticates}
    Auth_User -- "Success" --> CheckStatus{Check Account Status}
    
    CheckStatus -- "Pending/Disabled" --> LoginError[Show Error & Block Access]
    CheckStatus -- "Active" --> CheckPass{Forced Password Change?}
    
    CheckPass -- "Yes (require_password_change=1)" --> ChangePass[Change Password Page]
    ChangePass -- "Success" --> DB_PassUpdated[(DB: Updated Password & require_password_change=0)]
    DB_PassUpdated --> RedirectDash
    
    CheckPass -- "No" --> RedirectDash[Role-Based Redirection]

    %% Post-Login Activities
    RedirectDash -- "Resident Role" --> ResDash[Resident Dashboard]
    RedirectDash -- "Admin Role" --> AdminDash

    ResDash --> Profile[Profile Management]
    Profile -- "Update Address/Contact" --> DB_Profile[(DB: Profile Updated)]

    AdminDash --> SecurityControl[RBAC & Access Control]
    SecurityControl -- "Try Disable Self/Admin" --> SystemBlock{Protection Guard}
    SystemBlock -- "Triggered" --> ErrorMsg[Block Action & Show Warning]
    SecurityControl -- "Reset User/Deactivate" --> DB_UserUpdated[(DB: User Entry Updated)]
```

### Key Logic Highlights
1.  **Account Lifecycle**: Every online registration begins as `Pending` and must pass through an Administrative manual audit before becoming `Active`.
2.  **Security Gatekeeping**: The system automatically checks for the `require_password_change` flag upon every login attempt, ensuring that temporary or admin-reset passwords are changed immediately.
3.  **Administrative Protection**: Built-in logical guards prevent the "self-lockout" scenario where an admin might accidentally disable their own account or other critical admin staff.
4.  **Role-Based Access Control (RBAC)**: Authentication leads to a fork in the flow where the user is strictly routed to either the Resident Portal or the Command Center (Admin Panel) based on their database-assigned role.

---

## Incident Reporting Module: UX User Flow

This flow maps the process of a resident providing visual and descriptive information about a community hazard and how that data enters the system's administrative workflow.

```mermaid
graph TD
    Start((START)) --> Dash[Resident Dashboard]
    Dash --> Form[Submit Report Page]
    
    Form --> SelectType[Select Type: Flood or Drainage]
    SelectType --> SelectSeverity[Select Severity: Low to Critical]
    SelectSeverity --> SelectLocation[Select Street & Enter Landmarks]
    SelectLocation --> Description[Enter Incident Description]
    
    Description --> Upload{Upload Photo?}
    Upload -- "Yes" --> ProcessPhoto[System Saves Image to Server]
    ProcessPhoto --> Submit
    Upload -- "No" --> Submit[Resident Clicks Submit]

    Submit --> DB_Save[(DB: Create Report Record)]
    
    DB_Save -- "Automatic" --> SetPending[Status = 'Pending']
    DB_Save -- "Automatic" --> SetTimestamp[created_at = current_timestamp]
    DB_Save -- "Foreign Key" --> LinkUser[Linked to user_id]

    SetPending --> AdminView[Visible in Admin View Reports Dashboard]
    LinkUser --> ResView[Visible in Resident 'My Reports' Section]
    
    AdminView --> Finish((END))
```

### Key Logic Highlights
1.  **Guided Entry**: The user interface uses structured dropdowns (Type, Severity, Street) to ensure data is clean and easier for the Admin to filter later.
2.  **Media Integration**: While photo evidence is optional to lower the barrier for quick reporting, the system handles the physical file storage and database path linking automatically if provided.
3.  **Automated Integrity**: The resident doesn't need to enter the time; the system guarantees accuracy by generating the `created_at` timestamp at the exact moment of database insertion.
4.  **Instant Visibility**: Through Relational Mapping (Foreign Keys), the report is instantly available to both the user (for tracking) and the Administrator (for emergency response).
