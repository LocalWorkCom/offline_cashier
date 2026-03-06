<?php

namespace App\Http\Requests\Finance;

use App\Helper\APIResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreateCostCenterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    private string $failReason = '';

    public function authorize(): bool
    {
        $employee = auth('employee')->user();        
        $facility_id = $employee->employeeFacility->facility_id;

        $result_data = \App\Models\Facility::find($facility_id);
        if (!$result_data) {
            $this->failReason = 'not_facility';
            return false;
        }

        if ($result_data->is_active != 1) {
            $this->failReason = 'not_active_facility';
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $employee = auth('employee')->user();
        $facility_id = $employee->employeeFacility->facility_id;

        return [
            'name_ar' => [
                'required_without_all:branch_id,company_id',
                'string',
                'max:255',
                Rule::unique('cost_centers', 'name_ar')->where('facility_id', $facility_id)->whereNull('deleted_at'),
            ],
            'code' => [
                'sometimes',
                'required',
                'regex:/^[a-zA-Z0-9]+$/',
                Rule::unique('cost_centers', 'code')->where('facility_id', $facility_id)->whereNull('deleted_at'),
            ],
            'is_active' => 'nullable|integer|in:0,1,2',
            'facility_id' => ['required', 'numeric',
                Rule::exists('facilities', 'id')->whereNull('deleted_at')
            ],
            'company_id' => ['nullable', 'numeric',
                Rule::exists('company_profile_settings', 'id')->whereNull('deleted_at'),
                Rule::unique('cost_centers', 'company_id')->where('facility_id', $facility_id)->whereNull('deleted_at'),
            ],
            'branch_id' => ['nullable', 'numeric',
                Rule::exists('branches', 'id')->whereNull('deleted_at'),
                Rule::unique('cost_centers', 'branch_id')->where('facility_id', $facility_id)->whereNull('deleted_at'),
            ],
            'parent_id' => ['nullable', 'numeric',
                Rule::exists('cost_centers', 'id')->whereNull('deleted_at')
            ]
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            // 'name_ar.unique' => __('The Arabic name already exists.')
            // 'name_en.unique' => __('The English name already exists.'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $lang = $this->header('lang', 'en');
        $response = respondError(
            $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
            400,
            $validator->errors()
        );
        throw new HttpResponseException($response);
    }

    protected function failedAuthorization()
    {
        $lang = $this->header('lang', 'ar');
        if ($this->failReason === 'not_active_facility') {
            $message = $lang === 'en'
                ? 'You cannot add a new cost center because the facility is not active.'
                : 'لا يمكنك اضافة مركز تكلفة جديد لان المنشاة غير مفعلة';
        } 
        if($this->failReason === 'not_facility') {
            $message = $lang === 'en'
                ? 'This facility is not found.'
                : 'المنشاة غير موجودة.';
        }

        throw new HttpResponseException(
            respondError($message, 403)
        );
    }
}
