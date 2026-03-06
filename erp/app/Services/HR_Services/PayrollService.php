<?php

namespace App\Services\HR_Services;

use App\Models\AbsenceSetting;
use App\Models\PayrollSheet;
use App\Models\PayrollSheetItem;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\BonusRequest;
use App\Models\EmployeePayrollSetting;
use App\Models\LateDeductionSetting;
use App\Models\LeaveRequest;
use App\Models\PaymentFrequency;
use App\Models\Payroll;
use App\Models\Penalty;
use App\Models\SalaryAdvanceRequest;
use App\Models\TerminationOfService;
use App\Services\HR_Services\EmployeeAttendanceService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Faker\Provider\ar_EG\Payment;

class PayrollService
{
    // public function createPayrollSheet($period)
    // {
    //     // create sheet
    //     $sheet = PayrollSheet::create([
    //         'period' => $period,
    //         'status' => 'open',
    //     ]);

    //     // get all active employees
    //     $employees = Employee::where('status', 'active')->get();

    //     foreach ($employees as $employee) {
    //         PayrollSheetItem::create([
    //             'sheet_id'      => $sheet->id,
    //             'employee_id'   => $employee->id,
    //             'base_salary'   => $employee->salary,
    //             'attendance'    => 0,
    //             'deductions'    => 0,
    //             'bonuses'       => 0,
    //             'final_salary'  => 0,
    //         ]);
    //     }

    //     return $sheet;
    // }

    /**
     * Step 1: Generate payroll sheet and review attendance/salary
     */
    protected function isTodayPayrollDay($setting, $type): bool
    {
        $today = now();

        $paymentFrequency = $setting->paymentFrequency;

        if (!$paymentFrequency) {
            return false;
        }

        switch ($type) {
            case 'monthly':
                $scheduledDay = $paymentFrequency->payment_monthday ?? 30;
                return (int)$today->day === (int)$scheduledDay;

            case 'weekly':
                $scheduledWeekday = $paymentFrequency->payment_weekday ?? 5; // Friday by default
                return (int)$today->dayOfWeek === (int)$scheduledWeekday;

            case 'daily':
                return true; // runs every day

            default:
                return false;
        }
    }

