<?php

namespace App\Http\Controllers\Api\DashboardApis;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashPaymentSetting;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Services\SettingsServices\CashPaymentSettingService;

class CashPaymentSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $cashPaymentSettingService;
    public function __construct(CashPaymentSettingService $cashPaymentSettingService)
    {
        return $this->cashPaymentSettingService = $cashPaymentSettingService;
    }

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $response = $this->cashPaymentSettingService->index($request);
            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // try {
        $lang = $request->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        return $response = $this->cashPaymentSettingService->store($request);
        // } catch (\Exception $e) {
        //     return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        // }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $response = $this->cashPaymentSettingService->show($request);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $cash_setting = CashPaymentSetting::find($request->id);
            if (!$cash_setting) {
                return respondErrorData(
                    $lang == 'en' ? 'Cash Setting not found.' : 'الإعدادات النقدية غير موجودة.',
                    404
                );
            }
            return $response = $this->cashPaymentSettingService->update($request, $request->id);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $request['lang'] = $lang;
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $response = $this->cashPaymentSettingService->delete($request, $request->id);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
}
