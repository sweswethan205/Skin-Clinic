<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$doctor_id = $_SESSION['doctor_id'];
$doctor_name = $_SESSION['doctor_name'] ?? 'Doctor';
$doctor_photo = $_SESSION['doctor_photo'] ?? '';

$doctor = $conn->query("SELECT name, photo FROM doctors WHERE id = $doctor_id LIMIT 1")->fetch_assoc();
if ($doctor) {
    $doctor_photo = $doctor['photo'] ?? '';
    $doctor_name = $doctor['name'] ?? 'Doctor';
    $_SESSION['doctor_photo'] = $doctor_photo;
    $_SESSION['doctor_name'] = $doctor_name;
}

$status_filter = $_GET['status'] ?? 'all';
$valid_statuses = ['all', 'pending', 'confirmed', 'cancelled'];
if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'all';
}

$query = "
    SELECT
        a.id, a.appointment_start, a.appointment_end, a.status, a.created_at, a.receipt_image,
        u.name AS patient_name, u.email AS patient_email, u.phone AS patient_phone,
        t.treatment_name, t.duration, t.price,
        s.available_date, s.start_time AS schedule_start, s.end_time AS schedule_end
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN treatments t ON a.treatment_id = t.id
    JOIN schedules s ON a.schedule_id = s.id
    WHERE s.doctor_id = ?
";

if ($status_filter !== 'all') {
    $query .= " AND a.status = ?";
}
$query .= " ORDER BY s.available_date DESC, a.appointment_start DESC";

$stmt = $conn->prepare($query);
if ($status_filter !== 'all') {
    $stmt->bind_param("is", $doctor_id, $status_filter);
} else {
    $stmt->bind_param("i", $doctor_id);
}
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_count = count($appointments);
$pending_count = count(array_filter($appointments, fn($a) => $a['status'] === 'pending'));
$confirmed_count = count(array_filter($appointments, fn($a) => $a['status'] === 'confirmed'));
$cancelled_count = count(array_filter($appointments, fn($a) => $a['status'] === 'cancelled'));

