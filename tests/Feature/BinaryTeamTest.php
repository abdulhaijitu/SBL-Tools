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
        $response = $this->actingAs($this->admin)->get(route('binary.index'));

        $response->assertOk();
        $response->assertSee('Binary Team Engine');
        $response->assertSee('ভিজুয়াল বাইনারি টিম নেটওয়ার্ক');
        $response->assertSee('SBL Founder & Head');
        $response->assertSee('SBL-1001');
        $response->assertSee('Rafiqul Islam (Dhaka Hub)');
        $response->assertSee('Kamal Hossain (CTG Hub)');
    }

    public function test_admin_can_place_new_member_in_vacant_position(): void
    {
        $right1 = BinaryNode::where('member_code', 'SBL-1003')->first();
        $this->assertNotNull($right1);

        // Right-Right position is vacant
        $response = $this->actingAs($this->admin)->post(route('binary.store'), [
            'member_name' => 'Belal Hossain',
            'phone' => '01799887766',
            'email' => 'belal@sbl.test',
            'parent_id' => $right1->id,
            'position' => 'right',
            'package_name' => 'International 550k',
            'rank_name' => 'Silver Member',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('binary_nodes', [
            'member_name' => 'Belal Hossain',
            'parent_id' => $right1->id,
            'position' => 'right',
            'point_value' => 500.00,
        ]);

        // Verify volume propagated to parent
        $freshRight1 = $right1->fresh();
        $this->assertEquals(1, $freshRight1->right_count);
        $this->assertEquals(500.00, (float)$freshRight1->right_bv);
    }

    public function test_cannot_place_member_in_already_occupied_position(): void
    {
        $root = BinaryNode::whereNull('parent_id')->first();

        // Left of root is already occupied by SBL-1002
        $response = $this->actingAs($this->admin)->post(route('binary.store'), [
            'member_name' => 'Duplicate Placement',
            'parent_id' => $root->id,
            'position' => 'left',
            'package_name' => 'National 120k',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_binary_search_redirects_to_focused_member(): void
    {
        $target = BinaryNode::where('member_code', 'SBL-1004')->first();

        $response = $this->actingAs($this->admin)->get(route('binary.search', ['search' => 'SBL-1004']));

        $response->assertRedirect(route('binary.index', ['node_id' => $target->id]));
    }

    public function test_extreme_navigation_redirects(): void
    {
        $root = BinaryNode::whereNull('parent_id')->first();

        $response = $this->actingAs($this->admin)->get(route('binary.extreme', [
            'node' => $root->id,
            'direction' => 'left',
        ]));

        $response->assertRedirect();
    }
}
