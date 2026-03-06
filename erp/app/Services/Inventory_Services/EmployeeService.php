<?php


namespace App\Services\Inventory_Services;

use App\Models\InventoryEmployee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\DirectSupplyPermissionStatusSetting;
use App\Models\Employee;
use App\Models\Store;
use Illuminate\Support\Facades\App;

class EmployeeService
{

    public function getAllEmployees($request)
    {
        $employees = Employee::with('inventoryStores')
            ->where('flag', 'inventory')
            ->whereNull('deleted_at');

        // Filter by status if provided
        if ($request->has('status') && $request->status !== '') {
            $employees->where('status', $request->status);
        }
        if ($request->has('is_user') && $request->is_user !== '') {
            $employees->where('is_active', $request->is_user);
        }
        // Filter by warehouse (via relation inventoryStores.store_id)
        if ($request->has('warehouse') && $request->warehouse !== '') {
            $employees->whereHas('inventoryStores', function ($query) use ($request) {
                $query->where('store_id', $request->warehouse);
            });
        }
        return $employees;
    }

    public function createEmployee($request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $store = Store::where('id', $request->warehouse_id)->whereNull('deleted_at')->first();
        if (!$store && $request->warehouse_id) {
            return respondError($lang == 'en' ? 'The specified warehouse does not exist.' : 'المستودع المحدد غير موجود.', 400);
        }
        $employee = new Employee();
        $employee->first_name = $request->first_name;
        $employee->last_name = $request->last_name;
        $employee->employee_status_id  = 1;
        $employee->national_id = $request->national_id;
        $employee->country_code = $request->country_code;
        $employee->email = $request->email;
        $employee->country_id = $request->country_id;
        $employee->employee_code = $request->employee_code;
        $employee->phone_number = $request->phone_number;
        $employee->branch_id = $request->store ? $request->branch->id :auth('employee')->user()->branch_id;
        $employee->status = $request->status;
        $employee->flag = 'inventory';
        $employee->save();

        $assignemployee = new InventoryEmployee();
        $assignemployee->employee_id = $employee->id;
        $assignemployee->store_id = $store->id;
        $assignemployee->position = $request->position;
        $assignemployee->department = $request->department;
        $assignemployee->save();
        if (isset($request['image']) && $request->hasFile('image')) {
            UploadFile2('images/employees', 'image', $employee, $request->file('image'));
        }
        if (isset($request['contract']) && $request->hasFile('contract')) {
            UploadFile2('images/employees', 'contract_file', $employee, $request->file('contract'));
        }
        if (isset($request['id_photo']) && $request->hasFile('id_photo')) {
            UploadFile2('images/employees', 'national_id_photo', $employee, $request->file('id_photo'));
        }
        return $employee;
    }

    public function updateEmployee(Employee $employee, $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $store = null;
        if ($request->warehouse_id) {
            $store = Store::where('id', $request->warehouse_id)->whereNull('deleted_at')->first();
            if (!$store) {
                return respondError($lang == 'en' ? 'The specified warehouse does not exist.' : 'المستودع المحدد غير موجود.', 400);
            }
        }

        $employee->first_name   = $request->first_name ?? $employee->first_name;
        $employee->last_name    = $request->last_name ?? $employee->last_name;
        $employee->national_id  = $request->national_id ?? $employee->national_id;
        $employee->country_code = $request->country_code ?? $employee->country_code;
        $employee->country_id   = $store ? $store->country_id ?? $employee->country_id : $employee->country_id;
        $employee->employee_code = $request->employee_code ?? $employee->employee_code;
        $employee->phone_number = $request->phone_number ?? $employee->phone_number;
        $employee->email = $request->email ?? $employee->email;

        $employee->is_active    = $request->is_active ?? $employee->is_active;
        $employee->save();

        // Update or create InventoryEmployee link
        $assign = InventoryEmployee::firstOrNew(['employee_id' => $employee->id]);
        $assign->store_id   = $store ? $store->id : $assign->store_id;
        $assign->position   = $request->position ?? $assign->position;
        $assign->department = $request->department ?? $assign->department;
        $assign->save();

        // Handle file uploads (replace old if new provided)
         if (isset($request['image']) && $request->hasFile('image')) {
            UploadFile2('images/employees', 'image', $employee, $request->file('image'));
        }
        if (isset($request['contract']) && $request->hasFile('contract')) {
            UploadFile2('images/employees', 'contract_file', $employee, $request->file('contract'));
        }
        if (isset($request['id_photo']) && $request->hasFile('id_photo')) {
            UploadFile2('images/employees', 'national_id_photo', $employee, $request->file('id_photo'));
        }

        return $employee;
    }
}
