<?php


namespace App\Services\ProcurementServices;

use App\Http\Resources\purchase\PurchaseOrderResource;
use App\Models\DamyProduct;
use App\Models\PoMergeLog;
use App\Models\PricingDeal;
use App\Models\PricingDealItem;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDeal;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\RejectPurchaseRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PurchaseOrderService
{
    public function generateAutoNumber()
    {
        $today = now()->format('Y-m-d');

        // Get the latest PO created today
        $latestPo = PurchaseOrder::whereDate('created_at', $today)
            ->orderBy('id', 'desc')
            ->first();

        if ($latestPo && preg_match('/PO-\d{8}-(\d+)/', $latestPo->po_number, $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format: PO-YYYYMMDD-0001
        $prNumber= 'PO-' . now()->format('Ymd') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return $prNumber;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();

        // You can check module if needed
        $module = getCurrentModuleDependOnRoute($request, 'purchase-order');   // procurement | inventory

        $query = PurchaseOrder::with([
            'pr',
            'category',
            'deals','deals.priceDeal.vendor','purchaseRequest',
            'items','toDepartment','employee'
        ])->whereNull('deleted_at');

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $query->orderBy('created_at', 'desc');

        $purchaseRequests = paginateOrGetAll($query, $request);
        $purchaseRequests['data'] =
            PurchaseOrderResource::collection($purchaseRequests['data']);

        return ResponseWithSuccessDataPaginated($lang, $purchaseRequests, 1);
    }


    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        $rules = [
            'to_department_id' => [
                'required',
                Rule::exists('departments', 'id')
                    ->whereNull('deleted_at')
                    ->where('status', 'active'),
            ],
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')
                    ->whereNull('deleted_at')
                    ->where('employee_status_id', 1),
            ],
            'main_category_id' => [
                'required',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at')
                    ->where('active', 1),
            ],
            'type_po'   => 'required|in:pr_linked,unlinked',
            'pr_id'     => 'nullable|exists:purchase_requests,id',
            'po_number' => 'nullable|string|unique:purchase_orders,po_number',

            'address' => 'required|string',
            'lat'     => 'nullable|string',
            'long'    => 'required|string',

            'type'     => 'required|in:direct,indirect',
            'priority' => 'nullable|in:Low,Medium,High,Urgent',

            'arraival_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'period'        => 'required|integer|min:0',
            'total'         => 'required|numeric|min:0',

            'note_delivery' => 'nullable|string',
            'note'          => 'nullable|string',

            'items' => 'required|array|min:1',

            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'items.*.brand_id' => [
                'required',
                Rule::exists('brands', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', 1),
            ],
            'items.*.category_id' => [
                'required',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at')
                    ->where('active', 1),
            ],
            'items.*.ordered_quantity' => 'required|numeric|min:0.01',
            'items.*.unit_id'          => 'required|exists:units,id',
            'items.*.price'            => 'required|numeric|min:0',
            'items.*.sub_total'        => 'required|numeric|min:0',
            'items.*.note'             => 'nullable|string',

            'pricing_deals_id'   => 'required|array|min:3',
            'pricing_deals_id.*.pricing_deals_id' => 'required|exists:pricing_deals,id',

        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return respondError(
                $lang === 'en' ? 'Validation Error' : 'خطأ في التحقق',
                422,
                $validator->errors()
            );
        }

        $itemCategoryIds = collect($request->items)
            ->pluck('category_id')
            ->unique()
            ->values();

        if ($itemCategoryIds->count() !== 1 || $itemCategoryIds->first() != $request->main_category_id) {
            return respondError(
                $lang === 'en'
                    ? 'All items must belong to the selected category'
                    : 'يجب أن تنتمي جميع الأصناف إلى الفئة المحددة',
                422
            );
        }

        $pricingDealIds = collect($request->pricing_deals_id)
            ->pluck('pricing_deals_id')
            ->filter()
            ->values()
            ->all();

//        if (!empty($pricingDealIds)) {
//            $productIds = collect($request->items)->pluck('product_id')->unique();
//            $categoryId = $request->category_id;
//
//            foreach ($pricingDealIds as $dealId) {
//                $validItemCount = PricingDealItem::where('pricing_deal_id', $dealId)
//                    ->where('category_id', $categoryId)
//                    ->whereIn('product_id', $productIds)
//                    ->count();
//
//                if ($validItemCount === 0) {
//                    return respondError(
//                        $lang === 'en'
//                            ? "Pricing deal #{$dealId} is invalid for selected products or category"
//                            : "عرض التسعير #{$dealId} غير صالح للأصناف أو الفئة المحددة",
//                        422
//                    );
//                }
//            }
//        }


        DB::beginTransaction();

        try {

            $purchaseOrder = $this->createPO(
                $request->all(),
                $request->items,
                $request->pricing_deals_id ?? []
            );

            DB::commit();

            return ResponseWithSuccessData($lang, [
                'po_id' => $purchaseOrder->id
            ], 1);

        } catch (\Throwable $e) {
            DB::rollBack();
            return respondError(
                $lang === 'en' ? 'Failed to create purchase order' : 'فشل إنشاء أمر الشراء',
                500,
                $e->getMessage()
            );
        }
    }
    public function filterPr(PurchaseRequest $pr)
    {
        $prItems = PurchaseRequestItem::with('product.product')
            ->where('purchase_request_id', $pr->id)
            ->whereHas('product.product', function($q) {
                $q->whereNotNull('category_id');
            })
            ->whereNull('deleted_at')
            ->get();

        // Group items by Product's category_id
        $itemsByCategory = $prItems->groupBy(function ($item) {
            return $item->product->product->category_id ?? null;
        });

        $createdPOs = [];
        DB::beginTransaction();
        try {
            foreach ($itemsByCategory as $categoryId => $items) {

                // Skip if category_id is null
                if (!$categoryId) continue;

                $poData = [
                    'type_po'          => 'pr_linked',
                    'pr_id'            => $pr->id,
                    'to_department_id' => auth('employee')->user()->department_id,
                    'employee_id'      => auth('employee')->id(),
                    'category_id'      => $categoryId,
                    'address'          => 'Default Address',
                    'type'             => $pr->type,
                    'priority'         => $pr->priority,
                    'arraival_date'    => $pr->arraival_date,
                    'period'           =>  $pr->period,
                    'total'            => $items->sum(function ($i) {
                        return $i->ordered_quantity * ($i->product->product->unit_price ?? 0);
                    }),
                    'status'           => 'draft',
                ];

                $poItems = $items->map(function ($item) {
                    return [
                        'product_id'       => $item->product_brand_id,
                        'brand_id'         => $item->product->brand_id ?? null,
                        'category_id'      => $item->product->product->category_id ?? null,
                        'unit_id'          => $item->unit_id,
                        'ordered_quantity' => $item->ordered_quantity,
                        'price'            => $item->product->product->unit_price ?? 0,
                        'sub_total'        => $item->ordered_quantity * ($item->product->product->unit_price ?? 0),
                        'note'             => $item->note,
                    ];
                });

                $createdPOs[] = $this->createPO($poData, $poItems->toArray());
            }

            DB::commit();
            return $createdPOs;

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function createPO(array $data, array $items, array $pricingDeals = [])
    {
        $poNumber = $data['po_number'] ?? 'PO-' . now()->format('Ymd') . '-' . str_pad(PurchaseOrder::count() + 1, 4, '0', STR_PAD_LEFT);
        $total = collect($items)->sum(function ($item) {
            return $item['sub_total']
                ?? (($item['ordered_quantity'] ?? 1) * ($item['price'] ?? 0));
        });
        // Direct / Indirect logic
        if ($data['type'] === 'direct') {
            $priority = 'Urgent';
            $period   = 0;
            $arraival = now()->toDateString();
        } else {
            $priority = $data['priority'];
            $period   = $data['period'];
            $arraival = $data['arraival_date'];
        }

        $purchaseOrder = PurchaseOrder::create([
            'po_number'        => $poNumber,
            'type_po'          => $data['type_po'],
            'pr_id'            => $data['pr_id'] ?? null,
            'to_department_id' => $data['to_department_id'],
            'employee_id'      => $data['employee_id'],
            'category_id'      => $data['main_category_id'],
            'address'          => $data['address'],
            'lat'              => $data['lat'] ?? null,
            'long'             => $data['long']?? null,
            'type'             => $data['type'],
            'priority'         => $priority,
            'arraival_date'    => $arraival,
            'period'           => $period,
            'total'            => $total,
            'note_delivery'    => $data['note_delivery'] ?? null,
            'note'             => $data['note'] ?? null,
            'status'           => $data['status'] ?? 'submitted',
        ]);

        // Create PO items
        foreach ($items as $item) {
            PurchaseOrderItem::create([
                'po_id'       => $purchaseOrder->id,
                'product_id'  => $item['product_id'],
                'brand_id'    => $item['brand_id'],
                'category_id' => $item['category_id'],
                'unit_id'     => $item['unit_id'],
                'quantity'    => $item['ordered_quantity'] ?? 1,
                'price'       => $item['price'] ?? 0,
                'sub_total'   => $item['sub_total'] ?? ($item['quantity'] * $item['price']),
                'notes'       => $item['note'] ?? null,
            ]);
        }

        // Attach pricing deals if provided
        if (!empty($pricingDeals)) {
            foreach ($pricingDeals as $dealId) {
                PurchaseOrderDeal::create([
                    'po_id'         => $purchaseOrder->id,
                    'price_deal_id' => $dealId['pricing_deals_id'],
                    'status'        => 'pending',
                ]);
            }
        }

        return $purchaseOrder;
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $purchaseOrder = PurchaseOrder::with([
            'pr',
            'category',
            'deals','deals.priceDeal.vendor','purchaseRequest',
            'items','toDepartment','employee'
        ])->find($id);
        if (!$purchaseOrder) {
            return respondErrorData($lang == 'en' ? 'Purchase Order not found.' : 'أمر الشراء غير موجود .', 404);
        }
        $module = getCurrentModuleDependOnRoute($request, 'purchase-order');   // procurement | inventory

        if ($purchaseOrder->fm_approval_id) {
            $purchaseOrder->setRelation(
                'deals',
                $purchaseOrder->deals->where('status', 'accept_pm')->values()
            );
        }

        $purchaseOrderResource = new PurchaseOrderResource($purchaseOrder);

        return ResponseWithSuccessData($lang, $purchaseOrderResource, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $purchaseOrder = PurchaseOrder::find($id);

        if (!$purchaseOrder) {
            return respondError(
                $lang === 'en' ? 'Purchase order not found' : 'أمر الشراء غير موجود',
                404
            );
        }

        if ($purchaseOrder->status !== 'draft') {
            return respondError(
                $lang === 'en' ? 'Only draft purchase orders can be updated' : 'يمكن تحديث أوامر الشراء في حالة المسودة فقط',
                422
            );
        }

        $rules = [
            'to_department_id' => ['required', Rule::exists('departments', 'id')->whereNull('deleted_at')->where('status', 'active')],
            'employee_id' => ['required', Rule::exists('employees', 'id')->whereNull('deleted_at')->where('employee_status_id', 1)],
            'main_category_id' => ['required', Rule::exists('categories', 'id')->whereNull('deleted_at')->where('active', 1)],

            'type_po' => 'required|in:pr_linked,unlinked',
            'pr_id' => 'nullable|exists:purchase_requests,id',
            'po_number' => ['nullable','string', Rule::unique('purchase_orders','po_number')->ignore($purchaseOrder->id)],

            'address' => 'required|string',
            'lat' => 'nullable|string',
            'long' => 'required|string',

            'type' => 'required|in:direct,indirect',
            'priority' => 'nullable|in:Low,Medium,High,Urgent',

            'arraival_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'period' => 'required|integer|min:0',
            'total' => 'required|numeric|min:0',

            'note_delivery' => 'nullable|string',
            'note' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'items.*.brand_id' => ['required', Rule::exists('brands', 'id')->whereNull('deleted_at')->where('is_active', 1)],
            'items.*.category_id' => ['required', Rule::exists('categories', 'id')->whereNull('deleted_at')->where('active', 1)],
            'items.*.ordered_quantity' => 'required|numeric|min:0.01',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.sub_total' => 'required|numeric|min:0',
            'items.*.note' => 'nullable|string',
            'pricing_deals_id'   => 'required|array|min:3',
            'pricing_deals_id.*.pricing_deals_id' => 'required|exists:pricing_deals,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return respondError(
                $lang === 'en' ? 'Validation Error' : 'خطأ في التحقق',
                422,
                $validator->errors()
            );
        }

        $itemCategoryIds = collect($request->items)
            ->pluck('category_id')
            ->unique()
            ->values();

        if ($itemCategoryIds->count() !== 1 || $itemCategoryIds->first() != $request->main_category_id) {
            return respondError(
                $lang === 'en' ? 'All items must belong to the selected category' : 'يجب أن تنتمي جميع الأصناف إلى الفئة المحددة',
                422
            );
        }

        if ($request->filled('pricing_deals_id')) {
            $productIds = collect($request->items)->pluck('product_id')->unique();
            $categoryId = $request->main_category_id;

            foreach ($request->pricing_deals_id as $dealId) {
                $validItemCount = PricingDealItem::where('pricing_deal_id', $dealId)
                    ->where('category_id', $categoryId)
                    ->whereIn('product_id', $productIds)
                    ->count();

                if ($validItemCount === 0) {
                    return respondError(
                        $lang === 'en' ? "Pricing deal #{$dealId} is invalid for selected products or category" : "عرض التسعير #{$dealId} غير صالح للأصناف أو الفئة المحددة",
                        422
                    );
                }
            }
        }

        DB::beginTransaction();

//        try {
            // Update purchase order fields
            $purchaseOrder->update([
                'po_number' => $request->po_number ?? $purchaseOrder->po_number,
                'type_po' => $request->type_po,
                'pr_id' => $request->pr_id,
                'to_department_id' => $request->to_department_id,
                'employee_id' => $request->employee_id,
                'category_id' => $request->main_category_id,
                'address' => $request->address,
                'lat' => $request->lat,
                'long' => $request->long,
                'type' => $request->type,
                'status' => $request->status ?? $purchaseOrder->status,
                'priority' => $request->type === 'direct' ? 'Urgent' : $request->priority,
                'arraival_date' => $request->type === 'direct' ? now()->toDateString() : $request->arraival_date,
                'period' => $request->type === 'direct' ? 0 : $request->period,
                'total' => $request->total,
                'note_delivery' => $request->note_delivery,
                'note' => $request->note,
            ]);

            // Delete old items & deals
            $purchaseOrder->items()->delete();
            $purchaseOrder->deals()->delete();

            // Insert new items
            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'po_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'brand_id' => $item['brand_id'],
                    'category_id' => $item['category_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['ordered_quantity'],
                    'price' => $item['price'],
                    'sub_total' => $item['sub_total'],
                    'notes' => $item['note'] ?? null,
                ]);
            }

            // Insert new deals
//            foreach ($request->pricing_deals_id as $dealId) {
//                PurchaseOrderDeal::create([
//                    'po_id' => $purchaseOrder->id,
//                    'price_deal_id' => $dealId,
//                    'status' => 'pending',
//                ]);
//            }
        if (!empty($request->pricing_deals_id)) {
            foreach ($request->pricing_deals_id as $dealId) {
                PurchaseOrderDeal::create([
                    'po_id'         => $purchaseOrder->id,
                    'price_deal_id' => $dealId['pricing_deals_id'],
                    'status'        => 'pending',
                ]);
            }
        }
            DB::commit();

            return ResponseWithSuccessData($lang, [
                'po_id' => $purchaseOrder->id
            ], 1);

//        } catch (\Throwable $e) {
//            DB::rollBack();
//            return respondError(
//                $lang === 'en' ? 'Failed to update purchase order' : 'فشل تحديث أمر الشراء',
//                500,
//                $e->getMessage()
//            );
//        }
    }

    public function deleteMultiple(Request $request, $ids = null)
    {
        $lang = $request->header('lang', 'ar');

        // Determine IDs
        if (is_null($ids)) {
            $ids = $request->input('ids', []);
        } elseif (!is_array($ids)) {
            $ids = [$ids];
        }

        if (empty($ids)) {
            return respondError(
                $lang === 'en' ? 'No purchase order ID(s) provided.' : 'لم يتم توفير أي معرف لأمر الشراء.',
                400
            );
        }

        $deleted = [];
        $errors = [];

        foreach ($ids as $id) {
            $purchaseOrder = PurchaseOrder::find($id);
            if (!$purchaseOrder) {
                $errors[] = "ID {$id}: " . ($lang === 'en' ? 'not found' : 'غير موجود');
                continue;
            }

            if ($purchaseOrder->status !== 'draft') {
                $errors[] = "ID {$id}: " . ($lang === 'en' ? 'cannot be deleted' : 'لا يمكن حذفه');
                continue;
            }

            $purchaseOrder->deleted_by = authActionSave()['by'];
            $purchaseOrder->save();
            $purchaseOrder->delete();

            $deleted[] = $id;
        }

        return ResponseWithSuccessData(
            $lang,
            [
                'deleted_ids' => $deleted,
                'errors'      => $errors,
            ],
            1
        );
    }

    public function approvePOByPM(Request $request, $poId)
    {
        $lang=$request->header('lang','ar');
        app()->setLocale($lang);
        $validator = Validator::make($request->all(), [
            'deal_ids' => 'required|array|min:3',
            'deal_ids.*' => 'exists:purchase_order_deals,id',
        ]);
        if ($validator->fails()) {
            return respondError(
                $lang === 'en' ? 'Validation Error' : 'خطأ في التحقق',
                422,
                $validator->errors()
            );
        }
        $po = PurchaseOrder::with('deals.priceDeal')->findOrFail($poId);
        // Filter deals that belong to this PO, are selected, and not expired
        $selectedDeals = $po->deals
            ->whereIn('id', $request->deal_ids)
            ->filter(function ($deal) {
                return $deal->priceDeal && $deal->priceDeal->end_date
                    && $deal->priceDeal->end_date >= now();
            });
        if ($selectedDeals->count() < 3) {
            return respondError("PO must have at least 3 active pricing deals for PM approval.", 400);
        }

        DB::transaction(function () use ($po, $selectedDeals) {
            $po->update([
                'status' => 'accept_po',
                'pm_approval_id' => authActionSave()['by'],
                'pm_approval_at' => now(),
            ]);

            // Approve only selected deals, reject the rest
            foreach ($po->deals as $deal) {
                $deal->update([
                    'status' => $selectedDeals->contains($deal) ? 'accept_pm' : 'rejected',
                ]);
            }

            activity('purchase-order')
                ->performedOn($po)
                ->causedBy(auth()->user())
                ->log('PO approved by PM with selected deals');
        });


        return ResponseWithSuccessData(
            $lang,
            null,
            1
        );    }
    public function approvePOByFM(Request $request, $poId)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'deal_id' => 'required|exists:purchase_order_deals,id',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang === 'en' ? 'Validation Error' : 'خطأ في التحقق',
                422,
                $validator->errors()
            );
        }

        $po = PurchaseOrder::with('deals.priceDeal')->findOrFail($poId);
        if (!$po) {
            return respondError( __('validation.not_found'),  404
            );
        }
        // Only allow FM approval if PO is already approved by PM
        if ($po->status !== 'accept_po') {
            return respondError(
                $lang === 'en' ? 'PO must be approved by PM before FM approval.' : 'يجب أن يتم اعتماد أمر الشراء من قبل مدير الشراء قبل اعتماد المدير المالي.',
                400
            );
        }

        // Filter deals: PM accepted and not expired
        $validDeals = $po->deals->filter(function ($deal) {
            return $deal->status === 'accept_pm'
                && $deal->priceDeal
                && $deal->priceDeal->end_date
                && $deal->priceDeal->end_date >= now();
        });

        $selectedDeal = $validDeals->where('id', $request->deal_id)->first();

        if (!$selectedDeal) {
            return respondError(
                $lang === 'en' ? 'Selected deal is not valid or expired.' : 'العرض المختار غير صالح أو انتهت صلاحيته.',
                400
            );
        }

        DB::transaction(function () use ($po, $selectedDeal) {
            // Update PO status and FM approval
            $po->update([
                'status' => 'accepted',
                'fm_approval_id' => authActionSave()['by'],
                'fm_approval_at' => now(),
            ]);

            // Approve the selected deal, reject the rest
            $selectedDeal->update([
                'status' => 'accept_fm',
            ]);
//call create supply order
            activity('purchase-order')
                ->performedOn($po)
                ->causedBy(auth()->user())
                ->log('PO approved by FM with selected deal');
        });

        return ResponseWithSuccessData($lang, null, 1);
    }

    public function reject(Request $request, $poId)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $validator = Validator::make($request->all(), [
            'reject_reason_id' => 'nullable|exists:reject_purchase_requests,id',
            'name_ar' => 'nullable|string|max:255|required_without:reject_reason_id',
            'name_en' => 'nullable|string|max:255|required_without:reject_reason_id',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang === 'en' ? 'Validation Error' : 'خطأ في التحقق',
                422,
                $validator->errors()
            );
        }

        $po = PurchaseOrder::find($poId);
        if(!$po){
            return respondError(__('validation.not_found'),404);
        }
        if (in_array($po->status, ['accept_fm', 'accepted','draft'])) {
            return respondError(
                $lang === 'en'
                    ? 'Cannot reject a PO that is already finalized.'
                    : 'لا يمكن رفض أمر شراء تم اعتماده نهائياً.',
                400
            );
        }

        DB::transaction(function () use ($po, $request) {

            $reasonId = $request->reject_reason_id;

            // Save custom reason if provided
            if (!$reasonId) {
                $reasonId = \DB::table('reject_purchase_requests')->insertGetId([
                    'name_ar' => $request->name_ar,
                    'name_en' => $request->name_en,
                    'created_at'=> now(),
                    'updated_at'=> now(),
                ]);
            }

            $employee = auth('employee')->user();
            $rejectedFrom = $employee->hasRole('Finance_Manager') ? 'fm' : 'pm';

            // Update PO with rejection info
            $po->update([
                'status' => 'rejected',
                'rejected_by' => authActionSave()['by'],
                'rejected_from' => $rejectedFrom,
                'rejected_at' => now(),
                'reject_reason_id' => $reasonId,
            ]);

            activity('purchase-order')
                ->performedOn($po)
                ->causedBy($employee)
                ->log("PO rejected by $rejectedFrom");
        });

        return ResponseWithSuccessData($lang, null, 1);
    }

    public function mergePOs(Request $request){
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $validator = Validator::make($request->all(), [
            'merge_po_ids'   => 'required|array|min:2',
            'merge_po_ids.*' => 'exists:purchase_orders,id',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang === 'en' ? 'Validation error' : 'خطأ في التحقق',
                400,
                $validator->errors()
            );
        }

        // Fetch POs with relations
        $purchaseOrders = PurchaseOrder::with(['items', 'deals'])
            ->whereIn('id', $request->merge_po_ids)
            ->get();

        $invalidStatus = $purchaseOrders->whereNotIn('status', ['draft']);
        if ($invalidStatus->isNotEmpty()) {
            return respondError(
                $lang === 'en'
                    ? 'Only draft or submitted POs can be merged'
                    : 'يمكن دمج أوامر الشراء في حالة مسودة فقط',
                400
            );
        }

        $categoryIds = $purchaseOrders->pluck('category_id')->unique();
        if ($categoryIds->count() > 1) {
            return respondError(
                $lang === 'en'
                    ? 'All purchase orders must belong to the same category'
                    : 'يجب أن تكون جميع أوامر الشراء من نفس الفئة',
                400
            );
        }
        $purchaseOrders = PurchaseOrder::with(['items', 'deals'])
            ->whereIn('id', $request->merge_po_ids)
            ->where('is_merged', 0)      // not already merged
            ->whereNull('deleted_at')    // not deleted
            ->get();

// Check if we missed any requested PO IDs
        $missingPOs = array_diff($request->merge_po_ids, $purchaseOrders->pluck('id')->toArray());
        if (!empty($missingPOs)) {
            return respondError(
                $lang === 'en'
                    ? 'Some POs are deleted or already merged'
                    : 'بعض أوامر الشراء تم دمجها بالفعل أو تم حذفها',
                400,
                ['merge_po_ids' => $missingPOs]
            );
        }

        DB::transaction(function () use ($purchaseOrders, &$mergedPO) {

            $firstPO = $purchaseOrders->first();
            // Create new merged PO
            $mergedPO = PurchaseOrder::create([
                'po_number'        => $this->generateAutoNumber(),
                'type_po'          => 'unlinked',
                'pr_id'            => null,
                'to_department_id' => $firstPO->to_department_id,
                'employee_id'      => authActionSave()['by'],
                'category_id'      => $firstPO->category_id,
                'address'          => $firstPO->address,
                'lat'              => $firstPO->lat,
                'long'             => $firstPO->long,
                'type'             => 'indirect',
                'priority'         => 'low',
                'status'           => 'draft',
                'total'            => 0,
            ]);

            $total = 0;
            $this->saveMergeLog($mergedPO, $purchaseOrders);

            foreach ($purchaseOrders as $po) {

                // Move items to new PO
                foreach ($po->items as $item) {
                    $subTotal = $item->sub_total;

                    $item->update([
                        'po_id' => $mergedPO->id,

                    ]);

                    $total += $subTotal;
                }

                // Move deals to new PO
                foreach ($po->deals as $deal) {
                    $deal->update([
                        'po_id' => $mergedPO->id,
                        'status' => 'pending',
                    ]);
                }

                // Mark old PO as merged
                $po->update([
                    'is_merged'    => 1,
                    'merged_po_id' => $mergedPO->id,
                    'deleted_at' => now(),
                    'deleted_by'=> authActionSave()['by'],
                ]);

                // Optionally, you can delete the old PO
                 $po->delete();

                // Log each merged PO
                activity('purchase-order')
                    ->performedOn($po)
                    ->causedBy(auth('employee')->user())
                    ->log("PO #{$po->po_number} merged into PO #{$mergedPO->po_number}");
            }

            $mergedPO->update(['total' => $total]);

            // Log the creation of the merged PO
            activity('purchase-order')
                ->performedOn($mergedPO)
                ->causedBy(auth('employee')->user())
                ->log('Merged PO created from multiple POs');
        });

        return ResponseWithSuccessData($lang, [
            'merged_po_id' => $mergedPO->id
        ], 1);

    }
    private function saveMergeLog($mergedPO, $oldPOs)
    {
        // Collect old PO IDs
        $oldPOIds = $oldPOs->pluck('id')->toArray();

        // Collect linked PR IDs if any
        $linkedPRs = $oldPOs->pluck('pr_id')->filter()->values()->toArray();

        // Collect items
        $items = [];
        foreach ($oldPOs as $po) {
            foreach ($po->items as $item) {
                $items[] = [
                    'po_id'       => $po->id,
                    'product_id'  => $item->product_id,
                    'brand_id'    => $item->brand_id,
                    'category_id' => $item->category_id,
                    'unit_id'     => $item->unit_id,
                    'quantity'    => $item->quantity,
                    'price'       => $item->price,
                    'sub_total'   => $item->sub_total,
                ];
            }
        }

        // Collect deals
        $deals = [];
        foreach ($oldPOs as $po) {
            foreach ($po->deals as $deal) {
                $deals[] = [
                    'po_id'  => $po->id,
                    'deal_id'=> $deal->id,
                    'status' => $deal->status,
                ];
            }
        }

        // Save log
        PoMergeLog::create([
            'merged_po_id' => $mergedPO->id,
            'old_po_ids'   => $oldPOIds,
            'linked_prs'   => $linkedPRs,
            'items'        => $items,
            'deals'        => $deals,
            'created_by'   => Auth::id(),
        ]);
    }
}
