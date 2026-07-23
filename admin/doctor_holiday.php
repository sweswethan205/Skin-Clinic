<?php
require_once __DIR__ . '/../config/db.php';

$admin = $conn->query("SELECT username, photo FROM admins ORDER BY id ASC LIMIT 1")->fetch_assoc();
$admin_photo = $admin['photo'] ?? '';
$admin_username = $admin['username'] ?? 'Admin';

$message = '';
$message_type = '';

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $doctor_id = intval($_POST['doctor_id']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = trim($_POST['reason']);
    $notes = trim($_POST['notes'] ?? '');

    if ($doctor_id <= 0) {
        $message = "Please select a doctor.";
        $message_type = "error";
        header("Location: doctor_holiday.php?msg=" . urlencode($message) . "&type=$message_type");
        exit;
    }

    if (empty($start_date) || empty($end_date)) {
        $message = "Start date and end date are required.";
        $message_type = "error";
        header("Location: doctor_holiday.php?msg=" . urlencode($message) . "&type=$message_type");
        exit;
    }

    if ($end_date < $start_date) {
        $message = "End date must be on or after the start date.";
        $message_type = "error";
        header("Location: doctor_holiday.php?msg=" . urlencode($message) . "&type=$message_type");
        exit;
    }

    // Check for overlapping holidays for the same doctor
    $overlap_check = $conn->prepare("SELECT id FROM doctor_holidays WHERE doctor_id = ? AND start_date <= ? AND end_date >= ? LIMIT 1");
    $overlap_check->bind_param("iss", $doctor_id, $end_date, $start_date);
    $overlap_check->execute();
    $overlap_result = $overlap_check->get_result();
    $overlap_check->close();

    if ($overlap_result->fetch_assoc()) {
        $message = "This doctor already has a holiday entry that overlaps with the selected dates.";
        $message_type = "error";
        header("Location: doctor_holiday.php?msg=" . urlencode($message) . "&type=$message_type");
        exit;
    }

    // Check for existing non-cancelled appointments during the holiday
    $appt_check = $conn->prepare(
        "SELECT a.id, a.user_id, a.appointment_start, a.appointment_end, u.name AS user_name, t.treatment_name
         FROM appointments a
         JOIN schedules s ON a.schedule_id = s.id
         JOIN users u ON a.user_id = u.id
         JOIN treatments t ON a.treatment_id = t.id
         WHERE s.doctor_id = ? AND a.status != 'cancelled'
         AND s.available_date >= ? AND s.available_date <= ?"
    );
    $appt_check->bind_param("iss", $doctor_id, $start_date, $end_date);
    $appt_check->execute();
    $appt_result = $appt_check->get_result();
    $conflicting_appointments = [];
    while ($appt_row = $appt_result->fetch_assoc()) {
        $conflicting_appointments[] = $appt_row;
    }
    $appt_check->close();

    // Insert the holiday
    $insert = $conn->prepare("INSERT INTO doctor_holidays (doctor_id, start_date, end_date, reason, notes) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("issss", $doctor_id, $start_date, $end_date, $reason, $notes);
    if ($insert->execute()) {
        $holiday_id = $insert->insert_id;
        $insert->close();

        if (!empty($conflicting_appointments)) {
            $doctor_name = $_POST['doctor_name'] ?? 'Doctor';
            $h_title = "Appointment May Need Rescheduling";

            foreach ($conflicting_appointments as $conflict) {
                $h_msg = "Dr. " . htmlspecialchars($doctor_name) . " will be on {$reason} from " . date('M d, Y', strtotime($start_date)) . " to " . date('M d, Y', strtotime($end_date)) . ". Your appointment on " . date('M d, Y', strtotime($conflict['appointment_start'])) . " for {$conflict['treatment_name']} may need rescheduling. Please contact the clinic.";
                $n_stmt = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, title, message, type, target_role) VALUES (?, ?, ?, ?, 'holiday', 'user')");
                $n_stmt->bind_param("iiss", $conflict['user_id'], $conflict['id'], $h_title, $h_msg);
                $n_stmt->execute();
                $n_stmt->close();
            }

            $message = "Holiday added. " . count($conflicting_appointments) . " existing appointment(s) affected. Patients have been notified.";
            $message_type = "success";
        } else {
            $message = "Doctor holiday added successfully!";
            $message_type = "success";
        }
    } else {
        $message = "Error adding holiday: " . $conn->error;
        $message_type = "error";
    }

    header("Location: doctor_holiday.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $holiday_id = intval($_POST['holiday_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = trim($_POST['reason']);
    $notes = trim($_POST['notes'] ?? '');

    if ($holiday_id <= 0 || $doctor_id <= 0) {
        $message = "Invalid request.";
        $message_type = "error";
        header("Location: doctor_holiday.php?msg=" . urlencode($message) . "&type=$message_type");
        exit;
    }

    if (empty($start_date) || empty($end_date)) {
        $message = "Start date and end date are required.";
        $message_type = "error";
        header("Location: doctor_holiday.php?msg=" . urlencode($message) . "&type=$message_type");
        exit;
    }

    if ($end_date < $start_date) {
        $message = "End date must be on or after the start date.";
        $message_type = "error";
        header("Location: doctor_holiday.php?msg=" . urlencode($message) . "&type=$message_type");
        exit;
    }

    // Check for overlapping holidays for the same doctor (exclude current holiday)
    $overlap_check = $conn->prepare("SELECT id FROM doctor_holidays WHERE doctor_id = ? AND id != ? AND start_date <= ? AND end_date >= ? LIMIT 1");
    $overlap_check->bind_param("iiss", $doctor_id, $holiday_id, $end_date, $start_date);
    $overlap_check->execute();
    $overlap_result = $overlap_check->get_result();
    $overlap_check->close();

    if ($overlap_result->fetch_assoc()) {
        $message = "This doctor already has a holiday entry that overlaps with the selected dates.";
        $message_type = "error";
        header("Location: doctor_holiday.php?msg=" . urlencode($message) . "&type=$message_type");
        exit;
    }

    // Get old dates to find new conflicting appointments
    $old_stmt = $conn->prepare("SELECT doctor_id, start_date, end_date FROM doctor_holidays WHERE id = ?");
    $old_stmt->bind_param("i", $holiday_id);
    $old_stmt->execute();
    $old_row = $old_stmt->get_result()->fetch_assoc();
    $old_stmt->close();

    // Update the holiday
    $update = $conn->prepare("UPDATE doctor_holidays SET doctor_id = ?, start_date = ?, end_date = ?, reason = ?, notes = ? WHERE id = ?");
    $update->bind_param("issssi", $doctor_id, $start_date, $end_date, $reason, $notes, $holiday_id);
    if ($update->execute()) {
        $update->close();

        // Check for newly affected appointments (appointments in the new date range that weren't in the old range)
        $appt_check = $conn->prepare(
            "SELECT a.id, a.user_id, a.appointment_start, a.appointment_end, t.treatment_name
             FROM appointments a
             JOIN schedules s ON a.schedule_id = s.id
             JOIN treatments t ON a.treatment_id = t.id
             WHERE s.doctor_id = ? AND a.status != 'cancelled'
             AND s.available_date >= ? AND s.available_date <= ?"
        );
        $appt_check->bind_param("iss", $doctor_id, $start_date, $end_date);
        $appt_check->execute();
        $appt_result = $appt_check->get_result();
        $conflicting_appointments = [];
        while ($appt_row = $appt_result->fetch_assoc()) {
            $conflicting_appointments[] = $appt_row;
        }
        $appt_check->close();

        if (!empty($conflicting_appointments)) {
            $doctor_name = $_POST['doctor_name'] ?? 'Doctor';
            $h_title = "Appointment May Need Rescheduling";

            foreach ($conflicting_appointments as $conflict) {
                // Check if this appointment was already notified for this holiday
                $already_notified = $conn->prepare("SELECT id FROM notifications WHERE user_id = ? AND appointment_id = ? AND type = 'holiday' LIMIT 1");
                $already_notified->bind_param("ii", $conflict['user_id'], $conflict['id']);
                $already_notified->execute();
                $notif_exists = $already_notified->get_result()->fetch_assoc();
                $already_notified->close();

                if (!$notif_exists) {
                    $h_msg = "Dr. " . htmlspecialchars($doctor_name) . " will be on {$reason} from " . date('M d, Y', strtotime($start_date)) . " to " . date('M d, Y', strtotime($end_date)) . ". Your appointment on " . date('M d, Y', strtotime($conflict['appointment_start'])) . " for {$conflict['treatment_name']} may need rescheduling. Please contact the clinic.";
                    $n_stmt = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, title, message, type, target_role) VALUES (?, ?, ?, ?, 'holiday', 'user')");
                    $n_stmt->bind_param("iiss", $conflict['user_id'], $conflict['id'], $h_title, $h_msg);
                    $n_stmt->execute();
                    $n_stmt->close();
                }
            }

            $message = "Holiday updated. " . count($conflicting_appointments) . " appointment(s) may be affected. Patients have been notified.";
            $message_type = "success";
        } else {
            $message = "Holiday updated successfully!";
            $message_type = "success";
        }
    } else {
        $message = "Error updating holiday: " . $conn->error;
        $message_type = "error";
    }

    header("Location: doctor_holiday.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM doctor_holidays WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Holiday deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting holiday: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
    header("Location: doctor_holiday.php?msg=" . urlencode($message) . "&type=$message_type");
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

// Doctor filter
$doctor_filter = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

// Pagination
$per_page = 15;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Count total
if ($doctor_filter > 0) {
    $cnt_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM doctor_holidays WHERE doctor_id = ?");
    $cnt_stmt->bind_param("i", $doctor_filter);
    $cnt_stmt->execute();
    $total_rows = $cnt_stmt->get_result()->fetch_assoc()['cnt'];
    $cnt_stmt->close();
} else {
    $total_rows = $conn->query("SELECT COUNT(*) AS cnt FROM doctor_holidays")->fetch_assoc()['cnt'];
}
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// Fetch holidays with doctor name (paginated)
if ($doctor_filter > 0) {
    $hol_stmt = $conn->prepare("SELECT dh.*, d.name AS doctor_name FROM doctor_holidays dh JOIN doctors d ON dh.doctor_id = d.id WHERE dh.doctor_id = ? ORDER BY dh.start_date DESC LIMIT ? OFFSET ?");
    $hol_stmt->bind_param("iii", $doctor_filter, $per_page, $offset);
    $hol_stmt->execute();
    $holidays_result = $hol_stmt->get_result();
} else {
    $hol_stmt = $conn->prepare("SELECT dh.*, d.name AS doctor_name FROM doctor_holidays dh JOIN doctors d ON dh.doctor_id = d.id ORDER BY dh.start_date DESC LIMIT ? OFFSET ?");
    $hol_stmt->bind_param("ii", $per_page, $offset);
    $hol_stmt->execute();
    $holidays_result = $hol_stmt->get_result();
}
$holidays = [];
while ($row = $holidays_result->fetch_assoc()) {
    $holidays[] = $row;
}
$hol_stmt->close();

$total_holidays = $total_rows;
$today = date('Y-m-d');

// Count active/upcoming holidays
$active_count = 0;
$upcoming_count = 0;
if ($doctor_filter > 0) {
    $ac_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM doctor_holidays WHERE doctor_id = ? AND start_date <= ? AND end_date >= ?");
    $ac_stmt->bind_param("iss", $doctor_filter, $today, $today);
    $ac_stmt->execute();
    $active_count = $ac_stmt->get_result()->fetch_assoc()['cnt'];
    $ac_stmt->close();

    $uc_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM doctor_holidays WHERE doctor_id = ? AND start_date > ?");
    $uc_stmt->bind_param("is", $doctor_filter, $today);
    $uc_stmt->execute();
    $upcoming_count = $uc_stmt->get_result()->fetch_assoc()['cnt'];
    $uc_stmt->close();
} else {
    $active_count = $conn->query("SELECT COUNT(*) AS cnt FROM doctor_holidays WHERE start_date <= '$today' AND end_date >= '$today'")->fetch_assoc()['cnt'];
    $upcoming_count = $conn->query("SELECT COUNT(*) AS cnt FROM doctor_holidays WHERE start_date > '$today'")->fetch_assoc()['cnt'];
}

$doctor_options = [];
foreach ($doctors as $doc) {
    $doctor_options[] = ['id' => $doc['id'], 'name' => $doc['name']];
}
$holidaysData_json = json_encode($holidays);
$doctors_json = json_encode($doctor_options);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Doctor Holidays</title>
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
                    <h2 class="text-xl font-extrabold text-brand-dark dark:text-white tracking-tight">Doctor Holidays</h2>
                    <p class="text-xs text-brand-muted dark:text-gray-400 font-medium">Manage doctor leave, vacation, and training days.</p>
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
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-purple-50 dark:bg-purple-900/20 text-purple-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-calendar-xmark"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Total Holidays</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white"><?php echo $total_holidays; ?></span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-user-slash"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Active Now</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white"><?php echo $active_count; ?></span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 text-blue-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block">Upcoming</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-white"><?php echo $upcoming_count; ?></span>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-bold text-brand-dark dark:text-white px-2">Holiday Directory</span>
                    <form method="GET" action="doctor_holiday.php" class="flex items-center gap-2">
                        <select name="doctor_id" onchange="this.form.submit()" class="text-xs border border-slate-200 dark:border-gray-700 rounded-lg px-3 py-2 font-semibold text-brand-muted dark:text-gray-400 bg-white dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none">
                            <option value="0">All Doctors</option>
                            <?php foreach ($doctors as $doc): ?>
                            <option value="<?php echo $doc['id']; ?>" <?php echo $doctor_filter === $doc['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($doc['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($doctor_filter > 0): ?>
                        <a href="doctor_holiday.php" class="text-[11px] text-brand-muted dark:text-gray-400 hover:text-brand-pink font-semibold"><i class="fa-solid fa-xmark"></i> Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                <button onclick="openCreateModal()" class="px-4 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)] flex items-center justify-center gap-2 shrink-0">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add Holiday
                </button>
            </div>

            <!-- Holidays Table -->
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-slate-200/60 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-gray-950 border-b border-slate-200/50 dark:border-gray-800 text-[11px] font-bold uppercase tracking-wider text-brand-muted dark:text-gray-400">
                                <th class="py-3 px-3 sm:py-4 sm:px-6">#</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Doctor</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Period</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Reason</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Status</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800 text-xs font-semibold text-brand-dark dark:text-gray-300">
                            <?php if (empty($holidays)): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="text-brand-muted dark:text-gray-400">
                                        <i class="fa-regular fa-calendar-xmark text-3xl mb-3 block"></i>
                                        <span class="font-bold text-sm">No holidays found</span>
                                        <p class="text-[11px] font-medium mt-1">Add a new holiday to get started.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php $i = $offset + 1; foreach ($holidays as $holiday): ?>
                            <?php
                                $is_active = ($holiday['start_date'] <= $today && $holiday['end_date'] >= $today);
                                $is_upcoming = ($holiday['start_date'] > $today);
                                $is_past = ($holiday['end_date'] < $today);
                                $days = (strtotime($holiday['end_date']) - strtotime($holiday['start_date'])) / 86400 + 1;
                            ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-gray-800 transition-colors group">
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-brand-muted dark:text-gray-400"><?= $i++ ?></td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 bg-brand-lightPink dark:bg-pink-900/20 rounded-xl flex items-center justify-center text-brand-pink text-xs font-bold">
                                            <?php echo strtoupper(substr($holiday['doctor_name'], 0, 2)); ?>
                                        </div>
                                        <span class="font-bold text-brand-dark dark:text-white group-hover:text-brand-pink transition-colors"><?php echo htmlspecialchars($holiday['doctor_name']); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <span class="font-medium text-slate-600 dark:text-gray-400">
                                        <?php echo date('M d, Y', strtotime($holiday['start_date'])); ?>
                                        <?php if ($holiday['start_date'] !== $holiday['end_date']): ?>
                                            - <?php echo date('M d, Y', strtotime($holiday['end_date'])); ?>
                                        <?php endif; ?>
                                    </span>
                                    <span class="text-[10px] text-brand-muted dark:text-gray-500 block mt-0.5"><?= $days ?> day<?= $days > 1 ? 's' : '' ?></span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                                        <?php
                                        switch ($holiday['reason']) {
                                            case 'Vacation': echo 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'; break;
                                            case 'Sick Leave': echo 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400'; break;
                                            case 'Training': echo 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400'; break;
                                            case 'Personal Leave': echo 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400'; break;
                                            default: echo 'bg-slate-50 dark:bg-gray-800 text-slate-600 dark:text-gray-400'; break;
                                        }
                                        ?>">
                                        <?php echo htmlspecialchars($holiday['reason']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <?php if ($is_active): ?>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-red-600 dark:text-red-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> Active
                                        </span>
                                    <?php elseif ($is_upcoming): ?>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 dark:text-blue-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Upcoming
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400 dark:text-gray-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-gray-600"></span> Past
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-right space-x-1 whitespace-nowrap">
                                    <?php if (!empty($holiday['notes'])): ?>
                                    <button onclick="viewNotes('<?= htmlspecialchars(addslashes($holiday['notes']), ENT_QUOTES) ?>')" class="p-1.5 bg-slate-50 hover:bg-slate-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-brand-muted dark:text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 rounded-lg transition-colors" title="View Notes">
                                        <i class="fa-regular fa-note-sticky"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button onclick="openEditModal(<?= $holiday['id'] ?>)" class="p-1.5 bg-slate-50 hover:bg-slate-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white rounded-lg transition-colors" title="Edit Holiday">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="confirmDelete(<?= $holiday['id'] ?>)" class="p-1.5 bg-slate-50 hover:bg-red-50 dark:bg-gray-800 dark:hover:bg-red-900/30 text-brand-muted dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 rounded-lg transition-colors" title="Delete Holiday">
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
                    <span>Showing <?php echo $total_rows > 0 ? $offset + 1 : 0; ?>–<?php echo min($offset + $per_page, $total_rows); ?> of <?php echo $total_rows; ?> holiday<?php echo $total_rows !== 1 ? 's' : ''; ?></span>
                    <?php if ($total_pages > 1): ?>
                    <div class="flex items-center gap-1">
                        <?php
                        $page_base = 'doctor_holiday.php?' . ($doctor_filter > 0 ? "doctor_id=$doctor_filter&" : '');
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
                <h3 class="text-base font-extrabold text-brand-dark dark:text-white"><i class="fa-solid fa-calendar-xmark text-brand-pink mr-2"></i> Add Doctor Holiday</h3>
                <button onclick="closeCreateModal()" class="text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="doctor_holiday.php" class="p-6 space-y-5" id="createHolidayForm">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="doctor_name" id="create_doctor_name" value="">
                <div>
                    <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Doctor</label>
                    <select name="doctor_id" id="create_doctor_id" required onchange="document.getElementById('create_doctor_name').value = this.options[this.selectedIndex].text"
                        class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white bg-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
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
                <div>
                    <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Reason</label>
                    <select name="reason" required
                        class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white bg-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                        <option value="Vacation">Vacation</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Training">Training</option>
                        <option value="Personal Leave">Personal Leave</option>
                        <option value="Conference">Conference</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Notes (Optional)</label>
                    <textarea name="notes" rows="3" placeholder="Additional notes about this holiday..."
                        class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all resize-none"></textarea>
                </div>

                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-xl p-3 text-[11px] font-medium text-amber-700 dark:text-amber-400">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    Patients with existing appointments during this period will be <strong>notified</strong> that their appointment may need rescheduling. Monthly schedules remain unchanged.
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeCreateModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-brand-dark dark:text-white text-xs font-bold rounded-xl transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)]">
                        <i class="fa-solid fa-plus mr-1"></i> Add Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-lg mx-4 shadow-2xl">
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100 dark:border-gray-800">
                <h3 class="text-base font-extrabold text-brand-dark dark:text-white"><i class="fa-regular fa-pen-to-square text-brand-pink mr-2"></i> Edit Doctor Holiday</h3>
                <button onclick="closeEditModal()" class="text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="doctor_holiday.php" class="p-6 space-y-5" id="editHolidayForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="holiday_id" id="edit_holiday_id" value="">
                <input type="hidden" name="doctor_name" id="edit_doctor_name" value="">
                <div>
                    <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Doctor</label>
                    <select name="doctor_id" id="edit_doctor_id" required onchange="document.getElementById('edit_doctor_name').value = this.options[this.selectedIndex].text"
                        class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white bg-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                        <option value="">Select Doctor</option>
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
                <div>
                    <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Reason</label>
                    <select name="reason" id="edit_reason" required
                        class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white bg-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                        <option value="Vacation">Vacation</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Training">Training</option>
                        <option value="Personal Leave">Personal Leave</option>
                        <option value="Conference">Conference</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted dark:text-gray-400 uppercase tracking-wider block mb-1.5">Notes (Optional)</label>
                    <textarea name="notes" id="edit_notes" rows="3" placeholder="Additional notes about this holiday..."
                        class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all resize-none"></textarea>
                </div>

                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-xl p-3 text-[11px] font-medium text-amber-700 dark:text-amber-400">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    If the new dates affect existing appointments, patients will be <strong>notified</strong>. Monthly schedules remain unchanged.
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-brand-dark dark:text-white text-xs font-bold rounded-xl transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)]">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Update Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- NOTES VIEW MODAL -->
    <div id="notesModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-sm mx-4 shadow-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-extrabold text-brand-dark dark:text-white"><i class="fa-regular fa-note-sticky text-brand-pink mr-2"></i> Holiday Notes</h3>
                <button onclick="closeNotesModal()" class="text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <p id="notesContent" class="text-xs font-medium text-slate-600 dark:text-gray-300 leading-relaxed bg-slate-50 dark:bg-gray-800 p-4 rounded-xl border border-slate-100 dark:border-gray-700"></p>
            <div class="flex justify-end pt-4">
                <button onclick="closeNotesModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-brand-dark dark:text-white text-xs font-bold rounded-xl transition-all">Close</button>
            </div>
        </div>
    </div>

    <!-- DELETE CONFIRM MODAL -->
    <div id="deleteModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-sm mx-4 shadow-2xl p-6 text-center">
            <div class="w-14 h-14 mx-auto bg-red-50 dark:bg-red-900/20 rounded-2xl flex items-center justify-center text-red-500 text-2xl mb-4">
                <i class="fa-regular fa-trash-can"></i>
            </div>
            <h3 class="text-base font-extrabold text-brand-dark dark:text-white mb-2">Delete Holiday?</h3>
            <p class="text-xs font-medium text-brand-muted dark:text-gray-300 mb-6">This action cannot be undone. Are you sure you want to delete this holiday entry?</p>
            <div class="flex justify-center gap-3">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-brand-dark dark:text-white text-xs font-bold rounded-xl transition-all">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(239,68,68,0.25)]">
                    <i class="fa-solid fa-trash-can mr-1"></i> Delete
                </a>
            </div>
        </div>
    </div>

    <script>
        const holidaysData = <?= $holidaysData_json ?>;
        const doctorsData = <?= $doctors_json ?>;

        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function openEditModal(id) {
            const holiday = holidaysData.find(h => h.id == id);
            if (!holiday) return;

            document.getElementById('edit_holiday_id').value = holiday.id;
            document.getElementById('edit_start_date').value = holiday.start_date;
            document.getElementById('edit_end_date').value = holiday.end_date;
            document.getElementById('edit_reason').value = holiday.reason;
            document.getElementById('edit_notes').value = holiday.notes || '';

            // Populate doctor dropdown
            const doctorSelect = document.getElementById('edit_doctor_id');
            doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
            doctorsData.forEach(doc => {
                const opt = document.createElement('option');
                opt.value = doc.id;
                opt.textContent = doc.name;
                if (doc.id == holiday.doctor_id) {
                    opt.selected = true;
                    document.getElementById('edit_doctor_name').value = doc.name;
                }
                doctorSelect.appendChild(opt);
            });

            // Set min for end date
            document.getElementById('edit_end_date').min = holiday.start_date;

            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function viewNotes(notes) {
            document.getElementById('notesContent').textContent = notes;
            document.getElementById('notesModal').classList.remove('hidden');
        }

        function closeNotesModal() {
            document.getElementById('notesModal').classList.add('hidden');
        }

        function confirmDelete(id) {
            document.getElementById('deleteConfirmBtn').href = 'doctor_holiday.php?delete=' + id;
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

        // Date validation - end date must be >= start date
        document.getElementById('create_start_date').addEventListener('change', function() {
            const endInput = document.getElementById('create_end_date');
            endInput.min = this.value;
            if (endInput.value && endInput.value < this.value) {
                endInput.value = this.value;
            }
        });

        document.getElementById('edit_start_date').addEventListener('change', function() {
            const endInput = document.getElementById('edit_end_date');
            endInput.min = this.value;
            if (endInput.value && endInput.value < this.value) {
                endInput.value = this.value;
            }
        });
    </script>

</body>
</html>
