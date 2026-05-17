<?php
require_once "../../includes/auth_check.php";
require_once "../../config/db.php";

if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit();
}

header('Content-Type: application/json');

$username = trim($_GET['username'] ?? '');

if (empty($username)) {
    echo json_encode(['available' => false, 'message' => '']);
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

echo json_encode([
    'available' => $stmt->num_rows === 0,
    'message'   => $stmt->num_rows === 0 ? 'Username is available' : 'Username is already taken'
]);
