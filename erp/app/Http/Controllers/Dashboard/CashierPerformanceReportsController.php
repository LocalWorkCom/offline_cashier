<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ReportServices\CashierPerformanceReportService;

class CashierPerformanceReportsController extends Controller
{
    protected $cashierService;

    public function __construct(CashierPerformanceReportService $cashierService)
    {
        $this->cashierService = $cashierService;
    }

    public function list(Request $request)
    {
        $data = $this->cashierService->listCashiers($request);

        return view('dashboard.reports.cashier_performance.list', [
            'cashiers' => $data['cashiers']->get(),
            'allCashiers' => $data['allCashiers']
        ]);
    }

    public function show($id)
    {
        $cashier = $this->cashierService->showCashier($id);

        return view('dashboard.reports.cashier_performance.show', compact('cashier'));
    }
}
