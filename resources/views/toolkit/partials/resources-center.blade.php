{{-- SBL Marketing Resource Center --}}
<div class="space-y-6"
     x-data="{
        categoryFilter: 'all',
        searchQuery: '',
        sortBy: 'recommended',
        languageFilter: 'all',
        verificationFilter: 'all',
        
        // Pinned / Favorites (Local Storage)
        pinnedResources: JSON.parse(localStorage.getItem('sbl_pinned_resources') || '[]'),
        togglePin(id) {
            id = parseInt(id);
            if (this.pinnedResources.includes(id)) {
                this.pinnedResources = this.pinnedResources.filter(x => x !== id);
            } else {
                this.pinnedResources.push(id);
            }
            localStorage.setItem('sbl_pinned_resources', JSON.stringify(this.pinnedResources));
        },
        isPinned(id) {
            return this.pinnedResources.includes(parseInt(id));
        },

        // Toast feedback
        toastMessage: '',
        toastVisible: false,
        showToast(msg) {
            this.toastMessage = msg;
            this.toastVisible = true;
            setTimeout(() => { this.toastVisible = false; }, 2600);
        },

        // Copy link
        copyLink(url, title = '') {
            const fullUrl = url.startsWith('http') ? url : window.location.origin + url;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(fullUrl).then(() => {
                    this.showToast('✓ Link copied: ' + (title || 'Resource URL'));
                });
            } else {
                const ta = document.createElement('textarea');
                ta.value = fullUrl;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                this.showToast('✓ Link copied!');
            }
        },

        // Share resource
        shareModalOpen: false,
        activeShareResource: null,
        shareResource(res) {
            this.activeShareResource = res;
            const fullUrl = res.file_url.startsWith('http') ? res.file_url : window.location.origin + res.file_url;
            const shareText = `*${res.title}*\nType: ${res.category} (${res.file_type.toUpperCase()})\n${res.description ? res.description + '\n' : ''}Link: ${fullUrl}`;
            
            if (navigator.share) {
                navigator.share({
                    title: res.title,
                    text: res.description || res.title,
                    url: fullUrl
                }).catch(() => {
                    this.shareModalOpen = true;
                });
            } else {
                this.shareModalOpen = true;
            }
        },

        // QR Code Modal
        qrModalOpen: false,
        qrResource: null,
        openQrModal(res) {
            this.qrResource = res;
            this.qrModalOpen = true;
        },

        // Preview Modal
        previewModalOpen: false,
        previewResource: null,
        openPreview(res) {
            this.previewResource = res;
            this.previewModalOpen = true;
        },

        // Curated Kit Modal
        kitModalOpen: false,
        activeKit: null,
        openKitModal(kit) {
            this.activeKit = kit;
            this.kitModalOpen = true;
        },
        copyKitSummary(kit) {
            let summary = `*SBL Toolkit: ${kit.title}*\n${kit.description}\n\nIncluded Resources:\n`;
            this.allResourcesList.forEach(r => {
                if (kit.resource_ids.includes(r.id)) {
                    const u = r.file_url.startsWith('http') ? r.file_url : window.location.origin + r.file_url;
                    summary += `• ${r.title} (${r.file_type.toUpperCase()}): ${u}\n`;
                }
            });
            summary += `\nShared via SBL Marketing Resource Center`;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(summary).then(() => {
                    this.showToast('✓ Kit summary copied to clipboard!');
                });
            }
        },

        // Details Modal
        detailsModalOpen: false,
        detailsResource: null,
        openDetails(res) {
            this.detailsResource = res;
            this.detailsModalOpen = true;
        },

        // Admin Modals
        createModalOpen: false,
        editModalOpen: false,
        editingResource: {
            id: null,
            title: '',
            short_title: '',
            category: 'Leaflets',
            resource_type: 'leaflet',
            file_type: 'pdf',
            file_url: '',
            thumbnail_url: '',
            file_size: '',
            badge: '',
            version: 'v1.0',
            source: '',
            is_official: false,
            verification_status: 'needs_verification',
            issue_date: '',
            expiry_date: '',
            issued_by: '',
            tags: '',
            language: 'bilingual',
            is_featured: false,
            is_counseling_toolkit: false,
            is_public: true,
            is_downloadable: true,
            is_shareable: true,
            status: 'current',
            description: '',
            notes: '',
            sort_order: 0
        },
        openEditModal(res) {
            this.editingResource = Object.assign({}, res);
            this.editModalOpen = true;
        },

        // Master resources list in Alpine
        allResourcesList: {{ Js::from($resources) }},

        // Resource match helper
        matchesResource(res) {
            // Category
            if (this.categoryFilter !== 'all' && res.category !== this.categoryFilter) {
                return false;
            }
            // Language
            if (this.languageFilter !== 'all' && res.language !== this.languageFilter) {
                return false;
            }
            // Verification
            if (this.verificationFilter === 'verified' && !res.is_official) {
                return false;
            }
            if (this.verificationFilter === 'counseling' && !res.is_counseling_toolkit) {
                return false;
            }
            // Search Query
            if (this.searchQuery.trim()) {
                const q = this.searchQuery.toLowerCase().trim();
                const text = `${res.title} ${res.short_title || ''} ${res.description || ''} ${res.category || ''} ${res.tags || ''} ${res.file_type || ''} ${res.badge || ''}`.toLowerCase();
                if (!text.includes(q)) return false;
            }
            return true;
        }
     }">

    <!-- 01. COMPACT HERO HEADER -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-orange-950 text-white p-5 md:p-6 rounded-3xl border border-slate-800 shadow-md">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-orange-600/30 text-orange-400 border border-orange-500/30">
                        <span data-en="SBL Marketing Resource Center" data-bn="এসবিএল মার্কেটিং রিসোর্স সেন্টার">SBL Marketing Resource Center</span>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-800/80">
                        ✓ {{ $stats['verified'] }} <span data-en="Verified" data-bn="যাচাইকৃত">Verified</span>
                    </span>
                    <span class="text-[11px] text-slate-400 font-mono">
                        {{ $stats['total'] }} <span data-en="Total Assets" data-bn="মোট এসেট">Total Assets</span>
                    </span>
                </div>
                <h1 class="text-xl md:text-2xl font-black tracking-tight text-white">
                    <span data-en="Marketing Materials & Document Hub" data-bn="মার্কেটিং ম্যাটেরিয়ালস ও অফিসিয়াল ডকুমেন্ট হাব">Marketing Materials & Document Hub</span>
                </h1>
                <p class="text-xs md:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    <span data-en="Verified documents, presentations, marketing assets and training materials in one place for client counseling & team empowerment."
                          data-bn="ক্লায়েন্ট কাউন্সেলিং, টিম ট্রেনিং এবং ব্যবসায়িক উপস্থাপনার জন্য যাচাইকৃত অফিসিয়াল ডকুমেন্টস, লিফলেট ও ব্র্যান্ড এসেট।">
                        Verified documents, presentations, marketing assets and training materials in one place for client counseling & team empowerment.
                    </span>
                </p>
            </div>

            <!-- Header Action Controls & Counters -->
            <div class="flex flex-wrap items-center gap-2.5 flex-shrink-0">
                <div class="hidden sm:flex items-center gap-2 bg-slate-900/80 px-3 py-1.5 rounded-2xl border border-slate-800 text-[11px]">
                    <div class="text-center px-2 border-r border-slate-800">
                        <span class="block font-bold text-orange-400">{{ $stats['presentations'] }}</span>
                        <span class="text-[10px] text-slate-400" data-en="Slides" data-bn="স্লাইডস">Slides</span>
                    </div>
                    <div class="text-center px-2 border-r border-slate-800">
                        <span class="block font-bold text-indigo-400">{{ $stats['marketing'] }}</span>
                        <span class="text-[10px] text-slate-400" data-en="Leaflets" data-bn="লিফলেট">Leaflets</span>
                    </div>
                    <div class="text-center px-2">
                        <span class="block font-bold text-emerald-400">{{ $stats['legal'] }}</span>
                        <span class="text-[10px] text-slate-400" data-en="Legal" data-bn="লিগ্যাল">Legal</span>
                    </div>
                </div>

                @if($canManage)
                <button type="button"
                        @click="createModalOpen = true"
                        class="px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-2xl transition-all shadow-md shadow-orange-600/20 flex items-center gap-2 cursor-pointer active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span data-en="+ Add Resource" data-bn="+ নতুন রিসোর্স যোগ">+ Add Resource</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- 02. SEARCH, CATEGORIES & SORT BAR -->
    <div class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-xs space-y-3.5">
        <!-- Live Instant Search -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="relative flex-1">
                <input type="text"
                       x-model="searchQuery"
                       placeholder="Search resources, documents, leaflets or presentations..."
                       class="w-full pl-10 pr-9 py-2.5 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 focus:outline-none transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <button type="button"
                        x-show="searchQuery"
                        @click="searchQuery = ''"
                        class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold cursor-pointer p-1">
                    ✕
                </button>
            </div>

            <!-- Filter Controls (Language & Verification) -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <select x-model="languageFilter"
                        class="px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-orange-500/20">
                    <option value="all">🌐 All Languages</option>
                    <option value="bangla">🇧🇩 বাংলা (Bangla)</option>
                    <option value="english">🇬🇧 English</option>
                    <option value="bilingual">🌍 Bilingual</option>
                </select>

                <select x-model="verificationFilter"
                        class="px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-orange-500/20">
                    <option value="all">🛡️ All Statuses</option>
                    <option value="verified">✓ Official / Verified Only</option>
                    <option value="counseling">💼 Counseling Toolkit Only</option>
                </select>
            </div>
        </div>

        <!-- Horizontal Scrollable Category Filter Chips -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-semibold scrollbar-none">
            <button type="button"
                    @click="categoryFilter = 'all'"
                    :class="categoryFilter === 'all' ? 'bg-orange-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-3.5 py-1.5 rounded-xl transition-all whitespace-nowrap cursor-pointer">
                <span data-en="All Resources" data-bn="সকল রিসোর্স">All Resources</span>
                <span class="ml-1 text-[10px] opacity-80">({{ $stats['total'] }})</span>
            </button>
            @foreach($resourceCategories as $cat)
            <button type="button"
                    @click="categoryFilter = '{{ $cat }}'"
                    :class="categoryFilter === '{{ $cat }}' ? 'bg-orange-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-3.5 py-1.5 rounded-xl transition-all whitespace-nowrap cursor-pointer">
                {{ $cat }}
            </button>
            @endforeach
        </div>
    </div>

    <!-- 03. MY PINNED RESOURCES (FAVORITES STRIP) -->
    <div x-show="pinnedResources.length > 0" class="space-y-2.5" x-cloak>
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <span class="text-amber-500">★</span>
                <span data-en="My Pinned Resources (Quick Access)" data-bn="আমার পিন করা রিসোর্স (দ্রুত অ্যাক্সেস)">My Pinned Resources</span>
            </h3>
            <span class="text-[11px] text-slate-400" x-text="pinnedResources.length + ' Pinned'"></span>
        </div>
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
            <template x-for="res in allResourcesList.filter(r => isPinned(r.id))" :key="'pin-' + res.id">
                <div class="flex-shrink-0 bg-white border border-amber-200 hover:border-amber-300 rounded-2xl p-2.5 shadow-2xs flex items-center gap-3 transition-all hover:shadow-xs group">
                    <span class="text-lg" x-text="res.file_type === 'pdf' ? '📄' : (res.file_type === 'presentation' ? '📊' : '🖼️')"></span>
                    <div class="max-w-[170px]">
                        <h4 class="text-xs font-bold text-slate-900 truncate" x-text="res.title"></h4>
                        <span class="text-[10px] text-slate-400 font-mono uppercase" x-text="res.file_type + ' • ' + (res.file_size || 'File')"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="openPreview(res)" class="p-1.5 bg-orange-50 text-orange-600 hover:bg-orange-100 rounded-lg text-xs font-bold cursor-pointer" title="Preview">👁️</button>
                        <button type="button" @click="togglePin(res.id)" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg text-xs cursor-pointer" title="Unpin">✕</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- 04. FEATURED RESOURCES (MAX 4) -->
    @if($featuredResources->count() > 0)
    <div class="space-y-3" x-show="categoryFilter === 'all' && !searchQuery.trim()">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-orange-600">⭐</span>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    <span data-en="Featured Marketing Resources" data-bn="প্রধান মার্কেটিং রিসোর্স">Featured Marketing Resources</span>
                </h3>
            </div>
            <span class="text-[11px] text-slate-400 font-medium" data-en="Key counseling & induction materials" data-bn="কাউন্সেলিং ও লিডারশিপের মূল ফাইলসমূহ">
                Key counseling & induction materials
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($featuredResources as $feat)
            <div class="bg-gradient-to-br from-white to-orange-50/40 rounded-2xl border border-orange-200/80 p-4 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:shadow-md transition-all group">
                <div class="flex items-start gap-3.5">
                    <div class="w-14 h-14 rounded-2xl bg-white border border-orange-200 shadow-2xs flex items-center justify-center text-3xl flex-shrink-0 group-hover:scale-105 transition-transform overflow-hidden">
                        @if($feat->thumbnail_url)
                        <img src="{{ $feat->thumbnail_url }}" alt="{{ $feat->title }}" class="w-full h-full object-cover">
                        @else
                        <span>{{ $feat->file_icon }}</span>
                        @endif
                    </div>
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider bg-orange-100 text-orange-800">
                                {{ $feat->category }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase border {{ $feat->verification_badge_class }}">
                                {{ $feat->verification_badge_label }}
                            </span>
                            @if($feat->version)
                            <span class="text-[10px] font-mono text-slate-400 font-semibold">{{ $feat->version }}</span>
                            @endif
                        </div>
                        <h4 class="font-bold text-slate-900 text-sm group-hover:text-orange-600 transition-colors line-clamp-1">
                            {{ $feat->title }}
                        </h4>
                        <p class="text-xs text-slate-600 line-clamp-1">
                            {{ $feat->description }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-orange-100">
                    <button type="button"
                            @click="openPreview({{ json_encode($feat) }})"
                            class="flex-1 sm:flex-none px-3.5 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                        <span>👁️</span>
                        <span data-en="Preview" data-bn="প্রিভিউ">Preview</span>
                    </button>
                    <a href="{{ $feat->file_url }}"
                       download
                       target="_blank"
                       rel="noopener noreferrer"
                       class="p-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-xl transition-colors cursor-pointer"
                       title="Download Resource">
                        ⬇️
                    </a>
                    <button type="button"
                            @click="shareResource({{ json_encode($feat) }})"
                            class="p-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-xl transition-colors cursor-pointer"
                            title="Share">
                        ↗️
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- 05. CURATED RESOURCE KITS / COLLECTIONS -->
    <div class="space-y-3" x-show="categoryFilter === 'all' && !searchQuery.trim()">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <span class="text-indigo-600">💼</span>
                <span data-en="Curated Counseling & Marketing Kits" data-bn="কিউরেটেড কাউন্সেলিং ও মার্কেটিং কিটস">Curated Counseling & Marketing Kits</span>
            </h3>
            <span class="text-[11px] text-slate-400" data-en="Pre-packaged resource bundles for instant sharing" data-bn="ক্লায়েন্টদের এক ক্লিকে পাঠানোর জন্য রেডি বান্ডেল">
                Pre-packaged resource bundles
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
            @foreach($curatedKits as $kit)
            <div class="bg-white rounded-2xl border border-slate-200/80 hover:border-indigo-300 p-4 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group">
                <div class="space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">{{ $kit['icon'] }}</span>
                            <div>
                                <h4 class="font-bold text-slate-900 text-xs md:text-sm group-hover:text-indigo-600 transition-colors">
                                    {{ $kit['title'] }}
                                </h4>
                                <span class="text-[10px] font-semibold text-indigo-600">{{ $kit['badge'] }}</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-600">
                            {{ count($kit['resource_ids']) }} Files
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                        {{ $kit['description'] }}
                    </p>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <button type="button"
                            @click="openKitModal({{ json_encode($kit) }})"
                            class="flex-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition-colors flex items-center justify-center gap-1 cursor-pointer">
                        <span>📦</span>
                        <span data-en="View Kit Files" data-bn="ফাইলগুলো দেখুন">View Kit Files</span>
                    </button>
                    <button type="button"
                            @click="copyKitSummary({{ json_encode($kit) }})"
                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer"
                            title="Copy Shareable Kit Summary">
                        <span>📋 Share Kit</span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- 06. COUNSELING TOOLKIT (MEETING-READY FILES) -->
    @if($counselingResources->count() > 0)
    <div class="space-y-3" x-show="categoryFilter === 'all' && !searchQuery.trim()">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <span class="text-emerald-600">🤝</span>
                <span data-en="Counseling Toolkit (Live Prospect Meeting Files)" data-bn="কাউন্সেলিং টুলকিট (মিটিং ও সেলস ফাইলসমূহ)">Counseling Toolkit</span>
            </h3>
            <span class="text-[11px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                {{ $counselingResources->count() }} Sales Assets
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5">
            @foreach($counselingResources as $cs)
            <div class="bg-white rounded-2xl border border-emerald-100 hover:border-emerald-300 p-3.5 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between group">
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center text-xl flex-shrink-0">
                            {{ $cs->file_icon }}
                        </div>
                        <div class="min-w-0">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-700 block truncate">
                                {{ $cs->category }}
                            </span>
                            <h4 class="font-bold text-slate-900 text-xs group-hover:text-emerald-700 transition-colors truncate">
                                {{ $cs->title }}
                            </h4>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono">
                        <span>{{ strtoupper($cs->file_type) }} • {{ $cs->file_size ?: 'Doc' }}</span>
                        <span class="font-sans font-semibold text-slate-500">{{ $cs->version }}</span>
                    </div>
                </div>

                <div class="pt-2.5 mt-2.5 border-t border-slate-100 flex items-center gap-1.5">
                    <button type="button"
                            @click="openPreview({{ json_encode($cs) }})"
                            class="flex-1 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-lg transition-colors text-center cursor-pointer">
                        Preview
                    </button>
                    <button type="button"
                            @click="shareResource({{ json_encode($cs) }})"
                            class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg text-xs cursor-pointer"
                            title="Share">
                        ↗️
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- 07. ALL RESOURCES DIRECTORY GRID -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <span class="text-slate-600">📁</span>
                <span data-en="All Marketing & Document Library" data-bn="সকল মার্কেটিং ও ডকুমেন্ট লাইব্রেরি">All Marketing & Document Library</span>
            </h3>
            <span class="text-[11px] text-slate-400 font-mono">
                Showing matching resources
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($resources as $res)
            <div x-show="matchesResource({{ json_encode($res) }})"
                 class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between hover:shadow-md hover:border-orange-200 transition-all group relative">

                <div class="space-y-3.5">
                    <!-- Top Bar: Thumbnail/Icon + Category & Verification Badges -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 border border-orange-100 group-hover:bg-orange-600 group-hover:text-white flex items-center justify-center text-2xl transition-colors flex-shrink-0 overflow-hidden">
                                @if($res->thumbnail_url)
                                <img src="{{ $res->thumbnail_url }}" alt="{{ $res->title }}" class="w-full h-full object-cover">
                                @else
                                <span>{{ $res->file_icon }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block truncate">
                                    {{ $res->category }}
                                </span>
                                <h3 class="font-bold text-slate-900 text-sm group-hover:text-orange-600 transition-colors line-clamp-2">
                                    {{ $res->title }}
                                </h3>
                            </div>
                        </div>

                        <!-- Pin Button -->
                        <button type="button"
                                @click="togglePin({{ $res->id }})"
                                class="p-1.5 rounded-lg text-slate-300 hover:text-amber-500 transition-colors cursor-pointer flex-shrink-0"
                                :class="isPinned({{ $res->id }}) ? 'text-amber-500 font-bold' : ''"
                                title="Pin to Quick Access">
                            <span x-text="isPinned({{ $res->id }}) ? '★' : '☆'"></span>
                        </button>
                    </div>

                    <!-- Badges Strip -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase border {{ $res->verification_badge_class }}">
                            {{ $res->verification_badge_label }}
                        </span>
                        @if($res->badge)
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                            {{ $res->badge }}
                        </span>
                        @endif
                        @if($res->version)
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] font-mono font-semibold bg-slate-50 text-slate-500 border border-slate-200">
                            {{ $res->version }}
                        </span>
                        @endif
                        @if($res->is_counseling_toolkit)
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            💼 Counseling Tool
                        </span>
                        @endif
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">
                        {{ $res->description }}
                    </p>

                    <!-- Data-Sensitive Notice if applicable -->
                    @if(in_array($res->category, ['Leaflets', 'Official Leaflets', 'Policies & Guides']) || str_contains(strtolower($res->title), 'package') || str_contains(strtolower($res->title), 'rank'))
                    <div class="p-2 bg-amber-50/70 border border-amber-200/80 rounded-xl text-[11px] text-amber-800 flex items-center gap-1.5">
                        <span>⚠️</span>
                        <span data-en="Data-sensitive document. Verify current SBL plan before presenting."
                              data-bn="প্ল্যান পরিবর্তনশীল। ক্লায়েন্টকে দেখানোর পূর্বে বর্তমান রেট যাচাই করুন।">
                            Data-sensitive document. Verify current SBL plan before presenting.
                        </span>
                    </div>
                    @endif

                    <!-- File Metadata Details -->
                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-400 font-medium pt-1">
                        <span class="uppercase font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                            {{ strtoupper($res->file_type) }}
                        </span>
                        @if($res->file_size)
                        <span>• {{ $res->file_size }}</span>
                        @endif
                        <span>• {{ ucfirst($res->language) }}</span>
                        @if($res->issue_date)
                        <span>• Issued: {{ $res->issue_date->format('M Y') }}</span>
                        @endif
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <!-- Primary Action: PREVIEW FIRST -->
                    <button type="button"
                            @click="openPreview({{ json_encode($res) }})"
                            class="flex-1 px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                        <span>👁️</span>
                        <span data-en="Preview Document" data-bn="প্রিভিউ দেখুন">Preview Document</span>
                    </button>

                    <!-- Direct Download -->
                    <a href="{{ $res->file_url }}"
                       download
                       target="_blank"
                       rel="noopener noreferrer"
                       class="p-2 bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200 rounded-xl transition-colors cursor-pointer"
                       title="Download Resource">
                        ⬇️
                    </a>

                    <!-- Share -->
                    <button type="button"
                            @click="shareResource({{ json_encode($res) }})"
                            class="p-2 bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-orange-600 border border-slate-200 rounded-xl transition-colors cursor-pointer"
                            title="Share Resource">
                        ↗️
                    </button>

                    <!-- QR Code -->
                    <button type="button"
                            @click="openQrModal({{ json_encode($res) }})"
                            class="p-2 bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-indigo-600 border border-slate-200 rounded-xl transition-colors cursor-pointer"
                            title="QR Code">
                        📱
                    </button>

                    <!-- Admin Edit & Actions -->
                    @if($canManage)
                    <button type="button"
                            @click="openEditModal({{ json_encode($res) }})"
                            class="p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-colors cursor-pointer"
                            title="Edit Resource">
                        ✏️
                    </button>
                    @endif
                </div>

            </div>
            @empty
            <div class="col-span-full py-12 text-center text-slate-400 bg-white rounded-3xl border border-slate-200">
                <span class="text-4xl block mb-2">📁</span>
                <p class="font-bold text-slate-700" data-en="No resources found" data-bn="কোনো রিসোর্স পাওয়া যায়নি">No resources found</p>
                <p class="text-xs text-slate-500 mt-1" data-en="Try selecting another category or clearing your search query." data-bn="অন্য ক্যাটাগরি বেছে নিন অথবা সার্চ বক্সটি ক্লিয়ার করুন।">
                    Try selecting another category or clearing your search query.
                </p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- 08. IMPORTANT COMPLIANCE & ACCURACY DISCLAIMER -->
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-slate-600 text-xs flex items-start gap-3">
        <span class="text-base text-orange-600 flex-shrink-0">ℹ️</span>
        <div class="space-y-1">
            <h4 class="font-bold text-slate-900" data-en="Official SBL Information Notice" data-bn="অফিসিয়াল এসবিএল তথ্য সতর্কতা">Official SBL Information Notice</h4>
            <p class="leading-relaxed"
               data-en="Marketing plans, commissions, package details and policies may change. Always use the latest verified SBL resource before presenting information to a prospect. Older or superseded materials should be referenced with caution."
               data-bn="মার্কেটিং প্ল্যান, কমিশন, প্যাকেজ সংক্রান্ত নিয়মাবলী ও পলিসি পরিবর্তনশীল। প্রসপেক্টকে উপস্থাপনের পূর্বে সর্বদা সর্বশেষ যাচাইকৃত রিসোর্স ব্যবহার নিশ্চিত করুন।">
                Marketing plans, commissions, package details and policies may change. Always use the latest verified SBL resource before presenting information to a prospect.
            </p>
        </div>
    </div>

    <!-- ==================== MODALS & DRAWERS ==================== -->

    <!-- PREVIEW MODAL / FULL-SCREEN DRAWER -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="previewModalOpen"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4"
         x-transition
         x-cloak>
        <div @click.away="previewModalOpen = false"
             class="bg-white rounded-3xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden">
            
            <!-- Header -->
            <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="text-xl" x-text="previewResource ? previewResource.file_icon : '📄'"></span>
                    <div class="min-w-0">
                        <h3 class="font-bold text-slate-900 text-sm md:text-base truncate" x-text="previewResource ? previewResource.title : 'Preview'"></h3>
                        <div class="flex items-center gap-2 text-[10px] text-slate-400 font-mono">
                            <span x-text="previewResource ? previewResource.category : ''"></span>
                            <span>•</span>
                            <span x-text="previewResource ? (previewResource.file_size || 'Document') : ''"></span>
                            <span>•</span>
                            <span x-text="previewResource ? (previewResource.version || 'v1.0') : ''"></span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="previewModalOpen = false" class="p-2 text-slate-400 hover:text-slate-700 text-lg font-bold rounded-xl cursor-pointer">✕</button>
            </div>

            <!-- Preview Content Frame -->
            <div class="flex-1 overflow-y-auto p-4 bg-slate-900/5 flex items-center justify-center min-h-[350px]">
                <template x-if="previewResource && (previewResource.file_type === 'image' || previewResource.file_url.endsWith('.png') || previewResource.file_url.endsWith('.jpg') || previewResource.file_url.endsWith('.jpeg'))">
                    <img :src="previewResource.file_url" :alt="previewResource.title" class="max-h-[70vh] max-w-full object-contain rounded-xl shadow-md border border-slate-200">
                </template>

                <template x-if="previewResource && previewResource.file_type === 'pdf'">
                    <iframe :src="previewResource.file_url" class="w-full h-[65vh] rounded-xl border border-slate-200 bg-white" title="PDF Preview"></iframe>
                </template>

                <template x-if="previewResource && (previewResource.file_type === 'presentation' || previewResource.file_url.includes('docs.google.com'))">
                    <iframe :src="previewResource.file_url" class="w-full h-[65vh] rounded-xl border border-slate-200 bg-white" title="Presentation Preview"></iframe>
                </template>

                <template x-if="previewResource && !['image', 'pdf', 'presentation'].includes(previewResource.file_type) && !previewResource.file_url.endsWith('.png') && !previewResource.file_url.endsWith('.jpg') && !previewResource.file_url.includes('docs.google.com')">
                    <div class="text-center p-8 space-y-4">
                        <span class="text-5xl block">📑</span>
                        <div>
                            <h4 class="font-bold text-slate-800 text-base" x-text="previewResource.title"></h4>
                            <p class="text-xs text-slate-500 mt-1">This file type is best viewed in an external application.</p>
                        </div>
                        <a :href="previewResource.file_url" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-xl transition-colors">
                            <span>Open / Download File</span>
                            <span>↗️</span>
                        </a>
                    </div>
                </template>
            </div>

            <!-- Footer Action Controls -->
            <div class="p-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-white">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600">Verification:</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700" x-text="previewResource ? (previewResource.is_official ? '✓ Official Asset' : 'Internal Resource') : ''"></span>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="previewResource ? previewResource.file_url : '#'"
                       download
                       target="_blank"
                       class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span>⬇️</span>
                        <span>Download</span>
                    </a>
                    <button type="button"
                            @click="if(previewResource) shareResource(previewResource)"
                            class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer">
                        ↗️ Share
                    </button>
                    <button type="button"
                            @click="if(previewResource) copyLink(previewResource.file_url, previewResource.title)"
                            class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer">
                        📋 Copy Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- QR CODE MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="qrModalOpen"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="qrModalOpen = false"
             class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 text-center space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Resource QR Code</h3>
                <button type="button" @click="qrModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer">✕</button>
            </div>

            <div class="space-y-1">
                <h4 class="font-bold text-slate-900 text-sm truncate" x-text="qrResource ? qrResource.title : ''"></h4>
                <p class="text-[11px] text-slate-500" data-en="Scan with camera for instant document access" data-bn="মোবাইল ক্যামেরা দিয়ে স্ক্যান করে সরাসরি ডাউনলোড করুন">
                    Scan with camera for instant document access
                </p>
            </div>

            <!-- SVG QR Code Rendering -->
            <div class="flex items-center justify-center p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                <template x-if="qrResource">
                    <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(qrResource.file_url.startsWith('http') ? qrResource.file_url : window.location.origin + qrResource.file_url)"
                         alt="Resource QR Code"
                         class="w-48 h-48 rounded-xl shadow-xs">
                </template>
            </div>

            <div class="flex items-center justify-center gap-2 pt-2">
                <button type="button"
                        @click="if(qrResource) copyLink(qrResource.file_url, qrResource.title)"
                        class="flex-1 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors cursor-pointer">
                    📋 Copy Link
                </button>
                <button type="button"
                        @click="qrModalOpen = false"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- CURATED KIT MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="kitModalOpen"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="kitModalOpen = false"
             class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-2xl" x-text="activeKit ? activeKit.icon : '📦'"></span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" x-text="activeKit ? activeKit.title : 'Resource Kit'"></h3>
                        <span class="text-[10px] text-indigo-600 font-semibold" x-text="activeKit ? activeKit.badge : ''"></span>
                    </div>
                </div>
                <button type="button" @click="kitModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer">✕</button>
            </div>

            <p class="text-xs text-slate-600" x-text="activeKit ? activeKit.description : ''"></p>

            <!-- Kit Included Resources List -->
            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                <template x-for="r in allResourcesList.filter(res => activeKit && activeKit.resource_ids.includes(res.id))" :key="'kit-file-' + r.id">
                    <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="text-lg" x-text="r.file_type === 'pdf' ? '📄' : (r.file_type === 'presentation' ? '📊' : '🖼️')"></span>
                            <div class="min-w-0">
                                <h5 class="text-xs font-bold text-slate-900 truncate" x-text="r.title"></h5>
                                <span class="text-[10px] text-slate-400 font-mono" x-text="r.file_type.toUpperCase() + ' • ' + (r.file_size || 'File')"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <button type="button" @click="openPreview(r)" class="px-2 py-1 bg-white hover:bg-orange-50 text-orange-600 border border-slate-200 rounded-lg text-[11px] font-bold cursor-pointer">👁️</button>
                            <a :href="r.file_url" download target="_blank" class="px-2 py-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-[11px] font-bold cursor-pointer">⬇️</a>
                        </div>
                    </div>
                </template>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                <button type="button"
                        @click="if(activeKit) copyKitSummary(activeKit)"
                        class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-colors cursor-pointer flex items-center justify-center gap-1.5 shadow-xs">
                    <span>📋</span>
                    <span>Copy Full Kit Summary</span>
                </button>
                <button type="button"
                        @click="kitModalOpen = false"
                        class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- SHARE FALLBACK MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="shareModalOpen"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="shareModalOpen = false"
             class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Share Resource</h3>
                <button type="button" @click="shareModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer">✕</button>
            </div>

            <div class="space-y-1">
                <h4 class="font-bold text-slate-900 text-sm truncate" x-text="activeShareResource ? activeShareResource.title : ''"></h4>
                <p class="text-xs text-slate-500">Choose a platform to share this material with prospects:</p>
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <!-- WhatsApp -->
                <a :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent((activeShareResource ? activeShareResource.title + '\n' : '') + (activeShareResource && activeShareResource.file_url.startsWith('http') ? activeShareResource.file_url : window.location.origin + (activeShareResource ? activeShareResource.file_url : '')))"
                   target="_blank"
                   class="p-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200 flex items-center justify-center gap-2 transition-colors">
                    <span>💬 WhatsApp</span>
                </a>

                <!-- Facebook -->
                <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(activeShareResource && activeShareResource.file_url.startsWith('http') ? activeShareResource.file_url : window.location.origin + (activeShareResource ? activeShareResource.file_url : ''))"
                   target="_blank"
                   class="p-3 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs rounded-xl border border-blue-200 flex items-center justify-center gap-2 transition-colors">
                    <span>📘 Facebook</span>
                </a>
            </div>

            <div class="pt-2">
                <button type="button"
                        @click="if(activeShareResource) { copyLink(activeShareResource.file_url, activeShareResource.title); shareModalOpen = false; }"
                        class="w-full py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors cursor-pointer">
                    📋 Copy Shareable Link
                </button>
            </div>
        </div>
    </div>

    @if($canManage)
    <!-- ADD RESOURCE MODAL (Admin) -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="createModalOpen"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="createModalOpen = false" class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📁</span>
                    <h3 class="text-base font-bold text-slate-900">Add New Official Resource</h3>
                </div>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">✕</button>
            </div>

            <form action="{{ route('marketing-resources.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Resource Title *</label>
                    <input type="text" name="title" required placeholder="e.g. SBL Official Dropshipping Leaflet" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="Leaflets">Leaflets</option>
                            <option value="Presentations">Presentations</option>
                            <option value="Policies & Guides">Policies & Guides</option>
                            <option value="Legal & Compliance">Legal & Compliance</option>
                            <option value="Marketing Media">Marketing Media</option>
                            <option value="Brand Assets">Brand Assets</option>
                            <option value="Official Documents">Official Documents</option>
                            <option value="Training Materials">Training Materials</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Type *</label>
                        <select name="file_type" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="pdf">PDF Document</option>
                            <option value="image">Image / High-Res</option>
                            <option value="presentation">Presentation / Slides</option>
                            <option value="doc">Word / Document</option>
                            <option value="spreadsheet">Spreadsheet</option>
                            <option value="video">Video</option>
                            <option value="zip">Zip / Asset Pack</option>
                            <option value="link">External Resource</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File URL or Path *</label>
                    <input type="text" name="file_url" required placeholder="/images/sbl-packages-sheet.png or https://..." class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Size</label>
                        <input type="text" name="file_size" placeholder="e.g. 1.8 MB" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Version</label>
                        <input type="text" name="version" value="v1.0" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Language</label>
                        <select name="language" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="bilingual">Bilingual</option>
                            <option value="bangla">Bangla</option>
                            <option value="english">English</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Verification Status *</label>
                        <select name="verification_status" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="needs_verification">Needs Verification</option>
                            <option value="official_verified">Official & Verified</option>
                            <option value="sbl_provided">SBL Provided</option>
                            <option value="internal_marketing">Internal Marketing Material</option>
                            <option value="verified_document">Verified Document</option>
                            <option value="government_document">Govt. Document</option>
                            <option value="needs_review">Needs Review</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                        <input type="text" name="badge" placeholder="e.g. High-Res Comparison" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief summary for counselors & team members..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                </div>

                <!-- Toggles -->
                <div class="grid grid-cols-3 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_official" value="1" class="w-4 h-4 rounded text-orange-600">
                        <span class="font-bold text-slate-700">Official SBL</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" class="w-4 h-4 rounded text-orange-600">
                        <span class="font-bold text-slate-700">Featured</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_counseling_toolkit" value="1" class="w-4 h-4 rounded text-orange-600">
                        <span class="font-bold text-slate-700">Counseling Toolkit</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-xs transition-colors cursor-pointer">Save Resource</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT RESOURCE MODAL (Admin) -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="editModalOpen"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="editModalOpen = false" class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">✏️</span>
                    <h3 class="text-base font-bold text-slate-900">Edit Marketing Resource</h3>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">✕</button>
            </div>

            <form :action="'{{ url('/marketing-resources') }}/' + editingResource.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Resource Title *</label>
                    <input type="text" name="title" x-model="editingResource.title" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="category" x-model="editingResource.category" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="Leaflets">Leaflets</option>
                            <option value="Presentations">Presentations</option>
                            <option value="Policies & Guides">Policies & Guides</option>
                            <option value="Legal & Compliance">Legal & Compliance</option>
                            <option value="Marketing Media">Marketing Media</option>
                            <option value="Brand Assets">Brand Assets</option>
                            <option value="Official Documents">Official Documents</option>
                            <option value="Training Materials">Training Materials</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Type *</label>
                        <select name="file_type" x-model="editingResource.file_type" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="pdf">PDF Document</option>
                            <option value="image">Image / High-Res</option>
                            <option value="presentation">Presentation / Slides</option>
                            <option value="doc">Word / Document</option>
                            <option value="spreadsheet">Spreadsheet</option>
                            <option value="video">Video</option>
                            <option value="zip">Zip / Asset Pack</option>
                            <option value="link">External Resource</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File URL or Path *</label>
                    <input type="text" name="file_url" x-model="editingResource.file_url" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Size</label>
                        <input type="text" name="file_size" x-model="editingResource.file_size" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Version</label>
                        <input type="text" name="version" x-model="editingResource.version" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Language</label>
                        <select name="language" x-model="editingResource.language" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="bilingual">Bilingual</option>
                            <option value="bangla">Bangla</option>
                            <option value="english">English</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Verification Status *</label>
                        <select name="verification_status" x-model="editingResource.verification_status" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="needs_verification">Needs Verification</option>
                            <option value="official_verified">Official & Verified</option>
                            <option value="sbl_provided">SBL Provided</option>
                            <option value="internal_marketing">Internal Marketing Material</option>
                            <option value="verified_document">Verified Document</option>
                            <option value="government_document">Govt. Document</option>
                            <option value="needs_review">Needs Review</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                        <input type="text" name="badge" x-model="editingResource.badge" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" x-model="editingResource.description" rows="2" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                </div>

                <!-- Toggles -->
                <div class="grid grid-cols-3 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_official" value="1" :checked="editingResource.is_official" class="w-4 h-4 rounded text-orange-600">
                        <span class="font-bold text-slate-700">Official SBL</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" :checked="editingResource.is_featured" class="w-4 h-4 rounded text-orange-600">
                        <span class="font-bold text-slate-700">Featured</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_counseling_toolkit" value="1" :checked="editingResource.is_counseling_toolkit" class="w-4 h-4 rounded text-orange-600">
                        <span class="font-bold text-slate-700">Counseling Toolkit</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <button type="submit"
                            name="action"
                            value="archive"
                            formnovalidate
                            onclick="return confirm('Archive this resource? Older versions will be hidden from public view.');"
                            class="px-3 py-2 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-xl transition-colors cursor-pointer">
                        📦 Archive Resource
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-xs transition-colors cursor-pointer">Update Resource</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- TOAST NOTIFICATION -->
    <div x-show="toastVisible"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-4 py-2.5 rounded-2xl shadow-xl border border-slate-700 text-xs font-bold flex items-center gap-2"
         x-cloak>
        <span x-text="toastMessage"></span>
    </div>

</div>

