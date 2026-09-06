<?php

namespace App\Http\Controllers;

use App\Models\CommissionType;
use App\Models\InvestmentPlan;
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SblToolkitController extends Controller
{
    public function index(Request $request): View
    {
        $activeTab = $request->query('tab', 'packages'); // 'packages', 'compensation', 'counseling', 'calculator'

        $plans = InvestmentPlan::where('active', true)->get();
        $ranks = Rank::where('active', true)->orderBy('order')->get();
        $commissions = CommissionType::where('active', true)->get();

        // Market vs SBL 20k Dropshipping Comparison (PDF Page 4)
        $marketComparisons = [
            ['service' => 'ওয়েবসাইট ডেভেলপমেন্ট এবং ডোমেইন, হোস্টিং ক্রয়', 'market' => '৫০,০০০ থেকে ৩ লক্ষ টাকা'],
            ['service' => 'পণ্য সোর্সিং (বাছাইকৃত ও ভেরিফাইড মার্চেন্ট থেকে)', 'market' => '৫০,০০০ থেকে ১ লক্ষ টাকা'],
            ['service' => 'শপিফাই ই-কমার্স ওয়েবসাইট', 'market' => 'মাসে ৩০০০ টাকা'],
            ['service' => 'ফেসবুক পেজ সেটআপ', 'market' => '৫০০০ থেকে ১০০০০ টাকা'],
            ['service' => 'ভিডিও/ইমেজ বিজ্ঞাপন কন্টেন্ট তৈরি', 'market' => '২০০০ থেকে ৫০০০ টাকা'],
            ['service' => 'ফেসবুক এড ক্যাম্পেইন পরিচালনা', 'market' => '৫০,০০০ থেকে ১ লক্ষ টাকা'],
            ['service' => 'প্রোডাক্ট প্যাকেজিং ও ডেলিভারি সাপোর্ট', 'market' => '২০,০০০ থেকে ৫০,০০০ টাকা'],
            ['service' => 'পেজ মডারেশন এবং কাস্টমার সার্ভিস', 'market' => '১৫,০০০ থেকে ৩০,০০০ টাকা'],
            ['service' => 'বিক্রয় বিশ্লেষণ এবং রিপোর্টিং', 'market' => '৫০,০০০ থেকে ১ লক্ষ টাকা'],
            ['service' => 'পেমেন্ট এবং অর্ডার প্রসেসিং অটোমেশন', 'market' => '৫০০০ থেকে ১০০০০ টাকা'],
            ['service' => 'স্টক ম্যানেজমেন্ট', 'market' => '২০,০০০ থেকে ৫০,০০০ টাকা'],
            ['service' => 'অনলাইন মার্কেটিং (ফেসবুক বুস্ট) বাজেট', 'market' => '২ লক্ষ থেকে ৫ লক্ষ টাকা'],
        ];

        // 6-Month Trajectory (PDF Page 4)
        $growthTrajectory = [
            ['month' => '1st Month', 'ad_spend' => '-', 'audience' => 'Zero Audience', 'orders' => 'Zero Sells Record'],
            ['month' => '2nd Month', 'ad_spend' => '5*30 = 150 USD', 'audience' => '10k Audience', 'orders' => '50+ Orders'],
            ['month' => '3rd Month', 'ad_spend' => '5*30 = 150 USD', 'audience' => '30k Audience', 'orders' => '100+ Orders'],
            ['month' => '4th Month', 'ad_spend' => '5*30 = 150 USD', 'audience' => '50k Audience', 'orders' => '200+ Orders'],
            ['month' => '5th Month', 'ad_spend' => '5*30 = 150 USD', 'audience' => '100k Audience', 'orders' => '300+ Orders'],
            ['month' => '6th Month', 'ad_spend' => '5*30 = 150 USD', 'audience' => '200k Audience', 'orders' => '500+ Orders'],
        ];

        // Counseling 1: Investor vs Networker Comparison (PDF Page 6)
        $counselingPoints = [
            [
                'investor' => 'বিনিয়োগের নিরাপত্তা (নিরাপদ মূলধন ও সাপ্তাহিক রিটার্ন)',
                'networker' => 'একাধিক সেন্টার নেয়ার সুবিধা (মাল্টিপল আইডি ইনকাম)',
            ],
            [
                'investor' => 'লিগ্যাল এবং অথেন্টিক বিজনেস মডেল',
                'networker' => 'আগে আসার সুবিধা (টিম স্পিলওভার ও পজিশন বেনিফিট)',
            ],
            [
                'investor' => 'নিজের বিজনেস ব্র্যান্ডিং - স্থায়ী ইনকাম',
                'networker' => 'ডেইলি ৫০০০০ টাকা ইনকাম করার সুযোগ (Pair Reward)',
            ],
            [
                'investor' => 'ডিজিটাল এসেট এর কন্ট্রোলিং এবং ভ্যালু বৃদ্ধি',
                'networker' => 'স্থায়ী ইনকাম এর সুযোগ - টিম ওয়ার্ক ও প্যাসিভ আর্নিং',
            ],
        ];

        // 10-Generation Affiliate Matrix (PDF Page 7)
        $generationMatrix = [
            ['gen' => '1st', 'people' => '10', 'rate' => '10%', 'investment' => '10 × 10,000 = 1,00,000', 'commission' => '10,000'],
            ['gen' => '2nd', 'people' => '100', 'rate' => '2%', 'investment' => '100 × 10,000 = 10,00,000', 'commission' => '20,000'],
            ['gen' => '3rd', 'people' => '1,000', 'rate' => '1%', 'investment' => '1,000 × 10,000 = 1,00,00,000', 'commission' => '1,00,000'],
            ['gen' => '4th', 'people' => '10,000', 'rate' => '1%', 'investment' => '10,000 × 10,000 = 10,00,00,000', 'commission' => '10,00,000'],
            ['gen' => '5th', 'people' => '1,00,000', 'rate' => '0.5%', 'investment' => '1,00,000 × 10,000 = 1,00,00,00,000', 'commission' => '50,00,000'],
            ['gen' => '6th', 'people' => '10,00,000', 'rate' => '0.1%', 'investment' => '10,00,000 × 10,000 = 10,00,00,00,000', 'commission' => '1,00,00,000'],
            ['gen' => '7th', 'people' => '1,00,00,000', 'rate' => '0.1%', 'investment' => '1,00,00,000 × 10,000 = 1,00,00,00,00,000', 'commission' => '10,00,00,000'],
            ['gen' => '8th', 'people' => '10,00,00,000', 'rate' => '0.1%', 'investment' => '10,00,00,000 × 10,000 = 10,00,00,00,00,000', 'commission' => '1,00,00,00,000'],
            ['gen' => '9th', 'people' => '1,00,00,00,000', 'rate' => '0.1%', 'investment' => '1,00,00,00,000 × 10,000 = 1,00,00,00,00,00,000', 'commission' => '10,00,00,00,000'],
            ['gen' => '10th', 'people' => '10,00,00,00,000', 'rate' => '0.1%', 'investment' => '10,00,00,00,000 × 10,000 = 10,00,00,00,00,00,000', 'commission' => '100,00,00,00,000'],
        ];

        return view('toolkit.index', compact(
            'plans',
            'ranks',
            'commissions',
            'marketComparisons',
            'growthTrajectory',
            'counselingPoints',
            'generationMatrix',
            'activeTab'
        ));
    }
}

