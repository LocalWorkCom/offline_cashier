<?php

namespace App\Http\Controllers\Api\CashierAPIs;

use App\Models\Tip;
use App\Models\Order;
use App\Models\Table;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Events\TotalPaid;
use App\Models\BranchMenu;
use App\Events\TableStatus;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\OrderTracking;
use App\Models\WaiterRequest;
use Illuminate\Support\Carbon;
use App\Models\EmployeeMachine;
use App\Models\OrderTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\ClientServices\TipService;
use App\Services\ClientServices\OrderService;
use App\Services\HR_Services\TimetableService;
use App\Services\ClientServices\InvoiceService;


class CashierInvoiceController extends Controller
{

    protected $orderService;
    protected $timeTableService;
    protected $invoiceService;
    protected $tipService;

    public function __construct(OrderService $orderService, TimetableService $timeTableService, InvoiceService $invoiceService, TipService $tipService)
    {
        $this->tipService = $tipService;
        $this->orderService = $orderService;
        $this->timeTableService = $timeTableService;
        $this->invoiceService = $invoiceService;
    }
    public function listOrdersInvoices(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $today = Carbon::today();
        $shiftDetails = TimetableService::getTimetableForDate($employee->id, $today);
        $twentyFourHoursAgo = Carbon::now()->subHours(24);

        $invoicesQuery = Invoice::whereHas('orders', function ($q) use ($employee, $twentyFourHoursAgo) {
            $q->where('branch_id', $employee->branch_id)
                ->where(function ($query) use ($twentyFourHoursAgo) {
                    $query->where(function ($subQuery) use ($twentyFourHoursAgo) {
                        // Include non-completed/cancelled orders regardless of time
                        $subQuery->whereNotIn('status', ['completed', 'cancelled']);
                    })->orWhere(function ($subQuery) use ($twentyFourHoursAgo) {
                        // Include completed/cancelled orders only if within 24 hours
                        $subQuery->whereIn('status', ['completed', 'cancelled'])
                            ->where('created_at', '>=', $twentyFourHoursAgo);
                    });
                })
                ->where(function ($query) use ($twentyFourHoursAgo) {
                    // ✅ Handle Talabat logic inside orders
                    $query->where('type', '!=', 'talabat')
                        ->orWhere(function ($q) use ($twentyFourHoursAgo) {
                            $q->where('type', 'talabat')
                                ->where(function ($subQ) use ($twentyFourHoursAgo) {
                                    // Show if paid OR unpaid but within 10 hours
                                    $subQ->whereHas('orderTransactions', function ($trans) {
                                        $trans->where('payment_status', 'paid');
                                    })
                                        ->orWhere(function ($transQ) use ($twentyFourHoursAgo) {
                                            $transQ->whereHas('orderTransactions', function ($trans) {
                                                $trans->where('payment_status', 'unpaid');
                                            })
                                                ->where('created_at', '>=', $twentyFourHoursAgo);
                                        });
                                });
                        });
                });
        })
            ->with([
                'orders.branch',
                'orders.tracking',
                'orders.table',
                'orders.orderTransactions',
                'invoiceDetails',
                'invoiceDetails.orderDetail.dish',
                'invoiceDetails.orderAddon'
            ]);


        if ($shiftDetails['status']) {
            $onDutyTime = $shiftDetails['data']['on_duty_time'];
            $offDutyTime = $shiftDetails['data']['off_duty_time'];
            $invoicesQuery->whereHas('orders', function ($query) use ($onDutyTime, $offDutyTime, $shiftDetails) {
                if ($shiftDetails['data']['cross_day']) {
                    $query->where(function ($q) use ($onDutyTime, $offDutyTime) {
                        $q->whereTime('created_at', '>=', $onDutyTime)
                            ->orWhereTime('created_at', '<=', $offDutyTime);
                    });
                } else {
                    $query->whereTime('created_at', '>=', $onDutyTime)
                        ->whereTime('created_at', '<=', $offDutyTime);
                }
            });
        }

        $invoices = $invoicesQuery->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($invoices->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.',
                'errorData' => ['error' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.'],
                'data' => null
            ], 200);
        }

        $responseData = $invoices->map(function ($invoice) use ($lang, $employee) {
            $order = $invoice->orders;

            $hasPaidTransaction = $order->orderTransactions->contains(function ($transaction) {
                return $transaction->payment_status === 'paid' && $transaction->is_refund == 0;
            });

            if ($hasPaidTransaction) {
                $dishes = $invoice->invoiceDetails->where('type', 'dish')->keyBy('details_id');
            } else {
                $dishes = $invoice->invoiceDetails->where('status', '!=', 'cancel')->where('type', 'dish')->keyBy('details_id');
            }

            $orderItemsCount = $dishes->sum('quantity');
            $maxDishTime = $dishes->max(function ($detail) {
                if ($detail->type === 'dish' && $detail->orderDetail?->dish) {
                    return $detail->orderDetail->dish->time ?? 0;
                }
                return 0;
            });

            $invoiceData = [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type,
                'invoice_number' => $invoice->invoice_num,
                'table_number' => ($order->type == 'dine-in') ? $order->table?->table_number : null,
                'invoice_print_status' => $order->print_status ?? null,
                'order_id' => $order->id ?? null,
                'order_type' => $order->type ?? null,
                'order_number' => $order->order_number ?? null,
                'order_items_count' => $orderItemsCount,
                'order_time' => $maxDishTime,
                'print_count' => $order->print_count_cashier ?? 0,
                'order_status' => $order->status ?? null,
                'payment_status' => $invoice->status ?? null
            ];

            $invoiceData['invoice_details'] = $this->getInvoiceDetailsData($invoice, $employee, $lang);
            $invoiceData['invoice_tips'] = $invoice->tips;

            return $invoiceData;
        })->values();

        $response = [
            'invoices' => $responseData,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }

