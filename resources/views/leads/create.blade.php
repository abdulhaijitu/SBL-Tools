@extends('layouts.app')

@section('page-title', 'Add New Lead')
@section('page-subtitle', 'Fast Lead Capture (< 30 Seconds Entry)')

@section('content')
<div class="max-w-2xl mx-auto" 
     x-data="{
        name: '{{ old('name', '') }}',
        mobile: '{{ old('mobile', '') }}',
        whatsapp: '{{ old('whatsapp', '') }}',
        email: '{{ old('email', '') }}',
        photoData: '{{ old('photo', '') }}',
        sameAsMobile: true,

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

        handlePhotoSelect(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const maxDim = 256;
                    let w = img.width;
                    let h = img.height;
                    if (w > h) {
                        if (w > maxDim) { h = Math.round((h * maxDim) / w); w = maxDim; }
                    } else {
                        if (h > maxDim) { w = Math.round((w * maxDim) / h); h = maxDim; }
                    }
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, w, h);
                    this.photoData = canvas.toDataURL('image/jpeg', 0.85);
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        },

        removePhoto() {
            this.photoData = '';
            if (this.$refs.photoInput) this.$refs.photoInput.value = '';
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

            <!-- Photo Upload Box -->
            <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                <div class="relative w-14 h-14 rounded-2xl bg-orange-100 text-orange-700 font-bold text-xl flex items-center justify-center flex-shrink-0 shadow-xs overflow-hidden border border-orange-200/60">
                    <template x-if="photoData">
                        <img :src="photoData" alt="Lead Photo" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!photoData">
                        <span x-text="name ? name.charAt(0).toUpperCase() : '📷'"></span>
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-bold text-slate-800 mb-0.5">Lead Photo / Avatar</label>
                    <p class="text-[11px] text-slate-500 mb-2">Upload profile picture or business card photo (optional)</p>
                    <div class="flex items-center gap-2">
                        <label class="cursor-pointer px-3 py-1.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors shadow-xs">
                            <span>Choose Photo</span>
                            <input type="file" x-ref="photoInput" @change="handlePhotoSelect($event)" accept="image/*" class="hidden">
                        </label>
                        <button type="button" x-show="photoData" @click="removePhoto()" class="px-2 py-1 text-xs text-rose-600 hover:text-rose-800 font-semibold transition-colors" x-cloak>
                            Remove
                        </button>
                    </div>
                    <input type="hidden" name="photo" :value="photoData">
                </div>
            </div>

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
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Mobile Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="tel" 
                           name="mobile" 
                           required 
                           inputmode="tel"
                           autocomplete="tel"
                           x-model="mobile"
                           @input="onMobileChange()"
                           placeholder="017xxxxxxxx" 
                           class="w-full text-base sm:text-sm rounded-xl border @error('mobile') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:border-orange-500 focus:ring-1 focus:ring-orange-500 px-3.5 py-2.5 font-medium">
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
                        <label class="inline-flex items-center gap-1 text-[11px] text-slate-500 cursor-pointer select-none">
                            <input type="checkbox" 
                                   x-model="sameAsMobile" 
                                   @change="toggleSameAsMobile()"
                                   class="rounded text-orange-600 focus:ring-orange-500 w-3.5 h-3.5 border-slate-300">
                            <span>Same as Mobile</span>
                        </label>
                    </div>
                    <input type="tel" 
                           name="whatsapp" 
                           inputmode="tel"
                           x-model="whatsapp"
                           placeholder="01xxxxxxxxx" 
                           class="w-full text-base sm:text-sm rounded-xl border border-slate-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 px-3.5 py-2.5">
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

</div>
@endsection

