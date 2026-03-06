<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Country;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    protected function prepareForValidation()
    {
        // Check if this is an update (route has 'id' parameter)
        if ($this->route('id')) {
            $employee = Employee::find($this->route('id'));

            if (!$employee) {
                $lang = $this->header('lang', 'en');
                app()->setLocale($lang);

                $message = $lang === 'ar'
                    ? 'الموظف غير موجود'
                    : 'Employee not found';

                // Immediately return 404 using your same response format
                throw new HttpResponseException(
                    respondError($message, 404)
                );
            }
        }
    }
    public function rules()
    {
        $employeeId = $this->route('id') ?? auth('employee')->id();
        $isAuthEmployee = $this->route('id') === null;


        $branchCountryId = Branch::find($this->branch_id)?->country_id;
        $employeeCountryId = \App\Models\Country::where('id', $this->country_code)
            ->orWhere('phone_code', $this->country_code)
            ->value('id');
        return [
            'employee_code' => [
                'required',
                'string',
                // Rule::unique('employees')->ignore($employeeId),
                Rule::unique('employees', 'employee_code')->ignore($employeeId, 'id'),
            ],
            'email' => [
                'required',
                'email',
                // Rule::unique('employees')->ignore($employeeId),
                // Rule::unique('users')->ignore($employeeId),
                Rule::unique('employees', 'email')->ignore($employeeId, 'id'),
                Rule::unique('users', 'email')->ignore($employeeId, 'id'),
            ],
            'national_id' => [
                'nullable',
                'numeric',
                'min:1',
                Rule::unique('employees')->ignore($employeeId),
            ],
            'assurance_number' => [
                'nullable',
                'numeric',
                'min:1',
                Rule::unique('employees')->ignore($employeeId),
            ],
            'biometric_id' => [
                'nullable',
                'numeric',
                'min:1',
                Rule::unique('employees')->ignore($employeeId),
            ],
            'employee_status_id' => [
                'nullable',
                'numeric',
                'min:1',
                Rule::unique('employees')->ignore($employeeId),
            ],
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'first_name_en' => 'nullable|string',
            'last_name_en' => 'nullable|string',
            'permissions_ids' => 'nullable|array',
            'country_code' => [
                'required',
                'string',
                function ($fail) use ($employeeCountryId) {
                    if (!$employeeCountryId) {
                        $fail(__('validation.exists')); // Or custom error message
                    }
                },
            ],
            'branch_id' => 'required_unless:flag,kitchen manager,officer,Head Board,admin',
            'phone' => [
                'required',
                'numeric',
                Rule::unique('employees', 'phone_number')
                    ->where(function ($query) {
                        return $query->where('country_code', $this->country_code);
                    })
                    ->ignore($employeeId),
                function ($attribute, $value, $fail) {
                    if (!empty($this->country_code)) {
                        $country = Country::where('phone_code', $this->country_code)->first();
                        if ($country && strlen($value) != $country->length) {
                            $fail(__('validation.custom.phone.length', [
                                'attribute' => __('auth.phone'),
                                'length' => $country->length
                            ]));
                        }
                    }
                },
            ],

            'gender' => 'nullable|string',
            'role' => 'nullable|string',

            'cuisine_categories' => 'required_if:flag,Head Chef',
            'flag' => 'required',
            'birth_date' => 'nullable|date',
            'marital_status' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'address_en' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'nationality_id' => 'nullable|exists:nationalities,id',
            'department_id' => 'nullable|exists:departments,id',
            'marital_status_id' => 'nullable|exists:marital_statuses,id',
            'military_service_status_id' => 'nullable|exists:military_service_statuses,id',
            'supervisor_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $branchId = $this->input('branch_id');
                    $departmentId = $this->input('department_id');

                    $exists = Employee::where('id', $value)
                        ->where('branch_id', $branchId)
                        ->where('department_id', $departmentId)
                        ->exists();

                    if (!$exists) {
                        $fail(__('validation.custom.supervisor_id.exists_in_branch_department'));
                    }
                },
            ],

            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:1',
            'assurance_salary' => 'nullable|numeric|min:1',
            'banks' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    $payrollCount = collect($value)->where('is_payroll_account', 1)->count();
                    if ($payrollCount > 1) {
                        $fail(__('validation.only_one_payroll_account'));
                    }
                },
            ],
            'banks.*.bank_name_id' => 'required|exists:bank_names,id',
            'banks.*.bank_account_number' => 'required|numeric',
            'banks.*.bank_iban' => 'nullable|numeric',
            'banks.*.is_payroll_account' => 'required|in:0,1',
            'employment_type' => 'nullable|string',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_biometric' => 'nullable|boolean',
            'vehicle_number' => 'required_if:flag,driver',
            'vehicle_type' => [
                'required_if:flag,driver',
                function ($attribute, $value, $fail) {
                    if (!\App\Models\VehicleSetting::where('id', $value)->exists()) {
                        $fail(__('validation.custom.vehicle_type.exists'));
                    }
                },
            ],
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'facebook_link' => 'nullable|url',
            'instagram_link' => 'nullable|url',
            'twitter_link' => 'nullable|url',
            'passport_number' => [
                Rule::requiredIf(function () use ($branchCountryId, $employeeCountryId) {
                    return $employeeCountryId && $branchCountryId && $employeeCountryId != $branchCountryId;
                }),
                'nullable',
                'string',
                Rule::unique('employee_legal_documents')->ignore($employeeId, 'employee_id'),
            ],

            'work_permit_expiry_date' => [
                Rule::requiredIf(fn() => $employeeCountryId && $branchCountryId && $employeeCountryId != $branchCountryId),
                'nullable',
                'date',
            ],

            'residency_expiry_date' => [
                Rule::requiredIf(fn() => $employeeCountryId && $branchCountryId && $employeeCountryId != $branchCountryId),
                'nullable',
                'date',
            ],

            'passport_expiry_date' => [
                Rule::requiredIf(fn() => $employeeCountryId && $branchCountryId && $employeeCountryId != $branchCountryId),
                'nullable',
                'date',
            ],

            'passport_copy' => [
                Rule::requiredIf(fn() => $employeeCountryId && $branchCountryId && $employeeCountryId != $branchCountryId),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
            ],

            'work_permit' => [
                Rule::requiredIf(fn() => $employeeCountryId && $branchCountryId && $employeeCountryId != $branchCountryId),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
            ],
            'resume' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'national_id_photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'contract' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'warehouse_id'     => 'nullable|integer|exists:stores,id',
            'military_service_status_id' => 'required|numeric|exists:military_service_statuses,id',
            'whatsapp_number' => 'nullable|string',
            'current_address' => 'required|string',
            'home_country_address' => $isAuthEmployee ? 'required' : 'nullable|string',
            'position_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $departmentId = $this->input('department_id');
                    if (!\App\Models\Position::where('id', $value)->where('department_id', $departmentId)->exists()) {
                        $fail(__('validation.custom.position_id.exists'));
                    }
                },
            ],
            'emergency_contact_one_name' => $isAuthEmployee ? 'required' : 'nullable|string',
            'emergency_contact_one_relation' => $isAuthEmployee ? 'required' : 'nullable|string',
            'emergency_contact_one_phone' => $isAuthEmployee ? 'required' : 'nullable|numeric|min:1',
            'emergency_contact_two_name' => $isAuthEmployee ? 'required' : 'nullable|string',
            'emergency_contact_two_relation' => $isAuthEmployee ? 'required' : 'nullable|string',
            'emergency_contact_two_phone' => $isAuthEmployee ? 'required' : 'nullable|numeric|min:1',
            'employee_status_id' => 'required|numeric|exists:employee_status,id',
            'previous_position' => 'nullable|string',
            'previous_salary' => 'nullable|numeric|min:1',
            'expected_salary' => 'nullable|numeric|min:1',
            'num_experience_years' => 'nullable|numeric|min:0',
            'shift_id' => 'nullable|exists:shifts,id',
            'schedule_start_date' => 'nullable|date',
            'schedule_end_date' => 'nullable|date',
            'payment_type_id' => 'required|numeric|exists:payment_types,id',
            'payment_frequency_id' => 'required|numeric|exists:payment_frequencies,id',
            'filed_of_study_id' => 'required|numeric|exists:filed_of_studies,id',
            'education_level_id' => 'required|numeric|exists:education_levels,id',
            'university_id' => 'required|numeric|exists:universities,id',
            'graduation_year' => 'nullable|numeric|min:1900',
            'degree_certificate' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'additional_certifications' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'certification_documents' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            // 'egypt_license_expiry' => 'nullable|date|required_if:flag,driver|required_if:egypt_license,1',
            'egypt_license_expiry' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    if (
                        ($this->input('flag') === 'driver') &&
                        ($this->input('egypt_license') == 1) &&
                        empty($value)
                    ) {
                        $fail(__('validation.required'));
                    }
                },
            ],

            // 'egypt_license_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|required_if:flag,driver|required_if:egypt_license,1',
            'egypt_license_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf',

            'egypt_license' => 'nullable',
            // 'kuwait_license_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|required_if:flag,driver|required_if:kuwait_license,1',
            'kuwait_license_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf',

            'kuwait_license_expiry' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    if (
                        ($this->input('flag') === 'driver') &&
                        ($this->input('kuwait_license') == 1) &&
                        empty($value)
                    ) {
                        $fail(__('validation.required'));
                    }
                },
            ],

            'kuwait_license' => 'nullable',
            // 'license_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|required_if:flag,driver|required_if:driving_license,1',
            'license_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf',

            'license_expiry' => 'nullable|date|required_if:flag,driver|required_if:driving_license,1',
            'driving_license' => 'nullable',
            'license_country' => 'nullable',
            'Drug_Test_Report' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'Criminal_Record' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'Tasks_instructions' => $isAuthEmployee ? 'required' : 'nullable',
            'Languages_Spoken' => $isAuthEmployee ? 'required' : 'nullable',
            'residency_transferable' => 'nullable',
            'Visa_Information' => $isAuthEmployee ? 'required' : 'nullable',
            'hear_about_us' => $isAuthEmployee ? 'required' : 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'employee_code.unique' => __('validation.employee_code_unique'),
            'national_id.unique' => __('validation.national_id_unique'),
            'passport_number.unique' => __('validation.passport_number_unique'),
            'assurance_number.unique' => __('validation.assurance_number_unique'),
            'biometric_id.unique' => __('validation.biometric_id_unique'),
        ];
    }
    public function failedValidation($validator)
    {
        $lang = $this->header('lang', 'en');
        app()->setLocale($lang);

        if ($this->expectsJson()) {
            throw new HttpResponseException(
                respondError(
                    $lang === 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                )
            );
        }
    }
}
