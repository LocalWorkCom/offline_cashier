<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\SettingsServices\RoleService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{ protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        $roles = $this->roleService->getRoles()->get();
        return view('dashboard.roles.list', compact('roles'));
    }

    public function show($id)
    {
        $role = $this->roleService->getRoleById($id);
        $groupedPermissions = $role->permissions->groupBy('group_name');

        return view('dashboard.roles.show', compact('role', 'groupedPermissions'));
    }

    public function create()
    {
        $groupedPermissions = $this->roleService->getGroupedPermissions();
        return view('dashboard.roles.add', compact('groupedPermissions'));
    }

    public function store(Request $request)
    {
        $result = $this->roleService->createRole($request);
        if (isset($result['error'])) {
            return redirect()->back()->withErrors($result['error'])->withInput();
        }
        return redirect()->route('roles.list');
    }

    public function edit($id)
    {
        $role = $this->roleService->getRoleById($id);
        $permissions = Permission::where('guard_name', $role->guard_name)
            // ->where('is_active', 0)
            ->get();
        // Apply role-specific exclusions
        if (in_array($role->name, ['Kitchen Manager', 'Branch Manager'])) {
            $permissions = $permissions->reject(function ($permission) {
                return in_array($permission->name, [
                    'view einvoices',
                    'view einvoice_settings',
                    'create einvoice_settings',
                    'update einvoice_settings',
                    'view officer_assign_setting',
                    'create officer_assign_setting',
                    'update officer_assign_setting',
                    'view einvoices_superadmin'
                ]);
            });
        }

        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $parts = explode(' ', $permission->name);
            $group = $parts[1] ?? 'Others';
            $groupedPermissions[$group][] = $permission;
        }

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('dashboard.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

   public function update(Request $request, $id)
{
    $result = $this->roleService->updateRole($request, $id);

    if (isset($result['errorData']['error'])) {
        return redirect()->back()->withErrors($result['errorData']['error'])->withInput();
    }

    return redirect()->route('roles.list')->with('success', 'Role updated successfully.');
}


    public function destroy($id)
    {
        $result = $this->roleService->deleteRole($id);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->route('roles.list')->with('success', $result['success']);
    }
}
