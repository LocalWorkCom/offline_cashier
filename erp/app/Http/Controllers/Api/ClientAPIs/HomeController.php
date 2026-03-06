<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Models\Branch;
use App\Models\Complaint;
use App\Models\Dish;
use App\Models\Offer;
use App\Services\KitchenServices\DishCategoryService;
use App\Traits\BranchTrait;
use App\Traits\DishCategoryTrait;
use App\Traits\MostPopularTrait;
use App\Traits\SliderTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\BranchMenu;
use App\Models\Order;

class HomeController extends Controller
{
    protected $dishCategoryService;
    use BranchTrait, MostPopularTrait, DishCategoryTrait, SliderTrait;
    public function __construct(DishCategoryService $dishCategoryService)
    {
        $this->dishCategoryService = $dishCategoryService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $user = auth('api')->user(); // Get authenticated user

        $branchesResponse = $this->listBranchAndNear($request);
        $branchesData = $branchesResponse->getData()->data; // LAT AND LONG OPTIONAL
        $branches = [
            'branch' => $branchesData->branch ?? null,
            'branches' => $branchesData->branches ?? null,
        ];

        // Check if the branch is provided and lat/long is available
        $branchId = $branches['branch']->id ?? null;

        $sliderResponse = $this->getSlider($request);
        $slider = $sliderResponse->getData()->data; //**************

        $menuResponse = $this->menuDishes($request);
        $menu = $menuResponse->getData()->data->categories; // changed to all categories
        $menu = array_map(function ($item) {
            unset($item->dishes); // Remove the dishes array
            return $item;
        }, $menu);

        $mostPopularResponse = $this->getMostPopular($request);
        $mostPopular = $mostPopularResponse->getData()->data; //5 //if auth return favourite

        // $isOffers = true;

        // if ($isOffers) {
        //     // Check if lat/long are provided and if branch is not null
        //     if ($branchId && isset($request->lat) && isset($request->long)) {
        //         // Filter offers by branch if lat/long is provided and branch is not null
        //         $offers = Offer::where(function ($query) use ($branchId) {
        //             if ($branchId) {
        //                 // Check if the offer is specific to the branch or is available in all branches
        //                 $query->where('branch_id', $branchId)
        //                     ->orWhere('branch_id', -1);  // Include offers available in all branches
        //             }
        //         })
        //             ->get(); // Get offers based on the branch filter

        //         // If offers exist, set them as the menu
        //         if ($offers->isNotEmpty()) {
        //             $menu = $offers;
        //         }
        //     } else {
        //         $offers = Offer::where('branch_id', -1)
        //             ->get(); // Get offers based on the branch filter
        //     }
        // }

        // if ($offers->isNotEmpty()) {
        //     $staticItem = (object) [
        //         'id' => 0,
        //         'parent_id' => null,
        //         'image_path' => 'https://erpsystem.testdomain100.online/front/AlKout-Resturant/SiteAssets/images/offers.png',
        //         'is_active' => true,
        //         'name' => $lang == 'en' ? 'Offers' : 'العروض',
        //         'description' => $lang == 'en' ? 'Offers Details' : 'تفاصيل العروض',
        //     ];
        //     $menu[] = $staticItem;
        //     $menu = collect($menu)
        //         ->partition(fn($item) => $item->id === 0)
        //         ->flatten()
        //         ->toArray();
        // }

        // Check if the user is authenticated and mark favorites for popular dishes
        $hasAddress = false; // Default value
        $lastOrder = false; // Default value
        if (CheckToken()) {
            $user = auth('api')->user(); // Get authenticated user

            if ($user) {
                // Check if the user has an address
                $hasAddress = DB::table('client_addresses')
                    ->where('user_id', $user->id)
                    ->exists();

                $mostPopular = collect($mostPopular)->map(function ($dish) use ($user) {
                    $isFavorite = DB::table('user_favorite_dishes')
                        ->where('user_id', $user->id)
                        ->where('dish_id', $dish->id)
                        ->get(); // Check if the dish is in user's favorites
                    if ($isFavorite->isNotEmpty()) {
                        $flag = true;
                    }

                    $dish->is_favorite = $flag ?? false;
                    return $dish;
                })->toArray();

                $lastOrder = Order::where('client_id', $user->id)
                    ->latest('created_at') // or 'id' if you prefer by ID
                    ->where('status', 'completed')
                    ->first();
            }
        } else {
            $mostPopular = collect($mostPopular)->map(function ($dish) {
                $dish->is_favorite = false;
                return $dish;
            })->toArray();
        }

        $isCompleted = false;

        if (!$lastOrder) {
            $isCompleted = true;
        } else {
            $hasRated = Complaint::latest('created_at')->where('order_id', $lastOrder->id)->exists();
            $isCompleted = $hasRated;
        }

        $data = [
            'branches' => $branches ?? null,
            'slider' => $slider ?? null,
            'menu' => $menu ?? null,
            'mostPopular' => $mostPopular ?? null,
            'has_address' => $hasAddress, // Add the has_address flag to the response
            'isRateOrder' => $isCompleted, // last order is rated
        ];

        if (empty($data['branches']) && empty($data['slider']) && empty($data['menu']) && empty($data['mostPopular'])) {
            return RespondWithBadRequestData($lang, 8); // Unauthorized response
        }

        if (empty($data['branches']) || empty($data['slider']) || empty($data['menu']) || empty($data['mostPopular'])) {
            foreach ($data as $key => $value) {
                if (is_array($value) && empty($value)) {
                    $data[$key] = null;
                }
            }
        }

        return ResponseWithSuccessData($lang, $data, 1);
    }

