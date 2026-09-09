<!-- ========================================================================= -->
<!-- VARIANT A: COMPACT CARD                                                   -->
<!-- Max-width 420-460px on tablet/desktop, full width on mobile              -->
<!-- ========================================================================= -->
<article 
    class="sbl-card-hover bg-white rounded-2xl sm:rounded-3xl border border-[#E5E7EB] shadow-xs overflow-hidden max-w-[440px] mx-auto text-[#111827] flex flex-col justify-between"
    aria-labelledby="pkg-title-a"
>
    <!-- Top Accent Ribbon -->
    <div class="sbl-ribbon" aria-hidden="true"></div>

    <div class="p-5 sm:p-6 space-y-5">
        <!-- Badges & Headline -->
        <div class="space-y-2">
            <div class="flex items-center justify-between gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold tracking-wide uppercase bg-[#FDEDE7] text-[#AB2925] border border-[#FDEDE7]">
                    STARTER MEMBERSHIP
                </span>
                <span class="text-[11px] font-bold text-[#C2410C] tracking-wide uppercase bg-orange-50 px-2 py-0.5 rounded-md">
                    Official Plan
                </span>
            </div>

            <h3 id="pkg-title-a" class="text-xl sm:text-2xl font-bold tracking-tight text-[#111827]">
                SBL Membership Package
            </h3>
            <p class="text-xs sm:text-sm font-semibold text-[#C2410C]">
                SBL Ecosystem-এ আপনার শুরু
            </p>
        </div>

        <!-- Price Block -->
        <div class="p-3.5 bg-[#FFF7F3] rounded-2xl border border-[#FDEDE7]">
            <div class="flex items-baseline gap-2">
                <span class="text-3xl sm:text-4xl font-extrabold text-[#111827] tracking-tight">৳১০,০০০</span>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">BDT</span>
            </div>
            <p class="text-xs font-bold uppercase tracking-wider text-[#AB2925] mt-0.5">
                Membership Activation
            </p>
        </div>

        <!-- Short Description -->
        <p class="text-xs sm:text-sm text-[#374151] leading-relaxed">
            SBL Ecosystem-এর Affiliate ও Network কার্যক্রম শুরু করার জন্য entry-level membership package।
        </p>

        <!-- Core Features (Max 6 compact visible features) -->
        <div class="space-y-2 pt-1 border-t border-[#E5E7EB]">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">প্যাকেজের প্রধান সুবিধাসমূহ:</p>
            <ul class="space-y-2 text-xs sm:text-sm text-[#374151]" role="list">
                <li class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Facebook Page Setup Support</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Affiliate Account Setup</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Unlimited Sponsor সুবিধা</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Content Support</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Spot Commission Plan Access (10%)</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#AB2925] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Pair Reward ও UDR Eligibility</span>
                </li>
            </ul>
            <p class="text-[11px] text-slate-500 pt-1 italic">
                +২টি আরও সুবিধা (Referral Return ও Rank Reward) বিস্তারিতে অন্তর্ভুক্ত
            </p>
        </div>

        <!-- Trust / Access Policy Note -->
        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-[12px] text-slate-600 leading-relaxed">
            <span class="font-bold text-slate-700">অ্যাক্সেস নীতি:</span> Membership-এর অধীনে তৈরি account/page-এর ownership ও access SBL-এর প্রযোজ্য official policy অনুযায়ী নির্ধারিত হবে।
        </div>

        <!-- CTAs -->
        <div class="space-y-2.5 pt-2">
            <button 
                type="button" 
                @click="openModal('compact', 'card')"
                class="w-full btn-sbl-secondary text-sm font-bold min-h-[44px]"
                data-analytics-event="toolkit_package_details_click"
                data-package-id="membership-10000"
                data-package-name="SBL Membership Package"
                data-package-price="10000"
                data-component-variant="compact"
                data-cta-location="card"
                aria-label="প্যাকেজের বিস্তারিত দেখুন (মডেল ওপেন করুন)"
            >
                <span>প্যাকেজের বিস্তারিত দেখুন</span>
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <a 
                href="https://shoplogistbd.com" 
                target="_blank" 
                rel="noopener"
                @click="trackJoin('compact', 'card')"
                class="w-full btn-sbl-primary text-sm font-bold min-h-[44px]"
                data-analytics-event="toolkit_package_join_click"
                data-package-id="membership-10000"
                data-package-name="SBL Membership Package"
                data-package-price="10000"
                data-component-variant="compact"
                data-cta-location="card"
            >
                <span>Membership শুরু করুন</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>
</article>
