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
        if (BinaryNode::count() > 0) {
            return;
        }

        $service = new BinaryTreeService();
        $admin = User::first();

        // 1. Root Node (SBL Head / Admin)
        $root = BinaryNode::create([
            'user_id' => $admin?->id,
            'member_name' => 'SBL Founder & Head',
            'member_code' => 'SBL-1001',
            'phone' => '01700000000',
            'email' => 'founder@sbl.com.bd',
            'package_name' => 'Crown VIP',
            'point_value' => 500.00,
            'rank_name' => 'Crown Director',
            'joined_at' => now()->subMonths(6),
        ]);

        // 2. Level 2 - Left Node (Dhaka Division Hub)
        $left1 = $service->placeMember([
            'member_name' => 'Rafiqul Islam (Dhaka Hub)',
            'member_code' => 'SBL-1002',
            'phone' => '01711001122',
            'email' => 'rafiq@sbl.test',
            'parent_id' => $root->id,
            'sponsor_id' => $root->id,
            'position' => 'left',
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'rank_name' => 'Diamond Leader',
        ]);

        // 3. Level 2 - Right Node (Chittagong Division Hub)
        $right1 = $service->placeMember([
            'member_name' => 'Kamal Hossain (CTG Hub)',
            'member_code' => 'SBL-1003',
            'phone' => '01811002233',
            'email' => 'kamal@sbl.test',
            'parent_id' => $root->id,
            'sponsor_id' => $root->id,
            'position' => 'right',
            'package_name' => 'International 550k',
            'point_value' => 500.00,
            'rank_name' => 'Platinum Leader',
        ]);

        // 4. Level 3 - Left's Left (Mirpur Dropshipper)
        $service->placeMember([
            'member_name' => 'Tanvir Ahmed (Mirpur)',
            'member_code' => 'SBL-1004',
            'phone' => '01611003344',
            'email' => 'tanvir@sbl.test',
            'parent_id' => $left1->id,
            'sponsor_id' => $left1->id,
            'position' => 'left',
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'rank_name' => 'Gold Member',
        ]);

        // 5. Level 3 - Left's Right (Uttara Merchant)
        $service->placeMember([
            'member_name' => 'Farhana Akter (Uttara)',
            'member_code' => 'SBL-1005',
            'phone' => '01911004455',
            'email' => 'farhana@sbl.test',
            'parent_id' => $left1->id,
            'sponsor_id' => $left1->id,
            'position' => 'right',
            'package_name' => 'International 550k',
            'point_value' => 500.00,
            'rank_name' => 'Gold Member',
        ]);

        // 6. Level 3 - Right's Left (Agrabad Investor)
        $service->placeMember([
            'member_name' => 'Nazmul Huda (Agrabad)',
            'member_code' => 'SBL-1006',
            'phone' => '01511005566',
            'email' => 'nazmul@sbl.test',
            'parent_id' => $right1->id,
            'sponsor_id' => $right1->id,
            'position' => 'left',
            'package_name' => 'National 120k',
            'point_value' => 100.00,
            'rank_name' => 'Silver Member',
        ]);

        // Note: Right's Right ($right1->right) is left VACANT on purpose so user can visually place a member!
    }
}
