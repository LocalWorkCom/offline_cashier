<?php

namespace App\Services\ReportServices;

use App\Models\Branch;
use App\Models\BranchSafe;
use App\Models\Employee;
use App\Models\EmployeeOpeningBalance;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\Timetable;
use App\Services\HR_Services\TimetableService;
use Carbon\Carbon;

class CashierBalancesReportService
{
    protected $timeTableService;

    public function __construct(TimetableService $timeTableService)
    {
        $this->timeTableService = $timeTableService;
    }

    /**
     * Get cashier balances with filters
     */
    public function index($filters)
    {
        $balances = EmployeeOpeningBalance::with(['employees', 'cashierMachines.branches']);

        // Apply cashier filter
        if (!empty($filters['cashier_id'])) {
            $balances->where('employee_id', $filters['cashier_id']);
        }

        // Apply branch filter
        if (!empty($filters['branch_id'])) {
            $balances->whereHas('cashierMachines', function ($query) use ($filters) {
                $query->where('branch_id', $filters['branch_id']);
            });
        }

        // Apply date filter
        if (!empty($filters['from'])) {
            $balances->whereDate('created_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $balances->whereDate('created_at', '<=', $filters['to']);
        }

        // Apply shift filter
        if (!empty($filters['shift_id'])) {
            $shift = Timetable::find($filters['shift_id']);
            if($shift){

                $shiftStart = $shift->on_duty_time;
                $shiftEnd = $shift->off_duty_time;

                if ($shiftStart && $shiftEnd) {
                    $balances->where(function ($query) use ($shiftStart, $shiftEnd) {
                        $query->where(function ($q) use ($shiftStart) {
                            $q->where('time', $shiftStart)
                            ->where('type', 1);
                        })->orWhere(function ($q) use ($shiftEnd) {
                            $q->where('time', $shiftEnd)
                            ->where('type', 2);
                        });
                    });
                }
            }else{
               $balances->whereRaw('1 = 0');
            }
        }

        return [
            // 'balances' => $balances->get(),
            'balances' => $balances,
            'shifts' => Timetable::all(),
            'cashiers' => Employee::where('flag', 'cashier')->get(),
            'branches' => Branch::all(),
        ];
    }

    /**
     * Get cashier balance details
     */
    public function show($id)
    {
        $balance = EmployeeOpeningBalance::with(['employees', 'cashierMachines'])->findOrFail($id);

        $lastbalance = EmployeeOpeningBalance::latest()
            ->where('cashier_machine_id', $balance->cashier_machine_id)
            ->where('employee_schedule_id', $balance->employee_schedule_id)
            ->where('type', 2)
            ->first();

        $today = Carbon::now()->format('Y-m-d');
        $shift = $this->timeTableService->getTimetableForDate($balance->employee_id, $today);

        $shiftStart = $shift['status'] ? $shift['data']['on_duty_time'] : null;
        $endStart   = $shift['status'] ? $shift['data']['off_duty_time'] : null;

        $start = $balance->created_at;
        $end   = $balance->date . ' ' . $endStart;

        // Cash totals
        $cashTotalwithoutrefund = Order::where('orders.cashier_id', $balance->employee_id)
            ->join('order_transactions as t', 't.order_id', '=', 'orders.id')
            ->whereBetween('orders.updated_at', [$start, $end])
            ->where('t.payment_method', 'cash')
            ->where('t.payment_status', 'paid')
            ->sum('t.paid');

        $cashTotalrefund = Order::where('orders.cashier_id', $balance->employee_id)
            ->join('order_transactions as t', 't.order_id', '=', 'orders.id')
            ->whereBetween('orders.updated_at', [$start, $end])
            ->where('t.payment_method', 'cash')
            ->where('t.payment_status', 'paid')
            ->where('t.is_refund', 1)
            ->sum('t.refund');

        // Visa totals
        $visaTotal = Order::where('orders.cashier_id', $balance->employee_id)
            ->join('order_transactions as t', 't.order_id', '=', 'orders.id')
            ->whereBetween('t.paid_at', [$start, $end])
            ->where('t.payment_method', 'credit')
            ->where('t.payment_status', 'paid')
            ->sum('t.paid');

        $openCash = $balance->open_cash;
        $openVisa = $balance->open_visa;
        $closeCash = $cashTotalwithoutrefund - $cashTotalrefund;
        $closeVisa = $visaTotal;

        $startshiftreal = $balance->created_at;
        $endshiftreal   = $balance->updated_at;

        $branchSafe = BranchSafe::where('branch_id', $balance->cashierMachines->branch_id)
            ->where('cashier_machine_id', $balance->cashierMachines->id)
            ->first();

        return [
            'balance' => $balance,
            'lastbalance' => $lastbalance,
            'shiftStart' => $shiftStart,
            'endStart' => $endStart,
            'startshiftreal' => $startshiftreal,
            'endshiftreal' => $endshiftreal,
            'openCash' => $openCash,
            'openVisa' => $openVisa,
            'closeCash' => $closeCash,
            'closeVisa' => $closeVisa,
            'branchSafeCash' => $branchSafe?->cash_amount ?? 0,
            'branchSafeVisa' => $branchSafe?->visa_amount ?? 0,
        ];
    }
}
