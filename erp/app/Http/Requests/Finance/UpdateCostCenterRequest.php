<?php

namespace App\Http\Requests\Finance;

use App\Helper\APIResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateCostCenterRequest extends FormRequest
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

        $id = $this->route('id');
        if (!$id) {
            $this->failReason = 'not_fount';
            return false;
        }
        $result_data = \App\Models\CostCenter::find($id);
        if (!$result_data) {
            $this->failReason = 'not_cost_center';
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $costCenter = $this->route('id');
        $employee = auth('employee')->user();
        $facility_id = $employee->employeeFacility->facility_id;

        return [
            'name_ar' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('cost_centers', 'name_ar')
                    ->whereNull('deleted_at')
                    ->where('facility_id', $facility_id)
                    ->ignore($costCenter),
            ],
            'is_active' => 'sometimes|nullable|integer|in:0,1,2',
            // 'code' => [
            //     'sometimes',
            //     'required',
            //     'regex:/^[a-zA-Z0-9]+$/',
            //     Rule::unique('cost_centers', 'code')
            //         ->whereNull('deleted_at')
            //         ->ignore($costCenter),
            // ],
            'company_id' => ['sometimes','required', 'numeric',
                Rule::exists('company_profile_settings', 'id')->whereNull('deleted_at'),
                Rule::unique('cost_centers', 'company_id')->where('facility_id', $facility_id)->whereNull('deleted_at')->ignore($costCenter),
            ],
            'branch_id' => ['sometimes','required', 'numeric',
                Rule::exists('branches', 'id')->whereNull('deleted_at'),
                Rule::unique('cost_centers', 'branch_id')->where('facility_id', $facility_id)->whereNull('deleted_at')->ignore($costCenter),
            ],
            'parent_id' => ['sometimes','required', 'numeric',
                Rule::exists('cost_centers', 'id')->whereNull('deleted_at')
            ]
        ];
    }

    public function messages(): array
    {
        return [
            // 'name_ar.unique' => 'اسم المنشأة بالعربي موجود مسبقًا',
            // 'email.unique'   => 'البريد الإلكتروني مستخدم في منشأة أخرى',
        ];
    }

    /**
     * Handle validation failure
     */
    protected function failedValidation(Validator $validator)
    {
        $lang = $this->header('lang', 'en');

        throw new HttpResponseException(
            respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق من البيانات.',
                422,
                $validator->errors()
            )
        );
    }

    protected function failedAuthorization()
    {
        $lang = $this->header('lang', 'ar');

        if ($this->failReason === 'not_active_facility') {
            $message = $lang === 'en'
                ? 'You cannot add a new entry because the facility is not active.'
                : 'لا يمكنك تعديل مركز تكلفة لان المنشاة غير مفعلة';
        } 

        if($this->failReason === 'not_facility') {
            $message = $lang === 'en'
                ? 'This facility is not found.'
                : 'المنشاة غير موجودة.';
        }

        if($this->failReason === 'not_fount') {
            $message = $lang === 'en'
                ? 'This item is not found.'
                : 'هذا العنصر غير موجود.';
        }

        if($this->failReason === 'not_cost_center') {
            $message = $lang === 'en'
                ? 'This cost center is not found.'
                : 'مركز التكلفة غير موجودة.';
        }

        throw new HttpResponseException(
            respondError($message, 403)
        );
    }
}
