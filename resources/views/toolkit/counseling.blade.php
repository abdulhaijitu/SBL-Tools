@extends('layouts.app')

@section('meta-title', 'Counseling Guide | SBL Marketing')
@section('page-title', 'Counseling Guide')
@section('page-subtitle', 'Sales Counseling Sheet, Investor vs Networker Pitch Guide & Objection Handling')
@section('meta-description', 'SBL Sales Counseling Sheet: Investor Mindset vs Networker Mindset comparative pitch guide and conversion scripts.')

@section('content')
<div class="space-y-6">
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
@endsection
