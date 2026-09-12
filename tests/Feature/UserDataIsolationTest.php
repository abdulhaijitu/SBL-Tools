<?php

namespace Tests\Feature;

use App\Enums\LeadStage;
use App\Enums\LeadTemperature;
use App\Enums\PresentationOutcome;
use App\Enums\PresentationType;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Models\Lead;
use App\Models\Presentation;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDataIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected \App\Models\LeadSource $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->source = \App\Models\LeadSource::create([
            'name' => 'Direct Referral',
            'slug' => 'direct-referral',
            'is_active' => true,
        ]);
    }

    public function test_user_cannot_see_another_users_lead_in_leads_list(): void
    {
        $memberRole = Role::where('slug', 'member')->first();

        $userA = User::factory()->create();
        $userA->roles()->attach($memberRole);

        $userB = User::factory()->create();
        $userB->roles()->attach($memberRole);

        $leadA = Lead::create([
            'name' => 'User A Secret Lead',
            'mobile' => '01711111111',
            'stage' => LeadStage::NEW,
            'temperature' => LeadTemperature::WARM,
            'lead_source_id' => $this->source->id,
            'interest_types' => ['Business'],
            'owner_user_id' => $userA->id,
        ]);

        $leadB = Lead::create([
            'name' => 'User B Secret Lead',
            'mobile' => '01722222222',
            'stage' => LeadStage::NEW,
            'temperature' => LeadTemperature::WARM,
            'lead_source_id' => $this->source->id,
            'interest_types' => ['Business'],
            'owner_user_id' => $userB->id,
        ]);

        // User A visits leads index
        $responseA = $this->actingAs($userA)->get(route('leads.index'));
        $responseA->assertOk();
        $responseA->assertSee('User A Secret Lead');
        $responseA->assertDontSee('User B Secret Lead');

        // User B visits leads index
        $responseB = $this->actingAs($userB)->get(route('leads.index'));
        $responseB->assertOk();
        $responseB->assertSee('User B Secret Lead');
        $responseB->assertDontSee('User A Secret Lead');
    }

    public function test_user_cannot_view_or_edit_another_users_lead(): void
    {
        $memberRole = Role::where('slug', 'member')->first();

        $userA = User::factory()->create();
        $userA->roles()->attach($memberRole);

        $userB = User::factory()->create();
        $userB->roles()->attach($memberRole);

        $leadA = Lead::create([
            'name' => 'User A Private Lead',
            'mobile' => '01733333333',
            'stage' => LeadStage::NEW,
            'temperature' => LeadTemperature::HOT,
            'lead_source_id' => $this->source->id,
            'interest_types' => ['Product'],
            'owner_user_id' => $userA->id,
        ]);

        // User B tries to view User A's lead -> 404 or 403 (inaccessible)
        $resShow = $this->actingAs($userB)->get(route('leads.show', $leadA));
        $this->assertTrue(in_array($resShow->status(), [403, 404]));

        // User B tries to edit User A's lead -> 404 or 403
        $resEdit = $this->actingAs($userB)->get(route('leads.edit', $leadA));
        $this->assertTrue(in_array($resEdit->status(), [403, 404]));

        // User B tries to update User A's lead -> 404 or 403
        $resUpdate = $this->actingAs($userB)->put(route('leads.update', $leadA), [
            'name' => 'Hacked Name',
            'mobile' => '01733333333',
        ]);
        $this->assertTrue(in_array($resUpdate->status(), [403, 404]));

        // User B tries to delete User A's lead -> 404 or 403
        $resDelete = $this->actingAs($userB)->delete(route('leads.destroy', $leadA));
        $this->assertTrue(in_array($resDelete->status(), [403, 404]));

        $this->assertDatabaseHas('leads', ['id' => $leadA->id, 'name' => 'User A Private Lead']);
    }

    public function test_super_admin_can_access_all_leads(): void
    {
        $superAdmin = User::where('email', 'admin@sbl.test')->first() ?? User::factory()->create();
        $adminRole = Role::where('slug', 'super-admin')->first();
        if (! $superAdmin->roles->contains($adminRole->id)) {
            $superAdmin->roles()->attach($adminRole);
        }

        $memberRole = Role::where('slug', 'member')->first();
        $userA = User::factory()->create();
        $userA->roles()->attach($memberRole);

        $lead = Lead::create([
            'name' => 'Member Lead Visible To Admin',
            'mobile' => '01744444444',
            'stage' => LeadStage::NEW,
            'temperature' => LeadTemperature::WARM,
            'lead_source_id' => $this->source->id,
            'interest_types' => ['Business'],
            'owner_user_id' => $userA->id,
        ]);

        $this->actingAs($superAdmin)->get(route('leads.index'))->assertOk()->assertSee('Member Lead Visible To Admin');
        $this->actingAs($superAdmin)->get(route('leads.show', $lead))->assertOk();
    }

    public function test_tasks_are_isolated_between_users(): void
    {
        $memberRole = Role::where('slug', 'member')->first();

        $userA = User::factory()->create();
        $userA->roles()->attach($memberRole);

        $userB = User::factory()->create();
        $userB->roles()->attach($memberRole);

        $taskA = Task::create([
            'title' => 'Follow up on deal User A',
            'type' => TaskType::FOLLOW_UP,
            'user_id' => $userA->id,
            'priority' => TaskPriority::HIGH,
            'status' => TaskStatus::PENDING,
            'due_at' => now()->addDay(),
        ]);

        // User B cannot complete or delete User A's task
        $resComplete = $this->actingAs($userB)->post(route('tasks.complete', $taskA));
        $this->assertTrue(in_array($resComplete->status(), [403, 404]));

        $resDelete = $this->actingAs($userB)->delete(route('tasks.destroy', $taskA));
        $this->assertTrue(in_array($resDelete->status(), [403, 404]));

        // User A can complete their own task
        $this->actingAs($userA)->post(route('tasks.complete', $taskA), [
            'outcome' => 'Meeting completed successfully',
        ])->assertRedirect();
        $this->assertEquals(TaskStatus::COMPLETED, $taskA->fresh()->status);
    }

    public function test_team_explorer_is_isolated_and_starts_blank_for_each_user(): void
    {
        $memberRole = Role::where('slug', 'member')->first();

        $userA = User::factory()->create();
        $userA->roles()->attach($memberRole);

        $userB = User::factory()->create();
        $userB->roles()->attach($memberRole);

        // User A visits Team Explorer
        $responseA = $this->actingAs($userA)->get(route('team.index'));
        $responseA->assertOk();

        // User A's root was created and has 0 children
        $rootA = \App\Models\BinaryNode::where('tree_owner_id', $userA->id)->whereNull('parent_id')->first();
        $this->assertNotNull($rootA);
        $this->assertEquals(0, $rootA->children()->count());

        // User B visits Team Explorer
        $responseB = $this->actingAs($userB)->get(route('team.index'));
        $responseB->assertOk();

        // User B's root is distinct from User A's
        $rootB = \App\Models\BinaryNode::where('tree_owner_id', $userB->id)->whereNull('parent_id')->first();
        $this->assertNotNull($rootB);
        $this->assertNotEquals($rootA->id, $rootB->id);
        $this->assertEquals(0, $rootB->children()->count());
    }
}
