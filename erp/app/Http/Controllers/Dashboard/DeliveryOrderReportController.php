<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ReportServices\DeliveryOrderReportService;
use Illuminate\Http\Request;

class DeliveryOrderReportController extends Controller
{
    protected $DeliveryOrderReportService;

    public function __construct(DeliveryOrderReportService $DeliveryOrderReportService)
    {
        $this->DeliveryOrderReportService = $DeliveryOrderReportService;
    }

    public function index(Request $request)
    {
        $response = $this->DeliveryOrderReportService->index($request);
        $responseData = $response->original['data'] ?? [];

        return view('dashboard.reports.delivery_order_report.list', [
            'DeliveryReport' => $responseData['DeliveryReport'] ?? [],
            'timetables' => $responseData['timetables'] ?? [],
            'selectedTimetableId' => $responseData['selectedTimetableId'] ?? null,
            'branches' => $responseData['branches'] ?? [],
            'deliveries' => $responseData['deliveries'] ?? [],
            'totals' => $responseData['totals'] ?? ['TotalCancelledOrders' => 0, 'TotalComplineOrders' => 0], // ✅ Add this
        ]);
    }

    public function show(Request $request, $id)
    {
        $response = $this->DeliveryOrderReportService->show($request, $id);
        $responseData = $response->getData(); // if using JsonResponse

        // Check if the data is nested under 'Delivery' key in the response
        $DeliveryData = json_decode(json_encode($responseData->data->Delivery ?? $responseData->data), true);

        return view('dashboard.reports.delivery_order_report.show', [
            'DeliveryReport' => $DeliveryData
        ]);
    }
}
