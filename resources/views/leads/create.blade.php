@extends('layouts.app')

@section('page-title', 'Add New Lead')
@section('page-subtitle', 'Fast Lead Capture (< 30 Seconds Entry)')

@section('content')
<div class="max-w-2xl mx-auto" 
     x-data="{
        name: '{{ old('name', '') }}',
        mobile: '{{ old('mobile', '') }}',
        whatsapp: '{{ old('whatsapp', '') }}',
        sameAsMobile: false,
        contactPickerOpen: false,
        pickerTarget: 'mobile',
        activeTab: 'sbl',
        searchTerm: '',
        devicePickerSupported: ('contacts' in navigator && 'ContactsManager' in window),

        toggleSameAsMobile() {
            if (this.sameAsMobile) {
                this.whatsapp = this.mobile;
            }
        },

        onMobileChange() {
            if (this.sameAsMobile) {
                this.whatsapp = this.mobile;
            }
        },

        openContactPicker(target) {
            this.pickerTarget = target;
            this.contactPickerOpen = true;
            this.searchTerm = '';
        },

        selectContact(contactName, phone, wa) {
            const chosen = (this.pickerTarget === 'whatsapp') ? (wa || phone || '') : (phone || wa || '');
            if (this.pickerTarget === 'mobile') {
                this.mobile = chosen;
                if (!this.name && contactName) {
                    this.name = contactName;
                }
                if (this.sameAsMobile) {
                    this.whatsapp = chosen;
                }
            } else if (this.pickerTarget === 'whatsapp') {
                this.whatsapp = chosen;
            }
            this.contactPickerOpen = false;
        },

        async pickFromPhonebook() {
            if ('contacts' in navigator && 'ContactsManager' in window) {
                try {
                    const props = ['name', 'tel'];
                    const contacts = await navigator.contacts.select(props, { multiple: false });
                    if (contacts && contacts.length > 0) {
                        const c = contacts[0];
                        const pickedTel = (c.tel && c.tel.length > 0) ? c.tel[0].replace(/\s+/g, '') : '';
                        const pickedName = (c.name && c.name.length > 0) ? c.name[0] : '';
                        this.selectContact(pickedName, pickedTel, pickedTel);
                    }
                } catch (err) {
                    console.warn('Native contact picker error:', err);
                }
            } else {
                alert('Device phonebook picker is supported on mobile Chrome/Android. Please select from the directory list below.');
            }
        }
     }">

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

        @if (isset($errors) && $errors->any())
            <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-rose-900">
                    <span>⚠️</span> Please correct the following errors:
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('leads.store') }}" method="POST" class="space-y-5">
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
                           autocomplete="name"
                           x-model="name"
                           placeholder="e.g. Rafiqul Islam" 
                           class="w-full text-base sm:text-sm rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:border-orange-500 focus:ring-1 focus:ring-orange-500 px-3.5 py-2.5">
                    @error('name')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Mobile Number <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" 
                                @click="openContactPicker('mobile')" 
                                class="text-[11px] font-semibold text-orange-600 hover:text-orange-700 flex items-center gap-1 hover:underline">
                            <span>📖 কন্টাক্ট থেকে নিন</span>
                        </button>
                    </div>
                    <div class="relative">
                        <input type="tel" 
                               name="mobile" 
                               required 
                               inputmode="tel"
                               autocomplete="tel"
                               x-model="mobile"
                               @input="onMobileChange()"
                               placeholder="017xxxxxxxx" 
                               class="w-full text-base sm:text-sm rounded-xl border @error('mobile') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:border-orange-500 focus:ring-1 focus:ring-orange-500 pl-3.5 pr-9 py-2.5">
                        <button type="button" 
                                @click="openContactPicker('mobile')" 
                                title="Select from Contacts"
                                class="absolute right-2.5 top-2.5 text-slate-400 hover:text-orange-600 p-0.5">
                            📞
                        </button>
                    </div>
                    @error('mobile')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- WhatsApp & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-slate-600">
                            WhatsApp Number
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="openContactPicker('whatsapp')" 
                                    class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-0.5 hover:underline">
                                <span>📖 সিলেক্ট</span>
                            </button>
                            <label class="inline-flex items-center gap-1 text-[11px] text-slate-500 cursor-pointer select-none">
                                <input type="checkbox" 
                                       x-model="sameAsMobile" 
                                       @change="toggleSameAsMobile()"
                                       class="rounded text-orange-600 focus:ring-orange-500 w-3.5 h-3.5 border-slate-300">
                                <span>Same</span>
                            </label>
                        </div>
                    </div>
                    <div class="relative">
                        <input type="tel" 
                               name="whatsapp" 
                               inputmode="tel"
                               x-model="whatsapp"
                               placeholder="01xxxxxxxxx" 
                               class="w-full text-base sm:text-sm rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 pl-3.5 pr-9 py-2.5">
                        <button type="button" 
                                @click="openContactPicker('whatsapp')" 
                                title="Select WhatsApp from Contacts"
                                class="absolute right-2.5 top-2.5 text-slate-400 hover:text-emerald-600 p-0.5">
                            💬
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        Email Address
                    </label>
                    <input type="email" 
                           name="email" 
                           inputmode="email"
                           autocomplete="email"
                           value="{{ old('email') }}"
                           placeholder="name@example.com" 
                           class="w-full text-base sm:text-sm rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 px-3.5 py-2.5">
                </div>
            </div>

            <!-- Lead Source & Stage -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Lead Source <span class="text-rose-500">*</span>
                    </label>
                    <select name="lead_source_id" required class="w-full text-base sm:text-sm rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 px-3.5 py-2.5 bg-white">
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
                    <select name="stage" class="w-full text-base sm:text-sm rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 px-3.5 py-2.5 bg-white">
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->value }}" {{ old('stage', 'new') == $stage->value ? 'selected' : '' }}>
                                {{ $stage->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Quick Interest Types -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Interests (Select all that apply)
                </label>
                <div class="flex flex-wrap gap-2">
                    @php
                        $availableInterests = ['Invest', 'Affiliate and Networking'];
                    @endphp
                    @foreach ($availableInterests as $interest)
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium cursor-pointer hover:bg-orange-50/50 transition-colors has-checked:bg-orange-600 has-checked:text-white has-checked:border-orange-600 active:scale-95">
                            <input type="checkbox" name="interest_types[]" value="{{ $interest }}" class="hidden">
                            <span>{{ $interest }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Schedule Immediate Next Action -->
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
                    <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Dhaka, Mirpur" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Profession / Business</label>
                    <input type="text" name="profession_or_business" value="{{ old('profession_or_business') }}" placeholder="e.g. Retailer, Student" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Initial Notes</label>
                <textarea name="notes" rows="2" placeholder="Any specific requirements or comments..." class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 p-3">{{ old('notes') }}</textarea>
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

    <!-- Contact Directory Selection Modal -->
    <div x-show="contactPickerOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
        
        <div @click.away="contactPickerOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh]">
            
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <span>📖</span>
                        <span x-text="pickerTarget === 'whatsapp' ? 'Select WhatsApp Number' : 'Select Mobile Number'"></span>
                    </h3>
                    <p class="text-[11px] text-slate-500">Pick from SBL contacts, team directory, or device phonebook.</p>
                </div>
                <button @click="contactPickerOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg text-lg leading-none">&times;</button>
            </div>

            <!-- Native Phonebook / Search Bar -->
            <div class="p-4 border-b border-slate-100 space-y-3 bg-white">
                <template x-if="devicePickerSupported">
                    <button type="button" 
                            @click="pickFromPhonebook()"
                            class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition-colors">
                        <span>📱</span>
                        <span>Open Phone Contacts / Phonebook</span>
                    </button>
                </template>

                <div class="relative">
                    <input type="text" 
                           x-model="searchTerm" 
                           placeholder="Search contact by name, phone, or department..." 
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-orange-500 bg-slate-50">
                    <span class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</span>
                </div>

                <!-- Tabs -->
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-semibold">
                    <button type="button" 
                            @click="activeTab = 'sbl'" 
                            :class="activeTab === 'sbl' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="flex-1 py-1.5 rounded-lg transition-all text-center">
                        🏢 SBL Helpline & Offices ({{ count($sblContacts ?? []) }})
                    </button>
                    <button type="button" 
                            @click="activeTab = 'team'" 
                            :class="activeTab === 'team' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="flex-1 py-1.5 rounded-lg transition-all text-center">
                        👥 Team Members ({{ count($teamMembers ?? []) }})
                    </button>
                </div>
            </div>

            <!-- Contact List -->
            <div class="overflow-y-auto p-4 space-y-2 flex-1 divide-y divide-slate-100">
                <!-- SBL Official Contacts -->
                <template x-if="activeTab === 'sbl'">
                    <div class="space-y-2">
                        @forelse($sblContacts ?? [] as $c)
                            <div x-show="!searchTerm || '{{ strtolower($c->department . ' ' . $c->contact_person . ' ' . $c->phone . ' ' . $c->whatsapp) }}'.includes(searchTerm.toLowerCase())"
                                 @click="selectContact('{{ addslashes($c->department) }}', '{{ $c->phone }}', '{{ $c->whatsapp }}')"
                                 class="p-3 rounded-xl hover:bg-orange-50/60 border border-slate-100 hover:border-orange-200 transition-colors cursor-pointer flex items-center justify-between gap-3 group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-base flex items-center justify-center flex-shrink-0 group-hover:bg-orange-100">
                                        {{ $c->icon ?: '📞' }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-xs truncate group-hover:text-orange-600">
                                            {{ $c->department }}
                                        </div>
                                        @if($c->contact_person)
                                            <div class="text-[11px] text-slate-500 truncate">{{ $c->contact_person }}</div>
                                        @endif
                                        <div class="text-[11px] font-mono text-slate-600 mt-0.5">
                                            📞 {{ $c->phone }} @if($c->whatsapp && $c->whatsapp !== $c->phone) • 💬 {{ $c->whatsapp }} @endif
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 group-hover:bg-orange-600 group-hover:text-white text-slate-700 text-[11px] font-bold flex-shrink-0 transition-colors">
                                    Select
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-6 text-xs text-slate-400">No SBL Contacts configured yet.</div>
                        @endforelse
                    </div>
                </template>

                <!-- Team Members -->
                <template x-if="activeTab === 'team'">
                    <div class="space-y-2">
                        @forelse($teamMembers ?? [] as $m)
                            <div x-show="!searchTerm || '{{ strtolower($m->name . ' ' . $m->phone . ' ' . $m->designation) }}'.includes(searchTerm.toLowerCase())"
                                 @click="selectContact('{{ addslashes($m->name) }}', '{{ $m->phone }}', '{{ $m->phone }}')"
                                 class="p-3 rounded-xl hover:bg-orange-50/60 border border-slate-100 hover:border-orange-200 transition-colors cursor-pointer flex items-center justify-between gap-3 group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-orange-400 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ substr($m->name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-xs truncate group-hover:text-orange-600">
                                            {{ $m->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 truncate">{{ $m->designation ?: 'Team Member' }}</div>
                                        <div class="text-[11px] font-mono text-slate-600 mt-0.5">
                                            📞 {{ $m->phone }}
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 group-hover:bg-orange-600 group-hover:text-white text-slate-700 text-[11px] font-bold flex-shrink-0 transition-colors">
                                    Select
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-6 text-xs text-slate-400">No Team Members with phone numbers found.</div>
                        @endforelse
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="px-4 py-3 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="button" 
                        @click="contactPickerOpen = false" 
                        class="px-4 py-1.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-white">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

