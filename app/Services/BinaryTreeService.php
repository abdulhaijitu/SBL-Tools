<?php

namespace App\Services;

use App\Models\BinaryNode;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BinaryTreeService
{
    /**
     * Get visual tree data starting from a root node down to specified depth (default: 3 levels).
     */
    public function getVisualTree(?int $rootId = null, int $maxLevels = 3): array
    {
        $root = null;
        if ($rootId) {
            $root = BinaryNode::with(['user'])->find($rootId);
        }

        if (! $root) {
            $root = BinaryNode::with(['user'])->whereNull('parent_id')->orderBy('id')->first();
        }

        if (! $root) {
            return [
                'root' => null,
                'levels' => [],
                'stats' => null,
            ];
        }

        $levels = [];
        // Level 1: Root
        $levels[1] = [$this->formatNodeForView($root)];

        // Queue for BFS traversal up to maxLevels
        $currentLevelNodes = [$root];

        for ($level = 2; $level <= $maxLevels; $level++) {
            $nextLevelNodes = [];
            $levelData = [];

            foreach ($currentLevelNodes as $parent) {
                if ($parent && ! ($parent->is_vacant ?? false)) {
                    // Left Child
                    $left = BinaryNode::with(['user'])
                        ->where('parent_id', $parent->id)
                        ->where('position', 'left')
                        ->first();

                    if ($left) {
                        $levelData[] = $this->formatNodeForView($left);
                        $nextLevelNodes[] = $left;
                    } else {
                        $vacantLeft = $this->formatVacantSlot($parent->id, 'left');
                        $levelData[] = $vacantLeft;
                        $nextLevelNodes[] = null;
                    }

                    // Right Child
                    $right = BinaryNode::with(['user'])
                        ->where('parent_id', $parent->id)
                        ->where('position', 'right')
                        ->first();

                    if ($right) {
                        $levelData[] = $this->formatNodeForView($right);
                        $nextLevelNodes[] = $right;
                    } else {
                        $vacantRight = $this->formatVacantSlot($parent->id, 'right');
                        $levelData[] = $vacantRight;
                        $nextLevelNodes[] = null;
                    }
                } else {
                    // Symmetrical empty placeholders for balanced visual rendering
                    $levelData[] = null;
                    $levelData[] = null;
                    $nextLevelNodes[] = null;
                    $nextLevelNodes[] = null;
                }
            }

            $levels[$level] = $levelData;
            $currentLevelNodes = $nextLevelNodes;
        }

        return [
            'root' => $root,
            'levels' => $levels,
            'stats' => [
                'total_members' => $root->total_team_count + 1,
                'left_count' => $root->left_count,
                'right_count' => $root->right_count,
                'left_bv' => $root->left_bv,
                'right_bv' => $root->right_bv,
                'carry_left' => $root->carry_left,
                'carry_right' => $root->carry_right,
                'matched_pairs' => $root->matched_pairs,
                'weaker_leg' => $root->weaker_leg,
            ],
        ];
    }

    /**
     * Place a new member in the binary tree under parent and position.
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

                // Verify slot is vacant
                $existing = BinaryNode::where('parent_id', $parentId)
                    ->where('position', $position)
                    ->exists();

                if ($existing) {
                    throw new InvalidArgumentException("Slot '{$position}' under {$parent->member_name} is already occupied.");
                }
            }

            // Generate unique member code if not provided
            $memberCode = $data['member_code'] ?? null;
            if (! $memberCode) {
                $nextId = (BinaryNode::max('id') ?? 0) + 1001;
                $memberCode = 'SBL-' . $nextId;
            }

            // Points calculation based on package
            $pointValue = $data['point_value'] ?? 100.00;
            if (isset($data['package_name'])) {
                if (str_contains(strtolower($data['package_name']), '550')) {
                    $pointValue = 500.00;
                } elseif (str_contains(strtolower($data['package_name']), '120')) {
                    $pointValue = 100.00;
                }
            }

            $contributions = $data['contributions'] ?? [
                ['amount' => (float)$pointValue, 'date' => now()->toDateString(), 'note' => $data['package_name'] ?? 'Initial Package']
            ];

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
                'package_name' => $data['package_name'] ?? 'National 120k',
                'point_value' => $pointValue,
                'contributions' => $contributions,
                'left_target_count' => (int)($data['left_target_count'] ?? 0),
                'right_target_count' => (int)($data['right_target_count'] ?? 0),
                'rank_name' => $data['rank_name'] ?? 'Member',
                'joined_at' => now(),
            ]);

            // Propagate volume and counts upwards
            $this->propagateUpline($node);

            return $node;
        });
    }

    /**
     * Recursively update left/right counts, point volume, and pair matching up the tree.
     */
    protected function propagateUpline(BinaryNode $newNode): void
    {
        $current = $newNode;
        $pv = (float)$newNode->point_value;

        while ($current->parent_id) {
            $parent = BinaryNode::find($current->parent_id);
            if (! $parent) {
                break;
            }

            $pos = $current->position;

            if ($pos === 'left') {
                $parent->left_count += 1;
                $parent->left_bv += $pv;
                $parent->carry_left += $pv;
            } elseif ($pos === 'right') {
                $parent->right_count += 1;
                $parent->right_bv += $pv;
                $parent->carry_right += $pv;
            }

            // Binary 1:1 matching calculation (100 BV pair unit)
            $pairUnit = 100.00;
            $possiblePairs = (int)floor(min($parent->carry_left, $parent->carry_right) / $pairUnit);

            if ($possiblePairs > 0) {
                $parent->matched_pairs += $possiblePairs;
                $deduction = $possiblePairs * $pairUnit;
                $parent->carry_left = max(0, $parent->carry_left - $deduction);
                $parent->carry_right = max(0, $parent->carry_right - $deduction);
            }

            $parent->save();
            $current = $parent;
        }
    }

    /**
     * Update an existing member in the binary tree.
     */
    public function updateMember(BinaryNode $node, array $data): BinaryNode
    {
        return DB::transaction(function () use ($node, $data) {
            $oldPv = (float)$node->point_value;
            $newPv = isset($data['point_value']) && $data['point_value'] !== '' ? (float)$data['point_value'] : $oldPv;

            // Handle multiple contributions if passed
            $contributions = $node->contributions ?: [];
            if (isset($data['contributions'])) {
                if (is_string($data['contributions'])) {
                    $contributions = json_decode($data['contributions'], true) ?: [];
                } elseif (is_array($data['contributions'])) {
                    $contributions = $data['contributions'];
                }

                // If contributions exist, recalculate point_value
                if (!empty($contributions) && is_array($contributions)) {
                    $sum = 0.0;
                    foreach ($contributions as $item) {
                        $sum += (float)($item['amount'] ?? 0);
                    }
                    if ($sum > 0 || count($contributions) > 0) {
                        $newPv = $sum;
                    }
                }
            }

            if (isset($data['package_name']) && $data['package_name'] !== $node->package_name && !isset($data['point_value']) && !isset($data['contributions'])) {
                if (str_contains(strtolower($data['package_name']), '550')) {
                    $newPv = 500.00;
                } elseif (str_contains(strtolower($data['package_name']), '120')) {
                    $newPv = 100.00;
                } elseif (str_contains(strtolower($data['package_name']), '25')) {
                    $newPv = 25.00;
                }
            }

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
                'point_value' => $newPv,
                'contributions' => $contributions,
                'rank_name' => $data['rank_name'] ?? $node->rank_name,
                'sponsor_id' => array_key_exists('sponsor_id', $data) ? ($data['sponsor_id'] ? (int)$data['sponsor_id'] : null) : $node->sponsor_id,
                'sponsor_name' => array_key_exists('sponsor_name', $data) ? $data['sponsor_name'] : $node->sponsor_name,
                'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : $node->is_active,
                'user_id' => array_key_exists('user_id', $data) ? ($data['user_id'] ? (int)$data['user_id'] : null) : $node->user_id,
            ];

            if (isset($data['left_count']) && $data['left_count'] !== '') {
                $updateData['left_count'] = (int)$data['left_count'];
            }
            if (isset($data['left_target_count']) && $data['left_target_count'] !== '') {
                $updateData['left_target_count'] = (int)$data['left_target_count'];
            }
            if (isset($data['right_count']) && $data['right_count'] !== '') {
                $updateData['right_count'] = (int)$data['right_count'];
            }
            if (isset($data['right_target_count']) && $data['right_target_count'] !== '') {
                $updateData['right_target_count'] = (int)$data['right_target_count'];
            }
            if (isset($data['left_bv']) && $data['left_bv'] !== '') {
                $updateData['left_bv'] = (float)$data['left_bv'];
            }
            if (isset($data['right_bv']) && $data['right_bv'] !== '') {
                $updateData['right_bv'] = (float)$data['right_bv'];
            }

            $node->update($updateData);

            // If PV changed, propagate difference upline
            $pvDiff = $newPv - $oldPv;
            if (abs($pvDiff) > 0.001) {
                $this->propagatePvDifference($node, $pvDiff);
            }

            return $node;
        });
    }

    /**
     * Delete a node from the binary tree.
     * Only leaf nodes (nodes without children) can be removed to preserve tree integrity.
     */
    public function deleteNode(BinaryNode $node): void
    {
        DB::transaction(function () use ($node) {
            if ($node->children()->exists()) {
                throw new InvalidArgumentException("এই মেম্বারের ডাউনলাইনে সক্রিয় টিম মেম্বার রয়েছে। ট্রি স্ট্রাকচার অক্ষুণ্ণ রাখতে ডাউনলাইন মেম্বারসহ নোড সরাসরি মুছে ফেলা যাবে না।");
            }

            // Rollback upline counts and volume
            $this->rollbackUpline($node);

            $node->delete();
        });
    }

    /**
     * Subtract counts and point volume from ancestors when a leaf node is removed.
     */
    protected function rollbackUpline(BinaryNode $node): void
    {
        $current = $node;
        $pv = (float)$node->point_value;

        while ($current->parent_id) {
            $parent = BinaryNode::find($current->parent_id);
            if (! $parent) {
                break;
            }

            $pos = $current->position;

            if ($pos === 'left') {
                $parent->left_count = max(0, $parent->left_count - 1);
                $parent->left_bv = max(0, $parent->left_bv - $pv);
                $parent->carry_left = max(0, $parent->carry_left - $pv);
            } elseif ($pos === 'right') {
                $parent->right_count = max(0, $parent->right_count - 1);
                $parent->right_bv = max(0, $parent->right_bv - $pv);
                $parent->carry_right = max(0, $parent->carry_right - $pv);
            }

            $parent->save();
            $current = $parent;
        }
    }

    /**
     * Propagate difference in point value up the tree when node package/PV changes.
     */
    protected function propagatePvDifference(BinaryNode $node, float $pvDiff): void
    {
        $current = $node;

        while ($current->parent_id) {
            $parent = BinaryNode::find($current->parent_id);
            if (! $parent) {
                break;
            }

            $pos = $current->position;

            if ($pos === 'left') {
                $parent->left_bv = max(0, $parent->left_bv + $pvDiff);
                $parent->carry_left = max(0, $parent->carry_left + $pvDiff);
            } elseif ($pos === 'right') {
                $parent->right_bv = max(0, $parent->right_bv + $pvDiff);
                $parent->carry_right = max(0, $parent->carry_right + $pvDiff);
            }

            // Recheck pair matching
            $pairUnit = 100.00;
            $possiblePairs = (int)floor(min($parent->carry_left, $parent->carry_right) / $pairUnit);
            if ($possiblePairs > 0) {
                $parent->matched_pairs += $possiblePairs;
                $deduction = $possiblePairs * $pairUnit;
                $parent->carry_left = max(0, $parent->carry_left - $deduction);
                $parent->carry_right = max(0, $parent->carry_right - $deduction);
            }

            $parent->save();
            $current = $parent;
        }
    }

    /**
     * Find extreme left descendant of a node.
     */
    public function getExtremeLeft(BinaryNode $node): BinaryNode
    {
        $current = $node;
        while ($left = $current->leftChild) {
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
        while ($right = $current->rightChild) {
            $current = $right;
        }
        return $current;
    }

    protected function formatNodeForView(BinaryNode $node): array
    {
        $node->loadMissing(['sponsor', 'parent']);
        $sponsorName = $node->sponsor_name ?: ($node->sponsor?->member_name ?? ($node->parent?->member_name ?? 'Md Abdul Hai'));

        $code = $node->member_code ?: ('SBL-' . $node->id);
        $username = str_starts_with($code, '@') ? $code : ('@' . strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $code)));

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
            'point_value' => (float)$node->point_value,
            'contributions' => $node->contributions ?: [
                ['amount' => (float)$node->point_value, 'date' => $node->created_at ? $node->created_at->toDateString() : now()->toDateString(), 'note' => $node->package_name ?: 'Initial']
            ],
            'rank_name' => $node->rank_name ?: 'NA',
            'position' => $node->position,
            'left_count' => $node->left_count,
            'left_target_count' => $node->left_target_count ?: $node->left_count,
            'right_count' => $node->right_count,
            'right_target_count' => $node->right_target_count ?: $node->right_count,
            'left_bv' => (float)$node->left_bv,
            'right_bv' => (float)$node->right_bv,
            'carry_left' => (float)$node->carry_left,
            'carry_right' => (float)$node->carry_right,
            'matched_pairs' => $node->matched_pairs,
            'weaker_leg' => $node->weaker_leg,
            'parent_id' => $node->parent_id,
            'has_children' => $node->children()->exists(),
        ];
    }

    protected function formatVacantSlot(int $parentId, string $position): array
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
