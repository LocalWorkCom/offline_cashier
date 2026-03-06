<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use Illuminate\Http\Request;
use App\Models\SocialMediaInformationSetting;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\SettingsServices\SocialMediaInformationSettingService;

class SocialMediaInformationSettingController extends Controller
{
    protected $socialMediaInformationSettingService;

    public function __construct(SocialMediaInformationSettingService $socialMediaInformationSettingService)
    {
        $this->socialMediaInformationSettingService = $socialMediaInformationSettingService;
    }


    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        try {
            $response = $this->socialMediaInformationSettingService->index($request, false);
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
            $exists = SocialMediaInformationSetting::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $socialMediaInformationSetting = SocialMediaInformationSetting::with('companyProfileSetting')->findOrFail($id);
            return ResponseWithSuccessData($lang, $socialMediaInformationSetting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            $result = $this->socialMediaInformationSettingService->store($request, false);

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

        try {
            $result = $this->socialMediaInformationSettingService->update($request, $id, false);

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
            $exists = SocialMediaInformationSetting::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $response = $this->socialMediaInformationSettingService->destroy($request, $id, false);
            return $response;
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
