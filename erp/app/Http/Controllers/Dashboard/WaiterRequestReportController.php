<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
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
        $filters = $request->only(['order_number', 'invoice_number']);
        $request = $this->waiterRequestReportService->listRequests($filters);
        $requests = $request->original;
        return view('dashboard.reports.waiter_request.list', compact('requests'));
    }

    public function search(Request $request)
    {
        $search = $request->all();
        $requests = $this->waiterRequestReportService->searchRequests($search);
        $requests = $requests->original;
        return view('dashboard.reports.waiter_request.list', compact('requests'));
    }
}
