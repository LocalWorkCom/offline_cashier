<?php

namespace App\Traits;

use App\Models\Branch;
use App\Models\DishSize;
use App\Models\BranchMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait MostPopularTrait
{
    use BranchTrait;
    protected $lang;

    
    public function getMostPopular(Request $request)
    {
        $this->lang = $request->header('lang', 'ar');
        $branchesResponse = $this->listBranchAndNear($request);
        $branchesData = $branchesResponse->getData()->data; // LAT AND LONG OPTIONAL
        $branch = $branchesData->branch ?? null;
        $branchId = $branch->id ?? null;
        $popularDishes = getMostDishesOrdered($branchId);

        // Collect all popular dish IDs
        $popularDishIds = array_unique(Collection::make($popularDishes)->pluck('id')->toArray());
        //    $popularDishIds = [1, 5];
        //        dd($popularDishIds);

        $hasDefaultSize = DishSize::where('deleted_at', null)->where('default_size', 1)->get();

        $hasDefaultSizeIds = array_unique($hasDefaultSize->pluck('dish_id')->toArray());

        //    dd($hasDefaultSizeIds);

        // Fetch branch_menu IDs for popular dishes and branch ID
        $branchMenus = BranchMenu::whereIn('dish_id', $popularDishIds)
            ->where('branch_id', $branchId)
            ->where('deleted_at', null)
            ->get();
        //    dd($branchMenus);

        foreach ($popularDishes as $popularDish) {
            $branchMenu = $branchMenus->firstWhere('dish_id', $popularDish->id);
            if ($popularDish) {
                //            dd($popularDish->id);
                if (in_array($popularDish->id, $hasDefaultSizeIds)) {
                    //                dd(true);
                    $dishSize = DishSize::where('dish_id', $popularDish->id)
                        ->where('default_size', 1)
                        ->where('deleted_at', null)
                        ->first();
                    //                dd($branchMenu->price);

                    if ($dishSize) {
                        $price = $dishSize->price;
                    }
                }
                // Get the corresponding branch_menu ID
                $popularDish->id = $branchMenu->id ?? null;
                $popularDish->category_id = $branchMenu->branch_menu_category_id ?? null;
                $popularDish->price = $price ?? $branchMenu->price;
                $popularDish->is_active = $branchMenu->is_active ?? null;
                //            dd($branchMenu->price);



                // Hide unused fields
                $popularDish->makeHidden(['name_ar', 'description_ar', 'name_en', 'description_en']);

                // Transform language-specific fields
                if (request()->header('lang', 'ar') === 'en') {
                    $popularDish['name'] = $popularDish->name_en;
                    $popularDish['description'] = $popularDish->description_en;
                } else {
                    $popularDish['name'] = $popularDish->name_ar;
                    $popularDish['description'] = $popularDish->description_ar;
                }

                // Add transformations for boolean fields
                $popularDish->is_active = (bool) $popularDish->is_active;
                $popularDish->has_sizes = (bool) $popularDish->has_sizes;
                $popularDish->has_addon = (bool) $popularDish->has_addon;
            }
        }

        if (!$popularDishes) {
            return RespondWithBadRequestData($this->lang, 2);
        }

        return ResponseWithSuccessData($this->lang, $popularDishes, 1);
    }
}
// public function getMostPopular(Request $request)
//     {
//         $this->lang = $request->header('lang', 'ar');

//         try {
//             $branch = $this->getBranchFromRequest($request);
//             $popularDishes = $this->getPopularDishesWithDetails($branch);

//             return $popularDishes->isEmpty()
//                 ? RespondWithBadRequestData($this->lang, 2)
//                 : ResponseWithSuccessData($this->lang, $popularDishes, 1);
//         } catch (\Exception $e) {
//             return RespondWithBadRequestData($this->lang, 2);
//         }
//     }

//     protected function getBranchFromRequest(Request $request)
//     {
//         // First try to get the explicitly requested branch
//         if ($request->has('branch_id')) {
//             $branch = Branch::where('id', $request->branch_id)
//                 ->whereNull('deleted_at')
//                 ->first();
//             if ($branch) {
//                 return $branch;
//             }
//         }

