<?php


namespace App\Services\HR_Services;

use Illuminate\Http\Request;
use App\Models\EducationLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EducationLevelService
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    public function index(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $education_level = EducationLevel::get();
        foreach ($education_level as $uni) {
            $uni->employees_count = DB::table('employee_educations')
                ->where('education_level_id', $uni->id)
                ->distinct('employee_id')
                ->count('employee_id');
        }
        if (!$checkToken) {
            $education_level = $education_level->makeVisible(['name_en', 'name_ar']);
        }

        return ResponseWithSuccessData($lang, $education_level, 1);
    }
    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;

        // Check if either name already exists
        if (CheckExistColumnValue('education_levels', 'name_ar', $name_ar) || CheckExistColumnValue('education_levels', 'name_en', $name_en)) {
            return RespondWithBadRequest($lang, 9);
        }

        // $created_by = Auth::guard('admin')->user()->id;

        $educationLevel = new EducationLevel();
        $educationLevel->name_ar = $name_ar;
        $educationLevel->name_en = $name_en;
        $educationLevel->created_by = authActionSave()['by'];
        $educationLevel->created_by_type = authActionSave()['type'];
        $educationLevel->save();

        return RespondWithSuccessRequest($lang, 1);
    }

    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        $educationLevel = EducationLevel::find($id);
        if (!$educationLevel) {
            return RespondWithBadRequestData($lang, 8);
        }

        // Check if names changed
        if ($educationLevel->name_ar == $request->name_ar && $educationLevel->name_en == $request->name_en) {
            return RespondWithBadRequestData($lang, 10);
        }

        // Check uniqueness but exclude the current record
        $exists_ar = EducationLevel::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = EducationLevel::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

        if ($exists_ar || $exists_en) {
            return RespondWithBadRequest($lang, 9);
        }

        // $modified_by= Auth::guard('admin')->user()->id;

        $educationLevel->name_ar = $request->name_ar;
        $educationLevel->name_en = $request->name_en;
        $educationLevel->modified_by = authActionSave()['by'];
        $educationLevel->modified_by_type = authActionSave()['type'];
        $educationLevel->save();

        return RespondWithSuccessRequest($lang, 1);
    }

    public function delete(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        // Find the EducationLevel by ID, or throw a 404 if not found
        $educationLevel = EducationLevel::find($id);
        if (!$educationLevel) {
            return  RespondWithBadRequestData($lang, 8);
        }
        // Delete the EducationLevel
        $educationLevel->deleted_by = authActionSave()['by'];
        $educationLevel->deleted_by_type = authActionSave()['type'];
        $educationLevel->save();
        $educationLevel->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}
