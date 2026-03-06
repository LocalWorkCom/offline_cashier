<?php

namespace App\Jobs;

use App\Models\Coupon;
use App\Models\UserCoupon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class UpdateCouponStatuses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now(); // Current time

        // Deactivate expired Coupon where end date has passed and is_active = 1
        Coupon::where('is_active', 1)
            ->where('end_date', '<', $now)
            ->update(['is_active' => 0]);

        // Activate Coupon where start date has arrived or passed and is_active = 0
        Coupon::where('is_active', 0)
            ->where('start_date', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->update(['is_active' => 1]);

        $expiredCoupons = UserCoupon::whereHas('coupon', function ($query) use ($now) {
            $query->where('end_date', '<', $now);
        })->get();

        foreach ($expiredCoupons as $userCoupon) {
            $userCoupon->update(['status' => 'inactive']);
        }
    }
}
