<?php

namespace App\Services\SettingsServices;

use App\Models\CashPaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CashPaymentSettingService
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lang = app()->getLocale();
        try {
            $user = auth()->guard('employee')->user();
            $data = CashPaymentSetting::with('branch')->has('branch');
            if ($user->flag == "branch manager" && $user->branch_id) {
                $data->where('branch_id', $user->branch_id);
            }
            $fields = [];
            return paginateOrGetAll($data, $request, $fields);
        } catch (\Exception $e) {
            Log::error('Error fetching cash payment setting: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function store(Request $request)
    {
        $lang = app()->getLocale();
        // try {
        $validator = Validator::make($request->all(), [
            'branch_id' => [
                'required',
                'numeric',
                'min:1',
                'exists:branches,id',
                Rule::unique('cash_payment_settings', 'branch_id')->whereNull('deleted_at'),
            ],
            'min_cash' => 'required|numeric|min:0',
            'max_cash' => 'required|numeric|min:0',
            'enforce_limit' => 'nullable|boolean|in:0,1',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;
        $hexa_code = $request->hexa_code;

        $craeted = authActionSave();
        $created_by = $craeted['by'];
        $created_type = $craeted['type'];

        // Create the new color
        $cash_payment_setting = new CashPaymentSetting();
        $cash_payment_setting->branch_id = $request->branch_id;
        $cash_payment_setting->min_cash = $request->min_cash;
        $cash_payment_setting->max_cash = $request->max_cash;
        $cash_payment_setting->enforce_limit = $request->enforce_limit;
        $cash_payment_setting->created_by = $created_by;
        $cash_payment_setting->created_by_type = $created_type;
        $cash_payment_setting->save();
        $cash_payment_setting->makeHidden('name_site');
        return ResponseWithSuccessData($lang, $cash_payment_setting, 1);
        // } catch (\Exception $e) {
        //     Log::error('Error fetching cash payment setting: ' . $e->getMessage(), [
        //         'stack' => $e->getTraceAsString(),
        //     ]);
        //     return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        // }
    }

    public function show(Request $request)
    {
        $lang = app()->getLocale();
        try {
            $data = CashPaymentSetting::where('id', $request->id)->with('branch')->first();
            if (!$data) {
                return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            }
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching vehicle settings: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();
        try {
            // Validate the input including 'hexa_code'
            $validator = Validator::make($request->all(), [
                'branch_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    'exists:branches,id',
                    Rule::unique('cash_payment_settings', 'branch_id')->ignore($id)->whereNull('deleted_at'),
                ],
                'min_cash' => 'required|numeric|min:0',
                'max_cash' => 'required|numeric|min:0',
                'enforce_limit' => 'nullable|boolean|in:0,1',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $cash_payment_setting = CashPaymentSetting::where('id', $request->id)->first();
            if (!$cash_payment_setting) {
                return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            }
            $craeted = authActionSave();
            $modified_by = $craeted['by'];
            $modified_type = $craeted['type'];

            $cash_payment_setting = CashPaymentSetting::find($id);
            $cash_payment_setting->branch_id = $request->branch_id;
            $cash_payment_setting->min_cash = $request->min_cash;
            $cash_payment_setting->max_cash = $request->max_cash;
            $cash_payment_setting->enforce_limit = $request->enforce_limit;
            $cash_payment_setting->modified_by = $modified_by;
            $cash_payment_setting->modified_by_type = $modified_type;
            $cash_payment_setting->save();

            return ResponseWithSuccessData($lang, $cash_payment_setting, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching cash payment setting: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function delete(Request $request, $id)
    {
        if (isset($request->lang)) {
            $lang = $request->lang;
        } else {
            $lang = app()->getLocale();
        }

        try {

            $cash_payment_setting = CashPaymentSetting::where('id', $request->id)->first();
            if (!$cash_payment_setting) {
                return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            }

            $craeted = authActionSave();
            $deleted_by = $craeted['by'];
            $deleted_type = $craeted['type'];
            $cash_payment_setting->deleted_by = $deleted_by;
            $cash_payment_setting->deleted_by_type = $deleted_type;
            $cash_payment_setting->save();
            $delete_color = $cash_payment_setting->delete();

            return ResponseWithSuccessData($lang, [($lang == 'en' ? ['Deleted successfully!'] : ['تم الحذف بنجاح!'])], 1);
        } catch (\Exception $e) {
            Log::error('Error fetching color: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
}