    public function showFavorites(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        try {
            // Validate token-based user authentication
            $token = CheckToken();
            $user = auth('api')->user();
            if (!$token) {
                return RespondWithBadRequestData($lang, 2); // Unauthorized response
            }

            $branchId = $request->query('branch_id') ?? Branch::where('is_default', 1)->value('id');

            // Query the user favorites with all dish details
            $userFavorites = DB::table('user_favorite_dishes')
                ->join('branch_menus', 'user_favorite_dishes.dish_id', '=', 'branch_menus.id')
                ->join('dishes', 'branch_menus.dish_id', '=', 'dishes.id')
                ->whereNull('branch_menus.deleted_at')
                ->where('branch_menus.is_active', 1)
                ->where('user_favorite_dishes.user_id', $user->id);

            // Apply branch filter if branch_id is provided
            if ($branchId) {
                $userFavorites->where('branch_menus.branch_id', $branchId);
            }

            $userFavorites = $userFavorites
                ->select(
                    'user_favorite_dishes.id as favorite_id',
                    'user_favorite_dishes.user_id',
                    'branch_menus.id as dish_id',
                    'dishes.id as real_dish_id',
                    'branch_menus.branch_id as branch_id',
                    'branch_menus.branch_menu_category_id as category_id',
                    'dishes.cuisine_id',
                    'branch_menus.price',
                    'dishes.image',
                    'dishes.name_en',
                    'dishes.name_ar',
                    'dishes.description_en',
                    'dishes.description_ar',
                    'dishes.is_active'
                )
                ->get()
                ->map(function ($favorite) use ($lang, $request) {
                    $dish = Dish::find($favorite->real_dish_id);
                    $price = $dish ? ($dish->sizeDefaults->price ?? $favorite->price) : $favorite->price;

                    $has_addon = DB::table('branch_menu_addons')
                        ->where('dish_id', $favorite->dish_id)
                        ->where('branch_id', $favorite->branch_id)
                        ->where('is_active', 1)
                        ->whereNull('deleted_at')
                        ->exists();

                    $has_size = DB::table('branch_menu_sizes')
                        ->where('dish_id', $favorite->dish_id)
                        ->where('branch_id', $favorite->branch_id)
                        ->where('is_active', 1)
                        ->whereNull('deleted_at')
                        ->exists();

                    $favorite->dish_name = $lang == 'en' ? $favorite->name_en : $favorite->name_ar;
                    $favorite->dish_description = $lang == 'en' ? $favorite->description_en : $favorite->description_ar;
                    $favorite->has_addon = $has_addon;
                    $favorite->has_size = $has_size;
                    $favorite->is_active = $favorite->is_active == 1;
                    $favorite->price = $price;

                    // Remove unnecessary columns
                    unset($favorite->name_en, $favorite->name_ar, $favorite->description_en, $favorite->description_ar, $favorite->branch_id, $favorite->real_dish_id);

                    // Fetch detailed dish info using menuDishesDetails
                    $customRequest = clone $request; // Clone to avoid modifying original request
                    $customRequest->dishId = $favorite->dish_id;
                    $dishDetails = $this->menuDishesDetails($customRequest);

                    if (is_object($dishDetails) && isset($dishDetails->original['data'])) {
                        return (object) $dishDetails->original['data']; // Replace favorite with detailed dish info
                    }

                    return $favorite;
                });
            $filteredCollection = $userFavorites->map(function ($item) {
                unset($item->sizes, $item->addon_categories);
                $item = $item->dish;
                unset($item['has_size'], $item['has_addon']);
                return $item;
            });
            // Handle empty favorites
            if ($filteredCollection->isEmpty()) {
                return ResponseWithSuccessData($lang, null, 1);
            }

            return ResponseWithSuccessData($lang, ['favorite_dishes' => $filteredCollection], 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2); // Generic error response
        }
    }

