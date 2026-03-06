<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize()
    {
        return auth('employee')->check();
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string|max:255',
            'zones' => 'sometimes|required|array|min:1',
            'zones.*.id' => 'sometimes|exists:zones,id',
            'zones.*.name' => 'sometimes|required|string|max:255',
            'zones.*.description' => 'nullable|string',
            'zones.*.storage_location_id' => 'sometimes|required|exists:storage_locations,id',
            'zones.*.rack_shelves' => 'nullable|array',
            'zones.*.rack_shelves.*.id' => 'sometimes|exists:rack_shelves,id',
            'zones.*.rack_shelves.*.identifier' => [
                'sometimes',
                'required_without:zones.*.rack_shelves.*.id',
                'string',
                'max:255',
                'distinct',
            ],
        ];
    }

    public function messages()
    {
        $lang = $this->header('lang', 'ar');
        return [
            'name.required' => $lang == 'en' ? 'The name field is required.' : 'حقل الاسم مطلوب.',
            'name.max' => $lang == 'en' ? 'The name may not be greater than 255 characters.' : 'الاسم لا يمكن أن يتجاوز 255 حرفًا.',
            'address.max' => $lang == 'en' ? 'The address may not be greater than 255 characters.' : 'العنوان لا يمكن أن يتجاوز 255 حرفًا.',
            'zones.required' => $lang == 'en' ? 'Zones are required.' : 'المناطق مطلوبة.',
            'zones.min' => $lang == 'en' ? 'At least one zone is required.' : 'مطلوب منطقة واحدة على الأقل.',
            'zones.*.id.exists' => $lang == 'en' ? 'The selected zone ID is invalid.' : 'معرف المنطقة المحدد غير صالح.',
            'zones.*.name.required' => $lang == 'en' ? 'The zone name is required.' : 'اسم المنطقة مطلوب.',
            'zones.*.name.max' => $lang == 'en' ? 'The zone name may not be greater than 255 characters.' : 'اسم المنطقة لا يمكن أن يتجاوز 255 حرفًا.',
            'zones.*.storage_location_id.required' => $lang == 'en' ? 'The storage location for the zone is required.' : 'موقع التخزين للمنطقة مطلوب.',
            'zones.*.storage_location_id.exists' => $lang == 'en' ? 'The selected storage location for the zone is invalid.' : 'موقع التخزين المحدد للمنطقة غير صالح.',
            'zones.*.rack_shelves.array' => $lang == 'en' ? 'Rack shelves must be an array.' : 'الأرفف يجب أن تكون مصفوفة.',
            'zones.*.rack_shelves.*.id.exists' => $lang == 'en' ? 'The selected rack shelf ID is invalid.' : 'معرف الرف المحدد غير صالح.',
            'zones.*.rack_shelves.*.identifier.required_without' => $lang == 'en' ? 'The rack shelf identifier is required for new rack shelves.' : 'معرف الرف مطلوب للأرفف الجديدة.',
            'zones.*.rack_shelves.*.identifier.max' => $lang == 'en' ? 'The rack shelf identifier may not be greater than 255 characters.' : 'معرف الرف لا يمكن أن يتجاوز 255 حرفًا.',
            'zones.*.rack_shelves.*.identifier.distinct' => $lang == 'en' ? 'Rack shelf identifiers must be unique within a zone.' : 'معرفات الأرفف يجب أن تكون فريدة داخل المنطقة.',
        ];
    }

    protected function prepareForValidation()
    {
        $lang = $this->header('lang', 'ar');
        \Illuminate\Support\Facades\App::setLocale($lang);
    }
}
