<?php

namespace App\Http\Requests;

use App\Traits\AuthenticatesWithGuards;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Country;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;

class UpdateProfileRequest extends FormRequest
{
    use AuthenticatesWithGuards;

    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $lang = $this->header('lang', 'ar');
        App::setLocale($lang);
    }

    public function rules(): array
    {
        $phone_length = Country::where('phone_code', $this->country_code)->value('length');
        $userId = $this->getAuthenticatedUser()?->id;

        return [
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'numeric',
                Rule::unique('users', 'phone')
                    ->where(function ($query) {
                        return $query->where('country_code', $this->country_code)
                            ->whereNull('deleted_at');
                    })
                    ->ignore($userId),
                function ($attribute, $value, $fail) use ($phone_length) {
                    if ($phone_length && strlen($value) != $phone_length) {
                        $fail(__('validation.custom.phone.length', [
                            'attribute' => __('auth.phone'),
                            'length' => $phone_length
                        ]));
                    }
                },
            ],
            'country_code' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->whereNull('deleted_at')
                    ->ignore($userId),
            ],

            'birth_date' => 'nullable|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('auth.name')]),
            'phone.required' => __('validation.required', ['attribute' => __('auth.phone')]),
            'phone.unique' => __('validation.unique', ['attribute' => __('auth.phone')]),
            'phone.numeric' => __('validation.custom.phone.numeric', ['attribute' => __('auth.phone')]),
            'email.required' => __('validation.required', ['attribute' => __('auth.email')]),
            'email.email' => __('validation.email', ['attribute' => __('auth.email')]),
            'birth_date.date_format' => __('validation.date_format', ['attribute' => __('auth.birth_date'), 'format' => 'Y-m-d']),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            // For API or AJAX requests
            throw new HttpResponseException(
                response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => $validator->errors(),
                ], 400)
            );
        }

        // For normal web form submission (Blade)
        throw new ValidationException(
            $validator,
            redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
        );
    }
}
