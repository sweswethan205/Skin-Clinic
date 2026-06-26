<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Skin Clinic Booking</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            pink: '#E85D75',
                            navy: '#1F2937',
                            green: '#22C55E'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .flatpickr-calendar {
            box-shadow: none !important;
            width: 100% !important;
        }
    </style>

</head>

<body class="bg-[#FAF9F6] min-h-screen">
    <?php include '../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-6 py-10">

        <!-- STEPPER -->
        <div class="flex justify-center mb-12">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-brand-pink text-white flex items-center justify-center font-bold">
                    1
                </div>
                <span class="font-semibold">Doctor</span>

                <div class="w-12 h-[2px] bg-gray-300"></div>

                <div id="step2" class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                    2
                </div>
                <span>Date</span>

                <div class="w-12 h-[2px] bg-gray-300"></div>

                <div id="step3" class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                    3
                </div>
                <span>Time</span>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-10">

            <!-- LEFT -->
            <div>
                <h2 class="text-4xl font-bold text-brand-navy mb-2">Choose Your Doctor</h2>
                <p class="text-gray-500 mb-8">Select a dermatologist to continue booking.</p>

                <div class="grid grid-cols-2 gap-5">
                    <!-- Doctor 1 -->
                    <button onclick="selectDoctor('https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=500&q=80', 'Dr. Sophia Martinez', 'Lead Dermatologist')"
                        class="group relative h-56 rounded-3xl overflow-hidden shadow-lg hover:-translate-y-2 hover:shadow-2xl transition">
                        <img src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=500&q=80" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 text-white text-left">
                            <h3 class="font-bold">Dr. Sophia Martinez</h3>
                            <p class="text-sm">Lead Dermatologist</p>
                        </div>
                    </button>

                    <!-- Doctor 2 -->
                    <button onclick="selectDoctor('https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=500&q=80', 'Dr. Julian Thorne', 'Aesthetic Specialist')"
                        class="group relative h-56 rounded-3xl overflow-hidden shadow-lg hover:-translate-y-2 hover:shadow-2xl transition">
                        <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=500&q=80" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 text-white text-left">
                            <h3 class="font-bold">Dr. Julian Thorne</h3>
                            <p class="text-sm">Aesthetic Specialist</p>
                        </div>
                    </button>

                    <!-- Doctor 3 -->
                    <button onclick="selectDoctor('https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=500&q=80', 'Dr. Amara Okafor', 'Cosmetic Expert')"
                        class="group relative h-56 rounded-3xl overflow-hidden shadow-lg hover:-translate-y-2 hover:shadow-2xl transition">
                        <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=500&q=80" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 text-white text-left">
                            <h3 class="font-bold">Dr. Amara Okafor</h3>
                            <p class="text-sm">Cosmetic Expert</p>
                        </div>
                    </button>

                    <!-- Doctor 4 -->
                    <button onclick="selectDoctor('https://images.unsplash.com/photo-1622253692010-339f2dd650d5?auto=format&fit=crop&w=500&q=80', 'Dr. Marcus Chen', 'Skin Surgeon')"
                        class="group relative h-56 rounded-3xl overflow-hidden shadow-lg hover:-translate-y-2 hover:shadow-2xl transition">
                        <img src="https://images.unsplash.com/photo-1622253692010-339f2dd650d5?auto=format&fit=crop&w=500&q=80" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 text-white text-left">
                            <h3 class="font-bold">Dr. Marcus Chen</h3>
                            <p class="text-sm">Skin Surgeon</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="bg-white rounded-[35px] p-8 shadow-lg relative">

                <h2 class="text-2xl font-bold mb-6">Appointment Details</h2>

                <div class="flex items-center gap-4 bg-[#FAF9F6] rounded-3xl p-5">
                    <img id="doctor-image" src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="w-20 h-20 rounded-full object-cover">
                    <div>
                        <h3 id="display-name" class="font-bold text-lg">Select Doctor</h3>
                        <p id="display-title" class="text-gray-500">Choose specialist</p>
                    </div>
                </div>

                <div id="calendar-section" class="hidden mt-4">
                    <h3 class="font-semibold mb-4">Choose Date</h3>
                    <div id="calendar-widget"></div>
                </div>

                <div id="time-slots-container" class="hidden mt-8">
                    <h3 class="font-semibold mb-4">Choose Time</h3>

                    <div class="grid grid-cols-3 gap-3">
                        <button onclick="handleBooking()" class="border-2 bg-green-500 rounded-xl py-3 text-white font-medium">09:00</button>
                        <button onclick="handleBooking()" class="border-2 bg-green-500 rounded-xl py-3 text-white font-medium">09:30</button>
                        <button onclick="handleBooking()" disabled class="bg-indigo-500 rounded-xl py-3 text-white font-medium">10:00</button>
                        <button onclick="handleBooking()" disabled class="bg-indigo-500 rounded-xl py-3 text-white font-medium">10:30</button>
                        <button onclick="handleBooking()" class="border-2 bg-green-500 rounded-xl py-3 text-white font-medium">11:00</button>
                        <button onclick="handleBooking()" class="border-2 bg-green-500 rounded-xl py-3 text-white font-medium">11:30</button>
                    </div>

                    <div class="flex gap-6 mt-5 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-green-500 rounded"></div>
                            Available
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-indigo-500 rounded"></div>
                            Booked
                        </div>
                    </div>
                </div>

                <!-- Login Modal -->
                <div id="auth-modal" class="fixed inset-0 bg-brand-navy/60 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
                    <div class="bg-white rounded-[2rem] p-8 max-w-sm w-full text-center shadow-2xl border border-gray-50 relative">
                        
                        <!-- Right Corner Cross Sign Button -->
                        <button onclick="closeAuthModal()" class="absolute top-5 right-6 text-gray-400 hover:text-brand-pink transition-colors duration-200 p-1 rounded-full hover:bg-gray-50" aria-label="Close modal">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>

                        <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center text-brand-pink mx-auto mb-4">
                            <i class="fa-solid fa-user-lock text-lg"></i>
                        </div>
                        <h2 class="text-xl font-serif font-bold text-brand-navy mb-2">Account Required</h2>
                        <p class="text-sm text-gray-500 mb-6">Please log in or sign up to finalize your slot verification details.</p>
                        
                        <div class="flex flex-col gap-3">
                            <a href="../auth/login.php" class="bg-brand-pink text-white py-3 rounded-xl font-semibold tracking-wide text-sm shadow-md shadow-pink-100 hover:opacity-95 transition">Login</a>
                            <a href="../auth/re.php" class="bg-gray-50 text-brand-navy py-3 rounded-xl font-semibold tracking-wide text-sm border border-gray-100 hover:bg-gray-100 transition">Register</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        function selectDoctor(image, name, title) {
            document.getElementById('doctor-image').src = image;
            document.getElementById('display-name').innerText = name;
            document.getElementById('display-title').innerText = title;

            document.getElementById('calendar-section').classList.remove('hidden');

            document.getElementById('step2').classList.remove('bg-gray-200');
            document.getElementById('step2').classList.add('bg-brand-pink', 'text-white');
        }

        flatpickr("#calendar-widget", {
            inline: true,
            minDate: "today",
            defaultDate: "today",
            onChange: function() {
                document.getElementById('time-slots-container').classList.remove('hidden');

                document.getElementById('step3').classList.remove('bg-gray-200');
                document.getElementById('step3').classList.add('bg-brand-pink', 'text-white');
            }
        });

        function handleBooking() {
            const isLoggedIn = localStorage.getItem('user_token');

            if (isLoggedIn) {
                alert("Booking confirmed!");
            } else {
                document.getElementById('auth-modal').classList.remove('hidden');
            }
        }

        // Close modal function to bring the user right back to time selections
        function closeAuthModal() {
            document.getElementById('auth-modal').classList.add('hidden');
        }
    </script>

    <?php include '../includes/footer.php'; ?>

</body>

</html>