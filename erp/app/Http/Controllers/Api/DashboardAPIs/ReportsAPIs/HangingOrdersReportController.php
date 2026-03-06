<?php

namespace App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs;

use App\Http\Controllers\Controller;
use App\Models\DeliveryComplaints;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use App\Services\ReportServices\DeliveryComplaintsReportService;
use App\Services\ReportServices\OrdersReportsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HangingOrdersReportController extends Controller
{
    protected $complaintsService;

    public function __construct(DeliveryComplaintsReportService $complaintsService)
    {
        $this->complaintsService = $complaintsService;
    }

public function hangingOrders(Request $request)
{
    $lang = $request->header('lang', 'ar');
    $filters = [
        'branch_id'        => $request->query('branch'),
        'date_from'        => $request->query('from_date'),
        'date_to'          => $request->query('to_date'),
        'make_type'        => $request->query('maketypeof_order'),
        'complaint_status' => $request->query('status_of_complaint'),
        'manage_type'      => $request->query('manage_type'),
        'order_number'     => $request->query('order_number'),
        'invoice_number'   => $request->query('invoice_number'),
        'delivery_name'    => $request->query('delivery_name'),
        'delivery_phone'   => $request->query('delivery_phone'),
    ];

    $query = DeliveryComplaints::query()->with('order', 'order.delivery', 'order.branch');
// dd($filters);
    if ($filters['complaint_status']) {
        $query->where('status', $filters['complaint_status']);
    }
    if ($filters['manage_type']) {
        $query->where('manage', $filters['manage_type']);
    }
    if ($filters['branch_id']) {
        $query->whereHas('order', function ($q) use ($filters) {
            $q->where('branch_id', $filters['branch_id']);
        });
    }
    if ($filters['date_from']) {
        $query->whereHas('order', function ($q) use ($filters) {
            $q->whereDate('created_at', '>=', $filters['date_from']);
        });
    }
    if ($filters['date_to']) {
        $query->whereHas('order', function ($q) use ($filters) {
            $q->whereDate('created_at', '<=', $filters['date_to']);
        });
    }
    if ($filters['make_type']) {
        $query->whereHas('order', function ($q) use ($filters) {
            $q->where('make_type', $filters['make_type']);
        });
    }
    if ($filters['order_number']) {
        $query->whereHas('order', function ($q) use ($filters) {
            $q->where('order_number', 'like', '%' . $filters['order_number'] . '%');
        });
    }
    if ($filters['invoice_number']) {
        $query->whereHas('order', function ($q) use ($filters) {
            $q->where('invoice_number', $filters['invoice_number']);
        });
    }
    if ($filters['delivery_name']) {
        $query->whereHas('order.delivery', function ($q) use ($filters) {
            $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $filters['delivery_name'] . '%']);
        });
    }
    if ($filters['delivery_phone']) {
        $query->whereHas('order.delivery', function ($q) use ($filters) {
            $q->where('phone_number', 'like', '%' . $filters['delivery_phone'] . '%');
        });
    }
        $data = paginateOrGetAll($query, $request,  [], []);
        
        if (isset($data['data'])) {
            $data['data'] = collect($data['data'])->map(function ($item) use ($lang) {
                $item->order->branch_name = optional($item->order->branch)->{"name_{$lang}"} ?? null;
                unset($item->order->branch);
                return $item;
            });
        }

    return ResponseWithSuccessDataPaginated($lang, $data, 1);
}
    
    public function hangingOrdersDetails($id, Request $request)
    {

        $lang = $request->header('lang', 'ar');
        try {
            $complaints = DeliveryComplaints::with('order', 'order.delivery', 'order.branch')->findOrFail($id);

            $order = $this->complaintsService->show($request, $id, $lang);
            $order->delivery_name = optional($complaints->order->delivery)->first_name . ' ' . optional($complaints->order->delivery)->last_name;
            $order->delivery_phone = optional($complaints->order->delivery)->phone_number;
            $order->order_number = $complaints->order->order_number;
            $order->client_name = $complaints->order->client_name;
            $order->client_phone = $complaints->order->client_phone;
            $order->invoice_number = $complaints->order->invoice_number;
            $order->branch_name = optional($complaints->order->branch)->{"name_{$lang}"} ?? null;

            return ResponseWithSuccessDataPaginated($lang,  ['data' => $order], 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? ' الشكوي غير موجود' : 'complaint not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
