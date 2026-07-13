<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Ensure this path is 100% correct relative to this file
include_once '../config/db.php';

// Ensure messages table exists
$conn->query("CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `subject` VARCHAR(150) DEFAULT NULL,
  `message_text` TEXT NOT NULL,
  `status` ENUM('unread', 'read') DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$notification = '';
$db_error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $preferred_date = trim($_POST['preferred_date'] ?? '');
    $message_text = trim($_POST['message_text'] ?? '');

    if (empty($name) || empty($email) || empty($message_text)) {
        $notification = 'error';
        $db_error_message = 'Name, email, and message are required.';
    } else {
        // Combining phone and date since they aren't explicitly separate columns in your schema
        $combined_message = "Phone: " . $phone . "\nPreferred Date: " . $preferred_date . "\n\nMessage:\n" . $message_text;
        $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        $subject = 'Contact Form Inquiry';

        $stmt = $conn->prepare("INSERT INTO messages (user_id, name, email, subject, message_text) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("issss", $user_id, $name, $email, $subject, $combined_message);

            // if ($stmt->execute()) {
            //     $message_id = $stmt->insert_id;
            //     $notif_title = 'Message Sent';
            //     $notif_msg = "Your contact message has been sent successfully. Our team will review and respond shortly.";
            //     $nstmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'contact')");
            //     $nstmt->bind_param("iss", $user_id, $notif_title, $notif_msg);
            //     $nstmt->execute();
            //     $nstmt->close();
            //     $_SESSION['contact_success'] = true;
            //     header('Location: contact.php');
            //     exit;
            // } 
            if ($stmt->execute()) {
                $message_id = $stmt->insert_id;
                
                if ($user_id) {
                    $notif_title = 'New Contact Message';
                    $clean_user_msg = str_replace(array("\r", "\n"), ' ', $message_text);
                    $notif_msg = $name . " sent a contact message: " . $clean_user_msg;
                    $target = 'admin';
                    $nstmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, target_role) VALUES (?, ?, ?, 'contact', ?)");
                    $nstmt->bind_param("isss", $user_id, $notif_title, $notif_msg, $target);
                    $nstmt->execute();
                    $nstmt->close();
                }
                
                $_SESSION['contact_success'] = true;
                header('Location: contact.php');
                exit;
            }
            else {
                $notification = 'error';
                $db_error_message = "Execute failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $notification = 'error';
            $db_error_message = "Prepare failed: " . $conn->error;
        }
    }
}

if (isset($_SESSION['contact_success'])) {
    $notification = 'success';
    unset($_SESSION['contact_success']);
}

include '../includes/header.php';

$prefilled_name = $_SESSION['user_name'] ?? '';
$prefilled_email = '';
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $e_query = "SELECT email FROM users WHERE id = ? LIMIT 1";
    if ($e_stmt = $conn->prepare($e_query)) {
        $e_stmt->bind_param("i", $uid);
        $e_stmt->execute();
        $e_result = $e_stmt->get_result();
        if ($e_row = $e_result->fetch_assoc()) {
            $prefilled_email = $e_row['email'];
        }
        $e_stmt->close();
    }
}
?>
    
