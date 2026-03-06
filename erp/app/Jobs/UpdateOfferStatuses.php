<?php

namespace App\Jobs;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class UpdateOfferStatuses implements ShouldQueue
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
        $now = Carbon::now(); // Current time

        // Deactivate expired offers where end date has passed and is_active = 1
        Offer::where('is_active', 1)
            ->where('end_date', '<', $now)
            ->update(['is_active' => 0]);

        // Deactivate offers with no details
        Offer::where('is_active', 1)
            ->doesntHave('details')
            ->update(['is_active' => 0]);

        // Activate offers where start date has arrived or passed and is_active = 0
        Offer::where('is_active', 0)
            ->where('start_date', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->update(['is_active' => 1]);
    }
}
