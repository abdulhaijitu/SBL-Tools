<!-- ========================================================================= -->
<!-- NATIONAL PACKAGE COMPLIANCE & DETAILS MODAL DIALOG                        -->
<!-- role="dialog" aria-modal="true" aria-labelledby="national-modal-title"     -->
<!-- Dimensions: w-[calc(100%-24px)], max-w-[760px], max-h-[90dvh]             -->
<!-- Focus trap & Return focus handled by coordinator                          -->
<!-- ========================================================================= -->
<div 
    x-show="nationalModalOpen" 
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="national-modal-title"
    @keydown.escape.window="closeNationalModal()"
>
    <!-- Modal Backdrop -->
    <div 
        x-show="nationalModalOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click="closeNationalModal()"
        aria-hidden="true"
    ></div>

    <!-- Modal Card Container -->
    <div 
        x-show="nationalModalOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-[calc(100%-24px)] max-w-[760px] max-h-[90dvh] flex flex-col z-10 overflow-hidden"
        @click.stop
    >
        <!-- Modal Top Accent Ribbon -->
        <div class="sbl-ribbon" aria-hidden="true"></div>

        <!-- Sticky Modal Header -->
        <div class="flex items-center justify-between p-4 sm:p-6 border-b border-slate-100 bg-white sticky top-0 z-10 shrink-0">
            <div class="space-y-0.5">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-orange-100 text-[#C2410C]">
                        National Dropshipping
                    </span>
                    <span class="text-xs font-bold text-slate-500">100 BV</span>
                </div>
                <h3 id="national-modal-title" class="text-lg sm:text-xl font-extrabold text-slate-900">
                    National Package — আর্থিক শর্ত ও রিটার্ন পলিসি
                </h3>
            </div>
            
            <!-- Visible Close Button -->
            <button 
                type="button" 
                @click="closeNationalModal()"
                class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 flex items-center justify-center transition focus:outline-none focus:ring-2 focus:ring-[#AB2925]"
                aria-label="মোডাল বন্ধ করুন"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Scrollable Modal Body -->
        <div class="p-4 sm:p-6 overflow-y-auto space-y-6 text-slate-700 text-sm leading-relaxed">
            
            <!-- 1. Financial Breakdown Grid -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#AB2925]"></span>
                    প্যাকেজ বিনিয়োগ ও ফি কাঠামো
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-3 bg-[#FFF7F3] rounded-2xl border border-[#FDEDE7]">
                        <span class="text-xs text-slate-600 block">মূলধন বিনিয়োগ</span>
                        <span class="text-lg sm:text-xl font-black text-slate-900">৳১,০০,০০০</span>
                        <span class="text-[10px] text-[#AB2925] font-semibold block mt-0.5">সর্বোচ্চ ৪,৯০,০০০ Tk পর্যন্ত</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80">
                        <span class="text-xs text-slate-600 block">ওয়েবসাইট ডেভেলপমেন্ট ফি</span>
                        <span class="text-lg sm:text-xl font-black text-slate-900">৳২০,০০০</span>
                        <span class="text-[10px] text-slate-500 font-semibold block mt-0.5">এককালীন সেটআপ চার্জ</span>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-100">
                        <span class="text-xs text-emerald-800 block">প্রারম্ভিক মোট প্যাকেজ</span>
                        <span class="text-lg sm:text-xl font-black text-emerald-950">৳১,২০,০০০</span>
                        <span class="text-[10px] text-emerald-700 font-semibold block mt-0.5">১০০ BV বাইনারি ভলিউম</span>
                    </div>
                </div>
            </div>

            <!-- 2. Return & Earnings Schedule -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#C2410C]"></span>
                    সাপ্তাহিক রিটার্ন ও লাইফটাইম প্রফিট মডেল (লিফলেট স্টেটমেন্ট)
                </h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 bg-white rounded-xl border border-slate-200">
                        <span class="font-bold text-slate-900 block text-sm mb-1">১০০ সপ্তাহের রিটার্ন:</span>
                        <p class="text-slate-600">
                            প্রতি সপ্তাহে <strong class="text-slate-900">১.৭৫% (১৭৫০ টাকা)</strong> করে ১০০ সপ্তাহ (২৪ মাস)।
                        </p>
                        <p class="mt-1 font-bold text-[#AB2925]">
                            মোট মূলধন সহ রিটার্ন: ১,৭৫,০০০ টাকা
                        </p>
                    </div>

                    <div class="p-3 bg-white rounded-xl border border-slate-200">
                        <span class="font-bold text-slate-900 block text-sm mb-1">আজীবন মুনাফা (Lifetime Profit):</span>
                        <p class="text-slate-600">
                            ১০০ সপ্তাহ পর আর কোনো বিনিয়োগ না করে প্রতি মাসে কম-বেশি 
                            <strong class="text-slate-900">৫,০০০ থেকে ২০,০০০ টাকা</strong> পর্যন্ত মুনাফা অর্জন সম্ভব।
                        </p>
                    </div>
                </div>

                <!-- Crowdfunding Box -->
                <div class="p-3 bg-orange-50/60 rounded-xl border border-orange-100 text-xs">
                    <span class="font-bold text-orange-950">ক্রাউডফান্ডিং সুযোগ (Crowdfunding):</span>
                    <span class="text-orange-900"> ন্যাশনাল প্যাকেজের উদ্যোক্তাগণ সর্বোচ্চ <strong>১০ লাখ টাকা পর্যন্ত</strong> ব্যবসা সম্প্রসারণে ক্রাউডফান্ডিং সুবিধা পাওয়ার সুযোগ পাবেন।</span>
                </div>
            </div>

            <!-- 3. Leaflet Verbatim Quote -->
            <div class="p-3.5 bg-amber-50/70 rounded-2xl border border-amber-200/80 text-xs text-amber-950 space-y-1.5">
                <span class="font-bold uppercase tracking-wider text-amber-900 block flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-700" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    অফিসিয়াল লিফলেট বিবরণী:
                </span>
                <p class="italic leading-relaxed">
                    “১,২০,০০০ টাকা বিনিয়োগ করলে প্রতি সপ্তাহে ১৭৫০ টাকা করে ১০০ সপ্তাহ অর্থাৎ ২৪ মাসে মোট মূলধন সহ এক লক্ষ ৭৫ হাজার টাকা গ্যারান্টি সহকারে পাবেন। এবং পরবর্তীতে আর কোন বিনিয়োগ না করে প্রতি মাসে কম বেশি ৫,০০০ থেকে ২০,০০০ টাকা পর্যন্ত আজীবন মুনাফা অর্জন করা সম্ভব।”
                </p>
            </div>

            <!-- 4. Mandatory Disclaimer -->
            <div class="p-3.5 bg-slate-100 rounded-2xl border border-slate-200 text-xs text-slate-600 leading-relaxed">
                <span class="font-bold text-slate-800 block mb-1">প্রয়োজনীয় সতর্কতা ও শর্তাবলী:</span>
                প্যাকেজ সুবিধা, কমিশন, ড্রপশিপিং বিক্রয় ও রিটার্ন SBL-এর বর্তমান নীতিমালা, ব্যবসায়িক পারফরম্যান্স ও প্রযোজ্য শর্তসাপেক্ষ। প্রদর্শিত কোনো অঙ্ককে ব্যক্তিগত নিশ্চিত আয় বা স্থায়ী ব্যাংক গ্যারান্টি হিসেবে বিবেচনা করবেন না।
            </div>

            <!-- 5. Secondary Brand / Leaflet Photo Context -->
            <div class="space-y-2 pt-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block">
                    অফিসিয়াল লিফলেট ও রেফারেন্স
                </span>
                <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-50">
                    <img 
                        src="{{ asset('images/sbl/sbl-office-leaflet.jpg') }}" 
                        alt="SBL Dropshipping Package Official Leaflet" 
                        loading="lazy"
                        width="700"
                        height="420"
                        class="w-full h-auto object-cover max-h-56"
                    >
                </div>
            </div>

        </div>

        <!-- Sticky Modal Footer -->
        <div class="p-4 sm:p-6 border-t border-slate-100 bg-slate-50/80 sticky bottom-0 z-10 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
            <button 
                type="button" 
                @click="openCalculator('national', 120000); closeNationalModal()"
                class="w-full sm:w-auto px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs sm:text-sm font-bold rounded-xl transition flex items-center justify-center gap-1.5"
            >
                <span>🧮 ক্যালকুলেটরে National হিসাব দেখুন</span>
            </button>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button 
                    type="button" 
                    @click="closeNationalModal()"
                    class="w-1/2 sm:w-auto px-4 py-2.5 bg-white hover:bg-slate-100 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl border border-slate-300 transition text-center"
                >
                    <span>বন্ধ করুন</span>
                </button>

                <a 
                    href="https://shoplogistbd.com" 
                    target="_blank" 
                    rel="noopener"
                    @click="trackJoin('national', 'modal')"
                    class="w-1/2 sm:w-auto btn-sbl-primary text-xs sm:text-sm font-bold text-center justify-center"
                >
                    <span>শুরু করুন</span>
                </a>
            </div>
        </div>

    </div>
</div>