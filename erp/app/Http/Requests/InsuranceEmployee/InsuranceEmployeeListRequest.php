<?php

namespace App\Http\Requests\InsuranceEmployee;

use App\Http\Requests\PaginationListRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class InsuranceEmployeeListRequest extends PaginationListRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'insurance_id' => [
                'sometimes',
                'integer',
                Rule::exists('insurances', 'id')->whereNull('deleted_at'),
            ],
            'employee_id' => [
                'sometimes',
                'integer',
                Rule::exists('employees', 'id')->whereNull('deleted_at'),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function getFilters(): array
    {
        return $this->safe()->except(['page', 'sort_dir', 'per_page']);
    }
}
