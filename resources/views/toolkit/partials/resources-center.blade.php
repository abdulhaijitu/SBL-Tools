{{-- SBL Marketing Resource Center --}}
@php
    $totalCount = $resources->count();
    $verifiedCount = $resources->filter(fn($r) => $r->is_verified)->count();

    // Map kits with count and purpose
    $kitsData = collect($curatedKits)->map(function($kit) {
        $count = count($kit['resource_ids'] ?? []);
        $purpose = match($kit['id']) {
            'investor' => 'Investor counseling & package explanation',
            'networker' => 'Ranks, commission & leadership resources',
            'prospect' => 'Quick introductory resources',
            'training' => 'Scripts, training and counseling',
            'branding' => 'Logo and promotional assets',
            default => $kit['description'] ?? 'Counseling and marketing resources',
        };

        return [
            'id' => $kit['id'],
            'title' => $kit['title'],
            'icon' => $kit['icon'] ?? '📁',
            'count' => $count,
            'files_label' => "{$count} Files",
            'purpose' => $purpose,
            'resource_ids' => $kit['resource_ids'] ?? [],
        ];
    });

    // Map resources for client-side modal usage
    $clientResources = $resources->map(function($r) {
        $catLower = strtolower($r->category . ' ' . $r->title);
        $standardCategory = 'Documents';
        $typeLabel = 'Official Document';

        if (str_contains($catLower, 'leaflet')) {
            $standardCategory = 'Leaflets';
            $typeLabel = 'Official Leaflet';
        } elseif (str_contains($catLower, 'presentation') || str_contains($catLower, 'slide') || $r->resource_type === 'presentation') {
            $standardCategory = 'Presentations';
            $typeLabel = 'Presentation';
        } elseif (str_contains($catLower, 'guide') || str_contains($catLower, 'policy') || str_contains($catLower, 'rank')) {
            $standardCategory = 'Guides';
            $typeLabel = 'Guide';
        } elseif (str_contains($catLower, 'legal') || str_contains($catLower, 'license') || str_contains($catLower, 'cert') || str_contains($catLower, 'registration')) {
            $standardCategory = 'Legal';
            $typeLabel = 'Legal';
        } elseif (str_contains($catLower, 'brand') || str_contains($catLower, 'logo') || str_contains($catLower, 'asset') || str_contains($catLower, 'media')) {
            $standardCategory = 'Brand Assets';
            $typeLabel = 'Brand Assets';
        } elseif (str_contains($catLower, 'training') || str_contains($catLower, 'academy')) {
            $standardCategory = 'Training';
            $typeLabel = 'Training';
        }

        $size = $r->file_size;
        if (!$size) {
            $size = match($r->id) {
                1 => '1.8 MB',
                2 => '8.4 MB',
                3 => '950 KB',
                4 => '1.2 MB',
                5 => '3.5 MB',
                default => '1.5 MB',
            };
        }

        $desc = $r->description ?: 'Official SBL marketing resource.';
        if ($r->id == 1) $desc = 'National & International package comparison';
        elseif ($r->id == 2) $desc = 'Full business model overview & profit structure';
        elseif ($r->id == 3) $desc = 'Career progression & leadership qualification criteria';
        elseif ($r->id == 4) $desc = 'Official government registration & trade credentials';
        elseif ($r->id == 5) $desc = 'Vector logos, official colors, and promotional artwork';

        $ext = strtoupper($r->file_type);
        if ($r->file_type === 'presentation') $ext = 'PPT/PDF';
        elseif ($r->file_type === 'image') $ext = ($r->id == 5) ? 'ZIP/Image' : 'IMAGE';
        elseif ($r->file_type === 'pdf') $ext = 'PDF';

        return [
            'id' => $r->id,
            'title' => $r->title,
            'category' => $r->category,
            'standardCategory' => $standardCategory,
            'typeLabel' => $typeLabel,
            'description' => $desc,
            'file_type' => $r->file_type,
            'file_url' => $r->file_url,
            'file_size' => $size,
            'file_meta' => "{$ext} • {$size}",
            'icon' => $r->file_icon ?: '📄',
            'is_verified' => (bool)$r->is_verified,
            'status' => $r->is_verified ? 'Verified' : 'Needs Verification',
        ];
    });
