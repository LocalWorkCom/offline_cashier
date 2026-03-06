<?php

namespace App\Console\Commands;

use App\Jobs\UpdateOfferStatuses;
use Illuminate\Console\Command;

class UpdateOfferStatusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the statuses of offers based on their dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        UpdateOfferStatuses::dispatch();
        $this->info('Offer statuses updated successfully.');
    }
}
