<?php

namespace App\Http\Controllers\Api\CashierAPIs;

use App\Models\Branch;
use App\Models\BranchMenu;
use App\Models\BranchMenuCategory;
use App\Models\BranchMenuAddonCategory;
use App\Traits\BranchTrait;
use Illuminate\Http\Request;
use App\Traits\DishCategoryTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache; // ✅ ADD THIS
use App\Services\KitchenServices\DishCategoryService;   // ✅ ADD THIS
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MenuDishesController extends Controller
{
    use BranchTrait, DishCategoryTrait;

    protected $dishCategoryService;
    public function __construct(DishCategoryService $dishCategoryService)
    {
        $this->dishCategoryService = $dishCategoryService;
    }
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $branchId = $request->branchId;
        if ($branchId) {
            $branch = Branch::find($branchId);

            // If branch doesn't exist, return error
            if (!$branch) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            // If branch is not active, return error
            if (!$branch->is_active) {
                return respondError(__('branch_menu_category.NotActive'), 404);
            }
        }

        $branchesResponse = $this->listBranchAndNear($request);
        $branchesData = $branchesResponse->getData()->data; // LAT AND LONG OPTIONAL
        $branches = [
            'branch' => $branchesData->branch ?? null,
            'branches' => $branchesData->branches ?? null,
        ];

        // Check if the branch is provided and lat/long is available
        $branchId = $branches['branch']->id ?? null;

        $menuResponse = $this->menuDishes($request);
        // return $menuResponse;
        // dd($menuResponse->getData()->data);
        $menu = $menuResponse->getData()->data->categories ?? []; // changed to all categories

        $menu = array_map(function ($item) use ($request) {
            $item = (array)$item;
        //    dd($item);

            // Check if dishes are available and loop through them
            if (isset($item['dishes']) && is_array($item['dishes'])) {
                foreach ($item['dishes'] as &$dish) { // Use reference to modify the dish
                    // Call menuDishesDetails function and update the dish object
                    $request->dishId = $dish->id;
                    $dishDetails = $this->menuDishesDetails($request);
                    // Check if $dishDetails is an object and if it has the 'original' property
                    if (is_object($dishDetails) && isset($dishDetails->original)) {
                        $originalData = $dishDetails->original;
                        $originalData = (array) $originalData; // Convert to array

                        // Access 'data' from the originalData
                        if (isset($originalData['data'])) {
                            // Update the dish with 'data'
                            $dish = $originalData['data']; // Update dish with the 'data' from the original response
                            //                            dd($dish); // Dump the updated dish
                        }
                    }
                }
            }

              // ✅ أضف id_menu_integration لو موجود
              return $item;
        }, $menu);

        $data = collect($menu)->isEmpty() ? null :  $menu;

        return ResponseWithSuccessData($lang, $data, 1);
    }

    public function categories(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $menuResponse = $this->menuDishes($request);
        if ($menuResponse->getData()->data != null) {
            $menu = $menuResponse->getData()->data->categories; // changed to all categories
            $menu = array_map(function ($item) {
                unset($item->dishes); // Remove the dishes array
                return $item;
            }, $menu);
        } else {
            $menu = null;
        }

        if ($menu != null) {
            $data = $menu;
        } else {
            $data = null;
        }

        return ResponseWithSuccessData($lang, $data, 1);
    }



    public function getDishDetails(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $dishId = $request->dishId;

        // Validate that dishId is provided
        if (!$dishId) {
            return respondError(__('dish.dish_id_required'), 400);
        }

        try {
            // Get dish details using your existing menuDishesDetails function
            $dishDetailsResponse = $this->menuDishesDetails($request);

            // Check if the response is valid
            if (is_object($dishDetailsResponse) && isset($dishDetailsResponse->original)) {
                $originalData = $dishDetailsResponse->original;

                // Convert to array if it's an object
                if (is_object($originalData)) {
                    $originalData = (array)$originalData;
                }

                // Check if data exists in the response
                if (isset($originalData['data'])) {
                    $dishData = $originalData['data'];

                    // Convert to array if it's an object
                    if (is_object($dishData)) {
                        $dishData = (array)$dishData;
                    }

                    // Extract the dish information correctly
                    $dishInfo = isset($dishData['dish']) ? $dishData['dish'] : $dishData;
                    $sizes = isset($dishData['sizes']) ? $dishData['sizes'] : [];
                    $addonCategories = isset($dishData['addon_categories']) ? $dishData['addon_categories'] : [];

                    // If dishInfo is an object, convert to array for processing
                    if (is_object($dishInfo)) {
                        $dishInfo = (array)$dishInfo;
                    }

                    // Check if we have a nested dish object (the problematic case)
                    if (isset($dishInfo['dish']) && is_object($dishInfo['dish'])) {
                        // Extract the actual dish data from the nested structure
                        $actualDish = $dishInfo['dish'];
                        $sizes = isset($dishInfo['sizes']) ? $dishInfo['sizes'] : $sizes;
                        $addonCategories = isset($dishInfo['addon_categories']) ? $dishInfo['addon_categories'] : $addonCategories;
                    } else {
                        $actualDish = $dishInfo;
                    }

                    // Format the response correctly
                    $formattedResponse = [
                        'dish' => $actualDish,
                        'sizes' => $sizes,
                        'addon_categories' => $addonCategories
                    ];

                    return ResponseWithSuccessData($lang, $formattedResponse, 1);
                }
            }

            // If we reach here, the dish was not found or there was an error
            return respondError(__('dish.not_found'), 404);
        } catch (\Exception $e) {
            return respondError(__('dish.fetch_error'), 500);
        }
    }

    //dalia
    public function categoriesByBranch2(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        // $lang = in_array($lang, ['en', 'ar']) ? $lang : 'ar';

        $validator = Validator::make($request->all(), [
            'branchId' => ['required', 'integer', 'exists:branches,id'],
        ]);

        if ($validator->fails()) {
            $lang = $request->header('lang', 'ar'); // fallback to 'ar' if not provided
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $branchId = $this->resolveBranchId($request);
        $branch = Branch::find($branchId);
        if (!$branch->is_active) {
            return respondError(__('branch_menu_category.NotActive'), 403);
        }

        $cacheKey = $this->buildActiveCategoriesCacheKey($branchId, $lang);
        $categories = Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($branchId, $lang) {
                return BranchMenuCategory::query()
                    ->with([
                        'dish_categories:id,name_ar,name_en,description_ar,description_en,image_path',
                    ])
                    ->withCount([
                        'branchMenus as active_dishes_count' => function ($query) use ($branchId) {
                            $query->where('branch_id', $branchId)
                                ->where('is_active', 1)
                                ->whereNull('deleted_at')
                                ->whereHas('dish', function ($dishQuery) {
                                    $dishQuery->where('is_active', 1)
                                        ->whereNull('deleted_at');
                                });
                        },
                    ])
                    ->where('branch_id', $branchId)
                    ->where('is_active', 1)
                    ->whereHas('branchMenus', function ($subQuery) use ($branchId) {
                        $subQuery->where('branch_id', $branchId)
                            ->where('is_active', 1)
                            ->whereNull('deleted_at')
                            ->whereHas('dish', function ($dishQuery) {
                                $dishQuery->where('is_active', 1)
                                    ->whereNull('deleted_at');
                            });
                    })
                    ->orderBy('id')
                    ->get()
                    ->map(function (BranchMenuCategory $category) use ($lang) {
                        $dishCategory = $category->dish_categories;
                        if (!$dishCategory || $category->active_dishes_count === 0) {
                            return null;
                        }

                        return [
                            'id' => $category->id,
                            'name' => $lang === 'en' ? $dishCategory->name_en : $dishCategory->name_ar,
                            'description' => $lang === 'en' ? $dishCategory->description_en : $dishCategory->description_ar,
                            'image_path' => $dishCategory->image_path,
                            'dishes_count' => (int) $category->active_dishes_count,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->toArray();
            }
        );

        $categories = empty($categories) ? null : $categories;

        return ResponseWithSuccessData($lang, $categories, 1);
    }

    public function categoriesByBranch(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $validator = Validator::make($request->all(), [
            'branchId' => ['required', 'integer', 'exists:branches,id'],
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang === 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $branchId = $this->resolveBranchId($request);
        $branch = Branch::find($branchId);

        if (!$branch->is_active) {
            return respondError(__('branch_menu_category.NotActive'), 403);
        }

        // $categories = BranchMenuCategory::with(['dish_categories', 'branchMenus.dish'])
        //     ->withCount([
        //                 'branchMenus as active_dishes_count' => function ($query) use ($branchId) {
        //                     $query->where('branch_id', $branchId)
        //                         ->where('is_active', 1)
        //                         ->whereNull('deleted_at')
        //                         ->whereHas('dish', function ($dishQuery) {
        //                             $dishQuery->where('is_active', 1)
        //                                 ->whereNull('deleted_at');
        //                         });
        //                 },
        //             ])
        //     ->where('branch_id', $branchId)
        //     ->where('is_active', 1)
        //     ->whereHas('dish_categories', function ($query) use ($branchId) {
        //         $query->where('branch_id', $branchId)
        //             ->where('is_active', 1)
        //             ->whereNull('deleted_at');
        //     })
        //     ->whereHas('branchMenus', function ($query) use ($branchId) {
        //         $query->where('branch_id', $branchId)
        //             ->where('is_active', 1)
        //             ->whereNull('deleted_at')
        //             ->whereHas('dish', function ($dishQuery) {
        //                 $dishQuery->where('is_active', 1)
        //                         ->whereNull('deleted_at');
        //             });
        //     })
        //     ->orderBy('id')
        //     ->get()
        //     ->map(function (BranchMenuCategory $category) use ($lang) {
        //         $dishCategory = $category->dish_categories;

        //         if (!$dishCategory || $category->active_dishes_count === 0) {
        //             return null;
        //         }

        //         return [
        //             'id' => $category->id,
        //             'name' => $lang === 'en'
        //                 ? ($dishCategory->name_en ?? '')
        //                 : ($dishCategory->name_ar ?? ''),
        //             'description' => $lang === 'en'
        //                 ? ($dishCategory->description_en ?? '')
        //                 : ($dishCategory->description_ar ?? ''),
        //             'image_path' => $dishCategory->image_path ?? null,
        //             'dishes_count' => (int) $category->active_dishes_count,
        //         ];
        //     })
        //     ->filter()
        //     ->values()
        //     ->toArray();


        $activeDishFilter = function ($q) {
            $q->where('is_active', 1)->whereNull('deleted_at');
        };

        $categories = BranchMenuCategory::query()
            ->with([
                'dish_categories' => $activeDishFilter,
                'branchMenus' => function ($q) use ($branchId, $activeDishFilter) {
                    $q->where('branch_id', $branchId)
                    ->where($activeDishFilter)
                    ->whereHas('dish', $activeDishFilter);
                },
                'branchMenus.dish' => $activeDishFilter,
                'branchMenus.menusIntegrationDishs',
            ])
            ->withCount([
                'branchMenus as active_dishes_count' => function ($q) use ($branchId, $activeDishFilter) {
                    $q->where('branch_id', $branchId)
                    ->where($activeDishFilter)
                    ->whereHas('dish', $activeDishFilter);
                }
            ])
            ->where('branch_id', $branchId)
            ->where($activeDishFilter)
            ->whereHas('dish_categories', $activeDishFilter)
            ->whereHas('branchMenus', function ($q) use ($branchId, $activeDishFilter) {
                $q->where('branch_id', $branchId)
                ->where($activeDishFilter)
                ->whereHas('dish', $activeDishFilter);
            })
            ->orderBy('id')
            ->get()
            ->map(function (BranchMenuCategory $category) use ($lang) {
                $dishCategory = $category->dish_categories;

                if (!$dishCategory || $category->active_dishes_count == 0) {
                    return null;
                }

                $isIntegration = $category->branchMenus->contains(function ($menu) {
                    return $menu->menusIntegrationDishs && $menu->menusIntegrationDishs->isNotEmpty();
                });

                return [
                    'id' => $category->id,
                    'name' => $lang === 'en'
                        ? ($dishCategory->name_en ?? '')
                        : ($dishCategory->name_ar ?? ''),
                    'description' => $lang === 'en'
                        ? ($dishCategory->description_en ?? '')
                        : ($dishCategory->description_ar ?? ''),
                    'image_path' => $dishCategory->image_path,
                    'dishes_count' => (int) $category->active_dishes_count,
                    'is_integration' => $isIntegration ? true : false,
                ];
            })
            ->filter()
            ->values()
            ->toArray();


        return ResponseWithSuccessData($lang, $categories, 1);
    }


    public function categoryDishesByBranch(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $validator = Validator::make($request->all(), [
            'categoryId' => ['required', 'integer', 'exists:branch_menu_categories,id'],
            'branchId' => ['required', 'integer', 'exists:branches,id'],
        ]);

        if ($validator->fails()) {
            $lang = $request->header('lang', 'ar'); // fallback to 'ar' if not provided
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $categoryId = (int) $request->query('categoryId', 0);
        $branchId = $this->resolveBranchId($request);

        $branch = Branch::find($branchId);
        if (!$branch) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        if (!$branch->is_active) {
            return respondError(__('branch_menu_category.NotActive'), 403);
        }

        $currency_symbol = optional($branch->country)->currency_symbol ?? '';

        // Get the category
        $category = BranchMenuCategory::with([
            'dish_categories:id,name_ar,name_en,description_ar,description_en,image_path',
        ])
            ->where('id', $categoryId)
            ->where('branch_id', $branchId)
            ->where('is_active', 1)
            ->first();

        if (!$category || !$category->dish_categories) {
            return respondError(__('branch_menu_category.category_not_found') ?? 'Category not found for this branch.', 404);
        }

        // Get all dishes (menus) in that category
        $menuDetailsData = BranchMenu::query()
            ->where('branch_id', $branchId)
            ->where('branch_menu_category_id', $categoryId)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->with([
                // Main dish
                'dish' => fn($q) => $q->where('is_active', 1)
                                    ->whereNull('deleted_at'),
                // Sizes
                'branchMenuSizes' => fn($q) => $q->where('is_active', 1)
                                                ->where('branch_id', $branchId)
                                                ->whereNull('deleted_at')
                                                ->with([
                                                    'dishSizes' => fn($s) => $s->whereNull('deleted_at'),
                                                ]),
                // Addons
                'branchMenuAddons' => fn($q) => $q->where('is_active', 1)
                                                ->where('branch_id', $branchId)
                                                ->whereNull('deleted_at')
                                                ->with([
                                                    'dishAddons' => fn($qa) => $qa->whereNull('deleted_at')
                                                                                    ->with('addons'),
                                                    'branchMenuAddonCategories' => fn($qb) => $qb->where('is_active', 1)
                                                                                                ->whereNull('deleted_at')
                                                                                                ->with('addonCategories'),
                                                ]),
            ])
            ->orderByDesc('id')
            ->get();

        // Map dishes
        $dishes = $menuDetailsData->map(function (BranchMenu $menuDetails) use ($currency_symbol, $branchId) {
            if (!$menuDetails) {
                return null;
            }
            $dish = $menuDetails?->dish;

            if (!$dish) {
                return null;
            }
            $dishId = $dish->id;

            $BranchMenuAddonCategory = BranchMenuAddonCategory::Active()->where('branch_id', $branchId)
                ->with('branchMenuAddons', function ($query) use ($dishId, $branchId) {
                    return $query->where('branch_id', $branchId)->where('dish_id', $dishId);
                })
                ->get();

            return $dish = [
                'dish' => [
                    'id' => $menuDetails->id,
                    //'dish_id' => $menuDetails->dish_id,
                    'name' => $menuDetails?->dish?->name,
                    'description' => $menuDetails?->dish?->description,
                    'price' => doubleval($menuDetails?->dish?->has_sizes ? ($menuDetails?->dish?->menu_size_default ? $menuDetails?->dish?->menu_size_default?->price : 0) : $menuDetails->price),
                    // 'price' => $menuDetails->dish->menu_size_default,
                    'currency_symbol' => $currency_symbol,
                    'has_size' => $menuDetails?->dish?->has_sizes ? true : false,
                    'has_addon' => $menuDetails?->dish?->has_addon ? true : false,
                    'image' => $menuDetails?->dish?->image ?? null,
                    'share_link' => route('menu.details', $menuDetails->dish_id),
                    // 'is_favorites' => $is_favorites,
                    'mostOrdered' => checkDishExistMostOrderd($branchId, $menuDetails->dish_id),
                    'is_integration' => $menuDetails->is_menus_integration == 1 ? true : false,
                    'Id_menus_integrations' => $menuDetails->id_menus_integrations ?? [],
                ],
                'sizes' => $menuDetails?->dish?->has_sizes == true
                    ? $menuDetails?->branchMenuSizes?->map(function ($size) use ($currency_symbol) {
                        return [
                            'id' => $size->id,
                            'name' => $size?->dishSizes?->name,
                            'price' => doubleval($size->price),
                            'currency_symbol' => $currency_symbol,
                            'default_size' => $size?->dishSizes?->default_size ? true : false,
                        ];
                    })
                    : [],
                'addon_categories' => $menuDetails?->dish?->has_addon == true
                    ? $BranchMenuAddonCategory->filter(function ($addon_category) {
                        return $addon_category?->branchMenuAddons->count() > 0;
                    })->map(function ($addon_category) use ($currency_symbol, $dishId) {
                        return [
                            'id' => $addon_category->id,
                            'name' => $addon_category?->addonCategories?->name,
                            'min_addons' => minAddons($dishId, $addon_category->addon_category_id),
                            'max_addons' => maxAddons($dishId, $addon_category->addon_category_id),
                            'addons' => $addon_category?->branchMenuAddons?->map(function ($addon) use ($currency_symbol) {
                                return [
                                    'id' => $addon->id,
                                    'name' => $addon?->dishAddons?->addons?->name,
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
        })->filter()->values();

        $data = [
            'id' => $category->id,
            'name' => $lang === 'en' ? $category?->dish_categories?->name_en : $category?->dish_categories?->name_ar,
            'description' => $lang === 'en' ? $category?->dish_categories?->description_en : $category?->dish_categories?->description_ar,
            'image_path' => $category?->dish_categories?->image_path,
            'is_integration' => $dishes->contains(function ($dish) {
                return $dish['dish']['is_integration'] == true;
            }) ? true : false,
            "dishes" => $dishes
            ];

        return ResponseWithSuccessData($lang, $data, 1);
    }


    protected function resolveBranchId(Request $request): ?int
    {
        $branchId = $request->query('branchId', $request->branchId);
        if ($branchId) {
            return (int) $branchId;
        }

        $branchesResponse = $this->listBranchAndNear($request);
        if (!is_object($branchesResponse) || !method_exists($branchesResponse, 'getData')) {
            return null;
        }

        $branchesData = optional($branchesResponse->getData())->data ?? null;
        if (!$branchesData || !isset($branchesData->branch)) {
            return null;
        }

        return $branchesData->branch->id ?? null;
    }


    protected function buildActiveCategoriesCacheKey(int $branchId, string $lang): string
    {
        return sprintf('cashier:categories:snapshot:%d:%s', $branchId, $lang);
    }


}
