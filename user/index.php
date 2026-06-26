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
</head>
<body class="bg-white font-sans text-brand-dark antialiased">

    <header class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between sticky top-0 z-50 bg-white/95 backdrop-blur-md">
        <div class="flex items-center space-x-2 text-brand-pink">
            <i class="fa-solid fa-spa text-2xl"></i>
            <span class="font-serif font-bold text-xl tracking-wide text-brand-dark">GlowSkin <span class="block text-xs font-sans font-semibold tracking-widest text-brand-pink -mt-1">SKIN CLINIC</span></span>
        </div>
        <nav class="hidden md:flex space-x-8 text-sm font-medium text-brand-dark">
            <a href="#" class="text-brand-pink">Home</a>
            <a href="#" class="hover:text-brand-pink transition">About Us</a>
            <a href="#" class="hover:text-brand-pink transition">Treatments</a>
            <a href="#" class="hover:text-brand-pink transition">Our Doctors</a>
            <a href="#" class="hover:text-brand-pink transition">Gallery</a>
            <a href="#" class="hover:text-brand-pink transition">Blog</a>
            <a href="#" class="hover:text-brand-pink transition">Contact</a>
        </nav>
        <button class="bg-brand-pink text-white px-5 py-2.5 rounded-md text-sm font-medium hover:bg-opacity-90 transition">Book Appointment</button>
    </header>

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
                    <button class="bg-brand-pink text-white px-6 py-3 rounded-md text-sm font-medium hover:bg-opacity-90 transition">Book Appointment</button>
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
            
           <!-- HERO IMAGE AREA (MATCHING IMAGE_CCA645.PNG AESTHETIC) -->
            <div class="relative flex justify-center items-center mt-12 md:mt-0 z-10 md:translate-x-4">
                
                <!-- Fluid Mask Container with warm beige studio portrait background -->
                <!-- <div class="w-[85%] md:w-[82%] aspect-[4/5] bg-cover bg-center shadow-md relative overflow-visible" 
                     style="background-image: url('https://images.unsplash.com/photo-1512290923902-8a9f81dc236c?auto=format&fit=crop&w=800&q=80'); 
                            border-radius: 42% 58% 40% 60% / 40% 43% 57% 60%;"> -->

                            <img class="w-full h-full object-cover" data-alt="A serene portrait of a young woman with radiant, healthy skin, looking upwards with a peaceful expression. She is softly lit in a high-key studio setting with a warm, minimalist cream background that aligns with a clinical luxury aesthetic. Soft shadows and a slight texture capture a natural, clean, and medical-grade beauty atmosphere." src="https://lh3.googleusercontent.com/aida-public/AB6AXuC0LSo_Lt2DM3W98ekrBBqcOw3rou2DkgnJrkJicEhAVXbJ0bbJaU6otnUqxAe-yF5xY5dnJWRXxoHsAxunU2paPECTiCUjZBwfmN8UPCJToX3nmz7xJFISgZxrBsNz0PWVNSuDHMkEIlc0FzTPkF62BaK2AGXKFNwr_SQYM-sBque3YXVa4TrM0ZP0TEQQ505wzqy7v-ylQYBHp-u154pnSGnHAemeOBOez8nlDmk4vwkXpn_JESgk3Fq62cjecxfvtoc3P1Lta2A" style="border-radius: 42% 58% 40% 60% / 40% 43% 57% 60%">
                    
                    <!-- Floating Overlaid Experience Card -->
                    <div class="absolute -left-6 bottom-10 bg-white/95 backdrop-blur-xs py-4 px-6 rounded-xl shadow-lg border border-pink-100 text-center min-w-[190px] z-20">
                        <h4 class="font-serif text-base text-brand-dark font-semibold tracking-tight">Acne Expertise</h4>
                        <p class="text-[10px] text-brand-textMuted italic mb-2">You Can Trust</p>
                        <div class="text-brand-pink text-[10px] space-x-0.5">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 py-20">
        <!-- POPULAR TREATMENTS SECTION -->
     <!-- #endregion --><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    
    <!-- Section Header Area -->
    <div class="flex justify-between items-end mb-10">
        <div>
            <span class="text-xs font-semibold tracking-widest text-brand-pink uppercase block mb-2">Our Popular Treatments</span>
            <h2 class="font-serif text-3xl text-brand-dark font-bold">Advanced Care for Radiant Skin</h2>
        </div>
        <a href="#" class="text-brand-pink hover:underline font-medium text-sm flex items-center gap-1">
            View All Treatments &rarr;
        </a>
    </div>

    <!-- 4-Column Treatments Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Card 1: Hydra Facial -->
        <div class="bg-white rounded-2xl overflow-hidden shadow-xs border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
            <div class="overflow-hidden aspect-video">
                <img src="https://images.unsplash.com/photo-1512290923902-8a9f81dc236c?auto=format&fit=crop&w=600&q=80" alt="Hydra Facial" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
            </div>
            <div class="p-5">
                <h3 class="font-serif text-lg text-brand-dark font-bold mb-2">Hydra Facial</h3>
                <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Deep cleansing and hydration for smooth, glowing skin.</p>
                <div class="flex justify-between items-center">
                    <span class="text-brand-pink font-bold text-lg">$120</span>
                    <a href="#" class="bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">Book Now</a>
                </div>
            </div>
        </div>

        <!-- Card 2: Acne Treatment -->
        <div class="bg-white rounded-2xl overflow-hidden shadow-xs border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
            <div class="overflow-hidden aspect-video">
                <img src="https://images.unsplash.com/photo-1519699047748-de8e457a634e?auto=format&fit=crop&w=600&q=80" alt="Acne Treatment" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
            </div>
            <div class="p-5">
                <h3 class="font-serif text-lg text-brand-dark font-bold mb-2">Acne Treatment</h3>
                <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Effective solutions for clear, healthy, and acne-free skin.</p>
                <div class="flex justify-between items-center">
                    <span class="text-brand-pink font-bold text-lg">$100</span>
                    <a href="#" class="bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">Book Now</a>
                </div>
            </div>
        </div>

        <!-- Card 3: Anti-Aging Facial -->
        <div class="bg-white rounded-2xl overflow-hidden shadow-xs border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
            <div class="overflow-hidden aspect-video">
                <img src="https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=600&q=80" alt="Anti-Aging Facial" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
            </div>
            <div class="p-5">
                <h3 class="font-serif text-lg text-brand-dark font-bold mb-2">Anti-Aging Facial</h3>
                <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Reduce fine lines and wrinkles for youthful, radiant skin.</p>
                <div class="flex justify-between items-center">
                    <span class="text-brand-pink font-bold text-lg">$150</span>
                    <a href="#" class="bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">Book Now</a>
                </div>
            </div>
        </div>

        <!-- Card 4: Skin Brightening -->
        <div class="bg-white rounded-2xl overflow-hidden shadow-xs border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
            <div class="overflow-hidden aspect-video">
                <img src="https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=600&q=80" alt="Skin Brightening" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
            </div>
            <div class="p-5">
                <h3 class="font-serif text-lg text-brand-dark font-bold mb-2">Skin Brightening</h3>
                <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Brighten dull skin and improve uneven skin tone.</p>
                <div class="flex justify-between items-center">
                    <span class="text-brand-pink font-bold text-lg">$110</span>
                    <a href="#" class="bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">Book Now</a>
                </div>
            </div>
        </div>

    </div>
