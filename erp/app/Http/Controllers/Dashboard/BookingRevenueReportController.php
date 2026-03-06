<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ReportServices\BookingRevenueReportService;
use Illuminate\Http\Request;

class BookingRevenueReportController extends Controller
{
    protected $ordersReportsService;
    protected $checkToken;


    public function __construct(BookingRevenueReportService $ordersReportsService)
    {
        $this->ordersReportsService = $ordersReportsService;
        $this->checkToken = false;
    }

    public function index()
    {
        $orders = $this->ordersReportsService->listOrders()->get();
        return view('dashboard.reports.booking_revenue.list', compact('orders'));
    }

    public function show($id)
    {
        $order = $this->ordersReportsService->orderDetails($id);
        $transaction = $order->orderTransactions->first();
        $tracking = $order->tracking->first();
        $cancellationReason = $order->cancellationReasons->first(); // Get the first cancellation reason

        return view('dashboard.reports.booking_revenue.show', compact('order', 'transaction', 'tracking', 'cancellationReason'));
    }
}
