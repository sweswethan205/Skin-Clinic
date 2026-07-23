<?php
session_start();
include_once '../config/db.php';

$admin = $conn->query("SELECT username, photo FROM admins ORDER BY id ASC LIMIT 1")->fetch_assoc();
$admin_photo = $admin['photo'] ?? '';
$admin_username = $admin['username'] ?? 'Admin';

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
// MARK ALL AS READ
// ===============================
if (isset($_GET['action']) && $_GET['action'] === 'mark_all_read') {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE target_role = 'admin' AND is_read = 0");
    header("Location: notification.php?msg=" . urlencode("All notifications marked as read.") . "&type=success");
    exit;
}

// ===============================
// CLEAR ALL NOTIFICATIONS
// ===============================
if (isset($_GET['action']) && $_GET['action'] === 'clear_all') {
    $conn->query("DELETE FROM notifications WHERE target_role = 'admin'");
    header("Location: notification.php?msg=" . urlencode("All notifications cleared.") . "&type=success");
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

// Read flash message
$flash_msg = $_GET['msg'] ?? '';
$flash_type = $_GET['type'] ?? '';

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

$count_sql = "SELECT COUNT(*) AS cnt FROM notifications n WHERE n.target_role = 'admin'";
$count_result = $conn->query($count_sql);
$total_items = (int) $count_result->fetch_assoc()['cnt'];
$per_page = 10;
$total_pages = (int) max(1, ceil($total_items / $per_page));
$current_page = isset($_GET['page']) ? (int) max(1, min((int) $_GET['page'], $total_pages)) : 1;
$offset = ($current_page - 1) * $per_page;

$sql .= " LIMIT $per_page OFFSET $offset";
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
            darkMode: 'class',
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
    <script>
        (function() {
            const saved = localStorage.getItem('admin_theme');
            if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
            updateIcons();
        })();

        function updateIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            const moon = document.getElementById('admin-icon-moon');
            const sun = document.getElementById('admin-icon-sun');
            if (moon) moon.style.display = isDark ? 'none' : 'inline';
            if (sun) sun.style.display = isDark ? 'inline' : 'none';
        }

        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('admin_theme', html.classList.contains('dark') ? 'dark' : 'light');
            updateIcons();
        }
    </script>
</head>

