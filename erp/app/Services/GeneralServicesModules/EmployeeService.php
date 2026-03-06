<?php


namespace App\Services\GeneralServicesModules;

use App\Models\Country;
use App\Models\InventoryEmployee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\DirectSupplyPermissionStatusSetting;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{

    public function getAllEmployees(Request $request, string $module)
    {
        $employees = Employee::query()
            ->whereNull('deleted_at');

        if ($module === 'inventory') {
            $employees->with('inventoryStores')->where('flag', 'inventory');
        }

        if ($module === 'purchase') {
            $employees->with('department')->where('flag', 'purchase');
        }

        if ($request->filled('status')) {
            $employees->where('status', $request->status);
        }

        if ($request->filled('is_user')) {
            $employees->where('is_active', $request->is_user);
        }
        if ($request->filled('from')) {
            $employees->where('created_at', $request->from);
        }

        if ($request->filled('to')) {
            $employees->where('created_at', $request->to);
        }
        // Inventory warehouse filter
        if ($module === 'inventory' && $request->filled('warehouse')) {
            $employees->whereHas('inventoryStores', function ($q) use ($request) {
                $q->where('store_id', $request->warehouse);
            });
        }
        if ($module === 'purchase' && $request->filled('department')) {
            $employees->where('department', $request->department_id);
        }
        return $employees;
    }
    public function showEmployee(Request $request, ?int $id, string $module)
    {
        // If id not provided → use authenticated employee
        $employeeId = $id ?? auth('employee')->id();
        if (!$employeeId) {
            return null;
        }

        $query = Employee::query()
            ->whereNull('deleted_at')
            ->where('id', $employeeId);

        if ($module === 'inventory') {
            $query->with('inventoryStores')
                ->where('flag', 'inventory');
        }

        if ($module === 'purchase') {
            $query->with('department')
                ->where('flag', 'purchase');
        }

        return $query->first();
    }

    public function createEmployee($request, $module)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        $employee = new Employee();
        $employee->first_name        = $request->first_name;
        $employee->last_name         = $request->last_name;
        $employee->employee_status_id = 1;
        $employee->national_id       = $request->national_id;
        $employee->country_code      = $request->country_code;
        $employee->email             = $request->email;
        $employee->country_id        = $request->country_id;
        $employee->employee_code     = $request->employee_code;
        $employee->phone_number      = $request->phone_number;
        $employee->branch_id         = auth('employee')->user()->branch_id;
        $employee->employee_status_id            = $request->status == 0 ? 1 :  $request->status;
        $employee->flag              = $module;
        if ($module == 'purchase') {
            $employee->department_id            = $request->department;
            $employee->position_name            = $request->position;
        }
        $employee->save();
        if ($module === 'inventory') {
            $this->saveInventoryEmployee($employee, $request, $lang);
        }

        if ($request->filled('role')) {
            $employee->roles()->attach($request->role);
        }

        return $employee;
    }

    public function updateEmployee(Employee $employee, $request, string $module)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        // Common employee fields
        $employee->first_name   = $request->first_name ?? $employee->first_name;
        $employee->last_name    = $request->last_name ?? $employee->last_name;
        $employee->national_id  = $request->national_id ?? $employee->national_id;
        $employee->country_code = $request->country_code ?? $employee->country_code;
        $employee->employee_code = $request->employee_code ?? $employee->employee_code;
        $employee->phone_number = $request->phone_number ?? $employee->phone_number;
        $employee->email        = $request->email ?? $employee->email;
        $employee->employee_status_id = isset($request->status)
            ? ($request->status == 0 ? 1 : $request->status)
            : $employee->employee_status_id;

        // MODULE-SPECIFIC UPDATES
        if ($module === 'purchase') {
            // Update foreign keys for purchase module
            $employee->department_id = $request->department ?? $employee->department_id;
            $employee->position_name   = $request->position ?? $employee->position_name;
        }

        $employee->save();

        if ($module === 'inventory') {
            $this->saveInventoryEmployee($employee, $request, $lang);
        }
        if ($request->filled('role')) {
            $employee->roles()->sync([$request->role]);
        }


        return $employee;
    }
    private function saveInventoryEmployee(Employee $employee, $request, string $lang)
    {
        // Find warehouse if provided
        $store = null;
        if ($request->filled('warehouse_id')) {
            $store = Store::where('id', $request->warehouse_id)
                ->whereNull('deleted_at')
                ->first();
            if (!$store) {
                throw new \Exception(
                    $lang === 'en'
                        ? 'The specified warehouse does not exist.'
                        : 'المستودع المحدد غير موجود.'
                );
            }
        }

        // Get existing pivot or create new
        $assign = InventoryEmployee::firstOrNew(['employee_id' => $employee->id]);

        // Assign values
        $assign->store_id   = $store ? $store->id : $assign->store_id;
        $assign->position   = $request->position ?? $assign->position;
        $assign->department = $request->department ?? $assign->department;
        $assign->save();

        // Handle files
        if (isset($request['image']) && $request->hasFile('image')) {
            UploadFile2('images/employees', 'image', $employee, $request->file('image'));
        }
        if (isset($request['contract']) && $request->hasFile('contract')) {
            UploadFile2('images/employees', 'contract_file', $employee, $request->file('contract'));
        }
        if (isset($request['id_photo']) && $request->hasFile('id_photo')) {
            UploadFile2('images/employees', 'national_id_photo', $employee, $request->file('id_photo'));
        }
        return $assign;
    }

    public function getModuleRoles(Request $request, string $module)
    {
        $lang = $request->header('lang', 'ar');

        $roles = Role::where('module_id', $module)
            ->select('id', 'name')
            ->get()
            ->toArray();

        return  $roles;
    }
    public function createRoleWithPermissions($request, $moduleId)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        $role = new Role();
        $role->name = $request->name_en;
        $role->name_en = $request->name_en;
        $role->name_ar = $request->name_ar;
        $role->module_id = $moduleId;
        $role->guard_name = 'employee';
        $role->save();

        $validPermissionIds = Permission::query()
            ->where('guard_name', 'employee')
            ->where('is_active', 1)
            ->pluck('id')
            ->toArray();

        $requestedPermissionIds = $request->permissions ?? [];

        $filteredPermissions = array_intersect($requestedPermissionIds, $validPermissionIds);

        if (empty($filteredPermissions)) {
            return respondError(__('validation.permissioncannotbeassigned'), 400);
        }

        $role->permissions()->sync($filteredPermissions);


        logPermissionsAndRoleChanges('create_role', [
            'role_id' => $role->id,
            'permission_ids' => $filteredPermissions,
            'assigned_by' => auth('employee')->id(),
        ]);

        return ResponseWithSuccessData($lang, [
            'role' => $role,
            'assigned_permissions' => $filteredPermissions,
        ], 1);
    }
    public function getEmployeeRolesAndPermissionsService($employeeId, $moduleFlag, $lang)
    {
        try {
            // Load employee with roles & permissions
            $employee = Employee::with(['roles', 'permissions'])->where('flag', $moduleFlag)->find($employeeId);

            if (!$employee) {
                $message = $lang === 'ar' ? 'الموظف غير موجود' : 'Employee not found';
                return respondError($message, 404);
            }

            //  Get all permissions via roles (role-based)
            $rolePermissions = Permission::query()
                ->where('guard_name', 'employee')
                ->where('is_active', 1)
                ->whereHas('roles', function ($q) use ($employee) {
                    $q->whereIn('roles.id', $employee->roles->pluck('id'));
                })
                ->get();

            // Get directly assigned permissions
            $directPermissions = $employee->permissions()
                ->where('is_active', 1)
                ->get();

            // Combine permissions
            $allPermissions = $rolePermissions->merge($directPermissions)->unique('id')->values();

            $formattedPermissions = $allPermissions->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'is_global' => $p->is_global,
                    'name_en' => $p->name_en ?? null,
                    'name_ar' => $p->name_ar ?? null,
                ];
            })->values();

            return ResponseWithSuccessData($lang, [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'roles' => $employee->roles->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'name' => $r->name,
                        ];
                    }),
                ],
                'permissions' => $formattedPermissions,
            ], 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 500);
        }
    }

    public function handleDashboardAccess($request, $module, $lang)
    {
        try {
            $authUser = auth('employee')->user();

            $rules = [
                'employee_id'   => 'required|integer|exists:employees,id',
                'password'      => 'required|string|min:6',
                'role_id'       => 'nullable|integer|exists:roles,id',
                'is_active'     => 'nullable|integer|in:0,1',
                'name'          => 'required|string',
            ];

            if ($module === 'inventory') {
                $rules['warehouse_ids'] = 'required|array|min:1';
                $rules['warehouse_ids.*'] = 'integer|exists:stores,id';
            }

            if ($module === 'purchase') {
                $rules['full_name'] = 'nullable|string|max:255';
                $rules['email'] = 'nullable|email|unique:employees,email,' . $request->employee_id;
                $rules['national_id'] = 'nullable|string|max:20|unique:employees,national_id,' . $request->employee_id;
                $rules['country_code'] = 'nullable|max:20|exists:countries,phone_code';
                $rules['phone_number'] = 'nullable|max:20|unique:employees,phone_number,' . $request->employee_id;
                $rules['position'] = 'nullable|string|max:255';
                $rules['department'] = 'nullable|exists:departments,id';
            }

            $validator = Validator::make($request->all(), $rules);

            $validator->after(function ($validator) use ($request, $module) {
                $employee = Employee::find($request->employee_id);

                if ($employee && $employee->flag !== $module) {
                    $validator->errors()->add(
                        'employee_id',
                        __('validation.' . $module . '_only')
                    );
                }
            });

            if ($module === 'purchase') {
                $country = Country::where('phone_code', $request->country_code)->first();
                $validator->after(function ($validator) use ($request, $country) {
                    if ($country && isset($country->length)) {
                        if (strlen($request->phone_number) !== (int)$country->length) {
                            $validator->errors()->add(
                                'phone_number',
                                __('validation.phone_length', ['length' => $country->length])
                            );
                        }
                    }
                });
            }

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق من البيانات.',
                    400,
                    $validator->errors()
                );
            }

            $employee = Employee::find($request->employee_id);
            if (!($authUser->hasRole('HR_Manager', 'employee') || $authUser->hasRole('superAdmin', 'employee'))) {
                if ($module === 'purchase') {
                    if ($authUser->flag !== $employee->flag) {
                        return respondError(
                            $lang == 'en'
                                ? 'You are not authorized to create dashboard access for this employee.'
                                : 'غير مصرح لك بإنشاء وصول للوحة التحكم لهذا الموظف.',
                            403
                        );
                    }
                } elseif ($module === 'inventory') {
                    if ($authUser->flag !== $employee->flag || $authUser->branch_id !== $employee->branch_id) {
                        return respondError(
                            $lang == 'en'
                                ? 'You are not authorized to create dashboard access for this employee.'
                                : 'غير مصرح لك بإنشاء وصول للوحة التحكم لهذا الموظف.',
                            403
                        );
                    }
                }
            }

            if ($request->is_active == 1) {
                $employee->is_active = 1;
                $employee->password = Hash::make($request->password);

                if ($module === 'purchase') {
                    $nameParts = preg_split('/\s+/', trim($request->full_name), 2);
                    $employee->first_name = $nameParts[0] ?? '';
                    $employee->last_name = $nameParts[1] ?? '';
                    $employee->email = $request->email ?? $employee->email;
                    $employee->phone_number = $request->phone_number ?? $employee->phone_number;
                    $employee->position_name = $request->position ?? $employee->position_name;
                    $employee->department_id = $request->department ?? $employee->department_id;
                }

                $employee->save();
            }

            if ($module === 'inventory' && $request->has('warehouse_ids')) {
                $employee->inventoryStores()->sync($request->warehouse_ids);
            }

            if ($request->filled('role_id')) {
                $role = Role::where('id', $request->role_id)
                    ->where('guard_name', 'employee')
                    ->first();

                if ($role) {
                    $employee->syncRoles([$role->name]);
                } else {
                    return respondError(
                        $lang == 'en' ? 'Invalid role for employee guard.' : 'دور غير صالح للموظف.',
                        400
                    );
                }
            }

            logPermissionsAndRoleChanges('update_role_permissions', [
                'role_id'        => $role->id ?? null,
                'permission_ids' => null,
                'extra_data'     => [
                    'role_name'   => $role->name ?? null,
                    'assigned_to' => $employee->id,
                    'assigned_by' => $authUser->id,
                ],
            ]);

            return ResponseWithSuccessData($lang, [
                'employee_id' => $employee->id,
                'name' => $employee->full_name,
                'user_name' => $employee->user_name,
                'access_dashboard' => (bool)$employee->is_active,
                'assigned_warehouses' =>
                $module === 'inventory'
                    ? $employee->inventoryStores()->pluck('store_id')
                    : null,
                'assigned_role' => $employee->roles->pluck('name'),
                'assigned_permissions' => $employee->permissions->pluck('name'),
            ], 1);
        } catch (\Exception $e) {
            return respondError(
                $lang == 'en'
                    ? 'An error occurred while processing request.'
                    : 'حدث خطأ أثناء تنفيذ الطلب.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
    public function assignPermissionsToEmployee($request, $module, $lang = 'ar')
    {
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where('flag', $module)
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
            'role_id' => 'nullable|integer|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return respondError(__('Validation.error'), 400, $validator->errors());
        }

        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($request->employee_id);
            $requestedPermissions = $request->permissions ?? [];
            $roleId = $request->role_id ?? null;

            $currentRoles = $employee->getRoleNames()->toArray();
            $rolePermissions = [];

            // === CASE 1: Assign a new role ===
            if ($roleId) {
                $role = Role::with('permissions')->findOrFail($roleId);
                $rolePermissions = $role->permissions->pluck('id')->toArray();

                // Remove previous roles that are different
                foreach ($currentRoles as $roleName) {
                    if ($roleName !== $role->name) {
                        $employee->removeRole($roleName);
                    }
                }

                // Assign role if missing
                if (!$employee->hasRole($role->name)) {
                    $employee->assignRole($role->name);
                }

                // Direct permissions exclude role permissions
                $requestedPermissions = array_diff($requestedPermissions, $rolePermissions);
            }

            // === CASE 2: No role_id provided ===
            else {
                if (!empty($currentRoles)) {
                    $rolePermissions = Permission::whereHas('roles', function ($q) use ($currentRoles) {
                        $q->whereIn('name', $currentRoles);
                    })->pluck('id')->toArray();

                    $missing = array_diff($rolePermissions, $requestedPermissions);

                    if (empty($missing)) {
                        $requestedPermissions = array_diff($requestedPermissions, $rolePermissions);
                    } else {
                        foreach ($currentRoles as $roleName) {
                            $employee->removeRole($roleName);
                        }
                        $requestedPermissions = array_unique($requestedPermissions);
                    }
                }
            }

            // Sync direct permissions
            $directPermissionNames = Permission::whereIn('id', $requestedPermissions)->pluck('name')->toArray();
            $employee->syncPermissions($directPermissionNames, false);

            DB::commit();

            return ResponseWithSuccessData($lang, [
                'employee_id' => $employee->id,
                'assigned_permissions' => $employee->getAllPermissions()->pluck('name'),
                'assigned_roles' => $employee->getRoleNames(),
            ], 1);
        } catch (\Exception $e) {
            DB::rollBack();
            return respondError($e->getMessage(), 500);
        }
    }
}
