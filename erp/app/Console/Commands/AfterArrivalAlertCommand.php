<?php

namespace App\Console\Commands;

use App\Jobs\AfterArrivalAlert;
use Illuminate\Console\Command;

class AfterArrivalAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservation:after-arrival-alert-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alerting the customer service after arrival time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        AfterArrivalAlert::dispatch();
        $this->info('Customer service notified of after arrival successfully.');
    }
}
