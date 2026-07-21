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

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($_POST['action'] === 'add') {
        if (empty($name) || empty($email) || empty($phone) || empty($password)) {
            $message = 'All fields are required.';
            $msg_type = 'error';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed);
            if ($stmt->execute()) {
                $message = 'User added successfully.';
                $msg_type = 'success';
            } else {
                $message = 'Email already exists.';
                $msg_type = 'error';
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0 && !empty($name) && !empty($email) && !empty($phone)) {
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, password=? WHERE id=?");
                $stmt->bind_param("ssssi", $name, $email, $phone, $hashed, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
                $stmt->bind_param("sssi", $name, $email, $phone, $id);
            }
            if ($stmt->execute()) {
                $message = 'User updated successfully.';
                $msg_type = 'success';
            } else {
                $message = 'Update failed.';
                $msg_type = 'error';
            }
            $stmt->close();
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = 'User deleted successfully.';
        $msg_type = 'success';
    }
    $stmt->close();
}

// Pagination
$per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$total_rows = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'];
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// Fetch users (paginated)
$users = [];
$result = $conn->prepare("SELECT id, name, email, phone, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
$result->bind_param("ii", $per_page, $offset);
$result->execute();
$res = $result->get_result();
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $users[] = $row;
    }
}
$result->close();

