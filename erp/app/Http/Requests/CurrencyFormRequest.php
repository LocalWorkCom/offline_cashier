<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyFormRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'currency_ar' => 'required|string',
            'currency_en' => 'required|string',
            'currency_symbol' => 'required|string',
            'currency_code' => 'required|string',
            'is_default' => 'nullable|in:0,1',
            'country_id' => 'required|exists:countries,id',
            ];
    }
}
