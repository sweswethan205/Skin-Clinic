<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_token']) || $_SESSION['user_token'] !== 'authenticated_success_token') {
    header('Location: ../auth/re.php');
    exit;
}
include_once '../config/db.php';

$user_id = intval($_SESSION['user_id']);
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$booking = null;
if ($booking_id > 0) {
    $query = "SELECT 
                a.id, a.status, a.created_at, a.receipt_image,
                t.treatment_name, t.price, t.description AS treatment_desc,
                s.available_date, s.start_time, s.end_time,
                d.name AS doctor_name, d.photo AS doctor_photo, d.description AS doctor_desc,
                pm.method_name AS payment_method
              FROM appointments a
              JOIN treatments t ON t.id = a.treatment_id
              JOIN schedules s ON s.id = a.schedule_id
              JOIN doctors d ON d.id = s.doctor_id
              LEFT JOIN payment_methods pm ON pm.id = a.payment_method_id
              WHERE a.id = ? AND a.user_id = ?
              LIMIT 1";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        $stmt->close();
    }
}

$status_colors = [
    'pending' => 'bg-amber-50 text-amber-600 border-amber-200',
    'confirmed' => 'bg-blue-50 text-blue-600 border-blue-200',
    'completed' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
    'cancelled' => 'bg-red-50 text-red-500 border-red-200',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - GlowSkin Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { pink: '#FF6584', lightPink: '#FFF0F2', dark: '#2D2D2D', textMuted: '#666666' }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'], serif: ['Playfair Display', 'serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-[#FAF9F6] font-sans text-brand-dark antialiased min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <main class="flex-grow max-w-4xl mx-auto w-full px-6 py-12">
        <a href="my-bookings.php" class="inline-flex items-center gap-2 text-sm text-brand-textMuted hover:text-brand-pink transition mb-8">
            <i class="fa-solid fa-arrow-left"></i> Back to My Bookings
        </a>

        <?php if (!$booking): ?>
            <div class="bg-white border border-gray-100 rounded-2xl p-12 text-center">
                <div class="w-16 h-16 bg-brand-lightPink rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-regular fa-calendar-xmark text-2xl text-brand-pink"></i>
                </div>
                <h3 class="font-serif text-xl font-bold text-brand-dark mb-2">Booking Not Found</h3>
                <p class="text-sm text-brand-textMuted">This booking does not exist or you don't have access to it.</p>
            </div>
        <?php else:
            $color_class = $status_colors[$booking['status']] ?? 'bg-gray-50 text-gray-600 border-gray-200';
        ?>
            <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-[0_10px_30px_rgba(0,0,0,0.02)]">
                <div class="bg-gradient-to-r from-brand-pink/10 to-pink-50 px-8 py-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="font-serif text-2xl font-bold text-brand-dark"><?= htmlspecialchars($booking['treatment_name']) ?></h1>
                            <p class="text-sm text-brand-textMuted mt-1">Booking #<?= $booking['id'] ?></p>
                        </div>
                        <span class="text-xs font-semibold px-4 py-1.5 rounded-full border <?= $color_class ?>">
                            <?= ucfirst($booking['status']) ?>
                        </span>
                    </div>
                </div>

                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-textMuted">Schedule</h3>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="w-8 h-8 rounded-lg bg-brand-lightPink flex items-center justify-center text-brand-pink shrink-0"><i class="fa-regular fa-calendar"></i></span>
                                    <div><span class="block font-semibold"><?= date('l, F d, Y', strtotime($booking['available_date'])) ?></span></div>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="w-8 h-8 rounded-lg bg-brand-lightPink flex items-center justify-center text-brand-pink shrink-0"><i class="fa-regular fa-clock"></i></span>
                                    <div><span class="block font-semibold"><?= date('h:i A', strtotime($booking['start_time'])) ?> - <?= date('h:i A', strtotime($booking['end_time'])) ?></span></div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-textMuted">Doctor</h3>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-brand-lightPink flex items-center justify-center text-brand-pink font-bold overflow-hidden shrink-0">
                                    <?php if ($booking['doctor_photo']): ?>
                                        <img src="../<?= htmlspecialchars($booking['doctor_photo']) ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <i class="fa-solid fa-user-doctor"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="block font-semibold text-sm">Dr. <?= htmlspecialchars($booking['doctor_name']) ?></span>
                                    <?php if ($booking['doctor_desc']): ?>
                                        <span class="text-xs text-brand-textMuted"><?= htmlspecialchars(substr($booking['doctor_desc'], 0, 60)) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-textMuted">Payment</h3>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="w-8 h-8 rounded-lg bg-brand-lightPink flex items-center justify-center text-brand-pink shrink-0"><i class="fa-solid fa-wallet"></i></span>
                                    <div>
                                        <span class="block font-semibold"><?= $booking['payment_method'] ? htmlspecialchars($booking['payment_method']) : 'N/A' ?></span>
                                        <span class="text-xs text-brand-textMuted">Payment Method</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-500 shrink-0"><i class="fa-solid fa-money-bill-wave"></i></span>
                                    <div>
                                        <span class="block font-semibold text-lg text-brand-pink">$<?= number_format($booking['price'], 2) ?></span>
                                        <span class="text-xs text-brand-textMuted">Total Amount</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-textMuted">Receipt</h3>
                            <?php if (!empty($booking['receipt_image']) && file_exists(__DIR__ . '/../' . ltrim($booking['receipt_image'], './'))): ?>
                                <a href="#" onclick="document.getElementById('receipt-modal').classList.remove('hidden');document.getElementById('receipt-modal').classList.add('flex');return false;">
                                    <img src="<?= htmlspecialchars($booking['receipt_image']) ?>" alt="Receipt" class="w-40 h-32 object-cover rounded-xl border border-gray-200 hover:ring-2 hover:ring-brand-pink transition-all cursor-pointer">
                                </a>
                                <p class="text-xs text-brand-textMuted">Click to enlarge</p>
                            <?php else: ?>
                                <div class="w-40 h-32 bg-gray-50 rounded-xl border border-dashed border-gray-200 flex flex-col items-center justify-center text-brand-textMuted">
                                    <i class="fa-regular fa-receipt text-2xl mb-1"></i>
                                    <span class="text-xs">No receipt uploaded</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($booking['treatment_desc']): ?>
                    <hr class="border-gray-100">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-brand-textMuted mb-3">Treatment Description</h3>
                        <p class="text-sm text-brand-textMuted leading-relaxed"><?= nl2br(htmlspecialchars($booking['treatment_desc'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <hr class="border-gray-100">

                    <div class="flex items-center justify-between text-xs text-brand-textMuted">
                        <span>Booked on <?= date('F d, Y \a\t h:i A', strtotime($booking['created_at'])) ?></span>
                        <a href="my-bookings.php" class="text-brand-pink hover:underline font-semibold">Back to Bookings</a>
                    </div>
                </div>
            </div>

            <!-- Receipt Modal -->
            <div id="receipt-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-50 p-4 hidden">
                <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                        <h3 class="text-xs font-bold text-brand-dark uppercase tracking-wider">Payment Receipt</h3>
                        <button onclick="document.getElementById('receipt-modal').classList.add('hidden');document.getElementById('receipt-modal').classList.remove('flex')" class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 text-brand-textMuted hover:text-brand-dark flex items-center justify-center transition-colors">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                    <div class="p-4 flex items-center justify-center">
                        <img src="<?= htmlspecialchars($booking['receipt_image']) ?>" alt="Receipt" class="max-w-full max-h-[75vh] object-contain rounded-xl border border-gray-100">
                    </div>
                </div>
            </div>
            <script>
                document.getElementById('receipt-modal')?.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.add('hidden');
                        this.classList.remove('flex');
                    }
                });
            </script>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
