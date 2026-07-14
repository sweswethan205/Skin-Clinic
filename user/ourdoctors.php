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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght=300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    
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
<body class="bg-brand-lightPink/50 font-sans text-brand-dark antialiased min-h-screen flex flex-col justify-between dark:bg-gray-950 dark:text-gray-100">
    
    <?php include '../includes/header.php'; ?>

    <main class="max-w-5xl mx-auto w-full px-6 py-16 flex-grow space-y-12">
        
        <div class="border-b border-gray-200 pb-6 mb-10">
            <h1 class="font-serif text-3xl font-bold tracking-tight text-brand-dark dark:text-white">Clinical Profiles & Accreditations</h1>
            <p class="text-xs text-brand-textMuted mt-1 dark:text-gray-400">Verified background logs and structural core competencies of our resident medical team.</p>
        </div>

        <?php if (empty($doctors)): ?>
        <div class="text-center py-20">
            <i class="fa-regular fa-user-xmark text-4xl text-gray-300 dark:text-gray-600 mb-4 block"></i>
            <p class="text-sm text-gray-400 font-medium">No doctor profiles available at the moment.</p>
        </div>
        <?php else: ?>
        <div class="space-y-8">
            <?php foreach ($doctors as $doctor): ?>
            <section class="bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-[0_15px_40px_rgba(0,0,0,0.01)] grid grid-cols-1 md:grid-cols-12 gap-8 items-start dark:bg-gray-900 dark:border-gray-800">
                
                <div class="md:col-span-4 lg:col-span-3">
                    <div class="w-full relative aspect-square rounded-2xl overflow-hidden bg-slate-100 border border-gray-100 shadow-xs">
                        <?php if (!empty($doctor['photo'])): ?>
                        <img src="../<?php echo htmlspecialchars($doctor['photo']); ?>" alt="Dr. <?php echo htmlspecialchars($doctor['name']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-brand-lightPink text-brand-pink text-5xl font-bold">
                            <?php echo strtoupper(substr($doctor['name'], 0, 2)); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="md:col-span-8 lg:col-span-9 flex flex-col justify-between h-full min-h-[180px] space-y-6">
                    
                    <div class="space-y-4">
                        <div>
                            <h2 class="font-serif text-2xl font-bold tracking-tight text-brand-dark mt-0.5 dark:text-white">Dr. <?php echo htmlspecialchars($doctor['name']); ?></h2>
                            <?php if (!empty($doctor['email'])): ?>
                            <span class="text-[10px] text-gray-400 font-medium block mt-0.5 dark:text-gray-500"><?php echo htmlspecialchars($doctor['email']); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($doctor['description'])): ?>
                        <div class="space-y-1">
                            <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Clinical Focus & Biography</h4>
                            <p class="text-xs text-brand-textMuted leading-relaxed dark:text-gray-400">
                                <?php echo nl2br(htmlspecialchars($doctor['description'])); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center space-x-3 pt-2 max-w-md">
                        <div class="flex-1 bg-slate-50 border border-slate-100 rounded-xl p-2.5 text-center dark:bg-gray-800 dark:border-gray-700">
                            <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest block mb-0.5">Experience</span>
                            <span class="text-[11px] font-bold text-brand-dark dark:text-white"><?php echo $doctor['experience']; ?>+ Yrs Active</span>
                        </div>
                        
                        <?php if (!empty($doctor['phone'])): ?>
                        <a href="tel:<?php echo htmlspecialchars($doctor['phone']); ?>" class="flex-1 bg-brand-lightPink/50 border border-pink-100/40 rounded-xl p-2.5 text-center hover:bg-brand-lightPink transition-colors block dark:bg-pink-900/20 dark:border-pink-800/30">
                            <span class="text-[8px] font-bold text-brand-pink uppercase tracking-widest block mb-0.5">Contact</span>
                            <span class="text-[11px] font-bold text-brand-pink truncate block"><?php echo htmlspecialchars($doctor['phone']); ?></span>
                        </a>
                        <?php else: ?>
                        <div class="flex-1 bg-gray-50 border border-gray-100 rounded-xl p-2.5 text-center opacity-50 dark:bg-gray-800 dark:border-gray-700">
                            <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest block mb-0.5">Contact</span>
                            <span class="text-[11px] font-bold text-gray-400 block">N/A</span>
                        </div>
                        <?php endif; ?>
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