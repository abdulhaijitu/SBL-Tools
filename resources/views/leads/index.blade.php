@extends('layouts.app')

@section('page-title', 'Leads CRM')
@section('page-subtitle', 'Manage Inbound & Outbound Pipeline')

@section('content')
<div class="space-y-4">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search -->
            <form method="GET" action="{{ route('leads.index') }}" class="flex-1 flex items-center gap-2">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                <div class="relative flex-1">
                    <input type="text" 
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
                    <a href="{{ route('leads.index', ['view' => $viewMode]) }}" class="px-2.5 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200">
                        Clear
                    </a>
                @endif
            </form>

            <!-- View Switcher & Add Lead Button -->
            <div class="flex items-center gap-2 self-end sm:self-auto">
                <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200">
                    <a href="{{ route('leads.index', array_merge(request()->query(), ['view' => 'table'])) }}" 
                       class="px-3 py-1 text-xs font-semibold rounded-lg transition-all {{ $viewMode === 'table' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Table
                    </a>
                    <a href="{{ route('leads.index', array_merge(request()->query(), ['view' => 'kanban'])) }}" 
                       class="px-3 py-1 text-xs font-semibold rounded-lg transition-all {{ $viewMode === 'kanban' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Kanban
                    </a>
                </div>

                <a href="{{ route('leads.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Add Lead</span>
                </a>
            </div>
        </div>

        <!-- Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <span class="text-slate-400 font-semibold text-[11px] uppercase tracking-wider flex-shrink-0">Filters:</span>
            
            <a href="{{ route('leads.index', ['view' => $viewMode]) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ !request()->hasAny(['filter', 'stage', 'temperature']) ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                All Leads
            </a>
            <a href="{{ route('leads.index', ['view' => $viewMode, 'filter' => 'overdue']) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ request('filter') === 'overdue' ? 'bg-rose-600 text-white border-rose-600' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }}">
                🚨 Overdue Follow-ups
            </a>
            <a href="{{ route('leads.index', ['view' => $viewMode, 'filter' => 'due_today']) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ request('filter') === 'due_today' ? 'bg-orange-600 text-white border-orange-600' : 'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100' }}">
                ⏰ Due Today
            </a>
            <a href="{{ route('leads.index', ['view' => $viewMode, 'filter' => 'needs_action']) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ request('filter') === 'needs_action' ? 'bg-amber-600 text-white border-amber-600' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' }}">
                ⚠️ Needs Next Action
            </a>
            <a href="{{ route('leads.index', ['view' => $viewMode, 'temperature' => 'hot']) }}" 
               class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0 {{ request('temperature') === 'hot' ? 'bg-red-600 text-white border-red-600' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' }}">
                🔥 Hot
            </a>
        </div>
    </div>

    <!-- Leads Table View -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Lead Info</th>
                        <th class="py-3.5 px-4">Contact</th>
                        <th class="py-3.5 px-4">Source & Interest</th>
                        <th class="py-3.5 px-4">Stage</th>
                        <th class="py-3.5 px-4">Score / Temp</th>
                        <th class="py-3.5 px-4">Next Action</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($leads as $lead)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Lead Info -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                        {{ substr($lead->name, 0, 1) }}
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

                            <!-- Contact -->
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">{{ $lead->mobile }}</div>
                                <div class="flex items-center gap-2 mt-1">
                                    <a href="tel:{{ $lead->mobile }}" title="Call" class="text-slate-400 hover:text-emerald-600">
                                        📞
                                    </a>
                                    @if ($lead->whatsapp)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp) }}" target="_blank" title="WhatsApp" class="text-slate-400 hover:text-emerald-600">
                                            💬
                                        </a>
                                    @endif
                                    @if ($lead->facebook_url)
                                        <a href="{{ $lead->facebook_url }}" target="_blank" title="Facebook" class="text-slate-400 hover:text-blue-600">
                                            🌐
                                        </a>
                                    @endif
                                </div>
                            </td>

                            <!-- Source & Interest -->
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-800 block">{{ $lead->source->name ?? 'N/A' }}</span>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @if($lead->lead_tag)
                                        <span class="px-1.5 py-0.5 rounded bg-orange-100 text-orange-800 text-[10px] font-bold">
                                            {{ $lead->lead_tag }}
                                        </span>
                                    @endif
                                    @if($lead->interest_types)
                                        @foreach(array_slice($lead->interest_types, 0, 2) as $type)
                                            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px]">
                                                {{ $type }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </td>

                            <!-- Stage -->
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $lead->stage->badgeClasses() }}">
                                    {{ $lead->stage->label() }}
                                </span>
                            </td>

                            <!-- Score / Temp -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-900">{{ $lead->score }}/100</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $lead->temperature->badgeClasses() }}">
                                        {{ $lead->temperature->label() }}
                                    </span>
                                </div>
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
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs transition-colors">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="text-base font-semibold text-slate-700">No leads found</div>
                                <div class="text-xs text-slate-500 mt-1">Try adjusting your filters or add a new lead.</div>
                                <a href="{{ route('leads.create') }}" class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-orange-600 text-white text-xs font-semibold">
                                    + Add New Lead
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $leads->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

