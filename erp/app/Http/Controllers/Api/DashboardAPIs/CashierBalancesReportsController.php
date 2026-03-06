<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

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
    protected $lang;

    // Inject the service via constructor
    public function __construct(CashierBalancesReportService $cashierBalancesReportService)
    {
        $this->cashierBalancesReportService = $cashierBalancesReportService;
        $this->lang =  app()->getLocale();
    }
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'from', 'to', 'shift_id', 'cashier_id', 'branch_id']);
        $data = $this->cashierBalancesReportService->index($filters);

        $response = paginateOrGetAll($data['balances'], $request);
// dd($response['data']);
        $responseData['data'] = [
            'balances' => $response['data'],
            'shifts' => $data['shifts'],
            'selectedShiftId' => $filters['shift_id'] ?? null,
            'cashiers' => $data['cashiers'],
            'selectedCashierId' => $filters['cashier_id'] ?? null,
            'branches' => $data['branches'],
            'selectedBranchId' => $filters['branch_id'] ?? null,
        ];

        return ResponseWithSuccessDataPaginated($this->lang, $response, 1);
        // $response =  [
        //     'balances' => $data['balances'],
        //     'shifts' => $data['shifts'],
        //     'selectedShiftId' => $filters['shift_id'] ?? null,
        //     'cashiers' => $data['cashiers'],
        //     'selectedCashierId' => $filters['cashier_id'] ?? null,
        //     'branches' => $data['branches'],
        //     'selectedBranchId' => $filters['branch_id'] ?? null,
        // ];
        // return ResponseWithSuccessData($this->lang, $response, 1);
    }

    public function show($id)
    {
        try {
            $balance = EmployeeOpeningBalance::with(['employees', 'cashierMachines'])->findOrFail($id);
            $data = $this->cashierBalancesReportService->show($id);
            return ResponseWithSuccessData($this->lang, $data, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return respondErrorData(
                $this->lang == 'en' ? ['Not existing any more'] : ['غير موجود'],
                400,
                $this->lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']
            );
        }
    }
}
