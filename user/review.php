<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../config/db.php';

$reviews_query = "SELECT t.name, t.rating, t.review_text, t.created_at, u.photo FROM testimonials t LEFT JOIN users u ON u.id = t.user_id WHERE t.status = 'approved' ORDER BY t.created_at DESC";
$reviews_result = $conn->query($reviews_query);
$reviews = [];
if ($reviews_result && $reviews_result->num_rows > 0) {
    while ($row = $reviews_result->fetch_assoc()) {
        $reviews[] = $row;
    }
}

$notification = '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$eligible_appointments = [];

if ($user_id) {
    $uid = intval($user_id);
    $stmt = $conn->prepare("
        SELECT a.id, a.appointment_start, a.appointment_end, a.status, a.created_at,
               t.treatment_name, s.available_date, d.name AS doctor_name
        FROM appointments a
        JOIN treatments t ON t.id = a.treatment_id
        JOIN schedules s ON s.id = a.schedule_id
        JOIN doctors d ON d.id = s.doctor_id
        WHERE a.user_id = ?
        AND a.status IN ('confirmed', 'completed')
        AND a.id NOT IN (SELECT appointment_id FROM testimonials WHERE appointment_id IS NOT NULL AND user_id = ?)
        ORDER BY s.available_date DESC
    ");
    $stmt->bind_param("ii", $uid, $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $eligible_appointments[] = $row;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$user_id) {
        $notification = 'login_required';
    } else {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $rating = intval($_POST['rating']);
        $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);
        $appointment_id = intval($_POST['appointment_id']);
        $uid = intval($user_id);

        $check = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND user_id = ? AND status IN ('confirmed', 'completed')");
        $check->bind_param("ii", $appointment_id, $uid);
        $check->execute();
        $check_result = $check->get_result();
        $valid_appointment = $check_result->fetch_assoc();
        $check->close();

        if (!$valid_appointment) {
            $notification = 'invalid_appointment';
        } else {
            $dup = $conn->prepare("SELECT id FROM testimonials WHERE appointment_id = ? AND user_id = ?");
            $dup->bind_param("ii", $appointment_id, $uid);
            $dup->execute();
            $dup_result = $dup->get_result();
            $already_reviewed = $dup_result->fetch_assoc();
            $dup->close();

            if ($already_reviewed) {
                $notification = 'already_reviewed';
            } else {
                $insert = $conn->prepare("INSERT INTO testimonials (user_id, name, rating, review_text, appointment_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                $insert->bind_param("isisi", $uid, $name, $rating, $review_text, $appointment_id);

                if ($insert->execute()) {
                    $notif_title = 'New Review';
                    $notif_msg = "$name submitted a $rating-star review: " . substr($review_text, 0, 50) . (strlen($review_text) > 50 ? '...' : '');
                    $nid = intval($user_id);
                    $target = 'admin';
                    $nstmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, target_role) VALUES (?, ?, ?, 'review', ?)");
                    $nstmt->bind_param("isss", $nid, $notif_title, $notif_msg, $target);
                    $nstmt->execute();
                    $nstmt->close();
                    $notification = 'success';

                    $eligible_appointments = array_filter($eligible_appointments, function ($a) use ($appointment_id) {
                        return intval($a['id']) !== $appointment_id;
                    });
                } else {
                    $notification = 'error';
                }
                $insert->close();
            }
        }
    }
}

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$user_photo = '';
$user_email = '';

if ($user_id) {
    $uid = intval($user_id);
    $pstmt = $conn->prepare("SELECT name, email, photo FROM users WHERE id = ? LIMIT 1");
    $pstmt->bind_param("i", $uid);
    $pstmt->execute();
    $presult = $pstmt->get_result();
    if ($prow = $presult->fetch_assoc()) {
        $user_name = $prow['name'];
        $user_email = $prow['email'];
        $user_photo = $prow['photo'] ?? '';
    }
    $pstmt->close();
}