    public function generatePayrollSheet($type)
    {
        DB::beginTransaction();

        try {
            $sheet = PayrollSheet::create([
                'start_date' => now(),
                'end_date'   => now(),
                'type'       => $type,
                'status'     => 'draft',
            ]);

            $settings = EmployeePayrollSetting::with(['employee', 'paymentFrequency'])
                ->whereHas('paymentFrequency', function ($q) use ($type) {
                    $q->where('name_en', $type);
                })
                ->where(function ($q) {
                    $today = now()->toDateString();
                    $q->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $today);
                })
                ->where('effective_from', '<=', now())
                ->get();

            foreach ($settings as $setting) {

                $employee = $setting->employee;

                [$startDate, $endDate] = $this->determinePayrollPeriod($employee, $setting);

                if (!$startDate || !$endDate) {
                    continue;
                }
                $attendanceLogs = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();
                $EmployeeAttendanceService = app(EmployeeAttendanceService::class);
                $filters = [
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                ];

                $metrics = $this->calculateAttendanceMetricsForRange($employee, $attendanceLogs, $startDate, $endDate);
                $shiftHours   = $metrics['scheduled_hours_need'];

                $absences       = $EmployeeAttendanceService->calculateAbsentDays($employee->id, $filters, $attendanceLogs);
                $overtimeHours  = $attendanceLogs->sum('overtime_hours');
                $lateMinutes    = $attendanceLogs->sum('late_minutes');
                $earlyDeparture = $attendanceLogs->sum('early_departure_minutes');
                // حساب الراتب حسب النوع
                $salaryResult = $this->calculateNetSalary(
                    $employee,
                    $setting,
                    $attendanceLogs,
                    $lateMinutes,
                    $absences,
                    $overtimeHours,
                    $shiftHours,
                    $startDate,
                    $endDate
                );

                $netSalary = $salaryResult['net_salary'];


                PayrollSheetItem::create([
                    'payroll_sheet_id' => $sheet->id,
                    'employee_id'      => $employee->id,
                    'base_salary'      => $setting->salary_value,
                    'overtime'         => $overtimeHours,
                    'deductions'       => $salaryResult['absence_deduction'] + $salaryResult['late_deduction'], // هيتحسب جوه calculateNetSalary
                    'bonuses'          => $salaryResult['bonus'], // نفس الكلام
                    'salary_advance'   => $salaryResult['salary_advance'],
                    'net_salary'       => $netSalary,
                    'calculation_details' => json_encode([
                        'effective_from'   => $startDate,
                        'effective_to'     => $endDate,
                        'type'             => $setting->paymentFrequency->name_en,
                        'overtime_hours'   => $overtimeHours,
                        'late_minutes'     => $lateMinutes,
                        'early_departure'  => $earlyDeparture,
                        'absence_days'     => $absences,
                    ]),
                    'status' => 'pending',
                ]);
            }

            DB::commit();
            return ['success' => true, 'data' => $sheet->load('items')];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    protected function calculateAttendanceMetricsForRange(Employee $employee, $attendanceLogs, $startDate, $endDate)
    {
        $totalShiftHours = 0;
        $totalOvertime = 0;
        $totalLate = 0;
        $totalEarly = 0;
        foreach ($attendanceLogs as $log) {
            $metrics = calculateAttendanceMetrics(
                $employee,
                $log->date,
                $log->clock_in,
                $log->clock_out
            );
            $totalShiftHours += $metrics['scheduled_hours_need'] ?? 0;
            $totalOvertime   += $metrics['overtime_minutes'] ?? 0;
            $totalLate       += $metrics['late_minutes'] ?? 0;
            $totalEarly      += $metrics['early_departure_minutes'] ?? 0;
        }

        return [
            'scheduled_hours_need' => $totalShiftHours,
            'overtime_minutes'     => $totalOvertime,
            'late_minutes'         => $totalLate,
            'early_departure'      => $totalEarly,
        ];
    }

    public function generatePayrollSheetForTermination($type, $employeeId)
    {
        DB::beginTransaction();

        try {
            // أنشئ شيت جديد
            $sheet = PayrollSheet::create([
                'start_date' => now(),
                'end_date'   => now(),
                'type'       => $type,
                'status'     => 'draft',
            ]);

            $settings = EmployeePayrollSetting::with(['employee', 'paymentFrequency'])
                ->whereHas('paymentFrequency', function ($q) use ($type) {
                    $q->where('name_en', $type);
                })
                ->where(function ($q) {
                    $today = now()->toDateString();
                    $q->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $today);
                })
                ->where('employee_id', $employeeId)
                ->where('effective_from', '<=', now())
                ->get();

            foreach ($settings as $setting) {

                $employee = $setting->employee;

                [$startDate, $endDate] = $this->determinePayrollPeriod($employee, $setting);

                $termination = TerminationOfService::where('employee_id', $employeeId)->first();

                if (!$startDate || !$termination->endDate) {
                    continue;
                }


                // --- نفس الحسابات بتاعتك ---
                $attendanceLogs = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $termination->endDate])
                    ->get();

                $EmployeeAttendanceService = app(EmployeeAttendanceService::class);
                $filters = [
                    'start_date' => $startDate,
                    'end_date'   => $termination->endDate,
                ];

                $metrics = $this->calculateAttendanceMetricsForRange($employee, $attendanceLogs, $startDate, $endDate);
                $shiftHours   = $metrics['scheduled_hours_need'];

                $absences       = $EmployeeAttendanceService->calculateAbsentDays($employee->id, $filters, $attendanceLogs);
                $overtimeHours  = $attendanceLogs->sum('overtime_hours');
                $lateMinutes    = $attendanceLogs->sum('late_minutes');
                $earlyDeparture = $attendanceLogs->sum('early_departure_minutes');
                // حساب الراتب حسب النوع
                $salaryResult = $this->calculateNetSalary(
                    $employee,
                    $setting,
                    $attendanceLogs,
                    $lateMinutes,
                    $absences,
                    $overtimeHours,
                    $shiftHours,
                    $startDate,
                    $termination->endDate,
                    'termination'
                );

                $netSalary = $salaryResult['net_salary'];


                PayrollSheetItem::create([
                    'payroll_sheet_id' => $sheet->id,
                    'employee_id'      => $employee->id,
                    'base_salary'      => $setting->salary_value,
                    'overtime'         => $overtimeHours,
                    'deductions'       => $salaryResult['absence_deduction'] + $salaryResult['late_deduction'], // هيتحسب جوه calculateNetSalary
                    'bonuses'          => $salaryResult['bonus'], // نفس الكلام
                    'salary_advance'   => $salaryResult['salary_advance'],
                    'net_salary'       => $netSalary,
                    'calculation_details' => json_encode([
                        'effective_from'   => $startDate,
                        'effective_to'     => $termination->endDate,
                        'type'             =>  $setting->paymentFrequency->name_en,
                        'overtime_hours'   => $overtimeHours,
                        'late_minutes'     => $lateMinutes,
                        'early_departure'  => $earlyDeparture,
                        'absence_days'     => $absences,
                    ]),
                    'status' => 'pending',
                ]);
            }

