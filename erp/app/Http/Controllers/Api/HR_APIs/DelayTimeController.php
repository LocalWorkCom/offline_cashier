<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayTimeResource;
use App\Models\DelayTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DelayTimeController extends Controller
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
        $times = DelayTime::all();

        return ResponseWithSuccessData($lang, DelayTimeResource::collection($times), 1);
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
            'time' => 'required|numeric',
            'type' => 'required|in:1,2',
            'punishment_ar' => 'required|string',
            'punishment_en' => 'required|string',
        ]);

        $time = new DelayTime();
        $time->time = $data['time'];
        $time->type = $data['type'];
        $time->punishment_ar = $data['punishment_ar'];
        $time->punishment_en = $data['punishment_en'];
        $time->created_by = Auth::guard('api')->user()->id;
        $time->save();

        return ResponseWithSuccessData($lang, DelayTimeResource::make($time), 1);
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

        $time = DelayTime::find($id);

        if (!$time) {
            return RespondWithBadRequestData($lang, 2);
        }

        return ResponseWithSuccessData($lang, DelayTimeResource::make($time), 1);
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
            'time' => 'sometimes|required|numeric',
            'type' => 'sometimes|required|in:1,2',
            'punishment_ar' => 'sometimes|required|string',
            'punishment_en' => 'sometimes|required|string',
        ]);

        $time = DelayTime::find($id);

        if (!$time) {
            return RespondWithBadRequestData($lang, 2);
        }

        if (isset($data['time'])) {
            $time->time = $data['time'];
        }
        if (isset($data['type'])) {
            $time->type = $data['type'];
        }
        if (isset($data['punishment_ar'])) {
            $time->punishment_ar = $data['punishment_ar'];
        }
        if (isset($data['punishment_en'])) {
            $time->punishment_en = $data['punishment_en'];
        }
        $time->modified_by = Auth::guard('api')->user()->id;
        $time->save();

        return ResponseWithSuccessData($lang, DelayTimeResource::make($time), 1);
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

        $time = DelayTime::find($id);

        if (!$time) {
            return RespondWithBadRequestData($lang, 2);
        }
        $time->deleted_by = Auth::guard('api')->user()->id;
        $time->save();
        $time->delete();

        return ResponseWithSuccessData($lang, DelayTimeResource::make($time), 1);
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

        $time = DelayTime::withTrashed()->find($id);

        if (!$time) {
            return RespondWithBadRequestData($lang, 2);
        }

        $time->restore();

        return ResponseWithSuccessData($lang, DelayTimeResource::make($time), 1);
    }
}
