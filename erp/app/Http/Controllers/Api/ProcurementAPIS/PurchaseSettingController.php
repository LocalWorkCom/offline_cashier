<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\PurchaseSetting;
use Illuminate\Support\Facades\Validator;

class PurchaseSettingController extends Controller
{
    public function show(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $setting = PurchaseSetting::first();

        if (!$setting) {
            // Create default row automatically
            $setting = PurchaseSetting::create([
                'auto_creation_po' => 0,
                'notify_PM' => 0,
                'notify_EMPO' => 0,
            ]);
        }

        return ResponseWithSuccessData($lang, $setting, 1);
    }

    public function update(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'auto_creation_po' => 'nullable|boolean',
            'notify_PM'        => 'nullable|boolean',
            'notify_EMPO'      => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $employee = auth('employee')->user();
        $setting = PurchaseSetting::first();

        if (!$setting) {
            $setting = new PurchaseSetting();
        }

        $setting->auto_creation_po = $request->auto_creation_po ?? $setting->auto_creation_po;
        $setting->notify_PM        = $request->notify_PM ?? $setting->notify_PM;
        $setting->notify_EMPO      = $request->notify_EMPO ?? $setting->notify_EMPO;

        $setting->updated_by = $employee->id;
        $setting->save();

        return ResponseWithSuccessData($lang, $setting, 1);
    }
}
