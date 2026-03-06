<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ReportServices\BranchSafeControllerService;
use Illuminate\Http\Request;

class BranchSafeController extends Controller
{
    protected $BranchSafeControllerService;
    protected $checkToken;


    public function __construct(BranchSafeControllerService $BranchSafeControllerService)
    {
        $this->BranchSafeControllerService = $BranchSafeControllerService;
        $this->checkToken = true;
    }


    public function index(Request $request)
    {
        $request = $this->BranchSafeControllerService->index();
        // dd($request);
        return view('dashboard.reports.branch_safe.list', compact('request'));
    }

    public function show(Request $request , $id)
    {
        $response = $this->BranchSafeControllerService->show($id);
        return view('dashboard.reports.branch_safe.show', compact('response'));
    }
}
