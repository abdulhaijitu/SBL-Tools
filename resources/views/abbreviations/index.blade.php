@extends('layouts.app')
@section('page-title', 'Abbreviation')
@section('page-subtitle', 'Essential SBL Dropshipping, E-Commerce, Logistics & Marketing Glossary')
@section('content')
<div class="space-y-4" x-data="abbreviationManager" data-terms="{{ json_encode($abbreviations) }}" data-can-manage="{{ auth()->user()?->hasPermission('users.manage') ? '1' : '0' }}">
    <!-- Heading & Add Action -->
    <div class="section-heading">
        <div>
            <h2>Dropshipping Abbreviations</h2>
            <p>Essential glossary and operational concepts for SBL team members.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="btn-primary" x-show="canManage" @click="edit()">
                <x-ui-icon name="plus" class="w-4 h-4" />
                <span>Add Abbreviation</span>
            </button>
        </div>
    </div>

    <p x-show="error" x-cloak class="app-notice app-notice-error" role="alert" x-text="error"></p>

    <!-- Search & Summary Bar -->
    <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs sm:text-sm text-slate-600">
            <span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse"></span>
            <span>Total <strong class="font-semibold text-slate-900" x-text="terms.length"></strong> terms listed</span>
            <span class="text-slate-300">|</span>
            <span class="text-slate-500"><span x-text="filtered.length"></span> matching</span>
        </div>

        <div class="relative w-full sm:w-80">
            <input type="search" 
                   x-model="search" 
                   placeholder="Search short form, name, meaning..." 
                   class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-200 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 bg-slate-50/50">
            <div class="absolute left-3 top-2.5 text-slate-400 pointer-events-none">
                <x-ui-icon name="search" class="w-3.5 h-3.5" />
            </div>
            <button type="button" 
                    x-show="search" 
                    @click="search = ''" 
                    class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-base font-bold">
                &times;
            </button>
        </div>
    </div>

    <!-- Abbreviation Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-slate-600 font-semibold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-3 text-center w-12">No</th>
                        <th class="py-3 px-4 w-40 whitespace-nowrap">Short Form</th>
                        <th class="py-3 px-4 min-w-[220px]">Abbreviation</th>
                        <th class="py-3 px-4 min-w-[260px]">Use Case</th>
                        <th class="py-3 px-4 text-right w-28 whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(term, index) in filtered" :key="term.id">
                        <tr class="hover:bg-slate-50/60 transition-colors group">
                            <!-- No (Index) -->
                            <td class="py-3 px-3 text-center text-xs font-semibold text-slate-400" x-text="index + 1"></td>

                            <!-- Short Form -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold font-mono tracking-wide bg-orange-50 text-orange-700 border border-orange-200/70 shadow-2xs">
                                    <span x-text="term.icon || '📖'" class="text-xs"></span>
                                    <span x-text="term.code"></span>
                                </span>
                            </td>

                            <!-- Abbreviation (Full Name & Meaning) -->
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-900 text-xs sm:text-sm leading-snug" x-text="term.name"></div>
                                <div class="text-[11px] sm:text-xs text-slate-500 mt-0.5 leading-relaxed" x-text="term.meaning_bn"></div>
                                <template x-if="term.tag">
                                    <span class="inline-block mt-1 text-[10px] font-medium px-1.5 py-0.5 rounded bg-slate-100 text-slate-600" x-text="term.tag"></span>
                                </template>
                            </td>

                            <!-- Use Case -->
                            <td class="py-3 px-4">
                                <p class="text-xs text-slate-600 leading-relaxed" x-text="term.description_bn"></p>
                            </td>

                            <!-- Action Icons -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1 justify-end">
                                    <!-- Copy Button -->
                                    <button type="button" 
                                            @click="copy(term.code + ' - ' + term.name + ': ' + term.meaning_bn)" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors focus:ring-1 focus:ring-slate-300" 
                                            title="Copy full abbreviation details"
                                            aria-label="Copy full abbreviation details">
                                        <x-ui-icon name="copy" class="w-4 h-4" />
                                    </button>

                                    <!-- Edit Button -->
                                    <button type="button" 
                                            x-show="canManage" 
                                            :disabled="busy" 
                                            @click="edit(term)" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors focus:ring-1 focus:ring-blue-300" 
                                            title="Edit abbreviation"
                                            aria-label="Edit abbreviation">
                                        <x-ui-icon name="edit" class="w-4 h-4" />
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" 
                                            x-show="canManage" 
                                            :disabled="busy" 
                                            @click="remove(term)" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors focus:ring-1 focus:ring-rose-300" 
                                            title="Delete abbreviation"
                                            aria-label="Delete abbreviation">
                                        <x-ui-icon name="trash" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!filtered.length">
                        <td colspan="5" class="py-12 px-4 text-center text-slate-400">
                            <p class="text-sm font-medium">No abbreviations found matching your search.</p>
                            <p class="text-xs text-slate-400 mt-1">Try searching with a different short form, English name or Bangla keyword.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Modal Dialog -->
    <div x-show="editing" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         @keydown.escape.window="if (!busy) editing = false">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto"
             @click.outside="if (!busy) editing = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900" x-text="form.id ? 'Edit Abbreviation' : 'Add New Abbreviation'"></h3>
                <button type="button" 
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100" 
                        :disabled="busy" 
                        @click="editing = false; error = ''"
                        aria-label="Close dialog">
                    <x-ui-icon name="close" class="w-4 h-4" />
                </button>
            </div>

            <form @submit.prevent="save()" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Short Form Code -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Short Form *</label>
                        <input type="text" 
                               data-autofocus 
                               x-model="form.code" 
                               maxlength="50" 
                               required 
                               placeholder="e.g. COD, ROI, ACOS" 
                               class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                    </div>

                    <!-- Icon / Emoji -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Icon / Emoji</label>
                        <input type="text" 
                               x-model="form.icon" 
                               maxlength="20" 
                               placeholder="e.g. 📦, 💰, 🚚" 
                               class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                    </div>
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Abbreviation / Full Name *</label>
                    <input type="text" 
                           x-model="form.name" 
                           maxlength="200" 
                           required 
                           placeholder="e.g. Cash On Delivery" 
                           class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                </div>

                <!-- Bangla Meaning -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">বাংলা অর্থ (Bangla Meaning) *</label>
                    <textarea x-model="form.meaning_bn" 
                              maxlength="1000" 
                              required 
                              rows="2" 
                              placeholder="e.g. পণ্য হাতে পেয়ে মূল্য পরিশোধ" 
                              class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500"></textarea>
                </div>

                <!-- Use Case -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">ব্যবহার (Use Case) *</label>
                    <textarea x-model="form.description_bn" 
                              maxlength="3000" 
                              required 
                              rows="3" 
                              placeholder="e.g. কাস্টমার পার্সেল ডেলিভারি পাওয়ার পর কুরিয়ার ম্যানকে ক্যাশ প্রদান করে।" 
                              class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500"></textarea>
                </div>

                <!-- Optional Tag -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tag (Optional)</label>
                    <input type="text" 
                           x-model="form.tag" 
                           maxlength="100" 
                           placeholder="e.g. Payment, Marketing, Logistics" 
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
                        <span x-text="busy ? 'Saving...' : (form.id ? 'Update Abbreviation' : 'Save Abbreviation')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
