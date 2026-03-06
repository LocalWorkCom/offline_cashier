<?php

namespace App\Http\Requests\InsuranceEmployee;

use App\Http\Requests\BasicFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class InsuranceEmployeeUpdateRequest extends BasicFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'insurance_employee_id' => [
                'required',
                'integer',
                Rule::exists('insurance_employees', 'id')->whereNull('deleted_at'),
            ],
            'insurance_id' => [
                'required',
                'integer',
                Rule::exists('insurances', 'id')->whereNull('deleted_at'),
            ],
            'employee_id' => [
                'required',
                'integer',
                'exists:employees,id'
            ],
            'amount' => [
                'required',
                'numeric',
                'between:0,999999.99',
            ],
            'date' => [
                'required',
                'date',
                'date_format:Y-m-d',
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }

    public function getId()
    {
        return $this->route()->parameter('insurance_employee_id');
    }
}