    // dalia function to avoid code repetition
    public function listOrdersInvoicesEnchance(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $today = Carbon::today();
        $shiftDetails = TimetableService::getTimetableForDate($employee->id, $today);
        $twentyFourHoursAgo = Carbon::now()->subHours(24);

        // 🧩 الأساسيات
        $invoicesQuery = Invoice::whereHas('orders', function ($q) use ($employee, $twentyFourHoursAgo) {
            $q->where('branch_id', $employee->branch_id)
                ->where(function ($query) use ($twentyFourHoursAgo) {
                    $query->where(function ($subQuery) {
                        // Include non-completed/cancelled orders regardless of time
                        $subQuery->whereNotIn('status', ['completed', 'cancelled']);
                    })->orWhere(function ($subQuery) use ($twentyFourHoursAgo) {
                        // Include completed/cancelled orders only if within 24 hours
                        $subQuery->whereIn('status', ['completed', 'cancelled'])
                            ->where('created_at', '>=', $twentyFourHoursAgo);
                    });
                });
        });

        // 🕒 وقت الوردية
        if ($shiftDetails['status']) {
            $onDutyTime = $shiftDetails['data']['on_duty_time'];
            $offDutyTime = $shiftDetails['data']['off_duty_time'];

            $invoicesQuery->whereHas('orders', function ($query) use ($onDutyTime, $offDutyTime, $shiftDetails) {
                if ($shiftDetails['data']['cross_day']) {
                    $query->where(function ($q) use ($onDutyTime, $offDutyTime) {
                        $q->whereTime('created_at', '>=', $onDutyTime)
                            ->orWhereTime('created_at', '<=', $offDutyTime);
                    });
                } else {
                    $query->whereTime('created_at', '>=', $onDutyTime)
                        ->whereTime('created_at', '<=', $offDutyTime);
                }
            });
        }

        // ⚡ تحميل العلاقات الضرورية فقط
        $invoicesQuery->with([
            'orders:id,branch_id,type,table_id,status,print_status,print_count_cashier,order_number',
            'orders.table:id,table_number',
            'invoiceDetails:id,invoice_id,type,status,details_id,quantity',
            'invoiceDetails.orderDetail:id,dish_id',
            'invoiceDetails.orderDetail.dish:id,time',
            'tips:id,invoice_id,payment_method,tip_amount'
        ]);

        // 📄 Pagination (الصفحة - العدد)
        $perPage = $request->get('per_page', 50);
        $invoices = $invoicesQuery
            ->where('created_at', '>=', Carbon::now()->subWeek())
            // ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        if ($invoices->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'No orders found' : 'لا توجد طلبات.',
                'data' => null
            ]);
        }

        // ⚙️ تجهيز البيانات
        $responseData = $invoices->map(function ($invoice) use ($lang, $employee) {
            $order = $invoice->orders;

            $hasPaidTransaction = $order->orderTransactions
                ->contains(fn($transaction) => $transaction->payment_status === 'paid' && $transaction->is_refund == 0);

            $dishes = $invoice->invoiceDetails->where('type', 'dish');
            if (!$hasPaidTransaction) {
                $dishes = $dishes->where('status', '!=', 'cancel');
            }

            $orderItemsCount = $dishes->sum('quantity');
            $maxDishTime = $dishes->max(fn($detail) => optional($detail->orderDetail?->dish)->time ?? 0);

            return [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type,
                'invoice_number' => $invoice->invoice_num,
                'table_number' => $order->type === 'dine-in' ? optional($order->table)->table_number : null,
                'invoice_print_status' => $order->print_status,
                'order_id' => $order->id,
                'order_type' => $order->type,
                'order_number' => $order->order_number,
                'order_items_count' => $orderItemsCount,
                'order_time' => $maxDishTime,
                'print_count' => $order->print_count_cashier ?? 0,
                'order_status' => $order->status,
                'payment_status' => $invoice->status,
                'invoice_details' => $this->getInvoiceDetailsData($invoice, $employee, $lang),
                'invoice_tips' => $invoice->tips,
            ];
        });

        // ✅ Response مع Pagination
        return ResponseWithSuccessData($lang, [
            'invoices' => $responseData,
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ]
        ], 1);
    }



    private function getInvoiceDetailsData($invoice, $employee, $lang)
    {
        $cashierInfo = [
            'first_name' => $employee->first_name ?? '',
            'last_name' => $employee->last_name ?? '',
            'email' => $employee->email ?? '',
            'phone_number' => $employee->phone_number ?? '',
            'employee_code' => $employee->employee_code ?? '',
        ];

        $order = $invoice->orders;
        if (!$order) {
            return null;
        }
        $order = (object) $order;

        $branchDetails = [
            'branch_id' => $order->branch->id ?? null,
            'branch_name' => $order->branch->name ?? null,
            'branch_address' => ($lang === 'ar') ? $order->branch->address_ar ?? null : $order->branch->address_en ?? null,
            'created_at' => $invoice->created_at,
            'invoice_number' => $invoice->invoice_num,
            'order_number' => $order->order_number ?? null,
            'branch_phone' => $order->branch->phone ?? null,
        ];

        if ($order->type == 'dine-in' && $order->table) {
            $branchDetails['table_number'] = $order->table->table_number ?? null;
            $branchDetails['floor_name'] = $lang === 'ar'
                ? ($order->table->floors->name_ar ?? null)
                : ($order->table->floors->name_en ?? null);
            $branchDetails['floor_partition_name'] = $lang === 'ar'
                ? ($order->table->floorPartitions->name_ar ?? null)
                : ($order->table->floorPartitions->name_en ?? null);
        }

        $addressDetails = null;
        // dd($order->address);
        if ($order->type == 'Delivery') {
            //  dd($order->address->address);
            // $addressDetails = [
            //     'client_address' => $order->address->address ?? null,
            //     'client_name' => $order->Client && $order->Client->flag != 'unknown'
            //         ? $order->Client->name
            //         : 'عميل غير معروف',
            //     'client_phone' => ($order->Client && $order->Client->flag != 'unknown')
            //         ? $order->Client->phone
            //         : ($order->address ? $order->address->address_phone : $order->client_phone),

            //     'address_phone' => $order->address ? $order->address->address_phone : null,
            //     'address_notes' => $order->address->notes ?? null,
            // ];
            $addressDetails = [
                'client_address' => $order->address->address ?? null,
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : ($order->address ? $order->address->address_phone : $order->client_phone),
                'address_phone' => $order->address ? $order->address->address_phone : null,
                'address_notes' => $order->address->notes ?? null,
            ];
            // dd($addressDetails);
        }

        $currencySymbol = $order->branch?->country?->currency_symbol ?? 'ج.م';

        // Get merged invoice details
        $mergedData = $this->invoiceService->merge_invoice_details($invoice->id);
        $mergedDishes = $mergedData['dishes'];

        $orderDetails = collect($mergedDishes)->map(function ($dish) use ($lang, $order) {
            $dishAddons = collect($dish['addons'])->map(function ($addon) {
                return [
                    'addon_id' => $addon['addon_id'],
                    'addon_name' => $addon['name'],
                ];
            })->values();

            $totalPrice = ($order->tax_application == 0)
                ? $dish['price_before_tax']
                : $dish['price_after_tax'];

            $dishCouponId = $dish['coupon_id'] ?? null;
            $dishCouponValue = $dishCouponId ? ($dish['coupon_value'] ?? 0) : 0;

            return [
                'dish_id' => $dish['dish_id'],
                'dish_name' => $dish['name'],
                'size' => $dish['size'],
                'quantity' => $dish['quantity'],
                'total_dish_price' => $dish['total_before_coupon'],
                'total_dish_price_coupon_applied' => formatFloat($totalPrice),
                'note' => $dish['note'],
                'addons' => $dishAddons,
                'coupon_id' => $dishCouponId,
                'coupon_value' => formatFloat($dishCouponValue),
                'coupon_title' => $dishCouponId ? $dish['coupon_title'] : null
            ];
        })->values();

        $couponId = $invoice->coupon_id;
        $couponType = $invoice->coupon?->type;
        $couponValue = $invoice->coupon_value;
        $couponTitle = $invoice->coupon?->title;
        $couponCode = $invoice->coupon?->code;

        if (!$couponId) {
            $invoiceDetailsWithCoupons = $invoice->invoiceDetails->whereNotNull('coupon_id');
            if ($invoice->status != 'paid') {
                $invoiceDetailsWithCoupons = $invoiceDetailsWithCoupons->where('status', '!=', 'cancel');
            }
            if ($invoiceDetailsWithCoupons->isNotEmpty()) {
                $firstInvoiceDetailWithCoupon = $invoiceDetailsWithCoupons->first();
                $couponId = $firstInvoiceDetailWithCoupon->coupon_id;
                $couponType = $firstInvoiceDetailWithCoupon->coupon?->type;
                $couponTitle = $firstInvoiceDetailWithCoupon->coupon?->title;
                $couponCode = $firstInvoiceDetailWithCoupon->coupon?->code;
            }
        }

        $invoiceSummary = [
            'subtotal_price' => formatPrice($invoice->total_before_tax),
            'subtotal_price_before_coupon' => formatFloat($invoice->total_before_coupon),
            'delivery_fees' => formatFloat($order->delivery_fees ?? null),
            'service_fees' => formatFloat($invoice->service_fees ?? null),
            'service_percentage' => formatFloat($order->service_percentage ?? null),
            'tax_value' => formatFloat($invoice->tax ?? null),
            'tax_apply' => !empty($invoice->tax) || $invoice->tax != 0,
            'tax_application' => $order->tax_application == 1,
            'tax_percentage' => formatFloat($order->tax_percentage ?? null),
            'coupon_id' => $couponId,
            'coupon_code' => $couponCode,
            'coupon_type' => $couponType,
            'coupon_value' => formatFloat($couponValue),
            'coupon_title' => $couponTitle,
            'total_price' => formatFloat($invoice->total_after_tax),
        ];

        // Process transactions
        $refundTransactions = $invoice->orderTransactions->where('is_refund', 1);
        $normalTransactions = $invoice->orderTransactions->where('is_refund', 0);

        $processedTransactions = [];

        if ($refundTransactions->isNotEmpty()) {
            $firstRefundTransaction = $refundTransactions->first();
            $totalRefund = $refundTransactions->sum('refund');

            $processedTransactions[] = [
                'payment_status' => $firstRefundTransaction->payment_status,
                'payment_method' => $firstRefundTransaction->payment_method,
                'paid' => formatFloat($firstRefundTransaction->paid),
                'refund' => formatFloat($totalRefund),
                'date' => $firstRefundTransaction->date,
                'is_refund' => 1,
            ];
        }

        if ($normalTransactions->isNotEmpty()) {
            $cashTransactions = $normalTransactions->where('payment_method', 'cash');
            $creditTransactions = $normalTransactions->where('payment_method', 'credit');

            if ($cashTransactions->isNotEmpty()) {
                $firstCashTransaction = $cashTransactions->first();
                $processedTransactions[] = [
                    'payment_status' => $firstCashTransaction->payment_status,
                    'payment_method' => 'cash',
                    'paid' => formatFloat($cashTransactions->sum('paid')),
                    'refund' => formatFloat($cashTransactions->sum('refund')),
                    'date' => $firstCashTransaction->date,
                    'is_refund' => 0,
                ];
            }

            if ($creditTransactions->isNotEmpty()) {
                $firstCreditTransaction = $creditTransactions->first();
                $processedTransactions[] = [
                    'payment_status' => $firstCreditTransaction->payment_status,
                    'payment_method' => 'credit',
                    'paid' => formatFloat($creditTransactions->sum('paid')),
                    'refund' => formatFloat($creditTransactions->sum('refund')),
                    'date' => $firstCreditTransaction->date,
                    'is_refund' => 0,
                ];
            }

            $otherTransactions = $normalTransactions->whereNotIn('payment_method', ['cash', 'credit']);
            foreach ($otherTransactions as $transaction) {
                $processedTransactions[] = [
                    'payment_status' => $transaction->payment_status,
                    'payment_method' => $transaction->payment_method,
                    'paid' => formatFloat($transaction->paid),
                    'refund' => formatFloat($transaction->refund),
                    'date' => $transaction->date,
                    'is_refund' => $transaction->is_refund ?? 0,
                ];
            }
        }

        return [
            'order_status' => $order->status,
            'tracking-status' => $order->type == 'Delivery' ? ($order->tracking->last()->order_status ?? null) : null,
            'delivery_name' => $order->delivery ? ($order->delivery->first_name . ' ' . $order->delivery->last_name) : null,
            'order_type' => $order->type,
            'branch_details' => $branchDetails,
            'address_details' => $addressDetails,
            'orderDetails' => $orderDetails,
            'transactions' => $processedTransactions,
            'invoice_summary' => $invoiceSummary,
            'currency_symbol' => $currencySymbol,
            'print_count' => $order->print_count_cashier ?? 0,
            'is_refund' => $invoice->invoice_type === 'credit_note',
            'original_invoice_id' => $invoice->invoice_type === 'credit_note' ? $invoice->parent_id : null,
            'original_invoice_number' => $invoice->invoice_type === 'credit_note' ? $invoice->parentInvoice->invoice_num : null,
            'return_type' => $invoice->invoice_type === 'credit_note' ? $invoice->parentInvoice->returnInvoiceRequests->first()->request_type : null,
            'cashier_info' => $cashierInfo,
        ];
    }
    public function invoiceDetails(Request $request, $invoiceId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $cashierInfo = [
            'first_name'    => $employee->first_name ?? '',
            'last_name'     => $employee->last_name ?? '',
            'email'         => $employee->email ?? '',
            'phone_number'  => $employee->phone_number ?? '',
            'employee_code' => $employee->employee_code ?? '',
        ];

        $invoice = Invoice::where('id', $invoiceId)
            ->with([
                'orders.branch',
                'orders.tracking',
                'orders.table.floors',
                'orders.table.floorPartitions',
                'orders.delivery',
                'orders.address',
                'orders.client',
                'orders.coupon',
                'invoiceDetails',
                'orderTransactions',
                'returnInvoiceRequests',
            ])->first();

        //   return $invoice;

        if (!$invoice) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Invoice does not exist' : 'الفاتورة غير موجودة.',
                'errorData' => ['error' => $lang == 'en' ? 'Invoice does not exist' : 'الفاتورة غير موجودة.'],
                'data' => null
            ], 200);
        }

        $order = $invoice->orders;

        if (!$order) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Order not found for this invoice' : 'الطلب غير موجود لهذه الفاتورة.',
                'errorData' => ['error' => $lang == 'en' ? 'Order not found for this invoice' : 'الطلب غير موجود لهذه الفاتورة.'],
                'data' => null
            ], 200);
        }
        $order = (object) $order;

        $branchDetails = [
            'branch_id' => $order->branch->id ?? null,
            'branch_name' => $order->branch->name ?? null,
            'branch_address' => ($lang === 'ar') ? $order->branch->address_ar ?? null : $order->branch->address_en ?? null,
            'created_at' => $invoice->created_at,
            'invoice_number' => $invoice->invoice_num,
            'order_number' => $order->order_number ?? null,
            'branch_phone' => $order->branch->phone ?? null,
        ];
        if ($order->type == 'dine-in' && $order->table) {
            $branchDetails['table_number'] = $order->table->table_number ?? null;
            $branchDetails['floor_name'] = $lang === 'ar'
                ? ($order->table->floors->name_ar ?? null)
                : ($order->table->floors->name_en ?? null);
            $branchDetails['floor_partition_name'] = $lang === 'ar'
                ? ($order->table->floorPartitions->name_ar ?? null)
                : ($order->table->floorPartitions->name_en ?? null);
        }
        $addressDetails = null;
        if ($order->type == 'Delivery') {
            $addressDetails = [
                'client_address' => $order->address->address ?? null,
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : ($order->address ? $order->address->address_phone : $order->client_phone),
                'address_phone' => $order->address ? $order->address->address_phone : null,
                'address_notes' => $order->address->notes ?? null,
            ];
        }
        $currencySymbol = $order->branch?->country?->currency_symbol ?? 'ج.م';

        // Get merged invoice details
        $mergedData = $this->invoiceService->merge_invoice_details($invoiceId);
        $mergedDishes = $mergedData['dishes'];

        $orderDetails = collect($mergedDishes)->map(function ($dish) use ($lang, $order) {
            $dishAddons = collect($dish['addons'])->map(function ($addon) {
                return [
                    'addon_id' => $addon['addon_id'],
                    'addon_name' => $addon['name'],
                    // 'quantity' => $addon['quantity'],
                    // 'price_before_tax' => formatFloat($addon['price_before_tax']),
                    // 'price_after_tax' => formatFloat($addon['price_after_tax']),
                    // 'note' => $addon['note'],
                ];
            })->values();





            $totalPrice = ($order->tax_application == 0) ? $dish['price_before_tax'] : $dish['price_after_tax'];

            // Check if dish has coupon_id - only apply coupon if it exists
            $dishCouponId = $dish['coupon_id'] ?? null;
            $dishCouponValue = $dishCouponId ? ($dish['coupon_value'] ?? 0) : 0;

            return [
                'dish_id' => $dish['dish_id'],
                'dish_name' => $dish['name'],
                'size' => $dish['size'],
                'quantity' => $dish['quantity'],
                'total_dish_price' => $dish['total_before_coupon'],
                'total_dish_price_coupon_applied' => formatFloat($totalPrice),
                'note' => $dish['note'],
                'addons' => $dishAddons,
                'coupon_id' => $dishCouponId,
                'coupon_value' => formatFloat($dishCouponValue),
                'coupon_title' => $dishCouponId ? $dish['coupon_title'] : null
            ];
        })->values();

        $couponId = $invoice->coupon_id;
        $couponType = $invoice->coupon?->type;
        $couponValue = $invoice->coupon_value;
        $couponTitle = $invoice->coupon?->title;
        $couponCode = $invoice->coupon?->code;

        $neworderDetails = $this->talabat_dish_fetch($orderDetails, $invoice->orders?->type, $invoice->order_id);
        // return $neworderDetails;

        // If invoice doesn't have a coupon, check invoice details for coupon
        if (!$couponId) {
            $invoiceDetailsWithCoupons = $invoice->invoiceDetails->whereNotNull('coupon_id');
            if ($invoice->status != 'paid') {
                $invoiceDetailsWithCoupons = $invoiceDetailsWithCoupons->where('status', '!=', 'cancel');
            }
            if ($invoiceDetailsWithCoupons->isNotEmpty()) {
                $firstInvoiceDetailWithCoupon = $invoiceDetailsWithCoupons->first();
                $couponId = $firstInvoiceDetailWithCoupon->coupon_id;
                $couponType = $firstInvoiceDetailWithCoupon->coupon?->type;
                $couponTitle = $firstInvoiceDetailWithCoupon->coupon?->title;
                $couponCode = $firstInvoiceDetailWithCoupon->coupon?->code;
            }
        }

        $total_before_tax =  0;
        $total_before_coupon = 0;
        $service_fees = 0;
        $tax = 0;
        $total_after_tax = 0;
        $coupon_Value = 0;


        // return $mergedData;
        // if($invoice->status === 'unpaid'){
        //    foreach($mergedDishes as $key => $value){
        //     // return $value;
        //       $total_before_tax += $value['price_before_tax'];
        //       $total_before_coupon += $value['total_before_coupon'];
        //       $service_fees += $value['service_fees'];
        //       $tax += $value['tax'];
        //       $coupon_Value += $value['coupon_value'];
        //       $total_after_tax += $value['price_after_tax'];

        //    }

        // //    return $invoice->tips[0]->id;
        //     if($invoice->status === 'unpaid'){
        //             $updateTips = Tip::where('id', $invoice->tips[0]->id)->first();
        //             $updateTips->bill_amount = $total_after_tax;
        //             $updateTips->total_with_tip = $total_after_tax;
        //             $updateTips->save();
        //     }

        //    return $updateTips;
        // }
        // else{
        //     $total_before_tax =  $invoice->total_before_tax;
        //     $total_before_coupon = $invoice->total_before_coupon;
        //     $service_fees = $invoice->service_fees;
        //     $tax = $invoice->tax;
        //     $total_after_tax = $invoice->total_after_tax;
        //     $coupon_Value = $couponValue;
        // }

        //  $invoiceSummary = [
        //     'subtotal_price' => formatPrice($total_before_tax),
        //     'subtotal_price_before_coupon' => formatFloat($total_before_coupon),
        //     'delivery_fees' => formatFloat($order->delivery_fees ?? null),
        //     'service_fees' => formatFloat($service_fees ?? null),
        //     'service_percentage' => formatFloat($order->service_percentage ?? null),
        //     'tax_value' => formatFloat($tax ?? null),
        //     'tax_apply' => !empty($tax) || $tax != 0,
        //     'tax_application' => $order->tax_application == 1 ? true : false,
        //     'tax_percentage' => formatFloat($order->tax_percentage ?? null),
        //     'coupon_id' => $couponId,
        //     'coupon_code' => $couponCode,
        //     'coupon_type' => $couponType,
        //     'coupon_value' => formatFloat($coupon_Value),
        //     'coupon_title' => $couponTitle,
        //     'total_price' => formatFloat($total_after_tax),
        // ];





        $invoiceSummary = [
            'subtotal_price' => formatPrice($invoice->total_before_tax),
            'subtotal_price_before_coupon' => formatFloat($invoice->total_before_coupon),
            'delivery_fees' => formatFloat($order->delivery_fees ?? null),
            'service_fees' => formatFloat($invoice->service_fees ?? null),
            'service_percentage' => formatFloat($order->service_percentage ?? null),
            'tax_value' => formatFloat($invoice->tax ?? null),
            'tax_apply' => !empty($invoice->tax) || $invoice->tax != 0,
            'tax_application' => $order->tax_application == 1 ? true : false,
            'tax_percentage' => formatFloat($order->tax_percentage ?? null),
            'coupon_id' => $couponId,
            'coupon_code' => $couponCode,
            'coupon_type' => $couponType,
            'coupon_value' => formatFloat($couponValue),
            'coupon_title' => $couponTitle,
            'total_price' => formatFloat($invoice->total_after_tax),
        ];

        // Process transactions based on is_refund status
        $refundTransactions = $invoice->orderTransactions->where('is_refund', 1);
        $normalTransactions = $invoice->orderTransactions->where('is_refund', 0);

        $processedTransactions = [];

        // Handle refund transactions (is_refund = 1)
        if ($refundTransactions->isNotEmpty()) {
            $firstRefundTransaction = $refundTransactions->first();
            $totalRefund = $refundTransactions->sum('refund');

            $processedTransactions[] = [
                'payment_status' => $firstRefundTransaction->payment_status,
                'payment_method' => $firstRefundTransaction->payment_method,
                'paid' => formatFloat($firstRefundTransaction->paid),
                'refund' => formatFloat($totalRefund),
                'date' => $firstRefundTransaction->date,
                'is_refund' => 1,
            ];
        }

        // Handle normal transactions (is_refund = 0)
        if ($normalTransactions->isNotEmpty()) {
            $cashTransactions = $normalTransactions->where('payment_method', 'cash');
            $creditTransactions = $normalTransactions->where('payment_method', 'credit');

            // Merge all cash transactions into one
            if ($cashTransactions->isNotEmpty()) {
                $firstCashTransaction = $cashTransactions->first();
                $totalCashPaid = $cashTransactions->sum('paid');
                $totalCashRefund = $cashTransactions->sum('refund');

                $processedTransactions[] = [
                    'payment_status' => $firstCashTransaction->payment_status,
                    'payment_method' => 'cash',
                    'paid' => formatFloat($totalCashPaid),
                    'refund' => formatFloat($totalCashRefund),
                    'date' => $firstCashTransaction->date,
                    'is_refund' => 0,
                ];
            }

            // Merge all credit transactions into one
            if ($creditTransactions->isNotEmpty()) {
                $firstCreditTransaction = $creditTransactions->first();
                $totalCreditPaid = $creditTransactions->sum('paid');
                $totalCreditRefund = $creditTransactions->sum('refund');

                $processedTransactions[] = [
                    'payment_status' => $firstCreditTransaction->payment_status,
                    'payment_method' => 'credit',
                    'paid' => formatFloat($totalCreditPaid),
                    'refund' => formatFloat($totalCreditRefund),
                    'date' => $firstCreditTransaction->date,
                    'is_refund' => 0,
                ];
            }

            // Handle any other payment methods
            $otherTransactions = $normalTransactions->whereNotIn('payment_method', ['cash', 'credit']);
            foreach ($otherTransactions as $transaction) {
                $processedTransactions[] = [
                    'payment_status' => $transaction->payment_status,
                    'payment_method' => $transaction->payment_method,
                    'paid' => formatFloat($transaction->paid),
                    'refund' => formatFloat($transaction->refund),
                    'date' => $transaction->date,
                    'is_refund' => $transaction->is_refund ?? 0,
                ];
            }
        }

        $transactions = $processedTransactions;
        $invoiceArr = [
            'order_status' => $order->status,
            'tracking-status' => $order->type == 'Delivery' ? ($order->tracking->last()->order_status ?? null) : null,
            'delivery_name' => $order->delivery ? ($order->delivery->first_name . ' ' . $order->delivery->last_name) : null,
            'order_type' => $order->type,
            'branch_details' => $branchDetails,
            'address_details' => $addressDetails,
            'orderDetails' => $neworderDetails,
            'transactions' => $transactions,
            'invoice_summary' => $invoiceSummary,
            'currency_symbol' => $currencySymbol,
            'print_count' => $order->print_count_cashier ?? 0,
            'is_refund' => $invoice->invoice_type === 'credit_note',
            'original_invoice_id' => $invoice->invoice_type === 'credit_note' ? $invoice->parent_id : null,
            'original_invoice_number' => $invoice->invoice_type === 'credit_note' ? $invoice->parentInvoice->invoice_num : null,
            'return_type' => $invoice->invoice_type === 'credit_note' ? $invoice->parentInvoice->returnInvoiceRequests->first()->request_type : null,
            'cashier_info' => $cashierInfo,
        ];

        $response = [
            'order_id' => $order->id,
            'invoices' => [$invoiceArr],
            'invoice_tips' => $invoice->tips,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }

    public function talabat_dish_fetch($orderDetails, $order_type, $order_id)
    {
        // dd($order_type);
        $updatedItems = [];
        if ($order_type == 'talabat') {
            foreach ($orderDetails as $item) {
                $branchMenuDetails = OrderDetail::where('dish_id', $item['dish_id'])->where('order_id', $order_id)->first();
                //  dd($branchMenuDetails);

                // $menu_ingrate = MenusIntegrationDish::where()
                $item['total_dish_price'] = $branchMenuDetails
                    ? (float) str_replace(',', '', $branchMenuDetails->price_befor_tax)
                    : 0.0;

                $updatedItems[] = $item;
            }

            return $updatedItems;
        } else {
            // For other order types, return all dishes
            return $orderDetails;
        }
    }
    // public function updateOrderInvoice(Request $request, $orderId)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);

    //     $employee = auth('employee')->user();
    //     $cashier_machine_id = EmployeeMachine::where('employee_id', $employee->id)
    //         ->orderby('id', 'desc')
    //         ->first()->cashier_machine_id ?? null;
    //     if (!$employee) {
    //         return RespondWithBadRequest($lang, 4);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'order_status' => 'nullable|in:on_way,delivered',
    //         'payment_status' => 'nullable|in:paid',
    //         'cashier_machine_id' => 'nullable|exists:cashier_machines,id,deleted_at,NULL',
    //         'cash_amount' => [
    //             'required_if:payment_status,paid',
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //             function ($attribute, $value, $fail) use ($request, $lang) {
    //                 if ($value !== null && (!$request->has('payment_status') || $request->payment_status !== 'paid')) {
    //                     $fail($lang == 'en' ? 'You cannot enter cash amount unless payment status is updated to paid.' : 'لا يمكن إدخال مبلغ نقدي إلا إذا تم تحديث حالة الدفع إلى مدفوع.');
    //                 }
    //             },
    //         ],
    //         'credit_amount' => [
    //             'required_if:payment_status,paid',
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //             function ($attribute, $value, $fail) use ($request, $lang) {
    //                 if ($value !== null && (!$request->has('payment_status') || $request->payment_status !== 'paid')) {
    //                     $fail($lang == 'en' ? 'You cannot enter credit amount unless payment status is updated to paid.' : 'لا يمكن إدخال مبلغ ائتماني إلا إذا تم تحديث حالة الدفع إلى مدفوع.');
    //                 }
    //             },
    //         ],
    //         'reference_number' => [
    //             'nullable',
    //             function ($attribute, $value, $fail) use ($request, $lang) {
    //                 $creditAmount = $request->input('credit_amount');
    //                 if ($creditAmount !== null && floatval($creditAmount) != 0 && (is_null($value) || $value === '')) {
    //                     $fail($lang == 'en' ? 'Reference number is required when there is a credit amount.' : 'رقم المرجع مطلوب عند وجود مبلغ ائتماني.');
    //                 }
    //             },
    //         ],
    //     ]);

    //     if ($validator->fails()) {
    //         return respondError('Validation Error.', 400, $validator->errors());
    //     }

    //     $order = Order::where('branch_id', $employee->branch_id)
    //         ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'orderTransactions'])
    //         ->find($orderId);

    //     if (!$order) {
    //         return response()->json([
    //             'status' => false,
    //             'code' => 400,
    //             'message' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.',
    //             'errorData' => ['error' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.'],
    //             'data' => null
    //         ], 200);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // if ($request->has('cashier_machine_id')) {
    //         $order->update([
    //             'cashier_machine_id' => $request->cashier_machine_id ?? $cashier_machine_id,
    //         ]);
    //         // }

    //         if ($request->has('order_status') && $order->type !== 'Delivery') {
    //             DB::rollBack();
    //             return response()->json([
    //                 'status' => false,
    //                 'code' => 400,
    //                 'message' => $lang == 'en' ? 'Order status can only be updated for Delivery orders.' : 'يمكن تحديث حالة الطلب فقط للطلبات التوصيل.',
    //                 'errorData' => ['error' => $lang == 'en' ? 'Order status can only be updated for Delivery orders.' : 'يمكن تحديث حالة الطلب فقط للطلبات التوصيل.'],
    //                 'data' => null
    //             ], 200);
    //         }
    //         if ($request->has('order_status')) {
    //             $order->tracking()->create([
    //                 'order_id' => $order->id,
    //                 'order_status' => $request->order_status,
    //                 'created_by' => $employee->id,
    //             ]);
    //             // Notify: Order status updated
    //             $notifyData = [
    //                 'notification_type' => 'order',
    //                 'title_ar' => 'تم تحديث حالة الطلب',
    //                 'title_en' => 'Order status updated',
    //                 'description_ar' => 'تم تحديث حالة الطلب رقم ' . $orderId . ' إلى ' . $request->order_status . ' بواسطة الكاشير ' . $employee->first_name . ' ' . $employee->last_name . '.',
    //                 'description_en' => 'Order #' . $orderId . ' status updated to ' . $request->order_status . ' by cashier ' . $employee->first_name . ' ' . $employee->last_name . '.',
    //                 'created_by' => $employee->id,
    //                 'order_id' => $orderId
    //             ];
    //             runNotificationToEmployees($order->branch_id, $notifyData, $employee->id, $orderId, $lang);
    //         }

    //         $branchId = $employee->branch_id;
    //         $created_by = $employee->id;
    //         $makeType = 'cashier';
    //         $orderDeposit = getBranchSettings($branchId, 'order_reservation_deposit');
    //         $coupon = $order->coupon_id;

    //         if ($request->has('payment_status') && $request->payment_status === 'paid') {
    //             $hasUnpaidTransaction = $order->orderTransactions->contains(function ($transaction) {
    //                 return $transaction->payment_status == 'unpaid';
    //             });
    //             if (!$hasUnpaidTransaction) {
    //                 DB::rollBack();
    //                 return RespondWithErrorMsg($lang == 'en' ? 'No unpaid transactions exist for this order.' : 'لا توجد معاملات غير مدفوعة لهذا الطلب.');
    //             }

    //             // Update invoice status to paid
    //             $invoice = Invoice::where('order_id', $orderId)->first();
    //             $invoiceId = null;
    //             if ($invoice) {
    //                 $this->invoiceService->updateInvoice($invoice->id);
    //                 $invoiceId = $invoice->id;
    //             }

    //             $cash_amount = $request->cash_amount ?? 0;
    //             $credit_amount = $request->credit_amount ?? 0;

    //             if ($order->type == 'dine-in' || $order->type == 'Takeaway' || $order->type == 'Delivery') {
    //                 // $totalPaid = round($cash_amount + $credit_amount, 3);
    //                  $totalPaid = round($cash_amount + $credit_amount,2);
    //                   $taxedTotal = round((float)$order->total_price_after_tax, 2);
    //                 if ($totalPaid < $taxedTotal) {
    //                     DB::rollBack();
    //                     return RespondWithErrorMsg(__('order.amount_wrong'));
    //                 }
    //             }

    //             // Only update statuses if the amount is correct
    //             $order->update([
    //                 'status' => 'completed',
    //                 'print_status' => 'done',
    //                 'cashier_id' => $employee->id,
    //                 'modify_by' => $employee->id,
    //             ]);

    //             $order->tracking()->create([
    //                 'order_id' => $order->id,
    //                 'order_status' => 'completed',
    //                 'created_by' => $employee->id,
    //             ]);

    //             // Notify: Order status completed
    //             $notifyData = [
    //                 'notification_type' => 'order',
    //                 'title_ar' => 'تم تحديث حالة الطلب',
    //                 'title_en' => 'Order status updated',
    //                 'description_ar' => 'تم تحديث حالة الطلب رقم ' . $orderId . ' إلى مكتمل بواسطة الكاشير ' . $employee->first_name . ' ' . $employee->last_name . '.',
    //                 'description_en' => 'Order #' . $orderId . ' status updated to completed by cashier ' . $employee->first_name . ' ' . $employee->last_name . '.',
    //                 'created_by' => $employee->id,
    //                 'order_id' => $orderId
    //             ];
    //             runNotificationToEmployees($order->branch_id, $notifyData, $employee->id, $orderId, $lang);

    //             if ($order->type == 'dine-in' && $order->table) {
    //                 $order->Table()->update([
    //                     'status' => 1,
    //                     'modified_by' => $employee->id,
    //                 ]);

    //                 // Prepare table data for notification and broadcast
    //                 $table = $order->table->load(['floors', 'floorPartitions']);
    //                 $data = [
    //                     'id' => $table->id,
    //                     'name' => $table->name,
    //                     'name_ar' => $table->name_ar,
    //                     'name_en' => $table->name_en,
    //                     'table_number' => $table->table_number,
    //                     'status' => $table->status,
    //                     'smoking' => $table->smoking,
    //                     'floors' => [
    //                         'id' => $table->floors->id ?? null,
    //                         'name' => $table->floors->name ?? null,
    //                         'name_ar' => $table->floors->name_ar ?? null,
    //                         'name_en' => $table->floors->name_en ?? null
    //                     ],
    //                     'floor_partitions' => [
    //                         'id' => $table->floorPartitions->id ?? null,
    //                         'name' => $table->floorPartitions->name ?? null,
    //                         'name_ar' => $table->floorPartitions->name_ar ?? null,
    //                         'name_en' => $table->floorPartitions->name_en ?? null
    //                     ]
    //                 ];
    //                 // Notify: Table status updated
    //                 $notifyData = [
    //                     'notification_type' => 'table',
    //                     'description_ar' => 'تم تحديث حالة طاولة بالفرع',
    //                     'description_en' => 'Table status updated in your branch',
    //                     'title_ar' => 'تم تحديث حالة طاولة',
    //                     'title_en' => 'Table status updated',
    //                     'created_by' => $employee->id,
    //                     'order_id' => $order->id
    //                 ];
    //                 runNotificationToEmployees($table->branch_id, $notifyData, $employee->id, $table->id, $lang);
    //                 broadcast(new TableStatus($data, $table->branch_id, 'update'));
    //             }

    //             // Delete unpaid transactions
    //             $order->orderTransactions()->where('payment_status', 'unpaid')->delete();

    //             if ($order->type == 'dine-in' || $order->type == 'Takeaway'|| $order->type == 'Delivery') {
    //                 if ($cash_amount != 0) {
    //                     $this->orderService->storePaymentTransaction(
    //                         $order->id,
    //                         $order->type,
    //                         'cash',
    //                         $created_by,
    //                         $coupon,
    //                         $cash_amount,
    //                         $orderDeposit,
    //                         $makeType,
    //                         'paid',
    //                         0,
    //                         null,
    //                         $invoiceId
    //                     );
    //                 }
    //                 if ($credit_amount != 0) {
    //                     $reference_number = $request->reference_number;
    //                     $this->orderService->storePaymentTransaction(
    //                         $order->id,
    //                         $order->type,
    //                         'credit',
    //                         $created_by,
    //                         $coupon,
    //                         $credit_amount,
    //                         $orderDeposit,
    //                         $makeType,
    //                         'paid',
    //                         0,
    //                         $reference_number,
    //                         $invoiceId
    //                     );
    //                 }
    //             } else {
    //                 $this->orderService->storePaymentTransaction(
    //                     $order->id,
    //                     $order->type,
    //                     'cash',
    //                     $created_by,
    //                     $coupon,
    //                     $order->total_price_after_tax,
    //                     $orderDeposit,
    //                     $makeType,
    //                     'paid',
    //                     0,
    //                     null,
    //                     $invoice->id
    //                 );
    //             }

    //             // Notify: Invoice paid
    //             $notifyData = [
    //                 'notification_type' => 'invoice',
    //                 'title_ar' => 'تم دفع الفاتورة' . $order->order_number,
    //                 'title_en' => 'Invoice paid' . $order->order_number,
    //                 'description_ar' => 'تم دفع فاتورة الطلب رقم ' . $order->order_numbr . ' بواسطة الكاشير ' . $employee->first_name . ' ' . $employee->last_name . '.',
    //                 'description_en' => 'Order invoice #' . $order->order_numbr  . ' has been paid by cashier ' . $employee->first_name . ' ' . $employee->last_name . '.',
    //                 'created_by' => $employee->id,
    //                 'order_id' => $orderId
    //             ];
    //             runNotificationToEmployees($order->branch_id, $notifyData, $employee->id, $orderId, $lang);
    //         }

    //         DB::commit();

    //         $updatedOrder = Order::where('branch_id', $employee->branch_id)
    //             ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'orderTransactions'])
    //             ->find($orderId);


    //         $tiparray = [
    //             'order_id' => $orderId,
    //             'invoice_id' => $invoiceId,
    //             'request' => $request,
    //         ];
    //         $tips =  $this->tipService->update($tiparray);
    //         //    return $tips;




    //         // $responseData = $this->prepareInvoiceDetails($updatedOrder, $lang);
    //         return ResponseWithSuccessData($lang, $updatedOrder, 1);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return respondError($e->getMessage(), 500);
    //     }
    // }

    public function updateOrderInvoice(Request $request, $orderId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();
        $cashier_machine_id = EmployeeMachine::where('employee_id', $employee->id)
            ->orderby('id', 'desc')
            ->first()->cashier_machine_id ?? null;
        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $validator = Validator::make($request->all(), [
            'order_status' => 'nullable|in:on_way,delivered',
            'payment_status' => 'nullable|in:paid',
            'cashier_machine_id' => 'nullable|exists:cashier_machines,id,deleted_at,NULL',
            'cash_amount' => [
                'required_if:payment_status,paid',
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request, $lang) {
                    if ($value !== null && (!$request->has('payment_status') || $request->payment_status !== 'paid')) {
                        $fail($lang == 'en' ? 'You cannot enter cash amount unless payment status is updated to paid.' : 'لا يمكن إدخال مبلغ نقدي إلا إذا تم تحديث حالة الدفع إلى مدفوع.');
                    }
                },
            ],
            'credit_amount' => [
                'required_if:payment_status,paid',
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request, $lang) {
                    if ($value !== null && (!$request->has('payment_status') || $request->payment_status !== 'paid')) {
                        $fail($lang == 'en' ? 'You cannot enter credit amount unless payment status is updated to paid.' : 'لا يمكن إدخال مبلغ ائتماني إلا إذا تم تحديث حالة الدفع إلى مدفوع.');
                    }
                },
            ],
            'reference_number' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request, $lang) {
                    $creditAmount = $request->input('credit_amount');
                    if ($creditAmount !== null && floatval($creditAmount) != 0 && (is_null($value) || $value === '')) {
                        $fail($lang == 'en' ? 'Reference number is required when there is a credit amount.' : 'رقم المرجع مطلوب عند وجود مبلغ ائتماني.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $order = Order::where('branch_id', $employee->branch_id)
            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'orderTransactions'])
            ->find($orderId);

        if (!$order) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.',
                'errorData' => ['error' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.'],
                'data' => null
            ], 200);
        }

        DB::beginTransaction();
        try {
            // if ($request->has('cashier_machine_id')) {
            $order->update([
                'cashier_machine_id' => $request->cashier_machine_id ?? $cashier_machine_id,
            ]);
            // }

            if ($request->has('order_status') && $order->type !== 'Delivery') {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'message' => $lang == 'en' ? 'Order status can only be updated for Delivery orders.' : 'يمكن تحديث حالة الطلب فقط للطلبات التوصيل.',
                    'errorData' => ['error' => $lang == 'en' ? 'Order status can only be updated for Delivery orders.' : 'يمكن تحديث حالة الطلب فقط للطلبات التوصيل.'],
                    'data' => null
                ], 200);
            }
            if ($request->has('order_status')) {
                $order->tracking()->create([
                    'order_id' => $order->id,
                    'order_status' => $request->order_status,
                    'created_by' => $employee->id,
                ]);
                // Notify: Order status updated
                $notifyData = [
                    'notification_type' => 'order',
                    'title_ar' => 'تم تحديث حالة الطلب',
                    'title_en' => 'Order status updated',
                    'description_ar' => 'تم تحديث حالة الطلب رقم ' . $orderId . ' إلى ' . $request->order_status . ' بواسطة الكاشير ' . $employee->first_name . ' ' . $employee->last_name . '.',
                    'description_en' => 'Order #' . $orderId . ' status updated to ' . $request->order_status . ' by cashier ' . $employee->first_name . ' ' . $employee->last_name . '.',
                    'created_by' => $employee->id,
                    'order_id' => $orderId
                ];
                runNotificationToEmployees($order->branch_id, $notifyData, $employee->id, $orderId, $lang);
            }

            $branchId = $employee->branch_id;
            $created_by = $employee->id;
            $makeType = 'cashier';
            $orderDeposit = getBranchSettings($branchId, 'order_reservation_deposit');
            $coupon = $order->coupon_id;

            // تحديث invoice و order tables إذا تم إرسال بيانات الكوبون
            if ($request->has('coupon_value') && $request->coupon_value > 0) {
                $invoice = Invoice::where('order_id', $orderId)->first();
                if ($invoice) {
                    // الحصول على coupon_id من coupon_code إذا كان موجوداً
                    $couponId = null;
                    if ($request->has('coupon_code') && !empty($request->coupon_code)) {
                        $coupon = Coupon::where('code', $request->coupon_code)->first();
                        if ($coupon) {
                            $couponId = $coupon->id;
                        }
                    }

                    // الحصول على البيانات الأساسية من الـ invoice والـ order
                    $subtotalBeforeCoupon = (float)($invoice->total_before_coupon ?? $invoice->total_before_tax ?? 0);
                    $discountValue = (float)$request->coupon_value;
                    $subtotalAfterCoupon = max(0, $subtotalBeforeCoupon - $discountValue);

                    // الحصول على إعدادات الخدمة والضريبة من الـ order
                    $servicePerc = (float)($order->service_percentage ?? 0);
                    $serviceFixed = (float)($invoice->service_fees ?? 0);
                    $serviceAmount = $servicePerc > 0
                        ? ($subtotalAfterCoupon * $servicePerc) / 100
                        : $serviceFixed;

                    // حساب الضريبة
                    $taxPerc = (float)($order->tax_percentage ?? 0);
                    $deliveryFees = (float)($order->delivery_fees ?? 0);
                    $amountAfterService = $subtotalAfterCoupon + $serviceAmount;
                    $taxAmount = $taxPerc > 0 ? ($amountAfterService * $taxPerc) / 100 : 0;

                    // الحساب النهائي (يشمل delivery_fees)
                    $finalTotal = $amountAfterService + $taxAmount + $deliveryFees;

                    // تحديث الـ invoice table
                    $invoice->coupon_value = round($discountValue, 2);
                    $invoice->total_after_tax = round($finalTotal, 2);
                    $invoice->total_before_tax = round($subtotalAfterCoupon, 2);
                    $invoice->tax = round($taxAmount, 2);
                    if ($couponId) {
                        $invoice->coupon_id = $couponId; // حفظ coupon_id لاستخدام العلاقة
                    }
                    if ($servicePerc > 0) {
                        $invoice->service_fees = round($serviceAmount, 2);
                    }
                    $invoice->save();
                    $invoice->refresh();

                    // تحديث الـ order table
                    $order->coupon_value = round($discountValue, 2);
                    $order->total_price_after_tax = round($finalTotal, 2);
                    $order->total_price_befor_tax = round($subtotalAfterCoupon, 2);
                    $order->tax_value = round($taxAmount, 2);
                    if ($couponId) {
                        $order->coupon_id = $couponId; // حفظ coupon_id في الـ order أيضاً
                    }
                    if ($servicePerc > 0) {
                        $order->service_fees = round($serviceAmount, 2);
                    }
                    $order->save();
                    $order->refresh();

                    Log::info('✅ Invoice and Order updated with coupon', [
                        'order_id' => $orderId,
                        'invoice_id' => $invoice->id,
                        'coupon_id' => $couponId,
                        'coupon_value' => $discountValue,
                        'new_total_price' => $finalTotal,
                        'subtotal_before' => $subtotalBeforeCoupon,
                        'subtotal_after' => $subtotalAfterCoupon,
                        'tax_amount' => $taxAmount,
                        'service_amount' => $serviceAmount,
                        'delivery_fees' => $deliveryFees
                    ]);
                }
            }

            if ($request->has('payment_status') && $request->payment_status === 'paid') {
                $hasUnpaidTransaction = $order->orderTransactions->contains(function ($transaction) {
                    return $transaction->payment_status == 'unpaid';
                });
                if (!$hasUnpaidTransaction) {
                    DB::rollBack();
                    return RespondWithErrorMsg($lang == 'en' ? 'No unpaid transactions exist for this order.' : 'لا توجد معاملات غير مدفوعة لهذا الطلب.');
                }

                // Update invoice status to paid
                // ملاحظة: لا نستدعي updateInvoice هنا لأنه قد يعيد حساب كل شيء بدون الكوبون
                // البيانات تم تحديثها بالفعل في الكود السابق عند تطبيق الكوبون
                $invoice = Invoice::where('order_id', $orderId)->first();
                $invoiceId = null;
                if ($invoice) {
                    // فقط تحديث status إلى paid بدون إعادة حساب
                    $invoice->update(['status' => 'paid']);
                    $invoiceId = $invoice->id;
                }

                $cash_amount = $request->cash_amount ?? 0;
                $credit_amount = $request->credit_amount ?? 0;

                if ($order->type == 'dine-in' || $order->type == 'Takeaway' || $order->type == 'Delivery') {
                    // حساب المبلغ المدفوع
                    $totalPaid = round($cash_amount + $credit_amount, 2);

                    // تحديد المبلغ المطلوب للدفع
                    // أولوية: استخدام total المرسل من الـ frontend، ثم invoice_summary.total_price، ثم order->total_price_after_tax
                    $taxedTotal = null;
                    $totalSource = '';

                    // 1. استخدام total المرسل من الـ frontend إذا كان موجوداً وصحيحاً
                    // محاولة قراءة total من عدة مصادر محتملة
                    $requestTotal = $request->input('total')
                        ?? $request->input('totalll')
                        ?? ($request->has('total') ? $request->total : null);

                    // Log جميع القيم المستلمة من الـ request
                    Log::info('Request data for payment validation', [
                        'all_request_data' => $request->all(),
                        'total_input' => $request->input('total'),
                        'total_has' => $request->has('total'),
                        'requestTotal' => $requestTotal,
                        'cash_amount' => $cash_amount,
                        'credit_amount' => $credit_amount,
                        'json_body' => $request->getContent()
                    ]);

                    // التحقق من أن القيمة صحيحة - تحسين التحقق
                    if ($requestTotal !== null && $requestTotal !== '' && $requestTotal !== 'null' && $requestTotal !== 'undefined') {
                        $requestTotalFloat = (float)$requestTotal;
                        if (is_numeric($requestTotal) && $requestTotalFloat > 0) {
                            $taxedTotal = round($requestTotalFloat, 2);
                            $totalSource = 'request_total';
                            Log::info('✅ Using request total from frontend', [
                                'total' => $taxedTotal,
                                'original_value' => $requestTotal
                            ]);
                        } else {
                            Log::warning('Request total is not valid numeric', [
                                'requestTotal' => $requestTotal,
                                'type' => gettype($requestTotal)
                            ]);
                        }
                    } else {
                        Log::warning('Request total is null or empty', [
                            'requestTotal' => $requestTotal,
                            'has_total' => $request->has('total'),
                            'input_total' => $request->input('total')
                        ]);
                    }

                    // 2. استخدام invoice_summary.total_price من الـ invoice (فقط إذا لم يتم استخدام request_total)
                    if ($taxedTotal === null && $invoice && $invoice->invoice_summary) {
                        // محاولة قراءة invoice_summary كـ JSON أو array
                        $invoiceSummary = is_string($invoice->invoice_summary)
                            ? json_decode($invoice->invoice_summary, true)
                            : $invoice->invoice_summary;

                        if (is_array($invoiceSummary) && isset($invoiceSummary['total_price']) && (float)$invoiceSummary['total_price'] > 0) {
                            $taxedTotal = round((float)$invoiceSummary['total_price'], 2);
                            $totalSource = 'invoice_summary';
                        }
                    }
                    // 3. استخدام order->total_price_after_tax كقيمة احتياطية (فقط إذا لم يتم العثور على قيمة صحيحة)
                    if ($taxedTotal === null || $taxedTotal <= 0) {
                        $taxedTotal = round((float)$order->total_price_after_tax, 2);
                        $totalSource = 'order_total_price_after_tax';
                        Log::warning('⚠️ Using order total_price_after_tax as fallback', [
                            'taxedTotal' => $taxedTotal,
                            'requestTotal' => $requestTotal,
                            'request_has_total' => $request->has('total'),
                            'request_input_total' => $request->input('total')
                        ]);
                    }

                    // Log للتحقق من القيم المستخدمة
                    Log::info('Payment validation', [
                        'order_id' => $orderId,
                        'total_paid' => $totalPaid,
                        'taxed_total' => $taxedTotal,
                        'total_source' => $totalSource,
                        'request_total' => $requestTotal,
                        'cash_amount' => $cash_amount,
                        'credit_amount' => $credit_amount,
                        'order_total_price_after_tax' => $order->total_price_after_tax
                    ]);

                    // التحقق من أن المبلغ المدفوع لا يقل عن المبلغ المطلوب
                    // استخدام tolerance صغير (0.01) للتعامل مع مشاكل التقريب
                    $tolerance = 0.01;
                    if ($totalPaid < ($taxedTotal - $tolerance)) {
                        DB::rollBack();
                        Log::error('Payment amount validation failed', [
                            'order_id' => $orderId,
                            'total_paid' => $totalPaid,
                            'taxed_total' => $taxedTotal,
                            'difference' => $taxedTotal - $totalPaid
                        ]);
                        return RespondWithErrorMsg(__('order.amount_wrong'));
                    }
                }

                // Only update statuses if the amount is correct
                $order->update([
                    'status' => 'completed',
                    'print_status' => 'done',
                    'cashier_id' => $employee->id,
                    'modify_by' => $employee->id,
                ]);

                $order->tracking()->create([
                    'order_id' => $order->id,
                    'order_status' => 'completed',
                    'created_by' => $employee->id,
                ]);

                // Notify: Order status completed
                $notifyData = [
                    'notification_type' => 'order',
                    'title_ar' => 'تم تحديث حالة الطلب',
                    'title_en' => 'Order status updated',
                    'description_ar' => 'تم تحديث حالة الطلب رقم ' . $orderId . ' إلى مكتمل بواسطة الكاشير ' . $employee->first_name . ' ' . $employee->last_name . '.',
                    'description_en' => 'Order #' . $orderId . ' status updated to completed by cashier ' . $employee->first_name . ' ' . $employee->last_name . '.',
                    'created_by' => $employee->id,
                    'order_id' => $orderId
                ];
                runNotificationToEmployees($order->branch_id, $notifyData, $employee->id, $orderId, $lang);

                if ($order->type == 'dine-in' && $order->table) {
                    $order->Table()->update([
                        'status' => 1,
                        'modified_by' => $employee->id,
                    ]);

                    // Prepare table data for notification and broadcast
                    $table = $order->table->load(['floors', 'floorPartitions']);
                    $data = [
                        'id' => $table->id,
                        'name' => $table->name,
                        'name_ar' => $table->name_ar,
                        'name_en' => $table->name_en,
                        'table_number' => $table->table_number,
                        'status' => $table->status,
                        'smoking' => $table->smoking,
                        'floors' => [
                            'id' => $table->floors->id ?? null,
                            'name' => $table->floors->name ?? null,
                            'name_ar' => $table->floors->name_ar ?? null,
                            'name_en' => $table->floors->name_en ?? null
                        ],
                        'floor_partitions' => [
                            'id' => $table->floorPartitions->id ?? null,
                            'name' => $table->floorPartitions->name ?? null,
                            'name_ar' => $table->floorPartitions->name_ar ?? null,
                            'name_en' => $table->floorPartitions->name_en ?? null
                        ]
                    ];
                    // Notify: Table status updated
                    $notifyData = [
                        'notification_type' => 'table',
                        'description_ar' => 'تم تحديث حالة طاولة بالفرع',
                        'description_en' => 'Table status updated in your branch',
                        'title_ar' => 'تم تحديث حالة طاولة',
                        'title_en' => 'Table status updated',
                        'created_by' => $employee->id,
                        'order_id' => $order->id
                    ];
                    runNotificationToEmployees($table->branch_id, $notifyData, $employee->id, $table->id, $lang);
                    broadcast(new TableStatus($data, $table->branch_id, 'update'));
                }

                // Delete unpaid transactions
                $order->orderTransactions()->where('payment_status', 'unpaid')->delete();

                if ($order->type == 'dine-in' || $order->type == 'Takeaway' || $order->type == 'Delivery') {
                    if ($cash_amount != 0) {
                        $this->orderService->storePaymentTransaction(
                            $order->id,
                            $order->type,
                            'cash',
                            $created_by,
                            $coupon,
                            $cash_amount,
                            $orderDeposit,
                            $makeType,
                            'paid',
                            0,
                            null,
                            $invoiceId
                        );
                    }
                    if ($credit_amount != 0) {
                        $reference_number = $request->reference_number;
                        $this->orderService->storePaymentTransaction(
                            $order->id,
                            $order->type,
                            'credit',
                            $created_by,
                            $coupon,
                            $credit_amount,
                            $orderDeposit,
                            $makeType,
                            'paid',
                            0,
                            $reference_number,
                            $invoiceId
                        );
                    }
                } else {
                    $this->orderService->storePaymentTransaction(
                        $order->id,
                        $order->type,
                        'cash',
                        $created_by,
                        $coupon,
                        $order->total_price_after_tax,
                        $orderDeposit,
                        $makeType,
                        'paid',
                        0,
                        null,
                        $invoice->id
                    );
                }

                // Notify: Invoice paid
                $notifyData = [
                    'notification_type' => 'invoice',
                    'title_ar' => 'تم دفع الفاتورة' . $order->order_number,
                    'title_en' => 'Invoice paid' . $order->order_number,
                    'description_ar' => 'تم دفع فاتورة الطلب رقم ' . $order->order_numbr . ' بواسطة الكاشير ' . $employee->first_name . ' ' . $employee->last_name . '.',
                    'description_en' => 'Order invoice #' . $order->order_numbr  . ' has been paid by cashier ' . $employee->first_name . ' ' . $employee->last_name . '.',
                    'created_by' => $employee->id,
                    'order_id' => $orderId
                ];
                runNotificationToEmployees($order->branch_id, $notifyData, $employee->id, $orderId, $lang);
            }

            DB::commit();

            $updatedOrder = Order::where('branch_id', $employee->branch_id)
                ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'orderTransactions'])
                ->find($orderId);

            $tiparray = [
                'order_id' => $orderId,
                'invoice_id' => $invoiceId,
                'request' => $request,
            ];
            $tips =  $this->tipService->update($tiparray);
            //    return $tips;

            // الحصول على الـ Invoice المحدث وإرجاع البيانات بشكل صحيح
            $updatedInvoice = Invoice::where('order_id', $orderId)->first();
            if ($updatedInvoice) {
                // إعادة تحميل الـ invoice مع العلاقات المطلوبة (بعد التحديث)
                $updatedInvoice->refresh();
                $updatedInvoice->load(['orders.branch', 'orders.table', 'orders.address', 'orders.Client', 'invoiceDetails.orderDetail.dish', 'orderTransactions']);
                $responseData = $this->getInvoiceDetailsData($updatedInvoice, $employee, $lang);
                return ResponseWithSuccessData($lang, $responseData, 1);
            }

            // Fallback: إرجاع Order إذا لم يتم العثور على Invoice
            return ResponseWithSuccessData($lang, $updatedOrder, 1);
        } catch (\Exception $e) {
            DB::rollBack();
            return respondError($e->getMessage(), 500);
        }
    }
    public function printInvoice(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $user = auth('employee')->user();

            if ((!$user) || ($user->flag != 'cashier')) {
                return RespondWithBadRequest($lang, 4);
            }

            // cashier's info
            $today = Carbon::now()->format('Y-m-d');
            $shift = $this->timeTableService->getTimetableForDate($user->id, $today);
            if (!($shift['status'] == false)) {
                $shiftType = $lang == 'en' ? $shift['data']['timetable']['name_en'] : $shift['data']['timetable']['name_ar'];
                $user->shift_start = $shift['data']['on_duty_time'];
                $user->shift_end = $shift['data']['off_duty_time'];
                $user->shift_type = $shiftType;
                $user->employee_schedule_id = $shift['data']['employee_schedule_id'];
                $user->cross_day = $shift['data']['cross_day'];
            } else {
                $user->shift_start = null;
                $user->shift_end = null;
                $user->shift_type = null;
                $user->employee_schedule_id = null;
                $user->cross_day = null;
            }
            //            dd($user);

            $request->merge([
                'employee_schedule_id' => $user->employee_schedule_id,
                'shift_start' => $user->shift_start,
                'shift_end' => $user->shift_end,
            ]);
            //            dd($request->all());

            App::setLocale($lang);
            $validator = Validator::make($request->all(), [
                "order_id" => "required|exists:orders,id,deleted_at,NULL",
                "cashier_machine_id" => "required|exists:cashier_machines,id,deleted_at,NULL",
                "payment_method" => "required|in:cash,credit",
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $order = Order::find($request->order_id);
            $order_id = $order->id;
            $not_delivery = $order->type != 'Delivery';
            //            $takeaway = $order->type == 'Takeaway';
            //            completed = $order->status == 'completed';
            $order_tracking = OrderTracking::where('order_id', $order->id)->orderby('created_at', 'desc')->orderby('id', 'desc')->first();
            $order_transaction = OrderTransaction::where('order_id', $order->id)->orderby('created_at', 'desc')->orderby('id', 'desc')->first();
            $order_details = OrderDetail::where('order_id', $order->id)->get();
            $order_table = Table::find($order->table_id) ?? false;
            $waiter_request = WaiterRequest::where('type', 2)
                ->whereJsonContains('order_ids', $order_id)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            //pending for now
            // if ($waiter_request) {
            //     if ($waiter_request->status != 1) {
            //         if ($waiter_request->status == 0) {
            //             return respondError(
            //                 $lang == 'en' ? "Can't print! print request is pending from branch manager."
            //                     : 'لا يمكن الطباعة! طلب الطباعة معلق من قبل مدير الفرع.',
            //                 400,
            //                 $lang == 'en' ? ["Can't print! print request is pending from branch manager."]
            //                     : ['لا يمكن الطباعة! طلب الطباعة معلق من قبل مدير الفرع.']
            //             );
            //         }
            //         if ($waiter_request->status == 2) {
            //             return respondError(
            //                 $lang == 'en' ? "Can't print! print request is rejected from branch manager."
            //                     : 'لا يمكن الطباعة! طلب الطباعة مرفوض من قبل مدير الفرع.',
            //                 400,
            //                 $lang == 'en' ? ["Can't print! print request is rejected from branch manager."]
            //                     : ['لا يمكن الطباعة! طلب الطباعة مرفوض من قبل مدير الفرع.']
            //             );
            //         }
            //     }
            // }

            //            if ($order->type == 'Takeaway') {
            //                $policy_type = 'takeaway';
            //            } elseif ($order->type == 'dine-in') {
            //                $policy_type = 'dine-in';
            //            } elseif ($order->type == 'reservation-table') {
            //                $policy_type = 'reservation_with_order';
            //            } else {
            //                $policy_type = 'delivery';
            //            }
            //
            //            $policy = PaymentPolicies::where('branch_id', $order->branch_id)
            //                ->where('order_type', $policy_type)
            //                ->first();
            //            if (!$policy) {
            //                return RespondWithBadRequest($lang, 2);
            //            }
            //            $policyCount = PaymentPolicyInvoiceCount::where('payment_policy_id', $policy->id)->first();
            //            if (!$policyCount) {
            //                return RespondWithBadRequest($lang, 2);
            //            }
            //
            //            if ($order->print_count_cashier == $policyCount->invoice_count) {
            //                return respondError(
            //                    $lang == 'en' ? "Can't print! maximum allowed printed invoices reached for this order type." :
            //                        'لا يمكن الطباعة! تم الوصول إلى الحد الأقصى المسموح به لعدد الفواتير المطبوعة لهذا النوع من الطلبات.',
            //                    400,
            //                    $lang == 'en' ? ["Can't print! maximum allowed printed invoices reached for this order type."] :
            //                        ['لا يمكن الطباعة! تم الوصول إلى الحد الأقصى المسموح به لعدد الفواتير المطبوعة لهذا النوع من الطلبات.']
            //                );
            //            }

            DB::beginTransaction();
            if ($order_tracking && $order_transaction) {
                $hasPaidTransaction = OrderTransaction::where('order_id', $request->order_id)
                    ->where('payment_status', 'paid')
                    ->where('is_refund', 0)
                    ->exists();
                // if ($order_transaction->payment_status == 'paid' && $order_tracking->order_status == 'readyForPickup') {
                if ($hasPaidTransaction && $order->status != 'cancelled') {

                    $order->status = 'completed';
                    $order->print_status = 'done';
                    $order->save();

                    foreach ($order_details as $order_detail) {
                        if ($order_detail->status != 'cancel') {
                            $order_detail->status = 'completed';
                            $order_detail->save();
                        }
                    }

                    $order_tracking->order_status = 'completed';
                    $order_tracking->save();

                    //                $order_transaction->payment_status = 'paid';

                    // if ($order->type == 'dine-in') {
                    //     $order_transaction->payment_method = $request->payment_method;
                    // }

                    $order_transaction->save();

                    if ($order_table) {
                        $order_table->status = 1;
                        $order_table->save();
                    }

                    $notifyData = [
                        'notification_type' => 'invoice',
                        'title_ar' => 'تمت طباعة الفاتورة' . $order->order_number,
                        'title_en' => 'Invoice Printed' . $order->order_number,
                        'description_ar' => 'تم طباعة فاتورة جديدة للطلب رقم ' . $order->order_number . ' بواسطة الكاشير ' . $user->first_name . ' ' . $user->last_name . '.',
                        'description_en' => 'A new invoice for order #' . $order->order_number . ' has been printed by cashier ' . $user->first_name . ' ' . $user->last_name . '.',
                        'created_by' => $user->id,
                        'order_id' => $order_id
                    ];
                    runNotificationToEmployees($order->branch_id, $notifyData, $user->id, $order_id, $lang);
                } else {
                    if ($order->status == 'cancelled') {

                        $order->print_status = 'cancelled';
                        $order->save();
                    }

                    $notifyData = [
                        'notification_type' => 'invoice',
                        'title_ar' => 'تمت طباعة الفاتورة' . $order->order_number,
                        'title_en' => 'Invoice Printed' . $order->order_number,
                        'description_ar' => 'تم طباعة فاتورة جديدة للطلب رقم ' . $order->order_number . ' بواسطة الكاشير ' . $user->first_name . ' ' . $user->last_name . '.',
                        'description_en' => 'A new invoice for order #' . $order->order_number . ' has been printed by cashier ' . $user->first_name . ' ' . $user->last_name . '.',
                        'created_by' => $user->id,
                        'order_id' => $order_id
                    ];
                    runNotificationToEmployees($order->branch_id, $notifyData, $user->id, $order_id, $lang);
                }

                $order->cashier_id = $user->id;
                $order->cashier_machine_id = $request->cashier_machine_id;
                $order->print_count_cashier += 1;
                $order->save();

                $cashierBalanceController = app(CashierBalanceController::class);
                $response = $cashierBalanceController->getCurrentBalance($request);

                if ($response->original['status']) {
                    broadcast(new TotalPaid($user->id, $response->original['data']));
                }

                DB::commit();

                $order->printed_by = $user->first_name . ' ' . $user->last_name;

                return ResponseWithSuccessData($lang, $order, 1);
            }
            // hashed and changed from the edit invoice
            //            elseif (!$not_delivery) { // in case of delivery orders
            //                $order->print_status = 'done';
            //                $order->save();
            //
            //                //                if (!$order->save()) {
            //                //                    dd($order->toArray());
            //                //                }
            //
            //                DB::commit();
            //                return ResponseWithSuccessData($lang, $order->fresh(), 1);
            //            }
            else {
                DB::rollBack();
                if ($lang == 'en') {
                    $message = "Order not found.";
                } else {
                    $message = "الطلب غير موجود.";
                }
                return CustomRespondWithBadRequest($message);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return respondError($e->getMessage(), 400);
        }
    }
    public  function requestChangeStatus(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        return $order = $this->invoiceService->changeRequestStatus($request);
    }
}
