@php
    $root = $treeData['root'];
    $stats = $treeData['stats'] ?? [];
    $leftSlots = $treeData['left_slots'] ?? [];
    $rightSlots = $treeData['right_slots'] ?? [];
    $leftFilledCount = count(array_filter($leftSlots, fn($s) => empty($s['is_vacant'])));
    $rightFilledCount = count(array_filter($rightSlots, fn($s) => empty($s['is_vacant'])));
    $totalDirectCount = $leftFilledCount + $rightFilledCount;
    $parentNode = $treeData['parent_node'] ?? null;
    $isMainRoot = !empty($treeData['is_main_root']);
    $breadcrumbs = $treeData['breadcrumbs'] ?? [];
@endphp

<div class="space-y-5" x-data="{ 
    mobileBranchTab: 'LEFT',
    showMatchingInfo: false,
    showOnboarding: localStorage.getItem('sbl_hide_team_onboarding') !== '1'
}">

    <!-- ==================== 1. COMPACT TOP NAVIGATION & BREADCRUMBS ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs px-4 py-3 flex flex-wrap items-center justify-between gap-3">
        <!-- Navigation Controls: My Team & Back -->
        <div class="flex items-center gap-2">
            <a href="{{ route('team.index', ['owner_id' => $ownerId]) }}" 
               class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs"
               data-en="🏠 My Team" data-bn="🏠 আমার টিম">
                <span>🏠</span>
                <span data-en="My Team" data-bn="আমার টিম">My Team</span>
            </a>

            @if($parentNode)
                <a href="{{ route('team.show', ['memberId' => $parentNode->id, 'owner_id' => $ownerId]) }}" 
                   class="px-3 py-1.5 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-800 text-xs font-bold transition-all flex items-center gap-1.5 border border-orange-200/80 shadow-2xs">
                    <span>←</span>
                    <span data-en="Back" data-bn="পূর্ববর্তী">Back</span>
                    <span class="text-[11px] text-orange-600 font-normal hidden sm:inline">({{ $parentNode->member_name }})</span>
                </a>
            @endif
        </div>

        <!-- Breadcrumb Path (Collapsible if long) -->
        <div class="flex items-center gap-1.5 text-xs text-slate-600 overflow-x-auto max-w-full py-0.5">
            @php $totalBc = count($breadcrumbs); @endphp
            @if($totalBc > 0)
                @foreach($breadcrumbs as $idx => $bc)
                    @if($totalBc > 3 && $idx > 0 && $idx < $totalBc - 2)
                        @if($idx === 1)
                            <span class="text-slate-300 font-bold">›</span>
                            <span class="text-slate-400 font-mono text-[11px] px-1 bg-slate-50 rounded" title="Collapsed intermediate uplines">...</span>
                        @endif
                        @continue
                    @endif

                    @if($idx > 0)
                        <span class="text-slate-300 font-bold">›</span>
                    @endif

                    @if(!empty($bc['is_current']))
                        <span class="text-orange-700 font-black whitespace-nowrap bg-orange-50 px-2 py-0.5 rounded-lg border border-orange-200/80 shadow-2xs">
                            {{ $bc['name'] }}
                        </span>
                    @else
                        <a href="{{ route('team.show', ['memberId' => $bc['id'], 'owner_id' => $ownerId]) }}" 
                           class="text-slate-700 hover:text-orange-600 font-semibold transition-colors whitespace-nowrap">
                            {{ $bc['name'] }}
                        </a>
                    @endif
                @endforeach
            @else
                <span class="text-orange-700 font-black whitespace-nowrap bg-orange-50 px-2 py-0.5 rounded-lg border border-orange-200/80 shadow-2xs">
                    {{ $root->member_name ?? 'Team Root' }}
                </span>
            @endif
        </div>
    </div>

    <!-- ==================== ONBOARDING HINT (Dismissible) ==================== -->
    <template x-if="showOnboarding">
        <div class="bg-gradient-to-r from-orange-500/10 via-amber-500/10 to-orange-500/5 px-4 py-2.5 rounded-2xl border border-orange-200/80 flex items-center justify-between gap-3 text-xs shadow-2xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="text-base flex-shrink-0">💡</span>
                <p class="text-slate-700 leading-normal text-xs truncate sm:whitespace-normal" 
                   data-en="Each member can have 5 Left + 5 Right direct members. Click 'Explore Team' on any member to explore recursively." 
                   data-bn="প্রতিটি সদস্যের সর্বোচ্চ ৫টি বাম ও ৫টি ডান ডিরেক্ট স্লট রয়েছে। বিস্তারিত দেখতে মেম্বারের 'Explore Team'-এ চাপুন।">
                    Each member can have 5 Left + 5 Right direct members. Click 'Explore Team' on any member to explore recursively.
                </p>
            </div>
            <button type="button" 
                    @click="showOnboarding = false; localStorage.setItem('sbl_hide_team_onboarding', '1')" 
                    class="px-2.5 py-1 rounded-lg bg-orange-100 hover:bg-orange-200 text-orange-800 text-[11px] font-bold cursor-pointer whitespace-nowrap transition-colors"
                    data-en="Got it" data-bn="বুঝেছি">
                Got it
            </button>
        </div>
    </template>

    <!-- ==================== 2. CURRENT ACTIVE MEMBER CARD (COMPACT) ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 bg-gradient-to-r from-slate-950 via-slate-900 to-orange-950 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Left Info: Avatar, Name, Code, Rank, Sponsor -->
            <div class="flex items-center gap-3.5">
                <div data-current-member-initial class="w-13 h-13 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 text-white font-black text-2xl flex items-center justify-center shadow-md border border-white/20 flex-shrink-0">
                    {{ substr($root->member_name ?? 'M', 0, 1) }}
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 data-current-member-name class="text-lg font-black text-white tracking-tight truncate">
                            {{ $root->member_name ?? 'Team Root' }}
                        </h2>
                        <span data-current-member-rank class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-orange-500/30 text-orange-200 border border-orange-400/40">
                            {{ $root->rank_name ?? 'Member' }}
                        </span>
                        @if(!empty($stats['is_fme']))
                            <span data-current-member-fme class="px-2 py-0.5 rounded-full text-[9px] font-black bg-emerald-500/30 text-emerald-200 border border-emerald-400/40">
                                🌟 FME QUALIFIED
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-300 font-mono mt-1">
                        <span data-current-member-code class="text-orange-300 font-bold">
                            {{ $root->member_code ?? ('SBL-' . $root->id) }}
                        </span>
                        @if($root->phone)
                            <span data-current-member-phone-sep class="text-slate-500">•</span>
                            <span data-current-member-phone class="text-slate-300">{{ $root->phone }}</span>
                        @endif
                        <span class="text-slate-500">•</span>
                        <span class="text-slate-400 font-sans">
                            <span data-en="Sponsor:" data-bn="স্পন্সর:">Sponsor:</span>
                            <strong data-current-member-sponsor class="text-slate-200 font-semibold">{{ $stats['sponsor_name'] ?? 'Not assigned' }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Actions: Profile -->
            <div class="flex items-center gap-2 self-start sm:self-center flex-shrink-0">
                <button type="button" 
                        data-btn-full-details
                        @click="openDetailsModal({{ $root->id }})" 
                        class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold border border-white/15 transition-all flex items-center gap-1.5 shadow-2xs active:scale-95 cursor-pointer">
                    <span>👁️</span>
                    <span data-en="Profile" data-bn="প্রোফাইল">Profile</span>
                </button>
            </div>
        </div>

        <!-- ==================== 3. 4 COMPACT SUMMARY STATS CARDS ==================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-y lg:divide-y-0 divide-x-0 lg:divide-x divide-slate-100 bg-slate-50/80 border-t border-slate-200/80 text-xs">
            
            <!-- 1. LEFT TEAM -->
            <div class="p-3.5 sm:p-4 space-y-1">
                <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-emerald-800">
                    <span class="flex items-center gap-1">👈 <span data-en="Left Team" data-bn="বাম টিম">Left Team</span></span>
                    <span class="font-mono bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded text-[10px]">
                        {{ $leftFilledCount }}/5 <span data-en="Direct" data-bn="ডিরেক্ট">Direct</span>
                    </span>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xl sm:text-2xl font-black text-slate-900" data-stat-left-bv>{{ number_format($stats['left_bv'] ?? 0) }}</span>
                    <span class="text-xs font-bold text-slate-500">BV</span>
                </div>
                <div class="text-[11px] text-slate-500 flex items-center justify-between">
                    <span><strong class="text-slate-700" data-stat-left-network>{{ $stats['total_left_network'] ?? 0 }}</strong> <span data-en="Network" data-bn="নেটওয়ার্ক">Network</span></span>
                </div>
            </div>

            <!-- 2. RIGHT TEAM -->
            <div class="p-3.5 sm:p-4 space-y-1">
                <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-blue-800">
                    <span class="flex items-center gap-1">👉 <span data-en="Right Team" data-bn="ডান টিম">Right Team</span></span>
                    <span class="font-mono bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded text-[10px]">
                        {{ $rightFilledCount }}/5 <span data-en="Direct" data-bn="ডিরেক্ট">Direct</span>
                    </span>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xl sm:text-2xl font-black text-slate-900" data-stat-right-bv>{{ number_format($stats['right_bv'] ?? 0) }}</span>
                    <span class="text-xs font-bold text-slate-500">BV</span>
                </div>
                <div class="text-[11px] text-slate-500 flex items-center justify-between">
                    <span><strong class="text-slate-700" data-stat-right-network>{{ $stats['total_right_network'] ?? 0 }}</strong> <span data-en="Network" data-bn="নেটওয়ার্ক">Network</span></span>
                </div>
            </div>

            <!-- 3. MATCHED PAIRS -->
            <div class="p-3.5 sm:p-4 space-y-1">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-600 flex items-center justify-between">
                    <span>⚖️ <span data-en="Matched Pairs" data-bn="ম্যাচিং পেয়ার">Matched Pairs</span></span>
                    <span class="text-[10px] text-slate-400 font-mono" title="1 Pair = 100 BV matched unit">ⓘ 100 BV</span>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xl sm:text-2xl font-black text-orange-600" data-stat-matched-pairs>{{ $stats['matched_pairs'] ?? 0 }}</span>
                    <span class="text-xs font-bold text-slate-500" data-en="Pairs" data-bn="পেয়ার">Pairs</span>
                </div>
                <div class="text-[11px] text-slate-500">
                    <span data-en="Matched Volume" data-bn="ম্যাচিং ভলিউম">Matched Volume</span>
                </div>
            </div>

            <!-- 4. CARRY (L/R) -->
            <div class="p-3.5 sm:p-4 space-y-1 relative">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-600 flex items-center justify-between">
                    <span>📦 <span data-en="Carry Points" data-bn="ক্যারি পয়েন্ট">Carry Points</span></span>
                    <button type="button" 
                            @click="showMatchingInfo = !showMatchingInfo" 
                            class="text-[10px] text-orange-600 hover:text-orange-700 font-bold cursor-pointer"
                            title="How matching works">ⓘ Info</button>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-lg sm:text-xl font-black text-slate-900" data-stat-carry-display>
                        L: <span class="text-emerald-700" data-stat-carry-left>{{ $stats['carry_left'] ?? 0 }}</span> 
                        <span class="text-slate-300">|</span> 
                        R: <span class="text-blue-700" data-stat-carry-right>{{ $stats['carry_right'] ?? 0 }}</span>
                    </span>
                </div>
                <div class="text-[11px] text-slate-400">
                    <span data-en="Forwarded balance" data-bn="পরবর্তী ব্যালান্স">Forwarded balance</span>
                </div>

                <!-- Info Popover -->
                <div x-show="showMatchingInfo" 
                     @click.away="showMatchingInfo = false" 
                     class="absolute z-20 right-2 top-10 w-64 p-3 bg-slate-900 text-white rounded-xl shadow-xl text-[11px] space-y-1 border border-slate-700" 
                     x-cloak>
                    <div class="font-bold text-orange-400 flex items-center justify-between">
                        <span>How Matching & Carry Work</span>
                        <button type="button" @click="showMatchingInfo = false" class="text-slate-400 hover:text-white">&times;</button>
                    </div>
                    <p class="text-slate-300 leading-tight">
                        When both Left and Right legs reach 100 BV, 1 matching pair is formed. The remaining points stay as <strong>Carry Points</strong> for your next placement!
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- ==================== 4. PLACEMENT GUIDANCE & QUICK TEAM BUILDER ==================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <!-- Placement Guidance Card (Suggested for balance) -->
        <div class="p-3.5 bg-gradient-to-br from-amber-50 to-orange-50/50 rounded-2xl border border-amber-200/80 flex flex-col justify-between gap-2 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-900 flex items-center gap-1.5">
                    <span>⚡</span>
                    <span data-en="Placement Guidance" data-bn="প্লেসমেন্ট পরামর্শ">Placement Guidance</span>
                </span>
                <span class="text-[10px] text-amber-700 bg-amber-200/60 px-2 py-0.5 rounded-full font-semibold" data-en="For balance" data-bn="ভারসাম্যের জন্য">
                    For balance
                </span>
            </div>
            
            @php
                $isBalanced = ($stats['left_bv'] ?? 0) === ($stats['right_bv'] ?? 0) && ($stats['carry_left'] ?? 0) === ($stats['carry_right'] ?? 0);
                $recommendedBranch = $weakerLeg ?? 'LEFT';
            @endphp

            @if($isBalanced)
                <div class="text-xs text-slate-700">
                    <span class="font-black text-emerald-700" data-en="Both legs are balanced!" data-bn="উভয় টিম সমতায় রয়েছে!">Both legs are balanced!</span>
                    <div class="text-[11px] text-slate-500 mt-0.5" data-en="You may place your next member on either Left or Right." data-bn="আপনি যেকোনো সাইডে মেম্বার যুক্ত করতে পারেন।">
                        You may place your next member on either Left or Right.
                    </div>
                </div>
            @else
                <div class="text-xs text-slate-700">
                    <div class="flex items-center gap-1.5 font-bold">
                        <span data-en="Suggested leg:" data-bn="প্রস্তাবিত টিম:">Suggested leg:</span>
                        <span class="px-2 py-0.5 rounded font-black {{ $recommendedBranch === 'LEFT' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $recommendedBranch === 'LEFT' ? '👈 LEFT' : '👉 RIGHT' }}
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5">
                        <span data-en="Reason:" data-bn="কারণ:">Reason:</span> 
                        {{ $recommendedBranch === 'LEFT' ? 'Left BV is lower (more match BV needed)' : 'Right BV is lower (more match BV needed)' }}
                    </div>
                </div>
            @endif

            <div>
                @if($autoBalanceSlot)
                    <button type="button" 
                            @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, '{{ $autoBalanceSlot['branch'] }}', {{ $autoBalanceSlot['slot_number'] }})"
                            class="w-full py-1.5 px-3 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs transition-all shadow-xs active:scale-95 flex items-center justify-center gap-1 cursor-pointer">
                        <span>🎯</span>
                        <span data-en="Add to {{ $autoBalanceSlot['branch'] }} (Slot {{ $autoBalanceSlot['slot_number'] }})" data-bn="{{ $autoBalanceSlot['branch'] }} টিমে যুক্ত করুন (স্লট {{ $autoBalanceSlot['slot_number'] }})">
                            Add to {{ $autoBalanceSlot['branch'] }} (Slot {{ $autoBalanceSlot['slot_number'] }})
                        </span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Quick 1-Tap Team Builder -->
        <div class="md:col-span-2 p-3.5 bg-white rounded-2xl border border-slate-200/80 flex flex-col justify-between gap-3 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                    <span>🚀</span>
                    <span data-en="Quick Team Builder" data-bn="কুইক টিম বিল্ডার">Quick Team Builder</span>
                </span>
                <span class="text-[11px] text-slate-400 font-medium" data-en="1-tap empty slot selection" data-bn="১-ক্লিক খালি স্লট প্লেসমেন্ট">
                    1-tap empty slot selection
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <!-- Add Left Button -->
                @if($firstVacantLeft)
                    <button type="button" 
                            @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, 'LEFT', {{ $firstVacantLeft['slot_number'] }})"
                            class="py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>👈</span>
                        <span>+ Add Left (L{{ $firstVacantLeft['slot_number'] }})</span>
                    </button>
                @else
                    <button type="button" disabled class="py-2.5 px-3 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed text-center">
                        Left Full (5/5)
                    </button>
                @endif

                <!-- Add Right Button -->
                @if($firstVacantRight)
                    <button type="button" 
                            @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, 'RIGHT', {{ $firstVacantRight['slot_number'] }})"
                            class="py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>👉</span>
                        <span>+ Add Right (R{{ $firstVacantRight['slot_number'] }})</span>
                    </button>
                @else
                    <button type="button" disabled class="py-2.5 px-3 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed text-center">
                        Right Full (5/5)
                    </button>
                @endif

                <!-- Auto Balance Button -->
                @if($autoBalanceSlot)
                    <button type="button" 
                            @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, '{{ $autoBalanceSlot['branch'] }}', {{ $autoBalanceSlot['slot_number'] }})"
                            class="py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>🎯</span>
                        <span>Auto Balance</span>
                    </button>
                @else
                    <button type="button" disabled class="py-2.5 px-3 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed text-center">
                        Team Full (10/10)
                    </button>
                @endif
            </div>

            <div class="text-[11px] text-slate-500 flex items-center justify-between">
                <span><span data-en="Active slots occupied:" data-bn="সক্রিয় স্লট সংখ্যা:">Active slots occupied:</span> <strong class="text-slate-800">{{ $totalDirectCount }}/10</strong></span>
                <span class="text-slate-400">Recursive 5+5 model</span>
            </div>
        </div>
    </div>

    <!-- ==================== 5. MOBILE SEGMENTED TAB SWITCHER (<= 767px) ==================== -->
    <div class="block lg:hidden">
        <div class="grid grid-cols-2 p-1 bg-slate-200/80 rounded-2xl border border-slate-200 shadow-inner">
            <button type="button" 
                    @click="mobileBranchTab = 'LEFT'" 
                    class="py-2.5 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 cursor-pointer"
                    :class="mobileBranchTab === 'LEFT' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                <span>👈 <span data-en="Left Team" data-bn="বাম টিম">Left Team</span></span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                      :class="mobileBranchTab === 'LEFT' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-300 text-slate-700'">
                    {{ $leftFilledCount }}/5
                </span>
            </button>

            <button type="button" 
                    @click="mobileBranchTab = 'RIGHT'" 
                    class="py-2.5 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 cursor-pointer"
                    :class="mobileBranchTab === 'RIGHT' ? 'bg-white text-blue-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                <span>👉 <span data-en="Right Team" data-bn="ডান টিম">Right Team</span></span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                      :class="mobileBranchTab === 'RIGHT' ? 'bg-blue-100 text-blue-800' : 'bg-slate-300 text-slate-700'">
                    {{ $rightFilledCount }}/5
                </span>
            </button>
        </div>
    </div>

    <!-- ==================== 6. 10 DIRECT SLOTS (5 LEFT + 5 RIGHT) ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        
        <!-- ==================== LEFT TEAM (5 SLOTS) ==================== -->
        <div class="space-y-3" :class="mobileBranchTab === 'LEFT' ? 'block' : 'hidden lg:block'">
            <div class="flex items-center justify-between border-b-2 border-emerald-500/50 pb-2 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 font-black text-xs flex items-center justify-center">L</span>
                    <h3 class="text-xs sm:text-sm font-black text-slate-900 uppercase tracking-tight" data-en="Left Team (5 Direct Slots)" data-bn="বাম টিম (৫টি ডিরেক্ট স্লট)">
                        Left Team (5 Direct Slots)
                    </h3>
                </div>
                <div class="flex items-center gap-2">
                    <span data-left-header-count class="text-xs font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        {{ $leftFilledCount }}/5 Active
                    </span>
                    @if($firstVacantLeft)
                        <button type="button" 
                                @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, 'LEFT', {{ $firstVacantLeft['slot_number'] }})"
                                class="text-xs font-bold text-emerald-700 hover:text-emerald-900 underline cursor-pointer"
                                data-en="+ Add Next" data-bn="+ পরবর্তী যুক্ত করুন">
                            + Add Next
                        </button>
                    @endif
                </div>
            </div>

            <div data-left-slots-container class="space-y-2.5">
                @for($slot = 1; $slot <= 5; $slot++)
                    @php 
                        $slotData = $leftSlots[$slot] ?? ['is_vacant' => true, 'slot_number' => $slot, 'branch' => 'LEFT']; 
                        $isVacant = !empty($slotData['is_vacant']);
                    @endphp
                    
                    @if($isVacant)
                        <!-- Empty Slot Card -->
                        <div class="p-3.5 rounded-2xl border-2 border-dashed border-emerald-300/80 bg-emerald-50/20 hover:bg-emerald-50/50 transition-all flex items-center justify-between gap-3 group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 font-black flex items-center justify-center text-xs border border-emerald-200 flex-shrink-0">
                                    L{{ $slot }}
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-700">
                                        Slot L{{ $slot }} <span class="text-slate-400 font-normal" data-en="(Available)" data-bn="(খালি)">(Available)</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400" data-en="Click to place member" data-bn="মেম্বার প্লেস করতে ক্লিক করুন">
                                        Click to place member
                                    </div>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, 'LEFT', {{ $slot }})"
                                    class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center gap-1 cursor-pointer">
                                <span>➕</span>
                                <span data-en="Place Member" data-bn="প্লেস করুন">Place Member</span>
                            </button>
                        </div>
                    @else
                        <!-- Occupied Member Card -->
                        @php
                            $childDirectL = $slotData['direct_left_count'] ?? 0;
                            $childDirectR = $slotData['direct_right_count'] ?? 0;
                            $childDirectTotal = $childDirectL + $childDirectR;
                        @endphp
                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200 hover:border-emerald-400/80 shadow-xs hover:shadow-md transition-all space-y-2.5">
                            <div class="flex items-start justify-between gap-2.5">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-orange-400 font-black text-sm flex items-center justify-center shadow-xs flex-shrink-0 border border-slate-700">
                                        {{ substr($slotData['member_name'] ?? 'M', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-black text-[10px]">
                                                L{{ $slot }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                                                GEN 1
                                            </span>
                                            @if(!empty($slotData['is_target']))
                                                <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-1.5 py-0.5 rounded">🎯 Target</span>
                                            @endif
                                        </div>
                                        <div class="font-black text-slate-900 text-xs sm:text-sm mt-0.5 hover:text-orange-600 cursor-pointer truncate"
                                             @click="openDetailsModal({{ $slotData['id'] }})">
                                            {{ $slotData['member_name'] }}
                                        </div>
                                        <div class="flex items-center gap-1 text-xs text-slate-500 font-mono mt-0.5">
                                            <span class="truncate">{{ $slotData['member_code'] }}</span>
                                            <button type="button" @click.stop="copyToClipboard('{{ $slotData['member_code'] }}', 'Member Code')" class="text-slate-400 hover:text-orange-600 p-0.5 cursor-pointer">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Quick Links -->
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    @if(!empty($slotData['phone']))
                                        <a href="tel:{{ $slotData['phone'] }}" 
                                           title="Call {{ $slotData['phone'] }}"
                                           class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white flex items-center justify-center transition-all shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>
                                        </a>
                                        @php $waNum = \App\Support\PhoneNumber::whatsapp($slotData['phone']); @endphp
                                        @if($waNum)
                                            <a href="https://wa.me/{{ $waNum }}" 
                                               target="_blank" 
                                               title="WhatsApp"
                                               class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white flex items-center justify-center transition-all shadow-2xs">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Badges: Rank, BV, Package, Direct Team -->
                            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 text-xs">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-[10px]">
                                        {{ $slotData['rank_name'] ?? 'Member' }}
                                    </span>
                                    <span class="font-black text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/80 text-[10px]">
                                        {{ $slotData['point_value'] ?? 100 }} BV
                                    </span>
                                    <span class="text-slate-400 text-[10px]">
                                        {{ $slotData['package_name'] ?? 'National' }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 font-bold text-[10px] border border-purple-200">
                                        Team: {{ $childDirectTotal }}/10
                                    </span>
                                </div>

                                <!-- Primary Action: Explore Team & Details -->
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('team.show', ['memberId' => $slotData['id'], 'owner_id' => $ownerId]) }}" 
                                       class="px-2.5 py-1 rounded-lg bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs transition-all flex items-center gap-1 shadow-2xs">
                                        <span>👥</span>
                                        <span data-en="Explore Team" data-bn="টিম দেখুন">Explore Team</span>
                                    </a>
                                    <button type="button" 
                                            @click="openDetailsModal({{ $slotData['id'] }})"
                                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs transition-colors cursor-pointer"
                                            title="View Details">
                                        👁️
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                @endfor
            </div>
        </div>

        <!-- ==================== RIGHT TEAM (5 SLOTS) ==================== -->
        <div class="space-y-3" :class="mobileBranchTab === 'RIGHT' ? 'block' : 'hidden lg:block'">
            <div class="flex items-center justify-between border-b-2 border-blue-500/50 pb-2 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-800 font-black text-xs flex items-center justify-center">R</span>
                    <h3 class="text-xs sm:text-sm font-black text-slate-900 uppercase tracking-tight" data-en="Right Team (5 Direct Slots)" data-bn="ডান টিম (৫টি ডিরেক্ট স্লট)">
                        Right Team (5 Direct Slots)
                    </h3>
                </div>
                <div class="flex items-center gap-2">
                    <span data-right-header-count class="text-xs font-bold text-blue-800 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">
                        {{ $rightFilledCount }}/5 Active
                    </span>
                    @if($firstVacantRight)
                        <button type="button" 
                                @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, 'RIGHT', {{ $firstVacantRight['slot_number'] }})"
                                class="text-xs font-bold text-blue-700 hover:text-blue-900 underline cursor-pointer"
                                data-en="+ Add Next" data-bn="+ পরবর্তী যুক্ত করুন">
                            + Add Next
                        </button>
                    @endif
                </div>
            </div>

            <div data-right-slots-container class="space-y-2.5">
                @for($slot = 1; $slot <= 5; $slot++)
                    @php 
                        $slotData = $rightSlots[$slot] ?? ['is_vacant' => true, 'slot_number' => $slot, 'branch' => 'RIGHT']; 
                        $isVacant = !empty($slotData['is_vacant']);
                    @endphp
                    
                    @if($isVacant)
                        <!-- Empty Slot Card -->
                        <div class="p-3.5 rounded-2xl border-2 border-dashed border-blue-300/80 bg-blue-50/20 hover:bg-blue-50/50 transition-all flex items-center justify-between gap-3 group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-800 font-black flex items-center justify-center text-xs border border-blue-200 flex-shrink-0">
                                    R{{ $slot }}
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-700">
                                        Slot R{{ $slot }} <span class="text-slate-400 font-normal" data-en="(Available)" data-bn="(খালি)">(Available)</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400" data-en="Click to place member" data-bn="মেম্বার প্লেস করতে ক্লিক করুন">
                                        Click to place member
                                    </div>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, 'RIGHT', {{ $slot }})"
                                    class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center gap-1 cursor-pointer">
                                <span>➕</span>
                                <span data-en="Place Member" data-bn="প্লেস করুন">Place Member</span>
                            </button>
                        </div>
                    @else
                        <!-- Occupied Member Card -->
                        @php
                            $childDirectL = $slotData['direct_left_count'] ?? 0;
                            $childDirectR = $slotData['direct_right_count'] ?? 0;
                            $childDirectTotal = $childDirectL + $childDirectR;
                        @endphp
                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200 hover:border-blue-400/80 shadow-xs hover:shadow-md transition-all space-y-2.5">
                            <div class="flex items-start justify-between gap-2.5">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-orange-400 font-black text-sm flex items-center justify-center shadow-xs flex-shrink-0 border border-slate-700">
                                        {{ substr($slotData['member_name'] ?? 'M', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="px-1.5 py-0.5 rounded-md bg-blue-100 text-blue-800 font-black text-[10px]">
                                                R{{ $slot }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                                                GEN 1
                                            </span>
                                            @if(!empty($slotData['is_target']))
                                                <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-1.5 py-0.5 rounded">🎯 Target</span>
                                            @endif
                                        </div>
                                        <div class="font-black text-slate-900 text-xs sm:text-sm mt-0.5 hover:text-orange-600 cursor-pointer truncate"
                                             @click="openDetailsModal({{ $slotData['id'] }})">
                                            {{ $slotData['member_name'] }}
                                        </div>
                                        <div class="flex items-center gap-1 text-xs text-slate-500 font-mono mt-0.5">
                                            <span class="truncate">{{ $slotData['member_code'] }}</span>
                                            <button type="button" @click.stop="copyToClipboard('{{ $slotData['member_code'] }}', 'Member Code')" class="text-slate-400 hover:text-orange-600 p-0.5 cursor-pointer">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Quick Links -->
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    @if(!empty($slotData['phone']))
                                        <a href="tel:{{ $slotData['phone'] }}" 
                                           title="Call {{ $slotData['phone'] }}"
                                           class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white flex items-center justify-center transition-all shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>
                                        </a>
                                        @php $waNum = \App\Support\PhoneNumber::whatsapp($slotData['phone']); @endphp
                                        @if($waNum)
                                            <a href="https://wa.me/{{ $waNum }}" 
                                               target="_blank" 
                                               title="WhatsApp"
                                               class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white flex items-center justify-center transition-all shadow-2xs">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Badges: Rank, BV, Package, Direct Team -->
                            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 text-xs">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-[10px]">
                                        {{ $slotData['rank_name'] ?? 'Member' }}
                                    </span>
                                    <span class="font-black text-blue-800 bg-blue-50 px-2 py-0.5 rounded border border-blue-200/80 text-[10px]">
                                        {{ $slotData['point_value'] ?? 100 }} BV
                                    </span>
                                    <span class="text-slate-400 text-[10px]">
                                        {{ $slotData['package_name'] ?? 'National' }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 font-bold text-[10px] border border-purple-200">
                                        Team: {{ $childDirectTotal }}/10
                                    </span>
                                </div>

                                <!-- Primary Action: Explore Team & Details -->
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('team.show', ['memberId' => $slotData['id'], 'owner_id' => $ownerId]) }}" 
                                       class="px-2.5 py-1 rounded-lg bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs transition-all flex items-center gap-1 shadow-2xs">
                                        <span>👥</span>
                                        <span data-en="Explore Team" data-bn="টিম দেখুন">Explore Team</span>
                                    </a>
                                    <button type="button" 
                                            @click="openDetailsModal({{ $slotData['id'] }})"
                                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs transition-colors cursor-pointer"
                                            title="View Details">
                                        👁️
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                @endfor
            </div>
        </div>

    </div>

    <!-- ==================== 7. MOBILE STICKY BOTTOM ACTION BAR (<= 767px) ==================== -->
    <div x-show="!detailsModalOpen && !placementModalOpen && !editModalOpen" 
         class="fixed bottom-0 left-0 right-0 z-40 p-3 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-xl flex items-center gap-2 lg:hidden">
        <button type="button" 
                @click="openAddMemberModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }})"
                class="flex-1 py-2.5 px-3 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs font-black shadow-sm active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
            <span>➕</span>
            <span data-en="Add Member" data-bn="মেম্বার যুক্ত করুন">Add Member</span>
        </button>

        @if($autoBalanceSlot)
            <button type="button" 
                    @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, '{{ $autoBalanceSlot['branch'] }}', {{ $autoBalanceSlot['slot_number'] }})"
                    class="flex-1 py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-black shadow-sm active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <span>🎯</span>
                <span data-en="Auto Balance" data-bn="অটো ব্যালান্স">Auto Balance</span>
            </button>
        @endif
    </div>

</div>
