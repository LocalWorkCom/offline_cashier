<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\PenaltyDeductionResource;
use App\Models\PenaltyDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenaltyDeductionController extends Controller
{
    private $lang;

    public function __construct(Request $request)
    {
        $this->lang = $request->header('lang', 'ar');
//        if (!CheckTokenEmployee()) {
//            return RespondWithBadRequest($this->lang, 5);
//        }

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $deductions = PenaltyDeduction::with(['penalty.employee','penalty.reason'])->get();
        return ResponseWithSuccessData($this->lang, PenaltyDeductionResource::collection($deductions), 1);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function save(Request $request, string $id = null)
    {
        $data = $request->validate([
            'penalty_id' => 'required|numeric|exists:penalties,id',
            'employee_id' => 'required|numeric|exists:employees,id',
            'deduction_amount' => 'required|numeric',
        ]);
        $id == null ? $data['created_by'] =Auth::guard('api')->user()->id
            : $data['modified_by'] =Auth::guard('api')->user()->id;

        $deduction = PenaltyDeduction::updateOrCreate(['id' => $id], $data);

        return ResponseWithSuccessData($this->lang, PenaltyDeductionResource::make($deduction), 1);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = PenaltyDeduction::find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        return ResponseWithSuccessData($this->lang, PenaltyDeductionResource::make($data), 1);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = PenaltyDeduction::find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        $data->deleted_by = Auth::guard('api')->user()->id;
        $data->save();
        $data->delete();

        return ResponseWithSuccessData($this->lang, PenaltyDeductionResource::make($data), 1);
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $data = PenaltyDeduction::withTrashed()->find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        $data->restore();
        return ResponseWithSuccessData($this->lang, PenaltyDeductionResource::make($data), 1);
    }
}
