<?php

namespace App\Console\Commands;

use App\Jobs\HandleUserCoupons;
use App\Jobs\UpdateCouponStatuses;
use Illuminate\Console\Command;

class HandleUserCouponCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-coupons-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user coupons status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        HandleUserCoupons::dispatch();
        $this->info('users coupons statuses updated successfully.');
    }
}