<body class="bg-slate-50 dark:bg-gray-950 dark:text-gray-100">
    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <div class="flex">



        <!-- MAIN CONTENT -->
        <div class="flex-1 lg:ml-64">

            <header class="h-16 sm:h-20 bg-white dark:bg-gray-900 border-b border-slate-200/60 dark:border-gray-800 flex items-center justify-between px-4 sm:px-8 shrink-0 z-10 sticky top-0">
                <div class="flex items-center space-x-4">
                    <div>
                        <h2 class="text-xl font-extrabold text-brand-dark dark:text-white tracking-tight">Notifications</h2>
                        <p class="text-xs text-brand-muted dark:text-gray-400 font-medium">View system alerts and updates</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php include 'header-actions.php'; ?>
                    <a href="profile.php" class="flex items-center space-x-3 border-l pl-6 border-slate-200 dark:border-gray-700 hover:opacity-80 transition">
                        <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 dark:border-gray-700 bg-brand-lightPink flex items-center justify-center text-brand-pink font-bold text-sm">
                            <?php if ($admin_photo): ?>
                                <img src="../<?= htmlspecialchars($admin_photo) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?= strtoupper(substr($admin_username, 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <span class="text-xs font-bold text-brand-dark dark:text-white block"><?= htmlspecialchars($admin_username) ?></span>
                    </a>
                </div>
            </header>

            <div class="p-4 sm:p-6 lg:p-8">

                <?php if ($flash_msg): ?>
                <div class="mb-4 px-5 py-3 rounded-xl border text-sm font-bold flex items-center gap-3 <?= $flash_type === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800'; ?>">
                    <i class="fa-solid <?= $flash_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                    <span><?= htmlspecialchars($flash_msg) ?></span>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-current opacity-60 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <?php endif; ?>

                <!-- Toolbar -->
                <?php if ($total_items > 0): ?>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                    <span class="text-sm font-bold text-brand-dark dark:text-white px-2"><?= $total_items ?> notification<?= $total_items !== 1 ? 's' : '' ?></span>
                    <div class="flex items-center gap-2">
                        <a href="?action=mark_all_read"
                           onclick="return confirm('Mark all notifications as read?')"
                           class="px-4 py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 text-blue-600 dark:text-blue-400 text-xs font-bold rounded-xl transition-all border border-blue-200 dark:border-blue-800 flex items-center gap-2 shrink-0">
                            <i class="fa-solid fa-check-double text-[10px]"></i> Mark All as Read
                        </a>
                        <a href="?action=clear_all"
                           onclick="return confirm('Delete ALL notifications? This cannot be undone.')"
                           class="px-4 py-2 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold rounded-xl transition-all border border-red-200 dark:border-red-800 flex items-center gap-2 shrink-0">
                            <i class="fa-solid fa-trash-can text-[10px]"></i> Clear All
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- NOTIFICATION LIST -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow border border-slate-100 dark:border-gray-800 overflow-hidden">

                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 dark:bg-gray-950 text-slate-600 dark:text-gray-300">
                            <tr>
                                <th class="p-3 text-left">#</th>
                                <th class="p-3 text-left">Type</th>
                                <th class="p-3 text-left">Message</th>
                                <th class="p-3 text-left">User</th>
                                <th class="p-3 text-left">Status</th>
                                <th class="p-3 text-left">Date</th>
                                <th class="p-3 text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $i = $offset + 1; while ($row = $result->fetch_assoc()):
                                $type_colors = [
                                    'booking' => 'bg-blue-100 text-blue-600',
                                    'contact' => 'bg-amber-100 text-amber-600',
                                    'review' => 'bg-purple-100 text-purple-600',
                                    'status' => 'bg-emerald-100 text-emerald-600',
                                ];
                                $type_color = $type_colors[$row['type']] ?? 'bg-slate-100 text-slate-600 dark:text-gray-300';
                            ?>
                                <tr class="border-b dark:border-gray-800 hover:bg-slate-50 dark:hover:bg-gray-800">

                                    <td class="p-3 text-slate-500 dark:text-gray-400"><?= $i++ ?></td>

                                    <td class="p-3">
                                        <span class="px-2 py-1 text-xs font-bold rounded-full <?= $type_color ?>"><?= ucfirst($row['type']) ?></span>
                                    </td>

                                    <td class="p-3">
                                        <div class="font-medium text-slate-800 dark:text-white">
                                            <?= htmlspecialchars($row['title']) ?>
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-gray-400">
                                            <?= htmlspecialchars($row['message']) ?>
                                        </div>
                                    </td>

                                    <td class="p-3 text-slate-600 dark:text-gray-300">
                                        <?= $row['user_name'] ?? 'Guest' ?>
                                    </td>

                                    <td class="p-3">
                                        <?php if ($row['is_read'] == 0): ?>
                                            <span class="px-2 py-1 text-xs bg-red-100 text-red-600 rounded-full">Unread</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-600 rounded-full">Read</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="p-3 text-xs text-slate-500 dark:text-gray-400">
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

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="mt-4 flex items-center justify-center gap-1 text-xs font-semibold">
                        <?php if ((int)$current_page > 1): ?>
                            <a href="?page=<?= (int)$current_page - 1 ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 hover:bg-brand-pink hover:text-white hover:border-brand-pink transition font-bold">&laquo;</a>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <?php $is_active = ((int)$p === (int)$current_page); ?>
                            <a href="?page=<?= $p ?>"
                                class="px-3 py-1.5 rounded-lg border font-bold transition <?= $is_active ? 'bg-brand-pink text-white border-brand-pink' : 'bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 hover:bg-brand-pink hover:text-white hover:border-brand-pink' ?>">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ((int)$current_page < (int)$total_pages): ?>
                            <a href="?page=<?= (int)$current_page + 1 ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 hover:bg-brand-pink hover:text-white hover:border-brand-pink transition font-bold">&raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

</body>

</html>