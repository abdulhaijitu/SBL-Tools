@if($node['is_vacant'])
    @php
        $branch = strtoupper($node['branch'] ?? ($node['position'] === 'left' ? 'LEFT' : 'RIGHT'));
        $slotNumber = $node['slot_number'] ?? 1;
        $slotLabel = "{$branch}-{$slotNumber}";
        $genLabel = $node['generation_label'] ?? ('GEN ' . (($depth ?? 2) - 1));
        $isLeft = ($branch === 'LEFT');
    @endphp
    <!-- VACANT SLOT CARD (+ ADD MEMBER) -->
    <div class="w-56 md:w-60 p-3.5 rounded-2xl border-2 border-dashed {{ $isLeft ? 'border-emerald-500/40 bg-emerald-950/20 hover:bg-emerald-900/30' : 'border-blue-500/40 bg-blue-950/20 hover:bg-blue-900/30' }} transition-all flex flex-col items-center justify-center text-center space-y-2 text-white shadow-md group relative">
        <div class="flex items-center justify-between w-full px-1">
            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider bg-white/10 text-slate-300 border border-white/15">
                {{ $genLabel }}
            </span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ $isLeft ? 'bg-emerald-500/30 text-emerald-300 border border-emerald-500/50' : 'bg-blue-500/30 text-blue-300 border border-blue-500/50' }}">
                {{ $slotLabel }}
            </span>
        </div>

        <div class="w-9 h-9 rounded-full {{ $isLeft ? 'bg-emerald-500/20 text-emerald-300 group-hover:bg-emerald-500 group-hover:text-white' : 'bg-blue-500/20 text-blue-300 group-hover:bg-blue-500 group-hover:text-white' }} flex items-center justify-center text-lg font-black transition-all">
            +
        </div>

        <div class="text-xs font-bold text-slate-200">
            খালি স্লট (Vacant)
        </div>

        <button type="button" 
                @click="openPlacementModal({{ $node['parent_id'] }}, '{{ addslashes($node['parent_name'] ?? '') }}', '{{ $node['parent_code'] ?? '' }}', '{{ $branch }}', {{ $slotNumber }})"
                class="w-full py-1.5 px-3 rounded-xl {{ $isLeft ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-blue-600 hover:bg-blue-500' }} text-white text-xs font-bold shadow-md transition-all active:scale-95 flex items-center justify-center gap-1">
            <span>+</span> <span>স্লটে মেম্বার যোগ করুন</span>
        </button>
    </div>

