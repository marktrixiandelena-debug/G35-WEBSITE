<?php
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

if ($_SESSION['role'] !== 'resident') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $type = $_POST['type'];
    $location = trim($_POST['location']);
    $location_details = trim($_POST['location_details']);
    $description = trim($_POST['description']);
    $severity = $_POST['severity'];
    $status = 'pending';
    $photo_path = null;

    // Basic Validation
    if (empty($type) || empty($location) || empty($location_details) || empty($description) || empty($severity)) {
        $_SESSION['flash_error'] = 'Please fill in all required fields.';
        header("Location: submit_report.php");
        exit();
    }

    // Handle Photo Upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check format
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                $photo_path = "uploads/" . $file_name;
            } else {
                $_SESSION['flash_warning'] = 'Error uploading photo. Report will be submitted without photo.';
            }
        } else {
            $_SESSION['flash_error'] = 'Invalid file format. Only JPG, PNG, GIF allowed.';
            header("Location: submit_report.php");
            exit();
        }
    }

    // Insert into Database
    // Note: We use PREPARED STATEMENTS for security
    $stmt = $conn->prepare("INSERT INTO reports (user_id, type, location, location_details, description, severity, status, photo_path, report_source) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Online')");
    $stmt->bind_param("isssssss", $user_id, $type, $location, $location_details, $description, $severity, $status, $photo_path);

    if ($stmt->execute()) {
        // Success
        $_SESSION['flash_success'] = 'Report submitted successfully!';
        header("Location: my_reports.php");
        exit();
    } else {
        $_SESSION['flash_error'] = 'Error: ' . $stmt->error;
        header("Location: submit_report.php");
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: submit_report.php");
    exit();
}
?>