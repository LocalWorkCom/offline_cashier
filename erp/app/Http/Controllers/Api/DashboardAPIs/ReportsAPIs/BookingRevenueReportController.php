<?php

namespace App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\ReportServices\BookingRevenueReportService;
use Illuminate\Support\Facades\Validator;

class BookingRevenueReportController extends Controller
{
    protected $bookingRevenueReportService;

    public function __construct(BookingRevenueReportService $bookingRevenueReportService)
    {
        $this->bookingRevenueReportService = $bookingRevenueReportService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|exists:branches,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'order_number' => 'nullable|string',
            'invoice_number' => 'nullable|string',
            'table_id' => 'nullable|exists:tables,id',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $ordersQuery = $this->bookingRevenueReportService->listOrders();

        // Apply filters to the query
        $this->applyFilters($ordersQuery, $request);

        $paginated = paginateOrGetAll($ordersQuery, $request, []);

        if (isset($paginated['data']) && $paginated['data'] instanceof \Illuminate\Support\Collection) {
            $paginated['data'] = $paginated['data']->map(function ($order) {
                $lastTransaction = optional($order->orderTransactions)->last();
                return [
                    'id' => $order->id ?? '',
                    'table_id' => $order->table_id ?? '',
                    'table_name' => optional($order->Table)->name ?? '',
                    'order_number' => $order->order_number ?? '',
                    'invoice_number' => $order->invoice_number ?? '',
                    'date' => $order->created_at ? $order->created_at->format('Y-m-d') : '',
                    'order_type' => $order->order_type ?? '',
                    'branch_id' => $order->branch_id ?? '',
                    'branch_name' => optional($order->Branch)->name ?? '',
                    'client_name' => optional($order->Client)->flag != 'unknown'
                        ? optional($order->Client)->name
                        : (optional($order->address)->user_name ?? $order->client_name),
                    'total_price' => $order->total_price_after_tax ?? 0,
                    'order_status' => __('order.'.$order->status) ?? '',
                    'payment_status' => $lastTransaction ? __('order.' . $lastTransaction->payment_status) : null,
                    'payment_method' => $lastTransaction ? __('order.' . $lastTransaction->payment_method) : null,
                ];
            })->all();
        }

        return ResponseWithSuccessDataPaginated($lang, $paginated, 1);
    }

    /**
     * Apply filters to the orders query
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', '%' . $request->order_number . '%');
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('table_id')) {
            $query->where('table_id', $request->table_id);
        }

        return $query;
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $exists = Order::where('id', $id)->where('table_id', '!=', null)->exists();
        App::setLocale($lang);

        if (!$exists) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $response = $this->bookingRevenueReportService->orderDetails($id);
        $response->orderDetails = $response->orderDetails->map(function ($item){
                $item->status = __('order.'.$item->status) ?? $item->status;
                return $item;
            });
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $responseData = $response->getData(true);
            return ResponseWithSuccessData($lang, $responseData['data'] ?? $responseData, 1);
        }
        $response->status = __('order.'.$response->status);
        $response->type = __('order.'.$response->type);
        return ResponseWithSuccessData($lang, $response, 1);
    }
}
