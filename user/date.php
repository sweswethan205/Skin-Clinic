<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar { box-shadow: none !important; width: 100% !important; border: 1px solid #f3f4f6 !important; }
        .flatpickr-day.selected { background: #0f3a69 !important; border-color: #0f3a69 !important; }
    </style>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { navy: '#0f3a69', green: '#22c55e' } } } } }
    </script>
</head>
<body class="bg-[#FAF9F6] font-sans p-6 md:p-20 text-gray-800">

    <div class="max-w-4xl mx-auto bg-white rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.05)] p-8 md:p-12 border border-white">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-4xl font-serif text-gray-900 mb-3">Book Your Session</h1>
            <p class="text-gray-400 text-xs tracking-[0.2em] uppercase">Dr. Wai Skin & Aesthetic Clinic</p>
        </div>

        <!-- Doctor Profile -->
        <div class="flex items-center gap-4 p-6 bg-[#FAF9F6] rounded-2xl mb-10 border border-gray-100">
            <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-xl shadow-sm border border-gray-100">👤</div>
            <div>
                <h3 class="font-bold text-gray-900">Dr. Wai Lynn Htun</h3>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Senior Specialist / Yangon Branch</p>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex gap-6 mb-8 text-[10px] font-bold uppercase tracking-widest">
            <div class="flex items-center gap-2"><div class="w-3 h-3 bg-brand-navy rounded-full"></div> Booked</div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 bg-brand-green rounded-full"></div> Available</div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Calendar -->
            <div class="border-r border-gray-100">
                <div id="calendar-widget" class="w-full"></div>
            </div>

            <!-- Time Slots -->
            <div id="time-slots-container" class="hidden animate-in fade-in zoom-in duration-300">
                <h4 class="text-xs font-bold text-gray-900 mb-6 uppercase tracking-wider">Select Available Time</h4>
                <div class="grid grid-cols-3 gap-3">
                    <!-- Example: Trigger login check on click -->
                    <button onclick="handleBooking()" class="bg-brand-green text-white text-xs py-3 rounded-xl hover:scale-105 transition-transform shadow-md shadow-green-100">09:35</button>
                    <button onclick="handleBooking()" class="bg-brand-green text-white text-xs py-3 rounded-xl hover:scale-105 transition-transform shadow-md shadow-green-100">09:40</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal (Hidden) -->
    <div id="auth-modal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full text-center">
            <h2 class="text-xl font-bold mb-4">Account Required</h2>
            <p class="text-sm text-gray-500 mb-6">Please login or register to confirm your appointment.</p>
            <div class="flex flex-col gap-3">
                <a href="../auth/login.php" class="bg-brand-navy text-white py-3 rounded-xl font-bold">Login</a>
                <a href="../auth/register.php" class="bg-gray-100 text-gray-700 py-3 rounded-xl font-bold">Register</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // 1. Initialize Calendar with today's date pre-selected
    const calendar = flatpickr("#calendar-widget", { 
        inline: true, 
        dateFormat: "Y-m-d",
        defaultDate: "today", // Auto-selects today
        minDate: "today",     // Prevents picking past dates
        onChange: function(selectedDates, dateStr) {
            checkSlots(dateStr); // Check availability whenever date changes
        }
    });

    // 2. Trigger on page load
    document.addEventListener("DOMContentLoaded", function() {
        // Automatically show slots for today
        checkSlots("today");
    });

    function checkSlots(date) {
        const container = document.getElementById('time-slots-container');
        
        // --- ADD YOUR LOGIC HERE ---
        // Example: If today is fully booked, show "No booking today"
        const isFullyBooked = false; 

        if (isFullyBooked) {
            container.innerHTML = `<p class="text-center text-red-500 font-bold p-10">No booking today</p>`;
        }
        
        container.classList.remove('hidden');
    }

        // 2. Logic to check login status
        function handleBooking() {
            // Replace 'isLoggedIn' with your actual login check (e.g., checking for a session cookie or local storage)
            const isLoggedIn = localStorage.getItem('user_token'); 

            if (isLoggedIn) {
                alert("Booking confirmed!");
            } else {
                document.getElementById('auth-modal').classList.remove('hidden');
            }
        }
    </script>
</body>
</html>