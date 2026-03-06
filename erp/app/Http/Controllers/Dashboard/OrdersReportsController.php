<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ReportServices\OrdersReportsService;
use Illuminate\Http\Request;

class OrdersReportsController extends Controller
{
    protected $ordersReportsService;
    protected $checkToken;


    public function __construct(OrdersReportsService $ordersReportsService)
    {
        $this->ordersReportsService = $ordersReportsService;
        $this->checkToken = false;
    }

    public function list()
    {
        $orders = $this->ordersReportsService->listOrders('admin');
        return view('dashboard.reports.orders.list', compact('orders'));
    }

    public function show($id)
    {
        $order = $this->ordersReportsService->orderDetails($id);
        $transaction = $order->orderTransactions->first();
        $tracking = $order->tracking->first();
        $cancellationReason = $order->cancellationReasons->first(); // Get the first cancellation reason

        return view('dashboard.reports.orders.show', compact('order', 'transaction', 'tracking', 'cancellationReason'));
    }
}
