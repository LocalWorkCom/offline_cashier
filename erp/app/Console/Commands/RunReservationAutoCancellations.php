<?php

namespace App\Console\Commands;

use App\Jobs\ProcessReservationAutoCancellations;
use Illuminate\Console\Command;

class RunReservationAutoCancellations extends Command
{
    protected $signature = 'reservations:cancel-overdue';
    protected $description = 'Run the ProcessReservationAutoCancellations job to cancel overdue reservations';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Dispatching ProcessReservationAutoCancellations job...');
        ProcessReservationAutoCancellations::dispatch();
        $this->info('Job dispatched successfully.');
    }
}
