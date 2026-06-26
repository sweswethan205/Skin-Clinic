<!DOCTYPE html>

<html class="scroll-smooth" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Skin Clinic | Reveal Your Best Skin</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                "surface-dim": "#ccdbf4",
                "tertiary-container": "#7c7275",
                "outline": "#8a7174",
                "background": "#f8f9ff",
                "primary-fixed-dim": "#ffb2bf",
                "inverse-primary": "#ffb2bf",
                "on-surface": "#0d1c2e",
                "on-secondary-fixed": "#161d1f",
                "secondary": "#586062",
                "surface-container-lowest": "#ffffff",
                "secondary-container": "#dae1e3",
                "surface-container": "#e5eeff",
                "surface-tint": "#a83253",
                "error-container": "#ffdad6",
                "on-tertiary-fixed": "#201a1c",
                "on-primary-fixed-variant": "#88183c",
                "surface-variant": "#d4e4fc",
                "on-error": "#ffffff",
                "surface-container-low": "#eff4ff",
                "surface-container-high": "#dce9ff",
                "secondary-fixed": "#dde4e6",
                "on-tertiary-fixed-variant": "#4d4547",
                "surface-bright": "#f8f9ff",
                "on-primary-container": "#fffbff",
                "primary-fixed": "#ffd9de",
                "on-surface-variant": "#564145",
                "surface-container-highest": "#d4e4fc",
                "inverse-on-surface": "#eaf1ff",
                "on-background": "#0d1c2e",
                "on-secondary": "#ffffff",
                "outline-variant": "#ddbfc3",
                "inverse-surface": "#223144",
                "on-secondary-fixed-variant": "#41484a",
                "surface": "#f8f9ff",
                "on-primary-fixed": "#3f0015",
                "error": "#ba1a1a",
                "on-error-container": "#93000a",
                "tertiary-fixed-dim": "#d0c3c6",
                "on-secondary-container": "#5d6466",
                "primary": "#a53051",
                "tertiary": "#635a5c",
                "tertiary-fixed": "#eddfe2",
                "on-tertiary": "#ffffff",
                "on-primary": "#ffffff",
                "on-tertiary-container": "#fffbff",
                "secondary-fixed-dim": "#c1c8ca",
                "primary-container": "#c54869"
            },
            "borderRadius": {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "full": "9999px"
            },
            "spacing": {
                "section-gap": "80px",
                "container-max": "1200px",
                "stack-sm": "12px",
                "gutter": "24px",
                "unit": "8px",
                "stack-md": "24px",
                "margin-mobile": "16px"
            },
            "fontFamily": {
                "body-md": ["Manrope"],
                "headline-md": ["Manrope"],
                "body-lg": ["Manrope"],
                "headline-lg": ["Manrope"],
                "headline-lg-mobile": ["Manrope"],
                "headline-xl": ["Manrope"],
                "label-md": ["Manrope"]
            },
            "fontSize": {
                "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}],
                "headline-md": ["24px", {"lineHeight": "1.4", "fontWeight": "600"}],
                "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                "headline-lg": ["32px", {"lineHeight": "1.3", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                "headline-lg-mobile": ["28px", {"lineHeight": "1.3", "fontWeight": "700"}],
                "headline-xl": ["48px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                "label-md": ["14px", {"lineHeight": "1.2", "letterSpacing": "0.05em", "fontWeight": "600"}]
            }
          },
        },
      }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
        }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .before-after-handle {
            transition: left 0.1s ease-out;
        }
    </style>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
</head>
<body class="bg-background text-on-surface font-body-md overflow-x-hidden">
<!-- Top Navigation Bar (Shared Component Strategy) -->
<header class="sticky top-0 w-full z-[100] bg-background/95 backdrop-blur-sm shadow-sm">
<div class="flex justify-between items-center w-full px-margin-mobile py-4 max-w-container-max mx-auto">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-3xl">face_6</span>
<span class="text-headline-md font-headline-md font-bold text-on-surface">Skin Clinic</span>
</div>
<div class="hidden md:flex gap-8 items-center">
<a class="text-primary font-bold font-label-md text-label-md" href="#">Home</a>
<a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-md text-label-md" href="#">Treatments</a>
<a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-md text-label-md" href="#">Results</a>
<a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-md text-label-md" href="#">Contact</a>
</div>
<button class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md text-label-md hover:bg-primary-container transition-all active:scale-95">
                Book
            </button>
</div>
</header>
<!-- Hero Section -->
<section class="relative min-h-[751px] flex items-center pt-8 md:pt-0 overflow-hidden">
<div class="max-w-container-max mx-auto px-margin-mobile w-full grid grid-cols-1 md:grid-cols-2 gap-stack-md items-center z-10">
<div class="flex flex-col gap-stack-md animate-fade-in">
<div>
<span class="text-primary font-label-md text-label-md tracking-[0.2em] uppercase">Advanced Care for Healthy Skin</span>
<h1 class="text-headline-lg-mobile md:text-headline-xl font-headline-xl text-on-surface mt-2">
                        Reveal Your <br/><span class="text-primary">Best Skin</span>
</h1>
<p class="text-body-lg font-body-lg text-on-surface-variant max-w-md mt-4">
                        We provide advanced skin treatments with expert dermatologists and modern technology for healthy, beautiful and radiant skin.
                    </p>
</div>
<div class="flex flex-col sm:flex-row gap-4 pt-4">
<button class="bg-primary text-on-primary px-8 py-4 rounded-lg font-label-md text-label-md flex items-center justify-center gap-2 shadow-lg shadow-primary/20 hover:translate-y-[-2px] transition-all active:scale-95">
<span class="material-symbols-outlined">calendar_today</span>
                        Book Appointment
                    </button>
<button class="bg-surface-container-lowest text-primary px-8 py-4 rounded-lg font-label-md text-label-md flex items-center justify-center gap-2 border border-primary/20 hover:bg-surface-container transition-all">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">play_circle</span>
                        Watch Video
                    </button>
