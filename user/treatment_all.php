<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Treatments - GlowSkin Skin Clinic</title>
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
</head>
<body class="bg-[#FAF9F6] font-sans text-brand-dark antialiased">

<?php include '../includes/header.php' ?>

    <!-- CATEGORY/HERO HEADER -->
    <section class="bg-brand-lightPink/50 border-b border-pink-100/30 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 pt-16 pb-20 text-center relative z-10">
            <span class="text-xs font-semibold uppercase tracking-widest text-brand-pink mb-3 block">Clinical Solutions</span>
            <h1 class="font-serif text-4xl md:text-5xl text-brand-dark font-bold leading-tight mb-4">
                Our Professional <span class="text-brand-pink italic font-normal">Treatments</span>
            </h1>
            <p class="text-brand-textMuted max-w-xl mx-auto text-sm leading-relaxed">
                Explore our selection of state-of-the-art dermatological procedures customized completely to target your unique skin requirements.
            </p>

            <!-- Inline Categories Pill Filter Filter -->
            <div class="flex flex-wrap justify-center gap-2 mt-10">
                <button class="bg-brand-pink text-white text-xs font-medium px-5 py-2.5 rounded-full shadow-sm shadow-pink-200 transition">All Treatments</button>
                <button class="bg-white hover:bg-brand-lightPink text-brand-dark text-xs font-medium px-5 py-2.5 rounded-full border border-gray-100 transition">Facial Care</button>
                <button class="bg-white hover:bg-brand-lightPink text-brand-dark text-xs font-medium px-5 py-2.5 rounded-full border border-gray-100 transition">Acne & Scars</button>
                <button class="bg-white hover:bg-brand-lightPink text-brand-dark text-xs font-medium px-5 py-2.5 rounded-full border border-gray-100 transition">Anti-Aging</button>
                <button class="bg-white hover:bg-brand-lightPink text-brand-dark text-xs font-medium px-5 py-2.5 rounded-full border border-gray-100 transition">Laser Treatment</button>
            </div>
        </div>
        <!-- Minimal Graphic background element -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-lightPink rounded-full filter blur-3xl opacity-40"></div>
    </section>

    <!-- TREATMENTS EXTENDED GRID SYSTEM -->
    <section class="max-w-7xl mx-auto px-6 py-20">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Treatment Card 1: Hydra Facial -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-[0_10px_30px_rgba(0,0,0,0.02)] border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
                <div class="overflow-hidden aspect-[4/3] relative">
                    <img src="https://images.unsplash.com/photo-1512290923902-8a9f81dc236c?auto=format&fit=crop&w=600&q=80" alt="Hydra Facial" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                    <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-xs text-brand-pink font-semibold text-[10px] uppercase tracking-wider px-3 py-1 rounded-full border border-pink-50">Facial Care</span>
                </div>
                <div class="p-6">
                    <h3 class="font-serif text-xl text-brand-dark font-bold mb-2">Hydra Facial Elite</h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Multi-step treatment configuration using patented technology to cleanse, exfoliate, extract impurities, and deeply hydrate your skin texture simultaneously.</p>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                        <div>
                            <span class="text-xs text-gray-400 block font-light">Price from</span>
                            <span class="text-brand-pink font-bold text-xl">$120</span>
                        </div>
                        <a href="../user/doctor.php" class="bg-brand-pink text-white text-xs font-semibold px-5 py-2.5 rounded-xl transition-all shadow-md shadow-pink-100 hover:bg-opacity-95">Book Session</a>
                    </div>
                </div>
            </div>

            <!-- Treatment Card 2: Advanced Acne Therapy -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-[0_10px_30px_rgba(0,0,0,0.02)] border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
                <div class="overflow-hidden aspect-[4/3] relative">
                    <img src="https://images.unsplash.com/photo-1519699047748-de8e457a634e?auto=format&fit=crop&w=600&q=80" alt="Acne Treatment" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                    <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-xs text-brand-pink font-semibold text-[10px] uppercase tracking-wider px-3 py-1 rounded-full border border-pink-50">Acne & Scars</span>
                </div>
                <div class="p-6">
                    <h3 class="font-serif text-xl text-brand-dark font-bold mb-2">Advanced Acne Control</h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Clinical medical-grade targeted solutions to clear painful deep blemishes, decrease oil production, and prevent permanent scarring breakouts.</p>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                        <div>
                            <span class="text-xs text-gray-400 block font-light">Price from</span>
                            <span class="text-brand-pink font-bold text-xl">$100</span>
                        </div>
                        <a href="../user/doctor.php" class="bg-brand-pink text-white text-xs font-semibold px-5 py-2.5 rounded-xl transition-all shadow-md shadow-pink-100 hover:bg-opacity-95">Book Session</a>
                    </div>
                </div>
            </div>

            <!-- Treatment Card 3: Anti-Aging Facial -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-[0_10px_30px_rgba(0,0,0,0.02)] border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
                <div class="overflow-hidden aspect-[4/3] relative">
                    <img src="https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=600&q=80" alt="Anti-Aging Facial" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                    <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-xs text-brand-pink font-semibold text-[10px] uppercase tracking-wider px-3 py-1 rounded-full border border-pink-50">Anti-Aging</span>
                </div>
                <div class="p-6">
                    <h3 class="font-serif text-xl text-brand-dark font-bold mb-2">Collagen Lift Facial</h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Reduces micro fine wrinkles and expression lines by infusing organic peptide complexes and pure hyaluronic skin boosters cleanly.</p>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                        <div>
                            <span class="text-xs text-gray-400 block font-light">Price from</span>
                            <span class="text-brand-pink font-bold text-xl">$150</span>
                        </div>
                        <a href="../user/doctor.php" class="bg-brand-pink text-white text-xs font-semibold px-5 py-2.5 rounded-xl transition-all shadow-md shadow-pink-100 hover:bg-opacity-95">Book Session</a>
                    </div>
                </div>
            </div>

            <!-- Treatment Card 4: Skin Brightening Therapy -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-[0_10px_30px_rgba(0,0,0,0.02)] border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
                <div class="overflow-hidden aspect-[4/3] relative">
                    <img src="https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&w=600&q=80" alt="Skin Brightening" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                    <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-xs text-brand-pink font-semibold text-[10px] uppercase tracking-wider px-3 py-1 rounded-full border border-pink-50">Facial Care</span>
                </div>
                <div class="p-6">
                    <h3 class="font-serif text-xl text-brand-dark font-bold mb-2">Laser Radiance Peel</h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Effectively counteracts dull skin layouts and safely balances hyperpigmentation issues to restore natural crystal brightness uniformly.</p>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                        <div>
                            <span class="text-xs text-gray-400 block font-light">Price from</span>
                            <span class="text-brand-pink font-bold text-xl">$110</span>
                        </div>
                        <a href="../user/doctor.php" class="bg-brand-pink text-white text-xs font-semibold px-5 py-2.5 rounded-xl transition-all shadow-md shadow-pink-100 hover:bg-opacity-95">Book Session</a>
                    </div>
                </div>
            </div>

            <!-- Treatment Card 5: Carbon Laser Peel -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-[0_10px_30px_rgba(0,0,0,0.02)] border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
                <div class="overflow-hidden aspect-[4/3] relative">
                    <img src="https://images.unsplash.com/photo-1512290923902-8a9f81dc236c?auto=format&fit=crop&w=600&q=80" alt="Carbon Laser" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                    <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-xs text-brand-pink font-semibold text-[10px] uppercase tracking-wider px-3 py-1 rounded-full border border-pink-50">Laser Treatment</span>
                </div>
                <div class="p-6">
                    <h3 class="font-serif text-xl text-brand-dark font-bold mb-2">Hollywood Carbon Peel</h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-6">A premium treatment framework utilizing advanced medical Q-Switched laser technology to clear skin debris and tighten dilated open pores instantly.</p>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                        <div>
                            <span class="text-xs text-gray-400 block font-light">Price from</span>
                            <span class="text-brand-pink font-bold text-xl">$180</span>
                        </div>
                        <a href="../user/doctor.php" class="bg-brand-pink text-white text-xs font-semibold px-5 py-2.5 rounded-xl transition-all shadow-md shadow-pink-100 hover:bg-opacity-95">Book Session</a>
                    </div>
                </div>
            </div>

            <!-- Treatment Card 6: Microneedling RF -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-[0_10px_30px_rgba(0,0,0,0.02)] border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
                <div class="overflow-hidden aspect-[4/3] relative">
                    <img src="https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=600&q=80" alt="Microneedling" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                    <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-xs text-brand-pink font-semibold text-[10px] uppercase tracking-wider px-3 py-1 rounded-full border border-pink-50">Anti-Aging</span>
                </div>
                <div class="p-6">
                    <h3 class="font-serif text-xl text-brand-dark font-bold mb-2">RF Microneedling Therapy</h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Combines standard physical micro-needling structures with high radiofrequency energy currents to remodel structural elasticity deeper inside tissues.</p>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                        <div>
                            <span class="text-xs text-gray-400 block font-light">Price from</span>
                            <span class="text-brand-pink font-bold text-xl">$220</span>
                        </div>
                        <a href="../user/doctor.php" class="bg-brand-pink text-white text-xs font-semibold px-5 py-2.5 rounded-xl transition-all shadow-md shadow-pink-100 hover:bg-opacity-95">Book Session</a>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ASSURANCE BANNER COMPONENT -->
    <section class="max-w-7xl mx-auto px-6 pb-24">
        <div class="bg-brand-dark text-white rounded-3xl p-8 md:p-12 grid md:grid-cols-12 gap-8 items-center justify-between shadow-xl">
            <div class="md:col-span-8 space-y-3">
                <h3 class="font-serif text-2xl md:text-3xl font-semibold">Not sure which treatment suits your skin type?</h3>
                <p class="text-xs text-gray-300 max-w-lg leading-relaxed font-light">
                    Schedule a private initial skin assessment matrix consultation. Our clinic experts will analyze your pores completely.
                </p>
            </div>
            <div class="md:col-span-4 md:text-right">
                <a href="#contact" class="inline-block bg-brand-pink text-white text-xs font-semibold tracking-wider uppercase px-6 py-4 rounded-xl shadow-lg shadow-pink-500/10 hover:bg-opacity-90 transition-all">
                    Book Free Consultation
                </a>
            </div>
        </div>
    </section>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.treatment-card');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const filterValue = button.getAttribute('data-filter');

            // 1. Manage active button styles
            buttons.forEach(btn => {
                btn.classList.remove('bg-brand-pink', 'text-white', 'shadow-sm', 'shadow-pink-200');
                btn.classList.add('bg-white', 'text-brand-dark', 'border', 'border-gray-100');
            });
            
            // Highlight current clicked button
            button.classList.remove('bg-white', 'text-brand-dark', 'border', 'border-gray-100');
            button.classList.add('bg-brand-pink', 'text-white', 'shadow-sm', 'shadow-pink-200');

            // 2. Filter the cards
            cards.forEach(card => {
                if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
                    card.style.display = 'block';
                    // Quick fade-in animation trigger
                    setTimeout(() => card.style.opacity = '1', 10);
                } else {
                    card.style.opacity = '0';
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php include '../includes/footer.php' ?>

</body>
</html>