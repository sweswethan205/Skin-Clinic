<?php
require_once __DIR__ . '/../config/db.php';

$admin = $conn->query("SELECT username, photo FROM admins ORDER BY id ASC LIMIT 1")->fetch_assoc();
$admin_photo = $admin['photo'] ?? '';
$admin_username = $admin['username'] ?? 'Admin';

$message = '';
$message_type = '';

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $slot_time = trim($_POST['slot_time']);
        if (empty($slot_time)) {
            $message = "Time is required.";
            $message_type = "error";
        } else {
            $stmt = $conn->prepare("INSERT INTO time_slots (slot_time) VALUES (?)");
            $stmt->bind_param("s", $slot_time);
            if ($stmt->execute()) {
                $message = "Time slot added successfully!";
                $message_type = "success";
            } else {
                $message = $conn->errno == 1062 ? "This time slot already exists." : "Error adding time slot: " . $conn->error;
                $message_type = "error";
            }
            $stmt->close();
        }
        header("Location: time_slots.php?msg=" . urlencode($message) . "&type=$message_type");
        exit;
    } elseif ($_POST['action'] === 'update' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $slot_time = trim($_POST['slot_time']);
        if (empty($slot_time)) {
            $message = "Time is required.";
            $message_type = "error";
        } else {
            $stmt = $conn->prepare("UPDATE time_slots SET slot_time = ? WHERE id = ?");
            $stmt->bind_param("si", $slot_time, $id);
            if ($stmt->execute()) {
                $message = "Time slot updated successfully!";
                $message_type = "success";
            } else {
                $message = $conn->errno == 1062 ? "This time slot already exists." : "Error updating time slot: " . $conn->error;
                $message_type = "error";
            }
            $stmt->close();
        }
        header("Location: time_slots.php?msg=" . urlencode($message) . "&type=$message_type");
        exit;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM time_slots WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Time slot deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting time slot: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
    header("Location: time_slots.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Read message from redirect
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $message_type = $_GET['type'] ?? 'success';
}

// Fetch all time slots
$slots_result = $conn->query("SELECT * FROM time_slots ORDER BY slot_time ASC");
$slots = [];
while ($row = $slots_result->fetch_assoc()) {
    $row['id'] = (int)$row['id'];
    $slots[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Time Slots</title>
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
            <div class="flex items-center">
                <div>
                    <h2 class="text-xl font-extrabold text-brand-dark dark:text-white tracking-tight">Time Slots</h2>
                    <p class="text-xs text-brand-muted dark:text-gray-400 font-medium">Manage 30-minute booking intervals for appointments.</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <?php include 'header-actions.php'; ?>
                <a href="profile.php" class="flex items-center space-x-3 hover:opacity-80 transition">
                    <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 bg-brand-lightPink flex items-center justify-center text-brand-pink font-bold text-sm">
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
            <div class="px-5 py-3.5 rounded-xl border text-sm font-bold flex items-center gap-3 <?php echo $message_type === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200'; ?>">
                <i class="fa-solid <?php echo $message_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-current opacity-60 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-pink-50 text-brand-pink rounded-xl flex items-center justify-center text-sm"><i class="fa-regular fa-clock"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Total Slots</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white"><?php echo count($slots); ?></span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-hourglass-start"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Earliest</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white">
                            <?php echo !empty($slots) ? date('g:i A', strtotime($slots[0]['slot_time'])) : 'N/A'; ?>
                        </span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-hourglass-end"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Latest Start</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white">
                            <?php echo !empty($slots) ? date('g:i A', strtotime(end($slots)['slot_time'])) : 'N/A'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-sm font-bold text-brand-dark px-2">
                    Available Booking Time Slots
                </div>
                <button onclick="openCreateModal()" class="px-4 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)] flex items-center justify-center gap-2 shrink-0">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add Time Slot
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-slate-200/60 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-gray-950 border-b border-slate-200/50 dark:border-gray-800 text-[11px] font-bold uppercase tracking-wider text-brand-muted dark:text-gray-300">
                                <th class="py-3 px-3 sm:py-4 sm:px-6">#</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Time Slot</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">24-Hour Format</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-semibold text-brand-dark dark:text-gray-300">
                            <?php if (empty($slots)): ?>
                            <tr>
                                <td colspan="4" class="py-12 text-center">
                                    <div class="text-brand-muted">
                                        <i class="fa-regular fa-clock text-3xl mb-3 block"></i>
                                        <span class="font-bold text-sm">No time slots found</span>
                                        <p class="text-[11px] font-medium mt-1">Add a new time slot to get started.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($slots as $index => $slot): ?>
                            <tr class="hover:bg-slate-50/60 transition-colors group">
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <span class="text-brand-muted font-mono"><?php echo $index + 1; ?></span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 bg-brand-lightPink dark:bg-pink-900/20 rounded-xl flex items-center justify-center text-brand-pink text-xs font-bold">
                                            <i class="fa-regular fa-clock"></i>
                                        </div>
                                        <span class="font-bold text-brand-dark dark:text-white group-hover:text-brand-pink transition-colors">
                                            <?php echo date('g:i A', strtotime($slot['slot_time'])); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <span class="font-mono text-slate-500 dark:text-gray-400"><?php echo date('H:i', strtotime($slot['slot_time'])); ?></span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-right space-x-1 whitespace-nowrap">
                                    <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($slot), ENT_QUOTES); ?>)" class="p-1.5 bg-slate-50 hover:bg-slate-100 text-brand-muted hover:text-brand-dark rounded-lg transition-colors" title="Edit Slot">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="confirmDelete(<?php echo $slot['id']; ?>, '<?php echo date('g:i A', strtotime($slot['slot_time'])); ?>')" class="p-1.5 bg-slate-50 hover:bg-red-50 text-brand-muted hover:text-red-500 rounded-lg transition-colors" title="Delete Slot">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="bg-slate-50/50 dark:bg-gray-900 px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-brand-muted font-semibold dark:text-gray-400">
                    <span>Showing <?php echo count($slots); ?> time slot<?php echo count($slots) !== 1 ? 's' : ''; ?></span>
                </div>
            </div>
        </main>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 modal-bg z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-sm shadow-2xl border border-slate-100 dark:border-gray-700">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-bold text-brand-dark dark:text-white">Add Time Slot</h3>
                <button onclick="closeCreateModal()" class="text-brand-muted hover:text-brand-dark dark:hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="time_slots.php">
                <input type="hidden" name="action" value="create">
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-brand-muted uppercase tracking-wider mb-1.5">Time</label>
                        <input type="time" name="slot_time" required step="1800"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-semibold text-brand-dark dark:text-white focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none">
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-xs font-bold text-brand-muted hover:text-brand-dark rounded-xl border border-slate-200 dark:border-gray-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)]">Add Slot</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 modal-bg z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-sm shadow-2xl border border-slate-100 dark:border-gray-700">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-bold text-brand-dark dark:text-white">Edit Time Slot</h3>
                <button onclick="closeEditModal()" class="text-brand-muted hover:text-brand-dark dark:hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="time_slots.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-brand-muted uppercase tracking-wider mb-1.5">Time</label>
                        <input type="time" name="slot_time" id="edit_slot_time" required step="1800"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-semibold text-brand-dark dark:text-white focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none">
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-xs font-bold text-brand-muted hover:text-brand-dark rounded-xl border border-slate-200 dark:border-gray-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)]">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 modal-bg z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-sm shadow-2xl border border-slate-100 dark:border-gray-700 text-center p-6">
            <div class="w-14 h-14 bg-red-50 dark:bg-red-900/20 rounded-full flex items-center justify-center text-red-500 mx-auto mb-4">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-brand-dark dark:text-white mb-1">Delete Time Slot</h3>
            <p class="text-sm text-brand-muted dark:text-gray-400 mb-5">Are you sure you want to delete the <strong id="delete-slot-name" class="text-brand-dark dark:text-white"></strong> slot?</p>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 text-xs font-bold text-brand-muted border border-slate-200 dark:border-gray-700 rounded-xl hover:bg-slate-50">Cancel</button>
                <a id="delete-confirm-btn" href="#" class="flex-1 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition-all text-center">Delete</a>
            </div>
        </div>
    </div>

    <script>
        function openCreateModal() { document.getElementById('createModal').classList.remove('hidden'); }
        function closeCreateModal() { document.getElementById('createModal').classList.add('hidden'); }

        function openEditModal(slot) {
            document.getElementById('edit_id').value = slot.id;
            document.getElementById('edit_slot_time').value = slot.slot_time.substring(0, 5);
            document.getElementById('editModal').classList.remove('hidden');
        }
        function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); }

        function confirmDelete(id, name) {
            document.getElementById('delete-slot-name').textContent = name;
            document.getElementById('delete-confirm-btn').href = 'time_slots.php?delete=' + id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        function closeDeleteModal() { document.getElementById('deleteModal').classList.add('hidden'); }

        document.querySelectorAll('.modal-bg').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        });
    </script>

</body>
</html>
<?php $conn->close(); ?>
