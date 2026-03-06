<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderWaste;
use App\Models\OrderWasteLog;
use App\Services\ClientServices\OrderService;
use App\Services\ClientServices\WasteService;
use App\Traits\OrderWasteLogTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderWasteController extends Controller
{
    protected $orderService;
    protected $wasteService;
    public function __construct(OrderService $orderService, WasteService $wasteService)
    {
        $this->orderService = $orderService;
        $this->wasteService = $wasteService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $wastes = OrderWaste::get();
        if ($wastes->count() > 0) {
            return ResponseWithSuccessData($lang, $wastes, 1);
        }
        return ResponseWithSuccessData($lang, null, 1);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|numeric|min:1|exists:orders,id',
            'invoice_id' => 'required|numeric|min:1|exists:invoices,id',
            'return_invoice_request_id' => 'required|numeric|min:1|exists:return_invoice_requests,id',
            'order_detail_id' => 'required_if:flag,dish|numeric|min:1|exists:order_details,id',
            'order_addon_id' => 'required_if:flag,addon|numeric|min:1|exists:order_addons,id',
            'waste_quantity' => 'required|numeric|min:1',
            'waste_reason_id' => 'required|numeric|min:1|exists:waste_reasons,id',
            'type' => 'required|in:waste,not_waste,temp',
            'flag' => 'required|string|in:dish,addon',
            'reused' => 'required|boolean',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang === 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $result = $this->wasteService->storeWaste($request->all(), $lang, auth('employee')->id());

        if ($result['error']) {
            return respondErrorData($result['message'], $result['code'] ?? 400);
        }

        return ResponseWithSuccessData($lang, $result['data'], 1);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $lang = $request->header('lang', 'ar');
        $waste = OrderWaste::with('logs')->find($id);
        if ($waste) {
            return ResponseWithSuccessData($lang, $waste, 1);
        }
        return RespondWithBadRequest($lang, 2);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //        dd($request->all());
        $lang = $request->header('lang', 'ar');

        $validator = Validator::make($request->all(), [
            'waste_quantity' => 'required|numeric|min:1',
            'waste_reason_id' => 'required|numeric|min:1|exists:waste_reasons,id',
            'type' => 'required|string|in:temporary,permanent',
            'reused' => 'required|boolean',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $waste = OrderWaste::find($id);
        if (!$waste) {
            return respondError(
                $lang == 'en' ? 'Waste record not found.' : 'سجل الهدر غير موجود.',
                404
            );
        }

        $order = Order::find($waste->order_id);
        if ($order->status == 'completed') {
            return respondErrorData(
                $lang == 'en' ? 'Order completed.' : 'طلب مكتمل.',
                400,
                $lang == 'en' ? ['The Order is completed already.'] : ['الطلب مكتمل بالفعل.']
            );
        }

        DB::beginTransaction();
        try {
            $oldData = $waste->toArray();

            if ($waste->flag == 'dish') {
                $order_detail = OrderDetail::find($waste->order_detail_id);
                if (($order_detail->quantity < $request->waste_quantity)) {
                    return respondErrorData(
                        $lang == 'en' ? 'Wrong quantity.' : 'كمية خاطئة.',
                        400,
                        $lang == 'en' ? ['The available quantity is less than the wasted quantity.'] : ['الكمية المتاحة أقل من كمية الهادر.']
                    );
                }

                $waste->original_quantity = (int)$order_detail->quantity;
                $waste->waste_quantity = (int)$request->waste_quantity;
                $waste->waste_reason_id = (int)$request->waste_reason_id;
                $waste->type = $request->type;
                $waste->reused = (int)$request->reused;
                $waste->note = $request->note ?? null;
                $waste->modified_by = auth('employee')->id();
                $waste->save();

                $newData = $waste->toArray();

                // Get changed fields
                $changedFields = $this->getChangedFields($oldData, $newData);

                // Log the update
                $this->logWasteAction(
                    $waste,
                    'update',
                    $oldData,
                    $changedFields,
                    $lang == 'en' ? 'Waste record updated' : 'تم تحديث سجل الهدر'
                );

                //                if (($order_detail->quantity - $waste->waste_quantity) == 0)
                //                {
                //                    $order_detail->status = 'cancel';
                //                    $order_detail->save();
                //                }
                //                else
                //                {
                //                    $order_detail->quantity = $waste->original_quantity - $waste->waste_quantity;
                //                    $order_detail->save();
                //                }
                //                $this->orderService->CalculateOrder($order->id);

            } elseif ($waste->flag == 'addon') {
                $order_addon = OrderAddon::find($waste->order_addon_id);
                if ($order_addon->quantity < $waste->waste_quantity) {
                    return respondErrorData(
                        $lang == 'en' ? 'Wrong quantity.' : 'كمية خاطئة.',
                        400,
                        $lang == 'en' ? ['The available quantity is less than the wasted quantity.'] : ['الكمية المتاحة أقل من كمية الهادر.']
                    );
                }

                $waste->original_quantity = (int)$order_addon->quantity;
                $waste->waste_quantity = (int)$request->waste_quantity;
                $waste->waste_reason_id = (int)$request->waste_reason_id;
                $waste->type = $request->type;
                $waste->reused = (int)$request->reused;
                $waste->note = $request->note ?? null;
                $waste->modified_by = auth('employee')->id();
                $waste->save();

                $newData = $waste->toArray();

                // Get changed fields
                $changedFields = $this->getChangedFields($oldData, $newData);

                // Log the update
                $this->logWasteAction(
                    $waste,
                    'update',
                    $oldData,
                    $changedFields,
                    $lang == 'en' ? 'Waste record updated' : 'تم تحديث سجل الهدر'
                );

                //                if (($order_addon->quantity - $waste->waste_quantity) == 0)
                //                {
                //                    $order_addon->status = 'cancel';
                //                }
                //                $order_addon->quantity = $waste->original_quantity - $waste->waste_quantity;
                //                $order_addon->save();
                //
                //                $this->orderService->CalculateOrder($order->id);
                //
                //                if (($waste->original_quantity - $waste->waste_quantity) == 0)
                //                {
                //                    $order_addon->quantity = $waste->original_quantity;
                //                    $order_addon->save();
                //                }
            }

            $waste->log = $lang == 'en' ? 'Waste record updated' : 'تم تحديث سجل الهدر';

            DB::commit();
            return ResponseWithSuccessData($lang, $waste, 1);
        } catch (\Exception $e) {
            DB::rollBack();
            return respondError(
                $lang == 'en' ? 'Failed to update waste record.' : 'فشل تحديث سجل الهدر.',
                500
            );
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $lang = $request->header('lang', 'ar');
        $waste = OrderWaste::find($id);
        if (!$waste) {
            return respondError(
                $lang == 'en' ? 'Waste record not found.' : 'سجل الهدر غير موجود.',
                404
            );
        }

        // Log before deletion
        $this->logWasteAction(
            $waste,
            'delete',
            $waste->toArray(),
            null,
            $lang == 'en' ? 'Waste record deleted' : 'تم حذف سجل الهدر'
        );

        $waste->delete();
        $waste->deleted_by = auth('employee')->id();
        $waste->save();

        $waste->log = $lang == 'en' ? 'Waste record deleted' : 'تم حذف سجل الهدر';
        return ResponseWithSuccessData($lang, $waste, 1);
    }

    public function changeWasteStatus(Request $request, string $id)
    {
        $lang = $request->header('lang', 'ar');
        $validator = Validator::make($request->all(), [
            'waste_reason_id' => 'required|numeric|min:1|exists:waste_reasons,id',
            'type' => 'required|string|in:temporary,permanent',
            'reused' => 'required|boolean',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $waste = OrderWaste::find($id);
        if (!$waste) {
            return respondError(
                $lang == 'en' ? 'Waste record not found.' : 'سجل الهدر غير موجود.',
                404
            );
        }

        $oldData = $waste->toArray();

        $waste->waste_reason_id = (int) $request->waste_reason_id;
        $waste->type = $request->type;
        $waste->reused = (int) $request->reused;
        $waste->note = $request->note ?? null;
        $waste->modified_by = auth('employee')->id();
        $waste->save();

        $newData = $waste->toArray();
        $changedFields = $this->getChangedFields($oldData, $newData);

        // Log the status change
        $this->logWasteAction(
            $waste,
            'status_change',
            $oldData,
            $changedFields,
            $lang == 'en' ? 'Waste status changed' : 'تم تغيير حالة الهدر'
        );

        $waste->log = $lang == 'en' ? 'Waste status changed' : 'تم تغيير حالة الهدر';

        return ResponseWithSuccessData($lang, $waste, 1);
    }

    public function getLogs(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $waste = OrderWaste::find($id);

        if (!$waste) {
            return respondError(
                $lang == 'en' ? 'Waste record not found.' : 'سجل الهدر غير موجود.',
                404
            );
        }

        $logs = OrderWasteLog::where('order_waste_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return ResponseWithSuccessData($lang, $logs, 1);
    }
}
