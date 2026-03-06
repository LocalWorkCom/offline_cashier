<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Services\HR_Services\DepartmentService;
use App\Services\HR_Services\EmployeeService;
use App\Models\Department;
use App\Models\Rate;
use App\Models\SystemModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    protected $employeeService;
    protected $departmentService;
    protected $hidden = ['name', 'description'];
    protected $employee;

    public function __construct(EmployeeService $employeeService, DepartmentService $departmentService)
    {
        $this->employeeService = $employeeService;
        $this->departmentService = $departmentService;
    }


    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        $department_id = $request->department_id;
        $module = getCurrentModuleDependOnRoute($request, 'departments');
        $request->attributes->set('module', $module);
        if (!$department_id) {
            $departments = Department::whereNull('parent_id');
        } else {

            // $department_ids =$this->departmentService->getAllChildDepartmentIds($department_id);
            // $departments = Department::whrereIn('id', $department_ids)->get(); //undirect ????

            $departments = Department::where('parent_id', $department_id);
        }
        if ($request->filled('from')) {
            $departments->where('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $departments->where('created_at', '<=', $request->to);
        }

        if ($request->has('status')) {
            $departments->where('status', $request->status);
        }

        $departments = paginateOrGetAll($departments, $request, null, null);

        $data = DepartmentResource::collection($departments['data']);

        return ResponseWithSuccessDataPaginated($lang, [
            'data' => $data,
            'meta' => $departments['meta']
        ], 1);
    }
    public function list(Request $request)
    {
        $lang = $request->header('lang', 'en');
        $module = getCurrentModuleDependOnRoute($request, 'departments');
        $request->merge(['filter_by_auth_branch' => 1]);
        $departments = $this->departmentService->getAllDepartments($request);
        if ($request->filled('from')) {
            $departments->where('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $departments->where('created_at', '<=', $request->to);
        }

        if ($request->has('status')) {
            $departments->where('status', $request->status);
        }
        $request->attributes->set('module', $module);

        $response = paginateOrGetAll($departments, $request, null, ['created_at', 'updated_at']);
        $resourceData = DepartmentResource::collection($response['data']);

        return ResponseWithSuccessDataPaginated(
            $lang,
            [
                'data' => $resourceData,
                'meta' => $response['meta']
            ],
            1
        );
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $module = getCurrentModuleDependOnRoute($request, 'departments');

            $department = Department::find($id);
            $request->attributes->set('module', $module);

            return ResponseWithSuccessData($lang, new DepartmentResource($department), 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'القسم غير موجود' : 'Department not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error updating department: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
//        try {
            $systemModuleStatus = checkModuleActivation(1); //HR module id

            // if ($systemModuleStatus && getCurrentModuleDependOnRoute($request, 'departments') != 'hr') {
            //     return respondError($lang == 'en' ?  'This API is stopped. Adding employees can only be done from the HR module.' : 'هذه الواجهة متوقفة. يمكن إضافة الموظفين فقط من وحدة الموارد البشرية.', 400);
            // }
            $module = getCurrentModuleDependOnRoute($request, 'departments');

            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string',
                'name_en' => 'required|string',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'instructions_ar' => 'nullable|string',
                'instructions_en' => 'nullable|string',
                'branch_id' => [   // Required only if module is NOT procurement
                    function ($attribute, $value, $fail) use ($module, $lang) {
                        if ($module !== 'procurement' && empty($value)) {
                            $fail($lang === 'ar' ? 'الفرع مطلوب.' : 'Branch is required.');
                        }
                    },
                    'nullable',
                    'exists:branches,id'], // Add this line
                'status' => 'nullable|in:0,1',
            ]);

            if ($validator->fails()) {
                return RespondWithBadRequestWithData($validator->errors());
            }
            $module = getCurrentModuleDependOnRoute($request, 'departments');
            $request->attributes->set('module', $module);
            $request->merge([
                'branch_id' => auth('employee')->user()->branch_id
            ]);
            $department = $this->departmentService->createDepartment($request->all(), $module);
            return ResponseWithSuccessData($lang,  new DepartmentResource($department), 1);
//        } catch (\Exception $e) {
//            Log::error('Error creating department: ' . $e->getMessage());
//            return RespondWithBadRequestData($lang, 2);
//        }
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

//        try {
//            $systemModuleStatus = checkModuleActivation(1); //HR module id
//
//            if ($systemModuleStatus && getCurrentModuleDependOnRoute($request, 'departments') != 'hr') {
//                return respondError($lang == 'en' ?  'This API is stopped. Adding employees can only be done from the HR module.' : 'هذه الواجهة متوقفة. يمكن إضافة الموظفين فقط من وحدة الموارد البشرية.', 400);
//            }
            $module = getCurrentModuleDependOnRoute($request, 'departments');

            $validator = Validator::make($request->all(), [

                'name_ar' => 'required|string',
                'name_en' => 'required|string',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'instructions_ar' => 'nullable|string',
                'instructions_en' => 'nullable|string',
                'branch_id' => [
                    // Required only if module is NOT procurement
                    function ($attribute, $value, $fail) use ($module, $lang) {
                        if ($module !== 'procurement' && empty($value)) {
                            $fail($lang === 'ar' ? 'الفرع مطلوب.' : 'Branch is required.');
                        }
                    },
                    'nullable',
                    'exists:branches,id'
                ],                'status' => 'nullable|in:0,1',
            ]);

            if ($validator->fails()) {
                return RespondWithBadRequestWithData($validator->errors());
            }

            $department = $this->departmentService->updateDepartment($request->all(), $id, $module);
            $request->attributes->set('module', $module);

            return ResponseWithSuccessData($lang,  new DepartmentResource($department), 1);
//        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
//            $message = $lang === 'ar' ? 'القسم غير موجود' : 'Department not found';
//            return respondError($message, 404);
//        } catch (\Exception $e) {
//            Log::error('Error updating department: ' . $e->getMessage());
//            return RespondWithBadRequestData($lang, 2);
//        }
    }

    public function destroy( Request $request, $id = null)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $ids = collect(
                $id ? [$id] : $request->input('ids', [])
            )->unique()->values();

            // Validate IDs
            if ($ids->isEmpty()) {
                return respondError(
                    $lang === 'ar' ? 'لم يتم إرسال أي معرف.' : 'No Department IDs provided.',
                    422
                );
            }

            $departments = Department::whereIn('id', $ids)->get();

            if ($departments->count() !== $ids->count()) {
                return respondError(
                    $lang === 'ar' ? 'بعض اقسام غير موجودة.' : 'Some Departments were not found.',
                    404
                );
            }

            $systemModuleStatus = checkModuleActivation(1); //HR module id

            if ($systemModuleStatus && getCurrentModuleDependOnRoute($request, 'departments') != 'hr') {
                return respondError($lang == 'en' ?  'This API is stopped. Adding employees can only be done from the HR module.' : 'هذه الواجهة متوقفة. يمكن إضافة الموظفين فقط من وحدة الموارد البشرية.', 400);
            }
            foreach ($departments as $department) {

                if ($department->employees()) {
                    $department->status = 'active';
                    $department->save();
                    return respondError($lang == 'en' ? 'Cannot delete department as it assigned to employees , it updated to archived' : 'لا يمكن حذف القسمو تم وقف تفعيله', 400);
                } else {
                    $this->departmentService->deleteDepartment($id);
                }
            }
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'القسم غير موجود' : 'Department not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error updating department: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
