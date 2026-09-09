<?php

namespace App\Http\Controllers;

use App\Models\CommissionType;
use App\Models\EcosystemLink;
use App\Models\InvestmentPlan;
use App\Models\MarketingResource;
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SblToolkitController extends Controller
{
    /**
     * Backward-compatible entrypoint for /toolkit
     */
    public function index(Request $request): View
    {
        $rawTab = $request->query('tab', 'packages');
        $tabAliases = [
            'compensation' => 'ranks',
            'calculator' => 'commission',
            'ecosystem' => 'links',
            'websites' => 'links',
        ];
        $tab = $tabAliases[$rawTab] ?? $rawTab;

        return match ($tab) {
            'ranks' => $this->ranks($request),
            'counseling' => $this->counseling($request),
            'commission' => $this->commission($request),
            'links' => $this->links($request),
            'resources' => $this->resources($request),
            default => $this->packages($request),
        };
    }

    /**
     * Single Page: Membership & Dropshipping Packages
     */
    public function packages(Request $request): View
    {
        $plans = InvestmentPlan::where('active', true)->get();
        $marketComparisons = $this->getMarketComparisons();
        $growthTrajectory = $this->getGrowthTrajectory();

        return view('toolkit.packages', compact(
            'plans',
            'marketComparisons',
            'growthTrajectory'
        ));
    }

    /**
     * Single Page: SBL Career Ranks & Recognition Badges
     */
    public function ranks(Request $request): View
    {
        $ranks = Rank::where('active', true)->orderBy('order')->get();
        $commissions = CommissionType::where('active', true)->get();
        $generationMatrix = $this->getGenerationMatrix();

        return view('toolkit.ranks', compact(
            'ranks',
            'commissions',
            'generationMatrix'
        ));
    }

    /**
     * Single Page: Sales Counseling Guide & Pitch Sheets
     */
    public function counseling(Request $request): View
    {
        $counselingPoints = $this->getCounselingPoints();

        return view('toolkit.counseling', compact('counselingPoints'));
    }

    /**
     * Single Page: Commission Calculator & Multi-tier Simulator
     */
    public function commission(Request $request): View
    {
        $commissions = CommissionType::where('active', true)->get();
        $defaultType = $request->query('type', 'national');
        $defaultAmount = (int) $request->query('amount', 120000);

        return view('toolkit.commission', compact(
            'commissions',
            'defaultType',
            'defaultAmount'
        ));
    }

    /**
     * Single Page: Official SBL Links & Web Directory
     */
    public function links(Request $request): View
    {
        $links = EcosystemLink::where('is_active', true)->orderBy('sort_order')->get();
        $linkCategories = EcosystemLink::where('is_active', true)->distinct()->pluck('category');

        return view('toolkit.links', compact(
            'links',
            'linkCategories'
        ));
    }

    /**
     * Single Page: Official Marketing Resources, Leaflets & Assets
     */
    public function resources(Request $request): View
    {
        $resources = MarketingResource::where('is_active', true)->orderBy('sort_order')->get();
        $resourceCategories = MarketingResource::where('is_active', true)->distinct()->pluck('category');

        return view('toolkit.resources', compact(
            'resources',
            'resourceCategories'
        ));
    }

    /**
     * Standard Dropshipping Market vs SBL Comparison Data
     */
    private function getMarketComparisons(): array
    {
        return [
            ['service' => 'Website Development, Domain & Hosting Setup', 'market' => '50,000 to 300,000 BDT'],
            ['service' => 'Product Sourcing (Curated & Verified Merchants)', 'market' => '50,000 to 100,000 BDT'],
            ['service' => 'Shopify E-Commerce Website & Maintenance', 'market' => '3,000 BDT / month'],
            ['service' => 'Professional Facebook Business Page Setup', 'market' => '5,000 to 10,000 BDT'],
            ['service' => 'Video & Creative Image Ad Production', 'market' => '2,000 to 5,000 BDT'],
            ['service' => 'Facebook Ad Campaign Management & Optimization', 'market' => '50,000 to 100,000 BDT'],
            ['service' => 'Product Packaging & Delivery Logistics Support', 'market' => '20,000 to 50,000 BDT'],
            ['service' => 'Page Moderation & Dedicated Customer Support', 'market' => '15,000 to 30,000 BDT'],
            ['service' => 'Sales Analytics, Insights & Performance Reporting', 'market' => '50,000 to 100,000 BDT'],
            ['service' => 'Automated Payment Gateway & Order Processing', 'market' => '5,000 to 10,000 BDT'],
            ['service' => 'Inventory & Stock Management', 'market' => '20,000 to 50,000 BDT'],
            ['service' => 'Online Marketing Budget (Facebook Boosting)', 'market' => '200,000 to 500,000 BDT'],
        ];
    }

    /**
     * 6-Month Scale Projections
     */
    private function getGrowthTrajectory(): array
    {
        return [
            ['month' => '1st Month', 'ad_spend' => '-', 'audience' => 'Zero Audience', 'orders' => 'Zero Sells Record'],
            ['month' => '2nd Month', 'ad_spend' => '5*30 = 150 USD', 'audience' => '10k Audience', 'orders' => '50+ Orders'],
            ['month' => '3rd Month', 'ad_spend' => '5*30 = 150 USD', 'audience' => '30k Audience', 'orders' => '100+ Orders'],
            ['month' => '4th Month', 'ad_spend' => '5*30 = 150 USD', 'audience' => '50k Audience', 'orders' => '200+ Orders'],
            ['month' => '5th Month', 'ad_spend' => '5*30 = 150 USD', 'audience' => '100k Audience', 'orders' => '300+ Orders'],
            ['month' => '6th Month', 'ad_spend' => '5*30 = 150 USD', 'audience' => '200k Audience', 'orders' => '500+ Orders'],
        ];
    }

    /**
     * Investor vs Networker Comparison
     */
    private function getCounselingPoints(): array
    {
        return [
            [
                'investor' => 'Investment Security (Protected capital & steady weekly returns)',
                'networker' => 'Multiple Center Advantage (Multiply earnings with multiple IDs)',
            ],
            [
                'investor' => 'Legal & Authentic Business Model',
                'networker' => 'Early Mover Advantage (Binary team spillover & position benefits)',
            ],
            [
                'investor' => 'Personal Business Branding & Long-term Income',
                'networker' => 'Daily Income Potential up to 50,000 BDT (Pair Reward)',
            ],
            [
                'investor' => 'Digital Asset Ownership & Valuation Growth',
                'networker' => 'Sustainable Income & Long-term Teamwork / Passive Earnings',
            ],
        ];
    }

    /**
     * 10-Generation Affiliate Matrix
     */
    private function getGenerationMatrix(): array
    {
        return [
            ['gen' => '1st', 'people' => '10', 'rate' => '10%', 'investment' => '10 × 10,000 = 1,00,000', 'commission' => '10,000'],
            ['gen' => '2nd', 'people' => '100', 'rate' => '2%', 'investment' => '100 × 10,000 = 10,00,000', 'commission' => '20,000'],
            ['gen' => '3rd', 'people' => '1,000', 'rate' => '1%', 'investment' => '1,000 × 10,000 = 1,00,00,000', 'commission' => '1,00,000'],
            ['gen' => '4th', 'people' => '10,000', 'rate' => '1%', 'investment' => '10,000 × 10,000 = 10,00,00,000', 'commission' => '10,00,000'],
            ['gen' => '5th', 'people' => '1,00,000', 'rate' => '0.5%', 'investment' => '1,00,000 × 10,000 = 1,00,00,00,000', 'commission' => '50,00,000'],
            ['gen' => '6th', 'people' => '10,00,000', 'rate' => '0.1%', 'investment' => '10,00,000 × 10,000 = 10,00,00,00,000', 'commission' => '1,00,00,000'],
            ['gen' => '7th', 'people' => '1,00,00,000', 'rate' => '0.1%', 'investment' => '1,00,00,000 × 10,000 = 1,00,00,00,000', 'commission' => '10,00,00,000'],
            ['gen' => '8th', 'people' => '10,00,00,000', 'rate' => '0.1%', 'investment' => '10,00,00,000 × 10,000 = 10,00,00,00,000', 'commission' => '1,00,00,00,000'],
            ['gen' => '9th', 'people' => '1,00,00,00,000', 'rate' => '0.1%', 'investment' => '1,00,00,00,000 × 10,000 = 1,00,00,00,00,000', 'commission' => '10,00,00,00,000'],
            ['gen' => '10th', 'people' => '10,00,00,00,000', 'rate' => '0.1%', 'investment' => '10,00,00,00,000 × 10,000 = 10,00,00,00,00,000', 'commission' => '100,00,00,00,000'],
        ];
    }
}
