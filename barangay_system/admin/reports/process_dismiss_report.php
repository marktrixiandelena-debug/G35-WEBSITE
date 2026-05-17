<?php
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $report_id         = (int)($_POST['report_id'] ?? 0);
    $reason_type       = trim($_POST['reason_type'] ?? '');
    $additional_reason = trim($_POST['additional_reason'] ?? '');
    $admin_id          = $_SESSION['user_id'];

    // Carry returnto param for workflow state restoration
    $rawRt   = preg_replace('/[^a-zA-Z0-9=&_%+.-]/', '', $_POST['returnto'] ?? '');
    $rtParam = $rawRt ? '&returnto=' . urlencode($rawRt) : '';

    if ($report_id <= 0) {
        header("Location: view_reports.php");
        exit();
    }

    // --- Server-side status guard: only pending reports can be dismissed ---
    $statusCheck = $conn->prepare("SELECT status, type FROM reports WHERE id = ?");
    $statusCheck->bind_param("i", $report_id);
    $statusCheck->execute();
    $currentReport = $statusCheck->get_result()->fetch_assoc();
    $statusCheck->close();

    if (!$currentReport) {
        header("Location: view_reports.php?error=" . urlencode("Report not found."));
        exit();
    }

    if ($currentReport['status'] !== 'pending') {
        header("Location: report_details.php?id=$report_id&error=" . urlencode("Only pending reports can be dismissed.") . $rtParam);
        exit();
    }

    // --- Require a reason type ---
    if (empty($reason_type)) {
        header("Location: report_details.php?id=$report_id&error=" . urlencode("A dismissal reason is required.") . $rtParam);
        exit();
    }

    // Combine reason type and optional detail note
    $full_reason = $reason_type;
    if (!empty($additional_reason)) {
        $full_reason .= "\n\nDetails: " . $additional_reason;
    }

    $new_status     = 'dismissed';
    $current_status = 'pending';

    // Update report — double-keyed WHERE guards against race conditions
    $stmt = $conn->prepare("UPDATE reports SET status = ?, dismissal_reason = ? WHERE id = ? AND status = 'pending'");
    $stmt->bind_param("ssi", $new_status, $full_reason, $report_id);
    $stmt->execute();
    $stmt->close();

    // Log to Case Timeline
    $logStmt = $conn->prepare("INSERT INTO case_timeline (report_id, status_from, status_to, changed_by, notes) VALUES (?, ?, ?, ?, ?)");
    $logDetails = "Report Dismissed. Reason: " . $reason_type;
    $logStmt->bind_param("issis", $report_id, $current_status, $new_status, $admin_id, $logDetails);
    $logStmt->execute();
    $logStmt->close();

    // Log to System Audit
    require_once "../../includes/logger.php";
    $reportType   = ucfirst($currentReport['type'] ?? 'report');
    $auditDetails = "Dismissed $reportType Report #$report_id. Reason: $reason_type.";
    logActivity($conn, $_SESSION['user_id'], 'Dismiss Report', $auditDetails);

    $_SESSION['flash_success'] = "Report dismissed successfully.";
    header("Location: report_details.php?id=" . $report_id . $rtParam);
    exit();

} else {
    header("Location: view_reports.php");
    exit();
}
?>
