@php
    $mapId = 'member-' . $node['id'];
    $hasBranches = isset($node['left_slots']) && ($isRoot || collect(array_merge($node['left_slots'] ?? [], $node['right_slots'] ?? []))->contains(fn ($child) => empty($child['is_vacant'])));
    $details = \Illuminate\Support\Arr::except($node, ['left_slots', 'right_slots']);
@endphp
<div class="mindmap-row" x-data="{ expanded: true }">
    <div class="mindmap-member {{ $isRoot ? 'is-root' : '' }}" data-map-anchor="{{ $mapId }}" data-map-parent="{{ $mapParent }}">
        <button type="button" class="mindmap-name" @click="openDetailsModal({{ json_encode($details) }})">{{ $node['member_name'] }}{{ !empty($node['is_fme']) ? ' (FME)' : '' }}</button>
        <div>{{ $node['member_code'] ?? '' }}</div>
        @if(!empty($node['phone']))<div>Mobile: {{ $node['phone'] }}</div>@endif
        @if(!empty($node['email']))<div>{{ $node['email'] }}</div>@endif
        <div>{{ $node['rank_name'] ?? 'Member' }} · {{ $node['point_value'] ?? 0 }} BV</div>
        @if(!empty($node['is_target']))<div>Target member</div>@endif
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
