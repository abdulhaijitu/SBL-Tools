@extends('layouts.app')

@section('page-title', request('stage') === 'converted' ? 'Members' : 'Leads')
@section('page-subtitle', 'Manage Inbound & Outbound Pipeline')

@section('content')
<div class="space-y-4" x-data="leadsApp()">

    @if(request()->routeIs('members.index'))
        <div class="section-heading">
            <div>
                <h2 data-en="Members Directory" data-bn="মেম্বার্স ডিরেক্টরি">Members Directory</h2>
                <p data-en="Converted leads and their ongoing team relationships." data-bn="কনভার্ট হওয়া মেম্বার এবং তাদের বিবরণ।">Converted leads and their ongoing team relationships.</p>
            </div>
        </div>
    @endif

    <!-- ==================== 1. TOP ACTION & FILTER BAR ==================== -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search -->
            <form method="GET" action="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index') }}" class="flex-1 flex items-center gap-2">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                @if(request('stage') === 'converted')
                    <input type="hidden" name="stage" value="converted">
                @endif
                <div class="relative flex-1" x-data="{ localSearch: '{{ addslashes(request('search', '')) }}' }">
                    <input aria-label="Search leads by name, mobile, whatsapp, location..." type="text" 
                           name="search" 
                           id="lead-live-search-input"
                           x-model="localSearch"
                           @input="window.filterLeadsLive ? window.filterLeadsLive(localSearch) : null"
                           value="{{ request('search') }}" 
                           placeholder="Search leads by name, mobile, whatsapp, location... (live)" 
                           data-en-placeholder="Search leads by name, mobile, whatsapp, location... (live)"
                           data-bn-placeholder="নাম, মোবাইল, হোয়াটসঅ্যাপ বা এলাকা দিয়ে খুঁজুন (লাইভ)..."
                           class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 bg-slate-50/50">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <button type="button" x-show="localSearch" @click="localSearch = ''; window.filterLeadsLive(''); $el.previousElementSibling.previousElementSibling.focus()" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 p-0.5 rounded-full text-xs font-bold cursor-pointer" title="Clear">✕</button>
                </div>
                <span id="leads-live-counter" class="hidden px-2.5 py-1.5 text-xs font-semibold bg-orange-100 text-orange-800 rounded-xl whitespace-nowrap"></span>
                <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold cursor-pointer transition-colors" data-en="Search" data-bn="খুঁজুন">
                    Search
                </button>
                @if(request()->hasAny(['search', 'stage', 'temperature', 'source_id', 'filter']))
                    <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['view' => $viewMode]) }}" class="px-2.5 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition-colors" data-en="Clear" data-bn="মুছুন">
                        Clear
                    </a>
                @endif
            </form>

            <!-- View Switcher & Add Lead Button -->
            <div class="flex items-center gap-2 self-end sm:self-auto">
                @unless(request()->routeIs('members.index'))
                <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 text-xs font-semibold">
                    <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', array_merge(request()->query(), ['view' => 'table'])) }}" 
                       class="px-3 py-1.5 rounded-lg transition-all {{ $viewMode === 'table' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                       data-en="📋 Table" data-bn="📋 টেবিল">
                        📋 Table
                    </a>
                    <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', array_merge(request()->query(), ['view' => 'kanban'])) }}" 
                       class="px-3 py-1.5 rounded-lg transition-all {{ $viewMode === 'kanban' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                       data-en="📊 Kanban" data-bn="📊 কানবান">
                        📊 Kanban
                    </a>
                </div>
                @endunless

                @can('leads.create')
                <a href="{{ route('leads.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all">
                    <span>➕</span>
                    <span data-en="Add Lead" data-bn="নতুন লিড">Add Lead</span>
                </a>
                @endcan
            </div>
        </div>

        <!-- Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar">
            <span class="text-slate-400 font-semibold text-[11px] uppercase tracking-wider flex-shrink-0" data-en="Filters:" data-bn="ফিল্টার:">Filters:</span>
            
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['view' => $viewMode]) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ !request()->hasAny(['filter', 'stage', 'temperature']) ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}"
               data-en="All Leads" data-bn="সকল লিড">
                All Leads
            </a>
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['view' => $viewMode, 'filter' => 'overdue']) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ request('filter') === 'overdue' ? 'bg-rose-600 text-white border-rose-600' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }}"
               data-en="🚨 Overdue Follow-ups" data-bn="🚨 জরুরি ফলো-আপ">
                🚨 Overdue Follow-ups
            </a>
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['view' => $viewMode, 'filter' => 'due_today']) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ request('filter') === 'due_today' ? 'bg-orange-600 text-white border-orange-600' : 'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100' }}"
               data-en="⏰ Due Today" data-bn="⏰ আজকের শিডিউল">
                ⏰ Due Today
            </a>
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['view' => $viewMode, 'filter' => 'needs_action']) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ request('filter') === 'needs_action' ? 'bg-amber-600 text-white border-amber-600' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' }}"
               data-en="⚠️ Needs Next Action" data-bn="⚠️ অ্যাকশন প্রয়োজন">
                ⚠️ Needs Next Action
            </a>
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['view' => $viewMode, 'temperature' => 'hot']) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ request('temperature') === 'hot' ? 'bg-red-600 text-white border-red-600' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' }}"
               data-en="🔥 Hot Leads" data-bn="🔥 হট লিডস">
                🔥 Hot Leads
            </a>
        </div>
    </div>

    <!-- ==================== 2. LEADS LIST VIEW ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- Desktop Table (Visible >= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4" data-en="Lead Info" data-bn="লিড তথ্য">Lead Info</th>
                        <th class="py-3.5 px-4" data-en="Contact" data-bn="যোগাযোগ">Contact</th>
                        <th class="py-3.5 px-4" data-en="Stage (1-Tap Change)" data-bn="স্টেজ (১-ট্যাপ পরিবর্তন)">Stage (1-Tap Change)</th>
                        <th class="py-3.5 px-4" data-en="Next Action" data-bn="পরবর্তী অ্যাকশন">Next Action</th>
                        <th class="py-3.5 px-4 text-right" data-en="Actions" data-bn="অ্যাকশন">Actions</th>
                    </tr>
                </thead>
                <tbody id="desktop-leads-tbody" class="divide-y divide-slate-100">
                    @forelse ($leads as $lead)
                        <tr data-lead-id="{{ $lead->id }}" class="hover:bg-slate-50/70 transition-colors">
                            
                            <!-- 1. Lead Info -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-xs flex-shrink-0 overflow-hidden border border-orange-200/50 shadow-2xs">
                                        @if(!empty($lead->photo))
                                             <img src="{{ $lead->photo }}" alt="{{ $lead->name }}" class="w-full h-full object-cover">
                                        @else
                                             {{ substr($lead->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-slate-900 hover:text-orange-600 text-sm block">
                                                {{ $lead->name }}
                                            </a>
                                            <span class="text-xs font-mono text-slate-400 font-normal">#{{ $lead->id }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                                            <span>{{ $lead->location ?? 'No location' }}</span>
                                            @if($lead->profession_or_business) 
                                                <span>•</span> 
                                                <span>{{ $lead->profession_or_business }}</span> 
                                            @endif
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold border {{ $lead->temperature->badgeClasses() }}">
                                                {{ $lead->temperature->label() }} ({{ $lead->score }})
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 2. Contact (Call & WhatsApp 1-tap) -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-900 text-xs font-mono tracking-wide">{{ $lead->mobile }}</div>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <!-- Direct Call -->
                                    <a href="tel:{{ $lead->mobile }}" 
                                       @click="onDirectContact({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', 'Call')"
                                       title="Call {{ $lead->mobile }}" 
                                       class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white transition-all shadow-2xs active:scale-95 text-[11px] font-semibold" 
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
                                           class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-2xs active:scale-95 text-[11px] font-semibold" 
                                           aria-label="WhatsApp ({{ $lead->whatsapp ?: $lead->mobile }})">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>
                                            <span>WhatsApp</span>
                                        </a>
                                    @endif
                                </div>
                            </td>

                            <!-- 3. Stage: 1-Tap Quick Stage Dropdown -->
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

                            <!-- 4. Next Action & Quick Schedule -->
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

                                    <!-- Quick Schedule Button -->
                                    <button type="button" 
                                            @click="openQuickAction({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', '{{ $lead->stage->value }}')" 
                                            title="Schedule or Log Next Action" 
                                            class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-orange-100 hover:text-orange-800 text-slate-600 text-[11px] font-bold transition-colors cursor-pointer whitespace-nowrap shadow-2xs">
                                        + Schedule
                                    </button>
                                </div>
                            </td>

                            <!-- 5. Actions -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Quick Action Sheet Trigger -->
                                    <button type="button" 
                                            @click="openQuickAction({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', '{{ $lead->stage->value }}')"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center bg-orange-50 hover:bg-orange-600 text-orange-700 hover:text-white transition-all active:scale-95 shadow-2xs border border-orange-200/60 cursor-pointer" 
                                            title="Quick Actions (Log, Follow-up, Convert)">
                                        ⚡
                                    </button>

                                    <!-- View Profile -->
                                    <a href="{{ route('leads.show', $lead->id) }}" 
                                       class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition-all active:scale-95 shadow-xs border border-slate-200/60" 
                                       title="View Lead Profile"
                                       aria-label="View Lead Profile">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    @can('leads.edit')
                                    <a href="{{ route('leads.edit', $lead->id) }}" 
                                       class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition-all active:scale-95 shadow-xs border border-slate-200/60" 
                                       title="Edit Lead"
                                       aria-label="Edit Lead">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
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

        <!-- ==================== MOBILE CARD STACK (< md) ==================== -->
        <div id="mobile-leads-stack" class="block md:hidden divide-y divide-slate-100">
            @forelse ($leads as $lead)
                <div data-lead-id="{{ $lead->id }}" class="p-4 space-y-3 hover:bg-slate-50/50 transition-colors">
                    
                    <!-- Top Row: Avatar, Name & Stage Badge -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-sm flex-shrink-0 shadow-2xs overflow-hidden border border-orange-200/50">
                                @if(!empty($lead->photo))
                                    <img src="{{ $lead->photo }}" alt="{{ $lead->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ substr($lead->name, 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-slate-900 hover:text-orange-600 text-sm block">
                                        {{ $lead->name }}
                                    </a>
                                    <span class="text-xs font-mono text-slate-400 font-normal">#{{ $lead->id }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $lead->location ?? 'No location' }} 
                                    @if($lead->profession_or_business) • {{ $lead->profession_or_business }} @endif
                                </div>
                            </div>
                        </div>

                        <!-- 1-Tap Mobile Stage Trigger -->
                        <button type="button" 
                                @click="openQuickAction({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', '{{ $lead->stage->value }}', 'stage')"
                                class="px-2.5 py-1 rounded-full text-[10px] font-bold border flex-shrink-0 shadow-2xs active:scale-95 transition-all {{ $lead->stage->badgeClasses() }}"
                                id="mobile-stage-badge-{{ $lead->id }}">
                            {{ $lead->stage->label() }} ▾
                        </button>
                    </div>

                    <!-- Tags & Temperature Row -->
                    <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                        <span class="px-2 py-0.5 rounded-full font-semibold border {{ $lead->temperature->badgeClasses() }}">
                            {{ $lead->temperature->label() }} ({{ $lead->score }} pts)
                        </span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium">
                            {{ $lead->source->name ?? 'Direct' }}
                        </span>
                        @if($lead->lead_tag)
                            <span class="px-1.5 py-0.5 rounded bg-orange-100 text-orange-800 font-bold">
                                {{ $lead->lead_tag }}
                            </span>
                        @endif
                    </div>

                    <!-- Next Action Banner (Clear & Compact) -->
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
                                    <div class="text-[10px] text-slate-400">Schedule a call or follow-up</div>
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

                    <!-- Thumb-Friendly 3 Primary Action Buttons -->
                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <!-- 1. Call Button -->
                        <a href="tel:{{ $lead->mobile }}" 
                           @click="onDirectContact({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', 'Call')"
                           class="min-h-[44px] px-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold text-xs text-center flex items-center justify-center gap-1.5 active:scale-95 transition-all shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>
                            <span data-en="Call" data-bn="কল">Call</span>
                        </a>

                        <!-- 2. WhatsApp Button -->
                        <a href="https://wa.me/{{ \App\Support\PhoneNumber::whatsapp($lead->whatsapp ?: $lead->mobile) }}" 
                           target="_blank"
                           @click="onDirectContact({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', 'WhatsApp')"
                           class="min-h-[44px] px-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs text-center flex items-center justify-center gap-1.5 shadow-xs active:scale-95 transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>
                            <span data-en="WhatsApp" data-bn="হোয়াটসঅ্যাপ">WhatsApp</span>
                        </a>

                        <!-- 3. Quick Action Sheet Trigger -->
                        <button type="button" 
                                @click="openQuickAction({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', '{{ $lead->stage->value }}')" 
                                class="min-h-[44px] px-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs text-center flex items-center justify-center gap-1.5 shadow-xs active:scale-95 transition-all cursor-pointer">
                            <span>⚡</span>
                            <span data-en="Action" data-bn="অ্যাকশন">Action</span>
                        </button>
                    </div>

                    <!-- Manage Lead Footer Links -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                        <a href="{{ route('leads.show', $lead->id) }}" class="text-slate-600 hover:text-slate-900 font-semibold flex items-center gap-1">
                            <span data-en="Full Profile" data-bn="পূর্ণ বিবরণ">Full Profile</span> →
                        </a>

                        <div class="flex items-center gap-2">
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

    <!-- ==================== 3. 1-TAP QUICK ACTION DRAWER / MODAL ==================== -->
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
                <!-- Activity Type Chips -->
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

                <!-- 1-Tap Result Presets -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" data-en="Quick Result Preset (1-Tap)" data-bn="কুইক ফলাফল (১-ট্যাপ)">Quick Result Preset (1-Tap)</label>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="preset in ['Spoke with lead - Interested', 'Requested Presentation', 'Call Back Later', 'No answer / Busy', 'Left Voicemail']" :key="preset">
                            <button type="button" 
                                    @click="activityForm.title = preset" 
                                    :class="activityForm.title === preset ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" 
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-medium transition-colors cursor-pointer" 
                                    x-text="preset">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Notes / Description -->
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

                <!-- Preset Date & Time Shortcuts -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" data-en="Quick Schedule Shortcuts" data-bn="কুইক শিডিউল শর্টকাট">Quick Schedule Shortcuts</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" 
                                @click="setSchedulePreset('today_evening')" 
                                class="py-2 px-2 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-800 text-xs font-bold border border-orange-200 text-center cursor-pointer">
                            Today 5:00 PM
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

                <!-- Custom Date & Time Picker -->
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
            // After triggering call or whatsapp, open quick log sheet
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

            // Format for datetime-local input (YYYY-MM-DDTHH:mm)
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

                    // Update DOM badge text
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

                    // Update Next Action text in DOM dynamically
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
