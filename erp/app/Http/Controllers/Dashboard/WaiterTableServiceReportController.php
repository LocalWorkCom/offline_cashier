<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ReportServices\WaiterTableServiceReportService;

class WaiterTableServiceReportController extends Controller
{
    protected $WaiterTableServiceReportService;

    public function __construct(WaiterTableServiceReportService $WaiterTableServiceReportService)
    {
        $this->WaiterTableServiceReportService = $WaiterTableServiceReportService;
    }
    public function index(Request $request)
    {
        $response = $this->WaiterTableServiceReportService->index($request);
        $responseData = $response->original['data'] ?? [];

        // Fetch all waiters who have served orders
        // $waiterIds = Order::where('make_type', 'waiter')
        //     ->whereNotNull('waiter_id')
        //     ->pluck('waiter_id')
        //     ->unique();

        // $waiters = Employee::whereIn('id', $waiterIds)
        //     ->select('id', 'first_name', 'last_name', 'phone_number')
        //     ->get();
        $waiters = Employee::where('flag', 'waiter') // or is_waiter = true
            ->select('id', 'first_name', 'last_name', 'phone_number')
            ->get();

        return view('dashboard.reports.waiter_table_service_report.list', [
            'WaiterTableService' => $responseData['WaiterTableService'] ?? [],
            'shifts' => $responseData['shifts'] ?? [],
            'timetables' => $responseData['timetables'] ?? [],
            'selectedShiftId' => $responseData['selectedShiftId'] ?? null,
            'selectedTimetableId' => $responseData['selectedTimetableId'] ?? null,
            'branches' => $responseData['branches'] ?? [],
            'waiters' => $waiters,
            'totals' => $responseData['totals'] ?? [ // Add this line
                'total_orders' => 0,
                'total_price' => 0,
                'total_table' => 0
            ]
        ]);
    }
    public function show(Request $request, $id)
    {
        $response = $this->WaiterTableServiceReportService->show($request, $id);
        $responseData = $response->getData(); // if using JsonResponse

        // Check if the data is nested under 'waiter' key in the response
        $waiterData = json_decode(json_encode($responseData->data->waiter ?? $responseData->data), true);

        return view('dashboard.reports.waiter_table_service_report.show', [
            'WaiterTableService' => $waiterData
        ]);
    }
}
