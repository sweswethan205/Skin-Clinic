<?php require_once __DIR__ . '/../config/db.php'; ?>

<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Ensure this path is 100% correct relative to this file
include_once '../config/db.php';

// Ensure messages table exists
$conn->query("CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `subject` VARCHAR(150) DEFAULT NULL,
  `message_text` TEXT NOT NULL,
  `status` ENUM('unread', 'read') DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$notification = '';
$db_error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $preferred_date = trim($_POST['preferred_date'] ?? '');
    $message_text = trim($_POST['message_text'] ?? '');

    if (empty($name) || empty($email) || empty($message_text)) {
        $notification = 'error';
        $db_error_message = 'Name, email, and message are required.';
    } else {
        // Combining phone and date since they aren't explicitly separate columns in your schema
        $combined_message = "Phone: " . $phone . "\nPreferred Date: " . $preferred_date . "\n\nMessage:\n" . $message_text;
        $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        $subject = 'Contact Form Inquiry';

        $stmt = $conn->prepare("INSERT INTO messages (user_id, name, email, subject, message_text) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("issss", $user_id, $name, $email, $subject, $combined_message);

            if ($stmt->execute()) {
                $_SESSION['contact_success'] = true;
                header('Location: contact.php');
                exit;
            } else {
                $notification = 'error';
                $db_error_message = "Execute failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $notification = 'error';
            $db_error_message = "Prepare failed: " . $conn->error;
        }
    }
}

if (isset($_SESSION['contact_success'])) {
    $notification = 'success';
    unset($_SESSION['contact_success']);
}

$prefilled_name = $_SESSION['user_name'] ?? '';
$prefilled_email = '';
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $e_query = "SELECT email FROM users WHERE id = ? LIMIT 1";
    if ($e_stmt = $conn->prepare($e_query)) {
        $e_stmt->bind_param("i", $uid);
        $e_stmt->execute();
        $e_result = $e_stmt->get_result();
        if ($e_row = $e_result->fetch_assoc()) {
            $prefilled_email = $e_row['email'];
        }
        $e_stmt->close();
    }
}

//Review
$notification = ''; // Variable to hold success/error alerts

// 🚀 1. BACKEND PROCESSING: Run this ONLY when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $rating = intval($_POST['rating']);
    $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);
    
    // Check if the user is logged in, otherwise set to database NULL
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "NULL";

    // Insert review as 'pending' so admin can review it first
    $query = "INSERT INTO testimonials (user_id, name, rating, review_text, status) 
              VALUES ($user_id, '$name', $rating, '$review_text', 'pending')";

    if ($conn->query($query)) {
        $notification = 'success';
    } else {
        $notification = 'error';
    }
}

// Check if user is logged in to pre-fill their name automatically
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Skin Clinic - Reveal Your Natural Glow</title>
    <!-- Tailwind CSS & CDN Dependencies -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    
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

    <!-- Continuous Floating Transition CSS -->
    <style>
        @keyframes gentleFloat {
            0%, 100% {
                transform: translateY(-50%) translateX(0px);
            }
            50% {
                transform: translateY(-58%) translateX(4px); /* Moves up subtly and slightly right */
            }
        }
        .animate-float {
            animation: gentleFloat 4s ease-in-out infinite;
        }
       
    </style>
</head>
<body class="bg-brand-lightPink font-sans text-brand-dark antialiased">

