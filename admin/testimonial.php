<?php
require_once __DIR__ . '/../config/db.php';

$admin = $conn->query("SELECT username, photo FROM admins ORDER BY id ASC LIMIT 1")->fetch_assoc();
$admin_photo = $admin['photo'] ?? '';
$admin_username = $admin['username'] ?? 'Admin';

$message = '';
$message_type = '';

// Approve / Reject
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE testimonials SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();
        $message = "Testimonial " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully!";
        $message_type = "success";
    }
    header("Location: testimonial.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Testimonial deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting testimonial.";
        $message_type = "error";
    }
    $stmt->close();
    header("Location: testimonial.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Read message from redirect
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $message_type = $_GET['type'] ?? 'success';
}

$testimonials = $conn->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Testimonials</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
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
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .modal-bg { background: rgba(15, 23, 42, 0.5); }
    </style>
</head>
<body class="bg-brand-canvas text-slate-700 min-h-screen flex antialiased">

    <?php include 'sidebar.php'; ?>

    <div class="flex-grow flex flex-col min-w-0 lg:ml-64">
        <header class="h-16 sm:h-20 bg-white border-b border-slate-200/60 flex items-center justify-between px-4 sm:px-8 shrink-0 z-10 sticky top-0">
            <div class="flex items-center space-x-4">
                
                <div>
                    <h2 class="text-xl font-extrabold text-brand-dark tracking-tight">Testimonials</h2>
                    <p class="text-xs text-brand-muted font-medium">Manage patient reviews and feedback</p>
                </div>
            </div>
            <a href="profile.php" class="flex items-center space-x-3 hover:opacity-80 transition">
                <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 bg-brand-lightPink flex items-center justify-center text-brand-pink font-bold text-sm">
                    <?php if ($admin_photo): ?>
                        <img src="../<?php echo htmlspecialchars($admin_photo); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="text-xs font-bold text-brand-dark block leading-tight"><?php echo htmlspecialchars($admin_username); ?></span>
                    <!-- <span class="text-[10px] font-medium text-brand-muted">Clinic Supervisor</span> -->
                </div>
            </a>
        </header>

        <main class="flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6">
            <?php if ($message): ?>
            <div class="px-5 py-3.5 rounded-xl border text-sm font-bold flex items-center gap-3 <?php echo $message_type === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200'; ?>">
                <i class="fa-solid <?php echo $message_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-current opacity-60 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                <?php
                $total = count($testimonials);
                $pending = count(array_filter($testimonials, fn($t) => $t['status'] === 'pending'));
                $approved = count(array_filter($testimonials, fn($t) => $t['status'] === 'approved'));
                $rejected = count(array_filter($testimonials, fn($t) => $t['status'] === 'rejected'));
                ?>
                <div class="bg-white p-4 rounded-xl border border-slate-200/50 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-pink-50 text-brand-pink rounded-xl flex items-center justify-center text-sm"><i class="fa-regular fa-comment-dots"></i></div>
                    <div><span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Total</span><span class="text-xl font-extrabold text-brand-dark"><?php echo $total; ?></span></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200/50 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-regular fa-clock"></i></div>
                    <div><span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Pending</span><span class="text-xl font-extrabold text-brand-dark"><?php echo $pending; ?></span></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200/50 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-check"></i></div>
                    <div><span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Approved</span><span class="text-xl font-extrabold text-brand-dark"><?php echo $approved; ?></span></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200/50 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-red-50 text-red-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-ban"></i></div>
                    <div><span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Rejected</span><span class="text-xl font-extrabold text-brand-dark"><?php echo $rejected; ?></span></div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200/50 text-[11px] font-bold uppercase tracking-wider text-brand-muted">
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Patient</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Rating</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Review</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Status</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Date</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-semibold text-brand-dark">
                            <?php if (empty($testimonials)): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="text-brand-muted">
                                        <i class="fa-regular fa-comment-dots text-3xl mb-3 block"></i>
                                        <span class="font-bold text-sm">No testimonials yet</span>
                                        <p class="text-[11px] font-medium mt-1">Patient reviews will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($testimonials as $t): ?>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-full bg-brand-lightPink flex items-center justify-center text-brand-pink text-xs font-bold">
                                            <?php echo strtoupper(substr($t['name'], 0, 1)); ?>
                                        </div>
                                        <span class="font-bold"><?php echo htmlspecialchars($t['name']); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <div class="flex items-center gap-0.5 text-amber-400 text-xs">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa-solid fa-star <?php echo $i <= $t['rating'] ? '' : 'text-slate-200'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 max-w-xs">
                                    <span class="text-slate-500 line-clamp-2 block"><?php echo htmlspecialchars($t['review_text']); ?></span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <?php
                                    $badge = match($t['status']) {
                                        'approved' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                        'rejected' => 'bg-red-50 text-red-500 border-red-200',
                                        default => 'bg-amber-50 text-amber-600 border-amber-200'
                                    };
                                    ?>
                                    <span class="text-[10px] font-bold px-3 py-1 rounded-full border <?php echo $badge; ?>">
                                        <?php echo ucfirst($t['status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-brand-muted"><?php echo date('M d, Y', strtotime($t['created_at'])); ?></td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-right space-x-1 whitespace-nowrap">
                                    <?php if ($t['status'] === 'pending'): ?>
                                        <a href="?action=approve&id=<?php echo $t['id']; ?>" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-500 rounded-lg transition-colors inline-block" title="Approve">
                                            <i class="fa-solid fa-check"></i>
                                        </a>
                                        <a href="?action=reject&id=<?php echo $t['id']; ?>" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-500 rounded-lg transition-colors inline-block" title="Reject">
                                            <i class="fa-solid fa-ban"></i>
                                        </a>
                                    <?php endif; ?>
                                    <button onclick="confirmDelete(<?php echo $t['id']; ?>)" class="p-1.5 bg-slate-50 hover:bg-red-50 text-brand-muted hover:text-red-500 rounded-lg transition-colors" title="Delete">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="bg-slate-50/50 px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-brand-muted font-semibold">
                    <span>Showing <?php echo count($testimonials); ?> testimonial<?php echo count($testimonials) !== 1 ? 's' : ''; ?></span>
                </div>
            </div>
        </main>
    </div>

    <div id="deleteModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-3xl w-full max-w-sm mx-4 shadow-2xl p-6 text-center">
            <div class="w-14 h-14 mx-auto bg-red-50 rounded-2xl flex items-center justify-center text-red-500 text-2xl mb-4">
                <i class="fa-regular fa-trash-can"></i>
            </div>
            <h3 class="text-base font-extrabold text-brand-dark mb-2">Delete Testimonial?</h3>
            <p class="text-xs font-medium text-brand-muted mb-6">This action cannot be undone.</p>
            <div class="flex justify-center gap-3">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-brand-dark text-xs font-bold rounded-xl transition-all">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(239,68,68,0.25)]">
                    <i class="fa-solid fa-trash-can mr-1"></i> Delete
                </a>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            document.getElementById('deleteConfirmBtn').href = 'testimonial.php?delete=' + id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
        document.querySelectorAll('.modal-bg').forEach(el => {
            el.addEventListener('click', function(e) { if (e.target === this) this.classList.add('hidden'); });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') document.querySelectorAll('.modal-bg:not(.hidden)').forEach(m => m.classList.add('hidden'));
        });
    </script>
</body>
</html>