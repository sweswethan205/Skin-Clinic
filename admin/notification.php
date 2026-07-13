<?php
session_start();
include_once '../config/db.php';

// ===============================
// MARK AS READ
// ===============================
if (isset($_GET['read_id'])) {
    $id = intval($_GET['read_id']);
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $id");
    header("Location: notification.php");
    exit;
}

// ===============================
// DELETE NOTIFICATION
// ===============================
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM notifications WHERE id = $id");
    header("Location: notification.php");
    exit;
}

// ===============================
// FETCH NOTIFICATIONS
// ===============================
$sql = "SELECT n.*, 
        u.name AS user_name,
        a.treatment_id,
        t.treatment_name
        FROM notifications n
        LEFT JOIN users u ON n.user_id = u.id
        LEFT JOIN appointments a ON n.appointment_id = a.id
        LEFT JOIN treatments t ON a.treatment_id = t.id
        WHERE n.target_role = 'admin'
        ORDER BY n.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            pink: '#FF6584',
                            pinkHover: '#E04F6E',
                            lightPink: '#FFF0F2',
                            dark: '#0F172A',
                            muted: '#64748B',
                            canvas: '#F1F5F9'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .modal-bg {
            background: rgba(15, 23, 42, 0.5);
        }
    </style>
</head>

<body class="bg-slate-50">
    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <div class="flex">



        <!-- MAIN CONTENT -->
        <div class="flex-1 p-4 sm:p-6 lg:ml-64">

            <h1 class="text-2xl font-bold text-slate-800 mb-6">
                Notifications
            </h1>

            <!-- NOTIFICATION LIST -->
            <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">

                <table class="w-full text-sm">
                    <thead class="bg-slate-100 text-slate-600">
                        <tr>
                            <th class="p-3 text-left">Type</th>
                            <th class="p-3 text-left">Message</th>
                            <th class="p-3 text-left">User</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = $result->fetch_assoc()):
                            $type_colors = [
                                'booking' => 'bg-blue-100 text-blue-600',
                                'contact' => 'bg-amber-100 text-amber-600',
                                'review' => 'bg-purple-100 text-purple-600',
                                'status' => 'bg-emerald-100 text-emerald-600',
                            ];
                            $type_color = $type_colors[$row['type']] ?? 'bg-slate-100 text-slate-600';
                        ?>
                            <tr class="border-b hover:bg-slate-50">

                                <td class="p-3">
                                    <span class="px-2 py-1 text-xs font-bold rounded-full <?= $type_color ?>"><?= ucfirst($row['type']) ?></span>
                                </td>

                                <td class="p-3">
                                    <div class="font-medium text-slate-800">
                                        <?= htmlspecialchars($row['title']) ?>
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        <?= htmlspecialchars($row['message']) ?>
                                    </div>
                                </td>

                                <td class="p-3 text-slate-600">
                                    <?= $row['user_name'] ?? 'Guest' ?>
                                </td>

                                <td class="p-3">
                                    <?php if ($row['is_read'] == 0): ?>
                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-600 rounded-full">Unread</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-600 rounded-full">Read</span>
                                    <?php endif; ?>
                                </td>

                                <td class="p-3 text-xs text-slate-500">
                                    <?= date('Y-m-d H:i', strtotime($row['created_at'])) ?>
                                </td>

                                <td class="p-3 text-center space-x-2">

                                    <?php if ($row['is_read'] == 0): ?>
                                        <a href="?read_id=<?= $row['id'] ?>"
                                            class="text-blue-500 hover:underline text-xs">
                                            Mark Read
                                        </a>
                                    <?php endif; ?>

                                    <a href="?delete_id=<?= $row['id'] ?>"
                                        onclick="return confirm('Delete this notification?')"
                                        class="text-red-500 hover:underline text-xs">
                                        Delete
                                    </a>

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>

                </table>

            </div>

        </div>
    </div>

</body>

</html>