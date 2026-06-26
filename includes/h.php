<?php
// Get the current file name to determine which tab is active on page load
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Global Premium Navigation Bar -->
<nav class="bg-white border-b border-gray-100 py-4 px-6 sticky top-0 z-50 shadow-xs">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <!-- Clinic Logo -->
        <a href="index.php" class="font-serif tracking-wider text-sm uppercase font-bold text-brand-dark">
            Glow Skin<span class="text-brand-pink">.</span>
        </a>

        <!-- Menu Navigation Tabs -->
        <div class="flex items-center space-x-8" id="nav-links-container">
            <!-- HOME LINK -->
            <a href="index.php" class="nav-link relative pb-2 text-xs font-semibold uppercase tracking-widest transition-all duration-200 
                <?php echo ($current_page == 'index.php' || $current_page == '') ? 'text-brand-pink [text-shadow:0_2px_10px_rgba(255,101,132,0.2)]' : 'text-brand-textMuted hover:text-brand-pink'; ?>">
                Home
                <span class="underline-bar absolute bottom-0 left-0 w-full h-[2px] bg-brand-pink rounded-full shadow-[0_2px_8px_rgba(255,101,132,0.4)] <?php echo ($current_page == 'index.php' || $current_page == '') ? 'block' : 'hidden'; ?>"></span>
            </a>

            <!-- TREATMENT LINK -->
            <a href="treatments.php" class="nav-link relative pb-2 text-xs font-semibold uppercase tracking-widest transition-all duration-200 
                <?php echo ($current_page == 'treatments.php') ? 'text-brand-pink [text-shadow:0_2px_10px_rgba(255,101,132,0.2)]' : 'text-brand-textMuted hover:text-brand-pink'; ?>">
                Treatments
                <span class="underline-bar absolute bottom-0 left-0 w-full h-[2px] bg-brand-pink rounded-full shadow-[0_2px_8px_rgba(255,101,132,0.4)] <?php echo ($current_page == 'treatments.php') ? 'block' : 'hidden'; ?>"></span>
            </a>

            <!-- CONTACT LINK -->
            <a href="contact.php" class="nav-link relative pb-2 text-xs font-semibold uppercase tracking-widest transition-all duration-200 
                <?php echo ($current_page == 'contact.php') ? 'text-brand-pink [text-shadow:0_2px_10px_rgba(255,101,132,0.2)]' : 'text-brand-textMuted hover:text-brand-pink'; ?>">
                Contact Us
                <span class="underline-bar absolute bottom-0 left-0 w-full h-[2px] bg-brand-pink rounded-full shadow-[0_2px_8px_rgba(255,101,132,0.4)] <?php echo ($current_page == 'contact.php') ? 'block' : 'hidden'; ?>"></span>
            </a>
        </div>
    </div>
</nav>

<!-- Instant Click Response JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            // 1. Reset all links back to muted state instantly
            navLinks.forEach(l => {
                l.className = "nav-link relative pb-2 text-xs font-semibold uppercase tracking-widest transition-all duration-200 text-brand-textMuted hover:text-brand-pink";
                const bar = l.querySelector('.underline-bar');
                if(bar) bar.classList.add('hidden');
            });

            // 2. Turn the clicked link pink and show the shadow underline immediately
            link.className = "nav-link relative pb-2 text-xs font-semibold uppercase tracking-widest transition-all duration-200 text-brand-pink [text-shadow:0_2px_10px_rgba(255,101,132,0.2)]";
            const activeBar = link.querySelector('.underline-bar');
            if(activeBar) activeBar.classList.remove('hidden');
        });
    });
});
</script>