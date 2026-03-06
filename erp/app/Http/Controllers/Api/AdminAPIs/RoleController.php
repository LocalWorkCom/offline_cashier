<?php

namespace App\Http\Controllers\Api\AdminAPIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Models\Employee;
use App\Models\RolePermissionLog;
use App\Services\SettingsServices\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    protected $roleService;
    private $lang;

    public function __construct(RoleService $roleService, Request $request)
    {
        $this->roleService = $roleService;
        $this->lang = $request->header('lang', 'ar');
    }

    public function index(Request $request)
    {
        $query = $this->roleService->getRoles($request->gaurd);
        $fields = [];
        $visible = [];
        $response = paginateOrGetAll($query, $request, $fields, $visible);
        return ResponseWithSuccessDataPaginated($this->lang, $response, 1);
    }

    public function show($id)
    {
        try {
            $role = $this->roleService->getRoleById($id);




            return ResponseWithSuccessData(
                $this->lang,
                new PermissionResource($role),
                1
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $msg = $this->lang === 'ar' ? 'الدور غير موجود' : 'role not found';
            return respondError($msg, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching role: ' . $e->getMessage());
            return RespondWithBadRequestData($this->lang, 2);
        }
    }



    public function permissions(Request $request)
    {
        $permissions = $this->roleService->getGroupedPermissions($request->guard ?? 'admin');
        return ResponseWithSuccessData($this->lang,   $permissions, 1);
    }

    public function store(Request $request)
    {
        $result = $this->roleService->createRole($request, 'employee');

        if (isset($result['errorData'])) {
            return response()->json([
                'code' => 404,
                'status' => false,
                'message' => 'Permission already exists.',
                'errorData' => $result['errorData']['error']
            ], 200);
        }
        return ResponseWithSuccessData($this->lang, $result['role'], 1);
    }

    public function update(Request $request, $id)
    {
        try {
            $result = $this->roleService->updateRole($request, $id);

            if (isset($result['status']) && $result['status'] === false) {
                return response()->json([
                    'code' => $result['code'],
                    'status' => false,
                    'message' => $result['message'],
                    'errorData' => $result['errorData']['error']
                ], 200);
            }

            return ResponseWithSuccessData($this->lang, $result['role'], 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'status' => false,
                'message' => __('roles.role_not_found'),
                'errorData' => ['error' => __('roles.role_not_found')]
            ], 200);
        }
    }

    public function destroy($id)
    {
        try {
            $result = $this->roleService->deleteRole($id);

            if (isset($result['status']) && $result['status'] === false) {
                return response()->json([
                    'code' => $result['code'],
                    'status' => false,
                    'message' => $result['message'],
                    'errorData' => $result['errorData']['error']
                ], 200);
            }

        
            return RespondWithSuccessRequest($this->lang, 23);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'status' => false,
                'message' => __('roles.role_not_found'),
                'errorData' => ['error' => __('roles.role_not_found')]
            ], 200);
        }
    }
    public function assignRole(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'role' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = Role::where('guard_name', 'employee')
                        ->where(function ($q) use ($value) {
                            $q->where('id', $value)
                                ->orWhere('name', $value);
                        })
                        ->exists();

                    if (! $exists) {
                        $fail('The selected role is invalid for the employee guard.');
                    }
                },
            ],
            'employee_id' => [
                'required',
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    if ($value == auth('employee')->user()->id) {
                        $fail('You cannot assign the role to yourself.');
                    }
                },
            ]
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

        $employee = Employee::findOrFail($request->employee_id);
        $employee->syncRoles([$request->role]);
        logPermissionsAndRoleChanges('assign_role', [
            'employee_id' => $employee->id,
            'role_id' => optional(Role::findByName($request->role))->id,
            'extra_data' => ['role_name' => $request->role],
        ]);

        return ResponseWithSuccessData($this->lang, $employee->load('roles', 'permissions'), 1);
    }
}
