<?php

namespace Tests\Feature;

use App\Models\BinaryNode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BinaryTeamCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected BinaryNode $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@sbl.test',
            'name' => 'Admin User',
        ]);

        $this->root = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'SBL Founder',
            'member_code' => 'SBL-ROOT',
            'package_name' => 'International 550k',
            'point_value' => 500.00,
            'left_count' => 0,
            'right_count' => 0,
            'left_bv' => 0.00,
            'right_bv' => 0.00,
            'carry_left' => 0.00,
            'carry_right' => 0.00,
            'matched_pairs' => 0,
            'rank_name' => 'Crown Director',
            'is_active' => true,
        ]);
    }

    public function test_authenticated_user_can_view_binary_tree_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('binary.index'));

        $response->assertStatus(200);
        $response->assertSee('SBL Founder');
        $response->assertSee('Explorer');
        $response->assertSee('Directory');
    }

    public function test_authenticated_user_can_view_binary_table_directory(): void
    {
        $response = $this->actingAs($this->admin)->get(route('binary.index', ['view' => 'table']));

        $response->assertStatus(200);
        $response->assertSee('10-Slot Member Directory');
        $response->assertSee('SBL Founder');
        $response->assertSee('SBL-ROOT');
    }

    public function test_admin_can_place_new_member_in_slot(): void
    {
        $response = $this->actingAs($this->admin)->post(route('binary.store'), [
            'parent_id' => $this->root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'Kamal Hossain',
            'member_code' => 'SBL-1002',
            'phone' => '01711223344',
            'package_name' => 'National 120k',
            'rank_name' => 'Member',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('binary_nodes', [
            'member_name' => 'Kamal Hossain',
            'parent_id' => $this->root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
        ]);

        $this->root->refresh();
        $this->assertEquals(1, $this->root->left_count);
        $this->assertEquals(100.00, (float)$this->root->left_bv);
    }

    public function test_admin_can_update_team_member(): void
    {
        $member = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Old Member Name',
            'member_code' => 'SBL-1003',
            'parent_id' => $this->root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'phone' => '01700000000',
            'rank_name' => 'Member',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('binary.update', $member->id), [
            'member_name' => 'Updated Member Name',
            'phone' => '01899999999',
            'email' => 'updated@sbl.test',
            'package_name' => 'International 550k',
            'rank_name' => 'Gold Member',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $member->refresh();

        $this->assertEquals('Updated Member Name', $member->member_name);
        $this->assertEquals('01899999999', $member->phone);
        $this->assertEquals('Gold Member', $member->rank_name);
        $this->assertEquals('International 550k', $member->package_name);
    }

    public function test_admin_can_delete_leaf_member_with_upline_rollback(): void
    {
        $this->actingAs($this->admin)->post(route('binary.store'), [
            'parent_id' => $this->root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'Leaf Member',
            'member_code' => 'SBL-1005',
            'package_name' => 'National 120k',
        ]);

        $this->root->refresh();
        $this->assertEquals(1, $this->root->left_count);
        $this->assertEquals(100.00, (float)$this->root->left_bv);

        $leaf = BinaryNode::where('member_code', 'SBL-1005')->first();
        $this->assertNotNull($leaf);

        // Delete leaf node
        $response = $this->actingAs($this->admin)->delete(route('binary.destroy', $leaf->id));
        $response->assertRedirect();

        // Verify leaf deleted
        $this->assertDatabaseMissing('binary_nodes', [
            'id' => $leaf->id,
        ]);

        // Verify upline rolled back
        $this->root->refresh();
        $this->assertEquals(0, $this->root->left_count);
        $this->assertEquals(0.00, (float)$this->root->left_bv);
    }

    public function test_cannot_delete_member_with_active_downlines_without_cascade(): void
    {
        $child = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Parent Child',
            'member_code' => 'SBL-2001',
            'parent_id' => $this->root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
            'point_value' => 100.00,
        ]);

        BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Grand Child',
            'member_code' => 'SBL-2002',
            'parent_id' => $child->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
            'point_value' => 100.00,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('binary.destroy', $child->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('binary_nodes', [
            'id' => $child->id,
        ]);
    }

    public function test_admin_can_cascade_delete_member_with_active_downlines(): void
    {
        $child = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Parent Child',
            'member_code' => 'SBL-3001',
            'parent_id' => $this->root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
            'point_value' => 100.00,
        ]);

        $grandchild = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'member_name' => 'Grand Child',
            'member_code' => 'SBL-3002',
            'parent_id' => $child->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
            'point_value' => 100.00,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('binary.destroy', $child->id), [
            'cascade' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('binary_nodes', [
            'id' => $child->id,
        ]);
        $this->assertDatabaseMissing('binary_nodes', [
            'id' => $grandchild->id,
        ]);
    }

    public function test_admin_can_place_multiple_members_on_same_branch_different_slots(): void
    {
        // Place slot 1
        $r1 = $this->actingAs($this->admin)->post(route('binary.store'), [
            'parent_id' => $this->root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'Member Slot 1',
            'member_code' => 'SBL-SLOT1',
            'package_name' => 'National 120k',
        ]);
        $r1->assertSessionHasNoErrors();

        // Place slot 2 on SAME parent and SAME branch (previously failed with UNIQUE constraint)
        $r2 = $this->actingAs($this->admin)->post(route('binary.store'), [
            'parent_id' => $this->root->id,
            'branch' => 'LEFT',
            'slot_number' => 2,
            'member_name' => 'Member Slot 2',
            'member_code' => 'SBL-SLOT2',
            'package_name' => 'National 120k',
        ]);
        $r2->assertSessionHasNoErrors();

        $this->assertDatabaseHas('binary_nodes', [
            'parent_id' => $this->root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'Member Slot 1',
        ]);
        $this->assertDatabaseHas('binary_nodes', [
            'parent_id' => $this->root->id,
            'branch' => 'LEFT',
            'slot_number' => 2,
            'member_name' => 'Member Slot 2',
        ]);
    }

    public function test_admin_can_save_and_update_member_notes(): void
    {
        $response = $this->actingAs($this->admin)->post(route('binary.store'), [
            'parent_id' => $this->root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'Note Member',
            'member_code' => 'SBL-NOTE1',
            'package_name' => 'National 120k',
            'notes' => 'Important prospect discussion held on Monday.',
        ]);

        $response->assertSessionHasNoErrors();
        $member = BinaryNode::where('member_code', 'SBL-NOTE1')->firstOrFail();
        $this->assertEquals('Important prospect discussion held on Monday.', $member->notes);

        // Update note via PATCH endpoint
        $patchResponse = $this->actingAs($this->admin)->patchJson(route('team.update-notes', $member->id), [
            'notes' => 'Updated: Follow up confirmed for Friday.',
        ]);

        $patchResponse->assertOk();
        $patchResponse->assertJsonFragment([
            'success' => true,
            'notes' => 'Updated: Follow up confirmed for Friday.',
        ]);

        $member->refresh();
        $this->assertEquals('Updated: Follow up confirmed for Friday.', $member->notes);
    }

    public function test_admin_can_place_member_using_connector_id_and_code(): void
    {
        // 1. Verify view displays Member Connector option and Add Member button
        $viewResponse = $this->actingAs($this->admin)->get(route('binary.index'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Member Connector');
        $viewResponse->assertSee('Add Member');

        // 2. Place a member using connector_id
        $r1 = $this->actingAs($this->admin)->post(route('binary.store'), [
            'connector_id' => $this->root->id,
            'branch' => 'RIGHT',
            'slot_number' => 1,
            'member_name' => 'Connector Test Member 1',
            'member_code' => 'SBL-CONN1',
            'package_name' => 'National 120k',
        ]);
        $r1->assertSessionHasNoErrors();
        $this->assertDatabaseHas('binary_nodes', [
            'member_name' => 'Connector Test Member 1',
            'parent_id' => $this->root->id,
            'branch' => 'RIGHT',
            'slot_number' => 1,
        ]);

        // 3. Place another member under SBL-CONN1 using connector_code
        $r2 = $this->actingAs($this->admin)->post(route('binary.store'), [
            'connector_code' => 'SBL-CONN1',
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'Child Under Connector',
            'member_code' => 'SBL-CONN-CHILD',
            'package_name' => 'National 120k',
        ]);
        $r2->assertSessionHasNoErrors();
        $parentConn = BinaryNode::where('member_code', 'SBL-CONN1')->firstOrFail();
        $this->assertDatabaseHas('binary_nodes', [
            'member_name' => 'Child Under Connector',
            'parent_id' => $parentConn->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
        ]);
    }

    public function test_user_can_customize_root_member_name_and_sync_with_user(): void
    {
        $response = $this->actingAs($this->admin)->put(route('team.update', $this->root), [
            'member_name' => 'Customized Super Admin Name',
            'member_code' => $this->root->member_code,
            'phone' => '01777656517',
            'package_name' => 'National 120k',
        ]);

        $response->assertSessionHasNoErrors();
        $this->root->refresh();
        $this->admin->refresh();

        $this->assertEquals('Customized Super Admin Name', $this->root->member_name);
        $this->assertEquals('Customized Super Admin Name', $this->admin->name);
    }

    public function test_user_profile_update_syncs_root_node_name(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('profile.update'), [
            'name' => 'Profile Updated Admin Name',
            'email' => $this->admin->email,
            'phone' => '01777656517',
        ]);

        $response->assertSessionHasNoErrors();
        $this->root->refresh();
        $this->admin->refresh();

        $this->assertEquals('Profile Updated Admin Name', $this->admin->name);
        $this->assertEquals('Profile Updated Admin Name', $this->root->member_name);
    }

    public function test_different_users_have_isolated_team_roots(): void
    {
        $user2 = User::factory()->create([
            'name' => 'Nusrat Jahan',
            'phone' => '01911223344',
            'email' => 'nusrat@sbl.test',
        ]);

        $this->actingAs($user2)->get(route('team.index'));

        $user2Root = BinaryNode::where('tree_owner_id', $user2->id)->whereNull('parent_id')->first();
        $this->assertNotNull($user2Root);
        $this->assertEquals('Nusrat Jahan', $user2Root->member_name);
        $this->assertEquals($user2->id, $user2Root->tree_owner_id);
        $this->assertNotEquals($this->root->id, $user2Root->id);
    }

    public function test_admin_can_place_member_with_multiple_packages_and_quantities(): void
    {
        $selectedPackages = [
            [
                'key' => 'starter',
                'name' => 'Starter Package (10K)',
                'price' => 10000,
                'bv' => 10,
                'qty' => 2,
            ],
            [
                'key' => 'national',
                'name' => 'National Package (120K)',
                'price' => 120000,
                'bv' => 100,
                'qty' => 1,
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('binary.store'), [
            'parent_id' => $this->root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'Multi Pack Member',
            'phone' => '01711223344',
            'email' => 'multipack@sbl.test',
            'selected_packages' => json_encode($selectedPackages),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $node = BinaryNode::where('member_name', 'Multi Pack Member')->first();
        $this->assertNotNull($node);
        $this->assertEquals(120.00, (float)$node->point_value);
        $this->assertStringContainsString('Starter Package (10K) x 2', $node->package_name);
        $this->assertStringContainsString('National Package (120K) x 1', $node->package_name);

        // Verify Investment records
        $investments = \App\Models\Investment::where('binary_node_id', $node->id)->get();
        $this->assertCount(2, $investments);

        $starterInv = $investments->firstWhere('plan_name', 'Starter Package (10K)');
        $this->assertNotNull($starterInv);
        $this->assertEquals(20000.00, (float)$starterInv->amount);
        $this->assertEquals(20.00, (float)$starterInv->point_value);

        $nationalInv = $investments->firstWhere('plan_name', 'National Package (120K)');
        $this->assertNotNull($nationalInv);
        $this->assertEquals(120000.00, (float)$nationalInv->amount);
        $this->assertEquals(100.00, (float)$nationalInv->point_value);

        // Verify upline left BV sync
        $this->root->refresh();
        $this->assertEquals(120.00, (float)$this->root->left_bv);
    }

    public function test_admin_can_place_member_with_others_custom_package(): void
    {
        $selectedPackages = [
            [
                'key' => 'others',
                'name' => 'Special Franchise Pack',
                'price' => 45000,
                'bv' => 35,
                'qty' => 2,
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('binary.store'), [
            'parent_id' => $this->root->id,
            'branch' => 'RIGHT',
            'slot_number' => 1,
            'member_name' => 'Custom Pack Member',
            'phone' => '01888990011',
            'email' => 'custompack@sbl.test',
            'selected_packages' => json_encode($selectedPackages),
        ]);

        $response->assertSessionHasNoErrors();

        $node = BinaryNode::where('member_name', 'Custom Pack Member')->first();
        $this->assertNotNull($node);
        $this->assertEquals(70.00, (float)$node->point_value);
        $this->assertEquals('Special Franchise Pack x 2', $node->package_name);

        $investments = \App\Models\Investment::where('binary_node_id', $node->id)->get();
        $this->assertCount(1, $investments);
        $this->assertEquals(90000.00, (float)$investments->first()->amount);
        $this->assertEquals(70.00, (float)$investments->first()->point_value);

        $this->root->refresh();
        $this->assertEquals(70.00, (float)$this->root->right_bv);
    }
}
