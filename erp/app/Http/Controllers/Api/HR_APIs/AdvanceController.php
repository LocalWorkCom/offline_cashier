<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdvanceResource;
use App\Models\Advance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvanceController extends Controller
{
    private $lang;

    public function __construct(Request $request)
    {
        $this->lang = $request->header('lang', 'ar');
        if (!CheckToken()) {
            return RespondWithBadRequest($this->lang, 5);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = Advance::with(['employee','request'])->get();
        return ResponseWithSuccessData($this->lang, AdvanceResource::collection($requests), 1);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function save(Request $request, string $id = null)
    {
        $data = $request->validate([
            'request_id' => 'required|numeric|exists:advance_requests,id',
            'employee_id' => 'required|numeric|exists:employees,id',
            'approval_date' => 'required|date',
            'starting_date' => 'required|date',
            'ending_date' => 'required|date',
            'amount_per_month' => 'required|numeric',
            'note' => 'nullable|string',
        ]);
        $id == null ?$data['created_by'] =Auth::guard('api')->user()->id
            : $data['modified_by'] =Auth::guard('api')->user()->id;

        $advance = Advance::updateOrCreate(['id' => $id], $data)->with(['employee','request']);

        return ResponseWithSuccessData($this->lang, AdvanceResource::make($advance), 1);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Advance::find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        return ResponseWithSuccessData($this->lang, AdvanceResource::make($data), 1);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Advance::find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        $data->deleted_by = Auth::guard('api')->user()->id;
        $data->save();
        $data->delete();

        return ResponseWithSuccessData($this->lang, AdvanceResource::make($data), 1);
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $data = Advance::withTrashed()->find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        $data->restore();
        return ResponseWithSuccessData($this->lang, AdvanceResource::make($data), 1);
    }
}
