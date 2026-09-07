@extends('layouts.app')

@section('page-title', 'Plans & Toolkit')
@section('page-subtitle', 'Official Business Packages, Compensation Models & Counseling Cheatsheet')

@section('content')
<div class="space-y-6" x-data="{ activeTab: @js(in_array(request('tab'), ['packages','compensation','counseling','calculator','ecosystem']) ? request('tab') : 'packages'), init() { this.$watch('activeTab', value => history.replaceState(null, '', '?tab=' + value)); } }">

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200/80 shadow-xs flex items-center gap-2 overflow-x-auto text-xs font-semibold">
        <button :aria-pressed="activeTab === 'packages'" @click="activeTab = 'packages'" 
                :class="activeTab === 'packages' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0">
            <span>📦</span> Packages
        </button>
        <button :aria-pressed="activeTab === 'compensation'" @click="activeTab = 'compensation'" 
                :class="activeTab === 'compensation' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0">
            <span>💰</span> Ranks
        </button>
        <button :aria-pressed="activeTab === 'counseling'" @click="activeTab = 'counseling'" 
                :class="activeTab === 'counseling' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0">
            <span>🎯</span> Counseling Guide
        </button>
        <button :aria-pressed="activeTab === 'calculator'" @click="activeTab = 'calculator'" 
                :class="activeTab === 'calculator' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0">
            <span>🧮</span> Commission Calculator
        </button>
        <button :aria-pressed="activeTab === 'ecosystem'" @click="activeTab = 'ecosystem'" 
                :class="activeTab === 'ecosystem' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0">
            <span>🌐</span> Websites
        </button>
    </div>

    <!-- TAB 1: DROPSHIPPING PACKAGES (PDF Page 1, 3 & 4) -->
    <div x-show="activeTab === 'packages'" class="space-y-6" x-cloak>
        
        <div class="section-heading"><div><h2>Business packages</h2><p>Compare package features, eligibility and terms.</p></div></div>

        <!-- National & International Package Cards (Page 1 & 3) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- National Package -->
            <div class="bg-white rounded-2xl border-2 border-orange-200 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-3 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded-lg uppercase">Domestic Market</span>
                        <span class="text-xs font-bold text-slate-500">100 Weeks (24 Mo)</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-900">National Package</h3>
                    <div class="text-2xl font-extrabold text-orange-600 mt-2">
                        ১,০০,০০০ ৳ - ৪,৯০,০০০ ৳
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Website Development Fee: ২০,০০০ ৳</p>

                    <div class="mt-6 space-y-3 text-xs text-slate-700">
                        <div class="flex items-center gap-2.5 font-semibold text-slate-900 bg-orange-50/60 p-2.5 rounded-xl border border-orange-100">
                            <span class="text-orange-600 font-bold">✓</span>
                            <span>সাপ্তাহিক ১.৭৫% রিটার্ন (১০০ সপ্তাহ ব্যাপী)</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>ক্রাউডফান্ডিং সুযোগ: সর্বোচ্চ ১০ লক্ষ টাকা পর্যন্ত</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>লাইফটাইম প্রফিট শেয়ারিং (১০০ সপ্তাহ পর): মাসে কমবেশি ৫,০০০ - ২০,০০০ ৳</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>Branded Shopify Store এবং পণ্য সোর্সিং</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>নিজস্ব প্যাকেজিং ও পেইড ক্যাম্পেইন সেটআপ</span>
                        </div>
                    </div>

                    <!-- Example Box -->
                    <div class="mt-6 p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 space-y-1">
                        <span class="font-bold text-slate-900 block text-[11px] uppercase tracking-wider">উদাহরণ হিসাব:</span>
                        <p>১,২০,০০০ টাকা বিনিয়োগে প্রতি সপ্তাহে <strong>১,৭৫০ টাকা</strong> করে ১০০ সপ্তাহে মোট মূলধনসহ <strong>১,৭৫,০০০ টাকা</strong> রিটার্নের উদাহরণ।</p>
                    </div>
                </div>
            </div>

            <!-- International Package -->
            <div class="bg-white rounded-2xl border-2 border-purple-200 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded-lg uppercase">Global Scale</span>
                        <span class="text-xs font-bold text-slate-500">100 Weeks (24 Mo)</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-900">International Package</h3>
                    <div class="text-2xl font-extrabold text-purple-600 mt-2">
                        ৫,০০,০০০ ৳ - আনলিমিটেড
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Website & Content Fee: ৫০,০০০ ৳</p>

                    <div class="mt-6 space-y-3 text-xs text-slate-700">
                        <div class="flex items-center gap-2.5 font-semibold text-slate-900 bg-purple-50/60 p-2.5 rounded-xl border border-purple-100">
                            <span class="text-purple-600 font-bold">✓</span>
                            <span>সাপ্তাহিক ২.০০% রিটার্ন (১০০ সপ্তাহ ব্যাপী)</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>ক্রাউডফান্ডিং সুযোগ: সর্বোচ্চ ৫০ লক্ষ টাকা পর্যন্ত</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>লাইফটাইম প্রফিট শেয়ারিং (১০০ সপ্তাহ পর): মাসে কমবেশি ২৫,০০০ - ১,০০,০০০ ৳</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>Dedicated Team for Project Management</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>Unlimited UGC Content & Global Marketing</span>
                        </div>
                    </div>

                    <!-- Example Box -->
                    <div class="mt-6 p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 space-y-1">
                        <span class="font-bold text-slate-900 block text-[11px] uppercase tracking-wider">উদাহরণ হিসাব:</span>
                        <p>৫,৫০,০০০ টাকা বিনিয়োগে প্রতি সপ্তাহে <strong>১০,০০০ টাকা</strong> করে ১০০ সপ্তাহে মোট মূলধনসহ <strong>১০,০০,০০০ টাকা</strong> রিটার্নের উদাহরণ।</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- 20k Dropshipping Package vs Market Cost Comparison (PDF Page 4) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-900">SBL Dropshipping Package Offer</h3>
                    <p class="text-xs text-slate-500">সাধারণ মার্কেটের মোট খরচ বনাম এসবিএল লাইফটাইম সার্ভিস অফার</p>
                </div>
                <div class="px-3.5 py-1.5 bg-emerald-100 text-emerald-800 rounded-xl font-bold text-xs">
                    মাত্র ২০,০০০ ৳ - ওয়ানটাইম পেমেন্ট - লাইফটাইম সার্ভিস
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-5">সার্ভিস / ফিচার</th>
                            <th class="py-3 px-5 text-right">সাধারণ মার্কেটে খরচ</th>
                            <th class="py-3 px-5 text-right">SBL প্যাকেজে খরচ</th>
                            <th class="py-3 px-5">Service / Feature</th>
                            <th class="py-3 px-5 text-right">Standard Market Cost</th>
                            <th class="py-3 px-5 text-right">SBL Package Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($marketComparisons as $row)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-2.5 px-5 font-medium text-slate-800">{{ $row['service'] }}</td>
                                <td class="py-2.5 px-5 text-right text-rose-600 font-semibold">{{ $row['market'] }}</td>
                                <td class="py-2.5 px-5 text-right text-emerald-600 font-bold">অন্তর্ভুক্ত (Included)</td>
                                <td class="py-2.5 px-5 text-right text-emerald-600 font-bold">Included</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 6-Month Growth Trajectory (PDF Page 4) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">ন্যূনতম ১০০০ ডলার স্কেলে ৬ মাসের গ্রোথ প্রজেকশন</h3>
                <p class="text-xs text-slate-500">পর্যায়ক্রমিক অ্যাড বাজেট, অডিয়েন্স রিচ এবং অর্ডার সংখ্যা</p>
                <h3 class="text-sm font-bold text-slate-900">6-Month Growth Projection ($1,000 Scale)</h3>
                <p class="text-xs text-slate-500">Progressive ad spend, audience reach and monthly order trajectory</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-center text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">মাস</th>
                            <th class="py-3 px-4">অ্যাড বাজেট</th>
                            <th class="py-3 px-4">অডিয়েন্স সাইজ</th>
                            <th class="py-3 px-4">মাসিক অর্ডার রেকর্ড</th>
                            <th class="py-3 px-4">Month</th>
                            <th class="py-3 px-4">Ad Budget</th>
                            <th class="py-3 px-4">Audience Size</th>
                            <th class="py-3 px-4">Monthly Orders</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($growthTrajectory as $tr)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $tr['month'] }}</td>
                                <td class="py-3 px-4 text-orange-600 font-semibold">{{ $tr['ad_spend'] }}</td>
                                <td class="py-3 px-4 font-medium">{{ $tr['audience'] }}</td>
                                <td class="py-3 px-4 font-bold text-emerald-700">{{ $tr['orders'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- TAB 2: COMPENSATION PLAN & RANKS (PDF Page 2 & 7) -->
    <div x-show="activeTab === 'compensation'" class="space-y-6" x-cloak>

        <!-- 10,000 Tk Membership Card (Page 2) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4 mb-4">
                <div>
                    <span class="px-2.5 py-0.5 bg-orange-100 text-orange-800 text-[11px] font-bold rounded-md uppercase">Entry Package</span>
                    <h3 class="text-lg font-black text-slate-900 mt-1">রেডি ই-কমার্স প্রজেক্ট ও মেম্বারশিপ প্যাকেজ</h3>
                    <p class="text-xs text-slate-500">ন্যূনতম ১০,০০০ টাকার Membership এক্টিভ করে ডেইলি ৫০০ থেকে ৫০,০০০ টাকা উপার্জনের সুযোগ।</p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-black text-orange-600">১০,০০০ ৳</span>
                    <span class="text-[11px] text-slate-400 block">১০০ সপ্তাহে ১৫,০০০ ৳ রিটার্ন</span>
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
            <h3 class="text-base font-bold text-slate-900 mb-4">SBL Marketing Plan (৫টি উপার্জনের ধারা)</h3>
            
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
                    <p class="text-xs text-slate-500">সেলস টিম গঠন করে বিভিন্ন পদবী অর্জনের মাধ্যমে ৪০ লক্ষ টাকা পর্যন্ত নগদ পুরস্কার</p>
                </div>
                <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded-lg">Up to 40,00,000 ৳</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-5">পদবী (Rank Code)</th>
                            <th class="py-3 px-5">পদবীর পূর্ণ নাম</th>
                            <th class="py-3 px-5">অর্জনের শর্ত (Requirement)</th>
                            <th class="py-3 px-5 text-right">নগদ পুরস্কার (Cash Incentive)</th>
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
                                    {{ number_format($rank->incentive_amount, 0) }} ৳
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
                <h3 class="text-base font-bold text-slate-900">১০-জেনারেশন অ্যাফিলিয়েট কমিশন ম্যাট্রিক্স</h3>
                <p class="text-xs text-slate-500">যদি প্রতি ব্যক্তি ১০ জন রেফার করে পারফেক্ট গ্রোথ হয় (১০ লেভেল হিসাব)</p>
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
                                <td class="py-2.5 px-5 text-right font-bold text-emerald-700">{{ $gen['commission'] }} ৳</td>
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
                <h3 class="text-xl font-bold text-slate-900 mt-1">কাউন্সেলিং - ১: ইনভেস্টর বনাম নেটওয়ার্কার পিচ গাইড</h3>
                <p class="text-xs text-slate-500">লিডের ধরন (Investor vs Networker) বুঝে সঠিক সুবিধাগুলো তুলে ধরুন।</p>
            </div>

            <!-- Side-by-side Comparative Table (Page 6) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Investor Box -->
                <div class="bg-orange-50/40 rounded-2xl border-2 border-orange-200 p-6 space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-orange-600 text-white font-bold flex items-center justify-center text-sm">I1</span>
                        <div>
                            <h4 class="font-bold text-base text-slate-900">Investor (বিনিয়োগকারী মাইন্ডসেট)</h4>
                            <span class="text-xs text-orange-700">ফোকাস: ক্যাপিটাল সুরক্ষা, স্থায়ী রিটার্ন ও ব্র্যান্ডিং</span>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs text-slate-700">
                        <div class="p-3 bg-white rounded-xl border border-orange-100">
                            <span class="font-bold text-slate-900 block mb-0.5">১. বিনিয়োগের নিরাপত্তা:</span>
                            সাপ্তাহিক গ্যারান্টিড রিটার্ন (১.৭৫% বা ২%) এবং ক্রাউডফান্ডিং সুযোগ।
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-orange-100">
                            <span class="font-bold text-slate-900 block mb-0.5">২. লিগ্যাল এবং অথেন্টিক বিজনেস:</span>
                            রিয়েল ই-কমার্স প্রোডাক্ট, শপিফাই স্টোর ও স্বচ্ছ চুক্তি।
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-orange-100">
                            <span class="font-bold text-slate-900 block mb-0.5">৩. নিজের বিজনেস ব্র্যান্ডিং - স্থায়ী ইনকাম:</span>
                            ১০০ সপ্তাহ পর আজীবন মাসিক ৫,০০০ থেকে ১,০০,০০০ টাকা প্রফিট শেয়ারিং।
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-orange-100">
                            <span class="font-bold text-slate-900 block mb-0.5">৪. ডিজিটাল এসেট এর কন্ট্রোলিং এবং ভ্যালু:</span>
                            ডিজিটাল স্টোর ও অডিয়েন্স ভ্যালু যা সময়ের সাথে বৃদ্ধি পায়।
                        </div>
                    </div>
                </div>

                <!-- Networker Box -->
                <div class="bg-purple-50/40 rounded-2xl border-2 border-purple-200 p-6 space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-purple-600 text-white font-bold flex items-center justify-center text-sm">A1</span>
                        <div>
                            <h4 class="font-bold text-base text-slate-900">Networker (টিম ও অ্যাফিলিয়েট মাইন্ডসেট)</h4>
                            <span class="text-xs text-purple-700">ফোকাস: দ্রুত ক্যাশফ্লো, টিম ম্যাচিং ও বড় পুরস্কার</span>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs text-slate-700">
                        <div class="p-3 bg-white rounded-xl border border-purple-100">
                            <span class="font-bold text-slate-900 block mb-0.5">১. একাধিক সেন্টার নেয়ার সুবিধা:</span>
                            মাল্টিপল ট্রাই-পড বা আইডি নিয়ে কাজ করে আয় কয়েকগুণ করার সুযোগ।
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-purple-100">
                            <span class="font-bold text-slate-900 block mb-0.5">২. আগে আসার সুবিধা:</span>
                            টিম স্পিলওভার (Spillover) পেয়ে বাইনারি টিম দ্রুত দাঁড় করানোর সুবিধা।
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-purple-100">
                            <span class="font-bold text-slate-900 block mb-0.5">৩. ডেইলি ৫০০০০ টাকা ইনকাম (Pair Reward):</span>
                            প্রতি পেয়ার ৫০০ টাকা হারে দিনে সর্বোচ্চ ১০০ পেয়ার পর্যন্ত আয়।
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-purple-100">
                            <span class="font-bold text-slate-900 block mb-0.5">৪. স্থায়ী ইনকাম এর সুযোগ - টিম ওয়ার্ক:</span>
                            ১০% স্পট কমিশন, ০.২৫% উইকলি রেফার রিটার্ন এবং ৪০ লাখ টাকা পর্যন্ত র‍্যাঙ্ক বোনাস।
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: LIVE INTERACTIVE CALCULATOR (PDF Page 1, 2, 3, 7) -->
    <div x-show="activeTab === 'calculator'" class="space-y-6" x-cloak 
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
                        <h3 class="text-base font-bold text-slate-900 mt-1">ইনভেস্টমেন্ট রিটার্ন ক্যালকুলেটর (100 Weeks)</h3>
                        <p class="text-xs text-slate-500">ডেভেলপমেন্ট ফি বাদে মূল বিনিয়োগের ওপর সাপ্তাহিক ও মাসিক রিটার্ন হিসাব।</p>
                    </div>

                    <!-- Package Type Toggle -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">প্যাকেজ নির্বাচন করুন:</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="packageType = 'national'; if(packageAmount > 490000 || packageAmount < 100000) packageAmount = 120000;"
                                    :class="packageType === 'national' ? 'bg-orange-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-2.5 px-3 rounded-xl text-xs transition-all text-left">
                                <span class="block font-bold">National Package</span>
                                <span class="text-[11px] opacity-90">১.৭৫%/সপ্তাহ • ফি ২০,০০০ ৳</span>
                            </button>
                            <button type="button" @click="packageType = 'international'; if(packageAmount < 500000) packageAmount = 550000;"
                                    :class="packageType === 'international' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-2.5 px-3 rounded-xl text-xs transition-all text-left">
                                <span class="block font-bold">International Package</span>
                                <span class="text-[11px] opacity-90">২.০০%/সপ্তাহ • ফি ৫০,০০০ ৳</span>
                            </button>
                        </div>
                    </div>

                    <!-- Package Amount Input -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">মোট প্যাকেজ মূল্য (টাকায়):</label>
                            <span class="text-[11px] font-medium text-slate-500">
                                ফি বাদ দিয়ে মূল ইনভেস্ট: <strong class="text-orange-600" x-text="coreInvestment.toLocaleString('en-IN') + ' ৳'"></strong>
                            </span>
                        </div>
                        <input type="number" inputmode="numeric" x-model.number="packageAmount" step="10000" class="w-full text-base font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
                        
                        <!-- Quick Presets -->
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <button type="button" @click="packageType = 'national'; packageAmount = 120000" 
                                    :class="packageType === 'national' && packageAmount === 120000 ? 'border-orange-500 bg-orange-50 text-orange-800 font-bold' : 'bg-slate-100 text-slate-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-medium border border-transparent hover:bg-slate-200 transition-all">
                                ১,২০,০০০ ৳ (National)
                            </button>
                            <button type="button" @click="packageType = 'national'; packageAmount = 250000" 
                                    :class="packageType === 'national' && packageAmount === 250000 ? 'border-orange-500 bg-orange-50 text-orange-800 font-bold' : 'bg-slate-100 text-slate-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-medium border border-transparent hover:bg-slate-200 transition-all">
                                ২,৫০,০০০ ৳
                            </button>
                            <button type="button" @click="packageType = 'international'; packageAmount = 550000" 
                                    :class="packageType === 'international' && packageAmount === 550000 ? 'border-purple-500 bg-purple-50 text-purple-800 font-bold' : 'bg-slate-100 text-slate-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-medium border border-transparent hover:bg-slate-200 transition-all">
                                ৫,৫০,০০০ ৳ (International)
                            </button>
                            <button type="button" @click="packageType = 'international'; packageAmount = 1000000" 
                                    :class="packageType === 'international' && packageAmount === 1000000 ? 'border-purple-500 bg-purple-50 text-purple-800 font-bold' : 'bg-slate-100 text-slate-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-medium border border-transparent hover:bg-slate-200 transition-all">
                                ১০,০০,০০০ ৳
                            </button>
                        </div>
                    </div>

                    <!-- Development Charge Clarification Notice -->
                    <div class="p-3 bg-amber-50/80 border border-amber-200 rounded-xl text-xs text-amber-900 space-y-1">
                        <div class="flex items-center justify-between font-semibold">
                            <span>🛠️ ডেভেলপমেন্ট চার্জ (অফেরতযোগ্য):</span>
                            <span class="font-bold text-amber-950" x-text="devFee.toLocaleString('en-IN') + ' ৳'"></span>
                        </div>
                        <p class="text-[11px] text-amber-800 leading-relaxed">
                            * এটি মূল ইনভেস্টমেন্ট নয় (ওয়েবসাইট ও টেকনিক্যাল সেটআপ ফি)। তাই রিটার্ন গণনা হবে মূল ইনভেস্ট 
                            <strong x-text="coreInvestment.toLocaleString('en-IN') + ' ৳'"></strong>-এর ওপর।
                        </p>
                    </div>
                </div>

                <!-- Live Results Display -->
                <div class="p-4 bg-slate-900 text-white rounded-2xl space-y-3 text-xs shadow-md">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <span class="text-slate-400">মূল বিনিয়োগ (Core Capital):</span>
                        <span class="font-bold text-white text-sm" x-text="coreInvestment.toLocaleString('en-IN') + ' ৳'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300 font-medium">সাপ্তাহিক রিটার্ন:</span>
                        <span class="font-extrabold text-orange-400 text-base" x-text="weeklyEarning.toLocaleString('en-IN') + ' ৳ / সপ্তাহ'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300 font-medium">মাসিক আনুমানিক রিটার্ন:</span>
                        <span class="font-bold text-emerald-400 text-sm" x-text="monthlyEarning.toLocaleString('en-IN') + ' ৳ / মাস'"></span>
                    </div>
                    <div class="border-t border-slate-800 pt-2 flex items-center justify-between">
                        <div>
                            <span class="text-slate-200 font-bold block">১০০ সপ্তাহে মোট রিটার্ন:</span>
                            <span class="text-[10px] text-slate-400">প্রদত্ত হার অনুযায়ী আনুমানিক হিসাব</span>
                        </div>
                        <span class="font-black text-emerald-400 text-xl" x-text="totalReturn100Weeks.toLocaleString('en-IN') + ' ৳'"></span>
                    </div>
                    <div class="border-t border-slate-800 pt-2 flex items-center justify-between text-[11px]">
                        <span class="text-slate-400">নিট প্রফিট (মোট প্যাকেজ বাদে):</span>
                        <span class="font-bold text-emerald-300" x-text="netProfitTotal.toLocaleString('en-IN') + ' ৳'"></span>
                    </div>
                </div>
            </div>

            <!-- 2. Direct Referral & Spot Commission Simulator -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-md uppercase">Direct Referral</span>
                        <h3 class="text-base font-bold text-slate-900 mt-1">ডিরেক্ট রেফারেল কমিশন (১ম জেনারেশন)</h3>
                        <p class="text-xs text-slate-500">আপনার ডিরেক্ট রেফারেন্সে কোনো প্রজেক্ট যুক্ত হলে তাৎক্ষণিক ও সাপ্তাহিক আয়।</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">রেফারেন্সকৃত প্রজেক্ট অ্যামাউন্ট (টাকায়):</label>
                        <input type="number" inputmode="numeric" x-model.number="referralAmount" step="10000" class="w-full text-base font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
                        
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <button type="button" @click="referralAmount = 10000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">১০,০০০ ৳</button>
                            <button type="button" @click="referralAmount = 120000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">১,২০,০০০ ৳</button>
                            <button type="button" @click="referralAmount = 250000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">২,৫০,০০০ ৳</button>
                            <button type="button" @click="referralAmount = 550000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">৫,৫০,০০০ ৳</button>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <!-- Spot Commission 10% -->
                        <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl flex items-center justify-between">
                            <div>
                                <span class="font-bold text-emerald-900 block text-sm">১০% স্পট কমিশন (তাৎক্ষণিক)</span>
                                <span class="text-[11px] text-emerald-700">প্রজেক্ট শুরুর সাথে সাথে ওয়ালেটে প্রদেয়</span>
                            </div>
                            <span class="text-2xl font-black text-emerald-700" x-text="spotCommission.toLocaleString('en-IN') + ' ৳'"></span>
                        </div>

                        <!-- Refer Return 0.25% -->
                        <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-2xl space-y-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-blue-900 block">০.২৫% সাপ্তাহিক রেফার রিটার্ন</span>
                                    <span class="text-[11px] text-blue-700">১০০ সপ্তাহ পর্যন্ত প্রতি সপ্তাহে প্রদেয়</span>
                                </div>
                                <span class="text-lg font-bold text-blue-700" x-text="weeklyReferReturn.toLocaleString('en-IN') + ' ৳ / সপ্তাহ'"></span>
                            </div>
                            <div class="border-t border-blue-200 pt-1.5 flex items-center justify-between text-[11px] text-blue-800">
                                <span>১০০ সপ্তাহে মোট রেফার রিটার্ন:</span>
                                <span class="font-bold text-blue-900" x-text="totalReferReturn100Weeks.toLocaleString('en-IN') + ' ৳'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 space-y-1">
                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                        <span>💡 পেয়ার রিওয়ার্ড (Pair Reward):</span>
                    </div>
                    <p class="text-[11px] leading-relaxed">
                        সেলস টিম গঠন করে প্রতি পেয়ারে পাবেন ৫০০ টাকা (দৈনিক সর্বোচ্চ ১০০ পেয়ার = ৫০,০০০ টাকা পর্যন্ত ক্যাশ ইনকাম)।
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
                        <h3 class="text-lg font-bold text-slate-900">১০-জেনারেশন কমিশন সিমুলেটর (10-Generation Matrix)</h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">আপনার সম্পূর্ণ ১০ স্তরের টিম ও নেটওয়ার্কের কমিশন প্রজেকশন সিমুলেট করুন।</p>
                </div>

                <!-- Simulation Mode Buttons -->
                <div class="flex items-center gap-2 text-xs">
                    <button type="button" @click="activeGenView = 'matrix'"
                            :class="activeGenView === 'matrix' ? 'bg-orange-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        টিম ম্যাট্রিক্স সিমুলেশন
                    </button>
                    <button type="button" @click="activeGenView = 'single'"
                            :class="activeGenView === 'single' ? 'bg-orange-600 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        একক প্রজেক্ট বণ্টন (Single Project)
                    </button>
                </div>
            </div>

            <!-- Matrix Mode Controls -->
            <div x-show="activeGenView === 'matrix'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">গড় প্যাকেজ / মেম্বারশিপ সাইজ (টাকায়):</label>
                        <input type="number" inputmode="numeric" x-model.number="teamPackageAmount" step="1000" class="w-full text-sm font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2 bg-white">
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            <button type="button" @click="teamPackageAmount = 10000" class="px-2 py-0.5 bg-white border border-slate-200 text-slate-700 rounded text-[11px] font-medium hover:border-orange-500">১০,০০০ ৳ (PDF Default)</button>
                            <button type="button" @click="teamPackageAmount = 120000" class="px-2 py-0.5 bg-white border border-slate-200 text-slate-700 rounded text-[11px] font-medium hover:border-orange-500">১,২০,০০০ ৳ (National)</button>
                            <button type="button" @click="teamPackageAmount = 550000" class="px-2 py-0.5 bg-white border border-slate-200 text-slate-700 rounded text-[11px] font-medium hover:border-orange-500">৫,৫০,০০০ ৳ (International)</button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">জনপ্রতি রেফারেল গুণক (Team Multiplier):</label>
                        <div class="grid grid-cols-4 gap-1.5">
                            <button type="button" @click="teamMultiplier = 2" 
                                    :class="teamMultiplier === 2 ? 'bg-slate-900 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700'"
                                    class="py-2 rounded-xl text-xs text-center transition-all">২×২ টিম</button>
                            <button type="button" @click="teamMultiplier = 3" 
                                    :class="teamMultiplier === 3 ? 'bg-slate-900 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700'"
                                    class="py-2 rounded-xl text-xs text-center transition-all">৩×৩ টিম</button>
                            <button type="button" @click="teamMultiplier = 5" 
                                    :class="teamMultiplier === 5 ? 'bg-slate-900 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700'"
                                    class="py-2 rounded-xl text-xs text-center transition-all">৫×৫ টিম</button>
                            <button type="button" @click="teamMultiplier = 10" 
                                    :class="teamMultiplier === 10 ? 'bg-orange-600 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700'"
                                    class="py-2 rounded-xl text-xs text-center transition-all">১০×১০ টিম (PDF)</button>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">প্রতি ব্যক্তি গড়ে কতজনকে রেফার করবে সেই ভিত্তিতে টিম বৃদ্ধি পাবে।</p>
                    </div>
                </div>

                <!-- Grand Matrix Summary Banner -->
                <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-orange-950 text-white p-5 md:p-6 rounded-2xl border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <span class="text-xs text-orange-400 font-bold uppercase tracking-wider block">
                            ১০ জেনারেশন সর্বমোট সম্ভাব্য কমিশন
                        </span>
                        <div class="text-2xl md:text-3xl font-black text-white mt-1">
                            <span x-text="totalMatrixCommission.toLocaleString('en-IN')"></span> ৳
                        </div>
                        <p class="text-xs text-slate-300 mt-1">
                            গড় প্যাকেজ <span class="font-bold text-orange-300" x-text="teamPackageAmount.toLocaleString('en-IN') + ' ৳'"></span> এবং 
                            <span class="font-bold text-orange-300" x-text="teamMultiplier + '×' + teamMultiplier"></span> ডুপ্লিকেশনে ১০টি লেভেল পূর্ণ হলে।
                        </p>
                    </div>
                    <div class="flex items-center gap-4 text-center">
                        <div class="bg-white/10 px-4 py-2.5 rounded-xl border border-white/10">
                            <span class="text-[10px] text-slate-300 block uppercase">১ম জেনারেশন (ডিরেক্ট)</span>
                            <span class="text-base font-bold text-emerald-400" x-text="getMatrixRow(0).commission.toLocaleString('en-IN') + ' ৳'"></span>
                        </div>
                        <div class="bg-white/10 px-4 py-2.5 rounded-xl border border-white/10">
                            <span class="text-[10px] text-slate-300 block uppercase">২য় - ১০ম জেনারেশন</span>
                            <span class="text-base font-bold text-orange-400" x-text="(totalMatrixCommission - getMatrixRow(0).commission).toLocaleString('en-IN') + ' ৳'"></span>
                        </div>
                    </div>
                </div>

                <!-- 10-Generation Breakdown Table -->
                <div class="overflow-x-auto border border-slate-200/80 rounded-2xl">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">জেনারেশন</th>
                                <th class="py-3 px-4">কমিশন রেট %</th>
                                <th class="py-3 px-4">টিম সদস্য সংখ্যা</th>
                                <th class="py-3 px-4">মোট টিম সেলস / ভলিউম</th>
                                <th class="py-3 px-4 text-right">আপনার কমিশন (টাকায়)</th>
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
                                    <td class="py-3 px-4 text-slate-600" x-text="getMatrixRow(index).volume.toLocaleString('en-IN') + ' ৳'"></td>
                                    <td class="py-3 px-4 text-right font-black text-emerald-700 text-sm" x-text="getMatrixRow(index).commission.toLocaleString('en-IN') + ' ৳'"></td>
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
                        <span class="text-xs font-bold text-slate-900 block">একটি একক প্রজেক্ট বিক্রয় হলে ১০ স্তরে কার কত কমিশন:</span>
                        <p class="text-xs text-slate-500">যেকোনো একটি নির্দিষ্ট প্যাকেজের ক্ষেত্রে আপলাইনে কীভাবে ১০ লেভেলে কমিশন জমা হয়।</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-700">প্রজেক্ট অ্যামাউন্ট:</label>
                        <input type="number" inputmode="numeric" x-model.number="referralAmount" step="10000" class="w-36 text-sm font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-1.5 bg-white">
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-200/80 rounded-2xl">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">আপলাইন লেভেল</th>
                                <th class="py-3 px-4">কমিশন রেট %</th>
                                <th class="py-3 px-4">প্রজেক্ট ভলিউম</th>
                                <th class="py-3 px-4 text-right">প্রদেয় কমিশন (টাকায়)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(gen, index) in genRates" :key="'single-' + index">
                                <tr class="hover:bg-slate-50/80" :class="index === 0 ? 'bg-emerald-50/50' : ''">
                                    <td class="py-2.5 px-4 font-bold text-slate-900">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold"
                                              :class="index === 0 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-800'"
                                              x-text="gen.gen"></span>
                                        <span class="ml-2" x-text="index === 0 ? '১ম জেনারেশন (স্পন্সর)' : index + 1 + 'ম আপলাইন লেভেল'"></span>
                                    </td>
                                    <td class="py-2.5 px-4 font-bold text-orange-600" x-text="gen.rate + '%'"></td>
                                    <td class="py-2.5 px-4 text-slate-700" x-text="referralAmount.toLocaleString('en-IN') + ' ৳'"></td>
                                    <td class="py-2.5 px-4 text-right font-black text-emerald-700" x-text="Math.round(referralAmount * (gen.rate / 100)).toLocaleString('en-IN') + ' ৳'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

    <!-- TAB 5: ECOSYSTEM WEBSITES -->
    <div x-show="activeTab === 'ecosystem'" class="space-y-6" x-cloak>
        <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-orange-950 text-white p-6 rounded-2xl border border-slate-800 shadow-md flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2">
                <span class="px-3 py-1 bg-orange-600/30 text-orange-400 border border-orange-500/30 rounded-full text-xs font-bold uppercase tracking-wider">
                    Official SBL Ecosystem Portals
                </span>
                <h2 class="text-xl md:text-2xl font-bold tracking-tight">গুরুত্বপূর্ণ ওয়েবসাইট ও লিঙ্ক ডিরেক্টরি</h2>
                <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                    ক্লায়েন্ট কাউন্সেলিং বা নিজের প্রয়োজনে SBL-এর সকল অফিসিয়াল ওয়েবসাইট, ড্রপশিপিং শপ, ইনভেস্টর পোর্টাল ও কমিউনিটি চ্যানেল ভিজিট করুন।
                </p>
            </div>
            <a href="{{ route('ecosystem.index') }}" class="px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center gap-2 flex-shrink-0">
                <span>Manage All Links</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>

        @php
            $ecosystemLinks = \App\Models\EcosystemLink::where('is_active', true)->orderBy('sort_order')->get();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($ecosystemLinks as $el)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between hover:shadow-md hover:border-orange-200 transition-all group">
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

                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ $el->url }}" target="_blank" rel="noopener noreferrer" class="w-full px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                        <span>Visit Website</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

