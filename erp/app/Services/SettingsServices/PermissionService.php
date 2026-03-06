<?php

namespace App\Services\SettingsServices;

use App\Models\Employee;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\File;

class PermissionService
{
    public function getAllPermissions()
    {
        $guard = getAuthenticatedGuard();
        $user = auth($guard)->user();

        $query = Permission::query()->with('modules');

        // ✅ If user is superAdmin → return GLOBAL + ACTIVE module permissions
        if ($user->hasRole('superAdmin')) {
            return $query->where('guard_name', $guard)
                ->where(function ($q) {
                    $q->where('is_global', 1)
                        ->orWhereHas('modules', function ($sub) {
                            $sub->where('system_modules.is_active', 1);
                        });
                });
        }

        // ✅ Collect module IDs directly from roles (since roles.module_id exists)
        $roles = $user->roles()->where('guard_name', $guard)->get();
        $moduleIds = $roles->pluck('module_id')->filter()->unique();

        // ✅ If user has a GLOBAL role → return all global permissions + module permissions
        // if ($roles->where('is_global', 1)->count() > 0) {
        //     return $query->where('guard_name', $guard)
        //         ->where(function ($q) use ($moduleIds) {
        //             $q->where('is_global', 1)
        //                 ->orWhereHas('modules', function ($sub) use ($moduleIds) {
        //                     $sub->whereIn('system_modules.id', $moduleIds)
        //                         ->where('system_modules.is_active', 1);
        //                 });
        //         });
        // }

        // ✅ Otherwise → only module permissions
        return $query
            ->where('guard_name', $guard)
            ->whereHas('modules', function ($q) use ($moduleIds) {
                $q->whereIn('system_modules.id', $moduleIds)
                    ->where('system_modules.is_active', 1);
            });
    }


    public function getAllPermissionsOfGuard($guard = 'admin')
    {

        return Permission::query()
            ->with(['modules' => function ($q) {
                $q->where('is_active', 1); // only active modules
            }])
            ->where('guard_name', $guard)
            ->where(function ($q) {
                $q->where('is_global', 1)
                    ->orWhereHas('modules', function ($sub) {
                        $sub->where('system_modules.is_active', 1);
                    });
            });
    }
public function getAllPermissionsOfModule($moduleId)
{
    return Permission::query()
        ->with('modules:id,name,is_active')
        ->where(function ($query) use ($moduleId) {
            $query->whereHas('modules', function ($q) use ($moduleId) {
                $q->where('system_modules.id', $moduleId)
                  ->where('system_modules.is_active', 1);
            })
            ->orWhere('is_global', 1);
        })
        ->where('guard_name', 'employee')
        ->where('is_active', 1);
}




    public function find($id)
    {
        return DB::table('permissions')->find($id);
    }

    public function createPermission(array $data)
    {
        // check if permission already exists (name + guard)
        $exists = Permission::where('name', $data['name_en'])
            ->where('guard_name', $data['guard_name'] ?? 'admin')
            ->exists();

        if ($exists) {
            return [

                'code'      => 404,
                'status'    => false,
                'message'   => 'Validation Error.',
                'data'      => null,
                'errorData' => ['error' => 'exists'],
            ];
        }

        // create permission
        $permission = Permission::create([
            'name' => $data['name_en'],
            'name_en' => $data['name_en'],
            'name_ar' => $data['name_ar'],
            'is_active'  => $data['is_active'],
            'is_global'  => $data['is_global'] ?? 0,
            'guard_name' => $data['guard_name'] ?? 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // attach modules only if NOT global
        if (($data['is_global'] ?? 0) == 0 && !empty($data['module_ids'])) {
            $permission->modules()->sync($data['module_ids']);
        }

        // logging
        logPermissionsAndRoleChanges('create_permission', [
            'employee_id'     => auth('employee')->id() ?? auth('admin')->id(),
            'permission_ids'  => [$permission->id],
            'extra_data'      => [
                'permission_name'  => $permission->name,
                'permission_guard' => $permission->guard_name,
            ],
        ]);

        // update translations
        $this->updateLangFiles($data['name_en'], $data['name_en'], $data['name_ar']);

        return $permission;
    }


    public function updatePermission($id, array $data)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return [
                'code'      => 404,
                'status'    => false,
                'message'   => __('roles.validation_error'),
                'data'      => null,
                'errorData' => ['error' => __('roles.permission_not_found')],
            ];
        }

        // check uniqueness of name within same guard
        $exists = Permission::where('id', '!=', $id)
            // ->where('name', $data['name_en'])
            ->where('guard_name', $data['guard_name'] ?? 'admin')
            ->exists();

        if ($exists) {
            return [

                'code'      => 404,
                'status'    => false,
                'message'   => __('roles.validation_error'),
                'data'      => null,

                'errorData' => ['error' => __('roles.data_exists')],
            ];
        }

        // update main fields
        $permission->update([
            'name_ar'       => $data['name_ar'],
            'name_en'       => $data['name_en'],
            'is_active'  => $data['is_active'] ?? $data['is_active_edit'] ?? 1,
            'is_global'  => $data['is_global'] ?? $permission->is_global,
            'guard_name' => $data['guard_name'] ?? 'admin',
            'updated_at' => now(),
        ]);


        // handle modules
        if (($data['is_global'] ?? $permission->is_global) == 0) {
            if (!empty($data['module_ids'])) {
                $permission->modules()->sync($data['module_ids']);
            } else {
                return [
                    'code'      => 404,
                    'status'    => false,
                    'message'   => __('roles.validation_error'),
                    'data'      => null,
                    'errorData' => ['error' => 'Non-global permission must have modules assigned.'],
                ];
            }
        } else {
            // global permission → clear modules
            $permission->modules()->detach();
        }

        // logging
        logPermissionsAndRoleChanges('update_permission', [
            'employee_id'    => auth('employee')->id() ?? auth('admin')->id(),
            'permission_ids' => [$permission->id],
            'extra_data'     => [
                'permission_name'  => $permission->name,
                'permission_guard' => $permission->guard_name,
            ],
        ]);

        // handle modules
        if (($data['is_global'] ?? $permission->is_global) == 0) {
            if (!empty($data['module_ids'])) {
                $permission->modules()->sync($data['module_ids']);
            } else {
                return [
                    'code'      => 404,
                    'status'    => false,
                    'message'   => __('roles.validation_error'),
                    'data'      => null,
                    'errorData' => ['error' => 'Non-global permission must have modules assigned.'],
                ];
            }
        } else {
            // global permission → clear modules
            $permission->modules()->detach();
        }

        // logging
        logPermissionsAndRoleChanges('update_permission', [
            'employee_id'    => auth('employee')->id() ?? auth('admin')->id(),
            'permission_ids' => [$permission->id],
            'extra_data'     => [
                'permission_name'  => $permission->name,
                'permission_guard' => $permission->guard_name,
            ],
        ]);

        // update translations


        $this->updateLangFiles($data['name_en'], $data['name_en'], $data['name_ar']);

        return [
            'status'  => true,
            'message' => 'Permission updated successfully.',
        ];
    }


