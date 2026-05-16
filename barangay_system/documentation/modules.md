# 📦 Official Modules of the System

**System Name:** Flood and Drainage Incident Reporting and Management System
**Barangay:** Barangay Calauag
**Last Updated:** May 15, 2026

---

## Overview

This document outlines the **7 official modules** of the system, covering the full lifecycle of flood and drainage incident management — from resident registration and report submission, through admin processing and team deployment, to transparency via announcements and accountability via audit logs.

---

## Module 1 — User Management Module

**Portal:** Admin + Resident
**Directory:** `admin/users/`, `admin/profile/`, `resident/profile/`, `auth/`
**DB Table:** `users`

### Description
Handles the complete account lifecycle, authentication, and personal profile management for both Admins and Residents.

### Features
| Feature | Description |
|---|---|
| Resident Registration | Resident self-registers; account status set to `Pending` |
| Admin Approval | Admin reviews and approves/rejects `Pending` registrations |
| Account Control | Supports `active`, `disabled`, `pending` statuses |
| Role-Based Access | Distinct access levels for `admin` and `resident` roles |
| Password Security | BCrypt hashing and forced password change on first login |
| Admin Lock | Safeguards to prevent admins from disabling admin accounts |
| Profile Management | Users can update their own contact number and address |

---

## Module 2 — Incident Reporting Module

**Portal:** Resident
**Directory:** `resident/reports/`
**DB Table:** `reports`

### Description
Allows registered residents to submit flood and drainage incident reports with optional photo evidence directly from the Resident Portal.

### Features
| Feature | Description |
|---|---|
| Submit Report | Residents file incidents with type, severity, location, and description |
| Severity Levels | `Low / Medium / High / Critical` |
| Location | Standardized street selection to ensure data consistency |
| Evidence Upload | Optional photo upload (JPG, PNG, GIF) |
| Report Tracking | Residents view their own history and monitor progress badges |

---

## Module 3 — Workflow and Report Management Module

**Portal:** Admin
**Directory:** `admin/reports/`
**DB Tables:** `reports`, `case_timeline`

### Description
Provides administrators with the tools to review, verify, dismiss, or resolve reports, maintaining resident transparency through a public case timeline and notes.

### Features
| Feature | Description |
|---|---|
| Admin Inbox | Centralized view of all reports with advanced filtering |
| Verification | Verify `Pending` reports to move them to `In Progress` |
| Dismissal | Dismiss invalid reports with a reason category (One-way) |
| Resolution | Mark reports as `Resolved`, finalizing the case and assignments |
| Case Timeline | Auto-logs all status changes, team assignments, and admin notes |
| Finalization | `Resolved` and `Dismissed` reports become read-only records |

### Report Status Flow
1. **`pending`**: Awaiting review.
2. **`in_progress`**: Accepted and potentially assigned a team.
3. **`resolved`**: Incident successfully addressed (Final).
4. **`dismissed`**: Report rejected with reason (Final).

---

## Module 4 — Manual Reporting Encoding Module

**Portal:** Admin
**Directory:** `admin/users/` (encode_report.php)
**DB Table:** `reports`

### Description
Enables administrators to manually log reports from Walk-in, Phone call, or backdated offline batch entries.

### Features
| Feature | Description |
|---|---|
| Encode Report | Admin logs incident on behalf of a reporter |
| Reporter Association | Link to a registered resident or log as a Guest |
| Report Source | Specifically tagged as `Walk-In`, `Phone Call`, or `Offline Batch` |
| Offline Batch | Allows custom date/time entry for past incidents |

---

## Module 5 — Response Team Management Module

**Portal:** Admin
**Directory:** `admin/teams/`
**DB Tables:** `response_teams`, `report_assignments`

### Description
Manages response teams and their assignments. Deployment status is automatically updated based on active reports.

### Features
| Feature | Description |
|---|---|
| Team Directory | Create and edit teams with leader and contact info |
| Dispatching | Assign teams to `In Progress` reports from the details page |
| Deployment Status | `Deployed` status is computed in UI for active assignments |
| Assignment Closure | Assignments are auto-completed when a report is `Resolved` |
| Team Protection | Prevents deactivation of teams currently deployed to active reports |

---

## Module 6 — Communication and Advisory Module

**Portal:** Admin + Resident
**Directory:** `admin/announcements/`, `resident/dashboard/`
**DB Table:** `announcements`

### Description
Broadcasting system for official updates and emergency advisories.

### Features
| Feature | Description |
|---|---|
| Manage Announcements | Admin creates and categorizes (System, Advisory, General) |
| Visibility Control | Toggle status between `Active` and `Inactive` |
| Resident Distribution | Active posts appear prominently on resident dashboards |

---

## Module 7 — Analytics and Audit Module

**Portal:** Admin
**Directory:** `admin/logs/`, `admin/dashboard/`
**DB Table:** `audit_logs`

### Description
Provides operational oversight through advanced analytics and a security audit trail.

### Features
| Feature | Description |
|---|---|
| Analytics Dashboard | Visual trends and ranked "Top Affected Areas" |
| Audit Trail | List of the 100 most recent actions, filterable by action type |
| Immutable Logs | Security records that cannot be edited or deleted |
