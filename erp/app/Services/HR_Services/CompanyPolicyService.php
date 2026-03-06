<?php

namespace App\Services\HR_Services;

use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyAcknowledgement;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class CompanyPolicyService
{
    public function index($request)
    {
        $lang = app()->getLocale();
        $employee = auth('employee')->user();

        $query = CompanyPolicy::where('is_active', 1);

        if ($employee->hasRole(['HR Admin', 'HR_Manager', 'superAdmin'])) {
            // HR Admin and HR Manager can query by any company_id or list all policies
            if ($request->has('company_id')) {
                $validator = Validator::make($request->all(), [
                    'company_id' => 'nullable|exists:company_profile_settings,id',
                ]);

                if ($validator->fails()) {
                    return [
                        'status' => false,
                        'message' => $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                        'data' => null,
                        'errorData' => $validator->errors(),
                        'validation_type' => true
                    ];
                }

                $query->where('company_id', $request->company_id);
            }
        } else {
            // Non-HR admins or managers can only see their company's policies
            $query->where('company_id', $employee->branch->company->id);
        }

        return $query;
    }
    public function show($id)
    {
        $employee = auth('employee')->user();

        $policy = CompanyPolicy::find($id);

        // Record policy acknowledgment
        CompanyPolicyAcknowledgement::firstOrCreate(
            [
                'company_policy_id' => $policy->id,
                'employee_id' => $employee->id,
            ],
            [
                'viewed_at' => now(),
            ]
        );

        return $policy;
    }
    public function store($request)
    {
        $employee = auth('employee')->id();

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('documents/company-policies', $fileName, 'public');

        // Deactivate previous versions of the same policy
        // if ($request->filled('title')) {
        //     CompanyPolicy::where('company_id', $request->company_id)
        //         ->where('title', $request->title)
        //         ->update(['is_active' => 0]);
        // }

        $policy = CompanyPolicy::create([
            'company_id' => $request->company_id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'version' => $request->version ?? '1.0',
            'is_active' => 1,
            'created_by' => $employee,
            'updated_by' => $employee,
        ]);
        return $policy;
    }
    public function update($request, $id)
    {
        $lang = app()->getLocale();
        $employee = Auth::guard('employee')->id();

        $policy = CompanyPolicy::find($id);

        $filePath = $policy->file_path;
        if ($request->hasFile('file')) {
            // Delete old file if it exists
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents/company-policies', $fileName, 'public');
        }

        // Deactivate previous versions of the same policy if title changes
        // if ($request->filled('title') && $request->title !== $policy->title) {
        //     CompanyPolicy::where('company_id', $request->company_id)
        //         ->where('title', $request->title)
        //         ->update(['is_active' => 0]);
        // }

        $policy->update([
            'company_id' => $request->company_id ?? $policy->company_id,
            'title' => $request->title ?? $policy->title,
            'description' => $request->description ?? $policy->description,
            'file_path' => $filePath,
            'version' => $request->version ?? $policy->version,
            'is_active' => $request->is_active ?? $policy->is_active,
            'updated_by' => $employee,
        ]);

        // Send notifications to all active employees and collect results
        $notificationResults = [];
        $employees = Employee::where('employees.status', 'active')
            ->whereNotNull('employees.device_token')
            ->join('branches', 'employees.branch_id', '=', 'branches.id')
            ->where('branches.company_profile_setting_id', $policy->company_id)
            ->select('employees.*')
            ->get();

        $fullUrl = url()->current();
        $apiBaseUrl = Str::before($fullUrl, '/api');

        $url = $apiBaseUrl . '/company-policies/' . $request->id;

        foreach ($employees as $employee) {
            $notification = send_push_notification(
                 $employee->device_token,
                "تم تحديث سياسة الشركة: {$policy->title}",
                "Company policy updated: {$policy->title}",
                'تحديث سياسة الشركة',
                'Company Policy Update',
                'policy_update',
                $employee->id,
                 Auth::guard('employee')->id(),
                $policy->id,
               $lang,8,
                 $url
            );

            $notificationResults[] = [
                'employee_id' => $employee->id,
                'employee_email' => $employee->email,
                'notification_sent' => $notification !== false,
                'notification_details' => $notification ? [
                    'title' => $notification['title'],
                    'description' => $notification['description'],
                    'type' => 'policy_update',
                    'request_id' => $notification['request_id'],
                    // 'url' => $notification['url'],
                    'lang' => $lang,
                    'fcm_response' => $notification['fcm_response'] ?? null,
                ] : null,
                'error' => $notification === false ? 'Failed to send notification' : null,
            ];
        }

        return $policy;
    }
    public function acknowledgements($id)
    {
        $lang = app()->getLocale();

        $acknowledgements = CompanyPolicyAcknowledgement::where('company_policy_id', $id)
            ->join('employees', 'company_policy_acknowledgements.employee_id', '=', 'employees.id')
            ->select(
                'company_policy_acknowledgements.employee_id',
                'employees.first_name',
                'employees.last_name',
                'employees.email',
                'company_policy_acknowledgements.viewed_at'
            )
            ->get();

        if ($acknowledgements->isEmpty()) {
            return [
                'status' => false,
                'message' => $lang == 'en' ? 'No employees have viewed this policy.' : 'لم يشاهد أي موظف هذه السياسة.',
                'data' => $acknowledgements
            ];
        }

        $response = [
            'policy_id' => $id,
            'acknowledgement_count' => $acknowledgements->count(),
            'acknowledgements' => $acknowledgements->map(function ($ack) {
                return [
                    'employee_id' => $ack->employee_id,
                    'first_name' => $ack->first_name,
                    'last_name' => $ack->last_name,
                    'email' => $ack->email,
                    'viewed_at' => $ack->viewed_at ? $ack->viewed_at : null,
                ];
            }),
        ];

        return $response;
    }
    public function delete($id)
    {
        $policy = CompanyPolicy::find($id);

        if (Storage::disk('public')->exists($policy->file_path)) {
            Storage::disk('public')->delete($policy->file_path);
        }
        $policy->is_active = 0;
        $policy->delete();
        $policy->save();

        return $policy;
    }
}
