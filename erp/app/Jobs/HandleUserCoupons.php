<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserCoupon;
use Illuminate\Support\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleUserCoupons implements ShouldQueue
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

        $expiredCoupons = UserCoupon::whereHas('coupon', function ($query) use ($now) {
            $query->where('end_date', '<', $now);
        })->get();

        foreach ($expiredCoupons as $userCoupon) {
            $userCoupon->update(['status' => 'inactive']);
        }


        // Retrieve all users
        $users = User::all();

        // Loop through all users
        foreach ($users as $user) {
            // Retrieve all coupons for this user (assuming each user can have multiple coupons)
            $coupons = UserCoupon::where('user_id', $user->id)->get();

            // Loop through each coupon
            foreach ($coupons as $coupon) {
                // Use the helper function to check if the coupon was used by the user
                $check = CheckUserCouponUsage($coupon->coupon_id, $user->id);

                // If the coupon was used, change its status
                if ($check) {
                    $coupon->status = 'used';
                    $coupon->save();
                }
            }
        }
    }
}
