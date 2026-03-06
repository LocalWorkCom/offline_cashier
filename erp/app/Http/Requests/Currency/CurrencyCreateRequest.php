<?php

namespace App\Http\Requests\Currency;

use App\Http\Requests\BasicFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class CurrencyCreateRequest extends BasicFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'currency_ar' => ['required', 'string', 'max:255'],
            'currency_en' => ['required', 'string', 'max:255'],
            'currency_symbol' => ['required', 'string'],
            'currency_code' => ['required', 'string'],
            'is_default' => ['boolean'],
            'country_id' => ['required', 'exists:countries,id'],
        ];
    }
}
