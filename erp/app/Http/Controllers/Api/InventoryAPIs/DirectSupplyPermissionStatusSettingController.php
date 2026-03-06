<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use Illuminate\Http\Request;
use App\Models\DirectSupplyPermissionStatusSetting;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Services\Inventory_Services\DirectSupplyPermissionStatusSettingService;

class DirectSupplyPermissionStatusSettingController extends Controller
{
    protected $DirectSupplyPermissionStatusSettingService;

    public function __construct(DirectSupplyPermissionStatusSettingService $DirectSupplyPermissionStatusSettingService)
    {
        $this->DirectSupplyPermissionStatusSettingService = $DirectSupplyPermissionStatusSettingService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        try {
            $response = $this->DirectSupplyPermissionStatusSettingService->index($request);

            // Now $response is a query builder, so paginateOrGetAll can work with it
            $result = paginateOrGetAll($response, $request, null);

            return ResponseWithSuccessDataPaginated($lang, $result, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function reorder(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            // Step 1: Validate request input (without exists)
            $validator = Validator::make($request->all(), [
                'order' => 'required|array',
                'order.*.id' => 'required|integer',
                'order.*.position' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            // Step 2: Check each ID manually before calling the service
            foreach ($request->order as $item) {
                $exists = DirectSupplyPermissionStatusSetting::where('id', $item['id'])->exists();
                if (!$exists) {
                    return respondError(
                        __('branch_menu_category.not_found') . ' (ID: ' . $item['id'] . ')',
                        404
                    );
                }
            }

            // Step 3: If all IDs exist, call the service to reorder
            $result = $this->DirectSupplyPermissionStatusSettingService->reorder($request);

            // If service returns error array
            if (is_array($result) && isset($result['code'])) {
                return response()->json($result, $result['code']);
            }

            return RespondWithSuccessRequest($lang, 1);
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

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            $result = $this->DirectSupplyPermissionStatusSettingService->store($request, false);

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
        if ($id >= 1 && $id <= 9) {
            return respondError(__('Cannot edit protected records (ID 1-9)'), 403);
        }
        try {
            $result = $this->DirectSupplyPermissionStatusSettingService->update($request, $id, false);

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

    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $exists = DirectSupplyPermissionStatusSetting::where('id', $id)->exists();
            App::setLocale($lang);
            if ($id >= 1 && $id <= 9) {
                return respondError(__('Cannot delete protected records (ID 1-9)'), 403);
            }
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $response = $this->DirectSupplyPermissionStatusSettingService->delete($request, $id, false);
            return $response;
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
