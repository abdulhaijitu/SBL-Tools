@extends('layouts.app')

@section('page-title', 'Team Explorer')
@section('page-subtitle', 'Hierarchical 5 Left + 5 Right Direct Placement Dashboard & Team Management')

@section('content')
<div class="space-y-6" x-data="{
    viewMode: '{{ $viewMode }}',
    detailsModalOpen: false,
    placementModalOpen: false,
    editModalOpen: false,
    activeDetailsTab: 'overview', // 'overview', 'investments', 'direct'
    detailsNode: {},
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
    showDetailsPass: false,
    getNodeData(node) {
        if (!node) return {};
        const id = (typeof node === 'object' && node !== null) ? node.id : node;
        if (window.DATA && window.DATA.nodes && Array.isArray(window.DATA.nodes)) {
            const live = window.DATA.nodes.find(n => Number(n.id) === Number(id));
            if (live) {
                let contribs = [];
                try {
                    contribs = typeof live.contributions === 'string' ? JSON.parse(live.contributions) : (live.contributions || []);
                } catch(e) {}
                let pv = Number(live.point_value) || 0;
                if (Array.isArray(contribs) && contribs.length > 0) {
                    pv = contribs.reduce((sum, c) => sum + (Number(c.amount) || 0), 0);
                }
                const code = live.member_code || ('SBL-' + live.id);
                const username = live.username || (code.startsWith('@') ? code : ('@' + code.replace(/[^a-zA-Z0-9_]/g, '').toLowerCase()));
                return {
                    ...live,
                    id: live.id,
                    member_name: live.member_name,
                    member_code: live.member_code,
                    username: username,
                    phone: live.phone || '',
                    email: live.email || '',
                    password_plain: live.password_plain || 'sbl123456',
                    tpin: live.tpin || '1234',
                    sponsor_id: live.sponsor_id,
                    sponsor_name: live.sponsor_name || 'Md. Samim',
                    user_id: live.user_id,
                    point_value: pv,
                    total_investment: pv,
                    own_investment: pv,
                    contributions: contribs,
                    rank_name: live.rank_name || 'Member',
                    is_active: live.is_active !== undefined ? Boolean(Number(live.is_active)) : true,
                    is_target: Boolean(Number(live.is_target)),
                    target_date: live.target_date || '',
                    target_notes: live.target_notes || '',
                    branch: live.branch || (live.position === 'left' ? 'LEFT' : 'RIGHT'),
                    slot_number: Number(live.slot_number) || 1,
                    direct_left_count: Number(live.left_count) || 0,
                    direct_right_count: Number(live.right_count) || 0
                };
            }
        }
        return (typeof node === 'object' && node !== null) ? node : { id: node };
    },
    openDetailsModal(node) {
        this.detailsNode = this.getNodeData(node);
        this.showDetailsPass = false;
        this.activeDetailsTab = 'overview';
        this.detailsModalOpen = true;
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
        const liveNode = this.getNodeData(node);
        let contribs = [];
        if (liveNode.contributions) {
            contribs = typeof liveNode.contributions === 'string' ? JSON.parse(liveNode.contributions) : liveNode.contributions;
        }
        if (!contribs || contribs.length === 0) {
            contribs = [
                { amount: liveNode.total_investment || liveNode.point_value || 0, date: new Date().toISOString().slice(0, 10), note: liveNode.package_name || 'Initial' }
            ];
        }

        this.editNode = {
            id: liveNode.id,
            member_name: liveNode.member_name || '',
            member_code: liveNode.member_code || '',
            phone: liveNode.phone || '',
            email: liveNode.email || '',
            password_plain: liveNode.password_plain || 'sbl123456',
            tpin: liveNode.tpin || '1234',
            package_name: liveNode.package_name || 'National 120k',
            point_value: liveNode.point_value !== undefined ? liveNode.point_value : 100,
            contributions: contribs,
            rank_name: liveNode.rank_name || 'Member',
            sponsor_id: liveNode.sponsor_id || '',
            sponsor_name: liveNode.sponsor_name || '',
            branch: liveNode.branch || 'LEFT',
            slot_number: liveNode.slot_number || 1,
            is_active: liveNode.is_active !== undefined ? Boolean(liveNode.is_active) : true,
            is_target: liveNode.is_target !== undefined ? Boolean(liveNode.is_target) : false,
            target_date: liveNode.target_date || '',
            target_notes: liveNode.target_notes || '',
            user_id: liveNode.user_id || ''
        };
        this.editModalOpen = true;
        this.$nextTick(() => {
            const form = document.querySelector('#edit-member-form');
            if (form && liveNode.id) {
                form.action = '/team/' + liveNode.id;
            }
        });
    }
}">

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 text-sm font-semibold flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
        <button @click="$el.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold cursor-pointer">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 text-sm font-semibold flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
        <button @click="$el.parentElement.remove()" class="text-rose-700 hover:text-rose-900 font-bold cursor-pointer">&times;</button>
    </div>
    @endif

    <!-- ==================== 1. TOP NAVIGATION & CONTROLS TOOLBAR ==================== -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 rounded-2xl p-4 md:p-5 border border-slate-800 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        
        <!-- Left Title & Navigation Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-2 pr-2 border-r border-slate-800">
                <span class="text-base font-black text-orange-400 uppercase tracking-wider flex items-center gap-1.5">
                    <span>👥</span> Team Explorer
                </span>
            </div>

            <!-- Home / Main Team Root -->
            <a href="{{ route('team.index', ['owner_id' => request('owner_id')]) }}" 
               class="px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white text-xs font-black rounded-xl transition-all shadow-sm active:scale-95 flex items-center gap-1.5" 
               title="Return to Main Root (Md. Abdul Hai)">
                <span>🏠</span> <span>Home / Main Team</span>
            </a>

            <!-- Back Button (Browser history or parent fallback) -->
            <button type="button" 
                    onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('team.index') }}'"
                    class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition-all flex items-center gap-1.5 shadow-sm active:scale-95 cursor-pointer"
                    title="Go Back">
                <span>◀</span> <span>Back</span>
            </button>

            <!-- Parent Member Button (if current member has parent) -->
            @if(!empty($treeData['parent_node']))
            <a href="{{ route('team.show', ['memberId' => $treeData['parent_node']->id, 'owner_id' => request('owner_id')]) }}" 
               class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition-all flex items-center gap-1.5 shadow-sm active:scale-95" 
               title="Go to Parent ({{ $treeData['parent_node']->member_name }})">
                <span>⬆️</span> <span>Parent ({{ $treeData['parent_node']->member_name }})</span>
            </a>
            @endif
        </div>

        <!-- Right Search & View Switcher -->
        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <!-- Search Member Form -->
            <form action="{{ route('team.search') }}" method="GET" class="flex-1 md:w-72 flex items-center gap-1.5">
                @if(request('owner_id'))
                <input type="hidden" name="owner_id" value="{{ request('owner_id') }}">
                @endif
                <div class="relative w-full">
                    <input type="text" 
                           name="search" 
                           list="team_search_datalist"
                           placeholder="Search member (name, code, phone)..." 
                           class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <datalist id="team_search_datalist">
                        @foreach($allNodes as $an)
                        <option value="{{ $an->member_code }}">{{ $an->member_name }} ({{ $an->member_code }})</option>
                        @endforeach
                    </datalist>
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition-colors shrink-0 cursor-pointer">
                    Search
                </button>
            </form>

            <!-- Super Admin User Workspace Switcher -->
            @if(!empty($isSuperAdmin) && count($users) > 0)
            <form action="{{ route('team.index') }}" method="GET" class="flex items-center gap-1 bg-slate-800/90 rounded-xl px-2 py-1 border border-slate-700">
                <span class="text-[11px] text-slate-400 font-bold">👤</span>
                <select name="owner_id" onchange="this.form.submit()" class="bg-transparent text-xs font-bold text-orange-300 border-none focus:ring-0 cursor-pointer pr-4 py-0.5">
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ (isset($ownerId) && (int)$ownerId === (int)$u->id) ? 'selected' : '' }} class="bg-slate-900 text-white">
                        {{ $u->name }}
                    </option>
                    @endforeach
                </select>
            </form>
            @endif

            <!-- View Switcher -->
            <div class="inline-flex rounded-xl bg-slate-800 p-0.5 border border-slate-700">
                <a href="{{ route('team.index', ['view' => 'tree', 'node_id' => request('node_id') ?? request('memberId'), 'owner_id' => request('owner_id')]) }}" 
                    class="px-3 py-1 text-xs font-bold rounded-lg transition-all {{ $viewMode !== 'table' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white' }}">
                    👥 Explorer
                </a>
                <a href="{{ route('team.index', ['view' => 'table', 'owner_id' => request('owner_id')]) }}" 
                    class="px-3 py-1 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'table' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white' }}">
                    📋 Directory
                </a>
            </div>
        </div>
    </div>

    <!-- ==================== 2. BREADCRUMB NAVIGATION TRAIL ==================== -->
    @if(!empty($treeData['breadcrumbs']) && count($treeData['breadcrumbs']) > 0)
    <div class="flex items-center gap-2 text-xs bg-white rounded-2xl px-4 py-2.5 border border-slate-200/80 text-slate-600 shadow-xs overflow-x-auto">
        <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Genealogy Path:</span>
        @foreach($treeData['breadcrumbs'] as $idx => $bc)
            @if($idx > 0)
            <span class="text-slate-300 font-bold">›</span>
            @endif
            @if($bc['is_current'])
            <span class="text-orange-600 font-black whitespace-nowrap bg-orange-50 px-2 py-0.5 rounded-lg border border-orange-200">
                {{ $bc['name'] }} ({{ $bc['slot_label'] }})
            </span>
            @else
            <a href="{{ route('team.show', ['memberId' => $bc['id'], 'owner_id' => request('owner_id')]) }}" 
               class="text-slate-700 hover:text-orange-600 font-bold transition-colors whitespace-nowrap">
                {{ $bc['name'] }}
            </a>
            @endif
        @endforeach
    </div>
    @endif

    @if($viewMode === 'table')
    <!-- ==================== MEMBER DIRECTORY TABLE VIEW ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden space-y-4 p-5">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">10-Slot Member Directory</h3>
                <p class="text-xs text-slate-500">Overview of all team members, placement positions, and network statistics.</p>
            </div>

            <!-- Search Form -->
            <form action="{{ route('team.index') }}" method="GET" class="w-full sm:w-80 flex items-center gap-2">
                <input type="hidden" name="view" value="table">
                @if(request('owner_id'))
                <input type="hidden" name="owner_id" value="{{ request('owner_id') }}">
                @endif
                <div class="relative flex-1">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search name, code, phone..." 
                           class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-colors cursor-pointer">
                    Search
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-y border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Member</th>
                        <th class="py-3 px-4">Placement Slot</th>
                        <th class="py-3 px-4">Rank & Package</th>
                        <th class="py-3 px-4 text-center">Direct Team</th>
                        <th class="py-3 px-4 text-center">Own Investment</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($members as $member)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <!-- Member Profile -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl {{ $member->is_target ? 'bg-purple-900 text-purple-200' : 'bg-slate-900 text-orange-400' }} font-black flex items-center justify-center text-xs flex-shrink-0 shadow-xs">
                                    {{ substr($member->member_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 hover:text-orange-600 transition-colors cursor-pointer"
                                         @click="openDetailsModal({{ json_encode($member) }})">
                                        {{ $member->member_name }}
                                    </div>
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
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-200">
                                {{ $member->rank_name }}
                            </span>
                            <div class="text-[11px] text-slate-500 font-medium mt-0.5">{{ $member->package_name }}</div>
                        </td>

                        <!-- Direct Team Count -->
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-black text-slate-900">{{ ($member->left_count ?? 0) + ($member->right_count ?? 0) }} Direct</span>
                            <div class="text-[10px] text-slate-400">Total Net</div>
                        </td>

                        <!-- Own Investment -->
                        <td class="py-3.5 px-4 text-center font-bold text-amber-600">
                            @currency($member->total_investment_amount ?: $member->point_value)
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
                                <a href="{{ route('team.show', ['memberId' => $member->id, 'owner_id' => request('owner_id')]) }}" 
                                   class="px-2 py-1 rounded-lg bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-xs transition-colors flex items-center gap-1" 
                                   title="Explore this member's team">
                                    <span>👥</span> <span>View Team</span>
                                </a>

                                <button type="button" 
                                        @click="openDetailsModal({{ json_encode($member) }})"
                                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" 
                                        title="View member details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400">
                            No team members found.
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
    <!-- ==================== 3. CURRENT MEMBER SUMMARY CARD (AT TOP) ==================== -->
    @php
        $curr = $treeData['current_member'] ?? [];
        $directL = (int)($treeData['stats']['direct_left_count'] ?? 0);
        $directR = (int)($treeData['stats']['direct_right_count'] ?? 0);
        $directTotal = (int)($treeData['stats']['direct_total_count'] ?? ($directL + $directR));
        $isFme = !empty($treeData['stats']['is_fme']);
        $ownInv = (float)($treeData['stats']['own_investment'] ?? 0);
        $sponsorName = $treeData['stats']['sponsor_name'] ?? 'Md. Samim';
        $currCode = $treeData['stats']['root_code'] ?? 'SBL-1001';
        $currUsername = $curr['username'] ?? (str_starts_with($currCode, '@') ? $currCode : ('@' . strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $currCode))));
    @endphp

    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 rounded-3xl p-6 border-2 border-slate-800 text-white shadow-2xl space-y-5 relative overflow-hidden">
        <!-- Subtle Glow Effect in Background -->
        <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-orange-500/10 blur-3xl pointer-events-none"></div>

        <!-- Top Profile Row: Name, Rank, Sponsor & Action Buttons -->
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 pb-4 border-b border-slate-800/90">
            <div class="flex items-center gap-4">
                <!-- Avatar / Initial -->
                <div data-current-member-initial class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-slate-950 font-black text-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                    {{ substr($treeData['stats']['root_name'] ?? 'M', 0, 1) }}
                </div>

                <div class="space-y-1">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 data-current-member-name class="text-xl md:text-2xl font-black text-white tracking-tight">
                            {{ $treeData['stats']['root_name'] ?? 'Md. Abdul Hai' }}
                        </h2>
                        
                        <!-- Rank Badge -->
                        <span data-current-member-rank class="px-3 py-0.5 rounded-full text-xs font-black {{ $isFme ? 'bg-amber-400 text-slate-950 shadow-md shadow-amber-400/30' : 'bg-slate-800 text-amber-300 border border-amber-400/40' }}">
                            {{ $treeData['stats']['rank_name'] ?? 'Member' }}
                        </span>

                        @if(!empty($treeData['is_main_root']))
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-blue-500/20 text-blue-300 border border-blue-400/30">
                            ★ Main Team Root
                        </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400">
                        <span class="font-mono text-slate-300 font-bold flex items-center gap-1">
                            <span data-current-member-username>{{ $currUsername }}</span>
                            <button type="button" @click="navigator.clipboard.writeText('{{ $currUsername }}'); alert('Username copied: {{ $currUsername }}');" class="hover:text-white cursor-pointer" title="Copy username">
                                <svg class="w-3.5 h-3.5 inline opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                        </span>
                        <span>•</span>
                        <span>Sponsor / Upline: <strong data-current-member-sponsor class="text-orange-300">{{ $sponsorName }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="flex items-center gap-2">
                <button type="button" 
                        data-btn-full-details
                        @click="openDetailsModal({{ json_encode($curr) }})"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl border border-slate-700 shadow-sm transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer">
                    <span>ℹ️</span> <span>Full Details</span>
                </button>

                <button type="button" 
                        data-btn-edit-member
                        @click="openEditModal({{ json_encode($curr) }})"
                        class="px-4 py-2 bg-orange-600/90 hover:bg-orange-600 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer">
                    <span>✏️</span> <span>Edit Member</span>
                </button>
            </div>
        </div>

        <!-- Metrics Grid: Direct Team, Own Investment & FME Progress -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
            <!-- 1. Direct Team Count -->
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-1">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                    <span>Direct Team</span>
                    <span data-current-direct-total class="text-white font-black">{{ $directTotal }}/10</span>
                </div>
                <div class="text-2xl font-black text-white flex items-baseline gap-2">
                    <span data-current-direct-sub>{{ $directTotal }}</span>
                    <span class="text-xs font-semibold text-slate-400">/ 10 Direct Slots</span>
                </div>
                <div data-current-direct-split class="text-xs text-slate-300 font-bold flex items-center gap-3 pt-1">
                    <span class="text-emerald-400">👈 Left: {{ $directL }}/5</span>
                    <span class="text-slate-600">|</span>
                    <span class="text-blue-400">👉 Right: {{ $directR }}/5</span>
                </div>
            </div>

            <!-- 2. Own Investment -->
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-1">
                <div class="text-[11px] font-bold text-amber-400 uppercase tracking-wider flex items-center justify-between">
                    <span>Own Investment</span>
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                </div>
                <div data-current-own-investment class="text-2xl font-black text-amber-300 truncate">
                    <span x-text="$store.currency ? $store.currency.format({{ $ownInv }}) : '{{ \App\Services\CurrencyService::format($ownInv) }}'">{{ \App\Services\CurrencyService::format($ownInv) }}</span>
                </div>
                <div data-current-investment-count class="text-[11px] text-slate-400 truncate">
                    {{ count($curr['contributions'] ?? []) }} Investment Records
                </div>
            </div>

            <!-- 3. FME Progress -->
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2">
                <div class="text-[11px] font-bold text-purple-300 uppercase tracking-wider flex items-center justify-between">
                    <span>FME Status & Progress</span>
                    @if($isFme)
                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-black border border-emerald-500/40">
                        Achieved
                    </span>
                    @else
                    <span class="text-slate-400 text-[10px] font-bold">5L + 5R Target</span>
                    @endif
                </div>

                @if($isFme)
                <div class="text-base font-black text-emerald-400 flex items-center gap-1.5 pt-1">
                    <span>★</span> <span>FME Qualified (5/5 L + 5/5 R)</span>
                </div>
                <div class="text-[11px] text-slate-400">
                    All 10 direct placement slots have been completed.
                </div>
                @else
                <div class="space-y-1.5 pt-0.5">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-emerald-400">Left: {{ $directL }}/5</span>
                        <span class="text-blue-400">Right: {{ $directR }}/5</span>
                    </div>
                    <!-- Dual progress bar -->
                    <div class="w-full h-2.5 rounded-full bg-slate-800 overflow-hidden flex">
                        <div class="h-full bg-emerald-500 transition-all duration-300" style="width: {{ ($directL / 10) * 100 }}%"></div>
                        <div class="h-full bg-blue-500 transition-all duration-300" style="width: {{ ($directR / 10) * 100 }}%"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ==================== 4. DIRECT TEAM SECTIONS (5 LEFT + 5 RIGHT) ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        
        <!-- ==================== LEFT DIRECT TEAM (5 POSITIONS) ==================== -->
        <div class="bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 rounded-3xl p-5 md:p-6 border-2 border-emerald-500/40 shadow-2xl space-y-4">
            <!-- Section Header -->
            <div class="flex items-center justify-between border-b border-emerald-500/30 pb-3.5">
                <div class="flex items-center gap-2.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-emerald-400 shadow-md shadow-emerald-400/60 animate-pulse"></span>
                    <h3 class="font-black text-base text-emerald-300 uppercase tracking-wider">
                        👈 LEFT DIRECT TEAM
                    </h3>
                </div>
                <div data-left-header-count class="px-3 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 font-black text-xs border border-emerald-500/40">
                    {{ $directL }}/5 Positions Filled
                </div>
            </div>

            <!-- 5 Left Slot Cards (L1 through L5) -->
            <div data-left-slots-container class="space-y-3">
                @for($s = 1; $s <= 5; $s++)
                    @php
                        $slotNode = $treeData['left_slots'][$s] ?? null;
                    @endphp
                    @if($slotNode)
                        @include('binary.partials.node_card', ['node' => $slotNode, 'depth' => 2])
                    @endif
                @endfor
            </div>
        </div>

        <!-- ==================== RIGHT DIRECT TEAM (5 POSITIONS) ==================== -->
        <div class="bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 rounded-3xl p-5 md:p-6 border-2 border-blue-500/40 shadow-2xl space-y-4">
            <!-- Section Header -->
            <div class="flex items-center justify-between border-b border-blue-500/30 pb-3.5">
                <div class="flex items-center gap-2.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-blue-400 shadow-md shadow-blue-400/60 animate-pulse"></span>
                    <h3 class="font-black text-base text-blue-300 uppercase tracking-wider">
                        RIGHT DIRECT TEAM 👉
                    </h3>
                </div>
                <div data-right-header-count class="px-3 py-1 rounded-xl bg-blue-500/20 text-blue-300 font-black text-xs border border-blue-500/40">
                    {{ $directR }}/5 Positions Filled
                </div>
            </div>

            <!-- 5 Right Slot Cards (R1 through R5) -->
            <div data-right-slots-container class="space-y-3">
                @for($s = 1; $s <= 5; $s++)
                    @php
                        $slotNode = $treeData['right_slots'][$s] ?? null;
                    @endphp
                    @if($slotNode)
                        @include('binary.partials.node_card', ['node' => $slotNode, 'depth' => 2])
                    @endif
                @endfor
            </div>
        </div>

    </div>
    @endif

    <!-- ==================== 5. MEMBER DETAILS MODAL / DRAWER ==================== -->
    <div x-show="detailsModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="detailsModalOpen = false" 
             class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 space-y-5 max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-orange-600 text-white font-black text-xl flex items-center justify-center shadow-md">
                        <span x-text="(detailsNode.member_name || 'M').charAt(0)">M</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900" x-text="detailsNode.member_name"></h3>
                        <div class="text-xs text-slate-500 font-mono" x-text="detailsNode.username || detailsNode.member_code"></div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a :href="'/team/' + detailsNode.id" 
                       class="px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-xl transition-all shadow-xs flex items-center gap-1">
                        <span>👥</span> <span>View Team</span>
                    </a>
                    <button @click="detailsModalOpen = false" class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer">&times;</button>
                </div>
            </div>

            <!-- Modal Tab Switcher (Overview vs Investments) -->
            <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                <button type="button" 
                        @click="activeDetailsTab = 'overview'"
                        class="px-4 py-2 text-xs font-black rounded-xl transition-all cursor-pointer"
                        :class="activeDetailsTab === 'overview' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'">
                    📋 Overview & Placement
                </button>
                <button type="button" 
                        @click="activeDetailsTab = 'investments'"
                        class="px-4 py-2 text-xs font-black rounded-xl transition-all cursor-pointer"
                        :class="activeDetailsTab === 'investments' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'">
                    💼 Investment History
                </button>
            </div>

            <!-- Tab 1: Overview & Placement -->
            <div x-show="activeDetailsTab === 'overview'" class="space-y-4">
                <!-- Info Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[10px] font-bold uppercase">Rank</span>
                        <div class="font-black text-slate-900 text-sm mt-0.5" x-text="detailsNode.rank_name || 'Member'"></div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[10px] font-bold uppercase">Placement Slot</span>
                        <div class="font-black text-orange-600 text-sm mt-0.5" x-text="detailsNode.slot_label || 'ROOT'"></div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[10px] font-bold uppercase">Sponsor Name</span>
                        <div class="font-bold text-slate-900 text-sm mt-0.5" x-text="detailsNode.sponsor_name || 'Md. Samim'"></div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[10px] font-bold uppercase">Phone Number</span>
                        <div class="font-bold text-slate-900 mt-0.5" x-text="detailsNode.phone || '01700000000'"></div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[10px] font-bold uppercase">Email Address</span>
                        <div class="font-bold text-slate-900 mt-0.5 truncate" x-text="detailsNode.email || 'N/A'"></div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[10px] font-bold uppercase">Package</span>
                        <div class="font-bold text-slate-900 mt-0.5" x-text="detailsNode.package_name || 'National 120k'"></div>
                    </div>
                </div>

                <!-- SBL Ecosystem Credentials Box -->
                <div class="p-4 bg-slate-900 text-white rounded-2xl border border-slate-800 space-y-2">
                    <div class="text-[11px] font-bold text-orange-300 uppercase tracking-wider flex items-center justify-between">
                        <span>🔐 SBL Ecosystem Login Credentials</span>
                        <span class="text-[10px] text-slate-400 font-normal">Official Portal Login</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                        <div class="flex items-center justify-between bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                            <div>
                                <span class="text-slate-400 text-[10px] font-medium">SBL Password:</span>
                                <div class="font-mono font-bold text-white text-sm" x-text="showDetailsPass ? (detailsNode.password_plain || 'sbl123456') : '••••••••'"></div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="showDetailsPass = !showDetailsPass" class="p-1 text-slate-400 hover:text-white" :title="showDetailsPass ? 'Hide' : 'Show'">
                                    <span x-text="showDetailsPass ? '🙈' : '👁️'">👁️</span>
                                </button>
                                <button type="button" @click="navigator.clipboard.writeText(detailsNode.password_plain || 'sbl123456'); alert('SBL Password copied!');" class="p-1 text-slate-400 hover:text-white" title="Copy">
                                    📋
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                            <div>
                                <span class="text-slate-400 text-[10px] font-medium">SBL TPIN:</span>
                                <div class="font-mono font-bold text-white text-sm" x-text="detailsNode.tpin || '1234'"></div>
                            </div>
                            <button type="button" @click="navigator.clipboard.writeText(detailsNode.tpin || '1234'); alert('TPIN copied!');" class="p-1 text-slate-400 hover:text-white" title="Copy">
                                📋
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Direct Team Counts -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
                    <div class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        Direct Team Positions (Max 10 Direct Slots)
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="p-2.5 bg-white rounded-xl border border-slate-200">
                            <span class="text-slate-400 text-[10px] font-bold">Left Direct</span>
                            <div class="font-black text-emerald-700 text-base" x-text="(detailsNode.direct_left_count || 0) + '/5'"></div>
                        </div>
                        <div class="p-2.5 bg-white rounded-xl border border-slate-200">
                            <span class="text-slate-400 text-[10px] font-bold">Right Direct</span>
                            <div class="font-black text-blue-700 text-base" x-text="(detailsNode.direct_right_count || 0) + '/5'"></div>
                        </div>
                        <div class="p-2.5 bg-white rounded-xl border border-slate-200">
                            <span class="text-slate-400 text-[10px] font-bold">Total Direct</span>
                            <div class="font-black text-purple-700 text-base" x-text="((detailsNode.direct_left_count || 0) + (detailsNode.direct_right_count || 0)) + '/10'"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Investment History -->
            <div x-show="activeDetailsTab === 'investments'" class="space-y-4">
                <div class="flex items-center justify-between bg-amber-50 border border-amber-200/80 p-3.5 rounded-2xl">
                    <div>
                        <span class="text-xs font-bold text-amber-900">Total Own Investment</span>
                        <div class="text-xl font-black text-amber-700" x-text="$store.currency ? $store.currency.format(detailsNode.own_investment || detailsNode.total_investment || 0) : '{{ \App\Services\CurrencyService::format(0) }}'"></div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-black text-xs">
                        Active Portfolio
                    </span>
                </div>

                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Investment History Records</h4>
                    <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden">
                        <template x-for="(c, idx) in (detailsNode.contributions || [])" :key="idx">
                            <div class="p-3 flex items-center justify-between text-xs hover:bg-slate-50">
                                <div>
                                    <div class="font-bold text-slate-900" x-text="c.note || 'Investment Record'"></div>
                                    <div class="text-[11px] text-slate-400" x-text="c.date || 'Active'"></div>
                                </div>
                                <div class="text-right">
                                    <div class="font-black text-emerald-700 text-sm" x-text="$store.currency ? $store.currency.format(parseFloat(c.amount || 0)) : (parseFloat(c.amount || 0) + ' BDT')"></div>
                                    <span class="px-2 py-0.2 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold">Active</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button type="button" 
                            @click="openEditModal(detailsNode); detailsModalOpen = false;"
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition-colors cursor-pointer">
                        ✏️ Edit Member
                    </button>
                    <template x-if="detailsNode.is_target">
                        <form :action="'/team/' + detailsNode.id + '/convert-target'" method="POST" class="inline">
                            @csrf
                            <button type="submit" onclick="return confirm('Do you want to convert this target member to an Active Confirmed Member?');" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer flex items-center gap-1">
                                <span>🎯</span> <span>Convert to Active</span>
                            </button>
                        </form>
                    </template>
                </div>

                <button type="button" 
                        @click="detailsModalOpen = false" 
                        class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition-colors cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== 6. PLACEMENT MODAL (+ ADD MEMBER) ==================== -->
    <div x-show="placementModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="placementModalOpen = false" 
             class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>➕</span> Add Member to Team
                </h3>
                <button @click="placementModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <!-- Placement Slot Indicator -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between text-xs">
                <div>
                    <span class="text-slate-400">Upline Sponsor:</span>
                    <div class="font-bold text-slate-900" x-text="selectedParentName + ' (' + selectedParentCode + ')'"></div>
                </div>
                <div class="text-right">
                    <span class="text-slate-400">Selected Slot:</span>
                    <div>
                        <span class="px-2.5 py-1 rounded-full font-black"
                              :class="selectedBranch === 'LEFT' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'"
                              x-text="(selectedBranch === 'LEFT' ? '👈 LEFT' : '👉 RIGHT') + ' Slot-' + selectedSlotNumber">
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
                                <span>🎯</span> Target / Planned Member (Future Prospect)?
                            </div>
                            <div class="text-[11px] text-purple-700">Mark as a future target prospect before actual onboarding.</div>
                        </div>
                        <input type="checkbox" name="is_target" value="1" x-model="isTargetMember" class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                    </label>

                    <div x-show="isTargetMember" x-transition class="space-y-2 pt-2 border-t border-purple-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-purple-900 mb-1">Target Date</label>
                                <input type="date" name="target_date" class="w-full text-xs bg-white border border-purple-300 rounded-lg p-2 focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-purple-900 mb-1">Target Notes</label>
                                <input type="text" name="target_notes" placeholder="e.g. Planning to join next month" class="w-full text-xs bg-white border border-purple-300 rounded-lg p-2 focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slot Selector -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Branch (Side)</label>
                        <select name="branch" x-model="selectedBranch" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-bold">
                            <option value="LEFT">👈 LEFT TEAM</option>
                            <option value="RIGHT">👉 RIGHT TEAM</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Slot Number (1 - 5)</label>
                        <select name="slot_number" x-model="selectedSlotNumber" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-bold">
                            <option value="1">Slot-1</option>
                            <option value="2">Slot-2</option>
                            <option value="3">Slot-3</option>
                            <option value="4">Slot-4</option>
                            <option value="5">Slot-5</option>
                        </select>
                    </div>
                </div>

                <!-- Member Name & Code -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="member_name" required placeholder="e.g. Md. Karim" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username / Member Code</label>
                        <input type="text" name="member_code" placeholder="Auto-generated or @username" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500 font-mono">
                    </div>
                </div>

                <!-- Phone & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" placeholder="017xxxxxxxx" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" placeholder="karim@sbl.test" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                <!-- Sponsor Name & Package -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sponsor / Upline Name</label>
                        <input type="text" name="sponsor_name" :value="selectedParentName" placeholder="Sponsor Name" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Select Package</label>
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
                        <label class="block text-xs font-bold text-slate-800 mb-1">SBL Ecosystem Password</label>
                        <input type="text" name="password_plain" value="sbl123456" class="w-full text-xs bg-white border border-slate-200 rounded-xl p-2.5 font-mono" placeholder="SBL Ecosystem Pass">
                        <span class="text-[10px] text-slate-500 font-medium">Official SBL Portal login password</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">SBL TPIN (Security PIN)</label>
                        <input type="text" name="tpin" value="1234" class="w-full text-xs bg-white border border-slate-200 rounded-xl p-2.5 font-mono" placeholder="1234">
                        <span class="text-[10px] text-slate-500 font-medium">Account transaction security PIN</span>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="placementModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-all shadow-md active:scale-95 cursor-pointer">
                        Save & Place Member
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== 7. EDIT MEMBER MODAL ==================== -->
    <div x-show="editModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="editModalOpen = false" 
             class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>✏️</span> Edit Member & Investments
                </h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form id="edit-member-form" :action="'/team/' + (editNode.id || '')" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')
                <input type="hidden" name="is_active" value="1">

                <!-- Target Member Status -->
                <div class="p-3 rounded-xl border border-purple-200 bg-purple-50/70 space-y-2">
                    <label class="flex items-center justify-between cursor-pointer">
                        <span class="text-xs font-black text-purple-950 flex items-center gap-1.5">
                            <span>🎯</span> Target / Planned Member
                        </span>
                        <input type="checkbox" name="is_target" value="1" x-model="editNode.is_target" class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                    </label>

                    <div x-show="editNode.is_target" class="space-y-2 pt-2 border-t border-purple-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-purple-900 mb-1">Target Date</label>
                                <input type="date" name="target_date" x-model="editNode.target_date" class="w-full text-xs bg-white border border-purple-300 rounded-lg p-2">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-purple-900 mb-1">Target Notes</label>
                                <input type="text" name="target_notes" x-model="editNode.target_notes" placeholder="e.g. Follow-up next month" class="w-full text-xs bg-white border border-purple-300 rounded-lg p-2">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Name & Code -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="member_name" x-model="editNode.member_name" required class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username / Code</label>
                        <input type="text" name="member_code" x-model="editNode.member_code" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-mono">
                    </div>
                </div>

                <!-- Phone & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" x-model="editNode.phone" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" x-model="editNode.email" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                    </div>
                </div>

                <!-- SBL Ecosystem Login Password & TPIN -->
                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">SBL Ecosystem Password</label>
                        <input type="text" name="password_plain" x-model="editNode.password_plain" class="w-full text-xs bg-white border border-slate-200 rounded-xl p-2.5 font-mono">
                        <span class="text-[10px] text-slate-500 font-medium">SBL Portal login password</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">SBL TPIN</label>
                        <input type="text" name="tpin" x-model="editNode.tpin" class="w-full text-xs bg-white border border-slate-200 rounded-xl p-2.5 font-mono">
                        <span class="text-[10px] text-slate-500 font-medium">Transaction security PIN</span>
                    </div>
                </div>

                <!-- Sponsor Name & Rank -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sponsor Name</label>
                        <input type="text" name="sponsor_name" x-model="editNode.sponsor_name" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Rank</label>
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
                        <label class="text-xs font-bold text-slate-900">💼 Own Investments & Installments</label>
                        <button type="button" @click="addContributionRow()" class="px-2 py-0.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] cursor-pointer">
                            + Add Installment
                        </button>
                    </div>

                    <input type="hidden" name="contributions" :value="JSON.stringify(editNode.contributions)">

                    <template x-for="(c, idx) in editNode.contributions" :key="idx">
                        <div class="flex items-center gap-2 text-xs bg-white p-2 rounded-lg border border-slate-200">
                            <input type="number" x-model="c.amount" @input="recalcTotalContribution()" placeholder="Amount" class="w-24 p-1 text-xs border rounded font-mono font-bold">
                            <input type="date" x-model="c.date" class="w-32 p-1 text-xs border rounded">
                            <input type="text" x-model="c.note" placeholder="Note / Description" class="flex-1 p-1 text-xs border rounded">
                            <button type="button" @click="removeContributionRow(idx)" class="text-rose-600 hover:text-rose-800 font-bold px-1 cursor-pointer">&times;</button>
                        </div>
                    </template>
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                    <button type="button" 
                            x-show="editNode.id"
                            @click="if (confirm('Warning: Are you sure you want to permanently delete this member from the team?')) { const delForm = document.querySelector('#delete-member-form'); delForm.action = '/team/' + editNode.id; delForm.submit(); }"
                            class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl border border-rose-200 transition-colors cursor-pointer flex items-center gap-1">
                        <span>🗑️</span> <span>Delete Member</span>
                    </button>
                    <div class="flex items-center gap-2 ml-auto">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-md active:scale-95 cursor-pointer">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-member-form" action="" method="POST" class="hidden">
                @csrf
                @method('DELETE')
                <input type="hidden" name="cascade" value="1">
            </form>
        </div>
    </div>

</div>
@endsection
