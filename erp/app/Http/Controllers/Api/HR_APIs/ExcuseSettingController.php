<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\ExcuseSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExcuseSettingController extends Controller
{
    /**
     * Show current excuse settings.
     */
    public function show(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $excuseSetting = ExcuseSetting::current();

            return ResponseWithSuccessData($lang, $excuseSetting, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching excuse settings: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Update the excuse settings.
     */
    public function update(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $excuseSetting = ExcuseSetting::current();

            // Validate the incoming data
            $request->validate([
                'max_daily_hours' => 'nullable|integer|min:0',
                'max_monthly_hours' => 'nullable|integer|min:0',
                'before_request_period' => 'required|integer|min:0',
                'is_paid' => 'required|boolean',
            ]);

            // Update the settings
            $excuseSetting->update($request->only([
                'max_daily_hours',
                'max_monthly_hours',
                'before_request_period',
                'is_paid',
            ]));

            return ResponseWithSuccessData($lang, $excuseSetting, 1);
        } catch (\Exception $e) {
            Log::error('Error updating excuse settings: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
