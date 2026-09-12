@php
    $ranksData = [
        [
            'code' => 'FME',
            'order' => 1,
            'name' => 'Field Marketing Executive',
            'name_bn' => 'ফিল্ড মার্কেটিং এক্সিকিউটিভ',
            'reward' => '৳5,000',
            'reward_raw' => 5000,
            'req_main' => '10 Direct Sponsors',
            'req_left_right' => 'Left: 5 • Right: 5',
            'left_team' => '5 Direct Sponsors',
            'right_team' => '5 Direct Sponsors',
            'prev_rank' => 'None (New Member)',
            'next_rank' => 'SME',
            'accent' => 'amber', // Bronze
            'accent_title' => 'Bronze Executive',
            'border_color' => 'border-amber-400 dark:border-amber-500/40',
            'bg_gradient' => 'from-amber-500/10 via-amber-500/5 to-transparent',
            'badge_bg' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-300',
            'note' => 'The foundational leadership rank in SBL. Achieved by sponsoring 10 active associates evenly balanced between Left and Right teams.',
        ],
        [
            'code' => 'SME',
            'order' => 2,
            'name' => 'Senior Marketing Executive',
            'name_bn' => 'সিনিয়র মার্কেটিং এক্সিকিউটিভ',
            'reward' => '৳50,000',
            'reward_raw' => 50000,
            'req_main' => '300 Binary Matched Pairs',
            'req_left_right' => 'Dual Team Performance',
            'left_team' => '300 Matched Volume',
            'right_team' => '300 Matched Volume',
            'prev_rank' => 'FME',
            'next_rank' => 'PME',
            'accent' => 'slate', // Silver
            'accent_title' => 'Silver Executive',
            'border_color' => 'border-slate-400 dark:border-slate-500/40',
            'bg_gradient' => 'from-slate-400/10 via-slate-400/5 to-transparent',
            'badge_bg' => 'bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-200',
            'note' => 'Senior leadership milestone reached by scaling your binary organization sales volume to 300 matched pairs.',
        ],
        [
            'code' => 'PME',
            'order' => 3,
            'name' => 'Promotional Marketing Executive',
            'name_bn' => 'প্রমোশনাল মার্কেটিং এক্সিকিউটিভ',
            'reward' => '৳1,00,000',
            'reward_raw' => 100000,
            'req_main' => '20 SME Leaders',
            'req_left_right' => 'Left: 13 SME • Right: 7 SME',
            'left_team' => '13 SME Leaders',
            'right_team' => '7 SME Leaders',
            'prev_rank' => 'SME',
            'next_rank' => 'BME',
            'accent' => 'yellow', // Gold
            'accent_title' => 'Gold Executive',
            'border_color' => 'border-yellow-400 dark:border-yellow-500/40',
            'bg_gradient' => 'from-yellow-500/10 via-yellow-500/5 to-transparent',
            'badge_bg' => 'bg-yellow-100 text-yellow-900 dark:bg-yellow-900/40 dark:text-yellow-300',
            'note' => 'Advanced organizational leadership. Reached by mentoring and developing 20 SME leaders across your dual sales teams.',
        ],
        [
            'code' => 'BME',
            'order' => 4,
            'name' => 'Brand Marketing Executive',
            'name_bn' => 'ব্র্যান্ড মার্কেটিং এক্সিকিউটিভ',
            'reward' => '৳5,00,000',
            'reward_raw' => 500000,
            'req_main' => '15 PME Leaders',
            'req_left_right' => 'Left: 10 PME • Right: 5 PME',
            'left_team' => '10 PME Leaders',
            'right_team' => '5 PME Leaders',
            'prev_rank' => 'PME',
            'next_rank' => 'GME',
            'accent' => 'cyan', // Diamond
            'accent_title' => 'Diamond Executive',
            'border_color' => 'border-cyan-400 dark:border-cyan-500/40',
            'bg_gradient' => 'from-cyan-500/10 via-cyan-500/5 to-transparent',
            'badge_bg' => 'bg-cyan-100 text-cyan-900 dark:bg-cyan-900/40 dark:text-cyan-300',
            'note' => 'High-tier Brand Ambassador status rewarded with half a million BDT cash incentive for expanding multi-city leadership.',
        ],
        [
            'code' => 'GME',
            'order' => 5,
            'name' => 'Global Marketing Executive',
            'name_bn' => 'গ্লোবাল মার্কেটিং এক্সিকিউটিভ',
            'reward' => '৳10,00,000',
            'reward_raw' => 1000000,
            'req_main' => '12 BME Leaders',
            'req_left_right' => 'Left: 8 BME • Right: 4 BME',
            'left_team' => '8 BME Leaders',
            'right_team' => '4 BME Leaders',
            'prev_rank' => 'BME',
            'next_rank' => 'ETD',
            'accent' => 'purple', // Crown
            'accent_title' => 'Crown Global',
            'border_color' => 'border-purple-400 dark:border-purple-500/40',
            'bg_gradient' => 'from-purple-500/10 via-purple-500/5 to-transparent',
            'badge_bg' => 'bg-purple-100 text-purple-900 dark:bg-purple-900/40 dark:text-purple-300',
            'note' => 'Executive international scale leadership. 1 Million BDT cash milestone for establishing cross-border enterprise teams.',
        ],
        [
            'code' => 'ETD',
            'order' => 6,
            'name' => 'Executive Team Director',
            'name_bn' => 'এক্সিকিউটিভ টিম ডিরেক্টর',
            'reward' => '৳20,00,000',
            'reward_raw' => 2000000,
            'req_main' => '10 GME Leaders',
            'req_left_right' => 'Left: 7 GME • Right: 3 GME',
            'left_team' => '7 GME Leaders',
            'right_team' => '3 GME Leaders',
            'prev_rank' => 'GME',
            'next_rank' => 'Apex Director',
            'accent' => 'emerald', // Executive
            'accent_title' => 'Director Ambassador',
            'border_color' => 'border-emerald-400 dark:border-emerald-500/40',
            'bg_gradient' => 'from-emerald-500/10 via-emerald-500/5 to-transparent',
            'badge_bg' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300',
            'note' => 'The highest pinnacle of SBL marketing achievement. 2 Million BDT cash reward recognizing apex organization leadership.',
        ],
    ];
