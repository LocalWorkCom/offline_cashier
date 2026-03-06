<?php


namespace App\Services\Inventory_Services;

use App\Http\Resources\Inventory\PurchaseRequestResource;
use App\Models\DamyProduct;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseSetting;
use App\Models\RejectPurchaseRequest;
use App\Services\ProcurementServices\PurchaseOrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PurchaseRequestService
{
    public function generateAutoNumber()
    {
        $today = Carbon::today()->toDateString();

        // Get latest PR for today
        $latestPr = PurchaseRequest::whereDate('created_at', $today)
            ->orderBy('id', 'desc')
            ->first();

        if ($latestPr && preg_match('/PR-\d{8}-(\d+)/', $latestPr->pr_number, $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format: PR-YYYYMMDD-0001
        $prNumber = 'PR-' . now()->format('Ymd') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return $prNumber;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();

        // You can check module if needed
        $module = getCurrentModuleDependOnRoute($request, 'purchase-request');   // procurement | inventory
        $validator = Validator::make($request->all(), [
            'reason_pr_id'     => 'nullable|exists:reason_purchase_requests,id',
            'vendor_id'        => 'nullable|exists:vendors,id',
            'store_id'         => 'nullable|exists:stores,id',
            'created_by'       => 'nullable|exists:employees,id',
            'created_at_from'  => 'nullable|date_format:Y-m-d',
            'created_at_to'    => 'nullable|date_format:Y-m-d|after_or_equal:created_at_from',
            'status'           => 'nullable|string',
            'type'             => 'nullable|string',
            'priority'         => 'nullable|string',
            'department_id'    => 'nullable|exists:departments,id',
            'product_id'       => 'nullable|exists:products,id',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $query = PurchaseRequest::with([
            'items',
            'items.product',
            'vendor',
            'store',
            'department',
            'reason',
            'damyProducts'
        ])->whereNull('deleted_at');

        $filters = [
            'reason_pr_id',
            'vendor_id',
            'store_id',
            'created_by',
            'status',
            'type',
            'priority',
            'department_id'
        ];

        foreach ($filters as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('created_at_from')) {
            $query->whereDate('created_at', '>=', $request->created_at_from);
        }
        if ($request->filled('created_at_to')) {
            $query->whereDate('created_at', '<=', $request->created_at_to);
        }
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        // Product filter
        if ($request->filled('product_id')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }
        if ($module == 'procurement') {
            $query->where(function ($q) use ($employee) {
                $q->where('status', '!=', 'draft')
                    ->orWhere(function ($q2) use ($employee) {
                        $q2->where('status', 'draft')
                            ->where('created_by', $employee->id);
                    });
            });
        }

        $query->orderBy('created_at', 'desc');

        $purchaseRequests = paginateOrGetAll($query, $request);
        $purchaseRequests['data'] =
            PurchaseRequestResource::collection($purchaseRequests['data']);

        return ResponseWithSuccessDataPaginated($lang, $purchaseRequests, 1);
    }


    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();
        $module = getCurrentModuleDependOnRoute($request, 'purchase-request');   // procurement | inventory
        $rules = [];
        if ($module === 'procurement') {
            $rules = [
                'department_id' => [
                    'required',
                    Rule::exists('departments', 'id')->where(function ($q) {
                        $q->whereNull('deleted_at')->where('status', 'active');
                    }),
                ],
                'status'         => 'required|in:draft,submitted',
                'pr_number'      => 'nullable|string|unique:purchase_requests,pr_number',
                'pr_date'      => 'nullable|date|after_or_equal:today',
                'receive_date' => 'nullable|date|after_or_equal:today',
                'priority'       => 'required|in:Urgent,High,Medium,Low',
                'type'           => 'required|in:direct,indirect',
                'period' => 'required | integer|min:0',
                'employee_name'  => 'nullable|string',
                'employee_code'  => 'required|string',
                'has_new_product' => [
                    'required',
                    'in:0,1',
                    function ($attribute, $value, $fail) use ($request, $lang) {
                        if (!empty($request->product_items) && $value != true) {
                            $message = $lang === 'ar'
                                ? 'يجب أن يكون "has_new_product" صحيحًا إذا كانت هناك منتجات جديدة.'
                                : 'has_new_product must be true if there are product_items.';
                            $fail($message);
                        }
                    }
                ],
                'note'           => 'nullable|string',
                'reason_pr_id' => [
                    'required',
                    Rule::exists('reason_purchase_requests', 'id')->where(function ($q) {
                        $q->whereNull('deleted_at')->where('is_active', 1);
                    }),
                ],
                // Items for existing products
                'items'                     => 'required|array|min:1',
                'items.*.product_id' => [
                    'required',
                    Rule::exists('products', 'id')->where(function ($q) {
                        $q->whereNull('deleted_at');
                    }),
                ],
                'items.*.brand_id' => [
                    'required',
                    Rule::exists('brands', 'id')->where(function ($q) {
                        $q->whereNull('deleted_at')->where('is_active', 1);
                    }),
                ],
                'items.*.category_id' => [
                    'required',
                    Rule::exists('categories', 'id')->where(function ($q) {
                        $q->whereNull('deleted_at')->where('active', 1);
                    }),
                ],
                'items.*.ordered_quantity'  => 'required_with:items|numeric|min:0.01',
                'items.*.unit_id'           => 'required_with:items|exists:units,id',
                'items.*.note'              => 'nullable|string',

                // New product request
                'product_items'                 => 'nullable|array',
                'product_items.*.product_name'  => 'required_with:product_items|string',
                'product_items.*.category'      => 'required_with:product_items|string',
                'product_items.*.brand'         => 'required_with:product_items|string',
                'product_items.*.quantity'      => 'required_with:product_items|numeric|min:1',
                'product_items.*.unit'          => 'required_with:product_items|string',
                'product_items.*.note'          => 'nullable|string',
            ];
        }

        if ($module === 'inventory') {
            $rules = [
                'store_id'       => 'required|exists:stores,id',
                'status'         => 'required|in:draft,submitted',
                'pr_number'      => 'nullable|string|unique:purchase_requests,pr_number',
                'pr_date'        => 'nullable|date',
                'reason_pr_id'   => 'required|exists:reason_purchase_requests,id',
                'items'                     => 'required|array|min:1',
                'items.*.product_id'        => 'required_with:items|exists:product_brands,id',
                'items.*.ordered_quantity'  => 'required_with:items|numeric|min:0.01',
                'items.*.unit_id'           => 'required_with:items|exists:units,id',
                'items.*.note'              => 'nullable|string'
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }
        if ($module === 'inventory') {
            if (!$employee->hasRole('Inventory_Manager') && $request->status === 'submitted') {
                return respondError(
                    $lang == 'en'
                        ? 'You are not authorized to submit purchase request.'
                        : 'غير مصرح لك بإرسال طلب الشراء.',
                    403
                );
            }
        }

        $prNumber = 'PR-' . now()->format('Ymd') . '-' . str_pad(PurchaseRequest::count() + 1, 4, '0', STR_PAD_LEFT);

        $createdAt = $request->status === 'submitted' ? now() : null;
        $draftedAt = $request->status === 'draft' ? now() : null;

        // Common fields
        $data = [
            'pr_number'       => $prNumber,
            'status'          => $request->status,
            'reason_pr_id'    => $request->reason_pr_id,
            'pr_date'         => $request->pr_date ?? now(),

            'created_by'      => authActionSave()['by'] ?? null,
            'created_by_type' => authActionSave()['type'] ?? null,

            'submitted_at'    => $createdAt,
            'drafted_at'      => $draftedAt,
        ];

        // Inventory fields
        if ($module === 'inventory') {
            $data['store_id'] = $request->store_id;
            $data['priority'] = 'Low';
            $data['receive_date'] =  now()->toDateString();
            $data['period'] = 0;
            $data['employee_name']   = auth('employee')->user()->first_name;
            $data['employee_code']   =auth('employee')->user()->employee_code;
            $data['department_id']   = auth('employee')->user()->department_id;
            $data['type']            = 'indirect';
            $data['has_new_product'] = 0;
        }

        // Procurement fields
        if ($module === 'procurement') {

            if ($request->type === 'direct') {
                $data['priority'] = 'Urgent';
                $data['receive_date'] = now()->toDateString();
                $data['period'] = 0;
            } else {
                // normal behavior
                $data['priority'] = $request->priority;
                $data['receive_date'] = $request->receive_date;
                $data['period'] = $request->period;
            }
            $data['employee_name']   = $request->employee_name;
            $data['employee_code']   = $request->employee_code;
            $data['department_id']   = $request->department_id;
            $data['type']            = $request->type;
            $data['has_new_product'] = $request->has_new_product ?? 0;
            $data['note']            = $request->note;
        }
        $purchaseRequest = PurchaseRequest::create($data);

        // Store Items
        if ($module === 'procurement') {
            // Save new products
            if ($request->has_new_product == true && $request->product_items) {
                foreach ($request->product_items as $product_item) {
                    $this->storeProducts($product_item, $purchaseRequest->id);
                }
            }
            // Store Items
            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('category_id', $item['category_id'])
                    ->whereNull('deleted_at')
                    ->first();

                if (!$product) {
                    return respondError(
                        $lang == 'en'
                            ? 'Product does not belong to the selected category .'
                            : 'المنتج لا ينتمي إلى الفئة .',
                        400
                    );
                }
                $productBrand = ProductBrand::where('product_id', $item['product_id'])
                    ->where('brand_id', $item['brand_id'])
                    ->whereNull('deleted_at')
                    ->where('status', 'active')
                    ->first();

                if (!$productBrand) {
                    return respondError(
                        $lang == 'en'
                            ? 'Product ' . $item['product_id'] . ' does not have this brand assigned or is inactive.'
                            : 'المنتج ' . $item['product_id'] . ' لا يحتوي على هذه العلامة التجارية أو غير نشط.',
                        400
                    );
                }

                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'product_brand_id'    => $productBrand->id,
                    'ordered_quantity'    => $item['ordered_quantity'],
                    'unit_id'             => $item['unit_id'],
                    'note'                => $item['note'] ?? null,
                ]);
            }
        } else {
            foreach ($request->items as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'product_brand_id'    => $item['product_id'],
                    'ordered_quantity'    => $item['ordered_quantity'],
                    'unit_id'             => $item['unit_id'],
                    'note'                => $item['note'] ?? null,
                ]);
            }
        }


        return ResponseWithSuccessData($lang, null, 1);
    }

    public function showProducts(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $query = DamyProduct::query();

        if ($request->filled('pr_id')) {
            $query->where('pr_id', $request->input('pr_id'));
        }

        $products = $query->get();

        return ResponseWithSuccessData($lang, $products, 1);
    }

    private function storeProducts(array $request, $pr_id)
    {
        $product = new DamyProduct();
        $product->name = $request['product_name'];
        $product->category = $request['category'];
        $product->brand = $request['brand'];
        $product->quantity = $request['quantity'];
        $product->unit = $request['unit'];
        $product->note = $request['note'];
        $product->status = 1;
        $product->pr_id = $pr_id;
        $product->save();
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $purchaseRequest = PurchaseRequest::with([
            'items',
            'items.product.brand',
            'items.product.product.Category',
            'vendor',
            'store',
            'department',
            'reason',
            'damyProducts'
        ])->find($id);
        if (!$purchaseRequest) {
            return respondErrorData($lang == 'en' ? 'Purchase Request not found.' : 'طلب الشراء غير موجود .', 404);
        }
        $purchaseRequest = new PurchaseRequestResource($purchaseRequest);

        return ResponseWithSuccessData($lang, $purchaseRequest, 1);
    }
    public function updatedamyProductStatus(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();
        $damy_product = DamyProduct::find($id);
        if (!$damy_product) {
            return respondErrorData($lang == 'en' ? 'Product not found.' : 'منتج غير موجود', 404);
        }
        $damy_product->status = 0;
        $damy_product->added_live_by = $employee->id;
        $damy_product->save();
        $activeCount = DamyProduct::where('pr_id', $damy_product->pr_id)
            ->where('status', 1)
            ->count();

        // If no more active dummy products, update purchase request
        if ($activeCount === 0) {
            PurchaseRequest::where('id', $damy_product->pr_id)
                ->update([
                    'has_new_product' => 0
                ]);
        }
        return ResponseWithSuccessData($lang, null, 1);
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();
        $module = getCurrentModuleDependOnRoute($request, 'purchase-request'); // procurement | inventory

        $purchaseRequest = PurchaseRequest::find($id);
        if (!$purchaseRequest) {
            return respondError($lang == 'ar' ? 'طلب الشراء غير موجود' : 'Purchase request not found', 404);
        }

        if ($module === 'inventory' && $purchaseRequest->status !== 'draft') {
            return respondError(
                $lang == 'en'
                    ? 'Inventory purchase requests can only be updated in draft status.'
                    : 'يمكن تحديث طلبات الشراء للمخزن فقط في حالة المسودة.',
                403
            );
        }

        // Validation rules
        $rules = [];
        if ($module === 'procurement') {
            $rules = [
                'department_id' => ['required', Rule::exists('departments', 'id')->whereNull('deleted_at')->where('status', 'active')],
                'status' => 'required|in:draft,submitted',
                'pr_date' => 'nullable|date',
                'receive_date' => 'nullable|date|after_or_equal:today',
                'priority' => 'required|in:Urgent,High,Medium,Low',
                'type' => 'required|in:direct,indirect',
                'period' => 'required|integer|min:0',
                'employee_name' => 'nullable|string',
                'employee_code' => 'required|string',
                'has_new_product' => 'required|in:0,1',
                'note' => 'nullable|string',
                'reason_pr_id' => ['required', Rule::exists('reason_purchase_requests', 'id')->whereNull('deleted_at')->where('is_active', 1)],
            ];

            if ($purchaseRequest->status === 'draft') {
                $rules['items'] = 'required|array|min:1';
                $rules['items.*.product_id'] = 'required';
                $rules['items.*.brand_id'] = 'required';
                $rules['items.*.category_id'] = 'required';
                $rules['items.*.ordered_quantity'] = 'required|numeric|min:0.01';
                $rules['items.*.unit_id'] = 'required|exists:units,id';
                $rules['items.*.note'] = 'nullable|string';
            }

            // New products can always be added if provided
            if ($request->has_new_product && $request->damy_items && $purchaseRequest->status === 'draft') {
                $rules['damy_items'] = 'required|array|min:1';
                $rules['damy_items.*.product_name'] = 'required|string';
                $rules['damy_items.*.category'] = 'required|string';
                $rules['damy_items.*.brand'] = 'required|string';
                $rules['damy_items.*.quantity'] = 'required|numeric|min:1';
                $rules['damy_items.*.unit'] = 'required|string';
                $rules['damy_items.*.note'] = 'nullable|string';
            }
        }

        if ($module === 'inventory') {
            $rules = [
                'store_id' => 'required|exists:stores,id',
                'status' => 'required|in:draft,submitted',
                'reason_pr_id' => 'required|exists:reason_purchase_requests,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:product_brands,id',
                'items.*.ordered_quantity' => 'required|numeric|min:0.01',
                'items.*.unit_id' => 'required|exists:units,id',
                'items.*.note' => 'nullable|string',
            ];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return respondError($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.', 400, $validator->errors());
        }

        // Update main fields
        $purchaseRequest->update([
            'status' => $request->status,
            'reason_pr_id' => $request->reason_pr_id,
            'pr_date' => $request->pr_date ?? $purchaseRequest->pr_date,
            'priority' => $request->priority ?? $purchaseRequest->priority,
            'receive_date' => $request->receive_date ?? $purchaseRequest->receive_date,
            'period' => $request->period ?? $purchaseRequest->period,
            'employee_name' => $request->employee_name,
            'employee_code' => $request->employee_code,
            'department_id' => $request->department_id ?? $purchaseRequest->department_id,
            'type' => $request->type ?? $purchaseRequest->type,
            'has_new_product' => $request->has_new_product ?? 0,
            'note' => $request->note,
            'store_id' => $module === 'inventory' ? $request->store_id : $purchaseRequest->store_id,
            'submitted_at' => $request->status === 'submitted' ? now() : $purchaseRequest->submitted_at,
            'drafted_at' => $request->status === 'draft' ? now() : $purchaseRequest->drafted_at,
        ]);

        // === Handle items ===
        if ($purchaseRequest->status === 'draft') {
            // Draft: delete existing items and replace
            $purchaseRequest->items()->delete();
            foreach ($request->items as $item) {
                $productBrandId =  $item['product_id'];

                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'product_brand_id' => $productBrandId,
                    'ordered_quantity' => $item['ordered_quantity'],
                    'unit_id' => $item['unit_id'],
                    'note' => $item['note'] ?? null,
                ]);
            }
        } elseif ($purchaseRequest->status === 'submitted' && $module === 'procurement') {
            // Submitted: only add new items
            foreach ($request->items as $item) {
                if (!isset($item['id'])) { // new item
                    $productBrand = ProductBrand::where('product_id', $item['product_id'])
                        ->where('brand_id', $item['brand_id'])
                        ->first();

                    PurchaseRequestItem::create([
                        'purchase_request_id' => $purchaseRequest->id,
                        'product_brand_id' => $productBrand->id,
                        'ordered_quantity' => $item['ordered_quantity'],
                        'unit_id' => $item['unit_id'],
                        'note' => $item['note'] ?? null,
                    ]);
                }
            }
        }

        // Add new damy_items if provided (Procurement)
        if ($module === 'procurement' && $purchaseRequest->status === 'draft' && $request->has_new_product && $request->damy_items) {
            $purchaseRequest->damyProducts()->delete();

            foreach ($request->damy_items as $product_item) {
                $this->storeProducts($product_item, $purchaseRequest->id);
            }
        }

        return ResponseWithSuccessData($lang, null, 1);
    }


    // public function update(Request $request, $id)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);

    //     $employee = auth('employee')->user();

    //     $purchaseRequest = PurchaseRequest::with('items')->where('id', $id)
    //         ->where('status', 'draft')
    //         ->first();
    //     if (!$purchaseRequest) {
    //         return respondErrorData($lang == 'en' ? 'Purchase Request not found or not in draft status.' : 'طلب الشراء غير موجود أو ليس في حالة المسودة.', 404);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'store_id' => 'required|exists:stores,id',
    //         'status' => 'required|in:draft,submitted',
    //         'pr_number' => 'nullable|string|unique:purchase_requests,pr_number,' . $purchaseRequest->id,
    //         'pr_date' => 'nullable|date',
    //         'reason_pr_id' => 'required|exists:reason_purchase_requests,id',
    //         'items' => 'required:array|min:1',
    //         'items.*.product_id' => 'required_with:items|exists:product_brands,id',
    //         'items.*.ordered_quantity' => 'required_with:items|numeric|min:0.01',
    //         'items.*.unit_id' => 'required_with:items|exists:units,id',
    //         'items.note' => 'nullable|string',

    //     ]);

    //     if ($validator->fails()) {
    //         return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
    //     }
    //     if (!$employee->hasRole('Inventory_Manager') && $request->status == 'submitted') {
    //         return respondError(
    //             $lang == 'en'
    //                 ? 'You are not authorized to make purchase request submitted.'
    //                 : 'غير مصرح لك بإنشاء طلب شراء.',
    //             403
    //         );
    //     }
    //     if ($purchaseRequest->items()->count() === 0 && $request->status == 'submitted') {
    //         return respondErrorData(
    //             $lang == 'en' ? 'Cannot submit a purchase request without items.' : 'لا يمكن تقديم أمر شراء بدون عناصر.',
    //             400
    //         );
    //     }

    //     // if ($request->has('items')) {
    //     //     $productIds = array_column($request->input('items', []), 'product_id');
    //     //     $productBrands = ProductBrand::whereIn('product_id', $productIds)
    //     //         ->get()
    //     //         ->keyBy('product_id');

    //     //     foreach ($request->input('items', []) as $index => $item) {
    //     //         $productBrand = $productBrands->get($item['product_id']);

    //     //         $validUnits = array_filter([
    //     //             $productBrand->default_unit_id,
    //     //             $productBrand->base_unit_id,
    //     //         ]);
    //     //         if (!in_array($item['unit_id'], $validUnits)) {
    //     //             return respondError(
    //     //                 $lang == 'en' ? 'Invalid unit for product ' . $item['product_id'] : 'وحدة غير صالحة للمنتج ' . $item['product_id'],
    //     //                 400,
    //     //                 ['items.' . $index . '.unit_id' => $lang == 'en' ? 'The ordered unit must be the default or base unit for the selected product.' : 'يجب أن تكون الوحدة المطلوبة هي الوحدة الافتراضية أو الأساسية للمنتج المحدد.']
    //     //             );
    //     //         }
    //     //     }
    //     // }

    //     $createdAt = $request['status'] === 'submitted' && $purchaseRequest->status !== 'submitted' ? now() : $purchaseRequest->submitted_at;
    //     $draftedAt = $request['status'] === 'draft' && $purchaseRequest->status !== 'draft' ? now() : $purchaseRequest->drafted_at;

    //     $purchaseRequest->update([
    //         'pr_number' => $request['pr_number'] ?? $purchaseRequest->pr_number,
    //         'store_id' => $request['store_id'],
    //         'status' => $request['status'],
    //         'reason_pr_id' => $request['reason_pr_id'],
    //         'pr_date' => $request['pr_date'] ?? $purchaseRequest->pr_date,
    //         'modified_by' => authActionSave()['by'],
    //         'modified_by_type' => authActionSave()['type'],
    //         'submitted_at' => $createdAt,
    //         'drafted_at' => $draftedAt,
    //     ]);
    //     if ($request->status == 'submitted') {
    //         $purchaseRequest->update([
    //             'submitted_by' => authActionSave()['by'],
    //             'submitted_by_type' => authActionSave()['type'],
    //         ]);
    //     }

    //     $existingItemIds = $purchaseRequest->items->pluck('id')->toArray();
    //     $newItemIds = [];

    //     foreach ($request['items'] as $item) {
    //         if (isset($item['id']) && in_array($item['id'], $existingItemIds)) {
    //             $purchaseRequestItem = PurchaseRequestItem::find($item['id']);
    //             $purchaseRequestItem->update([
    //                 'product_brand_id' => $item['product_id'],
    //                 'ordered_quantity' => $item['ordered_quantity'],
    //                 'unit_id' => $item['unit_id'],
    //                 'note' => $item['note'] ?? null,
    //             ]);
    //             $newItemIds[] = $item['id'];
    //         } else {
    //             $newItem = PurchaseRequestItem::create([
    //                 'purchase_request_id' => $purchaseRequest->id,
    //                 'product_brand_id' => $item['product_id'],
    //                 'ordered_quantity' => $item['ordered_quantity'],
    //                 'unit_id' => $item['unit_id'],
    //                 'note' => $item['note'] ?? null,
    //             ]);
    //             $newItemIds[] = $newItem->id;
    //         }
    //     }
    //     $itemsToDelete = array_diff($existingItemIds, $newItemIds);
    //     if (!empty($itemsToDelete)) {
    //         PurchaseRequestItem::whereIn('id', $itemsToDelete)->delete();
    //     }
    //     $purchaseRequest = new PurchaseRequestResource($purchaseRequest);

    //     return ResponseWithSuccessData($lang, null, 1);
    // }
    public function approveOrReject(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();
        $module = getCurrentModuleDependOnRoute($request, 'purchase-request'); // procurement | inventory

        // Check roles
        // if (! $employee->hasRole('Inventory_Manager') && ! $employee->hasRole('Purchase_Manager')) {
        //     return respondError(
        //         $lang == 'en'
        //             ? 'You are not authorized to accept or reject purchase request.'
        //             : 'غير مصرح لك بالموافقة أو الرفض على طلب الشراء.',
        //         403
        //     );
        // }

        $purchaseRequest = PurchaseRequest::where('id', $id)
            ->where('status', 'submitted')
            ->first();

        if (!$purchaseRequest) {
            return respondErrorData(
                $lang == 'en'
                    ? 'Purchase request not found or not in submitted status.'
                    : 'أمر الشراء غير موجود أو ليس في حالة التقديم.',
                400
            );
        }
        $purchaseRequestProduct = PurchaseRequest::where('id', $id)
            ->where('has_new_product', 1)
            ->first();

        if ($purchaseRequestProduct) {
            return respondErrorData(
                $lang == 'en'
                    ? 'Purchase request cannot changed status as there are dumy products'
                    : 'لا يمكن تغير الحاله لوجود منتجات تحتاج للاضافه',
                400
            );
        }

        // Validation rules
        $rules = [
            'action' => 'required|in:approve,reject',
        ];

        // Conditional reject reason based on role
        if ($request->input('action') === 'reject') {

            if ($employee->hasRole('Inventory_Manager')) {

                $rules['reject_reason_id'] = 'required|exists:reject_purchase_requests,id';

            } else {
                // Purchase Manager
                $rules['reject_reason_id'] = 'required_without:name_ar,name_en|exists:reject_purchase_requests,id';
                $rules['name_ar'] = 'required_without:reject_reason_id|string|min:1';
                $rules['name_en'] = 'required_without:reject_reason_id|string|min:1';
            }
        }


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $action = $request->input('action');
        $updateData = [
            'status' => $action === 'approve' ? 'approved' : 'rejected',
        ];

        $authData = authActionSave();

        if ($action === 'approve') {
            $updateData['approved_by'] = $authData['by'];
            $updateData['approved_by_type'] = $authData['type'];
            $updateData['approved_at'] = now();
            if ($module == 'procurement') {
                //call creation po auto
                $check_po_auto_generate= PurchaseSetting::first();
                if( $check_po_auto_generate->auto_creation_po){
                    $purchaseOrderService = app(PurchaseOrderService::class);
                    $purchaseOrderService->filterPr($purchaseRequest);

                }

            }
        } else {
            $updateData['rejected_by'] = $authData['by'];
            $updateData['rejected_by_type'] = $authData['type'];
            $updateData['rejected_at'] = now();

            if ($request->filled('reject_reason_id')) {
                $updateData['reject_reason_id'] = $request->input('reject_reason_id');
            }
        }
        // Get reject reason names if rejected
        if ($action === 'reject' && !$request->filled('reject_reason_id')) {
            $reason = new RejectPurchaseRequest();
            $reason->name_ar = $request->name_ar;
            $reason->name_en = $request->name_en;
            $reason->is_active = 1;
            $reason->created_by = auth('employee')->user()->id;
            $reason->save();
            $updateData['reject_reason_id'] =   $reason->id;
        }
        $purchaseRequest->update($updateData);

        return ResponseWithSuccessData($lang, null, 1);

        // return RespondWithSuccessMsg(
        //     $lang == 'en'
        //         ? 'Purchase request ' . $action . 'd successfully.'
        //         : 'تم ' . ($action === 'approve' ? 'الموافقة على' : 'رفض') . ' أمر الشراء بنجاح.',

        // );
    }

    // public function approveOrReject(Request $request, $id)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);
    //     $employee = auth('employee')->user();

    //     $purchaseRequest = PurchaseRequest::where('id', $id)
    //         ->where('status', 'submitted')
    //         ->first();

    //     if (!$purchaseRequest) {
    //         return respondErrorData(
    //             $lang == 'en' ? 'Purchase request not found or not in submitted status.' : 'أمر الشراء غير موجود أو ليس في حالة التقديم.',
    //             400
    //         );
    //     }
    //     if (!$employee->hasRole('Inventory_Manager')) {
    //         return respondError(
    //             $lang == 'en'
    //                 ? 'You are not authorized to accept or reject purchase request.'
    //                 : 'غير مصرح لك بالموافقه او الرفض علي طلب شراء.',
    //             403
    //         );
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'action' => 'required|in:approve,reject',
    //         'reject_reason_id' => 'required_if:action,reject|exists:reject_purchase_requests,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
    //     }

    //     $action = $request->input('action');
    //     $updateData = [
    //         'status' => $action === 'approve' ? 'approved' : 'rejected',
    //     ];

    //     if ($action === 'approve') {
    //         $updateData['approved_by'] = authActionSave()['by'];
    //         $updateData['approved_by_type'] = authActionSave()['type'];
    //         $updateData['approved_at'] = now();
    //     } else {
    //         $updateData['rejected_by'] = authActionSave()['by'];
    //         $updateData['rejected_by_type'] = authActionSave()['type'];
    //         $updateData['reject_reason_id'] = $request->input('reject_reason_id');
    //         $updateData['rejected_at'] = now();
    //     }

    //     $purchaseRequest->update($updateData);

    //     return RespondWithSuccessMsg(
    //         $lang == 'en' ? 'Purchase request ' . $action . 'd successfully.' : 'تم ' . ($action === 'approve' ? 'الموافقة على' : 'رفض') . ' أمر الشراء بنجاح.'
    //     );
    // }
    public function deleteMultiple(Request $request, $ids = null)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $routeId = $request->route('id');

        // 2️⃣ Get IDs from body (if exists)
        $bodyIds = $request->input('ids', []);

        // 3️⃣ Merge both sources
        $ids = collect()
            ->when($routeId, fn ($c) => $c->push($routeId))
            ->merge(is_array($bodyIds) ? $bodyIds : [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        // ✅ Validate IDs
        if ($ids->isEmpty()) {
            return respondError(
                $lang === 'en'
                    ? 'No purchase request ID(s) provided.'
                    : 'لم يتم توفير أي معرف لطلب الشراء.',
                400
            );
        }


        DB::beginTransaction();

        $deleted = [];
        $errors = [];

        try {
            foreach ($ids as $id) {
                $purchaseRequest = PurchaseRequest::find($id);

                if (!$purchaseRequest) {
                    return respondError(__('validation.not_found'), 404);

                }

                if ($purchaseRequest->status !== 'draft') {
                    $message = $lang === 'en'
                        ? 'Only draft purchase requests can be deleted'
                        : 'يمكن حذف طلبات الشراء في حالة المسودة فقط';
                    return respondError($message, 400);

                }

                if ($purchaseRequest->purchaseOrder()->exists()) {
                    $message = $lang === 'en'
                        ? 'Purchase request already linked to purchase order'
                        : 'طلب الشراء مرتبط بأمر شراء بالفعل';
                    return respondError($message, 400);
                }

                //Soft delete
                $purchaseRequest->update([
                    'deleted_by' => authActionSave()['by'],
                    'deleted_by_type' => authActionSave()['type'],
                ]);

                $purchaseRequest->delete();
                $deleted[] = $id;
            }

            DB::commit();

            return ResponseWithSuccessData($lang, [
                'deleted_ids' => $deleted,
                'errors' => $errors,
            ], 1);

        } catch (\Throwable $e) {
            DB::rollBack();

            return respondError(
                $lang === 'en'
                    ? 'Failed to delete purchase requests'
                    : 'فشل حذف طلبات الشراء',
                500,
                $e->getMessage()
            );
        }
    }
}
