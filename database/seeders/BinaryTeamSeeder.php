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
        // Only seed binary team if no nodes exist yet, preserving user-created tree data
        if (BinaryNode::count() > 0) {
            return;
        }

        $service = new BinaryTreeService();
        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;

        // 1. Root Node - Md. Abdul Hai (Sponsor: Md. Samim)
        $root = BinaryNode::create([
            'tree_owner_id' => $adminId,
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
            'rank_name' => 'Member',
            'sponsor_id' => null,
            'sponsor_name' => 'Md. Samim',
            'left_target_count' => 5,
            'right_target_count' => 5,
            'joined_at' => now()->subMonths(4),
        ]);

        // 2. Direct Left Slot-1 - Tahmina Akter (Sponsor: Md. Abdul Hai)
        $tahmina = $service->placeMember([
            'tree_owner_id' => $adminId,
            'member_name' => 'Tahmina Akter',
            'member_code' => '@taminaakter',
            'phone' => '01711000001',
            'email' => 'tahmina787162@gmail.com',
            'password_plain' => 'Tahmina@123',
            'tpin' => '5678',
            'parent_id' => $root->id,
            'sponsor_id' => $root->id,
            'sponsor_name' => 'Md. Abdul Hai',
            'branch' => 'LEFT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'contributions' => [
                ['amount' => 100.00, 'date' => now()->subDays(30)->toDateString(), 'note' => 'National Package 100 BV']
            ],
            'rank_name' => 'Member',
        ]);

        // 3. Direct Right Slot-1 - Khaled Saifulla (Sponsor: Md. Abdul Hai)
        $khaled = $service->placeMember([
            'tree_owner_id' => $adminId,
            'member_name' => 'Khaled Saifulla',
            'member_code' => '@khaledsaifulla',
            'phone' => '01711000003',
            'email' => 'md.khaledsaiful605211@gmail.com',
            'password_plain' => 'Khaled@99',
            'tpin' => '7890',
            'parent_id' => $root->id,
            'sponsor_id' => $root->id,
            'sponsor_name' => 'Md. Abdul Hai',
            'branch' => 'RIGHT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'contributions' => [
                ['amount' => 100.00, 'date' => now()->subDays(15)->toDateString(), 'note' => 'Starter Pack 100 BV']
            ],
            'rank_name' => 'Member',
        ]);

        // 4. Tahmina's Left Slot-1 - Md. Zobayer Abdullah (Sponsor: Tahmina Akter)
        $zobayer = $service->placeMember([
            'tree_owner_id' => $adminId,
            'member_name' => 'Md. Zobayer Abdullah',
            'member_code' => '@zobayerabdullah',
            'phone' => '01711000004',
            'email' => 'zobayerabdullah02@gmail.com',
            'password_plain' => 'Zobayer@77',
            'tpin' => '1122',
            'parent_id' => $tahmina->id,
            'sponsor_id' => $tahmina->id,
            'sponsor_name' => 'Tahmina Akter',
            'branch' => 'LEFT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'contributions' => [
                ['amount' => 100.00, 'date' => now()->subDays(12)->toDateString(), 'note' => 'National Package 100 BV']
            ],
            'rank_name' => 'Member',
        ]);

        // 5. Tahmina's Right Slot-1 - Tamim Tasmim (Sponsor: Tahmina Akter)
        $tamim = $service->placeMember([
            'tree_owner_id' => $adminId,
            'member_name' => 'Tamim Tasmim',
            'member_code' => '@tamimtasmim',
            'phone' => '01711000005',
            'email' => 'tamimtasmim02@gmail.com',
            'password_plain' => 'Tamim#44',
            'tpin' => '3344',
            'parent_id' => $tahmina->id,
            'sponsor_id' => $tahmina->id,
            'sponsor_name' => 'Tahmina Akter',
            'branch' => 'RIGHT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'contributions' => [
                ['amount' => 100.00, 'date' => now()->subDays(8)->toDateString(), 'note' => 'Starter Pack 100 BV']
            ],
            'rank_name' => 'Member',
        ]);

        // 6. Zobayer's Left Slot-1 - Md. Ferdaous Sheikh (Sponsor: Md. Zobayer Abdullah)
        $ferdaous = $service->placeMember([
            'tree_owner_id' => $adminId,
            'member_name' => 'Md. Ferdaous Sheikh',
            'member_code' => '@ferdaoussheikh',
            'phone' => '01711000006',
            'email' => 'sheikhferdous475@gmail.com',
            'password_plain' => 'Ferdaous#55',
            'tpin' => '9988',
            'parent_id' => $zobayer->id,
            'sponsor_id' => $zobayer->id,
            'sponsor_name' => 'Md. Zobayer Abdullah',
            'branch' => 'LEFT',
            'slot_number' => 1,
            'package_name' => 'National 120k',
            'point_value' => 250.00,
            'contributions' => [
                ['amount' => 150.00, 'date' => now()->subDays(10)->toDateString(), 'note' => 'Diamond Top-up 150$'],
                ['amount' => 100.00, 'date' => now()->subDays(5)->toDateString(), 'note' => 'Package Addon 100$']
            ],
            'rank_name' => 'Member',
        ]);
    }
}
