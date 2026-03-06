<?php

namespace App\Http\Controllers\Api\HR_APIs;

use Illuminate\Http\Request;
use App\Models\BonusSettings;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\BonusSettingsRequest;
use App\Services\HR_Services\BonusSettingsService;

class BonusSettingsController extends Controller
{
    protected $bonusSettingsService;

    public function __construct(BonusSettingsService $bonusSettingsService)
    {
        $this->bonusSettingsService = $bonusSettingsService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            // Get the query builder from service
            $query = $this->bonusSettingsService->index($request, false);

            // Apply pagination to the query
            $result = paginateOrGetAll($query, $request);

            return ResponseWithSuccessDataPaginated($lang, $result, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $exists = BonusSettings::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $BonusSettings = BonusSettings::findOrFail($id);
            return ResponseWithSuccessData($lang, $BonusSettings, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    // In your controller
    public function store(BonusSettingsRequest $request)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        try {
            $validated = $request->validated();
            $bonusSettings = $this->bonusSettingsService->createBonusSettings($validated);
            return ResponseWithSuccessData($lang, $bonusSettings, 1);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating bonus settings: ' . $e->getMessage());

            if ($e->getMessage() === '139') {
                $errorMessage = $lang === 'ar'
                    ? 'إعدادات المكافآت بهذه القيم موجودة مسبقاً.'
                    : 'Bonus settings with these values already exist.';

                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => $errorMessage,
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => true
                ], 400);
            }

            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function update(BonusSettingsRequest $request, BonusSettings $bonusSetting ,$id)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        try {
            $validated = $request->validated();
            $updatedSettings = $this->bonusSettingsService->updateBonusSettings($bonusSetting, $validated ,$id);
            // Return success response with updated data
            return response()->json([
                'status' => true,
                'message' => $lang === 'ar' ? 'طلب صحيح' : 'Request successful',
                'code' => 200,
                'data' => $updatedSettings
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating bonus settings: ' . $e->getMessage());

            if ($e->getMessage() === '139') {
                $errorMessage = $lang === 'ar'
                    ? 'إعدادات المكافآت بهذه القيم موجودة مسبقاً.'
                    : 'Bonus settings with these values already exist.';

                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => $errorMessage,
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => true
                ], 400);
            }

            return response()->json([
                'status' => false,
                'message' => $lang === 'ar' ? 'حدث خطأ ما' : 'An error occurred',
                'code' => 500,
                'data' => null
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        try {
            return $this->bonusSettingsService->delete($id, $lang);
        } catch (\Exception $e) {
            // Log the exception for debugging
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
