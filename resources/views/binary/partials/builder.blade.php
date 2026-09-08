@php
    $root = $treeData['root'];
    $stats = $treeData['stats'] ?? [];
    $leftSlots = $treeData['left_slots'] ?? [];
    $rightSlots = $treeData['right_slots'] ?? [];
    $leftFilledCount = count(array_filter($leftSlots, fn($s) => empty($s['is_vacant'])));
    $rightFilledCount = count(array_filter($rightSlots, fn($s) => empty($s['is_vacant'])));
@endphp

<div class="space-y-6" x-data="{ mobileBranchTab: 'LEFT' }">
    <!-- ==================== 1. ACTIVE ROOT MEMBER & BALANCE GAUGE ==================== -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Top Banner: Active Root Info -->
        <div class="p-5 sm:p-6 bg-gradient-to-r from-slate-900 via-slate-800 to-orange-950 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div data-current-member-initial class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 text-white font-black text-2xl flex items-center justify-center shadow-lg border-2 border-white/20 flex-shrink-0">
                    {{ substr($root->member_name ?? 'M', 0, 1) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 data-current-member-name class="text-xl font-black text-white tracking-tight">{{ $root->member_name ?? 'Team Root' }}</h2>
                        <span data-current-member-rank class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-orange-500/30 text-orange-200 border border-orange-400/40">
                            {{ $root->rank_name ?? 'Member' }}
                        </span>
                        @if(!empty($stats['is_fme']))
                            <span data-current-member-fme class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/30 text-emerald-200 border border-emerald-400/40">
                                FME QUALIFIED
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-300 font-mono mt-1">
                        <span data-current-member-code class="text-orange-300 font-bold">{{ $root->member_code ?? ('SBL-' . $root->id) }}</span>
                        @if($root->phone)
                            <span data-current-member-phone-sep>•</span>
                            <span data-current-member-phone>{{ $root->phone }}</span>
                        @endif
                        <span>•</span>
                        <span class="text-slate-400 font-sans">Sponsor: <strong data-current-member-sponsor>{{ $stats['sponsor_name'] ?? 'Not assigned' }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Links -->
            <div class="flex flex-wrap items-center gap-2">
                @if(!empty($treeData['parent_node']))
                    <a href="{{ route('team.show', ['memberId' => $treeData['parent_node']->id, 'owner_id' => $ownerId]) }}" 
                       class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold border border-white/15 transition-all flex items-center gap-1.5 shadow-2xs">
                        <span>⬆️</span>
                        <span>Upline ({{ $treeData['parent_node']->member_name }})</span>
                    </a>
                @endif
                <button type="button" 
                        data-btn-full-details
                        @click="openDetailsModal({{ $root->id }})" 
                        class="px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm active:scale-95 cursor-pointer">
                    <span>👁️</span>
                    <span>Full Profile</span>
                </button>
            </div>
        </div>

        <!-- Binary Balance Metrics Strip -->
        <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-slate-100 bg-slate-50/70 border-t border-slate-200/80 text-xs">
            <!-- Left Side Direct & Volume -->
            <div class="p-4 sm:p-5 space-y-1">
                <div class="flex items-center justify-between text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                    <span class="flex items-center gap-1 text-emerald-700">👈 Left Direct Team</span>
                    <span class="font-mono text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">{{ $leftFilledCount }}/5 Slots</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-black text-slate-900">{{ number_format($stats['left_bv'] ?? 0) }}</span>
                    <span class="text-xs font-bold text-slate-500">BV</span>
                </div>
                <div class="text-[11px] text-slate-400">Total Network: <strong>{{ $stats['total_left_network'] ?? 0 }} members</strong></div>
            </div>

            <!-- Right Side Direct & Volume -->
            <div class="p-4 sm:p-5 space-y-1">
                <div class="flex items-center justify-between text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                    <span class="flex items-center gap-1 text-blue-700">👉 Right Direct Team</span>
                    <span class="font-mono text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded">{{ $rightFilledCount }}/5 Slots</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-black text-slate-900">{{ number_format($stats['right_bv'] ?? 0) }}</span>
                    <span class="text-xs font-bold text-slate-500">BV</span>
                </div>
                <div class="text-[11px] text-slate-400">Total Network: <strong>{{ $stats['total_right_network'] ?? 0 }} members</strong></div>
            </div>

            <!-- Carry Points & Matching Pairs -->
            <div class="p-4 sm:p-5 space-y-1">
                <div class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">⚖️ Matching & Carry</div>
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-black text-orange-600">{{ $stats['matched_pairs'] ?? 0 }}</span>
                    <span class="text-xs font-bold text-slate-500">Matched Pairs</span>
                </div>
                <div class="text-[11px] text-slate-500 font-mono">
                    Carry: L: <strong class="text-emerald-700">{{ $stats['carry_left'] ?? 0 }}</strong> | R: <strong class="text-blue-700">{{ $stats['carry_right'] ?? 0 }}</strong>
                </div>
            </div>

            <!-- Weaker Leg Recommendation -->
            <div class="p-4 sm:p-5 space-y-1 bg-amber-50/50">
                <div class="text-amber-800 font-bold uppercase tracking-wider text-[10px] flex items-center gap-1">
                    <span>⚡ Placement Advice</span>
                </div>
                <div class="font-black text-slate-900 text-sm">
                    Recommended: 
                    <span class="px-2 py-0.5 rounded font-black {{ ($stats['weaker_leg'] ?? 'LEFT') === 'LEFT' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $stats['weaker_leg'] ?? 'LEFT' }} LEG
                    </span>
                </div>
                <div class="text-[11px] text-slate-600">
                    Place next member on {{ $stats['weaker_leg'] ?? 'LEFT' }} to balance BV points!
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== 2. QUICK 1-TAP PLACEMENT SHORTCUT BAR ==================== -->
    <div class="bg-gradient-to-r from-orange-50 via-amber-50 to-orange-50/60 p-4 rounded-2xl border border-orange-200/80 flex flex-wrap items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-xl bg-orange-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">⚡</span>
            <div>
                <h4 class="text-xs font-black text-slate-900">Quick Team Builder (১-ক্লিক প্লেসমেন্ট)</h4>
                <p class="text-[11px] text-slate-600">খালি স্লটে সরাসরি মেম্বার বসাতে নিচের যেকোনো বাটনে ট্যাপ করুন:</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Quick Add Left -->
            @if($firstVacantLeft)
                <button type="button" 
                        @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, 'LEFT', {{ $firstVacantLeft['slot_number'] }})"
                        class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>➕ Add to Left (Slot L-{{ $firstVacantLeft['slot_number'] }})</span>
                </button>
            @else
                <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold">Left Full (5/5)</span>
            @endif

            <!-- Quick Add Right -->
            @if($firstVacantRight)
                <button type="button" 
                        @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, 'RIGHT', {{ $firstVacantRight['slot_number'] }})"
                        class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>➕ Add to Right (Slot R-{{ $firstVacantRight['slot_number'] }})</span>
                </button>
            @else
                <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold">Right Full (5/5)</span>
            @endif

            <!-- Auto-Balance Weaker Leg Button -->
            @if($autoBalanceSlot)
                <button type="button" 
                        @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, '{{ $autoBalanceSlot['branch'] }}', {{ $autoBalanceSlot['slot_number'] }})"
                        class="px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-black shadow-xs active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>🎯 Auto-Balance ({{ $autoBalanceSlot['branch'] }}-{{ $autoBalanceSlot['slot_number'] }})</span>
                </button>
            @endif
        </div>
    </div>

    <!-- ==================== 3. MOBILE TAB SWITCHER (LEFT vs RIGHT) ==================== -->
    <div class="block lg:hidden">
        <div class="grid grid-cols-2 p-1.5 bg-slate-100 rounded-2xl border border-slate-200">
            <button type="button" 
                    @click="mobileBranchTab = 'LEFT'" 
                    class="py-2.5 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 cursor-pointer"
                    :class="mobileBranchTab === 'LEFT' ? 'bg-white text-emerald-800 shadow-xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900'">
                <span>👈 Left Team</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                      :class="mobileBranchTab === 'LEFT' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'">
                    {{ $leftFilledCount }}/5
                </span>
            </button>

            <button type="button" 
                    @click="mobileBranchTab = 'RIGHT'" 
                    class="py-2.5 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 cursor-pointer"
                    :class="mobileBranchTab === 'RIGHT' ? 'bg-white text-blue-800 shadow-xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900'">
                <span>👉 Right Team</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                      :class="mobileBranchTab === 'RIGHT' ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-600'">
                    {{ $rightFilledCount }}/5
                </span>
            </button>
        </div>
    </div>

    <!-- ==================== 4. 10-SLOT NETWORK STUDIO (DUAL COLUMN DESKTOP / TABBED MOBILE) ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- ==================== LEFT TEAM (5 SLOTS) ==================== -->
        <div class="space-y-3.5" :class="mobileBranchTab === 'LEFT' ? 'block' : 'hidden lg:block'">
            <div class="flex items-center justify-between border-b-2 border-emerald-500/40 pb-2 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 font-black text-xs flex items-center justify-center">L</span>
                    <h3 class="text-sm font-black text-slate-900">LEFT TEAM (৫টি স্লট)</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span data-left-header-count class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/80">
                        {{ $leftFilledCount }} Active / 5 Max
                    </span>
                    @if($firstVacantLeft)
                        <button type="button" 
                                @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, 'LEFT', {{ $firstVacantLeft['slot_number'] }})"
                                class="text-xs font-bold text-emerald-700 hover:text-emerald-900 underline cursor-pointer">
                            + Add Next
                        </button>
                    @endif
                </div>
            </div>

            <div data-left-slots-container class="space-y-3">
                @for($slot = 1; $slot <= 5; $slot++)
                    @php $slotData = $leftSlots[$slot] ?? ['is_vacant' => true, 'slot_number' => $slot, 'branch' => 'LEFT']; @endphp
                    
                    @if(!empty($slotData['is_vacant']))
                        <!-- Vacant Slot Card -->
                        <div class="p-4 rounded-2xl border-2 border-dashed border-emerald-300/80 bg-emerald-50/20 hover:bg-emerald-50/60 transition-all flex items-center justify-between gap-3 group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs border border-emerald-200">
                                    L-{{ $slot }}
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-700">Slot L-{{ $slot }} (খালি রয়েছে)</div>
                                    <div class="text-[11px] text-slate-400">নতুন মেম্বারকে এই পজিশনে বসান</div>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, 'LEFT', {{ $slot }})"
                                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center gap-1 cursor-pointer">
                                <span>➕</span>
                                <span>Place Member</span>
                            </button>
                        </div>
                    @else
                        <!-- Occupied Member Slot Card -->
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-emerald-400/80 shadow-xs hover:shadow-md transition-all space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-orange-400 font-black text-sm flex items-center justify-center shadow-xs flex-shrink-0 border border-slate-700">
                                        {{ substr($slotData['member_name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-black text-[10px]">
                                                SLOT L-{{ $slot }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                                                GEN 1
                                            </span>
                                            @if(!empty($slotData['is_target']))
                                                <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-1.5 py-0.5 rounded">🎯 Target</span>
                                            @endif
                                        </div>
                                        <div class="font-black text-slate-900 text-sm mt-0.5 hover:text-orange-600 cursor-pointer"
                                             @click="openDetailsModal({{ $slotData['id'] }})">
                                            {{ $slotData['member_name'] }}
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 font-mono mt-0.5">
                                            <span>{{ $slotData['member_code'] }}</span>
                                            <button type="button" @click.stop="copyToClipboard('{{ $slotData['member_code'] }}', 'Member Code')" class="text-slate-400 hover:text-orange-600 p-0.5 cursor-pointer">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Quick Icons (Call & WhatsApp) -->
                                <div class="flex items-center gap-1">
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

                            <!-- Meta Badges: Rank, Package, BV -->
                            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-[11px]">
                                        {{ $slotData['rank_name'] ?? 'Member' }}
                                    </span>
                                    <span class="font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/80 text-[11px]">
                                        {{ $slotData['point_value'] ?? 100 }} BV
                                    </span>
                                    <span class="text-slate-400 text-[11px]">
                                        {{ $slotData['package_name'] ?? 'National' }}
                                    </span>
                                </div>

                                <!-- Action Buttons: Drill-Down & Details -->
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('team.show', ['memberId' => $slotData['id'], 'owner_id' => $ownerId]) }}" 
                                       class="px-2.5 py-1 rounded-lg bg-orange-50 hover:bg-orange-600 text-orange-700 hover:text-white font-bold text-xs transition-all flex items-center gap-1 border border-orange-200/60 shadow-2xs">
                                        <span>👥</span>
                                        <span>Explore Team</span>
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
        <div class="space-y-3.5" :class="mobileBranchTab === 'RIGHT' ? 'block' : 'hidden lg:block'">
            <div class="flex items-center justify-between border-b-2 border-blue-500/40 pb-2 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-800 font-black text-xs flex items-center justify-center">R</span>
                    <h3 class="text-sm font-black text-slate-900">RIGHT TEAM (৫টি স্লট)</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span data-right-header-count class="text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200/80">
                        {{ $rightFilledCount }} Active / 5 Max
                    </span>
                    @if($firstVacantRight)
                        <button type="button" 
                                @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, 'RIGHT', {{ $firstVacantRight['slot_number'] }})"
                                class="text-xs font-bold text-blue-700 hover:text-blue-900 underline cursor-pointer">
                            + Add Next
                        </button>
                    @endif
                </div>
            </div>

            <div data-right-slots-container class="space-y-3">
                @for($slot = 1; $slot <= 5; $slot++)
                    @php $slotData = $rightSlots[$slot] ?? ['is_vacant' => true, 'slot_number' => $slot, 'branch' => 'RIGHT']; @endphp
                    
                    @if(!empty($slotData['is_vacant']))
                        <!-- Vacant Slot Card -->
                        <div class="p-4 rounded-2xl border-2 border-dashed border-blue-300/80 bg-blue-50/20 hover:bg-blue-50/60 transition-all flex items-center justify-between gap-3 group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs border border-blue-200">
                                    R-{{ $slot }}
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-700">Slot R-{{ $slot }} (খালি রয়েছে)</div>
                                    <div class="text-[11px] text-slate-400">নতুন মেম্বারকে এই পজিশনে বসান</div>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="openPlacementModal({{ $root->id }}, {{ json_encode($root->member_name) }}, {{ json_encode($root->member_code ?? '') }}, 'RIGHT', {{ $slot }})"
                                    class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center gap-1 cursor-pointer">
                                <span>➕</span>
                                <span>Place Member</span>
                            </button>
                        </div>
                    @else
                        <!-- Occupied Member Slot Card -->
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-blue-400/80 shadow-xs hover:shadow-md transition-all space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-orange-400 font-black text-sm flex items-center justify-center shadow-xs flex-shrink-0 border border-slate-700">
                                        {{ substr($slotData['member_name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 font-black text-[10px]">
                                                SLOT R-{{ $slot }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                                                GEN 1
                                            </span>
                                            @if(!empty($slotData['is_target']))
                                                <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-1.5 py-0.5 rounded">🎯 Target</span>
                                            @endif
                                        </div>
                                        <div class="font-black text-slate-900 text-sm mt-0.5 hover:text-orange-600 cursor-pointer"
                                             @click="openDetailsModal({{ $slotData['id'] }})">
                                            {{ $slotData['member_name'] }}
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 font-mono mt-0.5">
                                            <span>{{ $slotData['member_code'] }}</span>
                                            <button type="button" @click.stop="copyToClipboard('{{ $slotData['member_code'] }}', 'Member Code')" class="text-slate-400 hover:text-orange-600 p-0.5 cursor-pointer">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Quick Icons (Call & WhatsApp) -->
                                <div class="flex items-center gap-1">
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

                            <!-- Meta Badges: Rank, Package, BV -->
                            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-[11px]">
                                        {{ $slotData['rank_name'] ?? 'Member' }}
                                    </span>
                                    <span class="font-black text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200/80 text-[11px]">
                                        {{ $slotData['point_value'] ?? 100 }} BV
                                    </span>
                                    <span class="text-slate-400 text-[11px]">
                                        {{ $slotData['package_name'] ?? 'National' }}
                                    </span>
                                </div>

                                <!-- Action Buttons: Drill-Down & Details -->
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('team.show', ['memberId' => $slotData['id'], 'owner_id' => $ownerId]) }}" 
                                       class="px-2.5 py-1 rounded-lg bg-orange-50 hover:bg-orange-600 text-orange-700 hover:text-white font-bold text-xs transition-all flex items-center gap-1 border border-orange-200/60 shadow-2xs">
                                        <span>👥</span>
                                        <span>Explore Team</span>
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
</div>

