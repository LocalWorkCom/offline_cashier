<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\DeliverySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class DeliverySettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $delivery_settings = DeliverySetting::with('branches')->get();
            return ResponseWithSuccessData($lang, $delivery_settings, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function add(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'branch_id' => 'required|integer|exists:branches,id',
                'name_ar' => 'required|string',
                'name_en' => 'string',
                'latitude' => 'required|numeric',
                'longtitude' => 'required|numeric',
                'radius' => 'required|numeric',
                'price' => 'required|numeric'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $delivery_setting = new DeliverySetting();
            $delivery_setting->branch_id = $request->branch_id;
            $delivery_setting->name_ar = $request->name_ar;
            $delivery_setting->name_en = $request->name_en;
            $delivery_setting->latitude = $request->latitude;
            $delivery_setting->longtitude = $request->longtitude;
            $delivery_setting->radius = $request->radius;
            $delivery_setting->price = $request->price;
            $delivery_setting->created_by = $user_id;
            $delivery_setting->save();

            return ResponseWithSuccessData($lang, $delivery_setting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function edit(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'id' => 'required|exists:delivery_settings,id',
                'branch_id' => 'required|integer|exists:branches,id',
                'name_ar' => 'required|string',
                'name_en' => 'string',
                'latitude' => 'required|numeric',
                'longtitude' => 'required|numeric',
                'radius' => 'required|numeric',
                'price' => 'required|numeric'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $delivery_setting = DeliverySetting::findOrFail($request->id);
            $delivery_setting->branch_id = $request->branch_id;
            $delivery_setting->name_ar = $request->name_ar;
            $delivery_setting->name_en = $request->name_en;
            $delivery_setting->latitude = $request->latitude;
            $delivery_setting->longtitude = $request->longtitude;
            $delivery_setting->radius = $request->radius;
            $delivery_setting->price = $request->price;
            $delivery_setting->modified_by = $user_id;
            $delivery_setting->save();

            return ResponseWithSuccessData($lang, $delivery_setting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $user_id = Auth::guard('api')->user()->id;

            $delivery_setting = DeliverySetting::find($request->id);
            if (!$delivery_setting) {
                return  RespondWithBadRequestNotExist();
            }

            $delivery_setting->deleted_by = $user_id;
            $delivery_setting->save();

            $delivery_setting->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
