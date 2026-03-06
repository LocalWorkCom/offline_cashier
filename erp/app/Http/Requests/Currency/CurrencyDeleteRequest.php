<?php

namespace App\Http\Requests\Currency;

use App\Http\Requests\BasicFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class CurrencyDeleteRequest extends BasicFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'currency_id' => ['required',
                    Rule::exists('currencies', 'id')->whereNull('deleted_at')
            ],
        ];
    }

    public function getId(): mixed
    {
        return $this->route()->parameters['currency_id'];
    }
}
