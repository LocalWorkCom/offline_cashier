<?php

namespace App\Services\HR_Services;

use App\Models\WarningSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarningSettingsService
{
    public function getAllWarningSettings()
    {
        return WarningSettings::all(); // Assuming there's only one settings record
    }

    public function createWarningSettings(array $data, $userId = null)
    {
        DB::beginTransaction();

        try {
            $data['created_by'] = $userId;


            $warningSettings = WarningSettings::create($data);

            DB::commit();
            return $warningSettings;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WarningSettingsService Error: ' . $e->getMessage());
            throw $e; // Re-throw to let controller handle
        }
    }

    public function updateWarningSettings(WarningSettings $warningSettings, array $data, $userId = null)
    {
        DB::beginTransaction();

        try {
            $data['modified_by'] = $userId;
            $warningSettings->update($data);

            DB::commit();
            return $warningSettings;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function deleteWarningSettings(WarningSettings $warningSettings)
    {
        DB::beginTransaction();

        try {
            $warningSettings->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WarningSettingsService Error (delete): ' . $e->getMessage());
            throw $e;
        }
    }
}