<?php include '../includes/header.php' ?>


    <!-- HERO SECTION -->
    <section class="bg-brand-lightPink relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 items-center pt-12 pb-20 md:py-24">
            <div class="space-y-6 max-w-xl z-10">
                <span class="text-xs font-semibold uppercase tracking-wider text-brand-pink">Best Skin Clinic</span>
                <h1 class="font-serif text-5xl md:text-6xl text-brand-dark leading-tight">
                    Reveal Your <br><span class="text-brand-pink italic font-normal">Natural Glow</span>
                </h1>
                <p class="text-brand-textMuted leading-relaxed text-sm md:text-base">
                    We help you achieve healthy, radiant skin with advanced treatments and personalized care.
                </p>
                <div class="flex space-x-4 pt-2">
                    <a href="alltreatment.php">
                    <button class="bg-brand-pink text-white px-6 py-3 rounded-md text-sm font-medium hover:bg-opacity-90 transition">Book Appointment</button></a>
                    <button class="border border-brand-dark text-brand-dark px-6 py-3 rounded-md text-sm font-medium hover:bg-brand-dark hover:text-white transition">Explore Treatments</button>
                </div>
                
                <div class="grid grid-cols-3 gap-4 pt-8 border-t border-pink-200/60">
                    <div class="flex items-start space-x-2">
                        <div class="text-brand-pink p-1 bg-white rounded-full"><i class="fa-solid fa-user-doctor text-xs"></i></div>
                        <div>
                            <h4 class="text-xs font-semibold">Expert Doctors</h4>
                            <p class="text-[10px] text-brand-textMuted">Highly Qualified</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-2">
                        <div class="text-brand-pink p-1 bg-white rounded-full"><i class="fa-solid fa-microscope text-xs"></i></div>
                        <div>
                            <h4 class="text-xs font-semibold">Advanced Tech</h4>
                            <p class="text-[10px] text-brand-textMuted">Latest Equipment</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-2">
                        <div class="text-brand-pink p-1 bg-white rounded-full"><i class="fa-solid fa-heart text-xs"></i></div>
                        <div>
                            <h4 class="text-xs font-semibold">Personal Care</h4>
                            <p class="text-[10px] text-brand-textMuted">For You</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hero Image Asset Area -->
            <div class="relative flex justify-center items-center mt-12 md:mt-0 z-10 md:translate-x-4">
                <!-- Verbatim Reference Image Structure -->
                <img class="w-full h-full object-cover" data-alt="A serene portrait of a young woman with radiant, healthy skin, looking upwards with a peaceful expression. She is softly lit in a high-key studio setting with a warm, minimalist cream background that aligns with a clinical luxury aesthetic. Soft shadows and a slight texture capture a natural, clean, and medical-grade beauty atmosphere." src="https://lh3.googleusercontent.com/aida-public/AB6AXuC0LSo_Lt2DM3W98ekrBBqcOw3rou2DkgnJrkJicEhAVXbJ0bbJaU6otnUqxAe-yF5xY5dnJWRXxoHsAxunU2paPECTiCUjZBwfmN8UPCJToX3nmz7xJFISgZxrBsNz0PWVNSuDHMkEIlc0FzTPkF62BaK2AGXKFNwr_SQYM-sBque3YXVa4TrM0ZP0TEQQ505wzqy7v-ylQYBHp-u154pnSGnHAemeOBOez8nlDmk4vwkXpn_JESgk3Fq62cjecxfvtoc3P1Lta2A" style="border-radius: 42% 58% 40% 60% / 40% 43% 57% 60%">
                
                <!-- Badge Card: Placed Right-Center with Infinite Float Animation -->
                <div class="absolute -right-12 top-1/2 bg-white/95 backdrop-blur-xs py-4 px-6 rounded-xl shadow-lg border border-pink-200 text-center min-w-[190px] z-20 animate-float">
                    <h4 class="font-serif text-base text-brand-dark font-semibold tracking-tight text-[#1A2E26]">Acne Expertise</h4>
                    <p class="text-[10px] text-brand-textMuted italic mb-2">You Can Trust</p>
                    <div class="text-emerald-800 text-[10px] space-x-0.5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
