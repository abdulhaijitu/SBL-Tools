@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Operations Overview & Daily Action Priorities')

@section('content')
<div class="space-y-6">

    <div class="section-heading">
        <div><p>{{ now()->format('l, j F Y') }}</p><h2>Welcome back, {{ Auth::user()->name }}</h2><p>Your leads, follow-ups and team at a glance.</p></div>
        @can('reports.view')<a href="{{ route('reports.index') }}" class="btn-secondary">Activity reports</a>@endcan
    </div>
    @can('leads.view')
    <div class="metric-grid">
        <a href="{{ route('leads.index') }}" class="metric-card"><span>Total Active Leads</span><strong id="active-leads">{{ $totalActiveLeads }}</strong><small>{{ $newLeadsTodayCount }} added today</small></a>
        <a href="{{ route('leads.index', ['filter' => 'due_today']) }}" class="metric-card"><span>Today Followup</span><strong id="today-followup">{{ $followupsDueToday->count() }}</strong><small>Review today's follow-ups</small></a>
        <a href="{{ route('presentations.index') }}" class="metric-card"><span>Total Presentations</span><strong id="total-presentations">{{ $totalPresentations }}</strong><small>All presentation sessions</small></a>
    </div>

    @endcan
    @cannot('leads.view')
    <div class="app-panel"><h2 class="font-semibold">Your workspace is ready</h2><p class="mt-2 text-sm text-slate-600">Explore your team below. Your administrator can enable additional tools for your role.</p><a href="{{ route('team.index') }}" class="btn-primary mt-4">Open Team Explorer</a></div>
    @endcannot
    @if($teamRoot)
    <a href="{{ route('team.index') }}" class="app-panel flex flex-wrap items-center justify-between gap-3">
        <div><span class="text-xs font-medium text-slate-500">Team Explorer</span><h2 class="font-semibold mt-1">{{ $teamRoot->member_name }}</h2><p class="text-sm text-slate-500">{{ $teamRoot->children->where('is_target', false)->count() }} of 10 direct placements filled</p></div>
        <span class="text-sm font-semibold text-orange-700">Explore team &rarr;</span>
    </a>
    @endif

    @can('leads.view')
    <!-- ==================== 4. LEAD PIPELINE CONVERSION FUNNEL ==================== -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>📊</span> Lead Pipeline Funnel
                </h3>
                <p class="text-xs text-slate-500">Continuous conversion progress from initial contact to team partner conversion</p>
            </div>
            <a href="{{ route('leads.index', ['view' => 'kanban']) }}" 
               class="text-xs font-bold text-orange-600 hover:text-orange-700 flex items-center gap-1 transition-transform hover:translate-x-0.5">
                <span>View Kanban Board</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5">
            @php
                $stageColorMap = [
                    'new' => 'from-blue-500 to-blue-600',
                    'contacted' => 'from-cyan-500 to-cyan-600',
                    'interested' => 'from-indigo-500 to-indigo-600',
                    'qualified' => 'from-violet-500 to-violet-600',
                    'presentation' => 'from-purple-500 to-purple-600',
                    'follow_up' => 'from-amber-500 to-amber-600',
                    'decision' => 'from-orange-500 to-orange-600',
                    'converted' => 'from-emerald-500 to-emerald-600',
                ];
            @endphp
            @foreach ($funnelStages as $stageKey => $count)
                @php
                    $pct = $totalLeads > 0 ? min(100, round(($count / $totalLeads) * 100)) : 0;
                    $barGradient = $stageColorMap[$stageKey] ?? 'from-orange-500 to-orange-600';
                @endphp
                <a href="{{ route('leads.index', ['stage' => $stageKey]) }}" 
                   data-funnel-stage="{{ $stageKey }}"
                   class="bg-slate-50 hover:bg-orange-50/50 hover:border-orange-200 border border-slate-200/60 rounded-xl p-3 text-center transition-all group active:scale-95 flex flex-col justify-between shadow-2xs hover:shadow-xs">
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 group-hover:text-orange-700 tracking-tight block whitespace-normal min-h-8">
                            {{ ucfirst(str_replace('_', ' ', $stageKey)) }}
                        </span>
                        <span data-funnel-count="{{ $stageKey }}" class="text-xl font-black text-slate-900 group-hover:text-orange-600 mt-1 block">
                            {{ $count }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-slate-200/80 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r {{ $barGradient }} h-full rounded-full transition-all duration-500" 
                                 style="width: {{ $pct }}%">
                            </div>
                        </div>
                        <span class="text-[9px] text-slate-400 font-semibold mt-1 block">{{ $pct }}%</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- ==================== 5. OVERDUE FOLLOW-UPS & TODAY'S ACTIONS GRID ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Overdue Follow-ups & Due Today (2 Columns) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Urgent Overdue Follow-ups Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 "></span>
                        <h3 class="text-sm font-bold text-slate-900">Immediate Follow-up Needed (Overdue)</h3>
                    </div>
                    <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-200">
                        {{ $overdueFollowups->count() }} Overdue
                    </span>
                </div>

                @if ($overdueFollowups->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <span class="text-2xl block mb-1">🎉</span>
                        <span class="font-semibold text-slate-600">Great job! No overdue follow-ups right now.</span>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($overdueFollowups as $lead)
                            @php
                                $cleanWa = \App\Support\PhoneNumber::whatsapp($lead->whatsapp ?: $lead->mobile);
                            @endphp
                            <div data-lead-id="{{ $lead->id }}" class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 font-black flex items-center justify-center text-sm flex-shrink-0 shadow-xs">
                                        {{ substr($lead->name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-sm text-slate-900 hover:text-orange-600 truncate">
                                                {{ $lead->name }}
                                            </a>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $lead->temperature->badgeClasses() }}">
                                                {{ $lead->temperature->label() }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-3">
                                            <span class="font-medium text-slate-700">📞 {{ $lead->mobile }}</span>
                                            <span class="text-rose-600 font-bold bg-rose-50 px-1.5 py-0.2 rounded text-[10px]">
                                                Overdue {{ $lead->next_action_at?->diffForHumans() }}
                                            </span>
                                        </div>
                                        @if ($lead->next_action_type)
                                            <div class="text-[11px] font-semibold text-slate-600 mt-0.5">
                                                Action: <span class="text-orange-600 font-bold">{{ $lead->next_action_type }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                                    @if($cleanWa)
                                    <a href="https://wa.me/{{ $cleanWa }}" target="_blank" title="WhatsApp Message" 
                                       class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors text-xs font-bold flex items-center gap-1">
                                        <span>💬</span>
                                    </a>
                                    @endif
                                    <a href="tel:{{ $lead->mobile }}" title="Phone Call" 
                                       class="p-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors text-xs font-bold flex items-center gap-1">
                                        <span>📞</span>
                                    </a>
                                    <a href="{{ route('leads.show', $lead->id) }}" class="px-3 py-1.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-xs transition-all active:scale-95">
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
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>✓</span> Today's Scheduled Tasks
                    </h3>
                    <a href="{{ route('tasks.index') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">View All Tasks →</a>
                </div>

                @if ($todayTasks->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <span class="text-2xl block mb-1">📅</span>
                        <span class="font-semibold text-slate-600">No pending tasks scheduled for today yet.</span>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($todayTasks as $task)
                            <div data-task-id="{{ $task->id }}" class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $task->priority->badgeClasses() }} flex-shrink-0">
                                        {{ $task->priority->value }}
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-sm font-bold text-slate-900 truncate">{{ $task->title }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-2">
                                            <span class="font-semibold text-slate-700">{{ $task->type->value }}</span>
                                            @if ($task->lead)
                                                <span>•</span>
                                                <a href="{{ route('leads.show', $task->lead->id) }}" class="text-orange-600 hover:underline font-bold truncate">
                                                    {{ $task->lead->name }}
                                                </a>
                                            @endif
                                            <span>• Due {{ $task->due_at->format('h:i A') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <form action="{{ route('tasks.complete', $task->id) }}" method="POST" class="flex-shrink-0">
                                    @csrf
                                    <input type="hidden" name="outcome" value="Completed successfully as planned">
                                    <button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-xs font-bold text-slate-700 transition-colors shadow-2xs">
                                        ✓ Done
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

            <!-- High Priority Leads (Hot Leads) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                        <span>🔥</span> Hot Priority Leads
                    </h3>
                    <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full border border-orange-200">
                        {{ $hotLeads->count() }} hot
                    </span>
                </div>

                @if ($hotLeads->isEmpty())
                    <div class="py-6 text-center text-slate-400 text-xs">
                        No leads scored as Hot (80+) yet.
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach ($hotLeads as $lead)
                            <a data-lead-id="{{ $lead->id }}" href="{{ route('leads.show', $lead->id) }}" 
                               class="block p-3 rounded-xl border border-slate-100 hover:border-orange-300 hover:bg-orange-50/30 transition-all shadow-2xs group">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900 group-hover:text-orange-700 truncate">{{ $lead->name }}</span>
                                    <span class="px-2 py-0.5 text-[10px] font-black rounded-md bg-orange-600 text-white shadow-2xs">
                                        {{ $lead->score }} pts
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                                    <span class="font-medium text-slate-600">{{ $lead->stage->label() }}</span>
                                    <span>{{ $lead->mobile }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Stale Leads Alert -->
            @if ($staleLeads->isNotEmpty())
                <div class="bg-white rounded-2xl border border-purple-200 shadow-xs p-5 bg-purple-50/10">
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-purple-100">
                        <h3 class="text-sm font-bold text-purple-900 flex items-center gap-1.5">
                            <span>⏳</span> Stale Leads Alert
                        </h3>
                        <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full">
                            >7d inactive
                        </span>
                    </div>

                    <div class="space-y-2">
                        @foreach ($staleLeads as $lead)
                            <div data-lead-id="{{ $lead->id }}" class="flex items-center justify-between p-2.5 bg-white border border-purple-100 rounded-xl text-xs shadow-2xs">
                                <div>
                                    <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-slate-900 hover:text-purple-700 block">
                                        {{ $lead->name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400">Last contact: {{ $lead->last_contact_at?->diffForHumans() ?? 'None' }}</div>
                                </div>
                                <a href="{{ route('leads.show', $lead->id) }}" class="text-[11px] font-bold text-purple-700 hover:underline">
                                    Re-engage →
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recent Timeline Activity Stream -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                        <span>⚡</span> Recent Activity Stream
                    </h3>
                </div>
                <div class="space-y-3">
                    @forelse ($recentActivities as $activity)
                        <div class="flex items-start gap-2.5 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-orange-500 mt-1 flex-shrink-0 ring-4 ring-orange-100"></span>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-800 truncate">{{ $activity->title }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 truncate">
                                    @if ($activity->lead)
                                        <a href="{{ route('leads.show', $activity->lead->id) }}" class="text-orange-600 font-semibold hover:underline">{{ $activity->lead->name }}</a> •
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

    @endcan
</div>
@endsection
