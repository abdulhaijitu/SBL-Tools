@php
    $initialTier = in_array(request('tier'), ['all', 'starter', 'national', 'international']) ? request('tier') : 'all';
@endphp

<div 
    id="sbl-packages-system"
    class="space-y-6 sm:space-y-8"
    x-data="{
        selectedTier: @js($initialTier),
        detailsOpen: false,
        activePackage: 'national',
        shareOpen: false,
        sharePackage: 'national',
        presentationOpen: false,
        presentationIndex: 1,
        copiedToast: false,

        packages: {
            starter: {
                id: 'starter',
                name: 'Starter Membership',
                subTitle: 'SBL Membership Package',
                bengaliTitle: 'স্টার্টার মেম্বারশিপ',
                price: '৳১০,০০০',
                priceNum: 10000,
                type: 'Entry Affiliate Membership',
                badge: 'STARTER • LIFETIME',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                description: 'SBL Ecosystem-এর Affiliate ও Network কার্যক্রম শুরু করার জন্য entry-level membership package।',
                capital: 'N/A (Direct Membership Fee)',
                setupFee: 'Included',
                bv: 'Affiliate Status',
                duration: 'Lifetime',
                weeklyRate: 'N/A',
                weeklyAmount: 'N/A',
                totalPlanAmount: 'N/A',
                isInvestment: false,
                commission: '10% Spot / Direct Commission on referrals',
                highlights: [
                    'Lifetime Membership & Portal Access',
                    'Direct / Spot Commission Eligibility',
                    'Team & Network Binary Placement',
                    'Official Marketing Resources & Training'
                ],
                benefits: [
                    'Lifetime access to SBL ecosystem and member backoffice',
                    'Direct / Spot commission on all personally referred members',
                    'Full eligibility for team building and binary network placement',
                    'Access to official promotional resources, presentations & banners',
                    'Free participation in weekly coaching and skill development webinars',
                    'Instant flexibility to upgrade to Dropshipping packages anytime'
                ],
                shareSummary: 'SBL Starter Membership\nTotal: ৳10,000\nLifetime Membership & Affiliate Portal Access'
            },
            national: {
                id: 'national',
                name: 'National Dropshipping',
                subTitle: 'National Package',
                bengaliTitle: 'ন্যাশনাল ড্রপশিপিং',
                price: '৳১,২০,০০০',
                priceNum: 120000,
                type: 'Domestic E-Commerce Business',
                badge: 'POPULAR • 100 BV',
                badgeColor: 'bg-orange-50 text-[#C2410C] border-orange-200',
                description: 'Shopify ই-কমার্স স্টোর, সোর্সিং ও ডেলিভারি লজিস্টিকস সহ সম্পূর্ণ দেশীয় ড্রপশিপিং পরিচালনার ইনভেস্টমেন্ট প্যাকেজ।',
                capital: '৳১,০০,০০০',
                setupFee: '৳২০,০০০ (Website & Setup)',
                bv: '100 BV',
                duration: '100 Weeks (~24 Months)',
                weeklyRate: '1.75% / week',
                weeklyAmount: 'Approx. ৳1,750 / week',
                totalPlanAmount: '৳1,75,000 (over 100 weeks)',
                isInvestment: true,
                commission: '10% Spot Commission (৳10,000 on capital) + 100 BV',
                highlights: [
                    '100 BV (Binary Points)',
                    '100 Weeks Duration',
                    'Plan-based Weekly Return: 1.75% (~৳1,750/week)',
                    'Shopify Store & Sourcing Included'
                ],
                benefits: [
                    'Branded Shopify e-commerce website with local payment gateway',
                    'Verified local trending product sourcing with zero inventory holding',
                    'Automated packaging, courier integration & cash-on-delivery logistics',
                    'Paid digital marketing campaign setup & creative ad assets',
                    'Crowdfunding expansion opportunity up to 10 Lac BDT',
                    'Dedicated merchant dashboard with weekly plan-based payouts'
                ],
                shareSummary: 'SBL National Dropshipping Package\nTotal: ৳120,000 (৳100,000 Capital + ৳20,000 Setup)\n100 BV | 100-week plan | 1.75% Weekly Plan Rate'
            },
            international: {
                id: 'international',
                name: 'International Dropshipping',
                subTitle: 'International Package',
                bengaliTitle: 'আন্তর্জাতিক ড্রপশিপিং',
                price: '৳৫,৫০,০০০',
                priceNum: 550000,
                type: 'Global E-Commerce Enterprise',
                badge: 'ENTERPRISE • 500 BV',
                badgeColor: 'bg-amber-50 text-amber-800 border-amber-200',
                description: 'ডেডিকেটেড প্রজেক্ট টিম, গ্লোবাল শপিফাই স্টোর এবং আন্তর্জাতিক শিপিং সহ বিশ্বমানের গ্লোবাল ড্রপশিপিং প্যাকেজ।',
                capital: '৳৫,০০,০০০',
                setupFee: '৳৫০,০০০ (Website, Content & Setup)',
                bv: '500 BV',
                duration: '100 Weeks (~24 Months)',
                weeklyRate: '2.0% / week',
                weeklyAmount: 'Approx. ৳10,000 / week',
                totalPlanAmount: '৳10,00,000 (over 100 weeks)',
                isInvestment: true,
                commission: '10% Spot Commission (৳50,000 on capital) + 500 BV',
                highlights: [
                    '500 BV (Binary Points)',
                    '100 Weeks Duration',
                    'Plan-based Weekly Return: 2.0% (~৳10,000/week)',
                    'Global Shopify Store & Dedicated Team'
                ],
                benefits: [
                    'Global multi-currency Shopify e-commerce store (USD / EUR / GBP)',
                    'Worldwide product sourcing, supplier vetting & quality assurance',
                    'International fulfillment, express delivery & global warehousing',
                    'High-converting UGC video creatives & global ad campaigns',
                    'Dedicated project manager & full-time account management team',
                    'Crowdfunding expansion opportunity up to 50 Lac BDT'
                ],
                shareSummary: 'SBL International Dropshipping Package\nTotal: ৳550,000 (৳500,000 Capital + ৳50,000 Setup)\n500 BV | 100-week plan | 2.0% Weekly Plan Rate'
            }
        },

        openDetails(pkgKey) {
            this.activePackage = pkgKey;
            this.detailsOpen = true;
        },

        openShare(pkgKey) {
            this.sharePackage = pkgKey;
            this.shareOpen = true;
        },

        openPresentation(index = 0) {
            this.presentationIndex = index;
            this.presentationOpen = true;
        },

        nextPresentation() {
            this.presentationIndex = (this.presentationIndex + 1) % 3;
        },

        prevPresentation() {
            this.presentationIndex = (this.presentationIndex + 2) % 3;
        },

        get currentPresentationPkg() {
            const keys = ['starter', 'national', 'international'];
            return this.packages[keys[this.presentationIndex]];
        },

        getPackageUrl(pkgKey) {
            return window.location.origin + '/packages?tier=' + pkgKey;
        },

        shareWhatsApp(pkgKey) {
            const pkg = this.packages[pkgKey];
            const text = `${pkg.shareSummary}\nView full package details:\n${this.getPackageUrl(pkgKey)}`;
            window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(text), '_blank');
        },

        async shareNative(pkgKey) {
            const pkg = this.packages[pkgKey];
            const text = `${pkg.shareSummary}\nView full package details:`;
            const url = this.getPackageUrl(pkgKey);
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: pkg.name + ' - SBL Packages',
                        text: text,
                        url: url
                    });
                } catch (e) {
                    // Ignore share cancel
                }
            } else {
                this.copyLink(pkgKey);
            }
        },

        copyLink(pkgKey) {
            const url = this.getPackageUrl(pkgKey);
            navigator.clipboard.writeText(url).then(() => {
                this.copiedToast = true;
                setTimeout(() => { this.copiedToast = false; }, 2500);
            });
        }
    }"
    @open-presentation.window="openPresentation(1)"
    @keydown.escape.window="detailsOpen = false; shareOpen = false; presentationOpen = false;"
