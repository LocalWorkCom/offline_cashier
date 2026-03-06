<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\SupplyOrderReason;
use App\Services\Inventory_Services\SupplyOrderReasonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SupplyOrderReasonController extends Controller
{
    protected $service;
    protected $lang;
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
    public function __construct(SupplyOrderReasonService $service)
    {
        $this->service = $service;
        $this->lang = request()->header('lang', 'ar');
    }

    public function index(Request $request)
    {
        $data = $this->service->index();

        $orders = paginateOrGetAll($data, $request, $this->hidden, []);

        return ResponseWithSuccessDataPaginated($this->lang, $orders, 1);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            $message = $this->lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق من الصحة.';
            return respondError($message, 400, $validator->errors());
        }

        $reason = $this->service->store($request->all());
        return ResponseWithSuccessData($this->lang, $reason->makeHidden($this->hidden), 1);
    }

    public function show($id)
    {
        $reason = SupplyOrderReason::find($id);
        if (!$reason) {
            return respondError(
                $this->lang == 'en' ? 'Supply order reason not found.' : 'سبب أمر التوريد غير موجود.',
                404
            );
        }
        $reason = $reason?->makeHidden($this->hidden);

        return ResponseWithSuccessData($this->lang, $reason, 1);
    }

    public function update(Request $request, $id)
    {
        $reason = SupplyOrderReason::find($id);
        if (!$reason) {
            return respondError(
                $this->lang == 'en' ? 'Supply order reason not found.' : 'سبب أمر التوريد غير موجود.',
                404
            );
        }
        $validator = Validator::make($request->all(), [
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            $message = $this->lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق من الصحة.';
            return respondError($message, 400, $validator->errors());
        }

        $reason = $this->service->update($reason, $request->all());
        return ResponseWithSuccessData($this->lang, $reason->makeHidden($this->hidden), 1);
    }

    public function destroy($id)
    {
        $reason = SupplyOrderReason::find($id);
        if (!$reason) {
            return respondError(
                $this->lang == 'en' ? 'Supply order reason not found.' : 'سبب أمر التوريد غير موجود.',
                404
            );
        }
        $this->service->destroy($id);
        $message = $this->lang == 'en' ? 'Deleted successfully' : 'تم الحذف بنجاح';
        return RespondWithSuccessMsg($message);
    }
}
