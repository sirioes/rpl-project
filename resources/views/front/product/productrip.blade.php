<section class="w-full bg-white py-12 px-4 md:px-8 overflow-x-hidden">
    <div class="max-w-7xl mx-auto space-y-12">

        <div class="bg-white border border-gray-300 rounded-[20px] p-6 md:p-14 shadow-[0_4px_20px_rgba(0,0,0,0.08)]">

            <h2 class="text-2xl md:text-3xl font-extrabold text-black text-center mb-8">
                Exotic Bali Paradise Tour 5D4N
            </h2>

            <div class="flex flex-col lg:flex-row gap-8">

                {{-- KOLOM KIRI: SLIDER GAMBAR --}}
                <div class="w-full lg:w-[40%]">
                    <div class="aspect-4/5 w-full overflow-hidden rounded-2xl relative"
                        x-data="{
                            activeSlide: 0,
                            slides: [
                                'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=800&q=80',
                                'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=800&q=80',
                                'https://images.unsplash.com/photo-1555400038-63f5ba517a47?auto=format&fit=crop&w=800&q=80'
                            ],
                            init() {
                                if (this.slides.length > 1) {
                                    setInterval(() => {
                                        this.activeSlide = (this.activeSlide === this.slides.length - 1) ? 0 : this.activeSlide + 1;
                                    }, 5000); // Saya ubah jadi 5 detik untuk preview
                                }
                            }
                        }">

                        <div class="relative h-full w-full">
                            <template x-for="(image, index) in slides" :key="index">
                                <div x-show="activeSlide === index"
                                    x-transition:enter="transition duration-700 ease-out"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="transition duration-700 ease-in"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="absolute inset-0">
                                    <img :src="image"
                                        alt="Tour Image"
                                        class="w-full h-full object-cover">
                                </div>
                            </template>
                        </div>

                        <template x-if="slides.length > 1">
                            <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-2">
                                <template x-for="(image, index) in slides" :key="index">
                                    <div :class="activeSlide === index ? 'bg-white w-5' : 'bg-white/50 w-2'"
                                        class="h-1.5 rounded-full transition-all duration-500 cursor-pointer"
                                        @click="activeSlide = index">
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- KOLOM KANAN: DETAIL & FORM BOOKING --}}
                <div class="w-full lg:w-[60%] flex flex-col justify-between">
                    <div>
                        {{-- Info Tambahan: Tanggal & Kuota --}}
                        <div class="flex flex-wrap items-center gap-3 mb-6">
                            <div class="bg-blue-50 text-[#0099FF] px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2">
                                <i class="far fa-calendar-alt"></i>
                                25 Dec 2026, 08:00
                            </div>

                            <div class="bg-orange-50 text-orange-600 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2">
                                <i class="fas fa-ticket-alt"></i>
                                15 Tickets Left
                            </div>
                        </div>

                        <p class="text-gray-700 text-justify leading-relaxed mb-6">
                            Experience the ultimate getaway with our exclusive 5 days and 4 nights tour. Explore pristine beaches, lush rice terraces, and vibrant culture. This package includes accommodation, daily breakfast, and guided tours to the most iconic spots in the region.
                        </p>

                        <div class="mb-4">
                            <p class="font-semibold text-black mb-2">Departure Locations</p>
                            <div class="prose max-w-none list-disc pl-2 trix-content text-gray-700">
                                <ul>
                                    <li>Ngurah Rai International Airport (Terminal 1)</li>
                                    <li>Sanur Port (Fast Boat Meeting Point)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- BAGIAN BAWAH: FORM PEMESANAN --}}
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <form action="#" method="GET" class="flex flex-col sm:flex-row gap-4">

                            {{-- Input Jumlah Tiket --}}
                            <div class="flex items-center justify-between sm:justify-start border-2 border-gray-200 rounded-xl px-4 py-2 bg-gray-50 focus-within:border-[#10435E] transition-colors">
                                <span class="text-[#10435E] text-lg mr-3" title="Number of People"><i class="fas fa-user-friends"></i></span>
                                <input type="number" name="quantity" min="1" max="15" value="1" required
                                    class="w-16 bg-transparent border-none focus:ring-0 text-center font-bold text-xl p-0 text-[#10435E]">
                            </div>

                            {{-- Tombol Book Now --}}
                            <button type="button" class="flex-1 bg-[#10435E] text-white text-lg font-bold py-4 px-6 rounded-xl shadow-md hover:bg-[#0d364b] transition-colors duration-300 flex justify-center items-center gap-3">
                                <span>Book Now</span>
                                <span class="text-white/30">|</span>
                                <span>€ 499.00 <span class="text-sm font-normal">/ea</span></span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>