include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Leave a Review</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    <!-- Custom CSS added properly outside of script tags -->
    <style>
        @keyframes marquee {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .animate-marquee {
            animation: marquee 25s linear infinite;
        }

        .animate-marquee:hover {
            animation-play-state: paused;
        }
    </style>
</head>

<body class="bg-[#FFF0F2]/40 min-h-screen p-4 md:p-8 dark:bg-gray-950 dark:text-gray-100">

    <section class="w-full mx-auto px-6 py-10">
        <div class="text-center mb-12">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#FF6584] block mb-1">What Our Clients Say</span>
            <h2 class="font-serif text-3xl text-slate-800 dark:text-white">Real Stories, Real Results</h2>
        </div>

        <!-- Marquee Frame Wrapper -->
        <div class="overflow-hidden max-w-7xl mx-auto px-6 py-12 relative ma">
            <!-- added w-max and flex-nowrap to prevent elements breaking into lines -->
            <div class="flex flex-nowrap gap-6 animate-marquee w-max pb-4 max-w-7xl">
                <?php if (!empty($reviews)): ?>
                    <?php for ($i = 0; $i < 4; $i++): // Looping extra to ensure visual loop coverage 
                    ?>
                        <?php foreach ($reviews as $review): ?>
                            <!-- Specified definitive dimensions explicitly to ensure consistency -->
                            <div class="w-72 sm:w-80 bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-3 flex-shrink-0 dark:bg-gray-900 dark:border-gray-800">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-[#FF6584]/20 flex items-center justify-center text-[#FF6584] text-xs font-bold overflow-hidden flex-shrink-0">
                                        <?php if (!empty($review['photo'])): ?>
                                            <img src="../<?= htmlspecialchars($review['photo']) ?>" alt="Avatar" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?= strtoupper(substr($review['name'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-sm text-slate-800 dark:text-white"><?= htmlspecialchars($review['name']) ?></h4>
                                        <div class="text-amber-400 text-[10px]">
                                            <?php for ($s = 0; $s < intval($review['rating']); $s++): ?>
                                                <i class="fa-solid fa-star"></i>
                                            <?php endfor; ?>
                                            <?php for ($s = intval($review['rating']); $s < 5; $s++): ?>
                                                <i class="fa-regular fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 italic leading-relaxed dark:text-gray-400">"<?= htmlspecialchars($review['review_text']) ?>"</p>
                            </div>
                        <?php endforeach; ?>
                    <?php endfor; ?>
                <?php else: ?>
                    <div class="w-full text-center text-gray-400 text-sm py-8">No reviews yet. Be the first to share your experience!</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white max-w-7xl mx-auto px-6 py-12 md:p-14 rounded-[2.5rem] shadow-[0_20px_50px_rgba(255,101,132,0.04)] border border-pink-100/30 grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12 items-center mt-12 dark:bg-gray-900">

            <!-- LEFT PANEL: Typography & Branding Content -->
            <div class="md:col-span-5 space-y-5 ">
                <!-- Heart Icon Emblem -->
                <div class="w-14 h-14 bg-[#FFF0F2] rounded-2xl flex items-center justify-center text-[#FF6584] text-xl shadow-xs">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>

                <div class="space-y-3">
                    <h2 class="font-serif text-3xl md:text-4xl lg:text-[40px] font-bold tracking-tight text-[#2D2D2D] leading-[1.15] dark:text-white">
                        Share Your Glow Experience
                    </h2>
                    <p class="text-[13px] text-gray-500 font-medium leading-relaxed max-w-sm dark:text-gray-400">
                        Your feedback helps us refine our skin treatments and assists others in finding their perfect skincare journey.
                    </p>
                </div>
            </div>

            <!-- RIGHT PANEL: Contextual Embedded Form Box -->
            <div class="md:col-span-7 bg-brand-lightPink border border-gray-100/80 p-6 md:p-8 rounded-[2rem] dark:bg-gray-800 dark:border-gray-700">

                <?php if ($notification === 'success'): ?>
                    <div class="bg-emerald-50 text-emerald-600 border border-emerald-100 p-3 rounded-xl text-xs font-medium text-center mb-4">
                        Thank you! Your review has been submitted for approval.
                    </div>
                <?php elseif ($notification === 'error'): ?>
                    <div class="bg-rose-50 text-rose-600 border border-rose-100 p-3 rounded-xl text-xs font-medium text-center mb-4">
                        Something went wrong. Please try again.
                    </div>
                <?php elseif ($notification === 'login_required'): ?>
                    <div class="bg-amber-50 text-amber-600 border border-amber-100 p-3 rounded-xl text-xs font-medium text-center mb-4">
                        Please log in to leave a review after your appointment.
                    </div>
                <?php elseif ($notification === 'invalid_appointment'): ?>
                    <div class="bg-rose-50 text-rose-600 border border-rose-100 p-3 rounded-xl text-xs font-medium text-center mb-4">
                        Invalid appointment selected. Please choose a valid confirmed or completed appointment.
                    </div>
                <?php elseif ($notification === 'already_reviewed'): ?>
                    <div class="bg-amber-50 text-amber-600 border border-amber-100 p-3 rounded-xl text-xs font-medium text-center mb-4">
                        You have already reviewed this appointment. Each appointment allows only one review.
                    </div>
                <?php endif; ?>

                <?php if (!$user_id): ?>
                    <div class="text-center py-6">
                        <div class="w-14 h-14 bg-[#FFF0F2] rounded-2xl flex items-center justify-center text-[#FF6584] text-xl mx-auto mb-4">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Please log in to leave a review after your appointment.</p>
                        <a href="../auth/re.php" class="inline-block bg-[#FF6584] hover:bg-[#ff4d70] text-white font-bold text-[11px] tracking-wider uppercase px-7 py-3.5 rounded-xl shadow-md shadow-pink-500/10 transition-all">
                            Log In
                        </a>
                    </div>
                <?php elseif (empty($eligible_appointments)): ?>
                    <div class="text-center py-6">
                        <div class="w-14 h-14 bg-[#FFF0F2] rounded-2xl flex items-center justify-center text-[#FF6584] text-xl mx-auto mb-4">
                            <i class="fa-regular fa-calendar-check"></i>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">No confirmed or completed appointments available for review.</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">You can leave a review once your appointment is completed and hasn't been reviewed yet.</p>
                    </div>
                <?php else: ?>
                <form action="" method="POST" class="space-y-5">

                    <div class="flex items-center gap-4 p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-700">
                        <div class="w-12 h-12 rounded-full bg-[#FF6584]/20 flex items-center justify-center text-[#FF6584] text-lg font-bold overflow-hidden flex-shrink-0">
                            <?php if ($user_photo): ?>
                                <img src="../<?= htmlspecialchars($user_photo) ?>" alt="Profile" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?= strtoupper(substr($user_name, 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800 dark:text-white truncate"><?= htmlspecialchars($user_name) ?></p>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate"><?= htmlspecialchars($user_email) ?></p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">Select Appointment</label>
                        <select name="appointment_id" required
                            class="w-full px-4 py-3 text-xs font-medium bg-white border border-gray-200/80 rounded-xl outline-none focus:border-[#FF6584] transition-all dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                            <option value="">-- Choose an appointment --</option>
                            <?php foreach ($eligible_appointments as $apt): ?>
                                <option value="<?= intval($apt['id']) ?>">
                                    <?= htmlspecialchars($apt['treatment_name']) ?> with Dr. <?= htmlspecialchars($apt['doctor_name']) ?> on <?= date('M d, Y', strtotime($apt['available_date'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <input type="hidden" name="name" value="<?= htmlspecialchars($user_name) ?>">

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">Rating</label>
                        <div class="flex flex-row-reverse justify-end items-center gap-1 text-2xl text-gray-200 py-1">
                            <input type="radio" id="star5" name="rating" value="5" class="peer hidden" required />
                            <label for="star5" class="cursor-pointer transition-colors hover:text-amber-400 peer-checked:text-amber-400 peer-hover:text-amber-400">
                                <i class="fa-solid fa-star"></i>
                            </label>
                            <input type="radio" id="star4" name="rating" value="4" class="peer hidden" />
                            <label for="star4" class="cursor-pointer transition-colors hover:text-amber-400 peer-checked:text-amber-400 peer-hover:text-amber-400">
                                <i class="fa-solid fa-star"></i>
                            </label>
                            <input type="radio" id="star3" name="rating" value="3" class="peer hidden" />
                            <label for="star3" class="cursor-pointer transition-colors hover:text-amber-400 peer-checked:text-amber-400 peer-hover:text-amber-400">
                                <i class="fa-solid fa-star"></i>
                            </label>
                            <input type="radio" id="star2" name="rating" value="2" class="peer hidden" />
                            <label for="star2" class="cursor-pointer transition-colors hover:text-amber-400 peer-checked:text-amber-400 peer-hover:text-amber-400">
                                <i class="fa-solid fa-star"></i>
                            </label>
                            <input type="radio" id="star1" name="rating" value="1" class="peer hidden" />
                            <label for="star1" class="cursor-pointer transition-colors hover:text-amber-400 peer-checked:text-amber-400 peer-hover:text-amber-400">
                                <i class="fa-solid fa-star"></i>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">Your Review</label>
                        <textarea name="review_text" rows="4" required placeholder="How was your clinic visit or your treatment results? Tell us..."
                            class="w-full px-4 py-3 text-xs font-medium bg-white border border-gray-200/80 rounded-xl outline-none focus:border-[#FF6584] resize-none transition-all leading-relaxed dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200"></textarea>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                            class="bg-[#FF6584] hover:bg-[#ff4d70] text-white font-bold text-[11px] tracking-wider uppercase px-7 py-3.5 rounded-xl shadow-md shadow-pink-500/10 transition-all transform active:scale-[0.98]">
                            Submit Review
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

</body>

</html>