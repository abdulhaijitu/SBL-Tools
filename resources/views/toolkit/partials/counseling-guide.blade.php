{{-- SBL Marketing Counseling Guide: Interactive Sales Counseling Tool --}}
<div x-data="{
    // Counseling state
    selectedChip: 'capital_return',
    activeTab: 'investor', // 'investor', 'networker', 'hybrid'
    answers: {
        goal: 'investor_goal',
        budget: 'national_budget',
        approach: 'passive_app',
        network: 'mod_net',
        priority: 'cap_prot'
    },
    showClaimsModal: false,
    showLeadModal: false,
    showShareModal: false,
    activeObjection: null,
    copiedTalkingPoints: false,
    copiedSummary: false,
    leadSubmitting: false,
    leadSuccess: false,
    leadError: '',
    leadForm: {
        name: '',
        mobile: '',
        type: 'Investor',
        package: 'National (৳1,20,000)',
        budget: '৳1,20,000',
        goal: 'Capital Return & Dropshipping',
        temperature: 'Hot',
        next_followup_date: '',
        notes: ''
    },

    // Evaluated Counseling Recommendation
    get evaluatedDirection() {
        let scores = { investor: 0, networker: 0, hybrid: 0 };

        // Chip bias
        if (['capital_return'].includes(this.selectedChip)) scores.investor += 3;
        if (['extra_income', 'team_building'].includes(this.selectedChip)) scores.networker += 3;
        if (['business', 'product_sales'].includes(this.selectedChip)) scores.hybrid += 3;

        // Answers bias
        if (this.answers.goal === 'investor_goal') scores.investor += 2;
        if (this.answers.goal === 'networker_goal') scores.networker += 2;
        if (this.answers.goal === 'hybrid_goal') scores.hybrid += 2;

        if (this.answers.budget === 'international_budget') scores.investor += 2;
        if (this.answers.budget === 'starter_budget') scores.networker += 2;
        if (this.answers.budget === 'national_budget') scores.hybrid += 2;

        if (this.answers.approach === 'passive_app') scores.investor += 2;
        if (this.answers.approach === 'active_app') scores.networker += 2;
        if (this.answers.approach === 'hybrid_app') scores.hybrid += 2;

        if (this.answers.network === 'no_net') scores.investor += 1;
        if (this.answers.network === 'yes_net') scores.networker += 2;
        if (this.answers.network === 'mod_net') scores.hybrid += 1;

        if (this.answers.priority === 'cap_prot') scores.investor += 2;
        if (this.answers.priority === 'cashflow_pri') scores.networker += 2;
        if (this.answers.priority === 'growth_pri') scores.hybrid += 2;

        let primary = 'investor';
        if (scores.networker > scores.investor && scores.networker >= scores.hybrid) {
            primary = 'networker';
        } else if (scores.hybrid >= scores.investor && scores.hybrid >= scores.networker) {
            primary = 'hybrid';
        }

        let secondary = primary === 'investor' ? 'hybrid' : (primary === 'networker' ? 'hybrid' : 'investor');

        return {
            primary: primary,
            secondary: secondary,
            scores: scores
        };
    },

    setChip(chipId) {
        this.selectedChip = chipId;
        if (chipId === 'capital_return') { this.activeTab = 'investor'; }
        else if (chipId === 'extra_income' || chipId === 'team_building') { this.activeTab = 'networker'; }
        else if (chipId === 'business' || chipId === 'product_sales') { this.activeTab = 'hybrid'; }
    },

    scrollToSection(id) {
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    copyText(text, type) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text);
        } else {
            let textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }
        if (type === 'points') {
            this.copiedTalkingPoints = true;
            setTimeout(() => this.copiedTalkingPoints = false, 2500);
        } else if (type === 'summary') {
            this.copiedSummary = true;
            setTimeout(() => this.copiedSummary = false, 2500);
        }
    },

    getTalkingPointsText() {
        let path = this.activeTab;
        let title = path === 'investor' ? 'Investor Pitch Talking Points' : (path === 'networker' ? 'Networker Pitch Talking Points' : 'Hybrid Business Pitch Talking Points');
        let lines = [title + ' (SBL Marketing Guide):\n'];
        let points = @js($counselingConfig['talkingPoints'] ?? []);
        if (points[path]) {
            points[path].forEach(pt => {
                lines.push(pt.step + '. ' + pt.text);
            });
        }
        lines.push('\nImportant: Plan-based return is subject to active operational performance and terms.');
        return lines.join('\n');
    },

    getProspectSummaryText() {
        let path = this.activeTab;
        let pathName = path === 'investor' ? 'Investor Profile' : (path === 'networker' ? 'Networker Profile' : 'Hybrid Business Builder');
        let text = '🎯 SBL Counseling Summary - ' + pathName + '\n' +
            '-----------------------------------------\n' +
            'Primary Recommendation: ' + (path === 'investor' ? 'National / International Dropshipping' : (path === 'networker' ? 'Affiliate Partner & Binary Tree' : 'Commercial Package + Active Affiliate')) + '\n' +
            'Starting Capital: ' + (path === 'investor' ? '৳1,20,000 / ৳5,50,000' : (path === 'networker' ? '৳10,000 Starter / ৳1,20,000' : '৳1,20,000 National')) + '\n' +
            'Earning Streams: ' + (path === 'investor' ? 'Weekly plan-based returns over 100 weeks + store sales' : (path === 'networker' ? '10% Spot Commission, ৳500/pair binary reward, 10-Gen UDR' : 'Weekly returns + Spot + Pair + Ranks')) + '\n' +
            'Security: Real dropshipping products, verified weekly withdrawals.\n' +
            'Explore full details: ' + window.location.origin + '/counseling';
        return text;
    },

    openLeadModal(type, pkg, budget) {
        this.leadForm.type = type || (this.activeTab === 'investor' ? 'Investor' : (this.activeTab === 'networker' ? 'Networker' : 'Hybrid'));
        if (pkg) this.leadForm.package = pkg;
        if (budget) this.leadForm.budget = budget;
        this.leadSuccess = false;
        this.leadError = '';
        this.showLeadModal = true;
    },

    async submitLead() {
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
                    lead_source_id: 1, // Default or Counseling Guide
                    lead_source_detail: 'Counseling Guide (' + this.leadForm.type + ')',
                    interest_types: [this.leadForm.type.toLowerCase(), this.leadForm.package],
                    budget: this.leadForm.budget,
                    temperature: this.leadForm.temperature,
                    followup_date: this.leadForm.next_followup_date,
                    notes: 'Primary Goal: ' + this.leadForm.goal + ' | ' + this.leadForm.notes
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
}" class="space-y-6 pb-20 md:pb-8">

    {{-- SECTION 01: COMPACT HEADER --}}
    <div class="relative overflow-hidden bg-slate-900 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-5 md:p-8 text-white shadow-xl border border-slate-700/60">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-orange-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-500/20 text-orange-300 border border-orange-500/30 text-xs font-bold uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                        <span data-en="Sales Counseling Tool • Step 1" data-bn="কাউন্সেলিং টুল • ধাপ ১">Sales Counseling Tool • Step 1</span>
                    </span>
                    <span class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 border border-slate-700 text-xs font-medium" data-en="Interactive Prospect Profiler" data-bn="ইন্টারেক্টিভ প্রসপেক্ট প্রোফাইলার">Interactive Prospect Profiler</span>
                </div>
                <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight text-white" data-en="Investor vs Networker Counseling Guide" data-bn="ইনভেস্টর বনাম নেটওয়ার্কার কাউন্সেলিং গাইড">
                    Investor vs Networker Counseling Guide
                </h1>
                <p class="text-xs md:text-sm text-slate-300 leading-relaxed max-w-xl" data-en="Identify prospect motivation instantly, present verified opportunities with zero false claims, and transition smoothly into qualified follow-up." data-bn="প্রসপেক্টের চাহিদা ও মনস্তত্ত্ব দ্রুত শনাক্ত করুন, কোনো প্রকার বিভ্রান্তিকর তথ্য ছাড়া সঠিক প্ল্যান উপস্থাপন করুন এবং লিড সংরক্ষণ করুন।">
                    Identify prospect motivation instantly, present verified opportunities with zero false claims, and transition smoothly into qualified follow-up.
                </p>
            </div>

            {{-- Guide Navigation Bar --}}
            <div class="flex flex-wrap md:flex-col items-stretch gap-2 shrink-0">
                <button type="button" @click="scrollToSection('discovery-section')" class="px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span data-en="Quick Discovery (5 Qs)" data-bn="কুইক ডিসকভারি (৫ প্রশ্ন)">Quick Discovery (5 Qs)</span>
                </button>
                <div class="flex items-center gap-2">
                    <a href="{{ route('packages.index') }}" class="flex-1 px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition-all text-center" data-en="Packages" data-bn="প্যাকেজ">Packages</a>
                    <a href="{{ route('commission.index') }}" class="flex-1 px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition-all text-center" data-en="Calculator" data-bn="ক্যালকুলেটর">Calculator</a>
                    <a href="{{ route('ranks.index') }}" class="flex-1 px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition-all text-center" data-en="Ranks" data-bn="র‍্যাংক">Ranks</a>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 11: COUNSELING FLOW STEPPER (6 Steps) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <div class="flex items-center justify-between gap-2 overflow-x-auto pb-1 text-xs">
            @foreach($counselingConfig['steps'] ?? [] as $step)
                <div class="flex items-center gap-2 shrink-0 cursor-pointer group" @click="scrollToSection('{{ $step['id'] === 'discover' ? 'discovery-section' : ($step['id'] === 'identify' ? 'selector-section' : ($step['id'] === 'explain' ? 'cards-section' : ($step['id'] === 'show' ? 'talking-points-section' : ($step['id'] === 'ask' ? 'objections-section' : 'lead-trigger')))) }}')">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center font-extrabold text-xs transition-colors {{ $step['num'] === 1 ? 'bg-orange-600 text-white' : 'bg-slate-100 group-hover:bg-orange-100 text-slate-700 group-hover:text-orange-700' }}">
                        {{ $step['num'] }}
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 block group-hover:text-orange-600 transition-colors" data-en="{{ $step['name'] }}" data-bn="{{ $step['name_bn'] }}">{{ $step['name'] }}</span>
                        <span class="text-[10px] text-slate-400 hidden lg:inline" data-en="{{ $step['desc'] }}" data-bn="{{ $step['desc_bn'] }}">{{ $step['desc'] }}</span>
                    </div>
                    @if(!$loop->last)
                        <svg class="w-3.5 h-3.5 text-slate-300 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- SECTION 02: PROSPECT TYPE SELECTOR (Chips) --}}
    <div id="selector-section" class="bg-white rounded-2xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-orange-600" data-en="Step 2 • Motivation Mapping" data-bn="ধাপ ২ • প্রসপেক্ট মোটিভেশন ম্যাপিং">Step 2 • Motivation Mapping</span>
                <h2 class="text-base md:text-lg font-bold text-slate-900" data-en="What is this prospect mainly looking for?" data-bn="প্রসপেক্ট মূলত কী খুঁজছেন বা কোনটিতে আগ্রহী?">
                    What is this prospect mainly looking for?
                </h2>
            </div>
            <span class="text-xs text-slate-500" data-en="Select a chip to immediately highlight matching path" data-bn="যেকোনো একটি অপশন ক্লিক করে সঠিক পথ হাইলাইট করুন">
                Select a chip to highlight matching path
            </span>
        </div>

        <div class="flex flex-wrap gap-2.5">
            @foreach($counselingConfig['prospectChips'] ?? [] as $chip)
                <button type="button" @click="setChip('{{ $chip['id'] }}')"
                        :class="selectedChip === '{{ $chip['id'] }}' ? 'bg-orange-600 text-white ring-2 ring-orange-500 ring-offset-1 font-bold shadow-sm' : 'bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-medium'"
                        class="px-4 py-2.5 rounded-xl text-xs md:text-sm transition-all flex items-center gap-2 cursor-pointer">
                    <span>
                        @if($chip['id'] === 'capital_return') 💰
                        @elseif($chip['id'] === 'extra_income') 📈
                        @elseif($chip['id'] === 'business') 🏢
                        @elseif($chip['id'] === 'team_building') 👥
                        @elseif($chip['id'] === 'product_sales') 🛍️
                        @else ❓
                        @endif
                    </span>
                    <span data-en="{{ $chip['label_en'] }}" data-bn="{{ $chip['label_bn'] }}">{{ $chip['label_en'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- SECTION 07: QUICK DISCOVERY QUESTIONS (Max 5) --}}
    <div id="discovery-section" class="bg-gradient-to-br from-slate-50 via-white to-orange-50/30 rounded-2xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200/60 pb-3">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-orange-600" data-en="Discovery Questionnaire" data-bn="ডিসকভারি প্রশ্নাবলী">Discovery Questionnaire</span>
                <h3 class="text-base md:text-lg font-bold text-slate-900" data-en="Ask These 5 First (Counseling Script)" data-bn="কাউন্সেলিং শুরুর ৫টি মূল প্রশ্ন">
                    Ask These 5 First (Counseling Script)
                </h3>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-lg bg-orange-100 text-orange-800 font-semibold" data-en="Auto Evaluates Suggested Direction" data-bn="স্বয়ংক্রিয়ভাবে পথ সুপারিশ করে">
                Auto Evaluates Suggested Direction
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($counselingConfig['discoveryQuestions'] ?? [] as $q)
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-2.5 {{ $loop->last ? 'md:col-span-2 lg:col-span-1' : '' }}">
                    <h4 class="text-xs font-bold text-slate-800 leading-snug" data-en="{{ $q['q_en'] }}" data-bn="{{ $q['q_bn'] }}">
                        {{ $q['q_en'] }}
                    </h4>
                    <div class="space-y-1.5">
                        @foreach($q['options'] as $opt)
                            <label class="flex items-center gap-2.5 p-2 rounded-lg border text-xs cursor-pointer transition-colors"
                                   :class="answers['{{ $q['id'] }}'] === '{{ $opt['id'] }}' ? 'border-orange-500 bg-orange-50/70 text-orange-900 font-bold' : 'border-slate-100 hover:bg-slate-50 text-slate-700'">
                                <input type="radio" name="q_{{ $q['id'] }}" value="{{ $opt['id'] }}" x-model="answers['{{ $q['id'] }}']" class="text-orange-600 focus:ring-orange-500 w-3.5 h-3.5">
                                <span class="leading-tight" data-en="{{ $opt['label_en'] }}" data-bn="{{ $opt['label_bn'] }}">{{ $opt['label_en'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- SECTION 08: AUTO COUNSELING RESULT ("Suggested Counseling Direction") --}}
        <div class="bg-slate-900 rounded-2xl p-5 text-white flex flex-col md:flex-row items-center justify-between gap-4 border border-slate-800 shadow-md">
            <div class="space-y-1 text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider" data-en="Suggested Counseling Direction" data-bn="প্রস্তাবিত কাউন্সেলিং নির্দেশনা">
                        Suggested Counseling Direction
                    </span>
                </div>
                <div class="text-lg md:text-xl font-extrabold flex items-center justify-center md:justify-start gap-2">
                    <span data-en="Recommended Primary Path:" data-bn="মূল প্রস্তাবিত পথ:">Recommended Primary Path:</span>
                    <span class="text-orange-400 uppercase tracking-wide underline underline-offset-4"
                          x-text="evaluatedDirection.primary === 'investor' ? 'Investor Mindset' : (evaluatedDirection.primary === 'networker' ? 'Networker Mindset' : 'Hybrid Business Builder')"></span>
                </div>
                <p class="text-xs text-slate-400" data-en="Subject to qualification. This is an objective alignment tool, not guaranteed financial advice." data-bn="এটি প্রসপেক্টের সাথে সামঞ্জস্যপূর্ণ পথ খোঁজার টুল, কোনো আর্থিক গ্যারান্টি বা প্রতিশ্রুতি নয়।">
                    Subject to qualification. This is an objective alignment tool, not guaranteed financial advice.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="activeTab = evaluatedDirection.primary; scrollToSection('cards-section')" class="px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-md transition-all">
                    <span data-en="Focus This Path" data-bn="এই পথটি দেখুন">Focus This Path</span> &rarr;
                </button>
                <button type="button" @click="openLeadModal(evaluatedDirection.primary)" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs transition-all">
                    <span data-en="Save Lead" data-bn="লিড সেভ করুন">Save Lead</span>
                </button>
            </div>
        </div>
    </div>

    {{-- SECTION 16: MOBILE SEGMENTED TABS (<= 767px) --}}
    <div class="block md:hidden bg-slate-200/80 p-1 rounded-2xl flex items-center text-xs font-bold">
        <button type="button" @click="activeTab = 'investor'" :class="activeTab === 'investor' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-700'" class="flex-1 py-2.5 rounded-xl transition-all text-center">
            💼 Investor
        </button>
        <button type="button" @click="activeTab = 'networker'" :class="activeTab === 'networker' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-700'" class="flex-1 py-2.5 rounded-xl transition-all text-center">
            🚀 Networker
        </button>
        <button type="button" @click="activeTab = 'hybrid'" :class="activeTab === 'hybrid' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-700'" class="flex-1 py-2.5 rounded-xl transition-all text-center">
            ⚡ Hybrid
        </button>
    </div>

    {{-- SECTION 03, 04, 05, 06: 3 PATH CARDS (Investor, Networker, Hybrid) --}}
    <div id="cards-section" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        {{-- 1. INVESTOR CARD (Warm Orange Accent) --}}
        <div x-show="window.innerWidth >= 768 || activeTab === 'investor'"
             :class="activeTab === 'investor' ? 'ring-2 ring-orange-500 shadow-lg' : 'opacity-90'"
             class="bg-white rounded-3xl border-2 border-orange-200 overflow-hidden flex flex-col justify-between transition-all">
            <div class="p-5 md:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-orange-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-10 h-10 rounded-2xl bg-orange-600 text-white font-extrabold flex items-center justify-center text-base shadow-sm">I1</span>
                        <div>
                            <h3 class="font-extrabold text-base text-slate-900" data-en="Investor Mindset" data-bn="ইনভেস্টর মাইন্ডসেট">Investor Mindset</h3>
                            <span class="text-xs text-orange-700 font-semibold" data-en="Capital Deployment & Steady Earning" data-bn="মূলধন বিনিয়োগ ও নিয়মিত রিটার্ন">Capital Deployment & Steady Earning</span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md bg-orange-100 text-orange-800 text-[10px] font-bold">Passive Model</span>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed" data-en="Primary interest: capital exposure, weekly return structure, capital recovery timeline, and transparent dropshipping backing." data-bn="প্রধান লক্ষ্য: মূলধন অংশগ্রহণ, সাপ্তাহিক রিটার্ন কাঠামো, মূলধন রিকভারি সময়সীমা ও স্বচ্ছ ড্রপশিপিং নিশ্চয়তা।">
                    Primary interest: capital exposure, weekly return structure, capital recovery timeline, and transparent dropshipping backing.
                </p>

                {{-- 6 Structured Items --}}
                <div class="space-y-2.5 text-xs">
                    <div class="p-3 bg-orange-50/50 rounded-xl border border-orange-100">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="1. Capital Requirement" data-bn="১. প্রয়োজনীয় বাজেট ও প্রজেক্ট">1. Capital Requirement</span>
                        <span class="text-slate-600" data-en="National: ৳1,20,000 (৳1L Capital + ৳20k Fee) • International: ৳5,50,000 (৳5L Capital + ৳50k Fee)." data-bn="ন্যাশনাল: ১,২০,০০০ টাকা (১ লাখ মূলধন + ২০ হাজার ফি) • ইন্টারন্যাশনাল: ৫,৫০,০০০ টাকা (৫ লাখ মূলধন + ৫০ হাজার ফি)।">
                            National: ৳1,20,000 (৳1L Capital + ৳20k Fee) • International: ৳5,50,000 (৳5L Capital + ৳50k Fee).
                        </span>
                    </div>

                    <div class="p-3 bg-orange-50/50 rounded-xl border border-orange-100">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="2. Return Structure" data-bn="২. রিটার্ন কাঠামো (১০০ সপ্তাহ)">2. Return Structure</span>
                        <span class="text-slate-600" data-en="Plan-based weekly return: 1.75% (৳1,750/wk) for National; 2.0% (৳10,000/wk) for International across 100 weeks." data-bn="প্ল্যান অনুযায়ী সাপ্তাহিক রিটার্ন: ন্যাশনাল ১.৭৫% (১,৭৫০ টাকা/সপ্তাহ); ইন্টারন্যাশনাল ২.০% (১০,০০০ টাকা/সপ্তাহ) ১০০ সপ্তাহ পর্যন্ত।">
                            Plan-based weekly return: 1.75% (৳1,750/wk) for National; 2.0% (৳10,000/wk) for International across 100 weeks.
                        </span>
                    </div>

                    <div class="p-3 bg-orange-50/50 rounded-xl border border-orange-100">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="3. Capital Recovery" data-bn="৩. মূলধন রিকভারি সময়সীমা">3. Capital Recovery</span>
                        <span class="text-slate-600" data-en="Core capital recovered in ~50-57 weeks depending on configured package and active operational cycle." data-bn="প্যাকেজ ও স্বাভাবিক ব্যবসায়িক গতি অনুযায়ী আনুমানিক ৫০-৫৭ সপ্তাহে মূলধন উঠে আসে।">
                            Core capital recovered in ~50-57 weeks depending on configured package and active operational cycle.
                        </span>
                    </div>

                    <div class="p-3 bg-orange-50/50 rounded-xl border border-orange-100">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="4. Withdrawal Policy" data-bn="৪. উত্তোলন পদ্ধতি ও ওয়ালেট">4. Withdrawal Policy</span>
                        <span class="text-slate-600" data-en="Credited weekly to SBL wallet. Withdrawable via verified Bank account or MFS (bKash/Nagad) on scheduled dates." data-bn="সাপ্তাহিক উপার্জিত টাকা ওয়ালেটে জমা হয় এবং ব্যাংক বা বিকাশ/নগদে নিয়মিত উইথড্র করা যায়।">
                            Credited weekly to SBL wallet. Withdrawable via verified Bank account or MFS (bKash/Nagad) on scheduled dates.
                        </span>
                    </div>

                    <div class="p-3 bg-orange-50/50 rounded-xl border border-orange-100">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="5. Business & Product Basis" data-bn="৫. বাস্তব পণ্যের ব্যবসা ও স্টোর">5. Business & Product Basis</span>
                        <span class="text-slate-600" data-en="Real domestic & global dropshipping e-commerce catalog, live Shopify platform, and managed digital marketing." data-bn="প্রকৃত ই-কমার্স পণ্য, লাইভ শপিফাই স্টোর সেটআপ এবং দক্ষ টিম দ্বারা পরিচালিত ডিজিটাল অ্যাড ক্যাম্পেইন।">
                            Real domestic & global dropshipping e-commerce catalog, live Shopify platform, and managed digital marketing.
                        </span>
                    </div>

                    <div class="p-3 bg-orange-100/60 rounded-xl border border-orange-200">
                        <span class="font-bold text-orange-950 block mb-0.5" data-en="6. Investment Considerations" data-bn="৬. ঝুঁকি ও ব্যবসায়িক বাস্তবতা">6. Investment Considerations</span>
                        <span class="text-orange-900 text-[11px]" data-en="E-commerce operations are subject to market demand and logistics. Returns are plan-based, not fixed guaranteed bank interest." data-bn="এটি বাণিজ্যিক ব্যবসা, কোনো ফিক্সড ব্যাংক ডিপোজিট নয়। সেলস ও বিজ্ঞাপনের ওপর ভিত্তি করে প্ল্যান অনুযায়ী রিটার্ন বণ্টিত হয়।">
                            E-commerce operations are subject to market demand and logistics. Returns are plan-based, not fixed guaranteed bank interest.
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-orange-50/80 border-t border-orange-100 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('packages.index') }}" class="px-3 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs text-center transition-all shadow-xs" data-en="View Packages" data-bn="প্যাকেজ দেখুন">View Packages</a>
                    <a href="{{ route('commission.index') }}?type=national" class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-800 border border-slate-300 font-bold text-xs text-center transition-all" data-en="ROI Calculator" data-bn="ROI ক্যালকুলেটর">ROI Calculator</a>
                </div>
                <button type="button" @click="openLeadModal('Investor', 'National (৳1,20,000)', '৳1,20,000')" class="w-full py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all" data-en="Save as Investor Lead" data-bn="ইনভেস্টর লিড সংরক্ষণ">Save as Investor Lead</button>
            </div>
        </div>

        {{-- 2. NETWORKER CARD (Purple Accent) --}}
        <div x-show="window.innerWidth >= 768 || activeTab === 'networker'"
             :class="activeTab === 'networker' ? 'ring-2 ring-purple-500 shadow-lg' : 'opacity-90'"
             class="bg-white rounded-3xl border-2 border-purple-200 overflow-hidden flex flex-col justify-between transition-all">
            <div class="p-5 md:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-purple-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-10 h-10 rounded-2xl bg-purple-600 text-white font-extrabold flex items-center justify-center text-base shadow-sm">N1</span>
                        <div>
                            <h3 class="font-extrabold text-base text-slate-900" data-en="Networker Mindset" data-bn="নেটওয়ার্কার মাইন্ডসেট">Networker Mindset</h3>
                            <span class="text-xs text-purple-700 font-semibold" data-en="Active Team Growth & Performance Bonuses" data-bn="সক্রিয় টিম গঠন ও আকর্ষণীয় বোনাস">Active Team Growth & Performance Bonuses</span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 text-[10px] font-bold">Active Affiliate</span>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed" data-en="Primary interest: direct sales, affiliate sponsor bonuses, dual-team binary matching rewards, and career rank achievements." data-bn="প্রধান লক্ষ্য: ডিরেক্ট সেলস, তাৎক্ষণিক রেফারেল কমিশন, লেফট-রাইট পেয়ার ম্যাচিং বোনাস ও দীর্ঘমেয়াদি র‍্যাংক প্রাইজমানি।">
                    Primary interest: direct sales, affiliate sponsor bonuses, dual-team binary matching rewards, and career rank achievements.
                </p>

                {{-- 6 Structured Items --}}
                <div class="space-y-2.5 text-xs">
                    <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="1. Direct Referral (Spot Commission)" data-bn="১. ডিরেক্ট স্পট কমিশন">1. Direct Referral (Spot Commission)</span>
                        <span class="text-slate-600" data-en="10% instant cash commission on any referred project or membership package upon activation." data-bn="যেকোনো প্যাকেজ রেফার করলেই তাৎক্ষণিক ১০% নগদ স্পট কমিশন ওয়ালেটে জমা হয়।">
                            10% instant cash commission on any referred project or membership package upon activation.
                        </span>
                    </div>

                    <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="2. Pair Matching Reward" data-bn="২. পেয়ার ম্যাচিং রিওয়ার্ড">2. Pair Matching Reward</span>
                        <span class="text-slate-600" data-en="৳500 per binary pair (1 Left : 1 Right). Configured daily limit up to 100 pairs (৳50,000 maximum daily cap)." data-bn="প্রতি বাইনারি পেয়ারে ৫০০ টাকা। স্থায়িত্ব রক্ষায় দৈনিক সর্বোচ্চ ১০০ পেয়ার বা ৫০,০০০ টাকা ক্যাপিং।">
                            ৳500 per binary pair (1 Left : 1 Right). Configured daily limit up to 100 pairs (৳50,000 maximum daily cap).
                        </span>
                    </div>

                    <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="3. 10-Generation UDR Commission" data-bn="৩. ১০ প্রজন্মের UDR কমিশন">3. 10-Generation UDR Commission</span>
                        <span class="text-slate-600" data-en="Multi-tier overrides: 10% on Gen 1, 2% on Gen 2, 1% on Gen 3-4, 0.5% on Gen 5, and 0.1% on Gen 6-10." data-bn="১০ প্রজন্ম পর্যন্ত টিম কমিশন: ১ম-এ ১০%, ২য়-তে ২%, ৩য়-৪র্থ-তে ১%, ৫ম-তে ০.৫%, এবং ৬ষ্ঠ-১০ম-তে ০.১%।">
                            Multi-tier overrides: 10% on Gen 1, 2% on Gen 2, 1% on Gen 3-4, 0.5% on Gen 5, and 0.1% on Gen 6-10.
                        </span>
                    </div>

                    <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="4. Career Rank Milestone Rewards" data-bn="৪. ক্যারিয়ার র‍্যাংক রিওয়ার্ড">4. Career Rank Milestone Rewards</span>
                        <span class="text-slate-600" data-en="6 verified progressive ranks offering up to ৳36,55,000+ total cumulative cash prizes from FME to ETD." data-bn="FME (৫,০০০ টাকা) থেকে শুরু করে ETD (২০,০০,০০০ টাকা) পর্যন্ত মোট ৬টি স্বীকৃত র‍্যাংক প্রাইজমানি।">
                            6 verified progressive ranks offering up to ৳36,55,000+ total cumulative cash prizes from FME to ETD.
                        </span>
                    </div>

                    <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="5. Sales & Placement Flexibility" data-bn="৫. প্লেসমেন্ট ও মেম্বারশিপ">5. Sales & Placement Flexibility</span>
                        <span class="text-slate-600" data-en="Accessible starting from Starter Membership (৳10,000) with binary tree placement and free Facebook page setup." data-bn="মাত্র ১০,০০০ টাকার স্টার্টার মেম্বারশিপ দিয়ে বাইনারি পজিশন ও ফ্রি পেজ নিয়ে কাজ শুরু করার সুবিধা।">
                            Accessible starting from Starter Membership (৳10,000) with binary tree placement and free Facebook page setup.
                        </span>
                    </div>

                    <div class="p-3 bg-purple-100/60 rounded-xl border border-purple-200">
                        <span class="font-bold text-purple-950 block mb-0.5" data-en="6. Team Development Reality" data-bn="৬. পারফরম্যান্স নির্ভরতা">6. Team Development Reality</span>
                        <span class="text-purple-900 text-[11px]" data-en="Team rewards require active balanced development of both Left and Right legs; not a passive payout." data-bn="পেয়ার বোনাস ও র‍্যাংক অর্জন করতে লেফট ও রাইট উভয় টিমের সক্রিয় ব্যালেন্স ও মেম্বারশিপ নিশ্চিত করতে হয়।">
                            Team rewards require active balanced development of both Left and Right legs; not a passive payout.
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-purple-50/80 border-t border-purple-100 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('ranks.index') }}" class="px-3 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs text-center transition-all shadow-xs" data-en="View Ranks" data-bn="র‍্যাংক দেখুন">View Ranks</a>
                    <a href="{{ route('commission.index') }}" class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-800 border border-slate-300 font-bold text-xs text-center transition-all" data-en="Commission Plan" data-bn="মার্কেটিং প্ল্যান">Commission Plan</a>
                </div>
                <button type="button" @click="openLeadModal('Networker', 'Starter (৳10,000)', '৳10,000')" class="w-full py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all" data-en="Save as Networker Lead" data-bn="নেটওয়ার্কার লিড সংরক্ষণ">Save as Networker Lead</button>
            </div>
        </div>

        {{-- 3. HYBRID CARD (SBL Emerald / Slate Accent) --}}
        <div x-show="window.innerWidth >= 768 || activeTab === 'hybrid'"
             :class="activeTab === 'hybrid' ? 'ring-2 ring-emerald-500 shadow-lg' : 'opacity-90'"
             class="bg-white rounded-3xl border-2 border-slate-300 overflow-hidden flex flex-col justify-between transition-all md:col-span-2 lg:col-span-1">
            <div class="p-5 md:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-10 h-10 rounded-2xl bg-slate-900 text-white font-extrabold flex items-center justify-center text-base shadow-sm">H1</span>
                        <div>
                            <h3 class="font-extrabold text-base text-slate-900" data-en="Hybrid Business Builder" data-bn="হাইব্রিড বিজনেস বিল্ডার">Hybrid Business Builder</h3>
                            <span class="text-xs text-emerald-700 font-semibold" data-en="Capital Security + Active Team Growth" data-bn="মূলধনী আয় + সক্রিয় টিম সম্প্রসারণ">Capital Security + Active Team Growth</span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-bold">Recommended</span>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed" data-en="Suitable for prospects interested in both capital-based weekly returns and active business/network income opportunities." data-bn="যারা একই সাথে ড্রপশিপিং ইনভেস্টমেন্ট থেকে সাপ্তাহিক ক্যাশ ফ্লো চান এবং রেফারেল ও টিম তৈরি করে বড় আয় করতে চান।">
                    Suitable for prospects interested in both capital-based weekly returns and active business/network income opportunities.
                </p>

                {{-- 4 Concise Key Points --}}
                <div class="space-y-3 text-xs">
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="1. Foundational Commercial Package" data-bn="১. ফাউন্ডেশন কমার্শিয়াল প্যাকেজ">1. Foundational Commercial Package</span>
                        <span class="text-slate-600" data-en="Start with National (৳1,20,000) or Starter (৳10,000) to secure top binary positioning and immediate dropshipping operations." data-bn="ন্যাশনাল (১.২ লাখ) প্যাকেজ নিয়ে শুরু করুন যা ড্রপশিপিংয়ের পাশাপাশি বাইনারি ট্রির একদম শীর্ষে শক্ত অবস্থান তৈরি করে।">
                            Start with National (৳1,20,000) or Starter (৳10,000) to secure top binary positioning and immediate dropshipping operations.
                        </span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="2. Dual Cash Flow Streams" data-bn="২. যৌথ ক্যাশ ফ্লো ধারা">2. Dual Cash Flow Streams</span>
                        <span class="text-slate-600" data-en="Enjoy 1.75% weekly returns (৳1,750/wk) while sponsoring associates for instant 10% spot commissions (৳12,000 per National direct)." data-bn="সাপ্তাহিক ১.৭৫% নিশ্চিত রিটার্ন উপভোগের পাশাপাশি প্রতিটি ডিরেক্ট রেফারেল থেকে ১০% স্পট ক্যাশ কমিশন অর্জন।">
                            Enjoy 1.75% weekly returns (৳1,750/wk) while sponsoring associates for instant 10% spot commissions (৳12,000 per National direct).
                        </span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="font-bold text-slate-900 block mb-0.5" data-en="3. Team Opportunities & Ranks" data-bn="৩. টিম অপরচুনিটি ও র‍্যাংক">3. Team Opportunities & Ranks</span>
                        <span class="text-slate-600" data-en="Introduce 10 direct partners (5 Left + 5 Right) to unlock the FME Rank milestone bonus (৳5,000) and accelerate pair bonuses." data-bn="১০ জন ডিরেক্ট পার্টনার যুক্ত করে FME র‍্যাংক (৫,০০০ টাকা নগদ) অর্জন ও পেয়ার ম্যাচিং বোনাস বেগবান করা।">
                            Introduce 10 direct partners (5 Left + 5 Right) to unlock the FME Rank milestone bonus (৳5,000) and accelerate pair bonuses.
                        </span>
                    </div>

                    <div class="p-3.5 bg-emerald-50/70 rounded-xl border border-emerald-200">
                        <span class="font-bold text-emerald-950 block mb-0.5" data-en="4. Safe Capital Recovery First" data-bn="৪. আগে মূলধন রিকভারি কৌশল">4. Safe Capital Recovery First</span>
                        <span class="text-emerald-900 text-[11px]" data-en="Counsel the prospect to recover core capital within the first ~50 weeks before aggressive network expansion." data-bn="প্রথমে দ্রুত মূলধন নিরাপদ করার পরামর্শ দিন, এরপর অর্জিত প্রফিট ও কমিশন দিয়ে নেটওয়ার্ক আরও বড় করুন।">
                            Counsel the prospect to recover core capital within the first ~50 weeks before aggressive network expansion.
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-slate-50 border-t border-slate-200 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('packages.index') }}" class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs text-center transition-all shadow-xs" data-en="View Packages" data-bn="প্যাকেজ দেখুন">View Packages</a>
                    <a href="{{ route('ranks.index') }}" class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-800 border border-slate-300 font-bold text-xs text-center transition-all" data-en="View Ranks" data-bn="র‍্যাংক দেখুন">View Ranks</a>
                </div>
                <button type="button" @click="openLeadModal('Hybrid', 'National (৳1,20,000)', '৳1,20,000')" class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all shadow-xs" data-en="Save as Hybrid Lead" data-bn="হাইব্রিড লিড সংরক্ষণ">Save as Hybrid Lead</button>
            </div>
        </div>

    </div>

    {{-- SECTION 09: SUGGESTED TALKING POINTS & COPY BUTTON --}}
    <div id="talking-points-section" class="bg-white rounded-2xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-orange-600" data-en="Counseling Talking Points" data-bn="কাউন্সেলিং টকিং পয়েন্ট">Counseling Talking Points</span>
                <h3 class="text-base md:text-lg font-bold text-slate-900" data-en="Script & Talking Points for Active Counseling" data-bn="ফোনে বা মিটিংয়ে আলোচনার মূল পয়েন্টসমূহ">
                    Script & Talking Points for Active Counseling
                </h3>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="copyText(getTalkingPointsText(), 'points')" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                    <span x-text="copiedTalkingPoints ? 'Copied to Clipboard!' : 'Copy Talking Points'"></span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <template x-for="pt in (@js($counselingConfig['talkingPoints'] ?? []))[activeTab] || []" :key="pt.step">
                <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-200 text-xs space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-orange-600 text-white font-bold flex items-center justify-center text-[10px]" x-text="pt.step"></span>
                        <span class="font-bold text-slate-800" data-en="Talking Point" data-bn="আলোচনার বিষয়">Talking Point</span>
                    </div>
                    <p class="text-slate-600 leading-relaxed" x-text="pt.text"></p>
                </div>
            </template>
        </div>
    </div>

    {{-- SECTION 10: WHAT NOT TO SAY ("Avoid These Claims" vs "Use Instead") --}}
    <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-5 md:p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-sm">⚠️</span>
                <div>
                    <h3 class="font-bold text-sm md:text-base text-amber-950" data-en="Avoid These Claims (Compliance & Safe Marketing)" data-bn="যেসব দাবি সম্পূর্ণ পরিহার করবেন (নিরাপদ মার্কেটিং)">
                        Avoid These Claims (Compliance & Safe Marketing)
                    </h3>
                    <p class="text-xs text-amber-800" data-en="Protect prospect trust and adhere strictly to verified SBL business terminology." data-bn="প্রসপেক্টের আস্থা ও SBL পলিসির মর্যাদা রক্ষায় সঠিক শব্দচয়ন ব্যবহার করুন।">
                        Protect prospect trust and adhere strictly to verified SBL business terminology.
                    </p>
                </div>
            </div>
            <button type="button" @click="showClaimsModal = !showClaimsModal" class="text-xs font-bold text-amber-900 underline">
                <span x-text="showClaimsModal ? 'Collapse' : 'Expand All'"></span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs" x-show="showClaimsModal || true">
            @foreach($counselingConfig['claimsToAvoid'] ?? [] as $claim)
                <div class="p-3 bg-white rounded-xl border border-amber-200 space-y-1.5">
                    <div class="flex items-start gap-2 text-rose-700 font-semibold">
                        <span class="font-bold text-rose-600">✕ Avoid:</span>
                        <span data-en="{{ $claim['avoid'] }}" data-bn="{{ $claim['avoid_bn'] }}">{{ $claim['avoid'] }}</span>
                    </div>
                    <div class="flex items-start gap-2 text-emerald-800 font-bold border-t border-slate-100 pt-1">
                        <span class="text-emerald-600">✓ Use Instead:</span>
                        <span data-en="{{ $claim['use'] }}" data-bn="{{ $claim['use_bn'] }}">{{ $claim['use'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- SECTION 11: OBJECTION HANDLING (10-Item FAQ Accordion) --}}
    <div id="objections-section" class="bg-white rounded-2xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-4">
        <div class="border-b border-slate-100 pb-3">
            <span class="text-[11px] font-bold uppercase tracking-wider text-orange-600" data-en="Objection Handling" data-bn="আপত্তি ও প্রশ্ন সমাধান">Objection Handling</span>
            <h3 class="text-base md:text-lg font-bold text-slate-900" data-en="10 Common Prospect Questions & Verified Answers" data-bn="প্রসপেক্টের ১০টি সাধারণ প্রশ্ন ও বাস্তব উত্তর">
                10 Common Prospect Questions & Verified Answers
            </h3>
            <p class="text-xs text-slate-500" data-en="Direct answers sourced from official SBL operating framework." data-bn="SBL অফিশিয়াল নীতিমালা অনুযায়ী তৈরি নির্ভরযোগ্য উত্তরসমূহ।">
                Direct answers sourced from official SBL operating framework.
            </p>
        </div>

        <div class="space-y-2.5">
            @foreach($counselingConfig['objections'] ?? [] as $idx => $obj)
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <button type="button" @click="activeObjection = activeObjection === {{ $idx }} ? null : {{ $idx }}"
                            class="w-full p-3.5 text-left bg-slate-50 hover:bg-slate-100 flex items-center justify-between text-xs md:text-sm font-bold text-slate-800 transition-colors">
                        <span class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-orange-100 text-orange-800 text-[11px] font-bold flex items-center justify-center">{{ $idx + 1 }}</span>
                            <span data-en="{{ $obj['q_en'] }}" data-bn="{{ $obj['q_bn'] }}">{{ $obj['q_en'] }}</span>
                        </span>
                        <svg class="w-4 h-4 text-slate-400 transform transition-transform" :class="activeObjection === {{ $idx }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div x-show="activeObjection === {{ $idx }}" x-collapse class="p-4 bg-white text-xs text-slate-600 leading-relaxed border-t border-slate-200">
                        <p data-en="{{ $obj['a_en'] }}" data-bn="{{ $obj['a_bn'] }}">{{ $obj['a_en'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- SECTION 13: RECOMMENDED NEXT STEP (Contextual Actions) --}}
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white rounded-2xl p-5 md:p-6 space-y-4 border border-slate-700 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-xs font-bold text-orange-400 uppercase tracking-wider" data-en="Recommended Next Step" data-bn="পরবর্তী করণীয় পদক্ষেপ">Recommended Next Step</span>
                <h3 class="text-base md:text-lg font-bold text-white mt-0.5" data-en="Transition Prospect to Next Logical Tool" data-bn="প্রসপেক্টকে পরবর্তী প্রয়োজনীয় টুলে নিয়ে যান">
                    Transition Prospect to Next Logical Tool
                </h3>
            </div>
            <span class="text-xs text-slate-300" data-en="Contextual to selected profile" data-bn="নির্বাচিত প্রোফাইলের সাথে সামঞ্জস্যপূর্ণ">
                Contextual to selected profile
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <a href="{{ route('packages.index') }}" class="p-3.5 bg-white/10 hover:bg-white/15 rounded-xl border border-white/10 text-white text-xs space-y-1 transition-all">
                <span class="font-bold block text-sm">📦 Show Packages</span>
                <span class="text-[11px] text-slate-300">National ৳1.2L & International ৳5.5L specs</span>
            </a>
            <a href="{{ route('commission.index') }}" class="p-3.5 bg-white/10 hover:bg-white/15 rounded-xl border border-white/10 text-white text-xs space-y-1 transition-all">
                <span class="font-bold block text-sm">🧮 Open Calculator</span>
                <span class="text-[11px] text-slate-300">Real-time weekly ROI & recovery timeline</span>
            </a>
            <a href="{{ route('ranks.index') }}" class="p-3.5 bg-white/10 hover:bg-white/15 rounded-xl border border-white/10 text-white text-xs space-y-1 transition-all">
                <span class="font-bold block text-sm">🏆 Show Ranks</span>
                <span class="text-[11px] text-slate-300">6 Career Ranks & ৳36.55L cash milestones</span>
            </a>
            <button type="button" @click="openLeadModal()" class="p-3.5 bg-orange-600 hover:bg-orange-500 rounded-xl text-white text-xs space-y-1 transition-all text-left shadow-sm">
                <span class="font-bold block text-sm">📝 Save as Lead</span>
                <span class="text-[11px] text-orange-100">Store contact & set follow-up schedule</span>
            </button>
        </div>
    </div>

    {{-- SECTION 15: SHARE & COPY PROSPECT SUMMARY --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h4 class="text-sm font-bold text-slate-900" data-en="Send Prospect Summary (WhatsApp / Messenger)" data-bn="প্রসপেক্টকে সামারি পাঠান (হোয়াটসঅ্যাপ / মেসেঞ্জার)">
                Send Prospect Summary (WhatsApp / Messenger)
            </h4>
            <p class="text-xs text-slate-500" data-en="Generate compliant, professional counseling summary to share instantly." data-bn="একটি প্রফেশনাল ও নিরাপদ সারসংক্ষেপ তৈরি করে সরাসরি ক্লায়েন্টকে পাঠান।">
                Generate compliant, professional counseling summary to share instantly.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="copyText(getProspectSummaryText(), 'summary')" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                <span x-text="copiedSummary ? 'Summary Copied!' : 'Copy Summary'"></span>
            </button>
            <a :href="'https://wa.me/?text=' + encodeURIComponent(getProspectSummaryText())" target="_blank" rel="noopener noreferrer" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all shadow-xs flex items-center gap-1.5">
                <span>💬 WhatsApp</span>
            </a>
        </div>
    </div>

    {{-- SECTION 16: MOBILE STICKY BOTTOM ACTION BAR (<= 767px) --}}
    <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 p-3 flex md:hidden items-center gap-2 shadow-2xl">
        <button type="button" @click="openLeadModal()" class="flex-1 py-3 rounded-xl bg-orange-600 active:bg-orange-700 text-white font-extrabold text-xs text-center shadow-md flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
            <span data-en="Save Lead" data-bn="লিড সেভ করুন">Save Lead</span>
        </button>
        <button type="button" @click="copyText(getProspectSummaryText(), 'summary')" class="py-3 px-4 rounded-xl bg-slate-900 text-white font-bold text-xs">
            <span x-text="copiedSummary ? '✓ Copied' : 'Share'"></span>
        </button>
    </div>

    {{-- SECTION 14: LEAD CAPTURE MODAL ("Save Prospect" to /leads) --}}
    <div x-show="showLeadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showLeadModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-orange-600 text-white flex items-center justify-center font-bold text-sm">📝</span>
                    <div>
                        <h3 class="font-bold text-base text-slate-900" data-en="Save Prospect Follow-up" data-bn="প্রসপেক্ট তথ্য সংরক্ষণ">Save Prospect Follow-up</h3>
                        <span class="text-xs text-slate-500">Source: Counseling Guide</span>
                    </div>
                </div>
                <button type="button" @click="showLeadModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <div x-show="leadSuccess" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl font-bold text-center">
                ✓ Lead saved successfully to SBL Leads system!
            </div>
            <div x-show="leadError" class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl font-bold" x-text="leadError"></div>

            <form @submit.prevent="submitLead" class="space-y-3 text-xs">
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
                        <label class="block font-bold text-slate-700 mb-1">Prospect Profile Type</label>
                        <select x-model="leadForm.type" class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-semibold focus:border-orange-500">
                            <option value="Investor">Investor Mindset</option>
                            <option value="Networker">Networker Mindset</option>
                            <option value="Hybrid">Hybrid Business Builder</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Lead Temperature</label>
                        <select x-model="leadForm.temperature" class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-semibold focus:border-orange-500">
                            <option value="Hot">🔥 Hot (Ready this week)</option>
                            <option value="Warm">● Warm (Needs 1-2 consultations)</option>
                            <option value="Cold">○ Cold (Informational stage)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Interested Package</label>
                        <select x-model="leadForm.package" class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-semibold focus:border-orange-500">
                            <option value="Starter (৳10,000)">Starter (৳10,000)</option>
                            <option value="National (৳1,20,000)">National (৳1,20,000)</option>
                            <option value="International (৳5,50,000)">International (৳5,50,000)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Next Follow-up Date</label>
                        <input type="date" x-model="leadForm.next_followup_date" class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-semibold focus:border-orange-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Counselor Discussion Notes</label>
                    <textarea rows="2" x-model="leadForm.notes" placeholder="Key questions asked, preferred contact time, budget readiness..." class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-medium focus:border-orange-500"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="showLeadModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">Cancel</button>
                    <button type="submit" :disabled="leadSubmitting" class="px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold shadow-md flex items-center gap-2">
                        <span x-text="leadSubmitting ? 'Saving...' : 'Save Prospect Lead'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

