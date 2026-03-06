<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ReportServices\DeliveryPerformanceMetricsReportService;

class DeliveryPerformanceMetricsReportController extends Controller
{
    protected $DeliveryPerformanceMetricsReportService;
    protected $lang;

    public function __construct(DeliveryPerformanceMetricsReportService $DeliveryPerformanceMetricsReportService)
    {
        $this->lang =  app()->getLocale();
        $this->DeliveryPerformanceMetricsReportService = $DeliveryPerformanceMetricsReportService;
    }

    public function index(Request $request)
    {
        $response = $this->DeliveryPerformanceMetricsReportService->index($request);
        $responseData = $response->original;

        if (!$responseData['status']) {
            return $response;
        }
        $responseData = $response->original['data'] ?? [];

        $response = [
            'data' => $responseData['DeliveryReport'],
            'meta' => $responseData['meta']
        ];
        return ResponseWithSuccessDataPaginated($this->lang, $response, 1);
    }

    public function show(Request $request, $id)
    {
        $response = $this->DeliveryPerformanceMetricsReportService->show($request, $id);
        // $responseData = $response->getData(); // if using JsonResponse
        $responseData = $response->original;
        // dd($responseData);
        if (!$responseData['status']) {
            return respondErrorData($responseData['message'], 400);
        }
        // Check if the data is nested under 'Delivery' key in the response
        $DeliveryData = $responseData['data'];
        $response = [
            'DeliveryReport' => [
                'delivery' => $DeliveryData
            ]
        ];
        return ResponseWithSuccessData($this->lang, $response, 1);
    }
}
