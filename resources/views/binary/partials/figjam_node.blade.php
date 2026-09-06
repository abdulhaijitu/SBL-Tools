<div class="flex flex-col items-center select-none" data-tree-node-id="{{ $node['id'] ?? 'vacant' }}">
    <!-- Member / Vacant Node Card -->
    @include('binary.partials.node_card', ['node' => $node, 'depth' => $depth ?? 1])

    @if(!$node['is_vacant'])
        @php
            $downlineCount = (int)($node['left_count'] ?? 0) + (int)($node['right_count'] ?? 0);
        @endphp

        <!-- FigJam-style Interactive Sprout/Expand Toggle Button beneath card -->
        <div class="flex flex-col items-center z-20">
            <!-- Stem from card bottom down to toggle button -->
            <div class="w-[2px] h-3 bg-white/70"></div>
            
            <button type="button"
                    @click.stop="toggleExpanded({{ $node['id'] }})"
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border transition-all duration-150 shadow-md flex items-center gap-1.5 cursor-pointer hover:scale-105 active:scale-95"
                    :class="isExpanded({{ $node['id'] }}) ? 'bg-orange-600 text-white border-orange-400 hover:bg-orange-500 shadow-orange-950/40' : 'bg-slate-900 text-orange-300 border-orange-500/60 hover:bg-slate-800 shadow-black/50'"
                    title="ক্লিক করে সাব-ব্রাঞ্চ ওপেন বা ক্লোজ করুন">
                <span class="font-extrabold text-xs" x-text="isExpanded({{ $node['id'] }}) ? '−' : '+'">−</span>
                <span>{{ $downlineCount > 0 ? $downlineCount . ' টিম' : 'ব্রাঞ্চ' }}</span>
            </button>
        </div>

        <!-- Sub-branches (Left and Right) displayed when node is expanded -->
        <div x-show="isExpanded({{ $node['id'] }})" 
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             class="w-full flex flex-col items-center">
            
            <!-- Stem from toggle button down to horizontal bridge -->
            <div class="w-[2px] h-3.5 bg-white/70"></div>

            <!-- Left & Right Sub-branch columns with seamless connector bridge -->
            <div class="flex items-start justify-center pt-0 w-full">
                
                <!-- Left Branch Column -->
                <div class="flex flex-col items-center relative pr-3 md:pr-5">
                    <!-- Top horizontal line from center to right edge -->
                    <div class="absolute top-0 right-0 w-1/2 h-[2px] bg-white/70"></div>
                    <!-- Vertical stem down to Left child card -->
                    <div class="w-[2px] h-4 bg-white/70"></div>

                    @if(isset($node['left']))
                        @include('binary.partials.figjam_node', ['node' => $node['left'], 'depth' => ($depth ?? 1) + 1])
                    @endif
                </div>

                <!-- Right Branch Column -->
                <div class="flex flex-col items-center relative pl-3 md:pl-5">
                    <!-- Top horizontal line from left edge to center -->
                    <div class="absolute top-0 left-0 w-1/2 h-[2px] bg-white/70"></div>
                    <!-- Vertical stem down to Right child card -->
                    <div class="w-[2px] h-4 bg-white/70"></div>

                    @if(isset($node['right']))
                        @include('binary.partials.figjam_node', ['node' => $node['right'], 'depth' => ($depth ?? 1) + 1])
                    @endif
                </div>

            </div>
        </div>
    @endif
</div>
