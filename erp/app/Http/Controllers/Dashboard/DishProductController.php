<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\KitchenServices\CuisineService;
use App\Http\Controllers\Controller;
use App\Models\Cuisine;
use App\Models\DishCategory;
use App\Services\KitchenServices\DishProductService;
use App\Services\KitchenServices\DishCategoryService;
use Illuminate\Support\Facades\Validator;


class DishProductController extends Controller
{
    protected $dishCategoryService;
    protected $cuisineService;
    protected $dishProductService;

    public function __construct(DishCategoryService $dishCategoryService, CuisineService $cuisineService, DishProductService $dishProductService)
    {
        $this->dishCategoryService = $dishCategoryService;
        $this->cuisineService = $cuisineService;
        $this->dishProductService = $dishProductService;
    }

    public function create()
    {
        // Fetch categories (not deleted and active)
        $categories = DishCategory::whereNull('deleted_at')
            ->where('is_active', 1)
            ->get();

        // Fetch cuisines (not deleted and active)
        $cuisines = Cuisine::whereNull('deleted_at')
            ->where('is_active', 1)
            ->get();

        // Fetch branches (not deleted and active)
        $branches = Branch::whereNull('deleted_at')
            ->where('is_active', true)
            ->get();

        // Fetch products (not deleted, active, and of type 'complete')
        $products = Product::where('type', 'complete')
            ->whereNull('deleted_at')
            ->get();

        return view('dashboard.dish_products.create', compact('categories', 'cuisines', 'branches', 'products'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $data['created_by'] = auth('admin')->id();

            // Validate the request data
            $validator = Validator::make($data, [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'description_ar' => 'required|string',
                'description_en' => 'required|string',
                'price' => 'required|numeric|min:0',
                'branches' => 'required|array',
                'complete_product' => 'required|exists:products,id',
                'is_active' => 'required|boolean',
                'time' => 'nullable', // Updated rule for 12-hour AM/PM format
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'item_code_id' => 'nullable|exists:item_codes,id',
            ], [
                'name_ar.required' => __('validation.name_ar_required'),
                'name_en.required' => __('validation.name_en_required'),
                'description_ar.required' => __('validation.description_ar_required'),
                'description_en.required' => __('validation.description_en_required'),
                'price.required' => __('validation.price_required'),
                'price.min' => __('validation.price_min'),
                'branches.required' => __('validation.branches_required'),
                'complete_product.required' => __('validation.complete_product_required'),
                'is_active.required' => __('validation.is_active_required'),
                'image.image' => __('validation.image_invalid'),
                'image.mimes' => __('validation.image_mimes'),
                'image.max' => __('validation.image_max'),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $this->dishProductService->store($request);

            return redirect()->route('dashboard.dishes.index')->with('success', __('dishes.ProductCreated'));
        } catch (\Exception $e) {
            \Log::error('Product creation failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', __('dishes.ProductCreationFailed'));
        }
    }
}
