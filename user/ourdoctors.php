<?php
require_once __DIR__ . '/../config/db.php';

// Fetch only active doctors
$doctors_result = $conn->query("SELECT * FROM doctors WHERE status='active' ORDER BY name ASC");
$doctors = [];
while ($row = $doctors_result->fetch_assoc()) {
    $doctors[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Specialists Registry - GlowSkin Skin Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            pink: '#FF6584',
                            pinkHover: '#F45173',
                            lightPink: '#FFF0F2',
                            dark: '#1E1B18',
                            textMuted: '#64748B'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gradient-to-br from-rose-50/40 via-white to-pink-50/30 font-sans text-brand-dark antialiased min-h-screen flex flex-col justify-between dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 dark:text-gray-100">

    <?php include '../includes/header.php'; ?>

    <main class="max-w-5xl mx-auto w-full px-6 py-12 flex-grow space-y-10">

        <!-- Modern Header Section -->
        <div class="relative overflow-hidden bg-white/80 backdrop-blur-md border border-rose-100/60 dark:bg-gray-900/80 dark:border-gray-800 rounded-3xl p-8 shadow-sm">
            <div class="absolute -top-24 -right-24 w-60 h-60 bg-brand-pink/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-lightPink text-brand-pink border border-rose-200/50 mb-3 dark:bg-pink-950/40 dark:border-pink-900/40">
                        <i class="fa-solid fa-user-doctor text-[11px]"></i> Resident Medical Staff
                    </span>
                    <h1 class="font-serif text-3xl md:text-4xl font-bold tracking-tight text-brand-dark dark:text-white">Clinical Profiles & Accreditations</h1>
                    <p class="text-xs md:text-sm text-brand-textMuted mt-1.5 dark:text-gray-400">Verified background logs and structural core competencies of our resident medical team.</p>
                </div>
            </div>
        </div>

        <?php if (empty($doctors)): ?>
            <div class="text-center py-24 bg-white/60 dark:bg-gray-900/40 rounded-3xl border border-dashed border-gray-200 dark:border-gray-800">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 dark:text-gray-500">
                    <i class="fa-regular fa-user-xmark text-2xl"></i>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No doctor profiles available at the moment.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($doctors as $doctor): ?>
                    <section class="group bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800/80 rounded-3xl p-6 md:p-8 shadow-sm hover:shadow-xl hover:shadow-pink-500/5 transition-all duration-300">

                        <div class="flex flex-col md:flex-row gap-8 items-start">

                            <!-- Doctor Image Side -->
                            <div class="w-full md:w-56 shrink-0">
                                <div class="w-full aspect-[4/5] rounded-2xl overflow-hidden bg-slate-100 dark:bg-gray-800 border border-gray-100 dark:border-gray-800 shadow-inner relative group-hover:scale-[1.02] transition-transform duration-300">
                                    <?php if (!empty($doctor['photo'])): ?>
                                        <img src="../<?php echo htmlspecialchars($doctor['photo']); ?>" alt="Dr. <?php echo htmlspecialchars($doctor['name']); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center bg-brand-lightPink text-brand-pink text-5xl font-bold dark:bg-pink-950/30">
                                            <?php echo strtoupper(substr($doctor['name'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Doctor Info Side (Auto-expanding content) -->
                            <div class="flex-1 w-full flex flex-col justify-between space-y-6">

                                <div class="space-y-4">
                                    <!-- Name & Email Header -->
                                    <div class="border-b border-slate-100 dark:border-gray-800/80 pb-4">
                                        <h2 class="font-serif text-2xl md:text-3xl font-bold tracking-tight text-brand-dark dark:text-white">
                                            Dr. <?php echo htmlspecialchars($doctor['name']); ?>
                                        </h2>
                                        <?php if (!empty($doctor['email'])): ?>
                                            <span class="text-xs text-brand-pink font-medium flex items-center gap-1.5 mt-1">
                                                <i class="fa-regular fa-envelope text-[11px]"></i>
                                                <?php echo htmlspecialchars($doctor['email']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Full Description (Unconstrained Height Fix) -->
                                    <?php if (!empty($doctor['description'])): ?>
                                        <div class="space-y-2">
                                            <h4 class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 dark:text-gray-500 flex items-center gap-1.5">
                                                <i class="fa-solid fa-stethoscope text-brand-pink"></i> Clinical Focus & Biography
                                            </h4>
                                            <p class="text-xs md:text-sm text-slate-600 dark:text-gray-300 leading-relaxed break-words whitespace-normal font-normal">
                                                <?php echo nl2br(htmlspecialchars($doctor['description'])); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Info Badges Bar -->
                                <div class="flex flex-wrap items-center gap-3 pt-2">
                                    <div class="flex-1 min-w-[130px] bg-slate-50 border border-slate-100 rounded-2xl p-3 dark:bg-gray-800/60 dark:border-gray-700/60">
                                        <span class="text-[9px] font-bold text-slate-400 dark:text-gray-400 uppercase tracking-wider block">Experience</span>
                                        <span class="text-xs font-bold text-brand-dark dark:text-white mt-0.5 block">
                                            <i class="fa-solid fa-award text-amber-500 mr-1"></i><?php echo $doctor['experience']; ?>+ Yrs Active
                                        </span>
                                    </div>

                                    <?php if (!empty($doctor['phone'])): ?>
                                        <a href="tel:<?php echo htmlspecialchars($doctor['phone']); ?>" class="flex-1 min-w-[150px] bg-brand-lightPink/70 border border-rose-200/60 rounded-2xl p-3 hover:bg-brand-pink hover:text-white group/btn transition-all duration-200 block dark:bg-pink-950/30 dark:border-pink-800/30">
                                            <span class="text-[9px] font-bold text-brand-pink group-hover/btn:text-white/80 uppercase tracking-wider block transition-colors">Contact</span>
                                            <span class="text-xs font-bold text-brand-pink group-hover/btn:text-white truncate block transition-colors mt-0.5">
                                                <i class="fa-solid fa-phone mr-1 text-[10px]"></i><?php echo htmlspecialchars($doctor['phone']); ?>
                                            </span>
                                        </a>
                                    <?php else: ?>
                                        <div class="flex-1 min-w-[130px] bg-gray-50 border border-gray-100 rounded-2xl p-3 opacity-50 dark:bg-gray-800/40 dark:border-gray-700/40">
                                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Contact</span>
                                            <span class="text-xs font-bold text-gray-400 block mt-0.5">N/A</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <?php include '../includes/footer.php'; ?>

</body>

</html>