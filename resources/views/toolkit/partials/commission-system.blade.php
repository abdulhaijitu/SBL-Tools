{{-- SBL Marketing: Redesigned Simple & Smart Commission Plan --}}
<div x-data="{
    // Calculator States
    packageAmount: @js((int)($defaultAmount ?? 120000)),
    qualifiedPairs: 10,
    showUdrExample: false,
    
    // UI Modals & Drawers
    activeDrawer: null, // 'spot', 'refer_return', 'pair_reward', 'udr'
    presentationMode: false,
    currentSlide: 0,
    copiedToast: false,
    copiedToastMessage: '',

    // Detail Data for Drawers
    details: {
        spot: {
            id: 'spot',
            name: 'Spot / Direct Commission',
            name_bn: 'স্পট / ডিরেক্ট রেফারেল কমিশন',
            rate: '10%',
            accent: 'emerald',
            summary: 'Paid on eligible direct referral or package activation.',
            summary_bn: 'সরাসরি রেফারেন্স বা প্যাকেজ অ্যাক্টিভেশনের সাথে সাথে প্রাপ্য কমিশন।',
            howItWorks: 'Whenever you directly sponsor any new SBL member or dropshipping project, 10% direct commission is credited to your verified wallet immediately upon activation according to current SBL terms.',
            howItWorks_bn: 'আপনার সরাসরি রেফারেন্সে কোনো নতুন সদস্য বা ড্রপশিপিং প্রজেক্ট সক্রিয় হলে, প্যাকেজ মূল্যের ১০% তাৎক্ষণিকভাবে আপনার ভেরিফায়েড ক্যাশ ওয়ালেটে জমা হয়।',
            example: '৳120,000 eligible package × 10% = ৳12,000 instant spot commission',
            example_bn: '৳১,২০,০০০ টাকার প্যাকেজ × ১০% = ১২,০০০ টাকা তাৎক্ষণিক স্পট কমিশন',
            eligibility: 'All active associates with Starter, National, or International membership in good standing.',
            eligibility_bn: 'স্টার্টার, ন্যাশনাল বা ইন্টারন্যাশনাল প্যাকেজধারী সকল সক্রিয় অ্যাসোসিয়েট।',
            termsNote: 'Calculated on package value according to official SBL terms.'
        },
        refer_return: {
            id: 'refer_return',
            name: 'Referral Weekly Share',
            name_bn: 'রেফারেল সাপ্তাহিক শেয়ার (১০০ সপ্তাহ)',
            rate: '0.25% / week',
            accent: 'blue',
            summary: 'Continuous weekly incentive for up to 100 weeks on verified project capital.',
            summary_bn: 'ন্যাশনাল ও ইন্টারন্যাশনাল প্রজেক্ট ক্যাপিটালের ওপর ১০০ সপ্তাহব্যাপী সাপ্তাহিক বোনাস।',
            howItWorks: 'When you refer a National or International dropshipping project, you receive 0.25% weekly incentive on the participating core capital for up to 100 weeks, distributed alongside business operation cycles.',
            howItWorks_bn: 'আপনার সরাসরি রেফারেন্সে ন্যাশনাল বা আন্তর্জাতিক প্রজেক্ট বিনিয়োগ সম্পন্ন হলে ১০০ সপ্তাহ ধরে প্রতি সপ্তাহে মূলধনের ওপর ০.২৫% বিশেষ ইনসেনটিভ সরাসরি ওয়ালেটে জমা হয়।',
            example: '৳100,000 eligible capital × 0.25% = ৳250 / week (Total ৳25,000 across 100 weeks)',
            example_bn: '৳১,০০,০০০ মূলধন × ০.২৫% = প্রতি সপ্তাহে ২৫০ টাকা (১০০ সপ্তাহে মোট ২৫,০০০ টাকা)',
            eligibility: 'Direct sponsors of verified participating dropshipping projects (Not applicable to ৳10,000 Starter accounts).',
            eligibility_bn: 'ভেরিফায়েড প্রজেক্ট স্পনসরকারী সদস্য (স্টার্টার অ্যাকাউন্টে প্রযোজ্য নয়)।',
            termsNote: 'Subject to continuous project execution and verified active associate terms.'
        },
        pair_reward: {
            id: 'pair_reward',
            name: 'Pair Matching Reward',
            name_bn: 'বাইনারি পেয়ার ম্যাচিং রিওয়ার্ড',
            rate: '৳500 / Pair',
            accent: 'amber',
            summary: 'Dual team performance reward on 1:1 Left & Right volume match (max 100 pairs/day).',
            summary_bn: 'লেফট ও রাইট টিমে ১:১ অনুপাতে ব্যালেন্সড সেলস ভলিউম ম্যাচিং রিওয়ার্ড।',
            howItWorks: 'Binary matching bonus earned whenever team sales volume on your Left team and Right team match in a 1:1 ratio. Each qualified pair generates ৳500, up to a maximum daily limit of 100 pairs (৳50,000/day).',
            howItWorks_bn: 'আপনার বাইনারি নেটওয়ার্কের লেফট এবং রাইট টিমে ১:১ অনুপাতে টিম ভলিউম ম্যাচিং হলে প্রতি পেয়ারে ৫০০ টাকা রিওয়ার্ড অর্জিত হয়। দৈনিক সর্বোচ্চ সীমা ১০০ পেয়ার (৫০,০০০ টাকা)।',
            example: '10 Qualified Pairs in a day = ৳5,000 matching reward',
            example_bn: 'দিনে ১০টি কোয়ালিফায়েড পেয়ার ম্যাচিং = ৫,০০০ টাকা ম্যাচিং রিওয়ার্ড',
            eligibility: 'Active account with at least 1 direct Left + 1 direct Right verified active sponsor.',
            eligibility_bn: 'উভয় টিমে (লেফট ও রাইট) কমপক্ষে ১ জন করে সরাসরি ভেরিফায়েড সক্রিয় স্পনসর।',
            termsNote: 'Subject to current SBL daily pair limit and qualification rules.'
        },
        udr: {
            id: 'udr',
            name: 'Generation / UDR Commission',
            name_bn: 'জেনারেশন / UDR টিম কমিশন',
            rate: 'Up to 10 Tiers',
            accent: 'purple',
            summary: 'Tier-based development bonus paid from active team downline volume.',
            summary_bn: '১০ম প্রজন্ম পর্যন্ত টিম পারফরম্যান্স ও সেলসের ওপর নির্ধারিত উন্নয়ন কমিশন।',
            howItWorks: 'Unity Development Commission (UDR) distributes pre-defined percentage overrides from the active product sales and package volume generated across your extended referral network up to 10 generations deep.',
            howItWorks_bn: 'আপনার স্পনসর নেটওয়ার্কের ১০ম প্রজন্ম পর্যন্ত সেলস ও পারফরম্যান্সের ওপর স্তরভিত্তিক নির্ধারিত কমিশন বণ্টন করা হয়।',
            example: 'Gen 2 Team Volume: ৳100,000 × 2% = ৳2,000 estimated commission',
            example_bn: '২য় প্রজন্মের টিম ভলিউম: ৳১,০০,০০০ × ২% = ২,০০০ টাকা কমিশন',
            eligibility: 'Rank milestone qualifications (FME, SME, PME, etc.) and active team volume criteria.',
            eligibility_bn: 'নির্ধারিত পদবী কোয়ালিফিকেশন ও সক্রিয় টিম সেলস শর্ত সাপেক্ষে।',
            termsNote: 'Actual eligibility and commission depend on current SBL qualification and verified team activity.'
        }
    },

    // Currency Formatter
    fmt(val) {
        let num = Number(val) || 0;
        return '৳' + Math.round(num).toLocaleString('en-IN');
    },

    // Direct Calculator Computed Properties
    get spotCommission() {
        return Math.round(this.packageAmount * 0.10);
    },
    get isStarter() {
        return this.packageAmount <= 10000;
    },
    get isInternational() {
        return this.packageAmount >= 500000;
    },
    get eligibleCapital() {
        if (this.isStarter) return 0;
        if (this.isInternational) return Math.max(0, this.packageAmount - 50000);
        return Math.max(0, this.packageAmount - 20000);
    },
    get weeklyReferralShare() {
        if (this.isStarter) return 0;
        return Math.round(this.eligibleCapital * 0.0025);
    },
    get totalReferralShare100Weeks() {
        return this.weeklyReferralShare * 100;
    },

    // Pair Calculator Computed
    get pairRewardTotal() {
        let pairs = Math.max(0, parseInt(this.qualifiedPairs) || 0);
        return pairs * 500;
    },

    // UDR Example Computed
    udrExampleVolume: 100000,
    get udrExampleCommission() {
        return Math.round(this.udrExampleVolume * 0.02);
    },

    // Share Functions
    showToast(msg) {
        this.copiedToastMessage = msg;
        this.copiedToast = true;
        setTimeout(() => { this.copiedToast = false; }, 2600);
    },

    copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text);
        } else {
            let el = document.createElement('textarea');
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
        }
        this.showToast('Copied to clipboard!');
    },

    shareWhatsApp(text) {
        const url = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text);
        window.open(url, '_blank');
    },

    shareOverview() {
        const text = '📊 SBL Commission Plan Summary 2026\n' +
            '-----------------------------------------\n' +
            '1. Spot / Direct Commission: 10%\n' +
            '   (Immediate on eligible package activation)\n' +
            '2. Referral Weekly Share: 0.25% / week\n' +
            '   (Up to 100 weeks on participating project capital)\n' +
            '3. Pair Matching Reward: ৳500 / Pair\n' +
            '   (1:1 Dual team match, max 100 pairs/day)\n' +
            '4. Generation / UDR: Up to 10 Tiers\n' +
            '   (Gen 1: 10%, Gen 2: 2%, Gen 3-4: 1%, Gen 5: 0.5%, Gen 6-10: 0.1%)\n' +
            '-----------------------------------------\n' +
            'Subject to current SBL terms and qualification.\n' +
            'View official plan: ' + window.location.origin + '/commission';
        
        if (navigator.share) {
            navigator.share({ title: 'SBL Commission Plan', text: text, url: window.location.origin + '/commission' }).catch(() => {});
        } else {
            this.copyText(text);
        }
    },

    shareDirectCalc() {
        let weeklyText = this.isStarter ? 'Not Applicable (Starter membership)' : (this.fmt(this.weeklyReferralShare) + '/week for 100 weeks (~' + this.fmt(this.totalReferralShare100Weeks) + ')');
        const text = '⚡ SBL Direct Commission Calculation\n' +
            '-----------------------------------------\n' +
            'Package Amount: ' + this.fmt(this.packageAmount) + '\n' +
            '• Spot Commission (10%): ' + this.fmt(this.spotCommission) + '\n' +
            '• Referral Weekly Share: ' + weeklyText + '\n' +
            '-----------------------------------------\n' +
            'Subject to current SBL terms and verified active associate qualification.\n' +
            'Calculate live: ' + window.location.origin + '/commission';
        this.shareWhatsApp(text);
    },

    sharePairCalc() {
        const text = '⚖️ SBL Pair Matching Reward Calculation\n' +
            '-----------------------------------------\n' +
            'Qualified Pairs: ' + this.qualifiedPairs + ' Pairs\n' +
            'Rate: 1 Pair = ৳500\n' +
            '• Total Matching Reward: ' + this.fmt(this.pairRewardTotal) + '\n' +
            '-----------------------------------------\n' +
            'Note: Subject to current SBL daily pair limit (max 100 pairs/day) and dual team qualification (1 Left + 1 Right).\n' +
            'Calculate live: ' + window.location.origin + '/commission';
        this.shareWhatsApp(text);
    },

    shareType(typeKey) {
        const item = this.details[typeKey];
        if (!item) return;
        const text = '📌 SBL ' + item.name + '\n' +
            '-----------------------------------------\n' +
            'Current Rate: ' + item.rate + '\n' +
            'How it works: ' + item.howItWorks + '\n' +
            'Example: ' + item.example + '\n' +
            'Eligibility: ' + item.eligibility + '\n' +
            '-----------------------------------------\n' +
            item.termsNote + '\n' +
            'View details: ' + window.location.origin + '/commission';
        this.shareWhatsApp(text);
    },

    scrollToId(id) {
        const el = document.getElementById(id);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}"
