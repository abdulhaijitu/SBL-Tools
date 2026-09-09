{{-- SBL Marketing Global Omnisearch & Command Palette --}}
<div x-data="globalOmnisearch" 
     x-cloak 
     x-show="open" 
     @keydown.escape.window="closeModal()" 
     class="fixed inset-0 z-50 flex items-start justify-center pt-10 sm:pt-20 px-4 pb-6 overflow-y-auto"
     role="dialog" 
     aria-modal="true" 
     aria-label="Global Search">
    
    <!-- Backdrop -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()" 
         class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" 
         aria-hidden="true"></div>

    <!-- Modal Dialog Window -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
         class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden flex flex-col z-10 my-auto sm:my-0 max-h-[85vh]">
        
        <!-- Search Input Bar -->
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-slate-100 bg-slate-50/50">
            <svg class="w-5 h-5 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            
            <input id="global-omnisearch-input" 
                   type="text" 
                   x-model="query" 
                   @input="onInput()" 
                   @keydown.down.prevent="navigateDown()" 
                   @keydown.up.prevent="navigateUp()" 
                   @keydown.enter.prevent="selectCurrent()" 
                   placeholder="Search leads, team members, tools, resources, glossary..." 
                   autocomplete="off" 
                   spellcheck="false" 
                   class="w-full bg-transparent border-0 text-slate-900 text-sm md:text-base focus:ring-0 focus:outline-none placeholder-slate-400 font-medium">

            <!-- Loading Spinner -->
            <div x-show="loading" class="flex-shrink-0 animate-spin text-orange-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- Clear Button -->
            <button type="button" 
                    x-show="query.length > 0" 
                    @click="query = ''; onInput(); document.getElementById('global-omnisearch-input').focus()" 
                    class="text-xs px-2 py-0.5 rounded-md bg-slate-200/70 hover:bg-slate-300 text-slate-600 transition-colors cursor-pointer"
                    title="Clear">
                ✕
            </button>

            <!-- ESC Badge -->
            <kbd @click="closeModal()" class="px-2 py-0.5 text-[10px] font-semibold text-slate-400 bg-white border border-slate-200 rounded-md shadow-2xs cursor-pointer hover:bg-slate-100">
                ESC
            </kbd>
        </div>

        <!-- Quick Filter Category Pills -->
        <div class="px-4 py-2 border-b border-slate-100 bg-white flex items-center gap-1.5 overflow-x-auto text-[11px] font-semibold">
            <span class="text-slate-400 uppercase tracking-wider text-[10px] mr-1">Filter:</span>
            <button type="button" @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-orange-50 text-orange-700 border-orange-200' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'" class="px-2.5 py-0.5 rounded-lg border transition-all whitespace-nowrap cursor-pointer">All (<span x-text="results.length"></span>)</button>
            <template x-for="(items, cat) in categories" :key="cat">
                <button type="button" @click="activeTab = cat" :class="activeTab === cat ? 'bg-orange-50 text-orange-700 border-orange-200' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'" class="px-2.5 py-0.5 rounded-lg border transition-all whitespace-nowrap cursor-pointer">
                    <span x-text="cat"></span> (<span x-text="items.length"></span>)
                </button>
            </template>
        </div>

        <!-- Results Container -->
        <div class="overflow-y-auto p-2 divide-y divide-slate-100 flex-1 max-h-[50vh]">
            <!-- Empty state when no matches -->
            <div x-show="results.length === 0" class="py-12 text-center">
                <div class="text-3xl mb-2">🔍</div>
                <div class="text-sm font-bold text-slate-800">No results found</div>
                <div class="text-xs text-slate-400 mt-1">We couldn't find anything matching "<span class="font-semibold text-slate-600" x-text="query"></span>". Try another keyword.</div>
            </div>

            <!-- Grouped Category Sections -->
            <template x-for="(items, categoryName) in categories" :key="categoryName">
                <div x-show="activeTab === 'all' || activeTab === categoryName" class="py-2 first:pt-1 last:pb-1">
                    <!-- Category Header -->
                    <div class="px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span x-text="categoryName"></span>
                        <span class="text-[10px] font-normal lowercase text-slate-400" x-text="items.length + ' result' + (items.length > 1 ? 's' : '')"></span>
                    </div>

                    <!-- Items in Category -->
                    <div class="mt-1 space-y-1">
                        <template x-for="item in items" :key="item.url + item.title">
                            <div :data-search-idx="results.indexOf(item)"
                                 @mouseenter="selectedIndex = results.indexOf(item)"
                                 @click="if (item.external) { window.open(item.url, '_blank'); } else { window.location.href = item.url; }"
                                 :class="selectedIndex === results.indexOf(item) ? 'bg-orange-50/90 text-orange-950 border-orange-200 shadow-2xs' : 'hover:bg-slate-50 text-slate-800 border-transparent'"
                                 class="flex items-center justify-between px-3 py-2 rounded-xl border transition-all cursor-pointer group">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm flex-shrink-0 group-hover:bg-orange-100 group-hover:text-orange-700 transition-colors"
                                         x-text="item.icon || '📌'">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-bold truncate flex items-center gap-2">
                                            <span x-html="highlightMatch(item.title)"></span>
                                            <template x-if="item.badge">
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200" x-text="item.badge"></span>
                                            </template>
                                        </div>
                                        <div class="text-[11px] text-slate-500 truncate mt-0.5" x-html="highlightMatch(item.subtitle)"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                                    <span x-show="selectedIndex === results.indexOf(item)" class="text-[10px] font-bold text-orange-600 px-1.5 py-0.5 rounded bg-orange-100">
                                        Press ↵
                                    </span>
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-orange-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer Shortcuts -->
        <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1">
                    <kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded text-[10px] shadow-2xs font-mono">↑</kbd>
                    <kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded text-[10px] shadow-2xs font-mono">↓</kbd>
                    <span>navigate</span>
                </span>
                <span class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] shadow-2xs font-mono">↵</kbd>
                    <span>open</span>
                </span>
                <span class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] shadow-2xs font-mono">esc</kbd>
                    <span>close</span>
                </span>
            </div>
            <div class="text-[10px] font-medium text-slate-400">
                SBL Live Search
            </div>
        </div>
    </div>
</div>
