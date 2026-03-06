<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Order;
use App\Services\ReportServices\DeliveryOrdersReportsService;
use Illuminate\Http\Request;

class DeliveryOrdersReportsController extends Controller
{
    protected $ordersReportsService;
    protected $checkToken;


    public function __construct(DeliveryOrdersReportsService $ordersReportsService)
    {
        $this->ordersReportsService = $ordersReportsService;
        $this->checkToken = false;
    }

    public function list()
    {
        $orders = $this->ordersReportsService->listOrders($this->checkToken);
        $paymentStatuses = $this->ordersReportsService->getPaymentStatuses();
        $paymentMethods = $this->ordersReportsService->getPaymentMethods();
        $deliveries = Employee::where('flag', 'driver')->get();
        return view(
            'dashboard.reports.delivery_orders.list',
            compact('orders', 'deliveries', 'paymentMethods', 'paymentStatuses')
        );
    }

    public function show($id)
    {
        $order = $this->ordersReportsService->orderDetails($id);
        $transaction = $order->orderTransactions->first();
        $tracking = $order->tracking->first();
        $cancellationReason = $order->cancellationReasons->first();
        return view('dashboard.reports.delivery_orders.show', compact('order', 'transaction', 'tracking', 'cancellationReason'));
    }
}
