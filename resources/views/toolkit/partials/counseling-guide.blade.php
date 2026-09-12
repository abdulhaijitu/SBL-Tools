{{-- SBL Marketing - Prospect Counseling Guide (Streamlined Assistant) --}}
@php
    $questions = $counselingConfig['discoveryQuestions'] ?? [];
    $results = $counselingConfig['results'] ?? [];
    $talkingPoints = $counselingConfig['talkingPoints'] ?? [];
    $safeWording = $counselingConfig['safeWording'] ?? [];
    $objections = $counselingConfig['objections'] ?? [];
    $nextSteps = $counselingConfig['nextSteps'] ?? [];
@endphp

<div x-data="{
    // Step wizard for mobile (1 to 5, 6 = result view)
    mobileStep: 1,
    totalQuestions: {{ count($questions) }},

    // Answers object
    answers: {
        goal: 'capital_return',
        budget: 'b_120k',
        involvement: 'managed',
        network: 'net_some',
        priority: 'pri_safety'
    },

    // Active objection accordion
    activeObjection: null,
    copiedPoints: false,

    // Calculate recommended mindset based on answers
    get recommendedPath() {
        let scores = { investor: 0, networker: 0, hybrid: 0 };

        // Goal weighting
        if (this.answers.goal === 'capital_return') scores.investor += 3;
        else if (this.answers.goal === 'extra_income') scores.networker += 3;
        else if (this.answers.goal === 'both') scores.hybrid += 3;

        // Budget weighting
        if (this.answers.budget === 'b_550k') scores.investor += 2;
        else if (this.answers.budget === 'b_10k') scores.networker += 2;
        else if (this.answers.budget === 'b_120k') scores.hybrid += 2;

        // Involvement weighting
        if (this.answers.involvement === 'managed') scores.investor += 3;
        else if (this.answers.involvement === 'active_net') scores.networker += 3;
        else if (this.answers.involvement === 'biz_net') scores.hybrid += 3;

        // Network weighting
        if (this.answers.network === 'net_yes') scores.networker += 2;
        else if (this.answers.network === 'net_no') scores.investor += 2;
        else if (this.answers.network === 'net_some') scores.hybrid += 2;

        // Priority weighting
        if (this.answers.priority === 'pri_safety') scores.investor += 3;
        else if (this.answers.priority === 'pri_cashflow') scores.networker += 3;
        else if (this.answers.priority === 'pri_growth') scores.hybrid += 3;

        if (scores.investor > scores.networker && scores.investor > scores.hybrid) {
            return 'investor';
        } else if (scores.networker > scores.investor && scores.networker > scores.hybrid) {
            return 'networker';
        } else {
            return 'hybrid';
        }
    },

    selectOption(questionId, optionId) {
        this.answers[questionId] = optionId;
    },

    nextMobileStep() {
        if (this.mobileStep < this.totalQuestions) {
            this.mobileStep++;
        } else {
            this.mobileStep = 6; // Results screen on mobile
            this.scrollToResult();
        }
    },

    prevMobileStep() {
        if (this.mobileStep > 1) {
            this.mobileStep--;
        }
    },

    changeAnswers() {
        this.mobileStep = 1;
        const qSection = document.getElementById('discovery-section');
        if (qSection) {
            qSection.scrollIntoView({ behavior: 'smooth' });
        }
    },

    scrollToResult() {
        this.$nextTick(() => {
            const el = document.getElementById('result-section');
            if (el) el.scrollIntoView({ behavior: 'smooth' });
        });
    },

    scrollToPoints() {
        this.$nextTick(() => {
            const el = document.getElementById('talking-points-section');
            if (el) el.scrollIntoView({ behavior: 'smooth' });
        });
    },

    copyTalkingPoints() {
        const path = this.recommendedPath;
        let points = @js($talkingPoints);
        let list = points[path] || [];
        let text = 'SBL Counseling Talking Points (' + path.toUpperCase() + '):\n\n';
        list.forEach((pt, i) => {
            text += (i + 1) + '. ' + pt.text + '\n';
        });

        navigator.clipboard.writeText(text).then(() => {
            this.copiedPoints = true;
            setTimeout(() => { this.copiedPoints = false; }, 2500);
        });
    }
}" class="max-w-6xl mx-auto space-y-8 pb-12 text-slate-800">

    {{-- 1. PAGE HEADER (Compact, Minimal, Presentation-Friendly) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 md:p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold tracking-wide uppercase mb-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span data-en="SBL Prospect Counseling Tool • Investor vs Networker Discovery" data-bn="SBL প্রসপেক্ট কাউন্সেলিং টুল • ইনভেস্টর বনাম নেটওয়ার্কার ডিসকভারি">
                    SBL Prospect Counseling Tool • Investor vs Networker Discovery
                </span>
            </div>
            <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight" data-en="Counseling Guide" data-bn="কাউন্সেলিং গাইড">
                Counseling Guide
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5" data-en="Ask a few questions and find the right discussion path." data-bn="প্রসপেক্টকে কিছু সহজ প্রশ্ন করুন এবং সঠিক আলোচনার পথ বেছে নিন।">
                Ask a few questions and find the right discussion path.
            </p>
        </div>

        {{-- Quick Indicator of Current Recommended Path --}}
        <div class="flex items-center gap-2 self-start md:self-auto bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200 text-xs">
            <span class="text-slate-500 font-medium" data-en="Active Direction:" data-bn="বর্তমান অভিমুখ:">Active Direction:</span>
            <span class="font-bold uppercase tracking-wider px-2 py-0.5 rounded-md text-[11px]"
                  :class="{
                      'bg-amber-100 text-amber-800 border border-amber-300': recommendedPath === 'investor',
                      'bg-indigo-100 text-indigo-800 border border-indigo-300': recommendedPath === 'networker',
                      'bg-emerald-100 text-emerald-800 border border-emerald-300': recommendedPath === 'hybrid'
                  }"
                  x-text="recommendedPath === 'investor' ? 'Investor Mindset' : (recommendedPath === 'networker' ? 'Networker Mindset' : 'Hybrid Builder')">
            </span>
        </div>
    </div>

    {{-- 2. MAIN DISCOVERY SECTION (Ask These 5 Questions) --}}
    <div id="discovery-section" class="bg-white rounded-2xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600" data-en="Prospect Discovery" data-bn="প্রসপেক্ট ডিসকভারি">Prospect Discovery</span>
                <h2 class="text-lg md:text-xl font-bold text-slate-900" data-en="Ask These 5 Questions" data-bn="এই ৫টি প্রশ্ন করুন">
                    Ask These 5 Questions
                </h2>
                <p class="text-xs text-slate-500" data-en="Identify what the prospect is mainly looking for in SBL." data-bn="প্রসপেক্ট মূলত SBL-এ কী খুঁজছেন তা সহজে শনাক্ত করুন।">
                    Identify what the prospect is mainly looking for in SBL.
                </p>
            </div>

            {{-- Mobile Step Dots Indicator (<= 768px) --}}
            <div class="flex md:hidden items-center gap-1.5 self-start sm:self-auto bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                <span class="text-xs font-bold text-slate-600 mr-1" x-text="'Question ' + (mobileStep <= 5 ? mobileStep : 5) + ' of 5'"></span>
                @foreach($questions as $index => $q)
                    <span class="w-2 h-2 rounded-full transition-all"
                          :class="mobileStep === {{ $index + 1 }} ? 'bg-emerald-600 w-4' : (mobileStep > {{ $index + 1 }} ? 'bg-emerald-400' : 'bg-slate-300')">
                    </span>
                @endforeach
            </div>
        </div>

        {{-- MOBILE VIEW: 1 Question at a time wizard (< 768px) --}}
        <div class="block md:hidden space-y-5">
            @foreach($questions as $index => $q)
                <div x-show="mobileStep === {{ $index + 1 }}" x-cloak class="space-y-4">
                    <div class="flex items-start gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center shrink-0 mt-0.5">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <h3 class="font-bold text-sm text-slate-900" data-en="{{ $q['q_en'] }}" data-bn="{{ $q['q_bn'] }}">
                                {{ $q['q_en'] }}
                            </h3>
                            <span class="text-[11px] text-slate-400" data-en="Tap one option below:" data-bn="নিচের যেকোনো একটি অপশনে চাপ দিন:">Tap one option below:</span>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($q['options'] as $opt)
                            <button type="button"
                                    @click="selectOption('{{ $q['id'] }}', '{{ $opt['id'] }}')"
                                    class="w-full text-left p-3.5 rounded-xl border text-xs font-semibold transition-all flex items-center justify-between"
                                    :class="answers['{{ $q['id'] }}'] === '{{ $opt['id'] }}'
                                        ? 'bg-emerald-50/80 border-emerald-500 text-emerald-950 font-bold shadow-xs ring-1 ring-emerald-400'
                                        : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300'">
                                <span data-en="{{ $opt['label_en'] }}" data-bn="{{ $opt['label_bn'] }}">{{ $opt['label_en'] }}</span>
                                <span class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 ml-2"
                                      :class="answers['{{ $q['id'] }}'] === '{{ $opt['id'] }}' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300'">
                                    <template x-if="answers['{{ $q['id'] }}'] === '{{ $opt['id'] }}'">
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Mobile Navigation Buttons --}}
                    <div class="pt-3 flex items-center justify-between gap-3 border-t border-slate-100">
                        <button type="button"
                                @click="prevMobileStep"
                                x-show="mobileStep > 1"
                                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1.5 transition-colors">
                            <span>← Back</span>
                        </button>
                        <div x-show="mobileStep === 1" class="text-[11px] text-slate-400">Step 1 of 5</div>

                        <button type="button"
                                @click="nextMobileStep"
                                class="ml-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                            <span x-text="mobileStep < 5 ? 'Next Question →' : 'See Suggested Path ↓'"></span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- DESKTOP VIEW: Clean Compact Layout showing all 5 questions (>= 768px) --}}
        <div class="hidden md:grid md:grid-cols-1 lg:grid-cols-5 gap-4">
            @foreach($questions as $index => $q)
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Q{{ $index + 1 }}</span>
                        </div>
                        <h3 class="font-bold text-xs text-slate-900 leading-snug" data-en="{{ $q['q_en'] }}" data-bn="{{ $q['q_bn'] }}">
                            {{ $q['q_en'] }}
                        </h3>
                    </div>

                    <div class="space-y-1.5 pt-1">
                        @foreach($q['options'] as $opt)
                            <button type="button"
                                    @click="selectOption('{{ $q['id'] }}', '{{ $opt['id'] }}')"
                                    class="w-full text-left p-2.5 rounded-lg border text-[11px] transition-all flex items-start justify-between gap-1.5"
                                    :class="answers['{{ $q['id'] }}'] === '{{ $opt['id'] }}'
                                        ? 'bg-white border-emerald-600 text-emerald-950 font-bold shadow-xs ring-1 ring-emerald-500'
                                        : 'bg-white/80 border-slate-200 text-slate-600 hover:bg-white hover:border-slate-300'">
                                <span data-en="{{ $opt['label_en'] }}" data-bn="{{ $opt['label_bn'] }}">{{ $opt['label_en'] }}</span>
                                <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0 mt-0.5"
                                      :class="answers['{{ $q['id'] }}'] === '{{ $opt['id'] }}' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300'">
                                    <template x-if="answers['{{ $q['id'] }}'] === '{{ $opt['id'] }}'">
                                        <svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 3. RESULT / SUGGESTED PATH (ONE RESULT ONLY) --}}
    <div id="result-section" class="scroll-mt-6">
        @foreach($results as $key => $res)
            <div x-show="recommendedPath === '{{ $key }}'"
                 x-cloak
                 class="rounded-2xl border p-5 md:p-7 shadow-sm transition-all"
                 :class="{
                     'bg-amber-50/50 border-amber-300 ring-1 ring-amber-200/50': '{{ $key }}' === 'investor',
                     'bg-indigo-50/50 border-indigo-300 ring-1 ring-indigo-200/50': '{{ $key }}' === 'networker',
                     'bg-emerald-50/50 border-emerald-300 ring-1 ring-emerald-200/50': '{{ $key }}' === 'hybrid'
                 }">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4"
                     :class="{
                         'border-amber-200': '{{ $key }}' === 'investor',
                         'border-indigo-200': '{{ $key }}' === 'networker',
                         'border-emerald-200': '{{ $key }}' === 'hybrid'
                     }">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md"
                                  :class="{
                                      'bg-amber-200/80 text-amber-900': '{{ $key }}' === 'investor',
                                      'bg-indigo-200/80 text-indigo-900': '{{ $key }}' === 'networker',
                                      'bg-emerald-200/80 text-emerald-900': '{{ $key }}' === 'hybrid'
                                  }"
                                  data-en="Recommended Path" data-bn="প্রস্তাবিত অভিমুখ">
                                Recommended Path
                            </span>
                            <span class="text-xs text-slate-500 font-medium" data-en="({{ $res['badge_en'] }})" data-bn="({{ $res['badge_bn'] }})">
                                ({{ $res['badge_en'] }})
                            </span>
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight"
                            data-en="{{ $res['name_en'] }}" data-bn="{{ $res['name_bn'] }}">
                            {{ $res['name_en'] }}
                        </h2>
                        <p class="text-xs md:text-sm text-slate-600 mt-1 max-w-2xl"
                           data-en="{{ $res['description_en'] }}" data-bn="{{ $res['description_bn'] }}">
                            {{ $res['description_en'] }}
                        </p>
                    </div>

                    {{-- Change Answers Button --}}
                    <button type="button"
                            @click="changeAnswers"
                            class="self-start md:self-auto px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs font-bold shadow-2xs flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        <span data-en="Change Answers" data-bn="উত্তর পরিবর্তন করুন">Change Answers</span>
                    </button>
                </div>

                <div class="py-5 space-y-3">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider"
                        data-en="Best suited for prospects mainly interested in:"
                        data-bn="যেসব প্রসপেক্ট মূলত এগুলোতে আগ্রহী তাদের জন্য উপযুক্ত:">
                        Best suited for prospects mainly interested in:
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach($res['best_suited'] as $item)
                            <div class="flex items-start gap-2 text-xs font-medium text-slate-700 bg-white/70 p-2.5 rounded-xl border border-slate-200/80">
                                <span class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold"
                                      :class="{
                                          'text-amber-700 bg-amber-100': '{{ $key }}' === 'investor',
                                          'text-indigo-700 bg-indigo-100': '{{ $key }}' === 'networker',
                                          'text-emerald-700 bg-emerald-100': '{{ $key }}' === 'hybrid'
                                      }">✓</span>
                                <span data-en="{{ $item['en'] }}" data-bn="{{ $item['bn'] }}">{{ $item['en'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-4 border-t flex flex-wrap items-center gap-3"
                     :class="{
                         'border-amber-200': '{{ $key }}' === 'investor',
                         'border-indigo-200': '{{ $key }}' === 'networker',
                         'border-emerald-200': '{{ $key }}' === 'hybrid'
                     }">
                    <button type="button"
                            @click="scrollToPoints"
                            class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-colors">
                        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        <span data-en="View Talking Points" data-bn="টকিং পয়েন্ট দেখুন">View Talking Points</span>
                    </button>

                    @foreach($res['actions'] as $act)
                        @if(isset($act['route']))
                            <a href="{{ route($act['route']) }}"
                               class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-colors">
                                <span data-en="{{ $act['label_en'] }}" data-bn="{{ $act['label_bn'] }}">{{ $act['label_en'] }}</span>
                                <span>→</span>
                            </a>
                        @endif
                    @endforeach
                </div>

                {{-- Small Regulatory / Guidance Note --}}
                <div class="mt-4 pt-3 border-t text-[11px] text-slate-500 italic"
                     :class="{
                         'border-amber-200/60': '{{ $key }}' === 'investor',
                         'border-indigo-200/60': '{{ $key }}' === 'networker',
                         'border-emerald-200/60': '{{ $key }}' === 'hybrid'
                     }"
                     data-en="Suggested direction only. Final decision should be based on current SBL terms and the prospect’s own assessment."
                     data-bn="এটি শুধুমাত্র প্রস্তাবিত আলোচনার দিকনির্দেশনা। চূড়ান্ত সিদ্ধান্ত SBL নীতিমালা ও প্রসপেক্টের নিজস্ব মূল্যায়নের ওপর ভিত্তি করে গ্রহণ করা উচিত।">
                    Suggested direction only. Final decision should be based on current SBL terms and the prospect’s own assessment.
                </div>
            </div>
        @endforeach
    </div>

    {{-- 4. TALKING POINTS (6 Clear Points Contextual to Selected Path) --}}
    <div id="talking-points-section" class="bg-white rounded-2xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-5 scroll-mt-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600" data-en="Counselor Talking Points" data-bn="কাউন্সেলর টকিং পয়েন্ট">Counselor Talking Points</span>
                <h3 class="text-base md:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span x-text="recommendedPath === 'investor' ? 'Investor Talking Points' : (recommendedPath === 'networker' ? 'Networker Talking Points' : 'Hybrid Business Builder Talking Points')"></span>
                </h3>
                <p class="text-xs text-slate-500" data-en="Explain these 6 points in simple words. Do not overwhelm with complex financial math." data-bn="সহজ ভাষায় এই ৬টি পয়েন্ট বুঝিয়ে বলুন। অপ্রয়োজনীয় জটিল হিসাব পরিহার করুন।">
                    Explain these 6 points in simple words. Do not overwhelm with complex financial math.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        @click="copyTalkingPoints"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                    <span x-text="copiedPoints ? 'Copied!' : 'Copy Points'"></span>
                </button>
            </div>
        </div>

        {{-- Points Grid (Contextual to Path) --}}
        @foreach($talkingPoints as $pathKey => $points)
            <div x-show="recommendedPath === '{{ $pathKey }}'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                @foreach($points as $idx => $pt)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition-colors flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center shrink-0 mt-0.5">
                            {{ $pt['step'] }}
                        </span>
                        <div class="space-y-0.5">
                            <p class="text-xs font-semibold text-slate-800 leading-relaxed" data-en="{{ $pt['text'] }}" data-bn="{{ $pt['text_bn'] }}">
                                {{ $pt['text'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        {{-- Direct Action Links from Talking Points --}}
        <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2">
            <span class="text-xs text-slate-500 font-medium" data-en="Explore complete details:" data-bn="বিস্তারিত তথ্য দেখতে লিংকে যান:">Explore complete details:</span>
            <template x-if="recommendedPath === 'investor'">
                <a href="{{ route('packages.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 underline flex items-center gap-1">
                    <span>View Packages</span>
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            </template>
            <template x-if="recommendedPath === 'networker'">
                <div class="flex items-center gap-3">
                    <a href="{{ route('ranks.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 underline flex items-center gap-1">
                        <span>View Ranks</span>
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                    <a href="{{ route('commission.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 underline flex items-center gap-1">
                        <span>View Commission Plan</span>
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                </div>
            </template>
            <template x-if="recommendedPath === 'hybrid'">
                <div class="flex items-center gap-3">
                    <a href="{{ route('packages.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 underline flex items-center gap-1">
                        <span>View Packages</span>
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                    <a href="{{ route('ranks.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 underline flex items-center gap-1">
                        <span>View Ranks</span>
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                </div>
            </template>
        </div>
    </div>

    {{-- 5. IMPORTANT: SAFE WORDING (Compact Compliance Section) --}}
    <div class="bg-amber-50/60 border border-amber-200/80 rounded-2xl p-5 md:p-6 space-y-4">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs">⚠️</span>
            <div>
                <h3 class="font-bold text-sm md:text-base text-amber-950" data-en="Use Safe & Clear Language" data-bn="নিরাপদ ও স্পষ্ট শব্দচয়ন ব্যবহার করুন">
                    Use Safe & Clear Language
                </h3>
                <p class="text-xs text-amber-800" data-en="Maintain trust and adhere strictly to verified SBL business terminology." data-bn="প্রসপেক্টের আস্থা ও নিয়মনীতি রক্ষায় সঠিক ব্যবসায়িক পরিভাষা ব্যবহার করুন।">
                    Maintain trust and adhere strictly to verified SBL business terminology.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            @foreach($safeWording as $claim)
                <div class="p-3.5 bg-white rounded-xl border border-amber-200 space-y-2 shadow-2xs">
                    <div class="flex items-start gap-2 text-rose-700 font-semibold">
                        <span class="font-bold text-rose-600 shrink-0">✕ Avoid:</span>
                        <span data-en="“{{ $claim['avoid'] }}”" data-bn="“{{ $claim['avoid_bn'] }}”">“{{ $claim['avoid'] }}”</span>
                    </div>
                    <div class="flex items-start gap-2 text-emerald-800 font-bold border-t border-slate-100 pt-1.5">
                        <span class="text-emerald-600 shrink-0">✓ Use:</span>
                        <span data-en="“{{ $claim['use'] }}”" data-bn="“{{ $claim['use_bn'] }}”">“{{ $claim['use'] }}”</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 6. COMMON QUESTIONS (FAQ Accordion - Max 6) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-4">
        <div class="border-b border-slate-100 pb-3">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600" data-en="Common Questions" data-bn="সাধারণ প্রশ্নোত্তর">Common Questions</span>
            <h3 class="text-base md:text-lg font-bold text-slate-900" data-en="6 Common Prospect Questions & Direct Answers" data-bn="প্রসপেক্টের ৬টি সাধারণ প্রশ্ন ও সরাসরি উত্তর">
                6 Common Prospect Questions & Direct Answers
            </h3>
            <p class="text-xs text-slate-500" data-en="Clear, verified answers grounded in official SBL operational policies." data-bn="SBL অফিশিয়াল নীতিমালা অনুযায়ী সংক্ষিপ্ত ও স্পষ্ট উত্তর।">
                Clear, verified answers grounded in official SBL operational policies.
            </p>
        </div>

        <div class="space-y-2.5">
            @foreach($objections as $idx => $obj)
                <div class="border border-slate-200 rounded-xl overflow-hidden transition-colors">
                    <button type="button"
                            @click="activeObjection = activeObjection === {{ $idx }} ? null : {{ $idx }}"
                            class="w-full p-3.5 text-left bg-slate-50 hover:bg-slate-100/80 flex items-center justify-between text-xs md:text-sm font-bold text-slate-800 transition-colors">
                        <span class="flex items-center gap-2.5">
                            <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-800 text-[11px] font-bold flex items-center justify-center shrink-0">
                                {{ $idx + 1 }}
                            </span>
                            <span data-en="{{ $obj['q_en'] }}" data-bn="{{ $obj['q_bn'] }}">{{ $obj['q_en'] }}</span>
                        </span>
                        <svg class="w-4 h-4 text-slate-400 transform transition-transform shrink-0 ml-2"
                             :class="activeObjection === {{ $idx }} ? 'rotate-180 text-emerald-600' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="activeObjection === {{ $idx }}" x-collapse class="p-4 bg-white text-xs text-slate-600 leading-relaxed border-t border-slate-200">
                        <p data-en="{{ $obj['a_en'] }}" data-bn="{{ $obj['a_bn'] }}">{{ $obj['a_en'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 7. NEXT STEP (What should I show next? - 3 Clean Cards) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 md:p-6 shadow-xs space-y-4">
        <div class="border-b border-slate-100 pb-3">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600" data-en="Next Step" data-bn="পরবর্তী পদক্ষেপ">Next Step</span>
            <h3 class="text-base md:text-lg font-bold text-slate-900" data-en="What should I show next?" data-bn="এরপর ক্লায়েন্টকে কোন পেজটি দেখাবেন?">
                What should I show next?
            </h3>
            <p class="text-xs text-slate-500" data-en="Direct the prospect to the exact SBL page based on their discussion focus." data-bn="আলোচনার বিষয়বস্তু অনুযায়ী সংশ্লিষ্ট SBL পেজে প্রবেশ করুন।">
                Direct the prospect to the exact SBL page based on their discussion focus.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($nextSteps as $step)
                <a href="{{ route($step['route']) }}"
                   class="group p-4 rounded-xl border border-slate-200 hover:border-emerald-500 bg-slate-50/50 hover:bg-emerald-50/30 transition-all flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xl">{{ $step['icon'] }}</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-600 group-hover:border-emerald-300 group-hover:text-emerald-700">
                                {{ $step['badge'] }}
                            </span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 group-hover:text-emerald-700 transition-colors"
                            data-en="{{ $step['title'] }}" data-bn="{{ $step['title_bn'] }}">
                            {{ $step['title'] }}
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5"
                           data-en="{{ $step['desc'] }}" data-bn="{{ $step['desc_bn'] }}">
                            {{ $step['desc'] }}
                        </p>
                    </div>

                    <div class="pt-2 border-t border-slate-200/60 flex items-center text-xs font-bold text-emerald-600 group-hover:text-emerald-700">
                        <span data-en="Open {{ $step['title'] }}" data-bn="{{ $step['title_bn'] }} খুলুন">Open {{ $step['title'] }}</span>
                        <svg class="w-3.5 h-3.5 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

</div>
