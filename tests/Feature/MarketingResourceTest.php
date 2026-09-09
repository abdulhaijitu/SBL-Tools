<?php

namespace Tests\Feature;

use App\Models\MarketingResource;
use App\Models\Role;
use App\Models\User;
use App\Models\BinaryNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole('sales-agent');
    }

    public function test_user_can_view_resources_tab(): void
    {
        MarketingResource::create([
            'title' => 'Official Bangla Leaflet',
            'category' => 'Leaflets & Sheets',
            'file_type' => 'pdf',
            'file_url' => 'images/sbl/sbl-office-leaflet.jpg',
            'file_size' => '2.4 MB',
            'badge' => 'OFFICIAL LEAFLET',
            'description' => 'Official leaflet for packages',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->regularUser)->get('/toolkit?tab=resources');
        $response->assertStatus(200);
        $response->assertSee('Official Bangla Leaflet');
        $response->assertSee('Leaflets & Sheets');
    }

    public function test_super_admin_can_create_update_and_delete_resource(): void
    {
        // 1. Create
        $response = $this->actingAs($this->superAdmin)->post('/marketing-resources', [
            'title' => 'Pitch Deck 2026',
            'category' => 'Presentations',
            'file_type' => 'pdf',
            'file_url' => 'https://example.com/pitch.pdf',
            'file_size' => '5 MB',
            'badge' => 'PRESENTATION',
            'description' => 'New investor presentation',
            'sort_order' => 2,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('marketing_resources', [
            'title' => 'Pitch Deck 2026',
            'category' => 'Presentations',
        ]);

        $resource = MarketingResource::where('title', 'Pitch Deck 2026')->first();

        // 2. Update
        $updateResponse = $this->actingAs($this->superAdmin)->put("/marketing-resources/{$resource->id}", [
            'title' => 'Updated Pitch Deck 2026',
            'category' => 'Presentations',
            'file_type' => 'pdf',
            'file_url' => 'https://example.com/pitch_v2.pdf',
            'file_size' => '6 MB',
            'badge' => 'REVISED',
            'description' => 'Updated deck',
            'sort_order' => 2,
        ]);

        $updateResponse->assertStatus(302);
        $this->assertDatabaseHas('marketing_resources', [
            'id' => $resource->id,
            'title' => 'Updated Pitch Deck 2026',
            'badge' => 'REVISED',
        ]);

        // 3. Delete
        $deleteResponse = $this->actingAs($this->superAdmin)->delete("/marketing-resources/{$resource->id}");
        $deleteResponse->assertStatus(302);
        $this->assertDatabaseMissing('marketing_resources', [
            'id' => $resource->id,
        ]);
    }

    public function test_regular_user_cannot_mutate_resources(): void
    {
        $resource = MarketingResource::create([
            'title' => 'Protected Legal Document',
            'category' => 'Legal & Certs',
            'file_type' => 'pdf',
            'file_url' => 'https://example.com/legal.pdf',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $storeRes = $this->actingAs($this->regularUser)->post('/marketing-resources', [
            'title' => 'Unauthorized Document',
            'category' => 'Presentations',
            'file_type' => 'pdf',
            'file_url' => 'https://example.com/unauth.pdf',
        ]);
        $storeRes->assertStatus(403);

        $updateRes = $this->actingAs($this->regularUser)->put("/marketing-resources/{$resource->id}", [
            'title' => 'Hacked Document',
            'category' => 'Legal & Certs',
            'file_type' => 'pdf',
            'file_url' => 'https://example.com/legal.pdf',
        ]);
        $updateRes->assertStatus(403);

        $deleteRes = $this->actingAs($this->regularUser)->delete("/marketing-resources/{$resource->id}");
        $deleteRes->assertStatus(403);
    }

    public function test_user_can_manage_own_team_and_cannot_modify_other_user_tree(): void
    {
        // 1. User has own root
        $myRoot = BinaryNode::create([
            'member_name' => 'My Root',
            'member_code' => 'MY001',
            'phone' => '01700000001',
            'email' => 'my_root@example.com',
            'package_name' => 'Starter 10k',
            'point_value' => 10,
            'rank_name' => 'Member',
            'is_active' => true,
            'tree_owner_id' => $this->regularUser->id,
            'user_id' => $this->regularUser->id,
        ]);

        // Place a child member
        $placeRes = $this->actingAs($this->regularUser)->post('/team/place', [
            'parent_id' => $myRoot->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'member_name' => 'My Downline',
            'member_code' => 'MY002',
            'phone' => '01700000002',
            'email' => 'my_downline@example.com',
            'package_name' => 'Starter 10k',
            'point_value' => 10,
            'rank_name' => 'Member',
            'is_active' => '1',
        ]);
        $placeRes->assertStatus(302);
        $myChild = BinaryNode::where('member_code', 'MY002')->first();
        $this->assertNotNull($myChild);
        $this->assertEquals($this->regularUser->id, $myChild->tree_owner_id);

        // Update child member
        $updateOwn = $this->actingAs($this->regularUser)->put("/team/{$myChild->id}", [
            'member_name' => 'Updated Downline',
            'member_code' => 'MY002',
            'phone' => '01700000002',
            'email' => 'my_downline@example.com',
            'package_name' => 'National 120k',
            'point_value' => 120,
            'rank_name' => 'Star',
            'is_active' => '1',
        ]);
        $updateOwn->assertStatus(302);
        $this->assertEquals('Updated Downline', $myChild->fresh()->member_name);

        // Delete child member
        $deleteOwn = $this->actingAs($this->regularUser)->delete("/team/{$myChild->id}");
        $deleteOwn->assertStatus(302);
        $this->assertDatabaseMissing('binary_nodes', ['id' => $myChild->id]);

        // 2. Isolation against other user's tree
        $otherUser = User::factory()->create();
        $otherUser->assignRole('sales-agent');

        $otherRoot = BinaryNode::create([
            'member_name' => 'Other Root',
            'member_code' => 'OTH001',
            'phone' => '01711000001',
            'email' => 'other_root@example.com',
            'package_name' => 'Starter 10k',
            'point_value' => 10,
            'rank_name' => 'Member',
            'is_active' => true,
            'tree_owner_id' => $otherUser->id,
            'user_id' => $otherUser->id,
        ]);

        $otherChild = BinaryNode::create([
            'member_name' => 'Other Child',
            'member_code' => 'OTH002',
            'phone' => '01711000002',
            'email' => 'other_child@example.com',
            'package_name' => 'Starter 10k',
            'point_value' => 10,
            'rank_name' => 'Member',
            'parent_id' => $otherRoot->id,
            'branch' => 'LEFT',
            'slot_number' => 1,
            'is_active' => true,
            'tree_owner_id' => $otherUser->id,
            'user_id' => $otherUser->id,
        ]);

        // Attempt by regularUser to delete other user's child (isolated by workspace scope -> 404)
        $deleteOther = $this->actingAs($this->regularUser)->delete("/team/{$otherChild->id}");
        $deleteOther->assertStatus(404);

        // Attempt by regularUser to update other user's child (isolated by workspace scope -> 404)
        $updateOther = $this->actingAs($this->regularUser)->put("/team/{$otherChild->id}", [
            'member_name' => 'Hijacked Name',
            'member_code' => 'OTH002',
            'phone' => '01711000002',
            'email' => 'other_child@example.com',
            'package_name' => 'Starter 10k',
            'point_value' => 10,
            'rank_name' => 'Member',
            'is_active' => '1',
        ]);
        $updateOther->assertStatus(404);
    }
}
