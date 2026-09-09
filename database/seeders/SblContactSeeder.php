<?php

namespace Database\Seeders;

use App\Models\SblContact;
use Illuminate\Database\Seeder;

class SblContactSeeder extends Seeder
{
    public function run(): void
    {
        $contacts = [
            [
                'department' => 'Customer Care & Support Cell',
                'contact_person' => 'Support Team',
                'phone' => '01700000000',
                'whatsapp' => '01700000000',
                'email' => 'support@sbl.com.bd',
                'available_hours' => '24/7 Support (Always Available)',
                'description' => 'Contact for general inquiries, service-related questions, and immediate assistance.',
                'icon' => '📞',
                'badge' => '24/7 Helpline',
                'is_primary' => true,
                'sort_order' => 1,
            ],
            [
                'department' => 'Dropshipping & Merchant Desk',
                'contact_person' => 'Merchant Support Officer',
                'phone' => '01800000001',
                'whatsapp' => '01800000001',
                'email' => 'dropship@sbl.com.bd',
                'available_hours' => '10:00 AM - 08:00 PM',
                'description' => 'Support for dropshipping inventory, order fulfillment, courier delivery status, and merchant onboarding.',
                'icon' => '🛍️',
                'badge' => 'Dropship Support',
                'is_primary' => false,
                'sort_order' => 2,
            ],
            [
                'department' => 'Investor & Crowdfunding Cell',
                'contact_person' => 'Investment Relations Officer',
                'phone' => '01900000002',
                'whatsapp' => '01900000002',
                'email' => 'invest@sbl.com.bd',
                'available_hours' => '10:00 AM - 08:00 PM',
                'description' => 'Information on 120,000/- & 550,000/- BDT crowdfunding packages and weekly profit-sharing plans.',
                'icon' => '📈',
                'badge' => 'Investor Hotline',
                'is_primary' => false,
                'sort_order' => 3,
            ],
            [
                'department' => 'IT & Member Portal Support',
                'contact_person' => 'System Admin',
                'phone' => '01600000003',
                'whatsapp' => '01600000003',
                'email' => 'tech@sbl.com.bd',
                'available_hours' => '10:00 AM - 10:00 PM',
                'description' => 'Assistance with portal login issues, back-office dashboard errors, and technical queries.',
                'icon' => '💻',
                'badge' => 'IT Team',
                'is_primary' => false,
                'sort_order' => 4,
            ],
            [
                'department' => 'Counseling & Leadership Training',
                'contact_person' => 'Training Coordinator',
                'phone' => '01500000004',
                'whatsapp' => '01500000004',
                'email' => 'training@sbl.com.bd',
                'available_hours' => '10:00 AM - 07:00 PM',
                'description' => 'Schedule and book Counseling-1 guidance and leadership workshop sessions.',
                'icon' => '🎓',
                'badge' => 'Training Desk',
                'is_primary' => false,
                'sort_order' => 5,
            ],
            [
                'department' => 'Accounts & Commission Payout Desk',
                'contact_person' => 'Finance Executive',
                'phone' => '01700000005',
                'whatsapp' => '01700000005',
                'email' => 'accounts@sbl.com.bd',
                'available_hours' => '11:00 AM - 06:00 PM (Sun-Thu)',
                'description' => 'Inquiries regarding wallet withdrawals, referral commission statements, and payouts.',
                'icon' => '💳',
                'badge' => 'Finance Desk',
                'is_primary' => false,
                'sort_order' => 6,
            ],
        ];

        foreach ($contacts as $contact) {
            SblContact::updateOrCreate(
                ['department' => $contact['department']],
                $contact
            );
        }
    }
}
