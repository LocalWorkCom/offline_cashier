<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Branch;
use App\Services\ReportServices\DeliveryPerformanceMetricsReportService;

class DeliveryPerformanceMetricsReportController extends Controller
{
    protected $DeliveryPerformanceMetricsReportService;

    public function __construct(DeliveryPerformanceMetricsReportService $DeliveryPerformanceMetricsReportService)
    {
        $this->DeliveryPerformanceMetricsReportService = $DeliveryPerformanceMetricsReportService;
    }

    public function index(Request $request)
    {
        $response = $this->DeliveryPerformanceMetricsReportService->index($request);
        $responseData = $response->original['data'] ?? [];

        // Get distinct delivery names for the dropdown
        $deliveryNames = Order::where('type', 'Delivery')
            ->whereNotNull('delivery_id')
            ->with('delivery:id,first_name,last_name')
            ->get()
            ->pluck('delivery')
            ->unique('id')
            ->map(function ($delivery) {
                return [
                    'id' => $delivery->id,
                    'name' => $delivery->first_name . ' ' . $delivery->last_name
                ];
            })
            ->sortBy('name')
            ->values();
        $branches = Branch::select('id', 'name_ar', 'name_en')->get();
        $areas = Area::select('id', 'name_ar', 'name_en')->get();
        return view('dashboard.reports.delivery_performance_metrics_report.list', [
            'DeliveryReport' => $responseData['DeliveryReport'] ?? [],
            'timetables' => $responseData['timetables'] ?? [],
            'selectedTimetableId' => $responseData['selectedTimetableId'] ?? null,
            'branches' => $branches,
            'areas' => $areas,
            'deliveryNames' => $deliveryNames,
            'totals' => $responseData['totals'] ?? [ // Pass totals to the view
                'totalCompletedOrders' => 0,
                'TotalCancelledOrders' => 0,
                'TotalHoldOrders' => 0
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $response = $this->DeliveryPerformanceMetricsReportService->show($request, $id);
        $responseData = $response->getData(); // if using JsonResponse

        // Check if the data is nested under 'Delivery' key in the response
        $DeliveryData = json_decode(json_encode($responseData->data->Delivery ?? $responseData->data), true);
        return view('dashboard.reports.delivery_performance_metrics_report.show', [
            'DeliveryReport' => [
                'delivery' => $DeliveryData
            ]
        ]);
    }
}
