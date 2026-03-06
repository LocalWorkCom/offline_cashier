<?php

namespace App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs;

use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryOrderCancelResource;
use App\Services\ReportServices\DeliveryOrderReportService;

class CancelledDeliveryOrdersReportController extends Controller
{
    protected $DeliveryOrderReportService;

    protected $fields = [];
    protected $fields_visible = [];

    public function __construct(DeliveryOrderReportService $DeliveryOrderReportService)
    {
        $this->DeliveryOrderReportService = $DeliveryOrderReportService;
    }

    public function list(Request $request)
    {
        $lang = $request->header('lang') ?? 'ar';
        $ordersQuery = $this->DeliveryOrderReportService->getCancelledDeliveryOrders_API($request);
        $paginated = paginateOrGetAll($ordersQuery, $request, []);
        $orders = $paginated['data'];

        // Group by delivery_id
        $grouped = collect($orders)->groupBy('delivery_id')->map(function ($orders, $deliveryId) {
            $delivery = $orders->first()->delivery;
            $cancelledOrders = $orders->map(function ($order) {
                return [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number ?? $order->id,
                    'branch' => $order->branch,
                    'complaint' => $order->deliveryComplaints,
                    'cancellation_reason' => $order->cancellationReasons,
                ];
            })->values();

            return [
                'delivery' => $delivery,
                'cancelled_orders_count' => $orders->count(),
                'cancelled_orders' => $cancelledOrders,
            ];
        })->values();

        $response = [
            'data' => $grouped,
            'meta' => $paginated['meta'],
        ];

        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang') ?? 'ar';
        $ordersQuery = $this->DeliveryOrderReportService->getCancelledDeliveryOrderById_API($request, $id);
        $orders = $ordersQuery->get();

        if ($orders->isEmpty()) {
            $message = $lang == 'en' ? 'Delivery not found' : 'المندوب غير موجود';
            return respondError($message, 404);
        }

        $delivery = $orders->first()->delivery;
        $cancelledOrders = $orders->map(function ($order) {
            return [
                'order_id' => $order->id,
                'order_number' => $order->order_number ?? $order->id,
                'branch' => $order->branch,
                'complaint' => $order->deliveryComplaints,
                'cancellation_reason' => $order->cancellationReasons,
            ];
        })->values();

        $data = [
            'delivery' => $delivery,
            'cancelled_orders_count' => $orders->count(),
            'cancelled_orders' => $cancelledOrders,
        ];

        return ResponseWithSuccessData($lang, $data, 1);
    }
}
