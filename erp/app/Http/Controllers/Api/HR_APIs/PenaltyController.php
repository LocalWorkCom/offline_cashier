<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Models\Penalty;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CompanyPolicy;
use App\Models\PenaltyReason;
use App\Models\PenaltyApproval;
use App\Models\PenaltyDocument;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PenaltyResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PenaltyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $employee = auth('employee')->user();
        //        if (!CheckTokenEmployee()) {
        //            return RespondWithBadRequest($lang, 5);
        //        }
        //        $reasonField = $lang === 'en' ? 'reason_en' : 'reason_ar';
        //
        //        $penalties = Penalty::with([
        //            "reason",
        //            'employee',
        //        ])->get();
        $query = Penalty::with(['reason', 'employee', 'approval']);
        $child_employees = getSupervisedEmployees($employee->id);

        // Filter: By Employee ID
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter: By Penalty Type (from the related reason model)
        if ($request->filled('penalty_type')) {
            $query->whereHas('reason', function ($q) use ($request) {
                $q->where('type', $request->penalty_type);
            });
        }

        // Filter: By Date Range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('effective_date', [
                $request->from_date,
                $request->to_date
            ]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('effective_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('effective_date', '<=', $request->to_date);
        }

        // Filter: By Approval Status (from related approval model)
        if ($request->filled('approval_status')) {
            $query->whereHas('approval', function ($q) use ($request) {
                $q->where('status', $request->approval_status);
            });
        }

        // $penalties = $query->get();

        if ($employee->hasRole('HR_Manager')) {

            $myRequests = (clone $query)->where('employee_id', $employee->id);
            $employeeRequests = (clone $query)->where('employee_id', '!=', $employee->id);
        } elseif ($child_employees && $child_employees->count() > 0) {

            $childIds = $child_employees->pluck('id')->toArray();
            $myRequests = (clone $query)->where('employee_id', $employee->id);
            $employeeRequests = (clone $query)->whereIn('employee_id', $childIds);
        } else {

            $myRequests = (clone $query)->where('employee_id', $employee->id);
            $employeeRequests = Penalty::query()->whereRaw('1 = 0');
        }

        $myRequestsData = paginateOrGetAll($myRequests, $request, []);
        $employeeRequestsData = paginateOrGetAll($employeeRequests, $request, []);

        return ResponseWithSuccessDataPaginated($lang, [
            'data' => [
                'my' => $myRequestsData['data'] ?? null,
                'employees' => $employeeRequestsData['data'] ?? null,
            ],
            'meta' => $myRequestsData['meta'] ?? null,
        ], 1);
        // $result = paginateOrGetAll($query, $request);

        // return ResponseWithSuccessDataPaginated($lang, $result, 1);

        // return ResponseWithSuccessData($lang, PenaltyResource::collection($penalties), 1);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        try {
            // Step 1: Validate basic fields
            $baseData = $request->validate([
                'reason_id' => 'required|exists:penalty_reasons,id',
                'employee_id' => 'required|exists:employees,id',
                'calculation_type' => 'required|string|in:percentage,fixed',
                'amount' => 'required|numeric|min:0',
                'effective_date' => 'required|date',
                'end_date' => 'required|date',
                'documents' => 'nullable|array',
                'documents.*' => 'file|mimes:png,jpg,jpeg,pdf,doc,docx',
                'note' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $e->errors(),
                'validation_type' => true
            ], 400);
        }

        // Step 2: Get the penalty type from reason_id
        $penaltyReason = PenaltyReason::find($request->reason_id);
        if (!$penaltyReason) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        $penaltyType = $penaltyReason->type;

        // Step 3: Add conditional validation rules
        $conditionalRules = [];

        switch ($penaltyType) {
            case 'bonus_loss':
                $conditionalRules['bonus_type'] = 'required|string|in:performance_bonus,attendance_bonus,sales_commission,holiday_bonus,year_end_bonus,referral_bonus,sign_on_bonus,retention_bonus,project_completion_bonus,safety_bonus';
                break;

            case 'allowance_reduction':
                $conditionalRules['allowance_type'] = 'required|string|in:housing,transportation,meal,medical,mobile,internet,shift,hardship,travel,uniform';
                break;

            case 'fine':
                $conditionalRules['violation_type_id'] = 'required|exists:violation_types,id';
                break;
        }

        // Step 4: Validate the conditional fields with try-catch
        try {
            $additionalData = $request->validate($conditionalRules);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Conditional Validation Error.',
                'data' => null,
                'errorData' => $e->errors(),
                'validation_type' => true
            ], 400);
        }

        // Step 5: Merge all validated data
        $data = array_merge($baseData, $additionalData);

        // Step 6: Store penalty and approval inside a transaction
        try {
            DB::beginTransaction();

            $penalty = new Penalty();
            $penalty->reason_id = $data['reason_id'];
            $penalty->employee_id = $data['employee_id'];
            $penalty->amount = $data['amount'];
            $penalty->calculation_type = $data['calculation_type'];
            $penalty->effective_date = $data['effective_date'];
            $penalty->end_date = $data['end_date'];
            $penalty->note = $data['note'] ?? null;
            $penalty->created_by = authActionSave()['by'];
            $penalty->created_by_type = authActionSave()['type'];

            // Set conditional field based on penalty type
            if ($penaltyType === 'bonus_loss') {
                $penalty->bonus_type = $data['bonus_type'];
                $penalty->allowance_type = null;
                $penalty->violation_type_id = null;
            } elseif ($penaltyType === 'allowance_reduction') {
                $penalty->allowance_type = $data['allowance_type'];
                $penalty->bonus_type = null;
                $penalty->violation_type_id = null;
            } elseif ($penaltyType === 'fine') {
                $penalty->violation_type_id = $data['violation_type_id'];
                $penalty->bonus_type = null;
                $penalty->allowance_type = null;
            } else {
                $penalty->bonus_type = null;
                $penalty->allowance_type = null;
                $penalty->violation_type_id = null;
            }

            $penalty->save();

            // Create pending approval record
            $approval = PenaltyApproval::updateOrCreate(
                ['penalty_id' => $penalty->id],
                ['status' => 'pending'],
                ['created_by' => authActionSave()['by']],
                ['created_by_type' => authActionSave()['type']]
            );
            $approval->save();

            if ($request->hasFile('documents')) {
                $files = $request->file('documents');

                foreach ($files as $file) {
                    $document = new PenaltyDocument();
                    UploadFile('images/penalties_documents', 'file_path', $document, $file);
                    $document->original_name = $file->getClientOriginalName();
                    $document->mime_type = explode('/', $file->getClientMimeType())[1];
                    $document->penalty_id = $penalty->id;
                    $document->created_by = authActionSave()['by'];
                    $document->created_by_type = authActionSave()['type'];
                    $document->save();
                }
            }

            $this->sendPenaltyNotifications($penalty, $lang);
            DB::commit();

            return ResponseWithSuccessData($lang, $penalty, 1);
        } catch (\Exception $e) {
            DB::rollBack();
            return RespondWithBadRequestData($lang, 2);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $penalty = Penalty::with(['reason', 'employee'])->find($id);

        if (!$penalty) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        // return ResponseWithSuccessData($lang, PenaltyResource::make($penalty), 1);
        return ResponseWithSuccessData($lang, $penalty, 1);
    }

    // public function show(string $id, Request $request)
    // {
    //     $penalites = Penalty::with(['approval', 'reason'])
    //     ->where('employee_id', $id)
    //     ->whereBetween('created_at', [$request->startDate, $request->endDate])->get();
    //     $updateIn = [
    //         'bonuses' => 0,
    //         'allowance' => 0,
    //         'salaryDeduction' => 0,
    //     ];
    //     $amount = 0;
    //     foreach($penalites as $penalty)
    //     {
    //         switch ($penalty) {
    //             case $penalty->reason->type == 'bonus_loss' && $penalty->approval->status == 'approved':
    //                 $updateIn['bonuses'] += $penalty->amount;
    //                 $amount += $penalty->amount;
    //                 break;

    //             case $penalty->reason->type == 'allowance_reduction':
    //                 $updateIn['allowance'] += $penalty->amount;
    //                 $amount += $penalty->amount;
    //                 break;

    //             case $penalty->reason->type == 'salary_deduction' && $penalty->approval->status == 'approved':
    //                 $updateIn['salaryDeduction'] +=  $penalty->amount;
    //                 $amount += $penalty->amount;
    //                 break;

    //             case $penalty->reason->type == 'fine' && $penalty->approval->status == 'approved':
    //                 $updateIn['salaryDeduction'] +=  $penalty->amount;
    //                 $amount += $penalty->amount;
    //                 break;

    //             default:
    //                 $deductMinutes = 0;
    //         }
    //     }
    //     $data =  [
    //         'updateIn' => $updateIn,
    //         'amount' => $amount,
    //     ];
    //     $lang = $request->header('lang', 'ar');

    //     // $penalty = Penalty::with(['reason', 'employee'])->find($id);

    //     // if (!$penalty) {
    //     //     return respondError(__('branch_menu_category.not_found'), 404);
    //     // }

    //     // // return ResponseWithSuccessData($lang, PenaltyResource::make($penalty), 1);
    //     return ResponseWithSuccessData($lang, $data, 1);
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lang = $request->header('lang', 'ar');

        //        if (!CheckTokenEmployee()) {
        //            return RespondWithBadRequest($lang, 5);
        //        }

        $penalty = Penalty::find($id);
        if (!$penalty) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        // Step 1: Validate base fields
        try {
            // Step 1: Validate basic fields
            $baseData = $request->validate([
                'reason_id' => 'sometimes|required|exists:penalty_reasons,id',
                'employee_id' => 'sometimes|required|exists:employees,id',
                'note' => 'nullable|string',
                'amount' => 'sometimes|required|numeric|min:0',
                'calculation_type' => 'sometimes|required|string|in:percentage,fixed',
                'effective_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date',
                'documents' => 'nullable|array',
                'documents.*' => 'file|mimes:png,jpg,jpeg,pdf,doc,docx',
                'deleted_documents' => 'nullable|array',
                'deleted_documents.*' => 'exists:penalty_documents,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $e->errors(),
                'validation_type' => true
            ], 400);
        }


        // Step 2: Get penalty type (use new reason_id if provided)
        $reasonId = $baseData['reason_id'] ?? $penalty->reason_id;
        $penaltyReason = PenaltyReason::find($reasonId);
        if (!$penaltyReason) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        $penaltyType = $penaltyReason->type;

        // Step 3: Define conditional rules
        $conditionalRules = [];

        switch ($penaltyType) {
            case 'bonus_loss':
                $conditionalRules['bonus_type'] = 'required|string|in:performance_bonus,attendance_bonus,sales_commission,holiday_bonus,year_end_bonus,referral_bonus,sign_on_bonus,retention_bonus,project_completion_bonus,safety_bonus';
                break;

            case 'allowance_reduction':
                $conditionalRules['allowance_type'] = 'required|string|in:housing,transportation,meal,medical,mobile,internet,shift,hardship,travel,uniform';
                break;

            case 'fine':
                $conditionalRules['violation_type_id'] = 'required|exists:violation_types,id';
                break;
        }

        // Step 4: Validate conditional fields
        try {
            $conditionalData = $request->validate($conditionalRules);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Conditional Validation Error.',
                'data' => null,
                'errorData' => $e->errors(),
                'validation_type' => true
            ], 400);
        }
        // Step 5: Merge all data
        $data = array_merge($baseData, $conditionalData);

        // Step 6: Start transaction
        try {
            DB::beginTransaction();

            // Update base fields
            foreach ($data as $field => $value) {
                if ($field !== 'documents' && $field !== 'deleted_documents') {
                    $penalty->$field = $value;
                }
            }

            // Set conditional fields
            if ($penaltyType === 'bonus_loss') {
                $penalty->bonus_type = $data['bonus_type'];
                $penalty->allowance_type = null;
                $penalty->violation_type_id = null;
            } elseif ($penaltyType === 'allowance_reduction') {
                $penalty->allowance_type = $data['allowance_type'];
                $penalty->bonus_type = null;
                $penalty->violation_type_id = null;
            } elseif ($penaltyType === 'fine') {
                $penalty->violation_type_id = $data['violation_type_id'];
                $penalty->bonus_type = null;
                $penalty->allowance_type = null;
            } else {
                $penalty->bonus_type = null;
                $penalty->allowance_type = null;
                $penalty->violation_type_id = null;
            }
            $penalty->modified_by = authActionSave()['by'];
            $penalty->modified_by_type = authActionSave()['type'];
            $penalty->save();

            // Handle document deletions
            if (isset($data['deleted_documents']) && count($data['deleted_documents']) > 0) {
                $documentsToDelete = PenaltyDocument::whereIn('id', $data['deleted_documents'])
                    ->where('penalty_id', $penalty->id)
                    ->get();

                foreach ($documentsToDelete as $document) {
                    // Delete file from storage
                    DeleteFile('images/penalties_documents', $document->file_path);
                    // Delete record from database
                    $document->delete();
                }
            }

            // Handle new document uploads
            if ($request->hasFile('documents')) {
                $files = $request->file('documents');

                foreach ($files as $file) {
                    $document = new PenaltyDocument();

                    UploadFile('images/penalties_documents', 'file_path', $document, $file);

                    $document->original_name = $file->getClientOriginalName();
                    $document->mime_type = explode('/', $file->getClientMimeType())[1];
                    $document->penalty_id = $penalty->id;
                    $document->modified_by = authActionSave()['by'];
                    $document->modified_by_type = authActionSave()['type'];

                    $document->save();
                }
            }

            // Optional: Update approval record status back to pending
            PenaltyApproval::updateOrCreate(
                ['penalty_id' => $penalty->id],
                ['status' => 'pending'],
                ['modified_by' => authActionSave()['by']],
                ['modified_by_type' => authActionSave()['type']]
            );

            $this->sendPenaltyNotifications($penalty, $lang);

            DB::commit();
            return ResponseWithSuccessData($lang, $penalty, 1);
        } catch (\Exception $e) {
            DB::rollBack();
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lang = request()->header('lang', 'ar');

        //        if (!CheckTokenEmployee()) {
        //            return RespondWithBadRequest($lang, 5);
        //        }

        $penalty = Penalty::find($id);
        if (!$penalty) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        try {
            DB::beginTransaction();

            // Mark who deleted
            // $penalty->deleted_by = Auth::guard('employee')->user()->id;
            $penalty->deleted_by = authActionSave()['by'];
            $penalty->deleted_by_type = authActionSave()['type'];
            $penalty->save();

            // Get all documents to delete their files first
            $documents = PenaltyDocument::where('penalty_id', $penalty->id)->get();

            foreach ($documents as $document) {
                $filename = basename($document->file_path);
                //                dd($filename);
                // Delete file from storage
                DeleteFile('images/penalties_documents', $filename);
                // Delete record from database
                $document->delete();
            }

            // Soft delete penalty
            $penalty->delete();

            // Also soft delete related approval if exists
            $approval = PenaltyApproval::where('penalty_id', $penalty->id)->first();
            if ($approval) {
                $approval->delete();
            }

            DB::commit();
            return ResponseWithSuccessData($lang, $penalty, 1);
            // return ResponseWithSuccessData($lang, PenaltyResource::make($penalty), 1);
        } catch (\Exception $e) {
            DB::rollBack();
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id, Request $request)
    {
        $lang = $request->header('lang', 'ar');

        //        if (!CheckTokenEmployee()) {
        //            return RespondWithBadRequest($lang, 5);
        //        }

        $penalty = Penalty::withTrashed()->find($id);
        if (!$penalty) {
            return RespondWithBadRequestData($lang, 2);
        }

        try {
            DB::beginTransaction();

            // Restore penalty
            $penalty->restore();

            //            // Restore related documents (no need to restore files as they weren't deleted from storage)
            //            PenaltyDocument::withTrashed()
            //                ->where('penalty_id', $penalty->id)
            //                ->restore();

            // Restore or recreate approval
            $approval = PenaltyApproval::withTrashed()->where('penalty_id', $penalty->id)->first();

            if ($approval) {
                $approval->restore();
                $approval->status = 'pending';
                $approval->save();
            } else {
                PenaltyApproval::create([
                    'penalty_id' => $penalty->id,
                    'status' => 'pending',
                ]);
            }

            DB::commit();
            return ResponseWithSuccessData($lang, PenaltyResource::make($penalty), 1);
        } catch (\Exception $e) {
            DB::rollBack();
            return respondError($e->getMessage(), 2);
        }
    }
    public function get_approvals(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        //        $user = auth()->guard('employee')->user();
        //
        //        $allowedFlags = explode('|', 'hr');
        //        dd($allowedFlags);
        //        if (!$user  ||!in_array($user->flag, $allowedFlags)) {
        //            return RespondWithBadRequest($lang, 36);
        //        }
        // Use query() instead of all() to get a query builder instance
        $approvals = PenaltyApproval::query();

        $result = paginateOrGetAll($approvals, $request);

        return ResponseWithSuccessDataPaginated($lang, $result, 1);
    }
    public function approval_request_change_status(Request $request, string $id)
    {
        $lang = $request->header('lang', 'ar');

        // Validation rules
        try {
            $data = $request->validate([
                'status' => 'required|string|in:pending,approved,rejected',
                'note' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $e->errors(),
                'validation_type' => true
            ], 400);
        }

        $approval = PenaltyApproval::find($id);

        if (!$approval) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $penalty = Penalty::find($approval->penalty_id);

        if (!$penalty) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        if (isset($data['status'])) {
            $approval->status = $data['status'];
        }

        if (isset($data['note'])) {
            $approval->note = $data['note'];
        }

        $approval->modified_by = authActionSave()['by'];
        $approval->modified_by_type = authActionSave()['type'];
        $approval->save();

        // Get the employee associated with the penalty
        $employee = Employee::find($penalty->employee_id);

        // Prepare notification results
        $notificationResult = null;
        $fullUrl = url()->current();
        $apiBaseUrl = Str::before($fullUrl, '/api');
        $url = $apiBaseUrl . '/penalties/' . $penalty->id;

        if ($employee) {
            $notificationSent = false;
            $requestId = $penalty->id;

            // Only send push notification if status is changed to approved or rejected
            if (in_array($data['status'], ['approved', 'rejected']) && !empty($employee->device_token)) {

                // Prepare notification messages based on status
                $title_ar = $data['status'] === 'approved' ? 'تم الموافقة على العقوبة' : 'تم رفض العقوبة';
                $title_en = $data['status'] === 'approved' ? 'Penalty Approved' : 'Penalty Rejected';

                $description_ar = $data['status'] === 'approved'
                    ? "تمت الموافقة على العقوبة المطبقة عليك"
                    : "تم رفض العقوبة المطبقة عليك";

                $description_en = $data['status'] === 'approved'
                    ? "The penalty applied to you has been approved"
                    : "The penalty applied to you has been rejected";

                // Add note if available
                if (!empty($data['note'])) {
                    $description_ar .= ". ملاحظة: " . $data['note'];
                    $description_en .= ". Note: " . $data['note'];
                }

                $notification = send_push_notification(
                    $employee->device_token,
                    $description_ar,
                    $description_en,
                    $title_ar,
                    $title_en,
                    'penalty_status_update',
                    $employee->id,
                    authActionSave()['by'],
                    $requestId,
                    $lang,
                    7,
                    $url
                );

                $notificationSent = $notification !== false;
            }

            $notificationResult = [
                'user_id' => $employee->id,
                'notification_sent' => $notificationSent,
            ];
        }

        $responseData = [
            'approval' => $approval,
            'notification' => $notificationResult
        ];

        return ResponseWithSuccessData($lang, $responseData, 1);
    }
    private function sendPenaltyNotifications(Penalty $penalty, string $lang): void
    {
        $current_user_id = auth('employee')->user()->id;
        $assigned_employee = Employee::find($penalty->employee_id);

        if (!$assigned_employee) {
            return; // Employee not found, skip notifications
        }

        $position = Position::find($assigned_employee->position_id);
        $department = Department::find($assigned_employee->department_id);

        $employee_full_name = $assigned_employee->first_name . ' ' . $assigned_employee->last_name;

        // Use safe access with null coalescing to avoid errors
        $position_name_ar = $position->name_ar ?? 'غير محدد';
        $position_name_en = $position->name_en ?? 'Not specified';
        $department_name_ar = $department->name_ar ?? 'غير محدد';
        $department_name_en = $department->name_en ?? 'Not specified';

        $body_ar = 'لقد تم تعيين جزاء للموظف ' . $employee_full_name .
            ' في منصب ' . $position_name_ar .
            ' بقسم ' . $department_name_ar .
            ' بداية من تاريخ ' . $penalty->effective_date .
            ' وحتى تاريخ ' . $penalty->end_date . '.';

        $body_en = $employee_full_name .
            ' has penalty assigned as ' . $position_name_en .
            ' in ' . $department_name_en .
            ' department starting from ' . $penalty->effective_date .
            ' till ' . $penalty->end_date . '.';

        $title_ar = 'تعيين جزاء لموظف';
        $title_en = 'Employee penalty assigned';

        // Notify all HR employees in the same branch
        $hr_employees = Employee::where('flag', 'hr')
            ->where('branch_id', $assigned_employee->branch_id)
            ->get();

        foreach ($hr_employees as $hr_employee) {
            if ($hr_employee->device_token) {
                send_push_notification(
                    $hr_employee->device_token,
                    $body_ar,
                    $body_en,
                    $title_ar,
                    $title_en,
                    'hr',
                    $hr_employee->id,
                    $current_user_id,
                    $assigned_employee->id,
                    $lang,7
                );
            }
        }

        // Notify the penalized employee
        if ($assigned_employee->device_token) {
            send_push_notification(
                $assigned_employee->device_token,
                $body_ar,
                $body_en,
                $title_ar,
                $title_en,
                'hr',
                $assigned_employee->id,
                $current_user_id,
                $assigned_employee->id,
                $lang,7
            );
        }
    }
    public function penaltyReport(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $query = Penalty::with(['reason', 'employee', 'approval']);
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter: By Penalty Type (from the related reason model)
        if ($request->filled('penalty_type')) {
            $query->whereHas('reason', function ($q) use ($request) {
                $q->where('type', $request->penalty_type);
            });
        }

        // Filter: By Date Range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('effective_date', [
                $request->from_date,
                $request->to_date
            ]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('effective_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('effective_date', '<=', $request->to_date);
        }

        // Filter: By Approval Status (from related approval model)
        if ($request->filled('approval_status')) {
            $query->whereHas('approval', function ($q) use ($request) {
                $q->where('status', $request->approval_status);
            });
        }
        $penalties = paginateOrGetAll($query, $request);
        $meta = $penalties['meta'];
        $penalties = $penalties['data'];
        $data = [];
        foreach ($penalties as $penalty) {
            $data[] = [
                'id' => $penalty->id,
                'reason' => $penalty->reason->reason_ar,
                'employee' => $penalty->employee->first_name . ' ' . $penalty->employee->last_name,
                'amount' => $penalty->amount,
                'calculation_type' => $penalty->calculation_type,
                'effective_date' => $penalty->effective_date,
                'end_date' => $penalty->end_date,
                'note' => $penalty->note,
                'type' => $penalty->reason->type,
                'code' => $penalty->reason->code,
                'status' => $penalty->approval->status ?? 'N/A',
                'created_at' => $penalty->created_at->toDateTimeString(),
                'documents' => $penalty->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'original_name' => $doc->original_name,
                        'file_path' => $doc->file_path,
                        'mime_type' => $doc->mime_type,
                    ];
                }),
            ];
        }
        $responseData = [
            'data' => $data,
            'meta' => $meta
        ];
        // dd($penalty);
        return ResponseWithSuccessDataPaginated($lang, $responseData, 1);
    }
}
