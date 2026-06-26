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

    <header class="w-full px-10 py-4 flex items-center justify-between 
sticky top-0 z-50 bg-white shadow-sm border-b">
        <div class="flex items-center space-x-2 text-brand-pink">
            <i class="fa-solid fa-spa text-2xl"></i>
            <span class="font-serif font-bold text-xl tracking-wide text-brand-dark">GlowSkin <span class="block text-xs font-sans font-semibold tracking-widest text-brand-pink -mt-1">SKIN CLINIC</span></span>
        </div>
        <nav class="hidden md:flex space-x-8 text-sm font-medium text-brand-dark">
            <a href="../user/index1.php" class="text-brand-pink">Home</a>
            <a href="../user/treatment.php" class="hover:text-brand-pink transition">Treatments</a>
            <a href="../user/about.php" class="hover:text-brand-pink transition">About Us</a>
            <a href="../user/doctors.php" class="hover:text-brand-pink transition">Our Doctors</a>
            <a href="../user/gallery.php" class="hover:text-brand-pink transition">Gallery</a>
            <a href="../user/contact.php" class="hover:text-brand-pink transition">Blog</a>
            <a href="../user/contact.php" class="hover:text-brand-pink transition">Contact</a>
        </nav>
        <a href="../user/treatment.php">
        <button class="bg-brand-pink text-white px-5 py-2.5 rounded-md text-sm font-medium hover:bg-opacity-90 transition">Book Appointment</button></a>
    </header>