</div>
</div>
<div class="relative mt-8 md:mt-0">
<div class="relative z-10 rounded-3xl overflow-hidden aspect-[4/5] shadow-2xl">
<img class="w-full h-full object-cover" data-alt="A professional studio portrait of a young woman with radiant, flawless skin, looking calmly at the camera while touching her cheek. The lighting is soft and high-key, emphasizing natural glow and professional skin care results. The background is a clean, minimalist studio setting with soft coral and white floral accents, reflecting a high-end medical clinic aesthetic." src="https://lh3.googleusercontent.com/aida-public/AB6AXuB1MHlBEjby2OgTMZekbVFmzUznC1a8IYxZqt7e0aOlNB2vfTdmAVKmLFIqziMgt0LdUh4aREIlJxICbvRWDr5nGJasfoWU0iPOng1w-vG_z4G1fDBBLSZrLsrxm5daux-2j2ciPdqSJnJr-xc064ZeuQvy3ce1GK30mF7A8ApBtiaYMLwcPLF_RkztxAQH70j4FJn3Y7pFg5M_kiThGTV5xFu2tKrl2UkxQTroqZgR91fkMCjPeYGgpJQghQ0vw8YGHQFWth8lcd0g"/>
</div>
<!-- Decorative Elements -->
<div class="absolute -top-10 -right-10 w-40 h-40 bg-primary/10 rounded-full blur-3xl"></div>
<div class="absolute -bottom-10 -left-10 w-60 h-60 bg-surface-variant rounded-full blur-3xl"></div>
</div>
</div>
<!-- Background Animation Layer -->
<div class="absolute inset-0 z-0 opacity-40">

</div>
</section>
<!-- Features Section -->
<section class="py-section-gap bg-surface-container-lowest">
<div class="max-w-container-max mx-auto px-margin-mobile">
<div class="grid grid-cols-2 md:grid-cols-4 gap-gutter">
<div class="flex flex-col items-center text-center p-6 bg-background rounded-2xl border border-outline-variant/30 hover:shadow-md transition-shadow">
<div class="w-14 h-14 bg-primary-fixed rounded-full flex items-center justify-center mb-4 text-primary">
<span class="material-symbols-outlined text-3xl">medical_services</span>
</div>
<h3 class="font-headline-md text-headline-md text-sm mb-2">Expert Doctors</h3>
<p class="text-body-md text-on-surface-variant text-xs leading-relaxed">Experienced and certified dermatologists</p>
</div>
<div class="flex flex-col items-center text-center p-6 bg-background rounded-2xl border border-outline-variant/30 hover:shadow-md transition-shadow">
<div class="w-14 h-14 bg-surface-variant rounded-full flex items-center justify-center mb-4 text-on-surface-variant">
<span class="material-symbols-outlined text-3xl">verified_user</span>
</div>
<h3 class="font-headline-md text-headline-md text-sm mb-2">Safe &amp; Effective</h3>
<p class="text-body-md text-on-surface-variant text-xs leading-relaxed">FDA-approved products and tech</p>
</div>
<div class="flex flex-col items-center text-center p-6 bg-background rounded-2xl border border-outline-variant/30 hover:shadow-md transition-shadow">
<div class="w-14 h-14 bg-primary-fixed rounded-full flex items-center justify-center mb-4 text-primary">
<span class="material-symbols-outlined text-3xl">timer</span>
</div>
<h3 class="font-headline-md text-headline-md text-sm mb-2">Quick Results</h3>
<p class="text-body-md text-on-surface-variant text-xs leading-relaxed">Visible results with custom plans</p>
</div>
<div class="flex flex-col items-center text-center p-6 bg-background rounded-2xl border border-outline-variant/30 hover:shadow-md transition-shadow">
<div class="w-14 h-14 bg-surface-variant rounded-full flex items-center justify-center mb-4 text-on-surface-variant">
<span class="material-symbols-outlined text-3xl">favorite</span>
</div>
<h3 class="font-headline-md text-headline-md text-sm mb-2">Patient Care</h3>
<p class="text-body-md text-on-surface-variant text-xs leading-relaxed">Your satisfaction is our priority</p>
</div>
</div>
</div>
</section>
<!-- Popular Treatments Section -->
<section class="py-section-gap overflow-hidden">
<div class="max-w-container-max mx-auto px-margin-mobile mb-stack-md flex justify-between items-end">
<div>
<span class="text-primary font-label-md text-label-md tracking-wider">OUR TREATMENTS</span>
<h2 class="text-headline-lg font-headline-lg mt-2">Popular Treatments</h2>
</div>
<button class="hidden md:flex items-center gap-2 text-primary font-label-md text-label-md group">
                View All Treatments
                <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
