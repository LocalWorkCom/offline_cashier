<?php


namespace App\Services\ReportServices;

use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use Illuminate\Http\Request;

class CancelledOrdersReportsService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function listOrders(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $query = Order::with(['Branch', 'cancellationReasons'])->where('status', 'cancelled')->whereHas('cancellationReasons');
        // if (auth('admin')->user()->hasRole('Branch Manager')) {
        //     $branch_id = getBranchManagerID();
        //     if ($branch_id) {
        //         $query->where('branch_id', $branch_id);
        //     }
        // }

        $orders = $query;

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('date', [$request->from_date, $request->to_date]);
        }

        if ($request->filled('source') && $request->source != 'all') {
            $query->where('make_type', $request->source);
        }

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
            'cancellationReasons' // Load the cancellation reasons
        ])->findOrFail($id);

        $order['transaction'] = OrderTransaction::first();
        //        $order['delivery'] = Order::first();
        return $order;
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
