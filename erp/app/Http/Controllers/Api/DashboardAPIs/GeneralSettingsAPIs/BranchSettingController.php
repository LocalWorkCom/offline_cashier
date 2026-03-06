<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use Illuminate\Http\Request;
use App\Models\BranchSetting;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\SettingsServices\BranchSettingService;

class BranchSettingController extends Controller
{
    protected $branchSettingService;

    public function __construct(BranchSettingService $branchSettingService)
    {
        $this->branchSettingService = $branchSettingService;
    }


    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        app()->setLocale($lang);
        try {
            // Get the query builder from service
            $response = $this->branchSettingService->index($request, false);

            // Apply pagination to the query
            $result = paginateOrGetAll($response, $request, null);

            $result['data'] = $result['data']->map(function($setting) {
                $setting->deposit_without_order_deduction_policy = __('branch_settings.' . strtolower($setting->deposit_without_order_deduction_policy));
                $setting->deposit_with_order_deduction_policy = __('branch_settings.' . strtolower($setting->deposit_with_order_deduction_policy));
                $setting->full_paid_order_deduction_policy = __('branch_settings.' . strtolower($setting->full_paid_order_deduction_policy));

                return $setting;
            });

            return ResponseWithSuccessDataPaginated($lang, $result, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function add(Request $request)
    {
        $lang = $request->header('lang', 'en');
        app()->setLocale($lang);
        try {
            $response = $this->branchSettingService->add($request);
            $responseData = $response->original;

            if (isset($responseData['data'])) {
                return ResponseWithSuccessData($lang, $responseData['data'], 1);
            }
            return $response;
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function edit(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            // Check if record exists
            if (!BranchSetting::where('id', $id)->exists()) {
                App::setLocale($lang);
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            // Process the update through service
            return $this->branchSettingService->update($request, $id, false);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $exists = BranchSetting::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $response = $this->branchSettingService->delete($request, $id, false);
            return $response;
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        try {
            $exists = BranchSetting::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $branchSetting = BranchSetting::with(['branch' => function ($query) {
                $query->select('id', 'name_ar', 'name_en');
            }])->findOrFail($id);
            $branchSetting->deposit_without_order_deduction_policy = __('branch_settings.' . strtolower($branchSetting->deposit_without_order_deduction_policy));
            $branchSetting->deposit_with_order_deduction_policy = __('branch_settings.' . strtolower($branchSetting->deposit_with_order_deduction_policy));
            $branchSetting->full_paid_order_deduction_policy = __('branch_settings.' . strtolower($branchSetting->full_paid_order_deduction_policy));

            return ResponseWithSuccessData($lang, $branchSetting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
