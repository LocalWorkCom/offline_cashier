<?php

namespace App\Services\HR_Services;

use App\Models\Employee;
use App\Models\InventoryEmployee;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Support\Str;
use App\Models\CompanyPolicy;
use App\Traits\EmployeeStoreTrait;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Dashboard\ChefCuisineCategoryController;
use App\Models\Branch;
use App\Models\CompanyProfileSetting;
use App\Models\Country;
use App\Models\EmployeeAdditionalInfo;
use App\Models\EmployeeContactInfo;
use App\Models\EmployeeLegalDocument;
use App\Models\EmployeeLicense;

class EmployeeService
{
    use EmployeeStoreTrait;
    protected $chefCuisineService;

    public function __construct(ChefCuisineCategoryController $chefCuisineService)
    {
        $this->chefCuisineService = $chefCuisineService;
    }
    public function getAllEmployees($request, $user = null)
    {
        $employees = Employee::with([
            'user',
            'country',
            'department',
            'ethnicBackground',
            'educations.university',
            'employeeBankingInfo.bankName',
            'educations.education_level',
            'educations.filed_of_study',
            'employeeStatus',
            'supervisor',
            'nationality',
            'position',
            'experience',
            'legalDocument',
            'militaryStatus',
            'maritalStatus',
            'employeeContactInfo',
            'vehicle',
            'employeeSalaryDetail',
            'employeePayrollSettings',
            'subDepartment',
            'additionalInfo',
            'license',
            'paymentTypes',
            'paymentFrequencies'
        ]);

        // If not provided, get the authenticated user
        $user = $user ?? auth('employee')->user();

        // Determine if user is privileged (superAdmin, hrmanager, branch manager)
        $isPrivileged = $user->hasRole(['superAdmin', 'HR_Manager', 'Branch_Manager'], 'employee');

        //Restrict access for non-privileged users
        if (!$isPrivileged) {
            // 🔍 Get IDs of employees supervised by this user
            $supervisedEmployeeIds = getSupervisedEmployees($user->id)->pluck('id')->toArray();

            if (empty($supervisedEmployeeIds)) {
                // No supervised employees → return empty result
                return Employee::whereRaw('1=0');
            }

            // Show only those supervised employees
            $employees->whereIn('id', $supervisedEmployeeIds);
        } else {
            // If branch manager or admin → filter by branch
            if ($user->hasRole(['branch manager', 'Branch Manager', 'Branch_Manager'])) {
                $branch_id = getBranchManagerID();
                if ($branch_id) {
                    $employees->where('branch_id', $branch_id);
                }
            }
        }

        // Department filter
        if ($request->filled('department_id')) {
            $employees->where('department_id', $request->department_id);
        }

        // Joining date filter
        if ($request->filled('join_date')) {
            $employees->whereDate('created_at', $request->join_date);
        }

        // Position filter
        if ($request->filled('position_id')) {
            $employees->where('position_id', $request->position_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $employees->where('status', $request->status);
        }

        // Role filter
        if ($request->filled('role')) {
            $roleName = $request->role;
            $employees->whereHas('roles', function ($query) use ($roleName) {
                $query->where('name', $roleName);
            });
        }
        // Leave request number filter
        if ($request->filled('leave_request_number')) {
            $leave_request_number = trim($request->leave_request_number);

            $employees->whereHas('leaveRequests', function ($query) use ($leave_request_number) {
                $query->where('request_num', 'like', $leave_request_number . '%');
            });
        }

        //  Other ID-based filters
        $idFilters = [
            'ethnic_background_id',
            'employee_status_id',
            'branch_id',
            'marital_status_id',
            'military_service_status_id'
        ];

        foreach ($idFilters as $filter) {
            if ($request->filled($filter)) {
                $employees->where($filter, $request->$filter);
            }
        }

        // ✅ Relationship-based filters
        if ($request->filled('university_id')) {
            $employees->whereHas('educations', fn($q) => $q->where('university_id', $request->university_id));
        }

        if ($request->filled('bank_name_id')) {
            $employees->whereHas('employeeBankingInfo', fn($q) => $q->where('bank_name_id', $request->bank_name_id));
        }

        if ($request->filled('education_level_id')) {
            $employees->whereHas('educations', fn($q) => $q->where('education_level_id', $request->education_level_id));
        }

        if ($request->filled('filed_of_study_id')) {
            $employees->whereHas('educations', fn($q) => $q->where('filed_of_study_id', $request->filed_of_study_id));
        }

        // ✅ Employee Code search
        if ($request->filled('employee_id')) {
            $code = preg_replace('/[^0-9]/', '', $request->employee_id);
            $employees->where('employee_code', 'LIKE', '%' . $code . '%');
        }

        // ✅ Name search
        if ($request->filled('name')) {
            $name = trim($request->name);
            $searchTerm = '%' . str_replace(' ', '%', $name) . '%';

            $employees->where(function ($q) use ($searchTerm, $name) {
                $q->whereRaw('LOWER(first_name) LIKE ?', [strtolower($searchTerm)])
                    ->orWhereRaw('LOWER(last_name) LIKE ?', [strtolower($searchTerm)])
                    ->orWhereRaw('LOWER(first_name_en) LIKE ?', [strtolower($searchTerm)])
                    ->orWhereRaw('LOWER(last_name_en) LIKE ?', [strtolower($searchTerm)])
                    ->orWhereRaw('LOWER(CONCAT(first_name, " ", last_name)) LIKE ?', [strtolower('%' . $name . '%')])
                    ->orWhereRaw('LOWER(CONCAT(first_name_en, " ", last_name_en)) LIKE ?', [strtolower('%' . $name . '%')])
                    ->orWhereHas('employeeContactInfo', function ($q) use ($searchTerm) {
                        $q->whereRaw('LOWER(emergency_contact_one_name) LIKE ?', [strtolower($searchTerm)])
                            ->orWhereRaw('LOWER(emergency_contact_two_name) LIKE ?', [strtolower($searchTerm)]);
                    });
            });
        }

        // ✅ Hire date search
        if ($request->filled('hire_date')) {
            $employees->whereDate('hire_date', $request->hire_date);
        }

        // ✅ Phone number search
        if ($request->filled('phone')) {
            $phone = preg_replace('/[^0-9]/', '', $request->phone);
            $employees->where(function ($q) use ($phone) {
                $q->whereRaw('REGEXP_REPLACE(phone_number, "[^0-9]", "") LIKE ?', ['%' . $phone . '%'])
                    ->orWhereHas('employeeContactInfo', function ($q) use ($phone) {
                        $q->whereRaw('REGEXP_REPLACE(emergency_contact_one_phone, "[^0-9]", "") LIKE ?', ['%' . $phone . '%'])
                            ->orWhereRaw('REGEXP_REPLACE(emergency_contact_two_phone, "[^0-9]", "") LIKE ?', ['%' . $phone . '%']);
                    });
            });
        }

        return $employees;
    }


    public function getEmployee($id)
    {
        return Employee::with(
            'user',
            'branch',
            'country',
            'department',
            'ethnicBackground',
            'educations.university',
            // 'employeeBankingInfo.bank_names',
            'employeeBankingInfo.bankName',
            'educations.education_level',
            'educations.filed_of_study',
            'employeeStatus',
            'supervisor',
            'nationality',
            'position',
            'experience',
            'legalDocument',
            'militaryStatus',
            'maritalStatus',
            'employeeSchedules',
            'employeeContactInfo',
            'vehicle',
            'employeeSalaryDetail',
            'employeePayrollSettings',
            'employeeSchedules.shift.details.timetable',
            'roles.permissions',
            'subDepartment',
            'penalties',
            'delays',
            'additionalInfo',
            'warnings',
            'Performance',
            'attendanceRecords',
            'license',
            'paymentTypes',
            'paymentFrequencies'

        )->findOrFail($id);
    }
    public function createEmployee($data, $userId = null, $guard = 'admin')
    {
        DB::beginTransaction();

        // try {
        $lang = $data->header('lang', 'ar');
        // Create related entities
        $user = $this->createUserIfNeeded($data, $guard);
        $vehicle = $this->createVehicleIfNeeded($data);

        // Create main employee record
        $employee = $this->createEmployeeRecord($data, $userId, $user, $vehicle);
        if ($employee->flag == 'inventory') {
            $assignemployee = new InventoryEmployee();
            $assignemployee->employee_id = $employee->id;
            $assignemployee->store_id = $data->warehouse_id;
            $assignemployee->save();
        }
        // Create all related employee records
        $this->createEmployeeRelations($employee, $data, $userId);

        // Handle file uploads
        $this->handleFileUploads($employee, $data);

        // Handle role assignments
        $this->assignRolesAndPermissions($employee, $user, $data, $guard, false,  $data->has('role') ? $data['role'] : null);

        $requiredPermissions = [
            'view profile',
            'update profile',
            'view leave_requests',
            'delete leave_requests',
            'update leave_requests'
        ];

        foreach ($requiredPermissions as $permissionName) {
            // Ensure the permission exists for the "employee" guard
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'employee',
            ]);

            // Assign to the employee if not already assigned
            if (
                $employee && method_exists($employee, 'hasPermissionTo') &&
                !$employee->hasPermissionTo($permissionName, 'employee')
            ) {
                $employee->givePermissionTo($permissionName);
            }
        }

