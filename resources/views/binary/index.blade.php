@extends('layouts.app')

@section('page-title', 'Binary Team Engine')
@section('page-subtitle', 'Visual Dual-Team Genealogy Tree, Member Directory & Placement Volume')

@section('content')
<div class="space-y-6" x-data="{
    viewMode: '{{ $viewMode }}',
    placementModalOpen: false,
    editModalOpen: false,
    editNode: {
        id: null,
        member_name: '',
        member_code: '',
        phone: '',
        email: '',
        password_plain: 'sbl123456',
        tpin: '1234',
        package_name: 'National 120k',
        point_value: 100,
        contributions: [],
        rank_name: 'Member',
        sponsor_id: '',
        left_count: 0,
        left_target_count: 0,
        right_count: 0,
        right_target_count: 0,
        left_bv: 0,
        right_bv: 0,
        is_active: true,
        user_id: ''
    },
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
    },
    addContributionRow() {
        if (!this.editNode.contributions) this.editNode.contributions = [];
        this.editNode.contributions.push({
            amount: 100,
            date: new Date().toISOString().slice(0, 10),
            note: 'Contribution'
        });
        this.recalcTotalContribution();
    },
    removeContributionRow(index) {
        this.editNode.contributions.splice(index, 1);
        this.recalcTotalContribution();
    },
    recalcTotalContribution() {
        let sum = 0;
        if (this.editNode.contributions && this.editNode.contributions.length > 0) {
            this.editNode.contributions.forEach(c => {
                sum += parseFloat(c.amount || 0);
            });
            this.editNode.point_value = sum;
        }
    },
    openEditModal(node) {
        let contribs = [];
        if (node.contributions) {
            contribs = typeof node.contributions === 'string' ? JSON.parse(node.contributions) : node.contributions;
        }
        if (!contribs || contribs.length === 0) {
            contribs = [
                { amount: node.point_value || 0, date: new Date().toISOString().slice(0, 10), note: node.package_name || 'Initial' }
            ];
        }

        this.editNode = {
            id: node.id,
            member_name: node.member_name || '',
            member_code: node.member_code || '',
            phone: node.phone || '',
            email: node.email || '',
            password_plain: node.password_plain || 'sbl123456',
            tpin: node.tpin || '1234',
            package_name: node.package_name || 'National 120k',
            point_value: node.point_value !== undefined ? node.point_value : 100,
            contributions: contribs,
            rank_name: node.rank_name || 'Member',
            sponsor_id: node.sponsor_id || '',
            left_count: node.left_count !== undefined ? node.left_count : 0,
            left_target_count: node.left_target_count !== undefined ? node.left_target_count : (node.left_count || 0),
            right_count: node.right_count !== undefined ? node.right_count : 0,
            right_target_count: node.right_target_count !== undefined ? node.right_target_count : (node.right_count || 0),
            left_bv: node.left_bv !== undefined ? node.left_bv : 0,
            right_bv: node.right_bv !== undefined ? node.right_bv : 0,
            is_active: node.is_active !== undefined ? Boolean(node.is_active) : true,
            user_id: node.user_id || ''
        };
        this.editModalOpen = true;
        this.$nextTick(() => {
            const form = document.querySelector('#edit-member-form');
            if (form && node.id) {
                form.action = '/binary/' + node.id;
            }
        });
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

        <!-- View Switcher & Fast Navigation Actions -->
        <div class="flex flex-col sm:flex-row items-end sm:items-center gap-3 flex-shrink-0">
            <!-- View Mode Switcher -->
            <div class="inline-flex rounded-xl bg-slate-800/90 p-1 border border-slate-700 shadow-xs">
                <a href="{{ route('binary.index', ['view' => 'tree', 'node_id' => request('node_id')]) }}" 
                   class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $viewMode !== 'table' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white' }}">
                    🌲 Tree View
                </a>
                <a href="{{ route('binary.index', ['view' => 'table']) }}" 
                   class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $viewMode === 'table' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white' }}">
                    📋 Member Directory
                </a>
            </div>

            @if($viewMode !== 'table')
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('binary.index') }}" 
                   class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition-colors flex items-center gap-1" title="টপ রুট মেম্বারে ফিরুন">
                    <span>🏠</span> Top
                </a>

                @if($treeData['root'] && $treeData['root']->parent_id)
                <a href="{{ route('binary.index', ['node_id' => $treeData['root']->parent_id]) }}" 
                   class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition-colors flex items-center gap-1" title="এক ধাপ আপলাইনে যান">
                    <span>⬆️</span> Up
                </a>
                @endif

                @if($treeData['root'])
                <a href="{{ route('binary.extreme', ['node' => $treeData['root']->id, 'direction' => 'left']) }}" 
                   class="px-2.5 py-1.5 bg-emerald-950/80 hover:bg-emerald-900 text-emerald-300 border border-emerald-800/80 text-xs font-semibold rounded-xl transition-colors flex items-center gap-1" title="বাম পাশের শেষ প্রান্তে যান">
                    <span>◀️</span> Left
                </a>
                <a href="{{ route('binary.extreme', ['node' => $treeData['root']->id, 'direction' => 'right']) }}" 
                   class="px-2.5 py-1.5 bg-blue-950/80 hover:bg-blue-900 text-blue-300 border border-blue-800/80 text-xs font-semibold rounded-xl transition-colors flex items-center gap-1" title="ডান পাশের শেষ প্রান্তে যান">
                    <span>Right</span> <span>▶️</span>
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>

    @if($viewMode === 'table')
    <!-- ==================== MEMBER DIRECTORY TABLE VIEW ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden space-y-4 p-5">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">টিম মেম্বার তালিকা (Member Directory)</h3>
                <p class="text-xs text-slate-500">বাইনারি নেটওয়ার্কের সকল সক্রিয় মেম্বারের বিবরণ, পজিশন ও ডাউনলাইন ভলিউম।</p>
            </div>

            <!-- Search Form -->
            <form action="{{ route('binary.index') }}" method="GET" class="w-full sm:w-80 flex items-center gap-2">
                <input type="hidden" name="view" value="table">
                <div class="relative flex-1">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="নাম, কোড বা মোবাইল নম্বর..." 
                           class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-colors">
                    Search
                </button>
                @if(request('search'))
                <a href="{{ route('binary.index', ['view' => 'table']) }}" class="text-xs text-slate-400 hover:text-slate-600">Clear</a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-y border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">মেম্বার</th>
                        <th class="py-3 px-4">প্লেসমেন্ট (Upline)</th>
                        <th class="py-3 px-4">প্যাকেজ ও পদবী</th>
                        <th class="py-3 px-4 text-center">বাম টিম (Left)</th>
                        <th class="py-3 px-4 text-center">ডান টিম (Right)</th>
                        <th class="py-3 px-4 text-center">ম্যাচিং পেয়ার</th>
                        <th class="py-3 px-4 text-center">স্ট্যাটাস</th>
                        <th class="py-3 px-4 text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody data-binary-table-body class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($members as $member)
                    <tr data-node-id="{{ $member->id }}" class="hover:bg-slate-50/60 transition-colors">
                        <!-- Member Profile -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-900 to-slate-800 text-orange-400 font-bold flex items-center justify-center text-xs flex-shrink-0 shadow-xs">
                                    {{ substr($member->member_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 hover:text-orange-600 transition-colors">{{ $member->member_name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $member->member_code }} @if($member->phone) • {{ $member->phone }} @endif</div>
                                </div>
                            </div>
                        </td>

                        <!-- Placement Upline -->
                        <td class="py-3.5 px-4">
                            @if($member->parent)
                                <div class="font-semibold text-slate-800">{{ $member->parent->member_name }}</div>
                                <div class="text-[11px]">
                                    <span class="px-1.5 py-0.5 rounded font-bold {{ $member->position === 'left' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                        {{ $member->position === 'left' ? '👈 Left Team' : '👉 Right Team' }}
                                    </span>
                                </div>
                            @else
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold text-[11px]">Top Root</span>
                            @endif
                        </td>

                        <!-- Package & Rank -->
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-slate-900">{{ $member->package_name }}</div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-200">
                                {{ $member->rank_name }}
                            </span>
                        </td>

                        <!-- Left Team Count & BV -->
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-emerald-700">{{ $member->left_count }} জন</span>
                            <div class="text-[11px] text-slate-500">{{ (int)$member->left_bv }} BV</div>
                        </td>

                        <!-- Right Team Count & BV -->
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-blue-700">{{ $member->right_count }} জন</span>
                            <div class="text-[11px] text-slate-500">{{ (int)$member->right_bv }} BV</div>
                        </td>

                        <!-- Matched Pairs -->
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-extrabold text-purple-700">{{ $member->matched_pairs }}</span>
                            <div class="text-[10px] text-slate-400">Pairs</div>
                        </td>

                        <!-- Status -->
                        <td class="py-3.5 px-4 text-center">
                            @if($member->is_active)
                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-200">Active</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-bold text-[10px]">Inactive</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('binary.index', ['node_id' => $member->id]) }}" 
                                   class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" 
                                   title="ট্রি ভিউতে দেখুন">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>

                                <button type="button" 
                                        @click="openEditModal({
                                            id: {{ $member->id }},
                                            member_name: '{{ addslashes($member->member_name) }}',
                                            member_code: '{{ $member->member_code }}',
                                            phone: '{{ $member->phone }}',
                                            email: '{{ $member->email }}',
                                            package_name: '{{ $member->package_name }}',
                                            rank_name: '{{ $member->rank_name }}',
                                            is_active: {{ $member->is_active ? 'true' : 'false' }},
                                            user_id: '{{ $member->user_id }}'
                                        })"
                                        class="p-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 transition-colors" 
                                        title="মেম্বার এডিট করুন">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>

                                @if($member->children->isEmpty())
                                <form action="{{ route('binary.destroy', $member->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই মেম্বারকে ({{ $member->member_name }}) রিমুভ করতে চান?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors" title="মেম্বার রিমুভ করুন">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                                @else
                                <button type="button" 
                                        onclick="alert('এই মেম্বারের ডাউনলাইনে সক্রিয় মেম্বার রয়েছে। ট্রি অখণ্ড রাখতে ডাউনলাইন থাকা অবস্থায় ডিলিট করা সম্ভব নয়।')"
                                        class="p-1.5 rounded-lg bg-slate-100 text-slate-400 cursor-not-allowed" 
                                        title="ডাউনলাইন থাকায় ডিলিট সম্ভব নয়">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-400">
                            কোনো টিম মেম্বার পাওয়া যায়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($members->hasPages())
        <div class="pt-4 border-t border-slate-100">
            {{ $members->links() }}
        </div>
        @endif
    </div>

    @else
    <!-- ==================== VISUAL GENEALOGY TREE VIEW ==================== -->
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

            <!-- Current Root Tag & Quick Placement -->
            <div class="flex items-center gap-2">
                @if($treeData['root'])
                <div class="text-xs font-medium text-slate-600 flex items-center gap-2">
                    <span class="text-slate-400">বর্তমান ফোকাস:</span>
                    <span class="px-2.5 py-1 rounded-lg bg-orange-50 text-orange-700 font-bold border border-orange-200">
                        {{ $treeData['root']->member_name }} ({{ $treeData['root']->member_code }})
                    </span>
                </div>
                @endif
            </div>
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
    <div class="bg-[#203648] rounded-2xl border border-slate-700/80 p-6 md:p-10 shadow-xl overflow-x-auto min-h-[650px]">
        <div class="min-w-[850px] mx-auto flex flex-col items-center">
            
            @if(empty($treeData['levels']))
                <div class="text-center py-20 text-white">
                    <div class="text-5xl mb-3">🌲</div>
                    <h3 class="text-lg font-bold">কোনো টিম ডাটা নেই</h3>
                    <p class="text-xs text-slate-300">দয়া করে ডাটাবেজ সিড করুন অথবা নতুন রুট মেম্বার যুক্ত করুন।</p>
                </div>
            @else

                <!-- LEVEL 1: Root Node Container with Attached Downward Stem -->
                <div class="flex flex-col items-center w-full">
                    @if(isset($treeData['levels'][1][0]))
                        @php $node = $treeData['levels'][1][0]; @endphp
                        @include('binary.partials.node_card', ['node' => $node, 'level' => 1])

                        @if(!empty($treeData['levels'][2]))
                        <!-- Seamless Connector: Level 1 to Level 2 -->
                        <div class="w-full flex flex-col items-center">
                            <!-- Vertical stem touching parent bottom -->
                            <div class="w-[2px] h-8 bg-white/75"></div>
                            <!-- Horizontal branch spanning between centers of Left and Right Level 2 children -->
                            <div class="w-[480px] md:w-[520px] h-[2px] bg-white/75 rounded-full"></div>
                            <!-- Two vertical stems touching child tops -->
                            <div class="w-[480px] md:w-[520px] flex justify-between">
                                <div class="w-[2px] h-8 bg-white/75"></div>
                                <div class="w-[2px] h-8 bg-white/75"></div>
                            </div>
                        </div>
                        @endif
                    @endif
                </div>

                <!-- LEVEL 2: 2 Nodes (Left and Right) with Attached Connectors to Level 3 -->
                <div class="grid grid-cols-2 gap-12 md:gap-20 w-full max-w-[840px] justify-items-center">
                    @foreach($treeData['levels'][2] as $index => $node)
                        <div class="flex flex-col items-center w-full">
                            @if($node)
                                @include('binary.partials.node_card', ['node' => $node, 'level' => 2])

                                @if(!$node['is_vacant'] && !empty($treeData['levels'][3]))
                                <!-- Seamless Connector: Level 2 to Level 3 (under this parent) -->
                                <div class="w-full flex flex-col items-center">
                                    <div class="w-[2px] h-8 bg-white/75"></div>
                                    <div class="w-[260px] md:w-[280px] h-[2px] bg-white/75 rounded-full"></div>
                                    <div class="w-[260px] md:w-[280px] flex justify-between">
                                        <div class="w-[2px] h-8 bg-white/75"></div>
                                        <div class="w-[2px] h-8 bg-white/75"></div>
                                    </div>
                                </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- LEVEL 3: 4 Nodes (LL, LR, RL, RR) -->
                <div class="grid grid-cols-4 gap-3 md:gap-4 w-full max-w-[1160px] justify-items-center">
                    @foreach($treeData['levels'][3] as $index => $node)
                        <div class="flex justify-center w-full">
                            @if($node)
                                @include('binary.partials.node_card', ['node' => $node, 'level' => 3])
                            @endif
                        </div>
                    @endforeach
                </div>

            @endif

        </div>
    </div>
    @endif

    <!-- ==================== 1. PLACEMENT MODAL (+ ADD MEMBER) ==================== -->
    <div x-show="placementModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="placementModalOpen = false" 
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5">
            
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
                            <option value="{{ $user->id }}" data-user-id="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
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

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">লগইন পাসওয়ার্ড</label>
                            <input type="text" name="password_plain" value="sbl123456" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">TPIN (ট্রানজেকশন পিন)</label>
                            <input type="text" name="tpin" value="1234" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none font-mono">
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
                            <option value="{{ $n->id }}" data-node-id="{{ $n->id }}">{{ $n->member_name }} ({{ $n->member_code }})</option>
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

    <!-- ==================== 2. EDIT MEMBER MODAL ==================== -->
    <div x-show="editModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto"
         x-cloak>
        <div @click.outside="editModalOpen = false" 
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5 my-8">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>✏️</span> মেম্বার প্রোফাইল এডিট করুন
                </h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form id="edit-member-form" :action="'{{ url('/binary') }}/' + editNode.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="contributions" :value="JSON.stringify(editNode.contributions)">

                <!-- Member Name & Username -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">পূর্ণ নাম <span class="text-rose-500">*</span></label>
                        <input type="text" name="member_name" x-model="editNode.member_name" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">ইউজারনেম / কোড</label>
                        <input type="text" name="member_code" x-model="editNode.member_code" placeholder="@username" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none font-mono">
                    </div>
                </div>

                <!-- Email & Phone -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">ইমেইল</label>
                        <input type="email" name="email" x-model="editNode.email" placeholder="tahmina@example.com" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">মোবাইল নম্বর</label>
                        <input type="text" name="phone" x-model="editNode.phone" placeholder="017xxxxxxxx" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <!-- Password & TPIN -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">পাসওয়ার্ড (Password)</label>
                        <input type="text" name="password_plain" x-model="editNode.password_plain" placeholder="Password" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">TPIN (পিন কোড)</label>
                        <input type="text" name="tpin" x-model="editNode.tpin" placeholder="1234" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 font-mono">
                    </div>
                </div>

                <!-- Sponsor (By) & Rank -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">স্পন্সর (By)</label>
                        <select name="sponsor_id" x-model="editNode.sponsor_id" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500">
                            <option value="">-- কোনো স্পন্সর নয় --</option>
                            @foreach($allNodes as $n)
                            <option value="{{ $n->id }}">{{ $n->member_name }} ({{ $n->member_code }})</option>
                            <option value="{{ $n->id }}" data-node-id="{{ $n->id }}">{{ $n->member_name }} ({{ $n->member_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">পদবী (Rank)</label>
                        <input type="text" name="rank_name" x-model="editNode.rank_name" placeholder="Member / Silver" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                <!-- Package & Multiple Contributions Manager -->
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">কন্ট্রিবিউশন হিস্ট্রি (Contributions)</span>
                            <p class="text-[10px] text-slate-500">একাধিক কন্ট্রিবিউশন/টপ-আপ যোগ করুন।</p>
                        </div>
                        <button type="button" @click="addContributionRow()" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold flex items-center gap-1 transition-colors">
                            <span>+ Add</span>
                        </button>
                    </div>

                    <!-- Dynamic Contributions List -->
                    <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                        <template x-for="(c, idx) in editNode.contributions" :key="idx">
                            <div class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-200 text-xs shadow-2xs">
                                <div class="w-24">
                                    <label class="text-[9px] text-slate-400 block">Amount ($)</label>
                                    <input type="number" step="1" min="0" x-model="c.amount" @input="recalcTotalContribution()" class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded text-xs font-bold text-emerald-700">
                                </div>
                                <div class="w-28">
                                    <label class="text-[9px] text-slate-400 block">Date</label>
                                    <input type="date" x-model="c.date" class="w-full px-1.5 py-1 bg-slate-50 border border-slate-200 rounded text-[11px]">
                                </div>
                                <div class="flex-1">
                                    <label class="text-[9px] text-slate-400 block">Note / Package</label>
                                    <input type="text" x-model="c.note" placeholder="Top-up Note" class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded text-xs">
                                </div>
                                <button type="button" @click="removeContributionRow(idx)" class="text-rose-500 hover:text-rose-700 text-sm font-bold pt-3 px-1" title="Remove row">&times;</button>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-xs">
                        <span class="font-bold text-slate-700">Total Contribution ($/BV):</span>
                        <div class="flex items-center gap-2">
                            <input type="number" step="1" min="0" name="point_value" x-model="editNode.point_value" class="w-24 px-2 py-1 text-xs bg-white border border-slate-300 rounded font-extrabold text-emerald-700 text-right">
                            <span class="font-extrabold text-emerald-800">$</span>
                        </div>
                    </div>
                </div>

                <!-- Team & Volume Counters (Left & Right) with Configurable X/Y Targets -->
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80 space-y-2.5">
                    <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block">বাইনারি টিম ও ভলিউম কাউন্টার (কনফিগারেবল)</span>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5 p-2 bg-emerald-50/50 rounded-lg border border-emerald-200/60">
                            <span class="text-[10px] font-bold text-emerald-800 uppercase block">👈 Left Team (Active / Target)</span>
                            <div class="grid grid-cols-3 gap-1">
                                <div>
                                    <label class="text-[8px] text-slate-500 block font-medium">Active (X)</label>
                                    <input type="number" min="0" name="left_count" x-model="editNode.left_count" class="w-full px-1.5 py-1 text-xs bg-white border border-slate-200 rounded font-bold text-emerald-700">
                                </div>
                                <div>
                                    <label class="text-[8px] text-slate-500 block font-medium">Target (Y)</label>
                                    <input type="number" min="0" name="left_target_count" x-model="editNode.left_target_count" class="w-full px-1.5 py-1 text-xs bg-white border border-slate-200 rounded font-bold text-emerald-700">
                                </div>
                                <div>
                                    <label class="text-[8px] text-slate-500 block font-medium">Vol ($)</label>
                                    <input type="number" min="0" step="1" name="left_bv" x-model="editNode.left_bv" class="w-full px-1.5 py-1 text-xs bg-white border border-slate-200 rounded font-bold text-emerald-700">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5 p-2 bg-blue-50/50 rounded-lg border border-blue-200/60">
                            <span class="text-[10px] font-bold text-blue-800 uppercase block">👉 Right Team (Active / Target)</span>
                            <div class="grid grid-cols-3 gap-1">
                                <div>
                                    <label class="text-[8px] text-slate-500 block font-medium">Active (X)</label>
                                    <input type="number" min="0" name="right_count" x-model="editNode.right_count" class="w-full px-1.5 py-1 text-xs bg-white border border-slate-200 rounded font-bold text-blue-700">
                                </div>
                                <div>
                                    <label class="text-[8px] text-slate-500 block font-medium">Target (Y)</label>
                                    <input type="number" min="0" name="right_target_count" x-model="editNode.right_target_count" class="w-full px-1.5 py-1 text-xs bg-white border border-slate-200 rounded font-bold text-blue-700">
                                </div>
                                <div>
                                    <label class="text-[8px] text-slate-500 block font-medium">Vol ($)</label>
                                    <input type="number" min="0" step="1" name="right_bv" x-model="editNode.right_bv" class="w-full px-1.5 py-1 text-xs bg-white border border-slate-200 rounded font-bold text-blue-700">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Link & Status -->
                <div class="grid grid-cols-2 gap-3 items-center">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">লিংকড ইউজার</label>
                        <select name="user_id" x-model="editNode.user_id" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500">
                            <option value="">-- কোনো ইউজার নয় --</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            <option value="{{ $user->id }}" data-user-id="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-4">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input type="checkbox" name="is_active" value="1" x-model="editNode.is_active" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500 w-4 h-4">
                            <span>অ্যাকাউন্ট সক্রিয় (Active)</span>
                        </label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 active:scale-95 text-white text-sm font-bold rounded-xl shadow-xs transition-colors flex items-center gap-2">
                        <span>Save Changes</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
