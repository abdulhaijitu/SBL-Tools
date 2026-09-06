<?php

namespace Database\Seeders;

use App\Models\BinaryNode;
use App\Models\User;
use App\Services\BinaryTreeService;
use Illuminate\Database\Seeder;

class BinaryTeamSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate/clean existing nodes to re-seed authentic data
        BinaryNode::query()->delete();

        $service = new BinaryTreeService();
        $admin = User::first();

        // 1. Root Node - Tahmina Akter (By: Md Abdul Hai)
        $root = BinaryNode::create([
            'user_id' => $admin?->id,
            'member_name' => 'Tahmina Akter',
            'member_code' => '@taminaakter',
            'phone' => '01711000001',
            'email' => 'tahmina787162@gmail.com',
            'password_plain' => 'Tahmina@123',
            'tpin' => '5678',
            'package_name' => 'National 120k',
            'point_value' => 0.00,
            'contributions' => [],
            'rank_name' => 'Member',
            'sponsor_id' => null, // Root defaults to Md Abdul Hai
            'left_target_count' => 4,
            'right_target_count' => 1,
            'joined_at' => now()->subMonths(3),
        ]);

        // 2. Level 2 - Left Node: Lubaba Mart (By: Tahmina Akter)
        $lubaba = $service->placeMember([
            'member_name' => 'Lubaba Mart',
            'member_code' => '@tahera_akter_lubaba',
            'phone' => '01711000002',
            'email' => 'ucljitu@gmail.com',
            'password_plain' => 'Lubaba#2026',
            'tpin' => '2244',
            'parent_id' => $root->id,
            'sponsor_id' => $root->id,
            'position' => 'left',
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'left_target_count' => 2,
            'right_target_count' => 1,
            'contributions' => [
                ['amount' => 100.00, 'date' => now()->subDays(20)->toDateString(), 'note' => 'National Package 100 BV']
            ],
            'rank_name' => 'Member',
        ]);

        // 3. Level 2 - Right Node: Khaled Saifulla (By: Tahmina Akter)
        $khaled = $service->placeMember([
            'member_name' => 'Khaled Saifulla',
            'member_code' => '@khaledsaifulla',
            'phone' => '01711000003',
            'email' => 'md.khaledsaiful605211@gmail.com',
            'password_plain' => 'Khaled@99',
            'tpin' => '7890',
            'parent_id' => $root->id,
            'sponsor_id' => $root->id,
            'position' => 'right',
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'left_target_count' => 0,
            'right_target_count' => 0,
            'contributions' => [
                ['amount' => 100.00, 'date' => now()->subDays(15)->toDateString(), 'note' => 'Starter Pack 100 BV']
            ],
            'rank_name' => 'Member',
        ]);

        // 4. Level 3 - Left's Left: Md. Zobayer Abdullah (By: Md Abdul Hai)
        $zobayer = $service->placeMember([
            'member_name' => 'Md. Zobayer Abdullah',
            'member_code' => '@zobayerabdullah',
            'phone' => '01711000004',
            'email' => 'zobayerabdullah02@gmail.com',
            'password_plain' => 'Zobayer@77',
            'tpin' => '1122',
            'parent_id' => $lubaba->id,
            'sponsor_id' => null, // By Md Abdul Hai
            'position' => 'left',
            'package_name' => 'National 120k',
            'point_value' => 0.00,
            'left_target_count' => 1,
            'right_target_count' => 0,
            'contributions' => [],
            'rank_name' => 'Member',
        ]);

        // 5. Level 3 - Left's Right: Tamim Tasmim (By: Tahmina Akter)
        $service->placeMember([
            'member_name' => 'Tamim Tasmim',
            'member_code' => '@tamimtasmim',
            'phone' => '01711000005',
            'email' => 'tahmina787162@gmail.com',
            'password_plain' => 'Tamim#44',
            'tpin' => '3344',
            'parent_id' => $lubaba->id,
            'sponsor_id' => $root->id,
            'position' => 'right',
            'package_name' => 'National 120k',
            'point_value' => 0.00,
            'left_target_count' => 0,
            'right_target_count' => 0,
            'contributions' => [],
            'rank_name' => 'Member',
        ]);

        // 6. Level 4 - Left's Left's Left: Md. Ferdaous Sheikh (By: Md Abdul Hai)
        $service->placeMember([
            'member_name' => 'Md. Ferdaous Sheikh',
            'member_code' => '@ferdaoussheikh',
            'phone' => '01711000006',
            'email' => 'sheikhferdous475@gmail.com',
            'password_plain' => 'Ferdaous#55',
            'tpin' => '9988',
            'parent_id' => $zobayer->id,
            'sponsor_id' => null, // By Md Abdul Hai
            'position' => 'left',
            'package_name' => 'National 120k',
            'point_value' => 250.00,
            'left_target_count' => 0,
            'right_target_count' => 0,
            'contributions' => [
                ['amount' => 150.00, 'date' => now()->subDays(10)->toDateString(), 'note' => 'Diamond Top-up 150$'],
                ['amount' => 100.00, 'date' => now()->subDays(5)->toDateString(), 'note' => 'Package Addon 100$']
            ],
            'rank_name' => 'Member',
        ]);
    }
}
