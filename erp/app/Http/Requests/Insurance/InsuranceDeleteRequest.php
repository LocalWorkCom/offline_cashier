<?php

namespace App\Http\Requests\Insurance;

use App\Http\Requests\BasicFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class InsuranceDeleteRequest extends BasicFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'insurance_id' => ['required',
                Rule::exists('insurances', 'id')->whereNull('deleted_at')
            ]
        ];
    }

    public function getId(): mixed
    {
        return $this->route()->parameters['insurance_id'];
    }
}
