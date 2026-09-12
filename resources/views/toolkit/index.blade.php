@extends('layouts.app')

@section('meta-title', 'SBL Marketing Tools | SBL Marketing')
@section('page-title', 'SBL Marketing Tools')
@section('page-subtitle', 'Packages, Ranks, Counseling Guide, Commission, Links & Resources')
@section('meta-description', 'SBL Marketing-এর Membership ও Dropshipping package, সুবিধা, service details, ownership note এবং রিসোর্স এক জায়গায় দেখুন।')

@section('content')
<div class="space-y-6" 
    x-on:switch-to-calculator.window="activeTab = 'commission'"
    x-data="{ 
    activeTab: @js($activeTab ?? (in_array(request('tab'), ['packages','ranks','compensation','counseling','commission','calculator','links','ecosystem','resources']) ? (in_array(request('tab'), ['compensation']) ? 'ranks' : (in_array(request('tab'), ['calculator']) ? 'commission' : (in_array(request('tab'), ['ecosystem']) ? 'links' : request('tab')))) : 'packages')), 
    createWebsiteModalOpen: false,
    editWebsiteModalOpen: false,
    editingWebsite: { id: null, title: '', url: '', category: 'Official Portals', badge: '', description: '', icon: '🌐', sort_order: 0 },
    createResourceModalOpen: false,
    editResourceModalOpen: false,
    editingResource: { id: null, title: '', category: 'Leaflets & Sheets', file_type: 'pdf', file_url: '', file_size: '', badge: '', description: '', sort_order: 0 },
    resourceFilter: 'all',
    resourceSearch: '',
    copiedUrl: null,
    copyToClipboard(url) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url);
            this.copiedUrl = url;
            setTimeout(() => { if (this.copiedUrl === url) this.copiedUrl = null; }, 2000);
        }
    },
    openEditWebsiteModal(link) {
        this.editingWebsite = Object.assign({}, link);
        this.editWebsiteModalOpen = true;
    },
    openEditResourceModal(res) {
        this.editingResource = Object.assign({}, res);
        this.editResourceModalOpen = true;
    },
    init() { 
        this.$watch('activeTab', value => history.replaceState(null, '', '?tab=' + value)); 
    } 
}">

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200/80 shadow-xs flex items-center gap-2 overflow-x-auto text-xs font-semibold">
        <button :aria-pressed="activeTab === 'packages'" @click="activeTab = 'packages'" 
                :class="activeTab === 'packages' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer">
            <span>📦</span> Packages
        </button>
        <button :aria-pressed="activeTab === 'ranks' || activeTab === 'compensation'" @click="activeTab = 'ranks'" 
                :class="activeTab === 'ranks' || activeTab === 'compensation' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer">
            <span>🏆</span> Ranks
        </button>
        <button :aria-pressed="activeTab === 'counseling'" @click="activeTab = 'counseling'" 
                :class="activeTab === 'counseling' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer">
            <span>🎯</span> Counseling Guide
        </button>
        <button :aria-pressed="activeTab === 'commission' || activeTab === 'calculator'" @click="activeTab = 'commission'" 
                :class="activeTab === 'commission' || activeTab === 'calculator' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer">
            <span>🧮</span> Commission
        </button>
        <button :aria-pressed="activeTab === 'links' || activeTab === 'ecosystem'" @click="activeTab = 'links'" 
                :class="activeTab === 'links' || activeTab === 'ecosystem' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer">
            <span>🔗</span> Links
        </button>
        <button :aria-pressed="activeTab === 'resources'" @click="activeTab = 'resources'" 
                :class="activeTab === 'resources' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer">
            <span>📁</span> Resources
        </button>
    </div>

    <!-- TAB 1: DROPSHIPPING & MEMBERSHIP PACKAGES -->
    <div x-show="activeTab === 'packages'" class="space-y-6" x-cloak>

        <!-- SBL MEMBERSHIP PACKAGES (Starter ৳১০,০০০, National ৳১,২০,০০০, International ৳৫,৫০,০০০) -->
        @include('toolkit.partials.membership-package')
    </div>

    <!-- TAB 2: COMPENSATION PLAN & RANKS (PDF Page 2 & 7) -->
    <div x-show="activeTab === 'ranks' || activeTab === 'compensation'" class="space-y-6" x-cloak>

        <!-- 10,000 Tk Membership Card (Page 2) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4 mb-4">
                <div>
                    <span class="px-2.5 py-0.5 bg-orange-100 text-orange-800 text-[11px] font-bold rounded-md uppercase">Entry Package</span>
                    <h3 class="text-lg font-black text-slate-900 mt-1">Ready E-Commerce Project & Membership Package</h3>
                    <p class="text-xs text-slate-500">Earn daily 500 to 50,000 BDT by activating a minimum 10,000 BDT Membership.</p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-black text-orange-600">10,000 BDT</span>
                    <span class="text-[11px] text-slate-400 block">15,000 BDT return over 100 weeks</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 font-semibold text-slate-800 flex items-center gap-2">
                    <span class="text-orange-500">✓</span> Free Facebook Page Setup
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 font-semibold text-slate-800 flex items-center gap-2">
                    <span class="text-orange-500">✓</span> Free Affiliate Account Setup
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 font-semibold text-slate-800 flex items-center gap-2">
                    <span class="text-orange-500">✓</span> Unlimited Direct Sponsor
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 font-semibold text-slate-800 flex items-center gap-2">
                    <span class="text-orange-500">✓</span> Free Content Marketing Support
                </div>
            </div>
        </div>

        <!-- 5 Marketing Earning Streams (Page 2) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <h3 class="text-base font-bold text-slate-900 mb-4">SBL Marketing Plan (5 Earning Streams)</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($commissions as $comm)
                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 flex flex-col justify-between hover:border-orange-200 transition-all">
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">{{ $comm->name }}</span>
                            <span class="px-2 py-0.5 mt-1 inline-block bg-orange-100 text-orange-800 rounded text-[11px] font-bold">
                                {{ $comm->rate_description }}
                            </span>
                            <p class="text-xs text-slate-600 mt-2 leading-relaxed">{{ $comm->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Official Ranks & Cash Incentives Table (Page 2) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">SBL Rank Rewards & Cash Incentives</h3>
                    <p class="text-xs text-slate-500">Earn cash rewards up to 40 Lakh BDT by building sales teams and achieving ranks</p>
                </div>
                <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded-lg">Up to 40,00,000 BDT</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-5">Rank Code</th>
                            <th class="py-3 px-5">Designation</th>
                            <th class="py-3 px-5">Eligibility Requirement</th>
                            <th class="py-3 px-5 text-right">Cash Incentive</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($ranks as $rank)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3.5 px-5 font-black text-slate-900">
                                    <span class="px-2.5 py-1 bg-slate-900 text-white rounded-lg text-xs">{{ $rank->code }}</span>
                                </td>
                                <td class="py-3.5 px-5 font-bold text-slate-800">{{ $rank->name }}</td>
                                <td class="py-3.5 px-5 font-medium text-slate-600">{{ $rank->requirement_text }}</td>
                                <td class="py-3.5 px-5 text-right font-extrabold text-orange-600 text-sm">
                                    {{ number_format($rank->incentive_amount, 0) }} BDT
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 10-Generation Affiliate Matrix (Page 7) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">10-Generation Affiliate Commission Matrix</h3>
                <p class="text-xs text-slate-500">Projections based on 10x10 referral matrix (Levels 1 to 10)</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-5">Generation</th>
                            <th class="py-3 px-5">Referred People</th>
                            <th class="py-3 px-5">Investment (TK)</th>
                            <th class="py-3 px-5">Commission %</th>
                            <th class="py-3 px-5 text-right">Your Commission (TK)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($generationMatrix as $gen)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-2.5 px-5 font-bold text-slate-900">{{ $gen['gen'] }}</td>
                                <td class="py-2.5 px-5 font-semibold text-slate-800">{{ $gen['people'] }}</td>
                                <td class="py-2.5 px-5 text-slate-600">{{ $gen['investment'] }}</td>
                                <td class="py-2.5 px-5 font-bold text-orange-600">{{ $gen['rate'] }}</td>
                                <td class="py-2.5 px-5 text-right font-bold text-emerald-700">{{ $gen['commission'] }} BDT</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- TAB 3: COUNSELING GUIDE (PDF Page 6) -->
    <div x-show="activeTab === 'counseling'" class="space-y-6" x-cloak>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <span class="px-2.5 py-0.5 bg-orange-100 text-orange-800 text-[11px] font-bold rounded-md uppercase">Sales Counseling Sheet</span>
                <h3 class="text-xl font-bold text-slate-900 mt-1">Counseling - 1: Investor vs Networker Pitch Guide</h3>
                <p class="text-xs text-slate-500">Understand the lead type (Investor vs Networker) to highlight the relevant benefits.</p>
            </div>

            <!-- Side-by-side Comparative Table (Page 6) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Investor Box -->
                <div class="bg-orange-50/40 rounded-2xl border-2 border-orange-200 p-6 space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-orange-600 text-white font-bold flex items-center justify-center text-sm">I1</span>
                        <div>
                            <h4 class="font-bold text-base text-slate-900">Investor Mindset</h4>
                            <span class="text-xs text-orange-700">Focus: Capital protection, steady returns & branding</span>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs text-slate-700">
                        <div class="p-3 bg-white rounded-xl border border-orange-100">
                            <span class="font-bold text-slate-900 block mb-0.5">1. Investment Security:</span>
                            Weekly guaranteed return (1.75% or 2%) and crowdfunding opportunity.
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-orange-100">
                            <span class="font-bold text-slate-900 block mb-0.5">2. Legal & Authentic Business:</span>
                            Real e-commerce products, Shopify store, and transparent contract.
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-orange-100">
                            <span class="font-bold text-slate-900 block mb-0.5">3. Business Branding & Sustainable Income:</span>
                            Lifetime monthly profit sharing of 5,000 to 100,000 BDT after 100 weeks.
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-orange-100">
                            <span class="font-bold text-slate-900 block mb-0.5">4. Digital Asset Control & Value:</span>
                            Digital store and audience equity that appreciates over time.
                        </div>
                    </div>
                </div>

                <!-- Networker Box -->
                <div class="bg-purple-50/40 rounded-2xl border-2 border-purple-200 p-6 space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-purple-600 text-white font-bold flex items-center justify-center text-sm">A1</span>
                        <div>
                            <h4 class="font-bold text-base text-slate-900">Networker Mindset (Team & Affiliate)</h4>
                            <span class="text-xs text-purple-700">Focus: Fast cashflow, team matching & large rewards</span>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs text-slate-700">
                        <div class="p-3 bg-white rounded-xl border border-purple-100">
                            <span class="font-bold text-slate-900 block mb-0.5">1. Multiple Centers Advantage:</span>
                            Operate multiple tri-pods or IDs to multiply earning potential.
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-purple-100">
                            <span class="font-bold text-slate-900 block mb-0.5">2. Early Mover Advantage:</span>
                            Receive binary team spillovers to accelerate team building.
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-purple-100">
                            <span class="font-bold text-slate-900 block mb-0.5">3. Daily Up to 50,000 BDT Income (Pair Reward):</span>
                            Earn 500 BDT per pair up to a maximum of 100 pairs daily.
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-purple-100">
                            <span class="font-bold text-slate-900 block mb-0.5">4. Sustainable Income & Teamwork:</span>
                            10% spot commission, 0.25% weekly refer return, and rank bonuses up to 40 Lakh BDT.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: COMMISSION CALCULATOR (PDF Page 1, 2, 3, 7) -->
    <div x-show="activeTab === 'commission' || activeTab === 'calculator'" class="space-y-6" x-cloak 
         x-on:switch-to-calculator.window="packageType = $event.detail.type; packageAmount = $event.detail.amount; window.scrollTo({ top: 0, behavior: 'smooth' });"
         x-data="{
            packageType: 'national',
            packageAmount: 120000,
            referralAmount: 120000,
            teamPackageAmount: 10000,
            teamMultiplier: 10,
            activeGenView: 'matrix', // 'matrix' or 'single'

            genRates: [
                { gen: '1st', rate: 10.0, label: '1st Generation (Direct Sponsor)' },
                { gen: '2nd', rate: 2.0, label: '2nd Generation' },
                { gen: '3rd', rate: 1.0, label: '3rd Generation' },
                { gen: '4th', rate: 1.0, label: '4th Generation' },
                { gen: '5th', rate: 0.5, label: '5th Generation' },
                { gen: '6th', rate: 0.1, label: '6th Generation' },
                { gen: '7th', rate: 0.1, label: '7th Generation' },
                { gen: '8th', rate: 0.1, label: '8th Generation' },
                { gen: '9th', rate: 0.1, label: '9th Generation' },
                { gen: '10th', rate: 0.1, label: '10th Generation' }
            ],

            // 1. Investment ROI Properties
            get devFee() {
                return this.packageType === 'national' ? 20000 : 50000;
            },
            get coreInvestment() {
                return Math.max(0, this.packageAmount - this.devFee);
            },
            get weeklyRate() {
                return this.packageType === 'national' ? 0.0175 : 0.02;
            },
            get weeklyEarning() {
                return Math.round(this.coreInvestment * this.weeklyRate);
            },
            get monthlyEarning() {
                // 1750 * 30 / 7 = 7500 (National 1,20,000)
                // 10000 * 30 / 7 = 42857 (International 5,50,000)
                return Math.round((this.weeklyEarning * 30) / 7);
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

            // 2. Direct Referral Simulator
            get spotCommission() {
                return Math.round(this.referralAmount * 0.10);
            },
            get weeklyReferReturn() {
                return Math.round(this.referralAmount * 0.0025);
            },
            get totalReferReturn100Weeks() {
                return Math.round(this.weeklyReferReturn * 100);
            },

            // 3. 10-Generation Matrix Calculations
            getMatrixRow(index) {
                let level = index + 1;
                let rate = this.genRates[index].rate;
                let people = Math.pow(this.teamMultiplier, level);
                let volume = people * this.teamPackageAmount;
                let commission = Math.round(volume * (rate / 100));
                return {
                    level: level,
                    gen: this.genRates[index].gen,
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
            }
         }">
        
        <!-- Top Row: Investment ROI Simulator & Direct Referral Simulator -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- 1. Investment ROI Simulator -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <span class="px-2.5 py-0.5 bg-orange-100 text-orange-800 text-[10px] font-bold rounded-md uppercase">ROI Simulator</span>
                        <h3 class="text-base font-bold text-slate-900 mt-1">Investment Return Calculator (100 Weeks)</h3>
                        <p class="text-xs text-slate-500">Weekly and monthly return projection based on capital excluding development fee.</p>
                    </div>

                    <!-- Package Type Toggle -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Package Type:</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="packageType = 'national'; if(packageAmount > 490000 || packageAmount < 100000) packageAmount = 120000;"
                                    :class="packageType === 'national' ? 'bg-orange-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-2.5 px-3 rounded-xl text-xs transition-all text-left">
                                <span class="block font-bold">National Package</span>
                                <span class="text-[11px] opacity-90">1.75%/week • Fee: 20,000 BDT</span>
                            </button>
                            <button type="button" @click="packageType = 'international'; if(packageAmount < 500000) packageAmount = 550000;"
                                    :class="packageType === 'international' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-2.5 px-3 rounded-xl text-xs transition-all text-left">
                                <span class="block font-bold">International Package</span>
                                <span class="text-[11px] opacity-90">2.00%/week • Fee: 50,000 BDT</span>
                            </button>
                        </div>
                    </div>

                    <!-- Package Amount Input -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">Total Package Amount (BDT):</label>
                            <span class="text-[11px] font-medium text-slate-500">
                                Core Investment (Excl. Fee): <strong class="text-orange-600" x-text="coreInvestment.toLocaleString('en-IN') + ' BDT'"></strong>
                            </span>
                        </div>
                        <input type="number" inputmode="numeric" x-model.number="packageAmount" step="10000" class="w-full text-base font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
                        
                        <!-- Quick Presets -->
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <button type="button" @click="packageType = 'national'; packageAmount = 120000" 
                                    :class="packageType === 'national' && packageAmount === 120000 ? 'border-orange-500 bg-orange-50 text-orange-800 font-bold' : 'bg-slate-100 text-slate-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-medium border border-transparent hover:bg-slate-200 transition-all">
                                120,000 BDT (National)
                            </button>
                            <button type="button" @click="packageType = 'national'; packageAmount = 250000" 
                                    :class="packageType === 'national' && packageAmount === 250000 ? 'border-orange-500 bg-orange-50 text-orange-800 font-bold' : 'bg-slate-100 text-slate-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-medium border border-transparent hover:bg-slate-200 transition-all">
                                250,000 BDT
                            </button>
                            <button type="button" @click="packageType = 'international'; packageAmount = 550000" 
                                    :class="packageType === 'international' && packageAmount === 550000 ? 'border-purple-500 bg-purple-50 text-purple-800 font-bold' : 'bg-slate-100 text-slate-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-medium border border-transparent hover:bg-slate-200 transition-all">
                                550,000 BDT (International)
                            </button>
                            <button type="button" @click="packageType = 'international'; packageAmount = 1000000" 
                                    :class="packageType === 'international' && packageAmount === 1000000 ? 'border-purple-500 bg-purple-50 text-purple-800 font-bold' : 'bg-slate-100 text-slate-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-medium border border-transparent hover:bg-slate-200 transition-all">
                                1,000,000 BDT
                            </button>
                        </div>
                    </div>

                    <!-- Development Charge Clarification Notice -->
                    <div class="p-3 bg-amber-50/80 border border-amber-200 rounded-xl text-xs text-amber-900 space-y-1">
                        <div class="flex items-center justify-between font-semibold">
                            <span>🛠️ Development Charge (Non-refundable):</span>
                            <span class="font-bold text-amber-950" x-text="devFee.toLocaleString('en-IN') + ' BDT'"></span>
                        </div>
                        <p class="text-[11px] text-amber-800 leading-relaxed">
                            * This is not part of the core investment (website & technical setup fee). Returns are calculated on core capital of 
                            <strong x-text="coreInvestment.toLocaleString('en-IN') + ' BDT'"></strong>.
                        </p>
                    </div>
                </div>

                <!-- Live Results Display -->
                <div class="p-4 bg-slate-900 text-white rounded-2xl space-y-3 text-xs shadow-md">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <span class="text-slate-400">Core Capital:</span>
                        <span class="font-bold text-white text-sm" x-text="coreInvestment.toLocaleString('en-IN') + ' BDT'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300 font-medium">Weekly Return:</span>
                        <span class="font-extrabold text-orange-400 text-base" x-text="weeklyEarning.toLocaleString('en-IN') + ' BDT / week'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300 font-medium">Estimated Monthly Return:</span>
                        <span class="font-bold text-emerald-400 text-sm" x-text="monthlyEarning.toLocaleString('en-IN') + ' BDT / mo'"></span>
                    </div>
                    <div class="border-t border-slate-800 pt-2 flex items-center justify-between">
                        <div>
                            <span class="text-slate-200 font-bold block">Total 100-Week Return:</span>
                            <span class="text-[10px] text-slate-400">Estimated based on defined weekly rate</span>
                        </div>
                        <span class="font-black text-emerald-400 text-xl" x-text="totalReturn100Weeks.toLocaleString('en-IN') + ' BDT'"></span>
                    </div>
                    <div class="border-t border-slate-800 pt-2 flex items-center justify-between text-[11px]">
                        <span class="text-slate-400">Net Profit (After Total Package):</span>
                        <span class="font-bold text-emerald-300" x-text="netProfitTotal.toLocaleString('en-IN') + ' BDT'"></span>
                    </div>
                </div>
            </div>

            <!-- 2. Direct Referral & Spot Commission Simulator -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-md uppercase">Direct Referral</span>
                        <h3 class="text-base font-bold text-slate-900 mt-1">Direct Referral Commission (1st Generation)</h3>
                        <p class="text-xs text-slate-500">Instant and weekly income earned when projects join via your direct referral.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Referred Project Amount (BDT):</label>
                        <input type="number" inputmode="numeric" x-model.number="referralAmount" step="10000" class="w-full text-base font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
                        
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <button type="button" @click="referralAmount = 10000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">10,000 BDT</button>
                            <button type="button" @click="referralAmount = 120000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">120,000 BDT</button>
                            <button type="button" @click="referralAmount = 250000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">250,000 BDT</button>
                            <button type="button" @click="referralAmount = 550000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">550,000 BDT</button>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <!-- Spot Commission 10% -->
                        <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl flex items-center justify-between">
                            <div>
                                <span class="font-bold text-emerald-900 block text-sm">10% Spot Commission (Instant)</span>
                                <span class="text-[11px] text-emerald-700">Credited to wallet immediately upon project activation</span>
                            </div>
                            <span class="text-2xl font-black text-emerald-700" x-text="spotCommission.toLocaleString('en-IN') + ' BDT'"></span>
                        </div>

                        <!-- Refer Return 0.25% -->
                        <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-2xl space-y-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-blue-900 block">0.25% Weekly Refer Return</span>
                                    <span class="text-[11px] text-blue-700">Payable weekly for up to 100 weeks</span>
                                </div>
                                <span class="text-lg font-bold text-blue-700" x-text="weeklyReferReturn.toLocaleString('en-IN') + ' BDT / week'"></span>
                            </div>
                            <div class="border-t border-blue-200 pt-1.5 flex items-center justify-between text-[11px] text-blue-800">
                                <span>Total 100-Week Refer Return:</span>
                                <span class="font-bold text-blue-900" x-text="totalReferReturn100Weeks.toLocaleString('en-IN') + ' BDT'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 space-y-1">
                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                        <span>💡 Pair Reward:</span>
                    </div>
                    <p class="text-[11px] leading-relaxed">
                        Earn 500 BDT per pair by building sales teams (up to 100 pairs daily = 50,000 BDT max cash income).
                    </p>
                </div>
            </div>

        </div>

        <!-- Bottom Section: 10-Generation Affiliate Commission Simulator (PDF Page 7) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 text-[10px] font-bold rounded-md uppercase">Multi-Tier Affiliate</span>
                        <h3 class="text-lg font-bold text-slate-900">10-Generation Commission Simulator (10-Generation Matrix)</h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Simulate commission projections for your full 10-tier team and network.</p>
                </div>

                <!-- Simulation Mode Buttons -->
                <div class="flex items-center gap-2 text-xs">
                    <button type="button" @click="activeGenView = 'matrix'"
                            :class="activeGenView === 'matrix' ? 'bg-orange-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        Team Matrix Simulation
                    </button>
                    <button type="button" @click="activeGenView = 'single'"
                            :class="activeGenView === 'single' ? 'bg-orange-600 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        Single Project Distribution
                    </button>
                </div>
            </div>

            <!-- Matrix Mode Controls -->
            <div x-show="activeGenView === 'matrix'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Average Package / Membership Size (BDT):</label>
                        <input type="number" inputmode="numeric" x-model.number="teamPackageAmount" step="1000" class="w-full text-sm font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2 bg-white">
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            <button type="button" @click="teamPackageAmount = 10000" class="px-2 py-0.5 bg-white border border-slate-200 text-slate-700 rounded text-[11px] font-medium hover:border-orange-500">10,000 BDT (PDF Default)</button>
                            <button type="button" @click="teamPackageAmount = 120000" class="px-2 py-0.5 bg-white border border-slate-200 text-slate-700 rounded text-[11px] font-medium hover:border-orange-500">120,000 BDT (National)</button>
                            <button type="button" @click="teamPackageAmount = 550000" class="px-2 py-0.5 bg-white border border-slate-200 text-slate-700 rounded text-[11px] font-medium hover:border-orange-500">550,000 BDT (International)</button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Referral Multiplier Per Person (Team Multiplier):</label>
                        <div class="grid grid-cols-4 gap-1.5">
                            <button type="button" @click="teamMultiplier = 2" 
                                    :class="teamMultiplier === 2 ? 'bg-slate-900 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700'"
                                    class="py-2 rounded-xl text-xs text-center transition-all">2×2 Team</button>
                            <button type="button" @click="teamMultiplier = 3" 
                                    :class="teamMultiplier === 3 ? 'bg-slate-900 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700'"
                                    class="py-2 rounded-xl text-xs text-center transition-all">3×3 Team</button>
                            <button type="button" @click="teamMultiplier = 5" 
                                    :class="teamMultiplier === 5 ? 'bg-slate-900 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700'"
                                    class="py-2 rounded-xl text-xs text-center transition-all">5×5 Team</button>
                            <button type="button" @click="teamMultiplier = 10" 
                                    :class="teamMultiplier === 10 ? 'bg-orange-600 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700'"
                                    class="py-2 rounded-xl text-xs text-center transition-all">10×10 Team (PDF)</button>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Team grows exponentially based on how many people each member refers on average.</p>
                    </div>
                </div>

                <!-- Grand Matrix Summary Banner -->
                <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-orange-950 text-white p-5 md:p-6 rounded-2xl border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <span class="text-xs text-orange-400 font-bold uppercase tracking-wider block">
                            Total 10-Generation Potential Commission
                        </span>
                        <div class="text-2xl md:text-3xl font-black text-white mt-1">
                            <span x-text="totalMatrixCommission.toLocaleString('en-IN')"></span> BDT
                        </div>
                        <p class="text-xs text-slate-300 mt-1">
                            Based on average package <span class="font-bold text-orange-300" x-text="teamPackageAmount.toLocaleString('en-IN') + ' BDT'"></span> and full 10 levels with 
                            <span class="font-bold text-orange-300" x-text="teamMultiplier + '×' + teamMultiplier"></span> duplication.
                        </p>
                    </div>
                    <div class="flex items-center gap-4 text-center">
                        <div class="bg-white/10 px-4 py-2.5 rounded-xl border border-white/10">
                            <span class="text-[10px] text-slate-300 block uppercase">1st Generation (Direct)</span>
                            <span class="text-base font-bold text-emerald-400" x-text="getMatrixRow(0).commission.toLocaleString('en-IN') + ' BDT'"></span>
                        </div>
                        <div class="bg-white/10 px-4 py-2.5 rounded-xl border border-white/10">
                            <span class="text-[10px] text-slate-300 block uppercase">2nd - 10th Generation</span>
                            <span class="text-base font-bold text-orange-400" x-text="(totalMatrixCommission - getMatrixRow(0).commission).toLocaleString('en-IN') + ' BDT'"></span>
                        </div>
                    </div>
                </div>

                <!-- 10-Generation Breakdown Table -->
                <div class="overflow-x-auto border border-slate-200/80 rounded-2xl">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Generation</th>
                                <th class="py-3 px-4">Commission Rate %</th>
                                <th class="py-3 px-4">Team Members</th>
                                <th class="py-3 px-4">Total Team Sales Volume</th>
                                <th class="py-3 px-4 text-right">Your Commission (BDT)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(gen, index) in genRates" :key="index">
                                <tr class="hover:bg-slate-50/80 transition-colors" :class="index === 0 ? 'bg-orange-50/40 font-medium' : ''">
                                    <td class="py-3 px-4 font-bold text-slate-900">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold"
                                              :class="index === 0 ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-700'"
                                              x-text="gen.gen"></span>
                                        <span class="ml-1.5 hidden sm:inline" x-text="index === 0 ? '(Direct Sponsor)' : ''"></span>
                                    </td>
                                    <td class="py-3 px-4 font-extrabold text-orange-600" x-text="gen.rate + '%'"></td>
                                    <td class="py-3 px-4 font-semibold text-slate-800" x-text="getMatrixRow(index).people.toLocaleString('en-IN')"></td>
                                    <td class="py-3 px-4 text-slate-600" x-text="getMatrixRow(index).volume.toLocaleString('en-IN') + ' BDT'"></td>
                                    <td class="py-3 px-4 text-right font-black text-emerald-700 text-sm" x-text="getMatrixRow(index).commission.toLocaleString('en-IN') + ' BDT'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Single Project Distribution Mode -->
            <div x-show="activeGenView === 'single'" class="space-y-4" x-cloak>
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Commission Distribution Across 10 Tiers for a Single Project Sale:</span>
                        <p class="text-xs text-slate-500">How commission is credited to 10 upline levels for any single project package.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-700">Project Amount:</label>
                        <input type="number" inputmode="numeric" x-model.number="referralAmount" step="10000" class="w-36 text-sm font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-1.5 bg-white">
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-200/80 rounded-2xl">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Upline Level</th>
                                <th class="py-3 px-4">Commission Rate %</th>
                                <th class="py-3 px-4">Project Volume</th>
                                <th class="py-3 px-4 text-right">Payable Commission (BDT)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(gen, index) in genRates" :key="'single-' + index">
                                <tr class="hover:bg-slate-50/80" :class="index === 0 ? 'bg-emerald-50/50' : ''">
                                    <td class="py-2.5 px-4 font-bold text-slate-900">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold"
                                              :class="index === 0 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-800'"
                                              x-text="gen.gen"></span>
                                        <span class="ml-2" x-text="index === 0 ? '1st Gen (Sponsor)' : (index + 1) + 'th Upline Level'"></span>
                                    </td>
                                    <td class="py-2.5 px-4 font-bold text-orange-600" x-text="gen.rate + '%'"></td>
                                    <td class="py-2.5 px-4 text-slate-700" x-text="referralAmount.toLocaleString('en-IN') + ' BDT'"></td>
                                    <td class="py-2.5 px-4 text-right font-black text-emerald-700" x-text="Math.round(referralAmount * (gen.rate / 100)).toLocaleString('en-IN') + ' BDT'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

    <!-- TAB 5: SBL LINKS & WEB DIRECTORY -->
    <div x-show="activeTab === 'links' || activeTab === 'ecosystem'" class="space-y-6" x-cloak>
        <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-orange-950 text-white p-6 rounded-2xl border border-slate-800 shadow-md flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2">
                <span class="px-3 py-1 bg-orange-600/30 text-orange-400 border border-orange-500/30 rounded-full text-xs font-bold uppercase tracking-wider">
                    Official SBL Links & Directory
                </span>
                <h2 class="text-xl md:text-2xl font-bold tracking-tight">Official SBL Links & Web Directory</h2>
                <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Visit official SBL portals, dropshipping stores, investor platforms, and tools.
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button" @click="createWebsiteModalOpen = true" class="px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center gap-2 shadow-xs cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Add Link</span>
                </button>
                <a href="{{ route('ecosystem.index') }}" class="px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold rounded-xl transition-colors flex items-center gap-1.5 border border-slate-700">
                    <span>Manage All</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>

        @php
            $ecosystemLinks = \App\Models\EcosystemLink::where('is_active', true)->orderBy('sort_order')->get();
        @endphp

        <div id="toolkit-websites-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($ecosystemLinks as $el)
            <div data-link-id="{{ $el->id }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between hover:shadow-md hover:border-orange-200 transition-all group">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 group-hover:bg-orange-50 flex items-center justify-center text-xl flex-shrink-0">
                                {{ $el->icon ?: '🌐' }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm group-hover:text-orange-600 transition-colors">{{ $el->title }}</h4>
                                <span class="text-[10px] font-semibold text-slate-400">{{ $el->category }}</span>
                            </div>
                        </div>
                        @if($el->badge)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800">
                            {{ $el->badge }}
                        </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">{{ $el->description }}</p>

                    <div class="text-[11px] font-mono text-slate-400 truncate">
                        {{ $el->url }}
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="{{ $el->url }}" target="_blank" rel="noopener noreferrer" class="flex-1 px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                        <span>Visit Website</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>

                    <button type="button" @click="openEditWebsiteModal({{ json_encode($el) }})" class="p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-colors cursor-pointer" title="Edit Website">
                        ✏️
                    </button>

                    <form action="{{ route('ecosystem.destroy', $el) }}" method="POST" onsubmit="return confirm('Remove {{ addslashes($el->title) }}?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors cursor-pointer" title="Delete Website">
                            🗑️
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <!-- CREATE WEBSITE MODAL -->
        <div role="dialog" aria-modal="true" tabindex="-1" x-show="createWebsiteModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-transition
             x-cloak>
            <div @click.away="createWebsiteModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🌐</span>
                        <h3 class="text-base font-bold text-slate-900">Add Ecosystem Website / Portal</h3>
                    </div>
                    <button @click="createWebsiteModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
                </div>

                <form action="{{ route('ecosystem.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Platform Name *</label>
                            <input type="text" name="title" required placeholder="e.g. SBL Dropshipping Shop" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Icon / Emoji</label>
                            <input type="text" name="icon" value="🌐" placeholder="🛍️" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none text-center text-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Website URL *</label>
                        <input type="url" name="url" required placeholder="https://example.sbl.com.bd" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                            <select name="category" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                                <option value="Official Portals">Official Portals</option>
                                <option value="Business & Commerce">Business & Commerce</option>
                                <option value="Affiliate & Community">Affiliate & Community</option>
                                <option value="Support & Training">Support & Training</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                            <input type="text" name="badge" placeholder="e.g. Dropshipping Hub" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                        <textarea name="description" rows="2" placeholder="Brief description of this platform or service..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="createWebsiteModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors cursor-pointer">Save Website</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT WEBSITE MODAL -->
        <div role="dialog" aria-modal="true" tabindex="-1" x-show="editWebsiteModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-transition
             x-cloak>
            <div @click.away="editWebsiteModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✏️</span>
                        <h3 class="text-base font-bold text-slate-900">Edit Website / Portal</h3>
                    </div>
                    <button @click="editWebsiteModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
                </div>

                <form :action="'{{ url('/ecosystem') }}/' + editingWebsite.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Platform Name *</label>
                            <input type="text" name="title" x-model="editingWebsite.title" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Icon</label>
                            <input type="text" name="icon" x-model="editingWebsite.icon" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none text-center text-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Website URL *</label>
                        <input type="url" name="url" x-model="editingWebsite.url" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                            <select name="category" x-model="editingWebsite.category" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                                <option value="Official Portals">Official Portals</option>
                                <option value="Business & Commerce">Business & Commerce</option>
                                <option value="Affiliate & Community">Affiliate & Community</option>
                                <option value="Support & Training">Support & Training</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                            <input type="text" name="badge" x-model="editingWebsite.badge" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                        <textarea name="description" x-model="editingWebsite.description" rows="2" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="editWebsiteModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors cursor-pointer">Update Website</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TAB 6: RESOURCES & OFFICIAL LEAFLETS -->
    <div x-show="activeTab === 'resources'" class="space-y-6" x-cloak>
        @include('toolkit.partials.resources')
    </div>

</div>
@endsection


