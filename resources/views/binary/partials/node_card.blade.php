@if($node['is_vacant'])
    <!-- VACANT SLOT CARD (+ ADD MEMBER) -->
    <div class="w-48 md:w-56 p-4 rounded-2xl border-2 border-dashed {{ $node['position'] === 'left' ? 'border-emerald-300 bg-emerald-50/40 hover:bg-emerald-50 hover:border-emerald-400' : 'border-blue-300 bg-blue-50/40 hover:bg-blue-50 hover:border-blue-400' }} transition-all flex flex-col items-center justify-center text-center space-y-2.5 group shadow-xs">
        
        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $node['position'] === 'left' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
            {{ $node['position'] === 'left' ? '👈 বাম টিম (Left)' : '👉 ডান টিম (Right)' }}
        </span>

        <div class="w-10 h-10 rounded-full {{ $node['position'] === 'left' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }} flex items-center justify-center text-lg font-bold group-hover:scale-110 transition-transform">
            +
        </div>

        <div class="text-xs font-bold text-slate-700">
            খালি পজিশন
        </div>

        <button type="button" 
                @click="openPlacementModal({{ $node['parent_id'] }}, '{{ addslashes($node['parent_name']) }}', '{{ $node['parent_code'] }}', '{{ $node['position'] }}')"
                class="w-full py-1.5 px-2 rounded-xl {{ $node['position'] === 'left' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-[11px] font-bold shadow-xs transition-colors">
            + মেম্বার যোগ করুন
        </button>
    </div>

@else
    <!-- OCCUPIED MEMBER NODE CARD -->
    <div data-node-id="{{ $node['id'] }}" class="w-48 md:w-56 bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md hover:border-orange-300 transition-all p-3.5 flex flex-col justify-between group">
        
        <div>
            <!-- Top Rank & Position Pill -->
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-orange-100 text-orange-800 border border-orange-200 truncate max-w-[120px]">
                    {{ $node['rank_name'] }}
                </span>

                @if($node['position'])
                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $node['position'] === 'left' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                    {{ $node['position'] === 'left' ? 'L' : 'R' }}
                </span>
                @else
                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">ROOT</span>
                @endif
            </div>

            <!-- Avatar & Member Info -->
            <div class="flex items-center gap-2.5 mb-2.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-slate-900 to-slate-800 text-orange-400 font-bold flex items-center justify-center text-sm shadow-xs flex-shrink-0">
                    {{ substr($node['member_name'], 0, 1) }}
                </div>
                <div class="truncate">
                    <h4 class="text-xs font-bold text-slate-900 truncate group-hover:text-orange-600 transition-colors" title="{{ $node['member_name'] }}">
                        {{ $node['member_name'] }}
                    </h4>
                    <div class="text-[10px] font-semibold text-slate-400 tracking-wide font-mono">
                        {{ $node['member_code'] }}
                    </div>
                </div>
            </div>

            <!-- Package & Point Badge -->
            <div class="mb-2.5 flex items-center justify-between text-[10px] bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">
                <span class="text-slate-600 font-medium truncate max-w-[100px]">{{ $node['package_name'] }}</span>
                <span class="font-bold text-orange-600">{{ (int)$node['point_value'] }} BV</span>
            </div>

            <!-- Left vs Right Counters -->
            <div class="grid grid-cols-2 gap-1 text-[10px] text-center font-medium bg-slate-50/80 p-1.5 rounded-lg border border-slate-100 mb-2">
                <div class="text-emerald-700 border-r border-slate-200/60 pr-1">
                    <div class="text-[9px] text-slate-400 uppercase font-bold">Left</div>
                    <div class="font-extrabold">{{ $node['left_count'] }} <span class="font-normal text-[9px]">({{ (int)$node['left_bv'] }})</span></div>
                </div>
                <div class="text-blue-700 pl-1">
                    <div class="text-[9px] text-slate-400 uppercase font-bold">Right</div>
                    <div class="font-extrabold">{{ $node['right_count'] }} <span class="font-normal text-[9px]">({{ (int)$node['right_bv'] }})</span></div>
                </div>
            </div>
        </div>

        <!-- Action Row: Downline Drill-down + Edit/Delete Actions -->
        <div class="border-t border-slate-100 pt-1.5 flex items-center justify-between gap-1">
            <a href="{{ route('binary.index', ['node_id' => $node['id']]) }}" 
               class="text-[11px] font-bold text-orange-600 hover:text-orange-700 hover:bg-orange-50 px-2 py-0.5 rounded transition-colors flex items-center gap-1" title="এই মেম্বারকে কেন্দ্র করে ট্রি দেখুন">
                <span>ডাউনলাইন</span>
                <span class="text-xs">⬇️</span>
            </a>

            <div class="flex items-center gap-1">
                <button type="button" 
                        @click="openEditModal({{ json_encode($node) }})"
                        class="text-slate-400 hover:text-orange-600 p-1 rounded hover:bg-orange-50 transition-colors" 
                        title="মেম্বার এডিট করুন">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                </button>

                @if(! $node['has_children'])
                <form action="{{ route('binary.destroy', $node['id']) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই মেম্বারকে ({{ $node['member_name'] }}) রিমুভ করতে চান? এর ফলে আপলাইন পয়েন্ট রোলব্যাক হবে।');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 rounded hover:bg-rose-50 transition-colors" title="মেম্বার রিমুভ করুন">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
                @else
                <button type="button" 
                        onclick="alert('এই মেম্বারের ডাউনলাইনে সক্রিয় টিম রয়েছে। ট্রি অখণ্ড রাখতে ডাউনলাইন থাকা অবস্থায় সরাসরি মুছে ফেলা যাবে না।')"
                        class="text-slate-300 hover:text-slate-400 p-1 rounded transition-colors cursor-not-allowed" 
                        title="ডাউনলাইন থাকায় ডিলিট সম্ভব নয়">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
                @endif
            </div>
        </div>

    </div>
@endif
