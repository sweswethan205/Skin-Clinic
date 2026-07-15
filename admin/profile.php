<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config/db.php';

$upload_dir = __DIR__ . '/../uploads/profiles/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$message = '';
$message_type = '';

// Ensure at least one admin exists
$check_admin = $conn->query("SELECT id FROM admins LIMIT 1");
if ($check_admin->num_rows === 0) {
    $default_pw = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admins (username, email, password) VALUES ('admin', 'admin@glowskin.com', '$default_pw')");
}

$admin = $conn->query("SELECT * FROM admins ORDER BY id ASC LIMIT 1")->fetch_assoc();
$admin_id = intval($admin['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'profile') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $photo_path = $admin['photo'] ?? '';

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($_FILES['photo']['type'], $allowed)) {
                $message = 'Only JPG, PNG, GIF, and WebP images are allowed.';
                $message_type = 'error';
            } else {
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'admin_' . $admin_id . '_' . time() . '.' . $ext;
                $dest = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                    if (!empty($admin['photo']) && file_exists(__DIR__ . '/../' . $admin['photo'])) {
                        unlink(__DIR__ . '/../' . $admin['photo']);
                    }
                    $photo_path = 'uploads/profiles/' . $filename;
                }
            }
        }

        if (empty($message)) {
            $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ?, photo = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $email, $photo_path, $admin_id);
            if ($stmt->execute()) {
                $admin['username'] = $username;
                $admin['email'] = $email;
                $admin['photo'] = $photo_path;
                $message = 'Profile updated successfully.';
                $message_type = 'success';
            } else {
                $message = 'Failed to update profile.';
                $message_type = 'error';
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new_pw = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $admin['password'])) {
            $message = 'Current password is incorrect.';
            $message_type = 'error';
        } elseif (strlen($new_pw) < 6) {
            $message = 'New password must be at least 6 characters.';
            $message_type = 'error';
        } elseif ($new_pw !== $confirm) {
            $message = 'Passwords do not match.';
            $message_type = 'error';
        } else {
            $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $admin_id);
            if ($stmt->execute()) {
                $admin['password'] = $hashed;
                $message = 'Password changed successfully.';
                $message_type = 'success';
            } else {
                $message = 'Failed to update password.';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Admin Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { pink: '#FF6584', pinkHover: '#E04F6E', lightPink: '#FFF0F2', dark: '#0F172A', muted: '#64748B', canvas: '#F1F5F9' }
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>
    <script>
        (function() {
            const saved = localStorage.getItem('admin_theme');
            if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
            updateIcons();
        })();
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('admin_theme', html.classList.contains('dark') ? 'dark' : 'light');
            updateIcons();
        }
        function updateIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            const moon = document.getElementById('admin-icon-moon');
            const sun = document.getElementById('admin-icon-sun');
            if (moon) moon.style.display = isDark ? 'none' : 'inline';
            if (sun) sun.style.display = isDark ? 'inline' : 'none';
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-brand-canvas dark:bg-gray-950 text-slate-700 dark:text-gray-100 min-h-screen flex antialiased">

    <aside class="fixed top-0 left-0 w-68 h-screen bg-white dark:bg-gray-900 border-r border-slate-200/60 dark:border-gray-800 flex flex-col justify-between p-6 overflow-y-auto z-50">
        <div class="space-y-8">
            <div class="flex items-center space-x-3 px-2">
                <div class="w-10 h-10 bg-brand-lightPink rounded-xl flex items-center justify-center text-brand-pink shadow-xs">
                    <i class="fa-solid fa-spa text-xl"></i>
                </div>
                <div>
                    <h1 class="font-extrabold text-brand-dark text-base tracking-tight leading-tight">GlowSkin Clinic</h1>
                    <span class="text-[11px] text-brand-pink font-bold uppercase tracking-wider">Admin Panel</span>
                </div>
            </div>
            <nav class="space-y-1.5">
                <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white hover:bg-slate-50 dark:hover:bg-gray-800 rounded-xl font-semibold text-sm transition-all group">
                    <i class="fa-solid fa-chart-pie text-base group-hover:text-brand-pink transition-colors"></i>
                    <span>Dashboard</span>
                </a>
                <a href="appointment.php" class="flex items-center space-x-3 px-4 py-3 text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white hover:bg-slate-50 dark:hover:bg-gray-800 rounded-xl font-semibold text-sm transition-all group">
                    <i class="fa-regular fa-calendar-check text-base group-hover:text-brand-pink transition-colors"></i>
                    <span>Appointments</span>
                </a>
                <a href="patient.php" class="flex items-center space-x-3 px-4 py-3 text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white hover:bg-slate-50 dark:hover:bg-gray-800 rounded-xl font-semibold text-sm transition-all group">
                    <i class="fa-regular fa-user text-base group-hover:text-brand-pink transition-colors"></i>
                    <span>Patients</span>
                </a>
                <a href="doctor.php" class="flex items-center space-x-3 px-4 py-3 text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white hover:bg-slate-50 dark:hover:bg-gray-800 rounded-xl font-semibold text-sm transition-all group">
                    <i class="fa-solid fa-user-doctor text-base group-hover:text-brand-pink transition-colors"></i>
                    <span>Doctors</span>
                </a>
                <a href="schedule.php" class="flex items-center space-x-3 px-4 py-3 text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white hover:bg-slate-50 dark:hover:bg-gray-800 rounded-xl font-semibold text-sm transition-all group">
                    <i class="fa-regular fa-clock text-base group-hover:text-brand-pink transition-colors"></i>
                    <span>Schedules</span>
                </a>
                <a href="treatment.php" class="flex items-center space-x-3 px-4 py-3 text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white hover:bg-slate-50 dark:hover:bg-gray-800 rounded-xl font-semibold text-sm transition-all group">
                    <i class="fa-solid fa-hand-holding-medical text-base group-hover:text-brand-pink transition-colors"></i>
                    <span>Treatments</span>
                </a>
                <a href="testimonial.php" class="flex items-center space-x-3 px-4 py-3 text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white hover:bg-slate-50 dark:hover:bg-gray-800 rounded-xl font-semibold text-sm transition-all group">
                    <i class="fa-regular fa-comment-dots text-base group-hover:text-brand-pink transition-colors"></i>
                    <span>Testimonials</span>
                </a>
                <a href="message.php" class="flex items-center space-x-3 px-4 py-3 text-brand-muted dark:text-gray-400 hover:text-brand-dark dark:hover:text-white hover:bg-slate-50 dark:hover:bg-gray-800 rounded-xl font-semibold text-sm transition-all group">
                    <i class="fa-regular fa-envelope text-base group-hover:text-brand-pink transition-colors"></i>
                    <span>Messages</span>
                </a>
                <a href="profile.php" class="flex items-center space-x-3 px-4 py-3 bg-brand-lightPink dark:bg-pink-900/20 text-brand-pink rounded-xl font-bold text-sm transition-all shadow-xs">
                    <i class="fa-solid fa-user-gear text-base"></i>
                    <span>Profile</span>
                </a>
            </nav>
        </div>
        <div class="space-y-4">
            <div class="bg-gradient-to-br from-brand-lightPink to-white dark:from-pink-900/20 dark:to-gray-900 rounded-2xl p-4 text-center relative border border-pink-100/60 dark:border-pink-800/30 shadow-xs">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-white dark:bg-gray-800 w-8 h-8 rounded-full border border-pink-100 dark:border-pink-800/30 flex items-center justify-center shadow-sm text-brand-pink">
                    <i class="fa-solid fa-headset text-xs"></i>
                </div>
                <h4 class="text-xs font-bold text-brand-dark dark:text-white mt-2">Need Help?</h4>
                <a href="#" class="text-[11px] font-bold text-brand-pink hover:text-brand-pinkHover block mt-0.5 transition-colors">Contact Support</a>
            </div>
            <a href="../admin/logout.php" class="flex items-center space-x-3 px-4 py-2.5 text-brand-muted dark:text-gray-400 hover:text-red-500 font-bold text-sm transition-all rounded-xl hover:bg-red-50/50 dark:hover:bg-red-900/20">
                <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <div class="flex-grow flex flex-col min-w-0 lg:ml-64">
        <header class="h-16 sm:h-20 bg-white dark:bg-gray-900 border-b border-slate-200/60 dark:border-gray-800 flex items-center justify-between px-4 sm:px-8 shrink-0 z-10">
            <div class="flex items-center space-x-4">
                <button class="text-brand-muted dark:text-gray-400 text-lg hover:text-brand-dark dark:hover:text-white transition-colors"><i class="fa-solid fa-bars-staggered"></i></button>
                <div>
                    <h2 class="text-xl font-extrabold text-brand-dark dark:text-white tracking-tight">Admin Profile</h2>
                    <p class="text-xs text-brand-muted dark:text-gray-400 font-medium">Manage your account settings</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="toggleDarkMode()" class="text-slate-500 dark:text-gray-400 hover:text-brand-pink transition p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-gray-800" title="Toggle dark mode">
                    <i class="fa-solid fa-moon text-lg" id="admin-icon-moon"></i>
                    <i class="fa-solid fa-sun text-lg" id="admin-icon-sun" style="display:none"></i>
                </button>
                <div class="w-10 h-10 rounded-xl overflow-hidden border border-slate-200 dark:border-gray-700 bg-brand-lightPink dark:bg-pink-900/20 flex items-center justify-center text-brand-pink font-bold">
                    <?php if (!empty($admin['photo'])): ?>
                        <img src="../<?php echo htmlspecialchars($admin['photo']); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?php echo strtoupper(substr($admin['username'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="text-xs font-bold text-brand-dark dark:text-white block leading-tight"><?php echo htmlspecialchars($admin['username']); ?></span>
                </div>
            </div>
        </header>

        <main class="flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6">
            <?php if ($message): ?>
            <div class="px-5 py-3.5 rounded-xl border text-sm font-bold flex items-center gap-3 <?php echo $message_type === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800'; ?>">
                <i class="fa-solid <?php echo $message_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-current opacity-60 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-900 border border-slate-200/60 dark:border-gray-800 rounded-2xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.03)]">
                <input type="hidden" name="action" value="profile">
                <div class="flex items-center gap-6 pb-8 border-b border-slate-100 dark:border-gray-800">
                    <div class="relative">
                        <div class="w-20 h-20 rounded-full bg-brand-lightPink dark:bg-pink-900/20 flex items-center justify-center text-brand-pink text-3xl font-bold overflow-hidden border border-slate-200 dark:border-gray-700">
                            <?php if (!empty($admin['photo'])): ?>
                                <img src="../<?php echo htmlspecialchars($admin['photo']); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php echo strtoupper(substr($admin['username'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <label for="photo-upload" class="absolute -bottom-1 -right-1 w-7 h-7 bg-brand-pink text-white rounded-full flex items-center justify-center cursor-pointer shadow-md hover:bg-opacity-90 transition text-xs">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="file" id="photo-upload" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden">
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-brand-dark dark:text-white"><?php echo htmlspecialchars($admin['username']); ?></h2>
                        <p class="text-sm text-brand-muted dark:text-gray-400">Clinic Administrator</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-8">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-brand-muted dark:text-gray-400 mb-2">Username</label>
                        <input type="text" name="username" required value="<?php echo htmlspecialchars($admin['username']); ?>" class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-sm font-semibold dark:text-white outline-none focus:border-brand-pink focus:bg-white dark:focus:bg-gray-700 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-brand-muted dark:text-gray-400 mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-sm font-semibold dark:text-white outline-none focus:border-brand-pink focus:bg-white dark:focus:bg-gray-700 transition">
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 dark:border-gray-800 flex justify-end">
                    <button type="submit" class="bg-brand-pink hover:bg-brand-pinkHover text-white px-6 py-3 rounded-xl text-sm font-bold transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)] flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Save Changes
                    </button>
                </div>
            </form>

            <form method="POST" class="bg-white dark:bg-gray-900 border border-slate-200/60 dark:border-gray-800 rounded-2xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.03)] mt-8">
                <input type="hidden" name="action" value="password">
                <h2 class="text-lg font-bold text-brand-dark dark:text-white mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-lock text-brand-pink"></i> Change Password
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-brand-muted dark:text-gray-400 mb-2">Current Password</label>
                        <input type="password" name="current_password" required class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-sm dark:text-white outline-none focus:border-brand-pink focus:bg-white dark:focus:bg-gray-700 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-brand-muted dark:text-gray-400 mb-2">New Password</label>
                        <input type="password" name="new_password" required minlength="6" class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-sm dark:text-white outline-none focus:border-brand-pink focus:bg-white dark:focus:bg-gray-700 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-brand-muted dark:text-gray-400 mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" required minlength="6" class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-sm dark:text-white outline-none focus:border-brand-pink focus:bg-white dark:focus:bg-gray-700 transition">
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-gray-800 flex justify-end">
                    <button type="submit" class="bg-slate-800 dark:bg-gray-700 hover:bg-slate-900 dark:hover:bg-gray-600 text-white px-6 py-3 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                        <i class="fa-solid fa-key"></i> Update Password
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.getElementById('photo-upload')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    const container = document.querySelector('.w-20.h-20');
                    container.innerHTML = '<img src="' + ev.target.result + '" class="w-full h-full object-cover">';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
