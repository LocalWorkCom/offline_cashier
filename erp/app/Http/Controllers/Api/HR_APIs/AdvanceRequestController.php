<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdvanceRequestResource;
use App\Models\AdvanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvanceRequestController extends Controller
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
        $requests = AdvanceRequest::all();
        return ResponseWithSuccessData($this->lang, AdvanceRequestResource::collection($requests), 1);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function save(Request $request, string $id = null)
    {
        $data = $request->validate([
            'advance_setting_id' => 'required|numeric|exists:advance_settings,id',
            'employee_id' => 'required|numeric|exists:employees,id',
            'reason' => 'nullable|string',
            'status' => 'required|numeric|in:0,1,2',
        ]);
        $id == null ?$data['created_by'] =Auth::guard('api')->user()->id
                : $data['modified_by'] =Auth::guard('api')->user()->id;
        $advance = AdvanceRequest::updateOrCreate(['id' => $id], $data);
        return ResponseWithSuccessData($this->lang, AdvanceRequestResource::make($advance), 1);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = AdvanceRequest::find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        return ResponseWithSuccessData($this->lang, AdvanceRequestResource::make($data), 1);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = AdvanceRequest::find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        $data->deleted_by = Auth::guard('api')->user()->id;
        $data->save();
        $data->delete();

        return ResponseWithSuccessData($this->lang, AdvanceRequestResource::make($data), 1);
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $data = AdvanceRequest::withTrashed()->find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        $data->restore();
        return ResponseWithSuccessData($this->lang, AdvanceRequestResource::make($data), 1);
    }
}
