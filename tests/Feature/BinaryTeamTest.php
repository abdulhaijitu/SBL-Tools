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
        $response->assertSee('Binary Team Tree');
        $response->assertSee('Md. Abdul Hai');
        $response->assertSee('Md. Samim');
        $response->assertSee('Tahmina Akter');
        $response->assertSee('Khaled Saifulla');
        $response->assertSee('Md. Zobayer Abdullah');
    }

    public function test_admin_can_place_new_member_in_vacant_position(): void
    {
        $khaled = BinaryNode::where('member_code', '@khaledsaifulla')->first();
        $this->assertNotNull($khaled);

        // Right-Right position under Khaled is vacant
        $response = $this->actingAs($this->admin)->post(route('binary.store'), [
            'member_name' => 'Belal Hossain',
            'phone' => '01799887766',
            'email' => 'belal@sbl.test',
            'parent_id' => $khaled->id,
            'position' => 'right',
            'package_name' => 'International 550k',
            'rank_name' => 'Silver Member',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('binary_nodes', [
            'member_name' => 'Belal Hossain',
            'parent_id' => $khaled->id,
            'position' => 'right',
            'point_value' => 500.00,
        ]);

        // Verify volume propagated to parent
        $freshKhaled = $khaled->fresh();
        $this->assertEquals(1, $freshKhaled->right_count);
        $this->assertEquals(500.00, (float)$freshKhaled->right_bv);
    }

    public function test_cannot_place_member_in_already_occupied_position(): void
    {
        $root = BinaryNode::whereNull('parent_id')->first();

        // Left of root is already occupied by Tahmina Akter
        $response = $this->actingAs($this->admin)->post(route('binary.store'), [
            'member_name' => 'Duplicate Placement',
            'parent_id' => $root->id,
            'position' => 'left',
            'package_name' => 'National 120k',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_viewing_member_tree_makes_them_temporary_root_and_breadcrumbs_work(): void
    {
        $tahmina = BinaryNode::where('member_code', '@taminaakter')->first();
        $this->assertNotNull($tahmina);

        $response = $this->actingAs($this->admin)->get(route('binary.index', ['node_id' => $tahmina->id]));
        $response->assertOk();
        $response->assertSee('Tahmina Akter');
        $response->assertSee('Md. Abdul Hai'); // In breadcrumbs / main root link
    }

    public function test_binary_search_redirects_to_focused_member(): void
    {
        $target = BinaryNode::where('member_code', '@zobayerabdullah')->first();

        $response = $this->actingAs($this->admin)->get(route('binary.search', ['search' => '@zobayerabdullah']));

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
