<?php
require_once "../../config/db.php";

$results = [];

// ── Step 1: Add missing columns ────────────────────────────────────────────
$columns = [
    'guest_name'    => "ALTER TABLE reports ADD COLUMN guest_name VARCHAR(150) DEFAULT NULL AFTER user_id",
    'guest_contact' => "ALTER TABLE reports ADD COLUMN guest_contact VARCHAR(50) DEFAULT NULL AFTER guest_name",
    'report_source' => "ALTER TABLE reports ADD COLUMN report_source VARCHAR(50) NOT NULL DEFAULT 'Walk-In' AFTER status",
    'encoded_by'    => "ALTER TABLE reports ADD COLUMN encoded_by INT(11) DEFAULT NULL AFTER report_source",
];

foreach ($columns as $col => $sql) {
    $check = $conn->query("SHOW COLUMNS FROM reports LIKE '$col'");
    if ($check->num_rows === 0) {
        if ($conn->query($sql)) {
            $results[] = "✅ Added column: <strong>$col</strong>";
        } else {
            $results[] = "❌ Error adding <strong>$col</strong>: " . $conn->error;
        }
    } else {
        $results[] = "⚠️ Already exists: <strong>$col</strong> (skipped)";
    }
}

// ── Step 2: Make user_id nullable so guest reports can have NULL ────────────
$col_check = $conn->query("SHOW COLUMNS FROM reports LIKE 'user_id'");
$col_info  = $col_check->fetch_assoc();

if ($col_info && $col_info['Null'] === 'NO') {
    // Drop the old FK first (it won't allow NULL with ON DELETE CASCADE)
    $fk_check = $conn->query("
        SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'reports'
          AND COLUMN_NAME = 'user_id'
          AND REFERENCED_TABLE_NAME = 'users'
        LIMIT 1
    ");
    $fk = $fk_check->fetch_assoc();

    if ($fk) {
        $fk_name = $fk['CONSTRAINT_NAME'];
        if ($conn->query("ALTER TABLE reports DROP FOREIGN KEY `$fk_name`")) {
            $results[] = "✅ Dropped old FK: <strong>$fk_name</strong>";
        } else {
            $results[] = "❌ Could not drop FK <strong>$fk_name</strong>: " . $conn->error;
        }
    }

    // Make user_id nullable
    if ($conn->query("ALTER TABLE reports MODIFY COLUMN user_id INT(11) DEFAULT NULL")) {
        $results[] = "✅ <strong>user_id</strong> is now nullable (guest reports allowed)";
    } else {
        $results[] = "❌ Could not make user_id nullable: " . $conn->error;
    }

    // Re-add FK with SET NULL so deleting a user orphans the report rather than deletes it
    if ($conn->query("ALTER TABLE reports ADD CONSTRAINT reports_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL")) {
        $results[] = "✅ Re-added FK with <strong>ON DELETE SET NULL</strong>";
    } else {
        $results[] = "❌ Could not re-add FK: " . $conn->error;
    }
} else {
    $results[] = "⚠️ <strong>user_id</strong> already nullable (skipped)";
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Migration: Encode Report Fix</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 3rem auto; padding: 2rem; background: #f4f6fa; }
        h2 { color: #1e3a5f; }
        ul { list-style: none; padding: 0; }
        li { padding: 0.5rem 1rem; margin: 0.4rem 0; background: #fff; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        a { display:inline-block; margin-top:1.5rem; padding:.6rem 1.4rem; background:#1e3a5f; color:#fff; border-radius:6px; text-decoration:none; }
    </style>
</head>
<body>
    <h2>Migration: Encode Report Fix</h2>
    <ul>
        <?php foreach ($results as $r): ?>
            <li><?php echo $r; ?></li>
        <?php endforeach; ?>
    </ul>
    <p><strong>Migration complete.</strong></p>
    <a href="../users/encode_report.php">← Back to Encode Report</a>
</body>
</html>
