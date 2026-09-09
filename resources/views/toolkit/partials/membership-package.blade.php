@php
    // SBL 3-Tier Package Architecture:
    // 1. Starter Membership: ৳১০,০০০
    // 2. National Dropshipping: ৳১,২০,০০০ (100 BV)
    // 3. International Dropshipping: ৳৫,৫০,০০০ (500 BV)
    $activeTier = in_array(request('tier'), ['all', 'starter', 'national', 'international']) ? request('tier') : 'all';
@endphp

<section 
    id="sbl-packages-system"
    class="space-y-6 pt-1 pb-4"
    x-data="{
        selectedTier: @js($activeTier),
        modalOpen: false,
        nationalModalOpen: false,
        internationalModalOpen: false,
        activeAccordion: 'features',
        triggerButton: null,
        
        openModal(sourceVariant = 'starter', location = 'card') {
            this.triggerButton = document.activeElement;
            this.modalOpen = true;
            if (window.trackToolkitEvent) {
                window.trackToolkitEvent('toolkit_package_details_click', {
                    package_id: 'membership-10000',
                    package_name: 'SBL Membership Package',
                    package_price_bdt: 10000,
                    component_variant: sourceVariant,
                    cta_location: location
                });
                window.trackToolkitEvent('toolkit_package_modal_open', {
                    package_id: 'membership-10000',
                    package_name: 'SBL Membership Package',
                    package_price_bdt: 10000,
                    component_variant: sourceVariant,
                    cta_location: location
                });
            }
            this.$nextTick(() => {
                const closeBtn = document.getElementById('pkg-modal-close-btn');
                if (closeBtn) closeBtn.focus();
            });
        },
        
        closeModal(sourceVariant = 'starter') {
            this.modalOpen = false;
            if (window.trackToolkitEvent) {
                window.trackToolkitEvent('toolkit_package_modal_close', {
                    package_id: 'membership-10000',
                    component_variant: sourceVariant,
                    cta_location: 'modal'
                });
            }
            if (this.triggerButton && typeof this.triggerButton.focus === 'function') {
                this.triggerButton.focus();
            }
        },

        openNationalModal(location = 'card') {
            this.triggerButton = document.activeElement;
            this.nationalModalOpen = true;
            if (window.trackToolkitEvent) {
                window.trackToolkitEvent('toolkit_package_details_click', {
                    package_id: 'national-120000',
                    package_name: 'National Package',
                    package_price_bdt: 120000,
                    component_variant: 'national',
                    cta_location: location
                });
                window.trackToolkitEvent('toolkit_package_modal_open', {
                    package_id: 'national-120000',
                    package_name: 'National Package',
                    package_price_bdt: 120000,
                    component_variant: 'national',
                    cta_location: location
                });
            }
        },

        closeNationalModal() {
            this.nationalModalOpen = false;
            if (window.trackToolkitEvent) {
                window.trackToolkitEvent('toolkit_package_modal_close', {
                    package_id: 'national-120000',
                    component_variant: 'national',
                    cta_location: 'modal'
                });
            }
            if (this.triggerButton && typeof this.triggerButton.focus === 'function') {
                this.triggerButton.focus();
            }
        },

        openInternationalModal(location = 'card') {
            this.triggerButton = document.activeElement;
            this.internationalModalOpen = true;
            if (window.trackToolkitEvent) {
                window.trackToolkitEvent('toolkit_package_details_click', {
                    package_id: 'international-550000',
                    package_name: 'International Package',
                    package_price_bdt: 550000,
                    component_variant: 'international',
                    cta_location: location
                });
                window.trackToolkitEvent('toolkit_package_modal_open', {
                    package_id: 'international-550000',
                    package_name: 'International Package',
                    package_price_bdt: 550000,
                    component_variant: 'international',
                    cta_location: location
                });
            }
        },

        closeInternationalModal() {
            this.internationalModalOpen = false;
            if (window.trackToolkitEvent) {
                window.trackToolkitEvent('toolkit_package_modal_close', {
                    package_id: 'international-550000',
                    component_variant: 'international',
                    cta_location: 'modal'
                });
            }
            if (this.triggerButton && typeof this.triggerButton.focus === 'function') {
                this.triggerButton.focus();
            }
        },

        openCalculator(type = 'national', amount = 120000) {
            window.dispatchEvent(new CustomEvent('switch-to-calculator', {
                detail: { type: type, amount: amount }
            }));
        },

        trackJoin(pkgKey = 'starter', location = 'card') {
            const map = {
                starter: { id: 'membership-10000', name: 'SBL Membership Package', price: 10000 },
                feature: { id: 'membership-10000', name: 'SBL Membership Package', price: 10000 },
                national: { id: 'national-120000', name: 'National Package', price: 120000 },
                international: { id: 'international-550000', name: 'International Package', price: 550000 }
            };
            const meta = map[pkgKey] || map.starter;
            if (window.trackToolkitEvent) {
                window.trackToolkitEvent('toolkit_package_join_click', {
                    package_id: meta.id,
                    package_name: meta.name,
                    package_price_bdt: meta.price,
                    component_variant: pkgKey,
                    cta_location: location
                });
            }
        },

        trackTerms(sourceVariant = 'starter', location = 'card') {
            if (window.trackToolkitEvent) {
                window.trackToolkitEvent('toolkit_package_terms_click', {
                    package_id: 'membership-10000',
                    component_variant: sourceVariant,
                    cta_location: location
                });
            }
        },

        toggleAccordion(id) {
            this.activeAccordion = this.activeAccordion === id ? null : id;
        }
    }"
    @keydown.escape.window="if (modalOpen) closeModal('starter'); if (nationalModalOpen) closeNationalModal(); if (internationalModalOpen) closeInternationalModal();"
