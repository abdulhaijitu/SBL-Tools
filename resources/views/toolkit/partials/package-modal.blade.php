<!-- ========================================================================= -->
<!-- ACCESSIBLE DETAILS MODAL (Production SBL Membership Package)              -->
<!-- Requirements:                                                            -->
<!-- - aria-labelledby="package-modal-title"                                   -->
<!-- - visible close control                                                  -->
<!-- - Escape close                                                           -->
<!-- - focus management (traps focus, returns to trigger)                     -->
<!-- - scrollable body                                                        -->
<!-- - max-width approximately 760px (max-w-[760px])                           -->
<!-- - mobile width calc(100% - 24px) (w-[calc(100%-24px)])                   -->
<!-- - max-height 90dvh (max-h-[90dvh])                                       -->
<!-- - secondary leaflet brand context image (/images/sbl/sbl-office-leaflet.jpg)-->
<!-- ========================================================================= -->
<div 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="package-modal-title"
    x-show="modalOpen" 
    x-cloak 
    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
    style="display: none;"
>
    <!-- Backdrop (closes modal on click) -->
    <div 
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click="closeModal('feature')"
        aria-hidden="true"
    ></div>

    <!-- Modal Dialog Box -->
    <div 
        class="sbl-modal-anim relative bg-white rounded-[22px] border border-slate-200 shadow-2xl w-[calc(100%-24px)] max-w-[760px] max-h-[90dvh] flex flex-col overflow-hidden z-10 text-[#111827]"
        @click.stop
    >
        <!-- Top Accent Ribbon -->
        <div class="sbl-ribbon" aria-hidden="true"></div>

        <!-- Modal Header -->
        <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-[#E5E7EB] flex items-center justify-between gap-3 bg-white shrink-0">
            <div class="space-y-0.5">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold uppercase bg-[#FDEDE7] text-[#AB2925] border border-[#FDEDE7]">
                        STARTER MEMBERSHIP
                    </span>
                    <span class="text-xs font-bold text-slate-800">৳১০,০০০ BDT</span>
                </div>
                <h3 id="package-modal-title" class="text-lg sm:text-xl font-extrabold text-[#111827]">
                    SBL Membership Package Details
                </h3>
            </div>

            <button 
                type="button" 
                id="pkg-modal-close-btn"
                @click="closeModal('feature')"
                class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 flex items-center justify-center transition shrink-0 min-h-[44px]"
                aria-label="Close dialog"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Body (Vertically Scrollable) -->
        <div class="p-5 sm:p-6 overflow-y-auto space-y-6 text-xs sm:text-sm text-[#374151]">
            
            <!-- Package Summary & Headline -->
            <div class="p-4 bg-[#FFF7F3] rounded-2xl border border-[#FDEDE7] space-y-1">
                <div class="flex items-center justify-between gap-2">
                    <h4 class="font-bold text-[#AB2925] text-sm">SBL Ecosystem-এ আপনার শুরু</h4>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#6B7280]">৳১০,০০০ Activation</span>
                </div>
                <p class="text-xs sm:text-sm text-[#374151] leading-relaxed">
                    SBL Ecosystem-এর Affiliate ও Network কার্যক্রম শুরু করার জন্য entry-level membership package।
                </p>
            </div>

            <!-- 8 Core Features List -->
            <div class="space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-[#E5E7EB]">
                    <h4 class="font-bold text-[#111827] text-sm uppercase tracking-wide">
                        সম্পূর্ণ ৮টি ফিচার ও সুবিধার তালিকা:
                    </h4>
                    <span class="text-xs font-semibold text-[#AB2925] bg-[#FDEDE7] px-2 py-0.5 rounded-md">
                        8 Core Features
                    </span>
                </div>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2.5" role="list">
                    <li class="flex items-start gap-2 p-2 bg-slate-50 rounded-xl border border-slate-100">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Facebook Page Setup Support</strong>
                            <span class="text-[11px] text-[#6B7280]">পেজ ক্রিয়েশন ও কনফিগারেশন সাপোর্ট</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-2 p-2 bg-slate-50 rounded-xl border border-slate-100">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Affiliate Account Setup</strong>
                            <span class="text-[11px] text-[#6B7280]">অ্যাফিলিয়েট অ্যাকাউন্ট অ্যাক্টিভেশন</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-2 p-2 bg-slate-50 rounded-xl border border-slate-100">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Unlimited Sponsor সুবিধা</strong>
                            <span class="text-[11px] text-[#6B7280]">সীমাহীন রেফারেল স্পন্সরশিপ</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-2 p-2 bg-slate-50 rounded-xl border border-slate-100">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Content Support</strong>
                            <span class="text-[11px] text-[#6B7280]">প্রচারের জন্য ডিজিটাল কনটেন্ট</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-2 p-2 bg-slate-50 rounded-xl border border-slate-100">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Spot Commission Plan Access</strong>
                            <span class="text-[11px] text-[#6B7280]">১০% স্পট কমিশন প্ল্যান অ্যাক্সেস</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-2 p-2 bg-slate-50 rounded-xl border border-slate-100">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Referral Return Plan Access</strong>
                            <span class="text-[11px] text-[#6B7280]">১০০ সপ্তাহের রিটার্ন প্ল্যান অ্যাক্সেস</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-2 p-2 bg-slate-50 rounded-xl border border-slate-100">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Pair Reward ও UDR Eligibility</strong>
                            <span class="text-[11px] text-[#6B7280]">পেয়ার বোনাস ও ৫% UDR কমিশন যোগ্যতা</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-2 p-2 bg-slate-50 rounded-xl border border-slate-100">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Rank Reward Plan Access</strong>
                            <span class="text-[11px] text-[#6B7280]">অফিশিয়াল র‍্যাঙ্ক ইনসেনটিভ প্ল্যান</span>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Marketing Plan Details -->
            <div class="space-y-3 pt-3 border-t border-[#E5E7EB]">
                <h4 class="font-bold text-[#111827] text-sm uppercase tracking-wide">
                    মার্কেটিং প্ল্যান ও পার্সেন্টেজ স্পেসিফিকেশন:
                </h4>
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 space-y-2">
                    <div class="flex justify-between py-1 border-b border-slate-200/60 text-xs">
                        <span class="font-semibold text-slate-600">Spot Commission</span>
                        <span class="font-bold text-slate-900">10% (leaflet states 10%)</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60 text-xs">
                        <span class="font-semibold text-slate-600">Refer Return</span>
                        <span class="font-bold text-slate-900">0.25% for 100 weeks</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60 text-xs">
                        <span class="font-semibold text-slate-600">Pair Reward</span>
                        <span class="font-bold text-slate-900">current applicable SBL plan অনুযায়ী</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60 text-xs">
                        <span class="font-semibold text-slate-600">Unity Development Commission (UDR)</span>
                        <span class="font-bold text-slate-900">5% (leaflet states 5%)</span>
                    </div>
                    <div class="flex justify-between py-1 text-xs">
                        <span class="font-semibold text-slate-600">Rank Reward</span>
                        <span class="font-bold text-slate-900">eligibility and current applicable SBL plan অনুযায়ী</span>
                    </div>
                </div>
            </div>

            <!-- Leaflet-Stated Return & Disclaimer Section -->
            <div class="bg-[#FFF7F3] border border-[#FDEDE7] rounded-2xl p-4 sm:p-5 space-y-3">
                <div>
                    <span class="text-xs font-bold text-[#AB2925] uppercase tracking-wider block">Leaflet-Stated Return:</span>
                    <p class="text-sm font-bold text-[#111827] mt-0.5">
                        “১০০ সপ্তাহে ৳১০,০০০”
                    </p>
                </div>

                <div class="text-xs text-[#374151] leading-relaxed border-t border-[#FDEDE7] pt-2">
                    <strong class="text-[#111827]">মালিকানা ও অ্যাক্সেস স্পষ্টীকরণ:</strong> Membership-এর অধীনে তৈরি account/page-এর ownership ও access SBL-এর প্রযোজ্য official policy অনুযায়ী নির্ধারিত হবে।
                </div>

                <div class="text-xs text-[#AB2925] leading-relaxed border-t border-[#FDEDE7] pt-2">
                    <strong class="block mb-0.5 font-bold">অফিশিয়াল ডিসক্লেইমার:</strong>
                    “প্যাকেজ সুবিধা, কমিশন, রিওয়ার্ড ও রিটার্ন SBL-এর বর্তমান নীতিমালা, যোগ্যতা ও প্রযোজ্য শর্তসাপেক্ষ। প্রদর্শিত কোনো অঙ্ককে নিশ্চিত ব্যক্তিগত আয় হিসেবে বিবেচনা করবেন না।”
                </div>
            </div>

            <!-- Secondary Brand Context: Official Leaflet Reference -->
            <div class="space-y-2 pt-2 border-t border-[#E5E7EB]">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800">অফিশিয়াল লিফলেট রেফারেন্স</span>
                    <span class="text-[11px] text-slate-500">Official Brand Context</span>
                </div>
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                    <img 
                        src="/images/sbl/sbl-office-leaflet.jpg" 
                        alt="SBL office leaflet detailing membership and network packages" 
                        loading="lazy" 
                        width="700" 
                        height="420" 
                        class="w-full h-auto max-h-52 sm:max-h-60 object-cover object-top"
                    />
                </div>
            </div>

        </div>

        <!-- Modal Footer CTA -->
        <div class="p-4 sm:p-5 bg-slate-50 border-t border-[#E5E7EB] flex flex-col-reverse sm:flex-row items-center justify-end gap-3 shrink-0">
            <button 
                type="button" 
                @click="closeModal('feature')"
                class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs sm:text-sm font-semibold hover:bg-slate-100 transition min-h-[44px]"
            >
                বন্ধ করুন
            </button>
            <a 
                href="https://shoplogistbd.com" 
                target="_blank" 
                rel="noopener"
                @click="trackJoin('feature', 'modal')"
                class="w-full sm:w-auto btn-sbl-primary text-xs sm:text-sm font-bold min-h-[44px]"
                data-analytics-event="toolkit_package_join_click"
                data-package-id="membership-10000"
                data-package-name="SBL Membership Package"
                data-package-price="10000"
                data-component-variant="feature"
                data-cta-location="modal"
            >
                <span>Membership শুরু করুন</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>

    </div>
</div>
