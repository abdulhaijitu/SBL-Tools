@extends('layouts.app')

@section('page-title', 'Abbreviation')
@section('page-subtitle', 'Essential SBL Dropshipping, E-Commerce, Logistics & Marketing Glossary')

@section('content')
<div class="space-y-6" x-data="{
    searchQuery: '',
    selectedCategory: 'all',
    copyToClipboard(text, title) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: title + ' copied to clipboard!', type: 'success' } }));
            });
        }
    },
    matchesFilter(abbr) {
        const catMatch = (this.selectedCategory === 'all') || (abbr.category_slug === this.selectedCategory);
        if (!catMatch) return false;
        if (!this.searchQuery.trim()) return true;
        const q = this.searchQuery.toLowerCase().trim();
        const searchPool = (abbr.code + ' ' + abbr.name + ' ' + abbr.meaning_bn + ' ' + abbr.description_bn + ' ' + abbr.category + ' ' + abbr.tag).toLowerCase();
        return searchPool.includes(q);
    }
}">

    <!-- Page Heading -->
    <div class="section-heading">
        <div>
            <h2>SBL Dropshipping Abbreviations</h2>
            <p>A quick-reference guide and handbook for short forms, terminologies, and operational concepts.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-orange-50 border border-orange-200/80 text-orange-800 text-xs font-bold shadow-xs">
                <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                <span>{{ count($abbreviations) }} Terms Listed</span>
            </span>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        @foreach($categories as $cat)
        <button type="button" 
                @click="selectedCategory = '{{ $cat['slug'] }}'"
                :class="selectedCategory === '{{ $cat['slug'] }}' ? 'bg-orange-700 text-white shadow-xs font-semibold' : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200/80 font-medium'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer">
            <span>{{ $cat['icon'] }}</span>
            <span>{{ $cat['name'] }}</span>
            <span :class="selectedCategory === '{{ $cat['slug'] }}' ? 'bg-orange-800/80 text-orange-100' : 'bg-slate-100 text-slate-500'" class="px-1.5 py-0.5 rounded-md text-[10px] font-mono">
                {{ $cat['count'] }}
            </span>
        </button>
        @endforeach
    </div>

    <!-- Search & Quick Info Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-96">
            <input type="text" 
                   x-model="searchQuery" 
                   placeholder="Search by abbreviation, full form, or meaning (e.g. COD, ROAS, RTO)..." 
                   class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>

        <div class="text-xs text-slate-500 flex items-center gap-3">
            <span class="inline-flex items-center gap-1">
                <kbd class="px-1.5 py-0.5 text-[10px] font-mono bg-slate-100 border border-slate-200 rounded">Click</kbd> any abbreviation to copy
            </span>
        </div>
    </div>

    <!-- Compact Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="bg-slate-50/90 text-slate-500 uppercase text-[11px] font-bold border-b border-slate-200/80 tracking-wider select-none">
                    <tr>
                        <th class="py-3.5 px-4 min-w-[140px]">Short Form</th>
                        <th class="py-3.5 px-4 min-w-[200px]">Full Name & Standard Term</th>
                        <th class="py-3.5 px-4 min-w-[150px]">Category</th>
                        <th class="py-3.5 px-4 min-w-[260px]">বাংলা অর্থ (Meaning)</th>
                        <th class="py-3.5 px-4 min-w-[320px]">ব্যবহার ও প্রায়োগিক ভূমিকা (Use Case)</th>
                        <th class="py-3.5 px-4 text-right min-w-[80px]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($abbreviations as $abbr)
                    <tr x-show="matchesFilter({{ json_encode($abbr) }})" 
                        class="hover:bg-slate-50/75 transition-colors group">
                        
                        <!-- 1. Short Form -->
                        <td class="py-3.5 px-4 align-middle whitespace-nowrap">
                            <button type="button" 
                                    @click="copyToClipboard('{{ $abbr['code'] }}', '{{ $abbr['code'] }}')" 
                                    class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-orange-600 text-white font-mono font-bold text-xs tracking-wider shadow-xs transition-colors cursor-pointer group/btn" 
                                    title="Click to copy {{ $abbr['code'] }}">
                                <span>{{ $abbr['icon'] }}</span>
                                <span class="font-extrabold">{{ $abbr['code'] }}</span>
                                <svg class="w-3 h-3 text-slate-400 group-hover/btn:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                        </td>

                        <!-- 2. Full Name -->
                        <td class="py-3.5 px-4 align-middle">
                            <div class="font-bold text-slate-900 text-sm">
                                {{ $abbr['name'] }}
                            </div>
                            <div class="text-[11px] text-slate-400 font-medium">
                                {{ $abbr['tag'] }}
                            </div>
                        </td>

                        <!-- 3. Category -->
                        <td class="py-3.5 px-4 align-middle whitespace-nowrap">
                            @php
                                $badgeClass = match($abbr['category_slug']) {
                                    'ecommerce' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'marketing' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'logistics' => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'network' => 'bg-orange-50 text-orange-700 border-orange-200',
                                    'finance' => 'bg-slate-100 text-slate-700 border-slate-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                            @endphp
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                                {{ $abbr['category'] }}
                            </span>
                        </td>

                        <!-- 4. Meaning in Bangla -->
                        <td class="py-3.5 px-4 align-middle">
                            <div class="text-sm font-semibold text-slate-800 leading-snug">
                                {{ $abbr['meaning_bn'] }}
                            </div>
                        </td>

                        <!-- 5. Use Case & Explanation -->
                        <td class="py-3.5 px-4 align-middle">
                            <div class="text-xs text-slate-600 leading-relaxed max-w-md">
                                {{ $abbr['description_bn'] }}
                            </div>
                        </td>

                        <!-- 6. Action: Copy -->
                        <td class="py-3.5 px-4 align-middle text-right whitespace-nowrap">
                            <button type="button" 
                                    @click="copyToClipboard('{{ $abbr['code'] }} - {{ $abbr['name'] }}: {{ $abbr['meaning_bn'] }}', 'Definition')" 
                                    title="Copy term & definition" 
                                    class="p-1.5 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
