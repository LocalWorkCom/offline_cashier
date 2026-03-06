<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\OvertimeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class OvertimeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $over_times = OvertimeType::get();
            return ResponseWithSuccessData($lang, $over_times, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function add(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'name_ar' => 'required',
                'name_en' => 'required'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $over_time = new OvertimeType();
            $over_time->name_ar = $request->name_ar;
            $over_time->name_en = $request->name_en;
            $over_time->created_by = $user_id;
            $over_time->save();

            return ResponseWithSuccessData($lang, $over_time, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function edit(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'id' => 'required|exists:overtime_types,id',
                'name_ar' => 'required',
                'name_en' => 'required'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $over_time = OvertimeType::find($request->id);
            $over_time->name_ar = $request->name_ar;
            $over_time->name_en = $request->name_en;
            $over_time->modified_by = $user_id;
            $over_time->save();

            return ResponseWithSuccessData($lang, $over_time, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $user_id = Auth::guard('api')->user()->id;

            $over_time = OvertimeType::find($request->id);
            if (!$over_time) {
                return  RespondWithBadRequestNotExist();
            }

            $over_time->deleted_by = $user_id;
            $over_time->save();

            $over_time->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
