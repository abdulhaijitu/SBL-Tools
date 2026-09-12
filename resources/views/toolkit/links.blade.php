@extends('layouts.app')

@section('meta-title', 'SBL Links & Resources | Official Websites & Tools')
@section('page-title', 'Links & Resources')
@section('page-subtitle', 'Official websites, business tools and useful SBL resources in one place.')
@section('meta-description', 'Official SBL website directory, member portals, dropshipping stores and marketing tools.')

@section('content')
@php
    $ecosystemLinks = \App\Models\EcosystemLink::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

    // Standardize category and clean domain/path display for each link
    $formattedLinks = $ecosystemLinks->map(function($el) {
        $rawUrl = $el->url;
        $cleanUrl = preg_replace('#^https?://(www\.)?#i', '', rtrim($rawUrl, '/'));
        
        $category = 'Official';
        $catLower = strtolower($el->category . ' ' . $el->title . ' ' . $rawUrl);
        
        if (str_contains($catLower, 'office') || str_contains($catLower, 'backoffice') || str_contains($catLower, 'member')) {
            $category = 'Backoffice';
        } elseif (str_contains($catLower, 'wa.me') || str_contains($catLower, 'whatsapp') || str_contains($catLower, 'support')) {
            $category = 'Support';
            $cleanUrl = 'WhatsApp';
        } elseif (str_contains($catLower, 'facebook') || str_contains($catLower, 'community')) {
            $category = 'Community';
            $cleanUrl = 'Facebook Group';
        } elseif (str_contains($catLower, 'academy') || str_contains($catLower, 'training')) {
            $category = 'Training';
        } elseif (str_contains($catLower, 'shop') || str_contains($catLower, 'invest') || str_contains($catLower, 'business') || str_contains($catLower, 'commerce')) {
            $category = 'Business';
        } elseif ($el->is_official || str_contains($catLower, 'portal') || str_contains($catLower, 'official')) {
            $category = 'Official';
        }

        return [
            'id' => $el->id,
            'title' => $el->title,
            'url' => $el->url,
            'cleanUrl' => $cleanUrl,
            'category' => $category,
            'rawCategory' => $el->category,
            'description' => $el->description ?: 'Official SBL platform resource.',
            'icon' => $el->icon ?: '🌐',
            'is_official' => (bool)$el->is_official,
            'verification_status' => $el->verification_status ?: 'verified',
            'sort_order' => $el->sort_order,
            'badge' => $el->badge ?: '',
        ];
    });

    $marketingTools = [
        [
            'id' => 'tool-packages',
            'title' => 'Packages',
            'fullName' => 'SBL Packages',
            'purpose' => 'Package information and presentation',
            'url' => route('packages.index'),
            'category' => 'Tools',
            'icon' => '📦',
            'type' => 'Internal Tool',
        ],
        [
            'id' => 'tool-commission',
            'title' => 'Commission Plan',
            'fullName' => 'Commission Plan',
            'purpose' => 'Commission information and calculator',
            'url' => route('commission.index'),
            'category' => 'Tools',
            'icon' => '💰',
            'type' => 'Internal Tool',
        ],
        [
            'id' => 'tool-ranks',
            'title' => 'Ranks',
            'fullName' => 'Career Ranks',
            'purpose' => 'Career rank information',
            'url' => route('ranks.index'),
            'category' => 'Tools',
            'icon' => '🏆',
            'type' => 'Internal Tool',
        ],
        [
            'id' => 'tool-counseling',
            'title' => 'Counseling Guide',
            'fullName' => 'Counseling Guide',
            'purpose' => 'Prospect counseling assistant',
            'url' => route('counseling.index'),
            'category' => 'Tools',
            'icon' => '🧭',
            'type' => 'Internal Tool',
        ],
        [
            'id' => 'tool-presentation',
            'title' => 'Presentation',
            'fullName' => 'Presentation Resources',
            'purpose' => 'SBL presentation resources',
            'url' => route('presentations.index'),
            'category' => 'Tools',
            'icon' => '📽️',
            'type' => 'Internal Tool',
        ],
        [
            'id' => 'tool-leads',
            'title' => 'Leads',
            'fullName' => 'Lead Management',
            'purpose' => 'Lead management',
            'url' => route('leads.index'),
            'category' => 'Tools',
            'icon' => '👥',
            'type' => 'Internal Tool',
        ],
    ];
@endphp

