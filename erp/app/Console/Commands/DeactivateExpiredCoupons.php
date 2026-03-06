<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coupon;

class DeactivateExpiredCoupons extends Command
{
    protected $signature = 'coupons:deactivate-expired';
    protected $description = 'Deactivate coupons that are no longer valid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $expiredCoupons = Coupon::where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->where('end_date', '<', $now)
                      ->orWhere(function ($subQuery) {
                          $subQuery->whereNotNull('usage_limit')
                                   ->whereColumn('count_usage', '>=', 'usage_limit');
                      });
            })
            ->update(['is_active' => false]);

        // Output result to the console
        $this->info("Expired coupons have been deactivated.");
    }
}