</button>
</div>
<div class="flex gap-gutter overflow-x-auto px-margin-mobile md:px-[calc((100vw-1200px)/2)] hide-scrollbar snap-x snap-mandatory">
<!-- Treatment Card 1 -->
<div class="min-w-[280px] md:min-w-[320px] snap-start group">
<div class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm border border-outline-variant/20 hover:shadow-xl transition-all h-full">
<div class="h-56 overflow-hidden">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="A close-up photograph of a professional facial treatment session. A clinician's gloved hands are gently applying a soothing mask to a patient's clear skin. The setting is bright, clinical yet serene, with a color palette of whites and soft pinks, emphasizing cleanliness and expert care." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBZUbsGkMN2bB8m4mCCyGxLQSqUUby-v_QaQr3SelgFxQVXCO3KNC9U0RX-L2blvhHDEw_cyz-dP9KL--szHVIfzbghNizUCB0ZlcQZ89zG2LNjOI0CrLgk8rD1FuG6jPSqVivo_MCvhYdBM2aqutQQhF6sk6a3C23TLOj2OGYA047CN0V38y83U0073JFCzXI7R6VCyV8szAZxpt5cPnSpVVZ-IqJPgD9SuTxgB1nmdtLJrHNHkdgK0ZrpjCw3hOyx7Dj48jS4Iif9"/>
</div>
<div class="p-6 flex flex-col gap-3">
<h4 class="font-headline-md text-headline-md text-lg">Acne Treatment</h4>
<p class="text-on-surface-variant text-body-md text-sm line-clamp-2">Effective solutions for acne and prevent breakouts with medical grade products.</p>
<div class="flex justify-between items-center mt-4">
<span class="text-primary font-bold">From 30,000 MMK</span>
<span class="material-symbols-outlined text-primary-container">chevron_right</span>
</div>
</div>
</div>
</div>
<!-- Treatment Card 2 -->
<div class="min-w-[280px] md:min-w-[320px] snap-start group">
<div class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm border border-outline-variant/20 hover:shadow-xl transition-all h-full">
<div class="h-56 overflow-hidden">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="A high-tech laser therapy session for skin rejuvenation. The image shows a sleek medical laser device being used on a patient's face by a specialist in a modern, pristine clinical environment. High-key lighting and a palette of cool whites and warm coral accents create a futuristic yet comforting feel." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAy0EsUdTkcjCC1EHqA7jFWuhefwd4E8zzF-kNq9FA7Z_G2y6n5-MpSgzNzcSslm37VvbtJp3NLMlUsDsEAh_x2KwtKFqz8JY2GGjMoaFjpAH78J3oLZvbjViQgisQ1ZUD6SRXHP25RIr8XbPa-3CcgziPHPDga10SxpeQ-kamJRQuluchb8FsCchDX0B4tr6_CHcgvMOa6YXS8Kj_R5StUeq3ayaHd0PzqSfIdk5Cyo5aM6gPThWUrLHpqb-Rini_sXXr2RG6n2d3i"/>
</div>
<div class="p-6 flex flex-col gap-3">
<h4 class="font-headline-md text-headline-md text-lg">Laser Treatment</h4>
<p class="text-on-surface-variant text-body-md text-sm line-clamp-2">Advanced laser technology for skin problems like pigmentation and scarring.</p>
<div class="flex justify-between items-center mt-4">
<span class="text-primary font-bold">From 80,000 MMK</span>
<span class="material-symbols-outlined text-primary-container">chevron_right</span>
</div>
</div>
</div>
</div>
<!-- Treatment Card 3 -->
<div class="min-w-[280px] md:min-w-[320px] snap-start group">
<div class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm border border-outline-variant/20 hover:shadow-xl transition-all h-full">
<div class="h-56 overflow-hidden">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="A serene spa facial treatment featuring botanical ingredients and steam therapy. The aesthetic is extremely clean and airy, with soft lighting highlighting the dewy skin of the client. Minimalist luxury vibes with a focus on holistic wellness and professional dermatological precision." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDmgqH-TwDEV4FjBkw06Mr_o_4QAG7WSQIqGCzZN7RfmyBevExAkrDre97rc5Lw3tOA6VmSbS4DsNfO826rqVANg0b3Pb5x4hqsFOgCFMLllXsszul6qWDHmFRmo2sIs6WNBbADsGW5CYpr8w3sW7nGFQQH4RZLuQmsZAzLISnRrXH2eFkJhekzAp0L3RwhiVNpbTQjrImAnxAoux6YqGgQwu5AFxoVPSWpTR67VuHnku_5AGZs9ptBucluk4fyQw3N750HYdLQL6in"/>
</div>
<div class="p-6 flex flex-col gap-3">
<h4 class="font-headline-md text-headline-md text-lg">Facial Care</h4>
<p class="text-on-surface-variant text-body-md text-sm line-clamp-2">Deep cleansing and nourishing facial treatments for immediate glowing results.</p>
<div class="flex justify-between items-center mt-4">
<span class="text-primary font-bold">From 25,000 MMK</span>
<span class="material-symbols-outlined text-primary-container">chevron_right</span>
</div>
</div>
</div>
</div>
<!-- Treatment Card 4 -->
<div class="min-w-[280px] md:min-w-[320px] snap-start group">
<div class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm border border-outline-variant/20 hover:shadow-xl transition-all h-full">
<div class="h-56 overflow-hidden">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Micro-needling or PRP therapy session being performed in a sterile, upscale medical room. Focus on the sophisticated tools and the professional demeanor of the clinician. The mood is scientific and trustworthy, using a palette of clinical blues and soft coral highlights." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDhREHFivj6fittbvgSrtpFjRHN1mEA4Ds2MXcGpFhZ_B3uaPEavZ5qgnof6XdwdP66U9V4Uzq-lULibgetkhSULWjINCnqT_SHd1-kZaGpKxi8Qviu4MMW_rQctBVD5lnVSc8vxsNxrWsBVa4WJqHkeZITX78GfrsLa9vkCQsx9yODONbM2GkgpkB9Oqc-1KPa8UQhdjgzOPhvdb0Cu2QZt-N81OfxiiA5zG8HerCr_9QAPArATtGlZM7A9v3q9vCQ-2iXOY8GI8XB"/>
</div>
<div class="p-6 flex flex-col gap-3">
<h4 class="font-headline-md text-headline-md text-lg">PRP Therapy</h4>
<p class="text-on-surface-variant text-body-md text-sm line-clamp-2">Natural treatment using your own plasma for skin rejuvenation and intense healing.</p>
<div class="flex justify-between items-center mt-4">
<span class="text-primary font-bold">From 120,000 MMK</span>
<span class="material-symbols-outlined text-primary-container">chevron_right</span>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- Stats Section -->
<section class="py-12 opacity-100 transition-opacity animate-in fade-in slide-in-from-bottom-8 duration-1000 bg-surface-container-low">
<div class="max-w-container-max mx-auto px-margin-mobile">
<div class="grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-gutter text-on-primary"><div class="bg-surface-container-lowest p-8 rounded-[32px] shadow-xl border border-outline-variant/20 flex flex-col items-center text-center gap-4 hover:shadow-2xl transition-all duration-500 group">
    <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
        <span class="material-symbols-outlined text-4xl">groups</span>
    </div>
    <div>
        <span class="text-headline-lg font-headline-xl text-on-surface block stat-counter" data-target="10000">10,000</span>
        <span class="text-primary font-bold text-xl">+</span>
    </div>
    <span class="text-label-md font-label-md text-on-surface-variant uppercase tracking-widest">Happy Patients</span>
