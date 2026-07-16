<?php
// Clear any accidental whitespace or output before this point
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../config/db.php';

// --- 1. HANDLE TIME SLOT AJAX REQUEST INLINE ---
if (isset($_GET['action']) && $_GET['action'] === 'get_timeslots') {
    ob_end_clean(); // Clear buffer to ensure ONLY pristine JSON is returned
    header('Content-Type: application/json');
    
    if (!isset($_GET['doctor_id']) || !isset($_GET['date'])) {
        echo json_encode([]);
        exit;
    }

    $doctor_id = intval($_GET['doctor_id']);
    $booking_date = $_GET['date'];

    $query = "SELECT id AS schedule_id, start_time, is_booked 
              FROM schedules 
              WHERE doctor_id = ? AND available_date = ? 
              ORDER BY start_time ASC";

    $slots = [];

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("is", $doctor_id, $booking_date);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $slots[] = [
                    'schedule_id' => $row['schedule_id'],
                    'time'        => date("h:i A", strtotime($row['start_time'])), 
                    'is_booked'   => ($row['is_booked'] === 'yes') 
                ];
            }
        }
        $stmt->close();
    }

    echo json_encode($slots);
    exit;
}

// --- HANDLE AVAILABLE DATES AJAX REQUEST ---
if (isset($_GET['action']) && $_GET['action'] === 'get_available_dates') {
    ob_end_clean();
    header('Content-Type: application/json');

    if (!isset($_GET['doctor_id'])) {
        echo json_encode([]);
        exit;
    }

    $doctor_id = intval($_GET['doctor_id']);

    $query = "SELECT DISTINCT available_date FROM schedules WHERE doctor_id = ? ORDER BY available_date ASC";
    $dates = [];

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $doctor_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $dates[] = $row['available_date'];
            }
        }
        $stmt->close();
    }

    echo json_encode($dates);
    exit;
}

// --- 2. STORE TREATMENT & SCHEDULE IN SESSION ---
if (isset($_GET['treatment_id'])) {
    $_SESSION['booking_treatment_id'] = intval($_GET['treatment_id']);
}

// --- 3. FETCH TREATMENT INFO FOR DISPLAY ---
$treatment_name = '';
$treatment_price = 0;
if (!empty($_SESSION['booking_treatment_id'])) {
    $tid = intval($_SESSION['booking_treatment_id']);
    $tq = $conn->prepare("SELECT treatment_name, price FROM treatments WHERE id = ? LIMIT 1");
    if ($tq) {
        $tq->bind_param("i", $tid);
        $tq->execute();
        $tr = $tq->get_result()->fetch_assoc();
        if ($tr) {
            $treatment_name = $tr['treatment_name'];
            $treatment_price = $tr['price'];
        }
        $tq->close();
    }
}

// --- 4. CHECK LOGIN STATUS ---
$is_logged_in = isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;

