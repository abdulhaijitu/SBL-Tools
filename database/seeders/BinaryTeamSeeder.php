<?php

namespace Database\Seeders;

use App\Models\BinaryNode;
use App\Models\User;
use Illuminate\Database\Seeder;

class BinaryTeamSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed binary team if no nodes exist yet, preserving user-created tree data
        if (BinaryNode::count() > 0) {
            return;
        }

        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;

        // Root Node - Blank team initially, user manages their own team
        BinaryNode::create([
            'tree_owner_id' => $adminId,
            'user_id' => $admin?->id,
            'member_name' => $admin?->name ?? 'Md. Abdul Hai',
            'member_code' => 'mdabdulhaijitu1',
            'phone' => $admin?->phone ?? '01711000000',
            'email' => $admin?->email ?? 'abdulhaijitu@gmail.com',
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
}
