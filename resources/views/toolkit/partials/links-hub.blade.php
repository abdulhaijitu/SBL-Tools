{{-- SBL Marketing > Links Page: SBL Resource Hub Partial --}}
<div x-data="{
    searchQuery: '',
    selectedCategory: 'All',
    viewMode: 'grid',
    favorites: JSON.parse(localStorage.getItem('sbl_fav_links') || '[]'),
    copiedUrl: null,
    toastMessage: '',
    toastVisible: false,
    
    // Modals
    qrModalOpen: false,
    qrTarget: { title: '', url: '', domain: '' },
    detailsModalOpen: false,
    selectedLink: null,
    shareModalOpen: false,
    shareTarget: { title: '', url: '' },
    addModalOpen: false,
    editModalOpen: false,
    editingLink: { id: null, title: '', url: '', category: 'Business & Commerce', type: 'external', badge: '', description: '', icon: '🌐', sort_order: 0, is_official: false, verification_status: 'unverified', is_featured: false, tags: '', notes: '' },
    
    // Toast helper
    showToast(msg) {
        this.toastMessage = msg;
        this.toastVisible = true;
        setTimeout(() => { this.toastVisible = false; }, 2500);
    },
    
    // Copy helper
    copyLink(url, title = 'Link') {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(() => {
                this.copiedUrl = url;
                this.showToast((title ? title + ' ' : '') + 'copied to clipboard!');
                setTimeout(() => { if (this.copiedUrl === url) this.copiedUrl = null; }, 2000);
            });
        }
    },
    
    // Toggle favorite
    toggleFavorite(id) {
        const strId = String(id);
        const idx = this.favorites.indexOf(strId);
        if (idx > -1) {
            this.favorites.splice(idx, 1);
            this.showToast('Removed from quick links');
        } else {
            this.favorites.push(strId);
            this.showToast('Pinned to quick links!');
        }
        localStorage.setItem('sbl_fav_links', JSON.stringify(this.favorites));
    },
    
    isFavorite(id) {
        return this.favorites.includes(String(id));
    },
    
    // Open QR modal
    openQR(link) {
        this.qrTarget = {
            title: link.title,
            url: link.url,
            domain: link.domain || link.url.replace(/^https?:\/\//i, '').split('/')[0]
        };
        this.qrModalOpen = true;
    },
    
    // Open Details modal
    openDetails(link) {
        this.selectedLink = link;
        this.detailsModalOpen = true;
    },
    
    // Share function
    shareLink(link) {
        if (navigator.share) {
            navigator.share({
                title: link.title,
                text: link.title + ' - Official SBL Resource',
                url: link.url
            }).catch(() => {});
        } else {
            this.shareTarget = { title: link.title, url: link.url };
            this.shareModalOpen = true;
        }
    },
    
    // Open Edit modal
    openEditModal(link) {
        this.editingLink = {
            id: link.id,
            title: link.title,
            url: link.url,
            category: link.category,
            type: link.type || 'external',
            badge: link.badge || '',
            description: link.description || '',
            icon: link.icon || '🌐',
            sort_order: link.sort_order || 0,
            is_official: Boolean(link.is_official),
            verification_status: link.verification_status || 'unverified',
            is_featured: Boolean(link.is_featured),
            tags: link.tags || '',
            notes: link.notes || ''
        };
        this.editModalOpen = true;
    },
    
    // Filter helper
    matchesFilter(item) {
        const q = this.searchQuery.trim().toLowerCase();
        const matchesCategory = (this.selectedCategory === 'All') ||
            (this.selectedCategory === 'Official' && item.is_official && item.verification_status === 'verified') ||
            (this.selectedCategory === 'Marketing Tools' && (item.category === 'Marketing Tools' || item.type === 'internal')) ||
            (this.selectedCategory === 'Business' && (item.category.includes('Business') || item.category.includes('Commerce'))) ||
            (this.selectedCategory === 'Member' && (item.category.includes('Member') || item.category.includes('Affiliate'))) ||
            (this.selectedCategory === 'Products' && (item.category.includes('Product') || item.category.includes('Shop'))) ||
            (this.selectedCategory === 'Training' && (item.category.includes('Training') || item.category.includes('Academy'))) ||
            (this.selectedCategory === 'Support' && (item.category.includes('Support') || item.category.includes('Helpdesk'))) ||
            (this.selectedCategory === 'Community' && (item.category.includes('Community') || item.category.includes('Social')));
            
        if (!matchesCategory) return false;
        if (!q) return true;
        
        const haystack = [
            item.title || '',
            item.url || '',
            item.domain || '',
            item.category || '',
            item.description || '',
            item.badge || '',
            item.tags || ''
        ].join(' ').toLowerCase();
        
        return haystack.includes(q);
    }
}" class="space-y-6">

    <!-- 01. COMPACT PAGE HEADER -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 text-white p-5 md:p-6 rounded-2xl border border-slate-800 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-orange-600/20 text-orange-400 border border-orange-500/30 rounded-full text-[11px] font-bold uppercase tracking-wider">
                    <span>🌐</span> <span data-en="SBL Resource Hub" data-bn="এসবিএল রিসোর্স হাব">SBL Resource Hub</span>
                </span>
                @if(($verifiedCount ?? 0) > 0)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-full text-[11px] font-bold">
                    <span>✓</span> <span>{{ $verifiedCount }}</span> <span data-en="Verified Resources" data-bn="যাচাইকৃত রিসোর্স">Verified Resources</span>
                </span>
                @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-800 text-slate-300 border border-slate-700 rounded-full text-[11px] font-medium">
                    <span data-en="Verification In Progress" data-bn="যাচাইকরণ চলমান">Verification In Progress</span>
                </span>
                @endif
            </div>
            <h1 class="text-xl md:text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                <span data-en="SBL Portals, Business Websites & Tools" data-bn="এসবিএল পোর্টাল, ব্যবসায়িক ওয়েবসাইট ও টুলস">SBL Portals, Business Websites & Tools</span>
            </h1>
            <p class="text-xs md:text-sm text-slate-300 max-w-2xl leading-relaxed" data-en="Instant access to verified SBL websites, dropshipping hub, investor dashboards, training resources and internal marketing calculators." data-bn="যাচাইকৃত এসবিএল ওয়েবসাইট, ড্রপশিপিং হাব, ইনভেস্টর ড্যাশবোর্ড, ট্রেনিং একাডেমি এবং ইন্টারনাল মার্কেটিং টুলসে সরাসরি প্রবেশ করুন।">
                Instant access to verified SBL websites, dropshipping hub, investor dashboards, training resources and internal marketing calculators.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-shrink-0 self-stretch md:self-auto justify-end">
            @if($canManage ?? false)
            <button type="button" @click="addModalOpen = true" class="px-3.5 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span data-en="Add Link" data-bn="নতুন লিংক যুক্ত করুন">Add Link</span>
            </button>
            <a href="{{ route('ecosystem.index') }}" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold rounded-xl transition-colors flex items-center gap-1.5 border border-slate-700">
                <span data-en="Manage All" data-bn="সব ম্যানেজ করুন">Manage All</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
            @endif
        </div>
    </div>

    <!-- 02. LIVE SEARCH & QUICK STATS BAR -->
    <div class="bg-white rounded-2xl p-3 md:p-4 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative w-full md:w-96">
            <input type="text" 
                   x-model="searchQuery" 
                   placeholder="Search SBL links, domains, or keywords..." 
                   class="w-full pl-9 pr-8 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all placeholder:text-slate-400">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <button type="button" 
                    x-show="searchQuery" 
                    @click="searchQuery = ''" 
                    class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold cursor-pointer"
                    title="Clear search">✕</button>
        </div>

        <!-- Quick Summary & Filter Reset -->
        <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span><strong class="text-slate-900 font-bold">{{ count($links) }}</strong> <span data-en="Ecosystem Links" data-bn="ইকোসিস্টেম লিংক">Ecosystem Links</span></span>
                <span class="text-slate-300">•</span>
                <span><strong class="text-slate-900 font-bold">{{ count($marketingToolkit) }}</strong> <span data-en="Marketing Tools" data-bn="মার্কেটিং টুলস">Marketing Tools</span></span>
            </div>

            <button type="button" 
                    x-show="searchQuery || selectedCategory !== 'All'" 
                    @click="searchQuery = ''; selectedCategory = 'All';" 
                    class="text-orange-600 hover:text-orange-700 font-bold text-xs underline cursor-pointer"
                    data-en="Reset Filters" data-bn="ফিল্টার রিসেট">
                Reset
            </button>
        </div>
    </div>

    <!-- 03. CATEGORY NAVIGATION CHIPS -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-medium scrollbar-none -mx-4 px-4 md:mx-0 md:px-0">
        @php
            $categoryChips = [
                ['id' => 'All', 'label_en' => 'All Resources', 'label_bn' => 'সকল রিসোর্স', 'icon' => '📂'],
                ['id' => 'Official', 'label_en' => 'Official Portals', 'label_bn' => 'অফিসিয়াল পোর্টাল', 'icon' => '🏛️'],
                ['id' => 'Business', 'label_en' => 'Business & Dropship', 'label_bn' => 'বিজনেস ও ড্রপশিপ', 'icon' => '🛍️'],
                ['id' => 'Member', 'label_en' => 'Member Backoffice', 'label_bn' => 'মেম্বার ব্যাকঅফিস', 'icon' => '👥'],
                ['id' => 'Marketing Tools', 'label_en' => 'Marketing Tools', 'label_bn' => 'মার্কেটিং টুলস', 'icon' => '🧮'],
                ['id' => 'Training', 'label_en' => 'Training Academy', 'label_bn' => 'ট্রেনিং একাডেমি', 'icon' => '🎓'],
                ['id' => 'Support', 'label_en' => 'Support & Helpdesk', 'label_bn' => 'সাপোর্ট ও হেল্পডেস্ক', 'icon' => '📞'],
                ['id' => 'Community', 'label_en' => 'Community & Social', 'label_bn' => 'কমিউনিটি ও সোশ্যাল', 'icon' => '💬'],
            ];
        @endphp

        @foreach($categoryChips as $chip)
        <button type="button" 
                @click="selectedCategory = '{{ $chip['id'] }}'"
                :class="selectedCategory === '{{ $chip['id'] }}' 
                    ? 'bg-slate-900 text-white shadow-xs font-bold' 
                    : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200/80'"
                class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
            <span>{{ $chip['icon'] }}</span>
            <span data-en="{{ $chip['label_en'] }}" data-bn="{{ $chip['label_bn'] }}">{{ $chip['label_en'] }}</span>
        </button>
        @endforeach
    </div>

    <!-- 04. MY QUICK LINKS (STARRED / FAVORITES) -->
    <div x-show="favorites.length > 0 && selectedCategory === 'All' && !searchQuery" 
         x-transition 
         class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-4 space-y-2.5">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                <span>⭐</span> <span data-en="My Quick Links (Pinned for Fast Access)" data-bn="আমার পিন করা প্রিয় লিংকসমূহ">My Quick Links (Pinned for Fast Access)</span>
            </h3>
            <span class="text-[11px] text-amber-700" x-text="favorites.length + ' pinned'"></span>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @foreach($links as $link)
            <div x-show="isFavorite({{ $link->id }})" 
                 class="inline-flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-amber-200 shadow-xs text-xs">
                <span>{{ $link->icon ?: '🌐' }}</span>
                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="font-bold text-slate-900 hover:text-orange-600 transition-colors">
                    {{ $link->title }}
                </a>
                <span class="text-[10px] text-slate-400 font-mono">{{ $link->domain }}</span>
                <button type="button" @click="toggleFavorite({{ $link->id }})" class="text-amber-500 hover:text-slate-400 ml-1 text-sm cursor-pointer" title="Unpin">★</button>
            </div>
            @endforeach
        </div>
    </div>

    <!-- 05. FEATURED RESOURCES (SHOWN ONLY IF VERIFIED) -->
    @if(isset($featuredLinks) && count($featuredLinks) > 0)
    <div x-show="selectedCategory === 'All' && !searchQuery" class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <span class="text-orange-500">★</span> <span data-en="Featured Official Portals" data-bn="গুরুত্বপূর্ণ অফিসিয়াল পোর্টাল">Featured Official Portals</span>
            </h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($featuredLinks as $fl)
            <div class="bg-white rounded-2xl border-2 border-emerald-500/30 p-4 shadow-xs flex flex-col justify-between hover:shadow-md transition-all group">
                <div class="space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-xl flex-shrink-0">
                                {{ $fl->icon ?: '🌐' }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm group-hover:text-emerald-700 transition-colors">{{ $fl->title }}</h4>
                                <div class="text-[10px] font-mono text-emerald-700 flex items-center gap-1">
                                    <span>✓ Verified</span> • <span>{{ $fl->domain }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ $fl->description }}</p>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="{{ $fl->url }}" target="_blank" rel="noopener noreferrer" class="flex-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center justify-center gap-1.5">
                        <span data-en="Open Portal" data-bn="পোর্টাল ওপেন করুন">Open Portal</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                    <button type="button" @click="copyLink('{{ $fl->url }}', '{{ addslashes($fl->title) }}')" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg cursor-pointer" title="Copy Link">📋</button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- 06. INTERNAL MARKETING TOOLKIT (DEDICATED SECTION) -->
    <div x-show="(selectedCategory === 'All' || selectedCategory === 'Marketing Tools') && !searchQuery" class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="text-indigo-600">🧮</span> <span data-en="SBL Marketing Toolkit (Internal Tools)" data-bn="এসবিএল মার্কেটিং টুলকিট (ইন্টারনাল টুলস)">SBL Marketing Toolkit (Internal Tools)</span>
                </h3>
                <p class="text-[11px] text-slate-400" data-en="Core presentation, counseling and compensation calculators built for daily counseling." data-bn="প্রতিদিনের কাউন্সেলিং ও প্রেজেন্টেশনের জন্য প্রয়োজনীয় ইন্টারনাল ক্যালকুলেটর ও গাইড।">
                    Core presentation, counseling and compensation calculators built for daily counseling.
                </p>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                6 Tools Available
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($marketingToolkit as $tool)
            <div class="bg-white rounded-2xl border border-indigo-100 hover:border-indigo-300 shadow-xs p-4 flex flex-col justify-between hover:shadow-md transition-all group">
                <div class="space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 group-hover:bg-indigo-100 flex items-center justify-center text-xl flex-shrink-0 transition-colors">
                                {{ $tool['icon'] }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm group-hover:text-indigo-700 transition-colors">
                                    {{ $tool['title'] }}
                                </h4>
                                <div class="text-[10px] font-semibold text-indigo-600 flex items-center gap-1">
                                    <span>🔒 Internal Tool</span> • <span class="font-mono text-slate-400">{{ $tool['domain'] }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            {{ $tool['badge'] }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                        {{ $tool['description'] }}
                    </p>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="{{ $tool['url'] }}" class="flex-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                        <span data-en="Launch Tool" data-bn="টুল ব্যবহার করুন">Launch Tool</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    <button type="button" @click="copyLink('{{ $tool['url'] }}', '{{ addslashes($tool['title']) }}')" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors cursor-pointer" title="Copy Link">📋</button>
                    <button type="button" @click="shareLink({ title: '{{ addslashes($tool['title']) }}', url: '{{ $tool['url'] }}' })" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors cursor-pointer" title="Share">↗️</button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- 07. MAIN RESOURCE DIRECTORY (ECOSYSTEM LINKS GRID) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <span class="text-slate-600">🌐</span> <span data-en="All SBL Ecosystem & Partner Resources" data-bn="সকল এসবিএল ইকোসিস্টেম ও রিসোর্স">All SBL Ecosystem & Partner Resources</span>
            </h3>
            <span class="text-xs text-slate-400" x-text="'Showing ' + (selectedCategory === 'All' ? 'All' : selectedCategory)"></span>
        </div>

        <div id="sbl-resource-hub-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($links as $link)
            @php
                $linkPayload = [
                    'id' => $link->id,
                    'title' => $link->title,
                    'url' => $link->url,
                    'domain' => $link->domain,
                    'category' => $link->category,
                    'type' => $link->type ?? 'external',
                    'badge' => $link->badge ?? '',
                    'description' => $link->description ?? '',
                    'icon' => $link->icon ?: '🌐',
                    'sort_order' => $link->sort_order,
                    'is_official' => (bool)$link->is_official,
                    'verification_status' => $link->verification_status ?? 'unverified',
                    'verified_at' => $link->verified_at ? $link->verified_at->format('Y-m-d') : null,
                    'verified_by' => $link->verified_by ?? null,
                    'is_featured' => (bool)$link->is_featured,
                    'tags' => $link->tags ?? '',
                    'notes' => $link->notes ?? '',
                    'is_review_recommended' => $link->is_review_recommended,
                ];
            @endphp
            <div data-link-id="{{ $link->id }}" 
                 x-show="matchesFilter({{ json_encode($linkPayload) }})"
                 class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 flex flex-col justify-between hover:shadow-md hover:border-orange-200 transition-all group">
                
                <div class="space-y-3">
                    <!-- Card Top Row: Icon, Title, Badges, Star -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 group-hover:bg-orange-50 group-hover:border-orange-200 flex items-center justify-center text-xl flex-shrink-0 transition-colors">
                                {{ $link->icon ?: '🌐' }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-900 text-sm group-hover:text-orange-600 transition-colors truncate">
                                    {{ $link->title }}
                                </h4>
                                <div class="flex items-center gap-1.5 flex-wrap text-[10px] font-semibold text-slate-400">
                                    <span>{{ $link->category }}</span>
                                    <span>•</span>
                                    <span class="font-mono text-slate-500 truncate">{{ $link->domain }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Star Favorite Button -->
                        <button type="button" 
                                @click="toggleFavorite({{ $link->id }})" 
                                class="p-1 rounded-lg text-slate-300 hover:text-amber-500 transition-colors flex-shrink-0 cursor-pointer"
                                :class="isFavorite({{ $link->id }}) ? '!text-amber-500' : ''"
                                :title="isFavorite({{ $link->id }}) ? 'Remove from favorites' : 'Pin to favorites'">
                            <span class="text-base" x-text="isFavorite({{ $link->id }}) ? '★' : '☆'"></span>
                        </button>
                    </div>

                    <!-- Verification & Type Badges -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        {{-- 1. Official & Verified Badge (STRICT RULE: ONLY WHEN is_official = true AND verification_status = 'verified') --}}
                        @if($link->is_official && $link->verification_status === 'verified')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span>✓</span> <span data-en="Official SBL" data-bn="অফিসিয়াল এসবিএল">Official SBL</span>
                        </span>
                        @elseif($link->verification_status === 'verified')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            <span>✓</span> <span data-en="Verified Resource" data-bn="যাচাইকৃত রিসোর্স">Verified Resource</span>
                        </span>
                        @elseif($link->verification_status === 'needs_review' || $link->is_review_recommended)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Review recommended: link was verified over 180 days ago or flagged for update.">
                            <span>⚠️</span> <span data-en="Review Recommended" data-bn="পর্যালোচনা আবশ্যক">Review Recommended</span>
                        </span>
                        @else
                        {{-- Unverified link badge --}}
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                            <span data-en="Unverified" data-bn="অযাচাইকৃত">Unverified</span>
                        </span>
                        @endif

                        @if($link->badge)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800">
                            {{ $link->badge }}
                        </span>
                        @endif
                    </div>

                    <!-- Description clamped to 2 lines -->
                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed min-h-[32px]">
                        {{ $link->description ?: 'No description provided.' }}
                    </p>

                    <!-- Clean Domain Display (Security & Trust) -->
                    <div class="text-[11px] font-mono text-slate-400 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100 flex items-center justify-between">
                        <span class="truncate">{{ $link->domain }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-slate-400 font-sans">HTTPS</span>
                    </div>
                </div>

                <!-- Card Action Bar -->
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between gap-1.5">
                    <!-- Primary Open Button (min 44px touch target) -->
                    <a href="{{ $link->url }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="flex-1 min-h-[40px] px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                        <span data-en="Open" data-bn="প্রবেশ করুন">Open</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>

                    <!-- Copy Link -->
                    <button type="button" 
                            @click="copyLink('{{ $link->url }}', '{{ addslashes($link->title) }}')" 
                            class="min-h-[40px] min-w-[40px] p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors flex items-center justify-center cursor-pointer" 
                            title="Copy Link">
                        📋
                    </button>

                    <!-- Share -->
                    <button type="button" 
                            @click="shareLink({{ json_encode($linkPayload) }})" 
                            class="min-h-[40px] min-w-[40px] p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors flex items-center justify-center cursor-pointer" 
                            title="Share Link">
                        ↗️
                    </button>

                    <!-- QR Code -->
                    <button type="button" 
                            @click="openQR({{ json_encode($linkPayload) }})" 
                            class="min-h-[40px] min-w-[40px] p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors flex items-center justify-center cursor-pointer" 
                            title="QR Code">
                        📱
                    </button>

                    <!-- Details / More Menu -->
                    <button type="button" 
                            @click="openDetails({{ json_encode($linkPayload) }})" 
                            class="min-h-[40px] min-w-[40px] p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors flex items-center justify-center cursor-pointer" 
                            title="Link Details">
                        ℹ️
                    </button>

                    @if($canManage ?? false)
                    <!-- Admin Edit Button -->
                    <button type="button" 
                            @click="openEditModal({{ json_encode($linkPayload) }})" 
                            class="min-h-[40px] min-w-[40px] p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-colors flex items-center justify-center cursor-pointer" 
                            title="Edit Link">
                        ✏️
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200">
                <span class="text-3xl">🔍</span>
                <h4 class="font-bold text-slate-800 text-sm mt-2" data-en="No links found" data-bn="কোনো লিংক পাওয়া যায়নি">No links found</h4>
                <p class="text-xs text-slate-400 mt-1" data-en="Try adjusting your search query or selected category." data-bn="আপনার সার্চ কীওয়ার্ড বা ক্যাটাগরি পরিবর্তন করে দেখুন।">Try adjusting your search query or selected category.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- 08. COMPLIANCE & IMPORTANT INFORMATION NOTICE -->
    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 flex items-start gap-3 text-xs text-slate-600 leading-relaxed">
        <span class="text-base flex-shrink-0">⚖️</span>
        <div class="space-y-1">
            <h4 class="font-bold text-slate-800" data-en="Important Resource & Compliance Notice" data-bn="জরুরী রিসোর্স ও সম্মতি নোটিশ">
                Important Resource & Compliance Notice
            </h4>
            <p data-en="External web resources and third-party portals may update or change over time. The 'Verified' status reflects the most recent administrative review recorded in this system. Always verify current SBL terms, package details, and refund conditions directly from the official backoffice before presenting financial calculations or business agreements to prospects." data-bn="এক্সটার্নাল ওয়েব রিসোর্স ও থার্ড পার্টি পোর্টাল সময়ে পরিবর্তিত হতে পারে। 'যাচাইকৃত' স্ট্যাটাস এই সিস্টেমে সংরক্ষিত সর্বশেষ অ্যাডমিন পর্যালোচনার ভিত্তিতে প্রদর্শিত। কোনো প্রসপেক্টকে আর্থিক হিসাব বা চুক্তি উপস্থাপনের পূর্বে সর্বদা অফিসিয়াল ব্যাকঅফিস থেকে বর্তমান শর্তাবলী যাচাই করে নিন।">
                External web resources and third-party portals may update or change over time. The 'Verified' status reflects the most recent administrative review recorded in this system. Always verify current SBL terms, package details, and refund conditions directly from the official backoffice before presenting financial calculations or business agreements to prospects.
            </p>
        </div>
    </div>

    <!-- ==================== MODALS & DRAWERS ==================== -->

    <!-- QR CODE MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="qrModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition x-cloak>
        <div @click.away="qrModalOpen = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 space-y-4 text-center">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900" x-text="qrTarget.title"></h3>
                <button type="button" @click="qrModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer">✕</button>
            </div>

            <div class="py-2 flex flex-col items-center">
                <div class="p-3 bg-white rounded-2xl border-2 border-slate-200 shadow-sm inline-block">
                    <template x-if="qrModalOpen && qrTarget && qrTarget.url">
                        <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(qrTarget.url)" 
                             :alt="qrTarget.title" 
                             class="w-48 h-48 rounded-lg object-contain">
                    </template>
                </div>
                <div class="mt-3 text-xs font-mono text-slate-500 truncate max-w-xs" x-text="qrTarget.url"></div>
                <p class="text-[11px] text-slate-400 mt-1" data-en="Scan with camera for instant mobile access during meetings" data-bn="মিটিং বা সেমিনারে ক্যামেরায় স্ক্যান করে সরাসরি ওপেন করুন">
                    Scan with camera for instant mobile access during meetings
                </p>
            </div>

            <div class="pt-2 flex items-center gap-2">
                <button type="button" @click="copyLink(qrTarget.url, qrTarget.title)" class="flex-1 py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer">
                    📋 Copy Link
                </button>
                <a :href="qrTarget.url" target="_blank" rel="noopener noreferrer" class="flex-1 py-2 px-3 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors text-center">
                    ↗️ Open URL
                </a>
            </div>
        </div>
    </div>

    <!-- LINK DETAILS DRAWER / MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="detailsModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition x-cloak>
        <div @click.away="detailsModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" x-show="selectedLink">
            <template x-if="selectedLink">
                <div class="space-y-4">
                    <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center text-2xl flex-shrink-0" x-text="selectedLink.icon || '🌐'"></div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900" x-text="selectedLink.title"></h3>
                                <span class="text-xs text-slate-500" x-text="selectedLink.category"></span>
                            </div>
                        </div>
                        <button type="button" @click="detailsModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer">✕</button>
                    </div>

                    <!-- Details Table -->
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Domain</span>
                            <span class="font-mono font-bold text-slate-800" x-text="selectedLink.domain"></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Full Destination</span>
                            <span class="font-mono text-slate-700 truncate max-w-[220px]" x-text="selectedLink.url"></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Verification Status</span>
                            <span class="font-bold capitalize" 
                                  :class="selectedLink.is_official && selectedLink.verification_status === 'verified' ? 'text-emerald-600' : (selectedLink.verification_status === 'verified' ? 'text-blue-600' : 'text-slate-500')"
                                  x-text="selectedLink.is_official && selectedLink.verification_status === 'verified' ? '✓ Official SBL Verified' : selectedLink.verification_status"></span>
                        </div>
                        <template x-if="selectedLink.verified_at">
                            <div class="flex justify-between py-1.5 border-b border-slate-100">
                                <span class="text-slate-500">Verified Date</span>
                                <span class="text-slate-700" x-text="selectedLink.verified_at"></span>
                            </div>
                        </template>
                        <template x-if="selectedLink.verified_by">
                            <div class="flex justify-between py-1.5 border-b border-slate-100">
                                <span class="text-slate-500">Verified By</span>
                                <span class="text-slate-700" x-text="selectedLink.verified_by"></span>
                            </div>
                        </template>
                        <template x-if="selectedLink.tags">
                            <div class="flex justify-between py-1.5 border-b border-slate-100">
                                <span class="text-slate-500">Tags</span>
                                <span class="text-slate-700" x-text="selectedLink.tags"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Description -->
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs text-slate-600 leading-relaxed" x-text="selectedLink.description || 'No detailed description.'"></div>

                    <!-- Action Buttons -->
                    <div class="pt-2 flex items-center gap-2">
                        <a :href="selectedLink.url" target="_blank" rel="noopener noreferrer" class="flex-1 py-2.5 px-3 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors text-center">
                            Open Resource ↗
                        </a>
                        <button type="button" @click="copyLink(selectedLink.url, selectedLink.title)" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer">
                            Copy
                        </button>
                        <a :href="'https://wa.me/?text=' + encodeURIComponent(selectedLink.title + '\n' + selectedLink.url)" target="_blank" rel="noopener noreferrer" class="py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors" title="Share via WhatsApp">
                            WhatsApp
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- SHARE FALLBACK MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="shareModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition x-cloak>
        <div @click.away="shareModalOpen = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900" data-en="Share Resource" data-bn="রিসোর্স শেয়ার করুন">Share Resource</h3>
                <button type="button" @click="shareModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer">✕</button>
            </div>

            <p class="text-xs font-semibold text-slate-700" x-text="shareTarget.title"></p>

            <div class="grid grid-cols-2 gap-2 text-xs">
                <a :href="'https://wa.me/?text=' + encodeURIComponent(shareTarget.title + '\n' + shareTarget.url)" target="_blank" rel="noopener noreferrer" class="p-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl font-bold flex items-center justify-center gap-2">
                    <span>💬</span> WhatsApp
                </a>
                <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareTarget.url)" target="_blank" rel="noopener noreferrer" class="p-3 bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 rounded-xl font-bold flex items-center justify-center gap-2">
                    <span>🌐</span> Facebook
                </a>
            </div>

            <button type="button" @click="copyLink(shareTarget.url, shareTarget.title); shareModalOpen = false;" class="w-full py-2.5 px-3 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-colors cursor-pointer">
                📋 Copy Link to Clipboard
            </button>
        </div>
    </div>

    @if($canManage ?? false)
    <!-- ADMIN: ADD LINK MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="addModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition x-cloak>
        <div @click.away="addModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🌐</span>
                    <h3 class="text-base font-bold text-slate-900" data-en="Add SBL Resource / Link" data-bn="নতুন এসবিএল লিংক যুক্ত করুন">Add SBL Resource / Link</h3>
                </div>
                <button type="button" @click="addModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('ecosystem.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-4 gap-3">
                    <div class="col-span-3">
                        <label class="block font-bold text-slate-700 uppercase mb-1">Resource Title *</label>
                        <input type="text" name="title" required placeholder="e.g. SBL Dropshipping Portal" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Icon</label>
                        <input type="text" name="icon" value="🌐" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none text-center text-base">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Destination URL *</label>
                    <input type="url" name="url" required placeholder="https://shop.sbl.com.bd" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="Official Portals">Official Portals</option>
                            <option value="Business & Commerce">Business & Commerce</option>
                            <option value="Affiliate & Community">Affiliate & Community</option>
                            <option value="Support & Training">Support & Training</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                        <input type="text" name="badge" placeholder="e.g. Dropship Hub" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <!-- Verification & Official Controls (STRICT POLICY) -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-slate-800">Verification Status</label>
                        <select name="verification_status" class="px-2.5 py-1 text-xs bg-white border border-slate-200 rounded-lg">
                            <option value="unverified" selected>Unverified</option>
                            <option value="verified">Verified</option>
                            <option value="needs_review">Needs Review</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_official" value="1" id="add_is_official" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                        <label for="add_is_official" class="text-slate-700 cursor-pointer">
                            Mark as Official SBL (Requires Verified status)
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief description of this platform..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 font-bold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-xs transition-colors cursor-pointer">Save Resource</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADMIN: EDIT LINK MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="editModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition x-cloak>
        <div @click.away="editModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">✏️</span>
                    <h3 class="text-base font-bold text-slate-900" data-en="Edit Resource / Link" data-bn="রিসোর্স সম্পাদনা করুন">Edit Resource / Link</h3>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form :action="'{{ url('/ecosystem') }}/' + editingLink.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-4 gap-3">
                    <div class="col-span-3">
                        <label class="block font-bold text-slate-700 uppercase mb-1">Resource Title *</label>
                        <input type="text" name="title" x-model="editingLink.title" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Icon</label>
                        <input type="text" name="icon" x-model="editingLink.icon" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none text-center text-base">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Destination URL *</label>
                    <input type="url" name="url" x-model="editingLink.url" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="category" x-model="editingLink.category" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="Official Portals">Official Portals</option>
                            <option value="Business & Commerce">Business & Commerce</option>
                            <option value="Affiliate & Community">Affiliate & Community</option>
                            <option value="Support & Training">Support & Training</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                        <input type="text" name="badge" x-model="editingLink.badge" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <!-- Verification Status Toggle -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-slate-800">Verification Status</label>
                        <select name="verification_status" x-model="editingLink.verification_status" class="px-2.5 py-1 text-xs bg-white border border-slate-200 rounded-lg">
                            <option value="unverified">Unverified</option>
                            <option value="verified">Verified</option>
                            <option value="needs_review">Needs Review</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_official" value="1" id="edit_is_official" :checked="editingLink.is_official" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                        <label for="edit_is_official" class="text-slate-700 cursor-pointer">
                            Mark as Official SBL (Requires Verified status)
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" x-model="editingLink.description" rows="2" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <button type="submit" form="delete-link-form" class="text-rose-600 hover:text-rose-700 font-semibold cursor-pointer">
                        Delete Resource
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 font-bold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-xs transition-colors cursor-pointer">Update Resource</button>
                    </div>
                </div>
            </form>

            <form id="delete-link-form" :action="'{{ url('/ecosystem') }}/' + editingLink.id" method="POST" onsubmit="return confirm('Permanently remove this resource?');" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
    @endif

    <!-- FLOATING TOAST NOTIFICATION -->
    <div x-show="toastVisible" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-4 py-2.5 rounded-xl shadow-xl border border-slate-700 text-xs font-bold flex items-center gap-2"
         x-cloak>
        <span class="text-emerald-400">✓</span>
        <span x-text="toastMessage"></span>
    </div>

</div>
