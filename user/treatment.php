<?php
require_once __DIR__ . '/../config/db.php';
$treatments_result = $conn->query("SELECT * FROM treatments ORDER BY treatment_name ASC LIMIT 4");
$treatments = [];
while ($row = $treatments_result->fetch_assoc()) {
    $treatments[] = $row;
}
?>
<?php include '../includes/header.php'; ?>

<!-- POPULAR TREATMENTS SECTION -->
    <section class="max-w-7xl mx-auto px-6 py-20">
        <div class="flex justify-between items-end mb-10">
            <div>
                <span class="text-xs font-semibold tracking-widest text-brand-pink uppercase block mb-2">Our Popular Treatments</span>
                <h2 class="font-serif text-3xl text-brand-dark dark:text-white font-bold">Advanced Care for Radiant Skin</h2>
            </div>
            <a href="../user/alltreatment.php" class="text-brand-pink hover:underline font-medium text-sm flex items-center gap-1">
                View All Treatments →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if (empty($treatments)): ?>
            <div class="col-span-full text-center py-10 text-sm text-gray-400 dark:text-gray-500">No treatments available at the moment.</div>
            <?php else: ?>
            <?php foreach ($treatments as $treatment): ?>
            <!-- Card Container - flex-col h-full forces equal layout sizes -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl overflow-hidden shadow-md border border-gray-100 dark:border-gray-800  ease-out  hover:shadow-xl hover:border-pink-100 group flex flex-col h-full">
                
                <!-- Card Image Box -->
                <div class="overflow-hidden aspect-video shrink-0">
                    <?php if (!empty($treatment['image'])): ?>
                    <img src="../<?php echo htmlspecialchars($treatment['image']); ?>" alt="<?php echo htmlspecialchars($treatment['treatment_name']); ?>" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-brand-lightPink dark:bg-pink-900/20 text-brand-pink text-3xl">
                        <i class="fa-solid fa-hand-holding-medical"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Content & Button Container -->
                <div class="p-5 flex flex-col flex-grow justify-between">
                    <div>
                        <h3 class="font-serif text-lg text-brand-dark dark:text-white font-bold mb-2"><?php echo htmlspecialchars($treatment['treatment_name']); ?></h3>
                        <!-- line-clamp-3 forces multi-line descriptions to match perfectly across rows -->
                        <p class="text-xs text-brand-textMuted dark:text-gray-400 leading-relaxed mb-6 line-clamp-3"><?php echo htmlspecialchars($treatment['description'] ?? ''); ?></p>
                    </div>
                    
                    <!-- Bottom Action Footer Row -->
                    <div class="flex justify-between items-end mt-auto pt-4 border-t border-gray-50 dark:border-gray-800">
                        <div>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 block font-light leading-none mb-1">Price from</span>
                            <span class="text-brand-pink font-bold text-lg">$<?php echo number_format($treatment['price'], 2); ?></span>
                        </div>
                        <a href="../user/booking.php?treatment_id=<?php echo $treatment['id']; ?>" class="bg-brand-pink hover:bg-opacity-90 text-white text-xs font-semibold px-4 py-2.5 rounded-lg transition-all shadow-xs">
                            Book Session
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>