// --- 5. STANDARD PAGE DATA LOADING ---
$doctors = $conn->query("SELECT id, name, photo, description FROM doctors WHERE status='active' ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skin Clinic Booking</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            pink: '#E85D75',
                            navy: '#1F2937',
                            green: '#22C55E'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
        }
        .cal-day {
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            color: #94a3b8;
        }
        .cal-day.empty { pointer-events: none; }
        .cal-day.past { color: #e2e8f0; pointer-events: none; }
        .cal-day.available {
            color: #1e293b;
            background: #ffffff;
        }
        .cal-day.available:hover {
            background: #f1f5f9;
        }
        .cal-day.selected {
            background: #E85D75;
            color: #fff;
            border-color: #E85D75;
        }
        .cal-day.today {
            border: 2px solid #E85D75;
        }
        .cal-nav-btn {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.15s;
            color: #64748b;
            font-size: 12px;
        }
        .cal-nav-btn:hover { background: #f1f5f9; }
        .dark .cal-day { color: #94a3b8; }
        .dark .cal-day.past { color: #374151; }
        .dark .cal-day.available { color: #e2e8f0; background: #1f2937; }
        .dark .cal-day.available:hover { background: #374151; }
    </style>
</head>

<body class="bg-[#FAF9F6] dark:bg-gray-950 min-h-screen text-slate-800 dark:text-gray-100">
    <?php include '../includes/header.php'; ?>

    <div class="max-w-6xl mx-auto px-4 py-12">
        
        <div class="max-w-xl mx-auto mb-12">
            <div class="flex items-center justify-between relative">
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-[2px] bg-slate-200 z-0"></div>
                <div id="step-progress-line" class="absolute left-0 top-1/2 -translate-y-1/2 w-0 h-[2px] bg-brand-pink transition-all duration-500 z-0"></div>

                <div class="flex flex-col items-center relative z-10">
                    <div id="step1-icon" class="w-9 h-9 rounded-full bg-brand-pink text-white flex items-center justify-center font-bold shadow-md shadow-pink-200 text-sm">1</div>
                    <span class="text-xs font-semibold mt-2 text-slate-700 dark:text-gray-200">Doctor</span>
                </div>
                <div class="flex flex-col items-center relative z-10">
                    <div id="step2-icon" class="w-9 h-9 rounded-full bg-white dark:bg-gray-800 border-2 border-slate-200 dark:border-gray-600 text-slate-500 dark:text-gray-500 flex items-center justify-center font-bold text-sm">2</div>
                    <span class="text-xs font-semibold mt-2 text-slate-400 dark:text-gray-500">Date</span>
                </div>
                <div class="flex flex-col items-center relative z-10">
                    <div id="step3-icon" class="w-9 h-9 rounded-full bg-white dark:bg-gray-800 border-2 border-slate-200 dark:border-gray-600 text-slate-500 dark:text-gray-500 flex items-center justify-center font-bold text-sm">3</div>
                    <span class="text-xs font-semibold mt-2 text-slate-400 dark:text-gray-500">Time</span>
                </div>
            </div>
        </div>

        <div class="space-y-10">
            
            <div>
                <div class="mb-5">
                    <h2 class="text-xl font-bold tracking-tight text-slate-800 dark:text-white">Choose Your Doctor</h2>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-0.5">Select a dermatologist to view available consultation timelines.</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php if ($doctors && $doctors->num_rows > 0): ?>
                        <?php while ($doc = $doctors->fetch_assoc()): 
                            $photoPath = !empty($doc['photo']) ? $doc['photo'] : '';
                            if (!empty($photoPath) && strpos($photoPath, 'http') !== 0 && !file_exists($photoPath)) {
                                if (file_exists('../' . $photoPath)) {
                                    $photoPath = '../' . $photoPath;
                                }
                            }
                            $photo = !empty($photoPath) ? $photoPath : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                            $specialization = !empty($doc['description']) ? $doc['description'] : 'Dermatologist';
                        ?>
                        <button onclick="selectDoctor(this, <?= $doc['id'] ?>, '<?= htmlspecialchars($photo, ENT_QUOTES) ?>', '<?= htmlspecialchars($doc['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($specialization, ENT_QUOTES) ?>')"
                            class="doctor-card group bg-white dark:bg-gray-900 p-3 rounded-2xl border border-slate-200/60 dark:border-gray-700 text-left hover:shadow-lg relative">
                            <div class="absolute top-4 right-4 w-5 h-5 rounded-full border-2 border-slate-300 dark:border-gray-600 flex items-center justify-center bg-white dark:bg-gray-800 group-hover:border-brand-pink check-indicator">
                                <div class="w-2.5 h-2.5 rounded-full bg-brand-pink scale-0 inner-circle"></div>
                            </div>
                            <div class="aspect-[1/1] w-full rounded-xl overflow-hidden bg-slate-100 dark:bg-gray-700 mb-3">
                                <img src="<?= htmlspecialchars($photo) ?>" class="w-full h-full object-cover" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'">
                            </div>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-white group-hover:text-brand-pink truncate">Dr.<?= htmlspecialchars($doc['name']) ?></h3>
                            <p class="text-[11px] text-slate-400 dark:text-gray-500 font-medium mt-0.5 truncate"><?= htmlspecialchars($specialization) ?></p>
                        </button>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="col-span-4 text-center text-slate-400 dark:text-gray-500 py-8">No doctors available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-[24px] border border-slate-100 dark:border-gray-800 shadow-sm p-6 md:p-8">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-5 pb-3 border-b border-slate-100 dark:border-gray-700 flex items-center gap-2">
                    <i class="fa-regular fa-calendar-check text-brand-pink"></i>
                    Appointment Detail
                </h2>

                <div class="grid md:grid-cols-12 gap-6 items-start">
                    
                    <div class="md:col-span-4 bg-slate-50 border border-slate-100 dark:bg-gray-800 dark:border-gray-700 p-4 rounded-xl sticky top-4">
                        <?php if ($treatment_name): ?>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-2.5">Selected Treatment</p>
                        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-slate-200">
                            <div class="w-12 h-12 rounded-xl bg-brand-pink/10 flex items-center justify-center text-brand-pink text-lg">
                                <i class="fa-regular fa-spa"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm text-slate-800 dark:text-white truncate"><?= htmlspecialchars($treatment_name) ?></h3>
                                <p class="text-[11px] text-slate-500 dark:text-gray-400 font-medium">$<?= number_format($treatment_price, 2) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-2.5">Selected Professional</p>
                        <div class="flex items-center gap-3">
                            <img id="doctor-image" src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="w-12 h-12 rounded-full object-cover ring-4 ring-white dark:ring-gray-900 shadow-sm">
                            <div class="min-w-0">
                                <h3 id="display-name" class="font-bold text-sm text-slate-800 dark:text-white truncate">Select Doctor</h3>
                                <p id="display-title" class="text-[11px] text-slate-500 dark:text-gray-400 font-medium truncate">Choose specialist above</p>
                            </div>
                        </div>
                    </div>

                    <div id="calendar-section" class="md:col-span-4 opacity-40 pointer-events-none">
                        <h3 class="text-xs font-bold text-slate-700 dark:text-gray-200 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-pink"></span> Choose Date
                        </h3>
                        <div class="bg-slate-50/60 dark:bg-gray-800 p-3 rounded-xl border border-slate-100 dark:border-gray-700">
                            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-gray-700 p-3">
                                <div class="flex items-center justify-between mb-3">
                                    <button type="button" class="cal-nav-btn" onclick="calPrev()"><i class="fa-solid fa-chevron-left"></i></button>
                                    <span id="cal-month-label" class="text-xs font-bold text-slate-700 dark:text-gray-200"></span>
                                    <button type="button" class="cal-nav-btn" onclick="calNext()"><i class="fa-solid fa-chevron-right"></i></button>
                                </div>
                                <div class="cal-grid mb-1">
                                    <div class="text-center text-[10px] font-bold text-slate-400 dark:text-gray-500 py-1">Sun</div>
                                    <div class="text-center text-[10px] font-bold text-slate-400 dark:text-gray-500 py-1">Mon</div>
                                    <div class="text-center text-[10px] font-bold text-slate-400 dark:text-gray-500 py-1">Tue</div>
                                    <div class="text-center text-[10px] font-bold text-slate-400 dark:text-gray-500 py-1">Wed</div>
                                    <div class="text-center text-[10px] font-bold text-slate-400 dark:text-gray-500 py-1">Thu</div>
                                    <div class="text-center text-[10px] font-bold text-slate-400 dark:text-gray-500 py-1">Fri</div>
                                    <div class="text-center text-[10px] font-bold text-slate-400 dark:text-gray-500 py-1">Sat</div>
                                </div>
                                <div id="cal-days" class="cal-grid"></div>
                            </div>
                        </div>
                    </div>

                    <div id="time-slots-container" class="md:col-span-4 opacity-40 pointer-events-none">
                        <h3 class="text-xs font-bold text-slate-700 dark:text-gray-200 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-pink"></span> Choose Time
                        </h3>


                        <div id="booking-message" class="hidden mb-4 p-3 rounded-xl text-sm font-medium text-center"></div>


                        
                        <div id="slots-dynamic-grid" class="grid grid-cols-2 gap-2">
<p class="text-xs text-slate-400 dark:text-gray-500 text-center col-span-2 py-4">Choose a practitioner and date first.</p>
                        </div>

                        <div id="slots-legend" class="hidden gap-4 mt-4 text-[11px] text-slate-500 dark:text-gray-400 font-medium bg-slate-50 dark:bg-gray-800 p-2.5 rounded-xl border border-slate-100 dark:border-gray-700 justify-center">
                            <div class="flex items-center gap-1.5">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full"></div> Available
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="w-2 h-2 bg-blue-400 rounded-full"></div> Booked
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div id="auth-modal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-gray-900 rounded-[2rem] p-8 max-w-sm w-full text-center shadow-2xl border border-slate-100 dark:border-gray-700 relative">
                <button type="button" onclick="closeAuthModal()" class="absolute top-5 right-6 text-slate-400 dark:text-gray-500 hover:text-brand-pink p-1 rounded-full hover:bg-slate-50 dark:hover:bg-gray-800">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
                <div class="w-14 h-14 bg-pink-50 dark:bg-pink-900/20 rounded-full flex items-center justify-center text-brand-pink mx-auto mb-4">
                    <i class="fa-solid fa-user-lock text-xl"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Account Required</h2>
                <p class="text-sm text-slate-500 dark:text-gray-400 mb-6 leading-relaxed">Please log in or sign up to finalize your slot verification details.</p>
                <div class="flex flex-col gap-3">
                    <a id="auth-signin-btn" href="../auth/login.php" class="bg-brand-pink text-white py-3 rounded-xl font-semibold text-sm shadow-lg shadow-pink-200 hover:bg-opacity-95 text-center">Login</a>
                    <a id="auth-signup-btn" href="../auth/re.php" class="bg-slate-50 text-slate-700 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 py-3 rounded-xl font-semibold text-sm border border-slate-200 hover:bg-slate-100 text-center">Register</a>
                </div>
            </div>
        </div>

    </div>

    <script>
        let availableDates = [];
        let selectedDoctorId = "";
        let selectedDateString = "";
        let selectedTimeButton = null;
        let userLoggedIn = <?= $is_logged_in ? 'true' : 'false' ?>;

        const calToday = new Date();
        let calMonth = calToday.getMonth();
        let calYear = calToday.getFullYear();
        const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];

        function renderCalendar() {
            document.getElementById('cal-month-label').textContent = monthNames[calMonth] + ' ' + calYear;
            const container = document.getElementById('cal-days');
            container.innerHTML = '';

            const firstDay = new Date(calYear, calMonth, 1).getDay();
            const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
            const todayStr = calToday.getFullYear() + '-' + String(calToday.getMonth() + 1).padStart(2, '0') + '-' + String(calToday.getDate()).padStart(2, '0');

            for (let i = 0; i < firstDay; i++) {
                const empty = document.createElement('div');
                empty.className = 'cal-day empty';
                container.appendChild(empty);
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = calYear + '-' + String(calMonth + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                const btn = document.createElement('div');
                btn.className = 'cal-day';
                btn.textContent = d;

                if (dateStr < todayStr) {
                    btn.classList.add('past');
                } else {
                    if (dateStr === todayStr) {
                        btn.classList.add('today');
                    }
                    if (availableDates.includes(dateStr)) {
                        btn.classList.add('available');
                        btn.onclick = function() { selectDate(dateStr, btn); };
                    }
                }

                if (dateStr === selectedDateString) {
                    btn.classList.add('selected');
                }

                container.appendChild(btn);
            }
        }

        function selectDate(dateStr, btn) {
            document.querySelectorAll('.cal-day.selected').forEach(el => el.classList.remove('selected'));
            btn.classList.add('selected');
            selectedDateString = dateStr;

            document.getElementById('time-slots-container').classList.remove('opacity-40', 'pointer-events-none');
            document.getElementById('step-progress-line').style.width = '100%';
            document.getElementById('step3-icon').classList.replace('bg-white', 'bg-brand-pink');
            document.getElementById('step3-icon').classList.replace('text-slate-500', 'text-white');
            document.getElementById('step3-icon').classList.add('shadow-md', 'shadow-pink-200');

            loadAvailableTimeSlots();
        }

        function calPrev() {
            calMonth--;
            if (calMonth < 0) { calMonth = 11; calYear--; }
            renderCalendar();
        }

        function calNext() {
            calMonth++;
            if (calMonth > 11) { calMonth = 0; calYear++; }
            renderCalendar();
        }

        renderCalendar();

        function selectDoctor(element, doctorId, image, name, title) {
            document.querySelectorAll('.doctor-card').forEach(card => {
                card.classList.remove('border-brand-pink', 'ring-2', 'ring-pink-100', 'bg-pink-50/10');
                const indicator = card.querySelector('.check-indicator');
                indicator.classList.remove('border-brand-pink');
                indicator.classList.add('border-slate-300');
                card.querySelector('.inner-circle').classList.add('scale-0');
            });

            element.classList.add('border-brand-pink', 'ring-2', 'ring-pink-100', 'bg-pink-50/10');
            
            const activeIndicator = element.querySelector('.check-indicator');
            activeIndicator.classList.remove('border-slate-300');
            activeIndicator.classList.add('border-brand-pink');
            element.querySelector('.inner-circle').classList.remove('scale-0');

            selectedDoctorId = doctorId;

            document.getElementById('doctor-image').src = image;
            document.getElementById('display-name').innerText = name;
            document.getElementById('display-title').innerText = title;

            document.getElementById('calendar-section').classList.remove('opacity-40', 'pointer-events-none');
            document.getElementById('step-progress-line').style.width = '50%';
            document.getElementById('step2-icon').classList.replace('bg-white', 'bg-brand-pink');
            document.getElementById('step2-icon').classList.replace('text-slate-500', 'text-white');
            document.getElementById('step2-icon').classList.add('shadow-md', 'shadow-pink-200');

            // Reset time section
            document.getElementById('time-slots-container').classList.add('opacity-40', 'pointer-events-none');
            document.getElementById('step3-icon').classList.replace('bg-brand-pink', 'bg-white');
            document.getElementById('step3-icon').classList.replace('text-white', 'text-slate-500');
            document.getElementById('step3-icon').classList.remove('shadow-md', 'shadow-pink-200');
            document.getElementById('slots-dynamic-grid').innerHTML = '<p class="text-xs text-slate-400 text-center col-span-2 py-4">Choose a practitioner and date first.</p>';
            document.getElementById('slots-legend').classList.add('hidden');
            document.getElementById('slots-legend').classList.remove('flex');

            selectedDateString = "";
            selectedTimeButton = null;

            document.querySelectorAll('.cal-day.selected').forEach(el => el.classList.remove('selected'));
            calMonth = calToday.getMonth();
            calYear = calToday.getFullYear();
            fetchAvailableDates(doctorId);
        }

        function fetchAvailableDates(doctorId) {
            fetch(`${window.location.pathname}?action=get_available_dates&doctor_id=${doctorId}`)
                .then(response => {
                    if (!response.ok) throw new Error("HTTP error");
                    return response.json();
                })
                .then(dates => {
                    availableDates = dates;
                    renderCalendar();
                })
                .catch(err => {
                    console.error("Failed to load available dates:", err);
                });
        }

        function loadAvailableTimeSlots() {
            if (selectedDoctorId == "" || selectedDateString == "")
                return;

            let grid = document.getElementById("slots-dynamic-grid");
            let legend = document.getElementById("slots-legend");
            grid.innerHTML = "<p class='text-center col-span-2 text-sm text-slate-400'>Loading...</p>";

            fetch(`${window.location.pathname}?action=get_timeslots&doctor_id=${selectedDoctorId}&date=${selectedDateString}`)
                .then(response => {
                    if (!response.ok) throw new Error("HTTP error status");
                    return response.json();
                })
                .then(slots => {
                    grid.innerHTML = "";

                    // if (slots.length === 0) {
                    //     grid.innerHTML = "<p class='text-slate-400 col-span-2 text-center text-sm'>No available schedule for this date.</p>";
                    //     legend.classList.add('hidden');
                    //     return;
                    // }

                    const message = document.getElementById("booking-message");

if (slots.length === 0) {

    message.classList.remove("hidden");
    message.className = "mb-4 p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 text-sm font-medium text-center";
    message.innerHTML = "❌No available schedule for this date.";

    grid.innerHTML = "";
    legend.classList.add("hidden");
    return;
}

// Hide message when slots are available
message.classList.add("hidden");

                    legend.classList.remove('hidden');
                    legend.classList.add('flex');

                    slots.forEach(item => {
                        let btn = document.createElement("button");
                        btn.type = "button";

                        if (item.is_booked) {
                            btn.disabled = true;
                            btn.className = "w-full py-2.5 bg-blue-50/70 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 text-blue-400 dark:text-blue-500 font-medium rounded-xl text-xs cursor-not-allowed flex items-center justify-center gap-1.5";
                            btn.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-blue-300"></span> ${item.time}`;
                        } else {
                            btn.className = "w-full py-2.5 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400 font-semibold rounded-xl text-xs flex items-center justify-center gap-1.5 time-slot-btn";
                            btn.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 indicator-dot"></span> ${item.time}`;
                            btn.onclick = function() {
                                handleBooking(this, item.schedule_id);
                            };
                        }

                        grid.appendChild(btn);
                    });
                })
                .catch((err) => {
                    console.error(err);
                    grid.innerHTML = "<p class='text-rose-500 col-span-2 text-center text-xs font-mono bg-rose-50 border border-rose-100 p-2 rounded-xl'>Failed to read schedule data structure format.</p>";
                    legend.classList.add('hidden');
                });
        }

        function handleBooking(btnElement, scheduleId) {
            if(selectedTimeButton) {
                selectedTimeButton.classList.remove('bg-brand-pink', 'text-white', 'border-brand-pink');
                selectedTimeButton.classList.add('bg-emerald-50', 'text-emerald-800', 'border-emerald-200', 'hover:bg-emerald-100');
                selectedTimeButton.querySelector('.indicator-dot').classList.replace('bg-white', 'bg-emerald-500');
            }

            selectedTimeButton = btnElement;
            btnElement.classList.remove('bg-emerald-50', 'text-emerald-800', 'border-emerald-200', 'hover:bg-emerald-100');
            btnElement.classList.add('bg-brand-pink', 'text-white', 'border-brand-pink');
            btnElement.querySelector('.indicator-dot').classList.replace('bg-emerald-500', 'bg-white');

            setTimeout(() => {
                if (userLoggedIn) {
                    window.location.href = '../user/payment.php?schedule_id=' + scheduleId;
                } else {
                    document.getElementById('auth-signin-btn').href = '../auth/login.php?schedule_id=' + scheduleId + '&redirect=../user/payment.php';
                    document.getElementById('auth-signup-btn').href = '../auth/re.php?schedule_id=' + scheduleId + '&redirect=../user/payment.php';
                    document.getElementById('auth-modal').classList.remove('hidden');
                }
            }, 150);
        }

        function closeAuthModal() {
            document.getElementById('auth-modal').classList.add('hidden');
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>