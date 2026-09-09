<!-- ========================================================================= -->
<!-- SBL MEMBERSHIP PACKAGE: FEATURE-FOCUSED PRODUCTION COMPONENT              -->
<!-- Desktop: Two-column card (~40% left, ~60% right)                         -->
<!-- Mobile: Single column, stacked with price & features first, CTAs at bottom -->
<!-- Card Padding: 20px mobile (p-5), 24px tablet (sm:p-6), 32px desktop (lg:p-8) -->
<!-- Card Radius: 22px (rounded-[22px])                                       -->
<!-- Accent Ribbon: Thin top gradient line (#AB2925 to #C2410C)               -->
<!-- ========================================================================= -->
<article 
    class="sbl-card-hover bg-white rounded-[22px] border border-[#E5E7EB] shadow-xs overflow-hidden max-w-5xl mx-auto text-[#111827]"
    aria-labelledby="pkg-title-b"
>
    <!-- Top Accent Ribbon (Thin red/orange accent line) -->
    <div class="sbl-ribbon" aria-hidden="true"></div>

    <div class="p-5 sm:p-6 lg:p-8">
        <!-- Main Responsive Two-Column Layout (Mobile: 1-col, Large screen: ~40/60) -->
        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-stretch">
            
            <!-- LEFT COLUMN (~40% on Large Screen: Identity, Price, Description, CTAs) -->
            <div class="lg:w-5/12 flex flex-col justify-between space-y-5 lg:pr-8 lg:border-r lg:border-[#E5E7EB]">
                <div class="space-y-4">
                    <!-- Badges -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold tracking-wide uppercase bg-[#FDEDE7] text-[#AB2925] border border-[#FDEDE7]">
                            STARTER MEMBERSHIP
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold tracking-wide uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            RECOMMENDED
                        </span>
                    </div>

                    <!-- Price Block -->
                    <div class="p-4 bg-[#FFF7F3] rounded-2xl border border-[#FDEDE7]">
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl sm:text-4xl font-extrabold text-[#111827] tracking-tight">৳১০,০০০</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#6B7280]">BDT</span>
                        </div>
                        <p class="text-xs font-bold uppercase tracking-wider text-[#AB2925] mt-1">
                            Membership Activation
                        </p>
                    </div>

                    <!-- Title & Short Heading -->
                    <div>
                        <h3 id="pkg-title-b" class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#111827]">
                            SBL Membership Package
                        </h3>
                        <p class="text-sm font-semibold text-[#C2410C] mt-1">
                            SBL Ecosystem-এ আপনার শুরু
                        </p>
                    </div>

                    <!-- Description -->
                    <p class="text-xs sm:text-sm text-[#374151] leading-relaxed">
                        SBL Ecosystem-এর Affiliate ও Network কার্যক্রম শুরু করার জন্য entry-level membership package।
                    </p>
                </div>

                <!-- Desktop CTAs (Hidden on Mobile/Tablet < lg) -->
                <div class="hidden lg:block space-y-3 pt-2">
                    <a 
                        href="https://shoplogistbd.com" 
                        target="_blank" 
                        rel="noopener"
                        @click="trackJoin('feature', 'card')"
                        class="w-full btn-sbl-primary text-sm font-bold min-h-[44px]"
                        data-analytics-event="toolkit_package_join_click"
                        data-package-id="membership-10000"
                        data-package-name="SBL Membership Package"
                        data-package-price="10000"
                        data-component-variant="feature"
                        data-cta-location="card"
                    >
                        <span>Membership শুরু করুন</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>

                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            type="button" 
                            @click="openModal('feature', 'card')"
                            class="btn-sbl-secondary text-xs sm:text-sm font-semibold min-h-[44px]"
                            data-analytics-event="toolkit_package_details_click"
                            data-package-id="membership-10000"
                            data-package-name="SBL Membership Package"
                            data-package-price="10000"
                            data-component-variant="feature"
                            data-cta-location="card"
                            aria-label="প্যাকেজের বিস্তারিত দেখুন"
                        >
                            <span>প্যাকেজের বিস্তারিত দেখুন</span>
                        </button>

                        <button 
                            type="button" 
                            @click="trackTerms('feature', 'card'); openModal('feature', 'card')"
                            class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition min-h-[44px] flex items-center justify-center text-center"
                            data-analytics-event="toolkit_package_terms_click"
                            data-package-id="membership-10000"
                            data-package-name="SBL Membership Package"
                            data-package-price="10000"
                            data-component-variant="feature"
                            data-cta-location="card"
                            aria-label="শর্ত ও প্ল্যান দেখুন"
                        >
                            <span>শর্ত ও প্ল্যান দেখুন</span>
                        </button>
                    </div>

                    <!-- Trust Note (Desktop) -->
                    <p class="text-[11px] text-[#6B7280] text-center font-medium pt-1">
                        স্বচ্ছ তথ্য • পরিষ্কার শর্ত • Official SBL plan অনুযায়ী সুবিধা
                    </p>
                </div>

            </div>

            <!-- RIGHT COLUMN (~60% on Large Screen: 8 Core Features Grid & Ownership Note) -->
            <div class="lg:w-7/12 flex flex-col justify-between space-y-5">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
                        <h4 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#111827]">
                            অন্তর্ভুক্ত ৮টি প্রধান সুবিধা
                        </h4>
                        <span class="text-xs font-semibold text-[#AB2925] bg-[#FDEDE7] px-2 py-0.5 rounded-md">
                            8 Core Features
                        </span>
                    </div>

                    <!-- 8-Feature Responsive Grid (1-column on narrow, 2-column where content fits) -->
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3 pt-3.5" role="list">
                        <!-- Feature 1 -->
                        <li class="flex items-start gap-2.5 p-2.5 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                            <div class="w-6 h-6 rounded-full bg-[#FDEDE7] text-[#AB2925] flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Facebook Page Setup Support</strong>
                                <span class="text-[11px] text-[#6B7280]">পেজ ক্রিয়েশন ও কনফিগারেশন সাপোর্ট</span>
                            </div>
                        </li>

                        <!-- Feature 2 -->
                        <li class="flex items-start gap-2.5 p-2.5 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                            <div class="w-6 h-6 rounded-full bg-[#FDEDE7] text-[#AB2925] flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Affiliate Account Setup</strong>
                                <span class="text-[11px] text-[#6B7280]">অ্যাফিলিয়েট অ্যাকাউন্ট অ্যাক্টিভেশন</span>
                            </div>
                        </li>

                        <!-- Feature 3 -->
                        <li class="flex items-start gap-2.5 p-2.5 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                            <div class="w-6 h-6 rounded-full bg-[#FDEDE7] text-[#AB2925] flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Unlimited Sponsor সুবিধা</strong>
                                <span class="text-[11px] text-[#6B7280]">সীমাহীন রেফারেল স্পন্সরশিপ</span>
                            </div>
                        </li>

                        <!-- Feature 4 -->
                        <li class="flex items-start gap-2.5 p-2.5 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                            <div class="w-6 h-6 rounded-full bg-[#FDEDE7] text-[#AB2925] flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Content Support</strong>
                                <span class="text-[11px] text-[#6B7280]">প্রচারের জন্য ডিজিটাল কনটেন্ট</span>
                            </div>
                        </li>

                        <!-- Feature 5 -->
                        <li class="flex items-start gap-2.5 p-2.5 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                            <div class="w-6 h-6 rounded-full bg-[#FDEDE7] text-[#AB2925] flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Spot Commission Plan Access</strong>
                                <span class="text-[11px] text-[#6B7280]">১০% স্পট কমিশন প্ল্যান অ্যাক্সেস</span>
                            </div>
                        </li>

                        <!-- Feature 6 -->
                        <li class="flex items-start gap-2.5 p-2.5 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                            <div class="w-6 h-6 rounded-full bg-[#FDEDE7] text-[#AB2925] flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Referral Return Plan Access</strong>
                                <span class="text-[11px] text-[#6B7280]">১০০ সপ্তাহের রিটার্ন প্ল্যান অ্যাক্সেস</span>
                            </div>
                        </li>

                        <!-- Feature 7 -->
                        <li class="flex items-start gap-2.5 p-2.5 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                            <div class="w-6 h-6 rounded-full bg-[#FDEDE7] text-[#AB2925] flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Pair Reward ও UDR Eligibility</strong>
                                <span class="text-[11px] text-[#6B7280]">পেয়ার বোনাস ও ৫% UDR কমিশন যোগ্যতা</span>
                            </div>
                        </li>

                        <!-- Feature 8 -->
                        <li class="flex items-start gap-2.5 p-2.5 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                            <div class="w-6 h-6 rounded-full bg-[#FDEDE7] text-[#AB2925] flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <strong class="text-xs sm:text-sm font-bold text-[#111827] block">Rank Reward Plan Access</strong>
                                <span class="text-[11px] text-[#6B7280]">অফিশিয়াল র‍্যাঙ্ক অ্যাচিভমেন্ট ইনসেনটিভ</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Ownership / Access Note Box -->
                <div class="bg-[#FFF7F3] border border-[#FDEDE7] rounded-2xl p-4 text-xs text-[#374151] space-y-1 mt-4">
                    <div class="flex items-center gap-1.5 font-bold text-[#AB2925]">
                        <svg class="w-4 h-4 text-[#AB2925] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>মালিকানা ও অ্যাক্সেস স্পষ্টীকরণ:</span>
                    </div>
                    <p class="leading-relaxed">
                        Membership-এর অধীনে তৈরি account/page-এর ownership ও access SBL-এর প্রযোজ্য official policy অনুযায়ী নির্ধারিত হবে।
                    </p>
                </div>
            </div>

        </div>

        <!-- Mobile CTAs (Only rendered on mobile/tablet < lg screens, stacked at bottom) -->
        <div class="block lg:hidden pt-6 mt-6 border-t border-[#E5E7EB] space-y-2.5">
            <a 
                href="https://shoplogistbd.com" 
                target="_blank" 
                rel="noopener"
                @click="trackJoin('feature', 'card')"
                class="w-full btn-sbl-primary text-sm font-bold min-h-[44px]"
                data-analytics-event="toolkit_package_join_click"
                data-package-id="membership-10000"
                data-package-name="SBL Membership Package"
                data-package-price="10000"
                data-component-variant="feature"
                data-cta-location="card"
            >
                <span>Membership শুরু করুন</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>

            <div class="grid grid-cols-2 gap-2">
                <button 
                    type="button" 
                    @click="openModal('feature', 'card')"
                    class="btn-sbl-secondary text-xs sm:text-sm font-semibold min-h-[44px]"
                    data-analytics-event="toolkit_package_details_click"
                    data-package-id="membership-10000"
                    data-package-name="SBL Membership Package"
                    data-package-price="10000"
                    data-component-variant="feature"
                    data-cta-location="card"
                    aria-label="প্যাকেজের বিস্তারিত দেখুন"
                >
                    <span>প্যাকেজের বিস্তারিত দেখুন</span>
                </button>

                <button 
                    type="button" 
                    @click="trackTerms('feature', 'card'); openModal('feature', 'card')"
                    class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition min-h-[44px] flex items-center justify-center text-center"
                    data-analytics-event="toolkit_package_terms_click"
                    data-package-id="membership-10000"
                    data-package-name="SBL Membership Package"
                    data-package-price="10000"
                    data-component-variant="feature"
                    data-cta-location="card"
                    aria-label="শর্ত ও প্ল্যান দেখুন"
                >
                    <span>শর্ত ও প্ল্যান দেখুন</span>
                </button>
            </div>

            <!-- Trust Note (Mobile) -->
            <p class="text-[11px] text-[#6B7280] text-center font-medium pt-1">
                স্বচ্ছ তথ্য • পরিষ্কার শর্ত • Official SBL plan অনুযায়ী সুবিধা
            </p>
        </div>

    </div>
</article>
