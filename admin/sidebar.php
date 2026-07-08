<?php
$current_page = basename($_SERVER['PHP_SELF']);
include_once '../config/db.php';
$notif_count = 0;
$nc = $conn->query("SELECT COUNT(*) AS cnt FROM notifications WHERE is_read = 0");
if ($nc && $nc_row = $nc->fetch_assoc()) {
    $notif_count = $nc_row['cnt'];
}
$msg_unread = 0;
$mc = $conn->query("SELECT COUNT(*) AS cnt FROM messages WHERE status = 'unread'");
if ($mc && $mc_row = $mc->fetch_assoc()) {
    $msg_unread = $mc_row['cnt'];
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
    bg-white border-r border-slate-100
    flex flex-col justify-between
    p-6 overflow-y-auto
    transform -translate-x-full lg:translate-x-0
    transition-transform duration-300">

    <div class="space-y-8">

        <!-- Logo -->
        <div class="flex items-center space-x-3 text-brand-pink sticky top-0 z-50">
            <i class="fa-solid fa-spa text-3xl"></i>

            <div>
                <h1 class="font-bold text-slate-800 text-base">
                    GlowSkin
                </h1>

                <span class="text-xs text-slate-400">
                    Admin Panel
                </span>
            </div>
            <div class="flex items-center space-x-3 sm:space-x-4 px-16">
                <button onclick="toggleSidebar()" class="text-slate-600 text-lg hover:text-brand-pink"><i class="fa-solid fa-bars"></i></button>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="space-y-2">

            <a href="../admin/dashboard.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                <?= ($current_page == "dashboard.php")
                    ? "bg-brand-lightPink text-brand-pink border-l-4 border-brand-pink font-semibold"
                    : "text-slate-500 hover:bg-slate-50 hover:text-brand-pink"; ?>">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>

            <a href="../admin/appointment.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                <?= ($current_page == "appointment.php")
                    ? "bg-brand-lightPink text-brand-pink border-l-4 border-brand-pink font-semibold"
                    : "text-slate-500 hover:bg-slate-50 hover:text-brand-pink"; ?>">
                <i class="fa-regular fa-calendar-check"></i>
                <span>Appointments</span>
            </a>

            <a href="../admin/patient.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                <?= ($current_page == "patient.php")
                    ? "bg-brand-lightPink text-brand-pink border-l-4 border-brand-pink font-semibold"
                    : "text-slate-500 hover:bg-slate-50 hover:text-brand-pink"; ?>">
                <i class="fa-regular fa-address-book"></i>
                <span>Users</span>
            </a>

            <a href="../admin/doctor.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                <?= ($current_page == "doctor.php")
                    ? "bg-brand-lightPink text-brand-pink border-l-4 border-brand-pink font-semibold"
                    : "text-slate-500 hover:bg-slate-50 hover:text-brand-pink"; ?>">
                <i class="fa-solid fa-user-doctor"></i>
                <span>Doctors</span>
            </a>

            <a href="../admin/treatment.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                <?= ($current_page == "treatment.php")
                    ? "bg-brand-lightPink text-brand-pink border-l-4 border-brand-pink font-semibold"
                    : "text-slate-500 hover:bg-slate-50 hover:text-brand-pink"; ?>">
                <i class="fa-solid fa-hand-holding-medical"></i>
                <span>Treatments</span>
            </a>

            <a href="../admin/schedule.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                <?= ($current_page == "schedule.php")
                    ? "bg-brand-lightPink text-brand-pink border-l-4 border-brand-pink font-semibold"
                    : "text-slate-500 hover:bg-slate-50 hover:text-brand-pink"; ?>">
                <i class="fa-regular fa-clock"></i>
                <span>Schedules</span>
            </a>

            <a href="../admin/testimonial.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                <?= ($current_page == "testimonial.php")
                    ? "bg-brand-lightPink text-brand-pink border-l-4 border-brand-pink font-semibold"
                    : "text-slate-500 hover:bg-slate-50 hover:text-brand-pink"; ?>">
                <i class="fa-regular fa-comment-dots"></i>
                <span>Testimonials</span>
            </a>

            <a href="../admin/message.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                <?= ($current_page == "message.php")
                    ? "bg-brand-lightPink text-brand-pink border-l-4 border-brand-pink font-semibold"
                    : "text-slate-500 hover:bg-slate-50 hover:text-brand-pink"; ?>">
                <i class="fa-regular fa-envelope"></i>
                <span>Messages</span>
                <?php if ($msg_unread > 0): ?>
                    <span class="ml-auto bg-brand-pink text-white text-[10px] font-bold px-2 py-0.5 rounded-full"><?= $msg_unread ?></span>
                <?php endif; ?>
            </a>

            <a href="../admin/notification.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                <?= ($current_page == "notification.php")
                    ? "bg-brand-lightPink text-brand-pink border-l-4 border-brand-pink font-semibold"
                    : "text-slate-500 hover:bg-slate-50 hover:text-brand-pink"; ?>">
                <i class="fa-regular fa-bell"></i>
                <span>Notifications</span>
                <?php if ($notif_count > 0): ?>
                    <span class="ml-auto bg-brand-pink text-white text-[10px] font-bold px-2 py-0.5 rounded-full"><?= $notif_count ?></span>
                <?php endif; ?>
            </a>


        </nav>

    </div>

    <div class="space-y-6">

        <div class="bg-brand-lightPink rounded-2xl p-4 text-center border border-pink-100">
            <div class="w-10 h-10 rounded-full bg-white mx-auto flex items-center justify-center shadow">
                <i class="fa-solid fa-headset text-brand-pink"></i>
            </div>

            <h4 class="mt-3 font-bold text-sm">
                Need Help?
            </h4>

            <a href="#" class="text-xs text-brand-pink font-semibold">
                Contact Support
            </a>
        </div>

        <a href="../admin/logout.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl bg-brand-pink hover:bg-red-300 hover:text-red-500 ">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Logout</span>
        </a>

    </div>

</aside>