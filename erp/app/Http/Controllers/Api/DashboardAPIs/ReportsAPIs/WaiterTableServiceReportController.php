<?php

namespace App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\ReportServices\WaiterTableServiceReportService;
use Illuminate\Http\Request;

class WaiterTableServiceReportController extends Controller
{
    protected $WaiterTableServiceReportService;

    public function __construct(WaiterTableServiceReportService $WaiterTableServiceReportService)
    {
        $this->WaiterTableServiceReportService = $WaiterTableServiceReportService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $response = $this->WaiterTableServiceReportService->index($request, 1);
        $responseData = $response->original['data'] ?? [];

        $waiters = Employee::where('flag', 'waiter') // or is_waiter = true
            ->select('id', 'first_name', 'last_name', 'phone_number')
            ->get();

        $data['data'] = [
            'WaiterTableService' => $responseData['WaiterTableService'] ?? [],
            'shifts' => $responseData['shifts'] ?? [],
            'timetables' => $responseData['timetables'] ?? [],
            'selectedShiftId' => $responseData['selectedShiftId'] ?? null,
            'selectedTimetableId' => $responseData['selectedTimetableId'] ?? null,
            'branches' => $responseData['branches'] ?? [],
            'selectedBranchId' => $responseData['selectedBranchId'] ?? null, // Selected branch ID
            'waiters' => $waiters,
            'totals' => $responseData['totals'] ?? [ // Add this line
                'total_orders' => 0,
                'total_price' => 0,
                'total_table' => 0
            ],
        ];
            
        $data['meta'] = $responseData['meta'] ?? null;
        return ResponseWithSuccessDataPaginated($lang, $data, 1);
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $response = $this->WaiterTableServiceReportService->show($request, $id, 1);
        $responseData = $response->getData(); // if using JsonResponse
        if ($response->getStatusCode() != 200) {
            return $response; // Return the error response as-is
        }
        // Check if the data is nested under 'waiter' key in the response
        $waiterData = json_decode(json_encode($responseData->data->waiter ?? $responseData->data), true);
        

        $data = [
            'WaiterTableService' => $waiterData
        ];
        return ResponseWithSuccessData($lang, $data, 1);
    }

    public function allwaiters(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $waiters = Employee::where('flag', 'waiter')->select('id', 'first_name', 'last_name', 'phone_number')->get();

            return ResponseWithSuccessData($lang, $waiters, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
