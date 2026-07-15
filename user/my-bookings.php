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
$bookings = [];

$query = "SELECT 
            a.id,
            a.status,
            a.created_at as booked_on,
            t.treatment_name,
            t.price,
            s.available_date,
            s.start_time,
            s.end_time,
            d.name as doctor_name
          FROM appointments a
          JOIN treatments t ON t.id = a.treatment_id
          JOIN schedules s ON s.id = a.schedule_id
          JOIN doctors d ON d.id = s.doctor_id
          WHERE a.user_id = ?
          ORDER BY a.created_at DESC";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $stmt->close();
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
    <title>My Bookings - GlowSkin Skin Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
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
                        serif: ['Playfair Display', 'serif']
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-brand-lightPink/50 dark:bg-gray-950 font-sans text-brand-dark dark:text-white antialiased min-h-screen flex flex-col dark:text-gray-100">
    <?php include '../includes/header.php'; ?>

    <main class="flex-grow max-w-5xl mx-auto w-full px-6 py-12">

        <?php if (isset($_GET['msg'])): ?>
            <div class="mb-6 p-4 rounded-xl text-sm font-medium <?= isset($_GET['type']) && $_GET['type'] === 'error' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' ?>">
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="font-serif text-3xl md:text-4xl font-bold tracking-tight">My Appointments</h1>
                <p class="text-sm text-brand-textMuted dark:text-gray-400 mt-1">View all your appointment history</p>
            </div>
            <a href="../user/treatment.php">
                <button class="bg-brand-pink text-white px-5 py-2.5 rounded-md text-sm font-medium hover:bg-opacity-90 transition flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> New Booking
                </button>
            </a>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 mb-6 shadow-[0_5px_20px_rgba(0,0,0,0.02)] flex items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-brand-lightPink dark:bg-gray-800 flex items-center justify-center">
                    <i class="fa-regular fa-calendar-check text-brand-pink"></i>
                </div>
                <div>
                    <span class="text-2xl font-bold text-brand-dark dark:text-white"><?= count($bookings) ?></span>
                    <p class="text-xs text-brand-textMuted dark:text-gray-400">Total Appointments</p>
                </div>
            </div>
        </div>

        <?php if (empty($bookings)): ?>
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-12 text-center shadow-[0_10px_30px_rgba(0,0,0,0.02)]">
                <div class="w-16 h-16 bg-brand-lightPink dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-regular fa-calendar text-2xl text-brand-pink"></i>
                </div>
                <h3 class="font-serif text-xl font-bold text-brand-dark dark:text-white mb-2">No Bookings Yet</h3>
                <p class="text-sm text-brand-textMuted dark:text-gray-400 mb-6">You haven't made any appointments yet.</p>
                <a href="../user/booking.php" class="inline-block bg-brand-pink text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-opacity-90 transition">
                    Book Your First Appointment
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($bookings as $booking):
                    $color_class = $status_colors[$booking['status']] ?? 'bg-gray-50 text-gray-600 border-gray-200';
                ?>
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-[0_5px_20px_rgba(0,0,0,0.02)] hover:shadow-[0_10px_30px_rgba(0,0,0,0.04)] transition">
                        <div class="flex items-start justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-bold text-brand-dark dark:text-white"><?php echo htmlspecialchars($booking['treatment_name']); ?></h3>
                                    <span class="text-xs font-semibold px-3 py-1 rounded-full border <?php echo $color_class; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-brand-textMuted dark:text-gray-400">
                                    <span class="flex items-center gap-2">
                                        <i class="fa-regular fa-calendar text-brand-pink text-xs"></i>
                                        <?php echo date('M d, Y', strtotime($booking['available_date'])); ?>
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <i class="fa-regular fa-clock text-brand-pink text-xs"></i>
                                        <?php echo date('h:i A', strtotime($booking['start_time'])); ?> - <?php echo date('h:i A', strtotime($booking['end_time'])); ?>
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <i class="fa-solid fa-user-doctor text-brand-pink text-xs"></i>
                                        Dr. <?php echo htmlspecialchars($booking['doctor_name']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-brand-pink">$<?php echo number_format($booking['price'], 2); ?></span>
                                <p class="text-[10px] text-brand-textMuted dark:text-gray-400 uppercase tracking-wider mt-1">Booked <?php echo date('M d', strtotime($booking['booked_on'])); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 mt-4 pt-4 border-t border-gray-50 dark:border-gray-800">
                            <a href="booking-details.php?id=<?= $booking['id'] ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-pink bg-brand-lightPink dark:bg-gray-800 px-3 py-1.5 rounded-lg hover:bg-brand-pink hover:text-white transition-all">
                                <i class="fa-regular fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>