<?php

namespace App\Http\Controllers\Api\HR_APIs;

use Illuminate\Http\Request;
use App\Models\PerformanceReview;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\HR_Services\PerformanceReviewService;

class PerformanceReviewsController extends Controller
{
    protected $performanceReviewService;

    public function __construct(PerformanceReviewService $performanceReviewService)
    {
        $this->performanceReviewService = $performanceReviewService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            // Get the data from service
            $result = $this->performanceReviewService->index($request, false);

            // Check if the result already has the structured format (my/employees)
            if (isset($result['my']) && isset($result['employees'])) {
                // Combine them into data + add meta info
                $data = [
                    'my' => $result['my'],
                    'employees' => $result['employees'],
                ];

                $meta = [
                    'totalItems' => count($result['employees']) + count($result['my']),
                    'itemsPerPage' => 'all',
                    'totalPages' => 1,
                    'currentPage' => 1,
                ];

                return response()->json([
                    'status' => true,
                    'message' => __('messages.success', [], $lang),
                    'code' => 200,
                    'data' => $data,
                    'meta' => $meta,
                ], 200);
            }

            // If it's not structured, use pagination helper
            $paginatedResult = paginateOrGetAll($result, $request);

            return ResponseWithSuccessDataPaginated($lang, $paginatedResult, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function report(Request $request)
    {
        $lang = $request->header('lang', 'en');
        $user = auth()->user();

        try {
            // Get query from service
            $query = $this->performanceReviewService->report($request);

            // Fetch all data (no pagination here)
            $reviews = $query->get();

            // Group into "my" and "employees"
            $my = $reviews->where('employee_id', $user->id)->values();
            $employees = $reviews->where('employee_id', '!=', $user->id)->values();

            // Combine in the same format as index()
            $data = [
                'my' => $my,
                'employees' => $employees,
            ];

            $meta = [
                'totalItems' => $reviews->count(),
                'itemsPerPage' => 'all',
                'totalPages' => 1,
                'currentPage' => 1,
            ];

            return response()->json([
                'status' => true,
                'message' => __('messages.success', [], $lang),
                'code' => 200,
                'data' => $data,
                'meta' => $meta,
            ], 200);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }


    // public function report(Request $request)
    // {
    //     $lang = $request->header('lang', 'en');

    //     $query = $this->performanceReviewService->report($request);

    //     // Apply pagination
    //     $result = paginateOrGetAll($query, $request);

    //     return ResponseWithSuccessDataPaginated($lang, $result, 1);
    // }

    public function add(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            $response = $this->performanceReviewService->store($request);
            $responseData = $response->getData();

            if (isset($responseData->data)) {
                return ResponseWithSuccessData($lang, $responseData->data, 1);
            }
            return $response;
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