</div>
    </section>

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

    <section class="bg-[#FFF0F2] py-16 border-t border-b border-pink-100/40">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 lg:grid-cols-4 gap-6">
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-pink-100/30 flex flex-col items-center justify-center text-center transition-transform duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="text-brandPink text-3xl mb-3 flex justify-center w-12 h-12 items-center bg-[#FFF0F2] rounded-full">
                    <i class="fa-regular fa-face-smile text-rose-400"></i>
                </div>
                <h3 class="text-3xl font-bold text-gray-800 tracking-tight">
                    <span class="counter" data-target="10000">0</span>+
                </h3>
                <p class="text-xs text-gray-500 font-medium mt-1">Happy Clients</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-pink-100/30 flex flex-col items-center justify-center text-center transition-transform duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="text-brandPink text-3xl mb-3 flex justify-center w-12 h-12 items-center bg-[#FFF0F2] rounded-full">
                    <i class="fa-solid fa-user-doctor text-rose-400"></i>
                </div>
                <h3 class="text-3xl font-bold text-gray-800 tracking-tight">
                    <span class="counter" data-target="20">0</span>+
                </h3>
                <p class="text-xs text-gray-500 font-medium mt-1">Expert Doctors</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-pink-100/30 flex flex-col items-center justify-center text-center transition-transform duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="text-brandPink text-3xl mb-3 flex justify-center w-12 h-12 items-center bg-[#FFF0F2] rounded-full">
                    <i class="fa-solid fa-sparkles text-rose-400"></i>
                </div>
                <h3 class="text-3xl font-bold text-gray-800 tracking-tight">
                    <span class="counter" data-target="50">0</span>+
                </h3>
                <p class="text-xs text-gray-500 font-medium mt-1">Treatments</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-pink-100/30 flex flex-col items-center justify-center text-center transition-transform duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="text-brandPink text-3xl mb-3 flex justify-center w-12 h-12 items-center bg-[#FFF0F2] rounded-full">
                    <i class="fa-solid fa-award text-rose-400"></i>
                </div>
                <h3 class="text-3xl font-bold text-gray-800 tracking-tight">
                    <span class="counter" data-target="5">0</span>+
                </h3>
                <p class="text-xs text-gray-500 font-medium mt-1">Years Experience</p>
            </div>

        </div>
    </section>

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
                <p class="text-xs text-brand-textMuted italic leading-relaxed">
                    "GlowSkin Clinic transformed my skin! The staff is professional and the results are amazing. Highly recommended!"
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center space-x-3">
                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=150&q=80" alt="Emily" class="w-10 h-10 rounded-full object-cover">
                    <div>
                        <h4 class="font-semibold text-sm">Emily Davis</h4>
                        <div class="text-yellow-400 text-xs"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                    </div>
                </div>
                <p class="text-xs text-brand-textMuted italic leading-relaxed">
                    "I love the personalized care here. My acne is completely gone and my skin has never looked better."
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center space-x-3">
                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80" alt="Sophia" class="w-10 h-10 rounded-full object-cover">
                    <div>
                        <h4 class="font-semibold text-sm">Sophia Miller</h4>
                        <div class="text-yellow-400 text-xs"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                    </div>
                </div>
                <p class="text-xs text-brand-textMuted italic leading-relaxed">
                    "Best facial treatment I've ever had. My skin feels so fresh and glowing after every session."
                </p>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex justify-between items-end mb-10">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-brand-pink block mb-1">From Our Blog</span>
                <h2 class="font-serif text-3xl text-brand-dark">Skincare Tips & Latest Updates</h2>
            </div>
            <a href="#" class="text-brand-pink font-semibold text-sm hover:underline">View All Blog Posts &rarr;</a>
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

    <footer class="bg-brand-lightPink pt-16 pb-8 border-t border-pink-100">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-10 text-sm mb-12">
            <div class="space-y-4">
                <div class="flex items-center space-x-2 text-brand-pink">
                    <i class="fa-solid fa-spa text-2xl"></i>
                    <span class="font-serif font-bold text-xl tracking-wide text-brand-dark">GlowSkin</span>
                </div>
                <p class="text-xs text-brand-textMuted leading-relaxed">
                    We are dedicated to helping you look and feel your best with advanced treatments and personalized care.
                </p>
                <div class="flex space-x-3 text-brand-pink text-base">
                    <a href="#" class="hover:text-brand-dark"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" class="hover:text-brand-dark"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="hover:text-brand-dark"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" class="hover:text-brand-dark"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>

            <div>
                <h4 class="font-semibold mb-4 text-brand-dark">Quick Links</h4>
                <ul class="space-y-2 text-xs text-brand-textMuted">
                    <li><a href="#" class="hover:text-brand-pink">Home</a></li>
                    <li><a href="#" class="hover:text-brand-pink">About Us</a></li>
                    <li><a href="#" class="hover:text-brand-pink">Treatments</a></li>
                    <li><a href="#" class="hover:text-brand-pink">Our Doctors</a></li>
                    <li><a href="#" class="hover:text-brand-pink">Gallery</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold mb-4 text-brand-dark">Contact Us</h4>
                <ul class="space-y-3 text-xs text-brand-textMuted">
                    <li class="flex items-start space-x-2">
                        <i class="fa-solid fa-location-dot mt-0.5 text-brand-pink"></i>
                        <span>123 Glowing Skin Ave,<br>Beauty City, BC 12345</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i class="fa-solid fa-phone text-brand-pink"></i>
                        <span>+1 234 567 890</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i class="fa-solid fa-envelope text-brand-pink"></i>
                        <span>info@glowskin.com</span>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold mb-4 text-brand-dark">Newsletter</h4>
                <p class="text-xs text-brand-textMuted mb-3">Subscribe to get updates and special offers.</p>
                <div class="flex flex-col space-y-2">
                    <input type="email" placeholder="Enter your email" class="p-2.5 text-xs rounded border border-pink-200 focus:outline-none focus:border-brand-pink">
                    <button class="bg-brand-pink text-white text-xs py-2.5 rounded font-medium hover:bg-opacity-90 transition">Subscribe</button>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 pt-6 border-t border-pink-200/40 flex flex-col sm:flex-row justify-between text-[11px] text-brand-textMuted">
            <p>&copy; 2026 GlowSkin Clinic. All Rights Reserved.</p>
            <div class="space-x-4 mt-2 sm:mt-0">
                <a href="#" class="hover:underline">Privacy Policy</a>
                <a href="#" class="hover:underline">Terms & Conditions</a>
            </div>
        </div>
    </footer>

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

</body>
</html>