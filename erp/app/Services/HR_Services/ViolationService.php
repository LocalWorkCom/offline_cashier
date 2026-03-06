<?php

namespace App\Services\HR_Services;

use App\Models\Violation;
use App\Models\ViolationLog;
use App\Models\ViolationPenalty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ViolationService
{
    public function index(Request $request)
    {
        $auth = auth('employee')->user();

        // Base query
        $Violations = Violation::with([
            'violationPenalties.penalty',
            'employeeViolations.employee.department',
            'employeeViolations.employee.position'
        ]);

        // ✅ Get my own violations
        $myViolations = (clone $Violations)
            ->whereHas('employeeViolations', function ($query) use ($auth) {
                $query->where('employee_id', $auth->id);
            });

        // ✅ Default: no employees
        $employeeViolations = Violation::query()->whereRaw('1 = 0');

        // ✅ Check if not superAdmin or Hr_Manager
        if (!$auth->hasRole(['superAdmin', 'Hr_Manager'], 'employee')) {

            // Get supervised employee IDs
            $supervised = getSupervisedEmployees($auth->id);
            $supervisedIds = $supervised->pluck('id')->toArray();

            if (!empty($supervisedIds)) {
                // Fetch supervised employees' violations
                $employeeViolations = (clone $Violations)->whereHas('employeeViolations', function ($query) use ($supervisedIds) {
                    $query->whereIn('employee_id', $supervisedIds);
                });
            }
        } else {
            // ✅ HR manager or superAdmin sees all employees except themselves
            $employeeViolations = (clone $Violations)->whereHas('employeeViolations', function ($query) use ($auth) {
                $query->where('employee_id', '!=', $auth->id);
            });
        }

        // ✅ Execute queries (important!)
        return [
            'my' => $myViolations->get(),
            'employees' => $employeeViolations->get(),
        ];
    }


    public function store(Request $request, $lang)
    {


        $validated = $request->validated();

        if (count($validated['penalties']) !== $validated['max_repetition']) {
            return respondError(__('validation.penalties_must _equal _max'), 400, __('validation.penalties_must _equal _max'));
        }

        $violation = Violation::create([
            'name' => $validated['violation_name'],
            'max_repeat' => $validated['max_repetition'],
            'within_period' => $validated['within_period'],
            'created_by' => auth('employee')->id(),
        ]);

        foreach ($validated['penalties'] as $penalty) {
            ViolationPenalty::create([
                'violation_id' => $violation->id,
                'penalty_id' => $penalty['id'],
                'order_penalty' => $penalty['order'],
                'created_by' => auth('employee')->id(),
            ]);
        }
        return $violation;
    }


    public function update(Request $request, $lang, $id)
    {
        $violation = Violation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'violation_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('violations', 'name')->ignore($violation->id),
            ],
            'max_repetition' => 'required|integer|min:1',
            'within_period' => 'required|integer|min:1',
            'penalties' => 'required|array',
            'penalties.*.id' => 'required|exists:penalty_reasons,id',
            'penalties.*.order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return respondError($lang === 'en' ? 'Validation Error.' : 'خطأ في التحقق.', 400, $validator->errors());
        }

        $validated = $validator->validated();

        // Extract orders
        $orders = array_column($validated['penalties'], 'order');

        // 1. Check if count matches max_repetition
        if (count($orders) !== $validated['max_repetition']) {
            return respondErrorData(__('validation.penalties_must _equal _max'), 400, __('validation.penalties_must _equal _max'));
        }

        // 2. Check for duplicates
        if (count($orders) !== count(array_unique($orders))) {
            return respondErrorData(__('validation.Duplicate_order'), 400, __('validation.Duplicate_order'));
        }

        // 3. Check if orders are exactly 1 to max_repetition
        $expectedOrders = range(1, $validated['max_repetition']);
        sort($orders); // Ensure order
        if ($orders !== $expectedOrders) {
            return respondErrorData(__('validation.penalties_must _equal _max'), 400, __('validation.penalties_must _equal _max'));
        }

        $employeeId = auth('employee')->id();

        $violation->update([
            'name' => $validated['violation_name'],
            'max_repeat' => $validated['max_repetition'],
            'within_period' => $validated['within_period'],
            'modified_by' => $employeeId,
        ]);

        // Fetch existing penalties
        $oldPenalties = ViolationPenalty::where('violation_id', $violation->id)->get()->keyBy('penalty_id');

        // Track penalty IDs from the new request
        $newPenaltyIds = [];

        foreach ($validated['penalties'] as $penalty) {
            $penaltyId = $penalty['id'];
            $order = $penalty['order'];
            $newPenaltyIds[] = $penaltyId;

            if ($oldPenalties->has($penaltyId)) {
                // Already exists - check if order changed
                $old = $oldPenalties[$penaltyId];
                if ($old->order_penalty != $order) {
                    // Update existing
                    $old->update(['order_penalty' => $order]);

                    // Log update
                    ViolationLog::create([
                        'violation_id' => $violation->id,
                        'old_penalty_id' => $penaltyId,
                        'new_penalty_id' => $penaltyId,
                        'old_order' => $old->order_penalty,
                        'new_order' => $order,
                        'action' => 'updated',
                        'changed_by' => $employeeId,
                    ]);
                }
                // If no change → do nothing
            } else {
                // New penalty
                ViolationPenalty::create([
                    'violation_id' => $violation->id,
                    'penalty_id' => $penaltyId,
                    'order_penalty' => $order,
                    'created_by' => $employeeId,
                ]);

                ViolationLog::create([
                    'violation_id' => $violation->id,
                    'new_penalty_id' => $penaltyId,
                    'new_order' => $order,
                    'action' => 'added',
                    'changed_by' => $employeeId,
                ]);
            }
        }

        // Delete penalties that were removed
        foreach ($oldPenalties as $penaltyId => $penalty) {
            if (!in_array($penaltyId, $newPenaltyIds)) {
                $penalty->delete(); // or soft delete if needed

                ViolationLog::create([
                    'violation_id' => $violation->id,
                    'old_penalty_id' => $penaltyId,
                    'old_order' => $penalty->order_penalty,
                    'action' => 'deleted',
                    'changed_by' => $employeeId,
                ]);
            }
        }

        return $violation;
    }


    public function destroy(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $Violations = Violation::find($id);
        if (!$Violations) {
            return RespondWithBadRequestData($lang, 8);
        }

        if ($Violations->logo) {
            DeleteFile('images/Violations', $Violations->logo);
        }
        if ($Violations->scanned_trade_license) {
            DeleteFile('documents/trade_licenses', $Violations->scanned_trade_license);
        }
        $Violations->update(['deleted_by' => auth('admin')->id()]);

        $Violations->delete();
        return RespondWithSuccessRequest($lang, 1);
    }

    public function restore(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $Violations = Violation::withTrashed()->findOrFail($id);
            $Violations->restore();
            return ResponseWithSuccessData($lang, $Violations, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring Violation: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
