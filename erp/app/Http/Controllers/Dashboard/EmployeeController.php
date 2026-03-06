<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\StoreEmployeeRequest;
use App\Models\BankName;
use App\Models\Branch;
use App\Models\Country;
use App\Models\CuisineCategory;
use App\Models\EducationLevel;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\EmployeeStatus;
use App\Models\FiledOfStudy;
use App\Models\MilitaryServiceStatus;
use App\Models\Nationality;
use App\Models\PaymentFrequency;
use App\Models\PaymentType;
use App\Models\Position;
use App\Models\Department;
use App\Models\University;
use App\Services\KitchenServices\ChefCuisineCategoryService;
use Illuminate\Http\Request;


use App\Models\Shift;
use App\Imports\EmployeeImport;
use Illuminate\Validation\Rule;
use App\Services\HR_Services\EmployeeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CompanyProfileSetting;
use App\Models\Dish;
use Spatie\Permission\Models\Permission;
use Database\Seeders\CompanyProfileSettingsTableSeeder;

class EmployeeController extends Controller
{
    protected $employeeService;
    protected $chefCuisineService;

    protected $checkToken;

    public function __construct(EmployeeService $employeeService, ChefCuisineCategoryService $chefCuisineService)
    {
        $this->employeeService = $employeeService;
        $this->chefCuisineService = $chefCuisineService;

        $this->checkToken = false;
    }

    public function index(Request $request)
    {
        $employees = $this->employeeService->getAllEmployees($request, auth('admin')->user())->get();
        $departments = Department::all();
        $positions = Position::all();
        $fields = FiledOfStudy::get();
        $levels = EducationLevel::get();
        $universities = University::get();
        return view('dashboard.employee.index', compact('employees', 'departments', 'positions'));
    }


