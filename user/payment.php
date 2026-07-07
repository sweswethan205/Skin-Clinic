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

    // Map form payment method to payment_methods table
    $method_map = [
        'kbz_pay' => 'KBZ Pay',
        'wave_pay' => 'Wave Pay',
        'cb_pay' => 'CB Pay',
        'trusty_pay' => 'Trusty Pay',
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
        $payment_method_id = 1; // fallback to first method
    }

    // Handle receipt upload
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
        // Check if slot is already booked by another user
        $chk = $conn->prepare("SELECT is_booked FROM schedules WHERE id = ? LIMIT 1");
        $chk->bind_param("i", $schedule_id);
        $chk->execute();
        $chk_res = $chk->get_result();
        $chk_row = $chk_res->fetch_assoc();
        $chk->close();
        if (!$chk_row || $chk_row['is_booked'] === 'yes') {
            $errors[] = 'This time slot has already been booked by another patient.';
        }

        // Check if user already booked this slot
        $dup = $conn->prepare("SELECT id FROM appointments WHERE user_id = ? AND schedule_id = ? AND status != 'cancelled' LIMIT 1");
        $dup->bind_param("ii", $user_id, $schedule_id);
        $dup->execute();
        $dup_res = $dup->get_result();
        $dup_exists = $dup_res->fetch_assoc();
        $dup->close();
        if ($dup_exists) {
            $errors[] = 'You already have a booking for this time slot.';
        }
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, treatment_id, schedule_id, payment_method_id, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iiii", $user_id, $treatment_id, $schedule_id, $payment_method_id);
        if ($stmt->execute()) {
            $appointment_id = $stmt->insert_id;
            $stmt->close();

            $upd = $conn->prepare("UPDATE schedules SET is_booked = 'yes' WHERE id = ? AND is_booked = 'no'");
            $upd->bind_param("i", $schedule_id);
            $upd->execute();
            if ($upd->affected_rows === 0) {
                $conn->rollback();
                $errors[] = 'This time slot was just booked by another patient. Please choose another.';
                header("Location: booking.php?msg=" . urlencode("Slot no longer available!") . "&type=error");
                exit;
            }
            $upd->close();

            // Create notification
            $title = 'New Appointment';
            $msg = "User #$user_id booked appointment #$appointment_id.";
            $notif = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, title, message, type) VALUES (?, ?, ?, ?, 'booking')");
            $notif->bind_param("iiss", $user_id, $appointment_id, $title, $msg);
            $notif->execute();
            $notif->close();

            $conn->commit();
            unset($_SESSION['booking_schedule_id'], $_SESSION['booking_treatment_id']);
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

