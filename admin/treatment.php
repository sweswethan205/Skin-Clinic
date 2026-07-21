<?php
require_once __DIR__ . '/../config/db.php';

$admin = $conn->query("SELECT username, photo FROM admins ORDER BY id ASC LIMIT 1")->fetch_assoc();
$admin_photo = $admin['photo'] ?? '';
$admin_username = $admin['username'] ?? 'Admin';

$message = '';
$message_type = '';

$upload_dir = __DIR__ . '/../uploads/treatments/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

function handle_image_upload($field_name)
{
    global $upload_dir;
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return false;
    }
    $filename = 'treatment_' . time() . '_' . uniqid() . '.' . $ext;
    $dest = $upload_dir . $filename;
    return move_uploaded_file($_FILES[$field_name]['tmp_name'], $dest) ? 'uploads/treatments/' . $filename : false;
}

// Handle Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $treatment_name = trim($_POST['treatment_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $duration = intval($_POST['duration']);
    if (!in_array($duration, [30, 60, 90])) $duration = 60;

    $image_uploaded = handle_image_upload('image');

    if ($_POST['action'] === 'create') {
        if ($image_uploaded === false) {
            $message = "Invalid file type. Allowed: jpg, jpeg, png, gif, webp.";
            $message_type = "error";
            header("Location: treatment.php?msg=" . urlencode($message) . "&type=$message_type");
            exit;
        }
        $image = $image_uploaded ?? '';
        $stmt = $conn->prepare("INSERT INTO treatments (treatment_name, description, price, duration, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $treatment_name, $description, $price, $duration, $image);
        if ($stmt->execute()) {
            $message = "Treatment added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding treatment: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'update' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        if ($image_uploaded === null) {
            $stmt = $conn->prepare("SELECT image FROM treatments WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            $image = $existing['image'] ?? '';
            $stmt->close();
        } elseif ($image_uploaded === false) {
            $message = "Invalid file type. Allowed: jpg, jpeg, png, gif, webp.";
            $message_type = "error";
            header("Location: treatment.php?msg=" . urlencode($message) . "&type=$message_type");
            exit;
        } else {
            $image = $image_uploaded;
        }
        $stmt = $conn->prepare("UPDATE treatments SET treatment_name=?, description=?, price=?, duration=?, image=? WHERE id=?");
        $stmt->bind_param("ssdisi", $treatment_name, $description, $price, $duration, $image, $id);
        if ($stmt->execute()) {
            $message = "Treatment updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating treatment: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    }
    header("Location: treatment.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM treatments WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Treatment deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting treatment: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
    header("Location: treatment.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Read message from redirect
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $message_type = $_GET['type'] ?? 'success';
}

// Fetch all treatments
$treatments_result = $conn->query("SELECT * FROM treatments ORDER BY treatment_name ASC");
$treatments = [];
while ($row = $treatments_result->fetch_assoc()) {
    $row['id'] = (int)$row['id'];
    $treatments[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Treatment Catalog</title>
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
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .modal-bg {
            background: rgba(15, 23, 42, 0.5);
        }
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

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>
    <!-- CONTENT -->
    <div class="flex-grow flex flex-col min-w-0 lg:ml-64">

        <!-- HEADER -->
        <header class="h-16 sm:h-20 bg-white dark:bg-gray-900 border-b border-slate-200/60 dark:border-gray-800 flex items-center justify-between px-4 sm:px-8 shrink-0 z-10 sticky top-0">
            <div class="flex items-center">

                <div>
                    <h2 class="text-xl font-extrabold text-brand-dark dark:text-white tracking-tight">Treatment Catalog</h2>
                    <p class="text-xs text-brand-muted dark:text-gray-400 font-medium">Configure medical skincare items, standard pricing, and descriptions.</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <?php include 'header-actions.php'; ?>
                <a href="profile.php" class="flex items-center space-x-3 hover:opacity-80 transition">
                    <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 bg-brand-lightPink flex items-center justify-center text-brand-pink font-bold text-sm">
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
        </header>

        <!-- MAIN -->
        <main class="flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6">

            <!-- Message Alert -->
            <?php if ($message): ?>
                <div class="px-5 py-3.5 rounded-xl border text-sm font-bold flex items-center gap-3 <?php echo $message_type === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200'; ?>">
                    <i class="fa-solid <?php echo $message_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-current opacity-60 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-pink-50 text-brand-pink rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-hand-holding-medical"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Total Treatments</span>
                        <span class="text-xl font-extrabold text-brand-dark"><?php echo count($treatments); ?></span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-tag"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Avg Price</span>
                        <span class="text-xl font-extrabold text-brand-dark">
                            <?php
                            $prices = array_column($treatments, 'price');
                            $count = count($prices);
                            echo $count > 0 ? '$' . number_format(array_sum($prices) / $count, 2) : 'N/A';
                            ?>
                        </span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-slate-200/50 dark:border-gray-800 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-dollar-sign"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Highest Price</span>
                        <span class="text-xl font-extrabold text-brand-dark">
                            <?php echo $count > 0 ? '$' . number_format(max($prices), 2) : 'N/A'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-2xl border border-slate-200/50 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-sm font-bold text-brand-dark px-2">
                    Active Clinical Treatment Roster
                </div>
                <button onclick="openCreateModal()" class="px-4 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)] flex items-center justify-center gap-2 shrink-0">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add New Treatment
                </button>
            </div>

            <!-- Treatments Table -->
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-slate-200/60 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-gray-950 border-b border-slate-200/50 dark:border-gray-800 text-[11px] font-bold uppercase tracking-wider text-brand-muted dark:text-gray-300">
                                <th class="py-3 px-3 sm:py-4 sm:px-6">#</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Treatment</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Description</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-center">Duration</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-center">Price</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-semibold text-brand-dark dark:text-gray-300">
                            <?php if (empty($treatments)): ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <div class="text-brand-muted">
                                            <i class="fa-regular fa-hand-back-fist text-3xl mb-3 block"></i>
                                            <span class="font-bold text-sm">No treatments found</span>
                                            <p class="text-[11px] font-medium mt-1">Add a new treatment to get started.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($treatments as $treatment): ?>
                                    <tr class="hover:bg-slate-50/60 transition-colors group">
                                        <td class="py-3 px-3 sm:py-4 sm:px-6">
                                            <div class="flex items-center space-x-3">
                                                <?php if (!empty($treatment['image'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($treatment['image']); ?>" class="w-10 h-10 rounded-xl object-cover border border-slate-100">
                                                <?php else: ?>
                                                    <div class="w-10 h-10 bg-brand-lightPink rounded-xl flex items-center justify-center text-brand-pink text-xs font-bold">
                                                        <i class="fa-solid fa-hand-holding-medical"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <span class="font-bold text-brand-dark block group-hover:text-brand-pink transition-colors"><?php echo htmlspecialchars($treatment['treatment_name']); ?></span>
                                                    <span class="text-[10px] text-brand-muted block font-medium">ID: #TX-<?php echo str_pad($treatment['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 sm:py-4 sm:px-6 max-w-sm">
                                            <span class="text-slate-500 dark:text-gray-400 line-clamp-2 block">
                                                <?php echo !empty($treatment['description']) ? htmlspecialchars(substr($treatment['description'], 0, 100)) . (strlen($treatment['description']) > 100 ? '...' : '') : '—'; ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 sm:py-4 sm:px-6 text-center">
                                            <span class="px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                                <i class="fa-regular fa-clock"></i> <?php echo $treatment['duration']; ?> min
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 sm:py-4 sm:px-6 text-center">
                                            <span class="font-bold text-slate-700 dark:text-gray-300"><?php echo number_format($treatment['price'], 2); ?>MMK</span>
                                        </td>
                                        <td class="py-3 px-3 sm:py-4 sm:px-6 text-right space-x-1 whitespace-nowrap">
                                            <button onclick="openEditModal(<?php echo $treatment['id']; ?>)" class="p-1.5 bg-slate-50 hover:bg-slate-100 text-brand-muted hover:text-brand-dark rounded-lg transition-colors" title="Edit Treatment">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button onclick="confirmDelete(<?php echo $treatment['id']; ?>)" class="p-1.5 bg-slate-50 hover:bg-red-50 text-brand-muted hover:text-red-500 rounded-lg transition-colors" title="Delete Treatment">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="bg-slate-50/50 dark:bg-gray-900 px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-brand-muted font-semibold dark:text-gray-400">
                    <span>Showing <?php echo count($treatments); ?> treatment<?php echo count($treatments) !== 1 ? 's' : ''; ?></span>
                </div>
            </div>
        </main>
    </div>

    <!-- CREATE MODAL -->
    <div id="createModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-lg mx-4 shadow-2xl">
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100">
                <h3 class="text-base font-extrabold text-brand-dark dark:text-white"><i class="fa-solid fa-plus text-brand-pink mr-2"></i> Add New Treatment</h3>
                <button onclick="closeCreateModal()" class="text-brand-muted hover:text-brand-dark text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="treatment.php" enctype="multipart/form-data" class="p-6 space-y-5">
                <input type="hidden" name="action" value="create">

                <div>
                    <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Treatment Name <span class="text-red-400">*</span></label>
                    <input type="text" name="treatment_name" required
                        class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                </div>

                <div>
                    <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Price <span class="text-red-400">*</span></label>
                        <input type="number" name="price" step="0.01" min="0" required
                            class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Duration <span class="text-red-400">*</span></label>
                        <select name="duration" required class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                            <option value="30">30 minutes</option>
                            <option value="60" selected>60 minutes</option>
                            <option value="90">90 minutes</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Image</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"
                        class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-pink file:text-white hover:file:bg-brand-pinkHover focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeCreateModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:text-white text-brand-dark text-xs font-bold rounded-xl transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)]">
                        <i class="fa-solid fa-plus mr-1"></i> Add Treatment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-lg mx-4 shadow-2xl">
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100">
                <h3 class="text-base font-extrabold text-brand-dark dark:text-white"><i class="fa-regular fa-pen-to-square text-brand-pink mr-2"></i> Edit Treatment</h3>
                <button onclick="closeEditModal()" class="text-brand-muted hover:text-brand-dark text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="treatment.php" enctype="multipart/form-data" class="p-6 space-y-5">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id" value="">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Treatment Name <span class="text-red-400">*</span></label>
                        <input type="text" name="treatment_name" id="edit_treatment_name" required
                            class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Description</label>
                    <textarea name="description" id="edit_description" rows="3"
                        class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all"></textarea>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Price <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input type="number" name="price" id="edit_price" step="0.01" min="0" required
                                class="w-full border border-slate-200 dark:border-gray-700 rounded-xl pl-8 pr-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Duration <span class="text-red-400">*</span></label>
                        <select name="duration" id="edit_duration" required class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                            <option value="30">30 min</option>
                            <option value="60">60 min</option>
                            <option value="90">90 min</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Image <span class="text-[10px] text-brand-muted font-medium normal-case">(leave empty to keep current)</span></label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"
                            class="w-full border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold text-brand-dark dark:text-white dark:bg-gray-900 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-pink file:text-white hover:file:bg-brand-pinkHover focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:text-white text-brand-dark text-xs font-bold rounded-xl transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)]">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Update Treatment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE CONFIRM MODAL -->
    <div id="deleteModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-sm mx-4 shadow-2xl p-6 text-center">
            <div class="w-14 h-14 mx-auto bg-red-50 rounded-2xl flex items-center justify-center text-red-500 text-2xl mb-4">
                <i class="fa-regular fa-trash-can"></i>
            </div>
            <h3 class="text-base font-extrabold text-brand-dark dark:text-white mb-2">Delete Treatment?</h3>
            <p class="text-xs font-medium text-brand-muted dark:text-gray-300 mb-6">This action cannot be undone. Are you sure you want to delete this treatment?</p>
            <div class="flex justify-center gap-3">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:text-white text-brand-dark text-xs font-bold rounded-xl transition-all">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(239,68,68,0.25)]">
                    <i class="fa-solid fa-trash-can mr-1"></i> Delete
                </a>
            </div>
        </div>
    </div>

    <script>
        const treatmentsData = <?php echo json_encode($treatments, JSON_UNESCAPED_UNICODE) ?: '[]'; ?>;

        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function openEditModal(id) {
            const treatment = treatmentsData.find(t => t.id == id);
            if (!treatment) return;
            document.getElementById('edit_id').value = treatment.id;
            document.getElementById('edit_treatment_name').value = treatment.treatment_name;
            document.getElementById('edit_description').value = treatment.description || '';
            document.getElementById('edit_price').value = treatment.price;
            document.getElementById('edit_duration').value = treatment.duration || 60;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function confirmDelete(id) {
            document.getElementById('deleteConfirmBtn').href = 'treatment.php?delete=' + id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        document.querySelectorAll('.modal-bg').forEach(el => {
            el.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-bg:not(.hidden)').forEach(m => m.classList.add('hidden'));
            }
        });
    </script>

</body>

</html>