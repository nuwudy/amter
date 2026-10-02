<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tiers = [
            [
                'name' => '1 Day Pass',
                'duration_days' => 1,
                'price' => 10.00,
                'is_active' => true,
                'is_best_value' => false,
            ],
            [
                'name' => '1 Week Pass',
                'duration_days' => 7,
                'price' => 59.00,
                'is_active' => true,
                'is_best_value' => false,
            ],
            [
                'name' => '1 Month Fluency',
                'duration_days' => 30,
                'price' => 249.00,
                'is_active' => true,
                'is_best_value' => false,
            ],
            [
                'name' => '3 Months Mastery',
                'duration_days' => 90,
                'price' => 599.00,
                'is_active' => true,
                'is_best_value' => false,
            ],
            [
                'name' => '6 Months Pro Speaker',
                'duration_days' => 180,
                'price' => 999.00,
                'is_active' => true,
                'is_best_value' => false,
            ],
            [
                'name' => '1 Year All-Access',
                'duration_days' => 365,
                'price' => 1799.00,
                'is_active' => true,
                'is_best_value' => true,
            ],
        ];

        // Upsert the 6 progressive tiers by duration_days
        foreach ($tiers as $tier) {
            Plan::updateOrCreate(
                ['duration_days' => $tier['duration_days']],
                $tier
            );
        }

        // Deactivate any legacy plans that don't match the new 6 tiers
        Plan::whereNotIn('duration_days', [1, 7, 30, 90, 180, 365])
            ->update(['is_active' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-enable legacy plans if needed
        Plan::whereNotIn('duration_days', [1, 7, 30, 90, 180, 365])
            ->update(['is_active' => true]);
    }
};
