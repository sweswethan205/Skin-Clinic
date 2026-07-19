<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_id'])) {
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $schedule_id = intval($_POST['schedule_id']);
    $treatment_id = isset($_SESSION['booking_treatment_id']) ? intval($_SESSION['booking_treatment_id']) : 0;
    $payment_method_val = $_POST['payment_method'] ?? '';
    $appointment_start = $_POST['appointment_start'] ?? $_SESSION['booking_start_time'] ?? '';
    $appointment_end = $_POST['appointment_end'] ?? $_SESSION['booking_end_time'] ?? '';

    $errors = [];

    if ($user_id <= 0) {
        $errors[] = 'You must be logged in to book an appointment.';
    }
    if ($schedule_id <= 0) {
        $errors[] = 'Invalid schedule selection.';
    }
    if ($treatment_id <= 0) {
        $errors[] = 'Invalid treatment selection.';
    }

    $method_map = [
        'kbz_pay' => 'KBZ Pay',
        'wave_pay' => 'Wave Pay',
        'cb_pay' => 'CB Pay',
        'aya_pay' => 'AYA Pay', // 👈 Updated to AYA Pay
    ];
    $method_name = $method_map[$payment_method_val] ?? '';
    $payment_method_id = 0;
    if ($method_name) {
        $pm_query = "SELECT id FROM payment_methods WHERE method_name LIKE ? LIMIT 1";
        if ($pm_stmt = $conn->prepare($pm_query)) {
            $like_name = '%' . $method_name . '%';
            $pm_stmt->bind_param("s", $like_name);
            $pm_stmt->execute();
            $pm_result = $pm_stmt->get_result();
            if ($pm_row = $pm_result->fetch_assoc()) {
                $payment_method_id = $pm_row['id'];
            }
            $pm_stmt->close();
        }
    }
    if ($payment_method_id <= 0) {
        $payment_method_id = 1;
    }

    $receipt_path = '';
    if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/receipts/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['png', 'jpg', 'jpeg'];
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Only PNG, JPG, JPEG files are allowed.';
        }
        if ($_FILES['receipt_image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'File size must be under 5MB.';
        }
        if (empty($errors)) {
            $receipt_path = $upload_dir . 'receipt_' . time() . '_' . $user_id . '.' . $ext;
            move_uploaded_file($_FILES['receipt_image']['tmp_name'], $receipt_path);
        }
    }

    if (empty($errors)) {
        $conn->begin_transaction();

        // Lock schedule row to prevent concurrent booking races
        $lock_sched = $conn->prepare("SELECT id FROM schedules WHERE id = ? FOR UPDATE");
        $lock_sched->bind_param("i", $schedule_id);
        $lock_sched->execute();
        $lock_sched->close();

        // Doctor overlap check (inside transaction with lock)
        $overlap = $conn->prepare("SELECT id FROM appointments WHERE schedule_id = ? AND status != 'cancelled' AND appointment_start < ? AND appointment_end > ? LIMIT 1 FOR UPDATE");
        $overlap->bind_param("sss", $schedule_id, $appointment_end, $appointment_start);
        $overlap->execute();
        $overlap_res = $overlap->get_result();
        $overlap_exists = $overlap_res->fetch_assoc();
        $overlap->close();
        if ($overlap_exists) {
            $conn->rollback();
            $errors[] = 'This time slot overlaps with an existing appointment. Please choose another.';
        }

        if (empty($errors)) {
            $dup = $conn->prepare("SELECT id FROM appointments WHERE user_id = ? AND schedule_id = ? AND status != 'cancelled' AND appointment_start < ? AND appointment_end > ? LIMIT 1");
            $dup->bind_param("isss", $user_id, $schedule_id, $appointment_end, $appointment_start);
            $dup->execute();
            $dup_res = $dup->get_result();
            $dup_exists = $dup_res->fetch_assoc();
            $dup->close();
            if ($dup_exists) {
                $conn->rollback();
                $errors[] = 'You already have a booking for this time slot.';
            }
        }
    }

    // --- AUTO-ASSIGN ROOM (inside transaction) ---
    $assigned_room_id = null;
    if (empty($errors) && $treatment_id > 0) {
        $date_query = $conn->prepare("SELECT available_date FROM schedules WHERE id = ? LIMIT 1");
        $date_query->bind_param("i", $schedule_id);
        $date_query->execute();
        $date_result = $date_query->get_result();
        $date_row = $date_result->fetch_assoc();
        $date_query->close();
        $available_date = $date_row['available_date'] ?? '';

        if (!empty($available_date) && !empty($appointment_start) && !empty($appointment_end)) {
            $room_query = $conn->prepare(
                "SELECT r.id, r.capacity FROM rooms r 
                 JOIN treatment_rooms tr ON tr.room_id = r.id 
                 WHERE tr.treatment_id = ? AND r.status = 'active' 
                 ORDER BY r.room_number ASC"
            );
            $room_query->bind_param("i", $treatment_id);
            $room_query->execute();
            $room_result = $room_query->get_result();

            while ($room_row = $room_result->fetch_assoc()) {
                $candidate_room_id = $room_row['id'];
                $room_capacity = intval($room_row['capacity']);

                // Lock overlapping appointments in this room to prevent race condition
                $room_check = $conn->prepare(
                    "SELECT COUNT(*) AS booked FROM appointments 
                     WHERE room_id = ? AND status != 'cancelled' 
                     AND appointment_start < ? AND appointment_end > ? 
                     AND schedule_id IN (SELECT id FROM schedules WHERE available_date = ?) FOR UPDATE"
                );
                $room_check->bind_param("isss", $candidate_room_id, $appointment_end, $appointment_start, $available_date);
                $room_check->execute();
                $room_check_result = $room_check->get_result();
                $booked_row = $room_check_result->fetch_assoc();
                $room_check->close();

                $booked_count = intval($booked_row['booked']);

                if ($booked_count < $room_capacity) {
                    $assigned_room_id = $candidate_room_id;
                    break;
                }
            }
            $room_query->close();
        }

        if ($assigned_room_id === null) {
            $conn->rollback();
            $errors[] = 'No treatment rooms are available for this time slot. Please try a different time.';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, treatment_id, schedule_id, room_id, payment_method_id, appointment_start, appointment_end, receipt_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iiiissss", $user_id, $treatment_id, $schedule_id, $assigned_room_id, $payment_method_id, $appointment_start, $appointment_end, $receipt_path);
        if ($stmt->execute()) {
            $appointment_id = $stmt->insert_id;
            $stmt->close();

            $title = 'New Appointment';
            $room_label = '';
            if ($assigned_room_id) {
                $rm_q = $conn->prepare("SELECT room_name, room_number FROM rooms WHERE id = ? LIMIT 1");
                $rm_q->bind_param("i", $assigned_room_id);
                $rm_q->execute();
                $rm_r = $rm_q->get_result()->fetch_assoc();
                $rm_q->close();
                if ($rm_r) $room_label = ' in ' . $rm_r['room_number'] . ' (' . $rm_r['room_name'] . ')';
            }
            $msg = "User #$user_id booked appointment #$appointment_id{$room_label}.";
            $target = 'admin';
            $notif = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, title, message, type, target_role) VALUES (?, ?, ?, ?, 'booking', ?)");
            $notif->bind_param("iisss", $user_id, $appointment_id, $title, $msg, $target);
            $notif->execute();
            $notif->close();

            $conn->commit();
            unset($_SESSION['booking_schedule_id'], $_SESSION['booking_treatment_id'], $_SESSION['booking_start_time'], $_SESSION['booking_end_time']);
            header("Location: my-bookings.php?msg=" . urlencode("Appointment booked successfully!") . "&type=success");
            exit;
        } else {
            $conn->rollback();
            $errors[] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
    }

    if (!empty($errors)) {
        $error_msg = implode(' ', $errors);
        header("Location: payment.php?schedule_id=$schedule_id&msg=" . urlencode($error_msg) . "&type=error");
        exit;
    }
}

