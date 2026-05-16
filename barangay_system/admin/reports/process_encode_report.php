<?php
/**
 * Manual Report Processing (Admin)
 * Validates and persists incident reports filed by administrative staff.
 * Correctly routes data between registered user IDs and guest identity strings.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../users/encode_report.php");
    exit();
}

$admin_id      = $_SESSION['user_id'];
$reporter_type = $_POST['reporter_type'] ?? 'registered';
$resident_id   = ($reporter_type === 'registered') ? (int) ($_POST['resident_id'] ?? 0) : null;
$guest_name    = ($reporter_type === 'guest') ? trim($_POST['guest_name'] ?? '') : null;
$guest_contact = ($reporter_type === 'guest') ? trim($_POST['guest_contact'] ?? '') : null;

$type          = trim($_POST['type'] ?? '');
$location      = trim($_POST['location'] ?? '');
$location_details = trim($_POST['location_details'] ?? '');
$description   = trim($_POST['description'] ?? '');
$severity      = $_POST['severity'] ?? 'Low';
$report_source = $_POST['report_source'] ?? 'Walk-In';
$original_time = trim($_POST['original_time'] ?? '');
$status        = 'pending';
$photo_path    = null;

// ---- Validation ----
if (empty($type) || empty($location) || empty($location_details) || empty($description)) {
    $_SESSION['flash_error'] = "Please fill in all required incident details.";
    header("Location: ../users/encode_report.php");
    exit();
}

if ($reporter_type === 'registered' && !$resident_id) {
    $_SESSION['flash_error'] = "Please select a resident.";
    header("Location: ../users/encode_report.php");
    exit();
}

if ($reporter_type === 'guest' && empty($guest_name)) {
    $_SESSION['flash_error'] = "Please provide the guest name.";
    header("Location: ../users/encode_report.php");
    exit();
}

$allowed_sources = ['Walk-In', 'Phone Call', 'Offline Batch'];
if (!in_array($report_source, $allowed_sources)) {
    $report_source = 'Walk-In';
}

$resident_name = "Guest Walk-in";
if ($reporter_type === 'registered') {
    // Verify resident exists
    $chk = $conn->prepare("SELECT id, full_name FROM users WHERE id = ? AND role = 'resident' AND status = 'active'");
    $chk->bind_param("i", $resident_id);
    $chk->execute();
    $resident = $chk->get_result()->fetch_assoc();
    if (!$resident) {
        $_SESSION['flash_error'] = "Selected resident not found or inactive.";
        header("Location: ../users/encode_report.php");
        exit();
    }
    $resident_name = $resident['full_name'];
} elseif ($reporter_type === 'guest') {
    $resident_name = "Guest: " . $guest_name;
}

// ---- Photo Upload ----
if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $target_dir = "../../resident/uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($ext, $allowed_types)) {
        $safe_name = time() . "_encoded_" . uniqid() . "." . $ext;
        $target_file = $target_dir . $safe_name;
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $photo_path = "uploads/" . $safe_name;
        }
    } else {
        $_SESSION['flash_error'] = "Invalid photo format. Only JPG, PNG, GIF allowed.";
        header("Location: ../users/encode_report.php");
        exit();
    }
}

// ---- Determine timestamp ----
$use_custom_time = ($report_source === 'Offline Batch' && !empty($original_time));

// ---- Pre-flight: check that the migration has been run ----
$col_check = $conn->query("SHOW COLUMNS FROM reports LIKE 'report_source'");
if (!$col_check || $col_check->num_rows === 0) {
    $_SESSION['flash_error'] = "Database not ready. Please run the migration first: admin/migrate_report_source.php";
    header("Location: ../users/encode_report.php");
    exit();
}

// ---- Insert Report ----
if ($use_custom_time) {
    $created_at = date('Y-m-d H:i:s', strtotime($original_time));
    $stmt = $conn->prepare("INSERT INTO reports (user_id, guest_name, guest_contact, type, location, location_details, description, severity, status, photo_path, report_source, encoded_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['flash_error'] = "DB prepare failed: " . $conn->error;
        header("Location: ../users/encode_report.php");
        exit();
    }
    $stmt->bind_param("issssssssssiss", $resident_id, $guest_name, $guest_contact, $type, $location, $location_details, $description, $severity, $status, $photo_path, $report_source, $admin_id, $created_at, $created_at);
} else {
    $stmt = $conn->prepare("INSERT INTO reports (user_id, guest_name, guest_contact, type, location, location_details, description, severity, status, photo_path, report_source, encoded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['flash_error'] = "DB prepare failed: " . $conn->error;
        header("Location: ../users/encode_report.php");
        exit();
    }
    $stmt->bind_param("issssssssssi", $resident_id, $guest_name, $guest_contact, $type, $location, $location_details, $description, $severity, $status, $photo_path, $report_source, $admin_id);
}

if (!$stmt->execute()) {
    $_SESSION['flash_error'] = "Database error: " . $stmt->error;
    header("Location: ../users/encode_report.php");
    exit();
}

$new_report_id = $conn->insert_id;
$stmt->close();

// ---- Audit Log ----
$source_label = $report_source;
$details = "Manually encoded report #{$new_report_id} for resident: {$resident_name} | Source: {$source_label} | Type: {$type} | Location: {$location}";

$audit = $conn->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'Manual Report Encoded', ?)");
$audit->bind_param("is", $admin_id, $details);
$audit->execute();
$audit->close();

// ---- Add initial case timeline entry ----
$init_note = "Report manually encoded by admin via {$source_label}.";
$tl = $conn->prepare("INSERT INTO case_timeline (report_id, status_from, status_to, changed_by, notes) VALUES (?, NULL, 'pending', ?, ?)");
$tl->bind_param("iis", $new_report_id, $admin_id, $init_note);
$tl->execute();
$tl->close();

$conn->close();

$_SESSION['flash_success'] = "Report successfully encoded and saved. It is now in the system as Pending.";
header("Location: report_details.php?id={$new_report_id}");
exit();
?>
