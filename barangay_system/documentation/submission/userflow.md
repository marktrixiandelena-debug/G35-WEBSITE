# 🌀 System User Flows

This document outlines the step-by-step classic user flows for each module within the Flood and Drainage Incident Reporting and Management System.

---

### 1. User Management Module (Account Creation & Activation)
**Participants:** Resident, Administrator
1.  **Start:** Resident visits the **Registration Page**.
2.  **Registration:** Resident fills out personal details (Full Name, Contact, Address) and submits.
3.  **Hold:** System creates a user record with status set to `Pending`. Access is restricted.
4.  **Review:** Administrator logs into the **Admin Panel** and navigates to **Manage Users**.
5.  **Approval:** Administrator reviews the pending registration and clicks **Approve**.
6.  **Activation:** User status changes to `Active`.
7.  **End:** Resident can now log in and access the Resident Dashboard.

---

### 2. Incident Reporting Module (Resident Submission)
**Participants:** Resident
1.  **Start:** Resident logs into the system.
2.  **Form Access:** Resident navigates to the **Submit Report** section via the sidebar.
3.  **Data Entry:** Resident selects incident type (Flood/Drainage), severity, and street name.
4.  **Evidence:** Resident uploads photo evidence (optional but recommended).
5.  **Submission:** Resident clicks **Submit Report**.
6.  **Processing:** System saves records to `reports` and marks status as `Pending`.
7.  **End:** Resident is redirected to **My Reports** to view their submission history.

---

### 3. Advanced Report Management Module (Workflow & Timeline)
**Participants:** Administrator, Resident
1.  **Start:** Administrator logs in and opens the **View Reports** inbox.
2.  **Detail View:** Administrator selects a report to view full incident details and photos.
3.  **Action:** Administrator clicks **Update Status** (e.g., Change from `Pending` to `In Progress`).
4.  **Remarks:** Administrator adds a progress note (e.g., "Assessing water level blockage").
5.  **Transition:** System updates the report record and automatically creates a new **Case Timeline** entry.
6.  **Transparency:** Resident views their report; they now see the updated status and the admin's note in the history.
7.  **End:** Process repeats until the report is marked as `Resolved` or `Closed`.

---

### 4. Manual Report Encoding Module (Offline/Guest Logging)
**Participants:** Administrator
1.  **Start:** Administrator receives a report via Walk-in or Phone Call.
2.  **Form Access:** Administrator navigates to the **Encode Report** page.
3.  **Association:** Administrator chooses to link the report to a **Registered Resident** or logs it as a **Guest**.
4.  **Mode Selection:** Admin chooses **Real-time** (current time) or **Offline Batch** (manual date/time override).
5.  **Submission:** Administrator fills in the incident details and hits **Encode**.
6.  **Logging:** System records the report and creates an **Audit Log** entry identifying the encoder.
7.  **End:** The encoded report appears in the global inbox just like an online submission.

---

### 5. Response Team Management Module (Automated Deployment)
**Participants:** Administrator, Response Team
1.  **Start:** Administrator determine that an incident requires physical attention.
2.  **Selection:** Administrator views the list of response teams. 
3.  **Dispatch:** Administrator assigns a specific team to a specific incident report.
4.  **Automated Status:** The system automatically identifies the team as **Deployed** in the dashboard as long as their assigned report is in an active state (Pending/In Progress).
5.  **Tracking:** Officials can see which street the team is currently helping by looking at the "Active Deployments" count.
6.  **Resolution:** Once the team finishes the work, the Administrator marks the **Report status** as **Resolved**.
7.  **Auto-Revert:** The system automatically detects the report is resolved and reverts the team's visible status back to **Active** (Available).

---

### 6. Communication and Advisory Module (Announcements)
**Participants:** Administrator, Resident
1.  **Start:** Administrator needs to broadcast a flood advisory.
2.  **Creation:** Admin navigates to **Manage Announcements** and clicks **New Announcement**.
3.  **Targeting:** Admin enters title, message, and selects category (e.g., Advisory).
4.  **Activation:** Admin sets the status to `Active`.
5.  **Broadcast:** Resident logs in or refreshes their dashboard.
6.  **Visibility:** The announcement appears in a prominent section of the **Resident Dashboard**.
7.  **End:** Admin disables the announcement once the advisory period ends.

---

### 7. System Intelligence and Auditing Module (Monitoring)
**Participants:** Administrator
1.  **Start:** Administrator performs a sensitive action (e.g., Disabling a user account).
2.  **Background:** System automatically generates a record in the **Audit Logs**.
3.  **Analytics:** Administrator navigates to the **Analytics Dashboard** to view data trends.
4.  **Data Review:** Admin views the distribution of flood reports per street and severity.
5.  **Auditing:** Administrator checks the **System Logs** to verify recent activity and ensure accountability.
6.  **End:** Data insights are used by the administrator to prioritize barangay response resources.
