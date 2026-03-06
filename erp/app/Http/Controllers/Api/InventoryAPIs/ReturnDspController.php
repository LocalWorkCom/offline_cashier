<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inventory\DspReturnResource;
use App\Models\ReturnDsp;
use App\Services\Inventory_Services\ReturnDspService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReturnDspController extends Controller
{
    protected $returnDspService;

    public function __construct(ReturnDspService $returnDspService)
    {
        $this->returnDspService = $returnDspService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $query = $this->returnDspService->index($request);
        $returnDsp = paginateOrGetAll($query, $request, null, null);

        // $dataCollection = $returnDsp['data'] ?? collect();

        $data = DspReturnResource::collection($returnDsp['data']);

        return ResponseWithSuccessDataPaginated($lang, [
            'data' => $data,
            'meta' => $returnDsp['meta'] ?? null,
        ], 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $validator = Validator::make($request->all(), [
            'dsp_id' => [
                'required',
                Rule::exists('direct_supply_permissions', 'id')
                    ->where(function ($query) {
                        $query->whereIn('dsp_status_id', [6, 9]);
                    }),
            ],
            'status' => 'required|in:draft,submitted',
            'reason_id' => 'required|exists:reasone_return_dsps,id',
            'items' => 'required|array|min:1',
            'items.*' => [
                'integer',
                Rule::exists('direct_supply_permission_items', 'item_id')
                    ->where('dsp_id', $request->dsp_id),
            ],
            'quantity'  => 'required|array|min:1|size:' . count($request->items),
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:png,jpg,jpeg,pdf,doc,docx',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }
        $returnDsp = $this->returnDspService->store($request);
        //  $data = new DspReturnResource($returnDsp);
        return ResponseWithSuccessData(
            $lang,
            $returnDsp,
            1
        );
    }

    public function update(Request $request, $id)
    {
        return $this->returnDspService->update($request, $id);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $ReturnDsp = ReturnDsp::with([
            'reason',
            'documents',
            'dsp.items.issues',
            'dsp.status',
            'dsp.fromStore',
            'dsp.toStore',
            'dsp.qaTester',
            'dsp.purchaseRequest',
            'dsp.supplyOrder'
        ])->find($id);

        if (!$ReturnDsp) {
            return respondError(__('validation.not_found'), 404);
        }
        $data = new DspReturnResource($ReturnDsp);

        return ResponseWithSuccessData(
            $lang,
            $data,
            1
        );
    }

    public function delete(Request $request, $id)
    {
        return $this->returnDspService->delete($request, $id);
    }

    public function approveOrReject(Request $request, $id)
    {
        return $this->returnDspService->approveOrReject($request, $id);
    }
}