@keydown.escape.window="presentationMode = false; activeDrawer = null"
class="min-h-screen bg-slate-50 text-slate-800 pb-20 antialiased selection:bg-emerald-500 selection:text-white"
>
    <!-- Notification Toast -->
    <div 
        x-show="copiedToast" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-3"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 bg-slate-900 text-white text-xs sm:text-sm font-medium px-4 py-3 rounded-xl shadow-xl border border-slate-700/50"
        style="display: none;"
    >
        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
        </svg>
        <span x-text="copiedToastMessage"></span>
    </div>

    <!-- MAIN PAGE CONTAINER -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8 space-y-8">
        
        <!-- ==========================================
             1. COMPACT PAGE HEADER
             ========================================== -->
        <header class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-7 shadow-xs">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Official SBL Compensation Plan 2026
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        SBL Commission Plan
                    </h1>
                    <p class="text-sm sm:text-base text-slate-600 max-w-2xl">
                        Understand and calculate SBL earning opportunities. Transparent, verified, and presentation-ready.
                    </p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <button 
                        type="button" 
                        @click="presentationMode = true; currentSlide = 0"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-xs transition-colors cursor-pointer min-h-[44px]"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Presentation View</span>
                    </button>
                    
                    <button 
                        type="button" 
                        @click="shareOverview()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-medium text-sm bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-700 border border-slate-300 shadow-xs transition-colors cursor-pointer min-h-[44px]"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        <span>Share Plan</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- ==========================================
             2. COMMISSION OVERVIEW (4 Cards Only)
             ========================================== -->
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Core Commission Structure</h2>
                    <p class="text-xs sm:text-sm text-slate-500">The 4 primary earning streams for active SBL associates.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                
                <!-- Card 1: Spot Commission -->
                <div class="bg-white border-2 border-emerald-100 rounded-2xl p-5 shadow-xs hover:border-emerald-300 transition-all flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-8 -mt-8 pointer-events-none transition-transform group-hover:scale-110"></div>
                    <div class="space-y-3 relative z-10">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                ⚡ Direct
                            </span>
                            <span class="text-xs font-medium text-slate-400">Stream 01</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Spot / Direct Commission</h3>
                            <div class="text-3xl font-black text-emerald-600 mt-1">10%</div>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed min-h-[36px]">
                            Paid immediately on eligible direct referral or package activation.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center gap-2 mt-4 relative z-10">
                        <button 
                            type="button" 
                            @click="scrollToId('direct-calculator')"
                            class="flex-1 py-2 px-3 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors text-center cursor-pointer min-h-[38px] flex items-center justify-center"
                        >
                            Calculate
                        </button>
                        <button 
                            type="button" 
                            @click="activeDrawer = 'spot'"
                            class="py-2 px-3 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer min-h-[38px] flex items-center justify-center"
                        >
                            View Details
                        </button>
                    </div>
                </div>

                <!-- Card 2: Referral Weekly Share -->
                <div class="bg-white border-2 border-blue-100 rounded-2xl p-5 shadow-xs hover:border-blue-300 transition-all flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-8 -mt-8 pointer-events-none transition-transform group-hover:scale-110"></div>
                    <div class="space-y-3 relative z-10">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                📈 Weekly Share
                            </span>
                            <span class="text-xs font-medium text-slate-400">Stream 02</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Referral Weekly Share</h3>
                            <div class="text-3xl font-black text-blue-600 mt-1">0.25% <span class="text-sm font-semibold text-slate-500">/ wk</span></div>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed min-h-[36px]">
                            Up to 100 weeks where applicable on verified participating project capital.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center gap-2 mt-4 relative z-10">
                        <button 
                            type="button" 
                            @click="scrollToId('direct-calculator')"
                            class="flex-1 py-2 px-3 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors text-center cursor-pointer min-h-[38px] flex items-center justify-center"
                        >
                            Calculate
                        </button>
                        <button 
                            type="button" 
                            @click="activeDrawer = 'refer_return'"
                            class="py-2 px-3 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer min-h-[38px] flex items-center justify-center"
                        >
                            View Details
                        </button>
                    </div>
                </div>

                <!-- Card 3: Pair Matching Reward -->
                <div class="bg-white border-2 border-amber-100 rounded-2xl p-5 shadow-xs hover:border-amber-300 transition-all flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-8 -mt-8 pointer-events-none transition-transform group-hover:scale-110"></div>
                    <div class="space-y-3 relative z-10">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                ⚖️ Binary Matching
                            </span>
                            <span class="text-xs font-medium text-slate-400">Stream 03</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Pair Matching Reward</h3>
                            <div class="text-3xl font-black text-amber-600 mt-1">৳500 <span class="text-sm font-semibold text-slate-500">/ Pair</span></div>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed min-h-[36px]">
                            Daily cap according to current plan on 1:1 Left & Right dual team volume match.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center gap-2 mt-4 relative z-10">
                        <button 
                            type="button" 
                            @click="scrollToId('pair-calculator')"
                            class="flex-1 py-2 px-3 text-xs font-semibold rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors text-center cursor-pointer min-h-[38px] flex items-center justify-center"
                        >
                            Calculate
                        </button>
                        <button 
                            type="button" 
                            @click="activeDrawer = 'pair_reward'"
                            class="py-2 px-3 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer min-h-[38px] flex items-center justify-center"
                        >
                            View Details
                        </button>
                    </div>
                </div>

                <!-- Card 4: Generation / UDR Commission -->
                <div class="bg-white border-2 border-purple-100 rounded-2xl p-5 shadow-xs hover:border-purple-300 transition-all flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50 rounded-bl-full -mr-8 -mt-8 pointer-events-none transition-transform group-hover:scale-110"></div>
                    <div class="space-y-3 relative z-10">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                🌐 Team UDR
                            </span>
                            <span class="text-xs font-medium text-slate-400">Stream 04</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Generation / UDR Commission</h3>
                            <div class="text-3xl font-black text-purple-600 mt-1">Up to 10 <span class="text-sm font-semibold text-slate-500">Tiers</span></div>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed min-h-[36px]">
                            Tier-based commission from active team downline volume (Gen 1: 10% to Gen 10: 0.1%).
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center gap-2 mt-4 relative z-10">
                        <button 
                            type="button" 
                            @click="scrollToId('udr-section')"
                            class="flex-1 py-2 px-3 text-xs font-semibold rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 transition-colors text-center cursor-pointer min-h-[38px] flex items-center justify-center"
                        >
                            View Table
                        </button>
                        <button 
                            type="button" 
                            @click="activeDrawer = 'udr'"
                            class="py-2 px-3 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer min-h-[38px] flex items-center justify-center"
                        >
                            View Details
                        </button>
                    </div>
                </div>

            </div>
        </section>

        <!-- ==========================================
             3. SIMPLE DIRECT COMMISSION CALCULATOR
             ========================================== -->
        <section id="direct-calculator" class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-7 shadow-xs space-y-6 scroll-mt-20">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Direct Commission Calculator</h2>
                    <p class="text-xs sm:text-sm text-slate-500">Calculate instant 10% spot commission and eligible weekly referral share.</p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <button 
                        type="button" 
                        @click="shareDirectCalc()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors cursor-pointer min-h-[36px]"
                    >
                        <span>WhatsApp Share</span>
                    </button>
                    <button 
                        type="button" 
                        @click="copyText('Direct Commission for ' + fmt(packageAmount) + ': Spot 10% = ' + fmt(spotCommission) + ', Weekly Share = ' + (isStarter ? 'N/A' : fmt(weeklyReferralShare) + '/wk'))"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors cursor-pointer min-h-[36px]"
                    >
                        <span>Copy</span>
                    </button>
                </div>
            </div>

            <!-- Input & Presets -->
            <div class="space-y-4">
                <label class="block text-xs sm:text-sm font-semibold text-slate-700">
                    Package / Project Amount
                </label>
                
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                    <div class="sm:col-span-6 relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-medium text-sm">৳</span>
                        <input 
                            type="number" 
                            x-model.number="packageAmount" 
                            min="1000" 
                            step="1000"
                            class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-semibold text-slate-900 text-base"
                            placeholder="120000"
                        >
                    </div>
                    
                    <div class="sm:col-span-6 flex items-center gap-2">
                        <button 
                            type="button" 
                            @click="packageAmount = 10000"
                            :class="packageAmount === 10000 ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                            class="flex-1 py-2.5 px-2 text-xs font-semibold rounded-xl border transition-colors cursor-pointer min-h-[44px] text-center"
                        >
                            ৳10,000<br><span class="text-[10px] font-normal opacity-90">Starter</span>
                        </button>
                        <button 
                            type="button" 
                            @click="packageAmount = 120000"
                            :class="packageAmount === 120000 ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                            class="flex-1 py-2.5 px-2 text-xs font-semibold rounded-xl border transition-colors cursor-pointer min-h-[44px] text-center"
                        >
                            ৳120,000<br><span class="text-[10px] font-normal opacity-90">National</span>
                        </button>
                        <button 
                            type="button" 
                            @click="packageAmount = 550000"
                            :class="packageAmount === 550000 ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                            class="flex-1 py-2.5 px-2 text-xs font-semibold rounded-xl border transition-colors cursor-pointer min-h-[44px] text-center"
                        >
                            ৳550,000<br><span class="text-[10px] font-normal opacity-90">International</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Calculation Output Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                
                <!-- Spot Result -->
                <div class="p-5 rounded-xl bg-emerald-50/70 border border-emerald-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-800 tracking-wide uppercase">Spot Commission</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">10% Instant</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-emerald-700" x-text="fmt(spotCommission)"></div>
                    <p class="text-xs text-emerald-900/80">
                        Formula: <span x-text="fmt(packageAmount)"></span> × 10% = <strong x-text="fmt(spotCommission)"></strong> credited immediately.
                    </p>
                </div>

                <!-- Referral Weekly Share Result -->
                <div class="p-5 rounded-xl bg-blue-50/70 border border-blue-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-800 tracking-wide uppercase">Referral Weekly Share</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">0.25% / Week</span>
                    </div>
                    
                    <template x-if="isStarter">
                        <div>
                            <div class="text-lg sm:text-xl font-bold text-slate-500">Not Applicable</div>
                            <p class="text-xs text-slate-500 mt-1">
                                Starter Membership does not have participating project capital.
                            </p>
                        </div>
                    </template>
                    
                    <template x-if="!isStarter">
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-blue-700">
                                <span x-text="fmt(weeklyReferralShare)"></span>
                                <span class="text-xs font-normal text-blue-900/70">/ week (up to 100 weeks)</span>
                            </div>
                            <p class="text-xs text-blue-900/80 mt-1">
                                Eligible Capital: <span x-text="fmt(eligibleCapital)"></span> × 0.25% = <strong x-text="fmt(weeklyReferralShare)"></strong> / week. Total potential: <span class="font-bold" x-text="fmt(totalReferralShare100Weeks)"></span> across 100 weeks.
                            </p>
                        </div>
                    </template>
                </div>

            </div>

            <p class="text-[11px] text-slate-400 italic">
                * Note: Weekly referral share is applicable only to participating Dropshipping project capital under current official SBL business terms.
            </p>
        </section>

        <!-- ==========================================
             4. PAIR REWARD CALCULATOR
             ========================================== -->
        <section id="pair-calculator" class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-7 shadow-xs space-y-6 scroll-mt-20">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Pair Matching Reward Calculator</h2>
                    <p class="text-xs sm:text-sm text-slate-500">Calculate dual team matching rewards based on qualified 1:1 pairs.</p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <button 
                        type="button" 
                        @click="sharePairCalc()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors cursor-pointer min-h-[36px]"
                    >
                        <span>WhatsApp Share</span>
                    </button>
                    <button 
                        type="button" 
                        @click="copyText(qualifiedPairs + ' Pairs @ ৳500/pair = ' + fmt(pairRewardTotal))"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors cursor-pointer min-h-[36px]"
                    >
                        <span>Copy</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                <!-- Inputs & Presets -->
                <div class="md:col-span-7 space-y-4">
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">
                        Qualified Pairs (1:1 Left & Right Match)
                    </label>
                    
                    <div class="relative">
                        <input 
                            type="number" 
                            x-model.number="qualifiedPairs" 
                            min="0" 
                            max="100" 
                            step="1"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-semibold text-slate-900 text-base"
                            placeholder="10"
                        >
                    </div>

                    <!-- Preset Chips -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-medium text-slate-400 mr-1">Presets:</span>
                        <template x-for="p in [1, 5, 10, 20, 50, 100]" :key="p">
                            <button 
                                type="button" 
                                @click="qualifiedPairs = p"
                                :class="qualifiedPairs === p ? 'bg-amber-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-3 py-1.5 rounded-lg text-xs transition-colors cursor-pointer min-h-[34px]"
                                x-text="p + (p === 1 ? ' Pair' : ' Pairs')"
                            ></button>
                        </template>
                    </div>

                    <div class="p-3 bg-amber-50/60 rounded-xl border border-amber-200/60 text-xs text-amber-900 leading-relaxed">
                        <strong>Official Rate:</strong> 1 Pair = <strong>৳500</strong>. Qualification requires active dual team placement (at least 1 direct Left + 1 direct Right sponsor).
                    </div>
                </div>

                <!-- Result Card -->
                <div class="md:col-span-5 bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-2xl p-6 shadow-md flex flex-col justify-between space-y-4">
                    <div class="space-y-1">
                        <span class="text-xs uppercase font-bold tracking-wider text-amber-100">Matching Reward</span>
                        <div class="text-3xl sm:text-4xl font-black tracking-tight" x-text="fmt(pairRewardTotal)"></div>
                    </div>

                    <div class="space-y-1.5 text-xs text-amber-100 border-t border-amber-400/40 pt-3">
                        <div class="flex justify-between">
                            <span>Formula:</span>
                            <span class="font-bold text-white"><span x-text="qualifiedPairs"></span> × ৳500</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Daily Cap:</span>
                            <span class="font-bold text-white">100 Pairs (৳50,000)</span>
                        </div>
                    </div>

                    <div class="text-[11px] text-amber-100/90 leading-snug">
                        “Subject to current SBL daily pair limit and qualification rules.”
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================
             5. GENERATION / UDR RATE TABLE
             ========================================== -->
        <section id="udr-section" class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-7 shadow-xs space-y-6 scroll-mt-20">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Generation / UDR Commission</h2>
                    <p class="text-xs sm:text-sm text-slate-500">Tier-based development bonus paid from active team downline volume.</p>
                </div>
                <div>
                    <button 
                        type="button" 
                        @click="showUdrExample = !showUdrExample"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 transition-colors cursor-pointer min-h-[40px]"
                    >
                        <span x-text="showUdrExample ? 'Hide Example' : 'View Example'"></span>
                    </button>
                </div>
            </div>

            <!-- Simple 6-Row Rate Table -->
            <div class="overflow-x-auto -mx-5 sm:mx-0">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-semibold">
                            <th class="py-3 px-4 sm:px-6">Generation</th>
                            <th class="py-3 px-4 sm:px-6">Rate</th>
                            <th class="py-3 px-4 sm:px-6">Eligible Sales Volume Basis</th>
                            <th class="py-3 px-4 sm:px-6 hidden sm:table-cell">Qualification Requirement</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">Gen 1</td>
                            <td class="py-3.5 px-4 sm:px-6 font-extrabold text-purple-600 text-base">10%</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-600">Direct Sponsor Personal Volume</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-500 hidden sm:table-cell">Active Starter or higher account</td>
                        </tr>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">Gen 2</td>
                            <td class="py-3.5 px-4 sm:px-6 font-extrabold text-purple-600 text-base">2%</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-600">2nd Generation Team Volume</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-500 hidden sm:table-cell">Active dual team with 2 direct sponsors</td>
                        </tr>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">Gen 3</td>
                            <td class="py-3.5 px-4 sm:px-6 font-extrabold text-purple-600 text-base">1%</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-600">3rd Generation Team Volume</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-500 hidden sm:table-cell">Active dual team with 4 direct sponsors</td>
                        </tr>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">Gen 4</td>
                            <td class="py-3.5 px-4 sm:px-6 font-extrabold text-purple-600 text-base">1%</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-600">4th Generation Team Volume</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-500 hidden sm:table-cell">FME or higher rank qualification</td>
                        </tr>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">Gen 5</td>
                            <td class="py-3.5 px-4 sm:px-6 font-extrabold text-purple-600 text-base">0.5%</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-600">5th Generation Team Volume</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-500 hidden sm:table-cell">SME or higher rank qualification</td>
                        </tr>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">Gen 6–10</td>
                            <td class="py-3.5 px-4 sm:px-6 font-extrabold text-purple-600 text-base">0.1%</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-600">6th to 10th Generation Team Volume</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-500 hidden sm:table-cell">PME to ETD leadership qualification</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-slate-500 italic">
                “Actual eligibility and commission depend on current SBL qualification and verified team activity.”
            </p>

            <!-- ==========================================
                 6. SIMPLE UDR EXAMPLE (Toggleable)
                 ========================================== -->
            <div 
                x-show="showUdrExample" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="bg-purple-50/70 border border-purple-200/80 rounded-2xl p-5 sm:p-6 space-y-4"
                style="display: none;"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-900">Realistic UDR Example</span>
                    <span class="text-xs text-purple-700 bg-purple-100 px-2.5 py-0.5 rounded-full font-medium">Illustration</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-white p-4 rounded-xl border border-purple-100">
                    <div>
                        <span class="text-xs text-slate-500 block">Generation</span>
                        <strong class="text-sm sm:text-base text-slate-900 font-bold">Gen 2</strong>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 block">Eligible Volume</span>
                        <strong class="text-sm sm:text-base text-slate-900 font-bold">৳100,000</strong>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 block">Rate</span>
                        <strong class="text-sm sm:text-base text-purple-700 font-extrabold">2%</strong>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 block">Estimated Commission</span>
                        <strong class="text-sm sm:text-base text-emerald-700 font-black">৳2,000</strong>
                    </div>
                </div>

                <p class="text-xs text-purple-950/80 leading-relaxed">
                    Explanation: If your 2nd generation associates generate a combined ৳100,000 in eligible business volume, your 2% UDR override yields <strong>৳2,000</strong>. This page focuses on realistic commission clarity rather than theoretical exponential growth.
                </p>
            </div>
        </section>

        <!-- ==========================================
             7. PLAN-BASED RETURN (Strictly Separated)
             ========================================== -->
        <section class="bg-slate-900 text-white rounded-2xl p-5 sm:p-7 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-800 pb-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                        ⚠️ Important Separation
                    </div>
                    <h2 class="text-xl font-bold tracking-tight text-white">Project Plan Return</h2>
                    <p class="text-xs sm:text-sm text-amber-200/90 font-medium">
                        “Plan-based project return, not affiliate commission.”
                    </p>
                </div>
                <div class="text-xs text-slate-400 max-w-xs sm:text-right">
                    Returns generated from live commercial dropshipping store sales across 100 weeks.
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- National Project Return Card -->
                <div class="bg-slate-800/90 border border-slate-700/80 rounded-xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Domestic Store</span>
                            <h3 class="text-lg font-bold text-white">National Dropshipping Project</h3>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 font-semibold border border-emerald-500/30">
                            1.75% / week
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs border-y border-slate-700/60 py-3">
                        <div>
                            <span class="text-slate-400 block">Total Package:</span>
                            <strong class="text-white text-sm">৳1,20,000</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Core Capital:</span>
                            <strong class="text-emerald-400 text-sm">৳100,000</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Weekly Return:</span>
                            <strong class="text-white text-sm">৳1,750 / week</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Duration:</span>
                            <strong class="text-white text-sm">100 Weeks</strong>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="text-slate-400">Total 100-Week Return:</span>
                        <strong class="text-base font-extrabold text-emerald-400">৳1,75,000</strong>
                    </div>
                </div>

                <!-- International Project Return Card -->
                <div class="bg-slate-800/90 border border-slate-700/80 rounded-xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-blue-400 uppercase tracking-wider">Cross-Border Store</span>
                            <h3 class="text-lg font-bold text-white">International Dropshipping Project</h3>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-lg bg-blue-500/20 text-blue-300 font-semibold border border-blue-500/30">
                            2.0% / week
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs border-y border-slate-700/60 py-3">
                        <div>
                            <span class="text-slate-400 block">Total Package:</span>
                            <strong class="text-white text-sm">৳5,50,000</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Core Capital:</span>
                            <strong class="text-blue-400 text-sm">৳500,000</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Weekly Return:</span>
                            <strong class="text-white text-sm">৳10,000 / week</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Duration:</span>
                            <strong class="text-white text-sm">100 Weeks</strong>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="text-slate-400">Total 100-Week Return:</span>
                        <strong class="text-base font-extrabold text-blue-400">৳10,00,000</strong>
                    </div>
                </div>

            </div>

            <p class="text-[11px] text-slate-400 leading-relaxed">
                * Note: Dropshipping returns are derived from commercial product sales performance. Participation in these projects is entirely independent of affiliate team building or sponsoring.
            </p>
        </section>

    </div>

    <!-- ==========================================
         8. COMMISSION DETAILS DRAWER / MODAL
         ========================================== -->
    <div 
        x-show="activeDrawer !== null" 
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex justify-end"
        style="display: none;"
    >
        <div 
            @click.away="activeDrawer = null"
            x-show="activeDrawer !== null"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="w-full max-w-md bg-white h-full shadow-2xl flex flex-col justify-between overflow-y-auto p-6 space-y-6"
        >
            <template x-if="activeDrawer && details[activeDrawer]">
                <div class="space-y-6">
                    <!-- Drawer Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="space-y-1">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400" x-text="details[activeDrawer].rate + ' Rate'"></span>
                            <h3 class="text-xl font-extrabold text-slate-900" x-text="details[activeDrawer].name"></h3>
                        </div>
                        <button 
                            type="button" 
                            @click="activeDrawer = null"
                            class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition-colors cursor-pointer"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Drawer Content -->
                    <div class="space-y-4 text-sm">
                        <!-- What is it -->
                        <div class="space-y-1">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Overview</h4>
                            <p class="text-slate-700 leading-relaxed" x-text="details[activeDrawer].summary"></p>
                        </div>

                        <!-- How it works -->
                        <div class="space-y-1 p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">How It Works</h4>
                            <p class="text-slate-800 text-xs sm:text-sm leading-relaxed" x-text="details[activeDrawer].howItWorks"></p>
                        </div>

                        <!-- Concrete Example -->
                        <div class="space-y-1 p-3.5 bg-emerald-50/60 rounded-xl border border-emerald-200/80">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-800">Verified Example</h4>
                            <p class="text-emerald-950 font-semibold text-xs sm:text-sm" x-text="details[activeDrawer].example"></p>
                        </div>

                        <!-- Eligibility -->
                        <div class="space-y-1">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Eligibility & Condition</h4>
                            <p class="text-slate-600 text-xs leading-relaxed" x-text="details[activeDrawer].eligibility"></p>
                        </div>

                        <!-- Note -->
                        <div class="text-[11px] text-slate-400 italic pt-2" x-text="details[activeDrawer].termsNote"></div>
                    </div>
                </div>
            </template>

            <!-- Drawer Actions -->
            <div class="pt-4 border-t border-slate-100 flex flex-col gap-2.5">
                <button 
                    type="button" 
                    @click="shareType(activeDrawer)"
                    class="w-full py-3 px-4 rounded-xl font-semibold text-sm bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-2 min-h-[44px]"
                >
                    <span>Share via WhatsApp</span>
                </button>
                <button 
                    type="button" 
                    @click="copyText(details[activeDrawer].name + ': ' + details[activeDrawer].rate + ' • ' + details[activeDrawer].example)"
                    class="w-full py-2.5 px-4 rounded-xl font-medium text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer flex items-center justify-center min-h-[40px]"
                >
                    <span>Copy Text</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ==========================================
         9. PRESENTATION VIEW (Digital Slide Mode)
         ========================================== -->
    <div 
        x-show="presentationMode" 
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-slate-950 text-white flex flex-col justify-between p-4 sm:p-8"
        style="display: none;"
    >
        <!-- Top Controls -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center font-black text-slate-950 text-sm">
                    SBL
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-white tracking-tight">SBL Commission Plan</h2>
                    <span class="text-[11px] text-slate-400">Slide <span x-text="currentSlide + 1"></span> of 4</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    @click="presentationMode = false"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors cursor-pointer min-h-[36px]"
                >
                    Exit ✕
                </button>
            </div>
        </div>

        <!-- Slide Content (Full Height Centered) -->
        <div class="max-w-2xl mx-auto w-full my-auto py-8">
            
            <!-- Slide 0: Spot Commission -->
            <div x-show="currentSlide === 0" class="space-y-6 text-center animate-fade-in">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    Stream 1 of 4 • Direct Referral
                </span>
                <div class="text-6xl sm:text-8xl font-black text-emerald-400 tracking-tight">
                    10%
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-white">
                    Spot / Direct Commission
                </h3>
                <p class="text-sm sm:text-base text-slate-300 max-w-lg mx-auto leading-relaxed">
                    Paid directly to your cash wallet upon direct sponsoring and verified package activation according to current SBL terms.
                </p>
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-xs sm:text-sm text-emerald-300 font-semibold max-w-md mx-auto">
                    Example: ৳120,000 Package = ৳12,000 Instant Commission
                </div>
            </div>

            <!-- Slide 1: Pair Matching Reward -->
            <div x-show="currentSlide === 1" class="space-y-6 text-center animate-fade-in" style="display: none;">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                    Stream 2 of 4 • Dual Team
                </span>
                <div class="text-6xl sm:text-8xl font-black text-amber-400 tracking-tight">
                    ৳500
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-white">
                    Pair Matching Reward
                </h3>
                <p class="text-sm sm:text-base text-slate-300 max-w-lg mx-auto leading-relaxed">
                    Earned whenever Left and Right team volume match in a balanced 1:1 ratio. Max daily cap of 100 pairs (৳50,000/day).
                </p>
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-xs sm:text-sm text-amber-300 font-semibold max-w-md mx-auto">
                    Example: 10 Matched Pairs in a Day = ৳5,000
                </div>
            </div>

            <!-- Slide 2: Referral Weekly Share -->
            <div x-show="currentSlide === 2" class="space-y-6 text-center animate-fade-in" style="display: none;">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                    Stream 3 of 4 • Ongoing Share
                </span>
                <div class="text-6xl sm:text-8xl font-black text-blue-400 tracking-tight">
                    0.25%
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-white">
                    Referral Weekly Share (100 Weeks)
                </h3>
                <p class="text-sm sm:text-base text-slate-300 max-w-lg mx-auto leading-relaxed">
                    Ongoing weekly sponsor incentive paid for up to 100 weeks based on participating National and International dropshipping capital.
                </p>
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-xs sm:text-sm text-blue-300 font-semibold max-w-md mx-auto">
                    Example: ৳100,000 Core Capital = ৳250/week (৳25,000 total)
                </div>
            </div>

            <!-- Slide 3: Generation / UDR Commission -->
            <div x-show="currentSlide === 3" class="space-y-6 text-center animate-fade-in" style="display: none;">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-500/20 text-purple-400 border border-purple-500/30">
                    Stream 4 of 4 • Multi-Tier Override
                </span>
                <div class="text-6xl sm:text-8xl font-black text-purple-400 tracking-tight">
                    10 Tiers
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-white">
                    Generation / UDR Commission
                </h3>
                <p class="text-sm sm:text-base text-slate-300 max-w-lg mx-auto leading-relaxed">
                    Tiered team overrides: Gen 1 (10%), Gen 2 (2%), Gen 3–4 (1%), Gen 5 (0.5%), Gen 6–10 (0.1%) based on verified team volume.
                </p>
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-xs sm:text-sm text-purple-300 font-semibold max-w-md mx-auto">
                    Example: ৳100,000 in Gen 2 Team Volume = ৳2,000 UDR Bonus
                </div>
            </div>

        </div>

        <!-- Bottom Controls -->
        <div class="flex items-center justify-between border-t border-slate-800 pt-4">
            <button 
                type="button" 
                @click="currentSlide = (currentSlide > 0 ? currentSlide - 1 : 3)"
                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-slate-800 hover:bg-slate-700 text-white transition-colors cursor-pointer min-h-[44px]"
            >
                ← Previous
            </button>

            <!-- Slide Indicator Dots -->
            <div class="flex items-center gap-2">
                <template x-for="i in [0, 1, 2, 3]" :key="i">
                    <button 
                        type="button" 
                        @click="currentSlide = i" 
                        :class="currentSlide === i ? 'w-6 bg-emerald-500' : 'w-2 bg-slate-700 hover:bg-slate-600'" 
                        class="h-2 rounded-full transition-all cursor-pointer"
                    ></button>
                </template>
            </div>

            <button 
                type="button" 
                @click="currentSlide = (currentSlide < 3 ? currentSlide + 1 : 0)"
                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors cursor-pointer min-h-[44px]"
            >
                Next →
            </button>
        </div>
    </div>

</div>
