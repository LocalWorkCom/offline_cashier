<?php

namespace App\Services\HR_Services;

use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyAcknowledgement;
use App\Models\Employee;
use App\Models\EmployeeWarning;
use App\Models\WarningSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class EmployeeWarningService
{
    public function index($request)
    {
        $query = EmployeeWarning::with([
            'employee' => fn($query) => $query->select('id', 'first_name', 'last_name', 'email'),
            'hrManager' => fn($query) => $query->select('id', 'first_name', 'last_name', 'email')
        ]);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('issue_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('issue_date', '<=', $request->end_date);
        }

        return $query;
    }
    public function employeeWarnings($request)
    {
        $lang = app()->getLocale();

        $warnings = EmployeeWarning::with([
            'employee' => fn($query) => $query->select('id', 'first_name', 'last_name', 'email'),
            'hrManager' => fn($query) => $query->select('id', 'first_name', 'last_name', 'email')
        ])->where('employee_id', $request->employee_id)->get();

        if ($warnings->isEmpty()) {
            return [
                'status' => false,
                'message' => $lang == 'en' ? 'No warnings found for this employee.' : 'لم يتم العثور على تحذيرات لهذا الموظف.',
            ];
        }

        // Count total warnings
        $totalWarnings = $warnings->count();

        $response = [
            'employee_id' => (int) $request->employee_id,
            'total_warnings' => $totalWarnings,
            'warnings' => $warnings,
        ];
        return $response;
    }
    public function show($id)
    {
        $employee = auth('employee')->user();

        $warning = EmployeeWarning::with([
            'employee' => fn($query) => $query->select('id', 'first_name', 'last_name', 'email'),
            'hrManager' => fn($query) => $query->select('id', 'first_name', 'last_name', 'email')
        ])->find($id);

        // Update viewed_at if the authenticated employee is the one associated with the warning
        if ($warning->employee_id === $employee->id && is_null($warning->viewed_at)) {
            $warning->viewed_at = now();
            $warning->save();
        }

        return $warning;
    }
    public function store($request)
    {
        $lang = app()->getLocale();
        $hr_manager_id = auth('employee')->id();

        $employee = Employee::where('id', $request->employee_id)
            ->where('status', 'active')
            ->first();

        $documentPath = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $documentPath = $file->storeAs('documents/warnings', $fileName, 'public');
        }

        $warning = EmployeeWarning::create([
            'employee_id' => $request->employee_id,
            'hr_manager_id' => $hr_manager_id,
            'description' => $request->description,
            'issue_date' => $request->issue_date,
            'consequences' => $request->consequences,
            'action_plan' => $request->action_plan,
            'hr_signature' => $request->hr_signature,
            'approval_status' => 'pending',
            'document_path' => $documentPath,
            'acknowledgment_status' => 'pending',
        ]);

        $fullUrl = url()->current();
        $apiBaseUrl = Str::before($fullUrl, '/api');

        $url = $apiBaseUrl . '/employee-warnings/' . $warning->id;

        // Send notification to the employee
        if ($employee->device_token) {
            $notification = send_push_notification(
                 $employee->device_token,
                "تم إصدار تحذير كتابي لك: {$warning->description}",
                "A written warning has been issued to you: {$warning->description}",
                'تحذير كتابي جديد',
                'New Written Warning',
                'warning_issued',
                $employee->id,
                $hr_manager_id,
                $warning->id,
                $lang,7,
                $url
            );
        }
        // Check warning threshold
        $this->checkWarningThreshold($employee, $warning, $lang);

        return $warning;
    }
    public function acknowledge($request, $id)
    {
        $warning = EmployeeWarning::find($id);

        $warning->update([
            'acknowledgment_status' => $request->status === 'acknowledge' ? 'acknowledged' : 'refused',
            'acknowledged_at' => now(),
        ]);

        return $warning;
    }

    public function confirmDelivery($id)
    {
        $warning = EmployeeWarning::find($id);

        $warning->update([
            'acknowledgment_status' => 'hr_confirmed',
            'hr_confirmed_at' => now(),
        ]);

        return $warning;
    }
    public function update($request, $id)
    {
        $lang = app()->getLocale();

        $warning = EmployeeWarning::find($id);

        $employeeId = $request->employee_id ?? $warning->employee_id;

        $employee = Employee::where('id', $employeeId)
            ->where('status', 'active')
            ->first();

        // Handle document upload
        $documentPath = $warning->document_path;
        if ($request->hasFile('document')) {
            if ($documentPath && Storage::disk('public')->exists($documentPath)) {
                Storage::disk('public')->delete($documentPath);
            }

            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $documentPath = $file->storeAs('documents/warnings', $fileName, 'public');
        }

        $warning->update([
            'employee_id'    => $employeeId,
            'description'    => $request->description ?? $warning->description,
            'issue_date'     => $request->issue_date ?? $warning->issue_date,
            'consequences'   => $request->consequences ?? $warning->consequences,
            'action_plan'    => $request->action_plan ?? $warning->action_plan,
            'hr_signature'   => $request->hr_signature ?? $warning->hr_signature,
            'document_path'  => $documentPath,
        ]);

        // Eager load related models
        $warning->load([
            'employee:id,first_name,last_name,email',
            'hrManager:id,first_name,last_name,email'
        ]);

        // Check warning threshold
        $this->checkWarningThreshold($employee, $warning, $lang);

        return $warning;
    }
    public function delete($id)
    {
        $warning = EmployeeWarning::find($id);

        $warning->delete();

        return $warning;
    }
    protected function checkWarningThreshold(Employee $employee, EmployeeWarning $warning, $lang)
    {
        $setting = WarningSettings::whereNull('deleted_at')
            ->orderBy('days', 'desc')
            ->first();

        if (!$setting) {
            Log::warning('No Warning Setting Found', ['employee_id' => $employee->id]);
            return;
        }

        // Count warnings within the period
        $startDate = Carbon::now()->subDays($setting->days)->startOfDay();
        $warningCount = EmployeeWarning::where('employee_id', $employee->id)
            ->where('issue_date', '>=', $startDate)
            ->whereNull('deleted_at')
            ->count();

        if ($warningCount >= $setting->alert) {
            Log::info('Warning Threshold Reached', [
                'employee_id' => $employee->id,
                'warning_count' => $warningCount,
                'days' => $setting->days,
                'alert' => $setting->alert,
            ]);
            // Get HR users
            $hrUsers = Employee::where('flag', 'hr')
                ->where('status', 'active')
                ->whereNotNull('device_token')
                ->get();

            foreach ($hrUsers as $hr) {
                $notification = send_push_notification(
                     $hr->device_token,
                    "الموظف {$employee->first_name} {$employee->last_name} تلقى {$warningCount} تحذيرات خلال {$setting->days} يومًا. الإجراءات الموصى بها: خصم الراتب، التعليق، أو الإنهاء.",
                    "Employee {$employee->first_name} {$employee->last_name} received {$warningCount} warnings within {$setting->days} days. Recommended actions: Salary deduction, suspension, or termination.",
                    'تنبيه تحذيرات الموظف',
                    'Employee Warnings Alert',
                    'warning_threshold',
                    $hr->id,
                    Auth::guard('employee')->id(),
                    $employee->id,
                    $lang, 7,
                    url("/employee-warnings/track-employee-warnings?employee_id={$employee->id}")
                );
            }
        }
    }
}
