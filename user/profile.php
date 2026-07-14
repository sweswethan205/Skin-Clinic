<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_token']) || $_SESSION['user_token'] !== 'authenticated_success_token') {
    header('Location: ../auth/re.php');
    exit;
}
include_once '../config/db.php';

$user_id = intval($_SESSION['user_id']);
$user = [];
$message = '';
$error = '';

$stmt = $conn->prepare("SELECT name, email, phone, photo, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $user = $row;
}
$stmt->close();

$upload_dir = '../uploads/profiles/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $pw_stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $pw_stmt->bind_param("i", $user_id);
        $pw_stmt->execute();
        $pw_result = $pw_stmt->get_result();
        $pw_row = $pw_result->fetch_assoc();
        $pw_stmt->close();

        if (!password_verify($current_password, $pw_row['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $up = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $up->bind_param("si", $hashed, $user_id);
            if ($up->execute()) {
                $message = 'Password changed successfully.';
            } else {
                $error = 'Failed to update password.';
            }
            $up->close();
        }
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $photo_path = $user['photo'] ?? '';

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['photo']['type'];
            if (!in_array($file_type, $allowed)) {
                $error = 'Only JPG, PNG, GIF, and WebP images are allowed.';
            } else {
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
                $dest = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                    $photo_path = 'uploads/profiles/' . $filename;
                    if (!empty($user['photo']) && file_exists('../' . $user['photo'])) {
                        unlink('../' . $user['photo']);
                    }
                } else {
                    $error = 'Failed to upload image.';
                }
            }
        }

        if (empty($error)) {
            if (empty($name) || empty($email)) {
                $error = 'Name and email are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {
                $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
                $check->bind_param("si", $email, $user_id);
                $check->execute();
                if ($check->get_result()->num_rows > 0) {
                    $error = 'This email is already taken by another account.';
                } else {
                    $update = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, photo = ? WHERE id = ?");
                    $update->bind_param("ssssi", $name, $email, $phone, $photo_path, $user_id);
                    if ($update->execute()) {
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_photo'] = $photo_path;
                        $user['name'] = $name;
                        $user['email'] = $email;
                        $user['phone'] = $phone;
                        $user['photo'] = $photo_path;
                        $message = 'Profile updated successfully.';
                    } else {
                        $error = 'Failed to update profile. Please try again.';
                    }
                    $update->close();
                }
                $check->close();
            }
        }
    }
}

// $appointment_count = 0;
// $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE user_id = ?");
// $count_stmt->bind_param("i", $user_id);
// $count_stmt->execute();
// $count_result = $count_stmt->get_result();
// if ($count_row = $count_result->fetch_assoc()) {
//     $appointment_count = $count_row['total'];
//     <?php echo $appointment_count; 
// }
// $count_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - GlowSkin Skin Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { pink: '#FF6584', lightPink: '#FFF0F2', dark: '#2D2D2D', textMuted: '#666666' }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'], serif: ['Playfair Display', 'serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-lightPink/50 dark:bg-gray-950 font-sans text-brand-dark dark:text-white antialiased min-h-screen flex flex-col dark:text-gray-100">
    <?php include '../includes/header.php'; ?>

    <main class="flex-grow max-w-4xl mx-auto w-full px-6 py-12">
        <div class="mb-10">
            <h1 class="font-serif text-3xl md:text-4xl font-bold tracking-tight">My Profile</h1>
            <p class="text-sm text-brand-textMuted dark:text-gray-400 mt-1">Manage your personal information</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-medium rounded-xl flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 text-sm font-medium rounded-xl flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-8 shadow-[0_10px_30px_rgba(0,0,0,0.02)]">
            <div class="flex items-center gap-6 pb-8 border-b border-gray-100 dark:border-gray-800">
                <div class="relative">
                    <div class="w-20 h-20 rounded-full bg-brand-pink flex items-center justify-center text-white text-3xl font-bold overflow-hidden">
                        <?php if (!empty($user['photo'])): ?>
                            <img src="../<?php echo htmlspecialchars($user['photo']); ?>" alt="Avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <label for="photo-upload" class="absolute -bottom-1 -right-1 w-7 h-7 bg-brand-pink text-white rounded-full flex items-center justify-center cursor-pointer shadow-md hover:bg-opacity-90 transition text-xs">
                        <i class="fa-solid fa-camera"></i>
                    </label>
                    <input type="file" id="photo-upload" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden">
                </div>
                <div>
                    <h2 class="text-xl font-bold text-brand-dark dark:text-white"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h2>
                    <p class="text-sm text-brand-textMuted dark:text-gray-400">Member since <?php echo isset($user['created_at']) ? date('M Y', strtotime($user['created_at'])) : 'N/A'; ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-8">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-brand-textMuted dark:text-gray-400 mb-2">Full Name</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm dark:text-gray-200 font-medium outline-none focus:border-brand-pink focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-brand-textMuted dark:text-gray-400 mb-2">Email</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm dark:text-gray-200 font-medium outline-none focus:border-brand-pink focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-brand-textMuted dark:text-gray-400 mb-2">Phone</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm dark:text-gray-200 font-medium outline-none focus:border-brand-pink focus:bg-white transition">
                </div>
                <!-- <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-brand-textMuted dark:text-gray-400 mb-2">Total Appointments</label>
                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <i class="fa-regular fa-calendar text-brand-pink"></i>
                        <span class="text-sm font-medium"> bookings</span>
                    </div>
                </div> -->
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                <button type="submit" class="bg-brand-pink text-white px-6 py-3 rounded-xl text-sm font-semibold hover:bg-opacity-90 transition flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
            </div>
        </form>

        <form method="POST" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-8 shadow-[0_10px_30px_rgba(0,0,0,0.02)] mt-8">
            <input type="hidden" name="action" value="password">
            <h2 class="text-lg font-bold text-brand-dark dark:text-white mb-6 flex items-center gap-2">
                <i class="fa-solid fa-lock text-brand-pink"></i> Change Password
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-brand-textMuted dark:text-gray-400 mb-2">Current Password</label>
                    <input type="password" name="current_password" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm dark:text-gray-200 outline-none focus:border-brand-pink focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-brand-textMuted dark:text-gray-400 mb-2">New Password</label>
                    <input type="password" name="new_password" required minlength="6" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm dark:text-gray-200 outline-none focus:border-brand-pink focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-brand-textMuted dark:text-gray-400 mb-2">Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="6" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm dark:text-gray-200 outline-none focus:border-brand-pink focus:bg-white transition">
                </div>
            </div>
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                <button type="submit" class="bg-gray-800 text-white px-6 py-3 rounded-xl text-sm font-semibold hover:bg-gray-900 transition flex items-center gap-2">
                    <i class="fa-solid fa-key"></i> Update Password
                </button>
            </div>
        </form>
    </main>

    <script>
        document.getElementById('photo-upload')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    const img = document.querySelector('.w-20.h-20 img') || document.querySelector('.w-20.h-20');
                    const container = img.closest('.w-20.h-20');
                    container.innerHTML = '<img src="' + ev.target.result + '" alt="Avatar" class="w-full h-full object-cover">';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