</div>
<div class="bg-surface-container-lowest p-8 rounded-[32px] shadow-xl border border-outline-variant/20 flex flex-col items-center text-center gap-4 hover:shadow-2xl transition-all duration-500 group">
    <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
        <span class="material-symbols-outlined text-4xl">workspace_premium</span>
    </div>
    <div>
        <span class="text-headline-lg font-headline-xl text-on-surface block stat-counter" data-target="8">8</span>
        <span class="text-primary font-bold text-xl">+</span>
    </div>
    <span class="text-label-md font-label-md text-on-surface-variant uppercase tracking-widest">Years Experience</span>
</div>
<div class="bg-surface-container-lowest p-8 rounded-[32px] shadow-xl border border-outline-variant/20 flex flex-col items-center text-center gap-4 hover:shadow-2xl transition-all duration-500 group">
    <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
        <span class="material-symbols-outlined text-4xl">monitoring</span>
    </div>
    <div>
        <span class="text-headline-lg font-headline-xl text-on-surface block stat-counter" data-target="95">95</span>
        <span class="text-primary font-bold text-xl">%</span>
    </div>
    <span class="text-label-md font-label-md text-on-surface-variant uppercase tracking-widest">Success Rate</span>
</div>
<div class="bg-surface-container-lowest p-8 rounded-[32px] shadow-xl border border-outline-variant/20 flex flex-col items-center text-center gap-4 hover:shadow-2xl transition-all duration-500 group">
    <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
        <span class="material-symbols-outlined text-4xl">verified</span>
    </div>
    <div>
        <span class="text-headline-lg font-headline-xl text-on-surface block stat-counter" data-target="5000">5,000</span>
        <span class="text-primary font-bold text-xl">+</span>
    </div>
    <span class="text-label-md font-label-md text-on-surface-variant uppercase tracking-widest">Treatments</span>
</div></div>
</div>
</section>
<!-- Consultation CTA Section -->
<section class="py-section-gap">
<div class="max-w-container-max mx-auto px-margin-mobile">
<div class="grid grid-cols-1 md:grid-cols-12 gap-gutter items-center bg-surface-container-low rounded-[40px] overflow-hidden">
<div class="md:col-span-5 p-8 md:p-16 flex flex-col gap-6">
<div>
<span class="text-primary font-label-md text-label-md tracking-wider">WHY CHOOSE US</span>
<h2 class="text-headline-lg font-headline-lg mt-2">We Care About Your Skin</h2>
</div>
<ul class="space-y-4">
<li class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<span class="text-body-md font-medium">Personalized treatment plans</span>
</li>
<li class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<span class="text-body-md font-medium">Advanced technology &amp; equipment</span>
</li>
<li class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<span class="text-body-md font-medium">Safe, effective &amp; proven procedures</span>
</li>
</ul>
<div class="pt-4">
<button class="bg-primary text-on-primary px-8 py-4 rounded-xl font-label-md text-label-md w-full md:w-auto shadow-lg shadow-primary/20 hover:opacity-90 active:scale-95 transition-all">
                            Book Free Consultation
                        </button>
