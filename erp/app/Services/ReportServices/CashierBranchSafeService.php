<?php


namespace App\Services\ReportServices;

use Carbon\Carbon;
use App\Models\Tip;
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

class CashierBranchSafeService
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
        $lang = app()->getLocale();
        // if (!CheckToken() && $checkToken) {
        //     return RespondWithBadRequest($lang, 5);
        // }

        $query = BranchSafe::with(['branch', 'employee', 'creator'])->get();
        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->where('branch_id', $branch_id);
            }
        }
        $balances = [];
        // $safes = $query->get();
        // dd($query);
        foreach ($query as $safe) {
            $branchSafe = json_decode($safe->balances_ids);
            foreach ($branchSafe as $balance) {
                // dd($balance);
                $balances[] = EmployeeOpeningBalance::where('id', $balance)->first();
            }
        }
        // dd($balances);
        $data = [
            'query' => $query,
            'balances' => $balances,
        ];
        return $data;
    }
     public function show($id, $lang = 'ar')
    {
        $query = BranchSafe::with(['branch', 'branch.employees', 'employee', 'machine', 'employee.employeeSchedules'])->where('id', $id)->first();

        if (!$query) {
            return [
                'status' => true,
                'message' => ($lang == 'en' ? 'branch safe not exist' : 'رقم الخزنه غير موجود'),
                'code' => 401
            ];
        }
        // Get previous balance - this should be the end of the previous shift
        $lastBalance = BranchSafe::where('cashier_machine_id', $query->cashier_machine_id)
            ->where('id', '<', $query->id)
            ->orderBy('id', 'desc')
            ->first();
        $lastOpenBalance = EmployeeOpeningBalance::where('cashier_machine_id', $query->cashier_machine_id)
            ->orderBy('id', 'desc')
            ->first();
        // FIX: Use proper time boundaries - current shift starts when previous ended, ends when current was created
        $shiftEnd = Carbon::parse($query->created_at);
        $shiftStart = $lastBalance ? Carbon::parse($lastBalance->created_at) : Carbon::parse($lastOpenBalance->created_at);

        $cashierMachineId = $query->cashier_machine_id;
        $employee = Employee::where('id', $query->employee->id)->first();
        // FIX: Use consistent time boundaries across all queries
        $queryOrder = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->where('cashier_machine_id', $cashierMachineId);
            $q->where('status', '!=', 'cancelled');
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })->where('payment_status', 'paid');
        // Get all paid transactions grouped by order_id
        $transactions = (clone $queryOrder)
            ->select('order_id', 'is_refund', 'paid')
            ->get()
            ->groupBy('order_id');

        // Calculate the number of orders after excluding full refunds
        $numOrders = $transactions->filter(function ($group) {
            $totalPaid = $group->where('is_refund', 0);
            $totalRefund = $group->where('is_refund', 1);
            // dd( $totalPaid, $totalRefund);

            // Include only if not fully refunded
            return $totalPaid != $totalRefund;
        })->count();

        // FIX: Use consistent time boundaries
        $refundOrders = Order::where('orders.status', '=', 'cancelled')
            ->where('cashier_machine_id', $cashierMachineId)
            ->whereBetween('updated_at', [$shiftStart, $shiftEnd])
            ->count();

        $queryOrder2 = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->where('cashier_machine_id', $cashierMachineId);
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })
            ->where('is_refund', 1)
            ->distinct('order_id')
            ->count('order_id');
        $numOrders = $numOrders + $refundOrders;

        // FIX: Use consistent time boundaries for all financial calculations
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

        $totalbeforewithrefund = OrderTransaction::where('payment_status', 'paid')
            ->whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
                $q->where('cashier_machine_id', $cashierMachineId);
                $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
            })
            ->with('invoice.invoiceDetails')
            ->get()
            // ->unique('order_id')
            ->sum(function ($transaction) {
                if ($transaction->is_refund == 1) {
                    return optional($transaction->invoice?->invoiceDetails)->sum('total_before_tax') ?? 0;
                }
                return 0;
            });
        // dd($totalbeforewithrefund);
        // $totalbefore = abs($totalbeforewithoutrefund - $totalbeforewithrefund);
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
        $totalafterwithrefund = OrderTransaction::where('payment_status', 'paid')
            ->whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
                $q->where('cashier_machine_id', $cashierMachineId);
                $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
            })
            ->with('invoice.invoiceDetails')
            ->get()
            // ->unique('order_id')
            ->sum(function ($transaction) {
                if ($transaction->is_refund == 1 && isset($transaction->invoice) && $transaction->invoice->relationLoaded('invoiceDetails')) {
                    return $transaction->invoice->invoiceDetails->sum('total_after_tax');
                }
                return 0;
            });
        // $totalafter = abs($totalafterwithoutrefund - $totalafterwithrefund);

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
            ->unique('order_id')
            ->sum(fn($t) => $t->invoice->service_fees);
        // $serviceswithrefund = OrderTransaction::where('payment_status', 'paid')
        //     ->whereHas('order', function ($q) use ($cashierMachineId,  $shiftStart, $shiftEnd) {
        //         $q->where('cashier_machine_id', $cashierMachineId);
        //         $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        //     })
        //     ->with('invoice.invoiceDetails')
        //     ->get()
        //     // ->unique('order_id')
        //     ->sum(function ($transaction) {
        //         if ($transaction->is_refund == 1 && isset($transaction->invoice) && $transaction->invoice->relationLoaded('invoiceDetails')) {
        //             return $transaction->invoice->invoiceDetails->sum('service_fees');
        //         }
        //         return 0;
        //     });
        $services = round($serviceswithoutrefund, 2);

        $tax = OrderTransaction::where('payment_status', 'paid')
            ->whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
                $q->where('cashier_machine_id', $cashierMachineId);
                $q->whereNot('status', 'cancelled');

                $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
            })
            ->with('order')
            ->get()
            ->unique('order_id')
            ->sum(fn($t) => $t->order->tax_value);
        $taxwithrefund = OrderTransaction::where('payment_status', 'paid')
            ->whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
                $q->where('cashier_machine_id', $cashierMachineId);
                $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
            })
            ->with('invoice.invoiceDetails')
            ->get()
            // ->unique('order_id')
            ->sum(function ($transaction) {
                if ($transaction->is_refund == 1 && isset($transaction->invoice) && $transaction->invoice->invoice_type == 'credit_note' && $transaction->invoice->relationLoaded('invoiceDetails')) {
                    return $transaction->invoice->invoiceDetails->sum('tax');
                }
                return 0;
            });

        // $tax = abs($taxwithoutrefund - $taxwithrefund);
        // $numOrders = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
        //     $q->where('cashier_machine_id', $cashierMachineId);
        //     if ($end != 0) {
        //         $q->whereBetween('updated_at', [$end, $start]);
        //     } else {
        //         $q->where('updated_at', '>', $currentBranchSafe);
        //     }
        //     $q->selectRaw('SUM(total_price_after_tax) as total');
        //     $q->value('total')
        //     // $q->where('status', 'completed')
        //     //   ->where('print_status', 'done');
        // })
        // // ->where('payment_method', 'cash')
        // ->where('payment_status', 'paid')
        // ->count();
        //->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')

        // $xx = 8; // $lastBalance1->created_at
        // $cashierMachineId = $query->employee->id; // Extract cashier ID first

        // $visaTotal = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
        //     $q->where('cashier_machine_id', $cashierMachineId);

        //     if ($end != 0) {
        //         $q->whereBetween('updated_at', [$end, $start]);
        //     } else {
        //         $q->where('updated_at', '>', $currentBranchSafe);
        //     }

        //     // $q->where('status', 'completed')
        //     //   ->where('print_status', 'done');
        // })
        //     ->where('payment_method', 'credit')
        //     ->where('payment_status', 'paid')
        //     // ->count();
        //     // ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
        //     ->selectRaw('SUM(paid) as total')
        //     ->value('total');
        $baseQuery = OrderTransaction::whereHas('invoice', function ($q) use ($employee, $shiftStart, $shiftEnd) {
            // $q->where('created_by', $employee->id);
            $q->where('invoice_type', 'invoice');
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })->whereHas('order', function ($q) use ($cashierMachineId, $shiftEnd, $shiftStart) {
            $q->where('cashier_machine_id', $cashierMachineId);
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })
            ->where('payment_method', 'credit')
            ->where('payment_status', 'paid');

        $allVisaPaidTotal = (clone $baseQuery)->sum('paid');
        $paidTotalVisa = (clone $baseQuery)->where('is_refund', 0)->sum('paid');
        $refundTotalVisa = (clone $baseQuery)->where('is_refund', 1)->sum('refund');
        $visaTotal = $paidTotalVisa - $refundTotalVisa;

        // $baseQueryCash = OrderTransaction::whereHas('invoice', function ($q) use ($cashierMachineId, $employee, $shiftStart, $shiftEnd) {
        //     // $q->where('cashier_machine_id', $cashierMachineId);
        //     $q->where('created_by', $employee->id);
        //     $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        // })
        //     ->where('payment_method', 'cash')
        //     ->where('payment_status', 'paid');
        $baseQueryCash = OrderTransaction::join('invoices', 'order_transactions.invoice_id', '=', 'invoices.id')
            ->whereBetween('invoices.updated_at', [$shiftStart, $shiftEnd])
            ->where('order_transactions.payment_method', 'cash')
            ->where('order_transactions.payment_status', 'paid')
            ->whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
                $q->where('cashier_machine_id', $cashierMachineId)
                    ->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
            })
            ->selectRaw("
        SUM(CASE WHEN invoices.invoice_type = 'invoice' THEN order_transactions.paid ELSE 0 END) as total_invoices,
        SUM(CASE WHEN invoices.invoice_type = 'credit_note' AND order_transactions.is_refund = 1 THEN order_transactions.refund ELSE 0 END) as total_refunds
    ")
            ->first();


        $paidInvoices = Invoice::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->where('cashier_machine_id', $cashierMachineId);
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })
            ->where('invoice_type', 'invoice')
            ->where('status', 'paid');

        $allCashPaidTotal = ($baseQueryCash->total_invoices ?? 0) - ($baseQueryCash->total_refunds ?? 0);
        // dd($allCashPaidTotal);
        $subTotal = (clone $paidInvoices)->sum('total_before_coupon');
        $paidTotal = $baseQueryCash->total_invoices ?? 0;
        $refundTotal = $baseQueryCash->total_refunds ?? 0;
        $cashTotal = $paidTotal - $refundTotal;

        // $cashTotal = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
        //     $q->where('cashier_machine_id', $cashierMachineId);

        //     if ($end != 0) {
        //         $q->whereBetween('updated_at', [$end, $start]);
        //     } else {
        //         $q->where('updated_at', '>', $currentBranchSafe);
        //     }

        //     // $q->where('status', 'completed')
        //     //   ->where('print_status', 'done');
        // })
        //     ->where('payment_method', 'cash')
        //     ->where('payment_status', 'paid')
        //     // ->count();
        //     // ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
        //     ->selectRaw('SUM(paid) as total')
        //     ->value('total');
        $couponTotalFixedServiceandtax = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->where('cashier_machine_id', $cashierMachineId)
                ->whereBetween('updated_at', [$shiftStart, $shiftEnd])
                ->whereColumn('coupon_value', '=', 'total_price_before_coupon'); // filter
        })
            ->where('payment_status', 'paid')
            ->with('order') // make sure we have order relation
            ->get()
            ->unique('order_id');
        // Now calculate service+tax sum
        $serviceAndTaxSum = $couponTotalFixedServiceandtax->sum(function ($transaction) {
            return $transaction->original_price - $transaction->order->total_price_before_coupon;
        });

        $couponTotalFixedwithoutRefund = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            // $q->whereNotNull('coupon_id')

            $q->where('cashier_machine_id', $cashierMachineId);
            // $q->where('status', '!=', 'cancelled');
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })
            ->where('payment_status', 'paid')
            // ->where('is_refund', 0)
            ->with('invoice.invoiceDetails')
            ->get()
            ->unique('order_id');

        $couponTotalFixed = 0;
        // foreach ($couponTotalFixedwithoutRefund as $transaction) {
        //     if ($transaction->invoice && $transaction->invoice->invoiceDetails) {
        //         foreach ($transaction->invoice->invoiceDetails as $detail) {
        //             $couponTotalFixed += $detail->coupon_value;
        //         }
        //     }
        // }
        $couponTotalFixed = $couponTotalFixedwithoutRefund
            ->flatMap(fn($transaction) => $transaction->invoice?->invoiceDetails ?? [])
            ->sum('coupon_value');
        // dd(  $couponTotalFixed);
        $couponTotalFixed += $serviceAndTaxSum;
        // $couponTotalFixedwithRefund = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
        //     // $q->whereNotNull('coupon_id')
        //     $q->where('cashier_machine_id', $cashierMachineId);
        //     $q->where('status', '!=', 'cancelled');

        //     $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        // })
        //     // ->where('payment_method', 'cash')
        //     ->where('payment_status', 'paid')
        //     // ->where('is_refund', 1)
        //     // ->whereHas('order.coupon', function ($q) {
        //     //     $q->where('type', 'fixed');
        //     // })
        //     ->with('invoice.invoiceDetails')
        //     ->get()
        //     ->unique('order_id');
        // foreach ($couponTotalFixedwithRefund as $transaction) {
        //                 // dd($transaction->invoice->invoiceDetails);

        //     foreach ($transaction->invoice->invoiceDetails as $detail) {
        //         // dd($detail->coupon_value);
        //         $couponTotalFixed += (int)$detail->coupon_value;
        //         // dd($$detail->coupon_value);
        //     }
        // }
        // dd($couponTotalFixed);

        // dd($couponTotalFixedwithRefund);
        // ->values()
        // ->sum(function ($transaction) {
        //     return $transaction->order->coupon->value ?? 0;
        // });
        // $couponTotalall = OrderTransaction::where('payment_status', 'paid')
        //     ->whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
        //         $q->where('cashier_machine_id', $cashierMachineId);
        //         if ($end != 0) {
        //             $q->whereBetween('updated_at', [$end, $start]);
        //         } else {
        //             $q->where('updated_at', '>', $currentBranchSafe);
        //         }
        //     })
        //     ->with('invoice.invoiceDetails') // eager load the order
        //     ->get();
        //     dd($couponTotalall);
        $couponTotalpercentage = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->whereNotNull('coupon_id')
                ->where('cashier_machine_id', $cashierMachineId);
            $q->where('status', '!=', 'cancelled');

            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })
            // ->where('payment_method', 'cash')
            ->where('payment_status', 'paid')
            ->whereHas('order.coupon', function ($q) {
                $q->where('type', 'percentage');
            })
            ->with('order.coupon')
            ->get()
            ->unique('order_id') // <- Now applied on the collection
            ->values()
            ->sum(function ($transaction) {
                return $transaction->order->coupon->value ?? 0;
            });
        // dd($couponTotalpercentage);

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



        $dishes = [];
        $addons = [];
        $totalDishPrice = 0;
        $totalAddonPrice = 0;
        $nameColumndish = 'name_' . $lang;
        $nameColumnaddon = 'name_' . $lang;
        $talabat_total = (object) [
            'talabat_visa' => 0,
            'talabat_cash' => 0,
            'talabat_count' => 0,
            'talabat_cash_count' => 0,
            'talabat_visa_count' => 0,
        ];

        foreach ($allOrders as $order) {
            $dishorders = OrderDetail::with('dish.dishCategory')->where('order_id', $order->order_id)->where('status', '!=', 'cancel')->get();
            $addonorders = OrderAddon::with(['Addon.addons', 'Addon.category'])->where('order_id', $order->order_id)->where('status', '!=', 'cancel')->get();

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

            $talabat = $this->talabat($order->order_id, $order->invoice_id);

            $talabat_total->talabat_visa += $talabat->talabat_visa;
            $talabat_total->talabat_cash += $talabat->talabat_cash;
            $talabat_total->talabat_count += $talabat->talabat_count;
            $talabat_total->talabat_cash_count += $talabat->talabat_cash_count;
            $talabat_total->talabat_visa_count += $talabat->talabat_visa_count;
        }

        $allOrdersunpaid = OrderTransaction::with('order')
            ->whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
                $q->where('cashier_machine_id', $cashierMachineId);
                $q->where('status', '!=', 'cancelled');
                $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
            })
            ->where('payment_status', 'unpaid')
            ->get()
            ->unique('order_id')
            ->values();
        foreach ($allOrdersunpaid as $order) {
            $talabat = $this->talabat($order->order_id, $order->invoice_id);

            $talabat_total->talabat_visa += $talabat->talabat_visa;
            $talabat_total->talabat_cash += $talabat->talabat_cash;
            $talabat_total->talabat_count += $talabat->talabat_count;
            $talabat_total->talabat_cash_count += $talabat->talabat_cash_count;
            $talabat_total->talabat_visa_count += $talabat->talabat_visa_count;
        }
        // return $talabat_total;

        $dishes = array_values($dishes);
        $addons = array_values($addons);
        $merged = array_values(array_merge($dishes, $addons));
        $totalAllPrice = $totalDishPrice + $totalAddonPrice;
        // hanan
        // حساب الإكراميات
        // الحصول على جميع المعاملات المدفوعة في الوردية
        $paidTransactions = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $shiftStart, $shiftEnd) {
            $q->where('cashier_machine_id', $cashierMachineId);
            $q->where('status', '!=', 'cancelled');
            $q->whereBetween('updated_at', [$shiftStart, $shiftEnd]);
        })
            ->where('payment_status', 'paid')
            ->where('is_refund', 0)
            ->get();
        // hanan
        // حساب الإكراميات الكاش
        $cashTransactions = $paidTransactions->where('payment_method', 'cash');
        $tipCashTotal = 0;
        foreach ($cashTransactions as $transaction) {
            $tip = Tip::where('invoice_id', $transaction->invoice_id)
                ->where('order_id', $transaction->order_id)
                ->first();
            if ($tip && $tip->tip_amount) {
                $tipCashTotal += $tip->tip_amount;
            }
        }

        // حساب الإكراميات الفيزا
        $visaTransactions = $paidTransactions->where('payment_method', 'credit');
        $tipVisaTotal = 0;
        foreach ($visaTransactions as $transaction) {
            $tip = Tip::where('invoice_id', $transaction->invoice_id)
                ->where('order_id', $transaction->order_id)
                ->first();
            if ($tip && $tip->tip_amount) {
                $tipVisaTotal += $tip->tip_amount;
            }
        }

        // مجموع الإكراميات
        $tipTotal = $tipCashTotal + $tipVisaTotal;

        //    hanan
        // dd($query->employee->employeeSchedules->first()->id);
        $data = [
            'cashier_machine_id' => $cashierMachineId,
            'employee_schedule_id' => $query->employee->employeeSchedules->first()->id
            // 'shift_start' => "00:00:00",
            // 'shift_end' => "23:59:00",
        ];
        $fakeRequest = new Request();
        $fakeRequest->replace($data);
        $test = $this->returnInvoiceService->getCurrentBalance($fakeRequest);
        // return $test;
        // dd($test->original);
        // dd($dishes, $addons, $totalAllPrice);
        $dateFirstBalance = EmployeeOpeningBalance::where('cashier_machine_id', $cashierMachineId)->first();
        $queryLastBalance = json_decode($query->balances_ids, true);
        $lastOpen = !empty($queryLastBalance) ? min($queryLastBalance) : null;
        $opencashlastBalance = EmployeeOpeningBalance::where('id', $lastOpen)->latest()->first();




        // FIX: Return proper time boundaries in the response
        $data = [
            'orders_number' => $numOrders,
            "totalSales" => $subTotal,
            "cashTotalwithoutRefund" => round($allCashPaidTotal ?? 0, 2),
            "visaTotalwithoutRefund" => round($allVisaPaidTotal ?? 0, 2),
            "service" => round($services, 2),
            "Tax" => round(($taxwithrefund + $tax) ?? 0, 2),
            "actualAmount" => round($query->cash_amount ?? 0, 2),
            "deffictCash" => round($query->deficit_cash, 2),
            'orders_number_refund' => $queryOrder2,
            'priceRefund' => round($refundTotal + $refundTotalVisa, 2),
            "couponTotalFixed" => round($couponTotalFixed ?? 0, 2),
            "cashTotal" => round($cashTotal ?? 0, 2),
            "visaTotal" => round($visaTotal ?? 0, 2),

            "open_balance" => round($opencashlastBalance?->open_cash ?? 0, 2),
            "start_shift" => $shiftStart->format('Y-m-d H:i:s'),
            "end_shift" => $shiftEnd->format('Y-m-d H:i:s'),
            "cashier_name" => $query->employee->first_name . ' ' . $query->employee->last_name,
            "branch" => $query->branch->$nameColumndish,
            "branchManeger" => $query->branch->employees?->first()->first_name . ' ' . $query->branch->employees?->first()->last_name ?? '',
            "totalbeforTax" => round($totalbefore ?? 0, 2),
            "totalafterTax" => round($totalafter ?? 0, 2),
            "categories" => $merged ?? [],


            "totalAllPrice" => round($totalAllPrice, 2),
            'shiftId' => (int) $id,
            "deffictvise" => round($query->deficit_visa, 2),
            'currentCash' => round($query->cash_amount + (-1 * $query->deficit_cash), 2),
            'currentVisa' => round($query->visa_amount + (-1 * $query->deficit_visa), 2),
            'cashSentToSafe' => round($query->cash_amount, 2),
            // 'visaSentToSafe' => round((float)$query->visa_amount, 2),
            'talabatcount' => $talabat_total->talabat_count,
            'talabatcash' => $talabat_total->talabat_cash,
            'talabatvisa' => $talabat_total->talabat_visa,
            'talabat_cash_count' => $talabat_total->talabat_cash_count,
            'talabat_visa_count' => $talabat_total->talabat_visa_count,
            // tips
            'tipTotal' => round($tipTotal ?? 0, 2),
            'tipCash' => round($tipCashTotal ?? 0, 2),
            'tipVisa' => round($tipVisaTotal ?? 0, 2),
        ];

        return $data;
    }

    public function talabat($order_id, $invoice_id)
    {
        $talabatcashcount = 0;
        $talabatvisacount = 0;
        $talabatcash = 0;
        $talabatvisa = 0;
        $talabatcount = 0;

        $invoice = Tip::with('menu_Integration')
            ->where('invoice_id', $invoice_id)
            ->where('order_id', $order_id)
            ->first();

        if ($invoice) {
            if (
                $invoice->menus_integration_id != null &&
                $invoice->menu_Integration &&
                $invoice->menu_Integration->name_en == 'talabat'
            ) {
                $talabatcount++;

                if ($invoice->payment_status_menu_integration == 'paid') {
                    $talabatcashcount++;
                    $talabatcash += $invoice->bill_amount;
                } else {
                    $talabatvisacount++;
                    $talabatvisa += $invoice->bill_amount;
                }
            }
        }

        return (object) [
            'talabat_visa' => $talabatvisa,
            'talabat_cash' => $talabatcash,
            'talabat_count' => $talabatcount,
            'talabat_cash_count' => $talabatcashcount,
            'talabat_visa_count' => $talabatvisacount,
        ];
    }


    // public function show($id, $lang = 'ar')
    // {
    //     //endshift is createdat now and start lastbalance createdat and is first open balance now
    //     // if (!CheckToken() && $checkToken) {
    //     //     return RespondWithBadRequest($lang, 5);
    //     // }

    //     $query = BranchSafe::with(['branch', 'branch.employees', 'employee', 'machine', 'employee.employeeSchedules'])->where('id', $id)->first();

    //     if (!$query) {
    //         return [
    //             'status' => true,
    //             'message' => ($lang == 'en' ? 'branch safe not exist' : 'رقم الخزنه غير موجود'),
    //             'code' => 401
    //         ];
    //     }

    //     // dd($query);
    //     $lastBalance = BranchSafe::where('cashier_machine_id', $query->cashier_machine_id)
    //         ->where('id', '<', $query->id)
    //         ->orderBy('id', 'desc')
    //         ->first();

    //     $currentBranchSafe = Carbon::parse($query->created_at)->format('Y-m-d');
    //     $lastBranchSafe = $lastBalance ? Carbon::parse($lastBalance?->created_at)->format('Y-m-d') : 0;


    //     // $start = $lastBranchSafe . ' ' . $query->employee->employeeSchedules->first()->start_date;
    //     // $end = $lastBranchSafe == 0 ? $lastBranchSafe . ' ' . $query->employee->employeeSchedules->first()->end_date : 0;
    //     $start = Carbon::parse($query->created_at)->format('Y-m-d H:i:s');
    //     $end = $lastBalance ? Carbon::parse($lastBalance?->created_at)->format('Y-m-d H:i:s') : 0;
    //     // dd($start);
    //     // $lastBalance_created_at = $lastBalance?->created_at ?? $currentBranchSafe;
    //     $cashierMachineId = $query->cashier_machine_id; // Extract cashier ID first

    //     $queryOrder = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //         $q->where('cashier_machine_id', $cashierMachineId);
    //         $q->where('status', '!=', 'cancelled');
    //         if ($end != 0) {
    //             $q->whereBetween('updated_at', [min($end, $start), max($end, $start)]);
    //         } else {
    //             $q->where('updated_at', '>=', $currentBranchSafe);
    //         }
    //     })->where('payment_status', 'paid');
    //     // Get all paid transactions grouped by order_id
    //     $transactions = (clone $queryOrder)
    //         ->select('order_id', 'is_refund', 'paid')
    //         ->get()
    //         ->groupBy('order_id');

    //     // $numOrders = $transactions->count();//update to calculate all orders with refunds
    //     // Calculate the number of orders after excluding full refunds
    //     $numOrders = $transactions->filter(function ($group) {
    //         $totalPaid = $group->where('is_refund', 0)->sum('paid');
    //         $totalRefund = $group->where('is_refund', 1)->sum('paid');
    //         // Include only if not fully refunded
    //         return $totalPaid != $totalRefund;
    //     })->count();
    //     $refundOrders = Order::where('orders.status', '=', 'cancelled')
    //         ->when($end != 0, function ($q) use ($start, $end) {
    //             $q->whereBetween('updated_at', [min($end, $start), max($end, $start)]);
    //         }, function ($q) use ($currentBranchSafe) {
    //             $q->where('updated_at', '>=', $currentBranchSafe);
    //         })
    //         ->count();
    //     $queryOrder2 = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId) {
    //         $q->where('cashier_machine_id', $cashierMachineId);
    //     })
    //         ->where('is_refund', ' 1')
    //         ->when($end != 0, function ($q) use ($start, $end) {
    //             $q->whereBetween('updated_at', [min($start, $end), max($start, $end)]);
    //         }, function ($q) use ($currentBranchSafe) {
    //             $q->where('updated_at', '>=', $currentBranchSafe);
    //         })
    //         ->count();
    //     $numOrders = $numOrders + $refundOrders;
    //     // $refundOrders = $transactions->filter(function ($group) {
    //     //     $totalPaid   = $group->where('is_refund', 0)->sum('paid');
    //     //     $totalRefund = $group->where('is_refund', 1)->sum('paid');
    //     //     return $totalRefund >= $totalPaid; // fully refunded
    //     // })->count();
    //     // $numOrders = Order::whereBetween('updated_at', ['2025-08-05 13:56:11', '2025-08-05 13:57:15'])->get();

    //     // dd($numOrders);

    //     // Get total of related orders
    //     $totalbefore = OrderTransaction::where('payment_status', 'paid')
    //         ->whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //             $q->where('cashier_machine_id', $cashierMachineId);
    //             $q->where('status', '!=', 'cancelled');
    //             if ($end != 0) {
    //                 $q->whereBetween('updated_at', [$end, $start]);
    //             } else {
    //                 $q->where('updated_at', '>', $currentBranchSafe);
    //             }
    //         })
    //         ->with('order') // eager load the order
    //         ->get()
    //         ->unique('order_id')
    //         ->sum(fn($t) => $t->order->total_price_befor_tax);

    //     $totalbeforewithrefund = OrderTransaction::where('payment_status', 'paid')
    //         ->whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //             $q->where('cashier_machine_id', $cashierMachineId);
    //             if ($end != 0) {
    //                 $q->whereBetween('updated_at', [min($end, $start), max($end, $start)]);
    //             } else {
    //                 $q->where('updated_at', '>', $currentBranchSafe);
    //             }
    //         })
    //         ->with('invoice.invoiceDetails')
    //         ->get()
    //         // ->unique('order_id')
    //         ->sum(function ($transaction) {
    //             if ($transaction->is_refund == 1) {
    //                 return optional($transaction->invoice?->invoiceDetails)->sum('total_before_tax') ?? 0;
    //             }
    //             return 0;
    //         });
    //     // dd($totalbeforewithrefund);
    //     // $totalbefore = abs($totalbeforewithoutrefund - $totalbeforewithrefund);


    //     $totalafter = OrderTransaction::where('payment_status', 'paid')
    //         ->whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //             $q->where('cashier_machine_id', $cashierMachineId);
    //             $q->where('status', '!=', 'cancelled');

    //             if ($end != 0) {
    //                 $q->whereBetween('updated_at', [$end, $start]);
    //             } else {
    //                 $q->where('updated_at', '>', $currentBranchSafe);
    //             }
    //         })
    //         ->with('order') // eager load the order
    //         ->get()
    //         ->unique('order_id')
    //         ->sum(fn($t) => $t->order->total_price_after_tax);

    //     $totalafterwithrefund = OrderTransaction::where('payment_status', 'paid')
    //         ->whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //             $q->where('cashier_machine_id', $cashierMachineId);
    //             if ($end != 0) {
    //                 $q->whereBetween('updated_at', [min($end, $start), max($end, $start)]);
    //             } else {
    //                 $q->where('updated_at', '>', $currentBranchSafe);
    //             }
    //         })
    //         ->with('invoice.invoiceDetails')
    //         ->get()
    //         // ->unique('order_id')
    //         ->sum(function ($transaction) {
    //             if ($transaction->is_refund == 1 && isset($transaction->invoice) && $transaction->invoice->relationLoaded('invoiceDetails')) {
    //                 return $transaction->invoice->invoiceDetails->sum('total_after_tax');
    //             }
    //             return 0;
    //         });
    //     // $totalafter = abs($totalafterwithoutrefund - $totalafterwithrefund);

    //     $serviceswithoutrefund = OrderTransaction::where('payment_status', 'paid')
    //         ->whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //             $q->where('cashier_machine_id', $cashierMachineId);
    //             // $q->where('status', '!=', 'cancelled');

    //             if ($end != 0) {
    //                 $q->whereBetween('updated_at', [$end, $start]);
    //             } else {
    //                 $q->where('updated_at', '>', $currentBranchSafe);
    //             }
    //         })
    //         ->with('order') // eager load the order
    //         ->get()
    //         ->unique('order_id')
    //         ->sum(fn($t) => $t->order->service_fees);

    //     $serviceswithrefund = OrderTransaction::where('payment_status', 'paid')
    //         ->whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //             $q->where('cashier_machine_id', $cashierMachineId);
    //             if ($end != 0) {
    //                 $q->whereBetween('updated_at', [min($end, $start), max($end, $start)]);
    //             } else {
    //                 $q->where('updated_at', '>', $currentBranchSafe);
    //             }
    //         })
    //         ->with('invoice.invoiceDetails')
    //         ->get()
    //         // ->unique('order_id')
    //         ->sum(function ($transaction) {
    //             if ($transaction->is_refund == 1 && isset($transaction->invoice) && $transaction->invoice->relationLoaded('invoiceDetails')) {
    //                 return $transaction->invoice->invoiceDetails->sum('service_fees');
    //             }
    //             return 0;
    //         });
    //     $services = round($serviceswithoutrefund, 2);

    //     $tax = OrderTransaction::where('payment_status', 'paid')
    //         ->whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //             $q->where('cashier_machine_id', $cashierMachineId);
    //             //$q->where('status', '!=', 'cancelled');

    //             if ($end != 0) {
    //                 $q->whereBetween('updated_at', [$end, $start]);
    //             } else {
    //                 $q->where('updated_at', '>', $currentBranchSafe);
    //             }
    //         })
    //         ->with('order') // eager load the order
    //         ->get()
    //         ->unique('order_id')
    //         ->sum(fn($t) => $t->order->tax_value);

    //     $taxwithrefund = OrderTransaction::where('payment_status', 'paid')
    //         ->whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //             $q->where('cashier_machine_id', $cashierMachineId);
    //             if ($end != 0) {
    //                 $q->whereBetween('updated_at', [min($end, $start), max($end, $start)]);
    //             } else {
    //                 $q->where('updated_at', '>', $currentBranchSafe);
    //             }
    //         })
    //         ->with('invoice.invoiceDetails')
    //         ->get()
    //         // ->unique('order_id')
    //         ->sum(function ($transaction) {
    //             if ($transaction->is_refund == 1 && isset($transaction->invoice) && $transaction->invoice->relationLoaded('invoiceDetails')) {
    //                 return $transaction->invoice->invoiceDetails->sum('tax');
    //             }
    //             return 0;
    //         });
    //     // $tax = abs($taxwithoutrefund - $taxwithrefund);
    //     // $numOrders = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //     //     $q->where('cashier_machine_id', $cashierMachineId);
    //     //     if ($end != 0) {
    //     //         $q->whereBetween('updated_at', [$end, $start]);
    //     //     } else {
    //     //         $q->where('updated_at', '>', $currentBranchSafe);
    //     //     }
    //     //     $q->selectRaw('SUM(total_price_after_tax) as total');
    //     //     $q->value('total')
    //     //     // $q->where('status', 'completed')
    //     //     //   ->where('print_status', 'done');
    //     // })
    //     // // ->where('payment_method', 'cash')
    //     // ->where('payment_status', 'paid')
    //     // ->count();
    //     //->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')

    //     // $xx = 8; // $lastBalance1->created_at
    //     // $cashierMachineId = $query->employee->id; // Extract cashier ID first

    //     // $visaTotal = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //     //     $q->where('cashier_machine_id', $cashierMachineId);

    //     //     if ($end != 0) {
    //     //         $q->whereBetween('updated_at', [$end, $start]);
    //     //     } else {
    //     //         $q->where('updated_at', '>', $currentBranchSafe);
    //     //     }

    //     //     // $q->where('status', 'completed')
    //     //     //   ->where('print_status', 'done');
    //     // })
    //     //     ->where('payment_method', 'credit')
    //     //     ->where('payment_status', 'paid')
    //     //     // ->count();
    //     //     // ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
    //     //     ->selectRaw('SUM(paid) as total')
    //     //     ->value('total');
    //     $baseQuery = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //         $q->where('cashier_machine_id', $cashierMachineId);
    //         // $q->where('status', '!=', 'cancelled');

    //         if ($end != 0) {
    //             $q->whereBetween('updated_at', [min($end, $start), max($end, $start)]);
    //         } else {
    //             $q->where('updated_at', '>', $currentBranchSafe);
    //         }
    //     })
    //         ->where('payment_method', 'credit')
    //         ->where('payment_status', 'paid');

    //     $allVisaPaidTotal = (clone $baseQuery)
    //         ->sum('paid');
    //     // paid transactions
    //     $paidTotalVisa = (clone $baseQuery)
    //         ->where('is_refund', 0)
    //         ->sum('paid');

    //     // refund transactions
    //     $refundTotalVisa = (clone $baseQuery)
    //         ->where('is_refund', 1)
    //         ->sum('refund');

    //     // final total
    //     $visaTotal = $paidTotalVisa - $refundTotalVisa;
    //     $baseQueryCash = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //         $q->where('cashier_machine_id', $cashierMachineId);
    //         // $q->where('status', '!=', 'cancelled');

    //         if ($end != 0) {
    //             $q->whereBetween('updated_at', [min($end, $start), max($end, $start)]);
    //         } else {
    //             $q->where('updated_at', '>', $currentBranchSafe);
    //         }
    //     })
    //         ->where('payment_method', 'cash')
    //         ->where('payment_status', 'paid');

    //     $allCashPaidTotal = (clone $baseQueryCash)
    //         ->sum('paid');
    //     $allOriginalPriceTotal = (clone $baseQueryCash)
    //         ->sum('original_price');
    //     $allPaidTotal = $allCashPaidTotal + $allVisaPaidTotal;
    //     $paidTotal = (clone $baseQueryCash)
    //         ->where('is_refund', 0)
    //         ->sum('paid');

    //     $refundTotal = (clone $baseQueryCash)
    //         ->where('is_refund', 1)
    //         ->sum('refund');

    //     $cashTotal = $paidTotal - $refundTotal;

    //     // $cashTotal = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //     //     $q->where('cashier_machine_id', $cashierMachineId);

    //     //     if ($end != 0) {
    //     //         $q->whereBetween('updated_at', [$end, $start]);
    //     //     } else {
    //     //         $q->where('updated_at', '>', $currentBranchSafe);
    //     //     }

    //     //     // $q->where('status', 'completed')
    //     //     //   ->where('print_status', 'done');
    //     // })
    //     //     ->where('payment_method', 'cash')
    //     //     ->where('payment_status', 'paid')
    //     //     // ->count();
    //     //     // ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
    //     //     ->selectRaw('SUM(paid) as total')
    //     //     ->value('total');
    //     $couponTotalFixedwithoutRefund = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //         // $q->whereNotNull('coupon_id')
    //         $q->where('cashier_machine_id', $cashierMachineId);
    //         $q->where('status', '!=', 'cancelled');


    //         if ($end != 0) {
    //             $q->whereBetween('updated_at', [$end, $start]);
    //         } else {
    //             $q->where('updated_at', '>', $currentBranchSafe);
    //         }
    //     })
    //         // ->where('payment_method', 'cash')
    //         ->where('payment_status', 'paid')
    //         ->where('is_refund', 0)
    //         // ->whereHas('order.coupon', function ($q) {
    //         //     $q->where('type', 'fixed');
    //         // })
    //         ->with('invoice.invoiceDetails')
    //         ->get()
    //         ->unique('order_id'); // <- Now applied on the collection
    //     $couponTotalFixed = 0;
    //     foreach ($couponTotalFixedwithoutRefund as $transaction) {
    //         // Add null check for invoice and invoiceDetails
    //         if ($transaction->invoice && $transaction->invoice->invoiceDetails) {
    //             foreach ($transaction->invoice->invoiceDetails as $detail) {
    //                 $couponTotalFixed += $detail->coupon_value;
    //             }
    //         }
    //     }

    //     $couponTotalFixedwithRefund = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //         // $q->whereNotNull('coupon_id')
    //         $q->where('cashier_machine_id', $cashierMachineId);
    //         $q->where('status', '!=', 'cancelled');


    //         if ($end != 0) {
    //             $q->whereBetween('updated_at', [$end, $start]);
    //         } else {
    //             $q->where('updated_at', '>', $currentBranchSafe);
    //         }
    //     })
    //         // ->where('payment_method', 'cash')
    //         ->where('payment_status', 'paid')
    //         ->where('is_refund', 1)
    //         // ->whereHas('order.coupon', function ($q) {
    //         //     $q->where('type', 'fixed');
    //         // })
    //         ->with('invoice.invoiceDetails')
    //         ->get()
    //         ->unique('order_id');
    //     foreach ($couponTotalFixedwithRefund as $transaction) {
    //         foreach ($transaction->invoice->invoiceDetails as $detail) {
    //             // dd($detail->coupon_value);
    //             $couponTotalFixed -= (int)$detail->coupon_value;
    //             // dd($$detail->coupon_value);
    //         }
    //     }
    //     // dd($couponTotalFixed);
    //     // ->values()
    //     // ->sum(function ($transaction) {
    //     //     return $transaction->order->coupon->value ?? 0;
    //     // });
    //     // $couponTotalall = OrderTransaction::where('payment_status', 'paid')
    //     //     ->whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //     //         $q->where('cashier_machine_id', $cashierMachineId);
    //     //         if ($end != 0) {
    //     //             $q->whereBetween('updated_at', [$end, $start]);
    //     //         } else {
    //     //             $q->where('updated_at', '>', $currentBranchSafe);
    //     //         }
    //     //     })
    //     //     ->with('invoice.invoiceDetails') // eager load the order
    //     //     ->get();
    //     //     dd($couponTotalall);
    //     $couponTotalpercentage = OrderTransaction::whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //         $q->whereNotNull('coupon_id')
    //             ->where('cashier_machine_id', $cashierMachineId);
    //         $q->where('status', '!=', 'cancelled');


    //         if ($end != 0) {
    //             $q->whereBetween('updated_at', [$end, $start]);
    //         } else {
    //             $q->where('updated_at', '>', $currentBranchSafe);
    //         }
    //     })
    //         // ->where('payment_method', 'cash')
    //         ->where('payment_status', 'paid')
    //         ->whereHas('order.coupon', function ($q) {
    //             $q->where('type', 'percentage');
    //         })
    //         ->with('order.coupon')
    //         ->get()
    //         ->unique('order_id') // <- Now applied on the collection
    //         ->values()
    //         ->sum(function ($transaction) {
    //             return $transaction->order->coupon->value ?? 0;
    //         });
    //     // dd($couponTotalpercentage);

    //     $allOrders = OrderTransaction::with('order')
    //         ->whereHas('order', function ($q) use ($cashierMachineId, $start, $end, $currentBranchSafe) {
    //             $q->where('cashier_machine_id', $cashierMachineId);
    //             $q->where('status', '!=', 'cancelled');


    //             if ($end != 0) {
    //                 $q->whereBetween('updated_at', [$end, $start]);
    //             } else {
    //                 $q->where('updated_at', '>', $currentBranchSafe);
    //             }
    //         })
    //         ->where('payment_status', 'paid')
    //         ->get()
    //         ->unique('order_id') // <- Now applied on the collection
    //         ->values();
    //     // dd($allOrders->first());
    //     $dishes = [];
    //     $addons = [];
    //     $totalDishPrice = 0;
    //     $totalAddonPrice = 0;
    //     $nameColumndish = 'name_' . $lang;
    //     $nameColumnaddon = 'name_' . $lang;
    //     // dd($allOrders->first());
    //     $data = [];
    //     foreach ($allOrders as $order) {
    //         $dishorders = OrderDetail::with('dish.dishCategory')->where('order_id', $order->order_id)->where('status', '!=', 'cancel')->get();

    //         $addonorders = OrderAddon::with(['Addon.addons', 'Addon.category'])->where('order_id', $order->order_id)->where('status', '!=', 'cancel')->get();
    //         // dd($dishorders);
    //         foreach ($dishorders as $dish) {
    //             $name = $dish->dish->dishCategory->$nameColumndish;
    //             $price = $dish->price_befor_tax * $dish->quantity;
    //             // $data[] = [
    //             //     'dish' => $dish->dish->dishCategory->$nameColumndish,
    //             //     'qy' => $dish->quantity,
    //             //     'ord' => $order->order_id,
    //             //     'id' => $dish->dish->id
    //             // ];
    //             if (!isset($dishes[$name])) {
    //                 $dishes[$name] = [
    //                     'name' => $name,
    //                     'quantity' => 0,
    //                     'price' => 0,
    //                 ];
    //             }

    //             $dishes[$name]['quantity'] += $dish->quantity;
    //             $dishes[$name]['price'] += $price;
    //             $totalDishPrice += $price;
    //         }
    //         foreach ($addonorders as $addon) {
    //             $name = $addon->Addon?->category->$nameColumnaddon;
    //             $price = $addon->price_befor_tax * $addon->quantity;

    //             if (!isset($addons[$name])) {
    //                 $addons[$name] = [
    //                     'name' => $name,
    //                     'quantity' => 0,
    //                     'price' => 0,
    //                 ];
    //             }

    //             $addons[$name]['quantity'] += $addon->quantity;
    //             $addons[$name]['price'] += $price;
    //             $totalAddonPrice += $price;
    //         }
    //     }
    //     // dd($data);
    //     // Optional: convert associative arrays to indexed arrays for output
    //     $dishes = array_values($dishes);
    //     $addons = array_values($addons);
    //     $merged = array_values(array_merge($dishes, $addons));
    //     // Total combined price
    //     $totalAllPrice = $totalDishPrice + $totalAddonPrice;
    //     // dd($query->employee->employeeSchedules->first()->id);
    //     $data = [
    //         'cashier_machine_id' => $cashierMachineId,
    //         'employee_schedule_id' => $query->employee->employeeSchedules->first()->id
    //         // 'shift_start' => "00:00:00",
    //         // 'shift_end' => "23:59:00",
    //     ];
    //     $fakeRequest = new Request();
    //     $fakeRequest->replace($data);
    //     $test = $this->returnInvoiceService->getCurrentBalance($fakeRequest);
    //     // return $test;
    //     // dd($test->original);
    //     // dd($dishes, $addons, $totalAllPrice);

    //     $dateFirstBalance = EmployeeOpeningBalance::where('cashier_machine_id', $cashierMachineId)->first();
    //     $queryLastBalance = $query->balances_ids;
    //     $queryLastBalance = json_decode($query->balances_ids, true);

    //     $lastOpen = !empty($queryLastBalance) ? min($queryLastBalance) : null;
    //     $opencashlastBalance = EmployeeOpeningBalance::where('id', $lastOpen)->latest()->first();

    //     $data = [
    //         "open_balance" => round($opencashlastBalance?->open_cash ?? 0, 2),
    //         "start_shift" => $end === 0 ? Carbon::parse($dateFirstBalance?->created_at) : $end,
    //         "end_shift" => $start,
    //         "cashier_name" => $query->employee->first_name . ' ' . $query->employee->last_name,
    //         "branch" => $query->branch->$nameColumndish,
    //         "branchManeger" => $query->branch->employees?->first()->first_name . ' ' . $query->branch->employees?->first()->last_name ?? '',
    //         'orders_number' => $numOrders,
    //         'orders_number_refund' => $queryOrder2,
    //         'priceRefund' => round($refundTotal + $refundTotalVisa, 2),
    //         "totalbeforTax" => round($totalbefore ?? 0, 2),
    //         "totalafterTax" => round($totalafter ?? 0, 2),
    //         "actualAmount" => round($query->cash_amount ?? 0, 2),
    //         "Tax" => round($tax ?? 0, 2),
    //         "totalSales" => $allOriginalPriceTotal, //round((($query->visa_amount + (-1 * $query->deficit_visa)) + ($query->cash_amount + (-1 * $query->deficit_cash))), 2),
    //         "categories" => $merged ?? [],
    //         "cashTotalwithoutRefund" => round($allCashPaidTotal ?? 0, 2),
    //         "visaTotalwithoutRefund" => round($allVisaPaidTotal ?? 0, 2),
    //         "cashTotal" => round($cashTotal ?? 0, 2),
    //         "visaTotal" => round($visaTotal ?? 0, 2),
    //         "couponTotalFixed" => round($couponTotalFixed ?? 0, 2),
    //         // "couponTotalpercentage" => round($couponTotalpercentage ?? 0, 2),
    //         "totalAllPrice" => round($totalAllPrice, 2),
    //         // "addons" => $addons,
    //         // "dishes" => $dishes,
    //         'shiftId' => (int)$id,
    //         "service" => round($services, 2),
    //         "deffictCash" => round((int)$query->deficit_cash, 2),
    //         "deffictvise" => round((int)$query->deficit_visa, 2),
    //         'currentCash' => round(($query->cash_amount + (-1 * $query->deficit_cash)), 2),
    //         'currentVisa' => round(($query->visa_amount + (-1 * $query->deficit_visa)), 2),
    //         // 'current cash' => $test->original["data"][0]["value"],
    //         // 'current visa' => $test->original["data"][1]["value"],
    //         'cashSentToSafe' => round((float)$query->cash_amount, 2),
    //         'visaSentToSafe' => round((float)$query->visa_amount, 2),
    //         // 'different cash' => $totalClosingCash - (float)$query->cash_amount,
    //         // 'different visa' => $totalClosingVisa - (float)$query->visa_amount,
    //     ];
    //     return $data;
    //     // dd($data);

    //     // dd($query->employee->employeeSchedules);

    //     // if (auth('admin')->user()->hasRole('Branch Manager')) {
    //     //     $branch_id = getBranchManagerID();
    //     //     if ($branch_id) {
    //     //         $query->where('branch_id', $branch_id);
    //     //     }
    //     // }
    // }
}
