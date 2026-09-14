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
        $admin = User::where('phone', '01777656517')->first() ?? User::first();
        $adminId = $admin ? $admin->id : 1;

        // Ensure admin root node exists
        if (! BinaryNode::where('tree_owner_id', $adminId)->whereNull('parent_id')->exists()) {
            BinaryNode::create([
                'tree_owner_id' => $adminId,
                'user_id' => $admin?->id,
                'member_name' => $admin?->name ?? 'Md. Abdul Hai',
                'member_code' => 'mdabdulhaijitu1',
                'phone' => $admin?->phone ?? '01777656517',
                'email' => $admin?->email ?? 'mdabdulhaijitu@gmail.com',
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
        }

        // Ensure all other users have their own separate team root using BinaryTreeService
        $treeService = app(BinaryTreeService::class);
        foreach (User::where('id', '!=', $adminId)->get() as $user) {
            $treeService->ensureUserRoot($user);
        }
    }
}
