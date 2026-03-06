<?php

namespace App\Jobs;

use App\Models\BranchMenu;
use App\Models\Dish;
use App\Models\Offer;
use App\Models\OfferDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckOfferDetails implements ShouldQueue
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
        $offerDetailDishIds = OfferDetail::where('deleted_at', null)->pluck('type_id')->unique();

        $branchIds = BranchMenu::whereIn('dish_id', $offerDetailDishIds)
            ->pluck('branch_id')
            ->unique();

        $offerBranchIds = Offer::whereHas('details', function ($query) use ($branchIds) {
            $query->whereIn('branch_id', $branchIds);
        })->get(); // Get the full offer records

        foreach ($offerBranchIds as $offer) {
            // If the offer is applied to all branches (branch_id == -1)
            if ($offer->branch_id == -1) {
                // Handle the case for all branches: check all related branch menus and their offer details
                foreach ($offer->details as $detail) {
                    $branchMenus = BranchMenu::where('dish_id', $detail->type_id)
                        ->whereIn('branch_id', $branchIds) // Check across all branch IDs
                        ->get();

                    foreach ($branchMenus as $branchMenu) {
                        // If the branchMenu is inactive, delete the offer detail
                        if ($branchMenu->is_active == 0) {
                            $detail->delete();
                        }
                    }
                }
            } else {
                // If the offer is specific to certain branch IDs
                foreach ($offer->details as $detail) {
                    // Check if the 'type_id' in OfferDetail matches 'dish_id' in BranchMenu
                    $branchMenu = BranchMenu::where('dish_id', $detail->type_id)
                        ->where('branch_id', $offer->branch_id)
                        ->first(); // Get the first match

                    // If a match is found, check if the item is inactive
                    if (($branchMenu && $branchMenu->is_active == 0) || ($branchMenu && $branchMenu->deleted_at != null)) {
                        // Logic for when the item is inactive (is_active = 0)
                        $detail->delete();
                    }
                }
            }
        }
    }
}
