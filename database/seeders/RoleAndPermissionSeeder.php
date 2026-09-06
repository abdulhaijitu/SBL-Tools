<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define all Permissions grouped by module
        $permissions = [
            // Leads CRM
            ['name' => 'View Leads', 'slug' => 'leads.view', 'module' => 'Leads'],
            ['name' => 'Create Leads', 'slug' => 'leads.create', 'module' => 'Leads'],
            ['name' => 'Edit Leads', 'slug' => 'leads.edit', 'module' => 'Leads'],
            ['name' => 'Delete Leads', 'slug' => 'leads.delete', 'module' => 'Leads'],
            ['name' => 'Assign Leads', 'slug' => 'leads.assign', 'module' => 'Leads'],
            ['name' => 'Convert Leads', 'slug' => 'leads.convert', 'module' => 'Leads'],

            // Tasks & Activities
            ['name' => 'View Tasks', 'slug' => 'tasks.view', 'module' => 'Tasks'],
            ['name' => 'Manage Tasks', 'slug' => 'tasks.manage', 'module' => 'Tasks'],
            ['name' => 'Delete Tasks', 'slug' => 'tasks.delete', 'module' => 'Tasks'],

            // Presentations
            ['name' => 'View Presentations', 'slug' => 'presentations.view', 'module' => 'Presentations'],
            ['name' => 'Manage Presentations', 'slug' => 'presentations.manage', 'module' => 'Presentations'],

            // Marketing
            ['name' => 'View Marketing Calendar', 'slug' => 'marketing.view', 'module' => 'Marketing'],
            ['name' => 'Manage Marketing Content', 'slug' => 'marketing.manage', 'module' => 'Marketing'],

            // Reports & Analytics
            ['name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'Reports'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'module' => 'Reports'],

            // Toolkit
            ['name' => 'Access SBL Toolkit', 'slug' => 'toolkit.view', 'module' => 'Toolkit'],

            // Team & User Management
            ['name' => 'View Team Members', 'slug' => 'users.view', 'module' => 'Users'],
            ['name' => 'Manage Team Members', 'slug' => 'users.manage', 'module' => 'Users'],

            // Roles & Permissions
            ['name' => 'View Roles', 'slug' => 'roles.view', 'module' => 'Roles'],
            ['name' => 'Manage Roles & Permissions', 'slug' => 'roles.manage', 'module' => 'Roles'],
        ];

        $permissionModels = [];
        foreach ($permissions as $perm) {
            $permissionModels[$perm['slug']] = Permission::firstOrCreate(
                ['slug' => $perm['slug']],
                [
                    'name' => $perm['name'],
                    'module' => $perm['module'],
                    'description' => "Allows user to {$perm['name']}",
                ]
            );
        }

        // 2. Define System Roles
        $roles = [
            'super-admin' => [
                'name' => 'Super Admin',
                'description' => 'Unrestricted access to all tools, settings, users, and data across the system.',
                'is_system' => true,
                'permissions' => array_keys($permissionModels), // ALL permissions
            ],
            'sales-manager' => [
                'name' => 'Sales Manager',
                'description' => 'Supervises lead pipelines, assigns leads, delegates tasks, and views analytics.',
                'is_system' => true,
                'permissions' => [
                    'leads.view', 'leads.create', 'leads.edit', 'leads.delete', 'leads.assign', 'leads.convert',
                    'tasks.view', 'tasks.manage', 'tasks.delete',
                    'presentations.view', 'presentations.manage',
                    'marketing.view',
                    'reports.view', 'reports.export',
                    'toolkit.view',
                    'users.view',
                ],
            ],
            'sales-agent' => [
                'name' => 'Sales Executive',
                'description' => 'Manages personal lead assignments, follow-ups, presentations, and client conversion.',
                'is_system' => true,
                'permissions' => [
                    'leads.view', 'leads.create', 'leads.edit', 'leads.convert',
                    'tasks.view', 'tasks.manage',
                    'presentations.view', 'presentations.manage',
                    'toolkit.view',
                ],
            ],
            'marketing-officer' => [
                'name' => 'Marketing Specialist',
                'description' => 'Coordinates social media campaigns, manages content calendar, and evaluates lead channels.',
                'is_system' => true,
                'permissions' => [
                    'marketing.view', 'marketing.manage',
                    'reports.view',
                    'toolkit.view',
                    'leads.view',
                ],
            ],
            'viewer' => [
                'name' => 'Viewer / Auditor',
                'description' => 'Read-only visibility for reporting, monitoring, and compliance reviews.',
                'is_system' => true,
                'permissions' => [
                    'leads.view',
                    'reports.view',
                    'toolkit.view',
                ],
            ],
        ];

        $roleModels = [];
        foreach ($roles as $slug => $roleData) {
            $role = Role::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'is_system' => $roleData['is_system'],
                ]
            );

            // Sync permissions for role
            $permIds = [];
            foreach ($roleData['permissions'] as $pSlug) {
                if (isset($permissionModels[$pSlug])) {
                    $permIds[] = $permissionModels[$pSlug]->id;
                }
            }
            $role->permissions()->sync($permIds);
            $roleModels[$slug] = $role;
        }

        // 3. Attach Super Admin Role to primary admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@sbl.test'],
            [
                'name' => 'SBL Admin',
                'password' => Hash::make('password'),
                'phone' => '01700000000',
                'designation' => 'System Administrator',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $admin->update([
            'phone' => $admin->phone ?: '01700000000',
            'designation' => $admin->designation ?: 'System Administrator',
            'status' => 'active',
        ]);
        if (!$admin->roles->contains($roleModels['super-admin']->id)) {
            $admin->roles()->attach($roleModels['super-admin']->id);
        }

        // 4. Create Demo Team Members for different roles
        $team = [
            [
                'name' => 'Rahim Chowdhury',
                'email' => 'manager@sbl.test',
                'phone' => '01711223344',
                'designation' => 'Head of Sales',
                'role' => 'sales-manager',
            ],
            [
                'name' => 'Karim Hasan',
                'email' => 'agent@sbl.test',
                'phone' => '01811223344',
                'designation' => 'Senior Sales Executive',
                'role' => 'sales-agent',
            ],
            [
                'name' => 'Nusrat Jahan',
                'email' => 'marketing@sbl.test',
                'phone' => '01911223344',
                'designation' => 'Growth Marketing Lead',
                'role' => 'marketing-officer',
            ],
        ];

        foreach ($team as $memberData) {
            $user = User::firstOrCreate(
                ['email' => $memberData['email']],
                [
                    'name' => $memberData['name'],
                    'password' => Hash::make('password'),
                    'phone' => $memberData['phone'],
                    'designation' => $memberData['designation'],
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            if (isset($roleModels[$memberData['role']]) && !$user->roles->contains($roleModels[$memberData['role']]->id)) {
                $user->roles()->sync([$roleModels[$memberData['role']]->id]);
            }
        }
    }
}
