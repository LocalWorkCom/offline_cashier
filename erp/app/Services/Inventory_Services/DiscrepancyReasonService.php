<?php


namespace App\Services\Inventory_Services;

use App\Models\DiscrepancyReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DiscrepancyReasonService
{

    public function index(Request $request)
    {
        $lang = app()->getLocale();
        try {
            $response = DiscrepancyReason::query();

            // Now $response is a query builder, so paginateOrGetAll can work with it
            $result = paginateOrGetAll($response, $request, null);

            return ResponseWithSuccessDataPaginated($lang, $result, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }

    }
    public function show($id)
    {
        $lang = app()->getLocale();

        $reasons = DiscrepancyReason::find($id);
        if (!$reasons) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Discrepancy Reason not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        return ResponseWithSuccessData($lang, $reasons, 1);
    }
    public function store(Request $request)
    {
        $lang = app()->getLocale();

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            // Return array instead of response
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;

        if (CheckExistColumnValue('reason_purchase_requests', 'name_ar', $name_ar) || CheckExistColumnValue('reason_purchase_requests', 'name_en', $name_en)) {
            return RespondWithBadRequest($lang, 9);
        }

        $educationLevel = new DiscrepancyReason();
        $educationLevel->name_ar = $name_ar;
        $educationLevel->name_en = $name_en;
        $educationLevel->created_by = authActionSave()['by'];
        $educationLevel->created_by_type = authActionSave()['type'];
        $educationLevel->save();

        return RespondWithSuccessRequest($lang, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();

        if ($id >= 1 && $id <= 5) {
            return respondError(__('Cannot update protected records (ID 1-5)'), 403);
        }
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            // Return array instead of response
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        $reasons = DiscrepancyReason::find($id);
        if (!$reasons) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Discrepancy Reason not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        if ($reasons->name_ar == $request->name_ar && $reasons->name_en == $request->name_en) {
            return RespondWithBadRequestData($lang, 10);
        }

        $exists_ar = DiscrepancyReason::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = DiscrepancyReason::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

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
        $lang = app()->getLocale();

        $reasons = DiscrepancyReason::find($id);
        if (!$reasons) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Discrepancy Reason not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }
        if ($id >= 1 && $id <= 5) {
            return respondError(__('Cannot update protected records (ID 1-5)'), 403);
        }
        $reasons->deleted_by = authActionSave()['by'];
        $reasons->deleted_by_type = authActionSave()['type'];
        $reasons->save();
        $reasons->delete();

        return RespondWithSuccessRequest($lang, 1);
    }
}
