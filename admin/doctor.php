<?php
require_once __DIR__ . '/../config/db.php';

$admin = $conn->query("SELECT username, photo FROM admins ORDER BY id ASC LIMIT 1")->fetch_assoc();
$admin_photo = $admin['photo'] ?? '';
$admin_username = $admin['username'] ?? 'Admin';

$message = '';
$message_type = '';

$upload_dir = __DIR__ . '/../uploads/doctors/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle photo upload
function handle_photo_upload($field_name) {
    global $upload_dir;
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return false;
    }
    $filename = 'doctor_' . time() . '_' . uniqid() . '.' . $ext;
    $dest = $upload_dir . $filename;
    return move_uploaded_file($_FILES[$field_name]['tmp_name'], $dest) ? 'uploads/doctors/' . $filename : false;
}

// Handle Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $description = trim($_POST['description']);
    $experience = intval($_POST['experience']);
    $status = $_POST['status'];

    $photo_uploaded = handle_photo_upload('photo');

    if ($_POST['action'] === 'create') {
        if ($photo_uploaded === false) {
            $message = "Invalid file type. Allowed: jpg, jpeg, png, gif, webp.";
            $message_type = "error";
            header("Location: doctor.php?msg=" . urlencode($message) . "&type=$message_type");
            exit;
        }
        $photo = $photo_uploaded ?? '';
        $stmt = $conn->prepare("INSERT INTO doctors (name, email, phone, description, experience, photo, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiss", $name, $email, $phone, $description, $experience, $photo, $status);
        if ($stmt->execute()) {
            $message = "Doctor added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding doctor: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'update' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        // Keep existing photo if no new file uploaded
        if ($photo_uploaded === null) {
            $stmt = $conn->prepare("SELECT photo FROM doctors WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            $photo = $existing['photo'] ?? '';
            $stmt->close();
        } elseif ($photo_uploaded === false) {
            $message = "Invalid file type. Allowed: jpg, jpeg, png, gif, webp.";
            $message_type = "error";
            header("Location: doctor.php?msg=" . urlencode($message) . "&type=$message_type");
            exit;
        } else {
            $photo = $photo_uploaded;
        }
        $stmt = $conn->prepare("UPDATE doctors SET name=?, email=?, phone=?, description=?, experience=?, photo=?, status=? WHERE id=?");
        $stmt->bind_param("ssssissi", $name, $email, $phone, $description, $experience, $photo, $status, $id);
        if ($stmt->execute()) {
            $message = "Doctor updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating doctor: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    }
    header("Location: doctor.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM doctors WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Doctor deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting doctor: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
    header("Location: doctor.php?msg=" . urlencode($message) . "&type=$message_type");
    exit;
}

// Read message from redirect
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $message_type = $_GET['type'] ?? 'success';
}

// Fetch all doctors
$doctors_result = $conn->query("SELECT * FROM doctors ORDER BY name ASC");
$doctors = [];
while ($row = $doctors_result->fetch_assoc()) {
    $doctors[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Doctors Management</title>
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
        .modal-bg { background: rgba(15, 23, 42, 0.5); }
    </style>
</head>
<body class="bg-brand-canvas text-slate-700 min-h-screen flex antialiased">

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>
    <!-- CONTENT -->
    <div class="flex-grow flex flex-col min-w-0 lg:ml-64">

        <!-- HEADER -->
        <header class="h-16 sm:h-20 bg-white border-b border-slate-200/60 flex items-center justify-between px-4 sm:px-8 shrink-0 z-10">
            <div class="flex items-center space-x-4">
                
                <div>
                    <h2 class="text-xl font-extrabold text-brand-dark tracking-tight">Doctors Management</h2>
                    <p class="text-xs text-brand-muted font-medium">Manage clinical staff rosters and professional profiles.</p>
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
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                <div class="bg-white p-4 rounded-xl border border-slate-200/50 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-pink-50 text-brand-pink rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-user-md"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Total Doctors</span>
                        <span class="text-xl font-extrabold text-brand-dark"><?php echo count($doctors); ?></span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200/50 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-circle-check"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Active</span>
                        <span class="text-xl font-extrabold text-brand-dark">
                            <?php echo count(array_filter($doctors, fn($d) => $d['status'] === 'active')); ?>
                        </span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200/50 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-slate-50 text-slate-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-circle-pause"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Inactive</span>
                        <span class="text-xl font-extrabold text-brand-dark">
                            <?php echo count(array_filter($doctors, fn($d) => $d['status'] === 'inactive')); ?>
                        </span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200/50 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4">
                    <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-flask"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Avg Experience</span>
                        <span class="text-xl font-extrabold text-brand-dark">
                            <?php
                            $total_exp = array_sum(array_column($doctors, 'experience'));
                            $count = count($doctors);
                            echo $count > 0 ? round($total_exp / $count, 1) . ' yrs' : 'N/A';
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-sm font-bold text-brand-dark px-2">
                    Medical Team Directory
                </div>
                <button onclick="openCreateModal()" class="px-4 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)] flex items-center justify-center gap-2 shrink-0">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add New Doctor
                </button>
            </div>

            <!-- Doctors Table -->
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200/50 text-[11px] font-bold uppercase tracking-wider text-brand-muted">
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Doctor</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Contact</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Experience</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Description</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-center">Status</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-semibold text-brand-dark">
                            <?php if (empty($doctors)): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="text-brand-muted">
                                        <i class="fa-regular fa-user-xmark text-3xl mb-3 block"></i>
                                        <span class="font-bold text-sm">No doctors found</span>
                                        <p class="text-[11px] font-medium mt-1">Add a new doctor to get started.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($doctors as $doctor): ?>
                            <tr class="hover:bg-slate-50/60 transition-colors group">
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <div class="flex items-center space-x-3">
                                        <?php if (!empty($doctor['photo'])): ?>
                                        <img src="../<?php echo htmlspecialchars($doctor['photo']); ?>" class="w-10 h-10 rounded-xl object-cover border border-slate-100">
                                        <?php else: ?>
                                        <div class="w-10 h-10 bg-brand-lightPink rounded-xl flex items-center justify-center text-brand-pink text-xs font-bold">
                                            <?php echo strtoupper(substr($doctor['name'], 0, 2)); ?>
                                        </div>
                                        <?php endif; ?>
                                        <div>
                                            <span class="font-bold text-brand-dark block group-hover:text-brand-pink transition-colors">Dr. <?php echo htmlspecialchars($doctor['name']); ?></span>
                                            <span class="text-[10px] text-brand-muted block font-medium">ID: #DR-<?php echo str_pad($doctor['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <div class="space-y-0.5">
                                        <span class="block text-brand-dark"><?php echo htmlspecialchars($doctor['email']); ?></span>
                                        <?php if (!empty($doctor['phone'])): ?>
                                        <span class="block text-[10px] text-brand-muted"><?php echo htmlspecialchars($doctor['phone']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6">
                                    <span class="font-medium text-slate-600"><?php echo $doctor['experience']; ?> Years</span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 max-w-xs">
                                    <span class="text-slate-500 line-clamp-2 block">
                                        <?php echo !empty($doctor['description']) ? htmlspecialchars(substr($doctor['description'], 0, 80)) . (strlen($doctor['description']) > 80 ? '...' : '') : '—'; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-center">
                                    <?php if ($doctor['status'] === 'active'): ?>
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                        <i class="fa-solid fa-circle text-[7px]"></i> Active
                                    </span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 bg-slate-100 text-slate-500 border border-slate-200 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                        <i class="fa-regular fa-circle text-[7px]"></i> Inactive
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 sm:py-4 sm:px-6 text-right space-x-1 whitespace-nowrap">
                                    <button onclick="openEditModal(<?php echo $doctor['id']; ?>)" class="p-1.5 bg-slate-50 hover:bg-slate-100 text-brand-muted hover:text-brand-dark rounded-lg transition-colors" title="Edit Doctor">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="confirmDelete(<?php echo $doctor['id']; ?>)" class="p-1.5 bg-slate-50 hover:bg-red-50 text-brand-muted hover:text-red-500 rounded-lg transition-colors" title="Delete Doctor">
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
                    <span>Showing <?php echo count($doctors); ?> doctor<?php echo count($doctors) !== 1 ? 's' : ''; ?></span>
                </div>
            </div>
        </main>
    </div>

    <!-- CREATE MODAL -->
    <div id="createModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-3xl w-full max-w-lg mx-4 shadow-2xl">
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100">
                <h3 class="text-base font-extrabold text-brand-dark"><i class="fa-solid fa-user-plus text-brand-pink mr-2"></i> Add New Doctor</h3>
                <button onclick="closeCreateModal()" class="text-brand-muted hover:text-brand-dark text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="doctor.php" enctype="multipart/form-data" class="p-6 space-y-5">
                <input type="hidden" name="action" value="create">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Full Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Email <span class="text-red-400">*</span></label>
                        <input type="email" name="email" required
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Phone</label>
                        <input type="text" name="phone"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Experience (Years)</label>
                        <input type="number" name="experience" min="0"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Photo</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-brand-dark file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-pink file:text-white hover:file:bg-brand-pinkHover focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all"></textarea>
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Status</label>
                    <select name="status" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark bg-white focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeCreateModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-brand-dark text-xs font-bold rounded-xl transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)]">
                        <i class="fa-solid fa-plus mr-1"></i> Add Doctor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-3xl w-full max-w-lg mx-4 shadow-2xl">
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100">
                <h3 class="text-base font-extrabold text-brand-dark"><i class="fa-regular fa-pen-to-square text-brand-pink mr-2"></i> Edit Doctor</h3>
                <button onclick="closeEditModal()" class="text-brand-muted hover:text-brand-dark text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="doctor.php" enctype="multipart/form-data" class="p-6 space-y-5">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id" value="">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Full Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" id="edit_name" required
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Email <span class="text-red-400">*</span></label>
                        <input type="email" name="email" id="edit_email" required
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Phone</label>
                        <input type="text" name="phone" id="edit_phone"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Experience (Years)</label>
                        <input type="number" name="experience" id="edit_experience" min="0"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Photo <span class="text-[10px] text-brand-muted font-medium normal-case">(leave empty to keep current)</span></label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-brand-dark file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-pink file:text-white hover:file:bg-brand-pinkHover focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Description</label>
                    <textarea name="description" id="edit_description" rows="3"
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all"></textarea>
                </div>
                <div>
                    <label class="text-xs font-bold text-brand-muted uppercase tracking-wider block mb-1.5">Status</label>
                    <select name="status" id="edit_status" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-brand-dark bg-white focus:ring-2 focus:ring-brand-pink/20 focus:border-brand-pink outline-none transition-all">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-brand-dark text-xs font-bold rounded-xl transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)]">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Update Doctor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE CONFIRM MODAL -->
    <div id="deleteModal" class="fixed inset-0 modal-bg flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-3xl w-full max-w-sm mx-4 shadow-2xl p-6 text-center">
            <div class="w-14 h-14 mx-auto bg-red-50 rounded-2xl flex items-center justify-center text-red-500 text-2xl mb-4">
                <i class="fa-regular fa-trash-can"></i>
            </div>
            <h3 class="text-base font-extrabold text-brand-dark mb-2">Delete Doctor?</h3>
            <p class="text-xs font-medium text-brand-muted mb-6">This action cannot be undone. Are you sure you want to delete this doctor?</p>
            <div class="flex justify-center gap-3">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-brand-dark text-xs font-bold rounded-xl transition-all">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(239,68,68,0.25)]">
                    <i class="fa-solid fa-trash-can mr-1"></i> Delete
                </a>
            </div>
        </div>
    </div>

    <script>
        // Doctor data for edit modal (embedded as JSON)
        const doctorsData = <?php echo json_encode($doctors, JSON_UNESCAPED_UNICODE) ?: '[]'; ?>;

        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function openEditModal(id) {
            const doctor = doctorsData.find(d => d.id == id);
            if (!doctor) return;
            document.getElementById('edit_id').value = doctor.id;
            document.getElementById('edit_name').value = doctor.name;
            document.getElementById('edit_email').value = doctor.email;
            document.getElementById('edit_phone').value = doctor.phone || '';
            document.getElementById('edit_experience').value = doctor.experience || 0;
            document.getElementById('edit_description').value = doctor.description || '';
            document.getElementById('edit_status').value = doctor.status;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function confirmDelete(id) {
            document.getElementById('deleteConfirmBtn').href = 'doctor.php?delete=' + id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Close modals on backdrop click
        document.querySelectorAll('.modal-bg').forEach(el => {
            el.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        });

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-bg:not(.hidden)').forEach(m => m.classList.add('hidden'));
            }
        });
    </script>

</body>
</html>
