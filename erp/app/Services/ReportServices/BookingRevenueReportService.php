<?php


namespace App\Services\ReportServices;

use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;

class BookingRevenueReportService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function listOrders()
    {

        $orders = Order::with(['orderTransactions', 'Table', 'Branch', 'cancellationReasons', 'Client'])->where('status', 'completed')->whereHas('Table')->whereDoesntHave('cancellationReasons');
        // if (auth('admin')->user()->hasRole('Branch Manager')) {
        //     $branch_id = getBranchManagerID();
        //     if ($branch_id) {
        //         $orders->where('branch_id', $branch_id);
        //     }
        // }

        // $user = auth('employee')->user();
        // $orders->where('branch_id', $user->branch_id);

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

            $order['data'] = [
                'id' => $order->id ?? '',
                'table_id' => $order->table_id ?? '',
                'table_name' => $order->Table->name ?? '',
                'order_number' => $order->order_number ?? '',
                'invoice_number' => $order->invoice_number ?? '',
                'date' => $order->created_at->format('Y-m-d') ?? '',
                'order_type' => $order->order_type ?? '',
                'branch_id' => $order->branch_id ?? '',
                'branch_name' => $order->Branch->name ?? '',
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
                'total_price' => $order->total_price_after_tax ?? 0,
                'order_status' => $order->status ?? '',
                'payment_status' => $order->orderTransactions->last()->payment_status ?? null,
                'payment_method' => $order->orderTransactions->last()->payment_method ?? null,
            ];
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
        ])->where('table_id', '!=', null)->find($id);

        $order['transaction'] = OrderTransaction::first();
        //        $order['delivery'] = Order::first();
        return $order;
    }
}