        $current_user_id = auth('employee')->user()->id ?? auth('admin')->user()->id;

        // Get all HR employees in the branch
        $hr_employees = Employee::where('flag', 'hr')
            ->where('branch_id', $employee->branch_id)
            ->get();

        foreach ($hr_employees as $hr_employee) {
            // Check if employee has an FCM token
            if ($hr_employee->device_token != null) {
                $notification = send_push_notification(
                    $hr_employee->device_token, // Device token
                    'لقد انضم/ت إلينا ' . $employee->first_name . ' ' . $employee->last_name . ' في منصب ' . Position::find($employee->position_id)->name_ar . ' بقسم ' . Department::find($employee->department_id)->name_ar . ' بداية من تاريخ ' . $employee->hire_date . '.', // Arabic body
                    $employee->first_name . ' ' . $employee->last_name . ' has joined us as ' . Position::find($employee->position_id)->name_en . ' in ' . Department::find($employee->department_id)->name_en . ' department starting from ' . $employee->hire_date . '.', // English body
                    'انضم موظف جديد!', // Arabic title
                    'New Employee joined!', // English title
                    'hr', // notification_type
                    $hr_employee->id, // receiver_id
                    $current_user_id, // created_by (sender)
                    $employee->id,
                    $lang,
                    10
                );
            }

            // Send latest active policies to the new employee
            if ($employee->device_token != null && $employee->branch_id) {
                $policies = CompanyPolicy::where('company_policies.is_active', true)
                    ->join('branches', 'company_policies.company_id', '=', 'branches.company_profile_setting_id')
                    ->where('branches.id', $employee->branch_id)
                    ->select('company_policies.*')
                    ->get();

                foreach ($policies as $policy) {
                    send_push_notification(
                        $employee->device_token,
                        'يرجى مراجعة سياسة الشركة: ' . $policy->title,
                        'Please review the company policy: ' . $policy->title,
                        'سياسة شركة جديدة',
                        'New Company Policy',
                        'policy_onboarding',
                        $employee->id,
                        $current_user_id,
                        $policy->id,
                        $lang,
                        8,
                        url("/company-policies/{$policy->id}")
                    );
                }
            }
        }

