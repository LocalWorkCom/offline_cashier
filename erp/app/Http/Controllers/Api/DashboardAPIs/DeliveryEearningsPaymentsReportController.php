<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ReportServices\DeliveryEearningsPaymentsReportService;

class DeliveryEearningsPaymentsReportController extends Controller
{
    protected $DeliveryEearningsPaymentsReportService;
    protected $lang;

    public function __construct(DeliveryEearningsPaymentsReportService $DeliveryEearningsPaymentsReportService)
    {
        $this->lang =  app()->getLocale();

        $this->DeliveryEearningsPaymentsReportService = $DeliveryEearningsPaymentsReportService;
    }

    public function index(Request $request)
    {
        $response = $this->DeliveryEearningsPaymentsReportService->index($request);
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

        // Use totals from the API response if available
        $totals = $responseData['totals'] ?? [
            'totalCompletedOrders' => 0,
            'TotalEarnings' => 0
        ];
        $response = [
            'DeliveryReport' => $responseData['DeliveryReport'] ?? [],
            'timetables' => $responseData['timetables'] ?? [],
            'selectedTimetableId' => $responseData['selectedTimetableId'] ?? null,
            'branches' => $responseData['branches'] ?? [],
            'deliveryNames' => $deliveryNames,
            'totals' => $totals // Add totals to the view data

        ];
        return ResponseWithSuccessData($this->lang, $response, 1);
    }

    public function show(Request $request, $id)
    {
        $response = $this->DeliveryEearningsPaymentsReportService->show($request, $id);
        
        if (isset($response->original['code']) && $response->original['code'] == 404)
        {
            return respondError(__('No completed orders found for this Delivery'), 404);
        }
        $responseData = $response->getData(); // if using JsonResponse

        // Check if the data is nested under 'Delivery' key in the response
        $DeliveryData = json_decode(json_encode($responseData->data->Delivery ?? $responseData->data), true);
        return ResponseWithSuccessData($this->lang,  $DeliveryData, 1);
    }
}
