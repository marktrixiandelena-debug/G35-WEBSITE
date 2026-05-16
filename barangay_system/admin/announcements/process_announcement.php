<?php
/**
 * Announcement Action Processor (Admin)
 * Manages the CRUD lifecycle for community advisories and general notifications.
 * Ensures data integrity through whitelisting and records all actions to the audit log.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";
require_once "../../includes/logger.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

// Whitelisted values — only these are allowed into the database
$allowed_types    = ['General', 'Advisory', 'System Update'];
$allowed_statuses = ['Active', 'Inactive'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // -----------------------------------------------------------------------
    if ($action === 'create') {
        $title      = trim($_POST['title']   ?? '');
        $type       = $_POST['type']         ?? '';
        $status     = $_POST['status']       ?? '';
        $content    = trim($_POST['content'] ?? '');
        $created_by = $_SESSION['user_id'];

        // --- Validation ---
        if (empty($title)) {
            $_SESSION['flash_error'] = "Title is required.";
            header("Location: manage_announcements.php");
            exit();
        }
        if (empty($content)) {
            $_SESSION['flash_error'] = "Content is required.";
            header("Location: manage_announcements.php");
            exit();
        }
        if (!in_array($type, $allowed_types, true)) {
            $_SESSION['flash_error'] = "Invalid announcement type.";
            header("Location: manage_announcements.php");
            exit();
        }
        if (!in_array($status, $allowed_statuses, true)) {
            $_SESSION['flash_error'] = "Invalid announcement status.";
            header("Location: manage_announcements.php");
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO announcements (title, content, type, status, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $title, $content, $type, $status, $created_by);

        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            logActivity($conn, $_SESSION['user_id'], 'Create Announcement', "Created announcement ID $new_id: \"$title\" (Type: $type, Status: $status)");
            $_SESSION['flash_success'] = "Announcement created successfully.";
            header("Location: manage_announcements.php");
        } else {
            $_SESSION['flash_error'] = "Error creating announcement. Please try again.";
            header("Location: manage_announcements.php");
        }
        $stmt->close();
        exit();

    // -----------------------------------------------------------------------
    } elseif ($action === 'update') {
        $id      = (int)($_POST['id']     ?? 0);
        $title   = trim($_POST['title']   ?? '');
        $type    = $_POST['type']         ?? '';
        $status  = $_POST['status']       ?? '';
        $content = trim($_POST['content'] ?? '');

        // --- Validation ---
        if ($id <= 0) {
            $_SESSION['flash_error'] = "Invalid announcement ID.";
            header("Location: manage_announcements.php");
            exit();
        }
        if (empty($title)) {
            $_SESSION['flash_error'] = "Title is required.";
            header("Location: manage_announcements.php");
            exit();
        }
        if (empty($content)) {
            $_SESSION['flash_error'] = "Content is required.";
            header("Location: manage_announcements.php");
            exit();
        }
        if (!in_array($type, $allowed_types, true)) {
            $_SESSION['flash_error'] = "Invalid announcement type.";
            header("Location: manage_announcements.php");
            exit();
        }
        if (!in_array($status, $allowed_statuses, true)) {
            $_SESSION['flash_error'] = "Invalid announcement status.";
            header("Location: manage_announcements.php");
            exit();
        }

        $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, type = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $content, $type, $status, $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                logActivity($conn, $_SESSION['user_id'], 'Update Announcement', "Updated announcement ID $id: \"$title\" (Type: $type, Status: $status)");
                $_SESSION['flash_success'] = "Announcement updated successfully.";
                header("Location: manage_announcements.php");
            } else {
                // No rows affected — either nothing changed or record no longer exists
                $_SESSION['flash_error'] = "No changes were saved. The announcement may no longer exist.";
                header("Location: manage_announcements.php");
            }
        } else {
            $_SESSION['flash_error'] = "Error updating announcement. Please try again.";
            header("Location: manage_announcements.php");
        }
        $stmt->close();
        exit();

    // -----------------------------------------------------------------------
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['flash_error'] = "Invalid announcement ID.";
            header("Location: manage_announcements.php");
            exit();
        }

        // Fetch title before deletion so the audit log is descriptive
        $titleQ = $conn->prepare("SELECT title FROM announcements WHERE id = ?");
        $titleQ->bind_param("i", $id);
        $titleQ->execute();
        $titleRow = $titleQ->get_result()->fetch_assoc();
        $titleQ->close();

        if (!$titleRow) {
            $_SESSION['flash_error'] = "Announcement not found or already deleted.";
            header("Location: manage_announcements.php");
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            logActivity($conn, $_SESSION['user_id'], 'Delete Announcement', "Deleted announcement ID $id: \"" . $titleRow['title'] . "\"");
            $_SESSION['flash_success'] = "Announcement deleted successfully.";
            header("Location: manage_announcements.php");
        } else {
            $_SESSION['flash_error'] = "Error deleting announcement. Please try again.";
            header("Location: manage_announcements.php");
        }
        $stmt->close();
        exit();
    }

    // Unknown action
    $_SESSION['flash_error'] = "Invalid action.";
    header("Location: manage_announcements.php");
    exit();

} else {
    header("Location: manage_announcements.php");
    exit();
}
?>