$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : (isset($_SESSION['booking_schedule_id']) ? intval($_SESSION['booking_schedule_id']) : 0);
$treatment_id = isset($_SESSION['booking_treatment_id']) ? intval($_SESSION['booking_treatment_id']) : 0;

if (isset($_GET['start_time'])) {
    $_SESSION['booking_start_time'] = $_GET['start_time'];
}
if (isset($_GET['end_time'])) {
    $_SESSION['booking_end_time'] = $_GET['end_time'];
}
$booking_start_time = $_SESSION['booking_start_time'] ?? '';
$booking_end_time = $_SESSION['booking_end_time'] ?? '';

if ($schedule_id > 0) {
    $_SESSION['booking_schedule_id'] = $schedule_id;
}

$booking = [];

if ($schedule_id > 0) {
    $query = "SELECT 
                s.id AS schedule_id,
                s.available_date,
                s.start_time,
                d.id AS doctor_id,
                d.name AS doctor_name
              FROM schedules s
              JOIN doctors d ON d.id = s.doctor_id
              WHERE s.id = ?
              LIMIT 1";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $schedule_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();
        }
        $stmt->close();
    }

    if ($booking) {
        if ($treatment_id > 0) {
            $t_query = "SELECT treatment_name, price FROM treatments WHERE id = ? LIMIT 1";
            if ($t_stmt = $conn->prepare($t_query)) {
                $t_stmt->bind_param("i", $treatment_id);
                if ($t_stmt->execute()) {
                    $t_result = $t_stmt->get_result();
                    if ($t_row = $t_result->fetch_assoc()) {
                        $booking['treatment_name'] = $t_row['treatment_name'];
                        $booking['price'] = $t_row['price'];
                    }
                }
                $t_stmt->close();
            }
        }
        if (empty($booking['treatment_name'])) {
            $dt_query = "SELECT t.treatment_name, t.price 
                         FROM doctor_treatments dt 
                         JOIN treatments t ON t.id = dt.treatment_id 
                         WHERE dt.doctor_id = ? 
                         LIMIT 1";
            if ($dt_stmt = $conn->prepare($dt_query)) {
                $dt_stmt->bind_param("i", $booking['doctor_id']);
                if ($dt_stmt->execute()) {
                    $dt_result = $dt_stmt->get_result();
                    if ($dt_row = $dt_result->fetch_assoc()) {
                        $booking['treatment_name'] = $dt_row['treatment_name'];
                        $booking['price'] = $dt_row['price'];
                    }
                }
                $dt_stmt->close();
            }
        }
    }
}

