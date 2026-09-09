<?php

namespace Database\Seeders;

use App\Models\EcosystemLink;
use Illuminate\Database\Seeder;

class EcosystemLinkSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            [
                'title' => 'SBL Official Web Portal',
                'url' => 'https://sbl.com.bd',
                'category' => 'Official Portals',
                'badge' => 'Main Website',
                'icon' => '🌐',
                'description' => 'Official corporate website, company profile, vision, and global updates.',
                'sort_order' => 1,
            ],
            [
                'title' => 'SBL Dropshipping Marketplace',
                'url' => 'https://shop.sbl.com.bd',
                'category' => 'Business & Commerce',
                'badge' => 'Dropshipping Hub',
                'icon' => '🛍️',
                'description' => 'Dropshipping and national/international reselling marketplace without holding stock.',
                'sort_order' => 2,
            ],
            [
                'title' => 'SBL Crowdfunding & ROI Portal',
                'url' => 'https://invest.sbl.com.bd',
                'category' => 'Business & Commerce',
                'badge' => 'Crowdfunding',
                'icon' => '📈',
                'description' => 'Crowdfunding packages (120,000/- & 550,000/- BDT), weekly returns, and live tracking.',
                'sort_order' => 3,
            ],
            [
                'title' => 'SBL Member Backoffice & Team',
                'url' => 'https://office.sbl.com.bd',
                'category' => 'Affiliate & Community',
                'badge' => 'Backoffice',
                'icon' => '👥',
                'description' => 'Affiliate member portal, 10-generation referral network, and commission statements.',
                'sort_order' => 4,
            ],
            [
                'title' => 'SBL Training & Counseling Academy',
                'url' => 'https://academy.sbl.com.bd',
                'category' => 'Support & Training',
                'badge' => 'Academy',
                'icon' => '🎓',
                'description' => 'Dropshipping training, Counseling-1 guidance, and video presentation resources.',
                'sort_order' => 5,
            ],
            [
                'title' => 'SBL Official Facebook Community',
                'url' => 'https://facebook.com/groups/sblecosystem',
                'category' => 'Affiliate & Community',
                'badge' => 'Facebook Group',
                'icon' => '💬',
                'description' => 'Official Facebook discussion community for SBL dropshippers, investors, and members.',
                'sort_order' => 6,
            ],
            [
                'title' => 'SBL 24/7 WhatsApp Support',
                'url' => 'https://wa.me/8801700000000',
                'category' => 'Support & Training',
                'badge' => 'Helpdesk',
                'icon' => '📱',
                'description' => 'Customer care, instant technical support, and membership assistance.',
                'sort_order' => 7,
            ],
        ];

        foreach ($links as $link) {
            EcosystemLink::updateOrCreate(
                ['title' => $link['title']],
                $link
            );
        }
    }
}
