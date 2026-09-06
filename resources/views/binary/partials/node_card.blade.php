@if($node['is_vacant'])
    <!-- VACANT SLOT CARD (+ ADD MEMBER) -->
    <div class="w-60 md:w-64 p-4 rounded-xl border-2 border-dashed border-white/30 bg-white/5 hover:bg-white/10 transition-all flex flex-col items-center justify-center text-center space-y-2.5 text-white shadow-md group">
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
        $phone = $node['phone'] ?: '01700000000';
        $password = $node['password_plain'] ?? 'sbl123456';
        $tpin = $node['tpin'] ?? '1234';
        $leftTarget = $node['left_target_count'] ?? $node['left_count'];
        $rightTarget = $node['right_target_count'] ?? $node['right_count'];
    @endphp

    <!-- OCCUPIED MEMBER NODE CARD -->
    <div data-node-id="{{ $node['id'] }}" 
         x-data="{ showPass: false }"
         class="w-60 md:w-64 rounded-xl border {{ $cardBg }} shadow-lg p-3.5 flex flex-col justify-between text-center text-white select-none relative group transition-all duration-200 hover:shadow-2xl hover:scale-[1.015]">
        
        <!-- Floating Action Overlay (Edit, Drill-down, Delete) -->
        <div class="absolute top-2 right-2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-20">
            <button type="button" 
                    @click.stop="openEditModal({{ json_encode($node) }})"
                    class="p-1 rounded-md bg-black/60 hover:bg-black/90 text-white text-xs backdrop-blur-xs transition-colors shadow-xs" 
                    title="মেম্বার এডিট করুন">
                ✏️
            </button>
            <a href="{{ route('binary.index', ['node_id' => $node['id']]) }}" 
               class="p-1 rounded-md bg-black/60 hover:bg-black/90 text-white text-xs backdrop-blur-xs transition-colors shadow-xs" 
               title="এই মেম্বার থেকে নতুন ট্রি দেখুন (Drill Down)">
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
            
            <!-- 1. Full Name -->
            <h3 class="font-bold text-base md:text-lg text-white leading-tight tracking-tight drop-shadow-xs flex items-center justify-center gap-1">
                <span>{{ $node['member_name'] }}</span>
            </h3>

            <!-- 2. Username (with copy button) -->
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

            <!-- 4. Rank -->
            <div class="text-xs {{ $subTextColor }} font-normal">
                Rank: <span class="font-bold {{ !empty($node['is_fme']) ? 'text-amber-200' : '' }}">{{ $node['rank_name'] ?: 'Member' }}</span>
            </div>

            <!-- 5. Email with Copy Icon -->
            <div class="text-[11px] {{ $subTextColor }} flex items-center justify-center gap-1 hover:text-white transition-colors"
                 @click.stop="navigator.clipboard.writeText('{{ $userEmail }}'); alert('Email copied: {{ $userEmail }}');"
                 title="Click to copy email">
                <span class="truncate max-w-[190px]">({{ $userEmail }}</span>
                <svg class="w-3.5 h-3.5 inline-block opacity-75 hover:opacity-100 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                <span>)</span>
            </div>

            <!-- 6. Password & TPIN Row (with copy and toggle) -->
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

            <!-- 7. Sponsor / By -->
            <div class="text-xs {{ $subTextColor }} font-medium pt-0.5">
                By {{ $sponsorName }}
            </div>
        </div>

        <!-- 8. Binary Legs Stats (L vs R) with dark vertical divider -->
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
                <div class="text-[11px] text-white/95 font-semibold leading-tight">Team- {{ $node['left_display'] ?? ($node['left_count'] < 5 ? $node['left_count'].'/5' : $node['left_count']) }}</div>
                <div class="text-[11px] text-white/95 font-semibold leading-tight">Vol- <span x-text="$store.currency ? $store.currency.format({{ (float)($node['left_investment_volume'] ?? $node['left_bv']) }}) : '{{ \App\Services\CurrencyService::format((float)($node['left_investment_volume'] ?? $node['left_bv'])) }}'">{{ \App\Services\CurrencyService::format((float)($node['left_investment_volume'] ?? $node['left_bv'])) }}</span></div>
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
                <div class="text-[11px] text-white/95 font-semibold leading-tight">Team- {{ $node['right_display'] ?? ($node['right_count'] < 5 ? $node['right_count'].'/5' : $node['right_count']) }}</div>
                <div class="text-[11px] text-white/95 font-semibold leading-tight">Vol- <span x-text="$store.currency ? $store.currency.format({{ (float)($node['right_investment_volume'] ?? $node['right_bv']) }}) : '{{ \App\Services\CurrencyService::format((float)($node['right_investment_volume'] ?? $node['right_bv'])) }}'">{{ \App\Services\CurrencyService::format((float)($node['right_investment_volume'] ?? $node['right_bv'])) }}</span></div>
            </div>
        </div>

        <!-- 9. Bottom Total Investment Row -->
        <div class="mt-2 pt-1.5 border-t border-white/20 text-xs font-semibold text-white/95 flex items-center justify-between px-1">
            <span>Total Investment: <span x-text="$store.currency ? $store.currency.format({{ (float)($node['total_investment'] ?? $node['point_value']) }}) : '{{ \App\Services\CurrencyService::format((float)($node['total_investment'] ?? $node['point_value'])) }}'">{{ \App\Services\CurrencyService::format((float)($node['total_investment'] ?? $node['point_value'])) }}</span></span>
            @if(!empty($node['contributions']) && count($node['contributions']) > 1)
            <span class="text-[10px] bg-white/20 px-1.5 py-0.5 rounded-full font-bold" title="Multiple investments added">
                {{ count($node['contributions']) }} records
            </span>
            @endif
        </div>
    </div>
@endif
