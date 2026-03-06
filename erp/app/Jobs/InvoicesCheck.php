<?php

namespace App\Jobs;

use App\Models\CashierSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InvoicesCheck implements ShouldQueue
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
        $currentTime = now()->format('H:i:s');

        $records = CashierSetting::where('auto_run_time', $currentTime)->get();

        foreach ($records as $record) {
            // Call function here for sending invoices
            // Then check on invoices sent or not
//            $this->runFunction($record);
        }

        Log::info('InvoicesCheckJob executed at ' . $currentTime);
    }
}
