<?php

namespace App\Services\HR_Services;

use App\Models\SalarySystemTypesSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalarySystemTypesSettingService
{
    public function getAllSalarySettings(Request $request)
    {
        $salary = SalarySystemTypesSetting::query();
        $data = paginateOrGetAll($salary, $request, null, null);
        return $data;
    }

    public function createSalarySystemTypesSetting(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
            ]);
            if ($validator->fails()) {
                return respondError('Validation Error.', 400, $validator->errors());
            }
            $data = [
                'name' => $request->input('name'),
                'created_by' => authActionSave()['by'],
                'created_by_type' => authActionSave()['type'],
            ];


            $SalarySystemTypesSetting = SalarySystemTypesSetting::create($data);

            DB::commit();
            return $SalarySystemTypesSetting;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateSalarySystemTypesSetting(Request $request, $id)
    {
        
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return respondError('Validation Error.', 400, $validator->errors());
            }
            
            $SalarySystemTypesSetting = SalarySystemTypesSetting::find($id);
            if (!$SalarySystemTypesSetting) {
                return respondError('Salary system Setting not found.', 404);
            }
            
            $SalarySystemTypesSetting->update([
                'name' => $request->input('name'),
                'modified_by'        => authActionSave()['by'],
                'modified_by_type'   => authActionSave()['type'],
            ]);
            
            DB::commit();
            return $SalarySystemTypesSetting;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getSalarySystemTypesSettingById($id)
    {
        try {
            $SalarySystemTypesSetting = SalarySystemTypesSetting::find($id);
            if (!$SalarySystemTypesSetting) {
                return respondError('Salary system Setting not found.', 404);
            }
            return $SalarySystemTypesSetting;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
