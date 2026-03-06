<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Area;
use App\Models\User;
use App\Services\SettingsServices\AreaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RegionsController extends Controller
{
    protected $AreaService;
    protected $checkToken;

    public function __construct(AreaService $AreaService)
    {
        $this->AreaService = $AreaService;
        $this->checkToken = false;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        try {
            $response = $this->AreaService->index($request, false);

            // Now $response is a query builder, so paginateOrGetAll can work with it
            $result = paginateOrGetAll($response, $request, null);

            return ResponseWithSuccessDataPaginated($lang, $result, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            $result = $this->AreaService->store($request, false);

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
    // public function show(Request $request, $id)
    // {
    //     $lang = $request->header('lang', 'ar');

    //     try {
    //         $exists = Area::where('id', $id)->exists();
    //         App::setLocale($lang);

    //         if (!$exists) {
    //             return respondError(__('branch_menu_category.not_found'), 404);
    //         }

    //         $Area = Area::findOrFail($id);
    //         return ResponseWithSuccessData($lang, $Area, 1);
    //     } catch (\Exception $e) {
    //         return RespondWithBadRequestData($lang, 2);
    //     }
    // }
    // public function update(Request $request, $id)
    // {
    //     $lang = $request->header('lang', 'ar');

    //     try {
    //         $result = $this->AreaService->update($request, $id, false);

    //         // Check if result is an array (error response)
    //         if (is_array($result) && isset($result['code'])) {
    //             // Return the error response directly without wrapping
    //             return response()->json($result, $result['code']);
    //         }

    //         // If it's a BusinessActivity object, return success response
    //         return ResponseWithSuccessData($lang, $result, 1);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'code' => 500,
    //             'status' => false,
    //             'message' => 'Internal server error',
    //             'data' => null,
    //             'errorData' => ['error' => $e->getMessage()],
    //             'validation_type' => false
    //         ], 500);
    //     }
    // }

    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $exists = Area::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $response = $this->AreaService->destroy($id);
            return $response;
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
