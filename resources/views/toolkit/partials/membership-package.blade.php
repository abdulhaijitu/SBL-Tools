@php
    $config = $packageConfig ?? app(App\Http\Controllers\SblToolkitController::class)->getPackageConfig();
    $marketData = $marketComparisons ?? app(App\Http\Controllers\SblToolkitController::class)->getMarketComparisons();
    $growthData = $growthTrajectory ?? app(App\Http\Controllers\SblToolkitController::class)->getGrowthTrajectory();
    $activeTier = in_array(request('package') ?? request('tier'), ['starter', 'national', 'international']) ? (request('package') ?? request('tier')) : 'all';
@endphp

<section 
    id="sbl-packages-system"
    class="space-y-10 pb-16"
    x-data="{
        packages: @js($config),
        selectedTier: @js($activeTier),
        mobileActivePkg: 'national',
        
        // Modals & Drawers
        detailsModalOpen: false,
        activeDetailsPkg: null,
        activeDetailsTab: 'overview',
        
        // Lead Generation Modal
        leadModalOpen: false,
        leadPkg: { id: 'national', name: 'National Dropshipping', price: 120000, bv: 100 },
        leadForm: {
            name: '',
            mobile: '',
            amount: '',
            notes: '',
            submitting: false,
            success: false,
            error: null
        },
        
        // Share & Marketing Modal
        shareModalOpen: false,
        sharePkg: null,
        copiedToast: false,
        toastMessage: 'Copied to clipboard!',
        
        // Benefit list toggles (4 visible by default)
        expandedBenefits: {
            starter: false,
            national: false,
            international: false
        },
        
        // Quick Comparison Mobile View
        comparePkgMobile: 'national',

        // Backward compatibility for existing modal triggers
        modalOpen: false,
        nationalModalOpen: false,
        internationalModalOpen: false,
        openModal() { this.openDetails('starter'); },
        closeModal() { this.detailsModalOpen = false; this.modalOpen = false; },
        openNationalModal() { this.openDetails('national'); },
        closeNationalModal() { this.detailsModalOpen = false; this.nationalModalOpen = false; },
        openInternationalModal() { this.openDetails('international'); },
        closeInternationalModal() { this.detailsModalOpen = false; this.internationalModalOpen = false; },

        // Interactive ROI Calculator State
        calculator: {
            selectedPkgId: 'national',
            customAmount: 120000,
            get activePkg() {
                return $data.packages[this.selectedPkgId] || $data.packages.national;
            },
            get weeklyReturn() {
                const pkg = this.activePkg;
                if (!pkg.weekly_return_percent || pkg.capital_amount <= 0) return 0;
                const capitalRatio = pkg.capital_amount / pkg.price;
                const workingCapital = Math.max(0, this.customAmount * capitalRatio);
                return Math.round(workingCapital * (pkg.weekly_return_percent / 100));
            },
            get monthlyReturn() {
                return Math.round(this.weeklyReturn * 4.333);
            },
            get totalReturn() {
                return this.weeklyReturn * 100;
            },
            get recoveryWeeks() {
                if (this.weeklyReturn <= 0) return 0;
                return Math.ceil(this.customAmount / this.weeklyReturn);
            }
        },

        // Package Recommender Wizard
        wizard: {
            step: 1,
            budget: 'medium',       // low, medium, high
            goal: 'dropshipping',   // affiliate, dropshipping, global
            involvement: 'semi',    // flexible, semi, hands_off
            get recommendedPkgId() {
                if (this.budget === 'high' || this.goal === 'global' || this.involvement === 'hands_off') {
                    return 'international';
                }
                if (this.budget === 'medium' || this.goal === 'dropshipping') {
                    return 'national';
                }
                return 'starter';
            },
            get recommendedPkg() {
                return $data.packages[this.recommendedPkgId];
            }
        },

        // FAQ Accordions (1 to 10)
        openFaq: null,
        toggleFaq(index) {
            this.openFaq = this.openFaq === index ? null : index;
        },

        init() {
            const params = new URLSearchParams(window.location.search);
            const p = params.get('package') || params.get('tier');
            if (p && this.packages[p]) {
                this.selectedTier = p;
                this.mobileActivePkg = p;
                this.comparePkgMobile = p;
                this.calculator.selectedPkgId = p;
                this.calculator.customAmount = this.packages[p].price;
            }
            if (!this.activeDetailsPkg) {
                this.activeDetailsPkg = this.packages.national;
            }
        },

        selectTier(tier) {
            this.selectedTier = tier;
            if (tier !== 'all') {
                this.mobileActivePkg = tier;
                this.comparePkgMobile = tier;
            }
            const el = document.getElementById('package-cards-grid');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        openDetails(pkgId) {
            this.activeDetailsPkg = this.packages[pkgId] || this.packages.national;
            this.activeDetailsTab = 'overview';
            this.detailsModalOpen = true;
        },

        openLeadModal(pkgId) {
            const pkg = this.packages[pkgId] || this.packages.national;
            this.leadPkg = pkg;
            this.leadForm.amount = '৳' + pkg.price.toLocaleString();
            this.leadForm.submitting = false;
            this.leadForm.success = false;
            this.leadForm.error = null;
            this.leadModalOpen = true;
        },

        async submitLead() {
            if (!this.leadForm.name || !this.leadForm.mobile) {
                this.leadForm.error = 'Please enter your name and mobile number.';
                return;
            }
            this.leadForm.submitting = true;
            this.leadForm.error = null;
            try {
                const payload = {
                    name: this.leadForm.name.trim(),
                    mobile: this.leadForm.mobile.trim(),
                    whatsapp: this.leadForm.mobile.trim(),
                    lead_source_id: 1,
                    lead_source_detail: 'Packages Page',
                    stage: 'interested',
                    budget_range: this.leadForm.amount || ('৳' + this.leadPkg.price.toLocaleString()),
                    notes: `[Packages Page] Selected: ${this.leadPkg.name} (৳${this.leadPkg.price.toLocaleString()}). ${this.leadForm.notes || ''}`.trim(),
                    interest_types: ['Dropshipping', 'E-commerce']
                };
                const res = await fetch('/leads', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (res.ok && (data.success || data.lead_id)) {
                    this.leadForm.success = true;
                    setTimeout(() => {
                        this.leadModalOpen = false;
                        this.leadForm.name = '';
                        this.leadForm.mobile = '';
                        this.leadForm.notes = '';
                        this.leadForm.success = false;
                    }, 2500);
                } else {
                    this.leadForm.error = data.message || 'Could not submit request. Please try again.';
                }
            } catch (e) {
                this.leadForm.error = 'Connection issue. Please verify your internet and try again.';
            } finally {
                this.leadForm.submitting = false;
            }
        },

        getShareText(pkg) {
            return `📦 SBL ${pkg.name}\n💰 Price: ${pkg.price_formatted} (${pkg.setup_fee_formatted})\n📊 Business Volume: ${pkg.bv_formatted} | Term: ${pkg.duration}\n📈 Plan Return: ${pkg.weekly_return_text}\n🤝 Direct Spot: ${pkg.direct_commission}\n🚀 Crowdfunding: ${pkg.crowdfunding_formatted}\n🔗 View details: ${window.location.origin}/packages?package=${pkg.id}`;
        },

        openShareModal(pkgId) {
            this.sharePkg = this.packages[pkgId] || this.packages.national;
            if (navigator.share && window.innerWidth < 768) {
                navigator.share({
                    title: `SBL ${this.sharePkg.name}`,
                    text: this.getShareText(this.sharePkg),
                    url: `${window.location.origin}/packages?package=${this.sharePkg.id}`
                }).catch(() => {});
                return;
            }
            this.shareModalOpen = true;
        },

        copySummary(pkg) {
            const text = this.getShareText(pkg);
            navigator.clipboard.writeText(text);
            this.toastMessage = 'Package summary copied! Ready to paste.';
            this.copiedToast = true;
            setTimeout(() => { this.copiedToast = false; }, 3000);
        },

        copyLink(pkg) {
            const url = `${window.location.origin}/packages?package=${pkg.id}`;
            navigator.clipboard.writeText(url);
            this.toastMessage = 'Direct link copied to clipboard!';
            this.copiedToast = true;
            setTimeout(() => { this.copiedToast = false; }, 3000);
        },

        shareWhatsApp(pkg) {
            const text = encodeURIComponent(this.getShareText(pkg));
            window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
        },

        shareFacebook(pkg) {
            const url = encodeURIComponent(`${window.location.origin}/packages?package=${pkg.id}`);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
        },

        scrollToCalculator(pkgId) {
            if (pkgId) {
                this.calculator.selectedPkgId = pkgId;
                this.calculator.customAmount = this.packages[pkgId].price;
            }
            const el = document.getElementById('roi-calculator');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }"
>

    <!-- 01. COMPACT PAGE HEADER -->
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 sm:p-8 text-white shadow-xl border border-slate-800">
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-orange-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-[11px] font-semibold text-orange-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span data-en="Official SBL 2026 Plan" data-bn="অফিসিয়াল SBL ২০২৬ প্ল্যান">Official SBL 2026 Plan</span>
                    <span class="text-white/40">•</span>
                    <span data-en="Verified Structure" data-bn="যাচাইকৃত স্ট্রাকচার">Verified Structure</span>
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight">
                    <span data-en="SBL Business & Membership Packages" data-bn="SBL বিজনেস ও মেম্বারশিপ প্যাকেজসমূহ">SBL Business & Membership Packages</span>
                </h1>
                <p class="text-sm sm:text-base text-slate-300 font-normal leading-relaxed">
                    <span data-en="Choose the verified tier designed for your dropshipping scale and affiliate career. Turnkey Shopify store, logistics fulfillment, and plan-based returns in one unified ecosystem." data-bn="আপনার ড্রপশিপিং ও অ্যাফিলিয়েট ক্যারিয়ারের জন্য উপযুক্ত প্যাকেজ নির্বাচন করুন। রেডি শপিফাই স্টোর, লজিস্টিকস ডেলিভারি ও প্ল্যান-ভিত্তিক রিটার্ন সমন্বিত প্ল্যাটফর্ম।">
                        Choose the verified tier designed for your dropshipping scale and affiliate career. Turnkey Shopify store, logistics fulfillment, and plan-based returns in one unified ecosystem.
                    </span>
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <button 
                    type="button"
                    @click="scrollToCalculator('national')"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/20 text-white text-xs sm:text-sm font-bold transition-all shadow-xs cursor-pointer min-h-[44px]"
                >
                    <span>📊</span>
                    <span data-en="ROI Calculator" data-bn="রিটার্ন ক্যালকুলেটর">ROI Calculator</span>
                </button>
                <a 
                    href="#package-comparison"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs sm:text-sm font-bold transition-all shadow-md shadow-orange-600/30 cursor-pointer min-h-[44px]"
                >
                    <span>⚡</span>
                    <span data-en="Compare Packages" data-bn="প্যাকেজ তুলনা করুন">Compare Packages</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 02. PACKAGE FILTER SWITCHER (Horizontally Scrollable Pills on Mobile) -->
    <div class="sticky top-16 z-20 -mx-4 px-4 sm:mx-0 sm:px-0 py-2 bg-slate-50/90 dark:bg-slate-900/90 backdrop-blur-md">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-2">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1 w-full sm:w-auto">
                <button 
                    type="button"
                    @click="selectTier('all')"
                    :class="selectedTier === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer min-h-[44px] shrink-0"
                >
                    <span data-en="All Packages" data-bn="সকল প্যাকেজ">All Packages</span>
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-md text-[10px]" :class="selectedTier === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">3</span>
                </button>

                <button 
                    type="button"
                    @click="selectTier('starter')"
                    :class="selectedTier === 'starter' ? 'bg-slate-800 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer min-h-[44px] shrink-0"
                >
                    <span data-en="Starter (৳10,000)" data-bn="স্টার্টার (৳১০,০০০)">Starter (৳10,000)</span>
                </button>

                <button 
                    type="button"
                    @click="selectTier('national')"
                    :class="selectedTier === 'national' ? 'bg-orange-600 text-white shadow-md shadow-orange-600/30' : 'bg-orange-50 text-orange-950 hover:bg-orange-100 border border-orange-200'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer min-h-[44px] shrink-0 relative"
                >
                    <span class="inline-flex items-center gap-1.5">
                        <span class="text-xs">⭐</span>
                        <span data-en="National (৳120,000)" data-bn="ন্যাশনাল (৳১,২০,০০০)">National (৳120,000)</span>
                    </span>
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-amber-400 text-amber-950">Popular</span>
                </button>

                <button 
                    type="button"
                    @click="selectTier('international')"
                    :class="selectedTier === 'international' ? 'bg-amber-700 text-white shadow-sm' : 'bg-amber-50 text-amber-950 hover:bg-amber-100 border border-amber-200'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer min-h-[44px] shrink-0"
                >
                    <span data-en="International (৳550,000)" data-bn="আন্তর্জাতিক (৳৫,৫০,০০০)">International (৳550,000)</span>
                </button>
            </div>

            <!-- Currency/Verification Badge on Desktop -->
            <div class="hidden lg:flex items-center gap-2 text-xs text-slate-500 font-medium shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span data-en="Last Verified: September 2026" data-bn="সর্বশেষ যাচাইকৃত: সেপ্টেম্বর ২০২৬">Last Verified: September 2026</span>
            </div>
        </div>
    </div>

    <!-- 03. MAIN PACKAGE CARDS (3-COLUMN COMPARATIVE PRICING GRID) -->
    <div id="package-cards-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch">

        <!-- ========================================== -->
        <!-- CARD 1: STARTER MEMBERSHIP (৳10,000)       -->
        <!-- ========================================== -->
        <div 
            x-show="selectedTier === 'all' || selectedTier === 'starter'"
            x-transition
            class="flex flex-col rounded-3xl bg-white border border-slate-200 hover:border-slate-300 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden relative"
        >
            <!-- Top Accent -->
            <div class="h-2 bg-slate-700 w-full"></div>

            <div class="p-6 sm:p-7 flex flex-col flex-1">
                <!-- Header & Badge -->
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-800">
                            Starter • Affiliate
                        </span>
                    </div>
                    <span class="text-xs font-bold text-slate-500">Entry Tier</span>
                </div>

                <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-snug">
                    <span data-en="Starter Membership" data-bn="স্টার্টার মেম্বারশিপ">Starter Membership</span>
                </h3>
                <p class="text-xs text-slate-500 mt-1 min-h-[34px]">
                    <span data-en="Entry-level affiliate portal and network binary marketing access" data-bn="অ্যাফিলিয়েট নেটওয়ার্ক ও ডিরেক্ট সেলস রেফারেল ক্যারিয়ারের এন্ট্রি প্যাকেজ">Entry-level affiliate portal and network binary marketing access</span>
                </p>

                <!-- Prominent Price Display -->
                <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">৳10,000</span>
                        <span class="text-xs font-semibold text-slate-500">BDT</span>
                    </div>
                    <div class="mt-1 text-xs text-slate-600 font-medium">
                        <span data-en="One-time Setup & Activation • Lifetime Access" data-bn="এককালীন সেটআপ ও অ্যাক্টিভেশন • লাইফটাইম এক্সেস">One-time Setup & Activation • Lifetime Access</span>
                    </div>
                </div>

                <!-- Key Metrics Grid -->
                <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-100 text-xs">
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-[11px] text-slate-500 block" data-en="Direct Spot" data-bn="স্পট কমিশন">Direct Spot</span>
                        <span class="font-bold text-slate-900">10% (৳1,000)</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-[11px] text-slate-500 block" data-en="Duration" data-bn="মেয়াদকাল">Duration</span>
                        <span class="font-bold text-slate-900" data-en="Lifetime" data-bn="লাইফটাইম">Lifetime</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-[11px] text-slate-500 block" data-en="Business Volume" data-bn="পয়েন্ট (BV)">Business Volume</span>
                        <span class="font-bold text-slate-700" data-en="Entry Level" data-bn="এন্ট্রি লেভেল">Entry Level</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-[11px] text-slate-500 block" data-en="Upgradable" data-bn="আপগ্রেড">Upgradable</span>
                        <span class="font-bold text-emerald-700" data-en="Anytime" data-bn="যেকোনো সময়">Anytime</span>
                    </div>
                </div>

                <!-- Benefits List (4 initial + expander) -->
                <div class="mt-5 space-y-2.5 flex-1">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500" data-en="Core Deliverables:" data-bn="মূল সুবিধাসমূহ:">Core Deliverables:</p>
                    <ul class="space-y-2 text-xs text-slate-700">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
                            <span data-en="Full Access to SBL Affiliate Portal & Dashboard" data-bn="SBL অ্যাফিলিয়েট পোর্টাল ও ড্যাশবোর্ডে পূর্ণ এক্সেস">Full Access to SBL Affiliate Portal & Dashboard</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
                            <span data-en="10% Instant Spot Direct Referral Commission" data-bn="১০% তাৎক্ষণিক স্পট ডিরেক্ট রেফারেল কমিশন">10% Instant Spot Direct Referral Commission</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
                            <span data-en="Binary Matching & Team Generation Eligibility" data-bn="বাইনারি ম্যাচিং ও টিম জেনারেশন কমিশন সুবিধা">Binary Matching & Team Generation Eligibility</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
                            <span data-en="Merchant Products Catalog & Digital Promotional Assets" data-bn="মার্চেন্ট প্রোডাক্ট ক্যাটালগ ও প্রমোশনাল মার্কেটিং অ্যাসেট">Merchant Products Catalog & Digital Promotional Assets</span>
                        </li>

                        <!-- Hidden extra benefits toggled via Alpine -->
                        <template x-if="expandedBenefits.starter">
                            <div class="space-y-2 pt-1 border-t border-dashed border-slate-200">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
                                    <span data-en="Official Training Community & Sales Pitch Guides" data-bn="অফিসিয়াল ট্রেনিং সেশন ও সেলস গাইডলাইন এক্সেস">Official Training Community & Sales Pitch Guides</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
                                    <span data-en="Lifetime Membership with seamless upgrade option" data-bn="লাইফটাইম মেম্বারশিপ এবং যেকোনো সময় আপগ্রেড সুবিধা">Lifetime Membership with seamless upgrade option</span>
                                </li>
                            </div>
                        </template>
                    </ul>

                    <!-- Expand / Collapse Button -->
                    <button 
                        type="button"
                        @click="expandedBenefits.starter = !expandedBenefits.starter"
                        class="text-[11px] font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1 mt-1 cursor-pointer"
                    >
                        <span x-text="expandedBenefits.starter ? '− Show fewer benefits' : '+ View all 6 benefits'"></span>
                    </button>
                </div>

                <!-- Action CTAs -->
                <div class="mt-6 space-y-2.5 pt-4 border-t border-slate-100">
                    <button 
                        type="button"
                        @click="openLeadModal('starter')"
                        class="w-full py-3 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer min-h-[44px]"
                    >
                        <span data-en="Interested • Get Started" data-bn="আগ্রহী • শুরু করুন">Interested • Get Started</span>
                        <span>→</span>
                    </button>

                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            type="button"
                            @click="openDetails('starter')"
                            class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs transition-colors flex items-center justify-center gap-1 cursor-pointer min-h-[44px]"
                        >
                            <span>🔍</span>
                            <span data-en="Full Details" data-bn="বিস্তারিত">Full Details</span>
                        </button>
                        <button 
                            type="button"
                            @click="openShareModal('starter')"
                            class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors flex items-center justify-center gap-1 cursor-pointer min-h-[44px]"
                        >
                            <span>📤</span>
                            <span data-en="Share" data-bn="শেয়ার">Share</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- ========================================== -->
        <!-- CARD 2: NATIONAL DROPSHIPPING (৳120,000)   -->
        <!-- ⭐ MOST POPULAR • 100 BV                    -->
        <!-- ========================================== -->
        <div 
            x-show="selectedTier === 'all' || selectedTier === 'national'"
            x-transition
            class="flex flex-col rounded-3xl bg-white border-2 border-orange-500 shadow-xl shadow-orange-500/10 hover:shadow-2xl hover:shadow-orange-500/15 transition-all duration-300 overflow-hidden relative lg:-translate-y-2 z-10"
        >
            <!-- Most Popular Header Banner -->
            <div class="bg-orange-600 bg-gradient-to-r from-orange-600 via-amber-600 to-orange-600 px-4 py-1.5 text-center text-white text-xs font-extrabold uppercase tracking-widest shadow-xs flex items-center justify-center gap-1.5">
                <span>⭐</span>
                <span data-en="MOST POPULAR • 100 BV CAREER TIER" data-bn="সর্বাধিক জনপ্রিয় • ১০০ BV ক্যারিয়ার প্যাকেজ">MOST POPULAR • 100 BV CAREER TIER</span>
            </div>

            <div class="p-6 sm:p-7 flex flex-col flex-1">
                <!-- Header & Badge -->
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-orange-100 text-orange-800">
                            National Dropshipping
                        </span>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900">
                        100 BV
                    </span>
                </div>

                <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-snug">
                    <span data-en="National Dropshipping" data-bn="ন্যাশনাল ড্রপশিপিং প্যাকেজ">National Dropshipping</span>
                </h3>
                <p class="text-xs text-slate-500 mt-1 min-h-[34px]">
                    <span data-en="Complete turnkey domestic e-commerce business with Shopify store & weekly returns" data-bn="রেডি শপিফাই স্টোর, দেশীয় প্রোডাক্ট সোর্সিং ও সাপ্তাহিক রিটার্নসহ পূর্ণাঙ্গ ব্যবসা">Complete turnkey domestic e-commerce business with Shopify store & weekly returns</span>
                </p>

                <!-- Prominent Price Display -->
                <div class="mt-4 p-4 rounded-2xl bg-orange-50/70 border border-orange-200/80">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl sm:text-4xl font-black text-orange-950 tracking-tight">৳120,000</span>
                        <span class="text-xs font-bold text-orange-700">BDT</span>
                    </div>
                    <div class="mt-1 text-xs text-orange-900 font-semibold flex items-center justify-between">
                        <span data-en="৳100,000 Capital + ৳20,000 Store Setup" data-bn="৳১,০০,০০০ ইনভেস্টমেন্ট + ৳২০,০০০ সেটআপ ফি">৳100,000 Capital + ৳20,000 Store Setup</span>
                    </div>
                </div>

                <!-- Key Metrics Grid -->
                <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-100 text-xs">
                    <div class="p-2.5 rounded-xl bg-orange-50/50 border border-orange-100">
                        <span class="text-[11px] text-slate-500 block" data-en="Weekly Return (100 Wks)" data-bn="সাপ্তাহিক রিটার্ন (১০০ সপ্তাহ)">Weekly Return (100 Wks)</span>
                        <span class="font-bold text-orange-700">1.75% (৳1,750/wk)</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] text-slate-500 block" data-en="Total Plan Return" data-bn="মোট প্ল্যান রিটার্ন">Total Plan Return</span>
                        <span class="font-bold text-slate-900">৳1,75,000 BDT</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] text-slate-500 block" data-en="Spot Commission" data-bn="স্পট ডিরেক্ট">Spot Commission</span>
                        <span class="font-bold text-slate-900">10% (৳12,000)</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] text-slate-500 block" data-en="Crowdfunding Limit" data-bn="ক্রাউডফান্ডিং">Crowdfunding Limit</span>
                        <span class="font-bold text-emerald-700" data-en="Up to ৳10 Lac" data-bn="১০ লাখ টাকা পর্যন্ত">Up to ৳10 Lac</span>
                    </div>
                </div>

                <!-- Benefits List (4 initial + expander) -->
                <div class="mt-5 space-y-2.5 flex-1">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500" data-en="Core Deliverables:" data-bn="মূল সুবিধাসমূহ:">Core Deliverables:</p>
                    <ul class="space-y-2 text-xs text-slate-700">
                        <li class="flex items-start gap-2">
                            <span class="text-orange-600 font-bold shrink-0 mt-0.5">✓</span>
                            <span data-en="Branded Turnkey Shopify Store & Custom Domain Setup" data-bn="ব্র্যান্ডেড শপিফাই স্টোর ও কাস্টম ডোমেন সেটআপ">Branded Turnkey Shopify Store & Custom Domain Setup</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-orange-600 font-bold shrink-0 mt-0.5">✓</span>
                            <span data-en="Curated Verified High-Margin Dropshipping Products" data-bn="যাচাইকৃত প্রিমিয়াম ও ট্রেন্ডিং প্রোডাক্ট সোর্সিং">Curated Verified High-Margin Dropshipping Products</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-orange-600 font-bold shrink-0 mt-0.5">✓</span>
                            <span data-en="Weekly 1.75% Plan-based Return on Capital (100 Weeks)" data-bn="ক্যাপিটালের ওপর সাপ্তাহিক ১.৭৫% প্ল্যান-ভিত্তিক রিটার্ন (১০০ সপ্তাহ)">Weekly 1.75% Plan-based Return on Capital (100 Weeks)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-orange-600 font-bold shrink-0 mt-0.5">✓</span>
                            <span data-en="Custom SBL Packaging & Automated Logistics Support" data-bn="নিজস্ব কাস্টম প্যাকেজিং ও ডেলিভারি লজিস্টিকস সাপোর্ট">Custom SBL Packaging & Automated Logistics Support</span>
                        </li>

                        <!-- Hidden extra benefits toggled via Alpine -->
                        <template x-if="expandedBenefits.national">
                            <div class="space-y-2 pt-1 border-t border-dashed border-orange-200">
                                <li class="flex items-start gap-2">
                                    <span class="text-orange-600 font-bold shrink-0 mt-0.5">✓</span>
                                    <span data-en="Post-100 Weeks Monthly Profit Sharing (৳5k - ৳20k)" data-bn="১০০ সপ্তাহ পর আজীবন মাসিক প্রফিট শেয়ারিং (৫হাজার - ২০হাজার)">Post-100 Weeks Monthly Profit Sharing (৳5k - ৳20k)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-orange-600 font-bold shrink-0 mt-0.5">✓</span>
                                    <span data-en="Business Crowdfunding Opportunity up to ৳10 Lac BDT" data-bn="১০ লাখ টাকা পর্যন্ত বিজনেস ক্রাউডফান্ডিং সম্প্রসারণ সুবিধা">Business Crowdfunding Opportunity up to ৳10 Lac BDT</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-orange-600 font-bold shrink-0 mt-0.5">✓</span>
                                    <span data-en="100 BV for Fast-track Binary Career Progression" data-bn="ক্যারিয়ার পদোন্নতি ও বোনাসের জন্য ১০০ BV পয়েন্ট">100 BV for Fast-track Binary Career Progression</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-orange-600 font-bold shrink-0 mt-0.5">✓</span>
                                    <span data-en="Paid Facebook Ad Campaigns & Order Moderation" data-bn="পেইড ফেসবুক এড ক্যাম্পেইন ও অর্ডার ম্যানেজমেন্ট সাপোর্ট">Paid Facebook Ad Campaigns & Order Moderation</span>
                                </li>
                            </div>
                        </template>
                    </ul>

                    <!-- Expand / Collapse Button -->
                    <button 
                        type="button"
                        @click="expandedBenefits.national = !expandedBenefits.national"
                        class="text-[11px] font-bold text-orange-600 hover:text-orange-700 inline-flex items-center gap-1 mt-1 cursor-pointer"
                    >
                        <span x-text="expandedBenefits.national ? '− Show fewer benefits' : '+ View all 8 benefits'"></span>
                    </button>
                </div>

                <!-- Action CTAs -->
                <div class="mt-6 space-y-2.5 pt-4 border-t border-orange-100">
                    <button 
                        type="button"
                        @click="openLeadModal('national')"
                        class="w-full py-3.5 px-4 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-sm transition-all shadow-md shadow-orange-600/30 flex items-center justify-center gap-2 cursor-pointer min-h-[44px]"
                    >
                        <span data-en="Interested • Choose National" data-bn="আগ্রহী • ন্যাশনাল নির্বাচন করুন">Interested • Choose National</span>
                        <span>→</span>
                    </button>

                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            type="button"
                            @click="openDetails('national')"
                            class="py-2 px-2 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-950 font-bold text-xs transition-colors flex items-center justify-center gap-1 cursor-pointer min-h-[44px]"
                        >
                            <span>🔍</span>
                            <span data-en="Details" data-bn="বিস্তারিত">Details</span>
                        </button>
                        <button 
                            type="button"
                            @click="scrollToCalculator('national')"
                            class="py-2 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs transition-colors flex items-center justify-center gap-1 cursor-pointer min-h-[44px]"
                        >
                            <span>📊</span>
                            <span data-en="ROI" data-bn="রিটার্ন">ROI</span>
                        </button>
                        <button 
                            type="button"
                            @click="openShareModal('national')"
                            class="py-2 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors flex items-center justify-center gap-1 cursor-pointer min-h-[44px]"
                        >
                            <span>📤</span>
                            <span data-en="Share" data-bn="শেয়ার">Share</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- ========================================== -->
        <!-- CARD 3: INTERNATIONAL DROPSHIPPING         -->
        <!-- ৳550,000 (500 BV) • GLOBAL ENTERPRISE      -->
        <!-- ========================================== -->
        <div 
            x-show="selectedTier === 'all' || selectedTier === 'international'"
            x-transition
            class="flex flex-col rounded-3xl bg-white border border-amber-300 hover:border-amber-400 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden relative"
        >
            <!-- Top Accent -->
            <div class="h-2 bg-amber-600 bg-gradient-to-r from-amber-600 to-amber-700 w-full"></div>

            <div class="p-6 sm:p-7 flex flex-col flex-1">
                <!-- Header & Badge -->
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-amber-100 text-amber-900">
                            👑 Global Enterprise
                        </span>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-950">
                        500 BV
                    </span>
                </div>

                <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-snug">
                    <span data-en="International Dropshipping" data-bn="আন্তর্জাতিক ড্রপশিপিং">International Dropshipping</span>
                </h3>
                <p class="text-xs text-slate-500 mt-1 min-h-[34px]">
                    <span data-en="Cross-border global marketplace with dedicated full-time management team" data-bn="আন্তর্জাতিক মার্কেটপ্লেস ও সার্বক্ষণিক ডেডিকেটেড ম্যানেজমেন্ট টিম সাপোর্ট">Cross-border global marketplace with dedicated full-time management team</span>
                </p>

                <!-- Prominent Price Display -->
                <div class="mt-4 p-4 rounded-2xl bg-amber-50/80 border border-amber-200">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl sm:text-4xl font-black text-amber-950 tracking-tight">৳550,000</span>
                        <span class="text-xs font-bold text-amber-700">BDT</span>
                    </div>
                    <div class="mt-1 text-xs text-amber-900 font-semibold flex items-center justify-between">
                        <span data-en="৳500,000 Capital + ৳50,000 Content/Setup" data-bn="৳৫,০০,০০০ ইনভেস্টমেন্ট + ৳৫০,০০০ কনটেন্ট ও ফি">৳500,000 Capital + ৳50,000 Content/Setup</span>
                    </div>
                </div>

                <!-- Key Metrics Grid -->
                <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-100 text-xs">
                    <div class="p-2.5 rounded-xl bg-amber-50/60 border border-amber-100">
                        <span class="text-[11px] text-slate-500 block" data-en="Weekly Return (100 Wks)" data-bn="সাপ্তাহিক রিটার্ন (১০০ সপ্তাহ)">Weekly Return (100 Wks)</span>
                        <span class="font-bold text-amber-800">2.0% (৳10,000/wk)</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] text-slate-500 block" data-en="Total Plan Return" data-bn="মোট প্ল্যান রিটার্ন">Total Plan Return</span>
                        <span class="font-bold text-slate-900">৳10,00,000 BDT</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] text-slate-500 block" data-en="Spot Commission" data-bn="স্পট ডিরেক্ট">Spot Commission</span>
                        <span class="font-bold text-slate-900">10% (৳55,000)</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] text-slate-500 block" data-en="Crowdfunding Limit" data-bn="ক্রাউডফান্ডিং">Crowdfunding Limit</span>
                        <span class="font-bold text-emerald-700" data-en="Up to ৳50 Lac" data-bn="৫০ লাখ টাকা পর্যন্ত">Up to ৳50 Lac</span>
                    </div>
                </div>

                <!-- Benefits List (4 initial + expander) -->
                <div class="mt-5 space-y-2.5 flex-1">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500" data-en="Core Deliverables:" data-bn="মূল সুবিধাসমূহ:">Core Deliverables:</p>
                    <ul class="space-y-2 text-xs text-slate-700">
                        <li class="flex items-start gap-2">
                            <span class="text-amber-600 font-bold shrink-0 mt-0.5">✓</span>
                            <span data-en="Dedicated Full-Time Project Management Team" data-bn="সার্বক্ষণিক ডেডিকেটেড এক্সপার্ট প্রজেক্ট টিম">Dedicated Full-Time Project Management Team</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-600 font-bold shrink-0 mt-0.5">✓</span>
                            <span data-en="Global Shopify Store with Multi-Currency Checkout" data-bn="আন্তর্জাতিক মাল্টি-কারেন্সি শপিফাই মার্কেটপ্লেস সেটআপ">Global Shopify Store with Multi-Currency Checkout</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-600 font-bold shrink-0 mt-0.5">✓</span>
                            <span data-en="Weekly 2.0% Plan-based Return on Capital (100 Weeks)" data-bn="ক্যাপিটালের ওপর সাপ্তাহিক ২.০% প্ল্যান-ভিত্তিক রিটার্ন (১০০ সপ্তাহ)">Weekly 2.0% Plan-based Return on Capital (100 Weeks)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-600 font-bold shrink-0 mt-0.5">✓</span>
                            <span data-en="Unlimited High-Converting UGC Video Creatives & Ads" data-bn="আনলিমিটেড হাই-কনভার্টিং ইউজার ভিডিও ও ক্রিয়েটিভ প্রডাকশন">Unlimited High-Converting UGC Video Creatives & Ads</span>
                        </li>

                        <!-- Hidden extra benefits toggled via Alpine -->
                        <template x-if="expandedBenefits.international">
                            <div class="space-y-2 pt-1 border-t border-dashed border-amber-200">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold shrink-0 mt-0.5">✓</span>
                                    <span data-en="Post-100 Weeks Monthly Profit Sharing (৳25k - ৳100k)" data-bn="১০০ সপ্তাহ পর আজীবন এক্সিকিউটিভ প্রফিট শেয়ারিং (২৫হাজার - ১লাখ)">Post-100 Weeks Monthly Profit Sharing (৳25k - ৳100k)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold shrink-0 mt-0.5">✓</span>
                                    <span data-en="Business Crowdfunding Opportunity up to ৳50 Lac BDT" data-bn="৫০ লাখ টাকা পর্যন্ত আন্তর্জাতিক ক্রাউডফান্ডিং সম্প্রসারণ সুবিধা">Business Crowdfunding Opportunity up to ৳50 Lac BDT</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold shrink-0 mt-0.5">✓</span>
                                    <span data-en="500 BV for Executive Rank & Leadership Incentives" data-bn="টপ-লেভেল লিডারশিপ ও র‍্যাংক অর্জনের জন্য ৫০০ BV পয়েন্ট">500 BV for Executive Rank & Leadership Incentives</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold shrink-0 mt-0.5">✓</span>
                                    <span data-en="Direct Global Merchant Sourcing & Overseas Fulfillment" data-bn="গ্লোবাল মার্চেন্ট সোর্সিং ও আন্তর্জাতিক ফুলফিলমেন্ট সাপোর্ট">Direct Global Merchant Sourcing & Overseas Fulfillment</span>
                                </li>
                            </div>
                        </template>
                    </ul>

                    <!-- Expand / Collapse Button -->
                    <button 
                        type="button"
                        @click="expandedBenefits.international = !expandedBenefits.international"
                        class="text-[11px] font-bold text-amber-700 hover:text-amber-800 inline-flex items-center gap-1 mt-1 cursor-pointer"
                    >
                        <span x-text="expandedBenefits.international ? '− Show fewer benefits' : '+ View all 8 benefits'"></span>
                    </button>
                </div>

                <!-- Action CTAs -->
                <div class="mt-6 space-y-2.5 pt-4 border-t border-slate-100">
                    <button 
                        type="button"
                        @click="openLeadModal('international')"
                        class="w-full py-3.5 px-4 rounded-xl bg-amber-700 hover:bg-amber-600 text-white font-bold text-sm transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer min-h-[44px]"
                    >
                        <span data-en="Interested • Choose International" data-bn="আগ্রহী • আন্তর্জাতিক নির্বাচন করুন">Interested • Choose International</span>
                        <span>→</span>
                    </button>

                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            type="button"
                            @click="openDetails('international')"
                            class="py-2 px-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-950 font-bold text-xs transition-colors flex items-center justify-center gap-1 cursor-pointer min-h-[44px]"
                        >
                            <span>🔍</span>
                            <span data-en="Details" data-bn="বিস্তারিত">Details</span>
                        </button>
                        <button 
                            type="button"
                            @click="scrollToCalculator('international')"
                            class="py-2 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs transition-colors flex items-center justify-center gap-1 cursor-pointer min-h-[44px]"
                        >
                            <span>📊</span>
                            <span data-en="ROI" data-bn="রিটার্ন">ROI</span>
                        </button>
                        <button 
                            type="button"
                            @click="openShareModal('international')"
                            class="py-2 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors flex items-center justify-center gap-1 cursor-pointer min-h-[44px]"
                        >
                            <span>📤</span>
                            <span data-en="Share" data-bn="শেয়ার">Share</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- 04. QUICK PACKAGE COMPARISON ("COMPARE PACKAGES") -->
    <div id="package-comparison" class="space-y-4 pt-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200">
            <div>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900" data-en="Side-by-Side Package Comparison" data-bn="প্যাকেজগুলোর পাশাপাশি পূর্ণ তুলনা">
                    Side-by-Side Package Comparison
                </h2>
                <p class="text-xs text-slate-500 mt-0.5" data-en="Quickly evaluate pricing, returns, support, and business volume across all 3 tiers" data-bn="৩টি প্যাকেজের মূল্য, রিটার্ন, সুযোগ-সুবিধা ও পয়েন্ট একনজরে তুলনা করুন">
                    Quickly evaluate pricing, returns, support, and business volume across all 3 tiers
                </p>
            </div>
            
            <!-- Mobile Selector Switch for Clean Viewing -->
            <div class="flex sm:hidden items-center gap-1 bg-slate-100 p-1 rounded-xl self-start">
                <button 
                    type="button"
                    @click="comparePkgMobile = 'starter'"
                    :class="comparePkgMobile === 'starter' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all"
                >Starter</button>
                <button 
                    type="button"
                    @click="comparePkgMobile = 'national'"
                    :class="comparePkgMobile === 'national' ? 'bg-orange-600 text-white font-bold shadow-xs' : 'text-slate-600'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all"
                >National</button>
                <button 
                    type="button"
                    @click="comparePkgMobile = 'international'"
                    :class="comparePkgMobile === 'international' ? 'bg-amber-700 text-white font-bold shadow-xs' : 'text-slate-600'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all"
                >International</button>
            </div>
        </div>

        <!-- Desktop Comparison Table (Hidden on Mobile to prevent squishing) -->
        <div class="hidden md:block bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="py-4 px-5 text-slate-500 font-bold uppercase tracking-wider w-1/4" data-en="Feature / Parameter" data-bn="ফিচার / বিবরণ">Feature / Parameter</th>
                            <th class="py-4 px-5 font-bold text-slate-900 w-1/4">
                                <div>Starter Membership</div>
                                <div class="text-[11px] text-slate-500 font-normal">৳10,000 BDT</div>
                            </th>
                            <th class="py-4 px-5 font-black text-orange-950 bg-orange-50/80 border-x-2 border-orange-400 w-1/4 relative">
                                <div class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-orange-600 text-white mb-1">
                                    ⭐ Popular
                                </div>
                                <div>National Dropshipping</div>
                                <div class="text-[11px] text-orange-800 font-semibold">৳120,000 (100 BV)</div>
                            </th>
                            <th class="py-4 px-5 font-bold text-amber-950 w-1/4">
                                <div>International Dropshipping</div>
                                <div class="text-[11px] text-amber-800 font-normal">৳550,000 (500 BV)</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-bold text-slate-900" data-en="Total Investment" data-bn="মোট বিনিয়োগ">Total Investment</td>
                            <td class="py-3 px-5 font-semibold text-slate-800">৳10,000 BDT</td>
                            <td class="py-3 px-5 font-extrabold text-orange-950 bg-orange-50/40 border-x-2 border-orange-400">৳120,000 BDT</td>
                            <td class="py-3 px-5 font-extrabold text-amber-950">৳550,000 BDT</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-medium text-slate-600" data-en="Capital vs Setup Fee" data-bn="মূলধন বনাম সেটআপ ফি">Capital vs Setup Fee</td>
                            <td class="py-3 px-5 text-slate-600">৳10,000 Setup</td>
                            <td class="py-3 px-5 text-slate-800 bg-orange-50/40 border-x-2 border-orange-400 font-medium">৳100,000 Capital + ৳20,000 Setup</td>
                            <td class="py-3 px-5 text-slate-800 font-medium">৳500,000 Capital + ৳50,000 Setup</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-medium text-slate-600" data-en="Business Volume (BV)" data-bn="বিজনেস ভলিউম (BV)">Business Volume (BV)</td>
                            <td class="py-3 px-5 text-slate-500">Entry Tier</td>
                            <td class="py-3 px-5 font-bold text-orange-700 bg-orange-50/40 border-x-2 border-orange-400">100 BV</td>
                            <td class="py-3 px-5 font-bold text-amber-800">500 BV</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-medium text-slate-600" data-en="Package Duration" data-bn="মেয়াদকাল">Package Duration</td>
                            <td class="py-3 px-5 text-slate-800 font-medium">Lifetime</td>
                            <td class="py-3 px-5 font-bold text-slate-900 bg-orange-50/40 border-x-2 border-orange-400">100 Weeks (24 Mos)</td>
                            <td class="py-3 px-5 font-bold text-slate-900">100 Weeks (24 Mos)</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-bold text-slate-900" data-en="Plan-based Weekly Return" data-bn="প্ল্যান-ভিত্তিক সাপ্তাহিক রিটার্ন">Plan-based Weekly Return</td>
                            <td class="py-3 px-5 text-slate-500">Affiliate Commissions</td>
                            <td class="py-3 px-5 font-black text-orange-700 bg-orange-50/40 border-x-2 border-orange-400">1.75% / week (৳1,750)</td>
                            <td class="py-3 px-5 font-black text-amber-800">2.0% / week (৳10,000)</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-medium text-slate-600" data-en="Total 100-Wk Plan Return" data-bn="মোট ১০০ সপ্তাহের রিটার্ন">Total 100-Wk Plan Return</td>
                            <td class="py-3 px-5 text-slate-500">Performance Based</td>
                            <td class="py-3 px-5 font-bold text-slate-900 bg-orange-50/40 border-x-2 border-orange-400">৳1,75,000 BDT</td>
                            <td class="py-3 px-5 font-bold text-slate-900">৳10,00,000 BDT</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-medium text-slate-600" data-en="Spot Direct Commission" data-bn="স্পট ডিরেক্ট রেফারেল">Spot Direct Commission</td>
                            <td class="py-3 px-5 font-semibold text-slate-800">10% (৳1,000)</td>
                            <td class="py-3 px-5 font-bold text-orange-800 bg-orange-50/40 border-x-2 border-orange-400">10% (৳12,000)</td>
                            <td class="py-3 px-5 font-bold text-amber-900">10% (৳55,000)</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-medium text-slate-600" data-en="Shopify E-Commerce Store" data-bn="শপিফাই ই-কমার্স স্টোর">Shopify E-Commerce Store</td>
                            <td class="py-3 px-5 text-slate-400">−</td>
                            <td class="py-3 px-5 text-emerald-700 font-bold bg-orange-50/40 border-x-2 border-orange-400">✓ National Shopify</td>
                            <td class="py-3 px-5 text-emerald-700 font-bold">✓ Multi-Currency Global</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-medium text-slate-600" data-en="Product Sourcing & Packaging" data-bn="সোর্সিং ও প্যাকেজিং">Product Sourcing & Packaging</td>
                            <td class="py-3 px-5 text-slate-500">Affiliate Catalog</td>
                            <td class="py-3 px-5 text-emerald-700 font-bold bg-orange-50/40 border-x-2 border-orange-400">✓ Verified SBL Packaging</td>
                            <td class="py-3 px-5 text-emerald-700 font-bold">✓ Global Direct Sourcing</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-medium text-slate-600" data-en="Marketing & Video Ads" data-bn="মার্কেটিং ও ভিডিও অ্যাড">Marketing & Video Ads</td>
                            <td class="py-3 px-5 text-slate-500">Promotional Assets</td>
                            <td class="py-3 px-5 text-emerald-700 font-bold bg-orange-50/40 border-x-2 border-orange-400">✓ Campaign Setup & Moderation</td>
                            <td class="py-3 px-5 text-emerald-700 font-bold">✓ Unlimited UGC Video Creatives</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-medium text-slate-600" data-en="Post-100 Wks Profit Sharing" data-bn="১০০ সপ্তাহ পর প্রফিট শেয়ার">Post-100 Wks Profit Sharing</td>
                            <td class="py-3 px-5 text-slate-400">−</td>
                            <td class="py-3 px-5 font-bold text-orange-800 bg-orange-50/40 border-x-2 border-orange-400">৳5,000 to ৳20,000 / mo</td>
                            <td class="py-3 px-5 font-bold text-amber-900">৳25,000 to ৳100,000 / mo</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-medium text-slate-600" data-en="Crowdfunding Limit" data-bn="ক্রাউডফান্ডিং সীমা">Crowdfunding Limit</td>
                            <td class="py-3 px-5 text-slate-400">Not Eligible</td>
                            <td class="py-3 px-5 font-bold text-emerald-700 bg-orange-50/40 border-x-2 border-orange-400">Up to ৳10 Lac BDT</td>
                            <td class="py-3 px-5 font-bold text-emerald-700">Up to ৳50 Lac BDT</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                        <tr>
                            <td class="py-4 px-5 font-bold text-slate-500 uppercase">Action</td>
                            <td class="py-4 px-5">
                                <button type="button" @click="openLeadModal('starter')" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-all cursor-pointer min-h-[44px]">Select Starter</button>
                            </td>
                            <td class="py-4 px-5 bg-orange-50/80 border-x-2 border-orange-400">
                                <button type="button" @click="openLeadModal('national')" class="w-full px-4 py-2.5 rounded-xl bg-orange-600 text-white font-black text-xs hover:bg-orange-500 shadow-sm transition-all cursor-pointer min-h-[44px]">Select National</button>
                            </td>
                            <td class="py-4 px-5">
                                <button type="button" @click="openLeadModal('international')" class="px-4 py-2 rounded-xl bg-amber-700 text-white font-bold text-xs hover:bg-amber-600 transition-all cursor-pointer min-h-[44px]">Select International</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Mobile Clean Comparison Card View (Optimized for 360px-430px) -->
        <div class="block md:hidden bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <span class="text-xs text-slate-500 uppercase tracking-wider font-bold block" data-en="Selected Package" data-bn="নির্বাচিত প্যাকেজ">Selected Package</span>
                    <h3 class="text-lg font-black text-slate-900" x-text="packages[comparePkgMobile].name"></h3>
                </div>
                <div class="text-right">
                    <span class="text-xl font-black text-orange-600" x-text="packages[comparePkgMobile].price_formatted"></span>
                    <span class="text-[11px] text-slate-500 block" x-text="packages[comparePkgMobile].bv_formatted"></span>
                </div>
            </div>

            <!-- Comparison Rows -->
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500" data-en="Capital vs Setup:" data-bn="ক্যাপিটাল ও সেটআপ:">Capital vs Setup:</span>
                    <span class="font-bold text-slate-800 text-right" x-text="packages[comparePkgMobile].setup_fee_formatted"></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500" data-en="Plan-based Weekly Return:" data-bn="সাপ্তাহিক প্ল্যান রিটার্ন:">Plan-based Weekly Return:</span>
                    <span class="font-extrabold text-orange-700 text-right" x-text="packages[comparePkgMobile].weekly_return_text"></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500" data-en="Total 100-Wk Return:" data-bn="মোট ১০০ সপ্তাহের রিটার্ন:">Total 100-Wk Return:</span>
                    <span class="font-bold text-slate-900 text-right" x-text="packages[comparePkgMobile].total_plan_return_formatted || 'Performance based'"></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500" data-en="Spot Direct Commission:" data-bn="স্পট ডিরেক্ট কমিশন:">Spot Direct Commission:</span>
                    <span class="font-bold text-slate-800 text-right" x-text="packages[comparePkgMobile].direct_commission"></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500" data-en="Crowdfunding Eligibility:" data-bn="ক্রাউডফান্ডিং সীমা:">Crowdfunding Eligibility:</span>
                    <span class="font-bold text-emerald-700 text-right" x-text="packages[comparePkgMobile].crowdfunding_formatted"></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500" data-en="Post-100 Wks Profit Share:" data-bn="১০০ সপ্তাহ পর প্রফিট:">Post-100 Wks Profit Share:</span>
                    <span class="font-bold text-slate-800 text-right" x-text="packages[comparePkgMobile].profit_sharing"></span>
                </div>
            </div>

            <div class="pt-2">
                <button 
                    type="button"
                    @click="openLeadModal(comparePkgMobile)"
                    class="w-full py-3 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-1 min-h-[44px]"
                >
                    <span data-en="Interested in This Package" data-bn="এই প্যাকেজে আগ্রহী">Interested in This Package</span>
                    <span>→</span>
                </button>
            </div>
        </div>
    </div>


    <!-- 06. INTERACTIVE ROI CALCULATOR (Minimal, Clean, Safe Estimates) -->
    <div id="roi-calculator" class="space-y-4 pt-6">
        <div class="bg-slate-900 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-6 sm:p-8 text-white border border-slate-700 shadow-xl relative overflow-hidden">
            <div class="absolute -right-24 -bottom-24 w-80 h-80 rounded-full bg-orange-500/10 blur-3xl pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-8 relative z-10">
                <!-- Left: Controls -->
                <div class="space-y-5 lg:w-1/2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/20 text-orange-300 border border-orange-500/30 text-xs font-semibold">
                        <span>📊</span>
                        <span data-en="Interactive ROI Calculator" data-bn="ইন্টারেক্টিভ রিটার্ন ক্যালকুলেটর">Interactive ROI Calculator</span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white leading-tight">
                        <span data-en="Estimate Your Plan-Based Dropshipping Returns" data-bn="আপনার সম্ভাব্য প্ল্যান-ভিত্তিক রিটার্ন গণনা করুন">Estimate Your Plan-Based Dropshipping Returns</span>
                    </h2>

                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        <span data-en="Select any SBL package to calculate weekly returns, monthly estimates, and 100-week totals according to current official terms." data-bn="আপনার পছন্দের প্যাকেজ নির্বাচন করে বর্তমান অফিসিয়াল নিয়মে সাপ্তাহিক ও বাৎসরিক সম্ভাব্য রিটার্ন হিসাব করুন।">
                            Select any SBL package to calculate weekly returns, monthly estimates, and 100-week totals according to current official terms.
                        </span>
                    </p>

                    <!-- Package Select Tabs -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-400 block" data-en="Choose SBL Package:" data-bn="প্যাকেজ নির্বাচন করুন:">Choose SBL Package:</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button 
                                type="button"
                                @click="calculator.selectedPkgId = 'starter'; calculator.customAmount = packages.starter.price"
                                :class="calculator.selectedPkgId === 'starter' ? 'bg-white text-slate-900 font-extrabold shadow-sm' : 'bg-white/10 text-white hover:bg-white/15 border border-white/10'"
                                class="py-2.5 px-3 rounded-xl text-xs transition-all cursor-pointer min-h-[44px]"
                            >
                                Starter (৳10k)
                            </button>
                            <button 
                                type="button"
                                @click="calculator.selectedPkgId = 'national'; calculator.customAmount = packages.national.price"
                                :class="calculator.selectedPkgId === 'national' ? 'bg-orange-600 text-white font-extrabold shadow-md shadow-orange-600/30' : 'bg-white/10 text-white hover:bg-white/15 border border-white/10'"
                                class="py-2.5 px-3 rounded-xl text-xs transition-all cursor-pointer min-h-[44px]"
                            >
                                National (৳120k)
                            </button>
                            <button 
                                type="button"
                                @click="calculator.selectedPkgId = 'international'; calculator.customAmount = packages.international.price"
                                :class="calculator.selectedPkgId === 'international' ? 'bg-amber-600 text-white font-extrabold shadow-sm' : 'bg-white/10 text-white hover:bg-white/15 border border-white/10'"
                                class="py-2.5 px-3 rounded-xl text-xs transition-all cursor-pointer min-h-[44px]"
                            >
                                International (৳550k)
                            </button>
                        </div>
                    </div>

                    <!-- Investment Amount Display -->
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400" data-en="Total Investment Value:" data-bn="মোট বিনিয়োগ মূল্য:">Total Investment Value:</span>
                            <span class="text-lg font-black text-white" x-text="'৳' + calculator.customAmount.toLocaleString() + ' BDT'"></span>
                        </div>
                        <div class="text-[11px] text-slate-400">
                            <span data-en="Capital Breakdown:" data-bn="মূলধন বিভাজন:">Capital Breakdown:</span> 
                            <span class="text-orange-300 font-semibold" x-text="calculator.activePkg.setup_fee_formatted"></span>
                        </div>
                    </div>
                </div>

                <!-- Right: Metric Calculation Cards -->
                <div class="lg:w-1/2 flex flex-col justify-between space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Weekly Return -->
                        <div class="p-4 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-md">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-orange-300 block" data-en="Weekly Plan Return" data-bn="সাপ্তাহিক প্ল্যান রিটার্ন">Weekly Plan Return</span>
                            <div class="mt-1 flex items-baseline gap-1">
                                <span class="text-2xl sm:text-3xl font-black text-white" x-text="calculator.weeklyReturn > 0 ? '৳' + calculator.weeklyReturn.toLocaleString() : 'N/A'"></span>
                            </div>
                            <span class="text-[10px] text-slate-400 block mt-0.5" x-text="calculator.activePkg.weekly_return_percent > 0 ? calculator.activePkg.weekly_return_percent + '% weekly on capital' : 'Performance commissions'"></span>
                        </div>

                        <!-- Monthly Estimate -->
                        <div class="p-4 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-md">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-300 block" data-en="Monthly Estimate" data-bn="মাসিক আনুমানিক">Monthly Estimate</span>
                            <div class="mt-1 flex items-baseline gap-1">
                                <span class="text-2xl sm:text-3xl font-black text-white" x-text="calculator.monthlyReturn > 0 ? '৳' + calculator.monthlyReturn.toLocaleString() : 'N/A'"></span>
                            </div>
                            <span class="text-[10px] text-slate-400 block mt-0.5" data-en="Weekly × 4.33 weeks" data-bn="সাপ্তাহিক × ৪.৩৩ সপ্তাহ">Weekly × 4.33 weeks</span>
                        </div>

                        <!-- 100-Week Total Return -->
                        <div class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 backdrop-blur-md">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-300 block" data-en="Total 100-Week Return" data-bn="১০০ সপ্তাহে মোট রিটার্ন">Total 100-Week Return</span>
                            <div class="mt-1 flex items-baseline gap-1">
                                <span class="text-2xl sm:text-3xl font-black text-emerald-400" x-text="calculator.totalReturn > 0 ? '৳' + calculator.totalReturn.toLocaleString() : 'Performance based'"></span>
                            </div>
                            <span class="text-[10px] text-emerald-200/60 block mt-0.5" data-en="Full 24-month return cycle" data-bn="সম্পূর্ণ ২৪ মাসের রিটার্ন চক্র">Full 24-month return cycle</span>
                        </div>

                        <!-- Post-100 Weeks Profit Sharing -->
                        <div class="p-4 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-md">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-orange-300 block" data-en="Post-100 Weeks Share" data-bn="১০০ সপ্তাহ পর শেয়ারিং">Post-100 Weeks Share</span>
                            <div class="mt-1 text-sm sm:text-base font-extrabold text-white leading-snug" x-text="calculator.activePkg.profit_sharing"></div>
                            <span class="text-[10px] text-slate-400 block mt-0.5" data-en="Ongoing monthly business share" data-bn="আজীবন মাসিক ব্যবসা লভ্যাংশ">Ongoing monthly business share</span>
                        </div>
                    </div>

                    <!-- Clean Estimate Disclaimer -->
                    <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 text-[11px] text-slate-400 leading-relaxed flex items-start gap-2">
                        <span class="text-amber-400 text-sm shrink-0">⚠️</span>
                        <div>
                            <strong class="text-white font-semibold" data-en="Plan-Based Estimate Disclaimer:" data-bn="প্ল্যান-ভিত্তিক আনুমানিক বিবরণ:">Plan-Based Estimate Disclaimer:</strong>
                            <span data-en="Returns are calculated based on current official SBL plan terms. Projections represent business estimates and are not guaranteed fixed profits. Subject to SBL terms and active compliance." data-bn="উক্ত রিটার্ন বর্তমান SBL বিজনেস প্ল্যান অনুযায়ী হিসাবকৃত। এটি কোনো ফিক্সড ডিপোজিট বা গ্যারান্টেড প্রফিট নয়, কোম্পানির প্রচলিত নিয়মানুযায়ী ব্যবসায়িক রিটার্ন।">
                                Returns are calculated based on current official SBL plan terms. Projections represent business estimates and are not guaranteed fixed profits. Subject to SBL terms and active compliance.
                            </span>
                        </div>
                    </div>

                    <!-- CTA -->
                    <button 
                        type="button"
                        @click="openLeadModal(calculator.selectedPkgId)"
                        class="w-full py-3.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-extrabold text-sm shadow-lg shadow-orange-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer min-h-[44px]"
                    >
                        <span data-en="Get Started with" data-bn="শুরু করুন:">Get Started with</span>
                        <span x-text="calculator.activePkg.name"></span>
                        <span>→</span>
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- 07. BUSINESS COST COMPARISON (Dropshipping Market Cost Comparison) -->
    <div class="space-y-4 pt-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200">
            <div>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900" data-en="Dropshipping Market Cost Comparison" data-bn="ড্রপশিপিং মার্কেট খরচ বনাম SBL অফার">
                    Dropshipping Market Cost Comparison
                </h2>
                <p class="text-xs text-slate-500 mt-0.5" data-en="Standard agency outsourcing rates compared to SBL lifetime turnkey service" data-bn="বাইরের বিভিন্ন এজেন্সি থেকে সার্ভিস নেওয়ার খরচের সাথে SBL এর সমন্বিত সুবিধার তুলনা">
                    Standard agency outsourcing rates compared to SBL lifetime turnkey service
                </p>
            </div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-black self-start">
                <span>✓</span>
                <span data-en="96% Commercial Cost Reduction" data-bn="৯৬% মার্কেট খরচ সাশ্রয়">96% Commercial Cost Reduction</span>
            </div>
        </div>

        <!-- Desktop Market Comparison Table -->
        <div class="hidden md:block bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider" data-en="Service / Feature Deliverable" data-bn="সার্ভিস / ডেলিভারেবল">Service / Feature Deliverable</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider" data-en="Standard Agency Cost vs SBL" data-bn="মার্কেট রেট বনাম SBL">Standard Agency Cost vs SBL</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($marketData as $row)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-6 font-medium text-slate-800 flex items-center gap-2.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                                    <span data-en="{{ $row['service'] }}" data-bn="{{ $row['service_bn'] ?? $row['service'] }}">{{ $row['service'] }}</span>
                                </td>
                                <td class="py-3 px-6 text-right text-rose-600 font-semibold whitespace-nowrap">
                                    {{ $row['market'] }}
                                </td>
                                <td class="py-3 px-6 text-right whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        ✓ {{ $row['sbl'] ?? 'Included' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50/90 border-t-2 border-slate-200 font-bold text-xs text-slate-900">
                        <tr>
                            <td class="py-4 px-6 uppercase tracking-wider text-slate-500" data-en="Cumulative Market Outsourcing Total" data-bn="মার্কেট থেকে আলাদা নেওয়ার আনুমানিক মোট খরচ">Cumulative Market Outsourcing Total</td>
                            <td class="py-4 px-6 text-right text-rose-600 font-black text-sm whitespace-nowrap">৳5,04,000+ BDT</td>
                            <td class="py-4 px-6 text-right text-emerald-800 font-black text-sm whitespace-nowrap">
                                ৳20,000 BDT (Lifetime)
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Mobile Card Rows for Market Comparison (Prevents wide horizontal scrolling) -->
        <div class="block md:hidden space-y-2.5">
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-900 flex items-center justify-between">
                <span data-en="Cumulative Market Cost:" data-bn="মার্কেট মোট খরচ:">Cumulative Market Cost:</span>
                <span class="text-rose-600 font-black">৳5,04,000+ BDT</span>
            </div>
            <div class="p-3.5 rounded-2xl bg-orange-50 border border-orange-200 text-xs font-bold text-orange-950 flex items-center justify-between">
                <span data-en="SBL Lifetime Dropshipping Setup:" data-bn="SBL লাইফটাইম সেটআপ ফি:">SBL Lifetime Dropshipping Setup:</span>
                <span class="text-emerald-800 font-black">Only ৳20,000 BDT</span>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100 overflow-hidden shadow-xs">
                @foreach ($marketData as $row)
                    <div class="p-3.5 flex items-start justify-between gap-3 text-xs">
                        <div class="space-y-0.5">
                            <p class="font-medium text-slate-800" data-en="{{ $row['service'] }}" data-bn="{{ $row['service_bn'] ?? $row['service'] }}">{{ $row['service'] }}</p>
                            <span class="text-[11px] text-rose-600 font-semibold block">Market: {{ $row['market'] }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                            ✓ Included
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>


    <!-- 08. EXAMPLE GROWTH SCENARIO (Renamed from Growth Projection, Illustrative Scenario) -->
    <div class="space-y-4 pt-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg sm:text-xl font-extrabold text-slate-900" data-en="Example Growth Scenario" data-bn="সম্ভাব্য গ্রোথ দৃশ্যপট">
                        Example Growth Scenario
                    </h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 uppercase" data-en="Illustrative Scenario" data-bn="উদাহরণমূলক দৃশ্যপট">
                        Illustrative Scenario
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5" data-en="Progressive 6-month dropshipping scaling model at $1,000 ad allocation" data-bn="১,০০০ ডলার স্কেলিং মডেলে ৬ মাসের সম্ভাব্য এডভার্টাইজিং ও অর্ডার প্রবৃদ্ধি">
                    Progressive 6-month dropshipping scaling model at $1,000 ad allocation
                </p>
            </div>
            <span class="text-xs text-slate-500 font-medium self-start sm:self-auto" data-en="Based on optimized conversion funnel" data-bn="অপটিমাইজড ফানেলের ভিত্তিতে">Based on optimized conversion funnel</span>
        </div>

        <!-- Desktop Growth Table -->
        <div class="hidden md:block bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-6" data-en="Timeline" data-bn="সময়কাল">Timeline</th>
                            <th class="py-3.5 px-6" data-en="Ad Budget Allocation" data-bn="অ্যাড বাজেট">Ad Budget Allocation</th>
                            <th class="py-3.5 px-6" data-en="Audience Reach Size" data-bn="অডিয়েন্স রিচ">Audience Reach Size</th>
                            <th class="py-3.5 px-6 text-right" data-en="Projected Monthly Orders" data-bn="সম্ভাব্য মাসিক অর্ডার">Projected Monthly Orders</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($growthData as $tr)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-6">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-bold text-xs">
                                        {{ $tr['month'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 font-semibold text-orange-600">{{ $tr['ad_spend'] }}</td>
                                <td class="py-3.5 px-6 font-medium text-slate-700">{{ $tr['audience'] }}</td>
                                <td class="py-3.5 px-6 text-right">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-extrabold text-xs border border-emerald-200">
                                        {{ $tr['orders'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Timeline Cards for Growth -->
        <div class="block md:hidden space-y-2.5">
            @foreach ($growthData as $tr)
                <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-between gap-3 text-xs">
                    <div class="space-y-1">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 font-bold text-[11px] inline-block">
                            {{ $tr['month'] }}
                        </span>
                        <div class="text-slate-500 text-[11px]">
                            <span>Ad: <strong class="text-orange-600">{{ $tr['ad_spend'] }}</strong></span>
                            <span class="mx-1">•</span>
                            <span>{{ $tr['audience'] }}</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 font-extrabold text-xs border border-emerald-200 shrink-0">
                        {{ $tr['orders'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>


    <!-- 09. PACKAGE RECOMMENDER ("WHICH PACKAGE FITS ME?") -->
    <div class="space-y-4 pt-6">
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
            <div class="max-w-xl mx-auto text-center space-y-2 mb-6">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-100 text-orange-800 text-xs font-bold">
                    🎯 Quick Quiz (30 Seconds)
                </span>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900" data-en="Which SBL Package Fits Me Best?" data-bn="আমার জন্য কোন প্যাকেজটি সেরা?">
                    Which SBL Package Fits Me Best?
                </h2>
                <p class="text-xs text-slate-500" data-en="Answer 3 simple questions to find the ideal match for your investment & business goals." data-bn="আপনার বাজেট ও লক্ষ্যের সাথে মিলিয়ে সেরা প্যাকেজটি বেছে নিতে নিচের ৩টি প্রশ্নের উত্তর দিন।">
                    Answer 3 simple questions to find the ideal match for your investment & business goals.
                </p>
            </div>

            <!-- Quiz Steps Grid -->
            <div class="max-w-2xl mx-auto space-y-6">
                <!-- Question 1: Budget -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-800 block">
                        1. <span data-en="What is your planned investment budget?" data-bn="আপনার সম্ভাব্য বাজেট কেমন?">What is your planned investment budget?</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <button 
                            type="button"
                            @click="wizard.budget = 'low'"
                            :class="wizard.budget === 'low' ? 'bg-slate-900 text-white font-bold ring-2 ring-slate-900' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                            class="p-3 rounded-xl text-xs transition-all text-left cursor-pointer min-h-[44px]"
                        >
                            <div class="font-bold">৳10,000 - ৳50,000</div>
                            <div class="text-[11px] opacity-80" data-en="Affiliate & Network" data-bn="অ্যাফিলিয়েট ও নেটওয়ার্ক">Affiliate & Network</div>
                        </button>
                        <button 
                            type="button"
                            @click="wizard.budget = 'medium'"
                            :class="wizard.budget === 'medium' ? 'bg-orange-600 text-white font-bold ring-2 ring-orange-600 shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                            class="p-3 rounded-xl text-xs transition-all text-left cursor-pointer min-h-[44px]"
                        >
                            <div class="font-bold">৳100,000 - ৳300,000</div>
                            <div class="text-[11px] opacity-80" data-en="Domestic E-Commerce" data-bn="দেশীয় ড্রপশিপিং শপ">Domestic E-Commerce</div>
                        </button>
                        <button 
                            type="button"
                            @click="wizard.budget = 'high'"
                            :class="wizard.budget === 'high' ? 'bg-amber-700 text-white font-bold ring-2 ring-amber-700' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                            class="p-3 rounded-xl text-xs transition-all text-left cursor-pointer min-h-[44px]"
                        >
                            <div class="font-bold">৳500,000+</div>
                            <div class="text-[11px] opacity-80" data-en="Global Enterprise" data-bn="গ্লোবাল ব্র্যান্ড">Global Enterprise</div>
                        </button>
                    </div>
                </div>

                <!-- Question 2: Business Goal -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-800 block">
                        2. <span data-en="What is your primary objective?" data-bn="আপনার প্রধান লক্ষ্য কী?">What is your primary objective?</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <button 
                            type="button"
                            @click="wizard.goal = 'affiliate'"
                            :class="wizard.goal === 'affiliate' ? 'bg-slate-900 text-white font-bold ring-2 ring-slate-900' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                            class="p-3 rounded-xl text-xs transition-all text-left cursor-pointer min-h-[44px]"
                        >
                            <div class="font-bold" data-en="Referral Sales" data-bn="সেলস রেফারেল">Referral Sales</div>
                            <div class="text-[11px] opacity-80" data-en="Earn commissions on deals" data-bn="কমিশন ও টিম বোনাস">Earn commissions on deals</div>
                        </button>
                        <button 
                            type="button"
                            @click="wizard.goal = 'dropshipping'"
                            :class="wizard.goal === 'dropshipping' ? 'bg-orange-600 text-white font-bold ring-2 ring-orange-600 shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                            class="p-3 rounded-xl text-xs transition-all text-left cursor-pointer min-h-[44px]"
                        >
                            <div class="font-bold" data-en="National Online Store" data-bn="দেশীয় শপিফাই স্টোর">National Online Store</div>
                            <div class="text-[11px] opacity-80" data-en="Turnkey Shopify & returns" data-bn="রেডি স্টোর ও রিটার্ন">Turnkey Shopify & returns</div>
                        </button>
                        <button 
                            type="button"
                            @click="wizard.goal = 'global'"
                            :class="wizard.goal === 'global' ? 'bg-amber-700 text-white font-bold ring-2 ring-amber-700' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                            class="p-3 rounded-xl text-xs transition-all text-left cursor-pointer min-h-[44px]"
                        >
                            <div class="font-bold" data-en="Cross-Border Global" data-bn="আন্তর্জাতিক ব্যবসা">Cross-Border Global</div>
                            <div class="text-[11px] opacity-80" data-en="Worldwide sales & team" data-bn="মাল্টি-কারেন্সি টিম">Worldwide sales & team</div>
                        </button>
                    </div>
                </div>

                <!-- Recommendation Result Box -->
                <div class="p-5 rounded-2xl bg-orange-50 bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-orange-700 block" data-en="Recommended For You:" data-bn="আপনার জন্য প্রস্তাবিত প্যাকেজ:">Recommended For You:</span>
                        <h4 class="text-lg font-black text-slate-900" x-text="wizard.recommendedPkg.name"></h4>
                        <p class="text-xs text-slate-600" x-text="wizard.recommendedPkg.subtitle"></p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button 
                            type="button"
                            @click="openDetails(wizard.recommendedPkgId)"
                            class="px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs font-bold hover:bg-slate-50 transition-colors cursor-pointer min-h-[44px]"
                        >
                            <span data-en="View Details" data-bn="বিস্তারিত">View Details</span>
                        </button>
                        <button 
                            type="button"
                            @click="openLeadModal(wizard.recommendedPkgId)"
                            class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold shadow-sm transition-all cursor-pointer min-h-[44px]"
                        >
                            <span data-en="Interested • Join" data-bn="আগ্রহী • শুরু করুন">Interested • Join</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- 10. VERIFIED FAQ ACCORDION -->
    <div class="space-y-4 pt-6">
        <div class="pb-2 border-b border-slate-200">
            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900" data-en="Frequently Asked Questions" data-bn="সাধারণ জিজ্ঞাসা ও উত্তর (FAQ)">
                Frequently Asked Questions
            </h2>
            <p class="text-xs text-slate-500 mt-0.5" data-en="Clear answers regarding SBL package durations, returns, commissions, and crowdfunding" data-bn="প্যাকেজ মেয়াদ, রিটার্ন কাঠামো, কমিশন ও ক্রাউডফান্ডিং সংক্রান্ত জরুরি প্রশ্নের উত্তর">
                Clear answers regarding SBL package durations, returns, commissions, and crowdfunding
            </p>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1: What is BV -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <button 
                    type="button"
                    @click="toggleFaq(1)"
                    class="w-full p-4 sm:p-5 text-left font-bold text-xs sm:text-sm text-slate-900 flex items-center justify-between gap-3 cursor-pointer"
                >
                    <span data-en="1. What is BV (Business Volume) in SBL packages?" data-bn="১. SBL প্যাকেজে BV (Business Volume) কী?">1. What is BV (Business Volume) in SBL packages?</span>
                    <span class="text-slate-400 font-mono text-base" x-text="openFaq === 1 ? '−' : '+'">+</span>
                </button>
                <div x-show="openFaq === 1" x-collapse class="px-4 sm:px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    <span data-en="BV represents Business Volume points allocated to packages (e.g. 100 BV for National, 500 BV for International). It determines your career ranking progression (such as FME, SME, etc.), binary matching bonus eligibility, and team performance incentives." data-bn="BV হলো বিজনেস ভলিউম পয়েন্ট (যেমন: ন্যাশনালে ১০০ BV, আন্তর্জাতিকে ৫০০ BV)। এটি আপনার ক্যারিয়ার র‍্যাংক পদোন্নতি (FME, SME ইত্যাদি), বাইনারি ম্যাচিং ইনকাম এবং টিম লিডারশিপ বোনাস নির্ধারণে ভূমিকা রাখে।">
                        BV represents Business Volume points allocated to packages (e.g. 100 BV for National, 500 BV for International). It determines your career ranking progression (such as FME, SME, etc.), binary matching bonus eligibility, and team performance incentives.
                    </span>
                </div>
            </div>

            <!-- FAQ 2: Duration -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <button 
                    type="button"
                    @click="toggleFaq(2)"
                    class="w-full p-4 sm:p-5 text-left font-bold text-xs sm:text-sm text-slate-900 flex items-center justify-between gap-3 cursor-pointer"
                >
                    <span data-en="2. What is the active duration of the dropshipping packages?" data-bn="২. ড্রপশিপিং প্যাকেজের মেয়াদকাল কতদিন?">2. What is the active duration of the dropshipping packages?</span>
                    <span class="text-slate-400 font-mono text-base" x-text="openFaq === 2 ? '−' : '+'">+</span>
                </button>
                <div x-show="openFaq === 2" x-collapse class="px-4 sm:px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    <span data-en="SBL Dropshipping packages have a primary term of 100 weeks (approx. 24 months) for the capital return cycle. Following 100 weeks, eligible members transition into ongoing monthly profit sharing according to company terms." data-bn="SBL ড্রপশিপিং প্যাকেজের ক্যাপিটাল রিটার্ন চক্র হলো ১০০ সপ্তাহ (প্রায় ২৪ মাস)। ১০০ সপ্তাহ অতিক্রান্ত হওয়ার পর যোগ্যতা অর্জনকারী সদস্যরা আজীবন মাসিক প্রফিট শেয়ারিং সুবিধা পেয়ে থাকেন।">
                        SBL Dropshipping packages have a primary term of 100 weeks (approx. 24 months) for the capital return cycle. Following 100 weeks, eligible members transition into ongoing monthly profit sharing according to company terms.
                    </span>
                </div>
            </div>

            <!-- FAQ 3: Spot Direct Commission -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <button 
                    type="button"
                    @click="toggleFaq(3)"
                    class="w-full p-4 sm:p-5 text-left font-bold text-xs sm:text-sm text-slate-900 flex items-center justify-between gap-3 cursor-pointer"
                >
                    <span data-en="3. How does Spot Direct Commission work?" data-bn="৩. স্পট ডিরেক্ট রেফারেল কমিশন কীভাবে দেওয়া হয়?">3. How does Spot Direct Commission work?</span>
                    <span class="text-slate-400 font-mono text-base" x-text="openFaq === 3 ? '−' : '+'">+</span>
                </button>
                <div x-show="openFaq === 3" x-collapse class="px-4 sm:px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    <span data-en="Upon activating any package through your direct referral link or member code, you receive an instant 10% direct incentive (e.g. ৳1,000 for Starter, ৳12,000 for National, ৳55,000 for International) credited directly to your affiliate balance." data-bn="আপনার রেফারেন্স বা মেম্বার কোডের মাধ্যমে কোনো নতুন প্যাকেজ অ্যাক্টিভ হলে সাথে সাথে ১০% স্পট রেফারেল কমিশন (স্টার্টারে ১,০০০ টাকা, ন্যাশনালে ১২,০০০ টাকা, আন্তর্জাতিকে ৫৫,০০০ টাকা) অ্যাকাউন্টে যুক্ত হয়।">
                        Upon activating any package through your direct referral link or member code, you receive an instant 10% direct incentive (e.g. ৳1,000 for Starter, ৳12,000 for National, ৳55,000 for International) credited directly to your affiliate balance.
                    </span>
                </div>
            </div>

            <!-- FAQ 4: Is the return guaranteed -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <button 
                    type="button"
                    @click="toggleFaq(4)"
                    class="w-full p-4 sm:p-5 text-left font-bold text-xs sm:text-sm text-slate-900 flex items-center justify-between gap-3 cursor-pointer"
                >
                    <span data-en="4. Is the weekly return guaranteed or risk-free?" data-bn="৪. সাপ্তাহিক রিটার্ন কি ফিক্সড বা গ্যারান্টেড?">4. Is the weekly return guaranteed or risk-free?</span>
                    <span class="text-slate-400 font-mono text-base" x-text="openFaq === 4 ? '−' : '+'">+</span>
                </button>
                <div x-show="openFaq === 4" x-collapse class="px-4 sm:px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    <span data-en="No. In strict compliance with SBL business ethics, returns are plan-based estimates derived from active business operations and contract terms. They do not constitute fixed bank interest or guaranteed income. Members participate under mutual terms and conditions." data-bn="না। SBL নীতিমালার আলোকে রিটার্ন হলো ব্যবসায়িক ড্রপশিপিং কার্যক্রমের ওপর ভিত্তি করে নির্ধারিত প্ল্যান-ভিত্তিক প্রাক্কলন। এটি কোনো ব্যাংক সুদ বা ফিক্সড গ্যারান্টেড মুনাফা নয়। ব্যবসায়িক শর্তাবলি মেনে চুক্তি পরিচালিত হয়।">
                        No. In strict compliance with SBL business ethics, returns are plan-based estimates derived from active business operations and contract terms. They do not constitute fixed bank interest or guaranteed income. Members participate under mutual terms and conditions.
                    </span>
                </div>
            </div>

            <!-- FAQ 5: What is included in Setup Fee -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <button 
                    type="button"
                    @click="toggleFaq(5)"
                    class="w-full p-4 sm:p-5 text-left font-bold text-xs sm:text-sm text-slate-900 flex items-center justify-between gap-3 cursor-pointer"
                >
                    <span data-en="5. What is included in the Setup Fee?" data-bn="৫. সেটআপ ফি-র মধ্যে কী কী সার্ভিস অন্তর্ভুক্ত থাকে?">5. What is included in the Setup Fee?</span>
                    <span class="text-slate-400 font-mono text-base" x-text="openFaq === 5 ? '−' : '+'">+</span>
                </button>
                <div x-show="openFaq === 5" x-collapse class="px-4 sm:px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    <span data-en="The setup fee covers full turnkey Shopify store setup, domain configuration, high-margin product sourcing, packaging design, ad creative video production, and logistics courier integration." data-bn="সেটআপ ফি-র আওতায় রেডি শপিফাই ওয়েবসাইট ডেভেলপমেন্ট, কাস্টম ডোমেন, লাভজনক প্রোডাক্ট সোর্সিং, নিজস্ব প্যাকেজিং, ভিডিও অ্যাড ক্রিয়েটিভ ও কুরিয়ার লজিস্টিকস সার্ভিস সম্পূর্ণ রেডি করে দেওয়া হয়।">
                        The setup fee covers full turnkey Shopify store setup, domain configuration, high-margin product sourcing, packaging design, ad creative video production, and logistics courier integration.
                    </span>
                </div>
            </div>

            <!-- FAQ 6: Crowdfunding eligibility -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <button 
                    type="button"
                    @click="toggleFaq(6)"
                    class="w-full p-4 sm:p-5 text-left font-bold text-xs sm:text-sm text-slate-900 flex items-center justify-between gap-3 cursor-pointer"
                >
                    <span data-en="6. How does Crowdfunding Eligibility work?" data-bn="৬. বিজনেস ক্রাউডফান্ডিং সুবিধা কীভাবে পাওয়া যায়?">6. How does Crowdfunding Eligibility work?</span>
                    <span class="text-slate-400 font-mono text-base" x-text="openFaq === 6 ? '−' : '+'">+</span>
                </button>
                <div x-show="openFaq === 6" x-collapse class="px-4 sm:px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    <span data-en="Members operating National or International dropshipping packages can apply for internal crowdfunding expansion (up to ৳10 Lac for National, up to ৳50 Lac for International) to scale their winning product inventory based on operational performance." data-bn="ন্যাশনাল ও আন্তর্জাতিক ড্রপশিপাররা তাদের ব্যবসার ধারাবাহিক পারফরম্যান্সের ওপর ভিত্তি করে স্টক সম্প্রসারণের জন্য যথাক্রমে ১০ লাখ এবং ৫০ লাখ টাকা পর্যন্ত ক্রাউডফান্ডিং বিনিয়োগ সুবিধা পেয়ে থাকেন।">
                        Members operating National or International dropshipping packages can apply for internal crowdfunding expansion (up to ৳10 Lac for National, up to ৳50 Lac for International) to scale their winning product inventory based on operational performance.
                    </span>
                </div>
            </div>

            <!-- FAQ 7: Can I upgrade later -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <button 
                    type="button"
                    @click="toggleFaq(7)"
                    class="w-full p-4 sm:p-5 text-left font-bold text-xs sm:text-sm text-slate-900 flex items-center justify-between gap-3 cursor-pointer"
                >
                    <span data-en="7. Can I upgrade my package later?" data-bn="৭. পরবর্তীতে কি প্যাকেজ আপগ্রেড করা সম্ভব?">7. Can I upgrade my package later?</span>
                    <span class="text-slate-400 font-mono text-base" x-text="openFaq === 7 ? '−' : '+'">+</span>
                </button>
                <div x-show="openFaq === 7" x-collapse class="px-4 sm:px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    <span data-en="Yes. You can start with Starter or National, and subsequently upgrade to higher tiers by fulfilling package requirements through your SBL portal account." data-bn="হ্যাঁ। আপনি স্টার্টার বা ন্যাশনাল দিয়ে শুরু করে যেকোনো সময় উচ্চতর প্যাকেজে আপগ্রেড করতে পারবেন।">
                        Yes. You can start with Starter or National, and subsequently upgrade to higher tiers by fulfilling package requirements through your SBL portal account.
                    </span>
                </div>
            </div>

            <!-- FAQ 8: Withdrawal procedure -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <button 
                    type="button"
                    @click="toggleFaq(8)"
                    class="w-full p-4 sm:p-5 text-left font-bold text-xs sm:text-sm text-slate-900 flex items-center justify-between gap-3 cursor-pointer"
                >
                    <span data-en="8. How can I withdraw returns and commissions?" data-bn="৮. রিটার্ন ও কমিশন কীভাবে উত্তোলন করা যায়?">8. How can I withdraw returns and commissions?</span>
                    <span class="text-slate-400 font-mono text-base" x-text="openFaq === 8 ? '−' : '+'">+</span>
                </button>
                <div x-show="openFaq === 8" x-collapse class="px-4 sm:px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    <span data-en="Earnings are credited directly to your official SBL balance and can be transferred through verified commercial bank accounts or mobile financial services (bKash/Nagad) on scheduled disbursement cycles." data-bn="উপার্জিত রিটার্ন ও কমিশন অফিসিয়াল পোর্টাল থেকে ব্যাংক একাউন্ট অথবা বিকাশ/নগদের মাধ্যমে নিয়মিত শিডিউলে সহজে উইথড্র করা যায়।">
                        Earnings are credited directly to your official SBL balance and can be transferred through verified commercial bank accounts or mobile financial services (bKash/Nagad) on scheduled disbursement cycles.
                    </span>
                </div>
            </div>
        </div>
    </div>


    <!-- 11. IMPORTANT DISCLAIMER & TRUST POLICY -->
    <div class="p-5 sm:p-6 rounded-3xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 leading-relaxed space-y-2">
        <div class="flex items-center gap-2 font-bold text-slate-800">
            <span class="text-base">🛡️</span>
            <span data-en="Official SBL Policy & Compliance Notice" data-bn="অফিসিয়াল SBL নীতিমালা ও কমপ্লায়েন্স নোটিশ">Official SBL Policy & Compliance Notice</span>
        </div>
        <p data-en="Package structures, business volume (BV), commission disbursements, and promotional crowdfunding eligibility are governed strictly by official SBL Ecosystem rules and active contracts. Plan-based figures, weekly calculations, and market projections represent illustrative business estimates and do not constitute guaranteed financial returns. Members should consult official advisers and review documentation prior to initiating capital allocation." data-bn="প্যাকেজের শর্তাবলি, বিজনেস ভলিউম (BV), কমিশন এবং ক্রাউডফান্ডিং সুবিধা কোম্পানির সার্বিক নীতিমালা ও চুক্তির অধীন। প্রদর্শিত রিটার্ন হিসাব ব্যবসায়িক প্রাক্কলন মাত্র এবং কোনো ফিক্সড বা নিশ্চিত মুনাফা নয়। সদস্যদের যাবতীয় শর্তাবলি অবগত হয়ে ব্যবসায়িক সিদ্ধান্ত গ্রহণের অনুরোধ করা যাচ্ছে।">
            Package structures, business volume (BV), commission disbursements, and promotional crowdfunding eligibility are governed strictly by official SBL Ecosystem rules and active contracts. Plan-based figures, weekly calculations, and market projections represent illustrative business estimates and do not constitute guaranteed financial returns. Members should consult official advisers and review documentation prior to initiating capital allocation.
        </p>
        <div class="pt-1 text-[11px] text-slate-400 font-medium">
            <span data-en="Last Verified by Compliance: September 2026 • Document Ref: SBL-MKT-PKG-2026-V4" data-bn="সর্বশেষ ভেরিফায়েড: সেপ্টেম্বর ২০২৬ • রেফারেন্স: SBL-MKT-PKG-2026-V4">Last Verified by Compliance: September 2026 • Document Ref: SBL-MKT-PKG-2026-V4</span>
        </div>
    </div>


    <!-- 12. FINAL HIGH-CONVERTING CTA -->
    <div class="rounded-3xl bg-orange-600 bg-gradient-to-r from-orange-600 via-amber-600 to-orange-700 p-8 sm:p-10 text-white text-center space-y-4 shadow-xl">
        <h2 class="text-2xl sm:text-3xl font-black max-w-xl mx-auto leading-tight" data-en="Ready to Start Your Scalable Dropshipping Journey?" data-bn="আপনার লাভজনক ড্রপশিপিং ও ক্যারিয়ার শুরু করতে প্রস্তুত?">
            Ready to Start Your Scalable Dropshipping Journey?
        </h2>
        <p class="text-xs sm:text-sm text-orange-100 max-w-lg mx-auto font-medium" data-en="Connect directly with an SBL official business consultant to setup your store and activate your chosen package." data-bn="আমাদের অফিসিয়াল বিজনেস কনসালট্যান্টের সাথে কথা বলুন এবং আপনার পছন্দের প্যাকেজ নির্বাচন করে স্টোর সেটআপ সম্পন্ন করুন।">
            Connect directly with an SBL official business consultant to setup your store and activate your chosen package.
        </p>
        <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
            <button 
                type="button"
                @click="openLeadModal('national')"
                class="px-6 py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-sm shadow-md transition-all cursor-pointer min-h-[44px]"
            >
                <span data-en="Speak with a Consultant" data-bn="কনসালট্যান্টের সাথে কথা বলুন">Speak with a Consultant</span>
                <span>→</span>
            </button>
            <a 
                href="#package-comparison"
                class="px-5 py-3.5 rounded-xl bg-white/15 hover:bg-white/20 border border-white/20 text-white font-bold text-sm transition-all cursor-pointer min-h-[44px]"
            >
                <span data-en="Compare Packages Again" data-bn="প্যাকেজগুলোর তুলনা দেখুন">Compare Packages Again</span>
            </a>
        </div>
    </div>


    <!-- ========================================================================= -->
    <!-- 13. LEAD GENERATION MODAL (POSTS TO /leads)                               -->
    <!-- ========================================================================= -->
    <div 
        x-show="leadModalOpen" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="leadModalOpen = false"
    >
        <!-- Backdrop -->
        <div 
            x-show="leadModalOpen"
            x-transition.opacity
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
            @click="leadModalOpen = false"
        ></div>

        <!-- Modal Dialog -->
        <div 
            x-show="leadModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-[calc(100%-24px)] max-w-[480px] p-6 z-10 overflow-hidden"
            @click.stop
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-600"></span>
                    <h3 class="text-base font-extrabold text-slate-900" data-en="Express Your Interest" data-bn="আগ্রহ প্রকাশ করুন">Express Your Interest</h3>
                </div>
                <button 
                    type="button" 
                    @click="leadModalOpen = false" 
                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:text-slate-900 flex items-center justify-center text-sm font-bold cursor-pointer"
                >✕</button>
            </div>

            <!-- Success State -->
            <template x-if="leadForm.success">
                <div class="py-8 text-center space-y-3">
                    <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto text-2xl font-bold">
                        ✓
                    </div>
                    <h4 class="text-lg font-black text-slate-900" data-en="Thank You!" data-bn="ধন্যবাদ!">Thank You!</h4>
                    <p class="text-xs text-slate-600 max-w-xs mx-auto" data-en="Your interest has been received. Our team will contact you shortly." data-bn="আপনার আগ্রহ সফলভাবে সংরক্ষিত হয়েছে। আমাদের প্রতিনিধি দ্রুত যোগাযোগ করবেন।">
                        Your interest has been received. Our team will contact you shortly.
                    </p>
                </div>
            </template>

            <!-- Lead Form Form -->
            <template x-if="!leadForm.success && leadPkg">
                <form @submit.prevent="submitLead" class="space-y-3.5 mt-4 text-xs">
                    <template x-if="leadForm.error">
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-semibold text-xs" x-text="leadForm.error"></div>
                    </template>

                    <!-- Package Selector -->
                    <div>
                        <label class="block text-slate-700 font-bold mb-1" data-en="Selected Package:" data-bn="নির্বাচিত প্যাকেজ:">Selected Package:</label>
                        <select 
                            x-model="leadPkg.id" 
                            @change="leadPkg = packages[$event.target.value]; leadForm.amount = '৳' + leadPkg.price.toLocaleString()"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-medium text-slate-800 bg-slate-50 focus:bg-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 min-h-[44px]"
                        >
                            <option value="starter">Starter Membership (৳10,000)</option>
                            <option value="national">National Dropshipping (৳120,000 - 100 BV)</option>
                            <option value="international">International Dropshipping (৳550,000 - 500 BV)</option>
                        </select>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-slate-700 font-bold mb-1" data-en="Your Full Name *" data-bn="আপনার নাম *">Your Full Name *</label>
                        <input 
                            type="text" 
                            x-model="leadForm.name" 
                            required 
                            placeholder="e.g. Md. Abdul Hai" 
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 min-h-[44px]"
                        />
                    </div>

                    <!-- Mobile -->
                    <div>
                        <label class="block text-slate-700 font-bold mb-1" data-en="Phone / WhatsApp Number *" data-bn="মোবাইল বা হোয়াটসঅ্যাপ নম্বর *">Phone / WhatsApp Number *</label>
                        <input 
                            type="tel" 
                            x-model="leadForm.mobile" 
                            required 
                            placeholder="01XXXXXXXXX" 
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 min-h-[44px]"
                        />
                    </div>

                    <!-- Estimated Budget -->
                    <div>
                        <label class="block text-slate-700 font-bold mb-1" data-en="Interested Amount / Budget" data-bn="বাজেট বা সম্ভাব্য অ্যামাউন্ট">Interested Amount / Budget</label>
                        <input 
                            type="text" 
                            x-model="leadForm.amount" 
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 min-h-[44px]"
                        />
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-slate-700 font-bold mb-1" data-en="Notes or Questions (Optional)" data-bn="মন্তব্য বা জিজ্ঞাসা (ঐচ্ছিক)">Notes or Questions (Optional)</label>
                        <textarea 
                            x-model="leadForm.notes" 
                            rows="2" 
                            placeholder="Tell us any specific requirements..." 
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-800 focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                        ></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            :disabled="leadForm.submitting"
                            class="w-full py-3.5 rounded-xl bg-orange-600 hover:bg-orange-500 disabled:opacity-50 text-white font-extrabold text-sm shadow-md transition-all cursor-pointer min-h-[44px]"
                        >
                            <span x-text="leadForm.submitting ? 'Submitting...' : 'Submit Interest • Contact Me'"></span>
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>


    <!-- ========================================================================= -->
    <!-- 14. PACKAGE DETAILS DRAWER / MODAL (CENTERED ON DESKTOP, SHEET ON MOBILE) -->
    <!-- ========================================================================= -->
    <div 
        x-show="detailsModalOpen" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="detailsModalOpen = false"
    >
        <div 
            x-show="detailsModalOpen"
            x-transition.opacity
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
            @click="detailsModalOpen = false"
        ></div>

        <div 
            x-show="detailsModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-[calc(100%-24px)] max-w-[680px] max-h-[90dvh] flex flex-col z-10 overflow-hidden"
            @click.stop
        >
            <!-- Header -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10 shrink-0">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-100 text-orange-800" x-text="activeDetailsPkg?.badge"></span>
                        <span class="text-xs font-bold text-slate-500" x-text="'Verified: ' + (activeDetailsPkg?.last_verified_at || 'September 2026')"></span>
                    </div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900" x-text="activeDetailsPkg?.name"></h3>
                </div>
                <button 
                    type="button" 
                    @click="detailsModalOpen = false" 
                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:text-slate-900 flex items-center justify-center text-sm font-bold cursor-pointer"
                >✕</button>
            </div>

            <!-- Scrollable Body -->
            <div class="p-5 sm:p-6 space-y-5 overflow-y-auto text-xs text-slate-700">
                <!-- Investment Breakdown -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block" data-en="Investment Allocation" data-bn="বিনিয়োগ বিভাজন">Investment Allocation</span>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-black text-slate-900" x-text="activeDetailsPkg?.price_formatted"></span>
                        <span class="text-xs font-bold text-orange-700" x-text="activeDetailsPkg?.bv_formatted"></span>
                    </div>
                    <div class="text-xs text-slate-600 font-medium" x-text="activeDetailsPkg?.setup_fee_formatted"></div>
                </div>

                <!-- Core Deliverables -->
                <div class="space-y-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block" data-en="Deliverables & Services" data-bn="সুবিধা ও সেবাসমূহ">Deliverables & Services</span>
                    <ul class="space-y-2">
                        <template x-for="(benefit, idx) in (activeDetailsPkg?.benefits || [])" :key="idx">
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
                                <span x-text="benefit.text"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                <!-- Terms & Disclaimer -->
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px] space-y-1">
                    <span class="font-bold block" data-en="Policy & Return Notice:" data-bn="নীতিমালা ও রিটার্ন নোটিশ:">Policy & Return Notice:</span>
                    <p x-text="activeDetailsPkg?.disclaimer"></p>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-2 shrink-0">
                <button 
                    type="button" 
                    @click="copySummary(activeDetailsPkg)" 
                    class="px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 hover:bg-slate-100 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer min-h-[44px]"
                >
                    <span>📋</span>
                    <span data-en="Copy Package Summary" data-bn="সামারি কপি করুন">Copy Package Summary</span>
                </button>

                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        @click="openShareModal(activeDetailsPkg?.id)" 
                        class="px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition-all cursor-pointer min-h-[44px]"
                    >
                        <span>📤 Share</span>
                    </button>
                    <button 
                        type="button" 
                        @click="detailsModalOpen = false; openLeadModal(activeDetailsPkg?.id)" 
                        class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold shadow-sm transition-all cursor-pointer min-h-[44px]"
                    >
                        <span data-en="Interested • Apply" data-bn="আগ্রহী • আবেদন করুন">Interested • Apply</span>
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- ========================================================================= -->
    <!-- 15. SHARE PACKAGE MODAL (WEB SHARE / WHATSAPP / FACEBOOK / LINK)           -->
    <!-- ========================================================================= -->
    <div 
        x-show="shareModalOpen" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="shareModalOpen = false"
    >
        <div 
            x-show="shareModalOpen"
            x-transition.opacity
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
            @click="shareModalOpen = false"
        ></div>

        <div 
            x-show="shareModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-[calc(100%-24px)] max-w-[420px] p-6 z-10 overflow-hidden"
            @click.stop
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="space-y-0.5">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block" data-en="Marketing Tool" data-bn="মার্কেটিং টুল">Marketing Tool</span>
                    <h3 class="text-base font-extrabold text-slate-900" data-en="Share SBL Package" data-bn="প্যাকেজ শেয়ার করুন">Share SBL Package</h3>
                </div>
                <button 
                    type="button" 
                    @click="shareModalOpen = false" 
                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:text-slate-900 flex items-center justify-center text-sm font-bold cursor-pointer"
                >✕</button>
            </div>

            <div class="space-y-3 pt-4 text-xs">
                <!-- Package Preview -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-900 block" x-text="sharePkg?.name"></span>
                        <span class="text-slate-500 text-[11px]" x-text="sharePkg?.price_formatted + ' (' + sharePkg?.bv_formatted + ')'"></span>
                    </div>
                    <span class="text-orange-600 font-bold text-xs">Verified</span>
                </div>

                <!-- Action Options -->
                <div class="grid grid-cols-2 gap-2 pt-2">
                    <button 
                        type="button" 
                        @click="shareWhatsApp(sharePkg)" 
                        class="p-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-900 font-bold flex items-center justify-center gap-2 cursor-pointer transition-colors min-h-[44px]"
                    >
                        <span>💬</span>
                        <span>WhatsApp</span>
                    </button>
                    <button 
                        type="button" 
                        @click="shareFacebook(sharePkg)" 
                        class="p-3 rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-900 font-bold flex items-center justify-center gap-2 cursor-pointer transition-colors min-h-[44px]"
                    >
                        <span>🌐</span>
                        <span>Facebook</span>
                    </button>
                    <button 
                        type="button" 
                        @click="copySummary(sharePkg)" 
                        class="p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-800 font-bold flex items-center justify-center gap-2 cursor-pointer transition-colors min-h-[44px]"
                    >
                        <span>📋</span>
                        <span>Copy Summary</span>
                    </button>
                    <button 
                        type="button" 
                        @click="copyLink(sharePkg)" 
                        class="p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-800 font-bold flex items-center justify-center gap-2 cursor-pointer transition-colors min-h-[44px]"
                    >
                        <span>🔗</span>
                        <span>Copy Link</span>
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- ========================================================================= -->
    <!-- 16. STICKY MOBILE CTA BAR (Bottom action bar on small screens)             -->
    <!-- ========================================================================= -->
    <div 
        x-show="!leadModalOpen && !detailsModalOpen && !shareModalOpen"
        x-cloak
        class="fixed bottom-0 left-0 right-0 z-30 block md:hidden bg-white/95 backdrop-blur-md border-t border-slate-200 p-3 shadow-2xl transition-all"
    >
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block" data-en="Selected Plan" data-bn="নির্বাচিত প্ল্যান">Selected Plan</span>
                <div class="flex items-baseline gap-1.5 truncate">
                    <span class="text-sm font-black text-slate-900 truncate" x-text="packages[mobileActivePkg]?.name"></span>
                    <span class="text-xs font-black text-orange-600" x-text="packages[mobileActivePkg]?.price_formatted"></span>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button 
                    type="button"
                    @click="openDetails(mobileActivePkg)"
                    class="px-3 py-2 rounded-xl bg-slate-100 text-slate-800 font-bold text-xs cursor-pointer min-h-[44px]"
                >
                    <span>Details</span>
                </button>
                <button 
                    type="button"
                    @click="openLeadModal(mobileActivePkg)"
                    class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-black text-xs shadow-md shadow-orange-600/30 cursor-pointer min-h-[44px]"
                >
                    <span data-en="Interested" data-bn="আগ্রহী">Interested</span>
                    <span>→</span>
                </button>
            </div>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div 
        x-show="copiedToast" 
        x-cloak
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-16 md:bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 rounded-2xl bg-slate-900/90 text-white text-xs font-bold shadow-xl border border-slate-700 backdrop-blur-md flex items-center gap-2"
    >
        <span>✓</span>
        <span x-text="toastMessage"></span>
    </div>

    <!-- ACCESSIBLE DETAILS MODALS (Preserved for compatibility) -->
    @include('toolkit.partials.package-modal')
    @include('toolkit.partials.modal-national')
    @include('toolkit.partials.modal-international')

</section>
