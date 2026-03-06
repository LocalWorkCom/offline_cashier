<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\City;
use App\Models\User;
use App\Services\SettingsServices\CitiesService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    protected $CitiesService;
    protected $checkToken;

    public function __construct(CitiesService $CitiesService)
    {
        $this->CitiesService = $CitiesService;
        $this->checkToken = false;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        try {
            $response = $this->CitiesService->index($request, false);

            if(isset($request->country_id))
            {
                $response->where('country_id', $request->country_id);
            }
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
            $result = $this->CitiesService->store($request, false);

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
    //         $exists = City::where('id', $id)->exists();
    //         App::setLocale($lang);

    //         if (!$exists) {
    //             return respondError(__('branch_menu_category.not_found'), 404);
    //         }

    //         $Cities = City::findOrFail($id);
    //         return ResponseWithSuccessData($lang, $Cities, 1);
    //     } catch (\Exception $e) {
    //         return RespondWithBadRequestData($lang, 2);
    //     }
    // }
    // public function update(Request $request, $id)
    // {
    //     $lang = $request->header('lang', 'ar');

    //     try {
    //         $result = $this->CitiesService->update($request, $id, false);

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
            $exists = City::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $response = $this->CitiesService->destroy($id);
            return $response;
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
