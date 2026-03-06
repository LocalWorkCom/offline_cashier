<?php

namespace App\Services\HR_Services;

use App\Models\ViolationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ViolationTypeService
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $violation_types = ViolationType::all();

        if (!$checkToken) {
            // Include 'hexa_code' in the visible fields
            $violation_types = $violation_types->makeVisible(['name_en', 'name_ar']);
        }

        return ResponseWithSuccessData($lang, $violation_types, 1);
    }

    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        // Validate the input including 'hexa_code'
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'string',

        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;

        if (CheckExistColumnValue('violation_types', 'name_ar', $name_ar) || CheckExistColumnValue('violation_types', 'name_en', $name_en)) {
            return RespondWithBadRequest($lang, 9);
        }

        $created_by =  Auth::guard('admin')->user()->id;

        // Create the new violation_type
        $violation_type = new ViolationType();
        $violation_type->name_ar = $name_ar;
        $violation_type->name_en = $name_en;
        $violation_type->created_by = $created_by;
        $violation_type->save();

        return RespondWithSuccessRequest($lang, 1);
    }

    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        // Validate the input including 'hexa_code'
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'string',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        // Retrieve the violation_type by ID, or throw an exception if not found
        $violation_type = ViolationType::find($id);
        if (!$violation_type) {
            return  RespondWithBadRequestData($lang, 8);
        }

        if (
            $violation_type->name_ar == $request->name_ar && $violation_type->name_en == $request->name_en && $violation_type->hexa_code == $request->hexa_code
        ) {
            return  RespondWithBadRequestData($lang, 10);
        }

        if (CheckExistColumnValue('violation_types', 'name_ar', $request->name_ar) && CheckExistColumnValue('violation_types', 'name_en', $request->name_en)) {
            return RespondWithBadRequest($lang, 9);
        }

        $modified_by =  Auth::guard('admin')->user()->id;

        // Assign the updated values to the violation_type model
        $violation_type->name_ar = $request->name_ar;
        $violation_type->name_en = $request->name_en;
        $violation_type->modified_by = $modified_by;

        // Update the violation_type in the database
        $violation_type->save();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }

    public function delete(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        // Check token
        if ($checkToken && !CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }

        // Find the violation_type by ID, or throw a 404 if not found
        $violation_type = ViolationType::find($id);
        if (!$violation_type) {
            return RespondWithBadRequestData($lang, 8);
        }

        // Check if the violation_type is associated with any products
        $activeProductviolation_types = $violation_type->productviolation_types()->count();
        if ($activeProductviolation_types > 0) {
            return CustomRespondWithBadRequest(__('violation_type.The violation_type has relations'));
        }

        // Delete the violation_type
        $violation_type->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}
