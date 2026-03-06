<?php

namespace App\Traits;

use App\Http\Resources\OfferResource;
use App\Models\Branch;
use App\Models\BranchMenu;
use App\Models\BranchMenuAddon;
use App\Models\BranchMenuAddonCategory;
use App\Models\BranchMenuSize;
use App\Models\Country;
use App\Models\Dish;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

trait DishCategoryTrait
{
    use BranchTrait, MostPopularTrait;
    public function menuDishes(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $categoryId = $request->query('categoryId', 'all');
        $validator = Validator::make($request->all(), [
            'branchId' => 'nullable|exists:branches,id'
        ]);
        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        } else {
            $branchId = $request->query('branchId');
        }
        $offers = $request->query('offers', 0);
        $searchName = $request->query('name', null); // Add search query parameter
        $orderBy = $request->query('orderBy', 'newest'); // Add orderBy query parameter ('newest' or 'most_ordered')

        if (!$branchId) {
            $branchesResponse = $this->listBranchAndNear($request);
            $branchesData = $branchesResponse->getData()->data; // LAT AND LONG OPTIONAL
            $branch = $branchesData->branch ?? null;
            $branchId = $branch->id ?? null;
        }

        if (!is_numeric($categoryId) && $categoryId !== 'all') {
            return respondError('Validation Error', 400, [
                'categoryId' => $lang == 'en' ? ['it must be a number or all'] : ['يجب ان تكون رقم او all'],
                'offers' => $lang == 'en' ? ['it must be 0 or 1'] : ['يجب ان تكون 0 او 1'],
            ]);
        }

        //        $nameColumn = ($lang === 'en') ? 'name_en' : 'name_ar';

