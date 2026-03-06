<?php


namespace App\Services\SettingsServices;

use App\Models\Employee;
use App\Models\EducationLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequestData($lang, 2);
        }

        $education_level_query = EducationLevel::query();

        $education_level = paginateOrGetAll($education_level_query, $request, [], []);
        $meta = $education_level['meta'];

        foreach ($education_level['data'] as $eduLevel) {
            // Get employee IDs for this education level
            $employeeIds = DB::table('employee_educations')
                ->where('education_level_id', $eduLevel->id)
                ->pluck('employee_id')
                ->toArray();

            // Get all employee data
            $employees = Employee::whereIn('id', $employeeIds)->get();

            // Set employees count and full employee data
            $eduLevel->employees_count = $employees->count();
            $eduLevel->employees = $employees;
        }

        if (!$checkToken) {
            $education_level = $education_level['data']->makeVisible(['name_en', 'name_ar']);
        }
        $educationLevelCount = EducationLevel::count();


        $data = ['data' => ['EducationLevels' => $education_level, 'educationLevelCount' => $educationLevelCount], 'meta' => $meta];

        // $data = ['data' => ['EducationLevels' => $education_level, 'educationLevelCount' => $educationLevelCount], 'meta' => $meta];

        return ResponseWithSuccessData($lang, $data, 1);
    }
    public function show(Request $request, $checkToken, $id)
    {
        $lang = app()->getLocale();

        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequestData($lang, 2);
        }

        $education_level_select = EducationLevel::where('id', $id)->select('name_ar', 'name_en', 'id')->first();
        if (!$education_level_select) {
            return false;
        }
        $education_level = [
            'id' => $education_level_select->id,
            'name_ar' => $education_level_select->name_ar,
            'name_en' => $education_level_select->name_en,
        ];

        return $education_level;
    }

    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequestData($lang, 2);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|unique:education_levels,name_ar',
            'name_en' => 'required|string|unique:education_levels,name_en',
            'country_id' => 'nullable|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;
        $country_id = $request->country_id ?? null;

        // Check if either name already exists
        if (CheckExistColumnValue('education_levels', 'name_ar', $name_ar) || CheckExistColumnValue('education_levels', 'name_en', $name_en)) {
            return RespondWithBadRequest($lang, 9);
        }

        // $created_by = Auth::guard('employee')->user()->id;

        $educationLevel = new EducationLevel();
        $educationLevel->name_ar = $name_ar;
        $educationLevel->name_en = $name_en;
        $educationLevel->country_id = $country_id;
        $educationLevel->created_by = authActionSave()['by'];
        $educationLevel->created_by_type = authActionSave()['type'];
        $educationLevel->save();
        return ResponseWithSuccessData($lang, $educationLevel, 1);
    }

    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequestData($lang, 2);
        }
        $educationLevel = EducationLevel::find($id);
        if (!$educationLevel) {
            // dd(0);
            return RespondWithBadRequestData($lang, 8);
        }
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|unique:education_levels,name_ar,' . $id,
            'name_en' => 'required|string|unique:education_levels,name_en,' . $id,
            'country_id' => 'nullable|exists:countries,id',
        ]);

        if ($validator->fails()) {
            // dd(0);
            return RespondWithBadRequestWithData($validator->errors());
        }



        // Check if names changed
        if ($educationLevel->name_ar === $request->name_ar && $educationLevel->name_en === $request->name_en) {
            // dd(0);
            return RespondWithBadRequestData($lang, 10);
        }

        // Check uniqueness but exclude the current record
        $exists_ar = EducationLevel::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = EducationLevel::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

        if ($exists_ar || $exists_en) {
            // dd(0);
            return RespondWithBadRequest($lang, 9);
        }


        $educationLevel->name_ar = $request->name_ar;
        $educationLevel->name_en = $request->name_en;
        $educationLevel->country_id = $request->country_id ?? null;
        $educationLevel->modified_by = authActionSave()['by'];
        $educationLevel->modified_by_type = authActionSave()['type'];
        $educationLevel->save();

        return ResponseWithSuccessData($lang, $educationLevel, 1);
    }

    public function delete(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequestData($lang, 2);
        }

        $educationLevel = EducationLevel::find($id);
        if (!$educationLevel) {
            return  RespondWithBadRequestData($lang, 8);
        }
        $educationLevel->deleted_by = authActionSave()['by'];
        $educationLevel->deleted_by_type = authActionSave()['type'];
        $educationLevel->save();
        $educationLevel->delete();

        return RespondWithSuccessRequest($lang, 1);
    }
}