    public function deletePermission($id)
    {
        $permission = Permission::find($id);
        if (!$permission) {
            return [
                'code' => 404,
                'status' => false,
                'message' => __('roles.validation_error'),
                'data' => null,
                'errorData' => ['error' => __('roles.permission_not_found')],
            ];
        }

        $roleCount = DB::table('role_has_permissions')
            ->where('permission_id', $id)
            ->count();

        if ($roleCount > 0) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => ['error' => __('roles.delete_error')],
            ];
        }
        logPermissionsAndRoleChanges('delete_permission', [
            'employee_id' => auth('employee')->id() ?? auth('admin')->id(),
            'permission_ids' => [$id],
            'extra_data' => ['permission_name' => $permission->name, 'permission_guard' => $permission->guard_name],
        ]);
        $this->removeLangKeys($permission->name);
        $permission->delete();

        return ['success' => __('roles.delete_success')];
    }

    private function updateLangFiles($key, $enValue, $arValue)
    {
        $this->updateLangFile(resource_path('lang/en/permissions.php'), $key, $enValue);
        $this->updateLangFile(resource_path('lang/ar/permissions.php'), $key, $arValue);
    }

    private function updateLangFile($filePath, $key, $value)
    {
        if (!File::exists($filePath)) {
            File::put($filePath, "<?php\n\nreturn [\n];");
        }

        $translations = include $filePath;
        $translations[$key] = $value;

        $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
        File::put($filePath, $content);
    }

    private function removeLangKeys($key)
    {
        foreach (['en', 'ar'] as $lang) {
            $filePath = resource_path("lang/{$lang}/permissions.php");
            if (file_exists($filePath)) {
                $translations = include $filePath;
                unset($translations[$key]);
                File::put($filePath, "<?php return " . var_export($translations, true) . ";");
            }
        }
    }

    public function assignEmployeePermission(array $data)
    {
        $employee = Employee::with('roles', 'permissions')->findOrFail($data['employee_id']);

        // Get valid permissions (avoid invalid IDs)
        $permissions = Permission::whereIn('name', $data['permissions'])->orWhereIn('id', $data['permissions'])->where('guard_name', 'employee')->pluck('name')->toArray();

        // Attach new permissions without removing old ones
        foreach ($permissions as $permission) {
            if (!$employee->hasPermissionTo($permission)) {
                $employee->givePermissionTo($permission);
            }
        }
        $allPermissions = $employee->getAllPermissions();

        // Group by second segment of permission name (split by ".")
        $grouped = $allPermissions->groupBy(function ($perm) {
            $parts = explode('.', $perm->name);
            return $parts[1] ?? $parts[0]; // if only one part exists
        })->map(function ($perms) {
            return $perms->map(fn($p) => [
                'id'   => $p->id,
                'name' => $p->name,
            ])->values();
        });

        logPermissionsAndRoleChanges('assign_permissions_to_employee', [
            'employee_id'    => $employee->id,

            'permission_ids' => $permissions,
            'extra_data'     => [
                'employee_name' => $employee->name,
                'assigned_by'   => auth('employee')->user()->first_name ?? auth('admin')->user()->first_name,
            'permission_ids' => $permissions,
            'extra_data'     => [
                'employee_name' => $employee->name,
                'assigned_by'   => auth('employee')->user()->first_name ?? auth('admin')->user()->first_name,

            ],
        ],
        ]);

        return [
            'employee' => [
                'id'         => $employee->id,
                'name'       => $employee->first_name . ' ' . $employee->last_name,
                'email'      => $employee->email,
            ],
            'roles' => $employee->roles->pluck('name'),
            'permissions' => $grouped,
        ];
    }
}