@else
    @php
        $isTarget = !empty($node['is_target']);
        $isContributed = (float)($node['total_investment'] ?? $node['point_value']) > 0;
        
        // Card Background Color
        if ($isTarget) {
            $cardBg = 'bg-gradient-to-b from-purple-900/90 to-indigo-950/95 border-purple-400/60 shadow-purple-950/50';
            $subTextColor = 'text-purple-200';
            $badgeColor = 'bg-purple-500/40 text-purple-200 border-purple-400/50';
        } elseif ($isContributed) {
            $cardBg = 'bg-gradient-to-b from-[#2d6f5f] to-[#1e4d41] border-[#5ea694] shadow-emerald-950/40';
            $subTextColor = 'text-emerald-100';
            $badgeColor = 'bg-emerald-500/30 text-emerald-100 border-emerald-400/40';
        } else {
            $cardBg = 'bg-gradient-to-b from-[#ab8438] to-[#73561f] border-[#e2be74] shadow-amber-950/40';
            $subTextColor = 'text-amber-100';
            $badgeColor = 'bg-amber-500/30 text-amber-100 border-amber-400/40';
        }

        $code = $node['member_code'] ?: ('SBL-' . $node['id']);
        $username = $node['username'] ?? (str_starts_with($code, '@') ? $code : ('@' . strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $code))));
        $sponsorName = $node['sponsor_name'] ?? 'Md. Samim';
        $userEmail = $node['email'] ?: 'member' . $node['id'] . '@sbl.test';
        $phone = $node['phone'] ?: '01700000000';
        $password = $node['password_plain'] ?? 'sbl123456';
        $tpin = $node['tpin'] ?? '1234';
        
        $genLabel = $node['generation_label'] ?? ((($depth ?? 1) === 1) ? 'ROOT' : ('GEN ' . (($depth ?? 1) - 1)));
        $isRootNode = ($genLabel === 'ROOT');
        $slotLabel = $node['slot_label'] ?? ($isRootNode ? 'ROOT' : (strtoupper($node['branch'] ?? '') . '-' . ($node['slot_number'] ?? 1)));
        
        $directL = (int)($node['direct_left_count'] ?? 0);
        $directR = (int)($node['direct_right_count'] ?? 0);
        $totalL = (int)($node['total_left_network'] ?? 0);
        $totalR = (int)($node['total_right_network'] ?? 0);
    @endphp

    <!-- OCCUPIED MEMBER NODE CARD -->
    <div data-node-id="{{ $node['id'] }}" 
         x-data="{ showPass: false }"
         class="w-56 md:w-60 rounded-2xl border {{ $cardBg }} shadow-xl p-3 flex flex-col justify-between text-center text-white select-none relative group transition-all duration-200 hover:shadow-2xl hover:scale-[1.02]">
        
        <!-- Top Row: Generation Badge & Slot Badge & Floating Overlay -->
        <div class="flex items-center justify-between gap-1 mb-1.5">
            <!-- Generation Level Badge -->
            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider {{ $isRootNode ? 'bg-amber-400 text-slate-950 font-black border border-amber-300' : 'bg-black/40 text-white border border-white/20' }}">
                {{ $genLabel }}
            </span>

            <!-- Slot Badge -->
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $badgeColor }} border">
                {{ $slotLabel }}
            </span>

            <!-- Target Badge if target -->
            @if($isTarget)
            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-extrabold bg-purple-400 text-slate-950 shadow-xs" title="পরিকল্পিত টার্গেট মেম্বার">
                🎯 Target
            </span>
            @endif

            <!-- Floating Action Overlay (View Tree, Edit, Delete) -->
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-30">
                <a href="{{ route('binary.index', ['node_id' => $node['id']]) }}" 
                   class="p-1 rounded-md bg-black/80 hover:bg-black text-white text-xs backdrop-blur-xs transition-colors shadow-xs" 
                   title="এই মেম্বারের ১০-স্লট টিম ট্রি দেখুন (View Team Tree)">
                    🌲
                </a>
                <button type="button" 
                        @click.stop="openEditModal({{ json_encode($node) }})"
                        class="p-1 rounded-md bg-black/80 hover:bg-black text-white text-xs backdrop-blur-xs transition-colors shadow-xs" 
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
            <h3 class="font-bold text-sm md:text-base text-white leading-tight tracking-tight drop-shadow-xs flex items-center justify-center gap-1">
                <span>{{ $node['member_name'] }}</span>
            </h3>

            <!-- 2. Username / Member ID -->
            <div class="text-[11px] {{ $subTextColor }} font-medium font-mono flex items-center justify-center gap-1"
                 @click.stop="navigator.clipboard.writeText('{{ $username }}'); alert('Username copied: {{ $username }}');"
                 title="Click to copy username">
                <span>{{ $username }}</span>
                <svg class="w-3 h-3 inline-block opacity-75 hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            </div>

            <!-- 3. Mobile Number -->
            <div class="text-[10px] {{ $subTextColor }} font-medium flex items-center justify-center gap-1 pt-0.5">
                <a href="tel:{{ $phone }}" @click.stop class="hover:text-white transition-colors">
                    📞 <span>{{ $phone }}</span>
                </a>
            </div>

            <!-- 4. Rank & Sponsor Row -->
            <div class="text-[11px] {{ $subTextColor }} flex items-center justify-center gap-1.5 pt-0.5">
                <span>Rank:</span>
                <span class="font-black px-1.5 py-0.2 rounded {{ !empty($node['is_fme']) ? 'bg-amber-400 text-slate-950 shadow-xs' : 'bg-black/30 text-white' }}">
                    {{ $node['rank_name'] ?: 'Member' }}
                </span>
            </div>

            <!-- 5. Sponsor Name -->
            <div class="text-[10px] {{ $subTextColor }} font-medium">
                <span class="opacity-75">Sponsor:</span> <span class="font-bold text-white/95">{{ $sponsorName }}</span>
            </div>

            <!-- 6. SBL Ecosystem Portal Login Password & TPIN Row -->
            <div class="text-[9px] {{ $subTextColor }} flex items-center justify-center gap-1.5 py-0.5 bg-black/20 rounded-md px-1 mt-0.5" title="SBL Ecosystem Login Credentials">
                <div class="flex items-center gap-0.5">
                    <span class="opacity-80 font-semibold" title="SBL Ecosystem পোর্টাল লগইন পাসওয়ার্ড (অ্যাপ পাসওয়ার্ড থেকে আলাদা)">SBL Pass:</span>
                    <span class="font-mono font-bold" x-text="showPass ? '{{ $password }}' : '••••••'">••••••</span>
                    <button type="button" @click.stop="showPass = !showPass" class="opacity-80 hover:opacity-100" :title="showPass ? 'Hide Password' : 'Show Password'">
                        <span x-text="showPass ? '🙈' : '👁️'">👁️</span>
                    </button>
                    <button type="button" @click.stop="navigator.clipboard.writeText('{{ $password }}'); alert('SBL Ecosystem Password copied: {{ $password }}');" title="Copy SBL Ecosystem Password">
                        <svg class="w-3 h-3 opacity-75 hover:opacity-100 inline cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </button>
                </div>
                <span class="opacity-40">|</span>
                <div class="flex items-center gap-0.5">
                    <span class="opacity-80 font-semibold" title="SBL ট্রানজেকশন TPIN">TPIN:</span>
                    <span class="font-mono font-bold">{{ $tpin }}</span>
                    <button type="button" @click.stop="navigator.clipboard.writeText('{{ $tpin }}'); alert('SBL TPIN copied: {{ $tpin }}');" title="Copy TPIN">
                        <svg class="w-3 h-3 opacity-75 hover:opacity-100 inline cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- 7. Direct Slots & Network Stats (5L + 5R) -->
        <div class="mt-2 pt-2 border-t border-white/20 grid grid-cols-2 text-xs relative font-medium">
            <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-[1px] bg-white/20 rounded-full"></div>

            <!-- Left Branch Stats -->
            <div class="pr-1.5 space-y-0.5 text-center">
                <div class="font-black text-emerald-300 text-[10px] uppercase flex items-center justify-center gap-1">
                    <span>👈 LEFT ({{ $directL }}/5)</span>
                </div>
                <div class="text-[10px] text-white/95 font-bold">Net: {{ $totalL }} জন</div>
                <div class="text-[10px] text-emerald-200 font-medium truncate">
                    @currency($node['left_investment_volume'] ?? 0)
                </div>
            </div>

            <!-- Right Branch Stats -->
            <div class="pl-1.5 space-y-0.5 text-center">
                <div class="font-black text-blue-300 text-[10px] uppercase flex items-center justify-center gap-1">
                    <span>RIGHT ({{ $directR }}/5) 👉</span>
                </div>
                <div class="text-[10px] text-white/95 font-bold">Net: {{ $totalR }} জন</div>
                <div class="text-[10px] text-blue-200 font-medium truncate">
                    @currency($node['right_investment_volume'] ?? 0)
                </div>
            </div>
        </div>

        <!-- 8. Target Conversion Button (if target) or View Tree Action -->
        <div class="mt-2 pt-1.5 border-t border-white/20 text-xs font-semibold text-white/95 flex items-center justify-between px-1">
            @if($isTarget)
            <form action="{{ route('binary.convert-target', $node['id']) }}" method="POST" class="w-full" @click.stop>
                @csrf
                <button type="submit" class="w-full py-1 px-2 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-[11px] shadow-sm transition-all flex items-center justify-center gap-1">
                    <span>🚀</span> <span>অ্যাক্টিভ মেম্বার করুন</span>
                </button>
            </form>
            @else
            <span>Own: <strong class="text-amber-200" x-text="$store.currency ? $store.currency.format({{ (float)($node['own_investment'] ?? $node['point_value']) }}) : '{{ \App\Services\CurrencyService::format((float)($node['own_investment'] ?? $node['point_value'])) }}'">{{ \App\Services\CurrencyService::format((float)($node['own_investment'] ?? $node['point_value'])) }}</strong></span>
            
            <a href="{{ route('binary.index', ['node_id' => $node['id']]) }}" 
               class="px-2 py-0.5 rounded-lg bg-black/40 hover:bg-black/70 text-[10px] font-bold text-amber-200 flex items-center gap-1 transition-all"
               title="এই মেম্বারের ১০-স্লট টিম ট্রি খুলুন">
                <span>View Team</span> <span>➔</span>
            </a>
            @endif
        </div>
    </div>
@endif
