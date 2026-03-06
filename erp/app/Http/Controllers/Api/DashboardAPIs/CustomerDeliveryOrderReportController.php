<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ReportServices\CustomerServiceDeliveryOrdersReportsService;
use App\Services\OrdersReportsService;
use Illuminate\Http\Request;

class CustomerDeliveryOrderReportController extends Controller
{
    protected $ordersReportsService;
    protected $checkToken;


    public function __construct(CustomerServiceDeliveryOrdersReportsService $ordersReportsService)
    {
        $this->ordersReportsService = $ordersReportsService;
        $this->checkToken = false;
    }

   public function list(Request $request)
{
    $lang = $request->header('lang', 'ar');

    // Get query from service
    $query = $this->ordersReportsService->listOrders($request);

    // Paginate in controller
    $orders = paginateOrGetAll($query, $request);
        //  paginateOrGetAll($query, $request, $fields);

    // Enrich orders using service method
    $this->ordersReportsService->enrichOrders($orders['data']);

    return ResponseWithSuccessDataPaginated($lang, $orders, 1);
}

}
