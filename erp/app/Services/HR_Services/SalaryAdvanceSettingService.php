<?php

namespace App\Services\HR_Services;

use App\Models\SalaryAdvanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalaryAdvanceSettingService
{
    public function getAllSalarySettings(Request $request)
    {
        $salary = SalaryAdvanceSetting::query();
        $data = paginateOrGetAll($salary, $request, null, null);
        return $data;
    }

    public function createSalaryAdvanceSetting(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'type'            => 'required|in:specific_date,month',
                'percentage_type' => 'required|string|in:base_salary,total_salary',
                'max_percentage'  => 'required|numeric', // fixed typo "douple" → numeric
            ]);

            if ($validator->fails()) {
                return respondError('Validation Error.', 400, $validator->errors());
            }

            // 🔹 Delete existing setting(s)
            SalaryAdvanceSetting::query()->delete();

            $data = [
                'type'             => $request->input('type'),
                'percentage_type'  => $request->input('percentage_type'),
                'max_percentage'   => $request->input('max_percentage'),
                'created_by'       => authActionSave()['by'],
                'created_by_type'  => authActionSave()['type'],
            ];

            $SalaryAdvanceSetting = SalaryAdvanceSetting::create($data);

            DB::commit();
            return $SalaryAdvanceSetting;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateSalaryAdvanceSetting(Request $request, $id)
    {

        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:specific_date,month',
                'percentage_type'   => 'required|string|in:base_salary,total_salary',
                'max_percentage' => 'required|douple',
            ]);

            if ($validator->fails()) {
                return respondError('Validation Error.', 400, $validator->errors());
            }

            $SalaryAdvanceSetting = SalaryAdvanceSetting::find($id);
            if (!$SalaryAdvanceSetting) {
                return respondError('Salary Advance Setting not found.', 404);
            }

            $SalaryAdvanceSetting->update([
                'type' => $request->input('type'),
                'percentage_type'   => $request->input('percentage_type'),
                'max_percentage'   => $request->input('max_percentage'),
                'modified_by'        => authActionSave()['by'],
                'modified_by_type'   => authActionSave()['type'],
            ]);

            DB::commit();
            return $SalaryAdvanceSetting;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function deleteSalaryAdvanceSetting($is)
    {
        DB::beginTransaction();

        try {
            $SalaryAdvanceSetting = SalaryAdvanceSetting::find($is);
            if (!$SalaryAdvanceSetting) {
                return respondError('Salary Advance Setting not found.', 404);
            }
            $SalaryAdvanceSetting->deleted_by = authActionSave()['by'];
            $SalaryAdvanceSetting->deleted_by_type = authActionSave()['type'];
            $SalaryAdvanceSetting->save();
            $SalaryAdvanceSetting->delete();
            DB::commit();
            return $SalaryAdvanceSetting;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getSalaryAdvanceSettingById($id)
    {
        try {
            $SalaryAdvanceSetting = SalaryAdvanceSetting::find($id);
            if (!$SalaryAdvanceSetting) {
                return respondError('Salary Advance Setting not found.', 404);
            }
            return $SalaryAdvanceSetting;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
