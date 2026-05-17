<?php
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized.");
}

echo "<h2>Migrating Database for Guest Reports...</h2>";

// 1. Make user_id nullable
$sql1 = "ALTER TABLE reports MODIFY user_id INT(11) NULL";
if ($conn->query($sql1)) {
    echo "<p>Successfully modified user_id to be nullable.</p>";
} else {
    echo "<p>Error modifying user_id: " . $conn->error . "</p>";
}

// 2. Add guest_name column
$sql2 = "ALTER TABLE reports ADD COLUMN IF NOT EXISTS guest_name VARCHAR(100) NULL AFTER user_id";
if ($conn->query($sql2)) {
    echo "<p>Successfully added guest_name column.</p>";
} else {
    echo "<p>Error adding guest_name: " . $conn->error . "</p>";
}

// 3. Add guest_contact column
$sql3 = "ALTER TABLE reports ADD COLUMN IF NOT EXISTS guest_contact VARCHAR(50) NULL AFTER guest_name";
if ($conn->query($sql3)) {
    echo "<p>Successfully added guest_contact column.</p>";
} else {
    echo "<p>Error adding guest_contact: " . $conn->error . "</p>";
}

echo "<p>Done! <a href='../users/encode_report.php'>Go back</a></p>";
?>
