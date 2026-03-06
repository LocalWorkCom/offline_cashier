<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
  
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
   public function rules()
{
    return [
        'password' => ['required', 'string', 'min:6', 'confirmed'],
    ];
}

public function messages()
{
    return [
        'password.required' => __('validation.required', ['attribute' => __('auth.password')]),
        'password.min' => __('validation.min.string', ['attribute' => __('auth.password'), 'min' => 6]),
        'password.confirmed' => __('validation.confirmed', ['attribute' => __('auth.password')]),
    ];
}

}
