<?php
/**
 * Response Team Action Processor (Admin)
 * Manages the CRUD operations for emergency and maintenance response units.
 * Performs validation for unique naming and records configuration changes to the audit log.
 */
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $action = $_POST['action'];

    if ($action == 'create') {
        $team_name      = trim($_POST['team_name']);
        $team_leader    = trim($_POST['team_leader']);
        $contact_number = trim($_POST['contact_number']);
        $status         = $_POST['status'];

        // Only allow valid stored statuses
        $allowed_statuses = ['Active', 'Inactive'];
        if (!in_array($status, $allowed_statuses)) {
            $status = 'Active';
        }

        if (empty($team_name) || empty($team_leader) || empty($contact_number)) {
            $_SESSION['flash_error'] = "All fields are required.";
            header("Location: manage_teams.php");
            exit();
        }

        // Duplicate name check
        $dupCheck = $conn->prepare("SELECT id FROM response_teams WHERE team_name = ?");
        $dupCheck->bind_param("s", $team_name);
        $dupCheck->execute();
        $dupCheck->store_result();
        if ($dupCheck->num_rows > 0) {
            $_SESSION['flash_error'] = "A team with that name already exists.";
            header("Location: manage_teams.php");
            exit();
        }
        $dupCheck->close();

        $stmt = $conn->prepare("INSERT INTO response_teams (team_name, team_leader, contact_number, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $team_name, $team_leader, $contact_number, $status);

        if ($stmt->execute()) {
            require_once "../../includes/logger.php";
            logActivity($conn, $_SESSION['user_id'], 'Create Team', "Created response team: $team_name");
            $_SESSION['flash_success'] = "Team created successfully.";
            header("Location: manage_teams.php");
        } else {
            $_SESSION['flash_error'] = "Error creating team: " . $conn->error;
            header("Location: manage_teams.php");
        }
        $stmt->close();
        exit();

    } elseif ($action == 'update') {
        $team_id        = (int)$_POST['team_id'];
        $team_name      = trim($_POST['team_name']);
        $team_leader    = trim($_POST['team_leader']);
        $contact_number = trim($_POST['contact_number']);
        $status         = $_POST['status'];

        // Only allow valid stored statuses
        $allowed_statuses = ['Active', 'Inactive'];
        if (!in_array($status, $allowed_statuses)) {
            $status = 'Active';
        }

        if (empty($team_name) || empty($team_leader) || empty($contact_number)) {
            $_SESSION['flash_error'] = "All fields are required.";
            header("Location: manage_teams.php");
            exit();
        }

        // Duplicate name check (exclude current team)
        $dupCheck = $conn->prepare("SELECT id FROM response_teams WHERE team_name = ? AND id != ?");
        $dupCheck->bind_param("si", $team_name, $team_id);
        $dupCheck->execute();
        $dupCheck->store_result();
        if ($dupCheck->num_rows > 0) {
            $_SESSION['flash_error'] = "Another team with that name already exists.";
            header("Location: manage_teams.php");
            exit();
        }
        $dupCheck->close();

        $stmt = $conn->prepare("UPDATE response_teams SET team_name = ?, team_leader = ?, contact_number = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $team_name, $team_leader, $contact_number, $status, $team_id);

        if ($stmt->execute()) {
            require_once "../../includes/logger.php";
            logActivity($conn, $_SESSION['user_id'], 'Update Team', "Updated response team ID $team_id: $team_name");
            $_SESSION['flash_success'] = "Team updated successfully.";
            header("Location: manage_teams.php");
        } else {
            $_SESSION['flash_error'] = "Error updating team: " . $conn->error;
            header("Location: manage_teams.php");
        }
        $stmt->close();
        exit();
    }

    $conn->close();

} else {
    header("Location: manage_teams.php");
    exit();
}
?>
