<?php

namespace App\Http\Controllers\API\HR_APIs;

use App\Http\Controllers\Controller;
use App\Services\HR_Services\SalaryAdvanceSettingService;
use Illuminate\Http\Request;

class SalaryAdvanceSettingController extends Controller
{
    protected $SalaryAdvanceSettingService;

    public function __construct(SalaryAdvanceSettingService $SalaryAdvanceSettingService)
    {
        $this->SalaryAdvanceSettingService = $SalaryAdvanceSettingService;
    }

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $SalaryAdvanceSetting = $this->SalaryAdvanceSettingService->getAllSalarySettings($request);

            if (!$SalaryAdvanceSetting) {
                return response()->json([
                    'status' => false,
                    'message' => $lang == 'en' ? 'No Salary Advance Settings found' : 'لم يتم العثور على إعدادات السلفة',
                    'code' => 200,
                    'data' => null
                ], 200);
            }
            return ResponseWithSuccessDataPaginated($lang, $SalaryAdvanceSetting, 1);

            // $responseData = $SalaryAdvanceSetting->map(function ($setting) {
            //     return [
            //         'id' => (int)$setting->id,
            //         'max_advance_limit' => (int)$setting->max_advance_limit,
            //         'percentage_type' => $setting->percentage_type,
            //     ];
            // });

            // return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $salaryAdvanceSettings = $this->SalaryAdvanceSettingService->createSalaryAdvanceSetting(
                $request
            );

            $data = $salaryAdvanceSettings->original;
            if ($data) {
                return $data;
            }

            return ResponseWithSuccessData($lang, $salaryAdvanceSettings, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');

            $updatedSettings = $this->SalaryAdvanceSettingService->getSalaryAdvanceSettingById($id);
            $data = $updatedSettings->original;
            if ($data) {
                if ($data['code'] === 404) {
                    return RespondWithBadRequestData($lang, 8);
                } else {
                    return $data;
                }
            }

            return ResponseWithSuccessData($lang, $updatedSettings, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');

            $updatedSettings = $this->SalaryAdvanceSettingService->updateSalaryAdvanceSetting($request, $id);
            $data = $updatedSettings->original;
            if ($data) {
                if ($data['code'] === 404) {
                    return RespondWithBadRequestData($lang, 8);
                } else {
                    return $data;
                }
            }

            return ResponseWithSuccessData($lang, $updatedSettings, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');

        try {
            $deleteSalaryAdvanceSettings = $this->SalaryAdvanceSettingService->deleteSalaryAdvanceSetting($id);

            $data = $deleteSalaryAdvanceSettings->original;
            if ($data) {
                if ($data['code'] === 404) {
                    return RespondWithBadRequestData($lang, 8);
                } else {
                    return $data;
                }
            }

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
