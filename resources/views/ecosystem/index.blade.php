@extends('layouts.app')

@section('page-title', 'SBL Ecosystem Directory')
@section('page-subtitle', 'Official Websites, Dropshipping Hubs, Investor Portals & Community Channels')

@section('content')
<div class="space-y-6" x-data="{
    activeCategory: 'all',
    searchQuery: '',
    createModalOpen: false,
    editModalOpen: false,
    editingLink: { id: null, title: '', url: '', category: 'Official Portals', badge: '', description: '', icon: '🌐', sort_order: 0 },
    copyToClipboard(text, title) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: title + ' লিঙ্ক কপি করা হয়েছে!', type: 'success' } }));
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: title + ' link copied to clipboard!', type: 'success' } }));
            });
        }
    }
}">

    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-orange-950 text-white p-6 md:p-8 rounded-2xl border border-slate-800 shadow-md flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-orange-600/30 text-orange-400 border border-orange-500/30 rounded-full text-xs font-bold uppercase tracking-wider">
                <span>🌐</span> The Complete Ecommerce Ecosystem
            </div>
            <h2 class="text-2xl md:text-3xl font-bold tracking-tight">SBL Ecosystem অফিসিয়াল লিঙ্ক ও পোর্টাল</h2>
            <h2 class="text-2xl md:text-3xl font-bold tracking-tight">SBL Ecosystem Portals & Links</h2>
            <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                ড্রপশিপিং মার্কেটপ্লেস, ক্রাউডফান্ডিং ও ইনভেস্টর ড্যাশবোর্ড, মেম্বার ব্যাকঅফিস, ট্রেনিং একাডেমি এবং কমিউনিটি চ্যানেল—সবকিছু এক ক্লিকেই সহজে ভিজিট করুন।
                Instant one-click access to Dropshipping marketplaces, Investor dashboards, Member backoffice, Training academy, and Official channels.
            </p>
        </div>

        <div class="flex items-center gap-3 flex-shrink-0">
            @if(!Auth::user() || Auth::user()->hasRole(['super-admin', 'sales-manager']) || Auth::user()->can('toolkit.view'))
            <button @click="createModalOpen = true" class="px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Platform Link</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Category Filters & Search -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Category Navigation Buttons -->
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 text-xs font-semibold">
            <button @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'bg-orange-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3.5 py-2 rounded-xl transition-colors whitespace-nowrap">
                All Platforms ({{ $links->count() }})
            </button>
            @foreach($categories as $cat)
            <button @click="activeCategory = '{{ $cat }}'"
                    :class="activeCategory === '{{ $cat }}' ? 'bg-orange-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3.5 py-2 rounded-xl transition-colors whitespace-nowrap">
                {{ $cat }}
            </button>
            @endforeach
        </div>

        <!-- Search Bar -->
        <div class="relative w-full md:w-72">
            <input type="text" 
                   x-model="searchQuery" 
                   placeholder="Search platform, service..." 
                   class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    <!-- Platform Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($links as $link)
        <div data-link-id="{{ $link->id }}" x-show="(activeCategory === 'all' || activeCategory === '{{ $link->category }}') && ('{{ strtolower($link->title . ' ' . $link->url . ' ' . $link->description . ' ' . $link->badge) }}'.includes(searchQuery.toLowerCase()))"
             class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between hover:shadow-md hover:border-orange-200 transition-all group">
            
            <div class="space-y-3">
                
                <!-- Card Header -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200/80 group-hover:bg-orange-50 group-hover:border-orange-200 flex items-center justify-center text-2xl transition-colors flex-shrink-0">
                            {{ $link->icon ?: '🌐' }}
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base group-hover:text-orange-600 transition-colors">
                                {{ $link->title }}
                            </h3>
                            <div class="text-[11px] font-semibold text-slate-400">
                                {{ $link->category }}
                            </div>
                        </div>
                    </div>

                    @if($link->badge)
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-orange-100 text-orange-800 border border-orange-200">
                        {{ $link->badge }}
                    </span>
                    @endif
                </div>

                <!-- Description -->
                <p class="text-xs text-slate-600 leading-relaxed min-h-[40px]">
                    {{ $link->description ?: 'Official service within the SBL Ecommerce Ecosystem.' }}
                </p>

                <!-- URL Preview -->
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-mono text-slate-500 truncate max-w-[200px]" title="{{ $link->url }}">
                        {{ preg_replace('#^https?://#', '', rtrim($link->url, '/')) }}
                    </span>
                    <button @click="copyToClipboard('{{ $link->url }}', '{{ addslashes($link->title) }}')"
                            type="button" 
                            title="Copy link"
                            class="text-slate-400 hover:text-orange-600 font-semibold p-1 hover:bg-white rounded transition-colors">
                        📋
                    </button>
                </div>
            </div>

            <!-- Card Bottom Actions -->
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                <a href="{{ $link->url }}" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="flex-1 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl transition-colors flex items-center justify-center gap-2 shadow-xs">
                    <span>Visit Platform</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>

                @if(!Auth::user() || Auth::user()->hasRole(['super-admin', 'sales-manager']))
                <button @click="
                    editingLink = {
                        id: {{ $link->id }},
                        title: '{{ addslashes($link->title) }}',
                        url: '{{ addslashes($link->url) }}',
                        category: '{{ addslashes($link->category) }}',
                        badge: '{{ addslashes($link->badge ?? '') }}',
                        description: '{{ addslashes($link->description ?? '') }}',
                        icon: '{{ addslashes($link->icon ?? '🌐') }}',
                        sort_order: {{ $link->sort_order ?? 0 }}
                    };
                    editModalOpen = true;
                " class="p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-colors" title="Edit">
                    ✏️
                </button>

                <form action="{{ route('ecosystem.destroy', $link) }}" method="POST" onsubmit="return confirm('Remove {{ addslashes($link->title) }}?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" title="Delete">
                        🗑️
                    </button>
                </form>
                @endif
            </div>

        </div>
        @empty
        <div class="col-span-3 p-12 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-sm">
            No ecosystem links configured yet.
        </div>
        @endforelse
    </div>

    <!-- CREATE LINK MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="createModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="createModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🌐</span>
                    <h3 class="text-base font-bold text-slate-900">Add Ecosystem Website / Portal</h3>
                </div>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('ecosystem.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Platform Name *</label>
                        <input type="text" name="title" required placeholder="e.g. SBL Dropshipping Shop" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Icon / Emoji</label>
                        <input type="text" name="icon" value="🌐" placeholder="🛍️" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none text-center text-lg">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Website URL *</label>
                    <input type="url" name="url" required placeholder="https://example.sbl.com.bd" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="Official Portals">Official Portals</option>
                            <option value="Business & Commerce">Business & Commerce</option>
                            <option value="Affiliate & Community">Affiliate & Community</option>
                            <option value="Support & Training">Support & Training</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                        <input type="text" name="badge" placeholder="e.g. Dropshipping Hub" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Bengali Description</label>
                    <textarea name="description" rows="2" placeholder="সংক্ষেপে এই প্ল্যাটফর্মের কাজ বা সুবিধা উল্লেখ করুন..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief description of this platform or service..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors">Save Link</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT LINK MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="editModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="editModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">✏️</span>
                    <h3 class="text-base font-bold text-slate-900">Edit Ecosystem Link</h3>
                </div>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <form :action="'{{ url('/ecosystem') }}/' + editingLink.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Platform Name *</label>
                        <input type="text" name="title" x-model="editingLink.title" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Icon</label>
                        <input type="text" name="icon" x-model="editingLink.icon" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none text-center text-lg">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Website URL *</label>
                    <input type="url" name="url" x-model="editingLink.url" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="category" x-model="editingLink.category" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="Official Portals">Official Portals</option>
                            <option value="Business & Commerce">Business & Commerce</option>
                            <option value="Affiliate & Community">Affiliate & Community</option>
                            <option value="Support & Training">Support & Training</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                        <input type="text" name="badge" x-model="editingLink.badge" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Bengali Description</label>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" x-model="editingLink.description" rows="2" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors">Update Link</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