<section class="max-w-7xl mx-auto px-6 py-20">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-0 bg-white rounded-3xl overflow-hidden shadow-2xl shadow-pink-100/30 border border-pink-100/40">
        
        <!-- Left Side Aesthetic Info Banner -->
        <div class="lg:col-span-6 relative bg-brand-lightPink min-h-[450px] lg:min-h-[680px] flex flex-col justify-between">
            <div class="p-12 z-10 space-y-6 relative">
                <div class="flex items-center space-x-2">
                    <span class="font-serif tracking-wide text-xs uppercase font-semibold text-brand-dark">Glow Skin Clinic</span>
                </div>

                <div class="space-y-3 max-w-xs pt-8">
                    <h2 class="font-serif text-4xl text-brand-dark font-normal leading-tight tracking-wide">
                        Healthy Skin, <br>Confident You
                    </h2>
                    <div class="w-12 h-[1px] bg-brand-pink my-4"></div>
                    <p class="text-xs text-brand-textMuted tracking-wide font-light leading-relaxed">
                        Professional care for your natural beauty.
                    </p>
                </div>
            </div>

            <div class="absolute inset-0 w-full h-full z-0">
                <img src="https://images.unsplash.com/photo-1614859324967-bdf461fcf769?auto=format&fit=crop&w=800&q=80" alt="GlowSkin Natural Portrait Model" class="w-full h-full object-cover object-center">
            </div>
            <div class="absolute inset-0 bg-gradient-to-r from-brand-lightPink/60 via-transparent to-transparent z-5 pointer-events-none"></div>
        </div>

        <!-- Right Side Form Controls -->
        <div class="lg:col-span-6 p-8 md:p-14 flex flex-col justify-between bg-white z-10">
            <div class="text-center max-w-md mx-auto w-full">
                <span class="text-[10px] font-bold uppercase tracking-widest text-brand-pink block mb-2">Contact Us</span>
                <h3 class="font-serif text-2xl md:text-3xl text-brand-dark font-medium tracking-tight">We'd Love to Hear From You</h3>
                <p class="text-[11px] text-brand-textMuted mt-3 font-light leading-relaxed">
                    Have questions or want to book a consultation?<br>Fill out the form below and our team will get back to you.
                </p>
                
                <div class="flex items-center justify-center space-x-3 my-5">
                    <div class="w-8 h-[1px] bg-pink-100"></div>
                    <div class="w-2 h-2 rounded-full bg-brand-pink/40"></div>
                    <div class="w-8 h-[1px] bg-pink-100"></div>
                </div>

                <!-- Notification Alerts Display -->
                <?php if($notification === 'success'): ?>
                    <div class="mb-5 bg-emerald-50 text-emerald-600 p-3 rounded-lg text-xs font-medium text-center">
                        Message sent! Our clinic staff will review and contact you shortly.
                    </div>
                <?php elseif($notification === 'error'): ?>
                    <div class="mb-5 bg-rose-50 text-rose-600 p-3 rounded-lg text-xs font-medium text-center">
                        Failed to save message.<br>
                        <span class="text-[10px] font-mono text-rose-500 bg-white/70 p-1.5 block mt-2 rounded border border-rose-200">
                            DB Error: <?= htmlspecialchars($db_error_message) ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <form action="contact.php" method="POST" class="space-y-3.5 max-w-md mx-auto w-full">
                <div class="relative flex items-center">
                    <input type="text" name="name" required value="<?= htmlspecialchars($prefilled_name) ?>" placeholder="Your Name" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 transition-all font-light text-brand-dark">
                </div>

                <div class="relative flex items-center">
                    <input type="email" name="email" required value="<?= htmlspecialchars($prefilled_email) ?>" placeholder="Email Address" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 transition-all font-light text-brand-dark">
                </div>

                <div class="relative flex items-center">
                    <input type="tel" name="phone" required placeholder="Phone Number" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 transition-all font-light text-brand-dark">
                </div>

                <div class="relative flex items-center">
                    <input type="text" name="preferred_date" placeholder="Preferred Date (Optional)" onfocus="(this.type='date')" onblur="(this.type='text')" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 transition-all font-light text-brand-textMuted">
                </div>

                <div class="relative flex items-start">
                    <textarea name="message_text" rows="4" required placeholder="Your Message" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 resize-none transition-all font-light text-brand-dark"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-brand-pink hover:bg-opacity-90 text-white text-xs font-semibold tracking-widest uppercase py-4 rounded-lg shadow-md shadow-pink-400/10 transition-all duration-300 transform active:scale-[0.99]">
                        Send Message
                    </button>
                </div>
            </form>

            <div class="border-t border-pink-100/50 mt-8 pt-5 flex flex-wrap justify-center items-center gap-x-6 gap-y-2 text-[10px] text-brand-textMuted font-light max-w-md mx-auto w-full">
                <div>+95 9 123 456 789</div>
                <div class="text-pink-100">|</div>
                <div>info@glowskinclinic.com</div>
                <div class="text-pink-100">|</div>
                <div>Yangon, Myanmar</div>
            </div>
        </div>

    </div>    
</section>

<?php include '../includes/footer.php'; ?>