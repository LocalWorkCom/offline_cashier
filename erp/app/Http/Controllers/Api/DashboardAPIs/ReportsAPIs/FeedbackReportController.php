<?php

namespace App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs;

use Illuminate\Http\Request;
use App\Models\Complaint;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\ReportServices\FeedbackReportService;
use Carbon\Carbon;

class FeedbackReportController extends Controller
{
    protected $complaintsService;

    public function __construct(FeedbackReportService $complaintsService)
    {
        $this->complaintsService = $complaintsService;
    }
    // In your controller
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        app()->setLocale($lang);

        try {
            // Use your service method to get the base query
            $serviceResult = $this->complaintsService->index($request);

            // Check if the service returned an error response
            if ($serviceResult instanceof \Illuminate\Http\JsonResponse && $serviceResult->getStatusCode() !== 200) {
                return $serviceResult;
            }

            // Get the complaints data from service
            $complaints = $serviceResult->getData()->data ?? [];

            // Convert to query for pagination and totals (if needed)
            $query = Complaint::whereIn('id', collect($complaints)->pluck('id'));

            // Clone the query for totals to avoid affecting the main query
            $totalQuery = clone $query;

            // Get status-based counts
            $pendingCount = Complaint::where('status', 'pending')->count();
            $inprogressCount = Complaint::where('status', 'inprogress')->count();
            $solvedCount = Complaint::where('status', 'solved')->count();

            // Apply pagination
            $paginatedResult = paginateOrGetAll($query, $request);

            // Map the items using Collection's map() method
            if (isset($paginatedResult['data']) && $paginatedResult['data'] instanceof \Illuminate\Support\Collection) {
                $paginatedResult['data'] = $paginatedResult['data']->map(function ($complaint) {
                    return [
                        'id' => $complaint->id ?? '',
                        'name' => $complaint->client->name ?? '',
                        'phone' => $complaint->client->phone ?? '',
                        'branch' => [
                            'name_ar' => $complaint->order && $complaint->order->branch
                                ? $complaint->order->branch->name_ar
                                : '',
                            'name_en' => $complaint->order && $complaint->order->branch
                                ? $complaint->order->branch->name_en
                                : ''
                        ],
                        'branch_lang' => $complaint->order && $complaint->order->branch
                                ? $complaint->order->branch->name
                                : '',
                        'rate' => $complaint->rate ?? null,
                        'invoice_number' => $complaint->order->invoice_number ?? '',
                        'order_number' => $complaint->order->order_number ?? '',
                        'status_lang' => __('complaints.' . $complaint->status) ?? '',
                        'status' => $complaint->status ?? '',
                        'created_at' => $complaint->created_at?->format('Y-m-d') ?? '',
                        'complain' => $complaint->complain ?? '',
                        'comment' => $complaint->comment ?? '',
                        // Add other complaint-specific fields as needed
                        'client_id' => $complaint->client_id ?? null,
                        'order_id' => $complaint->order_id ?? null,

                    ];
                })->all(); // Convert back to array
            }

            // Add status-based totals to the response
            $paginatedResult['totals'] = [
                'pending' => $pendingCount,
                'inprogress' => $inprogressCount,
                'solved' => $solvedCount,
                // 'total_complaints' => $pendingCount + $inprogressCount + $solvedCount,
            ];
            $responseData['data'] = [
                'complaints' => $paginatedResult['data'],
                'totals' => $paginatedResult['totals'],
            ];
            $responseData['meta'] = [
                'meta' => $paginatedResult['meta'],
            ];

            return ResponseWithSuccessDataPaginated($lang, $responseData, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $exists = Complaint::where('id', $id)->exists();
        App::setLocale($lang);

        if (!$exists) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $response = $this->complaintsService->show($request, $id);

        // If the service returns a response object, extract the data
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $responseData = $response->getData(true);

            // Check if the service returned an error
            if (isset($responseData['success']) && !$responseData['success']) {
                return $response; // Return the error response as-is
            }

            return ResponseWithSuccessData($lang, $responseData['data'] ?? $responseData, 1);
        }

        // If the service returns raw data, use it directly
        return ResponseWithSuccessData($lang, $response, 1);
    }
}
