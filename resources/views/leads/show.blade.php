@extends('layouts.app')

@section('page-title', $lead->name)
@section('page-subtitle', 'Lead Details & Unified Activity Timeline')

@section('content')
<div class="space-y-5" x-data="{ 
    actionModal: false, 
    modalType: 'note', 
    modalTitle: 'Add Note',
    completeTaskModal: false,
    activeTaskId: null,
    activeTaskTitle: '',
    setPresetTime(type) {
        const now = new Date();
        let target = new Date();
        if (type === 'today_evening') {
            target.setHours(17, 0, 0, 0);
        } else if (type === 'tomorrow_morning') {
            target.setDate(now.getDate() + 1);
            target.setHours(10, 0, 0, 0);
        } else if (type === 'in_2_days') {
            target.setDate(now.getDate() + 2);
            target.setHours(11, 0, 0, 0);
        }
        const y = target.getFullYear();
        const m = String(target.getMonth() + 1).padStart(2, '0');
        const d = String(target.getDate()).padStart(2, '0');
        const h = String(target.getHours()).padStart(2, '0');
        const min = String(target.getMinutes()).padStart(2, '0');
        return `${y}-${m}-${d}T${h}:${min}`;
    }
}">

    <!-- Top Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('leads.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-orange-600 transition-colors bg-white px-3.5 py-2 rounded-xl border border-slate-200/80 shadow-xs min-h-[40px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span data-en="Back to Leads" data-bn="লিডস তালিকায় ফিরুন">Back to Leads</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="text-xs font-mono text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">#{{ $lead->id }}</span>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $lead->stage->badgeClasses() }}">
                {{ $lead->stage->label() }}
            </span>
        </div>
    </div>

    <!-- ==================== SECTION A: BASIC INFO CARD ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 md:p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            
            <!-- Lead Profile Left Column -->
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-700 font-bold text-xl flex items-center justify-center flex-shrink-0 shadow-xs overflow-hidden border border-orange-200/50">
                    @if (!empty($lead->photo))
                        <img src="{{ $lead->photo }}" alt="{{ $lead->name }}" class="w-full h-full object-cover">
                    @else
                        <span>{{ strtoupper(substr($lead->name, 0, 1)) }}</span>
                    @endif
                </div>

                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl font-black text-slate-900 tracking-tight">{{ $lead->name }}</h1>
                    </div>

                    <!-- Contact Details (Mobile & WhatsApp with 1-tap links) -->
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <!-- Mobile Call -->
                        <a href="tel:{{ $lead->mobile }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs border border-emerald-200/80 transition-all shadow-2xs active:scale-95 min-h-[38px]">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>
                            <span class="font-mono">{{ $lead->mobile }}</span>
                        </a>

                        <!-- WhatsApp -->
                        @php
                            $waNumber = \App\Support\PhoneNumber::whatsapp($lead->whatsapp ?: $lead->mobile);
                        @endphp
                        @if ($waNumber)
                            <a href="https://wa.me/{{ $waNumber }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-all active:scale-95 min-h-[38px]">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>
                                <span>WhatsApp</span>
                            </a>
                        @endif

                        @if ($lead->location)
                            <span class="inline-flex items-center gap-1 text-slate-500 text-xs py-1.5 px-2.5 bg-slate-50 border border-slate-100 rounded-xl">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $lead->location }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stage & Management Actions -->
            <div class="flex flex-wrap items-center gap-2 self-start md:self-auto">
                <!-- 1-Tap Stage Change Select -->
                <form action="{{ route('leads.update-stage', $lead->id) }}" method="POST" class="inline-flex items-center">
                    @csrf
                    @method('PATCH')
                    <div class="relative">
                        <select name="stage" onchange="this.form.submit()" class="text-xs font-bold rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2 bg-slate-50 cursor-pointer min-h-[40px] pr-8">
                            @foreach ($stages as $stage)
                                <option value="{{ $stage->value }}" {{ $lead->stage === $stage ? 'selected' : '' }}>
                                    Stage: {{ $stage->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <!-- Convert Lead Button -->
                @if ($lead->stage !== \App\Enums\LeadStage::CONVERTED)
                    @can('leads.convert')
                    <form action="{{ route('leads.convert', $lead->id) }}" method="POST" onsubmit="return confirm('Convert this lead to Customer/Member?')">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors min-h-[40px] cursor-pointer">
                            ✓ Convert Lead
                        </button>
                    </form>
                    @endcan
                @endif

                <!-- Edit Lead -->
                @can('leads.edit')
                <a href="{{ route('leads.edit', $lead->id) }}" class="p-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 hover:text-slate-900 min-h-[40px] min-w-[40px] flex items-center justify-center transition-all" title="Edit Lead">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    <span class="sr-only">Edit</span>
                </a>
                @endcan

                <!-- Delete Lead -->
                @can('leads.delete')
                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-xl border border-rose-200 hover:bg-rose-50 text-rose-600 hover:text-rose-700 min-h-[40px] min-w-[40px] flex items-center justify-center transition-all cursor-pointer" title="Delete Lead">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        <span class="sr-only">Delete</span>
                    </button>
                </form>
                @endcan
            </div>
        </div>

        <!-- Meta Details Grid (Profession, Source, Created At) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-slate-100 text-xs">
            <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[11px] font-semibold uppercase tracking-wider">Profession / Business</span>
                <span class="text-slate-800 font-bold mt-0.5 block truncate">{{ $lead->profession_or_business ?? 'Not specified' }}</span>
            </div>

            <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[11px] font-semibold uppercase tracking-wider">Lead Source</span>
                <span class="text-slate-800 font-bold mt-0.5 block truncate">{{ $lead->source->name ?? 'Direct' }}</span>
            </div>

            <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[11px] font-semibold uppercase tracking-wider">Created</span>
                <span class="text-slate-800 font-bold mt-0.5 block">{{ $lead->created_at->format('d M, Y') }}</span>
            </div>

            <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[11px] font-semibold uppercase tracking-wider">Owner / Assigned</span>
                <span class="text-slate-800 font-bold mt-0.5 block truncate">{{ $lead->owner->name ?? 'Unassigned' }}</span>
            </div>
        </div>
    </div>

    <!-- ==================== SECTION B: NEXT ACTION CARD ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 md:p-6 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-lg">🎯</span>
                <h2 class="text-sm sm:text-base font-bold text-slate-900" data-en="Next Action" data-bn="পরবর্তী অ্যাকশন">Next Action</h2>
            </div>

            <!-- Primary button: + Set Next Action -->
            <button type="button" 
                    @click="modalType = 'task'; modalTitle = 'Set Next Action'; actionModal = true" 
                    class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 active:scale-95 text-white font-bold text-xs shadow-xs transition-all cursor-pointer flex items-center gap-1.5 min-h-[40px]">
                <span>➕</span>
                <span data-en="Set Next Action" data-bn="পরবর্তী অ্যাকশন নির্ধারণ">Set Next Action</span>
            </button>
        </div>

        @if ($lead->next_action_at)
            <!-- Active Next Action Banner -->
            <div class="rounded-xl p-4 border flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $lead->is_next_action_overdue ? 'bg-rose-50/80 border-rose-200 text-rose-950' : 'bg-orange-50/60 border-orange-200 text-orange-950' }}">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-base font-bold">{{ $lead->next_action_type ?? 'Scheduled Action' }}</span>
                        @if($lead->is_next_action_overdue)
                            <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white text-[10px] font-black uppercase tracking-wider">Overdue</span>
                        @else
                            <span class="px-2 py-0.5 rounded-md bg-orange-200 text-orange-900 text-[10px] font-bold">Scheduled</span>
                        @endif
                    </div>
                    <div class="text-xs {{ $lead->is_next_action_overdue ? 'text-rose-700 font-semibold' : 'text-slate-600' }} flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $lead->next_action_at->format('d M, Y \a\t h:i A') }}</span>
                    </div>
                </div>

                <!-- If there is a matching pending task, allow completing it directly -->
                @php
                    $pendingTask = $lead->tasks->where('status', \App\Enums\TaskStatus::PENDING)->first();
                @endphp
                @if($pendingTask)
                    <button type="button" 
                            @click="activeTaskId = {{ $pendingTask->id }}; activeTaskTitle = '{{ addslashes($pendingTask->title) }}'; completeTaskModal = true" 
                            class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition-all cursor-pointer whitespace-nowrap self-start sm:self-auto min-h-[38px] flex items-center gap-1.5">
                        <span>✓</span>
                        <span data-en="Complete Follow-up" data-bn="ফলো-আপ সম্পন্ন করুন">Complete Follow-up</span>
                    </button>
                @endif
            </div>
        @else
            <!-- No Next Action Scheduled Warning -->
            <div class="rounded-xl p-3.5 bg-amber-50 border border-amber-200/80 text-amber-900 text-xs flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-base">⚠️</span>
                    <span>No next action scheduled. Rule: Never leave a lead without a next follow-up.</span>
                </div>
                <button type="button" 
                        @click="modalType = 'task'; modalTitle = 'Set Next Action'; actionModal = true" 
                        class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-2xs whitespace-nowrap cursor-pointer">
                    Schedule Now
                </button>
            </div>
        @endif
    </div>

    <!-- ==================== SECTION C: QUICK ACTIONS BAR ==================== -->
    <div class="bg-slate-900 rounded-2xl p-3 shadow-md flex items-center justify-between gap-2 overflow-x-auto text-white no-scrollbar">
        <span class="text-xs font-bold text-orange-400 uppercase tracking-wider px-2 flex-shrink-0" data-en="Quick Actions:" data-bn="কুইক অ্যাকশন:">Quick Actions:</span>
        <div class="flex items-center gap-2 flex-shrink-0">
            <!-- 1. Direct Call -->
            <a href="tel:{{ $lead->mobile }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-emerald-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5 min-h-[40px]">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>
                <span data-en="Call" data-bn="কল">Call</span>
            </a>

            <!-- 2. WhatsApp -->
            @if ($waNumber)
                <a href="https://wa.me/{{ $waNumber }}" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-emerald-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5 min-h-[40px]">
                    <svg class="w-4 h-4 text-emerald-400 fill-current" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>
                    <span>WhatsApp</span>
                </a>
            @endif

            <!-- 3. Add Note -->
            <button type="button" 
                    @click="modalType = 'note'; modalTitle = 'Add Note'; actionModal = true" 
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-orange-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer min-h-[40px]">
                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span data-en="Add Note" data-bn="নোট যুক্ত করুন">Add Note</span>
            </button>

            <!-- 4. Add Follow-up -->
            <button type="button" 
                    @click="modalType = 'task'; modalTitle = 'Add Follow-up / Task'; actionModal = true" 
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-orange-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer min-h-[40px]">
                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span data-en="Add Follow-up" data-bn="ফলো-আপ">Add Follow-up</span>
            </button>

            <!-- 5. Add Presentation -->
            <button type="button" 
                    @click="modalType = 'presentation'; modalTitle = 'Add Presentation'; actionModal = true" 
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-orange-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer min-h-[40px]">
                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span data-en="Add Presentation" data-bn="প্রেজেন্টেশন">Add Presentation</span>
            </button>
        </div>
    </div>

    <!-- ==================== SECTION D: UNIFIED CHRONOLOGICAL ACTIVITY TIMELINE ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 md:p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <span class="text-lg">📜</span>
                <h3 class="text-base font-bold text-slate-900" data-en="Activity Timeline" data-bn="অ্যাক্টিভিটি টাইমলাইন">Activity Timeline</h3>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                {{ $lead->activities->count() }} activities
            </span>
        </div>

        <div id="lead-show-timeline-container" class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
            @forelse ($lead->activities as $activity)
                <div class="relative">
                    <!-- Timeline Dot with Category Colors -->
                    <div class="absolute -left-6 top-1.5 w-4 h-4 rounded-full border-2 border-white shadow-2xs 
                        @if($activity->type === 'conversion') bg-emerald-500
                        @elseif($activity->type === 'stage_change') bg-blue-500
                        @elseif($activity->type === 'call') bg-emerald-600
                        @elseif($activity->type === 'whatsapp') bg-emerald-500
                        @elseif($activity->type === 'presentation') bg-purple-600
                        @elseif($activity->type === 'task_created' || $activity->type === 'task_completed') bg-orange-500
                        @elseif($activity->type === 'note') bg-amber-500
                        @else bg-slate-500 @endif">
                    </div>

                    <!-- Activity Card -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 text-xs space-y-2 hover:bg-slate-100/50 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm">{{ $activity->title }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    @if($activity->type === 'conversion') bg-emerald-100 text-emerald-800
                                    @elseif($activity->type === 'presentation') bg-purple-100 text-purple-800
                                    @elseif($activity->type === 'stage_change') bg-blue-100 text-blue-800
                                    @elseif($activity->type === 'call' || $activity->type === 'whatsapp') bg-emerald-100 text-emerald-800
                                    @elseif($activity->type === 'task_completed') bg-emerald-100 text-emerald-800
                                    @elseif($activity->type === 'task_created') bg-orange-100 text-orange-800
                                    @else bg-slate-200 text-slate-700 @endif">
                                    {{ str_replace('_', ' ', $activity->type) }}
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $activity->performed_at->format('d M, Y h:i A') }}</span>
                        </div>

                        @if ($activity->description)
                            <p class="text-slate-600 leading-relaxed whitespace-pre-line">{{ $activity->description }}</p>
                        @endif

                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 text-[10px] text-slate-400">
                            <span>Logged by {{ $activity->user->name ?? 'System' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400 text-xs">
                    <div class="text-sm font-semibold text-slate-600">No activity logged yet</div>
                    <div class="mt-1">Use the Quick Action buttons above to log your first call, note, or presentation.</div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ==================== MODAL 1: QUICK ACTIONS (Note / Call) ==================== -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="actionModal && (modalType === 'note' || modalType === 'call')" 
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/70 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="actionModal = false" class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl p-5 sm:p-6 border border-slate-200 max-h-[90vh] overflow-y-auto space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900" x-text="modalTitle"></h3>
                <button @click="actionModal = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('leads.add-activity', $lead->id) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="type" :value="modalType">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Title / Summary *</label>
                    <input type="text" name="title" required :placeholder="modalType === 'call' ? 'Call summary (e.g. Spoke with lead)' : 'Note title'" class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2 min-h-[40px]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Details</label>
                    <textarea name="description" rows="3" placeholder="Key discussion points, customer interest, next steps..." class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 p-2.5"></textarea>
                </div>

                <div class="bg-orange-50 p-3.5 rounded-xl border border-orange-200/80 space-y-2">
                    <span class="text-xs font-bold text-orange-950 block">Schedule Next Follow-up (Rule: Never leave lead without next action)</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <input type="text" name="next_action_type" placeholder="Action (e.g. Follow-up Call)" class="text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white min-h-[38px]">
                        <input type="datetime-local" name="next_action_at" class="text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white min-h-[38px]">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="actionModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer min-h-[40px]">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-xs min-h-[40px] cursor-pointer">Save & Record</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 2: ADD PRESENTATION ==================== -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="actionModal && modalType === 'presentation'" 
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/70 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="actionModal = false" class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl p-5 sm:p-6 border border-slate-200 max-h-[90vh] overflow-y-auto space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Add Presentation</h3>
                <button @click="actionModal = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('presentations.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Presentation Type *</label>
                        <select name="type" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white min-h-[40px]">
                            <option value="Online">Online (Zoom / Meet)</option>
                            <option value="Offline">Offline</option>
                            <option value="1-to-1">One-to-One</option>
                            <option value="Group">Group</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Outcome / Status</label>
                        <select name="outcome" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white min-h-[40px]">
                            <option value="">Scheduled</option>
                            <option value="Hot">🔥 Hot</option>
                            <option value="Warm">Warm</option>
                            <option value="Cold">Cold</option>
                            <option value="Converted">✓ Converted</option>
                            <option value="Not Interested">Not Interested</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Date & Time *</label>
                    <input type="datetime-local" name="date_time" required value="{{ now()->addDay()->setHour(16)->format('Y-m-d\TH:i') }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 min-h-[40px]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Topic / Agenda</label>
                    <input type="text" name="topic" placeholder="e.g. SBL Business & Income Plan" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 min-h-[40px]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Short Note / Questions & Objections</label>
                    <textarea name="notes" rows="2" placeholder="Key focus or questions raised..." class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <div class="bg-purple-50 p-3 rounded-xl border border-purple-200/80">
                    <label class="block text-xs font-bold text-purple-950 mb-1">Next Follow-up Date</label>
                    <input type="datetime-local" name="next_follow_up_at" value="{{ now()->addDays(2)->setHour(11)->format('Y-m-d\TH:i') }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white min-h-[38px]">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="actionModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer min-h-[40px]">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-xs min-h-[40px] cursor-pointer">Save Presentation</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 3: SET NEXT ACTION / ADD FOLLOW-UP ==================== -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="actionModal && modalType === 'task'" 
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/70 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="actionModal = false" class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl p-5 sm:p-6 border border-slate-200 max-h-[90vh] overflow-y-auto space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900" x-text="modalTitle"></h3>
                <button @click="actionModal = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-3.5" x-data="{ dueTime: '{{ now()->addDay()->setHour(11)->format('Y-m-d\TH:i') }}' }">
                @csrf
                <input type="hidden" name="related_lead_id" value="{{ $lead->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Action Title *</label>
                    <input type="text" name="title" required value="Follow-up with {{ $lead->name }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 min-h-[40px]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Action Type *</label>
                        <select name="type" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white min-h-[40px]">
                            <option value="Follow-up">Follow-up</option>
                            <option value="Call">Call</option>
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Meeting">Meeting</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Priority *</label>
                        <select name="priority" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white min-h-[40px]">
                            <option value="High">High</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                </div>

                <!-- Shortcuts -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Quick Schedule Shortcuts</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="dueTime = setPresetTime('today_evening')" class="py-1.5 px-2 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-800 text-xs font-bold border border-orange-200 text-center cursor-pointer">
                            Today 5 PM
                        </button>
                        <button type="button" @click="dueTime = setPresetTime('tomorrow_morning')" class="py-1.5 px-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-800 text-xs font-bold border border-blue-200 text-center cursor-pointer">
                            Tomorrow 10 AM
                        </button>
                        <button type="button" @click="dueTime = setPresetTime('in_2_days')" class="py-1.5 px-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-800 text-xs font-bold border border-purple-200 text-center cursor-pointer">
                            In 2 Days 11 AM
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Due Date & Time *</label>
                    <input type="datetime-local" name="due_at" x-model="dueTime" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 min-h-[40px]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Short Note</label>
                    <textarea name="notes" rows="2" placeholder="Instructions or reminder note..." class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="actionModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer min-h-[40px]">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-xs min-h-[40px] cursor-pointer">Schedule Action</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 4: COMPLETE FOLLOW-UP / SET IMMEDIATE NEXT STEP ==================== -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="completeTaskModal" 
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/70 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="completeTaskModal = false" class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl p-5 sm:p-6 border border-slate-200 max-h-[90vh] overflow-y-auto space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Complete Follow-up</h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="activeTaskTitle"></p>
                </div>
                <button @click="completeTaskModal = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center cursor-pointer">&times;</button>
            </div>

            <form :action="`/tasks/${activeTaskId}/complete`" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Follow-up Outcome / Result *</label>
                    <textarea name="outcome" required rows="2" placeholder="What was discussed? (e.g. Lead agreed to view presentation on Friday)" class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-orange-500"></textarea>
                </div>

                <div class="bg-orange-50 p-3.5 rounded-xl border border-orange-200/80 space-y-2.5">
                    <span class="text-xs font-bold text-orange-950 block">Immediately Set Next Action</span>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Next Action Type</label>
                        <input type="text" name="next_action" placeholder="e.g. Schedule 1-on-1 Presentation" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white min-h-[38px]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Next Action Date & Time</label>
                        <input type="datetime-local" name="next_action_at" value="{{ now()->addDays(2)->setHour(11)->format('Y-m-d\TH:i') }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white min-h-[38px]">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="completeTaskModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer min-h-[40px]">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs min-h-[40px] cursor-pointer">✓ Mark Completed & Schedule</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
