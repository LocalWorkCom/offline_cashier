<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchSafe;
use App\Models\Dish;
use App\Models\Employee;
use App\Models\EmployeeOpeningBalance;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\Shift;
use App\Models\Timetable;
use App\Services\HR_Services\TimetableService;
use App\Services\ReportServices\CashierBalancesReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashierBalancesReportsController extends Controller
{
    protected $cashierBalancesReportService;


    // Inject the service via constructor
    public function __construct(CashierBalancesReportService $cashierBalancesReportService)
    {
        $this->cashierBalancesReportService = $cashierBalancesReportService;
    }
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'from', 'to', 'shift_id', 'cashier_id', 'branch_id']);
        $data = $this->cashierBalancesReportService->index($filters);

        return view('dashboard.reports.cashier_balance.list', [
            'balances' => $data['balances']->get(),
            'shifts' => $data['shifts'],
            'selectedShiftId' => $filters['shift_id'] ?? null,
            'cashiers' => $data['cashiers'],
            'selectedCashierId' => $filters['cashier_id'] ?? null,
            'branches' => $data['branches'],
            'selectedBranchId' => $filters['branch_id'] ?? null,
        ]);
    }

    public function show($id)
    {
        $data = $this->cashierBalancesReportService->show($id);

        return view('dashboard.reports.cashier_balance.show', $data);
    }
}
