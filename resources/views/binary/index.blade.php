@extends('layouts.app')

@section('page-title', 'Binary Team Engine')
@section('page-subtitle', 'Visual Dual-Team Genealogy Tree, Placement & Matching Volume')

@section('content')
<div class="space-y-6" x-data="{
    placementModalOpen: false,
    selectedParentId: null,
    selectedParentName: '',
    selectedParentCode: '',
    selectedPosition: 'left',
    memberMode: 'new', // 'new' or 'existing'
    selectedUserId: '',
    openPlacementModal(parentId, parentName, parentCode, position) {
        this.selectedParentId = parentId;
        this.selectedParentName = parentName;
        this.selectedParentCode = parentCode;
        this.selectedPosition = position;
        this.placementModalOpen = true;
    }
}">

    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 text-sm font-semibold flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
        <button @click="$el.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-700 text-sm font-semibold flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
        <button @click="$el.parentElement.remove()" class="text-rose-700 hover:text-rose-900 font-bold">&times;</button>
    </div>
    @endif

    <!-- Hero Header & Navigation Bar -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-orange-950 text-white p-6 md:p-8 rounded-2xl border border-slate-800 shadow-md flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-orange-600/30 text-orange-400 border border-orange-500/30 rounded-full text-xs font-bold uppercase tracking-wider">
                <span>🌲</span> SBL Dual-Team Binary System
            </div>
            <h2 class="text-2xl md:text-3xl font-bold tracking-tight">ভিজুয়াল বাইনারি টিম নেটওয়ার্ক</h2>
            <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                বাম টিম (Left Team) ও ডান টিম (Right Team) পরিচালনা করুন। ট্রির যেকোনো খালি স্থানে এক ক্লিকেই মেম্বার প্লেসমেন্ট করুন এবং লাইভ BV পয়েন্ট ট্র্যাক করুন।
            </p>
        </div>

        <!-- Tree Fast Navigation Actions -->
        <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
            <a href="{{ route('binary.index') }}" 
               class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition-colors flex items-center gap-1.5" title="টপ রুট মেম্বারে ফিরুন">
                <span>🏠</span> Top Root
            </a>

            @if($treeData['root'] && $treeData['root']->parent_id)
            <a href="{{ route('binary.index', ['node_id' => $treeData['root']->parent_id]) }}" 
               class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition-colors flex items-center gap-1.5" title="এক ধাপ আপলাইনে যান">
                <span>⬆️</span> Up 1 Level
            </a>
            @endif

            @if($treeData['root'])
            <a href="{{ route('binary.extreme', ['node' => $treeData['root']->id, 'direction' => 'left']) }}" 
               class="px-3 py-2 bg-emerald-950/80 hover:bg-emerald-900 text-emerald-300 border border-emerald-800/80 text-xs font-semibold rounded-xl transition-colors flex items-center gap-1" title="বাম পাশের শেষ প্রান্তে যান">
                <span>◀️</span> Extreme Left
            </a>
            <a href="{{ route('binary.extreme', ['node' => $treeData['root']->id, 'direction' => 'right']) }}" 
               class="px-3 py-2 bg-blue-950/80 hover:bg-blue-900 text-blue-300 border border-blue-800/80 text-xs font-semibold rounded-xl transition-colors flex items-center gap-1" title="ডান পাশের শেষ প্রান্তে যান">
                <span>Extreme Right</span> <span>▶️</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Search & Live Metrics Summary Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-4">
        
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <!-- Search Form -->
            <form action="{{ route('binary.search') }}" method="GET" class="w-full sm:w-96 flex items-center gap-2">
                <div class="relative flex-1">
                    <input type="text" 
                           name="search" 
                           placeholder="মেম্বার কোড বা নাম দিয়ে খুঁজুন (e.g. SBL-1001)..." 
                           class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors">
                    Search
                </button>
            </form>

            <!-- Current Root Tag -->
            @if($treeData['root'])
            <div class="text-xs font-medium text-slate-600 flex items-center gap-2">
                <span class="text-slate-400">বর্তমান ফোকাস:</span>
                <span class="px-2.5 py-1 rounded-lg bg-orange-50 text-orange-700 font-bold border border-orange-200">
                    {{ $treeData['root']->member_name }} ({{ $treeData['root']->member_code }})
                </span>
            </div>
            @endif
        </div>

        <!-- 4-Col Performance Cards -->
        @if($treeData['stats'])
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            <!-- Left Team Metric -->
            <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 space-y-1">
                <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider flex items-center justify-between">
                    <span>👈 বাম টিম (Left Leg)</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-xl font-extrabold text-slate-900">
                    {{ $treeData['stats']['left_count'] }} <span class="text-xs font-normal text-slate-500">জন</span>
                </div>
                <div class="text-xs text-slate-600 font-medium">
                    মোট ভলিউম: <strong class="text-emerald-700">{{ number_format($treeData['stats']['left_bv'], 0) }} BV</strong>
                </div>
                <div class="text-[11px] text-slate-500 pt-1 border-t border-emerald-200/60">
                    বর্তমান ক্যারি: <strong>{{ number_format($treeData['stats']['carry_left'], 0) }} BV</strong>
                </div>
            </div>

            <!-- Right Team Metric -->
            <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200 space-y-1">
                <div class="text-[11px] font-bold text-blue-800 uppercase tracking-wider flex items-center justify-between">
                    <span>👉 ডান টিম (Right Leg)</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <div class="text-xl font-extrabold text-slate-900">
                    {{ $treeData['stats']['right_count'] }} <span class="text-xs font-normal text-slate-500">জন</span>
                </div>
                <div class="text-xs text-slate-600 font-medium">
                    মোট ভলিউম: <strong class="text-blue-700">{{ number_format($treeData['stats']['right_bv'], 0) }} BV</strong>
                </div>
                <div class="text-[11px] text-slate-500 pt-1 border-t border-blue-200/60">
                    বর্তমান ক্যারি: <strong>{{ number_format($treeData['stats']['carry_right'], 0) }} BV</strong>
                </div>
            </div>

            <!-- Matched Pairs -->
            <div class="p-4 rounded-xl bg-purple-50/60 border border-purple-200 space-y-1">
                <div class="text-[11px] font-bold text-purple-800 uppercase tracking-wider flex items-center justify-between">
                    <span>⚖️ ম্যাচিং পেয়ার (১:১)</span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 bg-purple-200 rounded text-purple-800">100 BV Pair</span>
                </div>
                <div class="text-xl font-extrabold text-purple-900">
                    {{ $treeData['stats']['matched_pairs'] }} <span class="text-xs font-normal text-slate-500">টি পেয়ার</span>
                </div>
                <div class="text-xs text-slate-600">
                    ম্যাচিং পয়েন্ট: <strong>{{ number_format($treeData['stats']['matched_pairs'] * 100, 0) }} BV</strong>
                </div>
                <div class="text-[11px] text-purple-700 font-semibold pt-1 border-t border-purple-200/60">
                    সফল ম্যাচিং কমপ্লিট
                </div>
            </div>

            <!-- Weaker Leg Balance Guide -->
            <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200 space-y-1">
                <div class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">
                    🎯 ফোকাস সাইড (দুর্বল লেগ)
                </div>
                <div class="text-base font-extrabold text-amber-900 pt-0.5">
                    @if($treeData['stats']['carry_left'] < $treeData['stats']['carry_right'])
                        👈 বাম টিম (Left Team)
                    @elseif($treeData['stats']['carry_right'] < $treeData['stats']['carry_left'])
                        👉 ডান টিম (Right Team)
                    @else
                        ⚖️ দুই সাইডই ব্যালেন্সড
                    @endif
                </div>
                <p class="text-[11px] text-slate-600 leading-tight">
                    ম্যাক্সিমাম পেয়ার ম্যাচিংয়ের জন্য দুর্বল সাইডে নতুন মেম্বার যুক্ত করুন।
                </p>
            </div>

        </div>
        @endif

    </div>

    <!-- Interactive Visual Genealogy Tree Canvas -->
    <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-6 md:p-10 shadow-xs overflow-x-auto min-h-[600px]">
        <div class="min-w-[768px] mx-auto flex flex-col items-center space-y-8">
            
            @if(empty($treeData['levels']))
                <div class="text-center py-16">
                    <div class="text-4xl mb-2">🌲</div>
                    <h3 class="text-lg font-bold text-slate-800">কোনো টিম ডাটা নেই</h3>
                    <p class="text-xs text-slate-500">দয়া করে ডাটাবেজ সিড করুন অথবা নতুন রুট মেম্বার যুক্ত করুন।</p>
                </div>
            @else

                <!-- LEVEL 1: Root Node -->
                <div class="flex justify-center w-full">
                    @if(isset($treeData['levels'][1][0]))
                        @php $node = $treeData['levels'][1][0]; @endphp
                        @include('binary.partials.node_card', ['node' => $node, 'level' => 1])
                    @endif
                </div>

                <!-- Connector Line Level 1 to Level 2 -->
                <div class="w-full max-w-[480px] flex flex-col items-center -my-4">
                    <div class="w-0.5 h-6 bg-slate-300"></div>
                    <div class="w-full h-0.5 bg-slate-300"></div>
                    <div class="w-full flex justify-between">
                        <div class="w-0.5 h-6 bg-slate-300"></div>
                        <div class="w-0.5 h-6 bg-slate-300"></div>
                    </div>
                </div>

                <!-- LEVEL 2: 2 Nodes (Left and Right) -->
                <div class="grid grid-cols-2 gap-8 md:gap-16 w-full max-w-[750px]">
                    @foreach($treeData['levels'][2] as $index => $node)
                        <div class="flex justify-center">
                            @if($node)
                                @include('binary.partials.node_card', ['node' => $node, 'level' => 2])
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Connector Line Level 2 to Level 3 -->
                <div class="grid grid-cols-2 gap-8 md:gap-16 w-full max-w-[750px] -my-4">
                    <!-- Left Parent Connectors -->
                    <div class="flex flex-col items-center">
                        <div class="w-0.5 h-5 bg-slate-300"></div>
                        <div class="w-full max-w-[200px] h-0.5 bg-slate-300"></div>
                        <div class="w-full max-w-[200px] flex justify-between">
                            <div class="w-0.5 h-5 bg-slate-300"></div>
                            <div class="w-0.5 h-5 bg-slate-300"></div>
                        </div>
                    </div>

                    <!-- Right Parent Connectors -->
                    <div class="flex flex-col items-center">
                        <div class="w-0.5 h-5 bg-slate-300"></div>
                        <div class="w-full max-w-[200px] h-0.5 bg-slate-300"></div>
                        <div class="w-full max-w-[200px] flex justify-between">
                            <div class="w-0.5 h-5 bg-slate-300"></div>
                            <div class="w-0.5 h-5 bg-slate-300"></div>
                        </div>
                    </div>
                </div>

                <!-- LEVEL 3: 4 Nodes (LL, LR, RL, RR) -->
                <div class="grid grid-cols-4 gap-4 md:gap-6 w-full max-w-[960px]">
                    @foreach($treeData['levels'][3] as $node)
                        <div class="flex justify-center">
                            @if($node)
                                @include('binary.partials.node_card', ['node' => $node, 'level' => 3])
                            @else
                                <div class="w-48 h-32 opacity-0 pointer-events-none"></div>
                            @endif
                        </div>
                    @endforeach
                </div>

            @endif

        </div>
    </div>

    <!-- Visual Member Placement Modal -->
    <div x-show="placementModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-cloak>
        <div @click.outside="placementModalOpen = false" 
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>➕</span> নতুন মেম্বার প্লেসমেন্ট করুন
                </h3>
                <button @click="placementModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <!-- Placement Target Indicator -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between text-xs">
                <div>
                    <span class="text-slate-400">আপলাইন প্যারেন্ট:</span>
                    <div class="font-bold text-slate-900" x-text="selectedParentName + ' (' + selectedParentCode + ')'"></div>
                </div>
                <div class="text-right">
                    <span class="text-slate-400">নির্ধারিত পজিশন:</span>
                    <div>
                        <span x-show="selectedPosition === 'left'" class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold">
                            👈 বাম টিম (Left)
                        </span>
                        <span x-show="selectedPosition === 'right'" class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 font-bold">
                            👉 ডান টিম (Right)
                        </span>
                    </div>
                </div>
            </div>

            <form action="{{ route('binary.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="parent_id" :value="selectedParentId">
                <input type="hidden" name="position" :value="selectedPosition">

                <!-- Member Source Mode Switcher -->
                <div class="flex rounded-xl bg-slate-100 p-1 border border-slate-200 text-xs font-semibold">
                    <button type="button" @click="memberMode = 'new'"
                            :class="memberMode === 'new' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="flex-1 py-1.5 rounded-lg transition-all text-center">
                        নতুন মেম্বার তৈরি করুন
                    </button>
                    <button type="button" @click="memberMode = 'existing'"
                            :class="memberMode === 'existing' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="flex-1 py-1.5 rounded-lg transition-all text-center">
                        রেজিস্টার্ড ইউজার থেকে নির্বাচন
                    </button>
                </div>

                <!-- Existing User Dropdown -->
                <div x-show="memberMode === 'existing'" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">ইউজার সিলেক্ট করুন <span class="text-rose-500">*</span></label>
                        <select name="user_id" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="">-- ইউজার বেছে নিন --</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- New Member Form Fields -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">মেম্বারের পূর্ণ নাম <span class="text-rose-500">*</span></label>
                        <input type="text" name="member_name" required placeholder="e.g. Shakil Mahmud" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">মোবাইল নম্বর</label>
                            <input type="text" name="phone" placeholder="017xxxxxxxx" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">ইমেইল (ঐচ্ছিক)</label>
                            <input type="email" name="email" placeholder="member@example.com" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Package & Point Value (BV) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">প্যাকেজ ও পয়েন্ট ভলিউম (BV) <span class="text-rose-500">*</span></label>
                    <select name="package_name" required class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none font-semibold">
                        @foreach($packages as $pkg)
                        <option value="{{ $pkg['name'] }}">{{ $pkg['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sponsor / Direct Referral -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">ডাইরেক্ট স্পন্সর</label>
                        <select name="sponsor_id" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500">
                            @foreach($allNodes as $n)
                            <option value="{{ $n->id }}">{{ $n->member_name }} ({{ $n->member_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">পদবী (Initial Rank)</label>
                        <select name="rank_name" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500">
                            <option value="Member">Member</option>
                            <option value="Silver Member">Silver Member</option>
                            <option value="Gold Member">Gold Member</option>
                            <option value="Platinum Leader">Platinum Leader</option>
                        </select>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="placementModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 active:scale-95 text-white text-sm font-bold rounded-xl shadow-xs transition-colors flex items-center gap-2">
                        <span>Save Placement</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