<div 
    x-data="{
        searchQuery: '',
        selectedCategory: 'All',
        links: @js($formattedLinks),
        tools: @js($marketingTools),
        copiedToast: false,
        copiedToastMessage: '',
        createModalOpen: false,
        editModalOpen: false,
        editingLink: { id: null, title: '', url: '', category: 'Official Portals', badge: '', description: '', icon: '🌐', sort_order: 0 },

        copyLink(url, title = 'Link') {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url);
            } else {
                let el = document.createElement('textarea');
                el.value = url;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
            }
            this.showToast((title ? title + ' ' : '') + 'copied to clipboard!');
        },

        showToast(msg) {
            this.copiedToastMessage = msg;
            this.copiedToast = true;
            setTimeout(() => { this.copiedToast = false; }, 2400);
        },

        openEditModal(link) {
            this.editingLink = Object.assign({}, link);
            this.editModalOpen = true;
        },

        get filteredLinks() {
            return this.links.filter(l => {
                if (this.selectedCategory !== 'All' && this.selectedCategory !== 'Tools') {
                    if (l.category !== this.selectedCategory) return false;
                }
                if (this.selectedCategory === 'Tools') {
                    return false;
                }
                if (this.searchQuery.trim()) {
                    let q = this.searchQuery.toLowerCase().trim();
                    let match = (l.title || '').toLowerCase().includes(q) ||
                                (l.url || '').toLowerCase().includes(q) ||
                                (l.cleanUrl || '').toLowerCase().includes(q) ||
                                (l.category || '').toLowerCase().includes(q) ||
                                (l.description || '').toLowerCase().includes(q);
                    if (!match) return false;
                }
                return true;
            });
        },

        get filteredTools() {
            return this.tools.filter(t => {
                if (this.selectedCategory !== 'All' && this.selectedCategory !== 'Tools') {
                    return false;
                }
                if (this.searchQuery.trim()) {
                    let q = this.searchQuery.toLowerCase().trim();
                    let match = (t.title || '').toLowerCase().includes(q) ||
                                (t.fullName || '').toLowerCase().includes(q) ||
                                (t.purpose || '').toLowerCase().includes(q) ||
                                (t.category || '').toLowerCase().includes(q);
                    if (!match) return false;
                }
                return true;
            });
        }
    }"
    class="space-y-6 sm:space-y-7 pb-16 antialiased"
