{{-- SBL Marketing Commission: Interactive Real-Time Multi-Tier Simulator & Calculators --}}
<div x-data="{
    // Package & Investment state
    packageType: @js($defaultType ?? 'national'),
    packageAmount: @js($defaultAmount ?? 120000),
    referralAmount: @js($defaultAmount ?? 120000),
    
    // Pair Reward state
    qualifiedPairs: 2,
    pairValue: 500,
    dailyPairCap: 100,

    // 10-Gen Simulation state
    teamPackageAmount: 10000,
    teamMultiplier: 2, // Realistic default as requested
    activeGenView: 'matrix', // 'matrix', 'single', 'flow'
    showAllGenerations: false, // Collapsed 4th-10th by default
    activeMobileGenTab: 'top3',

    // Modal & Action states
    showShareModal: false,
    showLeadModal: false,
    activeInfoModal: null,
    copiedCalc: false,
    copiedLink: false,
    leadSubmitting: false,
    leadSuccess: false,
    leadError: '',
    leadForm: {
        name: '',
        mobile: '',
        package: 'National (৳1,20,000)',
        calc_type: 'Investment & Direct Referral',
        budget: '৳1,20,000',
        interested_in: 'Dropshipping & Affiliate',
        notes: ''
    },

    // Verified Generation Matrix (Single Source of Truth)
    genRates: [
        { gen: '1st', gen_bn: '১ম', rate: 10.0, label: '1st Generation (Direct Sponsor)', label_bn: '১ম প্রজন্ম (ডিরেক্ট স্পন্সর)' },
        { gen: '2nd', gen_bn: '২য়', rate: 2.0, label: '2nd Generation', label_bn: '২য় প্রজন্ম' },
        { gen: '3rd', gen_bn: '৩য়', rate: 1.0, label: '3rd Generation', label_bn: '৩য় প্রজন্ম' },
        { gen: '4th', gen_bn: '৪র্থ', rate: 1.0, label: '4th Generation', label_bn: '৪র্থ প্রজন্ম' },
        { gen: '5th', gen_bn: '৫ম', rate: 0.5, label: '5th Generation', label_bn: '৫ম প্রজন্ম' },
        { gen: '6th', gen_bn: '৬ষ্ঠ', rate: 0.1, label: '6th Generation', label_bn: '৬ষ্ঠ প্রজন্ম' },
        { gen: '7th', gen_bn: '৭ম', rate: 0.1, label: '7th Generation', label_bn: '৭ম প্রজন্ম' },
        { gen: '8th', gen_bn: '৮ম', rate: 0.1, label: '8th Generation', label_bn: '৮ম প্রজন্ম' },
        { gen: '9th', gen_bn: '৯ম', rate: 0.1, label: '9th Generation', label_bn: '৯ম প্রজন্ম' },
        { gen: '10th', gen_bn: '১০ম', rate: 0.1, label: '10th Generation', label_bn: '১০ম প্রজন্ম' }
    ],

    // 1. Investment ROI Computations
    get devFee() {
        if (this.packageType === 'starter') return 10000;
        if (this.packageType === 'national') return 20000;
        if (this.packageType === 'international') return 50000;
        return this.packageAmount >= 500000 ? 50000 : 20000;
    },
    get coreInvestment() {
        return Math.max(0, this.packageAmount - this.devFee);
    },
    get weeklyRate() {
        if (this.packageType === 'starter') return 0;
        if (this.packageType === 'national') return 0.0175;
        if (this.packageType === 'international') return 0.02;
        return this.packageAmount >= 500000 ? 0.02 : 0.0175;
    },
    get weeklyEarning() {
        return Math.round(this.coreInvestment * this.weeklyRate);
    },
    get monthlyEarning() {
        return Math.round((this.weeklyEarning * 52) / 12);
    },
    get totalReturn100Weeks() {
        return Math.round(this.weeklyEarning * 100);
    },
    get netProfitTotal() {
        return Math.max(0, this.totalReturn100Weeks - this.packageAmount);
    },
    get netProfitCore() {
        return Math.max(0, this.totalReturn100Weeks - this.coreInvestment);
    },

    // Capital Recovery Computations
    get coreRecoveryWeeks() {
        if (this.weeklyEarning <= 0) return 0;
        return (this.coreInvestment / this.weeklyEarning).toFixed(1);
    },
    get totalRecoveryWeeks() {
        if (this.weeklyEarning <= 0) return 0;
        return (this.packageAmount / this.weeklyEarning).toFixed(1);
    },

    // 2. Direct Referral Computations
    get spotCommission() {
        return Math.round(this.referralAmount * 0.10);
    },
    get weeklyReferReturn() {
        return Math.round(this.referralAmount * 0.0025);
    },
    get totalReferReturn100Weeks() {
        return Math.round(this.weeklyReferReturn * 100);
    },
    get totalPotentialReferralIncome() {
        return this.spotCommission + this.totalReferReturn100Weeks;
    },

    // 3. Pair Reward Computations
    get dailyPairReward() {
        let validPairs = Math.min(this.dailyPairCap, Math.max(0, this.qualifiedPairs));
        return validPairs * this.pairValue;
    },
    get monthlyPairScenario() {
        return this.dailyPairReward * 30;
    },
    get maxDailyPairReward() {
        return this.dailyPairCap * this.pairValue;
    },

    // 4. 10-Generation Matrix Computations
    getMatrixRow(index) {
        let level = index + 1;
        let rate = this.genRates[index].rate;
        let people = Math.pow(this.teamMultiplier, level);
        let volume = people * this.teamPackageAmount;
        let commission = Math.round(volume * (rate / 100));
        return {
            level: level,
            gen: this.genRates[index].gen,
            gen_bn: this.genRates[index].gen_bn,
            rate: rate,
            people: people,
            volume: volume,
            commission: commission
        };
    },
    get totalMatrixCommission() {
        let sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += this.getMatrixRow(i).commission;
        }
        return sum;
    },
    get totalMatrixPeople() {
        let sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += Math.pow(this.teamMultiplier, i + 1);
        }
        return sum;
    },

    // Currency Formatting (USD / BDT with dynamic exchange rate)
    fmt(bdtValue) {
        let num = Number(bdtValue) || 0;
        if (this.$store?.currency?.code === 'USD') {
            let rate = this.$store?.currency?.rate || 120;
            let usd = Math.round(num / rate);
            return '$' + usd.toLocaleString('en-US');
        }
        return '৳' + Math.round(num).toLocaleString('en-IN');
    },

    // Abbreviated Display for huge numbers
    fmtAbbr(bdtValue) {
        let num = Number(bdtValue) || 0;
        if (this.$store?.currency?.code === 'USD') {
            let rate = this.$store?.currency?.rate || 120;
            let usd = Math.round(num / rate);
            if (usd >= 1e9) return '$' + (usd / 1e9).toFixed(2) + ' B';
            if (usd >= 1e6) return '$' + (usd / 1e6).toFixed(2) + ' M';
            return '$' + usd.toLocaleString('en-US');
        }
        if (num >= 1e7) {
            let crore = (num / 1e7).toFixed(2);
            return '৳' + crore + ' কোটি';
        }
        if (num >= 1e5) {
            let lac = (num / 1e5).toFixed(2);
            return '৳' + lac + ' লাখ';
        }
        return '৳' + Math.round(num).toLocaleString('en-IN');
    },

    scrollToId(id) {
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    selectPackagePreset(type, amount) {
        this.packageType = type;
        this.packageAmount = amount;
        this.leadForm.package = type === 'national' ? 'National (৳1,20,000)' : (type === 'international' ? 'International (৳5,50,000)' : 'Starter (৳10,000)');
        this.leadForm.budget = this.fmt(amount);
    },

    getMarketingCopyText() {
        let text = '📊 SBL Commission & Earning Estimate\n' +
            '-----------------------------------------\n' +
            'Package: ' + (this.packageType === 'national' ? 'National Project' : (this.packageType === 'international' ? 'International Store' : 'Starter')) + '\n' +
            'Total Package Amount: ' + this.fmt(this.packageAmount) + '\n' +
            'Core Investment Capital: ' + this.fmt(this.coreInvestment) + '\n' +
            'Setup / Development Fee: ' + this.fmt(this.devFee) + '\n' +
            'Estimated Weekly Return: ' + this.fmt(this.weeklyEarning) + ' / week\n' +
            'Estimated Monthly Return: ' + this.fmt(this.monthlyEarning) + ' / month\n' +
            'Total 100-Week Plan Return: ' + this.fmt(this.totalReturn100Weeks) + '\n' +
            'Estimated Capital Recovery: ~' + this.totalRecoveryWeeks + ' weeks\n' +
            '-----------------------------------------\n' +
            'Direct Referral (10% Spot): ' + this.fmt(this.spotCommission) + '\n' +
            'Weekly Refer Return (0.25%): ' + this.fmt(this.weeklyReferReturn) + '/wk\n' +
            'Binary Pair Reward: ৳500 / qualified pair\n' +
            '-----------------------------------------\n' +
            'Disclaimer: Illustrative calculation based on current SBL configured plan. Subject to terms and active operational performance.\n' +
            'Simulate live: ' + window.location.origin + '/commission?type=' + this.packageType + '&amount=' + this.packageAmount;
        return text;
    },

    copyCalc() {
        let text = this.getMarketingCopyText();
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
        this.copiedCalc = true;
        setTimeout(() => this.copiedCalc = false, 2500);
    },

    copyShareLink() {
        let url = window.location.origin + '/commission?type=' + this.packageType + '&amount=' + this.packageAmount;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url);
        }
        this.copiedLink = true;
        setTimeout(() => this.copiedLink = false, 2500);
    },

    openLeadModalForCalc() {
        this.leadForm.package = this.packageType === 'national' ? 'National (৳1,20,000)' : (this.packageType === 'international' ? 'International (৳5,50,000)' : 'Custom');
        this.leadForm.budget = this.fmt(this.packageAmount);
        this.leadSuccess = false;
        this.leadError = '';
        this.showLeadModal = true;
    },

    async submitLeadFromCommission() {
        if (!this.leadForm.name || !this.leadForm.mobile) {
            this.leadError = 'Please provide both Prospect Name and Mobile number.';
            return;
        }
        this.leadSubmitting = true;
        this.leadError = '';

        try {
            let res = await fetch('/leads', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]') ? document.querySelector('meta[name=csrf-token]').getAttribute('content') : ''
                },
                body: JSON.stringify({
                    name: this.leadForm.name,
                    mobile: this.leadForm.mobile,
                    lead_source_id: 1,
                    lead_source_detail: 'Commission Calculator',
                    interest_types: [this.leadForm.package, this.leadForm.calc_type],
                    budget: this.leadForm.budget,
                    notes: 'Calculation: ' + this.leadForm.package + ' | Weekly Return: ' + this.fmt(this.weeklyEarning) + ' | Notes: ' + this.leadForm.notes
                })
            });

            if (res.ok) {
                this.leadSuccess = true;
                setTimeout(() => {
                    this.showLeadModal = false;
                    this.leadSuccess = false;
                    this.leadForm.name = '';
                    this.leadForm.mobile = '';
                    this.leadForm.notes = '';
                }, 2000);
            } else {
                let data = await res.json();
                this.leadError = data.message || 'Failed to save lead. Please try again.';
            }
        } catch (err) {
            this.leadError = 'Network error saving lead. Check connection.';
        } finally {
            this.leadSubmitting = false;
        }
    }
}" class="space-y-8 pb-20 md:pb-10">

    {{-- SECTION 01: COMPACT HEADER & 4-CARD OVERVIEW --}}
    <div class="space-y-4">
        {{-- Header Banner --}}
        <div class="relative overflow-hidden bg-slate-900 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-5 md:p-8 text-white shadow-xl border border-slate-700/60">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-orange-600/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-500/20 text-orange-300 border border-orange-500/30 text-xs font-bold uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                            <span data-en="SBL Marketing • Commission Engine" data-bn="SBL মার্কেটিং • কমিশন ইঞ্জিন">SBL Marketing • Commission Engine</span>
                        </span>
                        <span class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 border border-slate-700 text-xs font-medium" data-en="Mathematically Transparent" data-bn="স্বচ্ছ গাণিতিক হিসাব">Mathematically Transparent</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight text-white" data-en="Commission & Multi-Tier Simulator" data-bn="কমিশন ও মাল্টি-টিয়ার সিমুলেটর">
                        Commission & Multi-Tier Simulator
                    </h1>
                    <p class="text-xs md:text-sm text-slate-300 leading-relaxed max-w-xl" data-en="Real-time plan returns, direct spot commissions, binary pair rewards, and 10-generation leadership override simulations." data-bn="বাস্তবসম্মত প্ল্যান রিটার্ন, তাৎক্ষণিক ডিরেক্ট কমিশন, বাইনারি পেয়ার রিওয়ার্ড এবং ১০ প্রজন্মের লিডারশিপ ওভাররাইড হিসাব করুন।">
                        Real-time plan returns, direct spot commissions, binary pair rewards, and 10-generation leadership override simulations.
                    </p>
                </div>

                {{-- Quick Actions --}}
                <div class="flex flex-wrap md:flex-col items-stretch gap-2 shrink-0">
                    <button type="button" @click="copyCalc()" class="px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                        <span x-text="copiedCalc ? 'Copied Calculation!' : 'Copy Calculation'"></span>
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showShareModal = true" class="flex-1 px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition-all text-center flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                            <span data-en="Share" data-bn="শেয়ার">Share</span>
                        </button>
                        <button type="button" @click="openLeadModalForCalc()" class="flex-1 px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition-all text-center flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                            <span data-en="Save Lead" data-bn="লিড সেভ">Save Lead</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4 Overview Cards ("Understand Your Earning Sources") --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. Plan Return --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4.5 shadow-xs flex flex-col justify-between space-y-3 hover:border-orange-300 transition-colors">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="w-9 h-9 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center text-lg font-bold">📈</span>
                        <span class="px-2 py-0.5 rounded-md bg-orange-50 text-orange-700 text-[10px] font-bold">100 Weeks</span>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900" data-en="Plan Return" data-bn="প্ল্যান রিটার্ন">Plan Return</h3>
                    <p class="text-xs text-slate-500" data-en="Weekly commercial returns derived from active dropshipping operations." data-bn="লাইভ ড্রপশিপিং সেলস থেকে অর্জিত সাপ্তাহিক বাণিজ্যিক মুনাফা।">
                        Weekly commercial returns derived from active dropshipping operations.
                    </p>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-medium" data-en="Verified Rate" data-bn="স্বীকৃত রেট">Verified Rate</span>
                        <span class="text-xs font-extrabold text-orange-600">1.75% – 2.0% / wk</span>
                    </div>
                    <button type="button" @click="scrollToId('investment-calc-section')" class="px-2.5 py-1 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 font-bold text-[11px] transition-colors" data-en="Calculate" data-bn="হিসেব করুন">Calculate &darr;</button>
                </div>
            </div>

            {{-- 2. Direct / Spot Commission --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4.5 shadow-xs flex flex-col justify-between space-y-3 hover:border-emerald-300 transition-colors">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg font-bold">⚡</span>
                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold">Instant</span>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900" data-en="Spot Commission" data-bn="স্পট কমিশন">Spot Commission</h3>
                    <p class="text-xs text-slate-500" data-en="Instant cash bonus credited immediately upon direct referral activation." data-bn="ডিরেক্ট রেফারেল যুক্ত হওয়ার সাথে সাথে তাৎক্ষণিক ওয়ালেটে জমা হওয়া কমিশন।">
                        Instant cash bonus credited immediately upon direct referral activation.
                    </p>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-medium" data-en="Verified Rate" data-bn="স্বীকৃত রেট">Verified Rate</span>
                        <span class="text-xs font-extrabold text-emerald-600">10% Spot + 0.25%/wk</span>
                    </div>
                    <button type="button" @click="scrollToId('referral-calc-section')" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] transition-colors" data-en="Calculate" data-bn="হিসেব করুন">Calculate &darr;</button>
                </div>
            </div>

            {{-- 3. Pair Reward --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4.5 shadow-xs flex flex-col justify-between space-y-3 hover:border-blue-300 transition-colors">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg font-bold">⚖️</span>
                        <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[10px] font-bold">Binary Match</span>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900" data-en="Pair Reward" data-bn="পেয়ার রিওয়ার্ড">Pair Reward</h3>
                    <p class="text-xs text-slate-500" data-en="Matching reward when 1 BV balances on Left and Right sales teams." data-bn="লেফট ও রাইট টিমে ১:১ পয়েন্ট ব্যালেন্স হওয়ার ওপর প্রাপ্ত ম্যাচিং বোনাস।">
                        Matching reward when 1 BV balances on Left and Right sales teams.
                    </p>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-medium" data-en="Verified Value" data-bn="স্বীকৃত মান">Verified Value</span>
                        <span class="text-xs font-extrabold text-blue-600">৳500 / Pair (Cap 100)</span>
                    </div>
                    <button type="button" @click="scrollToId('pair-calc-section')" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] transition-colors" data-en="Calculate" data-bn="হিসেব করুন">Calculate &darr;</button>
                </div>
            </div>

            {{-- 4. Generation Commission --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4.5 shadow-xs flex flex-col justify-between space-y-3 hover:border-purple-300 transition-colors">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg font-bold">🌐</span>
                        <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-800 text-[10px] font-bold">10 Tiers (UDR)</span>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900" data-en="Generation UDR" data-bn="জেনারেশন UDR">Generation UDR</h3>
                    <p class="text-xs text-slate-500" data-en="Tiered leadership commission distributed across 10 network generations." data-bn="১০ প্রজন্ম পর্যন্ত টিম ভলিউমের ওপর নির্ধারিত লিডারশিপ ওভাররাইড।">
                        Tiered leadership commission distributed across 10 network generations.
                    </p>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-medium" data-en="Depth" data-bn="গভীরতা">Depth</span>
                        <span class="text-xs font-extrabold text-purple-700">10% down to 0.1%</span>
                    </div>
                    <button type="button" @click="scrollToId('simulator-section')" class="px-2.5 py-1 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-[11px] transition-colors" data-en="Simulate" data-bn="সিমুলেট করুন">Simulate &darr;</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 02 & 03: INVESTMENT RETURN & DIRECT REFERRAL CALCULATORS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- 02. INVESTMENT RETURN CALCULATOR (WITH CAPITAL RECOVERY) --}}
        <div id="investment-calc-section" class="bg-white rounded-3xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-5 flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="px-2.5 py-0.5 bg-orange-100 text-orange-800 text-[10px] font-bold rounded-md uppercase">01 • ROI Simulator</span>
                        <h2 class="text-base font-extrabold text-slate-900 mt-1" data-en="Investment Return Calculator (100 Weeks)" data-bn="বিনিয়োগ রিটার্ন ক্যালকুলেটর (১০০ সপ্তাহ)">
                            Investment Return Calculator (100 Weeks)
                        </h2>
                    </div>
                    <button type="button" @click="activeInfoModal = 'roi_formula'" class="text-slate-400 hover:text-orange-600 transition-colors" title="View Calculation Formula">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </button>
                </div>

                {{-- Package Type Toggle --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" data-en="Select Package Preset:" data-bn="প্যাকেজ নির্বাচন করুন:">Select Package Preset:</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="selectPackagePreset('starter', 10000)"
                                :class="packageType === 'starter' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="py-2 px-2.5 rounded-xl text-xs transition-all text-left">
                            <span class="block font-bold truncate">Starter</span>
                            <span class="text-[10px] opacity-80" x-text="fmt(10000)"></span>
                        </button>
                        <button type="button" @click="selectPackagePreset('national', 120000)"
                                :class="packageType === 'national' ? 'bg-orange-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="py-2 px-2.5 rounded-xl text-xs transition-all text-left">
                            <span class="block font-bold truncate">National</span>
                            <span class="text-[10px] opacity-80" x-text="fmt(120000)"></span>
                        </button>
                        <button type="button" @click="selectPackagePreset('international', 550000)"
                                :class="packageType === 'international' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="py-2 px-2.5 rounded-xl text-xs transition-all text-left">
                            <span class="block font-bold truncate">International</span>
                            <span class="text-[10px] opacity-80" x-text="fmt(550000)"></span>
                        </button>
                    </div>
                </div>

                {{-- Amount Input --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700" data-en="Total Package Amount:" data-bn="মোট প্যাকেজ মূল্য:">Total Package Amount:</label>
                        <span class="text-[11px] font-semibold text-slate-500">
                            Core Capital: <strong class="text-orange-600" x-text="fmt(coreInvestment)"></strong>
                        </span>
                    </div>
                    <div class="relative">
                        <input type="number" inputmode="numeric" x-model.number="packageAmount" step="10000" class="w-full text-base font-extrabold rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5 bg-white">
                        <div class="absolute right-3 top-3 text-xs font-bold text-slate-400" x-text="$store?.currency?.code || 'BDT'"></div>
                    </div>

                    {{-- Quick Presets --}}
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <button type="button" @click="selectPackagePreset('starter', 10000)" class="px-2.5 py-1 rounded-lg text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">৳10k Starter</button>
                        <button type="button" @click="selectPackagePreset('national', 120000)" class="px-2.5 py-1 rounded-lg text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">৳1.20L National</button>
                        <button type="button" @click="selectPackagePreset('national', 250000)" class="px-2.5 py-1 rounded-lg text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">৳2.50L Project</button>
                        <button type="button" @click="selectPackagePreset('international', 550000)" class="px-2.5 py-1 rounded-lg text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">৳5.50L Intl</button>
                    </div>
                </div>

                {{-- Fee & Core Breakdown Notice --}}
                <div class="p-3 bg-amber-50/80 border border-amber-200 rounded-xl text-xs text-amber-900 space-y-1">
                    <div class="flex items-center justify-between font-semibold">
                        <span data-en="🛠️ Setup & Content Fee (Non-refundable):" data-bn="🛠️ সেটআপ ও কনটেন্ট ফি (অফেরতযোগ্য):">🛠️ Setup & Content Fee:</span>
                        <span class="font-bold text-amber-950" x-text="fmt(devFee)"></span>
                    </div>
                    <p class="text-[11px] text-amber-800 leading-relaxed" data-en="Returns are calculated exclusively on core capital. Technical store setup & domain fees are deducted upfront." data-bn="রিটার্ন শুধুমাত্র কোর ক্যাপিটালের ওপর হিসেব করা হয়। কারিগরি শপ ও কনটেন্ট ফি এর অন্তর্ভুক্ত নয়।">
                        Returns are calculated exclusively on core capital. Technical store setup & domain fees are deducted upfront.
                    </p>
                </div>
            </div>

            {{-- Results Display Box --}}
            <div class="p-4 bg-slate-900 text-white rounded-2xl space-y-3 text-xs shadow-md">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <span class="text-slate-400 font-medium" data-en="Active Core Capital:" data-bn="সক্রিয় কোর ক্যাপিটাল:">Active Core Capital:</span>
                    <span class="font-bold text-white text-sm" x-text="fmt(coreInvestment)"></span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-300 font-medium" data-en="Weekly Plan Return:" data-bn="সাপ্তাহিক প্ল্যান রিটার্ন:">Weekly Plan Return:</span>
                        <span class="text-[10px] text-slate-400 font-semibold" x-text="'(' + (weeklyRate * 100).toFixed(2) + '% / wk)'"></span>
                    </div>
                    <span class="font-extrabold text-orange-400 text-base" x-text="fmt(weeklyEarning) + ' / wk'"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-300 font-medium" data-en="Estimated Monthly Return:" data-bn="সম্ভাব্য মাসিক রিটার্ন:">Estimated Monthly Return:</span>
                    <span class="font-bold text-emerald-400 text-sm" x-text="fmt(monthlyEarning) + ' / mo'"></span>
                </div>
                <div class="border-t border-slate-800 pt-2 flex items-center justify-between">
                    <div>
                        <span class="text-slate-200 font-bold block" data-en="Total 100-Week Return:" data-bn="মোট ১০০ সপ্তাহের রিটার্ন:">Total 100-Week Return:</span>
                        <span class="text-[10px] text-slate-400" data-en="Estimated based on defined weekly rate" data-bn="নির্ধারিত প্ল্যান রেটের ওপর ভিত্তি করে">Estimated based on defined weekly rate</span>
                    </div>
                    <span class="font-black text-emerald-400 text-xl" x-text="fmt(totalReturn100Weeks)"></span>
                </div>
                <div class="border-t border-slate-800 pt-2 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400" data-en="Net Gain (Above Total Package Cost):" data-bn="নেট লাভ (প্যাকেজ মূল্য বাদে):">Net Gain (Above Total Package Cost):</span>
                    <span class="font-bold text-emerald-300" x-text="fmt(netProfitTotal)"></span>
                </div>

                {{-- SECTION 05: CAPITAL RECOVERY WIDGET --}}
                <div class="border-t border-slate-800 pt-2.5 mt-1 bg-white/5 p-2.5 rounded-xl space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-orange-300 uppercase tracking-wider" data-en="⏱️ Estimated Capital Recovery:" data-bn="⏱️ আনুমানিক মূলধন রিকভারি:">⏱️ Estimated Capital Recovery:</span>
                        <span class="font-black text-white text-xs" x-text="'~' + totalRecoveryWeeks + ' Weeks'"></span>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-300">
                        <span>Core Capital Recovery: <strong class="text-white" x-text="'~' + coreRecoveryWeeks + ' wks'"></strong></span>
                        <span>Total Package Recovery: <strong class="text-orange-300" x-text="'~' + totalRecoveryWeeks + ' wks'"></strong></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 03. DIRECT REFERRAL CALCULATOR --}}
        <div id="referral-calc-section" class="bg-white rounded-3xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-5 flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-md uppercase">02 • Direct Sponsor</span>
                        <h2 class="text-base font-extrabold text-slate-900 mt-1" data-en="Direct Referral Commission Calculator" data-bn="ডিরেক্ট রেফারেল কমিশন ক্যালকুলেটর">
                            Direct Referral Commission Calculator
                        </h2>
                    </div>
                    <button type="button" @click="activeInfoModal = 'direct_formula'" class="text-slate-400 hover:text-emerald-600 transition-colors" title="View Calculation Formula">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </button>
                </div>

                {{-- Input Amount --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Referred Package / Project Amount:" data-bn="রেফারকৃত প্যাকেজ বা প্রজেক্ট মূল্য:">Referred Package / Project Amount:</label>
                    <div class="relative">
                        <input type="number" inputmode="numeric" x-model.number="referralAmount" step="10000" class="w-full text-base font-extrabold rounded-xl border border-slate-300 focus:border-emerald-500 px-3.5 py-2.5 bg-white">
                        <div class="absolute right-3 top-3 text-xs font-bold text-slate-400" x-text="$store?.currency?.code || 'BDT'"></div>
                    </div>

                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <button type="button" @click="referralAmount = 10000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">৳10k Starter</button>
                        <button type="button" @click="referralAmount = 120000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">৳1.20L National</button>
                        <button type="button" @click="referralAmount = 250000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">৳2.50L Project</button>
                        <button type="button" @click="referralAmount = 550000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">৳5.50L Intl</button>
                    </div>
                </div>

                {{-- Live Results Cards --}}
                <div class="space-y-3 text-xs">
                    {{-- 10% Spot Commission --}}
                    <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl flex items-center justify-between">
                        <div>
                            <span class="font-bold text-emerald-900 block text-sm" data-en="10% Spot Commission (Instant)" data-bn="১০% স্পট কমিশন (তাৎক্ষণিক)">10% Spot Commission (Instant)</span>
                            <span class="text-[11px] text-emerald-700" data-en="Credited to wallet upon project activation" data-bn="প্রজেক্ট সক্রিয় হওয়ার সাথে সাথে ওয়ালেটে জমা">Credited to wallet upon project activation</span>
                        </div>
                        <span class="text-2xl font-black text-emerald-700" x-text="fmt(spotCommission)"></span>
                    </div>

                    {{-- 0.25% Weekly Refer Return --}}
                    <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-2xl space-y-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-bold text-blue-900 block" data-en="0.25% Weekly Refer Return" data-bn="০.২৫% সাপ্তাহিক রেফারেল রিটার্ন">0.25% Weekly Refer Return</span>
                                <span class="text-[11px] text-blue-700" data-en="Payable weekly for up to 100 weeks" data-bn="১০০ সপ্তাহ পর্যন্ত প্রতি সপ্তাহে ওয়ালেটে জমা">Payable weekly for up to 100 weeks</span>
                            </div>
                            <span class="text-lg font-bold text-blue-700" x-text="fmt(weeklyReferReturn) + ' / wk'"></span>
                        </div>
                        <div class="border-t border-blue-200 pt-1.5 flex items-center justify-between text-[11px] text-blue-800">
                            <span data-en="Total 100-Week Refer Return:" data-bn="মোট ১০০ সপ্তাহের রেফারেল রিটার্ন:">Total 100-Week Refer Return:</span>
                            <span class="font-bold text-blue-900" x-text="fmt(totalReferReturn100Weeks)"></span>
                        </div>
                    </div>

                    {{-- Total Direct Potential --}}
                    <div class="p-3.5 bg-slate-100 rounded-xl border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-800 block" data-en="Total Potential Direct Income:" data-bn="মোট সম্ভাব্য ডিরেক্ট আয়:">Total Potential Direct Income:</span>
                            <span class="text-[10px] text-slate-500">Spot Commission + 100-Week Refer Return</span>
                        </div>
                        <span class="text-base font-black text-slate-900" x-text="fmt(totalPotentialReferralIncome)"></span>
                    </div>
                </div>
            </div>

            {{-- Formula Visibility Box --}}
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-[11px] text-slate-600 space-y-1">
                <span class="font-bold text-slate-800 block" data-en="Direct Formula:" data-bn="ডিরেক্ট ফর্মুলা:">Direct Formula:</span>
                <p>Referral Amount × 10% = Spot Commission | Referral Amount × 0.25% × 100 = Total Refer Return</p>
            </div>
        </div>

    </div>

    {{-- SECTION 04: PAIR REWARD CALCULATOR (DEDICATED SECTION) --}}
    <div id="pair-calc-section" class="bg-white rounded-3xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-bold rounded-md uppercase">03 • Binary Matching</span>
                <h2 class="text-base md:text-lg font-extrabold text-slate-900 mt-1" data-en="Pair Reward Calculator (Dual-Team Binary Matching)" data-bn="পেয়ার রিওয়ার্ড ক্যালকুলেটর (বাইনারি ম্যাচিং)">
                    Pair Reward Calculator (Dual-Team Binary Matching)
                </h2>
                <p class="text-xs text-slate-500" data-en="Calculate potential team matching bonuses based on active qualified Left:Right pair volume." data-bn="লেফট ও রাইট টিমের কোয়ালিফাইড পয়েন্ট ম্যাচিং অনুযায়ী পেয়ার বোনাস হিসেব করুন।">
                    Calculate potential team matching bonuses based on active qualified Left:Right pair volume.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold">1 Pair = ৳500</span>
                <span class="px-2.5 py-1 rounded-lg bg-orange-100 text-orange-800 text-xs font-semibold">Daily Cap: 100 Pairs</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Pair Inputs --}}
            <div class="space-y-4 md:col-span-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Qualified Pairs per Day:" data-bn="দৈনিক কোয়ালিফাইড পেয়ার সংখ্যা:">Qualified Pairs per Day:</label>
                    <div class="flex items-center gap-3">
                        <input type="range" min="1" max="100" x-model.number="qualifiedPairs" class="w-full accent-blue-600">
                        <span class="w-12 text-center font-extrabold text-sm text-blue-700 bg-blue-50 py-1 rounded-lg border border-blue-200" x-text="qualifiedPairs"></span>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                        <span>1 Pair</span>
                        <span>50 Pairs</span>
                        <span>100 Pairs (Max Cap)</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    <button type="button" @click="qualifiedPairs = 1" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">1 Pair (৳500)</button>
                    <button type="button" @click="qualifiedPairs = 5" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">5 Pairs (৳2.5k)</button>
                    <button type="button" @click="qualifiedPairs = 10" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">10 Pairs (৳5k)</button>
                    <button type="button" @click="qualifiedPairs = 100" class="px-2.5 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-lg text-xs font-bold">100 Pairs (Max)</button>
                </div>
            </div>

            {{-- Pair Results --}}
            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-4 bg-blue-50/60 border border-blue-200 rounded-2xl flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-blue-900 block" data-en="Daily Pair Reward" data-bn="দৈনিক পেয়ার রিওয়ার্ড">Daily Pair Reward</span>
                        <span class="text-[10px] text-blue-700" x-text="qualifiedPairs + ' pairs × ৳500'"></span>
                    </div>
                    <span class="text-xl font-black text-blue-700 mt-3" x-text="fmt(dailyPairReward)"></span>
                </div>

                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block" data-en="Estimated Monthly Scenario" data-bn="সম্ভাব্য মাসিক সিনারিও">Estimated Monthly Scenario</span>
                        <span class="text-[10px] text-slate-500">Based on consistent 30-day pace</span>
                    </div>
                    <span class="text-xl font-black text-slate-800 mt-3" x-text="fmt(monthlyPairScenario)"></span>
                </div>

                <div class="p-4 bg-orange-50/60 border border-orange-200 rounded-2xl flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-orange-900 block" data-en="Configured Daily Cap" data-bn="দৈনিক সর্বোচ্চ ক্যাপিং">Configured Daily Cap</span>
                        <span class="text-[10px] text-orange-700">100 pairs limit for sustainability</span>
                    </div>
                    <span class="text-xl font-black text-orange-700 mt-3" x-text="fmt(maxDailyPairReward)"></span>
                </div>
            </div>
        </div>

        {{-- Safe Claim Notice --}}
        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 flex items-start gap-2">
            <span class="text-blue-600 font-bold">ℹ️ Note:</span>
            <p class="text-[11px] leading-relaxed" data-en="Maximum theoretical amount based on configured daily pair limit. Actual reward depends on qualified pair volume and applicable SBL binary conditions." data-bn="এটি দৈনিক সর্বোচ্চ সীমা ভিত্তিক হিসাব। প্রকৃত আয় টিম মেম্বারদের সক্রিয় সেলস পয়েন্ট ও দ্বিপাক্ষিক ব্যালেন্সের ওপর নির্ভরশীল।">
                Maximum theoretical amount based on configured daily pair limit. Actual reward depends on qualified pair volume and applicable SBL binary conditions.
            </p>
        </div>
    </div>

    {{-- SECTION 05, 08, 09, 10, 11: 10-GENERATION AFFILIATE COMMISSION SIMULATOR --}}
    <div id="simulator-section" class="bg-white rounded-3xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 text-[10px] font-bold rounded-md uppercase">04 • Multi-Tier Override</span>
                    <h2 class="text-base md:text-lg font-extrabold text-slate-900" data-en="10-Generation Commission Simulator (UDR Matrix)" data-bn="১০-প্রজন্ম কমিশন সিমুলেটর (UDR ম্যাট্রিক্স)">
                        10-Generation Commission Simulator (UDR Matrix)
                    </h2>
                </div>
                <p class="text-xs text-slate-500 mt-0.5" data-en="Mathematical simulation of leadership overrides across 10 network tiers." data-bn="১০ প্রজন্মব্যাপী টিম বিক্রয়ের ওপর লিডারশিপ কমিশনের গাণিতিক সিমুলেশন।">
                    Mathematical simulation of leadership overrides across 10 network tiers.
                </p>
            </div>

            {{-- Mode Switcher --}}
            <div class="flex items-center gap-2 text-xs">
                <button type="button" @click="activeGenView = 'matrix'"
                        :class="activeGenView === 'matrix' ? 'bg-orange-600 text-white font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="px-3 py-1.5 rounded-lg transition-all" data-en="Team Matrix" data-bn="টিম ম্যাট্রিক্স">
                    Team Matrix
                </button>
                <button type="button" @click="activeGenView = 'single'"
                        :class="activeGenView === 'single' ? 'bg-orange-600 text-white font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="px-3 py-1.5 rounded-lg transition-all" data-en="Single Project" data-bn="একক প্রজেক্ট">
                    Single Project
                </button>
                <button type="button" @click="activeGenView = 'flow'"
                        :class="activeGenView === 'flow' ? 'bg-orange-600 text-white font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="px-3 py-1.5 rounded-lg transition-all" data-en="Visual Flow" data-bn="ভিজ্যুয়াল ফ্লো">
                    Visual Flow
                </button>
            </div>
        </div>

        {{-- 1. MATRIX VIEW --}}
        <div x-show="activeGenView === 'matrix'" class="space-y-6">
            {{-- Controls Bar --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Average Package / Membership Size:" data-bn="গড় প্যাকেজ বা মেম্বারশিপ সাইজ:">Average Package / Membership Size:</label>
                    <input type="number" inputmode="numeric" x-model.number="teamPackageAmount" step="1000" class="w-full text-sm font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2 bg-white">
                    <div class="flex flex-wrap gap-1 mt-1.5">
                        <button type="button" @click="teamPackageAmount = 10000" class="px-2 py-0.5 bg-white border border-slate-200 text-slate-700 rounded text-[11px] font-medium hover:border-orange-500">৳10,000 Starter</button>
                        <button type="button" @click="teamPackageAmount = 120000" class="px-2 py-0.5 bg-white border border-slate-200 text-slate-700 rounded text-[11px] font-medium hover:border-orange-500">৳1,20,000 National</button>
                        <button type="button" @click="teamPackageAmount = 550000" class="px-2 py-0.5 bg-white border border-slate-200 text-slate-700 rounded text-[11px] font-medium hover:border-orange-500">৳5,50,000 Intl</button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Average Direct Referrals per Member (Multiplier):" data-bn="সদস্য প্রতি গড় রেফারেল গুণক:">Average Direct Referrals per Member (Multiplier):</label>
                    <div class="grid grid-cols-4 gap-1.5">
                        <button type="button" @click="teamMultiplier = 2" 
                                :class="teamMultiplier === 2 ? 'bg-orange-600 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700'"
                                class="py-2 rounded-xl text-xs text-center transition-all">2×2 Realistic</button>
                        <button type="button" @click="teamMultiplier = 3" 
                                :class="teamMultiplier === 3 ? 'bg-orange-600 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700'"
                                class="py-2 rounded-xl text-xs text-center transition-all">3×3 Team</button>
                        <button type="button" @click="teamMultiplier = 5" 
                                :class="teamMultiplier === 5 ? 'bg-orange-600 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700'"
                                class="py-2 rounded-xl text-xs text-center transition-all">5×5 Team</button>
                        <button type="button" @click="teamMultiplier = 10" 
                                :class="teamMultiplier === 10 ? 'bg-purple-600 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700'"
                                class="py-2 rounded-xl text-xs text-center transition-all">10×10 Max</button>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1" data-en="Default is set to 2× to prevent exaggerated mathematical figures." data-bn="অবাস্তব সংখ্যা পরিহার করতে ডিফল্ট ২× ডুপ্লিকেশন রাখা হয়েছে।">
                        Default is set to 2× to prevent exaggerated mathematical figures.
                    </p>
                </div>
            </div>

            {{-- SECTION 15: DE-EMPHASIZED SUMMARY BANNER --}}
            <div class="bg-slate-900 text-white p-5 rounded-2xl border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="space-y-1 text-center md:text-left">
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block" data-en="Illustrative Full-Network Projection" data-bn="পূর্ণ নেটওয়ার্কের সম্ভাব্য গাণিতিক হিসাব">
                        Illustrative Full-Network Projection
                    </span>
                    <div class="text-xl md:text-2xl font-black text-white">
                        <span x-text="fmtAbbr(totalMatrixCommission)"></span>
                    </div>
                    <p class="text-xs text-slate-400">
                        Based on <span class="font-bold text-orange-300" x-text="fmt(teamPackageAmount)"></span> package with complete <span class="font-bold text-orange-300" x-text="teamMultiplier + '×' + teamMultiplier"></span> mathematical duplication.
                    </p>
                </div>
                <div class="flex items-center gap-3 text-center">
                    <div class="bg-white/10 px-3.5 py-2 rounded-xl border border-white/10">
                        <span class="text-[10px] text-slate-300 block uppercase">1st Gen (Direct)</span>
                        <span class="text-sm font-bold text-emerald-400" x-text="fmt(getMatrixRow(0).commission)"></span>
                    </div>
                    <div class="bg-white/10 px-3.5 py-2 rounded-xl border border-white/10">
                        <span class="text-[10px] text-slate-300 block uppercase">2nd – 10th Gen</span>
                        <span class="text-sm font-bold text-orange-400" x-text="fmtAbbr(totalMatrixCommission - getMatrixRow(0).commission)"></span>
                    </div>
                </div>
            </div>

            {{-- SECTION 10: DESKTOP GENERATION TABLE (With Collapsible 4th-10th) --}}
            <div class="hidden md:block overflow-hidden border border-slate-200/80 rounded-2xl">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Generation</th>
                            <th class="py-3 px-4">Commission Rate %</th>
                            <th class="py-3 px-4">Team Members</th>
                            <th class="py-3 px-4">Total Sales Volume</th>
                            <th class="py-3 px-4 text-right">Illustrative Commission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(gen, index) in genRates" :key="'desk-' + index">
                            <tr x-show="showAllGenerations || index < 3"
                                class="hover:bg-slate-50/80 transition-colors"
                                :class="index === 0 ? 'bg-orange-50/50 font-medium' : (index < 3 ? 'bg-slate-50/30' : '')">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold"
                                          :class="index === 0 ? 'bg-orange-600 text-white' : (index < 3 ? 'bg-slate-200 text-slate-800' : 'bg-slate-100 text-slate-600')"
                                          x-text="gen.gen"></span>
                                    <span class="ml-1.5" x-text="index === 0 ? '(Direct Sponsor)' : (index < 3 ? '(Core Tier)' : '')"></span>
                                </td>
                                <td class="py-3 px-4 font-extrabold text-orange-600" x-text="gen.rate + '%'"></td>
                                <td class="py-3 px-4 font-semibold text-slate-800" x-text="getMatrixRow(index).people.toLocaleString('en-IN')"></td>
                                <td class="py-3 px-4 text-slate-600" x-text="fmt(getMatrixRow(index).volume)"></td>
                                <td class="py-3 px-4 text-right font-black text-emerald-700 text-sm" x-text="fmt(getMatrixRow(index).commission)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="p-3 bg-slate-50 border-t border-slate-200 text-center">
                    <button type="button" @click="showAllGenerations = !showAllGenerations" class="px-4 py-1.5 rounded-xl bg-white border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition-all">
                        <span x-text="showAllGenerations ? '▲ Hide Advanced Generations (4th – 10th)' : '▼ Show Advanced Generations (4th – 10th)'"></span>
                    </button>
                </div>
            </div>

            {{-- SECTION 11: MOBILE GENERATION ACCORDION CARDS (<= 767px) --}}
            <div class="block md:hidden space-y-3">
                <template x-for="(gen, index) in genRates" :key="'mob-' + index">
                    <div x-show="showAllGenerations || index < 3"
                         class="p-4 rounded-2xl border transition-all"
                         :class="index === 0 ? 'bg-orange-50/70 border-orange-200' : 'bg-white border-slate-200'">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold"
                                      :class="index === 0 ? 'bg-orange-600 text-white' : 'bg-slate-200 text-slate-800'"
                                      x-text="gen.gen + ' Generation'"></span>
                                <span class="text-[11px] text-slate-500" x-text="index === 0 ? '(Direct)' : ''"></span>
                            </div>
                            <span class="font-extrabold text-orange-600 text-sm" x-text="gen.rate + '%'"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[10px] text-slate-400 block">Team Members:</span>
                                <span class="font-bold text-slate-800" x-text="getMatrixRow(index).people.toLocaleString('en-IN')"></span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Total Volume:</span>
                                <span class="font-semibold text-slate-700 truncate block" x-text="fmt(getMatrixRow(index).volume)"></span>
                            </div>
                        </div>
                        <div class="border-t border-slate-100 mt-2.5 pt-2 flex items-center justify-between text-xs">
                            <span class="font-medium text-slate-600">Commission:</span>
                            <span class="font-black text-emerald-700 text-sm" x-text="fmt(getMatrixRow(index).commission)"></span>
                        </div>
                    </div>
                </template>

                <button type="button" @click="showAllGenerations = !showAllGenerations" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition-all">
                    <span x-text="showAllGenerations ? '▲ Hide Advanced Generations' : '▼ View All 10 Generations'"></span>
                </button>
            </div>
        </div>

        {{-- 2. SINGLE PROJECT DISTRIBUTION MODE (SECTION 12) --}}
        <div x-show="activeGenView === 'single'" class="space-y-4" x-cloak>
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="text-xs font-bold text-slate-900 block" data-en="Single Project Commission Distribution Across 10 Tiers:" data-bn="একটি প্রজেক্ট বিক্রিতে ১০ স্তরে কমিশন বণ্টন:">Single Project Commission Distribution Across 10 Tiers:</span>
                    <p class="text-xs text-slate-500">How commission is credited to 10 upline sponsor levels for any single project package.</p>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-700">Project Amount:</label>
                    <input type="number" inputmode="numeric" x-model.number="referralAmount" step="10000" class="w-36 text-sm font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-1.5 bg-white">
                </div>
            </div>

            <div class="overflow-x-auto border border-slate-200/80 rounded-2xl">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Upline Level</th>
                            <th class="py-3 px-4">Rate %</th>
                            <th class="py-3 px-4">Project Volume</th>
                            <th class="py-3 px-4 text-right">Payable Commission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(gen, index) in genRates" :key="'single-' + index">
                            <tr class="hover:bg-slate-50/80" :class="index === 0 ? 'bg-emerald-50/50' : ''">
                                <td class="py-2.5 px-4 font-bold text-slate-900">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold"
                                          :class="index === 0 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-800'"
                                          x-text="gen.gen"></span>
                                    <span class="ml-2" x-text="index === 0 ? '1st Gen (Direct Sponsor)' : (index + 1) + 'th Upline Level'"></span>
                                </td>
                                <td class="py-2.5 px-4 font-bold text-orange-600" x-text="gen.rate + '%'"></td>
                                <td class="py-2.5 px-4 text-slate-700" x-text="fmt(referralAmount)"></td>
                                <td class="py-2.5 px-4 text-right font-black text-emerald-700" x-text="fmt(Math.round(referralAmount * (gen.rate / 100)))"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 3. COMMISSION VISUAL FLOW (SECTION 13) --}}
        <div x-show="activeGenView === 'flow'" class="space-y-4" x-cloak>
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
                <span class="text-xs font-bold text-slate-900 block" data-en="How One Activation Generates Commission" data-bn="একটি অ্যাক্টিভেশন থেকে কমিশন বণ্টনের প্রবাহ">
                    How One Activation Generates Commission
                </span>
                <p class="text-xs text-slate-500 mt-0.5">Visual hierarchy showing upline distribution when a new member joins.</p>
            </div>

            <div class="p-6 bg-white border border-slate-200 rounded-2xl space-y-3">
                <div class="flex items-center gap-3 p-3 bg-orange-600 text-white rounded-xl shadow-xs">
                    <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-black">★</span>
                    <div>
                        <span class="font-bold text-sm block">New Member / Project Joins</span>
                        <span class="text-xs text-orange-100" x-text="'Project Volume: ' + fmt(referralAmount)"></span>
                    </div>
                </div>

                <div class="relative pl-6 space-y-2 border-l-2 border-dashed border-orange-300 ml-4">
                    <template x-for="(gen, index) in genRates" :key="'flow-' + index">
                        <div class="p-2.5 rounded-xl border flex items-center justify-between text-xs"
                             :class="index === 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50 border-slate-200'">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                                      :class="index === 0 ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700'"
                                      x-text="index + 1"></span>
                                <span class="font-bold text-slate-800" x-text="index === 0 ? 'Direct Sponsor (1st Gen)' : (index + 1) + 'th Upline'"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-orange-600" x-text="gen.rate + '%'"></span>
                                <span class="font-black text-emerald-700" x-text="fmt(Math.round(referralAmount * (gen.rate / 100)))"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    {{-- SECTION 14: SCENARIO COMPARISON ("Compare Scenarios") --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <span class="px-2.5 py-0.5 bg-slate-100 text-slate-800 text-[10px] font-bold rounded-md uppercase">Side-by-side</span>
                <h2 class="text-base md:text-lg font-extrabold text-slate-900 mt-1" data-en="Compare Scenarios" data-bn="প্যাকেজ তুলনা ও দৃশ্যকল্প">
                    Compare Scenarios
                </h2>
            </div>
            <span class="text-xs text-slate-500" data-en="Starter vs National vs International comparison" data-bn="স্টার্টার বনাম ন্যাশনাল বনাম ইন্টারন্যাশনাল তুলনা">
                Starter vs National vs International comparison
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            {{-- Scenario A --}}
            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                <div class="border-b border-slate-200 pb-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Scenario A</span>
                    <h3 class="font-extrabold text-sm text-slate-900">Starter Membership</h3>
                    <span class="text-lg font-black text-slate-900" x-text="fmt(10000)"></span>
                </div>
                <div class="space-y-1.5 text-slate-600">
                    <div class="flex justify-between"><span>Core Capital:</span><strong class="text-slate-800">৳0 (Fee only)</strong></div>
                    <div class="flex justify-between"><span>Weekly Return:</span><strong class="text-slate-500">None</strong></div>
                    <div class="flex justify-between"><span>Spot Commission:</span><strong class="text-emerald-600">10% (৳1,000)</strong></div>
                    <div class="flex justify-between"><span>100-Week Return:</span><strong class="text-slate-500">Affiliate based</strong></div>
                    <div class="flex justify-between"><span>Recovery:</span><strong class="text-slate-800">1 Direct Sponsor</strong></div>
                </div>
            </div>

            {{-- Scenario B --}}
            <div class="p-4 rounded-2xl border-2 border-orange-300 bg-orange-50/40 space-y-3 shadow-xs">
                <div class="border-b border-orange-200 pb-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-orange-600 uppercase">Scenario B (Popular)</span>
                        <span class="px-1.5 py-0.5 bg-orange-600 text-white text-[9px] font-bold rounded">National</span>
                    </div>
                    <h3 class="font-extrabold text-sm text-slate-900">National Project</h3>
                    <span class="text-lg font-black text-orange-600" x-text="fmt(120000)"></span>
                </div>
                <div class="space-y-1.5 text-slate-600">
                    <div class="flex justify-between"><span>Core Capital:</span><strong class="text-slate-800" x-text="fmt(100000)"></strong></div>
                    <div class="flex justify-between"><span>Weekly Return:</span><strong class="text-orange-600" x-text="fmt(1750) + '/wk'"></strong></div>
                    <div class="flex justify-between"><span>Spot Commission:</span><strong class="text-emerald-600" x-text="fmt(12000)"></strong></div>
                    <div class="flex justify-between"><span>100-Week Return:</span><strong class="text-emerald-700" x-text="fmt(175000)"></strong></div>
                    <div class="flex justify-between"><span>Capital Recovery:</span><strong class="text-orange-700">~57 Weeks</strong></div>
                </div>
            </div>

            {{-- Scenario C --}}
            <div class="p-4 rounded-2xl border border-purple-200 bg-purple-50/40 space-y-3">
                <div class="border-b border-purple-200 pb-2">
                    <span class="text-[10px] font-bold text-purple-600 uppercase">Scenario C (Enterprise)</span>
                    <h3 class="font-extrabold text-sm text-slate-900">International Store</h3>
                    <span class="text-lg font-black text-purple-700" x-text="fmt(550000)"></span>
                </div>
                <div class="space-y-1.5 text-slate-600">
                    <div class="flex justify-between"><span>Core Capital:</span><strong class="text-slate-800" x-text="fmt(500000)"></strong></div>
                    <div class="flex justify-between"><span>Weekly Return:</span><strong class="text-purple-600" x-text="fmt(10000) + '/wk'"></strong></div>
                    <div class="flex justify-between"><span>Spot Commission:</span><strong class="text-emerald-600" x-text="fmt(55000)"></strong></div>
                    <div class="flex justify-between"><span>100-Week Return:</span><strong class="text-emerald-700" x-text="fmt(1000000)"></strong></div>
                    <div class="flex justify-between"><span>Capital Recovery:</span><strong class="text-purple-700">~50 Weeks</strong></div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 26: COMPLIANCE DISCLAIMER --}}
    <div class="p-4 bg-slate-100 border border-slate-200 rounded-2xl text-xs text-slate-600 leading-relaxed flex items-start gap-2.5">
        <span class="text-base">⚖️</span>
        <div class="space-y-1">
            <span class="font-bold text-slate-800 block" data-en="Official Disclaimer & Compliance Policy" data-bn="অফিসিয়াল ডিসক্লেইমার ও কমপ্লায়েন্স পলিসি">Official Disclaimer & Compliance Policy</span>
            <p data-en="Calculations shown on this page are illustrative and based on the currently configured SBL plan. Actual earnings, commissions, qualification and withdrawals may vary according to eligibility, performance and current SBL terms. No earnings are guaranteed without qualifying commercial or team performance." data-bn="এই পৃষ্ঠায় প্রদর্শিত সমস্ত হিসাব বর্তমান SBL কনফিগার করা প্ল্যান অনুযায়ী সম্ভাব্য গাণিতিক হিসাব। প্রকৃত আয়, কমিশন ও উত্তোলন যোগ্যতা শর্তাবলী ও সক্রিয় ব্যবসায়িক ফলাফলের ওপর নির্ভরশীল। কোনো প্রকার ফিক্সড বা অলৌকিক আয়ের প্রতিশ্রুতি দেওয়া হয় না।">
                Calculations shown on this page are illustrative and based on the currently configured SBL plan. Actual earnings, commissions, qualification and withdrawals may vary according to eligibility, performance and current SBL terms. No earnings are guaranteed without qualifying commercial or team performance.
            </p>
        </div>
    </div>

    {{-- SECTION 21: STICKY MOBILE CALCULATOR SUMMARY (<= 767px) --}}
    <div x-show="!showLeadModal && !showShareModal" class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 p-3 flex md:hidden items-center justify-between shadow-2xl">
        <div class="space-y-0.5">
            <span class="text-[10px] text-slate-400 font-medium block" x-text="packageType.toUpperCase() + ' (' + fmt(packageAmount) + ')'"></span>
            <span class="text-xs font-black text-orange-600" x-text="fmt(weeklyEarning) + ' / week'"></span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="showShareModal = true" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs">
                Share
            </button>
            <button type="button" @click="openLeadModalForCalc()" class="px-4 py-2 rounded-xl bg-orange-600 text-white font-extrabold text-xs shadow-md">
                Save Lead
            </button>
        </div>
    </div>

    {{-- SHARE MODAL (SECTION 18) --}}
    <div x-show="showShareModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showShareModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-slate-900" data-en="Share Commission Estimate" data-bn="কমিশন হিসাব শেয়ার করুন">Share Commission Estimate</h3>
                <button type="button" @click="showShareModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>
            <div class="space-y-2">
                <a :href="'https://wa.me/?text=' + encodeURIComponent(getMarketingCopyText())" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 text-white font-bold text-xs flex items-center justify-center gap-2">
                    <span>💬 Share via WhatsApp</span>
                </a>
                <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.origin + '/commission?type=' + packageType + '&amount=' + packageAmount)" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 text-white font-bold text-xs flex items-center justify-center gap-2">
                    <span>📘 Share on Facebook</span>
                </a>
                <button type="button" @click="copyCalc()" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center justify-center gap-2">
                    <span x-text="copiedCalc ? '✓ Calculation Copied!' : 'Copy Summary Text'"></span>
                </button>
                <button type="button" @click="copyShareLink()" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center justify-center gap-2">
                    <span x-text="copiedLink ? '✓ Shareable Link Copied!' : 'Copy Calculator Link'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- SAVE TO LEAD MODAL (SECTION 19) --}}
    <div x-show="showLeadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showLeadModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-orange-600 text-white flex items-center justify-center font-bold text-sm">📝</span>
                    <div>
                        <h3 class="font-bold text-base text-slate-900" data-en="Save Calculation for Prospect" data-bn="প্রসপেক্টের জন্য হিসাব সংরক্ষণ">Save Calculation for Prospect</h3>
                        <span class="text-xs text-slate-500">Source: Commission Calculator</span>
                    </div>
                </div>
                <button type="button" @click="showLeadModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <div x-show="leadSuccess" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl font-bold text-center">
                ✓ Calculation & Prospect saved successfully to SBL Leads!
            </div>
            <div x-show="leadError" class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl font-bold" x-text="leadError"></div>

            <form @submit.prevent="submitLeadFromCommission" class="space-y-3 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Prospect Name *</label>
                        <input type="text" required x-model="leadForm.name" placeholder="Full Name" class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-semibold focus:border-orange-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mobile Number *</label>
                        <input type="tel" required x-model="leadForm.mobile" placeholder="017XXXXXXXX" class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-semibold focus:border-orange-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Selected Package</label>
                        <select x-model="leadForm.package" class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-semibold focus:border-orange-500">
                            <option value="Starter (৳10,000)">Starter (৳10,000)</option>
                            <option value="National (৳1,20,000)">National (৳1,20,000)</option>
                            <option value="International (৳5,50,000)">International (৳5,50,000)</option>
                            <option value="Custom Project">Custom Project</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Estimated Budget</label>
                        <input type="text" x-model="leadForm.budget" class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-semibold focus:border-orange-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Interested In</label>
                    <input type="text" x-model="leadForm.interested_in" class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-semibold focus:border-orange-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Consultation / Calculation Notes</label>
                    <textarea rows="2" x-model="leadForm.notes" placeholder="Agreed weekly return expectation, preferred follow-up timing..." class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-medium focus:border-orange-500"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="showLeadModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">Cancel</button>
                    <button type="submit" :disabled="leadSubmitting" class="px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold shadow-md flex items-center gap-2">
                        <span x-text="leadSubmitting ? 'Saving...' : 'Save Prospect Calculation'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

