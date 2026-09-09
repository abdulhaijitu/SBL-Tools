<?php

namespace App\Services;

use App\Models\BinaryNode;
use App\Models\Investment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BinaryTreeService
{
    protected RankService $rankService;

    public function __construct(?RankService $rankService = null)
    {
        $this->rankService = $rankService ?: new RankService();
    }

    /**
     * Get the main team root for a specific tree owner or the primary admin root.
     */
    public function getMainTeamRoot(?int $ownerId = null): ?BinaryNode
    {
        $query = BinaryNode::with(['user', 'sponsor', 'parent', 'investments'])
            ->whereNull('parent_id');

        if ($ownerId) {
            $query->where('tree_owner_id', $ownerId);
        }

        // Try to match Abdul Hai first if no specific owner is passed
        if (! $ownerId) {
            $namedRoot = (clone $query)
                ->where(function ($q) {
                    $q->where('member_name', 'like', '%Abdul Hai%')
                        ->orWhere('member_code', 'like', '%abdulhai%');
                })
                ->first();

            if ($namedRoot) {
                return $namedRoot;
            }
        }

        return $query->orderBy('id')->first();
    }

    /**
     * Auto-initialize a personal root node for a user if they don't have one yet.
     */
    public function ensureUserRoot(User $user): BinaryNode
    {
        $existingRoot = BinaryNode::where('tree_owner_id', $user->id)
            ->whereNull('parent_id')
            ->first();

        if ($existingRoot) {
            return $existingRoot;
        }

        $code = 'SBL-' . (1000 + $user->id);
        return BinaryNode::create([
            'tree_owner_id' => $user->id,
            'user_id' => $user->id,
            'member_name' => $user->name,
            'member_code' => $code,
            'phone' => $user->phone,
            'email' => $user->email,
            'password_plain' => null,
            'tpin' => null,
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'contributions' => [
                ['amount' => 100.00, 'date' => now()->toDateString(), 'note' => 'Initial Plan 100 BV']
            ],
            'rank_name' => 'Member',
            'sponsor_name' => null,
            'left_target_count' => 5,
            'right_target_count' => 5,
            'is_active' => true,
            'joined_at' => now(),
        ]);
    }

    /**
     * Get breadcrumb path from main root to currently viewed node.
     */
    public function getBreadcrumbs(BinaryNode $currentNode, ?BinaryNode $mainRoot = null): array
    {
        $trail = [];
        $curr = $currentNode;
        while ($curr) {
            $trail[] = [
                'id' => $curr->id,
                'name' => $curr->member_name,
                'code' => $curr->member_code ?: ('SBL-' . $curr->id),
                'slot_label' => $curr->slot_label,
                'is_current' => $curr->id === $currentNode->id,
            ];

            if ($mainRoot && $curr->id === $mainRoot->id) {
                break;
            }

            if (! $curr->parent_id) {
                break;
            }

            $curr = BinaryNode::find($curr->parent_id);
        }

        return array_reverse($trail);
    }

    /**
     * Get direct children for a node in a specific branch (LEFT or RIGHT), ordered 1 to 5.
     */
    public function getDirectChildrenByBranch(BinaryNode $node, string $branch): array
    {
        $branch = strtoupper($branch);
        $legacyPos = strtolower($branch);

        return BinaryNode::with(['user', 'sponsor', 'parent', 'investments'])
            ->where('parent_id', $node->id)
            ->where(function ($q) use ($branch, $legacyPos) {
                $q->where('branch', $branch)
                    ->orWhere(function ($q2) use ($legacyPos) {
                        $q2->whereNull('branch')->where('position', $legacyPos);
                    });
            })
            ->orderBy('slot_number')
            ->get()
            ->keyBy('slot_number')
            ->all();
    }

    /**
     * Get direct child at a specific slot (e.g. branch 'LEFT', slot_number 3).
     */
    public function getDirectChildAtSlot(BinaryNode $node, string $branch, int $slotNumber): ?BinaryNode
    {
        $branch = strtoupper($branch);
        $legacyPos = strtolower($branch);

        return BinaryNode::with(['user', 'sponsor', 'parent', 'investments'])
            ->where('parent_id', $node->id)
            ->where(function ($q) use ($branch, $legacyPos) {
                $q->where('branch', $branch)
                    ->orWhere(function ($q2) use ($legacyPos) {
                        $q2->whereNull('branch')->where('position', $legacyPos);
                    });
            })
            ->where('slot_number', $slotNumber)
            ->first();
    }

    /**
     * Recursively collect all descendants in the Left Team (all descendants under LEFT-1..LEFT-5).
     */
    public function getLeftTeamDescendants(BinaryNode $node): array
    {
        $directLeft = $this->getDirectChildrenByBranch($node, 'LEFT');
        $descendants = [];

        foreach ($directLeft as $child) {
            $descendants[] = $child;
            $this->collectAllSubtreeDescendants($child, $descendants);
        }

        return $descendants;
    }

    /**
     * Recursively collect all descendants in the Right Team (all descendants under RIGHT-1..RIGHT-5).
     */
    public function getRightTeamDescendants(BinaryNode $node): array
    {
        $directRight = $this->getDirectChildrenByBranch($node, 'RIGHT');
        $descendants = [];

        foreach ($directRight as $child) {
            $descendants[] = $child;
            $this->collectAllSubtreeDescendants($child, $descendants);
        }

        return $descendants;
    }

    /**
     * Helper to recursively collect all descendants under a node.
     */
    protected function collectAllSubtreeDescendants(BinaryNode $parent, array &$list): void
    {
        $children = BinaryNode::with(['user', 'sponsor', 'parent', 'investments'])
            ->where('parent_id', $parent->id)
            ->orderBy('branch')
            ->orderBy('slot_number')
            ->get();

        foreach ($children as $child) {
            $list[] = $child;
            $this->collectAllSubtreeDescendants($child, $list);
        }
    }

    /**
     * Dynamically calculate 5L + 5R statistics for any node from the actual placement tree.
     */
    public function calculateDynamicStats(BinaryNode $node): array
    {
        $directLeftMap = $this->getDirectChildrenByBranch($node, 'LEFT');
        $directRightMap = $this->getDirectChildrenByBranch($node, 'RIGHT');

        $directLeftCount = count($directLeftMap);
        $directRightCount = count($directRightMap);

        $leftDescendants = $this->getLeftTeamDescendants($node);
        $rightDescendants = $this->getRightTeamDescendants($node);

        $totalLeftNetwork = count($leftDescendants);
        $totalRightNetwork = count($rightDescendants);

        $activeLeftCount = 0;
        $targetLeftCount = 0;
        $leftVolume = 0.0;
        $leftBv = 0.0;

        foreach ($leftDescendants as $lNode) {
            $pv = $this->getNodeTotalPv($lNode);
            $amt = $this->getNodeTotalInvestmentAmount($lNode);

            if ($lNode->is_target) {
                $targetLeftCount++;
            } else {
                $activeLeftCount++;
                $leftVolume += $amt;
                $leftBv += $pv;
            }
        }

        $activeRightCount = 0;
        $targetRightCount = 0;
        $rightVolume = 0.0;
        $rightBv = 0.0;

        foreach ($rightDescendants as $rNode) {
            $pv = $this->getNodeTotalPv($rNode);
            $amt = $this->getNodeTotalInvestmentAmount($rNode);

            if ($rNode->is_target) {
                $targetRightCount++;
            } else {
                $activeRightCount++;
                $rightVolume += $amt;
                $rightBv += $pv;
            }
        }

        // Pair matching (100 BV pair unit)
        $pairUnit = 100.00;
        $matchedPairs = (int)floor(min($leftBv, $rightBv) / $pairUnit);
        $carryLeft = max(0.0, $leftBv - ($matchedPairs * $pairUnit));
        $carryRight = max(0.0, $rightBv - ($matchedPairs * $pairUnit));

        // Evaluate rank dynamically using RankService
        $rankInfo = $this->rankService->evaluateRank(
            $directLeftCount,
            $directRightCount,
            $totalLeftNetwork,
            $totalRightNetwork,
            $leftVolume,
            $rightVolume
        );

        $ownInvestment = $this->getNodeTotalInvestmentAmount($node);
        $ownPv = $this->getNodeTotalPv($node);

        return [
            'direct_left_count' => $directLeftCount,
            'direct_right_count' => $directRightCount,
            'direct_left_display' => "{$directLeftCount}/5",
            'direct_right_display' => "{$directRightCount}/5",
            'total_left_network' => $totalLeftNetwork,
            'total_right_network' => $totalRightNetwork,
            'active_left_count' => $activeLeftCount,
            'active_right_count' => $activeRightCount,
            'target_left_count' => $targetLeftCount,
            'target_right_count' => $targetRightCount,
            'left_investment_volume' => $leftVolume,
            'right_investment_volume' => $rightVolume,
            'left_bv' => $leftBv,
            'right_bv' => $rightBv,
            'carry_left' => $carryLeft,
            'carry_right' => $carryRight,
            'matched_pairs' => $matchedPairs,
            'total_team_members' => $totalLeftNetwork + $totalRightNetwork + 1,
            'total_team_volume' => $leftVolume + $rightVolume,
            'own_investment' => $ownInvestment,
            'own_pv' => $ownPv,
            'rank_info' => $rankInfo,
            'is_fme' => $rankInfo['is_fme'],
        ];
    }

    /**
     * Get visual tree data for 10-slot architecture starting from a root node.
     */
    public function getVisualTree(?int $rootId = null, ?int $ownerId = null, int $maxDepth = 2): array
    {
        $mainRoot = $this->getMainTeamRoot($ownerId);

        $root = null;
        if ($rootId) {
            $query = BinaryNode::with(['user', 'sponsor', 'parent', 'investments']);
            if ($ownerId) {
                $query->where('tree_owner_id', $ownerId);
            }
            $root = $query->find($rootId);
        }

        if (! $root) {
            $root = $mainRoot;
        }

        if (! $root) {
            return [
                'root' => null,
                'main_root' => null,
                'breadcrumbs' => [],
                'tree' => null,
                'all_node_ids' => [],
                'stats' => null,
            ];
        }

        $rootStats = $this->calculateDynamicStats($root);
        $breadcrumbs = $this->getBreadcrumbs($root, $mainRoot);

        $allNodeIds = [];
        $hierarchyTree = $this->buildTenSlotHierarchyTree($root, $allNodeIds, 1, $maxDepth);
        // Build direct 5 LEFT slots (strictly non-recursive for Team Explorer)
        $directLeftChildren = $this->getDirectChildrenByBranch($root, 'LEFT');
        $leftSlots = [];
        for ($slot = 1; $slot <= 5; $slot++) {
            if (isset($directLeftChildren[$slot])) {
                $childNode = $directLeftChildren[$slot];
                $childStats = $this->calculateDynamicStats($childNode);
                $leftSlots[$slot] = $this->formatNodeForView($childNode, $childStats);
                $leftSlots[$slot]['depth'] = 2;
                $leftSlots[$slot]['generation'] = 1;
                $leftSlots[$slot]['generation_label'] = 'GEN 1';
            } else {
                $leftSlots[$slot] = $this->formatVacantSlot($root->id, 'LEFT', $slot, 2);
            }
        }

        // Build direct 5 RIGHT slots (strictly non-recursive for Team Explorer)
        $directRightChildren = $this->getDirectChildrenByBranch($root, 'RIGHT');
        $rightSlots = [];
        for ($slot = 1; $slot <= 5; $slot++) {
            if (isset($directRightChildren[$slot])) {
                $childNode = $directRightChildren[$slot];
                $childStats = $this->calculateDynamicStats($childNode);
                $rightSlots[$slot] = $this->formatNodeForView($childNode, $childStats);
                $rightSlots[$slot]['depth'] = 2;
                $rightSlots[$slot]['generation'] = 1;
                $rightSlots[$slot]['generation_label'] = 'GEN 1';
            } else {
                $rightSlots[$slot] = $this->formatVacantSlot($root->id, 'RIGHT', $slot, 2);
            }
        }

        $formattedRoot = $this->formatNodeForView($root, $rootStats);
        $formattedRoot['left_slots'] = $leftSlots;
        $formattedRoot['right_slots'] = $rightSlots;

        $parentNode = $root->parent_id ? BinaryNode::find($root->parent_id) : null;

        return [
            'root' => $root,
            'current_member' => $formattedRoot,
            'main_root' => $mainRoot,
            'parent_node' => $parentNode,
            'is_main_root' => ($mainRoot && (int)$root->id === (int)$mainRoot->id),
            'breadcrumbs' => $breadcrumbs,
            'tree' => $hierarchyTree,
            'all_node_ids' => $allNodeIds,
            'left_slots' => $leftSlots,
            'right_slots' => $rightSlots,
            'stats' => [
                'root_name' => $root->member_name,
                'root_code' => $root->member_code ?: ('SBL-' . $root->id),
                'sponsor_name' => $root->sponsor_name ?: ($root->sponsor?->member_name ?? 'Not assigned'),
                'direct_left_count' => $rootStats['direct_left_count'],
                'direct_right_count' => $rootStats['direct_right_count'],
                'direct_total_count' => $rootStats['direct_left_count'] + $rootStats['direct_right_count'],
                'direct_left_display' => $rootStats['direct_left_display'],
                'direct_right_display' => $rootStats['direct_right_display'],
                'total_members' => $rootStats['total_team_members'],
                'total_left_network' => $rootStats['total_left_network'],
                'total_right_network' => $rootStats['total_right_network'],
                'target_left_count' => $rootStats['target_left_count'],
                'target_right_count' => $rootStats['target_right_count'],
                'left_bv' => $rootStats['left_bv'],
                'right_bv' => $rootStats['right_bv'],
                'left_investment_volume' => $rootStats['left_investment_volume'],
                'right_investment_volume' => $rootStats['right_investment_volume'],
                'own_investment' => $rootStats['own_investment'],
                'carry_left' => $rootStats['carry_left'],
                'carry_right' => $rootStats['carry_right'],
                'matched_pairs' => $rootStats['matched_pairs'],
                'weaker_leg' => $rootStats['carry_left'] <= $rootStats['carry_right'] ? 'LEFT' : 'RIGHT',
                'rank_name' => $rootStats['rank_info']['rank_name'],
                'is_fme' => $rootStats['rank_info']['is_fme'],
            ],
        ];
    }

    /**
     * Recursively build 10-slot hierarchy tree (5 Left + 5 Right per member).
     */
    public function buildTenSlotHierarchyTree(BinaryNode $node, array &$allNodeIds = [], int $depth = 1, int $maxDepth = 2): array
    {
        $stats = $this->calculateDynamicStats($node);
        $formatted = $this->formatNodeForView($node, $stats);
        $allNodeIds[] = $node->id;

        $generation = $depth - 1;
        $generationLabel = $generation === 0 ? 'ROOT' : 'GEN ' . $generation;

        $formatted['depth'] = $depth;
        $formatted['generation'] = $generation;
        $formatted['generation_label'] = $generationLabel;

        // Build 5 LEFT slots
        $directLeftChildren = $this->getDirectChildrenByBranch($node, 'LEFT');
        $leftSlots = [];
        for ($slot = 1; $slot <= 5; $slot++) {
            if (isset($directLeftChildren[$slot])) {
                $childNode = $directLeftChildren[$slot];
                if ($depth < $maxDepth) {
                    $leftSlots[$slot] = $this->buildTenSlotHierarchyTree($childNode, $allNodeIds, $depth + 1, $maxDepth);
                } else {
                    $childStats = $this->calculateDynamicStats($childNode);
                    $leftSlots[$slot] = $this->formatNodeForView($childNode, $childStats);
                    $leftSlots[$slot]['depth'] = $depth + 1;
                    $leftSlots[$slot]['generation'] = $depth;
                    $leftSlots[$slot]['generation_label'] = 'GEN ' . $depth;
                    $allNodeIds[] = $childNode->id;
                }
            } else {
                $leftSlots[$slot] = $this->formatVacantSlot($node->id, 'LEFT', $slot, $depth + 1);
            }
        }
        $formatted['left_slots'] = $leftSlots;

        // Build 5 RIGHT slots
        $directRightChildren = $this->getDirectChildrenByBranch($node, 'RIGHT');
        $rightSlots = [];
        for ($slot = 1; $slot <= 5; $slot++) {
            if (isset($directRightChildren[$slot])) {
                $childNode = $directRightChildren[$slot];
                if ($depth < $maxDepth) {
                    $rightSlots[$slot] = $this->buildTenSlotHierarchyTree($childNode, $allNodeIds, $depth + 1, $maxDepth);
                } else {
                    $childStats = $this->calculateDynamicStats($childNode);
                    $rightSlots[$slot] = $this->formatNodeForView($childNode, $childStats);
                    $rightSlots[$slot]['depth'] = $depth + 1;
                    $rightSlots[$slot]['generation'] = $depth;
                    $rightSlots[$slot]['generation_label'] = 'GEN ' . $depth;
                    $allNodeIds[] = $childNode->id;
                }
            } else {
                $rightSlots[$slot] = $this->formatVacantSlot($node->id, 'RIGHT', $slot, $depth + 1);
            }
        }
        $formatted['right_slots'] = $rightSlots;

        return $formatted;
    }

    /**
     * Place a member into a specific slot (1 to 5) under parent's LEFT or RIGHT branch.
     */
    public function placeMember(array $data): BinaryNode
    {
        return DB::transaction(function () use ($data) {
            $parentId = $data['parent_id'] ?? null;
            $branch = isset($data['branch']) ? strtoupper($data['branch']) : (isset($data['position']) ? strtoupper($data['position']) : null);
            $slotNumber = (int)($data['slot_number'] ?? ($data['position_slot'] ?? 1));

            if ($parentId) {
                $parent = BinaryNode::findOrFail($parentId);

                if (! in_array($branch, ['LEFT', 'RIGHT'])) {
                    throw new InvalidArgumentException("Branch must be either 'LEFT' or 'RIGHT'.");
                }

                if ($slotNumber < 1 || $slotNumber > 5) {
                    throw new InvalidArgumentException("Slot position must be between 1 and 5.");
                }

                // Check if exact slot (parent_id, branch, slot_number) is occupied
                $existing = BinaryNode::where('parent_id', $parentId)
                    ->where(function ($q) use ($branch) {
                        $q->where('branch', $branch)
                            ->orWhere('position', strtolower($branch));
                    })
                    ->where('slot_number', $slotNumber)
                    ->exists();

                if ($existing) {
                    throw new InvalidArgumentException("Slot {$branch}-{$slotNumber} under {$parent->member_name} is already occupied.");
                }

                // Check that parent doesn't exceed 5 direct members on this branch
                $directCount = BinaryNode::where('parent_id', $parentId)
                    ->where(function ($q) use ($branch) {
                        $q->where('branch', $branch)
                            ->orWhere('position', strtolower($branch));
                    })
                    ->count();

                if ($directCount >= 5) {
                    throw new InvalidArgumentException("Parent {$parent->member_name} already has maximum 5 direct {$branch} members.");
                }

                // Prevent placing a member under themselves or in their own descendant tree
                if (isset($data['id']) && $data['id']) {
                    if ((int)$data['id'] === (int)$parentId) {
                        throw new InvalidArgumentException("A member cannot be placed under themselves.");
                    }
                    $memberNode = BinaryNode::find($data['id']);
                    if ($memberNode && $this->isDescendantOf($memberNode, $parent)) {
                        throw new InvalidArgumentException("Circular placement detected: parent is already a descendant of this member.");
                    }
                }
            }

            // Tree owner scoping
            $treeOwnerId = $data['tree_owner_id'] ?? ($parentId ? BinaryNode::where('id', $parentId)->value('tree_owner_id') : ($data['user_id'] ?? null));

            // Generate unique member code if not provided
            $memberCode = $data['member_code'] ?? null;
            if (! $memberCode) {
                $nextId = (BinaryNode::max('id') ?? 0) + 1001;
                $memberCode = 'SBL-' . $nextId;
            }

            $pointValue = isset($data['point_value']) ? (float)$data['point_value'] : 100.00;
            $packageName = $data['package_name'] ?? 'National 120k';
            if (isset($data['package_name'])) {
                if (str_contains(strtolower($packageName), '550')) {
                    $pointValue = 500.00;
                } elseif (str_contains(strtolower($packageName), '120')) {
                    $pointValue = 100.00;
                } elseif (str_contains(strtolower($packageName), '25')) {
                    $pointValue = 25.00;
                }
            }

            $isTarget = !empty($data['is_target']);

            $node = BinaryNode::create([
                'tree_owner_id' => $treeOwnerId,
                'user_id' => $data['user_id'] ?? null,
                'member_name' => $data['member_name'],
                'member_code' => $memberCode,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'password_plain' => $data['password_plain'] ?? null,
                'tpin' => $data['tpin'] ?? null,
                'parent_id' => $parentId,
                'sponsor_id' => $data['sponsor_id'] ?? $parentId,
                'sponsor_name' => $data['sponsor_name'] ?? null,
                'branch' => $branch,
                'slot_number' => $slotNumber,
                'position' => $branch ? strtolower($branch) : null,
                'package_name' => $packageName,
                'point_value' => $pointValue,
                'contributions' => $data['contributions'] ?? [],
                'left_target_count' => 5,
                'right_target_count' => 5,
                'rank_name' => $data['rank_name'] ?? 'Member',
                'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
                'is_target' => $isTarget,
                'target_date' => $data['target_date'] ?? null,
                'target_notes' => $data['target_notes'] ?? null,
                'notes' => $data['notes'] ?? ($data['target_notes'] ?? null),
                'joined_at' => now(),
            ]);

            // Create initial Investment record if not target
            if (! $isTarget && ($pointValue > 0 || !empty($data['contributions']))) {
                $contribs = $data['contributions'] ?? [];
                if (!empty($contribs) && is_array($contribs)) {
                    foreach ($contribs as $c) {
                        Investment::create([
                            'binary_node_id' => $node->id,
                            'plan_name' => $c['note'] ?? $packageName,
                            'amount' => (float)($c['amount'] ?? $pointValue),
                            'point_value' => (float)($c['amount'] ?? $pointValue),
                            'status' => 'active',
                            'investment_date' => $c['date'] ?? now()->toDateString(),
                            'note' => $c['note'] ?? 'Initial Package Investment',
                        ]);
                    }
                } else {
                    Investment::create([
                        'binary_node_id' => $node->id,
                        'plan_name' => $packageName,
                        'amount' => $pointValue,
                        'point_value' => $pointValue,
                        'status' => 'active',
                        'investment_date' => now()->toDateString(),
                        'note' => 'Initial Package Investment',
                    ]);
                }
            }

            $this->syncUplineCounts($node);

            return $node;
        });
    }

    /**
     * Convert a Target/Planned member into an Active/Confirmed member.
     */
    public function convertToActive(BinaryNode $node): BinaryNode
    {
        return DB::transaction(function () use ($node) {
            $node->update([
                'is_target' => false,
                'is_active' => true,
                'joined_at' => now(),
            ]);

            if ($node->investments()->count() === 0) {
                $pv = (float)($node->point_value ?: 100.00);
                Investment::create([
                    'binary_node_id' => $node->id,
                    'plan_name' => $node->package_name ?: 'National 120k',
                    'amount' => $pv,
                    'point_value' => $pv,
                    'status' => 'active',
                    'investment_date' => now()->toDateString(),
                    'note' => 'Target to Active Conversion',
                ]);
            }

            $this->syncUplineCounts($node);
            return $node;
        });
    }

    /**
     * Sync derived dynamic counts to ancestor database columns.
     */
    public function syncUplineCounts(BinaryNode $node): void
    {
        $current = $node;
        while ($current) {
            $stats = $this->calculateDynamicStats($current);
            $dynamicRank = $stats['rank_info']['rank_code'] !== 'Member' ? $stats['rank_info']['rank_name'] : ($current->rank_name ?: 'Member');

            $current->update([
                'left_count' => $stats['total_left_network'],
                'right_count' => $stats['total_right_network'],
                'left_bv' => $stats['left_bv'],
                'right_bv' => $stats['right_bv'],
                'carry_left' => $stats['carry_left'],
                'carry_right' => $stats['carry_right'],
                'matched_pairs' => $stats['matched_pairs'],
                'rank_name' => $dynamicRank,
            ]);

            if (! $current->parent_id) {
                break;
            }
            $current = BinaryNode::find($current->parent_id);
        }
    }

    /**
     * Update an existing member.
     */
    public function updateMember(BinaryNode $node, array $data): BinaryNode
    {
        return DB::transaction(function () use ($node, $data) {
            $cleanCode = $node->member_code;
            if (!empty($data['member_code'])) {
                $cleanCode = trim($data['member_code']);
            }

            $updateData = [
                'member_name' => $data['member_name'] ?? $node->member_name,
                'member_code' => $cleanCode,
                'phone' => $data['phone'] ?? $node->phone,
                'email' => $data['email'] ?? $node->email,
                'password_plain' => $data['password_plain'] ?? $node->password_plain,
                'tpin' => $data['tpin'] ?? $node->tpin,
                'package_name' => $data['package_name'] ?? $node->package_name,
                'rank_name' => $data['rank_name'] ?? $node->rank_name,
                'sponsor_id' => array_key_exists('sponsor_id', $data) ? ($data['sponsor_id'] ? (int)$data['sponsor_id'] : null) : $node->sponsor_id,
                'sponsor_name' => array_key_exists('sponsor_name', $data) ? $data['sponsor_name'] : $node->sponsor_name,
                'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : $node->is_active,
                'is_target' => isset($data['is_target']) ? (bool)$data['is_target'] : $node->is_target,
                'target_date' => $data['target_date'] ?? $node->target_date,
                'target_notes' => $data['target_notes'] ?? $node->target_notes,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $node->notes,
                'user_id' => array_key_exists('user_id', $data) ? ($data['user_id'] ? (int)$data['user_id'] : null) : $node->user_id,
            ];

            // Handle contribution updates
            if (isset($data['contributions'])) {
                $contribs = is_string($data['contributions']) ? json_decode($data['contributions'], true) : $data['contributions'];
                if (is_array($contribs)) {
                    $updateData['contributions'] = $contribs;
                    $node->investments()->delete();
                    foreach ($contribs as $c) {
                        Investment::create([
                            'binary_node_id' => $node->id,
                            'plan_name' => $c['note'] ?? $node->package_name ?? 'Contribution',
                            'amount' => (float)($c['amount'] ?? 0),
                            'point_value' => (float)($c['amount'] ?? 0),
                            'status' => 'active',
                            'investment_date' => $c['date'] ?? now()->toDateString(),
                            'note' => $c['note'] ?? 'Contribution Record',
                        ]);
                    }
                    $updateData['point_value'] = (float)collect($contribs)->sum('amount');
                }
            } elseif (isset($data['point_value']) && $data['point_value'] !== '') {
                $updateData['point_value'] = (float)$data['point_value'];
            }

            $node->update($updateData);
            $this->syncUplineCounts($node);

            return $node;
        });
    }

    /**
     * Delete a leaf node or cascade delete a subtree.
     */
    public function deleteNode(BinaryNode $node, bool $cascade = false): void
    {
        DB::transaction(function () use ($node, $cascade) {
            $hasChildren = $node->children()->exists();
            if ($hasChildren && !$cascade) {
                throw new InvalidArgumentException("This member has active downline team members. To preserve tree integrity, nodes with downlines cannot be deleted directly. Use the cascade delete option to remove the entire branch.");
            }

            $parent = $node->parent_id ? BinaryNode::find($node->parent_id) : null;

            if ($hasChildren && $cascade) {
                $descendants = [];
                $this->collectAllSubtreeDescendants($node, $descendants);

                foreach (array_reverse($descendants) as $descendant) {
                    $descendant->investments()->delete();
                    $descendant->delete();
                }
            }

            $node->investments()->delete();
            $node->delete();

            if ($parent) {
                $this->syncUplineCounts($parent);
            }
        });
    }

    /**
     * Check if candidate is a descendant of ancestor node.
     */
    public function isDescendantOf(BinaryNode $ancestor, BinaryNode $candidate): bool
    {
        $current = $candidate;
        while ($current->parent_id) {
            if ((int)$current->parent_id === (int)$ancestor->id) {
                return true;
            }
            $current = BinaryNode::find($current->parent_id);
            if (! $current) {
                break;
            }
        }
        return false;
    }

    /**
     * Get total point value (BV) for a node.
     */
    public function getNodeTotalPv(BinaryNode $node): float
    {
        $invSum = (float)$node->investments()->where('status', 'active')->sum('point_value');
        if ($invSum > 0) {
            return $invSum;
        }
        return (float)($node->point_value ?? 0.0);
    }

    /**
     * Get total investment amount for a node.
     */
    public function getNodeTotalInvestmentAmount(BinaryNode $node): float
    {
        $invSum = (float)$node->investments()->where('status', 'active')->sum('amount');
        if ($invSum > 0) {
            return $invSum;
        }
        return (float)($node->point_value ?? 0.0);
    }

    /**
     * Format a node for UI presentation.
     */
    public function formatNodeForView(BinaryNode $node, ?array $stats = null): array
    {
        $stats = $stats ?: $this->calculateDynamicStats($node);
        $node->loadMissing(['sponsor', 'parent', 'investments']);

        $sponsorName = $node->sponsor_name ?: ($node->sponsor?->member_name ?? ($node->parent?->member_name ?? 'Not assigned'));
        $code = $node->member_code ?: ('SBL-' . $node->id);
        $username = str_starts_with($code, '@') ? $code : ('@' . strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $code)));

        $investmentsList = $node->investments()->where('status', 'active')->get()->map(function ($inv) {
            return [
                'amount' => (float)$inv->amount,
                'date' => $inv->investment_date ? $inv->investment_date->toDateString() : now()->toDateString(),
                'note' => $inv->plan_name ?: $inv->note ?: 'Investment',
            ];
        })->toArray();

        if (empty($investmentsList)) {
            $investmentsList = [
                ['amount' => $stats['own_investment'], 'date' => $node->created_at ? $node->created_at->toDateString() : now()->toDateString(), 'note' => $node->package_name ?: 'Initial']
            ];
        }

        $branch = strtoupper($node->branch ?: ($node->position === 'left' ? 'LEFT' : ($node->position === 'right' ? 'RIGHT' : '')));
        $slotNumber = $node->slot_number ?: 1;

        return [
            'is_vacant' => false,
            'id' => $node->id,
            'tree_owner_id' => $node->tree_owner_id,
            'member_name' => $node->member_name,
            'member_code' => $node->member_code,
            'username' => $username,
            'phone' => $node->phone,
            'email' => $node->email,
            'sponsor_id' => $node->sponsor_id,
            'sponsor_name' => $sponsorName,
            'user_id' => $node->user_id,
            'is_active' => (bool)$node->is_active,
            'is_target' => (bool)$node->is_target,
            'target_date' => $node->target_date ? $node->target_date->toDateString() : null,
            'target_notes' => $node->target_notes,
            'notes' => $node->notes ?: $node->target_notes,
            'package_name' => $node->package_name,
            'point_value' => $stats['own_pv'],
            'own_investment' => $stats['own_investment'],
            'total_investment' => $stats['own_investment'],
            'contributions' => $investmentsList,
            'rank_name' => $stats['rank_info']['rank_name'],
            'rank_code' => $stats['rank_info']['rank_code'],
            'is_fme' => $stats['rank_info']['is_fme'],
            'branch' => $branch,
            'slot_number' => $slotNumber,
            'slot_label' => $node->slot_label,
            'position' => $branch ? strtolower($branch) : null,
            'direct_left_count' => $stats['direct_left_count'],
            'direct_right_count' => $stats['direct_right_count'],
            'direct_left_display' => $stats['direct_left_display'],
            'direct_right_display' => $stats['direct_right_display'],
            'total_left_network' => $stats['total_left_network'],
            'total_right_network' => $stats['total_right_network'],
            'active_left_count' => $stats['active_left_count'],
            'active_right_count' => $stats['active_right_count'],
            'target_left_count' => $stats['target_left_count'],
            'target_right_count' => $stats['target_right_count'],
            'left_bv' => $stats['left_bv'],
            'right_bv' => $stats['right_bv'],
            'left_investment_volume' => $stats['left_investment_volume'],
            'right_investment_volume' => $stats['right_investment_volume'],
            'carry_left' => $stats['carry_left'],
            'carry_right' => $stats['carry_right'],
            'matched_pairs' => $stats['matched_pairs'],
            'parent_id' => $node->parent_id,
            'has_children' => $node->children()->exists(),
        ];
    }

    /**
     * Format a vacant slot for UI.
     */
    public function formatVacantSlot(int $parentId, string $branch, int $slotNumber, int $depth = 2): array
    {
        $parent = BinaryNode::find($parentId);
        $generation = $depth - 1;
        $branch = strtoupper($branch);

        return [
            'is_vacant' => true,
            'parent_id' => $parentId,
            'parent_name' => $parent ? $parent->member_name : 'Upline',
            'parent_code' => $parent ? $parent->member_code : '',
            'branch' => $branch,
            'slot_number' => $slotNumber,
            'slot_label' => "{$branch}-{$slotNumber}",
            'position' => strtolower($branch),
            'depth' => $depth,
            'generation' => $generation,
            'generation_label' => 'GEN ' . $generation,
        ];
    }
}
