<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeRateController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $employee = auth('employee')->user();

        $emplyee_rates = EmployeeRate::with('employee.position')->orderBy('effective_date', 'desc');
        if ($request->filled('month')) {
            $monthNumber = (int) $request->month;

            if ($monthNumber >= 1 && $monthNumber <= 12) {
                $year = now()->year;

                $startOfMonth = Carbon::create($year, $monthNumber, 1)->startOfMonth()->format('Y-m-d');
                $endOfMonth   = Carbon::create($year, $monthNumber, 1)->endOfMonth()->format('Y-m-d');

                $emplyee_rates->whereBetween('effective_date', [$startOfMonth, $endOfMonth]);
            }
        }
        $child_employees = getSupervisedEmployees($employee->id);

        if ($employee->hasRole('HR_Manager')) {

            $myRates = (clone $emplyee_rates)->where('employee_id', $employee->id);
            $employeeRates = (clone $emplyee_rates)->where('employee_id', '!=', $employee->id);
        } elseif ($child_employees && $child_employees->count() > 0) {

            $childIds = $child_employees->pluck('id')->toArray();
            $myRates = (clone $emplyee_rates)->where('employee_id', $employee->id);
            $employeeRates = (clone $emplyee_rates)->whereIn('employee_id', $childIds);
        } else {

            $myRates = (clone $emplyee_rates)->where('employee_id', $employee->id);
            $employeeRates = EmployeeRate::query()->whereRaw('1 = 0');
        }

        $myRatesData = paginateOrGetAll($myRates, $request, []);
        $employeeRatesData = paginateOrGetAll($employeeRates, $request, []);

        return ResponseWithSuccessData($lang, [
            'my' => $myRatesData['data'] ?? null,
            'employees' => $employeeRatesData['data'] ?? null,
        ], 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $validator = Validator::make($request->all(), [
            'employee_id'        => 'required|integer|exists:employees,id',
            'rate'             => 'required|integer',
            'rate_comment'         => 'required|string'
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

        $employee = auth('employee')->user();
        $child_employees = getSupervisedEmployees($employee->id);
        $childIds = $child_employees->pluck('id')->toArray();
        if (
            $employee->id == $request->employee_id
        ) {
            return respondError(__(
                $lang == 'ar'
                    ? 'غير مصرح لك بتقيم نفسك.'
                    : 'You are not authorized to create ratting for yourself.'
            ), 403);
        }
        if (
            !$employee->hasRole('HR_Manager') &&
            $employee->id != $request->employee_id &&
            !in_array($request->employee_id, $childIds)
        ) {
            return respondError(__(
                $lang == 'ar'
                    ? 'غير مصرح لك بتقيم هذا الموظف.'
                    : 'You are not authorized to create ratting for this employee.'
            ), 403);
        }

        $validated = $validator->validated();

        $employeeRate = EmployeeRate::create([
            'employee_id' => $validated['employee_id'],
            'rate' => $validated['rate'],
            'rate_comment' => $validated['rate_comment'],
            'effective_date' => now(),
            'created_by' => authActionSave()['by'],
            'created_by_type' => authActionSave()['type'],
        ]);
        return ResponseWithSuccessData($lang, $employeeRate, 1);
    }
}
