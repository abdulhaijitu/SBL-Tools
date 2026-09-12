<?php

namespace App\Http\Controllers;

use App\Models\CommissionType;
use App\Models\EcosystemLink;
use App\Models\InvestmentPlan;
use App\Models\MarketingResource;
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $packageConfig = $this->getPackageConfig();
        $plans = InvestmentPlan::where('active', true)->get();
        $marketComparisons = $this->getMarketComparisons();
        $growthTrajectory = $this->getGrowthTrajectory();

        return view('toolkit.packages', compact(
            'packageConfig',
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
        $rankConfig = $this->getRankConfig();
        $marketingPlanConfig = $this->getMarketingPlanConfig();
        $generationMatrix = $this->getGenerationMatrix();
        $ranks = Rank::where('active', true)->orderBy('order')->get();
        $commissions = CommissionType::where('active', true)->get();

        return view('toolkit.ranks', compact(
            'rankConfig',
            'marketingPlanConfig',
            'generationMatrix',
            'ranks',
            'commissions'
        ));
    }

    /**
     * Single Page: Sales Counseling Guide & Pitch Sheets
     */
    public function counseling(Request $request): View
    {
        $packageConfig = $this->getPackageConfig();
        $rankConfig = $this->getRankConfig();
        $marketingPlanConfig = $this->getMarketingPlanConfig();
        $counselingConfig = $this->getCounselingConfig();

        return view('toolkit.counseling', compact(
            'packageConfig',
            'rankConfig',
            'marketingPlanConfig',
            'counselingConfig'
        ));
    }

    /**
     * Single Page: Commission Calculator & Multi-tier Simulator
     */
    public function commission(Request $request): View
    {
        $packageConfig = $this->getPackageConfig();
        $rankConfig = $this->getRankConfig();
        $marketingPlanConfig = $this->getMarketingPlanConfig();
        $generationMatrix = $this->getGenerationMatrix();
        $commissions = CommissionType::where('active', true)->get();
        $defaultType = $request->query('type', 'national');
        $defaultAmount = (int) $request->query('amount', 120000);

        return view('toolkit.commission', compact(
            'packageConfig',
            'rankConfig',
            'marketingPlanConfig',
            'generationMatrix',
            'commissions',
            'defaultType',
            'defaultAmount'
        ));
    }

    /**
     * Single Page: Official SBL Links & Web Directory
     * Single Page: Official SBL Links & Web Directory (SBL Resource Hub)
     */
    public function links(Request $request): View
    {
        $links = EcosystemLink::where('is_active', true)->orderBy('sort_order')->get();
        $linkCategories = EcosystemLink::where('is_active', true)->distinct()->pluck('category');
        $links = EcosystemLink::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
        $verifiedCount = $links->where('is_official', true)->where('verification_status', 'verified')->count();
        $featuredLinks = $links->where('is_featured', true)->where('is_official', true)->where('verification_status', 'verified');

        $marketingToolkit = [
            [
                'id' => 'tool-packages',
                'title' => 'SBL Packages & Crowdfunding',
                'url' => route('packages.index'),
                'domain' => $request->getHost(),
                'category' => 'Marketing Tools',
                'type' => 'internal',
                'badge' => 'Internal Tool',
                'description' => 'Starter, National (৳1.2L) and International (৳5.5L) crowdfunding investment packages with 70/30 capital & profit return breakdowns.',
                'icon' => '📦',
                'is_official' => true,
                'verification_status' => 'verified',
                'tags' => 'packages, roi, starter, national, international, crowdfunding',
            ],
            [
                'id' => 'tool-commission',
                'title' => 'Compensation & Commission Simulator',
                'url' => route('commission.index'),
                'domain' => $request->getHost(),
                'category' => 'Marketing Tools',
                'type' => 'internal',
                'badge' => 'Internal Tool',
                'description' => 'Live commission calculator: Direct Referral (10% spot + 0.25% weekly), Pair Rewards (1:1), and 10-Generation network payouts.',
                'icon' => '💰',
                'is_official' => true,
                'verification_status' => 'verified',
                'tags' => 'commission, binary, direct, pairs, generation, returns, calculator',
            ],
            [
                'id' => 'tool-ranks',
                'title' => 'SBL Career Ranks & Recognition',
                'url' => route('ranks.index'),
                'domain' => $request->getHost(),
                'category' => 'Marketing Tools',
                'type' => 'internal',
                'badge' => 'Internal Tool',
                'description' => 'Official 10-stage leadership ladder from Core Partner to Global Crown Ambassador with pair qualification criteria.',
                'icon' => '🏆',
                'is_official' => true,
                'verification_status' => 'verified',
                'tags' => 'ranks, career, ambassador, partner, director, leadership',
            ],
            [
                'id' => 'tool-counseling',
                'title' => 'Counseling-1: Pitch & Conversion Guide',
                'url' => route('counseling.index'),
                'domain' => $request->getHost(),
                'category' => 'Marketing Tools',
                'type' => 'internal',
                'badge' => 'Internal Tool',
                'description' => 'Step-by-step interactive prospect counseling framework: Investor vs Networker pitch, discovery questions, and objection handling.',
                'icon' => '🧭',
                'is_official' => true,
                'verification_status' => 'verified',
                'tags' => 'counseling, pitch, investor, networker, script, objections',
            ],
            [
                'id' => 'tool-presentations',
                'title' => 'Webinar & Presentation Slides',
                'url' => route('presentations.index'),
                'domain' => $request->getHost(),
                'category' => 'Marketing Tools',
                'type' => 'internal',
                'badge' => 'Internal Tool',
                'description' => 'Official presentation slides and corporate pitch decks for physical seminars, online webinars, and 1-on-1 prospect briefings.',
                'icon' => '📽️',
                'is_official' => true,
                'verification_status' => 'verified',
                'tags' => 'presentation, slides, webinar, pitch, deck, training',
            ],
            [
                'id' => 'tool-leads-create',
                'title' => 'Add New Prospect (Lead Capture)',
                'url' => route('leads.create'),
                'domain' => $request->getHost(),
                'category' => 'Marketing Tools',
                'type' => 'internal',
                'badge' => 'Internal Tool',
                'description' => 'Direct prospect registration form to capture phone, WhatsApp, interest profile, investment capacity, and schedule follow-ups.',
                'icon' => '📝',
                'is_official' => true,
                'verification_status' => 'verified',
                'tags' => 'leads, prospect, create, capture, followup, crm',
            ],
        ];

        $canManage = Auth::check() && (
            (method_exists(Auth::user(), 'isSuperAdmin') && Auth::user()->isSuperAdmin()) ||
            (method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole(['super-admin', 'sales-manager'])) ||
            (method_exists(Auth::user(), 'hasPermission') && Auth::user()->hasPermission('marketing.manage'))
        );

        return view('toolkit.links', compact(
            'links',
            'linkCategories',
            'verifiedCount',
            'featuredLinks',
            'marketingToolkit',
            'canManage'
        ));
    }

    /**
     * Single Page: Official Marketing Resources, Leaflets & Assets
     */
    public function resources(Request $request): View
    {
        $resources = MarketingResource::where('is_active', true)->orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        $featuredResources = MarketingResource::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        $counselingResources = MarketingResource::where('is_active', true)
            ->where('is_counseling_toolkit', true)
            ->orderBy('sort_order')
            ->get();

        // Standardized and dynamic categories
        $standardCategories = [
            'Official Documents',
            'Marketing Media',
            'Leaflets',
            'Presentations',
            'Policies & Guides',
            'Legal & Compliance',
            'Brand Assets',
            'Training Materials',
        ];
        $existingCategories = MarketingResource::where('is_active', true)->distinct()->pluck('category')->toArray();
        $resourceCategories = array_values(array_unique(array_merge($standardCategories, $existingCategories)));

        // Category stats counters
        $stats = [
            'total' => $resources->count(),
            'verified' => $resources->filter(fn($r) => $r->is_verified)->count(),
            'marketing' => $resources->filter(fn($r) => in_array($r->category, ['Marketing Media', 'Leaflets', 'Brand Assets']))->count(),
            'presentations' => $resources->filter(fn($r) => $r->category === 'Presentations' || $r->resource_type === 'presentation')->count(),
            'legal' => $resources->filter(fn($r) => in_array($r->category, ['Legal & Compliance', 'Policies & Guides', 'Official Documents']))->count(),
        ];

        // Curated Resource Kits for Client Counseling & Team Building
        $curatedKits = [
            [
                'id' => 'investor',
                'title' => 'Investor Counseling Kit',
                'title_bn' => 'ইনভেস্টর কাউন্সেলিং কিট',
                'icon' => '💼',
                'badge' => 'High ROI & Trust',
                'badge_bn' => 'বিনিয়োগ ও বিশ্বস্ততা',
                'description' => 'Complete presentation, dropshipping package comparison leaflet & corporate compliance for investor meetings.',
                'description_bn' => 'বিনিয়োগকারী কাউন্সেলিংয়ের জন্য পূর্ণাঙ্গ প্রেজেন্টেশন, প্যাকেজ লিফলেট এবং সরকারি রেজিস্ট্রেশন ডকুমেন্টস।',
                'resource_ids' => [1, 2, 4],
            ],
            [
                'id' => 'networker',
                'title' => 'Networker & Leadership Kit',
                'title_bn' => 'নেটওয়ার্কার ও লিডারশিপ কিট',
                'icon' => '🚀',
                'badge' => 'Career & Matrix',
                'badge_bn' => 'ক্যারিয়ার ও গ্রোথ',
                'description' => '10-generation growth model, compensation structure, rank incentives, and new affiliate induction deck.',
                'description_bn' => '১০ জেনারেশন ডুপ্লিকেশন মডেল, কমিশন প্ল্যান, র্যাংক রিওয়ার্ড এবং নতুন টিম মেম্বারদের জন্য প্রেজেন্টেশন।',
                'resource_ids' => [2, 3],
            ],
            [
                'id' => 'prospect',
                'title' => 'New Prospect Quick Kit',
                'title_bn' => 'নতুন প্রসপেক্ট কুইক কিট',
                'icon' => '🤝',
                'badge' => 'Introductory',
                'badge_bn' => 'প্রাথমিক পরিচয়',
                'description' => 'High-resolution dropshipping package leaflet, corporate overview, and quick-start presentation slides.',
                'description_bn' => 'ড্রপশিপিং প্যাকেজ তুলনামূলক লিফলেট, কোম্পানির পরিচিতি এবং প্রাথমিক উপস্থাপনা স্লাইড।',
                'resource_ids' => [1, 2],
            ],
            [
                'id' => 'training',
                'title' => 'Team Training & Pitch Kit',
                'title_bn' => 'টিম ট্রেনিং ও পিচ কিট',
                'icon' => '🎓',
                'badge' => 'Skills & Script',
                'badge_bn' => 'দক্ষতা ও প্রশিক্ষণ',
                'description' => 'Objection handling, counseling scripts, leadership BV roadmap, and visual presentation guides.',
                'description_bn' => 'ক্লায়েন্ট কাউন্সেলিং স্ক্রিপ্ট, অবজেকশন হ্যান্ডলিং, লিডারশিপ র্যাংক গাইড এবং স্লাইড ডেক।',
                'resource_ids' => [2, 3, 5],
            ],
            [
                'id' => 'branding',
                'title' => 'Official Brand & Vector Kit',
                'title_bn' => 'অফিসিয়াল ব্র্যান্ড ও ভেক্টর কিট',
                'icon' => '🎨',
                'badge' => 'Visual Identity',
                'badge_bn' => 'ব্র্যান্ড এসেট',
                'description' => 'High-res vector logos, package QR codes, social media banners, and field promotional graphics.',
                'description_bn' => 'হাই-রেজুলেশন লোগো ভেক্টর, প্যাকেজ কিউআর কোড, সোশ্যাল মিডিয়া ব্যানার ও ফিল্ড মার্কেটিং গ্রাফিক্স।',
                'resource_ids' => [1, 5],
            ],
        ];

        $canManage = Auth::check() && (
            (method_exists(Auth::user(), 'isSuperAdmin') && Auth::user()->isSuperAdmin()) ||
            (method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole(['super-admin', 'sales-manager'])) ||
            (method_exists(Auth::user(), 'hasPermission') && Auth::user()->hasPermission('marketing.manage'))
        );

        return view('toolkit.resources', compact(
            'resources',
            'featuredResources',
            'counselingResources',
            'resourceCategories',
            'stats',
            'curatedKits',
            'canManage'
        ));
    }

    /**
     * Central Single Source of Truth for SBL Packages
     */
    public function getPackageConfig(): array
    {
        return [
            'starter' => [
                'id' => 'starter',
                'key' => 'membership-10000',
                'name' => 'Starter Membership',
                'name_bn' => 'স্টার্টার মেম্বারশিপ',
                'subtitle' => 'Entry-level Affiliate & Network Marketing',
                'subtitle_bn' => 'এন্ট্রি লেভেল অ্যাফিলিয়েট ও নেটওয়ার্ক মেম্বারশিপ',
                'badge' => 'Starter • Affiliate',
                'badge_bn' => 'স্টার্টার • অ্যাফিলিয়েট',
                'price' => 10000,
                'price_formatted' => '৳10,000',
                'capital_amount' => 0,
                'capital_formatted' => '৳0',
                'setup_fee' => 10000,
                'setup_fee_formatted' => '৳10,000 Setup',
                'setup_fee_formatted_bn' => '৳১০,০০০ সেটআপ ও অ্যাক্টিভেশন',
                'bv' => 0,
                'bv_formatted' => 'Entry Level',
                'bv_formatted_bn' => 'এন্ট্রি লেভেল',
                'duration' => 'Lifetime',
                'duration_bn' => 'লাইফটাইম',
                'weekly_return_percent' => 0,
                'weekly_return_text' => 'Plan-based commissions',
                'weekly_return_text_bn' => 'প্ল্যান-ভিত্তিক কমিশন',
                'weekly_amount' => 0,
                'total_plan_return' => 0,
                'direct_commission' => '10% Spot Commission',
                'direct_commission_bn' => '১০% স্পট রেফারেল কমিশন',
                'direct_commission_amount' => '৳1,000',
                'crowdfunding_limit' => 0,
                'crowdfunding_formatted' => 'Not Applicable',
                'crowdfunding_formatted_bn' => 'প্রযোজ্য নয়',
                'profit_sharing' => 'Binary & Team Matching Bonus',
                'profit_sharing_bn' => 'বাইনারি ও টিম ম্যাচিং বোনাস',
                'benefits' => [
                    ['text' => 'Full Access to SBL Affiliate Portal & Dashboard', 'text_bn' => 'SBL অ্যাফিলিয়েট পোর্টাল ও ড্যাশবোর্ডে পূর্ণ এক্সেস'],
                    ['text' => '10% Spot Direct Sales Referral Commission', 'text_bn' => '১০% তাৎক্ষণিক স্পট ডিরেক্ট রেফারেল কমিশন'],
                    ['text' => 'Binary Matching & Career Generation Eligibility', 'text_bn' => 'বাইনারি ম্যাচিং ও ক্যারিয়ার জেনারেশন কমিশন সুবিধা'],
                    ['text' => 'Merchant Catalog & Digital Marketing Materials', 'text_bn' => 'মার্চেন্ট প্রোডাক্ট ক্যাটালগ ও মার্কেটিং রিসোর্স'],
                    ['text' => 'Official Training Community & Sales Pitch Guides', 'text_bn' => 'অফিসিয়াল ট্রেনিং সেশন ও সেলস গাইডলাইন এক্সেস'],
                    ['text' => 'Lifetime Membership with Upgrade Option', 'text_bn' => 'লাইফটাইম মেম্বারশিপ এবং যেকোনো সময় আপগ্রেড সুবিধা'],
                ],
                'eligibility' => 'Open to all aspiring digital entrepreneurs and affiliates.',
                'eligibility_bn' => 'সকল নতুন উদ্যোক্তা ও ডিজিটাল মার্কেটারদের জন্য উন্মুক্ত।',
                'disclaimer' => 'Commissions and performance bonuses are plan-based according to active sales generation. Subject to SBL terms.',
                'disclaimer_bn' => 'কমিশন ও বোনাস সেলস পারফরম্যান্সের ওপর প্ল্যান অনুযায়ী নির্ধারিত। SBL নীতিমালা প্রযোজ্য।',
                'last_verified_at' => 'September 2026',
                'status' => 'active',
                'theme' => 'slate',
            ],
            'national' => [
                'id' => 'national',
                'key' => 'national-120000',
                'name' => 'National Dropshipping',
                'name_bn' => 'ন্যাশনাল ড্রপশিপিং',
                'subtitle' => 'Turnkey Domestic Dropshipping & Shopify Store',
                'subtitle_bn' => 'দেশীয় ড্রপশিপিং ও রেডি শপিফাই ই-কমার্স ব্যবসা',
                'badge' => '⭐ Most Popular • 100 BV',
                'badge_bn' => '⭐ সর্বাধিক জনপ্রিয় • ১০০ BV',
                'price' => 120000,
                'price_formatted' => '৳120,000',
                'capital_amount' => 100000,
                'capital_formatted' => '৳100,000 Capital',
                'capital_formatted_bn' => '৳১,০০,০০০ মূলধন বিনিয়োগ',
                'setup_fee' => 20000,
                'setup_fee_formatted' => '৳100,000 Capital + ৳20,000 Setup',
                'setup_fee_formatted_bn' => '৳১,০০,০০০ ইনভেস্টমেন্ট + ৳২০,০০০ সেটআপ ফি',
                'bv' => 100,
                'bv_formatted' => '100 BV',
                'bv_formatted_bn' => '১০০ BV',
                'duration' => '100 Weeks (24 Months)',
                'duration_bn' => '১০০ সপ্তাহ (২৪ মাস)',
                'weekly_return_percent' => 1.75,
                'weekly_return_text' => '1.75% / Week (৳1,750/wk)',
                'weekly_return_text_bn' => '১.৭৫% / সপ্তাহ (৳১,৭৫০/সপ্তাহ)',
                'weekly_amount' => 1750,
                'total_plan_return' => 175000,
                'total_plan_return_formatted' => '৳1,75,000',
                'direct_commission' => '10% Spot Commission (৳12,000)',
                'direct_commission_bn' => '১০% স্পট রেফারেল কমিশন (৳১২,০০০)',
                'direct_commission_amount' => '৳12,000',
                'crowdfunding_limit' => 1000000,
                'crowdfunding_formatted' => 'Up to ৳10 Lac BDT',
                'crowdfunding_formatted_bn' => '১০ লাখ টাকা পর্যন্ত',
                'profit_sharing' => '৳5,000 to ৳20,000 / month (after 100 wks)',
                'profit_sharing_bn' => 'মাসে ৫,০০০ থেকে ২০,০০০ টাকা (১০০ সপ্তাহ পর)',
                'benefits' => [
                    ['text' => 'Branded Turnkey Shopify Store & Custom Domain', 'text_bn' => 'ব্র্যান্ডেড শপিফাই স্টোর ও কাস্টম ডোমেন সেটআপ'],
                    ['text' => 'Curated Verified High-Margin Dropshipping Products', 'text_bn' => 'যাচাইকৃত প্রিমিয়াম ও ট্রেন্ডিং প্রোডাক্ট সোর্সিং'],
                    ['text' => 'Weekly 1.75% Plan-based Return on Capital (100 Weeks)', 'text_bn' => 'ক্যাপিটালের ওপর সাপ্তাহিক ১.৭৫% প্ল্যান-ভিত্তিক রিটার্ন (১০০ সপ্তাহ)'],
                    ['text' => 'Custom SBL Packaging & Automated Courier Delivery', 'text_bn' => 'নিজস্ব কাস্টম প্যাকেজিং ও ডেলিভারি লজিস্টিকস সাপোর্ট'],
                    ['text' => 'Post-100 Weeks Monthly Profit Sharing (৳5k - ৳20k)', 'text_bn' => '১০০ সপ্তাহ পর আজীবন মাসিক প্রফিট শেয়ারিং (৫হাজার - ২০হাজার)'],
                    ['text' => 'Business Expansion Crowdfunding up to ৳10 Lac BDT', 'text_bn' => '১০ লাখ টাকা পর্যন্ত বিজনেস ক্রাউডফান্ডিং সম্প্রসারণ সুবিধা'],
                    ['text' => '100 BV for Fast-track Binary Career Progression', 'text_bn' => 'ক্যারিয়ার পদোন্নতি ও বোনাসের জন্য ১০০ BV পয়েন্ট'],
                    ['text' => 'Dedicated Facebook Ad Campaigns & Order Moderation', 'text_bn' => 'পেইড ফেসবুক এড ক্যাম্পেইন ও অর্ডার ম্যানেজমেন্ট সাপোর্ট'],
                ],
                'eligibility' => 'Entrepreneurs seeking verified domestic dropshipping operations.',
                'eligibility_bn' => 'দেশীয় ই-কমার্সে প্রতিষ্ঠিত ব্যবসা শুরু করতে ইচ্ছুক উদ্যোক্তা।',
                'disclaimer' => 'Plan-based returns are calculated on capital according to current SBL terms and do not represent guaranteed income.',
                'disclaimer_bn' => 'রিটার্ন বর্তমান SBL প্ল্যান অনুযায়ী ক্যাপিটালের ওপর নির্ধারিত, কোনো ফিক্সড বা গ্যারান্টেড ইনকাম নয়।',
                'last_verified_at' => 'September 2026',
                'status' => 'active',
                'theme' => 'orange',
            ],
            'international' => [
                'id' => 'international',
                'key' => 'international-550000',
                'name' => 'International Dropshipping',
                'name_bn' => 'আন্তর্জাতিক ড্রপশিপিং',
                'subtitle' => 'Global E-Commerce with Dedicated Project Team',
                'subtitle_bn' => 'ডেডিকেটেড টিম ও বৈশ্বিক মার্কেটপ্লেস ড্রপশিপিং',
                'badge' => '👑 Global Enterprise • 500 BV',
                'badge_bn' => '👑 গ্লোবাল এন্টারপ্রাইজ • ৫০০ BV',
                'price' => 550000,
                'price_formatted' => '৳550,000',
                'capital_amount' => 500000,
                'capital_formatted' => '৳500,000 Capital',
                'capital_formatted_bn' => '৳৫,০০,০০০ মূলধন বিনিয়োগ',
                'setup_fee' => 50000,
                'setup_fee_formatted' => '৳500,000 Capital + ৳50,000 Content/Setup',
                'setup_fee_formatted_bn' => '৳৫,০০,০০০ ইনভেস্টমেন্ট + ৳৫০,০০০ কনটেন্ট ও ফি',
                'bv' => 500,
                'bv_formatted' => '500 BV',
                'bv_formatted_bn' => '৫০০ BV',
                'duration' => '100 Weeks (24 Months)',
                'duration_bn' => '১০০ সপ্তাহ (২৪ মাস)',
                'weekly_return_percent' => 2.00,
                'weekly_return_text' => '2.0% / Week (৳10,000/wk)',
                'weekly_return_text_bn' => '২.০% / সপ্তাহ (৳১০,০০০/সপ্তাহ)',
                'weekly_amount' => 10000,
                'total_plan_return' => 1000000,
                'total_plan_return_formatted' => '৳10,00,000',
                'direct_commission' => '10% Spot Commission (৳55,000)',
                'direct_commission_bn' => '১০% স্পট রেফারেল কমিশন (৳৫৫,০০০)',
                'direct_commission_amount' => '৳55,000',
                'crowdfunding_limit' => 5000000,
                'crowdfunding_formatted' => 'Up to ৳50 Lac BDT',
                'crowdfunding_formatted_bn' => '৫০ লাখ টাকা পর্যন্ত',
                'profit_sharing' => '৳25,000 to ৳100,000 / month (after 100 wks)',
                'profit_sharing_bn' => 'মাসে ২৫,০০০ থেকে ১,০০,০০০ টাকা (১০০ সপ্তাহ পর)',
                'benefits' => [
                    ['text' => 'Dedicated Professional Team for End-to-End Management', 'text_bn' => 'প্রজেক্ট পরিচালনার জন্য সার্বক্ষণিক ডেডিকেটেড এক্সপার্ট টিম'],
                    ['text' => 'Global Shopify Store Setup with Multi-Currency Checkout', 'text_bn' => 'আন্তর্জাতিক মাল্টি-কারেন্সি শপিফাই মার্কেটপ্লেস সেটআপ'],
                    ['text' => 'Unlimited High-Converting UGC Video Creatives & Ads', 'text_bn' => 'আনলিমিটেড হাই-কনভার্টিং ইউজার ভিডিও ও ক্রিয়েটিভ প্রডাকশন'],
                    ['text' => 'Weekly 2.0% Plan-based Return on Capital (100 Weeks)', 'text_bn' => 'ক্যাপিটালের ওপর সাপ্তাহিক ২.০% প্ল্যান-ভিত্তিক রিটার্ন (১০০ সপ্তাহ)'],
                    ['text' => 'Post-100 Weeks Executive Profit Sharing (৳25k - ৳100k)', 'text_bn' => '১০০ সপ্তাহ পর আজীবন এক্সিকিউটিভ প্রফিট শেয়ারিং (২৫হাজার - ১লাখ)'],
                    ['text' => 'Business Expansion Crowdfunding up to ৳50 Lac BDT', 'text_bn' => '৫০ লাখ টাকা পর্যন্ত আন্তর্জাতিক ক্রাউডফান্ডিং সম্প্রসারণ সুবিধা'],
                    ['text' => '500 BV for Executive Binary Ranking & Leadership Incentives', 'text_bn' => 'টপ-লেভেল লিডারশিপ ও র‍্যাংক অর্জনের জন্য ৫০০ BV পয়েন্ট'],
                    ['text' => 'Direct Global Merchant Sourcing & Overseas Fulfillment', 'text_bn' => 'গ্লোবাল মার্চেন্ট সোর্সিং ও আন্তর্জাতিক ফুলফিলমেন্ট সাপোর্ট'],
                ],
                'eligibility' => 'High-ticket investors and serious enterprise cross-border operators.',
                'eligibility_bn' => 'উচ্চ মূল্যের বিনিয়োগকারী ও গ্লোবাল ই-কমার্স ব্র্যান্ড উদ্যোক্তা।',
                'disclaimer' => 'Plan-based returns are calculated on capital according to current SBL terms and do not represent guaranteed income.',
                'disclaimer_bn' => 'রিটার্ন বর্তমান SBL প্ল্যান অনুযায়ী ক্যাপিটালের ওপর নির্ধারিত, কোনো ফিক্সড বা গ্যারান্টেড ইনকাম নয়।',
                'last_verified_at' => 'September 2026',
                'status' => 'active',
                'theme' => 'amber',
            ],
        ];
    }

    /**
     * Standard Dropshipping Market vs SBL Comparison Data
     */
    public function getMarketComparisons(): array
    {
        return [
            ['service' => 'Website Development, Domain & Hosting Setup', 'service_bn' => 'ওয়েবসাইট ডেভেলপমেন্ট, ডোমেইন ও হোস্টিং সেটআপ', 'market' => '৳50,000 to ৳300,000', 'market_min' => 50000, 'sbl' => 'Included', 'sbl_bn' => 'অন্তর্ভুক্ত'],
            ['service' => 'Product Sourcing (Curated & Verified Merchants)', 'service_bn' => 'প্রোডাক্ট সোর্সিং (যাচাইকৃত মার্চেন্ট ও সাপ্লায়ার)', 'market' => '৳50,000 to ৳100,000', 'market_min' => 50000, 'sbl' => 'Included', 'sbl_bn' => 'অন্তর্ভুক্ত'],
            ['service' => 'Shopify E-Commerce Store & Annual Maintenance', 'service_bn' => 'শপিফাই স্টোর ও বাৎসরিক মেইনটেন্যান্স', 'market' => '৳36,000 / year', 'market_min' => 36000, 'sbl' => 'Included', 'sbl_bn' => 'অন্তর্ভুক্ত'],
            ['service' => 'Professional Facebook Business Page Setup', 'service_bn' => 'প্রফেশনাল ফেসবুক বিজনেস পেজ সেটআপ', 'market' => '৳5,000 to ৳10,000', 'market_min' => 5000, 'sbl' => 'Included', 'sbl_bn' => 'অন্তর্ভুক্ত'],
            ['service' => 'Video & Creative Image Ad Production', 'service_bn' => 'ভিডিও ও ইমেজ ক্রিয়েটিভ অ্যাড প্রোডাকশন', 'market' => '৳3,000 to ৳5,000', 'market_min' => 3000, 'sbl' => 'Included', 'sbl_bn' => 'অন্তর্ভুক্ত'],
            ['service' => 'Facebook Ad Campaign Management & Optimization', 'service_bn' => 'ফেসবুক অ্যাড ক্যাম্পেইন ম্যানেজমেন্ট ও অপটিমাইজেশন', 'market' => '৳50,000 to ৳100,000', 'market_min' => 50000, 'sbl' => 'Included', 'sbl_bn' => 'অন্তর্ভুক্ত'],
            ['service' => 'Product Packaging & Delivery Logistics Support', 'service_bn' => 'প্রোডাক্ট প্যাকেজিং ও ডেলিভারি লজিস্টিকস সাপোর্ট', 'market' => '৳20,000 to ৳50,000', 'market_min' => 20000, 'sbl' => 'Included', 'sbl_bn' => 'অন্তর্ভুক্ত'],
            ['service' => 'Page Moderation & Dedicated Customer Support', 'service_bn' => 'পেজ মডারেশন ও কাস্টমার সাপোর্ট', 'market' => '৳15,000 to ৳30,000', 'market_min' => 15000, 'sbl' => 'Included', 'sbl_bn' => 'অন্তর্ভুক্ত'],
            ['service' => 'Sales Analytics, Insights & Performance Reporting', 'service_bn' => 'সেলস অ্যানালিটিক্স ও পারফরম্যান্স রিপোর্টিং', 'market' => '৳50,000 to ৳100,000', 'market_min' => 50000, 'sbl' => 'Included', 'sbl_bn' => 'অন্তর্ভুক্ত'],
            ['service' => 'Automated Payment Gateway & Order Processing', 'service_bn' => 'অটোমেটেড পেমেন্ট গেটওয়ে ও অর্ডার প্রসেসিং', 'market' => '৳5,000 to ৳10,000', 'market_min' => 5000, 'sbl' => 'Included', 'sbl_bn' => 'অন্তর্ভুক্ত'],
            ['service' => 'Inventory & Stock Management', 'service_bn' => 'ইনভেন্টরি ও স্টক ম্যানেজমেন্ট', 'market' => '৳20,000 to ৳50,000', 'market_min' => 20000, 'sbl' => 'Included', 'sbl_bn' => 'অন্তর্ভুক্ত'],
            ['service' => 'Online Marketing Budget (Facebook Boosting)', 'service_bn' => 'অনলাইন মার্কেটিং বাজেট (ফেসবুক বুস্টিং)', 'market' => '৳200,000 to ৳500,000', 'market_min' => 200000, 'sbl' => 'Included', 'sbl_bn' => 'অন্তর্ভুক্ত'],
        ];
    }

    /**
     * 6-Month Scale Projections (Illustrative Scenario)
     */
    public function getGrowthTrajectory(): array
    {
        return [
            ['month' => '1st Month', 'month_bn' => '১ম মাস', 'ad_spend' => 'Preparation', 'ad_spend_bn' => 'প্রস্তুতিমূলক ধাপ', 'audience' => 'Audience Setup', 'audience_bn' => 'টার্গেটিং অডিয়েন্স সেটআপ', 'orders' => 'Store & Catalog Launch', 'orders_bn' => 'স্টোর ও ক্যাটালগ উন্মোচন'],
            ['month' => '2nd Month', 'month_bn' => '২য় মাস', 'ad_spend' => '5*30 = 150 USD', 'ad_spend_bn' => '৫*৩০ = ১৫০ ডলার', 'audience' => '10k Audience', 'audience_bn' => '১০,০০০ রিচ', 'orders' => '50+ Projected Orders', 'orders_bn' => '৫০+ সম্ভাব্য অর্ডার'],
            ['month' => '3rd Month', 'month_bn' => '৩য় মাস', 'ad_spend' => '5*30 = 150 USD', 'ad_spend_bn' => '৫*৩০ = ১৫০ ডলার', 'audience' => '30k Audience', 'audience_bn' => '৩০,০০০ রিচ', 'orders' => '100+ Projected Orders', 'orders_bn' => '১০০+ সম্ভাব্য অর্ডার'],
            ['month' => '4th Month', 'month_bn' => '৪র্থ মাস', 'ad_spend' => '5*30 = 150 USD', 'ad_spend_bn' => '৫*৩০ = ১৫০ ডলার', 'audience' => '50k Audience', 'audience_bn' => '৫০,০০০ রিচ', 'orders' => '200+ Projected Orders', 'orders_bn' => '২০০+ সম্ভাব্য অর্ডার'],
            ['month' => '5th Month', 'month_bn' => '৫ম মাস', 'ad_spend' => '5*30 = 150 USD', 'ad_spend_bn' => '৫*৩০ = ১৫০ ডলার', 'audience' => '100k Audience', 'audience_bn' => '১,০০,০০০ রিচ', 'orders' => '300+ Projected Orders', 'orders_bn' => '৩০০+ সম্ভাব্য অর্ডার'],
            ['month' => '6th Month', 'month_bn' => '৬ষ্ঠ মাস', 'ad_spend' => '5*30 = 150 USD', 'ad_spend_bn' => '৫*৩০ = ১৫০ ডলার', 'audience' => '200k Audience', 'audience_bn' => '২,০০,০০০ রিচ', 'orders' => '500+ Projected Orders', 'orders_bn' => '৫০০+ সম্ভাব্য অর্ডার'],
        ];
    }

    /**
     * Investor vs Networker Comparison
     */
    private function getCounselingPoints(): array
    {
        return [
            [
                'investor' => 'Investment Considerations (Capital deployment & weekly plan-based returns)',
                'networker' => 'Multiple Center Advantage (Multiply earnings with multiple IDs)',
            ],
            [
                'investor' => 'Legal & Authentic Dropshipping Model',
                'networker' => 'Early Mover Advantage (Binary team spillover & position benefits)',
            ],
            [
                'investor' => 'Personal Business Branding & Long-term Income',
                'networker' => 'Performance-based Pair Matching Reward (up to 100 pairs daily)',
            ],
            [
                'investor' => 'Live Store & E-commerce Operational Support',
                'networker' => 'Sustainable Income & Team Leadership Development',
            ],
        ];
    }

    /**
     * SBL Prospect Counseling Guide Configuration (Investor vs Networker vs Hybrid)
     */
    public function getCounselingConfig(): array
    {
        return [
            'discoveryQuestions' => [
                [
                    'id' => 'goal',
                    'q_en' => 'What is your primary goal?',
                    'q_bn' => 'আপনার প্রাথমিক বা মূল লক্ষ্য কোনটি?',
                    'options' => [
                        ['id' => 'capital_return', 'label_en' => 'Capital / Business Return', 'label_bn' => 'মূলধন / প্রজেক্টের রিটার্ন', 'affinity' => 'investor'],
                        ['id' => 'extra_income', 'label_en' => 'Extra Income / Affiliate', 'label_bn' => 'অতিরিক্ত আয় / অ্যাফিলিয়েট', 'affinity' => 'networker'],
                        ['id' => 'both', 'label_en' => 'Both (Return & Affiliate)', 'label_bn' => 'উভয় সুবিধা (রিটার্ন ও অ্যাফিলিয়েট)', 'affinity' => 'hybrid'],
                    ]
                ],
                [
                    'id' => 'budget',
                    'q_en' => 'What is your comfortable starting budget?',
                    'q_bn' => 'প্রাথমিকভাবে কোন বাজেটটি আপনার জন্য স্বাচ্ছন্দ্যদায়ক?',
                    'options' => [
                        ['id' => 'b_10k', 'label_en' => '৳10,000', 'label_bn' => '১০,০০০ টাকা', 'affinity' => 'networker'],
                        ['id' => 'b_120k', 'label_en' => '৳1,20,000', 'label_bn' => '১,২০,০০০ টাকা', 'affinity' => 'hybrid'],
                        ['id' => 'b_550k', 'label_en' => '৳5,50,000+', 'label_bn' => '৫,৫০,০০০+ টাকা', 'affinity' => 'investor'],
                    ]
                ],
                [
                    'id' => 'involvement',
                    'q_en' => 'What type of involvement do you prefer?',
                    'q_bn' => 'কাজের ক্ষেত্রে আপনি কেমন অংশগ্রহণ পছন্দ করেন?',
                    'options' => [
                        ['id' => 'managed', 'label_en' => 'Mostly managed / low involvement', 'label_bn' => 'ম্যানেজড / স্বল্প সম্পৃক্ততা', 'affinity' => 'investor'],
                        ['id' => 'active_net', 'label_en' => 'Active networking', 'label_bn' => 'সক্রিয় নেটওয়ার্কিং ও টিম বিল্ড', 'affinity' => 'networker'],
                        ['id' => 'biz_net', 'label_en' => 'Business + networking', 'label_bn' => 'ব্যবসা ও নেটওয়ার্কিং উভয়ই', 'affinity' => 'hybrid'],
                    ]
                ],
                [
                    'id' => 'network',
                    'q_en' => 'Do you already have a network or audience?',
                    'q_bn' => 'আপনার কি ইতোমধ্যে পরিচিত টিম বা অডিয়েন্স আছে?',
                    'options' => [
                        ['id' => 'net_yes', 'label_en' => 'Yes', 'label_bn' => 'হ্যাঁ, রেডি টিম/পরিচিতি আছে', 'affinity' => 'networker'],
                        ['id' => 'net_some', 'label_en' => 'Some contacts', 'label_bn' => 'কিছু পরিচিত যোগাযোগ আছে', 'affinity' => 'hybrid'],
                        ['id' => 'net_no', 'label_en' => 'Not really', 'label_bn' => 'না, তেমন পরিচিতি নেই', 'affinity' => 'investor'],
                    ]
                ],
                [
                    'id' => 'priority',
                    'q_en' => 'What is your main priority?',
                    'q_bn' => 'আপনার প্রধান আর্থিক অগ্রাধিকার কোনটি?',
                    'options' => [
                        ['id' => 'pri_safety', 'label_en' => 'Capital recovery / safety', 'label_bn' => 'মূলধন পুনরুদ্ধার ও নিরাপত্তা', 'affinity' => 'investor'],
                        ['id' => 'pri_cashflow', 'label_en' => 'Active cash flow', 'label_bn' => 'দ্রুত ক্যাশ ফ্লো ও কমিশন', 'affinity' => 'networker'],
                        ['id' => 'pri_growth', 'label_en' => 'Long-term business growth', 'label_bn' => 'দীর্ঘমেয়াদি ব্যবসায়িক প্রবৃদ্ধি', 'affinity' => 'hybrid'],
                    ]
                ],
            ],
            'results' => [
                'investor' => [
                    'key' => 'investor',
                    'name_en' => 'INVESTOR MINDSET',
                    'name_bn' => 'ইনভেস্টর মাইন্ডসেট',
                    'accent' => 'amber',
                    'badge_en' => 'Capital Focused',
                    'badge_bn' => 'মূলধন কেন্দ্রীক',
                    'description_en' => 'Best suited for prospects mainly interested in capital-based project participation and managed e-commerce operations.',
                    'description_bn' => 'যাঁরা মূলত মূলধনভিত্তিক প্রজেক্ট পার্টিসিপেশন ও ম্যানেজড ই-কমার্স ড্রপশিপিংয়ে বেশি আগ্রহী।',
                    'best_suited' => [
                        ['en' => 'Capital-based project participation', 'bn' => 'মূলধনভিত্তিক বাণিজ্যিক প্রজেক্টে অংশগ্রহণ'],
                        ['en' => 'Clear plan structure (100 weeks)', 'bn' => 'সুস্পষ্ট ও স্বচ্ছ প্ল্যান কাঠামো (১০০ সপ্তাহ)'],
                        ['en' => 'Predictable recovery timeline (~57 weeks)', 'bn' => 'মূলধন রিকভারি সময়সীমা (~৫৭ সপ্তাহ)'],
                        ['en' => 'Managed business operations', 'bn' => 'ম্যানেজড ই-কমার্স বিজনেস পরিচালনা'],
                    ],
                    'actions' => [
                        ['label_en' => 'View Talking Points', 'label_bn' => 'টকিং পয়েন্ট দেখুন', 'action' => 'points'],
                        ['label_en' => 'Show Packages', 'label_bn' => 'প্যাকেজ দেখুন', 'route' => 'packages.index'],
                    ]
                ],
                'networker' => [
                    'key' => 'networker',
                    'name_en' => 'NETWORKER MINDSET',
                    'name_bn' => 'নেটওয়ার্কার মাইন্ডসেট',
                    'accent' => 'indigo',
                    'badge_en' => 'Affiliate & Team',
                    'badge_bn' => 'অ্যাফিলিয়েট ও টিম',
                    'description_en' => 'Best suited for prospects interested in sales commissions, dual-team building, and leadership career ranks.',
                    'description_bn' => 'যাঁরা সেলস কমিশন, লেফট-রাইট টিম গঠন এবং ক্যারিয়ার পদবী অর্জনে বেশি আগ্রহী।',
                    'best_suited' => [
                        ['en' => 'Active affiliate team building', 'bn' => 'সক্রিয় অ্যাফিলিয়েট ও টিম সম্প্রসারণ'],
                        ['en' => '10% Direct spot commissions', 'bn' => '১০% তাৎক্ষণিক ডিরেক্ট স্পট কমিশন'],
                        ['en' => '৳500 Binary pair matching rewards', 'bn' => 'প্রতি পেয়ার ম্যাচিংয়ে ৫০০ টাকা রিওয়ার্ড'],
                        ['en' => '6 Career rank cash milestones', 'bn' => '৬টি ক্যারিয়ার পদবীর বড় প্রাইজমানি'],
                    ],
                    'actions' => [
                        ['label_en' => 'View Talking Points', 'label_bn' => 'টকিং পয়েন্ট দেখুন', 'action' => 'points'],
                        ['label_en' => 'View Ranks', 'label_bn' => 'র‍্যাংক রোডম্যাপ', 'route' => 'ranks.index'],
                        ['label_en' => 'View Commission Plan', 'label_bn' => 'মার্কেটিং প্ল্যান', 'route' => 'commission.index'],
                    ]
                ],
                'hybrid' => [
                    'key' => 'hybrid',
                    'name_en' => 'HYBRID BUSINESS BUILDER',
                    'name_bn' => 'হাইব্রিড বিজনেস বিল্ডার',
                    'accent' => 'emerald',
                    'badge_en' => 'Balanced Growth',
                    'badge_bn' => 'ভারসাম্যপূর্ণ প্রবৃদ্ধি',
                    'description_en' => 'Best suited for prospects who want dual benefits: e-commerce weekly returns combined with active team networking.',
                    'description_bn' => 'যাঁরা ড্রপশিপিং থেকে সাপ্তাহিক আয়ের পাশাপাশি টিম তৈরি করে বড় কমিশন নিশ্চিত করতে চান।',
                    'best_suited' => [
                        ['en' => 'Package / business participation', 'bn' => 'বাণিজ্যিক ড্রপশিপিং প্যাকেজে অংশগ্রহণ'],
                        ['en' => 'Dual income (Weekly return + Affiliate)', 'bn' => 'দ্বৈত আয়ের সুযোগ (সাপ্তাহিক রিটার্ন + অ্যাফিলিয়েট)'],
                        ['en' => 'Capital recovery first strategy', 'bn' => 'আগে মূলধন নিরাপদ ও রিকভার করার কৌশল'],
                        ['en' => 'Controlled, stable team growth', 'bn' => 'নিয়ন্ত্রিত ও টেকসই টিম সম্প্রসারণ'],
                    ],
                    'actions' => [
                        ['label_en' => 'View Talking Points', 'label_bn' => 'টকিং পয়েন্ট দেখুন', 'action' => 'points'],
                        ['label_en' => 'View Packages', 'label_bn' => 'প্যাকেজ দেখুন', 'route' => 'packages.index'],
                        ['label_en' => 'View Ranks', 'label_bn' => 'র‍্যাংক দেখুন', 'route' => 'ranks.index'],
                    ]
                ]
            ],
            'talkingPoints' => [
                'investor' => [
                    ['step' => 1, 'text' => 'Explain the package cost clearly (National ৳1,20,000 / International ৳5,50,000).', 'text_bn' => 'প্যাকেজের সঠিক খরচ স্পষ্ট করুন (ন্যাশনাল ১,২০,০০০ টাকা / ইন্টারন্যাশনাল ৫,৫০,০০০ টাকা)।'],
                    ['step' => 2, 'text' => 'Explain capital and setup fee separately (e.g. National: ৳1,00,000 core capital + ৳20,000 setup fee).', 'text_bn' => 'মূলধন ও সেটআপ ফি আলাদা করে বুঝিয়ে বলুন (যেমন ন্যাশনাল: ১ লাখ মূলধন + ২০ হাজার ফি)।'],
                    ['step' => 3, 'text' => 'Explain the current plan duration (100 weeks project term).', 'text_bn' => 'বর্তমান প্ল্যানের নির্দিষ্ট মেয়াদ বুঝিয়ে বলুন (১০০ সপ্তাহের বাণিজ্যিক প্রজেক্ট)।'],
                    ['step' => 4, 'text' => 'Explain plan-based return structure (1.75% / ৳1,750 weekly for National; 2.0% / ৳10,000 for International).', 'text_bn' => 'প্ল্যানভিত্তিক রিটার্ন কাঠামো পরিষ্কার করুন (ন্যাশনালে ১.৭৫% বা ১,৭৫০ টাকা/সপ্তাহ; ইন্টারন্যাশনালে ২.০% বা ১০,০০০ টাকা)।'],
                    ['step' => 5, 'text' => 'Explain withdrawal / realization process (weekly credits to verified wallet, bank/MFS transfer).', 'text_bn' => 'টাকা উত্তোলনের প্রক্রিয়া ব্যাখ্যা করুন (সাপ্তাহিক ওয়ালেট জমা ও ব্যাংক/বিকাশ উইথড্র)।'],
                    ['step' => 6, 'text' => 'Explain business and market risk (commercial e-commerce operation, not fixed guaranteed bank interest).', 'text_bn' => 'ব্যবসায়িক বাস্তবতা স্পষ্ট করুন (বাস্তব ড্রপশিপিং ব্যবসা, কোনো ফিক্সড ব্যাংক সুদ নয়)।'],
                ],
                'networker' => [
                    ['step' => 1, 'text' => 'Explain direct / spot commission (10% instant cash commission on any sponsored package).', 'text_bn' => 'ডিরেক্ট স্পট কমিশন বুঝিয়ে বলুন (যেকোনো প্যাকেজ স্পনসরে তাৎক্ষণিক ১০% ক্যাশ কমিশন)।'],
                    ['step' => 2, 'text' => 'Explain Left and Right team concept (dual-team binary tree placement and balance).', 'text_bn' => 'লেফট ও রাইট টিমের ধারণা দিন (বাইনারি ট্রি প্লেসমেন্ট ও ভারসাম্য রক্ষা)।'],
                    ['step' => 3, 'text' => 'Explain pair matching reward (৳500 per binary pair with 100 pairs daily cap).', 'text_bn' => 'পেয়ার ম্যাচিং রিওয়ার্ড ব্যাখ্যা করুন (প্রতি পেয়ার ৫০০ টাকা, দৈনিক সর্বোচ্চ ৫০,০০০ টাকা ক্যাপিং)।'],
                    ['step' => 4, 'text' => 'Explain generation / UDR concept (multi-tier overrides up to 10 generations).', 'text_bn' => '১০ প্রজন্মের UDR কমিশন কাঠামো সংক্ষেপে বলুন (১ম প্রজন্মে ১০%, ২য় প্রজন্মে ২% ইত্যাদি)।'],
                    ['step' => 5, 'text' => 'Explain rank journey (6 progressive ranks from FME ৳5,000 up to ETD ৳20,00,000).', 'text_bn' => 'র‍্যাংক জার্নি ও প্রাইজমানি দেখান (FME ৫,০০০ টাকা থেকে শুরু করে ETD ২০,০০,০০০ টাকা)।'],
                    ['step' => 6, 'text' => 'Explain that team income requires active performance (balanced legs and real team guidance).', 'text_bn' => 'পরিষ্কার করুন যে টিম ইনকাম সক্রিয় দল পরিচালনা ও কাজের ওপর নির্ভরশীল।'],
                ],
                'hybrid' => [
                    ['step' => 1, 'text' => 'Explain package / business participation (National ৳1.2L foundation for dropshipping + top binary position).', 'text_bn' => 'প্যাকেজ ও বিজনেসে অংশগ্রহণ তুলে ধরুন (ন্যাশনাল ১.২ লাখের ড্রপশিপিং ও বাইনারি পজিশন)।'],
                    ['step' => 2, 'text' => 'Explain affiliate opportunity (earn 10% spot commissions on any prospect referrals).', 'text_bn' => 'অ্যাফিলিয়েট সুযোগ ব্যাখ্যা করুন (রেফারেল থেকে তাৎক্ষণিক ১০% কমিশন অর্জন)।'],
                    ['step' => 3, 'text' => 'Explain team building (dual-leg binary expansion while collecting weekly store returns).', 'text_bn' => 'টিম বিল্ডিংয়ের সুবিধা বলুন (সাপ্তাহিক রিটার্নের পাশাপাশি টিম থেকে অতিরিক্ত আয়)।'],
                    ['step' => 4, 'text' => 'Explain rank progression (sponsor 10 directs to unlock FME and first milestone bonus).', 'text_bn' => 'র‍্যাংক প্রগ্রেশন বুঝিয়ে দিন (১০ জন ডিরেক্ট স্পনসর করে FME পদবী ও ৫,০০০ টাকা বোনাস)।'],
                    ['step' => 5, 'text' => 'Explain capital recovery first (prioritize securing core capital within ~57 weeks).', 'text_bn' => 'প্রথমে মূলধন নিরাপদ করার পরামর্শ দিন (প্রথম ৫৭ সপ্তাহে মূলধন রিকভারি অগ্রাধিকার)।'],
                    ['step' => 6, 'text' => 'Explain controlled business growth (expand teams steadily with reinvested commission profits).', 'text_bn' => 'নিয়ন্ত্রিত ব্যবসায়িক প্রবৃদ্ধি (অর্জিত কমিশন দিয়ে টেকসই টিম বৃদ্ধি)।'],
                ],
            ],
            'safeWording' => [
                [
                    'avoid' => 'Guaranteed Profit',
                    'avoid_bn' => 'নিশ্চিত লাভ',
                    'use' => 'Plan-based potential return',
                    'use_bn' => 'প্ল্যান অনুযায়ী সম্ভাব্য রিটার্ন'
                ],
                [
                    'avoid' => 'Risk-free investment',
                    'avoid_bn' => 'ঝুঁকিহীন ইনভেস্টমেন্ট',
                    'use' => 'Commercial business / project participation',
                    'use_bn' => 'বাণিজ্যিক প্রজেক্টে ব্যবসায়িক অংশগ্রহণ'
                ],
                [
                    'avoid' => 'Fixed daily income',
                    'avoid_bn' => 'ফিক্সড দৈনিক আয়',
                    'use' => 'Performance-based commission',
                    'use_bn' => 'পারফরম্যান্স ও টিমভিত্তিক কমিশন'
                ],
                [
                    'avoid' => 'Guaranteed rank reward',
                    'avoid_bn' => 'নিশ্চিত পদবী ও পুরস্কার',
                    'use' => 'Milestone reward subject to qualification',
                    'use_bn' => 'শর্ত ও যোগ্যতা পূরণ সাপেক্ষে মাইলস্টোন রিওয়ার্ড'
                ],
            ],
            'objections' => [
                [
                    'q_en' => 'Is my capital guaranteed?',
                    'q_bn' => 'আমার মূলধন কি গ্যারান্টেড বা ঝুঁকিমুক্ত?',
                    'a_en' => 'No commercial business can guarantee capital without risk. SBL operates real e-commerce dropshipping with physical inventory and advertising. Weekly returns are plan-based across 100 weeks reflecting authentic business operations.',
                    'a_bn' => 'বাণিজ্যিক ব্যবসায় কোনো ঝুঁকিহীন অলৌকিক গ্যারান্টি হয় না। SBL বাস্তব ই-কমার্স ড্রপশিপিং পরিচালনা করে। ১০০ সপ্তাহব্যাপী সাপ্তাহিক রিটার্ন ব্যবসায়িক পারফরম্যান্স ও প্ল্যান অনুযায়ী বণ্টন করা হয়।'
                ],
                [
                    'q_en' => 'How does the weekly plan work?',
                    'q_bn' => 'সাপ্তাহিক প্ল্যান কীভাবে কাজ করে?',
                    'a_en' => 'Weekly returns are calculated on core project capital (1.75% / ৳1,750 for National; 2.0% / ৳10,000 for International). Payouts are credited weekly to your verified wallet for up to 100 weeks.',
                    'a_bn' => 'মূলধনের ওপর সাপ্তাহিক রিটার্ন হিসেব করা হয় (ন্যাশনালে ১.৭৫% বা ১,৭৫০ টাকা; ইন্টারন্যাশনালে ২.০% বা ১০,০০০ টাকা)। এটি ১০০ সপ্তাহ পর্যন্ত নিয়মিত ওয়ালেটে জমা হয়।'
                ],
                [
                    'q_en' => 'How do withdrawals work?',
                    'q_bn' => 'উত্তোলন বা উইথড্র কীভাবে কাজ করে?',
                    'a_en' => 'Returns and commissions credit to your SBL verified wallet. You can submit withdrawal requests to your verified Bank Account or MFS (bKash/Nagad) on designated schedule windows.',
                    'a_bn' => 'উপার্জিত অর্থ ওয়ালেটে জমা হলে নির্ধারিত শিডিউল অনুযায়ী আপনার ভেরিফায়েড ব্যাংক অ্যাকাউন্ট অথবা বিকাশ/নগদে সরাসরি উইথড্র করা যায়।'
                ],
                [
                    'q_en' => 'Do I need to refer people?',
                    'q_bn' => 'আমাকে কি মানুষ রেফার করতে হবে?',
                    'a_en' => 'No. If you participate in a National or International dropshipping project, weekly returns come from e-commerce store operations. Team building and sponsoring are completely optional.',
                    'a_bn' => 'না। ন্যাশনাল বা ইন্টারন্যাশনাল প্রজেক্টে সাপ্তাহিক রিটার্ন ড্রপশিপিং সেলস থেকে আসে। কোনো মেম্বার রেফার করা বাধ্যতামূলক নয়, এটি সম্পূর্ণ ঐচ্ছিক।'
                ],
                [
                    'q_en' => 'Can I start with ৳10,000?',
                    'q_bn' => 'আমি কি ১০,০০০ টাকা দিয়ে শুরু করতে পারি?',
                    'a_en' => 'Yes. The Starter Membership (৳10,000) secures your position in the binary tree, includes affiliate account and Facebook page setup, and qualifies you for 10% direct spot commission.',
                    'a_bn' => 'হ্যাঁ। ১০,০০০ টাকার স্টার্টার মেম্বারশিপ নিয়ে বাইনারি পজিশন নিশ্চিত করা যায় এবং ফ্রি পেজ সেটআপ সহ ১০% ডিরেক্ট স্পট কমিশন উপার্জন শুরু করা যায়।'
                ],
                [
                    'q_en' => 'What are the main risks?',
                    'q_bn' => 'প্রধান ব্যবসায়িক ঝুঁকিগুলো কী কী?',
                    'a_en' => 'As a live e-commerce enterprise, returns depend on digital marketing performance, product demand, and delivery logistics. While professionally managed, returns reflect real commercial sales, not fixed bank deposits.',
                    'a_bn' => 'লাইভ ই-কমার্স ব্যবসা হিসেবে রিটার্ন ডিজিটাল মার্কেটিং, পণ্যের চাহিদা ও ডেলিভারি পারফরম্যান্সের ওপর নির্ভরশীল। এটি একটি খাঁটি ব্যবসা, কোনো ফিক্সড ব্যাংক ডিপোজিট নয়।'
                ],
            ],
            'nextSteps' => [
                [
                    'title' => 'Packages',
                    'title_bn' => 'প্যাকেজসমূহ',
                    'desc' => 'For package cost, duration and benefits',
                    'desc_bn' => 'প্যাকেজের খরচ, মেয়াদ ও বিস্তারিত সুবিধাসমূহ',
                    'route' => 'packages.index',
                    'icon' => '📦',
                    'badge' => 'Dropshipping'
                ],
                [
                    'title' => 'Ranks',
                    'title_bn' => 'ক্যারিয়ার র‍্যাংক',
                    'desc' => 'For career progression and team requirements',
                    'desc_bn' => 'ক্যারিয়ার পদবীর শর্ত ও মাইলস্টোন প্রাইজমানি',
                    'route' => 'ranks.index',
                    'icon' => '🏆',
                    'badge' => 'Leadership'
                ],
                [
                    'title' => 'Commission Plan',
                    'title_bn' => 'মার্কেটিং প্ল্যান',
                    'desc' => 'For affiliate and team income structure',
                    'desc_bn' => 'স্পট কমিশন, পেয়ার ম্যাচিং ও UDR কাঠামো',
                    'route' => 'commission.index',
                    'icon' => '📈',
                    'badge' => 'Affiliate'
                ],
            ]
        ];
    }

    /**
     * 10-Generation Affiliate Matrix (Official Rates + Illustrative Projections)
     */
    public function getGenerationMatrix(): array
    {
        return [
            [
                'generation' => 1,
                'gen' => '1st',
                'gen_bn' => '১ম প্রজন্ম',
                'rate' => '10%',
                'rate_num' => 0.10,
                'people' => '10',
                'people_num' => 10,
                'volume_formatted' => '৳1,00,000',
                'volume_formatted_bn' => '১,০০,০০০ টাকা',
                'commission' => '10,000',
                'commission_formatted' => '৳10,000',
                'commission_formatted_bn' => '১০,০০০ টাকা',
                'qualification' => 'Active Starter or higher membership',
                'qualification_bn' => 'সক্রিয় মেম্বারশিপ',
                'is_advanced' => false,
            ],
            [
                'generation' => 2,
                'gen' => '2nd',
                'gen_bn' => '২য় প্রজন্ম',
                'rate' => '2%',
                'rate_num' => 0.02,
                'people' => '100',
                'people_num' => 100,
                'volume_formatted' => '৳10,00,000',
                'volume_formatted_bn' => '১০,০০,০০০ টাকা',
                'commission' => '20,000',
                'commission_formatted' => '৳20,000',
                'commission_formatted_bn' => '২০,০০০ টাকা',
                'qualification' => 'Active dual team with 2 direct sponsors',
                'qualification_bn' => '২ জন ডিরেক্ট স্পনসর ও সক্রিয় আইডি',
                'is_advanced' => false,
            ],
            [
                'generation' => 3,
                'gen' => '3rd',
                'gen_bn' => '৩য় প্রজন্ম',
                'rate' => '1%',
                'rate_num' => 0.01,
                'people' => '1,000',
                'people_num' => 1000,
                'volume_formatted' => '৳1,00,00,000',
                'volume_formatted_bn' => '১,০০,০০,০০০ টাকা',
                'commission' => '1,00,000',
                'commission_formatted' => '৳1,00,000',
                'commission_formatted_bn' => '১,০০,০০০ টাকা',
                'qualification' => 'Active dual team with 4 direct sponsors',
                'qualification_bn' => '৪ জন ডিরেক্ট স্পনসর ও সক্রিয় আইডি',
                'is_advanced' => false,
            ],
            [
                'generation' => 4,
                'gen' => '4th',
                'gen_bn' => '৪র্থ প্রজন্ম',
                'rate' => '1%',
                'rate_num' => 0.01,
                'people' => '10,000',
                'people_num' => 10000,
                'volume_formatted' => '৳10,00,00,000',
                'volume_formatted_bn' => '১০,০০,০০,০০০ টাকা',
                'commission' => '10,00,000',
                'commission_formatted' => '৳10,00,000',
                'commission_formatted_bn' => '১০,০০,০০০ টাকা',
                'qualification' => 'FME or higher rank qualification',
                'qualification_bn' => 'FME বা তদূর্ধ্ব র‍্যাংক কোয়ালিফিকেশন',
                'is_advanced' => false,
            ],
            [
                'generation' => 5,
                'gen' => '5th',
                'gen_bn' => '৫ম প্রজন্ম',
                'rate' => '0.5%',
                'rate_num' => 0.005,
                'people' => '1,00,000',
                'people_num' => 100000,
                'volume_formatted' => '৳1,00,00,00,000',
                'volume_formatted_bn' => '১,০০,০০,০০,০০০ টাকা',
                'commission' => '50,00,000',
                'commission_formatted' => '৳50,00,000',
                'commission_formatted_bn' => '৫০,০০,০০০ টাকা',
                'qualification' => 'SME or higher rank qualification',
                'qualification_bn' => 'SME বা তদূর্ধ্ব র‍্যাংক কোয়ালিফিকেশন',
                'is_advanced' => false,
            ],
            [
                'generation' => 6,
                'gen' => '6th',
                'gen_bn' => '৬ষ্ঠ প্রজন্ম',
                'rate' => '0.1%',
                'rate_num' => 0.001,
                'people' => '10,00,000',
                'people_num' => 1000000,
                'volume_formatted' => '৳10,00,00,00,000',
                'volume_formatted_bn' => '১০,০০,০০,০০,০০০ টাকা',
                'commission' => '1,00,00,000',
                'commission_formatted' => '৳1,00,00,000',
                'commission_formatted_bn' => '১,০০,০০,০০০ টাকা',
                'qualification' => 'PME or higher rank qualification',
                'qualification_bn' => 'PME বা তদূর্ধ্ব র‍্যাংক কোয়ালিফিকেশন',
                'is_advanced' => false,
            ],
            [
                'generation' => 7,
                'gen' => '7th',
                'gen_bn' => '৭ম প্রজন্ম',
                'rate' => '0.1%',
                'rate_num' => 0.001,
                'people' => '1,00,00,000',
                'people_num' => 10000000,
                'volume_formatted' => '৳1,00,00,00,00,000',
                'volume_formatted_bn' => '১,০০,০০,০০,০০,০০০ টাকা',
                'commission' => '10,00,00,000',
                'commission_formatted' => '৳10,00,00,000',
                'commission_formatted_bn' => '১০,০০,০০,০০০ টাকা',
                'qualification' => 'BME or higher rank qualification',
                'qualification_bn' => 'BME বা তদূর্ধ্ব র‍্যাংক কোয়ালিফিকেশন',
                'is_advanced' => true,
            ],
            [
                'generation' => 8,
                'gen' => '8th',
                'gen_bn' => '৮ম প্রজন্ম',
                'rate' => '0.1%',
                'rate_num' => 0.001,
                'people' => '10,00,00,000',
                'people_num' => 100000000,
                'volume_formatted' => '৳10,00,00,00,00,000',
                'volume_formatted_bn' => '১০,০০,০০,০০,০০,০০০ টাকা',
                'commission' => '1,00,00,00,000',
                'commission_formatted' => '৳1,00,00,00,000',
                'commission_formatted_bn' => '১,০০,০০,০০,০০০ টাকা',
                'qualification' => 'GME or higher rank qualification',
                'qualification_bn' => 'GME বা তদূর্ধ্ব র‍্যাংক কোয়ালিফিকেশন',
                'is_advanced' => true,
            ],
            [
                'generation' => 9,
                'gen' => '9th',
                'gen_bn' => '৯ম প্রজন্ম',
                'rate' => '0.1%',
                'rate_num' => 0.001,
                'people' => '1,00,00,00,000',
                'people_num' => 1000000000,
                'volume_formatted' => '৳1,00,00,00,00,00,000',
                'volume_formatted_bn' => '১,০০,০০,০০,০০,০০,০০০ টাকা',
                'commission' => '10,00,00,00,000',
                'commission_formatted' => '৳10,00,00,00,000',
                'commission_formatted_bn' => '১০,০০,০০,০০,০০০ টাকা',
                'qualification' => 'ETD executive qualification',
                'qualification_bn' => 'ETD এক্সিকিউটিভ কোয়ালিফিকেশন',
                'is_advanced' => true,
            ],
            [
                'generation' => 10,
                'gen' => '10th',
                'gen_bn' => '১০ম প্রজন্ম',
                'rate' => '0.1%',
                'rate_num' => 0.001,
                'people' => '10,00,00,00,000',
                'people_num' => 10000000000,
                'volume_formatted' => '৳10,00,00,00,00,00,000',
                'volume_formatted_bn' => '১০,০০,০০,০০,০০,০০,০০০ টাকা',
                'commission' => '100,00,00,00,000',
                'commission_formatted' => '৳100,00,00,00,000',
                'commission_formatted_bn' => '১০০,০০,০০,০০,০০০ টাকা',
                'qualification' => 'Executive Director apex leadership',
                'qualification_bn' => 'শীর্ষ এক্সিকিউটিভ লিডারশিপ',
                'is_advanced' => true,
            ],
        ];
    }

    /**
     * Single Source of Truth: SBL Rank Configuration
     */
    public function getRankConfig(): array
    {
        return [
            'fme' => [
                'code' => 'FME',
                'order' => 1,
                'name' => 'Field Marketing Executive',
                'name_bn' => 'ফিল্ড মার্কেটিং এক্সিকিউটিভ',
                'shortName' => 'FME',
                'badge' => '🔰 Bronze Executive',
                'badge_bn' => '🔰 ব্রোঞ্জ এক্সিকিউটিভ',
                'color' => 'amber',
                'color_hex' => '#f59e0b',
                'requirement' => 'Direct Reference: 10 (Left 5 + Right 5)',
                'requirement_bn' => '১০ জন সরাসরি রেফারেল (লেফট ৫ + রাইট ৫)',
                'leftRequirement' => 5,
                'rightRequirement' => 5,
                'directRequirement' => 10,
                'teamRequirement' => 'None',
                'teamRequirement_bn' => 'প্রযোজ্য নয়',
                'pairRequirement' => 0,
                'cashReward' => 5000,
                'cashReward_formatted' => '৳5,000',
                'cashReward_formatted_bn' => '৫,০০০ টাকা',
                'status' => 'Verified Official',
                'verifiedAt' => 'September 2026',
                'description' => 'The foundational leadership rank in SBL. Earned by building a balanced binary entry team with active sponsors on both sides.',
                'description_bn' => 'SBL ক্যারিয়ারের প্রথম স্বীকৃত ধাপ। উভয় টিমে ভারসাম্যপূর্ণ রেফারেলের মাধ্যমে এই পদমর্যাদা ও এককালীন নগদ রিওয়ার্ড অর্জিত হয়।',
                'criteria_list' => [
                    ['en' => '10 direct verified sponsors (5 on Left, 5 on Right team)', 'bn' => '১০ জন সরাসরি ভেরিফায়েড স্পনসর (লেফটে ৫ ও রাইটে ৫ জন)'],
                    ['en' => 'Active membership account in good standing', 'bn' => 'সক্রিয় মেম্বারশিপ অ্যাকাউন্ট'],
                    ['en' => 'Binary placement structure activated', 'bn' => 'বাইনারি নেটওয়ার্ক স্ট্রাকচার সক্রিয়'],
                ],
            ],
            'sme' => [
                'code' => 'SME',
                'order' => 2,
                'name' => 'Senior Marketing Executive',
                'name_bn' => 'সিনিয়র মার্কেটিং এক্সিকিউটিভ',
                'shortName' => 'SME',
                'badge' => '🥈 Silver Executive',
                'badge_bn' => '🥈 সিলভার এক্সিকিউটিভ',
                'color' => 'slate',
                'color_hex' => '#64748b',
                'requirement' => '300 Binary Pair Rewards Matching',
                'requirement_bn' => '৩০০ বাইনারি পেয়ার রিওয়ার্ড ম্যাচিং',
                'leftRequirement' => 25,
                'rightRequirement' => 25,
                'directRequirement' => 10,
                'teamRequirement' => '300 Matching Pairs',
                'teamRequirement_bn' => '৩০০ ম্যাচিং পেয়ার',
                'pairRequirement' => 300,
                'cashReward' => 50000,
                'cashReward_formatted' => '৳50,000',
                'cashReward_formatted_bn' => '৫০,০০০ টাকা',
                'status' => 'Verified Official',
                'verifiedAt' => 'September 2026',
                'description' => 'Intermediate sales leadership rank achieved by scaling team binary sales volume to 300 matched pairs.',
                'description_bn' => 'টিমের উভয় পাশে ধারাবাহিক বিক্রয় ও কার্যক্রমের মাধ্যমে ৩০০ পেয়ার ম্যাচিং সম্পন্ন করে সিনিয়র লিডারশিপ অর্জন।',
                'criteria_list' => [
                    ['en' => 'Achieved 300 binary matching pairs in network', 'bn' => 'নেটওয়ার্কে মোট ৩০০ পেয়ার ম্যাচিং সম্পন্ন করা'],
                    ['en' => 'Maintain personal active membership', 'bn' => 'ব্যক্তিগত মেম্বারশিপ সক্রিয় রাখা'],
                    ['en' => 'Consistent dual-team sales momentum', 'bn' => 'উভয় টিমের ভারসাম্যপূর্ণ সেলস গতিশীলতা'],
                ],
            ],
            'pme' => [
                'code' => 'PME',
                'order' => 3,
                'name' => 'Promotional Marketing Executive',
                'name_bn' => 'প্রমোশনাল মার্কেটিং এক্সিকিউটিভ',
                'shortName' => 'PME',
                'badge' => '🥇 Gold Executive',
                'badge_bn' => '🥇 গোল্ড এক্সিকিউটিভ',
                'color' => 'yellow',
                'color_hex' => '#eab308',
                'requirement' => 'Team SME Leaders: Left 13, Right 7 (Total 20)',
                'requirement_bn' => 'টিমে SME লিডার: লেফট ১৩ জন, রাইট ৭ জন (মোট ২০ জন)',
                'leftRequirement' => 13,
                'rightRequirement' => 7,
                'directRequirement' => 10,
                'teamRequirement' => '20 SME Leaders (13 Left / 7 Right)',
                'teamRequirement_bn' => '২০ জন SME লিডার (লেফট ১৩ / রাইট ৭)',
                'pairRequirement' => 600,
                'cashReward' => 100000,
                'cashReward_formatted' => '৳1,00,000',
                'cashReward_formatted_bn' => '১,০০,০০০ টাকা',
                'status' => 'Verified Official',
                'verifiedAt' => 'September 2026',
                'description' => 'Advanced organizational leadership. Reached by mentoring and developing 20 SME leaders across your dual sales teams.',
                'description_bn' => 'উচ্চতর টিম লিডারশিপের পর্যায়। নিজের টিমের সদস্যদের সফল SME লিডার হিসেবে গড়ে তুলে এই মাইলফলক ও প্রাইজমানি লাভ।',
                'criteria_list' => [
                    ['en' => '13 qualified SME leaders developed in Left team', 'bn' => 'লেফট টিমে ১৩ জন কোয়ালিফায়েড SME তৈরি'],
                    ['en' => '7 qualified SME leaders developed in Right team', 'bn' => 'রাইট টিমে ৭ জন কোয়ালিফায়েড SME তৈরি'],
                    ['en' => 'Active participation in SBL promotional leadership', 'bn' => 'SBL প্রমোশনাল কার্যক্রমে সক্রিয় ভূমিকা'],
                ],
            ],
            'bme' => [
                'code' => 'BME',
                'order' => 4,
                'name' => 'Brand Marketing Executive',
                'name_bn' => 'ব্র্যান্ড মার্কেটিং এক্সিকিউটিভ',
                'shortName' => 'BME',
                'badge' => '💎 Diamond Executive',
                'badge_bn' => '💎 ডায়মন্ড এক্সিকিউটিভ',
                'color' => 'blue',
                'color_hex' => '#3b82f6',
                'requirement' => 'Team PME Leaders: Left 10, Right 5 (Total 15)',
                'requirement_bn' => 'টিমে PME লিডার: লেফট ১০ জন, রাইট ৫ জন (মোট ১৫ জন)',
                'leftRequirement' => 10,
                'rightRequirement' => 5,
                'directRequirement' => 10,
                'teamRequirement' => '15 PME Leaders (10 Left / 5 Right)',
                'teamRequirement_bn' => '১৫ জন PME লিডার (লেফট ১০ / রাইট ৫)',
                'pairRequirement' => 1500,
                'cashReward' => 500000,
                'cashReward_formatted' => '৳5,00,000',
                'cashReward_formatted_bn' => '৫,০০,০০০ টাকা',
                'status' => 'Verified Official',
                'verifiedAt' => 'September 2026',
                'description' => 'High-tier Brand Ambassador status rewarded with half a million BDT cash incentive for expanding multi-city leadership.',
                'description_bn' => 'ব্র্যান্ড অ্যাম্বাসেডর মর্যাদা। বৃহৎ পরিসরে টিম গড়ে ১৫ জন PME লিডার তৈরি করে ৫ লাখ টাকা নগদ রিওয়ার্ড অর্জন।',
                'criteria_list' => [
                    ['en' => '10 qualified PME leaders in Left team', 'bn' => 'লেফট টিমে ১০ জন PME লিডার তৈরি'],
                    ['en' => '5 qualified PME leaders in Right team', 'bn' => 'রাইট টিমে ৫ জন PME লিডার তৈরি'],
                    ['en' => 'National brand campaign representation', 'bn' => 'জাতীয় ব্র্যান্ডিং কার্যক্রমে সম্পৃক্ততা'],
                ],
            ],
            'gme' => [
                'code' => 'GME',
                'order' => 5,
                'name' => 'Global Marketing Executive',
                'name_bn' => 'গ্লোবাল মার্কেটিং এক্সিকিউটিভ',
                'shortName' => 'GME',
                'badge' => '👑 Crown Global',
                'badge_bn' => '👑 ক্রাউন গ্লোবাল',
                'color' => 'purple',
                'color_hex' => '#8b5cf6',
                'requirement' => 'Team BME Leaders: Left 8, Right 4 (Total 12)',
                'requirement_bn' => 'টিমে BME লিডার: লেফট ৮ জন, রাইট ৪ জন (মোট ১২ জন)',
                'leftRequirement' => 8,
                'rightRequirement' => 4,
                'directRequirement' => 10,
                'teamRequirement' => '12 BME Leaders (8 Left / 4 Right)',
                'teamRequirement_bn' => '১২ জন BME লিডার (লেফট ৮ / রাইট ৪)',
                'pairRequirement' => 3000,
                'cashReward' => 1000000,
                'cashReward_formatted' => '৳10,00,000',
                'cashReward_formatted_bn' => '১০,০০,০০০ টাকা',
                'status' => 'Verified Official',
                'verifiedAt' => 'September 2026',
                'description' => 'Executive international scale leadership. 1 Million BDT cash milestone for establishing cross-border enterprise teams.',
                'description_bn' => 'আন্তর্জাতিক স্তরের শীর্ষ এক্সিকিউটিভ সম্মাননা। টিম থেকে ১২ জন BME লিডার তৈরি করে ১০ লাখ টাকা ক্যাশ রিওয়ার্ড।',
                'criteria_list' => [
                    ['en' => '8 qualified BME leaders in Left team', 'bn' => 'লেফট টিমে ৮ জন BME লিডার তৈরি'],
                    ['en' => '4 qualified BME leaders in Right team', 'bn' => 'রাইট টিমে ৪ জন BME লিডার তৈরি'],
                    ['en' => 'Global dropshipping network guidance', 'bn' => 'গ্লোবাল নেটওয়ার্ক পরিচালনায় নেতৃত্ব'],
                ],
            ],
            'etd' => [
                'code' => 'ETD',
                'order' => 6,
                'name' => 'Executive Team Director',
                'name_bn' => 'এক্সিকিউটিভ টিম ডিরেক্টর',
                'shortName' => 'ETD',
                'badge' => '🏆 Director Ambassador',
                'badge_bn' => '🏆 ডিরেক্টর অ্যাম্বাসেডর',
                'color' => 'emerald',
                'color_hex' => '#10b981',
                'requirement' => 'Team GME Leaders: Left 7, Right 3 (Total 10)',
                'requirement_bn' => 'টিমে GME লিডার: লেফট ৭ জন, রাইট ৩ জন (মোট ১০ জন)',
                'leftRequirement' => 7,
                'rightRequirement' => 3,
                'directRequirement' => 10,
                'teamRequirement' => '10 GME Leaders (7 Left / 3 Right)',
                'teamRequirement_bn' => '১০ জন GME লিডার (লেফট ৭ / রাইট ৩)',
                'pairRequirement' => 6000,
                'cashReward' => 2000000,
                'cashReward_formatted' => '৳20,00,000',
                'cashReward_formatted_bn' => '২০,০০,০০০ টাকা',
                'status' => 'Verified Official',
                'verifiedAt' => 'September 2026',
                'description' => 'The highest pinnacle of SBL marketing achievement. 2 Million BDT cash reward recognizing apex organization leadership.',
                'description_bn' => 'SBL মার্কেটিং ক্যারিয়ারের সর্বোচ্চ শিখর। ১০ জন GME লিডার গড়ে ২০ লাখ টাকা বিশাল নগদ পুরস্কার ও ডিরেক্টর সম্মাননা।',
                'criteria_list' => [
                    ['en' => '7 qualified GME leaders in Left team', 'bn' => 'লেফট টিমে ৭ জন GME লিডার তৈরি'],
                    ['en' => '3 qualified GME leaders in Right team', 'bn' => 'রাইট টিমে ৩ জন GME লিডার তৈরি'],
                    ['en' => 'Board-level strategic advisory engagement', 'bn' => 'কৌশলগত উপদেষ্টা ও শীর্ষ নেতৃত্বের স্বীকৃতি'],
                ],
            ],
        ];
    }

    /**
     * Single Source of Truth: SBL 5 Earning Streams
     */
    public function getMarketingPlanConfig(): array
    {
        return [
            'streams' => [
                'spot' => [
                    'id' => 'spot',
                    'icon' => '⚡',
                    'name' => 'Spot / Direct Commission',
                    'name_bn' => 'স্পট / ডিরেক্ট রেফারেল কমিশন',
                    'rate' => '10%',
                    'rate_description' => '10% on Investment / Package Value',
                    'rate_description_bn' => 'প্যাকেজ মূল্যের ওপর ১০% তাৎক্ষণিক',
                    'description' => '10% marketing commission credited immediately to your cash wallet upon direct referral of any SBL membership or dropshipping package.',
                    'description_bn' => 'আপনার সরাসরি রেফারেন্সে কোনো মেম্বারশিপ বা প্যাকেজ অ্যাক্টিভেশনের সাথে সাথে মূলধনের ১০% ডিরেক্ট ক্যাশ ব্যালেন্সে জমা হয়।',
                    'eligibility' => 'All active associates (Starter, National, or International)',
                    'eligibility_bn' => 'সকল সক্রিয় মেম্বার (স্টার্টার, ন্যাশনাল বা ইন্টারন্যাশনাল)',
                    'example' => 'Example: National Package (৳1,20,000) generates ৳12,000 instant commission.',
                    'example_bn' => 'উদাহরণ: ন্যাশনাল প্যাকেজ (৳১,২০,০০০) রেফার করলে তাৎক্ষণিক ১২,০০০ টাকা কমিশন।',
                    'status' => 'Verified Official',
                ],
                'pair_reward' => [
                    'id' => 'pair_reward',
                    'icon' => '⚖️',
                    'name' => 'Pair Matching Reward',
                    'name_bn' => 'বাইনারি পেয়ার ম্যাচিং রিওয়ার্ড',
                    'rate' => '৳500 / Pair',
                    'rate_description' => '৳500 per binary pair (Max 100 PR/day = ৳50,000)',
                    'rate_description_bn' => 'প্রতি পেয়ারে ৫০০ টাকা (দৈনিক সর্বোচ্চ ১০০ পেয়ার = ৫০,০০০ টাকা)',
                    'description' => 'Binary matching bonus earned whenever team sales volume on your Left and Right teams match in a 1:1 ratio. Performance-driven based on active team volume.',
                    'description_bn' => 'আপনার বাইনারি নেটওয়ার্কের লেফট এবং রাইট টিমে ১:১ অনুপাতে টিম মেম্বার বা সেলস ভলিউম ম্যাচিং হলে প্রতি পেয়ারে ৫০০ টাকা করে রিওয়ার্ড অর্জিত হয়।',
                    'eligibility' => 'Active account with minimum 1 direct Left + 1 direct Right sponsor',
                    'eligibility_bn' => 'উভয় টিমে (লেফট ও রাইট) কমপক্ষে ১ জন করে ডিরেক্ট স্পনসর থাকতে হবে',
                    'example' => 'Example: 10 matched pairs on a day earn ৳5,000 matching bonus.',
                    'example_bn' => 'উদাহরণ: দিনে ১০ পেয়ার ম্যাচিং হলে ৫,০০০ টাকা ম্যাচিং বোনাস।',
                    'status' => 'Verified Official',
                ],
                'udr' => [
                    'id' => 'udr',
                    'icon' => '🌐',
                    'name' => 'Unity Development Commission (UDR)',
                    'name_bn' => 'ইউনিটি ডেভেলপমেন্ট কমিশন (UDR)',
                    'rate' => 'Up to 5%',
                    'rate_description' => 'Tier-based commission across 10 generations',
                    'rate_description_bn' => '১০ প্রজন্মব্যাপী স্তরভিত্তিক কমিশন',
                    'description' => 'Multi-tier development bonus paid from the active business volume generated across your extended downline network up to 10 generations.',
                    'description_bn' => 'আপনার রেফারেল নেটওয়ার্কের ১০ম প্রজন্ম পর্যন্ত সেলস ও পারফরম্যান্সের ওপর স্তরভিত্তিক নির্ধারিত কমিশন বণ্টন।',
                    'eligibility' => 'Qualified associates meeting rank criteria and team requirements',
                    'eligibility_bn' => 'র‍্যাংক ও টিম ক্রাইটেরিয়া অনুযায়ী কোয়ালিফায়েড মেম্বারগণ',
                    'example' => 'Gen 1: 10%, Gen 2: 2%, Gen 3-4: 1%, Gen 5: 0.5%, Gen 6-10: 0.1%.',
                    'example_bn' => '১ম প্রজন্ম: ১০%, ২য়: ২%, ৩য়-৪র্থ: ১%, ৫ম: ০.৫%, ৬ষ্ঠ-১০ম: ০.১%।',
                    'status' => 'Verified Official',
                ],
                'rank_reward' => [
                    'id' => 'rank_reward',
                    'icon' => '🎖️',
                    'name' => 'Rank Milestone Cash Rewards',
                    'name_bn' => 'র‍্যাংক অর্জন এককালীন ক্যাশ রিওয়ার্ড',
                    'rate' => '৳5k to ৳20 Lac',
                    'rate_description' => 'Up to ৳40,00,000 total career milestones',
                    'rate_description_bn' => 'সর্বোচ্চ ৪০ লাখ টাকা পর্যন্ত ক্যারিয়ার পুরস্কার',
                    'description' => 'Pre-set one-time milestone cash reward paid upon qualifying for each leadership rank (FME to ETD) based on team building and leader development.',
                    'description_bn' => 'FME (৫,০০০ টাকা) থেকে শুরু করে ETD (২০,০০,০০০ টাকা) পর্যন্ত প্রতিটি নির্ধারিত পদমর্যাদা অর্জনের সাথে সাথে এককালীন ক্যাশ অ্যাওয়ার্ড।',
                    'eligibility' => 'Rank milestone qualifiers with verified balanced dual-team structures',
                    'eligibility_bn' => 'উভয় টিমে নির্দিষ্ট লিডার ও সেলস কোটা পূরণকারী সদস্য',
                    'example' => 'FME = ৳5k, SME = ৳50k, PME = ৳1 Lac, BME = ৳5 Lac, GME = ৳10 Lac, ETD = ৳20 Lac.',
                    'example_bn' => 'FME = ৫ হাজার, SME = ৫০ হাজার, PME = ১ লাখ, BME = ৫ লাখ, GME = ১০ লাখ, ETD = ২০ লাখ।',
                    'status' => 'Verified Official',
                ],
                'refer_return' => [
                    'id' => 'refer_return',
                    'icon' => '📈',
                    'name' => 'Referral Weekly Return Share',
                    'name_bn' => 'রেফারেল সাপ্তাহিক শেয়ার (১০০ সপ্তাহ)',
                    'rate' => '0.25% / week',
                    'rate_description' => '0.25% weekly return for 100 weeks',
                    'rate_description_bn' => '১০০ সপ্তাহব্যাপী প্রতি সপ্তাহে ০.২৫% রিটার্ন',
                    'description' => 'Continuous weekly incentive distributed to the direct sponsor based on referred National and International project capital.',
                    'description_bn' => 'আপনার ডিরেক্ট রেফারেন্সে ন্যাশনাল বা আন্তর্জাতিক প্রজেক্ট ইনভেস্টমেন্ট সম্পন্ন হলে ১০০ সপ্তাহ ধরে প্রতি সপ্তাহে ০.২৫% বিশেষ বোনাস।',
                    'eligibility' => 'Direct sponsors of verified Dropshipping projects',
                    'eligibility_bn' => 'ভেরিফায়েড প্রজেক্ট স্পনসরকারী সদস্য',
                    'example' => 'Example: Sponsoring a National Project (৳1,00,000 Capital) earns ৳250/week for 100 weeks (Total ৳25,000).',
                    'example_bn' => 'উদাহরণ: ১ লাখ টাকার ক্যাপিটাল রেফার করলে প্রতি সপ্তাহে ২৫০ টাকা করে ১০০ সপ্তাহে মোট ২৫,০০০ টাকা।',
                    'status' => 'Verified Official',
                ],
            ],
            'project_returns' => [
                'national' => [
                    'name' => 'National Project',
                    'name_bn' => 'ন্যাশনাল ড্রপশিপিং প্রজেক্ট',
                    'package_amount' => 120000,
                    'package_amount_formatted' => '৳1,20,000',
                    'dev_fee' => 20000,
                    'core_capital' => 100000,
                    'core_capital_formatted' => '৳1,00,000',
                    'weekly_rate' => 0.0175,
                    'weekly_rate_str' => '1.75% / week',
                    'weekly_return' => 1750,
                    'weekly_return_formatted' => '৳1,750 / week',
                    'duration_weeks' => 100,
                    'total_return' => 175000,
                    'total_return_formatted' => '৳1,75,000',
                ],
                'international' => [
                    'name' => 'International Project',
                    'name_bn' => 'আন্তর্জাতিক ড্রপশিপিং প্রজেক্ট',
                    'package_amount' => 550000,
                    'package_amount_formatted' => '৳5,50,000',
                    'dev_fee' => 50000,
                    'core_capital' => 500000,
                    'core_capital_formatted' => '৳5,00,000',
                    'weekly_rate' => 0.02,
                    'weekly_rate_str' => '2.0% / week',
                    'weekly_return' => 10000,
                    'weekly_return_formatted' => '৳10,000 / week',
                    'duration_weeks' => 100,
                    'total_return' => 1000000,
                    'total_return_formatted' => '৳10,00,000',
                ],
            ],
            'udr_table' => [
                ['gen' => 'Gen 1', 'gen_bn' => '১ম প্রজন্ম', 'rate' => '10%', 'rate_num' => 0.10, 'basis' => 'Direct Sponsor Volume', 'basis_bn' => 'সরাসরি স্পনসর ভলিউম', 'note' => 'Paid on personal direct activations'],
                ['gen' => 'Gen 2', 'gen_bn' => '২য় প্রজন্ম', 'rate' => '2%', 'rate_num' => 0.02, 'basis' => '2nd Generation Team Volume', 'basis_bn' => '২য় প্রজন্মের টিম সেলস ভলিউম', 'note' => 'Requires 2 direct active sponsors'],
                ['gen' => 'Gen 3', 'gen_bn' => '৩য় প্রজন্ম', 'rate' => '1%', 'rate_num' => 0.01, 'basis' => '3rd Generation Team Volume', 'basis_bn' => '৩য় প্রজন্মের টিম সেলস ভলিউম', 'note' => 'Requires 4 direct active sponsors'],
                ['gen' => 'Gen 4', 'gen_bn' => '৪র্থ প্রজন্ম', 'rate' => '1%', 'rate_num' => 0.01, 'basis' => '4th Generation Team Volume', 'basis_bn' => '৪র্থ প্রজন্মের টিম সেলস ভলিউম', 'note' => 'FME leadership qualification'],
                ['gen' => 'Gen 5', 'gen_bn' => '৫ম প্রজন্ম', 'rate' => '0.5%', 'rate_num' => 0.005, 'basis' => '5th Generation Team Volume', 'basis_bn' => '৫ম প্রজন্মের টিম সেলস ভলিউম', 'note' => 'SME leadership qualification'],
                ['gen' => 'Gen 6–10', 'gen_bn' => '৬ষ্ঠ–১০ম প্রজন্ম', 'rate' => '0.1%', 'rate_num' => 0.001, 'basis' => '6th to 10th Generation Volume', 'basis_bn' => '৬ষ্ঠ থেকে ১০ম প্রজন্মের টিম সেলস ভলিউম', 'note' => 'PME to ETD leadership qualification'],
            ],
            'disclaimer' => 'All earnings, matching bonuses, and rank cash rewards are subject to active account status, dual-team placement criteria, and SBL business policies. Projections are illustrative and performance-dependent.',
            'disclaimer_bn' => 'সকল কমিশন, বাইনারি ম্যাচিং এবং র‍্যাংক প্রাইজমানি সক্রিয় অ্যাকাউন্টের শর্ত ও SBL ব্যবসায়িক নীতিমালা সাপেক্ষে অর্জিত হয়। কোনো আয় নিশ্চিত বা ফিক্সড সুদ নয়, সম্পূর্ণ পারফরম্যান্স ও টিম সেলস নির্ভর।',
        ];
    }
}
