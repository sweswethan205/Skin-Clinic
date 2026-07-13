<?php
session_start();
include_once '../config/db.php';

$message = '';
$error = '';

if (isset($_POST['run_migration'])) {
    // Check if column already exists
    $check = $conn->query("SHOW COLUMNS FROM notifications LIKE 'target_role'");
    if ($check && $check->num_rows > 0) {
        $message = 'Column `target_role` already exists. No migration needed.';
    } else {
        $sql = "ALTER TABLE notifications 
                ADD COLUMN target_role VARCHAR(10) NOT NULL DEFAULT 'user' 
                AFTER type";
        if ($conn->query($sql)) {
            $message = 'Migration successful: `target_role` column added.';

            // Update existing notifications with appropriate target_role
            $conn->query("UPDATE notifications SET target_role = 'admin' WHERE type IN ('booking', 'review', 'contact')");
            $conn->query("UPDATE notifications SET target_role = 'user' WHERE type = 'status'");
            $message .= ' Existing notifications updated with correct target_role.';
        } else {
            $error = 'Migration failed: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Migration - Target Role</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 p-8">
    <div class="max-w-lg mx-auto bg-white rounded-2xl shadow p-6">
        <h1 class="text-xl font-bold text-slate-800 mb-4">Notification Target Role Migration</h1>
        <?php if ($message): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <button type="submit" name="run_migration" class="bg-brand-pink text-white px-4 py-2 rounded-lg hover:bg-brand-pinkHover transition">
                Run Migration
            </button>
        </form>
        <p class="text-xs text-slate-500 mt-4">Adds <code>target_role</code> column to notifications table and updates existing rows.</p>
    </div>
</body>
</html>
