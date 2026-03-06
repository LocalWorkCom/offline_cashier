<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BonusSettingsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function prepareForValidation()
    {
        $lang = $this->header('lang', 'en');
        App::setLocale($lang);
    }

    public function rules()
    {
        return [
            'max_bonus_percentage' => 'required|numeric|min:0|max:100',
            'fixed_bonus_cap' => 'required|numeric|min:0',
            'days_convertible_to_money' => 'required|integer|min:0',
            'disbursement_timing' => [
                'required',
                Rule::in(['monthly_salary', 'specific_date', 'both'])
            ]
        ];
    }

    public function messages()
    {
        return [
            'max_bonus_percentage.required' => __('validation.custom.max_bonus_percentage.required'),
            'max_bonus_percentage.numeric' => __('validation.custom.max_bonus_percentage.numeric'),
            'max_bonus_percentage.min' => __('validation.custom.max_bonus_percentage.min'),
            'max_bonus_percentage.max' => __('validation.custom.max_bonus_percentage.max'),
            'fixed_bonus_cap.required' => __('validation.custom.fixed_bonus_cap.required'),
            'fixed_bonus_cap.numeric' => __('validation.custom.fixed_bonus_cap.numeric'),
            'fixed_bonus_cap.min' => __('validation.custom.fixed_bonus_cap.min'),
            'days_convertible_to_money.required' => __('validation.custom.days_convertible_to_money.required'),
            'days_convertible_to_money.integer' => __('validation.custom.days_convertible_to_money.integer'),
            'days_convertible_to_money.min' => __('validation.custom.days_convertible_to_money.min'),
            'disbursement_timing.required' => __('validation.custom.disbursement_timing.required'),
            'disbursement_timing.in' => __('validation.custom.disbursement_timing.in'),
        ];
    }
}