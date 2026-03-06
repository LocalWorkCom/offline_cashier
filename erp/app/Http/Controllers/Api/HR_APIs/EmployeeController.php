<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Resources\ChefCuisineCategoryResource;
use App\Http\Resources\CuisineCategoryResource;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\CuisineCategory;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Requests\UpdateProfileEmployeeRequest;

use App\Http\Resources\PermissionResource;
use App\Models\ChefCuisineCategory;
use App\Models\Dish;
use App\Models\Role;
use App\Services\HR_Services\EmployeeService;
use App\Services\HR_Services\DepartmentService;

use App\Services\KitchenServices\ChefCuisineCategoryService;
use App\Services\HR_Services\EmployeeScheduleService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class EmployeeController extends Controller
{
    protected $employeeService;
    protected $departmentService;
    protected $checkToken;
    protected $employee;
    protected $chefCuisineCategoryService;
    protected $employeeScheduleService;

    public function __construct(EmployeeService $employeeService, DepartmentService $departmentService, ChefCuisineCategoryService $chefCuisineCategoryService, EmployeeScheduleService $employeeScheduleService)
    {
        $this->employeeService = $employeeService;
        $this->departmentService = $departmentService;
        $this->employeeScheduleService = $employeeScheduleService;
        $this->checkToken = false;
        $this->employee = auth('employee')->user() ? auth('employee')->user() : null;
        $this->chefCuisineCategoryService = $chefCuisineCategoryService;
    }
    public function index(Request $request)
    {
        // try {
        $lang = $request->header('lang', 'en');
        $user = auth('admin')->user() ?? auth('employee')->user(); // new
        $employees = $this->employeeService->getAllEmployees($request, $user);
        $response = paginateOrGetAll($employees, $request);
        $resourceData = EmployeeResource::collection($response['data']);

        return ResponseWithSuccessDataPaginated(
            $lang,
            [
                'data' => $resourceData,
                'meta' => $response['meta']
            ],
            1
        );
        // } catch (\Exception $e) {
        //     Log::error('Error fetching employees: ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }

    public function show(Request $request, $id = null)
    {
        try {
            $lang = $request->header('lang') ?? 'en';
            app()->setLocale($lang);

            if ($request->routeIs('employees.profile.show')) {
                $employee = auth('employee')->user();
            } else {
                $employee = Employee::find($id);
            }
            if (!$employee) {
                return respondError(__('employee.not_found'), 404);
            }

            // always fetch through service with the actual employee id
            $employee = $this->employeeService->getEmployee($employee->id);

            if (!$employee) {
                return respondError(__('employee.not_found'), 404);
            }

            $resourceData = (new EmployeeResource($employee))->toArray(request());
            $schedules = getEmployeeWorkSchedule($employee->id, $lang);
            $resourceData['work_schedules'] = $schedules;

            return ResponseWithSuccessData($lang, $resourceData, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الموظف غير موجود' : 'Employee not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching employee: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(StoreEmployeeRequest $request)
    {
        try {
            $lang = $request->header('lang', 'en');

            $employee =  $this->employeeService->createEmployee($request, $this->employee?->id, 'employee');
            $employee = $this->employeeService->getEmployee($employee->id);
            $resourceData = new EmployeeResource($employee);

            return ResponseWithSuccessData($lang, $resourceData, 1);
        } catch (\Exception $e) {
            Log::error('Error creating employee: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function update(StoreEmployeeRequest $request,  $id = null)
    {
        $lang = $request->header('lang', 'en');
        // try {
        if ($request->routeIs('employees.profile.update')) {
            $employee = auth('employee')->user();
        } else {
            $employee = Employee::find($id);
        }

        if (!$employee) {
            return respondError(__('employee.not_found'), 404);
        }
        $result = $this->employeeService->updateEmployee($employee, $request,  $this->employee->user_id, 'employee');
        $resourceData = new EmployeeResource($result['employee_updated']);

        return ResponseWithSuccessData($lang, $resourceData, 1);
        // } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        //     $message = $lang === 'ar' ? 'الموظف غير موجود' : 'Employee not found';
        //     return respondError($message, 404);
        // } catch (\Exception $e) {
        //     Log::error('Error fetching employee: ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }

    public function updateProfile(UpdateProfileEmployeeRequest $request)
    // public function updateProfile(Request $request)
    {
        $lang = $request->header('lang', 'en');
        // try {
        $employee = auth('employee')->user();

        if (!$employee) {
            return respondError(__('employee.not_found'), 404);
        }
        $result = $this->employeeService->updateEmployeeProfile($employee, $request);
        $resourceData = (new EmployeeResource($result['employee_updated']))->toArray(request());
        $schedules = getEmployeeWorkSchedule($employee->id, $lang);
        $resourceData['work_schedules'] = $schedules;

        return ResponseWithSuccessData($lang, $resourceData, 1);
        // } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        //     $message = $lang === 'ar' ? 'الموظف غير موجود' : 'Employee not found';
        //     return respondError($message, 404);
        // } catch (\Exception $e) {
        //     Log::error('Error fetching employee: ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }
    public function assignCuisine(Request $request)
    {

        $lang = $request->header('lang', 'en');

        $employeeId = $request->input('employee_id');
        $categories = $request->input('categories');

        $employee = Employee::findOrFail($employeeId);

        foreach ($categories as $categoryData) {
            $categoryId = $categoryData['category_id'];
            $dishes = $categoryData['dishes'];

            // Get cuisine_category record
            $cuisineCategory = CuisineCategory::find($categoryId);

            if (!$cuisineCategory) {
                return RespondWithBadRequestData($lang, 2);
            }

            $cuisineId = $cuisineCategory->cuisine_id;
            $categoryId = $cuisineCategory->dish_category_id;
            // $categoryId = getBranchCategoryDetails($employee->branch_id, $categoryId_menu, 'api', null);
            // dd( $categoryId);
            // If dish id is -1, skip dish validation (means assign all dishes in that category)
            if (count($dishes) === 1 && $dishes[0] == -1) {
                continue;
            }
            // dd($cuisineCategory,$categoryId, $cuisineId, $dishes);

            foreach ($dishes as $dishId) {
                $dish = \App\Models\Dish::where('id', $dishId)
                    ->where('category_id', $categoryId)
                    ->where('cuisine_id', $cuisineId)
                    ->first();

                if (!$dish) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid dish ID: $dishId does not belong to category $categoryId and cuisine $cuisineId"
                    ], 400);
                    // return RespondWithBadRequestData($lang, "Invalid dish ID: $dishId does not belong to category $categoryId and cuisine $cuisineId");
                }
            }
        }

        // If validation passes, assign the data (delegated to service)
        $result = $this->employeeService->assign($request);
        return ResponseWithSuccessData($lang, null, 1);
    }
    public function listCuisineCategories(Request $request)
    {
        // try {
        $lang = $request->header('lang', 'en');

        $result = CuisineCategory::with(['dish_category', 'cuisine'])->get();


        return ResponseWithSuccessData(
            $lang,
            CuisineCategoryResource::collection($result),
            1
        );
        // } catch (\Exception $e) {
        //     Log::error('Error updating employee: ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }
    public function listChefs(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:chef,head_chef'
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $type = $request->input('type');

        $query = Employee::query();

        if ($type === 'chef') {
            $query->where('flag', 'chef');
        } elseif ($type === 'head_chef') {
            $query->where('flag', 'Head Chef');
        }

        $result = $query->get();

        return ResponseWithSuccessData(
            $lang,
            $result,
            1
        );
    }
    public function listCuisineCategoryDishes(Request $request, $cuisineCategoryId)
    {
        $lang = $request->header('lang', 'en');

        $cuisineCategory = CuisineCategory::with(['dish_category', 'cuisine'])
            ->find($cuisineCategoryId);

        if (!$cuisineCategory) {
            $msg = $lang === 'ar' ? 'فئة المطبخ غير موجودة.' : 'Cuisine category not found.';
            return respondError($msg, 404);
        }
        $dishes = Dish::where('category_id', $cuisineCategory->dish_category_id)
            ->where('cuisine_id', $cuisineCategory->cuisine_id)
            ->get()
            ->map(function ($dish) {
                return [
                    'id' => $dish->id,
                    'name_en' => $dish->name_en,
                    'name_ar' => $dish->name_ar,
                    'name' => $dish->name,
                ];
            });
        if ($dishes->isEmpty()) {
            $msg = $lang === 'ar' ? 'لا توجد أطباق لهذه الفئة.' : 'No dishes found for this category.';
            return respondError($msg, 404);
        }

        return ResponseWithSuccessData(
            $lang,
            $dishes,
            1
        );
    }

    public function getChefAssignedCuisines(Request $request)
    {
        $lang = $request->header('lang', 'en');



        $assignedCuisines = $this->chefCuisineCategoryService->index();
        $response = paginateOrGetAll($assignedCuisines['data']['assignedCuisines'], $request);
        $resourceData = ChefCuisineCategoryResource::collection($response['data']);
        return ResponseWithSuccessDataPaginated(
            $lang,
            [
                'data' => $resourceData,
                'meta' => $response['meta']
            ],
            1
        );
    }
    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            $employee = Employee::findOrFail($id);
            $employee->update(['deleted_by' => auth('admin')->id() ?? auth('employee')->id()]);
            $employee->delete();

            return ResponseWithSuccessData($lang, null, 'Employee deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting employee: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            $employee = Employee::onlyTrashed()->findOrFail($id);
            $employee->restore();

            return ResponseWithSuccessData($lang, $employee, 'Employee restored successfully.');
        } catch (\Exception $e) {
            Log::error('Error restoring employee: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function getEmployeeByDepartment(Request $request)
    {

        $lang = $request->header('lang', 'en');

        $messages = [
            'department_id.exists' => __('validation.email_or_phone.required'),
            'subDepartmentId.exists' => __('validation.password.required'),
        ];

        $validator = Validator::make($request->all(), [
            'department_id'    => 'nullable|exists:departments,id',
            'subDepartmentId'  => 'nullable|exists:departments,id'
        ], $messages);

        if ($validator->fails()) {

            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        if (!$request->department_id && !$request->subDepartmentId) {
            return respondErrorData('department required', 400, 'At least one of department_id or subDepartmentId must be provided.');
        }


        $employees = Employee::query()
            ->when($request->department_id, function ($query) use ($request) {
                $query->where('department_id', $request->department_id);
            })
            ->when($request->subDepartmentId, function ($query) use ($request) {
                $query->where('sub_department_id', $request->subDepartmentId);
            })
            ->get();

        return ResponseWithSuccessData($lang, $employees, 1);
    }
    public function getChildrenEmployee(Request $request)
    {
        $currentDepartmentId = $this->employee->department_id;
        $lang = $request->header('lang', 'en');
        $childDepartmentIds = $this->departmentService->getAllChildDepartmentIds($currentDepartmentId);

        $employees = Employee::query()
            ->whereIn('sub_department_id', $childDepartmentIds)
            ->get();

        return ResponseWithSuccessData($lang, $employees, 1);
    }

    public function getParentEmployees($id, $lang)
    {
        $user = auth('employee')->user();
        $employeedep = Employee::where('id', $id)->first();
        $currentDepartmentId = $employeedep->department_id;
        // $lang = $request->header('lang', 'en');

        $parentDepartmentIds = $this->departmentService->getAllParentDepartmentIds($currentDepartmentId);

        $employees = Employee::query()
            ->whereIn('sub_department_id', $parentDepartmentIds)
            ->get();

        foreach ($employees as $employee) {
            send_push_notification(
                $employee->device_token,
                "test",
                "test",
                "أنهاء الخدمة",
                "Termination",
                "employee",
                $employee->id,
                $user->id,
                $user->id,
                'ar',
                10
            );
        }

        return ResponseWithSuccessData($lang, $employees, 1);
    }
    public function getEmployees(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $user = auth('admin')->user() ?? auth('employee')->user();
            if ($user && ($user->hasRole('Branch Manager', 'admin') || $user->hasRole('Branch_Manager', 'employee'))) {
                $request = $request->merge(['branch_id' => $user->branch_id]);
            }
            $employees = $this->employeeService->getAllEmployeesByBranch($request, $user?->id);
            return ResponseWithSuccessData(
                $lang,
                $employees->map(fn($employee) => [
                    'id'        => $employee->id,
                    'full_name' => trim($employee->first_name . ' ' . $employee->last_name),
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'image' => $employee->image,
                    'email' => $employee->email,
                    'employee_code' => $employee->employee_code,
                    'phone_number' => $employee->phone_number,
                    'national_id' => $employee->national_id,
                    'last_attendance' => $employee->attendanceRecords->last()
                        ? $employee->attendanceRecords->last()->date . ' ' .
                            \Carbon\Carbon::parse($employee->attendanceRecords->last()->clock_in_time)->format('h:i A')
                        : null,
                    'nationality' => $employee->nationality?->name ?? null,
                    'department' => $employee->department?->name ?? null,
                    'position' => $employee->position?->name ?? null,
                    'employeeRates' => $employee->employeeRates ?? null,
                    'supervisor' => $employee->supervisor?->first_name . ' ' . $employee->supervisor?->last_name ?? null,
                ]),
                1
            );
        } catch (\Exception $e) {
            Log::error('Error fetching employees: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function getDepartmentHierarchy()
    {
        try {
            $departments = Department::with([
                'positions:id,name_en,name_ar,department_id',
                'subDepartments.positions:id,name_en,name_ar,department_id',
                'subDepartments.subDepartments.positions:id,name_en,name_ar,department_id', // optional deeper nesting
            ])
                ->whereNull('parent_id')
                ->select('id', 'name_en', 'name_ar')
                ->get();

            // 🧩 Transform the response
            $formatted = $departments->map(function ($department) {
                return $this->formatDepartment($department);
            });

            return response()->json([
                'status' => true,
                'message' => 'Departments hierarchy retrieved successfully',
                'data' => $formatted,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching department hierarchy: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch departments hierarchy',
            ], 500);
        }
    }

    /**
     * 🔁 Recursive formatter for department structure
     */
    private function formatDepartment($department)
    {
        return [
            'id' => $department->id,
            'name_en' => $department->name_en,
            'name_ar' => $department->name_ar,
            'positions' => $department->positions->map(function ($position) {
                return [
                    'id' => $position->id,
                    'name_en' => $position->name_en,
                    'name_ar' => $position->name_ar,
                ];
            }),
            'sub_departments' => $department->subDepartments->map(function ($subDept) {
                return $this->formatDepartment($subDept);
            }),
        ];
    }
    public function getHierarchies(Request $request)
    {
        $response = $this->employeeService->getHierarchies($request);
        $companies = $response['companies'];
        $branches = $response['branches'];
        $departments = $response['departments'];
        $employee = $response['employees'];

        return ResponseWithSuccessData('en', [
            // 'companies' => $companies,
            // 'branches' => $branches,
            'departments' => $departments,
            // 'employees' => $employee
        ], 1);
    }
    public function unassignChef(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        try {
            $response = $this->chefCuisineCategoryService->destroy($id);

            if ($response['success']) {
                return ResponseWithSuccessData($lang, null, 1);
            }

            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الطاهي غير موجود' : 'Chef not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching chef: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function getEmployeeRoleAndPermission(Request $request)
    {
        $lang = $request->header('lang', 'en');
        try {
            $employee = Auth::guard('employee')->user();
            if ($employee->hasPermissionTo('view employees_role_and_permission', 'employee')) {
                $employee_id = $request->employee_id ?? $employee->id;
            } else {
                $employee_id = $employee->id;
            }
            $employee = Employee::with(['roles.permissions', 'permissions'])->findOrFail($employee_id);

            // Collect role permissions
            $rolePermissions = $employee->roles
                ->flatMap(fn($role) => $role->permissions)
                ->pluck('id')
                ->toArray();

            // Collect direct permissions
            $directPermissions = $employee->permissions->pluck('id')->toArray();

            // Merge all permissions and remove duplicates
            $allPermissionIds = array_unique(array_merge($rolePermissions, $directPermissions));

            // Fetch full permissions from DB
            $permissions = Permission::whereIn('id', $allPermissionIds)
                ->where('guard_name', 'employee')
                ->where('is_active', 0)
                ->get();

            // Group permissions by the second word in name
            $grouped = $permissions
                ->map(function ($p) use ($rolePermissions, $directPermissions) {
                    $parts = explode(' ', $p->name);
                    return [
                        'id'        => $p->id,
                        'name'      => $p->name,
                        'assigned_via' => in_array($p->id, $rolePermissions) ? 'role' : 'direct',
                        'category'  => ucfirst($parts[1] ?? 'Others'),
                    ];
                })
                ->groupBy('category')
                ->map(fn($items, $category) => [
                    'category' => $category,
                    'items'    => $items->map(fn($i) => Arr::except($i, ['category']))->values(),
                ])
                ->values();

            $response = [
                'employee_id'   => $employee->id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'roles'         => $employee->roles->pluck('name')->values(),
                'permissions'   => $grouped,

            ];

            return ResponseWithSuccessData($lang, $response, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching roles and permissions: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function createAccessDashboard(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);

            $authUser = auth('employee')->user();

            // Validate input
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|integer|exists:employees,id',
                'password' => 'required|string|min:6',
                'role_id' => 'nullable|integer|exists:roles,id',
                'permission_ids' => 'nullable|array',
                'permission_ids.*' => 'integer|exists:permissions,id',
            ]);

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق من البيانات.',
                    400,
                    $validator->errors()
                );
            }

            $employee = Employee::find($request->employee_id);
            if (!$employee) {
                return respondError(
                    $lang == 'en' ? 'Employee not found.' : 'الموظف غير موجود.',
                    404
                );
            }

            //  Role-based authorization
            if (!($authUser->hasRole('HR_Manager', 'employee') || $authUser->hasRole('superAdmin', 'employee'))) {
                if ($authUser->flag !== $employee->flag || $authUser->branch_id !== $employee->branch_id) {
                    return respondError(
                        $lang == 'en'
                            ? 'You are not authorized to create dashboard access for this employee.'
                            : 'غير مصرح لك بإنشاء وصول للوحة التحكم لهذا الموظف.',
                        403
                    );
                }
            }

            //Optional: Prevent duplicate activation
            if ($employee->is_active == 1) {
                return respondError(
                    $lang == 'en'
                        ? 'Employee already has dashboard access.'
                        : 'الموظف لديه بالفعل صلاحية الوصول للوحة التحكم.',
                    400
                );
            }

            // Activate employee & set password
            $employee->update([
                'password' => Hash::make($request->password),
                'is_active' => 1,
            ]);

            // Assign role or permissions
            if ($request->filled('role_id')) {
                // ✅ Ensure role belongs to employee guard
                $role = Role::where('id', $request->role_id)
                    ->where('guard_name', 'employee')
                    ->first();

                if ($role) {
                    $employee->syncRoles([$role->name]);
                } else {
                    return respondError(
                        $lang == 'en' ? 'Invalid role for employee guard.' : 'دور غير صالح لحارس الموظف.',
                        400
                    );
                }
            } elseif ($request->filled('permission_ids')) {
                // Ensure permissions belong to employee guard
                $permissions = Permission::whereIn('id', $request->permission_ids)
                    ->where('guard_name', 'employee')
                    ->pluck('name')
                    ->toArray();

                if (empty($permissions)) {
                    return respondError(
                        $lang == 'en' ? 'No valid permissions found for employee guard.' : 'لا توجد صلاحيات صالحة لحارس الموظف.',
                        400
                    );
                }

                $employee->syncPermissions($permissions);
            } else {
                //  Default basic dashboard permissions (employee guard)
                $defaultPermissions = ['view dashboard', 'view profile', 'update profile'];

                // Ensure these permissions exist in DB with correct guard
                foreach ($defaultPermissions as $permName) {
                    Permission::firstOrCreate([
                        'name' => $permName,
                        'guard_name' => 'employee',
                    ]);
                }

                $employee->syncPermissions($defaultPermissions);
            }
            return ResponseWithSuccessData($lang, [
                'employee_id' => $employee->id,
                'name' => $lang == 'en'
                    ? ($employee->first_name . ' ' . $employee->last_name)
                    : ($employee->first_name_ar  . ' ' . $employee->last_name_ar),
                'access_dashboard' => true,
                'assigned_role' => $employee->roles->pluck('name'),
                'assigned_permissions' => $employee->permissions->pluck('name'),
            ], 1);
        } catch (\Exception $e) {
            Log::error('Error creating dashboard access: ' . $e->getMessage());
            return respondError(
                $lang == 'en' ? 'An error occurred while creating dashboard access.' : 'حدث خطأ أثناء إنشاء حساب الوصول.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
    public function toggleDashboardAccess(Request $request)
    {
        // try {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer|exists:employees,id',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق من البيانات.',
                400,
                $validator->errors()
            );
        }

        $authUser = auth('employee')->user();

        // 🧠 Only superAdmin or hrmanager can toggle any account
        if (!$authUser->hasRole(['superAdmin', 'Hr_Manager'], 'employee')) {
            // Others can only toggle accounts of employees in their same branch + flag
            $employee = Employee::where('id', $request->employee_id)
                ->where('branch_id', $authUser->branch_id)
                ->where('flag', $authUser->flag)
                ->first();

            if (!$employee) {
                return response()->json([
                    'status' => false,
                    'message' => __('You are not allowed to change status of this employee.')
                ], 403);
            }
        } else {
            $employee = Employee::find($request->employee_id);
        }

        // 🧩 Toggle active status
        $employee->update([
            'is_active' => $request->status,
        ]);

        $message = $request->status
            ? __('Employee dashboard account activated successfully.')
            : __('Employee dashboard account deactivated successfully.');
        return ResponseWithSuccessData($lang, [
            'employee_id' => $employee->id,
            'employee_name' => $employee->first_name . ' ' . $employee->last_name,
            'is_active' => (bool) $employee->is_active,
        ], 1);
        // } catch (\Exception $e) {
        //     Log::error('Error toggling dashboard access: ' . $e->getMessage());

        //     return response()->json([
        //         'status' => false,
        //         'message' => __('Failed to update account status.'),
        //     ], 500);
        // }
    }

    public function myTeam(Request $request)
    {
        $lang = $request->header('lang', 'en');
        $user = auth('employee')->user();

        $employees = $this->employeeService->getMyTeamEmployees($user->id);

        // $response = paginateOrGetAll($employees, $request);

        return ResponseWithSuccessData(
            $lang,
            $employees,
            1
        );
    }
}
