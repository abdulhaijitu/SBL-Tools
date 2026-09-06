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
        sponsor_name: '',
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
    expandedNodeIds: {{ json_encode($treeData['all_node_ids'] ?? []) }},
    zoomLevel: 1.0,
    panX: 0,
    panY: 0,
    isPanning: false,
    panStartX: 0,
    panStartY: 0,
    hasDragged: false,
    init() {
        this.$nextTick(() => {
            this.fitToScreen();
        });
    },
    startPan(e) {
        if (e.button !== 0) return;
        const tag = e.target.tagName ? e.target.tagName.toLowerCase() : '';
        if (['input', 'select', 'textarea', 'button', 'a'].includes(tag) || e.target.closest('button, a, input, select, textarea, [data-prevent-drag]')) {
            return;
        }
        this.isPanning = true;
        this.hasDragged = false;
        this.panStartX = e.clientX - this.panX;
        this.panStartY = e.clientY - this.panY;
    },
    onPan(e) {
        if (!this.isPanning) return;
        const newX = e.clientX - this.panStartX;
        const newY = e.clientY - this.panStartY;
        if (Math.abs(newX - this.panX) > 2 || Math.abs(newY - this.panY) > 2) {
            this.hasDragged = true;
        }
        this.panX = newX;
        this.panY = newY;
    },
    endPan() {
        this.isPanning = false;
    },
    fitToScreen() {
        const container = document.querySelector('#tree-viewport-container');
        const content = document.querySelector('#tree-content-root');
        if (container && content) {
            const containerWidth = container.clientWidth;
            const contentWidth = content.scrollWidth || content.clientWidth;
            if (contentWidth > 0 && containerWidth > 0 && contentWidth > containerWidth) {
                const ratio = (containerWidth - 48) / contentWidth;
                this.zoomLevel = Math.max(0.45, Math.min(1.0, Math.round(ratio * 100) / 100));
            } else {
                this.zoomLevel = 1.0;
            }
        } else {
            this.zoomLevel = 0.9;
        }
        this.panX = 0;
        this.panY = 10;
    },
    centerRoot() {
        this.zoomLevel = 1.0;
        this.panX = 0;
        this.panY = 10;
    },
    resetView() {
        this.zoomLevel = 1.0;
        this.panX = 0;
        this.panY = 0;
    },
    zoomIn() {
        this.zoomLevel = Math.min(1.8, Math.round((this.zoomLevel + 0.15) * 100) / 100);
    },
    zoomOut() {
        this.zoomLevel = Math.max(0.35, Math.round((this.zoomLevel - 0.15) * 100) / 100);
    },
    handleWheel(e) {
        if (e.ctrlKey || e.metaKey) {
            e.preventDefault();
            if (e.deltaY < 0) {
                this.zoomIn();
            } else {
                this.zoomOut();
            }
        }
    },
    isExpanded(id) {
        return this.expandedNodeIds.includes(Number(id));
    },
    toggleExpanded(id) {
        id = Number(id);
        if (this.expandedNodeIds.includes(id)) {
            this.expandedNodeIds = this.expandedNodeIds.filter(i => i !== id);
        } else {
            this.expandedNodeIds.push(id);
        }
    },
    expandAll() {
        this.expandedNodeIds = {{ json_encode($treeData['all_node_ids'] ?? []) }};
    },
    collapseAll() {
        this.expandedNodeIds = [{{ $treeData['root'] ? $treeData['root']->id : 0 }}];
    },
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
                { amount: node.total_investment || node.point_value || 0, date: new Date().toISOString().slice(0, 10), note: node.package_name || 'Initial' }
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
            sponsor_name: node.sponsor_name || '',
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

    <!-- ==================== 1. TEAM PAGE HEADER & SUMMARY BAR ==================== -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 rounded-2xl p-5 md:p-6 border border-slate-800 text-white shadow-xl space-y-4">
        <!-- Top Row: Title, Root, Sponsor & View Switcher -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-4 border-b border-slate-800">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <span class="text-orange-400 font-black text-sm uppercase tracking-wider flex items-center gap-1.5">
                        <span>🌲</span> Binary Team Tree
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-400/20 text-amber-300 border border-amber-400/40">
                        Rank: {{ $treeData['stats']['rank_name'] ?? 'FME' }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400 text-xs font-medium">Root:</span>
                        <strong class="text-white text-base font-bold">{{ $treeData['stats']['root_name'] ?? 'Md. Abdul Hai' }}</strong>
                        <span class="text-xs text-slate-400 font-mono">({{ $treeData['stats']['root_code'] ?? 'mdabdulhaijitu1' }})</span>
                    </div>
                    <span class="text-slate-600 hidden sm:inline">•</span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400 text-xs font-medium">Sponsor/Upline:</span>
                        <span class="text-orange-300 font-bold text-xs">{{ $treeData['stats']['sponsor_name'] ?? 'Md. Samim' }}</span>
                    </div>
                </div>
            </div>

            <!-- Fast Navigation & View Mode Switcher -->
            <div class="flex flex-wrap items-center gap-2">
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

                <!-- Back to Main Root (Md. Abdul Hai) -->
                <a href="{{ route('binary.index') }}" 
                   class="px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 shadow-sm active:scale-95" 
                   title="প্রধান রুট (Md. Abdul Hai)-এ ফিরে যান">
                    <span>🏠</span> Back to Main Root
                </a>

                @if($treeData['root'] && $treeData['root']->parent_id)
                <a href="{{ route('binary.index', ['node_id' => $treeData['root']->parent_id]) }}" 
                   class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition-all flex items-center gap-1.5 shadow-sm active:scale-95" 
                   title="এক ধাপ আপলাইনে যান">
                    <span>⬆️</span> Go to Parent
                </a>
                @endif
            </div>
        </div>

        <!-- Breadcrumb Bar if drilled down -->
        @if(!empty($treeData['breadcrumbs']) && count($treeData['breadcrumbs']) > 1)
        <div class="flex items-center gap-2 text-xs bg-slate-950/70 px-4 py-2 rounded-xl border border-slate-800 text-slate-300 overflow-x-auto">
            <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Breadcrumb:</span>
            @foreach($treeData['breadcrumbs'] as $idx => $bc)
                @if($idx > 0)
                <span class="text-slate-600 font-bold">›</span>
                @endif
                @if($bc['is_current'])
                <span class="text-amber-300 font-bold whitespace-nowrap">{{ $bc['name'] }}</span>
                @else
                <a href="{{ route('binary.index', ['node_id' => $bc['id']]) }}" class="text-slate-300 hover:text-orange-400 transition-colors whitespace-nowrap font-medium">{{ $bc['name'] }}</a>
                @endif
            @endforeach
        </div>
        @endif

        <!-- Bottom Row: Dynamic Summary Counters -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
            <!-- Left Team -->
            <div class="p-3.5 bg-slate-950/70 rounded-xl border border-emerald-500/30 space-y-1">
                <div class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider flex items-center justify-between">
                    <span>👈 Left Team</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-xl font-black text-white">
                    {{ $treeData['stats']['left_count'] }} <span class="text-xs font-normal text-slate-400">জন</span>
                </div>
                <div class="text-xs text-emerald-300 font-semibold truncate">
                    Vol: <span x-text="$store.currency ? $store.currency.format({{ (float)$treeData['stats']['left_investment_volume'] }}) : '{{ \App\Services\CurrencyService::format((float)$treeData['stats']['left_investment_volume']) }}'">{{ \App\Services\CurrencyService::format((float)$treeData['stats']['left_investment_volume']) }}</span> ({{ number_format($treeData['stats']['left_bv'], 0) }} BV)
                </div>
            </div>

            <!-- Right Team -->
            <div class="p-3.5 bg-slate-950/70 rounded-xl border border-blue-500/30 space-y-1">
                <div class="text-[10px] font-bold text-blue-400 uppercase tracking-wider flex items-center justify-between">
                    <span>👉 Right Team</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <div class="text-xl font-black text-white">
                    {{ $treeData['stats']['right_count'] }} <span class="text-xs font-normal text-slate-400">জন</span>
                </div>
                <div class="text-xs text-blue-300 font-semibold truncate">
                    Vol: <span x-text="$store.currency ? $store.currency.format({{ (float)$treeData['stats']['right_investment_volume'] }}) : '{{ \App\Services\CurrencyService::format((float)$treeData['stats']['right_investment_volume']) }}'">{{ \App\Services\CurrencyService::format((float)$treeData['stats']['right_investment_volume']) }}</span> ({{ number_format($treeData['stats']['right_bv'], 0) }} BV)
                </div>
            </div>

            <!-- Total Team -->
            <div class="p-3.5 bg-slate-950/70 rounded-xl border border-purple-500/30 space-y-1">
                <div class="text-[10px] font-bold text-purple-400 uppercase tracking-wider flex items-center justify-between">
                    <span>👥 Total Team</span>
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                </div>
                <div class="text-xl font-black text-white">
                    {{ $treeData['stats']['total_members'] }} <span class="text-xs font-normal text-slate-400">জন</span>
                </div>
                <div class="text-xs text-purple-300 font-semibold truncate">
                    Pairs: <strong>{{ $treeData['stats']['matched_pairs'] }}</strong> ({{ number_format($treeData['stats']['matched_pairs'] * 100, 0) }} BV)
                </div>
            </div>

            <!-- Own Investment -->
            <div class="p-3.5 bg-slate-950/70 rounded-xl border border-amber-500/30 space-y-1">
                <div class="text-[10px] font-bold text-amber-400 uppercase tracking-wider flex items-center justify-between">
                    <span>💼 Own Investment</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-xl font-black text-amber-300 truncate">
                    <span x-text="$store.currency ? $store.currency.format({{ (float)$treeData['stats']['own_investment'] }}) : '{{ \App\Services\CurrencyService::format((float)$treeData['stats']['own_investment']) }}'">{{ \App\Services\CurrencyService::format((float)$treeData['stats']['own_investment']) }}</span>
                </div>
                <div class="text-[11px] text-slate-400 truncate">
                    Direct qualified volume
                </div>
            </div>
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
                            <div class="text-[11px] text-slate-500 font-medium">@currency($member->left_bv) ({{ (int)$member->left_bv }} BV)</div>
                        </td>

                        <!-- Right Team Count & BV -->
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-blue-700">{{ $member->right_count }} জন</span>
                            <div class="text-[11px] text-slate-500 font-medium">@currency($member->right_bv) ({{ (int)$member->right_bv }} BV)</div>
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
                                            password_plain: '{{ addslashes($member->password_plain ?? "sbl123456") }}',
                                            tpin: '{{ $member->tpin ?? "1234" }}',
                                            package_name: '{{ $member->package_name }}',
                                            point_value: {{ (float)$member->point_value }},
                                            contributions: {{ json_encode($member->contributions ?: [['amount' => (float)$member->point_value, 'date' => now()->toDateString(), 'note' => $member->package_name]]) }},
                                            rank_name: '{{ $member->rank_name }}',
                                            sponsor_id: '{{ $member->sponsor_id }}',
                                            sponsor_name: '{{ addslashes($member->sponsor_name ?: ($member->sponsor?->member_name ?? ($member->parent_id === null ? "Md. Samim" : "Md. Abdul Hai"))) }}',
                                            left_count: {{ $member->left_count }},
                                            left_target_count: {{ $member->left_target_count ?: $member->left_count }},
                                            right_count: {{ $member->right_count }},
                                            right_target_count: {{ $member->right_target_count ?: $member->right_count }},
                                            left_bv: {{ (float)$member->left_bv }},
                                            right_bv: {{ (float)$member->right_bv }},
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
                                <form action="{{ route('binary.destroy', $member->id) }}" method="POST" onsubmit="return confirm('এই মেম্বারের ({{ $member->member_name }}) ডাউনলাইনে টিম মেম্বার রয়েছে। আপনি কি এই মেম্বারসহ তার পুরো ডাউনলাইন মুছে ফেলতে চান?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="cascade" value="1">
                                    <button type="submit" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors" title="মেম্বার ও ডাউনলাইন রিমুভ করুন">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
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
    <!-- ==================== 2. SEARCH & CONTROLS TOOLBAR ==================== -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Search Member Form -->
        <form action="{{ route('binary.search') }}" method="GET" class="w-full sm:w-96 flex items-center gap-2">
            <div class="relative flex-1">
                <input type="text" 
                       name="search" 
                       list="tree_search_datalist"
                       placeholder="মেম্বারের নাম বা কোড দিয়ে সার্চ করুন (e.g. SBL-1001)..." 
                       class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all font-medium">
                <datalist id="tree_search_datalist">
                    @foreach($allNodes as $an)
                    <option value="{{ $an->member_code }}">{{ $an->member_name }} ({{ $an->member_code }})</option>
                    @endforeach
                </datalist>
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors shrink-0">
                Search Member
            </button>
        </form>

        <!-- Current View Root Badge & Extreme Navigation -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="text-xs font-semibold text-slate-600 flex items-center gap-1.5">
                <span class="text-slate-400 text-[11px]">View Root:</span>
                <span class="px-2.5 py-1 rounded-lg bg-orange-50 text-orange-700 font-bold border border-orange-200">
                    {{ $treeData['root'] ? $treeData['root']->member_name : 'No Root' }}
                </span>
            </div>

            @if($treeData['root'])
            <div class="inline-flex items-center gap-1 pl-2 border-l border-slate-200">
                <a href="{{ route('binary.extreme', ['node' => $treeData['root']->id, 'direction' => 'left']) }}" 
                   class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-[11px] font-bold rounded-lg border border-emerald-200 transition-colors" title="Go to Extreme Left">
                    ◀ Left End
                </a>
                <a href="{{ route('binary.extreme', ['node' => $treeData['root']->id, 'direction' => 'right']) }}" 
                   class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-[11px] font-bold rounded-lg border border-blue-200 transition-colors" title="Go to Extreme Right">
                    Right End ▶
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- ==================== 3. FIGJAM-STYLE INTERACTIVE GENEALOGY TREE CANVAS ==================== -->
    <div class="bg-[#1b2b3a] rounded-2xl border border-slate-700/80 p-4 md:p-6 shadow-2xl relative select-none"
         @wheel="handleWheel($event)">
        
        <!-- Canvas Floating Top Toolbar with All 5 Required Controls -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-700/60 text-white">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                    <span>🎨</span> Binary Tree Canvas
                </span>
                <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-orange-500/20 text-orange-300 border border-orange-500/30 font-medium flex items-center gap-1">
                    <span>🖱️</span> মাউস ড্র্যাগ করে ক্যানভাস সরান
                </span>
            </div>

            <!-- Viewport & Zoom Controls (Fit to Screen, Center Root, Zoom In/Out, Reset) -->
            <div class="flex flex-wrap items-center gap-1.5">
                <!-- 1. Fit to Screen -->
                <button type="button" 
                        @click="fitToScreen()"
                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-600 transition-all flex items-center gap-1 shadow-sm active:scale-95"
                        title="সম্পূর্ণ ট্রি স্ক্রিনে ফিট করুন">
                    <span>🔍</span> Fit to Screen
                </button>

                <!-- 2. Center Root -->
                <button type="button" 
                        @click="centerRoot()"
                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-600 transition-all flex items-center gap-1 shadow-sm active:scale-95"
                        title="রুট মেম্বারকে সেন্টারে নিয়ে আসুন">
                    <span>🎯</span> Center Root
                </button>

                <!-- 3. Reset View -->
                <button type="button" 
                        @click="resetView()"
                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-600 transition-all flex items-center gap-1 shadow-sm active:scale-95"
                        title="ভিউ রিসেট করুন">
                    <span>🔄</span> Reset View
                </button>

                <div class="h-4 w-[1px] bg-slate-700 mx-1"></div>

                <!-- 4 & 5. Zoom In & Out -->
                <div class="inline-flex items-center rounded-xl bg-slate-800 p-0.5 border border-slate-700 shadow-sm">
                    <button type="button" 
                            @click="zoomOut()" 
                            class="px-2.5 py-1 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg text-xs font-bold transition-all"
                            title="Zoom Out">
                        −
                    </button>
                    <button type="button" 
                            @click="resetView()" 
                            class="px-2.5 py-1 text-slate-200 hover:text-white hover:bg-slate-700 rounded-lg text-[11px] font-mono font-bold transition-all"
                            title="Click to reset zoom">
                        <span x-text="Math.round(zoomLevel * 100) + '%'">100%</span>
                    </button>
                    <button type="button" 
                            @click="zoomIn()" 
                            class="px-2.5 py-1 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg text-xs font-bold transition-all"
                            title="Zoom In">
                        +
                    </button>
                </div>

                <div class="h-4 w-[1px] bg-slate-700 mx-1"></div>

                <!-- Expand / Collapse Branches -->
                <button type="button" 
                        @click="expandAll()"
                        class="px-2 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-semibold rounded-xl border border-slate-600 transition-all flex items-center gap-1 shadow-sm active:scale-95"
                        title="সবগুলো ব্রাঞ্চ খুলুন">
                    <span>🌲</span> Expand
                </button>
                <button type="button" 
                        @click="collapseAll()"
                        class="px-2 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-semibold rounded-xl border border-slate-600 transition-all flex items-center gap-1 shadow-sm active:scale-95"
                        title="সংকুচিত করুন">
                    <span>📁</span> Collapse
                </button>
            </div>
        </div>

        <!-- Mouse Draggable & Zoomable Viewport -->
        <div id="tree-viewport-container"
             class="overflow-hidden min-h-[660px] relative rounded-xl bg-[#142330]/80 border border-slate-800/80 cursor-grab active:cursor-grabbing select-none"
             :class="isPanning ? 'cursor-grabbing select-none' : 'cursor-grab'"
             @mousedown="startPan($event)"
             @mousemove="onPan($event)"
             @mouseup="endPan()"
             @mouseleave="endPan()">
            
            <div id="tree-content-root"
                 :style="'transform: translate(' + panX + 'px, ' + panY + 'px) scale(' + zoomLevel + '); transform-origin: top center; transition: ' + (isPanning ? 'none' : 'transform 0.12s ease-out') + ';'"
                 class="w-full flex justify-center items-start pt-8 pb-20 px-6">
                
                @if(empty($treeData['tree']))
                    <div class="text-center py-20 text-white">
                        <div class="text-5xl mb-3">🌲</div>
                        <h3 class="text-lg font-bold">কোনো টিম ডাটা নেই</h3>
                        <p class="text-xs text-slate-300">দয়া করে ডাটাবেজ সিড করুন অথবা নতুন রুট মেম্বার যুক্ত করুন।</p>
                    </div>
                @else
                    <!-- Render Recursive FigJam Branching Tree from Current Root -->
                    @include('binary.partials.figjam_node', ['node' => $treeData['tree'], 'depth' => 1])
                @endif

            </div>

            <!-- Floating Draggable Navigator Hint badge at bottom right -->
            <div class="absolute bottom-3 right-3 px-3 py-1.5 rounded-xl bg-slate-900/85 backdrop-blur-sm border border-slate-700/80 text-[11px] text-slate-300 pointer-events-none flex items-center gap-2 shadow-xl z-30">
                <span>🖱️ ড্র্যাগ করে সরান</span>
                <span class="opacity-40">•</span>
                <span>Ctrl + স্ক্রোল করে জুম</span>
            </div>
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
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">ডাইরেক্ট স্পন্সর (By) - টাইপ বা সিলেক্ট</label>
                        <div class="relative">
                            <input type="text" 
                                   name="sponsor_name" 
                                   list="placement_sponsor_list" 
                                   placeholder="স্পন্সরের নাম লিখুন..." 
                                   value="{{ $allNodes->first() ? $allNodes->first()->member_name : 'Md Abdul Hai' }}" 
                                   class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 font-medium">
                            <datalist id="placement_sponsor_list">
                                @foreach($allNodes as $n)
                                <option value="{{ $n->member_name }}">{{ $n->member_code }} ({{ $n->member_name }})</option>
                                @endforeach
                            </datalist>
                        </div>
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
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">স্পন্সর (By) - টাইপ বা সিলেক্ট করুন</label>
                        <div class="relative">
                            <input type="text" 
                                   name="sponsor_name" 
                                   list="edit_sponsor_list" 
                                   x-model="editNode.sponsor_name" 
                                   placeholder="স্পন্সরের নাম টাইপ করুন..." 
                                   class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 font-medium">
                            <datalist id="edit_sponsor_list">
                                @foreach($allNodes as $n)
                                <option value="{{ $n->member_name }}">{{ $n->member_code }} ({{ $n->member_name }})</option>
                                @endforeach
                            </datalist>
                        </div>
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
                        <span class="font-bold text-slate-700">Total Contribution (USD / BV):</span>
                        <div class="flex items-center gap-2">
                            <input type="number" step="1" min="0" name="point_value" x-model="editNode.point_value" class="w-24 px-2 py-1 text-xs bg-white border border-slate-300 rounded font-extrabold text-emerald-700 text-right">
                            <span class="font-bold text-emerald-800 text-xs" x-text="$store.currency ? ($store.currency.code === 'BDT' ? ('≈ ' + $store.currency.format(editNode.point_value)) : '$ USD') : '$ USD'">$ USD</span>
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
                <div class="flex items-center justify-between gap-3 pt-3 border-t border-slate-100">
                    <button type="button" 
                            @click="if(confirm('⚠️ আপনি কি নিশ্চিত যে এই মেম্বার কার্ডটি ডিলিট করতে চান? মেম্বারের নিচের সাব-টিম থাকলে তাও মুছে যাবে।')) { document.getElementById('delete-modal-node-form').submit(); }"
                            class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 active:scale-95 text-rose-700 text-xs font-bold rounded-xl border border-rose-200 transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>মেম্বার ডিলিট করুন</span>
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 active:scale-95 text-white text-sm font-bold rounded-xl shadow-xs transition-colors flex items-center gap-2">
                            <span>Save Changes</span>
                        </button>
                    </div>
                </div>

            </form>

            <!-- Delete Form for Modal -->
            <form id="delete-modal-node-form" :action="'/binary/' + editNode.id" method="POST" class="hidden">
                @csrf
                @method('DELETE')
                <input type="hidden" name="cascade" value="1">
            </form>

        </div>
    </div>

</div>
@endsection
