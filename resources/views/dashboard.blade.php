@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Operations Overview & Daily Action Priorities')

@section('content')
<div class="space-y-6">

    <!-- 1. COMPACT GREETING & DATE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <p id="dashboard-current-date" class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500" 
               data-en="{{ now()->format('l, j F Y') }}" 
               data-bn="{{ now()->locale('bn')->translatedFormat('l, j F Y') }}">
                {{ now()->format('l, j F Y') }}
            </p>
            <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight mt-0.5">
                <span data-en="Welcome back," data-bn="স্বাগতম,">Welcome back,</span> {{ Auth::user()->name }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5" 
               data-en="Here’s what needs your attention today." 
               data-bn="আজকের প্রয়োজনীয় অ্যাকশন ও গুরুত্বপূর্ণ আপডেট।">
                Here’s what needs your attention today.
            </p>
        </div>
        @can('reports.view')
        <div class="self-start sm:self-center flex-shrink-0">
            <a href="{{ route('reports.index') }}" class="btn-secondary text-xs sm:text-sm py-2 px-3.5 inline-flex items-center gap-1.5 shadow-2xs">
                <span>📊</span>
                <span data-en="Activity Reports" data-bn="অ্যাক্টিভিটি রিপোর্টস">Activity Reports</span>
            </a>
        </div>
        @endcan
    </div>

    @can('leads.view')
    <!-- 2. TOP 4 KPI CARDS (2x2 Grid on Mobile, 4 Columns on Desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- KPI 1: Active Leads -->
        <a href="{{ route('leads.index', ['status' => 'active']) }}" 
           class="bg-white hover:bg-orange-50/40 p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:border-orange-300 transition-all flex flex-col justify-between group active:scale-98">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 group-hover:text-orange-700 uppercase tracking-wider" 
                      data-en="Total Active Leads" data-bn="মোট অ্যাক্টিভ লিডস">Total Active Leads</span>
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
            </div>
            <div class="mt-2">
                <strong id="active-leads" class="text-2xl sm:text-3xl font-black text-slate-900 group-hover:text-orange-600 tracking-tight block">
                    {{ $totalActiveLeads }}
                </strong>
                <small class="text-[11px] font-semibold text-emerald-600 block mt-0.5">
                    +{{ $newLeadsTodayCount }} <span data-en="added today" data-bn="আজ যুক্ত">added today</span>
                </small>
            </div>
        </a>

        <!-- KPI 2: Follow-ups Due -->
        <a href="{{ route('leads.index', ['filter' => 'due_today']) }}" 
           class="bg-white hover:bg-orange-50/40 p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:border-orange-300 transition-all flex flex-col justify-between group active:scale-98">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 group-hover:text-orange-700 uppercase tracking-wider" 
                      data-en="Today Followup" data-bn="আজকের ফলো-আপ">Today Followup</span>
                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
            </div>
            <div class="mt-2">
                <strong id="today-followup" class="text-2xl sm:text-3xl font-black text-orange-600 tracking-tight block">
                    {{ $followupsDueToday->count() }}
                </strong>
                <small class="text-[11px] font-bold text-rose-600 block mt-0.5">
                    {{ $overdueFollowups->count() }} <span data-en="overdue action(s)" data-bn="অভারডিউ অ্যাকশন">overdue action(s)</span>
                </small>
            </div>
        </a>

        <!-- KPI 3: Today's Tasks -->
        <a href="{{ route('tasks.index', ['filter' => 'today']) }}" 
           class="bg-white hover:bg-orange-50/40 p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:border-orange-300 transition-all flex flex-col justify-between group active:scale-98">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 group-hover:text-orange-700 uppercase tracking-wider" 
                      data-en="Today's Tasks" data-bn="আজকের টাস্ক">Today's Tasks</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="mt-2">
                <strong id="today-tasks-kpi" class="text-2xl sm:text-3xl font-black text-slate-900 group-hover:text-orange-600 tracking-tight block">
                    {{ $todayTasks->count() }}
                </strong>
                <small class="text-[11px] font-medium text-slate-400 block mt-0.5" 
                       data-en="Pending today" data-bn="অপেক্ষারত নির্ধারিত">
                    Pending today
                </small>
            </div>
        </a>

        <!-- KPI 4: Presentations -->
        <a href="{{ route('presentations.index') }}" 
           class="bg-white hover:bg-orange-50/40 p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:border-orange-300 transition-all flex flex-col justify-between group active:scale-98">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 group-hover:text-orange-700 uppercase tracking-wider" 
                      data-en="Total Presentations" data-bn="মোট প্রেজেন্টেশন">Total Presentations</span>
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
            </div>
            <div class="mt-2">
                <strong id="total-presentations" class="text-2xl sm:text-3xl font-black text-purple-600 tracking-tight block">
                    {{ $presentationsThisMonthCount }}
                </strong>
                <small class="text-[11px] font-medium text-purple-600/80 block mt-0.5">
                    <span data-en="This month" data-bn="এই মাসে">This month</span> ({{ $totalPresentations }} <span data-en="total" data-bn="মোট">total</span>)
                </small>
            </div>
        </a>
    </div>

    <!-- 3. COMPACT QUICK ACTIONS BAR (Max 4 Actions) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
        <a href="{{ route('leads.create') }}" 
           class="flex items-center justify-center gap-2 p-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-bold text-xs shadow-xs transition-all active:scale-98">
            <span class="text-base leading-none">+</span>
            <span data-en="New Lead" data-bn="নতুন লিড">New Lead</span>
        </a>
        <a href="{{ route('tasks.index') }}" 
           class="flex items-center justify-center gap-2 p-3 bg-white hover:bg-slate-50 border border-slate-200/90 text-slate-800 rounded-xl font-bold text-xs shadow-2xs transition-all active:scale-98">
            <span class="text-emerald-600 font-black">+</span>
            <span data-en="Add Task" data-bn="টাস্ক যোগ করুন">Add Task</span>
        </a>
        <a href="{{ route('presentations.index') }}" 
           class="flex items-center justify-center gap-2 p-3 bg-white hover:bg-slate-50 border border-slate-200/90 text-slate-800 rounded-xl font-bold text-xs shadow-2xs transition-all active:scale-98">
            <span class="text-purple-600 font-black">▶</span>
            <span data-en="Presentation" data-bn="প্রেজেন্টেশন">Presentation</span>
        </a>
        <a href="{{ route('team.index') }}" 
           class="flex items-center justify-center gap-2 p-3 bg-white hover:bg-slate-50 border border-slate-200/90 text-slate-800 rounded-xl font-bold text-xs shadow-2xs transition-all active:scale-98">
            <span class="text-blue-600 font-black">👥</span>
            <span data-en="Explore Team" data-bn="টিম এক্সপ্লোর">Explore Team</span>
        </a>
    </div>

    <!-- 4. ACTION PRIORITIES: NEEDS ATTENTION & TODAY'S TASKS (2-Column Split on Desktop) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

        <!-- A. NEEDS ATTENTION CARD (Highest Priority) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                    <h2 class="text-sm font-bold text-slate-900" 
                        data-en="Needs Attention Today" 
                        data-bn="জরুরি মনোযোগ প্রয়োজন">
                        Needs Attention Today
                    </h2>
                </div>
                <div class="flex items-center gap-2">
                    <span id="dashboard-attention-badge" class="text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-200">
                        {{ $totalAttentionCount }} <span data-en="items" data-bn="আইটেম">items</span>
                    </span>
                    <span id="dashboard-overdue-badge" class="hidden">{{ $overdueFollowups->count() }} Overdue</span>
                    @if($totalAttentionCount > 5)
                    <a href="{{ route('leads.index', ['filter' => 'due_today']) }}" class="text-xs font-bold text-orange-600 hover:underline">
                        <span data-en="View All" data-bn="সব দেখুন">View All</span> &rarr;
                    </a>
                    @endif
                </div>
            </div>

            <!-- Empty State Placeholder -->
            <div id="dashboard-overdue-empty" class="p-8 text-center text-slate-400 text-xs my-auto" 
                 @if($needsAttention->isNotEmpty()) style="display: none;" @endif>
                <span class="text-3xl block mb-2">🎉</span>
                <span class="font-bold text-slate-700 text-sm block" 
                      data-en="You're all caught up!" 
                      data-bn="দারুণ! আপনি আপ-টু-ডেট আছেন!">
                    You're all caught up!
                </span>
                <span class="text-slate-400 mt-1 block" 
                      data-en="No overdue follow-ups or urgent tasks right now." 
                      data-bn="এই মুহূর্তে কোনো ওভারডিউ ফলো-আপ বা জরুরি টাস্ক বাকি নেই।">
                    No overdue follow-ups or urgent tasks right now.
                </span>
            </div>

            <!-- Urgent Attention List Container -->
            <div id="dashboard-overdue-container" class="divide-y divide-slate-100" 
                 @if($needsAttention->isEmpty()) style="display: none;" @endif>
                @foreach ($needsAttention as $item)
                    @php
                        $lead = $item['lead'];
                        $task = $item['task'];
                        $cleanWa = \App\Support\PhoneNumber::whatsapp($lead->whatsapp ?: $lead->mobile);
                        $isOverdue = $item['urgency'] === 'overdue';
                    @endphp
                    <div data-lead-id="{{ $lead->id }}" class="p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/70 transition-colors">
                        <div class="flex items-start gap-3 min-w-0">
                            <!-- Avatar -->
                            <div class="w-10 h-10 rounded-xl {{ $isOverdue ? 'bg-rose-100 text-rose-700' : 'bg-orange-100 text-orange-700' }} font-black flex items-center justify-center text-sm flex-shrink-0 shadow-2xs">
                                {{ mb_substr($lead->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-sm text-slate-900 hover:text-orange-600 truncate">
                                        {{ $lead->name }}
                                    </a>
                                    <span class="text-[11px] font-mono text-slate-400">#{{ $lead->id }}</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $lead->temperature->badgeClasses() }}">
                                        {{ $lead->temperature->label() }}
                                    </span>
                                    @if($lead->score > 0)
                                    <span class="px-1.5 py-0.2 text-[10px] font-bold rounded bg-slate-100 text-slate-600" title="Lead Score">
                                        {{ $lead->score }} pts
                                    </span>
                                    @endif
                                </div>

                                <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-slate-700">📞 {{ $lead->mobile }}</span>
                                    <span>•</span>
                                    @if($isOverdue)
                                    <span class="text-rose-600 font-bold bg-rose-50 px-1.5 py-0.2 rounded text-[10px]">
                                        {{ $item['badge_text'] }}
                                    </span>
                                    @else
                                    <span class="text-orange-600 font-bold bg-orange-50 px-1.5 py-0.2 rounded text-[10px]">
                                        {{ $item['badge_text'] }}
                                    </span>
                                    @endif
                                </div>

                                <div class="text-[11px] font-medium text-slate-600 mt-0.5 truncate">
                                    <span data-en="Action:" data-bn="অ্যাকশন:">Action:</span> 
                                    <span class="text-orange-600 font-bold">{{ $item['title'] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons (Min 44px touch targets on mobile) -->
                        <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0 mt-1 sm:mt-0">
                            @if($cleanWa)
                            <a href="https://wa.me/{{ $cleanWa }}" target="_blank" title="WhatsApp Message" aria-label="WhatsApp {{ $lead->name }}"
                               class="w-9 h-9 sm:w-8 sm:h-8 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors flex items-center justify-center font-bold shadow-2xs">
                                <span class="text-sm">💬</span>
                            </a>
                            @endif
                            <a href="tel:{{ $lead->mobile }}" title="Phone Call" aria-label="Call {{ $lead->name }}"
                               class="w-9 h-9 sm:w-8 sm:h-8 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors flex items-center justify-center font-bold shadow-2xs">
                                <span class="text-sm">📞</span>
                            </a>
                            <a href="{{ route('leads.show', $lead->id) }}" 
                               class="px-3 py-2 sm:py-1.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-2xs transition-all active:scale-95 flex items-center gap-1">
                                <span data-en="Take Action" data-bn="অ্যাকশন নিন">Take Action</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- B. TODAY'S SCHEDULED TASKS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <span class="text-emerald-600 font-black">✓</span>
                    <h2 class="text-sm font-bold text-slate-900" 
                        data-en="Today's Scheduled Tasks" 
                        data-bn="আজকের নির্ধারিত টাস্ক">
                        Today's Scheduled Tasks
                    </h2>
                </div>
                <a href="{{ route('tasks.index') }}" class="text-xs font-bold text-orange-600 hover:underline">
                    <span data-en="View All Tasks" data-bn="সব টাস্ক দেখুন">View All Tasks</span> &rarr;
                </a>
            </div>

            <!-- Empty State Placeholder -->
            <div id="dashboard-tasks-empty" class="p-8 text-center text-slate-400 text-xs my-auto" 
                 @if($todayTasks->isNotEmpty()) style="display: none;" @endif>
                <span class="text-3xl block mb-2">📅</span>
                <span class="font-bold text-slate-700 text-sm block" 
                      data-en="No tasks scheduled for today." 
                      data-bn="আজকের কোনো নির্ধারিত টাস্ক নেই।">
                    No tasks scheduled for today.
                </span>
                <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1 mt-3 px-3 py-1.5 rounded-xl bg-orange-50 text-orange-600 font-bold text-xs hover:bg-orange-100">
                    <span>+</span> <span data-en="Add New Task" data-bn="নতুন টাস্ক যোগ করুন">Add New Task</span>
                </a>
            </div>

            <!-- Tasks List Container -->
            <div id="dashboard-tasks-container" class="divide-y divide-slate-100" 
                 @if($todayTasks->isEmpty()) style="display: none;" @endif>
                @foreach ($todayTasks as $task)
                    <div data-task-id="{{ $task->id }}" class="p-3.5 sm:p-4 flex items-center justify-between gap-3 hover:bg-slate-50/70 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $task->priority->badgeClasses() }} flex-shrink-0">
                                {{ $task->priority->value }}
                            </span>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-slate-900 truncate">{{ $task->title }}</div>
                                <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-slate-700">{{ $task->type->value }}</span>
                                    @if ($task->lead)
                                        <span>•</span>
                                        <a href="{{ route('leads.show', $task->lead->id) }}" class="text-orange-600 hover:underline font-bold truncate">
                                            {{ $task->lead->name }} <span class="text-slate-400 font-normal">#{{ $task->lead->id }}</span>
                                        </a>
                                    @endif
                                    @if($task->due_at)
                                        <span>• <span data-en="Due" data-bn="সময়:">Due</span> {{ $task->due_at->format('h:i A') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- 1-Click Fast Complete -->
                        <form action="{{ route('tasks.complete', $task->id) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <input type="hidden" name="outcome" value="Completed successfully as planned">
                            <button type="submit" 
                                    class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-xs font-bold text-slate-700 transition-colors shadow-2xs active:scale-95">
                                ✓ <span data-en="Done" data-bn="সম্পন্ন">Done</span>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- 5. TEAM EXPLORER COMPACT SUMMARY -->
    @if($teamRoot)
    <div class="bg-gradient-to-r from-orange-500/10 via-amber-500/5 to-transparent rounded-2xl p-4 sm:p-5 border border-orange-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-orange-600 text-white font-black flex items-center justify-center text-lg shadow-xs flex-shrink-0">
                👥
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold uppercase tracking-wider text-orange-600" 
                          data-en="Team Explorer" data-bn="টিম এক্সপ্লোরার">Team Explorer</span>
                    <span class="text-xs text-slate-300">•</span>
                    <strong class="text-sm font-black text-slate-900">{{ $teamRoot->member_name }}</strong>
                </div>
                <div class="text-xs text-slate-600 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span class="font-semibold">
                        <span data-en="Direct Team:" data-bn="ডিরেক্ট টিম:">Direct Team:</span> 
                        <strong class="text-slate-900">{{ $directTeamSummary['total_direct'] }} / 10 <span data-en="Filled" data-bn="পূর্ণ">Filled</span></strong>
                    </span>
                    <span>
                        <span data-en="Left Team:" data-bn="লেফট টিম:">Left Team:</span> 
                        <strong class="text-blue-700">{{ $directTeamSummary['left_count'] }}/5</strong>
                    </span>
                    <span>
                        <span data-en="Right Team:" data-bn="রাইট টিম:">Right Team:</span> 
                        <strong class="text-emerald-700">{{ $directTeamSummary['right_count'] }}/5</strong>
                    </span>
                    @if($directTeamSummary['left_bv'] > 0 || $directTeamSummary['right_bv'] > 0)
                    <span class="text-slate-400 font-mono text-[11px]">
                        BV: L {{ number_format($directTeamSummary['left_bv'], 0) }} | R {{ number_format($directTeamSummary['right_bv'], 0) }}
                    </span>
                    @endif
                </div>
                <div class="text-[11px] font-semibold text-slate-500 mt-1">
                    <span data-en="Suggested Placement:" data-bn="প্রস্তাবিত প্লেসমেন্ট:">Suggested Placement:</span> 
                    <span class="text-orange-700 font-bold bg-orange-100/80 px-1.5 py-0.2 rounded">{{ $directTeamSummary['suggested_placement'] }}</span>
                    <span class="text-slate-400" data-en="(for binary balance)" data-bn="(ব্যালান্সের জন্য)">(for binary balance)</span>
                </div>
            </div>
        </div>

        <div class="self-end md:self-center flex-shrink-0">
            <a href="{{ route('team.index') }}" 
               class="btn-primary text-xs sm:text-sm py-2 px-4 shadow-xs flex items-center gap-1.5 active:scale-95">
                <span data-en="Explore Team" data-bn="টিম দেখুন">Explore Team</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>
    @endif

    <!-- 6. LEAD PIPELINE FUNNEL (Mobile Horizontally Scrollable, Desktop 8-Col Grid) -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>📊</span> 
                    <span data-en="Lead Pipeline Funnel" data-bn="লিড পাইপলাইন ফানেল">Lead Pipeline Funnel</span>
                </h3>
                <p class="text-xs text-slate-500" 
                   data-en="Distribution of active leads across sales lifecycle stages" 
                   data-bn="বিক্রয় চক্রের বিভিন্ন স্টেজে অ্যাক্টিভ লিডের বিন্যাস">
                    Distribution of active leads across sales lifecycle stages
                </p>
            </div>
            <a href="{{ route('leads.index', ['view' => 'kanban']) }}" 
               class="text-xs font-bold text-orange-600 hover:text-orange-700 flex items-center gap-1 transition-transform hover:translate-x-0.5">
                <span data-en="View Kanban Board" data-bn="কানবান বোর্ড দেখুন">View Kanban Board</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <!-- Scrollable on mobile, Grid on desktop -->
        <div class="flex overflow-x-auto sm:grid sm:grid-cols-4 lg:grid-cols-8 gap-2.5 pb-2 sm:pb-0 snap-x snap-mandatory">
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
                $stageBnMap = [
                    'new' => 'নতুন',
                    'contacted' => 'যোগাযোগকৃত',
                    'interested' => 'আগ্রহী',
                    'qualified' => 'যোগ্য',
                    'presentation' => 'প্রেজেন্টেশন',
                    'follow_up' => 'ফলো-আপ',
                    'decision' => 'সিদ্ধান্ত',
                    'converted' => 'কনভার্টেড',
                ];
            @endphp
            @foreach ($funnelStages as $stageKey => $count)
                @php
                    $pct = $totalLeads > 0 ? min(100, round(($count / $totalLeads) * 100)) : 0;
                    $barGradient = $stageColorMap[$stageKey] ?? 'from-orange-500 to-orange-600';
                    $stageBn = $stageBnMap[$stageKey] ?? $stageKey;
                @endphp
                <a href="{{ route('leads.index', ['stage' => $stageKey]) }}" 
                   data-funnel-stage="{{ $stageKey }}"
                   class="min-w-[120px] sm:min-w-0 flex-1 snap-start bg-slate-50 hover:bg-orange-50/50 hover:border-orange-200 border border-slate-200/60 rounded-xl p-3 text-center transition-all group active:scale-95 flex flex-col justify-between shadow-2xs hover:shadow-xs">
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 group-hover:text-orange-700 tracking-tight block whitespace-normal min-h-8"
                              data-en="{{ ucfirst(str_replace('_', ' ', $stageKey)) }}"
                              data-bn="{{ $stageBn }}">
                            {{ ucfirst(str_replace('_', ' ', $stageKey)) }}
                        </span>
                        <span data-funnel-count="{{ $stageKey }}" class="text-xl font-black text-slate-900 group-hover:text-orange-600 mt-1 block">
                            {{ $count }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-slate-200/80 h-1.5 rounded-full overflow-hidden">
                            <div data-funnel-bar="{{ $stageKey }}" class="bg-gradient-to-r {{ $barGradient }} h-full rounded-full transition-all duration-500" 
                                 style="width: {{ $pct }}%">
                            </div>
                        </div>
                        <span data-funnel-pct="{{ $stageKey }}" class="text-[9px] text-slate-400 font-semibold mt-1 block">{{ $pct }}% share</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- 7. PRIORITIES & RECENT ACTIVITY (3-Column Split on Large Screens) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-start">

        <!-- A. HOT PRIORITY LEADS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-5 flex flex-col">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                    <span>🔥</span> 
                    <span data-en="Hot Priority Leads" data-bn="হট প্রায়োরিটি লিডস">Hot Priority Leads</span>
                </h3>
                <span id="dashboard-hot-badge" class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full border border-orange-200">
                    {{ $hotLeads->count() }} <span data-en="hot" data-bn="হট">hot</span>
                </span>
            </div>

            <div id="dashboard-hot-empty" class="py-6 text-center text-slate-400 text-xs my-auto" 
                 @if($hotLeads->isNotEmpty()) style="display: none;" @endif>
                <span data-en="No leads scored as Hot (80+) yet." data-bn="এখনও কোনো হট লিড (৮০+ স্কোর) নেই।">
                    No leads scored as Hot (80+) yet.
                </span>
            </div>

            <div id="dashboard-hot-container" class="space-y-2" 
                 @if($hotLeads->isEmpty()) style="display: none;" @endif>
                @foreach ($hotLeads as $lead)
                    @php
                        $cleanWa = \App\Support\PhoneNumber::whatsapp($lead->whatsapp ?: $lead->mobile);
                    @endphp
                    <div data-lead-id="{{ $lead->id }}" 
                         class="p-2.5 rounded-xl border border-slate-100 hover:border-orange-300 hover:bg-orange-50/20 transition-all shadow-2xs group flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('leads.show', $lead->id) }}" class="text-xs font-bold text-slate-900 group-hover:text-orange-700 truncate">
                                    {{ $lead->name }}
                                </a>
                                <span class="font-mono text-[10px] text-slate-400">#{{ $lead->id }}</span>
                            </div>
                            <div class="text-[10px] text-slate-500 mt-0.5 flex items-center gap-2">
                                <span class="font-medium text-slate-600">{{ $lead->stage->label() }}</span>
                                <span>•</span>
                                <span>{{ $lead->mobile }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <span class="px-1.5 py-0.5 text-[10px] font-black rounded-md bg-orange-600 text-white shadow-2xs" title="Lead priority score">
                                {{ $lead->score }} pts
                            </span>
                            @if($cleanWa)
                            <a href="https://wa.me/{{ $cleanWa }}" target="_blank" title="WhatsApp Message" aria-label="WhatsApp {{ $lead->name }}"
                               class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors flex items-center justify-center font-bold text-xs">
                                💬
                            </a>
                            @endif
                            <a href="{{ route('leads.show', $lead->id) }}" title="Open Lead"
                               class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-orange-100 hover:text-orange-700 transition-colors flex items-center justify-center font-bold text-xs text-slate-600">
                                &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- B. NEEDS RE-ENGAGEMENT (Formerly Stale Leads) -->
        <div class="bg-white rounded-2xl border border-purple-200/90 shadow-xs p-4 sm:p-5 bg-purple-50/10 flex flex-col">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-purple-100">
                <h3 class="text-sm font-bold text-purple-900 flex items-center gap-1.5">
                    <span>⏳</span> 
                    <span data-en="Needs Re-engagement" data-bn="পুনঃযোগাযোগ প্রয়োজন">Needs Re-engagement</span>
                </h3>
                <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full"
                      data-en=">7d inactive" data-bn=">৭ দিন নিষ্ক্রিয়">
                    >7d inactive
                </span>
            </div>

            <div id="dashboard-stale-empty" class="py-6 text-center text-purple-400 text-xs my-auto" 
                 @if($staleLeads->isNotEmpty()) style="display: none;" @endif>
                <span data-en="No leads requiring re-engagement." data-bn="পুনঃযোগাযোগের জন্য কোনো নিষ্ক্রিয় লিড নেই।">
                    No leads requiring re-engagement.
                </span>
            </div>

            <div id="dashboard-stale-container" class="space-y-2" 
                 @if($staleLeads->isEmpty()) style="display: none;" @endif>
                @foreach ($staleLeads as $lead)
                    <div data-lead-id="{{ $lead->id }}" class="flex items-center justify-between p-2.5 bg-white border border-purple-100 rounded-xl text-xs shadow-2xs">
                        <div class="min-w-0 pr-2">
                            <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-slate-900 hover:text-purple-700 block truncate">
                                {{ $lead->name }} <span class="text-[11px] font-mono text-purple-600 font-normal">#{{ $lead->id }}</span>
                            </a>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                📞 {{ $lead->mobile }} • 
                                <span data-en="Last:" data-bn="সর্বশেষ:">Last:</span> 
                                {{ $lead->last_contact_at?->diffForHumans() ?? 'None' }}
                            </div>
                        </div>
                        <a href="{{ route('leads.show', $lead->id) }}" 
                           class="text-[11px] font-bold text-purple-700 hover:underline flex-shrink-0 px-2 py-1 bg-purple-50 rounded-lg">
                            <span data-en="Re-engage" data-bn="যোগাযোগ">Re-engage</span> &rarr;
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- C. RECENT TIMELINE ACTIVITY STREAM -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-5 flex flex-col md:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                    <span>⚡</span> 
                    <span data-en="Recent Activity Stream" data-bn="সাম্প্রতিক অ্যাক্টিভিটি">Recent Activity Stream</span>
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
                                    <a href="{{ route('leads.show', $activity->lead->id) }}" class="text-orange-600 font-semibold hover:underline">
                                        {{ $activity->lead->name }} <span class="text-slate-400 font-normal">#{{ $activity->lead->id }}</span>
                                    </a> •
                                @endif
                                {{ $activity->performed_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-slate-400 text-xs py-4" 
                         data-en="No recent activity recorded yet." 
                         data-bn="এখনও কোনো সাম্প্রতিক অ্যাক্টিভিটি নেই।">
                        No recent activity recorded yet.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    @endcan

    @cannot('leads.view')
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs text-center">
        <h2 class="text-base font-bold text-slate-900" data-en="Your workspace is ready" data-bn="আপনার ওয়ার্কস্পেস প্রস্তুত">
            Your workspace is ready
        </h2>
        <p class="mt-2 text-sm text-slate-600 max-w-md mx-auto" 
           data-en="Explore your team and organization resources below. Your administrator can enable additional CRM tools for your role."
           data-bn="নিচের টিম এক্সপ্লোরার এবং রিসোর্সসমূহ দেখুন। আপনার এডমিনিস্ট্রেটর আপনার রোলের জন্য সিআরএম টুল সক্রিয় করতে পারেন।">
            Explore your team and organization resources below. Your administrator can enable additional CRM tools for your role.
        </p>
        <div class="mt-5 flex justify-center gap-3">
            <a href="{{ route('team.index') }}" class="btn-primary">
                <span data-en="Open Team Explorer" data-bn="টিম এক্সপ্লোরার খুলুন">Open Team Explorer</span>
            </a>
            <a href="{{ route('packages.index') }}" class="btn-secondary">
                <span data-en="Packages & Ranks" data-bn="প্যাকেজ ও র্যাংক">Packages & Ranks</span>
            </a>
        </div>
    </div>
    @endcannot
</div>
@endsection
