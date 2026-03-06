<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;

class WarningSettingsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function prepareForValidation()
    {
        $lang = $this->header('lang', 'en'); // default to English
        App::setLocale($lang);
    }

    public function rules()
    {

        return [
            'days' => 'required|integer|min:0',
            'alert' => 'required|integer|min:0',
        ];
    }


    public function messages()
    {
        return [
            'days.required' => __('validation.custom.days.required'),
            'days.integer' => __('validation.custom.days.integer'),
            'days.min' => __('validation.custom.days.min'),
            'alert.required' => __('validation.custom.alert.required'),
            'alert.integer' => __('validation.custom.alert.integer'),
            'alert.min' => __('validation.custom.alert.min'),
        ];
    }
}
