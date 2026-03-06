<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inventory\EmployeeResource;
use App\Models\Country;
use App\Models\Employee;
use App\Models\InventoryEmployee;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\SystemModule;
use App\Services\GeneralServicesModules\EmployeeService as GeneralServicesModulesEmployeeService;
use App\Services\Inventory_Services\EmployeeService;
use Google\Service\CloudAsset\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InventoryEmployeeController extends Controller
{
    protected $employeesService;

    public function __construct(GeneralServicesModulesEmployeeService $employeesService)
    {
        $this->employeesService = $employeesService;
    }
    public function getEmployees(Request $request)
    {
        $lang = $request->header('lang',  'ar');

        try {

            $employees = $this->employeesService->getAllEmployees($request, 'inventory');
            $result = paginateOrGetAll($employees, $request, null);

            $employees = EmployeeResource::collection($result['data']);
          $employees->each(function ($item)use ($request) {
                $item->setModule('inventory',$request);
            });
            return ResponseWithSuccessDataPaginated($lang, ['data' => $employees, 'meta' => $result['meta']], 1);
        } catch (\Exception $e) {
            Log::error('Error fetching inventory: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function showEmployee(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = $this->employeesService->showEmployee($request, $id, 'inventory');

        if (!$employee) {
            $message = $lang === 'ar' ? 'الموظف غير موجود' : 'Employee not found';
            return respondError($message, 404);
        }
        $employee = new EmployeeResource($employee);
        $employee->setModule('inventory', $request);
        return ResponseWithSuccessData($lang, $employee, 1);
    }

    public function createEmployee(Request $request)
    {
        $lang = $request->header('lang',  'ar');

        // try {
        $systemModuleStatus = checkModuleActivation(1); //HR module id

        if ($systemModuleStatus) {
            return respondError($lang == 'en' ?  'This API is stopped. Adding employees can only be done from the HR module.' : 'هذه الواجهة متوقفة. يمكن إضافة الموظفين فقط من وحدة الموارد البشرية.', 400);
        }
        $validator = Validator::make($request->all(), [
            'full_name'         => 'required|string|max:255',
            'email'             => 'required|email|unique:employees,email',
            'national_id'       => 'required|string|max:20|unique:employees,national_id',
            'country_code'      => 'required|string|max:20|exists:countries,phone_code',
            'phone_number'      => 'required|string|max:20|unique:employees,phone_number',
            'position'         => 'required|string|max:255',
            'department'     => 'required|string|max:255',
            'employee_code'     => 'required|string|max:50|unique:employees,employee_code',
            'warehouse_id'     => 'nullable|integer|exists:stores,id',
            'id_photo'          => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'image'          => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'contract'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'status' => 'required|in:0,1',
        ]);

        // Use Laravel’s built-in lang-based validation
        $country = Country::where('phone_code', $request->country_code)->first();

        $validator->after(function ($validator) use ($request, $country) {
            if ($country && isset($country->length)) {
                $expectedLength = (int) $country->length;

                if (strlen($request->phone_number) !== $expectedLength) {
                    $validator->errors()->add(
                        'phone_number',
                        __('validation.phone_length', ['length' => $expectedLength])
                    );
                }
            }
        });
        if ($validator->fails()) {
            return respondError(__('validation.validation_error'), 400, $validator->errors());
        }

        $nameParts = preg_split('/\s+/', trim($request->full_name), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        // Merge parsed name parts into request before creating
        $request->merge([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'country_id' => $country ? $country->id : null,
        ]);
        $employee = $this->employeesService->createEmployee($request, 'inventory');
        $employee->load(['inventoryStores']);
        $employee = new EmployeeResource($employee);
        $employee->setModule('inventory', $request);

        return ResponseWithSuccessData($lang, $employee, 1);
        // } catch (\Exception $e) {
        //     Log::error('Error creating employee: ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }

    public function updateEmployee(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = Employee::with('inventoryStores')->find($id);

        if (!$employee) {
            return respondError(__('validation.not_found'), 404);
        }

        $rules = [
            'full_name'         => 'sometimes|required|string|max:255',
            'email'             => 'sometimes|required|email|unique:employees,email,' . $employee->id,
            'national_id'       => 'sometimes|required|string|max:20|unique:employees,national_id,' . $employee->id,
            'country_code'      => 'sometimes|required|string|max:20|exists:countries,phone_code',
            'phone_number'      => 'sometimes|required|string|max:20|unique:employees,phone_number,' . $employee->id,
            'position'          => 'sometimes|required|string|max:255',
            'department'        => 'sometimes|required|string|max:255',
            'employee_code'     => 'nullable|string|max:50|unique:employees,employee_code,' . $employee->id,
            'warehouse_id'      => 'nullable|integer|exists:stores,id',
            'id_photo'          => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'image'             => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'contract'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'status'         => 'required|in:0,1',
        ];

        $validator = Validator::make($request->all(), $rules);

        $country = Country::where('phone_code', $request->country_code)->first();

        $validator->after(function ($validator) use ($request, $country) {
            if ($country && isset($country->length) && isset($request->phone_number)) {
                $expectedLength = (int) $country->length;
                if (strlen($request->phone_number) !== $expectedLength) {
                    $validator->errors()->add(
                        'phone_number',
                        __('validation.phone_length', ['length' => $expectedLength])
                    );
                }
            }
        });

        if ($validator->fails()) {
            return respondError(__('validation.validation_error'), 400, $validator->errors());
        }

        if ($request->filled('full_name')) {
            $nameParts = preg_split('/\s+/', trim($request->full_name), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName  = $nameParts[1] ?? '';

            $request->merge([
                'first_name' => $firstName,
                'last_name'  => $lastName,
            ]);
        }

        $employee = $this->employeesService->updateEmployee($employee, $request,'inventory');
        $employee->load(['inventoryStores']);

        $employee = new EmployeeResource($employee);
        $employee->setModule('inventory', $request);

        return ResponseWithSuccessData($lang, $employee, 1);
    }

    public function AccessDashboard(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        return $this->employeesService->handleDashboardAccess(
            $request,
            'inventory',
            $lang
        );
    }

    public function createGroupOfPermissions(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'name_en' => 'required|string|max:255|unique:roles,name',
            'name_ar' => 'required|string|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return respondError(
                __('Validation.error'),
                400,
                $validator->errors()
            );
        }

        DB::beginTransaction();

        try {
            $role = $this->employeesService->createRoleWithPermissions($request, 2);
            return  $role;
        } catch (\Exception $e) {
            DB::rollBack();
            return respondError($e->getMessage(), 500);
        }
    }
    public function assignPermissionsToEmployee(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        return $this->employeesService->assignPermissionsToEmployee($request, 'inventory', $lang);
    }

    // public function assignPermissionsToEmployee(Request $request)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);

    //     $validator = Validator::make($request->all(), [
    //         'employee_id' => [
    //             'required',
    //             'integer',
    //             Rule::exists('employees', 'id')->where('flag', 'inventory')
    //         ],
    //         'permissions' => 'nullable|array',
    //         'permissions.*' => 'integer|exists:permissions,id',
    //         'role_id' => 'nullable|integer|exists:roles,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return respondError(__('Validation.error'), 400, $validator->errors());
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $employee = Employee::findOrFail($request->employee_id);
    //         $requestedPermissions = $request->permissions ?? [];
    //         $roleId = $request->role_id ?? null;

    //         $currentRoles = $employee->getRoleNames()->toArray();
    //         $rolePermissions = [];

    //         // === CASE 1: Assign a new role ===
    //         if ($roleId) {
    //             $role = Role::with('permissions')->findOrFail($roleId);
    //             $rolePermissions = $role->permissions->pluck('id')->toArray();

    //             // Remove any previous roles before assigning new one
    //             foreach ($currentRoles as $roleName) {
    //                 if ($roleName !== $role->name) {
    //                     $employee->removeRole($roleName);
    //                 }
    //             }

    //             // Assign role if missing
    //             if (!$employee->hasRole($role->name)) {
    //                 $employee->assignRole($role->name);
    //             }

    //             // Direct permissions exclude role permissions
    //             $requestedPermissions = array_diff($requestedPermissions, $rolePermissions);
    //         }

    //         // === CASE 2: No role_id provided ===
    //         else {
    //             if (!empty($currentRoles)) {
    //                 // Collect all permissions from currently assigned roles
    //                 $rolePermissions = Permission::whereHas('roles', function ($q) use ($currentRoles) {
    //                     $q->whereIn('name', $currentRoles);
    //                 })->pluck('id')->toArray();

    //                 // Check if all role permissions are still selected
    //                 $missing = array_diff($rolePermissions, $requestedPermissions);

    //                 if (empty($missing)) {
    //                     // ✅ All role permissions are still checked
    //                     // → Keep the role, don't unassign it, and remove duplicates from direct permissions
    //                     $requestedPermissions = array_diff($requestedPermissions, $rolePermissions);
    //                 } else {
    //                     // ❌ Some role permissions unchecked → remove the role
    //                     foreach ($currentRoles as $roleName) {
    //                         $employee->removeRole($roleName);
    //                     }

    //                     // Keep only the explicitly selected permissions as direct
    //                     // (DO NOT merge old role permissions)
    //                     $requestedPermissions = array_unique($requestedPermissions);
    //                 }
    //             }
    //         }

    //         // === Only sync if there are actual permission changes ===
    //         $directPermissionNames = Permission::whereIn('id', $requestedPermissions)->pluck('name')->toArray();
    //         $employee->syncPermissions($directPermissionNames, false);

    //         DB::commit();

    //         return ResponseWithSuccessData($lang, [
    //             'employee_id' => $employee->id,
    //             'assigned_permissions' => $employee->getAllPermissions()->pluck('name'),
    //             'assigned_roles' => $employee->getRoleNames(),
    //         ], 1);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return respondError($e->getMessage(), 500);
    //     }
    // }
    public function getEmployeeRolesAndPermissions(Request $request, $employeeId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        return $this->employeesService
            ->getEmployeeRolesAndPermissionsService($employeeId, 'inventory', $lang);
    }

    public function getInventoryRoles(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $roles = $this->employeesService->getModuleRoles($request, 2);

        return ResponseWithSuccessData($lang, $roles, 1);
    }

    public function destroyEmployee(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $employee = Employee::find($id);
        if (!$employee) {
            $message = $lang === 'ar' ? 'الموظف غير موجود' : 'Employee not found';
            return respondError($message, 404);
        }
        $hasRelations =
            DB::table('direct_supply_permissions')->where('created_by', $id)->orWhere('modified_by', $id)->orWhere('deleted_by', $id)->exists() ||
            DB::table('purchase_requests')->where('created_by', $id)->orWhere('modified_by', $id)->orWhere('deleted_by', $id)->exists() ||
            DB::table('supply_orders')->where('created_by', $id)->orWhere('updated_by', $id)->orWhere('deleted_by', $id)->exists();

        if ($hasRelations) {
            return respondError(
                $lang == 'en'
                    ? 'Cannot delete employee because related records exist in Direct Supply Orders, Purchase Requests, or Supply Orders.'
                    : 'لا يمكن حذف الموظف لوجود بيانات مرتبطة به في أوامر التوريد المباشر أو طلبات الشراء أو أوامر التوريد.',
                400
            );
        }

        // Safe to delete
        $employee->deleted_by = auth('employee')->user()->id;
        $employee->deleted_at = now();
        $employee->save();
        return ResponseWithSuccessData($lang, null, 1);
    }
}
