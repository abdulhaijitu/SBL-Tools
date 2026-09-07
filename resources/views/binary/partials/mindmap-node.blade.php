@php
    $mapId = 'member-' . $node['id'];
    $hasBranches = isset($node['left_slots']) && ($isRoot || collect(array_merge($node['left_slots'] ?? [], $node['right_slots'] ?? []))->contains(fn ($child) => empty($child['is_vacant'])));
    $details = \Illuminate\Support\Arr::except($node, ['left_slots', 'right_slots']);
@endphp
<div class="mindmap-row" x-data="{ expanded: true }">
    <div class="mindmap-member {{ $isRoot ? 'is-root' : '' }}" data-map-anchor="{{ $mapId }}" data-map-parent="{{ $mapParent }}">
        <button type="button" class="mindmap-name" @click="openDetailsModal({{ json_encode($details) }})">{{ $node['member_name'] }}{{ !empty($node['is_fme']) ? ' (FME)' : '' }}</button>
        @if(!empty($node['member_code']))
        <div class="flex items-center justify-between gap-1 text-[11px] text-slate-500 font-mono">
            <span class="truncate">{{ $node['member_code'] }}</span>
            <button type="button" @click.stop="copyToClipboard('{{ $node['member_code'] }}', 'Member Code')" title="Copy code" class="text-slate-400 hover:text-orange-600 p-0.5 transition-colors cursor-pointer flex-shrink-0">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </button>
        </div>
        @endif
        @if(!empty($node['phone']))
        <div class="flex items-center justify-between gap-1 text-[11px] text-slate-600">
            <span class="truncate">{{ $node['phone'] }}</span>
            <button type="button" @click.stop="copyToClipboard('{{ $node['phone'] }}', 'Phone number')" title="Copy phone" class="text-slate-400 hover:text-orange-600 p-0.5 transition-colors cursor-pointer flex-shrink-0">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </button>
        </div>
        @endif
        @if(!empty($node['email']))
        <div class="flex items-center justify-between gap-1 text-[11px] text-slate-500">
            <span class="truncate">{{ $node['email'] }}</span>
            <button type="button" @click.stop="copyToClipboard('{{ $node['email'] }}', 'Email address')" title="Copy email" class="text-slate-400 hover:text-orange-600 p-0.5 transition-colors cursor-pointer flex-shrink-0">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </button>
        </div>
        @endif
        <div class="text-[11px] text-slate-600">{{ $node['rank_name'] ?? 'Member' }} · {{ $node['point_value'] ?? 0 }} BV</div>
        @if(!empty($node['is_target']))<div class="text-purple-700 font-semibold text-[10px]">🎯 Target member</div>@endif
        @if(!empty($node['notes']))
        <div class="mt-1 px-1.5 py-0.5 rounded bg-amber-50 border border-amber-200/80 text-[10px] text-amber-900 truncate" title="{{ $node['notes'] }}">
            <span class="font-bold">📝</span> {{ Str::limit($node['notes'], 20) }}
        </div>
        @endif
        <div class="mindmap-actions">
            <a href="{{ route('team.show', ['memberId' => $node['id'], 'owner_id' => request('owner_id')]) }}">View team</a>
            @if($hasBranches)<button type="button" @click="expanded = !expanded; $nextTick(() => draw())" :aria-expanded="expanded" x-text="expanded ? 'Collapse' : 'Expand'"></button>@endif
        </div>
    </div>
    @if($hasBranches)
        <div class="mindmap-branches" x-show="expanded">
            @foreach(['RIGHT' => 'Right Side', 'LEFT' => 'Left Side'] as $branch => $label)
                @php $branchId = $mapId . '-' . $branch; $slots = $node[strtolower($branch) . '_slots'] ?? []; @endphp
                <div class="mindmap-branch">
                    <div class="mindmap-branch-label" data-map-anchor="{{ $branchId }}" data-map-parent="{{ $mapId }}">{{ $label }}</div>
                    <div class="mindmap-children">
                        @foreach($slots as $slot => $child)
                            @if(!empty($child['is_vacant']))
                                <button type="button" class="mindmap-vacant" data-map-anchor="{{ $branchId }}-slot-{{ $slot }}" data-map-parent="{{ $branchId }}"
                                    @click="openPlacementModal({{ $node['id'] }}, {{ json_encode($node['member_name']) }}, {{ json_encode($node['member_code'] ?? '') }}, '{{ $branch }}', {{ $slot }})">{{ $slot }} · Add member</button>
                            @else
                                @include('binary.partials.mindmap-node', ['node' => $child, 'mapParent' => $branchId, 'isRoot' => false])
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
