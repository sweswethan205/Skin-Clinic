<?php include '../includes/header.php'; ?>
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
            <!-- Hydra Facial -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-xs border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
                <div class="overflow-hidden aspect-video">
                    <img src="https://images.unsplash.com/photo-1512290923902-8a9f81dc236c?auto=format&fit=crop&w=600&q=80" alt="Hydra Facial" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                </div>
                <div class="p-5">
                    <h3 class="font-serif text-lg text-brand-dark font-bold mb-2">Hydra Facial</h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Deep cleansing and hydration for smooth, glowing skin.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-brand-pink font-bold text-lg">$120</span>
                        <a href="#" class="bg-brand-pink text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">Book Now</a>
                    </div>
                </div>
            </div>

            <!-- Acne Treatment -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-xs border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
                <div class="overflow-hidden aspect-video">
                    <img src="https://images.unsplash.com/photo-1519699047748-de8e457a634e?auto=format&fit=crop&w=600&q=80" alt="Acne Treatment" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                </div>
                <div class="p-5">
                    <h3 class="font-serif text-lg text-brand-dark font-bold mb-2">Acne Treatment</h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Effective solutions for clear, healthy, and acne-free skin.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-brand-pink font-bold text-lg">$100</span>
                        <a href="#" class="bg-brand-pink text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">Book Now</a>
                    </div>
                </div>
            </div>

            <!-- Anti-Aging Facial -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-xs border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
                <div class="overflow-hidden aspect-video">
                    <img src="https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=600&q=80" alt="Anti-Aging Facial" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                </div>
                <div class="p-5">
                    <h3 class="font-serif text-lg text-brand-dark font-bold mb-2">Anti-Aging Facial</h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Reduce fine lines and wrinkles for youthful, radiant skin.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-brand-pink font-bold text-lg">$150</span>
                        <a href="#" class="bg-brand-pink text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">Book Now</a>
                    </div>
                </div>
            </div>

            <!-- Skin Brightening -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-xs border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
                <div class="overflow-hidden aspect-video">
                    <img src="https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&w=600&q=80" alt="Skin Brightening" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                </div>
                <div class="p-5">
                    <h3 class="font-serif text-lg text-brand-dark font-bold mb-2">Skin Brightening</h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-6">Brighten dull skin and improve uneven skin tone.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-brand-pink font-bold text-lg">$110</span>
                        <a href="../user/doctor.php" class="bg-brand-pink text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include '../includes/footer.php'; ?>