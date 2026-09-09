<?php

namespace Database\Seeders;

use App\Models\CommissionType;
use App\Models\InvestmentPlan;
use App\Models\Rank;
use Illuminate\Database\Seeder;

class SblPdfDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Investment Plans from PDF Page 1 & 3
        InvestmentPlan::updateOrCreate(
            ['name' => 'National Package'],
            [
                'min_amount' => 100000.00,
                'max_amount' => 490000.00,
                'website_fee' => 20000.00,
                'weekly_return_percent' => 1.75,
                'duration_weeks' => 100,
                'crowdfunding_limit' => 1000000.00, // 10 Lac
                'lifetime_profit_sharing' => '5,000 to 20,000 BDT / month',
                'features' => [
                    'Branded Shopify Store and Product',
                    'Own Packaging',
                    'Paid Campaign Setup',
                    'Crowdfunding Opportunity up to 10 Lac',
                    'Weekly 1.75% for 100 weeks (24 Months)',
                    'Example: 1,20,000 Tk -> 1,750 Tk/week = Total 1,75,000 Tk',
                ],
                'active' => true,
            ]
        );

        InvestmentPlan::updateOrCreate(
            ['name' => 'International Package'],
            [
                'min_amount' => 500000.00,
                'max_amount' => null, // Unlimited
                'website_fee' => 50000.00,
                'weekly_return_percent' => 2.00,
                'duration_weeks' => 100,
                'crowdfunding_limit' => 5000000.00, // 50 Lac
                'lifetime_profit_sharing' => '25,000 to 100,000 BDT / month',
                'features' => [
                    'Dedicated Team for Project Management',
                    'Unlimited UGC Content',
                    'Crowdfunding Opportunity up to 50 Lac',
                    'Weekly 2% for 100 weeks (24 Months)',
                    'Example: 5,50,000 Tk -> 10,000 Tk/week = Total 10,00,000 Tk',
                ],
                'active' => true,
            ]
        );

        // 2. Ranks from PDF Page 2
        $ranks = [
            [
                'order' => 1,
                'code' => 'FME',
                'name' => 'Field Marketing Executive',
                'requirement_text' => 'Direct Reference 10',
                'incentive_amount' => 5000.00,
            ],
            [
                'order' => 2,
                'code' => 'SME',
                'name' => 'Senior Marketing Executive',
                'requirement_text' => '300 Pair Reward',
                'incentive_amount' => 50000.00,
            ],
            [
                'order' => 3,
                'code' => 'PME',
                'name' => 'Promotional Marketing Executive',
                'requirement_text' => 'Team: SME (Left: 13, Right: 7)',
                'incentive_amount' => 100000.00,
            ],
            [
                'order' => 4,
                'code' => 'BME',
                'name' => 'Brand Marketing Executive',
                'requirement_text' => 'Team: PME (Left: 10, Right: 5)',
                'incentive_amount' => 500000.00,
            ],
            [
                'order' => 5,
                'code' => 'GME',
                'name' => 'Global Marketing Executive',
                'requirement_text' => 'Team: BME (Left: 8, Right: 4)',
                'incentive_amount' => 1000000.00,
            ],
            [
                'order' => 6,
                'code' => 'ETD',
                'name' => 'Executive Team Director',
                'requirement_text' => 'Team: GME (Left: 7, Right: 3)',
                'incentive_amount' => 2000000.00,
            ],
        ];

        foreach ($ranks as $rankData) {
            Rank::updateOrCreate(
                ['code' => $rankData['code']],
                $rankData
            );
        }

        // 3. Commission Streams from PDF Page 2
        $commissions = [
            [
                'name' => 'Spot Commission',
                'code' => 'spot',
                'rate_description' => '10% on Investment',
                'description' => '10% marketing commission on the invested capital of any project developed under your referral in SBL.',
            ],
            [
                'name' => 'Refer Return',
                'code' => 'refer_return',
                'rate_description' => '0.25% per week for 100 weeks',
                'description' => '0.25% weekly return on the referred project investment amount, paid weekly for up to 100 weeks.',
            ],
            [
                'name' => 'Pair Reward',
                'code' => 'pair_reward',
                'rate_description' => '500 Tk/Pair (Daily max 100 PR = 50,000 Tk)',
                'description' => 'Earn 500 BDT per binary pair up to a maximum of 100 pairs daily by developing your referral and sales team.',
            ],
            [
                'name' => 'Unity Development Commission (UDR)',
                'code' => 'udr',
                'rate_description' => '5% across sales team',
                'description' => 'Earn up to an additional 5% commission across your sales team development.',
            ],
            [
                'name' => 'Rank Reward',
                'code' => 'rank_reward',
                'rate_description' => 'Up to 40,00,000 Tk Cash Incentive',
                'description' => 'Earn cash incentives and rank achievement bonuses up to 4,000,000 BDT through team progression.',
            ],
        ];

        foreach ($commissions as $commData) {
            CommissionType::updateOrCreate(
                ['code' => $commData['code']],
                $commData
            );
        }
    }
}