@endphp

<div 
    x-data="{
        searchQuery: '',
        selectedCategory: 'All',
        resourcesList: @js($clientResources),
        kitsList: @js($kitsData),
        
        // Modals
        previewModalOpen: false,
        previewItem: null,
        kitModalOpen: false,
        activeKit: null,
        createModalOpen: false,
        editModalOpen: false,
        editingResource: { id: null, title: '', category: 'Official Documents', file_type: 'pdf', file_url: '', file_size: '', badge: '', description: '' },

        // Toast feedback
        toastMessage: '',
        toastVisible: false,
        showToast(msg) {
            this.toastMessage = msg;
            this.toastVisible = true;
            setTimeout(() => { this.toastVisible = false; }, 2400);
        },

        // Copy / Share
        copyLink(url, title = 'Resource') {
            const fullUrl = url.startsWith('http') ? url : window.location.origin + '/' + url.replace(/^\/+/, '');
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(fullUrl);
            } else {
                let el = document.createElement('textarea');
                el.value = fullUrl;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
            }
            this.showToast((title ? title + ' ' : '') + 'link copied!');
        },

        shareResource(item) {
            const fullUrl = item.file_url.startsWith('http') ? item.file_url : window.location.origin + '/' + item.file_url.replace(/^\/+/, '');
            if (navigator.share) {
                navigator.share({
                    title: item.title,
                    text: item.title + ' - SBL Official Resource',
                    url: fullUrl
                }).catch(() => {});
            } else {
                this.copyLink(item.file_url, item.title);
            }
        },

        shareKit(kit) {
            let text = `*SBL Toolkit: ${kit.title}*\n${kit.purpose}\nIncludes ${kit.count} official resources.`;
            if (navigator.share) {
                navigator.share({
                    title: kit.title,
                    text: text,
                    url: window.location.href
                }).catch(() => {});
            } else {
                this.showToast(`${kit.title} summary copied!`);
            }
        },

        openPreview(item) {
            this.previewItem = item;
            this.previewModalOpen = true;
        },

        openKit(kit) {
            this.activeKit = kit;
            this.kitModalOpen = true;
        },

        openEditModal(item) {
            this.editingResource = Object.assign({}, item);
            this.editModalOpen = true;
        },

        resourceMatches(stdCat, origCat, searchTarget) {
            if (this.selectedCategory !== 'All') {
                const sc = this.selectedCategory.toLowerCase();
                if (!stdCat.toLowerCase().includes(sc) && !origCat.toLowerCase().includes(sc)) {
                    return false;
                }
            }
            if (this.searchQuery.trim()) {
                const q = this.searchQuery.toLowerCase().trim();
                if (!searchTarget.includes(q)) {
                    return false;
                }
            }
            return true;
        },

        getKitResources(resourceIds) {
            return this.resourcesList.filter(r => resourceIds.includes(r.id));
        }
    }"
    class="space-y-6 sm:space-y-7 pb-16 antialiased"
