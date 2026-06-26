<?php
// public/login.php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';

// Redirect logged-in users away from the login page
requireGuest();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Using your naming convention (identity = email/username)
    $identity = $_POST['identity'] ?? '';
    $password = $_POST['password'] ?? '';

    // Attempt login using your auth.php logic
    $result = loginUser($identity, $password);

    if ($result['success']) {
        flashMessage('success', 'Welcome back, ' . htmlspecialchars($_SESSION['user_name'] ?? 'User') . '!');
        // Redirect based on role
        header('Location: ' . ($result['role'] === 'admin' ? '/dailynew/admin/index.php' : '/dailynew/public/index.php'));
        exit;
    } else {
        $errors[] = $result['error'];
    }
}
?>
<?php require '../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - GlowSkin Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:wght@200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>

<body class="min-h-screen bg-[#FFF5F5] flex flex-col">

    <main class="flex-grow flex items-center justify-center p-4 md:p-10">
        <div class="relative bg-white rounded-3xl shadow-2xl overflow-hidden max-w-5xl w-full min-h-[600px] grid grid-cols-1 md:grid-cols-12">
            
            <div class="hidden md:flex md:col-span-5 bg-[#FFF5F5] p-8 flex-col justify-between relative border-r border-pink-100/30">
                <div class="relative z-10">
                    <a href="#" class="flex items-center gap-2 font-serif text-2xl font-bold text-slate-800">
                        <span class="text-pink-500"><i class="fa-solid fa-leaf"></i></span>
                        GlowSkin
                    </a>
                </div>
                <div class="relative flex justify-center items-center my-auto w-full">
                    <div class="w-[88%] aspect-[4/5] bg-cover bg-center shadow-md" 
                         style="background-image: url('https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=800&q=80'); border-radius: 60% 40% 60% 40% / 43% 50% 50% 57%;">
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 text-center font-medium">Your Personalized Skin Journey</div>
            </div>

            <div class="col-span-1 md:col-span-7 p-8 md:p-12 flex flex-col justify-center bg-white">
                <h1 class="font-serif text-2xl md:text-3xl font-bold text-slate-800 mb-8">Welcome Back</h1>
                
                <?php if (!empty($errors)): ?>
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form id="login-form" method="POST" action="" novalidate class="space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1.5">Email or Username</label>
                        <input type="text" name="identity" required placeholder="you@example.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-pink-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1.5">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Enter your password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-pink-400">
                    </div>
                    <button type="submit" class="w-full py-3 bg-pink-500 hover:bg-pink-600 text-white font-semibold rounded-xl transition-all">
                        Sign In
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-slate-400">
                    Don't have an account? <a href="register.php" class="text-pink-500 hover:underline font-semibold">Create account</a>
                </p>
            </div>
        </div>
    </main>

    <?php require '../includes/footer.php'; ?>
</body>
</html>