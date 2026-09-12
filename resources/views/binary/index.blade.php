@extends('layouts.app')

@section('page-title', 'Team Explorer')
@section('page-subtitle', 'Explore your member network, branches and placements')

@section('content')
<script>
function teamExplorerData() {
    return {
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
        password_plain: '',
        tpin: '',
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
        notes: '',
        user_id: ''
    },
    selectedParentId: null,
    selectedParentName: '',
    selectedParentCode: '',
    selectedBranch: 'LEFT',
    selectedSlotNumber: 1,
    sponsorName: '',
    isTargetMember: false,
    showDetailsPass: false,
    isEditingNote: false,
    noteSaving: false,
    tempNote: '',
    placementMemberName: '',
    placementMemberCode: '',
    placementPhone: '',
    placementEmail: '',
    placementNotes: '',
    selectedLeadId: '',
    selectedPackage: 'National 120k',
    placementStep: 'form',
    goToPlacementConfirm() {
        if (!this.placementMemberName || !this.placementMemberName.trim()) {
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Please enter member name', type: 'error' } }));
            return;
        }
        this.placementStep = 'confirm';
    },
    crmLeads: @json($crmLeads ?? []),
    onSelectLead(leadId) {
        if (!leadId) return;
        const lead = this.crmLeads.find(l => String(l.id) === String(leadId));
        if (lead) {
            this.placementMemberName = lead.name || '';
            this.placementPhone = lead.mobile || '';
            this.placementEmail = lead.email || '';
            if (lead.profession_or_business || lead.location) {
                this.placementNotes = [lead.profession_or_business, lead.location].filter(Boolean).join(' • ');
            }
        }
    },
    init() { 
        this.$watch('detailsModalOpen', open => { if (!open) { this.credentials = {}; this.showDetailsPass = false; this.isEditingNote = false; } }); 
        window.copyToClipboard = (text, label) => this.copyToClipboard(text, label);
        @if($errors->any())
            this.placementModalOpen = true;
        @endif
    },
    openAddMemberModal(parentId = null, parentName = '', parentCode = '') {
        const rootId = {{ $treeData['root']->id ?? 'null' }};
        const rootName = @json($treeData['root']->member_name ?? '');
        const rootCode = @json($treeData['root']->member_code ?? '');

        this.selectedParentId = parentId || rootId;
        this.selectedParentName = parentName || rootName;
        this.selectedParentCode = parentCode || rootCode;
        this.selectedBranch = 'LEFT';
        this.selectedSlotNumber = 1;
        this.sponsorName = parentName || rootName;
        this.isTargetMember = false;
        this.selectedLeadId = '';
        this.placementMemberName = '';
        this.placementMemberCode = '';
        this.placementPhone = '';
        this.placementEmail = '';
        this.placementNotes = '';
        this.placementStep = 'form';
        this.placementModalOpen = true;
    },
    copyToClipboard(text, label) {
        if (!text) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: (label || 'Item') + ' copied to clipboard!', type: 'success' } }));
            }).catch(() => {
                this.fallbackCopy(text, label);
            });
        } else {
            this.fallbackCopy(text, label);
        }
    },
    fallbackCopy(text, label) {
        const el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        window.dispatchEvent(new CustomEvent('notify', { detail: { message: (label || 'Item') + ' copied to clipboard!', type: 'success' } }));
    },
    async saveMemberNote() {
        if (!this.detailsNode.id) return;
        this.noteSaving = true;
        try {
            const res = await fetch('/team/' + this.detailsNode.id + '/notes', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ notes: this.tempNote })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Could not save note');
            this.detailsNode.notes = this.tempNote;
            this.isEditingNote = false;
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Member note saved successfully!', type: 'success' } }));
        } catch (e) {
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: e.message || 'Failed to save note', type: 'error' } }));
        } finally {
            this.noteSaving = false;
        }
    },
    credentials: {},
    credentialsLoading: false,
    async toggleCredentials() {
        if (this.showDetailsPass) { this.showDetailsPass = false; this.credentials = {}; return; }
        this.credentialsLoading = true;
        const memberId = this.detailsNode.id;
        try {
            const response = await fetch('/team/' + memberId + '/credentials', {headers: {Accept: 'application/json'}, cache: 'no-store'});
            if (!response.ok) throw new Error('Could not load credentials. Please try again.');
            const credentials = await response.json();
            if (this.detailsModalOpen && this.detailsNode.id === memberId) {
                this.credentials = credentials;
                this.showDetailsPass = true;
            }
        } catch (error) { this.$dispatch('notify', {message: error.message, type: 'error'}); }
        finally { this.credentialsLoading = false; }
    },
    getNodeData(node) {
        if (!node) return {};
        const id = (typeof node === 'object' && node !== null) ? node.id : node;
        let base = (typeof node === 'object' && node !== null) ? { ...node } : { id: node };
        const dataNodes = (window.DATA && Array.isArray(window.DATA.nodes)) ? window.DATA.nodes : (typeof DATA !== 'undefined' && Array.isArray(DATA.nodes) ? DATA.nodes : null);
        if (dataNodes) {
            const found = dataNodes.find(n => String(n.id) === String(id));
            if (found) {
                base = Object.assign({}, base, found);
            }
        }
        return base;
    },
    openDetailsModal(node) {
        this.detailsNode = this.getNodeData(node);
        this.showDetailsPass = false;
        this.credentials = {};
        this.activeDetailsTab = 'overview';
        this.tempNote = this.detailsNode.notes || this.detailsNode.target_notes || '';
        this.isEditingNote = false;
        this.detailsModalOpen = true;
    },
    openPlacementModal(parentId, parentName, parentCode, branch, slotNumber) {
        this.selectedParentId = parentId;
        this.selectedParentName = parentName;
        this.selectedParentCode = parentCode;
        this.selectedBranch = branch || 'LEFT';
        this.selectedSlotNumber = slotNumber || 1;
        this.sponsorName = parentName || '';
        this.isTargetMember = false;
        this.selectedLeadId = '';
        this.placementMemberName = '';
        this.placementMemberCode = '';
        this.placementPhone = '';
        this.placementEmail = '';
        this.placementNotes = '';
        this.placementStep = 'form';
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
            try {
                contribs = typeof liveNode.contributions === 'string' ? JSON.parse(liveNode.contributions) : liveNode.contributions;
            } catch (e) { contribs = []; }
        }
        if (!contribs || !Array.isArray(contribs) || contribs.length === 0) {
            contribs = [
                { amount: liveNode.total_investment || liveNode.point_value || 100, date: (liveNode.created_at ? String(liveNode.created_at).slice(0, 10) : new Date().toISOString().slice(0, 10)), note: liveNode.package_name || 'Initial' }
            ];
        }

        this.editNode = {
            id: liveNode.id,
            member_name: liveNode.member_name || '',
            member_code: liveNode.member_code || '',
            phone: liveNode.phone || '',
            email: liveNode.email || '',
            password_plain: '',
            tpin: '',
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
            notes: liveNode.notes || liveNode.target_notes || '',
            user_id: liveNode.user_id || ''
        };
        this.editModalOpen = true;
        this.$nextTick(() => {
            const form = document.querySelector('#edit-member-form');
            if (form && liveNode.id) {
                form.action = '/team/' + liveNode.id;
            }
        });
    },
    async submitEditMember(e) {
        e.preventDefault();
        this.recalcTotalContribution();
        const form = e.target;
        const ci = form.querySelector('input[name=contributions]');
        if (ci) ci.value = JSON.stringify(this.editNode.contributions);
        const targetUrl = '/team/' + this.editNode.id;
        form.action = targetUrl;
        
        if (window.DATA && Array.isArray(window.DATA.nodes)) {
            const idx = window.DATA.nodes.findIndex(n => String(n.id) === String(this.editNode.id));
            if (idx !== -1) {
                window.DATA.nodes[idx] = Object.assign({}, window.DATA.nodes[idx], {
                    member_name: this.editNode.member_name,
                    member_code: this.editNode.member_code,
                    phone: this.editNode.phone,
                    email: this.editNode.email,
                    package_name: this.editNode.package_name,
                    rank_name: this.editNode.rank_name,
                    sponsor_id: this.editNode.sponsor_id,
                    sponsor_name: this.editNode.sponsor_name,
                    point_value: this.editNode.point_value,
                    is_target: this.editNode.is_target ? 1 : 0,
                    target_date: this.editNode.target_date,
                    target_notes: this.editNode.target_notes,
                    notes: this.editNode.notes,
                    contributions: JSON.stringify(this.editNode.contributions)
                });
            }
        }
        
        try {
            const formData = new FormData(form);
            const res = await fetch(targetUrl, {
                method: 'POST',
                body: formData
            });
            this.editModalOpen = false;
            window.location.reload();
        } catch (err) {
            form.submit();
        }
    }
};
}
</script>

