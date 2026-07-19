<?php
require_once __DIR__ . '/../config/db.php';

$admin = $conn->query("SELECT username, photo FROM admins ORDER BY id ASC LIMIT 1")->fetch_assoc();
$admin_photo = $admin['photo'] ?? '';
$admin_username = $admin['username'] ?? 'Admin';

$message = '';
$message_type = '';

// Handle Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $room_name = trim($_POST['room_name']);
    $room_number = trim($_POST['room_number']);
    $description = trim($_POST['description']);
    $capacity = max(1, intval($_POST['capacity']));
    $status = $_POST['status'];

    if ($_POST['action'] === 'create') {
        $stmt = $conn->prepare("INSERT INTO rooms (room_name, room_number, description, capacity, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $room_name, $room_number, $description, $capacity, $status);
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            $stmt->close();

            if (!empty($_POST['treatments']) && is_array($_POST['treatments'])) {
                $tr_stmt = $conn->prepare("INSERT INTO treatment_rooms (treatment_id, room_id) VALUES (?, ?)");
                foreach ($_POST['treatments'] as $tid) {
                    $tid = intval($tid);
                    if ($tid > 0) {
                        $tr_stmt->bind_param("ii", $tid, $new_id);
                        $tr_stmt->execute();
                    }
                }
                $tr_stmt->close();
            }

            $message = "Room added successfully!";
            $message_type = "success";
        } else {
            $message = $conn->errno == 1062 ? "This room number already exists." : "Error adding room: " . $conn->error;
            $message_type = "error";
        }
    } elseif ($_POST['action'] === 'update' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE rooms SET room_name=?, room_number=?, description=?, capacity=?, status=? WHERE id=?");
        $stmt->bind_param("sssisi", $room_name, $room_number, $description, $capacity, $status, $id);
        if ($stmt->execute()) {
            $stmt->close();

            $tr_del = $conn->prepare("DELETE FROM treatment_rooms WHERE room_id = ?");
            $tr_del->bind_param("i", $id);
            $tr_del->execute();
            $tr_del->close();

            if (!empty($_POST['treatments']) && is_array($_POST['treatments'])) {
                $tr_stmt = $conn->prepare("INSERT INTO treatment_rooms (treatment_id, room_id) VALUES (?, ?)");
                foreach ($_POST['treatments'] as $tid) {
                    $tid = intval($tid);
                    if ($tid > 0) {
                        $tr_stmt->bind_param("ii", $tid, $id);
                        $tr_stmt->execute();
                    }
                }
                $tr_stmt->close();
            }

            $message = "Room updated successfully!";
            $message_type = "success";
        } else {
            $message = $conn->errno == 1062 ? "This room number already exists." : "Error updating room: " . $conn->error;
            $message_type = "error";
        }
    }
    header("Location: room.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM treatment_rooms WHERE room_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Room deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting room: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
    header("Location: room.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Read message from redirect
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $message_type = $_GET['type'] ?? 'success';
}

// Fetch all treatments
$treatments_result = $conn->query("SELECT id, treatment_name FROM treatments ORDER BY treatment_name ASC");
$treatments = [];
while ($trow = $treatments_result->fetch_assoc()) {
    $treatments[] = $trow;
}

// Fetch all rooms
$rooms_result = $conn->query("SELECT * FROM rooms ORDER BY room_number ASC");
$rooms = [];
while ($row = $rooms_result->fetch_assoc()) {
    $rooms[] = $row;
}

// Fetch treatments for each room
$tr_stmt = $conn->prepare("SELECT treatment_id FROM treatment_rooms WHERE room_id = ?");
foreach ($rooms as &$rm) {
    $tr_stmt->bind_param("i", $rm['id']);
    $tr_stmt->execute();
    $tr_result = $tr_stmt->get_result();
    $rm['assigned_treatments'] = [];
    while ($tr_row = $tr_result->fetch_assoc()) {
        $rm['assigned_treatments'][] = $tr_row['treatment_id'];
    }
}
$tr_stmt->close();
unset($rm);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Rooms Management</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .modal-bg { background: rgba(15, 23, 42, 0.5); }
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
<body class="bg-brand-canvas dark:bg-gray-950 text-slate-700 dark:text-gray-100 min-h-screen flex antialiased">

    <?php include 'sidebar.php'; ?>

    <div class="flex-grow flex flex-col min-w-0 lg:ml-64">

        <header class="h-16 sm:h-20 bg-white dark:bg-gray-900 border-b border-slate-200/60 dark:border-gray-800 flex items-center justify-between px-4 sm:px-8 shrink-0 z-10 sticky top-0">
            <div class="flex items-center space-x-4">
                <div>
                    <h2 class="text-xl font-extrabold text-brand-dark dark:text-white tracking-tight">Rooms Management</h2>
                    <p class="text-xs text-brand-muted dark:text-gray-400 font-medium">Manage treatment rooms and assign which treatments each room supports.</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <?php include 'header-actions.php'; ?>
                <a href="profile.php" class="flex items-center space-x-3 hover:opacity-80 transition">
                    <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 dark:border-gray-700 bg-brand-lightPink flex items-center justify-center text-brand-pink font-bold text-sm">
                        <?php if ($admin_photo): ?>
                            <img src="../<?php echo htmlspecialchars($admin_photo); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-brand-dark dark:text-white block leading-tight"><?php echo htmlspecialchars($admin_username); ?></span>
                    </div>
                </a>
            </div>
        </header>

        <main class="flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6">

            <?php if ($message): ?>
            <div class="px-5 py-3.5 rounded-xl border text-sm font-bold flex items-center gap-3 <?php echo $message_type === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800'; ?>">
                <i class="fa-solid <?php echo $message_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-current opacity-60 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-pink-50 dark:bg-pink-900/20 text-brand-pink rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-door-open"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Total Rooms</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white"><?php echo count($rooms); ?></span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-circle-check"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Active</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white">
                            <?php echo count(array_filter($rooms, fn($r) => $r['status'] === 'active')); ?>
                        </span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-amber-50 dark:bg-amber-900/20 text-amber-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-wrench"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Maintenance</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white">
                            <?php echo count(array_filter($rooms, fn($r) => $r['status'] === 'maintenance')); ?>
                        </span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-slate-50 dark:bg-gray-800 text-slate-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-circle-pause"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Inactive</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white">
                            <?php echo count(array_filter($rooms, fn($r) => $r['status'] === 'inactive')); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-sm font-bold text-brand-dark dark:text-white px-2">
                    Clinic Treatment Rooms
                </div>
                <button onclick="openCreateModal()" class="px-4 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)] flex items-center justify-center gap-2 shrink-0">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add New Room
                </button>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-slate-200/60 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-gray-950 border-b border-slate-200/50 dark:border-gray-800 text-[11px] font-bold uppercase tracking-wider text-brand-muted dark:text-gray-400">
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Room</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Number</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Description</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-center">Capacity</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Treatments</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-center">Status</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800 text-xs font-semibold text-brand-dark dark:text-gray-300">
                            <?php if (empty($rooms)): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center">
                                    <div class="text-brand-muted dark:text-gray-400">
                                        <i class="fa-solid fa-door-closed text-3xl mb-3 block"></i>
                                        <span class="font-bold text-sm">No rooms found</span>
                                        <p class="text-[11px] font-medium mt-1">Add a new room to get started.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($rooms as $room): ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-gray-800/60 transition-colors group">
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-brand-lightPink dark:bg-pink-900/20 rounded-xl flex items-center justify-center text-brand-pink text-xs font-bold">
                                            <i class="fa-solid fa-door-open"></i>
                                        </div>
                                        <div>
                                            <span class="font-bold text-brand-dark dark:text-white block group-hover:text-brand-pink transition-colors"><?php echo htmlspecialchars($room['room_name']); ?></span>
                                            <span class="text-[10px] text-brand-muted dark:text-gray-400 block font-medium">ID: #RM-<?php echo str_pad($room['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <span class="font-mono text-sm font-bold text-slate-600 dark:text-gray-300"><?php echo htmlspecialchars($room['room_number']); ?></span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 max-w-xs">
                                    <span class="text-slate-500 dark:text-gray-400 line-clamp-2 block">
                                        <?php echo !empty($room['description']) ? htmlspecialchars(substr($room['description'], 0, 80)) . (strlen($room['description']) > 80 ? '...' : '') : '—'; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-center">
                                    <span class="px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                        <i class="fa-solid fa-user"></i> <?php echo $room['capacity']; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <div class="flex flex-wrap gap-1">
                                        <?php if (!empty($room['assigned_treatments'])): ?>
                                            <?php foreach ($room['assigned_treatments'] as $atid): ?>
                                                <?php
                                                $tname = '';
                                                foreach ($treatments as $t) {
                                                    if ($t['id'] == $atid) { $tname = $t['treatment_name']; break; }
                                                }
                                                ?>
                                                <?php if ($tname): ?>
                                                <span class="px-1.5 py-0.5 bg-pink-50 text-brand-pink border border-pink-100 rounded text-[9px] font-bold"><?php echo htmlspecialchars($tname); ?></span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400 dark:text-gray-500 text-[10px]">No treatments assigned</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-center">
                                    <?php if ($room['status'] === 'active'): ?>
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                        <i class="fa-solid fa-circle text-[7px]"></i> Active
                                    </span>
                                    <?php elseif ($room['status'] === 'maintenance'): ?>
                                    <span class="px-2 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                        <i class="fa-solid fa-circle text-[7px]"></i> Maintenance
                                    </span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 bg-slate-100 text-slate-500 border border-slate-200 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                        <i class="fa-regular fa-circle text-[7px]"></i> Inactive
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-right space-x-1 whitespace-nowrap">
                                    <button onclick="openEditModal(<?php echo $room['id']; ?>)" class="p-1.5 bg-slate-50 dark:bg-gray-800 hover:bg-slate-100 dark:hover:bg-gray-700 text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white rounded-lg transition-colors" title="Edit Room">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="confirmDelete(<?php echo $room['id']; ?>)" class="p-1.5 bg-slate-50 dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-brand-muted dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 rounded-lg transition-colors" title="Delete Room">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="bg-slate-50/50 dark:bg-gray-900 px-6 py-4 border-t border-slate-100 dark:border-gray-800 flex items-center justify-between text-xs text-brand-muted dark:text-gray-400 font-semibold">
                    <span>Showing <?php echo count($rooms); ?> room<?php echo count($rooms) !== 1 ? 's' : ''; ?></span>
                </div>
            </div>
        </main>
    </div>

    <!-- CREATE MODAL -->
    <div id="createModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden p-4">
        <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-lg shadow-2xl max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100 dark:border-gray-800 shrink-0">
                <h3 class="text-base font-extrabold text-brand-dark dark:text-white"><i class="fa-solid fa-door-open text-brand-pink mr-2"></i> Add New Room</h3>
                <button onclick="closeCreateModal()" class="text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="room.php" class="p-6 space-y-5 overflow-y-auto flex-1">
                <input type="hidden" name="action" value="create">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Room Name <span class="text-red-400">*</span></label>
                        <input type="text" name="room_name" required placeholder="e.g., Treatment Suite A"
                            class="w-full border border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Room Number <span class="text-red-400">*</span></label>
                        <input type="text" name="room_number" required placeholder="e.g., R-001"
                            class="w-full border border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief description of the room"
                        class="w-full border border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Capacity</label>
                        <input type="number" name="capacity" min="1" value="1"
                            class="w-full border border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Status</label>
                        <select name="status" class="w-full border border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark bg-white focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                            <option value="active">Active</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider mb-2 block"><i class="fa-solid fa-stethoscope text-brand-pink mr-1"></i> Treatments This Room Supports</label>
                    <div class="max-h-40 overflow-y-auto border border-slate-200 dark:border-gray-700 rounded-xl p-3 space-y-2">
                        <?php if (!empty($treatments)): ?>
                            <?php foreach ($treatments as $treatment): ?>
                            <label class="flex items-center gap-2.5 cursor-pointer group/trt">
                                <input type="checkbox" name="treatments[]" value="<?php echo $treatment['id']; ?>"
                                    class="w-4 h-4 rounded border-slate-300 dark:border-gray-600 text-brand-pink focus:ring-brand-pink/30 cursor-pointer">
                                <span class="text-xs font-semibold text-brand-dark dark:text-gray-300 group-hover/trt:text-brand-pink transition-colors"><?php echo htmlspecialchars($treatment['treatment_name']); ?></span>
                            </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-xs text-brand-muted dark:text-gray-500 font-medium">No treatments available.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 sticky bottom-0 bg-white dark:bg-gray-900 -mx-6 px-6 pb-0 border-t border-slate-100 dark:border-gray-700">
                    <button type="button" onclick="closeCreateModal()" class="px-5 py-2.5 bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-brand-dark dark:text-white text-xs font-bold rounded-xl transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)]">
                        <i class="fa-solid fa-plus mr-1"></i> Add Room
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden p-4">
        <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-lg shadow-2xl max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100 dark:border-gray-800 shrink-0">
                <h3 class="text-base font-extrabold text-brand-dark dark:text-white"><i class="fa-regular fa-pen-to-square text-brand-pink mr-2"></i> Edit Room</h3>
                <button onclick="closeEditModal()" class="text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="room.php" class="p-6 space-y-5 overflow-y-auto flex-1">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id" value="">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Room Name <span class="text-red-400">*</span></label>
                        <input type="text" name="room_name" id="edit_room_name" required
                            class="w-full border border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Room Number <span class="text-red-400">*</span></label>
                        <input type="text" name="room_number" id="edit_room_number" required
                            class="w-full border border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Description</label>
                    <textarea name="description" id="edit_description" rows="2"
                        class="w-full border border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Capacity</label>
                        <input type="number" name="capacity" id="edit_capacity" min="1" value="1"
                            class="w-full border border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Status</label>
                        <select name="status" id="edit_status" class="w-full border border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark bg-white focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                            <option value="active">Active</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider mb-2 block"><i class="fa-solid fa-stethoscope text-brand-pink mr-1"></i> Treatments This Room Supports</label>
                    <div class="max-h-40 overflow-y-auto border border-slate-200 dark:border-gray-700 rounded-xl p-3 space-y-2">
                        <?php if (!empty($treatments)): ?>
                            <?php foreach ($treatments as $treatment): ?>
                            <label class="flex items-center gap-2.5 cursor-pointer group/trt">
                                <input type="checkbox" name="treatments[]" value="<?php echo $treatment['id']; ?>"
                                    class="edit-treatment-cb w-4 h-4 rounded border-slate-300 dark:border-gray-600 text-brand-pink focus:ring-brand-pink/30 cursor-pointer" data-tid="<?php echo $treatment['id']; ?>">
                                <span class="text-xs font-semibold text-brand-dark dark:text-gray-300 group-hover/trt:text-brand-pink transition-colors"><?php echo htmlspecialchars($treatment['treatment_name']); ?></span>
                            </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-xs text-brand-muted dark:text-gray-500 font-medium">No treatments available.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 sticky bottom-0 bg-white dark:bg-gray-900 -mx-6 px-6 pb-0 border-t border-slate-100 dark:border-gray-700">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-brand-dark dark:text-white text-xs font-bold rounded-xl transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)]">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Update Room
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE CONFIRM MODAL -->
    <div id="deleteModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-sm mx-4 shadow-2xl p-6 text-center">
            <div class="w-14 h-14 mx-auto bg-red-50 dark:bg-red-900/20 rounded-2xl flex items-center justify-center text-red-500 text-2xl mb-4">
                <i class="fa-regular fa-trash-can"></i>
            </div>
            <h3 class="text-base font-extrabold text-brand-dark dark:text-white mb-2">Delete Room?</h3>
            <p class="text-xs font-medium text-brand-muted dark:text-gray-300 mb-6">This action cannot be undone. Are you sure you want to delete this room?</p>
            <div class="flex justify-center gap-3">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-brand-dark dark:text-white text-xs font-bold rounded-xl transition-all">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(239,68,68,0.25)]">
                    <i class="fa-solid fa-trash-can mr-1"></i> Delete
                </a>
            </div>
        </div>
    </div>

    <script>
        const roomsData = <?php echo json_encode($rooms, JSON_UNESCAPED_UNICODE) ?: '[]'; ?>;

        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function openEditModal(id) {
            const room = roomsData.find(r => r.id == id);
            if (!room) return;
            document.getElementById('edit_id').value = room.id;
            document.getElementById('edit_room_name').value = room.room_name;
            document.getElementById('edit_room_number').value = room.room_number;
            document.getElementById('edit_description').value = room.description || '';
            document.getElementById('edit_capacity').value = room.capacity || 1;
            document.getElementById('edit_status').value = room.status || 'active';

            document.querySelectorAll('.edit-treatment-cb').forEach(cb => {
                cb.checked = room.assigned_treatments && room.assigned_treatments.includes(parseInt(cb.dataset.tid));
            });

            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function confirmDelete(id) {
            document.getElementById('deleteConfirmBtn').href = 'room.php?delete=' + id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        document.querySelectorAll('.modal-bg').forEach(el => {
            el.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-bg:not(.hidden)').forEach(m => m.classList.add('hidden'));
            }
        });
    </script>

</body>
</html>
<?php $conn->close(); ?>
