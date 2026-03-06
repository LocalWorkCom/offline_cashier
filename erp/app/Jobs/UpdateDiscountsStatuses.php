<?php

namespace App\Jobs;

use App\Models\BranchMenu;
use App\Models\Discount;
use App\Models\DishDiscount;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateDiscountsStatuses implements ShouldQueue
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

        // Deactivate expired Discount where end date has passed and is_active = 1
        Discount::where('is_active', 1)
            ->where('end_date', '<', $now)
            ->update(['is_active' => 0]);

        // Activate Discount where start date has arrived or passed and is_active = 0
        Discount::where('is_active', 0)
            ->where('start_date', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->update(['is_active' => 1]);


        ////////////////////////////////////////////////////////////////
        /// delete dish discount if dish is not active in branch menu

        $dishes = BranchMenu::where('is_active', 0)->pluck('dish_id');
        $dishes2 = BranchMenu::onlyTrashed()->pluck('dish_id');
        $allDeletedDishIds = $dishes->merge($dishes2);
        $allDeletedDishIdsArray = $allDeletedDishIds->toArray();
        DishDiscount::whereIn('dish_id', $allDeletedDishIdsArray)->delete();
    }
}
