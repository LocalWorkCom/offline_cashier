<?php

namespace App\Jobs;

use App\Models\Discount;
use App\Models\Dish;
use App\Models\DishDiscount;
use App\Models\Offer;
use App\Models\Slider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class UpdateSlider implements ShouldQueue
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
        // 1. Check for deleted dishes and remove associated sliders
        $deletedDishes = Dish::where('is_active',0)->pluck('id');
        $deletedDishes2 = Dish::onlyTrashed()->pluck('id');
        $allDeletedDishIds = $deletedDishes->merge($deletedDishes2);
        $allDeletedDishIdsArray = $allDeletedDishIds->toArray();
        Slider::where('flag', 'dish')->whereIn('dish_id', $allDeletedDishIdsArray)->delete();

        // 2. Check for deleted offers and remove associated sliders
        $deletedOffers = Offer::where('is_active',0)->pluck('id');
        $deletedOffers2 = Offer::onlyTrashed()->pluck('id');
        $allDeletedOfferIds = $deletedOffers->merge($deletedOffers2);
        $allDeletedOfferIdsArray = $allDeletedOfferIds->toArray();
        Slider::where('flag', 'offer')->whereIn('offer_id', $allDeletedOfferIdsArray)->delete();

        // 3. Check for deleted discounts and remove associated sliders
        $deletedDiscounts = Discount::where('is_active',0)->pluck('id');
        $deletedDiscounts2 = Discount::onlyTrashed()->pluck('id');
        $allDeletedDiscountIds = $deletedDiscounts->merge($deletedDiscounts2);
        $allDeletedDiscountIdsArray = $allDeletedDiscountIds->toArray();
        DishDiscount::whereIn('discount_id', $allDeletedDiscountIdsArray)->delete();

        $deletedDishDiscounts = DishDiscount::onlyTrashed()->pluck('id');
        Slider::where('flag', 'discount')->whereIn('discount_id', $deletedDishDiscounts)->delete();
    }
}