>

    <!-- TOP FILTER & PACKAGE SELECTOR BAR -->
    <div class="bg-white/95 backdrop-blur-xs border border-slate-200/90 rounded-2xl p-3 sm:p-4 shadow-xs print:hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div class="space-y-0.5">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold tracking-wider uppercase bg-[#FDEDE7] text-[#AB2925] border border-[#FDEDE7]">
                        SBL PACKAGES
                    </span>
                    <span class="text-xs font-bold text-slate-800">অফিসিয়াল ৩টি বিজনেস প্যাকেজ</span>
                </div>
                <p class="text-xs text-slate-500">আপনার প্রয়োজন ও বিনিয়োগ বাজেট অনুযায়ী উপযুক্ত প্যাকেজটি নির্বাচন করুন</p>
            </div>
            
            <!-- Switcher Tabs -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl overflow-x-auto text-xs font-semibold no-scrollbar">
                <button 
                    type="button" 
                    @click="selectedTier = 'all'"
                    :class="selectedTier === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap min-h-[36px]"
                    aria-label="সকল প্যাকেজ দেখুন"
                >
                    <span>সকল প্যাকেজ (৩টি)</span>
                </button>

                <button 
                    type="button" 
                    @click="selectedTier = 'starter'"
                    :class="selectedTier === 'starter' ? 'bg-white text-[#AB2925] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap min-h-[36px]"
                    aria-label="Starter প্যাকেজ (৳১০,০০০)"
                >
                    <span class="w-2 h-2 rounded-full bg-[#AB2925]"></span>
                    <span>Starter (৳১০,০০০)</span>
                </button>

                <button 
                    type="button" 
                    @click="selectedTier = 'national'"
                    :class="selectedTier === 'national' ? 'bg-white text-[#C2410C] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap min-h-[36px]"
                    aria-label="National প্যাকেজ (৳১,২০,০০০)"
                >
                    <span class="w-2 h-2 rounded-full bg-[#C2410C]"></span>
                    <span>National (৳১,২০,০০০)</span>
                </button>

                <button 
                    type="button" 
                    @click="selectedTier = 'international'"
                    :class="selectedTier === 'international' ? 'bg-white text-amber-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap min-h-[36px]"
                    aria-label="International প্যাকেজ (৳৫,৫০,০০০)"
                >
                    <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                    <span>International (৳৫,৫০,০০০)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- PACKAGES RENDERING CONTAINER -->
    <div class="space-y-6 sm:space-y-8">
        
        <!-- 1. STARTER MEMBERSHIP PACKAGE (৳১০,০০০) -->
        <div x-show="selectedTier === 'all' || selectedTier === 'starter'" x-cloak>
            <div class="mb-2.5 flex items-center justify-between" x-show="selectedTier === 'all'">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-wider text-[#AB2925]">প্যাকেজ ১</span>
                    <span class="text-xs font-bold text-slate-800">• এন্ট্রি লেভেল অ্যাফিলিয়েট ও নেটওয়ার্ক মেম্বারশিপ</span>
                </div>
                <span class="text-xs text-slate-500 font-semibold">৳১০,০০০ BDT</span>
            </div>
            @include('toolkit.partials.package-variant-b')
        </div>

        <!-- 2. NATIONAL DROPSHIPPING PACKAGE (৳১,২০,০০০) -->
        <div x-show="selectedTier === 'all' || selectedTier === 'national'" x-cloak>
            <div class="mb-2.5 flex items-center justify-between" x-show="selectedTier === 'all'">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-wider text-[#C2410C]">প্যাকেজ ২</span>
                    <span class="text-xs font-bold text-slate-800">• ন্যাশনাল ড্রপশিপিং ও শপিফাই ই-কমার্স ব্যবসা</span>
                </div>
                <span class="text-xs text-slate-500 font-semibold">৳১,২০,০০০ BDT (100 BV)</span>
            </div>
            @include('toolkit.partials.package-national')
        </div>

        <!-- 3. INTERNATIONAL DROPSHIPPING PACKAGE (৳৫,৫০,০০০) -->
        <div x-show="selectedTier === 'all' || selectedTier === 'international'" x-cloak>
            <div class="mb-2.5 flex items-center justify-between" x-show="selectedTier === 'all'">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-wider text-amber-800">প্যাকেজ ৩</span>
                    <span class="text-xs font-bold text-slate-800">• আন্তর্জাতিক ড্রপশিপিং ও গ্লোবাল প্রজেক্ট ম্যানেজমেন্ট</span>
                </div>
                <span class="text-xs text-slate-500 font-semibold">৳৫,৫০,০০০ BDT (500 BV)</span>
            </div>
            @include('toolkit.partials.package-international')
        </div>

    </div>

    <!-- ACCESSIBLE DETAILS MODALS FOR ALL 3 PACKAGES -->
    @include('toolkit.partials.package-modal')
    @include('toolkit.partials.modal-national')
    @include('toolkit.partials.modal-international')

</section>
