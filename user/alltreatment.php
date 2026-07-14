<?php
require_once __DIR__ . '/../config/db.php';
$treatments_result = $conn->query("SELECT * FROM treatments ORDER BY treatment_name ASC");
$treatments = [];
while ($row = $treatments_result->fetch_assoc()) {
    $treatments[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Treatments - GlowSkin Skin Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
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
<body class="bg-[#FAF9F6] font-sans text-brand-dark antialiased dark:bg-gray-950 dark:text-gray-100">

<?php include '../includes/header.php' ?>

    <section class="bg-brand-lightPink/50 border-b border-pink-100/30 dark:bg-gray-900 dark:border-gray-800 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 pt-16 pb-20 text-center relative z-10">
            <span class="text-xs font-semibold uppercase tracking-widest text-brand-pink mb-3 block">Clinical Solutions</span>
            <h1 class="font-serif text-4xl md:text-5xl text-brand-dark dark:text-white font-bold leading-tight mb-4">
                Our Professional <span class="text-brand-pink italic font-normal">Treatments</span>
            </h1>
            <p class="text-brand-textMuted dark:text-gray-400 max-w-xl mx-auto text-sm leading-relaxed">
                Explore our selection of state-of-the-art dermatological procedures customized completely to target your unique skin requirements.
            </p>
        </div>
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-lightPink rounded-full filter blur-3xl opacity-40"></div>
    </section>

    <!-- MAIN CARDS PLATFORM CONTAINER -->
    <section class="max-w-7xl mx-auto px-6 py-16">
        <?php if (empty($treatments)): ?>
        <div class="text-center py-20">
            <i class="fa-regular fa-hand-back-fist text-4xl text-gray-300 dark:text-gray-600 mb-4 block"></i>
            <p class="text-sm text-gray-400 dark:text-gray-600 font-medium">No treatments available at the moment.</p>
        </div>
        <?php else: ?>
        <!-- Adjusted layout columns grid to support 4 rows cleanly (sm:2, md:3, lg:4) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($treatments as $treatment): ?>
            <!-- Slimmed Card Container layout -->
            <div class="treatment-card bg-white dark:bg-gray-900 rounded-xl overflow-hidden shadow-xs border border-slate-100 dark:border-gray-800 transition-all duration-300 ease-out hover:shadow-lg hover:border-pink-100 group">
                
                <!-- Smaller Compact Image Section Height container framework -->
                <div class="overflow-hidden h-44 relative bg-slate-50 dark:bg-gray-800">
                    <?php if (!empty($treatment['image'])): ?>
                    <img src="../<?php echo htmlspecialchars($treatment['image']); ?>" alt="<?php echo htmlspecialchars($treatment['treatment_name']); ?>" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-brand-lightPink text-brand-pink text-3xl">
                        <i class="fa-solid fa-hand-holding-medical"></i>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Balanced Smaller Text Padding Body content box -->
                <div class="p-4 flex flex-col justify-between min-h-[180px]">
                    <div>
                        <h3 class="font-serif text-base text-brand-dark dark:text-white font-bold mb-1.5 line-clamp-1">
                            <?php echo htmlspecialchars($treatment['treatment_name']); ?>
                        </h3>
                        <!-- Added line-clamp to prevent messy mismatched card heights -->
                        <p class="text-[11px] text-brand-textMuted dark:text-gray-400 leading-normal mb-4 line-clamp-3">
                            <?php echo htmlspecialchars($treatment['description'] ?? ''); ?></p>
                    </div>

                    <div class="flex justify-between items-center pt-3 border-t border-slate-50 mt-auto">
                        <div>
                            <span class="text-[10px] text-gray-400 block font-light leading-none mb-0.5">Price from</span>
                            <span class="text-brand-pink font-bold text-base">$<?php echo number_format($treatment['price'], 2); ?></span>
                        </div>
                        <a href="../user/booking.php?treatment_id=<?php echo $treatment['id']; ?>" class="bg-brand-pink text-white text-[11px] font-semibold px-3.5 py-2 rounded-lg transition-all shadow-xs hover:bg-opacity-95">
                            Book Session
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

    <section class="max-w-7xl mx-auto px-6 pb-24">
        <div class="bg-brand-dark dark:bg-gray-800 text-white rounded-3xl p-8 md:p-12 grid md:grid-cols-12 gap-8 items-center justify-between shadow-xl">
            <div class="md:col-span-8 space-y-3">
                <h3 class="font-serif text-2xl md:text-3xl font-semibold dark:text-white">Not sure which treatment suits your skin type?</h3>
                <p class="text-xs text-gray-300 dark:text-gray-400 max-w-lg leading-relaxed font-light">
                    Schedule a private skin assessment consultation. Our clinic experts will analyze your pores completely.
                </p>
            </div>
            <div class="md:col-span-4 md:text-right">
                <a href="index.php#contact" class="inline-block bg-brand-pink text-white text-xs font-semibold tracking-wider uppercase px-6 py-4 rounded-xl shadow-lg shadow-pink-500/10 hover:bg-opacity-90 transition-all">
                    Book Free Consultation
                </a>
            </div>
        </div>
    </section>

<?php include '../includes/footer.php' ?>

</body>
</html>