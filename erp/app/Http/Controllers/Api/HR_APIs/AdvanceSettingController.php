<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdvanceSettingResource;
use App\Models\AdvanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvanceSettingController extends Controller
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

        $settings = AdvanceSetting::all();

        return ResponseWithSuccessData($lang, AdvanceSettingResource::collection($settings), 1);
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
            'min_salary' => 'required|numeric',
            'amount' => 'required|numeric',
            'months' => 'required|numeric',
            'amount_per_month' => 'required|numeric',
        ]);

        $setting = new AdvanceSetting();
        $setting->min_salary = $data['min_salary'];
        $setting->amount = $data['amount'];
        $setting->months = $data['months'];
        $setting->amount_per_month = $data['amount_per_month'];
        $setting->created_by = Auth::guard('api')->user()->id;
        $setting->save();

        return ResponseWithSuccessData($lang, AdvanceSettingResource::make($setting), 1);
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

        $setting = AdvanceSetting::find($id);

        if (!$setting) {
            return RespondWithBadRequestData($lang, 2);
        }

        return ResponseWithSuccessData($lang, AdvanceSettingResource::make($setting), 1);
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
            'min_salary' => 'sometimes|required|numeric',
            'amount' => 'sometimes|required|numeric',
            'months' => 'sometimes|required|numeric',
            'amount_per_month' => 'sometimes|required|numeric',
        ]);
        $setting = AdvanceSetting::find($id);
        if (!$setting) {
            return RespondWithBadRequestData($lang, 8);
        }
        if(isset($data['min_salary'])){
            $setting->min_salary = $data['min_salary'];
        }
        if(isset($data['amount'])){
            $setting->amount = $data['amount'];
        }
        if(isset($data['months'])){
            $setting->months = $data['months'];
        }
        if(isset($data['amount_per_month'])){
            $setting->amount_per_month = $data['amount_per_month'];
        }
        $setting->modified_by = Auth::guard('api')->user()->id;
        $setting->save();

        return ResponseWithSuccessData($lang, AdvanceSettingResource::make($setting), 1);
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

        $setting = AdvanceSetting::find($id);

        if (!$setting) {
            return RespondWithBadRequestData($lang, 2);
        }
        $setting->deleted_by = Auth::guard('api')->user()->id;
        $setting->save();
        $setting->delete();

        return ResponseWithSuccessData($lang, AdvanceSettingResource::make($setting), 1);
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

        $setting = AdvanceSetting::withTrashed()->find($id);

        if (!$setting) {
            return RespondWithBadRequestData($lang, 2);
        }

        $setting->restore();

        return ResponseWithSuccessData($lang, AdvanceSettingResource::make($setting), 1);
    }

}
