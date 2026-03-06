<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateViolationRequest;
use App\Http\Resources\AdvanceResource;
use App\Http\Resources\ReportViolatonResource;
use App\Http\Resources\ViolationResource;
use App\Models\Advance;
use App\Models\Employee;
use App\Models\EmployeeViolation;
use App\Models\Violation;
use App\Models\ViolationPenalty;
use App\Services\HR_Services\ViolationService;
use Carbon\Carbon;
use Google\Service\AdSenseHost\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ViolationController extends Controller
{
    private $lang;
    private $violationService;

    public function __construct(ViolationService $violationService)
    {
        $this->violationService = $violationService;
    }

public function index(Request $request)
{
    $lang = $request->header('lang', 'en');

    try {
        $response = $this->violationService->index($request);

        $myData = $response['my']->isNotEmpty()
            ? ViolationResource::collection($response['my'])    : null;
        $employeesData = $response['employees']->isNotEmpty()
            ? ViolationResource::collection($response['employees'])
            : null;

        return ResponseWithSuccessData(
            $lang,
            [
                'my' => $myData,
                'employees' => $employeesData,
            ],
            1
        );

    } catch (\Exception $e) {
        Log::error('Error fetching violations: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to fetch violations.',
            'error' => $e->getMessage()
        ], 500);
    }
}



    public function store(UpdateViolationRequest $request)
    {
        $lang = $request->header('lang', 'en');
        app()->setLocale($lang);
        try {
            DB::beginTransaction(); // Start transaction here

            // Call the service to store the violation
            $result = $this->violationService->store($request, $lang);

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                DB::rollBack(); // prevent commit if error returned
                return $result;
            }

            DB::commit(); // Commit only if everything passed
            return ResponseWithSuccessData($lang, $result, 1);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function update(UpdateViolationRequest $request, $id)
    {
        $lang = $request->header('lang', 'en');
        app()->setLocale($lang);


        try {
            DB::beginTransaction(); // Start transaction here

            // Call the service to store the violation
            $result = $this->violationService->update($request, $lang, $id);

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                DB::rollBack(); // prevent commit if error returned
                return $result;
            }

            DB::commit(); // Commit only if everything passed
            return ResponseWithSuccessData($lang, $result, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'المخالفة غير موجودة' : 'Violation not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching addon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function assignEmployee(Request $request)
    {
        try {
            $violationId = $request->input('violation_id');
            $employeeId = $request->input('employee_id');
            $lang = $request->header('lang', 'en');
            $validator = Validator::make($request->all(), [
                'violation_id' => 'required|exists:violations,id',
                'employee_id' => 'required|exists:employees,id',
            ]);

            if ($validator->fails()) {
                return respondError(__('validation.error'), 400,  $validator->errors());
            }
            $violation = Violation::with('violationPenalties')->findOrFail($violationId);
            $employee = Employee::findOrFail($employeeId);
            $maxRepeat = $violation->max_repeat;
            $withinPeriodDays = $violation->within_period;

            $startDate = Carbon::now()->subDays($withinPeriodDays)->startOfDay();

            $previousViolations = EmployeeViolation::whereHas('violationPenalty', function ($q) use ($violationId) {
                $q->where('violation_id', $violationId);
            })
                ->where('employee_id', $employeeId)
                ->whereDate('date', '>=', $startDate)
                ->count();
            $nextOrder = $previousViolations + 1;

            // Limit to max repeat penalty
            if ($nextOrder > $maxRepeat) {
                $nextOrder = $maxRepeat;
            }

            // Try to get the penalty for the next order
            $violationPenalty = $violation->violationPenalties()
                ->where('order_penalty', $nextOrder)
                ->first();

            // If not found, fallback to first penalty (order 1)
            if (!$violationPenalty) {
                $violationPenalty = $violation->violationPenalties()
                    ->orderBy('order_penalty', 'asc')
                    ->first();

                if (!$violationPenalty) {
                    return respondError(__('validation.No penalties defined for this violation.'), 400, __('validation.No penalties defined for this violation.'));
                }
            }

            $createdBy = auth('employee')->user()->id;
            $employee = Employee::findOrFail($employeeId);
            $employeeViolation = EmployeeViolation::create([
                'violation_penalty_id' => $violationPenalty->id,
                'employee_id' => $employeeId,
                'date' => Carbon::now(),
                'created_by' => $createdBy,
            ]);

            $notifyData = [
                'violation_id' => $violationPenalty->id,
                'title_ar' => "تم تعيين مخالفة جديدة",
                'type' => 'violation',
                'title_en' => "New Violation Assigned",
                'description_ar' => "تم تعيين مخالفة جديدة للموظف لك ، المخالفة: {$violationPenalty->violation->name}",
                'description_en' => "New violation assigned to you, Violation: {$violationPenalty->violation->name}",
            ];

            send_push_notification(
                $employee->device_token,
                $notifyData['description_ar'],
                $notifyData['description_en'],
                $notifyData['title_ar'],
                $notifyData['title_en'],
                "employee",
                $employee->id,
                $createdBy,
                $employeeViolation->id,
                $lang,
                7
            );

            return ResponseWithSuccessData($lang, $employeeViolation, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'المخالفة غير موجودة' : 'Violation not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching addon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function listForEmployee(Request $request)
    {
        //this will be for all employees can access to see there violations or HR and manager can see for all employees
        $lang = $request->header('lang', 'en');
        $authEmployee = auth('employee')->user();

        $canViewAll = $authEmployee->hasPermissionTo('View Violations_for_all_employees', 'employee');
        $employeeId = $canViewAll ? $request->employee_id : $authEmployee->id;

        $validator = Validator::make($request->all(), [
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return respondErrorData(__('validation.error.'), 200, $validator->errors());
        }

        $violations = EmployeeViolation::with([
            'violationPenalty.violation',
            'violationPenalty.penalty',
            'employee',
            'creator'
        ])
            ->when(!$canViewAll, function ($query) use ($employeeId) {
                // restrict to current employee if no permission
                $query->where('employee_id', $employeeId);
            })
            ->when($canViewAll && $employeeId, function ($query) use ($employeeId) {
                // if has permission AND employee_id provided -> filter that one
                $query->where('employee_id', $employeeId);
            })
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('employee_id')
            ->map(function ($employeeViolations, $employeeId) {
                $employee = $employeeViolations->first()->employee;

                return [
                    'employee_id'   => $employee->id,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'violations'    => $employeeViolations->map(function ($violation) {
                        return [
                            'violation_name' => $violation->violationPenalty->violation->name ?? null,
                            'penalty_name'   => $violation->violationPenalty->penalty->name ?? null,
                            'order_penalty'  => $violation->violationPenalty->order_penalty ?? null,
                            'assigned_at'    => $violation->created_at->format('Y-m-d H:i:s'),
                            'who_created'    => $violation->creator
                                ? $violation->creator->first_name . ' ' . $violation->creator->last_name
                                : null,
                        ];
                    })->values(),
                ];
            })
            ->values();

        return ResponseWithSuccessData($lang, $violations, 1);
    }

    public function frequentViolatorsReport(Request $request)
    {
        $lang = $request->header('lang', 'en');

        // Optional date range filter
        $from = $request->input('from_date')
            ? Carbon::parse($request->input('from_date'))->startOfDay()
            : null;
        $to = $request->input('to_date')
            ? Carbon::parse($request->input('to_date'))->endOfDay()
            : null;
        $query = EmployeeViolation::with('employee', 'violationPenalty.penalty', 'violationPenalty.violation')
            ->select('employee_id')
            ->selectRaw('COUNT(*) as total_violations')
            ->groupBy('employee_id')
            ->orderByDesc('total_violations');

        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
        }
        $result = paginateOrGetAll($query, $request);

        $resourceData = ReportViolatonResource::collection($result['data']);

        return ResponseWithSuccessDataPaginated(
            $lang,
            [
                'data' => $resourceData,
                'meta' => $result['meta']
            ],
            1
        );
    }
}
