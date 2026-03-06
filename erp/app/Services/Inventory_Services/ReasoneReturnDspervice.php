<?php


namespace App\Services\Inventory_Services;

use App\Models\ReasoneReturnDsp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReasoneReturnDspervice
{

    public function index(Request $request)
    {
        $lang = $request->header('lang','ar');

        $reasons = ReasoneReturnDsp::get();

        return ResponseWithSuccessData($lang, $reasons, 1);
    }
    public function show(Request $request,$id)
    {
        $lang = $request->header('lang','ar');

        $reasons = ReasoneReturnDsp::find($id);
        if (!$reasons) {
            return RespondWithBadRequestData($lang, 8);
        }

        return ResponseWithSuccessData($lang, $reasons, 1);
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang','ar');

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;

        if (CheckExistColumnValue('reasone_return_dsps', 'name_ar', $name_ar) || CheckExistColumnValue('reasone_return_dsps', 'name_en', $name_en)) {
            return RespondWithBadRequest($lang, 9);
        }

        $ReasoneReturnDsp = new ReasoneReturnDsp();
        $ReasoneReturnDsp->name_ar = $name_ar;
        $ReasoneReturnDsp->name_en = $name_en;
        $ReasoneReturnDsp->created_by = authActionSave()['by'];
        $ReasoneReturnDsp->created_by_type = authActionSave()['type'];
        $ReasoneReturnDsp->save();

        return RespondWithSuccessRequest($lang, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang','ar');

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        $reasons = ReasoneReturnDsp::find($id);
        if (!$reasons) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        if ($reasons->name_ar == $request->name_ar && $reasons->name_en == $request->name_en) {
            return RespondWithBadRequestData($lang, 10);
        }

        $exists_ar = ReasoneReturnDsp::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = ReasoneReturnDsp::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

        if ($exists_ar || $exists_en) {
            return RespondWithBadRequest($lang, 9);
        }

        $reasons->name_ar = $request->name_ar;
        $reasons->name_en = $request->name_en;
        $reasons->modified_by = authActionSave()['by'];
        $reasons->modified_by_type = authActionSave()['type'];
        $reasons->save();

        return RespondWithSuccessRequest($lang, 1);
    }

    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang','ar');
        $reasons = ReasoneReturnDsp::find($id);
        if (!$reasons) {
            return  RespondWithBadRequestData($lang, 8);
        }

        $reasons->deleted_by = authActionSave()['by'];
        $reasons->deleted_by_type = authActionSave()['type'];
        $reasons->save();
        $reasons->delete();

        return RespondWithSuccessRequest($lang, 1);
    }
}
