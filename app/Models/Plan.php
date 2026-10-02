<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'duration_days',
        'is_active',
        'is_best_value',
    ];

    /**
     * Get progressive discount percentage for a given duration.
     * Balanced Growth Model:
     * - 1 Day (1 day): 0% discount
     * - 1 Week (7 days): ~14.3% discount (₹8.57/day)
     * - 1 Month (30 days): 25.0% discount (₹7.50/day)
     * - 3 Months (90 days): 33.3% discount (₹6.67/day)
     * - 6 Months (180 days): 40.0% discount (₹6.00/day)
     * - 1 Year (365 days): 45.2% discount (₹5.48/day)
     */
    public static function getDiscountPercentageForDays(int $days): float
    {
        if ($days <= 1) return 0.0;
        if ($days <= 7) return 14.2857; // 14%
        if ($days <= 30) return 25.0; // 25%
        if ($days <= 90) return 33.3333; // 33.3%
        if ($days <= 180) return 40.0; // 40%
        return 45.2328; // 45.2%
    }

    /**
     * Calculate progressive fee based on duration and base daily rate.
     */
    public static function calculateProgressivePrice(int $days, float $baseDailyRate = 10.0): float
    {
        if ($days <= 0) return 0.0;

        $discount = self::getDiscountPercentageForDays($days);
        $effectiveDaily = $baseDailyRate * (1 - ($discount / 100));
        $total = $days * $effectiveDaily;

        return round($total);
    }

    /**
     * Sync progressive plans based on a base daily rate (e.g. ₹5, ₹7, ₹10).
     */
    public static function syncProgressivePlans(float $baseDailyRate = 10.0, bool $includeOneYear = false): void
    {
        $tiers = [
            [
                'name' => '1 Day Pass',
                'duration_days' => 1,
                'price' => self::calculateProgressivePrice(1, $baseDailyRate),
                'is_active' => true,
                'is_best_value' => false,
            ],
            [
                'name' => '1 Week Pass',
                'duration_days' => 7,
                'price' => self::calculateProgressivePrice(7, $baseDailyRate),
                'is_active' => true,
                'is_best_value' => false,
            ],
            [
                'name' => '1 Month Fluency',
                'duration_days' => 30,
                'price' => self::calculateProgressivePrice(30, $baseDailyRate),
                'is_active' => true,
                'is_best_value' => false,
            ],
            [
                'name' => '3 Months Mastery',
                'duration_days' => 90,
                'price' => self::calculateProgressivePrice(90, $baseDailyRate),
                'is_active' => true,
                'is_best_value' => false,
            ],
            [
                'name' => '6 Months Pro',
                'duration_days' => 180,
                'price' => self::calculateProgressivePrice(180, $baseDailyRate),
                'is_active' => true,
                'is_best_value' => true,
            ],
        ];

        if ($includeOneYear) {
            $tiers[] = [
                'name' => '1 Year All-Access',
                'duration_days' => 365,
                'price' => self::calculateProgressivePrice(365, $baseDailyRate),
                'is_active' => true,
                'is_best_value' => true,
            ];
            $tiers[4]['is_best_value'] = false;
        }

        $activeDays = collect($tiers)->pluck('duration_days')->all();

        // Deactivate all legacy plans that are not in the new progressive tiers
        self::whereNotIn('duration_days', $activeDays)->update(['is_active' => false]);

        foreach ($tiers as $tier) {
            self::updateOrCreate(
                ['duration_days' => $tier['duration_days']],
                $tier
            );
        }
    }
}
