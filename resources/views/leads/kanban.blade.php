@extends('layouts.app')

@section('page-title', 'Leads Kanban Board')
@section('page-subtitle', 'Visual Drag-and-Drop Pipeline Stages')

@section('content')
<div class="space-y-4" x-data="kanbanBoard()">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500">Drag lead cards between stages to update progress.</span>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto">
            <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200">
                <a href="{{ route('leads.index', ['view' => 'table']) }}" 
                   class="px-3 py-1 text-xs font-semibold rounded-lg text-slate-600 hover:text-slate-900">
                    Table
                </a>
                <a href="{{ route('leads.index', ['view' => 'kanban']) }}" 
                   class="px-3 py-1 text-xs font-semibold rounded-lg bg-white text-slate-900 shadow-xs">
                    Kanban
                </a>
            </div>

            <a href="{{ route('leads.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Lead</span>
            </a>
        </div>
    </div>

    <!-- Notification Toast for drag updates -->
    <div x-show="toastMessage" 
         x-transition 
         class="fixed top-20 right-6 z-50 bg-slate-900 text-white text-xs px-4 py-2.5 rounded-xl shadow-xl flex items-center gap-2"
         x-cloak>
        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
        <span x-text="toastMessage"></span>
    </div>

    <!-- Mobile Stage Quick Navigator Pills (sm/md only) -->
    <div class="block md:hidden bg-white rounded-2xl p-2.5 border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar text-[11px]">
            <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px] pl-1 pr-1 flex-shrink-0">Jump:</span>
            @foreach ($kanbanColumns as $stageKey => $stageLeads)
                <button type="button" 
                        @click="scrollToCol('{{ $stageKey }}')"
                        class="px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-orange-50 hover:border-orange-300 font-semibold text-slate-700 flex-shrink-0 flex items-center gap-1.5 transition-colors">
                    <span>{{ ucfirst(str_replace('_', ' ', $stageKey)) }}</span>
                    <span class="px-1.5 py-0.2 rounded-full bg-white text-slate-900 border text-[10px] font-bold">{{ $stageLeads->count() }}</span>
                </button>
            @endforeach
        </div>
        <div class="text-[11px] text-slate-400 text-center mt-2 flex items-center justify-center gap-1">
            <span>👈</span> <span>Swipe horizontally across pipeline stages</span> <span>👉</span>
        </div>
    </div>

    <!-- Horizontal Scrolling Kanban Board -->
    <div class="flex items-start gap-3 overflow-x-auto pb-6 min-h-[calc(100vh-230px)] no-scrollbar" id="kanban-scroll-wrapper">
        @foreach ($kanbanColumns as $stageKey => $stageLeads)
            <div id="kanban-col-{{ $stageKey }}" class="w-72 flex-shrink-0 bg-slate-100/80 rounded-2xl border border-slate-200/80 p-3 flex flex-col max-h-[calc(100vh-230px)]">
                <!-- Column Header -->
                <div class="flex items-center justify-between mb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full 
                            @if($stageKey === 'new') bg-blue-500 
                            @elseif($stageKey === 'contacted') bg-sky-500
                            @elseif($stageKey === 'interested') bg-amber-500
                            @elseif($stageKey === 'qualified') bg-indigo-500
                            @elseif($stageKey === 'presentation') bg-purple-500
                            @elseif($stageKey === 'follow_up') bg-orange-500
                            @elseif($stageKey === 'decision') bg-yellow-500
                            @elseif($stageKey === 'converted') bg-emerald-500
                            @else bg-slate-400 @endif">
                        </span>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-tight">
                            {{ ucfirst(str_replace('_', ' ', $stageKey)) }}
                        </h4>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-white px-2 py-0.5 rounded-full border border-slate-200">
                        {{ $stageLeads->count() }}
                    </span>
                </div>

                <!-- Cards Container (Sortable List) -->
                <div class="space-y-2.5 overflow-y-auto flex-1 pr-1 kanban-cards-container" 
                     data-stage="{{ $stageKey }}"
                     x-ref="col_{{ $stageKey }}">
                    @foreach ($stageLeads as $lead)
                        <div class="bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-xs hover:border-orange-300 hover:shadow-md transition-all cursor-grab active:cursor-grabbing kanban-card"
                             data-lead-id="{{ $lead->id }}">
                            
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-sm text-slate-900 hover:text-orange-600 truncate block">
                                    {{ $lead->name }}
                                </a>
                                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md border flex-shrink-0 {{ $lead->temperature->badgeClasses() }}">
                                    {{ $lead->temperature->label() }}
                                </span>
                            </div>

                            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                                <span>📞 {{ $lead->mobile }}</span>
                                <span class="font-bold text-slate-700">{{ $lead->score }} pts</span>
                            </div>

                            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                <div class="flex items-center gap-1.5">
                                    @if($lead->next_action_at)
                                        <span class="font-medium {{ $lead->is_next_action_overdue ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                                            {{ $lead->next_action_at->format('d M') }}
                                        </span>
                                    @else
                                        <span class="text-amber-600 font-semibold">No Action</span>
                                    @endif
                                </div>
                                
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('leads.show', $lead->id) }}" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition-colors" title="View Lead Profile">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <a href="{{ route('leads.edit', $lead->id) }}" class="text-slate-400 hover:text-orange-600 p-1 rounded hover:bg-orange-50 transition-colors" title="Edit Lead">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 rounded hover:bg-rose-50 transition-colors" title="Delete Lead">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

</div>

<script>
function kanbanBoard() {
    return {
        toastMessage: '',
        showToast(msg) {
            this.toastMessage = msg;
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: msg, type: 'success' } }));
            setTimeout(() => { this.toastMessage = ''; }, 3000);
        },
        scrollToCol(stageKey) {
            const el = document.getElementById('kanban-col-' + stageKey);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }
        },
        init() {
            const containers = document.querySelectorAll('.kanban-cards-container');
            containers.forEach(container => {
                new Sortable(container, {
                    group: 'kanban-pipeline',
                    animation: 150,
                    ghostClass: 'opacity-40',
                    onEnd: (evt) => {
                        const itemEl = evt.item;
                        const leadId = itemEl.getAttribute('data-lead-id');
                        const newStage = evt.to.getAttribute('data-stage');
                        const oldStage = evt.from.getAttribute('data-stage');

                        if (newStage === oldStage) return;

                        // Send AJAX request
                        fetch(`/leads/${leadId}/stage`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ stage: newStage })
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.showToast(`Stage updated to ${data.stage_label}!`);
                        })
                        .catch(err => {
                            console.error('Failed to update stage:', err);
                            this.showToast('Error updating stage.');
                        });
                    }
                });
            });
        }
    }
}
</script>
@endsection

