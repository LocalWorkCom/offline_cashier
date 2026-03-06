<?php

namespace App\Http\Requests\Finance;

use App\Helper\APIResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
class CreateFacilityRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('facilities', 'name_ar')->whereNull('deleted_at'),
            ],
            'is_active' => 'required|integer|in:0,1,2',
            'currency_id' => 'required|exists:currencies,id',
            'country_id' => 'required|exists:countries,id',
            'code' => ['required', 'regex:/^[a-zA-Z0-9]+$/'],
            'email' => [
                'nullable',
                'email',
                Rule::unique('facilities', 'email')->whereNull('deleted_at'),
            ],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'address' => 'nullable|string',
            'tax_id_number' => [
                'required',
                Rule::unique('facilities', 'tax_id_number')->whereNull('deleted_at'),
            ],
            'commercial_registration' => 'nullable',
            'commercial_registration_number' => [
                'nullable',
                Rule::unique('facilities', 'commercial_registration_number')->whereNull('deleted_at'),
            ],
            'language' => ['nullable', 'in:ar,en'],
            'vat_registration_number' => 'nullable|string',
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
}