</div>
</div>
<div class="md:col-span-7 h-[300px] md:h-full min-h-[400px] relative">
<img class="w-full h-full object-cover" data-alt="A wide-angle photo of a luxurious, modern skin clinic reception and interior. The space is bright with soft white walls, coral-colored designer chairs, and elegant marble floors. The lighting is warm and welcoming, reflecting a spa-like professional medical environment with a focus on premium patient experience." src="https://lh3.googleusercontent.com/aida-public/AB6AXuD-vjqLIIESqIfMB3_-9WgdyXS7Yyql4wVpqYup81G6u4ujk5uHUB0woo0Dzqn4mWDT_XtrOBYSI59W0qaaik4EBIBz9MVgqwvgSpV8UNk-lfcaTD46P9QBlNBomONL53rUXYb9S05kW8nWz5yslAJF_rbu-l-11uT6amQM7lWUGi96pRYV9s8hLeESFHx_Yxk6JCDDGbTAk4ob7gpxL1pCCIOJBqLc6QB5zyEtO8BmwMPFXZqfyaAAeZNOW-9mpsJ_XDw84xtfeOJI"/>
<div class="absolute inset-0 bg-gradient-to-r from-surface-container-low/80 to-transparent md:block hidden"></div>
</div>
</div>
</div>
</section>
<!-- Before & After Results -->
<section class="py-section-gap bg-surface-container-lowest">
<div class="max-w-container-max mx-auto px-margin-mobile text-center mb-stack-md">
<span class="text-primary font-label-md text-label-md tracking-widest">TRANSFORMATIONS</span>
<h2 class="text-headline-lg font-headline-lg mt-2">Real Results, Real People</h2>
</div>
<div class="max-w-container-max mx-auto px-margin-mobile">
<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
<!-- Result Card 1 -->
<div class="flex flex-col gap-4">
<div class="relative group rounded-2xl overflow-hidden aspect-square border-4 border-background shadow-lg overflow-hidden">
<!-- Before & After Slider Logic Mockup -->
<div class="absolute inset-0">
<img class="w-full h-full object-cover" data-alt="Before image: Close up of a cheek with active acne, redness, and inflammation. Clinical lighting, realistic skin texture. Medical aesthetic style." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBbAoEzaKgwyvxomRNDt81U35kuW3r2RTy0t5O7kNO6BzmgP0aI7nibIs3KFX4gpY7jopCAZSpfeHz19f2hCH9dUOQcSxnKC5lTB4o4x_edONFNyUh8NSiPQI_Q98LvyCU9JVovJwQuX6rbFrNMaYLo9YIn3hevTvNvdAZOJwxwEfZdIRsiNL26XdbS2-HAOqEHJox9AbD9IILpwBv3fzDY3EGiWrM2HKDSkGLdYDWIMLaqggYOd4UL58wmX3AzjG1Nnbs-ywQVgpn1"/>
</div>
<div class="absolute inset-0 w-1/2 overflow-hidden border-r-2 border-white before-after-handle" id="slider-1">
<img class="w-full h-full object-cover max-w-none" data-alt="After image: The same cheek from the before image, but with perfectly clear, smooth, and radiant skin after acne treatment. High-key lighting, professional skincare result aesthetic." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAk4WjsjT1_IJpNOKw5mR6fDm8RUwrRWbK0HPLI1cpLs-fAOhwxJV-O6vGgB2jVTpmRmO7adETy81cjdZShHS4aEzsZUXpsR9L2PjYjkK6sw7HbUOwHM7cvEGi2NtprMcbTbsP7-SOHllwGKMJUW962adDiS0h2crkeQxCL4UeLZTyRKC5veNxF8NM1Nl_-9myWo36XCCKVkrDMkgTKPQL_1-J7fo0cMXDohlWC1njNnGwaHvPtpxG0K-wj6CCPniH-eLXAgkO4P1Ze"/>
</div>
<div class="absolute inset-0 flex items-center justify-center pointer-events-none">
<div class="bg-white/90 px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase shadow-sm absolute left-4 top-4">Before</div>
<div class="bg-primary/90 text-white px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase shadow-sm absolute right-4 top-4">After</div>
</div>
</div>
<div class="text-center">
<h4 class="font-headline-md text-lg">Acne Treatment</h4>
<p class="text-on-surface-variant text-sm">Patient result after 3 months</p>
</div>
</div>
<!-- Result Card 2 -->
<div class="flex flex-col gap-4">
<div class="relative group rounded-2xl overflow-hidden aspect-square border-4 border-background shadow-lg overflow-hidden">
<div class="absolute inset-0">
<img class="w-full h-full object-cover" data-alt="Before image: Close up of skin with visible pigmentation, dark spots, and uneven tone. Minimalist clinical photography." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBzfJk9jP57gtt9cQC8W-vM4ned-4pwc9eHyjQAlLGRLDIRCxO5FwfeRm96_k8enSPYvFkFPoDCQGRj4UQJwg_H36WbjMmGp0vsz5d7gBtQat2I1y0CFenHOEQY5vmxM9Jh_B_ClYAxyuHOOO-PI9Z4j1o4pEWMSIEUO2LDwdAS-u-zg_7PL-IPT34iExusCOGGg3DceVkJ_i_dBAMG8VBhhd231wZWb5ZR3NOgXtAVtrRRnbPH_keHgEg0TKz7LkoOP1xiofaLvu1J"/>
</div>
<div class="absolute inset-0 w-1/2 overflow-hidden border-r-2 border-white before-after-handle">
<img class="w-full h-full object-cover max-w-none" data-alt="After image: The same skin area with completely even tone, reduced pigmentation, and a bright, healthy glow. Luxury clinic result photography." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAsv60D4bpUWABe9csLLpAalC2hXGHCH6Fh339mW3TGIsJuVFbYfN8F5me81ylk3Pil41J-nhb_cMA2UFzZNvQm0F5Hwd4W0YeIV4uFwX3yz-1X7hlsYSEWdVh5max1_h4DCAzmIxZF0KIQKRtV-zDYtzAM6bS0LImCxbW27F5fU7EepC7BFoJzP_LsTnezxIcPnkWjL2PZ2ob6XqE_H_Mkhx--8RZAy4AlQWCV8--32m3GOBhIi6T9UQxh0Ktx9uDfYsuAA7l-bIsv"/>
</div>
<div class="absolute inset-0 flex items-center justify-center pointer-events-none">
<div class="bg-white/90 px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase absolute left-4 top-4">Before</div>
<div class="bg-primary/90 text-white px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase absolute right-4 top-4">After</div>
</div>
</div>
<div class="text-center">
<h4 class="font-headline-md text-lg">Laser Rejuvenation</h4>
<p class="text-on-surface-variant text-sm">Patient result after 4 sessions</p>
</div>
</div>
<!-- Result Card 3 -->
<div class="flex flex-col gap-4">
<div class="relative group rounded-2xl overflow-hidden aspect-square border-4 border-background shadow-lg overflow-hidden">
<div class="absolute inset-0">
<img class="w-full h-full object-cover" data-alt="Before image: Face with visible fine lines around the eyes and dull skin appearance. Scientific dermatology style." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDHCYxA7pz0BJ0XqdfEA2vo8BMPxyO_ihYFDFJB0gATqBcLrEiY34eu8Af1mSCLTM7JpdeQTPbQHKPord65uwKNp4NMhHbfTGp6qs3lOb7S4sNRlCQYrp8L_-UDYXAuJiBRWEaqvzwIOBNRm7h586NV7H3g66n__ZgUXWf8KuGBWto4J-dfZ0RCb4YQUqhhAnORuJtduzzZpcqpdKD-wqUchQOlMRNlrzkUv1LgzAxsphnfOMS4eQTM_xvsL4gm2OX5xR4kM3Cc44Ii"/>
</div>
<div class="absolute inset-0 w-1/2 overflow-hidden border-r-2 border-white before-after-handle">
<img class="w-full h-full object-cover max-w-none" data-alt="After image: Visible skin tightening effect, reduced wrinkles, and a luminous, lifted facial appearance. Premium beauty clinic marketing aesthetic." src="https://lh3.googleusercontent.com/aida-public/AB6AXuC1flmOSzM1xE3DfAevpb4Ljqrrq_uIBYIy8viXjxHyBOMvggr6orCwYelOgi_6JZQQMeGsUI5LBCEijANK5zCly9H5YMhkhXZ22zKVCNCKXGgqCXO8C1WiyHgbTeJaAAxchCx1QY0xEvtXeNxCTMR2Y6HXwvioA8PQ3sL7HYSrG5Ohci26FLgdS1yT5Rvut9MtW98lK5p_CptvrLtxloiSnnXIXsqky0tfjuXMtCT6w4Sw4o0w36_C3UJZ0ISH6FiACY2nRGFa-xbB"/>
</div>
<div class="absolute inset-0 flex items-center justify-center pointer-events-none">
<div class="bg-white/90 px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase absolute left-4 top-4">Before</div>
<div class="bg-primary/90 text-white px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase absolute right-4 top-4">After</div>
</div>
</div>
<div class="text-center">
<h4 class="font-headline-md text-lg">Anti-Aging Facial</h4>
<p class="text-on-surface-variant text-sm">Patient result after 1 session</p>
</div>
</div>
</div>
</div>
</section>
<!-- Testimonials -->
<section class="py-section-gap overflow-hidden relative">
<div class="max-w-container-max mx-auto px-margin-mobile relative z-10">
<div class="text-center mb-stack-md">
<span class="text-primary font-label-md text-label-md tracking-widest">TESTIMONIALS</span>
<h2 class="text-headline-lg font-headline-lg mt-2">What Our Patients Say</h2>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
<!-- Testimonial 1 -->
<div class="bg-surface-container-low p-8 rounded-3xl flex flex-col gap-6 hover:shadow-lg transition-shadow">
<div class="flex gap-1 text-primary">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
<p class="text-body-md italic text-on-surface-variant">"The doctors are very professional and friendly. My skin improved so much after their treatment. Highly recommend!"</p>
<div class="flex items-center gap-4 pt-4 border-t border-outline-variant/30">
<img class="w-12 h-12 rounded-full object-cover" data-alt="Close up portrait of a smiling woman with clear, glowing skin, looking happy and satisfied. Natural lighting, warm and authentic feel." src="https://lh3.googleusercontent.com/aida-public/AB6AXuCy6cQk3HV5BHZ2ocOvXyHe0DWSJCJkKAoyWn8yNkKja8oPlqmxzWOD8vbqYR5atYqnXKCHes01c763uce7mAFv95YP_ktJOS-3yzNuSxlmOtuF8Cqu2i4NY_chOUqIOAEv0vMzkKpCRQdPsIC9bd4S-DhUGsIfsQMHWjtmuCQP8oHA9S-g-QexCmCyxVU0ThrTNV3jeDzayHT_RxL4eM5RA8BX1kS9eORVPVLdSU3HOLQb9QIN4E1NTdlmUEzgEF5_WbOJMbM-jHdj"/>
<div>
<p class="font-bold text-on-surface">May Thandar Aung</p>
<p class="text-xs text-primary font-medium">Acne Treatment</p>
</div>
</div>
</div>
<!-- Testimonial 2 -->
<div class="bg-surface-container-low p-8 rounded-3xl flex flex-col gap-6 hover:shadow-lg transition-shadow">
<div class="flex gap-1 text-primary">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
<p class="text-body-md italic text-on-surface-variant">"Amazing results! The clinic is clean, staff is super nice, and the treatment really works. I feel so much more confident."</p>
<div class="flex items-center gap-4 pt-4 border-t border-outline-variant/30">
<img class="w-12 h-12 rounded-full object-cover" data-alt="Smiling patient after a successful skin treatment. High-key clinical aesthetic, soft pink and white tones." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBxC_Ql4hrX0wGjMq30B8W9KXhJHMnDxnDEp8Z5ctRRaM0a2HPVqJVwHUhk4cIhkSbv8PnQ2bBNqnDnYwlQWqGfI1ilZsYfU40Pki-4J525TZ9lka_u3wSbQAm2oVQbbH8J47AtnBcRVR7ZjUQJDM2oO0wpqRuj8YwGzyMZqCgoLGV1BgfuRFl2WDLPlSieP64njMaY-r1kscqj5cCYVmBJDzwGpu1QQDSKaELhnMupQlPUlRjjarHpfTeRcF_OCu0NBfpi1Vxf36E5"/>
<div>
<p class="font-bold text-on-surface">Khin Myat Noe</p>
<p class="text-xs text-primary font-medium">Laser Treatment</p>
</div>
</div>
</div>
<!-- Testimonial 3 -->
<div class="bg-surface-container-low p-8 rounded-3xl flex flex-col gap-6 hover:shadow-lg transition-shadow">
<div class="flex gap-1 text-primary">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
<p class="text-body-md italic text-on-surface-variant">"I love the facial care here. My skin feels so fresh, smooth and glowing. It's like a spa and clinic in one."</p>
<div class="flex items-center gap-4 pt-4 border-t border-outline-variant/30">
<img class="w-12 h-12 rounded-full object-cover" data-alt="Happy patient portrait with flawless skin, reflecting the clinical precision and spa-like comfort of the skin clinic." src="https://lh3.googleusercontent.com/aida-public/AB6AXuC485TznsEvyXP5x74ZAKdwu2fKf1v8GIH7b4EygDjN1_VTP5qe6S9zzCFhHuhYJd41Nf3rMmU1Q3G2jVLI2seS7czLd1aRhDPlTLScMn7wyMFkqKrOG8k9LS3ELvh6pRMNCJjEkKeCq0Q3sYolv8y67zW_firkwOA9uXFjUppMaK5AsM0I5E5f1hh5SH3iA6zpTrg4mbl0A6cLoHziPCe0zEJJR5z6jqeI0gixd_7mEbwMUPIeM4pKhQO0WuSMlqfw3K9RWKGsQ9We"/>
<div>
<p class="font-bold text-on-surface">Thiri Yu Mon</p>
<p class="text-xs text-primary font-medium">Facial Care</p>
</div>
</div>
</div>
</div>
</div>
<!-- Background Aura -->
<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-primary/5 rounded-full blur-[120px] -z-0"></div>
</section>
<!-- Latest Blog Section -->
<section class="py-section-gap">
<div class="max-w-container-max mx-auto px-margin-mobile flex justify-between items-end mb-stack-md">
<div>
<span class="text-primary font-label-md text-label-md tracking-wider uppercase">Education</span>
<h2 class="text-headline-lg font-headline-lg mt-2">Latest From Our Blog</h2>
</div>
<button class="hidden md:flex items-center gap-2 text-primary font-label-md text-label-md group">
                View All Posts
                <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