$home_treatments = $conn->query("SELECT * FROM treatments ORDER BY treatment_name ASC LIMIT 4");
$home_treatments_list = [];
while ($row = $home_treatments->fetch_assoc()) {
    $home_treatments_list[] = $row;
}
?>
    <!-- POPULAR TREATMENTS SECTION -->
    <section class="max-w-7xl mx-auto px-6 py-20">
        <div class="flex justify-between items-end mb-10">
            <div>
                <span class="text-xs font-semibold tracking-widest text-brand-pink uppercase block mb-2">Our Popular Treatments</span>
                <h2 class="font-serif text-3xl text-brand-dark font-bold">Advanced Care for Radiant Skin</h2>
            </div>
            <a href="../user/alltreatment.php" class="text-brand-pink hover:underline font-medium text-sm flex items-center gap-1">
                View All Treatments →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if (empty($home_treatments_list)): ?>
            <div class="col-span-full text-center py-10 text-sm text-gray-400">No treatments available at the moment.</div>
            <?php else: ?>
            <?php foreach ($home_treatments_list as $t): ?>
            <div class="bg-white rounded-2xl overflow-hidden shadow-md border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
                <div class="overflow-hidden aspect-video">
                    <?php if (!empty($t['image'])): ?>
                    <img src="../<?php echo htmlspecialchars($t['image']); ?>" alt="<?php echo htmlspecialchars($t['treatment_name']); ?>" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-brand-lightPink text-brand-pink text-3xl">
                        <i class="fa-solid fa-hand-holding-medical"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="p-5">
                    <h3 class="font-serif text-lg text-brand-dark font-bold mb-2"><?php echo htmlspecialchars($t['treatment_name']); ?></h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-6"><?php echo htmlspecialchars($t['description'] ?? ''); ?></p>
                    <div class="flex justify-between items-center">
                        <span class="text-brand-pink font-bold text-lg">$<?php echo number_format($t['price'], 2); ?></span>
                        <a href="../user/booking.php?treatment_id=<?php echo $t['id']; ?>" class="bg-brand-pink text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">Book Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="max-w-7xl mx-auto px-6 py-12 grid md:grid-cols-2 gap-12 items-center">
        <div class="space-y-6">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-brand-pink block mb-1">About GlowSkin</span>
                <h2 class="font-serif text-3xl text-brand-dark mb-4">We Care About Your Skin</h2>
                <p class="text-sm text-brand-textMuted leading-relaxed">
                    At GlowSkin Clinic, we combine advanced technology with expert care to deliver safe, effective, and personalized skincare treatments.
                </p>
            </div>
            <ul class="space-y-2.5 text-sm text-brand-dark">
                <li class="flex items-center space-x-2"><i class="fa-regular fa-circle-check text-brand-pink"></i> <span>Expert Dermatologists</span></li>
                <li class="flex items-center space-x-2"><i class="fa-regular fa-circle-check text-brand-pink"></i> <span>Advanced Technology</span></li>
                <li class="flex items-center space-x-2"><i class="fa-regular fa-circle-check text-brand-pink"></i> <span>Personalized Skin Care</span></li>
                <li class="flex items-center space-x-2"><i class="fa-regular fa-circle-check text-brand-pink"></i> <span>Safe & Effective Treatments</span></li>
            </ul>
            <button class="bg-brand-pink text-white px-6 py-2.5 rounded-md text-sm font-medium hover:bg-opacity-90 transition">Learn More About Us</button>
        </div>
        <div>
            <img src="https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&w=800&q=80" alt="Clinic Interior" class="rounded-xl shadow-md w-full object-cover h-80">
        </div>
    </section>

    <!-- STATS COUNTER SECTION -->
    <section class="bg-[#FFF0F2] py-16 border-t border-b border-pink-100/40">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-pink-100/30 flex flex-col items-center justify-center text-center transition-transform duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="text-3xl mb-3 flex justify-center w-12 h-12 items-center bg-[#FFF0F2] rounded-full"><i class="fa-regular fa-face-smile text-rose-400"></i></div>
                <h3 class="text-3xl font-bold text-gray-800 tracking-tight"><span class="counter" data-target="10000">0</span>+</h3>
                <p class="text-xs text-gray-500 font-medium mt-1">Happy Clients</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-pink-100/30 flex flex-col items-center justify-center text-center transition-transform duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="text-3xl mb-3 flex justify-center w-12 h-12 items-center bg-[#FFF0F2] rounded-full"><i class="fa-solid fa-user-doctor text-rose-400"></i></div>
                <h3 class="text-3xl font-bold text-gray-800 tracking-tight"><span class="counter" data-target="20">0</span>+</h3>
                <p class="text-xs text-gray-500 font-medium mt-1">Expert Doctors</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-pink-100/30 flex flex-col items-center justify-center text-center transition-transform duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="text-3xl mb-3 flex justify-center w-12 h-12 items-center bg-[#FFF0F2] rounded-full"><i class="fa-solid fa-sparkles text-rose-400"></i></div>
                <h3 class="text-3xl font-bold text-gray-800 tracking-tight"><span class="counter" data-target="50">0</span>+</h3>
                <p class="text-xs text-gray-500 font-medium mt-1">Treatments</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-pink-100/30 flex flex-col items-center justify-center text-center transition-transform duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="text-3xl mb-3 flex justify-center w-12 h-12 items-center bg-[#FFF0F2] rounded-full"><i class="fa-solid fa-award text-rose-400"></i></div>
                <h3 class="text-3xl font-bold text-gray-800 tracking-tight"><span class="counter" data-target="5">0</span>+</h3>
                <p class="text-xs text-gray-500 font-medium mt-1">Years Experience</p>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS SECTION -->
    <section class="max-w-7xl mx-auto px-6 py-12">
        <div class="text-center mb-12">
            <span class="text-xs font-semibold uppercase tracking-wider text-brand-pink block mb-1">What Our Clients Say</span>
            <h2 class="font-serif text-3xl text-brand-dark">Real Stories, Real Results</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6 relative">
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center space-x-3">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80" alt="Jessica" class="w-10 h-10 rounded-full object-cover">
                    <div>
                        <h4 class="font-semibold text-sm">Jessica Brown</h4>
                        <div class="text-yellow-400 text-xs"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                    </div>
                </div>
                <p class="text-xs text-brand-textMuted italic leading-relaxed">"GlowSkin Clinic transformed my skin! The staff is professional and the results are amazing."</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center space-x-3">
                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=150&q=80" alt="Emily" class="w-10 h-10 rounded-full object-cover">
                    <div>
                        <h4 class="font-semibold text-sm">Emily Davis</h4>
                        <div class="text-yellow-400 text-xs"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                    </div>
                </div>
                <p class="text-xs text-brand-textMuted italic leading-relaxed">"I love the personalized care here. My acne is completely gone and my skin has never looked better."</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center space-x-3">
                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80" alt="Sophia" class="w-10 h-10 rounded-full object-cover">
                    <div>
                        <h4 class="font-semibold text-sm">Sophia Miller</h4>
                        <div class="text-yellow-400 text-xs"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                    </div>
                </div>
                <p class="text-xs text-brand-textMuted italic leading-relaxed">"Best facial treatment I've ever had. My skin feels so fresh and glowing after every session."</p>
            </div>
        </div>

        <!-- Add this call-to-action button right below the reviews -->
    <!-- add -->
    <!-- <div class="mt-12">
        <p class="text-slate-600 text-sm mb-3">Loved your treatment with us?</p>
        <a href="../user/review.php" class="inline-flex items-center space-x-2 border-2 border-pink-500 text-pink-500 font-semibold px-6 py-2.5 rounded-xl hover:bg-pink-500 hover:text-white transition-all text-sm">
            <i class="fa-regular fa-pen-to-square"></i>
            <span>Share Your Feedback</span>
        </a>
        <!-- add  -->
    <!-- </div>  -->

    <div class="bg-white max-w-7xl w-full p-8 md:p-14 rounded-[2.5rem] shadow-[0_20px_50px_rgba(255,101,132,0.04)] border border-pink-100/30 grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12 items-center mt-4">
        
        <!-- LEFT PANEL: Typography & Branding Content -->
        <div class="md:col-span-5 space-y-5">
            <!-- Heart Icon Emblem -->
            <div class="w-14 h-14 bg-[#FFF0F2] rounded-2xl flex items-center justify-center text-[#FF6584] text-xl shadow-xs">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            
            <div class="space-y-3">
                <h2 class="font-serif text-3xl md:text-4xl lg:text-[40px] font-bold tracking-tight text-[#2D2D2D] leading-[1.15]">
                    Share Your Glow Experience
                </h2>
                <p class="text-[13px] text-gray-500 font-medium leading-relaxed max-w-sm">
                    Your feedback helps us refine our skin treatments and assists others in finding their perfect skincare journey.
                </p>
            </div>
        </div>

        <!-- RIGHT PANEL: Contextual Embedded Form Box -->
        <div class="md:col-span-7 bg-[#FAF9F6] border border-gray-100/80 p-6 md:p-8 rounded-[2rem]">
            
            <?php if($notification === 'success'): ?>
                <div class="bg-emerald-50 text-emerald-600 border border-emerald-100 p-3 rounded-xl text-xs font-medium text-center mb-4">
                    🎉 Thank you! Your review has been submitted for approval.
                </div>
            <?php elseif($notification === 'error'): ?>
                <div class="bg-rose-50 text-rose-600 border border-rose-100 p-3 rounded-xl text-xs font-medium text-center mb-4">
                    ❌ Something went wrong. Please try again.
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                
                <!-- Dual Field Row (Name & Rating selection alignment) -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-start">
                    
                    <!-- Name input row -->
                    <div class="sm:col-span-7 space-y-1.5">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Your Name</label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($user_name) ?>" placeholder="Swe Swe" 
                               class="w-full px-4 py-3 text-xs font-medium bg-white border border-gray-200/80 rounded-xl outline-none focus:border-[#FF6584] transition-all">
                    </div>

                    <!-- Rating stars field align right stack -->
                    <div class="sm:col-span-5 space-y-1.5">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Rating</label>
                        
                        <!-- Star visual markup grid stack reverse list orientation -->
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
                </div>

                <!-- Textarea submission row wrapper box -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Your Review</label>
                    <textarea name="review_text" rows="4" required placeholder="How was your clinic visit or your treatment results? Tell us..." 
                              class="w-full px-4 py-3 text-xs font-medium bg-white border border-gray-200/80 rounded-xl outline-none focus:border-[#FF6584] resize-none transition-all leading-relaxed"></textarea>
                </div>

                <!-- Action interaction bottom row positioning -->
                <div class="flex justify-end pt-2">
                    <button type="submit" 
                            class="bg-[#FF6584] hover:bg-[#ff4d70] text-white font-bold text-[11px] tracking-wider uppercase px-7 py-3.5 rounded-xl shadow-md shadow-pink-500/10 transition-all transform active:scale-[0.98]">
                        Submit Review
                    </button>
                </div>
            </form>
        </div>

    </div>

    </section>
    <!-- Review Submission Section -->

    <!-- BLOG SECTION -->
    <section class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex justify-between items-end mb-10">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-brand-pink block mb-1">From Our Blog</span>
                <h2 class="font-serif text-3xl text-brand-dark">Skincare Tips & Latest Updates</h2>
            </div>
            <a href="#" class="text-brand-pink font-semibold text-sm hover:underline">View All Blog Posts →</a>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="flex space-x-4 bg-white p-3 rounded-lg border border-gray-50 shadow-xs">
                <img src="https://images.unsplash.com/photo-1556229010-aa3f7ff66b24?auto=format&fit=crop&w=200&q=80" alt="Blog 1" class="w-24 h-24 object-cover rounded-md">
                <div class="flex flex-col justify-between py-1">
                    <div>
                        <span class="text-[10px] font-semibold text-brand-pink uppercase tracking-wider">Skincare Tips</span>
                        <h4 class="font-semibold text-xs mt-1 text-brand-dark line-clamp-2">5 Skincare Tips for Healthy, Glowing Skin</h4>
                    </div>
                    <span class="text-[10px] text-gray-400">May 10, 2026</span>
                </div>
            </div>
            <div class="flex space-x-4 bg-white p-3 rounded-lg border border-gray-50 shadow-xs">
                <img src="https://images.unsplash.com/photo-1512290923902-8a9f81dc236c?auto=format&fit=crop&w=200&q=80" alt="Blog 2" class="w-24 h-24 object-cover rounded-md">
                <div class="flex flex-col justify-between py-1">
                    <div>
                        <span class="text-[10px] font-semibold text-brand-pink uppercase tracking-wider">Treatments</span>
                        <h4 class="font-semibold text-xs mt-1 text-brand-dark line-clamp-2">Benefits of Hydra Facial Treatment</h4>
                    </div>
                    <span class="text-[10px] text-gray-400">May 05, 2026</span>
                </div>
            </div>
            <div class="flex space-x-4 bg-white p-3 rounded-lg border border-gray-50 shadow-xs">
                <img src="https://images.unsplash.com/photo-1501554724485-a661670158a6?auto=format&fit=crop&w=200&q=80" alt="Blog 3" class="w-24 h-24 object-cover rounded-md">
                <div class="flex flex-col justify-between py-1">
                    <div>
                        <span class="text-[10px] font-semibold text-brand-pink uppercase tracking-wider">Skincare Tips</span>
                        <h4 class="font-semibold text-xs mt-1 text-brand-dark line-clamp-2">How to Protect Your Skin from Sun Damage</h4>
                    </div>
                    <span class="text-[10px] text-gray-400">Apr 28, 2026</span>
                </div>
            </div>
        </div>
    </section>