    public function show($id)
    {
        $employee = $this->employeeService->getEmployee($id);
        return view('dashboard.employee.show', compact('employee'));
    }
    public function fetchSupervisors(Request $request)
    {
        $branchId = $request->query('branch_id');
        $supervisors = Employee::where('branch_id', $branchId)
            ->where('flag', 'supervisor')
            ->get(['id', 'first_name', 'last_name', 'employee_code']);

        return response()->json($supervisors);
    }
    private function getGroupedPermissions()
    {
        $permissions = Permission::where('guard_name', 'admin')->where('is_active', 0)->get();
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $parts = explode(' ', $permission->name);
            $group = $parts[1] ?? 'Others'; // Default group name if no second part exists
            $groupedPermissions[$group][] = $permission;
        }
        return $groupedPermissions;
    }
    public function create()
    {
        $countries = Country::all();
        $nationalities = Nationality::all();
        $militarystatuses = MilitaryServiceStatus::all();
        $departments = Department::all();
        $positions = Position::all();
        $supervisors = Employee::where('flag', 'supervisor')->get();
        $branches = Branch::where('is_active', 1)->whereNull('deleted_at')->get();
        $shifts = Shift::all();
        $groupedPermissions = $this->getGroupedPermissions();
        $employeeStatuses = EmployeeStatus::all();
        $fields = FiledOfStudy::get();
        $levels = EducationLevel::get();
        $universities = University::get();
        $payment_types = PaymentType::get();
        $payment_frequencies = PaymentFrequency::get();
        $banks = BankName::get();
        $response = $this->chefCuisineService->index();
        $cuisineCategories = CuisineCategory::with(['dishes', 'dish_category', 'cuisine'])->get();
        // dd($cuisineCategories);
        return view(
            'dashboard.employee.create',
            compact('countries', 'cuisineCategories', 'nationalities', 'departments', 'positions', 'supervisors', 'groupedPermissions', 'militarystatuses', 'branches', 'shifts', 'employeeStatuses', 'fields', 'levels', 'universities', 'shifts', 'groupedPermissions', 'payment_types', 'payment_frequencies', 'banks')
        );
    }

    public function store(StoreEmployeeRequest $request)
    {
        try {
            $this->employeeService->createEmployee($request, auth('admin')->user()->id, 'admin');
            return redirect()->route('employees.list')->with('success', __('validation.employee_created'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'code' => 422,
                'data' => $e->errors(),
            ], 422);
        }
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $countries = Country::all();
        $nationalities = Nationality::all();
        $departments = Department::all();
        $positions = Position::all();
        $supervisors = Employee::where('id', '!=', $id)->get();
        return view(
            'dashboard.employee.edit',
            compact('employee', 'countries', 'nationalities', 'departments', 'positions', 'supervisors')
        );
    }

    // public function update(Request $request, $id)
    // {
    //     $employee = Employee::findOrFail($id);
    //     $validatedData = $request->validate([
    //         'employee_code' => 'nullable|string|unique:employees,employee_code,' . $employee->id,
    //         'first_name' => 'nullable|string',
    //         'last_name' => 'nullable|string',
    //         'flag' => 'required',

    //         'email' => [
    //             'nullable',
    //             'email',
    //             Rule::unique('employees')->ignore($employee),
    //         ],
    //         'country_code' => 'required|string',
    //         'phone' => [
    //             'required',
    //             'numeric',
    //             'min:1',
    //             function ($attribute, $value, $fail) use ($request) {
    //                 if (!empty($request->country_code)) {
    //                     $country = Country::where('phone_code', $request->country_code)->first();
    //                     if ($country && strlen($value) != $country->length) {
    //                         $fail(__('validation.custom.phone.length', ['attribute' => __('auth.phone'), 'length' => $country->length]));
    //                     }
    //                 }
    //             },
    //         ],
    //         'gender' => 'nullable|string',
    //         'birth_date' => 'nullable|date',
    //         'national_id' => 'nullable|numeric|min:1|unique:employees,national_id,' . $employee->id,
    //         'passport_number' => 'nullable|string|unique:employees,passport_number,' . $employee->id,
    //         'marital_status' => 'nullable|string',
    //         'blood_group' => 'nullable|string',
    //         'address_en' => 'nullable|string',
    //         'address_ar' => 'nullable|string',
    //         'nationality_id' => 'nullable|exists:nationalities,id',
    //         'department_id' => 'nullable|exists:departments,id',
    //         'position_id' => 'nullable|exists:positions,id',
    //         'supervisor_id' => 'nullable|exists:employees,id',
    //         'hire_date' => 'nullable|date',
    //         'salary' => 'nullable|numeric|min:1',
    //         'assurance_salary' => 'nullable|numeric|min:1',
    //         'assurance_number' => 'nullable|numeric|min:1|unique:employees,assurance_number,' . $employee->id,
    //         'bank_account' => 'nullable|numeric|min:1|unique:employees,bank_account,' . $employee->id,
    //         'employment_type' => 'nullable|string',
    //         'status' => 'nullable|string',
    //         'notes' => 'nullable|string',
    //         'is_biometric' => 'nullable|boolean',
    //         'biometric_id' => 'nullable|numeric|min:1|unique:employees,biometric_id,' . $employee->id,
    //         'vehicle_number' => 'required_if:flag,driver',
    //         'vehicle_type' => 'required_if:flag,driver',
    //         // 'city_id'=> 'required|exists:cities,id',
    //         // 'area_id'=> 'required|exists:areas,id',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
    //         'facebook_link' => 'nullable|url',
    //         'instagram_link' => 'nullable|url',
    //         'twitter_link' => 'nullable|url',
    //         'work_permit_expiry_date' => 'nullable|date',
    //         'residency_expiry_date' => 'nullable|date',
    //         'passport_expiry_date' => 'nullable|date',
    //         'passport_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
    //         'work_permit' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
    //         'resume' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
    //         'military_status_id' => 'required|numeric|exists:military_service_statuses,id',
    //         'whatsapp_number' => 'nullable|string',
    //         'current_address' => 'required|string',
    //         'home_country_address' => 'required|string',
    //         'emergency_contact_one_name' => 'nullable|string',
    //         'emergency_contact_one_relation' => 'nullable|string',
    //         'emergency_contact_one_phone' => 'nullable|numeric|min:1',
    //         'emergency_contact_two_name' => 'nullable|string',
    //         'emergency_contact_two_relation' => 'nullable|string',
    //         'emergency_contact_two_phone' => 'nullable|numeric|min:1',
    //         'employee_status_id' => 'required|numeric|exists:employees,id',
    //         'previous_position' => 'nullable|string',
    //         'previous_salary' => 'nullable|numeric|min:1',
    //         'expected_salary' => 'nullable|numeric|min:1',
    //         'num_experience_years' => 'nullable|numeric|min:0',
    //         'shift_id' => 'nullable|exists:shifts,id',
    //         'schedule_start_date' => 'nullable|date',
    //         'schedule_end_date' => 'nullable|date',
    //     ], [
    //         'employee_code.unique' => __('validation.employee_code_unique'),
    //         'national_id.unique' => __('validation.national_id_unique'),
    //         'passport_number.unique' => __('validation.passport_number_unique'),
    //         'assurance_number.unique' => __('validation.assurance_number_unique'),
    //         'bank_account.unique' => __('validation.bank_account_unique'),
    //         'biometric_id.unique' => __('validation.biometric_id_unique'),
    //     ]);

    //     $this->employeeService->updateEmployee($validatedData, $id, $this->checkToken);
    //     return redirect()->route('employees.list')->with('success', __('validation.employee_updated'));
    // }

    public function destroy($id)
    {
        $this->employeeService->deleteEmployee($id, $this->checkToken);
        return redirect()->route('employees.list')->with('success', __('validation.employee_deleted'));
    }
    public function importExcel(Request $request)
    {
        // Validate the file input
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        $file = $request->file('file');

        if (!$file) {
            Log::error('No file uploaded');
            return redirect()->back()->with('error', 'No file was uploaded.');
        }

        Log::info('File Uploaded Successfully', ['file_name' => $file->getClientOriginalName()]);

        try {
            Excel::import(new EmployeeImport, $file);
            return redirect()->back()->with('success', 'Employees imported successfully.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            Log::error('Excel Validation Error', ['errors' => $e->errors()]);
            return redirect()->back()->with('error', 'Validation error in Excel file.');
        } catch (\Exception $e) {
            Log::error('Excel Import Error', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'There was an error importing the file: ' . $e->getMessage());
        }
    }



    public function getPositionsByDepartment($departmentId)
    {
        $positions = Position::where('department_id', $departmentId)->get();

        return response()->json([
            'success' => true,
            'positions' => $positions->map(function ($position) {
                return [
                    'id' => $position->id,
                    'name' => app()->getLocale() === 'ar' ? $position->name_ar : $position->name_en,
                ];
            }),
        ]);
    }
    // public function import(Request $request)
    // {
    //     // Validate the uploaded file
    //     // $request->validate([
    //     //     'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
    //     // ]);

    //     // Log the file details for debugging
    //     $file = $request->file('file');
    //     // if (!$file->isValid()) {
    //     //     Log::error('Invalid file uploaded.');
    //     //     return redirect()->back()->withErrors(['file' => __('Invalid file uploaded.')]);
    //     // }

    //     try {
    //         // Import the file
    //         Excel::import(new EmployeeImport, $file);

    //         // Success response
    //         return redirect()->back()->with('success', __('File imported successfully!'));
    //     } catch (\Exception $e) {
    //         // Log and return error
    //         Log::error('Import Error: ' . $e->getMessage());
    //         return redirect()->back()->withErrors(['file' => __('There was an error importing the file.')]);
    //     }
    // }
    public function getHierarchies(Request $request)
    {
        $response = $this->employeeService->getHierarchies($request);
        $companies = $response['companies'];
        $branches = $response['branches'];
        $departments = $response['departments'];
        $employee = $response['employees'];

        return view('dashboard.employee.hierarchies', compact('companies', 'branches', 'departments', 'employee'));
    }
}
