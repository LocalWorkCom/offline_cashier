<?php


namespace App\Services\Inventory_Services;

use App\Models\DirectSupplyPermission;
use App\Models\DirectSupplyPermissionItem;
use App\Models\DocumentReturnDsp;
use App\Models\InventoryEmployee;
use App\Models\ReturnDsp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReturnDspService
{

    public function index(Request $request)
    {

        return  ReturnDsp::with([
            'reason',
            'documents',
            'dsp.items.issues',
            'dsp.status',
            'dsp.fromStore',
            'dsp.toStore',
            'dsp.qaTester',
            'dsp.purchaseRequest',
            'dsp.supplyOrder'
        ]);
    }

    public function store(Request $request)
    {

        $createdAt = $request['status'] === 'submitted' ? now() : null;

        $ReturnDsp = new ReturnDsp();
        $ReturnDsp->dsp_id = $request->dsp_id;
        $ReturnDsp->reason_id = $request->reason_id;
        $ReturnDsp->items = $request->items;
        $ReturnDsp->quantity = $request->quantity;
        $ReturnDsp->status = $request->status;
        $ReturnDsp->submitted_at = $createdAt;
        $ReturnDsp->created_by = authActionSave()['by'];
        $ReturnDsp->created_by_type = authActionSave()['type'];
        if ($request->status == 'submitted') {
            $ReturnDsp->submitted_by = authActionSave()['by'];
            $ReturnDsp->submitted_by_type = authActionSave()['type'];
        }
        $ReturnDsp->save();

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = 'DSP/documents';

                if (!file_exists(public_path($path))) {
                    mkdir(public_path($path), 0777, true);
                }
                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path($path), $filename);

                $document = new DocumentReturnDsp();
                $document->file_path = url($path . '/' . $filename);
                $document->original_name = $file->getClientOriginalName();
                $document->mime_type = $file->getClientMimeType();
                $document->return_dsp_id = $ReturnDsp->id;
                $document->save();
            }
        }
        $lang = $request->header('lang', 'ar');
        $this->send_notification_to_warehouse_contact($lang, $ReturnDsp);

        return $ReturnDsp;
    }

    public function send_notification_to_warehouse_contact($lang, $ReturnDsp)
    {
        app()->setLocale($lang);
        $employee = auth('employee')->user();

        $dsp = DirectSupplyPermission::find($ReturnDsp->dsp_id);

        $employee_full_name = $employee->first_name . ' ' . $employee->last_name;


        $body_ar = 'تم طلب اذن ارجاع منتجات من اذن الشراء المباشر رقم ' . $dsp->id . ' بواسطة الموظف ' . $employee_full_name . '.';

        $body_en = 'A return permission request has been made for Direct Supply Permission No. ' . $dsp->id . ' by employee ' . $employee_full_name . '.';

        $title_ar = 'طلب ارجاع اذن شراء مباشر';
        $title_en = 'Return DSP Request';

        $to_inventory_employees = InventoryEmployee::where('store_id', $dsp->to_store_id)
            ->get();
        $from_inventory_employees = InventoryEmployee::where('store_id', $dsp->from_store_id)->with('employee')
            ->get();

        foreach ($to_inventory_employees as $to_inventory_employee) {
            if ($to_inventory_employee->employee->device_token) {
                send_push_notification(
                    $to_inventory_employee->device_token,
                    $body_ar,
                    $body_en,
                    $title_ar,
                    $title_en,
                    'inventory manager',
                    $to_inventory_employee->id,
                    $employee->id,
                    $employee->id,
                    $lang,
                    7
                );
            }
        }
        foreach ($from_inventory_employees as $from_inventory_employee) {
            if ($from_inventory_employee->employee->device_token) {
                send_push_notification(
                    $from_inventory_employee->device_token,
                    $body_ar,
                    $body_en,
                    $title_ar,
                    $title_en,
                    'inventory manager',
                    $from_inventory_employee->id,
                    $employee->id,
                    $employee->id,
                    $lang,
                    7
                );
            }
        }
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $employee = auth('employee')->user();

        // if (!$employee->hasRole('Inventory_Manager') && !$employee->hasRole('Inventory_Employee')) {
        //     return respondError(
        //         $lang == 'en'
        //             ? 'You are not authorized to update return DSP.'
        //             : 'غير مصرح لك بتعديل طلب ارجاع اذن شراء مباشر.',
        //         403
        //     );
        // }

        $ReturnDsp = ReturnDsp::where('id', $id)
            ->where('status', 'draft')
            ->first();
        if (!$ReturnDsp) {
            return respondErrorData($lang == 'en' ? 'return Request not found or not in draft status.' : 'طلب الارجاع غير موجود أو ليس في حالة المسودة.', 404);
        }

        $validator = Validator::make($request->all(), [
            'dsp_id' => 'required',
            'status' => 'required|in:draft,submitted',
            // 'reason_id' => 'required|exists:reasone_return_dsps,id',
            // 'items' => 'required|array|min:1',
            // 'items.*' => [
            //     'integer',
            //     Rule::exists('direct_supply_permission_items', 'item_id')
            //         ->where('dsp_id', $request->dsp_id),
            // ],
            // 'quantity'  => 'required|array|min:1|size:' . count($request->items),
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:png,jpg,jpeg,pdf,doc,docx',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        // if (!$employee->hasRole('Inventory_Manager') && $request->status == 'submitted') {
        //     return respondError(
        //         $lang == 'en'
        //             ? 'You are not authorized to make purchase request submitted.'
        //             : 'غير مصرح لك بإنشاء طلب شراء.',
        //         403
        //     );
        // }

        $createdAt = $request['status'] === 'submitted' && $ReturnDsp->status !== 'submitted' ? now() : $ReturnDsp->submitted_at;

        $ReturnDsp->dsp_id = $request->dsp_id;
        // $ReturnDsp->reason_id = $request->reason_id;
        // $ReturnDsp->items = $request->items;
        $ReturnDsp->status = $request->status;
        $ReturnDsp->note = $request->note;

        // $ReturnDsp->quantity = $request->quantity;
        $ReturnDsp->submitted_at = $createdAt;
        $ReturnDsp->modified_by = authActionSave()['by'];
        $ReturnDsp->modified_by_type = authActionSave()['type'];
        if ($request->status == 'submitted') {
            $ReturnDsp->submitted_by = authActionSave()['by'];
            $ReturnDsp->submitted_by_type = authActionSave()['type'];
        }
        $ReturnDsp->save();

        if ($request->hasFile('documents')) {
            DocumentReturnDsp::where('return_dsp_id', $ReturnDsp->id)->delete();
            foreach ($request->file('documents') as $file) {
                $path = 'DSP/documents';

                if (!file_exists(public_path($path))) {
                    mkdir(public_path($path), 0777, true);
                }
                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path($path), $filename);

                $document = new DocumentReturnDsp();
                $document->file_path = url($path . '/' . $filename);
                $document->original_name = $file->getClientOriginalName();
                $document->mime_type = $file->getClientMimeType();
                $document->return_dsp_id = $ReturnDsp->id;
                $document->save();
            }
        }
        return RespondWithSuccessRequest($lang, 1);
    }

    public function show(Request $request, $id)
    {

        $lang = $request->header('lang', 'ar');
        $ReturnDsp = ReturnDsp::with(['reason', 'documents'])->find($id);
        if (!$ReturnDsp) {
            return respondErrorData($lang == 'en' ? 'return Request not found.' : 'طلب الارجاع غير موجود.', 404);
        }
        return ResponseWithSuccessData($lang, $ReturnDsp, 1);
    }

    public function delete(Request $request, $id)
    {
        $lang = app()->getLocale();
        $employee = auth()->user();

        // if (!$employee->hasRole('Inventory_Manager') && !$employee->hasRole('Inventory_Employee')) {
        //     return respondError(
        //         $lang == 'en'
        //             ? 'You are not authorized to delete return DSP.'
        //             : 'غير مصرح لك بحذف طلب ارجاع اذن شراء مباشر.',
        //         403
        //     );
        // }

        $ReturnDsp = ReturnDsp::where('id', $id)
            ->where('status', 'draft')
            ->first();
        if (!$ReturnDsp) {
            return respondErrorData($lang == 'en' ? 'return Request not found or not in draft status.' : 'طلب الارجاع غير موجود أو ليس في حالة المسودة.', 404);
        }

        DB::beginTransaction();
        try {
            DocumentReturnDsp::where('return_dsp_id', $ReturnDsp->id)->delete();

            $ReturnDsp->deleted_by = authActionSave()['by'];
            $ReturnDsp->deleted_by_type = authActionSave()['type'];
            $ReturnDsp->save();
            $ReturnDsp->delete();

            DB::commit();
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            DB::rollBack();
            return respondError($e->getMessage(), 500);
        }
    }

    public function approveOrReject(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();

        $ReturnDsp = ReturnDsp::where('id', $id)
            ->where('status', 'submitted')
            ->first();

        if (!$ReturnDsp) {
            return respondErrorData(
                $lang == 'en' ? 'return request not found or not in submitted status.' : 'أمر الارجاع غير موجود أو ليس في حالة التقديم.',
                400
            );
        }
        // if (!$employee->hasRole('Inventory_Manager')) {
        //     return respondError(
        //         $lang == 'en'
        //             ? 'You are not authorized to accept or reject return request.'
        //             : 'غير مصرح لك بالموافقه او الرفض علي طلب الارجاع.',
        //         403
        //     );
        // }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $action = $request->input('action');
        $updateData = [
            'status' => $action === 'approve' ? 'approved' : 'rejected',
        ];

       if ($action === 'approve') {
            $updateData['approved_by'] = authActionSave()['by'];
            $updateData['approved_by_type'] = authActionSave()['type'];
            $updateData['approved_at'] = now();

            // foreach ($ReturnDsp->dspItems as $issue) {
            //     $quantityToReturn = $issue->received_quantity;
            //                     $requestesToReturn = $issue->quantity;

            //     // $dspItem = DirectSupplyPermissionItem::where('id', $issue->dsp_item_id)->first();
            //     // // if ($dspItem) {
            //     //     if ($requestesToReturn >= $quantityToReturn) {
            //     //         $requestesToReturn -= $quantityToReturn;
            //     //         $dspItem->save();
            //     //     // } else {
            //     //     //     return respondErrorData(
            //     //     //         $lang == 'en'
            //     //     //             ? 'Return quantity exceeds available quantity for item ID: ' . $dspItem->id
            //     //     //             : 'كمية الارجاع تتجاوز الكمية المتاحة لمعرف الصنف: ' . $dspItem->id,
            //     //     //         400
            //     //     //     );
            //     //     // }
            //     // } else {
            //     //     return respondErrorData(
            //     //         $lang == 'en'
            //     //             ? 'Item not found for issue ID: ' . $issue->id
            //     //             : 'الصنف غير موجود لمعرف مشكلة الصنف: ' . $issue->id,
            //     //         400
            //     //     );
            //     // }
            // }
        }else {
            $updateData['rejected_by'] = authActionSave()['by'];
            $updateData['rejected_by_type'] = authActionSave()['type'];
            $updateData['reject_reason_id'] = $request->input('reject_reason_id');
            $updateData['rejected_at'] = now();
        }

        $ReturnDsp->update($updateData);

        return RespondWithSuccessMsg(
            $lang == 'en' ? 'return request ' . $action . 'd successfully.' : 'تم ' . ($action === 'approve' ? 'الموافقة على' : 'رفض') . ' أمر الارجاع بنجاح.'
        );
    }
}