// Store in session for persistence
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght=300;400;500;600;700&family=Playfair+Display:ital,wght=0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
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
<body class="bg-[#FAF9F6] font-sans text-brand-dark antialiased min-h-screen flex flex-col justify-between relative">
    <?php include '../includes/header.php'; ?>

    <header class="bg-white border-b border-gray-100 py-4 px-6 shadow-xs">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="doctor-and-date.html" class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-brand-textMuted hover:text-brand-pink transition">
                <i class="fa-solid fa-arrow-left"></i> Back to Schedule
            </a>
            <div class="text-xs font-medium text-brand-textMuted tracking-wide">
                Step <span class="text-brand-pink font-bold">2</span> of 3
            </div>
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
            <p class="text-sm text-brand-textMuted">Choose your preferred transaction system to securely hold your clinical session slot.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <div class="lg:col-span-7 space-y-6">
                
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-[0_10px_30px_rgba(0,0,0,0.01)] relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-brand-lightPink text-brand-pink text-[10px] font-bold uppercase tracking-widest px-4 py-1.5 rounded-bl-xl border-l border-b border-pink-100/30">
                        Confirmed Slot
                    </div>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-brand-pink mb-4">Booking Summary</h2>
                    
                    <div class="grid grid-cols-2 gap-y-4 gap-x-2">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Selected Treatment</span>
                            <span class="text-sm font-semibold text-brand-dark"><?= htmlspecialchars($booking['treatment_name'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Assigned Doctor</span>
                            <span class="text-sm font-semibold text-brand-dark">Dr. <?= htmlspecialchars($booking['doctor_name'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Patient Name</span>
                            <span class="text-sm font-semibold text-brand-dark"><?= htmlspecialchars($user_name ?: 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Date & Timestamp</span>
                            <span class="text-xs font-medium text-brand-textMuted"><?= isset($booking['available_date']) ? date("M d, Y", strtotime($booking['available_date'])) : 'N/A' ?> • <?= isset($booking['start_time']) ? date("h:i A", strtotime($booking['start_time'])) : 'N/A' ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Total Value</span>
                            <span class="text-sm font-bold text-brand-pink">$<?= isset($booking['price']) ? number_format($booking['price'], 2) : '0.00' ?></span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4 block">Payment Method</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="payment-methods-container">
                        
                        <label data-method="kbz_pay" class="payment-method-card flex items-center gap-4 p-5 rounded-xl cursor-pointer transition-all duration-200 group border-2 border-[#005BAa] bg-[#005BAa] text-white">
                            <input type="radio" name="payment_method" value="kbz_pay" checked class="hidden">
                            <div class="w-12 h-12 rounded-lg bg-white text-[#005BAa] flex flex-col items-center justify-center font-bold text-[11px] font-sans shrink-0 shadow-xs transition-colors group-hover:bg-slate-50">
                                <span class="text-[8px] font-medium tracking-tighter opacity-90 leading-none">KBZ</span>
                                <span class="font-extrabold text-xs -mt-0.5">Pay</span>
                            </div>
                            <span class="text-sm font-semibold tracking-wide transition-colors">KBZPay</span>
                        </label>

                        <label data-method="wave_pay" class="payment-method-card flex items-center gap-4 p-5 rounded-xl cursor-pointer transition-all duration-200 group border border-gray-200/80 bg-white text-slate-600">
                            <input type="radio" name="payment_method" value="wave_pay" class="hidden">
                            <div class="w-12 h-12 rounded-lg bg-[#F9CC1A] flex items-center justify-center shrink-0 shadow-xs relative overflow-hidden transition-colors">
                                <div class="w-7 h-7 rounded-full bg-[#004B93] flex items-center justify-center text-white text-[9px] font-bold font-serif">
                                    w
                                </div>
                            </div>
                            <span class="text-sm font-semibold tracking-wide transition-colors">WavePay</span>
                        </label>

                        <label data-method="cb_pay" class="payment-method-card flex items-center gap-4 p-5 rounded-xl cursor-pointer transition-all duration-200 group border border-gray-200/80 bg-white text-slate-600">
                            <input type="radio" name="payment_method" value="cb_pay" class="hidden">
                            <div class="w-12 h-12 rounded-lg bg-[#006BB6] text-white flex flex-col items-center justify-center shrink-0 shadow-xs relative overflow-hidden transition-colors">
                                <div class="w-8 h-4 bg-gradient-to-r from-red-500 via-yellow-400 to-green-500 absolute top-2 rounded-full opacity-75 blur-[1px]"></div>
                                <span class="font-bold text-xs tracking-tighter z-10">CBPay</span>
                            </div>
                            <span class="text-sm font-semibold tracking-wide transition-colors">CBPay</span>
                        </label>

                        <label data-method="trusty_pay" class="payment-method-card flex items-center gap-4 p-5 rounded-xl cursor-pointer transition-all duration-200 group border border-gray-200/80 bg-white text-slate-600">
                            <input type="radio" name="payment_method" value="trusty_pay" class="hidden">
                            <div class="w-12 h-12 rounded-lg bg-[#5A1793] text-white flex flex-col items-center justify-center font-bold text-[9px] leading-none shrink-0 shadow-xs transition-colors">
                                <span class="mb-0.5 font-light">$$$</span>
                                <span class="text-[7px] uppercase tracking-tighter opacity-80">Trusty</span>
                            </div>
                            <span class="text-sm font-semibold tracking-wide transition-colors">TrustyPay</span>
                        </label>

                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 bg-white border border-gray-100 rounded-2xl p-6 shadow-[0_15px_40px_rgba(0,0,0,0.02)]">
                
                <div class="flex items-center gap-3 bg-[#FAFAF8] rounded-xl p-3 border border-gray-100 mb-6">
                    <div class="text-emerald-500 bg-emerald-50 w-8 h-8 rounded-lg flex items-center justify-center text-xs">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-brand-dark">Secure Local Gateway</h4>
                        <p class="text-[10px] text-gray-400">Your payments are fully protected and encrypted.</p>
                    </div>
                </div>

                <form class="space-y-4" id="payment-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="schedule_id" value="<?= $schedule_id ?>">
                    
                    <div id="qr-scan-window" class="bg-slate-50 rounded-2xl p-4 border border-gray-100 text-center space-y-3">
                        <div class="flex items-center justify-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-brand-pink">
                            <i class="fa-solid fa-qrcode text-xs"></i> <span>Scan QR Code</span>
                        </div>
                        
                        <div class="w-40 h-40 mx-auto bg-white border border-gray-200/60 rounded-xl p-2.5 flex items-center justify-center shadow-xs relative group">
                            <img id="dynamic-qr-image" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=GlowSkin_<?= htmlspecialchars($user_name ?: 'Guest') ?>_Deposit" alt="Payment Scan Terminal" class="w-full h-full object-contain">
                        </div>
                        
                        <p class="text-[11px] text-brand-textMuted leading-normal">
                            Open your <span id="active-wallet-label" class="font-bold text-brand-dark">KBZPay</span> app and scan this secure dynamic invoice window to finalize your checkout balance instantly.
                        </p>
                    </div>

                    <!-- <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-brand-textMuted mb-1.5">Sender Reference / Txn ID</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400"><i class="fa-solid fa-receipt text-xs"></i></span>
                            <input type="text" required name="txn_id" placeholder="Enter last 6 digits or Txn ID" class="w-full bg-white border border-gray-200 text-sm font-medium pl-10 pr-4 py-3 rounded-xl focus:outline-none focus:ring-1 focus:ring-brand-pink focus:border-brand-pink transition">
                        </div>
                    </div> -->

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-brand-textMuted mb-1.5">Upload Receipt Screenshot</label>
                        <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-gray-200 border-dashed rounded-xl cursor-pointer bg-white hover:bg-slate-50/50 transition relative overflow-hidden group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4" id="upload-placeholder">
                                <i class="fa-solid fa-cloud-arrow-up text-gray-400 text-xl mb-2 group-hover:text-brand-pink transition-colors"></i>
                                <p class="text-[11px] text-gray-500 font-medium"><span class="text-brand-pink font-semibold">Click to upload</span> or drag and drop</p>
                                <p class="text-[9px] text-gray-400 mt-0.5">PNG, JPG or JPEG up to 5MB</p>
                            </div>
                            <input type="file" id="receipt-upload" name="receipt_image" accept="image/*" required class="hidden" />
                            <img id="receipt-preview" class="absolute inset-0 w-full h-full object-cover hidden" alt="Receipt Preview">
                        </label>
                    </div>

                    <div class="border-t border-gray-100 pt-5 mt-6 space-y-2.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-brand-textMuted font-light">Consultation Base Fee</span>
                            <span class="font-medium text-brand-dark">$<?= isset($booking['price']) ? number_format($booking['price'], 2) : '0.00' ?></span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-brand-textMuted font-light">Service Processing Surcharge</span>
                            <span class="font-medium text-emerald-500">FREE</span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-dashed border-gray-100">
                            <span class="font-serif text-sm font-bold text-brand-dark">Total Net Payable</span>
                            <span class="text-xl font-bold text-brand-pink">$<?= isset($booking['price']) ? number_format($booking['price'], 2) : '0.00' ?></span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-4">
                        <button type="submit" class="w-full bg-brand-pink text-white text-xs font-semibold tracking-wider uppercase py-4 rounded-xl shadow-md shadow-pink-100 hover:bg-opacity-95 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-check text-xs"></i> I Have Paid
                        </button>
                        
                        <a href="../user/booking.php" class="w-full bg-white border border-gray-200 hover:border-gray-300 text-brand-textMuted hover:text-brand-dark text-xs font-semibold tracking-wider uppercase py-3.5 rounded-xl transition-all flex items-center justify-center gap-2">
                            Cancel Process
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </main>

    <div id="success-modal" class="fixed inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center z-50 p-4 opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl transform scale-95 transition-transform duration-300">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 border border-emerald-100">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            
            <h3 class="font-serif text-2xl font-bold text-brand-dark mb-2">Booking Successful!</h3>
            <p class="text-xs text-brand-textMuted mb-6 leading-relaxed">Your professional clinic treatment session has been locked. Check your email for appointment notification receipts.</p>
            
            <button onclick="closeModal()" class="w-full bg-brand-pink hover:bg-opacity-95 text-white text-xs font-bold tracking-wider uppercase py-3.5 rounded-xl transition">
                Perfect, Thank You
            </button>
        </div>
    </div>

    <footer class="bg-white border-t border-gray-100 py-4 text-center text-[11px] text-gray-400">
        &copy; 2026 GlowSkin Skin Clinic. All encrypted portals secure.
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.payment-method-card');
        const paymentForm = document.getElementById('payment-form');
        const modal = document.getElementById('success-modal');
        const modalContent = modal.querySelector('.transform');
        
        const activeWalletLabel = document.getElementById('active-wallet-label');
        const dynamicQrImage = document.getElementById('dynamic-qr-image');
        
        const receiptUploadInput = document.getElementById('receipt-upload');
        const receiptPreview = document.getElementById('receipt-preview');
        const uploadPlaceholder = document.getElementById('upload-placeholder');

        // Brand Configuration Objects for active states
        const brandStyles = {
            kbz_pay: { bg: 'bg-[#005BAa]', border: 'border-[#005BAa]', text: 'text-white', innerIconBg: 'bg-white' },
            wave_pay: { bg: 'bg-[#F9CC1A]', border: 'border-[#F9CC1A]', text: 'text-slate-900', innerIconBg: 'bg-[#004B93]' },
            cb_pay: { bg: 'bg-[#006BB6]', border: 'border-[#006BB6]', text: 'text-white', innerIconBg: 'bg-white' },
            trusty_pay: { bg: 'bg-[#5A1793]', border: 'border-[#5A1793]', text: 'text-white', innerIconBg: 'bg-white' }
        };

        // Interactive Card Click Handler
        cards.forEach(card => {
            card.addEventListener('click', () => {
                const selectedMethod = card.getAttribute('data-method');

                // 1. Reset all cards to their unselected white state
                cards.forEach(c => {
                    const methodKey = c.getAttribute('data-method');
                    c.className = "payment-method-card flex items-center gap-4 p-5 rounded-xl cursor-pointer transition-all duration-200 group border border-gray-200/80 bg-white text-slate-600";
                    
                    // Reset internal icon badges back to normal fallback
                    const innerIconBox = c.querySelector('.w-12');
                    if(methodKey === 'kbz_pay') {
                        innerIconBox.className = "w-12 h-12 rounded-lg bg-[#005BAa] text-white flex flex-col items-center justify-center font-bold text-[11px] font-sans shrink-0 shadow-xs";
                    } else if(methodKey === 'wave_pay') {
                        innerIconBox.className = "w-12 h-12 rounded-lg bg-[#F9CC1A] flex items-center justify-center shrink-0 shadow-xs relative overflow-hidden";
                    } else if(methodKey === 'cb_pay') {
                        innerIconBox.className = "w-12 h-12 rounded-lg bg-[#006BB6] text-white flex flex-col items-center justify-center shrink-0 shadow-xs relative overflow-hidden";
                    } else if(methodKey === 'trusty_pay') {
                        innerIconBox.className = "w-12 h-12 rounded-lg bg-[#5A1793] text-white flex flex-col items-center justify-center font-bold text-[9px] leading-none shrink-0 shadow-xs";
                    }
                });

                // 2. Inject native active brand classes onto selected target card container
                const style = brandStyles[selectedMethod];
                card.className = `payment-method-card flex items-center gap-4 p-5 rounded-xl cursor-pointer transition-all duration-200 group border-2 ${style.border} ${style.bg} ${style.text}`;

                // Swap internal brand block text color nicely if needed for KBZPay layout adjustments
                const activeInnerIconBox = card.querySelector('.w-12');
                if (selectedMethod === 'kbz_pay') {
                    activeInnerIconBox.className = "w-12 h-12 rounded-lg bg-white text-[#005BAa] flex flex-col items-center justify-center font-bold text-[11px] font-sans shrink-0 shadow-xs";
                }

                // 3. Sync radio button logic status
                const radio = card.querySelector('input[type="radio"]');
                radio.checked = true;

                // 4. Update the side panel display text values
                const currentLabelText = card.querySelector('span:last-child').innerText;
                activeWalletLabel.innerText = currentLabelText;

                // 5. Load dynamic API QR Code matching string token values
                dynamicQrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=GlowSkin_${selectedMethod}_Deposit_Token`;
            });
        });

        // Live Upload Image Receipt Preview rendering pipeline
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

        // Form submit: show loading state and submit to server
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
    </script>
</body>
</html>