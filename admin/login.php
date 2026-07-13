<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_token']) && $_SESSION['admin_token'] === 'authenticated_success_token') {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, photo FROM admins WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_token'] = 'authenticated_success_token';
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_photo'] = $admin['photo'] ?? '';
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
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
    <title>Admin Login - GlowSkin Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
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
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-brand-bgGray flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-brand-lightPink rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                <i class="fa-solid fa-spa text-3xl text-brand-pink"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">GlowSkin Clinic</h1>
            <p class="text-sm text-slate-400 font-medium mt-1">Admin Panel Login</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100/80 shadow-md p-8">
            <?php if ($error): ?>
                <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-600 rounded-xl text-xs font-medium flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-sm"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-sm">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <input type="text" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" placeholder="Enter admin username" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-brand-pink focus:bg-white transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-sm">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="adminPassword" required placeholder="Enter admin password" class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-brand-pink focus:bg-white transition-colors">
                        <button type="button" onclick="togglePassword('adminPassword', this)" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600">
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

        <p class="text-center mt-6 text-xs text-slate-400">
            <a href="../auth/login.php" class="text-brand-pink hover:underline font-semibold">&larr; Back to Patient Login</a>
        </p>
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
