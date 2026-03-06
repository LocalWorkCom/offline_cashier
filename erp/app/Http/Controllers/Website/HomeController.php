<?php

namespace App\Http\Controllers\Website;

use App\Models\Complaint;
use App\Models\FAQ;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Rate;
use App\Models\Offer;
use App\Models\Branch;
use App\Models\Slider;
use App\Models\Discount;
use App\Models\BranchMenu;
use App\Models\BranchTime;
use App\Models\UserCoupon;
use App\Models\DishDiscount;
use App\Models\ReturnPolicy;
use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;
use App\Models\BranchMenuSize;
use App\Models\TermsAndCondition;
use App\Models\BranchMenuCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\StaticPageResource;
use App\Models\Notification;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the latitude and longitude from cookies
        // $userLat = $request->cookie('latitude') ?? ($_COOKIE['latitude'] ?? null);
        // $userLon = $request->cookie('longitude') ?? ($_COOKIE['longitude'] ?? null);
        $branchId = request()->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);

        if (!$branchId) {
            $branchId = getDefaultBranch();
        }
        // $currency = $branchId ?? Branch::find($branchId)->country->currency_symbol;
        $sliders = Slider::all();
        // $branches = Branch::all();
        $discounts = DishDiscount::with(['dish' => function ($query) {
            $query->select('id', 'name_ar', 'name_en', 'image');
        }])
            ->with('discount')
            ->whereHas('discount', function ($query) {
                $query->where('is_active', 1); // Only active discounts
            })
            ->join('branch_menus', 'branch_menus.dish_id', '=', 'dish_discount.dish_id')
            ->join('branches', 'branches.id', '=', 'branch_menus.branch_id')
            ->join('countries', 'countries.id', '=', 'branches.country_id')
            ->join('branch_discount', function ($join) use ($branchId) {
                $join->on('branch_discount.discount_id', '=', 'dish_discount.discount_id')
                    ->where('branch_discount.branch_id', $branchId);
            })
            ->select('dish_discount.*', 'countries.currency_symbol', 'branch_discount.branch_id')
            ->get();


        $discounts = $discounts->isNotEmpty() ? $discounts : null;

        $popularDishes = getMostDishesOrdered($branchId, 5);
        $menuCategories = BranchMenuCategory::with([
            'dish_categories',
            'branchMenus' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->where('is_active', 1)
                    ->with(['dish' => function ($dishQuery) {
                        $dishQuery->where('is_active', 1);
                    }]);
            },
        ])
            ->whereHas('branchMenus', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->where('is_active', 1);
            })
            ->where('is_active', 1)
            ->get()->unique();

        $userFavorites = [];
        $notifications = [];
        if (Auth::guard('client')->check()) {
            $userId = Auth::guard('client')->id();
            $userFavorites = DB::table('user_favorite_dishes')
                ->where('user_id', $userId)
                ->pluck('dish_id')
                ->toArray();

            $notifications = Notification::where('user_id', $userId)
                ->where('status', 0) // Unread notifications
                ->where('type', 'reservation_cancelled')
                ->orderBy('created_at', 'desc')
                ->get();

            $lastOrder = Order::where('client_id', $userId)
                ->orderBy('created_at', 'desc')
                ->where('status', 'completed')
                ->first();
            if ($lastOrder) {
                $rate = Complaint::where('order_id', $lastOrder->id)->first() ?? false;
                $orderDetails = OrderDetail::where('order_id', $lastOrder->id)->get();
            } else {
                $rate = false;
                $orderDetails = null;
            }
        } else {
            $rate = null;
            $lastOrder = null;
            $orderDetails = null;
        }

        $locale = app()->getLocale();
        $branch = Branch::with('country')->find($branchId);
        $currencySymbol = $branch?->country?->currency_symbol ?? 'ج.م';

        // Check if branch is open
        $isOpen = false;
        $currentTime = now();
        $currentDay = $currentTime->dayOfWeek;

        $branchTime = BranchTime::where('branch_id', $branchId)
            ->where('day', $currentDay)
            ->where('is_active', 1)
            ->first();

        if ($branchTime) {
            $openingTime = \Carbon\Carbon::parse($branchTime->opening_hour);
            $closingTime = \Carbon\Carbon::parse($branchTime->closing_hour);

            if ($branchTime->cross_day) {
                $closingTime = $closingTime->addDay();
                $isOpen = $currentTime->between($openingTime, $closingTime) || $currentTime->isSameDay($closingTime);
            } else {
                $isOpen = $currentTime->between($openingTime, $closingTime);
            }
        }

        return view(
            'website.landing',
            compact(['sliders', 'discounts', 'popularDishes', 'menuCategories', 'userFavorites', 'locale', 'branchId', 'lastOrder', 'rate', 'orderDetails', 'currencySymbol', 'isOpen', 'notifications'])
        );
    }

    public function showMenu(Request $request)
    {
        $id_details = 0;
        if ($request->id) {
            $check_menu = BranchMenu::where('dish_id', $request->id)->first();
            $id_details = $check_menu ? $request->id : 0;
        }

        $categoryId = $request->query('category_id');
        $filter = $request->query('filter', '');
        $branchId = request()->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);

        if (!$branchId) {
            return redirect()->back()->with('error', 'لا يوجد فرع متاح حاليًا.');
        }

        // Main query for menu categories
        $branchMenuQuery = BranchMenuCategory::with([
            'dish_categories' => function ($query) {
                $query->where('is_active', 1);
            },
            'branchMenus' => function ($query) use ($branchId, $filter) {
                $query->where('branch_id', $branchId)
                    ->where('is_active', 1)
                    ->with([
                        'dish' => function ($dishQuery) {
                            $dishQuery->where('is_active', 1);
                        },
                        'branchMenuSizes.dishSizes',
                    ])
                    ->when($filter === 'most_ordered', function ($query) {
                        $query->leftJoin('order_details', 'order_details.dish_id', '=', 'branch_menus.dish_id')
                            ->select('branch_menus.*')
                            ->selectRaw('SUM(order_details.quantity) as total_quantity')
                            ->groupBy('branch_menus.id')
                            ->orderByDesc('total_quantity');
                    })
                    ->when($filter === 'recently_added', function ($query) {
                        $query->orderByDesc('branch_menus.created_at');
                    });
            },
        ])
            ->whereHas('dish_categories', function ($query) {
                $query->where('is_active', 1);
            })
            ->whereHas('branchMenus', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->where('is_active', 1)
                    ->whereHas('dish', function ($q) {
                        $q->where('is_active', 1);
                    });
            })
            ->where('is_active', 1);

        $menuCategories = $branchMenuQuery->get();

        // Calculate default prices
        foreach ($menuCategories as $category) {
            foreach ($category->branchMenus as $menu) {
                if ($menu->dish && $menu->dish->has_sizes) {
                    $menu->defaultPrice = BranchMenuSize::where('branch_menu_sizes.dish_id', $menu->dish->id)
                        ->where('branch_id', $menu->branch_id)
                        ->leftJoin('dish_sizes', 'dish_sizes.id', '=', 'branch_menu_sizes.dish_size_id')
                        ->where('dish_sizes.default_size', 1)
                        ->select('branch_menu_sizes.price')
                        ->first()->price ?? $menu->price;
                } else {
                    $menu->defaultPrice = $menu->price;
                }
            }
        }

        // Set default category if none selected
        if (empty($categoryId)) {
            $firstActiveCategory = $menuCategories->firstWhere('dish_categories.is_active', true);
            $categoryId = $firstActiveCategory ? $firstActiveCategory->dish_categories->id : null;
        }

        // Get branch and currency info
        $branch = Branch::with('country')->find($branchId);
        $currencySymbol = $branch?->country?->currency_symbol ?? 'ج.م';

        // Get user favorites
        $userFavorites = [];
        if (Auth::guard('client')->check()) {
            $userFavorites = DB::table('user_favorite_dishes')
                ->where('user_id', Auth::guard('client')->id())
                ->pluck('dish_id')
                ->toArray();
        }

        // Get popular dishes
        $popularDishes = getMostDishesOrdered($branchId, 5);

        // Get active offers
        $offers = Offer::where('is_active', 1)
            ->where(function ($query) use ($branchId) {
                $query->whereRaw('FIND_IN_SET(?, branch_id)', [$branchId])
                    ->orWhere('branch_id', -1);
            })
            ->whereHas('details', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->get();

        // Check if branch is open
        $isOpen = false;
        $currentTime = now();
        $currentDay = $currentTime->dayOfWeek;

        $branchTime = BranchTime::where('branch_id', $branchId)
            ->where('day', $currentDay)
            ->where('is_active', 1)
            ->first();

        if ($branchTime) {
            $openingTime = \Carbon\Carbon::parse($branchTime->opening_hour);
            $closingTime = \Carbon\Carbon::parse($branchTime->closing_hour);

            if ($branchTime->cross_day) {
                $closingTime = $closingTime->addDay();
                $isOpen = $currentTime->between($openingTime, $closingTime) || $currentTime->isSameDay($closingTime);
            } else {
                $isOpen = $currentTime->between($openingTime, $closingTime);
            }
        }

        return view('website.menu', compact([
            'menuCategories',
            'filter',
            'userFavorites',
            'categoryId',
            'offers',
            'currencySymbol',
            'id_details',
            'isOpen',
            'popularDishes'
        ]));
    }

    public function showOffers(Request $request)
    {
        $categoryId = 'offers';
        $userLat = $request->cookie('latitude') ?? ($_COOKIE['latitude'] ?? null);
        $userLon = $request->cookie('longitude') ?? ($_COOKIE['longitude'] ?? null);
        $BranchId = $request->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);
        if ($BranchId) {
            $branchId = $BranchId;
            Log::info('Nearest branch selected:', ['branchId' => $branchId]);
        } else {
            $branchId = getDefaultBranch();
            Log::warning('Fallback to default branch:', ['branchId' => $branchId]);
        }

        //        if ($userLat && $userLon) {
        //            $nearestBranch = getNearestBranch($userLat, $userLon);
        //            if ($nearestBranch) {
        //                $branchId = $nearestBranch->id;
        //                Log::info('Nearest branch selected:', ['branchId' => $branchId]);
        //            } else {
        //                $branchId = getDefaultBranch();
        //                Log::warning('Fallback to default branch:', ['branchId' => $branchId]);
        //            }
        //        } else {
        //            $branchId = getDefaultBranch();
        //            Log::warning('No coordinates found, using default branch:', ['branchId' => $branchId]);
        //        }

        if (!$branchId) {
            return redirect()->back()->with('error', 'لا يوجد فرع متاح حاليًا.');
        }
        $menuCategories = BranchMenuCategory::with([
            'dish_categories',
            'branchMenus' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->where('is_active', 1)
                    ->with(['dish' => function ($dishQuery) {
                        $dishQuery->where('is_active', 1);
                    }]);
            },
        ])
            ->whereHas('branchMenus', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->where('is_active', 1);
            })
            ->where('is_active', 1)
            ->get();

        $userFavorites = [];
        if (Auth::guard('client')->check()) {
            $userFavorites = DB::table('user_favorite_dishes')
                ->where('user_id', Auth::guard('client')->id())
                ->pluck('dish_id')
                ->toArray();
        }
        $offers = Offer::where('is_active', 1)
            ->where(function ($query) use ($branchId) {
                $query->whereRaw('FIND_IN_SET(?, branch_id)', [$branchId])
                    ->orWhere('branch_id', -1);
            })
            ->whereHas('details', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->get();
        $branch = Branch::with('country')->find($branchId);

        $currencySymbol = $branch->country->currency_symbol ?? __('offer.currency_symbol');
        return view(
            'website.menu',
            compact(['menuCategories', 'userFavorites', 'categoryId', 'offers', 'currencySymbol'])
        );
    }

    public function contactUs()
    {
        $branches = Branch::all();
        return view(
            'website.contact-us',
            compact(['branches'])
        );
    }

    public function privacy()
    {
        $privacies = StaticPageResource::collection(
            PrivacyPolicy::where('active', 1)->get()
        );
        $privaciesArray = $privacies->toArray(request());
        $branches = Branch::all();
        return view('website.privacy', compact('privaciesArray', 'branches'));
    }

    public function return()
    {
        $returns = StaticPageResource::collection(
            ReturnPolicy::where('active', 1)->get()
        );
        $returnsArray = $returns->toArray(request());
        $branches = Branch::all();
        return view('website.return', compact('returnsArray', 'branches'));
    }

    public function terms()
    {
        $terms = StaticPageResource::collection(
            TermsAndCondition::where('active', 1)->get()
        );
        $termsArray = $terms->toArray(request());
        $branches = Branch::all();
        return view('website.terms', compact('termsArray', 'branches'));
    }

    public function addFavorite(Request $request)
    {
        if (!Auth::guard('client')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('client')->user();
        $dishId = $request->dish_id;

        $favorite = DB::table('user_favorite_dishes')
            ->where('user_id', $user->id)
            ->where('dish_id', $dishId)
            ->first();

        if ($favorite) {
            // Remove favorite
            DB::table('user_favorite_dishes')
                ->where('user_id', $user->id)
                ->where('dish_id', $dishId)
                ->delete();

            return response()->json(['status' => 'removed']);
        } else {
            // Add to favorites
            DB::table('user_favorite_dishes')->insert([
                'user_id' => $user->id,
                'dish_id' => $dishId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'added']);
        }
    }
    public function showFavorites(Request $request)
    {
        // $userLat = $request->cookie('latitude') ?? ($_COOKIE['latitude'] ?? null);
        // $userLon = $request->cookie('longitude') ?? ($_COOKIE['longitude'] ?? null);

        $branchId = request()->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);

        $branchMenuQuery = BranchMenuCategory::with([
            'dish_categories',
            'branchMenus' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->where('is_active', 1)
                    ->with([
                        'dish' => function ($dishQuery) {
                            $dishQuery->where('is_active', 1);
                        },
                        'branchMenuSizes.dishSizes',
                    ]);
            },
        ])
            ->whereHas('branchMenus', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)->where('is_active', 1);
            })
            ->where('is_active', 1);

        $menuCategories = $branchMenuQuery->get();
        // Calculate the default price for each menu item
        foreach ($menuCategories as $category) {
            foreach ($category->branchMenus as $menu) {
                if ($menu->dish && $menu->dish->has_sizes) {
                    $menu->defaultPrice = BranchMenuSize::where('branch_menu_sizes.dish_id', $menu->dish->id)
                        ->where('branch_id', $menu->branch_id)
                        ->leftJoin('dish_sizes', 'dish_sizes.id', '=', 'branch_menu_sizes.dish_size_id')
                        ->where('dish_sizes.default_size', 1)
                        ->select('branch_menu_sizes.price')
                        ->first()->price ?? $menu->price;
                } else {
                    $menu->defaultPrice = $menu->price;
                }
            }
        }
        $popularDishes = getMostDishesOrdered($branchId, 5);
        $currentTime = \Carbon\Carbon::now();
        $currentDay = $currentTime->dayOfWeek;
        $branchTime = BranchTime::where('branch_id', $branchId)
            ->where('day', $currentDay)
            ->where('is_active', 1)
            ->first();

        $isOpen = false;

        if ($branchTime) {
            $currentTime = \Carbon\Carbon::now();

            $openingTime = \Carbon\Carbon::parse($branchTime->opening_hour);
            $closingTime = \Carbon\Carbon::parse($branchTime->closing_hour);

            if ($branchTime->cross_day) {
                $closingTime = $closingTime->addDay();
            }

            if ($branchTime->cross_day) {
                $isOpen = $currentTime->between($openingTime, $closingTime)
                    || $currentTime->isSameDay($closingTime);
            } else {
                $isOpen = $currentTime->between($openingTime, $closingTime);
            }
        }
        $userFavorites = [];
        if (Auth::guard('client')->check()) {
            $userFavorites = DB::table('user_favorite_dishes')
                ->where('user_id', Auth::guard('client')->id())
                ->pluck('dish_id')
                ->toArray();
        }
        $branch = Branch::with('country')->find($branchId);
        $currencySymbol = $branch?->country?->currency_symbol ?? 'ج.م';

        return view(
            'website.favorites',
            compact(['menuCategories', 'userFavorites', 'currencySymbol', 'popularDishes', 'isOpen'])
        );
    }

    public function getfaqs()
    {
        $lang = app()->getLocale(); // Get the current language

        // Select only the necessary fields based on the language
        $faqs = FAQ::select(
            "name_{$lang} as name",
            "question_{$lang} as question",
            "answer_{$lang} as answer",
            'active'
        )->where('active', 1) // Only fetch active FAQs
            ->get();

        return view('website.faq', compact('faqs'));
    }

    public function showRate()
    {
        $rates = Rate::all();
        return view('website.rate', compact('rates'));
    }

    public function addRate(Request $request)
    {
        $lang = app()->getLocale();
        $validatedData = $request->validate([
            'value' => 'required|integer|min:1|max:5',
            'note' => 'nullable|string|max:1000',
        ]);

        $rating = new Rate();
        $rating->value = $validatedData['value'];
        $rating->note = $validatedData['note'];
        $rating->active = 1;
        $rating->created_by = auth()->id();
        $rating->save();

        return redirect()->back()->with('success', $lang == 'en' ? 'Thank you for your rating!' :  'شكراً لتقييمك!');
    }

    public function getBranchInfo(Request $request)
    {
        try {
            $branch_details = Branch::where('id', $request->branch_id)
                ->with('branchTimeDay')
                ->with('getFloorPartitions')
                ->first();
            if ($branch_details) {
                return $branch_details;
            } else {
                return __('cart.change_branch');
            }
        } catch (\Exception $e) {
            Log::error('Error fetching branch: ' . $e->getMessage());
            // return RespondWithBadRequestData($lang, 2);
        }
    }

    public function nearestBranch(Request $request)
    {
        $lat = $request->input('lat');
        $long = $request->input('long');

        // Call your helper function to get the nearest branch
        $nearestBranch = getNearestBranch($lat, $long);

        // Return the nearest branch ID as JSON
        return response()->json(['branch_id' => $nearestBranch ? $nearestBranch->id : null]);
    }

    public function showCoupons(Request $request)
    {
        if (Auth::guard('client')->check()) {
            $status = $request->query('type', 'active');

            $coupons = UserCoupon::where('user_id', Auth::guard('client')->id())
                ->whereHas('coupon', function ($query) use ($status) {
                    $query->where('status', $status)
                        ->where('is_active', 1)
                        ->whereNull('deleted_at');
                })
                ->with('coupon')
                ->get();

            return view('website.coupons', compact('coupons', 'status'));
        } else {
            return redirect()->route('client.login')->with('error', 'You must be logged in to view coupons.');
        }
    }

    public function markNotificationAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::guard('client')->id())
            ->first();

        if ($notification) {
            $notification->status = 1; // Mark as read
            $notification->save();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
    }
}
