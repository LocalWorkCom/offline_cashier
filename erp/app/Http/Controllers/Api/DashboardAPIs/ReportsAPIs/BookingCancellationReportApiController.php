<?php

namespace App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Services\ReportServices\BookingCancellationReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class BookingCancellationReportApiController extends Controller
{
    protected $ordersReportsService;
    protected $checkToken;


    public function __construct(BookingCancellationReportService $ordersReportsService)
    {
        $this->ordersReportsService = $ordersReportsService;
        $this->checkToken = false;
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

        $ordersQuery = $this->ordersReportsService->listOrders();

        // Apply filters to the query
        $this->applyFilters($ordersQuery, $request);

        $paginated = paginateOrGetAll($ordersQuery, $request, []);

        if (isset($paginated['data']) && $paginated['data'] instanceof \Illuminate\Support\Collection) {
            $paginated['data'] = $paginated['data']->map(function ($order) {
                $order_tracking = OrderTracking::where('order_id', $order->id)->orderby('id', 'desc')->first();
                $last_status = $order_tracking ? $order_tracking->order_status : null;
                if ($last_status === 'cancelled' && $order->cancellationReasons->isNotEmpty()) {
                    $type = __('cancellation_reasons.'.$order->cancellationReasons->first()->type) ?? null;
                    $user = optional($order->cancellationReasons->first()->user)->name ?? null;
                } else {
                    $type = '----';
                    $user = '----';
                }
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
                    'payment_status' => __('order.'.optional($order->orderTransactions)->last()->payment_status) ?? null,
                    'payment_method' => __('order.'.optional($order->orderTransactions)->last()->payment_method) ?? null,
                    'cancellation_reason' => optional($order->cancellationReasons)->first()->reason ?? null,
                    'make_type' => __('order.'.$order->make_type) ?? null,
                    'type' => $type,
                    'user' => $user,
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

        $exists = Order::where('id', $id)->where('table_id', '!=', null)->exists();
        App::setLocale($lang);

        if (!$exists) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $response = $this->ordersReportsService->orderDetails($id);
        $response->tracking = $response->tracking->map(function ($item){
            $item->order_status = __('order.'.$item->order_status) ?? $item->order_status;
            return $item;
        });

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $responseData = $response->getData(true);
            return ResponseWithSuccessData($lang, $responseData['data'] ?? $responseData, 1);
        }

        return ResponseWithSuccessData($lang, $response, 1);
    }
}
