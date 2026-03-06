<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Models\AuditType;
use App\Models\IssueType;
use App\Models\ReasonList;
use Illuminate\Http\Request;
use App\Models\StatusTracking;
use App\Models\AdjustmentReason;
use App\Models\InventorySetting;
use App\Models\DiscrepancyReason;
use App\Models\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\ExpiryAndStockAlertSetting;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InventorySettingController extends Controller
{
    private function getAuthenticatedEmployee()
    {
        $employee = auth('employee')->user();
        if (!$employee) {
            abort(response()->json(['error' => 'Unauthorized'], 401));
        }
    }

    private function indexModel(Request $request, $model)
    {
        // $this->getAuthenticatedEmployee();
        try {
            $lang = $request->header('lang', 'en');

            return ResponseWithSuccessData($lang, $model::all(), 1);
            // return response()->json();
        } catch (\Exception $e) {
            return $this->errorResponse("Failed to retrieve list", $e);
        }
    }

    private function showModel(Request $request, $model, $id, $label = 'record')
    {
        // $this->getAuthenticatedEmployee();
        try {
            $lang = $request->header('lang', 'en');
            return ResponseWithSuccessData($lang, $model::findOrFail($id), 1);
            // return response()->json($model::findOrFail($id));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "$label not found"], 404);
        } catch (\Exception $e) {
            return $this->errorResponse("Failed to retrieve $label", $e);
        }
    }

    private function storeModel(Request $request, $model)
    {
        // $this->getAuthenticatedEmployee();
        try {
            $lang = $request->header('lang', 'en');

            $validated = $this->validateData($request);
            // if ($request->has('steps')) {
            //     $validated['steps'] = $request->steps;
            // }
            return ResponseWithSuccessData($lang, $model::create($validated), 1);

            // return response()->json($model::create($validated), 201);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        } catch (\Exception $e) {
            return $this->errorResponse("Failed to create record", $e);
        }
    }

    private function updateModel(Request $request, $model, $id, $label = 'record')
    {
        // $this->getAuthenticatedEmployee();
        try {
            $lang = $request->header('lang', 'en');

            $validated = $this->validateData($request);
            $record = $model::findOrFail($id);
            // if ($request->has('steps')) {
            //     $validated['steps'] = $request->steps;
            // }
            $record->update($validated);
            return ResponseWithSuccessData($lang, $record, 1);

            // return response()->json($record);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "$label not found"], 404);
        } catch (\Exception $e) {
            return $this->errorResponse("Failed to update $label", $e);
        }
    }

    private function validateData(Request $request)
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            // 'title_ar' => 'nullable|string|max:255',
            // 'title_en' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ]);
    }

    private function validationError(ValidationException $e)
    {
        return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
    }

    private function errorResponse($message, \Exception $e)
    {
        return response()->json(['error' => $message, 'message' => $e->getMessage()], 500);
    }

    // ReasonList
    public function indexReason(Request $request)
    {
        return $this->indexModel($request, ReasonList::class);
    }
    public function showReason(Request $request, $id)
    {
        return $this->showModel($request, ReasonList::class, $id, 'reason');
    }
    public function storeReason(Request $request)
    {
        return $this->storeModel($request, ReasonList::class);
    }
    public function updateReason(Request $request, $id)
    {
        return $this->updateModel($request, ReasonList::class, $id, 'reason');
    }

    // StatusTracking
    public function indexTracking(Request $request)
    {
        return $this->indexModel($request, StatusTracking::class);
    }
    public function showTracking(Request $request, $id)
    {
        return $this->showModel($request, StatusTracking::class, $id, 'tracking');
    }
    public function storeTracking(Request $request)
    {
        return $this->storeModel($request, StatusTracking::class);
    }
    public function updateTracking(Request $request, $id)
    {
        return $this->updateModel($request, StatusTracking::class, $id, 'tracking');
    }

    // AdjustmentReason
    public function indexAdjustment(Request $request)
    {
        return $this->indexModel($request, AdjustmentReason::class);
    }
    public function showAdjustment(Request $request, $id)
    {
        return $this->showModel($request, AdjustmentReason::class, $id, 'adjustment');
    }
    public function storeAdjustment(Request $request)
    {
        return $this->storeModel($request, AdjustmentReason::class);
    }
    public function updateAdjustment(Request $request, $id)
    {
        return $this->updateModel($request, AdjustmentReason::class, $id, 'adjustment');
    }

    // Purchase Order

    public function getBasePurchase($parentId)
    {
        $parent = PurchaseOrderStatus::find($parentId);
        if (!$parent) {
            return null;
        }
        if ($parent->type == 1) {
            return $parent->id;
        }
        if ($parent->parent_id) {
            return $this->getBasePurchase($parent->parent_id);
        }

        return null;
    }
    public function indexPurchase(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $records = PurchaseOrderStatus::all();

        return ResponseWithSuccessData($lang, $records, 1);
    }
    public function showPurchase(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');

        $record = PurchaseOrderStatus::findOrFail($id);
        if (!$record) {
            return RespondWithBadRequestData($lang, 2);
        }

        return ResponseWithSuccessData($lang, $record, 1);
    }
    public function storePurchase(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $validated = $request->validate([
            'title_en' => 'required|string|max:255|unique:purchase_order_statuses,title_en',
            'title_ar' => 'required|string|max:255|unique:purchase_order_statuses,title_ar',
            'parent_id' => 'required|integer',
            'child_id' => 'nullable|integer',
            'status' => 'nullable|boolean',
        ]);

        $base_status = $this->getBasePurchase($validated['parent_id']);

        $validated['base_status'] = $base_status;
        $record = PurchaseOrderStatus::create($validated);
        return ResponseWithSuccessData($lang, $record, 1);
    }
    public function updatePurchase(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');

        if (in_array((int) $id, range(1, 7))) {
            return RespondWithBadRequestData($lang, 2); //This item cannot be updated.
        }

        $record = PurchaseOrderStatus::findOrFail($id);

        if (!$record) {
            return RespondWithBadRequestData($lang, 2);
        }

        $validated = $request->validate([
            'title_en' => 'required|string|max:255|unique:purchase_order_statuses,title_en,' . $id,
            'title_ar' => 'required|string|max:255|unique:purchase_order_statuses,title_ar,' . $id,
            'parent_id' => 'required|integer',
            'child_id' => 'nullable|integer',
            'status' => 'nullable|boolean',
        ]);

        $validated['base_status'] = $this->getBasePurchase($validated['parent_id']);

        $record->update($validated);

        return ResponseWithSuccessData($lang, $record, 1);
    }

    // Audit Types
    // public function indexAudit(Request $request)
    // {
    //     return $this->indexModel($request, AuditType::class);
    // }
    // public function showAudit(Request $request, $id)
    // {
    //     return $this->showModel($request, AuditType::class, $id, 'adjustment');
    // }
    // public function storeAudit(Request $request)
    // {
    //     return $this->storeModel($request, AuditType::class);
    // }
    // public function updateAudit(Request $request, $id)
    // {
    //     return $this->updateModel($request, AuditType::class, $id, 'adjustment');
    // }

    // Discrepancy Reason
    // public function indexDiscrepancy(Request $request)
    // {
    //     return $this->indexModel($request, DiscrepancyReason::class);
    // }
    // public function showDiscrepancy(Request $request, $id)
    // {
    //     return $this->showModel($request, DiscrepancyReason::class, $id, 'adjustment');
    // }
    // public function storeDiscrepancy(Request $request)
    // {
    //     return $this->storeModel($request, DiscrepancyReason::class);
    // }
    // public function updateDiscrepancy(Request $request, $id)
    // {
    //     return $this->updateModel($request, DiscrepancyReason::class, $id, 'adjustment');
    // }

    //InventorySetting
    public function showExpirySetting(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $settings = InventorySetting::first();

        return ResponseWithSuccessData($lang, $settings, 1);
    }

    public function updateExpirySetting(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $setting = InventorySetting::firstOrCreate([]);

        $validator = Validator::make($request->all(), [
            'notify_before_expiration' => 'boolean',
            'auto_purchase_order_on_low_stock' => 'boolean',
            'notification_frequency' => 'in:daily,monthly',
            'no_of_days' => 'nullable|integer|min:1',
            'receive_notifications' => 'boolean',
            'notification_recipient' => 'nullable|in:warehouse_staff,branch_managers',
        ]);

        if ($validator->fails()) {
            // Return array for validation errors instead of response
            return [
                'code' => 400,
                'status' => false,
                'message' => $lang === 'ar' ? 'خطأ في التحقق من الصحة.' : 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        // Validation: if daily → require no_of_days
        if ($request->notification_frequency === 'daily' && !$request->no_of_days) {
            $message = $lang === 'ar'
                ? 'عدد الأيام مطلوب عندما يكون توقيت التنبيه يومي'
                : 'no_of_days is required when notification_frequency = daily';
                return respondError(__($message), 404);

            // return [
            // 'code' => 400, // Changed from 404 to 400 for validation error
            //     'status' => false,
            //     'message' => $message,
            //     'data' => null,
            //     'errorData' => null,
            //     'validation_type' => false
            // ];
        }

        // If monthly frequency, set no_of_days to null
        if ($request->notification_frequency === 'monthly') {
            $request->merge(['no_of_days' => null]);
        }

        // Validation: if receive_notifications = 1 → require notification_recipient
        // if receive_notifications = 0 → notification_recipient should be null
        if ($request->receive_notifications == 1 && !$request->notification_recipient) {
            $message = $lang === 'ar'
                ? 'مستلم التنبيه مطلوب عندما يكون استقبال التنبيهات مفعل'
                : 'notification_recipient is required when receive_notifications = 1';


                return respondError(__($message), 404);

        }

        // If receive_notifications is 0, set notification_recipient to null
        if ($request->receive_notifications == 0) {
            $request->merge(['notification_recipient' => null]);
        }

        $validatedData = $validator->validated();

        // Apply the merged values
        if ($request->notification_frequency === 'monthly') {
            $validatedData['no_of_days'] = null;
        }
        if ($request->receive_notifications == 0) {
            $validatedData['notification_recipient'] = null;
        }

        $setting->update($validatedData);

        return ResponseWithSuccessData($lang, $setting, 1);
    }
}
