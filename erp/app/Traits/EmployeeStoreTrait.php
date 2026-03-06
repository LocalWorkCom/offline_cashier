<?php

namespace App\Traits;

use App\Models\Branch;
use App\Models\ChefCuisineCategory;
use App\Models\Country;
use App\Models\Employee;
use App\Models\EmployeeAdditionalInfo;
use App\Models\EmployeeBankingInfo;
use App\Models\EmployeeContactInfo;
use App\Models\EmployeeEducation;
use App\Models\EmployeeExperience;
use App\Models\EmployeeLegalDocument;
use App\Models\EmployeeLicense;
use App\Models\EmployeeSchedule;
use App\Models\EmployeeSalaryDetail;
use App\Models\EmployeePayrollSetting;
use App\Models\EmployeeStatus;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\KitchenServices\ChefCuisineCategoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

trait EmployeeStoreTrait
{
    protected $chefCuisineService;

    public function setChefCuisineService(ChefCuisineCategoryService $chefCuisineService)
    {
        $this->chefCuisineService = $chefCuisineService;
    }
    protected function createUserIfNeeded($data, $guard, $employee = null)
    {
        $adminFlags = ['branch manager', 'kitchen manager', 'officer', 'Head Board', 'admin', 'super_admin'];
        if (in_array($data['flag'], $adminFlags)) {

            if ($employee && $employee->user_id) {
                // Update existing user
                $user = User::find($employee->user_id);
                $user->update([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'flag' => 'admin',

                ]);
                return $user;
            } else {
                // Create new user
                return User::create([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                    'password' => Hash::make('123456'),
                    'phone' => $data['phone'],
                    'flag' => 'admin',
                ]);
            }
        }

        return null;
    }

    protected function createVehicleIfNeeded($data, $employee = null)
    {
        if ($data['flag'] === 'driver' && $data['vehicle_number'] != null) {
            if ($employee && $employee->vehicle_id) {
                // Update existing vehicle
                $vehicle = Vehicle::find($employee->vehicle_id);
                $vehicle->update([
                    'vehicle_type' => $data['vehicle_type'],
                    'license' => $data['vehicle_number'],
                ]);
                return $vehicle;
            } else {
                // Create new vehicle
                return Vehicle::create([
                    'vehicle_type' => $data['vehicle_type'],
                    'license' => $data['vehicle_number'],
                ]);
            }
        }

        // If employee is no longer a driver, delete the vehicle if it exists
        if ($employee && $employee->vehicle_id && $data['flag'] !== 'driver') {
            Vehicle::where('id', $employee->vehicle_id)->delete();
            return null;
        }

        return null;
    }

