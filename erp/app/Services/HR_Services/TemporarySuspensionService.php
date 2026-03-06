<?php

namespace App\Services\HR_Services;

use App\Models\Employee;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TemporarySuspension;
use Illuminate\Support\Facades\Validator;


class TemporarySuspensionService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            $user = auth()->user();

            // 🔹 HR Manager — get all suspensions with filters
            if ($user->hasRole('HR_Manager')) {
                $suspensions = TemporarySuspension::query()
                    ->with(['employee'])
                    ->when($request->has('employee_id'), fn($q) => $q->where('employee_id', $request->employee_id))
                    ->when($request->has('department_id'), function ($q) use ($request) {
                        $q->whereHas('employee', fn($query) => $query->where('department_id', $request->department_id));
                    })
                    ->when($request->has('approval_status'), fn($q) => $q->where('approval_status', $request->approval_status))
                    ->when($request->has('reason'), fn($q) => $q->where('reason', 'like', "%{$request->reason}%"))
                    ->when($request->has('start_date'), fn($q) => $q->where('start_date', '>=', $request->start_date))
                    ->when($request->has('end_date'), fn($q) => $q->where('end_date', '<=', $request->end_date))
                    ->orderBy('created_at', 'desc')
                    ->get();

                return [
                    'my' => $suspensions->where('employee_id', $user->id)->values(),
                    'employees' => $suspensions->where('employee_id', '!=', $user->id)->values()
                ];
            }

            // 🔹 Supervisor — their own + supervised employees
            $supervisedEmployees = getSupervisedEmployees($user->id);
            if ($supervisedEmployees && $supervisedEmployees->count() > 0) {
                $employeeIds = $supervisedEmployees->pluck('id')->toArray();

                $suspensions = TemporarySuspension::query()
                    ->with(['employee'])
                    ->where(function ($query) use ($user, $employeeIds, $request) {
                        $query->where('employee_id', $user->id)
                            ->orWhereIn('employee_id', $employeeIds);

                        // Filters within supervisor scope
                        if ($request->has('employee_id')) {
                            $query->where('employee_id', $request->employee_id);
                        }
                        if ($request->has('approval_status')) {
                            $query->where('approval_status', $request->approval_status);
                        }
                        if ($request->has('reason')) {
                            $query->where('reason', 'like', "%{$request->reason}%");
                        }
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();

                return [
                    'my' => $suspensions->where('employee_id', $user->id)->values(),
                    'employees' => $suspensions->where('employee_id', '!=', $user->id)->values()
                ];
            }

            // 🔹 Regular employee — only their own suspensions
            $suspensions = TemporarySuspension::query()
                ->with(['employee'])
                ->where('employee_id', $user->id)
                ->when($request->has('approval_status'), fn($q) => $q->where('approval_status', $request->approval_status))
                ->when($request->has('reason'), fn($q) => $q->where('reason', 'like', "%{$request->reason}%"))
                ->when($request->has('start_date'), fn($q) => $q->where('start_date', '>=', $request->start_date))
                ->when($request->has('end_date'), fn($q) => $q->where('end_date', '<=', $request->end_date))
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'my' => $suspensions,
                'employees' => collect([])
            ];
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function report(Request $request)
    {
        $user = auth()->user();

        $employeeId = $request->input('employee_id');
        $employeeCode = $request->input('employee_code');
        $employeeName = $request->input('employee_name');
        $departmentId = $request->input('department_id');
        $approvalStatus = $request->input('approval_status');
        $reason = $request->input('reason');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = TemporarySuspension::with('employee');

        // ✅ HR Manager logic
        if ($user->hasRole('HR_Manager')) {
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            if ($employeeCode) {
                $query->whereHas('employee', function ($q) use ($employeeCode) {
                    $q->where('employee_code', 'like', "%{$employeeCode}%");
                });
            }

            if ($employeeName) {
                $query->whereHas('employee', function ($q) use ($employeeName) {
                    $nameParts = explode(' ', trim($employeeName));
                    if (count($nameParts) === 2) {
                        $q->where(function ($subQuery) use ($nameParts) {
                            $subQuery->where('first_name', 'LIKE', "%{$nameParts[0]}%")
                                ->where('last_name', 'LIKE', "%{$nameParts[1]}%");
                        })->orWhere(function ($subQuery) use ($nameParts) {
                            $subQuery->where('first_name', 'LIKE', "%{$nameParts[1]}%")
                                ->where('last_name', 'LIKE', "%{$nameParts[0]}%");
                        });
                    } else {
                        $q->where(function ($subQuery) use ($employeeName) {
                            $subQuery->where('first_name', 'LIKE', "%{$employeeName}%")
                                ->orWhere('last_name', 'LIKE', "%{$employeeName}%");
                        });
                    }
                });
            }

            if ($departmentId) {
                $query->whereHas('employee', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }
        }

        // ✅ Supervisor logic
        else {
            $supervisedEmployees = getSupervisedEmployees($user->id);

            if ($supervisedEmployees && $supervisedEmployees->count() > 0) {
                $employeeIds = $supervisedEmployees->pluck('id')->toArray();

                $query->where(function ($q) use ($user, $employeeIds, $employeeId) {
                    $q->where('employee_id', $user->id)
                        ->orWhereIn('employee_id', $employeeIds);

                    if ($employeeId) {
                        $q->where('employee_id', $employeeId);
                    }
                });

                if ($employeeName) {
                    $query->whereHas('employee', function ($q) use ($employeeName) {
                        $nameParts = explode(' ', trim($employeeName));
                        if (count($nameParts) === 2) {
                            $q->where(function ($subQuery) use ($nameParts) {
                                $subQuery->where('first_name', 'LIKE', "%{$nameParts[0]}%")
                                    ->where('last_name', 'LIKE', "%{$nameParts[1]}%");
                            })->orWhere(function ($subQuery) use ($nameParts) {
                                $subQuery->where('first_name', 'LIKE', "%{$nameParts[1]}%")
                                    ->where('last_name', 'LIKE', "%{$nameParts[0]}%");
                            });
                        } else {
                            $q->where(function ($subQuery) use ($employeeName) {
                                $subQuery->where('first_name', 'LIKE', "%{$employeeName}%")
                                    ->orWhere('last_name', 'LIKE', "%{$employeeName}%");
                            });
                        }
                    });
                }

                if ($departmentId) {
                    $query->whereHas('employee', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    });
                }
            } else {
                // Regular employee → only their own records
                $query->where('employee_id', $user->id);
            }
        }

        // ⭐ Common filters for all roles
        if ($approvalStatus) {
            $query->where('approval_status', $approvalStatus);
        }

        if ($reason) {
            $query->where('reason', 'like', "%{$reason}%");
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('start_date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->where('start_date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->where('end_date', '<=', $toDate);
        }

        return $query->orderByDesc('created_at');
    }


    public function store(Request $request)
    {
        $user = auth()->user();

        $validateData = Validator::make($request->all(), [
            'employee_id' => 'required|integer|exists:employees,id',


            'suspension_duration' => 'required|integer|min:1',
            'reason' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:png,jpg,jpeg,pdf,doc,docx'


        ]);

        if ($validateData->fails()) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validateData->errors(),
                'validation_type' => true
            ], 400);
        }



        // 🔹 Restrict supervisors to their supervised employees
        $supervisedEmployees = getSupervisedEmployees($user->id);
        if ($supervisedEmployees && $supervisedEmployees->count() > 0) {
            if (!$supervisedEmployees || !$supervisedEmployees->pluck('id')->contains($request->employee_id)) {
                return response()->json([
                    'code' => 403,
                    'status' => false,
                    'message' => 'You are not authorized to add temporary_suspensions for this employee.',
                    'data' => null,
                    'errorData' => null
                ], 403);
            }
        }

        $temporarySuspension = new TemporarySuspension();
        $temporarySuspension->employee_id = $request->employee_id;
        $temporarySuspension->suspension_duration = $request->suspension_duration;
        $temporarySuspension->reason = $request->reason;
        $temporarySuspension->start_date = $request->start_date;
        $temporarySuspension->end_date = $request->end_date;
        $temporarySuspension->approval_status = "pending";
        $temporarySuspension->created_by = authActionSave()['by'];
        $temporarySuspension->created_by_type = authActionSave()['type'];

        // In the store method, replace the document handling with:
        if ($request->hasFile('supporting_documents')) {
            $files = $request->file('supporting_documents');
            $documents = [];

            foreach ($files as $file) {
                // Use the actual model but don't save it yet
                // $tempModel = new TemporarySuspension();
                $temporarySuspension->id = $temporarySuspension->id; // Use the same ID

                // Use UploadFile helper for each individual file
                UploadFile('temporary_suspensions/documents', 'supporting_documents', $temporarySuspension, $file);

                // Get the uploaded file path from the temporary model
                $filePath = $temporarySuspension->supporting_documents;

                $documents[] = [
                    'file_path' => $filePath,
                    'original_name' => $file->getClientOriginalName(),
                ];
            }

            $temporarySuspension->supporting_documents = $documents;
        }
        $temporarySuspension->save();


        // Get the employee
        $employee = Employee::find($request->employee_id);

        // Prepare notification results
        $notificationResult = null;
        $fullUrl = url()->current();
        $apiBaseUrl = Str::before($fullUrl, '/api');
        $url = $apiBaseUrl . '/temporary_suspensions/' . $temporarySuspension->id;

        if ($employee) {
            $notificationSent = false;
            $requestId = $temporarySuspension->id;

        $url = $apiBaseUrl . '/temporary_suspensions/' . $temporarySuspension->id;

        if ($employee) {
            $notificationSent = false;
            $requestId = $temporarySuspension->id;


            // Only send push, don't store in DB inside helper
            if (!empty($employee->device_token)) {
                $notification = send_push_notification(

                    //  $employee->device_token,

                    $employee->device_token,
                    "تم إضافة ايقاف موقت  لك قيد المراجعه بعدد ايام {$temporarySuspension->suspension_duration}",
                    "A new temporary suspensions has been added  for you  pending with suspension_duration {$temporarySuspension->suspension_duration}",
                    'ايقاف موقت جديد',
                    'New temporary suspensions',
                    'employee',
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
        return response()->json([
            'data' => [

                'temporary_suspensions' => $temporarySuspension,

                // 'temporary_suspensions' => $temporarySuspension,

                'notification' => $notificationResult
            ]
        ]);
    }
}


    public function update(Request $request, string $id)
    {
        $lang = $request->header('lang', 'ar');

        // Fixed validation rules
        $validateData = Validator::make($request->all(), [
            'approval_status' => 'string|in:pending,approved,rejected',
            'suspension_duration' => 'nullable|integer|min:1',
            'reason' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:png,jpg,jpeg,pdf,doc,docx',
            // 'deleted_documents' => 'nullable|array',
            // 'deleted_documents.*' => 'string',
        ]);

        if ($validateData->fails()) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validateData->errors(),
                'validation_type' => true
            ], 400);
        }

        $temporarySuspension = TemporarySuspension::find($id);

        if (!$temporarySuspension) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        DB::beginTransaction();

        try {
            // Track changes to detect if any field was modified
            $originalData = $temporarySuspension->getOriginal();
            $isAnyFieldModified = false;
            $isStatusModified = false;

            // Update basic fields and track changes
            if ($request->has('approval_status') && $temporarySuspension->approval_status !== $request->approval_status) {
                $temporarySuspension->approval_status = $request->approval_status;
                $isAnyFieldModified = true;
                $isStatusModified = true;
            }

            if ($request->has('suspension_duration') && $temporarySuspension->suspension_duration != $request->suspension_duration) {
                $temporarySuspension->suspension_duration = $request->suspension_duration;
                $isAnyFieldModified = true;
            }

            if ($request->has('reason') && $temporarySuspension->reason !== $request->reason) {
                $temporarySuspension->reason = $request->reason;
                $isAnyFieldModified = true;
            }

            if ($request->has('start_date') && $temporarySuspension->start_date != $request->start_date) {
                $temporarySuspension->start_date = $request->start_date;
                $isAnyFieldModified = true;
            }

            if ($request->has('end_date') && $temporarySuspension->end_date != $request->end_date) {
                $temporarySuspension->end_date = $request->end_date;
                $isAnyFieldModified = true;
            }

            // Get current documents
            $currentDocuments = $temporarySuspension->supporting_documents ?? [];
            $originalDocumentCount = count($currentDocuments);

            // // Handle document deletions
            // if ($request->has('deleted_documents') && is_array($request->deleted_documents)) {
            //     foreach ($request->deleted_documents as $deletedFilePath) {
            //         // Validate that it's a string path
            //         if (is_string($deletedFilePath)) {
            //             // Remove from storage
            //             if (Storage::exists($deletedFilePath)) {
            //                 Storage::delete($deletedFilePath);
            //             }

            //             // Remove from current documents array
            //             $currentDocuments = array_filter($currentDocuments, function ($doc) use ($deletedFilePath) {
            //                 return isset($doc['file_path']) && $doc['file_path'] !== $deletedFilePath;
            //             });
            //         }
            //     }
            //     $isAnyFieldModified = true;
            // }


            // Handle new document uploads using your preferred approach
            if ($request->hasFile('supporting_documents')) {
                $files = $request->file('supporting_documents');


                foreach ($files as $file) {
                    // Use the EXISTING model instance instead of creating a new one
                    $tempModel = $temporarySuspension; // Use the existing instance

                    // Use UploadFile helper for each individual file
                    UploadFile('temporary_suspensions/documents', 'supporting_documents', $tempModel, $file);

                    // Get the uploaded file path from the model
                    $filePath = $tempModel->supporting_documents;

                    $newDocument = [
                        'file_path' => $filePath,
                        'original_name' => $file->getClientOriginalName(),
                    ];

                    // Add new document to current documents
                    $currentDocuments[] = $newDocument;
                }

                // Check if documents were actually added
                if (count($currentDocuments) > $originalDocumentCount) {
                    $isAnyFieldModified = true;
                }
            }

            // Update the supporting_documents field if changed
            if ($currentDocuments != $temporarySuspension->supporting_documents) {
                $temporarySuspension->supporting_documents = array_values($currentDocuments);
                $isAnyFieldModified = true;
            }

            // Only save if there are changes
            if ($isAnyFieldModified) {
                $temporarySuspension->modified_by = authActionSave()['by'];
                $temporarySuspension->modified_by_type = authActionSave()['type'];
                $temporarySuspension->save();
            }

            // Get the employee
            $employee = Employee::find($temporarySuspension->employee_id);

            // Prepare notification results
            $notificationResult = null;
            $fullUrl = url()->current();
            $apiBaseUrl = Str::before($fullUrl, '/api');
            $url = $apiBaseUrl . '/temporary_suspensions/' . $temporarySuspension->id;

            if ($employee && $isAnyFieldModified && !empty($employee->device_token)) {
                $notificationSent = false;
                $requestId = $temporarySuspension->id;

                // Case 1: Status was changed to approved or rejected
                if ($isStatusModified && in_array($request->approval_status, ['approved', 'rejected'])) {
                    // Prepare status-specific notification messages
                    $title_ar = $request->approval_status === 'approved' ? 'تم الموافقة على الايقاف الموقت' : 'تم رفض الايقاف الموقت';
                    $title_en = $request->approval_status === 'approved' ? 'Temporary Suspension Approved' : 'Temporary Suspension Rejected';

                    $description_ar = $request->approval_status === 'approved'
                        ? "تم الموافقة على الايقاف الموقت المطبق عليك"
                        : "تم رفض الايقاف الموقت المطبق عليك";

                    $description_en = $request->approval_status === 'approved'
                        ? "The temporary suspension applied to you has been approved"
                        : "The temporary suspension applied to you has been rejected";

                    // Add reason if available
                    if (!empty($request->reason)) {
                        $description_ar .= ". السبب: " . $request->reason;
                        $description_en .= ". Reason: " . $request->reason;
                    }

                    $notification = send_push_notification(
                        $employee->device_token,
                        $description_ar,
                        $description_en,
                        $title_ar,
                        $title_en,
                        'employee',
                        $employee->id,
                        authActionSave()['by'],
                        $requestId,
                        $lang,
                        10,
                        $url
                    );

                    $notificationSent = $notification !== false;
                }
                // Case 2: Other fields were modified (but status wasn't changed to approved/rejected)
                else if (!$isStatusModified) {
                    // General update notification
                    $title_ar = 'تم تحديث طلب الايقاف الموقت';
                    $title_en = 'Temporary Suspension Updated';

                    $description_ar = 'تم تحديث معلومات طلب الايقاف الموقت الخاص بك';
                    $description_en = 'Your temporary suspension request information has been updated';

                    $notification = send_push_notification(
                        $employee->device_token,
                        $description_ar,
                        $description_en,
                        $title_ar,
                        $title_en,
                        'employee',
                        $employee->id,
                        authActionSave()['by'],
                        $requestId,
                        $lang,
                        10,
                        $url
                    );

                    $notificationSent = $notification !== false;
                }

                $notificationResult = [
                    'user_id' => $employee->id,
                    'notification_sent' => $notificationSent,
                    'notification_type' => $isStatusModified ? 'status_update' : 'general_update'
                ];
            }

            DB::commit();

            // Format supporting documents for response
            $formattedDocuments = [];
            if ($temporarySuspension->supporting_documents) {
                foreach ($temporarySuspension->supporting_documents as $document) {
                    $formattedDocuments[] = [
                        'file_path' => $document['file_path'],
                        'original_name' => $document['original_name'] ?? null
                    ];
                }
            }

            $responseData = [
                'temporarySuspension' => $temporarySuspension,
                'notification' => $notificationResult,
                'changes_made' => $isAnyFieldModified,
                'status_changed' => $isStatusModified
            ];

            return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => 'Failed to update temporary suspension.',
                'data' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
