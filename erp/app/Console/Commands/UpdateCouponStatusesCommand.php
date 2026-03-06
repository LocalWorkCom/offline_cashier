<?php

namespace App\Console\Commands;

use App\Jobs\UpdateCouponStatuses;
use Illuminate\Console\Command;

class UpdateCouponStatusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupons:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the statuses of coupons based on their dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        UpdateCouponStatuses::dispatch();
        $this->info('coupon statuses updated successfully.');
    }
}
