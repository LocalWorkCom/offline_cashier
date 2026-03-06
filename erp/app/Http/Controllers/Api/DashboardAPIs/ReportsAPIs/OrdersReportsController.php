<?php

namespace App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use App\Services\ReportServices\OrdersReportsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrdersReportsController extends Controller
{
    protected $ordersReportsService;


    public function __construct(OrdersReportsService $ordersReportsService)
    {
        $this->ordersReportsService = $ordersReportsService;
    }

    public function list(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $status = $request->query('status');
        $type = $request->query('type');
        $branch_id = $request->query('branch_id');
        $date_from = $request->query('date_from');
        $date_to = $request->query('date_to');
        $make_type = $request->query('make_type');
        $data = $this->ordersReportsService->listOrders('employee');

        if ($status) {
            $data = $data->where('status', $status);
        }
        if ($type) {
            $data = $data->where('type', $type);
        }
        if ($branch_id) {
            $data = $data->where('branch_id', $branch_id);
        }
        if ($date_from) {
            $data = $data->whereDate('created_at', '>=', $date_from);
        }
        if ($date_to) {
            $data = $data->whereDate('created_at', '<=', $date_to);
        }
        if ($make_type) {
            $data = $data->where('make_type', $make_type);
        }

        // Calculate totals before pagination
        $totalOrders = $data->count();
        // $data_order_calculate = $data->where('status', '!=', 'cancelled')->get();
        $data_order_calculate = $data->get();
        $totalPriceAfterTax = $data_order_calculate->sum('total_price_after_tax');
        $totalPriceBeforeTax = $data_order_calculate->sum('total_price_befor_tax');

        // Calculate total cash paid for orders with payment_status = 'paid' and payment method = 'cash'
        $totalBeforeTax = $data_order_calculate->sum('total_price_befor_tax');
        $totalAfterTax = $data_order_calculate->sum('total_price_after_tax');
        $totalServiceFees = $data_order_calculate->sum('service_fees');
        $totalCoupon = $data_order_calculate->sum('coupon_value');

        $totalCash = 0;
        $totalCredit = 0;

        foreach ($data_order_calculate as $order) {
            $transaction = $order->orderTransactions->first();

            // Add null check for transaction
            if ($transaction && (strtolower($transaction->payment_status) == 'paid' || strtolower($transaction->payment_status) == 'part')) {
                if ($transaction->payment_method === 'cash') {
                    $totalCash += floatval($transaction->paid);
                }
                if ($transaction->payment_method == 'credit_with_delivery' || $transaction->payment_method == 'credit') {
                    $totalCredit += floatval($transaction->paid);
                }
            }
        }

        // Get paginated data first
        $paginatedData = paginateOrGetAll($data, $request, [], []);

        // Process the paginated results
        foreach ($paginatedData['data'] as $order) {
            $order['details'] = OrderDetail::where('order_id', $order->id)->get();
            $order['addons'] = OrderAddon::where('order_id', $order->id)->get();
            $order['transaction'] = OrderTransaction::where('order_id', $order->id)->first();

            $order['transaction']->payment_status = __('einvoice.' . strtolower($order['transaction']->payment_status));
            $order['transaction']->payment_method = __('einvoice.' . strtolower($order['transaction']->payment_method));

            $order_tracking = OrderTracking::where('order_id', $order->id)->orderby('id', 'desc')->first();
            $order['last_status'] = $order_tracking ? $order_tracking->order_status : null;

            $order['source'] = $this->determineOrderSource($order);

            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';


            $order['currency_symbol'] = $currencySymbol;
            $order['status'] = __('order.' . strtolower($order['status']));
            $order['type'] = __('order.' . strtolower($order['type']));

            // Calculate order_items_count based on order status
            if ($order->status === 'cancelled') {
                $order['order_items_count'] = $order->orderDetails->sum('quantity');
            } else {
                // Assuming you have a relationship called orderDetailsWithoutCancel
                $order['order_items_count'] = $order->orderDetailsWithoutCancel->sum('quantity');
            }

            // Add cancellation reason data if the order is cancelled
            if ($order['last_status'] === 'cancelled') {
                $order['cancellation_reason'] = $order->cancellationReasons->first();
            }
        }
        $data = $paginatedData;
        $data['data'] = [
            'items' => $paginatedData['data'],   // keep the original paginated list
            'total_orders' => $totalOrders,
            'total_price_after_tax' => $totalPriceAfterTax,
            'total_price_before_tax' => $totalPriceBeforeTax,
            'total_service_fees' => $totalServiceFees,
            'total_coupon' => $totalCoupon,
            'total_cash' => $totalCash,
            'total_credit' => $totalCredit,
        ];

        return ResponseWithSuccessDataPaginated($lang, $data, 1);
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

    public function show($id, Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $order = Order::find($id);
            if (!$order) {
                $message = $lang === 'ar' ? ' الطلب غير موجود' : 'order not found';
                return respondError($message, 404);
            }
            $order = $this->ordersReportsService->orderDetailsdashboard($request, $id);
            return ResponseWithSuccessData($lang,  $order, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function details($id, Request $request)
    {
        $lang = $request->header('lang', 'ar');
        // try {
            $order = Order::find($id);
            if (!$order) {
                $message = $lang === 'ar' ? ' الطلب غير موجود' : 'order not found';
                return respondError($message, 404);
            }
            $order = $this->ordersReportsService->orderDetails($id);
            return ResponseWithSuccessData($lang,  $order, 1);
        // } catch (\Exception $e) {
        //     Log::error('Error fetching recipe: ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }
}
