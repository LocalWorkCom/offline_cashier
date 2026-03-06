<?php

namespace App\Services\HR_Services;

use App\Models\JobTypeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class jobTypeSettingService
{
    public function getAlljobTypeSettingService(Request $request)
    {
        $job = JobTypeSetting::withCount('employees')->with('employees');
        $data = paginateOrGetAll($job, $request, null, null);
        return $data;
    }

    public function createjobTypeSettingService(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string',
                'name_en' => 'required|string',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return respondError('Validation Error.', 400, $validator->errors());
            }
            $data = [
                'name_ar' => $request->input('name_ar'),
                'name_en' => $request->input('name_en'),
                'description_ar' => $request->input('description_ar'),
                'description_en' => $request->input('description_en'),
                'created_by' => authActionSave()['by'],
                'created_by_type' => authActionSave()['type'],
            ];


            $jobTypeSetting = JobTypeSetting::create($data);

            DB::commit();
            return $jobTypeSetting;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updatejobTypeSetting(Request $request, $id)
    {
        
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string',
                'name_en' => 'required|string',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return respondError('Validation Error.', 400, $validator->errors());
            }
            
            $jobTypeSetting = JobTypeSetting::find($id);
            if (!$jobTypeSetting) {
                return respondError('job type Setting not found.', 404);
            }
            
            $jobTypeSetting->update([
                'name_ar' => $request->input('name_ar'),
                'name_en' => $request->input('name_en'),
                'description_ar' => $request->input('description_ar'),
                'description_en' => $request->input('description_en'),
                'modified_by'        => authActionSave()['by'],
                'modified_by_type'   => authActionSave()['type'],
            ]);
            
            DB::commit();
            return $jobTypeSetting;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getjobTypeSettingById($id)
    {
        try {
            $jobTypeSetting = JobTypeSetting::withCount('employees')->with('employees')->find($id);
            if (!$jobTypeSetting) {
                return respondError('job type Setting not found.', 404);
            }
            return $jobTypeSetting;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteJobTypeSetting($is)
    {
        DB::beginTransaction();

        try {
            $jobTypeSetting = JobTypeSetting::find($is);
            if (!$jobTypeSetting) {
                return respondError('job type Setting not found.', 404);
            }
            $jobTypeSetting->deleted_by = authActionSave()['by'];
            $jobTypeSetting->deleted_by_type = authActionSave()['type'];
            $jobTypeSetting->save();
            $jobTypeSetting->delete();
            DB::commit();
            return $jobTypeSetting;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
