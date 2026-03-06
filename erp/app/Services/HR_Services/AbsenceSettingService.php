<?php

namespace App\Services\HR_Services;

use App\Models\AbsenceSetting;
use Illuminate\Http\Request;

class AbsenceSettingService
{
    public function index()
    {
        return AbsenceSetting::all();
    }

    public function show($id)
    {
        return AbsenceSetting::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'penalty_mode' => 'required|in:one_day,two_day,manual',
            'deduct_from' => 'required|in:basic,total',
            'allow_manual_override' => 'boolean',
        ]);

        return AbsenceSetting::create($data);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'penalty_mode' => 'required|in:one_day,two_day,manual',
            'deduct_from' => 'required|in:basic,total',
            'allow_manual_override' => 'boolean',
        ]);

        $setting = AbsenceSetting::findOrFail($id);
        $setting->update($data);

        return $setting;
    }

    public function destroy($id)
    {
        $setting = AbsenceSetting::findOrFail($id);
        $setting->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
