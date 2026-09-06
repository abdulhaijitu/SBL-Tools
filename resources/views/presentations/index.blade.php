@extends('layouts.app')

@section('page-title', 'Presentations')
@section('page-subtitle', 'Schedule, Record & Track Presentation Conversions')

@section('content')
<div class="space-y-4" x-data="{ newPresModal: false }">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Presentation Sessions</h3>
            <p class="text-xs text-slate-500">Track 1-on-1 and group presentations across all lead stages.</p>
        </div>

        <button @click="newPresModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Record Presentation</span>
        </button>
    </div>

    <!-- Presentations Grid / Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($presentations as $pres)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 hover:border-orange-200 transition-all flex flex-col justify-between">
            <div data-presentation-id="{{ $pres->id }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 hover:border-orange-200 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200">
                            {{ $pres->type->value }}
                        </span>
                        @if ($pres->outcome)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $pres->outcome->badgeClasses() }}">
                                {{ $pres->outcome->value }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                Pending
                            </span>
                        @endif
                    </div>

                    <h4 class="font-bold text-sm text-slate-900 mb-1">
                        {{ $pres->topic ?? 'SBL Ecosystem Presentation' }}
                    </h4>

                    @if ($pres->lead)
                        <div class="text-xs text-slate-600 mt-2 p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Lead:</span>
                            <a href="{{ route('leads.show', $pres->lead->id) }}" class="font-bold text-orange-600 hover:underline">
                                {{ $pres->lead->name }}
                            </a>
                            <span class="text-slate-500 text-[11px] block">📞 {{ $pres->lead->mobile }}</span>
                        </div>
                    @endif

                    @if ($pres->questions || $pres->objections)
                        <div class="mt-3 space-y-1 text-xs text-slate-600">
                            @if ($pres->questions)
                                <div><strong class="text-slate-800">Q:</strong> {{ $pres->questions }}</div>
                            @endif
                            @if ($pres->objections)
                                <div><strong class="text-rose-700">Objection:</strong> {{ $pres->objections }}</div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                    <span>{{ $pres->date_time->format('d M, Y h:i A') }}</span>
                    <span>By {{ $pres->user->name ?? 'Admin' }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 p-12 text-center text-slate-400 text-xs">
            <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 p-12 text-center text-slate-400 text-xs empty-presentations-notice">
                No presentations recorded yet. Click "Record Presentation" above to log a session.
            </div>
        @endforelse
    </div>

    @if ($presentations->hasPages())
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
            {{ $presentations->links() }}
        </div>
    @endif

    <!-- New Presentation Modal -->
    <div x-show="newPresModal" 
         x-transition 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="newPresModal = false" class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900">Record Presentation</h3>
                <button @click="newPresModal = false" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
            </div>

            <form action="{{ route('presentations.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Select Lead <span class="text-rose-500">*</span></label>
                    <select name="lead_id" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                        @foreach ($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->name }} ({{ $lead->mobile }}) - {{ $lead->stage->label() }}</option>
                            <option value="{{ $lead->id }}" data-lead-id="{{ $lead->id }}">{{ $lead->name }} ({{ $lead->mobile }}) - {{ $lead->stage->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Type <span class="text-rose-500">*</span></label>
                        <select name="type" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            @foreach ($types as $type)
                                <option value="{{ $type->value }}">{{ $type->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Date & Time <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="date_time" required value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Topic / Presentation Focus</label>
                    <input type="text" name="topic" placeholder="e.g. SBL Dropshipping & Compensation Overview" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Outcome</label>
                        <select name="outcome" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            <option value="">Pending / In Progress</option>
                            @foreach ($outcomes as $outcome)
                                <option value="{{ $outcome->value }}">{{ $outcome->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Next Follow-up Date</label>
                        <input type="datetime-local" name="next_follow_up_at" value="{{ now()->addDay()->setHour(11)->format('Y-m-d\TH:i') }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Questions Asked</label>
                        <textarea name="questions" rows="2" placeholder="Key questions..." class="w-full text-xs rounded-xl border border-slate-300 p-2"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Objections Raised</label>
                        <textarea name="objections" rows="2" placeholder="Objections to address..." class="w-full text-xs rounded-xl border border-slate-300 p-2"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="newPresModal = false" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl shadow-xs">Save Presentation</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

