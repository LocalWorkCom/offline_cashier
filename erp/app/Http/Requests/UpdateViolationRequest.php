<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateViolationRequest extends FormRequest
{

    public function rules(): array
    {
        $rules = [
            'violation_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('violations', 'name')->ignore($this->route('id')),
            ],
            'max_repetition' => 'required|integer|min:1',
            'within_period' => 'required|integer|min:1',
            'penalties' => 'required|array',
            'penalties.*.id' => 'required|exists:penalty_reasons,id',
            'penalties.*.order' => 'required|integer|min:1',
        ];

        // // ✅ Only add violation_id check when it's an update (PUT or PATCH)
        // if (in_array($this->method(), ['PUT', 'PATCH'])) {
        //     $rules['violation_id'] = ['required', 'exists:violations,id'];
        // }

        return $rules;
    }


    public function messages()
    {
        $lang = $this->header('lang', 'en');

        return [
            'violation_name.required' => $lang === 'en' ? 'Violation name is required.' : 'اسم المخالفة مطلوب.',
            'violation_name.unique' => $lang === 'en' ? 'Violation name must be unique.' : 'اسم المخالفة يجب أن يكون فريدًا.',
            'violation_name.string' => $lang === 'en' ? 'Violation name must be a string.' : 'اسم المخالفة يجب أن يكون نصًا.',
            'violation_name.max' => $lang === 'en' ? 'Violation name may not be greater than 255 characters.' : 'اسم المخالفة يجب ألا يزيد عن 255 حرفًا.',
            'max_repetition.required' => $lang === 'en' ? 'Max repetition is required.' : 'الحد الأقصى للتكرار مطلوب.',
            'max_repetition.integer' => $lang === 'en' ? 'Max repetition must be an integer.' : 'الحد الأقصى للتكرار يجب أن يكون عددًا صحيحًا.',
            'max_repetition.min' => $lang === 'en' ? 'Max repetition must be at least 1.' : 'الحد الأقصى للتكرار يجب أن يكون 1 على الأقل.',
            'within_period.required' => $lang === 'en' ? 'Within period is required.' : 'الفترة مطلوبة.',
            'within_period.integer' => $lang === 'en' ? 'Within period must be an integer.' : 'الفترة يجب أن تكون عددًا صحيحًا.',
            'within_period.min' => $lang === 'en' ? 'Within period must be at least 1.' : 'الفترة يجب أن تكون 1 على الأقل.',
            'penalties.required' => $lang === 'en' ? 'Penalties are required.' : 'العقوبات مطلوبة.',
            'penalties.array' => $lang === 'en' ? 'Penalties must be an array.' : 'العقوبات يجب أن تكون مصفوفة.',
            'penalties.*.id.required' => $lang === 'en' ? 'Penalty ID is required.' : 'معرف العقوبة مطلوب.',
            'penalties.*.id.exists' => $lang === 'en' ? 'Penalty ID must exist.' : 'معرف العقوبة يجب أن يكون موجودًا.',
            'penalties.*.order.required' => $lang === 'en' ? 'Penalty order is required.' : 'ترتيب العقوبة مطلوب.',
            'penalties.*.order.integer' => $lang === 'en' ? 'Penalty order must be an integer.' : 'ترتيب العقوبة يجب أن يكون عددًا صحيحًا.',
            'penalties.*.order.min' => $lang === 'en' ? 'Penalty order must be at least 1.' : 'ترتيب العقوبة يجب أن يكون 1 على الأقل.',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $penalties = $this->input('penalties', []);
            $max = $this->input('max_repetition');

            $orders = array_column($penalties, 'order');

            if (count($penalties) !== $max) {
                $validator->errors()->add('penalties', __('validation.penalties_must _equal _max'));
            }

            if (count($orders) !== count(array_unique($orders))) {
                $validator->errors()->add('penalties', __('validation.Duplicate_order'));
            }

            if (array_diff(range(1, $max), $orders)) {
                $validator->errors()->add('penalties', __('validation.penalties_must _equal _max'));
            }
        });
    }
    public function failedValidation(Validator $validator)
    {
        $response = respondError(__('Validation.error'), 400,  $validator->errors());

        throw new HttpResponseException($response);
    }
    protected function prepareForValidation()
    {
        $lang = $this->header('lang', 'ar');
        App::setLocale($lang);
        $this->merge(['violation_id' => $this->route('id')]);
    }
}
