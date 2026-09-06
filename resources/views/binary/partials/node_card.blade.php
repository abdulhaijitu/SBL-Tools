@if($node['is_vacant'])
    @php
        $branch = strtoupper($node['branch'] ?? ($node['position'] === 'left' ? 'LEFT' : 'RIGHT'));
        $slotNumber = $node['slot_number'] ?? 1;
        $slotLabel = "{$branch}-{$slotNumber}";
        $isLeft = ($branch === 'LEFT');
    @endphp
    <!-- VACANT SLOT CARD (+ ADD MEMBER) -->
    <div class="w-full p-4 rounded-2xl border-2 border-dashed {{ $isLeft ? 'border-emerald-500/30 bg-emerald-950/20 hover:bg-emerald-900/30' : 'border-blue-500/30 bg-blue-950/20 hover:bg-blue-900/30' }} transition-all flex flex-col justify-between space-y-3 text-white shadow-md group relative">
        <div class="flex items-center justify-between">
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider {{ $isLeft ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-blue-500/20 text-blue-300 border border-blue-500/40' }}">
                {{ $slotLabel }}
            </span>
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">
                খালি পজিশন
            </span>
        </div>

        <div class="py-2 text-center">
            <div class="w-10 h-10 mx-auto rounded-full {{ $isLeft ? 'bg-emerald-500/20 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-slate-950' : 'bg-blue-500/20 text-blue-400 group-hover:bg-blue-500 group-hover:text-slate-950' }} flex items-center justify-center text-xl font-black transition-all">
                +
            </div>
            <div class="text-xs font-bold text-slate-300 mt-1">
                {{ $slotLabel }} স্লট খালি রয়েছে
            </div>
        </div>

        <button type="button" 
                @click="openPlacementModal({{ $node['parent_id'] }}, '{{ addslashes($node['parent_name'] ?? '') }}', '{{ $node['parent_code'] ?? '' }}', '{{ $branch }}', {{ $slotNumber }})"
                class="w-full py-2 px-3 rounded-xl {{ $isLeft ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-blue-600 hover:bg-blue-500' }} text-white text-xs font-bold shadow-md transition-all active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
            <span>+</span> <span>স্লটে মেম্বার যোগ করুন</span>
        </button>
    </div>

@else
    @php
        $isTarget = !empty($node['is_target']);
        $isContributed = (float)($node['total_investment'] ?? $node['point_value']) > 0;
        
        $branch = strtoupper($node['branch'] ?? ($node['position'] === 'left' ? 'LEFT' : 'RIGHT'));
        $slotNumber = $node['slot_number'] ?? 1;
        $slotLabel = "{$branch}-{$slotNumber}";
        $isLeft = ($branch === 'LEFT');

        if ($isTarget) {
            $cardBorder = 'border-purple-500/40 bg-gradient-to-b from-purple-950/70 via-slate-900/90 to-purple-950/60';
            $slotBadge = 'bg-purple-500/30 text-purple-300 border-purple-400/40';
        } elseif ($isLeft) {
            $cardBorder = 'border-emerald-500/35 bg-gradient-to-b from-emerald-950/50 via-slate-900/95 to-slate-950';
            $slotBadge = 'bg-emerald-500/25 text-emerald-300 border-emerald-500/40';
        } else {
            $cardBorder = 'border-blue-500/35 bg-gradient-to-b from-blue-950/50 via-slate-900/95 to-slate-950';
            $slotBadge = 'bg-blue-500/25 text-blue-300 border-blue-500/40';
        }

        $code = $node['member_code'] ?: ('SBL-' . $node['id']);
        $username = $node['username'] ?? (str_starts_with($code, '@') ? $code : ('@' . strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $code))));
        $sponsorName = $node['sponsor_name'] ?? 'Md. Samim';
        $directL = (int)($node['direct_left_count'] ?? 0);
        $directR = (int)($node['direct_right_count'] ?? 0);
        $directTotal = $directL + $directR;
        $ownInv = (float)($node['own_investment'] ?? $node['total_investment'] ?? $node['point_value'] ?? 0);
    @endphp

    <!-- COMPACT OCCUPIED DIRECT MEMBER CARD -->
    <div data-node-id="{{ $node['id'] }}" 
         class="w-full rounded-2xl border {{ $cardBorder }} shadow-xl p-4 flex flex-col justify-between space-y-3.5 text-white select-none transition-all duration-200 hover:shadow-2xl hover:border-orange-500/50">
        
        <!-- Top Row: Slot Badge & Rank / Target -->
        <div class="flex items-center justify-between gap-1">
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider {{ $slotBadge }} border">
                {{ $slotLabel }}
            </span>

            <div class="flex items-center gap-1.5">
                @if($isTarget)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-500/30 text-purple-200 border border-purple-400/40">
                    🎯 Target
                </span>
                @endif

                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ !empty($node['is_fme']) ? 'bg-amber-400 text-slate-950 font-black shadow-xs' : 'bg-slate-800 text-slate-200 border border-slate-700' }}">
                    {{ $node['rank_name'] ?: 'Member' }}
                </span>
            </div>
        </div>

        <!-- Member Info -->
        <div class="space-y-1">
            <h4 class="font-black text-base text-white leading-tight tracking-tight hover:text-orange-400 transition-colors cursor-pointer"
                @click="openDetailsModal({{ json_encode($node) }})">
                {{ $node['member_name'] }}
            </h4>
            <div class="text-xs text-slate-400 font-mono flex items-center gap-1.5">
                <span>{{ $username }}</span>
                @if(!empty($node['phone']))
                <span class="text-slate-600">•</span>
                <span class="text-slate-300 font-sans">{{ $node['phone'] }}</span>
                @endif
            </div>
        </div>

        <!-- Metrics Grid: Own Investment & Direct Team -->
        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-800/80 text-xs">
            <div class="p-2 rounded-xl bg-slate-950/70 border border-slate-800 space-y-0.5">
                <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Own Inv</div>
                <div class="font-black text-amber-300 truncate">
                    <span x-text="$store.currency ? $store.currency.format({{ $ownInv }}) : '{{ \App\Services\CurrencyService::format($ownInv) }}'">{{ \App\Services\CurrencyService::format($ownInv) }}</span>
                </div>
            </div>

            <div class="p-2 rounded-xl bg-slate-950/70 border border-slate-800 space-y-0.5">
                <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Direct Team</div>
                <div class="font-black text-white flex items-center justify-between">
                    <span>{{ $directTotal }}/10</span>
                    <span class="text-[10px] text-slate-400 font-normal">({{ $directL }}L | {{ $directR }}R)</span>
                </div>
            </div>
        </div>

        <!-- Actions: View Team & View Details -->
        <div class="pt-1 flex items-center gap-2">
            <!-- 👥 View Team (Drills into that member's 10-slot tree) -->
            <a href="{{ route('team.show', ['memberId' => $node['id']]) }}" 
               class="flex-1 py-2 px-3 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-black text-xs shadow-md transition-all active:scale-95 flex items-center justify-center gap-1.5"
               title="এই মেম্বারের ১০-স্লট টিম এক্সপ্লোর করুন">
                <span>👥</span> <span>View Team</span>
            </a>

            <!-- ℹ️ Details Modal Trigger -->
            <button type="button" 
                    @click="openDetailsModal({{ json_encode($node) }})"
                    class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-bold text-xs border border-slate-700 transition-all active:scale-95 flex items-center justify-center gap-1 cursor-pointer"
                    title="মেম্বারের সম্পূর্ণ বিবরণ ও ইনভেস্টমেন্ট হিস্টোরি দেখুন">
                <span>ℹ️</span> <span>Details</span>
            </button>
        </div>
    </div>
@endif
