<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\SettingsServices\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
      protected $service;

    public function __construct(PermissionService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $permissions = $this->service->getAllPermissions()->get();
        return view('dashboard.permissions.list', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
            'guard_name' => 'required|string',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        $result = $this->service->createPermission($request->all());

        if (isset($result['error']) && $result['error'] === 'exists') {
            return redirect()->back()->with('error', __('roles.permission_already_exists', ['name' => $request->name_en]))->withInput();
        }

        return redirect()->back()->with('success', __('roles.created_successfully'));
    }

    public function edit($id)
    {
        $permission = $this->service->find($id);
        if (!$permission) {
            return response()->json(['error' => 'Permission not found'], 404);
        }

        $nameAr = __('permissions.' . $permission->name);
        return response()->json([
            'permission' => $permission,
            'name_en' => $permission->name,
            'id' => $id,
            'name_ar' => $nameAr,
            'isActive' => $permission->is_active,
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        $this->service->updatePermission($request->id, $request->all());

        return redirect()->route('permissions.list')->with('success', __('roles.updated_successfully'));
    }

    public function destroy($id)
    {
        $result = $this->service->deletePermission($id);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json(['success' => $result['success']]);
    }
}
