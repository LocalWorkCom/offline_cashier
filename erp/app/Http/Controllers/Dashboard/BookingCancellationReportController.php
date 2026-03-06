<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use App\Services\ReportServices\BookingCancellationReportService;
use Illuminate\Http\Request;

class BookingCancellationReportController extends Controller
{
    protected $ordersReportsService;
    protected $checkToken;


    public function __construct(BookingCancellationReportService $ordersReportsService)
    {
        $this->ordersReportsService = $ordersReportsService;
        $this->checkToken = false;
    }

    public function index()
    {
        $orders = $this->ordersReportsService->listOrders()->get();
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
        return view('dashboard.reports.booking_cancellation.list', compact('orders'));
    }

    public function show($id)
    {
        $order = $this->ordersReportsService->orderDetails($id);
        $transaction = $order->orderTransactions->first();
        $tracking = $order->tracking->first();
        $cancellationReason = $order->cancellationReasons->first(); // Get the first cancellation reason

        return view('dashboard.reports.booking_cancellation.show', compact('order', 'transaction', 'tracking', 'cancellationReason'));
    }
}