            DB::commit();
            return ['success' => true, 'data' => $sheet->load('items')];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    private function determinePayrollPeriod($employee, $setting)
    {
        $lastSheetItem = PayrollSheetItem::where('employee_id', $employee->id)
            ->latest('id')
            ->first();
        $calculation_details = json_decode($lastSheetItem->calculation_details);

        $startDate = $lastSheetItem ? Carbon::parse($calculation_details->effective_to)->addDay() : $setting->effective_from; //20/6/2025
        switch ($setting->paymentFrequency->name_en) {
            case 'monthly':
                $endOfMonth = Carbon::parse($startDate)->endOfMonth(); //20/7/2025
                $day = min($setting->payment_monthday ?? 30, $endOfMonth->day);
                $endDate = $endOfMonth->day($day);
                break;

            case 'weekly':
                $endDate = Carbon::parse($startDate)->next($setting->paymentFrequency->payment_weekday);

                break;

            case 'daily':
                $endDate = now();
                break;

            default:
                return [null, null];
        }

        return [$startDate, $endDate];
    }
    public function calculateNetSalary($employee, $setting, $attendanceLogs, $lateMinutes, $absences, $overtimeHours, $shiftHours, $startDate, $endDate, $type = null)
    {
        $payrollMonth = Carbon::parse($startDate);
        $month = $payrollMonth->month;
        $year = $payrollMonth->year;
        $netSalary = 0;
        // $daysWorked = $attendanceLogs->count();

        // --- Base salary calculation ---paymentFrequency
        switch ($setting->paymentFrequency->name_en) {
            case 'daily':
                $netSalary = $setting->salary_value;
                break;

            case 'weekly':
                // $weeks = $this->countPayrollWeeks($startDate, $endDate, $setting->payment_frequency->payment_weekday);
                $netSalary =  $setting->salary_value;
                break;

            case 'monthly':
                // $months = $this->countPayrollMonths($startDate, $endDate, $setting->payment_frequency->payment_monthday);
                $netSalary =  $setting->salary_value;
                break;
        }

        // --- Salary data helper ---
        $salaryData = (object)[
            'basic_salary' => $setting->salary_value,
            'total_salary' => $setting->salary_value, // can expand later for allowances
        ];

        // --- Deductions ---
        $lateDeduction = 0;
        if ($lateMinutes > 0) {
            $deductionSettings = LateDeductionSetting::first();
            if ($deductionSettings) {
                $result = $this->calculateLateDeduction($lateMinutes, $salaryData, $deductionSettings, $shiftHours);
                $lateDeduction = $result['deduction_amount'];
            }
        }

        $absenceDeduction = 0;
        if ($absences > 0) {
            $absenceSettings = AbsenceSetting::first();
            if ($absenceSettings) {
                $result = $this->calculateAbsenceDeduction($setting, $absenceSettings, $shiftHours);
                $absenceDeduction = $result['deduction_amount'] * $absences;
            }
        }
        // --- Salary advance ---
        if ($type == 'termination') {
            $salaryAdvance = SalaryAdvanceRequest::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where('deduction_month', '>=', $month)
                ->first()?->amount ?? 0;
        } else {
            $salaryAdvance = SalaryAdvanceRequest::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereMonth('deduction_month', $month)
                ->first()?->amount ?? 0;
        }


        // --- Bonus ---
        $bonusAmount = BonusRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereNull('payout_date')
            ->first()?->bonus_value ?? 0;

        // --- Overtime ---
        $dailyRate = match ($setting->paymentFrequency->name_en) {
            'daily'   => $setting->salary_value,
            'weekly'  => $setting->salary_value / 7,
            'monthly' => $setting->salary_value / 30,
            default   => 0,
        };
        $overtimePay = $overtimeHours * ($dailyRate / $shiftHours);

        // --- Penalties ---
        $calcPenalty = $this->calculatePenalty($employee->id, $startDate, $endDate);
        // --- Final net salary ---
        $netSalary = ($netSalary + $overtimePay + $bonusAmount)
            - ($lateDeduction + $absenceDeduction + $salaryAdvance + $calcPenalty['amount']);

        return [
            'net_salary'        => $netSalary,
            'base_salary'       => $setting->salary_value,
            'late_deduction'    => $lateDeduction,
            'absence_deduction' => $absenceDeduction,
            'salary_advance'    => $salaryAdvance,
            'bonus'             => $bonusAmount,
            'overtime_pay'      => $overtimePay,
            'penalty'           => $calcPenalty,
        ];
    }

