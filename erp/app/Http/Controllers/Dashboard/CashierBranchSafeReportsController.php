<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ReportServices\CashierBranchSafeService;
use Illuminate\Http\Request;

class CashierBranchSafeReportsController extends Controller
{
    protected $CashierBranchSafeService;
    protected $checkToken;


    public function __construct(CashierBranchSafeService $CashierBranchSafeService)
    {
        $this->CashierBranchSafeService = $CashierBranchSafeService;
        $this->checkToken = true;
    }
    public function index(Request $request)
    {
        $request = $this->CashierBranchSafeService->index();
        // dd($request);
        return view('dashboard.reports.cashier_branch_safe.list', compact('request'));
    }
    public function show(Request $request, $id)
    {
        $lang = app()->getLocale();

        $balance = $this->CashierBranchSafeService->show($id, $lang);
        // dd($request);
        return view('dashboard.reports.cashier_branch_safe.show', compact('balance'));
    }
}
