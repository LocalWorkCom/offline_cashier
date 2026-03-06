<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\DirectSupplyPermission;
use App\Models\Employee;
use App\Models\ProductBrand;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderHistory;
use App\Models\PurchaseOrderItem;
use App\Services\Inventory_Services\DirectSupplyPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    protected $dspService;
    public function __construct(DirectSupplyPermissionService $dspService)
    {
        $this->dspService = $dspService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $query = DirectSupplyPermission::whereNull('deleted_at');

        if ($request->has('status_id')) {
            $query->where('dsp_status_id', $request->status_id);
        }
        $dsps = paginateOrGetAll($query, $request, [], []);

        return ResponseWithSuccessDataPaginated($lang, $dsps, 1);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $dsp = DirectSupplyPermission::find($id);
        if (!$dsp) {
            return respondError(
                $lang == 'en' ? 'Purchase order not found.' : 'أمر الشراء غير موجود.',
                404
            );
        }
        $dsp->load(['items', 'creator', 'qaTester']);



        return ResponseWithSuccessData($lang, $dsp, 1);
    }
    //Store a new purchase order as draft/submitted
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();

        $validator = Validator::make($request->all(), [
            'date' => [
                'required',
                'date',
                'date_format:Y-m-d H:i:s',
            ],
            'type' => 'required|string|in:regular,sudden',
            'from_store_id' => 'required|exists:stores,id',
            'to_store_id' => 'required|exists:stores,id',
            'linked_pr_id' => 'nullable|exists:purchase_requests,id',
            'linked_so_id' => 'nullable|exists:supply_orders,id',
            'qa_tester_id' => 'nullable',
            'status' => [
                'nullable',
                'integer',
                'in:1,2',
            ],
            'items' => 'required|array|min:1',
            'items.*.category_id' => 'required|exists:categories,id',
            'items.*.item_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {

            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        // Custom validation for ordered_unit
        if ($request->has('items')) {
            $productIds = array_column($request->input('items', []), 'item_id');
            $productBrands = ProductBrand::whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');
            foreach ($request->input('items', []) as $index => $item) {
                $productBrand = $productBrands->get($item['item_id']);
                // dd($productBrands, $item['item_id'], $request->input('items', []));
                $validUnits = array_filter([
                    $productBrand->default_unit_id,
                    $productBrand->base_unit_id,
                ]);

                if (!in_array($item['unit_id'], $validUnits)) {
                    return respondError(
                        $lang == 'en' ? 'Invalid unit for product ' . $item['item_id'] : 'وحدة غير صالحة للمنتج ' . $item['item_id'],
                        400,
                        ['items.' . $index . '.unit_id' => $lang == 'en' ? 'The ordered unit must be the default or base unit for the selected product.' : 'يجب أن تكون الوحدة المطلوبة هي الوحدة الافتراضية أو الأساسية للمنتج المحدد.']
                    );
                }
            }
        }

        // Generate unique PO number (e.g., PO-20250702-0001)
        $poNumber = 'PO-' . now()->format('Ymd') . '-' . str_pad(DirectSupplyPermission::count() + 1, 4, '0', STR_PAD_LEFT);

        $request->merge(['po_number' => $poNumber]);
        $dsp = $this->dspService->createDSP($request->all());

        return ResponseWithSuccessData(
            $lang,
            $dsp->load(['items', 'creator', 'qaTester']),
            1
        );
    }
    //Update an existing purchase order with draft status
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();

        $dsp = DirectSupplyPermission::where('id', $id)
            ->where('dsp_status_id', 1)
            ->first();

        if (!$dsp) {
            return respondError(
                $lang == 'en' ? 'Purchase order not found or not in draft status.' : 'أمر الشراء غير موجود أو ليس في حالة المسودة.',
                404
            );
        }
        $validator = Validator::make($request->all(), [
            'status' => 'required|exists:direct_supply_permission_status_settings,id',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        // Custom validation for ordered_unit
        if ($request->has('items')) {
            $productIds = array_column($request->input('items', []), 'item_id');
            $productBrands = ProductBrand::whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');
            foreach ($request->input('items', []) as $index => $item) {
                $productBrand = $productBrands->get($item['item_id']);
                // dd($productBrands, $item['item_id'], $request->input('items', []));
                $validUnits = array_filter([
                    $productBrand->default_unit_id,
                    $productBrand->base_unit_id,
                ]);

                if (!in_array($item['unit_id'], $validUnits)) {
                    return respondError(
                        $lang == 'en' ? 'Invalid unit for product ' . $item['item_id'] : 'وحدة غير صالحة للمنتج ' . $item['item_id'],
                        400,
                        ['items.' . $index . '.unit_id' => $lang == 'en' ? 'The ordered unit must be the default or base unit for the selected product.' : 'يجب أن تكون الوحدة المطلوبة هي الوحدة الافتراضية أو الأساسية للمنتج المحدد.']
                    );
                }
            }
        }
        // what action will be according to status will be submitted
        $dsp = $this->dspService->updateStatus($dsp, $request['status']);


        return ResponseWithSuccessData(
            $lang,
            ['purchase_order' => $dsp->load(['items', 'creator', 'qaTester'])],
            1
        );
    }
    //Submit an existing purchase order with draft status
    public function submit(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();

        $purchaseOrder = DirectSupplyPermission::where('id', $id)
            ->where('status', 'draft')
            ->first();

        if (!$purchaseOrder) {
            return respondErrorData(
                $lang == 'en' ? 'Purchase order not found or not in draft status.' : 'أمر الشراء غير موجود أو ليس في حالة المسودة.',
                400
            );
        }

        if ($purchaseOrder->items()->count() === 0) {
            return respondErrorData(
                $lang == 'en' ? 'Cannot submit a purchase order without items.' : 'لا يمكن تقديم أمر شراء بدون عناصر.',
                400
            );
        }

        $purchaseOrder->update([
            'status' => 'submitted',
            'custom_status' => 2,
            'created_at' => now(),
            'created_by' => $employee->id,
        ]);

        // Log submission in PurchaseOrderHistory
        PurchaseOrderHistory::create([
            'purchase_order_id' => $purchaseOrder->id,
            'action' => 'submitted',
            'details' => json_encode([
                'message' => 'Purchase order submitted by employee #' . $employee->id,
                'po_number' => $purchaseOrder->po_number,
                'status' => 'submitted',
                'items_count' => $purchaseOrder->items()->count(),
            ]),
            'employee_id' => $employee->id,
        ]);


        return ResponseWithSuccessData(
            $lang,
            ['purchase_order' => $purchaseOrder->load(['items', 'vendor', 'store', 'createdBy'])],
            1
        );
    }
    //Approve or reject an existing purchase order with submitted status
    public function approveOrReject(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();

        $purchaseOrder = DirectSupplyPermission::where('id', $id)
            ->where('status', 'submitted')
            ->first();

        if (!$purchaseOrder) {
            return respondErrorData(
                $lang == 'en' ? 'Purchase order not found or not in submitted status.' : 'أمر الشراء غير موجود أو ليس في حالة التقديم.',
                400
            );
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $action = $request->input('action');
        $updateData = [
            'status' => $action === 'approve' ? 'approved' : 'rejected',
            'custom_status' => $action === 'approve' ? 3 : 4,
        ];

        if ($action === 'approve') {
            $updateData['approved_by'] = $employee->id;
            $updateData['approved_at'] = now();
        } else {
            $updateData['rejected_by'] = $employee->id;
            $updateData['rejected_at'] = now();
        }

        $purchaseOrder->update($updateData);

        // Log action in PurchaseOrderHistory
        PurchaseOrderHistory::create([
            'purchase_order_id' => $purchaseOrder->id,
            'action' => $action === 'approve' ? 'approved' : 'rejected',
            'details' => json_encode([
                'message' => 'Purchase order ' . ($action === 'approve' ? 'approved' : 'rejected') . ' by employee #' . $employee->id,
                'po_number' => $purchaseOrder->po_number,
                'status' => $action === 'approve' ? 'approved' : 'rejected',
                'items_count' => $purchaseOrder->items()->count(),
            ]),
            'employee_id' => $employee->id,
        ]);


        return RespondWithSuccessMsg(
            $lang == 'en' ? 'Purchase order ' . $action . 'd successfully.' : 'تم ' . ($action === 'approve' ? 'الموافقة على' : 'رفض') . ' أمر الشراء بنجاح.'
        );
    }
    //Receive an existing purchase order with approved status
    public function receive(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();

        $purchaseOrder = DirectSupplyPermission::where('id', $id)
            ->where('status', 'approved')
            ->with('items')
            ->first();

        if (!$purchaseOrder) {
            return respondErrorData(
                $lang == 'en' ? 'Purchase order not found or not in approved status.' : 'أمر الشراء غير موجود أو ليس في حالة الموافقة.',
                400
            );
        }

        $validator = Validator::make($request->all(), [
            'document_path' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:5120',
            'receiver_id' => 'required|exists:employees,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:product_brands,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.received_unit' => 'required|exists:units,id',
            'items.*.discrepancy_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }
        // Validate receiver_id has inventory flag
        $receiver = Employee::where('id', $request->input('receiver_id'))
            ->where('flag', 'inventory')
            ->first();

        if (!$receiver) {
            return respondErrorData(
                $lang == 'en' ? 'Receiver must be an inventory employee.' : 'يجب أن يكون المستلم موظف بالمخزن.',
                400
            );
        }

        // Validate that all items in the request match existing PurchaseOrderItems
        $existingItems = $purchaseOrder->items->keyBy('product_id');
        $requestProductIds = array_column($request->input('items', []), 'product_id');
        if (count($requestProductIds) !== count($existingItems)) {
            return respondErrorData(
                $lang == 'en' ? 'All existing items must be included in the request.' : 'يجب تضمين جميع العناصر الموجودة في الطلب.',
                400
            );
        }
        foreach ($requestProductIds as $productId) {
            if (!$existingItems->has($productId)) {
                return respondError(
                    $lang == 'en' ? 'Invalid product ID ' . $productId : 'معرف المنتج غير صالح ' . $productId,
                    400,
                    ['items.' . array_search($productId, $requestProductIds) . '.product_id' => $lang == 'en' ? 'The selected product ID does not exist in this purchase order.' : 'معرف المنتج المحدد غير موجود في أمر الشراء.']
                );
            }
        }

        // Custom validation for received_unit
        $productIds = array_column($request->input('items', []), 'product_id');
        $productBrands = ProductBrand::whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($request->input('items', []) as $index => $item) {
            $productBrand = $productBrands->get($item['product_id']);

            $validUnits = array_filter([
                $productBrand->default_unit_id,
                $productBrand->base_unit_id,
            ]);

            if (!in_array($item['received_unit'], $validUnits)) {
                return respondError(
                    $lang == 'en' ? 'Invalid received unit for product ' . $item['product_id'] : 'وحدة مستلمة غير صالحة للمنتج ' . $item['product_id'],
                    400,
                    ['items.' . $index . '.received_unit' => $lang == 'en' ? 'The received unit must be the default or base unit for the selected product.' : 'يجب أن تكون الوحدة المستلمة هي الوحدة الافتراضية أو الأساسية للمنتج المحدد.']
                );
            }
        }


        // Handle file upload for document_path
        $documentPath = $purchaseOrder->document_path;
        if ($request->hasFile('document_path') && $request->file('document_path')->isValid()) {
            if ($documentPath && Storage::disk('public')->exists($documentPath)) {
                Storage::disk('public')->delete($documentPath);
            }
            $documentPath = $request->file('document_path')->store('documents', 'public');
        }

        // Determine if all items match ordered values
        $allItemsMatch = true;
        foreach ($request['items'] as $item) {
            $existingItem = $existingItems->get($item['product_id']);
            if ($existingItem->ordered_quantity != $item['received_quantity'] || $existingItem->ordered_unit != $item['received_unit']) {
                $allItemsMatch = false;
                $hasDiscrepancy = 1;
                break;
            }
        }

        $purchaseOrder->update([
            'document_path' => $documentPath,
            'state' => $allItemsMatch ? 'received' : 'partially-received',
            'custom_status' => $allItemsMatch ? 5 : 6,
            'received_by' => $employee->id,
            'received_at' => now(),
        ]);

        foreach ($request['items'] as $item) {
            $existingItem = $existingItems->get($item['product_id']);
            $existingItem->update([
                'received_quantity' => $item['received_quantity'],
                'received_unit' => $item['received_unit'],
                'discrepancy_notes' => $item['discrepancy_notes'] ?? $existingItem->discrepancy_notes,
                'has_discrepancy' => $hasDiscrepancy ?? 0,
            ]);
        }

        // Log receiving in PurchaseOrderHistory
        PurchaseOrderHistory::create([
            'purchase_order_id' => $purchaseOrder->id,
            'action' => $allItemsMatch ? 'received' : 'partially-received',
            'details' => json_encode([
                'message' => 'Purchase order ' . ($allItemsMatch ? 'received' : 'partially received') . ' by employee #' . $employee->id,
                'po_number' => $purchaseOrder->po_number,
                'status' => $allItemsMatch ? 'received' : 'partially-received',
                'has_discrepancy' => $hasDiscrepancy ?? 0,
                'items_count' => count($request['items']),
                'document_path' => $documentPath,
            ]),
            'employee_id' => $employee->id,
        ]);

        return ResponseWithSuccessData(
            $lang,
            ['purchase_order' => $purchaseOrder->load(['items', 'vendor', 'store', 'receivedBy', 'receivedBy'])],
            1
        );
    }
    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();

        $purchaseOrder = DirectSupplyPermission::where('id', $id)
            ->where('status', 'draft')
            ->first();

        if (!$purchaseOrder) {
            return respondErrorData(
                $lang == 'en' ? 'Purchase order not found or not in draft status.' : 'أمر الشراء غير موجود أو ليس في حالة المسودة.',
                400
            );
        }

        $purchaseOrder->delete();

        // Soft delete related PurchaseOrderItems
        PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)->delete();

        // Log deletion in PurchaseOrderHistory
        PurchaseOrderHistory::create([
            'purchase_order_id' => $purchaseOrder->id,
            'action' => 'deleted',
            'details' => json_encode([
                'message' => 'Purchase order deleted by employee #' . $employee->id,
                'po_number' => $purchaseOrder->po_number,
                'status' => 'draft',
                'items_count' => $purchaseOrder->items()->count(),
            ]),
            'employee_id' => $employee->id,
        ]);


        return RespondWithSuccessMsg(
            $lang == 'en' ? 'Purchase order deleted successfully.' : 'تم حذف أمر الشراء بنجاح.'
        );
    }
    public function listInventoryEmployees(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employees = Employee::where('flag', 'inventory')->get();

        return ResponseWithSuccessData(
            $lang,
            ['employees' => $employees],
            1
        );
    }
}
