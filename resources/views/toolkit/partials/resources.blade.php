{{-- Official SBL Marketing Resources & Document Library --}}
<div class="space-y-6">

    <!-- Hero Header Banner -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-orange-950 text-white p-6 rounded-2xl border border-slate-800 shadow-md flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-orange-600/30 text-orange-400 border border-orange-500/30 rounded-full text-xs font-bold uppercase tracking-wider">
                    Official Document Center
                </span>
                <span class="text-xs text-slate-400">Total: {{ $resources->count() }} Assets</span>
            </div>
            <h2 class="text-xl md:text-2xl font-bold tracking-tight">অফিসিয়াল লিফলেট ও রিসোর্স লাইব্রেরি</h2>
            <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                ডাউনলোড ও ক্লায়েন্ট কাউন্সেলিংয়ের জন্য SBL-এর সকল অফিসিয়াল লিফলেট, প্রেজেন্টেশন ডেক, লিগ্যাল লাইসেন্স এবং ব্র্যান্ড এসেট এক জায়গায় সাজানো রয়েছে।
            </p>
        </div>

        @if(auth()->user() && auth()->user()->isSuperAdmin())
        <div class="flex items-center gap-3 flex-shrink-0">
            <button type="button" 
                    @click="createResourceModalOpen = true" 
                    class="px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Add Resource</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Category Filters & Search Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <!-- Category Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs font-semibold">
            <button type="button" 
                    @click="resourceFilter = 'all'" 
                    :class="resourceFilter === 'all' ? 'bg-orange-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-3.5 py-1.5 rounded-xl transition-all whitespace-nowrap">
                সকল রিসোর্স (All)
            </button>
            @foreach($resourceCategories as $cat)
            <button type="button" 
                    @click="resourceFilter = '{{ $cat }}'" 
                    :class="resourceFilter === '{{ $cat }}' ? 'bg-orange-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-3.5 py-1.5 rounded-xl transition-all whitespace-nowrap">
                {{ $cat }}
            </button>
            @endforeach
        </div>

        <!-- Search Input -->
        <div class="relative sm:w-64">
            <input type="text" 
                   x-model="resourceSearch" 
                   placeholder="রিসোর্স খুঁজুন..." 
                   class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    <!-- Resources Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($resources as $res)
        <div x-show="(resourceFilter === 'all' || resourceFilter === '{{ $res->category }}') && (!resourceSearch || '{{ strtolower($res->title . ' ' . $res->description . ' ' . $res->badge) }}'.includes(resourceSearch.toLowerCase()))" 
             class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between hover:shadow-md hover:border-orange-200 transition-all group">
            
            <div class="space-y-3.5">
                <!-- Header with Icon & Badges -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 border border-orange-100 group-hover:bg-orange-600 group-hover:text-white flex items-center justify-center text-2xl transition-colors flex-shrink-0">
                            {{ $res->file_icon }}
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">
                                {{ $res->category }}
                            </span>
                            <h3 class="font-bold text-slate-900 text-sm group-hover:text-orange-600 transition-colors line-clamp-1">
                                {{ $res->title }}
                            </h3>
                        </div>
                    </div>

                    @if($res->badge)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800 flex-shrink-0">
                        {{ $res->badge }}
                    </span>
                    @endif
                </div>

                <!-- Description -->
                <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">
                    {{ $res->description }}
                </p>

                <!-- File Meta Details -->
                <div class="flex items-center gap-3 text-[11px] text-slate-400 font-medium pt-1">
                    <span class="uppercase font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                        {{ strtoupper($res->file_type) }}
                    </span>
                    @if($res->file_size)
                    <span>• {{ $res->file_size }}</span>
                    @endif
                    <span>• Official SBL Asset</span>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                <a href="{{ $res->file_url }}" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="flex-1 px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                    <span>ডকুমেন্ট দেখুন</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>

                <button type="button" 
                        @click="copyToClipboard('{{ url($res->file_url) }}')" 
                        class="p-2 text-slate-500 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-colors cursor-pointer" 
                        title="Copy Shareable Link">
                    <span x-show="copiedUrl !== '{{ url($res->file_url) }}'">📋</span>
                    <span x-show="copiedUrl === '{{ url($res->file_url) }}'" class="text-emerald-600 font-bold text-xs">✓</span>
                </button>

                @if(auth()->user() && auth()->user()->isSuperAdmin())
                <button type="button" 
                        @click="openEditResourceModal({{ json_encode($res) }})" 
                        class="p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-colors cursor-pointer" 
                        title="Edit Resource">
                    ✏️
                </button>

                <form action="{{ route('marketing-resources.destroy', $res) }}" 
                      method="POST" 
                      onsubmit="return confirm('Are you sure you want to remove {{ addslashes($res->title) }}?');" 
                      class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors cursor-pointer" 
                            title="Delete Resource">
                        🗑️
                    </button>
                </form>
                @endif
            </div>

        </div>
        @empty
        <div class="col-span-full py-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
            <span class="text-4xl block mb-2">📁</span>
            <p class="font-bold text-slate-700">কোনো রিসোর্স পাওয়া যায়নি</p>
            <p class="text-xs text-slate-500 mt-1">সুপার অ্যাডমিন নতুন ডকুমেন্টস ও লিফলেট আপলোড করতে পারেন।</p>
        </div>
        @endforelse
    </div>

    @if(auth()->user() && auth()->user()->isSuperAdmin())
    <!-- CREATE RESOURCE MODAL (Super Admin Only) -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="createResourceModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="createResourceModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📁</span>
                    <h3 class="text-base font-bold text-slate-900">Add New Official Resource</h3>
                </div>
                <button @click="createResourceModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('marketing-resources.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Resource Title *</label>
                    <input type="text" name="title" required placeholder="e.g. SBL Official Package Leaflet (Bangla)" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="Leaflets & Sheets">Leaflets & Sheets</option>
                            <option value="Presentations">Presentations</option>
                            <option value="Guides">Guides</option>
                            <option value="Legal & Certs">Legal & Certs</option>
                            <option value="Brand Assets">Brand Assets</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Type *</label>
                        <select name="file_type" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="pdf">PDF Document</option>
                            <option value="image">Image / High-Res</option>
                            <option value="doc">Word / Docs</option>
                            <option value="sheet">Excel / Sheet</option>
                            <option value="video">Video</option>
                            <option value="zip">Zip Archive</option>
                            <option value="link">External Link</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File URL or Path *</label>
                    <input type="text" name="file_url" required placeholder="images/sbl/sbl-office-leaflet.jpg or https://..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    <span class="text-[11px] text-slate-400 mt-0.5 block">Can be relative to public/ or an external secure URL.</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Size</label>
                        <input type="text" name="file_size" placeholder="e.g. 2.4 MB" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                        <input type="text" name="badge" placeholder="e.g. OFFICIAL LEAFLET" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief description of this resource..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createResourceModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors cursor-pointer">Save Resource</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT RESOURCE MODAL (Super Admin Only) -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="editResourceModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="editResourceModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">✏️</span>
                    <h3 class="text-base font-bold text-slate-900">Edit Official Resource</h3>
                </div>
                <button @click="editResourceModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <form :action="'{{ url('/marketing-resources') }}/' + editingResource.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Resource Title *</label>
                    <input type="text" name="title" x-model="editingResource.title" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="category" x-model="editingResource.category" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="Leaflets & Sheets">Leaflets & Sheets</option>
                            <option value="Presentations">Presentations</option>
                            <option value="Guides">Guides</option>
                            <option value="Legal & Certs">Legal & Certs</option>
                            <option value="Brand Assets">Brand Assets</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Type *</label>
                        <select name="file_type" x-model="editingResource.file_type" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="pdf">PDF Document</option>
                            <option value="image">Image / High-Res</option>
                            <option value="doc">Word / Docs</option>
                            <option value="sheet">Excel / Sheet</option>
                            <option value="video">Video</option>
                            <option value="zip">Zip Archive</option>
                            <option value="link">External Link</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File URL or Path *</label>
                    <input type="text" name="file_url" x-model="editingResource.file_url" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Size</label>
                        <input type="text" name="file_size" x-model="editingResource.file_size" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Tag</label>
                        <input type="text" name="badge" x-model="editingResource.badge" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" x-model="editingResource.description" rows="2" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editResourceModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors cursor-pointer">Update Resource</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
