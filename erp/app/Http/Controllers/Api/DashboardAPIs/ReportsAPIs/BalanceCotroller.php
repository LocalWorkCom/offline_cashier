<?php

namespace App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchSafe;
use App\Models\Employee;
use App\Models\EmployeeOpeningBalance;
use App\Models\Order;
use App\Models\Timetable;
use App\Services\HR_Services\TimetableService;
use App\Services\ReportServices\OrdersReportsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BalanceCotroller extends Controller
{
    protected $checkToken;
    protected $lang;
    protected $timeTableService;
    protected $ordersReportsService;

    public function __construct(TimetableService $timeTableService, OrdersReportsService $ordersReportsService)
    {
        $this->timeTableService = $timeTableService;
        $this->ordersReportsService = $ordersReportsService;
        $this->checkToken = true;
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }
    public function index(Request $request)
    {
        $query = BranchSafe::with(['branch', 'employee', 'creator']);
        if (auth('employee')->check() && auth('employee')->user()->hasRole('Branch_Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->where('branch_id', $branch_id);
            }
        }
        $response = paginateOrGetAll($query, $request, [], []);
        $responeOfData = [];
        foreach ($response['data'] as $data) {
            $responeOfData[] = [
                'id' => $data->id,
                'branch_name' => $data->branch ? $data->branch->name : null,
                'employee_name' => $data->employee ? $data->employee->first_name . ' ' . $data->employee->last_name : 'N/A',
                'cash_amount' => $data ? $data->cash_amount : 'N/A',
                'visa_amount' => $data ? $data->visa_amount : 'N/A',
                'created_at' => Carbon::parse($data->created_at)->format('Y-m-d H:i:s'),
                'creator_name' => $data->creator ? $data->creator->first_name . ' ' . $data->creator->last_name : 'N/A'
            ];
        }

        return ResponseWithSuccessData($this->lang, $responeOfData, 1);
    }
    public function show(Request $request, $id)
    {
        $query = BranchSafe::with(['branch', 'employee', 'creator', 'machine'])->where('id', $id);

        // Check if the record exists first
        if (!$query->exists()) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        if (auth('employee')->check() && auth('employee')->user()->hasRole('Branch_Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->where('branch_id', $branch_id);
            }
        }
        $response = paginateOrGetAll($query, $request, [], []);

        $balances = [];
        foreach ($response['data'] as $safe) {
            $branchSafe = json_decode($safe->balances_ids);
            foreach ($branchSafe as $balance) {
                // dd($balance);
                $balances[] = EmployeeOpeningBalance::where('id', $balance)->first();
            }
        }
        $branchSafe = $response['data'][0];

        $data = [
            'branchSafe' => [
                'id'            => $branchSafe->id,
                'cash_amount'   => $branchSafe->cash_amount,
                'visa_amount'   => $branchSafe->visa_amount,
                'deficit_cash'  => $branchSafe->deficit_cash,
                'deficit_visa'  => $branchSafe->deficit_visa,
                'reason'        => $branchSafe->reason,
                'created_at'    => $branchSafe->created_at,
                'branch_name'   => app()->getLocale() == 'en' ? $branchSafe->branch->name_en : $branchSafe->branch->name_ar,
                'branch_address' => app()->getLocale() == 'en' ? $branchSafe->branch->address_en : $branchSafe->branch->address_ar,
                'branch_phone'  => $branchSafe->branch->phone,
                'branch_email'  => $branchSafe->branch->email,
                'employee_name' => $branchSafe->employee->first_name . ' ' . $branchSafe->employee->last_name,
                'employee_code' => $branchSafe->employee->employee_code,
                'machine_name'  => $branchSafe->machine->name,
                'employee_email' => $branchSafe->employee->email,
                'employee_phone' => $branchSafe->employee->country_code . ' ' . $branchSafe->employee->phone_number,
            ],
            'balances' => collect($balances)->map(function ($balance) {
                return [
                    'id'                   => $balance->id,
                    'date'                 => $balance->date,
                    'time'                 => $balance->time,
                    'open_cash'            => $balance->open_cash,
                    'open_visa'            => $balance->open_visa,
                    'close_cash'           => $balance->close_cash,
                    'close_visa'           => $balance->close_visa,
                    'real_cash'            => $balance->real_cash,
                    'real_visa'            => $balance->real_visa,
                    'deficit_cash'         => $balance->deficit_cash,
                    'deficit_visa'         => $balance->deficit_visa,
                    'deficit_cash_close'   => $balance->deficit_cash_close,
                    'deficit_visa_close'   => $balance->deficit_visa_close,
                ];
            })->values(),
        ];

        return ResponseWithSuccessData($this->lang, $data, 1);
    }

    public function indexCashierBalance(Request $request)
    {
        $filter = $request->get('status', 'all');
        $from = $request->get('from');
        $to = $request->get('to');
        $shiftId = $request->get('shift_id');
        $cashierId = $request->get('cashier_id');
        $branchId = $request->get('branch_id');
        $balances = EmployeeOpeningBalance::with(['employees', 'cashierMachines']);

        // Apply cashier filter
        if (!empty($cashierId)) {
            $balances->where('employee_id', $cashierId);
        }

        // Apply branch filter
        if (!empty($branchId)) {
            $balances->whereHas('cashierMachines', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        }

        // Apply date filter
        if ($from) {
            $balances->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $balances->whereDate('created_at', '<=', $to);
        }

        // Apply shift filter
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
            }
        }

        $shifts = Timetable::get();
        $cashiers = Employee::where('flag', 'cashier')->get();
        $branches = Branch::get();

        // $balances = paginateOrGetAll($balances, $request, [], []);

        // // $balances = $balances->get();
        // $balances = $balances['data'];
        $data = [];

        foreach ($balances as $balance) {
            $currency = $balance?->cashierMachines?->branches?->country?->currency_symbol ?? '';

            $data[] = [
                'balance_id'              => $balance->id,
                'machine_name'    => app()->getLocale() == 'en'
                    ? $balance->cashierMachines->name_en
                    : $balance->cashierMachines->name_ar,
                'employee_name'   => $balance->employees && $balance->employees->first_name
                    ? ($balance->employees->first_name . " " . $balance->employees->last_name)
                    : null,
                'open_cash'       => $balance->open_cash . " " . $currency,
                'open_visa'       => $balance->open_visa . " " . $currency,
                'close_cash'      => $balance->close_cash . " " . $currency,
                'close_visa'      => $balance->close_visa . " " . $currency,
                'real_cash'       => $balance->real_cash . " " . $currency,
                'real_visa'       => $balance->real_visa . " " . $currency,
                'deficit_cash'    => $balance->deficit_cash . " " . $currency,
                'deficit_visa'    => $balance->deficit_visa . " " . $currency,
                'time'            => $balance->time,
                'date'            => $balance->date,
                'type'            => $balance->type == 1
                    ? __('cashier_balances_reports.Open')
                    : __('cashier_balances_reports.Close'),
            ];
        }


        // Use the helper function for pagination
            $result = paginateOrGetAll($balances, $request, null);
            return ResponseWithSuccessDataPaginated($this->lang, $result, 1);
    }

    public function showCashierBalance(Request $request, $id)
    {
        $balance = EmployeeOpeningBalance::with(['employees', 'cashierMachines'])->find($id);

        if (!$balance) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $lastbalance = EmployeeOpeningBalance::latest()
            ->where('cashier_machine_id', $balance->cashier_machine_id)
            ->where('employee_schedule_id', $balance->employee_schedule_id)
            ->where('type', 2)
            ->first();

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
        $cashTotal = Order::where('cashier_id', $balance->employee_id)
            ->whereBetween('updated_at', [$start, $end])
            // ->where('status', 'completed')
            // ->where('print_status', 'done')
            ->whereHas('transaction', function ($query) {
                $query->where('payment_method', 'cash')->where('payment_status', 'paid');
            })
            ->sum('total_price_after_tax');

        $visaTotal = Order::where('cashier_id', $balance->employee_id)
            ->whereBetween('updated_at', [$start, $end])
            // ->where('status', 'completed')
            // ->where('print_status', 'done')
            ->whereHas('transaction', function ($query) {
                $query->where('payment_method', 'credit_card')->where('payment_status', 'paid');
            })
            ->sum('total_price_after_tax');

        $startshiftreal = $balance->created_at;
        $endshiftreal = $balance->updated_at;

        $data = [
            'Machine' => app()->getLocale() == 'en' ? $balance->cashierMachines->name_en : $balance->cashierMachines->name_ar,
            'Cashier' => $balance->employees && $balance->employees->first_name ? ($balance->employees->first_name . ' ' . $balance->employees->last_name) : __('cashier_balances_reports.None'),
            'RealStartShift' => $shiftStart ?? __('cashier_balances_reports.None'),
            'RealEndShift' => $endStart ?? __('cashier_balances_reports.None'),
            'StartShift' => $startshiftreal ? \Carbon\Carbon::parse($startshiftreal)->format('H:i:s') : __('cashier_balances_reports.None'),
            'EndShift' => $balance->type == 2 ? \Carbon\Carbon::parse($endshiftreal)->format('H:i:s') : __('cashier_balances_reports.noclose'),
            'OpenCash' => ($balance?->open_cash ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? ''),
            'OpenVisa' => ($balance?->open_visa ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? ''),
            'CloseCash' => ($balance?->close_cash ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? ''),
            'CloseVisa' => ($balance?->close_visa ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? ''),
            'Type' => $balance->type == 1 ? __('cashier_balances_reports.Open') : __('cashier_balances_reports.Close'),
            'Branch' => app()->getLocale() == 'en' ? $balance->employees?->branch?->name_en : $balance->employees?->branch?->name_ar,
            'RealOpenCash' => ($lastbalance?->close_cash ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? ''),
            'RealOpenVisa' => ($lastbalance?->close_visa ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? ''),
            'RealCloseCash' => (($cashTotal ?? 0) + ($lastbalance?->close_cash ?? 0)) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? ''),
            'RealCloseVisa' => (($visaTotal ?? 0) + ($lastbalance?->close_visa ?? 0)) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? ''),
            'DeficitCash' => ($balance?->deficit_cash ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? ''),
            'DeficitVisa' => ($balance?->deficit_visa ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? ''),
            'DeficitCashClose' => ($balance?->deficit_cash_close ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? ''),
            'DeficitVisaClose' => ($balance?->deficit_visa_close ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? ''),
            'Date' => $balance->date,
            'Time' => $balance->time,
        ];

        return ResponseWithSuccessData($this->lang, $data, 1);
    }
    public function showCashierBalanceTransaction(Request $request, $id)
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
        $cashTotal = Order::where('cashier_id', $balance->employee_id)
            ->whereBetween('updated_at', [$start, $end])
            // ->where('status', 'completed')
            // ->where('print_status', 'done')
            ->whereHas('transaction', function ($query) {
                $query->where('payment_method', 'cash')->where('payment_status', 'paid');
            })
            ->sum('total_price_after_tax');
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
            $transaction   = $detailedOrder->orderTransactions->first();
            $tracking      = $detailedOrder->tracking->last();
            $cancellationReason = $detailedOrder->cancellationReason ?? null;

            $allOrderDetails[] = [
                'order_number'        => $detailedOrder->order_number,
                'total_price'         => $detailedOrder->total_price_after_tax ?? 0,
                'currency'            => $detailedOrder->Branch->country->currency_symbol ?? '',
                'order_status'        => strtolower($tracking->order_status ?? 'unknown'),
                'order_status_date'   => $tracking?->created_at?->format('d-m-Y H:i'),
                'payment_method'      => strtolower($transaction->payment_method ?? 'unknown'),
                'payment_status'      => strtolower($transaction->payment_status ?? 'unknown'),
                'has_discount_coupon' => ($detailedOrder->discount_id || $detailedOrder->coupon_id) ? true : false,
                'cancellation_reason' => $cancellationReason?->reason,
            ];
        }

        $currency = $balance?->cashierMachines?->branches?->country?->currency_symbol ?? '';

        $data = [
            'id'             => $balance->id,
            'machine_name'   => $lang == 'en' ? $balance->cashierMachines->name_en : $balance->cashierMachines->name_ar,
            'employee_name'  => $balance->employees ? ($balance->employees->first_name . ' ' . $balance->employees->last_name) : null,
            'open_cash'      => $balance->open_cash . ' ' . $currency,
            'open_visa'      => $balance->open_visa . ' ' . $currency,
            'close_cash'     => $balance->close_cash . ' ' . $currency,
            'close_visa'     => $balance->close_visa . ' ' . $currency,
            'real_cash'      => $balance->real_cash . ' ' . $currency,
            'real_visa'      => $balance->real_visa . ' ' . $currency,
            'deficit_cash'   => $balance->deficit_cash . ' ' . $currency,
            'deficit_visa'   => $balance->deficit_visa . ' ' . $currency,
            'type'           => $balance->type == 1 ? __('cashier_balances_reports.Open') : __('cashier_balances_reports.Close'),

            'cash_total'     => $cashTotal,
            'visa_total'     => $visaTotal,
            'total_orders'   => $totalOrders,
            'complete_orders' => $completeOrders,
            'pending_orders' => $pendingOrders,
            'cancelled_orders' => $cancelledOrders,

            'orders'         => $allOrderDetails,
        ];

        return ResponseWithSuccessData($lang, $data, 1);
    }
    public function allcashiers(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $cashiers = Employee::where('flag', 'cashier')->select('id', 'first_name', 'last_name')->get();

            return ResponseWithSuccessData($lang, $cashiers, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
