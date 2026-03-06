<?php


namespace App\Services\Inventory_Services;

use App\Http\Resources\Inventory\ReasonPurchaseRequestResource;
use App\Models\ReasonPurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReasonPurchaseRequestService
{

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        $reasons = ReasonPurchaseRequest::query();

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
        $formatted = ReasonPurchaseRequestResource::collection($result['data'])->resolve();

        $result['data'] = $formatted;
        return ResponseWithSuccessDataPaginated($lang, $result, 1);
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $reasons = ReasonPurchaseRequest::find($id);
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
            'name_ar' => 'required|string|min:1',
            'name_en' => 'required|string|min:1',
            'is_active' => 'nullable|boolean|in:0,1',
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;
//
//        if (CheckExistColumnValue('reason_purchase_requests', 'name_ar', $name_ar) || CheckExistColumnValue('reason_purchase_requests', 'name_en', $name_en)) {
//            return respondError(__('validation.exists'), 400);
//        }

        $educationLevel = new ReasonPurchaseRequest();
        $educationLevel->name_ar = $name_ar;
        $educationLevel->name_en = $name_en;
        $educationLevel->is_active = $request->input('is_active', 1);
        $educationLevel->created_by = authActionSave()['by'];
        $educationLevel->created_by_type = authActionSave()['type'];
        $educationLevel->save();

        return RespondWithSuccessRequest($lang, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|min:3|max:100|regex:/\S/',
            'name_en' => 'required|string|min:3|max:100|regex:/\S/',
            'is_active' => 'nullable|boolean|in:0,1',
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        $reasons = ReasonPurchaseRequest::find($id);
        if (!$reasons) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

//        if ($reasons->name_ar == $request->name_ar && $reasons->name_en == $request->name_en) {
//            return RespondWithBadRequestData($lang, 10);
//        }
//
//        $exists_ar = ReasonPurchaseRequest::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
//        $exists_en = ReasonPurchaseRequest::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();
//
//        if ($exists_ar || $exists_en) {
//            return respondError(__('validation.exists'), 400);
//        }

        $reasons->name_ar = $request->name_ar;
        $reasons->name_en = $request->name_en;
        $reasons->is_active = $request->input('is_active', $reasons->is_active);
        $reasons->modified_by = authActionSave()['by'];
        $reasons->modified_by_type = authActionSave()['type'];
        $reasons->save();

        return RespondWithSuccessRequest($lang, 1);
    }

    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $reason = ReasonPurchaseRequest::find($id);


        if (!$reason) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        if ($reason->purchaseRequests()->exists()) {
            $reason->is_active = 0;
            $reason->updated_by = authActionSave()['by'];
            $reason->updated_by_type = authActionSave()['type'];
            $reason->save();
            $msg = __('validation.cannotDeleteReasonInUse');
            return ResponseWithSuccessData($lang, $msg, 1);
        } else {
            $reason->deleted_by = authActionSave()['by'];
            $reason->deleted_by_type = authActionSave()['type'];
            $reason->save();
            $reason->delete();
        }
        return RespondWithSuccessRequest($lang, 1);
    }
}
