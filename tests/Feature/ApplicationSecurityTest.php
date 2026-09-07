<?php

namespace Tests\Feature;

use App\Enums\{TaskType, TaskPriority, TaskStatus};

use App\Models\{BinaryNode, Lead, LeadSource, Role, Task, User};
use App\Services\BinaryTreeService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApplicationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_member_cannot_access_administration_or_mutate_crm(): void
    {
        $user = $this->userWithRole('member');
        $this->actingAs($user)->get('/users')->assertForbidden();
        $this->get('/roles')->assertForbidden();
        $this->post('/leads', [])->assertForbidden();
        $this->post('/contacts', [])->assertForbidden();
    }

    public function test_agent_cannot_read_or_change_another_workspace(): void
    {
        $owner = $this->userWithRole('sales-agent');
        $other = $this->userWithRole('sales-agent');
        $source = LeadSource::create(['name' => 'Referral', 'is_active' => true]);
        $lead = Lead::create(['name' => 'Private prospect', 'mobile' => '01800000001', 'owner_user_id' => $owner->id, 'lead_source_id' => $source->id, 'stage' => 'new', 'temperature' => 'warm']);
        $root = app(BinaryTreeService::class)->ensureUserRoot($owner);
        $this->actingAs($other)->get('/leads')->assertOk()->assertDontSee('Private prospect');
        $this->get('/leads/'.$lead->id)->assertNotFound();
        $this->patch('/leads/'.$lead->id.'/stage', ['stage' => 'converted'])->assertNotFound();
        $this->get('/team/'.$root->id)->assertNotFound();
        $this->get('/team/'.$root->id.'/credentials')->assertNotFound();
        $this->post('/tasks', ['title' => 'Private task', 'type' => TaskType::FOLLOW_UP->value, 'priority' => TaskPriority::HIGH->value, 'due_at' => now()->addDay()->toDateTimeString(), 'related_lead_id' => $lead->id])->assertNotFound();
    }

    public function test_credentials_are_encrypted_and_only_loaded_on_request(): void
    {
        $user = $this->userWithRole('member');
        $root = app(BinaryTreeService::class)->ensureUserRoot($user);
        $root->update(['password_plain' => 'private-test-secret', 'tpin' => '8372']);
        $raw = DB::table('binary_nodes')->where('id', $root->id)->first();
        $this->assertNotSame('private-test-secret', $raw->password_plain);
        $this->assertNotSame('8372', $raw->tpin);
        $this->assertArrayNotHasKey('password_plain', $root->toArray());
        $this->actingAs($user)->get('/team')->assertOk()->assertDontSee('private-test-secret');
        $this->getJson('/team/'.$root->id.'/credentials')->assertOk()->assertJson(['password_plain' => 'private-test-secret', 'tpin' => '8372'])->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_inactive_users_cannot_sign_in_or_use_an_existing_session(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($user)->get('/dashboard')->assertForbidden();
    }

    public function test_invalid_task_enums_return_validation_errors(): void
    {
        $this->actingAs($this->userWithRole('sales-agent'))->postJson('/tasks', ['title' => 'Invalid task', 'type' => 'bogus', 'priority' => 'bogus', 'due_at' => now()->toDateTimeString()])->assertUnprocessable()->assertJsonValidationErrors(['type', 'priority']);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_members_route_keeps_converted_filter_when_searching(): void
    {
        $user = $this->userWithRole('sales-agent');
        $source = LeadSource::create(['name' => 'Referral', 'is_active' => true]);
        foreach (['new', 'converted'] as $stage) Lead::create(['name' => 'Prospect '.$stage, 'mobile' => '01800000001', 'owner_user_id' => $user->id, 'lead_source_id' => $source->id, 'stage' => $stage, 'temperature' => 'warm']);
        $this->actingAs($user)->get('/members?search=Prospect&stage=new&view=kanban')->assertOk()->assertSee('Prospect converted')->assertDontSee('Prospect new');
    }

    public function test_delegated_manager_cannot_grant_super_admin(): void
    {
        $user = $this->userWithRole('sales-manager');
        $permission = \App\Models\Permission::where('slug', 'users.manage')->firstOrFail();
        $user->directPermissions()->attach($permission, ['type' => 'grant']);
        $this->actingAs($user)->post('/users', ['name' => 'Escalated', 'email' => 'escalated@example.test', 'password' => 'password123', 'role_id' => Role::where('slug', 'super-admin')->value('id')])->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'escalated@example.test']);
    }

    public function test_last_admin_cannot_delete_self(): void
    {
        $admin = User::where('email', 'admin@sbl.test')->firstOrFail();
        $admin->update(['password' => 'password123']);
        $this->actingAs($admin)->delete('/profile', ['password' => 'password123'])->assertSessionHasErrors('password');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_repeated_completion_creates_only_one_followup(): void
    {
        $user = $this->userWithRole('sales-agent');
        $source = LeadSource::create(['name' => 'Referral', 'is_active' => true]);
        $lead = Lead::create(['name' => 'Prospect', 'mobile' => '01800000001', 'owner_user_id' => $user->id, 'lead_source_id' => $source->id, 'stage' => 'new', 'temperature' => 'warm']);
        $task = Task::create(['title' => 'Call', 'type' => TaskType::FOLLOW_UP->value, 'priority' => TaskPriority::HIGH->value, 'status' => TaskStatus::PENDING->value, 'due_at' => now(), 'user_id' => $user->id, 'related_lead_id' => $lead->id]);
        $data = ['outcome' => 'Discussed package', 'next_action' => 'Call back', 'next_action_at' => now()->addDay()->toDateTimeString()];
        $this->actingAs($user)->post('/tasks/'.$task->id.'/complete', $data)->assertRedirect();
        $this->post('/tasks/'.$task->id.'/complete', $data)->assertRedirect();
        $this->assertDatabaseCount('tasks', 2);
    }

    public function test_report_source_counts_obey_the_selected_period(): void
    {
        $admin = User::where('email', 'admin@sbl.test')->firstOrFail();
        $source = LeadSource::create(['name' => 'Referral', 'is_active' => true]);
        foreach ([now(), now()->subMonths(2)] as $created) Lead::forceCreate(['name' => 'Prospect', 'mobile' => '01800000001', 'owner_user_id' => $admin->id, 'lead_source_id' => $source->id, 'stage' => 'converted', 'temperature' => 'warm', 'created_at' => $created]);
        $this->actingAs($admin)->get('/reports?period=today')->assertOk()->assertViewHas('totalLeads', 1)->assertViewHas('sources', fn ($sources) => $sources->first()['total_leads'] === 1);
    }

    public function test_custom_role_cannot_claim_the_reserved_admin_slug(): void
    {
        $admin = User::where('email', 'admin@sbl.test')->firstOrFail();
        $this->actingAs($admin)->post('/roles', ['name' => 'Super-Admin'])->assertRedirect();
        $this->assertDatabaseHas('roles', ['name' => 'Super-Admin', 'slug' => 'custom-super-admin']);
    }

    public function test_whatsapp_links_normalize_local_and_international_numbers(): void
    {
        $this->assertSame('8801711001122', \App\Support\PhoneNumber::whatsapp('01711 001122'));
        $this->assertSame('447700900123', \App\Support\PhoneNumber::whatsapp('+44 7700 900123'));
        $this->assertSame('447700900123', \App\Support\PhoneNumber::whatsapp('0044 7700 900123'));
    }
}