    protected function createEmployeeRecord($data, $userId, $user, $vehicle, $employee = null)
    {
        $country = Country::where('phone_code', $data['country_code'])->first();
        $createdBy = $userId ?? null;



        if ($employee) {
            // Update existing employee

            $employee->update([
                'employee_code' => $data['employee_code'] ?? $employee->employee_code,
                'user_id' => $user ? $user->id : null,
                'first_name' => $data['first_name'] ?? $employee->first_name,
                'last_name' => $data['last_name'] ?? $employee->last_name,
                'first_name_en' => $data['first_name_en'] ?? $employee->first_name_en,
                'last_name_en' => $data['last_name_en'] ?? $employee->last_name_en,
                'email' => $data['email'] ?? $employee->email,
                'country_code' => $data['country_code'] ?? $employee->country_code,
                'country_id' => $country->id ?? $employee->country_id,
                'city_id' => $data['city_id'] ?? $employee->city_id,
                'area_id' => $data['area_id'] ?? $employee->area_id,
                'phone_number' => $data['phone'] ?? $employee->phone_number,
                'gender' => $data['gender'] ?? $employee->gender,
                'birth_date' => $data['birth_date'] ?? $employee->birth_date,
                'national_id' => $data['national_id'] ?? $employee->national_id,
                'blood_group' => $data['blood_group'] ?? $employee->blood_group,
                'address' => $data['current_address'] ?? $employee->address,
                'nationality_id' => $data['nationality_id'] ?? $employee->nationality_id,
                'department_id' => $data['department_id'] ?? $employee->department_id,
                'position_id' => $data['position_id'] ?? $employee->position_id,
                'flag' => $data['flag'] ?? $employee->flag,
                'supervisor_id' => $data['supervisor_id'] ?? $employee->supervisor_id,
                'hire_date' => $data['hire_date'] ?? $employee->hire_date,
                'work_start_date' => $data['work_start_date'] ?? $employee->work_start_date,
                'salary' => $data['salary'] ?? $employee->salary,
                'assurance_salary' => $data['assurance_salary'] ?? $employee->assurance_salary,
                'assurance_number' => $data['assurance_number'] ?? $employee->assurance_number,
                'bank_account' => $data['bank_account_one'] ?? $employee->bank_account,
                'employment_type' => $data['employment_type'] ?? $employee->employment_type,
                // 'status' => $data['status'] ?? $employee->status,
                'ethnic_background_id' => $data['ethnic_background_id'] ?? $employee->ethnic_background_id,
                'employee_status_id' => $data['employee_status_id'] ?? $employee->employee_status_id,
                'status' => str_replace(' ', '_', strtoupper(EmployeeStatus::where('id',$data['employee_status_id'])->first()->name_en)) ?? "active",
                'status_reason' => $data['status_reason'] ?? $employee->status_reason,
                'payment_type_id' => $data['payment_type_id'] ?? $employee->payment_type_id,
                'payment_frequency_id' => $data['payment_frequency_id'] ?? $employee->payment_frequency_id,
                'notes' => $data['notes'] ?? $employee->notes,
                'is_biometric' => $data['is_biometric'] ?? $employee->is_biometric,
                'biometric_id' => $data['biometric_id'] ?? $employee->biometric_id,
                'vehicle_id' => $vehicle->id ?? $employee->vehicle_id,
                'branch_id' => $data['branch_id'] ?? $employee->branch_id,
                'marital_status_id' => $data['marital_status_id'] ?? $employee->marital_status_id,
                'military_service_status_id' => $data['military_service_status_id'] ?? $employee->military_service_status_id,
            ]);
            return $employee;
        } else {
            // Create new employee
            $employee = new Employee();
            $employee->employee_code = $data['employee_code'] ?? null;
            $employee->user_id = $user ? $user->id : null;
            $employee->first_name = $data['first_name'] ?? null;
            $employee->last_name = $data['last_name'] ?? null;
            $employee->first_name_en = $data['first_name_en'] ?? null;
            $employee->last_name_en = $data['last_name_en'] ?? null;
            $employee->email = $data['email'] ?? null;
            $employee->job_type_id = $data['job_type_id'] ?? null;
            $employee->country_code = $data['country_code'] ?? null;
            $employee->country_id = $country->id ?? null;
            $employee->city_id = $data['city_id'] ?? null;
            $employee->area_id = $data['area_id'] ?? null;
            $employee->phone_number = $data['phone'] ?? null;
            $employee->gender = $data['gender'] ?? null;
            $employee->birth_date = $data['birth_date'] ?? null;
            $employee->national_id = $data['national_id'] ?? null;
            $employee->blood_group = $data['blood_group'] ?? null;
            $employee->address = $data['current_address'] ?? null;
            $employee->nationality_id = $data['nationality_id'] ?? null;
            $employee->department_id = $data['department_id'] ?? null;
            $employee->position_id = $data['position_id'] ?? null;
            $employee->flag = $data['flag'] ?? null;
            $employee->supervisor_id = $data['supervisor_id'] ?? null;
            $employee->hire_date = $data['hire_date'] ?? null;
            $employee->work_start_date = $data['work_start_date'] ?? null;
            $employee->salary = $data['salary'] ?? null;
            $employee->assurance_salary = $data['assurance_salary'] ?? null;
            $employee->assurance_number = $data['assurance_number'] ?? null;
            $employee->bank_account = $data['bank_account_one'] ?? null;
            $employee->employment_type = $data['employment_type'] ?? null;
            // $employee->status = $data['status'] ?? null;
            $employee->employee_status_id = $data['employee_status_id'] ?? null;
            $employee->status = str_replace(' ', '_', strtolower(EmployeeStatus::where('id',$data['employee_status_id'])->first()->name_en)) ?? "active";
            $employee->status_reason = $data['status_reason'] ?? null;
            $employee->payment_type_id = $data['payment_type_id'] ?? null;
            $employee->payment_frequency_id = $data['payment_frequency_id'] ?? null;
            $employee->notes = $data['notes'] ?? null;
            $employee->is_biometric = $data['is_biometric'] ?? null;
            $employee->biometric_id = $data['biometric_id'] ?? null;
            $employee->password = Hash::make('123456');
            $employee->vehicle_id = $vehicle->id ?? null;
            $employee->branch_id = $data['branch_id'] ?? null;
            $employee->marital_status_id = $data['marital_status_id'] ?? null;
            $employee->military_service_status_id = $data['military_service_status_id'] ?? null;
            $employee->created_by = $createdBy;
            $employee->save();
            return $employee;
        }
    }

