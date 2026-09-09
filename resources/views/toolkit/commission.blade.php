@extends('layouts.app')

@section('meta-title', 'Commission | SBL Marketing')
@section('page-title', 'Commission')
@section('page-subtitle', 'Real-time ROI, Direct Referral & 10-Generation Matrix Simulator')
@section('meta-description', 'Real-time interactive investment return calculator, direct referral simulator and 10-tier matrix calculations.')

@section('content')
    <div class="space-y-6"
x-on:switch-to-calculator.window="packageType = $event.detail.type; packageAmount = $event.detail.amount; window.scrollTo({ top: 0, behavior: 'smooth' });"
         x-data="{
            packageType: @js($defaultType ?? 'national'),
            packageAmount: @js($defaultAmount ?? 120000),
            referralAmount: @js($defaultAmount ?? 120000),
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
@endsection
