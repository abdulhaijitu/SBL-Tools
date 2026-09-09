@extends('layouts.app')

@section('page-title', request('stage') === 'converted' ? 'Members' : 'Leads')
@section('page-subtitle', 'Manage Inbound & Outbound Pipeline')

@section('content')
<div class="space-y-4">

    @if(request()->routeIs('members.index'))<div class="section-heading"><div><h2>Members</h2><p>Converted leads and their ongoing relationships.</p></div></div>@endif
    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search -->
            <form method="GET" action="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index') }}" class="flex-1 flex items-center gap-2">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                @if(request('stage') === 'converted')
                    <input type="hidden" name="stage" value="converted">
                @endif
                <div class="relative flex-1">
                    <input aria-label="Search leads by name, mobile, whatsapp, location..." type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search leads by name, mobile, whatsapp, location..." 
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 bg-slate-50/50">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800">
                    Search
                </button>
                @if(request()->hasAny(['search', 'stage', 'temperature', 'source_id', 'filter']))
                    <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['view' => $viewMode]) }}" class="px-2.5 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200">
                        Clear
                    </a>
                @endif
            </form>

            <!-- View Switcher & Add Lead Button -->
            <div class="flex items-center gap-2 self-end sm:self-auto">
                @unless(request()->routeIs('members.index'))
                <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200">
                    <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', array_merge(request()->query(), ['view' => 'table'])) }}" 
                       class="px-3 py-1 text-xs font-semibold rounded-lg transition-all {{ $viewMode === 'table' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Table
                    </a>
                    <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', array_merge(request()->query(), ['view' => 'kanban'])) }}" 
                       class="px-3 py-1 text-xs font-semibold rounded-lg transition-all {{ $viewMode === 'kanban' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Kanban
                    </a>
                </div>

                @endunless
                @can('leads.create')<a href="{{ route('leads.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Add Lead</span>
                </a>@endcan
            </div>
        </div>

    <!-- Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar">
            <span class="text-slate-400 font-semibold text-[11px] uppercase tracking-wider flex-shrink-0">Filters:</span>
            
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['view' => $viewMode]) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ !request()->hasAny(['filter', 'stage', 'temperature']) ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                All Leads
            </a>
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['view' => $viewMode, 'filter' => 'overdue']) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ request('filter') === 'overdue' ? 'bg-rose-600 text-white border-rose-600' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }}">
                🚨 Overdue Follow-ups
            </a>
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['view' => $viewMode, 'filter' => 'due_today']) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ request('filter') === 'due_today' ? 'bg-orange-600 text-white border-orange-600' : 'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100' }}">
                ⏰ Due Today
            </a>
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['view' => $viewMode, 'filter' => 'needs_action']) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ request('filter') === 'needs_action' ? 'bg-amber-600 text-white border-amber-600' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' }}">
                ⚠️ Needs Next Action
            </a>
            <a href="{{ route(request()->routeIs('members.index') ? 'members.index' : 'leads.index', ['view' => $viewMode, 'temperature' => 'hot']) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ request('temperature') === 'hot' ? 'bg-red-600 text-white border-red-600' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' }}">
                🔥 Hot
            </a>
        </div>
    </div>

    <!-- Leads List View: Desktop Table & Mobile Card Stack -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Desktop Table (hidden on small/medium mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Lead Info</th>
                        <th class="py-3.5 px-4">Contact</th>
                        <th class="py-3.5 px-4">Stage</th>
                        <th class="py-3.5 px-4">Next Action</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($leads as $lead)
                        <tr data-lead-id="{{ $lead->id }}" class="hover:bg-slate-50/70 transition-colors">
                            <!-- Lead Info -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-xs flex-shrink-0 overflow-hidden border border-orange-200/50">
                                        @if(!empty($lead->photo))
                                             <img src="{{ $lead->photo }}" alt="{{ $lead->name }}" class="w-full h-full object-cover">
                                        @else
                                             {{ substr($lead->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-slate-900 hover:text-orange-600 text-sm block">
                                            {{ $lead->name }}
                                        </a>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $lead->location ?? 'No location' }} 
                                            @if($lead->profession_or_business) • {{ $lead->profession_or_business }} @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact (Number + Call, WhatsApp, Facebook Icons) -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-900 text-xs font-mono tracking-wide">{{ $lead->mobile }}</div>
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <!-- Call Icon -->
                                    <a href="tel:{{ $lead->mobile }}" 
                                       title="Call {{ $lead->mobile }}" 
                                       class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white transition-all shadow-2xs active:scale-95" 
                                       aria-label="Call {{ $lead->mobile }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/>
                                        </svg>
                                    </a>

                                    <!-- WhatsApp Icon -->
                                    @php
                                        $waNumber = \App\Support\PhoneNumber::whatsapp($lead->whatsapp ?: $lead->mobile);
                                    @endphp
                                    @if ($waNumber)
                                        <a href="https://wa.me/{{ $waNumber }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer" 
                                           title="WhatsApp ({{ $lead->whatsapp ?: $lead->mobile }})" 
                                           class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white transition-all shadow-2xs active:scale-95" 
                                           aria-label="WhatsApp ({{ $lead->whatsapp ?: $lead->mobile }})">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-slate-100 text-slate-300" title="No WhatsApp">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/>
                                            </svg>
                                        </span>
                                    @endif

                                    <!-- Facebook Icon -->
                                    @if ($lead->facebook_url)
                                        <a href="{{ $lead->facebook_url }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer" 
                                           title="Facebook Profile" 
                                           class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white transition-all shadow-2xs active:scale-95" 
                                           aria-label="Facebook Profile">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-slate-100 text-slate-300" title="No Facebook URL">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Stage -->
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $lead->stage->badgeClasses() }}">
                                    {{ $lead->stage->label() }}
                                </span>
                            </td>

                            <!-- Next Action -->
                            <td class="py-3.5 px-4">
                                @if ($lead->next_action_at)
                                    <div class="font-medium {{ $lead->is_next_action_overdue ? 'text-rose-600 font-bold' : 'text-slate-800' }}">
                                        {{ $lead->next_action_type ?? 'Action' }}
                                    </div>
                                    <div class="text-[11px] {{ $lead->is_next_action_overdue ? 'text-rose-500 font-semibold' : 'text-slate-400' }}">
                                        {{ $lead->next_action_at->format('d M, h:i A') }}
                                        @if($lead->is_next_action_overdue)
                                            (Overdue)
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[11px] text-amber-600 font-semibold bg-amber-50 px-2 py-0.5 rounded-md">
                                        Needs Next Action
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('leads.show', $lead->id) }}" 
                                       class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition-all active:scale-95 shadow-xs border border-slate-200/60" 
                                       title="View Lead Profile"
                                       aria-label="View Lead Profile">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span class="sr-only">View</span>
                                    </a>
                                    @can('leads.edit')
                                    <a href="{{ route('leads.edit', $lead->id) }}" 
                                       class="w-8 h-8 rounded-lg flex items-center justify-center bg-orange-50 hover:bg-orange-100 text-orange-600 hover:text-orange-800 transition-all active:scale-95 shadow-xs border border-orange-200/60" 
                                       title="Edit Lead"
                                       aria-label="Edit Lead">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span class="sr-only">Edit</span>
                                    </a>
                                    @endcan
                                    @can('leads.delete')
                                    <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-8 h-8 rounded-lg flex items-center justify-center bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 transition-all active:scale-95 shadow-xs border border-rose-200/60 cursor-pointer" 
                                                title="Delete Lead"
                                                aria-label="Delete Lead">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
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
                                <div class="text-base font-semibold text-slate-700">No leads found</div>
                                <div class="text-xs text-slate-500 mt-1">Try adjusting your filters or add a new lead.</div>
                                @can('leads.create')<a href="{{ route('leads.create') }}" class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-orange-600 text-white text-xs font-semibold">
                                    + Add New Lead
                                </a>@endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card Stack (Visible on mobile/tablets < md) -->
        <div class="block md:hidden divide-y divide-slate-100">
            @forelse ($leads as $lead)
                <div data-lead-id="{{ $lead->id }}" class="p-4 space-y-3 hover:bg-slate-50/50 transition-colors">
                    <!-- Top Row: Avatar, Name & Stage Badge -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-sm flex-shrink-0 shadow-xs overflow-hidden border border-orange-200/50">
                                @if(!empty($lead->photo))
                                    <img src="{{ $lead->photo }}" alt="{{ $lead->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ substr($lead->name, 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-slate-900 hover:text-orange-600 text-sm block">
                                    {{ $lead->name }}
                                </a>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $lead->location ?? 'No location' }} 
                                    @if($lead->profession_or_business) • {{ $lead->profession_or_business }} @endif
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border flex-shrink-0 {{ $lead->stage->badgeClasses() }}">
                            {{ $lead->stage->label() }}
                        </span>
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

                    <!-- Next Action Status -->
                    <div class="bg-slate-50 rounded-xl p-2.5 flex items-center justify-between text-xs border border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <span>{{ $lead->is_next_action_overdue ? '🚨' : '⏰' }}</span>
                            <div>
                                @if ($lead->next_action_at)
                                    <span class="font-semibold {{ $lead->is_next_action_overdue ? 'text-rose-600' : 'text-slate-700' }}">
                                        {{ $lead->next_action_type ?? 'Action' }}
                                    </span>
                                    <span class="text-[11px] {{ $lead->is_next_action_overdue ? 'text-rose-500 font-bold' : 'text-slate-400' }}">
                                        • {{ $lead->next_action_at->format('d M, h:i A') }}
                                        @if($lead->is_next_action_overdue) (Overdue) @endif
                                    </span>
                                @else
                                    <span class="text-amber-600 font-semibold text-[11px]">Needs Next Action</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Thumb-Friendly Action Buttons -->
                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <a href="tel:{{ $lead->mobile }}" 
                           class="py-2.5 px-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-semibold text-xs text-center flex items-center justify-center gap-1.5 active:scale-95 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>
                            <span>Call</span>
                        </a>

                        <a href="https://wa.me/{{ \App\Support\PhoneNumber::whatsapp($lead->whatsapp ?: $lead->mobile) }}" 
                           target="_blank"
                           class="py-2.5 px-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs text-center flex items-center justify-center gap-1.5 shadow-xs active:scale-95 transition-all">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>
                            <span>WhatsApp</span>
                        </a>

                        <a href="{{ route('leads.show', $lead->id) }}" 
                           class="py-2.5 px-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs text-center flex items-center justify-center gap-1 active:scale-95 transition-all">
                            <span>Details</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>

                    <!-- Manage Lead (Edit & Delete) -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        @can('leads.edit')<a href="{{ route('leads.edit', $lead->id) }}" 
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 text-xs font-semibold transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            Edit
                        </a>@endcan
                        @can('leads.delete')<form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Delete
                            </button>
                        </form>@endcan
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400">
                    <div class="text-base font-semibold text-slate-700">No leads found</div>
                    <div class="text-xs text-slate-500 mt-1">Try adjusting your filters or add a new lead.</div>
                    @can('leads.create')<a href="{{ route('leads.create') }}" class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-orange-600 text-white text-xs font-semibold">
                        + Add New Lead
                    </a>@endcan
                </div>
            @endforelse
        </div>

        @if($leads->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $leads->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

