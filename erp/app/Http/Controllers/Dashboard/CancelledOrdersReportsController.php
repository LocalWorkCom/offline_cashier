<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ReportServices\CancelledOrdersReportsService;
use Illuminate\Http\Request;

class CancelledOrdersReportsController extends Controller
{
    protected $ordersReportsService;
    protected $checkToken;


    public function __construct(CancelledOrdersReportsService $ordersReportsService)
    {
        $this->ordersReportsService = $ordersReportsService;
        $this->checkToken = false;
    }

    public function list(Request $request)
    {
        $orders = $this->ordersReportsService->listOrders($request, $this->checkToken);
        $orders = $orders->get();
        return view('dashboard.reports.cancelled_orders.list', compact('orders'));
    }

    public function show($id)
    {
        $order = $this->ordersReportsService->orderDetails($id);
        $transaction = $order->orderTransactions->first();
        $tracking = $order->tracking->first();
        $cancellationReason = $order->cancellationReasons->first(); // Get the first cancellation reason

        return view('dashboard.reports.cancelled_orders.show', compact('order', 'transaction', 'tracking', 'cancellationReason'));
    }
}
