@extends('layouts.app')

@section('page-title', 'Edit Lead: ' . $lead->name)
@section('page-subtitle', 'Update Lead Information')

@section('content')
<div class="max-w-2xl mx-auto"
     x-data="{
        name: '{{ old('name', $lead->name) }}',
        mobile: '{{ old('mobile', $lead->mobile) }}',
        whatsapp: '{{ old('whatsapp', $lead->whatsapp) }}',
        photoData: '{{ old('photo', $lead->photo ?? '') }}',
        sameAsMobile: false,

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
                <h2 id="lead-edit-title" class="text-base font-bold text-slate-900">Edit Lead Record</h2>
                <p class="text-xs text-slate-500">Update contact, pipeline stage, and qualification details.</p>
            </div>
            <a id="lead-edit-back-link" href="{{ route('leads.show', $lead->id) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900">
                Back to Profile
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

        <form id="lead-edit-form" action="{{ route('leads.update', $lead->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

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
                            <span>Change Photo</span>
                            <input type="file" x-ref="photoInput" @change="handlePhotoSelect($event)" accept="image/*" class="hidden">
                        </label>
                        <button type="button" x-show="photoData" @click="removePhoto()" class="px-2 py-1 text-xs text-rose-600 hover:text-rose-800 font-semibold transition-colors" x-cloak>
                            Remove
                        </button>
                    </div>
                    <input type="hidden" name="photo" :value="photoData">
                </div>
            </div>

            <!-- Name & Mobile -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Full Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" required x-model="name" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
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
                           x-model="mobile" 
                           @input="onMobileChange()"
                           class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
                    @error('mobile')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- WhatsApp & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-slate-600">WhatsApp</label>
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
                           class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3.5 py-2.5">
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

            <!-- Interests -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Interests</label>
                <div class="flex flex-wrap gap-2">
                    @php
                        $availableInterests = ['Invest', 'Affiliate and Networking'];
                        $currentInterests = $lead->interest_types ?? [];
                    @endphp
                    @foreach ($availableInterests as $interest)
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
                <button id="lead-edit-delete-btn"
                        type="button" 
                        onclick="if(confirm('Are you sure you want to delete this lead?')) document.getElementById('lead-edit-delete-form').submit();"
                        class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                    Delete Lead
                </button>

                <div class="flex items-center gap-2">
                    <a id="lead-edit-cancel-link" href="{{ route('leads.show', $lead->id) }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-xs">Save Changes</button>
                </div>
            </div>

        </form>

        <form id="lead-edit-delete-form" action="{{ route('leads.destroy', $lead->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>

</div>
@endsection

