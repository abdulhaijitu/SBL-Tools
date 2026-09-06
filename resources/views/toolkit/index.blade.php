@extends('layouts.app')

@section('page-title', 'SBL Plans & Toolkit')
@section('page-subtitle', 'Official Business Packages, Compensation Models & Counseling Cheatsheet')

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ request('tab', 'packages') }}' }">

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200/80 shadow-xs flex items-center gap-2 overflow-x-auto text-xs font-semibold">
        <button @click="activeTab = 'packages'" 
                :class="activeTab === 'packages' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0">
            <span>📦</span> Dropshipping Packages
        </button>
        <button @click="activeTab = 'compensation'" 
                :class="activeTab === 'compensation' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0">
            <span>💰</span> Compensation & Ranks
        </button>
        <button @click="activeTab = 'counseling'" 
                :class="activeTab === 'counseling' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0">
            <span>🎯</span> Counseling Guide (কাউন্সেলিং-১)
        </button>
        <button @click="activeTab = 'calculator'" 
                :class="activeTab === 'calculator' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 flex-shrink-0">
            <span>🧮</span> Live ROI & Commission Calculator
        </button>
    </div>

    <!-- TAB 1: DROPSHIPPING PACKAGES (PDF Page 1, 3 & 4) -->
    <div x-show="activeTab === 'packages'" class="space-y-6" x-cloak>
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-orange-950 text-white p-6 md:p-8 rounded-2xl border border-slate-800 shadow-md flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2">
                <span class="px-3 py-1 bg-orange-600/30 text-orange-400 border border-orange-500/30 rounded-full text-xs font-bold uppercase tracking-wider">
                    Official SBL Business Model
                </span>
                <h2 class="text-2xl font-bold tracking-tight">অনলাইনে আপনার নিজের একটা ব্যবসা হোক</h2>
                <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                    SBL ড্রপশিপিং মডেলে কোনো অভিজ্ঞতা এবং নিজের কোনো পণ্য স্টক করা ছাড়াই আধুনিক ইকমার্স বিজনেস পরিচালনা করুন।
                </p>
            </div>
            <div class="flex-shrink-0">
                <img src="{{ asset('images/sbl-logo.webp') }}" alt="SBL" class="h-16 w-auto object-contain bg-black/40 p-2 rounded-xl border border-orange-500/30">
            </div>
        </div>

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
                        <p>১,২০,০০০ টাকা বিনিয়োগে প্রতি সপ্তাহে <strong>১,৭৫০ টাকা</strong> করে ১০০ সপ্তাহে মোট মূলধনসহ <strong>১,৭৫,০০০ টাকা</strong> রিটার্ন নিশ্চিত।</p>
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
                        <p>৫,৫০,০০০ টাকা বিনিয়োগে প্রতি সপ্তাহে <strong>১০,০০০ টাকা</strong> করে ১০০ সপ্তাহে মোট মূলধনসহ <strong>১০,০০,০০০ টাকা</strong> রিটার্ন নিশ্চিত।</p>
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($marketComparisons as $row)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-2.5 px-5 font-medium text-slate-800">{{ $row['service'] }}</td>
                                <td class="py-2.5 px-5 text-right text-rose-600 font-semibold">{{ $row['market'] }}</td>
                                <td class="py-2.5 px-5 text-right text-emerald-600 font-bold">অন্তর্ভুক্ত (Included)</td>
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
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-center text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">মাস</th>
                            <th class="py-3 px-4">অ্যাড বাজেট</th>
                            <th class="py-3 px-4">অডিয়েন্স সাইজ</th>
                            <th class="py-3 px-4">মাসিক অর্ডার রেকর্ড</th>
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

    <!-- TAB 4: LIVE INTERACTIVE CALCULATOR (PDF Page 1, 2, 3) -->
    <div x-show="activeTab === 'calculator'" class="space-y-6" x-cloak 
         x-data="{
            packageType: 'national',
            investmentAmount: 120000,
            referralAmount: 120000,
            get weeklyRate() {
                return this.packageType === 'national' ? 0.0175 : 0.02;
            },
            get weeklyEarning() {
                return Math.round(this.investmentAmount * this.weeklyRate);
            },
            get monthlyEarning() {
                return Math.round(this.weeklyEarning * 4.33);
            },
            get totalReturn100Weeks() {
                return Math.round(this.weeklyEarning * 100);
            },
            get netProfit() {
                return Math.max(0, this.totalReturn100Weeks - this.investmentAmount);
            },
            get spotCommission() {
                return Math.round(this.referralAmount * 0.10);
            },
            get weeklyReferReturn() {
                return Math.round(this.referralAmount * 0.0025);
            },
            get totalReferReturn100Weeks() {
                return Math.round(this.weeklyReferReturn * 100);
            }
         }">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Investment ROI Simulator -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">ইনভেস্টমেন্ট রিটার্ন ক্যালকুলেটর (100 Weeks)</h3>
                    <p class="text-xs text-slate-500">বিনিয়োগকৃত মূলধনের ওপর সাপ্তাহিক রিটার্ন এবং ২৪ মাসের মোট আয় হিসাব করুন।</p>
                </div>

                <!-- Package Type Toggle -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">প্যাকেজ নির্বাচন করুন:</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="packageType = 'national'; if(investmentAmount > 490000) investmentAmount = 120000;"
                                :class="packageType === 'national' ? 'bg-orange-600 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                class="py-2 px-3 rounded-xl text-xs transition-all">
                            National Package (1.75%/wk)
                        </button>
                        <button type="button" @click="packageType = 'international'; if(investmentAmount < 500000) investmentAmount = 550000;"
                                :class="packageType === 'international' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                class="py-2 px-3 rounded-xl text-xs transition-all">
                            International Package (2%/wk)
                        </button>
                    </div>
                </div>

                <!-- Investment Amount Input -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">বিনিয়োগের পরিমাণ (টাকায়):</label>
                    <input type="number" x-model.number="investmentAmount" step="10000" class="w-full text-base font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
                    
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <button type="button" @click="packageType = 'national'; investmentAmount = 120000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">১,২০,০০০ ৳</button>
                        <button type="button" @click="packageType = 'national'; investmentAmount = 250000" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">২,৫০,০০০ ৳</button>
                        <button type="button" @click="packageType = 'international'; investmentAmount = 550000" class="px-2.5 py-1 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg text-xs font-medium">৫,৫০,০০০ ৳</button>
                        <button type="button" @click="packageType = 'international'; investmentAmount = 1000000" class="px-2.5 py-1 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg text-xs font-medium">১০,০০,০০০ ৳</button>
                    </div>
                </div>

                <!-- Live Results Display -->
                <div class="p-4 bg-orange-50/70 border border-orange-200 rounded-2xl space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 font-medium">সাপ্তাহিক রিটার্ন:</span>
                        <span class="font-extrabold text-orange-600 text-base" x-text="weeklyEarning.toLocaleString('en-IN') + ' ৳ / সপ্তাহ'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 font-medium">মাসিক আনুমানিক রিটার্ন:</span>
                        <span class="font-bold text-slate-800" x-text="monthlyEarning.toLocaleString('en-IN') + ' ৳ / মাস'"></span>
                    </div>
                    <div class="border-t border-orange-200 pt-2 flex items-center justify-between">
                        <span class="text-slate-700 font-bold">১০০ সপ্তাহে মোট রিটার্ন (২৪ মাস):</span>
                        <span class="font-black text-emerald-700 text-lg" x-text="totalReturn100Weeks.toLocaleString('en-IN') + ' ৳'"></span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span>নিট প্রফিট (মূলধন বাদে):</span>
                        <span class="font-bold text-slate-700" x-text="netProfit.toLocaleString('en-IN') + ' ৳'"></span>
                    </div>
                </div>
            </div>

            <!-- Referral & Commission Simulator -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">রেফারেল ও মার্কেটিং কমিশন সিমুলেটর</h3>
                    <p class="text-xs text-slate-500">আপনার মাধ্যমে কোনো প্রজেক্ট রেফার হলে তৎক্ষণাৎ এবং সাপ্তাহিক কমিশন হিসাব।</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">রেফারেন্সকৃত প্রজেক্ট অ্যামাউন্ট (টাকায়):</label>
                    <input type="number" x-model.number="referralAmount" step="10000" class="w-full text-base font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
                </div>

                <div class="space-y-3 text-xs">
                    <!-- Spot Commission 10% -->
                    <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl flex items-center justify-between">
                        <div>
                            <span class="font-bold text-emerald-900 block">১০% স্পট কমিশন (তাৎক্ষণিক)</span>
                            <span class="text-[11px] text-emerald-700">প্রজেক্ট শুরুর সাথে সাথে প্রদেয়</span>
                        </div>
                        <span class="text-xl font-black text-emerald-700" x-text="spotCommission.toLocaleString('en-IN') + ' ৳'"></span>
                    </div>

                    <!-- Refer Return 0.25% -->
                    <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-2xl space-y-1.5">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-bold text-blue-900 block">০.২৫% সাপ্তাহিক রেফার রিটার্ন</span>
                                <span class="text-[11px] text-blue-700">১০০ সপ্তাহ পর্যন্ত প্রতি সপ্তাহে প্রদেয়</span>
                            </div>
                            <span class="text-lg font-bold text-blue-700" x-text="weeklyReferReturn.toLocaleString('en-IN') + ' ৳ / সপ্তাহ'"></span>
                        </div>
                        <div class="border-t border-blue-200 pt-1.5 flex items-center justify-between text-[11px] text-blue-800">
                            <span>১০০ সপ্তাহে মোট রেফার রিটার্ন:</span>
                            <span class="font-bold" x-text="totalReferReturn100Weeks.toLocaleString('en-IN') + ' ৳'"></span>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600">
                    💡 <em>পেয়ার রিওয়ার্ড:</em> সেলস টিম গঠন করে প্রতি পেয়ারে পাবেন ৫০০ টাকা (দৈনিক সর্বোচ্চ ৫০,০০০ টাকা পর্যন্ত)।
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