    protected function createEmployeeRelations($employee, $data, $createdBy, $isUpdate = false)
    {

        // Contact Info
        $this->updateOrCreateRelation($employee, 'employeeContactInfo', EmployeeContactInfo::class, [
            'facebook_link' => $data['facebook_link'] ? $data['facebook_link'] : $employee->employeeContactInfo->first()?->facebook_link  ?? null,
            'instagram_link' => $data['instagram_link'] ? $data['instagram_link'] : $employee->employeeContactInfo->first()?->instagram_link   ?? null,
            'twitter_link' => $data['twitter_link'] ? $data['twitter_link']  : $employee->employeeContactInfo->first()?->twitter_link  ?? null,
            'whatsapp_number' => $data['whatsapp_number'] ? $data['whatsapp_number'] : $employee->employeeContactInfo->first()?->whatsapp_number  ?? null,
            'current_address' => $data['current_address'] ? $data['current_address'] : $employee->employeeContactInfo->first()?->current_address  ?? null,
            'home_country_address' => $data['home_country_address'] ? $data['home_country_address'] : $employee->employeeContactInfo->first()?->home_country_address  ?? null,
            'emergency_contact_one_name' => $data['emergency_contact_one_name'] ? $data['emergency_contact_one_name']  : $employee->employeeContactInfo->first()?->emergency_contact_one_name ?? null,
            'emergency_contact_one_relation' => $data['emergency_contact_one_relation'] ? $data['emergency_contact_one_relation']  : $employee->employeeContactInfo->first()?->emergency_contact_one_relation ?? null,
            'emergency_contact_one_phone' => $data['emergency_contact_one_phone'] ? $data['emergency_contact_one_phone']  : $employee->employeeContactInfo->first()?->emergency_contact_one_phone ?? null,
            'emergency_contact_two_name' => $data['emergency_contact_two_name']  ? $data['emergency_contact_two_name']  : $employee->employeeContactInfo->first()?->emergency_contact_two_name ?? null,
            'emergency_contact_two_relation' => $data['emergency_contact_two_relation'] ? $data['emergency_contact_two_relation']  : $employee->employeeContactInfo->first()?->emergency_contact_two_relation ?? null,
            'emergency_contact_two_phone' => $data['emergency_contact_two_phone'] ? $data['emergency_contact_two_phone']  : $employee->employeeContactInfo->first()?->emergency_contact_two_phone ?? null,
            // 'created_by' => $createdBy,
        ], $isUpdate);

        // Schedule
        // dd($employee->employeeSchedules->count());
        if (isset($data['shift_id']) && $data['shift_id'] != null) {
            $this->updateOrCreateRelation($employee, 'employeeSchedules', EmployeeSchedule::class, [
                'shift_id' => $data['shift_id'] ?? null,
                'start_date' => $data['schedule_start_date'] ?? null,
                'end_date' => $data['schedule_end_date'] ?? null,
                // 'created_by' => $createdBy,
            ], $isUpdate);
        }

        // Experience
        if (isset($data['previous_position']) && $data['previous_position'] != null) {
            $this->updateOrCreateRelation($employee, 'experience', EmployeeExperience::class, [
                'previous_position' => $data['previous_position'] ?? null,
                'previous_salary' => $data['previous_salary'] ?? null,
                'expected_salary' => $data['expected_salary'] ?? null,
                'num_experience_years' => $data['num_experience_years'] ?? null,
                // 'created_by' => $createdBy,
            ], $isUpdate);
        }

        // Legal Documents
        if (isset($data['passport_number']) && $data['passport_number'] != null) {
            $this->updateOrCreateRelation($employee, 'legalDocument', EmployeeLegalDocument::class, [
                'passport_number' => $data['passport_number'] ?? null,
                'passport_expiry_date' => $data['passport_expiry_date'] ?? null,
                'work_permit_expiry_date' => $data['work_permit_expiry_date'] ?? null,
                'residency_expiry_date' => $data['residency_expiry_date'] ?? null,
                // 'created_by' => $createdBy,
            ], $isUpdate);
        }

        // Education
        if (isset($data['filed_of_study_id']) && $data['filed_of_study_id'] != null) {
            $this->updateOrCreateRelation($employee, 'educations', EmployeeEducation::class, [
                'filed_of_study_id' => $data['filed_of_study_id'] ?? null,
                'education_level_id' => $data['education_level_id'] ?? null,
                'university_id' => $data['university_id'] ?? null,
                'graduation_year' => $data['graduation_year'] ?? null,
            ], $isUpdate);
        }

        // Banking Info
        if (isset($data['banks']) && $data['banks'] != null) {
            $this->updateBankingInfo($employee, $data, $isUpdate);
        }

        // Additional Info
        if (isset($data['hear_about_us']) && $data['hear_about_us'] != null) {
            $this->updateOrCreateRelation($employee, 'additionalInfo', EmployeeAdditionalInfo::class, [
                'additional_description' => $data['passport_number'] ?? null,
                'referral_source' => $data['hear_about_us'] ?? null,
                'languages_spoken' => $data['Languages_Spoken'] ?? null,
                'visa_information' => $data['Visa_Information'] ?? null,
                'is_residency_transferable' => $data['residency_transferable'] ?? null,
                'tasks_and_instructions' => $data['drug_test_report_file'] ?? null,
                'criminal_record_file' => $data['criminal_record_file'] ?? null,
                'drug_test_report_file' => $data['drug_test_report_file'] ?? null,
            ], $isUpdate);
        }

        // salary Info
        if (isset($data['salary']) && $data['salary'] != null) {
            $this->updateOrCreateRelation($employee, 'salaryInfo', EmployeeSalaryDetail::class, [
                'base_salary' => $data['salary'] ?? 0,
                'salary_work_permit' => $data['salary_work_permit'] ?? 0,
                'annual_salary' => $data['annual_salary'] ?? 0,
                'commission_type' => $data['commission_type'] ?? null,
                'is_allowance' => $data['is_allowance'] ?? 0,
                'is_bonus' => $data['is_bonus'] ?? 0,
                'is_commision' => $data['is_commision'] ?? 0,
                'currency_id' => $data['currency_id'] ?? null
            ], $isUpdate);

            $this->updateOrCreateRelation($employee, 'salaryInfo', EmployeePayrollSetting::class, [
                'salary_value' => $data['salary'] ?? null,
                'effective_from' => $data['work_start_date'] ?? null,
                'effective_to' => $data['end_salary_date'] ?? null,
                'payment_frequency_id' => $data['payment_frequency_id'] ?? null
            ], $isUpdate);
        }

        // License
        if ($data['driving_license'] == 1 || $data['kuwait_license'] == 1 || $data['egypt_license'] == 1) {
            $this->updateOrCreateRelation($employee, 'license', EmployeeLicense::class, [
                'has_license' => $data['driving_license'] ?? null,
                'license_country' => $data['license_country'] ?? null,
                'license_expiry_date' => $data['license_expiry'] ?? null,
                'has_kuwaiti_license' => $data['kuwait_license'] ?? null,
                'kuwaiti_license_expiry_date' => $data['kuwait_license_expiry'] ?? null,
                'has_egyptian_license' => $data['egypt_license'] ?? null,
                'egyptian_license_expiry_date' => $data['egypt_license_expiry'] ?? null,
            ], $isUpdate);
        } elseif ($isUpdate && $employee->license) {
            $employee->license->delete();
        }

        // Chef specific handling
        if ($data['flag'] === 'Head Chef') {
            $this->handleChefCreation($employee, $data, $isUpdate);
        }
    }