>

    <!-- 1. HORIZONTAL FILTER & PRESENTATION BAR -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-3 sm:p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Filter Pills -->
        <div class="w-full sm:w-auto flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 no-scrollbar">
            <button 
                type="button" 
                @click="selectedTier = 'all'"
                :class="selectedTier === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap min-h-[44px] flex items-center justify-center cursor-pointer"
            >
                All Packages
            </button>

            <button 
                type="button" 
                @click="selectedTier = 'starter'"
                :class="selectedTier === 'starter' ? 'bg-[#AB2925] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap min-h-[44px] flex items-center justify-center cursor-pointer"
            >
                Starter (৳১০k)
            </button>

            <button 
                type="button" 
                @click="selectedTier = 'national'"
                :class="selectedTier === 'national' ? 'bg-[#C2410C] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap min-h-[44px] flex items-center justify-center cursor-pointer"
            >
                National (৳১.২L)
            </button>

            <button 
                type="button" 
                @click="selectedTier = 'international'"
                :class="selectedTier === 'international' ? 'bg-amber-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap min-h-[44px] flex items-center justify-center cursor-pointer"
            >
                International (৳৫.৫L)
            </button>
        </div>

        <!-- Presentation View Trigger Button -->
        <button 
            type="button" 
            @click="openPresentation(1)"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-orange-50 text-orange-800 hover:bg-orange-100 border border-orange-200 font-bold text-xs transition-all active:scale-95 shadow-xs min-h-[44px] cursor-pointer"
            title="Open clean presentation view for prospect"
        >
            <span>📽️</span>
            <span>Presentation View</span>
        </button>
    </div>

    <!-- 2. MAIN PACKAGE CARDS (ONLY 3 PRIMARY CARDS) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- CARD 1: STARTER MEMBERSHIP (৳১০,০০০) -->
        <article 
            id="card-starter"
            x-show="selectedTier === 'all' || selectedTier === 'starter'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="bg-white rounded-2xl border border-slate-200/90 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden"
            x-cloak
        >
            <div class="p-5 sm:p-6 space-y-4">
                <!-- Top Badge & Type -->
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold tracking-wide uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Starter Membership
                    </span>
                    <span class="text-xs text-slate-600 font-bold">Lifetime</span>
                </div>

                <!-- Price Block -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">৳১০,০০০</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600">BDT</span>
                    </div>
                    <p class="text-xs font-bold text-emerald-700 mt-1">
                        One-time Activation Fee
                    </p>
                </div>

                <!-- Description -->
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Starter Membership</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Entry-level SBL membership and affiliate access.
                    </p>
                </div>

                <!-- 3-4 Key Highlights Only -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Lifetime Membership</span>
                    </div>
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Affiliate Portal Access</span>
                    </div>
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Direct / Spot Commission</span>
                    </div>
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Team / Network Eligibility</span>
                    </div>
                </div>
            </div>

            <!-- Card Bottom Actions -->
            <div class="p-5 sm:p-6 pt-0 grid grid-cols-2 gap-2">
                <button 
                    type="button" 
                    @click="openDetails('starter')"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs active:scale-95 transition-all min-h-[44px] cursor-pointer"
                >
                    View Details
                </button>
                <button 
                    type="button" 
                    @click="openShare('starter')"
                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-200 active:scale-95 transition-all min-h-[44px] cursor-pointer"
                >
                    <span>📤</span>
                    <span>Share</span>
                </button>
            </div>
        </article>

        <!-- CARD 2: NATIONAL DROPSHIPPING (৳১,২০,০০০) -->
        <article 
            id="card-national"
            x-show="selectedTier === 'all' || selectedTier === 'national'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="bg-white rounded-2xl border-2 border-orange-500/80 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden relative"
            x-cloak
        >
            <div class="p-5 sm:p-6 space-y-4">
                <!-- Top Badge & Type -->
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold tracking-wide uppercase bg-orange-100 text-[#C2410C] border border-orange-200">
                        National Dropshipping
                    </span>
                    <span class="text-xs font-bold text-orange-600">100 BV • 100 Weeks</span>
                </div>

                <!-- Price Block -->
                <div class="p-4 bg-orange-50/50 rounded-2xl border border-orange-100">
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">৳১,২০,০০০</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600">BDT</span>
                    </div>
                    <div class="text-xs text-orange-800 font-bold mt-1">
                        ৳১,০০,০০০ Capital + ৳২০,০০০ Setup
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h3 class="text-lg font-bold text-slate-900">National Dropshipping</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Shopify ই-কমার্স স্টোর ও ডেলিভারি লজিস্টিকস সহ দেশীয় ড্রপশিপিং।
                    </p>
                </div>

                <!-- 3-4 Key Highlights Only -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <span class="text-orange-600 font-bold">✓</span>
                        <span><strong>100 BV</strong> Binary Volume</span>
                    </div>
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <span class="text-orange-600 font-bold">✓</span>
                        <span><strong>100 Weeks</strong> Plan Duration</span>
                    </div>
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <span class="text-orange-600 font-bold">✓</span>
                        <span>Plan-based Weekly Return: <strong>1.75%</strong> (~৳1,750/week)</span>
                    </div>
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <span class="text-orange-600 font-bold">✓</span>
                        <span>Shopify Store & Local Sourcing Included</span>
                    </div>
                </div>
            </div>

            <!-- Card Bottom Actions -->
            <div class="p-5 sm:p-6 pt-0 grid grid-cols-2 gap-2">
                <button 
                    type="button" 
                    @click="openDetails('national')"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-xs active:scale-95 transition-all min-h-[44px] cursor-pointer"
                >
                    View Details
                </button>
                <button 
                    type="button" 
                    @click="openShare('national')"
                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-800 font-bold text-xs border border-orange-200 active:scale-95 transition-all min-h-[44px] cursor-pointer"
                >
                    <span>📤</span>
                    <span>Share</span>
                </button>
            </div>
        </article>

        <!-- CARD 3: INTERNATIONAL DROPSHIPPING (৳৫,৫০,০০০) -->
        <article 
            id="card-international"
            x-show="selectedTier === 'all' || selectedTier === 'international'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="bg-white rounded-2xl border border-amber-300 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden"
            x-cloak
        >
            <div class="p-5 sm:p-6 space-y-4">
                <!-- Top Badge & Type -->
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold tracking-wide uppercase bg-amber-100 text-amber-900 border border-amber-200">
                        International Dropshipping
                    </span>
                    <span class="text-xs font-bold text-amber-800">500 BV • 100 Weeks</span>
                </div>

                <!-- Price Block -->
                <div class="p-4 bg-amber-50/50 rounded-2xl border border-amber-100">
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">৳৫,৫০,০০০</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600">BDT</span>
                    </div>
                    <div class="text-xs text-amber-900 font-bold mt-1">
                        ৳৫,০০,০০০ Capital + ৳৫০,০০০ Setup
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h3 class="text-lg font-bold text-slate-900">International Dropshipping</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        গ্লোবাল মার্কেটপ্লেস ও ডেডিকেটেড ম্যানেজমেন্ট টিম সহ সম্পূর্ণ ড্রপশিপিং।
                    </p>
                </div>

                <!-- 3-4 Key Highlights Only -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <span class="text-amber-700 font-bold">✓</span>
                        <span><strong>500 BV</strong> Elite Volume</span>
                    </div>
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <span class="text-amber-700 font-bold">✓</span>
                        <span><strong>100 Weeks</strong> Plan Duration</span>
                    </div>
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <span class="text-amber-700 font-bold">✓</span>
                        <span>Plan-based Weekly Return: <strong>2.0%</strong> (~৳10,000/week)</span>
                    </div>
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <span class="text-amber-700 font-bold">✓</span>
                        <span>Global Shopify Store & Dedicated Team</span>
                    </div>
                </div>
            </div>

            <!-- Card Bottom Actions -->
            <div class="p-5 sm:p-6 pt-0 grid grid-cols-2 gap-2">
                <button 
                    type="button" 
                    @click="openDetails('international')"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-amber-800 hover:bg-amber-900 text-white font-bold text-xs shadow-xs active:scale-95 transition-all min-h-[44px] cursor-pointer"
                >
                    View Details
                </button>
                <button 
                    type="button" 
                    @click="openShare('international')"
                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 font-bold text-xs border border-amber-200 active:scale-95 transition-all min-h-[44px] cursor-pointer"
                >
                    <span>📤</span>
                    <span>Share</span>
                </button>
            </div>
        </article>

    </div>

    <!-- 3. SIMPLE PACKAGE COMPARISON SECTION (DESKTOP: 3-COL, MOBILE: STACKED CARDS) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 sm:p-6 space-y-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Simple Package Comparison</h3>
            <p class="text-xs text-slate-600 mt-0.5">Quick side-by-side comparison of official SBL packages</p>
        </div>

        <!-- Desktop Comparison Table (Hidden on Mobile < sm) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Feature</th>
                        <th class="py-3 px-4">Starter Membership</th>
                        <th class="py-3 px-4 text-[#C2410C]">National Dropshipping</th>
                        <th class="py-3 px-4 text-amber-900">International Dropshipping</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-3 px-4 font-bold text-slate-900">Total Cost</td>
                        <td class="py-3 px-4 font-extrabold text-slate-900">৳১০,০০০</td>
                        <td class="py-3 px-4 font-extrabold text-[#C2410C]">৳১,২০,০০০</td>
                        <td class="py-3 px-4 font-extrabold text-amber-900">৳৫,৫০,০০০</td>
                    </tr>
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-3 px-4 font-medium text-slate-600">Capital</td>
                        <td class="py-3 px-4">N/A</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">৳১,০০,০০০</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">৳৫,০০,০০০</td>
                    </tr>
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-3 px-4 font-medium text-slate-600">Setup Fee</td>
                        <td class="py-3 px-4">Included</td>
                        <td class="py-3 px-4">৳২০,০০০</td>
                        <td class="py-3 px-4">৳৫০,০০০</td>
                    </tr>
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-3 px-4 font-medium text-slate-600">Business Volume (BV)</td>
                        <td class="py-3 px-4">Affiliate Entry</td>
                        <td class="py-3 px-4 font-bold text-orange-600">100 BV</td>
                        <td class="py-3 px-4 font-bold text-amber-800">500 BV</td>
                    </tr>
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-3 px-4 font-medium text-slate-600">Duration</td>
                        <td class="py-3 px-4">Lifetime</td>
                        <td class="py-3 px-4">100 Weeks</td>
                        <td class="py-3 px-4">100 Weeks</td>
                    </tr>
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-3 px-4 font-medium text-slate-600">Weekly Plan Rate</td>
                        <td class="py-3 px-4 text-slate-600">N/A</td>
                        <td class="py-3 px-4 font-bold text-[#C2410C]">1.75% / week (~৳1,750)</td>
                        <td class="py-3 px-4 font-bold text-amber-800">2.0% / week (~৳10,000)</td>
                    </tr>
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-3 px-4 font-medium text-slate-600">Spot Commission</td>
                        <td class="py-3 px-4">10% on direct</td>
                        <td class="py-3 px-4">10% (৳10,000 on capital)</td>
                        <td class="py-3 px-4">10% (৳50,000 on capital)</td>
                    </tr>
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-3 px-4 font-medium text-slate-600">Business Type</td>
                        <td class="py-3 px-4">Affiliate & Network</td>
                        <td class="py-3 px-4">National Dropshipping Store</td>
                        <td class="py-3 px-4">Global Dropshipping Enterprise</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Stacked Comparison Cards (No horizontal scrolling on phones) -->
        <div class="block sm:hidden space-y-3">
            <!-- Starter Mobile Card -->
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 text-xs space-y-2">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                    <span class="font-bold text-slate-900">Starter Membership</span>
                    <span class="font-extrabold text-slate-900 text-sm">৳১০,০০০</span>
                </div>
                <div class="grid grid-cols-2 gap-1 text-[11px] text-slate-600">
                    <div>Capital: <span class="font-semibold text-slate-800">N/A</span></div>
                    <div>Setup: <span class="font-semibold text-slate-800">Included</span></div>
                    <div>BV: <span class="font-semibold text-slate-800">Affiliate Entry</span></div>
                    <div>Duration: <span class="font-semibold text-slate-800">Lifetime</span></div>
                    <div>Weekly Rate: <span class="font-semibold text-slate-800">N/A</span></div>
                    <div>Spot Comm: <span class="font-semibold text-slate-800">10% Direct</span></div>
                </div>
            </div>

            <!-- National Mobile Card -->
            <div class="p-3.5 bg-orange-50/40 rounded-xl border border-orange-200 text-xs space-y-2">
                <div class="flex items-center justify-between border-b border-orange-200 pb-2">
                    <span class="font-bold text-orange-950">National Dropshipping</span>
                    <span class="font-extrabold text-orange-700 text-sm">৳১,২০,০০০</span>
                </div>
                <div class="grid grid-cols-2 gap-1 text-[11px] text-slate-600">
                    <div>Capital: <span class="font-semibold text-slate-800">৳১,০০,০০০</span></div>
                    <div>Setup: <span class="font-semibold text-slate-800">৳২০,০০০</span></div>
                    <div>BV: <span class="font-bold text-orange-700">100 BV</span></div>
                    <div>Duration: <span class="font-semibold text-slate-800">100 Weeks</span></div>
                    <div>Weekly Rate: <span class="font-bold text-orange-700">1.75% (~৳1,750)</span></div>
                    <div>Spot Comm: <span class="font-semibold text-slate-800">10% (৳10,000)</span></div>
                </div>
            </div>

            <!-- International Mobile Card -->
            <div class="p-3.5 bg-amber-50/40 rounded-xl border border-amber-200 text-xs space-y-2">
                <div class="flex items-center justify-between border-b border-amber-200 pb-2">
                    <span class="font-bold text-amber-950">International Dropshipping</span>
                    <span class="font-extrabold text-amber-900 text-sm">৳৫,৫০,০০০</span>
                </div>
                <div class="grid grid-cols-2 gap-1 text-[11px] text-slate-600">
                    <div>Capital: <span class="font-semibold text-slate-800">৳৫,০০,০০০</span></div>
                    <div>Setup: <span class="font-semibold text-slate-800">৳৫০,০০০</span></div>
                    <div>BV: <span class="font-bold text-amber-900">500 BV</span></div>
                    <div>Duration: <span class="font-semibold text-slate-800">100 Weeks</span></div>
                    <div>Weekly Rate: <span class="font-bold text-amber-900">2.0% (~৳10,000)</span></div>
                    <div>Spot Comm: <span class="font-semibold text-slate-800">10% (৳50,000)</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. COMPLIANCE & DISCLAIMER NOTE -->
    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-center">
        <p class="text-[11px] text-slate-600 leading-relaxed max-w-2xl mx-auto">
            “Package structure, commissions and plan-based figures are subject to current SBL Ecosystem terms and policies. Plan-based figures are not guaranteed profits. Verify current official information before making a financial decision.”
        </p>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. PACKAGE DETAILS MODAL / DRAWER (SECTIONS A, B, C, D)                    -->
    <!-- ========================================================================= -->
    <div 
        role="dialog" 
        aria-modal="true" 
        x-show="detailsOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/70 backdrop-blur-xs"
        x-cloak
    >
        <div 
            @click.outside="detailsOpen = false" 
            class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl p-5 sm:p-6 border border-slate-200 max-h-[90vh] overflow-y-auto space-y-5"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-orange-600" x-text="packages[activePackage].badge"></span>
                    <h3 class="text-lg font-bold text-slate-900" x-text="packages[activePackage].name"></h3>
                </div>
                <button 
                    @click="detailsOpen = false" 
                    class="text-slate-600 hover:text-slate-800 text-xl font-bold w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center cursor-pointer min-h-[44px]"
                    aria-label="Close"
                >
                    &times;
                </button>
            </div>

            <!-- SECTION A: BASIC INFORMATION -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider">A. Basic Information</h4>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-600 block text-[11px]">Total Cost</span>
                        <span class="font-extrabold text-slate-900 text-sm mt-0.5 block" x-text="packages[activePackage].price"></span>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-600 block text-[11px]">Capital</span>
                        <span class="font-bold text-slate-800 text-sm mt-0.5 block" x-text="packages[activePackage].capital"></span>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-600 block text-[11px]">Setup Fee</span>
                        <span class="font-semibold text-slate-800 text-xs mt-0.5 block" x-text="packages[activePackage].setupFee"></span>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-600 block text-[11px]">Business Volume</span>
                        <span class="font-bold text-orange-600 text-xs mt-0.5 block" x-text="packages[activePackage].bv"></span>
                    </div>
                    <div class="col-span-2 p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-600 block text-[11px]">Duration</span>
                        <span class="font-semibold text-slate-800 text-xs mt-0.5 block" x-text="packages[activePackage].duration"></span>
                    </div>
                </div>
            </div>

            <!-- SECTION B: PLAN INFORMATION (WHERE APPLICABLE) -->
            <template x-if="packages[activePackage].isInvestment">
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider">B. Plan Information</h4>
                    <div class="p-3 bg-orange-50/50 rounded-xl border border-orange-200/80 text-xs space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Weekly Plan Rate:</span>
                            <span class="font-bold text-orange-700" x-text="packages[activePackage].weeklyRate"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Estimated Weekly Amount:</span>
                            <span class="font-bold text-slate-900" x-text="packages[activePackage].weeklyAmount"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Plan Duration:</span>
                            <span class="font-semibold text-slate-800" x-text="packages[activePackage].duration"></span>
                        </div>
                        <div class="flex items-center justify-between border-t border-orange-200/80 pt-1.5 mt-1">
                            <span class="text-slate-700 font-bold">Total Plan-based Amount:</span>
                            <span class="font-extrabold text-orange-800" x-text="packages[activePackage].totalPlanAmount"></span>
                        </div>
                        <p class="text-[10px] text-amber-800 font-medium pt-1 italic">
                            * Plan-based / according to current SBL terms. Figures are estimated and not guaranteed profits.
                        </p>
                    </div>
                </div>
            </template>

            <!-- SECTION C: MAIN BENEFITS (MAX 5-6 CLEAR BULLETS) -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider">C. Main Benefits</h4>
                <div class="space-y-2">
                    <template x-for="(benefit, idx) in packages[activePackage].benefits" :key="idx">
                        <div class="flex items-start gap-2.5 text-xs text-slate-700">
                            <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center flex-shrink-0 text-[10px] mt-0.5">✓</span>
                            <span class="leading-relaxed" x-text="benefit"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- SECTION D: COMMISSION -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider">D. Commission Information</h4>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs">
                    <span class="text-slate-600 block text-[11px]">Spot / Direct Commission:</span>
                    <span class="font-bold text-slate-900 text-xs mt-0.5 block" x-text="packages[activePackage].commission"></span>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                <button 
                    type="button" 
                    @click="detailsOpen = false; openShare(activePackage)"
                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-xs active:scale-95 transition-all min-h-[44px] cursor-pointer"
                >
                    <span>📤</span>
                    <span>Share This</span>
                </button>
                <button 
                    type="button" 
                    @click="detailsOpen = false"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs active:scale-95 transition-all min-h-[44px] cursor-pointer"
                >
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 6. SHARE MODAL (WHATSAPP, NATIVE SHARE, COPY LINK)                         -->
    <!-- ========================================================================= -->
    <div 
        role="dialog" 
        aria-modal="true" 
        x-show="shareOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/70 backdrop-blur-xs"
        x-cloak
    >
        <div 
            @click.outside="shareOpen = false" 
            class="w-full max-w-sm bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl p-5 sm:p-6 border border-slate-200 space-y-4"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Share Package</h3>
                    <p class="text-xs text-slate-600 mt-0.5" x-text="packages[sharePackage].name + ' (' + packages[sharePackage].price + ')'"></p>
                </div>
                <button 
                    @click="shareOpen = false" 
                    class="text-slate-600 hover:text-slate-800 text-xl font-bold w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center cursor-pointer min-h-[44px]"
                >
                    &times;
                </button>
            </div>

            <!-- Preview box -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs text-slate-700 whitespace-pre-line leading-relaxed font-mono">
                <span x-text="packages[sharePackage].shareSummary"></span>
                <span class="block text-orange-600 mt-1" x-text="'\nLink: ' + getPackageUrl(sharePackage)"></span>
            </div>

            <!-- Share Action Buttons -->
            <div class="space-y-2">
                <!-- WhatsApp -->
                <button 
                    type="button" 
                    @click="shareWhatsApp(sharePackage)"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition-all min-h-[44px] cursor-pointer"
                >
                    <span>💬</span>
                    <span>Share on WhatsApp</span>
                </button>

                <!-- Native Share (Messenger / Apps) -->
                <button 
                    type="button" 
                    @click="shareNative(sharePackage)"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs active:scale-95 transition-all min-h-[44px] cursor-pointer"
                >
                    <span>📱</span>
                    <span>Share via Other Apps</span>
                </button>

                <!-- Copy Link -->
                <button 
                    type="button" 
                    @click="copyLink(sharePackage)"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs border border-slate-200 active:scale-95 transition-all min-h-[44px] cursor-pointer"
                >
                    <span>📋</span>
                    <span>Copy Link</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 7. PRESENTATION MODE (DIGITAL BROCHURE VIEW FOR PHONE / PROSPECTS)         -->
    <!-- ========================================================================= -->
    <div 
        role="dialog" 
        aria-modal="true" 
        x-show="presentationOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 backdrop-blur-md p-3 sm:p-6"
        x-cloak
    >
        <div 
            class="w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[95vh]"
        >
            <!-- Presentation Header -->
            <div class="px-5 py-4 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-orange-600 text-white font-black flex items-center justify-center text-sm shadow-xs">SBL</span>
                    <div>
                        <div class="text-xs font-bold text-orange-400">Digital Presentation Brochure</div>
                        <div class="text-[11px] text-slate-300">Package <span x-text="presentationIndex + 1"></span> of 3</div>
                    </div>
                </div>
                <button 
                    @click="presentationOpen = false" 
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-colors min-h-[44px] flex items-center justify-center cursor-pointer"
                    aria-label="Exit Presentation"
                >
                    ✕ Exit
                </button>
            </div>

            <!-- Slide Body -->
            <div class="p-6 sm:p-8 space-y-5 overflow-y-auto flex-1">
                <!-- Package Identity & Price -->
                <div class="text-center space-y-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold tracking-wider uppercase border"
                          :class="currentPresentationPkg.badgeColor"
                          x-text="currentPresentationPkg.badge">
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight" x-text="currentPresentationPkg.name"></h2>
                    <div class="text-3xl sm:text-4xl font-black text-orange-600 mt-1" x-text="currentPresentationPkg.price"></div>
                    <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto leading-relaxed" x-text="currentPresentationPkg.description"></p>
                </div>

                <!-- 4-6 Key Information Points -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 block">Key Information</span>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2 bg-white rounded-xl border border-slate-200/80">
                            <span class="text-slate-600 block text-[10px]">Capital</span>
                            <span class="font-bold text-slate-800 text-xs mt-0.5 block" x-text="currentPresentationPkg.capital"></span>
                        </div>
                        <div class="p-2 bg-white rounded-xl border border-slate-200/80">
                            <span class="text-slate-600 block text-[10px]">Setup Fee</span>
                            <span class="font-bold text-slate-800 text-xs mt-0.5 block" x-text="currentPresentationPkg.setupFee"></span>
                        </div>
                        <div class="p-2 bg-white rounded-xl border border-slate-200/80">
                            <span class="text-slate-600 block text-[10px]">BV Points</span>
                            <span class="font-bold text-orange-600 text-xs mt-0.5 block" x-text="currentPresentationPkg.bv"></span>
                        </div>
                        <div class="p-2 bg-white rounded-xl border border-slate-200/80">
                            <span class="text-slate-600 block text-[10px]">Plan Duration</span>
                            <span class="font-bold text-slate-800 text-xs mt-0.5 block" x-text="currentPresentationPkg.duration"></span>
                        </div>
                    </div>
                </div>

                <!-- Main Benefits (Checkmarks) -->
                <div class="space-y-2.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 block">Included Benefits</span>
                    <div class="space-y-2">
                        <template x-for="(benefit, bIdx) in currentPresentationPkg.benefits" :key="bIdx">
                            <div class="flex items-start gap-2.5 text-xs text-slate-700">
                                <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center flex-shrink-0 text-[10px] mt-0.5">✓</span>
                                <span class="leading-relaxed" x-text="benefit"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Slide Navigation Footer -->
            <div class="p-4 sm:p-5 bg-slate-50 border-t border-slate-200/80 flex items-center justify-between gap-2">
                <button 
                    type="button" 
                    @click="prevPresentation()"
                    class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 font-bold text-xs border border-slate-300 shadow-xs active:scale-95 transition-all min-h-[44px] flex items-center gap-1 cursor-pointer"
                >
                    <span>←</span>
                    <span class="hidden sm:inline">Previous</span>
                </button>

                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full" :class="presentationIndex === 0 ? 'bg-orange-600' : 'bg-slate-300'"></span>
                    <span class="w-2.5 h-2.5 rounded-full" :class="presentationIndex === 1 ? 'bg-orange-600' : 'bg-slate-300'"></span>
                    <span class="w-2.5 h-2.5 rounded-full" :class="presentationIndex === 2 ? 'bg-orange-600' : 'bg-slate-300'"></span>
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        @click="openShare(currentPresentationPkg.id)"
                        class="px-3 py-2 rounded-xl bg-orange-100 hover:bg-orange-200 text-orange-800 font-bold text-xs transition-colors min-h-[44px] flex items-center gap-1 cursor-pointer"
                        title="Share this package"
                    >
                        <span>📤</span>
                        <span class="hidden sm:inline">Share</span>
                    </button>
                    <button 
                        type="button" 
                        @click="nextPresentation()"
                        class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs active:scale-95 transition-all min-h-[44px] flex items-center gap-1 cursor-pointer"
                    >
                        <span class="hidden sm:inline">Next</span>
                        <span>→</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div 
        x-show="copiedToast"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-4 py-2.5 rounded-xl shadow-xl text-xs font-bold flex items-center gap-2 border border-slate-800"
        x-cloak
    >
        <span>✓</span>
        <span>Package link copied to clipboard!</span>
    </div>

</div>
