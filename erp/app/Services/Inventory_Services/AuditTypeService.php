<?php


namespace App\Services\Inventory_Services;

use App\Models\AuditType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuditTypeService
{

    public function index(Request $request)
    {
        $lang = app()->getLocale();

        $reasons = AuditType::get();

        return ResponseWithSuccessData($lang, $reasons, 1);
    }
    // public function show($id)
    // {
    //     $lang = app()->getLocale();

    //     $reasons = AuditType::find($id);
    //     if (!$reasons) {
    //         return RespondWithBadRequestData($lang, 8);
    //     }

    //     return ResponseWithSuccessData($lang, $reasons, 1);
    // }
    // public function store(Request $request)
    // {
    //     $lang = app()->getLocale();

    //     $validator = Validator::make($request->all(), [
    //         'name_ar' => 'required|string',
    //         'name_en' => 'required|string',
    //         'scheduled_regularly' => 'required_if:name_en, Periodic Audits|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return RespondWithBadRequestWithData($validator->errors());
    //     }

    //     $name_ar = $request->name_ar;
    //     $name_en = $request->name_en;

    //     // if (CheckExistColumnValue('reason_purchase_requests', 'name_ar', $name_ar) || CheckExistColumnValue('reason_purchase_requests', 'name_en', $name_en)) {
    //     //     return RespondWithBadRequest($lang, 9);
    //     // }

    //     $educationLevel = new AuditType();
    //     $educationLevel->name_ar = $name_ar;
    //     $educationLevel->name_en = $name_en;
    //     $educationLevel->created_by = authActionSave()['by'];
    //     $educationLevel->created_by_type = authActionSave()['type'];
    //     $educationLevel->save();

    //     return RespondWithSuccessRequest($lang, 1);
    // }

    // public function update(Request $request, $id)
    // {
    //     $lang = app()->getLocale();

    //     $validator = Validator::make($request->all(), [
    //         'name_ar' => 'required|string',
    //         'name_en' => 'required|string',
    //         'scheduled_regularly' => 'required_if:name_en, Periodic Audits|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return RespondWithBadRequestWithData($validator->errors());
    //     }

    //     $reasons = AuditType::find($id);
    //     if (!$reasons) {
    //         return RespondWithBadRequestData($lang, 8);
    //     }

    //     if ($reasons->name_ar == $request->name_ar && $reasons->name_en == $request->name_en && $reasons->scheduled_regularly == $request->scheduled_regularly) {
    //         return RespondWithBadRequestData($lang, 10);
    //     }

    //     // $exists_ar = AuditType::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
    //     // $exists_en = AuditType::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

    //     // if ($exists_ar || $exists_en) {
    //     //     return RespondWithBadRequest($lang, 9);
    //     // }

    //     $reasons->name_ar = $request->name_ar;
    //     $reasons->name_en = $request->name_en;
    //     $reasons->modified_by = authActionSave()['by'];
    //     $reasons->modified_by_type = authActionSave()['type'];
    //     $reasons->save();

    //     return RespondWithSuccessRequest($lang, 1);
    // }

    // public function delete(Request $request, $id)
    // {
    //     $lang = app()->getLocale();

    //     $reasons = AuditType::find($id);
    //     if (!$reasons) {
    //         return  RespondWithBadRequestData($lang, 8);
    //     }

    //     $reasons->deleted_by = authActionSave()['by'];
    //     $reasons->deleted_by_type = authActionSave()['type'];
    //     $reasons->save();
    //     $reasons->delete();

    //     return RespondWithSuccessRequest($lang, 1);
    // }
}