// Today's appointments
$today = date('Y-m-d');
$today_stmt = $conn->prepare("
    SELECT
        a.id, a.appointment_start, a.appointment_end, a.status, a.receipt_image,
        u.name AS patient_name, u.phone AS patient_phone,
        t.treatment_name, t.duration, t.price,
        s.available_date, rm.room_name
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN treatments t ON a.treatment_id = t.id
    JOIN schedules s ON a.schedule_id = s.id
    LEFT JOIN rooms rm ON rm.id = a.room_id
    WHERE s.doctor_id = ? AND s.available_date = ? AND a.status != 'cancelled'
    ORDER BY a.appointment_start ASC
");
$today_stmt->bind_param("is", $doctor_id, $today);
$today_stmt->execute();
$today_appointments = $today_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$today_stmt->close();

$today_total = count($today_appointments);
$today_pending = count(array_filter($today_appointments, fn($a) => $a['status'] === 'pending'));
$today_confirmed = count(array_filter($today_appointments, fn($a) => $a['status'] === 'confirmed'));

function format_time_12($time)
{
    $time_obj = new DateTime($time);
    return $time_obj->format('g:i A');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Doctor Dashboard</title>
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
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('profile-dropdown');
            if (dropdown && !e.target.closest('#profile-dropdown') && !e.target.closest('button[onclick*="profile-dropdown"]')) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</head>

<body class="bg-brand-canvas dark:bg-gray-950 text-slate-700 dark:text-gray-100 min-h-screen flex antialiased">

    <!-- CONTENT -->
    <div class="flex-grow flex flex-col min-w-0">

        <!-- HEADER -->
        <header class="h-16 sm:h-20 bg-white dark:bg-gray-900 border-b border-slate-200/60 dark:border-gray-800 flex items-center justify-between px-4 sm:px-8 shrink-0 z-10 sticky top-0">
            <div class="flex items-center space-x-4">
                <div>
                    <h2 class="text-xl font-extrabold text-brand-dark dark:text-white tracking-tight">My Appointments</h2>
                    <p class="text-xs text-brand-muted dark:text-gray-400 font-medium">View your scheduled patient appointments.</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <?php include __DIR__ . '/header-actions.php'; ?>
                <div class="relative">
                    <button onclick="document.getElementById('profile-dropdown').classList.toggle('hidden')" class="flex items-center space-x-3 hover:opacity-80 transition">
                        <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 bg-brand-lightPink flex items-center justify-center text-brand-pink font-bold text-sm">
                            <?php if ($doctor_photo): ?>
                                <img src="../<?php echo htmlspecialchars($doctor_photo); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php echo strtoupper(substr($doctor_name, 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="hidden sm:block">
                            <span class="text-xs font-bold text-brand-dark dark:text-white block leading-tight">Dr. <?php echo htmlspecialchars($doctor_name); ?></span>
                            <span class="text-[10px] font-medium text-brand-muted dark:text-gray-400">Doctor</span>
                        </div>
                    </button>
                    <div id="profile-dropdown" class="hidden absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-900 rounded-xl border border-slate-200/60 dark:border-gray-700 shadow-lg z-50 overflow-hidden">
                        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-800 hover:text-brand-pink transition">
                            <i class="fa-solid fa-user-gear text-xs"></i> My Profile
                        </a>
                        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                            <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN -->
        <main class="flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6">

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 sm:gap-6">
                <a href="?status=all" class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4 transition hover:shadow-md <?= $status_filter === 'all' ? 'ring-2 ring-brand-pink/30' : '' ?>">
                    <div class="w-10 h-10 bg-pink-50 text-brand-pink rounded-xl flex items-center justify-center text-sm"><i class="fa-regular fa-calendar-check"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Total</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white"><?php echo $total_count; ?></span>
                    </div>
                </a>
                <a href="?status=pending" class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4 transition hover:shadow-md <?= $status_filter === 'pending' ? 'ring-2 ring-amber-300/50' : '' ?>">
                    <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Pending</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white"><?php echo $pending_count; ?></span>
                    </div>
                </a>
                <a href="?status=confirmed" class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4 transition hover:shadow-md <?= $status_filter === 'confirmed' ? 'ring-2 ring-emerald-300/50' : '' ?>">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-circle-check"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Confirmed</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white"><?php echo $confirmed_count; ?></span>
                    </div>
                </a>
                <a href="?status=cancelled" class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4 transition hover:shadow-md <?= $status_filter === 'cancelled' ? 'ring-2 ring-red-300/50' : '' ?>">
                    <div class="w-10 h-10 bg-red-50 text-red-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-circle-xmark"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Cancelled</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white"><?php echo $cancelled_count; ?></span>
                    </div>
                </a>
            </div>

            <!-- Today's Appointments -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-brand-pink/10 rounded-xl flex items-center justify-center text-brand-pink text-sm">
                            <i class="fa-regular fa-calendar-day"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-brand-dark dark:text-white">Today's Appointments</h3>
                            <p class="text-[10px] font-semibold text-brand-muted dark:text-gray-400"><?php echo date('l, M d, Y'); ?> &middot; <?php echo $today_total; ?> appointment<?php echo $today_total !== 1 ? 's' : ''; ?></p>
                        </div>
                    </div>
                    <?php if ($today_total > 0): ?>
                        <div class="flex gap-2">
                            <?php if ($today_pending > 0): ?>
                                <span class="px-2.5 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                    <i class="fa-solid fa-circle text-[7px]"></i> <?php echo $today_pending; ?> Pending
                                </span>
                            <?php endif; ?>
                            <?php if ($today_confirmed > 0): ?>
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                    <i class="fa-solid fa-circle text-[7px]"></i> <?php echo $today_confirmed; ?> Confirmed
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($today_appointments)): ?>
                    <div class="px-5 py-10 text-center">
                        <div class="text-brand-muted dark:text-gray-400">
                            <i class="fa-regular fa-calendar-check text-3xl mb-3 block"></i>
                            <span class="font-bold text-sm">No appointments today</span>
                            <p class="text-[11px] font-medium mt-1">Enjoy your free day or check upcoming schedules.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 dark:bg-gray-950 border-b border-slate-200/50 dark:border-gray-800 text-[11px] font-bold uppercase tracking-wider text-brand-muted dark:text-gray-400">
                                    <th class="py-3 px-3 sm:py-4 sm:px-6">Time</th>
                                    <th class="py-3 px-3 sm:py-4 sm:px-6">Patient</th>
                                    <th class="py-3 px-3 sm:py-4 sm:px-6">Treatment</th>
                                    <th class="py-3 px-3 sm:py-4 sm:px-6">Duration</th>
                                    <th class="py-3 px-3 sm:py-4 sm:px-6">Room</th>
                                    <th class="py-3 px-3 sm:py-4 sm:px-6 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-gray-800 text-xs font-semibold text-brand-dark dark:text-gray-300">
                                <?php foreach ($today_appointments as $apt): ?>
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-gray-800/60 transition-colors group">
                                    <td class="py-3 px-3 sm:py-4 sm:px-6">
                                        <span class="font-bold text-brand-dark dark:text-white">
                                            <?php echo format_time_12($apt['appointment_start']); ?>
                                        </span>
                                        <span class="text-[10px] text-brand-muted dark:text-gray-400 block font-medium">
                                            to <?php echo format_time_12($apt['appointment_end']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 sm:py-4 sm:px-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-brand-lightPink dark:bg-pink-900/20 rounded-xl flex items-center justify-center text-brand-pink text-xs font-bold shrink-0">
                                                <?php echo strtoupper(substr($apt['patient_name'], 0, 2)); ?>
                                            </div>
                                            <div>
                                                <span class="font-bold text-brand-dark dark:text-white block group-hover:text-brand-pink transition-colors"><?php echo htmlspecialchars($apt['patient_name']); ?></span>
                                                <?php if (!empty($apt['patient_phone'])): ?>
                                                    <span class="text-[10px] text-brand-muted dark:text-gray-400 block font-medium"><?php echo htmlspecialchars($apt['patient_phone']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 sm:py-4 sm:px-6">
                                        <span class="font-bold text-brand-dark dark:text-white block"><?php echo htmlspecialchars($apt['treatment_name']); ?></span>
                                        <span class="text-[10px] text-brand-muted dark:text-gray-400 block font-medium">$<?php echo number_format($apt['price'], 2); ?></span>
                                    </td>
                                    <td class="py-3 px-3 sm:py-4 sm:px-6">
                                        <span class="px-2 py-1 bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 rounded-lg text-[10px] font-bold">
                                            <?php echo $apt['duration']; ?> min
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 sm:py-4 sm:px-6">
                                        <?php if (!empty($apt['room_name'])): ?>
                                            <span class="font-semibold text-brand-dark dark:text-white"><?php echo htmlspecialchars($apt['room_name']); ?></span>
                                        <?php else: ?>
                                            <span class="text-brand-muted dark:text-gray-400">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 sm:py-4 sm:px-6 text-center">
                                        <?php if ($apt['status'] === 'pending'): ?>
                                        <span class="px-2 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                            <i class="fa-solid fa-circle text-[7px]"></i> Pending
                                        </span>
                                        <?php elseif ($apt['status'] === 'confirmed'): ?>
                                        <span class="px-2 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                            <i class="fa-solid fa-circle text-[7px]"></i> Confirmed
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-sm font-bold text-brand-dark dark:text-white px-2">
                    Appointment Records
                    <?php if ($status_filter !== 'all'): ?>
                        <span class="text-brand-muted dark:text-gray-400 font-medium"> &mdash; Filtered by: <span class="text-brand-pink capitalize"><?php echo $status_filter; ?></span></span>
                    <?php endif; ?>
                </div>
                <div class="flex gap-2">
                    <a href="?status=all" class="px-3 py-1.5 text-[10px] font-bold rounded-lg transition <?= $status_filter === 'all' ? 'bg-brand-pink text-white' : 'bg-slate-100 dark:bg-gray-800 text-brand-muted hover:text-brand-dark dark:text-gray-400' ?>">All</a>
                    <a href="?status=pending" class="px-3 py-1.5 text-[10px] font-bold rounded-lg transition <?= $status_filter === 'pending' ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-gray-800 text-brand-muted hover:text-brand-dark dark:text-gray-400' ?>">Pending</a>
                    <a href="?status=confirmed" class="px-3 py-1.5 text-[10px] font-bold rounded-lg transition <?= $status_filter === 'confirmed' ? 'bg-emerald-500 text-white' : 'bg-slate-100 dark:bg-gray-800 text-brand-muted hover:text-brand-dark dark:text-gray-400' ?>">Confirmed</a>
                    <a href="?status=cancelled" class="px-3 py-1.5 text-[10px] font-bold rounded-lg transition <?= $status_filter === 'cancelled' ? 'bg-red-500 text-white' : 'bg-slate-100 dark:bg-gray-800 text-brand-muted hover:text-brand-dark dark:text-gray-400' ?>">Cancelled</a>
                </div>
            </div>

            <!-- Appointments Table -->
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-slate-200/60 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-gray-950 border-b border-slate-200/50 dark:border-gray-800 text-[11px] font-bold uppercase tracking-wider text-brand-muted dark:text-gray-400">
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Patient</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Treatment</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Date</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Time</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Duration</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-center">Status</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Booked On</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800 text-xs font-semibold text-brand-dark dark:text-gray-300">
                            <?php if (empty($appointments)): ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <div class="text-brand-muted dark:text-gray-400">
                                            <i class="fa-regular fa-calendar-xmark text-3xl mb-3 block"></i>
                                            <span class="font-bold text-sm">No appointments found</span>
                                            <p class="text-[11px] font-medium mt-1">No appointments match the current filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($appointments as $apt): ?>
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-gray-800/60 transition-colors group">
                                        <td class="py-3 px-3 sm:py-4 sm:px-6">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-brand-lightPink dark:bg-pink-900/20 rounded-xl flex items-center justify-center text-brand-pink text-xs font-bold shrink-0">
                                                    <?php echo strtoupper(substr($apt['patient_name'], 0, 2)); ?>
                                                </div>
                                                <div>
                                                    <span class="font-bold text-brand-dark dark:text-white block group-hover:text-brand-pink transition-colors"><?php echo htmlspecialchars($apt['patient_name']); ?></span>
                                                    <?php if (!empty($apt['patient_phone'])): ?>
                                                        <span class="text-[10px] text-brand-muted dark:text-gray-400 block font-medium"><?php echo htmlspecialchars($apt['patient_phone']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 sm:py-4 sm:px-6">
                                            <div>
                                                <span class="font-bold text-brand-dark dark:text-white block"><?php echo htmlspecialchars($apt['treatment_name']); ?></span>
                                                <span class="text-[10px] text-brand-muted dark:text-gray-400 block font-medium">$<?php echo number_format($apt['price'], 2); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 sm:py-4 sm:px-6">
                                            <span class="font-bold text-brand-dark dark:text-white">
                                                <?php echo date('M d, Y', strtotime($apt['available_date'])); ?>
                                            </span>
                                            <span class="text-[10px] text-brand-muted dark:text-gray-400 block font-medium">
                                                <?php echo date('l', strtotime($apt['available_date'])); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 sm:py-4 sm:px-6">
                                            <span class="font-bold text-brand-dark dark:text-white">
                                                <?php echo format_time_12($apt['appointment_start']); ?>
                                            </span>
                                            <span class="text-[10px] text-brand-muted dark:text-gray-400 block font-medium">
                                                to <?php echo format_time_12($apt['appointment_end']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 sm:py-4 sm:px-6">
                                            <span class="px-2 py-1 bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 rounded-lg text-[10px] font-bold">
                                                <?php echo $apt['duration']; ?> min
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 sm:py-4 sm:px-6 text-center">
                                            <?php if ($apt['status'] === 'pending'): ?>
                                                <span class="px-2 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-circle text-[7px]"></i> Pending
                                                </span>
                                            <?php elseif ($apt['status'] === 'confirmed'): ?>
                                                <span class="px-2 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-circle text-[7px]"></i> Confirmed
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 bg-red-50 text-red-600 border border-red-100 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-circle text-[7px]"></i> Cancelled
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 sm:py-4 sm:px-6">
                                            <span class="text-slate-500 dark:text-gray-400 font-medium">
                                                <?php echo date('M d, Y', strtotime($apt['created_at'])); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="bg-slate-50/50 dark:bg-gray-950 px-6 py-4 border-t border-slate-100 dark:border-gray-800 flex items-center justify-between text-xs text-brand-muted dark:text-gray-400 font-semibold">
                    <span>Showing <?php echo $total_count; ?> appointment<?php echo $total_count !== 1 ? 's' : ''; ?></span>
                </div>
            </div>

        </main>
    </div>

</body>

</html>