<?php

namespace App\Services\HR_Services;

use App\Models\BonusSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\BonusSettingsRequest;

class BonusSettingsService
{
    public function index()
    {
        try {
            // If you only expect one settings record
            return BonusSettings::query();
        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve bonus settings: ' . $e->getMessage());
        }
    }

    // In your BonusSettingsService
    public function createBonusSettings(array $data)
    {
        DB::beginTransaction();

        try {
            // Check for existing duplicate settings
            $existingSettings = BonusSettings::where([
                ['max_bonus_percentage', '=', $data['max_bonus_percentage']],
                ['fixed_bonus_cap', '=', $data['fixed_bonus_cap']],
                ['days_convertible_to_money', '=', $data['days_convertible_to_money']],
                ['disbursement_timing', '=', $data['disbursement_timing']]
            ])->exists();

            if ($existingSettings) {
                throw new \Exception('139'); // Throw exception with error code
            }

            $data['created_by'] = authActionSave()['by'];
            $data['created_by_type'] = authActionSave()['type'];
            $bonusSettings = BonusSettings::create($data);

            DB::commit();
            return $bonusSettings;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BonusSettingsService Error: ' . $e->getMessage());
            throw $e; // Re-throw the exception
        }
    }

    public function updateBonusSettings(BonusSettings $bonusSettings, array $data ,$id)
    {
        DB::beginTransaction();
        try {
            // Check for existing duplicate settings
            $existingSettings = BonusSettings::where([
                ['max_bonus_percentage', '=', $data['max_bonus_percentage']],
                ['fixed_bonus_cap', '=', $data['fixed_bonus_cap']],
                ['days_convertible_to_money', '=', $data['days_convertible_to_money']],
                ['disbursement_timing', '=', $data['disbursement_timing']],
                ['id', '!=', $bonusSettings->id]
            ])->exists();
            if ($existingSettings) {
                throw new \Exception('139');
            }
            $bonusSettings2 = BonusSettings::where('id',$id)->first();
            $data['modified_by'] = authActionSave()['by'];
            $data['modified_by_type'] = authActionSave()['type'];

            // Update using the model's update method
            $bonusSettings2->update($data);

            // Refresh the model to get updated data
            $bonusSettings2->refresh();
            // dd($bonusSettings2)
            DB::commit();
            return $bonusSettings2;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BonusSettingsService Update Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id, $lang)
    {
        $bonusSettings = BonusSettings::find($id);

        if (!$bonusSettings) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        try {
            $bonusSettings->deleted_by = authActionSave()['by'];
            $bonusSettings->deleted_by_type = authActionSave()['type'];
            $bonusSettings->save();
            $bonusSettings->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