>
    <!-- Toast Notification -->
    <div 
        x-show="toastVisible" 
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
        <span x-text="toastMessage"></span>
    </div>

    <!-- ==========================================
         1. COMPACT PAGE HEADER
         ========================================== -->
    <header class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Official SBL Resources</span>
                    <span class="text-slate-300">•</span>
                    <span>{{ $totalCount }} Resources</span>
                    <span class="text-slate-300">•</span>
                    <span>{{ $verifiedCount }} Verified</span>
                </div>
                <h1 class="text-2xl sm:text-[28px] font-extrabold text-slate-900 tracking-tight leading-tight">
                    SBL Resource Center
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 max-w-2xl leading-normal">
                    Official documents, presentations and marketing materials in one place.
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
                    <span>+ Add Resource</span>
                </button>
            </div>
            @endif
        </div>

        @if($verifiedCount < $totalCount)
        <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center gap-2 text-xs text-amber-800 bg-amber-50/70 px-3 py-2 rounded-lg border border-amber-200/60">
            <span class="text-amber-600 font-bold shrink-0">⚠ Note:</span>
            <span>Resources marked <strong>Needs Verification</strong> should be verified against the current SBL business plan before client counseling.</span>
        </div>
        @endif
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
                placeholder="Search resources..." 
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
            <template x-for="cat in ['All', 'Documents', 'Presentations', 'Leaflets', 'Guides', 'Legal', 'Brand Assets', 'Training']" :key="cat">
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
         3. SECTION A: COUNSELING / MARKETING KITS
         ========================================== -->
    <section x-show="!searchQuery && selectedCategory === 'All'" class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                    Quick Resource Kits
                </h2>
                <p class="text-xs text-slate-500">Curated document sets for client meetings and team training</p>
            </div>
            <span class="text-xs text-slate-400">{{ count($curatedKits) }} Kits</span>
        </div>

        <!-- Desktop Kits Table -->
        <div class="hidden sm:block bg-white border border-slate-200/90 rounded-xl overflow-hidden shadow-xs">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-semibold text-xs tracking-wider uppercase">
                        <th class="py-3 px-4 sm:px-6 w-1/4">Kit</th>
                        <th class="py-3 px-4 w-28">Files</th>
                        <th class="py-3 px-4">Purpose</th>
                        <th class="py-3 px-4 sm:px-6 text-right w-44">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($curatedKits as $kit)
                        @php
                            $resCount = count($kit['resource_ids'] ?? []);
                            $kitPurpose = match($kit['id']) {
                                'investor' => 'Investor counseling & package explanation',
                                'networker' => 'Ranks, commission & leadership resources',
                                'prospect' => 'Quick introductory resources',
                                'training' => 'Scripts, training and counseling',
                                'branding' => 'Logo and promotional assets',
                                default => $kit['description'] ?? 'Counseling and marketing resources',
                            };
                            $kitJson = [
                                'id' => $kit['id'],
                                'title' => $kit['title'],
                                'icon' => $kit['icon'] ?? '📁',
                                'count' => $resCount,
                                'purpose' => $kitPurpose,
                                'resource_ids' => $kit['resource_ids'] ?? [],
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Kit Name Column -->
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-xl shrink-0">{{ $kit['icon'] ?? '📁' }}</span>
                                    <span class="font-bold text-slate-900 text-sm">{{ $kit['title'] }}</span>
                                </div>
                            </td>

                            <!-- Files Column -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-xs font-semibold">{{ $resCount }} Files</span>
                            </td>

                            <!-- Purpose Column -->
                            <td class="py-3.5 px-4 text-slate-600">
                                <span>{{ $kitPurpose }}</span>
                            </td>

                            <!-- Action Column -->
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button 
                                        type="button" 
                                        @click="openKit(@js($kitJson))" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors cursor-pointer min-h-[32px]"
                                    >
                                        <span>View Kit</span>
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="shareKit(@js($kitJson))" 
                                        class="inline-flex items-center px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg border border-slate-200/80 transition-colors cursor-pointer min-h-[32px]"
                                    >
                                        Share
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Kits List (Compact 72-100px items, 360px-430px) -->
        <div class="sm:hidden bg-white border border-slate-200/90 rounded-xl divide-y divide-slate-100 overflow-hidden shadow-xs">
            @foreach($curatedKits as $kit)
                @php
                    $resCount = count($kit['resource_ids'] ?? []);
                    $kitPurpose = match($kit['id']) {
                        'investor' => 'Investor counseling & package explanation',
                        'networker' => 'Ranks, commission & leadership resources',
                        'prospect' => 'Quick introductory resources',
                        'training' => 'Scripts, training and counseling',
                        'branding' => 'Logo and promotional assets',
                        default => $kit['description'] ?? 'Counseling and marketing resources',
                    };
                    $kitJson = [
                        'id' => $kit['id'],
                        'title' => $kit['title'],
                        'icon' => $kit['icon'] ?? '📁',
                        'count' => $resCount,
                        'purpose' => $kitPurpose,
                        'resource_ids' => $kit['resource_ids'] ?? [],
                    ];
                @endphp
                <div class="p-3.5 hover:bg-slate-50/70 transition-colors">
                    <div class="flex items-start justify-between gap-2.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="text-xl shrink-0">{{ $kit['icon'] ?? '📁' }}</span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $kit['title'] }}</h3>
                                    <span class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded text-[10px] font-semibold">{{ $resCount }} Files</span>
                                </div>
                                <p class="text-xs text-slate-500 truncate mt-0.5">{{ $kitPurpose }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <button 
                                type="button" 
                                @click="openKit(@js($kitJson))" 
                                class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white min-h-[36px] flex items-center justify-center shadow-xs cursor-pointer"
                            >
                                View
                            </button>
                            <button 
                                type="button" 
                                @click="shareKit(@js($kitJson))" 
                                class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 min-h-[36px] flex items-center justify-center cursor-pointer"
                            >
                                Share
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ==========================================
         4. SECTION B: MAIN RESOURCE LIBRARY
         ========================================== -->
    <section class="space-y-3 pt-1">
        <div class="flex items-center justify-between px-1">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                    Main Resource Library
                </h2>
                <p class="text-xs text-slate-500">Official presentations, leaflets, legal compliance and brand files</p>
            </div>
            <span class="text-xs text-slate-400">{{ $totalCount }} Resources</span>
        </div>

        <!-- Desktop Table Layout -->
        <div class="hidden sm:block bg-white border border-slate-200/90 rounded-xl overflow-hidden shadow-xs">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-semibold text-xs tracking-wider uppercase">
                        <th class="py-3 px-4 sm:px-6">Resource</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">File</th>
                        <th class="py-3 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($resources as $resource)
                        @php
                            $catLower = strtolower($resource->category . ' ' . $resource->title);
                            $stdCat = 'Documents';
                            $typeBadge = $resource->category ?: 'Official Document';

                            if (str_contains($catLower, 'leaflet')) {
                                $stdCat = 'Leaflets';
                                $typeBadge = 'Official Leaflet';
                            } elseif (str_contains($catLower, 'presentation') || str_contains($catLower, 'slide') || $resource->resource_type === 'presentation') {
                                $stdCat = 'Presentations';
                                $typeBadge = 'Presentation';
                            } elseif (str_contains($catLower, 'guide') || str_contains($catLower, 'policy') || str_contains($catLower, 'rank')) {
                                $stdCat = 'Guides';
                                $typeBadge = 'Guide';
                            } elseif (str_contains($catLower, 'legal') || str_contains($catLower, 'license') || str_contains($catLower, 'cert') || str_contains($catLower, 'registration')) {
                                $stdCat = 'Legal';
                                $typeBadge = 'Legal';
                            } elseif (str_contains($catLower, 'brand') || str_contains($catLower, 'logo') || str_contains($catLower, 'asset') || str_contains($catLower, 'media')) {
                                $stdCat = 'Brand Assets';
                                $typeBadge = 'Brand Assets';
                            } elseif (str_contains($catLower, 'training') || str_contains($catLower, 'academy')) {
                                $stdCat = 'Training';
                                $typeBadge = 'Training';
                            }

                            $size = $resource->file_size;
                            if (!$size) {
                                $size = match($resource->id) {
                                    1 => '1.8 MB',
                                    2 => '8.4 MB',
                                    3 => '950 KB',
                                    4 => '1.2 MB',
                                    5 => '3.5 MB',
                                    default => '1.5 MB',
                                };
                            }

                            $desc = $resource->description ?: 'Official SBL marketing resource.';
                            if ($resource->id == 1) $desc = 'National & International package comparison';
                            elseif ($resource->id == 2) $desc = 'Full business model overview & profit structure';
                            elseif ($resource->id == 3) $desc = 'Career progression & leadership qualification criteria';
                            elseif ($resource->id == 4) $desc = 'Official government registration & trade credentials';
                            elseif ($resource->id == 5) $desc = 'Vector logos, official colors, and promotional artwork';

                            $ext = strtoupper($resource->file_type);
                            if ($resource->file_type === 'presentation') $ext = 'PPT/PDF';
                            elseif ($resource->file_type === 'image') $ext = ($resource->id == 5) ? 'ZIP/Image' : 'IMAGE';
                            elseif ($resource->file_type === 'pdf') $ext = 'PDF';

                            $fileMeta = "{$ext} • {$size}";
                            $searchTarget = strtolower($resource->title . ' ' . $resource->category . ' ' . $stdCat . ' ' . $desc . ' ' . $fileMeta);

                            $resourceJson = [
                                'id' => $resource->id,
                                'title' => $resource->title,
                                'category' => $resource->category,
                                'standardCategory' => $stdCat,
                                'typeLabel' => $typeBadge,
                                'description' => $desc,
                                'file_type' => $resource->file_type,
                                'file_url' => $resource->file_url,
                                'file_size' => $size,
                                'file_meta' => $fileMeta,
                                'icon' => $resource->file_icon ?: '📄',
                                'is_verified' => (bool)$resource->is_verified,
                                'status' => $resource->is_verified ? 'Verified' : 'Needs Verification',
                            ];
                        @endphp
                        <tr 
                            x-show="resourceMatches(@js($stdCat), @js($resource->category), @js($searchTarget))"
                            class="hover:bg-slate-50/70 transition-colors"
                        >
                            <!-- Resource Column (Icon + Title + 1-line description) -->
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl shrink-0">{{ $resource->file_icon ?: '📄' }}</span>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-sm leading-snug">{{ $resource->title }}</div>
                                        <div class="text-xs text-slate-500 truncate max-w-sm lg:max-w-md mt-0.5">{{ $desc }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Type Column -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md text-xs font-medium">{{ $resource->category }}</span>
                            </td>

                            <!-- Status Column -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($resource->is_verified)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200/70" title="Verify current SBL plan before presenting">
                                        <span>⚠</span> Needs Verification
                                    </span>
                                @endif
                            </td>

                            <!-- File Column -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-mono text-xs text-slate-600 font-medium">{{ $fileMeta }}</span>
                            </td>

                            <!-- Actions Column -->
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button 
                                        type="button" 
                                        @click="openPreview(@js($resourceJson))" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors cursor-pointer min-h-[32px]"
                                    >
                                        <span>Preview</span>
                                    </button>

                                    <a 
                                        href="{{ $resource->file_url }}" 
                                        download 
                                        target="_blank" 
                                        rel="noopener noreferrer" 
                                        class="inline-flex items-center px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg border border-slate-200/80 transition-colors min-h-[32px]"
                                    >
                                        Download
                                    </a>

                                    <button 
                                        type="button" 
                                        @click="shareResource(@js($resourceJson))" 
                                        class="inline-flex items-center px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg border border-slate-200/80 transition-colors cursor-pointer min-h-[32px]"
                                    >
                                        Share
                                    </button>

                                    @if($canManage ?? false)
                                    <button 
                                        type="button" 
                                        @click="openEditModal(@js($resourceJson))" 
                                        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                        title="Edit Resource"
                                    >
                                        ✏️
                                    </button>

                                    <form action="{{ route('marketing-resources.destroy', $resource->id) }}" method="POST" onsubmit="return confirm('Remove this resource?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="Delete Resource">
                                            🗑️
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 text-xs">
                                No resources available in the library.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile List Layout (80-120px compact items, 360px-430px) -->
        <div class="sm:hidden bg-white border border-slate-200/90 rounded-xl divide-y divide-slate-100 overflow-hidden shadow-xs">
            @forelse($resources as $resource)
                @php
                    $catLower = strtolower($resource->category . ' ' . $resource->title);
                    $stdCat = 'Documents';
                    $typeBadge = $resource->category ?: 'Official Document';

                    if (str_contains($catLower, 'leaflet')) {
                        $stdCat = 'Leaflets';
                        $typeBadge = 'Official Leaflet';
                    } elseif (str_contains($catLower, 'presentation') || str_contains($catLower, 'slide') || $resource->resource_type === 'presentation') {
                        $stdCat = 'Presentations';
                        $typeBadge = 'Presentation';
                    } elseif (str_contains($catLower, 'guide') || str_contains($catLower, 'policy') || str_contains($catLower, 'rank')) {
                        $stdCat = 'Guides';
                        $typeBadge = 'Guide';
                    } elseif (str_contains($catLower, 'legal') || str_contains($catLower, 'license') || str_contains($catLower, 'cert') || str_contains($catLower, 'registration')) {
                        $stdCat = 'Legal';
                        $typeBadge = 'Legal';
                    } elseif (str_contains($catLower, 'brand') || str_contains($catLower, 'logo') || str_contains($catLower, 'asset') || str_contains($catLower, 'media')) {
                        $stdCat = 'Brand Assets';
                        $typeBadge = 'Brand Assets';
                    } elseif (str_contains($catLower, 'training') || str_contains($catLower, 'academy')) {
                        $stdCat = 'Training';
                        $typeBadge = 'Training';
                    }

                    $size = $resource->file_size;
                    if (!$size) {
                        $size = match($resource->id) {
                            1 => '1.8 MB',
                            2 => '8.4 MB',
                            3 => '950 KB',
                            4 => '1.2 MB',
                            5 => '3.5 MB',
                            default => '1.5 MB',
                        };
                    }

                    $desc = $resource->description ?: 'Official SBL marketing resource.';
                    if ($resource->id == 1) $desc = 'National & International package comparison';
                    elseif ($resource->id == 2) $desc = 'Full business model overview & profit structure';
                    elseif ($resource->id == 3) $desc = 'Career progression & leadership qualification criteria';
                    elseif ($resource->id == 4) $desc = 'Official government registration & trade credentials';
                    elseif ($resource->id == 5) $desc = 'Vector logos, official colors, and promotional artwork';

                    $ext = strtoupper($resource->file_type);
                    if ($resource->file_type === 'presentation') $ext = 'PPT/PDF';
                    elseif ($resource->file_type === 'image') $ext = ($resource->id == 5) ? 'ZIP/Image' : 'IMAGE';
                    elseif ($resource->file_type === 'pdf') $ext = 'PDF';

                    $fileMeta = "{$ext} • {$size}";
                    $searchTarget = strtolower($resource->title . ' ' . $resource->category . ' ' . $stdCat . ' ' . $desc . ' ' . $fileMeta);

                    $resourceJson = [
                        'id' => $resource->id,
                        'title' => $resource->title,
                        'category' => $resource->category,
                        'standardCategory' => $stdCat,
                        'typeLabel' => $typeBadge,
                        'description' => $desc,
                        'file_type' => $resource->file_type,
                        'file_url' => $resource->file_url,
                        'file_size' => $size,
                        'file_meta' => $fileMeta,
                        'icon' => $resource->file_icon ?: '📄',
                        'is_verified' => (bool)$resource->is_verified,
                        'status' => $resource->is_verified ? 'Verified' : 'Needs Verification',
                    ];
                @endphp
                <div 
                    x-show="resourceMatches(@js($stdCat), @js($resource->category), @js($searchTarget))"
                    class="p-3.5 hover:bg-slate-50/70 transition-colors space-y-2.5"
                >
                    <div class="flex items-start justify-between gap-2.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="text-xl shrink-0">{{ $resource->file_icon ?: '📄' }}</span>
                            <div class="min-w-0">
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $resource->title }}</h3>
                                <div class="flex items-center gap-1.5 mt-0.5 text-xs text-slate-500">
                                    <span class="font-medium text-slate-600">{{ $resource->category }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="font-mono text-slate-400 text-[11px]">{{ $fileMeta }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status badge on mobile -->
                        <div class="shrink-0">
                            @if($resource->is_verified)
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Verified
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                    ⚠ Verify
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 pt-1">
                        <button 
                            type="button" 
                            @click="openPreview(@js($resourceJson))" 
                            class="flex-1 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white min-h-[36px] flex items-center justify-center shadow-xs cursor-pointer"
                        >
                            Preview
                        </button>
                        <a 
                            href="{{ $resource->file_url }}" 
                            download 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="flex-1 py-1.5 rounded-lg text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 min-h-[36px] flex items-center justify-center text-center"
                        >
                            Download
                        </a>
                        <button 
                            type="button" 
                            @click="shareResource(@js($resourceJson))" 
                            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 min-h-[36px] flex items-center justify-center cursor-pointer"
                        >
                            Share
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-slate-500 text-xs">
                    No resources available.
                </div>
            @endforelse
        </div>
    </section>

    <!-- ==========================================
         5. RESOURCE PREVIEW MODAL
         ========================================== -->
    <div 
        role="dialog" 
        aria-modal="true" 
        tabindex="-1" 
        x-show="previewModalOpen" 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
        style="display: none;"
    >
        <div 
            @click.away="previewModalOpen = false" 
            x-show="previewModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-white rounded-2xl max-w-2xl w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto"
        >
            <template x-if="previewItem">
                <div>
                    <!-- Header -->
                    <div class="flex items-start justify-between border-b border-slate-100 pb-3 gap-3">
                        <div class="space-y-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-700" x-text="previewItem.typeLabel"></span>
                                <span class="text-xs font-mono text-slate-500" x-text="previewItem.file_meta"></span>
                                <template x-if="previewItem.is_verified">
                                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">✓ Verified</span>
                                </template>
                                <template x-if="!previewItem.is_verified">
                                    <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded">⚠ Needs Verification</span>
                                </template>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900" x-text="previewItem.title"></h3>
                            <p class="text-xs text-slate-500" x-text="previewItem.description"></p>
                        </div>
                        <button type="button" @click="previewModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
                    </div>

                    <!-- Preview Frame / Content -->
                    <div class="py-4">
                        <template x-if="previewItem.file_type === 'image' || previewItem.file_url.match(/\.(jpeg|jpg|gif|png|webp)$/i)">
                            <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center p-2 max-h-96">
                                <img :src="previewItem.file_url" :alt="previewItem.title" class="max-h-92 w-auto object-contain rounded-lg">
                            </div>
                        </template>

                        <template x-if="previewItem.file_type !== 'image' && !previewItem.file_url.match(/\.(jpeg|jpg|gif|png|webp)$/i)">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center space-y-3">
                                <span class="text-4xl" x-text="previewItem.icon"></span>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm" x-text="previewItem.title"></div>
                                    <div class="text-xs text-slate-500 mt-0.5" x-text="previewItem.file_meta"></div>
                                </div>
                                <p class="text-xs text-slate-600 max-w-md mx-auto leading-relaxed" x-text="previewItem.description"></p>
                            </div>
                        </template>
                    </div>

                    <!-- Footer Actions -->
                    <div class="border-t border-slate-100 pt-3 flex items-center justify-between gap-2 flex-wrap">
                        <button 
                            type="button" 
                            @click="copyLink(previewItem.file_url, previewItem.title)" 
                            class="px-3 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer"
                        >
                            Copy Link
                        </button>

                        <div class="flex items-center gap-2">
                            <button 
                                type="button" 
                                @click="previewModalOpen = false" 
                                class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 cursor-pointer"
                            >
                                Close
                            </button>
                            <a 
                                :href="previewItem.file_url" 
                                download 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Download File</span>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- ==========================================
         6. RESOURCE KIT VIEW MODAL
         ========================================== -->
    <div 
        role="dialog" 
        aria-modal="true" 
        tabindex="-1" 
        x-show="kitModalOpen" 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
        style="display: none;"
    >
        <div 
            @click.away="kitModalOpen = false" 
            x-show="kitModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-white rounded-2xl max-w-xl w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto"
        >
            <template x-if="activeKit">
                <div>
                    <!-- Header -->
                    <div class="flex items-start justify-between border-b border-slate-100 pb-3 gap-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl" x-text="activeKit.icon"></span>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900" x-text="activeKit.title"></h3>
                                <p class="text-xs text-slate-500" x-text="activeKit.purpose"></p>
                            </div>
                        </div>
                        <button type="button" @click="kitModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
                    </div>

                    <!-- Resources in Kit -->
                    <div class="py-3 space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Included Files</h4>
                        <div class="divide-y divide-slate-100 border border-slate-200/80 rounded-xl overflow-hidden">
                            <template x-for="item in getKitResources(activeKit.resource_ids)" :key="'kit-res-' + item.id">
                                <div class="p-3 bg-white hover:bg-slate-50/70 flex items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="text-lg shrink-0" x-text="item.icon"></span>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 text-xs sm:text-sm truncate" x-text="item.title"></div>
                                            <div class="text-[11px] text-slate-500 font-mono" x-text="item.file_meta"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button 
                                            type="button" 
                                            @click="openPreview(item)" 
                                            class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold cursor-pointer"
                                        >
                                            Preview
                                        </button>
                                        <a 
                                            :href="item.file_url" 
                                            download 
                                            target="_blank" 
                                            rel="noopener noreferrer" 
                                            class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium"
                                        >
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="border-t border-slate-100 pt-3 flex items-center justify-between gap-2">
                        <button 
                            type="button" 
                            @click="shareKit(activeKit)" 
                            class="px-3.5 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors cursor-pointer"
                        >
                            Share Kit Summary
                        </button>
                        <button 
                            type="button" 
                            @click="kitModalOpen = false" 
                            class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-xs transition-colors cursor-pointer"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    @if($canManage ?? false)
    <!-- ==========================================
         7. CREATE RESOURCE MODAL (Admin Only)
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
            class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📄</span>
                    <h3 class="text-base font-bold text-slate-900">Add New Marketing Resource</h3>
                </div>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('marketing-resources.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Resource Title *</label>
                    <input type="text" name="title" required placeholder="e.g. SBL Compensation Plan Deck" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="Official Documents">Official Documents</option>
                            <option value="Presentations">Presentations</option>
                            <option value="Leaflets">Leaflets</option>
                            <option value="Policies & Guides">Policies & Guides</option>
                            <option value="Legal & Compliance">Legal & Compliance</option>
                            <option value="Brand Assets">Brand Assets</option>
                            <option value="Training Materials">Training Materials</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Type *</label>
                        <select name="file_type" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="pdf">PDF Document</option>
                            <option value="presentation">Presentation (PPT)</option>
                            <option value="image">Image (PNG/JPG)</option>
                            <option value="doc">Word / Text</option>
                            <option value="zip">ZIP Archive</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File URL *</label>
                        <input type="text" name="file_url" required placeholder="docs/plan.pdf or https://..." class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Size</label>
                        <input type="text" name="file_size" placeholder="e.g. 2.4 MB" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description (1-line)</label>
                    <textarea name="description" rows="2" placeholder="Brief summary of this resource..." class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors cursor-pointer">Save Resource</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================
         8. EDIT RESOURCE MODAL (Admin Only)
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
            class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">✏️</span>
                    <h3 class="text-base font-bold text-slate-900">Edit Marketing Resource</h3>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form :action="'{{ url('/marketing-resources') }}/' + editingResource.id" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Resource Title *</label>
                    <input type="text" name="title" x-model="editingResource.title" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="category" x-model="editingResource.category" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="Official Documents">Official Documents</option>
                            <option value="Presentations">Presentations</option>
                            <option value="Leaflets">Leaflets</option>
                            <option value="Policies & Guides">Policies & Guides</option>
                            <option value="Legal & Compliance">Legal & Compliance</option>
                            <option value="Brand Assets">Brand Assets</option>
                            <option value="Training Materials">Training Materials</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Type *</label>
                        <select name="file_type" x-model="editingResource.file_type" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="pdf">PDF Document</option>
                            <option value="presentation">Presentation (PPT)</option>
                            <option value="image">Image (PNG/JPG)</option>
                            <option value="doc">Word / Text</option>
                            <option value="zip">ZIP Archive</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File URL *</label>
                        <input type="text" name="file_url" x-model="editingResource.file_url" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Size</label>
                        <input type="text" name="file_size" x-model="editingResource.file_size" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description (1-line)</label>
                    <textarea name="description" x-model="editingResource.description" rows="2" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors cursor-pointer">Update Resource</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
