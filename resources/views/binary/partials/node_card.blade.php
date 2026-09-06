@if($node['is_vacant'])
    @php
        $genLabel = $node['generation_label'] ?? ('GEN ' . (($depth ?? 2) - 1));
    @endphp
    <!-- VACANT SLOT CARD (+ ADD MEMBER) -->
    <div class="w-60 md:w-64 p-4 rounded-xl border-2 border-dashed border-white/30 bg-white/5 hover:bg-white/10 transition-all flex flex-col items-center justify-center text-center space-y-2.5 text-white shadow-md group relative">
        <div class="flex items-center justify-between w-full px-1">
            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider bg-white/10 text-slate-300 border border-white/15">
                {{ $genLabel }}
            </span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/15 text-slate-100 border border-white/20">
                {{ $node['position'] === 'left' ? '👈 Left Slot' : '👉 Right Slot' }}
            </span>
        </div>

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
        $isContributed = (float)($node['total_investment'] ?? $node['point_value']) > 0;
        $cardBg = $isContributed ? 'bg-[#367e6c] border-[#6ea99b]' : 'bg-[#c89e4c] border-[#ead599]';
        $subTextColor = $isContributed ? 'text-emerald-100/90' : 'text-amber-100/90';
        $dividerColor = 'bg-[#1e3243]';
        $code = $node['member_code'] ?: ('SBL-' . $node['id']);
        $username = $node['username'] ?? (str_starts_with($code, '@') ? $code : ('@' . strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $code))));
        $sponsorName = $node['sponsor_name'] ?? 'Md. Samim';
        $userEmail = $node['email'] ?: 'member' . $node['id'] . '@sbl.test';
        $phone = $node['phone'] ?: '01700000000';
        $password = $node['password_plain'] ?? 'sbl123456';
        $tpin = $node['tpin'] ?? '1234';
        $genLabel = $node['generation_label'] ?? ((($depth ?? 1) === 1) ? 'ROOT' : ('GEN ' . (($depth ?? 1) - 1)));
        $isRootNode = ($genLabel === 'ROOT');
    @endphp

    <!-- OCCUPIED MEMBER NODE CARD -->
    <div data-node-id="{{ $node['id'] }}" 
         x-data="{ showPass: false }"
         class="w-60 md:w-64 rounded-xl border {{ $cardBg }} shadow-lg p-3.5 flex flex-col justify-between text-center text-white select-none relative group transition-all duration-200 hover:shadow-2xl hover:scale-[1.015]">
        
        <!-- Top Row: Generation Badge & Floating Action Overlay -->
        <div class="flex items-center justify-between mb-1">
            <!-- Generation Level Badge -->
            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider {{ $isRootNode ? 'bg-amber-400/90 text-slate-950 border border-amber-300' : 'bg-black/30 text-white border border-white/20' }}">
                {{ $genLabel }}
            </span>

            <!-- Position Badge if child -->
            @if(!empty($node['position']))
            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-black/20 text-white/80">
                {{ $node['position'] === 'left' ? '👈 Left' : '👉 Right' }}
            </span>
            @endif

            <!-- Floating Action Overlay (View Tree, Edit, Delete) -->
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-20">
                <a href="{{ route('binary.index', ['node_id' => $node['id']]) }}" 
                   class="p-1 rounded-md bg-black/70 hover:bg-black/95 text-white text-xs backdrop-blur-xs transition-colors shadow-xs" 
                   title="এই মেম্বার থেকে নতুন ট্রি দেখুন (View Tree)">
                    🌲
                </a>
                <button type="button" 
                        @click.stop="openEditModal({{ json_encode($node) }})"
                        class="p-1 rounded-md bg-black/70 hover:bg-black/95 text-white text-xs backdrop-blur-xs transition-colors shadow-xs" 
                        title="মেম্বার এডিট করুন">
                    ✏️
                </button>
                @if(! $node['has_children'])
                <form action="{{ route('binary.destroy', $node['id']) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই মেম্বারকে ({{ $node['member_name'] }}) রিমুভ করতে চান?');" class="inline" @click.stop>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1 rounded-md bg-rose-600/90 hover:bg-rose-700 text-white text-xs backdrop-blur-xs transition-colors shadow-xs" title="মেম্বার মুছুন">
                        🗑️
                    </button>
                </form>
                @else
                <form action="{{ route('binary.destroy', $node['id']) }}" method="POST" onsubmit="return confirm('এই মেম্বারের ({{ $node['member_name'] }}) ডাউনলাইনে টিম মেম্বার রয়েছে। আপনি কি এই মেম্বারসহ তার পুরো ডাউনলাইন মুছে ফেলতে চান?');" class="inline" @click.stop>
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="cascade" value="1">
                    <button type="submit" class="p-1 rounded-md bg-rose-600/90 hover:bg-rose-700 text-white text-xs backdrop-blur-xs transition-colors shadow-xs" title="মেম্বার ও ডাউনলাইন মুছুন">
                        🗑️
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Main Info Box (Clickable to Edit) -->
        <div class="space-y-0.5 cursor-pointer text-center" @click="openEditModal({{ json_encode($node) }})">
            
            <!-- 1. Full Name -->
            <h3 class="font-bold text-base md:text-lg text-white leading-tight tracking-tight drop-shadow-xs flex items-center justify-center gap-1">
                <span>{{ $node['member_name'] }}</span>
            </h3>

            <!-- 2. Username / Member ID (with copy button) -->
            <div class="text-xs {{ $subTextColor }} font-medium font-mono flex items-center justify-center gap-1"
                 @click.stop="navigator.clipboard.writeText('{{ $username }}'); alert('Username copied: {{ $username }}');"
                 title="Click to copy username">
                <span>{{ $username }}</span>
                <svg class="w-3.5 h-3.5 inline-block opacity-75 hover:opacity-100 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            </div>

            <!-- 3. Mobile Number (Call & Copy) -->
            <div class="text-[11px] {{ $subTextColor }} font-medium flex items-center justify-center gap-1.5 pt-0.5">
                <a href="tel:{{ $phone }}" @click.stop class="hover:text-white transition-colors" title="Call Member">
                    📞 <span>{{ $phone }}</span>
                </a>
                <button type="button" @click.stop="navigator.clipboard.writeText('{{ $phone }}'); alert('Mobile number copied: {{ $phone }}');" title="Copy Mobile">
                    <svg class="w-3 h-3 opacity-75 hover:opacity-100 cursor-pointer inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                </button>
            </div>

            <!-- 4. Rank & Sponsor Row -->
            <div class="text-xs {{ $subTextColor }} flex items-center justify-center gap-1.5 pt-0.5">
                <span>Rank:</span>
                <span class="font-extrabold px-1.5 py-0.2 rounded {{ !empty($node['is_fme']) ? 'bg-amber-400 text-slate-950 shadow-xs' : 'bg-black/20 text-white' }}">
                    {{ $node['rank_name'] ?: 'Member' }}
                </span>
            </div>

            <!-- 5. Sponsor Name -->
            <div class="text-[11px] {{ $subTextColor }} font-medium pt-0.5">
                <span class="opacity-75">Sponsor:</span> <span class="font-semibold text-white/95">{{ $sponsorName }}</span>
            </div>

            <!-- 6. Password & TPIN Row (toggle & copy) -->
            <div class="text-[10px] {{ $subTextColor }} flex items-center justify-center gap-2 py-0.5 bg-black/15 rounded-md px-1 mt-0.5">
                <!-- Password -->
                <div class="flex items-center gap-1">
                    <span class="opacity-80">Pass:</span>
                    <span class="font-mono font-bold" x-text="showPass ? '{{ $password }}' : '••••••'">••••••</span>
                    <button type="button" @click.stop="showPass = !showPass" class="opacity-80 hover:opacity-100" :title="showPass ? 'Hide Password' : 'Show Password'">
                        <span x-text="showPass ? '🙈' : '👁️'">👁️</span>
                    </button>
                    <button type="button" @click.stop="navigator.clipboard.writeText('{{ $password }}'); alert('Password copied: {{ $password }}');" title="Copy Password">
                        <svg class="w-3 h-3 opacity-75 hover:opacity-100 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </button>
                </div>

                <span class="opacity-40">|</span>

                <!-- TPIN -->
                <div class="flex items-center gap-1">
                    <span class="opacity-80">TPIN:</span>
                    <span class="font-mono font-bold">{{ $tpin }}</span>
                    <button type="button" @click.stop="navigator.clipboard.writeText('{{ $tpin }}'); alert('TPIN copied: {{ $tpin }}');" title="Copy TPIN">
                        <svg class="w-3 h-3 opacity-75 hover:opacity-100 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- 7. Binary Legs Stats (L vs R) with vertical divider -->
        <div class="mt-2 pt-2 border-t border-white/20 grid grid-cols-2 text-xs relative font-medium">
            <!-- Vertical divider line -->
            <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-[2px] {{ $dividerColor }} rounded-full"></div>

            <!-- Left Leg -->
            <div class="pr-2 space-y-0.5 text-center">
                <div class="font-bold flex items-center justify-center gap-1">
                    <span>👈 L Team</span>
                    <button type="button" 
                            @click.stop="navigator.clipboard.writeText(window.location.origin + '/binary?ref={{ $node['id'] }}&pos=left'); alert('Left placement link copied!');" 
                            class="hover:text-amber-200 transition-colors"
                            title="Copy Left Placement Link">
                        <svg class="w-3.5 h-3.5 opacity-80 hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </button>
                </div>
                <div class="text-[11px] text-white/95 font-bold leading-tight">Team: {{ $node['left_count'] }}</div>
                <div class="text-[11px] text-white/90 font-medium leading-tight">Vol: <span x-text="$store.currency ? $store.currency.format({{ (float)($node['left_investment_volume'] ?? $node['left_bv']) }}) : '{{ \App\Services\CurrencyService::format((float)($node['left_investment_volume'] ?? $node['left_bv'])) }}'">{{ \App\Services\CurrencyService::format((float)($node['left_investment_volume'] ?? $node['left_bv'])) }}</span></div>
            </div>

            <!-- Right Leg -->
            <div class="pl-2 space-y-0.5 text-center">
                <div class="font-bold flex items-center justify-center gap-1">
                    <span>👉 R Team</span>
                    <button type="button" 
                            @click.stop="navigator.clipboard.writeText(window.location.origin + '/binary?ref={{ $node['id'] }}&pos=right'); alert('Right placement link copied!');" 
                            class="hover:text-amber-200 transition-colors"
                            title="Copy Right Placement Link">
                        <svg class="w-3.5 h-3.5 opacity-80 hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </button>
                </div>
                <div class="text-[11px] text-white/95 font-bold leading-tight">Team: {{ $node['right_count'] }}</div>
                <div class="text-[11px] text-white/90 font-medium leading-tight">Vol: <span x-text="$store.currency ? $store.currency.format({{ (float)($node['right_investment_volume'] ?? $node['right_bv']) }}) : '{{ \App\Services\CurrencyService::format((float)($node['right_investment_volume'] ?? $node['right_bv'])) }}'">{{ \App\Services\CurrencyService::format((float)($node['right_investment_volume'] ?? $node['right_bv'])) }}</span></div>
            </div>
        </div>

        <!-- 8. Own Investment Row -->
        <div class="mt-2 pt-1.5 border-t border-white/20 text-xs font-semibold text-white/95 flex items-center justify-between px-1">
            <span>Own Inv: <strong class="text-amber-200" x-text="$store.currency ? $store.currency.format({{ (float)($node['own_investment'] ?? $node['total_investment'] ?? $node['point_value']) }}) : '{{ \App\Services\CurrencyService::format((float)($node['own_investment'] ?? $node['total_investment'] ?? $node['point_value'])) }}'">{{ \App\Services\CurrencyService::format((float)($node['own_investment'] ?? $node['total_investment'] ?? $node['point_value'])) }}</strong></span>
            
            <a href="{{ route('binary.index', ['node_id' => $node['id']]) }}" 
               class="px-2 py-0.5 rounded-md bg-black/40 hover:bg-black/60 text-[10px] font-bold text-amber-200 flex items-center gap-1 transition-all"
               title="View this member's branch tree">
                <span>View Tree</span> <span>➔</span>
            </a>
        </div>
    </div>
@endif