</button>
</div>
<div class="max-w-container-max mx-auto px-margin-mobile">
<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
<!-- Blog 1 -->
<div class="group cursor-pointer">
<div class="aspect-[16/10] rounded-2xl overflow-hidden mb-4 shadow-sm group-hover:shadow-lg transition-all">
<img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" data-alt="A flat lay photograph of various high-quality dermatological skincare products like serums and creams on a white marble surface, accented with soft pink flowers. Bright, airy, and clean visual style." src="https://lh3.googleusercontent.com/aida-public/AB6AXuCNA0UXyHwlqyOemlekDtI73CCINX29lCMWcKtVgur_JeAZoCLJ2xxwUtlNPEtW4YTS4WschS50iQ9kidIBs3uO0DTkyScWSl-s0u4ArFSrumWvn5ORFU7YkFug-5zWxd2Tx_5JR_iDVSdyMs_GvLQiL1OUJqlPV5YJc-UsDII6mTK5W36XrSFgK_CLiUwc4CvUQQXJe-YukFK9KbZRmo7_zpNP8ME9Fu4CI9FFZxUU9lZzb1udzaUoVr0OYJjSkecBPj6rz9wKB3Ya"/>
</div>
<span class="bg-primary/10 text-primary text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-wider">Skin Care Tips</span>
<h3 class="font-headline-md text-headline-md text-lg mt-2 group-hover:text-primary transition-colors">Daily Skin Care Routine for Healthy Glowing Skin</h3>
<p class="text-on-surface-variant text-sm mt-2 line-clamp-2">Learn the fundamental steps to maintain a clear and vibrant complexion every day with expert advice...</p>
<div class="flex items-center gap-4 mt-4 text-xs text-on-surface-variant font-medium">
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">calendar_month</span> May 20, 2024</span>
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span> 5 min read</span>
</div>
</div>
<!-- Blog 2 -->
<div class="group cursor-pointer">
<div class="aspect-[16/10] rounded-2xl overflow-hidden mb-4 shadow-sm group-hover:shadow-lg transition-all">
<img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" data-alt="An abstract scientific close-up of skin texture or a medical laser beam interaction with a surface, representing modern dermatological technology. Minimalist and high-end aesthetic." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAUc6WdML9CUO-CymFYdvNMZJylP_d-N5v-T0zT6UVZ4bzeaRHZQjdajz67UyU4ifLSB-R7KKG2VwB_vgdaUc1T7yhRh112guOHNEX1CliOjC0vmtxxgaYVVt2lAWk-zG6qGe1nfzqC5-fZGKGXMMc5UCceUyBY9yXhb3LgaGK9U-IC9KfVt-jPXhsh3uim3UCJY-6eUvjrKfMyW5gGBN1WJUDzfCHloPdi8pIp08b7ooV2al9wmn-vmY0Wiv1BUhfjBNXTOfmREMgf"/>
</div>
<span class="bg-primary/10 text-primary text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-wider">Treatment</span>
<h3 class="font-headline-md text-headline-md text-lg mt-2 group-hover:text-primary transition-colors">How Laser Treatment Can Transform Your Skin</h3>
<p class="text-on-surface-variant text-sm mt-2 line-clamp-2">Understand the science behind laser therapy and how it effectively targets scarring and pigmentation...</p>
<div class="flex items-center gap-4 mt-4 text-xs text-on-surface-variant font-medium">
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">calendar_month</span> May 15, 2024</span>
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span> 8 min read</span>
</div>
</div>
<!-- Blog 3 -->
<div class="group cursor-pointer">
<div class="aspect-[16/10] rounded-2xl overflow-hidden mb-4 shadow-sm group-hover:shadow-lg transition-all">
<img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" data-alt="A portrait of a serene woman drinking green tea, surrounded by healthy food and clean living elements. Focus on holistic skin health and natural beauty. Minimalist spa-like lighting." src="https://lh3.googleusercontent.com/aida-public/AB6AXuCZvTxpFCehjTjzo5HkoBLuduq0dUqhTBSu2g63nYGS44moT14Yee6wW6bIU3En49ddBTgH_MQyLO-20_zcohchslSns8qIaZaNuRX1_ifNCf-BEjKxNxPFUGAH6iiwO_U4VVtCmGFnJdfJWY4ZEmp_b-9k9wEwKlCuG0gSUh1W_6ctQItqHKnGsO4WS7W-9Ar6FJsj9LZdjV73ywkxkrQnEpbER6eGqN8I4s02XT56ianyoo8Rsy11wYnlCEgvWaJm79buOhT5unuy"/>
</div>
<span class="bg-primary/10 text-primary text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-wider">Skin Health</span>
<h3 class="font-headline-md text-headline-md text-lg mt-2 group-hover:text-primary transition-colors">Top 5 Tips to Prevent Acne Naturally</h3>
<p class="text-on-surface-variant text-sm mt-2 line-clamp-2">Discover lifestyle changes and natural remedies that can help keep your skin clear and breakout-free...</p>
<div class="flex items-center gap-4 mt-4 text-xs text-on-surface-variant font-medium">
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">calendar_month</span> May 10, 2024</span>
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span> 4 min read</span>
</div>
</div>
</div>
</div>
</section>
<!-- Footer (Shared Component Strategy) -->
<footer class="w-full bg-surface-container-highest">
<div class="flex flex-col gap-stack-md px-margin-mobile py-stack-md w-full max-w-container-max mx-auto mb-[72px] md:mb-0">
<div class="grid grid-cols-1 md:grid-cols-12 gap-10">
<!-- Branding & Newsletter -->
<div class="md:col-span-4 flex flex-col gap-4">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-3xl">face_6</span>
<span class="text-headline-md font-headline-md text-primary">Skin Clinic</span>
</div>
<p class="text-body-md text-on-surface-variant">We are dedicated to providing the best skin care treatments with advanced technology and a team of expert dermatologists.</p>
<div class="flex gap-4 mt-2">
<a class="w-10 h-10 bg-background rounded-full flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-all shadow-sm" href="#">
<span class="material-symbols-outlined text-lg">face_nod</span>
</a>
<a class="w-10 h-10 bg-background rounded-full flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-all shadow-sm" href="#">
<span class="material-symbols-outlined text-lg">camera</span>
</a>
<a class="w-10 h-10 bg-background rounded-full flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-all shadow-sm" href="#">
<span class="material-symbols-outlined text-lg">brand_awareness</span>
</a>
</div>
</div>
<!-- Quick Links -->
<div class="md:col-span-2">
<h4 class="font-bold mb-6 text-on-surface uppercase tracking-widest text-[11px]">Quick Links</h4>
<ul class="flex flex-col gap-3">
<li><a class="text-on-surface-variant font-label-md text-label-md hover:text-primary transition-colors" href="#">About Us</a></li>
<li><a class="text-on-surface-variant font-label-md text-label-md hover:text-primary transition-colors" href="#">Treatments</a></li>
<li><a class="text-on-surface-variant font-label-md text-label-md hover:text-primary transition-colors" href="#">Our Doctors</a></li>
<li><a class="text-on-surface-variant font-label-md text-label-md hover:text-primary transition-colors" href="#">Results</a></li>
<li><a class="text-on-surface-variant font-label-md text-label-md hover:text-primary transition-colors" href="#">Contact</a></li>
</ul>
</div>
<!-- Contact Info -->
<div class="md:col-span-3">
<h4 class="font-bold mb-6 text-on-surface uppercase tracking-widest text-[11px]">Contact Us</h4>
<ul class="flex flex-col gap-4">
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-xl">location_on</span>
<span class="text-on-surface-variant text-sm">123, Beauty Street, Yankin Township, Yangon, Myanmar</span>
</li>
<li class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-xl">call</span>
<span class="text-on-surface-variant text-sm">09 123 456 789</span>
</li>
<li class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-xl">mail</span>
<span class="text-on-surface-variant text-sm">info@skinclinic.com</span>
</li>
</ul>
</div>
<!-- Newsletter -->
<div class="md:col-span-3">
<h4 class="font-bold mb-6 text-on-surface uppercase tracking-widest text-[11px]">Newsletter</h4>
<p class="text-sm text-on-surface-variant mb-4">Subscribe to get updates and special offers from us.</p>
<div class="flex flex-col gap-2">
<input class="bg-background border-outline-variant/30 rounded-xl px-4 py-3 text-sm focus:border-primary focus:ring-0" placeholder="Enter your email" type="email"/>
<button class="bg-primary text-on-primary py-3 rounded-xl font-bold text-sm hover:opacity-90 active:scale-95 transition-all">Subscribe</button>
</div>
</div>
</div>
<div class="border-t border-outline-variant/30 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-on-surface-variant">
<span>© 2024 Skin Clinic. All Rights Reserved.</span>
<div class="flex gap-6">
<a class="hover:text-primary transition-colors" href="#">Privacy Policy</a>
<a class="hover:text-primary transition-colors" href="#">Terms &amp; Conditions</a>
</div>
</div>
</div>
</footer>
<!-- Bottom Navigation Bar (Shared Component Strategy - Mobile Only) -->
<nav class="md:hidden fixed bottom-0 w-full z-50 rounded-t-xl bg-surface-container-low border-t border-outline-variant/30 shadow-[0_-4px_20px_rgba(45,52,54,0.05)]">
<div class="flex justify-around items-center w-full pb-safe pt-2 px-2">
<a class="flex flex-col items-center justify-center text-primary bg-primary-fixed/30 rounded-full px-4 py-1 active:scale-90 duration-150" href="#">
<span class="material-symbols-outlined">home</span>
<span class="font-label-md text-label-md text-[10px]">Home</span>
</a>
<a class="flex flex-col items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors" href="#">
<span class="material-symbols-outlined">medical_services</span>
<span class="font-label-md text-label-md text-[10px]">Treatments</span>
</a>
<a class="flex flex-col items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors" href="#">
<span class="material-symbols-outlined">auto_fix_high</span>
<span class="font-label-md text-label-md text-[10px]">Results</span>
</a>
<a class="flex flex-col items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors" href="#">
<span class="material-symbols-outlined">call</span>
<span class="font-label-md text-label-md text-[10px]">Contact</span>
</a>
</div>
</nav>
<script>
        // Micro-interactions for Before/After Slider
        document.querySelectorAll('.before-after-handle').forEach(slider => {
            let isResizing = false;
            const container = slider.parentElement;

            const updateSlider = (e) => {
                const rect = container.getBoundingClientRect();
                const x = (e.clientX || (e.touches && e.touches[0].clientX)) - rect.left;
                const percent = Math.min(Math.max(0, (x / rect.width) * 100), 100);
                slider.style.width = `${percent}%`;
            };

            container.addEventListener('mousemove', (e) => updateSlider(e));
            container.addEventListener('touchmove', (e) => updateSlider(e));
        });

        // Simple fade-in animation on scroll
        const observerOptions = { threshold: 0.1 };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in', 'fade-in', 'slide-in-from-bottom-8', 'duration-1000');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('section').forEach(section => {
            section.classList.add('opacity-0', 'transition-opacity');
            observer.observe(section);
        });

        // Adjust visibility once animation observer is set
        setTimeout(() => {
             document.querySelectorAll('section').forEach(s => s.classList.replace('opacity-0', 'opacity-100'));
        }, 100);
    </script>
</body></html>