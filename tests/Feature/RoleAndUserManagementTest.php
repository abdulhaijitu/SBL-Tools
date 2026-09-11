<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAndUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Role $managerRole;
    protected Role $agentRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::where('email', 'admin@sbl.test')->first();
        $this->managerRole = Role::where('slug', 'sales-manager')->first();
        $this->agentRole = Role::where('slug', 'sales-agent')->first();
    }

    public function test_super_admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('User Management');
        $response->assertSee('Total Users');
        $response->assertSee('Active Accounts');
        $response->assertSee('Staff & Team', false);
    }

    public function test_super_admin_can_create_new_team_member(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Tanvir Ahmed',
            'email' => 'tanvir@sbl.test',
            'phone' => '01799887766',
            'designation' => 'Junior Sales Executive',
            'role_id' => $this->agentRole->id,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'tanvir@sbl.test']);

        $user = User::where('email', 'tanvir@sbl.test')->first();
        $this->assertTrue($user->hasRole('sales-agent'));
        $this->assertFalse($user->hasRole('super-admin'));
    }

    public function test_phone_is_normalized_to_bangladesh_11_digit_format(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Phone Test User',
            'email' => 'phoneuser@sbl.test',
            'phone' => '+8801755112233',
            'designation' => 'Sales Intern',
            'role_id' => $this->agentRole->id,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('users.index'));
        $user = User::where('email', 'phoneuser@sbl.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals('01755112233', $user->phone);
    }

    public function test_super_admin_can_update_team_member(): void
    {
        $user = User::where('email', 'agent@sbl.test')->first();

        $response = $this->actingAs($this->admin)->put(route('users.update', $user), [
            'name' => 'Karim Hasan Promoted',
            'email' => 'agent@sbl.test',
            'phone' => '01811223344',
            'designation' => 'Assistant Sales Manager',
            'role_id' => $this->managerRole->id,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('users.index'));
        $user->refresh();
        $this->assertEquals('Karim Hasan Promoted', $user->name);
        $this->assertTrue($user->hasRole('sales-manager'));
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $this->admin));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'admin@sbl.test']);
    }

    public function test_user_with_leads_or_tasks_cannot_be_deleted_without_force_delete(): void
    {
        $user = User::where('email', 'agent@sbl.test')->first();

        $source = \App\Models\LeadSource::firstOrCreate(
            ['slug' => 'website'],
            ['name' => 'Website']
        );

        // Assign a lead to this user
        Lead::create([
            'name' => 'Test Lead Safety',
            'mobile' => '01711000000',
            'stage' => 'new',
            'lead_source_id' => $source->id,
            'owner_user_id' => $user->id,
        ]);

        // Attempt delete
        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        // User should NOT be deleted
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        // Now with force_delete
        $forceResponse = $this->actingAs($this->admin)->delete(route('users.destroy', $user), [
            'force_delete' => 1,
        ]);
        $forceResponse->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_super_admin_can_view_roles_and_edit_permissions(): void
    {
        $response = $this->actingAs($this->admin)->get(route('roles.index'));
        $response->assertOk();
        $response->assertSee('Roles & Permissions');
        $response->assertSee('Control what each role can view, create, edit and manage.');

        $editResponse = $this->actingAs($this->admin)->get(route('roles.edit', $this->agentRole));
        $editResponse->assertOk();
        $editResponse->assertSee('Module Permissions Matrix');
    }

    public function test_super_admin_can_create_custom_role_with_copied_permissions(): void
    {
        $response = $this->actingAs($this->admin)->post(route('roles.store'), [
            'name' => 'Field Sales Assistant',
            'description' => 'Assists agents in field operations',
            'copy_role_id' => $this->agentRole->id,
        ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'Field Sales Assistant']);

        $newRole = Role::where('name', 'Field Sales Assistant')->first();
        $this->assertFalse((bool) $newRole->is_system);
        $this->assertEquals($this->agentRole->permissions()->count(), $newRole->permissions()->count());
        $this->assertTrue($newRole->hasPermission('leads.view'));
    }

    public function test_system_role_cannot_be_deleted(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('roles.destroy', $this->agentRole));
        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseHas('roles', ['id' => $this->agentRole->id]);
    }

    public function test_custom_role_assigned_to_user_cannot_be_deleted(): void
    {
        $customRole = Role::create([
            'name' => 'Assigned Custom Role',
            'slug' => 'assigned-custom-role',
            'description' => 'Role with users',
            'is_system' => false,
        ]);

        $user = User::where('email', 'agent@sbl.test')->first();
        $user->roles()->attach($customRole->id);

        $response = $this->actingAs($this->admin)->delete(route('roles.destroy', $customRole));
        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseHas('roles', ['id' => $customRole->id]);

        // Detach user, now delete should succeed
        $user->roles()->detach($customRole->id);
        $deleteResponse = $this->actingAs($this->admin)->delete(route('roles.destroy', $customRole));
        $deleteResponse->assertRedirect(route('roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $customRole->id]);
    }

    public function test_super_admin_role_retains_full_permissions_when_updated(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $leadViewPerm = Permission::where('slug', 'leads.view')->first();

        // Attempt to strip all permissions except leads.view
        $response = $this->actingAs($this->admin)->put(route('roles.update', $superAdminRole), [
            'name' => 'Super Administrator',
            'description' => 'Full system controller',
            'permissions' => [$leadViewPerm->id],
        ]);

        $response->assertRedirect(route('roles.index'));
        $superAdminRole->refresh();
        // Super admin protection guarantees all 20 permissions remain intact
        $this->assertEquals(Permission::count(), $superAdminRole->permissions()->count());
    }

    public function test_super_admin_can_update_role_permissions(): void
    {
        $customRole = Role::create([
            'name' => 'Test Lead Coordinator',
            'slug' => 'test-lead-coordinator',
            'description' => 'Test description',
            'is_system' => false,
        ]);

        $perm = Permission::where('slug', 'leads.view')->first();

        $response = $this->actingAs($this->admin)->put(route('roles.update', $customRole), [
            'name' => 'Test Lead Coordinator Updated',
            'description' => 'Updated description',
            'permissions' => [$perm->id],
        ]);

        $response->assertRedirect(route('roles.index'));
        $customRole->refresh();
        $this->assertTrue($customRole->hasPermission('leads.view'));
    }
}
