<?php
include_once '../config/db.php';

echo "<pre>";

// Find actual FK name for appointment_id
$fk = $conn->query("
    SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications'
    AND COLUMN_NAME = 'appointment_id' AND REFERENCED_TABLE_NAME IS NOT NULL
");
$fk_name = null;
if ($fk && $row = $fk->fetch_assoc()) {
    $fk_name = $row['CONSTRAINT_NAME'];
}

$queries = [];

// Drop FK if exists
if ($fk_name) {
    $queries[] = "ALTER TABLE notifications DROP FOREIGN KEY `$fk_name`";
}

$queries[] = "ALTER TABLE notifications MODIFY COLUMN appointment_id INT NULL";

if ($fk_name) {
    $queries[] = "ALTER TABLE notifications ADD CONSTRAINT `$fk_name` FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL";
}

foreach ($queries as $sql) {
    if ($conn->query($sql)) {
        echo "OK: $sql\n";
    } else {
        echo "ERROR: " . $conn->error . "\n";
    }
}

echo "</pre>";
$conn->close();