<div class="space-y-6" x-data="teamExplorerData()">

@if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 text-sm font-semibold flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
        <button @click="$el.parentElement.remove()" class="text-rose-700 hover:text-rose-900 font-bold cursor-pointer">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2 mb-1">
            <span>⚠️</span>
            <span>Validation Error: Please review the member details below.</span>
        </div>
        <ul class="list-disc list-inside text-xs font-normal ml-6 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="app-panel space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <a class="btn-secondary" href="{{ route('team.index', ['owner_id' => $ownerId]) }}" data-en="🏠 My Team" data-bn="🏠 আমার টিম">
                    <span>🏠</span> <span data-en="My Team" data-bn="আমার টিম">My Team</span>
                </a>
                @if(!empty($treeData['parent_node']))
                    <a class="btn-secondary" href="{{ route('team.show', ['memberId' => $treeData['parent_node']->id, 'owner_id' => $ownerId]) }}">
                        <span>←</span> <span data-en="Back" data-bn="পূর্ববর্তী">Back</span>
                    </a>
                @endif
                <button type="button" 
                        @click="openAddMemberModal()" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all cursor-pointer">
                    <span>➕</span>
                    <span data-en="Add Member" data-bn="মেম্বার যুক্ত করুন">Add Member</span>
                </button>
            </div>
            <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-semibold">
                <a class="px-3 py-2 rounded-lg {{ $viewMode === 'builder' ? 'bg-white text-orange-700 shadow-sm' : 'text-slate-600' }}" href="{{ route('team.index', ['view' => 'builder', 'owner_id' => $ownerId, 'node_id' => $treeData['root']->id ?? null]) }}" title="Visual Binary Team Explorer" data-en="⚡ Team Explorer" data-bn="⚡ টিম এক্সপ্লোরার">
                    <span data-en="⚡ Team Explorer" data-bn="⚡ টিম এক্সপ্লোরার">⚡ Team Explorer</span>
                </a>
                <a class="px-3 py-2 rounded-lg {{ $viewMode === 'mindmap' ? 'bg-white text-orange-700 shadow-sm' : 'text-slate-600' }}" href="{{ route('team.index', ['view' => 'mindmap', 'owner_id' => $ownerId, 'node_id' => $treeData['root']->id ?? null]) }}" title="Mindmap Canvas Tree" data-en="🗺️ Mindmap" data-bn="🗺️ মাইন্ডম্যাপ">
                    <span data-en="🗺️ Mindmap" data-bn="🗺️ মাইন্ডম্যাপ">🗺️ Mindmap</span>
                </a>
                <a class="px-3 py-2 rounded-lg {{ $viewMode === 'table' ? 'bg-white text-orange-700 shadow-sm' : 'text-slate-600' }}" href="{{ route('team.index', ['view' => 'table', 'owner_id' => $ownerId]) }}" title="Directory List" data-en="📋 Directory" data-bn="📋 ডিরেক্টরি">
                    <span data-en="📋 Directory" data-bn="📋 ডিরেক্টরি">📋 Directory</span>
                </a>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <form action="{{ route('team.search') }}" method="GET" class="flex flex-1 min-w-0 gap-2 relative" x-data="memberLiveSearch()">
                <input type="hidden" name="owner_id" value="{{ $ownerId }}">
                <div class="relative flex-1 min-w-0">
                    <input type="search" 
                           name="search" 
                           x-model="searchQuery" 
                           @input="onInput()" 
                           @keydown.escape="open = false" 
                           @focus="if(searchQuery.length > 0 && results.length > 0) open = true" 
                           aria-label="Search members" 
                           placeholder="Search member name, code or phone (live)..." 
                           list="team_search_datalist" 
                           class="min-w-0 w-full rounded-xl border-slate-200 text-sm pl-9 pr-8" 
                           autocomplete="off" 
                           required>
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <button type="button" x-show="searchQuery" @click="searchQuery = ''; results = []; open = false" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold cursor-pointer">✕</button>

                    <!-- Live Member Dropdown Results -->
                    <div x-show="open && results.length > 0" 
                         @click.away="open = false" 
                         class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-72 overflow-y-auto divide-y divide-slate-100" 
                         x-cloak>
                        <template x-for="item in results" :key="item.id">
                            <a :href="item.url" class="flex items-center justify-between px-3.5 py-2.5 hover:bg-orange-50 transition-colors">
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-slate-900 text-xs flex items-center gap-2">
                                        <span x-text="item.member_name"></span>
                                        <span class="px-1.5 py-0.5 text-[10px] bg-slate-100 text-slate-700 rounded font-mono font-semibold" x-text="item.member_code"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 truncate" x-text="(item.phone ? '📞 ' + item.phone : '') + (item.rank_title ? ' • ' + item.rank_title : '') + (item.side ? ' • ' + item.side.toUpperCase() : '')"></div>
                                </div>
                                <span class="text-xs text-orange-600 font-semibold flex-shrink-0 ml-2">View Tree →</span>
                            </a>
                        </template>
                    </div>
                </div>
                <datalist id="team_search_datalist">@foreach($allNodes as $an)<option value="{{ $an->member_code }}">{{ $an->member_name }}</option>@endforeach</datalist>
            </form>
            @if($isSuperAdmin && count($users))
            <form action="{{ route('team.index') }}" method="GET" class="sm:max-w-56">
                <select name="owner_id" aria-label="Team workspace" onchange="this.form.submit()" class="w-full rounded-xl border-slate-200 text-sm">
                    @foreach($users as $u)<option value="{{ $u->id }}" @selected((int)$ownerId === (int)$u->id)>{{ $u->name }}</option>@endforeach
                </select>
            </form>
            @endif
        </div>
    </div>

    <!-- ==================== 2. BREADCRUMB NAVIGATION TRAIL ==================== -->
    @if($viewMode !== 'builder' && !empty($treeData['breadcrumbs']) && count($treeData['breadcrumbs']) > 0)
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
                <div class="flex items-center gap-3">
                    <h3 class="text-base font-bold text-slate-900">10-Slot Member Directory</h3>
                    <button type="button" 
                            @click="openAddMemberModal()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all cursor-pointer">
                        <span>➕</span>
                        <span>Add Member</span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Overview of all team members, placement positions, and network statistics.</p>
            </div>

            <!-- Search Form -->
            <form action="{{ route('team.index') }}" method="GET" class="w-full sm:w-96 flex items-center gap-2" x-data="{ dirSearch: '{{ addslashes(request('search', '')) }}' }">
                <input type="hidden" name="view" value="table">
                @if(request('owner_id'))
                <input type="hidden" name="owner_id" value="{{ request('owner_id') }}">
                @endif
                <div class="relative flex-1">
                    <input aria-label="Search name, code, phone..." type="text" 
                           name="search" 
                           x-model="dirSearch"
                           @input="window.filterDirectoryLive ? window.filterDirectoryLive(dirSearch) : null"
                           value="{{ request('search') }}"
                           placeholder="Search name, code, phone... (live)" 
                           class="w-full pl-9 pr-8 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <button type="button" x-show="dirSearch" @click="dirSearch = ''; window.filterDirectoryLive('');" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold cursor-pointer">✕</button>
                </div>
                <span id="directory-live-counter" class="hidden px-2 py-1 text-[11px] font-semibold bg-orange-100 text-orange-800 rounded-lg whitespace-nowrap"></span>
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
                <tbody data-binary-table-body class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($members as $member)
                    <tr data-node-id="{{ $member->id }}" class="hover:bg-slate-50/60 transition-colors">
                        <!-- Member Profile -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl {{ $member->is_target ? 'bg-purple-900 text-purple-200' : 'bg-slate-900 text-orange-400' }} font-black flex items-center justify-center text-xs flex-shrink-0 shadow-xs">
                                    {{ substr($member->member_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 hover:text-orange-600 transition-colors cursor-pointer"
                                         @click="openDetailsModal({{ $member->id }})">
                                        {{ $member->member_name }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono flex items-center gap-1.5 mt-0.5">
                                        <span>{{ $member->member_code }}</span>
                                        <button type="button" @click.stop="copyToClipboard('{{ $member->member_code }}', 'Member Code')" title="Copy Code" class="text-slate-300 hover:text-orange-600 p-0.5 transition-colors cursor-pointer">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                        @if($member->phone)
                                        <span>•</span>
                                        <span>{{ $member->phone }}</span>
                                        <button type="button" @click.stop="copyToClipboard('{{ $member->phone }}', 'Phone number')" title="Copy Phone" class="text-slate-300 hover:text-orange-600 p-0.5 transition-colors cursor-pointer">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                        @endif
                                        @if(!empty($member->notes))
                                        <span title="{{ $member->notes }}" class="cursor-help text-amber-600">📝</span>
                                        @endif
                                    </div>
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
                                    <span>👥</span> <span>View</span>
                                </a>

                                <button type="button" 
                                        @click="openDetailsModal({{ $member->id }})"
                                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer" 
                                        title="View member details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </button>

                                <button type="button" 
                                        @click="openEditModal({{ $member->id }})"
                                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer" 
                                        title="Edit member">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>

                                @if($member->parent_id !== null)
                                <form action="{{ route('team.destroy', $member->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete member {{ addslashes($member->member_name) }}? Any children will be safely reattached.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors cursor-pointer" title="Delete member">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                                @endif
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

    @elseif($viewMode === 'mindmap')
    @include('binary.partials.mindmap')
    @else
    @include('binary.partials.builder')
    @endif

    <!-- ==================== 5. MEMBER DETAILS MODAL / DRAWER ==================== -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="detailsModalOpen" 
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
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900" x-text="detailsNode.member_name"></h3>
                            <button type="button" @click="copyToClipboard(detailsNode.member_name, 'Member Name')" title="Copy Name" class="text-slate-400 hover:text-orange-600 p-0.5 transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 font-mono mt-0.5">
                            <span x-text="detailsNode.username || detailsNode.member_code"></span>
                            <button type="button" @click="copyToClipboard(detailsNode.username || detailsNode.member_code, 'Member Code')" title="Copy Code" class="text-slate-400 hover:text-orange-600 p-0.5 transition-colors cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
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
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[10px] font-bold uppercase">Sponsor Name</span>
                            <button type="button" x-show="detailsNode.sponsor_name" @click="copyToClipboard(detailsNode.sponsor_name, 'Sponsor Name')" title="Copy Sponsor" class="text-slate-400 hover:text-orange-600 p-0.5 cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                        <div class="font-bold text-slate-900 text-sm mt-0.5 truncate" x-text="detailsNode.sponsor_name || 'Md. Samim'"></div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[10px] font-bold uppercase">Phone Number</span>
                            <button type="button" x-show="detailsNode.phone" @click="copyToClipboard(detailsNode.phone, 'Phone Number')" title="Copy Phone" class="text-slate-400 hover:text-orange-600 p-0.5 cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                        <div class="font-bold text-slate-900 mt-0.5" x-text="detailsNode.phone || 'Not provided'"></div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[10px] font-bold uppercase">Email Address</span>
                            <button type="button" x-show="detailsNode.email" @click="copyToClipboard(detailsNode.email, 'Email Address')" title="Copy Email" class="text-slate-400 hover:text-orange-600 p-0.5 cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                        <div class="font-bold text-slate-900 mt-0.5 truncate" x-text="detailsNode.email || 'N/A'"></div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[10px] font-bold uppercase">Package</span>
                        <div class="font-bold text-slate-900 mt-0.5" x-text="detailsNode.package_name || 'National 120k'"></div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold">Member credentials</h3>
                        <button type="button" class="btn-secondary" :disabled="credentialsLoading" @click="toggleCredentials()" x-text="credentialsLoading ? 'Loading…' : (showDetailsPass ? 'Hide' : 'Show')"></button>
                    </div>
                    <p class="text-xs text-slate-500">Saved credentials are shown only when requested.</p>
                    <dl x-show="showDetailsPass" x-cloak class="grid grid-cols-2 gap-3 text-sm">
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60">
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500 text-xs font-semibold">Password</dt>
                                <button type="button" x-show="credentials.password_plain" @click="copyToClipboard(credentials.password_plain, 'Password')" title="Copy Password" class="text-slate-400 hover:text-orange-600 p-0.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </div>
                            <dd class="break-all font-mono font-bold text-slate-900 mt-0.5" x-text="credentials.password_plain || 'Not saved'"></dd>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60">
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500 text-xs font-semibold">TPIN</dt>
                                <button type="button" x-show="credentials.tpin" @click="copyToClipboard(credentials.tpin, 'TPIN')" title="Copy TPIN" class="text-slate-400 hover:text-orange-600 p-0.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </div>
                            <dd class="break-all font-mono font-bold text-slate-900 mt-0.5" x-text="credentials.tpin || 'Not saved'"></dd>
                        </div>
                    </dl>
                </div>

                <!-- Member Notes Box (View / Add / Edit inline) -->
                <div class="p-4 bg-amber-50/50 rounded-2xl border border-amber-200/80 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                            <span>📝</span> Member Notes
                        </span>
                        <button type="button" 
                                @click="if(!isEditingNote) { tempNote = detailsNode.notes || detailsNode.target_notes || ''; } isEditingNote = !isEditingNote" 
                                class="text-xs font-bold text-orange-700 hover:text-orange-800 transition-colors cursor-pointer"
                                x-text="isEditingNote ? 'Cancel' : (detailsNode.notes ? 'Edit Note' : '+ Add Note')">
                        </button>
                    </div>

                    <!-- Viewing Note -->
                    <div x-show="!isEditingNote">
                        <template x-if="detailsNode.notes || detailsNode.target_notes">
                            <div class="text-xs text-slate-700 bg-white p-3 rounded-xl border border-amber-200 whitespace-pre-line leading-relaxed" 
                                 x-text="detailsNode.notes || detailsNode.target_notes"></div>
                        </template>
                        <template x-if="!detailsNode.notes && !detailsNode.target_notes">
                            <p class="text-xs text-slate-400 italic">No notes added for this member yet. Click '+ Add Note' to add one.</p>
                        </template>
                    </div>

                    <!-- Editing Note Inline -->
                    <div x-show="isEditingNote" class="space-y-2" x-cloak>
                        <textarea x-model="tempNote" rows="3" class="w-full text-xs p-2.5 bg-white border border-amber-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none" placeholder="Write any notes, discussion summary, or goals for this member..."></textarea>
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="isEditingNote = false" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg cursor-pointer">Cancel</button>
                            <button type="button" @click="saveMemberNote()" :disabled="noteSaving" class="px-4 py-1.5 text-xs font-bold text-white bg-orange-600 hover:bg-orange-700 rounded-lg shadow-xs transition-all active:scale-95 flex items-center gap-1 cursor-pointer">
                                <span x-show="noteSaving">Saving…</span>
                                <span x-show="!noteSaving">Save Note</span>
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
                    <template x-if="detailsNode.parent_id">
                        <form :action="'/team/' + detailsNode.id" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this member? Any children will be safely reattached.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl transition-colors cursor-pointer flex items-center gap-1">
                                <span>🗑️</span> <span>Delete Member</span>
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
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="placementModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs"
         x-cloak>
        <div @click.outside="placementModalOpen = false" 
             class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span>➕</span> Add Member to Team
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Place a new or target member into your binary team genealogy.</p>
                </div>
                <button @click="placementModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            @if($errors->any())
            <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-xs space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-rose-800">
                    <span>⚠️</span> <span>Please check the following:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-rose-700 text-[11px]">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('binary.store') }}" method="POST" class="space-y-4">
                @csrf

                <div x-show="placementStep === 'form'" class="space-y-4">

                <!-- ==================== CRM LEADS QUICK IMPORT ==================== -->
                @if(isset($crmLeads) && count($crmLeads) > 0)
                <div class="p-3.5 bg-gradient-to-r from-amber-50 to-orange-50/60 rounded-2xl border border-amber-200/90 space-y-1.5 shadow-2xs">
                    <label class="block text-xs font-black text-amber-950 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <span>⚡</span>
                            <span>CRM Leads থেকে দ্রুত নির্বাচন করুন (Auto-Fill)</span>
                        </span>
                        <span class="text-[10px] text-amber-800 bg-amber-200/60 px-2 py-0.5 rounded-full font-bold">এক ক্লিকে ফিল</span>
                    </label>
                    <select x-model="selectedLeadId" 
                            @change="onSelectLead($event.target.value)"
                            class="w-full text-xs bg-white border border-amber-300 rounded-xl p-2.5 font-bold text-slate-800 shadow-xs focus:ring-2 focus:ring-amber-500 cursor-pointer">
                        <option value="">-- Select from CRM Leads (বাছাই করতে ক্লিক করুন) --</option>
                        @foreach($crmLeads as $cLead)
                            <option value="{{ $cLead->id }}">
                                {{ $cLead->name }} {{ $cLead->mobile ? '• '.$cLead->mobile : '' }} {{ $cLead->location ? '• '.$cLead->location : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-amber-800/80">লিড সিলেক্ট করলে মেম্বারের নাম, ফোন ও ইমেইল নিজে থেকেই পূরণ হয়ে যাবে।</p>
                </div>
                @endif

                <!-- ==================== PLACEMENT POSITION VISUAL PREVIEW ==================== -->
                <div class="p-3 bg-slate-100 rounded-2xl border border-slate-200 flex flex-wrap items-center justify-between text-xs font-bold gap-2">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-orange-600">📍 প্লেসমেন্ট টার্গেট:</span>
                        <span class="text-slate-900" x-text="selectedParentName || 'Root'"></span>
                        <span class="text-slate-400">›</span>
                        <span class="px-2 py-0.5 rounded text-[11px] font-black" 
                              :class="selectedBranch === 'LEFT' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'"
                              x-text="selectedBranch === 'LEFT' ? '👈 LEFT TEAM' : '👉 RIGHT TEAM'"></span>
                        <span class="text-slate-400">›</span>
                        <span class="bg-white px-2 py-0.5 rounded border border-slate-300 font-mono" x-text="'Slot ' + selectedSlotNumber"></span>
                    </div>
                </div>

                <!-- ==================== MEMBER CONNECTOR (PLACEMENT UPLINE) ==================== -->
                <div class="p-4 bg-orange-50/60 rounded-2xl border border-orange-200 space-y-3">
                    <div>
                        <label class="block text-xs font-black text-slate-900 mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-orange-950">
                                <span>🔗</span>
                                <span>Member Connector (Placement Upline) <span class="text-rose-500">*</span></span>
                            </span>
                            <span class="text-[10px] text-orange-700 font-semibold bg-orange-100 px-2 py-0.5 rounded-full">Direct Upline</span>
                        </label>
                        <select name="parent_id" 
                                x-model="selectedParentId" 
                                @change="
                                    const opt = $el.options[$el.selectedIndex];
                                    selectedParentName = opt.dataset.name || '';
                                    selectedParentCode = opt.dataset.code || '';
                                    if (!sponsorName) sponsorName = selectedParentName;
                                "
                                required 
                                class="w-full text-xs bg-white border border-orange-300 rounded-xl p-2.5 focus:ring-2 focus:ring-orange-500 font-bold text-slate-800 shadow-xs">
                            <option value="">-- Select Member Connector --</option>
                            @foreach($allNodes as $nodeOption)
                                <option value="{{ $nodeOption->id }}" 
                                        data-name="{{ $nodeOption->member_name }}" 
                                        data-code="{{ $nodeOption->member_code }}"
                                        :selected="selectedParentId == {{ $nodeOption->id }}">
                                    {{ $nodeOption->member_code ?: 'SBL-'.$nodeOption->id }} — {{ $nodeOption->member_name }} ({{ $nodeOption->rank_name ?? 'Member' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">
                            The new member will be placed under this connector in the specified branch and slot.
                        </p>
                    </div>

                    <!-- Connector Placement Slot Selector (Branch & Slot 1-5) -->
                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-orange-200/70">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Placement Side (Branch)</label>
                            <select name="branch" x-model="selectedBranch" class="w-full text-xs bg-white border border-slate-200 rounded-xl p-2.5 font-bold text-slate-800 shadow-xs">
                                <option value="LEFT">👈 LEFT TEAM</option>
                                <option value="RIGHT">👉 RIGHT TEAM</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Slot Number (Slot 1-5)</label>
                            <select name="slot_number" x-model="selectedSlotNumber" class="w-full text-xs bg-white border border-slate-200 rounded-xl p-2.5 font-bold text-slate-800 shadow-xs">
                                <option value="1">Slot-1</option>
                                <option value="2">Slot-2</option>
                                <option value="3">Slot-3</option>
                                <option value="4">Slot-4</option>
                                <option value="5">Slot-5</option>
                            </select>
                        </div>
                    </div>
                </div>

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

                <!-- Member Name & Code -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="member_name" x-model="placementMemberName" required placeholder="e.g. Md. Karim" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username / Member Code</label>
                        <input type="text" name="member_code" x-model="placementMemberCode" placeholder="Auto-generated or @username" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500 font-mono">
                    </div>
                </div>

                <!-- Phone & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" x-model="placementPhone" placeholder="017xxxxxxxx" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" x-model="placementEmail" placeholder="karim@sbl.test" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                <!-- Sponsor Name & Package -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                            <span>Sponsor (Referrer)</span>
                            <span class="text-[10px] text-slate-400 font-normal">Referrer</span>
                        </label>
                        <input type="text" 
                               name="sponsor_name" 
                               x-model="sponsorName" 
                               list="sponsors_datalist" 
                               placeholder="Sponsor Name or Code" 
                               class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500">
                        <datalist id="sponsors_datalist">
                            @foreach($allNodes as $sNode)
                                <option value="{{ $sNode->member_name }}">{{ $sNode->member_code }}</option>
                                <option value="{{ $sNode->member_code }}">{{ $sNode->member_name }}</option>
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Select Package</label>
                        <select name="package_name" x-model="selectedPackage" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500 font-medium">
                            @foreach($packages as $pkg)
                            <option value="{{ $pkg['name'] }}">{{ $pkg['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- SBL Ecosystem Login Password & TPIN -->
                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                    <div x-data="{ showPass: false }">
                        <label class="block text-xs font-bold text-slate-800 mb-1">SBL Ecosystem Password</label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" type="password" autocomplete="new-password" name="password_plain" class="w-full text-xs bg-white border border-slate-200 rounded-xl pl-2.5 pr-8 py-2.5 font-mono" placeholder="SBL Ecosystem Pass">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" aria-label="Toggle password visibility">
                                <svg x-show="!showPass" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPass" x-cloak class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <span class="text-[10px] text-slate-500 font-medium">Official SBL Portal login password</span>
                    </div>
                    <div x-data="{ showTpin: false }">
                        <label class="block text-xs font-bold text-slate-800 mb-1">SBL TPIN (Security PIN)</label>
                        <div class="relative">
                            <input :type="showTpin ? 'text' : 'password'" type="password" autocomplete="off" name="tpin" class="w-full text-xs bg-white border border-slate-200 rounded-xl pl-2.5 pr-8 py-2.5 font-mono" placeholder="1234">
                            <button type="button" @click="showTpin = !showTpin" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" aria-label="Toggle TPIN visibility">
                                <svg x-show="!showTpin" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showTpin" x-cloak class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <span class="text-[10px] text-slate-500 font-medium">Account transaction security PIN</span>
                    </div>
                </div>

                <!-- Member Notes -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Member Notes</label>
                    <textarea name="notes" x-model="placementNotes" rows="2" placeholder="Any special notes, background info, or goals for this member..." class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="placementModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer" data-en="Cancel" data-bn="বাতিল">
                        <span data-en="Cancel" data-bn="বাতিল">Cancel</span>
                    </button>
                    <button type="button" @click="goToPlacementConfirm()" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-all shadow-md active:scale-95 cursor-pointer flex items-center gap-1.5" data-en="Review & Place Member →" data-bn="রিভিউ ও প্লেসমেন্ট নিশ্চিত করুন →">
                        <span data-en="Review & Place Member" data-bn="রিভিউ ও প্লেসমেন্ট নিশ্চিত করুন">Review & Place Member</span>
                        <span>→</span>
                    </button>
                </div>
            </div>

            <!-- ==================== PLACEMENT CONFIRMATION REVIEW STEP ==================== -->
            <div x-show="placementStep === 'confirm'" class="space-y-4" x-cloak>
                <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 space-y-1">
                    <h4 class="text-xs font-black text-amber-950 flex items-center gap-1.5">
                        <span>🛡️</span>
                        <span data-en="Placement Confirmation Review" data-bn="প্লেসমেন্ট কনফার্মেশন রিভিউ">Placement Confirmation Review</span>
                    </h4>
                    <p class="text-[11px] text-amber-800" data-en="Please review the placement position carefully before submitting to avoid errors." data-bn="ভুল প্লেসমেন্ট এড়াতে সাবমিট করার আগে তথ্যগুলো ভালোভাবে যাচাই করুন।">
                        Please review the placement position carefully before submitting to avoid errors.
                    </p>
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <span class="text-slate-500 font-semibold" data-en="New Member:" data-bn="নতুন সদস্য:">New Member:</span>
                        <div class="text-right">
                            <span class="font-black text-slate-900" x-text="placementMemberName"></span>
                            <span class="block text-[10px] text-slate-400 font-mono" x-text="placementMemberCode || 'Auto-generated'"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <span class="text-slate-500 font-semibold" data-en="Placement Connector:" data-bn="প্লেসমেন্ট কানেক্টর:">Placement Connector:</span>
                        <span class="font-bold text-slate-900" x-text="selectedParentName || 'Root'"></span>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <span class="text-slate-500 font-semibold" data-en="Position Assigned:" data-bn="নির্ধারিত পজিশন:">Position Assigned:</span>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-black"
                              :class="selectedBranch === 'LEFT' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'"
                              x-text="(selectedBranch === 'LEFT' ? '👈 LEFT' : '👉 RIGHT') + ' • Slot ' + selectedSlotNumber"></span>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <span class="text-slate-500 font-semibold" data-en="Sponsor (Referrer):" data-bn="স্পন্সর (রেফারার):">Sponsor (Referrer):</span>
                        <span class="font-bold text-slate-900" x-text="sponsorName || selectedParentName || 'Self'"></span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-semibold" data-en="Package:" data-bn="প্যাকেজ:">Package:</span>
                        <span class="font-bold text-orange-600" x-text="selectedPackage"></span>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between gap-2 border-t border-slate-100">
                    <button type="button" @click="placementStep = 'form'" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer" data-en="← Edit Details" data-bn="← তথ্য পরিবর্তন">
                        ← Edit Details
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-md active:scale-95 cursor-pointer flex items-center gap-1.5" data-en="Confirm & Place Member ✓" data-bn="নিশ্চিত ও প্লেস করুন ✓">
                        <span>Confirm & Place Member ✓</span>
                    </button>
                </div>
            </div>
            </form>
        </div>
    </div>

    <!-- ==================== 7. EDIT MEMBER MODAL ==================== -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="editModalOpen" 
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

            <form id="edit-member-form" :action="'/team/' + (editNode.id || '')" method="POST" @submit="submitEditMember($event)" class="space-y-3.5">
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
                    <div x-data="{ showPass: false }">
                        <label class="block text-xs font-bold text-slate-800 mb-1">SBL Ecosystem Password</label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" type="password" autocomplete="new-password" placeholder="Leave blank to keep saved value" name="password_plain" x-model="editNode.password_plain" class="w-full text-xs bg-white border border-slate-200 rounded-xl pl-2.5 pr-8 py-2.5 font-mono">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" aria-label="Toggle password visibility">
                                <svg x-show="!showPass" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPass" x-cloak class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <span class="text-[10px] text-slate-500 font-medium">SBL Portal login password</span>
                    </div>
                    <div x-data="{ showTpin: false }">
                        <label class="block text-xs font-bold text-slate-800 mb-1">SBL TPIN</label>
                        <div class="relative">
                            <input :type="showTpin ? 'text' : 'password'" type="password" autocomplete="new-password" placeholder="Leave blank to keep saved value" name="tpin" x-model="editNode.tpin" class="w-full text-xs bg-white border border-slate-200 rounded-xl pl-2.5 pr-8 py-2.5 font-mono">
                            <button type="button" @click="showTpin = !showTpin" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" aria-label="Toggle TPIN visibility">
                                <svg x-show="!showTpin" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showTpin" x-cloak class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs bg-white p-2 rounded-lg border border-slate-200">
                            <input type="number" min="0" step="0.01" aria-label="Installment amount" x-model="c.amount" @input="recalcTotalContribution()" placeholder="Amount" class="w-full min-w-0 p-2 text-xs border rounded font-mono font-bold">
                            <input type="date" aria-label="Installment date" x-model="c.date" class="w-full min-w-0 p-2 text-xs border rounded">
                            <input type="text" aria-label="Installment note" x-model="c.note" placeholder="Note / Description" class="w-full min-w-0 p-2 text-xs border rounded">
                            <button type="button" @click="removeContributionRow(idx)" class="text-rose-700 font-semibold px-2">Remove installment</button>
                        </div>
                    </template>
                </div>

                <!-- Member Notes -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Member Notes</label>
                    <textarea name="notes" x-model="editNode.notes" rows="2.5" placeholder="Any special notes, background info, or goals for this member..." class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-orange-500"></textarea>
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
