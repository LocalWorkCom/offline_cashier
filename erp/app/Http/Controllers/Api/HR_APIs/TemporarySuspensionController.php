<?php

namespace App\Http\Controllers\Api\HR_APIs;

use Illuminate\Http\Request;
use App\Models\TemporarySuspension;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\HR_Services\TemporarySuspensionService;

class TemporarySuspensionController extends Controller
{
    protected $temporarySuspensionService;

    public function __construct(TemporarySuspensionService $temporarySuspensionService)
    {
        $this->temporarySuspensionService = $temporarySuspensionService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            $result = $this->temporarySuspensionService->index($request);

            if (isset($result['my']) && isset($result['employees'])) {
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

            $paginatedResult = paginateOrGetAll($result, $request);
            return ResponseWithSuccessDataPaginated($lang, $paginatedResult, 1);
        } catch (\Exception $e) {

            // Ensure supporting_documents is properly formatted
            if (isset($result['data'])) {
                $result['data'] = collect($result['data'])->map(function ($suspension) {
                    if (isset($suspension['supporting_documents']) && is_string($suspension['supporting_documents'])) {
                        $suspension['supporting_documents'] = json_decode($suspension['supporting_documents'], true) ?? [];
                    }
                    return $suspension;
                })->toArray();
            }

            return ResponseWithSuccessDataPaginated($lang, $result, 1);
        } catch (\Exception $e) {
            // Log the error for debugging

            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function report(Request $request)
    {
        $lang = $request->header('lang', 'en');
        $user = auth()->user();

        try {
            // Get query from service
            $query = $this->temporarySuspensionService->report($request);

            // Fetch all data
            $suspensions = $query->get();

            // Group results into "my" and "employees"
            $data = [
                'my' => $suspensions->where('employee_id', $user->id)->values(),
                'employees' => $suspensions->where('employee_id', '!=', $user->id)->values(),
            ];

            // Meta info
            $meta = [
                'totalItems' => $suspensions->count(),
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


    public function add(Request $request)
    {
        $lang = $request->header('lang', 'en');

        // try {
        $response = $this->temporarySuspensionService->store($request);
        $responseData = $response->getData();

        if (isset($responseData->data)) {
            return ResponseWithSuccessData($lang, $responseData->data, 1);
        }
        return $response;
        // } catch (\Exception $e) {
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }

    public function update(Request $request, string $id)
    {
        $lang = $request->header('lang', 'en');

        try {
            $response = $this->temporarySuspensionService->update($request, $id);
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
