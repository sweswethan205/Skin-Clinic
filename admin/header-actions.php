<!-- Notification Bell -->
<a href="notification.php" class="relative text-slate-500 dark:text-gray-400 hover:text-brand-pink transition p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-gray-800">
    <i class="fa-regular fa-bell text-lg"></i>
    <?php if ($notif_count > 0): ?>
        <span class="absolute -top-1 -right-1 bg-brand-pink text-white text-[9px] font-bold h-4 min-w-[16px] px-1 flex items-center justify-center rounded-full ring-2 ring-white dark:ring-gray-900 shadow-xs">
            <?= $notif_count ?>
        </span>
    <?php endif; ?>
</a>

<!-- Dark Mode Toggle -->
<button id="admin-dark-toggle" onclick="toggleDarkMode()" class="text-slate-500 dark:text-gray-400 hover:text-brand-pink transition p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-gray-800" title="Toggle dark mode">
    <i class="fa-solid fa-moon text-lg" id="admin-icon-moon"></i>
    <i class="fa-solid fa-sun text-lg" id="admin-icon-sun" style="display:none"></i>
</button>
