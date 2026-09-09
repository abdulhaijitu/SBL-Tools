@php
    // In production, render strictly the approved FEATURE-FOCUSED component (Variant B)
    // Developers can inspect preview variants via ?preview=1 or ?variant=a|b|c|all
    $isDevPreview = request()->has('preview') || request()->filled('variant');
    $activeVariant = in_array(request('variant'), ['a', 'b', 'c', 'all']) ? request('variant') : 'b';
@endphp

<section 
    id="sbl-membership-section"
    class="space-y-6 pt-2 pb-4"
    x-data="{
        variant: @js($activeVariant),
        modalOpen: false,
        activeAccordion: 'features',
        triggerButton: null,
        
        openModal(sourceVariant = 'feature', location = 'card') {
            this.triggerButton = document.activeElement;
            this.modalOpen = true;
            if (window.trackToolkitEvent) {
                window.trackToolkitEvent('toolkit_package_details_click', {
                    component_variant: sourceVariant,
                    cta_location: location
                });
                window.trackToolkitEvent('toolkit_package_modal_open', {
                    component_variant: sourceVariant,
                    cta_location: location
                });
            }
            this.$nextTick(() => {
                const closeBtn = document.getElementById('pkg-modal-close-btn');
                if (closeBtn) closeBtn.focus();
            });
        },
        
        closeModal(sourceVariant = 'feature') {
            this.modalOpen = false;
            if (window.trackToolkitEvent) {
                window.trackToolkitEvent('toolkit_package_modal_close', {
                    component_variant: sourceVariant,
                    cta_location: 'modal'
                });
            }
            if (this.triggerButton && typeof this.triggerButton.focus === 'function') {
                this.triggerButton.focus();
            }
        },

        trackJoin(sourceVariant = 'feature', location = 'card') {
            if (window.trackToolkitEvent) {
                window.trackToolkitEvent('toolkit_package_join_click', {
                    component_variant: sourceVariant,
                    cta_location: location
                });
            }
        },

        trackTerms(sourceVariant = 'feature', location = 'card') {
            if (window.trackToolkitEvent) {
                window.trackToolkitEvent('toolkit_package_terms_click', {
                    component_variant: sourceVariant,
                    cta_location: location
                });
            }
        },

        toggleAccordion(id) {
            this.activeAccordion = this.activeAccordion === id ? null : id;
        }
    }"
    @keydown.escape.window="if (modalOpen) closeModal('feature')"
>

    @if($isDevPreview)
    <!-- DEVELOPER PREVIEW SWITCHER BAR (Only visible with ?preview=1 or ?variant=...) -->
    <div class="bg-white/95 backdrop-blur-xs border border-slate-200/90 rounded-2xl p-3 sm:p-4 shadow-xs print:hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold tracking-wider uppercase bg-[#FDEDE7] text-[#AB2925] border border-[#FDEDE7]">
                    UI VARIANT PREVIEW
                </span>
                <span class="text-xs text-slate-500 hidden sm:inline">SBL Membership Package (৳১০,০০০)</span>
            </div>
            
            <!-- Switcher Tabs -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl overflow-x-auto text-xs font-semibold no-scrollbar">
                <button 
                    type="button" 
                    @click="variant = 'b'"
                    :class="variant === 'b' ? 'bg-white text-[#AB2925] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap min-h-[36px]"
                    aria-label="Show Variant B (Feature-Focused Card - Recommended)">
                    <span>Variant B</span>
                    <span class="px-1.5 py-0.5 bg-[#AB2925] text-white text-[10px] font-bold rounded-full">Recommended</span>
                </button>
                <button 
                    type="button" 
                    @click="variant = 'a'"
                    :class="variant === 'a' ? 'bg-white text-[#AB2925] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap min-h-[36px]"
                    aria-label="Show Variant A (Compact Card)">
                    <span>Variant A</span>
                    <span class="text-slate-400 font-normal">Compact</span>
                </button>
                <button 
                    type="button" 
                    @click="variant = 'c'"
                    :class="variant === 'c' ? 'bg-white text-[#AB2925] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap min-h-[36px]"
                    aria-label="Show Variant C (Long Details Panel)">
                    <span>Variant C</span>
                    <span class="text-slate-400 font-normal">Long Panel</span>
                </button>
                <button 
                    type="button" 
                    @click="variant = 'all'"
                    :class="variant === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1 whitespace-nowrap min-h-[36px]"
                    aria-label="Show All 3 Variants for Comparison">
                    <span>Compare All 3</span>
                </button>
            </div>
        </div>
    </div>

    <!-- PREVIEW: VARIANT A WRAPPER -->
    <div x-show="variant === 'a' || variant === 'all'" x-cloak>
        <div class="mb-3" x-show="variant === 'all'">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Variant A · Compact Card (Max 440px)</span>
        </div>
        @include('toolkit.partials.package-variant-a')
    </div>

    <!-- PREVIEW: VARIANT B WRAPPER (Recommended) -->
    <div x-show="variant === 'b' || variant === 'all'" x-cloak>
        <div class="mb-3" x-show="variant === 'all'">
            <span class="text-xs font-bold uppercase tracking-wider text-[#AB2925]">Variant B · Feature-Focused Card (Recommended Production Variant)</span>
        </div>
        @include('toolkit.partials.package-variant-b')
    </div>

    <!-- PREVIEW: VARIANT C WRAPPER -->
    <div x-show="variant === 'c' || variant === 'all'" x-cloak>
        <div class="mb-3" x-show="variant === 'all'">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Variant C · Long Details Panel</span>
        </div>
        @include('toolkit.partials.package-variant-c')
    </div>
    @else
    <!-- PRODUCTION VIEW: Approved Feature-Focused Component Only -->
    @include('toolkit.partials.package-variant-b')
    @endif

    <!-- SHARED ACCESSIBLE DETAILS MODAL -->
    @include('toolkit.partials.package-modal')

</section>
