<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\KitchenServices\KitchenStaffService;


class KitchenPerformanceReportController extends Controller
{
    protected $KitchenStaffService;


    public function __construct(KitchenStaffService $KitchenStaffService)
    {
        $this->KitchenStaffService = $KitchenStaffService;
    }
    public function index(Request $request)
    {
        $performanceData = $this->KitchenStaffService->listRequests($request);
        return view('dashboard.reports.kitchenStaff.performance.list', compact('performanceData'));
    }

    public function search(Request $request)
    {
        $search = $request->all();
        $data = $this->KitchenStaffService->searchRequests($search);
        $data = $data->get();
        return view('dashboard.reports.kitchenStaff.performance.list', compact('data'));
    }
}
