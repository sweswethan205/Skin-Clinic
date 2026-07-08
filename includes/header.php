<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_token']) && $_SESSION['user_token'] === 'authenticated_success_token';
$user_name = $_SESSION['user_name'] ?? '';
$user_photo = $_SESSION['user_photo'] ?? '';

// Ensure DB connection for notification count
if (!isset($conn) || $conn === null) {
    include_once __DIR__ . '/../config/db.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Skin Clinic - Reveal Your Natural Glow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght=0,600;0,700;1,400&display=swap" rel="stylesheet">
    
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
<body class="bg-white font-sans text-brand-dark antialiased">

    <header class="w-full px-10 flex items-center justify-between sticky top-0 z-50 bg-white shadow-sm border-b">
        <div class="flex items-center space-x-2 text-brand-pink py-4">
            <i class="fa-solid fa-spa text-2xl"></i>
            <span class="font-serif font-bold text-xl tracking-wide text-brand-dark">GlowSkin <span class="block text-xs font-sans font-semibold tracking-widest text-brand-pink -mt-1">SKIN CLINIC</span></span>
        </div>
        
        <nav id="main-nav" class="hidden md:flex space-x-8 text-sm font-medium text-brand-dark items-center">
            <a href="../user/index1.php" class="nav-item hover:text-brand-pink hover:underline decoration-brand-pink decoration-2 underline-offset-[6px] transition-all">Home</a>
            <a href="../user/treatment.php" class="nav-item hover:text-brand-pink hover:underline decoration-brand-pink decoration-2 underline-offset-[6px] transition-all">Treatments</a>
            <a href="../user/about.php" class="nav-item hover:text-brand-pink hover:underline decoration-brand-pink decoration-2 underline-offset-[6px] transition-all">About Us</a>
            <a href="../user/ourdoctors.php" class="nav-item hover:text-brand-pink hover:underline decoration-brand-pink decoration-2 underline-offset-[6px] transition-all">Our Doctors</a>
            <a href="../user/gallery.php" class="nav-item hover:text-brand-pink hover:underline decoration-brand-pink decoration-2 underline-offset-[6px] transition-all">Gallery</a>
            <a href="../user/review.php" class="nav-item hover:text-brand-pink hover:underline decoration-brand-pink decoration-2 underline-offset-[6px] transition-all">Review</a>
            <a href="../user/contact.php" class="nav-item hover:text-brand-pink hover:underline decoration-brand-pink decoration-2 underline-offset-[6px] transition-all">Contact</a>
        </nav>
        
        <div class="py-4 flex items-center gap-4">
            <?php if ($is_logged_in): ?>
                <?php
                // Fetch unread notification count for logged-in user
                $unread_notif = 0;
                if (isset($_SESSION['user_id'])) {
                    $uid = intval($_SESSION['user_id']);
                    $nq = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id = $uid AND is_read = 0");
                    if ($nq && $nr = $nq->fetch_assoc()) $unread_notif = $nr['c'];
                }
                ?>
                <a href="../user/notifications.php" class="relative text-brand-textMuted hover:text-brand-pink transition text-lg p-1">
                    <i class="fa-regular fa-bell"></i>
                    <?php if ($unread_notif > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-brand-pink text-white text-[9px] font-bold h-4 min-w-[16px] px-1 flex items-center justify-center rounded-full ring-2 ring-white"><?= $unread_notif ?></span>
                    <?php endif; ?>
                </a>
                <div class="relative group">
                    <div class="flex items-center gap-3 cursor-pointer">
                        <div class="w-8 h-8 rounded-full bg-brand-pink flex items-center justify-center text-white text-sm font-semibold overflow-hidden">
                            <?php if ($user_photo): ?>
                                <img src="../<?php echo htmlspecialchars($user_photo); ?>" alt="Avatar" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <span class="text-sm font-medium text-brand-dark hidden sm:inline"><?php echo htmlspecialchars($user_name); ?></span>
                        <i class="fa-solid fa-chevron-down text-xs text-brand-textMuted"></i>
                    </div>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <a href="../user/profile.php" class="flex items-center gap-3 px-4 py-3 text-sm text-brand-dark hover:bg-brand-lightPink rounded-t-lg transition">
                            <i class="fa-regular fa-user text-brand-pink"></i> My Profile
                        </a>
                        <a href="../user/my-bookings.php" class="flex items-center gap-3 px-4 py-3 text-sm text-brand-dark hover:bg-brand-lightPink transition">
                            <i class="fa-regular fa-calendar text-brand-pink"></i> My Appointments
                        </a>
                        <hr class="border-gray-100">
                        <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 text-sm text-red-500 hover:bg-red-50 rounded-b-lg transition">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="relative group">
                   <a href="../auth/re.php"> <button class="text-sm font-medium text-brand-dark hover:text-brand-pink transition flex items-center gap-1">
                        Sign Up 
                    </button></a>
                    <!-- <div class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <a href="../auth/login.php" class="flex items-center gap-3 px-4 py-3 text-sm text-brand-dark hover:bg-brand-lightPink rounded-t-lg transition">
                            <i class="fa-solid fa-right-to-bracket text-brand-pink"></i> Login
                        </a>
                        <a href="../auth/re.php" class="flex items-center gap-3 px-4 py-3 text-sm text-brand-dark hover:bg-brand-lightPink rounded-b-lg transition">
                            <i class="fa-solid fa-user-plus text-brand-pink"></i> Register
                        </a>
                    </div> -->
                </div>
                <a href="../user/treatment.php">
                    <button class="bg-brand-pink text-white px-5 py-2.5 rounded-md text-sm font-medium hover:bg-opacity-90 transition">Book Appointment</button>
                </a>
            <?php endif; ?>
            <!-- <a href="../user/booking.php">
                <button class="bg-brand-pink text-white px-5 py-2.5 rounded-md text-sm font-medium hover:bg-opacity-90 transition">Book Appointment</button>
            </a> -->
        </div>
    </header>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const currentPath = window.location.pathname.toLowerCase();
            const navLinks = document.querySelectorAll("#main-nav .nav-item");

            let matchedAny = false;

            navLinks.forEach(link => {
                const targetHref = link.getAttribute("href").toLowerCase();
                const cleanHref = targetHref.replace('../user/', '');
                
                if (currentPath.endsWith(cleanHref) && cleanHref !== '') {
                    link.classList.add("text-brand-pink", "underline", "decoration-brand-pink", "decoration-2", "underline-offset-[6px]");
                    matchedAny = true;
                }
            });

            // Fallback Engine: Defaults to styling Home on first run
            if (!matchedAny) {
                const homeLink = document.querySelector('#main-nav a[href*="index1.php"]');
                if (homeLink) {
                    homeLink.classList.add("text-brand-pink", "underline", "decoration-brand-pink", "decoration-2", "underline-offset-[6px]");
                }
            }
        });
    </script>

</body>
</html>