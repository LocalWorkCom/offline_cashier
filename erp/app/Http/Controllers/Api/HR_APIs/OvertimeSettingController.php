<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\OvertimeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class OvertimeSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $overtime_settings = OvertimeSetting::with('overtimes')->get();
            return ResponseWithSuccessData($lang, $overtime_settings, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function add(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'overtime_type_id' => 'required|exists:overtime_types,id',
                'quentity' => 'integer|nullable',
                'percent' => 'integer|nullable'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $overtime_setting = new OvertimeSetting();
            $overtime_setting->overtime_type_id = $request->overtime_type_id;
            $overtime_setting->quentity = $request->quentity;
            $overtime_setting->percent = $request->percent;
            $overtime_setting->created_by = $user_id;
            $overtime_setting->save();

            return ResponseWithSuccessData($lang, $overtime_setting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function edit(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'id' => 'required|exists:overtime_settings,id',
                'overtime_type_id' => 'required|exists:overtime_types,id',
                'quentity' => 'integer|nullable',
                'percent' => 'integer|nullable'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $overtime_setting = OvertimeSetting::find($request->id);
            $overtime_setting->overtime_type_id = $request->overtime_type_id;
            $overtime_setting->quentity = $request->quentity;
            $overtime_setting->percent = $request->percent;
            $overtime_setting->modified_by = $user_id;
            $overtime_setting->save();

            return ResponseWithSuccessData($lang, $overtime_setting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $user_id = Auth::guard('api')->user()->id;

            $overtime_setting = OvertimeSetting::find($request->id);
            if (!$overtime_setting) {
                return  RespondWithBadRequestNotExist();
            }

            $overtime_setting->deleted_by = $user_id;
            $overtime_setting->save();

            $overtime_setting->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
