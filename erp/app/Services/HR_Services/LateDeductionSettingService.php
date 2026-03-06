<?php

namespace App\Services\HR_Services;

use App\Models\LateDeductionSetting;

class LateDeductionSettingService
{
    public function index()
    {
        return LateDeductionSetting::all();
    }

    public function show($id)
    {
        return LateDeductionSetting::findOrFail($id);
    }

    public function store(array $data)
    {
        return LateDeductionSetting::create($data);
    }

    public function update($id, array $data)
    {
        $setting = LateDeductionSetting::findOrFail($id);
        $setting->update($data);
        return $setting;
    }

    public function destroy($id)
    {
        $setting = LateDeductionSetting::findOrFail($id);
        return $setting->delete();
    }
}
