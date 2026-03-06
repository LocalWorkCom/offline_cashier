<?php


namespace App\Services\ReportServices;

use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;

class CustomerServiceDeliveryOrdersReportsService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function listOrders($request)
    {
        $query = Order::with(['Branch', 'cancellationReasons', 'delivery', 'customerService'])
            ->whereHas('customerService')
            ->whereHas('delivery');

        if (auth('employee')->check() && auth('employee')->user()->hasRole('Branch_Manager')) {
            $branch_id = getBranchManagerID();

            if ($branch_id) {
                $query->whereHas('branch', function ($q) use ($branch_id) {
                    $q->where('branch_id', $branch_id);
                });
            }
        } elseif (auth('admin')->check() && auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->whereHas('order', function ($q) use ($branch_id) {
                    $q->where('branch_id', $branch_id);
                });
            }
        }

        // ✅ Apply filters
        if ($request->filled('order_number')) {
            $query->where('order_number', 'LIKE', '%' . $request->order_number . '%');
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'LIKE', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('customer_service_name')) {
            $query->whereHas('customerService', function ($q) use ($request) {
                $q->where('first_name', 'LIKE', '%' . $request->customer_service_name . '%');
            });
        }

        if ($request->filled('delivery_name')) {
            $query->whereHas('delivery', function ($q) use ($request) {
                $q->where('first_name', 'LIKE', '%' . $request->delivery_name . '%');
            });
        }

        return $query;
    }

    /**
     * Apply order extra details (shared by multiple controllers)
     */
    public function enrichOrders($orders)
    {
        foreach ($orders as $order) {
            $order['details'] = OrderDetail::where('order_id', $order->id)->get();
            $order['addons'] = OrderAddon::where('order_id', $order->id)->get();
            $order['transaction'] = OrderTransaction::where('order_id', $order->id)->first();
            $order_tracking = OrderTracking::where('order_id', $order->id)->orderby('id', 'desc')->first();
            $order['last_status'] = $order_tracking ? $order_tracking->order_status : null;
            $order['source'] = $this->determineOrderSource($order);

            if ($order['last_status'] === 'cancelled') {
                $order['cancellation_reason'] = $order->cancellationReasons->first();
            }
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
