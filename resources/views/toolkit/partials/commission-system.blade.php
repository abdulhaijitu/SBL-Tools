{{-- SBL Marketing: Polished Simple & Transparent Commission Plan --}}
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

    init() {
        // Enforce BDT by default on commission page to ensure currency consistency
        if (this.$store?.currency) {
            this.$store.currency.code = 'BDT';
            if (document.documentElement) {
                document.documentElement.dataset.currency = 'BDT';
            }
        }
    },

    get isUSD() {
        return this.$store?.currency?.code === 'USD';
    },

    get currSym() {
        return this.isUSD ? '$' : '৳';
    },

    // Dynamic Currency Formatter
    fmt(val) {
        let num = Number(val) || 0;
        if (this.isUSD) {
            let rate = Number(this.$store?.currency?.rate) || 120;
            let usd = Math.round(num / rate);
            return '$' + usd.toLocaleString('en-US');
        }
        return '৳' + Math.round(num).toLocaleString('en-IN');
    },

    // Detail Data for Drawers
    get details() {
        return {
            spot: {
                id: 'spot',
                name: 'Spot / Direct Commission',
                category: 'DIRECT',
                rate: '10%',
                accent: 'emerald',
                summary: 'Direct referral commission according to current SBL terms.',
                howItWorks: 'Whenever you directly sponsor any new SBL member or dropshipping project, 10% direct commission is credited to your verified wallet immediately upon activation according to current SBL terms.',
                example: this.fmt(120000) + ' package × 10% = ' + this.fmt(12000) + ' instant spot commission',
                eligibility: 'All active associates with Starter, National, or International membership in good standing.',
                termsNote: 'Calculated on package value according to official SBL terms.'
            },
            refer_return: {
                id: 'refer_return',
                name: 'Referral Weekly Share',
                category: 'WEEKLY SHARE',
                rate: '0.25% / week',
                accent: 'blue',
                summary: 'Weekly sponsor incentive for up to 100 weeks on verified participating project capital.',
                howItWorks: 'When you refer a National or International dropshipping project, you receive 0.25% weekly incentive on the participating core capital for up to 100 weeks, distributed alongside business operation cycles.',
                example: this.fmt(100000) + ' capital × 0.25% = ' + this.fmt(250) + ' / week (Total ' + this.fmt(25000) + ' across 100 weeks)',
                eligibility: 'Direct sponsors of verified participating dropshipping projects (Not applicable to Starter accounts).',
                termsNote: 'Subject to continuous project execution and verified active associate terms.'
            },
            pair_reward: {
                id: 'pair_reward',
                name: 'Pair Matching Reward',
                category: 'BINARY MATCHING',
                rate: this.fmt(500) + ' / Pair',
                accent: 'amber',
                summary: 'Dual team performance reward on 1:1 Left & Right volume match (max 100 pairs/day).',
                howItWorks: 'Binary matching bonus earned whenever team sales volume on your Left team and Right team match in a 1:1 ratio. Each qualified pair generates ' + this.fmt(500) + ', up to a maximum daily limit of 100 pairs (' + this.fmt(50000) + '/day).',
                example: '10 Qualified Pairs in a day = ' + this.fmt(5000) + ' matching reward',
                eligibility: 'Active account with at least 1 direct Left + 1 direct Right verified active sponsor.',
                termsNote: 'Subject to current SBL daily pair limit and qualification rules.'
            },
            udr: {
                id: 'udr',
                name: 'Generation / UDR Commission',
                category: 'TEAM OVERRIDE',
                rate: 'Up to 10 Tiers',
                accent: 'purple',
                summary: 'Tier-based development bonus paid from active team downline volume.',
                howItWorks: 'Unity Development Commission (UDR) distributes pre-defined percentage overrides from the active product sales and package volume generated across your extended referral network up to 10 generations deep.',
                example: 'Gen 2 Team Volume: ' + this.fmt(100000) + ' × 2% = ' + this.fmt(2000) + ' estimated commission',
                eligibility: 'Rank milestone qualifications (FME, SME, PME, etc.) and active team volume criteria.',
                termsNote: 'Actual eligibility and commission depend on current SBL qualification and verified team activity.'
            }
        };
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
            '3. Pair Matching Reward: ' + this.fmt(500) + ' / Pair\n' +
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
            'Rate: 1 Pair = ' + this.fmt(500) + '\n' +
            '• Total Matching Reward: ' + this.fmt(this.pairRewardTotal) + '\n' +
            '-----------------------------------------\n' +
            'Note: Subject to current SBL daily pair limit (max 100 pairs/day) and dual team qualification.\n' +
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5 sm:pt-6 space-y-7 sm:space-y-8">
        
        <!-- ==========================================
             1. PAGE HEADER (Compact & Light)
             ========================================== -->
        <header class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xs">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 sm:gap-5">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Official SBL Compensation Plan 2026
                    </div>
                    <h1 class="text-2xl sm:text-[28px] lg:text-[30px] font-extrabold text-slate-900 tracking-tight leading-tight">
                        SBL Commission Plan
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-600 max-w-2xl leading-normal">
                        Understand and calculate SBL earning opportunities.
                    </p>
                </div>
                
                <div class="flex flex-wrap items-center gap-2.5 shrink-0 pt-1 md:pt-0">
                    <button 
                        type="button" 
                        @click="presentationMode = true; currentSlide = 0"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-xs transition-colors cursor-pointer min-h-[44px]"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Presentation View</span>
                    </button>
                    
                    <button 
                        type="button" 
                        @click="shareOverview()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-medium text-xs sm:text-sm bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-700 border border-slate-300 shadow-xs transition-colors cursor-pointer min-h-[44px]"
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
             2. CORE COMMISSION STRUCTURE (4 Cards)
             ========================================== -->
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Core Commission Structure</h2>
                    <p class="text-xs sm:text-sm text-slate-500">The 4 primary earning streams for active SBL associates.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                
                <!-- Card 1: Spot Commission (Green) -->
                <div class="bg-white border border-slate-200/90 hover:border-slate-300 rounded-2xl p-5 sm:p-6 shadow-xs transition-all h-full flex flex-col justify-between group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold tracking-wider uppercase text-emerald-700">DIRECT</span>
                            <span class="text-xs font-medium text-slate-400">Stream 01</span>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-[17px] font-bold text-slate-900 leading-snug">Spot Commission</h3>
                            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1">10%</div>
                        </div>
                        <p class="text-xs sm:text-[13px] text-slate-600 leading-relaxed min-h-[38px]">
                            Direct referral commission according to current SBL terms.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-4">
                        <button 
                            type="button" 
                            @click="scrollToId('direct-calculator')"
                            class="text-xs sm:text-sm font-semibold text-emerald-700 hover:text-emerald-800 transition-colors cursor-pointer min-h-[44px] flex items-center gap-1"
                        >
                            Calculate
                        </button>
                        <button 
                            type="button" 
                            @click="activeDrawer = 'spot'"
                            class="text-xs sm:text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors cursor-pointer min-h-[44px] flex items-center gap-1"
                        >
                            Details →
                        </button>
                    </div>
                </div>

                <!-- Card 2: Referral Weekly Share (Blue) -->
                <div class="bg-white border border-slate-200/90 hover:border-slate-300 rounded-2xl p-5 sm:p-6 shadow-xs transition-all h-full flex flex-col justify-between group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold tracking-wider uppercase text-blue-700">WEEKLY SHARE</span>
                            <span class="text-xs font-medium text-slate-400">Stream 02</span>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-[17px] font-bold text-slate-900 leading-snug">Referral Weekly Share</h3>
                            <div class="text-2xl sm:text-3xl font-extrabold text-blue-600 mt-1">
                                0.25% <span class="text-xs font-semibold text-slate-500">/ week</span>
                            </div>
                        </div>
                        <p class="text-xs sm:text-[13px] text-slate-600 leading-relaxed min-h-[38px]">
                            Up to 100 weeks where applicable on verified participating project capital.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-4">
                        <button 
                            type="button" 
                            @click="scrollToId('direct-calculator')"
                            class="text-xs sm:text-sm font-semibold text-blue-700 hover:text-blue-800 transition-colors cursor-pointer min-h-[44px] flex items-center gap-1"
                        >
                            Calculate
                        </button>
                        <button 
                            type="button" 
                            @click="activeDrawer = 'refer_return'"
                            class="text-xs sm:text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors cursor-pointer min-h-[44px] flex items-center gap-1"
                        >
                            Details →
                        </button>
                    </div>
                </div>

                <!-- Card 3: Pair Matching Reward (Amber) -->
                <div class="bg-white border border-slate-200/90 hover:border-slate-300 rounded-2xl p-5 sm:p-6 shadow-xs transition-all h-full flex flex-col justify-between group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold tracking-wider uppercase text-amber-700">BINARY MATCHING</span>
                            <span class="text-xs font-medium text-slate-400">Stream 03</span>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-[17px] font-bold text-slate-900 leading-snug">Pair Matching Reward</h3>
                            <div class="text-2xl sm:text-3xl font-extrabold text-amber-600 mt-1">
                                <span x-text="fmt(500)">৳500</span> <span class="text-xs font-semibold text-slate-500">/ Pair</span>
                            </div>
                        </div>
                        <p class="text-xs sm:text-[13px] text-slate-600 leading-relaxed min-h-[38px]">
                            Daily cap according to current plan on 1:1 dual team volume match.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-4">
                        <button 
                            type="button" 
                            @click="scrollToId('pair-calculator')"
                            class="text-xs sm:text-sm font-semibold text-amber-700 hover:text-amber-800 transition-colors cursor-pointer min-h-[44px] flex items-center gap-1"
                        >
                            Calculate
                        </button>
                        <button 
                            type="button" 
                            @click="activeDrawer = 'pair_reward'"
                            class="text-xs sm:text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors cursor-pointer min-h-[44px] flex items-center gap-1"
                        >
                            Details →
                        </button>
                    </div>
                </div>

                <!-- Card 4: Generation / UDR Commission (Violet) -->
                <div class="bg-white border border-slate-200/90 hover:border-slate-300 rounded-2xl p-5 sm:p-6 shadow-xs transition-all h-full flex flex-col justify-between group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold tracking-wider uppercase text-purple-700">TEAM OVERRIDE</span>
                            <span class="text-xs font-medium text-slate-400">Stream 04</span>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-[17px] font-bold text-slate-900 leading-snug">Generation / UDR Commission</h3>
                            <div class="text-2xl sm:text-3xl font-extrabold text-purple-600 mt-1">
                                Up to 10 <span class="text-xs font-semibold text-slate-500">Tiers</span>
                            </div>
                        </div>
                        <p class="text-xs sm:text-[13px] text-slate-600 leading-relaxed min-h-[38px]">
                            Tier-based commission from active team downline volume (Gen 1: 10% to Gen 10: 0.1%).
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-4">
                        <button 
                            type="button" 
                            @click="scrollToId('udr-section')"
                            class="text-xs sm:text-sm font-semibold text-purple-700 hover:text-purple-800 transition-colors cursor-pointer min-h-[44px] flex items-center gap-1"
                        >
                            View Table
                        </button>
                        <button 
                            type="button" 
                            @click="activeDrawer = 'udr'"
                            class="text-xs sm:text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors cursor-pointer min-h-[44px] flex items-center gap-1"
                        >
                            Details →
                        </button>
                    </div>
                </div>

            </div>
        </section>

        <!-- ==========================================
             3. DIRECT COMMISSION CALCULATOR
             ========================================== -->
        <section id="direct-calculator" class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xs space-y-6 scroll-mt-20">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Direct Commission Calculator</h2>
                    <p class="text-xs sm:text-sm text-slate-500">Calculate instant 10% spot commission and eligible weekly referral share.</p>
                </div>
                <!-- Subtle Secondary Share Actions -->
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <button 
                        type="button" 
                        @click="shareDirectCalc()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 transition-colors cursor-pointer min-h-[36px]"
                    >
                        <span>WhatsApp</span>
                    </button>
                    <button 
                        type="button" 
                        @click="copyText('Direct Commission for ' + fmt(packageAmount) + ': Spot 10% = ' + fmt(spotCommission) + ', Weekly Share = ' + (isStarter ? 'N/A' : fmt(weeklyReferralShare) + '/wk'))"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 transition-colors cursor-pointer min-h-[36px]"
                    >
                        <span>Copy</span>
                    </button>
                </div>
            </div>

            <!-- Input & Presets -->
            <div class="space-y-3">
                <label class="block text-xs sm:text-sm font-semibold text-slate-700">
                    Package / Project Amount
                </label>
                
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    <!-- Clean Numeric Input -->
                    <div class="sm:col-span-5 relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-sm" x-text="currSym">৳</span>
                        <input 
                            type="number" 
                            x-model.number="packageAmount" 
                            min="0" 
                            step="1000"
                            inputmode="numeric"
                            class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-semibold text-slate-900 text-base min-h-[44px]"
                            placeholder="120000"
                        >
                    </div>
                    
                    <!-- Preset Chips -->
                    <div class="sm:col-span-7 flex flex-wrap items-center gap-2">
                        <button 
                            type="button" 
                            @click="packageAmount = 10000"
                            :class="packageAmount === 10000 ? 'bg-emerald-600 text-white font-bold border-emerald-600 ring-2 ring-emerald-500/20' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                            class="flex-1 py-2 px-3 text-xs sm:text-sm rounded-xl border transition-colors cursor-pointer min-h-[44px] text-center flex flex-col justify-center"
                        >
                            <span class="font-bold" x-text="fmt(10000)">৳10,000</span>
                            <span class="text-xs opacity-80 font-normal">Starter</span>
                        </button>
                        <button 
                            type="button" 
                            @click="packageAmount = 120000"
                            :class="packageAmount === 120000 ? 'bg-emerald-600 text-white font-bold border-emerald-600 ring-2 ring-emerald-500/20' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                            class="flex-1 py-2 px-3 text-xs sm:text-sm rounded-xl border transition-colors cursor-pointer min-h-[44px] text-center flex flex-col justify-center"
                        >
                            <span class="font-bold" x-text="fmt(120000)">৳120,000</span>
                            <span class="text-xs opacity-80 font-normal">National</span>
                        </button>
                        <button 
                            type="button" 
                            @click="packageAmount = 550000"
                            :class="packageAmount === 550000 ? 'bg-emerald-600 text-white font-bold border-emerald-600 ring-2 ring-emerald-500/20' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                            class="flex-1 py-2 px-3 text-xs sm:text-sm rounded-xl border transition-colors cursor-pointer min-h-[44px] text-center flex flex-col justify-center"
                        >
                            <span class="font-bold" x-text="fmt(550000)">৳550,000</span>
                            <span class="text-xs opacity-80 font-normal">International</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Two Equal Result Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                
                <!-- Spot Result Card -->
                <div class="p-5 sm:p-6 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 space-y-2 flex flex-col justify-between">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-800 tracking-wider uppercase">Spot Commission</span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">10% Instant</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-emerald-700" x-text="fmt(spotCommission)"></div>
                    </div>
                    <div class="pt-2 border-t border-emerald-100/80 text-xs text-slate-600">
                        Formula: <span x-text="fmt(packageAmount)"></span> × 10% = <strong class="text-emerald-800" x-text="fmt(spotCommission)"></strong>
                    </div>
                </div>

                <!-- Referral Weekly Share Result Card -->
                <div class="p-5 sm:p-6 rounded-2xl bg-blue-50/60 border border-blue-200/80 space-y-2 flex flex-col justify-between">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-blue-800 tracking-wider uppercase">Referral Weekly Share</span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">0.25% / Week</span>
                        </div>
                        
                        <template x-if="isStarter">
                            <div>
                                <div class="text-2xl sm:text-3xl font-extrabold text-slate-400">Not Applicable</div>
                            </div>
                        </template>
                        
                        <template x-if="!isStarter">
                            <div class="text-2xl sm:text-3xl font-extrabold text-blue-700">
                                <span x-text="fmt(weeklyReferralShare)"></span>
                                <span class="text-xs font-semibold text-slate-500">/ week</span>
                            </div>
                        </template>
                    </div>
                    
                    <div class="pt-2 border-t border-blue-100/80 text-xs text-slate-600">
                        <template x-if="isStarter">
                            <span>Starter membership does not have participating project capital.</span>
                        </template>
                        <template x-if="!isStarter">
                            <span>Eligible Capital: <span x-text="fmt(eligibleCapital)"></span> × 0.25% = <strong class="text-blue-800" x-text="fmt(weeklyReferralShare)"></strong> / week (100 wks: <strong class="text-blue-800" x-text="fmt(totalReferralShare100Weeks)"></strong>)</span>
                        </template>
                    </div>
                </div>

            </div>

            <p class="text-xs text-slate-400 italic">
                * Note: Weekly referral share is applicable only to participating Dropshipping project capital under current official SBL business terms.
            </p>
        </section>

        <!-- ==========================================
             4. PAIR MATCHING REWARD CALCULATOR
             ========================================== -->
        <section id="pair-calculator" class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xs space-y-6 scroll-mt-20">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Pair Matching Reward Calculator</h2>
                    <p class="text-xs sm:text-sm text-slate-500">Calculate dual team matching rewards based on qualified 1:1 pairs.</p>
                </div>
                <!-- Subtle Secondary Share Actions -->
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <button 
                        type="button" 
                        @click="sharePairCalc()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 transition-colors cursor-pointer min-h-[36px]"
                    >
                        <span>WhatsApp</span>
                    </button>
                    <button 
                        type="button" 
                        @click="copyText(qualifiedPairs + ' Pairs @ ' + fmt(500) + '/pair = ' + fmt(pairRewardTotal))"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 transition-colors cursor-pointer min-h-[36px]"
                    >
                        <span>Copy</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-stretch">
                <!-- Inputs & Presets -->
                <div class="md:col-span-7 space-y-4 flex flex-col justify-between">
                    <div class="space-y-3">
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
                                inputmode="numeric"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-semibold text-slate-900 text-base min-h-[44px]"
                                placeholder="10"
                            >
                        </div>

                        <!-- Segmented Preset Chips (Min 44px touch targets) -->
                        <div class="space-y-1.5">
                            <span class="text-xs font-medium text-slate-400">Quick Select:</span>
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                <template x-for="p in [1, 5, 10, 20, 50, 100]" :key="p">
                                    <button 
                                        type="button" 
                                        @click="qualifiedPairs = p"
                                        :class="qualifiedPairs === p ? 'bg-amber-600 text-white font-bold border-amber-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                                        class="px-2.5 py-2 rounded-xl text-xs sm:text-sm border transition-colors cursor-pointer min-h-[44px] flex items-center justify-center font-semibold"
                                        x-text="p"
                                    ></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs text-slate-600 leading-relaxed">
                        Official Rate: 1 Pair = <strong class="text-slate-900" x-text="fmt(500)">৳500</strong>. Qualification requires active dual team placement (at least 1 direct Left + 1 direct Right sponsor).
                    </div>
                </div>

                <!-- Clean Amber Result Card (Not a large heavy solid block) -->
                <div class="md:col-span-5 bg-amber-50/70 border border-amber-200/90 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
                    <div class="space-y-1.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Matching Reward</span>
                        <div class="text-2xl sm:text-3xl font-extrabold text-amber-700 tracking-tight" x-text="fmt(pairRewardTotal)"></div>
                    </div>

                    <div class="space-y-2 text-xs text-slate-600 border-t border-amber-200/70 pt-3">
                        <div class="flex justify-between">
                            <span>Formula:</span>
                            <span class="font-bold text-slate-800"><span x-text="qualifiedPairs"></span> Pairs × <span x-text="fmt(500)"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Daily Cap:</span>
                            <span class="font-bold text-slate-800">100 Pairs (<span x-text="fmt(50000)"></span>)</span>
                        </div>
                    </div>

                    <div class="text-xs text-slate-500 italic pt-1">
                        “Subject to current SBL daily pair limit and qualification rules.”
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================
             5. GENERATION / UDR COMMISSION
             ========================================== -->
        <section id="udr-section" class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5 scroll-mt-20">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Generation / UDR Commission</h2>
                    <p class="text-xs sm:text-sm text-slate-500">Tier-based development bonus paid from active team downline volume.</p>
                </div>
                <div>
                    <button 
                        type="button" 
                        @click="showUdrExample = !showUdrExample"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 transition-colors cursor-pointer min-h-[44px]"
                    >
                        <span x-text="showUdrExample ? 'Hide Example' : 'View Example'"></span>
                    </button>
                </div>
            </div>

            <!-- Desktop View: Clean Rate Table -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-semibold">
                            <th class="py-3 px-4 sm:px-6">Generation</th>
                            <th class="py-3 px-4 sm:px-6">Rate</th>
                            <th class="py-3 px-4 sm:px-6">Eligible Volume Basis</th>
                            <th class="py-3 px-4 sm:px-6">Qualification Requirement</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">Gen 1</td>
                            <td class="py-3.5 px-4 sm:px-6 font-extrabold text-purple-600 text-base">10%</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-600">Direct Sponsor Personal Volume</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-500">Active Starter or higher account</td>
                        </tr>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">Gen 2</td>
                            <td class="py-3.5 px-4 sm:px-6 font-extrabold text-purple-600 text-base">2%</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-600">2nd Generation Team Volume</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-500">Active dual team with 2 direct sponsors</td>
                        </tr>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">Gen 3</td>
                            <td class="py-3.5 px-4 sm:px-6 font-extrabold text-purple-600 text-base">1%</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-600">3rd Generation Team Volume</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-500">Active dual team with 4 direct sponsors</td>
                        </tr>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">Gen 4</td>
                            <td class="py-3.5 px-4 sm:px-6 font-extrabold text-purple-600 text-base">1%</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-600">4th Generation Team Volume</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-500">FME or higher rank qualification</td>
                        </tr>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">Gen 5</td>
                            <td class="py-3.5 px-4 sm:px-6 font-extrabold text-purple-600 text-base">0.5%</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-600">5th Generation Team Volume</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-500">SME or higher rank qualification</td>
                        </tr>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">Gen 6–10</td>
                            <td class="py-3.5 px-4 sm:px-6 font-extrabold text-purple-600 text-base">0.1%</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-600">6th to 10th Generation Team Volume</td>
                            <td class="py-3.5 px-4 sm:px-6 text-slate-500">PME to ETD leadership qualification</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View: Compact Mobile Cards (No wide overflow) -->
            <div class="sm:hidden space-y-2.5">
                <div class="p-3.5 rounded-xl border border-slate-200/90 bg-white flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-900">Gen 1</span>
                        <p class="text-xs text-slate-500 mt-0.5">Direct Sponsor Personal Volume</p>
                    </div>
                    <span class="text-base font-extrabold text-purple-600">10%</span>
                </div>
                <div class="p-3.5 rounded-xl border border-slate-200/90 bg-white flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-900">Gen 2</span>
                        <p class="text-xs text-slate-500 mt-0.5">2nd Generation Team Volume</p>
                    </div>
                    <span class="text-base font-extrabold text-purple-600">2%</span>
                </div>
                <div class="p-3.5 rounded-xl border border-slate-200/90 bg-white flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-900">Gen 3</span>
                        <p class="text-xs text-slate-500 mt-0.5">3rd Generation Team Volume</p>
                    </div>
                    <span class="text-base font-extrabold text-purple-600">1%</span>
                </div>
                <div class="p-3.5 rounded-xl border border-slate-200/90 bg-white flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-900">Gen 4</span>
                        <p class="text-xs text-slate-500 mt-0.5">4th Generation Team Volume</p>
                    </div>
                    <span class="text-base font-extrabold text-purple-600">1%</span>
                </div>
                <div class="p-3.5 rounded-xl border border-slate-200/90 bg-white flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-900">Gen 5</span>
                        <p class="text-xs text-slate-500 mt-0.5">5th Generation Team Volume</p>
                    </div>
                    <span class="text-base font-extrabold text-purple-600">0.5%</span>
                </div>
                <div class="p-3.5 rounded-xl border border-slate-200/90 bg-white flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-900">Gen 6–10</span>
                        <p class="text-xs text-slate-500 mt-0.5">6th–10th Generation Team Volume</p>
                    </div>
                    <span class="text-base font-extrabold text-purple-600">0.1%</span>
                </div>
            </div>

            <p class="text-xs text-slate-500 italic">
                “Actual eligibility and commission depend on current SBL qualification and verified team activity.”
            </p>

            <!-- Toggleable Simple Example Card -->
            <div 
                x-show="showUdrExample" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="bg-purple-50/70 border border-purple-200/80 rounded-2xl p-5 sm:p-6 space-y-3"
                style="display: none;"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-900">Realistic UDR Example</span>
                    <span class="text-xs text-purple-700 bg-purple-100 px-2.5 py-0.5 rounded-full font-medium">Illustration</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white p-4 rounded-xl border border-purple-100">
                    <div>
                        <span class="text-xs text-slate-500 block">Generation</span>
                        <strong class="text-sm sm:text-base text-slate-900 font-bold">Gen 2</strong>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 block">Eligible Volume</span>
                        <strong class="text-sm sm:text-base text-slate-900 font-bold" x-text="fmt(100000)">৳100,000</strong>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 block">Rate</span>
                        <strong class="text-sm sm:text-base text-purple-700 font-extrabold">2%</strong>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 block">Estimated Commission</span>
                        <strong class="text-sm sm:text-base text-emerald-700 font-black" x-text="fmt(2000)">৳2,000</strong>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Explanation: If your 2nd generation associates generate a combined <span class="font-bold" x-text="fmt(100000)"></span> in eligible business volume, your 2% UDR override yields <strong class="text-emerald-700" x-text="fmt(2000)"></strong>. This page focuses on realistic commission clarity rather than theoretical exponential growth.
                </p>
            </div>
        </section>

        <!-- ==========================================
             6. PROJECT PLAN RETURN (Light Premium Style)
             ========================================== -->
        <section class="bg-slate-100/70 border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200/80 pb-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-slate-100">
                        Plan-based project return, not affiliate commission.
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold tracking-tight text-slate-900">Project Plan Return</h2>
                    <p class="text-xs sm:text-sm text-slate-600">
                        Commercial dropshipping store returns on participating core capital (100 weeks duration).
                    </p>
                </div>
            </div>

            <!-- Two Clean White Cards with Subtle Navy Top Border -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- National Project Return Card -->
                <div class="bg-white border-t-4 border-t-slate-800 border-x border-b border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Domestic Store</span>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">National Dropshipping</h3>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200/80">
                            1.75% / week
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs border-y border-slate-100 py-3.5">
                        <div>
                            <span class="text-slate-500 block">Total Package:</span>
                            <strong class="text-slate-900 text-sm" x-text="fmt(120000)">৳1,20,000</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Core Capital:</span>
                            <strong class="text-emerald-700 text-sm" x-text="fmt(100000)">৳100,000</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Weekly Return:</span>
                            <strong class="text-slate-900 text-sm"><span x-text="fmt(1750)">৳1,750</span> / wk</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Duration:</span>
                            <strong class="text-slate-900 text-sm">100 Weeks</strong>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-0.5">
                        <span class="text-slate-500 font-medium">Total 100-Week Return:</span>
                        <strong class="text-base font-extrabold text-emerald-700" x-text="fmt(175000)">৳1,75,000</strong>
                    </div>
                </div>

                <!-- International Project Return Card -->
                <div class="bg-white border-t-4 border-t-slate-800 border-x border-b border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Cross-Border Store</span>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">International Dropshipping</h3>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold border border-blue-200/80">
                            2.0% / week
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs border-y border-slate-100 py-3.5">
                        <div>
                            <span class="text-slate-500 block">Total Package:</span>
                            <strong class="text-slate-900 text-sm" x-text="fmt(550000)">৳5,50,000</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Core Capital:</span>
                            <strong class="text-blue-700 text-sm" x-text="fmt(500000)">৳500,000</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Weekly Return:</span>
                            <strong class="text-slate-900 text-sm"><span x-text="fmt(10000)">৳10,000</span> / wk</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Duration:</span>
                            <strong class="text-slate-900 text-sm">100 Weeks</strong>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-0.5">
                        <span class="text-slate-500 font-medium">Total 100-Week Return:</span>
                        <strong class="text-base font-extrabold text-blue-700" x-text="fmt(1000000)">৳10,00,000</strong>
                    </div>
                </div>

            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                * Note: Dropshipping returns are derived from commercial product sales performance and store fulfillment. Participation is independent of affiliate network commissions and not a guaranteed fixed deposit.
            </p>
        </section>

    </div>

    <!-- ==========================================
         7. COMMISSION DETAILS DRAWER
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
                    <div class="space-y-4 text-xs sm:text-sm">
                        <!-- Overview -->
                        <div class="space-y-1">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Overview</h4>
                            <p class="text-slate-700 leading-relaxed" x-text="details[activeDrawer].summary"></p>
                        </div>

                        <!-- How it works -->
                        <div class="space-y-1 p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">How It Works</h4>
                            <p class="text-slate-800 text-xs sm:text-sm leading-relaxed" x-text="details[activeDrawer].howItWorks"></p>
                        </div>

                        <!-- Verified Example -->
                        <div class="space-y-1 p-3.5 bg-emerald-50/60 rounded-xl border border-emerald-200/80">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-800">Verified Example</h4>
                            <p class="text-emerald-950 font-semibold text-xs sm:text-sm" x-text="details[activeDrawer].example"></p>
                        </div>

                        <!-- Eligibility -->
                        <div class="space-y-1">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Eligibility & Condition</h4>
                            <p class="text-slate-600 text-xs leading-relaxed" x-text="details[activeDrawer].eligibility"></p>
                        </div>

                        <!-- Terms Note -->
                        <div class="text-xs text-slate-400 italic pt-1" x-text="details[activeDrawer].termsNote"></div>
                    </div>
                </div>
            </template>

            <!-- Drawer Actions -->
            <div class="pt-4 border-t border-slate-100 flex flex-col gap-2.5">
                <button 
                    type="button" 
                    @click="shareType(activeDrawer)"
                    class="w-full py-3 px-4 rounded-xl font-semibold text-xs sm:text-sm bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-2 min-h-[44px]"
                >
                    <span>Share via WhatsApp</span>
                </button>
                <button 
                    type="button" 
                    @click="copyText(details[activeDrawer].name + ': ' + details[activeDrawer].rate + ' • ' + details[activeDrawer].example)"
                    class="w-full py-2.5 px-4 rounded-xl font-medium text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer flex items-center justify-center min-h-[44px]"
                >
                    <span>Copy Text</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ==========================================
         8. PRESENTATION VIEW (Digital Slide Mode)
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
                    <span class="text-xs text-slate-400">Slide <span x-text="currentSlide + 1"></span> of 4</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    @click="presentationMode = false"
                    class="px-3.5 py-2 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors cursor-pointer min-h-[44px] flex items-center justify-center"
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
                    Spot Commission
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 max-w-lg mx-auto leading-relaxed">
                    Direct referral commission according to current SBL terms.
                </p>
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-xs sm:text-sm text-emerald-300 font-semibold max-w-md mx-auto">
                    Example: <span x-text="fmt(120000)"></span> Package = <span x-text="fmt(12000)"></span> Instant Commission
                </div>
            </div>

            <!-- Slide 1: Pair Matching Reward -->
            <div x-show="currentSlide === 1" class="space-y-6 text-center animate-fade-in" style="display: none;">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                    Stream 2 of 4 • Dual Team
                </span>
                <div class="text-6xl sm:text-8xl font-black text-amber-400 tracking-tight">
                    <span x-text="fmt(500)">৳500</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-white">
                    Pair Matching Reward
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 max-w-lg mx-auto leading-relaxed">
                    Earned whenever Left and Right team volume match in a balanced 1:1 ratio. Max daily cap of 100 pairs (<span x-text="fmt(50000)"></span>/day).
                </p>
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-xs sm:text-sm text-amber-300 font-semibold max-w-md mx-auto">
                    Example: 10 Matched Pairs in a Day = <span x-text="fmt(5000)"></span>
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
                <p class="text-xs sm:text-sm text-slate-300 max-w-lg mx-auto leading-relaxed">
                    Ongoing weekly sponsor incentive paid for up to 100 weeks based on participating National and International dropshipping capital.
                </p>
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-xs sm:text-sm text-blue-300 font-semibold max-w-md mx-auto">
                    Example: <span x-text="fmt(100000)"></span> Core Capital = <span x-text="fmt(250)"></span>/week (<span x-text="fmt(25000)"></span> total)
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
                <p class="text-xs sm:text-sm text-slate-300 max-w-lg mx-auto leading-relaxed">
                    Tiered team overrides: Gen 1 (10%), Gen 2 (2%), Gen 3–4 (1%), Gen 5 (0.5%), Gen 6–10 (0.1%) based on verified team volume.
                </p>
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-xs sm:text-sm text-purple-300 font-semibold max-w-md mx-auto">
                    Example: <span x-text="fmt(100000)"></span> in Gen 2 Team Volume = <span x-text="fmt(2000)"></span> UDR Bonus
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
                        class="h-2 rounded-full transition-all cursor-pointer min-h-[20px]"
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
