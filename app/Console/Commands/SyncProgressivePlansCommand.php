<?php

namespace App\Console\Commands;

use App\Models\Plan;
use Illuminate\Console\Command;

class SyncProgressivePlansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plans:sync {--base=10 : Base daily rate in rupees} {--include-year : Whether to include 1 Year tier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync progressive fee reduction plans (1 Day, 1 Week, 1 Month, 3 Months, 6 Months) anchored to base daily rate';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $base = (float) $this->option('base');
        $includeYear = (bool) $this->option('include-year');

        $this->info("Syncing progressive plans with Base Daily Rate = ₹{$base}...");

        Plan::syncProgressivePlans($base, $includeYear);

        $plans = Plan::where('is_active', true)->orderBy('duration_days', 'asc')->get();

        $rows = [];
        foreach ($plans as $p) {
            $daily = round($p->price / $p->duration_days, 2);
            $linear = $p->duration_days * $base;
            $disc = $linear > $p->price ? round((($linear - $p->price) / $linear) * 100) : 0;
            $rows[] = [
                $p->name,
                $p->duration_days . ' days',
                '₹' . number_format($p->price, 0),
                '₹' . number_format($daily, 2) . '/day',
                $disc . '% off',
                $p->is_best_value ? 'Yes' : 'No',
            ];
        }

        $this->table(['Plan Name', 'Duration', 'Price', 'Per Day Cost', 'Discount', 'Best Value'], $rows);
        $this->info("All progressive plans successfully synced!");

        return Command::SUCCESS;
    }
}
