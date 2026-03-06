<?php


namespace App\Services\HR_Services;

use Illuminate\Http\Request;
use App\Models\EmployeeStatus;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeStatusService
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

        $employee_status = EmployeeStatus::withCount('employees')->with('employees');


        return $employee_status;
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
                Rule::unique('employee_status')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('employee_status')->whereNull('deleted_at')
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
        if (CheckExistColumnValue('employee_status', 'name_ar', $name_ar) || CheckExistColumnValue('employee_status', 'name_en', $name_en)) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }


        $employeeStatus = new EmployeeStatus();
        $employeeStatus->name_ar = $name_ar;
        $employeeStatus->name_en = $name_en;
        $employeeStatus->created_by = authActionSave()['by'];
        $employeeStatus->created_by_type = authActionSave()['type'];
        $employeeStatus->save();

        return $employeeStatus;
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
        $employeeStatus = EmployeeStatus::find($id);
        if (!$employeeStatus) {
            // Return array for error instead of response
            return [
                'code' => 404,
                'status' => false,
                'message' => 'employee Status not found', // Fixed message
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }
        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('employee_status')->whereNull('deleted_at')->ignore($id)
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('employee_status')->whereNull('deleted_at')->ignore($id)
            ],
        ]);

        if ($validator->fails()) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        $employeeStatus = EmployeeStatus::find($id);

        // Check if names changed
        if ($employeeStatus->name_ar == $request->name_ar && $employeeStatus->name_en == $request->name_en) {
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
        $exists_ar = EmployeeStatus::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = EmployeeStatus::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

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

        $employeeStatus->name_ar = $request->name_ar;
        $employeeStatus->name_en = $request->name_en;
        $employeeStatus->modified_by = authActionSave()['by'];
        $employeeStatus->modified_by_type = authActionSave()['type'];
        $employeeStatus->save();

        return $employeeStatus;
    }

    public function delete(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        // Find the employeeStatus by ID, or throw a 404 if not found
        $employeeStatus = EmployeeStatus::find($id);
        if (!$employeeStatus) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        // Delete the employeeStatus
        $employeeStatus->deleted_by = authActionSave()['by'];
        $employeeStatus->deleted_by_type = authActionSave()['type'];
        $employeeStatus->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}
