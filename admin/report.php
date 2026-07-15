<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_token']) || $_SESSION['admin_token'] !== 'authenticated_success_token') {
    header('Location: login.php');
    exit;
}

include_once '../config/db.php';

$admin = $conn->query("SELECT username, photo FROM admins ORDER BY id ASC LIMIT 1")->fetch_assoc();
$admin_photo = $admin['photo'] ?? '';
$admin_username = $admin['username'] ?? 'Admin';

// Active tab
$active_tab = $_GET['tab'] ?? 'appointment';
if (!in_array($active_tab, ['appointment', 'revenue', 'treatment'])) {
    $active_tab = 'appointment';
}

// Filter defaults
$filter_from = $_GET['from'] ?? date('Y-m-01');
$filter_to = $_GET['to'] ?? date('Y-m-d');
$filter_doctor = $_GET['doctor'] ?? '';
$filter_treatment = $_GET['treatment'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_payment = $_GET['payment'] ?? '';

// Fetch dropdown data
$doctors_list = [];
$d_res = $conn->query("SELECT id, name FROM doctors WHERE status='active' ORDER BY name");
if ($d_res) while ($r = $d_res->fetch_assoc()) $doctors_list[] = $r;

$treatments_list = [];
$t_res = $conn->query("SELECT id, treatment_name FROM treatments ORDER BY treatment_name");
if ($t_res) while ($r = $t_res->fetch_assoc()) $treatments_list[] = $r;

$payments_list = [];
$p_res = $conn->query("SELECT id, method_name FROM payment_methods ORDER BY method_name");
if ($p_res) while ($r = $p_res->fetch_assoc()) $payments_list[] = $r;

// ===================== APPOINTMENT REPORT =====================
$apt_where = "WHERE DATE(a.created_at) BETWEEN ? AND ?";
$apt_params = [$filter_from, $filter_to];
$apt_types = "ss";

if ($filter_doctor !== '') {
    $apt_where .= " AND s.doctor_id = ?";
    $apt_params[] = $filter_doctor;
    $apt_types .= "i";
}
if ($filter_treatment !== '') {
    $apt_where .= " AND a.treatment_id = ?";
    $apt_params[] = $filter_treatment;
    $apt_types .= "i";
}
if ($filter_status !== '') {
    $apt_where .= " AND a.status = ?";
    $apt_params[] = $filter_status;
    $apt_types .= "s";
}

// Appointment counts by status
$apt_counts = ['total' => 0, 'pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
$stmt = $conn->prepare("SELECT a.status, COUNT(*) AS cnt FROM appointments a JOIN schedules s ON s.id = a.schedule_id $apt_where GROUP BY a.status");
$stmt->bind_param($apt_types, ...$apt_params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $apt_counts['total'] += $row['cnt'];
    if (isset($apt_counts[$row['status']])) $apt_counts[$row['status']] = $row['cnt'];
}
$stmt->close();

// Appointments by doctor
$apt_by_doctor = [];
$stmt = $conn->prepare("SELECT d.name AS doctor_name, COUNT(*) AS cnt FROM appointments a JOIN schedules s ON s.id = a.schedule_id JOIN doctors d ON d.id = s.doctor_id $apt_where GROUP BY s.doctor_id ORDER BY cnt DESC");
$stmt->bind_param($apt_types, ...$apt_params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $apt_by_doctor[] = $row;
$stmt->close();

// Appointments by day (line chart)
$apt_by_day = [];
$stmt = $conn->prepare("SELECT DATE(a.created_at) AS day, COUNT(*) AS cnt FROM appointments a JOIN schedules s ON s.id = a.schedule_id $apt_where GROUP BY DATE(a.created_at) ORDER BY day ASC");
$stmt->bind_param($apt_types, ...$apt_params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $apt_by_day[$row['day']] = (int)$row['cnt'];
$stmt->close();

// Appointment list
$apt_list = [];
$stmt = $conn->prepare("SELECT a.id, a.status, a.created_at, u.name AS patient_name, t.treatment_name, d.name AS doctor_name, s.available_date, s.start_time FROM appointments a JOIN users u ON u.id = a.user_id JOIN treatments t ON t.id = a.treatment_id JOIN schedules s ON s.id = a.schedule_id JOIN doctors d ON d.id = s.doctor_id $apt_where ORDER BY a.created_at DESC");
$stmt->bind_param($apt_types, ...$apt_params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $apt_list[] = $row;
$stmt->close();

// ===================== REVENUE REPORT =====================
$rev_where = "WHERE DATE(a.created_at) BETWEEN ? AND ? AND a.status IN ('completed','confirmed')";
$rev_params = [$filter_from, $filter_to];
$rev_types = "ss";

if ($filter_payment !== '') {
    $rev_where .= " AND a.payment_method_id = ?";
    $rev_params[] = $filter_payment;
    $rev_types .= "i";
}

$rev_total = 0;
$rev_avg = 0;
$rev_max = 0;
$stmt = $conn->prepare("SELECT COALESCE(SUM(t.price),0) AS total, COALESCE(AVG(t.price),0) AS avg_p, COALESCE(MAX(t.price),0) AS max_p FROM appointments a JOIN treatments t ON t.id = a.treatment_id $rev_where");
$stmt->bind_param($rev_types, ...$rev_params);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $rev_total = $row['total'];
    $rev_avg = $row['avg_p'];
    $rev_max = $row['max_p'];
}
$stmt->close();

// Revenue by treatment
$rev_by_treatment = [];
$stmt = $conn->prepare("SELECT t.treatment_name, SUM(t.price) AS revenue, COUNT(*) AS cnt FROM appointments a JOIN treatments t ON t.id = a.treatment_id $rev_where GROUP BY a.treatment_id ORDER BY revenue DESC");
$stmt->bind_param($rev_types, ...$rev_params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $rev_by_treatment[] = $row;
$stmt->close();

// Revenue by payment method
$rev_by_payment = [];
$stmt = $conn->prepare("SELECT pm.method_name, SUM(t.price) AS revenue, COUNT(*) AS cnt FROM appointments a JOIN treatments t ON t.id = a.treatment_id JOIN payment_methods pm ON pm.id = a.payment_method_id $rev_where GROUP BY a.payment_method_id ORDER BY revenue DESC");
$stmt->bind_param($rev_types, ...$rev_params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $rev_by_payment[] = $row;
$stmt->close();

// Daily revenue
$rev_by_day = [];
$stmt = $conn->prepare("SELECT DATE(a.created_at) AS day, SUM(t.price) AS revenue, COUNT(*) AS cnt FROM appointments a JOIN treatments t ON t.id = a.treatment_id $rev_where GROUP BY DATE(a.created_at) ORDER BY day ASC");
$stmt->bind_param($rev_types, ...$rev_params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $rev_by_day[] = $row;
$stmt->close();

// ===================== TREATMENT ANALYTICS =====================
$treatment_stats = [];
$res = $conn->query("SELECT t.id, t.treatment_name, t.price, (SELECT COUNT(*) FROM appointments a WHERE a.treatment_id = t.id) AS total_bookings, (SELECT COALESCE(SUM(t2.price),0) FROM appointments a JOIN treatments t2 ON t2.id = a.treatment_id WHERE a.treatment_id = t.id AND a.status IN ('completed','confirmed')) AS total_revenue FROM treatments t ORDER BY total_bookings DESC");
if ($res) while ($row = $res->fetch_assoc()) $treatment_stats[] = $row;

$total_bookings_all = 0;
foreach ($treatment_stats as $ts) $total_bookings_all += $ts['total_bookings'];

$best_treatment = '';
$best_treatment_bookings = 0;
$best_rev_treatment = '';
$best_rev_amount = 0;
foreach ($treatment_stats as $ts) {
    if ($ts['total_bookings'] > $best_treatment_bookings) {
        $best_treatment_bookings = $ts['total_bookings'];
        $best_treatment = $ts['treatment_name'];
    }
    if ($ts['total_revenue'] > $best_rev_amount) {
        $best_rev_amount = $ts['total_revenue'];
        $best_rev_treatment = $ts['treatment_name'];
    }
}

// Helper: build filter query string for tab switching
function build_filter_qs($overrides = [])
{
    $params = array_merge($_GET, $overrides);
    $qs = [];
    foreach (['from', 'to', 'doctor', 'treatment', 'status', 'payment', 'tab'] as $k) {
        if (!empty($params[$k])) $qs[] = $k . '=' . urlencode($params[$k]);
    }
    return implode('&', $qs);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Reports</title>
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

        .tab-active {
            color: #FF6584;
            border-bottom: 2px solid #FF6584;
            font-weight: 700;
        }

        .tab-inactive {
            color: #64748B;
            border-bottom: 2px solid transparent;
        }

        .tab-inactive:hover {
            color: #FF6584;
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

<body class="bg-brand-canvas dark:bg-gray-950 text-slate-700 dark:text-gray-100 min-h-screen flex antialiased">

    <?php include 'sidebar.php'; ?>

    <div class="flex-grow flex flex-col min-w-0 lg:ml-64">

        <header class="h-16 sm:h-20 bg-white dark:bg-gray-900 border-b border-slate-200/60 dark:border-gray-800 flex items-center justify-between px-4 sm:px-8 shrink-0 z-10 sticky top-0">
            <div class="flex items-center space-x-4">
                <div>
            <h2 class="text-xl font-extrabold text-brand-dark dark:text-white tracking-tight">Reports</h2>
            <p class="text-xs text-brand-muted dark:text-gray-400 font-medium">Analytics and insights for your clinic</p>
                </div>
            </div>
    <div class="flex items-center space-x-6">
        <?php include 'header-actions.php'; ?>
        <a href="profile.php" class="flex items-center space-x-3 border-l pl-6 border-slate-200 dark:border-gray-700 hover:opacity-80 transition">
                    <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 bg-brand-lightPink flex items-center justify-center text-brand-pink font-bold text-sm">
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

        <main class="flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6">

            <!-- TAB NAVIGATION -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] px-6">
                <nav class="flex space-x-6 overflow-x-auto text-xs uppercase tracking-wider">
                    <a href="?tab=appointment&<?= build_filter_qs(['tab' => 'appointment']) ?>" class="py-4 whitespace-nowrap transition <?= $active_tab === 'appointment' ? 'tab-active' : 'tab-inactive' ?>">
                        <i class="fa-regular fa-calendar-check mr-1.5"></i>Appointments
                    </a>
                    <a href="?tab=revenue&<?= build_filter_qs(['tab' => 'revenue']) ?>" class="py-4 whitespace-nowrap transition <?= $active_tab === 'revenue' ? 'tab-active' : 'tab-inactive' ?>">
                        <i class="fa-solid fa-coins mr-1.5"></i>Revenue
                    </a>
                    <a href="?tab=treatment&<?= build_filter_qs(['tab' => 'treatment']) ?>" class="py-4 whitespace-nowrap transition <?= $active_tab === 'treatment' ? 'tab-active' : 'tab-inactive' ?>">
                        <i class="fa-solid fa-hand-holding-medical mr-1.5"></i>Treatment Analytics
                    </a>
                </nav>
            </div>

            <!-- ======================== APPOINTMENT REPORT ======================== -->
            <?php if ($active_tab === 'appointment'): ?>

                <!-- Filters -->
                <form method="GET" class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                    <input type="hidden" name="tab" value="appointment">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                        <div>
                            <label class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block mb-1">From</label>
                            <input type="date" name="from" value="<?= htmlspecialchars($filter_from) ?>" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-brand-pink bg-slate-50">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block mb-1">To</label>
                            <input type="date" name="to" value="<?= htmlspecialchars($filter_to) ?>" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-brand-pink bg-slate-50">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block mb-1">Doctor</label>
                            <select name="doctor" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-brand-pink bg-slate-50">
                                <option value="">All Doctors</option>
                                <?php foreach ($doctors_list as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= $filter_doctor == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block mb-1">Treatment</label>
                            <select name="treatment" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-brand-pink bg-slate-50">
                                <option value="">All Treatments</option>
                                <?php foreach ($treatments_list as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $filter_treatment == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['treatment_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block mb-1">Status</label>
                            <select name="status" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-brand-pink bg-slate-50">
                                <option value="">All Statuses</option>
                                <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $filter_status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="completed" <?= $filter_status === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $filter_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-5 py-2 bg-brand-pink text-white text-xs font-bold rounded-xl hover:bg-brand-pinkHover transition"><i class="fa-solid fa-filter mr-1"></i>Filter</button>
                            <a href="?tab=appointment" class="px-4 py-2 bg-slate-100 text-brand-muted text-xs font-bold rounded-xl hover:bg-slate-200 transition">Reset</a>
                        </div>
                    </div>
                </form>

                <!-- Summary Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center space-x-3">
                        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500"><i class="fa-solid fa-list-check text-sm"></i></div>
                        <div><span class="text-[10px] text-brand-muted font-medium block">Total</span><span class="text-xl font-extrabold text-brand-dark"><?= $apt_counts['total'] ?></span></div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500"><i class="fa-regular fa-clock text-sm"></i></div>
                        <div><span class="text-[10px] text-brand-muted font-medium block">Pending</span><span class="text-xl font-extrabold text-brand-dark"><?= $apt_counts['pending'] ?></span></div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center space-x-3">
                        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-500"><i class="fa-solid fa-circle-check text-sm"></i></div>
                        <div><span class="text-[10px] text-brand-muted font-medium block">Confirmed</span><span class="text-xl font-extrabold text-brand-dark"><?= $apt_counts['confirmed'] ?></span></div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center space-x-3">
                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400"><i class="fa-solid fa-flag-checkered text-sm"></i></div>
                        <div><span class="text-[10px] text-brand-muted font-medium block">Completed</span><span class="text-xl font-extrabold text-brand-dark"><?= $apt_counts['completed'] ?></span></div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center space-x-3">
                        <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-500"><i class="fa-solid fa-ban text-sm"></i></div>
                        <div><span class="text-[10px] text-brand-muted font-medium block">Cancelled</span><span class="text-xl font-extrabold text-brand-dark"><?= $apt_counts['cancelled'] ?></span></div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Status Bar Chart -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                        <h3 class="text-sm font-bold text-brand-dark mb-4">Appointments by Status</h3>
                        <?php
                        $status_labels = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
                        $status_values = [$apt_counts['pending'], $apt_counts['confirmed'], $apt_counts['completed'], $apt_counts['cancelled']];
                        $status_colors = ['#3B82F6', '#10B981', '#94A3B8', '#F43F5E'];
                        $bar_max = max(array_merge($status_values, [1]));
                        ?>
                        <div class="space-y-3">
                            <?php for ($i = 0; $i < 4; $i++): ?>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-bold text-brand-muted w-20 text-right"><?= $status_labels[$i] ?></span>
                                    <div class="flex-grow bg-slate-100 rounded-full h-6 overflow-hidden">
                                        <div class="h-full rounded-full flex items-center pl-2" style="width: <?= $bar_max > 0 ? ($status_values[$i] / $bar_max * 100) : 0 ?>%; background-color: <?= $status_colors[$i] ?>; min-width: <?= $status_values[$i] > 0 ? '2rem' : '0' ?>;">
                                            <?php if ($status_values[$i] > 0): ?>
                                                <span class="text-[10px] font-bold text-white"><?= $status_values[$i] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Doctor Bar Chart -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                        <h3 class="text-sm font-bold text-brand-dark mb-4">Appointments by Doctor</h3>
                        <?php $doc_bar_max = max(array_merge(array_column($apt_by_doctor, 'cnt'), [1])); ?>
                        <div class="space-y-3">
                            <?php if (count($apt_by_doctor) > 0): ?>
                                <?php foreach ($apt_by_doctor as $doc): ?>
                                    <div class="flex items-center gap-3">
                                        <span class="text-[10px] font-bold text-brand-muted w-24 text-right truncate" title="<?= htmlspecialchars($doc['doctor_name']) ?>"><?= htmlspecialchars($doc['doctor_name']) ?></span>
                                        <div class="flex-grow bg-slate-100 rounded-full h-6 overflow-hidden">
                                            <div class="h-full rounded-full bg-brand-pink flex items-center pl-2" style="width: <?= $doc_bar_max > 0 ? ($doc['cnt'] / $doc_bar_max * 100) : 0 ?>%; min-width: <?= $doc['cnt'] > 0 ? '2rem' : '0' ?>;">
                                                <?php if ($doc['cnt'] > 0): ?>
                                                    <span class="text-[10px] font-bold text-white"><?= $doc['cnt'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-xs text-brand-muted text-center py-6">No data for selected filters.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Daily Trend Line Chart -->
                <?php if (count($apt_by_day) > 0): ?>
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                        <h3 class="text-sm font-bold text-brand-dark mb-4">Daily Appointment Trend</h3>
                        <?php
                        $days = array_keys($apt_by_day);
                        $vals = array_values($apt_by_day);
                        $cnt = count($vals);
                        $svg_w = 600;
                        $svg_h = 160;
                        $max_v = max(array_merge($vals, [1]));
                        $x_step = $cnt > 1 ? $svg_w / ($cnt - 1) : $svg_w;
                        $points = [];
                        foreach ($vals as $i => $v) {
                            $x = $i * $x_step;
                            $y = $svg_h - ($max_v > 0 ? ($v / $max_v * ($svg_h - 20)) : 0) - 10;
                            $points[] = "$x,$y";
                        }
                        $line = implode(' ', array_map(fn($p) => "L$p", $points));
                        $line = substr($line, 1);
                        $area = "M$line L" . ($cnt - 1) * $x_step . "," . $svg_h . " L0,$svg_h Z";
                        ?>
                        <div class="relative w-full h-48">
                            <svg viewBox="0 0 <?= $svg_w ?> <?= $svg_h ?>" class="w-full h-full">
                                <defs>
                                    <linearGradient id="aptGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#FF6584" stop-opacity="0.15" />
                                        <stop offset="100%" stop-color="#FF6584" stop-opacity="0" />
                                    </linearGradient>
                                </defs>
                                <path d="M<?= $area ?>" fill="url(#aptGrad)" />
                                <path d="M<?= $line ?>" fill="none" stroke="#FF6584" stroke-width="2.5" />
                                <?php foreach ($points as $i => $p): ?>
                                    <circle cx="<?= explode(',', $p)[0] ?>" cy="<?= explode(',', $p)[1] ?>" r="3" fill="#FF6584" stroke="white" stroke-width="1.5" />
                                <?php endforeach; ?>
                            </svg>
                            <div class="flex justify-between mt-1">
                                <?php
                                $label_step = $cnt > 8 ? ceil($cnt / 8) : 1;
                                for ($i = 0; $i < $cnt; $i += $label_step):
                                ?>
                                    <span class="text-[9px] text-brand-muted font-semibold"><?= date('d M', strtotime($days[$i])) ?></span>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Appointment Table -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-brand-dark">Appointment Details</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-200/50 text-[11px] font-bold uppercase tracking-wider text-brand-muted">
                                    <th class="py-3 px-6">#</th>
                                    <th class="py-3 px-6">Patient</th>
                                    <th class="py-3 px-6">Treatment</th>
                                    <th class="py-3 px-6">Doctor</th>
                                    <th class="py-3 px-6">Date</th>
                                    <th class="py-3 px-6">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs font-semibold text-brand-dark">
                                <?php if (count($apt_list) > 0): ?>
                                    <?php foreach ($apt_list as $i => $a):
                                        $sc = match ($a['status']) {
                                            'confirmed' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                            'pending' => 'text-blue-600 bg-blue-50 border-blue-100',
                                            'cancelled' => 'text-rose-600 bg-rose-50 border-rose-100',
                                            'completed' => 'text-slate-600 bg-slate-50 border-slate-100',
                                            default => 'text-slate-600 bg-slate-50 border-slate-100'
                                        };
                                    ?>
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="py-3 px-6 text-brand-muted"><?= $i + 1 ?></td>
                                            <td class="py-3 px-6 font-bold"><?= htmlspecialchars($a['patient_name']) ?></td>
                                            <td class="py-3 px-6"><?= htmlspecialchars($a['treatment_name']) ?></td>
                                            <td class="py-3 px-6"><i class="fa-solid fa-user-doctor text-brand-muted text-[10px] mr-1"></i><?= htmlspecialchars($a['doctor_name']) ?></td>
                                            <td class="py-3 px-6">
                                                <span class="font-bold"><?= date('d M Y', strtotime($a['available_date'])) ?></span>
                                                <span class="text-[10px] text-brand-muted block"><?= date('h:i A', strtotime($a['start_time'])) ?></span>
                                            </td>
                                            <td class="py-3 px-6"><span class="text-[10px] font-bold <?= $sc ?> px-2 py-0.5 rounded-lg border"><?= ucfirst($a['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-brand-muted">No appointments found for the selected filters.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-slate-50/50 px-6 py-3 border-t border-slate-100 text-xs text-brand-muted font-semibold">
                        Showing <?= count($apt_list) ?> <?= count($apt_list) === 1 ? 'record' : 'records' ?>
                    </div>
                </div>

            <?php endif; ?>

            <!-- ======================== REVENUE REPORT ======================== -->
            <?php if ($active_tab === 'revenue'): ?>

                <!-- Filters -->
                <form method="GET" class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                    <input type="hidden" name="tab" value="revenue">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block mb-1">From</label>
                            <input type="date" name="from" value="<?= htmlspecialchars($filter_from) ?>" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-brand-pink bg-slate-50">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block mb-1">To</label>
                            <input type="date" name="to" value="<?= htmlspecialchars($filter_to) ?>" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-brand-pink bg-slate-50">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block mb-1">Payment Method</label>
                            <select name="payment" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-brand-pink bg-slate-50">
                                <option value="">All Methods</option>
                                <?php foreach ($payments_list as $pm): ?>
                                    <option value="<?= $pm['id'] ?>" <?= $filter_payment == $pm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pm['method_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-5 py-2 bg-brand-pink text-white text-xs font-bold rounded-xl hover:bg-brand-pinkHover transition"><i class="fa-solid fa-filter mr-1"></i>Filter</button>
                            <a href="?tab=revenue" class="px-4 py-2 bg-slate-100 text-brand-muted text-xs font-bold rounded-xl hover:bg-slate-200 transition">Reset</a>
                        </div>
                    </div>
                </form>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                        <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-500 text-lg"><i class="fa-solid fa-sack-dollar"></i></div>
                        <div>
                            <span class="text-[11px] font-medium text-brand-muted block">Total Revenue</span>
                            <span class="text-2xl font-extrabold text-brand-dark"><?= number_format($rev_total, 0) ?> <span class="text-xs font-medium text-brand-muted">MMK</span></span>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500 text-lg"><i class="fa-solid fa-chart-line"></i></div>
                        <div>
                            <span class="text-[11px] font-medium text-brand-muted block">Average per Booking</span>
                            <span class="text-2xl font-extrabold text-brand-dark"><?= number_format($rev_avg, 0) ?> <span class="text-xs font-medium text-brand-muted">MMK</span></span>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                        <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 text-lg"><i class="fa-solid fa-arrow-trend-up"></i></div>
                        <div>
                            <span class="text-[11px] font-medium text-brand-muted block">Highest Payment</span>
                            <span class="text-2xl font-extrabold text-brand-dark"><?= number_format($rev_max, 0) ?> <span class="text-xs font-medium text-brand-muted">MMK</span></span>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Revenue by Treatment -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                        <h3 class="text-sm font-bold text-brand-dark mb-4">Revenue by Treatment</h3>
                        <?php $rev_t_max = max(array_merge(array_column($rev_by_treatment, 'revenue'), [1])); ?>
                        <div class="space-y-3">
                            <?php if (count($rev_by_treatment) > 0): ?>
                                <?php foreach ($rev_by_treatment as $rt): ?>
                                    <div class="flex items-center gap-3">
                                        <span class="text-[10px] font-bold text-brand-muted w-28 text-right truncate" title="<?= htmlspecialchars($rt['treatment_name']) ?>"><?= htmlspecialchars($rt['treatment_name']) ?></span>
                                        <div class="flex-grow bg-slate-100 rounded-full h-6 overflow-hidden">
                                            <div class="h-full rounded-full bg-emerald-500 flex items-center pl-2" style="width: <?= $rev_t_max > 0 ? ($rt['revenue'] / $rev_t_max * 100) : 0 ?>%; min-width: <?= $rt['revenue'] > 0 ? '3.5rem' : '0' ?>;">
                                                <?php if ($rt['revenue'] > 0): ?>
                                                    <span class="text-[10px] font-bold text-white"><?= number_format($rt['revenue'], 0) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-xs text-brand-muted text-center py-6">No revenue data for selected filters.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Revenue by Payment Method (Donut) -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                        <h3 class="text-sm font-bold text-brand-dark mb-4">Revenue by Payment Method</h3>
                        <?php
                        $donut_colors = ['#FF6584', '#A855F7', '#F59E0B', '#10B981', '#3B82F6'];
                        $rev_pm_total = array_sum(array_column($rev_by_payment, 'revenue'));
                        ?>
                        <?php if (count($rev_by_payment) > 0): ?>
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                                <div class="relative w-36 h-36 shrink-0">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                        <circle cx="18" cy="18" r="15.915" fill="none" stroke="#E2E8F0" stroke-width="3.5"></circle>
                                        <?php $offset = 0; ?>
                                        <?php foreach ($rev_by_payment as $i => $rp):
                                            $pct = $rev_pm_total > 0 ? round($rp['revenue'] / $rev_pm_total * 100) : 0;
                                        ?>
                                            <circle cx="18" cy="18" r="15.915" fill="none" stroke="<?= $donut_colors[$i % 5] ?>" stroke-width="3.5" stroke-dasharray="<?= $pct ?> 100" stroke-dashoffset="-<?= $offset ?>"></circle>
                                            <?php $offset += $pct; ?>
                                        <?php endforeach; ?>
                                    </svg>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                        <span class="text-[9px] text-brand-muted font-medium block">Total</span>
                                        <span class="text-sm font-extrabold text-brand-dark"><?= number_format($rev_pm_total, 0) ?></span>
                                        <span class="text-[8px] text-brand-muted">MMK</span>
                                    </div>
                                </div>
                                <div class="w-full space-y-2 text-xs">
                                    <?php foreach ($rev_by_payment as $i => $rp):
                                        $pct = $rev_pm_total > 0 ? round($rp['revenue'] / $rev_pm_total * 100) : 0;
                                    ?>
                                        <div class="flex items-center justify-between font-medium">
                                            <span class="flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: <?= $donut_colors[$i % 5] ?>"></span>
                                                <?= htmlspecialchars($rp['method_name']) ?>
                                            </span>
                                            <span class="text-brand-muted"><?= $pct ?>%</span>
                                            <span class="font-bold text-brand-dark"><?= number_format($rp['revenue'], 0) ?> MMK</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-xs text-brand-muted text-center py-6">No payment data available.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Daily Revenue Table -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-brand-dark">Daily Revenue Breakdown</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-200/50 text-[11px] font-bold uppercase tracking-wider text-brand-muted">
                                    <th class="py-3 px-6">#</th>
                                    <th class="py-3 px-6">Date</th>
                                    <th class="py-3 px-6">Bookings</th>
                                    <th class="py-3 px-6 text-right">Revenue (MMK)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs font-semibold text-brand-dark">
                                <?php if (count($rev_by_day) > 0): ?>
                                    <?php foreach ($rev_by_day as $i => $rd): ?>
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="py-3 px-6 text-brand-muted"><?= $i + 1 ?></td>
                                            <td class="py-3 px-6 font-bold"><?= date('d M Y', strtotime($rd['day'])) ?></td>
                                            <td class="py-3 px-6"><?= $rd['cnt'] ?> booking<?= $rd['cnt'] != 1 ? 's' : '' ?></td>
                                            <td class="py-3 px-6 text-right font-extrabold text-emerald-600"><?= number_format($rd['revenue'], 0) ?> MMK</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-brand-muted">No revenue data for the selected period.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-slate-50/50 px-6 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-brand-muted font-semibold">
                        <span>Showing <?= count($rev_by_day) ?> day<?= count($rev_by_day) != 1 ? 's' : '' ?></span>
                        <span class="font-extrabold text-brand-dark">Total: <?= number_format($rev_total, 0) ?> MMK</span>
                    </div>
                </div>

            <?php endif; ?>

            <!-- ======================== TREATMENT ANALYTICS ======================== -->
            <?php if ($active_tab === 'treatment'): ?>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                        <div class="w-12 h-12 bg-brand-lightPink rounded-xl flex items-center justify-center text-brand-pink text-lg"><i class="fa-solid fa-fire"></i></div>
                        <div>
                            <span class="text-[11px] font-medium text-brand-muted block">Most Popular Treatment</span>
                            <span class="text-lg font-extrabold text-brand-dark"><?= $best_treatment ? htmlspecialchars($best_treatment) : 'N/A' ?></span>
                            <span class="text-[10px] text-brand-muted block"><?= $best_treatment_bookings ?> booking<?= $best_treatment_bookings != 1 ? 's' : '' ?></span>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                        <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-500 text-lg"><i class="fa-solid fa-coins"></i></div>
                        <div>
                            <span class="text-[11px] font-medium text-brand-muted block">Highest Revenue Treatment</span>
                            <span class="text-lg font-extrabold text-brand-dark"><?= $best_rev_treatment ? htmlspecialchars($best_rev_treatment) : 'N/A' ?></span>
                            <span class="text-[10px] text-brand-muted block"><?= number_format($best_rev_amount, 0) ?> MMK</span>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500 text-lg"><i class="fa-solid fa-chart-simple"></i></div>
                        <div>
                            <span class="text-[11px] font-medium text-brand-muted block">Total Bookings</span>
                            <span class="text-2xl font-extrabold text-brand-dark"><?= $total_bookings_all ?></span>
                        </div>
                    </div>
                </div>

                <!-- Bookings per Treatment Chart -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                    <h3 class="text-sm font-bold text-brand-dark mb-4">Bookings per Treatment</h3>
                    <?php $treatment_bar_max = max(array_merge(array_column($treatment_stats, 'total_bookings'), [1])); ?>
                    <div class="space-y-3">
                        <?php if (count($treatment_stats) > 0): ?>
                            <?php foreach ($treatment_stats as $ts): ?>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-bold text-brand-muted w-32 text-right truncate" title="<?= htmlspecialchars($ts['treatment_name']) ?>"><?= htmlspecialchars($ts['treatment_name']) ?></span>
                                    <div class="flex-grow bg-slate-100 rounded-full h-6 overflow-hidden">
                                        <div class="h-full rounded-full bg-brand-pink flex items-center pl-2" style="width: <?= $treatment_bar_max > 0 ? ($ts['total_bookings'] / $treatment_bar_max * 100) : 0 ?>%; min-width: <?= $ts['total_bookings'] > 0 ? '2rem' : '0' ?>;">
                                            <?php if ($ts['total_bookings'] > 0): ?>
                                                <span class="text-[10px] font-bold text-white"><?= $ts['total_bookings'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-brand-dark w-16 text-right"><?= number_format($ts['total_revenue'], 0) ?> MMK</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-xs text-brand-muted text-center py-6">No treatment data available.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Treatment Detail Table -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-brand-dark">Treatment Analytics Detail</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-200/50 text-[11px] font-bold uppercase tracking-wider text-brand-muted">
                                    <th class="py-3 px-6">#</th>
                                    <th class="py-3 px-6">Treatment</th>
                                    <th class="py-3 px-6 text-right">Price</th>
                                    <th class="py-3 px-6 text-center">Total Bookings</th>
                                    <th class="py-3 px-6 text-center">% of Total</th>
                                    <th class="py-3 px-6 text-right">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs font-semibold text-brand-dark">
                                <?php if (count($treatment_stats) > 0): ?>
                                    <?php foreach ($treatment_stats as $i => $ts):
                                        $pct = $total_bookings_all > 0 ? round($ts['total_bookings'] / $total_bookings_all * 100, 1) : 0;
                                    ?>
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="py-3 px-6 text-brand-muted"><?= $i + 1 ?></td>
                                            <td class="py-3 px-6 font-bold"><?= htmlspecialchars($ts['treatment_name']) ?></td>
                                            <td class="py-3 px-6 text-right text-brand-muted"><?= number_format($ts['price'], 0) ?> MMK</td>
                                            <td class="py-3 px-6 text-center font-extrabold"><?= $ts['total_bookings'] ?></td>
                                            <td class="py-3 px-6 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                        <div class="h-full bg-brand-pink rounded-full" style="width: <?= $pct ?>%"></div>
                                                    </div>
                                                    <span class="text-[10px] font-bold text-brand-muted"><?= $pct ?>%</span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-6 text-right font-extrabold text-emerald-600"><?= number_format($ts['total_revenue'], 0) ?> MMK</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-brand-muted">No treatments found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>

        </main>
    </div>

    <script>
        document.getElementById('mobile-menu-toggle')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
                sidebar.classList.toggle('translate-x-0');
            }
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>