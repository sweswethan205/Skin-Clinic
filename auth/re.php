<?php
// register.php - Logic at the top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php'; // Uses your $conn

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture input
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // Validation
    if (empty($username) || empty($email) || empty($phone) || empty($password)) {
        $errors[] = "All fields are required.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } else {
        // Secure Hashing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare Query - Matches your schema exactly
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $phone, $hashedPassword);

        if ($stmt->execute()) {
            $success = "Account created successfully!";
            // Optional: Clear fields on success so they don't remain in inputs
            $username = $email = $phone = "";
        } else {
            $errors[] = "Registration failed: This email is already registered.";
        }
        $stmt->close();
    }
}
?>

<?php include '../includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - GlowSkin Clinic</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:wght@200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .font-serif {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>

<body class="min-h-screen bg-[#FFF5F5] p-4 md:p-10">
    <main class="flex-grow flex items-center justify-center p-4 md:p-10">

    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-pink-300/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-rose-200/30 rounded-full blur-3xl"></div>
    </div>

    <div class="relative bg-white rounded-3xl shadow-2xl overflow-hidden max-w-5xl w-full min-h-[680px] grid grid-cols-1 md:grid-cols-12 transform transition-all duration-500 hover:shadow-pink-100/50">
        
        <div class="hidden md:flex md:col-span-5 bg-[#FFF5F5] p-8 flex-col justify-between relative overflow-hidden border-r border-pink-100/30">
            <div class="relative z-10">
                <a href="#" class="flex items-center gap-2 font-serif text-2xl font-bold text-slate-800 group">
                    <span class="text-pink-500 text-xl transition-transform group-hover:rotate-12 duration-300">
                        <i class="fa-solid fa-leaf"></i>
                    </span>
                    GlowSkin
                </a>
                <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-widest font-semibold">Best Skin Clinic</p>
            </div>

            <div class="relative flex justify-center items-center my-auto py-6 z-10 w-full">
                <div class="w-[88%] aspect-[4/5] bg-cover bg-center shadow-md relative overflow-visible" 
                     style="background-image: url('https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=800&q=80'); 
                            border-radius: 42% 58% 40% 60% / 40% 43% 57% 60%;">
                    
                    <div class="absolute -left-4 bottom-8 bg-white/95 backdrop-blur-xs py-3 px-5 rounded-xl shadow-lg border border-pink-100 text-center min-w-[170px] z-20">
                        <h4 class="font-serif text-sm text-slate-800 font-semibold tracking-tight">Acne Expertise</h4>
                        <p class="text-[9px] text-slate-400 italic mb-1.5">You Can Trust</p>
                        <div class="text-pink-500 text-[9px] space-x-0.5">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative z-10 text-[11px] text-slate-400 text-center font-medium">
                Reveal Your <span class="text-pink-500 italic">Natural Glow</span>
            </div>
        </div>

        <div class="col-span-1 md:col-span-7 p-8 md:p-12 flex flex-col justify-center bg-white">
            
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="font-serif text-2xl md:text-3xl font-bold text-slate-800">Create Account</h1>
                    <p class="text-sm text-slate-400 mt-1">Free forever. Upgrade to premium routine tracking anytime.</p>
                </div>
                <span class="md:hidden font-serif text-xl font-bold text-slate-800 flex items-center gap-1">
                    <i class="fa-solid fa-leaf text-pink-500"></i>
                </span>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-xs font-medium rounded-xl flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php foreach($errors as $err) echo htmlspecialchars($err); ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-600 text-xs font-medium rounded-xl flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <form id="register-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate class="space-y-4">
                
                <div>
                    <label for="username" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <input type="text" id="username" name="username" required minlength="3" maxlength="50" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" placeholder="johndoe" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 focus:border-pink-400 focus:bg-white rounded-xl text-slate-800 placeholder-slate-400 text-sm transition-all outline-none">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" placeholder="you@example.com" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 focus:border-pink-400 focus:bg-white rounded-xl text-slate-800 placeholder-slate-400 text-sm transition-all outline-none">
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Phone Number</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-phone text-xs"></i>
                        </span>
                        <input type="tel" id="phone" name="phone" required value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" placeholder="+1 (555) 000-0000" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 focus:border-pink-400 focus:bg-white rounded-xl text-slate-800 placeholder-slate-400 text-sm transition-all outline-none">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" required minlength="6" placeholder="Min. 6 characters" class="w-full pl-11 pr-12 py-3 bg-slate-50 border border-slate-200 focus:border-pink-400 focus:bg-white rounded-xl text-slate-800 placeholder-slate-400 text-sm transition-all outline-none">

                        <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-pink-500 transition-colors">
                            <i class="fa-regular fa-eye text-base"></i>
                        </button>
                    </div>

                    <div class="mt-2 h-1 bg-slate-100 rounded-full overflow-hidden">
                        <div id="pw-strength" class="h-full w-0 rounded-full transition-all duration-300"></div>
                    </div>
                    <p id="pw-strength-label" class="text-xs text-slate-400 mt-1 min-h-[16px]"></p>
                </div>

                <div>
                    <label for="confirm" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-shield-heart"></i>
                        </span>
                        <input type="password" id="confirm" name="confirm" required placeholder="Repeat your password" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 focus:border-pink-400 focus:bg-white rounded-xl text-slate-800 placeholder-slate-400 text-sm transition-all outline-none">
                    </div>
                    <p id="pw-match-msg" class="text-xs mt-1 hidden min-h-[16px]"></p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-pink-500 hover:bg-pink-600 text-white font-semibold rounded-xl transition-all shadow-md shadow-pink-100 hover:shadow-lg flex items-center justify-center gap-2 transform active:scale-98">
                        <i class="fa-solid fa-user-plus text-xs"></i>
                        Create Free Account
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-slate-400">
                Already have an account?
                <a href="../auth/login.php" class="text-pink-500 hover:underline font-semibold">Sign in</a>
            </p>

        </div>
    </div>
    </main>

    <script>
        // Password Toggle View Script
        document.getElementById('toggle-password').addEventListener('click', () => {
            const password = document.getElementById('password');
            const icon = document.querySelector('#toggle-password i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.className = 'fa-solid fa-eye-slash text-base';
            } else {
                password.type = 'password';
                icon.className = 'fa-regular fa-eye text-base';
            }
        });

        // Password Real-time Strength Metrics Script
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('pw-strength');
        const strengthLabel = document.getElementById('pw-strength-label');

        passwordInput.addEventListener('input', () => {
            const length = passwordInput.value.length;
            let width = '0%';
            let color = '';
            let text = '';

            if(length >= 10){
                width = '100%';
                color = 'bg-emerald-500';
                text = 'Strong password structure';
            } else if(length >= 8){
                width = '75%';
                color = 'bg-amber-400';
                text = 'Good password length';
            } else if(length >= 6){
                width = '50%';
                color = 'bg-orange-400';
                text = 'Fair system strength';
            } else if(length > 0){
                width = '25%';
                color = 'bg-rose-500';
                text = 'Weak length indicator';
            }

            strengthBar.className = `h-full rounded-full transition-all duration-300 ${color}`;
            strengthBar.style.width = width;
            strengthLabel.textContent = text;
        });

        // Twin Field Sync Verification Script
        const confirmInput = document.getElementById('confirm');
        const matchMsg = document.getElementById('pw-match-msg');

        confirmInput.addEventListener('input', () => {
            if(confirmInput.value === ''){
                matchMsg.classList.add('hidden');
                return;
            }

            const match = passwordInput.value === confirmInput.value;
            matchMsg.classList.remove('hidden');
            matchMsg.textContent = match ? '✓ Passwords match' : '✗ Passwords do not match';
            matchMsg.className = `text-xs mt-1 ${match ? 'text-emerald-500' : 'text-rose-500'}`;
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>