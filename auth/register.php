<?php
require '../includes/header.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Account - GlowSkin Clinic</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts (Serif for headings, Sans for body text) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:wght@200..800&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-[#FFF5F5] min-h-screen flex items-center justify-center p-4 md:p-10">

    <!-- Main Registration Container -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-5xl w-full min-h-[650px] grid grid-cols-1 md:grid-cols-12 transform transition-all duration-500 hover:shadow-pink-100">
        
        <!-- LEFT PANEL: Brand Info & Visuals (Hidden on small mobile screens) -->
        <div class="hidden md:flex md:col-span-5 bg-gradient-to-br from-[#FFF5F5] via-[#FFEBEB] to-[#FED7D7] p-8 flex-col justify-between relative overflow-hidden">
            <!-- Subtle Decorative Circles -->
            <div class="absolute top-0 right-0 w-48 h-48 bg-white/40 rounded-full blur-2xl -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-pink-200/30 rounded-full blur-3xl -ml-20 -mb-20"></div>

            <!-- Header/Logo Area -->
            <div class="relative z-10">
                <div class="flex items-center gap-2 font-serif text-2xl font-bold text-[#2D3748]">
                    <span class="text-pink-500 text-xl"><i class="fa-solid fa-leaf"></i></span>
                    GlowSkin
                </div>
                <p class="text-xs text-gray-500 mt-1 uppercase tracking-widest font-semibold">Skin Clinic</p>
            </div>

            <!-- Value Proposition / Center Text -->
            <div class="relative z-10 my-auto">
                <h2 class="font-serif text-3xl font-bold text-[#1A202C] leading-tight mb-4">
                    Begin Your <br><span class="text-pink-500 italic">Radiant</span> Journey
                </h2>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Join our clinic community today to track treatments, manage your personalized skincare path, and instantly lock in clinic sessions.
                </p>
                
                <!-- Trust Micro-Badge -->
                <div class="mt-8 flex items-center gap-3 bg-white/80 backdrop-blur-xs py-2 px-4 rounded-xl border border-pink-100/50 w-max shadow-xs">
                    <div class="flex text-pink-400 text-xs gap-0.5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700">10k+ Happy Clients</span>
                </div>
            </div>

            <!-- Footer Text -->
            <div class="relative z-10 text-xs text-gray-500">
                &copy; 2026 GlowSkin. All rights reserved.
            </div>
        </div>

        <!-- RIGHT PANEL: Registration Form -->
        <div class="col-span-1 md:col-span-7 p-8 md:p-12 flex flex-col justify-center">
            
            <!-- Mobile Navigation Back-Link or Identity -->
            <div class="flex items-center justify-between mb-8 md:mb-6">
                <div>
                    <h1 class="font-serif text-2xl md:text-3xl font-bold text-gray-800">Create Account</h1>
                    <p class="text-sm text-gray-500 mt-1">Get access to premium skincare routines.</p>
                </div>
                <span class="md:hidden font-serif text-xl font-bold text-gray-800 flex items-center gap-1">
                    <i class="fa-solid fa-leaf text-pink-500"></i>
                </span>
            </div>

            <!-- Social Registration Buttons -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <button class="flex items-center justify-center gap-2 py-3 px-4 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-pink-200 transition-all active:scale-98">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-4 h-4">
                    Google
                </button>
                <button class="flex items-center justify-center gap-2 py-3 px-4 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-pink-200 transition-all active:scale-98">
                    <img src="https://www.svgrepo.com/show/448224/facebook.svg" alt="Facebook" class="w-4 h-4">
                    Facebook
                </button>
            </div>

            <!-- Divider Line -->
            <div class="relative flex items-center justify-center my-4">
                <div class="border-t border-gray-100 w-full"></div>
                <span class="absolute bg-white px-4 text-xs text-gray-400 font-medium uppercase tracking-wider">Or register with email</span>
            </div>

            <!-- Form Content -->
            <form action="#" class="space-y-4">
                
                <!-- Full Name Field -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5" for="name">Full Name</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <input type="text" id="name" required placeholder="John Doe" 
                            class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-hidden focus:border-pink-400 focus:bg-white transition-all">
                    </div>
                </div>

                <!-- Email Address Field -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5" for="email">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="email" id="email" required placeholder="name@example.com" 
                            class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-hidden focus:border-pink-400 focus:bg-white transition-all">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5" for="password">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" required placeholder="••••••••" 
                            class="w-full pl-11 pr-12 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-hidden focus:border-pink-400 focus:bg-white transition-all">
                        <!-- Password Visibility Toggle Icon -->
                        <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-pink-500">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Terms and Conditions Checkbox -->
                <div class="flex items-start items-center justify-between pt-1">
                    <div class="flex items-center">
                        <input type="checkbox" id="terms" required 
                            class="w-4 h-4 text-pink-500 border-gray-300 rounded-sm focus:ring-pink-400 checked:bg-pink-500">
                        <label for="terms" class="ml-2 text-xs text-gray-500">
                            I agree to the <a href="#" class="text-pink-500 font-medium hover:underline">Terms of Service</a> & <a href="#" class="text-pink-500 font-medium hover:underline">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                        class="w-full bg-pink-500 hover:bg-pink-600 text-white font-semibold py-3 px-4 rounded-xl shadow-md shadow-pink-100 hover:shadow-lg transition-all transform active:scale-98 flex items-center justify-center gap-2">
                        Create Account
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>

            </form>

            <!-- Bottom Redirect Link -->
            <p class="text-sm text-center text-gray-500 mt-8">
                Already have an account? 
                <a href="../auth/login.php" class="text-pink-500 font-semibold hover:underline">Sign In</a>
            </p>

        </div>
    </div>

    <?php require '../includes/footer.php'; ?>

</body>
</html>