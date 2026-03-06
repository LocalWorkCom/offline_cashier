<?php

namespace App\Http\Controllers\API\HR_APIs;

use App\Http\Controllers\Controller;
use App\Services\HR_Services\jobTypeSettingService;
use Illuminate\Http\Request;

class JobTypesSettingController extends Controller
{
    protected $jobTypeSettingService;

    public function __construct(jobTypeSettingService $jobTypeSettingService)
    {
        $this->jobTypeSettingService = $jobTypeSettingService;
    }

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $jobTypeSetting = $this->jobTypeSettingService->getAlljobTypeSettingService($request);

            if (!$jobTypeSetting) {
                return response()->json([
                    'status' => false,
                    'message' => $lang == 'en' ? 'No Job Types Settings found' : 'لم يتم العثور على إعدادات أنواع الوظائف',
                    'code' => 200,
                    'data' => null
                ], 200);
            }

            return ResponseWithSuccessDataPaginated($lang, $jobTypeSetting, 1);
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
            $jobTypeSetting = $this->jobTypeSettingService->createjobTypeSettingService(
                $request
            );

            $data = $jobTypeSetting->original;
            if ($data) {
                return $data;
            }

            return ResponseWithSuccessData($lang, $jobTypeSetting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');

            $jobTypeSetting = $this->jobTypeSettingService->getjobTypeSettingById($id);
            $data = $jobTypeSetting->original;
            if ($data) {
                if ($data['code'] === 404) {
                    return RespondWithBadRequestData($lang, 8);
                } else {
                    return $data;
                }
            }

            return ResponseWithSuccessData($lang, $jobTypeSetting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');

            $jobTypeSetting = $this->jobTypeSettingService->updatejobTypeSetting($request, $id);
            $data = $jobTypeSetting->original;
            if ($data) {
                if ($data['code'] === 404) {
                    return RespondWithBadRequestData($lang, 8);
                } else {
                    return $data;
                }
            }

            return ResponseWithSuccessData($lang, $jobTypeSetting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');

        try {
            $jobTypeSetting = $this->jobTypeSettingService->deleteJobTypeSetting($id);

            $data = $jobTypeSetting->original;
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
