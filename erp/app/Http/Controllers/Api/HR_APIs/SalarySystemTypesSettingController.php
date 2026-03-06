<?php

namespace App\Http\Controllers\API\HR_APIs;

use App\Http\Controllers\Controller;
use App\Services\HR_Services\SalarySystemTypesSettingService;
use Illuminate\Http\Request;

class SalarySystemTypesSettingController extends Controller
{
    protected $SalarySystemTypesSettingService;

    public function __construct(SalarySystemTypesSettingService $SalarySystemTypesSettingService)
    {
        $this->SalarySystemTypesSettingService = $SalarySystemTypesSettingService;
    }

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $SalarySystemTypesSetting = $this->SalarySystemTypesSettingService->getAllSalarySettings($request);

            if (!$SalarySystemTypesSetting) {
                return response()->json([
                    'status' => false,
                    'message' => $lang == 'en' ? 'No Salary System Types Settings found' : 'لم يتم العثور على إعدادات أنواع نظام الرواتب',
                    'code' => 200,
                    'data' => null
                ], 200);
            }

            return ResponseWithSuccessDataPaginated($lang, $SalarySystemTypesSetting, 1);
            // $responseData = $SalarySystemTypesSetting->map(function ($setting) {
            //     return [
            //         'id' => (int)$setting->id,
            //         'name' => $setting->name
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
            $SalarySystemTypesSetting = $this->SalarySystemTypesSettingService->createSalarySystemTypesSetting(
                $request
            );

            $data = $SalarySystemTypesSetting->original;
            if ($data) {
                return $data;
            }

            return ResponseWithSuccessData($lang, $SalarySystemTypesSetting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');

            $SalarySystemTypesSetting = $this->SalarySystemTypesSettingService->getSalarySystemTypesSettingById($id);
            $data = $SalarySystemTypesSetting->original;
            if ($data) {
                if ($data['code'] === 404) {
                    return RespondWithBadRequestData($lang, 8);
                } else {
                    return $data;
                }
            }

            return ResponseWithSuccessData($lang, $SalarySystemTypesSetting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');

            $SalarySystemTypesSetting = $this->SalarySystemTypesSettingService->updateSalarySystemTypesSetting($request, $id);
            $data = $SalarySystemTypesSetting->original;
            if ($data) {
                if ($data['code'] === 404) {
                    return RespondWithBadRequestData($lang, 8);
                } else {
                    return $data;
                }
            }

            return ResponseWithSuccessData($lang, $SalarySystemTypesSetting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
