<?php

namespace Tests\Feature;

use App\Models\BinaryNode;
use App\Models\Lead;
use App\Models\MarketingResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_search_api(): void
    {
        $response = $this->getJson('/api/search?q=test');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_search_tools_and_navigation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/search?q=packages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'query',
                'total',
                'results',
                'categories',
            ]);

        $this->assertGreaterThan(0, $response->json('total'));
        $titles = collect($response->json('results'))->pluck('title')->toArray();
        $this->assertContains('Packages', $titles);
    }

    public function test_user_can_search_leads(): void
    {
        $user = User::factory()->create();
        $source = \App\Models\LeadSource::create(['name' => 'Web', 'is_active' => true, 'order' => 1]);
        Lead::create([
            'owner_user_id' => $user->id,
            'assigned_to' => $user->id,
            'lead_source_id' => $source->id,
            'name' => 'John Doe Special',
            'mobile' => '01711223344',
            'stage' => \App\Enums\LeadStage::NEW,
        ]);

        $response = $this->actingAs($user)->getJson('/api/search?q=Special');

        $response->assertStatus(200);
        $titles = collect($response->json('results'))->pluck('title')->toArray();
        $this->assertContains('John Doe Special', $titles);
    }

    public function test_user_can_search_team_members(): void
    {
        $user = User::factory()->create();
        BinaryNode::create([
            'tree_owner_id' => $user->id,
            'member_name' => 'Star Member Rahim',
            'member_code' => 'SBL-STAR-99',
            'phone' => '01899999999',
            'side' => 'left',
            'slot_position' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->getJson('/api/search?q=Rahim');

        $response->assertStatus(200);
        $titles = collect($response->json('results'))->pluck('title')->toArray();
        $this->assertContains('Star Member Rahim', $titles);
    }

    public function test_team_search_json_endpoint_returns_matching_members(): void
    {
        $user = User::factory()->create();
        BinaryNode::create([
            'tree_owner_id' => $user->id,
            'member_name' => 'Fast Explorer Karim',
            'member_code' => 'SBL-KARIM-01',
            'phone' => '01911111111',
            'side' => 'right',
            'slot_position' => 2,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->getJson('/team-search?search=Karim');

        $response->assertStatus(200)
            ->assertJsonCount(1);
        $this->assertEquals('Fast Explorer Karim', $response->json('0.member_name'));
        $this->assertEquals('SBL-KARIM-01', $response->json('0.member_code'));
    }

    public function test_user_cannot_see_other_users_leads_in_search(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $source = \App\Models\LeadSource::create(['name' => 'Direct', 'is_active' => true, 'order' => 2]);

        Lead::create([
            'owner_user_id' => $user2->id,
            'assigned_to' => $user2->id,
            'lead_source_id' => $source->id,
            'name' => 'Secret Customer 99',
            'mobile' => '01988776655',
            'stage' => \App\Enums\LeadStage::NEW,
        ]);

        $response = $this->actingAs($user1)->getJson('/api/search?q=Secret');

        $response->assertStatus(200);
        $titles = collect($response->json('results'))->pluck('title')->toArray();
        $this->assertNotContains('Secret Customer 99', $titles);
    }
}
