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
     * Evaluate rank and formatting dynamically based on direct counts and total network counts.
     */
    public function evaluateRank(
        int $directLeft = 0,
        int $directRight = 0,
        int $totalLeftNetwork = 0,
        int $totalRightNetwork = 0,
        float $leftVolume = 0.0,
        float $rightVolume = 0.0
    ): array {
        $isFmeQualified = ($directLeft >= 5 && $directRight >= 5);

        $currentRankCode = 'Member';
        $currentRankName = 'Member';

        if ($isFmeQualified) {
            $currentRankCode = 'FME';
            $currentRankName = 'Field Marketing Executive';

            // Check higher ranks based on total downline network
            foreach (['ETD', 'GME', 'BME', 'PME', 'SME'] as $code) {
                $config = self::RANKS[$code];
                if ($totalLeftNetwork >= $config['left'] && $totalRightNetwork >= $config['right']) {
                    $currentRankCode = $code;
                    $currentRankName = $config['name'];
                    break;
                }
            }
        }

        // Format direct team count display: "3/5" or "5/5"
        $leftDisplay = "{$directLeft}/5";
        $rightDisplay = "{$directRight}/5";

        return [
            'rank_code' => $currentRankCode,
            'rank_name' => $currentRankName,
            'is_fme' => $isFmeQualified,
            'direct_left' => $directLeft,
            'direct_right' => $directRight,
            'total_left_network' => $totalLeftNetwork,
            'total_right_network' => $totalRightNetwork,
            'left_display' => $leftDisplay,
            'right_display' => $rightDisplay,
            'left_target' => 5,
            'right_target' => 5,
        ];
    }
}
