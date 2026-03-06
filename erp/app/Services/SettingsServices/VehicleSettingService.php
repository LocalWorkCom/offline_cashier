<?php

namespace App\Services\SettingsServices;

use Illuminate\Http\Request;
use App\Models\VehicleSetting;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VehicleSettingService
{
    public function index(Request $request)
    {
        $lang = app()->getLocale();
        try {
            $data = VehicleSetting::query();
            $fields = [];
            // $VehicleSettings = $data->makeVisible(['vehicle_type', 'vehicle_max', 'vehicle_min']);
            return $response = paginateOrGetAll($data, $request, $fields);
        } catch (\Exception $e) {
            Log::error('Error fetching vehicle settings: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function show(Request $request)
    {
        $lang = app()->getLocale();
        try {
            $data = VehicleSetting::where('id', $request->id)->first();
            if(!$data){
                return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            }
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching vehicle settings: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();
    
        // if (!CheckToken() && $checkToken) {
        //     return RespondWithBadRequest($lang, 5);
        // }
    
        // Custom validation for vehicle_max and vehicle_min
        $validator = Validator::make($request->all(), [
            'vehicle_type' => [
                'required',
                'string',
                'in:' . implode(',', VehicleSetting::getVehicleTypes()),
                // Ensure the vehicle type is unique, excluding the current record
                Rule::unique('vehicle_settings', 'vehicle_type')->ignore($id),
                // function ($attribute, $value, $fail) use ($request, $id) {
                //     // Check if the vehicle type hasn't changed
                //     $existing = VehicleSetting::find($id);
                //     if ($existing && $existing->vehicle_type === $value) {
                //         $fail('The vehicle type has not changed.');
                //     }
                // }
            ],
            'vehicle_max' => 'required|integer|min:1',
            'vehicle_min' => [
                'required',
                'integer',
                'min:1',
                'lte:vehicle_max', // Ensure vehicle_min is less than or equal to vehicle_max
            ],
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            // Handle the case where vehicle_min > vehicle_max
            $error = $validator->errors()->first('vehicle_min');
            if (strpos($error, 'lte:vehicle_max') !== false) {
                // return RespondWithBadRequest($lang ,400);
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }
    
        $VehicleSetting = VehicleSetting::find($id);
        if (!$VehicleSetting) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    
        if (
            $VehicleSetting->vehicle_type === $request->vehicle_type &&
            $VehicleSetting->vehicle_max == $request->vehicle_max &&
            $VehicleSetting->vehicle_min == $request->vehicle_min
        ) {
            // return respondErrorData(($lang == 'en' ? ['No Change'] : ['لا يوجد تغير']), 400, $lang == 'en' ? ['Data is not changed.'] : ['لا يوجد تحديث']);
            return RespondWithBadRequest($lang, 2);
        }
    
        // Update the Vehicle Setting
        $craeted = authActionSave();
        $created_by = $craeted['by'];
        $created_type = $craeted['type'];

        $VehicleSetting->vehicle_type = $request->vehicle_type;
        $VehicleSetting->vehicle_max = $request->vehicle_max;
        $VehicleSetting->vehicle_min = $request->vehicle_min;
        $VehicleSetting->modified_by = $created_by;
        $VehicleSetting->modified_type = $created_type;
        $VehicleSetting->save();
    
        return ResponseWithSuccessData($lang, $VehicleSetting, 1);
    }
    
}
