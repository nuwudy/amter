<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class ProgressivePlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sync 1 Day, 1 Week, 1 Month, 3 Months, 6 Months with ₹10 base rate
        Plan::syncProgressivePlans(baseDailyRate: 10.0, includeOneYear: false);
    }
}