    protected function updateOrCreateRelation($employee, $relation, $model, $attributes, $isUpdate = false)
    {
        if ($isUpdate && $employee->relation) {
            $employee->$relation->update($attributes);
        } else {
            $model::updateOrCreate(['employee_id' => $employee->id], array_merge($attributes, ['employee_id' => $employee->id]));
        }
    }

    protected function updateBankingInfo($employee, $data, $isUpdate = false)
    {
        if ($isUpdate) {
            // First delete all existing banking info for this employee
            EmployeeBankingInfo::where('employee_id', $employee->id)->delete();
        }

        // Then create new records
        foreach ($data['banks'] ?? [] as $bank) {
            EmployeeBankingInfo::create([
                'employee_id' => $employee->id,
                'bank_name_id' => $bank['bank_name_id'],
                'bank_account_number' => $bank['bank_account_number'],
                'bank_iban' => $bank['bank_iban'] ?? null,
                'is_payroll_account' => $bank['is_payroll_account'] ?? 0,
            ]);
        }
    }

    protected function handleFileUploads($employee, $data, $isUpdate = false)
    {
        // dd($data)
        // Employee image
        if (isset($data['image']) && $data->hasFile('image')) {
            UploadFile('images/employees', 'image', $employee, $data['image']);
        }
        if (isset($data['contract']) && $data->hasFile('contract')) {
            UploadFile('images/employees', 'contract_file', $employee, $data['contract']);
        }
        // Legal documents
        $document = $employee->legalDocument?->first();
        if ($document) {
            if ((isset($data['passport_copy']) && $data->hasFile('passport_copy')) || (isset($data['national_id_photo']) && $data->hasFile('national_id_photo'))) {

                UploadFile('files/employees/passports', 'passport_copy', $document, $data['passport_copy'] ?? $data['national_id_photo']);
            }
            if (isset($data['work_permit']) && $data->hasFile('work_permit')) {

                UploadFile('files/employees/work_permits', 'work_permit', $document, $data['work_permit']);
            }
            if (isset($data['residency_permit']) && $data->hasFile('residency_permit')) {

                UploadFile('files/employees/residency_permits', 'residency_permit', $document, $data['residency_permit']);
            }
        }
        // Experience documents
        $experience = $employee->experience?->first();

        if ($experience && isset($data['resume']) && $data->hasFile('resume')) {
            UploadFile('files/employees/resumes', 'resume', $experience, $data['resume']);
        }

        // Education documents
        $education = $employee->education?->first();

        if ($education) {
            if (isset($data['degree_certificate']) && $data->hasFile('degree_certificate')) {
                UploadFile('files/employees/degree_certificates', 'degree_certificate', $education, $data['degree_certificate']);
            }
            if (isset($data['additional_certifications']) && $data->hasFile('additional_certifications')) {
                UploadFile('files/employees/additional_certifications', 'additional_certifications', $education, $data['additional_certifications']);
            }
            if (isset($data['certification_documents']) && $data->hasFile('certification_documents')) {
                UploadFile('files/employees/certification_documents', 'certification_documents', $education, $data['certification_documents']);
            }
        }

        // Additional info documents
        $additionalInfo = $employee->additionalInfo?->first();
        if ($additionalInfo) {
            if (isset($data['Drug_Test_Report']) && $data->hasFile('Drug_Test_Report')) {
                UploadFile('images/employees/DrugTest', 'drug_test_report_file', $additionalInfo, $data['Drug_Test_Report']);
            }
            if (isset($data['Criminal_Record']) && $data->hasFile('Criminal_Record')) {
                UploadFile('images/employees/CriminalRecord', 'criminal_record_file', $additionalInfo, $data['Criminal_Record']);
            }
        }

        // License documents
        $license = $employee->license?->first();
        if ($license) {
            // dd(isset($data['license_copy']));
            if (isset($data['license_copy']) && $data->hasFile('license_copy')) {
                UploadFile('images/employees/license', 'license_copy', $license, $data['license_copy']);
            }
            if (isset($data['kuwait_license_copy']) && $data->hasFile('kuwait_license_copy')) {
                UploadFile('images/employees/license/kuwaiti', 'kuwaiti_license_copy', $license, $data['kuwait_license_copy']);
            }
            if (isset($data['egypt_license_copy']) && $data->hasFile('egypt_license_copy')) {
                UploadFile('images/employees/license/egypt', 'egyptian_license_copy', $license, $data['egypt_license_copy']);
            }
        }
    }

