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
                <h2 class="font-serif text-3xl text-brand-dark font-bold">Advanced Care for Radiant Skin</h2>
            </div>
            <a href="../user/alltreatment.php" class="text-brand-pink hover:underline font-medium text-sm flex items-center gap-1">
                View All Treatments →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if (empty($treatments)): ?>
            <div class="col-span-full text-center py-10 text-sm text-gray-400">No treatments available at the moment.</div>
            <?php else: ?>
            <?php foreach ($treatments as $treatment): ?>
            <div class="bg-white rounded-2xl overflow-hidden shadow-md border border-gray-100 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-xl hover:border-pink-100 group">
                <div class="overflow-hidden aspect-video">
                    <?php if (!empty($treatment['image'])): ?>
                    <img src="../<?php echo htmlspecialchars($treatment['image']); ?>" alt="<?php echo htmlspecialchars($treatment['treatment_name']); ?>" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-brand-lightPink text-brand-pink text-3xl">
                        <i class="fa-solid fa-hand-holding-medical"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="p-5">
                    <h3 class="font-serif text-lg text-brand-dark font-bold mb-2"><?php echo htmlspecialchars($treatment['treatment_name']); ?></h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-6"><?php echo htmlspecialchars($treatment['description'] ?? ''); ?></p>
                    <div class="flex justify-between items-center">
                        <span class="text-brand-pink font-bold text-lg"><?php echo number_format($treatment['price'], 2); ?>MMK</span>
                        <a href="../user/booking.php?treatment_id=<?php echo $treatment['id']; ?>" class="bg-brand-pink text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">Book Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    <?php include '../includes/footer.php'; ?>
