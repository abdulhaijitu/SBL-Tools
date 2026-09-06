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
                'description' => 'অফিসিয়াল কর্পোরেট ওয়েবসাইট, কোম্পানি পরিচিতি, ভিশন ও গ্লোবাল আপডেট।',
                'sort_order' => 1,
            ],
            [
                'title' => 'SBL Dropshipping Marketplace',
                'url' => 'https://shop.sbl.com.bd',
                'category' => 'Business & Commerce',
                'badge' => 'Dropshipping Hub',
                'icon' => '🛍️',
                'description' => 'পণ্য স্টক না রেখেই ড্রপশিপিং ও ন্যাশনাল/ইন্টারন্যাশনাল রিসেলিং মার্কেটপ্লেস।',
                'sort_order' => 2,
            ],
            [
                'title' => 'SBL Crowdfunding & ROI Portal',
                'url' => 'https://invest.sbl.com.bd',
                'category' => 'Business & Commerce',
                'badge' => 'Crowdfunding',
                'icon' => '📈',
                'description' => '১২০,০০০/- ও ৫৫০,০০০/- টাকার ক্রাউডফান্ডিং প্যাকেজ, সাপ্তাহিক রিটার্ন ও লাইভ ট্র্যাকিং।',
                'sort_order' => 3,
            ],
            [
                'title' => 'SBL Member Backoffice & Team',
                'url' => 'https://office.sbl.com.bd',
                'category' => 'Affiliate & Community',
                'badge' => 'Backoffice',
                'icon' => '👥',
                'description' => 'অ্যাফিলিয়েট মেম্বার পোর্টাল, ১০ জেনারেশন রেফারেল নেটওয়ার্ক ও কমিশন স্টেটমেন্ট।',
                'sort_order' => 4,
            ],
            [
                'title' => 'SBL Training & Counseling Academy',
                'url' => 'https://academy.sbl.com.bd',
                'category' => 'Support & Training',
                'badge' => 'Academy',
                'icon' => '🎓',
                'description' => 'ড্রপশিপিং ট্রেনিং, কাউন্সেলিং-১ গাইড এবং প্রেজেন্টেশন ভিডিও রিসোর্স।',
                'sort_order' => 5,
            ],
            [
                'title' => 'SBL Official Facebook Community',
                'url' => 'https://facebook.com/groups/sblecosystem',
                'category' => 'Affiliate & Community',
                'badge' => 'Facebook Group',
                'icon' => '💬',
                'description' => 'এসবিএল ড্রপশিপার, ইনভেস্টর ও মেম্বারদের অফিসিয়াল ফেসবুক ডিসকাশন কমিউনিটি।',
                'sort_order' => 6,
            ],
            [
                'title' => 'SBL 24/7 WhatsApp Support',
                'url' => 'https://wa.me/8801700000000',
                'category' => 'Support & Training',
                'badge' => 'Helpdesk',
                'icon' => '📱',
                'description' => 'কাস্টমার কেয়ার, ইনস্ট্যান্ট টেকনিক্যাল সাপোর্ট ও মেম্বারশিপ সার্ভিস।',
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