    public function storeFavorite(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        try {
            // Get the authenticated user
            $user = auth('api')->user();

            if (!$user) {
                return RespondWithBadRequestData($lang, 4); // Unauthorized response
            }

            // Get the dish_id from the request
            $dishId = $request->input('dish_id');

            // Validate that dish_id exists in the BranchMenu table
            $branchMenu = BranchMenu::where('id', $dishId)
                ->whereNull('deleted_at')
                ->where('is_active', 1)
                ->first();

            if (!$branchMenu) {
                return respondError('Validation Error', 400, [
                    'dish_id' => $lang == 'en' ? ['invalid number'] : ['رقم غير صحيح'],
                ]); // Invalid dish_id
            }

            // Check if the dish already exists in the user's favorites
            $existingFavorite = DB::table('user_favorite_dishes')
                ->where('user_id', $user->id)
                ->where('dish_id', $branchMenu->id)
                ->first();

            if ($existingFavorite) {
                // If the dish is already a favorite, delete it to unfavorite
                DB::table('user_favorite_dishes')
                    ->where('user_id', $user->id)
                    ->where('dish_id', $branchMenu->id)
                    ->delete();
                return ResponseWithSuccessData($lang, $lang == 'en' ? 'The dish has been removed from your favorites.' : 'تم إزالة الطبق من المفضلة.', 1);

                // return ResponseWithSuccessData($lang, __('offer.favorite_deleted'), 1); // Favorite removed
            }

            // Store the new favorite dish in the database
            DB::table('user_favorite_dishes')->insert([
                'user_id' => $user->id,
                'dish_id' => $branchMenu->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Return success response for favorite action
            return ResponseWithSuccessData($lang, $lang == 'en' ? 'The dish has been added to your favorites.' : 'تمت إضافة الطبق إلى المفضلة.', 1);

            // return ResponseWithSuccessData($lang, __('offer.favorite_added'), 1); // Favorite added successfully
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 8); // Generic error response
        }
    }

    public function deleteFavorite($id, Request $request)
    {
        $lang = $request->header('lang', 'ar');

        try {
            // Authenticate user using token
            $user = auth('api')->user();
            if (!$user) {
                return RespondWithBadRequestData($lang, 4); // Unauthorized response
            }

            // Check if the favorite dish exists for the given user
            $favorite = DB::table('user_favorite_dishes')
                ->where('user_id', $user->id)
                ->where('dish_id', $id) // Use the dish ID from the URL parameter
                ->first();

            if (!$favorite) {
                // If the dish is not in the user's favorites
                return RespondWithBadRequestData($lang, 8); // Favorite not found
            }

            // Delete the favorite dish record
            DB::table('user_favorite_dishes')
                ->where('user_id', $user->id)
                ->where('dish_id', $id)
                ->delete();

            // Return success response
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 8); // Generic error response
        }
    }
}
