<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth check - redirect to login if not authenticated
if (!isset($_SESSION['admin_token']) || $_SESSION['admin_token'] !== 'authenticated_success_token') {
    header('Location: login.php');
    exit;
}

include_once '../config/db.php';

$admin = $conn->query("SELECT username, photo FROM admins ORDER BY id ASC LIMIT 1")->fetch_assoc();
$admin_photo = $admin['photo'] ?? '';
$admin_username = $admin['username'] ?? 'Admin';

// Stats
$total_appointments = 0;
$total_patients = 0;
$total_doctors = 0;
$total_treatments = 0;

$result = $conn->query("SELECT COUNT(*) AS c FROM appointments");
if ($result) { $row = $result->fetch_assoc(); $total_appointments = $row['c']; }

$result = $conn->query("SELECT COUNT(*) AS c FROM users");
if ($result) { $row = $result->fetch_assoc(); $total_patients = $row['c']; }

$result = $conn->query("SELECT COUNT(*) AS c FROM doctors WHERE status='active'");
if ($result) { $row = $result->fetch_assoc(); $total_doctors = $row['c']; }

$result = $conn->query("SELECT COUNT(*) AS c FROM treatments");
if ($result) { $row = $result->fetch_assoc(); $total_treatments = $row['c']; }

$total_rooms = 0;
$result = $conn->query("SELECT COUNT(*) AS c FROM rooms WHERE status = 'active'");
if ($result) { $row = $result->fetch_assoc(); $total_rooms = $row['c']; }

// Total revenue (sum of treatment prices for completed/confirmed appointments)
$total_revenue = 0;
$rev_result = $conn->query("SELECT COALESCE(SUM(t.price), 0) AS total FROM appointments a JOIN treatments t ON t.id = a.treatment_id WHERE a.status IN ('completed','confirmed')");
if ($rev_result) { $row = $rev_result->fetch_assoc(); $total_revenue = $row['total']; }

// Unread notifications count
$unread_notif_count = 0;
$nc = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE is_read = 0 AND target_role = 'admin'");
if ($nc && $ncr = $nc->fetch_assoc()) {
    $unread_notif_count = $ncr['c'];
}

// Recent appointments
$recent_appointments = [];
$r_query = "SELECT 
            a.id, a.status, a.created_at,
            u.name AS patient_name,
            t.treatment_name,
            s.available_date,
            s.start_time,
            d.name AS doctor_name
            FROM appointments a
            JOIN users u ON u.id = a.user_id
            JOIN treatments t ON t.id = a.treatment_id
            JOIN schedules s ON s.id = a.schedule_id
            JOIN doctors d ON d.id = s.doctor_id
            ORDER BY a.created_at DESC
            LIMIT 5";
if ($r_result = $conn->query($r_query)) {
    while ($row = $r_result->fetch_assoc()) {
        $recent_appointments[] = $row;
    }
}

// Appointments overview (daily counts for current month)
$chart_labels = [];
$chart_data = [];
$chart_max = 10;
$c_query = "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
            FROM appointments
            WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
            GROUP BY DATE(created_at)
            ORDER BY day ASC";
if ($c_result = $conn->query($c_query)) {
    $chart_raw = [];
    while ($row = $c_result->fetch_assoc()) {
        $chart_raw[$row['day']] = (int)$row['cnt'];
    }
    $days_in_month = (int)date('t');
    $year = date('Y');
    $month = date('m');
    for ($d = 1; $d <= $days_in_month; $d++) {
        $date = sprintf('%s-%s-%02d', $year, $month, $d);
        $label = date('j M', strtotime($date));
        $chart_labels[] = $label;
        $val = $chart_raw[$date] ?? 0;
        $chart_data[] = $val;
        if ($val > $chart_max) $chart_max = $val;
    }
}
$chart_max = max($chart_max, 5);
$chart_step = max(1, ceil($chart_max / 5));
$chart_ticks = [];
for ($t = 0; $t <= 5; $t++) {
    $chart_ticks[] = $t * $chart_step;
}

