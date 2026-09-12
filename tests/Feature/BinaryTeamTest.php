<?php

namespace Tests\Feature;

use App\Models\BinaryNode;
use App\Models\User;
use Database\Seeders\BinaryTeamSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BinaryTeamTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(BinaryTeamSeeder::class);

        $this->admin = User::where('email', 'admin@sbl.test')->first();
    }

    public function test_authenticated_user_can_view_binary_tree(): void
    {
        $response = $this->actingAs($this->admin)->get(route('team.index'));

        $response->assertOk();
        $response->assertSee('Team Explorer');
        $response->assertSee($this->admin->name);
    }

    public function test_visual_tree_preserves_nested_descendants_and_their_ids(): void
    {
        $root = BinaryNode::whereNull('parent_id')->first();
        $child = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'user_id' => $this->admin->id,
            'parent_id' => $root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'Child Node',
            'member_code' => 'CHILD01',
            'phone' => '01711000001',
            'is_active' => true,
        ]);

        $tree = app(\App\Services\BinaryTreeService::class)->getVisualTree(null, $this->admin->id, 2);
        $rootData = $tree['tree'];
        $children = array_merge($rootData['left_slots'], $rootData['right_slots']);
        $nested = collect($children)->first(fn($node) => empty($node['is_vacant']) && isset($node['left_slots']));

        $this->assertNotNull($nested, 'The recursive tree must not be overwritten by flat direct slots.');
        $this->assertContains($nested['id'], $tree['all_node_ids']);
        $this->assertCount(5, $nested['left_slots']);
        $this->assertCount(5, $nested['right_slots']);
    }

    public function test_admin_can_place_new_member_in_vacant_slot(): void
    {
        $root = BinaryNode::whereNull('parent_id')->first();
        $this->assertNotNull($root);

        // Right Slot 2 under Root is vacant
        $response = $this->actingAs($this->admin)->post(route('binary.store'), [
            'member_name' => 'Belal Hossain',
            'phone' => '01799887766',
            'email' => 'belal@sbl.test',
            'parent_id' => $root->id,
            'branch' => 'RIGHT',
            'slot_number' => 2,
            'package_name' => 'International 550k',
            'rank_name' => 'Silver Member',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('binary_nodes', [
            'member_name' => 'Belal Hossain',
            'parent_id' => $root->id,
            'branch' => 'RIGHT',
            'slot_number' => 2,
            'point_value' => 500.00,
        ]);

        // Verify volume propagated to parent
        $freshRoot = $root->fresh();
        $this->assertEquals(1, $freshRoot->right_count);
        $this->assertEquals(500.00, (float)$freshRoot->right_bv);
    }

    public function test_cannot_place_member_in_already_occupied_slot(): void
    {
        $root = BinaryNode::whereNull('parent_id')->first();

        // Place a member in Left Slot-1
        BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'user_id' => $this->admin->id,
            'parent_id' => $root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'Existing Member',
            'member_code' => 'EXIST01',
            'phone' => '01711000002',
            'is_active' => true,
        ]);

        // Attempt duplicate placement in same slot
        $response = $this->actingAs($this->admin)->post(route('binary.store'), [
            'member_name' => 'Duplicate Placement',
            'parent_id' => $root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_viewing_member_tree_makes_them_temporary_root_and_breadcrumbs_work(): void
    {
        $root = BinaryNode::whereNull('parent_id')->first();
        $tahmina = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'user_id' => $this->admin->id,
            'parent_id' => $root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'Tahmina Akter',
            'member_code' => '@taminaakter',
            'phone' => '01711000003',
            'is_active' => true,
        ]);
        $zobayer = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'user_id' => $this->admin->id,
            'parent_id' => $tahmina->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'Md. Zobayer Abdullah',
            'member_code' => '@zobayerabdullah',
            'phone' => '01711000004',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('team.show', ['memberId' => $tahmina->id]));
        $response->assertOk();
        $response->assertSee('Tahmina Akter');
        $response->assertSee($root->member_name);
        $response->assertSee('Md. Zobayer Abdullah');
    }

    public function test_binary_search_redirects_to_focused_member(): void
    {
        $root = BinaryNode::whereNull('parent_id')->first();
        $target = BinaryNode::create([
            'tree_owner_id' => $this->admin->id,
            'user_id' => $this->admin->id,
            'parent_id' => $root->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'Md. Zobayer Abdullah',
            'member_code' => '@zobayerabdullah',
            'phone' => '01711000005',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('team.search', ['search' => '@zobayerabdullah']));

        $response->assertRedirect(route('team.show', ['memberId' => $target->id]));
    }

    public function test_authenticated_user_can_view_binary_builder_view_with_slots_and_leads(): void
    {
        // Seed a CRM lead
        $source = \App\Models\LeadSource::firstOrCreate(['name' => 'Direct Contact'], ['is_active' => true, 'order' => 1]);
        \App\Models\Lead::create([
            'owner_user_id' => $this->admin->id,
            'lead_source_id' => $source->id,
            'name' => 'Tanvir Ahmed',
            'mobile' => '01811223344',
            'email' => 'tanvir@sbl.test',
            'stage' => \App\Enums\LeadStage::QUALIFIED,
        ]);

        $response = $this->actingAs($this->admin)->get(route('team.index'));

        $response->assertOk();
        $response->assertSee('Team Explorer');
        $response->assertSee('Mindmap');
        $response->assertSee('Directory');
        $response->assertSee('LEFT TEAM');
        $response->assertSee('RIGHT TEAM');
        $response->assertSee('Place Member');
        $response->assertSee('Tanvir Ahmed');
        $response->assertSee('CRM Leads থেকে দ্রুত নির্বাচন করুন');
    }

    public function test_user_can_view_mindmap_canvas_mode(): void
    {
        $response = $this->actingAs($this->admin)->get(route('team.index', ['view' => 'mindmap']));

        $response->assertOk();
        $response->assertSee('mindmap-board');
        $response->assertSee('Team Explorer');
    }
}
