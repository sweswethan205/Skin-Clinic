
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skin Clinic Appointment</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            pink: '#FF6584',
                            navy: '#0F3A69',
                            green: '#22C55E',
                            blue: '#3B82F6'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-[#FAF9F6] min-h-screen">

<div class="max-w-7xl mx-auto px-6 py-12">

    <div class="grid lg:grid-cols-2 gap-10">

        <!-- LEFT SIDE -->
        <div>

            <h2 class="text-3xl font-bold text-brand-navy mb-2">
                Choose Your Doctor
            </h2>

            <p class="text-gray-500 mb-8">
                Select a specialist to continue booking.
            </p>

            <div class="grid grid-cols-2 gap-5">

                <!-- Doctor 1 -->
                <button
                    onclick="selectDoctor(
                    'https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=500&q=80',
                    'Dr. Sophia Martinez',
                    'Lead Dermatologist'
                    )"
                    class="group relative h-72 rounded-[30px] overflow-hidden shadow-xl">

                    <img
                        src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=500&q=80"
                        class="w-full h-full object-cover group-hover:scale-110 transition duration-500">

                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>

                    <div class="absolute bottom-0 left-0 p-5">
                        <h3 class="text-white font-bold text-lg">
                            Dr. Sophia Martinez
                        </h3>
                        <p class="text-gray-200 text-sm">
                            Lead Dermatologist
                        </p>
                    </div>
                </button>

                <!-- Doctor 2 -->
                <button
                    onclick="selectDoctor(
                    'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=500&q=80',
                    'Dr. Julian Thorne',
                    'Aesthetic Specialist'
                    )"
                    class="group relative h-72 rounded-[30px] overflow-hidden shadow-xl">

                    <img
                        src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=500&q=80"
                        class="w-full h-full object-cover group-hover:scale-110 transition duration-500">

                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>

                    <div class="absolute bottom-0 left-0 p-5">
                        <h3 class="text-white font-bold text-lg">
                            Dr. Julian Thorne
                        </h3>
                        <p class="text-gray-200 text-sm">
                            Aesthetic Specialist
                        </p>
                    </div>
                </button>

                <!-- Doctor 3 -->
                <button
                    onclick="selectDoctor(
                    'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=500&q=80',
                    'Dr. Amara Okafor',
                    'Cosmetic Expert'
                    )"
                    class="group relative h-72 rounded-[30px] overflow-hidden shadow-xl">

                    <img
                        src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=500&q=80"
                        class="w-full h-full object-cover group-hover:scale-110 transition duration-500">

                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>

                    <div class="absolute bottom-0 left-0 p-5">
                        <h3 class="text-white font-bold text-lg">
                            Dr. Amara Okafor
                        </h3>
                        <p class="text-gray-200 text-sm">
                            Cosmetic Expert
                        </p>
                    </div>
                </button>

                <!-- Doctor 4 -->
                <button
                    onclick="selectDoctor(
                    'https://images.unsplash.com/photo-1622253692010-339f2dd650d5?auto=format&fit=crop&w=500&q=80',
                    'Dr. Marcus Chen',
                    'Skin Surgeon'
                    )"
                    class="group relative h-72 rounded-[30px] overflow-hidden shadow-xl">

                    <img
                        src="https://images.unsplash.com/photo-1622253692010-339f2dd650d5?auto=format&fit=crop&w=500&q=80"
                        class="w-full h-full object-cover group-hover:scale-110 transition duration-500">

                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>

                    <div class="absolute bottom-0 left-0 p-5">
                        <h3 class="text-white font-bold text-lg">
                            Dr. Marcus Chen
                        </h3>
                        <p class="text-gray-200 text-sm">
                            Skin Surgeon
                        </p>
                    </div>
                </button>

            </div>

        </div>

        <!-- RIGHT SIDE -->
        <div class="bg-white rounded-[35px] p-8 shadow-lg border border-gray-100">

            <h2 class="text-2xl font-bold text-brand-navy mb-6">
                Appointment Details
            </h2>

            <!-- Selected Doctor -->
            <div class="flex items-center gap-4 p-5 bg-[#FAF9F6] rounded-3xl mb-8">

                <img
                    id="doctor-image"
                    src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                    class="w-16 h-16 rounded-full object-cover border-4 border-white">

                <div>
                    <h3 id="display-name"
                        class="font-bold text-lg">
                        Select Doctor
                    </h3>

                    <p id="display-title"
                       class="text-gray-500 text-sm">
                        Choose your specialist
                    </p>
                </div>

            </div>

            <!-- Calendar -->
            <div id="calendar-widget"></div>

            <!-- Time Slots -->
            <div id="time-slots-container" class="hidden mt-8">

                <h3 class="font-bold text-lg mb-4">
                    Available Time Slots
                </h3>

                <div class="grid grid-cols-3 gap-3">

                    <button class="bg-green-500 text-white rounded-2xl py-3 font-semibold">
                        09:00
                    </button>

                    <button class="bg-green-500 text-white rounded-2xl py-3 font-semibold">
                        09:30
                    </button>

                    <button disabled
                        class="bg-blue-500 text-white rounded-2xl py-3 opacity-70 cursor-not-allowed">
                        10:00
                    </button>

                    <button disabled
                        class="bg-blue-500 text-white rounded-2xl py-3 opacity-70 cursor-not-allowed">
                        10:30
                    </button>

                    <button class="bg-green-500 text-white rounded-2xl py-3 font-semibold">
                        11:00
                    </button>

                    <button class="bg-green-500 text-white rounded-2xl py-3 font-semibold">
                        11:30
                    </button>

                </div>

                <div class="flex gap-6 mt-6">

                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded bg-green-500"></span>
                        <span class="text-sm">Available</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded bg-blue-500"></span>
                        <span class="text-sm">Booked</span>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>

function selectDoctor(image,name,title){

    document.getElementById("doctor-image").src=image;

    document.getElementById("display-name").innerText=name;

    document.getElementById("display-title").innerText=title;

    document.getElementById("calendar-widget")
            .scrollIntoView({behavior:'smooth'});
}

flatpickr("#calendar-widget",{
    inline:true,
    dateFormat:"Y-m-d",
    defaultDate:"today",
    minDate:"today",

    onChange:function(){

        document
            .getElementById("time-slots-container")
            .classList.remove("hidden");
    }
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

</script>

</body>
</html>

