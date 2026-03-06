<?php


namespace App\Services\HR_Services;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\EthnicBackground;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EthnicBackgroundService
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

        $ethnic_backgrounds = EthnicBackground::withCount('employees')->with('employees');

        // if (!$checkToken) {
        //     $ethnic_backgrounds = $ethnic_backgrounds->makeVisible(['name_en', 'name_ar']);
        // }

        return $ethnic_backgrounds;
    }
    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            // Return array instead of response to avoid nesting
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Token validation failed',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }


        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('ethnic_backgrounds')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('ethnic_backgrounds')->whereNull('deleted_at')
            ],
        ]);

        if ($validator->fails()) {
            // Return array instead of response
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }


        $name_ar = $request->name_ar;
        $name_en = $request->name_en;

        // Check if either name already exists
        if (CheckExistColumnValue('ethnic_backgrounds', 'name_ar', $name_ar) || CheckExistColumnValue('ethnic_backgrounds', 'name_en', $name_en)) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }


        $ethnicBackground = new EthnicBackground();
        $ethnicBackground->name_ar = $name_ar;
        $ethnicBackground->name_en = $name_en;
        $ethnicBackground->created_by = authActionSave()['by'];
        $ethnicBackground->created_by_type = authActionSave()['type'];
        $ethnicBackground->save();

        return $ethnicBackground;
    }

    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            // Return array for error instead of response
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Token validation failed',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        $ethnicBackground = EthnicBackground::find($id);
        if (!$ethnicBackground) {
            // Return array for error instead of response
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Ethnic background not found', // Fixed message
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('ethnic_backgrounds')->whereNull('deleted_at')->ignore($id)
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('ethnic_backgrounds')->whereNull('deleted_at')->ignore($id)
            ],
        ]);

        if ($validator->fails()) {
            // Return array for validation errors instead of response
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        // Check if names changed - use the already found $ethnicBackground
        if ($ethnicBackground->name_ar == $request->name_ar && $ethnicBackground->name_en == $request->name_en) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        // Check uniqueness but exclude the current record
        $exists_ar = EthnicBackground::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = EthnicBackground::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

        if ($exists_ar || $exists_en) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        $ethnicBackground->name_ar = $request->name_ar;
        $ethnicBackground->name_en = $request->name_en;
        $ethnicBackground->modified_by = authActionSave()['by'];
        $ethnicBackground->modified_by_type = authActionSave()['type'];
        $ethnicBackground->save();

        return $ethnicBackground;
    }

    public function delete(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        // Find the EthnicBackground by ID, or throw a 404 if not found
        $ethnicBackground = EthnicBackground::find($id);
        if (!$ethnicBackground) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        // Delete the EthnicBackground
        $ethnicBackground->deleted_by = authActionSave()['by'];
        $ethnicBackground->deleted_by_type = authActionSave()['type'];
        $ethnicBackground->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}