        if ($branchId) {
            // Fetch most popular dishes once
            $mostPopularResponse = $this->getMostPopular($request);
            $mostPopular = collect($mostPopularResponse->getData()->data);
            $popularDishIds = $mostPopular->pluck('id')->toArray();
            //            dd($popularDishIds);

            $branch = Branch::find($branchId); // Get the branch data, including the country_id
            $country = Country::find($branch->country_id); // Fetch the country based on country_id
            $currencySymbol = $country ? $country->currency_symbol : 'ج . م'; // Fallback to a default currency symbol

            $subQuery = DB::table('order_details')
                ->select('dish_id', DB::raw('COUNT(*) as total_quantity'))
                ->groupBy('dish_id');

            // Scenario 1: If categoryId is greater than 0, fetch dishes for the category
            if (is_numeric($categoryId) && $categoryId > 0) {
                $menus = BranchMenu::join('branch_menu_categories', 'branch_menus.branch_menu_category_id', '=', 'branch_menu_categories.id')
                    ->join('dish_categories', 'branch_menu_categories.dish_category_id', '=', 'dish_categories.id') // Join with dish_categories
                    ->join('dishes', 'branch_menus.dish_id', '=', 'dishes.id') // Join with dishes table
                    ->leftJoinSub($subQuery, 'order_stats', function ($join) {
                        $join->on('dishes.id', '=', 'order_stats.dish_id');
                    })
                    ->where('branch_menu_categories.branch_id', $branchId)
                    ->where('branch_menus.branch_id', $branchId)
                    ->where('branch_menu_categories.is_active', 1)
                    ->where('branch_menus.is_active', 1)
                    ->where('dishes.is_active', 1)
                    ->where('dishes.deleted_at', null)
                    ->where('branch_menu_categories.id', $categoryId) // Filter by specific category ID
                    ->when($searchName, function ($query, $searchName) use ($lang) {
                        return $query->where('dishes.name_' . $lang, 'LIKE', '%' . $searchName . '%'); // Filter dishes by name using the search term
                    })
                    // In the menuDishes method, modify the query when orderBy is 'most_ordered'

                    ->when($orderBy === 'most_ordered', function ($query) use ($popularDishIds) {
                        if (!empty($popularDishIds)) {
                            $placeholders = implode(',', array_fill(0, count($popularDishIds), '?'));
                            $query->orderByRaw(
                                "FIELD(dishes.id, {$placeholders}) DESC",
                                $popularDishIds
                            );
                        }
                        return $query->orderByDesc('total_quantity');
                    })
                    ->when($orderBy === 'newest', function ($query) {
                        return $query->orderBy('branch_menus.created_at', 'desc'); // Order by created_at, descending (newest first)
                    })
                    ->select(
                        'branch_menus.dish_id AS real_dish_id',
                        'branch_menus.branch_menu_category_id',
                        'branch_menu_categories.id AS branch_menu_category_id',
                        'branch_menus.is_menus_integration As is_menus_integration',
                        'branch_menus.price AS price',
                        'dish_categories.id AS dish_category_id',
                        'dish_categories.name_' . $lang . ' AS dish_category_name',
                        'dish_categories.description_' . $lang . ' AS dish_category_description',
                        'dish_categories.image_path AS dish_category_image',
                        'branch_menus.id AS dish_id',
                        'dishes.cuisine_id AS cuisine_id',
                        'dishes.name_' . $lang . ' AS dish_name',
                        'dishes.description_' . $lang . ' AS dish_description',
                        'dishes.image AS dish_image',
                        'branch_menus.is_active AS is_active',
                        DB::raw('COALESCE(order_stats.total_quantity, 0) AS total_quantity') // Handle dishes with no orders
                    )
                    ->get()
                    ->groupBy('dish_category_id') // Group by category ID
                    ->map(function ($group) use ($lang, $currencySymbol, $popularDishIds, $branchId, $orderBy) {
                          $isIntegration = $group->contains(function ($item) {
                                $bm = BranchMenu::find($item->dish_id);
                                return !empty($bm->id_menus_integrations);
                            });


                        $dishes = $group->map(function ($item) use ($lang, $currencySymbol, $popularDishIds, $branchId) {
                            $branchMenuIntegration = BranchMenu::find($item->dish_id);
                            $idMenusIntegrations = $branchMenuIntegration ? $branchMenuIntegration->id_menus_integrations : [];

                            $dish = Dish::find($item->real_dish_id);
                            $price = $item->price; // Default price

                            if ($dish) {
                                $defaultSize = $dish->menu_size_default;
                                $price = $defaultSize ? $defaultSize->price : $item->price;
                            }
                            $has_addon = BranchMenuAddon::where('dish_id', $dish->id)
                                ->where('branch_id', $branchId)
                                ->where('is_active', 1)
                                ->whereNull('deleted_at')
                                ->exists();

                            $has_size = BranchMenuSize::where('dish_id', $dish->id)
                                ->where('branch_id', $branchId)
                                ->where('is_active', 1)
                                ->whereNull('deleted_at')
                                ->exists();

                            return [
                                'id' => $item->dish_id,
                                'category_id' => $item->branch_menu_category_id,
                                'cuisine_id' => $item->cuisine_id,
                                'price' => $price,
                                'has_addon' => $has_addon,
                                'has_size' => $has_size,
                                'image' => $item->dish_image,
                                'is_active' => $item->is_active == 1 ? true : false,
                                'is_favorite' => false,
                                'is_most_popular' => in_array($item->dish_id, $popularDishIds),
                                'currency_symbol' => $currencySymbol,
                                'name' => $item->{'dish_name'},
                                'description' => $item->{'dish_description'},
                                // 'total_quantity' => $item->total_quantity ?? 0 // Make sure this field exists
                                'id_menus_integrations' => $idMenusIntegrations
                            ];
                        })->sortByDesc(function ($dish) {
                            return [
                                $dish['is_most_popular'] ? 1 : 0,  // Most popular first
                                $dish['total_quantity'] ?? 0        // Then by order quantity - ADDED NULL COALESCING
                            ];
                        })->values()->toArray();

                        // Apply sorting when orderBy is 'most_ordered'
                        // Replace the existing sorting logic with this:
                        if ($orderBy === 'most_ordered') {
                            $dishes = collect($dishes)->sortByDesc(function ($dish) { // ENSURE $dishes IS A COLLECTION
                                return [
                                    $dish['is_most_popular'] ? 1 : 0,  // Most popular first
                                    $dish['total_quantity'] ?? 0        // Then by order quantity - ADDED NULL COALESCING
                                ];
                            })->values()->toArray(); // CONVERT BACK TO ARRAY IF NEEDED
                        }

                        return [
                            'id' => $group->first()->branch_menu_category_id,
                            'name' => $group->first()->{'dish_category_name'},
                            'description' => $group->first()->{'dish_category_description'},
                            'image_path' => $group->first()->dish_category_image,
                           'is_integration' => $isIntegration,
                            'dishes' => $dishes
                        ];
                    });
                // Check if user is authenticated
                if (CheckToken()) {
                    $user = auth('api')->user();
                    if ($user) {
                        // Fetch all favorite dish IDs for the user to avoid repeated queries
                        $userFavorites = DB::table('user_favorite_dishes')
                            ->where('user_id', $user->id)
                            ->pluck('dish_id')
                            ->toArray();

                        $menus = $menus->map(function ($menu) use ($userFavorites) {
                            $menu['dishes'] = collect($menu['dishes'])->map(function ($dish) use ($userFavorites) { // ENSURE $menu['dishes'] IS A COLLECTION
                                // Set 'is_favorite' to true if the dish ID is in user's favorites
                                $dish['is_favorite'] = in_array($dish['id'], $userFavorites);
                                return $dish;
                            })->toArray(); // CONVERT BACK TO ARRAY IF NEEDED
                            return $menu;
                        });
                    }
                }
                if ($orderBy == 'offers') {
                    $activeOffers = Offer::with('details')
                        ->whereHas('details')
                        ->where('branch_id', $branchId)
                        ->orWhere('branch_id', -1)
                        ->where('is_active', 1)
                        ->get()
                        ->map(function ($offer) {
                            // Assuming you want to add the translated name for each detail
                            $offer->details->each(function ($detail) {
                                if (request()->header('lang', 'ar') === 'en') {
                                    $detail->type_name = $detail->getTypeName('en'); // Add English name
                                } else {
                                    $detail->type_name = $detail->getTypeName('ar'); // Add Arabic name
                                }
                            });
                            return $offer;
                        }) ?? collect();

                    $activeOffers = OfferResource::collection($activeOffers);
                    $activeOffers = $activeOffers->filter(function ($offer) {
                        return $offer->details->isNotEmpty();
                    });

                    $data['offers'] = OfferResource::collection($activeOffers);
                }

                $data = $menus->first();
                if (!$data) {
                    return RespondWithBadRequestData($lang, 8);
                }
                if (empty($data)) {
                    $data = null;
                }

                return ResponseWithSuccessData($lang, $data, 1);
            }

            if ($categoryId === 'all') { // Assuming you're using language columns like name_ar, description_ar for Arabic
                $menus = BranchMenu::join('branch_menu_categories', 'branch_menus.branch_menu_category_id', '=', 'branch_menu_categories.id')
                    ->join('dish_categories', 'branch_menu_categories.dish_category_id', '=', 'dish_categories.id') // Join with dish_categories
                    ->join('dishes', 'branch_menus.dish_id', '=', 'dishes.id') // Join with dishes table
                    ->leftJoinSub($subQuery, 'order_stats', function ($join) {
                        $join->on('dishes.id', '=', 'order_stats.dish_id');
                    })
                    ->where('branch_menu_categories.branch_id', $branchId)
                    ->where('branch_menus.branch_id', $branchId)
                    ->where('branch_menu_categories.is_active', 1)
                    ->where('branch_menus.is_active', 1)
                    ->where('dishes.is_active', 1)
                    ->where('dishes.deleted_at', null)
                    ->when($searchName, function ($query, $searchName) use ($lang) {
                        return $query->where('dishes.name_' . $lang, 'LIKE', '%' . $searchName . '%'); // Filter dishes by name using the search term
                    })
                    // In the menuDishes method, modify the query when orderBy is 'most_ordered'

                    ->when($orderBy === 'most_ordered', function ($query) use ($popularDishIds) {
                        if (!empty($popularDishIds)) {
                            $placeholders = implode(',', array_fill(0, count($popularDishIds), '?'));
                            $query->orderByRaw(
                                "FIELD(dishes.id, {$placeholders}) DESC",
                                $popularDishIds
                            );
                        }
                        return $query->orderByDesc('total_quantity');
                    })
                    ->when($orderBy === 'newest', function ($query) {
                        return $query->orderBy('branch_menus.created_at', 'desc'); // Order by created_at, descending (newest first)
                    })
                    ->select(
                        'branch_menus.dish_id AS real_dish_id',
                        'branch_menus.branch_menu_category_id',
                        'branch_menu_categories.id AS branch_menu_category_id',
                        'branch_menus.is_menus_integration As is_menus_integration',
                        'branch_menus.price AS price',
                        'dish_categories.id AS dish_category_id',
                        'dish_categories.name_' . $lang . ' AS dish_category_name',
                        'dish_categories.description_' . $lang . ' AS dish_category_description',
                        'dish_categories.image_path AS dish_category_image',
                        'branch_menus.id AS dish_id',
                        'dishes.cuisine_id AS cuisine_id',
                        'dishes.name_' . $lang . ' AS dish_name',
                        'dishes.description_' . $lang . ' AS dish_description',
                        'dishes.image AS dish_image',
                        'branch_menus.is_active AS is_active',
                        DB::raw('COALESCE(order_stats.total_quantity, 0) AS total_quantity') // Handle dishes with no orders
                    )
                    ->get()
                    ->groupBy('dish_category_id') // Group by category ID
                    ->map(function ($group) use ($lang, $currencySymbol, $popularDishIds, $branchId) {
                         $isIntegration = $group->contains(function ($item) {
                                $bm = BranchMenu::find($item->dish_id);
                                return !empty($bm->id_menus_integrations);
                            });
                        return [
                            'id' => $group->first()->branch_menu_category_id,
                            'name' => $group->first()->{'dish_category_name'},
                            'description' => $group->first()->{'dish_category_description'},
                            'image_path' => $group->first()->dish_category_image,
                            'is_integration' => $isIntegration,
                            'dishes' => $group->map(function ($item) use ($lang, $currencySymbol, $popularDishIds, $branchId) {
                                $dish = Dish::find($item->real_dish_id);
                                $branchMenuIntegration = BranchMenu::find($item->dish_id);
                                $idMenusIntegrations = $branchMenuIntegration ? $branchMenuIntegration->id_menus_integrations : [];
                                //            dd($dish);
                                if ($dish) {
                                    $defaultSize = $dish->menu_size_default;
                                    // If default si ze exists, use its price, otherwise fall back to branch_menus price
                                    $price = $defaultSize ? $defaultSize->price : $item->price;
                                } else {
                                    $price = $item->price;  // Fall back to price from branch_menus if dish is null
                                }
                                $addon = BranchMenuAddon::where('dish_id', $dish->id)->where('branch_id', $branchId)->where('is_active', 1)->where('deleted_at', null)->first();
                                $size = BranchMenuSize::where('dish_id', $dish->id)->where('branch_id', $branchId)->where('is_active', 1)->where('deleted_at', null)->first();
                                if ($addon !== null) {
                                    $has_addon = true;
                                } else {
                                    $has_addon = false;
                                }
                                if ($size !== null) {
                                    $has_size = true;
                                } else {
                                    $has_size = false;
                                }
                                return [
                                    'id' => $item->dish_id,
                                    'category_id' => $item->branch_menu_category_id,
                                    'cuisine_id' => $item->cuisine_id,
                                    'price' => $price,
                                    'has_addon' => $has_addon,
                                    'has_size' => $has_size,
                                    'image' => $item->dish_image,
                                    'is_active' => $item->is_active == 1 ? true : false,
                                    'is_favorite' => false,
                                    'is_most_popular' => in_array($item->dish_id, $popularDishIds),
                                    'currency_symbol' => $currencySymbol,
                                    'name' => $item->{'dish_name'},  // Dish name based on lang
                                    'description' => $item->{'dish_description'},  // Dish description based on lang
                                    // 'total_quantity' => $item->total_quantity ?? 0 // Ensure this field exists
                                    'id_menus_integrations' => $idMenusIntegrations
                                ];
                            })->sortByDesc(function ($dish) {
                                return [
                                    $dish['is_most_popular'] ? 1 : 0,
                                    $dish['total_quantity'] ?? 0 // ADDED NULL COALESCING HERE
                                ];
                            })->values()->toArray(),
                        ];
                    });

                if (CheckToken()) {
                    $user = auth('api')->user(); // Get authenticated user
                    if ($user) {
                        $user = auth('api')->user(); // Get authenticated user
                        if ($user) {
                            // Fetch all favorite dish IDs for the user to avoid repeated queries
                            $userFavorites = DB::table('user_favorite_dishes')
                                ->where('user_id', $user->id)
                                ->pluck('dish_id')
                                ->toArray();

                            // Map over dishes to set 'is_favorite' based on user favorites
                            $menus = $menus->map(function ($menu) use ($userFavorites) {
                                $menu['dishes'] = array_map(function ($dish) use ($userFavorites) {
                                    // Set 'is_favorite' to true if the dish ID is in the user's favorites
                                    $dish['is_favorite'] = in_array($dish['id'], $userFavorites);
                                    return $dish;
                                }, $menu['dishes']);
                                return $menu;
                            });
                        }
                    }
                }
                $data['categories'] = $menus->values()->toArray();

                if (!$data) {
                    return RespondWithBadRequestData($lang, 8);
                }

                if (empty($data) || empty($data['categories'])) {
                    $data = null;
                }

                return ResponseWithSuccessData($lang, $data, 1);
            }

            // Scenario 3: If offers = 1, fetch active offers with details
            if ($categoryId == 0) {
                $activeOffers = Offer::with('details')
                    ->whereHas('details')
                    ->where('branch_id', $branchId)
                    ->orWhere('branch_id', -1)
                    ->where('is_active', 1)
                    ->get()
                    ->map(function ($offer) {
                        // Assuming you want to add the translated name for each detail
                        $offer->details->each(function ($detail) {
                            if (request()->header('lang', 'ar') === 'en') {
                                $detail->type_name = $detail->getTypeName('en'); // Add English name
                            } else {
                                $detail->type_name = $detail->getTypeName('ar'); // Add Arabic name
                            }
                        });
                        return $offer;
                    }) ?? collect();

                $activeOffers = OfferResource::collection($activeOffers);
                $activeOffers = $activeOffers->filter(function ($offer) {
                    return $offer->details->isNotEmpty();
                });

                $data['offers'] = $activeOffers;

                if (!$data) {
                    return RespondWithBadRequestData($lang, 8);
                }

                if (empty($data) || empty($data['offers'])) {
                    $data = null;
                }

                return ResponseWithSuccessData($lang, $data, 1);
            }
        }

