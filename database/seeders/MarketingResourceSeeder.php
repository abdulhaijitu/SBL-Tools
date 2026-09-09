<?php

namespace Database\Seeders;

use App\Models\MarketingResource;
use Illuminate\Database\Seeder;

class MarketingResourceSeeder extends Seeder
{
    public function run(): void
    {
        $resources = [
            [
                'title' => 'SBL Official Dropshipping Leaflet (High-Res Comparison)',
                'category' => 'Official Leaflets',
                'file_type' => 'image',
                'file_url' => '/images/sbl-packages-sheet.png',
                'file_size' => '1.8 MB',
                'badge' => 'Official Leaflet',
                'description' => 'Official ShopLogist Bangladesh Limited Dropshipping comparison sheet covering National & International packages, capital breakdown, guarantee terms, and QR validation.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'SBL Ecosystem Business Presentation Slide Deck',
                'category' => 'Presentations & Slides',
                'file_type' => 'presentation',
                'file_url' => 'https://docs.google.com/presentation/d/1official-sbl-presentation/preview',
                'file_size' => '8.4 MB',
                'badge' => 'Keynote Deck',
                'description' => 'Complete corporate presentation slides for client counseling, investor meetings, and new affiliate orientation with 10-generation growth model.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => '10 Leadership Ranks & Performance Incentive Matrix',
                'category' => 'Policies & Guides',
                'file_type' => 'pdf',
                'file_url' => '/ranks',
                'file_size' => '950 KB',
                'badge' => 'Incentive Guide',
                'description' => 'Detailed criteria, BV thresholds, and monetary incentives (up to 40 Lakh BDT) for all 10 corporate leadership ranks from SBL Starter to Crown Ambassador.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'SBL Corporate Trade License & Company Registration Cert',
                'category' => 'Policies & Guides',
                'file_type' => 'pdf',
                'file_url' => 'https://shoplogistbd.com/legal/trade-license.pdf',
                'file_size' => '1.2 MB',
                'badge' => 'Government Verified',
                'description' => 'Authentic verified government certification, RJSC incorporation certificate, and e-commerce compliance documents for investor trust.',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'SBL Official Brand Assets, Logos & Promotional Kit',
                'category' => 'Marketing Media',
                'file_type' => 'image',
                'file_url' => '/images/sbl-packages-qr.png',
                'file_size' => '3.5 MB',
                'badge' => 'Vector Pack',
                'description' => 'High-resolution logo vectors, social media banner templates, QR codes, and color schemes for field marketing executives.',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($resources as $res) {
            MarketingResource::updateOrCreate(
                ['title' => $res['title']],
                $res
            );
        }
    }
}