    protected function countPayrollWeeks($startDate, $endDate, $paymentWeekday)
    {
        $count = 0;
        $date = $startDate->copy();
        while ($date->lte($endDate)) {
            if ($date->dayOfWeek === (int)$paymentWeekday) {
                $count++;
            }
            $date->addDay();
        }
        return $count;
    }

    protected function countPayrollMonths($startDate, $endDate, $paymentMonthday)
    {
        $count = 0;
        $date = $startDate->copy()->startOfMonth();

        while ($date->lte($endDate)) {
            $daysInMonth = $date->daysInMonth;
            $day = min((int)$paymentMonthday, $daysInMonth);

            $payDate = $date->copy()->day($day);
            if ($payDate->between($startDate, $endDate)) {
                $count++;
            }
            $date->addMonth();
        }

        return $count;
    }

    /**
     * Step 2: Finance approval
     */
    public function approveByFinance(PayrollSheet $sheet)
    {
        if (!in_array($sheet->status, ['awaiting_finance_approval', 'review'])) {
            return response()->json([
                "code" => 400,
                "status" => false,
                "message" => "Validation Error.",
                "data" => null,
                "errorData" => [
                    "Sheet not ready for finance approval. Current status: " . $sheet->status

                ],
                "validation_type" => true
            ], 400);
        }



        // Approve sheet
        $sheet->update(['status' => 'approved']);

        // Finalize payroll for all items
        foreach ($sheet->items as $item) {
            $item->update(['status' => 'approved']);
            $this->finalizePayroll($item);
        }

        return $sheet;
    }


    /**
     * Step 3: Return for correction
     */
    public function returnForCorrection(PayrollSheet $sheet, $comments, $itemsNeedingCorrection = [])
    {
        // Update the sheet status
        $sheet->update([
            'status' => 'returned',
        ]);

        // If no specific items provided, update ALL items for this sheet
        if (empty($itemsNeedingCorrection)) {
            PayrollSheetItem::where('payroll_sheet_id', $sheet->id)
                ->update([
                    'status' => 'needs_correction',
                    'comments' => $comments
                ]);
        } else {
            // Update only specific items if provided
            PayrollSheetItem::whereIn('id', $itemsNeedingCorrection)
                ->update([
                    'status' => 'needs_correction',
                    'comments' => $comments
                ]);
        }

        return $sheet->load('items');
    }