@endphp

<div 
    id="sbl-ranks-container"
    class="space-y-10 pb-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 select-text"
    x-data="{
        ranks: @js($ranksData),
        selectedRank: null,
        detailModalOpen: false,
        presentationMode: false,
        currentSlide: 0,
        toastMessage: '',
        showToast: false,

        triggerToast(msg) {
            this.toastMessage = msg;
            this.showToast = true;
            setTimeout(() => { this.showToast = false; }, 3000);
        },

        openDetails(rank) {
            this.selectedRank = rank;
            this.detailModalOpen = true;
        },

        closeDetails() {
            this.detailModalOpen = false;
        },

        startPresentation(startIndex = 0) {
            this.currentSlide = startIndex;
            this.presentationMode = true;
            document.body.classList.add('overflow-hidden');
        },

        exitPresentation() {
            this.presentationMode = false;
            document.body.classList.remove('overflow-hidden');
        },

        nextSlide() {
            if (this.currentSlide < this.ranks.length - 1) {
                this.currentSlide++;
            }
        },

        prevSlide() {
            if (this.currentSlide > 0) {
                this.currentSlide--;
            }
        },

        getShareText(rank) {
            if (!rank) {
                return `SBL Career Ranks Journey\n\nUnderstand the complete leadership journey from FME to ETD:\n1. FME - Reward ৳5,000 (10 Direct Sponsors)\n2. SME - Reward ৳50,000 (300 Matched Pairs)\n3. PME - Reward ৳1,00,000 (20 SME Leaders)\n4. BME - Reward ৳5,00,000 (15 PME Leaders)\n5. GME - Reward ৳10,00,000 (12 BME Leaders)\n6. ETD - Reward ৳20,00,000 (10 GME Leaders)\n\nLearn more: ${window.location.origin}/ranks`;
            }
            return `SBL ${rank.code} Rank\n${rank.name}\n\nMilestone Reward:\n${rank.reward}\n\nQualification:\n${rank.req_main}\n(${rank.req_left_right})\n\nView full rank information:\n${window.location.origin}/ranks`;
        },

        shareWhatsApp(rank = null) {
            const text = encodeURIComponent(this.getShareText(rank));
            window.open(`https://wa.me/?text=${text}`, '_blank');
        },

        copyShareLink(rank = null) {
            const text = this.getShareText(rank);
            navigator.clipboard.writeText(text).then(() => {
                this.triggerToast('Rank information copied to clipboard!');
            }).catch(() => {
                this.triggerToast('Unable to copy to clipboard.');
            });
        },

        nativeShare(rank = null) {
            if (navigator.share) {
                navigator.share({
                    title: rank ? `SBL ${rank.code} Rank` : 'SBL Career Ranks',
                    text: this.getShareText(rank),
                    url: `${window.location.origin}/ranks`,
                }).catch(() => {});
            } else {
                this.copyShareLink(rank);
            }
        }
    }"
    @keydown.escape.window="if (presentationMode) exitPresentation(); else closeDetails();"
    @keydown.right.window="if (presentationMode) nextSlide();"
    @keydown.left.window="if (presentationMode) prevSlide();"
