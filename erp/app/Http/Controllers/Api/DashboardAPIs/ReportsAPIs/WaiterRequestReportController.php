<?php

namespace App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WaiterRequest;
use App\Services\ReportServices\WaiterRequestReportService;
use Illuminate\Http\Request;

class WaiterRequestReportController extends Controller
{
    protected $waiterRequestReportService;
    protected $checkToken;


    public function __construct(WaiterRequestReportService $waiterRequestReportService)
    {
        $this->waiterRequestReportService = $waiterRequestReportService;
        $this->checkToken = true;
    }


    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $branchOrderIds = Order::where('branch_id', getBranchManagerID())->pluck('id')->toArray();

        $query = WaiterRequest::select('id', 'type', 'status', 'order_ids', 'created_at', 'reason')
            ->when(auth('employee')->user()->hasRole('Branch_Manager'), function ($q) use ($branchOrderIds) {
                $q->where(function ($subQ) use ($branchOrderIds) {
                    foreach ($branchOrderIds as $orderId) {
                        $subQ->orWhereJsonContains('order_ids', $orderId);
                    }
                });
            });

        // Get paginated results
        $result = paginateOrGetAll($query, $request, [], []);

        // Prepare the data for response
        $requests = $this->waiterRequestReportService->prepareRequestsData($result['data']);

        // Replace the data in the result with the prepared requests
        $result['data'] = $requests;

        return ResponseWithSuccessDataPaginated($lang, $result, 1);
    }

    public function search(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $search = $request->all();
        $requests = $this->waiterRequestReportService->searchRequests($search);
        $requests = $requests->original;
        return ResponseWithSuccessData($lang, $requests, 1);
    }
}
