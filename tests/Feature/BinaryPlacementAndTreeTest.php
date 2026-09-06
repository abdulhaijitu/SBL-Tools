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
     * Expected: LEFT empty, RIGHT empty, counts 0/0.
     */
    public function test_1_member_with_no_children(): void
    {
        $root = BinaryNode::create([
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        $stats = $this->treeService->calculateDynamicStats($root);

        $this->assertNull($this->treeService->getDirectLeftChild($root));
        $this->assertNull($this->treeService->getDirectRightChild($root));
        $this->assertEquals(0, $stats['total_left_members']);
        $this->assertEquals(0, $stats['total_right_members']);
        $this->assertEquals('Member', $stats['rank_info']['rank_code']);
    }

    /**
     * TEST 2: Member has one LEFT member.
     * Expected: Left Team = 1, Right Team = 0.
     */
    public function test_2_member_with_one_left_child(): void
    {
        $root = BinaryNode::create([
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        $tahmina = $this->treeService->placeMember([
            'member_name' => 'Tahmina',
            'member_code' => '@tahmina',
            'parent_id' => $root->id,
            'position' => 'left',
            'package_name' => 'National 120k',
            'point_value' => 100.00,
        ]);

        $stats = $this->treeService->calculateDynamicStats($root);

        $this->assertNotNull($this->treeService->getDirectLeftChild($root));
        $this->assertEquals($tahmina->id, $this->treeService->getDirectLeftChild($root)->id);
        $this->assertNull($this->treeService->getDirectRightChild($root));
        $this->assertEquals(1, $stats['total_left_members']);
        $this->assertEquals(0, $stats['total_right_members']);
    }

    /**
     * TEST 3: Left member has another child.
     * Expected: Root Left Team = 2.
     */
    public function test_3_descendant_increases_root_left_team_count(): void
    {
        $root = BinaryNode::create([
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        $tahmina = $this->treeService->placeMember([
            'member_name' => 'Tahmina',
            'member_code' => '@tahmina',
            'parent_id' => $root->id,
            'position' => 'left',
            'package_name' => 'National 120k',
        ]);

        $memberA = $this->treeService->placeMember([
            'member_name' => 'Member A',
            'member_code' => '@member_a',
            'parent_id' => $tahmina->id,
            'position' => 'left',
            'package_name' => 'National 120k',
        ]);

        $stats = $this->treeService->calculateDynamicStats($root);

        $this->assertEquals(2, $stats['total_left_members']);
        $this->assertEquals(0, $stats['total_right_members']);
    }

    /**
     * TEST 4: Root reaches Left 5 + Right 5.
     * Expected: Rank = FME.
     */
    public function test_4_root_reaches_left_5_and_right_5_qualifies_for_fme(): void
    {
        $root = BinaryNode::create([
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        // Place 5 members in Left tree (true binary placement)
        $l1 = $this->treeService->placeMember(['member_name' => 'L1', 'member_code' => '@l1', 'parent_id' => $root->id, 'position' => 'left', 'point_value' => 100]);
        $l2 = $this->treeService->placeMember(['member_name' => 'L2', 'member_code' => '@l2', 'parent_id' => $l1->id, 'position' => 'left', 'point_value' => 100]);
        $l3 = $this->treeService->placeMember(['member_name' => 'L3', 'member_code' => '@l3', 'parent_id' => $l1->id, 'position' => 'right', 'point_value' => 100]);
        $l4 = $this->treeService->placeMember(['member_name' => 'L4', 'member_code' => '@l4', 'parent_id' => $l2->id, 'position' => 'left', 'point_value' => 100]);
        $l5 = $this->treeService->placeMember(['member_name' => 'L5', 'member_code' => '@l5', 'parent_id' => $l2->id, 'position' => 'right', 'point_value' => 100]);

        // Place 5 members in Right tree
        $r1 = $this->treeService->placeMember(['member_name' => 'R1', 'member_code' => '@r1', 'parent_id' => $root->id, 'position' => 'right', 'point_value' => 100]);
        $r2 = $this->treeService->placeMember(['member_name' => 'R2', 'member_code' => '@r2', 'parent_id' => $r1->id, 'position' => 'left', 'point_value' => 100]);
        $r3 = $this->treeService->placeMember(['member_name' => 'R3', 'member_code' => '@r3', 'parent_id' => $r1->id, 'position' => 'right', 'point_value' => 100]);
        $r4 = $this->treeService->placeMember(['member_name' => 'R4', 'member_code' => '@r4', 'parent_id' => $r2->id, 'position' => 'left', 'point_value' => 100]);
        $r5 = $this->treeService->placeMember(['member_name' => 'R5', 'member_code' => '@r5', 'parent_id' => $r2->id, 'position' => 'right', 'point_value' => 100]);

        $stats = $this->treeService->calculateDynamicStats($root);

        $this->assertEquals(5, $stats['total_left_members']);
        $this->assertEquals(5, $stats['total_right_members']);
        $this->assertTrue($stats['rank_info']['is_fme']);
        $this->assertEquals('FME', $stats['rank_info']['rank_code']);
        $this->assertEquals('5', $stats['rank_info']['left_display']);
        $this->assertEquals('5', $stats['rank_info']['right_display']);
    }

    /**
     * TEST 5: Add another member after FME.
     * Expected: Tree accepts member and count becomes 6 or more (never capped at 5).
     */
    public function test_5_tree_continues_growing_beyond_fme(): void
    {
        $root = BinaryNode::create([
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        $l1 = $this->treeService->placeMember(['member_name' => 'L1', 'member_code' => '@l1', 'parent_id' => $root->id, 'position' => 'left', 'point_value' => 100]);
        $l2 = $this->treeService->placeMember(['member_name' => 'L2', 'member_code' => '@l2', 'parent_id' => $l1->id, 'position' => 'left', 'point_value' => 100]);
        $l3 = $this->treeService->placeMember(['member_name' => 'L3', 'member_code' => '@l3', 'parent_id' => $l1->id, 'position' => 'right', 'point_value' => 100]);
        $l4 = $this->treeService->placeMember(['member_name' => 'L4', 'member_code' => '@l4', 'parent_id' => $l2->id, 'position' => 'left', 'point_value' => 100]);
        $l5 = $this->treeService->placeMember(['member_name' => 'L5', 'member_code' => '@l5', 'parent_id' => $l2->id, 'position' => 'right', 'point_value' => 100]);

        // Add 6th member in Left team
        $l6 = $this->treeService->placeMember(['member_name' => 'L6', 'member_code' => '@l6', 'parent_id' => $l3->id, 'position' => 'left', 'point_value' => 100]);

        $stats = $this->treeService->calculateDynamicStats($root);

        $this->assertEquals(6, $stats['total_left_members']);
        $this->assertEquals('6', $stats['rank_info']['left_display']);
    }

    /**
     * TEST 6: Open any downline member as root.
     * Expected: Their independent Left/Right tree and statistics display correctly.
     */
    public function test_6_downline_member_has_independent_tree_and_statistics(): void
    {
        $root = BinaryNode::create([
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        $tahmina = $this->treeService->placeMember(['member_name' => 'Tahmina', 'member_code' => '@tahmina', 'parent_id' => $root->id, 'position' => 'left', 'point_value' => 100]);
        $khaled = $this->treeService->placeMember(['member_name' => 'Khaled', 'member_code' => '@khaled', 'parent_id' => $root->id, 'position' => 'right', 'point_value' => 100]);

        // Children under Tahmina
        $tahminaLeft = $this->treeService->placeMember(['member_name' => 'Tahmina Left', 'member_code' => '@t_left', 'parent_id' => $tahmina->id, 'position' => 'left', 'point_value' => 100]);
        $tahminaRight = $this->treeService->placeMember(['member_name' => 'Tahmina Right', 'member_code' => '@t_right', 'parent_id' => $tahmina->id, 'position' => 'right', 'point_value' => 100]);

        // View Tahmina tree
        $tahminaStats = $this->treeService->calculateDynamicStats($tahmina);

        $this->assertEquals(1, $tahminaStats['total_left_members']);
        $this->assertEquals(1, $tahminaStats['total_right_members']);
        $this->assertEquals('1/5', $tahminaStats['rank_info']['left_display']);
        $this->assertEquals('1/5', $tahminaStats['rank_info']['right_display']);

        // View Khaled tree
        $khaledStats = $this->treeService->calculateDynamicStats($khaled);
        $this->assertEquals(0, $khaledStats['total_left_members']);
        $this->assertEquals(0, $khaledStats['total_right_members']);
    }

    /**
     * TEST 7: Member invests multiple times.
     * Expected: Only one tree node exists, investment total increases.
     */
    public function test_7_member_multiple_investments(): void
    {
        $root = BinaryNode::create([
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        $member = $this->treeService->placeMember([
            'member_name' => 'Investor Member',
            'member_code' => '@investor',
            'parent_id' => $root->id,
            'position' => 'left',
            'point_value' => 100.00,
        ]);

        // Add 2nd and 3rd investments
        $this->treeService->addInvestment($member, [
            'amount' => 500.00,
            'point_value' => 500.00,
            'plan_name' => 'Topup Package',
        ]);

        $this->treeService->addInvestment($member, [
            'amount' => 250.00,
            'point_value' => 250.00,
            'plan_name' => 'Bonus Package',
        ]);

        // Only one tree node exists
        $this->assertEquals(1, BinaryNode::where('member_code', '@investor')->count());

        // 3 investment records exist
        $this->assertEquals(3, Investment::where('binary_node_id', $member->id)->count());

        // Total investment is 100 + 500 + 250 = 850
        $totalInv = $this->treeService->getNodeTotalInvestmentAmount($member);
        $this->assertEquals(850.00, $totalInv);
    }

    /**
     * TEST 8: Try adding two direct LEFT children.
     * Expected: Reject the second placement with exception.
     */
    public function test_8_cannot_add_two_direct_left_children(): void
    {
        $root = BinaryNode::create([
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        $this->treeService->placeMember([
            'member_name' => 'First Left',
            'member_code' => '@first_left',
            'parent_id' => $root->id,
            'position' => 'left',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Slot 'left' under Md Abdul Hai is already occupied.");

        // Attempting second direct left under root must throw exception
        $this->treeService->placeMember([
            'member_name' => 'Second Left',
            'member_code' => '@second_left',
            'parent_id' => $root->id,
            'position' => 'left',
        ]);
    }

    /**
     * TEST 9: Try circular placement.
     * Expected: Reject.
     */
    public function test_9_reject_circular_placement(): void
    {
        $root = BinaryNode::create([
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        $child = $this->treeService->placeMember([
            'member_name' => 'Child Node',
            'member_code' => '@child',
            'parent_id' => $root->id,
            'position' => 'left',
        ]);

        // Attempt to place root under child
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Circular placement detected");

        $this->treeService->placeMember([
            'id' => $root->id,
            'member_name' => 'Md Abdul Hai',
            'parent_id' => $child->id,
            'position' => 'left',
        ]);
    }

    /**
     * TEST 10: Large tree performance test.
     * Expected: Tree calculations and views load fast and efficiently.
     */
    public function test_10_large_tree_performance(): void
    {
        $root = BinaryNode::create([
            'member_name' => 'Md Abdul Hai',
            'member_code' => '@abdulhai',
            'point_value' => 100.00,
        ]);

        // Build a 4-level full binary tree (15 nodes total)
        $queue = [$root];
        $createdCount = 1;

        for ($depth = 1; $depth <= 3; $depth++) {
            $nextQueue = [];
            foreach ($queue as $parent) {
                $leftChild = $this->treeService->placeMember([
                    'member_name' => "Node L {$createdCount}",
                    'member_code' => "@node_l_{$createdCount}",
                    'parent_id' => $parent->id,
                    'position' => 'left',
                    'point_value' => 100.00,
                ]);
                $createdCount++;

                $rightChild = $this->treeService->placeMember([
                    'member_name' => "Node R {$createdCount}",
                    'member_code' => "@node_r_{$createdCount}",
                    'parent_id' => $parent->id,
                    'position' => 'right',
                    'point_value' => 100.00,
                ]);
                $createdCount++;

                $nextQueue[] = $leftChild;
                $nextQueue[] = $rightChild;
            }
            $queue = $nextQueue;
        }

        $startTime = microtime(true);
        $treeData = $this->treeService->getVisualTree($root->id, 3);
        $elapsed = microtime(true) - $startTime;

        $this->assertLessThan(1.0, $elapsed); // Must render in under 1 second
        $this->assertEquals(7, $treeData['stats']['left_count']);
        $this->assertEquals(7, $treeData['stats']['right_count']);
        $this->assertEquals(15, $treeData['stats']['total_members']);
        $this->assertTrue($treeData['stats']['is_fme']);
        $this->assertEquals('Field Marketing Executive', $treeData['stats']['rank_name']);
    }
}
