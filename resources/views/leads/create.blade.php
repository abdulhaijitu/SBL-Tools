@extends('layouts.app')

@section('page-title', 'Add New Lead')
@section('page-subtitle', 'Fast Lead Capture (< 30 Seconds Entry)')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">

        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
            <div>
                <h2 class="text-base font-bold text-slate-900">New Lead Information</h2>
                <p class="text-xs text-slate-500">Quickly log inbound inquiry and schedule immediate next action.</p>
            </div>
            <a href="{{ route('leads.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900">
                Cancel
            </a>
        </div>

        <form action="{{ route('leads.store') }}" method="POST" class="space-y-5" x-data="{ quickTag: '' }">
            @csrf

            <!-- Primary Contact Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Full Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           required 
                           autofocus
                           value="{{ old('name') }}"
                           placeholder="e.g. Rafiqul Islam" 
                           class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 px-3.5 py-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Mobile Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="tel" 
                           name="mobile" 
                           required 
                           value="{{ old('mobile') }}"
                           placeholder="017xxxxxxxx" 
                           class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 px-3.5 py-2.5">
                </div>
            </div>

            <!-- WhatsApp & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        WhatsApp (if different)
                    </label>
                    <input type="tel" 
                           name="whatsapp" 
                           value="{{ old('whatsapp') }}"
                           placeholder="01xxxxxxxxx" 
                           class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 px-3.5 py-2.5">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        Email Address
                    </label>
                    <input type="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           placeholder="name@example.com" 
                           class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 px-3.5 py-2.5">
                </div>
            </div>

            <!-- Lead Source & Stage -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Lead Source <span class="text-rose-500">*</span>
                    </label>
                    <select name="lead_source_id" required class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 px-3.5 py-2.5 bg-white">
                        @foreach ($sources as $source)
                            <option value="{{ $source->id }}" {{ old('lead_source_id') == $source->id ? 'selected' : '' }}>
                                {{ $source->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        Pipeline Stage
                    </label>
                    <select name="stage" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 px-3.5 py-2.5 bg-white">
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->value }}" {{ old('stage') == $stage->value ? 'selected' : '' }}>
                                {{ $stage->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Quick Interest Types (Multiple Buttons) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Interests (Select all that apply)
                </label>
                <div class="flex flex-wrap gap-2">
                    @php
                        $availableInterests = ['Product', 'E-commerce', 'Dropshipping', 'Affiliate', 'Network', 'Investment', 'Partnership'];
                    @endphp
                    @foreach ($availableInterests as $interest)
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium cursor-pointer hover:bg-orange-50/50 transition-colors has-checked:bg-orange-600 has-checked:text-white has-checked:border-orange-600">
                            <input type="checkbox" name="interest_types[]" value="{{ $interest }}" class="hidden">
                            <span>{{ $interest }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Quick Tag Selector (P1, E1, A1, I1, B1) -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Quick Tag (Section 5)
                </label>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="lead_tag" :value="quickTag">
                    <button type="button" @click="quickTag = quickTag === 'P1' ? '' : 'P1'" 
                            :class="quickTag === 'P1' ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                        P1: Product
                    </button>
                    <button type="button" @click="quickTag = quickTag === 'E1' ? '' : 'E1'" 
                            :class="quickTag === 'E1' ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                        E1: E-commerce
                    </button>
                    <button type="button" @click="quickTag = quickTag === 'A1' ? '' : 'A1'" 
                            :class="quickTag === 'A1' ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                        A1: Affiliate/Network
                    </button>
                    <button type="button" @click="quickTag = quickTag === 'I1' ? '' : 'I1'" 
                            :class="quickTag === 'I1' ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                        I1: Investment
                    </button>
                    <button type="button" @click="quickTag = quickTag === 'B1' ? '' : 'B1'" 
                            :class="quickTag === 'B1' ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                        B1: Partnership
                    </button>
                </div>
            </div>

            <!-- Schedule Immediate Next Action (Section 9) -->
            <div class="bg-orange-50/50 border border-orange-200/80 rounded-2xl p-4 space-y-3">
                <div class="text-xs font-bold text-orange-900 flex items-center gap-1.5">
                    <span>⚡</span> Schedule Immediate Next Action
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Action Type</label>
                        <select name="next_action_type" class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2 bg-white">
                            <option value="Follow-up Call">Follow-up Call</option>
                            <option value="WhatsApp Discussion">WhatsApp Discussion</option>
                            <option value="Online Presentation">Online Presentation</option>
                            <option value="Send Catalog/Info">Send Catalog / Pricing</option>
                            <option value="In-person Meeting">In-person Meeting</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Due Date & Time</label>
                        <input type="datetime-local" 
                               name="next_action_at" 
                               value="{{ now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d\TH:i') }}"
                               class="w-full text-xs rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2 bg-white">
                    </div>
                </div>
            </div>

            <!-- Location & Initial Note -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Location / City</label>
                    <input type="text" name="location" placeholder="e.g. Dhaka, Mirpur" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Profession / Business</label>
                    <input type="text" name="profession_or_business" placeholder="e.g. Retailer, Student" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Initial Notes</label>
                <textarea name="notes" rows="2" placeholder="Any specific requirements or comments..." class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 p-3"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-orange-600 hover:bg-orange-700 active:scale-[0.99] text-white font-bold text-sm shadow-md shadow-orange-600/30 transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Save Lead & Schedule Action</span>
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

