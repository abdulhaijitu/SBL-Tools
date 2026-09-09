@extends('layouts.app')

@section('meta-title', 'Links | SBL Marketing')
@section('page-title', 'Links')
@section('page-subtitle', 'Official SBL Portals, E-Commerce Stores & Tools')
@section('meta-description', 'Official SBL website directory, partner links, customer portals, seller hubs and marketing tools.')

@section('content')
<div class="space-y-6"
     x-data="{
        createWebsiteModalOpen: false,
        editWebsiteModalOpen: false,
        editingWebsite: { id: null, title: '', url: '', category: 'Official Portals', badge: '', description: '', icon: '🌐', sort_order: 0 },
        copiedUrl: null,
        copyToClipboard(url) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url);
                this.copiedUrl = url;
                setTimeout(() => { if (this.copiedUrl === url) this.copiedUrl = null; }, 2000);
            }
        },
        openEditWebsiteModal(link) {
            this.editingWebsite = Object.assign({}, link);
            this.editWebsiteModalOpen = true;
        }
     }">
        <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-orange-950 text-white p-6 rounded-2xl border border-slate-800 shadow-md flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2">
                <span class="px-3 py-1 bg-orange-600/30 text-orange-400 border border-orange-500/30 rounded-full text-xs font-bold uppercase tracking-wider">
                    Official SBL Links & Directory
                </span>
                <h2 class="text-xl md:text-2xl font-bold tracking-tight">Official SBL Links & Web Directory</h2>
                <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Visit official SBL portals, dropshipping stores, investor platforms, and tools.
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button" @click="createWebsiteModalOpen = true" class="px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center gap-2 shadow-xs cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Add Link</span>
                </button>
                <a href="{{ route('ecosystem.index') }}" class="px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold rounded-xl transition-colors flex items-center gap-1.5 border border-slate-700">
                    <span>Manage All</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>

        @php
            $ecosystemLinks = \App\Models\EcosystemLink::where('is_active', true)->orderBy('sort_order')->get();
        @endphp

        <div id="toolkit-websites-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($ecosystemLinks as $el)
            <div data-link-id="{{ $el->id }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between hover:shadow-md hover:border-orange-200 transition-all group">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 group-hover:bg-orange-50 flex items-center justify-center text-xl flex-shrink-0">
                                {{ $el->icon ?: '🌐' }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm group-hover:text-orange-600 transition-colors">{{ $el->title }}</h4>
                                <span class="text-[10px] font-semibold text-slate-400">{{ $el->category }}</span>
                            </div>
                        </div>
                        @if($el->badge)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800">
                            {{ $el->badge }}
                        </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">{{ $el->description }}</p>

                    <div class="text-[11px] font-mono text-slate-400 truncate">
                        {{ $el->url }}
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="{{ $el->url }}" target="_blank" rel="noopener noreferrer" class="flex-1 px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                        <span>Visit Website</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>

                    <button type="button" @click="openEditWebsiteModal({{ json_encode($el) }})" class="p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-colors cursor-pointer" title="Edit Website">
                        ✏️
                    </button>

                    <form action="{{ route('ecosystem.destroy', $el) }}" method="POST" onsubmit="return confirm('Remove {{ addslashes($el->title) }}?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors cursor-pointer" title="Delete Website">
                            🗑️
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <!-- CREATE WEBSITE MODAL -->
        <div role="dialog" aria-modal="true" tabindex="-1" x-show="createWebsiteModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-transition
             x-cloak>
            <div @click.away="createWebsiteModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🌐</span>
                        <h3 class="text-base font-bold text-slate-900">Add Ecosystem Website / Portal</h3>
                    </div>
                    <button @click="createWebsiteModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
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
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                        <textarea name="description" rows="2" placeholder="Brief description of this platform or service..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="createWebsiteModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors cursor-pointer">Save Website</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT WEBSITE MODAL -->
        <div role="dialog" aria-modal="true" tabindex="-1" x-show="editWebsiteModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-transition
             x-cloak>
            <div @click.away="editWebsiteModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✏️</span>
                        <h3 class="text-base font-bold text-slate-900">Edit Website / Portal</h3>
                    </div>
                    <button @click="editWebsiteModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
                </div>

                <form :action="'{{ url('/ecosystem') }}/' + editingWebsite.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Platform Name *</label>
                            <input type="text" name="title" x-model="editingWebsite.title" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Icon</label>
                            <input type="text" name="icon" x-model="editingWebsite.icon" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none text-center text-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Website URL *</label>
                        <input type="url" name="url" x-model="editingWebsite.url" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                            <select name="category" x-model="editingWebsite.category" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                                <option value="Official Portals">Official Portals</option>
                                <option value="Business & Commerce">Business & Commerce</option>
                                <option value="Affiliate & Community">Affiliate & Community</option>
                                <option value="Support & Training">Support & Training</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                            <input type="text" name="badge" x-model="editingWebsite.badge" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                        <textarea name="description" x-model="editingWebsite.description" rows="2" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="editWebsiteModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors cursor-pointer">Update Website</button>
                    </div>
                </form>
            </div>
        </div>
</div>
@endsection