//         // Only fallback to default/nearest branch if no branch_id was provided
//         // or if the provided branch_id wasn't found
//         $branchesResponse = $this->listBranchAndNear($request);
//         $branchesData = $branchesResponse->getData()->data;

//         return $branchesData->branch ?? null;
//     }

//     //   protected function getBranchFromRequest(Request $request)
//     //     {
//     //         $branchesResponse = $this->listBranchAndNear($request);
//     //         $branchesData = $branchesResponse->getData()->data;

//     //         return $branchesData->branch ?? null;
//     //     }
//     protected function getPopularDishesWithDetails($branch)
//     {
//         if (!$branch) {
//             return collect();
//         }

//         $popularDishes = collect(getMostDishesOrdered($branch->id));
//         $popularDishIds = $popularDishes->pluck('id')->unique();

//         $branchMenus = $this->getBranchMenus($branch->id, $popularDishIds);
//         $defaultSizeDishIds = $this->getDefaultSizeDishIds();

//         return $popularDishes->map(function ($dish) use ($branchMenus, $defaultSizeDishIds, $branch) {
//             $transformedDish = $this->transformDish($dish, $branchMenus, $defaultSizeDishIds);
//             if ($transformedDish) {
//                 // Always set most_popular to true for dishes returned by getMostDishesOrdered
//                 $transformedDish->most_popular = true;
//             }
//             return $transformedDish;
//         })->filter();
//     }

//     protected function getBranchMenus($branchId, $dishIds)
//     {
//         return BranchMenu::whereIn('dish_id', $dishIds)
//             ->where('branch_id', $branchId)
//             ->whereNull('deleted_at')
//             ->get();
//     }

//     protected function getDefaultSizeDishIds()
//     {
//         return DishSize::whereNull('deleted_at')
//             ->where('default_size', 1)
//             ->get()
//             ->pluck('dish_id')
//             ->unique()
//             ->toArray();
//     }

//     protected function transformDish($dish, $branchMenus, $defaultSizeDishIds)
//     {
//         $branchMenu = $branchMenus->firstWhere('dish_id', $dish->id);

//         if (!$branchMenu) {
//             return null;
//         }

//         $dish = $this->setDishBasicInfo($dish, $branchMenu);
//         $dish = $this->handleDishPricing($dish, $defaultSizeDishIds);
//         $dish = $this->transformLanguageFields($dish);
//         $dish = $this->transformBooleanFields($dish);

//         return $dish;
//     }

//     protected function setDishBasicInfo($dish, $branchMenu)
//     {
//         $dish->id = $branchMenu->id;
//         $dish->category_id = $branchMenu->branch_menu_category_id;
//         $dish->is_active = $branchMenu->is_active;
//         $dish->makeHidden(['name_ar', 'description_ar', 'name_en', 'description_en']);

//         return $dish;
//     }

//     protected function handleDishPricing($dish, $defaultSizeDishIds)
//     {
//         if (in_array($dish->id, $defaultSizeDishIds)) {
//             $defaultSize = DishSize::where('dish_id', $dish->id)
//                 ->where('default_size', 1)
//                 ->whereNull('deleted_at')
//                 ->first();

//             $dish->price = $defaultSize->price ?? $dish->price;
//         }

//         return $dish;
//     }

//     protected function transformLanguageFields($dish)
//     {
//         $isEnglish = $this->lang === 'en';

//         $dish->name = $isEnglish ? $dish->name_en : $dish->name_ar;
//         $dish->description = $isEnglish ? $dish->description_en : $dish->description_ar;

//         return $dish;
//     }

//     protected function transformBooleanFields($dish)
//     {
//         $dish->is_active = (bool) $dish->is_active;
//         $dish->has_sizes = (bool) $dish->has_sizes;
//         $dish->has_addon = (bool) $dish->has_addon;

//         return $dish;
//     }