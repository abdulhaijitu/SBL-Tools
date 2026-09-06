@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Operations Overview & Daily Action Priorities')

@section('content')
<div class="space-y-6">

    <!-- ==================== 1. WELCOME HERO & QUICK ACTIONS ==================== -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 rounded-2xl p-5 md:p-6 border border-slate-800 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-500/20 text-orange-400 border border-orange-500/30">
                        ⚡ Daily Command Center
                    </span>
                    <span class="text-xs text-slate-400 font-medium">
                        {{ now()->format('l, d M Y') }}
                    </span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-white tracking-tight">
                    Welcome back, <span class="text-orange-400">{{ Auth::user()->name ?? 'Leader' }}</span> 👋
                </h1>
                <p class="text-xs md:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Here is your real-time overview of CRM follow-ups, presentations, funnel conversions, and Team Explorer direct placements.
                </p>
            </div>

            <!-- Quick Action Shortcut Pills -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('leads.create') }}" 
                   class="px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold transition-all shadow-md shadow-orange-600/30 hover:scale-105 active:scale-95 flex items-center gap-1.5">
                    <span>+</span> <span>Add New Lead</span>
                </a>
                <a href="{{ route('team.index') }}" 
                   class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-orange-300 hover:text-white border border-slate-700 text-xs font-bold transition-all shadow-sm hover:scale-105 active:scale-95 flex items-center gap-1.5">
                    <span>👥</span> <span>Team Explorer</span>
                </a>
                <a href="{{ route('presentations.index') }}" 
                   class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-purple-300 hover:text-white border border-slate-700 text-xs font-bold transition-all shadow-sm hover:scale-105 active:scale-95 flex items-center gap-1.5">
                    <span>🎤</span> <span>Presentation</span>
                </a>
            </div>
        </div>

        <!-- Subtle Background Glow Elements -->
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-orange-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -top-10 w-40 h-40 bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- ==================== 2. KEY PERFORMANCE INDICATORS (KPIs) ==================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- 1. Follow-ups Due Today -->
        <a href="{{ route('tasks.index') }}" class="group bg-white rounded-2xl p-4 md:p-5 border border-slate-200/80 shadow-xs hover:border-orange-300 hover:shadow-md transition-all relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Follow-ups Today</span>
                    <span data-metric="followups-today" class="text-2xl md:text-3xl font-black text-slate-900 mt-1 block group-hover:text-orange-600 transition-colors">
                        {{ $followupsDueToday->count() }}
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-xl group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Scheduled for today</span>
                <span class="text-orange-600 font-bold group-hover:translate-x-0.5 transition-transform">View Tasks →</span>
            </div>
            <div class="absolute top-0 inset-x-0 h-1 bg-orange-500 rounded-t-2xl"></div>
        </a>

        <!-- 2. Overdue Follow-ups (Alert) -->
        <a href="{{ route('leads.index') }}" class="group bg-white rounded-2xl p-4 md:p-5 border {{ $overdueFollowups->count() > 0 ? 'border-rose-200 bg-rose-50/10' : 'border-slate-200/80' }} shadow-xs hover:border-rose-300 hover:shadow-md transition-all relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold {{ $overdueFollowups->count() > 0 ? 'text-rose-600' : 'text-slate-500' }} uppercase tracking-wider block">Overdue Follow-ups</span>
                    <span data-metric="overdue-followups" class="text-2xl md:text-3xl font-black {{ $overdueFollowups->count() > 0 ? 'text-rose-700' : 'text-slate-900' }} mt-1 block">
                        {{ $overdueFollowups->count() }}
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl {{ $overdueFollowups->count() > 0 ? 'bg-rose-100 text-rose-600 animate-pulse' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-bold text-xl group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t {{ $overdueFollowups->count() > 0 ? 'border-rose-100' : 'border-slate-100' }} flex items-center justify-between text-[11px] {{ $overdueFollowups->count() > 0 ? 'text-rose-600 font-semibold' : 'text-slate-500' }}">
                <span>{{ $overdueFollowups->count() > 0 ? 'Urgent attention needed' : 'All caught up' }}</span>
                <span class="font-bold group-hover:translate-x-0.5 transition-transform">Action list →</span>
            </div>
            <div class="absolute top-0 inset-x-0 h-1 {{ $overdueFollowups->count() > 0 ? 'bg-rose-500' : 'bg-slate-300' }} rounded-t-2xl"></div>
        </a>

        <!-- 3. Presentations Today -->
        <a href="{{ route('presentations.index') }}" class="group bg-white rounded-2xl p-4 md:p-5 border border-slate-200/80 shadow-xs hover:border-purple-300 hover:shadow-md transition-all relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Presentations Today</span>
                    <span data-metric="presentations-today" class="text-2xl md:text-3xl font-black text-slate-900 mt-1 block group-hover:text-purple-600 transition-colors">
                        {{ $presentationsToday->count() }}
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>1-on-1 & Group sessions</span>
                <span class="text-purple-600 font-bold group-hover:translate-x-0.5 transition-transform">Pitch deck →</span>
            </div>
            <div class="absolute top-0 inset-x-0 h-1 bg-purple-500 rounded-t-2xl"></div>
        </a>

        <!-- 4. Total Active Leads / Pipeline Base -->
        <a href="{{ route('leads.index') }}" class="group bg-white rounded-2xl p-4 md:p-5 border border-slate-200/80 shadow-xs hover:border-blue-300 hover:shadow-md transition-all relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Active Leads</span>
                    <span data-metric="total-leads" class="text-2xl md:text-3xl font-black text-slate-900 mt-1 block group-hover:text-blue-600 transition-colors">
                        {{ $totalLeads }}
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xl group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span><span data-metric="new-leads-today" class="font-bold text-emerald-600">+{{ $newLeadsTodayCount }}</span> added today</span>
                <span class="text-blue-600 font-bold group-hover:translate-x-0.5 transition-transform">CRM Hub →</span>
            </div>
            <div class="absolute top-0 inset-x-0 h-1 bg-blue-500 rounded-t-2xl"></div>
        </a>

    </div>

    <!-- ==================== 3. 5L + 5R TEAM EXPLORER SUMMARY WIDGET ==================== -->
    @if(isset($teamRoot))
    @php
        $directChildren = $teamRoot->children ?? collect();
        $directLeft = $directChildren->where('branch', 'LEFT')->where('is_target', false)->count();
        $directRight = $directChildren->where('branch', 'RIGHT')->where('is_target', false)->count();
        $directTotal = $directLeft + $directRight;
        $isFmeQualified = ($directLeft >= 5 && $directRight >= 5);
        $fmePercent = min(100, (int)(($directTotal / 10) * 100));
    @endphp
    <div class="bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 rounded-2xl p-5 border border-slate-800 text-white shadow-xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-orange-600/20 text-orange-400 border border-orange-500/30 flex items-center justify-center text-xl font-bold">
                    👥
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-black text-white">SBL Team Placement & FME Status</h3>
                        @if($isFmeQualified)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            ⭐ FME QUALIFIED
                        </span>
                        @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                            🎯 FME IN PROGRESS
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Root Leader: <span class="text-slate-200 font-semibold">{{ $teamRoot->member_name }}</span> ({{ $teamRoot->member_code ?? 'SBL-ROOT' }}) • Sponsor: <span class="text-slate-300 font-medium">{{ $teamRoot->sponsor_name ?? 'Md. Samim' }}</span>
                    </p>
                </div>
            </div>

            <a href="{{ route('team.index') }}" 
               class="px-4 py-2 bg-orange-600 hover:bg-orange-500 text-white text-xs font-black rounded-xl transition-all shadow-md shadow-orange-600/30 hover:scale-105 active:scale-95 flex items-center gap-1.5 self-end md:self-center">
                <span>👥 Open Team Explorer</span>
                <span>→</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4">
            <!-- Direct Team Count -->
            <div class="bg-slate-800/80 rounded-xl p-3 border border-slate-700/60">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Direct Team</span>
                <span class="text-xl font-black text-orange-400 mt-0.5 block">{{ $directTotal }} / 10</span>
                <span class="text-[10px] text-slate-400">Left: {{ $directLeft }}/5 • Right: {{ $directRight }}/5</span>
            </div>

            <!-- Left Team Network -->
            <div class="bg-slate-800/80 rounded-xl p-3 border border-slate-700/60">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Left Network</span>
                <span class="text-xl font-black text-white mt-0.5 block">{{ $teamRoot->left_count ?? 0 }} Members</span>
                <span class="text-[10px] text-slate-400">Volume: {{ number_format((float)($teamRoot->left_bv ?? 0)) }} BV</span>
            </div>

            <!-- Right Team Network -->
            <div class="bg-slate-800/80 rounded-xl p-3 border border-slate-700/60">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Right Network</span>
                <span class="text-xl font-black text-white mt-0.5 block">{{ $teamRoot->right_count ?? 0 }} Members</span>
                <span class="text-[10px] text-slate-400">Volume: {{ number_format((float)($teamRoot->right_bv ?? 0)) }} BV</span>
            </div>

            <!-- Matched Pairs / Rank -->
            <div class="bg-slate-800/80 rounded-xl p-3 border border-slate-700/60">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Matched Pairs</span>
                <span class="text-xl font-black text-emerald-400 mt-0.5 block">{{ $teamRoot->matched_pairs ?? 0 }} Pairs</span>
                <span class="text-[10px] text-slate-400">Rank: {{ $teamRoot->rank_name ?? 'Founder' }}</span>
            </div>
        </div>

        <!-- FME Progress Bar -->
        <div class="mt-4 pt-3 border-t border-slate-800">
            <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="text-slate-300 font-semibold flex items-center gap-1.5">
                    <span>🏆</span> FME Qualification Progress (5 Left + 5 Right Direct Placements)
                </span>
                <span class="font-bold text-orange-400">{{ $fmePercent }}% Complete</span>
            </div>
            <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                <div class="bg-gradient-to-r from-orange-500 to-emerald-500 h-full rounded-full transition-all duration-700" style="width: {{ $fmePercent }}%"></div>
            </div>
        </div>
    </div>
    @endif

    <!-- ==================== 4. LEAD PIPELINE CONVERSION FUNNEL ==================== -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-4">
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
                        <span class="text-[11px] font-bold text-slate-500 group-hover:text-orange-700 uppercase tracking-tight block truncate">
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
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
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
                                $cleanWa = preg_replace('/[^0-9]/', '', $lead->whatsapp ?: $lead->mobile);
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

</div>
@endsection
