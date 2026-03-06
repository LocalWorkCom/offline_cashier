<?php

namespace App\Services\SettingsServices;

use App\Models\RolePermissionLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class RoleService
{

    public function getRoles($guard = null)
    {
        $query = Role::with('permissions');
        $guard = getAuthenticatedGuard();
        $user = auth($guard)->user();

        if ($guard === 'employee') {
            if ($user && $user->hasRole('Branch_Manager')) {
                // Return permissions for employee guard with Branch_Manager role
                $query->where('name', 'Branch_Manager');
            } elseif ($user && $user->hasRole('superAdmin')) {
                // Return permissions for admin guard with Branch Manager role
                $query->where('guard_name', 'employee');
            }
        } elseif ($guard === 'admin') {
            if ($user && $user->hasRole('Branch Manager')) {
                // Return permissions for admin guard with Branch Manager role
                $query->where('guard_name', 'admin');
                if ($guard) {
                    $query->where('guard_name', $guard);
                }
            }
        }


        return $query;
    }

    public function getRoleById($id)
    {
        $guard = getAuthenticatedGuard();

        $query = Role::query();
        if ($guard == 'employee') {
            $query->where('guard_name', $guard);
        }
        return $query->findOrFail($id);
    }

    public function getGroupedPermissions($guard = 'admin')
    {
        $permissions = Permission::where('guard_name', $guard)->where('is_active', 0)->get();
        $groupedPermissions = [];

        foreach ($permissions as $permission) {
            $parts = explode(' ', $permission->name);
            $group = $parts[1] ?? 'Others';
            $groupedPermissions[$group][] = $permission;
        }

        return $groupedPermissions;
    }

    public function createRole(Request $request, $guard = 'admin')
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
            'permissions_ids' => 'required|array',
            'permissions_ids.*' => 'string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => ['error' => $validator->errors()],
            ];
        }

        $validated = $validator->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $guard,
        ]);

        logPermissionsAndRoleChanges('create_role', [
            'role_id' => $role->id,
            'extra_data' => ['name' => $role->name],
        ]);

        $permissions = Permission::whereIn('name', $validated['permissions_ids'])->where('guard_name', $guard)->get();
        $role->syncPermissions($permissions);

        return ['role' => $role];
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if (!$role) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => ['error' => __('roles.role_not_found')],
            ];
        }

        $validator = Validator::make($request->all(), [
            // 'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($id)],
            'permissions_ids' => 'required|array',
            'permissions_ids.*' => 'string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => ['error' => $validator->errors()],
            ];
        }

        $validated = $validator->validated();

        // $role->update(['name' => $validated['name']]);

        $permissions = Permission::whereIn('name', $validated['permissions_ids'])
            ->where('guard_name', $role->guard_name)->get();

        $role->syncPermissions($permissions);
        logPermissionsAndRoleChanges('update_role_permissions', [
            'role_id'        => $role->id,
            'permission_ids' => $permissions->pluck('id')->toArray(),
            'extra_data'     => [
                'role_name'  => $role->name,
                'updated_by' => auth('employee')->user()->id ?? auth('admin')->user()->id,
            ],
        ]);
        return ['role' => $role];
    }

    public function deleteRole($id)
    {
        $role = Role::findOrFail($id);
        if (!$role) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => ['error' => __('roles.role_not_found')],
            ];
        }

        if ($role->users()->count() > 0) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => ['error' => __('roles.role_assigned_to_users')],
            ];
        }

        $role->delete();
        logPermissionsAndRoleChanges('delete_role', [
            'role_id'        => $role->id,
            'extra_data'     => [
                'role_name'  => $role->name,
                'deleted_by' => auth('employee')->user()->name ?? auth('admin')->user()->name,
            ],
        ]);
        return ['success' => 'Role deleted successfully.'];
    }
}
