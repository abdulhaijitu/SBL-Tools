<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class MemberRoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['member' => 'Members', 'demo-member' => 'Demo Members'] as $slug => $name) {
            Role::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => 'Access is configured through the permissions editor.',
                'is_system' => true,
            ]);
        }
    }
}
