# 📋 Module Assignment: Flood & Drainage Management System

Based on the current architecture of the `barangay_system`, here is a logical distribution of the three core transaction modules to three developers. Each module is designed to cover specific functional areas while ensuring a fair distribution of complexity and tech-stack focus.

---

## 🏗️ Overview of Modules

| Module | Personal Role | Key Responsibilities | Focus Area |
| :--- | :--- | :--- | :--- |
| **Module 1** | **Resident Portal Expert** | Submission & personal tracking of incidents. | Frontend, CX, & UX |
| **Module 2** | **Logic & Process Architect** | Report review, status lifecycle, & manual encoding. | Backend & Core Logic |
| **Module 3** | **Operations Manager** | Resource dispatching, users, & communications. | Systems & Integration |

---

## 👤 Person 1: Resident Incident Interface
**Functional Domain:** The "Source" of Data – Handling how citizens report issues.

### 📁 Primary Files & Components
*   `resident/reports/submit_report.php` (Standard & Photo uploads)
*   `resident/reports/my_reports.php` (Personal dashboard)
*   `resident/reports/view_report.php` (Detailed status tracking)
*   `resident/reports/process_report.php` (Submission logic)

### 🛠️ Key Technical Challenges
*   **Photo Evidence Handling:** Securely uploading and storing incident photos (JPG/PNG, <5MB).
*   **Validated Input:** Ensuring street dropdowns and incident types are correctly captured.
*   **Resident UX:** Creating a clean, mobile-friendly interface for stressed residents reporting floods.

---

## 👤 Person 2: Incident Lifecycle & Workflow
**Functional Domain:** The "Processing" Center – Managing the life of a report from pending to resolved.

### 📁 Primary Files & Components
*   `admin/reports/view_reports.php` (Admin monitoring hub)
*   `admin/reports/report_details.php` (Status updates & Timeline logic)
*   `admin/reports/process_encode_report.php` (Manual walk-in/phone encoding)
*   `admin/reports/update_report_status.php` (State transitions)

### 🛠️ Key Technical Challenges
*   **Transaction Integrity:** Updating both the `reports` table and the `case_timeline` history table simultaneously.
*   **Report Routing:** Logic to handle differently sourced reports (Online vs. Walk-in).
*   **Complex Forms:** Building the "Manual Encoder" allowing admins to back-date reports for offline incidents.

---

## 👤 Person 3: Operations & Resource Management
**Functional Domain:** The "Orchestration" Layer – Managing the teams and people that solve the problems.

### 📁 Primary Files & Components
*   `admin/teams/` (Team CRUD & Status Management)
*   `admin/users/` (Approval of pending residents & administrator management)
*   `admin/announcements/` (Broadcasting alerts to the dashboard)
*   `admin/logs/` (System audit trails & statistics)

### 🛠️ Key Technical Challenges
*   **Dispatch Logic:** Managing the connection between a `report` and a `response_team` (Report Assignments).
*   **Security & Governance:** Overseeing the `users` table, including account approvals and password resets.
*   **Broadcast Engine:** Ensuring active announcements correctly appear on all resident dashboards asynchronously.

---

> [!TIP]
> Each person should first review the `database.sql` to understand the tables they are responsible for (e.g., Person 2 focuses on `reports` and `case_timeline`, while Person 3 focuses on `response_teams` and `users`).
