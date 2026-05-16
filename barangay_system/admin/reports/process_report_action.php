<?php
/**
 * Report Action Processor (Admin)
 * Orchestrates status lifecycle transitions, situational team assignments, and 
 * case timeline logging for individual incident reports.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $report_id  = (int)($_POST['report_id']  ?? 0);
    $new_status = trim($_POST['new_status']  ?? '');
    $team_id    = (int)($_POST['team_id']    ?? 0);
    $notes      = trim($_POST['notes']       ?? '');
    $admin_id   = $_SESSION['user_id'];

    if ($report_id <= 0) {
        header("Location: view_reports.php");
        exit();
    }

    // --- Fetch actual current status from DB (prevents bypassing UI) ---
    $fetchStmt = $conn->prepare("SELECT status FROM reports WHERE id = ?");
    $fetchStmt->bind_param("i", $report_id);
    $fetchStmt->execute();
    $currentReport = $fetchStmt->get_result()->fetch_assoc();
    $fetchStmt->close();

    if (!$currentReport) {
        header("Location: view_reports.php?error=" . urlencode("Report not found."));
        exit();
    }

    $db_status = $currentReport['status'];

    // --- Enforce read-only states: resolved and dismissed are permanently finalized ---
    if ($db_status === 'resolved' || $db_status === 'dismissed') {
        header("Location: report_details.php?id=$report_id&error=" . urlencode("This report is finalized and cannot be modified."));
        exit();
    }

    // --- Validate allowed status transitions ---
    $allowed_transitions = [
        'pending'     => ['in_progress'],
        'in_progress' => ['resolved'],
    ];

    $status_changed = ($new_status !== $db_status);

    if ($status_changed) {
        $allowed_next = $allowed_transitions[$db_status] ?? [];
        if (!in_array($new_status, $allowed_next, true)) {
            header("Location: report_details.php?id=$report_id&error=" . urlencode("Invalid status transition."));
            exit();
        }
    }

    // --- 1. Update Status ---
    // Track whether notes were already persisted inside the status-change log
    // so we don't duplicate them in step 3.
    $notes_logged_with_status = false;

    if ($status_changed) {
        $updateStmt = $conn->prepare("UPDATE reports SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $new_status, $report_id);
        $updateStmt->execute();
        $updateStmt->close();

        // Log status change to Case Timeline — notes are bundled here if provided
        $logStmt = $conn->prepare("INSERT INTO case_timeline (report_id, status_from, status_to, changed_by, notes) VALUES (?, ?, ?, ?, ?)");
        $logStmt->bind_param("issis", $report_id, $db_status, $new_status, $admin_id, $notes);
        $logStmt->execute();
        $logStmt->close();
        $notes_logged_with_status = true; // notes already saved; skip step 3

        // On resolution: close all open assignments for this report
        if ($new_status === 'resolved') {
            $completeStmt = $conn->prepare("UPDATE report_assignments SET status = 'Completed' WHERE report_id = ? AND status = 'Assigned'");
            $completeStmt->bind_param("i", $report_id);
            $completeStmt->execute();
            $completeStmt->close();
        }
    }

    // --- 2. Assign Team (only valid for non-resolved transitions) ---
    if ($team_id > 0 && $new_status !== 'resolved') {
        // Check if there is already an active assignment to this same team
        $check = $conn->prepare("SELECT team_id FROM report_assignments WHERE report_id = ? AND status = 'Assigned' ORDER BY assigned_at DESC LIMIT 1");
        $check->bind_param("i", $report_id);
        $check->execute();
        $current_assignment = $check->get_result()->fetch_assoc();
        $check->close();

        if (!$current_assignment || $current_assignment['team_id'] != $team_id) {
            // Mark previous active assignments as Completed
            $completeStmt = $conn->prepare("UPDATE report_assignments SET status = 'Completed' WHERE report_id = ? AND status = 'Assigned'");
            $completeStmt->bind_param("i", $report_id);
            $completeStmt->execute();
            $completeStmt->close();

            // Insert new assignment
            $assignStmt = $conn->prepare("INSERT INTO report_assignments (report_id, team_id, status) VALUES (?, ?, 'Assigned')");
            $assignStmt->bind_param("ii", $report_id, $team_id);
            $assignStmt->execute();
            $assignStmt->close();

            // Fetch team name for timeline note
            $teamQ = $conn->prepare("SELECT team_name FROM response_teams WHERE id = ?");
            $teamQ->bind_param("i", $team_id);
            $teamQ->execute();
            $teamName = $teamQ->get_result()->fetch_assoc()['team_name'] ?? "Team #$team_id";
            $teamQ->close();

            // Log team assignment to Case Timeline (team assignment uses its own generated note)
            $logStmt = $conn->prepare("INSERT INTO case_timeline (report_id, status_from, status_to, changed_by, notes) VALUES (?, 'System', 'Team Assigned', ?, ?)");
            $assignNote = "Assigned to " . $teamName;
            $logStmt->bind_param("iis", $report_id, $admin_id, $assignNote);
            $logStmt->execute();
            $logStmt->close();
        }
        // Note: admin's typed notes are NOT bundled with team assignment log —
        // they are saved independently in step 3 below.
    }

    // --- 3. Save Case Notes independently ---
    // Notes are ALWAYS saved as their own timeline entry when provided, UNLESS
    // they were already bundled inside a status-change log entry in step 1.
    //
    // FIX: previously this required `$team_id <= 0`, which silently dropped
    // any note typed while a team was pre-selected in the dropdown. That
    // condition is now removed — notes are independent of team selection.
    if (!empty($notes) && !$notes_logged_with_status) {
        $logStmt = $conn->prepare("INSERT INTO case_timeline (report_id, status_from, status_to, changed_by, notes) VALUES (?, ?, ?, ?, ?)");
        $logStmt->bind_param("issis", $report_id, $db_status, $db_status, $admin_id, $notes);
        $logStmt->execute();
        $logStmt->close();
    }

    // --- System Audit Log ---
    require_once "../../includes/logger.php";
    if ($status_changed) {
        $auditDetails = "Updated Report #$report_id: Status changed from '$db_status' to '$new_status'.";
        if ($team_id > 0 && $new_status !== 'resolved') $auditDetails .= " Team assigned: ID $team_id.";
    } elseif ($team_id > 0) {
        $auditDetails = "Updated Report #$report_id: Team assignment updated. Current status: '$db_status'.";
    } else {
        $auditDetails = "Updated Report #$report_id: Note added. Current status: '$db_status'.";
    }
    logActivity($conn, $_SESSION['user_id'], 'Update Report', $auditDetails);

    $_SESSION['flash_success'] = "Report updated successfully.";
    header("Location: report_details.php?id=" . $report_id);
    exit();

} else {
    header("Location: view_reports.php");
    exit();
}
?>