        DB::commit();

        return $employee;
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     throw $e;
        // }
    }


    public function updateEmployee($employee, $data, $userId = null, $guard = 'admin')
    {
        DB::beginTransaction();

        try {
            // Update related entities
            $user = $this->createUserIfNeeded($data, $guard, $employee);
            $vehicle = $this->createVehicleIfNeeded($data, $employee);
            // Update main employee record
            $employee = $this->createEmployeeRecord($data, $userId, $user, $vehicle, $employee);

            // Update all related employee records
            $this->createEmployeeRelations($employee, $data, $userId, true);

            // Handle file uploads
            $this->handleFileUploads($employee, $data, true);
            $this->assignRolesAndPermissions(
                $employee,
                $user,
                $data,
                $guard,
                true,
                $data->has('role') ? $data['role'] : null
            );

            // Handle role assignments
            $notificationResult = null;

            // Get the employee fresh from DB
            $employee = Employee::find($employee->id);

            $fullUrl = url()->current();
            $apiBaseUrl = Str::before($fullUrl, '/api');
            $url = $apiBaseUrl . '/employees/update/' . $employee->id;

            if ($employee) {
                $notificationSent = false;
                $requestId = $employee->id;

                // Build notification payload (include receiver_id and token here)
                $payload = [
                    'user_fcm_token' => $employee->device_token,
                    'receiver_id'    => $employee->id,
                    'description_ar' => "تم تعديل الملف الشخصي للموظف {$employee->first_name}",
                    'description_en' => "employee_updated {$employee->first_name}",
                    'title_ar'       => 'تم تعديل الملف الشخصي بنجاح',
                    'title_en'       => 'Employee updated successfully',
                    'type'           => 'employee',
                    'created_by'     => authActionSave()['by'],
                    'request_id'     => $requestId,
                    'lang'           => app()->getLocale(),
                    'notify_type'    => 10,
                    'url'            => $url,
                ];

                // 🔹 Send to updated employee
                if (!empty($employee->device_token)) {
                    $notification = send_push_notification(...$payload);
                    $notificationSent = $notification !== false;
                }

                // 🔹 Send same notification to all HR_Manager & superAdmin users
                $notifiableUsers = Employee::whereIn('flag', ['hr', 'admin'])
                    ->where('id', '!=', $employee->id)
                    ->get();

                foreach ($notifiableUsers as $notifiable) {
                    if (!empty($notifiable->device_token)) {
                        $payload['user_fcm_token'] = $notifiable->device_token;
                        $payload['receiver_id']    = $notifiable->id;

                        send_push_notification(...$payload);
                    }
                }

                $notificationResult = [
                    'user_id'           => $employee->id,
                    'notification_sent' => $notificationSent,
                ];
            }
            $employee->refresh();
            $employee->load([
                'user',
                'country',
                'department',
                'ethnicBackground',
                'educations.university',
                'employeeBankingInfo.bankName',
                'educations.education_level',
                'educations.filed_of_study',
                'employeeStatus',
                'supervisor',
                'nationality',
                'position',
                'experience',
                'legalDocument',
                'militaryStatus',
                'maritalStatus',
                'employeeSchedules',
                'employeeContactInfo',
                'vehicle',
                'employeeSalaryDetail',
                'employeePayrollSettings',
                'employeeSchedules.shift.details.timetable',
                'roles.permissions',
                'subDepartment',
                'penalties',
                'delays',
                'additionalInfo',
                'warnings',
                'Performance',
                'attendanceRecords',
                'license',
                'paymentTypes',
                'paymentFrequencies'
            ]);


            DB::commit();
            return [
                'employee_updated' => $employee,
                'notification'     => $notificationResult
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteEmployee($id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $employee = Employee::findOrFail($id);

        $employee->delete();
        // User::findOrFail($employee->user_id)->delete();
    }
    public function assign($request)
    {
        return $this->chefCuisineService->store($request);
    }
    public function listCuisineCategories()
    {
        return $this->chefCuisineService->getAllCuisines();
    }

    public function getAllEmployeesByBranch($request, $userId)
    {
        $branchId = $request->input('branch_id');
        $employees = Employee::with('nationality', 'department', 'position', 'supervisor', 'employeeRates', 'attendanceRecords')->whereNot('id', $userId);

        if ($request->input('flag')) {
            $employees->whereIn('flag', array_merge($request->input('flag')));
        }
        if ($request->input('payment_frequency_id')) {
            $employees->where('payment_frequency_id', $request->payment_frequency_id);
        }
        if ($request->input('payment_frequency_id')) {
        $employees->where('payment_frequency_id', $request->payment_frequency_id);
        }
        if ($branchId) {
            $employees->where('branch_id', $branchId);
        }
        return $employees->get();
    }
    public function getHierarchies($request)
    {
        // Collect filters from request
        $companyId     = $request->input('company_id');
        $branchId      = $request->input('branch_id');
        $departmentId  = $request->input('department_id');
        $positionId    = $request->input('position_id');
        $status        = $request->input('status');

        // Companies
        $companies = CompanyProfileSetting::all();

        // Branches (filter by company if set)
        $branches = Branch::when($companyId, function ($q) use ($companyId) {
            $q->where('company_profile_setting_id', $companyId);
        })->get();

        // Departments (filter by branch/company if set)
        $departments = Department::with([
            'branch',
            'branch.company',
            'children.positions.subPositions',
            'children.positions.employees',
            'children.employees',
        ])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($companyId, function ($q) use ($companyId) {
                $q->whereHas('branch.company', function ($sub) use ($companyId) {
                    $sub->where('id', $companyId);
                });
            })
            ->get();

        // Employees (filter by multiple conditions)
        $employees = Employee::with(['position', 'department', 'subDepartment', 'branch.company'])
            ->when($companyId, function ($q) use ($companyId) {
                $q->whereHas('branch.company', function ($sub) use ($companyId) {
                    $sub->where('id', $companyId);
                });
            })
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId)
                    ->orWhere('sub_department_id', $departmentId);
            })
            ->when($positionId, function ($q) use ($positionId) {
                $q->where('position_id', $positionId);
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->get()
            ->map(fn($emp) => [
                'id' => $emp->id,
                'name' => $emp->first_name,
                'manager_id' => $emp->manager_id,
                'position' => $emp->position?->name ?? '',
                'department_id' => $emp->department_id ?? 0,
                'sub_department_id' => $emp->sub_department_id ?? 0,
                'department_name' => $emp->department?->name_en,
                'sub_department_name' => $emp->subDepartment?->name_en,
                'start_date' => $emp->start_date,
                'branch_id' => $emp->branch_id,
                'position_id' => $emp->position_id ?? 0,
                'status' => $emp->status,
                'company_id' => $emp->branch->company->id ?? null
            ]);

        return [
            'companies' => $companies,
            'branches' => $branches,
            'departments' => $departments,
            'employees' => $employees
        ];
    }

    public function getEmployeeRoleAndPermission($employeeId)
    {
        $employee = Employee::with('roles.permissions')->find($employeeId);

        if (!$employee) {
            return null; // or throw an exception, or return an error response
        }

        return [
            'roles' => $employee->roles,
            'permissions' => $employee->getAllPermissions()
        ];
    }

    public function updateEmployeeProfile($employee, $request)
    {
        if (!$employee) {
            return null;
        }

        $country = Country::where('phone_code', $request->country_code)->first();

        // 🧩 Update basic employee data
        $fullName = trim($request->full_name);
        $parts = explode(' ', $fullName, 2);

        $firstName = $parts[0] ?? null;
        $lastName  = $parts[1] ?? null;
        $employee->update([
            'first_name' => $firstName ?: ($request->first_name ?: $employee->first_name),
            'last_name'  => $lastName  ?: ($request->last_name  ?: $employee->last_name),
            // 'first_name' => $request->first_name ?? $employee->first_name,
            // 'last_name' => $request->last_name ?? $employee->last_name,
            'first_name_en' => $request->first_name_en ?? $employee->first_name_en,
            'last_name_en' => $request->last_name_en ?? $employee->last_name_en,
            'email' => $request->email ?? $employee->email,
            'country_code' => $request->country_code ?? $employee->country_code,
            'country_id' => $request->country_id ?? $employee->country_id,
            'phone_number' => $request->phone_number ?? $employee->phone_number,
            'gender' => $request->gender ?? $employee->gender,
            'birth_date' => $request->birth_date ?? $employee->birth_date,
            'national_id' => $request->national_id ?? $employee->national_id,
            'address' => $request->current_address ?? $employee->address,
            'nationality_id' => $request->nationality_id ?? $employee->nationality_id,
            'marital_status_id' => $request->marital_status_id ?? $employee->marital_status_id,
            
        ]);

        // 🖼️ Upload profile image if exists
        if ($request->hasFile('image')) {
            UploadFile('images/employees', 'image', $employee, $request->file('image'));
        }

        // 🚘 License Info
        if (
            $request->hasFile('license_copy') ||
            $request->hasFile('egypt_license_copy') ||
            $request->hasFile('kuwait_license_copy')
        ) {
            $this->updateOrCreateRelation($employee, 'license', EmployeeLicense::class, [
                'has_license' => 1,
                'license_country' => $request->license_country,
                'license_expiry_date' => $request->license_expiry,
                'has_kuwaiti_license' => $employee->kuwait_license ?? 0,
                'kuwaiti_license_expiry_date' => $employee->kuwait_license_expiry,
                'has_egyptian_license' => $employee->egypt_license ?? 0,
                'egyptian_license_expiry_date' => $employee->egypt_license_expiry,
            ], true);
        }

        // $license = $employee->license?->first();
        $license = $employee->license;

        // 📎 Upload license copies
        if ($request->hasFile('license_copy')) {
            UploadFile('images/employees/license', 'license_copy', $license, $request->file('license_copy'));
        }
        if ($request->hasFile('kuwait_license_copy')) {
            UploadFile('images/employees/license/kuwaiti', 'kuwait_license_copy', $license, $request->file('kuwait_license_copy'));
        }
        if ($request->hasFile('egypt_license_copy')) {
            UploadFile('images/employees/license/egypt', 'egypt_license_copy', $license, $request->file('egypt_license_copy'));
        }

        // 📄 Additional Info
        $this->updateOrCreateRelation($employee, 'additionalInfo', EmployeeAdditionalInfo::class, array_filter([
            'additional_description' => $request->passport_number,
            'referral_source' => $request->hear_about_us,
            'languages_spoken' => $request->Languages_Spoken,
            'visa_information' => $request->Visa_Information,
            'is_residency_transferable' => $request->residency_transferable,
            'tasks_and_instructions' => $request->tasks_and_instructions,
            'criminal_record_file' => $request->criminal_record_file,
            'drug_test_report_file' => $request->drug_test_report_file,
        ], fn($value) => !is_null($value)), true);

        // 🪪 Legal Document
        if (!empty($request->passport_number)) {
            $this->updateOrCreateRelation($employee, 'legalDocument', EmployeeLegalDocument::class, [
                'passport_number' => $request->passport_number,
                'passport_expiry_date' => $request->passport_expiry_date,
                'work_permit_expiry_date' => $request->work_permit_expiry_date,
                'residency_expiry_date' => $request->residency_expiry_date,
            ], true);
        }

        // ☎️ Contact Info
        $existingContact = $employee->employeeContactInfo->first();
        $this->updateOrCreateRelation($employee, 'employeeContactInfo', EmployeeContactInfo::class, [
            'whatsapp_number' => $request->whatsapp_number ?? $existingContact?->whatsapp_number,
            'current_address' => $request->current_address ?? $existingContact?->current_address,
            'home_country_address' => $request->home_country_address ?? $existingContact?->home_country_address,
            'emergency_contact_one_name' => $request->emergency_contact_one_name ?? $existingContact?->emergency_contact_one_name,
            'emergency_contact_one_relation' => $request->emergency_contact_one_relation ?? $existingContact?->emergency_contact_one_relation,
            'emergency_contact_one_phone' => $request->emergency_contact_one_phone ?? $existingContact?->emergency_contact_one_phone,
            'emergency_contact_two_name' => $request->emergency_contact_two_name ?? $existingContact?->emergency_contact_two_name,
            'emergency_contact_two_relation' => $request->emergency_contact_two_relation ?? $existingContact?->emergency_contact_two_relation,
            'emergency_contact_two_phone' => $request->emergency_contact_two_phone ?? $existingContact?->emergency_contact_two_phone,
        ], true);
        $document = $employee->legalDocument?->first();

        // 🧾 File uploads related to documents
        if ($request->hasFile('work_permit')) {
            UploadFile('images/employees/work_permit', 'work_permit', $document, $request->file('work_permit'));
        }
        if ($request->hasFile('residency_permit')) {
            UploadFile('images/employees/residency_permit', 'residency_permit', $document, $request->file('residency_permit'));
        }
        if ($request->hasFile('passport_copy')) {
            UploadFile('images/employees/passport', 'passport_copy', $document, $request->file('passport_copy'));
        }

        $employee->refresh();
        $employee->load(
            'user',
            'branch',
            'country',
            'department',
            'ethnicBackground',
            'educations.university',
            // 'employeeBankingInfo.bank_names',
            'employeeBankingInfo.bankName',
            'educations.education_level',
            'educations.filed_of_study',
            'employeeStatus',
            'supervisor',
            'nationality',
            'position',
            'experience',
            'legalDocument',
            'militaryStatus',
            'maritalStatus',
            'employeeSchedules',
            'employeeContactInfo',
            'vehicle',
            'employeeSalaryDetail',
            'employeePayrollSettings',
            'employeeSchedules.shift.details.timetable',
            'roles.permissions',
            'subDepartment',
            'penalties',
            'delays',
            'additionalInfo',
            'warnings',
            'Performance',
            'attendanceRecords',
            'license',
            'paymentTypes',
            'paymentFrequencies'

        );
        return ['employee_updated' => $employee];
    }

    public function getMyTeamEmployees($supervisorId)
    {
        return Employee::with('position')
            ->where('supervisor_id', $supervisorId)
            ->get();
    }
}
