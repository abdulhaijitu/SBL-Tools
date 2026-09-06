@extends('layouts.app')

@section('page-title', '10-Slot Team Placement Engine')
@section('page-subtitle', 'Hierarchical 5 Left + 5 Right Direct Placement Tree & Strategic Target Planning')

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
        branch: 'LEFT',
        slot_number: 1,
        is_active: true,
        is_target: false,
        target_date: '',
        target_notes: '',
        user_id: ''
    },
    selectedParentId: null,
    selectedParentName: '',
    selectedParentCode: '',
    selectedBranch: 'LEFT',
    selectedSlotNumber: 1,
    isTargetMember: false,
    memberMode: 'new',
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
                this.zoomLevel = Math.max(0.40, Math.min(1.0, Math.round(ratio * 100) / 100));
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
        this.zoomLevel = Math.max(0.30, Math.round((this.zoomLevel - 0.15) * 100) / 100);
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
    openPlacementModal(parentId, parentName, parentCode, branch, slotNumber) {
        this.selectedParentId = parentId;
        this.selectedParentName = parentName;
        this.selectedParentCode = parentCode;
        this.selectedBranch = branch || 'LEFT';
        this.selectedSlotNumber = slotNumber || 1;
        this.isTargetMember = false;
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
            branch: node.branch || 'LEFT',
            slot_number: node.slot_number || 1,
            is_active: node.is_active !== undefined ? Boolean(node.is_active) : true,
            is_target: node.is_target !== undefined ? Boolean(node.is_target) : false,
            target_date: node.target_date || '',
            target_notes: node.target_notes || '',
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
        <!-- Top Row: Title, Root, Sponsor & Workspace Switcher -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-4 border-b border-slate-800">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-orange-400 font-black text-sm uppercase tracking-wider flex items-center gap-1.5">
                        <span>🌲</span> 5 Left + 5 Right Team Tree (১০ স্লট)
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-amber-400/20 text-amber-300 border border-amber-400/40">
                        Rank: {{ $treeData['stats']['rank_name'] ?? 'Member' }}
                    </span>
                    @if(!empty($treeData['stats']['is_fme']))
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                        ★ FME Qualified (5L + 5R)
                    </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400 text-xs font-medium">Root:</span>
                        <strong class="text-white text-base font-bold">{{ $treeData['stats']['root_name'] ?? 'Md. Abdul Hai' }}</strong>
                        <span class="text-xs text-slate-400 font-mono">({{ $treeData['stats']['root_code'] ?? 'SBL-1001' }})</span>
                    </div>
                    <span class="text-slate-600 hidden sm:inline">•</span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400 text-xs font-medium">Sponsor:</span>
                        <span class="text-orange-300 font-bold text-xs">{{ $treeData['stats']['sponsor_name'] ?? 'Md. Samim' }}</span>
                    </div>
                </div>
            </div>

            <!-- Workspace Switcher & View Mode -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Super Admin User Workspace Switcher -->
                @if(!empty($isSuperAdmin) && count($users) > 0)
                <div class="relative">
                    <form action="{{ route('binary.index') }}" method="GET" class="flex items-center gap-1.5 bg-slate-800/90 rounded-xl px-2.5 py-1 border border-slate-700">
                        <span class="text-[11px] text-slate-400 font-bold">👤 ট্রি:</span>
                        <select name="owner_id" onchange="this.form.submit()" class="bg-transparent text-xs font-bold text-orange-300 border-none focus:ring-0 cursor-pointer pr-6 py-0.5">
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ (isset($ownerId) && (int)$ownerId === (int)$u->id) ? 'selected' : '' }} class="bg-slate-900 text-white">
                                {{ $u->name }} ({{ $u->email }})
                            </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                @endif

                <!-- View Mode Switcher -->
                <div class="inline-flex rounded-xl bg-slate-800/90 p-1 border border-slate-700 shadow-xs">
                    <a href="{{ route('binary.index', ['view' => 'tree', 'node_id' => request('node_id'), 'owner_id' => request('owner_id')]) }}" 
                       class="px-3 py-1 text-xs font-bold rounded-lg transition-all {{ $viewMode !== 'table' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white' }}">
                        🌲 Tree View
                    </a>
                    <a href="{{ route('binary.index', ['view' => 'table', 'owner_id' => request('owner_id')]) }}" 
                       class="px-3 py-1 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'table' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white' }}">
                        📋 Directory
                    </a>
                </div>

                <!-- Back to Main Root -->
                <a href="{{ route('binary.index', ['owner_id' => request('owner_id')]) }}" 
                   class="px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1 shadow-sm active:scale-95" 
                   title="প্রধান রুট-এ ফিরে যান">
                    <span>🏠</span> Main Root
                </a>

                @if($treeData['root'] && $treeData['root']->parent_id)
                <a href="{{ route('binary.index', ['node_id' => $treeData['root']->parent_id, 'owner_id' => request('owner_id')]) }}" 
                   class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition-all flex items-center gap-1 shadow-sm active:scale-95" 
                   title="এক ধাপ আপলাইনে যান">
                    <span>⬆️</span> Up Parent
                </a>
                @endif
            </div>
        </div>

        <!-- Breadcrumb Bar -->
        @if(!empty($treeData['breadcrumbs']) && count($treeData['breadcrumbs']) > 1)
        <div class="flex items-center gap-2 text-xs bg-slate-950/70 px-4 py-2 rounded-xl border border-slate-800 text-slate-300 overflow-x-auto">
            <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Breadcrumb:</span>
            @foreach($treeData['breadcrumbs'] as $idx => $bc)
                @if($idx > 0)
                <span class="text-slate-600 font-bold">›</span>
                @endif
                @if($bc['is_current'])
                <span class="text-amber-300 font-bold whitespace-nowrap">{{ $bc['name'] }} ({{ $bc['slot_label'] }})</span>
                @else
                <a href="{{ route('binary.index', ['node_id' => $bc['id'], 'owner_id' => request('owner_id')]) }}" class="text-slate-300 hover:text-orange-400 transition-colors whitespace-nowrap font-medium">{{ $bc['name'] }}</a>
                @endif
            @endforeach
        </div>
        @endif

        <!-- Bottom Row: Dynamic 10-Slot Summary Counters -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
            <!-- Left Team -->
            <div class="p-3.5 bg-slate-950/70 rounded-xl border border-emerald-500/30 space-y-1">
                <div class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider flex items-center justify-between">
                    <span>👈 LEFT TEAM (5 Slots)</span>
                    <span class="px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-300 font-black text-[10px]">{{ $treeData['stats']['direct_left_display'] ?? '0/5' }} Direct</span>
                </div>
                <div class="text-xl font-black text-white">
                    {{ $treeData['stats']['total_left_network'] ?? 0 }} <span class="text-xs font-normal text-slate-400">জন Net</span>
                </div>
                <div class="text-xs text-emerald-300 font-semibold truncate">
                    Vol: <span x-text="$store.currency ? $store.currency.format({{ (float)($treeData['stats']['left_investment_volume'] ?? 0) }}) : '{{ \App\Services\CurrencyService::format((float)($treeData['stats']['left_investment_volume'] ?? 0)) }}'">{{ \App\Services\CurrencyService::format((float)($treeData['stats']['left_investment_volume'] ?? 0)) }}</span> ({{ number_format($treeData['stats']['left_bv'] ?? 0, 0) }} BV)
                </div>
            </div>

            <!-- Right Team -->
            <div class="p-3.5 bg-slate-950/70 rounded-xl border border-blue-500/30 space-y-1">
                <div class="text-[10px] font-bold text-blue-400 uppercase tracking-wider flex items-center justify-between">
                    <span>👉 RIGHT TEAM (5 Slots)</span>
                    <span class="px-1.5 py-0.2 rounded bg-blue-500/20 text-blue-300 font-black text-[10px]">{{ $treeData['stats']['direct_right_display'] ?? '0/5' }} Direct</span>
                </div>
                <div class="text-xl font-black text-white">
                    {{ $treeData['stats']['total_right_network'] ?? 0 }} <span class="text-xs font-normal text-slate-400">জন Net</span>
                </div>
                <div class="text-xs text-blue-300 font-semibold truncate">
                    Vol: <span x-text="$store.currency ? $store.currency.format({{ (float)($treeData['stats']['right_investment_volume'] ?? 0) }}) : '{{ \App\Services\CurrencyService::format((float)($treeData['stats']['right_investment_volume'] ?? 0)) }}'">{{ \App\Services\CurrencyService::format((float)($treeData['stats']['right_investment_volume'] ?? 0)) }}</span> ({{ number_format($treeData['stats']['right_bv'] ?? 0, 0) }} BV)
                </div>
            </div>

            <!-- Total Team & Matched Pairs -->
            <div class="p-3.5 bg-slate-950/70 rounded-xl border border-purple-500/30 space-y-1">
                <div class="text-[10px] font-bold text-purple-400 uppercase tracking-wider flex items-center justify-between">
                    <span>👥 Total Network</span>
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                </div>
                <div class="text-xl font-black text-white">
                    {{ $treeData['stats']['total_members'] ?? 1 }} <span class="text-xs font-normal text-slate-400">জন</span>
                </div>
                <div class="text-xs text-purple-300 font-semibold truncate">
                    Pairs: <strong>{{ $treeData['stats']['matched_pairs'] ?? 0 }}</strong> ({{ number_format(($treeData['stats']['matched_pairs'] ?? 0) * 100, 0) }} BV)
                </div>
            </div>

            <!-- Own Investment & Targets -->
            <div class="p-3.5 bg-slate-950/70 rounded-xl border border-amber-500/30 space-y-1">
                <div class="text-[10px] font-bold text-amber-400 uppercase tracking-wider flex items-center justify-between">
                    <span>💼 Own Inv & Planning</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-xl font-black text-amber-300 truncate">
                    <span x-text="$store.currency ? $store.currency.format({{ (float)($treeData['stats']['own_investment'] ?? 0) }}) : '{{ \App\Services\CurrencyService::format((float)($treeData['stats']['own_investment'] ?? 0)) }}'">{{ \App\Services\CurrencyService::format((float)($treeData['stats']['own_investment'] ?? 0)) }}</span>
                </div>
                <div class="text-[11px] text-purple-300 truncate font-semibold">
                    🎯 Targets: {{ (int)($treeData['stats']['target_left_count'] ?? 0) + (int)($treeData['stats']['target_right_count'] ?? 0) }} জন পরিকল্পিত
                </div>
            </div>
        </div>
    </div>

    @if($viewMode === 'table')
    <!-- ==================== MEMBER DIRECTORY TABLE VIEW ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden space-y-4 p-5">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">টিম মেম্বার তালিকা (10-Slot Member Directory)</h3>
                <p class="text-xs text-slate-500">৫ বাম + ৫ ডান ডিরেক্ট প্লেসমেন্ট স্লটের মেম্বারদের তালিকা ও ডাউনলাইন ভলিউম।</p>
            </div>

            <!-- Search Form -->
            <form action="{{ route('binary.index') }}" method="GET" class="w-full sm:w-80 flex items-center gap-2">
                <input type="hidden" name="view" value="table">
                @if(request('owner_id'))
                <input type="hidden" name="owner_id" value="{{ request('owner_id') }}">
                @endif
                <div class="relative flex-1">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="নাম, কোড বা মোবাইল..." 
                           class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-colors">
                    Search
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-y border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">মেম্বার</th>
                        <th class="py-3 px-4">প্লেসমেন্ট স্লট</th>
                        <th class="py-3 px-4">প্যাকেজ ও পদবী</th>
                        <th class="py-3 px-4 text-center">বাম টিম (Left)</th>
                        <th class="py-3 px-4 text-center">ডান টিম (Right)</th>
                        <th class="py-3 px-4 text-center">টাইপ / স্ট্যাটাস</th>
                        <th class="py-3 px-4 text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($members as $member)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <!-- Member Profile -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl {{ $member->is_target ? 'bg-purple-900 text-purple-200' : 'bg-slate-900 text-orange-400' }} font-bold flex items-center justify-center text-xs flex-shrink-0 shadow-xs">
                                    {{ substr($member->member_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $member->member_name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $member->member_code }} @if($member->phone) • {{ $member->phone }} @endif</div>
                                </div>
                            </div>
                        </td>

                        <!-- Placement Slot -->
                        <td class="py-3.5 px-4">
                            @if($member->parent)
                                <div class="font-semibold text-slate-800">{{ $member->parent->member_name }}</div>
                                <div class="text-[11px]">
                                    <span class="px-1.5 py-0.5 rounded font-black {{ ($member->branch === 'LEFT' || $member->position === 'left') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                        {{ $member->slot_label }}
                                    </span>
                                </div>
                            @else
                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[11px]">Top Root</span>
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
                            <div class="text-[11px] text-slate-500 font-medium">@currency($member->left_bv)</div>
                        </td>

                        <!-- Right Team Count & BV -->
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-blue-700">{{ $member->right_count }} জন</span>
                            <div class="text-[11px] text-slate-500 font-medium">@currency($member->right_bv)</div>
                        </td>

                        <!-- Type & Status -->
                        <td class="py-3.5 px-4 text-center">
                            @if($member->is_target)
                            <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 font-bold text-[10px] border border-purple-200">🎯 Target</span>
                            @elseif($member->is_active)
                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-200">Active</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-bold text-[10px]">Inactive</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($member->is_target)
                                <form action="{{ route('binary.convert-target', $member->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-[10px] transition-colors" title="টার্গেট থেকে অ্যাক্টিভ করুন">
                                        🚀 Convert
                                    </button>
                                </form>
                                @endif

                                <a href="{{ route('binary.index', ['node_id' => $member->id, 'owner_id' => request('owner_id')]) }}" 
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
                                            branch: '{{ $member->branch ?: ($member->position === "left" ? "LEFT" : "RIGHT") }}',
                                            slot_number: {{ $member->slot_number ?: 1 }},
                                            is_active: {{ $member->is_active ? 'true' : 'false' }},
                                            is_target: {{ $member->is_target ? 'true' : 'false' }},
                                            target_date: '{{ $member->target_date ? $member->target_date->toDateString() : "" }}',
                                            target_notes: '{{ addslashes($member->target_notes ?? "") }}',
                                            user_id: '{{ $member->user_id }}'
                                        })"
                                        class="p-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 transition-colors" 
                                        title="মেম্বার এডিট করুন">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>

                                <form action="{{ route('binary.destroy', $member->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই মেম্বারকে ({{ $member->member_name }}) রিমুভ করতে চান?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="cascade" value="1">
                                    <button type="submit" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors" title="মেম্বার রিমুভ করুন">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400">
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
            @if(request('owner_id'))
            <input type="hidden" name="owner_id" value="{{ request('owner_id') }}">
            @endif
            <div class="relative flex-1">
                <input type="text" 
                       name="search" 
                       list="tree_search_datalist"
                       placeholder="মেম্বারের নাম বা কোড দিয়ে সার্চ করুন..." 
                       class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all font-medium">
                <datalist id="tree_search_datalist">
                    @foreach($allNodes as $an)
                    <option value="{{ $an->member_code }}">{{ $an->member_name }} ({{ $an->member_code }})</option>
                    @endforeach
                </datalist>
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors shrink-0">
                Search
            </button>
        </form>

        <!-- Current View Root Badge & Actions -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="text-xs font-semibold text-slate-600 flex items-center gap-1.5">
                <span class="text-slate-400 text-[11px]">View Root:</span>
                <span class="px-2.5 py-1 rounded-lg bg-orange-50 text-orange-700 font-bold border border-orange-200">
                    {{ $treeData['root'] ? $treeData['root']->member_name : 'No Root' }}
                </span>
            </div>
        </div>
    </div>

    <!-- ==================== 3. 10-SLOT FIGJAM INTERACTIVE TREE CANVAS ==================== -->
    <div class="bg-[#121e28] rounded-2xl border border-slate-700/80 p-4 md:p-6 shadow-2xl relative select-none"
         @wheel="handleWheel($event)">
        
        <!-- Canvas Floating Top Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-700/60 text-white">
            <div class="flex items-center gap-2">
                <span class="text-xs font-black text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                    <span>🎨</span> 10-Slot Placement Canvas
                </span>
                <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-orange-500/20 text-orange-300 border border-orange-500/30 font-medium flex items-center gap-1">
                    <span>🖱️</span> মাউস ড্র্যাগ করে স্ক্রোল করুন
                </span>
            </div>

            <!-- Viewport & Zoom Controls -->
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="button" 
                        @click="fitToScreen()"
                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-600 transition-all flex items-center gap-1 shadow-sm active:scale-95"
                        title="সম্পূর্ণ ট্রি স্ক্রিনে ফিট করুন">
                    <span>🔍</span> Fit
                </button>

                <button type="button" 
                        @click="centerRoot()"
                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-600 transition-all flex items-center gap-1 shadow-sm active:scale-95"
                        title="রুট সেন্টারে আনুন">
                    <span>🎯</span> Center
                </button>

                <button type="button" 
                        @click="resetView()"
                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-600 transition-all flex items-center gap-1 shadow-sm active:scale-95">
                    <span>🔄</span> Reset
                </button>

                <div class="h-4 w-[1px] bg-slate-700 mx-1"></div>

                <div class="inline-flex items-center rounded-xl bg-slate-800 p-0.5 border border-slate-700 shadow-sm">
                    <button type="button" 
                            @click="zoomOut()" 
                            class="px-2.5 py-1 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg text-xs font-bold transition-all">
                        −
                    </button>
                    <button type="button" 
                            @click="resetView()" 
                            class="px-2.5 py-1 text-slate-200 hover:text-white hover:bg-slate-700 rounded-lg text-[11px] font-mono font-bold transition-all">
                        <span x-text="Math.round(zoomLevel * 100) + '%'">100%</span>
                    </button>
                    <button type="button" 
                            @click="zoomIn()" 
                            class="px-2.5 py-1 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg text-xs font-bold transition-all">
                        +
                    </button>
                </div>

                <div class="h-4 w-[1px] bg-slate-700 mx-1"></div>

                <button type="button" 
                        @click="expandAll()"
                        class="px-2 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-semibold rounded-xl border border-slate-600 transition-all flex items-center gap-1 shadow-sm active:scale-95">
                    <span>🌲</span> Expand
                </button>
                <button type="button" 
                        @click="collapseAll()"
                        class="px-2 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-semibold rounded-xl border border-slate-600 transition-all flex items-center gap-1 shadow-sm active:scale-95">
                    <span>📁</span> Collapse
                </button>
            </div>
        </div>

        <!-- Viewport Container -->
        <div id="tree-viewport-container"
             class="overflow-hidden min-h-[680px] relative rounded-xl bg-[#0c161e]/90 border border-slate-800/80 cursor-grab active:cursor-grabbing select-none"
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
                        <p class="text-xs text-slate-300">দয়া করে নতুন রুট মেম্বার যুক্ত করুন।</p>
                    </div>
                @else
                    @include('binary.partials.figjam_node', ['node' => $treeData['tree'], 'depth' => 1])
                @endif

            </div>

            <!-- Draggable Navigator Hint -->
            <div class="absolute bottom-3 right-3 px-3 py-1.5 rounded-xl bg-slate-900/85 backdrop-blur-sm border border-slate-700/80 text-[11px] text-slate-300 pointer-events-none flex items-center gap-2 shadow-xl z-30">
                <span>🖱️ ড্র্যাগ করে সরান</span>
                <span class="opacity-40">•</span>
                <span>Ctrl + স্ক্রোল করে জুম</span>
            </div>
        </div>
    </div>
    @endif

    <!-- ==================== PLACEMENT MODAL (+ ADD MEMBER) ==================== -->
    <div x-show="placementModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="placementModalOpen = false" 
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>➕</span> নতুন মেম্বার প্লেসমেন্ট (১০-স্লট আর্কিটেকচার)
                </h3>
                <button @click="placementModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <!-- Placement Slot Indicator -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between text-xs">
                <div>
                    <span class="text-slate-400">আপলাইন প্যারেন্ট:</span>
                    <div class="font-bold text-slate-900" x-text="selectedParentName + ' (' + selectedParentCode + ')'"></div>
                </div>
                <div class="text-right">
                    <span class="text-slate-400">নির্ধারিত স্লট:</span>
                    <div>
                        <span class="px-2.5 py-1 rounded-full font-black"
                              :class="selectedBranch === 'LEFT' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'"
                              x-text="(selectedBranch === 'LEFT' ? '👈 LEFT' : '👉 RIGHT') + ' স্লট-' + selectedSlotNumber">
                        </span>
                    </div>
                </div>
            </div>

            <form action="{{ route('binary.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="parent_id" :value="selectedParentId">
                <input type="hidden" name="branch" :value="selectedBranch">
                <input type="hidden" name="slot_number" :value="selectedSlotNumber">

                <!-- Member Type Toggle (Active vs Planned Target) -->
                <div class="p-3 rounded-xl border border-purple-200 bg-purple-50/70 space-y-2">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <div class="text-xs font-black text-purple-950 flex items-center gap-1.5">
                                <span>🎯</span> এটি একটি পরিকল্পিত টার্গেট মেম্বার (Planning/Target Member)?
                            </div>
                            <div class="text-[11px] text-purple-700">টার্গেট মেম্বার হিসেবে ভবিষ্যতে টিম বাড়ানোর পরিকল্পনা করুন।</div>
                        </div>
                        <input type="checkbox" name="is_target" value="1" x-model="isTargetMember" class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                    </label>

                    <div x-show="isTargetMember" x-transition class="space-y-2 pt-2 border-t border-purple-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-purple-900 mb-1">টার্গেট কনফার্মেশন ডেট</label>
                                <input type="date" name="target_date" class="w-full text-xs bg-white border border-purple-300 rounded-lg p-2 focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-purple-900 mb-1">পরিকল্পনা নোট</label>
                                <input type="text" name="target_notes" placeholder="e.g. আগামী মাসের ১ম সপ্তাহে জয়েন করবে" class="w-full text-xs bg-white border border-purple-300 rounded-lg p-2 focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slot Selector -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">ব্রাঞ্চ (Branch)</label>
                        <select name="branch" x-model="selectedBranch" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-bold">
                            <option value="LEFT">👈 LEFT TEAM</option>
                            <option value="RIGHT">👉 RIGHT TEAM</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">স্লট নম্বর (1 - 5)</label>
                        <select name="slot_number" x-model="selectedSlotNumber" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-bold">
                            <option value="1">স্লট-১ (Slot-1)</option>
                            <option value="2">স্লট-২ (Slot-2)</option>
                            <option value="3">স্লট-৩ (Slot-3)</option>
                            <option value="4">স্লট-৪ (Slot-4)</option>
                            <option value="5">স্লট-৫ (Slot-5)</option>
                        </select>
                    </div>
                </div>

                <!-- Member Name & Code -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">মেম্বারের পুরো নাম <span class="text-rose-500">*</span></label>
                        <input type="text" name="member_name" required placeholder="e.g. Md. Karim" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">ইউজারনেম / মেম্বার কোড</label>
                        <input type="text" name="member_code" placeholder="স্বয়ংক্রিয় তৈরি হবে বা @username দিন" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500 font-mono">
                    </div>
                </div>

                <!-- Phone & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">মোবাইল নম্বর</label>
                        <input type="text" name="phone" placeholder="017xxxxxxxx" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">ইমেইল</label>
                        <input type="email" name="email" placeholder="karim@sbl.test" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                <!-- Sponsor Name & Package -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">স্পন্সর / আপলাইন নাম</label>
                        <input type="text" name="sponsor_name" :value="selectedParentName" placeholder="স্পন্সরের নাম" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">প্যাকেজ নির্বাচন</label>
                        <select name="package_name" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500 font-medium">
                            @foreach($packages as $pkg)
                            <option value="{{ $pkg['name'] }}">{{ $pkg['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- SBL Ecosystem Login Password & TPIN -->
                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>SBL Ecosystem পাসওয়ার্ড</span>
                        </label>
                        <input type="text" name="password_plain" value="sbl123456" class="w-full text-xs bg-white border border-slate-200 rounded-xl p-2.5 font-mono" placeholder="SBL Ecosystem Pass">
                        <span class="text-[10px] text-slate-500 font-medium">অফিসিয়াল SBL পোর্টাল লগইন পাসওয়ার্ড (অ্যাপ পাসওয়ার্ড থেকে আলাদা)</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">SBL TPIN (সিক্রেট পিন)</label>
                        <input type="text" name="tpin" value="1234" class="w-full text-xs bg-white border border-slate-200 rounded-xl p-2.5 font-mono" placeholder="1234">
                        <span class="text-[10px] text-slate-500 font-medium">SBL অ্যাকাউন্ট ট্রানজেকশন পিন</span>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="placementModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors">
                        বাতিল
                    </button>
                    <button type="submit" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-all shadow-md active:scale-95">
                        সংরক্ষণ ও প্লেসমেন্ট করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== EDIT MEMBER MODAL ==================== -->
    <div x-show="editModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="editModalOpen = false" 
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>✏️</span> মেম্বার তথ্য ও ইনভেস্টমেন্ট এডিট
                </h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form id="edit-member-form" action="" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')

                <!-- Target Member Status -->
                <div class="p-3 rounded-xl border border-purple-200 bg-purple-50/70 space-y-2">
                    <label class="flex items-center justify-between cursor-pointer">
                        <span class="text-xs font-black text-purple-950 flex items-center gap-1.5">
                            <span>🎯</span> পরিকল্পিত টার্গেট মেম্বার (Target / Planned)
                        </span>
                        <input type="checkbox" name="is_target" value="1" x-model="editNode.is_target" class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                    </label>

                    <div x-show="editNode.is_target" class="space-y-2 pt-2 border-t border-purple-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-purple-900 mb-1">টার্গেট ডেট</label>
                                <input type="date" name="target_date" x-model="editNode.target_date" class="w-full text-xs bg-white border border-purple-300 rounded-lg p-2">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-purple-900 mb-1">টার্গেট নোট</label>
                                <input type="text" name="target_notes" x-model="editNode.target_notes" class="w-full text-xs bg-white border border-purple-300 rounded-lg p-2">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Name & Code -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">নাম <span class="text-rose-500">*</span></label>
                        <input type="text" name="member_name" x-model="editNode.member_name" required class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">ইউজারনেম / কোড</label>
                        <input type="text" name="member_code" x-model="editNode.member_code" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-mono">
                    </div>
                </div>

                <!-- Phone & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">মোবাইল নম্বর</label>
                        <input type="text" name="phone" x-model="editNode.phone" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">ইমেইল</label>
                        <input type="email" name="email" x-model="editNode.email" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                    </div>
                </div>

                <!-- SBL Ecosystem Login Password & TPIN -->
                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">SBL Ecosystem পাসওয়ার্ড</label>
                        <input type="text" name="password_plain" x-model="editNode.password_plain" class="w-full text-xs bg-white border border-slate-200 rounded-xl p-2.5 font-mono">
                        <span class="text-[10px] text-slate-500 font-medium">SBL পোর্টাল লগইন পাসওয়ার্ড</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">SBL TPIN</label>
                        <input type="text" name="tpin" x-model="editNode.tpin" class="w-full text-xs bg-white border border-slate-200 rounded-xl p-2.5 font-mono">
                        <span class="text-[10px] text-slate-500 font-medium">ট্রানজেকশন পিন</span>
                    </div>
                </div>

                <!-- Sponsor Name & Rank -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">স্পন্সর নাম</label>
                        <input type="text" name="sponsor_name" x-model="editNode.sponsor_name" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">পদবী (Rank)</label>
                        <select name="rank_name" x-model="editNode.rank_name" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-bold">
                            <option value="Member">Member</option>
                            <option value="FME">Field Marketing Executive (FME)</option>
                            <option value="SME">Senior Marketing Executive (SME)</option>
                            <option value="PME">Premier Marketing Executive (PME)</option>
                            <option value="BME">Branch Marketing Executive (BME)</option>
                            <option value="GME">Global Marketing Executive (GME)</option>
                            <option value="ETD">Executive Top Director (ETD)</option>
                        </select>
                    </div>
                </div>

                <!-- Multiple Investments / Contributions Manager -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-900">💼 নিজস্ব ইনভেস্টমেন্ট ও কিস্তি রেকর্ড</label>
                        <button type="button" @click="addContributionRow()" class="px-2 py-0.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px]">
                            + কিস্তি যোগ করুন
                        </button>
                    </div>

                    <input type="hidden" name="contributions" :value="JSON.stringify(editNode.contributions)">

                    <template x-for="(c, idx) in editNode.contributions" :key="idx">
                        <div class="flex items-center gap-2 text-xs bg-white p-2 rounded-lg border border-slate-200">
                            <input type="number" x-model="c.amount" @input="recalcTotalContribution()" placeholder="পরিমাণ" class="w-24 p-1 text-xs border rounded font-mono font-bold">
                            <input type="date" x-model="c.date" class="w-32 p-1 text-xs border rounded">
                            <input type="text" x-model="c.note" placeholder="নোট / বিবরণ" class="flex-1 p-1 text-xs border rounded">
                            <button type="button" @click="removeContributionRow(idx)" class="text-rose-600 hover:text-rose-800 font-bold px-1">&times;</button>
                        </div>
                    </template>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">
                        বাতিল
                    </button>
                    <button type="submit" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-md active:scale-95">
                        আপডেট সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
