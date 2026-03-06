<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\VehicleSetting;
use App\Services\SettingsServices\VehicleSettingService;
use Illuminate\Http\Request;

class VehicleSettingController extends Controller
{
    protected $VehicleSettingService;
    protected $checkToken;
    protected $lang;

    public function __construct(VehicleSettingService $VehicleSettingService)
    {
        $this->VehicleSettingService = $VehicleSettingService;
        $this->checkToken = false;
        $this->lang = app()->getLocale();
    }

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $response = $this->VehicleSettingService->index($request);
            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);        
        }
    }

    public function show(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $response = $this->VehicleSettingService->show($request);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);        
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $response = $this->VehicleSettingService->update($request, $id);
            return ResponseWithSuccessData($lang, $response, 1);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);        
        }
    }
    
}
