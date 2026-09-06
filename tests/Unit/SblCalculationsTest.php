<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SblCalculationsTest extends TestCase
{
    /**
     * Test 120,000 BDT Package ROI Calculations.
     */
    public function test_national_120k_package_calculations(): void
    {
        $packagePrice = 120000;
        $developmentCharge = 20000;
        $baseInvestment = $packagePrice - $developmentCharge; // 100,000

        $weeklyReturn = 1750;
        $monthlyReturn = 7500;
        $weeks = 100;

        $this->assertEquals(100000, $baseInvestment);
        $this->assertEquals(175000, $weeklyReturn * $weeks);
        $this->assertEquals(1750, round($baseInvestment * 0.0175));
        $this->assertEquals(7500, (int)round(($weeklyReturn * 30) / 7));
    }

    /**
     * Test 550,000 BDT Package ROI Calculations.
     */
    public function test_international_550k_package_calculations(): void
    {
        $packagePrice = 550000;
        $developmentCharge = 50000;
        $baseInvestment = $packagePrice - $developmentCharge; // 500,000

        $weeklyReturn = 10000;
        $monthlyReturn = 42857;
        $weeks = 100;

        $this->assertEquals(500000, $baseInvestment);
        $this->assertEquals(1000000, $weeklyReturn * $weeks);
        $this->assertEquals(10000, round($baseInvestment * 0.02));
        $this->assertEquals(42857, (int)round(($weeklyReturn * 30) / 7));
    }

    /**
     * Test 10 Generation Referral Rates.
     */
    public function test_ten_generation_commission_distribution(): void
    {
        $generations = [
            1 => 3.50,
            2 => 1.50,
            3 => 1.00,
            4 => 0.50,
            5 => 0.50,
            6 => 0.50,
            7 => 0.50,
            8 => 0.50,
            9 => 0.50,
            10 => 0.50,
        ];

        $totalRate = array_sum($generations);
        $this->assertEquals(9.50, $totalRate);

        // Test commission on 100,000 base
        $base = 100000;
        $gen1Commission = round($base * ($generations[1] / 100));
        $this->assertEquals(3500, $gen1Commission);

        $gen2Commission = round($base * ($generations[2] / 100));
        $this->assertEquals(1500, $gen2Commission);
    }
}