>
    <!-- Notification Toast -->
    <div 
        x-show="copiedToast" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-3"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 bg-slate-900 text-white text-xs sm:text-sm font-medium px-4 py-3 rounded-xl shadow-xl border border-slate-700/50"
        style="display: none;"
    >
        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
        </svg>
        <span x-text="copiedToastMessage"></span>
    </div>

    <!-- ==========================================
         1. COMPACT PAGE HEADER
         ========================================== -->
    <header class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Official SBL Links & Resources
                </div>
                <h1 class="text-2xl sm:text-[28px] font-extrabold text-slate-900 tracking-tight leading-tight">
                    SBL Links & Resources
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 max-w-2xl leading-normal">
                    Official websites, business tools and useful SBL resources in one place.
                </p>
            </div>
            
            @if($canManage ?? false)
            <div class="flex items-center gap-2.5 shrink-0 pt-1 md:pt-0">
                <button 
                    type="button" 
                    @click="createModalOpen = true" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-xs transition-colors cursor-pointer min-h-[44px]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Add Link</span>
                </button>
                <a 
                    href="{{ route('ecosystem.index') }}" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 shadow-xs transition-colors min-h-[44px]"
                >
                    <span>Manage</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
            @endif
        </div>
    </header>

    <!-- ==========================================
         2. SEARCH + CATEGORY FILTER
         ========================================== -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-5 shadow-xs space-y-3.5">
        <!-- Live Search Field -->
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input 
                type="text" 
                x-model="searchQuery" 
                placeholder="Search links or tools..." 
                class="w-full pl-10 pr-9 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition-all min-h-[44px]"
            >
            <button 
                type="button" 
                x-show="searchQuery" 
                @click="searchQuery = ''" 
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-xs font-bold cursor-pointer"
                style="display: none;"
            >
                ✕
            </button>
        </div>

        <!-- Horizontal Scrollable Filter Chips -->
        <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1 scrollbar-none">
            <template x-for="cat in ['All', 'Official', 'Business', 'Backoffice', 'Tools', 'Training', 'Support', 'Community']" :key="cat">
                <button 
                    type="button" 
                    @click="selectedCategory = cat" 
                    :class="selectedCategory === cat ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition-colors shrink-0 cursor-pointer min-h-[36px] flex items-center justify-center"
                    x-text="cat"
                ></button>
            </template>
        </div>
    </div>

    <!-- ==========================================
         3. SECTION A: SBL LINKS & RESOURCES
         ========================================== -->
    <section x-show="filteredLinks.length > 0 || (selectedCategory !== 'Tools' && !searchQuery)" class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                SBL Links & Resources
            </h2>
            <span class="text-xs text-slate-500" x-text="filteredLinks.length + ' resources'"></span>
        </div>

        <!-- Desktop Table Layout -->
        <div class="hidden sm:block bg-white border border-slate-200/90 rounded-xl overflow-hidden shadow-xs">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-semibold text-xs tracking-wider uppercase">
                        <th class="py-3 px-4 sm:px-6">Resource</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Link / Type</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="link in filteredLinks" :key="link.id">
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Resource Column -->
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl shrink-0" x-text="link.icon"></span>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-sm leading-snug" x-text="link.title"></div>
                                        <div class="text-xs text-slate-500 truncate max-w-sm lg:max-w-md mt-0.5" x-text="link.description"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Category Column -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md text-xs font-medium" x-text="link.category"></span>
                            </td>

                            <!-- Link / Type Column -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-mono text-xs text-slate-600 font-medium" x-text="link.cleanUrl"></span>
                            </td>

                            <!-- Status Column -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <template x-if="link.verification_status === 'verified' || link.is_official">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Verified
                                    </span>
                                </template>
                                <template x-if="link.verification_status !== 'verified' && !link.is_official">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Active
                                    </span>
                                </template>
                            </td>

                            <!-- Actions Column -->
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a 
                                        :href="link.url" 
                                        target="_blank" 
                                        rel="noopener noreferrer" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors min-h-[32px]"
                                    >
                                        <span>Open</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>

                                    <button 
                                        type="button" 
                                        @click="copyLink(link.url, link.title)" 
                                        class="inline-flex items-center px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg border border-slate-200/80 transition-colors cursor-pointer min-h-[32px]"
                                    >
                                        Copy
                                    </button>

                                    @if($canManage ?? false)
                                    <button 
                                        type="button" 
                                        @click="openEditModal(link)" 
                                        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                        title="Edit Link"
                                    >
                                        ✏️
                                    </button>

                                    <form :action="'{{ url('/ecosystem') }}/' + link.id" method="POST" onsubmit="return confirm('Remove this link?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="Delete Link">
                                            🗑️
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Mobile List Layout (Compact 72-100px items, 360px-430px) -->
        <div class="sm:hidden bg-white border border-slate-200/90 rounded-xl divide-y divide-slate-100 overflow-hidden shadow-xs">
            <template x-for="link in filteredLinks" :key="'m-' + link.id">
                <div class="p-3.5 hover:bg-slate-50/70 transition-colors">
                    <div class="flex items-start justify-between gap-2.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="text-xl shrink-0" x-text="link.icon"></span>
                            <div class="min-w-0">
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 truncate" x-text="link.title"></h3>
                                <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500">
                                    <span class="font-medium text-slate-600" x-text="link.category"></span>
                                    <span class="text-slate-300">•</span>
                                    <span class="font-mono text-slate-400 truncate" x-text="link.cleanUrl"></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <a 
                                :href="link.url" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white min-h-[36px] flex items-center justify-center shadow-xs"
                            >
                                Open
                            </a>
                            <button 
                                type="button" 
                                @click="copyLink(link.url, link.title)" 
                                class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 min-h-[36px] flex items-center justify-center cursor-pointer"
                            >
                                Copy
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State for Section A -->
        <div x-show="filteredLinks.length === 0 && selectedCategory !== 'Tools'" class="bg-white border border-slate-200/90 rounded-xl p-6 text-center text-xs sm:text-sm text-slate-500" style="display: none;">
            No matching resources found in this category.
        </div>
    </section>

    <!-- ==========================================
         4. SECTION B: SBL MARKETING TOOLS
         ========================================== -->
    <section x-show="filteredTools.length > 0" class="space-y-3 pt-2">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                SBL Marketing Tools
            </h2>
            <span class="text-xs text-slate-500" x-text="filteredTools.length + ' tools'"></span>
        </div>

        <!-- Desktop Table Layout -->
        <div class="hidden sm:block bg-white border border-slate-200/90 rounded-xl overflow-hidden shadow-xs">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-semibold text-xs tracking-wider uppercase">
                        <th class="py-3 px-4 sm:px-6 w-1/3">Tool</th>
                        <th class="py-3 px-4 w-1/2">Purpose</th>
                        <th class="py-3 px-4 sm:px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="tool in filteredTools" :key="tool.id">
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Tool Column -->
                            <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-xl shrink-0" x-text="tool.icon"></span>
                                    <div>
                                        <span class="font-bold text-slate-900 text-sm" x-text="tool.title"></span>
                                        <span class="text-xs text-slate-400 block font-normal" x-text="tool.type"></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Purpose Column -->
                            <td class="py-3.5 px-4 text-slate-600">
                                <span x-text="tool.purpose"></span>
                            </td>

                            <!-- Action Column -->
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                <a 
                                    :href="tool.url" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors min-h-[32px]"
                                >
                                    <span>Open</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Mobile List Layout (Compact 72-100px items, 360px-430px) -->
        <div class="sm:hidden bg-white border border-slate-200/90 rounded-xl divide-y divide-slate-100 overflow-hidden shadow-xs">
            <template x-for="tool in filteredTools" :key="'m-' + tool.id">
                <div class="p-3.5 hover:bg-slate-50/70 transition-colors flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="text-xl shrink-0" x-text="tool.icon"></span>
                        <div class="min-w-0">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 truncate" x-text="tool.title"></h3>
                            <p class="text-xs text-slate-500 truncate" x-text="tool.purpose"></p>
                        </div>
                    </div>
                    <a 
                        :href="tool.url" 
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shrink-0 min-h-[36px] flex items-center justify-center shadow-xs"
                    >
                        Open
                    </a>
                </div>
            </template>
        </div>
    </section>

    <!-- Global Empty State if both sections match nothing -->
    <div 
        x-show="filteredLinks.length === 0 && filteredTools.length === 0" 
        class="bg-white border border-slate-200/90 rounded-2xl p-8 text-center space-y-2 shadow-xs"
        style="display: none;"
    >
        <div class="text-2xl">🔍</div>
        <h3 class="text-sm font-bold text-slate-800">No matching resources found</h3>
        <p class="text-xs text-slate-500">Try a different search term or select another category filter above.</p>
        <button 
            type="button" 
            @click="searchQuery = ''; selectedCategory = 'All'" 
            class="mt-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer"
        >
            Reset Filters
        </button>
    </div>

    @if($canManage ?? false)
    <!-- ==========================================
         5. CREATE LINK MODAL (Admin Only)
         ========================================== -->
    <div 
        role="dialog" 
        aria-modal="true" 
        tabindex="-1" 
        x-show="createModalOpen" 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        style="display: none;"
    >
        <div 
            @click.away="createModalOpen = false" 
            x-show="createModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🌐</span>
                    <h3 class="text-base font-bold text-slate-900">Add Ecosystem Website / Resource</h3>
                </div>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('ecosystem.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Platform Name *</label>
                        <input type="text" name="title" required placeholder="e.g. SBL Dropshipping Shop" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Icon / Emoji</label>
                        <input type="text" name="icon" value="🌐" placeholder="🛍️" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none text-center text-lg">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Website URL *</label>
                    <input type="url" name="url" required placeholder="https://example.sbl.com.bd" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="Official Portals">Official Portals</option>
                            <option value="Business & Commerce">Business & Commerce</option>
                            <option value="Affiliate & Community">Affiliate & Community</option>
                            <option value="Support & Training">Support & Training</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                        <input type="text" name="badge" placeholder="e.g. Dropshipping Hub" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief 1-line description of this platform..." class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors cursor-pointer">Save Link</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================
         6. EDIT LINK MODAL (Admin Only)
         ========================================== -->
    <div 
        role="dialog" 
        aria-modal="true" 
        tabindex="-1" 
        x-show="editModalOpen" 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        style="display: none;"
    >
        <div 
            @click.away="editModalOpen = false" 
            x-show="editModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">✏️</span>
                    <h3 class="text-base font-bold text-slate-900">Edit Website / Resource</h3>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form :action="'{{ url('/ecosystem') }}/' + editingLink.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Platform Name *</label>
                        <input type="text" name="title" x-model="editingLink.title" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Icon</label>
                        <input type="text" name="icon" x-model="editingLink.icon" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none text-center text-lg">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Website URL *</label>
                    <input type="url" name="url" x-model="editingLink.url" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="category" x-model="editingLink.rawCategory" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="Official Portals">Official Portals</option>
                            <option value="Business & Commerce">Business & Commerce</option>
                            <option value="Affiliate & Community">Affiliate & Community</option>
                            <option value="Support & Training">Support & Training</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                        <input type="text" name="badge" x-model="editingLink.badge" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" x-model="editingLink.description" rows="2" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors cursor-pointer">Update Link</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
