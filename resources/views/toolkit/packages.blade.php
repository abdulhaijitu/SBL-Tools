@extends('layouts.app')

@section('meta-title', 'Packages | SBL Marketing')
@section('page-title', 'Packages')
@section('page-subtitle', 'SBL Membership & Dropshipping Packages')
@section('meta-description', 'SBL Marketing-এর Membership ও Dropshipping package, সুবিধা, service details, ownership note এবং প্যাকেজ বিস্তারিত এক জায়গায় দেখুন।')

@section('content')
<div class="space-y-6">

        <!-- SBL MEMBERSHIP PACKAGES (Starter ৳১০,০০০, National ৳১,২০,০০০, International ৳৫,৫০,০০০) -->
        @include('toolkit.partials.membership-package')

        <!-- Additional Supporting Sections (Market comparison & 6-month growth) -->
        <div class="max-w-4xl mx-auto space-y-6 pt-4 print:hidden">
            <div class="section-heading">
                <div>
                    <h2>Dropshipping Market Cost Comparison & Projections</h2>
                    <p>Standard agency pricing vs SBL lifetime services</p>
                </div>
            </div>

            <!-- 20k Dropshipping Package vs Market Cost Comparison (PDF Page 4) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">SBL Dropshipping Package Offer</h3>
                        <p class="text-xs text-slate-500">Standard market costs compared to SBL lifetime service offer</p>
                    </div>
                    <div class="px-3.5 py-1.5 bg-emerald-100 text-emerald-800 rounded-xl font-bold text-xs">
                        Only 20,000 BDT - One-time Payment - Lifetime Service
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                            <tr>
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
                    <h3 class="text-sm font-bold text-slate-900">6-Month Growth Projection ($1,000 Scale)</h3>
                    <p class="text-xs text-slate-500">Progressive ad spend, audience reach and monthly order trajectory</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-center text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-200">
                            <tr>
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

</div>
@endsection
