<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
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
        $response->assertSee('Total Members');
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

    public function test_super_admin_can_view_roles_and_edit_permissions(): void
    {
        $response = $this->actingAs($this->admin)->get(route('roles.index'));
        $response->assertOk();
        $response->assertSee('Roles & Permissions');

        $editResponse = $this->actingAs($this->admin)->get(route('roles.edit', $this->agentRole));
        $editResponse->assertOk();
        $editResponse->assertSee('Module Permissions Matrix');
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
