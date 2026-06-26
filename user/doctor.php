<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            pink: '#FF6584',
                            lightPink: '#FFF0F2',
                            dark: '#2D2D2D',
                            textMuted: '#717171',
                        }
                    },
                    fontFamily: {
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white">

    <!-- 4-PERSON DOCTOR SELECTION UI -->
    <section class="max-w-7xl mx-auto px-6 py-24">
        <div class="mb-20 text-center">
            <h2 class="font-serif text-5xl text-brand-dark mb-6">Our Medical Experts</h2>
            <div class="w-20 h-1 bg-brand-pink mx-auto"></div>
        </div>

        <!-- Grid set to 4 columns on large screens -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            
            <!-- Doctor 1 -->
            <div class="group bg-white border border-neutral-100 rounded-[2rem] p-5 shadow-sm hover:shadow-2xl transition-all duration-500">
                <div class="relative w-full h-72 rounded-[1.5rem] overflow-hidden mb-6">
                     <img src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <h3 class="text-xl font-serif text-brand-dark">Dr. Sophia Martinez</h3>
                <p class="text-[10px] text-brand-pink uppercase font-bold mt-1 mb-5 tracking-widest">Lead Dermatologist</p>
                <a href="date.php" class="block text-center w-full bg-brand-lightPink/50 hover:bg-brand-pink text-brand-dark hover:text-white py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all">Select</a>
            </div>

            <!-- Doctor 2 -->
            <div class="group bg-white border border-neutral-100 rounded-[2rem] p-5 shadow-sm hover:shadow-2xl transition-all duration-500">
                <div class="relative w-full h-72 rounded-[1.5rem] overflow-hidden mb-6">
                    <img src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <h3 class="text-xl font-serif text-brand-dark">Dr. Julian Thorne</h3>
                <p class="text-[10px] text-brand-pink uppercase font-bold mt-1 mb-5 tracking-widest">Aesthetic Specialist</p>
                <a href="#" class="block text-center w-full bg-brand-lightPink/50 hover:bg-brand-pink text-brand-dark hover:text-white py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all">Select</a>
            </div>

            <!-- Doctor 3 -->
            <div class="group bg-white border border-neutral-100 rounded-[2rem] p-5 shadow-sm hover:shadow-2xl transition-all duration-500">
                <div class="relative w-full h-72 rounded-[1.5rem] overflow-hidden mb-6">
                    <img src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <h3 class="text-xl font-serif text-brand-dark">Dr. Amara Okafor</h3>
                <p class="text-[10px] text-brand-pink uppercase font-bold mt-1 mb-5 tracking-widest">Cosmetic Expert</p>
                <a href="#" class="block text-center w-full bg-brand-lightPink/50 hover:bg-brand-pink text-brand-dark hover:text-white py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all">Select</a>
            </div>

            <!-- Doctor 4 -->
            <div class="group bg-white border border-neutral-100 rounded-[2rem] p-5 shadow-sm hover:shadow-2xl transition-all duration-500">
                <div class="relative w-full h-72 rounded-[1.5rem] overflow-hidden mb-6">
                    <img src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <h3 class="text-xl font-serif text-brand-dark">Dr. Marcus Chen</h3>
                <p class="text-[10px] text-brand-pink uppercase font-bold mt-1 mb-5 tracking-widest">Skin Surgeon</p>
                <a href="#" class="block text-center w-full bg-brand-lightPink/50 hover:bg-brand-pink text-brand-dark hover:text-white py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all">Select</a>
            </div>

        </div>
    </section>

</body>
</html>