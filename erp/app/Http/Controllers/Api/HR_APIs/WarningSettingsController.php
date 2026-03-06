<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\WarningSettings;
use App\Http\Requests\WarningSettingsRequest;
use App\Services\HR_Services\WarningSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WarningSettingsController extends Controller
{
    protected $warningSettingsService;

    public function __construct(WarningSettingsService $warningSettingsService)
    {
        $this->warningSettingsService = $warningSettingsService;
    }

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $warningSettings = $this->warningSettingsService->getAllWarningSettings();

            if ($warningSettings->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No warning Settings found',
                    'code' => 404,
                    'data' => null
                ], 404);
            }

            // Transform all settings records
            $responseData = $warningSettings->map(function ($setting) {
                return [
                    'id' => (int)$setting->id,
                    'days' => (int)$setting->days,
                    'alert' => (int)$setting->alert,
                ];
            });

            return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching warning Settings: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(WarningSettingsRequest $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $validated = $request->validated(); // This will throw ValidationException if invalid
            // $userId = auth('employee')->user() ? auth('employee')->user()->id : null;

            $warningSettings = $this->warningSettingsService->createWarningSettings(
                $validated,
                // $userId
            );

            return ResponseWithSuccessData($lang, $warningSettings, 1);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating warning settings: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function update(WarningSettingsRequest $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');

            // Find the settings record
            $warningSettings = WarningSettings::find($id);

            if (!$warningSettings) {
                return RespondWithBadRequestData($lang, 8);
            }

            // Optional: Track who made the update
            // $userId = auth('employee')->user() ? auth('employee')->user()->id : null;
            // $request->merge(['updated_by' => $userId]);

            $updatedSettings = $this->warningSettingsService->updateWarningSettings($warningSettings, $request->validated());

            return ResponseWithSuccessData($lang, $updatedSettings, 1);
        } catch (\Exception $e) {
            Log::error('Error updating warning settings: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');

        try {
            $warningSettings = WarningSettings::find($id);

            if (!$warningSettings) {
                return RespondWithBadRequestData($lang, 8);
            }

            $deleteWarningSettings = $this->warningSettingsService->deleteWarningSettings($warningSettings);
            return ResponseWithSuccessData($lang, $deleteWarningSettings, 1);
        } catch (\Exception $e) {
            Log::error('Error deleting warning settings: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
