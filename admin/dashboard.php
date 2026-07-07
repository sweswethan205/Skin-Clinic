<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

// Unread notifications count
$unread_notif_count = 0;
$nc = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE is_read = 0 AND type = 'booking'");
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
</head>
<body class="bg-brand-bgGray text-slate-700 min-h-screen flex overflow-x-hidden">

    <?php include 'sidebar.php'; ?>
    
    <!-- CONTENT WRAPPER -->
    <div class="flex-grow flex flex-col min-w-0 lg:ml-64">
        
        <!-- TOP HEADER CONTEXT BAR -->
        <header class="h-16 sm:h-20 bg-white border-b border-slate-100 flex items-center justify-between px-4 sm:px-8 shrink-0 sticky top-0 z-50">
            <div class="flex items-center space-x-3 sm:space-x-4">
                
                <!-- MOBILE MENU TOGGLE ICON (Visible only on mobile/tablet) -->
                <button id="mobile-menu-toggle" class="lg:hidden text-slate-600 hover:text-brand-pink text-xl p-1 focus:outline-none transition-colors">
                    <i class="fa-solid fa-bars"></i>
                </button>
                
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Dashboard</h2>
                    <p class="text-[10px] sm:text-xs text-slate-400 font-medium">Welcome back, Admin!</p>
                </div>
            </div>

            <!-- Global Action Controls -->
            <div class="flex items-center space-x-3 sm:space-x-6">
                <!-- Search (hidden on mobile) -->
                <div class="relative w-64 hidden md:block">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" placeholder="Search..." class="w-full pl-9 pr-4 py-1.5 text-xs bg-slate-50 border border-slate-100 rounded-xl focus:outline-none focus:border-brand-pink/50">
                </div>
                
                <!-- Notification Bell -->
                <a href="notification.php" class="relative inline-block text-slate-500 hover:text-brand-pink text-xl transition-colors p-1 shrink-0">
                    <i class="fa-regular fa-bell"></i>
                    <?php if ($unread_notif_count > 0): ?>
                        <span class="absolute -top-1 -right-1.5 bg-brand-pink text-white text-[9px] font-bold h-4 min-w-[16px] px-1 flex items-center justify-center rounded-full ring-2 ring-white shadow-xs">
                            <?= $unread_notif_count ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Profile Badge -->
                <a href="profile.php" class="flex items-center space-x-3 border-l pl-6 border-slate-100 hover:opacity-80 transition">
                    <div class="w-9 h-9 rounded-full overflow-hidden bg-brand-lightPink flex items-center justify-center text-brand-pink text-xs font-bold">
                        <?php if ($admin_photo): ?>
                            <img src="../<?php echo htmlspecialchars($admin_photo); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-800 block"><?php echo htmlspecialchars($admin_username); ?></span>
                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                    </div>
                </a>
            </div>
        </header>

        <!-- DASHBOARD CONTAINER MAIN PLATFORM -->
        <main class="bg-slate-100 flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-4 sm:space-y-6">
            
            <!-- GRID LAYER 1: METRIC CARD SYSTEM -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Appointments -->
                <div class="bg-white p-5 rounded-2xl border border-slate-100/80 shadow-xs flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-pink-50 rounded-xl flex items-center justify-center text-brand-pink text-lg">
                            <i class="fa-regular fa-calendar-check"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Total Appointments</span>
                            <span class="text-2xl font-bold text-slate-800 tracking-tight"><?= $total_appointments ?></span>
                            <span class="text-[10px] font-medium text-slate-400 block mt-0.5">All Time</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50/60 px-2 py-0.5 rounded-md flex items-center gap-1">
                        <i class="fa-solid fa-arrow-up text-[8px]"></i> 12%
                    </span>
                </div>

                <!-- Total Patients -->
                <div class="bg-white p-5 rounded-2xl border border-slate-100/80 shadow-xs flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500 text-lg">
                            <i class="fa-solid fa-user-group"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Total Patients</span>
                            <span class="text-2xl font-bold text-slate-800 tracking-tight"><?= $total_patients ?></span>
                            <span class="text-[10px] font-medium text-slate-400 block mt-0.5">Registered Users</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50/60 px-2 py-0.5 rounded-md flex items-center gap-1">
                        <i class="fa-solid fa-arrow-up text-[8px]"></i> 8%
                    </span>
                </div>

                <!-- Total Doctors -->
                <div class="bg-white p-5 rounded-2xl border border-slate-100/80 shadow-xs flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 text-lg">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Total Doctors</span>
                            <span class="text-2xl font-bold text-slate-800 tracking-tight"><?= $total_doctors ?></span>
                            <span class="text-[10px] font-medium text-slate-400 block mt-0.5">Active Doctors</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50/60 px-2 py-0.5 rounded-md flex items-center gap-1">
                        <i class="fa-solid fa-arrow-up text-[8px]"></i> 5%
                    </span>
                </div>

                <!-- Total Treatments -->
                <div class="bg-white p-5 rounded-2xl border border-slate-100/80 shadow-xs flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-500 text-lg">
                            <i class="fa-solid fa-mortar-pestle"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block">Total Treatments</span>
                            <span class="text-2xl font-bold text-slate-800 tracking-tight"><?= $total_treatments ?></span>
                            <span class="text-[10px] font-medium text-slate-400 block mt-0.5">Active Treatments</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50/60 px-2 py-0.5 rounded-md flex items-center gap-1">
                        <i class="fa-solid fa-arrow-up text-[8px]"></i> 7%
                    </span>
                </div>
            </div>

            <!-- GRID LAYER 2: CHARTS & LOGICAL DATA ENTRIES -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Appointments Overview Container (Left) -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 lg:col-span-7 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-slate-800">Appointments Overview</h3>
                        <div class="relative">
                            <select class="appearance-none bg-slate-50 border border-slate-100 text-[10px] font-medium text-slate-500 px-3 py-1 pr-6 rounded-lg focus:outline-none">
                                <option>This Month</option>
                            </select>
                            <i class="fa-solid fa-chevron-down text-[8px] text-slate-400 absolute right-2.5 top-2.5 pointer-events-none"></i>
                        </div>
                    </div>
                    
                    <!-- SVG Interactive Vector Graph Chart Blueprint Mockup -->
                    <div class="relative w-full h-56 mt-4">
                        <!-- Y Axis Indicators -->
                        <div class="absolute left-0 top-0 h-full w-6 flex flex-col justify-between text-[10px] text-slate-300 font-medium">
                            <span>50</span><span>40</span><span>30</span><span>20</span><span>10</span><span>0</span>
                        </div>
                        
                        <!-- SVG Vector Data Line Overlay -->
                        <div class="ml-8 h-full relative border-b border-l border-slate-100/50">
                            <!-- Background Grid Horizontal Lines -->
                            <div class="absolute inset-x-0 top-0 h-[1px] border-t border-dashed border-slate-100"></div>
                            <div class="absolute inset-x-0 top-1/5 h-[1px] border-t border-dashed border-slate-100"></div>
                            <div class="absolute inset-x-0 top-2/5 h-[1px] border-t border-dashed border-slate-100"></div>
                            <div class="absolute inset-x-0 top-3/5 h-[1px] border-t border-dashed border-slate-100"></div>
                            <div class="absolute inset-x-0 top-4/5 h-[1px] border-t border-dashed border-slate-100"></div>
                            
                            <svg viewBox="0 0 500 150" class="w-full h-full overflow-visible absolute bottom-0">
                                <defs>
                                    <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#FF6584" stop-opacity="0.15"/>
                                        <stop offset="100%" stop-color="#FF6584" stop-opacity="0.00"/>
                                    </linearGradient>
                                </defs>
                                <path d="M0,120 Q40,70 80,90 T160,50 T240,75 T320,40 T400,85 T500,20 L500,150 L0,150 Z" fill="url(#chartGrad)" />
                                <path d="M0,120 Q40,70 80,90 T160,50 T240,75 T320,40 T400,85 T500,20" fill="none" stroke="#FF6584" stroke-width="2.5" />
                                
                                <!-- Tooltip Node Intersection Highlight -->
                                <circle cx="240" cy="53" r="4" fill="#FF6584" stroke="white" stroke-width="2" />
                            </svg>

                            <!-- Tooltip Text Box Node Indicator Placement -->
                            <div class="absolute top-[32px] left-[45%] -translate-x-1/2 bg-brand-pink text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md shadow-xs after:content-[''] after:absolute after:top-full after:left-1/2 after:-translate-x-1/2 after:border-4 after:border-transparent after:border-t-brand-pink">
                                35
                            </div>
                        </div>

                        <!-- X Axis Horizontal Text Labels -->
                        <div class="ml-8 mt-2 flex justify-between text-[9px] text-slate-400 font-semibold tracking-wide">
                            <span>01 May</span><span>05 May</span><span>10 May</span><span>15 May</span><span>20 May</span><span>25 May</span><span>30 May</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Appointments Log Section (Right) -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 lg:col-span-5 flex flex-col justify-between">
                    <div class="flex items-center justify-between border-b pb-3 border-slate-50">
                        <h3 class="text-sm font-bold text-slate-800">Recent Appointments</h3>
                        <a href="#" class="text-[10px] font-bold text-brand-pink bg-brand-lightPink px-2 py-0.5 rounded-md hover:opacity-90">View All</a>
                    </div>

                    <!-- Log Queue Units -->
                    <div class="divide-y divide-slate-50 mt-4 flex-grow space-y-3.5">
                        <?php if (count($recent_appointments) > 0): ?>
                            <?php foreach ($recent_appointments as $app): 
                                $status_class = match($app['status']) {
                                    'confirmed' => 'text-emerald-500 bg-emerald-50',
                                    'pending' => 'text-blue-500 bg-blue-50',
                                    'cancelled' => 'text-rose-500 bg-rose-50',
                                    'completed' => 'text-slate-500 bg-slate-50',
                                    default => 'text-slate-500 bg-slate-50'
                                };
                            ?>
                            <div class="flex items-center justify-between pt-2 first:pt-0">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 shrink-0">
                                        <?= strtoupper(substr($app['patient_name'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 block"><?= htmlspecialchars($app['patient_name']) ?></span>
                                        <span class="text-[10px] text-slate-400 block font-medium"><?= htmlspecialchars($app['treatment_name']) ?></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-semibold text-slate-600 block"><?= date("d M Y", strtotime($app['available_date'])) ?></span>
                                    <span class="text-[9px] text-slate-400 block"><?= date("h:i A", strtotime($app['start_time'])) ?></span>
                                </div>
                                <span class="text-[9px] font-bold <?= $status_class ?> px-2 py-0.5 rounded-md"><?= ucfirst($app['status']) ?></span>
                                <button class="text-slate-300 hover:text-slate-500 text-xs"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-xs text-slate-400 text-center py-4">No appointments yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- GRID LAYER 3: TOP TREATMENTS & INTERNAL MESSAGES LOG -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Top Treatments Donut Allocation Module (Left) -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 lg:col-span-7">
                    <h3 class="text-sm font-bold text-slate-800 mb-6">Top Treatments</h3>
                    
                    <?php
                    $chart_colors = ['#FF6584', '#A855F7', '#F59E0B', '#10B981', '#3B82F6'];
                    $color_classes = ['bg-brand-pink', 'bg-purple-500', 'bg-amber-500', 'bg-emerald-500', 'bg-blue-500'];
                    $dash_offset = 0;
                    ?>
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-8">
                        <!-- Circle Render -->
                        <div class="relative w-44 h-44 shrink-0">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="15.915" fill="none" stroke="#E2E8F0" stroke-width="3.5"></circle>
                                <?php $offset = 0; ?>
                                <?php foreach ($top_treatments as $i => $tt): 
                                    $pct = $tt_total > 0 ? round($tt['cnt'] / $tt_total * 100) : 0;
                                ?>
                                <circle cx="18" cy="18" r="15.915" fill="none" stroke="<?= $chart_colors[$i % 5] ?>" stroke-width="3.5" stroke-dasharray="<?= $pct ?> 100" stroke-dashoffset="-<?= $offset ?>"></circle>
                                <?php $offset += $pct; ?>
                                <?php endforeach; ?>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-[10px] text-slate-400 font-medium block">Total</span>
                                <span class="text-xl font-bold text-slate-800"><?= $tt_total ?></span>
                            </div>
                        </div>

                        <!-- Data Analytics Spread Table -->
                        <div class="w-full space-y-2 text-xs">
                            <?php foreach ($top_treatments as $i => $tt): 
                                $pct = $tt_total > 0 ? round($tt['cnt'] / $tt_total * 100) : 0;
                            ?>
                            <div class="flex items-center justify-between font-medium">
                                <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full <?= $color_classes[$i % 5] ?>"></span> <?= htmlspecialchars($tt['treatment_name']) ?></span>
                                <span class="text-slate-400"><?= $pct ?>%</span><span class="font-bold text-slate-700"><?= $tt['cnt'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Messages Communications Board Container -->
<div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-2xl shadow-slate-100/40 lg:col-span-5 flex flex-col justify-between">
    
    <!-- Header Block -->
    <div class="flex items-center justify-between border-b pb-3 border-slate-50">
        <h3 class="font-serif text-lg font-bold text-slate-800">Messages</h3>
        <a href="messages.php" class="text-[10px] font-bold text-brand-pink bg-brand-lightPink px-3 py-1 rounded-md hover:opacity-90 transition-opacity">
            View All
        </a>
    </div>

    <!-- Dynamic Message Loop Queue -->
    <div class="mt-4 divide-y divide-slate-50 flex-grow space-y-4">
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
                            <h5 class="text-xs font-bold text-slate-800 block"><?= htmlspecialchars($msg['name']) ?></h5>
                            <p class="text-[10px] text-slate-400 truncate max-w-[190px] font-medium mt-0.5">
                                <?= htmlspecialchars($msg['message_text']) ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Metadata Timeline Details -->
                    <div class="text-right flex flex-col items-end justify-between h-8 shrink-0">
                        <span class="text-[9px] text-slate-400 font-semibold tracking-tight">
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
            <div class="text-center py-10 text-slate-400 text-xs font-light">
                <i class="fa-regular fa-envelope-open text-xl block mb-2 text-slate-300"></i>
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