    protected function assignRolesAndPermissions($employee, $user, $data, $guard, $isUpdate = false, $role = null)
    {
        $admin = $guard === 'employee' ? $employee : $user;

        if (!$admin) {
            return;
        }

        // If a specific role was passed (e.g., from $data['role']), assign it directly
        if ($role) {
            $assignedRole = Role::firstOrCreate([
                'name' => $role,
                'guard_name' => $guard
            ]);

            if ($isUpdate) {
                $admin->syncRoles([$assignedRole->name]);
                $action = 'update_role';
            } else {
                $action = 'assign_role';

                $admin->assignRole($assignedRole);
            }
            logPermissionsAndRoleChanges($action, [
                'employee_id' => $admin->id,
                'role_id'     => $assignedRole->id,
                'extra_data'  => [
                    'role_name'   => $assignedRole->name,
                    'assigned_by' => auth('employee')->user()->name ?? auth('admin')->user()->name,
                    'is_update'   => $isUpdate,
                ],
            ]);
            return; // Skip default flag-role logic
        }

        // Default behavior: assign based on flag
        $roles = [
            'branch manager' => ['name' => 'Branch Manager', 'callback' => fn() => $this->handleBranchManagerAssignment($employee, $data)],
            'kitchen manager' => ['name' => 'Kitchen Manager', 'callback' => fn() => $this->handleKitchenManagerAssignment($employee)],
            'Head Board' => ['name' => 'Head Board', 'callback' => fn() => $this->handleHeadBoardAssignment($admin, $data)],
            'officer' => ['name' => 'officer'],
            'hr' => ['name' => 'HR_Employee'],
            'inventory' => ['name' => 'Inventory_Employee'],
            'finance' => ['name' => 'Finance_Employee'],
            'admin' => ['name' => 'superAdmin'],
        ];

        if (array_key_exists($data['flag'], $roles)) {
            $role = Role::firstOrCreate([
                'name' => $roles[$data['flag']]['name'],
                'guard_name' => $guard
            ]);

            if ($isUpdate) {
                $admin->syncRoles([$role->name]);
            } else {
                $admin->assignRole($role);
            }

            if (isset($roles[$data['flag']]['callback'])) {
                $roles[$data['flag']]['callback']();
            }
        } elseif ($isUpdate) {
            // Remove all roles if the flag doesn't match any role
            $admin->syncRoles([]);
        }
    }


