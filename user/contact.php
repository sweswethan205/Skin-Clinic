<?php include '../includes/header.php'; ?>
    


<!-- PREMIUM CONTACT US SECTION -->
    <section class="max-w-7xl mx-auto px-6 py-20 bg-white">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-0 bg-white rounded-3xl overflow-hidden shadow-2xl shadow-pink-100/30 border border-pink-100/40">
            
            <!-- LEFT COLUMN: Beautiful Visual Portrait -->
            <div class="lg:col-span-6 relative bg-brand-lightPink min-h-[450px] lg:min-h-[680px] flex flex-col justify-between">
                <!-- Overlay content that displays safely on top -->
                <div class="p-12 z-10 space-y-6 relative">
                    <!-- Top Brand Header -->
                    <div class="flex items-center space-x-2">
                        <span class="font-serif tracking-wide text-xs uppercase font-semibold text-brand-dark">Glow Skin Clinic</span>
                    </div>

                    <!-- Mid Hero Text Overlay -->
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

                <!-- LIVE ONLINE IMAGE (Guaranteed to load instantly) -->
                <div class="absolute inset-0 w-full h-full z-0">
                    <img src="https://images.unsplash.com/photo-1614859324967-bdf461fcf769?auto=format&fit=crop&w=800&q=80" alt="GlowSkin Natural Portrait Model" class="w-full h-full object-cover object-center">
                </div>
                <!-- Soft gradient overlay for text readability -->
                <div class="absolute inset-0 bg-gradient-to-r from-brand-lightPink/60 via-transparent to-transparent z-5 pointer-events-none"></div>
            </div>

            <!-- RIGHT COLUMN: Premium Structured Form Panel -->
            <div class="lg:col-span-6 p-8 md:p-14 flex flex-col justify-between bg-white z-10">
                <div class="text-center max-w-md mx-auto w-full">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-brand-pink block mb-2">Contact Us</span>
                    <h3 class="font-serif text-2xl md:text-3xl text-brand-dark font-medium tracking-tight">We'd Love to Hear From You</h3>
                    <p class="text-[11px] text-brand-textMuted mt-3 font-light leading-relaxed">
                        Have questions or want to book a consultation?<br>Fill out the form below and our team will get back to you.
                    </p>
                    
                    <!-- Decorative Clinic Divider Symbol -->
                    <div class="flex items-center justify-center space-x-3 my-5">
                        <div class="w-8 h-[1px] bg-pink-100"></div>
                        <div class="w-2 h-2 rounded-full bg-brand-pink/40"></div>
                        <div class="w-8 h-[1px] bg-pink-100"></div>
                    </div>
                </div>

                <!-- Functional Premium Form -->
                <form class="space-y-3.5 max-w-md mx-auto w-full" onsubmit="event.preventDefault();">
                    <!-- Name Input -->
                    <div class="relative flex items-center">
                        <input type="text" placeholder="Your Name" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 transition-all font-light text-brand-dark">
                    </div>

                    <!-- Email Input -->
                    <div class="relative flex items-center">
                        <input type="email" placeholder="Email Address" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 transition-all font-light text-brand-dark">
                    </div>

                    <!-- Phone Number Input -->
                    <div class="relative flex items-center">
                        <input type="tel" placeholder="Phone Number" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 transition-all font-light text-brand-dark">
                    </div>

                    <!-- Preferred Date Input -->
                    <div class="relative flex items-center">
                        <input type="text" placeholder="Preferred Date (Optional)" onfocus="(this.type='date')" onblur="(this.type='text')" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 transition-all font-light text-brand-textMuted">
                    </div>

                    <!-- Message Textarea -->
                    <div class="relative flex items-start">
                        <textarea rows="4" placeholder="Your Message" class="w-full text-xs px-4 py-3.5 bg-brand-lightPink/10 border-2 border-pink-100/60 rounded-lg placeholder-gray-400 outline-none focus:outline-none focus:border-brand-pink focus:ring-1 focus:ring-brand-pink/30 resize-none transition-all font-light text-brand-dark"></textarea>
                    </div>

                    <!-- Luxury Submission Action Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-brand-pink hover:bg-opacity-90 text-white text-xs font-semibold tracking-widest uppercase py-4 rounded-lg shadow-md shadow-pink-400/10 transition-all duration-300 transform active:scale-[0.99]">
                            Send Message
                        </button>
                    </div>
                </form>

                <!-- Bottom Footer Metadata Anchor Grid -->
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