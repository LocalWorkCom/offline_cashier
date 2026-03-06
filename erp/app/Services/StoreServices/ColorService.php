<?php

namespace App\Services\StoreServices;

use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class ColorService
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lang = app()->getLocale();
        try {
            $data = Color::query();
            $fields = ['name_site', 'name'];
            $fields_visible = ['name_ar', 'name_en'];
            return $response = paginateOrGetAll($data, $request, $fields, $fields_visible);
        } catch (\Exception $e) {
            Log::error('Error fetching color: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function store(Request $request)
    {
        $lang = app()->getLocale();
        try {
            // Validate the input including 'hexa_code'
            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|unique:colors,name_ar',
                'name_en' => 'required|string|unique:colors,name_en',
                'hexa_code' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
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
            $color = new Color();
            $color->name_ar = $name_ar;
            $color->name_en = $name_en;
            $color->hexa_code = $hexa_code; // Store the hex code
            $color->created_by = $created_by;
            $color->created_type = $created_type;
            $color->save();
            $color->makeHidden('name_site');
            return ResponseWithSuccessData($lang, $color, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching color: ' . $e->getMessage(), [
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
                'name_ar' => 'required|unique:colors,name_ar,' . $request->id,
                'name_en' => 'required|unique:colors,name_en,' . $request->id,
                'hexa_code' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $check_color = Color::where('id', $request->id)->first();
            if(!$check_color){
                return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            }

            $name_ar = $request->name_ar;
            $name_en = $request->name_en;
            $hexa_code = $request->hexa_code;
            
            $craeted = authActionSave();
            $modified_by = $craeted['by'];
            $modified_type = $craeted['type'];

            $check_color->name_ar = $name_ar;
            $check_color->name_en = $name_en;
            $check_color->hexa_code = $hexa_code; // Store the hex code
            $check_color->modified_by = $modified_by;
            $check_color->modified_type = $modified_type;
            $check_color->save();
            $check_color->makeHidden('name_site');
            return ResponseWithSuccessData($lang, $check_color, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching color: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function delete(Request $request, $id)
    {
        if(isset($request->lang)){
            $lang = $request->lang;
        }else{
            $lang = app()->getLocale();
        }
        try {

            $check_color = Color::where('id', $request->id)->first();
            if(!$check_color){
                return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            }

            $craeted = authActionSave();
            $deleted_by = $craeted['by'];
            $deleted_type = $craeted['type'];
            $check_color->deleted_by = $deleted_by;
            $check_color->deleted_type = $deleted_type;
            $check_color->save();
            $delete_color = $check_color->delete();

            return ResponseWithSuccessData($lang, [($lang == 'en' ? ['Deleted successfully!'] : ['تم الحذف بنجاح!'])], 1);
        } catch (\Exception $e) {
            Log::error('Error fetching color: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
}
