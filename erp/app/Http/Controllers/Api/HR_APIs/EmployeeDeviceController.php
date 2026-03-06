<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeDeviceController extends Controller
{
    public function addEmployeeToDevice(Request $request)
    {
        try {
            $request->validate([
                'emp_code' => 'required|string|max:20',
                'department_id' => 'required|integer',
                'area_ids' => 'required|array',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'hire_date' => 'nullable|date_format:Y-m-d',
                'gender' => 'nullable|in:S,F,M',
                'mobile' => 'nullable|string|max:15|regex:/^\+?[0-9]*$/',
                'email' => 'nullable|email',
            ]);

            $data = $request->only([
                'emp_code',
                'department_id',
                'area_ids',
                'first_name',
                'last_name',
                'hire_date',
                'gender',
                'mobile',
                'email',
            ]);

            // Call the helper function to add the employee to the device
            $response = addEmployeeToDevice(
                $data['emp_code'],
                $data['department_id'],
                $data['area_ids'],
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
                $data['hire_date'] ?? null,
                $data['gender'] ?? null,
                $data['mobile'] ?? null,
                $data['email'] ?? null
            );

            return response()->json(['success' => true, 'data' => $response], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
