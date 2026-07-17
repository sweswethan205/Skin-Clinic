<?php
require_once __DIR__ . '/../config/db.php';

// Fetch doctors for filter
$doctors_result = $conn->query("SELECT * FROM doctors ORDER BY name ASC");
$doctors = [];
while ($row = $doctors_result->fetch_assoc()) {
    $doctors[] = $row;
}

// Fetch schedules with doctor names
$doctor_filter = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
if ($doctor_filter > 0) {
    $sql = "SELECT s.*, d.name AS doctor_name, d.photo AS doctor_photo FROM schedules s JOIN doctors d ON s.doctor_id = d.id WHERE s.doctor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $doctor_filter);
    $stmt->execute();
    $schedules_result = $stmt->get_result();
    $stmt->close();
} else {
    $sql = "SELECT s.*, d.name AS doctor_name, d.photo AS doctor_photo FROM schedules s JOIN doctors d ON s.doctor_id = d.id";
    $schedules_result = $conn->query($sql);
}
$schedules = [];
while ($row = $schedules_result->fetch_assoc()) {
    $schedules[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Schedules - GlowSkin Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-[#FAF9F6] dark:bg-gray-950 min-h-screen text-slate-800 dark:text-gray-100">
    <?php include '../includes/header.php'; ?>

    <!-- Hero -->
    <section class="bg-gradient-to-br from-brand-lightPink to-pink-50 dark:from-gray-900 dark:to-gray-800 py-16">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <span class="text-xs font-semibold tracking-widest text-brand-pink uppercase block mb-3">Doctor Availability</span>
            <h1 class="font-serif text-4xl text-brand-dark dark:text-white font-bold mb-3">Doctor Schedules</h1>
            <p class="text-sm text-brand-textMuted dark:text-gray-400 max-w-xl mx-auto">Check available time slots and book your appointment with our specialist doctors.</p>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-6 py-12">

        <!-- Filter -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-100 dark:border-gray-800 shadow-sm p-4 mb-8 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="text-sm font-bold text-slate-700 dark:text-gray-200">Filter by Doctor</span>
                <form method="GET" action="schedule.php" class="flex items-center gap-2">
                    <select name="doctor_id" onchange="this.form.submit()" class="text-xs border border-slate-200 dark:border-gray-700 rounded-lg px-3 py-2 font-semibold text-slate-500 dark:text-gray-400 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none">
                        <option value="0">All Doctors</option>
                        <?php foreach ($doctors as $doc): ?>
                        <option value="<?php echo $doc['id']; ?>" <?php echo $doctor_filter === (int)$doc['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($doc['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($doctor_filter > 0): ?>
                    <a href="schedule.php" class="text-[11px] text-slate-400 hover:text-brand-pink font-semibold"><i class="fa-solid fa-xmark"></i> Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <a href="booking.php" class="px-4 py-2 bg-brand-pink text-white text-xs font-bold rounded-xl transition-all hover:bg-opacity-90 shadow-[0_4px_12px_rgba(232,93,117,0.25)]">
                <i class="fa-regular fa-calendar-plus mr-1"></i> Book Appointment
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-100 dark:border-gray-800 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 bg-pink-50 dark:bg-gray-800 text-brand-pink rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-calendar-day"></i></div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider block">Total Schedules</span>
                    <span class="text-xl font-extrabold text-slate-800 dark:text-white"><?php echo count($schedules); ?></span>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-100 dark:border-gray-800 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 bg-emerald-50 dark:bg-gray-800 text-emerald-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-check-circle"></i></div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider block">Available Dates</span>
                    <span class="text-xl font-extrabold text-slate-800 dark:text-white">
                        <?php
                        $unique_dates = array_unique(array_map(fn($s) => $s['available_date'], $schedules));
                        echo count($unique_dates);
                        ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Schedules List -->
        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-slate-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <?php if (empty($schedules)): ?>
            <div class="py-16 text-center">
                <div class="text-slate-300">
                    <i class="fa-regular fa-calendar-xmark text-4xl mb-3 block"></i>
                    <span class="font-bold text-sm text-slate-500">No schedules found</span>
                    <p class="text-xs text-slate-400 mt-1">Check back later for available time slots.</p>
                </div>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/70 dark:bg-gray-800 border-b border-slate-100 dark:border-gray-700 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">
                            <th class="py-4 px-6">Doctor</th>
                            <th class="py-4 px-6">Date</th>
                            <th class="py-4 px-6">Available Hours</th>
                            <th class="py-4 px-6 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-gray-800 text-xs font-semibold text-slate-700 dark:text-gray-300">
                        <?php foreach ($schedules as $schedule): ?>
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-gray-800/60 transition-colors group">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full overflow-hidden bg-brand-lightPink flex items-center justify-center text-brand-pink text-xs font-bold shrink-0">
                                        <?php if (!empty($schedule['doctor_photo'])): ?>
                                        <img src="../<?php echo htmlspecialchars($schedule['doctor_photo']); ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                        <?php echo strtoupper(substr($schedule['doctor_name'], 0, 1)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <span class="font-bold text-slate-800 group-hover:text-brand-pink transition-colors"><?php echo htmlspecialchars($schedule['doctor_name']); ?></span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-medium text-slate-500"><?php echo date('M d, Y', strtotime($schedule['available_date'])); ?></span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-mono text-sm"><?php echo date('h:i A', strtotime($schedule['start_time'])); ?> - <?php echo date('h:i A', strtotime($schedule['end_time'])); ?></span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="booking.php?doctor=<?php echo urlencode($schedule['doctor_name']); ?>" class="px-3 py-1.5 bg-brand-pink text-white text-[10px] font-bold rounded-lg hover:bg-opacity-90 transition-all">
                                    Book Now
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="bg-slate-50/50 px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 font-semibold">
                <span>Showing <?php echo count($schedules); ?> schedule<?php echo count($schedules) !== 1 ? 's' : ''; ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
