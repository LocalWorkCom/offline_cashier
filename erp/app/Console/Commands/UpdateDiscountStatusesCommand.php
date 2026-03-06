<?php

namespace App\Console\Commands;

use App\Jobs\UpdateDiscountsStatuses;
use Illuminate\Console\Command;

class UpdateDiscountStatusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discounts:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the statuses of discounts based on their dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        UpdateDiscountsStatuses::dispatch();
        $this->info('discount statuses updated successfully.');
    }
}
