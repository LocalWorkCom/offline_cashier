<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'BasicInfo' => [
                'id' => $this->id,
                'employee_code' => $this->employee_code,
                'name_ar' => $this->first_name . ' ' . $this->last_name,
                'name_en' => $this->first_name_en . ' ' . $this->last_name_en,
                'full_name' => $this->full_name,
                'gender' => $this->gender,
                'birth_date' => $this->birth_date,
                'image' => $this->image,
                'national_id' => $this->national_id,
                'nationality' =>  $this->whenLoaded('nationality', function () {
                    return [
                        'id' => $this->nationality_id,
                        'name' => $this->nationality->name,
                    ];
                }),
                'blood_group' => $this->blood_group,

                'hire_date' => $this->hire_date,
                'work_start_date' => $this->work_start_date,
                "salary" => $this->salary,
                "daily_excuse_hours" => $this->daily_excuse_hours,
                "monthly_excuse_hours" => $this->monthly_excuse_hours,
                "assurance_salary" => $this->assurance_salary,
                "assurance_number" => $this->assurance_number,
                "work_from_home" => $this->work_from_home,
                "work_from_home_days" => $this->work_from_home_days,

                // "status" => $this->status,
                "notes" => $this->notes,
                "is_biometric" => $this->is_biometric,
                "biometric_id" => $this->biometric_id,
                "flag" => $this->flag,
                'marital_status' =>  $this->whenLoaded('maritalStatus', function () {
                    return [
                        'id' => $this->marital_status_id,
                        'name' => $this->maritalStatus->name,
                    ];
                }),
                'military_status' =>  $this->whenLoaded('militaryStatus', function () {
                    return [
                        'id' => $this->military_service_status_id,
                        'name' => $this->militaryStatus->name,
                    ];
                }),
                'employee_status' =>  $this->whenLoaded('employeeStatus', function () {
                    return [
                        'id' => $this->employee_status_id,
                        'name' => $this->employeeStatus->name,
                    ];
                }),
            ],
            'ContactInfo' => [
                'email' => $this->email,
                'country_code' => $this->country_code,
                'phone_number' => $this->phone_number,
                'address' => $this->address,
                'country' => $this->whenLoaded('country', function () {
                    return new CountryResource($this->country);
                }),
                'area' => $this->whenLoaded('area', function () {
                    return [
                        'id' => $this->area_id,
                        'name' => $this->area->name,
                    ];
                }),
                'city' => $this->whenLoaded('city', function () {
                    return [
                        'id' => $this->city_id,
                        'name' => $this->city->name,
                    ];
                }),
                'social_info' => $this->whenLoaded('employeeContactInfo', function () {
                    return $this->employeeContactInfo->map(function ($info) {
                        return [
                            "facebook_link" => $info->facebook_link,
                            "instagram_link" => $info->instagram_link,
                            "twitter_link" => $info->twitter_link,
                            "whatsapp_number" => (string)$info->whatsapp_number,
                        ];
                    });
                }),
                'emergency_contact' => $this->whenLoaded('employeeContactInfo', function () {
                    return $this->employeeContactInfo->map(function ($info) {
                        return [
                            "current_address" => $info->current_address,
                            "home_country_address" => $info->home_country_address,
                            "emergency_contact_one_name" => $info->emergency_contact_one_name,
                            "emergency_contact_one_relation" => $info->emergency_contact_one_relation,
                            "emergency_contact_one_phone" => $info->emergency_contact_one_phone,
                            "emergency_contact_two_name" => $info->emergency_contact_two_name,
                            "emergency_contact_two_relation" => $info->emergency_contact_two_relation,
                            "emergency_contact_two_phone" => $info->emergency_contact_two_phone
                        ];
                    });
                }),
            ],
            'details' => [
                'Evaluation' =>  $this->Performance,
                "employment_type" => $this->employment_type,
                'hire_date' => $this->hire_date,
                'work_start_date' => $this->work_start_date,

                'vehicle' => $this->whenLoaded('vehicle', function () {
                    return [
                        // 'id' => $this->vehicle->id,
                        'name' => $this->vehicle->name,
                    ];
                }),

                'supervisor' => $this->whenLoaded('supervisor', function () {
                    return [
                        'id' => $this->supervisor_id,
                        'name' => $this->supervisor->name,
                    ];
                }),
                'attendance' => $this->whenLoaded('attendanceRecords', function () {
                    return $this->attendanceRecords->map(function ($record) {
                        return [
                            'id' => $record->id,
                            'date' => $record->date,
                            'status' => $record->status,
                        ];
                    });
                }),
                'branch' => $this->whenLoaded('branch', function () {
                    return new BranchResource($this->branch);
                }),
                'department' => $this->whenLoaded('department', function () {
                    return [
                        'id' => $this->department_id,
                        'name' => $this->department->name,
                    ];
                }),
                'sub_department' => $this->when(
                    $this->sub_department_id && $this->relationLoaded('subDepartment'),
                    function () {
                        return [
                            'id'   => $this->sub_department_id,
                            'name' => $this->subDepartment->name,
                        ];
                    }
                ),

                'banking_info' => $this->whenLoaded('employeeBankingInfo', function () {
                    return $this->employeeBankingInfo->map(function ($bank) {
                        return [
                            'id' => $bank->id,
                            'name' => $bank->bankName?->first()->name ?? null,
                            'bank_account_number' => $bank->bank_account_number ?? null,
                            'bank_iban' => $bank->bank_iban ?? null,
                            'is_payroll_account' => $bank->is_payroll_account ?? null,
                        ];
                    });
                }),

                'position' => $this->whenLoaded('position', function () {
                    return [
                        'id' => $this->position_id,
                        'name' => $this->position->name,
                    ];
                }),
                'employeeSchedules' => $this->whenLoaded('employeeSchedules', function () {
                    return $this->employeeSchedules->map(function ($schedule) {
                        return [
                            'id' => $schedule->id,
                            'name' => $schedule->shift?->details,
                        ];
                    });
                }),
            ],
            'Legal_documents' => [
                'passport_number' => $this->legalDocument->first()?->passport_number ?? null,
                'passport_expiry_date' => $this->legalDocument->first()?->passport_expiry_date ?? null,
                'passport_copy' => $this->legalDocument->first()?->passport_copy ?? null,
                'work_permit' => $this->legalDocument->first()?->work_permit ?? null,
                'work_permit_expiry_date' => $this->legalDocument->first()?->work_permit_expiry_date ?? null,
                'residency_permit' => $this->legalDocument->first()?->residency_permit ?? null,
                'residency_expiry_date' => $this->legalDocument->first()?->residency_expiry_date ?? null,
                'driving_license' => $this->license ?? null,
                'Documents' => $this->whenLoaded('employeeDocument', function () {
                    return $this->employeeDocument->map(function ($document) {
                        return [
                            'id' => $document->id,
                            'document_type' => $document->documentType->name ?? null,
                            'date' => $document->date,
                            'file' => $document->file,
                            'is_active' => $document->is_active,
                        ];
                    });
                }),

            ],
            'salary_info' => [
                'base_salary' => $this->employeeSalaryDetail->first()?->base_salary ?? null,
                'salary_work_permit' => $this->employeeSalaryDetail->first()?->salary_work_permit ?? null,
                'annual_salary' => $this->employeeSalaryDetail->first()?->annual_salary ?? null,
                'is_allowance' => $this->employeeSalaryDetail->first()?->is_allowance ?? null,
                'is_bonus' => $this->employeeSalaryDetail->first()?->is_bonus ?? null,
                'is_commision' => $this->employeeSalaryDetail->first()?->is_commision ?? null,
                'commission_type' => $this->employeeSalaryDetail->first()?->commission_type ?? null,

                'work_start_date' => Carbon::parse($this->employeePayrollSettings()->latest()->first()?->effective_from)->toDateString() ?? null,
                'end_salary_date' => Carbon::parse($this->employeePayrollSettings()->latest()->first()?->effective_to)->toDateString() ?? null,
                'payment_types' =>  $this->paymentTypes,
                'payment_frequencies' =>  $this->paymentFrequencies,
            ],
            'job_type_info' => [
                'name_ar' => $this->employeeJobTypeDetail->name_ar ?? null,
                'name_en' => $this->employeeJobTypeDetail->name_en ?? null,
            ],
            'other_info' => $this->whenLoaded('additionalInfo', function () {
                return [
                    'additional_description' => $this->additionalInfo->additional_description ?? null,
                    'referral_source' => $this->additionalInfo->referral_source ?? null,
                    'languages_spoken' => $this->additionalInfo->languages_spoken ?? null,
                    'visa_information' => $this->additionalInfo->visa_information ?? null,
                    'is_residency_transferable' => $this->additionalInfo->is_residency_transferable ?? null,
                    'tasks_and_instructions' => $this->additionalInfo->tasks_and_instructions ?? null,
                    'criminal_record_file' => $this->additionalInfo->criminal_record_file ?? null,
                    'drug_test_report_file' => $this->additionalInfo->drug_test_report_file ?? null,
                    'referral_source' => $this->additionalInfo->referral_source ?? null,
                    'Experiences' => $this->experience ?? null,
                    'tasks_and_instructions' => $this->additionalInfo->tasks_and_instructions ?? null,
                    'educations' => $this->educations ?? null
                ];
            }),



        ];
    }
}
