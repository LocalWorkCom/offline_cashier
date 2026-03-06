<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\BonusSettings;
use App\Http\Requests\BonusSettingsRequest;
use App\Models\BonusRequest;
use App\Models\BonusRequestTrack;
use App\Services\HR_Services\BonusSettingsService;
use App\Services\HR_Services\BounsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BounsController extends Controller
{
    protected $bounsService;
    protected $bonusSettingsService;


    public function __construct(BounsService $bounsService, BonusSettingsService $bonusSettingsService)
    {
        $this->bounsService = $bounsService;
        $this->bonusSettingsService = $bonusSettingsService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        $employee = auth('employee')->user();

        // Get supervised employees
        $child_employees = getSupervisedEmployees($employee->id);

        // Collectors
        $myRequests = collect();
        $employeeRequests = collect();

        // Filters (optional)
        $filters = $request->only(['department_id', 'status', 'bonus_type', 'from_date', 'to_date']);

        if ($employee->hasRole('HR_Manager')) {
            // HR Manager → get all bonus requests
            $allRequests = $this->bounsService->getFilteredBonusRequests($filters);

            // Split between my requests and others
            $myRequests = $allRequests->clone()->where('employee_id', $employee->id);
            $employeeRequests = $allRequests->clone()->where('employee_id', '!=', $employee->id);
        } elseif ($child_employees && $child_employees->count() > 0) {
            // Supervisor → get own + supervised employees’ bonus requests
            $childEmployeeIds = $child_employees->pluck('id')->toArray();

            $employeeRequests = $this->bounsService->getFilteredBonusRequests($filters)
                ->whereIn('employee_id', $childEmployeeIds);

            $myRequests = $this->bounsService->getFilteredBonusRequests($filters)
                ->where('employee_id', $employee->id);
        } else {
            // Regular employee → only their own requests
            $myRequests = $this->bounsService->getFilteredBonusRequests($filters)
                ->where('employee_id', $employee->id);
        }

        // Handle pagination
        $myRequestsData = paginateOrGetAll($myRequests, $request, []);
        $employeeRequestsData = paginateOrGetAll($employeeRequests, $request, []);

        // Response
        $data = [
            'my' => $myRequestsData,
            'employees' => $employeeRequestsData,
        ];

        return ResponseWithSuccessData($lang, $data, 1);
    }


    public function create(Request $request)
    {
        $lang = $request->header('lang', 'en');

        // Get settings
        $bonusSettings = $this->bonusSettingsService->index()->first();

        $maxPercentage = $bonusSettings->max_bonus_percentage;         // e.g. 4.45
        $maxFixedCap = $bonusSettings->fixed_bonus_cap;                // e.g. 1000
        $maxDays = (int)$bonusSettings->days_convertible_to_money;     // e.g. 10

        $messages = [
            'department_id.required' => __('bonus.custom.department_id.required'),
            'department_id.exists' => __('bonus.custom.department_id.exists'),

            'employee_id.required' => __('bonus.custom.employee_id.required'),
            'employee_id.exists' => __('bonus.custom.employee_id.exists'),

            'bonus_type.required' => __('bonus.custom.bonus_type.required'),
            'bonus_type.in' => __('bonus.custom.bonus_type.in'),

            'bonus_value.numeric' => __('bonus.custom.bonus_value.numeric'),
            'bonus_value.min' => __('bonus.custom.bonus_value.min'),
            'bonus_value.max' => __('bonus.custom.bonus_value.max'), // حتى لو مش مستخدم max حاليًا

            'reason.string' => __('bonus.custom.reason.string'),

            'payout_date.required' => __('bonus.custom.payout_date.required'),
            'payout_date.date' => __('bonus.custom.payout_date.date'),
        ];

        // Base rules
        $rules = [
            'department_id' => 'required|exists:departments,id',
            'employee_id' => 'required|exists:employees,id',
            'bonus_type' => 'required|in:amount,percentage,days',
            'bonus_value' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string',
            'payout_date' => 'required|date'
        ];

        // Dynamic validation based on bonus_type
        if ($request->bonus_type === 'percentage') {
            $rules['bonus_value'] .= "|max:$maxPercentage";
        } elseif ($request->bonus_type === 'amount') {
            $rules['bonus_value'] .= "|max:$maxFixedCap";
        } elseif ($request->bonus_type === 'days') {
            $rules['bonus_value'] .= "|max:$maxDays";
        }

        // Validate
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }
        // Proceed to create
        $bonusRequest = $this->bounsService->addBonusRequest($request->all());

        return ResponseWithSuccessData($lang, $bonusRequest, 1);
    }


    public function changeStatus(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');

        $validator = Validator::make($request->all(), [
            'status'       => 'required|in:Pending,Approved,Rejected,Processed',
            'reason'       => 'nullable|string|max:1000',
            'payout_date'  => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }
        if ($request->status === 'Rejected' && empty($reason)) {
            return respondErrorData('', 400, 'Reason is required when rejecting a bonus request.');
        }
        $bonusRequest = $this->bounsService->changeStatus(
            $id,
            $request->status,
            $request->reason,
            $request->payout_date
        );

        if (!$bonusRequest) {
            return respondError(($lang == 'en' ? 'Bonus request not found.' : 'طلب المكافأة غير موجود.'), 404);
        }

        return ResponseWithSuccessData($lang, $bonusRequest, 1);
    }
}
