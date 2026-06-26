<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-10">

    <!-- STATE 1: REVIEW BOOKING (image_dde55a.png) -->
    <div id="step-review" class="max-w-md mx-auto bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <h2 class="font-bold mb-4">Review Your Booking</h2>
        <div class="space-y-4 mb-6">
            <div class="flex justify-between"><span>Doctor</span><span>Dr. Wai Lin</span></div>
            <div class="flex justify-between"><span>Location Type</span><span>ONLINE (Viber)</span></div>
            <div class="flex justify-between"><span>Date & Time</span><span>20 June 2025</span></div>
            <div class="flex justify-between font-bold"><span>Payment Amount</span><span>30,000 MMK</span></div>
        </div>
        <button onclick="showPayment()" class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition">Book Appointment</button>
    </div>

    <!-- STATE 2: PAYMENT & CONFIRMATION (image_dde915.png) -->
    <!-- Added id="step-payment-container" to control visibility -->
    <div id="step-payment-container" class="hidden max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Left Side: Payment Method -->
        <div id="payment-method-box" class="bg-white p-6 rounded-xl border shadow-sm">
            <h2 class="text-pink-500 font-bold mb-4">9 PAYMENT SESSION</h2>
            <div class="space-y-4">
                <label class="block p-3 border rounded cursor-pointer"><input type="radio" name="pay" checked> KBZ Pay</label>
                <label class="block p-3 border rounded cursor-pointer"><input type="radio" name="pay"> Wave Pay</label>
            </div>
            <button onclick="showConfirmation()" class="w-full mt-6 bg-pink-500 text-white py-3 rounded-lg hover:bg-pink-600 transition">Pay Now</button>
        </div>

        <!-- RIGHT SIDE: CONFIRMATION -->
        <div id="step-confirmation" class="hidden bg-green-50 p-6 rounded-xl border border-green-200">
            <h2 class="text-green-700 font-bold text-center mb-4">APPOINTMENT CONFIRMED</h2>
            <div class="text-center text-4xl mb-4">✅</div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between border-b pb-2"><span>Doctor</span> <span id="conf-doctor" class="font-medium"></span></div>
                <div class="flex justify-between border-b pb-2"><span>Location Type</span> <span id="conf-location" class="font-medium"></span></div>
                <div class="flex justify-between border-b pb-2"><span>Date & Time</span> <span id="conf-date" class="font-medium"></span></div>
                <div class="flex justify-between pb-2"><span>Amount Paid</span> <span id="conf-amount" class="font-medium"></span></div>
            </div>
            <button class="w-full mt-6 bg-green-600 text-white py-3 rounded-lg font-bold hover:bg-green-700 transition">View My Appointments</button>
        </div>
    </div>

    <script>
        function showPayment() {
            // Hide the review step
            document.getElementById('step-review').classList.add('hidden');
            // Show the payment container
            document.getElementById('step-payment-container').classList.remove('hidden');
        }

        function showConfirmation() {
            // 1. Populate the data
            document.getElementById('conf-doctor').innerText = "Dr. Wai Lin";
            document.getElementById('conf-location').innerText = "ONLINE (Viber)";
            document.getElementById('conf-date').innerText = "20 June 2025, 10:00 AM";
            document.getElementById('conf-amount').innerText = "30,000 MMK";

            // 2. Reveal the confirmation box
            document.getElementById('step-confirmation').classList.remove('hidden');
            
            // 3. Hide the payment method box
            document.getElementById('payment-method-box').classList.add('hidden');
        }
    </script>
</body>
</html>