        // Default fallback
        return RespondWithBadRequestData($lang, 2, 'Invalid scenario.');
    }

    public function menuDishesDetails(Request $request) // HEND
    {
        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);

            $menuId = $request->dishId;
            //$branchId = $request->branchId;

            $validateData = Validator::make($request->all(), [
                'dishId' => 'required|exists:branch_menus,id'
                // 'branchId' => 'required|integer|exists:branches,id'
            ]);

            // if ($validateData->fails()) {
            //      return RespondWithBadRequestWithData($validateData->errors());
            // }

            if (!$request->dishId && $validateData->fails()) {
                //return respondError('Validation Error.', 404, $validateData->errors());
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => __('validation.dataNotFound'),
                    'data' => null,
                    'errorData' => ['error' => __('validation.dataNotFound')]
                ], 200);
            }

            $menuDetails = BranchMenu::where('id', $request->dishId)->first();
            // $menuDetails = BranchMenu::Active()->where('id', $request->dishId)->first();
            if (!$menuDetails) {
                //return respondError('Validation Error.', 404, __('validation.NotExist'));
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => __('validation.dataNotFound'),
                    'data' => null,
                    'errorData' => ['error' => __('validation.dataNotFound')]
                ], 200);
            }

            $branchId = $menuDetails->branch_id;
            $dishId = $menuDetails->dish_id;
            $branch_details = Branch::where('id', $branchId)->first();
            $currency_symbol = ($branch_details->country) ? $branch_details->country->currency_symbol : "";
            //$menuDetails = BranchMenu::Active()->where('dish_id', $request->dishId)->where('branch_id', $request->branchId)->first();

            $BranchMenuSize = BranchMenuSize::Active()->where('dish_id', $dishId)
                ->where('branch_id', $branchId)
                ->get();

            $BranchMenuAddonCategory = BranchMenuAddonCategory::Active()->where('branch_id', $branchId)
                ->with('branchMenuAddons', function ($query) use ($dishId, $branchId) {
                    return $query->where('branch_id', $branchId)->where('dish_id', $dishId);
                })
                ->get();



            $BranchMenuAddon = BranchMenuAddon::Active()->where('dish_id', $dishId)
                ->where('branch_id', $branchId)
                ->get();

            $is_favorites = false;
            if (auth('api')->user()) {
                $user_favorites = DB::table('user_favorite_dishes')
                    ->where('user_id', auth('api')->user()->id)
                    ->where('dish_id', $menuDetails->id)
                    ->first();
                if ($user_favorites) {
                    $is_favorites = true;
                } else {
                    $is_favorites = false;
                }
            }

            $dish = [
                'dish' => [
                    'id' => $menuDetails->id,
                    //'dish_id' => $menuDetails->dish_id,
                    'name' => $menuDetails->dish->name,
                    'description' => $menuDetails->dish->description,
                    'price' => doubleval($menuDetails->dish->has_sizes ? ($menuDetails->dish->menu_size_default ? $menuDetails->dish->menu_size_default->price : 0) : $menuDetails->price),
                    // 'price' => $menuDetails->dish->menu_size_default,
                    'currency_symbol' => $currency_symbol,
                    'has_size' => $menuDetails->dish->has_sizes ? true : false,
                    'has_addon' => $menuDetails->dish->has_addon ? true : false,
                    'image' => $menuDetails->dish->image ?? null,
                    'share_link' => route('menu.details', $menuDetails->dish_id),
                    'is_favorites' => $is_favorites,
                    'mostOrdered' => checkDishExistMostOrderd($branchId, $menuDetails->dish_id),
                    'is_integration' => $menuDetails->is_menus_integration == 1 ? true : false,
                    'Id_menus_integrations' => $menuDetails->id_menus_integrations ?? [],
                ],
                'sizes' => $menuDetails->dish->has_sizes == true
                    ? $BranchMenuSize->map(function ($size) use ($currency_symbol) {
                        return [
                            'id' => $size->id,
                            'name' => $size->dishSizes->name_site,
                            'price' => doubleval($size->price),
                            'currency_symbol' => $currency_symbol,
                            'default_size' => $size->dishSizes->default_size ? true : false,
                        ];
                    })
                    : [],
                // 'addon_categories' => $menuDetails->dish->has_addon
                //     ? $BranchMenuAddonCategory->filter(function ($addon_category) {
                //         return $addon_category->branchMenuAddons->count() > 0;
                //     })->map(function ($addon_category) use ($currency_symbol, $dishId) {
                //         return [
                //             'id' => $addon_category->id,
                //             'name' => optional($addon_category->addonCategories)->name_site,
                //             'min_addons' => minAddons($dishId, $addon_category->addon_category_id),
                //             'max_addons' => maxAddons($dishId, $addon_category->addon_category_id),
                //             'addons' => $addon_category->branchMenuAddons->map(function ($addon) use ($currency_symbol) {
                //                 return [
                //                     'id' => $addon->id,
                //                     'name' => optional(optional($addon->dishAddons)->addons)->name_site,
                //                     'price' => (float) $addon->price,
                //                     'currency_symbol' => $currency_symbol,
                //                 ];
                //             })->values(),
                //         ];
                //     })->values()
                //     : [],

                'addon_categories' => $menuDetails->dish->has_addon == true
                    ? $BranchMenuAddonCategory->filter(function ($addon_category) {
                        return $addon_category->branchMenuAddons->count() > 0;
                    })->map(function ($addon_category) use ($currency_symbol, $dishId) {
                        return [
                            'id' => $addon_category->id,
                            'name' => $addon_category->addonCategories->name_site,
                            'min_addons' => minAddons($dishId, $addon_category->addon_category_id),
                            'max_addons' => maxAddons($dishId, $addon_category->addon_category_id),
                            'addons' => $addon_category->branchMenuAddons->map(function ($addon) use ($currency_symbol) {
                                return [
                                    'id' => $addon->id,
                                    'name' => $addon->dishAddons->addons->name_site,
                                    'price' => doubleval($addon->price),
                                    'currency_symbol' => $currency_symbol,
                                    // 'min' => $addon->dishAddons->addons->min_addons,
                                    // 'max' => $addon->dishAddons->addons->max_addons,
                                ];
                            }),
                        ];
                    })->values()
                    : [],
            ];

            return ResponseWithSuccessData($lang, $dish, 1);
        } catch (\Exception $e) {
            return respondError('An error occurred.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function menuDishesDetailsInEdit(Request $request) // HEND
    {
        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);
            $dish = $this->menuDishesDetails($request);
            $dish = $dish->getData(true);
            $orderDetails = json_decode($request->orderDetails, true);

            $dish['data']['order_id'] = $orderDetails['order_id'] ?? 0;
            $dish['data']['dish']['quantity'] = $orderDetails['quantity'] ?? 0;
            $dish['data']['dish']['item_id'] = $orderDetails['id'] ?? null;
            $dish['data']['dish']['note'] = $orderDetails['note'] ?? null;
            $dish['data']['dish']['dish_order'] = $orderDetails['dish_order'] ?? null;

            // Add checked status to sizes
            foreach ($dish['data']['sizes'] as $k_size => $size) {
                $size_details = getBranchSizeDetails($orderDetails['order']['branch_id'], $size['id'], 'api', 'first');
                if ($size_details->dish_size_id == $orderDetails['dish_size_id']) {
                    $dish['data']['sizes'][$k_size]['checked'] = true;
                } else {
                    $dish['data']['sizes'][$k_size]['checked'] = false;
                }
            }

            $addon_items = array_filter($orderDetails['dish_addons'], function ($item) {
                return $item['status'] === "pending";
            });

            $dishAddonIds = array_column(array_values($addon_items), 'dish_addon_id');
            if (count($dish['data']['addon_categories']) > 0) {
                foreach ($dish['data']['addon_categories'][0]['addons'] as $k_addon => $addon) {
                    $addon_details = getBranchAddonDetails($orderDetails['order']['branch_id'], $addon['id'], 'api', 'first');
                    if (in_array($addon_details->dish_addon_id, $dishAddonIds)) {
                        $dish['data']['addon_categories'][0]['addons'][$k_addon]['checked'] = true;
                    } else {
                        $dish['data']['addon_categories'][0]['addons'][$k_addon]['checked'] = false;
                    }
                }
            }

            return $dish;

            // return ResponseWithSuccessData($lang, $dish, 1);
        } catch (\Exception $e) {
            return respondError('An error occurred.', 500, ['error' => $e->getMessage()]);
        }
    }
}