// Top treatments
$top_treatments = [];
$tt_query = "SELECT t.treatment_name, COUNT(*) AS cnt
             FROM appointments a
             JOIN treatments t ON t.id = a.treatment_id
             GROUP BY a.treatment_id
             ORDER BY cnt DESC
             LIMIT 5";
$tt_total = 0;
if ($tt_result = $conn->query($tt_query)) {
    while ($row = $tt_result->fetch_assoc()) {
        $top_treatments[] = $row;
        $tt_total += $row['cnt'];
    }
}
// Fetch the 3 most recent messages from the database
$recent_messages = [];
$msg_query = "SELECT name, message_text, status, created_at FROM messages ORDER BY created_at DESC LIMIT 3";
if ($msg_result = $conn->query($msg_query)) {
    while ($row = $msg_result->fetch_assoc()) {
        $recent_messages[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Admin Dashboard</title>
    <!-- Tailwind CSS & FontAwesome Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            pink: '#FF6584',
                            lightPink: '#FFF0F2',
                            dark: '#2D3748',
                            bgGray: '#F8FAFC'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
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
  <body class="bg-brand-bgGray dark:bg-gray-950 text-slate-700 dark:text-gray-100 min-h-screen flex overflow-x-hidden">

    <?php include 'sidebar.php'; ?>
    
    <!-- CONTENT WRAPPER -->
    <div class="flex-grow flex flex-col min-w-0 lg:ml-64">
        
        <!-- TOP HEADER CONTEXT BAR -->
        <header class="h-16 sm:h-20 bg-white dark:bg-gray-900 border-b border-slate-100 dark:border-gray-800 flex items-center justify-between px-4 sm:px-8 shrink-0 sticky top-0 z-50">
            <div class="flex items-center space-x-3 sm:space-x-4">
                
                <div>
            <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white">Dashboard</h2>
            <p class="text-[10px] sm:text-xs text-slate-400 dark:text-gray-400 font-medium">Welcome back, Admin!</p>
                </div>
            </div>

    <!-- Global Action Controls -->
    <div class="flex items-center space-x-3 sm:space-x-6">
        
        <?php include 'header-actions.php'; ?>

        <!-- Profile Badge -->
                <a href="profile.php" class="flex items-center space-x-3 border-l pl-6 border-slate-100 dark:border-gray-700 hover:opacity-80 transition">
                    <div class="w-9 h-9 rounded-full overflow-hidden bg-brand-lightPink flex items-center justify-center text-brand-pink text-xs font-bold">
                        <?php if ($admin_photo): ?>
                            <img src="../<?php echo htmlspecialchars($admin_photo); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-800 dark:text-white block"><?php echo htmlspecialchars($admin_username); ?></span>
                        <!-- <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i> -->
                    </div>
                </a>
            </div>
        </header>

        <!-- DASHBOARD CONTAINER MAIN PLATFORM -->
        <main class="bg-slate-100 dark:bg-gray-950 flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-4 sm:space-y-6">
            
            <!-- GRID LAYER 1: METRIC CARD SYSTEM -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6">
                <!-- Total Appointments -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-slate-100 dark:border-gray-800 shadow-md hover:shadow-xl flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-pink-50 rounded-xl flex items-center justify-center text-brand-pink text-lg">
                            <i class="fa-regular fa-calendar-check"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Total Appointments</span>
                            <span class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight"><?= $total_appointments ?></span>
                            <span class="text-[10px] font-medium text-slate-400 block mt-0.5">All Time</span>
                        </div>
                    </div>
                    <!-- <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50/60 px-2 py-0.5 rounded-md flex items-center gap-1">
                        <i class="fa-solid fa-arrow-up text-[8px]"></i> 12%
                    </span> -->
                </div>

                <!-- Total Patients -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-slate-100 dark:border-gray-800 shadow-md hover:shadow-xl flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500 text-lg">
                            <i class="fa-solid fa-user-group"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Total Patients</span>
                            <span class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight"><?= $total_patients ?></span>
                            <span class="text-[10px] font-medium text-slate-400 block mt-0.5">Registered Users</span>
                        </div>
                    </div>
                    <!-- <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50/60 px-2 py-0.5 rounded-md flex items-center gap-1">
                        <i class="fa-solid fa-arrow-up text-[8px]"></i> 8%
                    </span> -->
                </div>

                <!-- Total Doctors -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-slate-100 dark:border-gray-800 shadow-md hover:shadow-xl flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 text-lg">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Total Doctors</span>
                            <span class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight"><?= $total_doctors ?></span>
                            <span class="text-[10px] font-medium text-slate-400 block mt-0.5">Active Doctors</span>
                        </div>
                    </div>
                    <!-- <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50/60 px-2 py-0.5 rounded-md flex items-center gap-1">
                        <i class="fa-solid fa-arrow-up text-[8px]"></i> 5%
                    </span> -->
                </div>

                <!-- Total Treatments -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-slate-100 dark:border-gray-800 shadow-md hover:shadow-xl flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-500 text-lg">
                            <i class="fa-solid fa-mortar-pestle"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Total Treatments</span>
                            <span class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight"><?= $total_treatments ?></span>
                            <span class="text-[10px] font-medium text-slate-400 block mt-0.5">Active Treatments</span>
                        </div>
                    </div>
                </div>

                <!-- Active Rooms -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-slate-100 dark:border-gray-800 shadow-md hover:shadow-xl flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-cyan-50 rounded-xl flex items-center justify-center text-cyan-500 text-lg">
                            <i class="fa-solid fa-door-open"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Active Rooms</span>
                            <span class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight"><?= $total_rooms ?></span>
                            <span class="text-[10px] font-medium text-slate-400 block mt-0.5">Treatment Rooms</span>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-slate-100 dark:border-gray-800 shadow-md hover:shadow-xl flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-violet-50 rounded-xl flex items-center justify-center text-violet-500 text-lg">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Total Revenue</span>
                            <span class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight"><?= number_format($total_revenue, 0) ?> <span class="text-xs font-medium text-slate-400">MMK</span></span>
                            <!-- <span class="text-[10px] font-medium text-slate-400 block mt-0.5">Completed & Confirmed</span> -->
                        </div>
                    </div>
                    <!-- <span class="text-[10px] font-bold text-violet-500 bg-violet-50/60 px-2 py-0.5 rounded-md flex items-center gap-1">
                        <i class="fa-solid fa-wallet text-[8px]"></i> Revenue
                    </span> -->
                </div>
            </div>

            <!-- GRID LAYER 2: CHARTS & LOGICAL DATA ENTRIES -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Appointments Overview Container (Left) -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-100 dark:border-gray-800 lg:col-span-7 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Appointments Overview</h3>
                        <div class="relative">
                            <select class="appearance-none bg-slate-50 dark:bg-gray-800 border border-slate-100 dark:border-gray-700 text-[10px] font-medium text-slate-500 dark:text-gray-300 px-3 py-1 pr-6 rounded-lg focus:outline-none">
                                <option>This Month</option>
                            </select>
                            <i class="fa-solid fa-chevron-down text-[8px] text-slate-400 dark:text-gray-500 absolute right-2.5 top-2.5 pointer-events-none"></i>
                        </div>
                    </div>
                    
                    <!-- Dynamic Chart -->
                    <?php
                    $data_count = count($chart_data);
                    $svg_w = 500;
                    $svg_h = 150;
                    $pad = 0;
                    $plot_w = $svg_w - $pad * 2;
                    $plot_h = $svg_h - $pad * 2;
                    $max_val = $chart_max;
                    $points = [];
                    $x_interval = $data_count > 1 ? $plot_w / ($data_count - 1) : $plot_w;
                    foreach ($chart_data as $i => $val) {
                        $x = $i * $x_interval + $pad;
                        $y = $svg_h - ($max_val > 0 ? ($val / $max_val * $plot_h) : 0) - $pad;
                        $points[] = "$x,$y";
                    }
                    $line_path = implode(' ', array_map(function($p) { return "L$p"; }, $points));
                    $line_path = substr($line_path, 1); // remove first L
                    $area_path = "M$line_path L" . ($data_count - 1) * $x_interval + $pad . "," . ($svg_h - $pad) . " L$pad," . ($svg_h - $pad) . " Z";

                    // Find max index for tooltip
                    $max_idx = array_keys($chart_data, max($chart_data))[0] ?? 0;
                    $max_x = $max_idx * $x_interval + $pad;
                    $max_y = $svg_h - ($max_val > 0 ? ($chart_data[$max_idx] / $max_val * $plot_h) : 0) - $pad;
                    $max_label = $chart_labels[$max_idx] ?? '';
                    $max_val_display = $chart_data[$max_idx] ?? 0;
                    ?>
                    <div class="relative w-full h-56 mt-4">
                        <div class="absolute left-0 top-0 h-full w-6 flex flex-col justify-between text-[10px] text-slate-300 dark:text-gray-600 font-medium">
                            <?php foreach (array_reverse($chart_ticks) as $tick): ?>
                            <span><?= $tick ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="ml-8 h-full relative border-b border-l border-slate-100/50 dark:border-gray-800/50">
                            <?php for ($g = 1; $g <= 5; $g++): ?>
                            <div class="absolute inset-x-0 top-<?= ($g - 1) * 20 ?>-full h-[1px] border-t border-dashed border-slate-100 dark:border-gray-800" style="top: <?= ($g - 1) * 20 ?>%"></div>
                            <?php endfor; ?>
                            <svg viewBox="0 0 <?= $svg_w ?> <?= $svg_h ?>" class="w-full h-full overflow-visible absolute bottom-0">
                                <defs>
                                    <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#FF6584" stop-opacity="0.15"/>
                                        <stop offset="100%" stop-color="#FF6584" stop-opacity="0.00"/>
                                    </linearGradient>
                                </defs>
                                <path d="M<?= $area_path ?>" fill="url(#chartGrad)" />
                                <path d="M<?= $line_path ?>" fill="none" stroke="#FF6584" stroke-width="2.5" />
                                <circle cx="<?= $max_x ?>" cy="<?= $max_y ?>" r="4" fill="#FF6584" stroke="white" stroke-width="2" />
                            </svg>
                            <div class="absolute top-[<?= $max_y / $svg_h * 100 ?>%] left-[<?= $max_x / $plot_w * 100 ?>%] -translate-x-1/2 -translate-y-full bg-brand-pink text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md shadow-xs after:content-[''] after:absolute after:top-full after:left-1/2 after:-translate-x-1/2 after:border-4 after:border-transparent after:border-t-brand-pink">
                                <?= $max_val_display ?>
                            </div>
                        </div>
                        <div class="ml-8 mt-2 flex justify-between text-[9px] text-slate-400 dark:text-gray-500 font-semibold tracking-wide">
                            <?php
                            $label_count = count($chart_labels);
                            $step = $label_count > 7 ? floor($label_count / 7) : 1;
                            for ($i = 0; $i < $label_count; $i += $step):
                            ?>
                            <span><?= $chart_labels[$i] ?></span>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Appointments Log Section (Right) -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-100 dark:border-gray-800 lg:col-span-5 flex flex-col justify-between">
                    <div class="flex items-center justify-between border-b pb-3 border-slate-50 dark:border-gray-800">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Recent Appointments</h3>
                        <a href="#" class="text-[10px] font-bold text-brand-pink bg-brand-lightPink dark:bg-gray-800 px-2 py-0.5 rounded-md hover:opacity-90">View All</a>
                    </div>

                    <!-- Log Queue Units -->
                    <div class="divide-y divide-slate-50 dark:divide-gray-800 mt-4 flex-grow space-y-3.5">
                        <?php if (count($recent_appointments) > 0): ?>
                            <?php foreach ($recent_appointments as $app): 
                                $status_class = match($app['status']) {
                                    'confirmed' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/30',
                                    'pending' => 'text-blue-500 bg-blue-50 dark:bg-blue-900/30',
                                    // 'cancelled' => 'text-rose-500 bg-rose-50',
                                    'completed' => 'text-slate-500 bg-slate-50 dark:bg-gray-800',
                                    default => 'text-slate-500 bg-slate-50 dark:bg-gray-800'
                                };
                            ?>
                            <div class="flex items-center justify-between pt-2 first:pt-0">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-gray-800 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-gray-300 shrink-0">
                                        <?= strtoupper(substr($app['patient_name'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-white block"><?= htmlspecialchars($app['patient_name']) ?></span>
                                        <span class="text-[10px] text-slate-400 dark:text-gray-400 block font-medium"><?= htmlspecialchars($app['treatment_name']) ?></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-semibold text-slate-600 dark:text-gray-300 block"><?= date("d M Y", strtotime($app['available_date'])) ?></span>
                                    <span class="text-[9px] text-slate-400 dark:text-gray-500 block"><?= date("h:i A", strtotime($app['start_time'])) ?></span>
                                </div>
                                <span class="text-[9px] font-bold <?= $status_class ?> px-2 py-0.5 rounded-md"><?= ucfirst($app['status']) ?></span>
                                <button class="text-slate-300 dark:text-gray-600 hover:text-slate-500 dark:hover:text-gray-400 text-xs"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-xs text-slate-400 dark:text-gray-500 text-center py-4">No appointments yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- GRID LAYER 3: TOP TREATMENTS & INTERNAL MESSAGES LOG -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Top Treatments Donut Allocation Module (Left) -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-100 dark:border-gray-800 lg:col-span-7">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white mb-6">Top Treatments</h3>
                    
                    <?php
                    $chart_colors = ['#FF6584', '#A855F7', '#F59E0B', '#10B981', '#3B82F6'];
                    $color_classes = ['bg-brand-pink', 'bg-purple-500', 'bg-amber-500', 'bg-emerald-500', 'bg-blue-500'];
                    $dash_offset = 0;
                    ?>
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-8">
                        <!-- Circle Render -->
                        <div class="relative w-44 h-44 shrink-0">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="15.915" fill="none" stroke="#E2E8F0" class="dark:stroke-gray-700" stroke-width="3.5"></circle>
                                <?php $offset = 0; ?>
                                <?php foreach ($top_treatments as $i => $tt): 
                                    $pct = $tt_total > 0 ? round($tt['cnt'] / $tt_total * 100) : 0;
                                ?>
                                <circle cx="18" cy="18" r="15.915" fill="none" stroke="<?= $chart_colors[$i % 5] ?>" stroke-width="3.5" stroke-dasharray="<?= $pct ?> 100" stroke-dashoffset="-<?= $offset ?>"></circle>
                                <?php $offset += $pct; ?>
                                <?php endforeach; ?>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-[10px] text-slate-400 dark:text-gray-400 font-medium block">Total</span>
                                <span class="text-xl font-bold text-slate-800 dark:text-white"><?= $tt_total ?></span>
                            </div>
                        </div>

                        <!-- Data Analytics Spread Table -->
                        <div class="w-full space-y-2 text-xs">
                            <?php foreach ($top_treatments as $i => $tt): 
                                $pct = $tt_total > 0 ? round($tt['cnt'] / $tt_total * 100) : 0;
                            ?>
                            <div class="flex items-center justify-between font-medium">
                                <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full <?= $color_classes[$i % 5] ?>"></span> <span class="text-slate-700 dark:text-gray-300"><?= htmlspecialchars($tt['treatment_name']) ?></span></span>
                                <span class="text-slate-400 dark:text-gray-500"><?= $pct ?>%</span><span class="font-bold text-slate-700 dark:text-gray-300"><?= $tt['cnt'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Messages Communications Board Container -->
<div class="bg-white dark:bg-gray-900 p-6 rounded-3xl border border-slate-100 dark:border-gray-800 shadow-2xl shadow-slate-100/40 lg:col-span-5 flex flex-col justify-between">
    
    <!-- Header Block -->
    <div class="flex items-center justify-between border-b pb-3 border-slate-50 dark:border-gray-800">
        <h3 class="font-serif text-lg font-bold text-slate-800 dark:text-white">Messages</h3>
        <a href="messages.php" class="text-[10px] font-bold text-brand-pink bg-brand-lightPink dark:bg-gray-800 px-3 py-1 rounded-md hover:opacity-90 transition-opacity">
            View All
        </a>
    </div>

    <!-- Dynamic Message Loop Queue -->
    <div class="mt-4 divide-y divide-slate-50 dark:divide-gray-800 flex-grow space-y-4">
        <?php if (!empty($recent_messages)): ?>
            <?php foreach ($recent_messages as $index => $msg): 
                /**
                 * Dynamically cycles avatar accents down the list index sequence 
                 * to replicate the exact visual variety from image_b3c680.png
                 */
                $avatar_themes = [
                    ['bg' => 'bg-rose-50', 'text' => 'text-brand-pink'],
                    ['bg' => 'bg-blue-50', 'text' => 'text-blue-500'],
                    ['bg' => 'bg-amber-50', 'text' => 'text-amber-500']
                ];
                $current_theme = $avatar_themes[$index % count($avatar_themes)];
            ?>
                <!-- Dynamic Message Entry -->
                <div class="flex items-start justify-between pt-4 first:pt-1">
                    <div class="flex items-center space-x-3 min-w-0">
                        <!-- Round Identity Icon Box -->
                        <div class="w-8 h-8 <?= $current_theme['bg'] ?> <?= $current_theme['text'] ?> rounded-xl flex items-center justify-center shrink-0">
                            <i class="fa-regular fa-user text-xs"></i>
                        </div>
                        
                        <!-- Text Clipping Body Block -->
                        <div class="min-w-0">
                            <h5 class="text-xs font-bold text-slate-800 dark:text-white block"><?= htmlspecialchars($msg['name']) ?></h5>
                            <p class="text-[10px] text-slate-400 dark:text-gray-400 truncate max-w-[190px] font-medium mt-0.5">
                                <?= htmlspecialchars($msg['message_text']) ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Metadata Timeline Details -->
                    <div class="text-right flex flex-col items-end justify-between h-8 shrink-0">
                        <span class="text-[9px] text-slate-400 dark:text-gray-500 font-semibold tracking-tight">
                            <?= date("d M Y", strtotime($msg['created_at'])) ?>
                        </span>
                        
                        <!-- Unread Status Anchor Indicator Dot -->
                        <?php if (($msg['status'] ?? 'unread') === 'unread'): ?>
                            <span class="w-1.5 h-1.5 bg-brand-pink rounded-full block mt-auto"></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback design view when messages table is empty -->
            <div class="text-center py-10 text-slate-400 dark:text-gray-500 text-xs font-light">
                <i class="fa-regular fa-envelope-open text-xl block mb-2 text-slate-300 dark:text-gray-600"></i>
                No messages found in your inbox.
            </div>
        <?php endif; ?>
    </div>
</div>
    <!-- JAVASCRIPT FOR MOBILE SIDEBAR TOGGLE -->
    <script>
        document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
            // Target the sidebar element included via 'sidebar.php'
            // Usually modern mobile sidebars use an ID like 'sidebar' or 'admin-sidebar'
            const sidebar = document.getElementById('sidebar'); 
            
            if(sidebar) {
                // Toggles visibility on Tailwind responsive architectures
                sidebar.classList.toggle('-translate-x-full');
                sidebar.classList.toggle('translate-x-0');
            }
        });
    </script>

</body>
</html>