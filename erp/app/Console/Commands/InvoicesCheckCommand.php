<?php

namespace App\Console\Commands;

use App\Jobs\InvoicesCheck;
use Illuminate\Console\Command;

class InvoicesCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check on auto_run_time in CashierSetting model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        InvoicesCheck::dispatch();
        $this->info('invoices checked successfully.');
    }
}
