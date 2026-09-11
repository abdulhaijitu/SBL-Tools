@php
    $ranksData = $rankConfig ?? app(App\Http\Controllers\SblToolkitController::class)->getRankConfig();
    $streamsData = $marketingPlanConfig ?? app(App\Http\Controllers\SblToolkitController::class)->getMarketingPlanConfig();
    $matrixData = $generationMatrix ?? app(App\Http\Controllers\SblToolkitController::class)->getGenerationMatrix();
    $initialRank = request('rank', 'fme');
    if (!isset($ranksData[$initialRank])) {
        $initialRank = 'fme';
    }
@endphp

<div 
    id="sbl-rank-system"
    class="space-y-10 pb-20 select-text"
    x-data="{
        // Centralized Data
        ranks: @js($ranksData),
        streams: @js($streamsData['streams']),
        matrix: @js($matrixData),
        activeRankKey: @js($initialRank),
        
        // Navigation & Views
        activeTab: 'cards',
        showAllGenerations: false,
        showAdvancedGen: false,
        copiedToast: false,
        toastMessage: 'Copied to clipboard!',

        // Generation Calculator
        calcGen: 1,
        calcMembers: 10,
        calcAmount: 10000,
        get currentGenRow() {
            return this.matrix.find(m => m.generation === parseInt(this.calcGen)) || this.matrix[0];
        },
        get currentGenRate() {
            return this.currentGenRow.rate_num || 0.10;
        },
        get calculatedCommission() {
            const members = Math.max(0, parseInt(this.calcMembers) || 0);
            const amount = Math.max(0, parseFloat(this.calcAmount) || 0);
            return Math.round(members * amount * this.currentGenRate);
        },

        // Rank Progress Calculator
        progress: {
            directs: 10,
            leftDirects: 5,
            rightDirects: 5,
            pairs: 15,
            smeLeft: 0,
            smeRight: 0,
            pmeLeft: 0,
            pmeRight: 0,
            bmeLeft: 0,
            bmeRight: 0,
            gmeLeft: 0,
            gmeRight: 0,

            get evaluatedRank() {
                const directs = parseInt(this.directs) || 0;
                const left = parseInt(this.leftDirects) || 0;
                const right = parseInt(this.rightDirects) || 0;
                const pairs = parseInt(this.pairs) || 0;
                const smeL = parseInt(this.smeLeft) || 0;
                const smeR = parseInt(this.smeRight) || 0;
                const pmeL = parseInt(this.pmeLeft) || 0;
                const pmeR = parseInt(this.pmeRight) || 0;
                const bmeL = parseInt(this.bmeLeft) || 0;
                const bmeR = parseInt(this.bmeRight) || 0;
                const gmeL = parseInt(this.gmeLeft) || 0;
                const gmeR = parseInt(this.gmeRight) || 0;

                // ETD
                if (gmeL >= 7 && gmeR >= 3) return 'ETD';
                // GME
                if (bmeL >= 8 && bmeR >= 4) return 'GME';
                // BME
                if (pmeL >= 10 && pmeR >= 5) return 'BME';
                // PME
                if (smeL >= 13 && smeR >= 7) return 'PME';
                // SME
                if (pairs >= 300) return 'SME';
                // FME
                if (directs >= 10 && left >= 5 && right >= 5) return 'FME';

                return 'Member';
            },

            get nextTargetRank() {
                const current = this.evaluatedRank;
                if (current === 'Member') return 'FME';
                if (current === 'FME') return 'SME';
                if (current === 'SME') return 'PME';
                if (current === 'PME') return 'BME';
                if (current === 'BME') return 'GME';
                if (current === 'GME') return 'ETD';
                return 'ETD';
            },

            get missingRequirements() {
                const next = this.nextTargetRank;
                const left = parseInt(this.leftDirects) || 0;
                const right = parseInt(this.rightDirects) || 0;
                const directs = parseInt(this.directs) || 0;
                const pairs = parseInt(this.pairs) || 0;
                const smeL = parseInt(this.smeLeft) || 0;
                const smeR = parseInt(this.smeRight) || 0;

                const missing = [];
                if (next === 'FME') {
                    if (directs < 10) missing.push(`${10 - directs} more direct sponsors (Total 10)`);
                    if (left < 5) missing.push(`${5 - left} more direct sponsors in Left team`);
                    if (right < 5) missing.push(`${5 - right} more direct sponsors in Right team`);
                } else if (next === 'SME') {
                    if (pairs < 300) missing.push(`${300 - pairs} more binary matching pairs (Goal: 300)`);
                } else if (next === 'PME') {
                    if (smeL < 13) missing.push(`${13 - smeL} more SME leaders in Left team`);
                    if (smeR < 7) missing.push(`${7 - smeR} more SME leaders in Right team`);
                } else if (next === 'BME') {
                    missing.push('10 PME leaders in Left team & 5 PME in Right team');
                } else if (next === 'GME') {
                    missing.push('8 BME leaders in Left team & 4 BME in Right team');
                } else if (next === 'ETD') {
                    missing.push('7 GME leaders in Left team & 3 GME in Right team');
                }
                return missing;
            }
        },

        // Lead Modal State
        leadModalOpen: false,
        leadTargetRank: 'FME',
        leadInterest: 'Rank Progression',
        leadForm: {
            name: '',
            mobile: '',
            notes: '',
            submitting: false,
            success: false,
            error: null
        },

        // Share System
        shareModalOpen: false,
        shareRankKey: 'fme',

        init() {
            // Check query param for rank
            const params = new URLSearchParams(window.location.search);
            const r = params.get('rank');
            if (r && this.ranks[r.toLowerCase()]) {
                this.activeRankKey = r.toLowerCase();
                this.shareRankKey = r.toLowerCase();
                // Scroll to rank section smoothly if loaded with param
                this.$nextTick(() => {
                    const el = document.getElementById('rank-' + this.activeRankKey);
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            }
        },

        selectRank(key) {
            this.activeRankKey = key;
            this.shareRankKey = key;
            const url = new URL(window.location);
            url.searchParams.set('rank', key);
            window.history.replaceState({}, '', url);
        },

        formatNumber(num) {
            return new Intl.NumberFormat('en-IN').format(num);
        },

        formatMoney(num) {
            return '৳' + this.formatNumber(num);
        },

        triggerToast(msg) {
            this.toastMessage = msg;
            this.copiedToast = true;
            setTimeout(() => { this.copiedToast = false; }, 2500);
        },

        copyText(text, successMsg = 'Copied to clipboard!') {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    this.triggerToast(successMsg);
                }).catch(() => {
                    this.fallbackCopy(text, successMsg);
                });
            } else {
                this.fallbackCopy(text, successMsg);
            }
        },

        fallbackCopy(text, successMsg) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                this.triggerToast(successMsg);
            } catch (err) {
                this.triggerToast('Copy failed. Please manually copy.');
            }
            document.body.removeChild(textArea);
        },

        openLeadModal(rankCode = 'FME', interest = 'Rank Progression') {
            this.leadTargetRank = rankCode;
            this.leadInterest = interest;
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

            const payload = {
                name: this.leadForm.name,
                mobile: this.leadForm.mobile,
                lead_source_id: 1,
                lead_source_detail: 'Ranks Page - ' + this.leadTargetRank,
                lead_tag: 'Ranks',
                interest_types: [this.leadInterest || 'Rank Progression'],
                notes: `Target Rank: ${this.leadTargetRank} | Interest: ${this.leadInterest}. ${this.leadForm.notes || ''}`
            };

            try {
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';
                const res = await fetch('/leads', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (res.ok && (data.success || data.lead_id || data.status === 'success')) {
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
                this.leadForm.error = 'Connection issue. Please check your internet and try again.';
            } finally {
                this.leadForm.submitting = false;
            }
        },

        getRankSummaryText(rank) {
            return `🏆 SBL Rank: ${rank.name} (${rank.code})
🎖️ Badge: ${rank.badge}
💰 Milestone Cash Reward: ${rank.cashReward_formatted}
🎯 Eligibility Requirement: ${rank.requirement}
ℹ️ SBL Verified Plan 2026. Terms and active qualification apply.
🔗 View details: ${window.location.origin}/ranks?rank=${rank.code.toLowerCase()}`;
        },

        getMarketingPlanSummaryText() {
            return `🌟 SBL Marketing Plan & Career Summary 2026
💼 Entry Membership: ৳10,000 (Dropshipping & Affiliate Setup)
⚡ 5 Earning Streams:
1. Spot / Direct Commission: 10% instant bonus
2. Pair Matching Reward: ৳500 / Pair (Daily cap 100 PR = ৳50,000)
3. Unity Development (UDR): Up to 5% across 10 generations
4. Rank Milestone Rewards: ৳5,000 up to ৳20,00,000 (Total ~৳40 Lac)
5. Refer Return: 0.25% weekly share for 100 weeks

🏆 Official Ranks:
• FME: 10 Directs -> ৳5,000 Cash
• SME: 300 Matching Pairs -> ৳50,000 Cash
• PME: Team SME (13L/7R) -> ৳1,00,000 Cash
• BME: Team PME (10L/5R) -> ৳5,00,000 Cash
• GME: Team BME (8L/4R) -> ৳10,00,000 Cash
• ETD: Team GME (7L/3R) -> ৳20,00,000 Cash

ℹ️ Performance-based business opportunity. Terms apply.
🔗 Explore Ranks & Calculators: ${window.location.origin}/ranks`;
        },

        shareRank(rankKey) {
            const rank = this.ranks[rankKey];
            if (!rank) return;
            const url = `${window.location.origin}/ranks?rank=${rank.code.toLowerCase()}`;
            const text = this.getRankSummaryText(rank);

            if (navigator.share && window.innerWidth < 768) {
                navigator.share({
                    title: `SBL ${rank.name} (${rank.code})`,
                    text: text,
                    url: url
                }).catch(() => {});
                return;
            }

            this.shareRankKey = rankKey;
            this.shareModalOpen = true;
        },

        shareWhatsApp(text, url) {
            const fullText = text + '\n' + url;
            window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(fullText)}`, '_blank');
        },

        shareFacebook(url) {
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
        }
    }"
>

    <!-- TOAST NOTIFICATION -->
    <div 
        x-show="copiedToast" 
        x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-3"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-semibold shadow-xl border border-slate-700"
    >
        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span x-text="toastMessage"></span>
    </div>

    <!-- ========================================================================= -->
    <!-- 01. COMPACT HERO / HEADER SECTION -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden rounded-2xl bg-slate-900 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-6 sm:p-8 lg:p-10 shadow-lg border border-slate-800">
        <!-- Ambient decorative shapes -->
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <!-- Verified Badge & Status -->
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-orange-500/20 text-orange-300 border border-orange-500/30">
                        <svg class="w-3.5 h-3.5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span data-en="Official SBL Plan 2026" data-bn="অফিসিয়াল SBL প্ল্যান ২০২৬">Official SBL Plan 2026</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span data-en="Active Career System" data-bn="সক্রিয় ক্যারিয়ার ব্যবস্থা">Active Career System</span>
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight">
                    <span data-en="SBL Career Ranks & Recognition" data-bn="SBL ক্যারিয়ার র‍্যাংক ও স্বীকৃতি সম্মাননা">SBL Career Ranks & Recognition</span>
                </h1>

                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed" data-en="Build balanced dual sales teams, achieve verified career leadership ranks, and unlock cumulative cash rewards up to ৳40,00,000 BDT through transparent performance milestones." data-bn="উভয় টিমে সক্রিয় নেতৃত্ব গড়ে তুলুন, ক্যারিয়ার র‍্যাংক অর্জন করুন এবং স্বচ্ছ মাইলফলকের মাধ্যমে সর্বোচ্চ ৪০ লাখ টাকা পর্যন্ত ক্যাশ রিওয়ার্ড ও স্থায়ী আয় নিশ্চিত করুন।">
                    Build balanced dual sales teams, achieve verified career leadership ranks, and unlock cumulative cash rewards up to ৳40,00,000 BDT through transparent performance milestones.
                </p>

                <!-- Quick highlights chip row -->
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 pt-1 text-xs text-slate-300">
                    <div class="flex items-center gap-1.5">
                        <span class="text-amber-400 font-bold">✓ 6 Ranks</span>
                        <span class="text-slate-500">|</span>
                        <span>FME to ETD</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-orange-400 font-bold">✓ 5 Streams</span>
                        <span class="text-slate-500">|</span>
                        <span>Binary & Generation</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-emerald-400 font-bold">✓ Cash Milestones</span>
                        <span class="text-slate-500">|</span>
                        <span>৳5,000 to ৳20 Lac</span>
                    </div>
                </div>
            </div>

            <!-- Header Quick Action Buttons -->
            <div class="flex flex-col sm:flex-row lg:flex-col shrink-0 gap-2.5">
                <a 
                    href="#rank-journey" 
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold transition-all shadow-md active:scale-95 text-center"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                    <span data-en="Explore Rank Journey" data-bn="র‍্যাংক রোডম্যাপ দেখুন">Explore Rank Journey</span>
                </a>

                <a 
                    href="#commission-calculator" 
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold border border-slate-700 transition-all text-center"
                >
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span data-en="Calculate Commissions" data-bn="কমিশন হিসাব করুন">Calculate Commissions</span>
                </a>

                <button 
                    type="button"
                    @click="copyText(getMarketingPlanSummaryText(), 'SBL Marketing Plan summary copied!')"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700/80 transition-all text-center"
                >
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                    <span data-en="Copy Marketing Summary" data-bn="মার্কেটিং সামারি কপি">Copy Marketing Summary</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 02. ENTRY / MEMBERSHIP OVERVIEW (৳10,000 PACKAGE) -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5 mb-5">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-orange-100 text-orange-800 text-[11px] font-bold rounded-md uppercase tracking-wider" data-en="Foundation Entry" data-bn="প্রাথমিক মেম্বারশিপ">Foundation Entry</span>
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 text-[11px] font-semibold rounded-md" data-en="Starter Membership" data-bn="স্টার্টার প্যাকেজ">Starter Membership</span>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900" data-en="Ready E-Commerce Project & Membership Package" data-bn="রেডি ই-কমার্স প্রজেক্ট ও মেম্বারশিপ প্যাকেজ">
                    Ready E-Commerce Project & Membership Package
                </h2>
                <p class="text-xs text-slate-500 leading-relaxed max-w-2xl" data-en="The standard entry package to activate your binary positioning, qualify for personal direct affiliate bonuses, and begin your journey toward SBL career ranks." data-bn="বাইনারি পজিশন সক্রিয় করতে, ব্যক্তিগত ডিরেক্ট রেফারেল কমিশন পেতে এবং SBL ক্যারিয়ার র‍্যাংক অর্জনের যাত্রা শুরু করতে প্রাথমিক স্ট্যান্ডার্ড প্যাকেজ।">
                    The standard entry package to activate your binary positioning, qualify for personal direct affiliate bonuses, and begin your journey toward SBL career ranks.
                </p>
            </div>

            <div class="flex items-center sm:justify-end gap-3 shrink-0">
                <div class="text-left sm:text-right">
                    <span class="text-2xl sm:text-3xl font-extrabold text-orange-600 block leading-tight">৳10,000 <span class="text-xs text-slate-500 font-normal">BDT</span></span>
                    <span class="text-[11px] text-slate-400 font-medium block" data-en="100 Weeks Project Plan Basis" data-bn="১০০ সপ্তাহ প্রকল্পভিত্তিক">100 Weeks Project Plan Basis</span>
                </div>

                <div class="flex items-center gap-2 pl-2 border-l border-slate-100">
                    <a 
                        href="/packages?package=starter" 
                        class="px-3 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors"
                        data-en="View Package"
                        data-bn="প্যাকেজ দেখুন"
                    >
                        View Package
                    </a>
                    <button 
                        type="button"
                        @click="openLeadModal('Starter', 'Membership')"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-orange-600 hover:bg-orange-500 transition-all shadow-xs"
                        data-en="Interested"
                        data-bn="আগ্রহী"
                    >
                        Interested
                    </button>
                </div>
            </div>
        </div>

        <!-- 4 Verified Benefit Chips -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-xs">✓</span>
                <div>
                    <strong class="text-slate-800 block" data-en="Free Facebook Page Setup" data-bn="ফ্রি ফেসবুক পেজ সেটআপ">Free Facebook Page Setup</strong>
                    <span class="text-[11px] text-slate-500" data-en="Ready e-commerce presence branding" data-bn="ব্যবসায়িক পেজ ও ব্র্যান্ডিং সুবিধা">Ready e-commerce presence branding</span>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-xs">✓</span>
                <div>
                    <strong class="text-slate-800 block" data-en="Free Affiliate Account Setup" data-bn="ফ্রি অ্যাফিলিয়েট অ্যাকাউন্ট সেটআপ">Free Affiliate Account Setup</strong>
                    <span class="text-[11px] text-slate-500" data-en="Integrated referral tracking dashboard" data-bn="অটোমেটেড রেফারেল ড্যাশবোর্ড সুবিধা">Integrated referral tracking dashboard</span>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-xs">✓</span>
                <div>
                    <strong class="text-slate-800 block" data-en="Unlimited Direct Sponsor" data-bn="আনলিমিটেড ডিরেক্ট স্পনসর">Unlimited Direct Sponsor</strong>
                    <span class="text-[11px] text-slate-500" data-en="Earn 10% spot on direct referrals" data-bn="প্রতিটি সরাসরি রেফারেল থেকে ১০% স্পট">Earn 10% spot on direct referrals</span>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-xs">✓</span>
                <div>
                    <strong class="text-slate-800 block" data-en="Free Content Marketing Support" data-bn="ফ্রি কনটেন্ট মার্কেটিং সহায়তা">Free Content Marketing Support</strong>
                    <span class="text-[11px] text-slate-500" data-en="Promotional materials and campaign assets" data-bn="প্রমোশনাল ব্যানার ও সেলস কনটেন্ট">Promotional materials and campaign assets</span>
                </div>
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400">
            <span data-en="* SBL Starter project is performance and product based. Weekly distributions reflect active commercial dropshipping cycles." data-bn="* SBL স্টার্টার প্যাকেজ পণ্য ও ড্রপশিপিং পারফরম্যান্সের ওপর পরিচালিত। কোনো নিশ্চিত ফিক্সড মুনাফা বা সুদের প্রতিশ্রুতি প্রদান করা হয় না।">
                * SBL Starter project is performance and product based. Weekly distributions reflect active commercial dropshipping cycles.
            </span>
            <span class="font-medium text-slate-500" data-en="Direct Spot Bonus: 10% Instant" data-bn="ডিরেক্ট স্পট বোনাস: ১০% তাৎক্ষণিক">Direct Spot Bonus: 10% Instant</span>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 03. SBL MARKETING PLAN – 5 EARNING STREAMS -->
    <!-- ========================================================================= -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 text-[11px] font-bold rounded-md uppercase tracking-wider" data-en="Income Streams" data-bn="আয়ের ৫টি খাত">Income Streams</span>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1" data-en="SBL Marketing Plan (5 Earning Streams)" data-bn="SBL মার্কেটিং প্ল্যান (৫টি উপার্জনের ধারা)">
                    SBL Marketing Plan (5 Earning Streams)
                </h2>
                <p class="text-xs text-slate-500" data-en="A multi-layered compensation model combining direct referral bonuses, dual-team pairing rewards, multi-tier unity overrides, and career rank prizes." data-bn="একটি বহুমুখী কমিশন কাঠামো যেখানে রয়েছে সরাসরি রেফারেল বোনাস, বাইনারি পেয়ার ম্যাচিং, ১০ প্রজন্মব্যাপী ইউনিটি কমিশন এবং নিশ্চিত র‍্যাংক প্রাইজমানি।">
                    A multi-layered compensation model combining direct referral bonuses, dual-team pairing rewards, multi-tier unity overrides, and career rank prizes.
                </p>
            </div>
            <span class="text-xs font-semibold text-orange-600 bg-orange-50 px-3 py-1 rounded-lg border border-orange-100 self-start sm:self-auto" data-en="Verified Official Structure" data-bn="যাচাইকৃত অফিসিয়াল কাঠামো">
                Verified Official Structure
            </span>
        </div>

        <!-- 5 Streams Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="(stream, key) in streams" :key="key">
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:border-orange-300 hover:shadow-md transition-all flex flex-col justify-between group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-2xl p-2 rounded-xl bg-slate-50 border border-slate-100 group-hover:bg-orange-50 transition-colors" x-text="stream.icon"></span>
                            <span class="px-2.5 py-1 rounded-lg bg-orange-100 text-orange-800 text-[11px] font-bold" x-text="stream.rate_description"></span>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-slate-900" x-text="($store.lang && $store.lang.current === 'bn') ? stream.name_bn : stream.name"></h3>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed" x-text="($store.lang && $store.lang.current === 'bn') ? stream.description_bn : stream.description"></p>
                        </div>

                        <!-- Eligibility chip -->
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-[11px] text-slate-600 space-y-1">
                            <span class="font-bold text-slate-800 block text-[10px] uppercase tracking-wider" data-en="Eligibility:" data-bn="যোগ্যতার মাপকাঠি:">Eligibility:</span>
                            <span x-text="($store.lang && $store.lang.current === 'bn') ? stream.eligibility_bn : stream.eligibility"></span>
                        </div>
                    </div>

                    <!-- Example and CTA -->
                    <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500">
                        <span class="text-slate-700 font-medium block" x-text="($store.lang && $store.lang.current === 'bn') ? stream.example_bn : stream.example"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 04. RANK JOURNEY (VISUAL PROGRESSION TIMELINE) -->
    <!-- ========================================================================= -->
    <div id="rank-journey" class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-7 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 text-[11px] font-bold rounded-md uppercase tracking-wider" data-en="Career Roadmap" data-bn="ক্যারিয়ার রোডম্যাপ">Career Roadmap</span>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1" data-en="The SBL Leadership Progression Journey" data-bn="SBL লিডারশিপ পদোন্নতির ধারাবাহিক রোডম্যাপ">
                    The SBL Leadership Progression Journey
                </h2>
                <p class="text-xs text-slate-500" data-en="Track your pathway from direct referral foundation to Executive Team Director. Click any rank to inspect requirements." data-bn="সরাসরি রেফারেলের ভিত্তি থেকে শুরু করে এক্সিকিউটিভ ডিরেক্টর পদ পর্যন্ত অগ্রগতি পর্যবেক্ষণ করুন। নির্দিষ্ট র‍্যাংক ক্লিক করে বিস্তারিত দেখুন।">
                    Track your pathway from direct referral foundation to Executive Team Director. Click any rank to inspect requirements.
                </p>
            </div>
            <div class="text-xs font-semibold text-slate-500">
                <span data-en="Cumulative Milestone Rewards:" data-bn="মোট সম্ভাব্য প্রাইজমানি:">Cumulative Milestone Rewards:</span>
                <span class="font-extrabold text-purple-700 ml-1">৳36,55,000+ BDT</span>
            </div>
        </div>

        <!-- DESKTOP TIMELINE (Horizontal) -->
        <div class="hidden lg:block relative pt-4 pb-2">
            <!-- Connecting line background -->
            <div class="absolute top-12 left-10 right-10 h-1.5 bg-slate-100 rounded-full z-0"></div>
            
            <div class="grid grid-cols-6 gap-3 relative z-10">
                <template x-for="(rank, key) in ranks" :key="key">
                    <button 
                        type="button"
                        @click="selectRank(key)"
                        class="flex flex-col items-center text-center p-3 rounded-xl transition-all cursor-pointer group"
                        :class="activeRankKey === key ? 'bg-orange-50/80 ring-2 ring-orange-500 shadow-sm' : 'hover:bg-slate-50'"
                    >
                        <!-- Node circle -->
                        <div 
                            class="w-12 h-12 rounded-full flex items-center justify-center font-extrabold text-sm mb-3 shadow-sm transition-transform group-hover:scale-105"
                            :class="activeRankKey === key ? 'bg-orange-600 text-white ring-4 ring-orange-100' : 'bg-slate-900 text-white'"
                        >
                            <span x-text="rank.code"></span>
                        </div>

                        <!-- Rank Info -->
                        <span class="text-xs font-bold text-slate-900 block" x-text="rank.shortName"></span>
                        <span class="text-[11px] font-extrabold text-orange-600 block mt-0.5" x-text="rank.cashReward_formatted"></span>
                        <span class="text-[10px] text-slate-500 mt-1 line-clamp-2" x-text="($store.lang && $store.lang.current === 'bn') ? rank.requirement_bn : rank.requirement"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- MOBILE / TABLET TIMELINE (Vertical Milestone Nodes) -->
        <div class="lg:hidden space-y-3">
            <template x-for="(rank, key, idx) in ranks" :key="key">
                <div 
                    @click="selectRank(key)"
                    class="p-4 rounded-xl border transition-all cursor-pointer flex items-center justify-between gap-3"
                    :class="activeRankKey === key ? 'bg-orange-50/60 border-orange-400 ring-1 ring-orange-400' : 'bg-slate-50/70 border-slate-200/80 hover:bg-slate-100/70'"
                >
                    <div class="flex items-center gap-3">
                        <div 
                            class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-xs text-white shrink-0"
                            :class="activeRankKey === key ? 'bg-orange-600' : 'bg-slate-900'"
                            x-text="rank.code"
                        ></div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-xs font-bold text-slate-900" x-text="($store.lang && $store.lang.current === 'bn') ? rank.name_bn : rank.name"></h4>
                                <span class="text-[10px] px-1.5 py-0.5 bg-white text-slate-600 rounded border border-slate-200" x-text="rank.badge"></span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5" x-text="($store.lang && $store.lang.current === 'bn') ? rank.requirement_bn : rank.requirement"></p>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="text-xs font-black text-orange-600 block" x-text="rank.cashReward_formatted"></span>
                        <span class="text-[10px] text-slate-400" data-en="Reward" data-bn="পুরস্কার">Reward</span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 05. RANK REWARDS & DETAILED CARDS -->
    <!-- ========================================================================= -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-md uppercase tracking-wider" data-en="Official Rank Cards" data-bn="র‍্যাংক কার্ড ও বিস্তারিত">Official Rank Cards</span>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1" data-en="SBL Rank Cards & Cash Incentives" data-bn="SBL র‍্যাংক কার্ড ও নগদ প্রণোদনা">
                    SBL Rank Cards & Cash Incentives
                </h2>
                <p class="text-xs text-slate-500" data-en="Clear performance criteria, required leadership balance, and confirmed milestone prizes for every rank." data-bn="প্রতিটি পদমর্যাদার স্পষ্ট যোগ্যতা, প্রয়োজনীয় টিম ব্যালেন্স এবং অনুমোদিত নগদ পুরস্কারের তালিকা।">
                    Clear performance criteria, required leadership balance, and confirmed milestone prizes for every rank.
                </p>
            </div>
            
            <!-- Filter / Toggle buttons -->
            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl self-start sm:self-auto text-xs font-semibold">
                <button 
                    type="button" 
                    @click="activeTab = 'cards'"
                    :class="activeTab === 'cards' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition-all"
                    data-en="Cards View"
                    data-bn="কার্ড ভিউ"
                >Cards View</button>
                <button 
                    type="button" 
                    @click="activeTab = 'table'"
                    :class="activeTab === 'table' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition-all"
                    data-en="Table View"
                    data-bn="টেবিল ভিউ"
                >Table View</button>
            </div>
        </div>

        <!-- CARDS VIEW (Responsive 1/2/3 Grid) -->
        <div x-show="activeTab === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <template x-for="(rank, key) in ranks" :key="key">
                <div 
                    :id="'rank-' + key"
                    class="bg-white rounded-2xl border transition-all flex flex-col justify-between overflow-hidden shadow-xs hover:shadow-md"
                    :class="activeRankKey === key ? 'border-orange-400 ring-2 ring-orange-400/30' : 'border-slate-200/90'"
                >
                    <!-- Card Top Banner -->
                    <div class="p-5 border-b border-slate-100 space-y-3 bg-gradient-to-b from-slate-50/50 to-white">
                        <div class="flex items-center justify-between gap-2">
                            <span 
                                class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-black text-xs tracking-wider"
                                x-text="rank.code"
                            ></span>
                            <span 
                                class="text-xs font-semibold px-2.5 py-0.5 rounded-md border"
                                :class="activeRankKey === key ? 'bg-orange-50 text-orange-700 border-orange-200' : 'bg-slate-50 text-slate-600 border-slate-200'"
                                x-text="rank.badge"
                            ></span>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-slate-900" x-text="($store.lang && $store.lang.current === 'bn') ? rank.name_bn : rank.name"></h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed" x-text="($store.lang && $store.lang.current === 'bn') ? rank.description_bn : rank.description"></p>
                        </div>
                    </div>

                    <!-- Card Body / Milestone Reward & Requirement -->
                    <div class="p-5 space-y-4">
                        <div class="p-3.5 rounded-xl bg-orange-50/70 border border-orange-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-orange-700 uppercase tracking-wider block" data-en="Milestone Cash Reward" data-bn="এককালীন নগদ প্রাইজমানি">Milestone Cash Reward</span>
                                <span class="text-xl font-black text-orange-600" x-text="rank.cashReward_formatted"></span>
                            </div>
                            <span class="text-2xl">💰</span>
                        </div>

                        <!-- Requirement Checklist -->
                        <div class="space-y-2">
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block" data-en="Qualification Criteria:" data-bn="যোগ্যতার শর্তাবলী:">Qualification Criteria:</span>
                            <ul class="space-y-1.5 text-xs text-slate-600">
                                <template x-for="(crit, cIdx) in rank.criteria_list" :key="cIdx">
                                    <li class="flex items-start gap-2">
                                        <span class="text-emerald-500 font-bold shrink-0">✓</span>
                                        <span x-text="($store.lang && $store.lang.current === 'bn') ? crit.bn : crit.en"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <!-- Card Footer Actions -->
                    <div class="p-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between gap-2">
                        <button 
                            type="button"
                            @click="shareRank(key)"
                            class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-200 transition-colors flex items-center gap-1.5"
                            title="Share this rank"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            <span data-en="Share" data-bn="শেয়ার">Share</span>
                        </button>

                        <button 
                            type="button"
                            @click="openLeadModal(rank.code, 'Rank Qualification')"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-orange-600 transition-colors shadow-xs"
                            data-en="Target This Rank"
                            data-bn="লক্ষ্য নির্ধারণ করুন"
                        >
                            Target This Rank
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- ========================================================================= -->
        <!-- 06. DETAILED RANK TABLE (DESKTOP) & EXPANDABLE CARDS (MOBILE) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'table'" class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-900" data-en="Complete Rank & Criteria Matrix" data-bn="সম্পূর্ণ র‍্যাংক ও যোগ্যতার ম্যাট্রিক্স">Complete Rank & Criteria Matrix</h3>
                    <p class="text-xs text-slate-500" data-en="Comparative view of required team leaders, binary pairs, and one-time cash rewards." data-bn="প্রয়োজনীয় টিম লিডার, পেয়ার ও এককালীন ক্যাশ রিওয়ার্ডের তুলনামূলক তালিকা।">Comparative view of required team leaders, binary pairs, and one-time cash rewards.</p>
                </div>
                <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded-lg" data-en="Up to ৳40,00,000 Total" data-bn="মোট ৪০ লাখ টাকা পর্যন্ত">Up to ৳40,00,000 Total</span>
            </div>

            <!-- DESKTOP TABLE -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-5" data-en="Rank Code" data-bn="কোড">Rank Code</th>
                            <th class="py-3 px-5" data-en="Designation" data-bn="পদমর্যাদা">Designation</th>
                            <th class="py-3 px-5" data-en="Eligibility Requirement" data-bn="যোগ্যতার মাপকাঠি">Eligibility Requirement</th>
                            <th class="py-3 px-5" data-en="Dual Team Structure" data-bn="উভয় টিমের ব্যালেন্স">Dual Team Structure</th>
                            <th class="py-3 px-5 text-right" data-en="Cash Reward" data-bn="নগদ প্রাইজমানি">Cash Reward</th>
                            <th class="py-3 px-5 text-center" data-en="Action" data-bn="অ্যাকশন">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(rank, key) in ranks" :key="key">
                            <tr class="hover:bg-slate-50/80 transition-colors" :class="activeRankKey === key ? 'bg-orange-50/30 font-semibold' : ''">
                                <td class="py-3.5 px-5 font-black text-slate-900">
                                    <span class="px-2.5 py-1 bg-slate-900 text-white rounded-lg text-xs" x-text="rank.code"></span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="font-bold text-slate-900 block" x-text="($store.lang && $store.lang.current === 'bn') ? rank.name_bn : rank.name"></span>
                                    <span class="text-[11px] text-slate-500" x-text="rank.badge"></span>
                                </td>
                                <td class="py-3.5 px-5 font-medium text-slate-700" x-text="($store.lang && $store.lang.current === 'bn') ? rank.requirement_bn : rank.requirement"></td>
                                <td class="py-3.5 px-5 text-slate-600">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-100 text-[11px] font-medium" x-text="($store.lang && $store.lang.current === 'bn') ? rank.teamRequirement_bn : rank.teamRequirement"></span>
                                </td>
                                <td class="py-3.5 px-5 text-right font-extrabold text-orange-600 text-sm" x-text="rank.cashReward_formatted"></td>
                                <td class="py-3.5 px-5 text-center">
                                    <button 
                                        type="button" 
                                        @click="shareRank(key)"
                                        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors"
                                        title="Share Rank"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- MOBILE EXPANDABLE TABLE VIEW (Zero Overflow) -->
            <div class="md:hidden divide-y divide-slate-100">
                <template x-for="(rank, key) in ranks" :key="key">
                    <div class="p-4 space-y-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-bold text-xs" x-text="rank.code"></span>
                                <h4 class="text-xs font-bold text-slate-900" x-text="($store.lang && $store.lang.current === 'bn') ? rank.name_bn : rank.name"></h4>
                            </div>
                            <span class="text-xs font-black text-orange-600" x-text="rank.cashReward_formatted"></span>
                        </div>

                        <div class="p-2.5 rounded-lg bg-slate-50 text-[11px] text-slate-600 space-y-1">
                            <div><strong class="text-slate-800" data-en="Requirement:" data-bn="শর্ত:">Requirement:</strong> <span x-text="($store.lang && $store.lang.current === 'bn') ? rank.requirement_bn : rank.requirement"></span></div>
                            <div><strong class="text-slate-800" data-en="Team Target:" data-bn="টিম কোটা:">Team Target:</strong> <span x-text="($store.lang && $store.lang.current === 'bn') ? rank.teamRequirement_bn : rank.teamRequirement"></span></div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button 
                                type="button" 
                                @click="shareRank(key)" 
                                class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 text-xs font-semibold"
                                data-en="Share"
                                data-bn="শেয়ার"
                            >Share</button>
                            <button 
                                type="button" 
                                @click="openLeadModal(rank.code, 'Rank Matrix')" 
                                class="px-3 py-1.5 rounded-lg bg-orange-600 text-white text-xs font-bold"
                                data-en="Target"
                                data-bn="টার্গেট"
                            >Target</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 07 & 08. 10-GENERATION COMMISSION STRUCTURE -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden space-y-0">
        <div class="px-5 sm:px-6 py-5 border-b border-slate-100 space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 text-[11px] font-bold rounded-md uppercase tracking-wider" data-en="Affiliate Tier" data-bn="অ্যাফিলিয়েট টিয়ার">Affiliate Tier</span>
                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[11px] font-semibold rounded-md" data-en="10 Generations" data-bn="১০ম প্রজন্ম পর্যন্ত">10 Generations</span>
            </div>
            <h2 class="text-lg sm:text-xl font-bold text-slate-900" data-en="10-Generation Commission Structure" data-bn="১০-প্রজন্ম কমিশন কাঠামো">
                10-Generation Commission Structure
            </h2>
            <p class="text-xs text-slate-500 leading-relaxed" data-en="Commission rates are based on the current configured SBL plan. Example network sizes are illustrative calculations based on a hypothetical 10×10 referral model." data-bn="কমিশনের হার SBL-এর বর্তমান কনফিগারেশন অনুযায়ী নির্ধারিত। উদাহরণ হিসেবে দেওয়া সংখ্যাগুলো কাল্পনিক ১০×১০ মডেলের ওপর একটি গাণিতিক হিসাব মাত্র।">
                Commission rates are based on the current configured SBL plan. Example network sizes are illustrative calculations based on a hypothetical 10×10 referral model.
            </p>
        </div>

        <!-- Official Rates Notice -->
        <div class="p-4 bg-slate-50/70 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span data-en="Configured Rates: Gen 1 (10%), Gen 2 (2%), Gen 3-4 (1%), Gen 5 (0.5%), Gen 6-10 (0.1%)" data-bn="নির্ধারিত হার: ১ম প্রজন্ম (১০%), ২য় (২%), ৩য়-৪র্থ (১%), ৫ম (০.৫%), ৬ষ্ঠ-১০ম (০.১%)">
                    Configured Rates: Gen 1 (10%), Gen 2 (2%), Gen 3-4 (1%), Gen 5 (0.5%), Gen 6-10 (0.1%)
                </span>
            </div>
            <span class="text-[11px] font-semibold text-slate-500" data-en="* Actual commissions depend on verified team sales" data-bn="* কমিশন সক্রিয় টিম সেলস সাপেক্ষে অর্জিত হয়">* Actual commissions depend on verified team sales</span>
        </div>

        <!-- DESKTOP / TABLET GENERATION TABLE -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/90 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-5" data-en="Generation" data-bn="প্রজন্ম">Generation</th>
                        <th class="py-3 px-5" data-en="Official Rate" data-bn="নির্ধারিত কমিশন">Official Rate</th>
                        <th class="py-3 px-5" data-en="10×10 Members (Example)" data-bn="১০×১০ মেম্বার (মডেল)">10×10 Members (Example)</th>
                        <th class="py-3 px-5" data-en="Volume (Tk 10,000)" data-bn="মোট ভলিউম (১০ হাজার)">Volume (Tk 10,000)</th>
                        <th class="py-3 px-5 text-right" data-en="Illustrative Return" data-bn="প্রক্ষেপিত রিটার্ন">Illustrative Return</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <!-- Standard Generations (1 to 6) -->
                    <template x-for="row in matrix.filter(m => !m.is_advanced)" :key="row.generation">
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-5 font-bold text-slate-900">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800" x-text="($store.lang && $store.lang.current === 'bn') ? row.gen_bn : row.gen"></span>
                            </td>
                            <td class="py-3 px-5 font-black text-orange-600 text-sm" x-text="row.rate"></td>
                            <td class="py-3 px-5 text-slate-700 font-semibold" x-text="formatNumber(row.people_num)"></td>
                            <td class="py-3 px-5 text-slate-500" x-text="row.volume_formatted"></td>
                            <td class="py-3 px-5 text-right font-extrabold text-emerald-700 text-sm" x-text="row.commission_formatted"></td>
                        </tr>
                    </template>

                    <!-- Advanced Generations (7 to 10) - Collapsible -->
                    <template x-if="showAdvancedGen">
                        <template x-for="row in matrix.filter(m => m.is_advanced)" :key="row.generation">
                            <tr class="bg-amber-50/20 hover:bg-amber-50/40 transition-colors">
                                <td class="py-3 px-5 font-bold text-slate-900">
                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-900" x-text="($store.lang && $store.lang.current === 'bn') ? row.gen_bn : row.gen"></span>
                                    <span class="text-[10px] text-amber-700 ml-1">(Theoretical)</span>
                                </td>
                                <td class="py-3 px-5 font-black text-orange-600 text-sm" x-text="row.rate"></td>
                                <td class="py-3 px-5 text-slate-700 font-semibold" x-text="formatNumber(row.people_num)"></td>
                                <td class="py-3 px-5 text-slate-500" x-text="row.volume_formatted"></td>
                                <td class="py-3 px-5 text-right font-extrabold text-emerald-700 text-sm" x-text="row.commission_formatted"></td>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- MOBILE GENERATION VIEW (Card view with 3 default items) -->
        <div class="sm:hidden divide-y divide-slate-100">
            <template x-for="(row, idx) in (showAllGenerations ? matrix : matrix.slice(0, 3))" :key="row.generation">
                <div class="p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded bg-slate-900 text-white text-xs font-bold" x-text="($store.lang && $store.lang.current === 'bn') ? row.gen_bn : row.gen"></span>
                        <span class="text-sm font-black text-orange-600" x-text="row.rate + ' Commission'"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-[11px] p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <div>
                            <span class="text-slate-400 block" data-en="Illustrative Members:" data-bn="নমুনা সদস্য:">Illustrative Members:</span>
                            <strong class="text-slate-800" x-text="formatNumber(row.people_num)"></strong>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-400 block" data-en="Est. Return:" data-bn="প্রক্ষেপিত রিটার্ন:">Est. Return:</span>
                            <strong class="text-emerald-700" x-text="row.commission_formatted"></strong>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Collapsible Controls -->
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <!-- Mobile Toggle -->
            <div class="sm:hidden w-full">
                <button 
                    type="button"
                    @click="showAllGenerations = !showAllGenerations"
                    class="w-full py-2.5 px-4 rounded-xl border border-slate-200 bg-white font-bold text-slate-800 text-center"
                >
                    <span x-text="showAllGenerations ? 'Show Fewer Generations' : 'View All 10 Generations'"></span>
                </button>
            </div>

            <!-- Desktop Toggle -->
            <div class="hidden sm:flex items-center gap-3">
                <button 
                    type="button"
                    @click="showAdvancedGen = !showAdvancedGen"
                    class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 font-bold text-slate-800 transition-colors flex items-center gap-2"
                >
                    <span x-text="showAdvancedGen ? 'Hide Advanced Projections (Generations 7–10)' : 'Show Advanced Projections (Generations 7–10)'"></span>
                    <svg class="w-4 h-4 transition-transform" :class="showAdvancedGen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <span class="text-[11px] text-slate-400" data-en="Generations 7 to 10 are mathematical 10×10 expansion models." data-bn="৭ম থেকে ১০ম প্রজন্ম তাত্ত্বিক ১০×১০ গাণিতিক প্রক্ষেপণ।">
                    Generations 7 to 10 are mathematical 10×10 expansion models.
                </span>
            </div>

            <div class="text-[11px] text-slate-500">
                <span data-en="Total Configured Tiers: 10 Levels" data-bn="মোট কনফিগার করা লেভেল: ১০টি">Total Configured Tiers: 10 Levels</span>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 09. INTERACTIVE COMMISSION CALCULATOR -->
    <!-- ========================================================================= -->
    <div id="commission-calculator" class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-7 shadow-xs space-y-6">
        <div>
            <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-md uppercase tracking-wider" data-en="Income Simulator" data-bn="কমিশন ক্যালকুলেটর">Income Simulator</span>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1" data-en="Generation Commission Calculator" data-bn="প্রজন্মভিত্তিক কমিশন ক্যালকুলেটর">
                Generation Commission Calculator
            </h2>
            <p class="text-xs text-slate-500" data-en="Select any generation level, enter your active team member count, and simulate transparent estimated earnings based on official SBL percentage rates." data-bn="নির্দিষ্ট প্রজন্ম নির্বাচন করুন, আপনার সক্রিয় টিমের সদস্য সংখ্যা দিন এবং অফিসিয়াল কমিশনের হার অনুযায়ী প্রক্ষেপিত আয় হিসাব করুন।">
                Select any generation level, enter your active team member count, and simulate transparent estimated earnings based on official SBL percentage rates.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Inputs (7 cols) -->
            <div class="lg:col-span-7 space-y-4">
                <!-- Generation Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" data-en="Select Generation Level:" data-bn="প্রজন্ম নির্বাচন করুন:">Select Generation Level:</label>
                    <div class="grid grid-cols-5 sm:grid-cols-10 gap-1.5">
                        <template x-for="g in 10" :key="g">
                            <button 
                                type="button"
                                @click="calcGen = g"
                                class="py-2 rounded-lg text-xs font-extrabold border transition-all text-center"
                                :class="calcGen === g ? 'bg-orange-600 text-white border-orange-600 ring-2 ring-orange-200' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                x-text="'G' + g"
                            ></button>
                        </template>
                    </div>
                </div>

                <!-- Number of Members & Package Amount -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Active Qualified Members in This Gen:" data-bn="এই প্রজন্মে সক্রিয় সদস্য সংখ্যা:">Active Qualified Members in This Gen:</label>
                        <input 
                            type="number" 
                            x-model.number="calcMembers" 
                            min="1" 
                            max="10000000"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                            placeholder="e.g. 10"
                        >
                        <span class="text-[10px] text-slate-400 mt-1 block" data-en="Number of direct or indirect associates" data-bn="সরাসরি বা পরোক্ষ সহযোগী সংখ্যা">Number of direct or indirect associates</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Average Eligible Volume / Package (BDT):" data-bn="গড় প্যাকেজ বা ভলিউম (টাকা):">Average Eligible Volume / Package (BDT):</label>
                        <select 
                            x-model.number="calcAmount"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-orange-500 focus:outline-none bg-white"
                        >
                            <option value="10000">৳10,000 (Starter Membership)</option>
                            <option value="120000">৳1,20,000 (National Dropshipping)</option>
                            <option value="600000">৳6,00,000 (International Dropshipping)</option>
                        </select>
                        <span class="text-[10px] text-slate-400 mt-1 block" data-en="Base package value for commission rate" data-bn="কমিশন হিসাবের ভিত্তি প্যাকেজ মূল্য">Base package value for commission rate</span>
                    </div>
                </div>

                <!-- Formula transparency pill -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs text-slate-600 space-y-1">
                    <strong class="text-slate-800 block text-[11px] uppercase tracking-wider" data-en="Transparent Calculation Formula:" data-bn="স্বচ্ছ গাণিতিক ফর্মুলা:">Transparent Calculation Formula:</strong>
                    <div class="font-mono text-[11px] text-slate-700 flex flex-wrap items-center gap-1.5">
                        <span class="px-1.5 py-0.5 rounded bg-white border border-slate-200" x-text="formatNumber(calcMembers) + ' Members'"></span>
                        <span>×</span>
                        <span class="px-1.5 py-0.5 rounded bg-white border border-slate-200" x-text="formatMoney(calcAmount)"></span>
                        <span>×</span>
                        <span class="px-1.5 py-0.5 rounded bg-orange-100 text-orange-800 font-bold" x-text="(currentGenRate * 100).toFixed(1) + '% Rate'"></span>
                        <span>=</span>
                        <span class="font-bold text-emerald-700 font-mono" x-text="formatMoney(calculatedCommission)"></span>
                    </div>
                </div>
            </div>

            <!-- Output Display Card (5 cols) -->
            <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-6 shadow-md border border-slate-800 flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-lg bg-orange-500/20 text-orange-300 font-bold text-xs" x-text="'Generation ' + calcGen"></span>
                        <span class="text-xs text-slate-400 font-semibold" x-text="'Rate: ' + (currentGenRate * 100).toFixed(1) + '%'"></span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 block uppercase tracking-wider" data-en="Estimated Generation Commission" data-bn="প্রক্ষেপিত মোট কমিশন">Estimated Generation Commission</span>
                        <div class="text-3xl sm:text-4xl font-black text-emerald-400 mt-1" x-text="formatMoney(calculatedCommission)"></div>
                        <span class="text-[11px] text-slate-400 mt-1 block" data-en="* Illustrative calculation only. Performance-dependent." data-bn="* কাল্পনিক নমুনা হিসাব। আয় সম্পূর্ণ পারফরম্যান্স নির্ভর।">* Illustrative calculation only. Performance-dependent.</span>
                    </div>

                    <div class="pt-3 border-t border-slate-700/80 space-y-1.5 text-xs text-slate-300">
                        <div class="flex justify-between">
                            <span data-en="Total Volume:" data-bn="মোট সেলস ভলিউম:">Total Volume:</span>
                            <span class="font-semibold text-white" x-text="formatMoney(calcMembers * calcAmount)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span data-en="Applied Rate:" data-bn="প্রযোজ্য কমিশন হার:">Applied Rate:</span>
                            <span class="font-semibold text-orange-300" x-text="(currentGenRate * 100).toFixed(1) + '%'"></span>
                        </div>
                    </div>
                </div>

                <button 
                    type="button"
                    @click="openLeadModal('Generation ' + calcGen, 'Commission Simulator')"
                    class="w-full py-2.5 px-4 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold transition-all shadow-md text-center"
                    data-en="Discuss Affiliate Earnings with Advisor"
                    data-bn="অ্যাডভাইজারের সাথে আলোচনা করুন"
                >
                    Discuss Affiliate Earnings with Advisor
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 10. RANK PROGRESS CALCULATOR ("WHICH RANK CAN I TARGET?") -->
    <!-- ========================================================================= -->
    <div id="target-calculator" class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-7 shadow-xs space-y-6">
        <div>
            <span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 text-[11px] font-bold rounded-md uppercase tracking-wider" data-en="Rank Evaluator" data-bn="র‍্যাংক প্রগ্রেস ক্যালকুলেটর">Rank Evaluator</span>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1" data-en="Which Rank Can I Target?" data-bn="আমি কোন র‍্যাংক টার্গেট করতে পারি?">
                Which Rank Can I Target?
            </h2>
            <p class="text-xs text-slate-500" data-en="Enter your current or projected direct sponsors, dual team count, and binary matching pairs to see your current eligible rank and missing steps to reach the next level." data-bn="আপনার বর্তমান বা পরিকল্পিত ডিরেক্ট স্পনসর সংখ্যা ও বাইনারি পেয়ার ম্যাচিংয়ের সংখ্যা দিন। সিস্টেম সাথে সাথে আপনার বর্তমান র‍্যাংক এবং পরবর্তী ধাপে পৌঁছানোর প্রয়োজনীয় ঘাটতি দেখাবে।">
                Enter your current or projected direct sponsors, dual team count, and binary matching pairs to see your current eligible rank and missing steps to reach the next level.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Inputs (7 cols) -->
            <div class="lg:col-span-7 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Total Direct Sponsors:" data-bn="মোট সরাসরি স্পনসর:">Total Direct Sponsors:</label>
                        <input 
                            type="number" 
                            x-model.number="progress.directs" 
                            min="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                            placeholder="e.g. 10"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Left Team Directs:" data-bn="লেফট টিম স্পনসর:">Left Team Directs:</label>
                        <input 
                            type="number" 
                            x-model.number="progress.leftDirects" 
                            min="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                            placeholder="e.g. 5"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Right Team Directs:" data-bn="রাইট টিম স্পনসর:">Right Team Directs:</label>
                        <input 
                            type="number" 
                            x-model.number="progress.rightDirects" 
                            min="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                            placeholder="e.g. 5"
                        >
                    </div>
                </div>

                <!-- Binary Pairs & Developed Leaders -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Binary Matched Pairs:" data-bn="বাইনারি পেয়ার ম্যাচিং:">Binary Matched Pairs:</label>
                        <input 
                            type="number" 
                            x-model.number="progress.pairs" 
                            min="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                            placeholder="e.g. 300"
                        >
                        <span class="text-[10px] text-slate-400 mt-1 block">300 pairs required for SME</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" data-en="SME Leaders (Left):" data-bn="SME লিডার (লেফট):">SME Leaders (Left):</label>
                        <input 
                            type="number" 
                            x-model.number="progress.smeLeft" 
                            min="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                            placeholder="e.g. 13"
                        >
                        <span class="text-[10px] text-slate-400 mt-1 block">13 required for PME</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" data-en="SME Leaders (Right):" data-bn="SME লিডার (রাইট):">SME Leaders (Right):</label>
                        <input 
                            type="number" 
                            x-model.number="progress.smeRight" 
                            min="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                            placeholder="e.g. 7"
                        >
                        <span class="text-[10px] text-slate-400 mt-1 block">7 required for PME</span>
                    </div>
                </div>
            </div>

            <!-- Result Card (5 cols) -->
            <div class="lg:col-span-5 bg-slate-50 rounded-2xl border border-slate-200 p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block" data-en="Current Qualified Status" data-bn="বর্তমান অর্জিত মর্যাদা">Current Qualified Status</span>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xl font-black text-slate-900" x-text="progress.evaluatedRank"></span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800" data-en="Verified" data-bn="যাচাইকৃত">Verified</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block" data-en="Next Milestone" data-bn="পরবর্তী মাইলফলক">Next Milestone</span>
                        <span class="text-xl font-black text-orange-600 mt-0.5 block" x-text="progress.nextTargetRank"></span>
                    </div>
                </div>

                <!-- Missing Requirements Checklist -->
                <div>
                    <span class="text-xs font-bold text-slate-800 block mb-1.5" data-en="Requirements to Reach Next Rank:" data-bn="পরবর্তী র‍্যাংক অর্জনের প্রয়োজনীয় ধাপ:">Requirements to Reach Next Rank:</span>
                    <template x-if="progress.missingRequirements.length === 0">
                        <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold flex items-center gap-2">
                            <span>🎉</span>
                            <span data-en="Congratulations! You meet all qualifications for this rank." data-bn="অভিনন্দন! আপনি এই র‍্যাংকের সকল শর্ত পূরণ করেছেন।">Congratulations! You meet all qualifications for this rank.</span>
                        </div>
                    </template>

                    <template x-if="progress.missingRequirements.length > 0">
                        <ul class="space-y-1.5 text-xs text-slate-700">
                            <template x-for="(req, rIdx) in progress.missingRequirements" :key="rIdx">
                                <li class="p-2.5 rounded-lg bg-white border border-slate-200/80 flex items-start gap-2">
                                    <span class="text-orange-500 font-bold shrink-0">➔</span>
                                    <span x-text="req"></span>
                                </li>
                            </template>
                        </ul>
                    </template>
                </div>

                <button 
                    type="button"
                    @click="openLeadModal(progress.nextTargetRank, 'Rank Career Plan')"
                    class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-orange-600 text-white text-xs font-bold transition-colors text-center"
                    data-en="Plan My Career Roadmap"
                    data-bn="ক্যারিয়ার প্ল্যান নিশ্চিত করুন"
                >
                    Plan My Career Roadmap
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 11 & 12. PROSPECT-FRIENDLY MARKETING & SHARE SYSTEM -->
    <!-- ========================================================================= -->
    <div class="bg-gradient-to-r from-orange-50 via-amber-50 to-orange-50 rounded-2xl border border-orange-200/80 p-5 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="space-y-1 text-center sm:text-left">
            <h3 class="text-base sm:text-lg font-bold text-slate-900" data-en="Share SBL Marketing Plan With Prospective Partners" data-bn="সম্ভাব্য পার্টনারদের সাথে SBL প্ল্যান শেয়ার করুন">
                Share SBL Marketing Plan With Prospective Partners
            </h3>
            <p class="text-xs text-slate-600" data-en="One-click formatted WhatsApp summaries, clean link copying, and executive rank sheets." data-bn="এক ক্লিকে সাজানো হোয়াটসঅ্যাপ সামারি, পরিষ্কার লিঙ্ক এবং এক্সিকিউটিভ র‍্যাংক শিট শেয়ার করুন।">
                One-click formatted WhatsApp summaries, clean link copying, and executive rank sheets.
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-2.5 shrink-0">
            <button 
                type="button"
                @click="copyText(getMarketingPlanSummaryText(), 'SBL Marketing Plan summary copied to clipboard!')"
                class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-800 text-xs font-bold border border-slate-200 shadow-xs transition-all flex items-center gap-2"
            >
                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                </svg>
                <span data-en="Copy Full Plan Summary" data-bn="সম্পূর্ণ সামারি কপি">Copy Full Plan Summary</span>
            </button>

            <button 
                type="button"
                @click="shareWhatsApp(getMarketingPlanSummaryText(), window.location.href)"
                class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-xs transition-all flex items-center gap-2"
            >
                <span>💬</span>
                <span data-en="Share on WhatsApp" data-bn="হোয়াটসঅ্যাপে পাঠান">Share on WhatsApp</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 13. VERIFIED FAQ ACCORDION (10-12 ITEMS) -->
    <!-- ========================================================================= -->
    <div 
        id="rank-faqs"
        class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-7 shadow-xs space-y-5"
        x-data="{
            openFaq: null,
            toggleFaq(idx) {
                this.openFaq = this.openFaq === idx ? null : idx;
            }
        }"
    >
        <div>
            <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 text-[11px] font-bold rounded-md uppercase tracking-wider" data-en="Common Questions" data-bn="সাধারণ জিজ্ঞাসা">Common Questions</span>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1" data-en="Frequently Asked Questions (Ranks & Earnings)" data-bn="সচরাচর জিজ্ঞাসিত প্রশ্নাবলী (র‍্যাংক ও আয়)">
                Frequently Asked Questions (Ranks & Earnings)
            </h2>
            <p class="text-xs text-slate-500" data-en="Verified clarifications regarding SBL rank qualification, binary pairing rules, cash rewards, and compliance." data-bn="SBL র‍্যাংক অর্জন, বাইনারি ম্যাচিং, নগদ পুরস্কার ও কমপ্লায়েন্স সংক্রান্ত যাচাইকৃত প্রশ্নোত্তর।">
                Verified clarifications regarding SBL rank qualification, binary pairing rules, cash rewards, and compliance.
            </p>
        </div>

        <div class="space-y-2.5 divide-y divide-slate-100">
            <!-- FAQ 1 -->
            <div class="pt-2.5">
                <button 
                    type="button" 
                    @click="toggleFaq(1)" 
                    class="w-full flex items-center justify-between text-left py-2 font-bold text-xs sm:text-sm text-slate-900 hover:text-orange-600 transition-colors"
                >
                    <span data-en="1. What is an SBL Career Rank and how does it work?" data-bn="১. SBL ক্যারিয়ার র‍্যাংক কী এবং এটি কীভাবে কাজ করে?">1. What is an SBL Career Rank and how does it work?</span>
                    <span class="text-slate-400 font-bold ml-2" x-text="openFaq === 1 ? '−' : '+'"></span>
                </button>
                <div x-show="openFaq === 1" x-cloak class="pt-1 pb-3 text-xs text-slate-600 leading-relaxed space-y-1">
                    <p data-en="SBL Career Ranks (FME, SME, PME, BME, GME, ETD) are leadership milestones achieved by developing balanced dual sales teams. Each rank unlocks dedicated cash incentives and recognition bonuses based on verified sales volume." data-bn="SBL ক্যারিয়ার র‍্যাংক (FME, SME, PME, BME, GME, ETD) হলো টিম লিডারশিপের মাইলফলক। উভয় টিমে ভারসাম্যপূর্ণ রেফারেল ও সেলস পারফরম্যান্সের ওপর ভিত্তি করে এই র‍্যাংক ও নির্ধারিত নগদ পুরস্কার অর্জিত হয়।"></p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="pt-2.5">
                <button 
                    type="button" 
                    @click="toggleFaq(2)" 
                    class="w-full flex items-center justify-between text-left py-2 font-bold text-xs sm:text-sm text-slate-900 hover:text-orange-600 transition-colors"
                >
                    <span data-en="2. How do I qualify for FME (Field Marketing Executive)?" data-bn="২. কীভাবে FME (ফিল্ড মার্কেটিং এক্সিকিউটিভ) হওয়া যায়?">2. How do I qualify for FME (Field Marketing Executive)?</span>
                    <span class="text-slate-400 font-bold ml-2" x-text="openFaq === 2 ? '−' : '+'"></span>
                </button>
                <div x-show="openFaq === 2" x-cloak class="pt-1 pb-3 text-xs text-slate-600 leading-relaxed space-y-1">
                    <p data-en="To achieve FME, you must sponsor 10 direct verified associates in your binary tree with a dual balance of 5 in your Left team and 5 in your Right team. Upon qualification, a ৳5,000 BDT cash reward is credited." data-bn="FME পদমর্যাদার জন্য আপনাকে ১০ জন সরাসরি মেম্বার স্পনসর করতে হবে যার মধ্যে ৫ জন লেফট টিমে এবং ৫ জন রাইট টিমে সক্রিয় থাকতে হবে। কোয়ালিফাই করার সাথে সাথে ৫,০০০ টাকা নগদ পুরস্কার প্রযোজ্য হয়।"></p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="pt-2.5">
                <button 
                    type="button" 
                    @click="toggleFaq(3)" 
                    class="w-full flex items-center justify-between text-left py-2 font-bold text-xs sm:text-sm text-slate-900 hover:text-orange-600 transition-colors"
                >
                    <span data-en="3. What is required to reach SME (Senior Marketing Executive)?" data-bn="৩. SME হতে কী পরিমাণ বাইনারি ম্যাচিং বা পেয়ার প্রয়োজন?">3. What is required to reach SME (Senior Marketing Executive)?</span>
                    <span class="text-slate-400 font-bold ml-2" x-text="openFaq === 3 ? '−' : '+'"></span>
                </button>
                <div x-show="openFaq === 3" x-cloak class="pt-1 pb-3 text-xs text-slate-600 leading-relaxed space-y-1">
                    <p data-en="SME requires achieving 300 binary matching pair rewards across your organization. Upon reaching this milestone, you receive a ৳50,000 BDT cash incentive." data-bn="SME র‍্যাংকের জন্য নেটওয়ার্কে মোট ৩০০ পেয়ার ম্যাচিং রিওয়ার্ড সম্পন্ন করতে হয়। এই মাইলফলক স্পর্শ করলে ৫০,০০০ টাকা এককালীন নগদ প্রাইজমানি দেওয়া হয়।"></p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="pt-2.5">
                <button 
                    type="button" 
                    @click="toggleFaq(4)" 
                    class="w-full flex items-center justify-between text-left py-2 font-bold text-xs sm:text-sm text-slate-900 hover:text-orange-600 transition-colors"
                >
                    <span data-en="4. What are the requirements for PME, BME, GME, and ETD?" data-bn="৪. PME, BME, GME এবং ETD র‍্যাংকের শর্ত কী?">4. What are the requirements for PME, BME, GME, and ETD?</span>
                    <span class="text-slate-400 font-bold ml-2" x-text="openFaq === 4 ? '−' : '+'"></span>
                </button>
                <div x-show="openFaq === 4" x-cloak class="pt-1 pb-3 text-xs text-slate-600 leading-relaxed space-y-1">
                    <p data-en="Higher ranks focus on developing leaders in your dual teams: PME needs 20 SME leaders (13 Left, 7 Right - ৳1 Lac reward); BME needs 15 PME leaders (10 Left, 5 Right - ৳5 Lac reward); GME needs 12 BME leaders (8 Left, 4 Right - ৳10 Lac reward); and ETD needs 10 GME leaders (7 Left, 3 Right - ৳20 Lac reward)." data-bn="উচ্চতর র‍্যাংকের ক্ষেত্রে টিম লিডার তৈরি আবশ্যক: PME-এর জন্য ২০ জন SME (লেফট ১৩/রাইট ৭ - পুরস্কার ১ লাখ টাকা); BME-এর জন্য ১৫ জন PME (লেফট ১০/রাইট ৫ - পুরস্কার ৫ লাখ টাকা); GME-এর জন্য ১২ জন BME (লেফট ৮/রাইট ৪ - পুরস্কার ১০ লাখ টাকা); এবং ETD-এর জন্য ১০ জন GME (লেফট ৭/রাইট ৩ - পুরস্কার ২০ লাখ টাকা)।"></p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="pt-2.5">
                <button 
                    type="button" 
                    @click="toggleFaq(5)" 
                    class="w-full flex items-center justify-between text-left py-2 font-bold text-xs sm:text-sm text-slate-900 hover:text-orange-600 transition-colors"
                >
                    <span data-en="5. How does Spot Commission work?" data-bn="৫. স্পট কমিশন কীভাবে প্রদান করা হয়?">5. How does Spot Commission work?</span>
                    <span class="text-slate-400 font-bold ml-2" x-text="openFaq === 5 ? '−' : '+'"></span>
                </button>
                <div x-show="openFaq === 5" x-cloak class="pt-1 pb-3 text-xs text-slate-600 leading-relaxed space-y-1">
                    <p data-en="Spot commission pays an immediate 10% marketing commission upon the activation of any project or membership package referred directly by you. For example, referring a National Package (৳1,20,000) yields ৳12,000 instant commission." data-bn="আপনার সরাসরি রেফারেন্সে যেকোনো প্রজেক্ট বা মেম্বারশিপ অ্যাক্টিভ হলে সাথে সাথে প্যাকেজ মূল্যের ১০% ডিরেক্ট স্পট বোনাস হিসেবে জমা হয়। উদাহরণ: ১,২০,০০০ টাকার ন্যাশনাল প্যাকেজে ১২,০০০ টাকা ইনস্ট্যান্ট কমিশন।"></p>
                </div>
            </div>

            <!-- FAQ 6 -->
            <div class="pt-2.5">
                <button 
                    type="button" 
                    @click="toggleFaq(6)" 
                    class="w-full flex items-center justify-between text-left py-2 font-bold text-xs sm:text-sm text-slate-900 hover:text-orange-600 transition-colors"
                >
                    <span data-en="6. How does the Pair Matching Reward work and what is the daily limit?" data-bn="৬. পেয়ার ম্যাচিং রিওয়ার্ডের নিয়ম এবং দৈনিক সীমা কত?">6. How does the Pair Matching Reward work and what is the daily limit?</span>
                    <span class="text-slate-400 font-bold ml-2" x-text="openFaq === 6 ? '−' : '+'"></span>
                </button>
                <div x-show="openFaq === 6" x-cloak class="pt-1 pb-3 text-xs text-slate-600 leading-relaxed space-y-1">
                    <p data-en="When sales points on your Left and Right teams balance in a 1:1 ratio, you earn ৳500 per binary pair. To maintain network sustainability, the daily maximum cap is set at 100 pairs (৳50,000 BDT daily max)." data-bn="আপনার লেফট ও রাইট টিমে ১:১ অনুপাতে সেলস ভলিউম ম্যাচিং হলে প্রতি পেয়ারে ৫০০ টাকা কমিশন পাওয়া যায়। সিস্টেমের স্থায়িত্ব রক্ষার জন্য দৈনিক সর্বোচ্চ সীমা ১০০ পেয়ার বা ৫০,০০০ টাকা নির্ধারিত।"></p>
                </div>
            </div>

            <!-- FAQ 7 -->
            <div class="pt-2.5">
                <button 
                    type="button" 
                    @click="toggleFaq(7)" 
                    class="w-full flex items-center justify-between text-left py-2 font-bold text-xs sm:text-sm text-slate-900 hover:text-orange-600 transition-colors"
                >
                    <span data-en="7. What is UDR (Unity Development Commission)?" data-bn="৭. ইউনিটি ডেভেলপমেন্ট কমিশন (UDR) কী?">7. What is UDR (Unity Development Commission)?</span>
                    <span class="text-slate-400 font-bold ml-2" x-text="openFaq === 7 ? '−' : '+'"></span>
                </button>
                <div x-show="openFaq === 7" x-cloak class="pt-1 pb-3 text-xs text-slate-600 leading-relaxed space-y-1">
                    <p data-en="UDR is a multi-tier override commission distributed across up to 10 generations in your sales organization. It allows leaders to earn from the broader business activities of their downline network." data-bn="UDR হলো আপনার রেফারেল নেটওয়ার্কের ১০ম প্রজন্ম পর্যন্ত সেলস ও পারফরম্যান্সের ওপর স্তরভিত্তিক নির্ধারিত কমিশন। এটি টিম লিডারদের দীর্ঘমেয়াদি আয়ের সুযোগ সৃষ্টি করে।"></p>
                </div>
            </div>

            <!-- FAQ 8 -->
            <div class="pt-2.5">
                <button 
                    type="button" 
                    @click="toggleFaq(8)" 
                    class="w-full flex items-center justify-between text-left py-2 font-bold text-xs sm:text-sm text-slate-900 hover:text-orange-600 transition-colors"
                >
                    <span data-en="8. Are rank cash rewards guaranteed?" data-bn="৮. র‍্যাংক প্রাইজমানি কি নিশ্চিত?">8. Are rank cash rewards guaranteed?</span>
                    <span class="text-slate-400 font-bold ml-2" x-text="openFaq === 8 ? '−' : '+'"></span>
                </button>
                <div x-show="openFaq === 8" x-cloak class="pt-1 pb-3 text-xs text-slate-600 leading-relaxed space-y-1">
                    <p data-en="No rank reward or earning is a guaranteed passive return. All milestone cash incentives are strictly conditioned upon fulfilling the verified team sales quotas, direct sponsor requirements, and maintaining an active account in good standing." data-bn="কোনো র‍্যাংক রিওয়ার্ড নিশ্চিত বা ফিক্সড সুদ নয়। সকল প্রাইজমানি এবং কমিশন শর্তসাপেক্ষ—যথাযথ টিম ব্যালেন্স, নির্দিষ্ট সংখ্যক স্পনসর এবং সক্রিয় আইডি বজায় রাখার পরই প্রদান করা হয়।"></p>
                </div>
            </div>

            <!-- FAQ 9 -->
            <div class="pt-2.5">
                <button 
                    type="button" 
                    @click="toggleFaq(9)" 
                    class="w-full flex items-center justify-between text-left py-2 font-bold text-xs sm:text-sm text-slate-900 hover:text-orange-600 transition-colors"
                >
                    <span data-en="9. How long is the Refer Return paid?" data-bn="৯. রেফারেল সাপ্তাহিক শেয়ার কত দিন দেওয়া হয়?">9. How long is the Refer Return paid?</span>
                    <span class="text-slate-400 font-bold ml-2" x-text="openFaq === 9 ? '−' : '+'"></span>
                </button>
                <div x-show="openFaq === 9" x-cloak class="pt-1 pb-3 text-xs text-slate-600 leading-relaxed space-y-1">
                    <p data-en="Refer Return provides a 0.25% weekly share on referred National and International project capital, distributed for up to 100 weeks alongside the dropshipping term." data-bn="রেফারকৃত ন্যাশনাল ও ইন্টারন্যাশনাল প্রজেক্টের মূলধনের ওপর প্রতি সপ্তাহে ০.২৫% হারে সর্বোচ্চ ১০০ সপ্তাহ পর্যন্ত এই বিশেষ বোনাস বণ্টিত হয়।"></p>
                </div>
            </div>

            <!-- FAQ 10 -->
            <div class="pt-2.5">
                <button 
                    type="button" 
                    @click="toggleFaq(10)" 
                    class="w-full flex items-center justify-between text-left py-2 font-bold text-xs sm:text-sm text-slate-900 hover:text-orange-600 transition-colors"
                >
                    <span data-en="10. Where do I monitor my real-time team balance and rank progression?" data-bn="১০. আমি কীভাবে আমার টিমের অবস্থান ও র‍্যাংক অগ্রগতি চেক করব?">10. Where do I monitor my real-time team balance and rank progression?</span>
                    <span class="text-slate-400 font-bold ml-2" x-text="openFaq === 10 ? '−' : '+'"></span>
                </button>
                <div x-show="openFaq === 10" x-cloak class="pt-1 pb-3 text-xs text-slate-600 leading-relaxed space-y-1">
                    <p data-en="You can monitor your live binary tree, left/right team counts, and active pair matching progress directly inside your SBL User Dashboard under the 'Binary Network' and 'Rank Status' tabs." data-bn="আপনার SBL ইউজার ড্যাশবোর্ডের 'বাইনারি নেটওয়ার্ক' এবং 'টিম ওভারভিউ' সেকশনে সার্বক্ষণিক লাইভ লেফট/রাইট মেম্বার কাউন্ট এবং র‍্যাংক প্রগ্রেস দেখতে পারবেন।"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 14. COMPLIANCE & IMPORTANT INFORMATION -->
    <!-- ========================================================================= -->
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:p-6 space-y-3 text-xs text-slate-600">
        <div class="flex items-center gap-2">
            <span class="text-amber-500 font-bold text-base">⚠️</span>
            <h3 class="font-bold text-slate-900 text-sm" data-en="Official SBL Business & Marketing Policy" data-bn="অফিসিয়াল SBL নীতিমালা ও কমপ্লায়েন্স">
                Official SBL Business & Marketing Policy
            </h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 leading-relaxed">
            <div>
                <strong class="text-slate-800 block mb-1" data-en="Performance-Based Model:" data-bn="পারফরম্যান্স নির্ভর ব্যবসা:">Performance-Based Model:</strong>
                <p data-en="SBL operates an ethical dropshipping and affiliate sales model. All commissions, pair rewards, and milestone prizes are derived from genuine commercial trade and team product movements, never from recruitment alone." data-bn="SBL একটি পণ্য ও ই-কমার্স নির্ভর ড্রপশিপিং প্ল্যাটফর্ম। সকল কমিশন ও র‍্যাংক প্রাইজমানি প্রকৃত বাণিজ্যিক সেলস ভলিউম থেকে প্রদান করা হয়।"></p>
            </div>
            <div>
                <strong class="text-slate-800 block mb-1" data-en="Safe Marketing Standards:" data-bn="সঠিক উপস্থাপনা ও শর্তাবলী:">Safe Marketing Standards:</strong>
                <p data-en="Independent marketing executives must never represent SBL packages as interest-bearing bank deposits or guaranteed get-rich schemes. All rewards are subject to active account criteria and official company terms." data-bn="কোনো অবস্থাতেই SBL-কে সুদের বা নিশ্চিত অলৌকিক আয়ের মাধ্যম হিসেবে উপস্থাপন করা যাবে না। সকল আয় ব্যক্তিগত ও দলীয় পরিশ্রম ও নিয়মানুযায়ী অর্জিত হয়।"></p>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 15. FINAL CALL TO ACTION (DESKTOP & TABLET) -->
    <!-- ========================================================================= -->
    <div class="rounded-2xl bg-slate-900 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-6 sm:p-8 lg:p-10 shadow-lg border border-slate-800 text-center space-y-4">
        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-orange-500/20 text-orange-300 border border-orange-500/30 inline-block" data-en="Begin Your Leadership Pathway" data-bn="আপনার ক্যারিয়ার যাত্রা শুরু করুন">Begin Your Leadership Pathway</span>
        
        <h2 class="text-2xl sm:text-3xl font-extrabold max-w-2xl mx-auto" data-en="Ready to Build Your SBL Marketing Team?" data-bn="আপনি কি আপনার SBL মার্কেটিং টিম গড়তে প্রস্তুত?">
            Ready to Build Your SBL Marketing Team?
        </h2>

        <p class="text-xs sm:text-sm text-slate-300 max-w-xl mx-auto leading-relaxed" data-en="Activate your Starter Membership or explore National Dropshipping packages to activate your binary placement and qualify for career ranks today." data-bn="আজই আপনার মেম্বারশিপ বা ড্রপশিপিং প্রজেক্ট সক্রিয় করে বাইনারি নেটওয়ার্কে স্থান নিন এবং ক্যারিয়ার র‍্যাংকের পথে এক ধাপ এগিয়ে যান।">
            Activate your Starter Membership or explore National Dropshipping packages to activate your binary placement and qualify for career ranks today.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
            <button 
                type="button"
                @click="openLeadModal('Starter', 'Membership & Rank')"
                class="px-5 py-3 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold transition-all shadow-md active:scale-95"
                data-en="Get Started With Starter Package (৳10,000)"
                data-bn="স্টার্টার প্যাকেজ দিয়ে শুরু করুন (১০,০০০ টাকা)"
            >
                Get Started With Starter Package (৳10,000)
            </button>

            <a 
                href="/packages"
                class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition-colors"
                data-en="Explore All Packages"
                data-bn="সকল প্যাকেজ দেখুন"
            >
                Explore All Packages
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 16. MOBILE STICKY BOTTOM ACTION BAR -->
    <!-- ========================================================================= -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 p-3 px-4 flex items-center justify-between gap-3 shadow-2xl">
        <div class="min-w-0">
            <span class="text-[10px] text-slate-500 block uppercase tracking-wider" data-en="Entry Membership" data-bn="এন্ট্রি মেম্বারশিপ">Entry Membership</span>
            <span class="text-sm font-black text-orange-600 leading-tight block">৳10,000 BDT</span>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button 
                type="button"
                @click="copyText(getMarketingPlanSummaryText(), 'SBL Plan Summary copied!')"
                class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 transition-colors"
                title="Share Ranks"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
            </button>

            <button 
                type="button"
                @click="openLeadModal('Starter', 'Mobile Sticky CTA')"
                class="px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold transition-all shadow-md"
                data-en="Interested in SBL"
                data-bn="SBL-তে আগ্রহী"
            >
                Interested in SBL
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 17. LEAD CAPTURE MODAL (INTEGRATED WITH /leads) -->
    <!-- ========================================================================= -->
    <div 
        x-show="leadModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        @keydown.escape.window="leadModalOpen = false"
    >
        <div 
            @click.away="leadModalOpen = false"
            class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative overflow-hidden"
        >
            <button 
                type="button" 
                @click="leadModalOpen = false" 
                class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1"
                aria-label="Close"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="space-y-1 pr-6">
                <span class="px-2 py-0.5 rounded bg-orange-100 text-orange-800 font-bold text-[10px] uppercase tracking-wider" x-text="'Target: ' + leadTargetRank"></span>
                <h3 class="text-lg font-bold text-slate-900" data-en="Connect With An SBL Marketing Advisor" data-bn="SBL মার্কেটিং অ্যাডভাইজারের সাথে যোগাযোগ">
                    Connect With An SBL Marketing Advisor
                </h3>
                <p class="text-xs text-slate-500" data-en="Leave your details to receive full rank roadmap guidance and team placement support." data-bn="আপনার তথ্য প্রদান করুন। আমাদের টিম দ্রুত আপনার সাথে যোগাযোগ করবে।">
                    Leave your details to receive full rank roadmap guidance and team placement support.
                </p>
            </div>

            <!-- Success State -->
            <template x-if="leadForm.success">
                <div class="my-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-center space-y-2">
                    <span class="text-3xl">✅</span>
                    <h4 class="text-sm font-bold text-emerald-900" data-en="Request Submitted Successfully!" data-bn="আপনার আবেদনটি সফলভাবে জমা হয়েছে!">Request Submitted Successfully!</h4>
                    <p class="text-xs text-emerald-700" data-en="An SBL leadership counselor will reach out to you shortly." data-bn="একজন SBL লিডারশিপ কাউন্সেলর শীঘ্রই আপনার সাথে যোগাযোগ করবেন।">An SBL leadership counselor will reach out to you shortly.</p>
                </div>
            </template>

            <!-- Form -->
            <template x-if="!leadForm.success">
                <form @submit.prevent="submitLead" class="space-y-3.5 mt-4">
                    <template x-if="leadForm.error">
                        <div class="p-2.5 rounded-lg bg-red-50 text-red-700 text-xs border border-red-200" x-text="leadForm.error"></div>
                    </template>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Full Name *" data-bn="আপনার নাম *">Full Name *</label>
                        <input 
                            type="text" 
                            x-model="leadForm.name" 
                            required 
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                            placeholder="e.g. Abdul Hai"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Mobile Number (WhatsApp) *" data-bn="মোবাইল নম্বর (হোয়াটসঅ্যাপ) *">Mobile Number (WhatsApp) *</label>
                        <input 
                            type="tel" 
                            x-model="leadForm.mobile" 
                            required 
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                            placeholder="e.g. 01700000000"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Primary Interest:" data-bn="আগ্রহের ক্ষেত্র:">Primary Interest:</label>
                        <select 
                            x-model="leadInterest" 
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 focus:ring-2 focus:ring-orange-500 focus:outline-none bg-white"
                        >
                            <option value="Rank Progression">Career Rank Progression (FME/SME)</option>
                            <option value="Starter Membership">Starter Membership (৳10,000)</option>
                            <option value="National Dropshipping">National Dropshipping (৳1,20,000)</option>
                            <option value="Team Building">Binary Dual-Team Building</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Note / Questions (Optional):" data-bn="মন্তব্য বা প্রশ্ন (ঐচ্ছিক):">Note / Questions (Optional):</label>
                        <textarea 
                            x-model="leadForm.notes" 
                            rows="2" 
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                            placeholder="Tell us about your team or goals..."
                        ></textarea>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="leadForm.submitting"
                        class="w-full py-2.5 px-4 rounded-xl bg-orange-600 hover:bg-orange-500 disabled:opacity-60 text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2"
                    >
                        <span x-show="!leadForm.submitting" data-en="Submit My Request" data-bn="অনুরোধ জমা দিন">Submit My Request</span>
                        <span x-show="leadForm.submitting" data-en="Submitting..." data-bn="জমা হচ্ছে...">Submitting...</span>
                    </button>
                </form>
            </template>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 18. SHARE RANK MODAL -->
    <!-- ========================================================================= -->
    <div 
        x-show="shareModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        @keydown.escape.window="shareModalOpen = false"
    >
        <div 
            @click.away="shareModalOpen = false"
            class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 relative space-y-4"
        >
            <button 
                type="button" 
                @click="shareModalOpen = false" 
                class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1"
                aria-label="Close"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div>
                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold text-[10px] uppercase tracking-wider" x-text="ranks[shareRankKey]?.code"></span>
                <h3 class="text-base font-bold text-slate-900 mt-1" data-en="Share Rank Information" data-bn="র‍্যাংক তথ্য শেয়ার করুন">Share Rank Information</h3>
                <p class="text-xs text-slate-500" x-text="ranks[shareRankKey]?.name"></p>
            </div>

            <!-- Actions -->
            <div class="space-y-2">
                <button 
                    type="button"
                    @click="shareWhatsApp(getRankSummaryText(ranks[shareRankKey]), window.location.origin + '/ranks?rank=' + ranks[shareRankKey].code.toLowerCase()); shareModalOpen = false;"
                    class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all flex items-center justify-center gap-2"
                >
                    <span>💬</span>
                    <span data-en="Share via WhatsApp" data-bn="হোয়াটসঅ্যাপে শেয়ার">Share via WhatsApp</span>
                </button>

                <button 
                    type="button"
                    @click="shareFacebook(window.location.origin + '/ranks?rank=' + ranks[shareRankKey].code.toLowerCase()); shareModalOpen = false;"
                    class="w-full py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition-all flex items-center justify-center gap-2"
                >
                    <span>🌐</span>
                    <span data-en="Share on Facebook" data-bn="ফেসবুকে শেয়ার">Share on Facebook</span>
                </button>

                <button 
                    type="button"
                    @click="copyText(getRankSummaryText(ranks[shareRankKey]), 'Rank summary copied!'); shareModalOpen = false;"
                    class="w-full py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold transition-colors flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                    <span data-en="Copy Rank Summary Text" data-bn="র‍্যাংক সামারি টেক্সট কপি">Copy Rank Summary Text</span>
                </button>

                <button 
                    type="button"
                    @click="copyText(window.location.origin + '/ranks?rank=' + ranks[shareRankKey].code.toLowerCase(), 'Direct Rank Link copied!'); shareModalOpen = false;"
                    class="w-full py-2.5 px-3 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-medium border border-slate-200 transition-colors flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <span data-en="Copy Direct URL Link" data-bn="সরাসরি লিঙ্ক কপি">Copy Direct URL Link</span>
                </button>
            </div>
        </div>
    </div>

</div>
