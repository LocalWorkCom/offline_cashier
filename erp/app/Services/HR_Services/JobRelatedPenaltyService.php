<?php


namespace App\Services\HR_Services;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobRelatedPenalty;
use App\Models\JobRelatedPenaltyDocument;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class JobRelatedPenaltyService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function index(Request $request)
    {
        $lang = app()->getLocale();

        try {
            $query = JobRelatedPenalty::with('documents', 'employee');
            // Filter: By Employee ID
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            // Filter: By Penalty Type (from the related reason model)
            if ($request->filled('type')) {
                $query->where('type', $request->type);
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
                $query->where('status', $request->approval_status);
            }

            return $query;
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Something went wrong'] : ['حدث خطأ ما']), 500);
        }
    }

    public function show(Request $request, $id)
    {
        $lang = app()->getLocale();

        try {
            $JobRelated = JobRelatedPenalty::with('documents', 'employee')->find($id);

            if (!$JobRelated) {
                return respondErrorData(
                    $lang == 'en' ? ['Not existing any more'] : ['غير موجود'],
                    404,
                    $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']
                );
            }

            return ResponseWithSuccessData($lang, $JobRelated, 1);
        } catch (\Exception $e) {
            return respondErrorData(
                $lang == 'en' ? ['Something went wrong'] : ['حدث خطأ ما'],
                500
            );
        }
    }

    public function add(Request $request)
    {
        $lang = app()->getLocale();
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required',
                'type' => 'required|in:demotion,transfer,loss_job,restrict_system',
                'status' => 'nullable|in:pending,approved,rejected',
                'effective_date' => 'required|date',
                'reason' => 'required|string',
                'documents' => 'nullable|array',
                'documents.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $jobRelateType = $request->type;

            $conditionalRules = [];

            switch ($jobRelateType) {
                case 'demotion':
                    $conditionalRules['curr_possition_id'] = 'required|exists:positions,id';
                    $conditionalRules['new_possition_id'] = 'required|exists:positions,id';
                    break;

                case 'transfer':
                    $conditionalRules['curr_department_id'] = 'required|exists:departments,id';
                    $conditionalRules['new_department_id'] = 'required|exists:departments,id';
                    $conditionalRules['curr_possition_id'] = 'required|exists:positions,id';
                    $conditionalRules['new_possition_id'] = 'required|exists:positions,id';
                    $conditionalRules['curr_branch_id'] = 'required|exists:branches,id';
                    $conditionalRules['new_branch_id'] = 'required|exists:branches,id';
                    break;

                case 'loss_job':
                    $conditionalRules['privilege_type_id'] = 'required|exists:privilege_types,id';
                    break;

                case 'restrict_system':
                    $conditionalRules['restricted_system'] = 'required|string';
                    break;
            }
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

            $data = array_merge($validator->validated(), $additionalData);

            try {
                DB::beginTransaction();

                $JobRelated = new JobRelatedPenalty();
                $JobRelated->employee_id = $data['employee_id'];
                $JobRelated->type = $data['type'];
                $JobRelated->status = $data['status'] ?? 'pending';
                $JobRelated->effective_date = $data['effective_date'];
                $JobRelated->reason = $data['reason'];
                $JobRelated->created_by = authActionSave()['by'];
                $JobRelated->created_by_type = authActionSave()['type'];

                if ($jobRelateType === 'demotion') {
                    $JobRelated->curr_possition_id = $data['curr_possition_id'];
                    $JobRelated->new_possition_id = $data['new_possition_id'];
                } elseif ($jobRelateType === 'transfer') {
                    $JobRelated->curr_possition_id = $data['curr_possition_id'];
                    $JobRelated->new_possition_id = $data['new_possition_id'];
                    $JobRelated->new_department_id = $data['new_department_id'];
                    $JobRelated->curr_department_id = $data['curr_department_id'];
                    $JobRelated->new_branch_id = $data['new_branch_id'];
                    $JobRelated->curr_branch_id = $data['curr_branch_id'];
                } elseif ($jobRelateType === 'restrict_system') {
                    $JobRelated->restrict_system = $data['restricted_system'];
                } elseif ($jobRelateType === 'loss_job') {
                    $JobRelated->privilege_type_id = $data['privilege_type_id'];
                }

                $JobRelated->save();

                if ($request->hasFile('documents')) {
                    $files = $request->file('documents');
                    foreach ($files as $file) {
                        $path = 'images/penalties_documents';

                        if (!file_exists(public_path($path))) {
                            mkdir(public_path($path), 0777, true);
                        }
                        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path($path), $filename);

                        $document = new JobRelatedPenaltyDocument();
                        $document->file_path = url($path . '/' . $filename);
                        $document->original_name = $file->getClientOriginalName();
                        $document->mime_type = $file->getClientMimeType();
                        $document->job_related_id = $JobRelated->id;
                        $document->save();
                        Log::error('Document not saved', $document->toArray());
                    }
                }
                DB::commit();

                return ResponseWithSuccessData($lang, $JobRelated, 1);
            } catch (\Exception $e) {
                DB::rollBack();
                return RespondWithBadRequestData($lang, 2);
            }
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();

        try {
            // Validate the base input
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required',
                'type' => 'required|in:demotion,transfer,loss_job,restrict_system',
                'status' => 'nullable|in:pending,approved,rejected',
                'effective_date' => 'required|date',
                'reason' => 'required|string',
                'documents' => 'nullable|array',
                'documents.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $jobRelateType = $request->type;

            $conditionalRules = [];
            switch ($jobRelateType) {
                case 'demotion':
                    $conditionalRules['curr_possition_id'] = 'required|exists:positions,id';
                    $conditionalRules['new_possition_id'] = 'required|exists:positions,id';
                    break;

                case 'transfer':
                    $conditionalRules['curr_department_id'] = 'required|exists:departments,id';
                    $conditionalRules['new_department_id'] = 'required|exists:departments,id';
                    $conditionalRules['curr_possition_id'] = 'required|exists:positions,id';
                    $conditionalRules['new_possition_id'] = 'required|exists:positions,id';
                    $conditionalRules['curr_branch_id'] = 'required|exists:branches,id';
                    $conditionalRules['new_branch_id'] = 'required|exists:branches,id';
                    break;

                case 'loss_job':
                    $conditionalRules['privilege_type_id'] = 'required|exists:privilege_types,id';
                    break;

                case 'restrict_system':
                    $conditionalRules['restricted_system'] = 'required|string';
                    break;
            }

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

            $data = array_merge($validator->validated(), $additionalData);

            try {
                DB::beginTransaction();

                $JobRelated = JobRelatedPenalty::find($id);
                if (!$JobRelated) {
                    return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 404, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
                }
                $JobRelated->employee_id = $data['employee_id'];
                $JobRelated->type = $data['type'];
                $JobRelated->status = $data['status'] ?? 'pending';
                $JobRelated->effective_date = $data['effective_date'];
                $JobRelated->reason = $data['reason'];
                $JobRelated->modified_by = authActionSave()['by'];
                $JobRelated->modified_by_type = authActionSave()['type'];

                if ($jobRelateType === 'demotion') {
                    $JobRelated->curr_possition_id = $data['curr_possition_id'];
                    $JobRelated->new_possition_id = $data['new_possition_id'];
                } elseif ($jobRelateType === 'transfer') {
                    $JobRelated->curr_possition_id = $data['curr_possition_id'];
                    $JobRelated->new_possition_id = $data['new_possition_id'];
                    $JobRelated->new_department_id = $data['new_department_id'];
                    $JobRelated->curr_department_id = $data['curr_department_id'];
                    $JobRelated->new_branch_id = $data['new_branch_id'];
                    $JobRelated->curr_branch_id = $data['curr_branch_id'];
                } elseif ($jobRelateType === 'restrict_system') {
                    $JobRelated->restrict_system = $data['restricted_system'];
                } elseif ($jobRelateType === 'loss_job') {
                    $JobRelated->privilege_type_id = $data['privilege_type_id'];
                }

                $JobRelated->save();

                if ($request->hasFile('documents')) {
                    $files = $request->file('documents');
                    foreach ($files as $file) {
                        $path = 'images/penalties_documents';

                        if (!file_exists(public_path($path))) {
                            mkdir(public_path($path), 0777, true);
                        }
                        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path($path), $filename);

                        $document = new JobRelatedPenaltyDocument();
                        $document->file_path = url($path . '/' . $filename);
                        $document->original_name = $file->getClientOriginalName();
                        $document->mime_type = $file->getClientMimeType();
                        $document->job_related_id = $JobRelated->id;
                        $document->save();
                    }
                }

                DB::commit();
                return ResponseWithSuccessData($lang, $JobRelated, 1);
            } catch (\Exception $e) {
                DB::rollBack();
                return RespondWithBadRequestData($lang, 2);
            }
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function destroy(Request $request, $id)
    {
        $lang = app()->getLocale();

        try {
            DB::beginTransaction();

            $JobRelated = JobRelatedPenalty::with('documents')->find($id);

            if (!$JobRelated) {
                return respondErrorData(
                    $lang == 'en' ? ['Not existing any more'] : ['غير موجود'],
                    404,
                    $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']
                );
            }

            foreach ($JobRelated->documents as $doc) {
                $filePath = public_path(parse_url($doc->file_path, PHP_URL_PATH));
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $doc->delete();
            }
            $JobRelated->deleted_by = authActionSave()['by'];
            $JobRelated->deleted_by_type = authActionSave()['type'];
            $JobRelated->delete();

            DB::commit();
            // return ResponseWithSuccessData($lang, ['message' => $lang == 'en' ? 'Deleted successfully' : 'تم الحذف بنجاح'], 1);
            return RespondWithSuccessRequest($this->lang, 1);
        } catch (\Exception $e) {
            DB::rollBack();
            return respondErrorData(($lang == 'en' ? ['Delete failed'] : ['فشل الحذف']), 500);
        }
    }

    private function sendNotifications(JobRelatedPenalty $JobRelated, string $lang): void
    {
        $current_user_id = authActionSave()['by'];
        $assigned_employee = Employee::find($JobRelated->employee_id);

        if (!$assigned_employee) {
            return;
        }

        $position = Position::find($JobRelated->new_possition_id);
        $department = Department::find($JobRelated->new_department_id);
        $branch = Branch::find($JobRelated->new_branch_id);

        $employee_full_name = $assigned_employee->first_name . ' ' . $assigned_employee->last_name;

        // Use safe access with null coalescing to avoid errors
        $position_name_ar = $position->name_ar ?? 'غير محدد';
        $position_name_en = $position->name_en ?? 'Not specified';
        $department_name_ar = $department->name_ar ?? 'غير محدد';
        $department_name_en = $department->name_en ?? 'Not specified';
        $branch_name_ar = $branch->name_ar ?? 'غير محدد';
        $branch_name_en = $branch->name_en ?? 'Not specified';

        $body_ar = 'لقم تم نقل الموظف ' . $employee_full_name .
            ' في منصب ' . $position_name_ar .
            ' بقسم ' . $department_name_ar .
            ' بفرع ' . $branch_name_ar .
            ' بداية من تاريخ ' . $JobRelated->effective_date;

        $body_en = $employee_full_name .
            ' has been transferred to the position of ' . $position_name_en .
            ' in ' . $branch_name_en .
            ' in ' . $department_name_en .
            ' department starting from ' . $JobRelated->effective_date;

        $title_ar = 'نقل الموظف';
        $title_en = 'Employee transferred';

        // Notify all HR employees in the same branch
        $hr_employees = Employee::where('flag', 'hr')
            ->where('branch_id', $JobRelated->new_branch_id)
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
                    $lang,
                    7
                );
            }
        }

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
                $lang,
                7
            );
        }
    }
    private function sendNotificationsForIT(JobRelatedPenalty $JobRelated, string $lang): void
    {
        $current_user_id = authActionSave()['by'];
        $assigned_employee = Employee::find($JobRelated->employee_id);

        if (!$assigned_employee) {
            return;
        }

        $position = Position::find($assigned_employee->possition_id);
        $department = Department::find($assigned_employee->department_id);
        $branch = Branch::find($assigned_employee->branch_id);

        $employee_full_name = $assigned_employee->first_name . ' ' . $assigned_employee->last_name;

        // Use safe access with null coalescing to avoid errors
        $position_name_ar = $position->name_ar ?? 'غير محدد';
        $position_name_en = $position->name_en ?? 'Not specified';
        $department_name_ar = $department->name_ar ?? 'غير محدد';
        $department_name_en = $department->name_en ?? 'Not specified';
        $branch_name_ar = $branch->name_ar ?? 'غير محدد';
        $branch_name_en = $branch->name_en ?? 'Not specified';

        $body_ar = $employee_full_name . ' هذا الموظف لقد تم الغاء صلاحيات الوصول  هذه ' . $JobRelated->restricted_system .
            ' في منصب ' . $position_name_ar .
            ' بقسم ' . $department_name_ar .
            ' بفرع ' . $branch_name_ar .
            ' بداية من تاريخ ' . $JobRelated->effective_date;

        $body_en = 'The employee ' . $employee_full_name .
            ' has had their access revoked to ' . $JobRelated->restricted_system .
            ' in the position of ' . $position_name_en .
            ' in the department of ' . $department_name_en .
            ' at the branch ' . $branch_name_en .
            ' starting from ' . $JobRelated->effective_date;


        $title_ar = 'الغاء صلاحيات الوصول للموظف';
        $title_en = 'Revocation of Employee Access Rights';

        // Notify all it employees in the same branch
        $department_it = Department::where('name_en', 'it')->first();
        $it_employees = Employee::where('department_id', $department_it->id)
            ->where('branch_id', $assigned_employee->branch_id)
            ->get();

        foreach ($it_employees as $it_employee) {
            if ($it_employee->device_token) {
                send_push_notification(
                    $it_employee->device_token,
                    $body_ar,
                    $body_en,
                    $title_ar,
                    $title_en,
                    'it',
                    $it_employee->id,
                    $current_user_id,
                    $assigned_employee->id,
                    $lang,
                    7
                );
            }
        }

        if ($assigned_employee->device_token) {
            send_push_notification(
                $assigned_employee->device_token,
                $body_ar,
                $body_en,
                $title_ar,
                $title_en,
                'it',
                $assigned_employee->id,
                $current_user_id,
                $assigned_employee->id,
                $lang,
                7
            );
        }
    }
    public function changeStatus(Request $request, $id)
    {
        $lang = app()->getLocale();

        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,approved,rejected',
            ]);

            if ($validator->fails()) {
                return respondError(
                    ($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'),
                    400,
                    $validator->errors()
                );
            }

            $JobRelated = JobRelatedPenalty::find($id);
            if (!$JobRelated) {
                return respondErrorData(
                    ($lang == 'en' ? ['Not existing any more'] : ['غير موجود']),
                    404,
                    $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']
                );
            }
            $employee = Employee::find($JobRelated->employee_id);
            if ($request->status === 'approved') {
                if ($JobRelated->type === 'demotion') {
                    $employee->position_id = $JobRelated->new_possition_id;
                } elseif ($JobRelated->type === 'transfer') {
                    $employee->position_id = $JobRelated->new_possition_id;
                    $employee->department_id = $JobRelated->new_department_id;
                    $employee->branch_id = $JobRelated->new_branch_id;
                    $this->sendNotifications($JobRelated, $lang);
                } elseif ($JobRelated->type === 'restrict_system') {
                    $this->sendNotificationsForIT($JobRelated, $lang);
                }
                $employee->save();
            }
            $JobRelated->status = $request->status;
            $JobRelated->modified_by = authActionSave()['by'];
            $JobRelated->modified_by_type = authActionSave()['type'];
            $JobRelated->save();

            return ResponseWithSuccessData($lang, $JobRelated, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function report(Request $request)
    {
        $lang = app()->getLocale();

        try {
            $query = JobRelatedPenalty::with('documents', 'employee', 'newDepartment', 'currDepartment');
            // Filter: By Employee ID
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            // Filter: By Penalty Type (from the related reason model)
            if ($request->filled('type')) {
                $query->where('type', $request->type);
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
                $query->where('status', $request->approval_status);
            }

            return $query;
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Something went wrong'] : ['حدث خطأ ما']), 500);
        }
    }
}