// Stats
$total_users = $total_rows;
$new_this_month = 0;
$month_start = date('Y-m-01');
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM users WHERE created_at >= ?");
$stmt->bind_param("s", $month_start);
$stmt->execute();
$stmt->bind_result($new_this_month);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowSkin Clinic - Patients Registry</title>
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
            <h2 class="text-xl font-extrabold text-brand-dark dark:text-gray-100 dark:text-white tracking-tight">Patients Management</h2>
            <p class="text-xs text-brand-muted dark:text-gray-400 font-medium">Manage all registered patient accounts.</p>
                </div>
            </div>

    <div class="flex items-center space-x-6">
        <?php include 'header-actions.php'; ?>
        <a href="profile.php" class="flex items-center space-x-3 border-l pl-6 border-slate-200 dark:border-gray-700 hover:opacity-80 transition">
                    <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 bg-brand-lightPink dark:border-gray-700 flex items-center justify-center text-brand-pink font-bold text-sm">
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

        <main class="flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6">
            
            <?php if ($message): ?>
            <div class="px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 <?= $msg_type === 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200' ?>">
                <i class="fa-solid <?= $msg_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white p-4 rounded-xl border border-slate-200/50 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4 dark:bg-gray-900 dark:border-gray-800">
                    <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-user-group"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">Total Registered</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-gray-100"><?= $total_users ?> Patients</span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200/50 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4 dark:bg-gray-900 dark:border-gray-800">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-user-check"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">With Appointments</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-gray-100"><?= $total_users ?> Active</span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200/50 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex items-center space-x-4 dark:bg-gray-900 dark:border-gray-800">
                    <div class="w-10 h-10 bg-pink-50 text-brand-pink rounded-xl flex items-center justify-center text-sm"><i class="fa-solid fa-user-plus"></i></div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider block">New This Month</span>
                        <span class="text-xl font-extrabold text-brand-dark dark:text-gray-100">+<?= $new_this_month ?> Profiles</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:bg-gray-900 dark:border-gray-800">
                <div class="flex gap-2">
                    <input type="text" id="search-input" placeholder="Search by name or email..." class="bg-slate-50 border border-slate-200 text-xs font-semibold text-brand-dark px-3 py-2 rounded-xl focus:outline-none focus:border-brand-pink w-64 placeholder:text-slate-400 dark:bg-gray-900 dark:border-gray-700 dark:text-white dark:placeholder:text-gray-400">
                </div>
                <button onclick="openAddModal()" class="px-4 py-2.5 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)] flex items-center justify-center gap-2 shrink-0">
                    <i class="fa-solid fa-user-plus text-[10px]"></i> Add New User
                </button>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden dark:bg-gray-900 dark:border-gray-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200/50 text-[11px] font-bold uppercase tracking-wider text-brand-muted dark:bg-gray-950">
                                <th class="py-3 px-3 sm:py-4 sm:px-6">#</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Name</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Email</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Phone</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6">Registered Date</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-semibold text-brand-dark dark:text-gray-300 dark:divide-gray-800">
                            <?php if (count($users) > 0): ?>
                                <?php $i = $offset + 1; ?>
                                <?php foreach ($users as $u): ?>
                                <tr class="hover:bg-slate-50/60 transition-colors group">
                                    <td class="py-3 px-3 sm:py-4 sm:px-6 text-brand-muted dark:text-gray-400"><?= $i++ ?></td>
                                    <td class="py-3 px-3 sm:py-4 sm:px-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-xl bg-brand-lightPink text-brand-pink flex items-center justify-center text-xs font-bold border border-pink-100">
                                                <?= strtoupper(substr($u['name'], 0, 2)) ?>
                                            </div>
                                            <span class="font-bold text-brand-dark block group-hover:text-brand-pink transition-colors dark:text-gray-100"><?= htmlspecialchars($u['name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 sm:py-4 sm:px-6 text-brand-muted dark:text-gray-400"><?= htmlspecialchars($u['email']) ?></td>
                                    <td class="py-3 px-3 sm:py-4 sm:px-6 text-brand-muted dark:text-gray-400"><?= htmlspecialchars($u['phone']) ?></td>
                                    <td class="py-3 px-3 sm:py-4 sm:px-6 text-brand-muted dark:text-gray-400 text-center"><?= date("d M Y", strtotime($u['created_at'])) ?></td>
                                    <td class="py-3 px-3 sm:py-4 sm:px-6 text-right space-x-1 whitespace-nowrap">
                                        
                                        <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Delete this user?')" class="p-1.5 bg-slate-50 hover:bg-red-50 text-brand-muted hover:text-red-500 rounded-lg transition-colors inline-block dark:bg-gray-800 dark:text-gray-400" title="Delete"><i class="fa-regular fa-trash-can"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="py-8 text-center text-brand-muted dark:text-gray-400">No users found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="bg-slate-50/50 px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-brand-muted font-semibold dark:bg-gray-950 dark:border-gray-800 dark:text-gray-400">
                    <span>Showing <?= $offset + 1 ?>–<?= min($offset + $per_page, $total_rows) ?> of <?= $total_rows ?> entries</span>
                    <?php if ($total_pages > 1): ?>
                    <div class="flex items-center gap-1">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 hover:bg-slate-100 dark:hover:bg-gray-700 transition-colors"><i class="fa-solid fa-chevron-left text-[10px]"></i></a>
                        <?php endif; ?>
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        for ($p = $start; $p <= $end; $p++):
                        ?>
                        <a href="?page=<?= $p ?>" class="px-3 py-1.5 rounded-lg <?= $p === $page ? 'bg-brand-dark text-white' : 'bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 hover:bg-slate-100 dark:hover:bg-gray-700' ?> transition-colors"><?= $p ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 hover:bg-slate-100 dark:hover:bg-gray-700 transition-colors"><i class="fa-solid fa-chevron-right text-[10px]"></i></a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="user-modal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl relative dark:bg-gray-900">
            <button type="button" onclick="closeModal()" class="absolute top-5 right-6 text-slate-400 hover:text-brand-pink transition-colors dark:text-gray-400">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            <h3 id="modal-title" class="font-extrabold text-xl text-brand-dark mb-6 dark:text-white">Add New User</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="id" id="user-id" value="0">

                <div>
                    <label class="block text-xs font-bold text-brand-muted uppercase tracking-wider mb-1.5 dark:text-gray-400">Name</label>
                    <input type="text" name="name" id="user-name" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-brand-pink focus:bg-white transition-all dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-brand-muted uppercase tracking-wider mb-1.5 dark:text-gray-400">Email</label>
                    <input type="email" name="email" id="user-email" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-brand-pink focus:bg-white transition-all dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-brand-muted uppercase tracking-wider mb-1.5 dark:text-gray-400">Phone</label>
                    <input type="text" name="phone" id="user-phone" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-brand-pink focus:bg-white transition-all dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-brand-muted uppercase tracking-wider mb-1.5 dark:text-gray-400">
                        Password <span id="password-label" class="font-normal text-slate-400 dark:text-gray-400">(required for new users)</span>
                    </label>
                    <input type="password" name="password" id="user-password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-brand-pink focus:bg-white transition-all dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold rounded-xl transition-all shadow-[0_4px_12px_rgba(255,101,132,0.25)] dark:bg-gray-800">
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modal-title').textContent = 'Add New User';
            document.getElementById('form-action').value = 'add';
            document.getElementById('user-id').value = '0';
            document.getElementById('user-name').value = '';
            document.getElementById('user-email').value = '';
            document.getElementById('user-phone').value = '';
            document.getElementById('user-password').value = '';
            document.getElementById('user-password').required = true;
            document.getElementById('password-label').textContent = '(required)';
            document.getElementById('user-modal').classList.remove('hidden');
        }

        function openEditModal(user) {
            document.getElementById('modal-title').textContent = 'Edit User';
            document.getElementById('form-action').value = 'edit';
            document.getElementById('user-id').value = user.id;
            document.getElementById('user-name').value = user.name;
            document.getElementById('user-email').value = user.email;
            document.getElementById('user-phone').value = user.phone;
            document.getElementById('user-password').value = '';
            document.getElementById('user-password').required = false;
            document.getElementById('password-label').textContent = '(leave blank to keep current)';
            document.getElementById('user-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('user-modal').classList.add('hidden');
        }

        // Live search filter
        document.getElementById('search-input')?.addEventListener('keyup', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
