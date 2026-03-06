<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class StoreWarehouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth('employee')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'zones' => 'required|array|min:1',
            'zones.*.name' => 'required|string|max:255',
            'zones.*.description' => 'nullable|string',
            'zones.*.storage_location_id' => 'required|exists:storage_locations,id',
            'zones.*.rack_shelves' => 'nullable|array',
            'zones.*.rack_shelves.*.identifier' => 'required|string|max:255|distinct',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        $lang = Request::header('lang', 'ar');
        return [
            'name.required' => $lang == 'en' ? 'The name field is required.' : 'حقل الاسم مطلوب.',
            'zones.*.storage_location_id.required' => $lang == 'en' ? 'The storage location for the zone is required.' : 'موقع التخزين للمنطقة مطلوب.',
            'zones.*.storage_location_id.exists' => $lang == 'en' ? 'The selected storage location for the zone is invalid.' : 'موقع التخزين المحدد للمنطقة غير صالح.',
            'zones.required' => $lang == 'en' ? 'Zones are required.' : 'المناطق مطلوبة.',
            'zones.*.rack_shelves.array' => $lang == 'en' ? 'Rack shelves must be an array.' : 'الأرفف يجب أن تكون مصفوفة.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $lang = $this->header('lang', 'ar');
        \Illuminate\Support\Facades\App::setLocale($lang);
    }
}
