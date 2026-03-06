<?php

namespace App\Console\Commands;

use App\Jobs\BeforeArrivalAlert;
use Illuminate\Console\Command;

class BeforeArrivalAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservation:before-arrival-alert-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alerting the customer service before arrival time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        BeforeArrivalAlert::dispatch();
        $this->info('Customer service notified of before arrival successfully.');
    }
}
