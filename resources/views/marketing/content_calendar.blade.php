@extends('layouts.app')

@section('page-title', 'Marketing & Content Calendar')
@section('page-subtitle', '30-Day Organic & Campaign Marketing Schedule')

@section('content')
<div class="space-y-4" x-data="{ newContentModal: false, editModal: false, currentItem: {} }">

    <!-- Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Content</span>
            <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $stats['total'] }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider block">Published</span>
            <span class="text-2xl font-bold text-emerald-700 mt-1 block">{{ $stats['published'] }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-semibold text-blue-600 uppercase tracking-wider block">Planned & Ready</span>
            <span class="text-2xl font-bold text-blue-700 mt-1 block">{{ $stats['planned'] }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-semibold text-orange-600 uppercase tracking-wider block">Leads Generated</span>
            <span class="text-2xl font-bold text-orange-600 mt-1 block">{{ $stats['leads_generated'] ?? 0 }}</span>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2 overflow-x-auto text-xs pb-1 sm:pb-0">
            <a href="{{ route('marketing.content-calendar') }}" class="px-3 py-1.5 rounded-lg border font-medium {{ !request()->has('status') ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                All Content
            </a>
            @foreach ($statuses as $st)
                <a href="{{ route('marketing.content-calendar', ['status' => $st->value]) }}" class="px-3 py-1.5 rounded-lg border font-medium {{ request('status') === $st->value ? 'bg-orange-600 text-white border-orange-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                    {{ $st->value }}
                </a>
            @endforeach
        </div>

        <button @click="newContentModal = true" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold shadow-xs flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Schedule Content</span>
        </button>
    </div>

    <!-- Content Items Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($items as $item)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 hover:border-orange-200 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-800">
                            {{ $item->platform->value }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $item->status->badgeClasses() }}">
                            {{ $item->status->value }}
                        </span>
                    </div>

                    <h4 class="font-bold text-sm text-slate-900 mb-1.5">
                        {{ $item->title }}
                    </h4>

                    @if ($item->topic)
                        <div class="text-xs text-slate-500 mb-2">
                            Topic: <span class="font-medium text-slate-700">{{ $item->topic }}</span>
                        </div>
                    @endif

                    @if ($item->caption)
                        <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100 line-clamp-3 mb-3">
                            {{ $item->caption }}
                        </p>
                    @endif

                    <!-- Conversion / Results metrics -->
                    <div class="grid grid-cols-3 gap-2 bg-slate-50/70 p-2 rounded-xl text-center text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase">Reach</span>
                            <span class="font-bold text-slate-800">{{ $item->reach ?? 0 }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase">Leads</span>
                            <span class="font-bold text-orange-600">{{ $item->leads_generated ?? 0 }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase">Converted</span>
                            <span class="font-bold text-emerald-600">{{ $item->conversions ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400">📅 {{ $item->scheduled_at->format('d M, h:i A') }}</span>

                    <div class="flex items-center gap-1">
                        <button @click="currentItem = {{ $item->toJson() }}; editModal = true" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">
                            Update Metrics
                        </button>
                        <form action="{{ route('marketing.content-calendar.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Remove content item?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 p-12 text-center text-slate-400 text-xs">
                No marketing content scheduled. Click "Schedule Content" to plan your upcoming 30 days!
            </div>
        @endforelse
    </div>

    @if ($items->hasPages())
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
            {{ $items->links() }}
        </div>
    @endif

    <!-- New Content Item Modal -->
    <div x-show="newContentModal" 
         x-transition 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="newContentModal = false" class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900">Schedule New Content</h3>
                <button @click="newContentModal = false" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
            </div>

            <form action="{{ route('marketing.content-calendar.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Content Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. 3 Tips for High-Margin Dropshipping" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Platform <span class="text-rose-500">*</span></label>
                        <select name="platform" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            @foreach ($platforms as $platform)
                                <option value="{{ $platform->value }}">{{ $platform->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" {{ $status->value === 'Planned' ? 'selected' : '' }}>{{ $status->value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Topic</label>
                        <input type="text" name="topic" placeholder="e.g. Dropshipping & Growth" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Scheduled Date & Time <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="scheduled_at" required value="{{ now()->addDays(2)->setHour(20)->format('Y-m-d\TH:i') }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Caption / Copy</label>
                    <textarea name="caption" rows="3" placeholder="Post caption or script draft..." class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Call to Action (CTA)</label>
                    <input type="text" name="cta" placeholder="e.g. Send WhatsApp or Comment 'INFO'" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="newContentModal = false" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl shadow-xs">Save Content</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Metrics Modal -->
    <div x-show="editModal" 
         x-transition 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="editModal = false" class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-3">Update Metrics & Status</h3>

            <form :action="`/marketing/content-calendar/${currentItem.id}`" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                    <select name="status" x-model="currentItem.status" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}">{{ $status->value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Total Reach</label>
                        <input type="number" name="reach" x-model="currentItem.reach" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Engagement</label>
                        <input type="number" name="engagement" x-model="currentItem.engagement" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Leads Generated</label>
                        <input type="number" name="leads_generated" x-model="currentItem.leads_generated" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Conversions</label>
                        <input type="number" name="conversions" x-model="currentItem.conversions" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editModal = false" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl shadow-xs">Update Item</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

