<?php
// 1. Start session to track error states or user logins
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Connect to your database configuration (reusing your db connection path)
require_once __DIR__ . '/../config/db.php'; // Makes $conn available

$error_message = '';

// Store booking params in session if present
if (isset($_GET['schedule_id'])) {
    $_SESSION['booking_schedule_id'] = intval($_GET['schedule_id']);
}
if (isset($_GET['treatment_id'])) {
    $_SESSION['booking_treatment_id'] = intval($_GET['treatment_id']);
}

// 3. Process form when user clicks Sign In
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($identity) || empty($password)) {
        $error_message = 'Please fill out all fields.';
    } else {
        // Query database to find user by name OR email matching registration
        $stmt = $conn->prepare("SELECT id, name, password, photo FROM users WHERE name = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $identity, $identity);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify the entered password against the hashed database password
            if (password_verify($password, $user['password'])) {
                
                // Success: Set session data
                $_SESSION['user_token'] = 'authenticated_success_token';
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_photo'] = $user['photo'] ?? '';
                
                $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '../user/index1.php';
                header('Location: ' . $redirect);
                exit;
            } else {
                $error_message = 'Username and password do not match.';
            }
        } else {
            $error_message = 'Username and password do not match.';
        }
        $stmt->close();
    }
}

require '../includes/header.php';
?>

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
                <h1 class="font-serif text-2xl md:text-3xl font-bold text-slate-800">Welcome Back</h1>
                
                <?php if (!empty($error_message)): ?>
                    <div class="mt-4 p-3.5 bg-red-50 border border-red-200 text-red-600 rounded-xl text-xs font-medium flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-exclamation text-sm"></i>
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>
                
                <form id="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="space-y-5 mt-6">
                    <?php if (isset($_GET['redirect'])): ?>
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
                    <?php endif; ?>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1.5">Email or Username</label>
                        <input type="text" name="identity" required value="<?php echo isset($_POST['identity']) ? htmlspecialchars($_POST['identity']) : ''; ?>" placeholder="you@example.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-pink-400 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1.5">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Enter your password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-pink-400 transition-colors">
                    </div>
                    
                    <button type="submit" class="w-full py-3.5 bg-pink-500 hover:bg-pink-600 text-white font-semibold rounded-xl shadow-md shadow-pink-500/10 active:scale-[0.99] transition-all duration-150">
                        Sign In
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-slate-400">
                    Don't have an account? <a href="../auth/re.php" class="text-pink-500 hover:underline font-semibold">Create account</a>
                </p>
            </div>
        </div>
    </main>

    <?php require '../includes/footer.php'; ?>

</body>
</html>