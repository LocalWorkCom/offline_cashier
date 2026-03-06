<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\WasteReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class WasteReasonController extends Controller
{
    public function index(Request $request)
    {
        $employee = auth('employee')->user();

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $wasteReasons = WasteReason::whereNull('deleted_at')
            ->select('id', 'name_ar', 'name_en', 'created_at', 'updated_at');
        $result = paginateOrGetAll($wasteReasons, $request, null);

        return ResponseWithSuccessDataPaginated($lang, $result, 1);
    }

    public function store(Request $request)
    {
        $employee = auth('employee')->user();

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), []);
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255|unique:waste_reasons,name_ar',
            'name_en' => 'required|string|max:255|unique:waste_reasons,name_en'
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.validation_error'), 400, $validator->errors());
        }
        $wasteReason = WasteReason::create($validator->validated());
        return ResponseWithSuccessData($lang, $wasteReason, 1);
    }

    public function show(Request $request, $id)
    {
        $employee = auth('employee')->user();

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $wasteReason = WasteReason::select('id',  'name_ar', 'name_en', 'created_at', 'updated_at')->find($id);
        if (!$wasteReason) {
            return respondError($lang == 'en' ? 'Waste reason not found.' : 'سبب الهدر غير موجود.', 404);
        }
        return ResponseWithSuccessData($lang, $wasteReason, 1);
    }

    public function update(Request $request, $id)
    {
        $employee = auth('employee')->user();

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $wasteReason = WasteReason::find($id);
        if (!$wasteReason) {
            return respondError($lang == 'en' ? 'Waste reason not found.' : 'سبب الهدر غير موجود.', 404);
        }
        if ($id >= 1 && $id <= 3) {
            return respondError(__('Cannot update protected records (ID 1-3)'), 403);
        }
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255|unique:waste_reasons,name_ar,' . $id,
            'name_en' => 'required|string|max:255|unique:waste_reasons,name_en,' . $id,

            // 'status' => 'required|boolean',
        ], [
            'name_ar.required' => $lang == 'en' ? 'The name field is required.' : 'حقل الاسم مطلوب.',
            'name_ar.string' => $lang == 'en' ? 'The name must be a string.' : 'الاسم يجب أن يكون نصًا.',
            'name_ar.max' => $lang == 'en' ? 'The name may not be greater than 255 characters.' : 'الاسم لا يجب أن يتجاوز 255 حرفًا.',
            'name_ar.unique' => $lang == 'en' ? 'The name must be unique.' : 'الاسم يجب أن يكون فريدًا.',
            'name_en.required' => $lang == 'en' ? 'The name field is required.' : 'حقل الاسم مطلوب.',
            'name_en.string' => $lang == 'en' ? 'The name must be a string.' : 'الاسم يجب أن يكون نصًا.',
            'name_en.max' => $lang == 'en' ? 'The name may not be greater than 255 characters.' : 'الاسم لا يجب أن يتجاوز 255 حرفًا.',
            'name_en.unique' => $lang == 'en' ? 'The name must be unique.' : 'الاسم يجب أن يكون فريدًا.',
        ]);



        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $wasteReason->update($validator->validated());
        return ResponseWithSuccessData($lang, $wasteReason, 1);
    }

    public function destroy(Request $request, $id)
    {
        $employee = auth('employee')->user();

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $wasteReason = WasteReason::findOrFail($id);
        if (!$wasteReason) {
            return respondError($lang == 'en' ? 'Waste reason not found.' : 'سبب الهدر غير موجود.', 404);
        }
        if ($id >= 1 && $id <= 3) {
            return respondError(__('Cannot delete protected records (ID 1-3)'), 403);
        }
        if ($wasteReason->wasteReportItems()->exists()) {
        return respondError(__('validation.cannotDeleteReasonInUse'), 400);
    }
        $wasteReason->deleted_at = now();
        $wasteReason->save();
        return ResponseWithSuccessData($lang, $wasteReason, 1);
    }
}
