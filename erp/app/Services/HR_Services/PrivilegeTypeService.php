<?php


namespace App\Services\HR_Services;

use App\Models\PrivilegeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PrivilegeTypeService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function index(Request $request)
    {
        $lang = app()->getLocale();
        try {
            return $data = PrivilegeType::query();
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function show($request, $id)
    {
        try {
            $leave_type = PrivilegeType::where('id', $id)->first();
            if (!$leave_type) {
                return 0;
            }
            $leave_type->makeHidden(['name_ar', 'name_en', 'name_site', 'reason_ar', 'reason_en'])->makeVisible(['name', 'reason']);
            return $leave_type;
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function add(Request $request)
    {
        $lang = app()->getLocale();
        try {
            // Validate the input
            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|unique:privilege_types,name_ar',
                'name_en' => 'required|string|unique:privilege_types,name_en',
                'reason_ar' => 'nullable|string',
                'reason_en' => 'nullable|string',
                'is_active' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $craeted = authActionSave();
            $created_by = $craeted['by'];
            $created_by_type = $craeted['type'];
            // Create the new leave
            $PrivilegeType = new PrivilegeType();
            $PrivilegeType->name_ar = $request->name_ar;
            $PrivilegeType->name_en = $request->name_en;
            $PrivilegeType->reason_ar = $request->reason_ar ?? null;
            $PrivilegeType->reason_en = $request->reason_en ?? null;
            $PrivilegeType->created_by = $created_by;
            $PrivilegeType->created_by_type = $created_by_type;
            $PrivilegeType->is_active = $request->is_active ?? 1;
            $PrivilegeType->save();
            $PrivilegeType->makeHidden('name', 'name_site', 'reason');
            $PrivilegeType->makeVisible('name_ar', 'name_en');
            return ResponseWithSuccessData($lang, $PrivilegeType, 1);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function edit(Request $request, $id)
    {
        $lang = app()->getLocale();
        try {
            // Validate the input
            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|unique:privilege_types,name_ar,' . $id,
                'name_en' => 'required|string|unique:privilege_types,name_en,' . $id,
                'reason_ar' => 'nullable|string',
                'reason_en' => 'nullable|string',
                'is_active' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $PrivilegeType = PrivilegeType::where('id', $id)->first();
            if (!$PrivilegeType) {
                $message = $lang === 'ar' ? 'العنصر  غير موجود' : 'This item is not found';
                return respondError($message, 404);
            }
            $name_ar = $request->name_ar;
            $name_en = $request->name_en;

            $craeted = authActionSave();
            $modified_by = $craeted['by'];
            $modified_by_type = $craeted['type'];

            $PrivilegeType->name_ar = $name_ar;
            $PrivilegeType->name_en = $name_en;
            $PrivilegeType->reason_ar = $request->reason_ar ?? $PrivilegeType->reason_ar;
            $PrivilegeType->reason_en = $request->reason_en ?? $PrivilegeType->reason_en;
            $PrivilegeType->modified_by = $modified_by;
            $PrivilegeType->modified_by_type = $modified_by_type;
            $PrivilegeType->is_active = $request->is_active ?? $PrivilegeType->is_active;
            $PrivilegeType->save();
            $PrivilegeType->makeHidden('name', 'name_site', 'reason');
            $PrivilegeType->makeVisible('name_ar', 'name_en');
            return ResponseWithSuccessData($lang, $PrivilegeType, 1);
        } catch (\Exception $e) {
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

            $PrivilegeType = PrivilegeType::where('id', $id)->first();
            if (!$PrivilegeType) {
                $message = $lang === 'ar' ? 'العنصر  غير موجود' : 'This item is not found';
                return respondError($message, 404);
            }

            // $check_PrivilegeType_setting = JobRelatedPenalty::where('privilage_type_id', $id)->exists();
            // if($check_PrivilegeType_setting){
            //     return respondErrorData(($lang == 'en' ? ['Can nott delete this item'] : ['لا تسطيع الحذف']), 400, $lang == 'en' ? ['Cannot delete this item because related with other table'] : ['لا يمكن حذف هذا العنصر لأنه مرتبط بجدول اخر']);
            // }

            $craeted = authActionSave();
            $deleted_by = $craeted['by'];
            $deleted_by_type = $craeted['type'];
            $PrivilegeType->is_active = 0;
            $PrivilegeType->deleted_by = $deleted_by;
            $PrivilegeType->deleted_by_type = $deleted_by_type;
            $PrivilegeType->save();
            $PrivilegeType->delete();

            return RespondWithSuccessRequest($this->lang, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching color: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
}
