@extends('layouts.app')

@section('page-title', 'Tasks & Follow-ups')
@section('page-subtitle', 'Manage Daily Activities & Complete Interaction Cycles')

@section('content')
<div class="space-y-4" 
     x-data="{ 
        completeModal: false, 
        completeTaskId: null, 
        completeTaskTitle: '', 
        newTaskModal: false,
        editTaskModal: false,
        editingTask: {
            id: null,
            title: '',
            type: 'Follow-up',
            priority: 'Medium',
            related_lead_id: '',
            due_at: '',
            notes: ''
        },
        openEditTask(t) {
            this.editingTask = {
                id: t.id,
                title: t.title || '',
                type: t.type || 'Follow-up',
                priority: t.priority || 'Medium',
                related_lead_id: t.related_lead_id || '',
                due_at: t.due_at || '',
                notes: t.notes || ''
            };
            this.editTaskModal = true;
        }
     }">

    <!-- Stats and Action Header -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('tasks.index', ['filter' => 'today']) }}" 
           class="bg-white rounded-2xl p-4 border {{ $filter === 'today' ? 'border-orange-500 ring-2 ring-orange-500/20' : 'border-slate-200/80' }} shadow-xs">
            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Due Today</span>
            <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $stats['today'] }}</span>
        </a>

        <a href="{{ route('tasks.index', ['filter' => 'overdue']) }}" 
           class="bg-white rounded-2xl p-4 border {{ $filter === 'overdue' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-200/80' }} shadow-xs">
            <span class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider block">Overdue</span>
            <span class="text-2xl font-bold text-rose-700 mt-1 block">{{ $stats['overdue'] }}</span>
        </a>

        <a href="{{ route('tasks.index', ['filter' => 'pending']) }}" 
           class="bg-white rounded-2xl p-4 border {{ $filter === 'pending' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-200/80' }} shadow-xs">
            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Pending</span>
            <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $stats['pending'] }}</span>
        </a>

        <a href="{{ route('tasks.index', ['filter' => 'completed']) }}" 
           class="bg-white rounded-2xl p-4 border {{ $filter === 'completed' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200/80' }} shadow-xs">
            <span class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider block">Completed</span>
            <span class="text-2xl font-bold text-emerald-700 mt-1 block">{{ $stats['completed'] }}</span>
        </a>
    </div>

    <!-- Filter Bar & Create Button -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2 overflow-x-auto text-xs pb-1 sm:pb-0">
            <a href="{{ route('tasks.index', ['filter' => 'pending']) }}" class="px-3 py-1.5 rounded-lg border font-medium {{ $filter === 'pending' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                Pending
            </a>
            <a href="{{ route('tasks.index', ['filter' => 'today']) }}" class="px-3 py-1.5 rounded-lg border font-medium {{ $filter === 'today' ? 'bg-orange-600 text-white border-orange-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                Due Today
            </a>
            <a href="{{ route('tasks.index', ['filter' => 'overdue']) }}" class="px-3 py-1.5 rounded-lg border font-medium {{ $filter === 'overdue' ? 'bg-rose-600 text-white border-rose-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                Overdue
            </a>
            <a href="{{ route('tasks.index', ['filter' => 'completed']) }}" class="px-3 py-1.5 rounded-lg border font-medium {{ $filter === 'completed' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                Completed
            </a>
            <a href="{{ route('tasks.index', ['filter' => 'all']) }}" class="px-3 py-1.5 rounded-lg border font-medium {{ $filter === 'all' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                All Tasks
            </a>
        </div>

        <button @click="newTaskModal = true" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold shadow-xs transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Schedule Task</span>
        </button>
    </div>

    <!-- Tasks List -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs divide-y divide-slate-100 overflow-hidden">
        @forelse ($tasks as $task)
            <div data-task-id="{{ $task->id }}" class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/70 transition-colors">
                <div class="flex items-start gap-3">
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $task->priority->badgeClasses() }} flex-shrink-0 mt-0.5">
                        {{ $task->priority->value }}
                    </span>
                    <div>
                        <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>{{ $task->title }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium border {{ $task->status->badgeClasses() }}">
                                {{ $task->status->value }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-3">
                            <span class="font-medium text-slate-700">{{ $task->type->value }}</span>
                            @if ($task->lead)
                                <span>•</span>
                                <a href="{{ route('leads.show', $task->lead->id) }}" class="text-orange-600 font-semibold hover:underline">
                                    Lead: {{ $task->lead->name }} ({{ $task->lead->mobile }})
                                </a>
                            @endif
                            <span>•</span>
                            <span class="{{ $task->status !== \App\Enums\TaskStatus::COMPLETED && $task->due_at->isPast() ? 'text-rose-600 font-bold' : 'text-slate-500' }}">
                                Due: {{ $task->due_at->format('d M, Y h:i A') }}
                                @if($task->status !== \App\Enums\TaskStatus::COMPLETED && $task->due_at->isPast())
                                    (Overdue)
                                @endif
                            </span>
                        </div>
                        @if ($task->notes)
                            <p class="text-xs text-slate-600 mt-1.5 bg-slate-50 p-2 rounded-lg border border-slate-100 inline-block">
                                {{ $task->notes }}
                            </p>
                        @endif

                        @if ($task->outcome)
                            <div class="mt-2 text-xs text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100 inline-flex items-center gap-1.5">
                                <span>✓ Outcome:</span>
                                <span class="font-medium">{{ $task->outcome }}</span>
                                @if($task->next_action)
                                    <span>| Next: {{ $task->next_action }} ({{ $task->next_action_at?->format('d M') }})</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center">
                    @if ($task->status !== \App\Enums\TaskStatus::COMPLETED)
                        <button @click="completeTaskId = {{ $task->id }}; completeTaskTitle = '{{ addslashes($task->title) }}'; completeModal = true"
                                class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors">
                            ✓ Complete
                        </button>
                    @endif

                    <button type="button"
                            @click="openEditTask({{ json_encode([
                                'id' => $task->id,
                                'title' => $task->title,
                                'type' => $task->type->value,
                                'priority' => $task->priority->value,
                                'related_lead_id' => $task->related_lead_id,
                                'due_at' => $task->due_at->format('Y-m-d\TH:i'),
                                'notes' => $task->notes
                            ]) }})"
                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-orange-50 text-slate-700 hover:text-orange-700 font-semibold text-xs transition-colors">
                        Edit
                    </button>

                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Delete task?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg" title="Delete Task">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-slate-400 text-xs">
                No tasks found for this filter.
            </div>
        @endforelse
    </div>

    @if ($tasks->hasPages())
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
            {{ $tasks->links() }}
        </div>
    @endif

    <!-- Task Completion Modal (Section 9: Mandatory Outcome & Next Action) -->
    <div x-show="completeModal" 
         x-transition 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="completeModal = false" class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-1">Complete Task</h3>
            <p class="text-xs text-slate-500 mb-4" x-text="completeTaskTitle"></p>

            <form :action="`/tasks/${completeTaskId}/complete`" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Task Outcome <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="outcome" required rows="2" placeholder="e.g. Lead agreed to join online presentation, very interested." class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 p-2.5"></textarea>
                </div>

                <div class="bg-orange-50 p-3 rounded-xl border border-orange-200 space-y-2">
                    <span class="text-xs font-bold text-orange-900 block">Immediate Next Action (Rule: No lead without next step)</span>
                    <input type="text" name="next_action" placeholder="e.g. Send Zoom link & follow-up" class="w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Next Action Date/Time</label>
                        <input type="datetime-local" name="next_action_at" class="w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="completeModal = false" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs">Save & Complete</button>
                </div>
            </form>
        </div>
    </div>

    <!-- New Task Modal -->
    <div x-show="newTaskModal" 
         x-transition 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="newTaskModal = false" class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900">Schedule Task / Follow-up</h3>
                <button @click="newTaskModal = false" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
            </div>

            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. Follow-up regarding wholesale quote" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Type</label>
                        <select name="type" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            @foreach ($taskTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Priority</label>
                        <select name="priority" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->value }}" {{ $priority->value === 'Medium' ? 'selected' : '' }}>{{ $priority->value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Related Lead (Optional)</label>
                        <select name="related_lead_id" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            <option value="">None (General Task)</option>
                            @foreach ($leads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->name }} ({{ $lead->mobile }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Due Date & Time <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="due_at" required value="{{ now()->addDay()->setHour(11)->format('Y-m-d\TH:i') }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Specific notes or preparation details..." class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="newTaskModal = false" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl shadow-xs">Schedule Task</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div x-show="editTaskModal" 
         x-transition 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="editTaskModal = false" class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900">Edit Scheduled Task</h3>
                <button @click="editTaskModal = false" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
            </div>

            <form :action="`/tasks/${editingTask.id}`" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" x-model="editingTask.title" required placeholder="e.g. Follow-up regarding wholesale quote" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Type</label>
                        <select name="type" x-model="editingTask.type" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            @foreach ($taskTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Priority</label>
                        <select name="priority" x-model="editingTask.priority" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->value }}">{{ $priority->value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Related Lead (Optional)</label>
                        <select name="related_lead_id" x-model="editingTask.related_lead_id" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            <option value="">None (General Task)</option>
                            @foreach ($leads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->name }} ({{ $lead->mobile }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Due Date & Time <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="due_at" x-model="editingTask.due_at" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" x-model="editingTask.notes" rows="2" placeholder="Specific notes or preparation details..." class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editTaskModal = false" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl shadow-xs">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

