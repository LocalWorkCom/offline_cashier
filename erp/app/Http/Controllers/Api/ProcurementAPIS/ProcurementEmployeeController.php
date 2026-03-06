<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProcurementEmployeeController extends Controller
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

            $employees = $this->employeesService->getAllEmployees($request, 'purchase');
            $result = paginateOrGetAll($employees, $request, null);

            $employees = EmployeeResource::collection($result['data']);
            $employees->each(function ($item) use ($request) {
                $item->setModule('purchase',$request);
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
        app()->setLocale($lang);

        $employee = $this->employeesService
            ->showEmployee($request, $id, 'purchase');

        if (!$employee) {
            return respondError(
                $lang === 'ar' ? 'الموظف غير موجود' : 'Employee not found',
                404
            );
        }

        $resource = new EmployeeResource($employee);
        $resource->setModule('purchase',$request);

        return ResponseWithSuccessData($lang, $resource, 1);
    }
    public function profile(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        $employee = $this->employeesService
            ->showEmployee($request, null, 'purchase');

        if (!$employee) {
            return respondError(
                $lang === 'ar' ? 'الموظف غير موجود' : 'Employee not found',
                404
            );
        }

        $resource = new EmployeeResource($employee);
        $resource->setModule('purchase',$request);
        $resource->setprofile('profile');

        return ResponseWithSuccessData($lang, $resource, 1);
    }
    public function changePassword(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $messages = [
            "password.required" => __('validation.newPasswordRequired'),
            "password_confirm.required" => __('validation.confirmPassword'),
            "password_confirm.same" => __('validation.confirmPasswordSame'),
            'password.new' => __('validation.newOldPassword')
        ];

        $validator = Validator::make($request->all(), [
            "password" => "required|min: 6",
            "password_confirm" => "required|same:password",
        ], $messages);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $user = Auth::user();

        if (Hash::check($request->password, $user->password)) {
            return respondError('Password Error', 403, ['password' => [__('validation.newOldPassword')]]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $user->tokens()->delete();
        $token = $user->createToken("MyApp")->accessToken;

        $userData = $user->only(['id', 'name', 'email', 'country_code', 'phone']);
        $success = [
            "token" => $token,
            "user" => $userData,
        ];

        return ResponseWithSuccessData($lang, $success, 15);
    }
    public function updateProfile(Request $request)
{
    try {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'department_id' => 'required|integer|exists:departments,id',

            'position_name' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->whereNull('deleted_at')
                    ->ignore(auth('employee')->id()),
            ],
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }
        $nameParts = preg_split('/\s+/', trim($request->name), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';
        $employee = Employee::find(auth('employee')->user()->id) ;
        $employee->first_name = $firstName;
        $employee->last_name = $lastName;

        $employee->email = $request->email;
        $employee->department_id = $request->department_id;
        $employee->position_name = $request->position_name;

        $employee->save();


        return ResponseWithSuccessData($lang, $employee, 1);
    } catch (ValidationException  $e) {
        return back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        Log::error('Profile update failed', ['exception' => $e]);
        return back()->with('error', __('auth.update_failed'));
    }
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
            'national_id'       => 'required|string|min:14|max:20|unique:employees,national_id',
            'country_code'      => 'required|string|max:20|exists:countries,phone_code',
            'phone_number'      => 'required|string|max:20|unique:employees,phone_number',
            'position'          => 'required|string|max:255',
            'department'        => 'required|exists:departments,id',
            'employee_code'     => 'required|string|max:50|unique:employees,employee_code',
            'status'            => 'required|in:1,5,7',
            'role'           => 'nullable|integer|exists:roles,id',
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
        $employee = $this->employeesService->createEmployee($request, 'purchase');
        $employee->load(['inventoryStores']);
        $employee = new EmployeeResource($employee);
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
            'status'         => 'required|in:1,5,7',
            'role'           => 'nullable|integer|exists:roles,id',
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

        $employee = $this->employeesService->updateEmployee($employee, $request, 'purchase');
        $employee->load(['inventoryStores']);

        $employee = new EmployeeResource($employee);
        return ResponseWithSuccessData($lang, $employee, 1);
    }

    public function accessDashboard(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        return $this->employeesService->handleDashboardAccess(
            $request,
            'purchase',
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


        $role = $this->employeesService->createRoleWithPermissions($request, 4);
        return  $role;
    }

    public function assignPermissionsToEmployee(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        return $this->employeesService->assignPermissionsToEmployee($request, 'purchase', $lang);
    }

    public function getEmployeeRolesAndPermissions(Request $request, $employeeId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        return $this->employeesService
            ->getEmployeeRolesAndPermissionsService($employeeId, 'purchase', $lang);
    }
    public function getPurchaseRoles(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $roles = $this->employeesService->getModuleRoles($request, 4);

        return ResponseWithSuccessData($lang, $roles, 1);
    }

    public function destroyEmployee(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $employee = Employee::where('flag', 'purchase')->find($id);
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
