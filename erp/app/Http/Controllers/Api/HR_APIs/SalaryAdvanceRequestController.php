<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\PaymentFrequency;
use App\Models\SalaryAdvanceRequest;
use App\Models\SalaryAdvanceSetting;
use App\Services\HR_Services\HRRequestService;
use App\Services\HR_Services\HRServicesService;
use App\Services\HR_Services\SalaryAdvanceRequestService;
use Illuminate\Support\Facades\Validator;

class SalaryAdvanceRequestController extends Controller
{
    protected $salaryAdvanceRequestService;
    protected $lang;
    protected $hrServiceService;
    protected $hrRequestService;


    public function __construct(SalaryAdvanceRequestService $salaryAdvanceRequestService, HRRequestService $hrRequestService, HRServicesService $hrServiceService)
    {
        $this->salaryAdvanceRequestService = $salaryAdvanceRequestService;
        $this->hrServiceService = $hrServiceService;
        $this->hrRequestService = $hrRequestService;
        $this->lang =  app()->getLocale();
    }

    // Get all Salary Advance Requests
    public function index(Request $request)
    {
        $employee = auth('employee')->user();

        // Get supervised employees
        $child_employees = getSupervisedEmployees($employee->id);
        // Default containers
        $myRequests = collect();
        $employeeRequests = collect();
        $allRequests = $this->salaryAdvanceRequestService->getAll();

        // if ($employee->hasRole('HR_Manager')) {
        //     // HR Manager: get all requests
        //     $allRequests = $this->salaryAdvanceRequestService->getAll();
        //     // Separate "my" vs "employees"
        //     $myRequests = $allRequests->clone()->where('employee_id', $employee->id);
        //     $employeeRequests = $allRequests->clone()->where('employee_id', '!=',   $employee->id);
        // } else if ($child_employees && $child_employees->count() > 0) {
        //     // Supervisor: get requests of supervised employees
        //     $childEmployeeIds = $child_employees->pluck('id')->toArray();
        //     $employeeRequests = $this->salaryAdvanceRequestService->getAll()
        //         ->whereIn('employee_id', $childEmployeeIds);
        //     // Also include their own requests
        //     $myRequests = $this->salaryAdvanceRequestService->getAll()
        //         ->where('employee_id', $employee->id);
        // } else {
        //     // Regular employee: only their own requests
        //     $myRequests = $this->salaryAdvanceRequestService->getAll()
        //         ->where('employee_id', $employee->id);
        // }


        if ($employee->hasRole('HR_Manager')) {

            $myRequests = (clone $allRequests)->where('employee_id', $employee->id);
            $employeeRequests = (clone $allRequests)->where('employee_id', '!=', $employee->id);
        } elseif ($child_employees && $child_employees->count() > 0) {

            $childIds = $child_employees->pluck('id')->toArray();
            $myRequests = (clone $allRequests)->where('employee_id', $employee->id);
            $employeeRequests = (clone $allRequests)->whereIn('employee_id', $childIds);
        } else {

            $myRequests = (clone $allRequests)->where('employee_id', $employee->id);
            $employeeRequests = SalaryAdvanceRequest::query()->whereRaw('1 = 0');
        }

        $myRequestsData = paginateOrGetAll($myRequests, $request, []);
        $employeeRequestsData = paginateOrGetAll($employeeRequests, $request, []);

        // Handle pagination (if needed)
        // $myRequestsData = paginateOrGetAll($myRequests, $request, [], []);
        // $employeeRequestsData = paginateOrGetAll($employeeRequests, $request, [], []);

        // Build response
        $data = [
            'my' => $myRequestsData['data'],
            'employees' => $employeeRequestsData['data'],
        ];

        return ResponseWithSuccessData($this->lang, $data, 1);
    }



