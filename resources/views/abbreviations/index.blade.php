@extends('layouts.app')
@section('page-title', 'SBL Business Glossary')
@section('page-subtitle', 'Essential SBL, e-commerce, marketing and business terms explained simply.')
@section('content')
<div class="space-y-4" x-data="abbreviationManager" data-terms="{{ json_encode($abbreviations) }}" data-can-manage="{{ auth()->user()?->hasPermission('users.manage') ? '1' : '0' }}">
    <!-- 1. Header Area (Compact & Informative) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse"></span>
                <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight" data-en="SBL Business Glossary" data-bn="এসবিএল বিজনেস গ্লসারি">SBL Business Glossary</h2>
                <span class="px-2 py-0.5 rounded-full bg-orange-50 text-orange-700 text-xs font-bold border border-orange-200">
                    <span x-text="terms.length">37</span> <span data-en="Terms" data-bn="টি টার্ম">Terms</span>
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1" data-en="Essential SBL, e-commerce, marketing and business terms explained simply." data-bn="এসবিএল, ই-কমার্স, মার্কেটিং ও বিজনেস সংক্রান্ত প্রয়োজনীয় টার্মের সহজ ব্যাখ্যা।">
                Essential SBL, e-commerce, marketing and business terms explained simply.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <!-- Quick Learning Flashcard Mode -->
            <button type="button" 
                    @click="startLearning()" 
                    class="px-3 py-2 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-700 text-xs font-bold transition-all flex items-center gap-1.5 border border-orange-200 shadow-2xs active:scale-95 cursor-pointer">
                <span>🎓</span>
                <span data-en="Learn 5 Terms" data-bn="৫টি টার্ম শিখুন">Learn 5 Terms</span>
            </button>

            <!-- Add Abbreviation (Admin Only) -->
            <button type="button" 
                    class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs active:scale-95 cursor-pointer" 
                    x-show="canManage" 
                    @click="edit()">
                <x-ui-icon name="plus" class="w-3.5 h-3.5" />
                <span data-en="Add Term" data-bn="নতুন টার্ম যোগ করুন">Add Term</span>
            </button>
        </div>
    </div>

    <p x-show="error" x-cloak class="app-notice app-notice-error" role="alert" x-text="error"></p>

    <!-- 2. Sticky Search & Filter Bar -->
    <div class="sticky top-0 z-20 bg-slate-50/95 backdrop-blur-md pt-1 pb-3 space-y-2.5">
        <!-- Search Input -->
        <div class="bg-white rounded-2xl p-2 sm:p-2.5 border border-slate-200/90 shadow-xs flex items-center gap-2">
            <div class="pl-2 text-slate-400">
                <x-ui-icon name="search" class="w-4 h-4" />
            </div>
            <input type="search" 
                   x-model.debounce.200ms="search" 
                   placeholder="Search term, abbreviation or meaning..." 
                   data-en="Search term, abbreviation or meaning..." 
                   data-bn="টার্ম, সংক্ষেপ বা অর্থ দিয়ে খুঁজুন..." 
                   class="w-full py-1.5 px-2 text-xs sm:text-sm bg-transparent border-0 focus:ring-0 focus:outline-hidden text-slate-800 placeholder-slate-400">
            <button type="button" 
                    x-show="search" 
                    @click="search = ''" 
                    class="p-1 text-slate-400 hover:text-slate-600 text-base font-bold transition-colors cursor-pointer"
                    aria-label="Clear search">
                &times;
            </button>

            <!-- Sorting Dropdown -->
            <div class="border-l border-slate-200 pl-2 pr-1 shrink-0">
                <select x-model="sortBy" class="text-xs bg-slate-50 border border-slate-200 rounded-lg py-1 px-2 text-slate-700 font-medium focus:ring-1 focus:ring-orange-500 cursor-pointer">
                    <option value="recommended">Recommended</option>
                    <option value="az">A–Z</option>
                    <option value="category">Category</option>
                </select>
            </div>
        </div>

        <!-- Category Filter Chips (Desktop compact, Mobile horizontal scroll) -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5 -mx-2 px-2 sm:mx-0 sm:px-0">
            <template x-for="cat in categories" :key="cat.slug">
                <button type="button" 
                        @click="categoryFilter = cat.slug" 
                        :class="categoryFilter === cat.slug 
                            ? 'bg-slate-900 text-white border-slate-900 shadow-xs' 
                            : 'bg-white text-slate-600 hover:bg-slate-100/80 border-slate-200/80'" 
                        class="px-2.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <span x-text="cat.icon" class="text-xs"></span>
                    <span x-text="($store.lang && $store.lang.current === 'bn') ? cat.name_bn : cat.name_en"></span>
                    <span :class="categoryFilter === cat.slug ? 'bg-slate-700 text-slate-200' : 'bg-slate-100 text-slate-500'" 
                          class="px-1.5 py-0.2 rounded-full text-[10px] font-mono" 
                          x-text="cat.count"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- 3. "Start Here" Essential Terms (Shown when no search & category = all) -->
    <div x-show="!search && categoryFilter === 'all'" x-cloak class="bg-gradient-to-r from-orange-50/70 via-amber-50/40 to-slate-50 p-3.5 sm:p-4 rounded-2xl border border-orange-200/80 space-y-2.5 shadow-2xs">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-base">🚀</span>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider" data-en="Start Here (Essential Terms)" data-bn="শুরুতেই যা জানা জরুরি (মূল টার্ম)">Start Here (Essential Terms)</h3>
            </div>
            <span class="text-[11px] text-slate-500 font-medium" data-en="Tap any term for quick details" data-bn="বিস্তারিত দেখতে যেকোনো টার্মে চাপুন">Tap any term for quick details</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <template x-for="eTerm in essentialTerms" :key="eTerm.id">
                <div @click="openDetails(eTerm)" 
                     class="p-2.5 bg-white/90 hover:bg-white rounded-xl border border-orange-100 hover:border-orange-300 shadow-2xs hover:shadow-xs transition-all cursor-pointer group flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-1">
                        <span class="font-mono font-black text-xs text-orange-700 bg-orange-50 px-2 py-0.5 rounded-md border border-orange-200/80 group-hover:bg-orange-600 group-hover:text-white transition-colors" x-text="eTerm.code"></span>
                        <template x-if="isSblSpecific(eTerm.code)">
                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-purple-100 text-purple-700">SBL</span>
                        </template>
                    </div>
                    <div class="mt-1.5 font-bold text-[11px] text-slate-800 truncate" x-text="eTerm.name"></div>
                    <div class="text-[10px] text-slate-500 truncate" x-text="eTerm.meaning_bn"></div>
                </div>
            </template>
        </div>
    </div>

    <!-- 4. DESKTOP VIEW: Scannable Table (>= 768px) -->
    <div class="hidden md:block bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50/90 border-b border-slate-200/80 text-slate-600 font-bold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-3 text-center w-12">#</th>
                        <th class="py-3 px-4 w-44 whitespace-nowrap" data-en="Short Form" data-bn="সংক্ষিপ্ত রূপ">Short Form</th>
                        <th class="py-3 px-4 min-w-[200px]" data-en="Full Form" data-bn="পূর্ণ রূপ">Full Form</th>
                        <th class="py-3 px-4 w-36 whitespace-nowrap" data-en="Category" data-bn="ক্যাটাগরি">Category</th>
                        <th class="py-3 px-4 min-w-[260px]" data-en="Simple Meaning" data-bn="সহজ অর্থ">Simple Meaning</th>
                        <th class="py-3 px-4 text-right w-36 whitespace-nowrap" data-en="Action" data-bn="অ্যাকশন">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(term, index) in filtered" :key="term.id">
                        <tr class="hover:bg-slate-50/70 transition-colors group">
                            <!-- Number -->
                            <td class="py-3 px-3 text-center text-xs font-semibold text-slate-400 font-mono" x-text="index + 1"></td>

                            <!-- Short Form -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <button type="button" 
                                            @click="openDetails(term)" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black font-mono tracking-wide bg-orange-50 hover:bg-orange-600 text-orange-700 hover:text-white border border-orange-200/80 shadow-2xs transition-all cursor-pointer">
                                        <span x-text="term.icon || '📖'" class="text-xs"></span>
                                        <span x-text="term.code"></span>
                                    </button>
                                    <template x-if="isSblSpecific(term.code)">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200" title="SBL Specific Term">SBL</span>
                                    </template>
                                </div>
                            </td>

                            <!-- Full Form -->
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 text-xs sm:text-sm hover:text-orange-600 cursor-pointer" @click="openDetails(term)" x-text="term.name"></span>
                                <template x-if="getFormula(term.code)">
                                    <span class="ml-2 inline-flex items-center gap-1 text-[10px] font-mono px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span>📐 Formula</span>
                                    </span>
                                </template>
                            </td>

                            <!-- Category -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200/60" x-text="term.category || term.category_slug"></span>
                            </td>

                            <!-- Simple Meaning (1-line, easily scannable) -->
                            <td class="py-3 px-4">
                                <p class="text-xs text-slate-600 font-normal leading-relaxed truncate max-w-sm" x-text="term.meaning_bn"></p>
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1 justify-end">
                                    <!-- View Details Button -->
                                    <button type="button" 
                                            @click="openDetails(term)" 
                                            class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-orange-50 hover:text-orange-600 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-1 cursor-pointer" 
                                            title="View Details">
                                        <span>👁️</span>
                                        <span data-en="View" data-bn="দেখুন">View</span>
                                    </button>

                                    <!-- Favorite Star -->
                                    <button type="button" 
                                            @click="toggleFavorite(term.code)" 
                                            :class="isFavorite(term.code) ? 'text-amber-500' : 'text-slate-300 hover:text-amber-400'" 
                                            class="p-1.5 rounded-lg transition-colors cursor-pointer" 
                                            title="Save to My Terms">
                                        <span x-text="isFavorite(term.code) ? '⭐' : '☆'"></span>
                                    </button>

                                    <!-- Copy Button -->
                                    <button type="button" 
                                            @click="copyTerm(term)" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" 
                                            title="Copy Term">
                                        <x-ui-icon name="copy" class="w-3.5 h-3.5" />
                                    </button>

                                    <!-- Admin Edit -->
                                    <button type="button" 
                                            x-show="canManage" 
                                            @click="edit(term)" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer" 
                                            title="Edit Term">
                                        <x-ui-icon name="edit" class="w-3.5 h-3.5" />
                                    </button>

                                    <!-- Admin Delete -->
                                    <button type="button" 
                                            x-show="canManage" 
                                            @click="remove(term)" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" 
                                            title="Delete Term">
                                        <x-ui-icon name="trash" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. MOBILE VIEW: Compact Cards / Accordions (<= 767px) -->
    <div class="block md:hidden space-y-2.5">
        <template x-for="(term, index) in filtered" :key="term.id">
            <div class="p-3.5 bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-xs transition-all space-y-2">
                <!-- Top Row: Badge, Category, SBL indicator, Favorite Star -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-black font-mono tracking-wide bg-orange-50 text-orange-700 border border-orange-200/80 shadow-2xs flex items-center gap-1">
                            <span x-text="term.icon || '📖'" class="text-xs"></span>
                            <span x-text="term.code"></span>
                        </span>
                        <template x-if="isSblSpecific(term.code)">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">SBL Related</span>
                        </template>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200/60" x-text="term.category || term.category_slug"></span>
                        <button type="button" 
                                @click="toggleFavorite(term.code)" 
                                :class="isFavorite(term.code) ? 'text-amber-500' : 'text-slate-300'" 
                                class="p-1 rounded-lg text-sm cursor-pointer" 
                                aria-label="Favorite">
                            <span x-text="isFavorite(term.code) ? '⭐' : '☆'"></span>
                        </button>
                    </div>
                </div>

                <!-- Middle: Full Form -->
                <div>
                    <h4 class="font-bold text-slate-900 text-sm" x-text="term.name"></h4>
                </div>

                <!-- Meaning (Short Bangla) -->
                <div class="text-xs text-slate-600 font-normal leading-relaxed">
                    <p x-text="term.meaning_bn"></p>
                </div>

                <!-- Formula pill if present -->
                <template x-if="getFormula(term.code)">
                    <div class="p-2 rounded-xl bg-emerald-50/70 border border-emerald-200/70 text-[11px] font-mono text-emerald-800 flex items-center gap-1.5">
                        <span class="font-bold">📐</span>
                        <span class="truncate" x-text="getFormula(term.code)"></span>
                    </div>
                </template>

                <!-- Footer Action Buttons -->
                <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100 text-xs">
                    <button type="button" 
                            @click="openDetails(term)" 
                            class="flex-1 py-1.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs text-center transition-all flex items-center justify-center gap-1.5 shadow-2xs active:scale-98 cursor-pointer">
                        <span>👁️</span>
                        <span data-en="View Details" data-bn="বিস্তারিত দেখুন">View Details</span>
                    </button>

                    <button type="button" 
                            @click="copyTerm(term)" 
                            class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer" 
                            title="Copy">
                        <x-ui-icon name="copy" class="w-3.5 h-3.5" />
                    </button>

                    <button type="button" 
                            @click="shareTerm(term)" 
                            class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer" 
                            title="Share">
                        <span>📤</span>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- 6. Empty Search State -->
    <div x-show="!filtered.length" x-cloak class="bg-white rounded-2xl p-8 sm:p-12 text-center border border-slate-200/80 shadow-xs space-y-3">
        <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center mx-auto text-2xl border border-orange-200">
            🔍
        </div>
        <h4 class="text-base font-bold text-slate-800" data-en="No matching term found." data-bn="কোনো টার্ম খুঁজে পাওয়া যায়নি।">No matching term found.</h4>
        <p class="text-xs sm:text-sm text-slate-400 max-w-sm mx-auto" data-en="Try searching with a different short form, English full name, or Bangla keyword." data-bn="ভিন্ন কোনো শর্ট ফর্ম, ইংরেজি নাম অথবা বাংলা কি-ওয়ার্ড দিয়ে খুঁজে দেখুন।">
            Try searching with a different short form, English full name, or Bangla keyword.
        </p>
        <button type="button" 
                @click="search = ''; categoryFilter = 'all'" 
                class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-all shadow-xs cursor-pointer">
            <span data-en="Clear Search & Show All" data-bn="সার্চ মুছুন ও সবগুলো দেখুন">Clear Search & Show All</span>
        </button>
    </div>

    <!-- 7. TERM DETAILS DRAWER / BOTTOM SHEET -->
    <div x-show="drawerOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:justify-end bg-slate-900/60 backdrop-blur-xs transition-opacity" 
         @keydown.escape.window="closeDetails()">
        <!-- Backdrop Click -->
        <div class="fixed inset-0" @click="closeDetails()"></div>

        <!-- Sheet Panel -->
        <div class="relative w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-2xl sm:m-4 max-h-[90vh] sm:h-auto overflow-y-auto shadow-2xl border border-slate-200 z-10 space-y-4 p-5 sm:p-6"
             @click.outside="closeDetails()">
            
            <!-- Mobile Handle -->
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto -mt-2 mb-2 sm:hidden"></div>

            <template x-if="selectedTerm">
                <div class="space-y-4">
                    <!-- Drawer Header -->
                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-12 h-12 rounded-2xl bg-orange-600 text-white font-mono font-black text-base flex items-center justify-center shadow-md flex-shrink-0" x-text="selectedTerm.code"></span>
                            <div>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700" x-text="selectedTerm.category || selectedTerm.category_slug"></span>
                                    <template x-if="isSblSpecific(selectedTerm.code)">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200">SBL Related</span>
                                    </template>
                                </div>
                                <h3 class="font-black text-slate-900 text-base sm:text-lg mt-0.5 leading-snug" x-text="selectedTerm.name"></h3>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <!-- Star Favorite -->
                            <button type="button" 
                                    @click="toggleFavorite(selectedTerm.code)" 
                                    :class="isFavorite(selectedTerm.code) ? 'text-amber-500' : 'text-slate-300'" 
                                    class="p-2 rounded-xl hover:bg-slate-100 transition-colors text-base cursor-pointer" 
                                    title="Favorite">
                                <span x-text="isFavorite(selectedTerm.code) ? '⭐' : '☆'"></span>
                            </button>

                            <!-- Close Button -->
                            <button type="button" 
                                    @click="closeDetails()" 
                                    class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer" 
                                    aria-label="Close details">
                                <x-ui-icon name="close" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Formula Box (If Available) -->
                    <template x-if="getFormula(selectedTerm.code)">
                        <div class="p-3.5 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/80 space-y-1">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-800">
                                <span>📐</span>
                                <span data-en="Calculation Formula" data-bn="গণনার সূত্র">Calculation Formula</span>
                            </div>
                            <div class="font-mono text-xs sm:text-sm font-bold text-emerald-950 bg-white/80 p-2 rounded-xl border border-emerald-200/60" x-text="getFormula(selectedTerm.code)"></div>
                        </div>
                    </template>

                    <!-- Example Box (If Available) -->
                    <template x-if="getExample(selectedTerm.code)">
                        <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-2">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-amber-900">
                                <span>💡</span>
                                <span data-en="Practical Example" data-bn="বাস্তব উদাহরণ">Practical Example</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="bg-white/90 p-2 rounded-xl border border-amber-200/60 font-mono text-slate-700" x-text="getExample(selectedTerm.code).metric1"></div>
                                <div class="bg-white/90 p-2 rounded-xl border border-amber-200/60 font-mono text-slate-700" x-text="getExample(selectedTerm.code).metric2 || getExample(selectedTerm.code).impressions"></div>
                            </div>
                            <div class="p-2 rounded-xl bg-orange-100/70 border border-orange-200 text-xs font-black text-orange-950 text-center font-mono" x-text="'Result: ' + getExample(selectedTerm.code).result"></div>
                            <p class="text-[11px] text-amber-900/80 leading-relaxed font-normal" x-text="getExample(selectedTerm.code).note_bn"></p>
                        </div>
                    </template>

                    <!-- Bangla Meaning Section -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider" data-en="Bangla Meaning" data-bn="বাংলা অর্থ">Bangla Meaning</label>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 text-xs sm:text-sm font-bold text-slate-800 leading-relaxed" style="word-break: normal; overflow-wrap: break-word;" x-text="selectedTerm.meaning_bn"></div>
                    </div>

                    <!-- Detailed Explanation Section -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider" data-en="Detailed Explanation & Use Case" data-bn="বিস্তারিত ব্যাখ্যা ও ব্যবহার">Detailed Explanation & Use Case</label>
                        <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 text-xs sm:text-sm text-slate-700 leading-relaxed space-y-2" style="word-break: normal; overflow-wrap: break-word; line-height: 1.6;" x-text="selectedTerm.description_bn"></div>
                    </div>

                    <!-- Related Terms Section -->
                    <template x-if="getRelatedTerms(selectedTerm.code).length > 0">
                        <div class="space-y-1.5 pt-1">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider" data-en="Related Terms" data-bn="সম্পর্কিত অন্যান্য টার্ম">Related Terms</label>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <template x-for="rTerm in getRelatedTerms(selectedTerm.code)" :key="rTerm.id">
                                    <button type="button" 
                                            @click="openDetails(rTerm)" 
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-slate-100 hover:bg-orange-50 text-slate-700 hover:text-orange-700 border border-slate-200 hover:border-orange-200 transition-colors cursor-pointer flex items-center gap-1">
                                        <span>→</span>
                                        <span x-text="rTerm.code"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Drawer Action Bar -->
                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-slate-100">
                        <div class="flex items-center gap-2 flex-1">
                            <button type="button" 
                                    @click="copyTerm(selectedTerm)" 
                                    class="flex-1 py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <x-ui-icon name="copy" class="w-3.5 h-3.5" />
                                <span data-en="Copy" data-bn="কপি করুন">Copy</span>
                            </button>

                            <button type="button" 
                                    @click="shareTerm(selectedTerm)" 
                                    class="flex-1 py-2 px-3 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                                <span>📤</span>
                                <span data-en="Share" data-bn="শেয়ার করুন">Share</span>
                            </button>
                        </div>

                        <!-- Admin Edit inside Drawer -->
                        <template x-if="canManage">
                            <button type="button" 
                                    @click="edit(selectedTerm)" 
                                    class="p-2 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors cursor-pointer" 
                                    title="Edit Term">
                                <x-ui-icon name="edit" class="w-4 h-4" />
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- 8. QUICK LEARNING FLASHCARD MODAL ("Learn 5 Terms") -->
    <div x-show="learningOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         @keydown.escape.window="learningOpen = false">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4 text-center"
             @click.outside="learningOpen = false">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2.5 py-1 rounded-full border border-orange-200">
                    <span data-en="Flashcard" data-bn="ফ্ল্যাশকার্ড">Flashcard</span> <span x-text="(learningIndex + 1) + ' / ' + learningList.length"></span>
                </span>
                <button type="button" @click="learningOpen = false" class="p-1 text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <template x-if="learningList[learningIndex]">
                <div class="space-y-3 py-2">
                    <div class="w-16 h-16 rounded-3xl bg-slate-900 text-orange-400 font-black font-mono text-xl flex items-center justify-center mx-auto shadow-md border border-slate-700" x-text="learningList[learningIndex].code"></div>
                    <h3 class="text-lg font-black text-slate-900" x-text="learningList[learningIndex].name"></h3>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800" x-text="learningList[learningIndex].meaning_bn"></div>
                    <p class="text-xs text-slate-600 leading-relaxed text-left p-3 rounded-xl bg-white border border-slate-100" style="word-break: normal; overflow-wrap: break-word;" x-text="learningList[learningIndex].description_bn"></p>
                </div>
            </template>

            <div class="flex items-center justify-between gap-3 pt-2">
                <button type="button" 
                        @click="prevLearning()" 
                        :disabled="learningIndex === 0" 
                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                    <span data-en="← Previous" data-bn="← পূর্ববর্তী">← Previous</span>
                </button>
                <button type="button" 
                        @click="nextLearning()" 
                        class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold shadow-xs active:scale-95 cursor-pointer">
                    <span x-text="learningIndex === learningList.length - 1 ? 'Finish ✓' : 'Next →'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- 9. ADD/EDIT MODAL (Admin Only) -->
    <div x-show="editing" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         @keydown.escape.window="if (!busy) editing = false">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto"
             @click.outside="if (!busy) editing = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900" x-text="form.id ? 'Edit Glossary Term' : 'Add New Glossary Term'"></h3>
                <button type="button" 
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer" 
                        :disabled="busy" 
                        @click="editing = false; error = ''"
                        aria-label="Close dialog">
                    <x-ui-icon name="close" class="w-4 h-4" />
                </button>
            </div>

            <form @submit.prevent="save()" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Short Form *</label>
                        <input type="text" 
                               data-autofocus 
                               x-model="form.code" 
                               maxlength="50" 
                               required 
                               placeholder="e.g. COD, ROI, ROAS" 
                               class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Icon / Emoji</label>
                        <input type="text" 
                               x-model="form.icon" 
                               maxlength="20" 
                               placeholder="e.g. 📦, 💰, 🚚" 
                               class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
                    <input type="text" 
                           x-model="form.name" 
                           maxlength="200" 
                           required 
                           placeholder="e.g. Return On Investment" 
                           class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Category *</label>
                    <select x-model="form.category_slug" required class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        <option value="ecommerce">E-Commerce Core</option>
                        <option value="marketing">Marketing & Ads</option>
                        <option value="logistics">Logistics & Delivery</option>
                        <option value="network">SBL Network & System</option>
                        <option value="finance">Finance & Operations</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">বাংলা অর্থ (Bangla Meaning) *</label>
                    <textarea x-model="form.meaning_bn" 
                              maxlength="1000" 
                              required 
                              rows="2" 
                              placeholder="e.g. বিনিয়োগের বিপরীতে লাভের হার" 
                              class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">ব্যবহার ও ব্যাখ্যা (Use Case & Description) *</label>
                    <textarea x-model="form.description_bn" 
                              maxlength="3000" 
                              required 
                              rows="3" 
                              placeholder="বিস্তারিত ব্যবহার ও প্রেক্ষাপট লিখুন..." 
                              class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tag (Optional)</label>
                    <input type="text" 
                           x-model="form.tag" 
                           maxlength="100" 
                           placeholder="e.g. Payment, Marketing, Sourcing" 
                           class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" 
                            class="btn-secondary" 
                            :disabled="busy" 
                            @click="editing = false; error = ''">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="btn-primary" 
                            :disabled="busy">
                        <span x-text="busy ? 'Saving...' : (form.id ? 'Update Term' : 'Save Term')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
