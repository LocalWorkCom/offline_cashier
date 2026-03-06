<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use Illuminate\Http\Request;
use App\Models\EmployeeStatus;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\HR_Services\EmployeeStatusService;

class EmployeeStatusController extends Controller
{
    protected $EmployeeStatusService;

    public function __construct(EmployeeStatusService $EmployeeStatusService)
    {
        $this->EmployeeStatusService = $EmployeeStatusService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        try {
            $response = $this->EmployeeStatusService->index($request, false);

            // Now $response is a query builder, so paginateOrGetAll can work with it
            $result = paginateOrGetAll($response, $request, null);

            return ResponseWithSuccessDataPaginated($lang, $result, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $exists = EmployeeStatus::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $BankName = EmployeeStatus::findOrFail($id);
            return ResponseWithSuccessData($lang, $BankName, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            $result = $this->EmployeeStatusService->store($request, false);

            // Check if result is an array (error response)
            if (is_array($result) && isset($result['code'])) {
                // Return the error response directly without wrapping
                return response()->json($result, $result['code']);
            }

            // If it's a BusinessActivity object, return success response
            return ResponseWithSuccessData($lang, $result, 1);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => 'Internal server error',
                'data' => null,
                'errorData' => ['error' => $e->getMessage()],
                'validation_type' => false
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        // Prevent deletion of IDs 1 to 7
        if ($id >= 1 && $id <= 7) {
            return respondError(__('Cannot edit protected records (ID 1-7)'), 403);
        }
        try {
            $result = $this->EmployeeStatusService->update($request, $id, false);

            // Check if result is an array (error response)
            if (is_array($result) && isset($result['code'])) {
                // Return the error response directly without wrapping
                return response()->json($result, $result['code']);
            }

            // If it's a BusinessActivity object, return success response
            return ResponseWithSuccessData($lang, $result, 1);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => 'Internal server error',
                'data' => null,
                'errorData' => ['error' => $e->getMessage()],
                'validation_type' => false
            ], 500);
        }
    }

    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $exists = EmployeeStatus::where('id', $id)->exists();
            App::setLocale($lang);
            if ($id >= 1 && $id <= 7) {
                return respondError(__('Cannot edit protected records (ID 1-7)'), 403);
            }
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $response = $this->EmployeeStatusService->delete($request, $id, false);
            return $response;
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
