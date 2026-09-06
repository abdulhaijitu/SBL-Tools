<?php

namespace Tests\Feature;

use App\Models\BinaryNode;
use App\Models\Investment;
use App\Models\User;
use App\Services\BinaryTreeService;
use App\Services\RankService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class BinaryPlacementAndTreeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected BinaryTreeService $treeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::first();
        $this->treeService = new BinaryTreeService(new RankService());
    }

    /**
     * TEST 1: Member has no children.
     * Expected: 5 LEFT vacant, 5 RIGHT vacant, counts 0/0.
     */
    public function test_1_member_with_no_children(): void
    {
        $root = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        $stats = $this->treeService->calculateDynamicStats($root);

        $this->assertEmpty($this->treeService->getDirectChildrenByBranch($root, 'LEFT'));
        $this->assertEmpty($this->treeService->getDirectChildrenByBranch($root, 'RIGHT'));
        $this->assertEquals(0, $stats['direct_left_count']);
        $this->assertEquals(0, $stats['direct_right_count']);
        $this->assertEquals(0, $stats['total_left_network']);
        $this->assertEquals(0, $stats['total_right_network']);
        $this->assertEquals('Member', $stats['rank_info']['rank_code']);
    }

    /**
     * TEST 2: Member has one LEFT member in Slot-1.
     * Expected: Direct Left = 1/5, Total Left Network = 1, Right = 0.
     */
    public function test_2_member_with_one_left_child(): void
    {
        $root = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        $tahmina = $this->treeService->placeMember([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Tahmina',
            'member_code' => '@tahmina',
            'parent_id' => $root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
            'point_value' => 100.00,
        ]);

        $stats = $this->treeService->calculateDynamicStats($root);

        $leftChildren = $this->treeService->getDirectChildrenByBranch($root, 'LEFT');
        $this->assertCount(1, $leftChildren);
        $this->assertEquals($tahmina->id, $leftChildren[1]->id);
        $this->assertEquals(1, $stats['direct_left_count']);
        $this->assertEquals(0, $stats['direct_right_count']);
        $this->assertEquals(1, $stats['total_left_network']);
        $this->assertEquals(0, $stats['total_right_network']);
    }

    /**
     * TEST 3: Left member (Tahmina) places a child in her own Left Slot-1.
     * Expected: Root Direct Left = 1, Root Total Left Network = 2.
     */
    public function test_3_descendant_increases_root_left_network_count(): void
    {
        $root = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        $tahmina = $this->treeService->placeMember([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Tahmina',
            'member_code' => '@tahmina',
            'parent_id' => $root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
        ]);

        $memberA = $this->treeService->placeMember([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Member A',
            'member_code' => '@member_a',
            'parent_id' => $tahmina->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
        ]);

        $stats = $this->treeService->calculateDynamicStats($root);

        $this->assertEquals(1, $stats['direct_left_count']);
        $this->assertEquals(2, $stats['total_left_network']);
        $this->assertEquals(0, $stats['total_right_network']);
    }

    /**
     * TEST 4: Root fills all 5 Direct Left slots and 5 Direct Right slots.
     * Expected: Direct L = 5/5, Direct R = 5/5, Rank = FME.
     */
    public function test_4_root_fills_all_10_direct_slots_qualifies_for_fme(): void
    {
        $root = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        // Place 5 direct Left slots (L1..L5)
        for ($i = 1; $i <= 5; $i++) {
            $this->treeService->placeMember([
                'tree_owner_id' => $this->admin->id,
                'member_name' => "Left Member {$i}",
                'member_code' => "@left_{$i}",
                'parent_id' => $root->id,
                'branch' => 'LEFT',
                'slot_number' => $i,
                'point_value' => 100.00,
            ]);
        }

        // Place 5 direct Right slots (R1..R5)
        for ($i = 1; $i <= 5; $i++) {
            $this->treeService->placeMember([
                'tree_owner_id' => $this->admin->id,
                'member_name' => "Right Member {$i}",
                'member_code' => "@right_{$i}",
                'parent_id' => $root->id,
                'branch' => 'RIGHT',
                'slot_number' => $i,
                'point_value' => 100.00,
            ]);
        }

        $stats = $this->treeService->calculateDynamicStats($root);

        $this->assertEquals(5, $stats['direct_left_count']);
        $this->assertEquals(5, $stats['direct_right_count']);
        $this->assertEquals('5/5', $stats['direct_left_display']);
        $this->assertEquals('5/5', $stats['direct_right_display']);
        $this->assertTrue($stats['is_fme']);
        $this->assertEquals('FME', $stats['rank_info']['rank_code']);
    }

    /**
     * TEST 5: Try placing a 6th direct member on LEFT branch.
     * Expected: Rejection since max direct per branch is 5.
     */
    public function test_5_cannot_exceed_five_direct_members_per_branch(): void
    {
        $root = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $this->treeService->placeMember([
                'tree_owner_id' => $this->admin->id,
                'member_name' => "Left Member {$i}",
                'member_code' => "@left_{$i}",
                'parent_id' => $root->id,
                'branch' => 'LEFT',
                'slot_number' => $i,
                'point_value' => 100.00,
            ]);
        }

        $this->expectException(InvalidArgumentException::class);

        // Attempt to place in slot 6 or exceed 5
        $this->treeService->placeMember([
            'tree_owner_id' => $this->admin->id,
            'member_name' => "Left Member 6",
            'member_code' => "@left_6",
            'parent_id' => $root->id,
            'branch' => 'LEFT',
            'slot_number' => 6,
            'point_value' => 100.00,
        ]);
    }

    /**
     * TEST 6: Try placing into an already occupied slot.
     * Expected: Rejection.
     */
    public function test_6_cannot_place_into_occupied_slot(): void
    {
        $root = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        $this->treeService->placeMember([
            'tree_owner_id' => $this->admin->id,
            'member_name' => "First Slot 1",
            'member_code' => "@slot1_a",
            'parent_id' => $root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Slot LEFT-1 under Md Abdul Hai is already occupied.");

        $this->treeService->placeMember([
            'tree_owner_id' => $this->admin->id,
            'member_name' => "Second Slot 1",
            'member_code' => "@slot1_b",
            'parent_id' => $root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
        ]);
    }

    /**
     * TEST 7: Target member creation and conversion to active.
     */
    public function test_7_target_member_creation_and_conversion(): void
    {
        $root = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        // Place a target/planned member
        $target = $this->treeService->placeMember([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Target Member A',
            'member_code' => '@target_a',
            'parent_id' => $root->id,
            'branch' => 'LEFT',
            'slot_number' => 2,
            'is_target' => true,
            'target_date' => now()->addWeeks(2)->toDateString(),
            'target_notes' => 'Will join after salary',
        ]);

        $this->assertTrue($target->is_target);

        $statsBefore = $this->treeService->calculateDynamicStats($root);
        $this->assertEquals(1, $statsBefore['target_left_count']);
        $this->assertEquals(0, $statsBefore['active_left_count']);

        // Convert target to active
        $this->treeService->convertToActive($target);
        $target->refresh();

        $this->assertFalse($target->is_target);
        $this->assertTrue($target->is_active);

        $statsAfter = $this->treeService->calculateDynamicStats($root);
        $this->assertEquals(0, $statsAfter['target_left_count']);
        $this->assertEquals(1, $statsAfter['active_left_count']);
    }

    /**
     * TEST 8: User workspace isolation.
     */
    public function test_8_user_workspace_isolation(): void
    {
        $user2 = User::factory()->create(['name' => 'User 2', 'email' => 'user2@sbl.test']);

        // Auto-ensure user2 root
        $user2Root = $this->treeService->ensureUserRoot($user2);

        $this->assertEquals($user2->id, $user2Root->tree_owner_id);
        $this->assertEquals('User 2', $user2Root->member_name);

        // User 2 places a member in their tree
        $user2Child = $this->treeService->placeMember([
            'tree_owner_id' => $user2->id,
            'member_name' => 'User 2 Child',
            'member_code' => '@u2_child',
            'parent_id' => $user2Root->id,
            'branch' => 'RIGHT',
            'slot_number' => 1,
        ]);

        // Query user2 tree
        $user2Tree = $this->treeService->getVisualTree($user2Root->id, $user2->id);
        $this->assertEquals('User 2', $user2Tree['stats']['root_name']);
        $this->assertEquals(1, $user2Tree['stats']['direct_right_count']);
    }
}
