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
        // Deactivate all legacy plans first
        Plan::query()->update(['is_active' => false]);

        // Sync the 5 progressive fee reduction tiers based on ₹10/day base rate
        // 1 Day (₹10), 1 Week (₹60), 1 Month (₹225), 3 Months (₹600), 6 Months (₹1,080)
        Plan::syncProgressivePlans(baseDailyRate: 10.0, includeOneYear: false);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
