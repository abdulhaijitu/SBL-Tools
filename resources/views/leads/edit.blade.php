@extends('layouts.app')

@section('page-title', 'Edit Lead: ' . $lead->name)
@section('page-subtitle', 'Update Lead Information & Scoring')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">

        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
            <div>
                <h2 class="text-base font-bold text-slate-900">Edit Lead Record</h2>
                <p class="text-xs text-slate-500">Update contact, pipeline stage, and qualification details.</p>
            </div>
            <a href="{{ route('leads.show', $lead->id) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900">
                Back to Profile
            </a>
        </div>

        <form action="{{ route('leads.update', $lead->id) }}" method="POST" class="space-y-5" x-data="{ manualScore: {{ $lead->is_manual_score ? 'true' : 'false' }} }">
            @csrf
            @method('PUT')

            <!-- Name & Mobile -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Full Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" required value="{{ old('name', $lead->name) }}" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Mobile Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="tel" name="mobile" required value="{{ old('mobile', $lead->mobile) }}" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
                </div>
            </div>

            <!-- WhatsApp & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">WhatsApp</label>
                    <input type="tel" name="whatsapp" value="{{ old('whatsapp', $lead->whatsapp) }}" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $lead->email) }}" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
                </div>
            </div>

            <!-- Source & Stage -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Lead Source <span class="text-rose-500">*</span>
                    </label>
                    <select name="lead_source_id" required class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5 bg-white">
                        @foreach ($sources as $source)
                            <option value="{{ $source->id }}" {{ old('lead_source_id', $lead->lead_source_id) == $source->id ? 'selected' : '' }}>
                                {{ $source->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Pipeline Stage <span class="text-rose-500">*</span>
                    </label>
                    <select name="stage" required class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5 bg-white">
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->value }}" {{ old('stage', $lead->stage->value) === $stage->value ? 'selected' : '' }}>
                                {{ $stage->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Quick Tag & Lead Score Override -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Quick Tag</label>
                    <input type="text" name="lead_tag" value="{{ old('lead_tag', $lead->lead_tag) }}" placeholder="e.g. P1, E1, A1, I1, B1" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-semibold text-slate-600">Lead Score (0-100)</label>
                        <label class="inline-flex items-center gap-1.5 text-[11px] text-slate-500 cursor-pointer">
                            <input type="checkbox" name="is_manual_score" x-model="manualScore" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                            <span>Manual Override</span>
                        </label>
                    </div>
                    <input type="number" name="score" min="0" max="100" :disabled="!manualScore" value="{{ old('score', $lead->score) }}" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2 disabled:bg-slate-100">
                </div>
            </div>

            <!-- Interests -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Interests</label>
                <div class="flex flex-wrap gap-2">
                    @php
                        $availableInterests = ['Invest', 'Affiliate and Networking'];
                        $currentInterests = $lead->interest_types ?? [];
                        $allInterests = array_unique(array_merge($availableInterests, $currentInterests));
                    @endphp
                    @foreach ($allInterests as $interest)
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium cursor-pointer hover:bg-orange-50/50 transition-colors has-checked:bg-orange-600 has-checked:text-white has-checked:border-orange-600">
                            <input type="checkbox" name="interest_types[]" value="{{ $interest }}" {{ in_array($interest, $currentInterests) ? 'checked' : '' }} class="hidden">
                            <span>{{ $interest }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Location & Profession -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Location</label>
                    <input type="text" name="location" value="{{ old('location', $lead->location) }}" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Profession / Business</label>
                    <input type="text" name="profession_or_business" value="{{ old('profession_or_business', $lead->profession_or_business) }}" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 p-3">{{ old('notes', $lead->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <button type="button" 
                        onclick="if(confirm('Are you sure you want to delete this lead?')) document.getElementById('delete-lead-form-{{ $lead->id }}').submit();"
                        class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                    Delete Lead
                </button>

                <div class="flex items-center gap-2">
                    <a href="{{ route('leads.show', $lead->id) }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-xs">Save Changes</button>
                </div>
            </div>

        </form>

        <form id="delete-lead-form-{{ $lead->id }}" action="{{ route('leads.destroy', $lead->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection

