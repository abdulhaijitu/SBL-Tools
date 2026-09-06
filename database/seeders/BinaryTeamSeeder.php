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

        // 1. Root Node - Md. Abdul Hai (Sponsor: Md. Samim)
        $root = BinaryNode::create([
            'user_id' => $admin?->id,
            'member_name' => 'Md. Abdul Hai',
            'member_code' => 'mdabdulhaijitu1',
            'phone' => '01711000000',
            'email' => 'abdulhaijitu@gmail.com',
            'password_plain' => 'Admin@123',
            'tpin' => '1234',
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'contributions' => [
                ['amount' => 100.00, 'date' => now()->subMonths(4)->toDateString(), 'note' => 'Founder Package 100 BV']
            ],
            'rank_name' => 'FME',
            'sponsor_id' => null,
            'sponsor_name' => 'Md. Samim',
            'left_target_count' => 5,
            'right_target_count' => 5,
            'joined_at' => now()->subMonths(4),
        ]);

        // 2. Direct Left - Tahmina Akter (Sponsor: Md. Abdul Hai)
        $tahmina = $service->placeMember([
            'member_name' => 'Tahmina Akter',
            'member_code' => '@taminaakter',
            'phone' => '01711000001',
            'email' => 'tahmina787162@gmail.com',
            'password_plain' => 'Tahmina@123',
            'tpin' => '5678',
            'parent_id' => $root->id,
            'sponsor_id' => $root->id,
            'sponsor_name' => 'Md. Abdul Hai',
            'position' => 'left',
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'left_target_count' => 4,
            'right_target_count' => 1,
            'contributions' => [
                ['amount' => 100.00, 'date' => now()->subDays(30)->toDateString(), 'note' => 'National Package 100 BV']
            ],
            'rank_name' => 'Member',
        ]);

        // 3. Direct Right - Khaled Saifulla (Sponsor: Md. Abdul Hai)
        $khaled = $service->placeMember([
            'member_name' => 'Khaled Saifulla',
            'member_code' => '@khaledsaifulla',
            'phone' => '01711000003',
            'email' => 'md.khaledsaiful605211@gmail.com',
            'password_plain' => 'Khaled@99',
            'tpin' => '7890',
            'parent_id' => $root->id,
            'sponsor_id' => $root->id,
            'sponsor_name' => 'Md. Abdul Hai',
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

        // 4. Tahmina's Left - Md. Zobayer Abdullah (Sponsor: Tahmina Akter)
        $zobayer = $service->placeMember([
            'member_name' => 'Md. Zobayer Abdullah',
            'member_code' => '@zobayerabdullah',
            'phone' => '01711000004',
            'email' => 'zobayerabdullah02@gmail.com',
            'password_plain' => 'Zobayer@77',
            'tpin' => '1122',
            'parent_id' => $tahmina->id,
            'sponsor_id' => $tahmina->id,
            'sponsor_name' => 'Tahmina Akter',
            'position' => 'left',
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'left_target_count' => 1,
            'right_target_count' => 0,
            'contributions' => [
                ['amount' => 100.00, 'date' => now()->subDays(12)->toDateString(), 'note' => 'National Package 100 BV']
            ],
            'rank_name' => 'Member',
        ]);

        // 5. Tahmina's Right - Tamim Tasmim (Sponsor: Tahmina Akter)
        $tamim = $service->placeMember([
            'member_name' => 'Tamim Tasmim',
            'member_code' => '@tamimtasmim',
            'phone' => '01711000005',
            'email' => 'tamimtasmim02@gmail.com',
            'password_plain' => 'Tamim#44',
            'tpin' => '3344',
            'parent_id' => $tahmina->id,
            'sponsor_id' => $tahmina->id,
            'sponsor_name' => 'Tahmina Akter',
            'position' => 'right',
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'left_target_count' => 0,
            'right_target_count' => 0,
            'contributions' => [
                ['amount' => 100.00, 'date' => now()->subDays(8)->toDateString(), 'note' => 'Starter Pack 100 BV']
            ],
            'rank_name' => 'Member',
        ]);

        // 6. Zobayer's Left - Md. Ferdaous Sheikh (Sponsor: Md. Zobayer Abdullah)
        $ferdaous = $service->placeMember([
            'member_name' => 'Md. Ferdaous Sheikh',
            'member_code' => '@ferdaoussheikh',
            'phone' => '01711000006',
            'email' => 'sheikhferdous475@gmail.com',
            'password_plain' => 'Ferdaous#55',
            'tpin' => '9988',
            'parent_id' => $zobayer->id,
            'sponsor_id' => $zobayer->id,
            'sponsor_name' => 'Md. Zobayer Abdullah',
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
