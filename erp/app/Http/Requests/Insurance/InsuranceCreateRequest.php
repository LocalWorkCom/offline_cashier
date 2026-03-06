<?php

namespace App\Http\Requests\Insurance;

use App\Http\Requests\BasicFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class InsuranceCreateRequest extends BasicFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:65000'],
            'description_en' => ['nullable', 'string', 'max:65000'],
            'subscription_num' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'automatic_transfer' => ['boolean'],
            'company_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'employee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'subscription_expenses' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/', // Ensures proper decimal format (e.g., 10.99)
                'min:0', // Ensures the value is not negative
            ],
            // 'journal_ids' => ['array'],
            // 'journal_ids.*' => ['integer', 'exists:journals,id'],
        ];
    }
}
