<div class="flex flex-col items-center select-none" data-tree-node-id="{{ $node['id'] ?? 'vacant' }}">
    <!-- Member / Vacant Node Card -->
    @include('binary.partials.node_card', ['node' => $node, 'depth' => $depth ?? 1])

    @if(!$node['is_vacant'])
        @php
            $directL = (int)($node['direct_left_count'] ?? 0);
            $directR = (int)($node['direct_right_count'] ?? 0);
            $totalDirect = $directL + $directR;
            $leftSlots = $node['left_slots'] ?? [];
            $rightSlots = $node['right_slots'] ?? [];
        @endphp

        <!-- FigJam Stem down to 10-slot Container -->
        <div class="flex flex-col items-center z-20">
            <div class="w-[2px] h-4 bg-white/60"></div>
            
            <button type="button"
                    @click.stop="toggleExpanded({{ $node['id'] }})"
                    class="px-3 py-1 rounded-full text-[11px] font-black border transition-all duration-150 shadow-lg flex items-center gap-1.5 cursor-pointer hover:scale-105 active:scale-95"
                    :class="isExpanded({{ $node['id'] }}) ? 'bg-orange-600 text-white border-orange-400 hover:bg-orange-500 shadow-orange-950/50' : 'bg-slate-900 text-orange-300 border-orange-500/60 hover:bg-slate-800 shadow-black/60'"
                    title="১০-স্লট ডিরেক্ট প্লেসমেন্ট ওপেন বা ক্লোজ করুন">
                <span class="font-black text-xs" x-text="isExpanded({{ $node['id'] }}) ? '−' : '+'">−</span>
                <span>১০টি ডিরেক্ট প্লেসমেন্ট স্লট (L: {{ $directL }}/5 | R: {{ $directR }}/5)</span>
            </button>
        </div>

        <!-- 10-Slot Placement Wings (5 Left Slots + 5 Right Slots) -->
        <div x-show="isExpanded({{ $node['id'] }})" 
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             class="w-full flex flex-col items-center pt-2">
            
            <!-- Stem from toggle button down to crossbar bridge -->
            <div class="w-[2px] h-4 bg-white/60"></div>

            <!-- Two Visual Wings: LEFT TEAM (5 Slots) and RIGHT TEAM (5 Slots) -->
            <div class="flex flex-col lg:flex-row items-start justify-center gap-6 md:gap-8 pt-0 w-full">
                
                <!-- ==================== LEFT TEAM CONTAINER (5 SLOTS) ==================== -->
                <div class="flex-1 min-w-[320px] max-w-[660px] bg-[#162f27]/90 rounded-2xl border-2 border-emerald-500/40 p-4 shadow-2xl space-y-4">
                    <!-- Left Wing Header -->
                    <div class="flex items-center justify-between border-b border-emerald-500/30 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-400 shadow-sm shadow-emerald-400/50 animate-pulse"></span>
                            <h4 class="font-black text-sm text-emerald-300 uppercase tracking-wider">
                                👈 LEFT TEAM ({{ $directL }}/5 Direct)
                            </h4>
                        </div>
                        <div class="text-xs text-emerald-200 font-bold">
                            Total Net: {{ (int)($node['total_left_network'] ?? 0) }} জন
                        </div>
                    </div>

                    <!-- 5 Left Slots Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 justify-items-center">
                        @for($s = 1; $s <= 5; $s++)
                            @php
                                $slotNode = $leftSlots[$s] ?? null;
                            @endphp
                            <div class="flex flex-col items-center w-full">
                                @if($slotNode)
                                    @if($slotNode['is_vacant'])
                                        @include('binary.partials.node_card', ['node' => $slotNode, 'depth' => ($depth ?? 1) + 1])
                                    @else
                                        @include('binary.partials.node_card', ['node' => $slotNode, 'depth' => ($depth ?? 1) + 1])
                                    @endif
                                @endif
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- ==================== RIGHT TEAM CONTAINER (5 SLOTS) ==================== -->
                <div class="flex-1 min-w-[320px] max-w-[660px] bg-[#1a283e]/90 rounded-2xl border-2 border-blue-500/40 p-4 shadow-2xl space-y-4">
                    <!-- Right Wing Header -->
                    <div class="flex items-center justify-between border-b border-blue-500/30 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-400 shadow-sm shadow-blue-400/50 animate-pulse"></span>
                            <h4 class="font-black text-sm text-blue-300 uppercase tracking-wider">
                                RIGHT TEAM ({{ $directR }}/5 Direct) 👉
                            </h4>
                        </div>
                        <div class="text-xs text-blue-200 font-bold">
                            Total Net: {{ (int)($node['total_right_network'] ?? 0) }} জন
                        </div>
                    </div>

                    <!-- 5 Right Slots Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 justify-items-center">
                        @for($s = 1; $s <= 5; $s++)
                            @php
                                $slotNode = $rightSlots[$s] ?? null;
                            @endphp
                            <div class="flex flex-col items-center w-full">
                                @if($slotNode)
                                    @if($slotNode['is_vacant'])
                                        @include('binary.partials.node_card', ['node' => $slotNode, 'depth' => ($depth ?? 1) + 1])
                                    @else
                                        @include('binary.partials.node_card', ['node' => $slotNode, 'depth' => ($depth ?? 1) + 1])
                                    @endif
                                @endif
                            </div>
                        @endfor
                    </div>
                </div>

            </div>
        </div>
    @endif
</div>