>

    <!-- ================================================== -->
    <!-- 1. MINIMAL PAGE HEADER                             -->
    <!-- ================================================== -->
    <header class="pt-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                        Official SBL Plan
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Verified for 2026</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                    SBL Career Ranks
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 max-w-2xl">
                    Understand the SBL leadership journey from FME to ETD.
                </p>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex items-center gap-2 sm:gap-3">
                <button 
                    type="button"
                    @click="startPresentation(0)"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 min-h-[44px] rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Presentation View</span>
                </button>

                <button 
                    type="button"
                    @click="shareWhatsApp(null)"
                    class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 min-h-[44px] rounded-xl text-sm font-semibold bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all"
                    title="Share Ranks via WhatsApp"
                >
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z" />
                    </svg>
                    <span>Share</span>
                </button>
            </div>
        </div>
    </header>


    <!-- ================================================== -->
    <!-- 2. CAREER ROADMAP                                  -->
    <!-- ================================================== -->
    <section class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Career Roadmap Progression
            </h2>
            <span class="text-xs text-slate-400 dark:text-slate-500 hidden sm:inline">
                Tap any rank to view details
            </span>
        </div>

        <!-- Horizontal scrollable on mobile, flex grid on desktop -->
        <div class="relative overflow-x-auto pb-3 pt-1 -mx-4 px-4 sm:mx-0 sm:px-0 scrollbar-thin">
            <div class="flex items-center gap-2 sm:gap-3 min-w-max sm:min-w-0 sm:grid sm:grid-cols-6">
                @foreach ($ranksData as $index => $r)
                    <div 
                        @click="openDetails(ranks[{{ $index }}])"
                        class="cursor-pointer group relative flex flex-col justify-between p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-emerald-500 dark:hover:border-emerald-500/50 hover:shadow-md transition-all w-36 sm:w-auto min-h-[110px]"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold px-2 py-0.5 rounded-md {{ $r['badge_bg'] }}">
                                    {{ $r['code'] }}
                                </span>
                                <span class="text-[10px] font-medium text-slate-400">
                                    #{{ $r['order'] }}
                                </span>
                            </div>
                            <h3 class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-2 line-clamp-1 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                {{ $r['name'] }}
                            </h3>
                        </div>

                        <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $r['reward'] }}
                            </span>
                        </div>

                        @if ($index < count($ranksData) - 1)
                            <!-- Connector Arrow for Desktop / Tablet -->
                            <div class="hidden sm:block absolute -right-2 top-1/2 -translate-y-1/2 z-10 pointer-events-none">
                                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>


    <!-- ================================================== -->
    <!-- 3 & 4. RANK CARDS (6 CLEAN CARDS)                 -->
    <!-- ================================================== -->
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                Leadership Rank Cards
            </h2>
            <span class="text-xs text-slate-500 dark:text-slate-400">
                6 Verified Tiers
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @foreach ($ranksData as $index => $r)
                <div class="relative flex flex-col justify-between rounded-2xl bg-white dark:bg-slate-900 border-2 {{ $r['border_color'] }} p-5 shadow-sm hover:shadow-lg transition-all">
                    
                    <!-- Top Info -->
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-base font-black px-2.5 py-0.5 rounded-lg {{ $r['badge_bg'] }}">
                                        {{ $r['code'] }}
                                    </span>
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                        {{ $r['accent_title'] }}
                                    </span>
                                </div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mt-1.5">
                                    {{ $r['name'] }}
                                </h3>
                            </div>

                            <!-- Reward Badge -->
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">
                                    Milestone Reward
                                </span>
                                <span class="text-lg sm:text-xl font-extrabold text-emerald-600 dark:text-emerald-400">
                                    {{ $r['reward'] }}
                                </span>
                            </div>
                        </div>

                        <!-- Main Qualification Requirement -->
                        <div class="mt-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block mb-1">
                                Qualification Requirement
                            </span>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                {{ $r['req_main'] }}
                            </p>
                            <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 mt-1">
                                {{ $r['req_left_right'] }}
                            </p>
                        </div>
                    </div>

                    <!-- Card Actions (>= 44px Touch Targets) -->
                    <div class="mt-5 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center gap-2">
                        <button 
                            type="button"
                            @click="openDetails(ranks[{{ $index }}])"
                            class="flex-1 inline-flex items-center justify-center px-4 py-2.5 min-h-[44px] rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-400"
                        >
                            View Details
                        </button>

                        <button 
                            type="button"
                            @click="shareWhatsApp(ranks[{{ $index }}])"
                            class="inline-flex items-center justify-center px-3.5 py-2.5 min-h-[44px] rounded-xl text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            title="Share {{ $r['code'] }} Rank"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z" />
                            </svg>
                            <span>Share</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>


    <!-- ================================================== -->
    <!-- 6. QUICK SUMMARY (SCREENSHOT / PRESENTATION READY) -->
    <!-- ================================================== -->
    <section class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                    SBL Rank Journey Quick Summary
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Fast reference for presentations and team counseling
                </p>
            </div>
            <button 
                type="button"
                @click="copyShareLink(null)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 self-start sm:self-auto transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                </svg>
                <span>Copy Summary</span>
            </button>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800 mt-2">
            @foreach ($ranksData as $r)
                <div class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <span class="w-12 text-center text-xs font-extrabold px-2 py-1 rounded-md {{ $r['badge_bg'] }}">
                            {{ $r['code'] }}
                        </span>
                        <div>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white block sm:inline">
                                {{ $r['name'] }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 sm:ml-2">
                                ({{ $r['req_main'] }} • {{ $r['req_left_right'] }})
                            </span>
                        </div>
                    </div>
                    <div class="text-left sm:text-right pl-15 sm:pl-0">
                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $r['reward'] }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>


    <!-- ================================================== -->
    <!-- 9. SIMPLE MARKETING PLAN SUMMARY (OPTIONAL)        -->
    <!-- ================================================== -->
    <section class="rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                    SBL Marketing Income Streams
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    5 official earning opportunities in the SBL leadership ecosystem
                </p>
            </div>
            <a 
                href="{{ route('packages.index') }}"
                class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 transition-colors"
            >
                <span>View Packages & Plan</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60">
                <span class="text-xs font-bold text-slate-900 dark:text-white block">1. Spot Commission</span>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                    10% instant direct referral-based marketing commission.
                </p>
            </div>
            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60">
                <span class="text-xs font-bold text-slate-900 dark:text-white block">2. Pair Matching Reward</span>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                    ৳500 reward based on matched Left and Right team volume.
                </p>
            </div>
            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60">
                <span class="text-xs font-bold text-slate-900 dark:text-white block">3. Unity Development (UDR)</span>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                    Multi-generation development commission up to 10 generations.
                </p>
            </div>
            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60">
                <span class="text-xs font-bold text-slate-900 dark:text-white block">4. Rank Milestone Reward</span>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                    One-time milestone cash reward from ৳5,000 to ৳20,00,000.
                </p>
            </div>
            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60 sm:col-span-2 lg:col-span-1">
                <span class="text-xs font-bold text-slate-900 dark:text-white block">5. Referral Weekly Share</span>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                    Plan-based weekly sponsor incentive on qualified dropshipping projects.
                </p>
            </div>
        </div>
    </section>


    <!-- ================================================== -->
    <!-- 5. RANK DETAILS MODAL / DRAWER                     -->
    <!-- ================================================== -->
    <div 
        x-show="detailModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog" 
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div 
            x-show="detailModalOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeDetails()"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
        ></div>

        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="detailModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 w-full max-w-lg border border-slate-200 dark:border-slate-800"
            >
                <template x-if="selectedRank">
                    <div class="p-6">
                        <!-- Header with close button -->
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-xs font-black px-2.5 py-0.5 rounded-lg" :class="selectedRank.badge_bg" x-text="selectedRank.code"></span>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white mt-2" x-text="selectedRank.name"></h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-text="selectedRank.name_bn"></p>
                            </div>
                            <button 
                                type="button"
                                @click="closeDetails()"
                                class="rounded-lg p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 min-h-[44px] min-w-[44px] inline-flex items-center justify-center"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Milestone Reward Section -->
                        <div class="mt-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 block">
                                Milestone Cash Reward
                            </span>
                            <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block" x-text="selectedRank.reward"></span>
                            <span class="text-xs text-emerald-700/80 dark:text-emerald-400/70">One-time official rank reward upon verification</span>
                        </div>

                        <!-- Qualification Requirements -->
                        <div class="mt-4 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Qualification Requirements
                            </h4>
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60">
                                <span class="text-xs text-slate-400 block">Core Requirement</span>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-100 mt-0.5" x-text="selectedRank.req_main"></p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60">
                                    <span class="text-xs text-slate-400 block">Left Team</span>
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100 mt-0.5" x-text="selectedRank.left_team"></p>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60">
                                    <span class="text-xs text-slate-400 block">Right Team</span>
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100 mt-0.5" x-text="selectedRank.right_team"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Career Path Progression -->
                        <div class="mt-4 grid grid-cols-2 gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <div>
                                <span class="text-[11px] text-slate-400 block">Previous Rank</span>
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300" x-text="selectedRank.prev_rank"></span>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-400 block">Next Career Rank</span>
                                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400" x-text="selectedRank.next_rank"></span>
                            </div>
                        </div>

                        <!-- Short Note -->
                        <div class="mt-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Leadership Note</span>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1" x-text="selectedRank.note"></p>
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 flex items-center gap-3">
                            <button 
                                type="button"
                                @click="shareWhatsApp(selectedRank)"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 min-h-[44px] rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-500 text-white transition-colors"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z" />
                                </svg>
                                <span>Share on WhatsApp</span>
                            </button>

                            <button 
                                type="button"
                                @click="copyShareLink(selectedRank)"
                                class="inline-flex items-center justify-center px-4 py-2.5 min-h-[44px] rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors"
                            >
                                Copy Link
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>


    <!-- ================================================== -->
    <!-- 7. PRESENTATION VIEW (FULLSCREEN SLIDE MODE)      -->
    <!-- ================================================== -->
    <div 
        x-show="presentationMode"
        x-cloak
        class="fixed inset-0 z-50 bg-slate-950 text-white flex flex-col justify-between p-4 sm:p-8 select-none"
    >
        <!-- Top Presentation Bar -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <span class="text-xl sm:text-2xl font-black tracking-tight text-emerald-400">
                    SBL
                </span>
                <span class="text-xs sm:text-sm font-semibold text-slate-400 uppercase tracking-wider">
                    Career Rank Journey
                </span>
            </div>

            <!-- Slide Indicator & Close Button -->
            <div class="flex items-center gap-3">
                <span class="text-xs sm:text-sm font-bold text-slate-400">
                    Slide <span x-text="currentSlide + 1"></span> of <span x-text="ranks.length"></span>
                </span>
                <button 
                    type="button"
                    @click="exitPresentation()"
                    class="p-2 min-h-[44px] min-w-[44px] inline-flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors"
                    title="Exit Presentation Mode"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Main Slide Content (Optimized for Mobile Portrait Screen) -->
        <div class="my-auto max-w-xl mx-auto w-full py-6 text-center">
            <template x-if="ranks[currentSlide]">
                <div class="space-y-6">
                    <!-- Rank Code & Badge -->
                    <div>
                        <span 
                            class="inline-block text-2xl sm:text-3xl font-black px-4 py-1 rounded-xl shadow-lg"
                            :class="ranks[currentSlide].badge_bg"
                            x-text="ranks[currentSlide].code"
                        ></span>
                        <h2 class="text-2xl sm:text-4xl font-extrabold text-white mt-3" x-text="ranks[currentSlide].name"></h2>
                        <p class="text-sm sm:text-base text-slate-400 mt-1" x-text="ranks[currentSlide].name_bn"></p>
                    </div>

                    <!-- Large Reward Display -->
                    <div class="p-6 rounded-2xl bg-gradient-to-b from-emerald-950/50 to-slate-900 border border-emerald-500/30">
                        <span class="text-xs font-bold uppercase tracking-widest text-emerald-400">
                            Milestone Cash Reward
                        </span>
                        <div class="text-3xl sm:text-5xl font-black text-emerald-400 mt-2" x-text="ranks[currentSlide].reward"></div>
                        <span class="text-xs text-slate-400 mt-1 block">Guaranteed milestone recognition</span>
                    </div>

                    <!-- Qualification Requirements -->
                    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 text-left space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">
                            Qualification Requirement
                        </span>
                        <p class="text-base sm:text-lg font-bold text-white" x-text="ranks[currentSlide].req_main"></p>
                        
                        <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-800 text-sm">
                            <div class="p-2.5 rounded-lg bg-slate-800/60">
                                <span class="text-[11px] text-slate-400 block">Left Team</span>
                                <span class="font-bold text-emerald-400" x-text="ranks[currentSlide].left_team"></span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-slate-800/60">
                                <span class="text-[11px] text-slate-400 block">Right Team</span>
                                <span class="font-bold text-emerald-400" x-text="ranks[currentSlide].right_team"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Presentation Bottom Controls -->
        <div class="pt-4 border-t border-slate-800 flex items-center justify-between gap-3 max-w-xl mx-auto w-full">
            <button 
                type="button"
                @click="prevSlide()"
                :disabled="currentSlide === 0"
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 min-h-[44px] rounded-xl text-sm font-semibold bg-slate-800 hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed text-white transition-all"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Previous</span>
            </button>

            <!-- Quick Share from Presentation -->
            <button 
                type="button"
                @click="shareWhatsApp(ranks[currentSlide])"
                class="inline-flex items-center justify-center px-4 py-2.5 min-h-[44px] rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-500 text-white transition-colors"
            >
                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z" />
                </svg>
                <span>Share</span>
            </button>

            <button 
                type="button"
                @click="nextSlide()"
                :disabled="currentSlide === ranks.length - 1"
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 min-h-[44px] rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-500 disabled:opacity-30 disabled:cursor-not-allowed text-white transition-all"
            >
                <span>Next</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>


    <!-- ================================================== -->
    <!-- TOAST NOTIFICATION                                 -->
    <!-- ================================================== -->
    <div 
        x-show="showToast"
        x-cloak
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-4 py-3 rounded-xl shadow-lg border border-slate-800 flex items-center gap-2 text-sm"
    >
        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span x-text="toastMessage"></span>
    </div>

</div>
