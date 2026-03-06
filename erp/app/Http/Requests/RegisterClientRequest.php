<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Country;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;

class RegisterClientRequest extends FormRequest
{
    public function rules(): array
    {
        $phone_length = Country::where('phone_code', $this->country_code)->value('length');

        return [
            "name" => "required|string",
            "email" => [
                'nullable',
                'email',
                Rule::unique('users')->where(fn($query) => $query->whereNull('deleted_at')),
            ],
            'country_code' => 'required|string',
            "password" => "required|min:6",
            'phone' => [
                'required',
                'numeric',
                Rule::unique('users', 'phone')
                    ->where(
                        fn($query) =>
                        $query->where('country_code', $this->country_code)
                            ->whereNull('deleted_at')
                    ),
                function ($attribute, $value, $fail) use ($phone_length) {
                    if ($phone_length && strlen($value) != $phone_length) {
                        $fail(__('validation.custom.phone.length', [
                            'attribute' => __('auth.phone'),
                            'length' => $phone_length,
                        ]));
                    }
                },
            ],
            // "birth_date" => "nullable|date",
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('auth.nameweb')]),
            // 'email.required' => __('validation.required', ['attribute' => __('auth.emailweb')]),
            'email.unique' => __('validation.unique', ['attribute' => __('auth.email')]),
            'country_code.required' => __('validation.required', ['attribute' => __('auth.country_code')]),
            'password.required' => __('validation.required', ['attribute' => __('auth.password')]),
            'password.min' => __('validation.min.string', ['attribute' => __('auth.password'), 'min' => 6]),
            'phone.required' => __('validation.required', ['attribute' => __('auth.phoneplace')]),
            'phone.unique' => __('validation.unique', ['attribute' => __('auth.phone')]),
            // 'birth_date.date' => __('validation.date', ['attribute' => __('auth.date')]),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'code' => 400,
            'status' => false,
            'message' => 'Validation Error.',
            'data' => null,
            'errorData' => $validator->errors(),
        ], 200);

        throw new HttpResponseException($response);
    }
    protected function prepareForValidation()
    {
        $lang = $this->header('lang', 'ar');
        App::setLocale($lang);
    }
}
