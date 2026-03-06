<?php


namespace App\Services\ReportServices;

use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use App\Services\HR_Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class OrdersReportsService
{
    private $lang;
    protected $employee;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
        $this->employee = auth('employee')->user();
    }

    public function listOrders($checkType)
    {
        $lang = app()->getLocale();

        $query = Order::with(['Branch', 'cancellationReasons', 'orderDetails', 'orderAddons', 'orderTransactions', 'tracking', 'client', 'branch', 'Table'])->orderBy('id', 'desc');
        if ($checkType === 'admin') {
            if (auth('admin')->user()->hasRole('Branch Manager')) {
                $branch_id = getBranchManagerID();
                if ($branch_id) {
                    $query->where('branch_id', $branch_id);
                }
            }
            $orders = $query->limit(300)->get();
            foreach ($orders as $order) {
                $order['details'] = OrderDetail::where('order_id', $order->id)->get();
                $order['addons'] = OrderAddon::where('order_id', $order->id)->get();
                $order['transaction'] = OrderTransaction::where('order_id', $order->id)->first();
                $order_tracking = OrderTracking::where('order_id', $order->id)->orderby('id', 'desc')->first();
                $order['last_status'] = $order_tracking ? $order_tracking->order_status : null;

                $order['source'] = $this->determineOrderSource($order);

                // Add cancellation reason data if the order is cancelled
                if ($order['last_status'] === 'cancelled') {
                    $order['cancellation_reason'] = $order->cancellationReasons->first();
                }
            }
        } elseif ($checkType === 'employee') {
            if (auth('employee')->user()->hasRole('Branch_Manager')) {
                $branch_id = getBranchManagerID();
                if ($branch_id) {
                    $query->where('branch_id', $branch_id);
                }
            }
            $orders = $query;
        }
        return $orders;
    }

    public function orderDetails($id)
    {
        // $order = Order::with([
        //     'orderDetails.dish',
        //     'orderDetails.dishSize' => fn($q) => $q->where('is_active', 1)
        //                                         ->where('branch_id', $this->branch_id)
        //                                         ->whereNull('deleted_at')
        //                                         ->with([
        //                                             'dishSizes' => fn($s) => $s->whereNull('deleted_at'),
        //                                         ]),
        //     'address',
        //     'orderAddons',
        //     'orderAddons.Addon.addons',
        //     'orderTransactions',
        //     'cancellationReasons.reasonModel',
        //     'tracking',
        //     'client',
        //     'branch',
        //     'Table'
        // ])->findOrFail($id);

        $order = Order::with(['branch'])->findOrFail($id);
        $branchId = $order->branch_id;
        $order->load([
            'orderDetails.dish',
            'orderDetails.dishSize',
            'address',
            'orderAddons',
            'orderAddons.Addon.addons',
            'orderTransactions',
            'cancellationReasons.reasonModel',
            'tracking',
            'client',
            'branch',
            'Table'
        ]);

        $order->orderDetails->each(function ($detail) {
            $detail->makeHidden(['dishSize']);
        });


        // $order->map(function($orderDetail){
        //     collect($orderDetail->order_addons)->map(function($addon){
        //         $addon->addon_name = $addon->addon->addons->name;
        //         return $addon;
        //     });
        //     return $orderDetail;
        // });
        $order->makeHidden(['orderAddons', 'cancellationReasons']);
        $order->order_addons = collect($order->orderAddons)->map(function ($addon) {
            $addon->addon_name = optional($addon->Addon->addons)->name ?? null;

            $addon->unsetRelation('Addon');
            $addon->unsetRelation('addon');

            if ($addon->relationLoaded('Addon')) {
                $addon->Addon->unsetRelation('addons');
            } elseif ($addon->relationLoaded('addon')) {
                $addon->addon->unsetRelation('addons');
            }

            return $addon;
        });



        // $order->cancellation_reasons = collect($order->cancellationReasons)->map(function ($reason) {
        //     $reason->reason_name = optional($reason->reasonModel)->reason ?? null;
        //     return $reason;
        // });
        $order['transaction'] = OrderTransaction::first();
        $order['reason'] = $order->cancellationReasons?->first()->reason ?? null;
        $order['reason_name'] = $order->cancellationReasons?->first()->reasonModel->reason ?? null;
        $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';
        $order['currency_symbol'] = $currencySymbol;
        // $order->makeHidden(['cancellationReasons', 'orderAddons']);
        //        $order['delivery'] = Order::first();
        return $order;
    }

    public function orderDetailsdashboard(Request $request, $orderId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $orders = Order::where('branch_id', $this->employee->branch_id)->where('id', $orderId)
            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'orderTransactions'])->get();

        $responseData = $orders->map(function ($order) use ($lang) {
            $orderDetails = $order->orderDetails->map(function ($detail) use ($lang, $order) {
                // Include cancelled addons if order is cancelled or if the dish itself is cancelled
                $includeCancelledAddons = $order->status === 'cancelled' || $detail->status === 'cancel';

                $addons = $detail->dishAddons
                    ->filter(function ($addon) use ($lang, $includeCancelledAddons) {
                        $hasValidName = $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')};

                        if ($includeCancelledAddons) {
                            return $hasValidName;
                        } else {
                            return $addon->status !== 'cancel' && $hasValidName;
                        }
                    });

                if ($order->tax_application == 0) {
                    $orderDetailTotal = $detail->price_befor_tax;
                    $addonsTotal = $addons->sum(function ($addon) {
                        return $addon->price_before_tax;
                    });
                } else {
                    $orderDetailTotal = $detail->price_after_tax;
                    $addonsTotal = $addons->sum(function ($addon) {
                        return $addon->price_after_tax;
                    });
                }
                $total = $orderDetailTotal + $addonsTotal;

                if ($order->tax_application == 0) {
                    $orderDetailTotal = $detail->price_before_coupon;
                    $addonsTotal = $addons->sum(function ($addon) {
                        return $addon->price_before_coupon;
                    });
                } else {
                    $orderDetailTotal = $detail->price_after_tax;
                    $addonsTotal = $addons->sum(function ($addon) {
                        return $addon->price_after_tax;
                    });
                }
                $totalBeforeCoupon = $orderDetailTotal + $addonsTotal;

                // Check if dish has coupon_id - only apply coupon if it exists
                $dishCouponId = $detail->coupon_id ?? null;
                $dishCouponValue = $dishCouponId ? ($detail->coupon_value ?? 0) : 0;

                return [
                    'order_detail_id' => $detail->id,
                    'dish_id' => $detail->dish_id,
                    'dish_name' => $detail->dish->name ?? null,
                    'dish_image' => $detail->dish->image ?? null,
                    'size' => $detail->dish_size_id ?
                        (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                        : null,
                    'quantity' => $detail->quantity,
                    'total_dish_price' => formatFloat($totalBeforeCoupon),
                    'total_dish_price_coupon_applied' => formatFloat($total),
                    'note' => $detail->note ?? null,
                    'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                        return ($lang === 'ar') ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null;
                    }),
                    'coupon_id' => $dishCouponId,
                    'coupon_value' => formatFloat($dishCouponValue),
                    'coupon_title' => $dishCouponId ? $detail->coupon?->title : null
                ];
            });

            $subtotal = $order->total_price_befor_tax;
            if ($order->tax_application == 1) {
                $subtotal += $order->tax_value;
            }

            $couponId = $order->coupon_id;
            $couponType = $order->coupon?->type;
            $couponValue = $order->coupon_value;
            $couponTitle = $order->coupon?->title;

            // If order doesn't have a coupon, check order details for coupon
            if (!$couponId) {
                $orderDetailsWithCoupons = $order->orderDetails->whereNotNull('coupon_id')->where('status', '!=', 'cancel');
                if ($orderDetailsWithCoupons->isNotEmpty()) {
                    $firstOrderDetailWithCoupon = $orderDetailsWithCoupons->first();
                    $couponId = $firstOrderDetailWithCoupon->coupon_id;
                    $couponType = $firstOrderDetailWithCoupon->coupon?->type;
                    $couponTitle = $firstOrderDetailWithCoupon->coupon?->title;
                    $couponValue = $orderDetailsWithCoupons->sum('coupon_value');
                }
            }

            $orderSummary = [
                'order_number' => $order->order_number,
                'subtotal_price' => formatPrice($subtotal),
                'subtotal_price_before_coupon' => formatFloat($order->total_price_before_coupon),
                'total_price' => formatFloat($order->total_price_after_tax),
                'delivery_fees' => formatFloat($order->delivery_fees ?? null),
                'coupon_id' => $couponId ?? null,
                'coupon_type' => $couponType ?? null,
                'coupon_value' => formatFloat($couponValue ?? null),
                'coupon_title' => $couponTitle ?? null,
                'service_fees' => formatFloat($order->service_fees ?? null),
                'service_percentage' => formatFloat($order->service_percentage ?? null),
                'tax_value' => formatFloat($order->tax_value ?? null),
                'tax_apply' => !empty($order->tax_value) || $order->tax_value != 0,
                'tax_application' => $order->tax_application == 1 ? true : false,
                'tax_percentage' => formatFloat($order->tax_percentage ?? null),
                'order_notes' => $order->note ?? null,
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : ($order->address ? $order->address->address_phone : $order->client_phone),
                'client_email' => $order->Client->flag != 'unknown' ? $order->Client?->email : null,
                'make_type' => $order->make_type ?? null
            ];

            if ($order->type == 'Delivery') {
                $deliveryData = [
                    'client_address_phone' => $order->address ? $order->address->address_phone : null,
                    'client_address' => $order->address->address,
                    'delivery_id' => $order->delivery?->id ?? null,
                    'delivery_name' => $order->delivery?->first_name . ' ' . $order->delivery?->last_name ?? null,
                ];
            } else {
                $deliveryData = null;
            }

            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

            // Process transactions based on is_refund status
            $refundTransactions = $order->orderTransactions->where('is_refund', 1);
            $normalTransactions = $order->orderTransactions->where('is_refund', 0);

            $processedTransactions = [];

            // Handle normal transactions (is_refund = 0) - merge cash together and credit together separately
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
                        'paid' => round($totalCashPaid, 2),
                        'refund' => round($totalCashRefund, 2),
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
                        'paid' => round($totalCreditPaid, 2),
                        'refund' => round($totalCreditRefund, 2),
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
                        'paid' => round($transaction->paid, 2),
                        'refund' => round($transaction->refund, 2),
                        'date' => $transaction->date,
                        'is_refund' => $transaction->is_refund,
                    ];
                }
            }

            // Handle refund transactions (is_refund = 1)
            if ($refundTransactions->isNotEmpty()) {
                $firstRefundTransaction = $refundTransactions->first();
                $totalRefund = $refundTransactions->sum('refund');

                $processedTransactions[] = [
                    'payment_status' => $firstRefundTransaction->payment_status,
                    'payment_method' => $firstRefundTransaction->payment_method,
                    'paid' => round($firstRefundTransaction->paid, 2),
                    'refund' => round($totalRefund, 2),
                    'date' => $firstRefundTransaction->date,
                    'is_refund' => 1,
                ];
            }

            $transactions = $processedTransactions;

            return [
                'order_type' => $order->type,
                'table' => $order->type,
                'order_status' => $order->status,
                'tracking_status' => $order->tracking->last()->order_status ?? null,
                'created_at' => $order->created_at,
                'transactions' => $transactions,
                'order_details' => $orderDetails,
                'order_summary' => $orderSummary,
                'delivery_data' => $deliveryData,
                'currency_symbol' => $currencySymbol,
            ];
        });

        $response = [
            'orderDetails' => $responseData,
        ];

        return $response;
    }
    protected function determineOrderSource($order)
    {
        if ($order->waiter_id) {
            return 'waiter';
        } elseif ($order->cashier_id) {
            return 'cashier';
        } elseif ($order->client_id && optional($order->client)->flag !== 'unknown') {
            return 'online';
        }
        return 'unknown';
    }
}
