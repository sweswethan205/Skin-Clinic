<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../config/db.php';

$admin = $conn->query("SELECT username, photo FROM admins ORDER BY id ASC LIMIT 1")->fetch_assoc();
$admin_photo = $admin['photo'] ?? '';
$admin_username = $admin['username'] ?? 'Admin';

$message = '';
$msg_type = '';

// Handle status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    $new_status = '';
    if ($action === 'confirm') $new_status = 'confirmed';
    elseif ($action === 'cancel') $new_status = 'cancelled';
    elseif ($action === 'complete') $new_status = 'completed';
    elseif ($action === 'pending') $new_status = 'pending';

    if ($new_status) {
        // Get appointment info for notification
        $info = $conn->prepare("
            SELECT a.user_id, a.schedule_id, t.treatment_name 
            FROM appointments a 
            JOIN treatments t ON t.id = a.treatment_id 
            WHERE a.id = ? LIMIT 1
        ");
        $info->bind_param("i", $id);
        $info->execute();
        $info_res = $info->get_result();
        $app_info = $info_res->fetch_assoc();
        $info->close();

        $stmt = $conn->prepare("UPDATE appointments SET status=? WHERE id=?");
        $stmt->bind_param("si", $new_status, $id);
        if ($stmt->execute()) {
            $message = "Appointment #$id " . ($action === 'cancel' ? 'cancelled' : ($action === 'confirm' ? 'confirmed' : ($action === 'complete' ? 'completed' : 'updated'))) . " successfully.";
            $msg_type = 'success';

            // Create notification
            if ($app_info) {
                $title = 'Appointment ' . ucfirst($new_status);
                $treatment_name = $app_info['treatment_name'];
                $status_label = ucfirst($new_status);
                $notif_msg = "Your \"$treatment_name\" appointment has been $status_label.";
                $target = 'user';
                $notif = $conn->prepare("INSERT INTO notifications (user_id, appointment_id, title, message, type, target_role) VALUES (?, ?, ?, ?, 'status', ?)");
                $notif->bind_param("iisss", $app_info['user_id'], $id, $title, $notif_msg, $target);
                $notif->execute();
                $notif->close();
            }
        }
        $stmt->close();

        // If cancelled, mark schedule as available
        if ($action === 'cancel') {
            $stmt = $conn->prepare("UPDATE schedules s JOIN appointments a ON a.schedule_id = s.id SET s.is_booked='no' WHERE a.id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch appointments
$appointments = [];
$query = "SELECT a.id, a.status, a.created_at, a.receipt_image,
                 u.name AS patient_name,
                 t.treatment_name, t.price,
                 d.name AS doctor_name,
                 s.available_date,
                 s.start_time,
                 pm.method_name AS payment_method
          FROM appointments a
          JOIN users u ON u.id = a.user_id
          JOIN treatments t ON t.id = a.treatment_id
          JOIN schedules s ON s.id = a.schedule_id
          JOIN doctors d ON d.id = s.doctor_id
          LEFT JOIN payment_methods pm ON pm.id = a.payment_method_id
          ORDER BY a.created_at DESC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

// Counts by status
$counts = ['total' => 0, 'pending' => 0, 'confirmed' => 0, 'cancelled' => 0, 'completed' => 0];
foreach ($appointments as $a) {
    $counts['total']++;
    $s = $a['status'];
    if (isset($counts[$s])) $counts[$s]++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Appointments Registry</title>
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
                        brand: {
                            pink: '#FF6584',
                            pinkHover: '#E04F6E',
                            lightPink: '#FFF0F2',
                            dark: '#0F172A',
                            muted: '#64748B',
                            canvas: '#F1F5F9'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    <script>
        (function() {
            const saved = localStorage.getItem('admin_theme');
            if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
            updateIcons();
        })();
        function updateIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            const moon = document.getElementById('admin-icon-moon');
            const sun = document.getElementById('admin-icon-sun');
            if (moon) moon.style.display = isDark ? 'none' : 'inline';
            if (sun) sun.style.display = isDark ? 'inline' : 'none';
        }
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('admin_theme', html.classList.contains('dark') ? 'dark' : 'light');
            updateIcons();
        }
    </script>
</head>
<body class="bg-brand-canvas dark:bg-gray-950 text-slate-700 dark:text-gray-100 min-h-screen flex antialiased">

    <?php include 'sidebar.php'; ?>
    
    <div class="flex-grow flex flex-col min-w-0 lg:ml-64">
        
        <header class="h-16 sm:h-20 bg-white dark:bg-gray-900 border-b border-slate-200/60 dark:border-gray-800 flex items-center justify-between px-4 sm:px-8 shrink-0 z-10 sticky top-0">
            <div class="flex items-center space-x-4">
                
                <div>
            <h2 class="text-xl font-extrabold text-brand-dark dark:text-white tracking-tight">Appointments Management</h2>
            <p class="text-xs text-brand-muted dark:text-gray-400 font-medium">Review scheduling pipelines and verify intake statuses.</p>
                </div>
            </div>

    <div class="flex items-center space-x-6">
        <div class="relative w-64">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-brand-muted text-xs">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" id="search-input" placeholder="Search appointments..." class="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:border-brand-pink focus:bg-white transition-all placeholder:text-slate-400 dark:placeholder:text-gray-500 dark:text-white">
        </div>

        <?php include 'header-actions.php'; ?>

        <div class="flex items-center space-x-6">
                <a href="profile.php" class="flex items-center space-x-3 border-l pl-6 border-slate-200 dark:border-gray-700 hover:opacity-80 transition">
                    <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 dark:border-gray-700 bg-brand-lightPink flex items-center justify-center text-brand-pink font-bold text-sm">
                        <?php if ($admin_photo): ?>
                            <img src="../<?php echo htmlspecialchars($admin_photo); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-brand-dark dark:text-white block leading-tight"><?php echo htmlspecialchars($admin_username); ?></span>
                        <!-- <span class="text-[10px] font-medium text-brand-muted">Clinic Supervisor</span> -->
                    </div>
                </a>
            </div>
            </div>
        </header>

        <main class="flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6">
            
            <?php if ($message): ?>
            <div class="px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 <?= $msg_type === 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' : 'bg-red-50 text-red-600 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800' ?>">
                <i class="fa-solid <?= $msg_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="flex flex-wrap gap-2" id="filter-buttons">
                    <button onclick="filterTable('all')" class="filter-btn px-4 py-2 bg-brand-dark text-white text-xs font-bold rounded-xl shadow-xs transition-all" data-filter="all">All Intake (<?= $counts['total'] ?>)</button>
                    <button onclick="filterTable('pending')" class="filter-btn px-4 py-2 bg-slate-50 hover:bg-slate-100 text-brand-muted hover:text-brand-dark dark:text-gray-400 dark:hover:text-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-xs font-bold rounded-xl transition-all border border-slate-200/40 dark:border-gray-700" data-filter="pending">Pending (<?= $counts['pending'] ?>)</button>
                    <button onclick="filterTable('confirmed')" class="filter-btn px-4 py-2 bg-slate-50 hover:bg-slate-100 text-brand-muted hover:text-brand-dark dark:text-gray-400 dark:hover:text-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-xs font-bold rounded-xl transition-all border border-slate-200/40 dark:border-gray-700" data-filter="confirmed">Confirmed (<?= $counts['confirmed'] ?>)</button>
                    <button onclick="filterTable('cancelled')" class="filter-btn px-4 py-2 bg-slate-50 hover:bg-slate-100 text-brand-muted hover:text-brand-dark dark:text-gray-400 dark:hover:text-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-xs font-bold rounded-xl transition-all border border-slate-200/40 dark:border-gray-700" data-filter="cancelled">Cancelled (<?= $counts['cancelled'] ?>)</button>
                    <button onclick="filterTable('completed')" class="filter-btn px-4 py-2 bg-slate-50 hover:bg-slate-100 text-brand-muted hover:text-brand-dark dark:text-gray-400 dark:hover:text-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-xs font-bold rounded-xl transition-all border border-slate-200/40 dark:border-gray-700" data-filter="completed">Completed (<?= $counts['completed'] ?>)</button>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-slate-200/60 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-gray-950 border-b border-slate-200/50 dark:border-gray-800 text-[11px] font-bold uppercase tracking-wider text-brand-muted dark:text-gray-400">
                                <th class="py-3 px-3 sm:py-4 sm:px-6">#</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Patient</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Treatment</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Doctor</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Date & Time</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Payment</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Receipt</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Status</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800 text-xs font-semibold text-brand-dark dark:text-gray-300">
                            <?php if (count($appointments) > 0): ?>
                                <?php $i = 1; ?>
                                <?php foreach ($appointments as $a): 
                                    $status_class = match($a['status']) {
                                        'confirmed' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                        'pending' => 'text-blue-600 bg-blue-50 border-blue-100',
                                        'cancelled' => 'text-rose-600 bg-rose-50 border-rose-100',
                                        'completed' => 'text-slate-600 bg-slate-50 border-slate-100',
                                        default => 'text-slate-600 bg-slate-50 border-slate-100'
                                    };
                                ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-gray-800/50 transition-colors group" data-status="<?= $a['status'] ?>">
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-brand-muted"><?= $i++ ?></td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-xl bg-brand-lightPink text-brand-pink flex items-center justify-center text-xs font-bold border border-pink-100">
                                            <?= strtoupper(substr($a['patient_name'], 0, 2)) ?>
                                        </div>
                                        <span class="font-bold text-brand-dark group-hover:text-brand-pink transition-colors"><?= htmlspecialchars($a['patient_name']) ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 font-bold"><?= htmlspecialchars($a['treatment_name']) ?></td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-user-doctor text-brand-muted text-[11px]"></i>Dr. <?= htmlspecialchars($a['doctor_name']) ?></span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <span class="block font-bold"><?= date("d M Y", strtotime($a['available_date'])) ?></span>
                                    <span class="text-[10px] text-brand-muted block font-medium"><?= date("h:i A", strtotime($a['start_time'])) ?></span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <?php if (!empty($a['payment_method'])): ?>
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-brand-dark dark:text-gray-300 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 px-2 py-1 rounded-lg">
                                            <i class="fa-solid fa-wallet text-brand-muted text-[9px]"></i>
                                            <?= htmlspecialchars($a['payment_method']) ?>
                                        </span>
                                        <span class="block text-[11px] font-bold text-emerald-600 mt-0.5"><?= number_format($a['price'], 2) ?> MMK</span>
                                    <?php else: ?>
                                        <span class="text-[10px] text-brand-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <?php if (!empty($a['receipt_image']) && file_exists(__DIR__ . '/../' . ltrim($a['receipt_image'], './'))): ?>
                                        <button onclick="openReceiptModal('<?= htmlspecialchars($a['receipt_image']) ?>')" class="relative group cursor-pointer">
                                            <img src="<?= htmlspecialchars($a['receipt_image']) ?>" alt="Receipt" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shadow-xs group-hover:ring-2 group-hover:ring-brand-pink transition-all">
                                            <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-brand-pink text-white rounded-full flex items-center justify-center text-[8px] opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fa-solid fa-expand"></i>
                                            </span>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-[10px] text-brand-muted italic">No receipt</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <span class="text-[10px] font-bold <?= $status_class ?> px-2 py-0.5 rounded-lg border"><?= ucfirst($a['status']) ?></span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-right space-x-1 whitespace-nowrap">
                                    <?php if ($a['status'] === 'pending'): ?>
                                        <a href="?action=confirm&id=<?= $a['id'] ?>" class="p-1.5 bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white rounded-lg transition-colors inline-block" title="Confirm"><i class="fa-regular fa-circle-check"></i></a>
                                        <a href="?action=cancel&id=<?= $a['id'] ?>" onclick="return confirm('Cancel this appointment?')" class="p-1.5 bg-rose-50 hover:bg-rose-500 text-rose-500 hover:text-white rounded-lg transition-colors inline-block" title="Cancel"><i class="fa-regular fa-circle-xmark"></i></a>
                                    <?php elseif ($a['status'] === 'confirmed'): ?>
                                        <a href="?action=complete&id=<?= $a['id'] ?>" class="p-1.5 bg-blue-50 hover:bg-blue-500 text-blue-600 hover:text-white rounded-lg transition-colors inline-block" title="Mark Completed"><i class="fa-solid fa-check"></i></a>
                                        <a href="?action=cancel&id=<?= $a['id'] ?>" onclick="return confirm('Cancel this appointment?')" class="p-1.5 bg-rose-50 hover:bg-rose-500 text-rose-500 hover:text-white rounded-lg transition-colors inline-block" title="Cancel"><i class="fa-regular fa-circle-xmark"></i></a>
                                    <?php elseif ($a['status'] === 'completed' || $a['status'] === 'cancelled'): ?>
                                        <a href="?action=pending&id=<?= $a['id'] ?>" class="p-1.5 bg-slate-50 hover:bg-slate-100 text-brand-muted hover:text-brand-dark rounded-lg transition-colors inline-block" title="Reset to Pending"><i class="fa-solid fa-arrow-rotate-left"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <tr><td colspan="9" class="py-8 text-center text-brand-muted">No appointments found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="bg-slate-50/50 dark:bg-gray-800 px-6 py-4 border-t border-slate-100 dark:border-gray-700 flex items-center justify-between text-xs text-brand-muted dark:text-gray-400 font-semibold">
                    <span>Showing <?= count($appointments) ?> <?= count($appointments) === 1 ? 'entry' : 'entries' ?></span>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Status filter
        function filterTable(status) {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.className = btn.className.replace('bg-brand-dark text-white', 'bg-slate-50 text-brand-muted border border-slate-200/40 hover:bg-slate-100 hover:text-brand-dark');
                if (btn.dataset.filter === status) {
                    btn.className = 'filter-btn px-4 py-2 bg-brand-dark text-white text-xs font-bold rounded-xl shadow-xs transition-all';
                }
            });
            document.querySelectorAll('tbody tr').forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Live search
        document.getElementById('search-input')?.addEventListener('keyup', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                if (row.style.display === 'none') return;
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    </script>

    <!-- Receipt Image Modal -->
    <div id="receipt-modal" class="fixed inset-0 bg-black/50 dark:bg-black/70 backdrop-blur-sm items-center justify-center z-50 p-4 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
                <h3 class="text-xs font-bold text-brand-dark dark:text-white uppercase tracking-wider">Payment Receipt</h3>
                <button onclick="closeReceiptModal()" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-brand-muted hover:text-brand-dark dark:text-gray-300 dark:hover:text-white flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
            <div class="p-4 flex items-center justify-center">
                <img id="receipt-modal-img" src="" alt="Receipt" class="max-w-full max-h-[75vh] object-contain rounded-xl border border-slate-100 dark:border-gray-700">
            </div>
        </div>
    </div>

    <script>
        function openReceiptModal(src) {
            const modal = document.getElementById('receipt-modal');
            const img = document.getElementById('receipt-modal-img');
            img.src = src;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeReceiptModal() {
            const modal = document.getElementById('receipt-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('receipt-modal').addEventListener('click', function(e) {
            if (e.target === this) closeReceiptModal();
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