<!-- PREMIUM CONTACT US SECTION -->
    <section class="max-w-7xl mx-auto px-6 py-20 bg-white">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-0 bg-white rounded-3xl overflow-hidden shadow-2xl shadow-pink-100/30 border border-pink-100/40">
        
        <!-- Left Side Aesthetic Info Banner -->
        <div class="lg:col-span-6 relative bg-brand-lightPink min-h-[450px] lg:min-h-[680px] flex flex-col justify-between">
            <div class="p-12 z-10 space-y-6 relative">
                <div class="flex items-center space-x-2">
                    <span class="font-serif tracking-wide text-xs uppercase font-semibold text-brand-dark">Glow Skin Clinic</span>
                </div>

                <div class="space-y-3 max-w-xs pt-8">
                    <h2 class="font-serif text-4xl text-brand-dark font-normal leading-tight tracking-wide">
                        Healthy Skin, <br>Confident You
                    </h2>
                    <div class="w-12 h-[1px] bg-brand-pink my-4"></div>
                    <p class="text-xs text-brand-textMuted tracking-wide font-light leading-relaxed">
                        Professional care for your natural beauty.
                    </p>
                </div>
            </div>

            <div class="absolute inset-0 w-full h-full z-0">
                <img src="https://images.unsplash.com/photo-1614859324967-bdf461fcf769?auto=format&fit=crop&w=800&q=80" alt="GlowSkin Natural Portrait Model" class="w-full h-full object-cover object-center">
            </div>
            <div class="absolute inset-0 bg-gradient-to-r from-brand-lightPink/60 via-transparent to-transparent z-5 pointer-events-none"></div>
        </div>

        <!-- Right Side Form Controls -->
        <div class="lg:col-span-6 p-8 md:p-14 flex flex-col justify-between bg-white z-10">
            <div class="text-center max-w-md mx-auto w-full">
                <span class="text-[10px] font-bold uppercase tracking-widest text-brand-pink block mb-2">Contact Us</span>
                <h3 class="font-serif text-2xl md:text-3xl text-brand-dark font-medium tracking-tight">We'd Love to Hear From You</h3>
                <p class="text-[11px] text-brand-textMuted mt-3 font-light leading-relaxed">
                    Have questions or want to book a consultation?<br>Fill out the form below and our team will get back to you.
                </p>
                
                <div class="flex items-center justify-center space-x-3 my-5">
                    <div class="w-8 h-[1px] bg-pink-100"></div>
                    <div class="w-2 h-2 rounded-full bg-brand-pink/40"></div>
                    <div class="w-8 h-[1px] bg-pink-100"></div>
                </div>

                <!-- Notification Alerts Display -->
                <?php if($notification === 'success'): ?>
                    <div class="mb-5 bg-emerald-50 text-emerald-600 p-3 rounded-lg text-xs font-medium text-center">
                        Message sent! Our clinic staff will review and contact you shortly.
                    </div>
                <?php elseif($notification === 'error'): ?>
                    <div class="mb-5 bg-rose-50 text-rose-600 p-3 rounded-lg text-xs font-medium text-center">
                        Failed to save message.<br>
                        <span class="text-[10px] font-mono text-rose-500 bg-white/70 p-1.5 block mt-2 rounded border border-rose-200">
                            DB Error: <?= htmlspecialchars($db_error_message) ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <form action="contact.php" method="POST" class="space-y-3.5 max-w-md mx-auto w-full">
                <div class="relative flex items-center">
                    <input type="text" name="name" required value="<?= htmlspecialchars($prefilled_name) ?>" placeholder="Your Name" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 transition-all font-light text-brand-dark">
                </div>

                <div class="relative flex items-center">
                    <input type="email" name="email" required value="<?= htmlspecialchars($prefilled_email) ?>" placeholder="Email Address" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 transition-all font-light text-brand-dark">
                </div>

                <div class="relative flex items-center">
                    <input type="tel" name="phone" required placeholder="Phone Number" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 transition-all font-light text-brand-dark">
                </div>

                <div class="relative flex items-center">
                    <input type="text" name="preferred_date" placeholder="Preferred Date (Optional)" onfocus="(this.type='date')" onblur="(this.type='text')" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 transition-all font-light text-brand-textMuted">
                </div>

                <div class="relative flex items-start">
                    <textarea name="message_text" rows="4" required placeholder="Your Message" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 resize-none transition-all font-light text-brand-dark"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-brand-pink hover:bg-opacity-90 text-white text-xs font-semibold tracking-widest uppercase py-4 rounded-lg shadow-md shadow-pink-400/10 transition-all duration-300 transform active:scale-[0.99]">
                        Send Message
                    </button>
                </div>
            </form>

            <div class="border-t border-pink-100/50 mt-8 pt-5 flex flex-wrap justify-center items-center gap-x-6 gap-y-2 text-[10px] text-brand-textMuted font-light max-w-md mx-auto w-full">
                <div>+95 9 123 456 789</div>
                <div class="text-pink-100">|</div>
                <div>info@glowskinclinic.com</div>
                <div class="text-pink-100">|</div>
                <div>Yangon, Myanmar</div>
            </div>
        </div>

    </div>    
</section>
    <!-- Counter Intersection Observer Logic -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const counters = document.querySelectorAll('.counter');
            const speed = 60; 

            const animateCounter = (counter) => {
                const target = +counter.getAttribute('data-target');
                let count = 0;
                const increment = Math.ceil(target / speed);

                const updateCount = () => {
                    count += increment;
                    if (count < target) {
                        counter.innerText = count.toLocaleString();
                        setTimeout(updateCount, 25);
                    } else {
                        counter.innerText = target.toLocaleString();
                    }
                };
                updateCount();
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const counter = entry.target;
                        animateCounter(counter);
                        observer.unobserve(counter); 
                    }
                });
            }, { threshold: 0.2 });

            counters.forEach(counter => observer.observe(counter));
        });
    </script>

<?php include '../includes/footer.php' ?>

</body>
</html>