$user_name = '';
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $u_query = "SELECT name FROM users WHERE id = ?";
    if ($u_stmt = $conn->prepare($u_query)) {
        $u_stmt->bind_param("i", $uid);
        $u_stmt->execute();
        $u_result = $u_stmt->get_result();
        if ($u_row = $u_result->fetch_assoc()) {
            $user_name = $u_row['name'];
        }
        $u_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - GlowSkin Skin Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght=0,600;0,700;1,400&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            pink: '#FF6584',
                            lightPink: '#FFF0F2',
                            dark: '#2D2D2D',
                            textMuted: '#666666'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-[#FAF9F6] dark:bg-gray-950 font-sans text-brand-dark dark:text-gray-100 antialiased min-h-screen flex flex-col justify-between relative">
    <?php include '../includes/header.php'; ?>

    <header class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 py-4 px-6 shadow-xs">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="booking.php" class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-brand-textMuted hover:text-brand-pink transition">
                <i class="fa-solid fa-arrow-left"></i> Back to Schedule
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto w-full px-6 py-12 flex-grow">

        <?php if (isset($_GET['msg'])): ?>
            <div class="mb-6 p-4 rounded-xl text-sm font-medium <?= isset($_GET['type']) && $_GET['type'] === 'error' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' ?>">
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <div class="mb-10">
            <h1 class="font-serif text-3xl md:text-4xl font-bold tracking-tight mb-2">Secure Payment Method</h1>
            <p class="text-sm text-brand-textMuted dark:text-gray-400">Choose your preferred transaction system to securely hold your clinical session slot.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <div class="lg:col-span-7 space-y-6">

                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-[0_10px_30px_rgba(0,0,0,0.01)] relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-brand-lightPink text-brand-pink text-[10px] font-bold uppercase tracking-widest px-4 py-1.5 rounded-bl-xl border-l border-b border-pink-100/30">
                        Confirmed Slot
                    </div>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-brand-pink mb-4">Booking Summary</h2>

                    <div class="grid grid-cols-2 gap-y-4 gap-x-2">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Selected Treatment</span>
                            <span class="text-sm font-semibold text-brand-dark dark:text-white"><?= htmlspecialchars($booking['treatment_name'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Assigned Doctor</span>
                            <span class="text-sm font-semibold text-brand-dark dark:text-white">Dr. <?= htmlspecialchars($booking['doctor_name'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Patient Name</span>
                            <span class="text-sm font-semibold text-brand-dark dark:text-white"><?= htmlspecialchars($user_name ?: 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Date & Timestamp</span>
                            <span class="text-xs font-medium text-brand-textMuted dark:text-gray-400"><?= isset($booking['available_date']) ? date("M d, Y", strtotime($booking['available_date'])) : 'N/A' ?> - <?= $booking_start_time ? date("h:i A", strtotime($booking_start_time)) : 'N/A' ?> to <?= $booking_end_time ? date("h:i A", strtotime($booking_end_time)) : 'N/A' ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Total Value</span>
                            <span class="text-sm font-bold text-brand-pink">$<?= isset($booking['price']) ? number_format($booking['price'], 2) : '0.00' ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Treatment Room</span>
                            <span class="text-xs font-semibold text-emerald-600"><i class="fa-solid fa-circle-check mr-1 text-[9px]"></i>Auto-assigned at booking</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-gray-400 mb-4 block">Payment Method</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="payment-methods-container">

                        <label data-method="kbz_pay" class="payment-method-card flex items-center gap-4 p-5 rounded-xl cursor-pointer transition-all duration-200 group border-2 border-[#005BAa] bg-[#005BAa] text-white">
                            <input type="radio" name="payment_method" value="kbz_pay" checked class="hidden">
                            <div class="w-12 h-12 rounded-lg bg-white dark:bg-gray-100 overflow-hidden flex items-center justify-center shrink-0 shadow-xs">
                                <img src="../assets/images/kpay.png" alt="KBZ Pay" class="w-full h-full object-cover">
                            </div>
                            <span class="text-sm font-semibold tracking-wide transition-colors">KBZPay</span>
                        </label>

                        <label data-method="wave_pay" class="payment-method-card flex items-center gap-4 p-5 rounded-xl cursor-pointer transition-all duration-200 group border border-gray-200/80 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300">
                            <input type="radio" name="payment_method" value="wave_pay" class="hidden">
                            <div class="w-12 h-12 rounded-lg bg-white dark:bg-gray-100 overflow-hidden flex items-center justify-center shrink-0 shadow-xs">
                                <img src="../assets/images/wave.png" alt="Wave Pay" class="w-full h-full object-cover">
                            </div>
                            <span class="text-sm font-semibold tracking-wide transition-colors">WavePay</span>
                        </label>

                        <label data-method="cb_pay" class="payment-method-card flex items-center gap-4 p-5 rounded-xl cursor-pointer transition-all duration-200 group border border-gray-200/80 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300">
                            <input type="radio" name="payment_method" value="cb_pay" class="hidden">
                            <div class="w-12 h-12 rounded-lg bg-white dark:bg-gray-100 overflow-hidden flex items-center justify-center shrink-0 shadow-xs">
                                <img src="../assets/images/cbpay.png" alt="CB Pay" class="w-full h-full object-cover">
                            </div>
                            <span class="text-sm font-semibold tracking-wide transition-colors">CBPay</span>
                        </label>

                        <label data-method="aya_pay" class="payment-method-card flex items-center gap-4 p-5 rounded-xl cursor-pointer transition-all duration-200 group border border-gray-200/80 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300">
                            <input type="radio" name="payment_method" value="aya_pay" class="hidden">
                            <div class="w-12 h-12 rounded-lg bg-white dark:bg-gray-100 overflow-hidden flex items-center justify-center shrink-0 shadow-xs">
                                <img src="../assets/images/aya.png" alt="AYA Pay" class="w-full h-full object-cover">
                            </div>
                            <span class="text-sm font-semibold tracking-wide transition-colors">AYAPay</span>
                        </label>

                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-[0_15px_40px_rgba(0,0,0,0.02)]">

                <div class="flex items-center gap-3 bg-[#FAFAF8] dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 mb-6">
                    <div class="text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 w-8 h-8 rounded-lg flex items-center justify-center text-xs">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-brand-dark dark:text-white">Secure Local Gateway</h4>
                        <p class="text-[10px] text-gray-400">Your payments are fully protected and encrypted.</p>
                    </div>
                </div>

                <form class="space-y-4" id="payment-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="schedule_id" value="<?= $schedule_id ?>">
                    <input type="hidden" name="payment_method" id="payment_method_hidden" value="kbz_pay">
                    <input type="hidden" name="appointment_start" value="<?= htmlspecialchars($_GET['start_time'] ?? $booking_start_time ?? '') ?>">
                    <input type="hidden" name="appointment_end" value="<?= htmlspecialchars($_GET['end_time'] ?? $booking_end_time ?? '') ?>">

                    <div id="qr-scan-window" class="bg-[#005BAa]/5 rounded-2xl p-4 border border-[#005BAa]/20 text-center space-y-3 transition-colors duration-200">
                        <div class="flex items-center justify-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#005BAa]">
                            <i class="fa-solid fa-qrcode text-xs"></i> <span>Scan QR Code</span>
                        </div>

                        <!-- Account Info -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-200/60 dark:border-gray-700 space-y-2">
                            <div class="flex items-center justify-center gap-2">
                                <i class="fa-solid fa-user text-[10px] text-[#005BAa]"></i>
                                <span class="text-xs font-bold text-brand-dark dark:text-white">GlowSkin Clinic</span>
                            </div>
                            <div class="flex items-center justify-center gap-2">
                                <i class="fa-solid fa-phone text-[10px] text-[#005BAa]"></i>
                                <span id="account-phone" class="text-xs font-semibold text-brand-dark dark:text-white">09-123456789</span>
                                <button type="button" onclick="copyPhoneNumber()" class="text-[#005BAa] hover:text-[#004080] transition-colors" title="Copy phone number">
                                    <i class="fa-regular fa-copy text-[10px]"></i>
                                </button>
                            </div>
                            <div id="copy-success" class="hidden text-[10px] text-emerald-500 font-medium">
                                <i class="fa-solid fa-check mr-1"></i> Copied!
                            </div>
                        </div>

                        <div class="w-40 h-40 mx-auto bg-white dark:bg-gray-800 border border border-gray-200/60 dark:border-gray-700 rounded-xl p-2.5 flex items-center justify-center shadow-xs relative group">
                            <img id="dynamic-qr-image" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=GlowSkin_<?= htmlspecialchars($user_name ?: 'Guest') ?>_Deposit" alt="Payment Scan Terminal" class="w-full h-full object-contain">
                        </div>

                        <p class="text-[11px] text-brand-textMuted dark:text-gray-400 leading-normal">
                            Open your <span id="active-wallet-label" class="font-bold text-[#005BAa] transition-colors duration-200">KBZPay</span> app and scan this secure dynamic invoice window to finalize your checkout balance instantly.
                        </p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-brand-textMuted dark:text-gray-400 mb-1.5">Upload Receipt Screenshot</label>
                        <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-gray-200 dark:border-gray-700 border-dashed rounded-xl cursor-pointer bg-white dark:bg-gray-800 hover:bg-slate-50/50 dark:hover:bg-gray-700 transition relative overflow-hidden group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4" id="upload-placeholder">
                                <i class="fa-solid fa-cloud-arrow-up text-gray-400 text-xl mb-2 group-hover:text-brand-pink transition-colors"></i>
                                <p class="text-[11px] text-gray-500 font-medium"><span class="text-brand-pink font-semibold">Click to upload</span> or drag and drop</p>
                                <p class="text-[9px] text-gray-400 mt-0.5">PNG, JPG or JPEG up to 5MB</p>
                            </div>
                            <input type="file" id="receipt-upload" name="receipt_image" accept="image/*" required class="hidden" />
                            <img id="receipt-preview" class="absolute inset-0 w-full h-full object-contain hidden p-4" alt="Receipt Preview">
                        </label>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-800 pt-5 mt-6 space-y-2.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-brand-textMuted dark:text-gray-400 font-light">Consultation Base Fee</span>
                            <span class="font-medium text-brand-dark dark:text-white">$<?= isset($booking['price']) ? number_format($booking['price'], 2) : '0.00' ?></span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-brand-textMuted dark:text-gray-400 font-light">Service Processing Surcharge</span>
                            <span class="font-medium text-emerald-500">FREE</span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-dashed border-gray-100 dark:border-gray-700">
                            <span class="font-serif text-sm font-bold text-brand-dark dark:text-white">Total Net Payable</span>
                            <span class="text-xl font-bold text-brand-pink">$<?= isset($booking['price']) ? number_format($booking['price'], 2) : '0.00' ?></span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-4">
                        <button type="submit" class="w-full bg-brand-pink text-white text-xs font-semibold tracking-wider uppercase py-4 rounded-xl shadow-md shadow-pink-100 hover:bg-opacity-95 transition-all flex items-center justify-center gap-2 hover:bg-pink-600">
                            <i class="fa-solid fa-circle-check text-xs"></i> Payment
                        </button>

                        <a href="../user/booking.php" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-brand-textMuted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white text-xs font-semibold tracking-wider uppercase py-3.5 rounded-xl transition-all flex items-center justify-center gap-2">
                            Cancel Process
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </main>

    <div id="success-modal" class="fixed inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center z-50 p-4 opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl transform scale-95 transition-transform duration-300">
            <div class="w-16 h-16 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 border border-emerald-100 dark:border-emerald-800">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <h3 class="font-serif text-2xl font-bold text-brand-dark dark:text-white mb-2">Booking Successful!</h3>
            <p class="text-xs text-brand-textMuted dark:text-gray-400 mb-6 leading-relaxed">Your professional clinic treatment session has been locked. Check your email for appointment notification receipts.</p>

            <button onclick="closeModal()" class="w-full bg-brand-pink hover:bg-opacity-95 text-white text-xs font-bold tracking-wider uppercase py-3.5 rounded-xl transition">
                Perfect, Thank You
            </button>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.payment-method-card');
            const paymentForm = document.getElementById('payment-form');

            const activeWalletLabel = document.getElementById('active-wallet-label');
            const dynamicQrImage = document.getElementById('dynamic-qr-image');

            const receiptUploadInput = document.getElementById('receipt-upload');
            const receiptPreview = document.getElementById('receipt-preview');
            const uploadPlaceholder = document.getElementById('upload-placeholder');

            // Configurations mapping color shifts dynamically (with AYA Red style)
            const brandStyles = {
                kbz_pay: {
                    bg: 'bg-[#005BAa]',
                    border: 'border-[#005BAa]',
                    text: 'text-white',
                    qrBg: 'bg-[#005BAa]/5',
                    qrBorder: 'border-[#005BAa]/20',
                    qrText: 'text-[#005BAa]'
                },
                wave_pay: {
                    bg: 'bg-[#F9CC1A]',
                    border: 'border-[#F9CC1A]',
                    text: 'text-slate-900',
                    qrBg: 'bg-[#F9CC1A]/5',
                    qrBorder: 'border-[#F9CC1A]/20',
                    qrText: 'text-[#D4A500]'
                },
                cb_pay: {
                    bg: 'bg-[#006BB6]',
                    border: 'border-[#006BB6]',
                    text: 'text-white',
                    qrBg: 'bg-[#006BB6]/5',
                    qrBorder: 'border-[#006BB6]/20',
                    qrText: 'text-[#006BB6]'
                },
                aya_pay: {
                    bg: 'bg-[#E11C23]',
                    border: 'border-[#E11C23]',
                    text: 'text-white',
                    qrBg: 'bg-[#E11C23]/5',
                    qrBorder: 'border-[#E11C23]/20',
                    qrText: 'text-[#E11C23]'
                }
            };

            cards.forEach(card => {
                card.addEventListener('click', () => {
                    const selectedMethod = card.getAttribute('data-method');

                    // Clear styling on all payment options cards
                    cards.forEach(c => {
                        c.className = "payment-method-card flex items-center gap-4 p-5 rounded-xl cursor-pointer transition-all duration-200 group border border-gray-200/80 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300";
                    });

                    // Highlight selected card options
                    const style = brandStyles[selectedMethod];
                    card.className = `payment-method-card flex items-center gap-4 p-5 rounded-xl cursor-pointer transition-all duration-200 group border-2 ${style.border} ${style.bg} ${style.text}`;

                    const radio = card.querySelector('input[type="radio"]');
                    radio.checked = true;
                    document.getElementById('payment_method_hidden').value = selectedMethod;

                    // Sync the label string update inside description sentences
                    const currentLabelText = card.querySelector('span:last-child').innerText;
                    activeWalletLabel.innerText = currentLabelText;

                    // Dynamically modify text color on selection text
                    activeWalletLabel.className = `font-bold transition-colors duration-200 ${style.qrText}`;

                    dynamicQrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=GlowSkin_${selectedMethod}_Deposit_Token`;

                    // Update QR Layout Background and Header container styling classes
                    const qrContainer = document.getElementById('qr-scan-window');
                    const qrHeader = qrContainer.querySelector('.flex');

                    qrContainer.className = `${style.qrBg} rounded-2xl p-4 border ${style.qrBorder} text-center space-y-3 transition-colors duration-200`;
                    qrHeader.className = `flex items-center justify-center gap-1.5 text-[10px] font-bold uppercase tracking-wider ${style.qrText}`;
                });
            });

            receiptUploadInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.addEventListener('load', function() {
                        receiptPreview.setAttribute('src', this.result);
                        receiptPreview.classList.remove('hidden');
                        uploadPlaceholder.classList.add('hidden');
                    });
                    reader.readAsDataURL(file);
                }
            });

            paymentForm.addEventListener('submit', (event) => {
                const submitBtn = paymentForm.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Processing...';
            });
        });

        function closeModal() {
            const modal = document.getElementById('success-modal');
            const modalContent = modal.querySelector('.transform');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
        }

        function copyPhoneNumber() {
            const phoneElement = document.getElementById('account-phone');
            const phoneText = phoneElement.textContent.trim();
            navigator.clipboard.writeText(phoneText).then(() => {
                const successMsg = document.getElementById('copy-success');
                successMsg.classList.remove('hidden');
                setTimeout(() => {
                    successMsg.classList.add('hidden');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
    </script>
</body>

</html>