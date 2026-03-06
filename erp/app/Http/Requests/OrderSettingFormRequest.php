<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderSettingFormRequest extends FormRequest
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
            'tax_application' => 'required|in:0,1',
            'coupon_application' => 'required|in:0,1',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'time_cancellation' => 'required|numeric|min:0',
            'delivery_time' => 'required|numeric|min:0',
            'delivery_difference'=>'required|numeric|min:0',
        ];
    }
}