    protected function handleBranchManagerAssignment($employee, $data)
    {
        // Unassign current manager from branches
        Branch::where('employee_id', $employee->id)
            ->update(['employee_id' => null]);

        // Assign new manager if branch is specified
        if (isset($data['branch_id'])) {
            $branch = Branch::find($data['branch_id']);

            if ($branch) {
                // Unassign current manager if different
                if ($branch->employee_id && $branch->employee_id !== $employee->id) {
                    Employee::where('id', $branch->employee_id)
                        ->update(['branch_id' => null]);
                }

                $branch->employee_id = $employee->id;
                $branch->save();
            }
        }
    }

    protected function handleKitchenManagerAssignment($employee)
    {
        // Unassign any existing kitchen manager (excluding current employee)
        Employee::where('flag', 'kitchen manager')
            ->where('id', '!=', $employee->id)
            ->update(['flag' => 'employee']);
    }

    protected function handleHeadBoardAssignment($admin, $data)
    {
        if (isset($data['permissions_ids'])) {
            $permissionsIds = array_keys(array_filter($data['permissions_ids']));
            $admin->syncPermissions($permissionsIds);
        }
    }

    protected function handleChefCreation($employee, $data, $isUpdate = false)
    {
        if (!isset($data['cuisine_categories'])) {
            // For updates, we might want to clear existing assignments if no categories are provided
            if ($isUpdate) {
                $this->chefCuisineService->clearAssignments($employee->id);
            }
            return;
        }

        // Ensure service is available
        if (!$this->chefCuisineService) {
            $this->chefCuisineService = app(ChefCuisineCategoryService::class);
        }

        // Prepare request data
        $requestData = [
            'employee_id' => $employee->id,
            'categories' => []
        ];

        foreach ($data['cuisine_categories'] as $category) {
            $requestData['categories'][] = [
                'category_id' => is_array($category) ? $category['category_id'] : $category,
                'dishes' => is_array($category) ? ($category['dishes'] ?? [-1]) : [-1]
            ];
        }

        $request = new Request($requestData);

        // For updates, first clear existing assignments
        if ($isUpdate) {
            $this->clearAssignments($employee->id);
        }

        return $this->chefCuisineService->store($request);
    }
    public function clearAssignments($id)
    {
        try {
            $assignment = ChefCuisineCategory::where('employee_id', $id)->get();
            $assignment->each->delete();

            return [
                'success' => true,
                'message' => 'Cuisine unassigned successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }


}