    // Get a single Salary Advance Request
    public function show($id)
    {
        $employee = auth('employee')->user();
        $flag = $employee->flag;
        if ($flag != 'employee' && $flag != 'hr') {
            return RespondWithBadRequest($this->lang, 2);
        }
        $salaryAdvanceRequest = SalaryAdvanceRequest::find($id);

        if (!$salaryAdvanceRequest) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        if (!$employee->hasRole('HR_Manager')) {
            $request = $this->salaryAdvanceRequestService->getById($id);
            if ($request->employee_id != $employee->id) {
                return RespondWithBadRequest($this->lang, 2);
            }
            return ResponseWithSuccessData($this->lang, $request, 1);
        }
        try {
            $request = $this->salaryAdvanceRequestService->getById($id);
            return ResponseWithSuccessData($this->lang, $request, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequest($this->lang, 2);
        }
    }

    // Create a new Salary Advance Request
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $employee = auth('employee')->user();
        // if (!$employee->hasRole('HR_Manager')) {
        //     $message = $this->lang == 'en' ? 'not allowed' : 'غير مسموح';
        //     return respondError($message, 403);

        //     // return RespondWithBadRequest($this->lang, 2);
        // }
        $validator = Validator::make($request->all(), [

            'amount' => 'required',
            'reason' => 'required|string',
            // 'deduction_month' => 'nullable|date',
            // 'status' => 'required|in:pending,approved,rejected',
            // 'hr_comment' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            $message = $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق من الصحة.';
            return respondError($message, 400, $validator->errors());
        }
        $validated = $validator->validated();
        // dd($validated);
        $validated['created_by'] =  authActionSave()['by'];
        $validated['created_by_type'] = authActionSave()['type'];
        $validated['employee_id'] =  $employee->id;
        $validated['status'] =  "pending";

        $settings = SalaryAdvanceSetting::first();
        if (!$settings) {
            $message = $lang == 'en' ? 'Salary advance policy not configured.' : 'سياسة السلفة غير موجودة.';
            return respondError($message, 400);
        }

        $employee_salary = $employee->salary;
        $amount_percentage = $employee_salary > 0
            ? ($request->amount / $employee_salary) * 100
            : 0;
        if ($amount_percentage > $settings->max_percentage) {
            $message = $lang == 'en' ? 'Your salary advance more than Salary advance policy.' :  'السلفة المطلوبة تتجاوز سياسة السلفة.';
            return respondError($message, 400);
        }

        $employeePayrollSetting = Employee::find($employee->id)->activePayrollSetting();
        if (!$employeePayrollSetting) {
            $message = $lang == 'en' ? 'Employee payroll settings not configured.' :  ' إعدادات رواتب الموظفين غير موجودة.';
            return respondError($message, 400);
        }
        // ✅ Calculate salary base
        $baseSalary = $employeePayrollSetting->salary_value;

        $maxAllowed = ($settings->max_percentage / 100) * $baseSalary;

        // ✅ Enforce max percentage rule
        if ($validated['amount'] > $maxAllowed) {
            $message = $lang == 'en' ? 'Requested amount exceeds maximum allowed (" . $settings->max_percentage . "% of salary).' :  'المبلغ المطلوب يتجاوز الحد الأقصى المسموح به (' . $settings->max_percentage . '% من الراتب).';
            return respondError($message, 400);
        }
        if (!isset($validated['deduction_month'])) {
            if ($employeePayrollSetting->paymentFrequency->name_en == 'weekly') {
                $column = 'payment_weekday'; // stored as index like 0-6

                $index = PaymentFrequency::where('name_en', $employeePayrollSetting->paymentFrequency->name_en)
                    ->value($column);

                $weekdayMap = [
                    0 => 'Sunday',
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                ];

                $day = $weekdayMap[$index] ?? 'Friday'; // fallback to Friday if index invalid
                $dedaction_month = now()->next($day)->format('Y-m-d');
            } elseif ($employeePayrollSetting->paymentFrequency->name_en == 'monthly') {
                $column = 'payment_monthday';
                $day = PaymentFrequency::where('name_en', $employeePayrollSetting->paymentFrequency->name_en)
                    ->value($column);
                $dedaction_month = now()->addMonth()->day($day)->format('Y-m-d');
            } else {
                $dedaction_month = now()->addDays(1)->format('Y-m-d');
            }
        }
        $validated['deduction_month'] = $dedaction_month;
        $salaryAdvanceRequest = $this->salaryAdvanceRequestService->create($validated);
        $hr_service_id = $this->hrServiceService->getByKey('SalaryAdvance');

        $data = [
            'employee_id' => $employee->id, // Assuming `users` table for employees
            'hr_service_id' => $hr_service_id, // Assuming `hr_services` table
            'request_id' => $salaryAdvanceRequest->id,
            'status' => 'pending',
        ];
        $data['created_by'] =  authActionSave()['by'];
        $data['created_by_type'] = authActionSave()['type'];

        $hrRequest = $this->hrRequestService->create($data);
        return ResponseWithSuccessData($this->lang, $salaryAdvanceRequest, 1);
    }

    // Update an existing Salary Advance Request
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $employee = auth('employee')->user();
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'reason' => 'required|string',
        ]);
        $validated['updated_by'] =  authActionSave()['by'];
        $validated['updated_by_type'] = authActionSave()['type'];
        if (SalaryAdvanceRequest::find($id)->status != 'pending' || SalaryAdvanceRequest::find($id)->employee_id != $employee->id) {
            $message = $this->lang == 'en' ? 'not allowed' : 'غير مسموح';
            return respondError($message, 401);
        }
        try {
            $salaryAdvanceRequest = $this->salaryAdvanceRequestService->update($id, $validated);
            return ResponseWithSuccessData($lang, $salaryAdvanceRequest, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequest($this->lang, 2);
        }
    }

    // Delete a Salary Advance Request
    public function destroy($id)
    {
        $employee = auth('employee')->user();

        if (SalaryAdvanceRequest::find($id)->status != 'Pending' || SalaryAdvanceRequest::find($id)->employee_id != $employee->id) {
            $message = $this->lang == 'en' ? 'not allowed' : 'غير مسموح';
            return respondError($message, 401);
        }
        try {
            $this->salaryAdvanceRequestService->delete($id);
            return RespondWithSuccessRequest($this->lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequest($this->lang, 2);
        }
    }
    function changeStatus($id, Request $request)
    {
        $employee = auth('employee')->user();
        $flag = $employee->flag;
        if (!$employee->hasRole('HR_Manager')) {
            return RespondWithBadRequest($this->lang, 2);
        }
        $salaryAdvanceRequest = SalaryAdvanceRequest::find($id);

        if (!$salaryAdvanceRequest) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'hr_comment' => 'nullable|string',
            'advance_date' => 'required|date'
        ]);
        $validated['approved_by'] =  authActionSave()['by'];
        $validated['approved_by_type'] = authActionSave()['type'];
        try {
            $salaryAdvanceRequest = $this->salaryAdvanceRequestService->update($id, $validated);
            $hr_service_id = $this->hrServiceService->getByKey('SalaryAdvance');
            $hr_request_id = $this->hrRequestService->getByRequestId($salaryAdvanceRequest->id, $hr_service_id, $salaryAdvanceRequest->employee_id)->id;

            $data = [
                'employee_id' => $employee->id, // Assuming `users` table for employees
                'hr_service_id' => $hr_service_id, // Assuming `hr_services` table
                'request_id' => $salaryAdvanceRequest->id,
                'status' => $validated['status'],
            ];
            $data['updated_by'] =  authActionSave()['by'];
            $data['updated_by_type'] = authActionSave()['type'];

            $hrRequest = $this->hrRequestService->update($hr_request_id, $data);
            return   ResponseWithSuccessData($this->lang, $salaryAdvanceRequest, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequest($this->lang, 2);
        }
    }
}
