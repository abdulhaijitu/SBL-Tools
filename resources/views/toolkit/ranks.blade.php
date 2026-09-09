@extends('layouts.app')

@section('meta-title', 'Ranks | SBL Marketing')
@section('page-title', 'Ranks')
@section('page-subtitle', 'SBL Career Ranks, Criteria, Maintenance BV & Direct Line Requirements')
@section('meta-description', 'SBL Marketing career rank designations, binary criteria, maintenance BV, direct line requirements and rewards.')

@section('content')
<div class="space-y-6">

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
@endsection
