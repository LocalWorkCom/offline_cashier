<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TerminationApproval;
use App\Models\TerminationDocument;
use App\Models\TerminationOfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployeeTerminationController extends Controller
{

    protected $employee;

    public function __construct(EmployeeController $employee)
    {
        $this->employee = $employee;
    }
    public function allEmployee(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $employees = Employee::all();
            return ResponseWithSuccessData($lang, $employees, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function allTermination(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $user = auth()->user();
        if ($user->hasRole('HR_Manager')) {
            $terminations = TerminationOfService::query()
                ->with(['employee', 'documents'])
                ->when($request->filled('employee_id'), function ($q) use ($request) {
                    $q->where('employee_id', $request->employee_id);
                })
                ->when($request->filled('from_date') && $request->filled('to_date'), function ($q) use ($request) {
                    $q->whereBetween('created_at', [$request->from_date, $request->to_date]);
                })
                ->get();

            $terminationResponse['data'] = [
                'my' => $terminations->where('employee_id', $user->id)->values(),
                'employees' => $terminations->where('employee_id', '!=', $user->id)->values(),
            ];
            $terminationResponse['meta'] = [
                'totalItems' => count($terminationResponse['data']['employees']) + count($terminationResponse['data']['my']),
                'itemsPerPage' => 'all',
                'totalPages' => 1,
                'currentPage' => 1,
            ];;

            return ResponseWithSuccessDataPaginated($lang, $terminationResponse, 1);
        }

        $supervisedEmployees = getSupervisedEmployees($user->id);
        if ($supervisedEmployees && $supervisedEmployees->count() > 0) {
            $employeeIds = $supervisedEmployees->pluck('id')->toArray();

            $terminations = TerminationOfService::query()
                ->with(['employee', 'documents'])
                ->where(function ($query) use ($user, $employeeIds, $request) {
                    $query->where('employee_id', $user->id)
                        ->orWhereIn('employee_id', $employeeIds);

                    if ($request->has('employee_id')) {
                        $query->where('employee_id', $request->employee_id);
                    }
                    if ($request->has('from_date') && $request->has('to_date')) {
                        $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
                    }
                })
                ->get();

            $terminationResponse['data'] = [
                'my' => $terminations->where('employee_id', $user->id)->values(),
                'employees' => $terminations->where('employee_id', '!=', $user->id)->values(),
            ];
            $terminationResponse['meta'] = [
                'totalItems' => count($terminationResponse['data']['employees']) + count($terminationResponse['data']['my']),
                'itemsPerPage' => 'all',
                'totalPages' => 1,
                'currentPage' => 1,
            ];;

            return ResponseWithSuccessDataPaginated($lang, $terminationResponse, 1);
        }
        $terminations = TerminationOfService::query()
            ->with(['employee', 'documents'])
            ->where('employee_id', $user->id)
            ->when($request->has($request->has('from_date') && $request->has('to_date')), function ($q) use ($request) {
                $q->whereBetween('created_at', [$request->from_date, $request->to_date]);
            })
            ->get();

        $terminationResponse['data'] = [
            'my' => $terminations->where('employee_id', $user->id)->values(),
            'employees' => collect([]),
        ];
        $terminationResponse['meta'] = [
            'totalItems' => count($terminationResponse['data']['employees']) + count($terminationResponse['data']['my']),
            'itemsPerPage' => 'all',
            'totalPages' => 1,
            'currentPage' => 1,
        ];;

        return ResponseWithSuccessDataPaginated($lang, $terminationResponse, 1);
    }

    public function showTermination(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        $user = auth()->user();

        try {
            $termination = TerminationOfService::with(['employee', 'documents'])->find($id);

            if (!$termination) {
                return RespondWithBadRequestData($lang, 8); // Record not found
            }

            if ($user->hasRole('HR_Manager')) {
                return ResponseWithSuccessData($lang, $termination, 1);
            }

            $supervisedEmployees = getSupervisedEmployees($user->id);
            if ($supervisedEmployees && $supervisedEmployees->count() > 0) {
                $employeeIds = $supervisedEmployees->pluck('id')->toArray();

                if (
                    $termination->employee_id === $user->id ||
                    in_array($termination->employee_id, $employeeIds)
                ) {
                    return ResponseWithSuccessData($lang, $termination, 1);
                }

                return RespondWithBadRequest($lang, 2);
            }

            if ($termination->employee_id !== $user->id) {
                return RespondWithBadRequest($lang, 2);
            }

            return ResponseWithSuccessData($lang, $termination, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequest($lang, 0); // General error
        }
    }


    public function addTermination(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $user_id = authActionSave()['by'];
            $user = auth()->user();
            $validator = Validator::make($request->all(), [
                'employee_id'        => 'required|integer|exists:employees,id|unique:termination_of_services,employee_id',
                'reason'             => 'required|string',
                'start_date'         => 'required|date',
                'end_date'           => 'required|date|after_or_equal:start_date',
                'severance_package'  => 'nullable|integer',
                // 'approval_workflow'  => 'nullable|string|in:Pending,Approved,Rejected',
                'documents' => 'nullable|array',
                'documents.*' => 'file|mimes:png,jpg,jpeg,pdf,doc,docx',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => $validator->errors(),
                    'validation_type' => true
                ], 400);
            }
            if (!$user->hasRole('HR_Manager')) {
                $employees = getSupervisedEmployees($user_id);
                if ($employees == null) {
                    return respondError(__(
                        $lang == 'ar'
                            ? 'غير مصرح لك بإنهاء هذا الموظف.'
                            : 'You are not authorized to create termination for this employee.'
                    ), 403);
                }
                $employees = $employees->pluck('id')->toArray();
                $flagSuperVisor = in_array($request->employee_id, $employees);

                if (!$flagSuperVisor) {
                    return respondError(__(
                        $lang == 'ar'
                            ? 'غير مصرح لك بإنهاء هذا الموظف.'
                            : 'You are not authorized to create termination for this employee.'
                    ), 403);
                }
            }
            $validated = $validator->validated();

            $validated['last_day'] = $validated['end_date'];
            $validated['created_by'] = authActionSave()['by'];
            $validated['created_by_type'] = authActionSave()['type'];

            $termination = TerminationOfService::create($validated);

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = 'terminations/documents';

                    // UploadFile('images/terminations_documents', 'file_path', $document, $file);

                    if (!file_exists(public_path($path))) {
                        mkdir(public_path($path), 0777, true);
                    }
                    $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path($path), $filename);

                    $document = new TerminationDocument();
                    $document->file_path = url($path . '/' . $filename);
                    $document->original_name = $file->getClientOriginalName();
                    $document->mime_type = $file->getClientMimeType();
                    $document->termination_id = $termination->id;
                    $document->created_by = authActionSave()['by'];
                    $document->created_by_type = authActionSave()['type'];
                    $document->save();
                }
            }


            TerminationApproval::create(
                [
                    'termination_id' => $termination->id,
                    'viewed' => false,
                ]
            );

            $employee = Employee::where('id', $validated['employee_id'])->first();
            $fullUrl = url()->current();
            $apiBaseUrl = Str::before($fullUrl, '/api');
            $url = $apiBaseUrl . '/employee_termination/' . $termination->id;
            // dd($url);

            if ($employee) {
                $notificationSent = false;
                $requestId = $termination->id;
                // Only send push, don't store in DB inside helper
                if (!empty($employee->device_token)) {
                    $notification = send_push_notification(
                        $employee->device_token,
                        "سوف تنهي خدمتك في يوم {$validated['last_day']} بسبب {$validated['reason']} مع تعويضات مقدارها {$validated['severance_package']}",
                        "Your service will terminate on {$validated['last_day']} due to {$validated['reason']} with severance package in the amount of {$validated['severance_package']}",
                        "أنهاء الخدمة",
                        "Termination",
                        "employee",
                        $employee->id,
                        authActionSave()['by'],
                        $requestId,
                        app()->getLocale(),
                        10,
                        $url
                    );
                    $notificationSent = $notification !== false;
                }

                $notificationResult = [
                    'user_id' => $employee->id,
                    'notification_sent' => $notificationSent,
                ];
            }

            $parentDepartmentIds = $this->employee->getParentEmployees($employee->id, $lang);
            $created_emp_term = Employee::where('id', $validated['created_by'])->first();
            $data = [
                'id' => $termination->id,
                'employee' => $employee->first_name . ' ' . $employee->last_name,
                'reason' => $termination->reason,
                'start_date' => $termination->start_date,
                'end_date' => $termination->end_date,
                'created_by' => $created_emp_term->first_name . ' ' . $created_emp_term->last_name,
            ];

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return RespondWithBadRequestData($lang, 2);;
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function updateTermination(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            $user_id = authActionSave()['by'];
            $user = auth()->user();

            $validator = Validator::make($request->all(), [
                'employee_id'        => 'required|integer|exists:employees,id',
                'reason'             => 'required|string',
                'start_date'         => 'required|date',
                'end_date'           => 'required|date|after_or_equal:start_date',
                'severance_package'  => 'nullable|integer',
                // 'approval_workflow'  => 'nullable|string',
                'documents'          => 'nullable|array',
                'documents.*'        => 'file|mimes:png,jpg,jpeg,pdf,doc,docx',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => $validator->errors(),
                    'validation_type' => true
                ], 400);
            }

            $validated = $validator->validated();

            $validated['last_day'] = $validated['end_date'];
            $validated['modified_by'] = authActionSave()['by'];
            $validated['modified_by_type'] = authActionSave()['type'];

            $termination = TerminationOfService::find($id);
            if (!$termination) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            if (!$user->hasRole('HR_Manager')) {
                $employees = getSupervisedEmployees($user_id);
                if ($employees == null) {
                    return respondError(__(
                        $lang == 'ar'
                            ? 'غير مصرح لك بإنهاء هذا الموظف.'
                            : 'You are not authorized to create termination for this employee.'
                    ), 403);
                }
                $employees = $employees->pluck('id')->toArray();
                $flagSuperVisor = in_array($request->employee_id, $employees);
                $flagSuperVisor2 = in_array($termination->employee_id, $employees);
                if (!$flagSuperVisor || !$flagSuperVisor2) {
                    return respondError(__(
                        $lang == 'ar'
                            ? 'غير مصرح لك بإنهاء هذا الموظف.'
                            : 'You are not authorized to create termination for this employee.'
                    ), 403);
                }
            }
            $termination->update($validated);

            // handle documents
            if ($request->hasFile('documents')) {
                // remove old docs
                TerminationDocument::where('termination_id', $termination->id)->delete();

                foreach ($request->file('documents') as $file) {
                    $path = 'terminations/documents';
                    if (!file_exists(public_path($path))) {
                        mkdir(public_path($path), 0777, true);
                    }
                    $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path($path), $filename);

                    $document = new TerminationDocument();
                    $document->file_path = url($path . '/' . $filename);
                    $document->original_name = $file->getClientOriginalName();
                    $document->mime_type = $file->getClientMimeType();
                    $document->termination_id = $termination->id;
                    $document->created_by = authActionSave()['by'];
                    $document->created_by_type = authActionSave()['type'];
                    $document->save();
                }
            }

            // approval reset
            $approval = TerminationApproval::firstOrNew(['termination_id' => $termination->id]);
            $approval->viewed = false;
            $approval->save();

            $employee = Employee::where('id', $validated['employee_id'])->first();
            $fullUrl = url()->current();
            $apiBaseUrl = Str::before($fullUrl, '/api');
            $url = $apiBaseUrl . '/employee_termination/' . $termination->id;

            if ($employee) {
                $notificationSent = false;
                $requestId = $termination->id;

                if (!empty($employee->device_token)) {
                    $notification = send_push_notification(
                        $employee->device_token,
                        "سوف تنهي خدمتك في يوم {$validated['last_day']} بسبب {$validated['reason']} مع تعويضات مقدارها {$validated['severance_package']}",
                        "Your service will terminate on {$validated['last_day']} due to {$validated['reason']} with severance package in the amount of {$validated['severance_package']}",
                        "إنهاء الخدمة",
                        "Termination",
                        "employee",
                        $employee->id,
                        authActionSave()['by'],
                        $requestId,
                        app()->getLocale(),
                        10,
                        $url
                    );
                    $notificationSent = $notification !== false;
                }

                $notificationResult = [
                    'user_id' => $employee->id,
                    'notification_sent' => $notificationSent,
                ];
            }

            $parentDepartmentIds = $this->employee->getParentEmployees($employee->id, $lang);
            $modified_emp_term = Employee::where('id', $validated['modified_by'])->first();

            $data = [
                'id' => $termination->id,
                'employee' => $employee->first_name . ' ' . $employee->last_name,
                'reason' => $termination->reason,
                'start_date' => $termination->start_date,
                'end_date' => $termination->end_date,
                'modified_by' => $modified_emp_term->first_name . ' ' . $modified_emp_term->last_name,
            ];

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return RespondWithBadRequestData($lang, 2);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function deleteTermination(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            $user = auth()->user();

            $termination = TerminationOfService::with('documents')->where('id', $id)->first();
            if (!$termination) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            // soft delete documents
            $ter = TerminationOfService::find($id);
            if (!$ter) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            if (!$user->hasRole('HR_Manager')) {
                $employees = getSupervisedEmployees($user->id);
                if ($employees == null) {
                    return respondError(__(
                        $lang == 'ar'
                            ? 'غير مصرح لك بإنهاء هذا الموظف.'
                            : 'You are not authorized to create termination for this employee.'
                    ), 403);
                }
                $employees = $employees->pluck('id')->toArray();
                $flagSuperVisor2 = in_array($ter->employee_id, $employees);
                if (!$flagSuperVisor2) {
                    return respondError(__(
                        $lang == 'ar'
                            ? 'غير مصرح لك بإنهاء هذا الموظف.'
                            : 'You are not authorized to create termination for this employee.'
                    ), 403);
                }
            }
            $ter->deleted_by = authActionSave()['by'];
            $ter->deleted_by_type = authActionSave()['type'];
            $ter->save();
            foreach ($termination->documents as $document) {
                $document->delete(); // soft delete
            }
            // soft delete approval
            TerminationApproval::where('termination_id', $termination->id)->delete();

            $deleted_emp_term = Employee::where('id', $user->id)->first();

            // soft delete termination
            $termination->delete();

            $employee = Employee::find($termination->employee_id);

            $data = [
                'id' => $termination->id,
                'employee' => $employee->first_name . ' ' . $employee->last_name,
                'reason' => $termination->reason,
                'start_date' => $termination->start_date,
                'end_date' => $termination->end_date,
                'deleted_by' => $deleted_emp_term->first_name . ' ' . $deleted_emp_term->last_name,
            ];

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function approval_request_change_status(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $approval = TerminationApproval::where('termination_id', $id)->first();
        if (!$approval) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        $termination = TerminationOfService::where('id', $id)->first();
        $emp = Employee::where('id', $termination->employee_id)->first();

        $employee = auth('employee')->user();
        $validator = Validator::make($request->all(), [
            'status_employee' => 'nullable|string|in:pending,approved,rejected',
            'status_hr' => 'nullable|string|in:pending,approved,rejected',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ], 400);
        }

        $data = $validator->validated();


        if (!$approval) {
            return RespondWithBadRequestData($lang, 8);
        }

        // dd($employee->id);
        if (isset($data['status_employee']) && $employee->id === $termination->employee_id && $approval->status_employee != $data['status_employee']) {

            $approval->status_employee = $data['status_employee'];
            $approval->viewed = true;
            $approval->viewed_at = now();
        } elseif (isset($data['status_hr']) && ($employee->flag === "hr" || $employee->flag === "admin") && $approval->status_hr != $data['status_hr']) {
            // dd(0);
            $approval->status_hr = $data['status_hr'];
        } else {
            return RespondWithBadRequestData($lang, 2);
        }
        $approval->save();

        return ResponseWithSuccessData($lang, $approval, 1);
    }

    public function viewed_notification(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $approval = TerminationApproval::where('termination_id', $id)->first();
        if (is_null($approval->viewed_at)) {
            $approval->viewed_at = now();
            $approval->viewed = true;
        }
        $approval->save();
        return ResponseWithSuccessData($lang, $approval, 1);
    }

    // public function generate_termination_report(Request $request)
    // {
    //     $lang = $request->header('lang', 'ar');

    //     if (!CheckTokenEmployee()) {
    //         return RespondWithBadRequest($lang, 5);
    //     }

    //     $data = $request->validate([
    //         'data_report' => 'required|string'
    //     ]);
    //     $report = [
    //         'content' => $data["data_report"]
    //     ];
    //     return ResponseWithSuccessData($lang, $report, 1);
    // }
    public function generate_termination_report(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            $validated = $request->validate([
                'employee_id' => 'nullable|integer|exists:employees,id',
                'from_date'   => 'nullable|date',
                'to_date'     => 'nullable|date|after_or_equal:from_date',
            ]);

            $user = auth()->user();

            if ($user->hasRole('HR_Manager')) {
                $terminations = TerminationOfService::query()
                    ->with(['employee', 'documents'])
                    ->when(!empty($validated['employee_id']), function ($q) use ($validated) {
                        $q->where('employee_id', $validated['employee_id']);
                    })
                    ->when(!empty($validated['from_date']) && !empty($validated['to_date']), function ($q) use ($validated) {
                        $q->whereBetween('created_at', [$validated['from_date'], $validated['to_date']]);
                    })
                    ->get();

                $terminationResponse['data'] = [
                    'my' => $terminations->where('employee_id', $user->id)->values(),
                    'employees' => $terminations->where('employee_id', '!=', $user->id)->values(),
                ];

                $terminationResponse['meta'] = [
                    'totalItems'   => count($terminationResponse['data']['employees']) + count($terminationResponse['data']['my']),
                    'itemsPerPage' => 'all',
                    'totalPages'   => 1,
                    'currentPage'  => 1,
                ];

                return ResponseWithSuccessDataPaginated($lang, $terminationResponse, 1);
            }

            $supervisedEmployees = getSupervisedEmployees($user->id);
            if ($supervisedEmployees && $supervisedEmployees->count() > 0) {
                $employeeIds = $supervisedEmployees->pluck('id')->toArray();

                $terminations = TerminationOfService::query()
                    ->with(['employee', 'documents'])
                    ->where(function ($query) use ($user, $employeeIds, $validated) {
                        $query->where('employee_id', $user->id)
                            ->orWhereIn('employee_id', $employeeIds);

                        if (!empty($validated['employee_id'])) {
                            $query->where('employee_id', $validated['employee_id']);
                        }

                        if (!empty($validated['from_date']) && !empty($validated['to_date'])) {
                            $query->whereBetween('created_at', [$validated['from_date'], $validated['to_date']]);
                        }
                    })
                    ->get();

                $terminationResponse['data'] = [
                    'my' => $terminations->where('employee_id', $user->id)->values(),
                    'employees' => $terminations->where('employee_id', '!=', $user->id)->values(),
                ];

                $terminationResponse['meta'] = [
                    'totalItems'   => count($terminationResponse['data']['employees']) + count($terminationResponse['data']['my']),
                    'itemsPerPage' => 'all',
                    'totalPages'   => 1,
                    'currentPage'  => 1,
                ];

                return ResponseWithSuccessDataPaginated($lang, $terminationResponse, 1);
            }

            $terminations = TerminationOfService::query()
                ->with(['employee', 'documents'])
                ->where('employee_id', $user->id)
                ->when(!empty($validated['from_date']) && !empty($validated['to_date']), function ($q) use ($validated) {
                    $q->whereBetween('created_at', [$validated['from_date'], $validated['to_date']]);
                })
                ->get();

            $terminationResponse['data'] = [
                'my' => $terminations->where('employee_id', $user->id)->values(),
                'employees' => collect([]),
            ];

            $terminationResponse['meta'] = [
                'totalItems'   => count($terminationResponse['data']['my']),
                'itemsPerPage' => 'all',
                'totalPages'   => 1,
                'currentPage'  => 1,
            ];

            return ResponseWithSuccessDataPaginated($lang, $terminationResponse, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequest($lang, 0);
        }
    }
}
