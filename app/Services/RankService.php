<?php

namespace App\Services;

class RankService
{
    /**
     * Configurable rank thresholds.
     */
    public const RANKS = [
        'ETD' => ['name' => 'Executive Top Director', 'left' => 10000, 'right' => 10000, 'order' => 7],
        'GME' => ['name' => 'Global Marketing Executive', 'left' => 2000, 'right' => 2000, 'order' => 6],
        'BME' => ['name' => 'Branch Marketing Executive', 'left' => 500, 'right' => 500, 'order' => 5],
        'PME' => ['name' => 'Premier Marketing Executive', 'left' => 100, 'right' => 100, 'order' => 4],
        'SME' => ['name' => 'Senior Marketing Executive', 'left' => 25, 'right' => 25, 'order' => 3],
        'FME' => ['name' => 'Field Marketing Executive', 'left' => 5, 'right' => 5, 'order' => 2],
        'Member' => ['name' => 'Member', 'left' => 0, 'right' => 0, 'order' => 1],
    ];

    /**
     * Evaluate rank and formatting dynamically based on qualified left/right team counts.
     * Note: Rank never modifies tree structure or limits tree growth.
     */
    public function evaluateRank(int $qualifiedLeft, int $qualifiedRight, float $leftVolume = 0.0, float $rightVolume = 0.0): array
    {
        $currentRankCode = 'Member';
        $currentRankName = 'Member';
        $nextRankCode = 'FME';
        $nextRankLeftTarget = 5;
        $nextRankRightTarget = 5;

        foreach (self::RANKS as $code => $config) {
            if ($code === 'Member') {
                continue;
            }

            if ($qualifiedLeft >= $config['left'] && $qualifiedRight >= $config['right']) {
                $currentRankCode = $code;
                $currentRankName = $config['name'];
                break;
            }
        }

        // Determine targets for display
        $isFmeQualified = ($qualifiedLeft >= 5 && $qualifiedRight >= 5);

        // Format team count display: "3/5" before FME, "5", "6", "17", "100+" after FME
        $leftDisplay = $qualifiedLeft < 5 ? "{$qualifiedLeft}/5" : "{$qualifiedLeft}";
        $rightDisplay = $qualifiedRight < 5 ? "{$qualifiedRight}/5" : "{$qualifiedRight}";

        return [
            'rank_code' => $currentRankCode,
            'rank_name' => $currentRankName,
            'is_fme' => $isFmeQualified,
            'left_count' => $qualifiedLeft,
            'right_count' => $qualifiedRight,
            'left_display' => $leftDisplay,
            'right_display' => $rightDisplay,
            'left_target' => 5,
            'right_target' => 5,
        ];
    }
}
