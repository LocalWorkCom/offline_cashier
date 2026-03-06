<?php


namespace App\Services\ReportServices;

use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;

class DeliveryOrdersReportsService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function listOrders($checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $query = Order::with(['Branch', 'cancellationReasons'])->where('type', 'Delivery');
        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->where('branch_id', $branch_id);
            }
        }

        $orders = $query->get();
        foreach ($orders as $order) {
            $order['details'] = OrderDetail::where('order_id', $order->id)->get();
            $order['addons'] = OrderAddon::where('order_id', $order->id)->get();
            $order['transaction'] = OrderTransaction::where('order_id', $order->id)->first();
            $order_tracking = OrderTracking::where('order_id', $order->id)->orderby('id', 'desc')->first();
            $order['last_status'] = $order_tracking ? $order_tracking->order_status : null;

            // Add cancellation reason data if the order is cancelled
            if ($order['last_status'] === 'cancelled') {
                $order['cancellation_reason'] = $order->cancellationReasons->first();
            }
            //            $order['last_status'] = $order_tracking ? $order_tracking->order_status : null;
        }

        return $orders;
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
            'cancellationReasons', // Load the cancellation reasons
            'deliveryComplaints',
            'feedback',
            'delivery'
        ])->where('type', 'Delivery')->findOrFail($id);

        $order['transaction'] = OrderTransaction::first();
        //        $order['delivery'] = Order::first();
        return $order;
    }
    public function getPaymentStatuses()
    {
        return OrderTransaction::select('payment_status')
            ->distinct()
            ->orderBy('payment_status')
            ->pluck('payment_status')
            ->filter()
            ->values()
            ->toArray();
    }

    public function getPaymentMethods()
    {
        return OrderTransaction::select('payment_method')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method')
            ->filter()
            ->values()
            ->toArray();
    }
}
