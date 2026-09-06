<?php

namespace App\Services;

use App\Models\BinaryNode;
use App\Models\Investment;
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
     * Get visual tree data starting from a root node.
     */
    public function getVisualTree(?int $rootId = null, int $maxLevels = 3): array
    {
        $root = null;
        if ($rootId) {
            $root = BinaryNode::with(['user', 'sponsor', 'parent', 'investments'])->find($rootId);
        }

        if (! $root) {
            $root = BinaryNode::with(['user', 'sponsor', 'parent', 'investments'])->whereNull('parent_id')->orderBy('id')->first();
        }

        if (! $root) {
            return [
                'root' => null,
                'tree' => null,
                'all_node_ids' => [],
                'levels' => [],
                'stats' => null,
            ];
        }

        // Calculate dynamic stats from actual tree descendants
        $rootStats = $this->calculateDynamicStats($root);

        // Build Level data for 3-level crop view
        $levels = [];
        $levels[1] = [$this->formatNodeForView($root, $rootStats)];

        $currentLevelNodes = [$root];

        for ($level = 2; $level <= $maxLevels; $level++) {
            $nextLevelNodes = [];
            $levelData = [];

            foreach ($currentLevelNodes as $parent) {
                if ($parent && ! ($parent->is_vacant ?? false)) {
                    // Left Child
                    $left = $this->getDirectLeftChild($parent);
                    if ($left) {
                        $leftStats = $this->calculateDynamicStats($left);
                        $levelData[] = $this->formatNodeForView($left, $leftStats);
                        $nextLevelNodes[] = $left;
                    } else {
                        $levelData[] = $this->formatVacantSlot($parent->id, 'left');
                        $nextLevelNodes[] = null;
                    }

                    // Right Child
                    $right = $this->getDirectRightChild($parent);
                    if ($right) {
                        $rightStats = $this->calculateDynamicStats($right);
                        $levelData[] = $this->formatNodeForView($right, $rightStats);
                        $nextLevelNodes[] = $right;
                    } else {
                        $levelData[] = $this->formatVacantSlot($parent->id, 'right');
                        $nextLevelNodes[] = null;
                    }
                } else {
                    $levelData[] = null;
                    $levelData[] = null;
                    $nextLevelNodes[] = null;
                    $nextLevelNodes[] = null;
                }
            }

            $levels[$level] = $levelData;
            $currentLevelNodes = $nextLevelNodes;
        }

        // Build full multi-depth hierarchy tree for FigJam canvas
        $allNodeIds = [];
        $hierarchyTree = $this->buildHierarchyTree($root, $allNodeIds, 1);

        return [
            'root' => $root,
            'tree' => $hierarchyTree,
            'all_node_ids' => $allNodeIds,
            'levels' => $levels,
            'stats' => [
                'total_members' => $rootStats['total_team_members'],
                'left_count' => $rootStats['total_left_members'],
                'right_count' => $rootStats['total_right_members'],
                'left_bv' => $rootStats['left_bv'],
                'right_bv' => $rootStats['right_bv'],
                'carry_left' => $rootStats['carry_left'],
                'carry_right' => $rootStats['carry_right'],
                'matched_pairs' => $rootStats['matched_pairs'],
                'weaker_leg' => $rootStats['carry_left'] <= $rootStats['carry_right'] ? 'left' : 'right',
                'rank_name' => $rootStats['rank_info']['rank_name'],
                'is_fme' => $rootStats['rank_info']['is_fme'],
            ],
        ];
    }

    /**
     * Get direct left child of a node.
     */
    public function getDirectLeftChild(BinaryNode $node): ?BinaryNode
    {
        return BinaryNode::with(['user', 'sponsor', 'parent', 'investments'])
            ->where('parent_id', $node->id)
            ->where('position', 'left')
            ->first();
    }

    /**
     * Get direct right child of a node.
     */
    public function getDirectRightChild(BinaryNode $node): ?BinaryNode
    {
        return BinaryNode::with(['user', 'sponsor', 'parent', 'investments'])
            ->where('parent_id', $node->id)
            ->where('position', 'right')
            ->first();
    }

    /**
     * Recursively get all descendants in the Left Team of a member.
     * Starts with direct LEFT child, then all descendants beneath that child.
     */
    public function getLeftTeamDescendants(BinaryNode $node): array
    {
        $leftChild = $this->getDirectLeftChild($node);
        if (! $leftChild) {
            return [];
        }

        $descendants = [$leftChild];
        $this->collectAllSubtreeDescendants($leftChild, $descendants);
        return $descendants;
    }

    /**
     * Recursively get all descendants in the Right Team of a member.
     * Starts with direct RIGHT child, then all descendants beneath that child.
     */
    public function getRightTeamDescendants(BinaryNode $node): array
    {
        $rightChild = $this->getDirectRightChild($node);
        if (! $rightChild) {
            return [];
        }

        $descendants = [$rightChild];
        $this->collectAllSubtreeDescendants($rightChild, $descendants);
        return $descendants;
    }

    /**
     * Helper to recursively collect all descendants under a node.
     */
    protected function collectAllSubtreeDescendants(BinaryNode $parent, array &$list): void
    {
        $children = BinaryNode::with(['user', 'sponsor', 'parent', 'investments'])
            ->where('parent_id', $parent->id)
            ->get();

        foreach ($children as $child) {
            $list[] = $child;
            $this->collectAllSubtreeDescendants($child, $list);
        }
    }

    /**
     * Dynamically calculate binary tree statistics for any node from the actual placement tree.
     */
    public function calculateDynamicStats(BinaryNode $node): array
    {
        $leftNodes = $this->getLeftTeamDescendants($node);
        $rightNodes = $this->getRightTeamDescendants($node);

        $totalLeft = count($leftNodes);
        $totalRight = count($rightNodes);

        $qualifiedLeft = 0;
        $leftVolume = 0.0;
        $leftBv = 0.0;

        foreach ($leftNodes as $lNode) {
            $pv = $this->getNodeTotalPv($lNode);
            $amt = $this->getNodeTotalInvestmentAmount($lNode);

            if ($lNode->is_active && ($pv > 0 || $amt > 0)) {
                $qualifiedLeft++;
            }
            $leftVolume += $amt;
            $leftBv += $pv;
        }

        $qualifiedRight = 0;
        $rightVolume = 0.0;
        $rightBv = 0.0;

        foreach ($rightNodes as $rNode) {
            $pv = $this->getNodeTotalPv($rNode);
            $amt = $this->getNodeTotalInvestmentAmount($rNode);

            if ($rNode->is_active && ($pv > 0 || $amt > 0)) {
                $qualifiedRight++;
            }
            $rightVolume += $amt;
            $rightBv += $pv;
        }

        // Pair matching (100 BV pair unit)
        $pairUnit = 100.00;
        $matchedPairs = (int)floor(min($leftBv, $rightBv) / $pairUnit);
        $carryLeft = max(0.0, $leftBv - ($matchedPairs * $pairUnit));
        $carryRight = max(0.0, $rightBv - ($matchedPairs * $pairUnit));

        // Evaluate rank dynamically using RankService
        $rankInfo = $this->rankService->evaluateRank($totalLeft, $totalRight, $leftVolume, $rightVolume);

        $ownInvestment = $this->getNodeTotalInvestmentAmount($node);
        $ownPv = $this->getNodeTotalPv($node);

        return [
            'total_left_members' => $totalLeft,
            'total_right_members' => $totalRight,
            'qualified_left_members' => $qualifiedLeft,
            'qualified_right_members' => $qualifiedRight,
            'left_investment_volume' => $leftVolume,
            'right_investment_volume' => $rightVolume,
            'left_bv' => $leftBv,
            'right_bv' => $rightBv,
            'carry_left' => $carryLeft,
            'carry_right' => $carryRight,
            'matched_pairs' => $matchedPairs,
            'total_team_members' => $totalLeft + $totalRight + 1,
            'total_team_volume' => $leftVolume + $rightVolume,
            'own_investment' => $ownInvestment,
            'own_pv' => $ownPv,
            'rank_info' => $rankInfo,
        ];
    }

    /**
     * Get total point value (BV) for a node from active investments or fallback point_value.
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
     * Get total investment amount for a node from active investments or fallback point_value.
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
     * Place a new member in the binary tree under parent and position.
     * Core Binary Rule: Maximum 1 direct LEFT child and 1 direct RIGHT child.
     */
    public function placeMember(array $data): BinaryNode
    {
        return DB::transaction(function () use ($data) {
            $parentId = $data['parent_id'] ?? null;
            $position = $data['position'] ?? null;

            if ($parentId) {
                $parent = BinaryNode::findOrFail($parentId);

                if (! in_array($position, ['left', 'right'])) {
                    throw new InvalidArgumentException("Position must be either 'left' or 'right'.");
                }

                // Verify slot is vacant (strictly 1 direct LEFT, 1 direct RIGHT)
                $existing = BinaryNode::where('parent_id', $parentId)
                    ->where('position', $position)
                    ->exists();

                if ($existing) {
                    throw new InvalidArgumentException("Slot '{$position}' under {$parent->member_name} is already occupied.");
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

            // Generate unique member code if not provided
            $memberCode = $data['member_code'] ?? null;
            if (! $memberCode) {
                $nextId = (BinaryNode::max('id') ?? 0) + 1001;
                $memberCode = 'SBL-' . $nextId;
            }

            // Package & Point Value
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

            $node = BinaryNode::create([
                'user_id' => $data['user_id'] ?? null,
                'member_name' => $data['member_name'],
                'member_code' => $memberCode,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'password_plain' => $data['password_plain'] ?? 'sbl123456',
                'tpin' => $data['tpin'] ?? '1234',
                'parent_id' => $parentId,
                'sponsor_id' => $data['sponsor_id'] ?? $parentId,
                'sponsor_name' => $data['sponsor_name'] ?? null,
                'position' => $position,
                'package_name' => $packageName,
                'point_value' => $pointValue,
                'contributions' => $data['contributions'] ?? [],
                'left_target_count' => (int)($data['left_target_count'] ?? 5),
                'right_target_count' => (int)($data['right_target_count'] ?? 5),
                'rank_name' => $data['rank_name'] ?? 'Member',
                'joined_at' => now(),
            ]);

            // Create initial Investment record
            if ($pointValue > 0 || !empty($data['contributions'])) {
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

            // Sync upline counts & BV
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
        while ($current->parent_id) {
            $parent = BinaryNode::find($current->parent_id);
            if (! $parent) {
                break;
            }
            $stats = $this->calculateDynamicStats($parent);
            $parent->update([
                'left_count' => $stats['total_left_members'],
                'right_count' => $stats['total_right_members'],
                'left_bv' => $stats['left_bv'],
                'right_bv' => $stats['right_bv'],
                'carry_left' => $stats['carry_left'],
                'carry_right' => $stats['carry_right'],
                'matched_pairs' => $stats['matched_pairs'],
                'rank_name' => $stats['rank_info']['rank_name'],
            ]);
            $current = $parent;
        }
    }

    /**
     * Add multiple investment records to an existing member without creating another tree node.
     */
    public function addInvestment(BinaryNode $node, array $data): Investment
    {
        return DB::transaction(function () use ($node, $data) {
            $amount = (float)($data['amount'] ?? 0.0);
            $pv = isset($data['point_value']) ? (float)$data['point_value'] : $amount;

            $investment = Investment::create([
                'binary_node_id' => $node->id,
                'investment_plan_id' => $data['investment_plan_id'] ?? null,
                'plan_name' => $data['plan_name'] ?? 'Package Top-up',
                'amount' => $amount,
                'point_value' => $pv,
                'status' => $data['status'] ?? 'active',
                'investment_date' => $data['investment_date'] ?? now()->toDateString(),
                'note' => $data['note'] ?? 'Additional Investment',
            ]);

            // Update node point_value sum
            $totalPv = $this->getNodeTotalPv($node);
            $node->update(['point_value' => $totalPv]);

            $this->syncUplineCounts($node);

            return $investment;
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
                'user_id' => array_key_exists('user_id', $data) ? ($data['user_id'] ? (int)$data['user_id'] : null) : $node->user_id,
            ];

            // Handle contribution/investment updates
            if (isset($data['contributions'])) {
                $contribs = is_string($data['contributions']) ? json_decode($data['contributions'], true) : $data['contributions'];
                if (is_array($contribs)) {
                    $updateData['contributions'] = $contribs;
                    // Sync with investments table
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
     * Delete a leaf node from the binary tree.
     */
    public function deleteNode(BinaryNode $node): void
    {
        DB::transaction(function () use ($node) {
            if ($node->children()->exists()) {
                throw new InvalidArgumentException("এই মেম্বারের ডাউনলাইনে সক্রিয় টিম মেম্বার রয়েছে। ট্রি স্ট্রাকচার অক্ষুণ্ণ রাখতে ডাউনলাইন মেম্বারসহ নোড সরাসরি মুছে ফেলা যাবে না।");
            }

            $parent = $node->parent_id ? BinaryNode::find($node->parent_id) : null;

            $node->investments()->delete();
            $node->delete();

            if ($parent) {
                $stats = $this->calculateDynamicStats($parent);
                $parent->update([
                    'left_count' => $stats['total_left_members'],
                    'right_count' => $stats['total_right_members'],
                    'left_bv' => $stats['left_bv'],
                    'right_bv' => $stats['right_bv'],
                    'carry_left' => $stats['carry_left'],
                    'carry_right' => $stats['carry_right'],
                    'matched_pairs' => $stats['matched_pairs'],
                    'rank_name' => $stats['rank_info']['rank_name'],
                ]);
                $this->syncUplineCounts($parent);
            }
        });
    }

    /**
     * Recursively build hierarchy tree for FigJam canvas.
     */
    public function buildHierarchyTree(BinaryNode $node, array &$allNodeIds = [], int $depth = 1): array
    {
        $stats = $this->calculateDynamicStats($node);
        $formatted = $this->formatNodeForView($node, $stats);
        $allNodeIds[] = $node->id;

        // Left Child
        $left = $this->getDirectLeftChild($node);
        if ($left) {
            $formatted['left'] = $this->buildHierarchyTree($left, $allNodeIds, $depth + 1);
        } else {
            $formatted['left'] = $this->formatVacantSlot($node->id, 'left');
        }

        // Right Child
        $right = $this->getDirectRightChild($node);
        if ($right) {
            $formatted['right'] = $this->buildHierarchyTree($right, $allNodeIds, $depth + 1);
        } else {
            $formatted['right'] = $this->formatVacantSlot($node->id, 'right');
        }

        $formatted['depth'] = $depth;
        return $formatted;
    }

    /**
     * Find extreme left descendant of a node.
     */
    public function getExtremeLeft(BinaryNode $node): BinaryNode
    {
        $current = $node;
        while ($left = $this->getDirectLeftChild($current)) {
            $current = $left;
        }
        return $current;
    }

    /**
     * Find extreme right descendant of a node.
     */
    public function getExtremeRight(BinaryNode $node): BinaryNode
    {
        $current = $node;
        while ($right = $this->getDirectRightChild($current)) {
            $current = $right;
        }
        return $current;
    }

    /**
     * Format a node for UI presentation with dynamic statistics.
     */
    public function formatNodeForView(BinaryNode $node, ?array $stats = null): array
    {
        $stats = $stats ?: $this->calculateDynamicStats($node);
        $node->loadMissing(['sponsor', 'parent', 'investments']);

        $sponsorName = $node->sponsor_name ?: ($node->sponsor?->member_name ?? ($node->parent?->member_name ?? 'Md Abdul Hai'));
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

        return [
            'is_vacant' => false,
            'id' => $node->id,
            'member_name' => $node->member_name,
            'member_code' => $node->member_code,
            'username' => $username,
            'phone' => $node->phone ?: '01700000000',
            'email' => $node->email ?: 'tahmina787162@gmail.com',
            'password_plain' => $node->password_plain ?: 'sbl123456',
            'tpin' => $node->tpin ?: '1234',
            'sponsor_id' => $node->sponsor_id,
            'sponsor_name' => $sponsorName,
            'user_id' => $node->user_id,
            'is_active' => (bool)$node->is_active,
            'package_name' => $node->package_name,
            'point_value' => $stats['own_pv'],
            'total_investment' => $stats['own_investment'],
            'contributions' => $investmentsList,
            'rank_name' => $stats['rank_info']['rank_name'],
            'rank_code' => $stats['rank_info']['rank_code'],
            'is_fme' => $stats['rank_info']['is_fme'],
            'position' => $node->position,
            'left_count' => $stats['total_left_members'],
            'right_count' => $stats['total_right_members'],
            'left_display' => $stats['rank_info']['left_display'],
            'right_display' => $stats['rank_info']['right_display'],
            'left_target_count' => 5,
            'right_target_count' => 5,
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
     * Format an empty slot for UI.
     */
    public function formatVacantSlot(int $parentId, string $position): array
    {
        $parent = BinaryNode::find($parentId);
        return [
            'is_vacant' => true,
            'parent_id' => $parentId,
            'parent_name' => $parent ? $parent->member_name : 'Upline',
            'parent_code' => $parent ? $parent->member_code : '',
            'position' => $position,
        ];
    }
}
