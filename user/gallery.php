<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Media Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
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
    <style>
        /* Smooth fade-in animation for gallery filtered states */
        .gallery-item {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .gallery-item.hidden {
            display: none;
            opacity: 0;
            transform: scale(0.9);
        }
    </style>
</head>
<body class="bg-[#FFF0F2]/30 min-h-screen text-slate-800 antialiased">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-[#FF6584] block mb-2">Our Visual Journey</span>
            <h1 class="font-serif text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 tracking-tight leading-tight">
                Clinic Gallery & Results
            </h1>
            <p class="text-xs md:text-sm text-slate-400 mt-3 font-medium leading-relaxed">
                Take a virtual tour of our state-of-the-art clinic spaces, explore advanced treatments, and discover inspiring real patient transformations.
            </p>
        </div>

        <div class="flex flex-wrap justify-center items-center gap-2 mb-10">
            <button onclick="filterGallery('all')" class="filter-btn px-5 py-2.5 rounded-full text-xs font-semibold bg-slate-900 text-white shadow-sm transition-all duration-300">
                All Media
            </button>
            <button onclick="filterGallery('clinic')" class="filter-btn px-5 py-2.5 rounded-full text-xs font-semibold bg-white text-slate-500 border border-slate-100 hover:bg-slate-50 transition-all duration-300">
                Clinic Spaces
            </button>
            <button onclick="filterGallery('treatment')" class="filter-btn px-5 py-2.5 rounded-full text-xs font-semibold bg-white text-slate-500 border border-slate-100 hover:bg-slate-50 transition-all duration-300">
                Treatments
            </button>
            <button onclick="filterGallery('results')" class="filter-btn px-5 py-2.5 rounded-full text-xs font-semibold bg-white text-slate-500 border border-slate-100 hover:bg-slate-50 transition-all duration-300">
                Before & After
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            
            <div class="gallery-item group relative bg-white rounded-2xl overflow-hidden border border-pink-100/30 shadow-xs cursor-pointer" data-category="clinic" onclick="openLightbox(this)">
                <div class="aspect-[4/3] w-full overflow-hidden bg-slate-100 relative">
                    <img src="https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&q=80&w=800" alt="Premium Consultation Suite" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                        <span class="text-white text-xs font-medium"><i class="fa-solid fa-expand mr-1.5 text-[10px]"></i> View Full Image</span>
                    </div>
                </div>
                <div class="p-4">
                    <span class="text-[9px] font-bold tracking-wider uppercase text-[#FF6584]">Clinic Spaces</span>
                    <h3 class="font-bold text-sm text-slate-800 mt-0.5">Consultation Room 1</h3>
                </div>
            </div>

            <div class="gallery-item group relative bg-white rounded-2xl overflow-hidden border border-pink-100/30 shadow-xs cursor-pointer" data-category="treatment" onclick="openLightbox(this)">
                <div class="aspect-[4/3] w-full overflow-hidden bg-slate-100 relative">
                    <img src="https://images.unsplash.com/photo-1512290923902-8a9f81dc236c?auto=format&fit=crop&q=80&w=800" alt="Facial Therapy Routine" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                        <span class="text-white text-xs font-medium"><i class="fa-solid fa-expand mr-1.5 text-[10px]"></i> View Full Image</span>
                    </div>
                </div>
                <div class="p-4">
                    <span class="text-[9px] font-bold tracking-wider uppercase text-[#FF6584]">Treatments</span>
                    <h3 class="font-bold text-sm text-slate-800 mt-0.5">Laser Skin Resurfacing</h3>
                </div>
            </div>

            <div class="gallery-item group relative bg-white rounded-2xl overflow-hidden border border-pink-100/30 shadow-xs cursor-pointer" data-category="results" onclick="openLightbox(this)">
                <div class="aspect-[4/3] w-full overflow-hidden bg-slate-100 relative">
                    <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&q=80&w=800" alt="Acne Clear Progress Case" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                        <span class="text-white text-xs font-medium"><i class="fa-solid fa-expand mr-1.5 text-[10px]"></i> View Full Image</span>
                    </div>
                </div>
                <div class="p-4">
                    <span class="text-[9px] font-bold tracking-wider uppercase text-[#FF6584]">Before & After</span>
                    <h3 class="font-bold text-sm text-slate-800 mt-0.5">Acne Scar Repair Program</h3>
                </div>
            </div>

            <div class="gallery-item group relative bg-white rounded-2xl overflow-hidden border border-pink-100/30 shadow-xs cursor-pointer" data-category="clinic" onclick="openLightbox(this)">
                <div class="aspect-[4/3] w-full overflow-hidden bg-slate-100 relative">
                    <img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&q=80&w=800" alt="Clinic Front Lounge Lobby Area" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                        <span class="text-white text-xs font-medium"><i class="fa-solid fa-expand mr-1.5 text-[10px]"></i> View Full Image</span>
                    </div>
                </div>
                <div class="p-4">
                    <span class="text-[9px] font-bold tracking-wider uppercase text-[#FF6584]">Clinic Spaces</span>
                    <h3 class="font-bold text-sm text-slate-800 mt-0.5">Reception & Lounge Area</h3>
                </div>
            </div>

            <div class="gallery-item group relative bg-white rounded-2xl overflow-hidden border border-pink-100/30 shadow-xs cursor-pointer" data-category="treatment" onclick="openLightbox(this)">
                <div class="aspect-[4/3] w-full overflow-hidden bg-slate-100 relative">
                    <img src="https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&q=80&w=800" alt="Hydrafacial Skin Peeling Procedure" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                        <span class="text-white text-xs font-medium"><i class="fa-solid fa-expand mr-1.5 text-[10px]"></i> View Full Image</span>
                    </div>
                </div>
                <div class="p-4">
                    <span class="text-[9px] font-bold tracking-wider uppercase text-[#FF6584]">Treatments</span>
                    <h3 class="font-bold text-sm text-slate-800 mt-0.5">Deep Detox Hydrafacial</h3>
                </div>
            </div>

            <div class="gallery-item group relative bg-white rounded-2xl overflow-hidden border border-pink-100/30 shadow-xs cursor-pointer" data-category="results" onclick="openLightbox(this)">
                <div class="aspect-[4/3] w-full overflow-hidden bg-slate-100 relative">
                    <img src="https://images.unsplash.com/photo-1614859324967-bdf461fcf769?auto=format&fit=crop&q=80&w=800" alt="Anti aging Skin Tone Glow Treatment" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                        <span class="text-white text-xs font-medium"><i class="fa-solid fa-expand mr-1.5 text-[10px]"></i> View Full Image</span>
                    </div>
                </div>
                <div class="p-4">
                    <span class="text-[9px] font-bold tracking-wider uppercase text-[#FF6584]">Before & After</span>
                    <h3 class="font-bold text-sm text-slate-800 mt-0.5">Anti-Aging Glow Lifting</h3>
                </div>
            </div>

        </div>
    </div>

    <div id="lightbox" class="fixed inset-0 bg-black/95 flex flex-col justify-center items-center z-50 hidden opacity-0 transition-opacity duration-300">
        <button onclick="closeLightbox()" class="absolute top-5 right-5 text-white/80 hover:text-white text-2xl p-2 transition-colors">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="max-w-4xl max-h-[80vh] px-4">
            <img id="lightbox-img" src="" alt="Zoomed View" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl transform scale-95 transition-transform duration-300">
        </div>

        <div class="text-center mt-4 px-4">
            <span id="lightbox-cat" class="text-[10px] font-bold uppercase tracking-wider text-[#FF6584]">Category</span>
            <h4 id="lightbox-title" class="text-white text-base font-semibold mt-0.5">Image Title Mapping</h4>
        </div>
    </div>

    <script>
        // 1. GRID ANIMATION FILTER LOGIC
        function filterGallery(category) {
            const items = document.querySelectorAll('.gallery-item');
            const buttons = document.querySelectorAll('.filter-btn');

            // Reset selected button styles layout framework classes
            buttons.forEach(btn => {
                btn.classList.remove('bg-slate-900', 'text-white', 'shadow-sm');
                btn.classList.add('bg-white', 'text-slate-500', 'border', 'border-slate-100');
            });

            // Highlight target event action node configuration
            event.currentTarget.classList.remove('bg-white', 'text-slate-500', 'border', 'border-slate-100');
            event.currentTarget.classList.add('bg-slate-900', 'text-white', 'shadow-sm');

            // Toggle item block rendering parameters securely
            items.forEach(item => {
                if (category === 'all' || item.getAttribute('data-category') === category) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }

        // 2. MODAL MODAL VIEW INTERACTION LAYER LOGIC
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        const lightboxTitle = document.getElementById('lightbox-title');
        const lightboxCat = document.getElementById('lightbox-cat');

        function openLightbox(element) {
            const imgUrl = element.querySelector('img').src;
            const titleText = element.querySelector('h3').innerText;
            const categoryText = element.querySelector('span').innerText;

            lightboxImg.src = imgUrl;
            lightboxTitle.innerText = titleText;
            lightboxCat.innerText = categoryText;

            lightbox.classList.remove('hidden');
            setTimeout(() => {
                lightbox.classList.remove('opacity-0');
                lightboxImg.classList.remove('scale-95');
            }, 50);
        }

        function closeLightbox() {
            lightbox.classList.add('opacity-0');
            lightboxImg.classList.add('scale-95');
            setTimeout(() => {
                lightbox.classList.add('hidden');
            }, 300);
        }

        // Escape key integration closure callback option tracking
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>

</body>
</html>