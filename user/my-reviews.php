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

$my_reviews = [];
$mstmt = $conn->prepare("
    SELECT t.id, t.name, t.rating, t.review_text, t.status, t.created_at,
           t.appointment_id, a.appointment_start,
           tt.treatment_name, s.available_date, d.name AS doctor_name
    FROM testimonials t
    LEFT JOIN appointments a ON a.id = t.appointment_id
    LEFT JOIN treatments tt ON tt.id = a.treatment_id
    LEFT JOIN schedules s ON s.id = a.schedule_id
    LEFT JOIN doctors d ON d.id = s.doctor_id
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
");
$mstmt->bind_param("i", $user_id);
$mstmt->execute();
$mresult = $mstmt->get_result();
while ($row = $mresult->fetch_assoc()) {
    $my_reviews[] = $row;
}
$mstmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reviews - GlowSkin Skin Clinic</title>
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

        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="font-serif text-3xl md:text-4xl font-bold tracking-tight">My Reviews</h1>
                <p class="text-sm text-brand-textMuted dark:text-gray-400 mt-1">Your submitted feedback</p>
            </div>
            <a href="../user/review.php">
                <button class="bg-brand-pink text-white px-5 py-2.5 rounded-md text-sm font-medium hover:bg-opacity-90 transition flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Write a Review
                </button>
            </a>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 mb-6 shadow-[0_5px_20px_rgba(0,0,0,0.02)] flex items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-brand-lightPink dark:bg-gray-800 flex items-center justify-center">
                    <i class="fa-solid fa-star text-brand-pink"></i>
                </div>
                <div>
                    <span class="text-2xl font-bold text-brand-dark dark:text-white"><?= count($my_reviews) ?></span>
                    <p class="text-xs text-brand-textMuted dark:text-gray-400">Total Reviews</p>
                </div>
            </div>
        </div>

        <?php if (empty($my_reviews)): ?>
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-12 text-center shadow-[0_10px_30px_rgba(0,0,0,0.02)]">
                <div class="w-16 h-16 bg-brand-lightPink dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-regular fa-star text-2xl text-brand-pink"></i>
                </div>
                <h3 class="font-serif text-xl font-bold text-brand-dark dark:text-white mb-2">No Reviews Yet</h3>
                <p class="text-sm text-brand-textMuted dark:text-gray-400 mb-6">You haven't submitted any reviews yet.</p>
                <a href="../user/review.php" class="inline-block bg-brand-pink text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-opacity-90 transition">
                    Write Your First Review
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($my_reviews as $mr): ?>
                    <?php
                        $review_status_class = match($mr['status']) {
                            'approved' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                            'pending' => 'bg-amber-50 text-amber-600 border-amber-200',
                            'rejected' => 'bg-red-50 text-red-500 border-red-200',
                            default => 'bg-gray-50 text-gray-600 border-gray-200',
                        };
                    ?>
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-[0_5px_20px_rgba(0,0,0,0.02)] hover:shadow-[0_10px_30px_rgba(0,0,0,0.04)] transition">
                        <div class="flex items-start justify-between flex-wrap gap-3">
                            <div class="space-y-2">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <?php if ($mr['appointment_id']): ?>
                                        <span class="text-sm font-bold text-brand-dark dark:text-white">
                                            <?= htmlspecialchars($mr['treatment_name'] ?? 'Treatment') ?>
                                        </span>
                                        <span class="text-xs text-brand-textMuted dark:text-gray-500">with Dr. <?= htmlspecialchars($mr['doctor_name'] ?? '') ?></span>
                                        <span class="text-xs text-brand-textMuted dark:text-gray-500">
                                            <i class="fa-regular fa-calendar mr-1"></i><?= date('M d, Y', strtotime($mr['available_date'])) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="text-xs font-semibold px-3 py-1 rounded-full border <?= $review_status_class ?>">
                                        <?= ucfirst($mr['status']) ?>
                                    </span>
                                </div>
                                <div class="text-amber-400 text-xs">
                                    <?php for ($s = 0; $s < intval($mr['rating']); $s++): ?>
                                        <i class="fa-solid fa-star"></i>
                                    <?php endfor; ?>
                                    <?php for ($s = intval($mr['rating']); $s < 5; $s++): ?>
                                        <i class="fa-regular fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-xs text-brand-textMuted italic leading-relaxed dark:text-gray-400">"<?= htmlspecialchars($mr['review_text']) ?>"</p>
                            </div>
                            <span class="text-[10px] text-brand-textMuted dark:text-gray-500 whitespace-nowrap"><?= date('M d, Y h:i A', strtotime($mr['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