    /**
     * Step 4: Resubmit after correction
     */
    public function resubmit(PayrollSheet $sheet)
    {
        if ($sheet->status !== 'returned') {

            return response()->json([
                "code" => 400,
                "status" => false,
                "message" => "Validation Error.",
                "data" => null,
                "errorData" => [
                    "Sheet not in correction mode"

                ],
                "validation_type" => true
            ], 400);
        }

        $sheet->update(['status' => 'awaiting_finance']);
        return $sheet;
    }
    /**
     * Finalize payroll for an employee after finance approval
     */
    public function finalizePayroll(PayrollSheetItem $item)
    {
        if ($item->status !== 'approved') {
            throw new \Exception("Item must be approved before finalizing payroll");
        }
        return Payroll::create([
            'employee_id'          => $item->employee_id,
            'payroll_sheet_id'     => $item->payroll_sheet_id,
            'payroll_sheet_item_id' => $item->id,
            'final_salary'         => $item->net_salary,
            'salary_date'          => now()->toDateString(),
        ]);
    }
    private function calculateSalaryBySetting($setting, $attendanceLogs, $absences, $overtimeHours, $start, $end)
    {
        $daysWorked = $attendanceLogs->count();
        $netSalary = 0;

        switch ($setting->salary_type) {
            case 'daily':
                $netSalary = ($daysWorked * $setting->salary_value);
                break;

            case 'weekly':
                $weeks = ceil($start->diffInDays($end) / 7);
                $netSalary = ($weeks * $setting->salary_value);
                break;

            case 'monthly':
                $netSalary = $setting->salary_value;
                break;
        }

        // apply deductions + overtime
        switch ($setting->salary_type) {
            case 'daily':
                $dailyRate = $setting->salary_value; // already per day
                break;

            case 'weekly':
                $dailyRate = $setting->salary_value / 7; // weekly divided by 7 days
                break;

            case 'monthly':
                $dailyRate = $setting->salary_value / 30; // approx 30 days
                break;

            default:
                $dailyRate = 0;
        }

        $deductions = $absences * $dailyRate;
        $overtime   = $overtimeHours * ($dailyRate / 8);

        return $netSalary - $deductions + $overtime;
    }



    /**
     * HR edits a payroll sheet item (salary, attendance, deductions, bonuses, etc.)
     */
    public function updatePayrollItem($itemId, array $data)
    {
        $item = PayrollSheetItem::findOrFail($itemId);

        if ($item->status !== 'needs_correction' && $item->status !== 'reviewed') {
            return response()->json([
                "code" => 400,
                "status" => false,
                "message" => "Validation Error.",
                "data" => null,
                "errorData" => [
                    "Item cannot be edited in current status"

                ],
                "validation_type" => true
            ], 400);
        }

        // Update allowed fields
        $item->update([
            'base_salary'    => $data['base_salary'] ?? $item->base_salary,
            'overtime'       => $data['overtime'] ?? $item->overtime,
            'deductions'     => $data['deductions'] ?? $item->deductions,
            'bonuses'        => $data['bonuses'] ?? $item->bonuses,
            'net_salary'     => $this->recalculateNetSalary($item, $data),
            'status'         => 'reviewed', // after HR fixes it
        ]);

        return $item;
    }

    /**
     * Recalculate net salary
     */
    private function recalculateNetSalary(PayrollSheetItem $item, array $data)
    {
        $base      = $data['base_salary'] ?? $item->base_salary;
        $overtime  = $data['overtime'] ?? $item->overtime;
        $deduction = $data['deductions'] ?? $item->deductions;
        $bonus     = $data['bonuses'] ?? $item->bonuses;

        return ($base + $overtime + $bonus) - $deduction;
    }

    private function calculateLateDeduction($lateMinutes, $salaryData, $deductionSettings, $shiftHours = 8)
    {
        // Salary per minute (basic or total depending on settings)
        $rate = $this->salaryPerMinute($salaryData, $deductionSettings->deduct_from, $shiftHours);
        $deductMinutes = 0;

        switch ($deductionSettings->deduction_mode) {
            case 'exact':
                // Deduct exact late time
                $deductMinutes = $lateMinutes;
                break;

            case 'partial':
                // Round up to the nearest interval (e.g., 15, 30, 60 minutes)
                $interval = $deductionSettings->partial_interval ?? 15;
                $deductMinutes = ceil($lateMinutes / $interval) * $interval;
                break;

            case 'fixed':
                // If late minutes exceed threshold → deduct full day (8h = 480 mins)
                $threshold = $deductionSettings->fixed_threshold ?? 60;
                if ($lateMinutes > $threshold) {
                    $deductMinutes = $shiftHours * 60; // full day
                } else {
                    $deductMinutes = $lateMinutes; // else exact
                }
                break;

            case 'double':
                // Double the actual late time
                $deductMinutes = $lateMinutes * 2;
                break;

            default:
                $deductMinutes = 0;
        }
        // Final deduction value
        $deductionAmount = $deductMinutes * $rate;

        return [
            'deduct_minutes' => $deductMinutes,
            'deduction_amount' => $deductionAmount,
        ];
    }

