@if($node['is_vacant'])
    <!-- VACANT SLOT CARD (+ ADD MEMBER) -->
    <div class="w-56 md:w-60 p-4 rounded-xl border-2 border-dashed border-white/30 bg-white/5 hover:bg-white/10 transition-all flex flex-col items-center justify-center text-center space-y-2.5 text-white shadow-md group">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/15 text-slate-100 border border-white/20">
            {{ $node['position'] === 'left' ? '👈 Left Slot' : '👉 Right Slot' }}
        </span>

        <div class="w-10 h-10 rounded-full bg-white/15 text-white flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform">
            +
        </div>

        <div class="text-xs font-bold text-slate-200">
            খালি পজিশন
        </div>

        <button type="button" 
                @click="openPlacementModal({{ $node['parent_id'] }}, '{{ addslashes($node['parent_name']) }}', '{{ $node['parent_code'] }}', '{{ $node['position'] }}')"
                class="w-full py-2 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md transition-all active:scale-95">
            + মেম্বার যোগ করুন
        </button>
    </div>

@else
    @php
        $isContributed = (float)$node['point_value'] > 0;
        $cardBg = $isContributed ? 'bg-[#367e6c] border-[#6ea99b]' : 'bg-[#c89e4c] border-[#ead599]';
        $subTextColor = $isContributed ? 'text-emerald-100/90' : 'text-amber-100/90';
        $dividerColor = 'bg-[#1e3243]';
        $code = $node['member_code'] ?: ('SBL-' . $node['id']);
        $username = $node['username'] ?? (str_starts_with($code, '@') ? $code : ('@' . strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $code))));
        $sponsorName = $node['sponsor_name'] ?? 'Md Abdul Hai';
        $userEmail = $node['email'] ?: 'member' . $node['id'] . '@gmail.com';
    @endphp

    <!-- OCCUPIED MEMBER NODE CARD -->
    <div data-node-id="{{ $node['id'] }}" 
         class="w-56 md:w-60 rounded-xl border {{ $cardBg }} shadow-lg p-3.5 flex flex-col justify-between text-center text-white select-none relative group transition-all duration-200 hover:shadow-2xl hover:scale-[1.015]">
        
        <!-- Floating Action Overlay (Edit, Drill-down, Delete) -->
        <div class="absolute top-2 right-2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-20">
            <button type="button" 
                    @click.stop="openEditModal({{ json_encode($node) }})"
                    class="p-1 rounded-md bg-black/50 hover:bg-black/80 text-white text-xs backdrop-blur-xs transition-colors shadow-xs" 
                    title="মেম্বার এডিট করুন">
                ✏️
            </button>
            <a href="{{ route('binary.index', ['node_id' => $node['id']]) }}" 
               class="p-1 rounded-md bg-black/50 hover:bg-black/80 text-white text-xs backdrop-blur-xs transition-colors shadow-xs" 
               title="ডাউনলাইন ট্রি দেখুন">
                ⬇️
            </a>
            @if(! $node['has_children'])
            <form action="{{ route('binary.destroy', $node['id']) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই মেম্বারকে ({{ $node['member_name'] }}) রিমুভ করতে চান?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-1 rounded-md bg-rose-600/80 hover:bg-rose-700 text-white text-xs backdrop-blur-xs transition-colors shadow-xs" title="মেম্বার মুছুন">
                    🗑️
                </button>
            </form>
            @endif
        </div>

        <!-- Main Info Box (Clickable to Edit) -->
        <div class="space-y-0.5 cursor-pointer text-center" @click="openEditModal({{ json_encode($node) }})">
            <!-- Full Name -->
            <h3 class="font-bold text-base md:text-lg text-white leading-tight tracking-tight drop-shadow-xs">
                {{ $node['member_name'] }}
            </h3>

            <!-- Username -->
            <div class="text-xs {{ $subTextColor }} font-medium font-mono">
                {{ $username }}
            </div>

            <!-- Rank -->
            <div class="text-xs {{ $subTextColor }} font-normal">
                Rank: {{ $node['rank_name'] ?: 'NA' }}
            </div>

            <!-- Email with Copy Icon -->
            <div class="text-[11px] {{ $subTextColor }} flex items-center justify-center gap-1 hover:text-white transition-colors"
                 @click.stop="navigator.clipboard.writeText('{{ $userEmail }}'); alert('Email copied: {{ $userEmail }}');"
                 title="Click to copy email">
                <span>({{ $userEmail }}</span>
                <svg class="w-3.5 h-3.5 inline-block opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                <span>)</span>
            </div>

            <!-- Sponsor / By -->
            <div class="text-xs {{ $subTextColor }} font-medium pt-0.5">
                By {{ $sponsorName }}
            </div>
        </div>

        <!-- Binary Legs Stats (L vs R) with dark vertical divider -->
        <div class="mt-2.5 pt-2 border-t border-white/20 grid grid-cols-2 text-xs relative font-medium">
            <!-- Dark vertical divider line -->
            <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-[2px] {{ $dividerColor }} rounded-full"></div>

            <!-- Left Leg -->
            <div class="pr-2 space-y-0.5 text-center">
                <div class="font-bold flex items-center justify-center gap-1">
                    <span>L</span>
                    <button type="button" 
                            @click.stop="navigator.clipboard.writeText(window.location.origin + '/binary?ref={{ $node['id'] }}&pos=left'); alert('Left placement link copied!');" 
                            class="hover:text-amber-200 transition-colors"
                            title="Copy Left Placement Link">
                        <svg class="w-3.5 h-3.5 opacity-80 hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </button>
                </div>
                <div class="text-[11px] text-white/95 font-semibold leading-tight">Team- {{ $node['left_count'] }}/{{ $node['left_count'] }}</div>
                <div class="text-[11px] text-white/95 font-semibold leading-tight">Vol- {{ (int)$node['left_bv'] }}$</div>
            </div>

            <!-- Right Leg -->
            <div class="pl-2 space-y-0.5 text-center">
                <div class="font-bold flex items-center justify-center gap-1">
                    <span>R</span>
                    <button type="button" 
                            @click.stop="navigator.clipboard.writeText(window.location.origin + '/binary?ref={{ $node['id'] }}&pos=right'); alert('Right placement link copied!');" 
                            class="hover:text-amber-200 transition-colors"
                            title="Copy Right Placement Link">
                        <svg class="w-3.5 h-3.5 opacity-80 hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </button>
                </div>
                <div class="text-[11px] text-white/95 font-semibold leading-tight">Team- {{ $node['right_count'] }}/{{ $node['right_count'] }}</div>
                <div class="text-[11px] text-white/95 font-semibold leading-tight">Vol- {{ (int)$node['right_bv'] }}$</div>
            </div>
        </div>

        <!-- Bottom Total Contribution Row -->
        <div class="mt-2 pt-1.5 border-t border-white/20 text-xs font-semibold text-white/95">
            Total Contribution: {{ (int)$node['point_value'] }}$
        </div>
    </div>
@endif
