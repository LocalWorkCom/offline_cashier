<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inventory\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DirectSupplyPermissionItem;
use Illuminate\Support\Facades\File;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductBrandCategoryColor;
use App\Models\ProductBrandUnit;
use App\Models\ProductColor;
use App\Models\ProductImage;
use App\Models\ProductOpeningBalance;
use App\Models\ProductSize;
use App\Models\ProductStore;
use App\Models\ProductTransaction;
use App\Models\ProductUnit;
use App\Models\PurchaseRequestItem;
use App\Models\Store;
use App\Models\StoreTransaction;
use App\Models\SupplyOrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PDO;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAveragePrice(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        App::setLocale($lang);
        $data= ProductBrand::where('brand_id', $request->brand_id)->where('product_id', $request->product_id)->first();
        return ResponseWithSuccessData($lang, $data->avg_price, 1);

    }
    public function showProductdetail(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $query = Product::with([
            'category',
            'productBrands.brand',
            'productBrands.units',
            'productBrands.defaultUnit',
            'productBrands.baseUnit',
        ]);

        //  Filter by product_id (optional)
        if ($request->filled('product_id')) {
            $query->where('id', $request->product_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        $products = $query->get()
            ->filter(function ($product) {
                return $product->productBrands->isNotEmpty();
            })
            ->map(function ($product) use ($lang) {

                return [
                    'id'          => $product->id,
                    'name'        => $lang === 'ar' ? $product->name_ar : $product->name_en,
                    'description' => $lang === 'ar'
                        ? $product->description_ar
                        : $product->description_en,

                    'category' => $product->category ? [
                        'id'   => $product->category->id,
                        'name' => $lang === 'ar'
                            ? $product->category->name_ar
                            : $product->category->name_en,
                    ] : null,

                    'brands' => $product->productBrands->map(function ($productBrand) use ($lang) {

                        if (!$productBrand->brand) {
                            return null;
                        }

                        $units = collect();

                        if ($productBrand->relationLoaded('units')) {
                            foreach ($productBrand->units as $unit) {
                                $units->push([
                                    'id'   => $unit->id,
                                    'name' => $lang === 'ar'
                                        ? $unit->name_ar
                                        : $unit->name_en,
                                    'type' => 'normal',
                                ]);
                            }
                        }

                        if ($productBrand->defaultUnit) {
                            $units->push([
                                'id'   => $productBrand->defaultUnit->id,
                                'name' => $lang === 'ar'
                                    ? $productBrand->defaultUnit->name_ar
                                    : $productBrand->defaultUnit->name_en,
                                'type' => 'default',
                            ]);
                        }

                        if ($productBrand->baseUnit) {
                            $units->push([
                                'id'   => $productBrand->baseUnit->id,
                                'name' => $lang === 'ar'
                                    ? $productBrand->baseUnit->name_ar
                                    : $productBrand->baseUnit->name_en,
                                'type' => 'base',
                            ]);
                        }

                        $units = $units->unique('id')->values();

                        return [
                            'id'        => $productBrand->brand->id,
                            'name'      => $lang === 'ar'
                                ? $productBrand->brand->name_ar
                                : $productBrand->brand->name_en,
                            'avg_price' => $productBrand->avg_price ?? 0,

                            'units' => $units,
                        ];
                    })->filter()->values(),
                ];
            })
            ->values();

        return ResponseWithSuccessData($lang, $products, 1);
    }

    public function skuGeneration(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $last_sku = ProductBrand::whereNotNull('sku')
            ->orderBy('sku', 'desc')
            ->first();

        if ($last_sku) {

            preg_match('/\d+$/', $last_sku->sku, $matches);

            // If no numeric found → start from 0
            $lastNumber = isset($matches[0]) ? (int)$matches[0] : 0;

            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $maxIdAuto = ProductBrand::max('id_auto');
        $idAuto = $maxIdAuto ? ((int)$maxIdAuto + 1) : 1;

        while (true) {

            $sku = 'SKU-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $id_Auto = str_pad($idAuto, 6, '0', STR_PAD_LEFT);

            // Check uniqueness
            $existsSku = ProductBrand::where('sku', $sku)->exists();
            $existsIdAuto = ProductBrand::where('id_auto', $id_Auto)->exists();

            if (!$existsSku && !$existsIdAuto) {

                $data = [
                    'sku' => $sku,
                    'id_auto' => $id_Auto
                ];

                return ResponseWithSuccessData($lang, $data, 1);
            }

            // Increment values and retry
            $nextNumber++;
            $idAuto++;
        }
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $query = ProductBrand::with([
            'brand',
            'units',
            'product',
            'product.category',
            'productStores.storageLocation',
            'productStores.shelve',
            'productStores.zone',
            'openingBalance',
            'baseUnit',
            'defaultUnit',
        ])->orderByDesc('created_at')->orderByDesc('updated_at');

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('from')) {
            $query->where('created_at', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('created_at', $request->to);
        }
        if ($request->filled('last_updated')) {
            $query->where('updated_at', $request->to);
        }
        if ($request->filled('unit_id')) {
            $unitId = $request->unit_id;
            $query->whereHas('units', function ($q) use ($unitId) {
                $q->where('first_unit_id', $unitId)->orWhere('second_unit_id', $unitId);
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        if ($request->filled('sub_category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('sub_category_id', $request->sub_category_id);
            });
        }
        if ($request->filled('storage_location_id')) {
            $query->whereHas('productStores', function ($q) use ($request) {
                $q->where('storage_location_id', $request->storage_location_id);
            });
        }

        if ($request->filled('expiration_date')) {
            $query->whereHas('openingBalance', function ($q) use ($request) {
                $q->where('expiration_date', '<=', $request->expiration_date);
            });
        }
        $module = getCurrentModuleDependOnRoute($request, 'product');
        $request->attributes->set('module', $module);


        $products = paginateOrGetAll($query, $request);
        $products['data'] = ProductResource::collection($products['data']);

        return ResponseWithSuccessDataPaginated($lang, $products, 1);
    }
    public function showProduct(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $module = getCurrentModuleDependOnRoute($request, 'product');
        $request->attributes->set('module', $module);

        // $type = $request->input('type', 'basic');

        // Fetch the product brand with all necessary relationships
        $product = ProductBrand::with([
            'brand',
            'units',
            'product',
            'product.category',
            'productStores',
            'productStores.storageLocation',
            'productStores.shelve',
            'productStores.zone',
            'baseUnit',
            'defaultUnit',
            'openingBalance'
        ])->find($id);


        if (!$product) {
            return respondError($lang === 'en' ? 'Product not found.' : 'المنتج غير موجود.', 404);
        }

        if (!isset($request->type)) {
            return $this->showBasicInfo($request, $product->product_id, $id);
        }
        $data = (new ProductResource($product))->toArray($request);

        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => $lang === 'en'
                ? 'Product retrieved successfully.'
                : 'تم جلب المنتج بنجاح.',
            'data' => $data,
        ]);
    }

    public function showBasicInfoV2(Request $request, $id, $product_brand_id = null)
    {
        $lang = $request->header('lang', 'ar');

        $product = Product::with([
            'category',
            'productBrands.brand',
            'productBrands.units',
            'productBrands.openingBalance',
            'productBrands.productStores',
            'productBrands.productStores.storageLocation',
            'productBrands.productStores.shelve',
            'productBrands.productStores.zone'
        ])->find($id);

        if (!$product) {
            return respondError('validation.not_found', 404);
        }

        // Transform product into simplified structure
        $result = [
            'name_ar' => $product->name_ar,
            'name_en' => $product->name_en,
            'description_ar' => $product->description_ar,
            'description_en' => $product->description_en,
            'type' => $product->type,
            'category_id' => $product->category_id,
            'brands' => []
        ];

        foreach ($product->productBrands as $brand) {
            $store = $brand->productStores->first();
            $balance = $brand->openingBalance;

            $result['brands'][] = [
                'brand_id' => $brand->brand_id,
                'image' => $brand->image ? asset($brand->image) : null,
                'barcode' => $balance?->barcode,
                'is_reusable' => (bool) $brand->is_reusable,
                'is_have_expired' => (bool) $brand->is_have_expired,
                'production_date' => $balance?->production_date,
                'expired_date' => $balance?->expiration_date,
                'default_unit_id' => $brand->default_unit_id,
                'base_unit_id' => $brand->base_unit_id,
                'units' => $brand->units->map(function ($unit) {
                    return [
                        'first_unit_id' => $unit->first_unit_id,
                        'second_unit_id' => $unit->second_unit_id,
                        'factor' => (float) $unit->factor,
                    ];
                })->values(),
                'store_id' => $store?->store_id,
                'min_limit' => $store?->min_limit ? (float) $store->min_limit : null,
                'max_limit' => $store?->max_limit ? (float) $store->max_limit : null,
                'quantity' => $balance?->quantity ? (float) $balance->quantity : null,
                'shelve_id' => $store?->shelve_id,
                'zone_id' => $store?->zone_id,
                'is_freeze' => (bool) ($store?->is_freeze ?? false),
                'storage_location_id' => $store?->storage_location_id,
            ];
        }

        return ResponseWithSuccessData($lang, $result, 1);
    }
    public function showBasicInfo(Request $request, $id, $product_brand_id = null)
    {
        $lang = $request->header('lang', 'ar');

        $product = Product::with([
            'category',
            'subCategory',
            'productBrands.brand',
            'productBrands.units',
            'productBrands.openingBalance',
            'productStores.storageLocation',
            'productStores.shelve',
            'productStores.zone'
        ])->find($id);

        if (!$product) {
            return respondError('validation.not_found', 404);
        }

        // Convert to array
        $productData = $product->toArray();

        // // Rename "product_brands" to "brands"
        // if (isset($productData['product_brands'])) {
        //     $productData['brands'] = $productData['product_brands'];
        //     unset($productData['product_brands']);
        // }

        // Add current product brand ID
        $productData = [
            'id' => $productData['id'],
            'current_product_brand_id' => $product_brand_id ? (int) $product_brand_id : null,
        ] + $productData;

        // Fix shelve name if needed
        if (!empty($productData['product_stores'])) {
            foreach ($productData['product_stores'] as &$store) {
                if (!empty($store['shelve']) && isset($store['shelve']['identifier'])) {
                    $store['shelve']['name'] = $store['shelve']['identifier'];
                    unset($store['shelve']['identifier']);
                }
            }
        }

        return ResponseWithSuccessData($lang, $productData, 1);
    }

    public function storeProductTransaction(Request $request)
    {
        $employee = auth('employee')->user();
        $lang = $request->header('lang', 'ar');
        $validator = Validator::make($request->all(), [
            'product_brand_id' => 'required|exists:product_brands,id',
            'quantity' => 'required',
            'unit_id' => 'nullable',
            'barcode' => 'nullable|string',
            'production_date' => 'nullable',
            'expire_date' => 'nullable',
        ]);

        if ($validator->fails()) {
            return respondError('ValidationError', 400, $validator->errors());
        }
        $product_transaction = storeProductTransaction($request->product_brand_id, $request->quantity, $request->unit_id, $request->barcode, $request->production_date, $request->expire_date, $request->type, $employee->id, 'direct_supply_permission_items', 1);

        return ResponseWithSuccessData($lang, ['store' => true], 1);
    }

    public function getProductQuantity(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:product_brands,id',
            'barcode' => 'nullable|string',
            'barcode_list' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return respondError('ValidationError', 400, $validator->errors());
        }

        $barcodeList = $request->boolean('barcode_list', false);
        $barcode = $request->barcode;

        $quantityData = getProductQuantity(
            $request->product_id,
            'base',
            $barcode,
            $barcodeList
        );


        return ResponseWithSuccessData($lang, $quantityData, 1);
    }
    public function indexV2(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $query = Product::with([
            'category',
            'productBrands.brand',
            'productBrands.units',
            'productStores',
            'productStores.storageLocation',
            'productStores.shelve',
            'productStores.zone'
        ]);
        if ($request->filled('brand_id')) {
            $brandId = $request->brand_id;
            $query->whereHas('productBrands', function ($q) use ($brandId) {
                $q->where('brand_id', $brandId);
            });
        }

        if ($request->filled('unit_id')) {
            $unitId = $request->unit_id;
            $query->whereHas('productBrands.units', function ($q) use ($unitId) {
                $q->where('first_unit_id', $unitId)->orWhere('second_unit_id', $unitId);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // if ($request->filled('store_id')) {
        //     $storeId = $request->store_id;
        //     $query->whereHas('productStores', function ($q) use ($storeId) {
        //         $q->where('store_id', $storeId);
        //     });
        // }

        // if ($request->filled('expiration_date')) {
        //     $query->whereDate('expiration_date', '<=', $request->expiration_date);
        // }

        $products = paginateOrGetAll($query, $request, null);

        return ResponseWithSuccessDataPaginated($lang, $products, 1);
    }


    public function storeV2(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();
        $rules = [
            'name_ar' => [
                'required',
                'string',
                'min:1',
                Rule::unique('products', 'name_ar')->whereNull('deleted_at'),
            ],
            'name_en' => [
                'required',
                'string',
                'min:1',
                Rule::unique('products', 'name_en')->whereNull('deleted_at'),
            ],
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'category_id' => 'required',
            'integer',
            'exists:categories,id',
            Rule::exists('categories', 'id')->where(function ($query) {
                $query->where('active', 1)->whereNull('deleted_at');
            }),
            'brands' => 'required|array|min:1',
            'brands.*.brand_id' => 'required|integer|exists:brands,id',
            'brands.*.image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'brands.*.is_have_expired' => 'required|boolean',
            'brands.*.production_date' => 'nullable|date',
            'brands.*.expired_date' => 'nullable|date',
            'brands.*.default_unit_id' => [
                'required',
                Rule::exists('units', 'id')->where(function ($query) {
                    $query->where('active', 1)->whereNull('deleted_at');
                }),
            ],
            'brands.*.base_unit_id' => [
                'required',
                Rule::exists('units', 'id')->where(function ($query) {
                    $query->where('active', 1)->whereNull('deleted_at');
                }),
            ],
            'brands.*.units' => 'required|array|min:1',
            'brands.*.units.*.first_unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where(function ($query) {
                    $query->where('active', 1)->whereNull('deleted_at');
                }),
            ],
            'brands.*.units.*.second_unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where(function ($query) {
                    $query->where('active', 1)->whereNull('deleted_at');
                }),
            ],
            'brands.*.units.*.factor' => 'required|numeric|min:0.0001',

        ];

        // Add module-specific rules
        $module = getCurrentModuleDependOnRoute($request, 'product');
        if ($module == 'inventory') {
            $rules = array_merge($rules, [
                'type' => 'required|string|in:complete,raw',
                'brands.*.storage_location_id' => 'required|exists:storage_locations,id',
                'brands.*.zone_id' => 'required|exists:zones,id',
                'brands.*.shelve_id' => 'required|exists:rack_shelves,id',
                'brands.*.store_id' => 'required|exists:stores,id',
                'brands.*.is_freeze' => 'required|boolean',
                'brands.*.is_reusable' => 'required|boolean',
                'brands.*.barcode' => ['required', 'string'],
                'brands.*.quantity' => 'required|numeric|lte:brands.*.max_limit|gte:brands.*.min_limit',
                'brands.*.min_limit' => 'required|numeric',
                'brands.*.max_limit' => 'required|numeric|gte:brands.*.min_limit',
            ]);
        }

        if ($module == 'procurement') {
            $rules = array_merge($rules, [
                'new_product' => 'required|in:0,1',
                'brands.*.min_limit' => [
                    'required',
                    'numeric',
                    'min:0', // min cannot be negative
                    function ($attribute, $value, $fail) use ($request) {
                        // Find corresponding max_limit
                        preg_match('/brands\.(\d+)\.min_limit/', $attribute, $matches);
                        $index = $matches[1] ?? null;
                        if ($index !== null) {
                            $max = $request->input("brands.$index.max_limit");
                            if ($max !== null && $value == $max) {
                                $fail(__('validation.min_max_equal')); // min shouldn't equal max
                            }
                        }
                    },
                ],
                'brands.*.max_limit' => [
                    'required',
                    'numeric',
                    'gt:0', // max can't be 0
                    function ($attribute, $value, $fail) use ($request) {
                        // Find corresponding min_limit
                        preg_match('/brands\.(\d+)\.max_limit/', $attribute, $matches);
                        $index = $matches[1] ?? null;
                        if ($index !== null) {
                            $min = $request->input("brands.$index.min_limit");
                            if ($min !== null && $value <= $min) {
                                $fail(__('validation.max_greater_min')); // max must be greater than min
                            }
                        }
                    },
                ],

                'brands.*.barcode' => ['nullable', 'string', Rule::unique('product_transactions', 'barcode')],
                'brands.*.stock_market' => 'required|boolean',
                'brands.*.note' => 'nullable|string',
                'brands.*.validity_period' => [
                    'integer',
                    Rule::requiredIf(function () use ($request) {
                        // This closure will not work directly for each item, so we handle in custom rule below
                        return false; // we handle per-item in closure below
                    }),
                    function ($attribute, $value, $fail) use ($request) {
                        $lang = $request->header('lang', 'en');

                        // Extract the index: brands.0.validity_period → 0
                        preg_match('/brands\.(\d+)\.validity_period/', $attribute, $matches);
                        $index = $matches[1] ?? null;

                        if ($index !== null) {
                            $brand = $request->input("brands.$index");

                            //  Required if is_have_expired is true
                            if (!empty($brand['is_have_expired']) && $brand['is_have_expired'] == true && empty($value)) {
                                $fail($lang === 'ar'
                                    ? "فترة الصلاحية مطلوبة لأنه تم تفعيل الانتهاء."
                                    : "The validity period is required because the brand is marked as expired.");
                                return; // stop further checks
                            }

                            //  Check validity_period matches days until expired_date
                            if (!empty($brand['expired_date']) && $value !== null) {
                                $today = Carbon::today();
                                $diff = Carbon::parse($brand['expired_date'])->diffInDays($today);

                                if ($diff != $value) {
                                    $fail($lang === 'ar'
                                        ? "فترة الصلاحية يجب أن تكون مساوية لعدد الأيام من اليوم حتى تاريخ الانتهاء."
                                        : "The validity_period must be equal to the number of days from today until expired_date.");
                                }
                            }
                        }
                    }
                ],
                'brands.*.avg_price' => 'required_if:brands.*.stock_market,true|numeric|min:0',
                'brands.*.quantity' => 'required|numeric|lte:brands.*.max_limit',
                'brands.*.sku' => [
                    'nullable',
                    'string',
                    Rule::unique('product_brands', 'sku')->whereNull('deleted_at')
                ],
                'brands.*.id_auto' => [
                    'required',
                    'string',
                    Rule::unique('product_brands', 'id_auto')->whereNull('deleted_at')
                ],
                'brands.*.status' => 'nullable|in:active,inactive',
                'brands.*.depend_market' => 'nullable|boolean',
                'sub_category_id' => [
                    'nullable',
                    'integer',
                    'exists:categories,id',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('active', 1)->whereNull('deleted_at');
                    }),
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value) {
                            $lang = $request->header('lang', 'ar');
                            $subCategory = Category::find($value);
                            if (!$subCategory || $subCategory->parent_id != $request->category_id) {
                                $fail(
                                    $lang === 'ar'
                                        ? 'الفئة الملحقة المحددة لا تنتمي إلى الفئة الرئيسية المرسلة.'
                                        : 'The selected sub_category_id does not belong to the given category_id.'
                                );
                            }
                        }
                    }
                ]
            ]);
        }


        // Run validator once
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return respondError('ValidationError', 400, $validator->errors());
        }

        $category = Category::find($request->category_id);

        if (!$category) {
            $message = ($lang == 'ar') ? 'الفئة غير موجودة' : "category not exist";
            return respondError($message, 400);
        }


        foreach ($request->brands as $index => $brand) {
            $units = $brand['units'];
            if (empty($units)) {
                $message = ($lang == 'ar') ? 'وحدة المقاس خاطئة' : "Invalid unit chain for brand index $index";
                return respondError($message, 400);
            }

            $firstUnitId = $units[0]['first_unit_id'] ?? null;
            $lastUnitId = end($units)['second_unit_id'] ?? null;

            if ($firstUnitId != $brand['base_unit_id']) {
                $message = ($lang == 'ar') ? 'وحدة قياس الماركة الاولي يجب ان تكون نفس وحدة القياس الافتراضيه' : "First unit in brand index $index must match base_unit_id";
                return respondError($message, 400);
            }

            if ($lastUnitId != $brand['default_unit_id']) {
                $message = ($lang == 'ar') ? "اخر وحدة قياس يجب ان تكون نفس وحده القياس الاساسيه" : "Last unit in brand index $index must match default_unit_id";
                return respondError($message, 400);
            }
        }

        // Generate product code
        $code = GenerateCode('products', GetLastID('products'));

        $product = new Product();
        $product->name_ar = $request->name_ar;
        $product->name_en = $request->name_en;
        $product->description_ar = $request->description_ar;
        $product->description_en = $request->description_en;
        $product->code = $code;
        $product->type = $request->type ?? 'complete';
        $product->category_id = $request->category_id;
        $product->created_by = $employee->id;
        if ($module == 'procurement') {
            $product->sub_category_id = $request->sub_category_id;
        }
        $product->save();
        if ($module == 'procurement') {
            if (isset($request['image']) && $request['image'] instanceof \Illuminate\Http\UploadedFile) {
                UploadFile('images/products', 'main_image', $product, $request['image']);
            }
        }
        foreach ($request->brands as $brandInput) {
            if ($module == 'procurement') {

                if (!isset($brandInput['stock_market'])) {
                    return respondError("Brand index $index: stock_market is required", 400);
                }
                if ($brandInput['stock_market'] && (!isset($brandInput['avg_price']) || !is_numeric($brandInput['avg_price']))) {
                    $message = ($lang == 'ar')
                        ? "يجب إدخال السعر في حالة تفعيل  العتماد علي السوق الحالي رقم $index"
                        : "avg_price is required when stock market is true for brand index $index";
                    return respondError($message, 400);
                }
            }
            $productBrand = new ProductBrand();
            $productBrand->product_id = $product->id;
            $productBrand->brand_id = $brandInput['brand_id'];
            $productBrand->is_reusable = $brandInput['is_reusable'] ?? false;
            $productBrand->is_have_expired = $brandInput['is_have_expired'] ?? false;
            $productBrand->default_unit_id = $brandInput['default_unit_id'] ?? null;
            $productBrand->base_unit_id = $brandInput['base_unit_id'] ?? null;
            $productBrand->note = $brandInput['note'] ?? null;

            if ($module == 'procurement') {
                $productBrand->id_auto = $brandInput['id_auto'];
                $productBrand->validity_period = $brandInput['validity_period'] ?? 0;
                $productBrand->sku = $brandInput['sku'];
                $productBrand->depend_market = $brandInput['depend_market'] ?? 0;
                $productBrand->avg_price = $brandInput['avg_price'] ?? 0;
                $productBrand->stock_market = $brandInput['stock_market'] ?? true;
            }
            $productBrand->status = $brandInput['status'] ?? 'active';

            $productBrand->save();

            // Store product store info
            $product_store = new ProductStore();
            $product_store->product_brand_id = $productBrand->id;
            $product_store->store_id = $brandInput['store_id'] ?? Store::first()->id;
            $product_store->min_limit = $brandInput['min_limit'];
            $product_store->max_limit = $brandInput['max_limit'];
            $product_store->shelve_id = $brandInput['shelve_id'] ?? null;
            $product_store->zone_id = $brandInput['zone_id'] ?? null;
            $product_store->is_freeze = $brandInput['is_freeze'] ?? false;
            $product_store->storage_location_id = $brandInput['storage_location_id'] ?? null;
            $product_store->save();
            if ($module == 'inventory') {

                if (isset($brandInput['image']) && $brandInput['image'] instanceof \Illuminate\Http\UploadedFile) {
                    UploadFile('images/products/brand', 'image', $productBrand, $brandInput['image']);
                }
            }

            $product_opening_balance = new ProductOpeningBalance();
            $product_opening_balance->product_brand_id = $productBrand->id;
            $product_opening_balance->quantity = $brandInput['quantity'];
            $product_opening_balance->barcode = $brandInput['barcode']??null;
            $product_opening_balance->production_date = $brandInput['production_date'] ?? now();
            $product_opening_balance->expiration_date = $brandInput['expired_date'] ?? null;
            $product_opening_balance->save();

            storeProductTransaction(
                $productBrand->id,
                $brandInput['quantity'],
                $productBrand->default_unit_id,
                $brandInput['barcode']??null,
                $brandInput['production_date'] ?? null,
                $brandInput['expired_date'] ?? null,
                'in',
                $employee->id,
                'product_opening_balance',
                $product_opening_balance->id
            );

            foreach ($brandInput['units'] as $unit) {
                DB::table('product_brand_units')->insert([
                    'product_brand_id' => $productBrand->id,
                    'first_unit_id' => $unit['first_unit_id'],
                    'second_unit_id' => $unit['second_unit_id'],
                    'factor' => $unit['factor'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        if($request->new_product && $request->new_product == 1){
            return ResponseWithSuccessData($lang, ['new_product' => 1], 1);
        }
        return RespondWithSuccessRequest($lang, 1);
    }


    // public function updateV2(Request $request, $id)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);

    //     $validator = Validator::make($request->all(), [
    //         'name_ar' => 'required|string',
    //         'name_en' => 'required|string',
    //         'description_ar' => 'nullable|string',
    //         'description_en' => 'nullable|string',
    //         'main_image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //         'type' => 'required|string|in:complete,raw',
    //         'category_id' => 'required|integer|exists:categories,id',
    //         'product_brands' => 'required|array|min:1',
    //         'product_brands.*.brand_id' => 'required|integer|exists:brands,id',
    //         'product_brands.*.image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //         'product_brands.*.barcode' => 'required|string',
    //         'product_brands.*.is_reusable' => 'required|boolean',
    //         'product_brands.*.is_have_expired' => 'required|boolean',
    //         'product_brands.*.production_date' => 'required|date',
    //         'product_brands.*.expired_date' => 'required|date',
    //         'product_brands.*.default_unit_id' => 'required|exists:units,id',
    //         'product_brands.*.base_unit_id' => 'required|exists:units,id',
    //         'product_brands.*.units' => 'required|array|min:1',
    //         'product_brands.*.units.*.first_unit_id' => 'required|integer|exists:units,id',
    //         'product_brands.*.units.*.second_unit_id' => 'required|integer|exists:units,id',
    //         'product_brands.*.units.*.factor' => 'required|numeric|min:0.0001',
    //         'product_brands.*.store_id' => 'required|exists:stores,id',
    //         'product_brands.*.min_limit' => 'required|numeric',
    //         'product_brands.*.max_limit' => 'required|numeric',
    //         'product_brands.*.quantity' => 'required|numeric',
    //         'product_brands.*.shelve_id' => 'required|exists:rack_shelves,id',
    //         'product_brands.*.zone_id' => 'required|exists:zones,id',
    //         'product_brands.*.is_freeze' => 'required|boolean',
    //         'product_brands.*.storage_location_id' => 'required|exists:storage_locations,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return respondError('ValidationError', 400, $validator->errors());
    //     }

    //     $product = Product::find($id);
    //     if (!$product) return RespondWithBadRequestData($lang, 8);

    //     // Validate unit chain
    //     foreach ($request->product_brands as $index => $brand) {
    //         $units = $brand['units'];
    //         if (empty($units)) {
    //             $message = ($lang == 'ar') ? 'وحدة القياس غير صالحة' : "Invalid unit chain for brand index $index";
    //             return respondError($message, 400);
    //         }

    //         $firstUnitId = $units[0]['first_unit_id'] ?? null;
    //         $lastUnitId = end($units)['second_unit_id'] ?? null;

    //         if ($firstUnitId !=  $brand['base_unit_id']) {
    //             $message = ($lang == 'ar') ? 'وحدة قياس الماركة الاولي يجب ان تكون نفس وحدة القياس الافتراضيه' : "First unit in brand index $index must match default_unit_id";
    //             return respondError($message, 400);
    //         }

    //         if ($lastUnitId != $brand['default_unit_id']) {
    //             $message = ($lang == 'ar') ? "اخر وحدة قياس يجب ان تكون نفس وحده القياس الاساسيه" : "Last unit in brand index $index must match base_unit_id";
    //             return respondError($message, 400);
    //         }
    //     }

    //     // ✅ Update Product info
    //     $product->update([
    //         'name_ar' => $request->name_ar,
    //         'name_en' => $request->name_en,
    //         'description_ar' => $request->description_ar,
    //         'description_en' => $request->description_en,
    //         'type' => $request->type,
    //         'category_id' => $request->category_id,
    //         'modify_by' => Auth::guard('employee')->user()->id,
    //     ]);

    //     // ✅ Delete old product brands, units, and stores
    //     // $oldBrands = ProductBrand::where('product_id', $product->id)->get();
    //     // foreach ($oldBrands as $oldBrand) {
    //     // DB::table('product_brand_units')->where('product_brand_id', $oldBrand->id)->delete();
    //     // ProductStore::where('product_brand_id', $oldBrand->id)->delete();
    //     // $oldBrand->delete();
    //     // }

    //     // ✅ Recreate product brands, stores, and units
    //     foreach ($request->product_brands as $brandInput) {
    //         // $productBrand = new ProductBrand();
    //         $productBrand = ProductBrand::where('product_id', $product->id)->where('brand_id', $brandInput['brand_id'])->first();
    //         //   $productBrand->product_id = $product->id;
    //         //         $productBrand->brand_id = $brandInput['brand_id'];
    //         if ($productBrand) {
    //             $productBrand->is_reusable = $brandInput['is_reusable'];
    //             $productBrand->is_have_expired = $brandInput['is_have_expired'];
    //             $productBrand->production_date = $brandInput['production_date'];
    //             $productBrand->expired_date = $brandInput['expired_date'];
    //             $productBrand->default_unit_id = $brandInput['default_unit_id'];
    //             $productBrand->base_unit_id = $brandInput['base_unit_id'];
    //             $productBrand->barcode = $brandInput['barcode'];
    //             $productBrand->save();
    //             if (isset($brandInput['image']) && $brandInput['image'] instanceof \Illuminate\Http\UploadedFile) {
    //                 UploadFile('images/products/brand', 'image', $productBrand, $brandInput['image']);
    //             }
    //             foreach ($brandInput['units'] as $unit) {
    //                 DB::table('product_brand_units')->insert([
    //                     'product_brand_id' => $productBrand->id,
    //                     'first_unit_id' => $unit['first_unit_id'],
    //                     'second_unit_id' => $unit['second_unit_id'],
    //                     'factor' => $unit['factor'],
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }
    //         }

    //         // ✅ Product store info (brand-specific)
    //         $product_store = ProductStore::where('product_brand_id', $product->id)->where('store_id', $brandInput['store_id'])->first();
    //         // $product_store->product_brand_id = $productBrand->id;
    //         if ($product_store) {

    //             $product_store->store_id = $brandInput['store_id'];
    //             $product_store->min_limit = $brandInput['min_limit'];
    //             $product_store->max_limit = $brandInput['max_limit'];
    //             $product_store->shelve_id = $brandInput['shelve_id'];
    //             $product_store->zone_id = $brandInput['zone_id'];
    //             $product_store->is_freeze = $brandInput['is_freeze'];
    //             $product_store->storage_location_id = $brandInput['storage_location_id'];
    //             $product_store->save();
    //         }
    //         // if (isset($brandInput['image']) && $brandInput['image']) {
    //         //     UploadFile('images/products/brand', 'image', $productBrand, $brandInput['image']);
    //         // }

    //         // ✅ Store unit conversions

    //     }

    //     // ✅ Update product main image if uploaded
    //     if ($request->hasFile('main_image')) {
    //         UploadFile('images/products', 'main_image', $product, $request->file('main_image'));
    //     }

    //     return RespondWithSuccessRequest($lang, 1);
    // }
    public function updateV2(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);
            $authFlag = auth('employee')->user()->flag;
            $product = ProductBrand::find($id);
            if (!$product) {
                return respondError($lang === 'en' ? 'Product not found.' : 'المنتج غير موجود.', 404);
            }
            $rules = [
                'name_ar' => [
                    'required',
                    'string',
                    'min:1',
                    Rule::unique('products', 'name_ar')->ignore($product->product_id)->whereNull('deleted_at')
                ],
                'name_en' => [
                    'required',
                    'string',
                    'min:1',
                    Rule::unique('products', 'name_en')->ignore($product->product_id)->whereNull('deleted_at')
                ],
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'current_product_brand_id' => 'nullable|exists:product_brands,id',
                'code' => 'nullable|string|max:50',
                'image' => 'nullable|url',

                // product_brands array
                'product_brands' => 'required|array|min:1',
                'product_brands.*.id' => 'nullable|exists:product_brands,id',
                'product_brands.*.brand.id' => 'required|exists:brands,id',
                'product_brands.*.barcode' => ['nullable', 'string', Rule::unique('product_transactions', 'barcode')->ignore($id)],
                'product_brands.*.is_reusable' => 'nullable|boolean',
                'product_brands.*.is_have_expired' => 'nullable|boolean',
                'product_brands.*.production_date' => 'nullable|date',
                'product_brands.*.expired_date' => 'nullable|date|after_or_equal:product_brands.*.production_date',
                'product_brands.*.default_unit_id' => [
                    'required',
                    Rule::exists('units', 'id')->where(function ($query) {
                        $query->where('active', 1)->whereNull('deleted_at');
                    }),
                ],
                'product_brands.*.base_unit_id' => [
                    'required',
                    Rule::exists('units', 'id')->where(function ($query) {
                        $query->where('active', 1)->whereNull('deleted_at');
                    }),
                ],
                'product_brands.*.units' => 'required|array|min:1',
                'product_brands.*.units.*.first_unit_id' => [
                    'required',
                    'integer',
                    Rule::exists('units', 'id')->where(function ($query) {
                        $query->where('active', 1)->whereNull('deleted_at');
                    }),
                ],
                'product_brands.*.units.*.second_unit_id' => [
                    'required',
                    'integer',
                    Rule::exists('units', 'id')->where(function ($query) {
                        $query->where('active', 1)->whereNull('deleted_at');
                    }),
                ],
                'product_brands.*.status' => 'nullable|in:active,inactive',
                'product_brands.*.units.*.factor' => 'required|numeric|min:0',

            ];

            // Extra rules based on module
            $module = getCurrentModuleDependOnRoute($request, 'product');

            if ($module == 'inventory') {
                $rules = array_merge($rules, [
                    'product_stores.*.min_limit' => 'required|numeric',
                    'product_stores.*.max_limit' => 'required|numeric|gte:brands.*.min_limit',
                    'type' => 'required|string|in:complete,raw',
                    // 'product_stores.*.storage_location_id' => 'required|exists:storage_locations,id',
                    'product_stores.*.storage_location_id' => [
                        'required',
                        Rule::exists('storage_locations', 'id')->where(function ($q) {
                            $q->whereNull('deleted_at');
                        }),
                    ],
                    // 'product_stores.*.zone_id' => 'required|exists:zones,id',
                    'product_stores.*.zone_id' => [
                        'required',
                        Rule::exists('zones', 'id')->where(function ($q) {
                            $q->whereNull('deleted_at');
                        }),
                    ],

                    'product_stores.*.shelve_id' => [
                        'required',
                        Rule::exists('rack_shelves', 'id'),
                    ],
                    'product_stores.*.store_id' => 'required|exists:stores,id',
                    'product_stores.*.is_freeze' => 'required|boolean',
                    'product_brands.*.quantity' => 'required|numeric|lte:brands.*.max_limit|gte:brands.*.min_limit',
                    'product_brands.*.is_reusable' => 'required|boolean',
                ]);
            }

            if ($module == 'procurement') {
                $rules = array_merge($rules, [
                    'product_stores.*.min_limit' => [
                        'required',
                        'numeric',
                        'min:0',
                        function ($attribute, $value, $fail) use ($request) {
                            // Find corresponding max_limit
                            preg_match('/brands\.(\d+)\.min_limit/', $attribute, $matches);
                            $index = $matches[1] ?? null;
                            if ($index !== null) {
                                $max = $request->input("brands.$index.max_limit");
                                if ($max !== null && $value == $max) {
                                    $fail(__('validation.min_max_equal')); // min shouldn't equal max
                                }
                            }
                        },
                    ],
                    'product_stores.*.max_limit' => [
                        'required',
                        'numeric',
                        'gt:0',
                        function ($attribute, $value, $fail) use ($request) {
                            // Find corresponding min_limit
                            preg_match('/brands\.(\d+)\.max_limit/', $attribute, $matches);
                            $index = $matches[1] ?? null;
                            if ($index !== null) {
                                $min = $request->input("brands.$index.min_limit");
                                if ($min !== null && $value <= $min) {
                                    $fail(__('validation.max_greater_min')); // max must be greater than min
                                }
                            }
                        },
                    ],
                    'product_stores.*.stock_market' => 'required|boolean',
                    'product_brands.*.note' => 'required|string',
                    'product_stores.*.validity_period' => 'required_if:product_brands.*.is_have_expired,true|boolean',

                    'product_stores.*.avg_price' => 'required_if:brands.*.stock_market,true|numeric',
                    'product_brands.*.sku' => [
                        'nullable',
                        'string',
                        // Rule::unique('product_brands', 'sku')
                        //     ->ignore($request->input('brands.*.id'))->whereNull('deleted_at'),
                    ],

                    'product_brands.*.id_auto' => [
                        'required',
                        'string',
                        // Rule::unique('product_brands', 'id_auto')
                        //     ->ignore($request->input('brands.*.id'))->whereNull('deleted_at'),
                    ],

                    'product_brands.*.status' => 'nullable|in:active,inactive',
                    'product_brands.*.depend_market' => 'nullable|boolean',
                    'product_brands.*.quantity' => 'required|numeric',
                    'sub_category_id' => [
                        'nullable',
                        'integer',
                        'exists:categories,id',
                        function ($attribute, $value, $fail) use ($request) {
                            if ($value) {
                                $lang = $request->header('lang', 'en');
                                $subCategory = Category::find($value);

                                if (!$subCategory || $subCategory->parent_id != $request->category_id) {
                                    $fail(
                                        $lang === 'ar'
                                            ? 'الفئه الملحقه المحدد لا ينتمي إلى الفئه الرئيسيه المرسل.'
                                            : 'The selected sub_category_id does not belong to the given category_id.'
                                    );
                                }
                            }
                        }
                    ]
                ]);
            }

            $validator = Validator::make($request->all(), $rules);
            if ($module == 'procurement') {

                $validator->after(function ($validator) use ($request) {
                    foreach ($request->product_brands as $index => $pb) {
                        $brand = Brand::find($pb['brand']['id']);
                        if ($brand && isset($pb['quantity']) && $pb['quantity'] < $brand->max_limit) {
                            $validator->errors()->add(
                                "product_brands.$index.quantity",
                                "Quantity cannot be greater than max limit ({$brand->max_limit}) for this brand."
                            );
                        }
                    }
                });
                if ($request->has('product_brands')) {
                    foreach ($request->product_brands as $index => $brand) {

                        $validator->after(function ($validator) use ($brand, $index) {

                            // If new row → no ignore
                            $brandId = $brand['id'] ?? null;

                            $query = DB::table('product_brands')
                                ->where('id_auto', $brand['id_auto'])
                                ->whereNull('deleted_at');

                            if ($brandId) {
                                // Ignore current brand row
                                $query->where('id', '!=', $brandId);
                            }

                            if ($query->exists()) {
                                $validator->errors()->add(
                                    "product_brands.$index.id_auto",
                                    __('عفوا الرقم التلقائي مأخوذ مسبقاً')
                                );
                            }
                            $query = DB::table('product_brands')
                                ->where('sku', $brand['sku'])
                                ->whereNull('deleted_at');

                            if ($brandId) {
                                // Ignore current brand row
                                $query->where('id', '!=', $brandId);
                            }

                            if ($query->exists()) {
                                $validator->errors()->add(
                                    "product_brands.$index.sku",
                                    __('عفوا  sku مأخوذ مسبقاً')
                                );
                            }
                        });
                    }
                }
            }
            if ($validator->fails()) {
                return respondError('ValidationError', 400, $validator->errors());
            }

            $product_id = ProductBrand::find($id);
            if (!$product_id) {
                $message = ($lang == 'ar') ? 'الماركة غير موجوده' : "Brand is not exist";
                return respondError($message, 400);
            }
            $product = Product::find($product_id->product_id);
            if (!$product) {
                $message = ($lang == 'ar') ? 'الماركة غير مرتبطة بأي منتج' : "Brand is not associated with any product";
                return respondError($message, 400);
            }

            foreach ($request->product_brands as $index => $brand) {
                $units = $brand['units'];
                if (empty($units)) {
                    $message = ($lang == 'ar') ? 'وحدة القياس غير صالحة' : "Invalid unit chain for brand index $index";
                    return respondError($message, 400);
                }

                $firstUnitId = $units[0]['first_unit_id'] ?? null;
                $lastUnitId = end($units)['second_unit_id'] ?? null;

                if ($firstUnitId !=  $brand['base_unit_id']) {
                    $message = ($lang == 'ar') ? 'وحدة قياس الماركة الاولي يجب ان تكون نفس وحدة القياس الافتراضيه' : "First unit in brand index $index must match default_unit_id";
                    return respondError($message, 400);
                }

                if ($lastUnitId != $brand['default_unit_id']) {
                    $message = ($lang == 'ar') ? "اخر وحدة قياس يجب ان تكون نفس وحده القياس الاساسيه" : "Last unit in brand index $index must match base_unit_id";
                    return respondError($message, 400);
                }
            }
            $product->update([
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'description_ar' => $request->description_ar,
                'description_en' => $request->description_en,
                'type' => $request->type ?? 'complete',
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'modify_by' => Auth::guard('employee')->user()->id,
            ]);
            if ($module == 'procurement') {

                if (isset($request['image']) && $request['image'] instanceof \Illuminate\Http\UploadedFile) {
                    UploadFile('images/products', 'main_image', $product, $request['image']);
                }
            }
            foreach ($request->product_brands as $brandData) {
                $productBrand = ProductBrand::find($brandData['id']);

                if ($productBrand) {
                    $productBrand->is_reusable = $brandData['is_reusable'] ?? $productBrand->is_reusable;
                    $productBrand->is_have_expired = $brandData['is_have_expired'] ?? $productBrand->is_have_expired;
                    $productBrand->default_unit_id = $brandData['default_unit_id'] ?? $productBrand->default_unit_id;
                    $productBrand->base_unit_id = $brandData['base_unit_id'] ?? $productBrand->default_unit_id;
                    $productBrand->note = $brandData['note'] ?? $productBrand->note;
                    $productBrand->status = $brandData['status'] ?? $productBrand->status;
                    if ($module == 'procurement') {
                        $productBrand->sku = $brandData['sku'] ?? $productBrand->sku;
                        $productBrand->validity_period = $brandData['validity_period'] ?? $productBrand->validity_period;
                        $productBrand->depend_market = $brandInput['depend_market'] ?? 0;
                        $productBrand->avg_price = $brandData['avg_price'] ?? $productBrand->avg_price;
                        $productBrand->stock_market = $brandData['stock_market'] ?? $productBrand->stock_market;
                    }
                    $productBrand->save();
                    if ($module == 'inventory') {

                        if (isset($brandInput['image']) && $brandInput['image'] instanceof \Illuminate\Http\UploadedFile) {
                            UploadFile('images/products/brand', 'image', $productBrand, $brandInput['image']);
                        }
                    }
                    // Delete old unit conversions
                    DB::table('product_brand_units')->where('product_brand_id', $productBrand->id)->delete();

                    // Recreate unit chain
                    foreach ($brandData['units'] as $unit) {
                        DB::table('product_brand_units')->insert([
                            'product_brand_id' => $productBrand->id,
                            'first_unit_id' => $unit['first_unit_id'],
                            'second_unit_id' => $unit['second_unit_id'],
                            'factor' => $unit['factor'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            foreach ($request->product_stores as $storeData) {
                $productStore = ProductStore::find($storeData['id']);

                if ($productStore) {
                    // Build update data array
                    $updateData = [
                        'min_limit' => $storeData['min_limit'] ?? $productStore->min_limit,
                        'max_limit' => $storeData['max_limit'] ?? $productStore->max_limit,
                    ];

                    if ($module == 'inventory') {
                        $updateData = array_merge($updateData, [
                            'storage_location_id' => $storeData['storage_location_id'] ?? $productStore->storage_location_id,
                            'store_id' => $storeData['store_id'] ?? $productStore->store_id,
                            'zone_id' => $storeData['zone_id'] ?? $productStore->zone_id,
                            'shelve_id' => $storeData['shelve_id'] ?? $productStore->shelve_id,
                            'is_freeze' => $storeData['is_freeze'] ?? $productStore->is_freeze,
                        ]);
                    }

                    $productStore->update($updateData);
                }
            }


            return RespondWithSuccessRequest($lang, 1);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getFile() . ':' . $e->getLine(),
            ], 500);
        }
    }


    public function DeleteExistProductImage(Request $request) {}


    function DeleteFile($filePath)
    {
        $fullPath = public_path($filePath);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
    public function getProductBrandUnits(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $productBrand = ProductBrand::with([
            'units',
            'units.secondUnit',
            'units.firstUnit',
            'defaultUnit',
            'baseUnit'
        ])->find($id);

        if (!$productBrand) {
            return RespondWithBadRequestData($lang, 8);
        }

        $units = collect();
        // ✅ Default Unit
        if ($productBrand->defaultUnit) {
            $units->push([
                'id'   => $productBrand->default_unit_id,
                'name' => $productBrand->defaultUnit?->name,
            ]);
        }
        // ✅ Base Unit
        if ($productBrand->baseUnit) {
            $units->push([
                'id'   => $productBrand->base_unit_id,
                'name' => $productBrand->baseUnit?->name,
            ]);
        }



        // ✅ Conversion Units
        foreach ($productBrand->units as $unit) {
            if ($unit->firstUnit) {
                $units->push([
                    'id'   => $unit->firstUnit->id,
                    'name' => $unit->firstUnit->name,
                ]);
            }

            if ($unit->secondUnit) {
                $units->push([
                    'id'   => $unit->secondUnit->id,
                    'name' => $unit->secondUnit->name,
                ]);
            }
        }

        // ✅ Remove duplicates and nulls
        $units = $units->unique('id')->filter(fn($u) => !empty($u['name']))->values();

        return ResponseWithSuccessData($lang, $units, 1);
    }

    public function deleteV2(Request $request, $id)
    {
        $lang =  $request->header('lang', 'ar');
        $productBrand = ProductBrand::find($id);

        if (!$productBrand) {
            return RespondWithBadRequest($lang, 8);
        }

        $isUsed =
            PurchaseRequestItem::where('product_brand_id', $id)->exists() ||
            SupplyOrderItem::where('product_brand_id', $id)->exists() ||
            DirectSupplyPermissionItem::where('product_brand_id', $id)->exists();

        if ($isUsed) {
            return respondErrorData(
                $lang === 'en'
                    ? 'Cannot delete: Product brand is already used in related transactions.'
                    : 'لا يمكن الحذف: تم استخدام هذا الصنف في معاملات أخرى.',
                400
            );
        }

        if ($productBrand->main_image) {
            $this->DeleteFile('images/products/' . $productBrand->main_image);
        }

        ProductStore::where('product_brand_id', $id)->delete();
        ProductBrandUnit::where('product_brand_id', $id)->delete();
        //handle transaction
        $productBrand->status = 'inactive';
        $productBrand->delete();

        return RespondWithSuccessRequest($lang, 1); // Successfully deleted
    }
}
