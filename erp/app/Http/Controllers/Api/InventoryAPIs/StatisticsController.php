<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use App\Models\ProductTransaction;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;

class StatisticsController extends Controller
{
    // Statistics from product with pagination  on quantity and product
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);

            // Base query - Use Product instead of ProductBrand to avoid duplicates
            $query = Product::with([
                'category',
                'productBrands.brand',
                'productBrands.units.firstUnit',
                'productBrands.units.secondUnit',
                'productBrands.productStores.storageLocation',
                'productBrands.productStores.shelve',
                'productBrands.productStores.zone',
                'productBrands.openingBalance',
                'productBrands.baseUnit',
                'productBrands.defaultUnit',
                'productBrands.transactions'
            ]);

            //  Filters
            if ($request->filled('product_id')) {
                $query->where('id', $request->product_id);
            }

            if ($request->filled('product_brand_id')) {
                $query->whereHas('productBrands', function ($q) use ($request) {
                    $q->where('id', $request->product_brand_id);
                });
            }

            if ($request->filled('base_unit_id') || $request->filled('default_unit_id')) {
                $query->whereHas('productBrands.units', function ($q) use ($request) {
                    if ($request->filled('base_unit_id')) {
                        $q->where('first_unit_id', $request->base_unit_id);
                    }
                    if ($request->filled('default_unit_id')) {
                        $q->where('second_unit_id', $request->default_unit_id);
                    }
                });
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('barcode')) {
                $barcode = $request->barcode;
                $query->where('code', '=', $barcode);
            }

            if ($request->filled('has_expiration')) {
                $query->whereHas('productBrands', function ($q) {
                    $q->where('is_have_expired', 1);
                });
            }

            if ($request->filled('name')) {
                $name = $request->name;
                $query->where(function ($q) use ($name) {
                    $q->where('name_en', 'like', "%$name%")
                        ->orWhere('name_ar', 'like', "%$name%");
                });
            }

            //  Paginate products
            $result = paginateOrGetAll($query, $request, null);

            // Transform the products data
            $productsData = collect($result['data'])->map(function ($product) use ($lang) {
                $firstProductBrand = $product->productBrands->first();
                $firstProductStore = $firstProductBrand ? $firstProductBrand->productStores->first() : null;

                $totalQuantity = $product->productBrands->sum(function ($brand) {
                    return $brand->transactions->sum('quantity');
                });

                $allUnits = $product->productBrands->flatMap(function ($brand) {
                    return $brand->units;
                })->unique('id');

                $hasExpiration = $product->productBrands->contains('is_have_expired', 1);
                $expirationType = $product->productBrands->first()->expiration_type ?? null;

                $brandNames = $product->productBrands->map(function ($brand) use ($lang) {
                    return $brand->brand ?
                        ($lang === 'en' ? $brand->brand->name_en : $brand->brand->name_ar)
                        : null;
                })->filter()->unique()->implode(', ');

                return [
                    'product_id' => $product->id,
                    'barcode' => $product->code ?? null,
                    'name' => $lang === 'en' ? ($product->name_en ?? '') : ($product->name_ar ?? ''),
                    'image' => $product->image ?? null,
                    'category_id' => $product->category_id ?? null,
                    'category_name' => $product->category
                        ? ($lang === 'en' ? $product->category->name_en : $product->category->name_ar)
                        : null,
                    'brand_names' => $brandNames ?: null,
                    'storage_location' => [
                        'name_ar' => $firstProductStore->storageLocation->name_ar ?? null,
                        'name_en' => $firstProductStore->storageLocation->name_en ?? null,
                    ],
                    'is_have_expired' => $hasExpiration ? 1 : 0,
                    'expiration_type' => $expirationType,
                    'quantity' => $totalQuantity,
                    'units' => $allUnits->map(function ($unit) use ($lang) {
                        return [
                            'base_unit_id' => $unit->first_unit_id,
                            'default_unit_id' => $unit->second_unit_id,
                            'base_unit_name' => $unit->firstUnit
                                ? ($lang === 'en' ? $unit->firstUnit->name_en : $unit->firstUnit->name_ar)
                                : null,
                            'default_unit_name' => $unit->secondUnit
                                ? ($lang === 'en' ? $unit->secondUnit->name_en : $unit->secondUnit->name_ar)
                                : null,
                        ];
                    }),
                ];
            });

            $result['data'] = $productsData;

            //  Quantities - paginated
            $quantitiesQuery = ProductTransaction::selectRaw('product_brand_id, SUM(quantity) as total_quantity')
                ->groupBy('product_brand_id')
                ->with(['products.product', 'products.brand']);

            $quantitiesRaw = $quantitiesQuery->get()
                ->groupBy('products.product_id')
                ->map(function ($transactions, $productId) use ($lang) {
                    $firstTransaction = $transactions->first();
                    $totalQuantity = $transactions->sum('total_quantity');

                    $productName = $firstTransaction->products?->product?->{$lang === 'en' ? 'name_en' : 'name_ar'};
                    $brandName = $firstTransaction->products?->brand?->{$lang === 'en' ? 'name_en' : 'name_ar'};

                    return [
                        'product_id' => $productId,
                        'product_name' => $productName,
                        'brand_name' => $brandName,
                        'quantity' => $totalQuantity ?? 0,
                    ];
                })
                ->sortByDesc('quantity')
                ->values();

            // Apply pagination to quantities - Use the same per_page as products
            $quantitiesPage = max(1, (int) $request->get('page', 1));
            $quantitiesPerPage = $request->get('per_page', 10); // Use the main per_page parameter
            $quantitiesTotal = $quantitiesRaw->count();

            $quantitiesPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $quantitiesRaw->forPage($quantitiesPage, $quantitiesPerPage)->values(),
                $quantitiesTotal,
                $quantitiesPerPage,
                $quantitiesPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            //  Expiry calculations
            $allExpiryProducts = Product::whereHas('productBrands', function ($q) {
                $q->where('is_have_expired', 1);
            })
                ->with(['category', 'productBrands.transactions'])
                ->get();

            $expiryProducts = $allExpiryProducts
                ->groupBy('category_id')
                ->map(function ($products, $categoryId) use ($lang) {
                    $category = $products->first()->category;
                    $totalQuantity = $products->sum(function ($product) {
                        return $product->productBrands->sum(function ($brand) {
                            return $brand->transactions->sum('quantity');
                        });
                    });

                    return [
                        'category_id' => $categoryId,
                        'category_name' => $category
                            ? ($lang === 'en' ? $category->name_en : $category->name_ar)
                            : 'Unknown Category',
                        'quantity' => $totalQuantity,
                    ];
                })
                ->where('quantity', '>', 0)
                ->sortByDesc('quantity')
                ->values();

            $totalExpiryQuantity = $expiryProducts->sum('quantity');

            $expiryProducts = $expiryProducts->map(function ($category) use ($totalExpiryQuantity) {
                $percentage = $totalExpiryQuantity > 0 ? round(($category['quantity'] / $totalExpiryQuantity) * 100, 2) : 0;
                return array_merge($category, ['percentage' => $percentage]);
            });

            $expiryProductsCount = $allExpiryProducts->filter(function ($product) {
                $totalQuantity = $product->productBrands->sum(function ($brand) {
                    return $brand->transactions->sum('quantity');
                });
                return $totalQuantity > 0;
            })->count();

            //  Response
            return response()->json([
                'code' => 200,
                'status' => true,
                'message' => 'Products statistics retrieved successfully',
                'data' => [
                    'products' => $result,
                    'quantities' => [
                        'data' => $quantitiesPaginated->items(),
                        'meta' => [
                            'totalItems' => $quantitiesPaginated->total(),
                            'itemsPerPage' => (int) $quantitiesPaginated->perPage(),
                            'currentPage' => $quantitiesPaginated->currentPage(),
                            'totalPages' => $quantitiesPaginated->lastPage(),
                        ],
                    ],
                    'expiry_products' => $expiryProducts,
                    'expiry_products_count' => $expiryProductsCount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    // // Statistics from product with pagination on product only
    // public function index(Request $request)
    // {
    //     try {
    //         $lang = $request->header('lang', 'ar');
    //         App::setLocale($lang);

    //         //  Base query - Use Product instead of ProductBrand to avoid duplicates
    //         $query = Product::with([
    //             'category',
    //             'productBrands.brand',
    //             'productBrands.units.firstUnit',
    //             'productBrands.units.secondUnit',
    //             'productBrands.productStores.storageLocation',
    //             'productBrands.productStores.shelve',
    //             'productBrands.productStores.zone',
    //             'productBrands.openingBalance',
    //             'productBrands.baseUnit',
    //             'productBrands.defaultUnit',
    //             'productBrands.transactions'
    //         ]);

    //         //  Filters - UPDATED for Product model
    //         if ($request->filled('product_id')) {
    //             $query->where('id', $request->product_id);
    //         }

    //         if ($request->filled('product_brand_id')) {
    //             $query->whereHas('productBrands', function ($q) use ($request) {
    //                 $q->where('id', $request->product_brand_id);
    //             });
    //         }

    //         if ($request->filled('base_unit_id') || $request->filled('default_unit_id')) {
    //             $query->whereHas('productBrands.units', function ($q) use ($request) {
    //                 if ($request->filled('base_unit_id')) {
    //                     $q->where('first_unit_id', $request->base_unit_id);
    //                 }
    //                 if ($request->filled('default_unit_id')) {
    //                     $q->where('second_unit_id', $request->default_unit_id);
    //                 }
    //             });
    //         }

    //         if ($request->filled('category_id')) {
    //             $query->where('category_id', $request->category_id);
    //         }

    //         if ($request->filled('barcode')) {
    //             $barcode = $request->barcode;
    //             $query->where('code', '=', $barcode);
    //         }

    //         //  Filter by products that have expiration
    //         if ($request->filled('has_expiration')) {
    //             $query->whereHas('productBrands', function ($q) {
    //                 $q->where('is_have_expired', 1);
    //             });
    //         }

    //         if ($request->filled('name')) {
    //             $name = $request->name;
    //             $query->where('name_en', 'like', "%$name%")
    //                 ->orWhere('name_ar', 'like', "%$name%");
    //         }

    //         // Apply pagination to the main products query
    //         $result = paginateOrGetAll($query, $request, null);

    //         // Transform the products data
    //         $productsData = collect($result['data'])->map(function ($product) use ($lang) {
    //             $firstProductBrand = $product->productBrands->first();
    //             $firstProductStore = $firstProductBrand ? $firstProductBrand->productStores->first() : null;

    //             //  Calculate total quantity from all product brands' transactions
    //             $totalQuantity = $product->productBrands->sum(function ($brand) {
    //                 return $brand->transactions->sum('quantity');
    //             });

    //             //  Get all unique units from all product brands
    //             $allUnits = $product->productBrands->flatMap(function ($brand) {
    //                 return $brand->units;
    //             })->unique('id');

    //             //  Get expiration info from product brands
    //             $hasExpiration = $product->productBrands->contains('is_have_expired', 1);
    //             $expirationType = $product->productBrands->first()->expiration_type ?? null;

    //             //  Get brand names (concatenate if multiple brands)
    //             $brandNames = $product->productBrands->map(function ($brand) use ($lang) {
    //                 return $brand->brand ?
    //                     ($lang === 'en' ? $brand->brand->name_en : $brand->brand->name_ar)
    //                     : null;
    //             })->filter()->unique()->implode(', ');

    //             return [
    //                 'product_id' => $product->id,
    //                 'barcode' => $product->code ?? null,
    //                 'name' => $lang === 'en' ? ($product->name_en ?? '') : ($product->name_ar ?? ''),
    //                 'image' => $product->image ?? null,
    //                 'category_id' => $product->category_id ?? null,
    //                 'category_name' => $product->category
    //                     ? ($lang === 'en' ? $product->category->name_en : $product->category->name_ar)
    //                     : null,
    //                 'brand_names' => $brandNames ?: null,
    //                 'storage_location' => [
    //                     'name_ar' => $firstProductStore->storageLocation->name_ar ?? null,
    //                     'name_en' => $firstProductStore->storageLocation->name_en ?? null,
    //                 ],
    //                 'is_have_expired' => $hasExpiration ? 1 : 0,
    //                 'expiration_type' => $expirationType,
    //                 'quantity' => $totalQuantity,
    //                 //  Add units list here from all product brands
    //                 'units' => $allUnits->map(function ($unit) use ($lang) {
    //                     return [
    //                         'base_unit_id' => $unit->first_unit_id,
    //                         'default_unit_id' => $unit->second_unit_id,
    //                         'base_unit_name' => $unit->firstUnit
    //                             ? ($lang === 'en' ? $unit->firstUnit->name_en : $unit->firstUnit->name_ar)
    //                             : null,
    //                         'default_unit_name' => $unit->secondUnit
    //                             ? ($lang === 'en' ? $unit->secondUnit->name_en : $unit->secondUnit->name_ar)
    //                             : null,
    //                     ];
    //                 }),
    //             ];
    //         });

    //         // Replace the data with transformed data
    //         $result['data'] = $productsData;

    //         //  Quantity list from ProductTransaction (grouped by product)
    //         $quantities = ProductTransaction::selectRaw('product_brand_id, SUM(quantity) as total_quantity')
    //             ->groupBy('product_brand_id')
    //             ->with(['products.product', 'products.brand'])
    //             ->get()
    //             ->groupBy('products.product_id') // Group by product to avoid duplicates
    //             ->map(function ($transactions, $productId) use ($lang) {
    //                 $firstTransaction = $transactions->first();
    //                 $totalQuantity = $transactions->sum('total_quantity');

    //                 $productName = null;
    //                 $brandName = null;

    //                 if ($firstTransaction->products && $firstTransaction->products->product) {
    //                     $productName = $lang === 'en'
    //                         ? $firstTransaction->products->product->name_en
    //                         : $firstTransaction->products->product->name_ar;
    //                 }

    //                 if ($firstTransaction->products && $firstTransaction->products->brand) {
    //                     $brandName = $lang === 'en'
    //                         ? $firstTransaction->products->brand->name_en
    //                         : $firstTransaction->products->brand->name_ar;
    //                 }

    //                 return [
    //                     'product_id' => $productId,
    //                     'product_name' => $productName,
    //                     'brand_name' => $brandName,
    //                     'quantity' => $totalQuantity ?? 0,
    //                 ];
    //             })
    //             ->values()
    //             ->sortByDesc('quantity')
    //             ->values();

    //         // Get ALL expiry products for counting
    //         $allExpiryProducts = Product::whereHas('productBrands', function ($q) {
    //             $q->where('is_have_expired', 1);
    //         })
    //             ->with(['category', 'productBrands.transactions'])
    //             ->get();

    //         //  Expiry product list (only products that have expiry) - Grouped by CATEGORY
    //         $expiryProducts = $allExpiryProducts
    //             ->groupBy('category_id') // Group by category instead of product
    //             ->map(function ($products, $categoryId) use ($lang) {
    //                 $firstProduct = $products->first();
    //                 $category = $firstProduct->category;

    //                 // Calculate total quantity for ALL products in this category
    //                 $totalQuantity = $products->sum(function ($product) {
    //                     return $product->productBrands->sum(function ($brand) {
    //                         return $brand->transactions->sum('quantity');
    //                     });
    //                 });

    //                 return [
    //                     'category_id' => $categoryId,
    //                     'category_name' => $category
    //                         ? ($lang === 'en' ? $category->name_en : $category->name_ar)
    //                         : 'Unknown Category',
    //                     'quantity' => $totalQuantity,
    //                 ];
    //             })
    //             ->values()
    //             ->where('quantity', '>', 0)
    //             ->sortByDesc('quantity')
    //             ->values();

    //         // Calculate total quantity for percentage calculation
    //         $totalExpiryQuantity = $expiryProducts->sum('quantity');

    //         // Add percentage to each category
    //         $expiryProducts = $expiryProducts->map(function ($category) use ($totalExpiryQuantity) {
    //             $percentage = $totalExpiryQuantity > 0 ? round(($category['quantity'] / $totalExpiryQuantity) * 100, 2) : 0;

    //             return array_merge($category, [
    //                 'percentage' => $percentage
    //             ]);
    //         });

    //         // Count individual products that have expiry (for expiry_products_count)
    //         $expiryProductsCount = $allExpiryProducts
    //             ->filter(function ($product) {
    //                 // Only count products that have quantity > 0
    //                 $totalQuantity = $product->productBrands->sum(function ($brand) {
    //                     return $brand->transactions->sum('quantity');
    //                 });
    //                 return $totalQuantity > 0;
    //             })
    //             ->count();

    //         //  Response
    //         return response()->json([
    //             'code' => 200,
    //             'status' => true,
    //             'message' => 'Products statistics retrieved successfully',
    //             'data' => [
    //                 'products' => $result,
    //                 'quantities' => $quantities,
    //                 'expiry_products' => $expiryProducts,
    //                 'expiry_products_count' => $expiryProductsCount, // Count of individual products
    //             ],
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'code' => 500,
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //         ]);
    //     }
    // }
    // Statistics from product brand

    // public function index(Request $request)
    // {
    //     try {
    //         $lang = $request->header('lang', 'ar');
    //         App::setLocale($lang);

    //         //  Base query (same relationships as ProductController)
    //         $query = ProductBrand::with([
    //             'brand',
    //             'units',
    //             'product',
    //             'product.category',
    //             'productStores.storageLocation',
    //             'productStores.shelve',
    //             'productStores.zone',
    //             'openingBalance',
    //             'baseUnit',
    //             'defaultUnit',
    //             'transactions' // For quantity calculations
    //         ]);

    //         //  Filters - UPDATED
    //         if ($request->filled('product_brand_id')) {
    //             $query->where('id', $request->product_brand_id);
    //         }

    //         if ($request->filled('base_unit_id') || $request->filled('default_unit_id')) {
    //             $query->whereHas('units', function ($q) use ($request) {
    //                 if ($request->filled('base_unit_id')) {
    //                     $q->where('first_unit_id', $request->base_unit_id);
    //                 }
    //                 if ($request->filled('default_unit_id')) {
    //                     $q->where('second_unit_id', $request->default_unit_id);
    //                 }
    //             });
    //         }

    //         if ($request->filled('category_id')) {
    //             $query->whereHas('product', function ($q) use ($request) {
    //                 $q->where('category_id', $request->category_id);
    //             });
    //         }

    //         if ($request->filled('barcode')) {
    //             $barcode = $request->barcode;
    //             $query->whereHas('product', function ($q) use ($barcode) {
    //                 $q->where('code', '=', $barcode);
    //             });
    //         }

    //         //  Filter by products that have expiration
    //         if ($request->filled('has_expiration')) {
    //             $query->where('is_have_expired', 1);
    //         }

    //         if ($request->filled('name')) {
    //             $name = $request->name;
    //             $query->whereHas('product', function ($q) use ($name) {
    //                 $q->where('name_en', 'like', "%$name%")
    //                     ->orWhere('name_ar', 'like', "%$name%");
    //             });
    //         }

    //         //  Get data
    //         $products = $query->get()->map(function ($brand) use ($lang) {
    //             $product = $brand->product;
    //             $firstProductStore = $brand->productStores->first();

    //             // Calculate total quantity from product transactions
    //             $totalQuantity = $brand->transactions->sum('quantity');

    //             return [
    //                 'product_brand_id' => $brand->id,
    //                 'barcode' => $product->code ?? null,
    //                 'name' => $lang === 'en' ? ($product->name_en ?? '') : ($product->name_ar ?? ''),
    //                 'image' => $product->image ?? null,
    //                 'category_id' => $product->category_id ?? null,
    //                 'category_name' => $product->category
    //                     ? ($lang === 'en' ? $product->category->name_en : $product->category->name_ar)
    //                     : null,
    //                 'brand_name' => $brand->brand
    //                     ? ($lang === 'en' ? $brand->brand->name_en : $brand->brand->name_ar)
    //                     : null,
    //                 'storage_location' => [
    //                     'name_ar' => $firstProductStore->storageLocation->name_ar ?? null,
    //                     'name_en' => $firstProductStore->storageLocation->name_en ?? null,
    //                 ],
    //                   // 'zone' => [
    //                 //     'name_ar' => $firstProductStore->zone->name_ar ?? null,
    //                 //     'name_en' => $firstProductStore->zone->name_en ?? null,
    //                 // ],
    //                 // 'shelve' => [
    //                 //     'name_ar' => $firstProductStore->shelve->name_ar ?? null,
    //                 //     'name_en' => $firstProductStore->shelve->name_en ?? null,
    //                 // ],
    //                 'is_have_expired' => $brand->is_have_expired ?? 0, // Added expiration info
    //                 'expiration_type' => $brand->expiration_type ?? null, //  Added expiration type
    //                 'quantity' => $totalQuantity, //  Added quantity from transactions
    //                 //  Add units list here
    //                 'units' => $brand->units->map(function ($unit) use ($lang) {
    //                     return [
    //                         'base_unit_id' => $unit->first_unit_id,
    //                         'default_unit_id' => $unit->second_unit_id,
    //                         'base_unit_name' => $unit->firstUnit
    //                             ? ($lang === 'en' ? $unit->firstUnit->name_en : $unit->firstUnit->name_ar)
    //                             : null,
    //                         'default_unit_name' => $unit->secondUnit
    //                             ? ($lang === 'en' ? $unit->secondUnit->name_en : $unit->secondUnit->name_ar)
    //                             : null,
    //                     ];
    //                 }),
    //             ];
    //         });

    //         //  Quantity list from ProductTransaction (for statistics)
    //         $quantities = ProductTransaction::selectRaw('product_brand_id, SUM(quantity) as total_quantity')
    //             ->groupBy('product_brand_id')
    //             ->with(['products.product', 'products.brand'])
    //             ->get()
    //             ->map(function ($row) use ($lang) {
    //                 return [
    //                     'product_brand_id' => $row->product_brand_id,
    //                     'product_name' => $row->products && $row->products->product
    //                         ? ($lang === 'en'
    //                             ? $row->products->product->name_en
    //                             : $row->products->product->name_ar)
    //                         : null,
    //                     'brand_name' => $row->products && $row->products->brand
    //                         ? ($lang === 'en'
    //                             ? $row->products->brand->name_en
    //                             : $row->products->brand->name_ar)
    //                         : null,
    //                     'quantity' => $row->total_quantity ?? 0,
    //                 ];
    //             })
    //             ->sortByDesc('quantity')
    //             ->values();

    //         //  Expiry product list (only products that have expiry)
    //         $expiryProducts = ProductBrand::where('is_have_expired', 1)
    //             ->with(['product', 'product.category', 'transactions'])
    //             ->get()
    //             ->map(function ($brand) use ($lang) {
    //                 $totalQuantity = $brand->transactions->sum('quantity');

    //                 return [
    //                     'product_brand_id' => $brand->id,
    //                     'product_name' => $brand->product
    //                         ? ($lang === 'en'
    //                             ? $brand->product->name_en
    //                             : $brand->product->name_ar)
    //                         : null,
    //                     'category_id' => $brand->product->category_id ?? null,
    //                     'category_name' => $brand->product && $brand->product->category
    //                         ? ($lang === 'en'
    //                             ? $brand->product->category->name_en
    //                             : $brand->product->category->name_ar)
    //                         : null,
    //                     'is_have_expired' => $brand->is_have_expired,
    //                     'expiration_type' => $brand->expiration_type,
    //                     'quantity' => $totalQuantity,
    //                 ];
    //             })
    //             ->where('quantity', '>', 0) // Only products with quantity
    //             ->sortByDesc('quantity')
    //             ->values();

    //         //  Response
    //         return response()->json([
    //             'code' => 200,
    //             'status' => true,
    //             'message' => 'Products statistics retrieved successfully',
    //             'data' => [
    //                 'products' => $products,
    //                 'quantities' => $quantities,
    //                 'expiry_products' => $expiryProducts,
    //                 'expiry_products_count' => $expiryProducts->count(),
    //             ],
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'code' => 500,
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //         ]);
    //     }
    // }
    /**
     * Dashboard statistics with category quantity analysis
     */
    /**
     * Dashboard statistics with category quantity analysis and total inventory value
     */
    public function home(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);

            //  Date filters
            $dateFilter = $request->input('date_filter', 'all'); // all, this_week, last_week
            $statusFilter = $request->input('status'); // available, low, expired, out_of_stock
            $categoryFilter = $request->input('category_id');

            // Date Filter: this_week, last_week, or all time
            // Status Filter: available, low_quantity, expired, out_of_stock
            // Category Filter: Specific category ID

            // Category Status Logic:
            // Out of Stock: 70%+ products have zero quantity
            // Expired: 50%+ products are expired
            // Low Quantity: 60%+ products are low or out of stock
            // Available: Healthy stock levels


            //  Calculate date ranges
            $dateRanges = $this->getDateRanges($dateFilter);

            //  Get ALL categories data with store and product store relationships
            $allCategories = Category::with([
                'products.productStores.store', // Include product stores and their stores
                'products.productBrands.transactions',
                'products.productBrands' => function ($query) {
                    $query->where('is_have_expired', 1);
                },
                'products.productBrands.units'
            ])->get();

            // Get date-filtered categories data ONLY for category_quantities_percentage
            $dateFilteredCategories = Category::with([
                'products.productStores.store',
                'products.productBrands.transactions' => function ($query) use ($dateRanges) {
                    if ($dateRanges['start']) {
                        $query->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
                    }
                },
                'products.productBrands' => function ($query) {
                    $query->where('is_have_expired', 1);
                },
                'products.productBrands.units'
            ])->get();

            //  Initialize total inventory values and requirement tracking
            $totalInventoryValue = 0;
            $dateFilteredInventoryValue = 0;
            $totalMinRequirement = 0;
            $totalMaxCapacity = 0;

            //  Calculate statistics for ALL categories (no date filter)
            $allCategoryStats = $allCategories->map(function ($category) use ($lang, &$totalInventoryValue, &$totalMinRequirement, &$totalMaxCapacity) {
                return $this->calculateCategoryStats($category, $lang, $totalInventoryValue, $totalMinRequirement, $totalMaxCapacity);
            });

            //  Calculate statistics for date-filtered categories (ONLY for category_quantities_percentage)
            $dateFilteredCategoryStats = $dateFilteredCategories->map(function ($category) use ($lang, &$dateFilteredInventoryValue) {
                return $this->calculateCategoryStats($category, $lang, $dateFilteredInventoryValue);
            });

            //  Apply status filter ONLY to category_status_summary
            $filteredCategoryStats = $allCategoryStats;

            if ($statusFilter) {
                $filteredCategoryStats = $filteredCategoryStats->filter(function ($category) use ($statusFilter) {
                    return $category['status'] === $statusFilter;
                });
            }

            if ($categoryFilter) {
                $filteredCategoryStats = $filteredCategoryStats->where('category_id', $categoryFilter);
            }

            //  Get top 5 categories with lowest requirement coverage (from ALL data, no date filter)
            $lowQuantityCategories = $allCategoryStats
                ->where('total_quantity', '>', 0)
                ->sortBy('requirement_coverage') // Order by requirement_coverage (lowest first)
                ->take(5)
                ->values();

            // Overall statistics (from ALL data, no date filter)
            $totalCategories = $allCategories->count();
            $totalAllProducts = $allCategoryStats->sum('total_products');
            $totalAllQuantity = $allCategoryStats->sum('total_quantity');

            // Calculate total inventory value percentage compared to minimum requirement
            $totalInventoryValuePercentage = $this->calculateInventoryValuePercentage(
                $totalInventoryValue,
                $totalAllProducts,
                $totalAllQuantity,
                $totalMinRequirement,
                $totalMaxCapacity
            );

            $overallStats = [
                'total_categories' => $totalCategories,
                'total_products' => $totalAllProducts,
                'total_quantity' => $totalAllQuantity,
                'total_min_requirement' => $totalMinRequirement,
                'total_max_capacity' => $totalMaxCapacity,
                'average_quantity_per_product' => $totalAllProducts > 0 ? round($totalAllQuantity / $totalAllProducts, 2) : 0,
                'quantity_percentage' => $totalAllProducts > 0 ? round(($totalAllQuantity / ($totalAllProducts * 100)) * 100, 2) : 0,
                'total_inventory_value' => $totalInventoryValue,
                'total_inventory_value_percentage' => $totalInventoryValuePercentage,
            ];

            //  Category quantity percentages with inventory value (from DATE-FILTERED data only)
            $dateFilteredTotalQuantity = $dateFilteredCategoryStats->sum('total_quantity');
            $dateFilteredTotalValue = $dateFilteredInventoryValue;

            $categoryPercentages = $dateFilteredCategoryStats->map(function ($category) use ($dateFilteredTotalQuantity, $dateFilteredTotalValue) {
                $quantityPercentage = $dateFilteredTotalQuantity > 0 ? round(($category['total_quantity'] / $dateFilteredTotalQuantity) * 100, 2) : 0;
                $valuePercentage = $dateFilteredTotalValue > 0 ? round(($category['inventory_value'] / $dateFilteredTotalValue) * 100, 2) : 0;

                return [
                    'category_id' => $category['category_id'],
                    'category_name' => $category['category_name'],
                    'quantity' => $category['total_quantity'],
                    'percentage' => $quantityPercentage,
                    'inventory_value' => $category['inventory_value'],
                    'value_percentage' => $valuePercentage,
                    'status' => $category['status'],
                    'max_inventory_quantity' => $category['max_inventory_quantity'], // Maximum inventory quantity for this category
                    'min_requirement' => $category['min_requirement'],
                    'requirement_coverage' => $category['requirement_coverage'], // Percentage of minimum requirement covered
                ];
            })->sortBy('requirement_coverage') // Order by requirement_coverage (lowest first)
                ->values();

            //  Enhanced low quantity categories with requirement data
            $enhancedLowQuantityCategories = $lowQuantityCategories->map(function ($category) {
                return [
                    'category_id' => $category['category_id'],
                    'category_name' => $category['category_name'],
                    'total_quantity' => $category['total_quantity'],
                    'min_requirement' => $category['min_requirement'],
                    'percentage' => $category['requirement_coverage'], // Percentage of minimum requirement
                    'inventory_value' => $category['inventory_value'],
                    'status' => $category['status'],
                    'deficit' => max(0, $category['min_requirement'] - $category['total_quantity']), // How much below requirement
                ];
            });

            //  Create paginated structure for category_status_summary with your specific meta structure
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $total = $filteredCategoryStats->count();
            $items = $filteredCategoryStats->forPage($page, $perPage)->values();

            $categoryStatusSummary = [
                'data' => $items,
                'meta' => [
                    'totalItems' => $total,
                    'itemsPerPage' => (int) $perPage,
                    'currentPage' => (int) $page,
                    'totalPages' => ceil($total / $perPage),
                ]
            ];

            return response()->json([
                'code' => 200,
                'status' => true,
                'message' => 'Dashboard statistics retrieved successfully',
                'data' => [
                    'date_filter' => $dateFilter,
                    'overall_statistics' => $overallStats,
                    'low_quantity_categories' => $enhancedLowQuantityCategories, // Use enhanced version
                    'category_quantities_percentage' => $categoryPercentages, // Category Percentages uses date filter
                    'category_status_summary' => $categoryStatusSummary, // Category Status Summary uses status filter
                    'filters_applied' => [
                        'date_filter' => $dateFilter,
                        'status_filter' => $statusFilter,
                        'category_filter' => $categoryFilter,
                    ]
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    /**
     * Calculate category statistics with store requirements
     */
    private function calculateCategoryStats($category, $lang, &$inventoryValue, &$totalMinRequirement = 0, &$totalMaxCapacity = 0)
    {
        $totalProducts = 0;
        $totalQuantity = 0;
        $expiredProductsCount = 0;
        $lowQuantityProducts = 0;
        $outOfStockProducts = 0;
        $availableProducts = 0;
        $categoryValue = 0;
        $categoryMinRequirement = 0;
        $categoryMaxCapacity = 0;

        foreach ($category->products as $product) {
            $productQuantity = 0;
            $hasExpired = false;
            $productValue = 0;
            $productMinRequirement = 0;
            $productMaxCapacity = 0;

            // Calculate product quantity and value from transactions
            foreach ($product->productBrands as $brand) {
                $brandQuantity = $brand->transactions->sum('quantity');
                $productQuantity += $brandQuantity;

                // Calculate product value (quantity × price)
                $price = $brand->price ?? $brand->purchase_price ?? 0;
                $productValue += $brandQuantity * $price;

                // Check if product has expiration
                if ($brand->is_have_expired) {
                    $hasExpired = true;
                }
            }

            // Calculate store requirements for this product
            foreach ($product->productStores as $productStore) {
                $storeMinStorage = $productStore->store->min_storage ?? 0;
                $storeMaxStorage = $productStore->store->max_storage ?? 0;
                $productMinLimit = $productStore->min_limit ?? 0;
                $productMaxLimit = $productStore->max_limit ?? 0;

                // Use the most restrictive requirements
                $effectiveMin = max($storeMinStorage, $productMinLimit);
                $effectiveMax = min($storeMaxStorage, $productMaxLimit);

                $productMinRequirement += $effectiveMin;
                $productMaxCapacity += $effectiveMax;
            }

            $totalProducts++;
            $totalQuantity += $productQuantity;
            $categoryValue += $productValue;
            $categoryMinRequirement += $productMinRequirement;
            $categoryMaxCapacity += $productMaxCapacity;

            // Categorize product status considering requirements
            if ($productQuantity <= 0) {
                $outOfStockProducts++;
            } elseif ($hasExpired) {
                $expiredProductsCount++;
            } elseif ($productQuantity < $productMinRequirement) {
                $lowQuantityProducts++;
            } else {
                $availableProducts++;
            }
        }

        // Add to total inventory value and requirements
        $inventoryValue += $categoryValue;
        $totalMinRequirement += $categoryMinRequirement;
        $totalMaxCapacity += $categoryMaxCapacity;

        // Calculate requirement coverage percentage
        $requirementCoverage = $categoryMinRequirement > 0
            ? min(round(($totalQuantity / $categoryMinRequirement) * 100, 2), 100)
            : 0;

        // Determine category status
        $status = $this->getCategoryStatus($outOfStockProducts, $lowQuantityProducts, $expiredProductsCount, $totalProducts);

        return [
            'category_id' => $category->id,
            'category_name' => $lang === 'en' ? $category->name_en : $category->name_ar,
            'total_products' => $totalProducts,
            'total_quantity' => $totalQuantity,
            'available_products' => $availableProducts,
            'low_quantity_products' => $lowQuantityProducts,
            'expired_products' => $expiredProductsCount,
            'out_of_stock_products' => $outOfStockProducts,
            'status' => $status,
            'percentage' => $totalProducts > 0 ? round(($totalQuantity / ($totalProducts * 100)) * 100, 2) : 0,
            'inventory_value' => $categoryValue,
            'min_requirement' => $categoryMinRequirement,
            'max_inventory_quantity' => $categoryMaxCapacity, // Maximum inventory quantity for this category
            'requirement_coverage' => $requirementCoverage, // Percentage of minimum requirement covered
        ];
    }

    /**
     * Calculate total inventory value percentage compared to minimum requirement
     */
    private function calculateInventoryValuePercentage($totalInventoryValue, $totalProducts, $totalQuantity = 0, $totalMinRequirement = 0, $totalMaxCapacity = 0)
    {
        if ($totalProducts == 0) return 0;

        // If we have minimum requirement data, use it for calculation
        if ($totalMinRequirement > 0) {
            // Calculate the value of minimum requirement (using average product value)
            $averageProductValue = $totalInventoryValue / max($totalQuantity, 1);
            $minRequirementValue = $totalMinRequirement * $averageProductValue;

            if ($minRequirementValue > 0) {
                $percentage = ($totalInventoryValue / $minRequirementValue) * 100;
                return min(round($percentage, 2), 100);
            }
        }

        // Fallback to original calculation if no requirement data
        if ($totalInventoryValue > 0) {
            $averageProductValue = $totalInventoryValue / $totalProducts;
            $targetAverageValue = 100;
            $percentage = ($averageProductValue / $targetAverageValue) * 100;
            return min(round($percentage, 2), 100);
        }

        if ($totalQuantity > 0) {
            $averageQuantity = $totalQuantity / $totalProducts;
            $percentage = min(($averageQuantity / 20) * 100, 100);
            return round($percentage, 2);
        }

        return 0;
    }

    /**
     * Get date ranges based on filter
     */
    private function getDateRanges($dateFilter)
    {
        $now = now();

        switch ($dateFilter) {
            case 'this_week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek()
                ];
            case 'last_week':
                return [
                    'start' => $now->copy()->subWeek()->startOfWeek(),
                    'end' => $now->copy()->subWeek()->endOfWeek()
                ];
            default:
                return [
                    'start' => null,
                    'end' => null
                ];
        }
    }

    /**
     * Determine category status based on product conditions
     */
    private function getCategoryStatus($outOfStock, $lowQuantity, $expired, $totalProducts)
    {
        if ($totalProducts === 0) {
            return 'out_of_stock';
        }

        if ($outOfStock >= $totalProducts * 0.7) {
            return 'out_of_stock';
        } elseif ($expired >= $totalProducts * 0.5) {
            return 'expired';
        } elseif (($lowQuantity + $outOfStock) >= $totalProducts * 0.6) {
            return 'low_quantity';
        } else {
            return 'available';
        }
    }
    // public function home(Request $request)
    // {
    //     try {
    //         $lang = $request->header('lang', 'ar');
    //         App::setLocale($lang);

    //         $dateFilter = $request->get('date', 'this_week'); // this_week or last_week
    //         $statusFilter = $request->get('status'); // Available, Low quantity, Expired, Out of stock
    //         $categoryFilter = $request->get('category_id');

    //         //  Date range filter
    //         $startOfWeek = now()->startOfWeek();
    //         $endOfWeek = now()->endOfWeek();

    //         if ($dateFilter === 'last_week') {
    //             $startOfWeek = now()->subWeek()->startOfWeek();
    //             $endOfWeek = now()->subWeek()->endOfWeek();
    //         }

    //         //  Get all categories with related products and transactions
    //         $categories = \App\Models\Category::with([
    //             'products.productBrands.transactions' => function ($q) use ($startOfWeek, $endOfWeek) {
    //                 $q->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
    //             },
    //             'products.productBrands.openingBalance'
    //         ])
    //             ->when($categoryFilter, fn($q) => $q->where('id', $categoryFilter))
    //             ->get();

    //         //  Map data
    //         $categoryStats = $categories->map(function ($category) use ($lang) {
    //             $totalExpected = 0;
    //             $totalAvailable = 0;
    //             $totalExpired = 0;

    //             foreach ($category->products as $product) {
    //                 foreach ($product->productBrands as $brand) {
    //                     // make sure transactions/openingBalance are safe to sum
    //                     $availableQty = collect($brand->transactions)->sum('quantity');
    //                     $expectedQty  = collect($brand->openingBalance)->sum('quantity');

    //                     // If no opening balance, assume expected = available
    //                     if ($expectedQty == 0 && $availableQty > 0) {
    //                         $expectedQty = $availableQty;
    //                     }

    //                     $totalExpected += $expectedQty;
    //                     $totalAvailable += $availableQty;

    //                     if ($brand->is_have_expired == 1) {
    //                         $totalExpired += $availableQty;
    //                     }
    //                 }
    //             }

    //             $percentage = $totalExpected > 0 ? ($totalAvailable / $totalExpected) * 100 : 0;

    //             //  Determine status
    //             $status = 'Out of stock';
    //             if ($totalExpired > 0) {
    //                 $status = 'Expired';
    //             } elseif ($percentage > 40 && $percentage <= 90) {
    //                 $status = 'Available';
    //             } elseif ($percentage > 0 && $percentage <= 40) {
    //                 $status = 'Low quantity';
    //             } elseif ($percentage == 0) {
    //                 $status = 'Out of stock';
    //             }

    //             return [
    //                 'category_id' => $category->id,
    //                 'name' => $lang === 'en' ? $category->name_en : $category->name_ar,
    //                 'percentage' => round($percentage, 2),
    //                 'status' => $status,
    //             ];
    //         });

    //         //  Top 5 lowest stock categories
    //         $lowestCategories = $categoryStats->sortBy('percentage')->take(5)->values();

    //         //  Total stock %
    //         $totalPercentage = $categoryStats->avg('percentage');

    //         //  Apply status filter if provided
    //         if ($statusFilter) {
    //             $categoryStats = $categoryStats->filter(fn($item) => $item['status'] === $statusFilter)->values();
    //         }

    //         //  Response
    //         return response()->json([
    //             'code' => 200,
    //             'status' => true,
    //             'message' => 'Dashboard data retrieved successfully',
    //             'data' => [
    //                 'total_stock_percentage' => round($totalPercentage, 2),
    //                 'lowest_categories' => $lowestCategories,
    //                 'categories' => $categoryStats,
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'code' => 500,
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //         ]);
    //     }
    // }
}
