<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchSafe;
use App\Models\Dish;
use App\Models\Employee;
use App\Models\EmployeeOpeningBalance;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\Shift;
use App\Models\Timetable;
use App\Services\ReportServices\OrdersReportsService;
use App\Services\HR_Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashierBalanceTransactionsReportsController extends Controller
{
    protected $timeTableService;
    protected $ordersReportsService;


    // Inject the service via constructor
    public function __construct(TimetableService $timeTableService, OrdersReportsService $ordersReportsService)
    {
        $this->timeTableService = $timeTableService;
        $this->ordersReportsService = $ordersReportsService;
    }

    public function index(Request $request)
    {
        $filter = $request->get('status', 'all');
        $from = $request->get('from');
        $to = $request->get('to');
        $shiftId = $request->get('shift_id');
        $cashierId = $request->get('cashier_id'); // Changed from cashier_name to cashier_id
        $branchId = $request->get('branch_id');

        $balances = EmployeeOpeningBalance::with(['employees', 'cashierMachines.branches']);

        // Base query for cashiers (employees with flag = 'cashier')
        $cashiersQuery = Employee::where('flag', 'cashier')->with('branch');

        // If branch is selected, filter cashiers by that branch
        if ($branchId) {
            $cashiersQuery->where('branch_id', $branchId);

            // Also filter balances by branch
            $balances->whereHas('cashierMachines.branches', function ($query) use ($branchId) {
                $query->where('id', $branchId);
            });
        }

        // Get cashiers for dropdown
        $cashiers = $cashiersQuery->get();

        // Filter by date range
        if ($from) {
            $balances->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $balances->whereDate('created_at', '<=', $to);
        }

        // Filter by shift
        if ($shiftId) {
            $shift = Timetable::find($shiftId);
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
            };
        }

        // Filter by selected cashier
        if ($cashierId) {
            $balances->where('employee_id', $cashierId);
        }

        $shifts = Timetable::get();
        $branches = Branch::all();

        return view('dashboard.reports.cashier_balance_transactions.list', [
            'balances' => $balances->get(),
            'shifts' => $shifts,
            'branches' => $branches,
            'cashiers' => $cashiers,
            'selectedShiftId' => $shiftId,
            'selectedBranchId' => $branchId,
            'selectedCashierId' => $cashierId,
        ]);
    }

    public function show($id)
    {

        $lang = app()->getLocale();
        $balance = EmployeeOpeningBalance::with(['employees', 'cashierMachines'])->findOrFail($id);
        $lastbalance = EmployeeOpeningBalance::latest()->where('cashier_machine_id', $balance->cashier_machine_id)->where('employee_schedule_id', $balance->employee_schedule_id)->where('type', 2)->first();
        //    dd($lastbalance);

        $today = Carbon::now()->format('Y-m-d');

        $shift = $this->timeTableService->getTimetableForDate($balance->employee_id, $today);

        if (!($shift['status'] == false)) {
            $shiftStart = $shift['data']['on_duty_time'];
            $endStart = $shift['data']['off_duty_time'];
        } else {
            $shiftStart = null;
            $endStart = null;
        }

        $start = $balance->date . ' ' . $shiftStart;
        $end = $balance->date . ' ' . $endStart;
        //        $order = Order::where('cashier_id', $balance->employee_id)
        //            ->orderByDesc('updated_at')
        //            ->first();
        //        dd($start, $end);
        //        dd($order);
        // $cashTotal = Order::where('cashier_id', $balance->employee_id)
        //     ->whereBetween('updated_at', [$start, $end])
        //     // ->where('status', 'completed')
        //     // ->where('print_status', 'done')
        //     ->whereHas('transaction', function ($query) {
        //         $query->where('payment_method', 'cash')->where('payment_status', 'paid');
        //     })
        //     ->sum('total_price_after_tax');
        $cashTotalwithoutrefund = Order::where('orders.cashier_id', $balance->employee_id)
            // ->where('orders.status', '!=', 'cancelled')
            ->join('order_transactions as t', 't.order_id', '=', 'orders.id')
            ->whereBetween('orders.updated_at', [$start, $end])
            ->where('t.payment_method', 'cash')
            ->where('t.payment_status', 'paid')
            ->sum('t.paid');
        $cashTotalrefund = Order::where('orders.cashier_id', $balance->employee_id)
            // ->where('orders.status', '!=', 'cancelled')
            ->join('order_transactions as t', 't.order_id', '=', 'orders.id')
            ->whereBetween('orders.updated_at', [$start, $end])
            ->where('t.payment_method', 'cash')
            ->where('t.payment_status', 'paid')
            ->where('t.is_refund', 1)
            ->sum('t.refund');
            
        $cashTotal = $cashTotalwithoutrefund - $cashTotalrefund;
        // dd($cashTotal);
        $visaTotal = Order::where('cashier_id', $balance->employee_id)
            ->whereBetween('updated_at', [$start, $end])
            // ->where('status', 'completed')
            // ->where('print_status', 'done')
            ->whereHas('transaction', function ($query) {
                $query->where('payment_method', 'credit_card')->where('payment_status', 'paid');
            })
            ->sum('total_price_after_tax');

        $totalOrders = Order::where('cashier_id', $balance->employee_id)
            ->whereBetween('updated_at', [$start, $end])
            //            ->where('branch_id', $balance->employees->branch->id)
            ->count();

        $completeOrders = Order::where('cashier_id', $balance->employee_id)
            ->whereBetween('updated_at', [$start, $end])
            ->where('status', 'completed')
            ->where('print_status', 'done')
            ->count();

        $pendingOrders = Order::where('cashier_id', $balance->employee_id)
            ->whereBetween('updated_at', [$start, $end])
            ->whereNot('status', 'cancelled')
            ->whereNot('status', 'completed')
            ->count();

        $cancelledOrders = Order::where('cashier_id', $balance->employee_id)
            ->whereBetween('updated_at', [$start, $end])
            ->where('status', 'cancelled')
            ->count();

        $orders = Order::where('cashier_id', $balance->employee_id)
            ->orderby('id', 'desc')
            ->whereBetween('updated_at', [$start, $end])
            //            ->where('branch_id', $balance->employees->branch->id)
            ->get();

        $allOrderDetails = [];

        foreach ($orders as $order) {
            $detailedOrder = $this->ordersReportsService->orderDetails($order->id);
            $transaction = $detailedOrder->orderTransactions->first();
            $tracking = $detailedOrder->tracking->last();

            $allOrderDetails[] = [
                'order' => $detailedOrder,
                'transaction' => $transaction,
                'tracking' => $tracking,
            ];
        }

        $branchSafe = BranchSafe::where('branch_id', $balance->cashierMachines->branch_id)
            ->where('cashier_machine_id', $balance->cashierMachines->id)
            ->first();

        // If branch safe exists, get its balances (if present)
        $branchSafeCash = $branchSafe ? $branchSafe->cash_amount : 0;
        $branchSafeVisa = $branchSafe ? $branchSafe->visa_amount : 0;

        $startshiftreal  = $balance->created_at;
        $endshiftreal  = $balance->updated_at;
        return view('dashboard.reports.cashier_balance_transactions.show', compact('branchSafeCash', 'endshiftreal', 'startshiftreal', 'endshiftreal', 'endStart', 'shiftStart', 'balance', 'lastbalance', 'cashTotal', 'visaTotal', 'allOrderDetails', 'totalOrders', 'completeOrders', 'cancelledOrders', 'pendingOrders'));
    }

    public function orderDetails($id)
    {
        $order = Order::with([
            'orderDetails.dish',
            'address',
            'orderAddons',
            'orderTransactions',
            'tracking',
            'client',
            'branch',
            'cancellationReasons' // Load the cancellation reasons
        ])->findOrFail($id);

        $order['transaction'] = OrderTransaction::first();
        //        $order['delivery'] = Order::first();
        return $order;
    }
}
