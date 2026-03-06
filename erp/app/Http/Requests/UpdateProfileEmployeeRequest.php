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

class UpdateProfileEmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    // public function rules()
    // {
    //     $employeeId = auth('employee')->user()->id;
    //     $branchCountryId = Branch::find($this->branch_id)?->country_id;
    //     $employeeCountryId = \App\Models\Country::where('id', $this->country_code)
    //         ->orWhere('phone_code', $this->country_code)
    //         ->value('id');
    //     return [
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
    //         'first_name' => 'required|string',
    //         'last_name' => 'required|string',
    //         'first_name_en' => 'nullable|string',
    //         'last_name_en' => 'nullable|string',
    //         'gender' => 'nullable|string',
    //         'birth_date' => 'nullable|date',
    //         'marital_status' => 'nullable|string',
    //         'national_id' => [
    //             'nullable',
    //             'numeric',
    //             'min:1',
    //             Rule::unique('employees')->ignore($employeeId),
    //         ],
    //         'country_code' => [
    //             'required',
    //             'string',
    //             function ($fail) use ($employeeCountryId) {
    //                 if (!$employeeCountryId) {
    //                     $fail(__('validation.exists')); // Or custom error message
    //                 }
    //             },
    //         ],
    //         'additional_certifications' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
    //         'certification_documents' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
    //         'email' => [
    //             'required',
    //             'email',
    //             Rule::unique('employees', 'email')->ignore($employeeId, 'id'),
    //         ],
    //         'emergency_contact_one_name' => 'nullable|string',
    //         'emergency_contact_one_relation' => 'nullable|string',
    //         'emergency_contact_one_phone' => 'nullable|numeric|min:1',
    //         'emergency_contact_two_name' => 'nullable|string',
    //         'emergency_contact_two_relation' => 'nullable|string',
    //         'emergency_contact_two_phone' => 'nullable|numeric|min:1',
    //         'Languages_Spoken' => 'nullable',
    //         'residency_transferable' => 'nullable',
    //         'Visa_Information' => 'nullable',
    //         'hear_about_us' => 'nullable',
    //         'work_permit_expiry_date' => [
    //             'nullable',
    //             'date',
    //         ],
    //         'work_permit' => [
    //             'nullable',
    //             'file',
    //             'mimes:jpg,jpeg,png,pdf',
    //         ],
    //         'whatsapp_number' => 'nullable|string',
    //         'current_address' => 'required|string',
    //         'home_country_address' => 'required',
    //         'residency_expiry_date' => [
    //             Rule::requiredIf(fn() => $employeeCountryId && $branchCountryId && $employeeCountryId != $branchCountryId),
    //             'nullable',
    //             'date',
    //         ],
    //         'marital_status_id'=>'required',
    //         'license_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
    //         'phone' => [
    //             'required',
    //             'numeric',
    //             'min:1',
    //             function ($attribute, $value, $fail) {
    //                 if (!empty($this->country_code)) {
    //                     $country = Country::where('phone_code', $this->country_code)->first();
    //                     if ($country && strlen($value) != $country->length) {
    //                         $fail(__('validation.custom.phone.length', [
    //                             'attribute' => __('auth.phone'),
    //                             'length' => $country->length
    //                         ]));
    //                     }
    //                 }
    //             },
    //         ],


    //     ];
    // }

    // public function rules()
    // {
    //     $employeeId = auth('employee')->user()->id;
    //     $branchCountryId = Branch::find($this->branch_id)?->country_id;
    //     $employeeCountryId = \App\Models\Country::where('id', $this->country_code)
    //         ->orWhere('phone_code', $this->country_code)
    //         ->value('id');

    //     // Start with base rules
    //     $rules = [
    //         // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
    //         // 'first_name' => 'required|string',
    //         // 'last_name' => 'required|string',
    //         // 'first_name_en' => 'nullable|string',
    //         // 'last_name_en' => 'nullable|string',
    //         // 'gender' => 'nullable|string',
    //         // 'birth_date' => 'nullable|date',
    //         // 'marital_status' => 'nullable|string',
    //         // 'national_id' => [
    //         //     'nullable',
    //         //     'numeric',
    //         //     'min:1',
    //         //     Rule::unique('employees')->ignore($employeeId),
    //         // ],
    //         'country_code' => [
    //             'required',
    //             'string',
    //             function ($fail) use ($employeeCountryId) {
    //                 if (!$employeeCountryId) {
    //                     $fail(__('validation.exists'));
    //                 }
    //             },
    //         ],
    //         // 'email' => [
    //         //     'required',
    //         //     'email',
    //         //     Rule::unique('employees', 'email')->ignore($employeeId, 'id'),
    //         // ],
    //         // 'current_address' => 'required|string',
    //         'home_country_address' => 'required',
    //         'marital_status_id' => 'required',
    //         // 'phone' => [
    //         //     'required',
    //         //     'numeric',
    //         //     'min:1',
    //         //     function ($attribute, $value, $fail) {
    //         //         if (!empty($this->country_code)) {
    //         //             $country = \App\Models\Country::where('phone_code', $this->country_code)->first();
    //         //             if ($country && strlen($value) != $country->length) {
    //         //                 $fail(__('validation.custom.phone.length', [
    //         //                     'attribute' => __('auth.phone'),
    //         //                     'length' => $country->length
    //         //                 ]));
    //         //             }
    //         //         }
    //         //     },
    //         // ],
    //     ];

    //     if ($this->type == 1) {
    //         $rules = [
    //             'first_name' => 'required|string',
    //             'last_name' => 'required|string',
    //             'first_name_en' => 'nullable|string',
    //             'last_name_en' => 'nullable|string',
    //             'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
    //             'gender' => 'nullable|string',
    //             'birth_date' => 'nullable|date',
    //             'marital_status_id' => 'nullable|numeric|min:1|exists:marital_statuses,id',
    //             'nationality_id' => [
    //                 'nullable',
    //                 'numeric',
    //                 'min:1',
    //                 'exists:nationalities,id',
    //             ],
    //         ];
    //     } elseif ($this->type == 2) {
    //         $rules = [
    //             'phone_number' => [
    //                 'required',
    //                 'numeric',
    //                 'min:1',
    //                 Rule::unique('employees', 'phone_number')->ignore($employeeId),
    //                 function ($attribute, $value, $fail) {
    //                     if (!empty($this->country_code)) {
    //                         $country = \App\Models\Country::where('phone_code', $this->country_code)->first();
    //                         if ($country && strlen($value) != $country->length) {
    //                             $fail(__('validation.custom.phone.length', [
    //                                 'attribute' => __('auth.phone'),
    //                                 'length' => $country->length
    //                             ]));
    //                         }
    //                     }
    //                 },
    //             ], 
    //             'whatsapp_number' => [
    //                 'required',
    //                 'numeric',
    //                 'min:1',
    //                 Rule::unique('employee_contact_infos', 'whatsapp_number')->ignore($employeeId),
    //                 function ($attribute, $value, $fail) {
    //                     if (!empty($this->country_code)) {
    //                         $country = \App\Models\Country::where('phone_code', $this->country_code)->first();
    //                         if ($country && strlen($value) != $country->length) {
    //                             $fail(__('validation.custom.phone.length', [
    //                                 'attribute' => __('auth.phone'),
    //                                 'length' => $country->length
    //                             ]));
    //                         }
    //                     }
    //                 },
    //             ],
    //             'email' => [
    //                 'required',
    //                 'string',
    //                 'email',
    //                 Rule::unique('employees', 'email')->ignore($employeeId),
    //             ],
    //             'current_address' => 'required|string',
    //             'country_code' => 'required|string',
    //             'country_id' => [
    //                 'nullable',
    //                 'numeric',
    //                 'min:1',
    //                 'exists:countries,id',
    //             ],
    //         ];
    //     } elseif ($this->type == 3) {
    //         $rules = [
    //             'phone' => [
    //                 'required',
    //                 'numeric',
    //                 'min:1',
    //                 function ($attribute, $value, $fail) {
    //                     if (!empty($this->country_code)) {
    //                         $country = \App\Models\Country::where('phone_code', $this->country_code)->first();
    //                         if ($country && strlen($value) != $country->length) {
    //                             $fail(__('validation.custom.phone.length', [
    //                                 'attribute' => __('auth.phone'),
    //                                 'length' => $country->length
    //                             ]));
    //                         }
    //                     }
    //                 },
    //             ],
    //             'email' => [
    //                 'required',
    //                 'email',
    //                 Rule::unique('employees', 'email')->ignore($employeeId, 'id'),
    //             ],
    //         ];
    //     }

    //     return $rules;
    // }

    public function rules()
    {
        $employeeId = auth('employee')->user()->id;
        $branchCountryId = Branch::find($this->branch_id)?->country_id;
        $employeeCountryId = \App\Models\Country::where('id', $this->country_code)
            ->orWhere('phone_code', $this->country_code)
            ->value('id');

        $rules = [
            'country_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($employeeCountryId) {
                    if (!$employeeCountryId) {
                        $fail(__('validation.exists'));
                    }
                },
            ],
            'home_country_address' => 'required',
            'marital_status_id' => 'required',
        ];

        // ---------------- TYPE 1 ----------------
        if ($this->type == 1) {
            $rules = [
                'full_name' => 'required|string',
                // 'first_name' => 'required|string',
                // 'last_name'  => 'required|string',
                'first_name_en' => 'nullable|string',
                'last_name_en'  => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'gender' => 'nullable|string',
                'birth_date' => 'nullable|date',
                'marital_status_id' => 'nullable|numeric|min:1|exists:marital_statuses,id',
                'nationality_id' => [
                    'nullable',
                    'numeric',
                    'min:1',
                    'exists:nationalities,id',
                ],
            ];
        }

        // ---------------- TYPE 2 ----------------
        elseif ($this->type == 2) {
            $rules = [
                'phone_number' => [
                    'required',
                    'numeric',
                    'min:1',
                    Rule::unique('employees', 'phone_number')->ignore($employeeId, 'id'),
                    function ($attribute, $value, $fail) {
                        if (!empty($this->country_code)) {
                            $country = \App\Models\Country::where('phone_code', $this->country_code)->first();
                            if ($country && strlen($value) != $country->length) {
                                $fail(__('validation.custom.phone.length', [
                                    'attribute' => __('auth.phone'),
                                    'length' => $country->length
                                ]));
                            }
                        }
                    },
                ],
                'whatsapp_number' => [
                    'required',
                    'numeric',
                    'min:1',
                    Rule::unique('employee_contact_infos', 'whatsapp_number')
                        ->ignore($employeeId, 'employee_id'),
                    function ($attribute, $value, $fail) {
                        if (!empty($this->country_code)) {
                            $country = \App\Models\Country::where('phone_code', $this->country_code)->first();
                            if ($country && strlen($value) != $country->length) {
                                $fail(__('validation.custom.phone.length', [
                                    'attribute' => __('auth.phone'),
                                    'length' => $country->length
                                ]));
                            }
                        }
                    },
                ],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('employees', 'email')->ignore($employeeId),
                ],
                'current_address' => 'required|string',
                'country_code'    => 'required|string',
                'country_id' => [
                    'nullable',
                    'string',
                    'exists:countries,id',
                ],
            ];
        }

        // ---------------- TYPE 3 ----------------
        elseif ($this->type == 3) {
            $rules = [
                'work_permit' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,pdf',
                ],
                'passport_copy' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,pdf',
                ],
                'residency_permit' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,pdf',
                ],
                'license_copy' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,pdf',
                ],
            ];
        }

        return $rules;
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
