@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Operations Overview & Daily Action Priorities')

@section('content')
<div class="space-y-6">

    <!-- 1. Today's Key Action Priorities (Section 3.1 & 23) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Follow-ups Due Today -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Follow-ups Today</span>
                <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $followupsDueToday->count() }}</span>
                <span class="text-[11px] text-slate-400">Scheduled for today</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Overdue Follow-ups (Alert) -->
        <div class="bg-white rounded-2xl p-4 border {{ $overdueFollowups->count() > 0 ? 'border-rose-200 bg-rose-50/20' : 'border-slate-200/80' }} shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold {{ $overdueFollowups->count() > 0 ? 'text-rose-600' : 'text-slate-500' }} uppercase tracking-wider block">Overdue Follow-ups</span>
                <span class="text-2xl font-bold {{ $overdueFollowups->count() > 0 ? 'text-rose-700' : 'text-slate-900' }} mt-1 block">{{ $overdueFollowups->count() }}</span>
                <span class="text-[11px] text-slate-400">Requires urgent touch</span>
            </div>
            <div class="w-11 h-11 rounded-xl {{ $overdueFollowups->count() > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
        </div>

        <!-- Presentations Today -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Presentations Today</span>
                <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $presentationsToday->count() }}</span>
                <span class="text-[11px] text-slate-400">1-on-1 & Group sessions</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            </div>
        </div>

        <!-- Total Leads / Funnel Base -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Active Leads</span>
                <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $totalLeads }}</span>
                <span class="text-[11px] text-slate-400">{{ $newLeadsTodayCount }} added today</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- 2. Lead Pipeline Stages Quick Funnel (Section 3.1 & 5) -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Lead Pipeline Funnel</h3>
                <p class="text-xs text-slate-500">Continuous conversion progress from initial contact to conversion</p>
            </div>
            <a href="{{ route('leads.index', ['view' => 'kanban']) }}" class="text-xs font-semibold text-orange-600 hover:text-orange-700 flex items-center gap-1">
                <span>View Kanban Board</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2">
            @foreach ($funnelStages as $stageKey => $count)
                <a href="{{ route('leads.index', ['stage' => $stageKey]) }}" 
                   class="bg-slate-50 hover:bg-orange-50/50 hover:border-orange-200 border border-slate-100 rounded-xl p-3 text-center transition-all group active:scale-95 flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500 group-hover:text-orange-700 uppercase tracking-tight block truncate">
                            {{ ucfirst(str_replace('_', ' ', $stageKey)) }}
                        </span>
                        <span class="text-xl font-bold text-slate-900 group-hover:text-orange-600 mt-1 block">
                            {{ $count }}
                        </span>
                    </div>
                    <div class="w-full bg-slate-200/80 h-1 rounded-full mt-2 overflow-hidden">
                        <div class="bg-orange-500 h-full rounded-full transition-all duration-500" 
                             style="width: {{ $totalLeads > 0 ? min(100, round(($count / $totalLeads) * 100)) : 0 }}%">
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- 3. Overdue & Today Actions Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Overdue Follow-ups & Due Today (2 Columns) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Urgent Overdue Follow-ups Table/Cards -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                        <h3 class="text-sm font-bold text-slate-900">Immediate Follow-up Needed (Overdue)</h3>
                    </div>
                    <span class="text-xs font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">
                        {{ $overdueFollowups->count() }} Overdue
                    </span>
                </div>

                @if ($overdueFollowups->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-sm">
                        🎉 Great job! No overdue follow-ups right now.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($overdueFollowups as $lead)
                            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50 transition-colors">
                            <div data-lead-id="{{ $lead->id }}" class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50 transition-colors">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-sm flex-shrink-0">
                                        {{ substr($lead->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-sm text-slate-900 hover:text-orange-600">
                                                {{ $lead->name }}
                                            </a>
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $lead->temperature->badgeClasses() }}">
                                                {{ $lead->temperature->label() }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-3">
                                            <span>📞 {{ $lead->mobile }}</span>
                                            <span class="text-rose-600 font-medium">Overdue since {{ $lead->next_action_at?->diffForHumans() }}</span>
                                        </div>
                                        @if ($lead->next_action_type)
                                            <div class="text-xs font-medium text-slate-700 mt-1">
                                                Action: <span class="text-orange-600">{{ $lead->next_action_type }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 self-end sm:self-center">
                                    <a href="{{ route('leads.show', $lead->id) }}" class="px-3 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold shadow-xs">
                                        Take Action
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Today's Scheduled Tasks -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900">Today's Scheduled Tasks</h3>
                    <a href="{{ route('tasks.index') }}" class="text-xs font-semibold text-orange-600 hover:text-orange-700">View All Tasks</a>
                </div>

                @if ($todayTasks->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-sm">
                        No pending tasks scheduled for today yet.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($todayTasks as $task)
                            <div class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50 transition-colors">
                            <div data-task-id="{{ $task->id }}" class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $task->priority->badgeClasses() }}">
                                        {{ $task->priority->value }}
                                    </span>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ $task->title }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                                            <span>{{ $task->type->value }}</span>
                                            @if ($task->lead)
                                                <span>•</span>
                                                <a href="{{ route('leads.show', $task->lead->id) }}" class="text-orange-600 hover:underline">
                                                    {{ $task->lead->name }}
                                                </a>
                                            @endif
                                            <span>• Due {{ $task->due_at->format('h:i A') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <form action="{{ route('tasks.complete', $task->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="outcome" value="Completed successfully as planned">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-xs font-semibold text-slate-700 transition-colors">
                                        ✓ Mark Done
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- Right: Priorities & Recent Activity (1 Column) -->
        <div class="space-y-6">

            <!-- High Priority Leads (Hot / Warm) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                        <span>🔥</span> Hot Priority Leads
                    </h3>
                    <span class="text-xs font-semibold text-slate-400">{{ $hotLeads->count() }} hot</span>
                </div>

                @if ($hotLeads->isEmpty())
                    <div class="py-6 text-center text-slate-400 text-xs">
                        No leads scored as Hot (80+) yet.
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach ($hotLeads as $lead)
                            <a href="{{ route('leads.show', $lead->id) }}" class="block p-3 rounded-xl border border-slate-100 hover:border-orange-200 hover:bg-orange-50/30 transition-all">
                            <a data-lead-id="{{ $lead->id }}" href="{{ route('leads.show', $lead->id) }}" class="block p-3 rounded-xl border border-slate-100 hover:border-orange-200 hover:bg-orange-50/30 transition-all">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900 truncate">{{ $lead->name }}</span>
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-orange-600 text-white">
                                        Score: {{ $lead->score }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                                    <span>{{ $lead->stage->label() }}</span>
                                    <span>{{ $lead->mobile }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Stale Leads Warning -->
            @if ($staleLeads->isNotEmpty())
                <div class="bg-white rounded-2xl border border-purple-200 shadow-xs p-5 bg-purple-50/10">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-purple-900 flex items-center gap-1.5">
                            <span>⏳</span> Stale Leads Alert
                        </h3>
                        <span class="text-[11px] font-medium text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full">
                            >7-14 days inactive
                        </span>
                    </div>

                    <div class="space-y-2">
                        @foreach ($staleLeads as $lead)
                            <div class="flex items-center justify-between p-2.5 bg-white border border-purple-100 rounded-xl text-xs">
                            <div data-lead-id="{{ $lead->id }}" class="flex items-center justify-between p-2.5 bg-white border border-purple-100 rounded-xl text-xs">
                                <div>
                                    <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-slate-900 hover:text-purple-700">
                                        {{ $lead->name }}
                                    </a>
                                    <div class="text-[11px] text-slate-400">Last contact: {{ $lead->last_contact_at?->diffForHumans() ?? 'None' }}</div>
                                </div>
                                <a href="{{ route('leads.show', $lead->id) }}" class="text-[11px] font-semibold text-purple-700 hover:underline">
                                    Re-engage →
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recent Timeline Activities -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Recent Activity Stream</h3>
                <div class="space-y-3">
                    @forelse ($recentActivities as $activity)
                        <div class="flex items-start gap-2.5 text-xs">
                            <span class="w-2 h-2 rounded-full bg-orange-500 mt-1 flex-shrink-0"></span>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-slate-800 truncate">{{ $activity->title }}</div>
                                <div class="text-[11px] text-slate-500 truncate">
                                    @if ($activity->lead)
                                        <a href="{{ route('leads.show', $activity->lead->id) }}" class="text-orange-600 hover:underline">{{ $activity->lead->name }}</a> •
                                    @endif
                                    {{ $activity->performed_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-400 text-xs py-4">No recent activity recorded yet.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
