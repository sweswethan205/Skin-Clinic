<!-- Client Reviews Section -->
<section class="max-w-4xl mx-auto w-full px-6 pb-12">
    <div class="text-center mb-8">
        <span class="text-xs font-semibold uppercase tracking-wider text-brand-pink block mb-1">What Our Clients Say</span>
        <h2 class="font-serif text-3xl text-brand-dark">Client Reviews</h2>
    </div>
    <div class="overflow-hidden">
        <div class="flex gap-6 animate-marquee">
            <?php if (!empty($reviews)): ?>
                <?php for ($i = 0; $i < 2; $i++): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="flex-shrink-0 w-72 bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-brand-pink/20 flex items-center justify-center text-brand-pink text-xs font-bold">
                                    <?= strtoupper(substr($review['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-sm text-brand-dark"><?= htmlspecialchars($review['name']) ?></h4>
                                    <div class="text-yellow-400 text-[10px]">
                                        <?php for ($s = 0; $s < intval($review['rating']); $s++): ?>
                                            <i class="fa-solid fa-star"></i>
                                        <?php endfor; ?>
                                        <?php for ($s = intval($review['rating']); $s < 5; $s++): ?>
                                            <i class="fa-regular fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-brand-textMuted italic leading-relaxed">"<?= htmlspecialchars($review['review_text']) ?>"</p>
                        </div>
                    <?php endforeach; ?>
                <?php endfor; ?>
            <?php else: ?>
                <div class="w-full text-center text-gray-400 text-sm py-8">No reviews yet.</div>
            <?php endif; ?>
        </div>
    </div>
</section>
