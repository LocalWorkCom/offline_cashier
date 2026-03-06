<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RateFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('api')->check() && auth('api')->user()->flag === 'client';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'value' => 'required|numeric|in:1,2,3,4,5',
            'note' => 'nullable|string',
        ];
    }
}
