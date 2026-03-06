<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\PenaltyReason;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PenaltyReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $query = PenaltyReason::query();

        $result = paginateOrGetAll($query, $request);

        return ResponseWithSuccessDataPaginated($lang, $result, 1);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        //        if (!CheckTokenEmployee()) {
        //            return RespondWithBadRequest($lang, 5);
        //        }
        // $messages = [
        //     'code.required' => $lang === 'ar' ? 'الكود مطلوب.' : 'Code is required',
        //     'requires_approval.required' => $lang === 'ar' ? 'يتطلب موافقة مطلوب.' : 'Requires approval is required',
        // ];
        try {
            // Step 1: Validate basic fields
            $data =  $request->validate([
                'reason_ar' => 'required|string',
                'reason_en' => 'required|string',
                'punishment_ar' => 'nullable|string',
                'punishment_en' => 'nullable|string',
                'code' => 'required|string|unique:penalty_reasons,code',
                'type' => 'sometimes|required|string|in:salary_deduction,fine,allowance_reduction,bonus_loss',
                'requires_approval' => 'required|boolean',
                'note' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $e->errors(),
                'validation_type' => true
            ], 400);
        }
        // $userId = Auth::guard('employee')->user()->id;
        //        dd($userId);
        $reason = new PenaltyReason();
        $reason->type = $data['type'];
        $reason->reason_ar = $data['reason_ar'];
        $reason->reason_en = $data['reason_en'];
        $reason->punishment_ar = $data['punishment_ar'];
        $reason->punishment_en = $data['punishment_en'];
        $reason->code = $data['code'];
        $reason->requires_approval = $data['requires_approval'];
        $reason->note = $data['note'];
         $reason->created_by = authActionSave()['by'];
        $reason->created_by_type = authActionSave()['type'];
        $reason->created_at = Carbon::now();
        //        dd($reason);
        $reason->save();

        //        $response = [
        //            'id' => $reason->id,
        //            'reason' => $lang === 'ar' ? $reason->reason_ar : $reason->reason_en,
        //            'punishment' => $lang === 'ar' ? $reason->punishment_ar : $reason->punishment_en,
        //            'note' => $reason->note,
        //        ];

        return ResponseWithSuccessData($lang, $reason, 1);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $reason = PenaltyReason::find($id);

        if (!$reason) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $response = [
            'id' => $reason->id,
            'type' => $reason->type,
            'code' => $reason->code,
            'reason' => $lang === 'ar' ? $reason->reason_ar : $reason->reason_en,
            'punishment' => $lang === 'ar' ? $reason->punishment_ar : $reason->punishment_en,
            'requires_approval' => $reason->requires_approval == 1,
            'note' => $reason->note,
        ];

        return ResponseWithSuccessData($lang, $response, 1);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lang = $request->header('lang', 'ar');

        //        if (!CheckTokenEmployee()) {
        //            return RespondWithBadRequest($lang, 5);
        //        }
        try {
            // Step 1: Validate basic fields
            $request->validate([
                'type' => 'sometimes|required|string|in:salary_deduction,fine,allowance_reduction,bonus_loss',
                'code' => 'sometimes|required|string|unique:penalty_reasons,code',
                'reason_ar' => 'sometimes|required|string',
                'reason_en' => 'sometimes|required|string',
                'punishment_ar' => 'sometimes|nullable|string',
                'punishment_en' => 'sometimes|nullable|string',
                'requires_approval' => 'sometimes|required|string',
                'note' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $e->errors(),
                'validation_type' => true
            ], 400);
        }
        $reason = PenaltyReason::find($id);

        if (!$reason) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        if (isset($data['type'])) {
            $reason->type = $data['type'];
        }
        if (isset($data['reason_ar'])) {
            $reason->reason_ar = $data['reason_ar'];
        }
        if (isset($data['reason_en'])) {
            $reason->reason_en = $data['reason_en'];
        }
        if (isset($data['punishment_ar'])) {
            $reason->punishment_ar = $data['punishment_ar'];
        }
        if (isset($data['punishment_en'])) {
            $reason->punishment_en = $data['punishment_en'];
        }
        if (isset($data['note'])) {
            $reason->note = $data['note'];
        }
        if (isset($data['code'])) {
            $reason->code = $data['code'];
        }
        if (isset($data['requires_approval'])) {
            $reason->requires_approval = $data['requires_approval'];
        }
        $reason->updated_at = Carbon::now();
        $reason->modified_by = authActionSave()['by'];
        $reason->modified_by_type = authActionSave()['type'];
        $reason->save();

        $response = [
            'id' => $reason->id,
            'type' => $reason->type,
            'reason' => $lang === 'ar' ? $reason->reason_ar : $reason->reason_en,
            'punishment' => $lang === 'ar' ? $reason->punishment_ar : $reason->punishment_en,
            'code' => $reason->code,
            'requires_approval' => $reason->requires_approval == 1,
            'note' => $reason->note,
        ];

        return ResponseWithSuccessData($lang, $response, 1);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lang = request()->header('lang', 'ar');

        //        if (!CheckTokenEmployee()) {
        //            return RespondWithBadRequest($lang, 5);
        //        }

        $reason = PenaltyReason::find($id);

        if (!$reason) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        $reason->deleted_by = authActionSave()['by'];
        $reason->deleted_by_type = authActionSave()['type'];
        $reason->save();
        $reason->delete();

        //        $response = [
        //            'id' => $reason->id,
        //            'reason' => $lang === 'ar' ? $reason->reason_ar : $reason->reason_en,
        //            'punishment' => $lang === 'ar' ? $reason->punishment_ar : $reason->punishment_en,
        //            'note' => $reason->note,
        //        ];

        return ResponseWithSuccessData($lang, $reason, 1);
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id, Request $request)
    {
        $lang = $request->header('lang', 'ar');

        //        if (!CheckTokenEmployee()) {
        //            return RespondWithBadRequest($lang, 5);
        //        }

        $reason = PenaltyReason::withTrashed()->find($id);

        if (!$reason) {
            return RespondWithBadRequestData($lang, 2);
        }

        $reason->restore();

        return ResponseWithSuccessData($lang, $reason, 1);
    }
}
