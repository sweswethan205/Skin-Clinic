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

// Mark single as read
if (isset($_GET['read_id'])) {
    $id = intval($_GET['read_id']);
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $id AND user_id = $user_id");
    header("Location: notifications.php");
    exit;
}

// Mark all as read
if (isset($_GET['read_all'])) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id AND is_read = 0");
    header("Location: notifications.php");
    exit;
}

// Fetch notifications
$notifications = [];
$result = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

$unread_count = 0;
foreach ($notifications as $n) {
    if (!$n['is_read']) $unread_count++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - GlowSkin Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
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
<body class="bg-brand-lightPink font-sans antialiased">
    <?php include '../includes/header.php'; ?>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-brand-dark">Notifications</h1>
                <p class="text-sm text-brand-textMuted mt-1">Stay updated on your appointment status</p>
            </div>
            <?php if ($unread_count > 0): ?>
                <a href="?read_all=1" class="text-xs font-semibold text-brand-pink bg-brand-lightPink px-4 py-2 rounded-lg hover:opacity-80 transition">
                    Mark All as Read
                </a>
            <?php endif; ?>
        </div>

        <?php if (count($notifications) > 0): ?>
            <div class="space-y-3">
                <?php foreach ($notifications as $n): ?>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-start justify-between shadow-sm <?= $n['is_read'] ? '' : 'border-l-4 border-l-brand-pink' ?>">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-full <?= $n['is_read'] ? 'bg-gray-100 text-gray-400' : 'bg-brand-lightPink text-brand-pink' ?> flex items-center justify-center shrink-0">
                                <i class="<?= $n['type'] === 'status' ? 'fa-solid fa-rotate' : 'fa-regular fa-calendar-check' ?>"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-brand-dark"><?= htmlspecialchars($n['title']) ?></h4>
                                <p class="text-xs text-brand-textMuted mt-0.5"><?= htmlspecialchars($n['message']) ?></p>
                                <span class="text-[10px] text-gray-400 font-medium mt-1 block">
                                    <i class="fa-regular fa-clock mr-1"></i><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 ml-3">
                            <?php if (!$n['is_read']): ?>
                                <a href="?read_id=<?= $n['id'] ?>" class="text-[10px] text-blue-500 hover:underline font-medium">Mark Read</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-regular fa-bell-slash text-2xl text-gray-300"></i>
                </div>
                <h3 class="text-base font-bold text-brand-dark">No Notifications</h3>
                <p class="text-sm text-brand-textMuted mt-1">You're all caught up!</p>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
