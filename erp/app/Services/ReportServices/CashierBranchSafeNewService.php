<?php


namespace App\Services\ReportServices;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Employee;
use App\Models\BranchSafe;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\OrderTransaction;
use App\Models\EmployeeOpeningBalance;
use App\Services\ReturnInvoiceService;
use Illuminate\Support\Facades\Response;

class CashierBranchSafeNewService
{
    private $lang;
    protected $returnInvoiceService;
    public function __construct(ReturnInvoiceService $returnInvoiceService)
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
        $this->returnInvoiceService = $returnInvoiceService;
    }
    public function index()
    {
        $query = BranchSafe::with(['branch', 'employee', 'creator'])->get();
        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->where('branch_id', $branch_id);
            }
        }
        $balances = [];
        foreach ($query as $safe) {
            $branchSafe = json_decode($safe->balances_ids);
            foreach ($branchSafe as $balance) {
                $balances[] = EmployeeOpeningBalance::where('id', $balance)->first();
            }
        }
        $data = [
            'query' => $query,
            'balances' => $balances,
        ];
        return $data;
    }
    public function show($id, $lang = 'ar')
    {
        // Get branch safe with related data
        $query = BranchSafe::with([
            'branch',
            'branch.employees',
            'employee',
            'machine',
            'employee.employeeSchedules'
        ])->where('id', $id)->first();

        // Return error if branch safe doesn't exist
        if (!$query) {
            return [
                'status' => true,
                'message' => ($lang == 'en' ? 'branch safe not exist' : 'رقم الخزنه غير موجود'),
                'code' => 401
            ];
        }

        // Get previous balance (end of previous shift)
        $lastBalance = BranchSafe::where('cashier_machine_id', $query->cashier_machine_id)
            ->where('id', '<', $query->id)
            ->orderBy('id', 'desc')
            ->first();

        // Define shift time boundaries
        $shiftEnd = Carbon::parse($query->created_at);
        $shiftStart = $lastBalance
            ? Carbon::parse($lastBalance->created_at)
            : Carbon::parse($query->created_at)->startOfDay();

        $cashierMachineId = $query->cashier_machine_id;
        $nameColumndish = 'name_' . $lang;
        $nameColumnaddon = 'name_' . $lang;

        // ==================== ORDER COUNT CALCULATIONS ====================

        // Query for paid orders within shift timeframe
        $queryOrder = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->where('cashier_machine_id', $cashierMachineId);
            $q->where('status', '!=', 'cancelled');
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })->where('payment_status', 'paid');

        // Group transactions by order_id to identify fully refunded orders
        $transactions = (clone $queryOrder)
            ->select('order_id', 'is_refund', 'paid')
            ->get()
            ->groupBy('order_id');

        // Count orders excluding fully refunded ones
        $numOrders = $transactions->filter(function ($group) {
            $totalPaid = $group->where('is_refund', 0);
            $totalRefund = $group->where('is_refund', 1);
            return $totalPaid != $totalRefund;
        })->count();

        // Count cancelled orders within shift
        $refundOrders = Order::where('orders.status', '=', 'cancelled')
            ->where('cashier_machine_id', $cashierMachineId)
            ->whereBetween('updated_at', [$shiftStart, $shiftEnd])
            ->count();

        // Count distinct refunded orders
        $queryOrder2 = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->where('cashier_machine_id', $cashierMachineId);
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })
            ->where('is_refund', 1)
            ->distinct('order_id')
            ->count('order_id');

        // Total order count including cancelled orders
        $numOrders = $numOrders + $refundOrders;

        // ==================== FINANCIAL CALCULATIONS ====================

        // Calculate total before tax (excluding cancelled orders)
        $totalbefore = OrderTransaction::where('payment_status', 'paid')
            ->whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
                $q->where('cashier_machine_id', $cashierMachineId);
                $q->where('status', '!=', 'cancelled');
                $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
            })
            ->with('order')
            ->get()
            ->unique('order_id')
            ->sum(fn($t) => $t->order->total_price_befor_tax);

        // Calculate total after tax (excluding cancelled orders)
        $totalafter = OrderTransaction::where('payment_status', 'paid')
            ->whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
                $q->where('cashier_machine_id', $cashierMachineId);
                $q->where('status', '!=', 'cancelled');
                $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
            })
            ->with('order')
            ->get()
            ->unique('order_id')
            ->sum(fn($t) => $t->order->total_price_after_tax);

        // Calculate service fees from invoices
        $serviceswithoutrefund = OrderTransaction::where('payment_status', 'paid')
            ->whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
                $q->where('cashier_machine_id', $cashierMachineId);
                $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
            })
            ->with(['order', 'invoice'])
            ->whereHas('invoice', function ($q) {
                $q->where('invoice_type', 'invoice');
            })
            ->get()
            ->sum(fn($t) => $t->invoice->service_fees);
        $services = round($serviceswithoutrefund, 2);

        // Calculate tax value from orders
        $tax = OrderTransaction::where('payment_status', 'paid')
            ->whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
                $q->where('cashier_machine_id', $cashierMachineId);
                $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
            })
            ->with('order')
            ->get()
            ->unique('order_id')
            ->sum(fn($t) => $t->order->tax_value);

        // Calculate tax from refunded invoice details
        $taxwithrefund = OrderTransaction::where('payment_status', 'paid')
            ->whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
                $q->where('cashier_machine_id', $cashierMachineId);
                $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
            })
            ->with('invoice.invoiceDetails')
            ->get()
            ->sum(function ($transaction) {
                if ($transaction->is_refund == 1 && isset($transaction->invoice) && $transaction->invoice->relationLoaded('invoiceDetails')) {
                    return $transaction->invoice->invoiceDetails->sum('tax');
                }
                return 0;
            });

        // ==================== PAYMENT METHOD CALCULATIONS ====================

        // Visa payment calculations
        $baseQueryVisa = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->where('cashier_machine_id', $cashierMachineId);
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })
            ->where('payment_method', 'credit')
            ->where('payment_status', 'paid');

        $allVisaPaidTotal = (clone $baseQueryVisa)->sum('paid');
        $paidTotalVisa = (clone $baseQueryVisa)->where('is_refund', 0)->sum('paid');
        $refundTotalVisa = (clone $baseQueryVisa)->where('is_refund', 1)->sum('refund');
        $visaTotal = $paidTotalVisa - $refundTotalVisa;

        // Cash payment calculations
        $baseQueryCash = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->where('cashier_machine_id', $cashierMachineId);
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })
            ->where('payment_method', 'cash')
            ->where('payment_status', 'paid');

        $paidInvoices = Invoice::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->where('cashier_machine_id', $cashierMachineId);
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })
            ->where('invoice_type', 'invoice')
            ->where('status', 'paid');

        $allCashPaidTotal = (clone $baseQueryCash)->sum('paid');
        $subTotal = (clone $paidInvoices)->sum('total_before_coupon');
        $paidTotalCash = (clone $baseQueryCash)->where('is_refund', 0)->sum('paid');
        $refundTotalCash = (clone $baseQueryCash)->where('is_refund', 1)->sum('refund');
        $cashTotal = $paidTotalCash - $refundTotalCash;

        // ==================== COUPON CALCULATIONS ====================

        // Calculate service and tax for 100% coupon orders
        $couponTotalFixedServiceandtax = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->where('cashier_machine_id', $cashierMachineId)
                ->whereBetween('updated_at', [$shiftStart, $shiftEnd])
                ->whereColumn('coupon_value', '=', 'total_price_before_coupon');
        })
            ->where('payment_status', 'paid')
            ->with('order')
            ->get()
            ->unique('order_id');

        $serviceAndTaxSum = $couponTotalFixedServiceandtax->sum(function ($q) {
            $serviceCoupon100 = (($q->order->total_price_before_coupon * $q->order->service_percentage) / 100);
            if ($q->type == 'dine-in') {
                $taxcoupon100 = (($serviceCoupon100) * $q->order->tax_percentage) / 100;
            } else {
                $taxcoupon100 = ($q->order->total_price_before_coupon  * $q->order->tax_percentage) / 100;
            }
            return $serviceCoupon100 + $taxcoupon100;
        });

        // Calculate coupon values from invoice details
        $couponTotalFixedwithoutRefund = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->where('cashier_machine_id', $cashierMachineId);
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })
            ->where('payment_status', 'paid')
            ->with('invoice.invoiceDetails')
            ->get()
            ->unique('order_id');

        $couponTotalFixed = 0;
        foreach ($couponTotalFixedwithoutRefund as $transaction) {
            if ($transaction->invoice && $transaction->invoice->invoiceDetails) {
                foreach ($transaction->invoice->invoiceDetails as $detail) {
                    $couponTotalFixed += $detail->coupon_value;
                }
            }
        }
        $couponTotalFixed += $serviceAndTaxSum;

        // ==================== PRODUCT CATEGORY ANALYSIS ====================

        // Get all paid orders for product analysis
        $allOrders = OrderTransaction::with('order')
            ->whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
                $q->where('cashier_machine_id', $cashierMachineId);
                $q->where('status', '!=', 'cancelled');
                $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
            })
            ->where('payment_status', 'paid')
            ->get()
            ->unique('order_id')
            ->values();

        // Analyze dishes and addons by category
        $dishes = [];
        $addons = [];
        $totalDishPrice = 0;
        $totalAddonPrice = 0;

        foreach ($allOrders as $order) {
            $dishorders = OrderDetail::with('dish.dishCategory')
                ->where('order_id', $order->order_id)
                ->where('status', '!=', 'cancel')
                ->get();

            $addonorders = OrderAddon::with(['Addon.addons', 'Addon.category'])
                ->where('order_id', $order->order_id)
                ->where('status', '!=', 'cancel')
                ->get();

            // Process dish orders
            foreach ($dishorders as $dish) {
                $name = $dish->dish->dishCategory->$nameColumndish;
                $price = $dish->price_befor_tax * $dish->quantity;

                if (!isset($dishes[$name])) {
                    $dishes[$name] = [
                        'name' => $name,
                        'quantity' => 0,
                        'price' => 0,
                    ];
                }

                $dishes[$name]['quantity'] += $dish->quantity;
                $dishes[$name]['price'] += $price;
                $totalDishPrice += $price;
            }

            // Process addon orders
            foreach ($addonorders as $addon) {
                $name = $addon->Addon?->category->$nameColumnaddon;
                $price = $addon->price_befor_tax * $addon->quantity;

                if (!isset($addons[$name])) {
                    $addons[$name] = [
                        'name' => $name,
                        'quantity' => 0,
                        'price' => 0,
                    ];
                }

                $addons[$name]['quantity'] += $addon->quantity;
                $addons[$name]['price'] += $price;
                $totalAddonPrice += $price;
            }
        }

        $dishes = array_values($dishes);
        $addons = array_values($addons);
        $merged = array_values(array_merge($dishes, $addons));
        $totalAllPrice = $totalDishPrice + $totalAddonPrice;

        // ==================== BALANCE CALCULATIONS ====================

        // Get opening balance information
        $dateFirstBalance = EmployeeOpeningBalance::where('cashier_machine_id', $cashierMachineId)->first();
        $queryLastBalance = json_decode($query->balances_ids, true);
        $lastOpen = !empty($queryLastBalance) ? min($queryLastBalance) : null;
        $opencashlastBalance = EmployeeOpeningBalance::where('id', $lastOpen)->latest()->first();

        // Prepare final response data
        $data = [
            "open_balance" => round($opencashlastBalance?->open_cash ?? 0, 2),
            "start_shift" => $shiftStart->format('Y-m-d H:i:s'),
            "end_shift" => $shiftEnd->format('Y-m-d H:i:s'),
            "cashier_name" => $query->employee->first_name . ' ' . $query->employee->last_name,
            "branch" => $query->branch->$nameColumndish,
            "branchManeger" => $query->branch->employees?->first()->first_name . ' ' . $query->branch->employees?->first()->last_name ?? '',
            'orders_number' => $numOrders,
            'orders_number_refund' => $queryOrder2,
            'priceRefund' => round($refundTotalCash + $refundTotalVisa, 2),
            "totalbeforTax" => round($totalbefore ?? 0, 2),
            "totalafterTax" => round($totalafter ?? 0, 2),
            "actualAmount" => round($query->cash_amount ?? 0, 2),
            "Tax" => round(($taxwithrefund + $tax) ?? 0, 2),
            "totalSales" => $subTotal,
            "categories" => $merged ?? [],
            "cashTotalwithoutRefund" => round($allCashPaidTotal ?? 0, 2),
            "visaTotalwithoutRefund" => round($allVisaPaidTotal ?? 0, 2),
            "cashTotal" => round($cashTotal ?? 0, 2),
            "visaTotal" => round($visaTotal ?? 0, 2),
            "couponTotalFixed" => round($couponTotalFixed ?? 0, 2),
            "totalAllPrice" => round($totalAllPrice, 2),
            'shiftId' => (int)$id,
            "service" => round($services, 2),
            "deffictCash" => round((int)$query->deficit_cash, 2),
            "deffictvise" => round((int)$query->deficit_visa, 2),
            'currentCash' => round(($query->cash_amount + (-1 * $query->deficit_cash)), 2),
            'currentVisa' => round(($query->visa_amount + (-1 * $query->deficit_visa)), 2),
            'cashSentToSafe' => round((float)$query->cash_amount, 2),
            // 'visaSentToSafe' => round((float)$query->visa_amount, 2),
        ];

        return $data;
    }
}
