<?php

namespace App\Console\Commands;

use App\Jobs\CheckOfferDetails;
use Illuminate\Console\Command;

class CheckOfferDetailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:check-details';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'If any deleted dishes included in details, delete details.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        CheckOfferDetails::dispatch();
        $this->info('Offers details updated successfully.');
    }
}
