<?php

namespace App\Http\Controllers\Api\AdminAPIs;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SystemModule;
use App\Services\SettingsServices\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    protected $permissionService;
    private $lang;

    public function __construct(PermissionService $permissionService, Request $request)
    {
        $this->permissionService = $permissionService;
        $this->lang = $request->header('lang', 'ar');
    }

    public function assignPermissionsToModules(Request $request)
    {
        $data = $request->validate([
            'from_id'   => 'required|integer|exists:permissions,id',
            'to_id'     => 'required|integer|exists:permissions,id',
            'module_ids'       => 'nullable|array',
            'module_ids.*'     => 'exists:system_modules,id',
        ]);

        // ✅ Get permissions in range
        $permissions = Permission::whereBetween('id', [
            min($data['from_id'], $data['to_id']),
            max($data['from_id'], $data['to_id'])
        ])->get();

        if ($permissions->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No permissions found in this range.',
            ], 404);
        }

        if (! empty($data['module_ids'])) {
            // ✅ Case 1: Assign to modules
            $activeModules = SystemModule::whereIn('id', $data['module_ids'])
                ->where('is_active', 1)
                ->pluck('id')
                ->toArray();

            if (count($activeModules) !== count($data['module_ids'])) {
                return response()->json([
                    'status'  => false,
                    'message' => 'One or more modules are inactive or not found.',
                ], 422);
            }

            foreach ($permissions as $permission) {
                $permission->update(['is_global' => 0]);
                $permission->modules()->syncWithoutDetaching($activeModules);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Permissions (range) assigned to modules successfully.',
            ]);
        }

        // ✅ Case 2: No module_ids → make global
        foreach ($permissions as $permission) {
            $permission->update(['is_global' => 1]);
            $permission->modules()->detach();
        }

        return response()->json([
            'status'  => true,
            'message' => 'Permissions (range) updated as global successfully.',
        ]);
    }

    // public function assignPermissionsToModules(Request $request)
    // {
    //     $data = $request->validate([
    //         'permission_ids'   => 'required|array|min:1',
    //         'permission_ids.*' => 'exists:permissions,id',
    //         'module_ids'       => 'nullable|array',
    //         'module_ids.*'     => 'exists:system_modules,id',
    //     ]);

    //     $permissions = Permission::whereIn('id', $data['permission_ids'])->get();

    //     if (! empty($data['module_ids'])) {
    //         // ✅ Case 1: Assign to modules
    //         $activeModules = SystemModule::whereIn('id', $data['module_ids'])
    //             ->where('is_active', 1)
    //             ->pluck('id')
    //             ->toArray();

    //         if (count($activeModules) !== count($data['module_ids'])) {
    //             return response()->json([
    //                 'status'  => false,
    //                 'message' => 'One or more modules are inactive or not found.',
    //             ], 422);
    //         }

    //         foreach ($permissions as $permission) {
    //             $permission->update(['is_global' => 0]); // not global
    //             $permission->modules()->syncWithoutDetaching($activeModules);
    //         }

    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Permissions assigned to modules successfully.',
    //         ]);
    //     }

    //     // ✅ Case 2: No module_ids → make global
    //     foreach ($permissions as $permission) {
    //         $permission->update(['is_global' => 1]);
    //         $permission->modules()->detach(); // remove module assignments
    //     }

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Permissions updated as global successfully.',
    //     ]);
    // }

    public function index(Request $request)
    {
        $query = $this->permissionService->getAllPermissions();
        $fields = [];
        $visible = [];
        $response = paginateOrGetAll($query, $request, $fields, $visible);
        return ResponseWithSuccessDataPaginated($this->lang, $response, 1);
    }

    public function listOfPermissions(Request $request, $guard)
    {
        $query = $this->permissionService->getAllPermissionsOfGuard($guard);
        $fields = [];
        $visible = [];
        $response = paginateOrGetAll($query, $request, $fields, $visible);
        $grouped = [
            'global' => $response['data']->where('is_global', 1)->values(),
            'modules' => [],
        ];
        foreach ($response['data']->where('is_global', 0) as $permission) {
            $permission->modules->each(function ($module) {
                unset($module->pivot); // remove pivot only in response
            });
            foreach ($permission->modules as $module) {

                if ($module->is_active) {
                    $grouped['modules'][$module->name]['module'] = $module;
                    $grouped['modules'][$module->name]['permissions'][] = $permission;
                }
            }
        }
        return ResponseWithSuccessDataPaginated($this->lang, ['data' => $grouped, 'meta' => $response['meta']], 1);
    }
    // public function listOfPermissionsModule(Request $request, $moduleId)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);

    //     // Get all module-specific permissions
    //     // Get all module-specific permissions with group loaded
    //     $modulePermissions = Permission::query()
    //         ->where('guard_name', 'employee')
    //         ->where('is_active', 1)
    //         ->whereHas('modules', function ($q) use ($moduleId) {
    //             $q->where('system_modules.id', $moduleId)
    //                 ->where('system_modules.is_active', 1);
    //         })
    //         ->with('group') // load the group relation
    //         ->get();

    //     // Separate permissions assigned to a group and not assigned
    //     $permissionsNotAssigned = $modulePermissions->filter(fn($permission) => $permission->group_id === null);
    //     $permissionsAssigned = $modulePermissions->filter(fn($permission) => $permission->group_id !== null);

    //     // Group unassigned permissions by their second word
    //     $formatted = $permissionsNotAssigned->groupBy(function ($permission) {
    //         $parts = explode(' ', $permission->name);
    //         return $parts[1] ?? 'others';
    //     })->map(function ($permissions, $key) {
    //         return [
    //             'key' => $key,
    //             'permissions' => $permissions->values()
    //         ];
    //     })->values();

    //     // Group assigned permissions by their group
    //     $groupedPermissions = $permissionsAssigned->groupBy('group_id')->map(function ($permissions, $groupId) use($lang) {
    //         return [
    //             'group_id' => $groupId,
    //             'group_name' =>$lang == 'en' ? $permissions->first()->group->name_en :  $permissions->first()->group->name_ar, // get group name
    //             'permissions' => $permissions->map(function ($perm) {
    //                 return [
    //                     'id' => $perm->id,
    //                     'name' => $perm->name,
    //                     'name_ar' => $perm->name_ar ?? null,
    //                     'name_en' => $perm->name_en ?? null,
    //                 ];
    //             })->values()
    //         ];
    //     })->values();


    //     $responseData = [
    //         'permissions' => $formatted,
    //         'groupedPermissions' => $groupedPermissions,
    //     ];

    //     return ResponseWithSuccessData($lang, $responseData, 1);
    // }

    public function listOfPermissionsModule(Request $request, $moduleId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        // Get global permissions (not grouped)
        $globalPermissions = Permission::query()
            ->where('guard_name', 'employee')
            ->where('is_global', 1)
            ->where('is_active', 1)
            ->get();

        // Get module-specific permissions
        $modulePermissions = Permission::query()
            ->where('guard_name', 'employee')
            ->where('is_active', 1)
            ->whereHas('modules', function ($q) use ($moduleId) {
                $q->where('system_modules.id', $moduleId)
                    ->where('system_modules.is_active', 1);
            })
            ->get();

        // Group permissions by second word of English name (fallback to others if not exists)
        $grouped = collect($modulePermissions)->groupBy(function ($permission) {
            $parts = preg_split('/\s+/', trim($permission->name_en), -1, PREG_SPLIT_NO_EMPTY);
            return $parts[1] ?? 'others';
        });

        // Format grouped permissions with proper translation
        $formatted = $grouped->map(function ($permissions) use ($lang) {
            $firstPermission = $permissions->first();
       
            // Use proper language with fallback
            $fullName = $lang === 'ar'
                ? ($firstPermission->name_ar ?: $firstPermission->name_ar)
                : ($firstPermission->name_en ?: $firstPermission->name_en);

            $parts = preg_split('/\s+/', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);
            array_shift($parts); // Remove first word
            $translatedKey = implode(' ', $parts) ?: 'others';
      
            return [
                'key' => $translatedKey,
                'permissions' => $permissions->map(function ($permission) use ($lang) {
                    $nameAr = $permission->name_ar ?: $permission->name_ar;
                    $nameEn = $permission->name_en ?: $permission->name_en;

                    return [
                        'id' => $permission->id,
                        'name' => $lang === 'ar' ? $permission->name_ar : $permission->name_en,
                        'name_ar' => $permission->name_ar,
                        'name_en' => $permission->name_en,
                        'guard_name' => $permission->guard_name,
                        'is_global' => $permission->is_global,
                        'created_at' => $permission->created_at,
                        'updated_at' => $permission->updated_at,
                        'is_active' => $permission->is_active
                    ];
                })->values()
            ];
        })->values();

        // Roles and their permissions
        $roles = Role::query()
            ->where('guard_name', 'employee')
            ->where('module_id', $moduleId)
            ->whereHas('module', function ($q) {
                $q->where('is_active', 1);
            })
            ->whereHas('permissions', function ($q) {
                $q->where('is_active', 1);
            })
            ->with(['permissions' => function ($q) {
                $q->where('is_active', 1);
            }])
            ->get();
        $groupedPermissions = $roles->map(function ($role) use ($lang) {
            $roleName = $lang === 'ar'
                ? ($role->name_ar ?: $role->name_ar)
                : ($role->name_en ?: $role->name_en);

            return [
                'role_id' => $role->id,
                'role_name' => $roleName,
                'permissions' => $role->permissions->map(function ($perm) use ($lang) {
                    $nameAr = $perm->name_ar ?: $perm->name_ar;
                    $nameEn = $perm->name_en ?: $perm->name_en;

                    return [
                        'id' => $perm->id,
                        'name' => $lang === 'ar' ? $nameAr : $nameEn,
                        'display_name' => $perm->display_name ? trim($perm->display_name) : null,
                        'name_ar' => $nameAr,
                        'name_en' => $nameEn,
                    ];
                })->values()
            ];
        });

        $responseData = [
            'permissions' => $formatted,
            'groupedPermissions' => $groupedPermissions,
        ];

        return ResponseWithSuccessData($lang, $responseData, 1);
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'name_en' => [
                    'required',
                    'string',
                    Rule::unique('permissions', 'name')->where(function ($query) use ($request) {
                        return $query->where('guard_name', $request->guard_name ?? 'admin');
                    }),
                ],
                'name_ar'    => 'required|string',
                'guard_name' => 'nullable|string',
                'module_ids' => 'nullable|array',
                'module_ids.*' => 'exists:system_modules,id,is_active,1', // only active modules
                'is_global' => [
                    'required',
                    Rule::in([0, 1]),
                    function ($attribute, $value, $fail) use ($request) {
                        // if modules are provided, must NOT be global
                        if (!empty($request->module_ids) && $value == 1) {
                            $fail(__('A global permission cannot be assigned to specific modules.'));
                        }
                        // if no modules provided, must be global or else invalid
                        if (empty($request->module_ids) && $value == 0) {
                            $fail(__('A non-global permission must have at least one module.'));
                        }
                    },
                ],
                'is_active'  => 'required|in:0,1',
            ]);
        } catch (ValidationException $e) {
            return respondError(($this->lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 404, $e->errors());
        }

        $result = $this->permissionService->createPermission($request->all());

        if (isset($result['errorData']['error'])) {
            return respondError($result['errorData'], 404);
        }

        return ResponseWithSuccessData($this->lang, $result, 1);
    }


    public function show($id)
    {
        $permission = $this->permissionService->find($id);

        if (!$permission) {
            return respondError(($this->lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404);
        }
        return ResponseWithSuccessData($this->lang, $permission, 1);
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name_en' => [
                    'required',
                    'string',
                    Rule::unique('permissions', 'name')
                        ->ignore($id) // allow current permission name
                        ->where(function ($query) use ($request) {
                            return $query->where('guard_name', $request->guard_name ?? 'admin');
                        }),
                ],
                'name_ar'     => 'required|string',
                'guard_name'  => 'nullable|string',
                'module_ids'  => 'nullable|array',
                'module_ids.*' => 'exists:system_modules,id,is_active,1',
                'is_global'   => [
                    'required',
                    Rule::in([0, 1]),
                    function ($attribute, $value, $fail) use ($request) {
                        // global vs non-global validation
                        if (!empty($request->module_ids) && $value == 1) {
                            $fail(__('A global permission cannot be assigned to specific modules.'));
                        }
                        if (empty($request->module_ids) && $value == 0) {
                            $fail(__('A non-global permission must have at least one module.'));
                        }
                    },
                ],
                'is_active'   => 'required|in:0,1',
            ]);
        } catch (ValidationException $e) {
            return respondError(
                ($this->lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'),
                404,
                $e->errors()
            );
        }

        // Pass validated data to service
        $result = $this->permissionService->updatePermission($id, $request->all());

        if (isset($result['status']) && $result['status'] === false) {
            return respondError($result['message'], 404);
        }

        return RespondWithSuccessRequest($this->lang, 1);
    }



    public function destroy($id)
    {
        try {
            $result = $this->permissionService->deletePermission($id);

            if (isset($result['status']) && $result['status'] === false) {
                return respondError(($result['message']), $result['code'], $result['errorData']['error']);
            }

            return RespondWithSuccessRequest($this->lang, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return respondError(($this->lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 404, $this->lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    // public function assignEmployeePermission(Request $request)
    // {
    //     try {
    //         $lang = $request->header('lang', 'ar');
    //         $validator =  Validator::make($request->all(), [
    //             'employee_id' => [
    //                 'required',
    //                 Rule::exists('employees', 'id')
    //                     ->where('status', 'active')
    //                     ->whereNull('deleted_at'),
    //             ],
    //             'permissions' => 'required|array',
    //             'permissions.*' => [
    //                 function ($attribute, $value, $fail) {
    //                     $exists = Permission::where('guard_name', 'employee')
    //                         ->where(function ($q) use ($value) {
    //                             $q->where('id', $value)
    //                                 ->orWhere('name', $value);
    //                         })
    //                         ->exists();

    //                     if (!$exists) {
    //                         $fail("The selected {$attribute} is invalid.");
    //                     }
    //                 },
    //             ],
    //         ]);
    //         if ($validator->fails()) {
    //             return respondError(
    //                 $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
    //                 400,
    //                 $validator->errors()
    //             );
    //         }
    //         $validatedData = $validator->validated();

    //         $newAssign = $this->permissionService->assignEmployeePermission($validatedData);
    //         return ResponseWithSuccessData($lang, $newAssign, 1);
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         $message = $lang === 'ar' ? 'الصلاحيه غير موجودة' : 'Permission not found';
    //         return respondError($message, 404);
    //     } catch (\Exception $e) {
    //         Log::error('Error fetching permission: ' . $e->getMessage());
    //         return RespondWithBadRequestData($lang, 2);
    //     }
    // }

    public function assignEmployeePermission(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $authGuard = getAuthenticatedGuard();
        $authUser = auth($authGuard)->user();

        $validator = Validator::make($request->all(), [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')
                    ->where('employee_status_id', 1)
                    ->whereNull('deleted_at'),
            ],
            'module_id' => 'nullable|exists:system_modules,id',
            'permissions' => 'nullable|array',
            'permissions.*' => [
                function ($attribute, $value, $fail) use ($authUser) {
                    $permission = Permission::with('modules')
                        ->where('guard_name', 'employee')
                        ->where(function ($q) use ($value) {
                            $q->where('id', $value)
                                ->orWhere('name', $value);
                        })
                        ->first();

                    if (!$permission) {
                        return $fail("The selected {$attribute} is invalid.");
                    }

                    //case 1: superAdmin → full access
                    if ($authUser->hasRole('superAdmin')) {
                        return;
                    }

                    //case 2: HR_Manager → only HR module
                    if ($authUser->hasRole('HR_Manager')) {
                        $hrModule = SystemModule::where('name', 'HR')
                            ->where('is_active', 1)
                            ->first();

                        if (!$hrModule) {
                            return $fail("HR module is not active.");
                        }

                        // Allow only permissions linked to HR module
                        $permissionModuleIds = $permission->modules()->pluck('id')->toArray();
                        if (!in_array($hrModule->id, $permissionModuleIds)) {
                            return $fail("You can only assign HR module permissions.");
                        }

                        return;
                    }

                    //case 3:other roles
                    $userModuleIds = $authUser->roles()
                        ->whereHas('module', function ($q) {
                            $q->where('is_active', 1);
                        })
                        ->pluck('module_id')
                        ->unique()
                        ->toArray();

                    $permissionModuleIds = $permission->modules()
                        ->where('is_active', 1)
                        ->pluck('id')
                        ->toArray();

                    $common = array_intersect($userModuleIds, $permissionModuleIds);
                    if (empty($common)) {
                        return $fail("The selected {$attribute} cannot be assigned with your role’s modules.");
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $validatedData = $validator->validated();

        //If HR_Manager is assigning module_id → assign Manager role for that module
        if ($authUser->hasRole('HR_Manager') && isset($validatedData['module_id'])) {
            $employee = Employee::findOrFail($validatedData['employee_id']);
            $module = SystemModule::findOrFail($validatedData['module_id']);

            // Example: we assume each module has a role "Manager_<ModuleName>"
            $roleName = $module->name . '_Manager';
            $employee->assignRole($roleName);

            return ResponseWithSuccessData($lang, [
                'employee_id' => $employee->id,
                'assigned_role' => $roleName,
                'module' => $module->name
            ], 1);
        }

        //Otherwise → normal permission assignment
        $newAssign = $this->permissionService->assignEmployeePermission($validatedData);

        return ResponseWithSuccessData($lang, $newAssign, 1);
    }

    // public function updateEmployeePermission(Request $request)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     $authGuard = getAuthenticatedGuard();
    //     $authUser = auth($authGuard)->user();

    //     $validator = Validator::make($request->all(), [
    //         'employee_id' => [
    //             'required',
    //             Rule::exists('employees', 'id')
    //                 ->where('employee_status_id', 1)
    //                 ->whereNull('deleted_at'),
    //         ],
    //         'module_id' => 'nullable|exists:system_modules,id',
    //         'permissions' => 'nullable|array',
    //         'permissions.*' => [
    //             function ($attribute, $value, $fail) use ($authUser) {
    //                 $permission = Permission::with('modules')
    //                     ->where('guard_name', 'employee')
    //                     ->where(function ($q) use ($value) {
    //                         $q->where('id', $value)
    //                             ->orWhere('name', $value);
    //                     })
    //                     ->first();

    //                 if (!$permission) {
    //                     return $fail("The selected {$attribute} is invalid.");
    //                 }

    //                 // case 1: superAdmin → full access
    //                 if ($authUser->hasRole('superAdmin')) {
    //                     return;
    //                 }

    //                 // case 2: HR_Manager → only HR module
    //                 if ($authUser->hasRole('HR_Manager')) {
    //                     $hrModule = SystemModule::where('name', 'HR')
    //                         ->where('is_active', 1)
    //                         ->first();

    //                     if (!$hrModule) {
    //                         return $fail("HR module is not active.");
    //                     }

    //                     $permissionModuleIds = $permission->modules()->pluck('id')->toArray();
    //                     if (!in_array($hrModule->id, $permissionModuleIds)) {
    //                         return $fail("You can only assign HR module permissions.");
    //                     }

    //                     return;
    //                 }

    //                 // case 3: other roles
    //                 $userModuleIds = $authUser->roles()
    //                     ->whereHas('module', function ($q) {
    //                         $q->where('is_active', 1);
    //                     })
    //                     ->pluck('module_id')
    //                     ->unique()
    //                     ->toArray();

    //                 $permissionModuleIds = $permission->modules()
    //                     ->where('is_active', 1)
    //                     ->pluck('id')
    //                     ->toArray();

    //                 $common = array_intersect($userModuleIds, $permissionModuleIds);
    //                 if (empty($common)) {
    //                     return $fail("The selected {$attribute} cannot be assigned with your role’s modules.");
    //                 }
    //             },
    //         ],
    //     ]);

    //     if ($validator->fails()) {
    //         return respondError(
    //             $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
    //             400,
    //             $validator->errors()
    //         );
    //     }

    //     $validatedData = $validator->validated();
    //     $employee = Employee::findOrFail($validatedData['employee_id']);

    //     // Remove old permissions before assigning new ones
    //     $employee->syncPermissions([]);

    //     // HR Manager case
    //     if ($authUser->hasRole('HR_Manager') && isset($validatedData['module_id'])) {
    //         $module = SystemModule::findOrFail($validatedData['module_id']);
    //         $roleName = $module->name . '_Manager';
    //         $employee->assignRole($roleName);
    //     }

    //     if (isset($validatedData['permissions']) && is_array($validatedData['permissions'])) {
    //         $employee->syncPermissions($validatedData['permissions']);
    //     }

    //     $roles = $employee->getRoleNames();
    //     $permissions = $employee->getAllPermissions()
    //         ->groupBy('name')
    //         ->map(function ($items) {
    //             return $items->map(function ($perm) {
    //                 return [
    //                     'id' => $perm->id,
    //                     'name' => $perm->name,
    //                 ];
    //             })->values();
    //         });

    //     return response()->json([
    //         'status' => true,
    //         'message' => $lang == 'en' ? 'Valid request' : 'طلب صحيح',
    //         'code' => 200,
    //         'data' => [
    //             'employee' => [
    //                 'id' => $employee->id,
    //                 'name' => $employee->name,
    //                 'email' => $employee->email,
    //             ],
    //             'roles' => $roles,
    //             'permissions' => $permissions,
    //         ],
    //     ]);
    // }


    public function listModules()
    {
        try {
            $modules = SystemModule::with('permissions')->get();
            return ResponseWithSuccessData($this->lang, $modules, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching modules: ' . $e->getMessage());
            return RespondWithBadRequestData($this->lang, 2);
        }
    }
}
