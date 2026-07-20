<?php
require_once __DIR__ . '/../config/db.php';

$admin = $conn->query("SELECT username, photo FROM admins ORDER BY id ASC LIMIT 1")->fetch_assoc();
$admin_photo = $admin['photo'] ?? '';
$admin_username = $admin['username'] ?? 'Admin';

$message = '';
$message_type = '';

// Handle Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $doctor_id = intval($_POST['doctor_id']);

    if ($_POST['action'] === 'create') {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        if ($start_date >= $end_date) {
            $message = "End date must be after start date!";
            $message_type = "error";
            header("Location: schedule.php?msg=" . urlencode($message) . "&type=$message_type");
            exit;
        }

        $doc_stmt = $conn->prepare("SELECT work_start, work_end FROM doctors WHERE id=?");
        $doc_stmt->bind_param("i", $doctor_id);
        $doc_stmt->execute();
        $doc_result = $doc_stmt->get_result();
        $doctor = $doc_result->fetch_assoc();
        $doc_stmt->close();

        if (!$doctor) {
            $message = "Doctor not found!";
            $message_type = "error";
            header("Location: schedule.php?msg=" . urlencode($message) . "&type=$message_type");
            exit;
        }

        $work_start = $doctor['work_start'];
        $work_end = $doctor['work_end'];

        $current = new DateTime($start_date);
        $end = new DateTime($end_date);
        $end->modify('+1 day');
        $inserted = 0;
        $skipped = 0;

        $stmt = $conn->prepare("INSERT IGNORE INTO schedules (doctor_id, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");

        while ($current < $end) {
            $date_str = $current->format('Y-m-d');
            $stmt->bind_param("isss", $doctor_id, $date_str, $work_start, $work_end);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $inserted++;
                } else {
                    $skipped++;
                }
            }
            $current->modify('+1 day');
        }
        $stmt->close();

        $msg_parts = "$inserted availability date(s) added successfully!";
        if ($skipped > 0) {
            $msg_parts .= " ($skipped duplicate date(s) skipped)";
        }
        $message = $msg_parts;
        $message_type = "success";
    } elseif ($_POST['action'] === 'update' && isset($_POST['id'])) {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        if ($start_date >= $end_date) {
            $message = "End date must be after start date!";
            $message_type = "error";
            header("Location: schedule.php?msg=" . urlencode($message) . "&type=$message_type");
            exit;
        }

        $del = $conn->prepare("DELETE FROM schedules WHERE doctor_id=? AND available_date >= CURDATE() AND NOT EXISTS (SELECT 1 FROM appointments a WHERE a.schedule_id = schedules.id AND a.status != 'cancelled')");
        $del->bind_param("i", $doctor_id);
        $del->execute();
        $del->close();

        $doc_stmt = $conn->prepare("SELECT work_start, work_end FROM doctors WHERE id=?");
        $doc_stmt->bind_param("i", $doctor_id);
        $doc_stmt->execute();
        $doc_result = $doc_stmt->get_result();
        $doctor = $doc_result->fetch_assoc();
        $doc_stmt->close();

        if (!$doctor) {
            $message = "Doctor not found!";
            $message_type = "error";
            header("Location: schedule.php?msg=" . urlencode($message) . "&type=$message_type");
            exit;
        }

        $work_start = $doctor['work_start'];
        $work_end = $doctor['work_end'];

        $current = new DateTime($start_date);
        $end = new DateTime($end_date);
        $end->modify('+1 day');
        $inserted = 0;
        $skipped = 0;

        $stmt = $conn->prepare("INSERT IGNORE INTO schedules (doctor_id, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");

        while ($current < $end) {
            $date_str = $current->format('Y-m-d');
            $stmt->bind_param("isss", $doctor_id, $date_str, $work_start, $work_end);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $inserted++;
                } else {
                    $skipped++;
                }
            }
            $current->modify('+1 day');
        }
        $stmt->close();

        $msg_parts = "Schedule updated! $inserted availability date(s) created for " . htmlspecialchars($_POST['start_date']) . " to " . htmlspecialchars($_POST['end_date']);
        if ($skipped > 0) {
            $msg_parts .= " ($skipped duplicate date(s) skipped)";
        }
        $message = $msg_parts;
        $message_type = "success";
    }
    header("Location: schedule.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM schedules WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Schedule deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting schedule: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
    header("Location: schedule.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Read message from redirect
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $message_type = $_GET['type'] ?? 'success';
}

// Fetch all doctors for dropdown
$doctors_result = $conn->query("SELECT id, name FROM doctors ORDER BY name ASC");
$doctors = [];
while ($row = $doctors_result->fetch_assoc()) {
    $doctors[] = $row;
}

// Pagination
$doctor_filter = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$per_page = 15;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Count total
if ($doctor_filter > 0) {
    $cnt_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM schedules s WHERE s.doctor_id = ?");
    $cnt_stmt->bind_param("i", $doctor_filter);
    $cnt_stmt->execute();
    $total_rows = $cnt_stmt->get_result()->fetch_assoc()['cnt'];
    $cnt_stmt->close();
} else {
    $total_rows = $conn->query("SELECT COUNT(*) AS cnt FROM schedules")->fetch_assoc()['cnt'];
}
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// Fetch schedules with doctor name (paginated)
if ($doctor_filter > 0) {
    $sched_stmt = $conn->prepare("SELECT s.*, d.name AS doctor_name FROM schedules s JOIN doctors d ON s.doctor_id = d.id WHERE s.doctor_id = ? ORDER BY s.available_date DESC, s.start_time ASC LIMIT ? OFFSET ?");
    $sched_stmt->bind_param("iii", $doctor_filter, $per_page, $offset);
    $sched_stmt->execute();
    $schedules_result = $sched_stmt->get_result();
} else {
    $sched_stmt = $conn->prepare("SELECT s.*, d.name AS doctor_name FROM schedules s JOIN doctors d ON s.doctor_id = d.id ORDER BY s.available_date DESC, s.start_time ASC LIMIT ? OFFSET ?");
    $sched_stmt->bind_param("ii", $per_page, $offset);
    $sched_stmt->execute();
    $schedules_result = $sched_stmt->get_result();
}
$schedules = [];
while ($row = $schedules_result->fetch_assoc()) {
    $schedules[] = $row;
}
$sched_stmt->close();

// Stats use totals, not paginated data
$total_schedules = $total_rows;

// Fetch single schedule for edit modal
$edit_schedule = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM schedules WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_schedule = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Doctor Schedules</title>
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

    <!-- CONTENT -->
    <div class="flex-grow flex flex-col min-w-0 lg:ml-64">

        <!-- HEADER -->
        <header class="h-16 sm:h-20 bg-white dark:bg-gray-900 border-b border-slate-200/60 dark:border-gray-800 flex items-center justify-between px-4 sm:px-8 shrink-0 z-10 sticky top-0">
            <div class="flex items-center space-x-4">
               
                <div>
            <h2 class="text-xl font-extrabold text-brand-dark dark:text-white tracking-tight">Doctor Schedules</h2>
            <p class="text-xs text-brand-muted dark:text-gray-400 font-medium">Manage doctor availability and time slots.</p>
                </div>
    </div>

    <div class="flex items-center space-x-4">
        <?php include 'header-actions.php'; ?>
        <a href="profile.php" class="flex items-center space-x-3 hover:opacity-80 transition">
            <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 dark:border-gray-700 bg-brand-lightPink dark:bg-pink-900/20 flex items-center justify-center text-brand-pink font-bold text-sm">
                <?php if ($admin_photo): ?>
                    <img src="../<?php echo htmlspecialchars($admin_photo); ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div>
                <span class="text-xs font-bold text-brand-dark dark:text-white block leading-tight"><?php echo htmlspecialchars($admin_username); ?></span>
                <!-- <span class="text-[10px] font-medium text-brand-muted dark:text-gray-400">Clinic Supervisor</span> -->
            </div>
        </a>
    </div>
</header>

        <!-- MAIN -->
        <main class="flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6">

            <!-- Message Alert -->
            <?php if ($message): ?>
            <div class="px-5 py-3.5 rounded-xl border text-sm font-bold flex items-center gap-3 <?php echo $message_type === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800'; ?>">
                <i class="fa-solid <?php echo $message_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-current opacity-60 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <?php endif; ?>

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-pink-50 dark:bg-pink-900/20 text-brand-pink rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-calendar-day"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Total Schedules</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white"><?php echo $total_schedules; ?></span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-amber-50 dark:bg-amber-900/20 text-amber-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Active Doctors</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white"><?php echo count($doctors); ?></span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-calendar-check"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Unique Dates</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white">
                            <?php
                            if ($doctor_filter > 0) {
                                $ud_stmt = $conn->prepare("SELECT COUNT(DISTINCT s.available_date) AS cnt FROM schedules s WHERE s.doctor_id = ?");
                                $ud_stmt->bind_param("i", $doctor_filter);
                                $ud_stmt->execute();
                                echo $ud_stmt->get_result()->fetch_assoc()['cnt'];
                                $ud_stmt->close();
                            } else {
                                echo $conn->query("SELECT COUNT(DISTINCT available_date) AS cnt FROM schedules")->fetch_assoc()['cnt'];
                            }
                            ?>
                        </span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 text-blue-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Upcoming Dates</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white">
                            <?php
                            $today = date('Y-m-d');
                            if ($doctor_filter > 0) {
                                $up_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM schedules s WHERE s.doctor_id = ? AND s.available_date >= ?");
                                $up_stmt->bind_param("is", $doctor_filter, $today);
                                $up_stmt->execute();
                                echo $up_stmt->get_result()->fetch_assoc()['cnt'];
                                $up_stmt->close();
                            } else {
                                $up_result = $conn->query("SELECT COUNT(*) AS cnt FROM schedules WHERE available_date >= '$today'");
                                echo $up_result->fetch_assoc()['cnt'];
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-bold text-brand-dark dark:text-white px-2">Schedule Directory</span>
                    <form method="GET" action="schedule.php" class="flex items-center gap-2">
                        <select name="doctor_id" onchange="this.form.submit()" class="text-xs border border-slate-200 dark:border-gray-700 rounded-lg px-3 py-2 font-semibold text-brand-muted dark:text-gray-400 bg-white dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none">
                            <option value="0">All Doctors</option>
                            <?php foreach ($doctors as $doc): ?>
                            <option value="<?php echo $doc['id']; ?>" <?php echo $doctor_filter === $doc['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($doc['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($doctor_filter > 0): ?>
                        <a href="schedule.php" class="text-[11px] text-brand-muted dark:text-gray-400 hover:text-brand-pink font-semibold"><i class="fa-solid fa-xmark"></i> Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                <button onclick="openCreateModal()" class="px-4 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)] flex items-center justify-center gap-2 shrink-0">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add New Schedule
                </button>
            </div>

            <!-- Schedules Table -->
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-slate-200/60 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-gray-950 border-b border-slate-200/50 dark:border-gray-800 text-[11px] font-bold uppercase tracking-wider text-brand-muted dark:text-gray-400">
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Doctor</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Date</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Available Hours</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800 text-xs font-semibold text-brand-dark dark:text-gray-300">
                            <?php if (empty($schedules)): ?>
                            <tr>
                                <td colspan="4" class="py-12 text-center">
                                    <div class="text-brand-muted dark:text-gray-400">
                                        <i class="fa-regular fa-calendar-xmark text-3xl mb-3 block"></i>
                                        <span class="font-bold text-sm">No schedules found</span>
                                        <p class="text-[11px] font-medium mt-1">Add a new schedule to get started.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($schedules as $schedule): ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-gray-800 transition-colors group">
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 bg-brand-lightPink dark:bg-pink-900/20 rounded-xl flex items-center justify-center text-brand-pink text-xs font-bold">
                                            <?php echo strtoupper(substr($schedule['doctor_name'], 0, 2)); ?>
                                        </div>
                                        <span class="font-bold text-brand-dark dark:text-white group-hover:text-brand-pink transition-colors"><?php echo htmlspecialchars($schedule['doctor_name']); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <span class="font-medium text-slate-600 dark:text-gray-400"><?php echo date('M d, Y', strtotime($schedule['available_date'])); ?></span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <span class="font-mono text-sm"><?php echo date('h:i A', strtotime($schedule['start_time'])); ?> - <?php echo date('h:i A', strtotime($schedule['end_time'])); ?></span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-right space-x-1 whitespace-nowrap">
                                    <button onclick="openEditModal(<?php echo $schedule['id']; ?>)" class="p-1.5 bg-slate-50 hover:bg-slate-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white rounded-lg transition-colors" title="Edit Schedule">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="confirmDelete(<?php echo $schedule['id']; ?>)" class="p-1.5 bg-slate-50 hover:bg-red-50 dark:bg-gray-800 dark:hover:bg-red-900/30 text-brand-muted dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 rounded-lg transition-colors" title="Delete Schedule">
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
                    <span>Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $per_page, $total_rows); ?> of <?php echo $total_rows; ?> schedule<?php echo $total_rows !== 1 ? 's' : ''; ?></span>
                    <?php if ($total_pages > 1): ?>
                    <div class="flex items-center gap-1">
                        <?php
                        $page_base = 'schedule.php?' . ($doctor_filter > 0 ? "doctor_id=$doctor_filter&" : '');
                        ?>
                        <?php if ($page > 1): ?>
                        <a href="<?= $page_base ?>page=<?= $page - 1 ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 hover:bg-slate-100 dark:hover:bg-gray-700 transition-colors"><i class="fa-solid fa-chevron-left text-[10px]"></i></a>
                        <?php endif; ?>
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        for ($p = $start; $p <= $end; $p++):
                        ?>
                        <a href="<?= $page_base ?>page=<?= $p ?>" class="px-3 py-1.5 rounded-lg <?= $p === $page ? 'bg-brand-dark text-white' : 'bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 hover:bg-slate-100 dark:hover:bg-gray-700' ?> transition-colors"><?= $p ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                        <a href="<?= $page_base ?>page=<?= $page + 1 ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 hover:bg-slate-100 dark:hover:bg-gray-700 transition-colors"><i class="fa-solid fa-chevron-right text-[10px]"></i></a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- CREATE MODAL -->
    <div id="createModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-lg mx-4 shadow-2xl">
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100 dark:border-gray-800">
                <h3 class="text-base font-extrabold text-brand-dark dark:text-white"><i class="fa-regular fa-calendar-plus text-brand-pink mr-2"></i> Add Monthly Schedule</h3>
                <button onclick="closeCreateModal()" class="text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="schedule.php" class="p-6 space-y-5">
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Doctor</label>
                    <select name="doctor_id" required class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white bg-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                        <option value="">Select Doctor</option>
                        <?php foreach ($doctors as $doc): ?>
                        <option value="<?php echo $doc['id']; ?>"><?php echo htmlspecialchars($doc['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Start Date</label>
                        <input type="date" name="start_date" id="create_start_date" required min="<?php echo date('Y-m-d'); ?>"
                            class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">End Date</label>
                        <input type="date" name="end_date" id="create_end_date" required min="<?php echo date('Y-m-d'); ?>"
                            class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-3 text-[11px] font-medium text-blue-700 dark:text-blue-400">
                    <i class="fa-solid fa-info-circle mr-1"></i>
                    Working hours will be taken from the doctor's profile (e.g., <strong>09:00 - 19:00</strong>). One availability entry is created per day. Duplicate dates are skipped.
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeCreateModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-brand-dark dark:text-white text-xs font-bold rounded-xl transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)]">
                        <i class="fa-solid fa-plus mr-1"></i> Create Monthly Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-lg mx-4 shadow-2xl">
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100 dark:border-gray-800">
                <h3 class="text-base font-extrabold text-brand-dark dark:text-white"><i class="fa-regular fa-pen-to-square text-brand-pink mr-2"></i> Edit Monthly Schedule</h3>
                <button onclick="closeEditModal()" class="text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="schedule.php" class="p-6 space-y-5">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id" value="">
                <div>
                    <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Doctor</label>
                    <select name="doctor_id" id="edit_doctor_id" required class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white bg-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                        <option value="">Select Doctor</option>
                        <?php foreach ($doctors as $doc): ?>
                        <option value="<?php echo $doc['id']; ?>"><?php echo htmlspecialchars($doc['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Start Date</label>
                        <input type="date" name="start_date" id="edit_start_date" required min="<?php echo date('Y-m-d'); ?>"
                            class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">End Date</label>
                        <input type="date" name="end_date" id="edit_end_date" required min="<?php echo date('Y-m-d'); ?>"
                            class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-3 text-[11px] font-medium text-blue-700 dark:text-blue-400">
                    <i class="fa-solid fa-info-circle mr-1"></i>
                    Working hours will be taken from the doctor's profile (e.g., <strong>09:00 - 19:00</strong>). One availability entry is created per day. Duplicate dates are skipped.
                </div>

                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-xl p-3 text-[11px] font-medium text-amber-700 dark:text-amber-400">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    This will <strong>delete all future unbooked schedules</strong> for this doctor and regenerate availability entries based on the new date range. Dates with active appointments will not be affected.
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-brand-dark dark:text-white text-xs font-bold rounded-xl transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)]">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Update Schedule
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
            <h3 class="text-base font-extrabold text-brand-dark dark:text-white mb-2">Delete Schedule?</h3>
            <p class="text-xs font-medium text-brand-muted dark:text-gray-300 mb-6">This action cannot be undone. Are you sure you want to delete this schedule?</p>
            <div class="flex justify-center gap-3">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-brand-dark dark:text-white text-xs font-bold rounded-xl transition-all">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(239,68,68,0.25)]">
                    <i class="fa-solid fa-trash-can mr-1"></i> Delete
                </a>
            </div>
        </div>
    </div>

    <script>
        // Schedule data for edit modal (embedded as JSON)
        const schedulesData = <?php echo json_encode($schedules); ?>;

        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function openEditModal(id) {
            const schedule = schedulesData.find(s => s.id == id);
            if (!schedule) return;
            document.getElementById('edit_id').value = schedule.id;
            document.getElementById('edit_doctor_id').value = schedule.doctor_id;
            document.getElementById('edit_start_date').value = schedule.available_date;
            document.getElementById('edit_end_date').value = schedule.available_date;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function confirmDelete(id) {
            document.getElementById('deleteConfirmBtn').href = 'schedule.php?delete=' + id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Close modals on backdrop click
        document.querySelectorAll('.modal-bg').forEach(el => {
            el.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        });

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-bg:not(.hidden)').forEach(m => m.classList.add('hidden'));
            }
        });

        // Date validation - ensure end date is strictly after start date
        function getNextDay(dateStr) {
            const date = new Date(dateStr);
            date.setDate(date.getDate() + 1);
            return date.toISOString().split('T')[0];
        }

        document.getElementById('create_start_date').addEventListener('change', function() {
            const endInput = document.getElementById('create_end_date');
            endInput.min = getNextDay(this.value);
            if (endInput.value && endInput.value <= this.value) {
                endInput.value = getNextDay(this.value);
            }
        });

        document.getElementById('edit_start_date').addEventListener('change', function() {
            const endInput = document.getElementById('edit_end_date');
            endInput.min = getNextDay(this.value);
            if (endInput.value && endInput.value <= this.value) {
                endInput.value = getNextDay(this.value);
            }
        });
    </script>

</body>
</html>
