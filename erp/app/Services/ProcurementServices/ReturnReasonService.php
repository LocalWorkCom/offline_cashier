<?php


namespace App\Services\ProcurementServices;

use App\Http\Resources\Inventory\ReasonRejectResource;
use App\Http\Resources\Inventory\ReasonReturnResource;
use App\Models\ReturnReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReturnReasonService
{

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        $reasons = ReturnReason::query();

        // Filter by active status
        if ($request->has('is_active') && in_array($request->is_active, [0, 1])) {
            $reasons->where('is_active', $request->is_active);
        }

        // Filter by date range
        if ($request->has('from') && $request->has('to')) {
            $from = $request->from;
            $to = $request->to;
            $reasons->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        } elseif ($request->has('from')) {
            $reasons->where('created_at', '>=', $request->from . ' 00:00:00');
        } elseif ($request->has('to')) {
            $reasons->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        // Order by newest first
        $reasons->orderBy('created_at', 'desc')->orderBy('updated_at', 'desc');
        $result = paginateOrGetAll($reasons, $request, null);
 $formatted = ReasonReturnResource::collection($result['data'])->resolve();

        return ResponseWithSuccessDataPaginated($lang, ['data' => $formatted ,'meta' => $result['meta']], 1);    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $reasons = ReturnReason::find($id);
        if (!$reasons) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        $reasons->is_active = (int) $reasons->is_active;

        return ResponseWithSuccessData($lang, $reasons, 1);
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[\p{Arabic}A-Za-z0-9\s\-\(\)]+$/u',
                Rule::unique('return_reasons', 'name_ar')
                    ->whereNull('deleted_at'),
            ],

            'name_en' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[A-Za-z0-9\s\-\(\)]+$/',
                Rule::unique('return_reasons', 'name_en')
                    ->whereNull('deleted_at'),
            ],
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'is_active' => 'nullable|boolean|in:0,1',
        ]);


        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;

        if (
            CheckExistColumnValue('return_reasons', 'name_ar', $request->name_ar) ||
            CheckExistColumnValue('return_reasons', 'name_en', $request->name_en)
        ) {
            return respondError(__('validation.exists'), 400);
        }

        $educationLevel = new ReturnReason();
        $educationLevel->name_ar = $name_ar;
        $educationLevel->name_en = $name_en;
        $educationLevel->description_ar = $request->description_ar;
        $educationLevel->description_en = $request->description_en;
        $educationLevel->is_active = $request->input('is_active', 1);
        $educationLevel->created_by = authActionSave()['by'];
        $educationLevel->save();

        return RespondWithSuccessRequest($lang, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        $reasons = ReturnReason::find($id);
        if (!$reasons) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[\p{Arabic}A-Za-z0-9\s\-\(\)]+$/u',
                Rule::unique('return_reasons', 'name_ar')
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],

            'name_en' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[A-Za-z0-9\s\-\(\)]+$/',
                Rule::unique('return_reasons', 'name_en')
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'is_active' => 'nullable|boolean|in:0,1',
        ]);


        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }


//        if ($reasons->name_ar == $request->name_ar && $reasons->name_en == $request->name_en) {
//            return RespondWithBadRequestData($lang, 10);
//        }

        $exists_ar = ReturnReason::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = ReturnReason::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

        if ($exists_ar || $exists_en) {
            return respondError(__('validation.exists'), 400);
        }

        $reasons->name_ar = $request->name_ar;
        $reasons->name_en = $request->name_en;
        $reasons->description_ar = $request->description_ar;
        $reasons->description_en = $request->description_en;
        $reasons->is_active = $request->input('is_active', $reasons->is_active);
        $reasons->updated_by = authActionSave()['by'];
        $reasons->save();

        return RespondWithSuccessRequest($lang, 1);
    }

    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $reason = ReturnReason::find($id);


        if (!$reason) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        // if ($reason->purchaseRequests()->exists()) {
        //     $reason->is_active = 0;
        //     $reason->updated_by = authActionSave()['by'];
        //     $reason->updated_by_type = authActionSave()['type'];
        //     $reason->save();
        //     $msg = __('validation.cannotDeleteReasonInUse');
        //     return ResponseWithSuccessData($lang, $msg, 1);
        // } else {
        $reason->deleted_by = authActionSave()['by'];
        $reason->save();
        $reason->delete();
        // }
        return RespondWithSuccessRequest($lang, 1);
    }
}
