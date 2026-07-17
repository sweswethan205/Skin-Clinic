<?php
$current_page = basename($_SERVER['PHP_SELF']);
include_once '../config/db.php';

$doctor_id = $_SESSION['doctor_id'] ?? 0;
$notif_count = 0;
if ($doctor_id > 0) {
    $nc = $conn->query("SELECT COUNT(*) AS cnt FROM notifications WHERE is_read = 0 AND user_id = $doctor_id");
    if ($nc && $nc_row = $nc->fetch_assoc()) {
        $notif_count = $nc_row['cnt'];
    }
}
?>

<!-- Overlay -->
<div id="sidebarOverlay"
    class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"
    onclick="toggleSidebar()">
</div>

<!-- Sidebar -->
<aside id="sidebar"
    class="fixed top-0 left-0 z-50
w-64 h-screen
bg-white dark:bg-gray-900
border-r border-slate-100 dark:border-gray-800
flex flex-col justify-between
overflow-y-auto
px-6
transform -translate-x-full lg:translate-x-0
transition-transform duration-300">

    <!-- Logo -->
    <div class="sticky top-0 z-50 bg-white dark:bg-gray-900 py-6 border-b border-slate-100 dark:border-gray-800">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-spa text-3xl text-brand-pink"></i>
                <div>
                    <h1 class="font-bold text-lg text-slate-800 dark:text-white">
                        GlowSkin
                    </h1>
                    <p class="text-xs text-slate-400 dark:text-gray-500">
                        Doctor Panel
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="space-y-2 mt-4">

        <a href="../doctor/dashboard.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                <?= ($current_page == "dashboard.php")
                    ? "bg-brand-lightPink dark:bg-pink-900/20 text-brand-pink border-l-4 border-brand-pink font-semibold"
                    : "text-slate-500 dark:text-gray-400 hover:bg-slate-50 dark:hover:bg-gray-800 hover:text-brand-pink"; ?>">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Dashboard</span>
        </a>

    </nav>

    </div>

    <div class="space-y-8 sticky bottom-0">

        <div class="bg-brand-lightPink dark:bg-pink-900/20 rounded-2xl p-4 text-center border border-pink-100 dark:border-pink-800/30">
            <div class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 mx-auto flex items-center justify-center shadow">
                <i class="fa-solid fa-headset text-brand-pink"></i>
            </div>
            <h4 class="mt-3 font-bold text-sm dark:text-white">
                Need Help?
            </h4>
            <a href="#" class="text-xs text-brand-pink font-semibold">
                Contact Support
            </a>
        </div>

        <a href="../doctor/logout.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl bg-brand-pink hover:bg-red-300 hover:text-red-500 ">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Logout</span>
        </a>

    </div>

</aside>
