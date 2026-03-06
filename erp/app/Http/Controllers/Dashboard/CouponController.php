<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Unit;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\StoreServices\BrandService;
use App\Services\SettingsServices\CouponService;
use App\Http\Controllers\Controller;
use App\Models\BranchMenu;
use App\Models\BranchMenuCategory;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $couponService;
    protected $checkToken;
    protected $lang;


    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {
        $coupons = $this->couponService->index($request)
            ->with(['branches' => function ($query) {
                $query->select('branches.id', 'name_ar', 'name_en')
                    ->withPivot('dish_ids');
            }])
            ->get()
            ->map(function ($coupon) {
                $coupon->branches->each(function ($branch) {
                    $branch->dish_ids = json_decode($branch->pivot->dish_ids, true);
                });
                return $coupon;
            });

        return view('dashboard.coupon.list', compact('coupons'));
    }



    public function create(Request $request)
    {
        $branches = Branch::whereNull('deleted_at')->get();

        return view('dashboard.coupon.add', compact('branches'));
    }
    public function getBranchCategories($branch_id)
    {
        $categories = BranchMenuCategory::with('dish_categories')
            ->where('branch_id', $branch_id)
            ->whereHas('branchMenus', function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->dish_category_id,
                    'menu_id' => $category->id,
                    'name' => $category->dish_categories->name_site,
                ];
            });

        return response()->json(['categories' => $categories]);
    }

    public function getCategoryDishes($category_id, $branch_id)
    {
        $dishes = BranchMenu::with('dish')
            ->where('branch_menu_category_id', $category_id)
            ->where('branch_id', $branch_id)
            ->get()
            ->map(function ($dish) {
                return [
                    'id' => $dish->dish_id,
                    'menu_id' => $dish->id,
                    'name' => $dish->name,
                ];
            });

        return response()->json($dishes);
    }


    public function store(Request $request)
    {
        $response = $this->couponService->store($request, $this->checkToken);
        $responseData = $response->original;

        // Check if the response has a 'status' key
        if (isset($responseData['status']) && !$responseData['status']) {
            $errorData = $responseData['errorData'] ?? null;

            // Case: array error data (like invalid dishes)
            if (is_array($errorData) && isset($errorData['invalid_dishes'])) {
                $dishNames = collect($errorData['invalid_dishes'])
                    ->pluck('name')
                    ->implode(', ');

                $errorMessage = __('coupon.dishes_below_minimum_spend') . '<br>' . $dishNames;

                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }

            // Case: validation errors as MessageBag
            if ($errorData instanceof \Illuminate\Support\MessageBag) {
                return redirect()->back()->withErrors($errorData)->withInput();
            }

            // Fallback case
            return redirect()->back()
                ->with('error', $responseData['message'] ?? __('Something went wrong'))
                ->withInput();
        }


        // Success case
        $message = $responseData['message'] ?? __('Operation successful');
        // return redirect()->back()->with('success', $message);
        return redirect('dashboard/coupons')->with('message', __('coupon.Operation successful'));
    }

public function edit($id)
{
    $coupon = Coupon::with('branches')->findOrFail($id);

    $categoryIds = [];

    foreach ($coupon->branches as $branch) {
        // 1. Decode dish_ids from pivot
        $dishIds = json_decode($branch->pivot->dish_ids, true) ?? [];

        if (!empty($dishIds)) {
            // 2. Get categories for this branch
            $branchCategoryIds = BranchMenu::whereIn('dish_id', $dishIds)
                ->where('branch_id', $branch->id)
                ->pluck('branch_menu_category_id')
                ->toArray();

            // Merge without duplicates
            $categoryIds = array_unique(array_merge($categoryIds, $branchCategoryIds));
        }
    }

    // Reindex
    $categoryIds = array_values($categoryIds);

    return view('dashboard.coupon.edit', compact('coupon', 'categoryIds', 'id'));
}


    public function show($id)
    {
        $coupon = Coupon::with('branches')->findOrFail($id);
        return view('dashboard.coupon.show', compact('coupon', 'id'));
    }

    public function update(Request $request, $id)
    {
        $response = $this->couponService->update($request, $id, $this->checkToken);
        $responseData = $response->original;
        if (isset($responseData['status']) && !$responseData['status']) {
            // If 'data' key exists, handle validation errors
            if (isset($responseData['data'])) {
                $validationErrors = $responseData['data'];
                return redirect()->back()->withErrors($validationErrors)->withInput();
            } else {
                // dd(0);
                return redirect()->back()->withErrors($responseData['message'])->withInput();
            }

            // If no 'data' key is present, handle it gracefully
        }
        // if (!$responseData['status'] && isset($responseData['data'])) {
        //     $validationErrors = $responseData['data'];
        //     return redirect()->back()->withErrors($validationErrors)->withInput();
        // }
        return redirect('dashboard/coupons')->with('message', __('coupon.Operation successful'));
    }

    public function delete(Request $request, $id)
    {
        $response = $this->couponService->destroy($request, $id, $this->checkToken);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/coupons')->with('message', $message);
    }
}