    private function calculatePenalty($employeeId, $startDate, $endDate)
    {
        // $penalites = Penalty::with(['approval', 'reason'])
        // ->where('employee_id', $employeeId)
        // ->whereBetween('effective_date', [$startDate, $endDate])->get();
        $penalites = Penalty::with(['approval', 'reason'])
            ->where('employee_id', $employeeId)

            ->whereDate('effective_date', '>=', $startDate)
            ->whereDate('end_date', '<=', $endDate)
            ->get();
        $updateIn = [
            'bonuses' => 0,
            'allowance' => 0,
            'salaryDeduction' => 0,
        ];
        $amount = 0;
        foreach ($penalites as $penalty) {
            switch ($penalty) {
                case $penalty->reason->type == 'bonus_loss' && $penalty->approval->status == 'approved':
                    $updateIn['bonuses'] += $penalty->amount;
                    $amount += $penalty->amount;
                    break;

                case $penalty->reason->type == 'allowance_reduction':
                    $updateIn['allowance'] += $penalty->amount;
                    $amount += $penalty->amount;
                    break;

                case $penalty->reason->type == 'salary_deduction' && $penalty->approval->status == 'approved':
                    $updateIn['salaryDeduction'] +=  $penalty->amount;
                    $amount += $penalty->amount;
                    break;

                case $penalty->reason->type == 'fine' && $penalty->approval->status == 'approved':
                    $updateIn['salaryDeduction'] +=  $penalty->amount;
                    $amount += $penalty->amount;
                    break;

                default:
                    $deductMinutes = 0;
            }
        }
        return [
            'updateIn' => $updateIn,
            'amount' => $amount,
        ];
    }


    private function salaryPerMinute($salaryData, $deductFrom, $shiftHours = 8)
    {
        $baseSalary = $deductFrom === 'basic'
            ? $salaryData->basic_salary
            : $salaryData->total_salary;
        $workingMinutes = $shiftHours * 60; // 8 hours per day
        return $baseSalary / $workingMinutes;
    }
    // $deductionSettings = LateDeductionSetting::first(); // global settings
    // $salaryData = $employee->salary; // assuming relation
    // $lateMinutes = 22;

    // $deduction = $this->calculateLateDeduction($lateMinutes, $salaryData, $deductionSettings);

    // echo "Deduct Minutes: " . $deduction['deduct_minutes'];
    // echo "Deduct Amount: " . $deduction['deduction_amount'];
    // Employee Salary Breakdown:

    // Basic salary = 5000 EGP

    // Total salary (basic + allowances + bonuses) = 7000 EGP

    private function calculateAbsenceDeduction($salaryData, $absenceSettings, $shiftHours = 8)
    {
        $rate = $this->salaryPerMinute($salaryData, $absenceSettings->deduct_from);
        $workingMinutes = $shiftHours * 60; // 1 day = 8h = 480 min

        switch ($absenceSettings->penalty_mode) {
            case 'one_day':
                $deductMinutes = $workingMinutes;
                break;

            case 'two_day':
                $deductMinutes = $workingMinutes * 2;
                break;

            case 'manual':
                $deductMinutes = 0; // HR will adjust later
                break;

            default:
                $deductMinutes = $workingMinutes;
        }

        return [
            'deduct_minutes' => $deductMinutes,
            'deduction_amount' => $deductMinutes * $rate,
        ];
    }
    public function index(Request $request, $paginate = true)
    {
        $query = PayrollSheet::query();

        // Apply date filters if provided - find sheets within the date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->where('start_date', '>=', $request->start_date)
                    ->where('end_date', '<=', $request->end_date);
            });
        }

        if($request->has('status')) {
            $query->where('status', $request->status);
        }

        if($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        return $query;
    }
}
