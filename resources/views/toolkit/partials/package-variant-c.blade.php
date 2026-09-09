<!-- ========================================================================= -->
<!-- VARIANT C: LONG DETAILS PANEL                                             -->
<!-- Large inline section with accessible mobile disclosure/accordions         -->
<!-- ========================================================================= -->
<article 
    class="bg-white rounded-2xl sm:rounded-3xl border border-[#E5E7EB] shadow-xs overflow-hidden max-w-4xl mx-auto text-[#111827]"
    aria-labelledby="pkg-title-c"
>
    <!-- Top Accent Ribbon -->
    <div class="sbl-ribbon" aria-hidden="true"></div>

    <div class="p-5 sm:p-8 md:p-10 space-y-6 sm:space-y-8">
        
        <!-- Package Summary Header -->
        <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold tracking-wide uppercase bg-[#FDEDE7] text-[#AB2925] border border-[#FDEDE7]">
                        STARTER MEMBERSHIP
                    </span>
                    <span class="text-xs text-slate-500 font-semibold">Entry-Level Tier</span>
                </div>
                <h3 id="pkg-title-c" class="text-2xl sm:text-3xl font-extrabold text-[#111827] tracking-tight">
                    SBL Membership Package
                </h3>
                <p class="text-sm font-semibold text-[#C2410C]">
                    SBL Ecosystem-এ আপনার শুরু
                </p>
                <p class="text-xs sm:text-sm text-[#374151] max-w-xl leading-relaxed pt-1">
                    SBL Ecosystem-এর Affiliate ও Network কার্যক্রম শুরু করার জন্য entry-level membership package।
                </p>
            </div>

            <!-- Price Box -->
            <div class="p-4 bg-[#FFF7F3] rounded-2xl border border-[#FDEDE7] text-left sm:text-right shrink-0">
                <div class="text-3xl sm:text-4xl font-extrabold text-[#111827] tracking-tight">৳১০,০০০</div>
                <div class="text-xs font-bold uppercase tracking-wider text-[#AB2925] mt-0.5">Membership Activation</div>
            </div>
        </header>

        <!-- Quick Facts Strip -->
        <section aria-label="Quick Facts" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Model</div>
                <div class="text-xs sm:text-sm font-bold text-slate-900 mt-0.5">Affiliate & Network</div>
            </div>
            <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Setup Support</div>
                <div class="text-xs sm:text-sm font-bold text-slate-900 mt-0.5">Page & Account</div>
            </div>
            <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Spot Commission</div>
                <div class="text-xs sm:text-sm font-bold text-slate-900 mt-0.5">10% Direct</div>
            </div>
            <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">UDR Bonus</div>
                <div class="text-xs sm:text-sm font-bold text-slate-900 mt-0.5">5% Eligibility</div>
            </div>
        </section>

        <!-- Subsection 1: Core Feature List (Mobile Disclosure) -->
        <section class="border border-[#E5E7EB] rounded-2xl overflow-hidden bg-white">
            <button 
                type="button" 
                class="w-full p-4 sm:p-5 flex items-center justify-between text-left font-bold text-[#111827] bg-slate-50 hover:bg-slate-100 transition sm:cursor-default"
                @click="toggleAccordion('features')"
                :aria-expanded="activeAccordion === 'features'"
                aria-controls="panel-features"
            >
                <span class="flex items-center gap-2 text-sm sm:text-base">
                    <span class="w-6 h-6 rounded-full bg-[#FDEDE7] text-[#AB2925] flex items-center justify-center text-xs font-bold" aria-hidden="true">1</span>
                    <span>Core Feature List (অন্তর্ভুক্ত সুবিধাসমূহ)</span>
                </span>
                <svg class="w-5 h-5 text-slate-500 sm:hidden transition-transform" :class="{'rotate-180': activeAccordion === 'features'}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div id="panel-features" class="p-4 sm:p-6" x-show="activeAccordion === 'features' || window.innerWidth >= 640">
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3" role="list">
                    <li class="flex items-center gap-2.5 text-xs sm:text-sm text-[#374151]">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Facebook Page Setup Support</span>
                    </li>
                    <li class="flex items-center gap-2.5 text-xs sm:text-sm text-[#374151]">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Affiliate Account Setup</span>
                    </li>
                    <li class="flex items-center gap-2.5 text-xs sm:text-sm text-[#374151]">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Unlimited Sponsor সুবিধা</span>
                    </li>
                    <li class="flex items-center gap-2.5 text-xs sm:text-sm text-[#374151]">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Content Support</span>
                    </li>
                    <li class="flex items-center gap-2.5 text-xs sm:text-sm text-[#374151]">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Spot Commission Plan Access</span>
                    </li>
                    <li class="flex items-center gap-2.5 text-xs sm:text-sm text-[#374151]">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Referral Return Plan Access</span>
                    </li>
                    <li class="flex items-center gap-2.5 text-xs sm:text-sm text-[#374151]">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Pair Reward ও UDR Eligibility</span>
                    </li>
                    <li class="flex items-center gap-2.5 text-xs sm:text-sm text-[#374151]">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Rank Reward Plan Access</span>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Subsection 2: Marketing Plan Details (Mobile Disclosure) -->
        <section class="border border-[#E5E7EB] rounded-2xl overflow-hidden bg-white">
            <button 
                type="button" 
                class="w-full p-4 sm:p-5 flex items-center justify-between text-left font-bold text-[#111827] bg-slate-50 hover:bg-slate-100 transition sm:cursor-default"
                @click="toggleAccordion('marketing')"
                :aria-expanded="activeAccordion === 'marketing'"
                aria-controls="panel-marketing"
            >
                <span class="flex items-center gap-2 text-sm sm:text-base">
                    <span class="w-6 h-6 rounded-full bg-[#FDEDE7] text-[#AB2925] flex items-center justify-center text-xs font-bold" aria-hidden="true">2</span>
                    <span>Marketing Plan Details (কমিশন ও ইনসেনটিভ মডেল)</span>
                </span>
                <svg class="w-5 h-5 text-slate-500 sm:hidden transition-transform" :class="{'rotate-180': activeAccordion === 'marketing'}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div id="panel-marketing" class="p-4 sm:p-6" x-show="activeAccordion === 'marketing' || window.innerWidth >= 640">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-xs font-bold text-slate-500 uppercase">Spot Commission</span>
                        <p class="text-sm font-bold text-[#111827] mt-1">10%</p>
                        <span class="text-[11px] text-slate-500">Leaflet states 10% direct</span>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-xs font-bold text-slate-500 uppercase">Refer Return</span>
                        <p class="text-sm font-bold text-[#111827] mt-1">0.25% for 100 weeks</p>
                        <span class="text-[11px] text-slate-500">Leaflet states 0.25% weekly</span>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-xs font-bold text-slate-500 uppercase">UDR (Unity Dev)</span>
                        <p class="text-sm font-bold text-[#111827] mt-1">5%</p>
                        <span class="text-[11px] text-slate-500">Leaflet states 5% bonus</span>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-xs font-bold text-slate-500 uppercase">Pair Reward</span>
                        <p class="text-sm font-bold text-[#111827] mt-1">Applicable Plan</p>
                        <span class="text-[11px] text-slate-500">Available per applicable SBL plan</span>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 sm:col-span-2">
                        <span class="text-xs font-bold text-slate-500 uppercase">Rank Reward</span>
                        <p class="text-sm font-bold text-[#111827] mt-1">Milestone Rewards</p>
                        <span class="text-[11px] text-slate-500">Eligibility and amount according to applicable official plan</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Subsection 3: Leaflet-Stated Return, Ownership Clarification & Disclaimer -->
        <section class="border border-[#FDEDE7] rounded-2xl overflow-hidden bg-[#FFF7F3] p-5 sm:p-6 space-y-4">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-[#AB2925] text-white flex items-center justify-center text-xs font-bold" aria-hidden="true">!</span>
                <h4 class="text-sm sm:text-base font-bold text-[#AB2925]">
                    Leaflet-Stated Return ও প্রযোজ্য শর্তাবলী
                </h4>
            </div>

            <div class="p-3.5 bg-white/90 rounded-xl border border-[#FDEDE7]">
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Leaflet-Stated Return:</p>
                <p class="text-sm sm:text-base font-bold text-[#111827] mt-1">
                    “১০০ সপ্তাহে ৳১০,০০০ রিটার্ন”
                </p>
            </div>

            <!-- Ownership / Access Clarification -->
            <div class="text-xs sm:text-sm text-[#374151] leading-relaxed">
                <strong class="text-slate-900">মালিকানা ও অ্যাক্সেস স্পষ্টীকরণ:</strong> Membership-এর অধীনে তৈরি account/page-এর ownership ও access SBL-এর প্রযোজ্য official policy অনুযায়ী নির্ধারিত হবে।
            </div>

            <!-- Official Disclaimer -->
            <div class="pt-3 border-t border-[#FDEDE7] text-xs text-slate-600 leading-relaxed space-y-1">
                <strong class="text-[#AB2925] block">অফিশিয়াল ডিসক্লেইমার:</strong>
                <p>
                    “প্যাকেজ সুবিধা, কমিশন, রিওয়ার্ড ও রিটার্ন SBL-এর বর্তমান নীতিমালা, যোগ্যতা ও প্রযোজ্য শর্তসাপেক্ষ। প্রদর্শিত কোনো অঙ্ককে নিশ্চিত ব্যক্তিগত আয় হিসেবে বিবেচনা করবেন না।”
                </p>
            </div>
        </section>

        <!-- Bottom Action Strip -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-[#E5E7EB]">
            <div class="text-xs text-slate-500 text-center sm:text-left">
                অফিশিয়াল মেম্বারশিপে অ্যাক্টিভেশন নিশ্চিত করতে নিচের বাটনে ট্যাপ করুন।
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button 
                    type="button" 
                    @click="openModal('long', 'details_panel')"
                    class="btn-sbl-secondary text-xs sm:text-sm font-semibold flex-1 sm:flex-initial min-h-[44px]"
                    data-analytics-event="toolkit_package_details_click"
                    data-package-id="membership-10000"
                    data-package-name="SBL Membership Package"
                    data-package-price="10000"
                    data-component-variant="long"
                    data-cta-location="details_panel"
                    aria-label="প্যাকেজের বিস্তারিত দেখুন"
                >
                    <span>প্যাকেজের বিস্তারিত দেখুন</span>
                </button>

                <a 
                    href="https://shoplogistbd.com" 
                    target="_blank" 
                    rel="noopener"
                    @click="trackJoin('long', 'details_panel')"
                    class="btn-sbl-primary text-xs sm:text-sm font-bold flex-1 sm:flex-initial min-h-[44px]"
                    data-analytics-event="toolkit_package_join_click"
                    data-package-id="membership-10000"
                    data-package-name="SBL Membership Package"
                    data-package-price="10000"
                    data-component-variant="long"
                    data-cta-location="details_panel"
                >
                    <span>Membership শুরু করুন</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>

    </div>
</article>
