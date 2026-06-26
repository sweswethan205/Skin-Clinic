<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking | GlowSkin Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FFF5F5; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="p-4 md:p-10 flex flex-col items-center">

    <div class="max-w-2xl w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="font-serif text-3xl text-slate-800">Your Appointment</h1>
            <p class="text-slate-500 text-sm mt-1">Complete your booking details</p>
        </div>

        <!-- STATE 1: REVIEW -->
        <div id="step-review" class="bg-white p-8 rounded-[2rem] shadow-xl shadow-pink-100/50 border border-pink-50">
            <h2 class="font-serif text-xl text-slate-800 mb-6">Review Details</h2>
            <div class="space-y-4 mb-8">
                <div class="flex justify-between py-3 border-b border-slate-50 text-sm"><span class="text-slate-400">Doctor</span><span class="font-semibold text-slate-700">Dr. Wai Lin</span></div>
                <div class="flex justify-between py-3 border-b border-slate-50 text-sm"><span class="text-slate-400">Location</span><span class="font-semibold text-slate-700">ONLINE (Viber)</span></div>
                <div class="flex justify-between py-3 border-b border-slate-50 text-sm"><span class="text-slate-400">Date & Time</span><span class="font-semibold text-slate-700">20 June 2025</span></div>
                <div class="flex justify-between py-3 text-lg"><span class="text-slate-800 font-bold">Total</span><span class="text-pink-500 font-bold">30,000 MMK</span></div>
            </div>
            <button onclick="showPayment()" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-semibold hover:bg-pink-500 transition-all duration-300">
                Proceed to Payment
            </button>
        </div>

        <!-- STATE 2: PAYMENT & CONFIRMATION -->
        <div id="step-payment-container" class="hidden">
            
            <!-- Payment Section -->
            <div id="payment-method-box" class="bg-white p-8 rounded-[2rem] shadow-xl shadow-pink-100/50 border border-pink-50">
                <h2 class="font-serif text-xl text-slate-800 mb-6">Select Payment</h2>
                <div class="grid grid-cols-2 gap-4">
                    <label class="border-2 border-pink-100 p-4 rounded-2xl cursor-pointer hover:border-pink-500 transition-all">
                        <input type="radio" name="pay" checked class="accent-pink-500 mr-2"> KBZ Pay
                    </label>
                    <label class="border-2 border-pink-100 p-4 rounded-2xl cursor-pointer hover:border-pink-500 transition-all">
                        <input type="radio" name="pay" class="accent-pink-500 mr-2"> Wave Pay
                    </label>
                </div>
                <button onclick="showConfirmation()" class="w-full mt-8 bg-pink-500 text-white py-4 rounded-2xl font-semibold hover:bg-pink-600 transition-all">
                    Confirm Payment
                </button>
            </div>

            <!-- Confirmation Section -->
            <div id="step-confirmation" class="hidden bg-white p-8 rounded-[2rem] shadow-xl shadow-pink-100/50 border border-pink-50 text-center">
                <div class="w-16 h-16 bg-green-100 text-green-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-6">✓</div>
                <h2 class="font-serif text-2xl text-slate-800 mb-2">Confirmed!</h2>
                <p class="text-slate-500 text-sm mb-6">Your appointment is booked successfully.</p>
                
                <div class="bg-slate-50 p-6 rounded-2xl text-left space-y-3 text-sm mb-8">
                    <div class="flex justify-between"><span class="text-slate-400">Doctor</span> <span id="conf-doctor" class="font-medium"></span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Date</span> <span id="conf-date" class="font-medium"></span></div>
                </div>

                <a href="#" class="block w-full bg-slate-900 text-white py-4 rounded-2xl font-semibold hover:bg-pink-500 transition-all">View Appointments</a>
            </div>
        </div>
    </div>

    <script>
        function showPayment() {
            document.getElementById('step-review').classList.add('hidden');
            document.getElementById('step-payment-container').classList.remove('hidden');
        }

        function showConfirmation() {
            document.getElementById('conf-doctor').innerText = "Dr. Wai Lin";
            document.getElementById('conf-date').innerText = "20 June 2025";
            document.getElementById('step-confirmation').classList.remove('hidden');
            document.getElementById('payment-method-box').classList.add('hidden');
        }
    </script>
</body>
</html>