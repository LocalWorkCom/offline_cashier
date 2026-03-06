<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayResource;
use App\Models\Delay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DelayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }

        $delays = Delay::with([
            'time',
            'employee',
        ])->get();

        return ResponseWithSuccessData($lang, DelayResource::collection($delays), 1);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }

        $data = $request->validate([
            'time_id' => 'required|exists:delay_times,id',
            'employee_id' => 'required|exists:employees,id',
            'note' => 'nullable|string',
        ]);

        $delay = new Delay();
        $delay->time_id = $data['time_id'];
        $delay->employee_id = $data['employee_id'];
        $delay->note = $data['note'];
        $delay->created_by = Auth::guard('api')->user()->id;
        $delay->save();

        return ResponseWithSuccessData($lang, DelayResource::make($delay), 1);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $lang = $request->header('lang', 'ar');

        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }

        $delay = Delay::with(['time', 'employee:id,first_name,last_name'])->find($id);

        if (!$delay) {
            return RespondWithBadRequestData($lang, 2);
        }

        return ResponseWithSuccessData($lang, DelayResource::make($delay), 1);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lang = $request->header('lang', 'ar');

        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }

        $data = $request->validate([
            'time_id' => 'sometimes|required|exists:delay_times,id',
            'employee_id' => 'sometimes|required|exists:employees,id',
            'note' => 'nullable|string',
        ]);
        $delay = Delay::find($id);
        if (!$delay) {
            return RespondWithBadRequestData($lang, 8);
        }
        if(isset($data['time_id'])){
            $delay->time_id = $data['time_id'];
        }
        if(isset($data['employee_id'])){
            $delay->employee_id = $data['employee_id'];
        }
        if(isset($data['note'])){
            $delay->note = $data['note'];
        }
        $delay->modified_by = Auth::guard('api')->user()->id;
        $delay->save();

        return ResponseWithSuccessData($lang, DelayResource::make($delay), 1);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lang = request()->header('lang', 'ar');

        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }

        $delay = Delay::find($id);

        if (!$delay) {
            return RespondWithBadRequestData($lang, 2);
        }
        $delay->deleted_by = Auth::guard('api')->user()->id;
        $delay->save();
        $delay->delete();

        return ResponseWithSuccessData($lang, DelayResource::make($delay), 1);
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id, Request $request)
    {
        $lang = $request->header('lang', 'ar');

        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }

        $delay = Delay::withTrashed()->find($id);

        if (!$delay) {
            return RespondWithBadRequestData($lang, 2);
        }

        $delay->restore();

        return ResponseWithSuccessData($lang, DelayResource::make($delay), 1);
    }
}
