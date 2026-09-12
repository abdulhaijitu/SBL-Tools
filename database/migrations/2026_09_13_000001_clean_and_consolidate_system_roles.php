<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure 'super-admin' role exists and is named 'Super Admin'
        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Unrestricted access to all tools, settings, users, and data across the system.',
                'is_system' => true,
            ]
        );
        $superAdminRole->update(['name' => 'Super Admin', 'is_system' => true]);

        // 2. Ensure 'member' role exists and is named 'Member'
        $memberRole = Role::firstOrCreate(
            ['slug' => 'member'],
            [
                'name' => 'Member',
                'description' => 'Standard member access to personal leads, tasks, presentations, toolkit, and team management.',
                'is_system' => true,
            ]
        );
        $memberRole->update(['name' => 'Member', 'is_system' => true]);

        // 3. Ensure 'demo' role exists and is named 'Demo'
        // If 'demo-member' exists, update it to 'demo'
        $demoMemberRole = Role::where('slug', 'demo-member')->first();
        if ($demoMemberRole) {
            $demoMemberRole->update([
                'name' => 'Demo',
                'slug' => 'demo',
                'description' => 'Demo account access for prospective members.',
                'is_system' => true,
            ]);
            $demoRole = $demoMemberRole;
        } else {
            $demoRole = Role::firstOrCreate(
                ['slug' => 'demo'],
                [
                    'name' => 'Demo',
                    'description' => 'Demo account access for prospective members.',
                    'is_system' => true,
                ]
            );
            $demoRole->update(['name' => 'Demo', 'is_system' => true]);
        }

        // 4. Reassign users from obsolete roles to 'member' role
        $obsoleteSlugs = ['sales-manager', 'sales-agent', 'marketing-officer', 'viewer'];
        $obsoleteRoles = Role::whereIn('slug', $obsoleteSlugs)->get();

        foreach ($obsoleteRoles as $oldRole) {
            // Find users who have this role
            $userIds = DB::table('role_user')->where('role_id', $oldRole->id)->pluck('user_id');
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    // If user has no other role, assign member role
                    if (! $user->hasRole('super-admin') && ! $user->hasRole('member')) {
                        $user->assignRole($memberRole);
                    }
                }
            }
            // Detach and delete obsolete role
            DB::table('permission_role')->where('role_id', $oldRole->id)->delete();
            DB::table('role_user')->where('role_id', $oldRole->id)->delete();
            $oldRole->delete();
        }

        // 5. Clean up any dummy binary child nodes so teams start completely blank
        // Only keep primary root nodes
        DB::table('binary_nodes')->whereNotNull('parent_id')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op rollback
    }
};
