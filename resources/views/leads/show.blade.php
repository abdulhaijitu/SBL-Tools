@extends('layouts.app')

@section('page-title', $lead->name)
@section('page-subtitle', 'Lead Profile & Activity Timeline')

@section('content')
<div class="space-y-6" x-data="{ actionModal: false, modalType: 'note', modalTitle: 'Add Note' }">

    <!-- Back Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('leads.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-orange-600 transition-colors bg-white px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Leads</span>
        </a>
    </div>

    <!-- Lead Profile Header Card (Section 6) -->
    <div id="lead-show-profile-card" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 md:p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <!-- Left: Avatar & Primary Info -->
            <div class="flex items-start gap-4">
                <div id="lead-show-avatar" class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-700 font-bold text-xl flex items-center justify-center flex-shrink-0 shadow-xs overflow-hidden border border-orange-200/50">
                    @if (!empty($lead->photo))
                        <img src="{{ $lead->photo }}" alt="{{ $lead->name }}" class="w-full h-full object-cover">
                    @else
                        {{ substr($lead->name, 0, 1) }}
                    @endif
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 id="lead-show-name" class="text-xl font-bold text-slate-900">{{ $lead->name }}</h2>
                        <span id="lead-show-id" class="text-sm font-mono text-slate-400 font-normal">#{{ $lead->id }}</span>
                        <span id="lead-show-id" class="text-xs font-mono text-slate-500 font-semibold bg-slate-100 px-2 py-0.5 rounded-md">#{{ $lead->id }}</span>
                        <span id="lead-show-stage-badge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $lead->stage->badgeClasses() }}">
                            {{ $lead->stage->label() }}
                        </span>
                        <span id="lead-show-temp-badge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $lead->temperature->badgeClasses() }}">
                            {{ $lead->temperature->label() }}
                        </span>
                        @if ($lead->lead_tag)
                            <span class="px-2 py-0.5 rounded bg-orange-100 text-orange-800 text-xs font-bold">
                                {{ $lead->lead_tag }}
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-slate-500 mt-2 flex flex-wrap items-center gap-2">
                        <a id="lead-show-mobile-btn" href="tel:{{ $lead->mobile }}" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs flex items-center gap-1.5 border border-emerald-200 active:scale-95 transition-all">
                            <span>📞</span> <span id="lead-show-mobile-text">{{ $lead->mobile }}</span>
                    <div class="text-xs text-slate-500 mt-2.5 flex flex-wrap items-center gap-2">
                        <a id="lead-show-mobile-btn" href="tel:{{ $lead->mobile }}" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs flex items-center gap-1.5 border border-emerald-200/80 active:scale-95 transition-all shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>
                            <span id="lead-show-mobile-text">{{ $lead->mobile }}</span>
                        </a>
                        <a id="lead-show-wa-btn" href="https://wa.me/{{ \App\Support\PhoneNumber::whatsapp($lead->whatsapp ?: $lead->mobile) }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-xs active:scale-95 transition-all">
                            <span>💬</span> WhatsApp
                        <a id="lead-show-wa-btn" href="https://wa.me/{{ \App\Support\PhoneNumber::whatsapp($lead->whatsapp ?: $lead->mobile) }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-xs active:scale-95 transition-all">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>
                            <span>WhatsApp</span>
                        </a>
                        @if ($lead->facebook_url)
                            <a id="lead-show-fb-link" href="{{ $lead->facebook_url }}" target="_blank" class="px-2.5 py-1.5 rounded-xl border border-blue-200 text-blue-600 hover:bg-blue-50 font-medium text-xs flex items-center gap-1">
                                <span>🌐</span> FB
                            <a id="lead-show-fb-link" href="{{ $lead->facebook_url }}" target="_blank" rel="noopener noreferrer" class="px-2.5 py-1.5 rounded-xl border border-blue-200 text-blue-600 hover:bg-blue-50 font-medium text-xs flex items-center gap-1 active:scale-95 transition-all">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                <span>Facebook</span>
                            </a>
                        @endif
                        <span id="lead-show-location-container" class="flex items-center gap-1 text-slate-500 text-xs py-1 px-1" @if(!$lead->location) style="display: none;" @endif>
                            <span>📍</span> <span id="lead-show-location-text">{{ $lead->location }}</span>
                        @php
                            $validLocation = $lead->location && !str_starts_with($lead->location, 'http://') && !str_starts_with($lead->location, 'https://') && !str_contains($lead->location, '/leads/');
                        @endphp
                        <span id="lead-show-location-container" class="flex items-center gap-1 text-slate-500 text-xs py-1 px-1.5 bg-slate-50 border border-slate-100 rounded-lg" @if(!$validLocation) style="display: none;" @endif>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span id="lead-show-location-text">{{ $validLocation ? $lead->location : '' }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Stage Quick Update & Edit -->
            <div class="flex items-center gap-2">
                <form id="lead-show-stage-form" action="{{ route('leads.update-stage', $lead->id) }}" method="POST" class="inline-flex items-center">
                    @csrf
                    @method('PATCH')
                    <select id="lead-show-stage-select" name="stage" onchange="this.form.submit()" class="text-xs font-semibold rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2 bg-slate-50">
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->value }}" {{ $lead->stage === $stage ? 'selected' : '' }}>
                                Stage: {{ $stage->label() }}
                            </option>
                        @endforeach
                    </select>
                </form>

                @if ($lead->stage !== \App\Enums\LeadStage::CONVERTED)
                    @can('leads.convert')<form id="lead-show-convert-form" action="{{ route('leads.convert', $lead->id) }}" method="POST" onsubmit="return confirm('Convert this lead to Customer/Member?')">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition-colors">
                            ✓ Convert Lead
                        </button>
                    </form>@endcan
                @endif

                @can('leads.edit')<a id="lead-show-edit-link" href="{{ route('leads.edit', $lead->id) }}" class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 hover:text-slate-900" title="Edit Lead">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                </a>@endcan

                @can('leads.delete')<form id="lead-show-delete-form" action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-xl border border-rose-200 hover:bg-rose-50 text-rose-600 hover:text-rose-700" title="Delete Lead">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>@endcan
            </div>
        </div>

        <!-- Meta Snapshot Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 text-xs">
            <div>
                <span class="text-slate-400 block text-[11px] uppercase tracking-wider font-semibold">Lead Score</span>
                <div class="text-slate-900 font-bold text-sm mt-0.5 flex items-center gap-2">
                    <span id="lead-show-score-text">{{ $lead->score }} / 100</span>
                    <div class="w-16 h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div id="lead-show-score-bar" class="h-full bg-orange-500 rounded-full" style="width: {{ $lead->score }}%"></div>
                    </div>
                </div>
            </div>

            <div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4 text-xs">
            <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[11px] uppercase tracking-wider font-semibold">Lead Source</span>
                <span id="lead-show-source-text" class="text-slate-800 font-semibold text-sm mt-0.5 block">{{ $lead->source->name ?? 'N/A' }}</span>
                <span id="lead-show-source-text" class="text-slate-800 font-bold text-sm mt-0.5 block">{{ $lead->source->name ?? 'Direct' }}</span>
            </div>

            <div>
            <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[11px] uppercase tracking-wider font-semibold">Scheduled Next Action</span>
                @if ($lead->next_action_at)
                    <div id="lead-show-next-action-text" class="mt-0.5 {{ $lead->is_next_action_overdue ? 'text-rose-600 font-bold' : 'text-slate-800 font-semibold' }}">
                    <div id="lead-show-next-action-text" class="mt-0.5 {{ $lead->is_next_action_overdue ? 'text-rose-600 font-bold' : 'text-slate-800 font-bold' }} text-sm">
                        {{ $lead->next_action_type ?? 'Action' }} ({{ $lead->next_action_at->format('d M, h:i A') }})
                    </div>
                @else
                    <span id="lead-show-next-action-text" class="text-amber-600 font-semibold text-xs mt-0.5 block">Needs Next Action</span>
                    <span id="lead-show-next-action-text" class="text-amber-600 font-bold text-xs mt-0.5 block">Needs Next Action</span>
                @endif
            </div>

            <div>
            <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[11px] uppercase tracking-wider font-semibold">Interests</span>
                <div id="lead-show-interests-list" class="flex flex-wrap gap-1 mt-0.5">
                <div id="lead-show-interests-list" class="flex flex-wrap gap-1 mt-1">
                    @forelse ($lead->interests as $interest)
                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-medium">
                        <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 text-[11px] font-medium shadow-2xs">
                            {{ $interest->interest }}
                        </span>
                    @empty
                        <span class="text-slate-400">None specified</span>
                        <span class="text-slate-400 italic text-[11px]">None specified</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Bar (Section 6 & 24) -->
    <div class="bg-slate-900 rounded-2xl p-3 shadow-md flex items-center justify-between gap-2 overflow-x-auto text-white no-scrollbar">
    <div class="bg-slate-900 rounded-2xl p-2.5 shadow-md flex items-center justify-between gap-2 overflow-x-auto text-white no-scrollbar">
        <span class="text-xs font-bold text-orange-400 uppercase tracking-wider px-2 flex-shrink-0">Quick Action:</span>
        <div class="flex items-center gap-2 flex-shrink-0">
        <div class="flex items-center gap-1.5 flex-shrink-0">
            <button @click="modalType = 'call'; modalTitle = 'Log Phone / WhatsApp Call'; actionModal = true" 
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-orange-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5">
                <span>📞</span> Log Call
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-orange-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>
                <span>Log Call</span>
            </button>
            <button @click="modalType = 'note'; modalTitle = 'Add Interaction Note'; actionModal = true" 
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-orange-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5">
                <span>📝</span> Add Note
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-orange-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Add Note</span>
            </button>
            <button @click="modalType = 'presentation'; modalTitle = 'Schedule Presentation'; actionModal = true" 
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-orange-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5">
                <span>📊</span> Presentation
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-orange-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Presentation</span>
            </button>
            <button @click="modalType = 'task'; modalTitle = 'Schedule Task / Follow-up'; actionModal = true" 
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-orange-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5">
                <span>⏰</span> Next Follow-up
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-orange-600 active:scale-95 text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Next Follow-up</span>
            </button>
            <a href="{{ route('toolkit.index') }}" target="_blank" 
               class="px-3 py-1.5 rounded-xl bg-orange-600/30 hover:bg-orange-600 active:scale-95 text-orange-300 hover:text-white text-xs font-semibold transition-all flex items-center gap-1.5 border border-orange-500/40">
                <span>📖</span> Pitch Deck & Plans
            </a>
                <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Pitch Deck & Plans</span>
        </div>
    </div>

    <!-- Main Content Split: Timeline & Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Chronological Activity Timeline (Section 6) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-900">Chronological Activity Timeline</h3>
                    <span id="lead-activities-count" class="text-xs text-slate-400">{{ $lead->activities->count() }} activities</span>
                </div>

                <div id="lead-show-timeline-container" class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse ($lead->activities as $activity)
                        <div class="relative">
                            <!-- Dot -->
                            <div class="absolute -left-6 top-1 w-4 h-4 rounded-full border-2 border-white 
                                @if($activity->type === 'conversion') bg-emerald-500
                                @elseif($activity->type === 'stage_change') bg-blue-500
                                @elseif($activity->type === 'call') bg-orange-500
                                @elseif($activity->type === 'presentation') bg-purple-500
                                @else bg-slate-500 @endif">
                            </div>

                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 text-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-slate-900 text-sm">{{ $activity->title }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $activity->performed_at->format('d M, Y h:i A') }}</span>
                                </div>
                                @if ($activity->description)
                                    <p class="text-slate-600 mt-1 leading-relaxed">{{ $activity->description }}</p>
                                @endif
                                <div class="text-[10px] text-slate-400 mt-2">Logged by {{ $activity->user->name ?? 'System' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-400 text-xs">No activity logged yet. Use the Quick Action bar above to log your first call or note.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Tasks & Presentation History -->
        <div class="space-y-6">

            <!-- Active Tasks for this lead -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900">Associated Tasks</h3>
                    <span id="lead-tasks-count" class="text-xs text-slate-400">{{ $lead->tasks->count() }} total</span>
                </div>

                <div id="lead-show-tasks-container" class="space-y-2.5">
                    @forelse ($lead->tasks as $task)
                        <div class="p-3 rounded-xl border border-slate-100 text-xs hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800">{{ $task->title }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $task->status->badgeClasses() }}">
                                    {{ $task->status->value }}
                                </span>
                            </div>
                            <div class="text-slate-500 mt-1 flex items-center justify-between text-[11px]">
                                <span>Due: {{ $task->due_at->format('d M, h:i A') }}</span>
                                <span class="font-semibold text-slate-700">{{ $task->priority->value }}</span>
                            </div>
                            @if ($task->status !== \App\Enums\TaskStatus::COMPLETED)
                                <form action="{{ route('tasks.complete', $task->id) }}" method="POST" class="mt-2 pt-2 border-t border-slate-100">
                                    @csrf
                                    <input type="hidden" name="outcome" value="Completed successfully from lead profile">
                                    <button type="submit" class="text-xs text-emerald-600 font-semibold hover:underline">
                                        ✓ Mark as Done
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4 text-slate-400 text-xs">No pending tasks.</div>
                    @endforelse
                </div>
            </div>

            <!-- Presentation History for this lead -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900">Presentations</h3>
                    <span id="lead-presentations-count" class="text-xs text-slate-400">{{ $lead->presentations->count() }} sessions</span>
                </div>

                <div id="lead-show-presentations-container" class="space-y-2.5">
                    @forelse ($lead->presentations as $pres)
                        <div class="p-3 rounded-xl border border-purple-100 bg-purple-50/20 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-purple-900">{{ $pres->type->value }} Session</span>
                                @if($pres->outcome)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $pres->outcome->badgeClasses() }}">
                                        {{ $pres->outcome->value }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-slate-600 mt-1">{{ $pres->topic ?? 'No topic' }}</div>
                            <div class="text-slate-400 text-[11px] mt-1">{{ $pres->date_time->format('d M, Y h:i A') }}</div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-slate-400 text-xs">No presentations scheduled yet.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- Quick Action Modal -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="actionModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/70 backdrop-blur-xs"
         x-cloak>
        
        <div @click.outside="actionModal = false" class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl p-5 sm:p-6 border border-slate-200 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900" x-text="modalTitle"></h3>
                <button @click="actionModal = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center">&times;</button>
            </div>

            <!-- Form for Note / Call / Activity -->
            <form id="lead-show-activity-form" x-show="modalType === 'note' || modalType === 'call'" action="{{ route('leads.add-activity', $lead->id) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="type" :value="modalType">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Title / Summary</label>
                    <input type="text" name="title" required :placeholder="modalType === 'call' ? 'Call Summary / Outcome' : 'Note Title'" class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Discussion Details</label>
                    <textarea name="description" rows="3" placeholder="Write key points, objections, or commitments discussed..." class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 p-2.5"></textarea>
                </div>
                <div class="bg-orange-50 p-3 rounded-xl border border-orange-200/80 space-y-2">
                    <span class="text-[11px] font-bold text-orange-900 block">Schedule Next Follow-up (Rule: Never leave lead without next action)</span>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="next_action_type" placeholder="e.g. WhatsApp Message" class="text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white">
                        <input type="datetime-local" name="next_action_at" class="text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="actionModal = false" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-semibold text-xs shadow-xs">Save Activity</button>
                </div>
            </form>

            <!-- Form for Presentation -->
            <form id="lead-show-presentation-form" x-show="modalType === 'presentation'" action="{{ route('presentations.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Presentation Date & Time</label>
                    <input type="datetime-local" name="date_time" required value="{{ now()->addDay()->setHour(16)->format('Y-m-d\TH:i') }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Type</label>
                        <select name="type" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            <option value="Online">Online (Zoom/Meet)</option>
                            <option value="Offline">Offline</option>
                            <option value="Group">Group</option>
                            <option value="1-to-1">1-to-1</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Topic</label>
                        <input type="text" name="topic" placeholder="e.g. SBL Plan" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notes / Plan</label>
                    <textarea name="notes" rows="2" placeholder="Key focus or preparation notes..." class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="actionModal = false" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-semibold text-xs shadow-xs">Schedule Presentation</button>
                </div>
            </form>

            <!-- Form for Task / Next Follow-up -->
            <form id="lead-show-task-form" x-show="modalType === 'task'" action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="related_lead_id" value="{{ $lead->id }}">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Task Title</label>
                    <input type="text" name="title" required value="Follow-up with {{ $lead->name }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Task Type</label>
                        <select name="type" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            <option value="Follow-up">Follow-up</option>
                            <option value="Call">Call</option>
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Meeting">Meeting</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Priority</label>
                        <select name="priority" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            <option value="High">High</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Due Date & Time</label>
                    <input type="datetime-local" name="due_at" required value="{{ now()->addDay()->setHour(11)->format('Y-m-d\TH:i') }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="actionModal = false" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-semibold text-xs shadow-xs">Schedule Task</button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection

