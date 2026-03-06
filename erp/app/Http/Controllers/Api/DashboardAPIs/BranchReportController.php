<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Services\AdminServices\BranchReportService;
use Illuminate\Http\Request;

class BranchReportController extends Controller
{
    protected $branchReportService;

    public function __construct(BranchReportService $branchReportService)
    {
        $this->branchReportService = $branchReportService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $data = $this->branchReportService->getBranchesReport($request);
        return ResponseWithSuccessDataPaginated($lang, $data, 1);

    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $data = $this->branchReportService->show($request, $id);
        return ResponseWithSuccessDataPaginated($lang, $data, 1);

    }
}
