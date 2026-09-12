<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed Roles, Permissions, and Team Structure
        $this->call([
            RoleAndPermissionSeeder::class,
            MemberRoleSeeder::class,
            EcosystemLinkSeeder::class,
            SblContactSeeder::class,
            MarketingResourceSeeder::class,
            SblPdfDataSeeder::class,
            BinaryTeamSeeder::class,
        ]);

        // 1. Create Admin User
        User::firstOrCreate(
            ['email' => 'admin@sbl.test'],
            [
                'name' => 'SBL Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Default Lead Sources
        $sources = [
            'Facebook Profile',
            'Facebook Page',
            'Reel',
            'Story',
            'Messenger',
            'WhatsApp',
            'Referral',
            'Offline Meeting',
            'Event',
            'Existing Client',
            'Personal Network',
            'Other',
        ];

        foreach ($sources as $index => $sourceName) {
            LeadSource::firstOrCreate(
                ['name' => $sourceName],
                ['order' => $index + 1, 'is_active' => true]
            );
        }

        // 3. Campaigns
        Campaign::firstOrCreate(
            ['name' => 'Q3 E-commerce & Dropshipping Growth'],
            [
                'platform' => 'Facebook & Reels',
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->endOfMonth()->toDateString(),
                'budget' => 25000.00,
                'status' => 'Active',
                'notes' => 'Targeting young entrepreneurs and aspiring dropshippers.',
            ]
        );
    }
}
