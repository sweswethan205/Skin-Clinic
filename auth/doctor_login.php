<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['doctor_token']) && $_SESSION['doctor_token'] === 'authenticated_success_token') {
    header('Location: ../doctor/dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, photo FROM doctors WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $doctor = $result->fetch_assoc();
            if (password_verify($password, $doctor['password'])) {
                $_SESSION['doctor_token'] = 'authenticated_success_token';
                $_SESSION['doctor_id'] = $doctor['id'];
                $_SESSION['doctor_name'] = $doctor['name'];
                $_SESSION['doctor_email'] = $doctor['email'];
                $_SESSION['doctor_photo'] = $doctor['photo'] ?? '';
                header('Location: ../doctor/dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Login - GlowSkin Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            pink: '#FF6584',
                            lightPink: '#FFF0F2',
                            dark: '#2D3748',
                            bgGray: '#F8FAFC'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
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
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-brand-bgGray dark:bg-gray-950 flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-[28px] border border-slate-100 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-brand-lightPink dark:bg-pink-900/20 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                <i class="fa-solid fa-user-doctor text-3xl text-brand-pink"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">GlowSkin Clinic</h1>
            <p class="text-sm text-slate-400 dark:text-gray-500 font-medium mt-1">Doctor Panel Login</p>
        </div>

        <div class="space-y-5">
            <?php if ($error): ?>
                <div class="mb-5 p-3.5 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 rounded-xl text-xs font-medium flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-sm"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 dark:text-gray-500 text-sm">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" placeholder="doctor@example.com" class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-sm dark:text-white dark:placeholder:text-gray-500 outline-none focus:border-brand-pink focus:bg-white dark:focus:bg-gray-700 transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 dark:text-gray-500 text-sm">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="doctorPassword" required placeholder="Enter your password" class="w-full pl-10 pr-10 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-sm dark:text-white dark:placeholder:text-gray-500 outline-none focus:border-brand-pink focus:bg-white dark:focus:bg-gray-700 transition-colors">
                        <button type="button" onclick="togglePassword('doctorPassword', this)" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 dark:text-gray-500 hover:text-slate-600 dark:hover:text-gray-300">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-brand-pink hover:bg-pink-600 text-white font-semibold rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    Sign In
                </button>
            </form>
        </div>

        <div class="flex items-center justify-center mt-8 gap-3 border-t border-slate-50 dark:border-gray-800 pt-5">
            <p class="text-xs text-slate-400">
                <a href="../auth/login.php" class="text-brand-pink hover:underline font-semibold">&larr; Back to Patient Login</a>
            </p>
            <span class="text-slate-300 dark:text-gray-600">|</span>
            <button onclick="toggleDarkMode()" class="text-slate-400 dark:text-gray-500 hover:text-brand-pink transition p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-gray-800" title="Toggle dark mode">
                <i class="fa-solid fa-moon text-sm" id="admin-icon-moon"></i>
                <i class="fa-solid fa-sun text-sm" id="admin-icon-sun" style="display:none"></i>
            </button>
        </div>
    </div>

    <script>
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>
