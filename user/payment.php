<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - GlowSkin Skin Clinic</title>
    <!-- Tailwind CSS & Google Fonts Dependencies -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            pink: '#FF6584',
                            lightPink: '#FFF0F2',
                            dark: '#2D2D2D',
                            textMuted: '#666666'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#FAF9F6] font-sans text-brand-dark antialiased min-h-screen flex flex-col justify-between relative">
    <?php include '../includes/header.php'; ?>

    <!-- TOP HEADER / BACK NAV -->
    <header class="bg-white border-b border-gray-100 py-4 px-6 shadow-xs">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="doctor-and-date.html" class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-brand-textMuted hover:text-brand-pink transition">
                <i class="fa-solid fa-arrow-left"></i> Back to Schedule
            </a>
            <div class="text-xs font-medium text-brand-textMuted tracking-wide">
                Step <span class="text-brand-pink font-bold">2</span> of 3
            </div>
        </div>
    </header>

    <!-- MAIN BODY SECTION -->
    <main class="max-w-6xl mx-auto w-full px-6 py-12 flex-grow">
        <div class="mb-10">
            <h1 class="font-serif text-3xl md:text-4xl font-bold tracking-tight mb-2">Secure Payment Method</h1>
            <p class="text-sm text-brand-textMuted">Choose your preferred transaction system to securely hold your clinical session slot.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT PANEL: PAYMENT SYSTEM METHOD CHOOSING -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- SUMMARY COMPONENT CONTAINER -->
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-[0_10px_30px_rgba(0,0,0,0.01)] relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-brand-lightPink text-brand-pink text-[10px] font-bold uppercase tracking-widest px-4 py-1.5 rounded-bl-xl border-l border-b border-pink-100/30">
                        Confirmed Slot
                    </div>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-brand-pink mb-4">Booking Summary</h2>
                    
                    <div class="grid grid-cols-2 gap-y-4 gap-x-2">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Selected Treatment</span>
                            <span class="text-sm font-semibold text-brand-dark">Hydra Facial Elite</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Assigned Doctor</span>
                            <span class="text-sm font-semibold text-brand-dark">Dr. Elena Rodriguez</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Date & Timestamp</span>
                            <span class="text-xs font-medium text-brand-textMuted">Oct 24, 2026 • 10:30 AM</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Total Value</span>
                            <span class="text-sm font-bold text-brand-pink">$120.00</span>
                        </div>
                    </div>
                </div>

                <!-- SELECTION OPTION BUTTONS GRID WITH REAL IMAGES -->
               <div>
                    <label class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4 block">Payment Method</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="payment-methods-container">
                        
                        <!-- KBZPay (Active by Default) -->
                        <label class="payment-method-card flex items-center gap-4 p-5 bg-white border-2 border-brand-pink rounded-xl cursor-pointer transition-all duration-200 group">
                            <input type="radio" name="payment_method" value="kbz_pay" checked class="hidden">
                            <!-- Simulated KBZ Logo Panel -->
                            <div class="w-12 h-12 rounded-lg bg-[#005BAa] text-white flex flex-col items-center justify-center font-bold text-[11px] font-sans shrink-0 shadow-xs">
                                <span class="text-[8px] font-medium tracking-tighter opacity-90 leading-none">KBZ</span>
                                <span class="text-white font-extrabold text-xs -mt-0.5">Pay</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-700 tracking-wide transition-colors">KBZPay</span>
                        </label>

                        <!-- WavePay -->
                        <label class="payment-method-card flex items-center gap-4 p-5 bg-white border border-gray-200/80 hover:border-pink-200 rounded-xl cursor-pointer transition-all duration-200 group">
                            <input type="radio" name="payment_method" value="wave_pay" class="hidden">
                            <!-- Simulated Wave Logo Panel -->
                            <div class="w-12 h-12 rounded-lg bg-[#F9CC1A] flex items-center justify-center shrink-0 shadow-xs relative overflow-hidden">
                                <div class="w-7 h-7 rounded-full bg-[#004B93] flex items-center justify-center text-white text-[9px] font-bold font-serif">
                                    w
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-slate-600 tracking-wide transition-colors">WavePay</span>
                        </label>

                        <!-- CBPay -->
                        <label class="payment-method-card flex items-center gap-4 p-5 bg-white border border-gray-200/80 hover:border-pink-200 rounded-xl cursor-pointer transition-all duration-200 group">
                            <input type="radio" name="payment_method" value="cb_pay" class="hidden">
                            <!-- Simulated CB Logo Panel -->
                            <div class="w-12 h-12 rounded-lg bg-[#006BB6] text-white flex flex-col items-center justify-center shrink-0 shadow-xs relative overflow-hidden">
                                <div class="w-8 h-4 bg-gradient-to-r from-red-500 via-yellow-400 to-green-500 absolute top-2 rounded-full opacity-75 blur-[1px]"></div>
                                <span class="font-bold text-xs tracking-tighter z-10">CBPay</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-600 tracking-wide transition-colors">CBPay</span>
                        </label>

                        <!-- TrustyPay -->
                        <label class="payment-method-card flex items-center gap-4 p-5 bg-white border border-gray-200/80 hover:border-pink-200 rounded-xl cursor-pointer transition-all duration-200 group">
                            <input type="radio" name="payment_method" value="trusty_pay" class="hidden">
                            <!-- Simulated Trusty Logo Panel -->
                            <div class="w-12 h-12 rounded-lg bg-[#5A1793] text-white flex flex-col items-center justify-center font-bold text-[9px] leading-none shrink-0 shadow-xs">
                                <span class="mb-0.5 font-light">$$$</span>
                                <span class="text-[7px] uppercase tracking-tighter opacity-80">Trusty</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-600 tracking-wide transition-colors">TrustyPay</span>
                        </label>

                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: CONTENT SUMMARY & ACTION CONTROLS -->
            <div class="lg:col-span-5 bg-white border border-gray-100 rounded-2xl p-6 shadow-[0_15px_40px_rgba(0,0,0,0.02)]">
                
                <!-- Security standard note banner -->
                <div class="flex items-center gap-3 bg-[#FAFAF8] rounded-xl p-3 border border-gray-100 mb-6">
                    <div class="text-emerald-500 bg-emerald-50 w-8 h-8 rounded-lg flex items-center justify-center text-xs">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-brand-dark">Secure Local Gateway</h4>
                        <p class="text-[10px] text-gray-400">Your payments are fully protected and encrypted.</p>
                    </div>
                </div>

                <!-- Input Interactive Form Elements -->
                <form class="space-y-4" id="payment-form">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-brand-textMuted mb-1.5">Account / Phone Number</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400"><i class="fa-solid fa-mobile-screen-button text-xs"></i></span>
                            <input type="text" required placeholder="09 XXXXXXXXX" class="w-full bg-white border border-gray-200 text-sm font-medium pl-10 pr-4 py-3 rounded-xl focus:outline-none focus:ring-1 focus:ring-brand-pink focus:border-brand-pink transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-brand-textMuted mb-1.5">Account Name</label>
                        <input type="text" required placeholder="Your Name" class="w-full bg-white border border-gray-200 text-sm font-medium px-4 py-3 rounded-xl focus:outline-none focus:ring-1 focus:ring-brand-pink focus:border-brand-pink transition">
                    </div>
                    <!-- Future Settings Checkbox Wrapper -->
                    <label class="flex items-start gap-2.5 pt-2 cursor-pointer group">
                        <input type="checkbox" class="accent-brand-pink rounded mt-0.5 w-3.5 h-3.5">
                        <span class="text-[11px] text-brand-textMuted leading-tight group-hover:text-brand-dark transition">Save this account detail safely for future fast booking steps.</span>
                    </label>

                    <!-- Financial Dividers -->
                    <div class="border-t border-gray-100 pt-5 mt-6 space-y-2.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-brand-textMuted font-light">Consultation Base Fee</span>
                            <span class="font-medium text-brand-dark">$120.00</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-brand-textMuted font-light">Service Processing Surcharge</span>
                            <span class="font-medium text-emerald-500">FREE</span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-dashed border-gray-100">
                            <span class="font-serif text-sm font-bold text-brand-dark">Total Net Payable</span>
                            <span class="text-xl font-bold text-brand-pink">$120.00</span>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS GRID (Confirm Payment & Cancel) -->
                    <div class="space-y-2 pt-4">
                        <!-- Confirm Payment Button -->
                        <button type="submit" class="w-full bg-brand-pink text-white text-xs font-semibold tracking-wider uppercase py-4 rounded-xl shadow-md shadow-pink-100 hover:bg-opacity-95 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-check text-xs"></i> Confirm Payment
                        </button>
                        
                        <!-- Cancel Button (Links to doctor and date page) -->
                        <a href="doc.php" class="w-full bg-white border border-gray-200 hover:border-gray-300 text-brand-textMuted hover:text-brand-dark text-xs font-semibold tracking-wider uppercase py-3.5 rounded-xl transition-all flex items-center justify-center gap-2">
                            Cancel Process
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </main>

    <!-- MODERN UI SUCCESS DIALOG BOX / MODAL MODULAR OVERLAY -->
    <div id="success-modal" class="fixed inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center z-50 p-4 opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl transform scale-95 transition-transform duration-300">
            <!-- Animated Green Check Circle Icon -->
            <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 border border-emerald-100">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            
            <!-- Modal Header Title & Message Description -->
            <h3 class="font-serif text-2xl font-bold text-brand-dark mb-2">Booking Successful!</h3>
            <p class="text-xs text-brand-textMuted mb-6 leading-relaxed">Your professional clinic treatment session has been locked. Check your email for appointment notification receipts.</p>
            
            <!-- Dismiss Button Action -->
            <button onclick="closeModal()" class="w-full bg-brand-pink hover:bg-opacity-95 text-white text-xs font-bold tracking-wider uppercase py-3.5 rounded-xl transition">
                Perfect, Thank You
            </button>
        </div>
    </div>

    <!-- SYSTEM GLOBAL CORE FOOTER -->
    <footer class="bg-white border-t border-gray-100 py-4 text-center text-[11px] text-gray-400">
        &copy; 2026 GlowSkin Skin Clinic. All encrypted portals secure.
    </footer>

    <!-- INTERACTIVE SCRIPTS -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.payment-method-card');
        const paymentForm = document.getElementById('payment-form');
        const modal = document.getElementById('success-modal');
        const modalContent = modal.querySelector('.transform');

        // 1. Interactive Border Selection Handler
        cards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            
            card.addEventListener('click', () => {
                // Reset all cards back to non-selected default appearance
                cards.forEach(c => {
                    c.className = "payment-method-card flex items-center gap-4 p-5 bg-white border border-gray-200/80 hover:border-pink-200 rounded-xl cursor-pointer transition-all duration-200 group";
                    const textNode = c.querySelector('span');
                    textNode.className = "text-sm font-semibold text-slate-600 tracking-wide transition-colors";
                });

                // Apply active pink border style
                card.className = "payment-method-card flex items-center gap-4 p-5 bg-white border-2 border-brand-pink rounded-xl cursor-pointer transition-all duration-200 group";
                const activeText = card.querySelector('span');
                activeText.className = "text-sm font-semibold text-slate-700 tracking-wide transition-colors";
                
                radio.checked = true;
            });
        });

        // 2. Open Success Modal UI Trigger
        paymentForm.addEventListener('submit', (event) => {
            event.preventDefault(); // Stop default refresh behaviour
            
            // Show modal cleanly with smooth CSS animations
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        });
    });

    // 3. Close Modal UI Action
    function closeModal() {
        const modal = document.getElementById('success-modal');
        const modalContent = modal.querySelector('.transform');
        
        modal.classList.add('opacity-0', 'pointer-events-none');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
    }
    </script>

</body>
</html>