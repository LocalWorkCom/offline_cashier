<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\RejectReason;
use App\Services\Inventory_Services\RejectReasonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RejectReasonController extends Controller
{
    protected $rejectReasonService;

    protected $hidden = [
        // 'name_ar',
        // 'name_en',
        "created_by_type",
        'created_by',
        "updated_by",
        "updated_by_type",
        "deleted_by",
        "deleted_by_type",
        "deleted_at",
        "created_at",
        "updated_at"
    ];
    public function __construct(RejectReasonService $rejectReasonService)
    {
        $this->rejectReasonService = $rejectReasonService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $rejectReason = $this->rejectReasonService->all();
        $orders = paginateOrGetAll($rejectReason, $request, $this->hidden, []);

        return ResponseWithSuccessDataPaginated($lang, $orders, 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $reason = $this->rejectReasonService->store($request->all());
        return ResponseWithSuccessData($lang, $reason, 1);
    }

    public function show($id, Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $reason = RejectReason::find($id)?->makeHidden($this->hidden);
        if (!$reason) {
            return respondError(
                __('reject_reason.not_found') . ' (ID: ' . $id . ')',
                404
            );
        }
        return ResponseWithSuccessData($lang, $reason, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $reason = RejectReason::find($id);
        if (!$reason) {
            return respondError(
                __('reject_reason.not_found') . ' (ID: ' . $id . ')',
                404
            );
        }
        $reason = $this->rejectReasonService->update($id, $request->all())?->makeHidden($this->hidden);
        return ResponseWithSuccessData($lang, $reason, 1);
    }

    public function destroy($id, Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $reason = RejectReason::find($id);
        if (!$reason) {
            return respondError(
                __('reject_reason.not_found') . ' (ID: ' . $id . ')',
                404
            );
        }
        $this->rejectReasonService->destroy($id);
        return ResponseWithSuccessData($lang, null, 1);
    }

    public function restore($id, Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $reason = RejectReason::find($id);
        if (!$reason) {
            return respondError(
                __('reject_reason.not_found') . ' (ID: ' . $id . ')',
                404
            );
        }
        $reason = $this->rejectReasonService->restore($id);
        return ResponseWithSuccessData($lang, $reason, 1);
    }
}
