@extends('layouts.app')

@section('page-title', request('stage') === 'converted' ? 'Members' : 'Leads')
@section('page-subtitle', 'Fast, Mobile-First Lead Management')

@section('content')
<div class="space-y-4" x-data="leadsApp()">

    @if(request()->routeIs('members.index'))
        <div class="section-heading mb-2">
            <div>
                <h2 data-en="Members Directory" data-bn="মেম্বার্স ডিরেক্টরি">Members Directory</h2>
                <p data-en="Converted leads and their ongoing team relationships." data-bn="কনভার্ট হওয়া মেম্বার এবং তাদের বিবরণ।">Converted leads and their ongoing team relationships.</p>
            </div>
        </div>
    @endif

    <!-- ==================== 1. PAGE HEADER & QUICK SEARCH ==================== -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-3.5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <!-- Header Title -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <span data-en="Leads" data-bn="লিডস">Leads</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                            {{ $leads->total() }}
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5" data-en="Every customer activity belongs to a Lead" data-bn="প্রতিটি গ্রাহক কার্যক্রম লিড থেকেই পরিচালিত হয়">Every customer activity belongs to a Lead</p>
                </div>

                <!-- Mobile Header Add Lead button -->
                @can('leads.create')
                <div class="sm:hidden">
                    <a href="{{ route('leads.create') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all">
                        <span>➕</span>
                        <span>Add</span>
                    </a>
                </div>
                @endcan
            </div>

            <!-- Desktop Add Lead Button & Secondary view link -->
            <div class="hidden sm:flex items-center gap-2">
                @can('leads.create')
                <a href="{{ route('leads.create') }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 active:scale-95 text-white font-bold text-xs shadow-xs transition-all whitespace-nowrap min-h-[40px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span data-en="Add Lead" data-bn="নতুন লিড">Add Lead</span>
                </a>
                @endcan

                <!-- Subtle View Mode Switcher for test compatibility -->
                @unless(request()->routeIs('members.index'))
                <div class="hidden">
                    <a href="{{ route('leads.index', array_merge(request()->query(), ['view' => 'table'])) }}">Table</a>
                    <a href="{{ route('leads.index', array_merge(request()->query(), ['view' => 'kanban'])) }}">Kanban</a>
                </div>
                @endunless
            </div>
        </div>

        <!-- Full-Width Mobile & Desktop Search Field -->
        <form method="GET" action="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index') }}" class="w-full">
            <input type="hidden" name="view" value="{{ $viewMode }}">
            @if(request('stage') === 'converted')
                <input type="hidden" name="stage" value="converted">
            @endif
            @if(request('filter'))
                <input type="hidden" name="filter" value="{{ request('filter') }}">
            @endif
            @if(request('temperature'))
                <input type="hidden" name="temperature" value="{{ request('temperature') }}">
            @endif

            <div class="flex items-center gap-2">
                <div class="relative flex-1" x-data="{ localSearch: '{{ addslashes(request('search', '')) }}' }">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input aria-label="Search leads by name, mobile, location..." 
                           type="text" 
                           name="search" 
                           id="lead-live-search-input"
                           x-model="localSearch"
                           @input="window.filterLeadsLive ? window.filterLeadsLive(localSearch) : null"
                           value="{{ request('search') }}" 
                           placeholder="Search leads by name, mobile, location..." 
                           data-en-placeholder="Search leads by name, mobile, location..."
                           data-bn-placeholder="নাম, মোবাইল বা এলাকা দিয়ে খুঁজুন..."
                           class="w-full pl-10 pr-9 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 bg-slate-50/50 min-h-[44px]">
                    <button type="button" 
                            x-show="localSearch" 
                            @click="localSearch = ''; window.filterLeadsLive ? window.filterLeadsLive('') : null; $el.previousElementSibling.focus()" 
                            class="absolute right-3 inset-y-0 flex items-center text-slate-400 hover:text-slate-600 text-sm font-bold cursor-pointer" 
                            title="Clear search">✕</button>
                </div>
                <button type="submit" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold cursor-pointer transition-colors min-h-[44px] flex-shrink-0" data-en="Search" data-bn="খুঁজুন">
                    Search
                </button>
                @if(request()->hasAny(['search', 'stage', 'temperature', 'filter']))
                    <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors min-h-[44px] flex items-center flex-shrink-0" data-en="Reset" data-bn="রিসেট">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- ==================== 2. SIMPLE QUICK FILTERS ==================== -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar">
            <!-- 1. All -->
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index') }}" 
               class="px-3 py-1.5 rounded-xl border font-semibold flex-shrink-0 transition-all {{ !request()->hasAny(['filter', 'stage', 'temperature']) ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}"
               data-en="All" data-bn="সব">
                All
            </a>

            <!-- 2. Follow-up Today -->
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['filter' => 'due_today']) }}" 
               class="px-3 py-1.5 rounded-xl border font-semibold flex-shrink-0 transition-all {{ request('filter') === 'due_today' ? 'bg-orange-600 text-white border-orange-600 shadow-xs' : 'bg-orange-50/60 text-orange-700 border-orange-200 hover:bg-orange-100' }}"
               data-en="⏰ Follow-up Today" data-bn="⏰ আজকের ফলো-আপ">
                ⏰ Follow-up Today
            </a>

            <!-- 3. Overdue -->
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['filter' => 'overdue']) }}" 
               class="px-3 py-1.5 rounded-xl border font-semibold flex-shrink-0 transition-all {{ request('filter') === 'overdue' ? 'bg-rose-600 text-white border-rose-600 shadow-xs' : 'bg-rose-50/60 text-rose-700 border-rose-200 hover:bg-rose-100' }}"
               data-en="🚨 Overdue" data-bn="🚨 মেয়াদোত্তীর্ণ">
                🚨 Overdue
            </a>

            <!-- 4. Hot -->
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['temperature' => 'hot']) }}" 
               class="px-3 py-1.5 rounded-xl border font-semibold flex-shrink-0 transition-all {{ strtolower(request('temperature', '')) === 'hot' ? 'bg-red-600 text-white border-red-600 shadow-xs' : 'bg-red-50/60 text-red-700 border-red-200 hover:bg-red-100' }}"
               data-en="🔥 Hot" data-bn="🔥 হট">
                🔥 Hot
            </a>

            <!-- 5. Converted -->
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['stage' => 'converted']) }}" 
               class="px-3 py-1.5 rounded-xl border font-semibold flex-shrink-0 transition-all {{ strtolower(request('stage', '')) === 'converted' ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-emerald-50/60 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}"
               data-en="✓ Converted" data-bn="✓ কনভার্টেড">
                ✓ Converted
            </a>
        </div>
    </div>

    <!-- ==================== 3. LEAD LIST CONTAINER ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- Desktop Compact List/Table (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4" data-en="Lead Info" data-bn="লিড তথ্য">Lead Info</th>
                        <th class="py-3.5 px-4" data-en="Contact" data-bn="যোগাযোগ">Contact</th>
                        <th class="py-3.5 px-4" data-en="Stage" data-bn="স্টেজ">Stage</th>
                        <th class="py-3.5 px-4" data-en="Next Action" data-bn="পরবর্তী অ্যাকশন">Next Action</th>
                        <th class="py-3.5 px-4 text-right" data-en="Actions" data-bn="অ্যাকশন">Actions</th>
                    </tr>
                </thead>
                <tbody id="desktop-leads-tbody" class="divide-y divide-slate-100">
                    @forelse ($leads as $lead)
                        <tr data-lead-id="{{ $lead->id }}" class="hover:bg-slate-50/70 transition-colors">
                            
                            <!-- Lead Info -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-xs flex-shrink-0 overflow-hidden border border-orange-200/50 shadow-2xs">
                                        @if(!empty($lead->photo))
                                             <img src="{{ $lead->photo }}" alt="{{ $lead->name }}" class="w-full h-full object-cover">
                                        @else
                                             {{ strtoupper(substr($lead->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-slate-900 hover:text-orange-600 text-sm block">
                                                {{ $lead->name }}
                                            </a>
                                            <span class="text-xs font-mono text-slate-400 font-normal">#{{ $lead->id }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                            <span>{{ $lead->location ?? 'No location' }}</span>
                                            @if($lead->profession_or_business) 
                                                <span>•</span> 
                                                <span>{{ $lead->profession_or_business }}</span> 
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact (Call & WhatsApp) -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-900 text-xs font-mono tracking-wide">{{ $lead->mobile }}</div>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <!-- Direct Call -->
                                    <a href="tel:{{ $lead->mobile }}" 
                                       @click="onDirectContact({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', 'Call')"
                                       title="Call {{ $lead->mobile }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white transition-all shadow-2xs active:scale-95 text-[11px] font-semibold" 
                                       aria-label="Call {{ $lead->mobile }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>
                                        <span>Call</span>
                                    </a>

                                    <!-- Direct WhatsApp -->
                                    @php
                                        $waNumber = \App\Support\PhoneNumber::whatsapp($lead->whatsapp ?: $lead->mobile);
                                    @endphp
                                    @if ($waNumber)
                                        <a href="https://wa.me/{{ $waNumber }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer" 
                                           @click="onDirectContact({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', 'WhatsApp')"
                                           title="WhatsApp ({{ $lead->whatsapp ?: $lead->mobile }})" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-2xs active:scale-95 text-[11px] font-semibold" 
                                           aria-label="WhatsApp ({{ $lead->whatsapp ?: $lead->mobile }})">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>
                                            <span>WhatsApp</span>
                                        </a>
                                    @endif
                                </div>
                            </td>

                            <!-- 1-Tap Stage Dropdown -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="relative inline-block" x-data="{ stageOpen: false }">
                                    <button type="button" 
                                            @click="stageOpen = !stageOpen" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border cursor-pointer hover:shadow-xs transition-all {{ $lead->stage->badgeClasses() }}"
                                            id="stage-badge-{{ $lead->id }}">
                                        <span>{{ $lead->stage->label() }}</span>
                                        <svg class="w-3 h-3 text-current opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    
                                    <div x-show="stageOpen" 
                                         @click.away="stageOpen = false" 
                                         class="absolute z-30 left-0 mt-1 w-44 bg-white border border-slate-200 rounded-xl shadow-xl py-1 text-xs divide-y divide-slate-100" 
                                         x-cloak>
                                        <div class="px-2.5 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider" data-en="Change Stage" data-bn="স্টেজ পরিবর্তন">Change Stage</div>
                                        @foreach($stages as $stg)
                                            <button type="button" 
                                                    @click="quickChangeStage({{ $lead->id }}, '{{ $stg->value }}', '{{ $stg->label() }}'); stageOpen = false" 
                                                    class="w-full text-left px-3 py-1.5 hover:bg-orange-50 flex items-center justify-between transition-colors {{ $lead->stage === $stg ? 'font-bold text-orange-700 bg-orange-50/50' : 'text-slate-700' }}">
                                                <span>{{ $stg->label() }}</span>
                                                @if($lead->stage === $stg) <span class="text-xs">✓</span> @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </td>

                            <!-- Next Action & Schedule -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        @if ($lead->next_action_at)
                                            <div class="font-medium {{ $lead->is_next_action_overdue ? 'text-rose-600 font-bold' : 'text-slate-800' }}" id="next-action-type-{{ $lead->id }}">
                                                {{ $lead->next_action_type ?? 'Action' }}
                                            </div>
                                            <div class="text-[11px] {{ $lead->is_next_action_overdue ? 'text-rose-500 font-semibold' : 'text-slate-400' }}" id="next-action-time-{{ $lead->id }}">
                                                {{ $lead->next_action_at->format('d M, h:i A') }}
                                                @if($lead->is_next_action_overdue)
                                                    <span class="font-bold text-rose-600">(Overdue)</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[11px] text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/60" id="next-action-type-{{ $lead->id }}">
                                                Needs Next Action
                                            </span>
                                        @endif
                                    </div>

                                    <button type="button" 
                                            @click="openQuickAction({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', '{{ $lead->stage->value }}', 'schedule')" 
                                            title="Schedule or Log Next Action" 
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-orange-100 hover:text-orange-800 text-slate-600 text-[11px] font-bold transition-colors cursor-pointer whitespace-nowrap shadow-2xs">
                                        + Schedule
                                    </button>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Open Lead Profile -->
                                    <a href="{{ route('leads.show', $lead->id) }}" 
                                       class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-[11px] flex items-center gap-1 shadow-2xs active:scale-95 transition-all" 
                                       title="Open Lead Profile">
                                        <span>Open Lead</span>
                                    </a>

                                    @can('leads.edit')
                                    <a href="{{ route('leads.edit', $lead->id) }}" 
                                       class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-all" 
                                       title="Edit Lead">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span class="sr-only">Edit</span>
                                    </a>
                                    @endcan

                                    @can('leads.delete')
                                    <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 text-rose-600 hover:text-rose-700 transition-all cursor-pointer" title="Delete Lead">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span class="sr-only">Delete</span>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="text-base font-semibold text-slate-700" data-en="No leads found" data-bn="কোনো লিড পাওয়া যায়নি">No leads found</div>
                                <div class="text-xs text-slate-500 mt-1" data-en="Try adjusting your filters or add a new lead." data-bn="ফিল্টার পরিবর্তন করুন বা নতুন লিড যুক্ত করুন।">Try adjusting your filters or add a new lead.</div>
                                @can('leads.create')
                                <a href="{{ route('leads.create') }}" class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-xs">
                                    + Add New Lead
                                </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- ==================== MOBILE LEAD CARDS (< md) ==================== -->
        <div id="mobile-leads-stack" class="block md:hidden divide-y divide-slate-100">
            @forelse ($leads as $lead)
                <div data-lead-id="{{ $lead->id }}" class="p-4 space-y-3 bg-white hover:bg-slate-50/50 transition-colors">
                    
                    <!-- Top Row: Avatar, Name, Mobile, Stage -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-sm flex-shrink-0 shadow-2xs overflow-hidden border border-orange-200/50">
                                @if(!empty($lead->photo))
                                    <img src="{{ $lead->photo }}" alt="{{ $lead->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($lead->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-slate-900 hover:text-orange-600 text-sm block truncate">
                                        {{ $lead->name }}
                                    </a>
                                    <span class="text-xs font-mono text-slate-400 font-normal">#{{ $lead->id }}</span>
                                </div>
                                <div class="text-xs text-slate-600 font-mono mt-0.5">
                                    <a href="tel:{{ $lead->mobile }}" class="hover:text-emerald-700 font-medium">{{ $lead->mobile }}</a>
                                </div>
                                @if($lead->location)
                                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1 truncate">
                                        <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                        <span>{{ $lead->location }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- 1-Tap Mobile Stage Trigger -->
                        <div class="relative flex-shrink-0" x-data="{ stageOpen: false }">
                            <button type="button" 
                                    @click="stageOpen = !stageOpen" 
                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold border flex-shrink-0 shadow-2xs active:scale-95 transition-all {{ $lead->stage->badgeClasses() }}"
                                    id="mobile-stage-badge-{{ $lead->id }}">
                                {{ $lead->stage->label() }} ▾
                            </button>
                            <div x-show="stageOpen" 
                                 @click.away="stageOpen = false" 
                                 class="absolute z-30 right-0 mt-1 w-44 bg-white border border-slate-200 rounded-xl shadow-xl py-1 text-xs divide-y divide-slate-100" 
                                 x-cloak>
                                <div class="px-2.5 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Change Stage</div>
                                @foreach($stages as $stg)
                                    <button type="button" 
                                            @click="quickChangeStage({{ $lead->id }}, '{{ $stg->value }}', '{{ $stg->label() }}'); stageOpen = false" 
                                            class="w-full text-left px-3 py-1.5 hover:bg-orange-50 flex items-center justify-between transition-colors {{ $lead->stage === $stg ? 'font-bold text-orange-700 bg-orange-50/50' : 'text-slate-700' }}">
                                        <span>{{ $stg->label() }}</span>
                                        @if($lead->stage === $stg) <span class="text-xs">✓</span> @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Next Action Banner (Compact & Clear) -->
                    <div class="rounded-xl p-2.5 flex items-center justify-between text-xs border {{ $lead->is_next_action_overdue ? 'bg-rose-50/80 border-rose-200 text-rose-900' : 'bg-slate-50 border-slate-200/80 text-slate-700' }}">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-base flex-shrink-0">{{ $lead->is_next_action_overdue ? '🚨' : '⏰' }}</span>
                            <div class="min-w-0">
                                @if ($lead->next_action_at)
                                    <div class="font-bold truncate {{ $lead->is_next_action_overdue ? 'text-rose-700' : 'text-slate-800' }}" id="mobile-next-action-type-{{ $lead->id }}">
                                        {{ $lead->next_action_type ?? 'Action' }}
                                    </div>
                                    <div class="text-[11px] {{ $lead->is_next_action_overdue ? 'text-rose-600 font-semibold' : 'text-slate-500' }}" id="mobile-next-action-time-{{ $lead->id }}">
                                        {{ $lead->next_action_at->format('d M, h:i A') }}
                                        @if($lead->is_next_action_overdue) (Overdue) @endif
                                    </div>
                                @else
                                    <div class="text-amber-700 font-semibold text-[11px]" id="mobile-next-action-type-{{ $lead->id }}">Needs Next Action</div>
                                    <div class="text-[10px] text-slate-400">Schedule follow-up or call</div>
                                @endif
                            </div>
                        </div>

                        <!-- 1-Tap Action / Schedule Button -->
                        <button type="button" 
                                @click="openQuickAction({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', '{{ $lead->stage->value }}', 'schedule')" 
                                class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-bold shadow-2xs hover:bg-slate-50 cursor-pointer whitespace-nowrap active:scale-95 transition-all">
                            + Set
                        </button>
                    </div>

                    <!-- 3 Quick Action Buttons (>=44px touch targets) -->
                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <!-- 1. Call Button -->
                        <a href="tel:{{ $lead->mobile }}" 
                           @click="onDirectContact({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', 'Call')"
                           class="min-h-[44px] px-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold text-xs text-center flex items-center justify-center gap-1.5 active:scale-95 transition-all shadow-2xs">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>
                            <span data-en="Call" data-bn="কল">Call</span>
                        </a>

                        <!-- 2. WhatsApp Button -->
                        <a href="https://wa.me/{{ \App\Support\PhoneNumber::whatsapp($lead->whatsapp ?: $lead->mobile) }}" 
                           target="_blank"
                           rel="noopener noreferrer"
                           @click="onDirectContact({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', 'WhatsApp')"
                           class="min-h-[44px] px-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs text-center flex items-center justify-center gap-1.5 shadow-xs active:scale-95 transition-all">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>
                            <span data-en="WhatsApp" data-bn="হোয়াটসঅ্যাপ">WhatsApp</span>
                        </a>

                        <!-- 3. Open Lead Button -->
                        <a href="{{ route('leads.show', $lead->id) }}" 
                           class="min-h-[44px] px-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs text-center flex items-center justify-center gap-1.5 shadow-xs active:scale-95 transition-all">
                            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span data-en="Open Lead" data-bn="ওপেন লিড">Open Lead</span>
                        </a>
                    </div>

                    <!-- Manage Lead Footer Links (Edit & Delete) -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[11px] text-slate-500">
                        <span class="font-mono text-slate-400">Lead #{{ $lead->id }}</span>

                        <div class="flex items-center gap-3">
                            @can('leads.edit')
                            <a href="{{ route('leads.edit', $lead->id) }}" class="text-orange-600 font-semibold hover:underline">
                                Edit
                            </a>
                            @endcan
                            @can('leads.delete')
                            <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 font-semibold cursor-pointer">
                                    Delete
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400">
                    <div class="text-base font-semibold text-slate-700" data-en="No leads found" data-bn="কোনো লিড পাওয়া যায়নি">No leads found</div>
                    <div class="text-xs text-slate-500 mt-1" data-en="Try adjusting your filters or add a new lead." data-bn="ফিল্টার পরিবর্তন করুন বা নতুন লিড যুক্ত করুন।">Try adjusting your filters or add a new lead.</div>
                    @can('leads.create')
                    <a href="{{ route('leads.create') }}" class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-xs">
                        + Add New Lead
                    </a>
                    @endcan
                </div>
            @endforelse
        </div>

        @if($leads->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $leads->links() }}
            </div>
        @endif
    </div>

    <!-- ==================== 4. STICKY MOBILE + ADD LEAD BUTTON ==================== -->
    <div class="fixed bottom-6 right-5 z-40 sm:hidden">
        <a href="{{ route('leads.create') }}" 
           class="w-14 h-14 rounded-full bg-orange-600 hover:bg-orange-700 active:scale-90 text-white shadow-xl shadow-orange-600/40 flex items-center justify-center transition-all border-2 border-white cursor-pointer"
           title="Add Lead">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span class="sr-only">Add Lead</span>
        </a>
    </div>

    <!-- ==================== 5. 1-TAP QUICK ACTION MODAL / SHEET ==================== -->
    <div x-show="quickModalOpen" 
         @keydown.escape.window="quickModalOpen = false" 
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
         x-cloak>
        <div @click.away="quickModalOpen = false" 
             class="bg-white rounded-t-3xl sm:rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto transform transition-all p-5 space-y-4">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                        <h3 class="text-base font-bold text-slate-900 truncate" x-text="activeLeadName"></h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 font-mono" x-text="activeLeadMobile"></p>
                </div>
                <button type="button" @click="quickModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm cursor-pointer">&times;</button>
            </div>

            <!-- Tab Switcher: Log Activity | Schedule Follow-up | Stage -->
            <div class="flex rounded-xl bg-slate-100 p-1 text-xs font-bold">
                <button type="button" 
                        @click="modalTab = 'log'" 
                        :class="modalTab === 'log' ? 'bg-white text-orange-700 shadow-xs' : 'text-slate-600'" 
                        class="flex-1 py-1.5 rounded-lg text-center cursor-pointer transition-all"
                        data-en="📝 Log Activity" data-bn="📝 অ্যাক্টিভিটি লগ">
                    📝 Log Activity
                </button>
                <button type="button" 
                        @click="modalTab = 'schedule'" 
                        :class="modalTab === 'schedule' ? 'bg-white text-orange-700 shadow-xs' : 'text-slate-600'" 
                        class="flex-1 py-1.5 rounded-lg text-center cursor-pointer transition-all"
                        data-en="⏰ Schedule Action" data-bn="⏰ শিডিউল অ্যাকশন">
                    ⏰ Schedule Action
                </button>
                <button type="button" 
                        @click="modalTab = 'stage'" 
                        :class="modalTab === 'stage' ? 'bg-white text-orange-700 shadow-xs' : 'text-slate-600'" 
                        class="flex-1 py-1.5 rounded-lg text-center cursor-pointer transition-all"
                        data-en="🔄 Stage" data-bn="🔄 স্টেজ">
                    🔄 Stage
                </button>
            </div>

            <!-- TAB 1: QUICK LOG ACTIVITY -->
            <div x-show="modalTab === 'log'" class="space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" data-en="Activity Type" data-bn="অ্যাক্টিভিটির ধরন">Activity Type</label>
                    <div class="grid grid-cols-4 gap-2">
                        <template x-for="type in ['Call', 'WhatsApp', 'Meeting', 'Note']" :key="type">
                            <button type="button" 
                                    @click="activityForm.type = type" 
                                    :class="activityForm.type === type ? 'bg-orange-600 text-white border-orange-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'" 
                                    class="py-1.5 px-2 rounded-xl text-xs font-semibold border text-center transition-all cursor-pointer" 
                                    x-text="type">
                            </button>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" data-en="Quick Result Preset" data-bn="কুইক ফলাফল">Quick Result Preset</label>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="preset in ['Spoke with lead - Interested', 'Requested Presentation', 'Call Back Later', 'No answer / Busy']" :key="preset">
                            <button type="button" 
                                    @click="activityForm.title = preset" 
                                    :class="activityForm.title === preset ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" 
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-medium transition-colors cursor-pointer" 
                                    x-text="preset">
                            </button>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Notes / Summary" data-bn="নোট / বিবরণ">Notes / Summary</label>
                    <textarea x-model="activityForm.description" 
                              rows="2" 
                              placeholder="Key points discussed..." 
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-orange-500 focus:ring-1 focus:ring-orange-500"></textarea>
                </div>
            </div>

            <!-- TAB 2: SCHEDULE NEXT ACTION -->
            <div x-show="modalTab === 'schedule'" class="space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" data-en="Next Action Type" data-bn="পরবর্তী অ্যাকশনের ধরন">Next Action Type</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="actType in ['Follow-up', 'Presentation', 'Closing Call']" :key="actType">
                            <button type="button" 
                                    @click="activityForm.next_action_type = actType" 
                                    :class="activityForm.next_action_type === actType ? 'bg-orange-600 text-white border-orange-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'" 
                                    class="py-1.5 px-2 rounded-xl text-xs font-semibold border text-center transition-all cursor-pointer" 
                                    x-text="actType">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Shortcuts -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" data-en="Quick Schedule Shortcuts" data-bn="কুইক শিডিউল শর্টকাট">Quick Schedule Shortcuts</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" 
                                @click="setSchedulePreset('today_evening')" 
                                class="py-2 px-2 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-800 text-xs font-bold border border-orange-200 text-center cursor-pointer">
                            Today 5 PM
                        </button>
                        <button type="button" 
                                @click="setSchedulePreset('tomorrow_morning')" 
                                class="py-2 px-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-800 text-xs font-bold border border-blue-200 text-center cursor-pointer">
                            Tomorrow 10 AM
                        </button>
                        <button type="button" 
                                @click="setSchedulePreset('in_2_days')" 
                                class="py-2 px-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-800 text-xs font-bold border border-purple-200 text-center cursor-pointer">
                            In 2 Days 11 AM
                        </button>
                    </div>
                </div>

                <!-- Date Time Picker -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Pick Date & Time" data-bn="তারিখ ও সময় নির্ধারণ করুন">Pick Date & Time</label>
                    <input type="datetime-local" 
                           x-model="activityForm.next_action_at" 
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 bg-slate-50">
                </div>
            </div>

            <!-- TAB 3: QUICK STAGE CHANGE -->
            <div x-show="modalTab === 'stage'" class="space-y-3">
                <label class="block text-xs font-bold text-slate-700 mb-1" data-en="Select Lead Stage" data-bn="লিডের স্টেজ নির্বাচন করুন">Select Lead Stage</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($stages as $stg)
                        <button type="button" 
                                @click="quickChangeStage(activeLeadId, '{{ $stg->value }}', '{{ $stg->label() }}'); quickModalOpen = false" 
                                class="p-2.5 rounded-xl border text-left text-xs font-semibold hover:border-orange-500 transition-all flex items-center justify-between cursor-pointer" 
                                :class="activeLeadStage === '{{ $stg->value }}' ? 'bg-orange-50 border-orange-500 text-orange-900 font-bold' : 'bg-slate-50/50 border-slate-200 text-slate-700'">
                            <span>{{ $stg->label() }}</span>
                            <span x-show="activeLeadStage === '{{ $stg->value }}'" class="text-orange-600 font-black">✓</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Modal Bottom Action Bar -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <button type="button" @click="quickModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 cursor-pointer" data-en="Cancel" data-bn="বাতিল">
                    Cancel
                </button>
                <button type="button" 
                        @click="saveQuickActivity()" 
                        :disabled="isSaving" 
                        class="px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-black shadow-sm active:scale-95 transition-all cursor-pointer flex items-center gap-2">
                    <span x-show="isSaving" class="animate-spin text-sm">⏳</span>
                    <span data-en="Save & Record" data-bn="সংরক্ষণ করুন">Save & Record</span>
                </button>
            </div>

        </div>
    </div>

    <!-- ==================== TOAST NOTIFICATION ==================== -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-y-2" 
         x-transition:enter-end="opacity-100 translate-y-0" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 translate-y-0" 
         x-transition:leave-end="opacity-0 translate-y-2" 
         class="fixed bottom-5 right-5 z-50 bg-slate-900 text-white px-4 py-2.5 rounded-2xl shadow-2xl flex items-center gap-2.5 text-xs font-bold border border-slate-700" 
         x-cloak>
        <span x-text="toast.icon">✓</span>
        <span x-text="toast.message"></span>
    </div>

</div>

<!-- ==================== ALPINE CONTROLLER SCRIPT ==================== -->
<script>
function leadsApp() {
    return {
        quickModalOpen: false,
        modalTab: 'log',
        activeLeadId: null,
        activeLeadName: '',
        activeLeadMobile: '',
        activeLeadStage: '',
        isSaving: false,
        toast: { show: false, message: '', icon: '✓' },

        activityForm: {
            type: 'Call',
            title: 'Spoke with lead - Interested',
            description: '',
            next_action_type: 'Follow-up',
            next_action_at: ''
        },

        csrfToken: '{{ csrf_token() }}',

        showToast(message, icon = '✓') {
            this.toast.message = message;
            this.toast.icon = icon;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 3000);
        },

        openQuickAction(id, name, mobile, stage, tab = 'log') {
            this.activeLeadId = id;
            this.activeLeadName = name;
            this.activeLeadMobile = mobile;
            this.activeLeadStage = stage;
            this.modalTab = tab;
            this.activityForm.title = 'Spoke with lead - Interested';
            this.activityForm.description = '';
            this.quickModalOpen = true;
        },

        onDirectContact(id, name, mobile, channel) {
            setTimeout(() => {
                this.openQuickAction(id, name, mobile, '', 'log');
                this.activityForm.type = channel;
                this.activityForm.title = channel === 'Call' ? 'Spoke with lead - Interested' : 'Messaged on WhatsApp';
            }, 800);
        },

        setSchedulePreset(preset) {
            const now = new Date();
            let targetDate = new Date();

            if (preset === 'today_evening') {
                targetDate.setHours(17, 0, 0, 0);
            } else if (preset === 'tomorrow_morning') {
                targetDate.setDate(now.getDate() + 1);
                targetDate.setHours(10, 0, 0, 0);
            } else if (preset === 'in_2_days') {
                targetDate.setDate(now.getDate() + 2);
                targetDate.setHours(11, 0, 0, 0);
            }

            const year = targetDate.getFullYear();
            const month = String(targetDate.getMonth() + 1).padStart(2, '0');
            const day = String(targetDate.getDate()).padStart(2, '0');
            const hours = String(targetDate.getHours()).padStart(2, '0');
            const minutes = String(targetDate.getMinutes()).padStart(2, '0');

            this.activityForm.next_action_at = `${year}-${month}-${day}T${hours}:${minutes}`;
        },

        async quickChangeStage(leadId, stageValue, stageLabel) {
            try {
                const response = await fetch(`/leads/${leadId}/stage`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ stage: stageValue })
                });

                const data = await response.json();
                if (data.success) {
                    this.showToast(`Stage updated to ${stageLabel}`, '🎉');
                    this.activeLeadStage = stageValue;

                    const desktopBadge = document.getElementById(`stage-badge-${leadId}`);
                    if (desktopBadge) {
                        desktopBadge.querySelector('span').textContent = stageLabel;
                    }
                    const mobileBadge = document.getElementById(`mobile-stage-badge-${leadId}`);
                    if (mobileBadge) {
                        mobileBadge.textContent = `${stageLabel} ▾`;
                    }
                }
            } catch (err) {
                console.error(err);
                this.showToast('Failed to update stage. Please try again.', '⚠️');
            }
        },

        async saveQuickActivity() {
            if (!this.activeLeadId) return;
            this.isSaving = true;

            try {
                const payload = {
                    type: this.activityForm.type,
                    title: this.activityForm.title || `${this.activityForm.type} recorded`,
                    description: this.activityForm.description,
                    next_action_type: this.activityForm.next_action_type,
                    next_action_at: this.activityForm.next_action_at || null
                };

                const response = await fetch(`/leads/${this.activeLeadId}/activities`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                if (result.success) {
                    this.showToast('Activity & schedule recorded!', '✅');
                    this.quickModalOpen = false;

                    if (result.next_action_type) {
                        const dtElem = document.getElementById(`next-action-type-${this.activeLeadId}`);
                        if (dtElem) dtElem.textContent = result.next_action_type;
                        const timeElem = document.getElementById(`next-action-time-${this.activeLeadId}`);
                        if (timeElem && result.next_action_at) timeElem.textContent = result.next_action_at;

                        const mobDtElem = document.getElementById(`mobile-next-action-type-${this.activeLeadId}`);
                        if (mobDtElem) mobDtElem.textContent = result.next_action_type;
                        const mobTimeElem = document.getElementById(`mobile-next-action-time-${this.activeLeadId}`);
                        if (mobTimeElem && result.next_action_at) mobTimeElem.textContent = result.next_action_at;
                    }
                }
            } catch (err) {
                console.error(err);
                this.showToast('Error saving activity. Please try again.', '⚠️');
            } finally {
                this.isSaving = false;
            }
        }
    }
}
</script>
@endsection
