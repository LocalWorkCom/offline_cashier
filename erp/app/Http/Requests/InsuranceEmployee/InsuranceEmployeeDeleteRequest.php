<?php

namespace App\Http\Requests\InsuranceEmployee;

use App\Http\Requests\BasicFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class InsuranceEmployeeDeleteRequest extends BasicFormRequest
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
        ];
    }

    public function getId()
    {
        return $this->route()->parameter('insurance_employee_id');
    }
}
